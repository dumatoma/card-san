<?php
// [カスタムエイド 2026-10-20] お店を探す・Nearby の条件を見直す
//   ・テスト用/サンプルのお店(hide_in_search=1)と、契約が切れたお店は出さない
//   ・何も入力していないとき:近くのお店だけ(お客様の郵便番号 → My Card の並び順のお店の郵便番号、の上3桁。地域ごとに1件ずつ交互)
//   ・店名を入力したとき:全国から(上の除外は同じ)
//   ・Nearby:同じ条件で、すでに持っているお店を除き最大20件
// 実行: php /root/customaid_nearby_patch.php
// 先に web_shop に hide_in_search の欄を足してから実行すること(手順の③)。
// 何か1つでも想定と違えば、何も変更せずに終了します。

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_nearby_20261020/';
$tag = '[カスタムエイド 2026-10-20]';
$f = 'app/Http/Controllers/Api/Member/CardController.php';

$src = file_get_contents($base . $f);
if ($src === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($src, $tag) !== false) exit("すでに修正済みです。何も変更していません。\n");

// 1. お店を探す(一覧):一覧の作り方を差し替える
$n = 0;
$src = preg_replace_callback(
    '/        \$model = ShopCardModel::with\(\'shop\'\)->where\(\'default\', 1\)->has\(\'shop\'\);.*?\$cards = \$model->paginate\(\$request->page_size \?\? 100\);/s',
    function () use ($tag) {
        return <<<PHP
        // $tag テスト用・サンプル・契約が切れたお店は出さない。
        // 店名を入力したときは全国から、何も入力していないときは近くのお店だけ(Nearby と同じ条件・持っているお店も含む)
        \$keyword = trim((string) \$request->keyword);
        if (\$keyword !== '') {
            \$cards = ShopCardModel::with('shop')->where('default', 1)
                ->whereHas('shop', function (\$q) {
                    \$this->activeShop(\$q);
                })
                ->where(function (\$query) use (\$keyword) {
                    \$query->where('name', 'like', '%' . \$keyword . '%')->orWhere('shop_name', 'like', '%' . \$keyword . '%');
                })
                ->orderBy('id')->limit(50)->get();
        } else {
            \$cards = \$this->nearbyCards(auth()->user(), false, 50);
        }
PHP;
    },
    $src, 1, $n
);
if ($n !== 1) exit("変更する場所が見つかりません(一覧)。何も変更していません。\n");

$from = "return responseToJson(200, '', ['cards' => \$data, 'total' => \$cards->total()]);";
if (substr_count($src, $from) !== 1) exit("変更する場所が見つかりません(一覧の返し方)。何も変更していません。\n");
$src = str_replace($from, "return responseToJson(200, '', ['cards' => \$data, 'total' => count(\$data)]); // $tag", $src);

// 2. Nearby を差し替え、共通の部品を足す
$n = 0;
$src = preg_replace_callback(
    '/    public function nearby\(Request \$request\)\s*\{.*?return responseToJson\(200, \'\', \[\'cards\' => array_values\(\$cards\)\]\);\s*\}/s',
    function () use ($tag) {
        return <<<PHP
    // $tag Nearby:近くのお店(すでに持っているお店は除く)を最大20件
    public function nearby(Request \$request)
    {
        \$data = [];
        foreach (\$this->nearbyCards(auth()->user(), true, 20) as \$v) {
            \$data[] = [
                'id' => \$v->id,
                'sid' => \$v->sid,
                'shop_name' => \$v->shop_name,
                'image' => \$v->shop->carousel[0] ?? '',
                'shop_cate_name' => webConfig()->type('shop_cate')[\$v->shop->shop_cate] ?? ''
            ];
        }

        return responseToJson(200, '', ['cards' => \$data]);
    }

    // $tag 検索・Nearby に出してよいお店:検索に出さない印がなく、契約が有効
    private function activeShop(\$q)
    {
        \$q->where('hide_in_search', 0)->whereHas('vip', function (\$v) {
            \$v->where('start_time', '<=', time())->where('end_time', '>=', time())->where('status', 1);
        });
    }

    // $tag 近くのお店のカード。郵便番号の上3桁を
    //   ① お客様のアカウントの郵便番号 → ② My Card の並び順のお店の郵便番号 の順に集め(重なりは1つに)、
    //   地域ごとに1件ずつ交互に並べる。同じお店は1回だけ
    private function nearbyCards(\$user, \$excludeHeld, \$limit)
    {
        if (!\$user) {
            return collect([]);
        }
        \$members = ShopMemberModel::with('shop')->has('shop')->where('uid', \$user->id)->orderBy('sort')->orderBy('id')->get();

        \$zips = [(string) \$user->zip_code];
        foreach (\$members as \$m) {
            \$zips[] = (string) (\$m->shop->zip_code ?? '');
        }
        \$prefixes = [];
        foreach (\$zips as \$z) {
            \$d = preg_replace('/\\D/', '', \$z);
            if (strlen(\$d) >= 3 && !in_array(substr(\$d, 0, 3), \$prefixes, true)) {
                \$prefixes[] = substr(\$d, 0, 3);
            }
        }
        if (!\$prefixes) {
            return collect([]);
        }

        \$held = \$members->pluck('sid')->all();
        \$groups = [];
        foreach (\$prefixes as \$p) {
            \$groups[] = ShopCardModel::with('shop')->where('default', 1)
                ->whereHas('shop', function (\$q) use (\$p, \$excludeHeld, \$held) {
                    \$this->activeShop(\$q);
                    \$q->where('zip_code', 'like', \$p . '%');
                    if (\$excludeHeld && \$held) {
                        \$q->whereNotIn('id', \$held);
                    }
                })
                ->orderBy('id')->limit(\$limit)->get()->all();
        }

        \$out = [];
        \$seen = [];
        for (\$i = 0; count(\$out) < \$limit; \$i++) {
            \$any = false;
            foreach (\$groups as \$g) {
                if (!isset(\$g[\$i])) {
                    continue;
                }
                \$any = true;
                \$c = \$g[\$i];
                if (isset(\$seen[\$c->sid])) {
                    continue;
                }
                \$seen[\$c->sid] = 1;
                \$out[] = \$c;
                if (count(\$out) >= \$limit) {
                    break;
                }
            }
            if (!\$any) {
                break;
            }
        }

        return collect(\$out);
    }
PHP;
    },
    $src, 1, $n
);
if ($n !== 1) exit("変更する場所が見つかりません(Nearby)。何も変更していません。\n");

// バックアップ → 書き込み
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
if (!copy($base . $f, $backup . basename($f))) exit("バックアップに失敗しました。何も変更していません。\n");
file_put_contents($base . $f, $src);

echo "書き込みました。バックアップ: $backup\n";
echo "次に、文法の確認(手順の⑤)を行ってください。\n";
