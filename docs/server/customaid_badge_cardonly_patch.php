<?php
// [カスタムエイド 2026-10-27] Card-San App のバッジ:カードを持っているお店の未読だけ数える(カードを消したお店の未読が残って数えられていた)。確認だけ:引数なし/反映:apply
//   アプリが開いたときに聞く数(Member/MemberController の unread_num)と、通知に入れる数(CsBadge)の両方をそろえて直す
$apply = ($argv[1] ?? '') === 'apply'; $b = '/www/wwwroot/api.card-san.jp/'; $php = '/www/server/php/73/bin/php'; $ok = true; $out = [];
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$C = "[カスタムエイド 2026-10-27] カードを持っているお店の未読だけ数える(カードを消したお店の未読が残って数えられていた)";
$jobs = [
 $b . 'app/Http/Controllers/Api/Member/MemberController.php' => [
  ["            \$unread_num = GroupModel::where('uid', auth()->id())->where('unread->member', '>', 0)->sum('unread->member');\n", "            \$unread_num = 0; // $C\n"],
  ["                \$unread_num += MemberCouponModel::where('sid', \$member->sid)", "                \$csG = GroupModel::where('uid', auth()->id())->where('sid', \$member->sid)->first(); \$unread_num += (int) (\$csG->unread['member'] ?? 0); // $C\n                \$unread_num += MemberCouponModel::where('sid', \$member->sid)"]],
 '/root/customaid_CsBadge.php' => [
  ["        \$n = (int) \\App\\Models\\GroupModel::where('uid', \$u->id)->where('unread->member', '>', 0)->sum('unread->member');\n", "        \$n = 0; // $C\n"],
  ["            \$n += \\App\\Models\\MemberCouponModel::where(", "            \$csG = \\App\\Models\\GroupModel::where('uid', \$u->id)->where('sid', \$m->sid)->first(); \$n += (int) (\$csG->unread['member'] ?? 0);\n            \$n += \\App\\Models\\MemberCouponModel::where("]],
];
if (is_file($b . 'app/Libs/CsBadge.php')) $jobs[$b . 'app/Libs/CsBadge.php'] = $jobs['/root/customaid_CsBadge.php'];
foreach ($jobs as $f => $reps) {
  $t = @file_get_contents($f); if ($t === false) { echo "✕ $f がありません\n"; $ok = false; continue; }
  if (strpos($t, 'カードを持っているお店の未読だけ') !== false) { echo "○ " . basename($f) . " もう直してあります\n"; continue; }
  foreach ($reps as [$x, $y]) { $c = substr_count($t, $x); if ($c !== 1) { echo "✕ " . basename($f) . " 直す場所が {$c} か所(1か所のはず)\n"; $ok = false; continue 2; } $t = str_replace($x, $y, $t); }
  $tmp = '/tmp/customaid_co_' . basename($f); file_put_contents($tmp, $t); exec("$php -l " . escapeshellarg($tmp) . ' 2>&1', $o, $rc); @unlink($tmp);
  if ($rc) { echo "✕ " . basename($f) . " 直したあとの書き方に間違い\n"; $ok = false; continue; }
  echo "○ $f 直す場所2か所・書き方 問題なし\n"; $out[$f] = $t;
}
if (!$ok) { echo "\n問題があるので止めます。何も変更していません。\n"; exit(1); }
if (!$apply) { echo "\n確認だけ終わりました。問題がなければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_badge_cardonly_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
foreach ($out as $f => $t) { copy($f, $bk . str_replace('/', '_', ltrim($f, '/'))); $s = stat($f); file_put_contents($f, $t); @chown($f, $s['uid']); @chgrp($f, $s['gid']); echo "○ " . basename($f) . " を直しました\n"; }
echo "控え:$bk\n";
