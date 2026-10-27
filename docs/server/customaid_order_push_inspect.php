<?php
// [カスタムエイド 2026-10-26] 予約の自動メッセージ(予約受付・リマインド・変更・キャンセル・サンキュー)で、
//   お客さまのアプリに「通知バッジ」と「プッシュ通知」が届く作りになっているかを調べる(読むだけ・何も変更しない)。秘密の値は出さない
//   1. 今日入った予約(新しい順に3件)と、そのお客さまのメッセージ・未読の数・通知の番号があるか
//   2. 予約を作る処理(店舗側・お客さま側)で、メッセージ・未読の数・プッシュ通知を送っている行
//   3. リマインド・サンキュー(時間がたってから送る処理)で同じもの
//   4. プッシュ通知を送る部品(MessageService の push)の中身
//   5. 今日の Apple への直接送信の記録(cs_apns)と、個推の記録の最後
// 使い方:php -d disable_functions= /root/customaid_order_push_inspect.php > /root/order_push_inspect.txt 2>&1 ; wc -l /root/order_push_inspect.txt
$base = '/www/wwwroot/api.card-san.jp/';
chdir($base);
function h($t) { echo "\n========== $t ==========\n"; }
function mask($s) { return preg_replace('/\b[A-Za-z0-9+\/]{32,}\b/', '***', $s); }
function grepf($f, $re, $ctx = 0) {
    global $base;
    $L = @file($base . $f);
    if (!$L) { echo "($f がありません)\n"; return; }
    echo "--- $f\n";
    $shown = [];
    foreach ($L as $i => $l) {
        if (!preg_match($re, $l)) continue;
        for ($j = max(0, $i - $ctx); $j <= min(count($L) - 1, $i + $ctx); $j++) {
            if (isset($shown[$j])) continue;
            $shown[$j] = 1;
            echo ($j + 1) . ': ' . mask(rtrim($L[$j])) . "\n";
        }
    }
}
function func($f, $name) {
    global $base;
    $L = @file($base . $f);
    if (!$L) { echo "($f がありません)\n"; return; }
    $start = -1;
    foreach ($L as $i => $l) if (preg_match('/function\s+' . $name . '\s*\(/', $l)) { $start = $i; break; }
    if ($start < 0) { echo "($f に $name がありません)\n"; return; }
    echo "--- $f の $name\n";
    $d = 0; $seen = false;
    for ($j = $start; $j < count($L); $j++) {
        echo ($j + 1) . ': ' . mask(rtrim($L[$j])) . "\n";
        $d += substr_count($L[$j], '{') - substr_count($L[$j], '}');
        if (strpos($L[$j], '{') !== false) $seen = true;
        if ($seen && $d <= 0) break;
    }
}

require_once $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

