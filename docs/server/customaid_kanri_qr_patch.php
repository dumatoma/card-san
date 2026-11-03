<?php
// [カスタムエイド 2026-10-27] 管理WEB の「管理アプリのダウンロード」の QR を、外部サービス me-qr から自社のものに替える。確認だけ:引数なし/反映:apply
//   1. https://card-san.jp/s/kanri:iPhone → App Store(CardSan管理)、Android → Google Play、パソコン → 2つのボタン(routes/web.php に1つ)
//   2. その URL の QR 画像 storage/app/public/qrcode/kanri_download.png(https://api.card-san.jp/storage/qrcode/kanri_download.png)
//   3. 管理WEB(card-san.jp)の作ったあとの js の中の me-qr の画像の URL を、2 の URL に置き換える(ソースの jppc にも同じ直しを入れる)
$apply = ($argv[1] ?? '') === 'apply'; $base = '/www/wwwroot/api.card-san.jp/'; $web = '/www/wwwroot/card-san.jp/js/';
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$new = 'https://api.card-san.jp/storage/qrcode/kanri_download.png';
$olds = ['https://cdn.me-qr.com/qr/125260401.png?v=1725897315', 'https://cdn2.me-qr.com/qr/110063711.png?v=1716211083'];
$files = []; foreach (glob($web . '*.js') as $f) { $t = file_get_contents($f); $c = 0; foreach ($olds as $o) $c += substr_count($t, $o); if ($c) { $files[$f] = $c; echo "○ " . basename($f) . " に me-qr {$c}か所\n"; } }
if (!$files) { echo "✕ me-qr の場所が見つかりません(もう直してあるか、場所が違う)\n"; }
$fr = $base . 'routes/web.php'; $r = file_get_contents($fr); $has = strpos($r, "'/s/kanri'") !== false;
echo $has ? "○ /s/kanri はもうあります\n" : "○ /s/kanri を足します\n";
$rt = "\n// [カスタムエイド 2026-10-27] 管理アプリ(CardSan管理)のダウンロード:iPhone は App Store、Android は Google Play、パソコンは2つのボタン\n"
 . "Route::get('/s/kanri', function () { \$ua = (string) request()->header('User-Agent'); \$as = 'https://apps.apple.com/jp/app/id1663708240'; \$gp = 'https://play.google.com/store/apps/details?id=uni.UNI86DBD5D';\n"
 . "  if (preg_match('/iPhone|iPad|iPod/i', \$ua)) return redirect(\$as); if (preg_match('/Android/i', \$ua)) return redirect(\$gp);\n"
 . "  return response('<!doctype html><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><title>CardSan管理</title><body style=\"font-family:sans-serif;text-align:center;padding:40px 16px\"><h2>CardSan管理 アプリ</h2><p>スマートフォン・タブレットに入れてください。</p><p><a href=\"' . \$as . '\">App Store(iPhone・iPad)</a></p><p><a href=\"' . \$gp . '\">Google Play(Android)</a></p></body>')->header('Content-Type', 'text/html; charset=utf-8'); });\n";
$tmp = '/tmp/customaid_kanri_web.php'; file_put_contents($tmp, $r . ($has ? '' : $rt)); exec('/www/server/php/73/bin/php -l ' . $tmp . ' 2>&1', $o, $rc); @unlink($tmp);
echo ($rc ? "✕ routes の書き方に間違い " . implode(' ', $o) : "○ routes の書き方 問題なし") . "\n"; if ($rc || !$files) exit(1);
if (!$apply) { echo "\n反映するときは apply を付けて実行\n"; exit; }
$bk = '/www/backup_customaid_kanri_qr_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($fr, $bk . 'web.php');
foreach ($files as $f => $c) { copy($f, $bk . basename($f)); $st = stat($f); file_put_contents($f, str_replace($olds, $new, file_get_contents($f))); @chown($f, $st['uid']); @chgrp($f, $st['gid']); }
if (!$has) file_put_contents($fr, $r . $rt, LOCK_EX);
chdir($base); require $base . 'vendor/autoload.php'; $app = require $base . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\App\Services\QrcodeService::_init()->create('https://card-san.jp/s/kanri', 'kanri_download.png');
echo (is_file($base . 'storage/app/public/qrcode/kanri_download.png') ? "○ QR の画像を作りました\n" : "✕ QR の画像を作れませんでした\n");
exec('kill -USR2 $(pgrep -f "[p]hp-fpm: master") 2>&1');
echo "○ 反映しました。控え $bk\n確かめ方:curl -s -o /dev/null -w '%{http_code} %{redirect_url}\\n' -A iPhone https://card-san.jp/s/kanri\n";
