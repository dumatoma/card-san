<?php
// [カスタムエイド 2026-11-07] GBP 連携 その7b(サーバー):メニューを出せるかの判定を正しくする
//   Google は「サービスを変えられない(false)」ときに、その項目を返事に入れないことがある(false は省かれる)。
//   今は「返事なし」を「出せる」と扱っているので、「出せる(true)と返事があったときだけ」出すように直す
//
//   php -d disable_functions= /root/gbp_fix7b.php          … 確かめるだけ(何も変えない)
//   php -d disable_functions= /root/gbp_fix7b.php apply    … バックアップを取ってから変える
//   php -d disable_functions= /root/gbp_fix7b.php undo     … 元に戻す

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_gbpfix7b_20261107/';
$mode = $argv[1] ?? 'check';
$MARK = 'カスタムエイド 2026-11-07 GBP7b';
$f = 'app/Services/GbpService.php';
$bk = $backup . 'GbpService.php';
if ($mode === 'undo') {
    if (!is_file($bk)) exit("バックアップがありません。何もしていません。\n");
    copy($bk, $base . $f);
    @exec('/etc/init.d/php-fpm-73 reload 2>&1');
    exit("元に戻しました: {$f}\n");
}
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
$s = file_get_contents($base . $f);
if (strpos($s, $MARK) !== false) $ng('すでに直してあります');
if (strpos($s, 'カスタムエイド 2026-11-07 GBP7]') === false) $ng('先に gbp_fix7.php を apply してください');
$old = "        if (array_key_exists('canModifyServiceList', \$md) && !\$md['canModifyServiceList']) return";
$new = "        if (empty(\$md['canModifyServiceList'])) return /* [{$MARK}] 「出せる」と返事があったときだけ出す */";
$n = substr_count($s, $old);
if ($n !== 1) $ng("直す場所が見つかりません(見つかった数 {$n})");
$s = str_replace($old, $new, $s);
$old2 = "'can' => \$md['canModifyServiceList'] ?? '(Google から返事なし)'";
$new2 = "'can' => !empty(\$md['canModifyServiceList'])";
if (substr_count($s, $old2) !== 1) $ng('直す場所 2 が見つかりません');
$s = str_replace($old2, $new2, $s);

$tmp = sys_get_temp_dir() . '/cs_gbpfix7b.php';
file_put_contents($tmp, $s);
$o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c); @unlink($tmp);
echo ($c === 0 ? 'OK' : 'NG') . " 直したあとの文法\n";
if ($c !== 0) $ng(implode(' ', $o));
echo "OK 直す場所はすべて見つかりました(2 か所)\n";
if ($mode !== 'apply') exit("\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n");

if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
copy($base . $f, $bk);
file_put_contents($base . $f, $s);
$o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c);
if ($c !== 0) { copy($bk, $base . $f); exit("文法エラーがあったので元に戻しました。\n"); }
@exec('/etc/init.d/php-fpm-73 reload 2>&1');
echo "完了しました。バックアップ: {$backup}\n";
echo "続けて、もう一度見るだけの確認をしてください:\n php -d disable_functions= /root/gbp_fix7.php test 11\n";
