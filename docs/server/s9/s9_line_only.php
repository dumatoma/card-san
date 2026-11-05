<?php
// [カスタムエイド 2026-11-04 追記9] 試験用:店舗の受け付ける方法を「LINE だけ」にする・メールと LINE に戻す(パソコンで LINE のログインを試すため)
//   php -d disable_functions= /root/s9_line_only.php only 11   … メール・SMS をオフ、LINE だけオン
//   php -d disable_functions= /root/s9_line_only.php back 11   … メールと LINE をオン(今と同じに戻す)
//   php -d disable_functions= /root/s9_line_only.php show 11   … 今の受け付ける方法を見るだけ

$base = '/www/wwwroot/api.card-san.jp/';
require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!class_exists('App\Libs\CsReserveCh')) exit("CsReserveCh がありません\n");

$mode = $argv[1] ?? '';
$sid = (int) ($argv[2] ?? 0);
if (!$sid || !in_array($mode, ['only', 'back', 'show'], true)) exit("使い方:only|back|show 店舗ID\n");
echo "今:" . json_encode(App\Libs\CsReserveCh::get($sid), JSON_UNESCAPED_UNICODE) . "\n";
if ($mode === 'show') exit;
$r = $mode === 'only' ? App\Libs\CsReserveCh::set($sid, false, false, true) : App\Libs\CsReserveCh::set($sid, true, false, true);
echo ($r['ok'] ? 'OK ' : 'NG ') . $r['msg'] . "\n";
echo "変えたあと:" . json_encode(App\Libs\CsReserveCh::get($sid), JSON_UNESCAPED_UNICODE) . "\n";
