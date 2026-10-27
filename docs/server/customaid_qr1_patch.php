<?php
// [カスタムエイド 2026-10-26] 店舗QR統一 段階2:店舗ページ・お店の情報(ログインなし)・iPhone でアプリを直接開く設定。あわせて公開フォルダの鍵を守る。
//   1. app/Http/Controllers/CsShopPageController.php を置く
//   2. routes/web.php の最後に2行:GET /s/{id}(店舗ページ)、GET /api/cs/shop/{id}(お店の情報 JSON)
//   3. iPhone 用の設定ファイル /www/wwwroot/cs-applinks/apple-app-site-association(管理WEB を更新しても消えない場所)
//   4. nginx の追加設定(本体の設定ファイルは書き換えない):
//      ・card-san.jp:/s/ を api.card-san.jp(Laravel)に回す/.well-known/apple-app-site-association を返す
//      ・api.card-san.jp:.p8 .p12 .pem .key のファイルを外から取れないようにする(公開フォルダにある AuthKey_9JPXY898N6.p8 を守る)
//   5. 鍵 AuthKey_9JPXY898N6.p8 を使っている場所を表示する(移動は使っている場所を確かめてから。今回は外から取れなくするだけ)
//   nginx の設定は、書いたあと nginx -t で確かめ、だめなら消して元に戻す。
// 事前に /root に置くもの:customaid_CsShopPageController.php
// 使い方:
//   確認だけ:php -d disable_functions= /root/customaid_qr1_patch.php
//   反映    :php -d disable_functions= /root/customaid_qr1_patch.php apply
$apply = ($argv[1] ?? '') === 'apply';
$base = '/www/wwwroot/api.card-san.jp/';
$php = '/www/server/php/73/bin/php';
$nginx = '/www/server/nginx/sbin/nginx';
$ext = '/www/server/panel/vhost/nginx/extension/';
$ok = true;
function say($s) { echo $s, "\n"; }
function ng($s) { global $ok; $ok = false; echo "✕ ", $s, "\n"; }
function sh($c) { $o = []; exec($c . ' 2>&1', $o, $rc); return [$rc, implode("\n", $o)]; }
say($apply ? '=== 反映します ===' : '=== 確認だけ(何も変更しません) ===');

// 1. コントローラー
$src = '/root/customaid_CsShopPageController.php';
$dst = $base . 'app/Http/Controllers/CsShopPageController.php';
if (!is_file($src)) ng("$src がありません");
else { list($rc, $o) = sh($php . ' -l ' . escapeshellarg($src)); $rc ? ng("文法エラー: $o") : say("○ $src(文法 OK)→ " . (is_file($dst) ? '上書き' : '新規')); }

// 2. ルート
$web = $base . 'routes/web.php';
$wt = @file_get_contents($web);
$routes = "\n// [カスタムエイド 2026-10-26] 店舗QR統一:店舗ページ(card-san.jp/s/{id} から nginx で回ってくる)と、ログインなしで見られるお店の情報\n"
    . "Route::get('/s/{id}', '\\App\\Http\\Controllers\\CsShopPageController@show')->where('id', '[0-9]+');\n"
    . "Route::get('/api/cs/shop/{id}', '\\App\\Http\\Controllers\\CsShopPageController@info')->where('id', '[0-9]+');\n";
if ($wt === false) ng('routes/web.php がありません');
elseif (strpos($wt, 'CsShopPageController') !== false) say('○ routes/web.php はすでに追加済み');
else say('○ routes/web.php の最後に2行足す(今 ' . substr_count($wt, "\n") . ' 行)');
if ($wt !== false && preg_match('#[\'"]/s/#', $wt)) ng('routes/web.php に /s/ のルートがすでにあります(重なるので止めます)');

