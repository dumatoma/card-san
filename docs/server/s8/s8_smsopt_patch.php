<?php
// [カスタムエイド 2026-11-03] 段階8-1:SMS オプションの仕組み(予約の受け取り方法 メール・SMS)
//   1. 欄を足す:web_shop.cs_opt_sms(0)・reserve_ch_mail(1)・reserve_ch_sms(0)
//   2. app/Libs/CsReserveCh.php(新規。決まり:オプションがなければメールだけ/全部オフにはできない)
//   3. app/Http/Controllers/Api/Shop/CsReserveChController.php(新規)と routes/api.php の入口2つ(GET/POST cs/reserve-ch)
//   4. Member/ShopController(show):予約WEB に受け付ける方法(cs_ch)を渡す
//   5. Member/OrderController(store):お客様が選んだ方法(cs_ch = mail/sms)で受け付ける。SMS を選んだらメールは保存しない(→ SMS で届く)
// 使い方(このファイル1つを /root/ に置く):
//   php -d disable_functions= /root/s8_smsopt_patch.php               … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/s8_smsopt_patch.php apply         … バックアップを取ってから変更する
//   php -d disable_functions= /root/s8_smsopt_patch.php opt 11 on     … 試験用:店舗11 に「SMS オプション契約中」の印(off で外す)
//   php -d disable_functions= /root/s8_smsopt_patch.php undo          … 元に戻す(足した欄はそのまま。使われなくなるだけ)

const LIB_SRC = <<<'CSLIBSRC'
<?php

namespace App\Libs;

use Illuminate\Support\Facades\DB;

/**
 * [カスタムエイド 2026-11-03] 段階8-1:予約の受け取り方法(メール・SMS。LINE は段階9)
 *  web_shop の欄:cs_opt_sms(1 = SMS オプション契約中)/ reserve_ch_mail(1 = メールで受け付ける)/ reserve_ch_sms(1 = SMS で受け付ける)
 *  決まり(サーバーでも守る):
 *   ・SMS のオプションがないときは、メールは必ずオン・SMS は必ずオフ(プランを下げてオプションが外れたら、自然にメールだけに戻る)
 *   ・受け付ける方法を全部オフにはできない
 *  申し込み・お支払い(段階8-3)ができるまでは、cs_opt_sms はサーバーで手で付ける(試験用のお店だけ)
 */
class CsReserveCh
{
    const SMS_MONTH = 100; // 1か月の予約SMS の数(プランの注意書きと同じ)

    private static function row($sid)
    {
        try {
            return DB::table('web_shop')->where('id', (int) $sid)->first(['id', 'cs_opt_sms', 'reserve_ch_mail', 'reserve_ch_sms', 'vip_sms_month_count', 'vip_sms_count']);
        } catch (\Throwable $e) { return null; }
    }

    // 今、受け付けている方法(決まりを当てはめたあと)
    public static function get($sid)
    {
        $r = self::row($sid);
        $opt = $r && (int) $r->cs_opt_sms === 1;
        $mail = $opt ? ((int) $r->reserve_ch_mail === 1) : true;
        $sms = $opt && (int) $r->reserve_ch_sms === 1;
        if (!$mail && !$sms) $mail = true;
        return ['mail' => $mail ? 1 : 0, 'sms' => $sms ? 1 : 0, 'line' => 0, 'opt_sms' => $opt ? 1 : 0, 'opt_line' => 0];
    }

    // お店が変える。戻り値 ['ok' => bool, 'msg' => 文]
    public static function set($sid, $mail, $sms)
    {
        $r = self::row($sid);
        if (!$r) return ['ok' => false, 'msg' => '店舗が見つかりません'];
        $opt = (int) $r->cs_opt_sms === 1;
        if (!$opt && !$mail) return ['ok' => false, 'msg' => 'SMS・LINE のオプションがないときは、メールはオフにできません'];
        if (!$opt && $sms) return ['ok' => false, 'msg' => 'SMS のオプションをご契約ではありません'];
        if (!$mail && !$sms) return ['ok' => false, 'msg' => '受け付ける方法を1つ以上オンにしてください'];
        DB::table('web_shop')->where('id', (int) $sid)->update(['reserve_ch_mail' => $mail ? 1 : 0, 'reserve_ch_sms' => $sms ? 1 : 0]);
        return ['ok' => true, 'msg' => '受け付ける方法を変えました'];
    }

