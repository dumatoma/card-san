<?php
// [カスタムエイド 2026-10-29] SMS の送り主の名前を「cdsSMS」→「CardSan Rsv」に変える(全部の SMS 共通)。
//   あわせて、管理App の認証コードの本文「CardSan認証コード：」を Card-San App と同じ「認証コード：」にそろえる。
//   送り主の名前は .env の CS_SMS_FROM で後から変えられる(書いていなければ CardSan Rsv)
//   使い方: php customaid_sms_from_patch.php        → 確認だけ(何も変えない)
//           php customaid_sms_from_patch.php apply  → 控えを取ってから変える
$b = '/www/wwwroot/api.card-san.jp/'; $apply = ($argv[1] ?? '') === 'apply';
$jobs = [ // [ファイル, 場所の名前, 今の文字, 新しい文字]
  ['app/Libs/AlibabaCloudSms.php', '送り主の名前', "'From' => \"cdsSMS\",",
   "'From' => env('CS_SMS_FROM', 'CardSan Rsv'), // [カスタムエイド 2026-10-29] 送り主の名前(元は cdsSMS)"],
  ['app/Http/Controllers/Api/Shop/AuthController.php', '管理App の認証コードの本文', "'CardSan認証コード：' . \$code",
   "'認証コード：' . \$code"],
];
$out = []; $ng = 0;
foreach ($jobs as $j) { list($f, $name, $old, $new) = $j;
    if (!isset($out[$f])) $out[$f] = file_get_contents($b . $f);
    $n = substr_count($out[$f], $old);
    echo ($n === 1 ? 'OK ' : 'NG ') . "$name($f):見つかった数 $n\n"; if ($n !== 1) { $ng++; continue; }
    $out[$f] = str_replace($old, $new, $out[$f]); }
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if (!$apply) { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_sms_from_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
foreach ($out as $f => $s) { copy($b . $f, $bk . basename($f)); file_put_contents($b . $f, $s); }
$bad = 0; foreach ($out as $f => $s) { exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r); echo ($r === 0 ? '文法OK ' : '文法NG ') . "$f\n"; $o = []; if ($r !== 0) $bad++; }
if ($bad) { foreach ($out as $f => $s) copy($bk . basename($f), $b . $f); echo "\n文法の誤りがあったので、元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "\n変えました。控え: $bk\n";
