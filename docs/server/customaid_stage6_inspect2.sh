#!/bin/bash
# [カスタムエイド 2026-10-30] 段階6の前の確認 その2(見るだけ。何も変えない)。お店の表・会員の表も見る
cd /www/wwwroot/api.card-san.jp
echo "== 1. web_order にメールの欄があるか =="; php -d disable_functions= -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo implode(",", Illuminate\Support\Facades\Schema::getColumnListing("web_order")), "\n";'
echo "== 2. OrderModel の fillable / guarded =="; grep -n "fillable\|guarded" app/Models/OrderModel.php
echo "== 3. お客様の予約(Member/OrderController の store)=="; awk '/function store/{p=1} p{print FNR": "$0; if(/^    }/){exit}}' app/Http/Controllers/Api/Member/OrderController.php | grep -n "" | sed -n 1,400p | grep -v "^\s*$" | cut -c1-220
echo "== 4. SMS を送る場所の前後(お店の予約・変更・キャンセル・前日)=="
for f in app/Http/Controllers/Api/Shop/OrderController.php app/Jobs/OrderNoticeJob.php; do grep -n "CsSmsText::build" $f | cut -d: -f1 | while read n; do echo "-- $f:$n"; sed -n "$((n-8)),$((n+3))p" $f | cut -c1-200; done; done
echo "== 5. お店の表(web_shop)・会員の表(web_member)の欄 =="; php -d disable_functions= -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); foreach (["web_shop","web_member"] as $t) { echo $t, ": ", implode(",", Illuminate\Support\Facades\Schema::getColumnListing($t)), "\n"; }'
echo "== 6. 予約の Member/OrderController で会員のメールを使っているか =="; grep -n "email" app/Http/Controllers/Api/Member/OrderController.php | cut -c1-160
