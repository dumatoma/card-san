<?php
// [カスタムエイド 2026-10-28] クーポン配信:先にお客様のクーポンの記録を作ってから通知を送る。
//   以前は通知を送ってから記録を作っていたので、通知に入れるアイコンの数に新しいクーポンが入らず、閉じているアプリのバッジが増えなかった(坂本さん確認)
//   使い方: php customaid_coupon_order_patch.php → 確認だけ / php customaid_coupon_order_patch.php apply → 控えを取ってから変える
$b = '/www/wwwroot/api.card-san.jp/'; $f = $b . 'app/Jobs/CouponJob.php'; $apply = ($argv[1] ?? '') === 'apply';
$re = '/\n {8}if\(\$msg_list\)\{\s*\n {12}\(new \\\\App\\\\Libs\\\\GeTuiApi\(\'user\'\)\)->pushSingleBatchCid\(\$msg_list\);\s*\n {8}\}\s*\n\s*\n {8}return MemberCouponModel::insert\(\$data\);/';
$new = "\n        // [カスタムエイド 2026-10-28] 先にクーポンの記録を作ってから通知を送る(通知に入れるアイコンの数に、新しいクーポンを入れるため)\n"
     . "        \$csRes = MemberCouponModel::insert(\$data);\n\n"
     . "        if(\$msg_list){\n            (new \\App\\Libs\\GeTuiApi('user'))->pushSingleBatchCid(\$msg_list);\n        }\n\n"
     . "        return \$csRes;";
$s = file_get_contents($f); $n = preg_match_all($re, $s);
echo ($n === 1 ? 'OK' : 'NG') . " 直す場所が見つかった数:$n\n";
if ($n !== 1) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if (!$apply) { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_coupon_order_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($f, $bk . basename($f));
file_put_contents($f, preg_replace_callback($re, function () use ($new) { return $new; }, $s));
exec('php -l ' . escapeshellarg($f) . ' 2>&1', $o, $r);
if ($r !== 0) { copy($bk . basename($f), $f); echo "文法NG のため元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "文法OK\n変えました。控え: $bk\n";
