#!/bin/bash
# [カスタムエイド 2026-10-29] SMS の短いリンク r.card-san.jp を作る(宝塔パネルは使わない)
#   https://r.card-san.jp/英数字6文字 → アドレスはそのままで api.card-san.jp の /cs-r/英数字6文字 を表示
#   https://r.card-san.jp/ など、ほかの住所 → https://card-san.jp/ に移す
#   証明書は今の6つと同じ acme.sh(Let's Encrypt)で取り、自動で更新する
#   使い方: bash customaid_rsite_install.sh        → 確認だけ(何も変えない)
#           bash customaid_rsite_install.sh apply  → 作る
D=r.card-san.jp; W=/www/wwwroot/$D; C=/www/server/panel/vhost/cert/$D
V=/www/server/panel/vhost/nginx/$D.conf; NG=/www/server/nginx/sbin/nginx; A=/root/.acme.sh/acme.sh
SOCK=/tmp/php-cgi-73.sock; API=/www/wwwroot/api.card-san.jp/public; MODE=${1:-check}
ok(){ echo "OK $1"; }; ng(){ echo "NG $1"; NGC=$((NGC+1)); }; NGC=0
echo "== 確認 =="
[ -x $NG ] && ok "nginx がある" || ng "nginx が見つからない($NG)"
[ -x $A ] && ok "acme.sh がある" || ng "acme.sh が見つからない"
[ -S $SOCK ] && ok "PHP 7.3 のつなぎ先がある" || ng "PHP 7.3 のつなぎ先がない($SOCK)"
[ -f $API/index.php ] && ok "api.card-san.jp の入口がある" || ng "api.card-san.jp の入口がない"
[ -e $V ] && ng "r.card-san.jp の設定がもうある($V)" || ok "r.card-san.jp の設定はまだない"
[ -e $C ] && ng "r.card-san.jp の証明書の置き場がもうある" || ok "証明書の置き場はまだない"
$A --list 2>/dev/null | grep -q "^$D " && ng "acme.sh に r.card-san.jp がもうある" || ok "acme.sh に r.card-san.jp はまだない"
[ "$(getent hosts $D | awk '{print $1}')" = "8.209.220.234" ] && ok "r.card-san.jp はこのサーバーを指している" || ng "r.card-san.jp の住所が違う"
c=$(curl -s -o /dev/null -w "%{http_code}" "https://api.card-san.jp/cs-r/4uzXuv"); [ "$c" = "200" ] && ok "今の SMS のページが開ける(200)" || ng "今の SMS のページ: $c"
$NG -t >/dev/null 2>&1 && ok "今の nginx の設定に誤りはない" || ng "今の nginx の設定に誤りがある(先に直す)"
echo "(参考)cron から nginx が見つかるか: $(PATH=/usr/bin:/bin command -v nginx || echo 見つからない)"
if [ $NGC -gt 0 ]; then echo; echo "止めました(何も変えていません)。この結果を Claude に送ってください。"; exit 1; fi
if [ "$MODE" != "apply" ]; then echo; echo "確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。"; exit 0; fi
# ---- ここから apply ----
P80='server
{
    listen 80;
    server_name r.card-san.jp;
    root /www/wwwroot/r.card-san.jp;
    location ^~ /.well-known/acme-challenge/ { default_type text/plain; try_files $uri =404; }
    location / { return 301 https://$host$request_uri; }
    access_log /www/wwwlogs/r.card-san.jp.log;
    error_log /www/wwwlogs/r.card-san.jp.error.log;
}'
P443='server
{
    listen 443 ssl;
    http2 on;
    server_name r.card-san.jp;
    root /www/wwwroot/r.card-san.jp;
    ssl_certificate /www/server/panel/vhost/cert/r.card-san.jp/fullchain.pem;
    ssl_certificate_key /www/server/panel/vhost/cert/r.card-san.jp/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    add_header Strict-Transport-Security "max-age=31536000";
    location ~ "^/([A-Za-z0-9]{6})$" {
        fastcgi_pass unix:/tmp/php-cgi-73.sock;
        fastcgi_param SCRIPT_FILENAME /www/wwwroot/api.card-san.jp/public/index.php;
        fastcgi_param SCRIPT_NAME /index.php;
        fastcgi_param DOCUMENT_URI /index.php;
        fastcgi_param DOCUMENT_ROOT /www/wwwroot/api.card-san.jp/public;
        fastcgi_param REQUEST_URI /cs-r/$1;
        fastcgi_param QUERY_STRING $query_string;
        fastcgi_param REQUEST_METHOD $request_method;
        fastcgi_param CONTENT_TYPE $content_type;
        fastcgi_param CONTENT_LENGTH $content_length;
        fastcgi_param SERVER_PROTOCOL $server_protocol;
        fastcgi_param REQUEST_SCHEME https;
        fastcgi_param HTTPS on;
        fastcgi_param GATEWAY_INTERFACE CGI/1.1;
        fastcgi_param SERVER_SOFTWARE nginx;
        fastcgi_param REMOTE_ADDR $remote_addr;
        fastcgi_param REMOTE_PORT $remote_port;
        fastcgi_param SERVER_ADDR $server_addr;
        fastcgi_param SERVER_PORT 443;
        fastcgi_param SERVER_NAME $server_name;
        fastcgi_param REDIRECT_STATUS 200;
    }
    location / { return 301 https://card-san.jp/; }
    access_log /www/wwwlogs/r.card-san.jp.log;
    error_log /www/wwwlogs/r.card-san.jp.error.log;
}'
H='# [カスタムエイド 2026-10-29] SMS の短いリンク r.card-san.jp(宝塔パネルを使わずに作った。customaid_rsite_install.sh)'
echo; echo "== 1. http のサイトを作る =="
mkdir -p $W/.well-known/acme-challenge && chown -R www:www $W
printf '%s\n%s\n' "$H" "$P80" > $V
if ! $NG -t >/dev/null 2>&1; then $NG -t; rm -f $V; echo "nginx の設定に誤りがあったので、元に戻しました。この結果を Claude に送ってください。"; exit 1; fi
$NG -s reload; sleep 2; ok "http のサイトを作った"
echo cs_ok > $W/.well-known/acme-challenge/cs_test
t=$(curl -s --max-time 10 http://$D/.well-known/acme-challenge/cs_test); rm -f $W/.well-known/acme-challenge/cs_test
[ "$t" = "cs_ok" ] && ok "確認用のファイルが外から見える" || { ng "確認用のファイルが見えない($t)。http のサイトだけ作った状態で止めました。この結果を Claude に送ってください"; exit 1; }
echo; echo "== 2. 証明書を取る(Let's Encrypt)=="
if ! $A --issue -d $D -w $W --keylength ec-256 --server letsencrypt; then echo "NG 証明書を取れませんでした。http のサイトだけ作った状態で止めました。この結果を Claude に送ってください"; exit 1; fi
mkdir -p $C
if ! $A --install-cert -d $D --ecc --key-file $C/privkey.pem --fullchain-file $C/fullchain.pem --reloadcmd "$NG -s reload"; then echo "NG 証明書を置けませんでした。この結果を Claude に送ってください"; exit 1; fi
ok "証明書を取って置いた(自動更新つき)"
echo; echo "== 3. https で SMS のページにつなぐ =="
printf '%s\n%s\n\n%s\n' "$H" "$P80" "$P443" > $V
if ! $NG -t >/dev/null 2>&1; then $NG -t; printf '%s\n%s\n' "$H" "$P80" > $V; $NG -s reload; echo "nginx の設定に誤りがあったので、http だけの状態に戻しました。この結果を Claude に送ってください。"; exit 1; fi
$NG -s reload; sleep 2; ok "https の設定を入れた"
echo; echo "== 4. 開いてみる =="
for u in /4uzXuv /zzzzzz / /abc; do echo "https://$D$u → $(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' --max-time 10 https://$D$u)"; done
echo "http://$D/4uzXuv → $(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' --max-time 10 http://$D/4uzXuv)"
echo "ページの見出し: $(curl -s --max-time 10 https://$D/4uzXuv | grep -o '<title>[^<]*</title>')"
echo "証明書の期限: $(openssl x509 -enddate -noout -in $C/fullchain.pem | cut -d= -f2)"
echo; echo "作りました。"
