<?php
// [カスタムエイド 2026-11-04] 段階9 追記8:管理App の予約通知・予約詳細・予約内容の連絡方法で、LINE の予約を「LINE」と出す(今まで SMS と出ていた)
//   php -d disable_functions= /root/s9_line_fix8.php        … 確かめるだけ
//   php -d disable_functions= /root/s9_line_fix8.php apply  … バックアップを取ってから変更する
//   php -d disable_functions= /root/s9_line_fix8.php undo   … 元に戻す
$pairs = [
    ['        if ((string) ($order->email ?? \'\') !== \'\') return \'mail\';', '        if ((string) ($order->cs_line_uid ?? \'\') !== \'\') return \'line\'; // [カスタムエイド 2026-11-04 追記8] 段階9:LINE で受け取る予約(メール・SMS より先)
        if ((string) ($order->email ?? \'\') !== \'\') return \'mail\';']
];
$base = '/www/wwwroot/api.card-san.jp/';
$f = 'app/Libs/CsOrderContact.php';
$backup = '/www/backup_customaid_s9fix8_20261104/';
$bk = $backup . 'CsOrderContact.php';
$mode = $argv[1] ?? 'check';
if ($mode === 'undo') { if (!is_file($bk)) exit("バックアップがありません。何もしていません。\n"); copy($bk, $base . $f); exit("元に戻しました: {$f}\n"); }
$s = file_get_contents($base . $f);
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
if (strpos($s, '追記8') !== false) $ng('すでに修正済みです');
foreach ($pairs as $i => $p) { if (substr_count($s, $p[0]) !== 1) $ng('CsOrderContact.php の直す場所 ' . ($i + 1) . ' が見つかりません'); $s = str_replace($p[0], $p[1], $s); }
$tmp = sys_get_temp_dir() . '/cs_s9f8.php'; file_put_contents($tmp, $s); $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c); @unlink($tmp);
echo ($c === 0 ? 'OK' : 'NG') . " 直したあとの文法\n"; if ($c !== 0) $ng(implode(' ', $o));
if ($mode !== 'apply') exit("\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n");
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
copy($base . $f, $bk);
file_put_contents($base . $f, $s);
$o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c);
if ($c !== 0) { copy($bk, $base . $f); exit("文法エラーがあったので元に戻しました。\n"); }
echo "完了しました。バックアップ: {$backup}\n";
