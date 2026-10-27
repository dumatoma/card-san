<?php
// [カスタムエイド 2026-10-25] プッシュ通知を Apple(APNs)に直接送る仕組みを入れるパッチ。
//   ・会員(web_user)・管理者(web_shop_admin)の表に、端末の番号の欄を足す
//   ・app/Libs/CsApns.php と app/Console/cs_apns_sender.php を置く
//   ・鍵(.p8)を storage/app/cs_push/ に置く(root だけが読める)
//   ・GeTuiApi::pushSingleBatchCid に「Apple にも送る」1行、両方の AuthController::bindClientid に「番号を保存」1行を足す
//   ・root の crontab に送る係(毎分)を足す
//   ・裏の処理(queue)に新しいプログラムを読み直させる
//   アプリが新しくなって番号を送ってくるまでは、Apple には何も送られない(今の動きは変わらない)。
//
// 事前に /root に置くもの:customaid_CsApns.php、customaid_cs_apns_sender.php、AuthKey_ALM46S5TRS.p8
// 使い方:
//   確認だけ:php -d disable_functions= /root/customaid_apns_patch.php
//   反映    :php -d disable_functions= /root/customaid_apns_patch.php apply
$apply = ($argv[1] ?? '') === 'apply';
$base = '/www/wwwroot/api.card-san.jp/';
$php = '/www/server/php/73/bin/php';
chdir($base);
$KEY = 'AuthKey_ALM46S5TRS.p8';
$CRON = '* * * * * ' . $php . ' -d disable_functions= ' . $base . 'app/Console/cs_apns_sender.php >/dev/null 2>&1';
$ok = true;
function say($s) { echo $s, "\n"; }
function ng($s) { global $ok; $ok = false; echo "✕ ", $s, "\n"; }

say($apply ? '=== 反映します ===' : '=== 確認だけ(何も変更しません) ===');

// 1. 置くファイル
$src = ['/root/customaid_CsApns.php' => 'app/Libs/CsApns.php', '/root/customaid_cs_apns_sender.php' => 'app/Console/cs_apns_sender.php'];
foreach ($src as $s => $d) {
    if (!is_file($s)) { ng("$s がありません"); continue; }
    $o = []; exec($php . ' -l ' . escapeshellarg($s) . ' 2>&1', $o, $rc);
    if ($rc !== 0) ng("$s の文法エラー: " . implode(' ', $o)); else say("○ $s(文法 OK)→ $d" . (is_file($base . $d) ? '(上書き)' : '(新規)'));
}
$k = @file_get_contents('/root/' . $KEY);
if (!$k) ng("/root/$KEY がありません");
elseif (strpos($k, 'BEGIN PRIVATE KEY') === false || !openssl_pkey_get_private($k)) ng("/root/$KEY が鍵の形ではありません");
else say("○ /root/$KEY(鍵の形 OK。中身は表示しません)");

// 2. 書き足す場所
$edits = [
    'app/Libs/GeTuiApi.php' => [
        '/(Log::channel\(\'my_getui\'\)->info\(\'normalize failed: \' \. \$e->getMessage\(\)\);\s*\}\s*\}\n)/',
        "\$1\n        // [カスタムエイド 2026-10-25] Apple 用の番号が分かっている iPhone には、Apple(APNs)にも直接送る(app/Libs/CsApns.php)\n        \\App\\Libs\\CsApns::mirror(\$this->type, \$msg_list);\n",
    ],
    'app/Http/Controllers/Api/Shop/AuthController.php' => [
        '/(auth\(\'shop\'\)->user\(\)->update\(\[\'getui_clientid\' => \$request->clientid\]\);\s*\}\n)/',
        "\$1        \\App\\Libs\\CsApns::bind(auth('shop')->user(), \$request); // [カスタムエイド 2026-10-25] Apple 用の番号も保存\n",
    ],
    'app/Http/Controllers/Api/Member/AuthController.php' => [
        '/(auth\(\)->user\(\)->update\(\[\'getui_clientid\' => \$request->clientid\]\);\s*\}\n)/',
        "\$1        \\App\\Libs\\CsApns::bind(auth()->user(), \$request); // [カスタムエイド 2026-10-25] Apple 用の番号も保存\n",
    ],
];
$new = [];
foreach ($edits as $f => $e) {
    $t = @file_get_contents($base . $f);
    if ($t === false) { ng("$f がありません"); continue; }
    if (strpos($t, 'CsApns::') !== false) { say("○ $f はすでに書き足し済み(そのまま)"); continue; }
    $n = preg_match_all($e[0], $t);
    if ($n !== 1) { ng("$f の書き足す場所が $n か所(1か所のはず)"); continue; }
    $new[$f] = preg_replace($e[0], $e[1], $t, 1);
    say("○ $f に1行書き足す(場所 1か所)");
}

