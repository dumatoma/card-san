<?php
// [カスタムエイド 2026-11-03] 段階7:予約Webサイトの「メール」のブロック(管理App から予約受付用メールアドレスを登録・確認・写しのオン/オフ)
//   1. 欄を足す:web_shop.reserve_email_new(変更中の新しいアドレス。確認が済むまで今のアドレスを使い続ける)
//   2. app/Libs/CsShopMail.php を入れ替え(変更中のアドレス・status・resend を足した。今のファイルが 10/30 に入れたものと同じときだけ)
//   3. app/Http/Controllers/Api/Shop/CsReserveMailController.php(新規)
//   4. routes/api.php:お店の入口 4つ(cs/reserve-mail …)を「購入を復元」の下に足す
// 使い方(このファイル1つを /root/ に置く):
//   php -d disable_functions= /root/s7_reservemail_patch.php          … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/s7_reservemail_patch.php apply    … バックアップを取ってから変更する
//   php -d disable_functions= /root/s7_reservemail_patch.php undo     … 元に戻す(足した欄はそのまま。使われなくなるだけ)


const LIB_SRC = <<<'CSLIBSRC'
<?php

namespace App\Libs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// [カスタムエイド 2026-10-30] 段階6:お店の「予約受付用メールアドレス」(決定事項 26〜28)。
//   登録したら確認のメールを送り、リンクを押してもらって使えるようにする。確認が済んだアドレスだけを
//   予約メールの返信先(ReplyAddress)・写し(BccAddress。お店ごとにオン・オフ)・「お店にメールする」ボタンに使う。
//   表 web_shop の欄:reserve_email / reserve_email_ok(1=確認済み) / reserve_email_token / reserve_email_sent(確認のメールを送った時刻) / reserve_bcc_on(1=写しを送る)
// [カスタムエイド 2026-11-03] 段階7:reserve_email_new(変更中の新しいアドレス)。確認済みのアドレスを変えるときは、新しいアドレスの確認が済むまで今のアドレスを使い続ける。
//   お店の画面用に status()・resend() を足した
class CsShopMail
{
    const TOKEN_DAYS = 3; // 確認のリンクが使える日数

    public static function clean($v)
    {
        $v = mb_convert_kana((string) $v, 'as', 'UTF-8'); // 全角の英数字・記号・空白を半角に
        $v = preg_replace('/\s+/u', '', $v);
        $at = strrpos($v, '@'); // @ の後ろ(ドメイン)だけ小文字に
        return $at === false ? $v : substr($v, 0, $at) . '@' . strtolower(substr($v, $at + 1));
    }

    public static function valid($v)
    {
        return $v !== '' && strlen($v) <= 100 && filter_var($v, FILTER_VALIDATE_EMAIL) !== false;
    }

    private static function row($sid)
    {
        try {
            return DB::table('web_shop')->where('id', (int) $sid)->first(['id', 'shop_name', 'reserve_email', 'reserve_email_ok', 'reserve_bcc_on', 'reserve_email_new', 'reserve_email_sent']);
        } catch (\Throwable $e) { return null; }
    }

    // 確認が済んだ予約受付用メールアドレス(ないときは空)
    public static function shopMail($sid)
    {
        $r = self::row($sid);
        return ($r && (int) $r->reserve_email_ok === 1 && self::valid((string) $r->reserve_email)) ? (string) $r->reserve_email : '';
    }

    // 写しを送る先(確認済み・オンのときだけ)
    public static function bcc($sid)
    {
        $r = self::row($sid);
        return ($r && (int) $r->reserve_bcc_on === 1) ? self::shopMail($sid) : '';
    }

