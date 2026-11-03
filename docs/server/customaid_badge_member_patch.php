<?php
// [カスタムエイド 2026-10-27b] 管理App のアイコンのバッジ:お客様のメッセージの未読を、アプリの一覧と同じ数え方にする。確認だけ:引数なし/反映:apply
//   アプリの一覧(Shop MessageController の list)は「今もこのお店の会員のお客様」かつ「消していないやりとり」だけ。サーバーは会員でないお客様の未読も数えていて、
//   アイコンが「2」、アプリの TOP が「1」になっていた(坂本さんの確認。店舗11:会員でないお客様 170 の未読1件)
$apply = ($argv[1] ?? '') === 'apply'; $f = '/www/wwwroot/api.card-san.jp/app/Libs/CsBadge.php';
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$t = file_get_contents($f); if (strpos($t, 'csbadge-member-only') !== false) { echo "○ もう直してあります\n"; exit; }
$a = "        \$n = (int) \\App\\Models\\GroupModel::where('sid', \$sid)->where('unread->admin->' . \$a->id, '>', 0)->sum('unread->admin->' . \$a->id);\n";
$n2 = "        // [カスタムエイド 2026-10-27b] csbadge-member-only:アプリのメッセージ一覧と同じく、今も会員のお客様・消していないやりとりだけ数える\n"
 . "        \$n = (int) \\App\\Models\\GroupModel::where('sid', \$sid)->whereHas('member', function (\$q) use (\$sid) { \$q->where('sid', \$sid); })->whereRaw('last_message_time >= delete_time')->where('unread->admin->' . \$a->id, '>', 0)->sum('unread->admin->' . \$a->id);\n";
$c = substr_count($t, $a); echo ($c === 1 ? '○' : '✕') . " 直す場所 {$c}か所\n"; if ($c !== 1) exit(1);
$n = str_replace($a, $n2, $t); $tmp = '/tmp/cs_badge_m.php'; file_put_contents($tmp, $n); exec('/www/server/php/73/bin/php -l ' . $tmp . ' 2>&1', $o, $rc); @unlink($tmp);
echo ($rc ? "✕ 書き方に間違い\n" : "○ 書き方 問題なし\n"); if ($rc) exit(1);
if (!$apply) { echo "反映するときは apply を付けて実行\n"; exit; }
$bk = '/www/backup_customaid_badge_member_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($f, $bk . 'CsBadge.php'); $s = stat($f); file_put_contents($f, $n); @chown($f, $s['uid']); @chgrp($f, $s['gid']);
exec('kill -USR2 $(pgrep -f "[p]hp-fpm: master") 2>&1');
$b = '/www/wwwroot/api.card-san.jp/'; require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ad = \App\Models\ShopAdminModel::find(33); echo "○ 反映しました。控え {$bk}CsBadge.php\n今の admin 33 のアイコンの数(新しい数え方):" . \App\Libs\CsBadge::count('shop', $ad->getui_clientid) . "\n";
