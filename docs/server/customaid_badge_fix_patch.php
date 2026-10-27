<?php
// [カスタムエイド 2026-10-26] Card-San App のバッジの数が多い(自分が送ったメッセージまで数えている)を直すパッチ。
//   原因:会員がメッセージを送ると、Member/MessageController::store が「会員の未読」まで +1 していた
//         ($unread['member'] = ($unread['member'] ?? 0) + 1;)。会員の未読はお店から届いたときだけ増えるべき。
//   修正:会員が送ったときは、会員の未読を増やさない(お店の管理者の未読は今までどおり +1)。
//   参考として、お店がメッセージを送ったときの未読の付け方(Shop/MessageController)も表示する(変更はしない)。
// 使い方:
//   確認だけ:php -d disable_functions= /root/customaid_badge_fix_patch.php
//   反映    :php -d disable_functions= /root/customaid_badge_fix_patch.php apply
$apply = ($argv[1] ?? '') === 'apply';
$base = '/www/wwwroot/api.card-san.jp/';
$php = '/www/server/php/73/bin/php';
$f = 'app/Http/Controllers/Api/Member/MessageController.php';
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";

$t = @file_get_contents($base . $f);
if ($t === false) { echo "✕ $f がありません\n"; exit(1); }
$old = "\$unread['member'] = (\$unread['member'] ?? 0) + 1;";
$new = "\$unread['member'] = (\$unread['member'] ?? 0); // [カスタムエイド 2026-10-26] 会員が送ったときは、会員の未読(バッジ)を増やさない(以前は +1 して自分の送信まで数えていた)";
if (strpos($t, '[カスタムエイド 2026-10-26] 会員が送ったときは') !== false) { echo "○ すでに直してあります\n"; exit(0); }
$n = substr_count($t, $old);
echo ($n === 1 ? '○' : '✕') . " $f の直す場所:{$n}か所(1か所のはず)\n";
// 前後を表示
foreach (explode("\n", $t) as $i => $l) if (strpos($l, $old) !== false) echo '  ' . ($i + 1) . ': ' . trim($l) . "\n";

echo "\n-- 参考:お店が送ったときの未読の付け方(Shop/MessageController、変更しない)\n";
$s = @file($base . 'app/Http/Controllers/Api/Shop/MessageController.php') ?: [];
foreach ($s as $i => $l) if (preg_match('/\$unread\[/', $l)) echo '  ' . ($i + 1) . ': ' . trim($l) . "\n";

if ($n !== 1) { echo "\n問題があるので止めます。何も変更していません。\n"; exit(1); }
if (!$apply) { echo "\n確認だけ終わりました。問題がなければ、最後に apply を付けて実行してください。\n"; exit(0); }

$bk = '/www/backup_customaid_badgefix_' . date('Ymd_His') . '/';
mkdir($bk, 0700, true);
copy($base . $f, $bk . 'Member_MessageController.php');
file_put_contents($base . $f, str_replace($old, $new, $t));
exec($php . ' -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $rc);
if ($rc !== 0) { copy($bk . 'Member_MessageController.php', $base . $f); echo "✕ 文法エラーのため元に戻しました: " . implode(' ', $o) . "\n"; exit(1); }
echo "○ 直しました(文法 OK)。控え:$bk\n";
echo "このあと php-fpm を読み直してください:/etc/init.d/php-fpm-73 reload\n";