    // 登録(変更)して確認のメールを送る。戻り値 ['ok' => bool, 'msg' => 文]
    // [カスタムエイド 2026-11-03] 確認済みのアドレスがあるときは、新しいアドレスを reserve_email_new に入れ、確認が済むまで今のアドレスを使い続ける
    public static function request($sid, $email)
    {
        $email = self::clean($email);
        if (!self::valid($email)) return ['ok' => false, 'msg' => 'メールアドレスの形が正しくありません'];
        $r = self::row($sid);
        if (!$r) return ['ok' => false, 'msg' => '店舗が見つかりません'];
        $token = bin2hex(random_bytes(20)); // 40文字
        $curOk = (int) $r->reserve_email_ok === 1 && self::valid((string) $r->reserve_email);
        if ($curOk && $email === (string) $r->reserve_email) {
            DB::table('web_shop')->where('id', (int) $sid)->update(['reserve_email_new' => '', 'reserve_email_token' => '']);
            return ['ok' => true, 'msg' => 'このメールアドレスは確認済みです'];
        }
        if ($curOk) {
            DB::table('web_shop')->where('id', (int) $sid)->update(['reserve_email_new' => $email, 'reserve_email_token' => $token, 'reserve_email_sent' => time()]);
        } else {
            DB::table('web_shop')->where('id', (int) $sid)->update(['reserve_email' => $email, 'reserve_email_new' => '', 'reserve_email_ok' => 0, 'reserve_email_token' => $token, 'reserve_email_sent' => time()]);
        }
        $url = rtrim((string) env('CS_SHOP_MAIL_OK_BASE', 'https://api.card-san.jp/cs-mail-ok/'), '/') . '/' . $token;
        $shop = (string) $r->shop_name;
        $e = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
        $html = '<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"></head>'
            . '<body style="margin:0;background:#F5F5F7;font-family:-apple-system,BlinkMacSystemFont,\'Hiragino Sans\',Meiryo,sans-serif;color:#1D1D1F;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px 12px;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border-radius:14px;"><tr><td style="padding:24px 18px;font-size:15px;line-height:1.7;">'
            . '<div style="font-size:17px;font-weight:bold;margin-bottom:12px;">予約受付用メールアドレスの確認</div>'
            . $e($shop) . ' 様<br><br>Card-San の予約受付用メールアドレスとして、このアドレスが登録されました。<br>下のボタンを押すと、登録が完了します。<br><br>'
            . '<a href="' . $e($url) . '" style="display:block;text-align:center;padding:14px;border-radius:12px;background:#2B8A87;color:#ffffff;font-weight:bold;text-decoration:none;">メールアドレスを確認する</a><br>'
            . '<span style="font-size:13px;color:#6E6E73;">ボタンが押せないときは、次のアドレスを開いてください。<br><a href="' . $e($url) . '" style="color:#2C6BD6;word-break:break-all;">' . $e($url) . '</a><br><br>'
            . 'このリンクは ' . self::TOKEN_DAYS . ' 日間使えます。<br>登録すると、お客様が予約のメールに返信したとき、このアドレスに届きます。<br>お心当たりがないときは、このメールを捨ててください。</span>'
            . '</td></tr></table><div style="font-size:12px;color:#6E6E73;padding-top:14px;">このメールは送信専用のアドレスから送っています。<br>Card-San</div></td></tr></table></body></html>';
        $text = $shop . " 様\n\nCard-San の予約受付用メールアドレスとして、このアドレスが登録されました。\n次のアドレスを開くと、登録が完了します。\n\n" . $url
            . "\n\nこのリンクは " . self::TOKEN_DAYS . " 日間使えます。\n登録すると、お客様が予約のメールに返信したとき、このアドレスに届きます。\nお心当たりがないときは、このメールを捨ててください。\n\n--\nこのメールは送信専用のアドレスから送っています。\nCard-San";
        $ok = CsReserveMail::dm($email, '【Card-San】予約受付用メールアドレスの確認', $html, $text, 'Card-San');
        Log::info('CsShopMail request sid=' . (int) $sid . ' sent=' . ($ok ? 1 : 0));
        return $ok ? ['ok' => true, 'msg' => '確認のメールを送りました'] : ['ok' => false, 'msg' => '確認のメールを送れませんでした'];
    }

