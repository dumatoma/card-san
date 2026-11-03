<?php
// [カスタムエイド 2026-11-03] 購入を復元の受け口 POST /api/shop/cs/iap/restore を足す
//   php -d disable_functions= iaprestore_patch.php → 確認だけ / ... apply → 控えを取ってから足す / ... undo 控えのフォルダ
$b = '/www/wwwroot/api.card-san.jp/'; $src = __DIR__ . '/'; $mode = $argv[1] ?? 'check';
$r = 'routes/api.php'; $c = 'app/Http/Controllers/Api/Shop/CsIapRestoreController.php';
$old = "            Route::post('/vip/pay', 'VipController@pay');";
$new = $old . "\n            Route::post('/cs/iap/restore', 'CsIapRestoreController@restore'); // [カスタムエイド 2026-11-03] 購入を復元";
if ($mode === 'undo') {
    $bk = rtrim($argv[2] ?? '', '/') . '/'; if (!is_file($bk . 'api.php')) { echo "控えのフォルダがちがいます\n"; exit(1); }
    copy($bk . 'api.php', $b . $r); @unlink($b . $c); echo "元に戻しました\n"; exit(0);
}
$s = file_get_contents($b . $r); $n = substr_count($s, $old); $ng = 0;
echo ($n === 1 ? 'OK ' : 'NG ') . "routes/api.php の vip/pay の行(見つかった数 $n)\n"; if ($n !== 1) $ng++;
if (strpos($s, 'CsIapRestoreController') !== false) { echo "NG もう入っています\n"; $ng++; }
$v = file_get_contents($b . 'app/Http/Controllers/Api/Shop/VipController.php');
foreach (['namespace App\Http\Controllers\Api\Shop;', 'use App\Services\ShopVipService;', 'public function pay(Request $request, ShopVipService $service)'] as $w) { $ok = strpos($v, $w) !== false; echo ($ok ? 'OK ' : 'NG ') . "VipController に「$w」\n"; if (!$ok) $ng++; }
$m = file_get_contents($b . 'app/Models/ShopVipModel.php'); echo (strpos($m, 'product_id') !== false ? 'OK ' : '?? ') . "ShopVipModel に product_id\n";
exec('php -l ' . escapeshellarg($src . 'CsIapRestoreController.php') . ' 2>&1', $o, $rc); echo ($rc === 0 ? 'OK ' : 'NG ') . "新しいファイルの文法\n"; if ($rc) $ng++;
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if ($mode !== 'apply') { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_iaprestore_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($b . $r, $bk . 'api.php');
copy($src . 'CsIapRestoreController.php', $b . $c); @chown($b . $c, 'www'); @chgrp($b . $c, 'www');
file_put_contents($b . $r, str_replace($old, $new, $s));
exec('php -l ' . escapeshellarg($b . $r) . ' 2>&1', $o2, $rc2);
if ($rc2) { copy($bk . 'api.php', $b . $r); @unlink($b . $c); echo "文法NG のため元に戻しました\n"; exit(1); }
passthru('cd ' . $b . ' && php -d disable_functions= artisan route:list 2>/dev/null | grep "cs/iap/restore"');
echo "\n足しました。控え: $bk\n元に戻すとき: php -d disable_functions= iaprestore_patch.php undo $bk\n";
