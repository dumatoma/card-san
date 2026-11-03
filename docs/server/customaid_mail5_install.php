<?php
// [カスタムエイド 2026-10-29] 段階5:予約メールの部品を入れる。/root/cs_mail5/ に置いた2つのファイルを入れ、SMS のページの見た目をメールにそろえる
//   (カードの絵 200→180・マーク 56→44・ボタンの上の間を2倍)。お客様にはまだメールは届かない(送り先は段階6で入れる)
//   使い方: php -d disable_functions= install5.php → 確認だけ / ... apply → 控えを取ってから入れる / ... test 送り先 → 予約409(店舗11)の4種類を送る
$b = '/www/wwwroot/api.card-san.jp/'; $src = '/root/cs_mail5/'; chdir($b); $mode = $argv[1] ?? 'check';
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if ($mode === 'test') {
    $to = trim($argv[2] ?? ''); if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { echo "使い方:php -d disable_functions= install5.php test 送り先のメールアドレス\n"; exit(1); }
    $o = App\Models\OrderModel::find(409); if (!$o || (int) $o->sid !== 11) { echo "予約409(店舗11)が見つかりません\n"; exit(1); }
    echo "送り手:" . App\Libs\CsReserveMail::from() . "(" . env('CS_RESERVE_MAIL_HOST') . ")\n";
    foreach (['confirm', 'notice', 'update', 'cancel'] as $t) { $r = App\Libs\CsReserveMail::send($t, $o, $to, $o->name); echo str_pad($t, 8) . ':' . ($r ? '送りました' : 'NG(laravel.log を見る)') . "\n"; }
    exit(0);
}
$files = ['CsReserveMail.php' => 'app/Libs/CsReserveMail.php', 'cs_reserve_mail.blade.php' => 'resources/views/cs_reserve_mail.blade.php'];
$V = 'resources/views/cs_sms_page.blade.php';
$edits = [ // [名前, 今の文字, 新しい文字]
  ['マーク 56→44', '.icon{width:56px;height:56px;border-radius:50%;display:grid;place-items:center;font-size:28px;', '.icon{width:44px;height:44px;border-radius:50%;display:grid;place-items:center;font-size:22px;'],
  ['カードの絵 200→180', '.scard{display:block;width:200px;', '.scard{display:block;width:180px;'],
  ['ボタンの上の間', '.btns{display:grid;gap:10px}', '.btns{display:grid;gap:10px;margin-top:16px}'],
];
$ng = 0;
foreach ($files as $s => $t) { if (!is_file($src . $s)) { echo "NG 置く前のファイルがない: $src$s\n"; $ng++; } elseif (is_file($b . $t)) { echo "NG もうある: $t\n"; $ng++; } else echo "OK 入れる: $t(md5 " . md5_file($src . $s) . ")\n"; }
$vs = file_get_contents($b . $V);
foreach ($edits as $e) { $n = substr_count($vs, $e[1]); echo ($n === 1 ? 'OK ' : 'NG ') . "SMS のページ $e[0]:見つかった数 $n\n"; if ($n !== 1) $ng++; else $vs = str_replace($e[1], $e[2], $vs); }
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if ($mode !== 'apply') { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_mail5_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($b . $V, $bk . basename($V));
$undo = function () use ($b, $bk, $V, $files) { copy($bk . basename($V), $b . $V); foreach ($files as $t) @unlink($b . $t); };
foreach ($files as $s => $t) { copy($src . $s, $b . $t); @chown($b . $t, 'www'); @chgrp($b . $t, 'www'); }
file_put_contents($b . $V, $vs);
$bad = 0; exec('php -l ' . escapeshellarg($b . 'app/Libs/CsReserveMail.php') . ' 2>&1', $o, $r); echo ($r === 0 ? '文法OK ' : '文法NG ') . "app/Libs/CsReserveMail.php\n"; if ($r !== 0) $bad++;
if (!$bad) try {
  $rows = [['予約番号', 'UU0000000000000'], ['日時', '10月30日(金) 17:00 〜 18:00'], ['メニュー', 'カット', '9,000円', 'https://example.com/a.jpg'], ['担当者', '指名なし']];
  foreach (['confirm', 'notice', 'update', 'cancel'] as $ty) { $p = ['type' => $ty, 'heading' => '【見出し】', 'name' => '見本 花子', 'honor' => '様', 'top' => "本文上\n2行目", 'mid' => $ty == 'update' ? '本文中' : '', 'bottom' => '本文下', 'rows' => $rows, 'card' => 'https://example.com/c.jpg', 'shop' => ['sid' => 11, 'name' => 'CARD SALON', 'phone' => '03-0000-0000', 'address' => '東京都'], 'start_time' => time()];
    $h = view('cs_reserve_mail', ['p' => $p, 'url' => 'https://r.card-san.jp/a1B2c3'])->render(); $t = App\Libs\CsReserveMail::text($p, 'https://r.card-san.jp/a1B2c3');
    $h2 = view('cs_sms_page', ['p' => $p, 'state' => 'ok'])->render();
    $okh = strpos($h, 'width="180"') !== false && strpos($t, '予約番号：') !== false && strpos($h2, 'width:180px') !== false;
    echo ($okh ? 'OK ' : 'NG ') . "$ty(メール " . strlen($h) . " バイト・文字だけ " . mb_strlen($t) . " 文字・ページ)\n"; if (!$okh) $bad++; }
} catch (\Throwable $e) { echo "NG " . $e->getMessage() . "\n"; $bad++; }
exec('chown -R www:www ' . escapeshellarg($b . 'storage/framework/views'));
if ($bad) { $undo(); echo "\nうまくいかなかったので、元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "\n入れました。控え: $bk\n";
