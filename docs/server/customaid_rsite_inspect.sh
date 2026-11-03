#!/bin/bash
# [カスタムエイド 2026-10-29] r.card-san.jp を作る前の確認(見るだけ。何も変えない)
V=/www/server/panel/vhost/nginx
echo "== 1. api.card-san.jp の nginx 設定(PHP のつなぎ方・追加設定の読み込み)=="
grep -n "listen\|server_name\|root \|include\|ssl_certificate\|return 301\|rewrite" $V/api.card-san.jp.conf
echo "== 2. PHP 7.3 のつなぎ先 =="
cat /www/server/nginx/conf/enable-php-73.conf 2>/dev/null | grep -v "^\s*#"
echo "== 3. r.card-san.jp の設定がもうあるか =="
ls -la $V/r.card-san.jp.conf $V/extension/r.card-san.jp 2>&1
ls -d /www/wwwroot/r.card-san.jp 2>&1
echo "== 4. r.card-san.jp の住所(このサーバーを指しているか)=="
getent hosts r.card-san.jp; curl -s https://api.ipify.org; echo
echo "== 5. 80番(http)で外から届くか(Let's Encrypt の確認に使う)=="
curl -s -o /dev/null -w "http://r.card-san.jp/ → %{http_code}\n" --max-time 10 http://r.card-san.jp/
