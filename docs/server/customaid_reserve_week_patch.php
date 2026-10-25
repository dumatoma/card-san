<?php
// [カスタムエイド 2026-10-24] 予約WEB(order.card-san.jp/reserve)の予約日時選択
//   1. 予約枠が隔週で真っ白になる:営業時間の枠(sb)が0個・1個の日に sb[0]・sb[1] を読んでエラーで止まっていた。
//      計算の前に、中身が空の枠(00:00〜00:00 = どの時刻も含まない)を足して必ず2個にする(表示の結果は今までと同じ)
//   2. お問い合わせボタンが離れている:予約枠の高さを時間の列と同じにし、ボタンをすぐ下に(管理App と同じ見た目)
//   今使われている予約画面のファイル(index の js に書かれた番号)を探し、直したものを新しい番号のファイルとして置く。
//   index の js の番号も書き換える。古いファイルは消さない(戻すときは index の js をバックアップから戻すだけ)
//   使い方: php customaid_reserve_week_patch.php        … 確認だけ(何も変更しない)
//           php customaid_reserve_week_patch.php apply  … 変更する(バックアップを取ってから)
$dir = '/www/wwwroot/order.card-san.jp/reserve/static/js/';
$backup = '/www/backup_customaid_reserve_week_20261024b/';
$first = '/www/backup_customaid_reserve_week_20261024/'; // 1回目のバックアップ(使われていない古いものを直した分)
$apply = (($argv[1] ?? '') === 'apply');

// ---- 今使われている予約画面のファイル(入口の index.html が読み込んでいる index の js から探す)----
// [2026-10-24b] 1回目は index の js を名前の順に探したため、使われていない古いもの(index.380e5f92.js)を直してしまった。
//   その変更は下で元に戻してから、本当に使われているものを直す
$html = @file_get_contents(dirname($dir, 2) . '/index.html');
if (!$html || !preg_match('/static\/js\/(index\.[0-9a-f]+\.js)/', $html, $hm)) exit("index.html から index の js が見つかりません。何も変更していません。\n");
$idx = $dir . $hm[1]; $hash = null;
if (is_file($idx) && preg_match('/"pages-yuYue-yuYue":"([0-9a-f]{8})"/', file_get_contents($idx), $m)) $hash = $m[1];
else $idx = null;
if (!$idx) exit("index の js に予約画面の番号が見つかりません。何も変更していません。\n");
$chunk = $dir . 'pages-yuYue-yuYue.' . $hash . '.js';
if (!is_file($chunk)) exit("予約画面のファイルがありません: $chunk\n何も変更していません。\n");
echo "index の js:" . basename($idx) . "\n今使われている予約画面:" . basename($chunk) . "\n";
$s = file_get_contents($chunk);
if (strpos($s, 'customaid-20261024-week') !== false) exit("すでに直してあります。何も変更していません。\n");

// ---- 1. 営業時間の枠を必ず2個に ----
$re = '/([A-Za-z_$][\w$]*)\[([A-Za-z_$][\w$]*)\]\.sb=([A-Za-z_$][\w$]*)(?=[,;)])/';
$n = preg_match_all($re, $s, $mm);
if ($n !== 1) exit("営業時間の枠を入れる所が1つだけではありません({$n}か所)。何も変更していません。\n");
$s = preg_replace_callback($re, function ($m) {
    return $m[1] . '[' . $m[2] . '].sb=(function(q){/*customaid-20261024-week*/q=Array.isArray(q)?q.slice():[];while(q.length<2)q.push({start:"00:00",end:"00:00"});return q})(' . $m[3] . ')';
}, $s, 1);
echo "① 営業時間の枠:" . $mm[0][0] . " → 必ず2個にしてから計算\n";

// ---- 2-1. 予約枠の高さを時間の列と同じに ----
if (preg_match('/staticClass:"cs-timecol",style:([^{}]+?)\}/', $s, $tm) && substr_count($s, 'staticClass:"cs-pager cs-grid-pager",attrs:{id:"csGridPager"}') === 1) {
    $s = str_replace('staticClass:"cs-pager cs-grid-pager",attrs:{id:"csGridPager"}', 'staticClass:"cs-pager cs-grid-pager",style:' . $tm[1] . ',attrs:{id:"csGridPager"}', $s);
    echo "② 予約枠の高さ:時間の列と同じ(" . $tm[1] . ")\n";
} else {
    echo "② 予約枠の高さ:直す所が見つからないので、そのままにします(お問い合わせの位置だけ直します)\n";
}

// ---- 2-2. お問い合わせボタン ----
if (!preg_match('/\.btn1\[(data-v-[0-9a-f]+)\]\{[^}]*\}/', $s, $bm) || preg_match_all('/\.btn1\[data-v-[0-9a-f]+\]\{[^}]*\}/', $s) !== 1) exit("お問い合わせボタンの見た目が1つだけではありません。何も変更していません。\n");
$s = str_replace($bm[0], '.btn1[' . $bm[1] . ']{margin:%?48?% %?40?% calc(%?64?% + env(safe-area-inset-bottom));height:%?104?%;background:#1a73e8;border-radius:%?28?%;font-size:%?32?%;font-weight:700;color:#fff;display:flex;align-items:center;justify-content:center}', $s);
echo "③ お問い合わせボタン:予約枠のすぐ下・管理App と同じ見た目\n";

// ---- 新しい番号 ----
$new = substr(md5($s), 0, 8);
$newChunk = $dir . 'pages-yuYue-yuYue.' . $new . '.js';
$idxSrc = file_get_contents($idx);
if (substr_count($idxSrc, '"pages-yuYue-yuYue":"' . $hash . '"') !== 1) exit("index の js の番号が1つだけではありません。何も変更していません。\n");
$idxNew = str_replace('"pages-yuYue-yuYue":"' . $hash . '"', '"pages-yuYue-yuYue":"' . $new . '"', $idxSrc);
echo "新しいファイル:pages-yuYue-yuYue.$new.js\n";

if (!$apply) { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
// 1回目に直した、使われていない古い index の js を元に戻す
foreach (glob($first . 'index.*.js') ?: [] as $b) {
    $to = $dir . basename($b);
    if (realpath($to) !== realpath($idx) && is_file($to)) { copy($b, $to); echo "1回目に直した " . basename($b) . " を元に戻しました(使われていないファイル)\n"; }
}
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
if (!copy($idx, $backup . basename($idx)) || !copy($chunk, $backup . basename($chunk))) exit("バックアップに失敗しました。何も変更していません。\n");
if (file_put_contents($newChunk, $s) === false) exit("新しいファイルを書けません。何も変更していません。\n");
@chmod($newChunk, 0644);
if (file_put_contents($idx, $idxNew) === false) { @unlink($newChunk); exit("index の js を書き換えられません。元のままです。\n"); }
echo "完了しました。バックアップ:$backup\n";
echo "元に戻すとき:cp -p {$backup}" . basename($idx) . " {$idx}\n";
