<?php
// [カスタムエイド 2026-11-03] サーバー①:無料期間の終わり方(31人目から24時間・会員を消しても戻らない)・お客様のアプリの3日の猶予・アプリに渡す状態(cs_notice)
//   使い方(このファイルと同じ場所に CsTrial.php を置く):
//     php -d disable_functions= trialend_patch.php          → 確認だけ(何も変えない)
//     php -d disable_functions= trialend_patch.php apply    → 控えを取ってから変える(web_shop に cs_trial_over_at の列を足す)
//     php -d disable_functions= trialend_patch.php test 店舗ID → そのお店の状態を出す(見るだけ)
//     php -d disable_functions= trialend_patch.php undo     → ファイルを控えに戻す(足した列は残す。値が 0 なら動きに影響なし)
$b = '/www/wwwroot/api.card-san.jp/'; $here = __DIR__ . '/'; $mode = $argv[1] ?? 'check'; $last = '/root/cs_trialend_last_backup.txt';
$M = '[カスタムエイド 2026-11-03]';
function boot($b) { require_once $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); }
if ($mode === 'test') {
    boot($b); $sid = (int)($argv[2] ?? 0); $shop = \App\Models\ShopModel::find($sid);
    if (!$shop) { echo "店舗 $sid が見つかりません\n"; exit(1); }
    $mv = \App\Libs\CsTrial::memberVip($shop); $t31 = \App\Libs\CsTrial::thirtyFirstAt($sid);
    echo "店舗 $sid / 会員 " . $shop->member()->count() . " 人 / 31人目 " . ($t31 ? date('Y-m-d H:i', $t31) : 'なし') . " / 超えた印 " . ((int)$shop->cs_trial_over_at ? date('Y-m-d H:i', $shop->cs_trial_over_at) : 'なし') . "\n";
    echo "お客様のアプリ: " . ($mv ? '使える(契約 id ' . $mv->id . ' ' . date('Y-m-d H:i', $mv->end_time) . ' まで' . ($mv->end_time < time() ? '・猶予中' : '') . ')' : '止まる') . "\n";
    $now = \App\Models\ShopVipModel::where('sid', $sid)->where('start_time', '<=', time())->where('end_time', '>=', time())->where('status', 1)->orderBy('create_time', 'desc')->first();
    $n = \App\Libs\CsTrial::notice($shop, $now);
    foreach ($n as $k => $v) echo "  $k = " . (in_array($k, ['ends_at', 'over_at', 'customer_app_until', 'delete_at']) && $v ? date('Y-m-d H:i', $v) : $v) . "\n";
    exit(0);
}
if ($mode === 'undo') {
    $bk = is_file($last) ? trim(file_get_contents($last)) : '';
    if (!$bk || !is_dir($bk)) { echo "NG 控えが見つかりません\n"; exit(1); }
    foreach (glob($bk . '/*') as $x) { if (strpos(basename($x), 'app__') !== 0) continue; $rel = str_replace('__', '/', basename($x)); copy($x, $b . $rel); echo "戻しました $rel\n"; }
    if (is_file($b . 'app/Libs/CsTrial.php')) { rename($b . 'app/Libs/CsTrial.php', $bk . '/new_CsTrial.php'); echo "外しました app/Libs/CsTrial.php\n"; }
    exit(0);
}
$S = 'app/Http/Controllers/Api/Shop/ShopController.php';
$P = []; // [ファイル, 正規表現, 置きかえ, 説明]
$P[] = [$S, '/if \(\$is_extension && \(\$shop->member\(\)->count\(\) > 30 \|\| time\(\) >= \(int\)\$first_trial->start_time \+ 60 \* 86400\)\) \{\s*\$shop_vip->update\(\[\'end_time\' => time\(\) - 1\]\);\s*\$shop_vip = null;\s*\}/',
  "if (\$is_extension) {\n                // $M 31人目の登録から24時間・登録から60日で終わる(すぐには止めない)\n                \$cs_end = \\App\\Libs\\CsTrial::extensionEnd(\$shop, \$shop_vip, \$first_trial);\n                if (\$cs_end < (int)\$shop_vip->end_time) \$shop_vip->update(['end_time' => \$cs_end]);\n                if (\$cs_end <= time()) \$shop_vip = null;\n            }", 'お店:31人目から24時間'];
$P[] = [$S, '/(\n[ \t]*)if \(\$trial_vip_count >= 1 && \$paid_vip_count === 0 && \(/',
  "\$1\\App\\Libs\\CsTrial::markOverIfTrialOnly(\$shop); // $M 31人を超えて終わったら、会員を消しても延長しない\$1if (\$trial_vip_count >= 1 && \$paid_vip_count === 0 && !(int)\$shop->cs_trial_over_at && (", 'お店:会員を消しても戻らない'];
$P[] = [$S, '/(\n[ \t]*)\$shop->last_vip_is_try = \$last_vip->is_try \?\? 0;/',
  "\$0\$1\$shop->cs_notice = \\App\\Libs\\CsTrial::notice(\$shop, \$shop_vip); // $M 帯・全面のお知らせ・データを消す日", 'お店:アプリに状態(cs_notice)を渡す'];
