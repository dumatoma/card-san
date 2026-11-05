<?php
// [カスタムエイド 2026-11-04] 段階9 追記6:LINE の中で予約が終わったら、予約WEB の元の画面(Safari など)が予約完了の画面に進めるように
//   LINE の中のページが「終わった」と預け(POST /api/member/cs-line/done)、元の画面がそれを聞く(GET /api/member/cs-line/done?d=)
//   php -d disable_functions= /root/s9_line_fix6.php        … 確かめるだけ
//   php -d disable_functions= /root/s9_line_fix6.php apply  … バックアップを取ってから変更する
//   php -d disable_functions= /root/s9_line_fix6.php undo   … 元に戻す
$edits = [
    ['app/Libs/CsLine.php', '    // ---------- Webhook ----------', '    // [カスタムエイド 2026-11-04 追記6] LINE の中で予約が終わったことを預ける(予約WEB の元の画面が、戻ったときに予約完了の画面に進むため。60分)
    public static function draftDone($d, array $order)
    {
        $x = self::draftGet($d);
        if (!$x || $x[\'mode\'] !== \'order\') return false;
        $keep = [];
        foreach ([\'name\', \'order_no\', \'start_time\', \'amount\', \'remark\', \'appoint_name\', \'menu_name\', \'sid\', \'phone\', \'appoint_amount\'] as $k) $keep[$k] = isset($order[$k]) && is_scalar($order[$k]) ? (string) $order[$k] : \'\';
        if ($keep[\'order_no\'] === \'\') return false;
        Cache::put(\'cs_line_dn_\' . $d, [\'order\' => $keep, \'bot_name\' => $x[\'bot_name\']], 3600);
        return true;
    }

    public static function draftStatus($d)
    {
        $x = preg_match(\'/^[0-9a-f]{32}$/\', (string) $d) ? Cache::get(\'cs_line_dn_\' . $d) : null;
        return is_array($x) ? $x : null;
    }

    // ---------- Webhook ----------'],
    ['app/Http/Controllers/Api/Member/CsLineController.php', '    public function liff(Request $request)
    {
        $r = CsLine::liff((string) $request->d, (string) $request->id_token);
        if (!$r[\'ok\']) return responseToJson(400, $r[\'err\'] === \'expired\' ? \'時間が切れました。予約WEB から、もう一度やり直してください\' : \'LINE で本人の確認ができませんでした。もう一度開き直してください\');
        return responseToJson(200, \'\', $r);
    }
}', '    public function liff(Request $request)
    {
        $r = CsLine::liff((string) $request->d, (string) $request->id_token);
        if (!$r[\'ok\']) return responseToJson(400, $r[\'err\'] === \'expired\' ? \'時間が切れました。予約WEB から、もう一度やり直してください\' : \'LINE で本人の確認ができませんでした。もう一度開き直してください\');
        return responseToJson(200, \'\', $r);
    }

    // [カスタムエイド 2026-11-04 追記6] POST /api/member/cs-line/done {d, order}:LINE の中で予約が終わった / GET /api/member/cs-line/done?d=:予約WEB の元の画面が聞く
    public function done(Request $request)
    {
        $o = $request->input(\'order\');
        if (!is_array($o) || !CsLine::draftDone((string) $request->d, $o)) return responseToJson(400, \'\');
        return responseToJson(200, \'\');
    }

    public function doneGet(Request $request)
    {
        $x = CsLine::draftStatus((string) $request->d);
        return responseToJson(200, \'\', $x ? [\'done\' => 1] + $x : [\'done\' => 0]);
    }
}'],
    ['routes/api.php', '    Route::post(\'/member/cs-line/liff\', \'Member\\CsLineController@liff\');
', '    Route::post(\'/member/cs-line/liff\', \'Member\\CsLineController@liff\');
    Route::post(\'/member/cs-line/done\', \'Member\\CsLineController@done\'); // [カスタムエイド 2026-11-04 追記6]
    Route::get(\'/member/cs-line/done\', \'Member\\CsLineController@doneGet\');
']
];
$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_s9fix6_20261104/';
$mode = $argv[1] ?? 'check';
$bk = function ($f) use ($backup) { return $backup . str_replace('/', '__', $f); };
$artisan = function ($cmd) use ($base) { $o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan ' . $cmd . ' 2>&1', $o); return implode("\n", $o); };
$routes = function () use ($base, $artisan) { if (glob($base . 'bootstrap/cache/routes*.php')) echo $artisan('route:cache') . "\n"; };
if ($mode === 'undo') {
    if (!is_dir($backup)) exit("バックアップがありません。何もしていません。\n");
    foreach ($edits as $e) if (is_file($bk($e[0]))) { copy($bk($e[0]), $base . $e[0]); echo "元に戻しました: {$e[0]}\n"; }
    $routes(); exit("元に戻しました。\n");
}
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
$out = [];
foreach ($edits as $i => $e) {
    $s = isset($out[$e[0]]) ? $out[$e[0]] : file_get_contents($base . $e[0]);
    if (strpos($s, '追記6') !== false && !isset($out[$e[0]])) $ng("{$e[0]} はすでに修正済みです");
    if (substr_count($s, $e[1]) !== 1) $ng("{$e[0]} の直す場所が見つかりません");
    $out[$e[0]] = str_replace($e[1], $e[2], $s);
    echo "OK {$e[0]}\n";
}
foreach ($out as $f => $s) { $tmp = sys_get_temp_dir() . '/cs_s9f6.php'; file_put_contents($tmp, $s); $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c); @unlink($tmp); echo ($c === 0 ? 'OK' : 'NG') . " 直したあとの文法 {$f}\n"; if ($c !== 0) $ng(implode(' ', $o)); }
if ($mode !== 'apply') exit("\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n");
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($out as $f => $s) copy($base . $f, $bk($f));
foreach ($out as $f => $s) file_put_contents($base . $f, $s);
$ok = true;
foreach ($out as $f => $s) { $o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c); if ($c !== 0) $ok = false; }
if (!$ok) { foreach ($out as $f => $s) copy($bk($f), $base . $f); exit("文法エラーがあったので元に戻しました。\n"); }
$routes();
$o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:list --columns=method,uri 2>&1 | grep -E "cs-line/done"', $o);
echo "入口の確かめ:\n" . implode("\n", $o) . "\n完了しました。バックアップ: {$backup}\n";
