<?php
// [カスタムエイド 2026-10-27] smid をそのまま SQL に入れていた2か所を直す(SQL インジェクション対策)。確認だけ:引数なし/反映:apply
$apply = ($argv[1] ?? '') === 'apply'; $base = '/www/wwwroot/api.card-san.jp/'; $ok = true; $out = [];
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$jobs = [
 'app/Http/Controllers/Api/Member/OrderController.php' => ["whereRaw('FIND_IN_SET(' . \$request->smid . ', smids)')", "whereRaw('FIND_IN_SET(?, smids)', [(int) \$request->smid]) /* [カスタムエイド 2026-10-27] SQL インジェクション対策 */"],
 'app/Http/Controllers/Api/Shop/AdminController.php' => ["orWhereRaw('FIND_IN_SET(' . \$request->smid . ', smids)')", "orWhereRaw('FIND_IN_SET(?, smids)', [(int) \$request->smid]) /* [カスタムエイド 2026-10-27] SQL インジェクション対策 */"],
];
foreach ($jobs as $f => [$a, $b]) {
  $t = @file_get_contents($base . $f);
  if ($t === false) { echo "✕ $f がありません\n"; $ok = false; continue; }
  if (strpos($t, 'FIND_IN_SET(?, smids)') !== false) { echo "○ $f もう直してあります\n"; continue; }
  $c = substr_count($t, $a);
  if ($c !== 1) { echo "✕ $f 直す場所が {$c} か所(1か所のはず)\n"; $ok = false; continue; }
  $n = str_replace($a, $b, $t); $tmp = '/tmp/customaid_sqli_' . basename($f); file_put_contents($tmp, $n);
  exec('/www/server/php/73/bin/php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $rc); @unlink($tmp);
  if ($rc) { echo "✕ $f 直したあとの書き方に間違い\n"; $ok = false; continue; }
  echo "○ $f 直す場所1か所・書き方 問題なし\n"; $out[$base . $f] = $n;
}
echo "\n--- ほかに、値をそのまま SQL につないでいる所(参考)\n";
passthru("grep -rn \"raw(.*\\. *\\\$\\|Raw(.*\\. *\\\$\\|raw(\\\".*\\\$\" {$base}app --include=*.php -i | grep -v '\\.bak' | grep -v 'FIND_IN_SET(?, smids)' | head -20");
if (!$ok) { echo "\n問題があるので止めます。何も変更していません。\n"; exit(1); }
if (!$apply) { echo "\n確認だけ終わりました。問題がなければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_sqli_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
foreach ($out as $f => $n) { copy($f, $bk . basename($f)); $s = stat($f); file_put_contents($f, $n); @chown($f, $s['uid']); @chgrp($f, $s['gid']); echo "○ " . basename($f) . " を直しました\n"; }
echo "控え:$bk\n";
