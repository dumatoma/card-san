<?php
// [カスタムエイド 2026-10-26] Apple でサインインのとき、ときどき出るエラー
//   「file_get_contents(https://appleid.apple.com/auth/keys): ... 501 Not Implemented」を直す
//   原因:サインインを確かめる部品(vendor/griffinledingham/php-apple-signin)が、Apple の鍵を毎回取りに行き、ときどき失敗する。
//         失敗するとプログラムのエラー文が、そのままお客さまの画面に出ていた
//   直し(Card-San の側 app/Libs/Apple.php だけ。部品は書き換えない):
//     ① 問い合わせに名前(User-Agent)と待ち時間(10秒)を付ける
//     ② 失敗したら少し待って、あと2回やり直す
//     ③ それでもだめなら、画面には「うまくいきませんでした。もう一度お試しください。」と出し、
//        エラーの中身は storage/logs/customaid_apple_signin.log に残す
// 使い方:
//   確認だけ:php -d disable_functions= /root/customaid_apple_keys_patch.php
//   反映    :php -d disable_functions= /root/customaid_apple_keys_patch.php apply
$apply = ($argv[1] ?? '') === 'apply';
$f = '/www/wwwroot/api.card-san.jp/app/Libs/Apple.php';
$t = @file_get_contents($f);
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
if ($t === false) { echo "✕ $f がありません\n"; exit(1); }
$old = "        try {\n            \$appleSignInPayload = ASDecoder::getAppleSignInPayload(\$id_token);\n        } catch (\\Throwable \$th) {\n            return ['success' => false, 'message' => \$th->getMessage()];\n        }\n";
$new = "        // [カスタムエイド 2026-10-26] Apple の鍵を取りに行くのが、ときどき失敗する(501 Not Implemented など)ため、\n"
     . "        //   名前と待ち時間を付けて問い合わせ、失敗したら2回までやり直す。エラー文はお客さまに見せず、記録に残す\n"
     . "        \$appleSignInPayload = null;\n"
     . "        \$csLast = null;\n"
     . "        \$csPrev = stream_context_get_options(stream_context_get_default());\n"
     . "        stream_context_set_default(['http' => ['user_agent' => 'CardSan-API/1.0 (+https://card-san.jp)', 'timeout' => 10]]);\n"
     . "        for (\$csTry = 1; \$csTry <= 3 && !\$appleSignInPayload; \$csTry++) {\n"
     . "            try {\n"
     . "                \$appleSignInPayload = ASDecoder::getAppleSignInPayload(\$id_token);\n"
     . "            } catch (\\Throwable \$th) {\n"
     . "                \$csLast = \$th;\n"
     . "                @file_put_contents(storage_path('logs/customaid_apple_signin.log'), date('Y-m-d H:i:s') . \" try=\$csTry \" . \$th->getMessage() . \"\\n\", FILE_APPEND);\n"
     . "                if (\$csTry < 3) usleep(400000 * \$csTry);\n"
     . "            }\n"
     . "        }\n"
     . "        stream_context_set_default(\$csPrev ?: ['http' => []]);\n"
     . "        if (!\$appleSignInPayload) {\n"
     . "            return ['success' => false, 'message' => 'うまくいきませんでした。もう一度お試しください。'];\n"
     . "        }\n";
$c = substr_count($t, $old);
if ($c !== 1) { echo "✕ 直す場所が {$c} か所(1か所のはず)。何も変更していません。\n"; exit(1); }
echo "○ 直す場所:1か所(getUserId の中の Apple の確認)\n";
$n = str_replace($old, $new, $t);
$tmp = '/tmp/customaid_apple_check.php';
file_put_contents($tmp, $n);
exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $rc);
@unlink($tmp);
if ($rc) { echo "✕ 直したあとの文に書き間違いがあります:" . implode(' ', $o) . "\n何も変更していません。\n"; exit(1); }
echo "○ 直したあとの文の書き方:問題なし\n";
// 今このサーバーから Apple の鍵が取れるか(取れなくても直しは入れられる)
$ctx = stream_context_create(['http' => ['user_agent' => 'CardSan-API/1.0 (+https://card-san.jp)', 'timeout' => 10, 'ignore_errors' => true]]);
$body = @file_get_contents('https://appleid.apple.com/auth/keys', false, $ctx);
$st = isset($http_response_header[0]) ? $http_response_header[0] : '応答なし';
echo "○ 今 Apple の鍵を取ってみた結果:$st / " . ($body && strpos($body, '"keys"') !== false ? '鍵あり' : '鍵なし') . "\n";
if (!$apply) { echo "\n確認だけ終わりました。問題がなければ、最後に apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_apple_keys_' . date('Ymd_His') . '/';
mkdir($bk, 0700, true);
copy($f, $bk . 'Apple.php');
$s = stat($f);
file_put_contents($f, $n);
@chown($f, $s['uid']); @chgrp($f, $s['gid']); @chmod($f, $s['mode'] & 0777);
echo "○ 直しました。控え:$bk\n";
echo "確かめ方:アプリで「Appleでサインイン」を何回か試す。うまくいかないときの記録:/www/wwwroot/api.card-san.jp/storage/logs/customaid_apple_signin.log\n";
echo "元に戻すとき:\\cp {$bk}Apple.php $f\n";
