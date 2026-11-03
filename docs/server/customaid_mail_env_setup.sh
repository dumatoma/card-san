#!/bin/bash
# [カスタムエイド 2026-10-29] 予約メール用の送信元 no-reply@card-san.jp の設定を .env に足す(今のメール ro-reply-auto@cardsan.jp の設定は変えない)
#   パスワードは画面に出さずに入力してもらう(Claude には送らない)。使い方: bash customaid_mail_env_setup.sh
E=/www/wwwroot/api.card-san.jp/.env
if grep -q "^CS_RESERVE_MAIL_" $E; then echo "NG もう CS_RESERVE_MAIL_ の設定があります(何も変えていません)。Claude に知らせてください。"; exit 1; fi
read -r -s -p "no-reply@card-san.jp のパスワードを入力して Enter(画面には出ません): " PW; echo
read -r -s -p "確認のため、もう一度入力して Enter: " PW2; echo
if [ -z "$PW" ] || [ "$PW" != "$PW2" ]; then echo "NG パスワードが空か、2回が一致しません(何も変えていません)"; exit 1; fi
case "$PW" in *"'"*) echo "NG パスワードに ' (シングルクォート)が入っていると保存できません。Xサーバーで別のパスワードに変えてから、もう一度実行してください"; exit 1;; esac
B=/www/backup_customaid_mail_env_$(date +%Y%m%d_%H%M%S); mkdir -p $B && chmod 700 $B && cp -p $E $B/.env
{ echo ""; echo "# [カスタムエイド 2026-10-29] 予約メール(予約受付・明日のご予約・変更・キャンセル)の送信元"
  echo "CS_RESERVE_MAIL_HOST=sv16352.xserver.jp"; echo "CS_RESERVE_MAIL_PORT=465"; echo "CS_RESERVE_MAIL_ENCRYPTION=ssl"
  echo "CS_RESERVE_MAIL_USERNAME=no-reply@card-san.jp"; echo "CS_RESERVE_MAIL_PASSWORD='$PW'"; } >> $E
unset PW PW2
echo "足しました(パスワード以外):"; grep "^CS_RESERVE_MAIL_" $E | grep -v PASSWORD; echo "控え: $B"
