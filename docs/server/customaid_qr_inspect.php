<?php
// [カスタムエイド 2026-10-26] 店舗QR統一の準備:今の作りを読むだけ(何も変更しない)。秘密の値は出さない
//   ・card-san.jp / order.card-san.jp / api.card-san.jp の nginx の設定(どこに何が置いてあるか、/s/ と /.well-known/ が空いているか)
//   ・店舗QRを作る処理と、読み取ったあとの API(ShopController@qrcode)
//   ・お店の名前・ロゴ・カード画像がどの表のどの欄にあるか
//   ・店舗QRの画像の置き場所と件数
// 使い方:php -d disable_functions= /root/customaid_qr_inspect.php > /root/qr_inspect.txt 2>&1 ; wc -l /root/qr_inspect.txt
$base = '/www/wwwroot/api.card-san.jp/';
chdir($base);
function h($t) { echo "\n========== $t ==========\n"; }
function sh($c) { $o = []; exec($c . ' 2>&1', $o); echo implode("\n", $o), "\n"; }
function mask($s) { return preg_replace('/\b[A-Za-z0-9+\/]{32,}\b/', '***', $s); }
function around($f, $re, $before = 3, $after = 40, $max = 3) {
    global $base;
    $L = @file($base . $f);
    if (!$L) { echo "($f がありません)\n"; return; }
    $n = 0;
    foreach ($L as $i => $l) {
        if (preg_match($re, $l)) {
            echo "--- $f:" . ($i + 1) . "\n";
            for ($j = max(0, $i - $before); $j < min(count($L), $i + $after); $j++) echo ($j + 1) . ': ' . mask(rtrim($L[$j])) . "\n";
            if (++$n >= $max) break;
        }
    }
    if (!$n) echo "($f に見つかりません: $re)\n";
}

h('1. nginx のサイト一覧');
sh('ls -la /www/server/panel/vhost/nginx/');
foreach (['card-san.jp', 'www.card-san.jp', 'order.card-san.jp', 'api.card-san.jp'] as $d) {
    foreach (glob('/www/server/panel/vhost/nginx/*' . $d . '*.conf') as $f) {
        echo "--- $f\n";
        sh('grep -n "server_name\|root \|location\|rewrite\|return \|try_files\|proxy_pass\|include\|listen" ' . escapeshellarg($f));
    }
}
echo "\n-- server_name に card-san.jp を含む設定\n";
sh('grep -ln "server_name[^;]*card-san.jp" /www/server/panel/vhost/nginx/*.conf');

h('2. card-san.jp のドキュメントルートの中(一番上だけ)');
$roots = [];
foreach (glob('/www/server/panel/vhost/nginx/*.conf') as $f) {
    $t = file_get_contents($f);
    if (preg_match('/server_name[^;]*\bcard-san\.jp\b/', $t) && preg_match('/^\s*root\s+([^;]+);/m', $t, $m)) $roots[$f] = trim($m[1]);
}
foreach ($roots as $f => $r) {
    echo "--- $f → $r\n";
    sh('ls -la ' . escapeshellarg($r) . ' | head -40');
    sh('ls -la ' . escapeshellarg($r . '/.well-known') . ' 2>&1 | head');
    sh('ls -d ' . escapeshellarg($r . '/s') . ' 2>&1');
}

h('3. 外から見た /s/11 と /.well-known/apple-app-site-association(今の返事)');
foreach (['https://card-san.jp/s/11', 'https://card-san.jp/.well-known/apple-app-site-association', 'https://card-san.jp/.well-known/assetlinks.json', 'https://card-san.jp/apple-app-site-association'] as $u) {
    $c = curl_init($u);
    curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_HEADER => true, CURLOPT_NOBODY => false, CURLOPT_FOLLOWLOCATION => false]);
    $b = curl_exec($c); $i = curl_getinfo($c);
    echo "$u → HTTP {$i['http_code']} " . ($i['redirect_url'] ? '→ ' . $i['redirect_url'] : '') . ' / ' . ($i['content_type'] ?? '') . "\n";
    curl_close($c);
}

h('4. 店舗QRを作る処理');
around('app/Services/QrcodeService.php', '/function /', 2, 30, 5);
sh('grep -rn "QrcodeService\|qrcode" app/Http/Controllers/Api/Shop/AuthController.php | head');
around('app/Http/Controllers/Api/Shop/AuthController.php', '/QrcodeService|qrcode/', 5, 20, 2);

h('5. 読み取ったあとの API(Member ShopController@qrcode)とルート');
sh('grep -n "qrcode\|shop/" routes/api.php | head -30');
around('app/Http/Controllers/Api/Member/ShopController.php', '/function qrcode/', 2, 50, 1);

h('6. お店の名前・ロゴ・カード画像の欄');
require_once $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = config('database.connections.mysql.database');
foreach (['web_shop', 'web_shop_card'] as $t) {
    $c = Illuminate\Support\Facades\DB::select("SELECT COLUMN_NAME c, COLUMN_TYPE ty FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=?", [$db, $t]);
    echo "$t: " . implode(', ', array_map(function ($x) { return $x->c . '(' . $x->ty . ')'; }, $c)) . "\n\n";
}
echo "-- 店舗 11 の見本(名前・画像の欄だけ)\n";
$s = Illuminate\Support\Facades\DB::table('web_shop')->where('id', 11)->first();
if ($s) foreach ((array) $s as $k => $v) if (preg_match('/name|logo|image|img|avatar|qrcode|carousel|status|hide/i', $k)) echo "  $k = " . mb_substr((string) $v, 0, 120) . "\n";
$cd = Illuminate\Support\Facades\DB::table('web_shop_card')->where('sid', 11)->first();
if ($cd) foreach ((array) $cd as $k => $v) if (preg_match('/name|logo|image|img|bg|color|type|qrcode|frame|stamp/i', $k)) echo "  card.$k = " . mb_substr((string) $v, 0, 120) . "\n";
echo "\n-- 店舗の数:" . Illuminate\Support\Facades\DB::table('web_shop')->count() . "\n";

h('7. 店舗QRの画像の置き場所と件数');
sh('ls storage/app/public/qrcode 2>&1 | head; ls storage/app/public/qrcode/shop 2>/dev/null | wc -l; ls public/storage/qrcode/shop 2>/dev/null | head -3');
sh('grep -rn "qrcode" config/filesystems.php | head -5');

h('8. アプリ用の設定ファイルの置き場所の候補(api.card-san.jp の public)');
sh('ls -la public | head -30; ls -la public/.well-known 2>&1 | head');

echo "\n(おわり)\n";
