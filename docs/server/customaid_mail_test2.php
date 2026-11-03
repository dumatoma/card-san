<?php
// [カスタムエイド 2026-10-29] 試験メール2:iCloud で迷惑メールに入った原因になりそうな所を直して送る
//   ①メールID を「…@swift.generated」→「…@card-san.jp」 ②あいさつの名前を [127.0.0.1] → card-san.jp ③本文を本物の予約メールの形に(「試験」「迷惑メール」の言葉を使わない)
//   使い方: php customaid_mail_test2.php 送り先のメールアドレス
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b);
$to = trim($argv[1] ?? ''); if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { echo "使い方:php customaid_mail_test2.php 送り先のメールアドレス\n"; exit(1); }
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['mail.mailers.cs_reserve' => ['transport' => 'smtp', 'host' => env('CS_RESERVE_MAIL_HOST'), 'port' => (int) env('CS_RESERVE_MAIL_PORT'), 'encryption' => env('CS_RESERVE_MAIL_ENCRYPTION'), 'username' => env('CS_RESERVE_MAIL_USERNAME'), 'password' => env('CS_RESERVE_MAIL_PASSWORD'), 'timeout' => 20, 'local_domain' => 'card-san.jp']]);
$text = "坂本 様\n\nご予約ありがとうございます。\n以下の内容でご予約を承りました。\n\n予約番号:UU0000000000000\n日時:11月6日(金) 12:00 〜 13:00\nメニュー:レディースカット\n担当者:指名なし\n\nご予約の詳細はこちら\nhttps://r.card-san.jp/2GZ47R\n\nキャンセルや来店時刻に遅れる場合は、お店にご連絡ください。\n\nCARD SALON\n電話:03-1234-5678\n愛知県名古屋市中川区広田町1-42\n\n--\nこのメールは送信専用のアドレスから送っています。ご返信いただいてもお店には届きません。\nCard-San https://card-san.jp/";
$h = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
$html = '<!doctype html><html lang="ja"><head><meta charset="utf-8"></head><body style="margin:0;background:#F5F5F7;font-family:sans-serif;color:#1D1D1F"><div style="max-width:480px;margin:0 auto;padding:16px"><p style="font-weight:bold;font-size:18px;text-align:center">CARD SALON</p><div style="background:#fff;border-radius:12px;padding:16px">'
  . nl2br($h("坂本 様\n\nご予約ありがとうございます。\n以下の内容でご予約を承りました。\n\n予約番号:UU0000000000000\n日時:11月6日(金) 12:00 〜 13:00\nメニュー:レディースカット\n担当者:指名なし"))
  . '<p style="text-align:center;margin:20px 0"><a href="https://r.card-san.jp/2GZ47R" style="display:inline-block;background:#2B8A87;color:#fff;padding:12px 24px;border-radius:10px;text-decoration:none;font-weight:bold">ご予約の詳細を見る</a></p>'
  . nl2br($h("キャンセルや来店時刻に遅れる場合は、お店にご連絡ください。\n\nCARD SALON\n電話:03-1234-5678\n愛知県名古屋市中川区広田町1-42")) . '</div><p style="font-size:12px;color:#6E6E73">このメールは送信専用のアドレスから送っています。ご返信いただいてもお店には届きません。<br>Card-San https://card-san.jp/</p></div></body></html>';
try {
    Illuminate\Support\Facades\Mail::mailer('cs_reserve')->send([], [], function ($m) use ($to, $html, $text) {
        $m->getSwiftMessage()->setId(bin2hex(random_bytes(12)) . '@card-san.jp');
        $m->from('no-reply@card-san.jp', 'CARD SALON')->to($to)->subject('【CARD SALON】ご予約を受け付けました');
        $m->setBody($html, 'text/html'); $m->addPart($text, 'text/plain');
    });
    echo "送りました → $to\n";
} catch (\Throwable $e) { echo "NG " . $e->getMessage() . "\n"; exit(1); }
