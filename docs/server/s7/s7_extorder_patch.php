<?php
// [カスタムエイド 2026-11-03] 段階7:外部の予約(ホットペッパーなど)。時間を押さえるだけで、お客様への連絡はしない
//   1. 欄を足す:web_order.cs_ext(1 = 外部の予約)・cs_ext_src(予約元)・cs_ext_menu(メニュー。自由に入力)
//   2. OrderValidator:cs_ext = 1 のとき、予約元を必須にし、お客様名(任意)・メニュー(任意)を保存。
//      電話番号・会員・メニュー(smid)は空にする → 送る所(会員→アプリ/メール/電話→SMS)のどれにも当たらないので、
//      メール・SMS・アプリのお知らせは送らず、SMS の通数も数えない。予約通知(smid > 0 のときだけ作る)にも出ない
//   3. Shop/OrderController:予約表(index)と予約の詳しく(show)で cs_ext・cs_ext_src・cs_ext_menu を返す
// 使い方(このファイル1つを /root/ に置く):
//   php -d disable_functions= /root/s7_extorder_patch.php          … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/s7_extorder_patch.php apply    … バックアップを取ってから変更する
//   php -d disable_functions= /root/s7_extorder_patch.php undo     … 元に戻す(足した欄はそのまま。使われなくなるだけ)

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_s7extorder_20261103/';
$mode = $argv[1] ?? 'check';
$tag = '[カスタムエイド 2026-11-03]';
$fv = 'app/Validators/OrderValidator.php';
$fc = 'app/Http/Controllers/Api/Shop/OrderController.php';

if ($mode === 'undo') {
    if (!is_dir($backup)) exit("バックアップがありません。何もしていません。\n");
    copy($backup . 'OrderValidator.php', $base . $fv);
    copy($backup . 'OrderController.php', $base . $fc);
    exit("元に戻しました(足した欄はそのまま残っていますが、使われません)。\n");
}

require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };

$m = new App\Models\OrderModel();
$db = $m->getConnection(); $t = $db->getTablePrefix() . $m->getTable();
$cols = array_map(function ($c) { return $c->Field; }, $db->select("SHOW COLUMNS FROM `{$t}`"));
$add = array_values(array_diff(['cs_ext', 'cs_ext_src', 'cs_ext_menu'], $cols));
echo "① 欄:{$t} に " . ($add ? implode(', ', $add) . ' を足す' : '(もうある)') . "\n";
$fill = $m->getFillable(); $g = $m->getGuarded();
if ($fill) $ng('OrderModel に fillable の決まりがあります(Claude が直し方を変えます)');
if ($g === ['*']) $ng('OrderModel は guarded *');
echo "OK 保存できる欄の決まりは問題なし\n";

// --- 2. Validator ---
$sv = file_get_contents($base . $fv);
if (strpos($sv, 'cs_ext') !== false) $ng('OrderValidator はすでに修正済みです');
$key = "        \$data['member_id'] = \$request->member_id ?? '';\n";
if (substr_count($sv, $key) !== 1) $ng('OrderValidator の直す場所(member_id の行)が見つかりません');
$ins = "\n        // {$tag} 外部の予約(ホットペッパーなど)。予約元は必須、お客様名・メニューは任意。\n"
    . "        //   電話番号・会員・メニューは空にして、お客様へのお知らせ(アプリ・メール・SMS)が送られないようにする\n"
    . "        if((int) \$request->cs_ext === 1){\n"
    . "            \$cs_src = trim(mb_substr((string) (\$request->cs_ext_src ?? ''), 0, 50));\n"
    . "            if(\$cs_src === ''){\n"
    . "                return ['success' => false, 'message' => '予約元を選んでください'];\n"
    . "            }\n"
    . "            if(\$request->smid && (int) \$request->smid !== 0){\n"
    . "                return ['success' => false, 'message' => '外部の予約では、メニューは文字で入れてください'];\n"
    . "            }\n"
    . "            \$data['cs_ext'] = 1;\n"
    . "            \$data['cs_ext_src'] = \$cs_src;\n"
    . "            \$data['cs_ext_menu'] = trim(mb_substr((string) (\$request->cs_ext_menu ?? ''), 0, 100));\n"
    . "            \$data['name'] = trim(mb_substr((string) (\$request->name ?? ''), 0, 50));\n"
    . "            \$data['phone'] = '';\n"
    . "            \$data['mid'] = 0;\n"
    . "            \$data['member_id'] = '';\n"
    . "            \$data['email'] = '';\n"
    . "        }\n";
