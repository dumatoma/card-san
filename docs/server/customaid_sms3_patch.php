<?php
// [カスタムエイド 2026-10-29] 予約の SMS(予約受付・明日のご予約・ご予約変更・予約キャンセル)を3行の短い文にする(決定事項 11・12・14)
//   1行目 店舗名(15文字まで)/2行目 お名前(10文字まで)様 種類/3行目 [詳細]https://r.card-san.jp/英数字6文字
//   ページを作れなかったときは今までの長い文で送る。.env に CS_SMS_SHORT=0 と書くと今までの長い文に戻せる
//   あわせて SMS の記録に、Alibaba の返事(何通分 segments など)を残す(電話番号は残さない)
//   使い方: php ... → 確認だけ / php ... apply → 控えを取ってから変える / php ... test 電話番号 → 予約409(店舗11)の予約受付の SMS を1通送る
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b); $mode = $argv[1] ?? 'check'; $T = '[カスタムエイド 2026-10-29]';
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if ($mode === 'test') {
    $to = preg_replace('/[^0-9]/', '', $argv[2] ?? ''); if (strlen($to) < 10) { echo "使い方:php customaid_sms3_patch.php test 電話番号\n"; exit(1); }
    $o = App\Models\OrderModel::find(409); if (!$o || (int) $o->sid !== 11) { echo "予約409(店舗11)が見つかりません\n"; exit(1); }
    $msg = App\Libs\CsSmsText::build('confirm', $o); $crlf = preg_replace("/\r?\n/", "\r\n", $msg);
    echo "---- 送る文 ----\n$msg\n---- " . mb_strlen($crlf) . " 文字(改行は2文字で数える)----\n";
    $r = (new App\Libs\AlibabaCloudSms())->send($to, $msg); echo "結果:" . ($r['success'] ? 'OK' : 'NG ' . $r['message']) . "\n";
    $f = $b . 'storage/logs/sms/' . date('Y-m-d') . '.log'; echo "記録の最後:" . trim(substr((string) @file_get_contents($f), -300)) . "\n"; exit(0);
}
$S = 'app/Libs/CsSmsText.php'; $A = 'app/Libs/AlibabaCloudSms.php';
$oldBuild = "    public static function build(\$type, \$order)\n    {\n        return self::text(\$type, \$order, trim((string) (\$order->name ?? '')), true);\n    }\n";
$newBuild = "    // $T 予約の4種類は3行の短い文(店舗名/お名前様 種類/[詳細]ページのリンク)。ページを作れなければ今までの長い文。\n    //   .env の CS_SMS_SHORT=0 で今までの長い文に戻せる\n    public static function build(\$type, \$order)\n    {\n        \$name = trim((string) (\$order->name ?? ''));\n        if (isset(CsSmsPage::LABEL[\$type]) && (string) env('CS_SMS_SHORT', '1') !== '0') {\n            \$code = CsSmsPage::make(\$type, \$order, \$name);\n            if (\$code) return self::short(\$type, \$order, \$name, \$code);\n        }\n        return self::text(\$type, \$order, \$name, true);\n    }\n\n    // $T 3行の短い文。店舗名は15文字・お名前は10文字まで(超えたら最後の1文字を「…」)。いちばん長くても改行込みで70文字=1通\n    public static function short(\$type, \$order, \$name, \$code)\n    {\n        \$cut = function (\$s, \$n) { \$s = trim(preg_replace('/\\s+/u', ' ', (string) \$s)); return mb_strlen(\$s) > \$n ? mb_substr(\$s, 0, \$n - 1) . '…' : \$s; };\n        \$l2 = (\$name !== '' ? \$cut(\$name, 10) . '様 ' : '') . CsSmsPage::LABEL[\$type];\n        return implode(\"\\n\", array_filter([\$cut(\$order->shop->shop_name ?? '', 15), \$l2, '[詳細]' . CsSmsPage::base() . \$code], 'strlen'));\n    }\n";
$oldLog = "        \$log_str .= \"\\r\\n\" . '结果: ' . json_encode(\$result);\n";
$newLog = "        // $T 返事の中身を残す(以前は json_encode(\$result) で {} しか残らなかった)。何通分(segments)・文字数。電話番号は残さない\n        \$ra = method_exists(\$result, 'toArray') ? \$result->toArray() : [];\n        \$log_str .= \"\\r\\n\" . '结果: ' . json_encode(['code' => \$ra['ResponseCode'] ?? '', 'desc' => \$ra['ResponseDescription'] ?? '', 'segments' => \$ra['Segments'] ?? '', 'id' => \$ra['MessageId'] ?? '', 'chars' => mb_strlen(\$message)], JSON_UNESCAPED_UNICODE);\n";
$jobs = [[$S, '短い文にする(build)', $oldBuild, $newBuild], [$A, 'SMS の記録', $oldLog, $newLog]];
$out = []; $ng = 0;
foreach ($jobs as $j) { list($f, $name, $old, $new) = $j; $out[$f] = file_get_contents($b . $f);
    $n = substr_count($out[$f], $old); echo ($n === 1 ? 'OK ' : 'NG ') . "$name($f):見つかった数 $n\n"; if ($n !== 1) { $ng++; continue; }
    $out[$f] = str_replace($old, $new, $out[$f]); }
if (!method_exists('App\Libs\CsSmsPage', 'card')) { echo "NG SMS のページの部品が前の形のまま\n"; $ng++; }
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if ($mode !== 'apply') { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_sms3_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
foreach ($out as $f => $s) { copy($b . $f, $bk . basename($f)); file_put_contents($b . $f, $s); }
$undo = function () use ($b, $bk, $out) { foreach ($out as $f => $s) copy($bk . basename($f), $b . $f); };
$bad = 0; foreach ($out as $f => $s) { exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r); echo ($r === 0 ? '文法OK ' : '文法NG ') . "$f\n"; $o = []; if ($r !== 0) $bad++; }
if ($bad) { $undo(); echo "\n文法の誤りがあったので、元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "\n== 文の見本(予約409・店舗11。SMS は送らない)==\n";
$res = trim(shell_exec('php -d disable_functions= -r ' . escapeshellarg('chdir("' . $b . '"); require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); $o = App\Models\OrderModel::find(409); foreach (["confirm","notice","update","cancel"] as $t) { $m = App\Libs\CsSmsText::build($t, $o); echo $m . "\n(" . mb_strlen(preg_replace("/\r?\n/", "\r\n", $m)) . " 文字)\n\n"; } $x = new stdClass; $x->shop = (object) ["shop_name" => "ヘアサロン表参道本店スペシャルライン"]; echo App\Libs\CsSmsText::short("cancel", $x, "山田 花子テスト送信用", "a1B2c3") . "\n(長い名前の見本)\n";') . ' 2>&1'));
echo $res . "\n";
if (strpos($res, '[詳細]') === false) { $undo(); echo "\n見本を作れなかったので、元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "\n変えました。控え: $bk\n";