    // 今月の予約SMS の残り(数が戻る日 = ご契約の起算日)
    public static function smsInfo($sid)
    {
        $r = self::row($sid);
        $day = 0;
        try { $day = (int) \App\Models\ShopVipModel::where('sid', (int) $sid)->orderBy('id', 'desc')->value('settlement_day'); } catch (\Throwable $e) { $day = 0; }
        return [
            'month' => self::SMS_MONTH,
            'left' => $r ? max(0, (int) $r->vip_sms_month_count) : 0,
            'extra' => $r ? (int) $r->vip_sms_count : 0,
            'reset_day' => $day,
        ];
    }

    // 試験用:オプションの印を手で付ける・外す
    public static function setOpt($sid, $on)
    {
        return DB::table('web_shop')->where('id', (int) $sid)->update(['cs_opt_sms' => $on ? 1 : 0]);
    }
}
CSLIBSRC;
const CTL_SRC = <<<'CSCTLSRC'
<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Libs\CsReserveCh;
use Illuminate\Http\Request;

/**
 * [カスタムエイド 2026-11-03] 段階8-1:管理App「予約Webサイト」の受け付ける方法(メール・SMS)
 *  GET  /api/shop/cs/reserve-ch   今の状態と今月の予約SMS の残り(だれでも見られる)
 *  POST /api/shop/cs/reserve-ch   変える(mail = 1/0, sms = 1/0)。管理者と「予約設定」(8)の権限がある副管理者だけ
 */
class CsReserveChController extends Controller
{
    private function canEdit($u)
    {
        if ((int) ($u->admin_type ?? 1) !== 2) return true;
        $p = $u->privileges ?? [];
        if (!is_array($p)) $p = json_decode((string) $p, true) ?: [];
        return in_array(8, $p) || in_array('8', $p, true);
    }

    private function out($sid, $msg = '')
    {
        $ch = CsReserveCh::get($sid);
        $ch['sms_info'] = CsReserveCh::smsInfo($sid);
        $ch['can_edit'] = $this->canEdit(auth('shop')->user()) ? 1 : 0;
        return responseToJson(200, $msg, $ch);
    }

    public function show()
    {
        return $this->out(auth('shop')->user()->sid);
    }

    public function save(Request $request)
    {
        $u = auth('shop')->user();
        if (!$this->canEdit($u)) return responseToJson(403, '予約設定の権限がありません');
        $r = CsReserveCh::set($u->sid, (int) $request->input('mail', 1) === 1, (int) $request->input('sms', 0) === 1);
        if (!$r['ok']) return responseToJson(400, $r['msg']);
        return $this->out($u->sid, $r['msg']);
    }
}
CSCTLSRC;

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_s8smsopt_20261103/';
$mode = $argv[1] ?? 'check';
$tag = '[カスタムエイド 2026-11-03]';
$libPath = 'app/Libs/CsReserveCh.php';
$ctlPath = 'app/Http/Controllers/Api/Shop/CsReserveChController.php';
$fr = 'routes/api.php';
$fs = 'app/Http/Controllers/Api/Member/ShopController.php';
$fo = 'app/Http/Controllers/Api/Member/OrderController.php';
$files = [$fr, $fs, $fo];
$bk = function ($f) use ($backup) { return $backup . str_replace('/', '__', $f); };
$clearRoutes = function () use ($base) {
    foreach (glob($base . 'bootstrap/cache/routes*.php') ?: [] as $rc) { $o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:cache 2>&1', $o); echo implode("\n", $o) . "\n"; return; }
};

if ($mode === 'undo') {
    if (!is_dir($backup)) exit("バックアップがありません。何もしていません。\n");
    foreach ($files as $f) if (is_file($bk($f))) { copy($bk($f), $base . $f); echo "元に戻しました: {$f}\n"; }
    foreach ([$libPath, $ctlPath] as $p) if (is_file($base . $p)) { unlink($base . $p); echo "消しました: {$p}\n"; }
    $clearRoutes();
    exit("元に戻しました(足した欄はそのまま残っていますが、使われません)。\n");
}

require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };

