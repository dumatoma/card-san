<?php
// [カスタムエイド 2026-10-27] 予約通知:担当者(指名・担当)は本人が開くまで未読を残す/一覧に「確認済み(○○さん)」/管理App のバッジに予約通知の未読を入れる(運営からのお知らせは入れない)
// 確認だけ:引数なし/反映:apply
$apply = ($argv[1] ?? '') === 'apply'; $b = '/www/wwwroot/api.card-san.jp/'; $php = '/www/server/php/73/bin/php'; $ok = true; $out = [];
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$T = '[カスタムエイド 2026-10-27]';
$HELP = "    // $T 予約通知:この人にとって未読か。お店全体の印が未読なら未読。担当者(指名・担当)なら、本人が開くまで未読\n"
 . "    public static function noticeUnreadFor(\$notice, \$aid)\n    {\n        if (!(int) \$notice->is_read) return true;\n"
 . "        \$o = \$notice->order; if (!\$o) return false;\n"
 . "        if (!in_array((int) \$aid, array_filter([(int) \$o->appoint_said, (int) \$o->day_said]))) return false;\n"
 . "        return strpos((string) (\$notice->staff_read ?? ''), ',' . (int) \$aid . ',') === false;\n    }\n"
 . "    public static function orderUnread(\$sid, \$aid)\n    {\n        \$n = 0;\n"
 . "        foreach (\\App\\Models\\OrderNoticeModel::with('order')->has('order')->where('sid', \$sid)->get() as \$v) if (self::noticeUnreadFor(\$v, \$aid)) \$n++;\n        return \$n;\n    }\n";
$BADGE = [["        if (\$shop) \$n += \\App\\Models\\AdminNoticeModel::where('type', 1)->where('create_time', '>=', \$shop->create_time - 120 * 86400)->where('read->' . \$sid, 0)->count();\n",
           "        \$n += self::orderUnread(\$sid, \$a->id); // $T 予約通知の未読(運営からのお知らせは数に入れない。アプリのバッジと同じ)\n"],
          ["    public static function shop(\$cid)\n", $HELP . "    public static function shop(\$cid)\n"]];
$jobs = [
 $b . 'app/Libs/CsBadge.php' => $BADGE, '/root/customaid_CsBadge.php' => $BADGE,
 $b . 'app/Http/Controllers/Api/Shop/OrderNoticeController.php' => [
  ["        \$data = [];\n        foreach(\$notices as \$v){\n", "        \$csMe = auth('shop')->id(); \$csNames = []; // $T 確認した人の名前\n        foreach (\\App\\Models\\ShopAdminModel::where('sid', auth('shop')->user()->sid)->get() as \$csA) \$csNames[\$csA->id] = \$csA->message_name ?: \$csA->name;\n        \$data = [];\n        foreach(\$notices as \$v){\n"],
  ["                'is_read' => \$v->is_read,\n", "                'is_read' => \\App\\Libs\\CsBadge::noticeUnreadFor(\$v, \$csMe) ? 0 : 1, // $T 担当者は本人が開くまで未読\n                'read_name' => \$csNames[(int) \$v->read_said] ?? '', // $T 確認済み(○○さん)\n"],
  ["        \$unread_count = OrderNoticeModel::where('sid', auth('shop')->user()->sid)->where('is_read', 0)->count();\n", "        \$unread_count = 0; foreach (\$notices as \$v) if (\\App\\Libs\\CsBadge::noticeUnreadFor(\$v, \$csMe)) \$unread_count++; // $T 見ている人ごとの未読の数\n"],
  ["            \$data['is_read'] = 1;\n", "            \$data['is_read'] = 1;\n            // $T 最初に開いた人を覚える。担当者(指名・担当)が開いたら、その人の分も既読にする\n            \$csMe = auth('shop')->id(); \$csO = \$order_notice->order;\n            if (!(int) \$order_notice->read_said) \$data['read_said'] = \$csMe;\n            if (\$csO && in_array(\$csMe, [(int) \$csO->appoint_said, (int) \$csO->day_said]) && strpos((string) \$order_notice->staff_read, ',' . \$csMe . ',') === false) \$data['staff_read'] = (\$order_notice->staff_read ?: ',') . \$csMe . ',';\n"]],
];
foreach ($jobs as $f => $reps) {
  $t = @file_get_contents($f); if ($t === false) { echo "✕ $f がありません\n"; $ok = false; continue; }
  if (strpos($t, 'noticeUnreadFor') !== false) { echo "○ " . basename($f) . " もう直してあります\n"; continue; }
  foreach ($reps as [$x, $y]) { $c = substr_count($t, $x); if ($c !== 1) { echo "✕ " . basename($f) . " 直す場所が {$c} か所(1か所のはず)\n"; $ok = false; continue 2; } $t = str_replace($x, $y, $t); }
  $tmp = '/tmp/customaid_on_' . basename($f); file_put_contents($tmp, $t); exec("$php -l " . escapeshellarg($tmp) . ' 2>&1', $o, $rc); @unlink($tmp);
  if ($rc) { echo "✕ " . basename($f) . " 直したあとの書き方に間違い\n"; $ok = false; continue; }
  echo "○ $f 直す場所・書き方 問題なし\n"; $out[$f] = $t;
}
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$has = Illuminate\Support\Facades\Schema::hasColumn('web_order_notice', 'read_said') && Illuminate\Support\Facades\Schema::hasColumn('web_order_notice', 'staff_read');
echo $has ? "○ 表の欄(read_said・staff_read)はもうあります\n" : "・表 web_order_notice に欄を2つ足します(read_said:最初に開いた人/staff_read:開いた担当者)\n";
$old = App\Models\OrderNoticeModel::with('order')->has('order')->where('is_read', 1)->count();
echo "・今までに既読の予約通知 {$old} 件は、担当者も既読として扱います(反映した直後に、古い通知が未読に戻らないように)\n";
if (!$ok) { echo "\n問題があるので止めます。何も変更していません。\n"; exit(1); }
if (!$apply) { echo "\n確認だけ終わりました。問題がなければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_order_notice_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
if (!$has) { Illuminate\Support\Facades\DB::statement("ALTER TABLE web_order_notice ADD COLUMN read_said INT NOT NULL DEFAULT 0, ADD COLUMN staff_read VARCHAR(255) NOT NULL DEFAULT ''"); echo "○ 表に欄を2つ足しました\n"; }
$k = 0; foreach (App\Models\OrderNoticeModel::with('order')->has('order')->where('is_read', 1)->where('staff_read', '')->get() as $v) { $ids = array_filter([(int) $v->order->appoint_said, (int) $v->order->day_said]); if ($ids) { $v->update(['staff_read' => ',' . implode(',', array_unique($ids)) . ',']); $k++; } }
echo "○ 既読の予約通知 {$k} 件に、担当者の既読を入れました\n";
foreach ($out as $f => $t) { copy($f, $bk . str_replace('/', '_', ltrim($f, '/'))); $s = stat($f); file_put_contents($f, $t); @chown($f, $s['uid']); @chgrp($f, $s['gid']); echo "○ " . basename($f) . " を直しました\n"; }
echo "控え:$bk(元に戻すとき:控えのファイルを戻す。足した欄はそのままで問題なし)\n";
