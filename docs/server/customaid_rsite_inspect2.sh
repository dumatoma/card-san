#!/bin/bash
# [カスタムエイド 2026-10-29] r.card-san.jp をコマンドで作る前の確認2(見るだけ。何も変えない)
echo "== 1. acme.sh の場所と版 =="; ls -d /root/.acme.sh 2>&1; /root/.acme.sh/acme.sh --version 2>&1 | tail -1
echo "== 2. acme.sh が管理している証明書 =="; /root/.acme.sh/acme.sh --list 2>&1
echo "== 3. 自動更新の予定(root の crontab)=="; crontab -l 2>/dev/null | grep -v "^#"
echo "== 4. api.card-san.jp の証明書の更新のしかた(鍵の中身は出さない)=="
f=$(ls -d /root/.acme.sh/api.card-san.jp* 2>/dev/null | head -1); [ -n "$f" ] && grep -E "^Le_(Webroot|ReloadCmd|RealFullChainPath|RealKeyPath|NextRenewTimeStr|Keylength)=" $f/*.conf
echo "== 5. 証明書の期限 =="
for d in api.card-san.jp card-san.jp order.card-san.jp customaid.card-san.jp form.card-san.jp wss.card-san.jp; do
  c=/www/server/panel/vhost/cert/$d/fullchain.pem; [ -f $c ] && echo "$d: $(openssl x509 -enddate -noout -in $c | cut -d= -f2)" || echo "$d: ファイルなし"; done
echo "== 6. nginx が読む設定の場所 =="; grep -n "include" /www/server/nginx/conf/nginx.conf | grep -v "#"
echo "== 7. order.card-san.jp の設定の全体(見本にする)=="; grep -v "^\s*#" /www/server/panel/vhost/nginx/order.card-san.jp.conf | grep -v "^\s*$"
echo "== 8. well-known の設定 =="; cat /www/server/panel/vhost/nginx/well-known/api.card-san.jp.conf 2>&1
