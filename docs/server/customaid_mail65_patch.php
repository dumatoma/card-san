<?php
// [カスタムエイド 2026-11-02] 段階6.5:店舗の自動メール(新規登録・登録完了・ログイン・メールアドレス変更の4種類)を DirectMail(no-reply@mail.card-san.jp)から送る
//   送り口 app/Services/EmailService.php の send() だけを変える。DirectMail で送れなかったときは、今までどおり Xサーバー(MAIL_)から送る
//   .env に CS_SYSTEM_MAIL_DM=false と書けば、今までの送り方だけに戻る
//   使い方: php -d disable_functions= customaid_mail65_patch.php → 確認だけ / ... apply → 控えを取ってから変える / ... test 送り先 → 「登録完了」の見本を送る
$b = '/www/wwwroot/api.card-san.jp/'; $f = 'app/Services/EmailService.php'; $mode = $argv[1] ?? 'check';
if ($mode === 'test') {
    require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $to = trim($argv[2] ?? ''); if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { echo "使い方:php -d disable_functions= customaid_mail65_patch.php test 送り先\n"; exit(1); }
    \App\Services\EmailService::_init()->send($to, '【Card-San】(試験)アカウント登録完了のお知らせ', 'shop_register_success', ['email' => $to, 'name' => '見本 太郎', 'shop_name' => '見本の店']);
    echo "送りました。storage/logs/laravel.log の最後:\n"; passthru('grep -n "EmailService" ' . escapeshellarg($b . 'storage/logs/laravel.log') . ' | tail -2'); exit(0);
}
$old = "        return Mail::send('layouts.email.' . \$templet, \$data, function(\$mail) use(\$email, \$title){";
$new = <<<'PHP'
        // [カスタムエイド 2026-11-02] 段階6.5:DirectMail(予約メールと同じ no-reply@mail.card-san.jp・送り手の名前 Card-San)で送る。送れなかったら下の今までの送り方(Xサーバー)
        if (env('CS_SYSTEM_MAIL_DM', true) && class_exists('\App\Libs\CsReserveMail')) {
            try {
                $html = view('layouts.email.' . $templet, $data)->render();
                $text = html_entity_decode(strip_tags(preg_replace('#<br\s*/?>|</p>|</div>|</tr>|</h[1-6]>#i', "\n", $html)), ENT_QUOTES, 'UTF-8');
                $text = trim(preg_replace("/\n{3,}/", "\n\n", implode("\n", array_map('trim', explode("\n", $text)))));
                if (\App\Libs\CsReserveMail::dm($email, $title, $html, $text, 'Card-San')) {
                    \Illuminate\Support\Facades\Log::info('EmailService dm ok ' . $templet);
                    return null;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('EmailService dm ' . $e->getMessage());
            }
            \Illuminate\Support\Facades\Log::warning('EmailService dm NG → Xサーバーで送る ' . $templet);
        }

PHP;
$new .= $old;
$s = file_get_contents($b . $f); $n = substr_count($s, $old);
echo ($n === 1 ? 'OK ' : 'NG ') . "送り口(EmailService の send):見つかった数 $n\n";
if (strpos($s, 'CS_SYSTEM_MAIL_DM') !== false) { echo "NG もう入っています\n"; $n = 0; }
foreach (['app/Libs/CsReserveMail.php'] as $x) { $ok = is_file($b . $x) && strpos(file_get_contents($b . $x), 'function dm(') !== false; echo ($ok ? 'OK ' : 'NG ') . "DirectMail の部品(CsReserveMail::dm)\n"; if (!$ok) $n = 0; }
if ($n !== 1) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if ($mode !== 'apply') { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_mail65_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($b . $f, $bk . basename($f));
file_put_contents($b . $f, str_replace($old, $new, $s));
exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r);
if ($r !== 0) { copy($bk . basename($f), $b . $f); echo "文法NG のため元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "文法OK $f\n\n変えました。控え: $bk\n";
