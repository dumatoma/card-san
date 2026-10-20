<?php
// [カスタムエイド 2026-10-20] お客様アプリの「予約中」「来店履歴」の窓口を新設し、メニューの削除を「削除済みの印」に変える
// 実行: php /root/customaid_history_patch.php
// 先に web_shop_menu に deleted_at の欄を足してから実行すること(手順書の②)。
// 何か1つでも想定と違えば、何も変更せずに終了します。

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_history_20261020/';
$tag = '[カスタムエイド 2026-10-20]';
$files = [
    'model'  => 'app/Models/ShopMenuModel.php',
    'menu'   => 'app/Http/Controllers/Api/Member/MenuController.php',
    'routes' => 'routes/api.php',
];
$newFile = 'app/Http/Controllers/Api/Member/HistoryController.php';

if (file_exists($base . $newFile)) exit("すでに $newFile があります。何も変更していません。\n");

$src = [];
foreach ($files as $k => $f) {
    $src[$k] = file_get_contents($base . $f);
    if ($src[$k] === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
    if (strpos($src[$k], $tag) !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
}

function must_replace(&$s, $from, $to, $label) {
    if (substr_count($s, $from) !== 1) exit("変更する場所が見つかりません($label)。何も変更していません。\n");
    $s = str_replace($from, $to, $s);
}

// 1. メニュー:削除済みの印(SoftDeletes)
must_replace($src['model'], "use Illuminate\\Database\\Eloquent\\Model;\n",
    "use Illuminate\\Database\\Eloquent\\Model;\nuse Illuminate\\Database\\Eloquent\\SoftDeletes; // $tag\n", 'model-use');
$n = 0;
$src['model'] = preg_replace('/(class ShopMenuModel extends Model\s*\{\n)/', "$1    // $tag 削除してもデータを消さず「削除済み」の印(deleted_at)を付ける。ふつうの一覧からは自動で除かれる\n    use SoftDeletes;\n\n", $src['model'], 1, $n);
if ($n !== 1) exit("変更する場所が見つかりません(model-class)。何も変更していません。\n");

// 2. お気に入り:削除済みのメニューも返し、印を付ける
must_replace($src['menu'], "\$model = \$model->whereIn('id', auth()->user()->collect_mids);",
    "\$model = \$model->withTrashed()->whereIn('id', auth()->user()->collect_mids); // $tag お気に入りは削除済みのメニューも返す", 'menu-collect');
must_replace($src['menu'], "'is_order' => (\$vip->type ?? 0) == 3 ? \$menu->is_order : 0,",
    "'is_order' => ((\$vip->type ?? 0) == 3 && !\$menu->trashed()) ? \$menu->is_order : 0, // $tag 削除済みは予約不可", 'menu-is_order');
must_replace($src['menu'], "'mid' => \$member->id ?? 0\n",
    "'mid' => \$member->id ?? 0,\n                'deleted' => \$menu->trashed() ? 1 : 0 // $tag お店が削除したメニュー\n", 'menu-deleted');

// 3. 窓口を追加(ログインが必要な所)
must_replace($src['routes'], "Route::get('/card/nearby', 'CardController@nearby');",
    "Route::get('/card/nearby', 'CardController@nearby');\n\n            // $tag 予約中・来店履歴\n            Route::get('/history/orders', 'HistoryController@orders');\n            Route::get('/history/comes', 'HistoryController@comes');", 'routes');

// 4. 新しい窓口のプログラム
$history = <<<'PHP'
<?php
// [カスタムエイド 2026-10-20] お客様アプリの「予約中」「来店履歴」
//   GET /api/member/history/orders?sid=  … これからの予約(sid なしで全店舗)。日時の近い順
//   GET /api/member/history/comes?sid=   … 来店履歴(365日以内)。新しい順

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShopMemberModel;
use App\Models\OrderModel;
use App\Models\ComeLogModel;
use App\Models\ShopMenuModel;
use App\Models\ShopCardModel;
use App\Models\ShopModel;

class HistoryController extends Controller
{
    // ログイン中のお客様の、お店ごとの会員
    private function members(Request $request)
    {
        $model = ShopMemberModel::where('uid', auth()->id());
        if ($request->sid) {
            $model = $model->where('sid', $request->sid);
        }
        return $model->get(['id', 'sid', 'cid']);
    }

    // そのお店が予約を使えるプラン(プレミアム)か。Member/MenuController と同じ判定
    private function canOrder($sid, &$cache)
    {
        if (array_key_exists($sid, $cache)) {
            return $cache[$sid];
        }
        $shop = ShopModel::find($sid);
        $vip = $shop ? $shop->vip()
            ->where('start_time', '<=', time())
            ->where('end_time', '>=', time())
            ->where('status', 1)
            ->orderBy('create_time', 'desc')->first() : null;
        return $cache[$sid] = (($vip->type ?? 0) == 3);
    }

    private function firstImage($image)
    {
        if (is_array($image)) {
            return count($image) ? (string) reset($image) : '';
        }
        return is_string($image) ? $image : '';
    }

    private function week($t)
    {
        $w = ['日', '月', '火', '水', '木', '金', '土'];
        return $w[date('w', $t)];
    }

    public function orders(Request $request)
    {
        $members = $this->members($request);
        if ($members->isEmpty()) {
            return responseToJson(200, '', ['orders' => [], 'total' => 0]);
        }
        $byId = $members->keyBy('id');
        $cards = ShopCardModel::whereIn('id', $members->pluck('cid')->filter()->values()->all())->get()->keyBy('id');

        $orders = OrderModel::with(['shop', 'menu'])
            ->whereIn('mid', $members->pluck('id')->all())
            ->where('end_time', '>=', time())
            ->orderBy('start_time')
            ->limit(100)
            ->get();

        $data = [];
        foreach ($orders as $o) {
            $m = $byId[$o->mid] ?? null;
            $card = ($m && $m->cid) ? ($cards[$m->cid] ?? null) : null;
            $menu = $o->menu;
            $staff = '';
            if ($o->appoint_said) {
                $staff = $o->appoint_admin_detail['name'] ?? '';
            }
            if ($staff === '' && $o->day_said) {
                $staff = $o->day_admin_detail['name'] ?? '';
            }
            $data[] = [
                'id' => $o->id,
                'sid' => $o->sid,
                'shop_name' => $o->shop->shop_name ?? '',
                'card_image' => $card ? $this->firstImage($card->image) : '',
                'smid' => $o->smid,
                'menu_name' => $o->menu_name ?: ($menu->title ?? ''),
                'menu_image' => $menu ? $this->firstImage($menu->image) : '',
                'date' => date('Y/m/d', $o->start_time),
                'week' => $this->week($o->start_time),
                'start' => date('H:i', $o->start_time),
                'end' => date('H:i', $o->end_time),
                'staff' => $staff,
            ];
        }

        return responseToJson(200, '', ['orders' => $data, 'total' => count($data)]);
    }

    public function comes(Request $request)
    {
        $members = $this->members($request);
        if ($members->isEmpty()) {
            return responseToJson(200, '', ['comes' => [], 'total' => 0]);
        }

        $logs = ComeLogModel::whereIn('mid', $members->pluck('id')->all())
            ->where('date', '>=', strtotime('-365 days'))
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(200)
            ->get();

        $planCache = [];
        $menuCache = [];
        $data = [];
        foreach ($logs as $log) {
            $sid = $log->sid;
            if (!isset($menuCache[$sid])) {
                // 来店履歴はメニュー名で残っているので、今のメニュー(削除済みは除く)から名前で探す
                $menuCache[$sid] = ShopMenuModel::where('sid', $sid)->get(['id', 'title', 'image', 'is_order'])->keyBy('title');
            }
            $can = $this->canOrder($sid, $planCache);

            $menus = [];
            foreach ((array) ($log->menu_names ?? []) as $name) {
                if (!is_string($name) || $name === '') {
                    continue;
                }
                $mm = $menuCache[$sid][$name] ?? null;
                $menus[] = [
                    'name' => $name,
                    'id' => $mm->id ?? 0,
                    'image' => $mm ? $this->firstImage($mm->image) : '',
                    'can_order' => ($can && $mm && $mm->is_order == 1) ? 1 : 0,
                ];
            }

            $clean = function ($list) {
                $out = [];
                foreach ((array) ($list ?? []) as $v) {
                    if (is_string($v) && $v !== '' && !in_array($v, $out, true)) {
                        $out[] = $v;
                    }
                }
                return $out;
            };

            $data[] = [
                'id' => $log->id,
                'sid' => $sid,
                'date' => date('Y/m/d', $log->date),
                'week' => $this->week($log->date),
                'menus' => $menus,
                'staff' => $clean($log->day_sadmin_names),
                'coupons' => $clean($log->coupon_names),
            ];
        }

        return responseToJson(200, '', ['comes' => $data, 'total' => count($data)]);
    }
}
PHP;

// バックアップ → 書き込み
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($files as $k => $f) {
    if (!copy($base . $f, $backup . basename($f))) exit("バックアップに失敗しました: $f\n何も変更していません。\n");
}
foreach ($files as $k => $f) {
    file_put_contents($base . $f, $src[$k]);
}
file_put_contents($base . $newFile, $history);
@chown($base . $newFile, 'www');
@chgrp($base . $newFile, 'www');

echo "書き込みました。バックアップ: $backup\n";
echo "次に、文法の確認(手順書の④)を行ってください。\n";
