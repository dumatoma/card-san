<?php
// [カスタムエイド 2026-10-27b] 管理App:アイコンのバッジ(サーバーが数える)とアプリの TOP の数がずれる原因を調べる(読むだけ)
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b); $aid = (int) ($argv[1] ?? 33);
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); $DB = 'Illuminate\Support\Facades\DB';
$a = \App\Models\ShopAdminModel::find($aid); $sid = (int) $a->sid; echo "admin $aid(店 $sid) staff_room_last_read=" . $a->staff_room_last_read . "\n";
echo "\n--- 1. サーバーの数え方:お客様のメッセージの未読があるグループ\n";
foreach (\App\Models\GroupModel::where('sid', $sid)->where('unread->admin->' . $aid, '>', 0)->get() as $g) {
  $mem = \App\Models\ShopMemberModel::where('sid', $sid)->where('uid', $g->uid)->first(); $card = $mem ? \App\Models\ShopMemberModel::where('id', $mem->id)->has('card')->exists() : false;
  echo "group id={$g->id} uid={$g->uid} 未読=" . json_encode($g->unread) . " 会員=" . ($mem ? "あり(id {$mem->id})" : 'なし') . " カード=" . ($card ? 'あり' : 'なし') . "\n";
}
echo "\n--- 2. サーバーの数え方:スタッフルーム(last_read より後で、自分以外)\n";
$ctl = new \App\Http\Controllers\Api\Shop\StaffRoomController(); $rm = new \ReflectionMethod($ctl, 'groupTable'); $rm->setAccessible(true); $gt = $rm->invoke($ctl, $sid);
echo "表 $gt:" . ($DB::table($gt)->where('id', '>', (int) $a->staff_room_last_read)->where('admin_id', '!=', $aid)->count()) . " 件。最新3件:" . json_encode($DB::table($gt)->orderBy('id', 'desc')->limit(3)->get(['id', 'admin_id', 'create_time'])) . "\n";
echo "\n--- 3. アプリが読む数:スタッフルームの未読(StaffRoomController)\n"; passthru("grep -n 'group_unread\\|dm_unread\\|staff_room_last_read\\|function unread\\|function read' app/Http/Controllers/Api/Shop/StaffRoomController.php | head -20");
echo "\n--- 4. アプリが読む数:お客様のメッセージの一覧の unread_num(Shop MessageController)\n"; passthru("grep -n \"unread\\|request_type\\|type == 'list'\\|->type == \\\"list\\\"\\|has('card')\\|whereHas\" app/Http/Controllers/Api/Shop/MessageController.php | head -30");
echo "\n--- 5. アイコンの数を最後に送った記録\n"; passthru("tail -5 storage/logs/cs_apns/" . date('Y-m-d') . ".log");
