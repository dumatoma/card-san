<?php
// [カスタムエイド 2026-10-30] 段階6:WEB予約をメールで送る・お店の予約受付用メールアドレス(決定事項 24〜28)
//   /root/cs_mail6/ に置いたファイルを入れ、予約の保存・お店の予約/変更/キャンセル・前日のリマインドで「メールアドレスがあればメール、なければ今までどおり SMS」にする
//   使い方(どれも php -d disable_functions= install6.php ...):
//     (何も付けない) → 確認だけ / apply → 控えを取ってから入れる
//     shopmail 店舗ID メールアドレス → その店舗の予約受付用メールアドレスを登録して確認のメールを送る
//     bcc 店舗ID on|off → 写し(BCC)のオン・オフ / status 店舗ID → 今の設定を見る
//     test 送り先 → 予約409(店舗11)の「予約受付」メールを送る(店舗11の確認済みアドレスがあれば返信先・写しも付く)
$b = '/www/wwwroot/api.card-san.jp/'; $src = '/root/cs_mail6/'; chdir($b); $mode = $argv[1] ?? 'check';
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Schema; use Illuminate\Support\Facades\DB;

if (in_array($mode, ['shopmail', 'bcc', 'status', 'test'], true)) {
    if (!class_exists('App\Libs\CsShopMail')) { echo "まだ入っていません。先に apply してください\n"; exit(1); }
    $sid = (int) ($argv[2] ?? 0);
    if ($mode === 'shopmail') { $r = App\Libs\CsShopMail::request($sid, $argv[3] ?? ''); echo ($r['ok'] ? 'OK ' : 'NG ') . $r['msg'] . "\n"; exit($r['ok'] ? 0 : 1); }
    if ($mode === 'bcc') { $on = ($argv[3] ?? '') === 'on'; if (!in_array($argv[3] ?? '', ['on', 'off'], true)) { echo "on か off を付けてください\n"; exit(1); } App\Libs\CsShopMail::setBcc($sid, $on); echo "写し(BCC):" . ($on ? 'オン' : 'オフ') . "\n"; exit(0); }
    if ($mode === 'status') { $r = DB::table('web_shop')->where('id', $sid)->first(['shop_name', 'reserve_email', 'reserve_email_ok', 'reserve_bcc_on', 'reserve_email_sent']);
        if (!$r) { echo "店舗が見つかりません\n"; exit(1); }
        echo "店舗:{$r->shop_name}\n予約受付用メールアドレス:" . ($r->reserve_email !== '' ? $r->reserve_email : '(なし)') . "\n確認:" . ((int) $r->reserve_email_ok ? '済み' : 'まだ') . "\n写し(BCC):" . ((int) $r->reserve_bcc_on ? 'オン' : 'オフ') . "\n"; exit(0); }
    $to = trim($argv[2] ?? ''); if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { echo "使い方:php -d disable_functions= install6.php test 送り先のメールアドレス\n"; exit(1); }
    $o = App\Models\OrderModel::find(409); if (!$o || (int) $o->sid !== 11) { echo "予約409(店舗11)が見つかりません\n"; exit(1); }
    echo "返信先:" . (App\Libs\CsShopMail::shopMail(11) ?: '(なし)') . " / 写し:" . (App\Libs\CsShopMail::bcc(11) ?: '(なし)') . "\n";
    $r = App\Libs\CsReserveMail::send('confirm', $o, $to, $o->name); echo ($r ? '送りました' : 'NG(laravel.log を見てください)') . "\n"; exit($r ? 0 : 1);
}

