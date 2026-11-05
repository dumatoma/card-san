cd /www/wwwroot/api.card-san.jp && grep -n "shop/card\|'card'\|member/{\|'member'" routes/api.php | head -20; echo ===; grep -rln "is_point" app | head -20
