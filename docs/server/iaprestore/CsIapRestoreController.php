<?php
namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\ShopVipModel;
use App\Services\ShopVipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * [カスタムエイド 2026-11-03] 管理App「ご利用プラン」の「購入を復元」(App内課金・iPhone)
 *  払ったのにサーバーに届かなかったとき、残っている「まだ払っていない申し込み」(is_subscription=0)に、
 *  Apple から受け取り直した支払いの証明を合わせて、今までと同じ VipController@pay で確かめる(新しい確かめ方は作らない)
 */
class CsIapRestoreController extends Controller
{
    public function restore(Request $request)
    {
        $sid = auth('shop')->user()->sid;
        $items = (array)$request->input('items', []);
        foreach ($items as $it) {
            $tok = (string)($it['pay_token'] ?? ''); $tx = (string)($it['transaction_id'] ?? ''); $pid = (string)($it['product_id'] ?? '');
            if ($tok === '') continue;
            $base = function () use ($sid) {
                return ShopVipModel::where('sid', $sid)->where('card_type', 2)->where('is_subscription', 0)->where('create_time', '>=', time() - 30 * 86400);
            };
            $order = $pid !== '' ? $base()->where('product_id', $pid)->orderBy('id', 'desc')->first() : null;
            if (!$order) $order = $base()->orderBy('id', 'desc')->first();
            if (!$order) continue;
            try {
                $req = Request::create('/', 'POST', ['svid' => $order->id, 'pay_token' => $tok, 'transaction_id' => $tx]);
                $res = app(VipController::class)->pay($req, app(ShopVipService::class));
                $body = json_decode($res->getContent(), true);
            } catch (\Throwable $e) {
                Log::error('cs iap restore sid ' . $sid . ' order ' . $order->id . ' ' . $e->getMessage());
                continue;
            }
            Log::info('cs iap restore sid ' . $sid . ' order ' . $order->id . ' code ' . ($body['code'] ?? '?'));
            if ((int)($body['code'] ?? 0) === 200) {
                return responseToJson(200, '購入を復元しました。ご利用プランに反映しました。', ['restored' => 1]);
            }
        }
        $active = ShopVipModel::where('sid', $sid)->where('card_type', 2)->where('status', 1)->where('start_time', '<=', time())->where('end_time', '>=', time())->exists();
        if ($active) return responseToJson(200, 'ご購入は、すでにご利用プランに反映されています。', ['restored' => 0]);
        return responseToJson(200, '反映されていないご購入は見つかりませんでした。お困りのときは、ヘルプデスクからお問い合わせください。', ['restored' => 0]);
    }
}
