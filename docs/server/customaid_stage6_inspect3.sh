#!/bin/bash
# [カスタムエイド 2026-10-30] 段階6の前の確認 その3(見るだけ。何も変えない)。SMS のページの部品・見た目・道すじを見る
cd /www/wwwroot/api.card-san.jp
echo "== 1. app/Libs/CsSmsPage.php =="; cat -n app/Libs/CsSmsPage.php | cut -c1-260
echo "== 2. resources/views/cs_sms_page.blade.php =="; cat -n resources/views/cs_sms_page.blade.php | cut -c1-260
echo "== 3. cs-r の道すじ =="; grep -rn "cs-r\|CsSmsPage\|cs_sms" routes/ | cut -c1-200
echo "== 4. cs_sms_page の表の欄 =="; php -d disable_functions= -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo implode(",", Illuminate\Support\Facades\Schema::getColumnListing("cs_sms_page")), "\n";'
echo "== 5. お店のキャンセル(destroy)で予約を消す場所と SMS の順 =="; grep -n "function destroy\|->delete()\|CsSmsText::build('cancel'" app/Http/Controllers/Api/Shop/OrderController.php