$sv2 = str_replace($key, $key . $ins, $sv);
echo "② {$fv}:外部の予約の確かめ・保存\n";

// --- 3. Controller index・show ---
$sc = file_get_contents($base . $fc);
if (strpos($sc, "'cs_ext'") !== false) $ng('OrderController はすでに修正済みです');
$extLine = function ($ind, $v) use ($tag) {
    return "{$ind}'cs_ext' => (int) ({$v}->cs_ext ?? 0), // {$tag} 外部の予約\n"
        . "{$ind}'cs_ext_src' => (string) ({$v}->cs_ext_src ?? ''),\n"
        . "{$ind}'cs_ext_menu' => (string) ({$v}->cs_ext_menu ?? ''),\n";
};
$p = strpos($sc, 'public function index('); $q = strpos($sc, 'public function ', $p + 10);
if ($p === false || $q === false) $ng('index が見つかりません');
$idx = substr($sc, $p, $q - $p);
$n = preg_match_all('/\n([ \t]*)\'name\' => \$order->name,\n/', $idx);
echo "index の 'name' => \$order->name, の行:{$n} か所\n";
if ($n < 1) $ng('index の直す場所が見つかりません');
$idx2 = preg_replace_callback('/\n([ \t]*)\'name\' => \$order->name,\n/', function ($mm) use ($extLine) { return $mm[0] . $extLine($mm[1], '$order'); }, $idx);
$sc2 = substr($sc, 0, $p) . $idx2 . substr($sc, $q);
$re = '/\n([ \t]*)\'email\' => \(string\) \(\$order->email \?\? \'\'\),[^\n]*\n/';
if (preg_match_all($re, $sc2) !== 1) $ng('show の email の行が見つかりません(または2つ以上)');
$sc2 = preg_replace_callback($re, function ($mm) use ($extLine) { return $mm[0] . $extLine($mm[1], '$order'); }, $sc2, 1);
echo "③ {$fc}:予約表(index)と詳しく(show)で外部の予約の情報を返す\n";

$tmp = sys_get_temp_dir() . '/cs_s7e_';
file_put_contents($tmp . 'v.php', $sv2); file_put_contents($tmp . 'c.php', $sc2);
foreach (['v.php', 'c.php'] as $x) { $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp . $x) . ' 2>&1', $o, $c); echo ($c === 0 ? 'OK ' : 'NG ') . "直したあとの文法 {$x}\n"; if ($c !== 0) $ng(implode(' ', $o)); }

if ($mode !== 'apply') { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
copy($base . $fv, $backup . 'OrderValidator.php');
copy($base . $fc, $backup . 'OrderController.php');
$defs = [];
if (in_array('cs_ext', $add)) $defs[] = "ADD `cs_ext` TINYINT NOT NULL DEFAULT 0";
if (in_array('cs_ext_src', $add)) $defs[] = "ADD `cs_ext_src` VARCHAR(50) NOT NULL DEFAULT ''";
if (in_array('cs_ext_menu', $add)) $defs[] = "ADD `cs_ext_menu` VARCHAR(100) NOT NULL DEFAULT ''";
if ($defs) $db->statement("ALTER TABLE `{$t}` " . implode(', ', $defs));
echo "欄を足しました\n";
file_put_contents($base . $fv, $sv2);
file_put_contents($base . $fc, $sc2);
$ok = true;
foreach ([$fv, $fc] as $x) { $o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $x) . ' 2>&1', $o, $c); echo ($c === 0 ? 'OK ' : 'NG ') . "文法 {$x}\n"; if ($c !== 0) $ok = false; }
if (!$ok) { copy($backup . 'OrderValidator.php', $base . $fv); copy($backup . 'OrderController.php', $base . $fc); exit("文法エラーがあったので元に戻しました。\n"); }
echo "\n完了しました。バックアップ: {$backup}\n元に戻すとき: php -d disable_functions= /root/s7_extorder_patch.php undo\n";
