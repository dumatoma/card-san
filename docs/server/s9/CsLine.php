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
 * [カスタムエイド 2026-11-04 v2] 仕様 §0(22〜28):
 *  ・お店が入れる鍵は「Messaging API のチャネル ID・シークレット/LINE ログインのチャネル ID・シークレット/LIFF ID」。
 *    送るためのトークンは、Messaging API の ID とシークレットからサーバーが作る(短い期間のトークン。30日。切れる前に作り直す)
 *  ・お客様は LINE アプリの中(LIFF)で予約を確定する:予約WEB が入れた内容を「下書き」で預け → LIFF の住所を開く → LIFF の ID トークンを LINE で確かめて LINE の ID を得る
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

    // 鍵(5つそろっていないときは null)。v2:msg_id・liff_id を足し、トークンは token() で作る
    public static function cfg($sid)
    {
        $r = self::row($sid);
        if (!$r) return null;
        $c = ['msg_id' => (string) ($r->msg_id ?? ''), 'msg_secret' => self::dec($r->msg_secret), 'login_id' => (string) $r->login_id, 'login_secret' => self::dec($r->login_secret),
            'liff_id' => (string) ($r->liff_id ?? ''), 'bot_name' => (string) $r->bot_name, 'bot_id' => (string) $r->bot_id];
        foreach (['msg_id', 'msg_secret', 'login_id', 'login_secret', 'liff_id'] as $k) if ($c[$k] === '') return null;
        return $c;
    }

    // ID とシークレットから短い期間のトークンを作る。戻り値 [番号, トークン, 何秒もつか]
    private static function issue($id, $secret)
    {
        list($code, $r) = self::http('POST', 'https://api.line.me/v2/oauth/accessToken', [], null, ['grant_type' => 'client_credentials', 'client_id' => $id, 'client_secret' => $secret]);
        return [$code, (string) ($r['access_token'] ?? ''), (int) ($r['expires_in'] ?? 0), (string) ($r['error'] ?? '')];
    }

    // 送るためのトークン(のこり1日を切っていたら、作り直す)
    public static function token($sid, $force = false)
    {
        $r = self::row($sid);
        $c = self::cfg($sid);
        if (!$r || !$c) return '';
        $t = self::dec($r->msg_token);
        if (!$force && $t !== '' && (int) ($r->token_exp ?? 0) > time() + 86400) return $t;
        list($code, $t, $exp) = self::issue($c['msg_id'], $c['msg_secret']);
        if ($code !== 200 || $t === '') { Log::error('CsLine token issue sid=' . (int) $sid . ' code=' . $code); return ''; }
        DB::table(self::T_SHOP)->where('sid', (int) $sid)->update(['msg_token' => Crypt::encryptString($t), 'token_exp' => time() + max(3600, $exp)]);
        return $t;
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
            'keys' => $c ? ['msg_id' => $c['msg_id'], 'msg_secret' => self::mask($c['msg_secret']), 'login_id' => $c['login_id'], 'login_secret' => self::mask($c['login_secret']), 'liff_id' => $c['liff_id']] : null,
            'quota' => $q,
            'callback_url' => self::callbackUrl(),
            'webhook_url' => self::webhookUrl($sid),
        ];
    }

    // 「保存して確かめる」。戻り値 ['ok' => bool, 'msg' => 文, 'errors' => [欄 => 理由]]。1つでも違えば保存しない(仕様 19・§0 の 25)
    public static function save($sid, array $in)
    {
        $k = [];
        foreach (['msg_id', 'msg_secret', 'login_id', 'login_secret', 'liff_id'] as $f) $k[$f] = trim((string) ($in[$f] ?? ''));
        $err = [];
        foreach ($k as $f => $v) if ($v === '') $err[$f] = '入力してください';
        foreach (['msg_id', 'login_id'] as $f) if ($k[$f] !== '' && !preg_match('/^\d{6,20}$/', $k[$f])) $err[$f] = 'チャネル ID は数字です';
        if ($err) return ['ok' => false, 'msg' => '入っていない欄があります', 'errors' => $err];
        // [カスタムエイド 2026-11-05 追記10] 同じものが2か所に入っているとき:2つの欄を両方赤くして、それぞれの欄に何を入れるかを書く
        $same = [];
        if ($k['msg_id'] === $k['login_id']) {
            $same['msg_id'] = '下の「LINE ログインのチャネル」のチャネル ID と同じ番号です。ここには、緑の「Messaging API」の印があるチャネルのチャネル ID を入れてください';
            $same['login_id'] = '上の「Messaging API のチャネル」のチャネル ID と同じ番号です。ここには、「LINEログイン」の印があるチャネルのチャネル ID を入れてください';
        }
        if ($k['msg_secret'] === $k['login_secret']) {
            $same['msg_secret'] = '下の「LINE ログインのチャネル」のチャネルシークレットと同じです。ここには、緑の「Messaging API」の印があるチャネルのチャネルシークレットを入れてください';
            $same['login_secret'] = '上の「Messaging API のチャネル」のチャネルシークレットと同じです。ここには、「LINEログイン」の印があるチャネルのチャネルシークレットを入れてください';
        }
        if ($same) return ['ok' => false, 'msg' => 'つなげませんでした。同じものが2か所に入っています', 'errors' => $same];

        // 1. Messaging API のチャネル:トークンが作れて、そのトークンで公式アカウントの情報が取れること
        list($c1, $t1, $e1, $er1) = self::issue($k['msg_id'], $k['msg_secret']);
        $info = [];
        if ($c1 === 0) return ['ok' => false, 'msg' => 'つなげませんでした', 'errors' => ['msg_secret' => 'LINE につながりませんでした。少し待ってからもう一度押してください']];
        if ($c1 !== 200 || $t1 === '') $err['msg_secret'] = 'チャネル ID とシークレットの組み合わせが合いません';
        else {
            list($c2, $info) = self::bot($t1, 'GET', '/v2/bot/info');
            if ($c2 !== 200) {
                self::http('POST', 'https://api.line.me/v2/oauth/revoke', [], null, ['access_token' => $t1]);
                $err['msg_id'] = 'これは LINE ログインのチャネルです。緑の「Messaging API」の印があるチャネルの ID とシークレットを入れてください';
            }
        }
        // 2. LINE ログインのチャネル:トークンは作れるが、公式アカウントの情報は取れないこと
        list($c3, $t3) = self::issue($k['login_id'], $k['login_secret']);
        if ($c3 !== 200 || $t3 === '') $err['login_secret'] = 'チャネル ID とシークレットの組み合わせが合いません';
        else {
            list($c4) = self::bot($t3, 'GET', '/v2/bot/info');
            self::http('POST', 'https://api.line.me/v2/oauth/revoke', [], null, ['access_token' => $t3]);
            if ($c4 === 200) $err['login_id'] = 'これは Messaging API のチャネルです。「LINEログイン」の印があるチャネルの ID とシークレットを入れてください';
        }
        // 3. LIFF ID:LINE ログインのチャネルの「LIFF」タブにあるもの(「チャネル ID-英数字」の形)
        if (!preg_match('/^(\d{6,20})-[A-Za-z0-9]{4,32}$/', $k['liff_id'], $lm)) $err['liff_id'] = 'LIFF ID の形が違います。LINE ログインのチャネルの「LIFF」タブにある LIFF ID を入れてください';
        elseif ($lm[1] !== $k['login_id']) $err['liff_id'] = 'この LIFF ID は、入れた LINE ログインのチャネルのものではありません';
        if ($err) {
            if ($t1 !== '' && !isset($err['msg_id'])) self::http('POST', 'https://api.line.me/v2/oauth/revoke', [], null, ['access_token' => $t1]);
            return ['ok' => false, 'msg' => 'つなげませんでした', 'errors' => $err];
        }

        $row = ['msg_id' => $k['msg_id'], 'msg_secret' => Crypt::encryptString($k['msg_secret']), 'msg_token' => Crypt::encryptString($t1), 'token_exp' => time() + max(3600, $e1),
            'login_id' => $k['login_id'], 'login_secret' => Crypt::encryptString($k['login_secret']), 'liff_id' => $k['liff_id'],
            'bot_name' => mb_substr((string) ($info['displayName'] ?? ''), 0, 100), 'bot_id' => (string) ($info['basicId'] ?? ''),
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
        $tk = self::token($sid);
        if ($tk === '') return null;
        list($a, $qa) = self::bot($tk, 'GET', '/v2/bot/message/quota');
        list($b, $qb) = self::bot($tk, 'GET', '/v2/bot/message/quota/consumption');
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
        $tk = $uid ? self::token($sid) : '';
        if ($tk === '') return false;
        list($code) = self::bot($tk, 'GET', '/v2/bot/profile/' . rawurlencode($uid));
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
        $tk = self::token($sid);
        if ($tk === '') return [0, []];
        $key = self::uuid();
        $r = self::http('POST', 'https://api.line.me/v2/bot/message/push', ['Authorization' => 'Bearer ' . $tk, 'X-Line-Retry-Key' => $key], ['to' => $uid, 'messages' => $messages]);
        if ($r[0] === 401) { // トークンが使えなくなっていたら、作り直してもう1回
            $tk = self::token($sid, true);
            if ($tk !== '') $r = self::http('POST', 'https://api.line.me/v2/bot/message/push', ['Authorization' => 'Bearer ' . $tk, 'X-Line-Retry-Key' => $key], ['to' => $uid, 'messages' => $messages]);
        }
        return $r;
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
        // [カスタムエイド 2026-11-04 追記4] 坂本さん:ふつうのトークより文字が小さい → 文字を1段大きく(sm→md、見出し md→lg、お店 xs→sm)、LINE の文字の大きさの設定に合わせて変わるように(scaling)
        $tx = function ($s, $extra = []) { return array_merge(['type' => 'text', 'text' => (string) $s, 'wrap' => true, 'size' => 'md', 'color' => '#333333', 'scaling' => true], $extra); };
        $body = [$tx($subject, ['weight' => 'bold', 'size' => 'lg', 'color' => '#111111'])];
        $nm = trim(($p['name'] ?? '') . ' ' . ($p['honor'] ?? ''));
        if ($nm !== '') $body[] = $tx($nm, ['margin' => 'md']);
        if (($p['top'] ?? '') !== '') $body[] = $tx($p['top'], ['margin' => 'md']);
        $rows = [];
        foreach ($p['rows'] ?? [] as $r) {
            $v = (string) ($r[1] ?? '');
            // [カスタムエイド 2026-11-04 追記4] 価格はお店が入れた文字のまま(「5,000〜」を数にすると 5 になっていた)
            if ($r[0] === 'メニュー' && trim((string) ($r[2] ?? '')) !== '') { $pr = trim((string) $r[2]); $v .= '(' . (preg_match('/^[0-9]/', $pr) ? '￥' : '') . $pr . ')'; }
            if ($v === '') continue;
            $rows[] = ['type' => 'box', 'layout' => 'baseline', 'spacing' => 'sm', 'contents' => [
                $tx($r[0], ['color' => '#888888', 'flex' => 2]), $tx($v, ['flex' => 5])]];
        }
        if ($rows) $body[] = ['type' => 'box', 'layout' => 'vertical', 'margin' => 'lg', 'spacing' => 'sm', 'contents' => $rows];
        foreach ([$p['mid'] ?? '', $p['bottom'] ?? ''] as $s) if ($s !== '') $body[] = $tx($s, ['margin' => 'lg']);
        $sh = array_values(array_filter([$shop, ($p['shop']['phone'] ?? '') !== '' ? '電話:' . $p['shop']['phone'] : '', (string) ($p['shop']['address'] ?? '')]));
        if ($sh) $body[] = $tx(implode("\n", $sh), ['margin' => 'lg', 'size' => 'sm', 'color' => '#888888']);
        $bubble = ['type' => 'bubble', 'body' => ['type' => 'box', 'layout' => 'vertical', 'contents' => $body]];
        if ($code) {
            // [カスタムエイド 2026-11-04 追記5] 坂本さん:「予約の詳しいページを見る」を大きく(lg)・太字に。LINE のボタンは文字の大きさを変えられないので、押せる文字にした
            $bubble['footer'] = ['type' => 'box', 'layout' => 'vertical', 'paddingAll' => '16px', 'contents' => [
                // [カスタムエイド 2026-11-04 追記7] 坂本さん:文を「予約の詳細はこちら」に
                ['type' => 'text', 'text' => '予約の詳細はこちら', 'size' => 'lg', 'weight' => 'bold', 'color' => '#06A64C', 'align' => 'center', 'scaling' => true,
                    'action' => ['type' => 'uri', 'label' => '予約の詳細はこちら', 'uri' => CsSmsPage::base() . $code]]]];
        }
        return ['type' => 'flex', 'altText' => mb_substr($subject, 0, 100), 'contents' => $bubble];
    }

    // ---------- LIFF(LINE アプリの中で予約を確定する。§0 の 22・23) ----------
    // 予約WEB が入れた内容を預ける(60分)。mode = order(予約)/ test(試しに自分に送る)。戻り値 [下書きの番号, LIFF の住所]
    public static function draftPut($sid, $mode, array $data)
    {
        $c = self::cfg($sid);
        if (!$c) return ['', ''];
        $d = bin2hex(random_bytes(16));
        Cache::put('cs_line_df_' . $d, ['sid' => (int) $sid, 'mode' => $mode, 'data' => $data], 3600);
        return [$d, 'https://liff.line.me/' . rawurlencode($c['liff_id']) . '?d=' . $d];
    }

    // LIFF のページが下書きを読む
    public static function draftGet($d)
    {
        $x = preg_match('/^[0-9a-f]{32}$/', (string) $d) ? Cache::get('cs_line_df_' . $d) : null;
        if (!is_array($x)) return null;
        $c = self::cfg($x['sid']);
        if (!$c) return null;
        return ['sid' => $x['sid'], 'mode' => $x['mode'], 'data' => $x['data'], 'liff_id' => $c['liff_id'], 'bot_name' => $c['bot_name'],
            'add_url' => $c['bot_id'] !== '' ? 'https://line.me/R/ti/p/' . rawurlencode($c['bot_id']) : ''];
    }

    // LIFF の ID トークンを LINE で確かめて、お客様の LINE の ID を得る。order なら合言葉(ticket)、test なら試し送り
    // 戻り値 ['ok', 'err', 'mode', 'ticket', 'name', 'friend', 'sent', 'add_url']
    public static function liff($d, $idToken)
    {
        $x = self::draftGet($d);
        if (!$x) return ['ok' => false, 'err' => 'expired'];
        $c = self::cfg($x['sid']);
        list($code, $v) = self::http('POST', 'https://api.line.me/oauth2/v2.1/verify', [], null, ['id_token' => (string) $idToken, 'client_id' => $c['login_id']]);
        $uid = (string) ($v['sub'] ?? '');
        if ($code !== 200 || $uid === '') { Log::error('CsLine liff verify sid=' . $x['sid'] . ' code=' . $code . ' ' . ($v['error'] ?? '')); return ['ok' => false, 'err' => 'verify']; }
        $friend = self::isFriend($x['sid'], $uid);
        $out = ['ok' => true, 'err' => '', 'mode' => $x['mode'], 'name' => mb_substr((string) ($v['name'] ?? ''), 0, 50), 'friend' => $friend ? 1 : 0, 'add_url' => $x['add_url']];
        if ($x['mode'] === 'test') {
            $out['sent'] = 0;
            if ($friend) {
                list($p) = self::push($x['sid'], $uid, [['type' => 'text', 'text' => "【Card-San】LINE 予約のお知らせのテストです。\nお客様には、この LINE 公式アカウント(" . $c['bot_name'] . ")から予約のお知らせが届きます。"]]);
                Log::info('CsLine test(liff) sid=' . (int) $x['sid'] . ' code=' . $p);
                $out['sent'] = $p === 200 ? 1 : 0;
                if ($p === 429) self::setOver($x['sid']);
            }
            return $out;
        }
        $t = bin2hex(random_bytes(16));
        Cache::put('cs_line_tk_' . $t, ['sid' => (int) $x['sid'], 'uid' => $uid, 'name' => $out['name']], 3600);
        $out['ticket'] = $t;
        return $out;
    }

    // [カスタムエイド 2026-11-04 追記6] LINE の中で予約が終わったことを預ける(予約WEB の元の画面が、戻ったときに予約完了の画面に進むため。60分)
    public static function draftDone($d, array $order)
    {
        $x = self::draftGet($d);
        if (!$x || $x['mode'] !== 'order') return false;
        $keep = [];
        foreach (['name', 'order_no', 'start_time', 'amount', 'remark', 'appoint_name', 'menu_name', 'sid', 'phone', 'appoint_amount'] as $k) $keep[$k] = isset($order[$k]) && is_scalar($order[$k]) ? (string) $order[$k] : '';
        if ($keep['order_no'] === '') return false;
        Cache::put('cs_line_dn_' . $d, ['order' => $keep, 'bot_name' => $x['bot_name']], 3600);
        return true;
    }

    public static function draftStatus($d)
    {
        $x = preg_match('/^[0-9a-f]{32}$/', (string) $d) ? Cache::get('cs_line_dn_' . $d) : null;
        return is_array($x) ? $x : null;
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