// 3. 表の欄
require_once $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$cols = ['cs_push_platform' => 'VARCHAR(10) NULL', 'cs_apns_token' => 'VARCHAR(200) NULL', 'cs_fcm_token' => 'VARCHAR(255) NULL', 'cs_push_at' => 'INT UNSIGNED NULL'];
$alter = [];
foreach (['web_user', 'web_shop_admin'] as $t) {
    foreach ($cols as $c => $def) {
        if (\Illuminate\Support\Facades\Schema::hasColumn($t, $c)) say("○ $t.$c はすでにある");
        else { $alter[] = "ALTER TABLE `$t` ADD COLUMN `$c` $def"; say("○ $t.$c を足す"); }
    }
}

// 4. crontab
$ct = (string) shell_exec('crontab -l 2>/dev/null');
$hasCron = strpos($ct, 'cs_apns_sender.php') !== false;
say($hasCron ? '○ crontab はすでに登録済み' : '○ root の crontab に送る係を足す');

// 5. curl コマンドの確認
$o = []; exec('curl -V 2>&1', $o);
say(stripos(implode(' ', $o), 'nghttp2') !== false ? '○ curl コマンドは HTTP/2 対応' : '✕(注意)curl コマンドが HTTP/2 に対応していないかも');

if (!$ok) { say("\n問題があるので止めます。何も変更していません。"); exit(1); }
if (!$apply) { say("\n確認だけ終わりました。問題がなければ、最後に apply を付けて実行してください。"); exit(0); }

// ===== ここから反映 =====
$bk = '/www/backup_customaid_apns_' . date('Ymd_His') . '/';
mkdir($bk, 0700, true);
foreach (array_merge(array_keys($edits), array_values($src)) as $f) {
    if (is_file($base . $f)) { @mkdir(dirname($bk . $f), 0700, true); copy($base . $f, $bk . $f); }
}
file_put_contents($bk . 'crontab_root.txt', $ct);
say("控え:$bk");

// ファイルを置く
foreach ($src as $s => $d) {
    copy($s, $base . $d);
    chown($base . $d, 'www'); chgrp($base . $d, 'www'); chmod($base . $d, 0644);
}
// 書き足し(文法チェックして、だめなら元に戻す)
foreach ($new as $f => $t) {
    file_put_contents($base . $f, $t);
    $o = []; exec($php . ' -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $rc);
    if ($rc !== 0) {
        foreach (array_keys($new) as $g) if (is_file($bk . $g)) copy($bk . $g, $base . $g);
        foreach ($src as $s => $d) { if (is_file($bk . $d)) copy($bk . $d, $base . $d); else @unlink($base . $d); }
        say("✕ $f の文法エラーのため、すべて元に戻しました: " . implode(' ', $o));
        exit(1);
    }
    say("○ $f 書き足し(文法 OK)");
}
// 置き場所と鍵
$cp = $base . 'storage/app/cs_push/';
@mkdir($cp . 'spool', 0777, true);
chown($cp, 'www'); chgrp($cp, 'www'); chmod($cp, 0755);
chown($cp . 'spool', 'www'); chgrp($cp . 'spool', 'www'); chmod($cp . 'spool', 0777);
copy('/root/' . $KEY, $cp . $KEY);
chown($cp . $KEY, 'root'); chgrp($cp . $KEY, 'root'); chmod($cp . $KEY, 0600);
say("○ 鍵を $cp$KEY に置きました(root だけが読める)");
// 表の欄
foreach ($alter as $sql) { \Illuminate\Support\Facades\DB::statement($sql); say("○ $sql"); }
// crontab
if (!$hasCron) {
    $tmp = tempnam('/tmp', 'ct');
    file_put_contents($tmp, rtrim($ct) . ($ct === '' ? '' : "\n") . "# [カスタムエイド 2026-10-25] Apple(APNs)に通知を送る係\n" . $CRON . "\n");
    exec('crontab ' . escapeshellarg($tmp) . ' 2>&1', $o2, $rc2);
    unlink($tmp);
    say($rc2 === 0 ? '○ crontab に登録しました' : '✕ crontab の登録に失敗: ' . implode(' ', $o2));
}
// 裏の処理に読み直させる
$o3 = []; exec('sudo -u www ' . $php . ' ' . $base . 'artisan queue:restart 2>&1', $o3);
say('○ queue:restart ' . implode(' ', $o3));

say("\n反映しました。このあと php-fpm を読み直してください:/etc/init.d/php-fpm-73 reload");
say('元に戻すときは、控え(' . $bk . ')のファイルを戻し、crontab の cs_apns_sender の行を消してください。');
