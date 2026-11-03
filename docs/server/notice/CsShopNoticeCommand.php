<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libs\CsShopNotice;
use App\Libs\CsReserveMail;

/**
 * [カスタムエイド 2026-11-03] お店へのお知らせメール(毎時35分)
 *   php artisan cs:shop-notice --dry                 → 送らずに、だれに何を送るかの一覧だけ出す
 *   php artisan cs:shop-notice --sample --to=アドレス → 見本11種類を、そのアドレスに送る(記録しない)
 *   本当に送るのは .env に CS_SHOP_NOTICE_SEND=true があるときだけ(それまでは一覧を出すだけ)
 */
class CsShopNoticeCommand extends Command
{
    protected $signature = 'cs:shop-notice {--dry} {--sample} {--to=} {--sid=0}';
    protected $description = 'お店へのお知らせメール(無料期間・契約)';

    public function handle()
    {
        if ($this->option('sample')) {
            $to = (string)$this->option('to');
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { $this->error('--to=送り先 を付けてください'); return 1; }
            foreach (CsShopNotice::samples() as $s) {
                list($subject, $html, $text) = CsShopNotice::build($s[0], '見本の店', '見本 太郎', $s[1]);
                $ok = CsReserveMail::dm($to, '(見本)' . $subject, $html, $text, 'Card-San');
                $this->line(($ok ? '送りました ' : '送れませんでした ') . $s[0] . ' ' . $subject);
            }
            return 0;
        }
        $send = !$this->option('dry') && env('CS_SHOP_NOTICE_SEND', false) === true;
        $list = CsShopNotice::collect(!$this->option('dry'), (int)$this->option('sid'));
        $this->line(date('Y-m-d H:i') . ' 送る予定 ' . count($list) . ' 件' . ($send ? '' : '(送らずに一覧だけ)'));
        foreach ($list as $r) {
            list($sid, $kind, $ref, $to, $shopName, $name, $x) = $r;
            $m = CsShopNotice::build($kind, $shopName, $name, $x);
            if (!$m) continue;
            $line = "店舗 $sid [$kind] $to {$m[0]}";
            if (!$send) { $this->line('  ' . $line); continue; }
            if (CsReserveMail::dm($to, $m[0], $m[1], $m[2], 'Card-San')) { CsShopNotice::mark($sid, $kind, $ref, $to); $this->line('  送りました ' . $line); }
            else $this->line('  送れませんでした(次の回にもう一度) ' . $line);
        }
        return 0;
    }
}