    // 確認のリンクを押したとき。戻り値 ['ok' => bool, 'shop' => 店舗名, 'email' => アドレス]
    public static function verify($token)
    {
        if (!preg_match('/^[0-9a-f]{40}$/', (string) $token)) return ['ok' => false, 'shop' => '', 'email' => ''];
        $r = DB::table('web_shop')->where('reserve_email_token', $token)->first(['id', 'shop_name', 'reserve_email', 'reserve_email_new', 'reserve_email_sent']);
        if (!$r || (int) $r->reserve_email_sent < time() - self::TOKEN_DAYS * 86400) return ['ok' => false, 'shop' => '', 'email' => ''];
        // [カスタムエイド 2026-11-03] 変更中の新しいアドレスがあれば、それを使うアドレスにする
        $email = (string) $r->reserve_email_new !== '' ? (string) $r->reserve_email_new : (string) $r->reserve_email;
        DB::table('web_shop')->where('id', $r->id)->update(['reserve_email' => $email, 'reserve_email_new' => '', 'reserve_email_ok' => 1, 'reserve_email_token' => '']);
        Log::info('CsShopMail verified sid=' . (int) $r->id);
        return ['ok' => true, 'shop' => (string) $r->shop_name, 'email' => $email];
    }

    // [カスタムエイド 2026-11-03] お店の画面用:今の状態
    //   state:none(未登録)/ wait(確認待ち)/ ok(確認済み)。ok のときに new があれば「変更中(新しいアドレスの確認待ち)」
    public static function status($sid)
    {
        $r = self::row($sid);
        if (!$r) return null;
        $ok = (int) $r->reserve_email_ok === 1 && self::valid((string) $r->reserve_email);
        $email = (string) $r->reserve_email;
        $new = (string) ($r->reserve_email_new ?? '');
        $sent = (int) ($r->reserve_email_sent ?? 0);
        $state = $ok ? 'ok' : ($email !== '' ? 'wait' : 'none');
        $waiting = ($state === 'wait') || ($ok && $new !== '');
        return [
            'state' => $state,
            'email' => $email,
            'new_email' => $ok ? $new : '',
            'sent' => $sent ? date('n月j日 H:i', $sent) : '',
            'expired' => $waiting && $sent > 0 && $sent < time() - self::TOKEN_DAYS * 86400 ? 1 : 0,
            'bcc_on' => (int) $r->reserve_bcc_on === 1 ? 1 : 0,
            'mail_on' => 1,      // お客様にメールで送る(SMS・LINE のオプションができるまでは、いつもオン)
            'mail_locked' => 1,  // オプションがないのでオフにできない
            'need' => $ok ? 0 : 1, // 予約の画面に知らせを出す
        ];
    }

    // [カスタムエイド 2026-11-03] 確認のメールを送り直す(変更中なら新しいアドレス、確認待ちなら今のアドレスへ)
    public static function resend($sid)
    {
        $r = self::row($sid);
        if (!$r) return ['ok' => false, 'msg' => '店舗が見つかりません'];
        $new = (string) ($r->reserve_email_new ?? '');
        if ((int) $r->reserve_email_ok === 1 && $new !== '') return self::request($sid, $new);
        if ((int) $r->reserve_email_ok !== 1 && (string) $r->reserve_email !== '') return self::request($sid, (string) $r->reserve_email);
        return ['ok' => false, 'msg' => '送り直すメールアドレスがありません'];
    }

    // 写しのオン・オフ
    public static function setBcc($sid, $on)
    {
        return DB::table('web_shop')->where('id', (int) $sid)->update(['reserve_bcc_on' => $on ? 1 : 0]);
    }
}
CSLIBSRC;
const CTL_SRC = <<<'CSCTLSRC'
<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Libs\CsShopMail;
use Illuminate\Http\Request;

/**
 * [カスタムエイド 2026-11-03] 段階7:管理App「予約Webサイト」の「メール」のブロック
 *  GET  /api/shop/cs/reserve-mail         今の状態(だれでも見られる。予約の画面の知らせにも使う)
 *  POST /api/shop/cs/reserve-mail         登録・変更して確認のメールを送る(email)
 *  POST /api/shop/cs/reserve-mail/resend  確認のメールを送り直す
 *  POST /api/shop/cs/reserve-mail/bcc     写しのオン・オフ(on = 1/0)
 *  変えられるのは、管理者と「予約設定」(8)の権限がある副管理者だけ
 */
