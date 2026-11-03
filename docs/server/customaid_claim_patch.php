<?php
// [カスタムエイド 2026-10-27] 店舗QR統一:お店のページを見ずにアプリを入れた人に「ペースト」の画面を出さない。確認だけ:引数なし/反映:apply
//   お店のページを開いたことを、接続元(IP を暗号化した値)と時刻で30分だけ覚える(/api/cs/mark/{id})。コピーとペーストは今までどおり。
//   アプリを初めて開いたとき /api/cs/claim で聞き、見ていたときだけペーストの画面を出す(見ていなければ「ようこそ!」)
$apply = ($argv[1] ?? '') === 'apply'; $base = '/www/wwwroot/api.card-san.jp/'; $ok = true;
$fc = $base . 'app/Http/Controllers/CsShopPageController.php'; $fr = $base . 'routes/web.php';
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$t = file_get_contents($fc); $r = file_get_contents($fr);
if (strpos($t, 'function claim(') !== false) { echo "○ もう直してあります\n"; exit; }
$a2 = ". 'function go(){location.href=as}'\n";
$b2 = $a2 . "            // [カスタムエイド 2026-10-27] このページを開いたことをサーバーに記録(アプリを初めて開いたとき、ペーストの画面を出すかを決める)\n"
 . "            . 'function mk(k){try{fetch(\"https://api.card-san.jp/api/cs/mark/\"+id+\"?k=\"+k,{mode:\"no-cors\",keepalive:true,credentials:\"omit\",cache:\"no-store\"})}catch(e){}}mk(\"view\");'\n";
$a3 = "    private function notFound()";
$b3 = file_get_contents('/root/customaid_claim_methods.txt') . "\n" . $a3;
$c2 = substr_count($t, $a2); $c3 = substr_count($t, $a3);
echo ($c2 === 1 ? '○' : '✕') . " 開いたことの記録を入れる場所 {$c2}か所\n" . ($c3 === 1 ? '○' : '✕') . " 照合の処理を入れる場所 {$c3}か所\n";
if ($c2 !== 1 || $c3 !== 1) { echo "✕ 反映しません\n"; exit(1); }
$n = str_replace($a3, $b3, str_replace($a2, $b2, $t));
$rt = "\n// [カスタムエイド 2026-10-27] 店舗QR統一:お店のページを見たことの記録と、アプリを初めて開いたときの照合\n"
 . "Route::get('/api/cs/mark/{id}', '\\App\\Http\\Controllers\\CsShopPageController@mark')->where('id', '[0-9]+');\n"
 . "Route::get('/api/cs/claim', '\\App\\Http\\Controllers\\CsShopPageController@claim');\n";
if (strpos($r, 'CsShopPageController@claim') !== false) $rt = '';
$tmp = '/tmp/customaid_claim_ctl.php'; file_put_contents($tmp, $n); exec('/www/server/php/73/bin/php -l ' . $tmp . ' 2>&1', $o, $rc); @unlink($tmp);
echo ($rc ? "✕ 書き方に間違い " . implode(' ', $o) : "○ 直したあとの書き方 問題なし") . "\n"; if ($rc) exit(1);
if (!$apply) { echo "\n反映するときは apply を付けて実行\n"; exit; }
$bk = '/www/backup_customaid_claim_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($fc, $bk . 'CsShopPageController.php'); copy($fr, $bk . 'web.php');
$st = stat($fc); file_put_contents($fc, $n); @chown($fc, $st['uid']); @chgrp($fc, $st['gid']);
if ($rt) file_put_contents($fr, $r . $rt, LOCK_EX);
exec('kill -USR2 $(pgrep -f "[p]hp-fpm: master") 2>&1');
echo "○ 反映しました。控え $bk(php-fpm を読み直しました)\n確かめ方:curl -s https://api.card-san.jp/api/cs/claim?p=ios\n";
