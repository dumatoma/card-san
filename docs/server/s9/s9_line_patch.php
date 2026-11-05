<?php
// [カスタムエイド 2026-11-04] 段階9:LINE で予約のお知らせを送る(サーバー)
//   1. 欄・表を足す:web_shop.cs_opt_line(0)・reserve_ch_line(0)/ web_order.cs_line_uid('')/ 表 cs_shop_line・cs_line_follow
//   2. 新しいファイル:app/Libs/CsLine.php・Shop/CsLineController.php・Member/CsLineController.php・Api/CsLinePublicController.php
//   3. app/Libs/CsReserveCh.php を入れ替える(LINE を足す)。Shop/CsReserveChController の保存に line を足す
//   4. 送る5か所(Member/OrderController の受付・Shop/OrderController の受付・変更・キャンセル・OrderNoticeJob の前日)に「LINE の予約なら LINE」を、メールより先に足す
//   5. Member/OrderController(store):お客様が LINE を選んだとき(cs_ch = line・合言葉 cs_lt)
//   6. Shop/OrderController(show):予約詳細に cs_line(''/ok/ng)
//   7. routes/api.php のいちばん下に LINE の入口(鍵が記録に残らないよう api.request を通さない)
// 使い方(このファイル1つを /root/ に置く):
//   php -d disable_functions= /root/s9_line_patch.php               … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/s9_line_patch.php apply         … バックアップを取ってから変更する
//   php -d disable_functions= /root/s9_line_patch.php optline 11 on … 試験用:店舗11 に「LINE オプション契約中」の印(off で外す)
//   php -d disable_functions= /root/s9_line_patch.php undo          … 元に戻す(足した欄・表はそのまま。使われなくなるだけ)

const SRC_LIB = <<<'CSSRC1'
<?php

namespace App\Libs;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * [カスタムエイド 2026-11-04] 段階9:LINE で予約のお知らせを送る(お店の LINE 公式アカウントから)
 *  仕様:プロジェクト資料「CardSan_LINEで予約のお知らせ(段階9)_仕様書_20261103」
 *  表 cs_shop_line:お店ごとの鍵(暗号化して保存。管理App には、はじめとおわりの3文字だけ返す)
 *  表 cs_line_follow:お客様がお店の LINE をブロックしているか(Webhook の follow / unfollow と、送る前の確かめで記録)
 *  web_order.cs_line_uid:LINE で受け取る予約のお客様の LINE の ID(お店には出さない)
 *  LINE を選んだ予約は、届かなくても SMS・メールに切り替えない(仕様 6)
 */
class CsLine
{
    const T_SHOP = 'cs_shop_line';
    const T_FOLLOW = 'cs_line_follow';
    const SUBJ = ['confirm' => 'ご予約を受け付けました', 'notice' => '明日のご予約のお知らせ', 'update' => 'ご予約の内容が変わりました', 'cancel' => 'ご予約のキャンセルを承りました'];
    const BACK_OK = 'https://order.card-san.jp/';

    // ---------- 住所 ----------
    public static function base()
    {
        return rtrim((string) env('APP_URL', 'https://api.card-san.jp'), '/');
    }

    public static function callbackUrl()
    {
        return self::base() . '/api/cs-line/callback';
    }

    public static function webhookUrl($sid)
    {
        return self::base() . '/api/cs-line/webhook/' . (int) $sid;
    }

    // ---------- LINE への問い合わせ ----------
    // 戻り値 [番号, 中身(配列)]。つながらないときは [0, []]
    public static function http($method, $url, array $headers = [], $json = null, $form = null)
    {
        try {
            $o = ['http_errors' => false, 'timeout' => 10, 'headers' => $headers];
            if ($json !== null) $o['json'] = $json;
            if ($form !== null) $o['form_params'] = $form;
            $res = (new Client())->request($method, $url, $o);
            $body = json_decode((string) $res->getBody(), true);
            return [(int) $res->getStatusCode(), is_array($body) ? $body : []];
        } catch (\Throwable $e) {
            Log::error('CsLine http ' . $method . ' ' . parse_url($url, PHP_URL_PATH) . ' ' . $e->getMessage());
            return [0, []];
        }
    }

    private static function bot($token, $method, $path, $json = null)
    {
        return self::http($method, 'https://api.line.me' . $path, ['Authorization' => 'Bearer ' . $token], $json);
    }

    // ---------- 鍵 ----------
    public static function row($sid)
    {
        try { return DB::table(self::T_SHOP)->where('sid', (int) $sid)->first(); } catch (\Throwable $e) { return null; }
    }

    private static function dec($v)
    {
        try { return $v ? Crypt::decryptString($v) : ''; } catch (\Throwable $e) { return ''; }
    }

    // 鍵(4つそろっていないときは null)
    public static function cfg($sid)
    {
        $r = self::row($sid);
        if (!$r) return null;
        $c = ['msg_secret' => self::dec($r->msg_secret), 'msg_token' => self::dec($r->msg_token), 'login_id' => (string) $r->login_id, 'login_secret' => self::dec($r->login_secret),
            'bot_name' => (string) $r->bot_name, 'bot_id' => (string) $r->bot_id];
        foreach (['msg_secret', 'msg_token', 'login_id', 'login_secret'] as $k) if ($c[$k] === '') return null;
        return $c;
    }

    public static function configured($sid)
    {
        return self::cfg($sid) !== null;
    }

    // はじめとおわりの3文字だけ(仕様 17)
    public static function mask($s)
    {
        $s = (string) $s;
        if ($s === '') return '';
        if (mb_strlen($s) <= 8) return '••••••';
        return mb_substr($s, 0, 3) . '••••••••' . mb_substr($s, -3);
    }

    // 管理App に返す状態
    public static function status($sid)
    {
        $c = self::cfg($sid);
        $q = $c ? self::quota($sid) : null;
        return [
            'set' => $c ? 1 : 0,
            'bot_name' => $c ? $c['bot_name'] : '',
            'bot_id' => $c ? $c['bot_id'] : '',
            'keys' => $c ? ['msg_secret' => self::mask($c['msg_secret']), 'msg_token' => self::mask($c['msg_token']), 'login_id' => $c['login_id'], 'login_secret' => self::mask($c['login_secret'])] : null,
            'quota' => $q,
            'callback_url' => self::callbackUrl(),
            'webhook_url' => self::webhookUrl($sid),
        ];
    }

