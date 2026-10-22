<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ShopMemberModel;
use App\Models\MemberCouponModel;
use App\Models\OrderModel;
use App\Models\ComeLogModel;
use App\Libs\CsPosBarcode;

/**
 * [カスタムエイド 2026-10-22] お店のバーコード番号のクーポンを、お客様が Card-San App で「今すぐ使う」
 *  記録の仕方は、お店が管理Appで読み取ったとき(Shop\CouponController@use)と同じ:
 *  使った回数・今日の予約へのクーポン使用・来店の記録
 */
class CsCouponUseController extends Controller
{
    public function use(Request $request, MemberCouponModel $coupon)
    {
        $member = ShopMemberModel::where('id', $coupon->mid)->where('uid', auth()->id())->first();
        if (!$member) {
            return responseToJson(403);
        }
        $st = CsPosBarcode::state($coupon);
        if (!$st['enabled']) {
            return responseToJson(400, 'このクーポンは、お店で読み取って使うクーポンです');
        }
        if ($st['using']) {
            return responseToJson(200, '', ['pos' => $st]);
        }
        if (!$st['in_date']) {
            return responseToJson(400, '有効期限の外のため、使えません');
        }
        if ($st['left'] === 0) {
            return responseToJson(400, '利用回数の上限に達しています');
        }

        DB::beginTransaction();
        try {
            $c = MemberCouponModel::where('id', $coupon->id)->lockForUpdate()->first();
            if ($c->self_use_time && time() < $c->self_use_time + CsPosBarcode::SHOW_SECONDS) {
                DB::rollBack();
                return responseToJson(200, '', ['pos' => CsPosBarcode::state($c)]);
            }
            if ($c->count > 0 && $c->count <= $c->use_count) {
                DB::rollBack();
                return responseToJson(400, '利用回数の上限に達しています');
            }

            $use_detail = $c->use_detail;
            $use_detail[] = [
                'said' => 0,
                'self' => 1,
                'datetime' => date('Y-m-d H:i:s')
            ];
            $c->update([
                'use_count' => DB::raw('use_count + 1'),
                'use_detail' => $use_detail,
                'self_use_time' => time()
            ]);
            $c->coupon->update([
                'use_count' => DB::raw('use_count + 1')
            ]);

            $order = OrderModel::where('sid', $c->sid)
                                ->where('mid', $c->mid)
                                ->where('start_time', '>=', strtotime(date('Y-m-d')))
                                ->where('start_time', '<', strtotime(date('Y-m-d') . ' +1 day'))
                                ->orderByRaw('ABS(CAST(start_time AS SIGNED) - ?)', [time()])
                                ->first();
            if ($order) {
                $coupon_detail = $order->coupon_detail;
                $coupon_detail[$c->coupon->id] = [
                    'count' => ($coupon_detail[$c->coupon->id]['count'] ?? 0) + 1,
                    'name' => $c->coupon->title
                ];
                $order->update(['coupon_detail' => $coupon_detail]);
            }

            $come = ComeLogModel::where('mid', $c->mid)->where('date', strtotime(date('Y-m-d')))->first();
            if (!$come) {
                ComeLogModel::create([
                    'sid' => $c->sid,
                    'mid' => $c->mid,
                    'oids' => $order ? [$order->id] : [],
                    'cids' => [$c->scid],
                    'date' => strtotime(date('Y-m-d')),
                    'times' => [date('H:i')],
                    'menu_names' => $order ? [($order->menu_name ?: ($order->menu->title ?? ''))] : [],
                    'day_sadmin_names' => $order ? [$order->dayAdmin->name ?? ''] : [],
                    'coupon_names' => [$c->coupon->title],
                    'qrcode_count' => 0
                ]);
            } else {
                $come->update([
                    'oids' => $order && !in_array($order->id, $come->oids) ? array_merge($come->oids, [$order->id]) : $come->oids,
                    'cids' => !in_array($c->scid, $come->cids) ? array_merge($come->cids, [$c->scid]) : $come->cids,
                    'times' => array_merge($come->times, [date('H:i')]),
                    'menu_names' => $order && !in_array($order->id, $come->oids) ? array_merge($come->menu_names, [($order->menu_name ?: ($order->menu->title ?? ''))]) : $come->menu_names,
                    'day_sadmin_names' => $order && !in_array($order->id, $come->oids) ? array_merge($come->day_sadmin_names, [$order->dayAdmin->name ?? '']) : $come->day_sadmin_names,
                    'coupon_names' => !in_array($c->scid, $come->cids) ? array_merge($come->coupon_names, [$c->coupon->title]) : $come->coupon_names
                ]);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return responseToJson(500);
        }

        return responseToJson(200, 'ご利用ありがとうございます', ['pos' => CsPosBarcode::state(MemberCouponModel::find($coupon->id))]);
    }
}
