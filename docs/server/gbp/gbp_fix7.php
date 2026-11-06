<?php
// [カスタムエイド 2026-11-07] GBP 連携 その7(サーバー):Card-San のメニューを Google の「サービス」に出す
//   ・Googleビジネス連携の自動同期の項目に「メニュー」(menu)を足す
//   ・同期のとき、Card-San のメニュー(消したものは除く・最大100件)で Google のサービスの一覧を置き換える
//       名前(140字まで)・説明(250字まで)・値段(最初の数字だけ。「3,000円〜」→ 3,000円)。所要時間・分類は出さない
//   ・Google の業種でサービスを変えられないお店(canModifyServiceList が false)は出さずに知らせる
//   ・メニューが1件もないときは送らない(Google のサービスを空にしないため)
//   ・同じ内容は送り直さない(Redis に印を残す)。メニューの失敗は営業時間・写真の同期を止めない
//
//   php -d disable_functions= /root/gbp_fix7.php             … 確かめるだけ(何も変えない)
//   php -d disable_functions= /root/gbp_fix7.php apply       … バックアップを取ってから変える
//   php -d disable_functions= /root/gbp_fix7.php undo        … 元に戻す
//   php -d disable_functions= /root/gbp_fix7.php test 店舗ID  … (apply のあと)Google に送る中身を見るだけ(送らない)

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_gbpfix7_20261107/';
$mode = $argv[1] ?? 'check';
$MARK = 'カスタムエイド 2026-11-07 GBP7';
$F = ['svc' => 'app/Services/GbpService.php'];
if ($mode === 'undo') {
    $n = 0;
    foreach ($F as $f) { $b = $backup . str_replace('/', '__', $f); if (is_file($b)) { copy($b, $base . $f); $n++; } }
    if (!$n) exit("バックアップがありません。何もしていません。\n");
    @exec('/etc/init.d/php-fpm-73 reload 2>&1');
    exit("元に戻しました({$n} ファイル)。\n");
}
if ($mode === 'test') {
    $sid = (int) ($argv[2] ?? 0);
    if (!$sid) exit("店舗ID を付けてください(例: php -d disable_functions= /root/gbp_fix7.php test 11)\n");
    require $base . 'vendor/autoload.php';
    $app = require $base . 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $shop = App\Models\ShopModel::find($sid);
    if (!$shop) exit("店舗ID {$sid} がありません。\n");
    $r = (new App\Services\GbpService())->menuPreview($shop);
    foreach ($r as $k => $v) echo $k . ': ' . (is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $v) . "\n";
    exit("\n見るだけでした(Google には何も送っていません)。\n");
}
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
$src = [];
foreach ($F as $k => $f) { $src[$k] = file_get_contents($base . $f); if (strpos($src[$k], $MARK) !== false) $ng("すでに直してあります({$f})"); }
if (strpos($src['svc'], 'カスタムエイド 2026-11-06 GBP6') === false) $ng('先に gbp_fix6.php を apply してください');

$E = [];
$E['svc'][] = ["    const FIELDS = ['hours', 'photos'];",
               "    const FIELDS = ['hours', 'photos', 'menu']; // [{$MARK}] メニュー(Google のサービス)を足した\n    const FIELDS_OLD ="];
// FIELDS_OLD は元の行の残り(コメント)を受けるだけの印。下で消す
$E['svc'][] = ["        if (!\$mask && !in_array('photos', \$fields)) {\n",
               "        if (!\$mask && !in_array('photos', \$fields) && !in_array('menu', \$fields)) { // [{$MARK}] メニューだけでも同期する\n"];
$E['svc'][] = ["        if (in_array('photos', \$fields)) {\n            \$added = 0;\n",
               "        if (in_array('menu', \$fields)) \$detail['menu'] = \$this->syncMenu(\$shop, \$tk['token'], \$onlyIfChanged); // [{$MARK}]\n\n"
             . "        if (in_array('photos', \$fields)) {\n            \$added = 0;\n"];
