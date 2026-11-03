#!/bin/bash
# [カスタムエイド 2026-10-29] 予約メールの送り先を Xサーバー(no-reply@card-san.jp)→ Alibaba DirectMail(no-reply@mail.card-san.jp)に切り替える
#   .env の CS_RESERVE_MAIL_ の5行を入れ替える(ほかは変えない)。パスワードは画面に出さずに入力してもらう。戻すときは控えの .env を使う
E=/www/wwwroot/api.card-san.jp/.env
n=$(grep -c "^CS_RESERVE_MAIL_" $E); if [ "$n" != "5" ]; then echo "NG CS_RESERVE_MAIL_ の行が5行ではありません($n 行)。何も変えていません。Claude に知らせてください。"; exit 1; fi
read -r -s -p "DirectMail の SMTP パスワードを入力して Enter(画面には出ません): " PW; echo
read -r -s -p "確認のため、もう一度入力して Enter: " PW2; echo
if [ -z "$PW" ] || [ "$PW" != "$PW2" ]; then echo "NG パスワードが空か、2回が一致しません(何も変えていません)"; exit 1; fi
case "$PW" in *"'"*) echo "NG パスワードに ' が入っていると保存できません。DirectMail で別のパスワードにしてください"; exit 1;; esac
B=/www/backup_customaid_mail_env_dm_$(date +%Y%m%d_%H%M%S); mkdir -p $B && chmod 700 $B && cp -p $E $B/.env
grep -v "^CS_RESERVE_MAIL_" $E > $E.cs_tmp
{ echo "CS_RESERVE_MAIL_HOST=smtpdm-ap-southeast-1.aliyun.com"; echo "CS_RESERVE_MAIL_PORT=465"; echo "CS_RESERVE_MAIL_ENCRYPTION=ssl"
  echo "CS_RESERVE_MAIL_USERNAME=no-reply@mail.card-san.jp"; echo "CS_RESERVE_MAIL_PASSWORD='$PW'"; } >> $E.cs_tmp
chown --reference=$E $E.cs_tmp; chmod --reference=$E $E.cs_tmp; mv $E.cs_tmp $E
unset PW PW2
echo "切り替えました(パスワード以外):"; grep "^CS_RESERVE_MAIL_" $E | grep -v PASSWORD; echo "控え: $B"
