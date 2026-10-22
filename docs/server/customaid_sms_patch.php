<?php
// [カスタムエイド 2026-10-21] 予約の自動メッセージ(SMS・アプリ内メッセージ)を、店舗が設定した文から同じ文章で作る
//  ・SMS:以前は固定の文 → 設定した文で作る
//  ・アプリ内メッセージ:SMS と同じ文章に(改行もそのまま)
//  ・予約内容変更:スイッチがオフなら送らない(以前は確認が止められていた)
// 実行: php -d disable_functions= /root/customaid_sms_patch.php
// 想定と1つでも違えば、何も変更せずに終了します。文法エラーがあれば自動で元に戻します。
$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_sms_20261021/';
$tag = '[カスタムエイド 2026-10-21]';
$libPath = 'app/Libs/CsSmsText.php';
// ファイル => [SMS の固定文の数, アプリ内メッセージの数, 予約内容変更のスイッチの数]
$files = [
    'app/Jobs/OrderNoticeJob.php' => [1, 1, 0],
    'app/Jobs/OrderThankMessageJob.php' => [1, 1, 0],
    'app/Http/Controllers/Api/Shop/OrderController.php' => [3, 3, 1],
    'app/Http/Controllers/Api/Member/OrderController.php' => [1, 1, 0],
];
if (file_exists($base . $libPath)) exit("$libPath がすでにあります。何も変更していません。\n");
$libSrc = @file_get_contents('/root/customaid_CsSmsText.php');
if ($libSrc === false || strpos($libSrc, 'class CsSmsText') === false) exit("/root/customaid_CsSmsText.php がありません。何も変更していません。\n");

// SMS の固定文(2026-10-12 に書式をそろえたもの)
$smsRe = '/([ \t]*)\/\/ \[カスタムエイド 2026-10-12\] 書式をそろえた[^\n]*\n[ \t]*\$message = implode\("\\\\n\\\\n", array_filter\(\[\n(.*?)\n[ \t]*\], function \(\$v\) \{ return \$v !== null && \$v !== \'\'; \}\)\);\n/su';
// アプリ内メッセージ(予約確認・リマインダー・変更・キャンセル):見出しの行から住所の行まで
$appRe = '/([ \t]*)\$message_str = \'<p>\' \. \(\$[A-Za-z_>\-]*(order_confirm_message|order_notice_message|order_cancel_message|order_update_message)\[0\][^\n]*\n(?:[ \t]*\$message_str \.= [^\n]*\n)*?[ \t]*\$message_str \.= \'<p>\' \. \(\(\$[A-Za-z_>\-]*address1[^\n]*\n/u';
// アプリ内メッセージ(サンキュー):3行
$thxRe = '/([ \t]*)\$message_str = \'<p>\' \. \(\$order->member->name \?\? \'\'\) \. \(\$order->shop->thanks_message\[0\][^\n]*\n[ \t]*\$message_str \.= [^\n]*\n[ \t]*\$message_str \.= [^\n]*\n/u';
// 予約内容変更のスイッチ(コメントで止められている)
$updRe = '/([ \t]*)\/\/ if\(\$shop->order_update_on == 0\)\{\n[ \t]*\/\/     return responseToJson\(400\);\n[ \t]*\/\/ \}\n/u';
$typeOf = ['order_confirm_message' => 'confirm', 'order_notice_message' => 'notice', 'order_cancel_message' => 'cancel', 'order_update_message' => 'update'];

