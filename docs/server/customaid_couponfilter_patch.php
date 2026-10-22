<?php
// [カスタムエイド 2026-10-21] クーポン配信の絞り込み「来店日」「来店回数」を、メッセージと同じく実際の来店の記録で数える
//   1. クーポンの表に欄を足す:come_from・come_to/absent_days/visit_min・visit_max
//   2. 入力のチェック(ShopCouponValidator):新しい条件を受け取り、届く人数も同じ条件で数える
//   3. 送るしくみ(CouponJob):同じ条件で絞る(予約配信は、送る日の時点で数える)
//   4. 詳しく(CouponController の show):新しい条件を返す(予約の編集用)
//   部品 app/Libs/CsMemberFilter.php は、メッセージの修正で入れたものをそのまま使う
//
// 使い方:
//   php -d disable_functions= /root/customaid_couponfilter_patch.php        … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/customaid_couponfilter_patch.php apply  … 実際に変更する(先にバックアップを取る)

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_couponfilter_20261021/';
$tag = '[カスタムエイド 2026-10-21]';
$apply = (($argv[1] ?? '') === 'apply');

function looseRe($snippet)
{
    $parts = preg_split('/\s+/', trim($snippet));
    return '/' . implode('\s*', array_map(function ($p) { return preg_quote($p, '/'); }, $parts)) . '/u';
}

require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (!file_exists($base . 'app/Libs/CsMemberFilter.php')) exit("app/Libs/CsMemberFilter.php がありません(メッセージの修正が先に必要です)。何も変更していません。\n");

// --- 1. 欄 ---
$cm = new App\Models\ShopCouponModel();
$db = $cm->getConnection();
$tt = $cm->getTable();
$cols = array_map(function ($c) { return $c->Field; }, $db->select("SHOW COLUMNS FROM `" . $db->getTablePrefix() . $tt . "`"));
if (!in_array('sid', $cols)) exit("$tt に sid の欄がありません。何も変更していません。\n");
$addCols = array_values(array_diff(['come_from', 'come_to', 'absent_days', 'visit_min', 'visit_max'], $cols));
echo $addCols ? ("① " . $db->getTablePrefix() . "$tt に欄を足します: " . implode(', ', $addCols) . "\n") : "① 欄はもうあります\n";

$changes = [];

// --- 2. 入力のチェック ---
$f = 'app/Validators/ShopCouponValidator.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, 'CsMemberFilter') !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
$old = <<<'PHP'
            if($request->shop_count && in_array($request->shop_count_desc, [1, 2])){
                $data['shop_count'] = $request->shop_count;
                $data['shop_count_desc'] = $request->shop_count_desc;
            }
PHP;
$new = <<<PHP
            if(\$request->shop_count && in_array(\$request->shop_count_desc, [1, 2])){
                \$data['shop_count'] = \$request->shop_count;
                \$data['shop_count_desc'] = \$request->shop_count_desc;
            }

            // $tag 来店日・来店回数は、実際の来店の記録で絞る(app/Libs/CsMemberFilter.php)
            \$cs_f = \App\Libs\CsMemberFilter::fromRequest(\$request);
            if(\$cs_f === false){
                return ['success' => false, 'message' => '来店日の期間を正しく選んでください'];
            }
            \$data = array_merge(\$data, \$cs_f);
            \$model = \App\Libs\CsMemberFilter::apply(\$model, auth('shop')->user()->sid, \$cs_f);
PHP;
$re = looseRe($old);
if (preg_match_all($re, $s) !== 1) exit("$f の直す場所が見つかりません(または2つ以上)。何も変更していません。\n");
$changes[$f] = preg_replace_callback($re, function () use ($new) { return trim($new); }, $s, 1);
echo "② $f:新しい条件を受け取り、届く人数も同じ条件で数える\n";

// --- 3. 送るしくみ ---
$f = 'app/Jobs/CouponJob.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, 'CsMemberFilter') !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
$old = <<<'PHP'
            if($coupon->shop_count){
                $model = $model->where('shop_count', $coupon->shop_count_desc == 1 ? '<=' : '>=', $coupon->shop_count);
            }
PHP;
$new = <<<PHP
            if(\$coupon->shop_count){
                \$model = \$model->where('shop_count', \$coupon->shop_count_desc == 1 ? '<=' : '>=', \$coupon->shop_count);
            }

            // $tag 来店日・来店回数は、実際の来店の記録で絞る(送る日の時点で数える)
            \$model = \App\Libs\CsMemberFilter::apply(\$model, \$coupon->sid, [
                'come_from' => \$coupon->come_from,
                'come_to' => \$coupon->come_to,
                'absent_days' => \$coupon->absent_days,
                'visit_min' => \$coupon->visit_min,
                'visit_max' => \$coupon->visit_max,
            ]);
PHP;
$re = looseRe($old);
if (preg_match_all($re, $s) !== 1) exit("$f の直す場所が見つかりません(または2つ以上)。何も変更していません。\n");
$changes[$f] = preg_replace_callback($re, function () use ($new) { return trim($new); }, $s, 1);
echo "③ $f:同じ条件で絞って送る\n";

// --- 4. 詳しく ---
$f = 'app/Http/Controllers/Api/Shop/CouponController.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, "'absent_days'") !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
$re = '/([ \t]*)' . preg_quote("'shop_count_desc' => \$coupon->shop_count_desc", '/') . '(,?)[ \t]*\n/u';
if (preg_match_all($re, $s) !== 1) exit("$f の直す場所が見つかりません。何も変更していません。\n");
$changes[$f] = preg_replace_callback($re, function ($m) use ($tag) {
    $i = $m[1];
    return $i . "'shop_count_desc' => \$coupon->shop_count_desc,\n"
        . "$i// $tag 来店日・来店回数(実際の来店の記録で絞る)\n"
        . "$i'come_from' => \$coupon->come_from ? date('Y-m-d', \$coupon->come_from) : '',\n"
        . "$i'come_to' => \$coupon->come_to ? date('Y-m-d', \$coupon->come_to) : '',\n"
        . "$i'absent_days' => (int) \$coupon->absent_days,\n"
        . "$i'visit_min' => (int) \$coupon->visit_min,\n"
        . "$i'visit_max' => (int) \$coupon->visit_max" . ($m[2] === ',' ? ',' : '') . "\n";
}, $s, 1);
echo "④ $f:新しい条件を返す\n";

if (!$apply) {
    echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n";
    exit;
}

if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($changes as $f => $src) {
    if (!copy($base . $f, $backup . str_replace('/', '__', $f))) exit("バックアップに失敗しました: $f\n何も変更していません。\n");
}
if ($addCols) {
    $defs = [];
    foreach ($addCols as $c) $defs[] = "ADD `$c` INT NULL DEFAULT NULL";
    $db->statement("ALTER TABLE `" . $db->getTablePrefix() . $tt . "` " . implode(', ', $defs));
    echo "欄を足しました\n";
}
foreach ($changes as $f => $src) file_put_contents($base . $f, $src);

$ok = true;
foreach (array_keys($changes) as $f) {
    $out = []; $code = 1;
    exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $out, $code);
    echo implode("\n", $out), "\n";
    if ($code !== 0) $ok = false;
}
if (!$ok) {
    foreach ($changes as $f => $src) copy($backup . str_replace('/', '__', $f), $base . $f);
    exit("文法エラーがあったので、ファイルを元に戻しました(足した欄はそのままですが、使われません)。\n");
}
echo "完了しました。バックアップ: $backup\n";
