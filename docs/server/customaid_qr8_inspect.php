<?php
// [カスタムエイド 2026-10-27] 店舗QR統一 段階8の準備:読むだけ(何も変更しない)。店舗QRの作り方・保存場所・今の中身を見る
$base = '/www/wwwroot/api.card-san.jp/'; chdir($base);
function sh($c) { $o = []; exec($c . ' 2>&1', $o); echo implode("\n", $o), "\n"; }
echo "== 1. QrcodeService\n"; sh('cat -n app/Services/QrcodeService.php | head -80');
echo "\n== 2. QrcodeService を使っている場所\n"; sh('grep -rn "QrcodeService\|QrCode::" app routes --include=*.php | head -20');
echo "\n== 3. 店舗登録で QR を作る所\n"; sh('grep -n -B3 -A12 "QrcodeService" app/Http/Controllers/Api/Shop/AuthController.php | head -40');
require $base . 'vendor/autoload.php'; $app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
echo "\n== 4. qrcode という名前の欄\n";
$db = config('database.connections.mysql.database');
foreach (DB::select("SELECT TABLE_NAME t, COLUMN_NAME c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND COLUMN_NAME LIKE '%qr%'", [$db]) as $r) {
  echo "{$r->t}.{$r->c}  件数:" . DB::table($r->t)->whereNotNull($r->c)->where($r->c, '<>', '')->count() . "\n";
  foreach (DB::table($r->t)->whereNotNull($r->c)->where($r->c, '<>', '')->limit(3)->get() as $x) echo "   id=" . ($x->id ?? '?') . " " . mb_substr($x->{$r->c}, 0, 120) . "\n";
}
echo "\n== 5. お店の数・店舗QRの画像\n"; echo "web_shop: " . DB::table('web_shop')->count() . " 件(id " . DB::table('web_shop')->min('id') . "〜" . DB::table('web_shop')->max('id') . ")\n";
sh('ls storage/app/public/qrcode/shop | head -5; ls storage/app/public/qrcode/shop | wc -l; ls -la public/storage 2>&1 | head -2');
echo "\n== 6. 試しに URL の QR を /tmp に作れるか(本番の画像には触らない)\n";
try { $p = '/tmp/customaid_qr8_test.png'; \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(300)->margin(1)->generate('https://card-san.jp/s/11', $p); echo is_file($p) ? "○ 作れました " . filesize($p) . " バイト\n" : "✕ できませんでした\n"; } catch (\Throwable $e) { echo "✕ " . $e->getMessage() . "\n"; }
sh('file storage/app/public/qrcode/shop/$(ls storage/app/public/qrcode/shop | head -1)');
echo "\n(おわり)\n";