if ($mode === 'opt') {
    $sid = (int) ($argv[2] ?? 0); $on = ($argv[3] ?? '') === 'on';
    if (!$sid || !in_array($argv[3] ?? '', ['on', 'off'], true)) exit("使い方:opt 店舗ID on|off\n");
    if (!class_exists('App\Libs\CsReserveCh')) exit("先に apply してください\n");
    App\Libs\CsReserveCh::setOpt($sid, $on);
    echo "店舗{$sid}:SMS オプション " . ($on ? 'ご契約中(試験)' : '未契約') . " にしました\n";
    echo "今の状態:" . json_encode(App\Libs\CsReserveCh::get($sid) + ['sms_info' => App\Libs\CsReserveCh::smsInfo($sid)], JSON_UNESCAPED_UNICODE) . "\n";
    exit;
}

$db = Illuminate\Support\Facades\DB::connection(); $pre = $db->getTablePrefix();
$cols = array_map(function ($c) { return $c->Field; }, $db->select("SHOW COLUMNS FROM `{$pre}web_shop`"));
foreach (['vip_sms_month_count', 'vip_sms_count'] as $c) if (!in_array($c, $cols)) $ng("web_shop に {$c} がありません");
$add = array_values(array_diff(['cs_opt_sms', 'reserve_ch_mail', 'reserve_ch_sms'], $cols));
echo "① 欄:web_shop に " . ($add ? implode(', ', $add) . ' を足す' : '(もうある)') . "\n";
if (!class_exists('App\Models\ShopVipModel')) $ng('ShopVipModel がありません');
foreach ([$libPath, $ctlPath] as $p) if (file_exists($base . $p)) $ng("{$p} がすでにあります");

// routes
$sr = file_get_contents($base . $fr);
$anchor = "            Route::post('/cs/reserve-mail/bcc', 'CsReserveMailController@bcc');";
if (substr_count($sr, $anchor) !== 1) $ng('routes/api.php に cs/reserve-mail/bcc の行が見つかりません');
if (strpos($sr, 'CsReserveChController') !== false) $ng('routes/api.php はすでに修正済みです');
$sr2 = str_replace($anchor, $anchor . "\n            // {$tag} 段階8-1:受け付ける方法(メール・SMS)\n            Route::get('/cs/reserve-ch', 'CsReserveChController@show');\n            Route::post('/cs/reserve-ch', 'CsReserveChController@save');", $sr);
echo "② {$fr}:入口を2つ足す\n";

// Member ShopController
$ss = file_get_contents($base . $fs);
if (strpos($ss, 'cs_ch') !== false) $ng('Member/ShopController はすでに修正済みです');
$re = '/\n([ \t]*)\$shop->cs_app_stopped = [^\n]*\n/';
if (preg_match_all($re, $ss) !== 1) $ng('Member/ShopController の cs_app_stopped の行が見つかりません');
$ss2 = preg_replace_callback($re, function ($m) use ($tag) { return $m[0] . $m[1] . "\$shop->cs_ch = \\App\\Libs\\CsReserveCh::get(\$shop->id); // {$tag} 予約WEB:受け付ける方法(メール・SMS)\n"; }, $ss, 1);
echo "③ {$fs}:予約WEB に受け付ける方法を渡す\n";

