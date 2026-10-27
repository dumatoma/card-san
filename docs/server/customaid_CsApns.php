<?php

namespace App\Libs;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * [カスタムエイド 2026-10-25] プッシュ通知を Apple(APNs)に直接送るための部品。
 *   個推(GeTui)から Apple への受け渡しが止まっていて、アプリを閉じていると通知が出ないため。
 *   - bind():アプリから届いた iPhone の通知用の番号(デバイストークン)を保存する(bind_clientid から呼ぶ)
 *   - mirror():個推に送るのと同じ通知を、番号が分かっている iPhone には Apple にも送る(GeTuiApi::pushSingleBatchCid から呼ぶ)
 *   実際に Apple へ送るのは app/Console/cs_apns_sender.php(root の cron で動く)。ここでは送り待ちのファイルを置くだけ。
 *   鍵(.p8)は storage/app/cs_push/ に root だけが読める形で置く。
 */
class CsApns
{
    // 送り先のアプリ(Bundle ID)
    const TOPIC = ['user' => 'com.cardsan.customer', 'shop' => 'com.cardsan.kanri'];
    // 端末の番号を保存している表
    const TABLE = ['user' => 'web_user', 'shop' => 'web_shop_admin'];

    public static function spoolDir()
    {
        return storage_path('app/cs_push/spool/');
    }

    /**
     * アプリから届いた番号を保存する。
     * push_platform:ios / android、push_token:iPhone のデバイストークン(16進数)
     */
    public static function bind($user, $request)
    {
        try {
            if (!$user) return;
            $table = $user->getTable();
            $plat = strtolower((string) $request->input('push_platform', ''));
            $tok = strtolower(preg_replace('/[^0-9a-fA-F]/', '', (string) $request->input('push_token', '')));
            $up = [];
            if (in_array($plat, ['ios', 'android'])) $up['cs_push_platform'] = $plat;
            if ($plat == 'ios' && strlen($tok) >= 64 && strlen($tok) <= 200) {
                $up['cs_apns_token'] = $tok;
                // 同じ iPhone で別のアカウントに入り直したとき、前のアカウントに届かないようにする
                DB::table($table)->where('cs_apns_token', $tok)->where('id', '<>', $user->id)->update(['cs_apns_token' => null]);
            }
            if ($up) {
                $up['cs_push_at'] = time();
                DB::table($table)->where('id', $user->id)->update($up);
            }
        } catch (\Throwable $e) {
            Log::info('CsApns bind: ' . $e->getMessage());
        }
    }

    /**
     * 個推に送る通知(msg_list)のうち、Apple 用の番号が分かっている端末の分を、送り待ちに置く。
     */
    public static function mirror($type, $msg_list)
    {
        try {
            if (!isset(self::TABLE[$type]) || !is_array($msg_list)) return;
            $dir = self::spoolDir();
            if (!is_dir($dir)) return;

            $cids = [];
            foreach ($msg_list as $m) {
                foreach ((array) ($m['audience']['cid'] ?? []) as $c) if ($c) $cids[] = $c;
            }
            if (!$cids) return;

            $map = DB::table(self::TABLE[$type])
                ->whereIn('getui_clientid', array_values(array_unique($cids)))
                ->whereNotNull('cs_apns_token')->where('cs_apns_token', '<>', '')
                ->pluck('cs_apns_token', 'getui_clientid')->all();
            if (!$map) return;

            // Apple が「使えない番号」と返した番号は送らない(送る係が記録する)
            $bad = [];
            $bf = storage_path('app/cs_push/invalid_tokens.txt');
            if (is_file($bf)) $bad = array_flip(array_filter(array_map('trim', file($bf))));

            foreach ($msg_list as $m) {
                $body = self::body($m);
                if (!$body) continue;
                $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                foreach ((array) ($m['audience']['cid'] ?? []) as $c) {
                    $tok = $map[$c] ?? '';
                    if (!$tok || isset($bad[$tok])) continue;
                    self::spool($tok, self::TOPIC[$type], $json);
                }
            }
        } catch (\Throwable $e) {
            Log::info('CsApns mirror: ' . $e->getMessage());
        }
    }

    /**
     * 個推用の1件から、Apple に送る中身を作る(文面・バッジ・タップしたときの行き先)。
     */
    public static function body($m)
    {
        $ios = $m['push_channel']['ios'] ?? [];
        $aps = isset($ios['aps']) && is_array($ios['aps']) ? $ios['aps'] : [];
        if (empty($aps['alert'])) {
            // iPhone 用の指定がないときは、通知の文面から作る
            $n = $m['push_message']['notification'] ?? [];
            if (empty($n['title']) && empty($n['body'])) return null;
            $aps['alert'] = ['title' => (string) ($n['title'] ?? ''), 'body' => (string) ($n['body'] ?? '')];
        }
        unset($aps['content-available']);
        if (!isset($aps['sound'])) $aps['sound'] = 'default';
        $body = ['aps' => $aps];
        $payload = $ios['payload'] ?? ($m['push_message']['notification']['payload'] ?? null);
        if ($payload !== null) $body['payload'] = is_string($payload) ? $payload : json_encode($payload, JSON_UNESCAPED_UNICODE);
        return $body;
    }

    /**
     * 送り待ちのファイルを1つ置く(1行目:番号、2行目:アプリ、3行目:本番/開発、4行目:中身)
     */
    public static function spool($token, $topic, $json, $env = 'production')
    {
        $dir = self::spoolDir();
        $id = date('YmdHis') . '_' . str_replace('.', '', uniqid('', true)) . '_' . mt_rand(1000, 9999);
        $tmp = $dir . $id . '.tmp';
        file_put_contents($tmp, $token . "\n" . $topic . "\n" . $env . "\n" . str_replace(["\r", "\n"], ' ', $json) . "\n");
        @chmod($tmp, 0666);
        rename($tmp, $dir . $id . '.req');
    }
}
