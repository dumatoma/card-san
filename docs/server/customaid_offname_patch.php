<?php
// [カスタムエイド 2026-10-22] 予約の「お休み」「休憩・勤務時間外」を区別して記録する
//   メニューなし(smid=0)の予約は名前を保存しない作りだったため、名前が「お休み」「休憩・勤務時間外」のときだけ保存する
//   (予約表に「お休み」「休憩・勤務時間外」と表示するため。ほかの名前・お客さまの予約は今までどおり)
// 使い方:
//   php -d disable_functions= /root/customaid_offname_patch.php        … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/customaid_offname_patch.php apply  … 実際に変更する(先にバックアップを取る)
$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_offname_20261022/';
$f = 'app/Validators/OrderValidator.php';
$apply = (($argv[1] ?? '') === 'apply');

$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, '「お休み」「休憩・勤務時間外」を区別して記録する') !== false) exit("すでに修正済みです。何も変更していません。\n");
$re = '/^([ \t]*)\$data\[\'member_id\'\] = \$request->member_id \?\? \'\';\n/mu';
if (preg_match_all($re, $s) !== 1) exit("$f の直す場所が見つかりません(または2つ以上)。何も変更していません。\n");
$new = preg_replace_callback($re, function ($m) {
    $i = $m[1];
    return $m[0]
        . "\n" . $i . "// [カスタムエイド 2026-10-22] 「お休み」「休憩・勤務時間外」を区別して記録する(メニューなしの予約だけ・この2つの名前だけ)\n"
        . $i . "if (!\$request->smid && in_array(\$request->name, ['お休み', '休憩・勤務時間外'], true)) {\n"
        . $i . "    \$data['name'] = \$request->name;\n"
        . $i . "}\n";
}, $s, 1);
echo "① $f:メニューなしの予約で、名前が「お休み」「休憩・勤務時間外」のときは名前を保存します\n";

if (!$apply) { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
if (!copy($base . $f, $backup . str_replace('/', '__', $f))) exit("バックアップに失敗しました。何も変更していません。\n");
file_put_contents($base . $f, $new);
$out = []; $code = 1;
exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $out, $code);
echo implode("\n", $out), "\n";
if ($code !== 0) { copy($backup . str_replace('/', '__', $f), $base . $f); exit("文法エラーがあったので、元に戻しました。\n"); }
echo "完了しました。バックアップ: $backup\n";
