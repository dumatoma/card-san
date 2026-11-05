<?php
// [カスタムエイド 2026-11-03] 段階7:お店が入れる予約(管理App の新規予約・予約の変更)にお客様のメールアドレスを保存する
//   送る所(メールがあればメール、なければ SMS)は 10/30 にできているので、保存だけ足す
//   ・新規予約(store):email が送られてきて、空でなければ確かめて保存
//   ・予約の変更(update):email が送られてきたときだけ保存(空なら消す)。送られてこないときは今のまま
// 使い方(このファイル1つを /root/ に置く):
//   php -d disable_functions= /root/s7_ordermail_patch.php          … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/s7_ordermail_patch.php apply    … バックアップを取ってから変更する
//   php -d disable_functions= /root/s7_ordermail_patch.php undo     … 元に戻す

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_s7ordermail_20261103/';
$mode = $argv[1] ?? 'check';
$f = 'app/Http/Controllers/Api/Shop/OrderController.php';
$tag = '[カスタムエイド 2026-11-03]';

if ($mode === 'undo') {
    if (!is_file($backup . 'OrderController.php')) exit("バックアップがありません。何もしていません。\n");
    copy($backup . 'OrderController.php', $base . $f);
    exit("元に戻しました。\n");
}

require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };

if (!class_exists('App\Libs\CsShopMail')) $ng('CsShopMail がありません');
$m = new App\Models\OrderModel();
$db = $m->getConnection();
$cols = array_map(function ($c) { return $c->Field; }, $db->select("SHOW COLUMNS FROM `" . $db->getTablePrefix() . $m->getTable() . "`"));
if (!in_array('email', $cols)) $ng($m->getTable() . ' に email の欄がありません');
echo "OK " . $m->getTable() . " に email の欄がある\n";
$fill = $m->getFillable(); $g = $m->getGuarded();
if ($fill && !in_array('email', $fill)) $ng('OrderModel の fillable に email がありません');
if (!$fill && $g === ['*']) $ng('OrderModel は guarded *');
echo "OK 保存できる欄の決まりは問題なし\n";

$s = file_get_contents($base . $f);
if (strpos($s, 'cs_order_email') !== false) $ng('すでに修正済みです');
$ins = function ($s, $fn, $isUpdate) use ($tag, $ng) {
    $p = strpos($s, 'public function ' . $fn . '(');
    if ($p === false) $ng("{$fn} が見つかりません");
    $key = "        \$data = \$res['data'];\n";
    $q = strpos($s, $key, $p);
    $next = strpos($s, 'public function ', $p + 10);
    if ($q === false || ($next !== false && $q > $next)) $ng("{$fn} の中に \$data = \$res['data']; が見つかりません");
    $add = "\n        // {$tag} お客様のメールアドレス(管理App で入れたとき)。あればメール、なければ SMS で届く\n"
        . "        if(\$request->has('email')){\n"
        . "            \$cs_order_email = \\App\\Libs\\CsShopMail::clean(\$request->input('email', ''));\n"
        . "            if(\$cs_order_email !== '' && !\\App\\Libs\\CsShopMail::valid(\$cs_order_email)){\n"
        . "                return responseToJson(400, 'メールアドレスの形が正しくありません');\n"
        . "            }\n"
        . ($isUpdate
            ? "            \$data['email'] = \$cs_order_email;\n"
            : "            if(\$cs_order_email !== ''){\n                \$data['email'] = \$cs_order_email;\n            }\n")
        . "        }\n";
    return substr($s, 0, $q + strlen($key)) . $add . substr($s, $q + strlen($key));
};
$s = $ins($s, 'store', false);
$s = $ins($s, 'update', true);
echo "OK 新規予約(store)・予約の変更(update)に足す場所がある\n";
$tmp = sys_get_temp_dir() . '/cs_s7o.php'; file_put_contents($tmp, $s);
$o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c);
echo ($c === 0 ? 'OK ' : 'NG ') . "直したあとの文法\n"; if ($c !== 0) $ng(implode(' ', $o));

if ($mode !== 'apply') { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
copy($base . $f, $backup . 'OrderController.php');
file_put_contents($base . $f, $s);
$o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c);
if ($c !== 0) { copy($backup . 'OrderController.php', $base . $f); exit("文法エラーがあったので元に戻しました。\n"); }
echo "OK 文法 {$f}\n";
echo "\n完了しました。バックアップ: {$backup}\n元に戻すとき: php -d disable_functions= /root/s7_ordermail_patch.php undo\n";
