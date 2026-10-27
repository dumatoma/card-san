<?php
// [カスタムエイド 2026-10-25] プッシュ通知を自分たちで送る形(Apple・Google に直接/自社の WebSocket)にする準備。
//   サーバーの今の作りと、Apple(APNs)・Google(FCM)と直接通信できるかを読むだけ(何も変更しない・通知も送らない)
//   秘密の値(キー・パスワード・端末の番号)は表示しない
// 使い方:php -d disable_functions= /root/customaid_push_inspect.php > /root/push_inspect.txt 2>&1 ; wc -l /root/push_inspect.txt
$base = '/www/wwwroot/api.card-san.jp/';
chdir($base);
function h($t) { echo "\n========== $t ==========\n"; }
function sh($c) { $o = []; exec($c . ' 2>&1', $o); echo implode("\n", $o), "\n"; }
// 秘密らしい値を伏せる
function mask($s) {
    $s = preg_replace('/((secret|key|master|password|token|appkey|appid)[\'"]?\s*(=>|=|:)\s*)[\'"][^\'"]{4,}[\'"]/i', '$1"***"', $s);
    return preg_replace('/\b[A-Za-z0-9]{20,}\b/', '***', $s);
}
function around($f, $re, $before = 3, $after = 25, $max = 3) {
    global $base;
    $L = @file($base . $f);
    if (!$L) { echo "($f がありません)\n"; return; }
    $n = 0;
    foreach ($L as $i => $l) {
        if (preg_match($re, $l)) {
            echo "--- $f:" . ($i + 1) . "\n";
            for ($j = max(0, $i - $before); $j < min(count($L), $i + $after); $j++) echo ($j + 1) . ': ' . mask(rtrim($L[$j])) . "\n";
            if (++$n >= $max) break;
        }
    }
    if (!$n) echo "($f に見つかりません: $re)\n";
}
function method($f, $name, $max = 70) { around($f, '/function\s+' . preg_quote($name, '/') . '\s*\(/', 1, $max, 1); }

h('1. PHP・curl・OpenSSL(Apple・Google に直接送るのに必要な機能)');
echo 'PHP ' . PHP_VERSION . "\n";
$cv = curl_version();
echo 'curl ' . $cv['version'] . ' / ' . $cv['ssl_version'] . "\n";
echo 'curl の HTTP/2 対応(APNs に必要): ' . (($cv['features'] & CURL_VERSION_HTTP2) ? 'あり' : 'なし') . "\n";
echo 'CURL_HTTP_VERSION_2_0 定数: ' . (defined('CURL_HTTP_VERSION_2_0') ? 'あり' : 'なし') . "\n";
echo OPENSSL_VERSION_TEXT . "\n";
$curves = function_exists('openssl_get_curve_names') ? openssl_get_curve_names() : [];
echo 'prime256v1(ES256 の署名に必要): ' . (in_array('prime256v1', $curves) ? 'あり' : 'なし') . "\n";
// その場で作った使い捨ての鍵で、署名と確認ができるか(保存しない)
$k = @openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
if ($k) {
    $ok = openssl_sign('test', $sig, $k, OPENSSL_ALGO_SHA256);
    $pub = openssl_pkey_get_details($k)['key'];
    echo 'ES256 の署名テスト: ' . ($ok && openssl_verify('test', $sig, $pub, OPENSSL_ALGO_SHA256) === 1 ? 'OK' : 'NG') . "\n";
} else {
    echo 'ES256 の鍵を作れない: ' . openssl_error_string() . "\n";
}
echo 'RS256(FCM の鍵の署名)テスト: ';
$r = @openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
echo ($r && openssl_sign('test', $s2, $r, OPENSSL_ALGO_SHA256)) ? "OK\n" : "NG\n";
sh('curl -V | head -3');

h('2. Apple・Google につながるか(認証なしで試すだけ。エラーの返事が来れば、つながっている)');
function probe($label, $url, $post, $h2) {
    $c = curl_init($url);
    curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_CONNECTTIMEOUT => 10, CURLOPT_HEADER => false]);
    if ($h2 && defined('CURL_HTTP_VERSION_2_0')) curl_setopt($c, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
    if ($post !== null) { curl_setopt($c, CURLOPT_POST, true); curl_setopt($c, CURLOPT_POSTFIELDS, $post); curl_setopt($c, CURLOPT_HTTPHEADER, ['content-type: application/json']); }
    $b = curl_exec($c);
    $i = curl_getinfo($c);
    $v = isset($i['http_version']) ? $i['http_version'] : '?';
    echo "$label\n  HTTP " . $i['http_code'] . ' / http_version=' . $v . ' (3=HTTP/2) / ' . round($i['total_time'], 2) . "秒\n";
    if ($b === false) echo '  エラー: ' . curl_error($c) . "\n"; else echo '  返事: ' . mb_substr(trim($b), 0, 160) . "\n";
    curl_close($c);
}
probe('APNs 本番(api.push.apple.com)', 'https://api.push.apple.com/3/device/00', '{}', true);
probe('APNs 開発(api.sandbox.push.apple.com)', 'https://api.sandbox.push.apple.com/3/device/00', '{}', true);
probe('Google の鍵の受け取り口(oauth2.googleapis.com)', 'https://oauth2.googleapis.com/token', '{}', false);
probe('FCM(fcm.googleapis.com)', 'https://fcm.googleapis.com/v1/projects/card-san/messages:send', '{}', false);

