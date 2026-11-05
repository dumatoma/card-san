<?php
// [カスタムエイド 2026-11-05] 段階9 追記11:Card-San App の LINE ログイン(チャネル 1657727494)
//   ・シークレットを Line.php から .env(CS_LINE_LOGIN_SECRET)に移す(今のシークレットは画面に出さずにそのまま移す)
//   ・古いログインの入口(/api/member/line/get_login_url。合言葉が固定の 12345abcde)を止める。今の Card-San App は使っていない(坂本さん:ストアの古い版は誰も使っていない)
//   php -d disable_functions= /root/s9_line_fix11.php            … 確かめるだけ
//   php -d disable_functions= /root/s9_line_fix11.php apply      … バックアップを取ってから変更する
//   php -d disable_functions= /root/s9_line_fix11.php newsecret  … LINE Developers で作り直した新しいシークレットを貼って .env に入れる(文字は出ません)
//   php -d disable_functions= /root/s9_line_fix11.php test       … 今の .env のシークレットが LINE で使えるかだけ確かめる
//   php -d disable_functions= /root/s9_line_fix11.php undo       … 元に戻す(Line.php・LineController.php・.env・config/csline.php)
// 鍵はこの画面にも記録にも出しません。チャットにも貼らないでください

$base = '/www/wwwroot/api.card-san.jp/';
$fLine = $base . 'app/Libs/Line.php';
$fCtl = $base . 'app/Http/Controllers/Api/Member/LineController.php';
$fEnv = $base . '.env';
$fCfg = $base . 'config/csline.php';
$backup = '/www/backup_customaid_s9fix11_20261105/';
$mode = $argv[1] ?? 'check';
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };

// LINE に「でたらめなコード」で問い合わせて、チャネル ID とシークレットの組み合わせが正しいかだけを見る
//   正しい → invalid_grant(コードが違う)/ 違う → invalid_client
$test = function ($id, $secret) {
    $body = http_build_query(['grant_type' => 'authorization_code', 'code' => 'cs_check_only', 'redirect_uri' => 'https://api.card-san.jp/api/member/api_login_redirect/line', 'client_id' => $id, 'client_secret' => $secret]);
    $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $body, 'timeout' => 10, 'ignore_errors' => true]]);
    $r = @file_get_contents('https://api.line.me/oauth2/v2.1/token', false, $ctx);
    if ($r === false) return 'LINE につながりませんでした';
    $j = json_decode($r, true);
    $e = (string) ($j['error'] ?? '');
    if ($e === 'invalid_grant') return 'OK';
    if ($e === 'invalid_client') return 'シークレット(またはチャネル ID)が合いません';
    return 'わかりませんでした(' . $e . ')';
};
$envGet = function ($s, $k) { return preg_match('/^' . $k . '=(.*)$/m', $s, $m) ? trim($m[1], " \t\"'") : null; };

if ($mode === 'undo') {
    if (!is_file($backup . 'Line.php') || !is_file($backup . 'env')) exit("バックアップがありません。何もしていません。\n");
    copy($backup . 'Line.php', $fLine);
    if (is_file($backup . 'LineController.php')) copy($backup . 'LineController.php', $fCtl);
    copy($backup . 'env', $fEnv);
    if (is_file($fCfg)) unlink($fCfg);
    exit("元に戻しました(Line.php・LineController.php・.env。config/csline.php は消しました)\n");
}

if ($mode === 'test' || $mode === 'newsecret') {
    $env = file_get_contents($fEnv);
    $id = $envGet($env, 'CS_LINE_LOGIN_ID');
    if ($id === null || $envGet($env, 'CS_LINE_LOGIN_SECRET') === null) exit("先に apply を実行してください。何もしていません。\n");
    if ($mode === 'test') exit('今の .env のシークレット:' . $test($id, $envGet($env, 'CS_LINE_LOGIN_SECRET')) . "\n");
    echo "LINE Developers →「Card-San」のプロバイダー → チャネル ID {$id}(LINEログイン)→「チャネル基本設定」→「チャネルシークレット」\nの新しいシークレットを貼って Enter を押してください(貼っても文字は出ません):";
    @system('stty -echo');
    $v = trim((string) fgets(STDIN));
    @system('stty echo');
    echo "\n";
    if (!preg_match('/^[0-9a-f]{32}$/', $v)) exit("形が違います(英数字32文字のはずです。受け取った長さ " . strlen($v) . "文字)。何も変更していません。\n");
    $r = $test($id, $v);
    if ($r !== 'OK') exit("LINE で確かめました:{$r}\n.env は変えていません。\n");
    copy($fEnv, $backup . 'env_before_newsecret_' . date('His'));
    $env = preg_replace('/^CS_LINE_LOGIN_SECRET=.*$/m', 'CS_LINE_LOGIN_SECRET=' . $v, $env, 1);
    file_put_contents($fEnv, $env);
    exit("OK 新しいシークレットを .env に入れました(LINE で確かめ済み)。iPhone の Card-San App で「LINE でサインイン」を試してください。\n");
}

