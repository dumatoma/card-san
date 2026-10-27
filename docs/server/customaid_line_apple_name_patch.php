<?php
// [カスタムエイド 2026-10-26] ① LINE ログインを Safari で行い、終わったらアプリに戻す ② Apple のお名前をアカウントに入れる
//   ① AuthController@apiLoginRedirect:アプリが Safari で始めた LINE ログイン(state が csapp で始まる)のときだけ、
//      cardsan://line-login?code=… でアプリに戻すページを返す。それ以外(今までのアプリ内ブラウザ)は今までどおり
//   ② AppleController@getUserInfo:アプリから name(Apple が最初の1回だけ渡すお名前)が届き、
//      アカウントの名前が自動の「user123456」のままなら、そのお名前に変える
// 使い方:確認だけ php -d disable_functions= /root/customaid_line_apple_name_patch.php / 反映 … apply
$apply = ($argv[1] ?? '') === 'apply';
$dir = '/www/wwwroot/api.card-san.jp/app/Http/Controllers/Api/Member/';
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$jobs = [
    'AuthController.php' => [
        "        return view('api.member.auth.api_login_redirect', ['code' => \$request->code, 'client' => \$client]);",
        "        // [カスタムエイド 2026-10-26] アプリが Safari で始めた LINE ログインは、終わったらアプリ(cardsan://line-login)に戻す\n"
        . "        if (\$client === 'line' && strpos((string)\$request->state, 'csapp') === 0) {\n"
        . "            \$csTo = 'cardsan://line-login?' . http_build_query(['code' => (string)\$request->code, 'state' => (string)\$request->state, 'error' => (string)\$request->error]);\n"
        . "            \$csH = htmlspecialchars(\$csTo, ENT_QUOTES);\n"
        . "            return response('<!doctype html><html lang=\"ja\"><head><meta charset=\"utf-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><title>Card-San</title>'\n"
        . "                . '<style>body{margin:0;font-family:-apple-system,\"Hiragino Sans\",sans-serif;background:#f5f5f7;color:#1d1d1f;text-align:center;padding:72px 24px}'\n"
        . "                . 'a{display:block;margin:28px auto 0;max-width:320px;height:54px;line-height:54px;border-radius:27px;background:#06C755;color:#fff;font-weight:700;text-decoration:none}</style></head>'\n"
        . "                . '<body><div style=\"font-size:18px;font-weight:700\">Card-San アプリに戻ります</div>'\n"
        . "                . '<div style=\"font-size:14px;color:#6e6e73;margin-top:10px;line-height:1.7\">「開く」と出たら押してください。<br>戻らないときは、下のボタンを押してください。</div>'\n"
        . "                . '<a href=\"' . \$csH . '\">Card-San アプリに戻る</a>'\n"
        . "                . '<script>setTimeout(function(){location.href=' . json_encode(\$csTo) . '},300)</script></body></html>')\n"
        . "                ->header('Content-Type', 'text/html; charset=utf-8')->header('Cache-Control', 'no-store');\n"
        . "        }\n"
        . "        return view('api.member.auth.api_login_redirect', ['code' => \$request->code, 'client' => \$client]);",
    ],
    'AppleController.php' => [
        "        \$token = md5(time() . rand(1000, 9999));\n        Redis::set('member-' . \$user->id, \$token);",
        "        // [カスタムエイド 2026-10-26] Apple が最初の1回だけ渡すお名前が届き、名前が自動の「user123456」のままなら、そのお名前にする\n"
        . "        \$csName = trim((string)\$request->name);\n"
        . "        if (\$csName !== '' && preg_match('/^user\\d{6}\$/', (string)\$user->name)) {\n"
        . "            \$user->update(['name' => mb_substr(\$csName, 0, 50)]);\n"
        . "        }\n"
        . "        \$token = md5(time() . rand(1000, 9999));\n        Redis::set('member-' . \$user->id, \$token);",
    ],
];
$out = []; $ok = true;
foreach ($jobs as $file => [$a, $b]) {
    $f = $dir . $file;
    $t = @file_get_contents($f);
    if ($t === false) { echo "✕ $f がありません\n"; $ok = false; continue; }
    if (strpos($t, '[カスタムエイド 2026-10-26] ' . ($file === 'AuthController.php' ? 'アプリが Safari' : 'Apple が最初')) !== false) { echo "○ $file:もう直してあります\n"; continue; }
    $c = substr_count($t, $a);
    if ($c !== 1) { echo "✕ $file:直す場所が {$c} か所(1か所のはず)\n"; $ok = false; continue; }
    $n = str_replace($a, $b, $t);
    $tmp = '/tmp/customaid_chk_' . $file;
    file_put_contents($tmp, $n);
    $o = []; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $rc); @unlink($tmp);
    if ($rc) { echo "✕ $file:直したあとの文に書き間違い " . implode(' ', $o) . "\n"; $ok = false; continue; }
    echo "○ $file:直す場所 1か所・書き方 問題なし\n";
    $out[$f] = $n;
}
if (!$ok) { echo "\n問題があるので止めます。何も変更していません。\n"; exit(1); }
if (!$apply) { echo "\n確認だけ終わりました。問題がなければ、最後に apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_line_apple_' . date('Ymd_His') . '/';
mkdir($bk, 0700, true);
$back = [];
foreach ($out as $f => $n) {
    copy($f, $bk . basename($f));
    $s = stat($f);
    file_put_contents($f, $n);
    @chown($f, $s['uid']); @chgrp($f, $s['gid']); @chmod($f, $s['mode'] & 0777);
    echo "○ " . basename($f) . " を直しました\n";
    $back[] = "\\cp {$bk}" . basename($f) . " $f";
}
echo "控え:$bk\n元に戻すとき:" . implode(' ; ', $back) . "\n";
