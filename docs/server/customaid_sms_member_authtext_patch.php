<?php
// [カスタムエイド 2026-10-29] Card-San App(お客様)のサインイン・登録の認証コードの本文を「Card-Sanアプリ認証コード：」にする(坂本さん。番号から届くので何の認証コードか分かるように。管理App の「CardSan認証コード：」と見分ける)
//   使い方: php customaid_sms_member_authtext_patch.php → 確認だけ / ... apply → 控えを取ってから変える
$b = '/www/wwwroot/api.card-san.jp/'; $f = 'app/Http/Controllers/Api/Member/AuthController.php'; $apply = ($argv[1] ?? '') === 'apply';
$old = "send(\$request->phone, '認証コード：' . \$code"; $new = "send(\$request->phone, 'Card-Sanアプリ認証コード：' . \$code";
$s = file_get_contents($b . $f); $n = substr_count($s, $old);
echo ($n === 2 ? 'OK ' : 'NG ') . "認証コードの本文($f):見つかった数 $n(2か所のはず)\n";
if ($n !== 2) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if (!$apply) { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_sms_member_authtext_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($b . $f, $bk . basename($f));
file_put_contents($b . $f, str_replace($old, $new, $s));
exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r);
if ($r !== 0) { copy($bk . basename($f), $b . $f); echo "文法NG のため元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "文法OK $f\n\n変えました。控え: $bk\n";
