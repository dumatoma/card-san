<?php
// [カスタムエイド 2026-10-29] SMS のリンク先のページに ①メニューの写真(予約完了の画面と同じ右側) ②店舗名の下に「最初に渡すカード」の絵 を出す。
//   あわせて、お客様のクーポン一覧に unread(詳細を開いていない=1)を足す(Card-San App で未読のクーポンに赤い枠と赤丸を付けるため)
//   すでに作ったページは作ったときの写しなので変わらない。この修正のあとに送る SMS のページから出る(最後に試験のページを4つ作る)
//   使い方: php customaid_smspage_photo_patch.php → 確認だけ / ... apply → 控えを取ってから変える(失敗したら元に戻す)
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b); $apply = ($argv[1] ?? '') === 'apply'; $T = '[カスタムエイド 2026-10-29]';
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$L = 'app/Libs/CsSmsPage.php'; $V = 'resources/views/cs_sms_page.blade.php'; $C = 'app/Http/Controllers/Api/Member/CouponController.php';
$fns = "    // $T メニューの1枚目の写真(写真がない・形が違うときは空)\n    public static function photo(\$img)\n    {\n        if (is_string(\$img)) { \$a = json_decode(\$img, true); \$img = is_array(\$a) ? \$a : [\$img]; }\n        \$u = is_array(\$img) ? (string) (\$img[0] ?? '') : '';\n        return preg_match('#^https?://#', \$u) ? \$u : '';\n    }\n\n    // $T お店の「最初に渡すカード」(メンバーズカード一覧の default)の絵。ないときは空\n    public static function card(\$sid)\n    {\n        try {\n            \$u = (string) DB::table('web_shop_card')->where('sid', (int) \$sid)->where('default', 1)->orderBy('id', 'desc')->value('image');\n            return preg_match('#^https?://#', \$u) ? \$u : '';\n        } catch (\\Throwable \$e) { return ''; }\n    }\n\n";
$css = ".scard{display:block;width:200px;max-width:60%;aspect-ratio:280/170;object-fit:cover;border-radius:10px;box-shadow:0 1px 6px rgba(0,0,0,.18);margin:-4px auto 4px;background:#fff}\n.mph{width:72px;height:72px;border-radius:10px;object-fit:cover;flex-shrink:0;align-self:center}\n</style>";
$jobs = [ // [ファイル, 名前, 今の文字, 新しい文字]
  [$L, 'メニューの写真を写しに入れる', "['メニュー', (string) (\$order->menu->title ?? ''), (string) \$order->amount],",
      "['メニュー', (string) (\$order->menu->title ?? ''), (string) \$order->amount, self::photo(\$order->menu->image ?? null)], // $T 4つ目=写真"],
  [$L, 'カードの絵を写しに入れる', "            'start_time' => (int) \$order->start_time,\n",
      "            'start_time' => (int) \$order->start_time,\n            'card' => self::card((int) (\$order->sid ?? 0)), // $T 最初に渡すカードの絵\n"],
  [$L, '写真・カードを探す部品', "    private static function code()\n", $fns . "    private static function code()\n"],
  [$V, '見た目(写真・カード)', "</style>", $css],
  [$V, '店舗名の下にカード', "    <div class=\"shop\">{{ \$shop['name'] }}</div>\n    <div class=\"card hero\">",
      "    <div class=\"shop\">{{ \$shop['name'] }}</div>\n    @if (!empty(\$p['card']))<img class=\"scard\" src=\"{{ \$p['card'] }}\" alt=\"{{ \$shop['name'] }}のカード\">@endif {{-- $T 最初に渡すカード --}}\n    <div class=\"card hero\">"],
  [$V, 'メニューの右に写真', "@if (!empty(\$r[2]))<span class=\"price\">{{ \$r[2] }}</span>@endif</div></div>",
      "@if (!empty(\$r[2]))<span class=\"price\">{{ \$r[2] }}</span>@endif</div>@if (!empty(\$r[3]))<img class=\"mph\" src=\"{{ \$r[3] }}\" alt=\"\">@endif</div>"],
  [$C, 'クーポン一覧に未読の印', "                    'id' => \$v->id,\n                    'images' => \$v->coupon->images,",
      "                    'id' => \$v->id,\n                    'unread' => \$v->cs_read_time ? 0 : 1, // $T 詳細を開いていない(アプリで赤い枠と赤丸)\n                    'images' => \$v->coupon->images,"],
];
$out = []; $ng = 0;
foreach ($jobs as $j) { list($f, $name, $old, $new) = $j; if (!isset($out[$f])) $out[$f] = file_get_contents($b . $f);
    $n = substr_count($out[$f], $old); echo ($n === 1 ? 'OK ' : 'NG ') . "$name($f):見つかった数 $n\n"; if ($n !== 1) { $ng++; continue; }
    $out[$f] = str_replace($old, $new, $out[$f]); }
