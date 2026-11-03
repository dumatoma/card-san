<?php
namespace App\Libs;

use App\Models\ShopVipModel;
use App\Services\StripeBillingService;
use Illuminate\Support\Facades\Log;

/**
 * [カスタムエイド 2026-11-02] App内課金・Google Play課金 → クレジットカード(Stripe)の「予約」(B)
 *  - status():           アプリの定期購入の今の状態を Apple・Google に直接聞く(自動更新が ON か・いつまでか)
 *  - cancelReservations(): 予約中のカード払い(まだ始まっていない Stripe の契約)を取り消す(二重払いを防ぐ)
 *  - startNow():         アプリの契約が先に終わった(返金など)とき、予約中のカード払いを今すぐ始める
 */
class CsIapSwitch
{
    private static function log($msg)
    {
        Log::channel('my_stripe_pay')->info("\r\n[iapswitch] " . $msg);
    }

    /** @return array ['ok'=>bool, 'auto_renew'=>bool, 'active'=>bool, 'expires'=>int, 'msg'=>string] */
    public static function status(ShopVipModel $vip)
    {
        $r = ['ok' => false, 'auto_renew' => true, 'active' => false, 'expires' => 0, 'msg' => ''];
        try {
            if ($vip->card_type == 2) {
                $orig = $vip->apple_original_transaction_id;
                if (!$orig) { $r['msg'] = 'apple original transaction id なし'; return $r; }
                $res = (new ApplePay())->csSubscriptionStatus($orig);
                if (!$res['success']) { $r['msg'] = $res['message']; return $r; }
                $o = $res['output'];
                $r['ok'] = true;
                $r['auto_renew'] = (int)($o['autoRenewStatus'] ?? 1) === 1;
                $r['active'] = in_array((int)($o['status'] ?? 0), [1, 3, 4]); // 1 有効・3 請求の再試行中・4 猶予期間
                $r['expires'] = (int)(($o['expiresDate'] ?? 0) / 1000);
                return $r;
            }
            if ($vip->card_type == 3) {
                $token = $vip->google_pay_token;
                if (!$token) { $r['msg'] = 'google token なし'; return $r; }
                $res = (new GooglePay())->subscriptionsGet($token);
                if (!$res['success']) { $r['msg'] = $res['message']; return $r; }
                $sub = $res['output'];
                $line = $sub['lineItems'][0] ?? [];
                $state = $sub['subscriptionState'] ?? '';
                $r['ok'] = true;
                $r['auto_renew'] = !empty($line['autoRenewingPlan']['autoRenewEnabled']);
                $r['active'] = in_array($state, ['SUBSCRIPTION_STATE_ACTIVE', 'SUBSCRIPTION_STATE_IN_GRACE_PERIOD', 'SUBSCRIPTION_STATE_CANCELED', 'SUBSCRIPTION_STATE_ON_HOLD']);
                $r['expires'] = !empty($line['expiryTime']) ? strtotime($line['expiryTime']) : 0;
                return $r;
            }
            $r['msg'] = 'card_type ' . $vip->card_type;
        } catch (\Throwable $e) {
            $r['msg'] = $e->getMessage();
        }
        return $r;
    }

    /** このお店の予約中のカード払い(まだ始まっていない Stripe の契約) */
    public static function reservations($sid)
    {
        return ShopVipModel::where('sid', $sid)->where('card_type', 1)->where('status', 0)
            ->where('is_subscription', 1)->where('start_time', '>', time())
            ->where('subscription_id', 'like', 'sub_%')->get();
    }

    /** 予約中のカード払いを取り消す(Stripe の契約はお試し中なので、請求は起きない) */
    public static function cancelReservations($sid, $why)
    {
        $n = 0;
        foreach (self::reservations($sid) as $o) {
            try {
                $sub = (new StripeBillingService())->client()->subscriptions->retrieve($o->subscription_id);
                if ($sub->status === 'trialing') {
                    (new StripeBillingService())->client()->subscriptions->cancel($o->subscription_id);
                }
                $o->update(['status' => 2, 'cancel_time' => time(), 'is_subscription' => 0]);
                self::log("reservation canceled sid {$sid} order {$o->order_no} sub {$o->subscription_id} ({$why})");
                $n++;
            } catch (\Throwable $e) {
                self::log("reservation cancel FAILED sid {$sid} order {$o->order_no}: " . $e->getMessage());
            }
        }
        return $n;
    }

    /** アプリの契約が先に終わったとき:予約中のカード払いを今すぐ始める(Stripe のお試しを今終わらせる=今請求) */
    public static function startNow(ShopVipModel $o, $why)
    {
        try {
            (new StripeBillingService())->client()->subscriptions->update($o->subscription_id, ['trial_end' => 'now']);
            self::log("reservation started now sid {$o->sid} order {$o->order_no} sub {$o->subscription_id} ({$why})");
            return true;
        } catch (\Throwable $e) {
            self::log("reservation start-now FAILED sid {$o->sid} order {$o->order_no}: " . $e->getMessage());
            return false;
        }
    }
}
