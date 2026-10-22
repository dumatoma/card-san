<?php
// [カスタムエイド 2026-10-22] お知らせの日付:編集しても「最初に投稿した日」のまま(並び順・未読の数も投稿した日で)。編集した日は別に返す
//   今までは保存した日(update_time)で並べて表示していたため、編集すると新しい投稿のように上に来て、未読にもなっていた
//   Shop/NoticeController:並び順を投稿した日に、update_time の欄に投稿した日を入れる(今のアプリでも投稿した日が出る)、edit_time(編集した日 m/d)を足す
//   Member/NoticeController:未読の数・並び順・date を投稿した日に、edit_date(編集した日 m/d)を足す
//   お知らせの表には投稿した日の欄が無かったので、create_time を足す。今あるお知らせは update_time を入れる。新しく投稿したときに記録する
// 使い方:
//   php -d disable_functions= /root/customaid_noticedate_patch.php        … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/customaid_noticedate_patch.php apply  … 実際に変更する(先にバックアップを取る)

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_noticedate_20261022/';
$tag = '[カスタムエイド 2026-10-22]';
$apply = (($argv[1] ?? '') === 'apply');

require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$nm = new App\Models\NoticeModel();
$db = $nm->getConnection();
$types = [];
foreach ($db->select("SHOW COLUMNS FROM `" . $db->getTablePrefix() . $nm->getTable() . "`") as $c) $types[$c->Field] = strtolower($c->Type);
if (strpos($types['update_time'] ?? '', 'int') === false) exit("update_time が数字(秒)の欄ではありません。何も変更していません。\n");
$needCol = !isset($types['create_time']);
echo "お知らせ:全部 " . $db->table($nm->getTable())->count() . " 件。" . ($needCol ? "投稿した日の欄(create_time)を足し、今あるお知らせには最後に保存した日を入れます\n" : "投稿した日の欄はもうあります\n");

$ord = "orderByRaw('IF(create_time > 0, create_time, update_time) desc')";
$changes = [];
$one = function ($f, $s, $re, $cb) {
    if (preg_match_all($re, $s) !== 1) exit("$f の直す場所が見つかりません(または2つ以上): $re\n何も変更していません。\n");
    return preg_replace_callback($re, $cb, $s, 1);
};

// --- 管理App ---
$f = 'app/Http/Controllers/Api/Shop/NoticeController.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, $tag . ' お知らせの日付') !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
$s = $one($f, $s, '/->orderBy\(\'is_top\', \'desc\'\)->orderBy\(\'update_time\', \'desc\'\)->get\(\);/u', function ($m) use ($ord, $tag) {
    return "->orderBy('is_top', 'desc')->" . $ord . "->get(); // $tag お知らせの日付:投稿した日で並べる";
});
$s = $one($f, $s, '/([ \t]*)\'update_time\' => date\(\'Y-m-d H:i:s\', \$v->update_time\)(,?)[ \t]*\n/u', function ($m) use ($tag) {
    $i = $m[1];
    return $i . "// $tag お知らせの日付:投稿した日を出す(編集した日は edit_time)\n"
        . $i . "'update_time' => date('Y-m-d H:i:s', \$v->create_time ?: \$v->update_time),\n"
        . $i . "'edit_time' => (\$v->create_time && \$v->update_time - \$v->create_time > 60) ? date('n/j', \$v->update_time) : ''" . $m[2] . "\n";
});
$s = $one($f, $s, '/([ \t]*)NoticeModel::create\(\$data\);\n/u', function ($m) use ($tag) {
    $i = $m[1];
    return $i . "\$cs_notice = NoticeModel::create(\$data);\n"
        . $i . "\\DB::table(\$cs_notice->getTable())->where('id', \$cs_notice->id)->update(['create_time' => time()]); // $tag お知らせの日付:投稿した日を記録する\n";
});
$changes[$f] = $s;
echo "① $f:投稿したときに投稿した日を記録、投稿した日で並べて表示、編集した日を足す\n";

// --- Card-San App ---
$f = 'app/Http/Controllers/Api/Member/NoticeController.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, $tag . ' お知らせの日付') !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
$s = $one($f, $s, '/->where\(\'update_time\', \'>\', \$member->read_notice_time\)/u', function ($m) use ($tag) {
    return "->whereRaw('IF(create_time > 0, create_time, update_time) > ?', [(int) \$member->read_notice_time]) /* $tag お知らせの日付:編集しても未読にしない */";
});
$s = $one($f, $s, '/->orderBy\(\'update_time\', \'desc\'\)/u', function ($m) use ($ord, $tag) {
    return "->" . $ord . " /* $tag お知らせの日付:投稿した日で並べる */";
});
$s = $one($f, $s, '/([ \t]*)\'date\' => date\(\'y\/m\/d H:i\', \$notice->update_time\),[ \t]*\n/u', function ($m) use ($tag) {
    $i = $m[1];
    return $i . "// $tag お知らせの日付:投稿した日を出す(編集した日は edit_date)\n"
        . $i . "'date' => date('y/m/d H:i', \$notice->create_time ?: \$notice->update_time),\n"
        . $i . "'edit_date' => (\$notice->create_time && \$notice->update_time - \$notice->create_time > 60) ? date('n/j', \$notice->update_time) : '',\n";
});
$changes[$f] = $s;
echo "② $f:未読の数・並び順・日付を投稿した日に、編集した日を足す\n";

if (!$apply) { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($changes as $f => $src) if (!copy($base . $f, $backup . str_replace('/', '__', $f))) exit("バックアップに失敗しました: $f\n何も変更していません。\n");
if ($needCol) {
    $t = $db->getTablePrefix() . $nm->getTable();
    $db->statement("ALTER TABLE `$t` ADD `create_time` INT UNSIGNED NULL DEFAULT NULL AFTER `update_time`");
    $n = $db->update("UPDATE `$t` SET `create_time` = `update_time` WHERE `create_time` IS NULL");
    echo "投稿した日の欄を足して、$n 件に入れました\n";
}
foreach ($changes as $f => $src) file_put_contents($base . $f, $src);
$ok = true;
foreach (array_keys($changes) as $f) {
    $out = []; $code = 1;
    exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $out, $code);
    echo implode("\n", $out), "\n";
    if ($code !== 0) $ok = false;
}
if (!$ok) { foreach ($changes as $f => $src) copy($backup . str_replace('/', '__', $f), $base . $f); exit("文法エラーがあったので、元に戻しました。\n"); }
echo "完了しました。バックアップ: $backup\n";