foreach ([$L => 'function photo(', $C => "'unread'"] as $f => $k) if (strpos(file_get_contents($b . $f), $k) !== false) { echo "NG もう入っている($f)\n"; $ng++; }
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if (!$apply) { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_smspage_photo_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
foreach ($out as $f => $s) { copy($b . $f, $bk . basename($f)); file_put_contents($b . $f, $s); }
$undo = function () use ($b, $bk, $out) { foreach ($out as $f => $s) copy($bk . basename($f), $b . $f); };
$bad = 0; foreach ([$L, $C] as $f) { exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r); echo ($r === 0 ? '文法OK ' : '文法NG ') . "$f\n"; $o = []; if ($r !== 0) $bad++; }
if (!$bad) try { // 見本の写真・カードでページを作れるか(表には入れない)
  $rows = [['予約番号', 'UU0000000000000'], ['日時', '10月30日(金) 17:00 〜 18:00'], ['メニュー', 'カット', '9,000円', 'https://example.com/a.jpg'], ['担当者', '指名なし']];
  foreach (['confirm', 'cancel'] as $ty) { $p = ['type' => $ty, 'heading' => '【見出し】', 'name' => '見本 花子', 'honor' => '様', 'top' => '本文上', 'mid' => '', 'bottom' => '本文下', 'rows' => $rows, 'card' => 'https://example.com/c.jpg', 'shop' => ['sid' => 11, 'name' => 'CARD SALON', 'phone' => '03-0000-0000', 'address' => '東京都'], 'start_time' => time()];
    $h = view('cs_sms_page', ['p' => $p, 'state' => 'ok'])->render(); $okh = strpos($h, 'class="scard"') !== false && strpos($h, 'class="mph"') !== false;
    echo ($okh ? 'ページOK ' : 'ページNG ') . "$ty(写真・カードあり)\n"; if (!$okh) $bad++; }
  $p['card'] = ''; $p['rows'][2][3] = ''; $h = view('cs_sms_page', ['p' => $p, 'state' => 'ok'])->render(); echo "ページOK 写真・カードなし\n";
  $h = view('cs_sms_page', ['p' => ['type' => 'confirm', 'heading' => '', 'name' => '前の形', 'honor' => '様', 'top' => '', 'mid' => '', 'bottom' => '', 'rows' => [['メニュー', 'カット', '1円']], 'shop' => ['sid' => 11, 'name' => 'X', 'phone' => '', 'address' => '']], 'state' => 'ok'])->render(); echo "ページOK 前に作ったページ(写真・カードの欄なし)\n";
} catch (\Throwable $e) { echo "ページNG " . $e->getMessage() . "\n"; $bad++; }
exec('chown -R www:www ' . escapeshellarg($b . 'storage/framework/views'));
if ($bad) { $undo(); echo "\nうまくいかなかったので、元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "\n変えました。控え: $bk\n\n== 試験のページ(店舗11・予約409)==\n";
$o = App\Models\OrderModel::find(409);
if ($o && (int) $o->sid === 11) { $o->shop = App\Models\ShopModel::find(11);
  foreach (['confirm', 'notice', 'update', 'cancel'] as $t) { $c = App\Libs\CsSmsPage::make($t, $o, $o->name); echo str_pad(App\Libs\CsSmsPage::LABEL[$t], 8) . ':' . ($c ? App\Libs\CsSmsPage::base() . $c : '作れませんでした') . "\n"; }
  $p = json_decode(Illuminate\Support\Facades\DB::table('cs_sms_page')->orderBy('id', 'desc')->value('data'), true);
  echo "写真:" . ($p['rows'][2][3] ?? '') . "\nカード:" . ($p['card'] ?? '') . "\n"; }
