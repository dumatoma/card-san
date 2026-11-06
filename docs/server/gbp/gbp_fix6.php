<?php
// [カスタムエイド 2026-11-06] GBP 連携 その6(サーバー):管理App の TOP 画面に出す「Googleアクセス解析(最近30日の閲覧数)」
//   GET /api/shop/google_business/insights_summary
//     → { show: 1, total: 最近30日の閲覧数, prev: その前の30日, until: '11/3' }
//     出さないとき(連携していない・権限やプランが足りない・Google から取れない)は { show: 0 }。TOP 画面なのでエラーは出さない
//   検索キーワードは取らない(軽くするため)。数字は gbp_fix5 と同じく 6 時間ためておいたものを使う
//
//   php -d disable_functions= /root/gbp_fix6.php          … 確かめるだけ(何も変えない)
//   php -d disable_functions= /root/gbp_fix6.php apply    … バックアップを取ってから変える
//   php -d disable_functions= /root/gbp_fix6.php undo     … 元に戻す

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_gbpfix6_20261106/';
$mode = $argv[1] ?? 'check';
$MARK = 'カスタムエイド 2026-11-06 GBP6';
$F = [
    'svc'   => 'app/Services/GbpService.php',
    'ctl'   => 'app/Http/Controllers/Api/Shop/GoogleBusinessController.php',
    'route' => 'routes/api.php',
];
if ($mode === 'undo') {
    $n = 0;
    foreach ($F as $f) { $b = $backup . str_replace('/', '__', $f); if (is_file($b)) { copy($b, $base . $f); $n++; } }
    if (!$n) exit("バックアップがありません。何もしていません。\n");
    @exec('/etc/init.d/php-fpm-73 reload 2>&1');
    exit("元に戻しました({$n} ファイル)。\n");
}
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
$src = [];
foreach ($F as $k => $f) { $src[$k] = file_get_contents($base . $f); if (strpos($src[$k], $MARK) !== false) $ng("すでに直してあります({$f})"); }
if (strpos($src['svc'], 'カスタムエイド 2026-11-06 GBP5') === false) $ng('先に gbp_fix5.php を apply してください');

$E = [];
// 検索キーワードを取らない呼び方(kw=0)
$E['svc'][] = ["        \$kwMonths = (int) \$kwMonths === 3 ? 3 : 1;\n",
               "        \$kwMonths = in_array((int) \$kwMonths, [0, 3], true) ? (int) \$kwMonths : 1; // [{$MARK}] 0 = 検索キーワードは取らない(TOP 画面用)\n"];
$E['svc'][] = ["        \$kw = \$this->perfKeywordList(\$shop, \$tk['token'], \$loc, \$kwMonths, \$fresh);\n",
               "        \$kw = \$kwMonths ? \$this->perfKeywordList(\$shop, \$tk['token'], \$loc, \$kwMonths, \$fresh) : ['ok' => 1, 'list' => [], 'label' => '']; // [{$MARK}]\n"];

$E['ctl'][] = ["    // GET /api/shop/google_business/overview\n", <<<'N'
    // [カスタムエイド 2026-11-06 GBP6] GET /api/shop/google_business/insights_summary — TOP 画面の「Googleアクセス解析(最近30日の閲覧数)」
    public function insightsSummary()
    {
        if ($this->guard(16, 'gbp_review')) return responseToJson(200, '', ['show' => 0]); // 権限・プランが足りないときは出さない
        $shop = auth('shop')->user()->shop;
        if (!GbpService::connected($shop)) return responseToJson(200, '', ['show' => 0]);
        $r = (new GbpService())->insights($shop, 30, 0);
        if (!$r['ok']) return responseToJson(200, '', ['show' => 0]);
        return responseToJson(200, '', ['show' => 1, 'total' => $r['data']['views']['total'], 'prev' => $r['data']['views']['prev'], 'until' => $r['data']['until']]);
    }

    // GET /api/shop/google_business/overview

N];

$E['route'][] = ["                Route::get('/insights',                    'GoogleBusinessController@insights'); // [カスタムエイド 2026-11-06 GBP5] Google からのアクセス・検索キーワード\n",
                 "                Route::get('/insights',                    'GoogleBusinessController@insights'); // [カスタムエイド 2026-11-06 GBP5] Google からのアクセス・検索キーワード\n"
               . "                Route::get('/insights_summary',            'GoogleBusinessController@insightsSummary'); // [{$MARK}] TOP 画面の閲覧数\n"];

$out = $src;
foreach ($E as $k => $list) foreach ($list as $i => $p) {
    $n = substr_count($out[$k], $p[0]);
    if ($n !== 1) $ng($F[$k] . ' の直す場所 ' . ($i + 1) . ' が見つかりません(見つかった数 ' . $n . ')');
    $out[$k] = str_replace($p[0], $p[1], $out[$k]);
}
$allOk = true;
foreach ($out as $k => $s) {
    $tmp = sys_get_temp_dir() . '/cs_gbpfix6_' . $k . '.php';
    file_put_contents($tmp, $s);
    $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c); @unlink($tmp);
    echo ($c === 0 ? 'OK ' : 'NG ') . "直したあとの文法 {$F[$k]}\n";
    if ($c !== 0) { $allOk = false; echo '   ' . implode(' ', $o) . "\n"; }
}
if (!$allOk) $ng('文法エラー');
echo "OK 直す場所はすべて見つかりました(" . array_sum(array_map('count', $E)) . " か所)\n";
if ($mode !== 'apply') exit("\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n");

if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($F as $f) copy($base . $f, $backup . str_replace('/', '__', $f));
foreach ($out as $k => $s) file_put_contents($base . $F[$k], $s);
foreach ($F as $f) {
    $o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c);
    if ($c !== 0) { foreach ($F as $g) copy($backup . str_replace('/', '__', $g), $base . $g); exit("文法エラーがあったので元に戻しました:{$f}\n"); }
}
@exec('/etc/init.d/php-fpm-73 reload 2>&1');
$o = [];
exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:list 2>&1 | grep -c google_business/insights_summary', $o);
echo "insights_summary の道:" . (trim(implode('', $o)) === '1' ? 'OK' : '確認が必要') . "\n";
echo "完了しました。バックアップ: {$backup}\n";