h('3. 個推を使っている所(すべて)');
sh('grep -rn "GeTuiApi" app routes config --include=*.php | grep -v "^app/Libs/GeTuiApi.php" ');
echo "\n-- app/Libs の一覧\n";
sh('ls -la app/Libs');

h('4. GeTuiApi.php の作り(メソッドの一覧と、送る部分)');
sh('grep -n "function \\|class \\|const \\|restapi\\|config(\\|env(" app/Libs/GeTuiApi.php | sed -E "s/[A-Za-z0-9]{20,}/***/g"');
method('app/Libs/GeTuiApi.php', '__construct', 30);
method('app/Libs/GeTuiApi.php', 'pushSingleBatchCid', 60);
echo "\n-- PushBadgeService\n";
sh('ls app/Services 2>/dev/null; grep -rln "class PushBadgeService" app');
foreach (explode("\n", trim((string) shell_exec('grep -rln "class PushBadgeService" app 2>/dev/null'))) as $f) if ($f) sh('grep -n "function " ' . escapeshellarg($f));

h('5. 端末の登録(bind_clientid)');
around('routes/api.php', '/bind_clientid|bind_socket/', 1, 2, 6);
foreach (explode("\n", trim((string) shell_exec('grep -rln "function bind_clientid\\|function bindClientid\\|function bind_socket" app/Http/Controllers 2>/dev/null'))) as $f) {
    if (!$f) continue;
    method($f, 'bind_clientid', 30);
    method($f, 'bindClientid', 30);
    method($f, 'bind_socket', 30);
}

h('6. 端末の番号を保存している欄と件数(番号そのものは表示しない)');
require_once $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = config('database.connections.mysql.database');
$cols = Illuminate\Support\Facades\DB::select("SELECT TABLE_NAME t, COLUMN_NAME c, COLUMN_TYPE ty FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND (COLUMN_NAME LIKE '%clientid%' OR COLUMN_NAME LIKE '%client_id%' OR COLUMN_NAME LIKE '%cid%' OR COLUMN_NAME LIKE '%device%' OR COLUMN_NAME LIKE '%token%' OR COLUMN_NAME LIKE '%socket%') ORDER BY TABLE_NAME", [$db]);
foreach ($cols as $r) {
    if (preg_match('/^web_(staff_room_msg|staff_dm|message)_/', $r->t)) continue; // 店舗ごとのチャットの表は省く
    $n = Illuminate\Support\Facades\DB::table($r->t)->whereNotNull($r->c)->where($r->c, '<>', '')->count();
    $all = Illuminate\Support\Facades\DB::table($r->t)->count();
    echo "{$r->t}.{$r->c} ({$r->ty}) 入っている {$n} / 全 {$all}\n";
}
echo "\n-- 会員・管理者の表の欄\n";
foreach (['web_member', 'web_shop_admin'] as $t) {
    $c = Illuminate\Support\Facades\DB::select("SELECT COLUMN_NAME c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=?", [$db, $t]);
    echo $t . ': ' . implode(', ', array_map(function ($x) { return $x->c; }, $c)) . "\n";
}

h('7. 自社の WebSocket(wss.card-san.jp)を動かしているもの');
sh('ps -eo user,pid,etime,args | grep -iE "workerman|gateway|swoole|websocket|start\\.php|socket" | grep -v grep | head -20');
sh('grep -rln "wss.card-san.jp" /www/server/panel/vhost/nginx 2>/dev/null');
foreach (explode("\n", trim((string) shell_exec('grep -rln "wss.card-san.jp" /www/server/panel/vhost/nginx 2>/dev/null'))) as $f) {
    if ($f) { echo "--- $f\n"; sh('grep -n "server_name\\|location\\|proxy_pass\\|listen" ' . escapeshellarg($f)); }
}
echo "\n-- サーバーから WebSocket に送っている所\n";
sh('grep -rn "Gateway::\\|sendToUid\\|sendToClient\\|sendToGroup\\|GatewayClient" app --include=*.php | head -30');
sh('grep -n "workerman\\|gateway" composer.json');

h('8. 個推の記録(件数と最後の日だけ)');
sh('ls storage/logs/getui 2>/dev/null | tail -5; ls storage/logs/getui 2>/dev/null | wc -l');
$last = trim((string) shell_exec('ls storage/logs/getui 2>/dev/null | tail -1'));
if ($last) sh('grep -o "\\"status\\":\\"[a-z_]*\\"\\|successed_[a-z]*\\|\\"code\\":[0-9]*" storage/logs/getui/' . escapeshellarg($last) . ' | sort | uniq -c | head');

echo "\n(おわり)\n";
