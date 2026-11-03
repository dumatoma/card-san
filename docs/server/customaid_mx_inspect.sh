#!/bin/bash
# [カスタムエイド 2026-10-29] card-san.jp の受け取り先(MX)を Xサーバーに変える前の確認(見るだけ。何も変えない)
echo "== 1. このサーバーでメールを受け取る仕組み(25番)が動いているか =="; ss -lntp | grep -E ":25 |:465 |:587 " || echo "受け取る仕組みは動いていない"
echo "== 2. メールのソフトが入っているか =="; for s in postfix sendmail exim dovecot; do systemctl is-active $s 2>/dev/null | sed "s/^/$s: /"; done
echo "== 3. Alibaba の入口(セキュリティグループ)は分からないので、外から25番に届くかを試す =="; timeout 5 bash -c 'echo > /dev/tcp/8.209.220.234/25' 2>/dev/null && echo "25番:つながる" || echo "25番:つながらない"
echo "== 4. プログラムが受け取りのメールを読んでいないか(imap / pop3 の設定)=="; cd /www/wwwroot/api.card-san.jp && grep -rln "imap_open\|IMAP_HOST\|webklex\|pop3" app config .env 2>/dev/null | head || true
echo "== 5. 今のメールの送信の設定(パスワードは出さない)=="; grep -E "^MAIL_(MAILER|HOST|PORT|USERNAME|ENCRYPTION|FROM_ADDRESS|FROM_NAME)=" .env
