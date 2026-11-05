<?php
// [カスタムエイド 2026-11-04] 段階9 追記9:管理App の予約通知の一覧に、LINE の予約が届かない(ブロック・設定の解除)ことを渡す(cs_line = ''/ok/ng。予約詳細は s9 で済み)
//   php -d disable_functions= /root/s9_line_fix9.php        … 確かめるだけ
//   php -d disable_functions= /root/s9_line_fix9.php apply  … バックアップを取ってから変更する
//   php -d disable_functions= /root/s9_line_fix9.php undo   … 元に戻す
$pairs = [
    ['                \'contact\' => \App\Libs\CsOrderContact::of($v->order) // [カスタムエイド 2026-10-31] 連絡のしかた
',
     '                \'contact\' => \App\Libs\CsOrderContact::of($v->order), // [カスタムエイド 2026-10-31] 連絡のしかた
                \'cs_line\' => \App\Libs\CsLine::orderState($v->order) // [カスタムエイド 2026-11-04 追記9] 段階9:LINE の予約が届かないとき ng(赤い「届きません」)
']
];
$base = '/www/wwwroot/api.card-san.jp/';
$f = 'app/Http/Controllers/Api/Shop/OrderNoticeController.php';
$backup = '/www/backup_customaid_s9fix9_20261104/';
$bk = $backup . 'OrderNoticeController.php';
$mode = $argv[1] ?? 'check';
if ($mode === 'undo') { if (!is_file($bk)) exit("バックアップがありません。何もしていません。\n"); copy($bk, $base . $f); exit("元に戻しました: {$f}\n"); }
$s = file_get_contents($base . $f);
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
if (strpos($s, '追記9') !== false) $ng('すでに修正済みです');
foreach ($pairs as $i => $p) { $n = substr_count($s, $p[0]); if ($n !== 1) $ng('OrderNoticeController.php の直す場所 ' . ($i + 1) . ' が見つかりません(見つかった数 ' . $n . ')'); $s = str_replace($p[0], $p[1], $s); }
if (!is_file($base . 'app/Libs/CsLine.php')) $ng('app/Libs/CsLine.php がありません');
$tmp = sys_get_temp_dir() . '/cs_s9f9.php'; file_put_contents($tmp, $s); $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c); @unlink($tmp);
echo ($c === 0 ? 'OK' : 'NG') . " 直したあとの文法\n"; if ($c !== 0) $ng(implode(' ', $o));
if ($mode !== 'apply') exit("\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n");
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
copy($base . $f, $bk);
file_put_contents($base . $f, $s);
$o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c);
if ($c !== 0) { copy($bk, $base . $f); exit("文法エラーがあったので元に戻しました。\n"); }
echo "完了しました。バックアップ: {$backup}\n";
