<?php
// [カスタムエイド 2026-10-27] 店舗QR統一 段階8の続き:新しいお店を登録したときの店舗QRの中身を https://card-san.jp/s/店舗ID にする。確認だけ:引数なし/反映:apply
$apply = ($argv[1] ?? '') === 'apply'; $base = '/www/wwwroot/api.card-san.jp/'; $f = 'app/Http/Controllers/Api/Shop/AuthController.php';
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$t = file_get_contents($base . $f);
if (strpos($t, "'https://card-san.jp/s/' . \$shop->id") !== false) { echo "○ もう直してあります\n"; exit; }
$re = "/json_encode\(\[\s*'type'\s*=>\s*'shop',\s*'id'\s*=>\s*\\\$shop->id\s*\]\)/";
$c = preg_match_all($re, $t, $m);
if ($c !== 1) { echo "✕ 直す場所が {$c} か所(1か所のはず)。反映しません\n"; exit(1); }
echo "-- 今:\n" . $m[0][0] . "\n-- 直したあと:\n'https://card-san.jp/s/' . \$shop->id /* [カスタムエイド 2026-10-27] 店舗QRはお店のページの URL(店舗QR統一) */\n";
$n = preg_replace($re, "'https://card-san.jp/s/' . \$shop->id /* [カスタムエイド 2026-10-27] 店舗QRはお店のページの URL(店舗QR統一) */", $t, 1);
$tmp = '/tmp/customaid_qr8b_AuthController.php'; file_put_contents($tmp, $n);
exec('/www/server/php/73/bin/php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $rc); @unlink($tmp);
if ($rc) { echo "✕ 直したあとの書き方に間違い。反映しません\n" . implode("\n", $o) . "\n"; exit(1); }
echo "○ 直す場所1か所・書き方 問題なし\n";
if (!$apply) { echo "\n反映するときは apply を付けて実行\n"; exit; }
$bk = '/www/backup_customaid_qr8b_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($base . $f, $bk . 'AuthController.php');
file_put_contents($base . $f, $n); exec('kill -USR2 $(pgrep -f "[p]hp-fpm: master") 2>&1');
echo "○ 反映しました。控え {$bk}AuthController.php(php-fpm を読み直しました)\n";
