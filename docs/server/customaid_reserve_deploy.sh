#!/bin/bash
# [カスタムエイド 2026-10-30] 予約WEB(order.card-san.jp/reserve)を、段階6のビルド(メールの欄・確認の表示・完了画面)に入れ替える
#   使い方: bash deploy_reserve.sh → 確認だけ / ... apply → 今の reserve を控えに移してから入れ替える / ... undo → 控えに戻す
#   フォント(static/fonts の6つ・約27MB)は大きいので送らず、今の reserve から写す
W=/www/wwwroot/order.card-san.jp; R=$W/reserve; NEW=/root/cs_reserve/reserve; MODE=${1:-check}
LAST=/root/cs_reserve/last_backup.txt
if [ "$MODE" = "undo" ]; then
  [ -f "$LAST" ] || { echo "NG 控えの記録がありません"; exit 1; }
  BK=$(cat "$LAST"); [ -d "$BK/reserve" ] || { echo "NG 控えが見つかりません: $BK/reserve"; exit 1; }
  mv "$R" "$BK/reserve_new_returned" && mv "$BK/reserve" "$R" && echo "元に戻しました(新しいほうは $BK/reserve_new_returned に移しました)"; exit $?
fi
ng=0
[ -d "$R" ] && echo "OK 今の reserve: $(du -sh $R | cut -f1)・ファイル $(find $R -type f | wc -l) 個・持ち主 $(stat -c %U:%G $R)" || { echo "NG 今の reserve がない"; ng=1; }
[ -f "$NEW/index.html" ] && echo "OK 新しい reserve: ファイル $(find $NEW -type f | wc -l) 個" || { echo "NG 新しい reserve がない(/root/cs_reserve/reserve)"; ng=1; }
for f in Black Bold Light Medium Regular Thin; do
  [ -f "$R/static/fonts/NotoSansJP-$f.otf" ] || { echo "NG 今の reserve にフォントがない: NotoSansJP-$f.otf"; ng=1; }
done
[ $ng = 0 ] && echo "OK フォント6つ(今の reserve から写す)"
for s in $(grep -o '/reserve/static/[^"> ]*' "$NEW/index.html"); do
  [ -f "$NEW/${s#/reserve/}" ] && echo "OK index が読むファイル: ${s#/reserve/}" || { echo "NG index が読むファイルがない: $s"; ng=1; }
done
grep -q "customaid-slide-20261026b" "$NEW/index.html" && echo "OK 10/26 のスライドが入っている" || { echo "NG スライドが入っていない"; ng=1; }
grep -q "<title>Card-San 予約</title>" "$NEW/index.html" && echo "OK タイトル Card-San 予約" || { echo "NG タイトルが違う"; ng=1; }
grep -lq "lastOrderEmail" $NEW/static/js/pages-completed-completed.*.js && echo "OK 予約完了の画面にメールの文" || { echo "NG 予約完了の画面が古い"; ng=1; }
if [ $ng != 0 ]; then echo; echo "止めました(何も変えていません)。この結果を Claude に送ってください。"; exit 1; fi
if [ "$MODE" != "apply" ]; then echo; echo "確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。"; exit 0; fi
TS=$(date +%Y%m%d_%H%M%S); BK=/www/backup_customaid_reserve_$TS; ST=$W/reserve_new_$TS; OWN=$(stat -c %U:%G $R)
mkdir -p "$BK" && chmod 700 "$BK" || exit 1
cp -a "$NEW" "$ST" && cp -a "$R/static/fonts" "$ST/static/fonts" && chown -R "$OWN" "$ST" && find "$ST" -type d -exec chmod 755 {} + && find "$ST" -type f -exec chmod 644 {} + || { rm -rf "$ST"; echo "NG 新しいほうの準備で失敗(今の reserve は変えていません)"; exit 1; }
mv "$R" "$BK/reserve" && mv "$ST" "$R" || { [ -d "$R" ] || mv "$BK/reserve" "$R"; echo "NG 入れ替えで失敗(元に戻しました)"; exit 1; }
echo "$BK" > "$LAST"
echo "入れ替えました。今の reserve: ファイル $(find $R -type f | wc -l) 個"
echo "元の reserve の控え: $BK/reserve"
echo "おかしいときは: bash /root/cs_reserve/deploy_reserve.sh undo"
