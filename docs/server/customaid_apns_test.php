<?php
// [カスタムエイド 2026-10-25] Apple(APNs)への直接の通知を試す。
//   引数なし:Apple 用の番号を登録した端末の一覧を出すだけ(番号は先頭8文字だけ)
//   shop <管理者ID> または user <会員ID>:その人の iPhone にテストの通知を1件送り、送る係の記録を表示する
// 使い方:
//   php -d disable_functions= /root/customaid_apns_test.php
//   php -d disable_functions= /root/customaid_apns_test.php shop 12
$base = '/www/wwwroot/api.card-san.jp/';
chdir($base);
require_once $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$DB = 'Illuminate\Support\Facades\DB';

echo "== Apple 用の番号を登録した端末(新しい順)\n";
foreach (['shop' => 'web_shop_admin', 'user' => 'web_user'] as $type => $t) {
    $rows = $DB::table($t)->whereNotNull('cs_push_at')->orderBy('cs_push_at', 'desc')->limit(15)
        ->get($type == 'shop' ? ['id', 'name', 'cs_push_platform', 'cs_apns_token', 'cs_push_at'] : ['id', 'cs_push_platform', 'cs_apns_token', 'cs_push_at']);
    echo "-- $type($t): " . count($rows) . "件\n";
    foreach ($rows as $r) {
        $nm = $type == 'shop' ? ' ' . $r->name : '';
        echo "  id={$r->id}$nm / {$r->cs_push_platform} / 番号=" . ($r->cs_apns_token ? substr($r->cs_apns_token, 0, 8) . '…(' . strlen($r->cs_apns_token) . '文字)' : 'なし') . ' / ' . date('m-d H:i', $r->cs_push_at) . "\n";
    }
}

$type = $argv[1] ?? '';
$id = (int) ($argv[2] ?? 0);
if (!in_array($type, ['shop', 'user']) || !$id) { echo "\n(テストを送るときは:shop 管理者ID または user 会員ID を付けて実行)\n"; exit(0); }

$t = \App\Libs\CsApns::TABLE[$type];
$tok = $DB::table($t)->where('id', $id)->value('cs_apns_token');
if (!$tok) { echo "\nid=$id には Apple 用の番号がありません\n"; exit(1); }
$json = json_encode(['aps' => ['alert' => ['title' => 'Card-San テスト', 'body' => 'Apple に直接送ったテストの通知です(' . date('H:i:s') . ')'], 'sound' => 'default'],
    'payload' => json_encode(['type' => 'test'])], JSON_UNESCAPED_UNICODE);
\App\Libs\CsApns::spool($tok, \App\Libs\CsApns::TOPIC[$type], $json);
echo "\n送り待ちに置きました。送る係が数秒以内に送ります。記録を待っています…\n";
$log = $base . 'storage/logs/cs_apns/' . date('Y-m-d') . '.log';
for ($i = 0; $i < 20; $i++) {
    sleep(1);
    if (!glob($base . 'storage/app/cs_push/spool/*.req')) break;
}
sleep(2);
echo "-- 記録(最後の5行)\n";
echo is_file($log) ? implode('', array_slice(file($log), -5)) : "(まだ記録がありません。1分ほど待ってからもう一度見てください:tail $log)\n";
