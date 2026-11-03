#!/bin/bash
# [カスタムエイド 2026-10-29] SMS を3行の短い文に切り替える前の確認(見るだけ。何も変えない。鍵は伏せて出す)
cd /www/wwwroot/api.card-san.jp
echo "== 1. SMS の文を作る部品(CsSmsText::build)を使っている場所 =="; grep -rn "CsSmsText::build" app --include=*.php | grep -v "\.bak"
echo "== 2. CsSmsText.php の md5(前に入れたものと同じか)=="; md5sum app/Libs/CsSmsText.php
echo "== 3. SMS を送る部品(鍵は ***** に伏せる)=="; sed -E "s/(AccessKey[A-Za-z]*|accessKey[A-Za-z]*|secret[A-Za-z]*|Secret[A-Za-z]*)(['\"]?\s*(=>|=|,)\s*)['\"][^'\"]+['\"]/\1\2'*****'/g; s/['\"][A-Za-z0-9]{16,}['\"]/'*****'/g" app/Libs/AlibabaCloudSms.php | cat -n
echo "== 4. SMS の記録の最後の3件(電話番号は伏せる)=="; f=$(ls -t storage/logs/sms/*.log 2>/dev/null | head -1); echo "$f"; tail -6 "$f" 2>/dev/null | sed -E 's/(81|0)[0-9]{9,10}/***********/g' | cut -c1-300
