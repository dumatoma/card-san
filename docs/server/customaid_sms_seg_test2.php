<?php
// [カスタムエイド 2026-10-28] SMS の改行が何文字で数えられるか(何通分の料金になるか)を確かめる その2。
//   1回目は、送信の記録に Alibaba の返事が「{}」としか残っておらず読めなかった(記録の書き方の問題)。
//   今回は Alibaba に直接送り、返ってきた「Segments(何通分)」をそのまま出す。送る文・送り方(改行を CR+LF にする)は今の部品と同じ
//   php customaid_sms_seg_test2.php 09012345678
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b);
$to = preg_replace('/[^0-9]/', '', $argv[1] ?? ''); if (strlen($to) < 10) { echo "送り先の電話番号を付けてください\n"; exit(1); }
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$msg = "CARD SALON表参道本…\n山田 花子(テスト送信)様 予約キャンセル\n[詳細]https://r.card-san.jp/a1B2c3";
$msg = preg_replace("/(\\r\\n|\\r|\\n)/", "\r\n", $msg); // 今の部品(AlibabaCloudSms::send)と同じ
echo "送る文の長さ:" . mb_strlen($msg) . " 文字(改行を CR+LF の2文字で数えた長さ)\n";
new App\Libs\AlibabaCloudSms(); // 鍵の設定は部品のものを使う(値は出さない)
$r = AlibabaCloud\Client\AlibabaCloud::rpc()->product('Dysmsapi')->version('2018-05-01')->action('SendMessageToGlobe')->method('POST')
  ->host('dysmsapi.ap-southeast-1.aliyuncs.com')->options(['query' => ['From' => 'cdsSMS', 'RegionId' => 'ap-southeast-1', 'To' => '81' . ltrim($to, '0'), 'Message' => $msg]])->request();
$a = $r->toArray();
echo "結果:" . ($a['ResponseCode'] ?? '?') . "\n何通分(Segments):" . ($a['Segments'] ?? '?') . "\n";
echo "\n何通分=1 なら改行は1文字扱い(坂本さんの形のままでよい)。何通分=2 なら改行は2文字で数えられている(言葉を短くする必要あり)。\n";
