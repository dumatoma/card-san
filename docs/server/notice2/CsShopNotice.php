<?php
namespace App\Libs;

use App\Models\ShopModel;
use App\Models\ShopVipModel;
use App\Models\ShopAdminModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * [カスタムエイド 2026-11-03] 2026-11-03 3時:見本「Card-San お知らせ見本」を正として、文・色・形を見本どおりに直した。アカウント登録・認証コードのメール(systemMail)もここで作る
 * [カスタムエイド 2026-11-03] お店へのお知らせメール(無料期間・契約)。決定事項 2026-11-02・見本「Card-San お知らせ見本」
 *  3 7日前 / 4a 4b 前日 / 5 延長の終わる前日 / 5' 31人を超えた / 5'' お客様のアプリが止まる前日
 *  6 契約 / 7 解約受付 / 7a 契約が終わる前日 / 7b 契約が終了した / 8 契約内容の変更
 *  送った記録は cs_shop_notice_log(同じお知らせは1回だけ)
 */
class CsShopNotice
{
    const WEB = 'https://card-san.jp/';
    const HELP = 'https://cardsan.zendesk.com/hc/ja';
    const DAY = 86400;

    /* ---------------- 記録 ---------------- */
    public static function from()
    {
        return (int)DB::table('cs_shop_notice_log')->where('kind', 'init')->value('sent_at');
    }
    public static function sent($sid, $kind, $ref)
    {
        return DB::table('cs_shop_notice_log')->where('sid', $sid)->where('kind', $kind)->where('ref', (string)$ref)->exists();
    }
    public static function mark($sid, $kind, $ref, $to)
    {
        DB::table('cs_shop_notice_log')->insertOrIgnore(['sid' => $sid, 'kind' => $kind, 'ref' => (string)$ref, 'to_email' => $to, 'sent_at' => time()]);
    }

    /* ---------------- 言葉 ---------------- */
    public static function d($t)
    {
        $w = ['日', '月', '火', '水', '木', '金', '土'][date('w', $t)];
        return date('n', $t) . '月' . date('j', $t) . '日(' . $w . ')';
    }
    public static function md($t) { return date('n', $t) . '月' . date('j', $t) . '日'; } // [カスタムエイド 2026-11-03] 見本どおり曜日なし
    public static function dhm($t) { return self::md($t) . ' ' . date('G:i', $t); }
    public static function ymd($t) { return date('Y', $t) . '年' . date('n', $t) . '月' . date('j', $t) . '日'; }
    public static function plan($v)
    {
        $n = [1 => 'ライト', 2 => 'スタンダード', 3 => 'プレミアム'][(int)$v->type] ?? 'プラン';
        $m = [1 => '月額', 6 => '6ヶ月', 12 => '1年'][(int)$v->month] ?? '';
        return $n . ($m ? '・' . $m : '');
    }
    public static function planName($v) { return [1 => 'ライト', 2 => 'スタンダード', 3 => 'プレミアム'][(int)$v->type] ?? 'プラン'; }
    public static function pay($v)
    {
        return [1 => 'クレジットカード', 2 => 'App内課金(App Store)', 3 => 'Google Play課金'][(int)$v->card_type] ?? '';
    }
    public static function yen($v)
    {
        $a = (int)$v->amount;
        if ($a <= 0) return '';
        $u = [1 => '月額', 6 => '6ヶ月', 12 => '1年'][(int)$v->month] ?? '';
        return $u . ' ' . number_format($a) . '円(税込)';
    }

