<?php
namespace App\Libs;

use App\Models\ShopModel;
use App\Models\ShopVipModel;
use App\Models\ShopAdminModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
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
    public static function dhm($t) { return self::d($t) . ' ' . date('G:i', $t); }
    public static function ymd($t) { return date('Y', $t) . '年' . date('n', $t) . '月' . date('j', $t) . '日'; }
    public static function plan($v)
    {
        $n = [1 => 'ライト', 2 => 'スタンダード', 3 => 'プレミアム'][(int)$v->type] ?? 'プラン';
        $m = [1 => '月額', 6 => '6ヶ月', 12 => '1年'][(int)$v->month] ?? '';
        return $n . ($m ? '・' . $m : '');
    }
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

    /* ---------------- メールの形(見本と同じ) ---------------- */
    private static $F = "font-family:-apple-system,BlinkMacSystemFont,'Hiragino Sans','Hiragino Kaku Gothic ProN','Noto Sans JP',Meiryo,sans-serif;";
    private static function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
    private static function box($in) { return '<tr><td style="background:#FFFFFF;border-radius:16px;padding:22px 20px;border:1px solid #E5E5EA;box-shadow:0 1px 4px rgba(0,0,0,0.10);">' . $in . '</td></tr><tr><td style="height:24px;"></td></tr>'; }
    private static function rows($rows)
    {
        $h = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:14px;">';
        foreach (array_values($rows) as $i => $r) {
            $b = $i ? 'border-top:1px solid #E5E5EA;' : '';
            $h .= '<tr><td width="112" valign="top" style="width:112px;padding:16px 8px 16px 0;font-size:14px;color:#6E6E73;' . $b . '">' . self::e($r[0]) . '</td><td valign="top" style="padding:16px 0;font-size:16px;line-height:1.6;color:#1D1D1F;' . $b . '">' . $r[1] . '</td></tr>';
        }
        return $h . '</table>';
    }
    private static function note($t, $k = 'ok')
    {
        $c = ['ok' => ['#FFF4E5', '#8A4B00'], 'info' => ['#E8F0FC', '#1A4D99'], 'warn' => ['#FCE8E6', '#A50E0E']][$k];
        return '<div style="margin-top:18px;background:' . $c[0] . ';color:' . $c[1] . ';border-radius:12px;padding:18px 18px;font-size:16px;line-height:1.75;">' . $t . '</div>';
    }
    private static function btn($t, $url, $main = true)
    {
        $s = $main ? 'background:#2B8A87;color:#FFFFFF;' : 'background:#FFFFFF;color:#1D1D1F;border:1px solid #D2D2D7;';
        return '<a href="' . self::e($url) . '" style="display:block;margin-top:20px;text-align:center;padding:18px;border-radius:12px;font-weight:bold;text-decoration:none;font-size:18px;' . $s . '">' . self::e($t) . '</a>';
    }
    private static function payBox()
    {
        $i = function ($b, $t, $first = false, $best = false) {
            return '<div style="padding:16px 0;' . ($first ? '' : 'border-top:1px solid #E5E5EA;') . '"><div style="font-weight:bold;font-size:18px;">' . $b
                . ($best ? ' <span style="display:inline-block;margin-left:6px;padding:1px 8px;border-radius:10px;background:#E8710A;color:#FFFFFF;font-size:14px;">いちばんお得</span>' : '')
                . '</div><div style="font-size:16px;color:#515154;margin-top:6px;">' . $t . '</div></div>';
        };
        return self::box('<div style="font-weight:bold;font-size:20px;margin-bottom:6px;">お申し込み方法</div>'
            . $i('App内課金', 'iPhone の管理アプリ →「ご利用プラン」から', true)
            . $i('Google Play課金', 'Android の管理アプリ →「ご利用プラン」から')
            . $i('クレジットカード', '管理WEBサイトから', false, true)
            . self::btn('管理WEBサイトで申し込む', self::WEB));
    }
    private static function hero($mark, $color, $chip, $chipBg, $shop, $name)
    {
        return self::box('<div style="text-align:center;"><table role="presentation" align="center" cellpadding="0" cellspacing="0"><tr><td align="center" valign="middle" width="44" height="44" style="width:44px;height:44px;border-radius:22px;background:' . $color . ';color:#FFFFFF;font-size:22px;font-weight:bold;' . self::$F . '">' . $mark . '</td></tr></table>'
            . '<div style="margin-top:14px;"><span style="display:inline-block;padding:5px 14px;border-radius:14px;background:' . $chipBg . ';color:' . $color . ';font-size:16px;font-weight:bold;">' . self::e($chip) . '</span></div>'
            . '<div style="margin-top:12px;font-size:24px;font-weight:bold;line-height:1.4;">' . self::e($shop ?: 'お店') . '</div>'
            . ($name !== '' ? '<div style="margin-top:4px;font-size:16px;color:#3A3A3C;">' . self::e($name) . ' 様</div>' : '') . '</div>');
    }
    private static function layout($body)
    {
        return '<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
            . '<body style="margin:0;padding:0;background:#F5F5F7;' . self::$F . 'color:#1D1D1F;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F5F5F7;"><tr><td align="center" style="padding:20px 12px;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;">'
            . '<tr><td align="center" style="padding:4px 0 18px;font-size:24px;font-weight:bold;color:#1D1D1F;">Card-San</td></tr>' . $body
            . '<tr><td align="center" style="padding:8px 6px 0;font-size:14px;line-height:1.9;color:#6E6E73;">このメールは送信専用のアドレスから送っています。<br>ご返信いただいてもお返事できません。<br>お困りのときは <a href="' . self::HELP . '" style="color:#0066CC;">Card-San ヘルプデスク</a> へ<br>(管理アプリ・管理WEBサイトからも開けます)<br><br>Card-San(カードサン)<br>運営会社:合同会社カスタムエイド</td></tr>'
            . '</table></td></tr></table></body></html>';
    }
    private static function lead($t) { return '<div style="font-size:18px;line-height:1.75;">' . $t . '</div>'; }
    private static function text($html)
    {
        $t = preg_replace('#<a [^>]*href="([^"]+)"[^>]*>(.*?)</a>#is', '$2 ( $1 )', $html);
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
        switch ($kind) {
            case '3':
                $s = '【Card-San】無料期間は残り7日です(' . self::d($x['end']) . 'まで)';
                $b = self::hero('7', '#1A73E8', '無料期間 残り7日', '#E8F0FC', $shopName, $name)
                   . self::box(self::lead('Card-San の無料期間は <b>' . self::d($x['end']) . '</b> までです。') . self::rows([['今の会員数', $x['members'] . '人']])
                     . $noAuto . self::note('会員が30人以下のまま' . self::d($x['end']) . 'を迎えた場合、無料期間が最大で60日目(' . self::d($x['limit']) . ')まで延長します。', 'info'))
                   . self::box('<div style="font-weight:bold;font-size:20px;">続けてお使いになる場合</div><div style="font-size:16px;color:#515154;margin-top:8px;line-height:1.7;">プランと料金は、管理WEBサイトの「ご利用プラン」で確かめられます。</div>' . self::btn('プランと料金を見る', self::WEB, false));
                break;
            case '4a':
                $s = '【Card-San】無料期間が延びます(会員30人以下のため)';
                $b = self::hero('↻', '#137333', '無料期間が延びます', '#E6F4EA', $shopName, $name)
                   . self::box(self::lead('明日(' . self::d($x['end']) . ')で最初の30日が終わります。<br>会員が <b>30人以下</b> なので、無料期間が <b>最大で' . self::d($x['limit']) . 'まで</b> 自動で延びます。お手続きはいりません。')
                     . self::rows([['今の会員数', $x['members'] . '人'], ['延長の終わり', '最大で' . self::d($x['limit']) . '(60日目)']])
                     . self::note('延長中に会員が31人になると、その24時間後に無料期間が終わります。そのときは、すぐにメールでお知らせします。', 'info'));
                break;
            case '4b':
                $s = '【Card-San】明日で無料期間が終わります';
                $b = self::hero('!', $o, '明日で無料期間が終わります', $ob, $shopName, $name)
                   . self::box(self::lead('Card-San の無料期間は <b>明日(' . self::d($x['end']) . ')</b> で終わります。会員が30人を超えているため、延長はありません。')
                     . self::rows([['今の会員数', $x['members'] . '人'], ['無料期間', self::dhm($x['end']) . ' まで'], ['そのあと', '管理アプリ・管理WEBが使えなくなります'], ['お客様のアプリ', self::d($x['end'] + 3 * self::DAY) . 'まで使えます']]))
                   . self::payBox();
                break;
            case '5':
                $s = '【Card-San】明日で無料期間(延長)が終わります';
                $b = self::hero('!', $o, '明日で延長が終わります', $ob, $shopName, $name)
                   . self::box(self::lead('延長していた無料期間は <b>明日(' . self::d($x['end']) . ')</b> で終わります。登録から60日たつと、会員数に関係なく終わる決まりです。')
                     . self::rows([['無料期間', self::dhm($x['end']) . ' まで'], ['そのあと', '管理アプリ・管理WEBが使えなくなります'], ['お客様のアプリ', self::d($x['end'] + 3 * self::DAY) . 'まで使えます']]))
                   . self::payBox();
                break;
            case '5o':
                $s = '【Card-San】会員が30人を超えました(無料期間は24時間後に終わります)';
                $b = self::hero('31', $o, '会員が30人を超えました', $ob, $shopName, $name)
                   . self::box(self::lead(self::dhm($x['over']) . ' に、31人目の会員が登録されました。<br>無料でお使いいただけるのは <b>' . self::dhm($x['end']) . '</b> までです。')
                     . self::rows([['無料期間', self::dhm($x['end']) . ' まで'], ['そのあと', '管理アプリ・管理WEBが使えなくなります'], ['お客様のアプリ', self::d($x['end'] + 3 * self::DAY) . 'まで使えます']])
                     . self::note('会員を消して30人以下に戻しても、無料期間は元に戻りません。', 'warn'))
                   . self::payBox();
                break;
            case '5s':
                $s = '【Card-San】明日、お客様のアプリが使えなくなります';
                $b = self::hero('!', '#C5221F', 'お客様のアプリが明日止まります', '#FCE8E6', $shopName, $name)
                   . self::box(self::lead('無料期間は' . self::d($x['end']) . 'に終わりました。<br><b>明日(' . self::d($x['until']) . ')</b> で、お客様のアプリ(会員証・予約・クーポンなど)が使えなくなります。')
                     . self::note('お申し込みいただくと、管理アプリ・管理WEB・お客様のアプリが、今までどおりお使いいただけます。', 'info'))
                   . self::payBox();
                break;
            case '6':
                $s = '【Card-San】ご契約ありがとうございます';
                $r = [['プラン', self::e($x['plan'])]]; if ($x['yen']) $r[] = ['料金', self::e($x['yen'])];
                $r[] = ['お支払い方法', self::e($x['pay'])]; $r[] = ['次の更新日', self::ymd($x['next'])];
                $b = self::hero('✓', $g, 'ご契約ありがとうございます', $gb, $shopName, $name)
                   . self::box(self::lead(($x['start'] > time() + 3600 ? self::d($x['start']) . 'から、' : '') . 'Card-San のご契約が完了しました。すべての機能を、続けてお使いいただけます。') . self::rows($r)
                     . self::note('領収書は、お支払いの会社(Stripe・App Store・Google Play)から別に届きます。', 'info') . self::btn('管理WEBサイトで契約内容を見る', self::WEB, false));
                break;
            case '7':
                $s = '【Card-San】解約を受け付けました';
                $b = self::hero('×', '#5F6368', '解約を受け付けました', '#EFEFF2', $shopName, $name)
                   . self::box(self::lead('ご利用ありがとうございました。解約を受け付けました。')
                     . self::rows([['プラン', self::e($x['plan'])], ['ご利用できる日', self::ymd($x['end']) . ' まで'], ['そのあと', '管理アプリ・管理WEBは、再開のお手続きだけできます。お客様のアプリは使えなくなります'], ['データ', 'ご契約が終わってから30日間は残ります(' . self::ymd($x['end'] + 30 * self::DAY) . 'に削除)']])
                     . self::note('ご契約が終わる前日に、もう一度メールでお知らせします。', 'info')
                     . self::note('気が変わったときは、ご契約が終わるまでに続ける手続きをすると、そのまま続けてお使いいただけます(新たな料金はかかりません)。') . self::btn('管理WEBサイトで契約内容を見る', self::WEB, false));
                break;
            case '7a':
                $s = '【Card-San】明日でご契約が終了となります';
                $b = self::hero('!', $o, '明日でご契約が終了となります', $ob, $shopName, $name)
                   . self::box(self::lead('解約のお手続きをいただいたため、Card-San のご契約は <b>明日(' . self::d($x['end']) . ')</b> で終了となります。')
                     . self::rows([['プラン', self::e($x['plan'])], ['ご利用できる日', self::dhm($x['end']) . ' まで'], ['そのあと', '管理アプリ・管理WEBは、再開のお手続きだけできます'], ['お客様のアプリ', 'ご契約が終わると使えなくなります'], ['データ', self::ymd($x['end'] + 30 * self::DAY) . 'に削除されます']])
                     . self::note('明日までに再開のお手続きをすれば、今までどおりお使いいただけます。新たな料金はかかりません。', 'info'))
                   . self::box('<div style="font-weight:bold;font-size:20px;margin-bottom:6px;">再開する方法</div><div style="padding:16px 0;font-size:18px;"><b>クレジットカードでご契約中の方</b><div style="font-size:16px;color:#515154;margin-top:6px;">管理WEBサイトの「ご契約内容」→「継続する」</div></div><div style="padding:16px 0;border-top:1px solid #E5E5EA;font-size:18px;"><b>App内課金・Google Play課金でご契約中の方</b><div style="font-size:16px;color:#515154;margin-top:6px;line-height:1.7;">iPhone は「設定」→ お名前 →「サブスクリプション」、Android は「Playストア」→「定期購入」から、CardSan管理の定期購入を再開</div></div>' . self::btn('管理WEBサイトで契約内容を見る', self::WEB));
                break;
            case '7b':
                $s = '【Card-San】ご契約が終了しました';
                $b = self::hero('×', '#5F6368', 'ご契約が終了しました', '#EFEFF2', $shopName, $name)
                   . self::box(self::lead('Card-San のご契約は <b>' . self::d($x['end']) . '</b> で終了しました。<br>お店のアプリ(会員証・予約・クーポンなど)は、お客様が使えない状態になっています。')
                     . self::rows([['理由', $x['reason'] === 'cancel' ? '解約のお手続きをいただいたため' : 'お支払いが確認できなかったため'], ['管理アプリ・管理WEB', 'ログインして、再開のお手続きだけできます'], ['データ', '<b>' . self::ymd($x['end'] + 30 * self::DAY) . '</b> に、お店のアプリのデータ(会員・メンバーズカード・予約など)を削除します']])
                     . self::note(self::d($x['end'] + 30 * self::DAY) . 'までにお申し込みいただくと、データはそのままで、すぐに再開できます。', 'warn'))
                   . self::payBox();
                break;
            case '8':
                $s = '【Card-San】ご契約内容を変更しました';
                $r = [['変更前', self::e($x['before'])], ['変更後', '<b>' . self::e($x['plan']) . '</b>'], ['変わった日', self::ymd($x['start'])]];
                if ($x['yen']) $r[] = ['料金', self::e($x['yen'])];
                $r[] = ['お支払い方法', self::e($x['pay'])]; $r[] = ['次の更新日', self::ymd($x['next'])];
                $b = self::hero('↻', $g, 'ご契約内容を変更しました', $gb, $shopName, $name)
                   . self::box(self::lead('ご契約内容が変わりました。') . self::rows($r) . self::btn('管理WEBサイトで契約内容を見る', self::WEB, false));
                break;
            default:
                return null;
        }
        $html = self::layout($b);
        return [$s, $html, self::text($html)];
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
                    if ($now >= $end - 7 * self::DAY && $now < $end - 6 * self::DAY) $add('3', $first->id, ['end' => $end, 'limit' => $limit, 'members' => $members]);
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
                $x = ['plan' => self::plan($p), 'pay' => self::pay($p), 'yen' => self::yen($p), 'start' => (int)$p->start_time, 'next' => (int)$p->end_time];
                if (!$prev || (int)$prev->end_time < (int)$p->start_time - 2 * self::DAY) $add('6', $p->id, $x);
                elseif ($prev->type != $p->type || $prev->month != $p->month || $prev->card_type != $p->card_type) { $x['before'] = self::plan($prev) . '(' . self::pay($prev) . ')'; $add('8', $p->id, $x); }
            }
            if ($vip && (int)$vip->is_try === 0 && (int)$vip->cancel_time >= $from && (int)$vip->cancel_time > 0 && !$reserved) {
                $x = ['plan' => self::plan($vip), 'end' => (int)$vip->end_time];
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
              'plan' => self::plan($p), 'pay' => self::pay($p), 'yen' => self::yen($p), 'start' => time(), 'next' => time() + 30 * self::DAY, 'reason' => 'cancel', 'before' => 'ライト・月額(クレジットカード)'];
        $k = ['3', '4a', '4b', '5', '5o', '5s', '6', '7', '7a', '7b', '8'];
        return array_map(function ($kind) use ($x) { return [$kind, $x]; }, $k);
    }
}
