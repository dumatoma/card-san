# [カスタムエイド 2026-10-28] 一覧が空・undefined の調査 その2(読むだけ。何も変更しない)。IP と URL の後ろの部分は伏せる
L=/www/wwwlogs/api.card-san.jp.log; E=/www/wwwlogs/api.card-san.jp.error.log
echo "== 1. 通信の記録の形(最後の3行。IP と ? 以降は伏せる)=="; ls -la $L $E 2>&1; tail -n 3 $L | sed -E 's/^[0-9a-f.:]+ /x.x.x.x /; s/\?[^ "]*/?…/g' | cut -c1-250
echo "== 2. このサイトが使う PHP =="; grep -n "enable-php" /www/server/panel/vhost/nginx/api.card-san.jp.conf
echo "== 3. PHP の同時処理の設定 =="; for v in 73 74 80; do f=/www/server/php/$v/etc/php-fpm.conf; [ -f $f ] && echo "-- php$v" && grep -E "^(pm|pm\.max_children|pm\.start_servers|pm\.min_spare_servers|pm\.max_spare_servers|request_terminate_timeout|listen) ?=" $f; done
echo "== 4. PHP が手いっぱいになった記録(max_children)=="; for v in 73 74 80; do f=/www/server/php/$v/var/log/php-fpm.log; [ -f $f ] && echo "-- php$v: $(grep -c max_children $f) 回" && grep max_children $f | tail -n 5; done
echo "== 5. nginx のエラー記録(種類ごとの回数と最後の時刻)=="; [ -f $E ] && grep -oE "upstream timed out|connect\(\) to unix[^ ]* failed|recv\(\) failed|no live upstreams|upstream prematurely closed|client intended to send too large" $E | sort | uniq -c; [ -f $E ] && tail -n 3 $E | sed -E 's/client: [0-9a-f.:]+/client: x/; s/\?[^ "]*/?…/g' | cut -c1-250
echo "== 6. サーバーの混み具合 =="; uptime; nproc; free -m | head -2
echo "== (おわり) =="