    /* ---------------- メールの形(見本「Card-San お知らせ見本」と同じ。2026-11-03 坂本さんの「見本を正とする」による) ---------------- */
    const IMG = 'https://api.card-san.jp/cs-mail/'; // アイコン・ストアのバッジ(PNG)
    const STORE = [
        'kanri' => ['https://apps.apple.com/jp/app/cardsan%E7%AE%A1%E7%90%86/id1663708240', 'https://play.google.com/store/apps/details?id=uni.UNI86DBD5D'],
        'card'  => ['https://apps.apple.com/jp/app/id1663708425', 'https://play.google.com/store/apps/details?id=uni.UNI1ED8C1A'],
    ];
    const GUIDE = 'https://cardsan.zendesk.com/hc/ja/categories/44291110458777'; // 使い方ガイド・登録特典(中身ができたら差し替える)
    private static $F = "font-family:-apple-system,BlinkMacSystemFont,'Hiragino Sans','Hiragino Kaku Gothic ProN','Noto Sans JP',Meiryo,sans-serif;";
    private static function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
    private static function gap() { return '<tr><td style="height:24px;line-height:24px;font-size:0;">&nbsp;</td></tr>'; }
    private static function box($in, $center = false)
    {
        return '<tr><td style="background:#FFFFFF;border-radius:16px;padding:22px 20px;border:1px solid #E5E5EA;box-shadow:0 1px 4px rgba(0,0,0,0.10);' . ($center ? 'text-align:center;' : '') . '">' . $in . '</td></tr>' . self::gap();
    }
    private static function sec($t) { return '<tr><td style="padding:0 6px 10px;font-size:14px;color:#6E6E73;">' . $t . '</td></tr>'; }
    private static function rows($rows)
    {
        $h = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:14px;">';
        foreach (array_values($rows) as $i => $r) {
            $b = $i ? 'border-top:1px solid #E5E5EA;' : '';
            $h .= '<tr><td width="112" valign="top" style="width:112px;padding:16px 8px 16px 0;font-size:14px;color:#6E6E73;' . $b . '">' . self::e($r[0]) . '</td><td valign="top" style="padding:16px 0;font-size:16px;line-height:1.6;color:#1D1D1F;' . $b . '">' . $r[1] . '</td></tr>';
        }
        return $h . '</table>';
    }
    private static function small($t) { return '<br><span style="font-size:14px;color:#6E6E73;">' . $t . '</span>'; }
    private static function note($t, $k = 'ok')
    {
        $c = ['ok' => ['#E6F4EA', '#0D652D'], 'info' => ['#E8F0FC', '#174EA6'], 'warn' => ['#FFF4E5', '#8A4B00'], 'gray' => ['#F5F5F7', '#1D1D1F']][$k];
        return '<div style="margin-top:18px;background:' . $c[0] . ';color:' . $c[1] . ';border-radius:12px;padding:18px;font-size:16px;line-height:1.75;">' . $t . '</div>';
    }
    private static function btn($t, $url, $main = true)
    {
        $s = $main ? 'background:#2B8A87;color:#FFFFFF;' : 'background:#FFFFFF;color:#1D1D1F;border:1px solid #D2D2D7;';
        return '<a href="' . self::e($url) . '" style="display:block;margin-top:20px;text-align:center;padding:18px;border-radius:12px;font-weight:bold;text-decoration:none;font-size:18px;' . $s . '">' . self::e($t) . '</a>';
    }
    private static function h($t) { return '<div style="font-weight:bold;font-size:20px;margin-bottom:6px;">' . $t . '</div>'; }
    private static function sd($t, $top = 6) { return '<div style="font-size:16px;color:#515154;margin-top:' . $top . 'px;line-height:1.7;">' . $t . '</div>'; }
    private static function cap($t) { return '<div style="text-align:center;font-size:14px;color:#6E6E73;margin-top:8px;">' . $t . '</div>'; }
    private static function items($list) // [[見出し, 説明, いちばんお得か], ...]
    {
        $h = '';
        foreach ($list as $i => $r) {
            $h .= '<div style="padding:' . ($i ? '16px' : '6px') . ' 0 16px;' . ($i ? 'border-top:1px solid #E5E5EA;' : '') . '"><div style="font-weight:bold;font-size:18px;color:#1D1D1F;margin-bottom:6px;">' . $r[0]
                . (!empty($r[2]) ? ' <span style="display:inline-block;margin-left:8px;padding:1px 8px;border-radius:10px;background:#E8710A;color:#FFFFFF;font-size:14px;font-weight:bold;">いちばんお得</span>' : '')
                . '</div><div style="font-size:16px;color:#515154;line-height:1.6;">' . $r[1] . '</div></div>';
        }
        return '<div style="margin-top:6px;">' . $h . '</div>';
    }
    private static function payBox()
    {
        return self::box(self::h('お申し込み方法') . self::items([
            ['App内課金', 'iPhone の管理アプリ →「ご利用プラン」から'],
            ['Google Play課金', 'Android の管理アプリ →「ご利用プラン」から'],
            ['クレジットカード', '管理WEBサイトから', true],
        ]) . self::btn('管理WEBサイトで申し込む', self::WEB));
    }
    private static function hero($mark, $color, $chip, $chipBg, $shop, $name, $sub = '')
    {
        return self::box('<table role="presentation" align="center" cellpadding="0" cellspacing="0"><tr><td align="center" valign="middle" width="44" height="44" style="width:44px;height:44px;border-radius:22px;background:' . $color . ';color:#FFFFFF;font-size:22px;font-weight:bold;' . self::$F . '">' . $mark . '</td></tr></table>'
            . '<div style="margin-top:14px;"><span style="display:inline-block;padding:5px 14px;border-radius:14px;background:' . $chipBg . ';color:' . $color . ';font-size:16px;font-weight:bold;">' . self::e($chip) . '</span></div>'
            . '<div style="margin-top:12px;font-size:24px;font-weight:bold;line-height:1.4;word-break:break-all;">' . self::e($shop ?: 'お店') . '</div>'
            . ($name !== '' ? '<div style="margin-top:4px;font-size:16px;color:#3A3A3C;">' . self::e($name) . ' 様</div>' : '')
            . ($sub !== '' ? '<div style="margin-top:4px;font-size:16px;color:#6E6E73;">' . self::e($sub) . '</div>' : ''), true);
    }
    private static function layout($body)
    {
        return '<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
            . '<body style="margin:0;padding:0;background:#F5F5F7;' . self::$F . 'color:#1D1D1F;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F5F7;"><tr><td align="center" style="padding:20px 12px 22px;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;">'
            . '<tr><td align="center" style="padding:4px 0 18px;"><table role="presentation" cellpadding="0" cellspacing="0"><tr><td valign="middle" style="padding-right:8px;"><img src="' . self::IMG . 'icon.png" width="30" height="30" alt="" style="display:block;width:30px;height:30px;border:0;"></td><td valign="middle" style="font-size:24px;font-weight:bold;color:#1D1D1F;' . self::$F . '">Card-San</td></tr></table></td></tr>'
            . $body
            . '<tr><td align="center" style="padding:8px 6px 0;font-size:14px;line-height:1.9;color:#6E6E73;">このメールは送信専用のアドレスから送っています。<br>ご返信いただいてもお返事できません。<br>お困りのときは <a href="' . self::HELP . '" style="color:#0066CC;">Card-San ヘルプデスク</a> へ<br>(管理アプリ・管理WEBサイトからも開けます)<br><br>Card-San(カードサン)<br>運営会社:合同会社カスタムエイド</td></tr>'
            . '</table></td></tr></table></body></html>';
    }
    private static function lead($t) { return '<div style="font-size:18px;line-height:1.75;">' . $t . '</div>'; }
    private static function text($html)
    {
        $t = preg_replace('#<a [^>]*href="([^"]+)"[^>]*>(.*?)</a>#is', '$2 ( $1 )', $html);
        $t = preg_replace('#<img [^>]*alt="([^"]*)"[^>]*>#i', '$1', $t);
        $t = preg_replace('#</td>\s*<td[^>]*>#i', '：', $t);
        $t = preg_replace('#<br\s*/?>|</div>|</tr>#i', "\n", $t);
        $t = html_entity_decode(strip_tags($t), ENT_QUOTES, 'UTF-8');
        return trim(preg_replace("/\n{3,}/", "\n\n", implode("\n", array_map('trim', explode("\n", $t)))));
    }