    // 「保存して確かめる」。戻り値 ['ok' => bool, 'msg' => 文, 'errors' => [欄 => 理由]]。1つでも違えば保存しない(仕様 19)
    public static function save($sid, array $in)
    {
        $k = [];
        foreach (['msg_secret', 'msg_token', 'login_id', 'login_secret'] as $f) $k[$f] = trim((string) ($in[$f] ?? ''));
        $err = [];
        foreach ($k as $f => $v) if ($v === '') $err[$f] = '入力してください';
        if ($k['login_id'] !== '' && !preg_match('/^\d{6,20}$/', $k['login_id'])) $err['login_id'] = 'チャネル ID は数字です';
        if ($err) return ['ok' => false, 'msg' => '入っていない欄があります', 'errors' => $err];

        // 1. アクセストークン:公式アカウントの名前を取る
        list($code, $info) = self::bot($k['msg_token'], 'GET', '/v2/bot/info');
        if ($code !== 200) {
            return ['ok' => false, 'msg' => 'つなげませんでした', 'errors' => ['msg_token' => $code === 0 ? 'LINE につながりませんでした。少し待ってからもう一度押してください' : 'このトークンでは LINE 公式アカウントにつながりません']];
        }
        // 2. チャネルシークレット:Webhook の試し送りで、署名が合うか確かめる(Webhook URL が Card-San の受け取り先になっている必要がある)
        list($c2, $ep) = self::bot($k['msg_token'], 'GET', '/v2/bot/channel/webhook/endpoint');
        if ($c2 !== 200 || (string) ($ep['endpoint'] ?? '') !== self::webhookUrl($sid)) {
            $err['msg_secret'] = 'Messaging API の Webhook URL に、次の住所を入れてから押してください:' . self::webhookUrl($sid);
        } else {
            Cache::put('cs_line_pend_' . (int) $sid, $k['msg_secret'], 120);
            list($c3, $t) = self::bot($k['msg_token'], 'POST', '/v2/bot/channel/webhook/test', ['endpoint' => self::webhookUrl($sid)]);
            Cache::forget('cs_line_pend_' . (int) $sid);
            if (!($c3 === 200 && !empty($t['success']) && (int) ($t['statusCode'] ?? 0) === 200)) $err['msg_secret'] = 'チャネルシークレットが、トークンと同じチャネルのものではありません';
        }
        // 3. LINE ログイン:わざと使えない合言葉で問い合わせ、ID とシークレットの組み合わせだけを確かめる(invalid_client なら違う)※確認が必要
        list($c4, $tk) = self::http('POST', 'https://api.line.me/oauth2/v2.1/token', [], null,
            ['grant_type' => 'authorization_code', 'code' => 'cs_check_' . bin2hex(random_bytes(4)), 'redirect_uri' => self::callbackUrl(), 'client_id' => $k['login_id'], 'client_secret' => $k['login_secret']]);
        if ($c4 === 0) $err['login_secret'] = 'LINE につながりませんでした。少し待ってからもう一度押してください';
        elseif (($tk['error'] ?? '') === 'invalid_client') $err['login_secret'] = 'チャネル ID とシークレットの組み合わせが合いません';
        if ($err) return ['ok' => false, 'msg' => 'つなげませんでした', 'errors' => $err];

        $row = ['msg_secret' => Crypt::encryptString($k['msg_secret']), 'msg_token' => Crypt::encryptString($k['msg_token']), 'login_id' => $k['login_id'],
            'login_secret' => Crypt::encryptString($k['login_secret']), 'bot_name' => mb_substr((string) ($info['displayName'] ?? ''), 0, 100), 'bot_id' => (string) ($info['basicId'] ?? ''),
            'quota_cache' => '', 'quota_time' => 0, 'update_time' => time()];
        if (self::row($sid)) DB::table(self::T_SHOP)->where('sid', (int) $sid)->update($row);
        else DB::table(self::T_SHOP)->insert($row + ['sid' => (int) $sid]);
        Log::info('CsLine save sid=' . (int) $sid . ' bot=' . $row['bot_id']);
        return ['ok' => true, 'msg' => 'LINE 公式アカウント「' . $row['bot_name'] . '」とつながりました', 'errors' => []];
    }

    // 「設定を解除する」(仕様 18)。LINE で受けた予約は、表示のときに「届きません」になる
    public static function remove($sid)
    {
        DB::table(self::T_SHOP)->where('sid', (int) $sid)->delete();
        try { DB::table('web_shop')->where('id', (int) $sid)->update(['reserve_ch_line' => 0]); } catch (\Throwable $e) {}
        Log::info('CsLine remove sid=' . (int) $sid);
    }

    // ---------- 今月の通数(仕様 7・13) ----------
    // 戻り値 ['limit' => 上限(0 = 上限なし), 'used' => 今月送った数, 'left' => 残り, 'over' => 1/0]。30分覚えておく
    public static function quota($sid, $fresh = false)
    {
        $r = self::row($sid);
        if (!$r) return null;
        if (!$fresh && (int) $r->quota_time > time() - 1800 && $r->quota_cache) {
            $q = json_decode($r->quota_cache, true);
            if (is_array($q)) return $q;
        }
        $c = self::cfg($sid);
        if (!$c) return null;
        list($a, $qa) = self::bot($c['msg_token'], 'GET', '/v2/bot/message/quota');
        list($b, $qb) = self::bot($c['msg_token'], 'GET', '/v2/bot/message/quota/consumption');
        if ($a !== 200 || $b !== 200) return null;
        $limit = ($qa['type'] ?? '') === 'limited' ? (int) ($qa['value'] ?? 0) : 0;
        $used = (int) ($qb['totalUsage'] ?? 0);
        $q = ['limit' => $limit, 'used' => $used, 'left' => $limit > 0 ? max(0, $limit - $used) : -1, 'over' => ($limit > 0 && $used >= $limit) ? 1 : 0];
        DB::table(self::T_SHOP)->where('sid', (int) $sid)->update(['quota_cache' => json_encode($q), 'quota_time' => time()]);
        return $q;
    }

    private static function setOver($sid)
    {
        $q = ['limit' => 0, 'used' => 0, 'left' => 0, 'over' => 1];
        $r = self::row($sid);
        if ($r && $r->quota_cache) { $o = json_decode($r->quota_cache, true); if (is_array($o)) $q = array_merge($o, ['left' => 0, 'over' => 1]); }
        DB::table(self::T_SHOP)->where('sid', (int) $sid)->update(['quota_cache' => json_encode($q), 'quota_time' => time()]);
    }

    // 予約WEB に LINE を出してよいか(鍵がそろっていて、今月の上限に達していない)
    public static function ready($sid)
    {
        if (!self::configured($sid)) return false;
        $q = self::quota($sid);
        return !($q && !empty($q['over']));
    }

    // ---------- お客様の LINE ログイン ----------
    public static function backOk($back)
    {
        return is_string($back) && strpos($back, self::BACK_OK) === 0 && strlen($back) < 1000;
    }