class CsReserveMailController extends Controller
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
        $st = CsShopMail::status($sid);
        if (!$st) return responseToJson(400, '店舗が見つかりません');
        $st['can_edit'] = $this->canEdit(auth('shop')->user()) ? 1 : 0;
        return responseToJson(200, $msg, $st);
    }

    public function show()
    {
        return $this->out(auth('shop')->user()->sid);
    }

    public function save(Request $request)
    {
        $u = auth('shop')->user();
        if (!$this->canEdit($u)) return responseToJson(403, '予約設定の権限がありません');
        $r = CsShopMail::request($u->sid, (string) $request->input('email', ''));
        if (!$r['ok']) return responseToJson(400, $r['msg']);
        return $this->out($u->sid, $r['msg']);
    }

    public function resend()
    {
        $u = auth('shop')->user();
        if (!$this->canEdit($u)) return responseToJson(403, '予約設定の権限がありません');
        $r = CsShopMail::resend($u->sid);
        if (!$r['ok']) return responseToJson(400, $r['msg']);
        return $this->out($u->sid, $r['msg']);
    }

    public function bcc(Request $request)
    {
        $u = auth('shop')->user();
        if (!$this->canEdit($u)) return responseToJson(403, '予約設定の権限がありません');
        CsShopMail::setBcc($u->sid, (int) $request->input('on', 0) === 1);
        return $this->out($u->sid, (int) $request->input('on', 0) === 1 ? '写しを受け取るようにしました' : '写しを受け取らないようにしました');
    }
}
CSCTLSRC;

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_s7mail_20261103/';
$mode = $argv[1] ?? 'check';
$libPath = 'app/Libs/CsShopMail.php';
$ctlPath = 'app/Http/Controllers/Api/Shop/CsReserveMailController.php';
$routePath = 'routes/api.php';
$oldLibMd5 = 'aec6d1422c9f9655e68456a1b51f63e6';
$anchor = "            Route::post('/cs/iap/restore', 'CsIapRestoreController@restore'); // [カスタムエイド 2026-11-03] 購入を復元";
$add = "\n            // [カスタムエイド 2026-11-03] 段階7:予約Webサイトの「メール」(予約受付用メールアドレス・写し)\n"
    . "            Route::get('/cs/reserve-mail', 'CsReserveMailController@show');\n"
    . "            Route::post('/cs/reserve-mail', 'CsReserveMailController@save');\n"
    . "            Route::post('/cs/reserve-mail/resend', 'CsReserveMailController@resend');\n"
    . "            Route::post('/cs/reserve-mail/bcc', 'CsReserveMailController@bcc');";
$clearRoutes = function () use ($base) {
    foreach (glob($base . 'bootstrap/cache/routes*.php') ?: [] as $rc) {
        $o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:cache 2>&1', $o); echo implode("\n", $o) . "\n"; return;
    }
};

if ($mode === 'undo') {
    if (!is_dir($backup)) exit("バックアップがありません($backup)。何もしていません。\n");
    copy($backup . 'CsShopMail.php', $base . $libPath);
    copy($backup . 'api.php', $base . $routePath);
    if (is_file($base . $ctlPath)) unlink($base . $ctlPath);
    $clearRoutes();
    exit("元に戻しました(足した欄 reserve_email_new はそのまま残っていますが、使われません)。\n");
}

