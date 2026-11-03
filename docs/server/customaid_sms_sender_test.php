<?php
// [カスタムエイド 2026-10-29] SMS の送り主の名前(Sender ID)を変えて試験の SMS を1通送る(今の SMS の送り方は変えない)
// 2026-10-29 追記:空白を入れた名前も試せるようにした(例 "CardSan Rsv")。3つ目に本文を渡すとその文で送る(認証コードの形の試験用)
//   php customaid_sms_sender_test.php 09012345678 "CardSan Rsv"
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b);
$to = preg_replace('/[^0-9]/', '', $argv[1] ?? ''); $from = trim((string) ($argv[2] ?? ''));
if (strlen($to) < 10 || !preg_match('/^[A-Za-z0-9 ]{1,11}$/', $from)) { echo "使い方:php customaid_sms_sender_test.php 電話番号 送り主の名前(英数字と空白で11文字まで)\n"; exit(1); }
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
new App\Libs\AlibabaCloudSms(); // 鍵の設定は部品のものを使う(値は出さない)
$msg = isset($argv[3]) && $argv[3] !== '' ? (string) $argv[3] : "Card-San\r\n送り主の名前の試験です(" . $from . ")";
$r = AlibabaCloud\Client\AlibabaCloud::rpc()->product('Dysmsapi')->version('2018-05-01')->action('SendMessageToGlobe')->method('POST')
  ->host('dysmsapi.ap-southeast-1.aliyuncs.com')->options(['query' => ['From' => $from, 'RegionId' => 'ap-southeast-1', 'To' => '81' . ltrim($to, '0'), 'Message' => $msg]])->request();
$a = $r->toArray();
echo "送り主:$from\n結果:" . ($a['ResponseCode'] ?? '?') . "(" . ($a['ResponseDescription'] ?? '') . ")\n何通分:" . ($a['Segments'] ?? '?') . "\n";
