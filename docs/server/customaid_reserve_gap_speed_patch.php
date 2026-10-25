<?php
// [カスタムエイド 2026-10-24c] 予約WEB(order.card-san.jp/reserve)の予約日時選択 その2
//   1. お問い合わせボタンが予約表から離れている → すき間を今の半分くらいに
//      ・時間の列と予約枠の高さ:最後の時刻(営業の終わり)の文字が入るだけにする(1行分 80 → 32 に)
//      ・ボタンの上の余白:48 → 32
//   2. 週をスライドしたとき表示が少し遅い → 予約枠を出す前に、もともと 0.5秒 + 0.2秒 わざと待っていたのをやめる
//      (計算そのものは一瞬で終わる。待ち時間だけが無くなる。計算の中身は変えない)
//   今使われている予約画面のファイル(index.html → index の js に書かれた番号)を直し、新しい番号のファイルとして置く。
//   index の js の番号も書き換える。古いファイルは消さない(戻すときは index の js をバックアップから戻すだけ)
//   使い方: php customaid_reserve_gap_speed_patch.php        … 確認だけ(何も変更しない)
//           php customaid_reserve_gap_speed_patch.php apply  … 変更する(バックアップを取ってから)
$dir = '/www/wwwroot/order.card-san.jp/reserve/static/js/';
$backup = '/www/backup_customaid_reserve_gap_20261024c/';
$apply = (($argv[1] ?? '') === 'apply');

$html = @file_get_contents(dirname($dir, 2) . '/index.html');
if (!$html || !preg_match('/static\/js\/(index\.[0-9a-f]+\.js)/', $html, $hm)) exit("index.html から index の js が見つかりません。何も変更していません。\n");
$idx = $dir . $hm[1];
if (!is_file($idx) || !preg_match('/"pages-yuYue-yuYue":"([0-9a-f]{8})"/', file_get_contents($idx), $m)) exit("index の js に予約画面の番号が見つかりません。何も変更していません。\n");
$hash = $m[1];
$chunk = $dir . 'pages-yuYue-yuYue.' . $hash . '.js';
if (!is_file($chunk)) exit("予約画面のファイルがありません: $chunk\n何も変更していません。\n");
echo "index の js:" . basename($idx) . "\n今使われている予約画面:" . basename($chunk) . "\n";
$s = file_get_contents($chunk);
if (strpos($s, 'customaid-20261024c') !== false) exit("すでに直してあります。何も変更していません。\n");
if (strpos($s, 'customaid-20261024-week') === false) exit("前回の修正(隔週で真っ白)が入っていないファイルです。先に前回の修正を確認してください。何も変更していません。\n");

// ---- 1-1. 時間の列と予約枠の高さ ----
$re = '/"height:"\+80\*\(([A-Za-z_$][\w$]*)\.times\.length\+1\)\+"upx"/';
$n = preg_match_all($re, $s);
if ($n !== 2) exit("高さを決めている所が2か所ではありません({$n}か所)。何も変更していません。\n");
$s = preg_replace($re, '"height:"+(80*$1.times.length+32/*customaid-20261024c*/)+"upx"', $s);
echo "① 時間の列・予約枠の高さ:最後の行 80 → 32(2か所)\n";

// ---- 1-2. お問い合わせボタンの上の余白 ----
if (!preg_match('/\.btn1\[data-v-[0-9a-f]+\]\{margin:%\?48\?% /', $s, $bm) || preg_match_all('/\.btn1\[data-v-[0-9a-f]+\]\{margin:%\?48\?% /', $s) !== 1) exit("お問い合わせボタンの余白が見つかりません(1か所だけではありません)。何も変更していません。\n");
$s = str_replace($bm[0], str_replace('%?48?%', '%?32?%', $bm[0]), $s);
echo "② お問い合わせボタンの上の余白:48 → 32\n";

// ---- 2. わざと待っていた 0.5秒 + 0.2秒 をやめる ----
$re2 = '/(week_business=[A-Za-z_$][\w$]*\.wkbus\),)setTimeout\(\(function\(\)\{(for\(var [\s\S]*?)setTimeout\(\(function\(\)\{([A-Za-z_$][\w$]*)\.weel=([A-Za-z_$][\w$]*),\3\.totalheight=80\*\4\[0\]\.times\.length\}\),200\)\}\),500\)/';
$n2 = preg_match_all($re2, $s);
if ($n2 === 1) {
    $s = preg_replace($re2, '$1(function(){$2$3.weel=$4,$3.totalheight=80*$4[0].times.length})()', $s, 1);
    echo "③ 予約枠を出す前の待ち時間(0.5秒 + 0.2秒):やめました\n";
} else {
    echo "③ 待ち時間の所が見つからない({$n2}か所)ので、そのままにします(①②だけ直します)\n";
}

$new = substr(md5($s), 0, 8);
$newChunk = $dir . 'pages-yuYue-yuYue.' . $new . '.js';
$idxSrc = file_get_contents($idx);
if (substr_count($idxSrc, '"pages-yuYue-yuYue":"' . $hash . '"') !== 1) exit("index の js の番号が1つだけではありません。何も変更していません。\n");
$idxNew = str_replace('"pages-yuYue-yuYue":"' . $hash . '"', '"pages-yuYue-yuYue":"' . $new . '"', $idxSrc);
echo "新しいファイル:pages-yuYue-yuYue.$new.js\n";

if (!$apply) { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
if (!copy($idx, $backup . basename($idx)) || !copy($chunk, $backup . basename($chunk))) exit("バックアップに失敗しました。何も変更していません。\n");
if (file_put_contents($newChunk, $s) === false) exit("新しいファイルを書けません。何も変更していません。\n");
@chmod($newChunk, 0644);
if (file_put_contents($idx, $idxNew) === false) { @unlink($newChunk); exit("index の js を書き換えられません。元のままです。\n"); }
echo "完了しました。バックアップ:$backup\n";
echo "元に戻すとき:cp -p {$backup}" . basename($idx) . " {$idx}\n";
