<?php
// [カスタムエイド 2026-10-29] Card-San App(お客様)のサインイン・登録の認証コードの SMS を試しに1通送る(本文はサーバーのプログラムに今書いてある文をそのまま使う。コードは見本の 000000)
//   使い方: php customaid_sms_member_authtext_test.php 電話番号
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b);
$to = preg_replace('/[^0-9]/', '', $argv[1] ?? ''); if (strlen($to) < 10) { echo "使い方:php customaid_sms_member_authtext_test.php 電話番号\n"; exit(1); }
$src = file_get_contents($b . 'app/Http/Controllers/Api/Member/AuthController.php');
$n = preg_match_all("/AlibabaCloudSms\(\)\)->send\(\\\$request->phone, '([^']*)' \. \\\$code \. '([^']*)'\)/u", $src, $m);
if (!$n) { echo "NG 認証コードを送る行が見つかりません。この結果を Claude に送ってください。\n"; exit(1); }
echo "認証コードを送る場所:{$n}か所\n"; for ($i = 0; $i < $n; $i++) echo ' ' . ($i + 1) . ':' . $m[1][$i] . '(コード)' . $m[2][$i] . "\n";
$msg = $m[1][0] . '000000' . $m[2][0];
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo "送る文:$msg\n";
$r = (new App\Libs\AlibabaCloudSms())->send($to, $msg);
echo "結果:" . ($r['success'] ? 'OK' : 'NG ' . $r['message']) . "\n";
