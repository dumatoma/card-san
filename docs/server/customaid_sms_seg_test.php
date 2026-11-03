<?php
// [カスタムエイド 2026-10-28] SMS の改行が何文字で数えられるか(何通分の料金になるか)を確かめる。
//   php customaid_sms_seg_test.php look          → 今までの送信の記録から「何通分(Segments)」を見るだけ(送らない)。電話番号の数字は伏せる
//   php customaid_sms_seg_test.php send 09012345678 → いちばん長くなる形の試験の SMS を1通、その番号に送り、何通分だったかを出す
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b); $mode = $argv[1] ?? 'look';
$dir = $b . 'storage/logs/sms/'; $files = glob($dir . '*'); usort($files, function ($x, $y) { return filemtime($y) - filemtime($x); });
$tail = function ($n) use ($files) { if (!$files) { echo "送信の記録が見つかりません\n"; return; } $t = file_get_contents($files[0]);
  preg_match_all('/结果: (\{.*\})/', $t, $m); foreach (array_slice($m[1], -$n) as $j) { $r = json_decode($j, true);
  echo '  何通分(Segments)=' . ($r['Segments'] ?? '?') . '  結果=' . ($r['ResponseCode'] ?? '?') . '  宛先=' . preg_replace('/\d(?=\d{2})/', '*', (string) ($r['To'] ?? '')) . "\n"; } };
if ($mode === 'look') { echo "記録のファイル:" . ($files ? basename($files[0]) : 'なし') . "\n最近の送信:\n"; $tail(8); exit(0); }
$to = preg_replace('/[^0-9]/', '', $argv[2] ?? ''); if (strlen($to) < 10) { echo "送り先の電話番号を付けてください(例:php customaid_sms_seg_test.php send 09012345678)\n"; exit(1); }
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
// 坂本さんの形でいちばん長くなるとき:店舗名15文字(14文字+…)/お名前12文字+様+空白+「予約キャンセル」/[詳細]+リンク
$shop = 'CARD SALON表参道本…'; $name = '山田 花子(テスト送信)';
$msg = $shop . "\n" . $name . '様 予約キャンセル' . "\n" . '[詳細]https://r.card-san.jp/a1B2c3';
echo "送る文(改行を1文字で数えると " . mb_strlen($msg) . " 文字、2文字で数えると " . (mb_strlen($msg) + 2) . " 文字):\n$msg\n\n";
$r = (new App\Libs\AlibabaCloudSms())->send($to, $msg);
echo "送信:" . (!empty($r['success']) ? 'OK' : 'NG ' . ($r['message'] ?? '')) . "\n"; clearstatcache(); $files = glob($dir . '*'); usort($files, function ($x, $y) { return filemtime($y) - filemtime($x); });
echo "この送信の記録:\n"; $tail(1);
echo "\n何通分=1 なら改行は1文字(70文字で1通)。何通分=2 なら改行は2文字で数えられている(言葉を短くする必要あり)。\n";
