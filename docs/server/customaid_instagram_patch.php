<?php
// [カスタムエイド 2026-10-22] お知らせ(Instagram 連携)
//   1. 管理App 用に、連携している Instagram の投稿を返す入口 GET api/shop/instagram/media(中身は Card-San App 用の Member\InstagramController@media と同じ)
//   2. 連携が終わったときの画面(resources/views/api/shop/instagram/auth_redirect.blade.php)を、ほかの画面と同じ見た目に。少し待つと自動で設定の画面に戻る
// 使い方:
//   php -d disable_functions= /root/customaid_instagram_patch.php        … 確かめるだけ(何も変更しない)
//   php -d disable_functions= /root/customaid_instagram_patch.php apply  … 実際に変更する(先にバックアップを取る)

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_instagram_20261022/';
$tag = '[カスタムエイド 2026-10-22]';
$apply = (($argv[1] ?? '') === 'apply');
$ctlPath = 'app/Http/Controllers/Api/Shop/CsInstagramMediaController.php';
$bladePath = 'resources/views/api/shop/instagram/auth_redirect.blade.php';

if (file_exists($base . $ctlPath)) exit("$ctlPath がすでにあります。何も変更していません。\n");
if (!file_exists($base . 'app/Http/Controllers/Api/Member/InstagramController.php')) exit("Member/InstagramController.php がありません。何も変更していません。\n");

$ctl = <<<'PHP'
<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * [カスタムエイド 2026-10-22] 管理App のお知らせ一覧(Instagram 連携中)に、連携している Instagram の投稿を出す
 * 中身は Card-San App 用(Member\InstagramController@media)と同じ。お店はログインしているお店に決める
 */
class CsInstagramMediaController extends Controller
{
    public function media(Request $request)
    {
        $request->merge(['sid' => auth('shop')->user()->sid]);
        return app(\App\Http\Controllers\Api\Member\InstagramController::class)->media($request);
    }
}
PHP;

// --- 1. 入口 ---
$f = 'routes/api.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, 'CsInstagramMediaController') !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
$re = '/([ \t]*)Route::post\(\'\/create_access_token\', \'InstagramController@createAccessToken\'\);\n/u';
if (preg_match_all($re, $s) !== 1) exit("$f の直す場所が見つかりません(または2つ以上)。何も変更していません。\n");
$routes = preg_replace_callback($re, function ($m) use ($tag) {
    return $m[0] . "\n" . $m[1] . "// $tag 管理App のお知らせ一覧に、連携している Instagram の投稿を出す\n"
        . $m[1] . "Route::get('/media', 'CsInstagramMediaController@media');\n";
}, $s, 1);
echo "① $f:管理App 用の入口 GET api/shop/instagram/media を足す\n";

