<?php
// [カスタムエイド 2026-11-03] ポイントカード:他社のバーコードを表示する
//   1. 欄を足す:web_shop_card に cs_ext_bc_type(0 なし/1 JAN/2 CODE128)、web_shop_member に cs_ext_bc_value(お客様ごとの番号)
//   2. 部品 app/Libs/CsExtBarcode.php(番号の確かめ・画像)
//   3. ShopCardValidator:作るときだけ種類を保存。編集では変えられない(他社のカードは is_point を 0 のまま)
//   4. Shop/CardController(show):直さない(カードを丸ごと返しているので、足した欄もそのまま返る)
//   5. ShopMemberService(getMemberDetail):カードの情報に種類・番号・画像を入れる(管理App のメンバー情報と Card-San App のマイカードの両方で使う)
//   6. Shop/MemberController(show・update):メンバー情報で種類・番号を返す/メンバー編集で番号を保存する
//   番号はお客様ごと。カードを選び直しても消さない。他社のカードでないときは返さない(表示しない)
// 使い方(このファイル1つを /root/ に置く):
//   php -d disable_functions= /root/extbc_patch.php          … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/extbc_patch.php apply    … バックアップを取ってから変更する
//   php -d disable_functions= /root/extbc_patch.php undo     … バックアップから元に戻す(足した欄はそのまま。使われなくなるだけ)

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_extbc_20261103/';
$tag = '[カスタムエイド 2026-11-03]';
$mode = $argv[1] ?? 'check';
$libPath = 'app/Libs/CsExtBarcode.php';
$files = ['app/Validators/ShopCardValidator.php', 'app/Services/ShopMemberService.php', 'app/Http/Controllers/Api/Shop/MemberController.php'];

if ($mode === 'undo') {
    if (!is_dir($backup)) exit("バックアップがありません($backup)。何もしていません。\n");
    foreach ($files as $f) { $b = $backup . str_replace('/', '__', $f); if (is_file($b)) { copy($b, $base . $f); echo "元に戻しました: $f\n"; } }
    if (is_file($base . $libPath)) { unlink($base . $libPath); echo "消しました: $libPath\n"; }
    exit("元に戻しました(足した欄 cs_ext_bc_type・cs_ext_bc_value はそのまま残っていますが、使われません)。\n");
}

