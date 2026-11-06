<?php
// [カスタムエイド 2026-11-06] 予約リンク(Place Actions API)の確かめ — 見るだけ。Google にも表にも何も書かない
//   Google と連携しているお店ごとに、
//     1. そのお店の Google の業種で付けられるリンクの種類(APPOINTMENT があれば予約リンクを付けられる)
//     2. いま Google に付いているリンク(だれが付けたか・優先か)
//   を表示する
//
//   php -d disable_functions= /root/gbp_look2.php

$base = '/www/wwwroot/api.card-san.jp/';
require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$api = new App\Libs\GoogleBusiness();
$shops = App\Models\ShopModel::where('gbp_location_name', '<>', '')->whereNotNull('gbp_access_token')->get();
if (!count($shops)) exit("Google と連携しているお店がありません。\n");
foreach ($shops as $shop) {
    $loc = App\Services\GbpService::placeLocationId($shop);
    echo "==== お店 ID {$shop->id}  Google のお店の名前: {$shop->gbp_location_title}\n";
    $tk = $api->getValidToken($shop);
    if (!$tk['success'] || $loc === '') { echo "  Google との連携を確かめられませんでした: " . ($tk['message'] ?? '') . "\n"; continue; }
    $t = $api->placeActionTypes($tk['token'], $loc);
    if ($t['success']) {
        $types = array_column((array) ($t['data']['placeActionTypeMetadata'] ?? []), 'placeActionType');
        echo "  付けられるリンクの種類: " . ($types ? implode(', ', $types) : '(なし)') . "\n";
        echo "  → 予約リンク: " . (in_array('APPOINTMENT', $types, true) ? '付けられます' : '付けられません(業種が予約に対応していない)') . "\n";
    } else {
        echo "  種類を調べられませんでした: " . mb_substr((string) $t['message'], 0, 200) . "\n";
    }
    $l = $api->listPlaceActionLinks($tk['token'], $loc);
    if ($l['success']) {
        $links = (array) ($l['data']['placeActionLinks'] ?? []);
        echo "  いま付いているリンク: " . count($links) . " 件\n";
        foreach ($links as $x) {
            echo '   - ' . ($x['placeActionType'] ?? '') . ' / ' . ($x['providerType'] ?? '') . ' / 優先:' . (!empty($x['isPreferred']) ? 'はい' : 'いいえ') . ' / ' . ($x['uri'] ?? '') . "\n";
        }
    } else {
        echo "  いまのリンクを調べられませんでした: " . mb_substr((string) $l['message'], 0, 200) . "\n";
    }
    echo "  Card-San の予約リンクのスイッチ: " . ((int) ($shop->gbp_book_on ?? 0) ? 'オン' : 'オフ') . "\n";
}
echo "\n見るだけでした(何も変更していません)。\n";
