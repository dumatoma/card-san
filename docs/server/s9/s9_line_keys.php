<?php
// [カスタムエイド 2026-11-04] 段階9:管理App の LINE の画面ができるまでの試験用。店舗の LINE の鍵をサーバーで入れる・状態を見る
//   php -d disable_functions= /root/s9_line_keys.php set 11      … 4つの鍵を1つずつ貼る(画面には出ない)→ LINE に問い合わせて確かめ、正しければ保存
//   php -d disable_functions= /root/s9_line_keys.php status 11   … 今の状態(鍵ははじめとおわりの3文字だけ)・今月の通数・受け付ける方法
//   php -d disable_functions= /root/s9_line_keys.php lineon 11   … 店舗11 で「LINE で受け付ける」をオンにする(予約WEB に LINE ボタンが出る。off で戻す:lineoff)
//   php -d disable_functions= /root/s9_line_keys.php remove 11   … 「設定を解除する」と同じ(鍵を消す)
// 鍵はこの画面にも記録にも出さない。チャットにも貼らないでください

$base = '/www/wwwroot/api.card-san.jp/';
require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!class_exists('App\Libs\CsLine')) exit("先に s9_line_patch.php を apply してください\n");

$mode = $argv[1] ?? '';
$sid = (int) ($argv[2] ?? 0);
if (!$sid || !in_array($mode, ['set', 'status', 'lineon', 'lineoff', 'remove'], true)) exit("使い方:set|status|lineon|lineoff|remove 店舗ID\n");
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
    'msg_secret' => 'Messaging API のチャネルシークレット(Messaging API のチャネル →「チャネル基本設定」)',
    'msg_token' => 'Messaging API のチャネルアクセストークン(長期)(Messaging API のチャネル →「Messaging API設定」のいちばん下)',
    'login_id' => 'LINE ログインのチャネル ID(LINE ログインのチャネル「Card-San 予約」→「チャネル基本設定」。数字)',
    'login_secret' => 'LINE ログインのチャネルシークレット(同じ画面)',
];
$in = [];
foreach ($labels as $k => $label) {
    echo "\n{$label}\nを貼って Enter を押してください(貼っても文字は出ません):";
    $hide = $k !== 'login_id';
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