require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ng = function ($m) { exit("NG $m\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
if (file_exists($base . $libPath)) $ng("$libPath がすでにあります(修正済みかもしれません)");
foreach (['App\Models\ShopCardModel', 'App\Models\ShopMemberModel', 'App\Libs\CsPosBarcode', 'Picqer\Barcode\BarcodeGeneratorPNG'] as $c) if (!class_exists($c)) $ng("$c が見つかりません");

// --- 1. 欄 ---
$cm = new App\Models\ShopCardModel(); $mm = new App\Models\ShopMemberModel();
$db = $cm->getConnection(); $pre = $db->getTablePrefix();
$colsOf = function ($t) use ($db, $pre) { return array_map(function ($c) { return $c->Field; }, $db->select("SHOW COLUMNS FROM `" . $pre . $t . "`")); };
$addC = !in_array('cs_ext_bc_type', $colsOf($cm->getTable()));
$addM = !in_array('cs_ext_bc_value', $colsOf($mm->getTable()));
echo "① 欄:" . $pre . $cm->getTable() . ".cs_ext_bc_type " . ($addC ? '足す' : 'もうある') . "/" . $pre . $mm->getTable() . ".cs_ext_bc_value " . ($addM ? '足す' : 'もうある') . "\n";
// 保存できる欄の決まり(fillable)があるときは足す必要がある
foreach ([[$cm, 'cs_ext_bc_type'], [$mm, 'cs_ext_bc_value']] as $x) {
    $fill = $x[0]->getFillable(); $g = $x[0]->getGuarded();
    if ($fill && !in_array($x[1], $fill)) $ng(get_class($x[0]) . " に fillable の決まりがあります(Claude が直し方を変えます)");
    if (!$fill && $g !== [] && $g !== ['*'] && in_array($x[1], $g)) $ng(get_class($x[0]) . " の guarded に入っています");
    if (!$fill && $g === ['*']) $ng(get_class($x[0]) . " は保存できる欄が決まっていません(guarded *)");
}
echo "OK 保存できる欄の決まり(fillable/guarded)は問題なし\n";

$changes = [];
$read = function ($f) use ($base) {
    $s = file_get_contents($base . $f);
    if ($s === false) exit("NG 読み込めません: $f\n");
    if (strpos($s, 'cs_ext_bc') !== false) exit("NG すでに修正済みです($f)。何も変更していません。\n");
    return $s;
};
$one = function ($f, $re, $s) use ($ng) { if (preg_match_all($re, $s) !== 1) $ng("$f の直す場所が見つかりません(または2つ以上)"); };

// --- 3. Validator ---
$f = 'app/Validators/ShopCardValidator.php'; $s = $read($f);
$re = '/\n([ \t]*)\$data\[\'is_remark\'\] = \$request->is_remark \?\? 0;\n/u';
$one($f, $re, $s);
$s = preg_replace_callback($re, function ($m) use ($tag) {
    $i = $m[1];
    return "\n$i// $tag 他社のバーコードを表示する(ポイントカードだけ)。作るときだけ選べて、編集では変えられない\n"
        . "{$i}if(!in_array(\$data['type'], [1, 2])){\n"
        . "$i    if(\$shop_card === null){\n"
        . "$i        \$data['cs_ext_bc_type'] = (\$request->is_point != 1 && in_array((int) \$request->cs_ext_bc_type, [1, 2])) ? (int) \$request->cs_ext_bc_type : 0;\n"
        . "$i    }elseif((int) (\$shop_card->cs_ext_bc_type ?? 0) > 0){\n"
        . "$i        \$data['is_point'] = 0;\n"
        . "$i    }\n"
        . "$i}\n" . substr($m[0], 1);
}, $s, 1);
$changes[$f] = $s; echo "③ $f:作るときだけ種類を保存/編集では変えない\n";

// --- 4. CardController show:カードを丸ごと返しているので、足した欄 cs_ext_bc_type もそのまま返る(直さない) ---
echo "④ app/Http/Controllers/Api/Shop/CardController.php:直さない(カードを丸ごと返しているので、種類も一緒に返る)\n";

// --- 5. ShopMemberService ---
$f = 'app/Services/ShopMemberService.php'; $s = $read($f);
$re = '/([ \t]*)\'is_point\' => \(\$member->card->is_point \?\? \'\'\),\n/u';
$one($f, $re, $s);
$s = preg_replace_callback($re, function ($m) use ($tag) {
    return $m[0] . $m[1] . "// $tag 他社のバーコード(他社のカードのときだけ。番号はお客様ごと)\n"
        . $m[1] . "'cs_ext_bc_type' => (int) (\$member->card->cs_ext_bc_type ?? 0),\n"
        . $m[1] . "'cs_ext_bc_value' => \\App\\Libs\\CsExtBarcode::value(\$member),\n"
        . $m[1] . "'cs_ext_bc_image' => \\App\\Libs\\CsExtBarcode::image(\$member),\n";
}, $s, 1);
$changes[$f] = $s; echo "⑤ $f:カードの情報に種類・番号・画像\n";

// --- 6. Shop MemberController ---
$f = 'app/Http/Controllers/Api/Shop/MemberController.php'; $s = $read($f);
$re = '/([ \t]*)\'is_point\' => \$member->card->is_point \?\? \'\',\n/u';
$one($f, $re, $s);
$s = preg_replace_callback($re, function ($m) use ($tag) {
    return $m[0] . $m[1] . "'cs_ext_bc_type' => (int) (\$member->card->cs_ext_bc_type ?? 0), // $tag 他社のバーコード\n"
        . $m[1] . "'cs_ext_bc_value' => \\App\\Libs\\CsExtBarcode::value(\$member),\n";
}, $s, 1);
$re = '/([ \t]*)\'remark\' => \$request->remark \?\? \'\'\n([ \t]*)\];\n/u';
$one($f, $re, $s);
$s = preg_replace_callback($re, function ($m) use ($tag) {
    $i = $m[2];
    return $m[0]
        . "$i// $tag 他社のバーコード番号(メンバー編集)。送られてきたときだけ。他社のカードのときだけ保存\n"
        . "{$i}if(\$request->has('cs_ext_bc_value') && (int) (\$member->card->cs_ext_bc_type ?? 0) > 0){\n"
        . "$i    \$cs_v = trim((string) \$request->cs_ext_bc_value);\n"
        . "$i    if(\$cs_v === ''){\n"
        . "$i        \$data['cs_ext_bc_value'] = '';\n"
        . "$i    }else{\n"
        . "$i        \$cs_bc = \\App\\Libs\\CsPosBarcode::normalize(\$member->card->cs_ext_bc_type, \$cs_v);\n"
        . "$i        if(is_string(\$cs_bc)){\n"
        . "$i            return responseToJson(400, \$cs_bc);\n"
        . "$i        }\n"
        . "$i        \$data['cs_ext_bc_value'] = \$cs_bc['value'];\n"
        . "$i    }\n"
        . "$i}\n";
}, $s, 1);
$changes[$f] = $s; echo "⑥ $f:メンバー情報で返す/メンバー編集で保存\n";

$lib = <<<'PHPLIB'
<?php

namespace App\Libs;

use Illuminate\Support\Facades\Storage;

/**
 * [カスタムエイド 2026-11-03] ポイントカード:他社のバーコードを表示する
 *  ・web_shop_card.cs_ext_bc_type:0 = なし/1 = JAN(13桁・8桁)/2 = CODE128(作るときだけ選べる)
 *  ・web_shop_member.cs_ext_bc_value:お客様ごとの番号(お店がメンバー編集で入れる)。カードを選び直しても消さない
 *  ・番号の確かめはクーポンと同じ CsPosBarcode::normalize
 */
class CsExtBarcode
{
    // 他社のカードで、番号が入っているときだけ番号を返す
    public static function value($member)
    {
        $t = (int) ($member->card->cs_ext_bc_type ?? 0);
        if ($t === 0) return '';
        return (string) ($member->cs_ext_bc_value ?? '');
    }

    // バーコードの画像(番号が変わったら作り直す)
    public static function image($member)
    {
        $t = (int) ($member->card->cs_ext_bc_type ?? 0);
        $v = self::value($member);
        if ($t === 0 || $v === '') return '';
        try {
            $bar = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $type = ($t == 1) ? (strlen($v) == 8 ? $bar::TYPE_EAN_8 : $bar::TYPE_EAN_13) : $bar::TYPE_CODE_128;
            $file = 'ext_member/m' . $member->id . '-' . substr(md5($t . '|' . $v), 0, 12) . '.png';
            $path = '/storage/barcode/' . $file;
            if (!file_exists(public_path() . $path)) {
                Storage::put('barcode/' . $file, $bar->getBarcode($v, $type, 3, 120));
            }
            return requestUrl() . $path;
        } catch (\Throwable $e) {
            return '';
        }
    }
}
PHPLIB;

if ($mode !== 'apply') { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }

if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($files as $f) if (!copy($base . $f, $backup . str_replace('/', '__', $f))) exit("バックアップに失敗しました: $f\n何も変更していません。\n");
if ($addC) $db->statement("ALTER TABLE `" . $pre . $cm->getTable() . "` ADD `cs_ext_bc_type` TINYINT NOT NULL DEFAULT 0");
if ($addM) $db->statement("ALTER TABLE `" . $pre . $mm->getTable() . "` ADD `cs_ext_bc_value` VARCHAR(64) NOT NULL DEFAULT ''");
echo "欄を足しました\n";
file_put_contents($base . $libPath, $lib);
foreach ($changes as $f => $src) file_put_contents($base . $f, $src);
$ok = true;
foreach (array_merge([$libPath], $files) as $f) {
    $out = []; $code = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $out, $code);
    echo ($code === 0 ? 'OK ' : 'NG ') . "文法 $f\n"; if ($code !== 0) { $ok = false; echo implode("\n", $out) . "\n"; }
}
if (!$ok) {
    foreach ($files as $f) copy($backup . str_replace('/', '__', $f), $base . $f);
    unlink($base . $libPath);
    exit("文法エラーがあったので、ファイルを元に戻しました(足した欄はそのままですが、使われません)。\n");
}
$ref = $base . $files[0];
@chown($base . $libPath, fileowner($ref)); @chgrp($base . $libPath, filegroup($ref));
echo "番号の確かめ(12桁なら最後の1桁を付ける):" . json_encode(App\Libs\CsPosBarcode::normalize(1, '491234567890'), JSON_UNESCAPED_UNICODE) . "\n";
echo "\n完了しました。バックアップ: $backup\n元に戻すとき: php -d disable_functions= /root/extbc_patch.php undo\n";
