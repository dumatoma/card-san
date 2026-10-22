<?php
// [カスタムエイド 2026-10-22] クーポンで、お店のバーコード番号(レジ・POS で読み取る)を使えるようにする
//   1. 欄を足す:shop_coupon に barcode_type(0 自動/1 JAN/2 CODE128)・barcode_value、member_coupon に self_use_time(お客様が使った時刻)
//   2. 部品 app/Libs/CsPosBarcode.php(番号の確かめ・画像・状態)、app/Http/Controllers/Api/Member/CsCouponUseController.php(お客様が「今すぐ使う」)
//   3. ShopCouponValidator:番号を確かめて保存する
//   4. Shop/CouponController(index・show):番号を返す(管理Appの編集・コピー用)
//   5. Member/CouponController(show):お店の番号の状態を返す(使う前は番号も画像も返さない)
//   6. routes/api.php:Card-San App 用に POST coupon/{coupon}/self_use を足す
// 使い方:
//   先に /root/customaid_CsPosBarcode.php と /root/customaid_CsCouponUseController.php を置く
//   php -d disable_functions= /root/customaid_posbarcode_patch.php        … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/customaid_posbarcode_patch.php apply  … 実際に変更する(先にバックアップを取る)

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_posbarcode_20261022/';
$tag = '[カスタムエイド 2026-10-22]';
$apply = (($argv[1] ?? '') === 'apply');
$libPath = 'app/Libs/CsPosBarcode.php';
$ctlPath = 'app/Http/Controllers/Api/Member/CsCouponUseController.php';

require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach ([$libPath, $ctlPath] as $p) if (file_exists($base . $p)) exit("$p がすでにあります。何も変更していません。\n");
$libSrc = @file_get_contents('/root/customaid_CsPosBarcode.php');
$ctlSrc = @file_get_contents('/root/customaid_CsCouponUseController.php');
if ($libSrc === false || strpos($libSrc, 'class CsPosBarcode') === false) exit("/root/customaid_CsPosBarcode.php がありません。何も変更していません。\n");
if ($ctlSrc === false || strpos($ctlSrc, 'class CsCouponUseController') === false) exit("/root/customaid_CsCouponUseController.php がありません。何も変更していません。\n");
foreach (['App\Models\OrderModel', 'App\Models\ComeLogModel', 'App\Models\MemberCouponModel', 'App\Models\ShopCouponModel', 'Picqer\Barcode\BarcodeGeneratorPNG'] as $cls) {
    if (!class_exists($cls)) exit("$cls が見つかりません。何も変更していません。\n");
}
if (!defined('Picqer\Barcode\BarcodeGeneratorPNG::TYPE_EAN_8')) exit("バーコードの部品が JAN(8桁)に対応していません。何も変更していません。\n");

// --- 1. 欄 ---
$sc = new App\Models\ShopCouponModel();
$mc = new App\Models\MemberCouponModel();
$db = $sc->getConnection();
$pre = $db->getTablePrefix();
$colsOf = function ($t) use ($db, $pre) { return array_map(function ($c) { return $c->Field; }, $db->select("SHOW COLUMNS FROM `" . $pre . $t . "`")); };
$scCols = $colsOf($sc->getTable());
$mcCols = $colsOf($mc->getTable());
$addSc = array_values(array_diff(['barcode_type', 'barcode_value'], $scCols));
$addMc = array_values(array_diff(['self_use_time'], $mcCols));
echo "① 欄を足します:" . $pre . $sc->getTable() . " に " . ($addSc ? implode(', ', $addSc) : '(なし)') . "/" . $pre . $mc->getTable() . " に " . ($addMc ? implode(', ', $addMc) : '(なし)') . "\n";