$MS = 'app/Http/Controllers/Api/Member/ShopController.php';
$P[] = [$MS, '/\$shop_vip = ShopVipModel::where\(\'sid\', \$shop->id\)\s*->where\(\'start_time\', \'<=\', time\(\)\)\s*->where\(\'end_time\', \'>=\', time\(\)\)\s*->where\(\'status\', 1\)\s*->first\(\);/',
  "\$shop_vip = \\App\\Libs\\CsTrial::memberVip(\$shop); // $M 無料期間のあと3日間は使える", 'お客様:お店のページ(3日の猶予)'];
$P[] = [$MS, '/(\n[ \t]*)\$shop->page_footer_icon = \$page_footer_icon;/',
  "\$0\$1\$shop->cs_app_stopped = \$shop_vip ? 0 : 1; // $M 「このお店のアプリは、現在ご利用できません」用", 'お客様:止まっている印'];
$P[] = ['app/Http/Controllers/Api/Member/MenuController.php', '/\$vip = \$menu->shop \? \$menu->shop->vip\(\)\s*->where\(\'start_time\', \'<=\', time\(\)\)\s*->where\(\'end_time\', \'>=\', time\(\)\)\s*->where\(\'status\', 1\)\s*->orderBy\(\'create_time\', \'desc\'\)->first\(\) : null;/',
  "\$vip = \\App\\Libs\\CsTrial::memberVip(\$menu->shop); // $M 無料期間のあと3日間は使える", 'お客様:メニュー(3日の猶予)'];
$P[] = ['app/Http/Controllers/Api/Member/HistoryController.php', '/\$vip = \$shop \? \$shop->vip\(\)\s*->where\(\'start_time\', \'<=\', time\(\)\)\s*->where\(\'end_time\', \'>=\', time\(\)\)\s*->where\(\'status\', 1\)\s*->orderBy\(\'create_time\', \'desc\'\)->first\(\) : null;/',
  "\$vip = \\App\\Libs\\CsTrial::memberVip(\$shop); // $M 無料期間のあと3日間は使える", 'お客様:履歴(3日の猶予)'];

$ng = 0; $src = [];
foreach ($P as $p) {
    if (!isset($src[$p[0]])) $src[$p[0]] = (string)@file_get_contents($b . $p[0]);
    $n = preg_match_all($p[1], $src[$p[0]]);
    echo ($n === 1 ? 'OK ' : 'NG ') . "{$p[3]}({$p[0]}・見つかった数 $n)\n"; if ($n !== 1) $ng++;
}
foreach ($src as $f => $s) if (strpos($s, 'CsTrial::') !== false) { echo "NG もう入っています $f\n"; $ng++; }
$ok = is_file($here . 'CsTrial.php'); echo ($ok ? 'OK ' : 'NG ') . "新しいファイル CsTrial.php(" . ($ok ? md5_file($here . 'CsTrial.php') : 'なし') . ")\n"; if (!$ok) $ng++;
boot($b);
$ok = class_exists('\App\Models\ShopMemberModel') && class_exists('\App\Models\ShopModel'); echo ($ok ? 'OK ' : 'NG ') . "会員・お店の部品(ShopMemberModel・ShopModel)\n"; if (!$ok) $ng++;
$ok = \Illuminate\Support\Facades\Schema::hasColumn('web_shop_member', 'create_time'); echo ($ok ? 'OK ' : 'NG ') . "会員の登録時刻の列\n"; if (!$ok) $ng++;
$has = \Illuminate\Support\Facades\Schema::hasColumn('web_shop', 'cs_trial_over_at'); echo "列 web_shop.cs_trial_over_at:" . ($has ? 'もうある' : 'まだない(apply で足す)') . "\n";
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if ($mode !== 'apply') { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }

$bk = '/www/backup_customaid_trialend_' . date('Ymd_His'); mkdir($bk, 0700, true); file_put_contents($last, $bk);
foreach (array_keys($src) as $f) copy($b . $f, $bk . '/' . str_replace('/', '__', $f));
if (!$has) { \Illuminate\Support\Facades\DB::statement("ALTER TABLE web_shop ADD COLUMN cs_trial_over_at INT UNSIGNED NOT NULL DEFAULT 0"); echo "列を足しました web_shop.cs_trial_over_at\n"; }
$new = $src; foreach ($P as $p) $new[$p[0]] = preg_replace($p[1], $p[2], $new[$p[0]], 1);
$own = fileowner($b . 'app/Libs/ApplePay.php'); $grp = filegroup($b . 'app/Libs/ApplePay.php');
foreach ($new as $f => $s) file_put_contents($b . $f, $s);
copy($here . 'CsTrial.php', $b . 'app/Libs/CsTrial.php'); @chown($b . 'app/Libs/CsTrial.php', $own); @chgrp($b . 'app/Libs/CsTrial.php', $grp); @chmod($b . 'app/Libs/CsTrial.php', 0644);
$bad = 0;
foreach (array_merge(array_keys($new), ['app/Libs/CsTrial.php']) as $f) { $o = []; exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r); echo ($r === 0 ? '文法OK ' : '文法NG ') . "$f\n"; if ($r !== 0) $bad++; }
if ($bad) { foreach (array_keys($src) as $f) copy($bk . '/' . str_replace('/', '__', $f), $b . $f); @unlink($b . 'app/Libs/CsTrial.php'); echo "文法NG のため元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "\n変えました。控え: $bk\n戻すときは: php -d disable_functions= " . __FILE__ . " undo\n次に: php -d disable_functions= " . __FILE__ . " test 7(テスト店舗)\n";
