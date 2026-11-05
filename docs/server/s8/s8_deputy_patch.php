<?php
// [カスタムエイド 2026-11-03] 副管理者は、お金・契約が変わる操作(申し込み・支払い・プラン変更・周期変更・解約・継続・カード登録・購入を復元・追加購入)をできないようにする
//   見るだけの入口(vip の一覧・詳しく、vip_card)はそのまま(副管理者もご契約内容を見られるように)
//   今ある仕組み shop.privilege:1(管理者だけ)を、それぞれの入口に付ける(routes/api.php だけを直す)
// 使い方(このファイル1つを /root/ に置く):
//   php -d disable_functions= /root/s8_deputy_patch.php          … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/s8_deputy_patch.php apply    … バックアップを取ってから変更する
//   php -d disable_functions= /root/s8_deputy_patch.php undo     … 元に戻す

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_s8deputy_20261103/';
$mode = $argv[1] ?? 'check';
$f = 'routes/api.php';
$tag = '[カスタムエイド 2026-11-03]';
$clearRoutes = function () use ($base) {
    foreach (glob($base . 'bootstrap/cache/routes*.php') ?: [] as $rc) { $o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:cache 2>&1', $o); echo implode("\n", $o) . "\n"; return; }
};
if ($mode === 'undo') {
    if (!is_file($backup . 'api.php')) exit("バックアップがありません。何もしていません。\n");
    copy($backup . 'api.php', $base . $f); $clearRoutes();
    exit("元に戻しました。\n");
}

$P = "->middleware('shop.privilege:1')";
$map = [
    "            Route::post('/vip/pay', 'VipController@pay');"
        => "            Route::post('/vip/pay', 'VipController@pay'){$P}; // {$tag} 管理者だけ",
    "            Route::post('/cs/iap/restore', 'CsIapRestoreController@restore'); // [カスタムエイド 2026-11-03] 購入を復元"
        => "            Route::post('/cs/iap/restore', 'CsIapRestoreController@restore'){$P}; // [カスタムエイド 2026-11-03] 購入を復元(管理者だけ)",
    "            Route::post('/vip/{id}/resume', 'VipController@resume');"
        => "            Route::post('/vip/{id}/resume', 'VipController@resume'){$P}; // {$tag} 管理者だけ",
    "            Route::post('/vip_card/setup', 'VipController@cardSetup');"
        => "            Route::post('/vip_card/setup', 'VipController@cardSetup'){$P}; // {$tag} 管理者だけ",
    "            Route::apiResource('vip', 'VipController');"
        => "            // {$tag} 一覧・詳しくは副管理者も見られる。申し込み・変更・解約は管理者だけ\n"
         . "            Route::apiResource('vip', 'VipController')->only(['index', 'show']);\n"
         . "            Route::apiResource('vip', 'VipController')->except(['index', 'show']){$P};",
    "            Route::post('/vip_count/to_pay', 'VipCountController@toPay');"
        => "            Route::post('/vip_count/to_pay', 'VipCountController@toPay'){$P}; // {$tag} 追加購入は管理者だけ",
    "            Route::apiResource('vip_count', 'VipCountController');"
        => "            Route::apiResource('vip_count', 'VipCountController'){$P}; // {$tag} 管理者だけ",
];
$s = file_get_contents($base . $f);
if (strpos($s, "VipController@pay'){$P}") !== false) exit("NG すでに修正済みです。何も変更していません。\n");
foreach ($map as $old => $new) {
    $n = substr_count($s, $old);
    echo ($n === 1 ? 'OK ' : 'NG ') . "見つかった:" . trim(explode("\n", $old)[0]) . "\n";
    if ($n !== 1) exit("止めました。何も変更していません。この結果を Claude に送ってください。\n");
}
$s2 = str_replace(array_keys($map), array_values($map), $s);
$tmp = sys_get_temp_dir() . '/cs_s8d.php'; file_put_contents($tmp, $s2);
$o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c);
echo ($c === 0 ? 'OK ' : 'NG ') . "直したあとの文法\n"; if ($c !== 0) exit("止めました。何も変更していません。\n");
// ShopPrivilege の「1 = 管理者だけ」を確かめる
$mw = @file_get_contents($base . 'app/Http/Middleware/ShopPrivilege.php');
if ($mw === false || strpos($mw, "admin_type == 1") === false) exit("NG ShopPrivilege の作りが思っていたものと違います。何も変更していません。\n");
echo "OK shop.privilege:1 は管理者だけを通す\n";
if ($mode !== 'apply') { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
copy($base . $f, $backup . 'api.php');
file_put_contents($base . $f, $s2);
$o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c);
if ($c !== 0) { copy($backup . 'api.php', $base . $f); exit("文法エラーがあったので元に戻しました。\n"); }
echo "OK 文法 {$f}\n";
$clearRoutes();
$o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:list 2>&1 | grep -E "api/shop/(vip|vip_count|vip_card|cs/iap)"', $o);
echo "入口の確かめ(右の方に shop.privilege が付いていれば OK):\n" . implode("\n", $o) . "\n";
echo "\n完了しました。バックアップ: {$backup}\n元に戻すとき: php -d disable_functions= /root/s8_deputy_patch.php undo\n";
