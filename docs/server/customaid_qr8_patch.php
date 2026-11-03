<?php
// [カスタムエイド 2026-10-27] 店舗QR統一 段階8:全店舗の QR を https://card-san.jp/s/店舗ID に作り直す。確認だけ:引数なし/反映:apply
//   新しい画像を別の名前(店舗ID-cs1027.png)で作り、web_shop.qrcode をそれに変える(古い画像は消さない。アプリに古い画像が残らないよう名前を変える)
$apply = ($argv[1] ?? '') === 'apply'; $base = '/www/wwwroot/api.card-san.jp/'; chdir($base);
require $base . 'vendor/autoload.php'; $app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$rows = DB::table('web_shop')->select('id', 'qrcode')->orderBy('id')->get(); $plan = []; $ok = true;
foreach ($rows as $r) {
  $q = (string) $r->qrcode; $pre = 'https://api.card-san.jp/storage/qrcode/shop/';
  if (strpos($q, $pre) !== 0 && $q !== '') { echo "✕ id={$r->id} 今の qrcode の形が違う: $q\n"; $ok = false; continue; }
  $name = $r->id . '-cs1027.png'; $plan[] = [$r->id, $q, $pre . $name, $name];
  echo "○ id={$r->id} " . ($q ? basename($q) : '(なし)') . " → $name(中身 https://card-san.jp/s/{$r->id})\n";
}
echo "\n-- 新しいお店を作るときの QR(次に直す所。参考)\n";
$o = []; exec('grep -rn -B2 -A2 "qrcode()" app --include=*.php | grep -v "Helpers/functions.php" | head -40', $o); echo implode("\n", $o), "\n";
if (!$apply || !$ok) { echo $ok ? "\n問題なし。反映するときは apply を付けて実行\n" : "\n✕ 問題があるので反映しません\n"; exit; }
$bk = '/www/backup_customaid_qr8_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
exec('cp -rp storage/app/public/qrcode/shop ' . escapeshellarg($bk . 'shop'));
file_put_contents($bk . 'web_shop_qrcode.json', json_encode($rows, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
foreach ($plan as [$id, $old, $new, $name]) {
  \App\Services\QrcodeService::_init()->create('https://card-san.jp/s/' . $id, 'shop/' . $name);
  if (!is_file('storage/app/public/qrcode/shop/' . $name)) { echo "✕ id=$id 画像を作れませんでした(ここで止めます)\n"; exit(1); }
  DB::table('web_shop')->where('id', $id)->update(['qrcode' => $new]); echo "○ id=$id $name\n";
}
echo "\n完了。控え $bk(元の画像と web_shop.qrcode の元の値)\n";
