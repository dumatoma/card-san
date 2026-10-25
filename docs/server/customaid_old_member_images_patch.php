<?php
// [カスタムエイド 2026-10-24] 以前の「画像」欄(会員の image)を片付ける
//   管理App のメンバー情報の「画像」欄は、カルテ機能に一本化したので使わなくなった。
//   1. image 欄に画像がある会員と枚数を表示する(確認だけ)
//   2. apply を付けたときだけ:
//      ・全員分の image 欄の中身をバックアップ(JSON)に書き出す
//      ・画像ファイルは消さずに、バックアップのフォルダへ移す(いざというとき戻せるように)
//      ・image 欄を空([])にする
//   使い方: php customaid_old_member_images_patch.php        … 確認だけ(何も変更しない)
//           php customaid_old_member_images_patch.php apply  … 変更する
$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_old_member_images_20261024/';
$apply = (($argv[1] ?? '') === 'apply');

require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$rows = DB::table('web_shop_member')->whereNotNull('image')->where('image', '!=', '[]')->where('image', '!=', '')->get(['id', 'image']);
$list = [];
$files = [];
foreach ($rows as $r) {
    $urls = json_decode((string) $r->image, true);
    if (!is_array($urls) || !$urls) continue;
    $list[] = ['id' => (int) $r->id, 'image' => $urls];
    foreach ($urls as $u) {
        // https://api.card-san.jp/storage/uploads/... → public/storage/uploads/...
        $p = parse_url((string) $u, PHP_URL_PATH);
        if (!$p || strpos($p, '/storage/') !== 0 || strpos($p, '..') !== false) { echo "  (この URL はサーバーのファイルではないので、そのままにします: $u)\n"; continue; }
        $rel = substr($p, strlen('/storage/'));
        $files[] = $rel;
    }
}
$n = 0;
foreach ($list as $m) { echo "会員ID {$m['id']}:" . count($m['image']) . "枚\n"; $n += count($m['image']); }
$exist = 0;
foreach ($files as $rel) if (is_file($base . 'public/storage/' . $rel)) $exist++;
echo "\n合計:" . count($list) . "人・{$n}枚(サーバーにあるファイル {$exist} 個)\n";
if (!$list) { echo "片付けるものはありません。何も変更していません。\n"; exit; }
if (!$apply) { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }

if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
if (file_put_contents($backup . 'web_shop_member_image.json', json_encode($list, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) === false) exit("バックアップに失敗しました。何も変更していません。\n");
$moved = 0;
foreach ($files as $rel) {
    $from = $base . 'public/storage/' . $rel;
    if (!is_file($from)) continue;
    $to = $backup . 'files/' . $rel;
    if (!is_dir(dirname($to))) mkdir(dirname($to), 0700, true);
    if (@rename($from, $to)) $moved++; else echo "  移せませんでした(そのまま残します): $rel\n";
}
$ids = array_map(function ($m) { return $m['id']; }, $list);
$u = DB::table('web_shop_member')->whereIn('id', $ids)->update(['image' => '[]']);
echo "完了しました。image 欄を空にした会員:{$u}人/移した画像:{$moved}個\nバックアップ:$backup(元に戻すときに使います)\n";