$E['svc'][] = ["    /* ======================= クチコミ ======================= */\n", <<<'N'
    /* ======================= [カスタムエイド 2026-11-07 GBP7] メニュー → Google のサービス ======================= */

    /** Card-San のメニュー(消したものは除く・最大100件) */
    public static function menuRows($sid)
    {
        return DB::table('web_shop_menu as m')
            ->leftJoin('web_shop_menu_cate as c', 'c.id', '=', 'm.cid')
            ->where('m.sid', $sid)->whereNull('m.deleted_at')
            ->orderBy('c.sort')->orderBy('m.sort')->orderBy('m.id')
            ->limit(100)->get(['m.id', 'm.title', 'm.des', 'm.price'])->all();
    }

    /** 値段の文字から最初の数字だけ(「3,000円〜」→ 3000)。なければ 0 */
    public static function menuPrice($p)
    {
        $p = mb_convert_kana((string) $p, 'n');
        return preg_match('/\d[\d,]*/', $p, $m) ? (int) str_replace(',', '', $m[0]) : 0;
    }

    /** Google に送るサービスの一覧 */
    public static function serviceItems(array $rows, $category, $catKey = 'category')
    {
        $items = []; $seen = [];
        foreach ($rows as $r) {
            $name = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $r->title)));
            if ($name === '' || isset($seen[$name])) continue;
            $seen[$name] = 1;
            $label = ['displayName' => mb_substr($name, 0, 140), 'languageCode' => 'ja'];
            $des = trim(preg_replace('/[ \t]+/u', ' ', strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", (string) $r->des))));
            if ($des !== '') $label['description'] = mb_substr($des, 0, 250);
            $it = ['isOffered' => true, 'freeFormServiceItem' => [$catKey => $category, 'label' => $label]];
            $yen = self::menuPrice($r->price);
            if ($yen > 0) $it['price'] = ['currencyCode' => 'JPY', 'units' => (string) $yen];
            $items[] = $it;
        }
        return $items;
    }

    /** Google のお店の業種(メインの業種の gcid)と、サービスを変えられるか */
    private function serviceTarget($shop, $token)
    {
        $g = $this->api->getLocation($token, $shop->gbp_location_name, 'metadata,categories');
        if (!$g['success']) return ['ok' => 0, 'msg' => 'Google のお店の情報を読めませんでした(' . mb_substr((string) $g['message'], 0, 80) . ')'];
        $md = (array) ($g['data']['metadata'] ?? []);
        if (array_key_exists('canModifyServiceList', $md) && !$md['canModifyServiceList']) return ['ok' => 0, 'msg' => 'このお店の Google の業種では、メニュー(サービス)を出せません'];
        $cat = preg_replace('#^categories/#', '', (string) ($g['data']['categories']['primaryCategory']['name'] ?? ''));
        if ($cat === '') return ['ok' => 0, 'msg' => 'Google のお店の業種が分からないため、メニューを出せませんでした'];
        return ['ok' => 1, 'cat' => $cat, 'catName' => (string) ($g['data']['categories']['primaryCategory']['displayName'] ?? ''), 'can' => $md['canModifyServiceList'] ?? '(Google から返事なし)'];
    }

    /** メニューを Google のサービスに出す。結果の文('ok' か理由)を返す */
    public function syncMenu($shop, $token, $onlyIfChanged = false)
    {
        $rows = self::menuRows($shop->id);
        if (!$rows) return 'メニューが未登録のため同期しませんでした';
        $hkey = 'gbp_menu_hash_' . $shop->id;
        $hash = md5($shop->gbp_location_name . json_encode(self::serviceItems($rows, 'x'), JSON_UNESCAPED_UNICODE));
        if ($onlyIfChanged) { try { if (Redis::get($hkey) === $hash) return 'ok'; } catch (\Throwable $e) {} }
        $t = $this->serviceTarget($shop, $token);
        if (!$t['ok']) return $t['msg'];
        $r = $this->api->updateLocation($token, $shop->gbp_location_name, ['serviceItems' => self::serviceItems($rows, $t['cat'], 'category')], ['serviceItems']);
        if (!$r['success'] && stripos((string) $r['message'], 'Unknown name') !== false) {
            // 古い書き方(categoryId)しか受け付けないとき
            $r = $this->api->updateLocation($token, $shop->gbp_location_name, ['serviceItems' => self::serviceItems($rows, $t['cat'], 'categoryId')], ['serviceItems']);
        }
        if (!$r['success']) {
            $this->log("sync menu failed sid {$shop->id}: " . $r['message']);
            return 'メニューを Google に出せませんでした(' . mb_substr((string) $r['message'], 0, 80) . ')';
        }
        try { Redis::setex($hkey, 2592000, $hash); } catch (\Throwable $e) {}
        return 'ok';
    }

    /** 確かめるだけ(Google には送らない) */
    public function menuPreview($shop)
    {
        if (!self::connected($shop)) return ['結果' => 'Google と連携していません'];
        $tk = $this->api->getValidToken($shop);
        if (!$tk['success']) return ['結果' => 'Google との連携を確かめられませんでした'];
        $t = $this->serviceTarget($shop, $tk['token']);
        $rows = self::menuRows($shop->id);
        $items = self::serviceItems($rows, $t['cat'] ?? '(業種不明)');
        return [
            'Google の業種' => ($t['catName'] ?? '') . ' ' . ($t['cat'] ?? ''),
            'サービスを変えられるか' => $t['ok'] ? (is_bool($t['can']) ? ($t['can'] ? 'はい' : 'いいえ') : $t['can']) : $t['msg'],
            'Card-San のメニューの数' => count($rows),
            '送るサービスの数' => count($items),
            '送るサービス(最初の5件)' => array_map(function ($i) { return $i['freeFormServiceItem']['label']['displayName'] . ' / ' . (isset($i['price']) ? $i['price']['units'] . '円' : '値段なし'); }, array_slice($items, 0, 5)),
        ];
    }

    /* ======================= クチコミ ======================= */

