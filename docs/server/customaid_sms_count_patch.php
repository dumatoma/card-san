<?php
// [カスタムエイド 2026-10-28] お店の SMS(予約受付・変更・キャンセル・リマインド)を送ったら、必ず通数を減らす。
//   今までは、お客様がウェブで予約したときの1か所だけ数えていた。決まりは今までと同じ(月の通数→0なら追加分。追加分はマイナスになってよい)
//   使い方: php customaid_sms_count_patch.php        → 確認だけ(何も変えない)
//           php customaid_sms_count_patch.php apply  → 控えを取ってから変える
$b = '/www/wwwroot/api.card-san.jp/'; $apply = ($argv[1] ?? '') === 'apply'; $C = '// [カスタムエイド 2026-10-28] SMS を送ったら通数を数える';
$S = 'app/Http/Controllers/Api/Shop/OrderController.php'; $J = 'app/Jobs/OrderNoticeJob.php'; $send = '(new AlibabaCloudSms())->send($order->phone, $message);';
$use = function ($ind) use ($C) { return "\n" . $ind . '\App\Libs\CsSmsCount::use($order->sid); ' . $C; };
$q = function ($s) { return preg_quote($s, '/'); };
$jobs = [ // [ファイル, 場所の名前, 正規表現(1つ目=送る行、2つ目=すぐ後の目印), 字下げ]
  [$S, 'お店が作った予約', '/(\n {20}' . $q($send) . ')(\s*\n {16}\}\s*\n {12}\}\s*\n\s*\n {12}DB::commit\(\);\s*\n {8}\/\/ \} catch)/', str_repeat(' ', 20)],
  [$S, '変更', '/(\n {12}' . $q($send) . ')(\s*\n {8}\}\s*\n\s*\n {8}return responseToJson\(200, \'送信しました\'\);)/', str_repeat(' ', 12)],
  [$S, 'キャンセル', '/(\n {20}' . $q($send) . ')(\s*\n {16}\}\s*\n {12}\}\s*\n\s*\n {12}if\(\$order->notice\)\{)/', str_repeat(' ', 20)],
  [$J, 'リマインド', '/(\n {12}' . $q($send) . ')(\s*\n {8}\}\s*\n {4}\}\s*\n\}\s*$)/', str_repeat(' ', 12)],
];
$lib = $b . 'app/Libs/CsSmsCount.php';
$libSrc = "<?php\n\nnamespace App\\Libs;\n\n// [カスタムエイド 2026-10-28] お店の SMS を1通送ったら、通数を1つ減らす。決まりは今までの予約受付と同じ:\n//   月の通数(vip_sms_month_count)が残っていればそれを減らし、0なら追加分(vip_sms_count)を減らす。追加分はマイナスになってよい(毎日 0:05 に注意の印が付く)\n//   数えるのに失敗しても、SMS や予約の保存は止めない(記録だけ残す)\nclass CsSmsCount\n{\n    public static function use(\$sid)\n    {\n        try {\n            \$shop = \\App\\Models\\ShopModel::find((int) \$sid);\n            if (!\$shop) return;\n            if ((int) \$shop->vip_sms_month_count > 0) {\n                \$shop->decrement('vip_sms_month_count');\n            } else {\n                \$shop->decrement('vip_sms_count');\n            }\n        } catch (\\Throwable \$e) {\n            \\Illuminate\\Support\\Facades\\Log::error('CsSmsCount sid=' . \$sid . ' ' . \$e->getMessage());\n        }\n    }\n}\n";
$out = []; $ng = 0;
foreach ([$S, $J] as $f) $out[$f] = file_get_contents($b . $f);
foreach ($jobs as $j) { list($f, $name, $re, $ind) = $j;
    $n = preg_match_all($re, $out[$f]);
    echo ($n === 1 ? 'OK ' : 'NG ') . "$name($f):見つかった数 $n\n"; if ($n !== 1) { $ng++; continue; }
    $out[$f] = preg_replace_callback($re, function ($m) use ($use, $ind) { return $m[1] . $use($ind) . $m[2]; }, $out[$f]); }
echo (is_file($lib) ? 'NG 部品 CsSmsCount.php はもうある' : 'OK 部品 CsSmsCount.php を新しく作る') . "\n"; if (is_file($lib)) $ng++;
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if (!$apply) { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_sms_count_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
foreach ([$S, $J] as $f) { copy($b . $f, $bk . basename($f)); file_put_contents($b . $f, $out[$f]); }
file_put_contents($lib, $libSrc);
$bad = 0; foreach ([$S, $J, 'app/Libs/CsSmsCount.php'] as $f) { exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r); echo ($r === 0 ? '文法OK ' : '文法NG ') . "$f\n"; $o = []; if ($r !== 0) $bad++; }
if ($bad) { foreach ([$S, $J] as $f) copy($bk . basename($f), $b . $f); unlink($lib); echo "\n文法の誤りがあったので、元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "\n変えました。控え: $bk\n";
