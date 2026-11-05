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
