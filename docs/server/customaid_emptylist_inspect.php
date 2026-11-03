<?php
// [カスタムエイド 2026-10-28] 「一覧が空になる」「会員証が undefined」の原因を、サーバーの記録で調べる(読むだけ。何も変更しない)。個人情報は出さない
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b);
function h($t) { echo "\n========== $t ==========\n"; }
$want = ['/api/shop/message' => '管理App メッセージ一覧', '/api/shop/member' => '管理App メンバー一覧', '/api/member/member/' => 'Card-San App 会員証'];
h('1. 通信の記録(nginx・直近3日分):結果の数字ごとの回数');
$logs = array_merge(glob('/www/wwwlogs/api.card-san.jp.log*') ?: []); echo "記録のファイル:" . implode(' ', array_map('basename', $logs)) . "\n";
$cnt = []; $bad = []; $since = time() - 3 * 86400;
foreach ($logs as $lf) { if (preg_match('/\.gz$/', $lf)) continue; $fh = fopen($lf, 'r'); if (!$fh) continue;
  while (($l = fgets($fh)) !== false) {
    if (!preg_match('/\[([^\]]+)\] "(GET|POST) ([^ ?"]+)(\?[^ "]*)? [^"]*" (\d{3}) (\d+)/', $l, $m)) continue;
    $t = strtotime(str_replace('/', ' ', preg_replace('/:/', ' ', $m[1], 1))); if ($t && $t < $since) continue;
    foreach ($want as $p => $name) { if (strpos($m[3], $p) !== 0) continue; if ($p == '/api/shop/member' && $m[3] != '/api/shop/member') continue;
      if ($p == '/api/shop/message' && strpos($m[4] ?? '', 'type=list') === false) continue;
      $k = "$name  結果=$m[5]"; $cnt[$k] = ($cnt[$k] ?? 0) + 1; if ($m[5] != '200' || (int) $m[6] < 120) $bad[] = date('m/d H:i:s', $t) . "  $name  結果=$m[5]  大きさ=$m[6]"; } } fclose($fh); }
ksort($cnt); foreach ($cnt as $k => $v) echo "$k : $v 回\n";
h('2. うまくいかなかった通信(結果が200以外、または中身がほぼ空)。新しい順に最大40件'); foreach (array_slice(array_reverse($bad), 0, 40) as $x) echo "$x\n";
h('3. サーバーのエラーの記録(直近3日・同じものはまとめる。数字は伏せる)');
$el = []; foreach (glob($b . 'storage/logs/laravel*.log') as $f) { if (filemtime($f) < $since) continue; foreach (file($f) as $l) if (preg_match('/^\[(\d{4}-\d\d-\d\d \d\d:\d\d):\d\d\] \w+\.ERROR: (.{0,140})/', $l, $m)) { $k = preg_replace(['/\d{4,}/', "/'[^']*'/"], ['#', "'?'"], $m[2]); $el[$k][] = $m[1]; } }
uasort($el, function ($a, $b) { return count($b) - count($a); }); foreach (array_slice($el, 0, 15, true) as $k => $v) echo count($v) . "回(最後 " . end($v) . ")  $k\n";
h('4. impcustom さんのアカウントのカード(会員証を開く元のデータ)');
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); $D = 'Illuminate\Support\Facades\DB';
$cols = array_map(function ($r) { return $r->Field; }, $D::select("SHOW COLUMNS FROM web_user")); $ec = array_values(array_filter($cols, function ($c) { return stripos($c, 'mail') !== false; }));
$u = null; foreach ($ec as $c) { $u = $D::table('web_user')->where($c, 'impcustom@gmail.com')->first(); if ($u) { echo "見つかった欄:$c  user id=$u->id\n"; break; } }
if (!$u) echo "見つかりません(探した欄:" . implode(',', $ec) . ")\n";
else foreach (\App\Models\ShopMemberModel::where('uid', $u->id)->get() as $m) { try { $card = $m->card; } catch (\Throwable $e) { $card = null; echo "カードを読めない:" . $e->getMessage() . "\n"; }
  echo "会員 id=$m->id 店=$m->sid カード=" . ($card ? 'あり(id=' . $card->id . ' 種類=' . ($card->type ?? '?') . ')' : 'なし') . " 名前=" . (trim((string) ($m->name ?? '')) === '' ? '空' : 'あり') . " スタンプ数=" . (is_countable($m->seals) ? count($m->seals) : "?") . "\n"; }
echo "\n(おわり)\n";
