<?php
// [カスタムエイド 2026-10-25] お店の情報(アプリの店舗情報・会員証)が空になる不具合の修正
//   原因:Googleの通行証(アクセストークン)の期限が切れているとき、新しい通行証をお店のデータに保存する処理
//         (app/Libs/GoogleBusiness.php)が、お店のデータ全体を保存していた。
//         アプリにお店の情報を返す処理(Api/Member/ShopController)は、表示用に business_name(営業中など)・business_color を
//         一時的に付け足しているため、それも一緒に保存しようとして「Unknown column 'business_name'」のエラーになり、
//         お店の情報が返らなかった(2026-10-25 15:41〜15:43 CARD SALON)。
//         (エラーにならない場合でも、表示用に絞り込んだ下のメニュー(page_footer_icon)を保存してしまうおそれがあった)
//   直し方:通行証を保存するときは、通行証の欄だけを保存する(お店のデータのほかの欄には触らない)
//   使い方: php customaid_gbp_token_save_patch.php        … 確認だけ(何も変更しない。直す前と直した後を表示)
//           php customaid_gbp_token_save_patch.php apply  … 変更する(バックアップを取り、文法の確認に失敗したら元に戻す)
$file = '/www/wwwroot/api.card-san.jp/app/Libs/GoogleBusiness.php';
$backup = '/www/backup_customaid_gbp_token_20261025/';
$apply = (($argv[1] ?? '') === 'apply');

$s = @file_get_contents($file);
if ($s === false) exit("ファイルが読めません: $file\n");
if (strpos($s, 'customaid-20261025-token') !== false) exit("すでに直してあります。何も変更していません。\n");

$p = strpos($s, 'gbp_token_expires < time() + 300');
if ($p === false) exit("通行証の期限を調べている所が見つかりません。何も変更していません。\n");
if (substr_count($s, 'gbp_token_expires < time() + 300') !== 1) exit("通行証の期限を調べている所が1か所ではありません。何も変更していません。\n");

// その後ろ(1500文字以内)にある「$shop->update([ ... ]);」を探す
$q = strpos($s, '$shop->update(', $p);
if ($q === false || $q - $p > 1500) {
    echo "通行証を保存している所が「\$shop->update(」の形ではありませんでした。何も変更していません。\n";
    echo "---- 該当の所(確認用)----\n" . substr($s, max(0, $p - 200), 1200) . "\n";
    exit;
}
// 対応する「);」を探す(カッコの数を数える)
$i = $q + strlen('$shop->update(');
$depth = 1; $len = strlen($s); $inStr = null;
for (; $i < $len; $i++) {
    $c = $s[$i];
    if ($inStr) { if ($c === '\\') { $i++; continue; } if ($c === $inStr) $inStr = null; continue; }
    if ($c === "'" || $c === '"') { $inStr = $c; continue; }
    if ($c === '(') $depth++;
    if ($c === ')') { $depth--; if ($depth === 0) break; }
}
if ($depth !== 0 || ($s[$i + 1] ?? '') !== ';') exit("保存している所の終わりが見つかりません。何も変更していません。\n");
$old = substr($s, $q, $i + 2 - $q);                       // $shop->update([ ... ]);
$arr = trim(substr($s, $q + strlen('$shop->update('), $i - $q - strlen('$shop->update(')));
if ($arr === '' || $arr[0] !== '[') exit("保存している中身が [ ... ] の形ではありません。何も変更していません。\n");

// 行頭の字下げをそろえる
$ls = strrpos(substr($s, 0, $q), "\n");
$ind = preg_replace('/\S.*/s', '', substr($s, $ls + 1, $q - $ls - 1));
$new = "/* customaid-20261025-token: 通行証の欄だけを保存する(お店のデータ全体を保存すると、表示用に付け足した business_name などまで保存しようとしてエラーになっていた) */\n"
     . $ind . "\$csTokenCols = " . $arr . ";\n"
     . $ind . "if (\$shop->usesTimestamps() && \$shop->getUpdatedAtColumn()) {\n"
     . $ind . "    \$csTokenCols[\$shop->getUpdatedAtColumn()] = \$shop->freshTimestampString(); // 更新日時も今までどおり新しくする\n"
     . $ind . "}\n"
     . $ind . "\$shop->newQuery()->whereKey(\$shop->getKey())->update(\$csTokenCols);\n"
     . $ind . "foreach (\$csTokenCols as \$csK => \$csV) {\n"
     . $ind . "    \$shop->setAttribute(\$csK, \$csV);\n"
     . $ind . "    \$shop->syncOriginalAttribute(\$csK);\n"
     . $ind . "}";

$s2 = substr($s, 0, $q) . $new . substr($s, $i + 2);
echo "対象:$file\n\n---- 直す前 ----\n$ind$old\n\n---- 直した後 ----\n$ind$new\n\n";

if (!$apply) { echo "確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
if (!copy($file, $backup . 'GoogleBusiness.php')) exit("バックアップに失敗しました。何も変更していません。\n");
if (file_put_contents($file, $s2) === false) exit("書き込めません。何も変更していません。\n");
exec('php -l ' . escapeshellarg($file) . ' 2>&1', $out, $rc);
if ($rc !== 0) {
    copy($backup . 'GoogleBusiness.php', $file);
    exit("文法の確認でエラーが出たので、元に戻しました。\n" . implode("\n", $out) . "\n");
}
echo implode("\n", $out) . "\n完了しました。バックアップ:{$backup}GoogleBusiness.php\n";
echo "反映のため、次を実行してください:/etc/init.d/php-fpm-73 restart\n";
echo "元に戻すとき:cp -p {$backup}GoogleBusiness.php $file && /etc/init.d/php-fpm-73 restart\n";
