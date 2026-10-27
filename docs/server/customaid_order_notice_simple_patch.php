<?php
// [カスタムエイド 2026-10-27 2回目] 予約通知:担当者だけ未読を残すのをやめる(予約を受ける人と処理する人が違い、担当者がアプリを使うとは限らないため。坂本さんの判断)
//   誰か1人が開いたら全員既読(元の動き)。「確認済み(最初に開いた人)」の表示は残す。確認だけ:引数なし/反映:apply
$apply = ($argv[1] ?? '') === 'apply'; $php = '/www/server/php/73/bin/php'; $ok = true; $out = [];
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$x = "        if (!(int) \$notice->is_read) return true;\n";
$y = "        return !(int) \$notice->is_read; // [カスタムエイド 2026-10-27 2回目] お店全体の印だけで決める(担当者だけ未読を残すのはやめた)\n        if (!(int) \$notice->is_read) return true;\n";
foreach (['/www/wwwroot/api.card-san.jp/app/Libs/CsBadge.php', '/root/customaid_CsBadge.php'] as $f) {
  $t = @file_get_contents($f); if ($t === false) { echo "✕ $f がありません\n"; $ok = false; continue; }
  if (strpos($t, '担当者だけ未読を残すのはやめた') !== false) { echo "○ $f もう直してあります\n"; continue; }
  $c = substr_count($t, $x); if ($c !== 1) { echo "✕ $f 直す場所が {$c} か所(1か所のはず)\n"; $ok = false; continue; }
  $n = str_replace($x, $y, $t); file_put_contents('/tmp/cs_ons.php', $n); exec("$php -l /tmp/cs_ons.php 2>&1", $o, $rc); @unlink('/tmp/cs_ons.php');
  if ($rc) { echo "✕ $f 直したあとの書き方に間違い\n"; $ok = false; continue; }
  echo "○ $f 直す場所1か所・書き方 問題なし\n"; $out[$f] = $n;
}
if (!$ok) { echo "\n問題があるので止めます。何も変更していません。\n"; exit(1); }
if (!$apply) { echo "\n確認だけ終わりました。問題がなければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_order_notice_simple_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
foreach ($out as $f => $n) { copy($f, $bk . basename($f)); $s = stat($f); file_put_contents($f, $n); @chown($f, $s['uid']); @chgrp($f, $s['gid']); echo "○ " . basename($f) . " を直しました\n"; }
echo "控え:$bk\n";