    // LINE ログインの住所。mode = order(予約WEB)/ test(試しに自分に送る)
    public static function authUrl($sid, $back, $mode = 'order')
    {
        $c = self::cfg($sid);
        if (!$c) return '';
        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(8));
        Cache::put('cs_line_st_' . $state, ['sid' => (int) $sid, 'back' => (string) $back, 'mode' => $mode], 900);
        return 'https://access.line.me/oauth2/v2.1/authorize?' . http_build_query(['response_type' => 'code', 'client_id' => $c['login_id'], 'redirect_uri' => self::callbackUrl(),
            'state' => $state, 'scope' => 'profile openid', 'nonce' => $nonce, 'bot_prompt' => 'aggressive']);
    }

    // LINE から戻ってきたとき。戻り値 ['mode', 'sid', 'back', 'ok', 'err', 'ticket', 'name', 'friend', 'sent']
    public static function callback($code, $state, $error = '')
    {
        $st = $state ? Cache::pull('cs_line_st_' . $state) : null;
        if (!is_array($st)) return ['ok' => false, 'err' => 'expired', 'mode' => '', 'back' => ''];
        $out = ['mode' => $st['mode'], 'sid' => $st['sid'], 'back' => $st['back'], 'ok' => false, 'err' => ''];
        if ($error !== '' || !$code) { $out['err'] = 'cancel'; return $out; }
        $c = self::cfg($st['sid']);
        if (!$c) { $out['err'] = 'noconfig'; return $out; }
        list($a, $tk) = self::http('POST', 'https://api.line.me/oauth2/v2.1/token', [], null,
            ['grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => self::callbackUrl(), 'client_id' => $c['login_id'], 'client_secret' => $c['login_secret']]);
        $at = (string) ($tk['access_token'] ?? '');
        if ($a !== 200 || $at === '') { Log::error('CsLine token sid=' . $st['sid'] . ' code=' . $a . ' err=' . ($tk['error'] ?? '')); $out['err'] = 'token'; return $out; }
        list($b, $pf) = self::http('GET', 'https://api.line.me/v2/profile', ['Authorization' => 'Bearer ' . $at]);
        $uid = (string) ($pf['userId'] ?? '');
        if ($b !== 200 || $uid === '') { $out['err'] = 'profile'; return $out; }
        $friend = self::isFriend($st['sid'], $uid);
        $out += ['ok' => true, 'name' => mb_substr((string) ($pf['displayName'] ?? ''), 0, 50), 'friend' => $friend ? 1 : 0];
        if ($st['mode'] === 'test') {
            $out['sent'] = 0;
            if ($friend) {
                $msg = [['type' => 'text', 'text' => "【Card-San】LINE 予約のお知らせのテストです。\nお客様には、この LINE 公式アカウント(" . $c['bot_name'] . ")から予約のお知らせが届きます。"]];
                list($p) = self::push($st['sid'], $uid, $msg);
                $out['sent'] = $p === 200 ? 1 : 0;
                if ($p === 429) self::setOver($st['sid']);
            }
            return $out;
        }
        $t = bin2hex(random_bytes(16));
        Cache::put('cs_line_tk_' . $t, ['sid' => (int) $st['sid'], 'uid' => $uid, 'name' => $out['name']], 3600);
        $out['ticket'] = $t;
        return $out;
    }

    // 予約WEB が「つながったか」を聞く。友だち追加したかも、その場で確かめる
    public static function ticket($t, $sid)
    {
        $d = $t ? Cache::get('cs_line_tk_' . $t) : null;
        if (!is_array($d) || (int) $d['sid'] !== (int) $sid) return null;
        $c = self::cfg($sid);
        return ['name' => $d['name'], 'friend' => self::isFriend($sid, $d['uid']) ? 1 : 0, 'bot_name' => $c ? $c['bot_name'] : '',
            'add_url' => ($c && $c['bot_id'] !== '') ? 'https://line.me/R/ti/p/' . rawurlencode($c['bot_id']) : '', 'uid' => $d['uid']];
    }

    // ---------- 友だち・ブロック ----------
    // お店の LINE の友だちで、ブロックしていなければ true(プロフィールが取れる)。結果を cs_line_follow に残す
    public static function isFriend($sid, $uid)
    {
        $c = self::cfg($sid);
        if (!$c || !$uid) return false;
        list($code) = self::bot($c['msg_token'], 'GET', '/v2/bot/profile/' . rawurlencode($uid));
        if ($code === 200) { self::follow($sid, $uid, false); return true; }
        if ($code === 404) { self::follow($sid, $uid, true); return false; }
        return !self::blocked($sid, $uid); // つながらないときは、前の記録で決める
    }

    public static function follow($sid, $uid, $blocked)
    {
        try {
            $w = ['sid' => (int) $sid, 'uid' => (string) $uid];
            if (DB::table(self::T_FOLLOW)->where($w)->exists()) DB::table(self::T_FOLLOW)->where($w)->update(['blocked' => $blocked ? 1 : 0, 'update_time' => time()]);
            else DB::table(self::T_FOLLOW)->insert($w + ['blocked' => $blocked ? 1 : 0, 'update_time' => time()]);
        } catch (\Throwable $e) { Log::error('CsLine follow ' . $e->getMessage()); }
    }

    public static function blocked($sid, $uid)
    {
        try { return (int) DB::table(self::T_FOLLOW)->where(['sid' => (int) $sid, 'uid' => (string) $uid])->value('blocked') === 1; } catch (\Throwable $e) { return false; }
    }

    // お店の画面に出す状態:''(LINE の予約ではない)/ 'ok' / 'ng'(ブロック・設定の解除で届かない。仕様 6・18)
    public static function orderState($order)
    {
        $uid = (string) ($order->cs_line_uid ?? '');
        if ($uid === '') return '';
        if (!self::configured($order->sid)) return 'ng';
        return self::blocked($order->sid, $uid) ? 'ng' : 'ok';
    }

    // ---------- 送る ----------
    public static function push($sid, $uid, array $messages)
    {
        $c = self::cfg($sid);
        if (!$c) return [0, []];
        return self::http('POST', 'https://api.line.me/v2/bot/message/push',
            ['Authorization' => 'Bearer ' . $c['msg_token'], 'X-Line-Retry-Key' => self::uuid()], ['to' => $uid, 'messages' => $messages]);
    }

    private static function uuid()
    {
        $b = random_bytes(16);
        $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
        $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
    }

    // 予約のお知らせ(confirm / notice / update / cancel)。LINE の予約なら true を返す(届かなくても true。メール・SMS に切り替えない)
    public static function sendOrder($type, $order)
    {
        $uid = (string) ($order->cs_line_uid ?? '');
        if ($uid === '') return false;
        try {
            $sid = (int) $order->sid;
            if (!self::configured($sid)) { Log::info('CsLine skip(noconfig) type=' . $type . ' oid=' . $order->id); return true; }
            if (!self::isFriend($sid, $uid)) { Log::info('CsLine skip(blocked) type=' . $type . ' oid=' . $order->id); return true; }
            $msg = self::message($type, $order);
            list($code, $res) = self::push($sid, $uid, [$msg]);
            if ($code === 200) Log::info('CsLine ok type=' . $type . ' oid=' . $order->id);
            else {
                Log::error('CsLine push type=' . $type . ' oid=' . $order->id . ' code=' . $code . ' ' . mb_substr(json_encode($res, JSON_UNESCAPED_UNICODE), 0, 300));
                if ($code === 429) self::setOver($sid);
            }
        } catch (\Throwable $e) {
            Log::error('CsLine sendOrder ' . $e->getMessage());
        }
        return true;
    }

    // メール・SMS と同じ文(自動メッセージ)と予約内容で、LINE のメッセージを1つ作る。ボタンは予約内容のページ(仕様 8)
    public static function message($type, $order)
    {
        $p = CsSmsPage::parts($type, $order, $order->name);
        $code = CsSmsPage::make($type, $order, $order->name);
        $shop = trim((string) ($p['shop']['name'] ?? ''));
        $subject = ($shop !== '' ? '【' . $shop . '】' : '') . (self::SUBJ[$type] ?? 'ご予約のお知らせ');
        $tx = function ($s, $extra = []) { return array_merge(['type' => 'text', 'text' => (string) $s, 'wrap' => true, 'size' => 'sm', 'color' => '#333333'], $extra); };
        $body = [$tx($subject, ['weight' => 'bold', 'size' => 'md', 'color' => '#111111'])];
        $nm = trim(($p['name'] ?? '') . ' ' . ($p['honor'] ?? ''));
        if ($nm !== '') $body[] = $tx($nm, ['margin' => 'md']);
        if (($p['top'] ?? '') !== '') $body[] = $tx($p['top'], ['margin' => 'md']);
        $rows = [];
        foreach ($p['rows'] ?? [] as $r) {
            $v = (string) ($r[1] ?? '');
            if ($r[0] === 'メニュー' && !empty($r[2])) $v .= '(￥' . number_format((float) $r[2]) . ')';
            if ($v === '') continue;
            $rows[] = ['type' => 'box', 'layout' => 'baseline', 'spacing' => 'sm', 'contents' => [
                $tx($r[0], ['color' => '#888888', 'flex' => 2]), $tx($v, ['flex' => 5])]];
        }
        if ($rows) $body[] = ['type' => 'box', 'layout' => 'vertical', 'margin' => 'lg', 'spacing' => 'sm', 'contents' => $rows];
        foreach ([$p['mid'] ?? '', $p['bottom'] ?? ''] as $s) if ($s !== '') $body[] = $tx($s, ['margin' => 'lg']);
        $sh = array_values(array_filter([$shop, ($p['shop']['phone'] ?? '') !== '' ? '電話:' . $p['shop']['phone'] : '', (string) ($p['shop']['address'] ?? '')]));
        if ($sh) $body[] = $tx(implode("\n", $sh), ['margin' => 'lg', 'size' => 'xs', 'color' => '#888888']);
        $bubble = ['type' => 'bubble', 'body' => ['type' => 'box', 'layout' => 'vertical', 'contents' => $body]];
        if ($code) {
            $bubble['footer'] = ['type' => 'box', 'layout' => 'vertical', 'contents' => [
                ['type' => 'button', 'style' => 'link', 'color' => '#06A64C', 'action' => ['type' => 'uri', 'label' => '予約の詳しいページを見る', 'uri' => CsSmsPage::base() . $code]]]];
        }
        return ['type' => 'flex', 'altText' => mb_substr($subject, 0, 100), 'contents' => $bubble];
    }

    // ---------- Webhook ----------
    // 署名を確かめてから、follow / unfollow を記録する。署名が合わなければ false
    public static function webhook($sid, $raw, $sig)
    {
        $secrets = [];
        $c = self::cfg($sid);
        if ($c) $secrets[] = $c['msg_secret'];
        $pend = Cache::get('cs_line_pend_' . (int) $sid);
        if ($pend) $secrets[] = $pend;
        $ok = false;
        foreach ($secrets as $s) if ($sig !== '' && hash_equals(base64_encode(hash_hmac('sha256', $raw, $s, true)), $sig)) { $ok = true; break; }
        if (!$ok) return false;
        $j = json_decode($raw, true);
        foreach (($j['events'] ?? []) as $e) {
            $uid = (string) ($e['source']['userId'] ?? '');
            if ($uid === '') continue;
            if (($e['type'] ?? '') === 'follow') self::follow($sid, $uid, false);
            if (($e['type'] ?? '') === 'unfollow') self::follow($sid, $uid, true);
        }
        return true;
    }
}
CSSRC1;
const SRC_CH = <<<'CSSRC2'
<?php

namespace App\Libs;

use Illuminate\Support\Facades\DB;

/**
 * [カスタムエイド 2026-11-03] 段階8-1:予約の受け取り方法(メール・SMS。LINE は段階9)
 * [カスタムエイド 2026-11-04] 段階9:LINE を足した。cs_opt_line(1 = LINE オプション契約中)/ reserve_ch_line(1 = LINE で受け付ける)
 *   LINE は、鍵がそろっていて今月の上限に達していないときだけ出す(CsLine::ready)。オプション(SMS か LINE)がないときは、メールは必ずオン
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
            return DB::table('web_shop')->where('id', (int) $sid)->first(['id', 'cs_opt_sms', 'cs_opt_line', 'reserve_ch_mail', 'reserve_ch_sms', 'reserve_ch_line', 'vip_sms_month_count', 'vip_sms_count']);
        } catch (\Throwable $e) { return null; }
    }

    // 今、受け付けている方法(決まりを当てはめたあと)。line_on = お店のスイッチ、line = 予約WEB に出すか
    public static function get($sid)
    {
        $r = self::row($sid);
        $optS = $r && (int) $r->cs_opt_sms === 1;
        $optL = $r && (int) $r->cs_opt_line === 1;
        $mail = ($optS || $optL) ? ((int) $r->reserve_ch_mail === 1) : true;
        $sms = $optS && (int) $r->reserve_ch_sms === 1;
        $lineOn = $optL && (int) $r->reserve_ch_line === 1;
        $line = $lineOn && CsLine::ready($sid);
        if (!$mail && !$sms && !$line) $mail = true;
        return ['mail' => $mail ? 1 : 0, 'sms' => $sms ? 1 : 0, 'line' => $line ? 1 : 0, 'line_on' => $lineOn ? 1 : 0, 'opt_sms' => $optS ? 1 : 0, 'opt_line' => $optL ? 1 : 0];
    }

    // お店が変える。$line が null なら今のまま。戻り値 ['ok' => bool, 'msg' => 文]
    public static function set($sid, $mail, $sms, $line = null)
    {
        $r = self::row($sid);
        if (!$r) return ['ok' => false, 'msg' => '店舗が見つかりません'];
        $optS = (int) $r->cs_opt_sms === 1;
        $optL = (int) $r->cs_opt_line === 1;
        if ($line === null) $line = $optL && (int) $r->reserve_ch_line === 1;
        if (!$optS && !$optL && !$mail) return ['ok' => false, 'msg' => 'SMS・LINE のオプションがないときは、メールはオフにできません'];
        if (!$optS && $sms) return ['ok' => false, 'msg' => 'SMS のオプションをご契約ではありません'];
        if (!$optL && $line) return ['ok' => false, 'msg' => 'LINE のオプションをご契約ではありません'];
        if ($line && !CsLine::configured($sid)) return ['ok' => false, 'msg' => '先に LINE 予約の設定(4つの鍵)をしてください'];
        if (!$mail && !$sms && !$line) return ['ok' => false, 'msg' => '受け付ける方法を1つ以上オンにしてください'];
        DB::table('web_shop')->where('id', (int) $sid)->update(['reserve_ch_mail' => $mail ? 1 : 0, 'reserve_ch_sms' => $sms ? 1 : 0, 'reserve_ch_line' => $line ? 1 : 0]);
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

    // [カスタムエイド 2026-11-04] 試験用:LINE オプションの印
    public static function setOptLine($sid, $on)
    {
        $u = ['cs_opt_line' => $on ? 1 : 0];
        if (!$on) $u['reserve_ch_line'] = 0;
        return DB::table('web_shop')->where('id', (int) $sid)->update($u);
    }
}
CSSRC2;
const SRC_SHOPCTL = <<<'CSSRC3'
<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Libs\CsLine;
use Illuminate\Http\Request;

/**
 * [カスタムエイド 2026-11-04] 段階9:管理App「予約Webサイト」の LINE・「LINE予約の設定」
 *  GET  /api/shop/cs/line           今の状態(鍵ははじめとおわりの3文字だけ・今月の通数)。だれでも見られる
 *  POST /api/shop/cs/line           「保存して確かめる」(msg_secret・msg_token・login_id・login_secret)
 *  POST /api/shop/cs/line/remove    「設定を解除する」
 *  GET  /api/shop/cs/line/test-url  「試しに自分の LINE に送る」の LINE ログインの住所
 *  変えられるのは、管理者と「予約設定」(8)の権限がある副管理者だけ。
 *  鍵が記録(my_api のログ)に残らないよう、この入口は api.request を通さない(routes/api.php のいちばん下)
 */
class CsLineController extends Controller
{
    private function canEdit($u)
    {
        if ((int) ($u->admin_type ?? 1) !== 2) return true;
        $p = $u->privileges ?? [];
        if (!is_array($p)) $p = json_decode((string) $p, true) ?: [];
        return in_array(8, $p) || in_array('8', $p, true);
    }

    private function out($sid, $msg = '', $extra = [])
    {
        $d = CsLine::status($sid);
        $d['ch'] = \App\Libs\CsReserveCh::get($sid);
        $d['can_edit'] = $this->canEdit(auth('shop')->user()) ? 1 : 0;
        return responseToJson(200, $msg, array_merge($d, $extra));
    }

    public function show()
    {
        return $this->out(auth('shop')->user()->sid);
    }

    public function save(Request $request)
    {
        $u = auth('shop')->user();
        if (!$this->canEdit($u)) return responseToJson(403, '予約設定の権限がありません');
        if (!(int) (\App\Libs\CsReserveCh::get($u->sid)['opt_line'] ?? 0)) return responseToJson(400, 'LINE のオプションをご契約ではありません');
        $r = CsLine::save($u->sid, $request->only(['msg_secret', 'msg_token', 'login_id', 'login_secret']));
        if (!$r['ok']) return responseToJson(400, $r['msg'], ['errors' => $r['errors']]);
        return $this->out($u->sid, $r['msg']);
    }

    public function remove()
    {
        $u = auth('shop')->user();
        if (!$this->canEdit($u)) return responseToJson(403, '予約設定の権限がありません');
        CsLine::remove($u->sid);
        return $this->out($u->sid, 'LINE 予約の設定を解除しました');
    }

    public function testUrl()
    {
        $u = auth('shop')->user();
        $url = CsLine::authUrl($u->sid, '', 'test');
        if ($url === '') return responseToJson(400, '先に LINE 予約の設定をしてください');
        return responseToJson(200, '', ['url' => $url]);
    }
}
CSSRC3;
const SRC_MEMCTL = <<<'CSSRC4'
<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Libs\CsLine;
use App\Libs\CsReserveCh;
use Illuminate\Http\Request;

/**
 * [カスタムエイド 2026-11-04] 段階9:予約WEB の「LINE で受け取る」
 *  GET /api/member/cs-line/auth?sid=&back=   LINE ログインの住所(back は予約WEB の住所だけ。戻るときに ?cs_lt=合言葉 が付く)
 *  GET /api/member/cs-line/ticket?sid=&t=    つながったか・友だち追加したか(LINE の名前はお客様本人の画面にだけ出す)
 */
class CsLineController extends Controller
{
    public function auth(Request $request)
    {
        $sid = (int) $request->sid;
        $back = (string) $request->back;
        if (!CsLine::backOk($back)) return responseToJson(400, '戻り先が正しくありません');
        if (!(int) (CsReserveCh::get($sid)['line'] ?? 0)) return responseToJson(400, 'このお店では、LINE での受け取りは選べません');
        $url = CsLine::authUrl($sid, $back, 'order');
        if ($url === '') return responseToJson(400, 'このお店では、LINE での受け取りは選べません');
        return responseToJson(200, '', ['url' => $url]);
    }

    public function ticket(Request $request)
    {
        $d = CsLine::ticket((string) $request->t, (int) $request->sid);
        if (!$d) return responseToJson(400, 'LINE とのつながりが切れました。もう一度「LINE で受け取る」を押してください');
        unset($d['uid']);
        return responseToJson(200, '', $d);
    }
}
CSSRC4;
const SRC_PUBCTL = <<<'CSSRC5'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\CsLine;
use Illuminate\Http\Request;

/**
 * [カスタムエイド 2026-11-04] 段階9:LINE からの戻り先と合図の受け取り先(ログインなし)
 *  GET  /api/cs-line/callback         LINE ログインの戻り先(全部のお店で同じ住所。LINE ログインのチャネルの「コールバック URL」に入れる)
 *  POST /api/cs-line/webhook/{sid}    Messaging API の Webhook(お店ごと)。署名を確かめてから、友だち追加・ブロックを記録
 */
class CsLinePublicController extends Controller
{
    public function callback(Request $request)
    {
        $r = CsLine::callback((string) $request->code, (string) $request->state, (string) $request->error);
        if (($r['mode'] ?? '') === 'order' && CsLine::backOk($r['back'] ?? '')) {
            $q = $r['ok'] ? ['cs_lt' => $r['ticket']] : ['cs_le' => $r['err']];
            $sep = strpos($r['back'], '?') === false ? '?' : '&';
            return redirect()->away($r['back'] . $sep . http_build_query($q));
        }
        if (($r['mode'] ?? '') === 'test') {
            if (!$r['ok']) return $this->page('送れませんでした', $r['err'] === 'cancel' ? 'LINE のログインをやめました。' : 'もう一度、管理App の「試しに自分の LINE に送る」から押してください。');
            if (empty($r['friend'])) return $this->page('友だち追加をしてください', 'お店の LINE 公式アカウントを友だち追加してから、もう一度「試しに自分の LINE に送る」を押してください。');
            return $r['sent'] ? $this->page('送りました', 'LINE を開いて、テストのお知らせが届いたか確かめてください。この画面は閉じて大丈夫です。')
                : $this->page('送れませんでした', 'LINE の今月の上限に達しているか、LINE につながりませんでした。');
        }
        return $this->page('時間が切れました', 'もう一度、はじめからやり直してください。');
    }

    private function page($title, $text)
    {
        $t = htmlspecialchars($title, ENT_QUOTES);
        $x = htmlspecialchars($text, ENT_QUOTES);
        return response('<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Card-San</title>'
            . '<style>body{margin:0;font-family:-apple-system,"Hiragino Sans",sans-serif;background:#f5f5f7;color:#1d1d1f;text-align:center;padding:72px 24px}</style></head>'
            . '<body><div style="font-size:18px;font-weight:700">' . $t . '</div><div style="font-size:14px;color:#6e6e73;margin-top:12px;line-height:1.8">' . $x . '</div></body></html>')
            ->header('Content-Type', 'text/html; charset=utf-8')->header('Cache-Control', 'no-store');
    }

    public function webhook(Request $request, $sid)
    {
        $ok = CsLine::webhook((int) $sid, (string) $request->getContent(), (string) $request->header('X-Line-Signature', ''));
        return response('', $ok ? 200 : 401);
    }
}
CSSRC5;

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_s9line_20261104/';
$mode = $argv[1] ?? 'check';
$tag = '[カスタムエイド 2026-11-04]';
$new = [
    'app/Libs/CsLine.php' => SRC_LIB,
    'app/Http/Controllers/Api/Shop/CsLineController.php' => SRC_SHOPCTL,
    'app/Http/Controllers/Api/Member/CsLineController.php' => SRC_MEMCTL,
    'app/Http/Controllers/Api/CsLinePublicController.php' => SRC_PUBCTL,
];
$fCh = 'app/Libs/CsReserveCh.php';
$fChCtl = 'app/Http/Controllers/Api/Shop/CsReserveChController.php';
$fR = 'routes/api.php';
$fSo = 'app/Http/Controllers/Api/Shop/OrderController.php';
$fMo = 'app/Http/Controllers/Api/Member/OrderController.php';
$fJ = 'app/Jobs/OrderNoticeJob.php';
$files = [$fCh, $fChCtl, $fR, $fSo, $fMo, $fJ];
$bk = function ($f) use ($backup) { return $backup . str_replace('/', '__', $f); };
$artisan = function ($cmd) use ($base) { $o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan ' . $cmd . ' 2>&1', $o); return implode("\n", $o); };
$routes = function () use ($base, $artisan) { if (glob($base . 'bootstrap/cache/routes*.php')) echo $artisan('route:cache') . "\n"; };

if ($mode === 'undo') {
    if (!is_dir($backup)) exit("バックアップがありません。何もしていません。\n");
    foreach ($files as $f) if (is_file($bk($f))) { copy($bk($f), $base . $f); echo "元に戻しました: {$f}\n"; }
    foreach (array_keys($new) as $p) if (is_file($base . $p)) { unlink($base . $p); echo "消しました: {$p}\n"; }
    $routes();
    echo $artisan('queue:restart') . "\n";
    exit("元に戻しました(足した欄・表はそのまま残っていますが、使われません)。\n");
}

require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };

if ($mode === 'optline') {
    $sid = (int) ($argv[2] ?? 0);
    if (!$sid || !in_array($argv[3] ?? '', ['on', 'off'], true)) exit("使い方:optline 店舗ID on|off\n");
    if (!class_exists('App\Libs\CsLine')) exit("先に apply してください\n");
    App\Libs\CsReserveCh::setOptLine($sid, ($argv[3] ?? '') === 'on');
    echo "店舗{$sid}:LINE オプション " . (($argv[3] ?? '') === 'on' ? 'ご契約中(試験)' : '未契約') . " にしました\n";
    echo "今の状態:" . json_encode(App\Libs\CsReserveCh::get($sid), JSON_UNESCAPED_UNICODE) . "\n";
    exit;
}

$db = Illuminate\Support\Facades\DB::connection(); $pre = $db->getTablePrefix();
$colsOf = function ($t) use ($db, $pre) { return array_map(function ($c) { return $c->Field; }, $db->select("SHOW COLUMNS FROM `{$pre}{$t}`")); };
$hasT = function ($t) use ($db, $pre) { return count($db->select("SHOW TABLES LIKE '{$pre}{$t}'")) > 0; };
$addShop = array_values(array_diff(['cs_opt_line', 'reserve_ch_line'], $colsOf('web_shop')));
$addOrder = array_values(array_diff(['cs_line_uid'], $colsOf('web_order')));
foreach (['cs_opt_sms', 'reserve_ch_mail', 'reserve_ch_sms'] as $c) if (!in_array($c, $colsOf('web_shop'))) $ng("web_shop に {$c} がありません(段階8-1 が先)");
echo "① 欄:web_shop " . ($addShop ? implode(', ', $addShop) . ' を足す' : '(もうある)') . " / web_order " . ($addOrder ? 'cs_line_uid を足す' : '(もうある)') . "\n";
echo "   表:cs_shop_line " . ($hasT('cs_shop_line') ? '(もうある)' : 'を作る') . " / cs_line_follow " . ($hasT('cs_line_follow') ? '(もうある)' : 'を作る') . "\n";
$m = new App\Models\OrderModel();
if ($m->getFillable()) $ng('OrderModel に fillable の決まりがあります(Claude が直し方を変えます)');
if ($m->getGuarded() === ['*']) $ng('OrderModel は guarded *');
if (!class_exists('GuzzleHttp\Client')) $ng('GuzzleHttp がありません');
if (!config('app.key')) $ng('APP_KEY がありません(暗号化に使う)');
foreach (array_keys($new) as $p) if (file_exists($base . $p)) $ng("{$p} がすでにあります");
echo "② 新しいファイル 4つ\n";

// CsReserveCh(入れ替え)
$sc = file_get_contents($base . $fCh);
if (strpos($sc, "'line' => 0, 'opt_sms' => \$opt ? 1 : 0, 'opt_line' => 0") === false || strpos($sc, 'public static function set($sid, $mail, $sms)') === false) $ng('CsReserveCh が段階8-1 のときの形ではありません');
echo "③ {$fCh}:入れ替える(LINE を足す)\n";

// CsReserveChController
$scc = file_get_contents($base . $fChCtl);
$a = "\$r = CsReserveCh::set(\$u->sid, (int) \$request->input('mail', 1) === 1, (int) \$request->input('sms', 0) === 1);";
if (substr_count($scc, $a) !== 1) $ng('CsReserveChController の保存の行が見つかりません');
$scc2 = str_replace($a, "\$r = CsReserveCh::set(\$u->sid, (int) \$request->input('mail', 1) === 1, (int) \$request->input('sms', 0) === 1, \$request->has('line') ? (int) \$request->input('line') === 1 : null); // {$tag} 段階9:line", $scc);
echo "④ {$fChCtl}:保存に line を足す\n";

// 送る場所(}elseif(email) の前に LINE)
$reE = '/\n([ \t]*)\}elseif\(\$order->email \?\? \'\'\)\{\n([ \t]*\/\/[^\n]*\n[ \t]*)\\\\App\\\\Libs\\\\CsReserveMail::send\(\'(confirm|update|cancel|notice)\'/';
$putLine = function ($src, $want, $label) use ($reE, $tag, $ng) {
    if (strpos($src, 'CsLine::sendOrder') !== false) $ng("{$label} はすでに修正済みです");
    $n = preg_match_all($reE, $src, $mm);
    if ($n !== count($want) || $mm[3] !== $want) $ng("{$label} の送る場所が予定と違います(見つかった数 {$n}:" . implode(',', $mm[3]) . ')');
    return preg_replace_callback($reE, function ($m) use ($tag) {
        return "\n{$m[1]}}elseif(\\App\\Libs\\CsLine::sendOrder('{$m[3]}', \$order)){\n{$m[1]}    // {$tag} 段階9:LINE で受けた予約は LINE で送る(届かなくてもメール・SMS に切り替えない)\n{$m[1]}}elseif(\$order->email ?? ''){\n{$m[2]}\\App\\Libs\\CsReserveMail::send('{$m[3]}'";
    }, $src);
};
$sso = file_get_contents($base . $fSo);
$sso2 = $putLine($sso, ['confirm', 'update', 'cancel'], 'Shop/OrderController');
$aShow = "            'email' => (string) (\$order->email ?? ''), // [カスタムエイド 2026-10-31] お客様のメールアドレス\n";
if (substr_count($sso2, $aShow) !== 1) $ng('Shop/OrderController の show の email の行が見つかりません');
$sso2 = str_replace($aShow, $aShow . "            'cs_line' => \\App\\Libs\\CsLine::orderState(\$order), // {$tag} 段階9:LINE の予約(''/ok/ng = ブロック・設定の解除で届かない)\n", $sso2);
echo "⑤ {$fSo}:受付・変更・キャンセルに LINE、予約詳細に cs_line\n";

$sj = file_get_contents($base . $fJ);
$sj2 = $putLine($sj, ['notice'], 'OrderNoticeJob');
echo "⑥ {$fJ}:前日のリマインダーに LINE\n";

// Member/OrderController
$smo = file_get_contents($base . $fMo);
if (strpos($smo, 'CsLine') !== false) $ng('Member/OrderController はすでに修正済みです');
$reM = '/\n([ \t]*)if\(\$order->email \?\? \'\'\)\{\n([ \t]*\/\/[^\n]*\n[ \t]*)\\\\App\\\\Libs\\\\CsReserveMail::send\(\'confirm\'/';
if (preg_match_all($reM, $smo) !== 1) $ng('Member/OrderController の送る場所が見つかりません');
$smo2 = preg_replace_callback($reM, function ($m) use ($tag) {
    return "\n{$m[1]}if(\\App\\Libs\\CsLine::sendOrder('confirm', \$order)){\n{$m[1]}    // {$tag} 段階9:LINE で受け取る予約は LINE で送る\n{$m[1]}}elseif(\$order->email ?? ''){\n{$m[2]}\\App\\Libs\\CsReserveMail::send('confirm'";
}, $smo);
$p1 = "            if (\$cs_pick !== 'mail' && \$cs_pick !== 'sms') \$cs_pick = \$cs_chs['mail'] ? 'mail' : 'sms';\n";
$p2 = "            if (\$cs_pick === 'sms') {\n";
if (substr_count($smo2, $p1) !== 1 || substr_count($smo2, $p2) !== 1) $ng('Member/OrderController の受け取り方法の所(段階8-1)が見つかりません');
$smo2 = str_replace($p1, "            if (\$cs_pick !== 'mail' && \$cs_pick !== 'sms' && \$cs_pick !== 'line') \$cs_pick = \$cs_chs['mail'] ? 'mail' : 'sms'; // {$tag} 段階9:line\n", $smo2);
$smo2 = str_replace($p2, "            if (\$cs_pick === 'line') {\n"
    . "                // {$tag} 段階9:LINE で受け取る(予約WEB で LINE とつないだ合言葉 cs_lt。友だち追加まで済んでいること)\n"
    . "                if (!\$cs_chs['line']) {\n                    return responseToJson(400, 'このお店では、LINE での受け取りは選べません');\n                }\n"
    . "                if (trim((string) (\$request->phone ?? '')) === '') {\n                    return responseToJson(400, '電話番号を入力してください');\n                }\n"
    . "                \$cs_lt = \\App\\Libs\\CsLine::ticket((string) (\$request->cs_lt ?? ''), \$menu->sid);\n"
    . "                if (!\$cs_lt) {\n                    return responseToJson(400, 'LINE とのつながりが切れました。もう一度「LINE で受け取る」を押してください');\n                }\n"
    . "                if (!\$cs_lt['friend']) {\n                    return responseToJson(400, 'お店の LINE を友だち追加してください。追加すると予約できます');\n                }\n"
    . "                \$data['cs_line_uid'] = \$cs_lt['uid'];\n"
    . "                \$data['email'] = ''; // メールは送らない\n"
    . "            } elseif (\$cs_pick === 'sms') {\n", $smo2);
echo "⑦ {$fMo}:LINE を選んだ予約を受け付ける・受付のお知らせに LINE\n";

// routes
$sr = file_get_contents($base . $fR);
if (strpos($sr, 'CsLinePublicController') !== false) $ng('routes/api.php はすでに修正済みです');
$sr2 = rtrim($sr) . "\n\n// {$tag} 段階9:LINE で予約のお知らせ。鍵・LINE の合図が my_api の記録に残らないよう api.request を通さない\n"
    . "Route::namespace('Api')->middleware(['lang'])->prefix('api')->group(function(){\n"
    . "    Route::get('/cs-line/callback', 'CsLinePublicController@callback');\n"
    . "    Route::post('/cs-line/webhook/{sid}', 'CsLinePublicController@webhook')->where('sid', '[0-9]+');\n"
    . "    Route::get('/member/cs-line/auth', 'Member\\CsLineController@auth');\n"
    . "    Route::get('/member/cs-line/ticket', 'Member\\CsLineController@ticket');\n"
    . "    Route::middleware('shop.auth')->group(function(){\n"
    . "        Route::get('/shop/cs/line', 'Shop\\CsLineController@show');\n"
    . "        Route::post('/shop/cs/line', 'Shop\\CsLineController@save');\n"
    . "        Route::post('/shop/cs/line/remove', 'Shop\\CsLineController@remove');\n"
    . "        Route::get('/shop/cs/line/test-url', 'Shop\\CsLineController@testUrl');\n"
    . "    });\n"
    . "});\n";
echo "⑧ {$fR}:LINE の入口を足す\n";

$tmp = sys_get_temp_dir() . '/cs_s9_';
$chk = ['lib.php' => SRC_LIB, 'ch.php' => SRC_CH, 'shopctl.php' => SRC_SHOPCTL, 'memctl.php' => SRC_MEMCTL, 'pubctl.php' => SRC_PUBCTL,
    'chctl.php' => $scc2, 'so.php' => $sso2, 'j.php' => $sj2, 'mo.php' => $smo2, 'r.php' => $sr2];
foreach ($chk as $n => $src) { file_put_contents($tmp . $n, $src); $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp . $n) . ' 2>&1', $o, $c); @unlink($tmp . $n); echo ($c === 0 ? 'OK ' : 'NG ') . "直したあとの文法 {$n}\n"; if ($c !== 0) $ng(implode(' ', $o)); }

