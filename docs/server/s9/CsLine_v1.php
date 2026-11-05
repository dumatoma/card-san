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
        // 4. [カスタムエイド 2026-11-04 追記] LINE ログインの欄に Messaging API のチャネルの ID・シークレットを入れていないか
        //    (3 の確かめでは、Messaging API のチャネルでも通ってしまうため)
        if (!isset($err['login_secret']) && $k['login_secret'] === $k['msg_secret']) {
            $err['login_secret'] = 'Messaging API のチャネルシークレットと同じです。LINE ログインのチャネルのシークレットを入れてください';
        } elseif (!isset($err['login_secret'])) {
            list($c5, $st) = self::http('POST', 'https://api.line.me/v2/oauth/accessToken', [], null, ['grant_type' => 'client_credentials', 'client_id' => $k['login_id'], 'client_secret' => $k['login_secret']]);
            if ($c5 === 200 && !empty($st['access_token'])) {
                // [カスタムエイド 2026-11-04 追記2] 短い期間のトークンは LINE ログインのチャネルでも取れた。そのトークンで公式アカウントの情報が取れたら Messaging API のチャネル
                list($c6) = self::bot($st['access_token'], 'GET', '/v2/bot/info');
                self::http('POST', 'https://api.line.me/v2/oauth/revoke', [], null, ['access_token' => $st['access_token']]);
                if ($c6 === 200) $err['login_id'] = 'これは Messaging API のチャネルの ID です。LINE ログインのチャネルの ID とシークレットを入れてください';
            }
        }
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
        // [カスタムエイド 2026-11-04 追記3] += では 'ok' が false のまま残っていた(すでにある鍵は上書きされない)ので array_merge にする
        $out = array_merge($out, ['ok' => true, 'name' => mb_substr((string) ($pf['displayName'] ?? ''), 0, 50), 'friend' => $friend ? 1 : 0]);
        if ($st['mode'] === 'test') {
            $out['sent'] = 0;
            if ($friend) {
                $msg = [['type' => 'text', 'text' => "【Card-San】LINE 予約のお知らせのテストです。\nお客様には、この LINE 公式アカウント(" . $c['bot_name'] . ")から予約のお知らせが届きます。"]];
                list($p) = self::push($st['sid'], $uid, $msg);
                Log::info('CsLine test sid=' . (int) $st['sid'] . ' code=' . $p); // [カスタムエイド 2026-11-04 追記3]
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
