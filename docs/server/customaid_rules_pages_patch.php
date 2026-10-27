<?php
// [カスタムエイド 2026-10-26] ストア用の利用規約・プライバシーポリシーのページを、管理WEB「Rules」の文と同じにする
//   今:api.card-san.jp/api/privacypolicy_user などの4ページは、サーバーのファイルに書いた固定の文(2022年の版)
//       アプリの中は データベース(web_config)の文(管理WEB の Rules で直す)→ 2か所で食い違う
//   直し:4ページとも、アプリと同じ設定を読んで表示する。管理WEB の Rules を直せば、アプリもストア用のページも変わる
//       設定が空のときだけ、以前の固定の文を表示する(以前のファイルは customaid_legacy に残す)
// 使い方:
//   確認だけ:php -d disable_functions= /root/customaid_rules_pages_patch.php
//   反映    :php -d disable_functions= /root/customaid_rules_pages_patch.php apply
$apply = ($argv[1] ?? '') === 'apply';
$base = '/www/wwwroot/api.card-san.jp';
$vdir = $base . '/resources/views/api/';
$ldir = $vdir . 'customaid_legacy/';
$mark = 'customaid-rules-20261026';
$pages = [
    // ページ名 => [設定の名前, 管理WEB, ページの題名]
    'termsofuse'         => ['shop_service_regulations', 'Rules?type=1(利用規約・契約者用)',                 '利用規約 | CardSan管理'],
    'termsofuse_user'    => ['service_regulations',      'Rules?type=2(利用規約・エンドユーザー用)',         '利用規約 | Card-San App'],
    'privacypolicy'      => ['shop_privacy_policy',      'Rules?type=3(プライバシーポリシー・契約者用)',     'プライバシーポリシー | CardSan管理'],
    'privacypolicy_user' => ['privacy_policy',           'Rules?type=4(プライバシーポリシー・エンドユーザー用)', 'プライバシーポリシー | Card-San App'],
];
$ok = true;
function say($s) { echo $s, "\n"; }
function ng($s) { global $ok; $ok = false; echo "✕ ", $s, "\n"; }
function plain($s, $n = 40) { $t = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string)$s), ENT_QUOTES, 'UTF-8'))); return mb_substr($t, 0, $n); }
say($apply ? '=== 反映します ===' : '=== 確認だけ(何も変更しません) ===');

// Laravel を読み込む(アプリと同じ設定の読み方を使うため)
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!class_exists('\App\Services\ConfigService')) { ng('ConfigService が見つかりません'); exit(1); }

// アプリが読んでいる /api/config の中身(ConfigController@index の結果)
$api = null;
try {
    $res = app()->call([app(\App\Http\Controllers\Api\ConfigController::class), 'index']);
    $body = is_object($res) && method_exists($res, 'getContent') ? $res->getContent() : json_encode($res);
    $api = json_decode($body, true)['data']['config'] ?? null;
} catch (\Throwable $e) { ng('/api/config の中身を読めません: ' . $e->getMessage()); }