h('1. 今日入った予約(新しい順に3件)');
$today = strtotime(date('Y-m-d 00:00:00'));
try {
    $cols = array_map(function ($c) { return $c->Field; }, DB::select('SHOW COLUMNS FROM web_order'));
    echo "web_order の欄:" . implode(', ', $cols) . "\n";
    $tcol = in_array('create_time', $cols) ? 'create_time' : (in_array('created_at', $cols) ? 'created_at' : 'id');
    $rows = DB::table('web_order')->orderBy('id', 'desc')->limit(3)->get();
    foreach ($rows as $r) {
        $a = (array) $r;
        $out = [];
        foreach ($a as $k => $v) if (preg_match('/^(id|sid|mid|uid|member_id|day_said|appoint_said|source|type|platform|order_no|create_time|created_at|start_time)$/', $k)) {
            $out[] = $k . '=' . (preg_match('/time$/', $k) && is_numeric($v) ? date('m/d H:i', $v) : $v);
        }
        echo '予約:' . implode(' ', $out) . "\n";
        $mid = $a['mid'] ?? 0;
        if ($mid) {
            $m = DB::table('web_shop_member')->where('id', $mid)->first();
            if ($m) {
                $uid = $m->uid ?? 0;
                echo "  お客さま(店舗会員 id=$mid / 利用者 uid=$uid)\n";
                $u = DB::table('web_user')->where('id', $uid)->first();
                if ($u) {
                    $ua = (array) $u;
                    echo '  通知の番号:個推=' . (!empty($ua['getui_clientid']) ? 'あり' : 'なし')
                        . ' / Apple=' . (!empty($ua['cs_apns_token']) ? 'あり(先頭' . substr($ua['cs_apns_token'], 0, 8) . ')' : 'なし')
                        . ' / 端末=' . ($ua['cs_push_platform'] ?? '?') . "\n";
                }
                $g = DB::table('web_message_group')->where('sid', $a['sid'])->where('uid', $uid)->first();
                if ($g) {
                    $ga = (array) $g;
                    echo '  チャットのまとめ:最後のメッセージ ' . (isset($ga['last_message_time']) ? date('m/d H:i:s', $ga['last_message_time']) : '?')
                        . ' / 未読 ' . ($ga['unread'] ?? '') . "\n";
                }
                $tb = 'web_message_' . $a['sid'] . '_' . $uid;
                try {
                    foreach (DB::table($tb)->orderBy('id', 'desc')->limit(2)->get() as $mm) {
                        $ma = (array) $mm;
                        echo '  メッセージ id=' . $ma['id'] . ' ' . date('m/d H:i:s', $ma['create_time'] ?? 0) . ' 送り手=' . ($ma['sender'] ?? '?')
                            . ' 本文の先頭:' . mb_substr(strip_tags($ma['message'] ?? ''), 0, 30)
                            . ' / 通知の記録:' . mb_substr((string) ($ma['push_detail'] ?? ''), 0, 160) . "\n";
                    }
                } catch (\Throwable $e) { echo "  ($tb を読めません)\n"; }
                try { echo '  バッジの数(PushBadgeService::member)= ' . json_encode(\App\Services\PushBadgeService::member($uid)) . "\n"; } catch (\Throwable $e) { echo "  (バッジの数を数えられません:" . $e->getMessage() . ")\n"; }
            }
        } else echo "  (お客さまの会員番号なし=お休み・休憩、またはお客さまなしの予約)\n";
    }
} catch (\Throwable $e) { echo '(読めません:' . $e->getMessage() . ")\n"; }

h('2. 予約を作る処理で、メッセージ・未読・プッシュ通知を送っている行');
$re = '/order_confirm_on|unread\[|->push\(|GeTui|PushBadge|CsApns|function store|function cancel|function update|dispatch\(/';
grepf('app/Http/Controllers/Api/Shop/OrderController.php', $re);
grepf('app/Http/Controllers/Api/Member/OrderController.php', $re);

h('3. リマインド・サンキュー(あとで送る処理)');
grepf('app/Jobs/OrderNoticeJob.php', $re);
grepf('app/Jobs/OrderThankMessageJob.php', $re);

h('4. プッシュ通知を送る部品');
foreach (glob($base . 'app/Services/MessageService.php') as $f) func('app/Services/MessageService.php', 'push');
func('app/Services/MessageService.php', 'createMessage');

h('5. 今日の送信の記録');
$al = $base . 'storage/logs/cs_apns/' . date('Y-m-d') . '.log';
echo "-- Apple への直接送信(最後の 20 行):$al\n";
if (is_file($al)) { $L = file($al); echo implode('', array_slice($L, -20)); } else echo "(今日の記録はありません)\n";
echo "-- 送り待ちに残っているファイル:" . count(glob($base . 'storage/app/cs_push/spool/*.req')) . " 件\n";
echo "-- 個推の記録ファイル\n";
foreach (array_slice(glob($base . 'storage/logs/*getui*'), -3) as $f) {
    echo basename($f) . "(最後の 6 行)\n";
    $L = @file($f) ?: [];
    foreach (array_slice($L, -6) as $l) echo '  ' . mask(mb_substr(rtrim($l), 0, 300)) . "\n";
}

echo "\n(おわり)\n";