// 3. iPhone 用の設定ファイル
$aasaDir = '/www/wwwroot/cs-applinks/';
$aasa = json_encode(['applinks' => ['apps' => [], 'details' => [[
    'appID' => 'KB35CW7SD9.com.cardsan.customer',
    'paths' => ['/s/*'],
    'appIDs' => ['KB35CW7SD9.com.cardsan.customer'],
    'components' => [['/' => '/s/*', 'comment' => 'お店のQR']],
]]]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
say('○ iPhone 用の設定ファイルを ' . $aasaDir . 'apple-app-site-association に置く(KB35CW7SD9.com.cardsan.customer、/s/*)');

// 4. nginx の追加設定
$confS = $ext . 'card-san.jp/customaid_s.conf';
$confKey = $ext . 'api.card-san.jp/customaid_protect_keys.conf';
$cS = "# [カスタムエイド 2026-10-26] 店舗QR統一:/s/{id} を api.card-san.jp(Laravel)の店舗ページに回す。iPhone でアプリを直接開く設定ファイルを返す\n"
    . "location ^~ /s/ {\n    proxy_pass https://127.0.0.1;\n    proxy_ssl_server_name on;\n    proxy_ssl_name api.card-san.jp;\n    proxy_ssl_verify off;\n"
    . "    proxy_set_header Host api.card-san.jp;\n    proxy_set_header X-Real-IP \$remote_addr;\n    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;\n    proxy_set_header X-Forwarded-Proto https;\n}\n"
    . "location = /.well-known/apple-app-site-association {\n    default_type application/json;\n    alias {$aasaDir}apple-app-site-association;\n}\n"
    . "location = /apple-app-site-association {\n    default_type application/json;\n    alias {$aasaDir}apple-app-site-association;\n}\n";
$cK = "# [カスタムエイド 2026-10-26] 鍵・証明書のファイルを外から取れないようにする(public に置かれていた AuthKey_9JPXY898N6.p8 など)\n"
    . "location ~* \\.(p8|p12|pem|key)\$ {\n    return 404;\n}\n";
foreach ([$ext . 'card-san.jp', $ext . 'api.card-san.jp'] as $d) say((is_dir($d) ? '○ ' : '○(新しく作る) ') . $d);
say(is_file($confS) ? "○ $confS はすでにある(上書き)" : "○ $confS を作る");
say(is_file($confKey) ? "○ $confKey はすでにある(上書き)" : "○ $confKey を作る");
list($rc, $o) = sh($nginx . ' -t');
$rc ? ng("今の nginx の設定にエラーがあります(先に直す必要): $o") : say('○ 今の nginx の設定は正常(nginx -t)');

// 5. 鍵を使っている場所
say("\n-- AuthKey_9JPXY898N6 を使っている場所(app・config・.env・routes。値は伏せる)");
list($rc, $o) = sh('cd ' . $base . ' && grep -rn "9JPXY898N6\|AuthKey_" app config routes .env 2>/dev/null | sed -E "s/[A-Za-z0-9+\/]{40,}/***/g" | head -20');
say($o !== '' ? $o : '(見つかりません)');
list($rc, $o) = sh('ls -la ' . $base . 'public/AuthKey_9JPXY898N6.p8');
say($o);

if (!$ok) { say("\n問題があるので止めます。何も変更していません。"); exit(1); }
if (!$apply) { say("\n確認だけ終わりました。問題がなければ、最後に apply を付けて実行してください。"); exit(0); }

// ===== 反映 =====
$bk = '/www/backup_customaid_qr1_' . date('Ymd_His') . '/';
mkdir($bk, 0700, true);
copy($web, $bk . 'web.php');
if (is_file($dst)) copy($dst, $bk . 'CsShopPageController.php');
say("控え:$bk");

copy($src, $dst); chown($dst, 'www'); chgrp($dst, 'www'); chmod($dst, 0644);
if (strpos($wt, 'CsShopPageController') === false) file_put_contents($web, rtrim($wt) . "\n" . $routes);
list($rc, $o) = sh($php . ' -l ' . escapeshellarg($web));
if ($rc) { copy($bk . 'web.php', $web); @unlink($dst); say("✕ routes/web.php の文法エラーのため元に戻しました: $o"); exit(1); }
say('○ コントローラーとルートを追加(文法 OK)');
list($rc, $o) = sh('cd ' . $base . ' && sudo -u www ' . $php . ' artisan route:list 2>/dev/null | grep -c CsShopPage');
say('○ ルートの登録数:' . trim($o) . '(2 のはず)');

@mkdir($aasaDir, 0755, true);
file_put_contents($aasaDir . 'apple-app-site-association', $aasa);
chmod($aasaDir . 'apple-app-site-association', 0644);
say('○ iPhone 用の設定ファイルを置きました');

@mkdir($ext . 'card-san.jp', 0755, true);
@mkdir($ext . 'api.card-san.jp', 0755, true);
foreach ([$confS, $confKey] as $f) if (is_file($f)) copy($f, $bk . basename($f));
file_put_contents($confS, $cS);
file_put_contents($confKey, $cK);
list($rc, $o) = sh($nginx . ' -t');
if ($rc) {
    foreach ([$confS, $confKey] as $f) { if (is_file($bk . basename($f))) copy($bk . basename($f), $f); else @unlink($f); }
    say("✕ nginx の設定のエラーのため、追加の設定を消して元に戻しました: $o");
    say('(コントローラーとルートは入ったままです。/s/ は api.card-san.jp/s/{id} でだけ見られます)');
    exit(1);
}
list($rc, $o) = sh($nginx . ' -s reload');
say($rc ? "✕ nginx の読み直しに失敗: $o" : '○ nginx の設定を追加して読み直しました');

say("\n反映しました。このあと php-fpm を読み直してください:/etc/init.d/php-fpm-73 reload");
say('確かめ方:iPhone の Safari で https://card-san.jp/s/11 を開く/https://card-san.jp/.well-known/apple-app-site-association/https://api.card-san.jp/AuthKey_9JPXY898N6.p8(404 になれば鍵は守られている)');
say('元に戻すとき:控えの web.php を戻し、' . $confS . ' と ' . $confKey . ' を消して nginx -s reload');
