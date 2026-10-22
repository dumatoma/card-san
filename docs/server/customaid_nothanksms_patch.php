<?php
// [カスタムエイド 2026-10-22] サンキューメッセージは SMS では送らない(仕様。アプリのメッセージだけ)
//   2026-10-21 の修正で SMS の文を設定した文から作る形にしたが、サンキューの SMS は送らないのが正しい仕様なので、送る行を止める
// 使い方:
//   php -d disable_functions= /root/customaid_nothanksms_patch.php        … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/customaid_nothanksms_patch.php apply  … 実際に変更する(先にバックアップを取る)
$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_nothanksms_20261022/';
$f = 'app/Jobs/OrderThankMessageJob.php';
$apply = (($argv[1] ?? '') === 'apply');

$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, 'サンキューメッセージは SMS では送らない') !== false) exit("すでに修正済みです。何も変更していません。\n");
$re = '/([ \t]*)\$message = \\\\App\\\\Libs\\\\CsSmsText::build\(\'thanks\', \$order\);\s*\n\s*\(new AlibabaCloudSms\(\)\)->send\(\$order->phone, \$message\);\n/u';
if (preg_match_all($re, $s) !== 1) exit("$f の直す場所が見つかりません(または2つ以上)。何も変更していません。\n");
$new = preg_replace_callback($re, function ($m) {
    $i = $m[1];
    return $i . "// [カスタムエイド 2026-10-22] サンキューメッセージは SMS では送らない(仕様。アプリのメッセージだけ)\n"
        . $i . "// \$message = \\App\\Libs\\CsSmsText::build('thanks', \$order);\n"
        . $i . "// (new AlibabaCloudSms())->send(\$order->phone, \$message);\n";
}, $s, 1);
echo "① $f:サンキューの SMS を送る2行を止めます\n";

if (!$apply) { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
if (!copy($base . $f, $backup . str_replace('/', '__', $f))) exit("バックアップに失敗しました。何も変更していません。\n");
file_put_contents($base . $f, $new);
$out = []; $code = 1;
exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $out, $code);
echo implode("\n", $out), "\n";
if ($code !== 0) { copy($backup . str_replace('/', '__', $f), $base . $f); exit("文法エラーがあったので、元に戻しました。\n"); }
echo "完了しました。バックアップ: $backup\n";
