<?php
// [カスタムエイド 2026-10-26] 同じプッシュ通知が iPhone に2件届く不具合を直す
//   原因:10/25 から、Apple 用の番号が分かっている iPhone には Apple(APNs)に直接送っている(app/Libs/CsApns.php)。
//         ところが個推(GeTui)にも今までどおり送っていたので、アプリを閉じている iPhone には個推経由の分と合わせて2件届いていた。
//         (2026-10-26 14:06 の「予約受付のお知らせ」:メッセージは1通・Apple への直接送信は1回・届いた通知は2件)
//   修正:
//     1. CsApns::mirror が「Apple に直接送った端末」を返すようにする
//     2. GeTuiApi::pushSingleBatchCid で、その端末は個推に送らない(Android・まだ番号のない古いアプリの iPhone は今までどおり個推)
//     3. Apple への送信の記録(storage/logs/cs_apns/)の時刻を日本時間にする(今は中国時間で、1時間ずれて見えていた)
//   お客さま用(Card-San App)・お店用(管理App)の両方に効く。メッセージ・未読の数(バッジ)の作り方は変えない。
// 使い方:
//   確認だけ:php -d disable_functions= /root/customaid_push_dedupe_patch.php
//   反映    :php -d disable_functions= /root/customaid_push_dedupe_patch.php apply
$apply = ($argv[1] ?? '') === 'apply';
$base = '/www/wwwroot/api.card-san.jp/';
$php = '/www/server/php/73/bin/php';
$tag = '[カスタムエイド 2026-10-26]';
$ok = true;
function say($s) { echo $s, "\n"; }
function ng($s) { global $ok; $ok = false; echo "✕ ", $s, "\n"; }
say($apply ? '=== 反映します ===' : '=== 確認だけ(何も変更しません) ===');

$edits = [];

// 1. CsApns::mirror
$f1 = 'app/Libs/CsApns.php';
$t1 = @file_get_contents($base . $f1);
if ($t1 === false) ng("$f1 がありません");
elseif (strpos($t1, 'csDone') !== false) say("○ $f1 はすでに直してあります");
else {
    $start = strpos($t1, 'public static function mirror(');
    $end = strpos($t1, 'public static function body(');
    if ($start === false || $end === false || $end < $start) ng("$f1 の mirror の場所が見つかりません");
    else {
        $seg = substr($t1, $start, $end - $start);
        $n = $seg;
        $cnt = 0;
        $n = str_replace("return;", "return [];", $n, $c); $cnt += $c;
        $n = str_replace("            \$cids = [];\n", "            \$csDone = []; // $tag Apple に直接送った端末(個推の番号)。呼び出し元で個推に送らないようにする\n            \$cids = [];\n", $n, $c); $cnt += $c;
        $n = str_replace("                    self::spool(\$tok, self::TOPIC[\$type], \$json);\n", "                    self::spool(\$tok, self::TOPIC[\$type], \$json);\n                    \$csDone[\$c] = 1;\n", $n, $c); $cnt += $c;
        $n = preg_replace('/(\n        \} catch \(\\\\Throwable \$e\) \{\n            Log::info\(\'CsApns mirror: \' \. \$e->getMessage\(\)\);\n)(        \})/', "\n            return array_keys(\$csDone);$1            return [];\n$2", $n, 1, $c); $cnt += $c;
        say("○ $f1 の mirror:書きかえ $cnt か所(return [] に変える所・送った端末を覚える所・最後に返す所)");
        if (substr_count($n, 'return array_keys($csDone)') !== 1 || substr_count($n, '$csDone[$c] = 1') !== 1) ng("$f1 の書きかえが想定どおりになりません");
        else $edits[$f1] = substr($t1, 0, $start) . $n . substr($t1, $end);
    }
}

// 2. GeTuiApi::pushSingleBatchCid
$f2 = 'app/Libs/GeTuiApi.php';
$t2 = @file_get_contents($base . $f2);
$old2 = "        \\App\\Libs\\CsApns::mirror(\$this->type, \$msg_list);\n";
$new2 = "        \$csDone = \\App\\Libs\\CsApns::mirror(\$this->type, \$msg_list);\n"
      . "        // $tag Apple に直接送った iPhone には、個推からは送らない(同じ通知が2件届いていた)。Android・番号のない端末は今までどおり個推\n"
      . "        if (\$csDone) {\n"
      . "            \$csKeep = [];\n"
      . "            foreach (\$msg_list as \$csM) {\n"
      . "                \$csC = array_values(array_diff((array) (\$csM['audience']['cid'] ?? []), \$csDone));\n"
      . "                if (!\$csC) continue;\n"
      . "                \$csM['audience']['cid'] = \$csC;\n"
      . "                \$csKeep[] = \$csM;\n"
      . "            }\n"
      . "            \$msg_list = \$csKeep;\n"
      . "            if (!\$msg_list) return ['success' => true, 'message' => 'apns', 'data' => []];\n"
      . "        }\n";
