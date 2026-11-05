<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Libs\CsReserveCh;
use Illuminate\Support\Facades\DB;

/**
 * [カスタムエイド 2026-11-03] 段階8-2:管理App「ご利用状況」
 *  GET /api/shop/cs/usage  今月の予約SMS・メッセージ・クーポンの残り(月の数・追加でご購入の分)、数が戻る日、オプション。だれでも見られる(副管理者も)
 *  数は web_shop の vip_*_month_count(今月の残り)・vip_*_count(追加でご購入の分)。月の数は webConfig(AutoRecoverVipCount と同じ)
 */
class CsUsageController extends Controller
{
    public function show()
    {
        $sid = auth('shop')->user()->sid;
        $r = DB::table('web_shop')->where('id', (int) $sid)->first(['vip_sms_month_count', 'vip_sms_count', 'vip_message_month_count', 'vip_message_count', 'vip_coupon_month_count', 'vip_coupon_count']);
        if (!$r) return responseToJson(400, '店舗が見つかりません');
        $cfg = function ($k, $d) { try { $v = webConfig()->get($k); return is_numeric($v) && (int) $v > 0 ? (int) $v : $d; } catch (\Throwable $e) { return $d; } };
        $day = 0;
        try { $day = (int) \App\Models\ShopVipModel::where('sid', (int) $sid)->orderBy('id', 'desc')->value('settlement_day'); } catch (\Throwable $e) { $day = 0; }
        $item = function ($left, $extra, $month) { return ['month' => $month, 'left' => (int) $left, 'extra' => (int) $extra]; };
        $ch = CsReserveCh::get($sid);
        return responseToJson(200, '', [
            'reset_day' => $day,
            'sms' => $item($r->vip_sms_month_count, $r->vip_sms_count, $cfg('vip_sms_month_count', 100)),
            'message' => $item($r->vip_message_month_count, $r->vip_message_count, $cfg('vip_message_month_count', 3000)),
            'coupon' => $item($r->vip_coupon_month_count, $r->vip_coupon_count, $cfg('vip_coupon_month_count', 3000)),
            'opt_sms' => $ch['opt_sms'],
            'opt_line' => $ch['opt_line'],
        ]);
    }
}
