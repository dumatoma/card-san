<?php
// [カスタムエイド 2026-11-02] B:App内課金・Google Play課金 → クレジットカード(Stripe)の「予約」
//   ストアで解約(自動更新 OFF)したお店が、管理WEB でカード払いを申し込める。カード払いはアプリの契約が終わる1時間前から始まる
//   使い方(このファイルと同じ場所に CsIapSwitch.php・CsIapSwitchGuard.php を置く):
//     php -d disable_functions= iapswitch_patch.php        → 確認だけ(何も変えない)
//     php -d disable_functions= iapswitch_patch.php apply  → 控えを取ってから変える
//     php -d disable_functions= iapswitch_patch.php test 店舗ID → そのお店のアプリの定期購入の状態を Apple・Google に聞く(見るだけ)
//     php -d disable_functions= iapswitch_patch.php undo   → 控えに戻す
$b = '/www/wwwroot/api.card-san.jp/'; $here = __DIR__ . '/'; $mode = $argv[1] ?? 'check'; $last = '/root/cs_iapswitch_last_backup.txt';
$M = '[カスタムエイド 2026-11-02]';
if ($mode === 'test') {
    require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $sid = (int)($argv[2] ?? 0);
    $q = \App\Models\ShopVipModel::whereIn('card_type', [2, 3])->where('status', 1); if ($sid) $q->where('sid', $sid);
    $vip = $q->orderBy('end_time', 'desc')->first();
    if (!$vip) { echo "App内課金・Google Play課金の契約が見つかりません\n"; exit(1); }
    echo "店舗 {$vip->sid} / " . ($vip->card_type == 2 ? 'App Store' : 'Google Play') . " / DBの終わり " . date('Y-m-d H:i', $vip->end_time) . " / 解約の印 " . ($vip->cancel_time ? date('Y-m-d H:i', $vip->cancel_time) : 'なし') . "\n";
    $st = \App\Libs\CsIapSwitch::status($vip);
    echo "聞いた結果: " . ($st['ok'] ? 'OK' : 'NG ' . $st['msg']) . " / 自動更新 " . ($st['auto_renew'] ? 'ON' : 'OFF') . " / 有効 " . ($st['active'] ? 'はい' : 'いいえ') . " / 期限 " . ($st['expires'] ? date('Y-m-d H:i', $st['expires']) : '-') . "\n";
    exit(0);
}
if ($mode === 'undo') {
    $bk = is_file($last) ? trim(file_get_contents($last)) : '';
    if (!$bk || !is_dir($bk)) { echo "NG 控えが見つかりません\n"; exit(1); }
    foreach (glob($bk . '/*') as $x) { $rel = str_replace('__', '/', basename($x)); copy($x, $b . $rel); echo "戻しました $rel\n"; }
    foreach (['app/Libs/CsIapSwitch.php', 'app/Console/Commands/CsIapSwitchGuard.php'] as $nf) { if (is_file($b . $nf)) { rename($b . $nf, $bk . '/new_' . basename($nf)); echo "外しました $nf\n"; } }
    exit(0);
}
$P = []; // [ファイル, 探す文字, 置きかえる文字, 説明]
$P[] = ['app/Validators/ShopVipValidator.php',
"        // Apple IAP: 決済方法の変更は不可\n        if(\$now_vip && \$now_vip->card_type == 2 && \$now_vip->card_type != \$request->card_type){",
"        // $M App内課金・Google Play課金 → カード払いの予約(B)。ストアで解約(自動更新 OFF)していれば、アプリの契約が終わる1時間前から始める
        \$cs_switch = 0;
        if(\$now_vip && in_array(\$now_vip->card_type, [2, 3]) && \$request->card_type == 1){
            \$cs_st = \\App\\Libs\\CsIapSwitch::status(\$now_vip);
            if(!\$cs_st['ok']){
                return ['success' => false, 'message' => '定期購入の状態を確認できませんでした。しばらくしてから、もう一度お試しください。'];
            }
            if(\$cs_st['auto_renew'] && \$cs_st['active']){
                return ['success' => false, 'message' => \$now_vip->card_type == 2
                    ? 'App Store の定期購入(CardSan管理)が続いています。先に iPhone の「設定」→ お名前 →「サブスクリプション」→「CardSan管理」で解約してから、もう一度お申し込みください。解約しても、今のご契約の終わりまでお使いいただけます。カード払いはその日から始まります。'
                    : 'Google Play の定期購入(CardSan管理)が続いています。先に「Playストア」→ プロフィールのアイコン →「お支払いと定期購入」→「定期購入」→「CardSan管理」で解約してから、もう一度お申し込みください。解約しても、今のご契約の終わりまでお使いいただけます。カード払いはその日から始まります。'];
            }
            \$cs_switch = max((int)(\$cs_st['expires'] ?: \$now_vip->end_time) - 3600, time());
        }
        // Apple IAP: 決済方法の変更は不可
        if(!\$cs_switch && \$now_vip && \$now_vip->card_type == 2 && \$now_vip->card_type != \$request->card_type){", 'チェック:予約の判定を足す'];
$P[] = ['app/Validators/ShopVipValidator.php',
"        if(\$now_vip && \$now_vip->card_type == 3 && \$request->card_type != 3){",
"        if(!\$cs_switch && \$now_vip && \$now_vip->card_type == 3 && \$request->card_type != 3){", 'チェック:Google の止め'];
$P[] = ['app/Validators/ShopVipValidator.php',
"        if(\$now_vip && \$request->type <= \$now_vip->type && \$now_vip->card_type != \$request->card_type){",
"        if(!\$cs_switch && \$now_vip && \$request->type <= \$now_vip->type && \$now_vip->card_type != \$request->card_type){", 'チェック:支払い方法の変更の止め'];
$P[] = ['app/Validators/ShopVipValidator.php',
"        if(!\$now_vip || \$request->type > \$now_vip->type){",
"        if(\$cs_switch){ // $M 予約:アプリの契約が終わる1時間前から
            \$start_time = \$cs_switch;
        }elseif(!\$now_vip || \$request->type > \$now_vip->type){", 'チェック:開始日'];
$P[] = ['app/Libs/ApplePay.php',
"        return ['success' => false, 'message' => \$last];\n    }\n",
"        return ['success' => false, 'message' => \$last];\n    }\n
    /** $M 定期購入の今の状態(自動更新 ON/OFF・期限)を聞く。本番→見つからなければサンドボックス */
    public function csSubscriptionStatus(\$original_transaction_id)
    {
        \$last = 'unknown error';
        \$dec = function (\$jws) { \$p = explode('.', (string)\$jws); \$x = strtr(\$p[1] ?? '', '-_', '+/'); \$x .= str_repeat('=', (4 - strlen(\$x) % 4) % 4); return json_decode(base64_decode(\$x), true) ?: []; };
        foreach (['https://api.storekit.itunes.apple.com', 'https://api.storekit-sandbox.itunes.apple.com'] as \$base) {
            \$res = \$this->curl->send(['url' => \$base . '/inApps/v1/subscriptions/' . \$original_transaction_id, 'method' => 'get',
                'headers' => ['Authorization: Bearer ' . \$this->createJWT()]]);
            \$output = json_decode(\$res['output'] ?? '', true);
            foreach ((\$output['data'] ?? []) as \$g) {
                foreach ((\$g['lastTransactions'] ?? []) as \$t) {
                    if ((string)(\$t['originalTransactionId'] ?? '') !== (string)\$original_transaction_id) continue;
                    \$tx = \$dec(\$t['signedTransactionInfo'] ?? ''); \$rn = \$dec(\$t['signedRenewalInfo'] ?? '');
                    return ['success' => true, 'output' => ['status' => (int)(\$t['status'] ?? 0), 'autoRenewStatus' => (int)(\$rn['autoRenewStatus'] ?? 1),
                        'expiresDate' => (int)(\$tx['expiresDate'] ?? 0), 'environment' => \$output['environment'] ?? (strpos(\$base, 'sandbox') ? 'Sandbox' : 'Production')]];
                }
            }
            \$code = \$output['errorCode'] ?? (\$res['code'] ?? '');
            \$last = 'error code ' . \$code . (isset(\$output['errorMessage']) ? ' ' . \$output['errorMessage'] : '');
            if (!in_array((string)\$code, ['4040005', '4040010', '200'])) break; // 見つからない以外のエラーはサンドボックスに回さない
        }
        return ['success' => false, 'message' => \$last];
    }
", 'Apple:状態を聞く関数を足す'];
$P[] = ['app/Services/IapNotifyService.php',
"                \$this->log(\$ch, \"auto renew \" . (\$cancel ? 'OFF' : 'ON') . \" {\$orig}\");\n",
"                \$this->log(\$ch, \"auto renew \" . (\$cancel ? 'OFF' : 'ON') . \" {\$orig}\");\n                if (!\$cancel) \\App\\Libs\\CsIapSwitch::cancelReservations(\$anchor->sid, 'apple auto renew ON'); // $M 再開したらカード払いの予約を取り消す\n", 'Apple の知らせ:自動更新 ON で予約を取り消す'];
$P[] = ['app/Services/IapNotifyService.php',
"                        ->update(['cancel_time' => 0, 'is_subscription' => 1]);\n                }\n                if (!in_array(\$state,",
"                        ->update(['cancel_time' => 0, 'is_subscription' => 1]);\n                    \\App\\Libs\\CsIapSwitch::cancelReservations(\$anchor->sid, 'google restarted'); // $M 再開したらカード払いの予約を取り消す\n                }\n                if (!in_array(\$state,", 'Google の知らせ:再開で予約を取り消す'];
$P[] = ['app/Console/Kernel.php',
"        \$schedule->command('karte:work --purge')->dailyAt('04:10')->withoutOverlapping();\n",
"        \$schedule->command('karte:work --purge')->dailyAt('04:10')->withoutOverlapping();\n        // $M App内課金→カード払いの予約の見張り(毎時20分)\n        \$schedule->command('cs:iap-switch-guard')->hourlyAt(20)->withoutOverlapping();\n", '毎時の見張りを登録'];

$ng = 0; $src = [];
foreach ($P as $p) {
    if (!isset($src[$p[0]])) $src[$p[0]] = @file_get_contents($b . $p[0]);
    $n = substr_count((string)$src[$p[0]], $p[1]);
    echo ($n === 1 ? 'OK ' : 'NG ') . "{$p[3]}({$p[0]}・見つかった数 $n)\n"; if ($n !== 1) $ng++;
}
foreach (array_keys($src) as $f) { if (strpos((string)$src[$f], $M) !== false && strpos((string)$src[$f], 'CsIapSwitch') !== false) { echo "NG もう入っています $f\n"; $ng++; } }
foreach (['CsIapSwitch.php', 'CsIapSwitchGuard.php'] as $x) { $ok = is_file($here . $x); echo ($ok ? 'OK ' : 'NG ') . "新しいファイル $x(" . ($ok ? md5_file($here . $x) : 'なし') . ")\n"; if (!$ok) $ng++; }
$sbs = (string)@file_get_contents($b . 'app/Services/StripeBillingService.php');
$ok = strpos($sbs, 'public function client()') !== false; echo ($ok ? 'OK ' : 'NG ') . "Stripe の client() がある\n"; if (!$ok) $ng++;
$ok = strpos($sbs, "if (\$trialEnd) \$data['trial_end'] = \$trialEnd;") !== false; echo ($ok ? 'OK ' : 'NG ') . "Stripe の「始まる日を先にする」処理がある\n"; if (!$ok) $ng++;
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if ($mode !== 'apply') { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }

$bk = '/www/backup_customaid_iapswitch_' . date('Ymd_His'); mkdir($bk, 0700, true); file_put_contents($last, $bk);
foreach (array_keys($src) as $f) copy($b . $f, $bk . '/' . str_replace('/', '__', $f));
$new = $src; foreach ($P as $p) $new[$p[0]] = str_replace($p[1], $p[2], $new[$p[0]]);
$own = fileowner($b . 'app/Libs/ApplePay.php'); $grp = filegroup($b . 'app/Libs/ApplePay.php');
foreach ($new as $f => $s) file_put_contents($b . $f, $s);
copy($here . 'CsIapSwitch.php', $b . 'app/Libs/CsIapSwitch.php');
copy($here . 'CsIapSwitchGuard.php', $b . 'app/Console/Commands/CsIapSwitchGuard.php');
foreach (['app/Libs/CsIapSwitch.php', 'app/Console/Commands/CsIapSwitchGuard.php'] as $nf) { @chown($b . $nf, $own); @chgrp($b . $nf, $grp); @chmod($b . $nf, 0644); }
$bad = 0;
foreach (array_merge(array_keys($new), ['app/Libs/CsIapSwitch.php', 'app/Console/Commands/CsIapSwitchGuard.php']) as $f) {
    $o = []; exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r); echo ($r === 0 ? '文法OK ' : '文法NG ') . "$f\n"; if ($r !== 0) $bad++;
}
if ($bad) {
    foreach (array_keys($src) as $f) copy($bk . '/' . str_replace('/', '__', $f), $b . $f);
    @unlink($b . 'app/Libs/CsIapSwitch.php'); @unlink($b . 'app/Console/Commands/CsIapSwitchGuard.php');
    echo "文法NG のため元に戻しました。この結果を Claude に送ってください。\n"; exit(1);
}
echo "\n変えました。控え: $bk\n戻すときは: php -d disable_functions= " . __FILE__ . " undo\n";
echo "次に: php -d disable_functions= " . __FILE__ . " test   と   php -d disable_functions= artisan cs:iap-switch-guard --dry\n";
