#!/bin/bash
# [カスタムエイド 2026-11-02] 段階6.5 の前の確認 その2(見るだけ。何も変えない):EmailService の中身・呼んでいる場所・メールの見た目
cd /www/wwwroot/api.card-san.jp
echo "== 1. app/Services/EmailService.php =="; cat -n app/Services/EmailService.php | cut -c1-220
echo "== 2. EmailService を呼んでいる場所 =="; grep -rn "EmailService" app/ routes/ --include=*.php | grep -v "^app/Services/EmailService.php" | cut -c1-220
echo "== 3. メールの見た目(resources/views/layouts/email)=="; ls -la resources/views/layouts/email/
for f in resources/views/layouts/email/*.blade.php; do echo "-- $f ($(wc -c < $f) バイト)"; grep -o "{{[^}]*}}\|{!![^!]*!!}" "$f" | sort -u | head -10; done