    /** 件名と本文。$x はメールごとの値 */
    public static function build($kind, $shopName, $name, array $x)
    {
        $o = '#B46A00'; $ob = '#FFF4E5'; $g = '#2B8A87'; $gb = '#E3F1F0';
        $noAuto = self::note('無料期間が終わったあとも、お申し込みいただくまで料金はかかりません。');
        $view = self::btn('管理WEBサイトで契約内容を見る', self::WEB, false);
        switch ($kind) {
            case '3':
                $s = '【Card-San】無料期間は残り7日です(' . self::md($x['end']) . 'まで)';
                $b = self::hero('7', '#1A73E8', '無料期間 残り7日', '#E8F0FC', $shopName, $name)
                   . self::box(self::lead('Card-San の無料期間は <b>' . self::d($x['end']) . '</b> までです。')
                     . self::rows([['無料期間', self::md($x['tstart']) . '〜' . self::md($x['end'])], ['今の会員数', $x['members'] . '人']])
                     . $noAuto . self::note('会員が30人以下のまま' . self::md($x['end']) . 'を迎えた場合、無料期間が最大で60日目(' . self::md($x['limit']) . ')まで延長します。', 'info'))
                   . self::box(self::h('続けてお使いになる場合') . self::sd('プランと料金は、管理WEBサイトの「ご利用プラン」で確かめられます。') . self::btn('プランと料金を見る', self::WEB, false));
                break;
            case '4a':
                $s = '【Card-San】無料期間が延びます(会員30人以下のため)';
                $b = self::hero('↻', '#137333', '無料期間が延びます', '#E6F4EA', $shopName, $name)
                   . self::box(self::lead('明日(' . self::md($x['end']) . ')で最初の30日が終わります。<br>会員が <b>30人以下</b> なので、無料期間が <b>最大で' . self::md($x['limit']) . 'まで</b> 自動で延びます。お手続きはいりません。')
                     . self::rows([['今の会員数', $x['members'] . '人'], ['延長の終わり', '最大で' . self::md($x['limit']) . '(60日目)']])
                     . self::note('延長中に会員が31人になると、その24時間後に無料期間が終わります。そのときは、すぐにメールでお知らせします。', 'info'));
                break;
            case '4b':
                $s = '【Card-San】明日で無料期間が終わります';
                $b = self::hero('!', $o, '明日で無料期間が終わります', $ob, $shopName, $name)
                   . self::box(self::lead('Card-San の無料期間は <b>明日(' . self::md($x['end']) . ')</b> で終わります。会員が30人を超えているため、延長はありません。')
                     . self::rows([['今の会員数', $x['members'] . '人'], ['無料期間', self::dhm($x['end']) . ' まで'], ['そのあと', '管理アプリ・管理WEBが使えなくなります'], ['お客様のアプリ', self::md($x['end'] + 3 * self::DAY) . 'まで使えます']]))
                   . self::payBox();
                break;
            case '5':
                $s = '【Card-San】明日で無料期間(延長)が終わります';
                $b = self::hero('!', $o, '明日で延長が終わります', $ob, $shopName, $name)
                   . self::box(self::lead('延長していた無料期間は <b>明日(' . self::md($x['end']) . ')</b> で終わります。登録から60日たつと、会員数に関係なく終わる決まりです。')
                     . self::rows([['無料期間', self::dhm($x['end']) . ' まで'], ['そのあと', '管理アプリ・管理WEBが使えなくなります'], ['お客様のアプリ', self::md($x['end'] + 3 * self::DAY) . 'まで使えます']]))
                   . self::payBox();
                break;
            case '5o':
                $s = '【Card-San】会員が30人を超えました(無料期間は24時間後に終わります)';
                $b = self::hero('31', $o, '会員が30人を超えました', $ob, $shopName, $name)
                   . self::box(self::lead(self::dhm($x['over']) . ' に、31人目の会員が登録されました。<br>無料でお使いいただけるのは <b>' . self::dhm($x['end']) . '</b> までです。')
                     . self::rows([['無料期間', self::dhm($x['end']) . ' まで'], ['そのあと', '管理アプリ・管理WEBが使えなくなります'], ['お客様のアプリ', self::md($x['end'] + 3 * self::DAY) . 'まで使えます']])
                     . self::note('会員を消して30人以下に戻しても、無料期間は元に戻りません。', 'warn'))
                   . self::payBox();
                break;
            case '5s':
                $s = '【Card-San】明日、お客様のアプリが使えなくなります';
                $b = self::hero('!', '#C5221F', 'お客様のアプリが明日止まります', '#FCE8E6', $shopName, $name)
                   . self::box(self::lead('無料期間は' . self::md($x['end']) . 'に終わりました。<br><b>明日(' . self::md($x['until']) . ')</b> で、お客様のアプリが使えなくなります。')
                     . self::note('お申し込みいただくと、管理アプリ・管理WEB・お客様のアプリが、今までどおりお使いいただけます。', 'info'))
                   . self::payBox();
                break;
            case '6':
                $s = '【Card-San】ご契約ありがとうございます';
                $r = [['プラン', self::e($x['name'])]]; if ($x['yen']) $r[] = ['料金', self::e($x['yen'])];
                $r[] = ['お支払い方法', self::e($x['pay'])]; $r[] = ['次の更新日', self::ymd($x['next'])];
                $b = self::hero('✓', $g, 'ご契約ありがとうございます', $gb, $shopName, $name)
                   . self::box(self::lead('Card-San のご契約が完了しました。') . self::rows($r)
                     . self::note('領収書は、お支払いの会社(Stripe・App Store・Google Play)から別に届きます。', 'info') . $view);
                break;
            case '7':
                $s = '【Card-San】解約を受け付けました';
                $b = self::hero('×', '#5F6368', '解約を受け付けました', '#EFEFF2', $shopName, $name)
                   . self::box(self::lead('ご利用ありがとうございました。解約を受け付けました。')
                     . self::rows([['プラン', self::e($x['name'])], ['ご利用できる日', self::ymd($x['end']) . ' まで'], ['そのあと', '管理アプリ・管理WEBは、再開のお手続きだけできます。お客様のアプリは使えなくなります'], ['データ', 'ご契約が終わってから30日間は残ります(' . self::ymd($x['end'] + 30 * self::DAY) . 'に削除)']])
                     . self::note('ご契約が終わる前日に、もう一度メールでお知らせします。', 'info')
                     . self::note('気が変わったときは、ご契約が終わるまでに「継続する」を押すと、そのまま続けてお使いいただけます(新たな料金はかかりません)。', 'ok') . $view);
                break;
            case '7a':
                $s = '【Card-San】明日でご契約が終了となります';
                $b = self::hero('!', $o, '明日でご契約が終了となります', $ob, $shopName, $name)
                   . self::box(self::lead('解約のお手続きをいただいたため、Card-San のご契約は <b>明日(' . self::md($x['end']) . ')</b> で終了となります。')
                     . self::rows([['プラン', self::e($x['name'])], ['ご利用できる日', self::ymd($x['end']) . ' ' . date('G:i', $x['end']) . ' まで'], ['そのあと', '管理アプリ・管理WEBは、再開のお手続きだけできます'], ['お客様のアプリ', self::md($x['end'] + self::DAY) . 'から使えなくなります'], ['データ', self::ymd($x['end'] + 30 * self::DAY) . 'にすべて削除されます']])
                     . self::note('明日までに再開のお手続きをすれば、今までどおりお使いいただけます。新たな料金はかかりません。', 'info'))
                   . self::box(self::h('再開する方法') . self::items([
                       ['クレジットカードでご契約中の方', '管理WEBサイトの「ご契約内容」→「継続する」'],
                       ['App内課金・Google Play課金でご契約中の方', 'iPhone は「設定」→ お名前 →「サブスクリプション」、Android は「Playストア」→「定期購入」から、CardSan管理の定期購入を再開'],
                     ]) . self::btn('管理WEBサイトで契約内容を見る', self::WEB));
                break;
            case '7b':
                $s = '【Card-San】ご契約が終了しました';
                $b = self::hero('×', '#5F6368', 'ご契約が終了しました', '#EFEFF2', $shopName, $name)
                   . self::box(self::lead('Card-San のご契約は <b>' . self::md($x['end']) . '</b> で終了しました。<br>お店のアプリは、お客様が使えない状態になっています。')
                     . self::rows([['理由', $x['reason'] === 'cancel' ? '解約のお手続きをいただいたため' : 'お支払いが確認できなかったため'], ['管理アプリ・管理WEB', 'ログインして、再開のお手続きだけできます'], ['データ', '<b>' . self::ymd($x['end'] + 30 * self::DAY) . '</b> にすべて削除されます']])
                     . self::note(self::md($x['end'] + 30 * self::DAY) . 'までにお申し込みいただくと、会員・メンバーズカード・予約などのデータはそのままで、すぐに再開できます。', 'warn'))
                   . self::payBox();
                break;
            case '8':
                $s = '【Card-San】ご契約内容を変更しました';
                $day = date('Ymd', $x['start']) === date('Ymd') ? '今日(' . self::md($x['start']) . ')から' : self::md($x['start']) . 'から';
                $r = [['変更前', self::e($x['before'])], ['変更後', '<b>' . self::e($x['plan']) . '</b>'], ['変わる日', '<b>' . $day . '</b>']];
                if ($x['yen']) $r[] = ['料金', self::e($x['yen'])];
                $r[] = ['お支払い方法', self::e($x['pay'])]; $r[] = ['次の更新日', self::md($x['next'])];
                $b = self::hero('↻', $g, 'ご契約内容を変更しました', $gb, $shopName, $name)
                   . self::box(self::lead('ご契約内容の変更を受け付けました。') . self::rows($r)
                     . self::note('<b>アップグレード</b>は今日から新しいプランの機能が使えます。今の期間の残りの分の差額は、お支払いの会社(Stripe・App Store・Google Play)の決まりで計算されます。', 'info')
                     . self::note('<b>ダウングレード・お支払い周期の変更</b>は「変わる日」が次の更新日になります。それまでは今のプランのまま、すべての機能が使えます。', 'ok') . $view);
                break;
            default:
                return null;
        }
        $html = self::layout($b);
        return [$s, $html, self::text($html)];
    }

