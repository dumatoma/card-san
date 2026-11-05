<?php
// [カスタムエイド 2026-11-04] 段階9 追記4:LINE に届く予約のお知らせの文字を大きく(ふつうのトークに近く)・メニューの価格をお店が入れた文字のまま出す
//   php -d disable_functions= /root/s9_line_fix4.php        … 確かめるだけ
//   php -d disable_functions= /root/s9_line_fix4.php apply  … バックアップを取ってから変更する
//   php -d disable_functions= /root/s9_line_fix4.php undo   … 元に戻す
$pairs = [
    ['        $tx = function ($s, $extra = []) { return array_merge([\'type\' => \'text\', \'text\' => (string) $s, \'wrap\' => true, \'size\' => \'sm\', \'color\' => \'#333333\'], $extra); };
        $body = [$tx($subject, [\'weight\' => \'bold\', \'size\' => \'md\', \'color\' => \'#111111\'])];', '        // [カスタムエイド 2026-11-04 追記4] 坂本さん:ふつうのトークより文字が小さい → 文字を1段大きく(sm→md、見出し md→lg、お店 xs→sm)、LINE の文字の大きさの設定に合わせて変わるように(scaling)
        $tx = function ($s, $extra = []) { return array_merge([\'type\' => \'text\', \'text\' => (string) $s, \'wrap\' => true, \'size\' => \'md\', \'color\' => \'#333333\', \'scaling\' => true], $extra); };
        $body = [$tx($subject, [\'weight\' => \'bold\', \'size\' => \'lg\', \'color\' => \'#111111\'])];'],
    ['            if ($r[0] === \'メニュー\' && !empty($r[2])) $v .= \'(￥\' . number_format((float) $r[2]) . \')\';', '            // [カスタムエイド 2026-11-04 追記4] 価格はお店が入れた文字のまま(「5,000〜」を数にすると 5 になっていた)
            if ($r[0] === \'メニュー\' && trim((string) ($r[2] ?? \'\')) !== \'\') { $pr = trim((string) $r[2]); $v .= \'(\' . (preg_match(\'/^[0-9]/\', $pr) ? \'￥\' : \'\') . $pr . \')\'; }'],
    ['        if ($sh) $body[] = $tx(implode("\\n", $sh), [\'margin\' => \'lg\', \'size\' => \'xs\', \'color\' => \'#888888\']);', '        if ($sh) $body[] = $tx(implode("\\n", $sh), [\'margin\' => \'lg\', \'size\' => \'sm\', \'color\' => \'#888888\']);']
];
$base = '/www/wwwroot/api.card-san.jp/';
$f = 'app/Libs/CsLine.php';
$backup = '/www/backup_customaid_s9fix4_20261104/';
$bk = $backup . 'CsLine.php';
$mode = $argv[1] ?? 'check';
if ($mode === 'undo') { if (!is_file($bk)) exit("バックアップがありません。何もしていません。\n"); copy($bk, $base . $f); exit("元に戻しました: {$f}\n"); }
$s = file_get_contents($base . $f);
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
if (strpos($s, '追記4') !== false) $ng('すでに修正済みです');
foreach ($pairs as $i => $p) { if (substr_count($s, $p[0]) !== 1) $ng('CsLine.php の直す場所 ' . ($i + 1) . ' が見つかりません'); $s = str_replace($p[0], $p[1], $s); }
$tmp = sys_get_temp_dir() . '/cs_s9f4.php'; file_put_contents($tmp, $s); $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c); @unlink($tmp);
echo ($c === 0 ? 'OK' : 'NG') . " 直したあとの文法\n"; if ($c !== 0) $ng(implode(' ', $o));
if ($mode !== 'apply') exit("\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n");
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
copy($base . $f, $bk);
file_put_contents($base . $f, $s);
$o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c);
if ($c !== 0) { copy($bk, $base . $f); exit("文法エラーがあったので元に戻しました。\n"); }
echo "完了しました。バックアップ: {$backup}\n";
