<?php
// [カスタムエイド 2026-10-26] お店のページ(card-san.jp/s/店舗ID)を、版Bモック(画面1)どおりに直す
//   ・アイコンの下の「Card-San」の文字をなくす
//   ・「カードを受け取るまで」の手順の枠をなくす
//   ・iPhone のボタンの下の文:「インストール後、アプリの「ペースト」を押すと、このお店のカードが表示されます。」
//   ・LINE・Instagram の中で開いたときの案内を、いちばん下に移す(その場合だけ出すのは今までどおり)
// 使い方:
//   確認だけ:php /root/customaid_shoppage_mock_patch.php
//   反映    :php /root/customaid_shoppage_mock_patch.php apply
$apply = ($argv[1] ?? '') === 'apply';
$f = '/www/wwwroot/api.card-san.jp/app/Http/Controllers/CsShopPageController.php';
$t = @file_get_contents($f);
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
if ($t === false) { echo "✕ $f がありません\n"; exit(1); }
$note = "        if (\$inApp) {\n            \$h .= '<div class=\"note\">LINE・Instagram などのアプリの中で開いています。うまく進めないときは、右上のメニューから「ブラウザで開く」を選んでください。</div>';\n        }\n";
$rep = [
    'LINE などの案内を上から取る' => [$note, ''],
    'アイコンの下の「Card-San」をなくす' => [
        "<div class=\"logo app\"><img src=\"' . self::APP_ICON . '\" alt=\"Card-San\"></div><div class=\"an\">Card-San</div>';",
        "<div class=\"logo app\"><img src=\"' . self::APP_ICON . '\" alt=\"Card-San\"></div>'; // [カスタムエイド 2026-10-26b] モックどおり「Card-San」の文字はなし",
    ],
    '手順の枠をなくす' => [
        "        \$step2 = \$os == 'android' ? 'アプリを開いてアカウントを作る' : 'アプリを開いて「ペースト」を押す';\n"
        . "        \$step3 = \$os == 'android' ? 'お店のカードが自動で届きます' : 'アカウントを作るとカードが届きます';\n"
        . "        \$h .= '<div class=\"steps\"><div class=\"st\">カードを受け取るまで</div>'\n"
        . "            . '<div class=\"sp\"><i>1</i>下のボタンから Card-San を入れる(無料)</div>'\n"
        . "            . '<div class=\"sp\"><i>2</i>' . \$step2 . '</div><div class=\"sp\"><i>3</i>' . \$step3 . '</div></div>';\n",
        "        // [カスタムエイド 2026-10-26b] モックどおり「カードを受け取るまで」の手順の枠はなし\n",
    ],
    'iPhone のボタンの下の文' => [
        "<div class=\"tiny\">押すと、お店の情報をコピーしてから App Store が開きます</div>",
        "<div class=\"tiny\">インストール後、アプリの「ペースト」を押すと、<br>このお店のカードが表示されます。</div>",
    ],
    'LINE などの案内をいちばん下へ' => [
        "        \$h .= '<a class=\"lnk\" href=\"cardsan://shop/' . \$id . '\">アプリをお持ちの方はこちら</a>';\n",
        "        \$h .= '<a class=\"lnk\" href=\"cardsan://shop/' . \$id . '\">アプリをお持ちの方はこちら</a>';\n"
        . "        // [カスタムエイド 2026-10-26b] モックどおり、LINE・Instagram の中で開いたときの案内はいちばん下に\n" . $note,
    ],
];
$n = $t; $ok = true;
foreach ($rep as $label => [$a, $b]) {
    $c = substr_count($n, $a);
    if ($c !== 1) { echo "✕ $label:直す場所が {$c} か所(1か所のはず)\n"; $ok = false; continue; }
    $n = str_replace($a, $b, $n);
    echo "○ $label\n";
}
if (!$ok) { echo "\n問題があるので止めます。何も変更していません。\n"; exit(1); }
$tmp = '/tmp/customaid_shoppage_check.php';
file_put_contents($tmp, $n);
exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $rc);
@unlink($tmp);
if ($rc) { echo "✕ 直したあとの文に書き間違いがあります:" . implode(' ', $o) . "\n何も変更していません。\n"; exit(1); }
echo "○ 直したあとの文の書き方:問題なし\n";
if (!$apply) { echo "\n確認だけ終わりました。問題がなければ、最後に apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_shoppage_mock_' . date('Ymd_His') . '/';
mkdir($bk, 0700, true);
copy($f, $bk . 'CsShopPageController.php');
$st = stat($f);
file_put_contents($f, $n);
@chown($f, $st['uid']); @chgrp($f, $st['gid']); @chmod($f, $st['mode'] & 0777);
echo "○ 直しました。控え:$bk\n";
echo "確かめ方:iPhone の Safari で https://card-san.jp/s/11 を開く(1回再読み込み)\n";
echo "元に戻すとき:\\cp {$bk}CsShopPageController.php $f\n";