// --- 2. 連携が終わったときの画面 ---
$old = file_get_contents($base . $bladePath);
if ($old === false) exit("読み込みに失敗しました: $bladePath\n何も変更していません。\n");
if (strpos($old, '{{$code}}') === false || strpos($old, '/pages/notice/setting/setting?code=') === false) exit("$bladePath の中身が想定と違います。何も変更していません。\n");
$blade = <<<'HTML'
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
<title>Instagram 連携</title>
<!-- [カスタムエイド 2026-10-22] 連携が終わったときの画面を、アプリのほかの画面と同じ見た目に。少し待つと自動で「お知らせ設定」に戻る -->
<style>
  @font-face { font-family: CsSans; src: url('/static/font/NotoSansJP-Regular.otf'); }
  @font-face { font-family: CsSansB; src: url('/static/font/NotoSansJP-Bold.otf'); }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  html, body { height: 100%; }
  body { font-family: CsSans, -apple-system, "Hiragino Sans", sans-serif; background: #F2F2F7; color: #1D1D1F; display: flex; align-items: center; justify-content: center; padding: 24px 16px; }
  .card { width: 100%; max-width: 420px; background: #FFFFFF; border-radius: 20px; padding: 36px 24px 28px; text-align: center; }
  .app { font-size: 13px; color: #8A8A8E; letter-spacing: .5px; }
  .ok { width: 64px; height: 64px; border-radius: 50%; background: #E8F0FE; margin: 22px auto 16px; display: flex; align-items: center; justify-content: center; }
  .ok i { display: block; width: 14px; height: 26px; border-right: 4px solid #1A73E8; border-bottom: 4px solid #1A73E8; transform: rotate(45deg); margin-top: -6px; }
  .t { font-family: CsSansB, -apple-system, "Hiragino Sans", sans-serif; font-size: 20px; line-height: 1.5; }
  .m { font-size: 14px; color: #6E6E73; line-height: 1.7; margin-top: 10px; }
  .bar { height: 4px; border-radius: 2px; background: #E5E5EA; margin: 22px 0 0; overflow: hidden; }
  .bar b { display: block; height: 100%; width: 0; background: #1A73E8; animation: go 2s linear forwards; }
  @keyframes go { to { width: 100%; } }
  .btn { display: block; width: 100%; height: 52px; margin-top: 20px; border: 0; border-radius: 14px; background: #1A73E8; color: #FFFFFF; font-size: 16px; font-family: CsSansB, -apple-system, "Hiragino Sans", sans-serif; }
  .btn:active { opacity: .7; }
</style>
</head>
<body>
  <div class="card">
    <div class="app">Card-San 管理アプリ</div>
    <div class="ok"><i></i></div>
    <div class="t">Instagram と連携しました</div>
    <div class="m">ビジネスアカウントの確認ができました。<br />まもなく「お知らせ設定」に戻ります。</div>
    <div class="bar"><b></b></div>
    <button class="btn" type="button" id="next">お知らせ設定に戻る</button>
  </div>
  <script>
    var code = '{{$code}}';
    var isMobile = @if($client == 'mobile') true @else false @endif;
    var done = false;
    function goBack() {
      if (done) return;
      if (isMobile) {
        if (window.uni && uni.redirectTo) { done = true; uni.redirectTo({ url: '/pages/notice/setting/setting?code=' + code }); }
      } else {
        done = true; location.href = 'https://card-san.jp/#/noticeSet';
      }
    }
    document.getElementById('next').addEventListener('click', goBack);
    document.addEventListener('UniAppJSBridgeReady', function () { setTimeout(goBack, 2000); });
    if (!isMobile) setTimeout(goBack, 2000);
  </script>
  <script src="https://js.cdn.aliyun.dcloud.net.cn/dev/uni-app/uni.webview.1.5.2.js"></script>
</body>
</html>
HTML;
echo "② $bladePath:連携が終わったときの画面を新しい見た目に(少し待つと自動で戻る)\n";

if (!$apply) {
    echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n";
    exit;
}

if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ([$f, $bladePath] as $p) {
    if (!copy($base . $p, $backup . str_replace('/', '__', $p))) exit("バックアップに失敗しました: $p\n何も変更していません。\n");
}
file_put_contents($base . $ctlPath, $ctl);
file_put_contents($base . $f, $routes);
file_put_contents($base . $bladePath, $blade);

$ok = true;
foreach ([$ctlPath, $f] as $p) {
    $out = []; $code = 1;
    exec('php -l ' . escapeshellarg($base . $p) . ' 2>&1', $out, $code);
    echo implode("\n", $out), "\n";
    if ($code !== 0) $ok = false;
}
if (!$ok) {
    copy($backup . str_replace('/', '__', $f), $base . $f);
    copy($backup . str_replace('/', '__', $bladePath), $base . $bladePath);
    unlink($base . $ctlPath);
    exit("文法エラーがあったので、元に戻しました。\n");
}
$ref = $base . 'app/Http/Controllers/Api/Member/InstagramController.php';
@chown($base . $ctlPath, fileowner($ref)); @chgrp($base . $ctlPath, filegroup($ref));
foreach (glob($base . 'bootstrap/cache/routes*.php') ?: [] as $rc) {
    $out = [];
    exec('cd ' . escapeshellarg($base) . ' && php artisan route:cache 2>&1', $out);
    echo implode("\n", $out), "\n";
    break;
}
exec('cd ' . escapeshellarg($base) . ' && php artisan view:clear 2>&1', $out2);
$out = [];
exec('cd ' . escapeshellarg($base) . ' && php artisan route:list 2>&1 | grep "shop/instagram/media"', $out);
echo "入口の確かめ:" . (implode(' ', $out) ?: '見つかりません(確認が必要)') . "\n";
echo "完了しました。バックアップ: $backup\n";
