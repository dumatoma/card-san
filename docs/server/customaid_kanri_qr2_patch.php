<?php
// [カスタムエイド 2026-10-27] 管理アプリのダウンロードの QR の直し。確認だけ:引数なし/反映:apply
//   card-san.jp/s/ の下は Card-San App が開く場所(Universal Links)なので、card-san.jp/s/kanri を読むと Card-San App が開いていた(坂本さんの確認)。
//   Card-San App が開かない api.card-san.jp/kanri に移し、その URL で QR を作り直す(新しい名前 kanri_app.png。管理WEB の js の画像の URL も替える)
$apply = ($argv[1] ?? '') === 'apply'; $base = '/www/wwwroot/api.card-san.jp/'; $web = '/www/wwwroot/card-san.jp/js/';
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$fr = $base . 'routes/web.php'; $r = file_get_contents($fr); $c = substr_count($r, "Route::get('/s/kanri',");
echo ($c === 1 ? '○' : '✕') . " routes の /s/kanri {$c}か所 → /kanri に\n";
$files = []; foreach (glob($web . '*.js') as $f) { $n = substr_count(file_get_contents($f), 'qrcode/kanri_download.png'); if ($n) $files[$f] = $n; }
echo (count($files) ? '○' : '✕') . " 管理WEB の js " . count($files) . " ファイル(画像の URL を kanri_app.png に)\n";
if ($c !== 1 || !$files) { echo "✕ 反映しません\n"; exit(1); }
$nr = str_replace("Route::get('/s/kanri',", "Route::get('/kanri', /* [カスタムエイド 2026-10-27] /s/ の下は Card-San App が開くため移した */", $r);
$tmp = '/tmp/customaid_kanri2_web.php'; file_put_contents($tmp, $nr); exec('/www/server/php/73/bin/php -l ' . $tmp . ' 2>&1', $o, $rc); @unlink($tmp);
echo ($rc ? "✕ routes の書き方に間違い " . implode(' ', $o) : "○ routes の書き方 問題なし") . "\n"; if ($rc) exit(1);
if (!$apply) { echo "\n反映するときは apply を付けて実行\n"; exit; }
$bk = '/www/backup_customaid_kanri_qr2_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($fr, $bk . 'web.php');
file_put_contents($fr, $nr, LOCK_EX);
foreach ($files as $f => $n) { copy($f, $bk . basename($f)); $st = stat($f); file_put_contents($f, str_replace('qrcode/kanri_download.png', 'qrcode/kanri_app.png', file_get_contents($f))); @chown($f, $st['uid']); @chgrp($f, $st['gid']); }
chdir($base); require $base . 'vendor/autoload.php'; $app = require $base . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
\App\Services\QrcodeService::_init()->create('https://api.card-san.jp/kanri', 'kanri_app.png');
echo (is_file($base . 'storage/app/public/qrcode/kanri_app.png') ? "○ QR の画像を作りました(中身 https://api.card-san.jp/kanri)\n" : "✕ QR の画像を作れませんでした\n");
exec('kill -USR2 $(pgrep -f "[p]hp-fpm: master") 2>&1');
echo "○ 反映しました。控え $bk\n";
