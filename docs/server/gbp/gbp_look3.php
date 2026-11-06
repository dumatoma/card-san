<?php
// [カスタムエイド 2026-11-07] GBP 連携(メニュー・お知らせ・クーポン)の開発のため、サーバーのコードの「写し」を作る(見るだけ・何も変えない)
//   php -d disable_functions= /root/gbp_look3.php
//   → /root/gbp_look3_20261107.tgz ができる
//   入れるもの:メニュー・お知らせ・クーポン・会員登録(カード作成)・店舗ページ(/s/)のプログラム、表の作り(件数だけ。お客様の中身は入れない)
//   鍵のような長い文字は「[伏せ字]」にしてから入れる。.env は入れない
$base = '/www/wwwroot/api.card-san.jp/';
$out = '/root/gbp_look3_20261107';
$tgz = $out . '.tgz';
if (is_dir($out)) exec('rm -rf ' . escapeshellarg($out));
mkdir($out, 0700, true);
$mask = function ($s) {
    $s = preg_replace('/ya29\.[A-Za-z0-9_\-\.]+/', '[伏せ字]', $s);
    $s = preg_replace('/1\/\/[A-Za-z0-9_\-]{20,}/', '[伏せ字]', $s);
    $s = preg_replace_callback('/(["\'])([A-Za-z0-9_\-+=]{24,})\1/', function ($m) {
        $v = $m[2];
        if (!preg_match('/\d/', $v) || !preg_match('/[A-Za-z]/', $v) || preg_match('/^[a-z_]+\d*$/', $v)) return $m[0];
        return $m[1] . '[伏せ字]' . $m[1];
    }, $s);
    $s = preg_replace('/(access_token|refresh_token|client_secret|secret|password)(["\']?\s*[=:]>?\s*["\']?)[A-Za-z0-9_\-\.\/+=]{12,}/i', '$1$2[伏せ字]', $s);
    return $s;
};

// 1. メニュー・お知らせ・クーポン・会員登録・店舗ページに関係するファイル
$list = [];
exec('cd ' . escapeshellarg($base) . ' && grep -rliE "menu|notice|coupon|bindcard|bind_card|CardController|MemberCard|shop_member|CsShopPage|/s/" app routes config resources/views --include=*.php 2>/dev/null', $list);
$list = array_values(array_filter($list, function ($f) { return !preg_match('/\.bak|backup|_old|\/vendor\/|lang\//i', $f); }));
foreach (['app/Console/Kernel.php', 'app/Services/GbpService.php', 'app/Libs/GoogleBusiness.php', 'app/Http/Controllers/Api/Shop/GoogleBusinessController.php', 'routes/api.php', 'routes/web.php'] as $f) if (is_file($base . $f) && !in_array($f, $list)) $list[] = $f;
foreach ($list as $f) {
    $dst = $out . '/code/' . $f;
    if (!is_dir(dirname($dst))) mkdir(dirname($dst), 0700, true);
    file_put_contents($dst, $mask(file_get_contents($base . $f)));
}
file_put_contents($out . '/files.txt', implode("\n", $list) . "\n");

// 2. 表の作り(件数だけ)
require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = '';
try {
    foreach (Illuminate\Support\Facades\DB::select("SHOW TABLES") as $t) {
        $name = array_values((array) $t)[0];
        if (!preg_match('/menu|notice|coupon|card|member|vip_config|shop$/i', $name)) continue;
        $c = Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE `{$name}`");
        $db .= "-- {$name}(件数 " . Illuminate\Support\Facades\DB::table($name)->count() . ")\n" . array_values((array) $c[0])[1] . ";\n\n";
    }
} catch (\Throwable $e) { $db .= 'DB を読めませんでした:' . $e->getMessage() . "\n"; }
file_put_contents($out . '/db.txt', $db);

// 3. 道の一覧
$o = [];
exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:list 2>&1 | grep -iE "menu|notice|coupon|card|member|/s/"', $o);
file_put_contents($out . '/routes.txt', implode("\n", $o) . "\n");

exec('cd /root && tar czf ' . escapeshellarg($tgz) . ' ' . escapeshellarg(basename($out)));
chmod($tgz, 0600);
echo "ファイル " . count($list) . " 個を入れました\n";
echo "できたもの:{$tgz}(" . round(filesize($tgz) / 1024) . " KB)md5 " . md5_file($tgz) . "\n";
echo "鍵は伏せ字にしてあります。.env は入れていません。お客様の中身は入れていません(件数だけ)。\n";
