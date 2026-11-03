<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShopVipModel;
use App\Libs\CsIapSwitch;

/**
 * [カスタムエイド 2026-11-02] App内課金・Google Play課金 → カード払いの予約の見張り(毎時)
 *  予約中のカード払いが始まる48時間前から、アプリの定期購入の状態を Apple・Google に聞き直す
 *   - 自動更新が ON に戻っていた(再開した) → カード払いの予約を取り消す(二重払いを防ぐ)
 *   - アプリの契約が先に終わっていた(返金など) → カード払いを今すぐ始める(お客様のアプリを止めない)
 *  --dry を付けると、何も変えずに結果だけ出す
 */
class CsIapSwitchGuard extends Command
{
    protected $signature = 'cs:iap-switch-guard {--dry}';
    protected $description = 'App内課金→カード払いの予約の見張り';

    public function handle()
    {
        $dry = (bool)$this->option('dry');
        $list = ShopVipModel::where('card_type', 1)->where('status', 0)->where('is_subscription', 1)
            ->where('subscription_id', 'like', 'sub_%')
            ->where('start_time', '>', time())->where('start_time', '<', time() + 86400 * 2)->get();
        $this->line('予約中のカード払い(48時間以内に始まる): ' . $list->count() . ' 件' . ($dry ? '(確認だけ)' : ''));
        foreach ($list as $o) {
            $iap = ShopVipModel::where('sid', $o->sid)->whereIn('card_type', [2, 3])->where('status', 1)
                ->where('end_time', '>', time() - 86400 * 3)->orderBy('end_time', 'desc')->first();
            if (!$iap) { $this->line("sid {$o->sid} order {$o->order_no}: アプリの契約が見つからない(そのまま)"); continue; }
            $st = CsIapSwitch::status($iap);
            if (!$st['ok']) { $this->line("sid {$o->sid}: 状態を聞けなかった({$st['msg']})。次の回にもう一度"); continue; }
            if ($st['auto_renew'] && $st['active']) {
                $this->line("sid {$o->sid} order {$o->order_no}: 自動更新が ON → 予約を取り消す");
                if (!$dry) CsIapSwitch::cancelReservations($o->sid, 'guard: auto renew ON');
                continue;
            }
            if (!$st['active'] && $o->start_time > time() + 3600) {
                $this->line("sid {$o->sid} order {$o->order_no}: アプリの契約が先に終わった → 今すぐカード払いを始める");
                if (!$dry) CsIapSwitch::startNow($o, 'guard: iap ended early');
                continue;
            }
            $this->line("sid {$o->sid} order {$o->order_no}: 問題なし(アプリは " . date('Y-m-d H:i', $st['expires']) . " まで・カード払いは " . date('Y-m-d H:i', $o->start_time) . " から)");
        }
        return 0;
    }
}