if ($t2 === false) ng("$f2 がありません");
elseif (strpos($t2, '$csDone') !== false) say("○ $f2 はすでに直してあります");
elseif (substr_count($t2, $old2) !== 1) ng("$f2 の書きかえる場所が " . substr_count($t2, $old2) . " か所(1か所のはず)");
else { say("○ $f2:CsApns::mirror の行のあとに、個推に送らない処理を足す"); $edits[$f2] = str_replace($old2, $new2, $t2); }

// 参考:pushSingleBatchCid の戻り値を使っている所
say("\n-- 参考:pushSingleBatchCid の戻り値を使っている所(\$res = … の形)");
exec('cd ' . $base . ' && grep -rn "= (new \\\\\\\\App\\\\\\\\Libs\\\\\\\\GeTuiApi\|= (new GeTuiApi" app 2>/dev/null | head -20', $o);
say($o ? implode("\n", $o) : '(なし)');
exec('cd ' . $base . ' && grep -rn "\$res\[.success.\]\|\$res\[.data.\]" app/Http/Controllers/Api/Shop/OrderController.php app/Http/Controllers/Api/Member/OrderController.php 2>/dev/null | head', $o2);
say($o2 ? "そのあと \$res を見ている行:\n" . implode("\n", $o2) : '(戻り値の中身を見ている行はありません)');

// 3. 送る係の記録の時刻
$f3 = 'app/Console/cs_apns_sender.php';
$t3 = @file_get_contents($base . $f3);
if ($t3 === false) ng("$f3 がありません");
elseif (strpos($t3, "date_default_timezone_set('Asia/Tokyo')") !== false) say("\n○ $f3 はすでに日本時間です");
elseif (strpos($t3, "<?php\n") !== 0) ng("$f3 の先頭が想定と違います");
else { say("\n○ $f3:記録の時刻を日本時間にする(先頭に1行)"); $edits[$f3] = "<?php\ndate_default_timezone_set('Asia/Tokyo'); // $tag 記録の時刻を日本時間に(今までは中国時間で1時間ずれていた)\n" . substr($t3, 6); }

if (!$ok) { say("\n問題があるので止めます。何も変更していません。"); exit(1); }
if (!$edits) { say("\n直すところはありません。"); exit(0); }
if (!$apply) { say("\n確認だけ終わりました。問題がなければ、最後に apply を付けて実行してください。"); exit(0); }

// ===== 反映 =====
$bk = '/www/backup_customaid_push_dedupe_' . date('Ymd_His') . '/';
mkdir($bk, 0700, true);
foreach ($edits as $f => $t) copy($base . $f, $bk . basename($f));
say("控え:$bk");
foreach ($edits as $f => $t) {
    $st = stat($base . $f);
    file_put_contents($base . $f, $t);
    @chown($base . $f, $st['uid']); @chgrp($base . $f, $st['gid']);
    $o = []; exec($php . ' -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $rc);
    if ($rc !== 0) {
        foreach ($edits as $g => $u) copy($bk . basename($g), $base . $g);
        say("✕ $f の文法エラーのため、すべて元に戻しました: " . implode(' ', $o));
        exit(1);
    }
    say("○ 直しました:$f(文法 OK)");
}
say("\nこのあと php-fpm を読み直してください:/etc/init.d/php-fpm-73 reload");
say('確かめ方:Card-San App を閉じた iPhone に、管理App から予約を1件入れる → 「予約受付のお知らせ」が1件だけ届く');
say("元に戻すとき:\\cp {$bk}CsApns.php {$base}app/Libs/CsApns.php ; \\cp {$bk}GeTuiApi.php {$base}app/Libs/GeTuiApi.php ; \\cp {$bk}cs_apns_sender.php {$base}app/Console/cs_apns_sender.php ; /etc/init.d/php-fpm-73 reload");
