<?php
// [カスタムエイド 2026-10-26] 予約の自動メッセージの通知の調査・その2(読むだけ・何も変更しない)。秘密の値は出さない
//   1回目で分かったこと:今日 12:59 の予約(id=399・坂本こはさん uid=8)のあと、Apple への送信の記録がない
//   ここでは:①お店の自動メッセージの設定(オン・オフ) ②その予約のメッセージと未読の数 ③予約を作る処理のメッセージ〜通知の部分の全文 を見る
// 使い方:php -d disable_functions= /root/customaid_order_push_inspect2.php > /root/order_push_inspect2.txt 2>&1 ; wc -l /root/order_push_inspect2.txt
$base = '/www/wwwroot/api.card-san.jp/';
chdir($base);
function h($t) { echo "\n========== $t ==========\n"; }
function mask($s) { return preg_replace('/\b[A-Za-z0-9+\/]{32,}\b/', '***', $s); }
function lines($f, $from, $to) {
    global $base;
    $L = @file($base . $f);
    if (!$L) { echo "($f がありません)\n"; return; }
    echo "--- " . $f . "(" . $from . "〜" . $to . " 行)\n";
    for ($j = $from - 1; $j < min(count($L), $to); $j++) echo ($j + 1) . ': ' . mask(rtrim($L[$j])) . "\n";
}
require_once $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

h('1. お店 11 の自動メッセージの設定');
$s = DB::table('web_shop')->where('id', 11)->first();
if ($s) foreach ((array) $s as $k => $v) if (preg_match('/_on$|order_.*message|thanks/', $k)) echo "  $k = " . mb_substr((string) $v, 0, 80) . "\n";

h('2. 予約 id=399 のお客さま(uid=8)のメッセージと未読の数');
try {
    $g = new \App\Models\GroupModel();
    echo 'まとめの表:' . $g->getTable() . "\n";
    $r = \App\Models\GroupModel::where('sid', 11)->where('uid', 8)->first();
    if ($r) echo '  最後のメッセージ ' . date('m/d H:i:s', $r->last_message_time ?? 0) . ' / 送り手 ' . ($r->last_message_sender ?? '?') . ' / 未読 ' . json_encode($r->unread) . ' / 先頭:' . mb_substr(strip_tags((string) $r->last_message), 0, 30) . "\n";
} catch (\Throwable $e) { echo '(読めません:' . $e->getMessage() . ")\n"; }
try {
    foreach (DB::table('web_message_11_8')->orderBy('id', 'desc')->limit(4)->get() as $m) {
        echo '  メッセージ id=' . $m->id . ' ' . date('m/d H:i:s', $m->create_time) . ' 送り手=' . $m->sender . ' 先頭:' . mb_substr(strip_tags($m->message), 0, 24)
            . ' / 通知の記録:' . mb_substr((string) ($m->push_detail ?? ''), 0, 120) . "\n";
    }
} catch (\Throwable $e) { echo '(メッセージを読めません:' . $e->getMessage() . ")\n"; }
try { echo '  バッジの数(PushBadgeService::member(8))= ' . json_encode(\App\Services\PushBadgeService::member(8)) . "\n"; } catch (\Throwable $e) { echo '(バッジの数を数えられません:' . $e->getMessage() . ")\n"; }

h('3. お店側で予約を作る処理(store)のメッセージ〜通知の部分');
lines('app/Http/Controllers/Api/Shop/OrderController.php', 300, 430);

h('4. お客さま側で予約を作る処理(store)のメッセージ〜通知の部分');
lines('app/Http/Controllers/Api/Member/OrderController.php', 270, 450);

h('5. 個推に送る部品の、送る前の確認(GeTuiApi の pushSingleBatchCid)');
$L = @file($base . 'app/Libs/GeTuiApi.php') ?: [];
foreach ($L as $i => $l) if (preg_match('/function pushSingleBatchCid/', $l)) { lines('app/Libs/GeTuiApi.php', $i + 1, $i + 45); break; }

h('6. 今日の Apple への直接送信の記録(12:50 以降)');
$al = $base . 'storage/logs/cs_apns/' . date('Y-m-d') . '.log';
foreach ((is_file($al) ? file($al) : []) as $l) if (substr($l, 11, 5) >= '12:50') echo $l;
h('7. 個推の記録(今日・12:50 以降・最後の 30 行)');
foreach (glob($base . 'storage/logs/*getui*') as $f) {
    echo basename($f) . "\n";
    $L = @file($f) ?: [];
    $out = [];
    foreach ($L as $l) if (strpos($l, date('Y-m-d')) !== false && substr($l, 12, 5) >= '12:50') $out[] = '  ' . mask(mb_substr(rtrim($l), 0, 300));
    echo implode("\n", array_slice($out, -30)) . "\n";
}
echo "\n(おわり)\n";
