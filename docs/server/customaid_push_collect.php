<?php
// [カスタムエイド 2026-10-27b] 通知まわりを全部調べるため、関係するところと記録を1つの文字のファイルにまとめる(読むだけ。サーバーは何も変えない)。秘密の値は *** にする
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b); $o = '/root/cs_push_collect.txt'; $w = '';
$mask = function ($t) { $t = preg_replace('/((key|secret|master|password|passwd|token|appid|app_id|appkey|app_key|sign)[A-Za-z_]*[\'"]?\s*(=>|=|:)\s*)([\'"])[^\'"]{6,}\4/i', '$1$4***$4', $t); return preg_replace('/-----BEGIN[^-]+-----.*?-----END[^-]+-----/s', '***PEM***', $t); };
$sh = function ($c) { $r = []; exec($c . ' 2>&1', $r); return implode("\n", $r) . "\n"; };
foreach (['app/Libs/GeTuiApi.php', 'app/Libs/CsApns.php', 'app/Libs/CsBadge.php', 'app/Http/Controllers/CsViewingController.php'] as $f) $w .= "\n##### FILE $f\n" . $mask($sh("cat -n $f"));
$w .= "\n##### CALLS(通知を送る場所)\n" . $mask($sh("grep -rn -B12 -A4 'pushSingleBatchCid\\|new GeTuiApi\\|GeTuiApi::' app --include=*.php | grep -v '^app/Libs/GeTuiApi.php'"));
$w .= "\n##### BADGE(アプリが数を読む場所)\n" . $mask($sh("grep -rn -A25 'unread_num' app/Http/Controllers/Api/Member/MemberController.php | head -60"));
$d = date('Y-m-d');
$w .= "\n##### LOG cs_apns 今日\n" . $sh("tail -120 storage/logs/cs_apns/$d.log | cut -c1-260");
$w .= "\n##### LOG laravel 今日(通知)\n" . $sh("grep -iE 'getui|push|apns|badge' storage/logs/laravel-$d.log | tail -60 | cut -c1-260");
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$q = function ($s) { return Illuminate\Support\Facades\DB::select($s); };
foreach (['web_user', 'web_shop_admin'] as $tb) {
  $w .= "\n##### DEVICES $tb(端末の番号は先頭だけ、Apple の番号は最後の8文字だけ)\n" . json_encode($q("SELECT id, LEFT(getui_clientid,12) cid, cs_push_platform p, IF(cs_apns_token<>'',RIGHT(cs_apns_token,8),'') apns, cs_push_at FROM $tb WHERE getui_clientid IS NOT NULL AND getui_clientid<>'' ORDER BY cs_push_at DESC LIMIT 25"), JSON_UNESCAPED_UNICODE) . "\n";
  $w .= "同じ Apple の番号を持つ行:" . json_encode($q("SELECT RIGHT(cs_apns_token,8) apns, COUNT(*) n, GROUP_CONCAT(id) ids FROM $tb WHERE cs_apns_token IS NOT NULL AND cs_apns_token<>'' GROUP BY cs_apns_token HAVING n>1")) . "\n";
}
$w .= "\n##### CRON\n" . $sh("crontab -l | grep -v '^#'");
file_put_contents($o, $w); $n = count(file($o));
echo "できました:$o($n 行)\n300行ずつ、次で表示します:\n"; for ($i = 1; $i <= $n; $i += 300) echo "sed -n '$i," . ($i + 299) . "p' $o\n";
