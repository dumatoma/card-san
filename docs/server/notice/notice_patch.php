<?php
// [カスタムエイド 2026-11-03] サーバー②:お店へのお知らせメール(無料期間・契約 11種類)を毎時35分に送る仕組み
//   このファイルと同じ場所に CsShopNotice.php・CsShopNoticeCommand.php を置く
//     php -d disable_functions= notice_patch.php        → 確認だけ
//     php -d disable_functions= notice_patch.php apply  → 控えを取ってから入れる(記録の表 cs_shop_notice_log を作る)
//     php -d disable_functions= notice_patch.php undo   → 外す(記録の表は残す)
//   入れたあとも、.env に CS_SHOP_NOTICE_SEND=true を書くまでは、本当には送らない(一覧を出すだけ)
$b = '/www/wwwroot/api.card-san.jp/'; $here = __DIR__ . '/'; $mode = $argv[1] ?? 'check'; $last = '/root/cs_notice_last_backup.txt';
$K = 'app/Console/Kernel.php';
$old = "        \$schedule->command('cs:iap-switch-guard')->hourlyAt(20)->withoutOverlapping();\n";
$new = $old . "        // [カスタムエイド 2026-11-03] お店へのお知らせメール(無料期間・契約)。送るのは .env の CS_SHOP_NOTICE_SEND=true のときだけ\n        \$schedule->command('cs:shop-notice')->hourlyAt(35)->withoutOverlapping();\n";
$files = ['CsShopNotice.php' => 'app/Libs/CsShopNotice.php', 'CsShopNoticeCommand.php' => 'app/Console/Commands/CsShopNoticeCommand.php'];
if ($mode === 'undo') {
    $bk = is_file($last) ? trim(file_get_contents($last)) : '';
    if (!$bk || !is_file($bk . '/Kernel.php')) { echo "NG 控えが見つかりません\n"; exit(1); }
    copy($bk . '/Kernel.php', $b . $K); echo "戻しました $K\n";
    foreach ($files as $src => $dst) if (is_file($b . $dst)) { rename($b . $dst, $bk . '/new_' . $src); echo "外しました $dst\n"; }
    exit(0);
}
$ng = 0; $k = (string)@file_get_contents($b . $K); $n = substr_count($k, $old);
echo ($n === 1 ? 'OK ' : 'NG ') . "毎時の処理の登録場所(見つかった数 $n)\n"; if ($n !== 1) $ng++;
if (strpos($k, 'cs:shop-notice') !== false) { echo "NG もう入っています\n"; $ng++; }
foreach ($files as $src => $dst) { $ok = is_file($here . $src); echo ($ok ? 'OK ' : 'NG ') . "新しいファイル $src(" . ($ok ? md5_file($here . $src) : 'なし') . ")\n"; if (!$ok) $ng++; }
foreach (['app/Libs/CsReserveMail.php', 'app/Libs/CsTrial.php'] as $x) { $ok = is_file($b . $x); echo ($ok ? 'OK ' : 'NG ') . "使う部品 $x\n"; if (!$ok) $ng++; }
require_once $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$has = \Illuminate\Support\Facades\Schema::hasTable('cs_shop_notice_log'); echo "記録の表 cs_shop_notice_log:" . ($has ? 'もうある' : 'まだない(apply で作る)') . "\n";
echo "本当に送る設定(CS_SHOP_NOTICE_SEND):" . (env('CS_SHOP_NOTICE_SEND', false) === true ? 'ON' : 'OFF(一覧を出すだけ)') . "\n";
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if ($mode !== 'apply') { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_notice_' . date('Ymd_His'); mkdir($bk, 0700, true); file_put_contents($last, $bk); copy($b . $K, $bk . '/Kernel.php');
if (!$has) {
    \Illuminate\Support\Facades\DB::statement("CREATE TABLE cs_shop_notice_log (id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, sid INT UNSIGNED NOT NULL DEFAULT 0, kind VARCHAR(16) NOT NULL, ref VARCHAR(64) NOT NULL DEFAULT '', to_email VARCHAR(255) NOT NULL DEFAULT '', sent_at INT UNSIGNED NOT NULL DEFAULT 0, UNIQUE KEY u_notice (sid, kind, ref)) DEFAULT CHARSET=utf8mb4");
    \Illuminate\Support\Facades\DB::table('cs_shop_notice_log')->insert(['sid' => 0, 'kind' => 'init', 'ref' => 'start', 'to_email' => '', 'sent_at' => time()]);
    echo "記録の表を作りました(この時刻より前の契約の記録には、契約のメールを送りません)\n";
}
$own = fileowner($b . 'app/Libs/CsTrial.php'); $grp = filegroup($b . 'app/Libs/CsTrial.php');
foreach ($files as $src => $dst) { copy($here . $src, $b . $dst); @chown($b . $dst, $own); @chgrp($b . $dst, $grp); @chmod($b . $dst, 0644); }
file_put_contents($b . $K, str_replace($old, $new, $k));
$bad = 0; foreach (array_merge(array_values($files), [$K]) as $f) { $o = []; exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r); echo ($r === 0 ? '文法OK ' : '文法NG ') . "$f\n"; if ($r) $bad++; }
if ($bad) { copy($bk . '/Kernel.php', $b . $K); foreach ($files as $dst) @unlink($b . $dst); echo "文法NG のため元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "\n入れました。控え: $bk\n戻すときは: php -d disable_functions= " . __FILE__ . " undo\n";
echo "次に: cd $b && php -d disable_functions= artisan cs:shop-notice --dry\n";
