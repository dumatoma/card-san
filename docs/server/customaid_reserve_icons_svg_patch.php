<?php
// [カスタムエイド 2026-10-24d] 予約WEB(order.card-san.jp/reserve)の予約表の「○」と「×」を SVG にする
//   ○(予約できる枠):小さな png だったので、拡大するとぼやけていた → SVG にし、色を少し濃く(#93DDDC → #7FBDBA)
//   ×(予約済みの枠):SVG だが中身が「文字の×」で、端末のフォントで形が変わる → 線で描いた SVG に(色 #86868B はそのまま)
//   ○は予約画面のファイル(pages-yuYue-yuYue.番号.js)の中に埋め込まれているので、そこを差し替える。
//   ×は新しいファイル static/img/noTime.customaid1024.svg を置き、予約画面のファイルの参照先をそれに変える(古いファイルは残す)
//   新しい番号の予約画面ファイルとして置き、index の js の番号を書き換える。古いファイルは消さない
//   使い方: php customaid_reserve_icons_svg_patch.php        … 確認だけ(何も変更しない)
//           php customaid_reserve_icons_svg_patch.php apply  … 変更する(バックアップを取ってから)
$dir = '/www/wwwroot/order.card-san.jp/reserve/static/js/';
$imgDir = dirname($dir) . '/img/';
$backup = '/www/backup_customaid_reserve_icons_20261024d/';
$apply = (($argv[1] ?? '') === 'apply');
$oldRing = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDYuMC1jMDA2IDc5LmRhYmFjYmIsIDIwMjEvMDQvMTQtMDA6Mzk6NDQgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcD0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLyIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCAyMi40IChNYWNpbnRvc2gpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkQ4M0VDN0U1MkIyOTExRUQ4Q0RGQkU5QTVBODYxNkE5IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkQ4M0VDN0U2MkIyOTExRUQ4Q0RGQkU5QTVBODYxNkE5Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6RDgzRUM3RTMyQjI5MTFFRDhDREZCRTlBNUE4NjE2QTkiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6RDgzRUM3RTQyQjI5MTFFRDhDREZCRTlBNUE4NjE2QTkiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6jtgZDAAAGgklEQVR42tSa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';
$newRing = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0OCA0OCIgd2lkdGg9IjQ4IiBoZWlnaHQ9IjQ4Ij48ZyBmaWxsPSJub25lIiBzdHJva2U9IiM3RkJEQkEiIHN0cm9rZS13aWR0aD0iMiI+PGNpcmNsZSBjeD0iMjQiIGN5PSIyNCIgcj0iMjMiLz48Y2lyY2xlIGN4PSIyNCIgY3k9IjI0IiByPSIxNyIvPjwvZz48L3N2Zz4K';
$oldNo = 'static/img/noTime.0d5237d6.svg';
$newNo = 'static/img/noTime.customaid1024.svg';
$noSvg = base64_decode('PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgd2lkdGg9IjI0IiBoZWlnaHQ9IjI0Ij48cGF0aCBkPSJNNy44IDcuOGw4LjQgOC40TTE2LjIgNy44bC04LjQgOC40IiBmaWxsPSJub25lIiBzdHJva2U9IiM4Njg2OEIiIHN0cm9rZS13aWR0aD0iMS4zIiBzdHJva2UtbGluZWNhcD0icm91bmQiLz48L3N2Zz4K');

$html = @file_get_contents(dirname($dir, 2) . '/index.html');
if (!$html || !preg_match('/static\/js\/(index\.[0-9a-f]+\.js)/', $html, $hm)) exit("index.html から index の js が見つかりません。何も変更していません。\n");
$idx = $dir . $hm[1];
if (!is_file($idx) || !preg_match('/"pages-yuYue-yuYue":"([0-9a-f]{8})"/', file_get_contents($idx), $m)) exit("index の js に予約画面の番号が見つかりません。何も変更していません。\n");
$hash = $m[1];
$chunk = $dir . 'pages-yuYue-yuYue.' . $hash . '.js';
if (!is_file($chunk)) exit("予約画面のファイルがありません: $chunk\n何も変更していません。\n");
echo "index の js:" . basename($idx) . "\n今使われている予約画面:" . basename($chunk) . "\n";
$s = file_get_contents($chunk);
if (strpos($s, $newRing) !== false) exit("すでに直してあります。何も変更していません。\n");
$n = substr_count($s, $oldRing);
if ($n !== 1) exit("○の画像が1か所だけではありません({$n}か所)。何も変更していません。\n");
$s = str_replace($oldRing, $newRing, $s);
echo "① ○:png → SVG・色 #7FBDBA\n";
$n2 = substr_count($s, $oldNo);
if ($n2 === 1) { $s = str_replace($oldNo, $newNo, $s); echo "② ×:線で描いた SVG(" . basename($newNo) . ")に\n"; }
else { $newNo = null; echo "② ×:参照先が見つからない({$n2}か所)ので、そのままにします(○だけ直します)\n"; }

$newHash = substr(md5($s), 0, 8);
$newChunk = $dir . 'pages-yuYue-yuYue.' . $newHash . '.js';
$idxSrc = file_get_contents($idx);
if (substr_count($idxSrc, '"pages-yuYue-yuYue":"' . $hash . '"') !== 1) exit("index の js の番号が1つだけではありません。何も変更していません。\n");
$idxNew = str_replace('"pages-yuYue-yuYue":"' . $hash . '"', '"pages-yuYue-yuYue":"' . $newHash . '"', $idxSrc);
echo "新しいファイル:pages-yuYue-yuYue.$newHash.js\n";

if (!$apply) { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
if (!copy($idx, $backup . basename($idx)) || !copy($chunk, $backup . basename($chunk))) exit("バックアップに失敗しました。何も変更していません。\n");
if ($newNo) {
    if (!is_dir($imgDir) || file_put_contents($imgDir . basename($newNo), $noSvg) === false) exit("×の画像を置けません。何も変更していません。\n");
    @chmod($imgDir . basename($newNo), 0644);
}
if (file_put_contents($newChunk, $s) === false) exit("新しいファイルを書けません。何も変更していません。\n");
@chmod($newChunk, 0644);
if (file_put_contents($idx, $idxNew) === false) { @unlink($newChunk); exit("index の js を書き換えられません。元のままです。\n"); }
echo "完了しました。バックアップ:$backup\n";
echo "元に戻すとき:cp -p {$backup}" . basename($idx) . " {$idx}\n";
