<?php
// [カスタムエイド 2026-10-31] 予約通知・予約詳細に「連絡のしかた」(app=アプリ / mail=メール / sms=SMS。LINE は段階9)とメールアドレスを渡す
//   判定は予約のメッセージを送るときの決まりと同じ:会員の予約→アプリ、メールアドレスあり→メール、電話番号あり→SMS。表は変えない
//   使い方: php -d disable_functions= customaid_contact_patch.php → 確認だけ / ... apply → 控えを取ってから変える
$b = '/www/wwwroot/api.card-san.jp/'; $apply = ($argv[1] ?? '') === 'apply';
$lib = 'app/Libs/CsOrderContact.php';
$libSrc = <<<'PHP'
<?php

namespace App\Libs;

// [カスタムエイド 2026-10-31] 予約の「連絡のしかた」。予約のメッセージを送るときの決まりと同じ順で決める(会員→アプリ、メールあり→メール、電話あり→SMS)
class CsOrderContact
{
    public static function of($order)
    {
        if (!$order) return '';
        if ((int) ($order->mid ?? 0) > 0) return 'app';
        if ((string) ($order->email ?? '') !== '') return 'mail';
        if ((string) ($order->phone ?? '') !== '') return 'sms';
        return '';
    }
}
PHP;
$edits = [
  ['app/Http/Controllers/Api/Shop/OrderNoticeController.php', '予約通知の一覧', "                'menu_name' => \$v->order->menu_name\n",
   "                'menu_name' => \$v->order->menu_name,\n                'contact' => \\App\\Libs\\CsOrderContact::of(\$v->order) // [カスタムエイド 2026-10-31] 連絡のしかた\n"],
  ['app/Http/Controllers/Api/Shop/OrderController.php', '予約の詳細', "            'phone' => \$order->phone,\n            'member_id' => \$order->member_id,\n",
   "            'phone' => \$order->phone,\n            'email' => (string) (\$order->email ?? ''), // [カスタムエイド 2026-10-31] お客様のメールアドレス\n            'contact' => \\App\\Libs\\CsOrderContact::of(\$order), // [カスタムエイド 2026-10-31] 連絡のしかた\n            'member_id' => \$order->member_id,\n"],
];
$ng = 0; $txt = [];
if (is_file($b . $lib)) { echo "NG もうある: $lib\n"; $ng++; } else echo "OK 新しく入れる: $lib\n";
foreach ($edits as $e) { $s = file_get_contents($b . $e[0]); $n = substr_count($s, $e[2]); echo ($n === 1 ? 'OK ' : 'NG ') . "$e[1]:見つかった数 $n\n"; if ($n !== 1) $ng++; else $txt[$e[0]] = str_replace($e[2], $e[3], $s); }
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if (!$apply) { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_contact_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
foreach ($txt as $f => $s) copy($b . $f, $bk . basename($f));
file_put_contents($b . $lib, $libSrc . "\n"); @chown($b . $lib, 'www'); @chgrp($b . $lib, 'www');
foreach ($txt as $f => $s) file_put_contents($b . $f, $s);
$bad = 0; foreach (array_merge([$lib], array_keys($txt)) as $f) { $o = []; exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r); echo ($r === 0 ? '文法OK ' : '文法NG ') . "$f\n"; if ($r) $bad++; }
if ($bad) { foreach ($txt as $f => $s) copy($bk . basename($f), $b . $f); @unlink($b . $lib); echo "\n元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "\n変えました。控え: $bk\n";