// Member OrderController
$so = file_get_contents($base . $fo);
if (strpos($so, 'CsReserveCh') !== false) $ng('Member/OrderController はすでに修正済みです');
$a = strpos($so, "            \$cs_email = \\App\\Libs\\CsShopMail::clean(\$request->email ?? '');\n");
$endKey = "            \$data['email'] = \$cs_email;\n";
$b = $a === false ? false : strpos($so, $endKey, $a);
if ($a === false || $b === false || substr_count($so, $endKey) !== 1) $ng('Member/OrderController のメールアドレスの所が見つかりません');
$old = substr($so, $a, $b + strlen($endKey) - $a);
$ind = function ($t) { return implode("\n", array_map(function ($l) { return $l === '' ? '' : '    ' . $l; }, explode("\n", rtrim($t, "\n")))) . "\n"; };
$new = "            // {$tag} 段階8-1:お客様が選んだ受け取り方法(cs_ch = mail / sms)。お店がオンにした方法だけ受け付ける\n"
    . "            \$cs_chs = \\App\\Libs\\CsReserveCh::get(\$menu->sid);\n"
    . "            \$cs_pick = (string) (\$request->cs_ch ?? '');\n"
    . "            if (\$cs_pick !== 'mail' && \$cs_pick !== 'sms') \$cs_pick = \$cs_chs['mail'] ? 'mail' : 'sms';\n"
    . "            if (\$cs_pick === 'sms') {\n"
    . "                if (!\$cs_chs['sms']) {\n                    return responseToJson(400, 'このお店では、SMS での受け取りは選べません');\n                }\n"
    . "                if (trim((string) (\$request->phone ?? '')) === '') {\n                    return responseToJson(400, '電話番号を入力してください');\n                }\n"
    . "                \$data['email'] = ''; // メールアドレスは保存しない → SMS で届く\n"
    . "            } else {\n"
    . "                if (!\$cs_chs['mail']) {\n                    return responseToJson(400, 'このお店では、メールでの受け取りは選べません');\n                }\n"
    . $ind($old)
    . "            }\n";
$so2 = substr($so, 0, $a) . $new . substr($so, $b + strlen($endKey));
echo "④ {$fo}:選んだ方法で受け付ける\n";

$tmp = sys_get_temp_dir() . '/cs_s8_';
$chk = ['lib.php' => LIB_SRC, 'ctl.php' => CTL_SRC, 'r.php' => $sr2, 's.php' => $ss2, 'o.php' => $so2];
foreach ($chk as $n => $src) { file_put_contents($tmp . $n, $src); $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp . $n) . ' 2>&1', $o, $c); echo ($c === 0 ? 'OK ' : 'NG ') . "直したあとの文法 {$n}\n"; if ($c !== 0) $ng(implode(' ', $o)); }

if ($mode !== 'apply') { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($files as $f) copy($base . $f, $bk($f));
$defs = [];
if (in_array('cs_opt_sms', $add)) $defs[] = "ADD `cs_opt_sms` TINYINT NOT NULL DEFAULT 0";
if (in_array('reserve_ch_mail', $add)) $defs[] = "ADD `reserve_ch_mail` TINYINT NOT NULL DEFAULT 1";
if (in_array('reserve_ch_sms', $add)) $defs[] = "ADD `reserve_ch_sms` TINYINT NOT NULL DEFAULT 0";
if ($defs) $db->statement("ALTER TABLE `{$pre}web_shop` " . implode(', ', $defs));
echo "欄を足しました\n";
file_put_contents($base . $libPath, LIB_SRC); file_put_contents($base . $ctlPath, CTL_SRC);
file_put_contents($base . $fr, $sr2); file_put_contents($base . $fs, $ss2); file_put_contents($base . $fo, $so2);
$ok = true;
foreach (array_merge([$libPath, $ctlPath], $files) as $f) { $o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c); echo ($c === 0 ? 'OK ' : 'NG ') . "文法 {$f}\n"; if ($c !== 0) $ok = false; }
if (!$ok) {
    foreach ($files as $f) copy($bk($f), $base . $f);
    unlink($base . $libPath); unlink($base . $ctlPath);
    exit("文法エラーがあったので元に戻しました(足した欄はそのままですが、使われません)。\n");
}
$ref = $base . 'app/Http/Controllers/Api/Shop/CsReserveMailController.php';
foreach ([$libPath, $ctlPath] as $p) { @chown($base . $p, fileowner($ref)); @chgrp($base . $p, filegroup($ref)); }
$clearRoutes();
$o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:list 2>&1 | grep reserve-ch', $o);
echo "入口の確かめ:\n" . (implode("\n", $o) ?: '見つかりません(確認が必要)') . "\n";
echo "店舗11 の状態:" . json_encode(App\Libs\CsReserveCh::get(11) + ['sms_info' => App\Libs\CsReserveCh::smsInfo(11)], JSON_UNESCAPED_UNICODE) . "\n";
echo "\n完了しました。バックアップ: {$backup}\n元に戻すとき: php -d disable_functions= /root/s8_smsopt_patch.php undo\n";
