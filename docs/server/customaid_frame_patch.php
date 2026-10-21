<?php
// [カスタムエイド 2026-10-20] メンバーズカードの「枠に画像を使う」機能をサーバーに入れる
//   1. カードの表(ShopCardModel の表)に frame_image(枠の画像のURL)と frame_fit(tile:柄としてならべる / cover:全体に広げる)を足す
//   2. 管理Appから frame_image が送られてきたときだけ、カードを保存するときに一緒に保存する(ShopCardModel の保存の合図で行う)
//   3. カードの情報を返すところで、background_color を返している所すべてに frame_image・frame_fit も足す
//
// 使い方:
//   php /root/customaid_frame_patch.php        … 何を変えるかを表示するだけ(何も変更しない)
//   php /root/customaid_frame_patch.php apply  … 実際に変更する(先にバックアップを取る)
// 何か1つでも想定と違えば、何も変更せずに終了します。

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_frame_20261020/';
$tag = '[カスタムエイド 2026-10-20]';
$apply = (($argv[1] ?? '') === 'apply');

// --- 1. 欄を足す(Laravel の設定でつなぐ) ---
require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
// 表の名前は、カードのモデル(ShopCardModel)から取り出す(表の名前の付け方を決め打ちしない)
$cardModel = new App\Models\ShopCardModel();
$db = $cardModel->getConnection();
$table = $db->getTablePrefix() . $cardModel->getTable();
echo "カードの表: $table\n";
$cols = array_map(function ($c) { return $c->Field; }, $db->select("SHOW COLUMNS FROM `$table`"));
if (!in_array('background_color', $cols)) exit("$table に background_color の欄がありません。何も変更していません。\n");
$needCols = !in_array('frame_image', $cols);
echo $needCols ? "① $table に frame_image・frame_fit の欄を足します\n" : "① 欄はもうあります(足しません)\n";

// --- 2. モデル ---
$modelFile = 'app/Models/ShopCardModel.php';
$model = file_get_contents($base . $modelFile);
if ($model === false) exit("読み込みに失敗しました: $modelFile\n何も変更していません。\n");
$newModel = $model;
if (strpos($model, $tag) === false) {
    if (preg_match('/function\s+boot(ed)?\s*\(/', $model)) exit("$modelFile にすでに boot があります。手で確認が必要です。何も変更していません。\n");
    $n = 0;
    $newModel = preg_replace('/(class ShopCardModel extends \w+\s*\{\n)/', "$1" . <<<PHP
    // $tag 枠の画像。管理Appから frame_image が送られてきたときだけ、一緒に保存する(古いアプリ・管理ウェブからの保存では変えない)
    protected static function boot()
    {
        parent::boot();
        static::saving(function (\$card) {
            if (request()->has('frame_image')) {
                \$card->frame_image = (string) request('frame_image');
                \$card->frame_fit = request('frame_fit') == 'cover' ? 'cover' : 'tile';
            }
        });
    }


PHP
    , $model, 1, $n);
    if ($n !== 1) exit("変更する場所が見つかりません($modelFile)。何も変更していません。\n");
    echo "② $modelFile に保存の合図を足します\n";
} else {
    echo "② $modelFile はもう変更済みです\n";
}

// --- 3. カードの情報を返すところ ---
$changes = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base . 'app/Http/Controllers'));
foreach ($it as $f) {
    if (substr($f, -4) !== '.php') continue;
    $src = file_get_contents($f);
    if (strpos($src, "'background_color'") === false || strpos($src, "'frame_image'") !== false) continue;
    $cnt = 0;
    $new = preg_replace_callback(
        "/^(\s*)'background_color'\s*=>\s*([^\n]+?)(,?)(\s*(\/\/[^\n]*)?)$/m",
        function ($m) use ($tag, &$cnt) {
            $expr = rtrim($m[2]);
            // 例: \$card->background_color / \$v->card->background_color / \$item['background_color']
            if (preg_match('/^(.*)->background_color$/', $expr, $x)) {
                $img = $x[1] . '->frame_image ?? \'\'';
                $fit = $x[1] . '->frame_fit ?? \'tile\'';
            } elseif (preg_match('/^(.*)\[\'background_color\'\]$/', $expr, $x)) {
                $img = $x[1] . '[\'frame_image\'] ?? \'\'';
                $fit = $x[1] . '[\'frame_fit\'] ?? \'tile\'';
            } else {
                return $m[0]; // 形が違うものは触らない
            }
            $cnt++;
            return $m[1] . "'background_color' => " . $expr . "," . $m[4] . "\n"
                . $m[1] . "'frame_image' => " . $img . ", // $tag 枠の画像\n"
                . $m[1] . "'frame_fit' => " . $fit . ($m[3] === ',' ? ',' : '');
        },
        $src
    );
    if ($cnt > 0) {
        $changes[substr($f, strlen($base))] = $new;
        echo "③ " . substr($f, strlen($base)) . " … {$cnt}か所に frame_image・frame_fit を足します\n";
    }
}
if (!$changes) echo "③ background_color を個別に返している所は見つかりませんでした(カードをまるごと返している場合は、欄を足すだけで返るようになります)\n";

if (!$apply) {
    echo "\n(表示だけです。何も変更していません。問題なければ apply を付けて実行してください)\n";
    exit;
}

// --- 実行:バックアップ → 変更 ---
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
$files = array_merge($newModel !== $model ? [$modelFile] : [], array_keys($changes));
foreach ($files as $f) {
    $dst = $backup . str_replace('/', '__', $f);
    if (!copy($base . $f, $dst)) exit("バックアップに失敗しました: $f\n何も変更していません。\n");
}
if ($needCols) {
    $db->statement("ALTER TABLE `$table` ADD `frame_image` VARCHAR(500) NOT NULL DEFAULT '' AFTER `background_color`, ADD `frame_fit` VARCHAR(10) NOT NULL DEFAULT 'tile' AFTER `frame_image`");
}
if ($newModel !== $model) file_put_contents($base . $modelFile, $newModel);
foreach ($changes as $f => $src) file_put_contents($base . $f, $src);

echo "書き込みました。バックアップ: $backup\n";
echo "次に、文法の確認を行ってください。\n";
