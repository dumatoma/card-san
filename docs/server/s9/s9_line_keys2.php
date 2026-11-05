<?php
// [カスタムエイド 2026-11-04] 段階9 v2:管理App の LINE の画面ができるまでの試験用(鍵は5つ)。店舗の LINE の鍵をサーバーで入れる・状態を見る
//   php -d disable_functions= /root/s9_line_keys2.php set 11      … 5つを1つずつ貼る(画面には出ない)→ LINE に問い合わせて確かめ、正しければ保存
//   php -d disable_functions= /root/s9_line_keys2.php status 11   … 今の状態(鍵ははじめとおわりの3文字だけ)・今月の通数・受け付ける方法
//   php -d disable_functions= /root/s9_line_keys2.php lineon 11   … 店舗11 で「LINE で受け付ける」をオンにする(予約WEB に LINE ボタンが出る。off で戻す:lineoff)
//   php -d disable_functions= /root/s9_line_keys2.php remove 11   … 「設定を解除する」と同じ(鍵を消す)
//   php -d disable_functions= /root/s9_line_keys2.php testurl 11  … 「試しに自分の LINE に送る」の住所(LIFF)
// 鍵はこの画面にも記録にも出さない。チャットにも貼らないでください

$base = '/www/wwwroot/api.card-san.jp/';
require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!class_exists('App\Libs\CsLine') || !method_exists('App\Libs\CsLine', 'liff')) exit("先に s9_line_v2_patch.php を apply してください\n");

$mode = $argv[1] ?? '';
$sid = (int) ($argv[2] ?? 0);
if (!$sid || !in_array($mode, ['set', 'status', 'lineon', 'lineoff', 'remove', 'testurl'], true)) exit("使い方:set|status|lineon|lineoff|remove|testurl 店舗ID\n");
$show = function ($sid) {
    $s = App\Libs\CsLine::status($sid);
    echo "鍵:" . ($s['set'] ? 'そろっています' : 'まだありません') . "\n";
    if ($s['set']) {
        echo "LINE 公式アカウント:" . $s['bot_name'] . "(" . $s['bot_id'] . ")\n";
        foreach ($s['keys'] as $k => $v) echo "  {$k}:{$v}\n";
        $q = $s['quota'];
        echo "今月の通数:" . ($q ? ('送った数 ' . $q['used'] . ' / 上限 ' . ($q['limit'] ?: 'なし') . ($q['over'] ? '(上限に達しています)' : '')) : '取れませんでした') . "\n";
    }
    echo "受け付ける方法:" . json_encode(App\Libs\CsReserveCh::get($sid), JSON_UNESCAPED_UNICODE) . "\n";
};

if ($mode === 'status') { $show($sid); exit; }
if ($mode === 'testurl') { list($d, $u) = App\Libs\CsLine::draftPut($sid, 'test', []); echo ($u ?: '鍵がまだありません') . "\n(60分使えます。iPhone で開くと LINE アプリが開きます)\n"; exit; }
if ($mode === 'remove') { App\Libs\CsLine::remove($sid); echo "店舗{$sid} の LINE の鍵を消しました\n"; $show($sid); exit; }
if ($mode === 'lineon' || $mode === 'lineoff') {
    $ch = App\Libs\CsReserveCh::get($sid);
    $r = App\Libs\CsReserveCh::set($sid, (bool) $ch['mail'], (bool) $ch['sms'], $mode === 'lineon');
    echo $r['msg'] . "\n";
    $show($sid);
    exit;
}

// set:1つずつ貼る(文字は画面に出さない)
$labels = [
    'msg_id' => "1/5 Messaging API のチャネル ID\n   LINE Developers →「Card-San予約見本」→ チャネル「Card-San」(緑の Messaging API の印)→「チャネル基本設定」タブ → いちばん上の「チャネルID」(数字)",
    'msg_secret' => "2/5 Messaging API のチャネルシークレット\n   同じ画面(「Card-San」→「チャネル基本設定」)を下へ →「チャネルシークレット」",
    'login_id' => "3/5 LINE ログインのチャネル ID\n   チャネル「Card-San 予約」(LINEログインの印)→「チャネル基本設定」タブ → いちばん上の「チャネルID」(数字)",
    'login_secret' => "4/5 LINE ログインのチャネルシークレット\n   同じ画面(「Card-San 予約」→「チャネル基本設定」)を下へ →「チャネルシークレット」",
    'liff_id' => "5/5 LIFF ID\n   チャネル「Card-San 予約」→「LIFF」タブ →「LIFF ID」(数字-英数字)",
];
$in = [];
foreach ($labels as $k => $label) {
    $hideMsg = in_array($k, ['msg_secret', 'login_secret'], true) ? '(貼っても文字は出ません)' : '';
    echo "\n{$label}\nを貼って Enter を押してください{$hideMsg}:";
    $hide = in_array($k, ['msg_secret', 'login_secret'], true);
    if ($hide) @system('stty -echo');
    $v = trim((string) fgets(STDIN));
    if ($hide) { @system('stty echo'); echo "\n"; }
    echo $v === '' ? "(空でした)\n" : ('受け取りました(' . mb_strlen($v) . "文字)\n");
    $in[$k] = $v;
}
echo "\nLINE に問い合わせて確かめています…\n";
$r = App\Libs\CsLine::save($sid, $in);
echo ($r['ok'] ? 'OK ' : 'NG ') . $r['msg'] . "\n";
foreach ($r['errors'] as $k => $e) echo "  {$k}:{$e}\n";
echo "\n";
$show($sid);
