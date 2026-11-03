<?php
// [カスタムエイド 2026-10-28] 予約通知 段階1の続き:SMS を送る場所の前後を読むだけ(何も変更しない)。鍵・パスワードらしい行は伏せる
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b);
function h($t) { echo "\n========== $t ==========\n"; }
function show($f, $a, $z) {
    $L = @file($f); if (!$L) { echo "(ない) $f\n"; return; }
    for ($i = $a; $i <= min($z, count($L)); $i++) { $s = rtrim($L[$i - 1]);
        if (preg_match('/key|secret|password|passwd|token|AccessKey/i', $s) && preg_match('/[=:>]\s*[\'"][^\'"]{6,}/', $s)) $s = preg_replace('/([\'"])[^\'"]{6,}([\'"])/', '$1(伏せる)$2', $s);
        $s = preg_replace('/([\'"])[A-Za-z0-9+\/=_-]{16,}([\'"])/', '$1(伏せる)$2', $s); // 引用符の中の長い英数字(鍵の形)はどの行でも伏せる
        printf("%4d: %s\n", $i, $s); }
}
$M = 'app/Http/Controllers/Api/Member/OrderController.php'; $S = 'app/Http/Controllers/Api/Shop/OrderController.php';
h('A. 会員側 予約受付(SMS を送るところ)'); show($M, 248, 300); show($M, 438, 452);
h('B. お店側 予約作成の SMS'); show($S, 398, 430);
h('C. お店側 変更の SMS'); show($S, 700, 727);
h('D. お店側 キャンセル(削除)'); show($S, 728, 864);
h('E. リマインドの SMS'); show('app/Jobs/OrderNoticeJob.php', 36, 56); show('app/Jobs/OrderNoticeJob.php', 135, 175);
h('F. OrderStartTimeNoticeJob(何をしているか)'); show('app/Jobs/OrderStartTimeNoticeJob.php', 1, 120);
h('G. SMS を送る部品(鍵は伏せる)'); show('app/Libs/AlibabaCloudSms.php', 1, 90);
h('H. メールの部品'); show('app/Services/EmailService.php', 1, 60); echo shell_exec('ls resources/views/layouts/email 2>&1; grep -rn "EmailService" app --include=*.php | grep -v "^app/Services" | head');
h('I. ログイン不要のルート(会員側の予約のまわり)'); show('routes/api.php', 300, 345);
echo "\n(おわり)\n";
