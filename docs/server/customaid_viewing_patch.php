<?php
// [カスタムエイド 2026-10-27] 開いているチャットの相手からの通知を送らない。確認だけ:引数なし/反映:apply
$apply = ($argv[1] ?? '') === 'apply'; $b = '/www/wwwroot/api.card-san.jp/'; $php = '/www/server/php/73/bin/php'; $ok = true; $out = [];
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$src = '/root/customaid_CsViewingController.php'; $dst = $b . 'app/Http/Controllers/CsViewingController.php';
exec("$php -l " . escapeshellarg($src) . ' 2>&1', $o, $rc); echo $rc ? "✕ 新しいファイルの書き方に間違い\n" : "○ 新しいファイル(CsViewingController)書き方 問題なし\n"; if ($rc) $ok = false;
$R1 = "        Route::middleware('shop.auth')->group(function(){\n"; $R2 = "        Route::middleware('member.auth')->group(function(){\n";
$jobs = [
 'routes/api.php' => [[$R1, $R1 . "            Route::post('/cs_viewing', '\\App\\Http\\Controllers\\CsViewingController@shop'); // [カスタムエイド 2026-10-27] 開いているチャット\n"],
                      [$R2, $R2 . "            Route::post('/cs_viewing', '\\App\\Http\\Controllers\\CsViewingController@member'); // [カスタムエイド 2026-10-27] 開いているチャット\n"]],
 'app/Libs/GeTuiApi.php' => [["        \$csDone = \\App\\Libs\\CsApns::mirror(\$this->type, \$msg_list);\n",
   "        // [カスタムエイド 2026-10-27] 今その相手とのチャットを開いている端末には送らない(LINE と同じ)\n        \$msg_list = \\App\\Http\\Controllers\\CsViewingController::filter(\$this->type, \$msg_list);\n        if (!\$msg_list) return ['success' => true, 'message' => 'viewing', 'data' => []];\n        \$csDone = \\App\\Libs\\CsApns::mirror(\$this->type, \$msg_list);\n"]],
];
foreach ($jobs as $f => $reps) {
  $t = @file_get_contents($b . $f); if ($t === false) { echo "✕ $f がありません\n"; $ok = false; continue; }
  if (strpos($t, 'CsViewingController') !== false) { echo "○ $f もう直してあります\n"; continue; }
  foreach ($reps as [$x, $y]) { $c = substr_count($t, $x); if ($c !== 1) { echo "✕ $f 直す場所が {$c} か所(1か所のはず)\n"; $ok = false; continue 2; } $t = str_replace($x, $y, $t); }
  $tmp = '/tmp/customaid_view_' . basename($f); file_put_contents($tmp, $t); exec("$php -l " . escapeshellarg($tmp) . ' 2>&1', $o2, $rc2); @unlink($tmp);
  if ($rc2) { echo "✕ $f 直したあとの書き方に間違い\n"; $ok = false; continue; }
  echo "○ $f 直す場所・書き方 問題なし\n"; $out[$b . $f] = $t;
}
$rc3 = is_file($b . 'bootstrap/cache/routes-v7.php'); echo $rc3 ? "・ルートのキャッシュあり(反映のとき作り直す)\n" : "・ルートのキャッシュなし\n";
if (!$ok) { echo "\n問題があるので止めます。何も変更していません。\n"; exit(1); }
if (!$apply) { echo "\n確認だけ終わりました。問題がなければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_viewing_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
foreach ($out as $f => $t) { copy($f, $bk . basename($f)); $s = stat($f); file_put_contents($f, $t); @chown($f, $s['uid']); @chgrp($f, $s['gid']); echo "○ " . basename($f) . " を直しました\n"; }
if (!is_file($dst)) { copy($src, $dst); $s = stat($b . 'app/Libs/GeTuiApi.php'); @chown($dst, $s['uid']); @chgrp($dst, $s['gid']); echo "○ CsViewingController.php を置きました\n"; }
if ($rc3) { passthru("cd $b && $php artisan route:cache 2>&1 | tail -2"); }
echo "控え:$bk(元に戻すとき:控えの2つを戻し、$dst を消す)\n";
