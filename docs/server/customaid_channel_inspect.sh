#!/bin/bash
# [カスタムエイド 2026-10-31] 予約通知に「予約経路(アプリ・メール・LINE・SMS)」を出す前の確認(見るだけ。何も変えない)
cd /www/wwwroot/api.card-san.jp
echo "== 1. 道すじ(order_notice・order) =="; grep -n "order_notice\|'order'\|OrderNotice" routes/api.php | cut -c1-200
echo "== 2. 予約通知を作っている場所 =="; grep -rn "OrderNoticeModel::create" app/ | cut -c1-160
F=$(grep -rl "class OrderNoticeController" app/Http/Controllers/Api/Shop/ | head -1); echo "== 3. $F の index =="
awk '/function index/{p=1} p{print FNR": "$0; if(/^    }/){exit}}' "$F" | cut -c1-200
echo "== 4. OrderNoticeModel =="; cat -n app/Models/OrderNoticeModel.php | cut -c1-200
echo "== 5. お店の予約の詳細(Shop/OrderController show)=="; awk '/function show/{p=1} p{print FNR": "$0; if(/^    }/){exit}}' app/Http/Controllers/Api/Shop/OrderController.php | cut -c1-200
echo "== 6. 予約の数(経路の見当)=="; php -d disable_functions= -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); $d=Illuminate\Support\Facades\DB::table("web_order"); echo "全部 ", (clone $d)->count(), " / 会員(mid>0) ", (clone $d)->where("mid",">",0)->count(), " / メールあり ", (clone $d)->where("email","!=","")->count(), " / 会員でない ", (clone $d)->where("mid",0)->count(), "\n"; echo "予約通知 ", Illuminate\Support\Facades\DB::table("web_order_notice")->count(), "\n";' 2>&1 | tail -3
