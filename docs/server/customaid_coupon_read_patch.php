<?php
// [カスタムエイド 2026-10-28] クーポンの未読(バッジ)を「1枚ずつ、詳細を開いたら既読」にする(坂本さんの決定)。
//   以前は「最後にクーポン一覧を見た時刻」より新しいものを未読にしていたので、一覧を開いただけで全部消えていた。
//   ① web_member_coupon に欄 cs_read_time(開いた時刻)を足す ② 今までに一覧で見たクーポンは既読として引き継ぐ
//   ③ 詳細(show)を開いたら、そのクーポンだけ既読 ④ 未読の数え方(4か所)を「使える・使い切っていない・まだ開いていない」にそろえる
//   使い方: php customaid_coupon_read_patch.php → 確認だけ / php customaid_coupon_read_patch.php apply → 控えを取ってから変える
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b); $apply = ($argv[1] ?? '') === 'apply'; $T = '[カスタムエイド 2026-10-28]';
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$D = 'Illuminate\Support\Facades\DB'; $tc = (new App\Models\MemberCouponModel)->getTable(); $tm = (new App\Models\ShopMemberModel)->getTable();
$has = count($D::select("SHOW COLUMNS FROM `$tc` LIKE 'cs_read_time'")) > 0;
echo ($has ? 'NG 欄 cs_read_time はもうある' : "OK 欄 cs_read_time を $tc に足す") . "\n";
$old = $D::select("SELECT COUNT(*) n FROM `$tc` c JOIN `$tm` m ON m.id = c.mid WHERE c.create_time <= m.read_coupon_time")[0]->n;
$new = $D::select("SELECT COUNT(*) n FROM `$tc` c JOIN `$tm` m ON m.id = c.mid WHERE c.create_time > m.read_coupon_time")[0]->n;
echo "   今までに一覧で見たクーポン(既読として引き継ぐ):$old 枚 / まだ見ていないクーポン(未読のまま):$new 枚\n";
$cnt = "(?:\\\\App\\\\Models\\\\)?MemberCouponModel::where\\('sid', \\$(\\w+)->sid\\)->where\\('mid', \\$\\1->id\\)->where\\('create_time', '>', \\$\\1->read_coupon_time\\)->count\\(\\)";
$jobs = [
  ['app/Libs/CsBadge.php', '/' . $cnt . '/', function ($m) use ($T) { return '\App\Libs\CsCouponUnread::count($' . $m[1] . '->sid, $' . $m[1] . '->id) /* ' . $T . ' 詳細を開いていないクーポン */'; }, 1],
  ['app/Http/Controllers/Api/Member/MemberController.php', '/' . $cnt . '/', function ($m) use ($T) { return '\App\Libs\CsCouponUnread::count($' . $m[1] . '->sid, $' . $m[1] . '->id) /* ' . $T . ' 詳細を開いていないクーポン */'; }, 2],
  ['app/Services/PushBadgeService.php', '/' . $cnt . '/', function ($m) use ($T) { return '\App\Libs\CsCouponUnread::count($' . $m[1] . '->sid, $' . $m[1] . '->id) /* ' . $T . ' 詳細を開いていないクーポン */'; }, 1],
  ['app/Http/Controllers/Api/Member/CouponController.php', "/\\\$model->where\\('create_time', '>', \\\$member->read_coupon_time\\)->count\\(\\)/", function ($m) use ($T) { return "\$model->whereNull('cs_read_time')->count() /* $T 詳細を開いていないクーポン */"; }, 1],
  ['app/Http/Controllers/Api/Member/CouponController.php', "/(\\n {8})(\\\$file_name = \\\$coupon->id \\. '-' \\. time\\(\\))/", function ($m) use ($T) { return $m[1] . "// $T 詳細を開いたら、このクーポンだけ既読(バッジが1つ減る)" . $m[1] . "\\Illuminate\\Support\\Facades\\DB::table(\$coupon->getTable())->where('id', \$coupon->id)->whereNull('cs_read_time')->update(['cs_read_time' => time()]);" . $m[1] . $m[2]; }, 1],
];
$lib = $b . 'app/Libs/CsCouponUnread.php';
$libSrc = "<?php\n\nnamespace App\\Libs;\n\n// $T お客様のクーポンの未読の数。クーポン一覧に出るもの(使える期間内・使い切っていない)のうち、詳細をまだ開いていないもの\nclass CsCouponUnread\n{\n    public static function count(\$sid, \$mid)\n    {\n        return \\App\\Models\\MemberCouponModel::whereHas('coupon', function (\$q) {\n            \$q->where(function (\$q) {\n                \$q->where('is_indate', 0)->orWhere(function (\$q) {\n                    \$q->where('is_indate', 1)->where('indate_start_time', '<=', time())->where('indate_end_time', '>=', time());\n                });\n            });\n        })->where('sid', \$sid)->where('mid', \$mid)->where(function (\$q) {\n            \$q->where('count', 0)->orWhereRaw('count > use_count');\n        })->whereNull('cs_read_time')->count();\n    }\n}\n";
$out = []; $ng = $has ? 1 : 0;
foreach ($jobs as $j) { list($f, $re, $fn, $want) = $j; if (!isset($out[$f])) $out[$f] = file_get_contents($b . $f);
  $n = preg_match_all($re, $out[$f]); echo ($n === $want ? 'OK ' : 'NG ') . "$f:見つかった数 $n(あるはずの数 $want)\n"; if ($n !== $want) { $ng++; continue; }
  $out[$f] = preg_replace_callback($re, $fn, $out[$f]); }
echo (is_file($lib) ? 'NG 部品 CsCouponUnread.php はもうある' : 'OK 部品 CsCouponUnread.php を新しく作る') . "\n"; if (is_file($lib)) $ng++;
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if (!$apply) { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_coupon_read_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true);
foreach (array_keys($out) as $f) copy($b . $f, $bk . basename($f));
$D::statement("ALTER TABLE `$tc` ADD COLUMN `cs_read_time` INT UNSIGNED NULL DEFAULT NULL COMMENT '$T 詳細を開いた時刻(未読=NULL)'");
$k = $D::update("UPDATE `$tc` c JOIN `$tm` m ON m.id = c.mid SET c.cs_read_time = m.read_coupon_time WHERE c.create_time <= m.read_coupon_time");
echo "欄を足しました。既読として引き継いだクーポン:$k 枚\n";
file_put_contents($lib, $libSrc); foreach ($out as $f => $s) file_put_contents($b . $f, $s);
$bad = 0; foreach (array_merge(array_keys($out), ['app/Libs/CsCouponUnread.php']) as $f) { exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r); echo ($r === 0 ? '文法OK ' : '文法NG ') . "$f\n"; $o = []; if ($r !== 0) $bad++; }
if ($bad) { foreach (array_keys($out) as $f) copy($bk . basename($f), $b . $f); unlink($lib); echo "\n文法の誤りがあったので、プログラムを元に戻しました(足した欄は残っていますが、使われません)。この結果を Claude に送ってください。\n"; exit(1); }
echo "\n変えました。控え: $bk\n";