    /* ---------------- アカウント登録・認証コードのメール(見本の 1・C1〜C3)。ひな形 layouts/email/shop_*.blade.php から呼ぶ ---------------- */
    /** 無料期間の始まりと終わり(メールアドレスからお店を探す。見つからなければ null) */
    public static function welcomeTrial($email)
    {
        try {
            $ad = ShopAdminModel::where('email', $email)->where('admin_type', 1)->orderBy('id', 'desc')->first();
            if (!$ad) return null;
            $v = ShopVipModel::where('sid', $ad->sid)->where('is_try', 1)->orderBy('id', 'asc')->first();
            return $v ? [(int)$v->start_time, (int)$v->end_time] : null;
        } catch (\Throwable $e) { return null; }
    }
    private static function steps($list)
    {
        $h = '';
        foreach ($list as $i => $r) {
            $pad = $i ? 'padding:16px 0;border-top:1px solid #E5E5EA;' : 'padding:0 0 16px;';
            $h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td valign="top" width="40" style="width:40px;' . $pad . '"><div style="width:28px;height:28px;line-height:28px;border-radius:14px;background:#1A73E8;color:#FFFFFF;font-weight:bold;font-size:16px;text-align:center;">' . ($i + 1) . '</div></td>'
                . '<td valign="top" style="' . $pad . '"><div style="font-weight:bold;font-size:18px;">' . $r[0] . '</div>' . self::sd($r[1]) . ($r[2] ?? '') . '</td></tr></table>';
        }
        return $h;
    }
    private static function badges($kind)
    {
        $u = self::STORE[$kind];
        return '<table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:12px;"><tr>'
            . '<td style="padding-right:8px;"><a href="' . $u[0] . '"><img src="' . self::IMG . 'appstore_jp.png" width="109" height="40" alt="App Storeからダウンロード" style="display:block;width:109px;height:40px;border:0;"></a></td>'
            . '<td><a href="' . $u[1] . '"><img src="' . self::IMG . 'googleplay_jp.png" width="135" height="40" alt="Google Playで手に入れよう" style="display:block;width:135px;height:40px;border:0;"></a></td></tr></table>';
    }
    private static function card($ic, $t, $d, $b, $url)
    {
        return self::box('<table role="presentation" cellpadding="0" cellspacing="0"><tr><td valign="top" width="56" style="width:56px;"><div style="width:44px;height:44px;line-height:44px;border-radius:12px;background:#F1F3F4;text-align:center;font-size:22px;">' . $ic . '</div></td>'
            . '<td valign="top"><div style="font-weight:bold;font-size:18px;">' . $t . '</div>' . self::sd($d) . '</td></tr></table>' . self::btn($b, $url, false));
    }
    /** $kind: shop_register_success / shop_register / shop_login / shop_update_email。ひな形に渡された値($email・$name・$shop_name・$code)をそのまま渡す */
    public static function systemMail($kind, array $v)
    {
        $email = (string)($v['email'] ?? ''); $name = (string)($v['name'] ?? ''); $shop = (string)($v['shop_name'] ?? '');
        if ($kind === 'shop_register_success') {
            $t = isset($v['trial']) ? $v['trial'] : self::welcomeTrial($email);
            $period = $t ? self::md($t[0]) . '〜' . self::md($t[1]) : '今日から30日間';
            $b = self::hero('✓', '#2B8A87', '登録が完了しました', '#E3F1F0', $shop, $name, 'Card-San へようこそ')
               . self::box(self::lead('ご登録ありがとうございます。<br>今日から <b>30日間</b>、すべての機能を無料でお試しいただけます。')
                 . self::rows([['ログインID', self::e($email)], ['無料期間', $period . self::small('会員が30人以下なら、最大で60日まで延びます')]])
                 . self::note('無料期間が終わったあとも、お申し込みいただくまで料金はかかりません。'))
               . self::sec('はじめにやること(4つ)')
               . self::box(self::steps([
                   ['管理アプリを入れる', 'スマホからお店のアプリを作ったり、予約やお客様を見たりできます。', self::badges('kanri')],
                   ['お客様用アプリ「Card-San」も入れる', '作ったお店のアプリが、お客様にどう見えるかを確かめながら作れます。', self::badges('card')],
                   ['お店の情報を入れる', '店名・住所・営業時間・メニュー。お客様のアプリに、そのまま表示されます。'],
                   ['メンバーズカードを作る', '最初にお客様へ渡すカードです。お店の色やロゴで作れます。'],
                 ]) . self::btn('管理WEBサイトにログイン', self::WEB) . self::cap('パソコンからは、こちらが便利です'))
               . self::sec('お役立ち')
               . self::card('📘', '使い方ガイド', '画面の写真つきで、アプリの作り方を順番に説明しています。', 'ガイドを見る', self::GUIDE)
               . self::card('🎁', '登録特典', 'ご登録いただいた方へのプレゼントです。', '特典を受け取る', self::GUIDE);
            return self::layout($b);
        }
        $c = [
            'shop_login' => ['管理WEBサイト・管理アプリへのログインの認証コードです。画面に入力してください。', 'ログイン'],
            'shop_register' => ['Card-San へのご登録ありがとうございます。登録を続けるには、この認証コードを画面に入力してください。', 'アカウント登録'],
            'shop_update_email' => ['ログインID(メールアドレス)を、このアドレスに変える手続きの認証コードです。画面に入力してください。', 'メールアドレスの変更'],
        ][$kind];
        $b = self::box(self::lead($c[0])
            . '<div style="font-size:32px;font-weight:bold;letter-spacing:10px;text-align:center;background:#F5F5F7;border-radius:12px;padding:14px 0;margin:12px 0 6px;font-family:ui-monospace,Menlo,Consolas,monospace;">' . self::e($v['code'] ?? '') . '</div>'
            . self::cap('このコードの有効期限は <b>10分</b> です')
            . self::note('このコードは、' . $c[1] . 'のときだけ使います。だれにも教えないでください。Card-San から電話やメッセージでコードをお聞きすることはありません。', 'warn')
            . self::note('お心当たりがない場合は、このメールを消してください。ほかの人がまちがえてメールアドレスを入れた可能性があります。', 'gray'));
        return self::layout($b);
    }

