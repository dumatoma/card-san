<?php
// [カスタムエイド 2026-10-28] SMS の通数の数え方・鍵の置き場所を調べる(読むだけ。何も変更しない)。鍵の値は出さない
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b);
function h($t) { echo "\n========== $t ==========\n"; }
function sh($c) { $o = []; exec($c . ' 2>&1', $o); echo implode("\n", $o), "\n"; }
h('1. OrderStartTimeNoticeJob を呼んでいる場所'); sh('grep -rn "OrderStartTimeNoticeJob" app routes --include=*.php | grep -v "^app/Jobs/OrderStartTimeNoticeJob.php"');
h('2. AlibabaCloudSms を使っている場所(全部)'); sh('grep -rn "AlibabaCloudSms())->send\|new AlibabaCloudSms" app --include=*.php | grep -v "\.bak"');
h('3. 通数を数えている場所'); sh('grep -rn "vip_sms_month_count\|vip_sms_count" app --include=*.php | grep -v "\.bak" | cut -c1-220');
h('4. 設定のキャッシュ(ある=env() が読めないことがある)'); sh('ls -la bootstrap/cache/ ; grep -rn "config:cache\|optimize" /www/server/panel/plugin/supervisor/profile/*.ini 2>/dev/null | head -3');
h('5. .env にある設定の名前(値は出さない)'); sh("grep -o '^[A-Z_0-9]*=' .env | tr '\\n' ' '");
h('6. プログラムに直接書いてある鍵らしきもの(場所と名前だけ。値は出さない)');
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($b . 'app')); $it2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($b . 'config'));
foreach ([$it, $it2] as $I) foreach ($I as $f) { if (!preg_match('/\.php$/', $f)) continue; $L = file($f);
    foreach ($L as $n => $s) { if (preg_match('/[\'"][A-Za-z0-9+\/=_.-]{12,}[\'"]/', $s) && preg_match('/([A-Za-z_]*(key|secret|token|password|passwd|appid|app_id|client_id|clientid)[A-Za-z_]*)/i', $s, $m) && !preg_match('/env\(/', $s))
        echo str_replace($b, '', $f) . ':' . ($n + 1) . '  ' . $m[1] . ' (値は伏せる)' . "\n"; } }
h('7. お店の表の通数の欄の型'); require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM web_shop WHERE Field LIKE 'vip_sms%'") as $r) echo "$r->Field $r->Type 初期値=$r->Default\n";
echo "通数が0以下のお店の数:" . Illuminate\Support\Facades\DB::table('web_shop')->where('vip_sms_count', '<=', 0)->count() . "\n";
echo "\n(おわり)\n";
