#!/bin/bash
# [カスタムエイド 2026-10-29] 段階6(予約フォームにメールアドレス)の前の確認(見るだけ。何も変えない)
cd /www/wwwroot/api.card-san.jp
echo "== 1. web_order にメールの欄があるか =="; php -d disable_functions= -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo implode(",", Illuminate\Support\Facades\Schema::getColumnListing("web_order")), "\n";'
echo "== 2. OrderModel の fillable / guarded =="; grep -n "fillable\|guarded" app/Models/OrderModel.php
echo "== 3. お客様の予約(Member/OrderController の store)=="; awk '/function store/{p=1} p{print FNR": "$0; if(/^    }/){exit}}' app/Http/Controllers/Api/Member/OrderController.php | grep -n "" | sed -n 1,400p | grep -v "^\s*$" | cut -c1-220
echo "== 4. SMS を送る場所の前後(お店の予約・変更・キャンセル・前日)=="
for f in app/Http/Controllers/Api/Shop/OrderController.php app/Jobs/OrderNoticeJob.php; do grep -n "CsSmsText::build" $f | cut -d: -f1 | while read n; do echo "-- $f:$n"; sed -n "$((n-8)),$((n+3))p" $f | cut -c1-200; done; done
