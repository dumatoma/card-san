<?php
// [カスタムエイド 2026-10-28] 一覧が空の調査 その3(読むだけ。何も変更しない)。鍵らしい値は伏せる
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b);
function h($t) { echo "\n========== $t ==========\n"; }
function show($f, $a, $z) { $L = @file($b = $f); if (!$L) { echo "(ない) $f\n"; return; } for ($i = $a; $i <= min($z, count($L)); $i++) { $s = rtrim($L[$i - 1]); $s = preg_replace('/([\'"])[A-Za-z0-9+\/=_-]{16,}([\'"])/', '$1(伏せる)$2', $s); printf("%4d: %s\n", $i, $s); } }
h('1. 管理App の通信の入口の決まり(ApiRequest)'); show('app/Http/Middleware/ApiRequest.php', 1, 80);
h('2. 管理App のログインの確かめ方(ShopAuth)'); show('app/Http/Middleware/ShopAuth.php', 1, 60);
h('3. メッセージ一覧を作る処理'); show('app/Http/Controllers/Api/Shop/MessageController.php', 25, 75);
h('4. 通信の記録が残っている表・ファイル'); echo shell_exec('ls -la storage/logs | tail -n 15');
echo "\n(おわり)\n";