$new = ['CsShopMail.php' => 'app/Libs/CsShopMail.php', 'CsShopMailController.php' => 'app/Http/Controllers/CsShopMailController.php'];
$rep = ['CsReserveMail.php' => ['app/Libs/CsReserveMail.php', 'eefc49a5da95f0cfcf9b14f7c07f23cf'], 'cs_reserve_mail.blade.php' => ['resources/views/cs_reserve_mail.blade.php', '3833446d7a7b99c0464019a3cf43160f']];
$MO = 'app/Http/Controllers/Api/Member/OrderController.php'; $SO = 'app/Http/Controllers/Api/Shop/OrderController.php'; $JB = 'app/Jobs/OrderNoticeJob.php';
$PG = 'resources/views/cs_sms_page.blade.php'; $RT = 'routes/web.php';
$M = '// [カスタムエイド 2026-10-30] 段階6:メールアドレスがあればメールで送る(なければ今までどおり SMS)';
$sms = '(\s*\/\/ \[カスタムエイド 2026-10-21\][^\n]*\n\s*\$message = \\\\App\\\\Libs\\\\CsSmsText::build\(\'%s\', \$order\);)';
$edits = [ // [ファイル, 名前, 種類(s=文字・r=正規表現), 今, 新しい]
  [$MO, 'お客様の予約:メールアドレスを受け取る', 's', "            \$data['name'] = \$request->name ?? '';\n            \$data['phone'] = \$request->phone ?? '';\n",
      "            \$data['name'] = \$request->name ?? '';\n            \$data['phone'] = \$request->phone ?? '';\n"
    . "            // [カスタムエイド 2026-10-30] 段階6:WEB予約のメールアドレス(予約の内容はメールで送る)。.env の CS_WEB_EMAIL_REQUIRED=true で必須に\n"
    . "            \$cs_email = \\App\\Libs\\CsShopMail::clean(\$request->email ?? '');\n"
    . "            if (\$cs_email !== '' && !\\App\\Libs\\CsShopMail::valid(\$cs_email)) {\n                return responseToJson(400, 'メールアドレスの形が正しくありません');\n            }\n"
    . "            if (\$cs_email === '' && env('CS_WEB_EMAIL_REQUIRED', false)) {\n                return responseToJson(400, 'メールアドレスを入力してください');\n            }\n"
    . "            \$data['email'] = \$cs_email;\n"],
  [$MO, 'お客様の予約:受付をメールで', 'r', '/if\(\$order->phone\)\{' . sprintf($sms, 'confirm') . '/',
      "if(\$order->email ?? ''){\n                        $M\n                        \\App\\Libs\\CsReserveMail::send('confirm', \$order, \$order->email, \$order->name);\n                    }elseif(\$order->phone){\$1"],
  [$SO, 'お店の予約:受付', 'r', '/\}elseif\(\$order->phone\)\{' . sprintf($sms, 'confirm') . '/',
      "}elseif(\$order->email ?? ''){\n                    $M\n                    \\App\\Libs\\CsReserveMail::send('confirm', \$order, \$order->email, \$order->name);\n                }elseif(\$order->phone){\$1"],
  [$SO, 'お店の予約:変更', 'r', '/\}else\{' . sprintf($sms, 'update') . '/',
      "}elseif(\$order->email ?? ''){\n            $M\n            \\App\\Libs\\CsReserveMail::send('update', \$order, \$order->email, \$order->name);\n        }else{\$1"],
  [$SO, 'お店の予約:キャンセル', 'r', '/\}elseif\(\$order->phone\)\{' . sprintf($sms, 'cancel') . '/',
      "}elseif(\$order->email ?? ''){\n                    $M\n                    \\App\\Libs\\CsReserveMail::send('cancel', \$order, \$order->email, \$order->name);\n                }elseif(\$order->phone){\$1"],
  [$JB, '前日のリマインド', 'r', '/\}elseif\(\$order->phone\)\{' . sprintf($sms, 'notice') . '/',
      "}elseif(\$order->email ?? ''){\n            $M\n            \\App\\Libs\\CsReserveMail::send('notice', \$order, \$order->email, \$order->name);\n        }elseif(\$order->phone){\$1"],
  [$PG, 'SMS のページ:お店にメールするボタン', 's', "        @if (\$tel !== '')<a class=\"btn {{ \$p['type'] === 'cancel' ? 'sub' : 'main' }}\" href=\"tel:{{ \$tel }}\">お店に電話する</a>@endif\n",
      "        @if (\$tel !== '')<a class=\"btn {{ \$p['type'] === 'cancel' ? 'sub' : 'main' }}\" href=\"tel:{{ \$tel }}\">お店に電話する</a>@endif\n"
    . "        @php \$rm = \\App\\Libs\\CsShopMail::shopMail((int) (\$shop['sid'] ?? 0)); @endphp {{-- [カスタムエイド 2026-10-30] 段階6:お店の予約受付用メールアドレス(確認済みのときだけ) --}}\n"
    . "        @if (\$rm !== '')<a class=\"btn sub\" href=\"mailto:{{ \$rm }}?subject={{ rawurlencode('ご予約について(予約番号 ' . (\$p['rows'][0][1] ?? '') . ')') }}\">お店にメールする</a>@endif\n"],
  [$RT, '確認のリンクの道すじ', 'r', '/(Route::get\(\'\/cs-r\/\{code\}\'[^\n]*\n)/',
      "\$1Route::get('/cs-mail-ok/{token}', '\\App\\Http\\Controllers\\CsShopMailController@verify')->where('token', '[0-9a-f]{40}')->middleware('throttle:30,1'); // [カスタムエイド 2026-10-30] 段階6:お店の予約受付用メールアドレスの確認\n"],
];
$cols = ['web_order' => ['email' => ['string', 100, '']], 'web_shop' => ['reserve_email' => ['string', 100, ''], 'reserve_email_ok' => ['tinyInteger', 0, 0], 'reserve_email_token' => ['string', 64, ''], 'reserve_email_sent' => ['integer', 0, 0], 'reserve_bcc_on' => ['tinyInteger', 0, 1]]];

