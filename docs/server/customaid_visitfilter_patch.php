<?php
// [カスタムエイド 2026-10-21] 一斉配信の絞り込み「来店日」「来店回数」を、実際の来店の記録(会員証の読み込み・1日1件)で数える
//   今までの last_shop_time・shop_count は、予約が入ったときだけ更新される(来店では更新されない)ため、
//   予約のないお店では使えず、キャンセルした予約も回数に入っていた。
//   1. 一斉配信の表(web_timing_message)に欄を足す:come_from・come_to(期間で選ぶ、日付の 0 時)/absent_days(しばらく来ていない、日数)/visit_min・visit_max(来店回数)
//   2. 新しい部品 app/Libs/CsMemberFilter.php:来店の記録の表から、条件に合うお客様だけに絞る
//   3. 入力のチェック(TimingMessageValidator):新しい条件を受け取り、届く人数も同じ条件で数える
//   4. 送るしくみ(TimingMessageJob):同じ条件で絞る(予約配信は、送る日の時点で数える)
//   5. 詳しく(TimingMessageController の show):新しい条件を返す(編集のとき用)
//   今までの条件(shop_time・shop_count)は、古いアプリから送られたときのためにそのまま残す
//
// 使い方:
//   php -d disable_functions= /root/customaid_visitfilter_patch.php        … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/customaid_visitfilter_patch.php apply  … 実際に変更する(先にバックアップを取る)

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_visitfilter_20261021/';
$tag = '[カスタムエイド 2026-10-21]';
$apply = (($argv[1] ?? '') === 'apply');
$libPath = 'app/Libs/CsMemberFilter.php';

function looseRe($snippet)
{
    $parts = preg_split('/\s+/', trim($snippet));
    return '/' . implode('\s*', array_map(function ($p) { return preg_quote($p, '/'); }, $parts)) . '/u';
}

require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (file_exists($base . $libPath)) exit("$libPath がすでにあります。何も変更していません。\n");

// --- 0. 来店の記録とお客様のつながりを確かめる(来店の記録の mid = お客様の表の id) ---
$come = new App\Models\ComeLogModel();
$mem = new App\Models\ShopMemberModel();
$ct = $come->getTable();
$mt = $mem->getTable();
$db = $come->getConnection();
$total = $db->table($ct)->count();
$hit = $db->table($ct . ' as c')->join($mt . ' as m', function ($j) { $j->on('m.id', '=', 'c.mid')->on('m.sid', '=', 'c.sid'); })->count();
echo "来店の記録 $total 件のうち、お客様の表(id と sid)につながる件数: $hit 件\n";
$bad = $db->table($ct . ' as c')->join($mt . ' as m', 'm.id', '=', 'c.mid')->whereColumn('m.sid', '<>', 'c.sid')->count(); echo "別のお店のお客様につながる件数: $bad 件(0 なら問題なし。つながらない分は、削除されたお客様の記録)\n"; if ($bad > 0) exit("来店の記録とお客様の表のつながりが想定と違います。何も変更していません。\n");

// --- 1. 欄 ---
$tm = new App\Models\TimingMessageModel();
$tt = $tm->getTable();
$cols = array_map(function ($c) { return $c->Field; }, $db->select("SHOW COLUMNS FROM `" . $db->getTablePrefix() . $tt . "`"));
$addCols = array_values(array_diff(['come_from', 'come_to', 'absent_days', 'visit_min', 'visit_max'], $cols));
echo $addCols ? ("① $tt に欄を足します: " . implode(', ', $addCols) . "\n") : "① 欄はもうあります\n";

$changes = [];

// --- 3. 入力のチェック ---
$f = 'app/Validators/TimingMessageValidator.php';
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
echo "③ $f:新しい条件を受け取り、届く人数も同じ条件で数える\n";

// --- 4. 送るしくみ ---
$f = 'app/Jobs/TimingMessageJob.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, 'CsMemberFilter') !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
$old = <<<'PHP'
            if($timing_message->shop_count){
                $model = $model->where('shop_count', $timing_message->shop_count_desc == 1 ? '<=' : '>=', $timing_message->shop_count);
            }
PHP;
$new = <<<PHP
            if(\$timing_message->shop_count){
                \$model = \$model->where('shop_count', \$timing_message->shop_count_desc == 1 ? '<=' : '>=', \$timing_message->shop_count);
            }

            // $tag 来店日・来店回数は、実際の来店の記録で絞る(送る日の時点で数える)
            \$model = \App\Libs\CsMemberFilter::apply(\$model, \$timing_message->sid, [
                'come_from' => \$timing_message->come_from,
                'come_to' => \$timing_message->come_to,
                'absent_days' => \$timing_message->absent_days,
                'visit_min' => \$timing_message->visit_min,
                'visit_max' => \$timing_message->visit_max,
            ]);
PHP;
$re = looseRe($old);
if (preg_match_all($re, $s) !== 1) exit("$f の直す場所が見つかりません(または2つ以上)。何も変更していません。\n");
$changes[$f] = preg_replace_callback($re, function () use ($new) { return trim($new); }, $s, 1);
echo "④ $f:同じ条件で絞って送る\n";