$new = [];
foreach ($files as $f => $cnt) {
    $s = file_get_contents($base . $f);
    if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
    if (strpos($s, $tag . ' SMS') !== false || strpos($s, 'CsSmsText') !== false) exit("すでに修正済みです($f)。何も変更していません。\n");

    // 1. SMS
    $n = preg_match_all($smsRe, $s, $mm);
    if ($n !== $cnt[0]) exit("$f の SMS の文の数が想定と違います(想定 {$cnt[0]}、実際 $n)。何も変更していません。\n");
    $bad = false;
    $s = preg_replace_callback($smsRe, function ($m) use ($tag, &$bad) {
        $b = $m[2];
        if (strpos($b, '【予約確認のお知らせ】') !== false) $t = 'notice';
        elseif (strpos($b, '【予約受付のお知らせ】') !== false) $t = 'confirm';
        elseif (strpos($b, '【予約変更のお知らせ】') !== false) $t = 'update';
        elseif (strpos($b, '【予約キャンセルのお知らせ】') !== false) $t = 'cancel';
        elseif (strpos($b, 'この度は当店をご利用頂き') !== false) $t = 'thanks';
        else { $bad = true; return $m[0]; }
        return $m[1] . "// $tag SMS の文を、自動メッセージで設定した文から作る(以前は SMS だけ固定の文)。中身は app/Libs/CsSmsText.php\n"
             . $m[1] . "\$message = \\App\\Libs\\CsSmsText::build('$t', \$order);\n";
    }, $s);
    if ($bad) exit("$f で種類の分からない SMS の文がありました。何も変更していません。\n");

    // 2. アプリ内メッセージ
    $n = preg_match_all($appRe, $s, $mm) + preg_match_all($thxRe, $s, $mm2);
    if ($n !== $cnt[1]) exit("$f のアプリ内メッセージの数が想定と違います(想定 {$cnt[1]}、実際 $n)。何も変更していません。\n");
    $s = preg_replace_callback($appRe, function ($m) use ($tag, $typeOf) {
        return $m[1] . "// $tag アプリ内メッセージも SMS と同じ文章に(設定した改行もそのまま)。中身は app/Libs/CsSmsText.php\n"
             . $m[1] . "\$message_str = \\App\\Libs\\CsSmsText::html('" . $typeOf[$m[2]] . "', \$order, \$member->name);\n";
    }, $s);
    $s = preg_replace_callback($thxRe, function ($m) use ($tag) {
        return $m[1] . "// $tag アプリ内メッセージも SMS と同じ文章に(設定した改行もそのまま)。中身は app/Libs/CsSmsText.php\n"
             . $m[1] . "\$message_str = \\App\\Libs\\CsSmsText::html('thanks', \$order, \$order->member->name ?? '');\n";
    }, $s);

    // 3. 予約内容変更のスイッチ
    $n = preg_match_all($updRe, $s, $mm);
    if ($n !== $cnt[2]) exit("$f の予約内容変更のスイッチの数が想定と違います(想定 {$cnt[2]}、実際 $n)。何も変更していません。\n");
    $s = preg_replace_callback($updRe, function ($m) use ($tag) {
        return $m[1] . "// $tag 予約内容変更のスイッチがオフなら送らない(以前はこの確認が止められていた)。画面にエラーを出さないよう 200 で終える\n"
             . $m[1] . "if(\$shop->order_update_on == 0){\n"
             . $m[1] . "    return responseToJson(200);\n"
             . $m[1] . "}\n";
    }, $s);

    $new[$f] = $s;
}

// バックアップ
if (!is_dir($backup) && !mkdir($backup, 0755, true)) exit("バックアップ先を作れませんでした。何も変更していません。\n");
foreach ($files as $f => $cnt) {
    if (!copy($base . $f, $backup . str_replace('/', '__', $f))) exit("バックアップに失敗しました: $f\n何も変更していません。\n");
}

// 書き込み
file_put_contents($base . $libPath, $libSrc);
foreach ($new as $f => $s) file_put_contents($base . $f, $s);

// 文法チェック(だめなら全部元に戻す)
$ok = true;
foreach (array_merge([$libPath], array_keys($files)) as $f) {
    $out = []; $code = 1;
    exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $out, $code);
    echo implode("\n", $out), "\n";
    if ($code !== 0) $ok = false;
}
if (!$ok) {
    foreach ($files as $f => $cnt) copy($backup . str_replace('/', '__', $f), $base . $f);
    unlink($base . $libPath);
    exit("文法エラーがあったので、すべて元に戻しました。\n");
}
$ref = $base . 'app/Jobs/OrderNoticeJob.php';
@chown($base . $libPath, fileowner($ref));
@chgrp($base . $libPath, filegroup($ref));
echo "完了しました。バックアップ: $backup\n";