$ng = 0;
foreach ($new as $s => $t) { if (!is_file($src . $s)) { echo "NG 置く前のファイルがない: $src$s\n"; $ng++; } elseif (is_file($b . $t)) { echo "NG もうある: $t\n"; $ng++; } else echo "OK 新しく入れる: $t\n"; }
foreach ($rep as $s => $t) { if (!is_file($src . $s)) { echo "NG 置く前のファイルがない: $src$s\n"; $ng++; continue; } $m = md5_file($b . $t[0]);
    if ($m !== $t[1]) { echo "NG 今のファイルが前回入れたものと違う: $t[0]($m)\n"; $ng++; } else echo "OK 入れ替える: $t[0]\n"; }
$txt = [];
foreach ($edits as $e) { if (!isset($txt[$e[0]])) $txt[$e[0]] = file_get_contents($b . $e[0]);
    if ($e[2] === 's') { $n = substr_count($txt[$e[0]], $e[3]); if ($n === 1) $txt[$e[0]] = str_replace($e[3], $e[4], $txt[$e[0]]); }
    else { $n = preg_match_all($e[3], $txt[$e[0]]); if ($n === 1) $txt[$e[0]] = preg_replace($e[3], $e[4], $txt[$e[0]], 1); }
    echo ($n === 1 ? 'OK ' : 'NG ') . "$e[1]:見つかった数 $n\n"; if ($n !== 1) $ng++; }
foreach ($cols as $tb => $cs) foreach ($cs as $c => $d) echo (Schema::hasColumn($tb, $c) ? 'もうある ' : '足す ') . "$tb.$c\n";
$rc = glob($b . 'bootstrap/cache/routes*.php'); echo 'ルートの控え(キャッシュ):' . ($rc ? implode(',', array_map('basename', $rc)) . '(apply のあと作り直す)' : 'なし') . "\n";
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if ($mode !== 'apply') { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }

