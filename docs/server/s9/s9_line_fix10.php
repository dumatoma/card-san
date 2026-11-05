<?php
// [カスタムエイド 2026-11-05] 段階9 追記10:LINE の鍵の「保存して確かめる」で、同じチャネル ID・同じシークレットが2か所に入っているときの文を分かりやすくする(2つの欄を両方赤く・それぞれの欄に何を入れるか)
//   php -d disable_functions= /root/s9_line_fix10.php        … 確かめるだけ
//   php -d disable_functions= /root/s9_line_fix10.php apply  … バックアップを取ってから変更する
//   php -d disable_functions= /root/s9_line_fix10.php undo   … 元に戻す
$pairs = [
    ['        if ($k[\'msg_id\'] === $k[\'login_id\']) return [\'ok\' => false, \'msg\' => \'つなげませんでした\', \'errors\' => [\'login_id\' => \'2つのチャネル ID が同じです。「LINEログイン」の印があるチャネルの ID を入れてください\']];
        if ($k[\'msg_secret\'] === $k[\'login_secret\']) return [\'ok\' => false, \'msg\' => \'つなげませんでした\', \'errors\' => [\'login_secret\' => \'2つのシークレットが同じです。「LINEログイン」の印があるチャネルのシークレットを入れてください\']];
',
     '        // [カスタムエイド 2026-11-05 追記10] 同じものが2か所に入っているとき:2つの欄を両方赤くして、それぞれの欄に何を入れるかを書く
        $same = [];
        if ($k[\'msg_id\'] === $k[\'login_id\']) {
            $same[\'msg_id\'] = \'下の「LINE ログインのチャネル」のチャネル ID と同じ番号です。ここには、緑の「Messaging API」の印があるチャネルのチャネル ID を入れてください\';
            $same[\'login_id\'] = \'上の「Messaging API のチャネル」のチャネル ID と同じ番号です。ここには、「LINEログイン」の印があるチャネルのチャネル ID を入れてください\';
        }
        if ($k[\'msg_secret\'] === $k[\'login_secret\']) {
            $same[\'msg_secret\'] = \'下の「LINE ログインのチャネル」のチャネルシークレットと同じです。ここには、緑の「Messaging API」の印があるチャネルのチャネルシークレットを入れてください\';
            $same[\'login_secret\'] = \'上の「Messaging API のチャネル」のチャネルシークレットと同じです。ここには、「LINEログイン」の印があるチャネルのチャネルシークレットを入れてください\';
        }
        if ($same) return [\'ok\' => false, \'msg\' => \'つなげませんでした。同じものが2か所に入っています\', \'errors\' => $same];
']
];
$base = '/www/wwwroot/api.card-san.jp/';
$f = 'app/Libs/CsLine.php';
$backup = '/www/backup_customaid_s9fix10_20261105/';
$bk = $backup . 'CsLine.php';
$mode = $argv[1] ?? 'check';
if ($mode === 'undo') { if (!is_file($bk)) exit("バックアップがありません。何もしていません。\n"); copy($bk, $base . $f); exit("元に戻しました: {$f}\n"); }
$s = file_get_contents($base . $f);
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
if (strpos($s, '追記10') !== false) $ng('すでに修正済みです');
foreach ($pairs as $i => $p) { $n = substr_count($s, $p[0]); if ($n !== 1) $ng('CsLine.php の直す場所 ' . ($i + 1) . ' が見つかりません(見つかった数 ' . $n . ')'); $s = str_replace($p[0], $p[1], $s); }
$tmp = sys_get_temp_dir() . '/cs_s9f10.php'; file_put_contents($tmp, $s); $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c); @unlink($tmp);
echo ($c === 0 ? 'OK' : 'NG') . " 直したあとの文法\n"; if ($c !== 0) $ng(implode(' ', $o));
echo "今の CsLine.php の md5:" . md5_file($base . $f) . "\n";
if ($mode !== 'apply') exit("\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n");
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
copy($base . $f, $bk);
file_put_contents($base . $f, $s);
$o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c);
if ($c !== 0) { copy($bk, $base . $f); exit("文法エラーがあったので元に戻しました。\n"); }
echo "完了しました。バックアップ: {$backup}\n";
