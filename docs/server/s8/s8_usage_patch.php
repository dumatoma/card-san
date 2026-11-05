<?php
// [カスタムエイド 2026-11-03] 段階8-2:「ご利用状況」の入口 GET /api/shop/cs/usage(新しいファイル1つ+ routes/api.php に1行)
// 使い方(このファイル1つを /root/ に置く):
//   php -d disable_functions= /root/s8_usage_patch.php          … 確かめるだけ
//   php -d disable_functions= /root/s8_usage_patch.php apply    … バックアップを取ってから変更する(終わったら店舗11 の結果を出す)
//   php -d disable_functions= /root/s8_usage_patch.php undo     … 元に戻す

const CTL_SRC = <<<'CSCTLSRC'
<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Libs\CsReserveCh;
use Illuminate\Support\Facades\DB;

/**
 * [カスタムエイド 2026-11-03] 段階8-2:管理App「ご利用状況」
 *  GET /api/shop/cs/usage  今月の予約SMS・メッセージ・クーポンの残り(月の数・追加でご購入の分)、数が戻る日、オプション。だれでも見られる(副管理者も)
 *  数は web_shop の vip_*_month_count(今月の残り)・vip_*_count(追加でご購入の分)。月の数は webConfig(AutoRecoverVipCount と同じ)
 */
class CsUsageController extends Controller
{
    public function show()
    {
        $sid = auth('shop')->user()->sid;
        $r = DB::table('web_shop')->where('id', (int) $sid)->first(['vip_sms_month_count', 'vip_sms_count', 'vip_message_month_count', 'vip_message_count', 'vip_coupon_month_count', 'vip_coupon_count']);
        if (!$r) return responseToJson(400, '店舗が見つかりません');
        $cfg = function ($k, $d) { try { $v = webConfig()->get($k); return is_numeric($v) && (int) $v > 0 ? (int) $v : $d; } catch (\Throwable $e) { return $d; } };
        $day = 0;
        try { $day = (int) \App\Models\ShopVipModel::where('sid', (int) $sid)->orderBy('id', 'desc')->value('settlement_day'); } catch (\Throwable $e) { $day = 0; }
        $item = function ($left, $extra, $month) { return ['month' => $month, 'left' => (int) $left, 'extra' => (int) $extra]; };
        $ch = CsReserveCh::get($sid);
        return responseToJson(200, '', [
            'reset_day' => $day,
            'sms' => $item($r->vip_sms_month_count, $r->vip_sms_count, $cfg('vip_sms_month_count', 100)),
            'message' => $item($r->vip_message_month_count, $r->vip_message_count, $cfg('vip_message_month_count', 3000)),
            'coupon' => $item($r->vip_coupon_month_count, $r->vip_coupon_count, $cfg('vip_coupon_month_count', 3000)),
            'opt_sms' => $ch['opt_sms'],
            'opt_line' => $ch['opt_line'],
        ]);
    }
}
CSCTLSRC;

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_s8usage_20261103/';
$mode = $argv[1] ?? 'check';
$ctlPath = 'app/Http/Controllers/Api/Shop/CsUsageController.php';
$fr = 'routes/api.php';
$clearRoutes = function () use ($base) {
    foreach (glob($base . 'bootstrap/cache/routes*.php') ?: [] as $rc) { $o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:cache 2>&1', $o); echo implode("\n", $o) . "\n"; return; }
};
if ($mode === 'undo') {
    if (!is_file($backup . 'api.php')) exit("バックアップがありません。何もしていません。\n");
    copy($backup . 'api.php', $base . $fr); if (is_file($base . $ctlPath)) unlink($base . $ctlPath); $clearRoutes();
    exit("元に戻しました。\n");
}
require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
if (!class_exists('App\Libs\CsReserveCh')) $ng('CsReserveCh がありません(段階8-1 の修正が先に要ります)');
if (file_exists($base . $ctlPath)) $ng("{$ctlPath} がすでにあります");
foreach (['vip_sms_month_count', 'vip_message_month_count', 'vip_coupon_month_count'] as $k) { $v = webConfig()->get($k); echo "月の数 {$k} = " . var_export($v, true) . "\n"; }
$sr = file_get_contents($base . $fr);
$anchor = "            Route::post('/cs/reserve-ch', 'CsReserveChController@save');";
if (substr_count($sr, $anchor) !== 1) $ng('routes/api.php に cs/reserve-ch の行が見つかりません');
if (strpos($sr, 'CsUsageController') !== false) $ng('すでに修正済みです');
$sr2 = str_replace($anchor, $anchor . "\n            Route::get('/cs/usage', 'CsUsageController@show'); // [カスタムエイド 2026-11-03] 段階8-2:ご利用状況", $sr);
$tmp = sys_get_temp_dir() . '/cs_s8u_'; file_put_contents($tmp . 'c.php', CTL_SRC); file_put_contents($tmp . 'r.php', $sr2);
foreach (['c.php', 'r.php'] as $n) { $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp . $n) . ' 2>&1', $o, $c); echo ($c === 0 ? 'OK ' : 'NG ') . "直したあとの文法 {$n}\n"; if ($c !== 0) $ng(implode(' ', $o)); }
if ($mode !== 'apply') { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
copy($base . $fr, $backup . 'api.php');
file_put_contents($base . $ctlPath, CTL_SRC); file_put_contents($base . $fr, $sr2);
$ok = true;
foreach ([$ctlPath, $fr] as $f) { $o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c); echo ($c === 0 ? 'OK ' : 'NG ') . "文法 {$f}\n"; if ($c !== 0) $ok = false; }
if (!$ok) { copy($backup . 'api.php', $base . $fr); unlink($base . $ctlPath); exit("文法エラーがあったので元に戻しました。\n"); }
$ref = $base . 'app/Http/Controllers/Api/Shop/CsReserveChController.php';
@chown($base . $ctlPath, fileowner($ref)); @chgrp($base . $ctlPath, filegroup($ref));
$clearRoutes();
$r = Illuminate\Support\Facades\DB::table('web_shop')->where('id', 11)->first(['vip_sms_month_count', 'vip_sms_count', 'vip_message_month_count', 'vip_message_count', 'vip_coupon_month_count', 'vip_coupon_count']);
echo "店舗11 の数:" . json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
echo "\n完了しました。バックアップ: {$backup}\n元に戻すとき: php -d disable_functions= /root/s8_usage_patch.php undo\n";