$bk = '/www/backup_customaid_mail6_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
$chg = array_merge(array_keys($txt), array_column($rep, 0));
foreach ($chg as $f) copy($b . $f, $bk . str_replace('/', '__', $f));
$undo = function () use ($b, $bk, $chg, $new) { foreach ($chg as $f) copy($bk . str_replace('/', '__', $f), $b . $f); foreach ($new as $t) @unlink($b . $t); };
$bad = 0;
try {
    foreach ($cols as $tb => $cs) foreach ($cs as $c => $d) if (!Schema::hasColumn($tb, $c)) Schema::table($tb, function ($t) use ($c, $d) {
        $x = $d[0] === 'string' ? $t->string($c, $d[1]) : $t->{$d[0]}($c); $x->default($d[2]); });
    foreach ($cols as $tb => $cs) foreach ($cs as $c => $d) if (!Schema::hasColumn($tb, $c)) { echo "NG 欄を足せませんでした: $tb.$c\n"; $bad++; }
} catch (\Throwable $e) { echo "NG 欄を足すとき: " . $e->getMessage() . "\n"; $bad++; }
if (!$bad) {
    foreach ($new as $s => $t) copy($src . $s, $b . $t);
    foreach ($rep as $s => $t) copy($src . $s, $b . $t[0]);
    foreach ($txt as $f => $s) file_put_contents($b . $f, $s);
    foreach (array_merge(array_values($new), $chg) as $f) { @chown($b . $f, 'www'); @chgrp($b . $f, 'www');
        if (substr($f, -4) === '.php' && strpos($f, '.blade.') === false) { $o = []; exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r); echo ($r === 0 ? '文法OK ' : '文法NG ') . "$f\n"; if ($r !== 0) $bad++; } }
}
if (!$bad) {
    $o = []; exec('php -d disable_functions= -r ' . escapeshellarg('require "' . $b . 'vendor/autoload.php"; $app = require "' . $b . 'bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();'
        . ' $p = ["type"=>"confirm","heading"=>"","name"=>"見本 花子","honor"=>"様","top"=>"","mid"=>"","bottom"=>"","rows"=>[["予約番号","UU1"],["日時","x"],["メニュー","カット","9,000円",""],["担当者","指名なし"]],"shop"=>["sid"=>11,"name"=>"見本","phone"=>"0300000000","address"=>""],"start_time"=>time(),"card"=>""];'
        . ' $a = view("cs_reserve_mail", ["p"=>$p,"url"=>"u","rm"=>"shop@example.com"])->render(); $c = view("cs_reserve_mail", ["p"=>$p,"url"=>"u","rm"=>""])->render(); $d = view("cs_sms_page", ["p"=>$p,"state"=>"ok"])->render();'
        . ' $t1 = App\Libs\CsReserveMail::text($p, "u", "shop@example.com");'
        . ' echo (strpos($a,"お店にメールする")!==false && strpos($a,"お店に届きます")!==false && strpos($c,"お店にメールする")===false && strpos($c,"届きません")!==false && strpos($d,"お店に電話する")!==false && strpos($t1,"お店に届きます")!==false) ? "RENDER_OK" : "RENDER_NG";'
        . ' echo " ", App\Libs\CsShopMail::clean("Ｈａｎａｋｏ＠Ｅｘａｍｐｌｅ.COM "), " ", var_export(App\Libs\CsShopMail::valid("a@b"), true);') . ' 2>&1', $o, $r);
    $out = implode("\n", $o); echo "見た目の試し:" . $out . "\n";
    if (strpos($out, 'RENDER_OK Hanako@example.com false') === false) $bad++;
}
if (!$bad && $rc) { $o = []; exec('cd ' . escapeshellarg($b) . ' && php -d disable_functions= artisan route:cache 2>&1', $o, $r); echo "ルートの控えを作り直しました:" . implode(' ', $o) . "\n"; if ($r !== 0) $bad++; }
exec('chown -R www:www ' . escapeshellarg($b . 'storage/framework/views'));
if ($bad) { $undo(); echo "\nうまくいかなかったので、ファイルは元に戻しました(足した欄は残っています。使わなければ影響はありません)。この結果を Claude に送ってください。\n"; exit(1); }
echo "\n入れました。控え: $bk\n";