    /* ---------------- だれに何を送るか ---------------- */
    public static function owner($sid)
    {
        return ShopAdminModel::where('sid', $sid)->where('admin_type', 1)->orderBy('id')->first();
    }

    /** @return array [[sid, kind, ref, to, shopName, name, x], ...]  $apply=true のときは、31人目から24時間の終わりをデータに入れる */
    public static function collect($apply = false, $onlySid = 0)
    {
        $now = time(); $from = self::from(); $out = [];
        $sids = ShopVipModel::query()->select('sid')->distinct()->pluck('sid');
        foreach ($sids as $sid) {
            if ($onlySid && (int)$sid !== (int)$onlySid) continue;
            $shop = ShopModel::find($sid); if (!$shop) continue;
            $ad = self::owner($sid); $to = $ad ? trim((string)$ad->email) : '';
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) continue;
            $add = function ($kind, $ref, $x) use (&$out, $sid, $to, $shop, $ad) {
                if (!self::sent($sid, $kind, $ref)) $out[] = [(int)$sid, $kind, (string)$ref, $to, (string)$shop->name, (string)$ad->name, $x];
            };
            $vip = ShopVipModel::where('sid', $sid)->where('start_time', '<=', $now)->where('end_time', '>=', $now)->where('status', 1)->orderBy('create_time', 'desc')->first();
            $reserved = ShopVipModel::where('sid', $sid)->where('status', 0)->where('is_subscription', 1)->where('end_time', '>', $now)->exists();
            $first = ShopVipModel::where('sid', $sid)->where('is_try', 1)->orderBy('id', 'asc')->first();
            $members = $shop->member()->count();

            /* ---- 無料期間(テスト用店舗・有料の契約中・予約中は送らない) ---- */
            if ($first && !CsTrial::unlimited($sid) && !$reserved && (!$vip || (int)$vip->is_try === 1)) {
                $limit = (int)$first->start_time + 60 * self::DAY;
                if ($vip && (int)$vip->id === (int)$first->id) {
                    $end = (int)$first->end_time;
                    if ($now >= $end - 7 * self::DAY && $now < $end - 6 * self::DAY) $add('3', $first->id, ['tstart' => (int)$first->start_time, 'end' => $end, 'limit' => $limit, 'members' => $members]);
                    if ($now >= $end - self::DAY && $now < $end) $add($members <= 30 ? '4a' : '4b', $first->id, ['end' => $end, 'limit' => $limit, 'members' => $members]);
                } elseif ($vip) { // 延長中
                    $end = CsTrial::extensionEnd($shop, $vip, $first);
                    if ($apply && $end < (int)$vip->end_time) $vip->update(['end_time' => $end]);
                    if ((int)$shop->cs_trial_over_at > 0) $add('5o', 'over', ['over' => (int)$shop->cs_trial_over_at, 'end' => $end]);
                    elseif ($now >= $end - self::DAY && $now < $end) $add('5', $vip->id, ['end' => $end]);
                } else {
                    $n = CsTrial::notice($shop, null);
                    $wouldExtend = $members <= 30 && !(int)$shop->cs_trial_over_at && $now < $limit; // 次に開いたときに延長される
                    if ($n['state'] === 'trial_ended' && !$wouldExtend && $now >= $n['customer_app_until'] - self::DAY && $now < $n['customer_app_until']) {
                        $add('5s', 'until' . $n['customer_app_until'], ['end' => $n['ends_at'], 'until' => $n['customer_app_until']]);
                    }
                }
            }

            /* ---- 契約(この仕組みを入れたあとの記録だけ) ---- */
            if (!$from) continue;
            $paid = ShopVipModel::where('sid', $sid)->where('status', 1)->where('is_try', 0)->where('card_type', '>', 0)->orderBy('start_time')->get();
            foreach ($paid as $p) {
                if ((int)$p->create_time < $from || (int)$p->create_time < $now - 3 * self::DAY || (int)$p->start_time > $now) continue;
                $prev = null; foreach ($paid as $q) { if ($q->id != $p->id && (int)$q->start_time < (int)$p->start_time) $prev = $q; }
                $x = ['plan' => self::plan($p), 'name' => self::planName($p), 'pay' => self::pay($p), 'yen' => self::yen($p), 'start' => (int)$p->start_time, 'next' => (int)$p->end_time];
                if (!$prev || (int)$prev->end_time < (int)$p->start_time - 2 * self::DAY) $add('6', $p->id, $x);
                elseif ($prev->type != $p->type || $prev->month != $p->month || $prev->card_type != $p->card_type) { $x['before'] = self::plan($prev); $add('8', $p->id, $x); }
            }
            if ($vip && (int)$vip->is_try === 0 && (int)$vip->cancel_time >= $from && (int)$vip->cancel_time > 0 && !$reserved) {
                $x = ['plan' => self::plan($vip), 'name' => self::planName($vip), 'end' => (int)$vip->end_time];
                $add('7', 'c' . $vip->end_time, $x);
                if ($now >= (int)$vip->end_time - self::DAY && $now < (int)$vip->end_time) $add('7a', 'e' . $vip->end_time, $x);
            }
            if (!$vip && !$reserved) {
                $last = CsTrial::lastVip($sid);
                if ($last && (int)$last->is_try === 0 && (int)$last->end_time >= $from && $now >= (int)$last->end_time + 6 * 3600 && $now < (int)$last->end_time + 3 * self::DAY) {
                    $add('7b', 'e' . $last->end_time, ['end' => (int)$last->end_time, 'reason' => (int)$last->cancel_time > 0 ? 'cancel' : 'payment']);
                }
            }
        }
        return $out;
    }

    /** 見本:12種類を、決まった値で作る */
    public static function samples()
    {
        $t = strtotime(date('Y-m-d 23:59:00', time() + 7 * self::DAY)); $p = (object)['type' => 2, 'month' => 1, 'card_type' => 1, 'amount' => 3500];
        $x = ['end' => $t, 'limit' => $t + 30 * self::DAY, 'members' => 12, 'over' => $t - 86400, 'until' => $t + 3 * self::DAY,
              'plan' => self::plan($p), 'name' => self::planName($p), 'pay' => self::pay($p), 'yen' => self::yen($p), 'start' => time(), 'tstart' => $t - 29 * self::DAY, 'next' => time() + 30 * self::DAY, 'reason' => 'cancel', 'before' => 'ライト・月額'];
        $k = ['3', '4a', '4b', '5', '5o', '5s', '6', '7', '7a', '7b', '8'];
        return array_map(function ($kind) use ($x) { return [$kind, $x]; }, $k);
    }
}
