#!/bin/bash
# [カスタムエイド 2026-10-29] SMS のページにメニューの写真・最初に渡すカードを足す前と、クーポン一覧の未読の印の前の確認(見るだけ。何も変えない)
cd /www/wwwroot/api.card-san.jp
fn(){ awk -v f="function $2" 'index($0,f){p=1} p{print FNR": "$0; if(/^    }/){exit}}' "$1"; }
echo "== 1. お客様のクーポン一覧(Member/CouponController の index)=="; fn app/Http/Controllers/Api/Member/CouponController.php index | head -60
echo "== 2. 予約のメニューのつながり(OrderModel の menu)=="; grep -n "function menu\|function shop\b" -A3 app/Models/OrderModel.php
echo "== 3. お店のカード一覧(Shop/CardController の index)=="; fn app/Http/Controllers/Api/Shop/CardController.php index | head -70
echo "== 4. 最初に渡すカードの印(default)=="; grep -n "default" app/Http/Controllers/Api/Shop/CardController.php | head -20
echo "== 5. image を作っているところ =="; grep -rn "function getImageAttribute\|function getImagesAttribute" app/Models | head
php -d disable_functions= -r '
require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$o = App\Models\OrderModel::find(409);
echo "== 6. 予約409のメニュー ==\n"; if ($o && $o->menu) { $m = $o->menu; echo "表: " . $m->getTable() . "\n"; foreach ($m->getAttributes() as $k => $v) if (preg_match("/image|img|pic|photo/i", $k)) echo "$k(そのまま): " . substr((string)$v, 0, 200) . "\n"; echo "image(読んだ形): " . substr(json_encode($m->image, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), 0, 300) . "\n"; } else echo "メニューなし\n";
echo "== 7. カードらしい表と欄 ==\n"; foreach (Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE \"%card%\"") as $r) { $t = array_values((array)$r)[0]; echo "-- $t: " . implode(",", Illuminate\Support\Facades\Schema::getColumnListing($t)) . "\n"; }
'
