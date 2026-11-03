<?php
// [カスタムエイド 2026-10-29] no-reply@card-san.jp から試験のメールを1通送る(HTML とテキストの両方入り)。Gmail・携帯のアドレスで迷惑メールに入らないかを見る
//   使い方: php customaid_mail_test.php 送り先のメールアドレス
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b);
$to = trim($argv[1] ?? ''); if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { echo "使い方:php customaid_mail_test.php 送り先のメールアドレス\n"; exit(1); }
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!env('CS_RESERVE_MAIL_PASSWORD')) { echo "NG .env に CS_RESERVE_MAIL_PASSWORD がありません(customaid_mail_env_setup.sh を先に)\n"; exit(1); }
config(['mail.mailers.cs_reserve' => ['transport' => 'smtp', 'host' => env('CS_RESERVE_MAIL_HOST'), 'port' => (int) env('CS_RESERVE_MAIL_PORT'), 'encryption' => env('CS_RESERVE_MAIL_ENCRYPTION'), 'username' => env('CS_RESERVE_MAIL_USERNAME'), 'password' => env('CS_RESERVE_MAIL_PASSWORD'), 'timeout' => 20]]);
$text = "Card-San の予約メールの試験です。\n\nこのメールが「迷惑メール」ではなく受信トレイに届いたかを確かめてください。\n\nhttps://r.card-san.jp/\n\n--\nCard-San(このアドレスは送信専用です)";
$html = '<!doctype html><html lang="ja"><body style="font-family:sans-serif;color:#1D1D1F"><p>Card-San の予約メールの試験です。</p><p>このメールが「迷惑メール」ではなく受信トレイに届いたかを確かめてください。</p><p><a href="https://r.card-san.jp/">https://r.card-san.jp/</a></p><p style="color:#6E6E73;font-size:12px">Card-San(このアドレスは送信専用です)</p></body></html>';
try {
    Illuminate\Support\Facades\Mail::mailer('cs_reserve')->send([], [], function ($m) use ($to, $html, $text) {
        $m->from('no-reply@card-san.jp', 'Card-San')->to($to)->subject('【試験】Card-San 予約メールの届き方の確認');
        $m->setBody($html, 'text/html'); $m->addPart($text, 'text/plain');
    });
    echo "送りました → $to\n";
} catch (\Throwable $e) { echo "NG " . $e->getMessage() . "\n"; exit(1); }
