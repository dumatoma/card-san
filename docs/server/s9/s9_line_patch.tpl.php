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
@@CsLine.php@@
CSSRC1;
const SRC_CH = <<<'CSSRC2'
@@CsReserveCh.php@@
CSSRC2;
const SRC_SHOPCTL = <<<'CSSRC3'
@@ShopCsLineController.php@@
CSSRC3;
const SRC_MEMCTL = <<<'CSSRC4'
@@MemberCsLineController.php@@
CSSRC4;
const SRC_PUBCTL = <<<'CSSRC5'
@@CsLinePublicController.php@@
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
$o = []; exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:list --columns=method,uri,middleware 2>&1 | grep cs-line\\\\\|cs/line', $o);
echo "入口の確かめ:\n" . (implode("\n", $o) ?: '見つかりません(確認が必要)') . "\n";
echo "店舗11 の状態:" . json_encode(App\Libs\CsReserveCh::get(11), JSON_UNESCAPED_UNICODE) . "\n";
echo "コールバック URL:" . App\Libs\CsLine::callbackUrl() . "\n";
echo "店舗11 の Webhook URL:" . App\Libs\CsLine::webhookUrl(11) . "\n";
echo "\n完了しました。バックアップ: {$backup}\n元に戻すとき: php -d disable_functions= /root/s9_line_patch.php undo\n";