N];

$out = $src;
foreach ($E as $k => $list) foreach ($list as $i => $p) {
    $n = substr_count($out[$k], $p[0]);
    if ($n !== 1) $ng($F[$k] . ' の直す場所 ' . ($i + 1) . ' が見つかりません(見つかった数 ' . $n . ')');
    $out[$k] = str_replace($p[0], $p[1], $out[$k]);
}
// 元の FIELDS の行の残り(「 // ...業種・住所の同期はやめた...」)はコメントにする
$out['svc'] = preg_replace('/\n    const FIELDS_OLD = (\/\/[^\n]*)\n/', "\n    $1\n", $out['svc'], 1, $c);
if ($c !== 1) $ng('FIELDS の行を直せません');
$allOk = true;
foreach ($out as $k => $s) {
    $tmp = sys_get_temp_dir() . '/cs_gbpfix7_' . $k . '.php';
    file_put_contents($tmp, $s);
    $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c); @unlink($tmp);
    echo ($c === 0 ? 'OK ' : 'NG ') . "直したあとの文法 {$F[$k]}\n";
    if ($c !== 0) { $allOk = false; echo '   ' . implode(' ', $o) . "\n"; }
}
if (!$allOk) $ng('文法エラー');
echo "OK 直す場所はすべて見つかりました(" . array_sum(array_map('count', $E)) . " か所)\n";
if ($mode !== 'apply') exit("\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n");

if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($F as $f) copy($base . $f, $backup . str_replace('/', '__', $f));
foreach ($out as $k => $s) file_put_contents($base . $F[$k], $s);
foreach ($F as $f) {
    $o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c);
    if ($c !== 0) { foreach ($F as $g) copy($backup . str_replace('/', '__', $g), $base . $g); exit("文法エラーがあったので元に戻しました:{$f}\n"); }
}
@exec('/etc/init.d/php-fpm-73 reload 2>&1');
echo "完了しました。バックアップ: {$backup}\n";
echo "続けて、Google に送る中身を見るだけの確認ができます(送りません):\n php -d disable_functions= /root/gbp_fix7.php test 11\n";
