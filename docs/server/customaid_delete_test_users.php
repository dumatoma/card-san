<?php
// [カスタムエイド 2026-10-26] 新規登録のテストのため、坂本さんのテスト用アカウント(お客さまアプリ)を消す
//   対象(坂本さんの指示):Apple ID snrikk1510@me.com / LINE で作ったアカウント(1つ)/ activist0825@gmail.com
//   ・確認だけ:対象のアカウントと、それにつながる記録(お店の会員など)の数を出す。何も消さない
//   ・反映    :消す前に全部の行をファイルに控えてから消す(控えから戻せる)
// 使い方:
//   確認だけ:php /root/customaid_delete_test_users.php
//   反映    :php /root/customaid_delete_test_users.php apply 160,158   ← 確認で出た id のうち、消すものだけをカンマで並べる
$apply = ($argv[1] ?? '') === 'apply';
$base = '/www/wwwroot/api.card-san.jp';
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
echo $apply ? "=== 消します ===\n" : "=== 確認だけ(何も消しません) ===\n";

$cands = DB::table('web_user')
    ->where('apple_email', 'like', '%1510@me.com%')
    ->orWhere('line_id', '<>', '')
    ->orWhere('google_email', 'activist0825@gmail.com')
    ->orWhere('name', 'like', '%activist0825%')
    ->get(['id', 'uuid', 'name', 'phone', 'apple_id', 'apple_email', 'line_id', 'line_name', 'google_email', 'create_time']);
echo "\n■ 対象の候補(web_user)\n";
foreach ($cands as $u) {
    $how = $u->apple_email ? 'Apple ' . $u->apple_email : ($u->line_id ? 'LINE ' . $u->line_name : ($u->google_email ? 'Google ' . $u->google_email : '?'));
    echo "  id={$u->id} 名前={$u->name} 電話={$u->phone} 方法={$how} 作成=" . date('Y-m-d H:i', $u->create_time) . "\n";
}

// web_user につながる表(uid / user_id の欄がある表)
$links = [];
foreach (DB::select('SHOW TABLES') as $r) {
    $t = array_values((array)$r)[0];
    if ($t === 'web_user') continue;
    $c = Schema::getColumnListing($t);
    foreach (['uid', 'user_id'] as $k) if (in_array($k, $c)) $links[] = [$t, $k];
}
$ids = $apply ? array_values(array_filter(array_map('intval', explode(',', $argv[2] ?? '')))) : $cands->pluck('id')->all();
if ($apply) {
    $ok = DB::table('web_user')->whereIn('id', $ids)->pluck('id')->all(); // [カスタムエイド 2026-10-26b] 候補にない id(Google で作ったものなど)も、坂本さんが指定すれば消せるように
    foreach (DB::table('web_user')->whereIn('id', $ids)->get(['id', 'name', 'phone', 'google_id', 'apple_id', 'line_id', 'create_time']) as $u) echo "  消す:id={$u->id} 名前={$u->name} 電話={$u->phone} 作成=" . date('Y-m-d H:i', $u->create_time) . "\n";
    if (!$ids || count($ok) !== count($ids)) { echo "\n✕ 消す id が、上の候補にないものを含んでいるか、空です。何も消していません。\n"; exit(1); }
}
echo "\n■ つながる記録の数(id " . implode(',', $ids) . ")\n";
$rows = [];
foreach ($links as [$t, $k]) {
    $n = $ids ? DB::table($t)->whereIn($k, $ids)->count() : 0;
    if ($n) { echo "  $t.$k:$n 件\n"; $rows[] = [$t, $k]; }
}
if (!$apply) { echo "\n確認だけ終わりました。消すアカウントの id を確かめて、例:php /root/customaid_delete_test_users.php apply 160,158 のように実行してください。\n"; exit(0); }

$bk = '/www/backup_customaid_delete_test_users_' . date('Ymd_His') . '/';
mkdir($bk, 0700, true);
$dump = ['web_user' => DB::table('web_user')->whereIn('id', $ids)->get()];
foreach ($rows as [$t, $k]) $dump["$t.$k"] = DB::table($t)->whereIn($k, $ids)->get();
file_put_contents($bk . 'rows.json', json_encode($dump, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "\n○ 消す前の行を控えました:{$bk}rows.json\n";
DB::transaction(function () use ($rows, $ids) {
    foreach ($rows as [$t, $k]) { $n = DB::table($t)->whereIn($k, $ids)->delete(); echo "  $t:$n 件を消しました\n"; }
    $n = DB::table('web_user')->whereIn('id', $ids)->delete();
    echo "  web_user:$n 件を消しました\n";
});
echo "○ 終わりました\n";
