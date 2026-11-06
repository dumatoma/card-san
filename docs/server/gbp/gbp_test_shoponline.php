<?php
// [カスタムエイド 2026-11-06] Foot Comfort で Place Actions API(予約リンクと同じ仕組み)が動くかを試す
//   予約(APPOINTMENT)の代わりに「オンラインで購入」(SHOP_ONLINE)のリンクを、本当のお店のサイトへ作る/消す
//   Card-San の表(web_shop)には何も書かない。Google のお店のプロフィールだけが変わる
//
//   php -d disable_functions= /root/gbp_test_shoponline.php 店舗ID           … 見るだけ(付けられる種類・いまのリンク。予約を作ろうとして断られるかも試す=作らない)
//   php -d disable_functions= /root/gbp_test_shoponline.php 店舗ID create    … 「オンラインで購入」リンクを作る(https://footcomfort.jp/)
//   php -d disable_functions= /root/gbp_test_shoponline.php 店舗ID delete    … このスクリプトで作ったリンクを消す

$base = '/www/wwwroot/api.card-san.jp/';
$URL = 'https://footcomfort.jp/';
$sid = (int) ($argv[1] ?? 0);
$mode = $argv[2] ?? 'check';
if (!$sid) exit("店舗ID を付けてください(例: php -d disable_functions= /root/gbp_test_shoponline.php 123)\n");
require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$shop = App\Models\ShopModel::find($sid);
if (!$shop || (string) $shop->gbp_location_name === '') exit("店舗ID {$sid} は Google と連携していません。\n");
echo "お店 ID {$sid}  Google のお店の名前: {$shop->gbp_location_title}\n";
$api = new App\Libs\GoogleBusiness();
$loc = App\Services\GbpService::placeLocationId($shop);
$tk = $api->getValidToken($shop);
if (!$tk['success'] || $loc === '') exit("Google との連携を確かめられませんでした: " . ($tk['message'] ?? '') . "\n");
$token = $tk['token'];

$show = function () use ($api, $token, $loc, $URL) {
    $l = $api->listPlaceActionLinks($token, $loc);
    if (!$l['success']) { echo "いまのリンクを調べられませんでした: " . mb_substr((string) $l['message'], 0, 300) . "\n"; return []; }
    $links = (array) ($l['data']['placeActionLinks'] ?? []);
    echo "いま付いているリンク: " . count($links) . " 件\n";
    foreach ($links as $x) {
        echo ' - ' . ($x['placeActionType'] ?? '') . ' / ' . ($x['providerType'] ?? '') . ' / 優先:' . (!empty($x['isPreferred']) ? 'はい' : 'いいえ') . ' / ' . ($x['uri'] ?? '') . (rtrim((string) ($x['uri'] ?? ''), '/') === rtrim($URL, '/') && ($x['placeActionType'] ?? '') === 'SHOP_ONLINE' ? '  ← このテストのリンク' : '') . "\n";
    }
    return $links;
};

if ($mode === 'check') {
    $t = $api->placeActionTypes($token, $loc);
    if ($t['success']) {
        $types = array_column((array) ($t['data']['placeActionTypeMetadata'] ?? []), 'placeActionType');
        echo "付けられるリンクの種類: " . ($types ? implode(', ', $types) : '(なし)') . "\n";
        echo "→ オンラインで購入(SHOP_ONLINE): " . (in_array('SHOP_ONLINE', $types, true) ? '付けられます' : '付けられません') . "\n";
        echo "→ 予約(APPOINTMENT): " . (in_array('APPOINTMENT', $types, true) ? '付けられます' : '付けられません') . "\n";
    } else {
        echo "種類を調べられませんでした: " . mb_substr((string) $t['message'], 0, 300) . "\n";
    }
    $show();
    echo "\n見るだけでした(何も変更していません)。\n";
    exit;
}
if ($mode === 'create') {
    foreach ($show() as $x) {
        if (($x['placeActionType'] ?? '') === 'SHOP_ONLINE' && rtrim((string) ($x['uri'] ?? ''), '/') === rtrim($URL, '/')) exit("同じリンクがもうあります。作りませんでした。\n");
    }
    $c = $api->createPlaceActionLink($token, $loc, ['uri' => $URL, 'placeActionType' => 'SHOP_ONLINE']);
    if (!$c['success']) exit("作れませんでした: " . mb_substr((string) $c['message'], 0, 300) . "\n" . json_encode($c['data']['error'] ?? [], JSON_UNESCAPED_UNICODE) . "\n");
    echo "作りました: " . ($c['data']['name'] ?? '') . "\n";
    $show();
    echo "\nGoogle のプロフィールに出るまで時間がかかることがあります。いらなくなったら delete を付けて実行してください。\n";
    exit;
}
if ($mode === 'delete') {
    $n = 0;
    foreach ($show() as $x) {
        if (($x['placeActionType'] ?? '') === 'SHOP_ONLINE' && rtrim((string) ($x['uri'] ?? ''), '/') === rtrim($URL, '/') && !empty($x['name'])) {
            $d = $api->deletePlaceActionLink($token, $x['name']);
            echo $d['success'] ? "消しました: {$x['name']}\n" : "消せませんでした: " . mb_substr((string) $d['message'], 0, 300) . "\n";
            $n++;
        }
    }
    if (!$n) echo "このテストのリンクはありませんでした。\n";
    exit;
}
echo "最後は create か delete です(何も付けなければ見るだけ)。\n";
