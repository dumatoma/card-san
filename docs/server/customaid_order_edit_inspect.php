<?php
// [カスタムエイド 2026-10-26] 管理App「予約内容」で時間を短くして保存すると「通信に失敗しました」になる原因を調べる(読むだけ・何も変更しない)。秘密の値は出さない
//   1. 今日のサーバーのエラー記録(laravel.log)のうち、予約の変更(order)に関係するもの
//   2. 予約を変更する処理(Shop/OrderController の update)の全体
//   3. その中で呼んでいる、時間・メニューの確認の処理
//   4. いままでのカスタムエイドの修正が入っている場所
// 使い方:php -d disable_functions= /root/customaid_order_edit_inspect.php > /root/order_edit_inspect.txt 2>&1 ; wc -l /root/order_edit_inspect.txt
$base = '/www/wwwroot/api.card-san.jp/';
chdir($base);
function h($t) { echo "\n========== $t ==========\n"; }
function mask($s) { return preg_replace('/\b[A-Za-z0-9+\/]{32,}\b/', '***', $s); }
function sh($c) { $o = []; exec($c . ' 2>&1', $o); echo mask(implode("\n", $o)), "\n"; }

h('1. 今日のエラー記録(最後の 40 件の見出しと、その次の数行)');
$logs = glob($base . 'storage/logs/laravel*.log');
usort($logs, function ($a, $b) { return filemtime($b) - filemtime($a); });
echo "記録ファイル:" . implode(', ', array_map('basename', array_slice($logs, 0, 3))) . "\n";
if ($logs) {
    $L = @file($logs[0]) ?: [];
    $hits = [];
    foreach ($L as $i => $l) if (preg_match('/^\[' . date('Y-m-d') . '[^\]]*\] \w+\.(ERROR|CRITICAL|ALERT|EMERGENCY)/', $l)) $hits[] = $i;
    echo "今日のエラーの数:" . count($hits) . "\n";
    foreach (array_slice($hits, -40) as $i) {
        echo "--- 行 " . ($i + 1) . "\n";
        for ($j = $i; $j < min(count($L), $i + 6); $j++) echo mask(mb_substr(rtrim($L[$j]), 0, 400)) . "\n";
    }
}

h('2. Shop/OrderController の update(予約の変更)');
$f = 'app/Http/Controllers/Api/Shop/OrderController.php';
$L = @file($base . $f) ?: [];
$start = -1;
foreach ($L as $i => $l) if (preg_match('/function\s+update\s*\(/', $l)) { $start = $i; break; }
if ($start < 0) echo "(update が見つかりません)\n";
else {
    $depth = 0; $seen = false;
    for ($j = $start; $j < count($L); $j++) {
        echo ($j + 1) . ': ' . mask(rtrim($L[$j])) . "\n";
        $depth += substr_count($L[$j], '{') - substr_count($L[$j], '}');
        if (strpos($L[$j], '{') !== false) $seen = true;
        if ($seen && $depth <= 0) break;
    }
}

h('3. このファイルの中の、時間・メニューの長さの確認らしき行');
foreach ($L as $i => $l) if (preg_match('/duration|minute|diffInMinutes|time_length|service_time|menu.*time|時間|422|validate/i', $l)) echo ($i + 1) . ': ' . mask(trim($l)) . "\n";

h('4. カスタムエイドの修正が入っている場所(このファイル)');
foreach ($L as $i => $l) if (strpos($l, 'カスタムエイド') !== false) echo ($i + 1) . ': ' . mask(trim($l)) . "\n";
echo "\n最終更新:" . date('Y-m-d H:i:s', @filemtime($base . $f)) . "\n";
sh('ls -lt ' . $base . 'app/Http/Controllers/Api/Shop/ | head -8');
sh('ls -dt /www/backup_customaid_* | head -15');

echo "\n(おわり)\n";