require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ng = function ($m) { exit("NG $m\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };

// 確かめ
$cur = md5_file($base . $libPath);
echo ($cur === $oldLibMd5 ? 'OK ' : 'NG ') . "今の CsShopMail.php(md5 {$cur}。10/30 に入れたもの $oldLibMd5)\n";
if ($cur !== $oldLibMd5) $ng('CsShopMail.php が 10/30 のものと違います');
if (file_exists($base . $ctlPath)) $ng("$ctlPath がすでにあります");
$routes = file_get_contents($base . $routePath);
if (substr_count($routes, $anchor) !== 1) $ng('routes/api.php に「購入を復元」の行が見つかりません(または2つ以上)');
if (strpos($routes, 'CsReserveMailController') !== false) $ng('routes/api.php はすでに修正済みです');
echo "OK routes/api.php に足す場所がある\n";
$db = Illuminate\Support\Facades\DB::connection();
$cols = array_map(function ($c) { return $c->Field; }, $db->select("SHOW COLUMNS FROM `" . $db->getTablePrefix() . "web_shop`"));
foreach (['reserve_email', 'reserve_email_ok', 'reserve_email_token', 'reserve_email_sent', 'reserve_bcc_on'] as $c) if (!in_array($c, $cols)) $ng("web_shop に $c がありません");
$addCol = !in_array('reserve_email_new', $cols);
echo "① 欄:web_shop.reserve_email_new " . ($addCol ? '足す' : 'もうある') . "\n";
// 管理者と副管理者の見分け(ログインしている人のモデルに admin_type・privileges があるか)
$pm = config('auth.providers.shops.model');
echo "ログインの人のモデル:$pm\n";
try {
    $m = new $pm(); $t = $m->getTable();
    $mc = array_map(function ($c) { return $c->Field; }, $db->select("SHOW COLUMNS FROM `" . $db->getTablePrefix() . $t . "`"));
    echo (in_array('admin_type', $mc) ? 'OK ' : 'NG ') . "$t に admin_type\n";
    echo (in_array('privileges', $mc) ? 'OK ' : 'NG ') . "$t に privileges\n";
    echo (in_array('sid', $mc) ? 'OK ' : 'NG ') . "$t に sid\n";
    if (!in_array('admin_type', $mc) || !in_array('privileges', $mc) || !in_array('sid', $mc)) $ng('管理者と副管理者の見分けが、思っていた作りと違います');
} catch (\Throwable $e) { $ng('ログインの人のモデルが読めません:' . $e->getMessage()); }

$tmp = sys_get_temp_dir() . '/cs_s7_';
file_put_contents($tmp . 'lib.php', LIB_SRC); file_put_contents($tmp . 'ctl.php', CTL_SRC);
foreach (['lib.php', 'ctl.php'] as $f) { $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp . $f) . ' 2>&1', $o, $c); echo ($c === 0 ? 'OK ' : 'NG ') . "新しいファイルの文法 $f\n"; if ($c !== 0) $ng('文法エラー'); }
echo "② $libPath を入れ替える\n③ $ctlPath を足す\n④ $routePath に入口を4つ足す\n";

if ($mode !== 'apply') { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }

if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
copy($base . $libPath, $backup . 'CsShopMail.php');
copy($base . $routePath, $backup . 'api.php');
if ($addCol) $db->statement("ALTER TABLE `" . $db->getTablePrefix() . "web_shop` ADD `reserve_email_new` VARCHAR(100) NOT NULL DEFAULT ''");
echo "欄を足しました\n";
file_put_contents($base . $libPath, LIB_SRC);
file_put_contents($base . $ctlPath, CTL_SRC);
file_put_contents($base . $routePath, str_replace($anchor, $anchor . $add, $routes));
$ok = true;
foreach ([$libPath, $ctlPath, $routePath] as $f) { $o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c); echo ($c === 0 ? 'OK ' : 'NG ') . "文法 $f\n"; if ($c !== 0) $ok = false; }
if (!$ok) {
    copy($backup . 'CsShopMail.php', $base . $libPath); copy($backup . 'api.php', $base . $routePath); unlink($base . $ctlPath);
    exit("文法エラーがあったので、元に戻しました(足した欄はそのままですが、使われません)。\n");
}
$ref = $base . 'app/Http/Controllers/Api/Shop/CsIapRestoreController.php';
foreach ([$libPath, $ctlPath] as $p) { @chown($base . $p, fileowner($ref)); @chgrp($base . $p, filegroup($ref)); }
$clearRoutes();
$o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:list 2>&1 | grep reserve-mail', $o);
echo "入口の確かめ:\n" . (implode("\n", $o) ?: '見つかりません(確認が必要)') . "\n";
echo "店舗11 の状態:" . json_encode(App\Libs\CsShopMail::status(11), JSON_UNESCAPED_UNICODE) . "\n";
echo "\n完了しました。バックアップ: $backup\n元に戻すとき: php -d disable_functions= /root/s7_reservemail_patch.php undo\n";
