<?php
// [カスタムエイド 2026-10-29] 管理App の認証コードの SMS を試しに1通送る(本文はサーバーのプログラムに今書いてある文をそのまま使う。コードは見本の 000000)
//   使い方: php customaid_sms_authtext_test.php 電話番号
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b);
$to = preg_replace('/[^0-9]/', '', $argv[1] ?? ''); if (strlen($to) < 10) { echo "使い方:php customaid_sms_authtext_test.php 電話番号\n"; exit(1); }
$src = file_get_contents($b . 'app/Http/Controllers/Api/Shop/AuthController.php');
if (!preg_match("/AlibabaCloudSms\(\)\)->send\(\\\$request->phone, '([^']*)' \. \\\$code \. '([^']*)'\)/u", $src, $m)) { echo "NG 認証コードを送る行が見つかりません。この結果を Claude に送ってください。\n"; exit(1); }
$msg = $m[1] . '000000' . $m[2];
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo "送る文:$msg\n";
$r = (new App\Libs\AlibabaCloudSms())->send($to, $msg);
echo "結果:" . ($r['success'] ? 'OK' : 'NG ' . $r['message']) . "\n";
