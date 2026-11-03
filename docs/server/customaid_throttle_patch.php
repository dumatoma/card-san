<?php
// [カスタムエイド 2026-10-28] サーバーの「1分に60回まで」の制限を、使い方に合わせて直す。
//   今は「つないでいる場所(IP)ごとに1分60回」。同じ Wi-Fi の管理App と Card-San App が合計で数えられ、超えると返事を断られて一覧が空・会員証が undefined になっていた。
//   直したあと:①アプリの利用者(uuid)ごとに1分300回 ②同じ場所(IP)全体で1分1200回(いたずらの大量の通信は今までどおり止める)
//   使い方: php customaid_throttle_patch.php → 確認だけ / php customaid_throttle_patch.php apply → 控えを取ってから変える
$b = '/www/wwwroot/api.card-san.jp/'; $f = $b . 'app/Providers/RouteServiceProvider.php'; $apply = ($argv[1] ?? '') === 'apply';
$lock = json_decode(@file_get_contents($b . 'composer.lock'), true); $ver = '?';
foreach (($lock['packages'] ?? []) as $p) if ($p['name'] === 'laravel/framework') $ver = $p['version'];
echo "Laravel の版:$ver\n"; $major = (int) ltrim(explode('.', ltrim($ver, 'v'))[0], 'v');
$old = "            return Limit::perMinute(60)->by(optional(\$request->user())->id ?: \$request->ip());\n";
$new = "            // [カスタムエイド 2026-10-28] 以前は「IP ごとに1分60回」。同じ Wi-Fi の管理App と Card-San App が合計で数えられ、超えると一覧が空になっていた\n"
     . "            //   ①利用者(ヘッダーの uuid)ごとに1分300回 ②同じ IP 全体で1分1200回(大量の通信は今までどおり止める)\n"
     . "            \$csWho = (string) \$request->header('uuid');\n"
     . "            return [\n"
     . "                Limit::perMinute(300)->by('cs-u:' . (\$csWho !== '' ? \$csWho : \$request->ip())),\n"
     . "                Limit::perMinute(1200)->by('cs-ip:' . \$request->ip()),\n"
     . "            ];\n";
$s = file_get_contents($f); $n = substr_count($s, $old);
echo ($n === 1 ? 'OK' : 'NG') . " 直す行が見つかった数:$n\n";
if ($major < 8) { echo "NG Laravel 8 より古いので、この直し方は使えません\n"; $n = 0; }
$tr = @file_get_contents($b . 'vendor/laravel/framework/src/Illuminate/Routing/Middleware/ThrottleRequests.php');
if (strpos((string) $tr, 'Arr::wrap($limiterResponse)') === false) { echo "NG このサーバーの Laravel は、制限を2つ並べる書き方に対応していません\n"; $n = 0; } else echo "OK 制限を2つ並べる書き方に対応しています\n";
if ($n !== 1) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if (!$apply) { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_throttle_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($f, $bk . basename($f));
file_put_contents($f, str_replace($old, $new, $s));
exec('php -l ' . escapeshellarg($f) . ' 2>&1', $o, $r);
if ($r !== 0) { copy($bk . basename($f), $f); echo "文法NG のため元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "文法OK\n変えました。控え: $bk\n(元に戻すとき: cp {$bk}RouteServiceProvider.php $f)\n";
