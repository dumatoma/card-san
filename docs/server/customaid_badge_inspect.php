<?php
// [カスタムエイド 2026-10-26] バッジの数のずれを調べる(読むだけ・何も変更しない)。秘密の値は出さない
//   ・PushBadgeService.php(通知のバッジの数を数える部品)の全体
//   ・アプリが使う未読の数(member?request_type=unread_num、message?type=count)の数え方
//   ・会員 id=8 の、いまの数(テストで使っている坂本さんの会員)
// 使い方:php -d disable_functions= /root/customaid_badge_inspect.php > /root/badge_inspect.txt 2>&1 ; wc -l /root/badge_inspect.txt
$base = '/www/wwwroot/api.card-san.jp/';
chdir($base);
function h($t) { echo "\n========== $t ==========\n"; }
function around($f, $re, $before = 3, $after = 40, $max = 3) {
    global $base;
    $L = @file($base . $f);
    if (!$L) { echo "($f がありません)\n"; return; }
    $n = 0;
    foreach ($L as $i => $l) {
        if (preg_match($re, $l)) {
            echo "--- $f:" . ($i + 1) . "\n";
            for ($j = max(0, $i - $before); $j < min(count($L), $i + $after); $j++) echo ($j + 1) . ': ' . rtrim($L[$j]) . "\n";
            if (++$n >= $max) break;
        }
    }
    if (!$n) echo "($f に見つかりません: $re)\n";
}

h('1. app/Services/PushBadgeService.php(全体)');
echo @file_get_contents($base . 'app/Services/PushBadgeService.php');

h('2. 会員の未読の数(member?request_type=unread_num)');
around('app/Http/Controllers/Api/Member/MemberController.php', '/unread_num/', 15, 40, 3);

h('3. お店ごとの未読の数(message?type=count)');
around('app/Http/Controllers/Api/Member/MessageController.php', '/count/', 5, 30, 4);

h('4. 会員がメッセージを送ったときの未読の付け方');
around('app/Http/Controllers/Api/Member/MessageController.php', '/function store|unread/', 2, 50, 3);

h('5. 会員 id=8 のいまの数');
require_once $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try { echo 'PushBadgeService::member(8) = ' . json_encode(\App\Services\PushBadgeService::member(8)) . "\n"; } catch (\Throwable $e) { echo '(エラー) ' . $e->getMessage() . "\n"; }
$db = config('database.connections.mysql.database');
foreach (['web_message_group', 'web_member_message_group', 'web_message'] as $t) {
    $c = Illuminate\Support\Facades\DB::select("SELECT COLUMN_NAME c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=?", [$db, $t]);
    if ($c) echo "$t: " . implode(', ', array_map(function ($x) { return $x->c; }, $c)) . "\n";
}
$tabs = Illuminate\Support\Facades\DB::select("SELECT TABLE_NAME t FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND (TABLE_NAME LIKE '%message%group%' OR TABLE_NAME LIKE '%unread%')", [$db]);
echo '表:' . implode(', ', array_map(function ($x) { return $x->t; }, $tabs)) . "\n";
echo "\n(おわり)\n";
