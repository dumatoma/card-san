#!/bin/bash
# [カスタムエイド 2026-11-02] 段階6.5 の前の確認(見るだけ。何も変えない):アカウント作成・契約・解約などの自動メールの送り方。パスワード等は出さない
cd /www/wwwroot/api.card-san.jp
echo "== 1. .env の MAIL_ の設定(パスワード・ユーザー名は伏せる) =="; grep -n '^MAIL_' .env | sed -E 's/^([0-9]+:MAIL_(PASSWORD|USERNAME)=).*/\1(伏せています)/'
echo "== 2. config/mail.php の既定と from =="; grep -n "'default'\|'from'\|'address'\|'name' =>" config/mail.php | head -12
echo "== 3. メールを送っている場所 =="; grep -rn "Mail::\|->notify(\|new .*Mail(" app/ --include=*.php | grep -v "CsReserveMail\|CsShopMail" | cut -c1-200
echo "== 4. Mailable / Notification のクラス =="; ls app/Mail app/Notifications 2>/dev/null
for f in app/Mail/*.php; do [ -f "$f" ] && { echo "-- $f"; grep -n "from(\|subject(\|view(\|markdown(\|text(\|attach\|function build" "$f" | cut -c1-200; }; done
echo "== 5. メールの見た目(views)の候補 =="; grep -rln "ro-reply\|cardsan.jp" resources/views app config 2>/dev/null | head
echo "== 6. 送ったメールの記録(laravel.log の最後の Mail 関係 5行) =="; grep -n "Swift\|Expected response code\|mail" storage/logs/laravel.log 2>/dev/null | tail -5 | cut -c1-220
echo "== 7. Laravel の版 =="; php -d disable_functions= artisan --version 2>/dev/null
