<?php
// [カスタムエイド 2026-10-27] 予約通知の送り分け 段階1:今の作りを読むだけ(何も変更しない)。秘密の値は出さない
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b);
function h($t) { echo "\n========== $t ==========\n"; }
function sh($c) { $o = []; exec($c . ' 2>&1', $o); echo implode("\n", $o), "\n"; }
h('1. SMS の文を作る処理(CsSmsText)'); sh('cat -n app/Libs/CsSmsText.php');
h('2. 予約を受け付ける処理(Member OrderController の store と SMS)'); sh('grep -n "function \|CsSmsText\|sendSms\|Sms::\|sms\|vip_sms\|order_confirm\|dispatch\|->save()\|::create(" app/Http/Controllers/Api/Member/OrderController.php | head -60');
h('3. お店側の予約の処理(Shop OrderController)'); sh('grep -n "function \|CsSmsText\|sendSms\|sms\|cancel\|delete\|status\|dispatch" app/Http/Controllers/Api/Shop/OrderController.php | head -80');
h('4. リマインド・サンキューの仕事'); sh('grep -n "function \|CsSmsText\|sendSms\|sms\|where\|dispatch" app/Jobs/OrderNoticeJob.php app/Jobs/OrderThankMessageJob.php | head -50');
h('5. SMS を送る部品'); sh('grep -rln "Dysmsapi\|sendSms\|AlibabaCloud" app | head; grep -rn "function sendSms\|function send(" app/Libs app/Services 2>/dev/null | head');
h('6. ルート(予約)'); sh('grep -n "order" routes/api.php | head -40');
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = config('database.connections.mysql.database'); $q = function ($s, $p = []) { return Illuminate\Support\Facades\DB::select($s, $p); };
h('7. 予約の表 web_order の欄'); foreach ($q("SELECT COLUMN_NAME c, COLUMN_TYPE t FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME='web_order'", [$db]) as $r) echo "$r->c($r->t) ";
echo "\n予約の数:" . $q("SELECT COUNT(*) n FROM web_order")[0]->n . " / これからの予約:" . $q("SELECT COUNT(*) n FROM web_order WHERE start_time > UNIX_TIMESTAMP()")[0]->n . "(start_time が数字のとき)\n";
h('8. 欄の名前に status・cancel・email・notify・short・confirm・remind を含むもの(全部の表)');
foreach ($q("SELECT TABLE_NAME t, COLUMN_NAME c, COLUMN_TYPE ty FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND (COLUMN_NAME LIKE '%cancel%' OR COLUMN_NAME LIKE '%email%' OR COLUMN_NAME LIKE '%notify%' OR COLUMN_NAME LIKE '%short%' OR COLUMN_NAME LIKE '%confirm%' OR COLUMN_NAME LIKE '%remind%' OR (TABLE_NAME LIKE '%order%' AND COLUMN_NAME LIKE '%status%'))", [$db]) as $r) echo "$r->t.$r->c($r->ty)\n";
h('9. 予約の見本(最新1件。名前・電話・メール・メモは伏せる)'); $o = $q("SELECT * FROM web_order ORDER BY id DESC LIMIT 1"); if ($o) foreach ((array) $o[0] as $k => $v) echo "  $k = " . (preg_match('/name|phone|tel|mail|remark|note|addr|memo/i', $k) ? '(伏せる)' : mb_substr((string) $v, 0, 80)) . "\n";
h('10. メールの設定(パスワードは出さない)'); foreach (['default', 'mailers.smtp.host', 'mailers.smtp.port', 'mailers.smtp.encryption', 'mailers.smtp.username', 'from.address', 'from.name'] as $k) echo "mail.$k = " . json_encode(config("mail.$k"), JSON_UNESCAPED_UNICODE) . "\n";
echo "queue = " . config('queue.default') . "\n"; sh('ls app/Mail 2>&1 | head; ls resources/views/emails resources/views/mail 2>&1 | head; grep -rn "Mail::to\|Mail::send" app | head');
h('11. キューの常駐'); sh('cat /www/server/panel/plugin/supervisor/profile/queue.ini 2>/dev/null | grep -v "^;" | head -15; ps aux | grep "[q]ueue:" | head -3');
h('12. order.card-san.jp の nginx と置き場所'); sh('f=$(grep -ln "server_name[^;]*order.card-san.jp" /www/server/panel/vhost/nginx/*.conf); echo $f; grep -n "server_name\|root \|location\|rewrite\|return \|try_files\|include" $f; ls /www/server/panel/vhost/nginx/extension/order.card-san.jp/ 2>&1');
sh('r=$(grep -h "^\s*root " $(grep -ln "server_name[^;]*order.card-san.jp" /www/server/panel/vhost/nginx/*.conf) | head -1 | awk "{print \$2}" | tr -d ";"); echo "root=$r"; ls -la $r | head -20; ls $r/reserve 2>/dev/null | head; ls $r/reserve/static/js 2>/dev/null | head -40');
echo "\n(おわり)\n";