$tpl = <<<'BLADE'
{{-- [カスタムエイド 2026-10-26] __MARK__:管理WEB「__ADMIN__」の文をそのまま表示する(アプリと同じ設定 __KEY__ を読む)。空のときだけ以前の固定の文 --}}
@php
$csText = (string) \App\Services\ConfigService::_init()->get('__KEY__', '');
@endphp
@if (trim($csText) === '')
@include('api.customaid_legacy.__NAME__')
@else
<!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>__TITLE__</title>
<style>
body{margin:0;background:#fff;color:#1d1d1f;font-family:-apple-system,BlinkMacSystemFont,"Hiragino Sans","Noto Sans JP",sans-serif}
main{max-width:760px;margin:0 auto;padding:24px 16px 48px;font-size:15px;line-height:1.8;white-space:pre-wrap;word-break:break-word}
</style>
</head>
<body><main>{{ $csText }}</main></body>
</html>
@endif
BLADE;

$news = [];
foreach ($pages as $name => [$key, $admin, $title]) {
    $f = $vdir . $name . '.blade.php';
    say("\n■ /api/$name ← 管理WEB $admin(設定 $key)");
    $old = @file_get_contents($f);
    if ($old === false) { ng("$f がありません"); continue; }
    if (strpos($old, $mark) !== false) { say('  ○ もう直してあります(作り直します)'); }
    $val = (string) \App\Services\ConfigService::_init()->get($key, '');
    say('  今のページの文 :' . plain(strpos($old, $mark) !== false ? @file_get_contents($ldir . $name . '.blade.php') : $old));
    say('  管理WEB の文   :' . ($val === '' ? '(空 → 以前の固定の文を表示)' : plain($val)) . '(' . mb_strlen($val) . '文字)');
    if (preg_match('/制定日[^0-9０-９]*([0-9０-９]{4})/u', $old, $m1)) say('  今のページの制定日の年:' . $m1[1]);
    if (preg_match('/制定日[^0-9０-９]*([0-9０-９]{4})/u', $val, $m2)) say('  管理WEB の制定日の年  :' . $m2[1]);
    if (is_array($api)) {
        if (($api[$key] ?? null) === $val) say('  ○ アプリが読んでいる文(/api/config)と同じ'); else ng("  /api/config の $key と、読み出した文が違います");
    }
    $news[$name] = str_replace(['__MARK__', '__ADMIN__', '__KEY__', '__NAME__', '__TITLE__'], [$mark, $admin, $key, $name, $title], $tpl);
    // 表示の試し(一時ファイルで作って、管理WEB の文が出るか)
    $tmp = sys_get_temp_dir() . "/customaid_rules_test_$name.blade.php";
    file_put_contents($tmp, $news[$name]);
    if (!is_file($ldir . $name . '.blade.php')) { $tmpL = true; @mkdir($ldir, 0755, true); copy($f, $ldir . $name . '.blade.php'); } else { $tmpL = false; }
    try {
        $html = view()->file($tmp)->render();
        $want = $val === '' ? plain($old, 20) : plain($val, 20);
        if ($want !== '' && strpos(plain($html, 100000), $want) === false) ng('  試しに表示した中に、期待する文が見つかりません');
        else say('  ○ 試しに表示:OK(' . strlen($html) . ' バイト)');
    } catch (\Throwable $e) { ng('  試しに表示できません: ' . $e->getMessage()); }
    @unlink($tmp);
    if ($tmpL && !$apply) { @unlink($ldir . $name . '.blade.php'); }
}
if (!$apply) { @rmdir($ldir); }
// 試しに表示したときにできた一時ファイルの持ち主を、他のファイルと同じにする
$cv = $base . '/storage/framework/views';
$st = @stat($cv);
if ($st) foreach (glob($cv . '/*.php') as $c) { if (fileowner($c) === 0) { @chown($c, $st['uid']); @chgrp($c, $st['gid']); } }

if (!$ok) { say("\n問題があるので止めます。何も変更していません。"); exit(1); }
if (!$apply) { say("\n確認だけ終わりました。上の「今のページの文」と「管理WEB の文」の組み合わせが正しければ、最後に apply を付けて実行してください。"); exit(0); }

// ===== 反映 =====
$bk = '/www/backup_customaid_rules_pages_' . date('Ymd_His') . '/';
mkdir($bk, 0700, true);
@mkdir($ldir, 0755, true);
$vst = stat($vdir);
@chown($ldir, $vst['uid']); @chgrp($ldir, $vst['gid']);
foreach ($news as $name => $n) {
    $f = $vdir . $name . '.blade.php';
    copy($f, $bk . $name . '.blade.php');
    $st = stat($f);
    if (!is_file($ldir . $name . '.blade.php')) copy($f, $ldir . $name . '.blade.php');
    @chown($ldir . $name . '.blade.php', $st['uid']); @chgrp($ldir . $name . '.blade.php', $st['gid']);
    file_put_contents($f, $n);
    @chown($f, $st['uid']); @chgrp($f, $st['gid']); @chmod($f, $st['mode'] & 0777);
    say("○ $name を直しました");
}
say("控え:$bk");
say("\n確かめ方:次の4つを開いて、管理WEB の Rules と同じ文が出ているか");
foreach ($pages as $name => $p) say("  https://api.card-san.jp/api/$name");
$back = [];
foreach ($pages as $name => $p) $back[] = "\\cp {$bk}{$name}.blade.php {$vdir}{$name}.blade.php";
say("元に戻すとき:" . implode(' ; ', $back));
