<?php
// [カスタムエイド 2026-10-21] 一斉配信の配信数
//   1. TimingMessageValidator:口コミボタンを付けると配信数が2倍になっていたのを、人数どおり(1人 = 1通)に直す
//   2. TimingMessageController の index・show:配信数(order_send_count)も返す
//      ・この修正より前に口コミボタン付きで申し込んだ分は2倍で保存されているので、半分にして返す(保存されている数は変えない)
$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_sendcount_20261021/';
$tag = '[カスタムエイド 2026-10-21] 配信数';
$apply = (($argv[1] ?? '') === 'apply');
$cut = time();
$changes = [];

$f = 'app/Validators/TimingMessageValidator.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
$old = "\$data['order_send_count'] = \$model->count() * (\$request->is_comment_button == 1 ? 2 : 1);";
if (substr_count($s, $old) !== 1) exit("$f に 2倍にしている行が1つだけありません。何も変更していません。\n");
$changes[$f] = str_replace($old, "\$data['order_send_count'] = \$model->count(); // $tag:口コミボタンを付けても2倍にしない(1人 = 1通)", $s);
echo "① $f:口コミボタンで2倍にしない\n";

$f = 'app/Http/Controllers/Api/Shop/TimingMessageController.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, "'order_send_count' =>") !== false) exit("すでに配信数を返しています($f)。何も変更していません。\n");
foreach (['$v', '$timing_message'] as $x) {
    $ct = '(is_numeric(' . $x . '->create_time) ? ' . $x . '->create_time : strtotime(' . $x . '->create_time))';
    $line = "'order_send_count' => (int) ((" . $x . '->is_comment_button == 1 && ' . $x . '->create_time && ' . $ct . ' < ' . $cut . ') ? round(' . $x . '->order_send_count / 2) : ' . $x . "->order_send_count), // $tag(前の2倍の分は半分に)";
    $re = "/([ \\t]*)'images' => json_decode\\(" . preg_quote($x . '->images', '/') . "[^\\n]*\\n/u";
    if (preg_match_all($re, $s) !== 1) exit("$f に {$x}->images の行が1つだけありません。何も変更していません。\n");
    $s = preg_replace_callback($re, function ($m) use ($line) { return $m[0] . $m[1] . $line . "\n"; }, $s, 1);
}
$changes[$f] = $s;
echo "② $f:一覧・詳しくで配信数を返す\n";

if (!$apply) { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($changes as $f => $src) if (!copy($base . $f, $backup . str_replace('/', '__', $f))) exit("バックアップに失敗しました: $f\n何も変更していません。\n");
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