$changes = [];
$must = function ($f, $re, $s) { if (preg_match_all($re, $s) !== 1) exit("$f の直す場所が見つかりません(または2つ以上)。何も変更していません。\n"); };
$read = function ($f) use ($base, $tag) {
    $s = file_get_contents($base . $f);
    if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
    if (strpos($s, 'CsPosBarcode') !== false || strpos($s, 'CsCouponUseController') !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
    return $s;
};

// --- 3. 入力のチェック ---
$f = 'app/Validators/ShopCouponValidator.php';
$s = $read($f);
$re = '/([ \t]*)\$data\[\'is_bar_code\'\] = \$request->is_bar_code \? 1 : 0;\n/u';
$must($f, $re, $s);
$changes[$f] = preg_replace_callback($re, function ($m) use ($tag) {
    $i = $m[1];
    return $m[0]
        . "$i// $tag お店のバーコード番号(1 JAN/2 CODE128)。自動で発行のときは 0\n"
        . "$i\$cs_bc = \\App\\Libs\\CsPosBarcode::normalize(\$data['is_bar_code'] ? \$request->barcode_type : 0, \$request->barcode_value);\n"
        . "{$i}if(is_string(\$cs_bc)){\n"
        . "$i    return ['success' => false, 'message' => \$cs_bc];\n"
        . "$i}\n"
        . "$i\$data['barcode_type'] = \$cs_bc['type'];\n"
        . "$i\$data['barcode_value'] = \$cs_bc['value'];\n";
}, $s, 1);
echo "③ $f:お店のバーコード番号を確かめて保存する\n";

// --- 4. 管理App の一覧・詳しく ---
$f = 'app/Http/Controllers/Api/Shop/CouponController.php';
$s = $read($f);
foreach (['$v', '$coupon'] as $x) {
    $re = '/([ \t]*)\'is_bar_code\' => ' . preg_quote($x, '/') . '->is_bar_code,\n/u';
    $must($f, $re, $s);
    $s = preg_replace_callback($re, function ($m) use ($x, $tag) {
        return $m[0] . $m[1] . "'barcode_type' => (int) " . $x . "->barcode_type, // $tag お店のバーコード番号\n"
            . $m[1] . "'barcode_value' => (string) " . $x . "->barcode_value,\n";
    }, $s, 1);
}
$changes[$f] = $s;
echo "④ $f:一覧・詳しくで番号を返す\n";

// --- 5. Card-San App のクーポン詳細 ---
$f = 'app/Http/Controllers/Api/Member/CouponController.php';
$s = $read($f);
$re = '/([ \t]*)\'remark\' => \$coupon->coupon->remark,\n/u';
$must($f, $re, $s);
$changes[$f] = preg_replace_callback($re, function ($m) use ($tag) {
    return $m[0] . $m[1] . "'pos' => \\App\\Libs\\CsPosBarcode::state(\$coupon), // $tag お店のバーコード番号の状態(使う前は番号を返さない)\n";
}, $s, 1);
echo "⑤ $f:お店のバーコード番号の状態を返す\n";

// --- 6. 入口 ---
$f = 'routes/api.php';
$s = $read($f);
$re = '/\/\/优惠券\s*\n([ \t]*)Route::apiResource\(\'coupon\', \'CouponController\'\);\n/u';
$must($f, $re, $s);
$changes[$f] = preg_replace_callback($re, function ($m) use ($tag) {
    return $m[0] . "\n" . $m[1] . "// $tag お店のバーコード番号のクーポンを「今すぐ使う」\n"
        . $m[1] . "Route::post('/coupon/{coupon}/self_use', 'CsCouponUseController@use');\n";
}, $s, 1);
echo "⑥ $f:Card-San App 用の入口を足す\n";

$routeCache = null;
foreach (glob($base . 'bootstrap/cache/routes*.php') ?: [] as $rc) $routeCache = $rc;
if ($routeCache) echo "※ 入口の一覧がキャッシュされています。実行するときに作り直します\n";

if (!$apply) {
    echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n";
    exit;
}

if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($changes as $f => $src) {
    if (!copy($base . $f, $backup . str_replace('/', '__', $f))) exit("バックアップに失敗しました: $f\n何も変更していません。\n");
}
if ($addSc) {
    $defs = [];
    if (in_array('barcode_type', $addSc)) $defs[] = "ADD `barcode_type` TINYINT NOT NULL DEFAULT 0";
    if (in_array('barcode_value', $addSc)) $defs[] = "ADD `barcode_value` VARCHAR(64) NOT NULL DEFAULT ''";
    $db->statement("ALTER TABLE `" . $pre . $sc->getTable() . "` " . implode(', ', $defs));
}
if ($addMc) $db->statement("ALTER TABLE `" . $pre . $mc->getTable() . "` ADD `self_use_time` INT NULL DEFAULT NULL");
echo "欄を足しました\n";
file_put_contents($base . $libPath, $libSrc);
file_put_contents($base . $ctlPath, $ctlSrc);
foreach ($changes as $f => $src) file_put_contents($base . $f, $src);

$ok = true;
foreach (array_merge([$libPath, $ctlPath], array_keys($changes)) as $f) {
    $out = []; $code = 1;
    exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $out, $code);
    echo implode("\n", $out), "\n";
    if ($code !== 0) $ok = false;
}
if (!$ok) {
    foreach ($changes as $f => $src) copy($backup . str_replace('/', '__', $f), $base . $f);
    unlink($base . $libPath);
    unlink($base . $ctlPath);
    exit("文法エラーがあったので、ファイルを元に戻しました(足した欄はそのままですが、使われません)。\n");
}
$ref = $base . 'app/Http/Controllers/Api/Member/CouponController.php';
foreach ([$libPath, $ctlPath] as $p) { @chown($base . $p, fileowner($ref)); @chgrp($base . $p, filegroup($ref)); }
if ($routeCache) {
    $out = [];
    exec('cd ' . escapeshellarg($base) . ' && php artisan route:cache 2>&1', $out);
    echo implode("\n", $out), "\n";
}
// 確かめ:入口ができたか
$out = [];
exec('cd ' . escapeshellarg($base) . ' && php artisan route:list 2>&1 | grep self_use', $out);
echo "入口の確かめ:" . (implode(' ', $out) ?: '見つかりません(確認が必要)') . "\n";
// 確かめ:番号のチェック
echo "番号の確かめ:" . json_encode(App\Libs\CsPosBarcode::normalize(1, '491234567890'), JSON_UNESCAPED_UNICODE) . "\n";
echo "完了しました。バックアップ: $backup\n";