if ($mode !== 'apply') { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }

if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($files as $f) copy($base . $f, $bk($f));
$defs = [];
if (in_array('cs_opt_line', $addShop)) $defs[] = "ADD `cs_opt_line` TINYINT NOT NULL DEFAULT 0";
if (in_array('reserve_ch_line', $addShop)) $defs[] = "ADD `reserve_ch_line` TINYINT NOT NULL DEFAULT 0";
if ($defs) $db->statement("ALTER TABLE `{$pre}web_shop` " . implode(', ', $defs));
if ($addOrder) $db->statement("ALTER TABLE `{$pre}web_order` ADD `cs_line_uid` VARCHAR(64) NOT NULL DEFAULT ''");
if (!$hasT('cs_shop_line')) $db->statement("CREATE TABLE `{$pre}cs_shop_line` (`sid` INT UNSIGNED NOT NULL PRIMARY KEY, `msg_secret` TEXT, `msg_token` TEXT, `login_id` VARCHAR(32) NOT NULL DEFAULT '', `login_secret` TEXT, `bot_name` VARCHAR(100) NOT NULL DEFAULT '', `bot_id` VARCHAR(64) NOT NULL DEFAULT '', `quota_cache` VARCHAR(255) NOT NULL DEFAULT '', `quota_time` INT NOT NULL DEFAULT 0, `update_time` INT NOT NULL DEFAULT 0) DEFAULT CHARSET=utf8mb4");
if (!$hasT('cs_line_follow')) $db->statement("CREATE TABLE `{$pre}cs_line_follow` (`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, `sid` INT UNSIGNED NOT NULL, `uid` VARCHAR(64) NOT NULL, `blocked` TINYINT NOT NULL DEFAULT 0, `update_time` INT NOT NULL DEFAULT 0, UNIQUE KEY `sid_uid` (`sid`, `uid`)) DEFAULT CHARSET=utf8mb4");
echo "欄・表を足しました\n";
foreach ($new as $p => $src) file_put_contents($base . $p, $src);
file_put_contents($base . $fCh, SRC_CH);
file_put_contents($base . $fChCtl, $scc2); file_put_contents($base . $fSo, $sso2); file_put_contents($base . $fJ, $sj2); file_put_contents($base . $fMo, $smo2); file_put_contents($base . $fR, $sr2);
$ok = true;
foreach (array_merge(array_keys($new), $files) as $f) { $o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c); echo ($c === 0 ? 'OK ' : 'NG ') . "文法 {$f}\n"; if ($c !== 0) $ok = false; }
if (!$ok) {
    foreach ($files as $f) copy($bk($f), $base . $f);
    foreach (array_keys($new) as $p) @unlink($base . $p);
    exit("文法エラーがあったので元に戻しました(足した欄・表はそのままですが、使われません)。\n");
}
$ref = $base . $fChCtl;
foreach (array_keys($new) as $p) { @chown($base . $p, fileowner($ref)); @chgrp($base . $p, filegroup($ref)); }
$routes();
echo $artisan('queue:restart') . "\n";
$o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:list --columns=method,uri,middleware 2>&1 | grep -E "cs-line|cs/line"', $o);
echo "入口の確かめ:\n" . (implode("\n", $o) ?: '見つかりません(確認が必要)') . "\n";
echo "店舗11 の状態:" . json_encode(App\Libs\CsReserveCh::get(11), JSON_UNESCAPED_UNICODE) . "\n";
echo "コールバック URL:" . App\Libs\CsLine::callbackUrl() . "\n";
echo "店舗11 の Webhook URL:" . App\Libs\CsLine::webhookUrl(11) . "\n";
echo "\n完了しました。バックアップ: {$backup}\n元に戻すとき: php -d disable_functions= /root/s9_line_patch.php undo\n";
