<?php
// [カスタムエイド 2026-10-26] 管理App「予約内容」で時間を変えて保存すると「通信に失敗しました」になる不具合を直す
//   原因(サーバーのエラー記録 2026-10-26 13:08〜):
//     予約を変えるとき、元の時間の「30分ごとの予約の数」(web_order_shop_log.order_count)を 1 減らしてから、新しい時間で 1 増やしている。
//     ところが、その数がすでに 0 の枠があると、0 から 1 を引けず(マイナスにできない欄)エラーで止まり、保存されなかった。
//     (10/29 13:30〜14:30 野崎はるかさんの予約:13:30 か 14:00 の枠の数が 0 になっていた)
//   修正:数を減らすのは、1 以上の枠だけにする(0 の枠はそのまま)。ほかの処理(予約・メッセージ)は変えない。
//   同じ書き方の「減らす」処理がほかの場所(取り消しなど)にもあれば、同じように直す。
// 使い方:
//   確認だけ:php -d disable_functions= /root/customaid_ordercount_patch.php
//   反映    :php -d disable_functions= /root/customaid_ordercount_patch.php apply
$apply = ($argv[1] ?? '') === 'apply';
$base = '/www/wwwroot/api.card-san.jp/';
$php = '/www/server/php/73/bin/php';
$tag = '[カスタムエイド 2026-10-26]';
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";

// 1. 「予約の数を減らす」処理を探す
$old = "->decrement('order_count');";
$new = "->where('order_count', '>', 0)->decrement('order_count'); // $tag 数が 0 の枠は減らさない(0 から引くとエラーで保存できなかった)";
$list = [];
exec('cd ' . $base . ' && grep -rl ' . escapeshellarg("decrement('order_count')") . ' app 2>/dev/null', $files);
foreach ($files as $f) {
    $t = file_get_contents($base . $f);
    $n = substr_count($t, $old);
    $done = substr_count($t, "->where('order_count', '>', 0)->decrement('order_count')");
    echo "○ $f:直す場所 {$n} か所" . ($done ? "(直し済み {$done} か所)" : '') . "\n";
    foreach (explode("\n", $t) as $i => $l) if (strpos($l, $old) !== false) echo '    ' . ($i + 1) . ': ' . trim($l) . "\n";
    if ($n) $list[$f] = $t;
}
if (!$list) exit("\n直す場所がありません(すでに直してあるか、書き方が違います)。何も変更していません。\n");

// 2. 参考:エラーになった予約の枠の数(10/29 13:30〜14:30・店舗 11・担当 70)
require_once $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo "\n-- 参考:10/29 13:00〜15:00 の担当 70 の枠の数(web_order_shop_log)\n";
foreach (Illuminate\Support\Facades\DB::table('web_order_shop_log')->where('sid', 11)->where('said', 70)
    ->where('start_time', '>=', 1793246400)->where('start_time', '<', 1793253600)->orderBy('start_time')->get() as $r) {
    echo '   ' . date('m/d H:i', $r->start_time) . ' 数=' . $r->order_count . "\n";
}
echo "-- 参考:同じ時間の予約(web_order)\n";
try {
    foreach (Illuminate\Support\Facades\DB::table('web_order')->where('sid', 11)->where('day_said', 70)
        ->where('start_time', '<', 1793253600)->where('end_time', '>', 1793246400)->get(['id', 'start_time', 'end_time']) as $r) {
        echo '   予約 id=' . $r->id . ' ' . date('m/d H:i', $r->start_time) . '〜' . date('H:i', $r->end_time) . "\n";
    }
} catch (\Throwable $e) { echo '   (読めませんでした)' . "\n"; }

if (!$apply) exit("\n確認だけ終わりました。問題がなければ、最後に apply を付けて実行してください。\n");

// ===== 反映 =====
$bk = '/www/backup_customaid_ordercount_' . date('Ymd_His') . '/';
mkdir($bk, 0700, true);
foreach ($list as $f => $t) {
    $dst = $bk . str_replace('/', '__', $f);
    copy($base . $f, $dst);
    file_put_contents($base . $f, str_replace($old, $new, $t));
    exec($php . ' -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $rc);
    if ($rc !== 0) {
        foreach ($list as $g => $u) { $b = $bk . str_replace('/', '__', $g); if (is_file($b)) copy($b, $base . $g); }
        exit("✕ 文法エラーのため、すべて元に戻しました: " . implode(' ', $o) . "\n");
    }
    echo "○ 直しました:$f\n";
}
echo "控え:$bk\n";
echo "このあと php-fpm を読み直してください:/etc/init.d/php-fpm-73 reload\n";
echo "確かめ方:管理App で 10/29 13:30 の予約の時間を短くして保存 →「保存しました」と出る\n";
echo "元に戻すとき:控えのファイルを元の場所に戻して php-fpm を読み直す(控えの名前の __ は / のこと)\n";