// --- 5. 詳しく ---
$f = 'app/Http/Controllers/Api/Shop/TimingMessageController.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, "'absent_days'") !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
$re = '/([ \t]*)' . preg_quote("'shop_count_desc' => \$timing_message->shop_count_desc,", '/') . '\n/u';
if (preg_match_all($re, $s) !== 1) exit("$f の直す場所が見つかりません。何も変更していません。\n");
$changes[$f] = preg_replace_callback($re, function ($m) use ($tag) {
    $i = $m[1];
    return $m[0]
        . "$i// $tag 来店日・来店回数(実際の来店の記録で絞る)\n"
        . "$i'come_from' => \$timing_message->come_from ? date('Y-m-d', \$timing_message->come_from) : '',\n"
        . "$i'come_to' => \$timing_message->come_to ? date('Y-m-d', \$timing_message->come_to) : '',\n"
        . "$i'absent_days' => (int) \$timing_message->absent_days,\n"
        . "$i'visit_min' => (int) \$timing_message->visit_min,\n"
        . "$i'visit_max' => (int) \$timing_message->visit_max,\n";
}, $s, 1);
echo "⑤ $f:新しい条件を返す\n";

// --- 2. 部品 ---
$lib = <<<'PHP'
<?php

namespace App\Libs;

use App\Models\ComeLogModel;

/**
 * [カスタムエイド 2026-10-21] 一斉配信の絞り込み:実際の来店の記録(web_come_log・お客様ごとに1日1件、date はその日の 0 時)で絞る
 *   come_from・come_to … この期間に1回でも来店したお客様(日付の 0 時。今日だけも選べる)
 *   absent_days        … 最後の来店から○日以上たったお客様(1回以上来店したことのある方)
 *   visit_min・visit_max … これまでの来店回数(来店した日数)。0 は指定なし
 */
class CsMemberFilter
{
    // 管理App から送られた値を、保存する形にする。おかしな期間なら false
    public static function fromRequest($request)
    {
        $f = ['come_from' => null, 'come_to' => null, 'absent_days' => null, 'visit_min' => null, 'visit_max' => null];
        if ($request->come_from || $request->come_to) {
            $from = strtotime((string) $request->come_from);
            $to = strtotime((string) ($request->come_to ?: $request->come_from));
            if (!$from || !$to || $from > $to) {
                return false;
            }
            $f['come_from'] = strtotime(date('Y-m-d', $from));
            $f['come_to'] = strtotime(date('Y-m-d', $to));
        }
        if ((int) $request->absent_days > 0) {
            $f['absent_days'] = (int) $request->absent_days;
        }
        if ((int) $request->visit_min > 0) {
            $f['visit_min'] = (int) $request->visit_min;
        }
        if ((int) $request->visit_max > 0) {
            $f['visit_max'] = (int) $request->visit_max;
        }
        return $f;
    }

    // お客様の表の絞り込み($model)に、来店の条件を足す
    public static function apply($model, $sid, $f)
    {
        $t = (new ComeLogModel())->getTable();
        $from = (int) ($f['come_from'] ?? 0);
        $to = (int) ($f['come_to'] ?? 0);
        $absent = (int) ($f['absent_days'] ?? 0);
        $vmin = (int) ($f['visit_min'] ?? 0);
        $vmax = (int) ($f['visit_max'] ?? 0);

        if ($from && $to) {
            $model = $model->whereIn('id', function ($q) use ($t, $sid, $from, $to) {
                $q->select('mid')->from($t)->where('sid', $sid)->where('date', '>=', $from)->where('date', '<=', $to);
            });
        }
        if ($absent > 0) {
            $cutoff = strtotime('today') - $absent * 86400;
            $model = $model->whereIn('id', function ($q) use ($t, $sid, $cutoff) {
                $q->select('mid')->from($t)->where('sid', $sid)->groupBy('mid')->havingRaw('MAX(`date`) <= ?', [$cutoff]);
            });
        }
        if ($vmin > 0 || $vmax > 0) {
            $model = $model->whereIn('id', function ($q) use ($t, $sid, $vmin, $vmax) {
                $q->select('mid')->from($t)->where('sid', $sid)->groupBy('mid');
                if ($vmin > 0) {
                    $q->havingRaw('COUNT(*) >= ?', [$vmin]);
                }
                if ($vmax > 0) {
                    $q->havingRaw('COUNT(*) <= ?', [$vmax]);
                }
            });
        }
        return $model;
    }
}
PHP;

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
file_put_contents($base . $libPath, $lib);
foreach ($changes as $f => $src) file_put_contents($base . $f, $src);

$ok = true;
foreach (array_merge([$libPath], array_keys($changes)) as $f) {
    $out = []; $code = 1;
    exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $out, $code);
    echo implode("\n", $out), "\n";
    if ($code !== 0) $ok = false;
}
if (!$ok) {
    foreach ($changes as $f => $src) copy($backup . str_replace('/', '__', $f), $base . $f);
    unlink($base . $libPath);
    exit("文法エラーがあったので、ファイルを元に戻しました(足した欄はそのままですが、使われません)。\n");
}
$ref = $base . 'app/Jobs/TimingMessageJob.php';
@chown($base . $libPath, fileowner($ref));
@chgrp($base . $libPath, filegroup($ref));

// 動作の確かめ:このお店で「今日来店した人」「来店回数1回以上の人」の数
$sid = (int) $db->table($ct)->orderBy('id', 'desc')->value('sid');
if ($sid) {
    $today = strtotime('today');
    $a = App\Libs\CsMemberFilter::apply(App\Models\ShopMemberModel::where('sid', $sid), $sid, ['come_from' => $today, 'come_to' => $today])->count();
    $b = App\Libs\CsMemberFilter::apply(App\Models\ShopMemberModel::where('sid', $sid), $sid, ['visit_min' => 1])->count();
    echo "確かめ(店舗 $sid):今日来店した人 $a 人/来店1回以上の人 $b 人\n";
}
echo "完了しました。バックアップ: $backup\n";
