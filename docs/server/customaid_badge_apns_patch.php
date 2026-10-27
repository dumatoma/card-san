<?php
// [カスタムエイド 2026-10-27] Apple に直接送る通知にアイコンのバッジの数を入れる。確認だけ:引数なし(バッジの数を試しに計算して見せる)/反映:apply
$apply = ($argv[1] ?? '') === 'apply'; $b = '/www/wwwroot/api.card-san.jp/'; $php = '/www/server/php/73/bin/php';
echo $apply ? "=== 反映します ===\n" : "=== 確認だけ(何も変更しません) ===\n";
$src = '/root/customaid_CsBadge.php'; $dst = $b . 'app/Libs/CsBadge.php'; $f = $b . 'app/Libs/CsApns.php';
exec("$php -l " . escapeshellarg($src) . ' 2>&1', $o, $rc); if ($rc) { echo "✕ CsBadge の書き方に間違い\n"; exit(1); } echo "○ CsBadge 書き方 問題なし\n";
$old = "                \$json = json_encode(\$body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);\n                foreach ((array) (\$m['audience']['cid'] ?? []) as \$c) {\n                    \$tok = \$map[\$c] ?? '';\n                    if (!\$tok || isset(\$bad[\$tok])) continue;\n";
$new = "                foreach ((array) (\$m['audience']['cid'] ?? []) as \$c) {\n                    \$tok = \$map[\$c] ?? '';\n                    if (!\$tok || isset(\$bad[\$tok])) continue;\n                    // [カスタムエイド 2026-10-27] アイコンのバッジの数を入れる(個推の auto_badge は Apple では効かず、数が変わらなかった)\n                    \$b2 = \$body; \$bn = \\App\\Libs\\CsBadge::count(\$type, \$c); if (\$bn !== null) \$b2['aps']['badge'] = (int) \$bn;\n                    \$json = json_encode(\$b2, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);\n";
$t = file_get_contents($f);
if (strpos($t, 'CsBadge') !== false) { echo "○ CsApns.php もう直してあります\n"; $t2 = null; }
else { $c = substr_count($t, $old); if ($c !== 1) { echo "✕ CsApns.php 直す場所が {$c} か所(1か所のはず)。何も変更していません\n"; exit(1); }
  $t2 = str_replace($old, $new, $t); file_put_contents('/tmp/cs_apns_chk.php', $t2); exec("$php -l /tmp/cs_apns_chk.php 2>&1", $o2, $rc2); @unlink('/tmp/cs_apns_chk.php');
  if ($rc2) { echo "✕ CsApns.php 直したあとの書き方に間違い\n"; exit(1); } echo "○ CsApns.php 直す場所1か所・書き方 問題なし\n"; }
// 試しに計算(読むだけ)
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!class_exists('App\Libs\CsBadge', false)) require $src;
use Illuminate\Support\Facades\DB;
$try = function ($fn) { try { return var_export($fn(), true); } catch (\Throwable $e) { return '✕ エラー:' . mb_substr($e->getMessage(), 0, 150); } };
foreach (DB::table('web_user')->where('getui_clientid', '<>', '')->whereNotNull('cs_apns_token')->where('cs_apns_token', '<>', '')->limit(5)->get(['id', 'name', 'getui_clientid']) as $u)
  echo "  お客様 id={$u->id} {$u->name} → バッジ " . $try(function () use ($u) { return App\Libs\CsBadge::member($u->getui_clientid); }) . "\n";
foreach (DB::table('web_shop_admin')->where('getui_clientid', '<>', '')->whereNotNull('cs_apns_token')->where('cs_apns_token', '<>', '')->limit(5)->get(['id', 'sid', 'name', 'getui_clientid']) as $a)
  echo "  スタッフ id={$a->id} 店{$a->sid} {$a->name} → バッジ " . $try(function () use ($a) { return App\Libs\CsBadge::shop($a->getui_clientid); }) . "\n";
if (!$apply) { echo "\n確認だけ終わりました。上のバッジの数がアプリの数と合っていれば apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_badge_apns_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
$s = stat($f);
copy($src, $dst); @chown($dst, $s['uid']); @chgrp($dst, $s['gid']); echo "○ CsBadge.php を置きました\n";
if ($t2 !== null) { copy($f, $bk . 'CsApns.php'); file_put_contents($f, $t2); @chown($f, $s['uid']); @chgrp($f, $s['gid']); echo "○ CsApns.php を直しました\n"; }
echo "控え:$bk(元に戻すとき:控えの CsApns.php を戻し、$dst を消す)\n";
