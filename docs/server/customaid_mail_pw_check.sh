#!/bin/bash
# [カスタムエイド 2026-10-29] DirectMail のパスワードが、サーバーの設定に正しく入っているかを確かめる(見るだけ。何も変えない。パスワードは画面に出さない)
#   DirectMail に入れたパスワードをもう一度入力 → 設定から読んだものと同じか・文字数・記号の有無だけを出す
read -r -s -p "DirectMail に入れたパスワードを入力して Enter(画面には出ません): " PW; echo
export CS_PW_IN="$PW"; unset PW
cd /www/wwwroot/api.card-san.jp && php -r '
require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$in = (string) getenv("CS_PW_IN"); $env = (string) env("CS_RESERVE_MAIL_PASSWORD");
echo "入力の文字数:" . strlen($in) . " / 設定から読んだ文字数:" . strlen($env) . "\n";
echo ($in === $env ? "OK 同じです(サーバーの設定は正しい。DirectMail 側の問題)" : "NG 違います(サーバーに入ったパスワードが違う)") . "\n";
echo "英数字以外が入っているか:" . (preg_match("/[^A-Za-z0-9]/", $env) ? "はい" : "いいえ") . "\n";
'
unset CS_PW_IN
