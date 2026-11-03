<?php
// [カスタムエイド 2026-10-30] 予約メールなどの ID(Message-ID)を送り主と同じ @mail.card-san.jp にする(今は @alibaba.com。Gmail の判定を少しでもよくするため)
//   DirectMail の API の Headers で Message-ID を渡す。.env に CS_MAIL_OWN_MSGID=false と書けば元の動き(@alibaba.com)に戻る
//   使い方: php -d disable_functions= customaid_mail6_msgid_patch.php → 確認だけ / ... apply → 控えを取ってから変える
$b = '/www/wwwroot/api.card-san.jp/'; $f = 'app/Libs/CsReserveMail.php'; $apply = ($argv[1] ?? '') === 'apply';
$old = "            if (\$bcc !== '' && filter_var(\$bcc, FILTER_VALIDATE_EMAIL) && strcasecmp(\$bcc, \$to) !== 0) \$f['BccAddress'] = \$bcc;\n";
$new = $old . "            if (env('CS_MAIL_OWN_MSGID', true)) \$f['Headers'] = json_encode(['Message-ID' => '<' . bin2hex(random_bytes(16)) . '@' . self::domain() . '>']); // [カスタムエイド 2026-10-30] メールの ID を送り主のドメインに\n";
$s = file_get_contents($b . $f); $n = substr_count($s, $old);
echo ($n === 1 ? 'OK ' : 'NG ') . "写しの行のあとに入れる:見つかった数 $n\n";
if (strpos($s, "CS_MAIL_OWN_MSGID") !== false) { echo "NG もう入っています\n"; $n = 0; }
if ($n !== 1) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if (!$apply) { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_mail6_msgid_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($b . $f, $bk . basename($f));
file_put_contents($b . $f, str_replace($old, $new, $s));
exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r);
if ($r !== 0) { copy($bk . basename($f), $b . $f); echo "文法NG のため元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "文法OK $f\n\n変えました。控え: $bk\n";
