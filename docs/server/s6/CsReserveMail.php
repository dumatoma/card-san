<?php

namespace App\Libs;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

// [カスタムエイド 2026-10-29] 予約メール(段階5)。SMS のリンク先ページと同じ中身を HTML とテキストの両方で1通に入れて送る。
//   送り手の名前はお店の名前、アドレスは .env の CS_RESERVE_MAIL_FROM(なければ CS_RESERVE_MAIL_USERNAME)。送り先の決め方は段階6(予約フォーム)で入れる
// [カスタムエイド 2026-10-30] 段階6:お店の予約受付用メールアドレス(確認済み)を返信先に、オンなら写し(BCC)も送る。送る処理は dm() にまとめた
class CsReserveMail
{
    const SUBJ = ['confirm' => 'ご予約を受け付けました', 'notice' => '明日のご予約のお知らせ', 'update' => 'ご予約の内容が変わりました', 'cancel' => 'ご予約のキャンセルを承りました'];

    public static function from()
    {
        return (string) env('CS_RESERVE_MAIL_FROM', env('CS_RESERVE_MAIL_USERNAME', ''));
    }

    // メールID とあいさつの名前に使うドメイン(送り手のアドレスの @ の後ろ)
    public static function domain()
    {
        $at = strrchr(self::from(), '@');
        return $at ? substr($at, 1) : 'card-san.jp';
    }

    public static function mailer()
    {
        config(['mail.mailers.cs_reserve' => ['transport' => 'smtp', 'host' => env('CS_RESERVE_MAIL_HOST'), 'port' => (int) env('CS_RESERVE_MAIL_PORT', 465),
            'encryption' => env('CS_RESERVE_MAIL_ENCRYPTION', 'ssl'), 'username' => env('CS_RESERVE_MAIL_USERNAME'), 'password' => env('CS_RESERVE_MAIL_PASSWORD'),
            'timeout' => 20, 'local_domain' => self::domain()]]);
        return Mail::mailer('cs_reserve');
    }

    // 予約からページ(写し)を作り、同じ中身のメールを送る。成功で true(失敗しても予約の保存は止めない)
    public static function send($type, $order, $to, $name)
    {
        $code = CsSmsPage::make($type, $order, $name);
        return self::sendParts($to, CsSmsPage::parts($type, $order, $name), $code);
    }

    public static function sendParts($to, array $p, $code, $useShop = true)
    {
        try {
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
            $shop = trim((string) ($p['shop']['name'] ?? ''));
            $url = $code ? CsSmsPage::base() . $code : '';
            $subject = ($shop !== '' ? '【' . $shop . '】' : '') . (self::SUBJ[$p['type'] ?? ''] ?? 'ご予約のお知らせ');
            // [カスタムエイド 2026-10-30] 段階6:お店の予約受付用メールアドレス(確認済みのときだけ)
            $sid = (int) ($p['shop']['sid'] ?? 0);
            $rm = $useShop ? CsShopMail::shopMail($sid) : '';
            $bcc = $useShop ? CsShopMail::bcc($sid) : '';
            $html = view('cs_reserve_mail', ['p' => $p, 'url' => $url, 'rm' => $rm])->render();
            $text = self::text($p, $url, $rm);
            $ok = self::dm($to, $subject, $html, $text, $shop, $rm, $bcc);
            if ($ok) Log::info('CsReserveMail ok type=' . ($p['type'] ?? '') . ' sid=' . $sid . ' code=' . $code . ' reply=' . ($rm !== '' ? 1 : 0) . ' bcc=' . ($bcc !== '' ? 1 : 0));
            return $ok;
        } catch (\Throwable $e) {
            Log::error('CsReserveMail ' . $e->getMessage());
            return false;
        }
    }

    // [カスタムエイド 2026-10-30] DirectMail の API で1通送る(鍵は SMS と同じ。送り手の名前は15文字まで)。返信先・写しは確認済みのアドレスだけ渡す
    public static function dm($to, $subject, $html, $text, $alias = '', $reply = '', $bcc = '')
    {
        try {
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
            new AlibabaCloudSms(); // 鍵の準備(SMS の部品と同じ)
            $alias = trim((string) $alias);
            $alias = mb_strlen($alias) > 15 ? mb_substr($alias, 0, 15) : $alias;
            $f = ['AccountName' => self::from(), 'AddressType' => 1, 'ReplyToAddress' => 'false', 'ToAddress' => $to, 'FromAlias' => $alias !== '' ? $alias : 'Card-San',
                'Subject' => $subject, 'HtmlBody' => $html, 'TextBody' => $text];
            if ($reply !== '' && filter_var($reply, FILTER_VALIDATE_EMAIL)) $f['ReplyAddress'] = $reply;
            if ($bcc !== '' && filter_var($bcc, FILTER_VALIDATE_EMAIL) && strcasecmp($bcc, $to) !== 0) $f['BccAddress'] = $bcc;
            \AlibabaCloud\Client\AlibabaCloud::rpc()->product('Dm')->version('2015-11-23')->action('SingleSendMail')->method('POST')
                ->host(env('CS_RESERVE_MAIL_API_HOST', 'dm.ap-southeast-1.aliyuncs.com'))->options(['query' => ['RegionId' => 'ap-southeast-1'], 'form_params' => $f])->request();
            return true;
        } catch (\Throwable $e) {
            Log::error('CsReserveMail dm ' . $e->getMessage());
            return false;
        }
    }

    // 文字だけの版(HTML を出さないメールソフト用)
    public static function text(array $p, $url, $rm = '')
    {
        $L = [];
        if (($p['heading'] ?? '') !== '') { $L[] = $p['heading']; $L[] = ''; }
        $L[] = trim(($p['name'] ?? '') . ' ' . ($p['honor'] ?? '')); $L[] = '';
        if (($p['top'] ?? '') !== '') { $L[] = $p['top']; $L[] = ''; }
        foreach ($p['rows'] ?? [] as $r) {
            $L[] = $r[0] . '：' . $r[1];
            if ($r[0] === 'メニュー' && !empty($r[2])) $L[] = '価格：' . $r[2];
        }
        $L[] = '';
        foreach ([$p['mid'] ?? '', $p['bottom'] ?? ''] as $s) if ($s !== '') { $L[] = $s; $L[] = ''; }
        $s = $p['shop'] ?? [];
        foreach ([($s['name'] ?? '') !== '' ? '店舗名：' . $s['name'] : '', ($s['phone'] ?? '') !== '' ? '電話：' . $s['phone'] : '', $s['address'] ?? ''] as $v) if ($v !== '') $L[] = $v;
        $L[] = '';
        if ($rm !== '') { $L[] = 'お店のメール：' . $rm; $L[] = ''; } // [カスタムエイド 2026-10-30] 段階6
        if (($p['type'] ?? '') === 'cancel') { $L[] = 'もう一度予約する'; $L[] = 'https://order.card-san.jp/reserve/?id=' . (int) ($s['sid'] ?? 0); $L[] = ''; }
        if ($url !== '') { $L[] = 'ページで見る'; $L[] = $url; $L[] = ''; }
        $L[] = '--';
        if ($rm !== '') { $L[] = 'このメールに返信すると、お店に届きます。'; } // [カスタムエイド 2026-10-30] 段階6
        else { $L[] = 'このメールは送信専用のアドレスから送っています。'; $L[] = 'ご返信いただいてもお店には届きません。'; }
        $L[] = 'Card-San';
        return implode("\n", $L);
    }
}
