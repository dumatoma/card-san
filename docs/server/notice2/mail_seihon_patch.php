<?php
// [カスタムエイド 2026-11-03] 見本「Card-San お知らせ見本」を正として、お店へのメールをすべて見本どおりにする
//   1) app/Libs/CsShopNotice.php を入れ替え(3〜8 の文・色・形、1 と C1〜C3 を作る systemMail)
//   2) resources/views/layouts/email/shop_*.blade.php(4つ)を、systemMail を呼ぶ1行に入れ替え
//   3) public/cs-mail/ に画像(アイコン・App Store・Google Play のバッジ)を置く
//   php -d disable_functions= mail_seihon_patch.php          → 確認だけ(何も変えない)
//   php -d disable_functions= mail_seihon_patch.php apply    → 控えを取ってから入れ替え
//   php -d disable_functions= mail_seihon_patch.php test 送り先 → 1・C1〜C3 の見本4通を送る
//   php -d disable_functions= mail_seihon_patch.php undo 控えのフォルダ → 元に戻す
$b = '/www/wwwroot/api.card-san.jp/'; $src = __DIR__ . '/'; $mode = $argv[1] ?? 'check';
$lib = 'app/Libs/CsShopNotice.php'; $old = 'a8dc6fa3753aceff6fbc4f84d3aea2f4';
$views = ['shop_register_success', 'shop_register', 'shop_login', 'shop_update_email'];
$imgs = ['icon.png', 'appstore_jp.png', 'googleplay_jp.png'];
$boot = function () use ($b) { require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); };
$clear = function () use ($b) { exec('cd ' . $b . ' && php -d disable_functions= artisan view:clear 2>&1', $o); echo implode("\n", $o) . "\n"; };

if ($mode === 'undo') {
    $bk = rtrim($argv[2] ?? '', '/') . '/'; if (!is_file($bk . 'CsShopNotice.php')) { echo "控えのフォルダがちがいます\n"; exit(1); }
    copy($bk . 'CsShopNotice.php', $b . $lib);
    foreach ($views as $v) if (is_file($bk . $v . '.blade.php')) copy($bk . $v . '.blade.php', $b . 'resources/views/layouts/email/' . $v . '.blade.php');
    $clear(); echo "元に戻しました(画像 public/cs-mail/ はそのまま残しています)\n"; exit(0);
}
if ($mode === 'test') {
    $boot(); $to = trim($argv[2] ?? ''); if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { echo "送り先を付けてください\n"; exit(1); }
    $s = \App\Services\EmailService::_init(); $x = ['email' => $to, 'name' => '見本 太郎', 'shop_name' => '見本の店', 'code' => '482916'];
    foreach (['shop_register_success' => 'アカウント登録完了のお知らせ', 'shop_login' => 'ログイン認証コード', 'shop_register' => 'アカウント認証コード', 'shop_update_email' => 'メールアドレス(ID)変更認証コード'] as $t => $sub) {
        $s->send($to, '【Card-San】(見本)' . $sub, $t, $x); echo "送りました:$t\n";
    }
    passthru('grep -n "EmailService" ' . $b . 'storage/logs/laravel.log | tail -4'); exit(0);
}

$ng = 0;
$cur = is_file($b . $lib) ? md5_file($b . $lib) : '';
echo ($cur === $old ? 'OK ' : 'NG ') . "今の CsShopNotice.php(md5 $cur。入れ替える前のはず $old)\n"; if ($cur !== $old) $ng++;
foreach (array_merge(['CsShopNotice.php'], array_map(function ($v) { return $v . '.blade.php'; }, $views), $imgs) as $f) {
    $ok = is_file($src . $f); echo ($ok ? 'OK ' : 'NG ') . "新しいファイル $f" . ($ok ? ' md5 ' . md5_file($src . $f) : '') . "\n"; if (!$ok) $ng++;
}
exec('php -l ' . escapeshellarg($src . 'CsShopNotice.php') . ' 2>&1', $o, $r); echo ($r === 0 ? 'OK ' : 'NG ') . "新しい CsShopNotice.php の文法\n"; if ($r !== 0) $ng++;
echo "\n今の件名(プログラムの中。見本の件名と同じかを Claude が確かめます):\n";
passthru('cd ' . $b . ' && grep -rn "shop_register_success\|shop_register\'\|shop_login\'\|shop_update_email\'" app --include=*.php | grep -v CsShopNotice | head -20');
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if ($mode !== 'apply') { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }

$bk = '/www/backup_customaid_mailseihon_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
copy($b . $lib, $bk . 'CsShopNotice.php');
foreach ($views as $v) { $p = $b . 'resources/views/layouts/email/' . $v . '.blade.php'; if (is_file($p)) copy($p, $bk . $v . '.blade.php'); }
copy($src . 'CsShopNotice.php', $b . $lib);
foreach ($views as $v) { $p = $b . 'resources/views/layouts/email/' . $v . '.blade.php'; copy($src . $v . '.blade.php', $p); @chown($p, 'www'); @chgrp($p, 'www'); }
@chown($b . $lib, 'www'); @chgrp($b . $lib, 'www');
@mkdir($b . 'public/cs-mail', 0755, true);
foreach ($imgs as $f) { copy($src . $f, $b . 'public/cs-mail/' . $f); @chown($b . 'public/cs-mail/' . $f, 'www'); }
$clear();
$boot();
$x = ['email' => 'a@example.com', 'name' => '見本', 'shop_name' => '見本の店', 'code' => '123456'];
foreach ($views as $v) {
    try { $h = view('layouts.email.' . $v, $x)->render(); echo (strpos($h, 'box-shadow') !== false && strpos($h, 'カスタムエイド') !== false ? 'OK ' : 'NG ') . "表示できる $v\n"; }
    catch (\Throwable $e) { echo "NG $v " . $e->getMessage() . "\n元に戻すとき: php -d disable_functions= mail_seihon_patch.php undo $bk\n"; exit(1); }
}
foreach (\App\Libs\CsShopNotice::samples() as $s) { $m = \App\Libs\CsShopNotice::build($s[0], '見本の店', '見本', $s[1]); echo ($m && strpos($m[1], 'box-shadow') !== false ? 'OK ' : 'NG ') . "お知らせ {$s[0]}\n"; }
foreach ($imgs as $f) { $c = trim(shell_exec('curl -s -o /dev/null -w "%{http_code}" https://api.card-san.jp/cs-mail/' . $f)); echo ($c === '200' ? 'OK ' : 'NG ') . "画像 https://api.card-san.jp/cs-mail/$f ($c)\n"; }
echo "\n入れ替えました。控え: $bk\n元に戻すとき: php -d disable_functions= mail_seihon_patch.php undo $bk\n";
