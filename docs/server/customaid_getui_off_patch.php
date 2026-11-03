<?php
// [カスタムエイド 2026-10-27b] 個推(GeTui)への送信を止める。iPhone は Apple に直接送る(CsApns)だけにする。確認だけ:引数なし/反映:apply
//   アプリから個推を外しても、個推のサーバーに前のアプリの番号が残っていて、Apple 経由で同じ通知がもう1つ届き、バッジの数もずれていた(坂本さんの確認)
//   Android は今だれも使っていない(FCM はこれから)。確認だけのときは、送る処理・Apple に送る部品・今日の送信の記録も表示する
$apply = ($argv[1] ?? '') === 'apply'; $b = '/www/wwwroot/api.card-san.jp/'; $f = $b . 'app/Libs/GeTuiApi.php';
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$t = file_get_contents($f); $a = "        \$csDone = \\App\\Libs\\CsApns::mirror(\$this->type, \$msg_list);\n";
if (strpos($t, 'customaid-getui-off') !== false) { echo "○ もう止めてあります\n"; exit; }
$c = substr_count($t, $a); echo ($c === 1 ? '○' : '✕') . " 個推に送る手前の場所 {$c}か所\n";
if (!$apply) {
  echo "\n----- 送る処理(pushSingleBatchCid)\n"; passthru("grep -n -A40 'function pushSingleBatchCid' $f | head -55");
  echo "\n----- Apple に送る部品(CsApns の mirror と spool)\n"; passthru("grep -n 'function \\|spool(\\|badge\\|foreach' {$b}app/Libs/CsApns.php | head -40");
  echo "\n----- 今日 Apple に送った記録(最後の15件)\n"; passthru("tail -15 {$b}storage/logs/cs_apns/" . date('Y-m-d') . ".log 2>&1 | cut -c1-200");
}
if ($c !== 1) { echo "✕ 反映しません\n"; exit(1); }
$n = str_replace($a, $a . "        // [カスタムエイド 2026-10-27b] customaid-getui-off:個推には送らない(Apple に直接送った分だけ)。前のアプリの番号に個推経由でもう1つ届いていたため\n        return ['success' => true, 'message' => 'cs-apns-only', 'data' => []];\n", $t);
$tmp = '/tmp/customaid_getui_off.php'; file_put_contents($tmp, $n); exec('/www/server/php/73/bin/php -l ' . $tmp . ' 2>&1', $o, $rc); @unlink($tmp);
echo ($rc ? "✕ 書き方に間違い " . implode(' ', $o) : "○ 直したあとの書き方 問題なし") . "\n"; if ($rc) exit(1);
if (!$apply) { echo "\n反映するときは apply を付けて実行\n"; exit; }
$bk = '/www/backup_customaid_getui_off_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($f, $bk . 'GeTuiApi.php');
$st = stat($f); file_put_contents($f, $n); @chown($f, $st['uid']); @chgrp($f, $st['gid']);
exec('kill -USR2 $(pgrep -f "[p]hp-fpm: master") 2>&1');
echo "○ 個推への送信を止めました。控え {$bk}GeTuiApi.php(元に戻すとき:\\cp {$bk}GeTuiApi.php $f)\n";
echo "※ キューの仕事(予約の知らせなど)は queue:listen なので、次の仕事から新しい処理になります\n";