// check / apply
if (!is_file($fLine)) $ng('app/Libs/Line.php がありません');
$s = file_get_contents($fLine);
$env = file_get_contents($fEnv);
if (strpos($s, '追記11') !== false) $ng('すでに修正済みです');
if (is_file($fCfg)) $ng('config/csline.php がもうあります');
if ($envGet($env, 'CS_LINE_LOGIN_SECRET') !== null) $ng('.env に CS_LINE_LOGIN_SECRET がもうあります');
if (!preg_match("/^    private \\\$login_client_id = '(\\d+)';\$/m", $s, $mi)) $ng('Line.php のチャネル ID の行が見つかりません');
if (!preg_match("/^    private \\\$login_client_secret = '([^']+)';\$/m", $s, $ms)) $ng('Line.php のシークレットの行が見つかりません');
$id = $mi[1];
$secret = $ms[1];
$pairs = [
    [$ms[0], "    private \$login_client_secret = ''; // [カスタムエイド 2026-11-05 追記11] .env の CS_LINE_LOGIN_SECRET から読む(config/csline.php)"],
    ["        \$this->login_redirect_uri = route('api.member.api_login_redirect', ['client' => 'line']);\n",
     "        \$this->login_redirect_uri = route('api.member.api_login_redirect', ['client' => 'line']);\n"
     . "        \$this->login_client_id = (string) config('csline.login_id', \$this->login_client_id); // [カスタムエイド 2026-11-05 追記11]\n"
     . "        \$this->login_client_secret = (string) config('csline.login_secret', ''); // [カスタムエイド 2026-11-05 追記11]\n"],
];
foreach ($pairs as $i => $p) { $n = substr_count($s, $p[0]); if ($n !== 1) $ng('Line.php の直す場所 ' . ($i + 1) . ' が見つかりません(見つかった数 ' . $n . ')'); $s = str_replace($p[0], $p[1], $s); }
if (!is_file($fCtl)) $ng('LineController.php がありません');
$sc = file_get_contents($fCtl);
$oc = "        return responseToJson(200, '', ['url' => (new Line())->getLoginUrl()]);";
$n = substr_count($sc, $oc); if ($n !== 1) $ng('LineController.php の直す場所が見つかりません(見つかった数 ' . $n . ')');
$sc = str_replace($oc, "        return responseToJson(410, '古い版のアプリです。App Store から Card-San App を最新にしてください'); // [カスタムエイド 2026-11-05 追記11] 古いログインの入口(合言葉が固定)は止めた。元:" . trim($oc), $sc);
$cfg = "<?php\n// [カスタムエイド 2026-11-05 追記11] Card-San App の LINE ログイン(チャネル {$id})。シークレットは .env に置く\nreturn [\n    'login_id' => env('CS_LINE_LOGIN_ID', '{$id}'),\n    'login_secret' => env('CS_LINE_LOGIN_SECRET', ''),\n];\n";
$tmp = sys_get_temp_dir() . '/cs_s9f11.php'; $okAll = true;
foreach ([$s, $cfg, $sc] as $code) { file_put_contents($tmp, $code); $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c); if ($c !== 0) $okAll = false; }
@unlink($tmp);
echo ($okAll ? 'OK' : 'NG') . " 直したあとの文法(Line.php・LineController.php・config/csline.php)\n"; if (!$okAll) $ng('文法エラー');
echo "チャネル ID:{$id}\n";
echo '今のシークレット(Line.php に書かれているもの)を LINE で確かめました:' . $test($id, $secret) . "\n";
echo "今の Line.php の md5:" . md5_file($fLine) . "\n";
if ($mode !== 'apply') exit("\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n");

if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
copy($fLine, $backup . 'Line.php');
copy($fCtl, $backup . 'LineController.php');
copy($fEnv, $backup . 'env');
chmod($backup . 'env', 0600);
file_put_contents($fCfg, $cfg);
$st = stat($base . 'config/app.php'); @chown($fCfg, $st['uid']); @chgrp($fCfg, $st['gid']); @chmod($fCfg, 0644);
$add = (substr($env, -1) === "\n" ? '' : "\n") . "# [カスタムエイド 2026-11-05 追記11] Card-San App の LINE ログイン\nCS_LINE_LOGIN_ID={$id}\nCS_LINE_LOGIN_SECRET={$secret}\n";
file_put_contents($fEnv, $add, FILE_APPEND);
file_put_contents($fLine, $s);
file_put_contents($fCtl, $sc);
$c1 = 1; $c2 = 1; $o = []; exec('php -l ' . escapeshellarg($fLine) . ' 2>&1', $o, $c1); $o = []; exec('php -l ' . escapeshellarg($fCtl) . ' 2>&1', $o, $c2);
if ($c1 !== 0 || $c2 !== 0) { copy($backup . 'Line.php', $fLine); copy($backup . 'LineController.php', $fCtl); copy($backup . 'env', $fEnv); unlink($fCfg); exit("文法エラーがあったので元に戻しました。\n"); }
// Laravel から読めるか(画面には出さず、同じかどうかだけ)
$o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan tinker --execute=' . escapeshellarg('echo md5((string) config("csline.login_secret")) === "' . md5($secret) . '" ? "same" : "diff";') . ' 2>&1', $o);
echo 'Laravel から .env のシークレットを読めるか:' . (strpos(implode('', $o), 'same') !== false ? 'OK' : '確認が必要(' . mb_substr(implode(' ', $o), 0, 80) . ')') . "\n";
echo "完了しました。バックアップ: {$backup}\n";
echo "このあと:LINE Developers でシークレットを作り直し、すぐに newsecret を実行してください。\n";
