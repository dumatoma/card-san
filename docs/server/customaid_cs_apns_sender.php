<?php
// [カスタムエイド 2026-10-25] Apple(APNs)に通知を送る係。
//   app/Libs/CsApns.php が storage/app/cs_push/spool/ に置いた送り待ちのファイルを、サーバーの curl コマンド(HTTP/2 対応)で Apple に送る。
//   (PHP の curl の部品は HTTP/2 に対応していないため、curl コマンドを使う)
//   root の crontab から毎分起動し、約55秒のあいだ1秒ごとに送り待ちを見る(2つ同時には動かない)。
//   * * * * * /www/server/php/73/bin/php -d disable_functions= /www/wwwroot/api.card-san.jp/app/Console/cs_apns_sender.php >/dev/null 2>&1
//   記録:storage/logs/cs_apns/日付.log(番号は先頭8文字だけ)
$base = '/www/wwwroot/api.card-san.jp/';
$dir = $base . 'storage/app/cs_push/';
$spool = $dir . 'spool/';
$logd = $base . 'storage/logs/cs_apns/';
$KEY_ID = 'ALM46S5TRS';
$TEAM_ID = 'KB35CW7SD9';

$lock = @fopen($dir . 'sender.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) exit(0);
if (!is_dir($logd)) @mkdir($logd, 0755, true);

function cs_log($s)
{
    global $logd;
    @file_put_contents($logd . date('Y-m-d') . '.log', date('Y-m-d H:i:s') . ' ' . $s . "\n", FILE_APPEND);
}
function b64u($s) { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
// openssl の署名(DER)を、JWT の ES256 の形(r と s を32バイトずつ)に直す
function der2raw($der)
{
    if (strlen($der) < 8 || ord($der[0]) != 0x30) return false;
    $p = 2;
    if (ord($der[1]) & 0x80) $p = 2 + (ord($der[1]) & 0x7f);
    if (ord($der[$p]) != 0x02) return false;
    $rl = ord($der[$p + 1]); $r = substr($der, $p + 2, $rl); $p += 2 + $rl;
    if (ord($der[$p]) != 0x02) return false;
    $sl = ord($der[$p + 1]); $s = substr($der, $p + 2, $sl);
    $r = str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT);
    $s = str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);
    return $r . $s;
}
function make_jwt($key, $kid, $team)
{
    $h = b64u(json_encode(['alg' => 'ES256', 'kid' => $kid]));
    $c = b64u(json_encode(['iss' => $team, 'iat' => time()]));
    $pk = openssl_pkey_get_private($key);
    if (!$pk || !openssl_sign($h . '.' . $c, $der, $pk, OPENSSL_ALGO_SHA256)) return false;
    $raw = der2raw($der);
    return $raw ? $h . '.' . $c . '.' . b64u($raw) : false;
}
// HTTP/2 に対応した curl コマンドを探す
function find_curl()
{
    foreach (['/usr/bin/curl', '/usr/local/bin/curl', '/bin/curl', 'curl'] as $c) {
        $o = [];
        @exec(escapeshellarg($c) . ' -V 2>/dev/null', $o);
        if ($o && stripos(implode(' ', $o), 'nghttp2') !== false) return $c;
    }
    return false;
}

$key = @file_get_contents($dir . 'AuthKey_' . $KEY_ID . '.p8');
if (!$key) { cs_log('鍵がありません'); exit(1); }
$curl = find_curl();
if (!$curl) { cs_log('HTTP/2 対応の curl コマンドがありません'); exit(1); }

$jwt = false; $jwtAt = 0;
$end = time() + 55;
while (time() < $end) {
    $files = glob($spool . '*.req') ?: [];
    sort($files);
    if (!$files) { sleep(1); continue; }
    foreach ($files as $f) {
        $w = substr($f, 0, -4) . '.work';
        if (!@rename($f, $w)) continue;
        $L = @file($w, FILE_IGNORE_NEW_LINES);
        @unlink($w);
        if (!$L || count($L) < 4) { cs_log('読めないファイル ' . basename($f)); continue; }
        list($tok, $topic, $env, $json) = $L;
        if (!preg_match('/^[0-9a-f]{64,200}$/', $tok) || !preg_match('/^[A-Za-z0-9.\-]+$/', $topic)) { cs_log('番号かアプリの指定がおかしい'); continue; }
        // 認証の札(JWT)は30分ごとに作り直す(Apple は20分〜60分を求めている)
        if (!$jwt || time() - $jwtAt > 1800) {
            $jwt = make_jwt($key, $KEY_ID, $TEAM_ID);
            $jwtAt = time();
            if (!$jwt) { cs_log('JWT を作れません: ' . openssl_error_string()); exit(1); }
        }
        $host = $env == 'sandbox' ? 'https://api.sandbox.push.apple.com' : 'https://api.push.apple.com';
        $cmd = escapeshellarg($curl) . ' -s --http2 --max-time 15'
            . ' -H ' . escapeshellarg('authorization: bearer ' . $jwt)
            . ' -H ' . escapeshellarg('apns-topic: ' . $topic)
            . ' -H ' . escapeshellarg('apns-push-type: alert')
            . ' -H ' . escapeshellarg('apns-priority: 10')
            . ' -H ' . escapeshellarg('content-type: application/json')
            . ' --data-binary ' . escapeshellarg($json)
            . ' -w ' . escapeshellarg("\n%{http_code}")
            . ' ' . escapeshellarg($host . '/3/device/' . $tok) . ' 2>&1';
        $out = [];
        exec($cmd, $out);
        $code = trim((string) array_pop($out));
        $resp = trim(implode(' ', $out));
        $short = substr($topic, strrpos($topic, '.') + 1) . ' ' . substr($tok, 0, 8);
        if ($code === '200') {
            cs_log("OK $short");
        } else {
            cs_log("NG $short HTTP $code $resp");
            // 使えなくなった番号は、次から送らない
            if ($code === '410' || stripos($resp, 'BadDeviceToken') !== false || stripos($resp, 'Unregistered') !== false) {
                @file_put_contents($dir . 'invalid_tokens.txt', $tok . "\n", FILE_APPEND);
            }
            // 認証の札が古い・おかしいときは作り直す
            if (stripos($resp, 'ExpiredProviderToken') !== false || stripos($resp, 'InvalidProviderToken') !== false) $jwt = false;
        }
    }
}
