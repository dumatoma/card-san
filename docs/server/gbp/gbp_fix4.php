<?php
// [カスタムエイド 2026-11-06] GBP 連携 その4(サーバー):Google の「予約リンク」(ローカル ビジネス リンク・Place Actions API)
//   お店が管理App の「Google に予約リンクを出す」をオンにしたときだけ、Google のお店のプロフィールに
//   Card-San の予約ページ(https://order.card-san.jp/reserve?id=店舗ID)への予約リンク(APPOINTMENT)を作り、「ビジネス優先」にする。
//   ・優先にするのは作ったときの1回だけ(あとでお店が Google で優先を変えても、かけ直さない)
//   ・オフにしたとき・連携を解除したとき・違うお店につなぎ直したときは、作ったリンクを消す
//   ・お店の Google の業種が予約に対応していないときは作らず、そう知らせる
//
//   php -d disable_functions= /root/gbp_fix4.php         … 確かめるだけ(何も変えない)
//   php -d disable_functions= /root/gbp_fix4.php apply   … バックアップを取ってから変える(表に欄を3つ足す)
//   php -d disable_functions= /root/gbp_fix4.php undo    … ファイルを元に戻す(足した欄は残る。使われないだけ)

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_gbpfix4_20261106/';
$mode = $argv[1] ?? 'check';
$MARK = 'カスタムエイド 2026-11-06 GBP4';
$F = [
    'lib' => 'app/Libs/GoogleBusiness.php',
    'svc' => 'app/Services/GbpService.php',
    'ctl' => 'app/Http/Controllers/Api/Shop/GoogleBusinessController.php',
];
if ($mode === 'undo') {
    $n = 0;
    foreach ($F as $f) { $b = $backup . str_replace('/', '__', $f); if (is_file($b)) { copy($b, $base . $f); $n++; } }
    if (!$n) exit("バックアップがありません。何もしていません。\n");
    @exec('/etc/init.d/php-fpm-73 reload 2>&1');
    exit("元に戻しました({$n} ファイル)。表に足した欄(web_shop.gbp_book_on・gbp_book_link・gbp_book_msg)は残っています。\n");
}
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
$src = [];
foreach ($F as $k => $f) { $src[$k] = file_get_contents($base . $f); if (strpos($src[$k], $MARK) !== false) $ng("すでに直してあります({$f})"); }
if (strpos($src['svc'], 'カスタムエイド 2026-11-06 GBP3') === false) $ng('先に gbp_fix3.php を apply してください');

// お店の表(ShopModel)が、どの欄を update() で書けるか(参考に表示するだけ)
$mf = $base . 'app/Models/ShopModel.php';
if (is_file($mf)) {
    $ms = file_get_contents($mf);
    $hasF = preg_match('/protected\s+\$fillable/', $ms); $hasG = preg_match('/protected\s+\$guarded\s*=\s*([^;]*);/', $ms, $gm);
    echo '参考 ShopModel: ' . ($hasF ? '$fillable あり' . (strpos($ms, "'gbp_ins_auto_at'") !== false ? '(gbp_ins_auto_at あり)' : '(gbp_ins_auto_at なし)') : '$fillable なし') . ' / ' . ($hasG ? '$guarded = ' . trim($gm[1]) : '$guarded なし') . "\n";
}

$E = [];

// ---------- Google の呼び出し(Place Actions API) ----------
$E['lib'][] = ["    // 有効なアクセストークンを取得（期限切れなら自動リフレッシュ）\n", <<<'N'
    /* [カスタムエイド 2026-11-06 GBP4] Place Actions API(予約リンク = ローカル ビジネス リンク) */
    const PLACE_API = 'https://mybusinessplaceactions.googleapis.com/v1';

    /** そのお店に付けられるリンクの種類(APPOINTMENT など) */
    public function placeActionTypes($access_token, $location_id)
    {
        return $this->call('GET', self::PLACE_API . '/placeActionTypeMetadata?languageCode=ja&filter=' . rawurlencode('location=' . $location_id), $access_token);
    }

    public function listPlaceActionLinks($access_token, $location_id)
    {
        return $this->call('GET', self::PLACE_API . '/' . $location_id . '/placeActionLinks?pageSize=50', $access_token);
    }

    public function createPlaceActionLink($access_token, $location_id, array $body)
    {
        return $this->call('POST', self::PLACE_API . '/' . $location_id . '/placeActionLinks', $access_token, $body);
    }

    public function preferPlaceActionLink($access_token, $link_name)
    {
        return $this->call('PATCH', self::PLACE_API . '/' . $link_name . '?updateMask=isPreferred', $access_token, ['isPreferred' => true]);
    }

    public function deletePlaceActionLink($access_token, $link_name)
    {
        return $this->call('DELETE', self::PLACE_API . '/' . $link_name, $access_token);
    }

    // 有効なアクセストークンを取得（期限切れなら自動リフレッシュ）

N];

// ---------- 状態を返す ----------
$E['svc'][] = ["            'ins_auto'       => (int) \$shop->gbp_ins_sync_on,\n",
               "            'ins_auto'       => (int) \$shop->gbp_ins_sync_on,\n"
             . "            'book_on'        => (int) (\$shop->gbp_book_on ?? 0), // [{$MARK}] Google に予約リンクを出す\n"
             . "            'book_msg'       => (string) (\$shop->gbp_book_msg ?? ''),\n"
             . "            'book_url'       => self::bookUrl(\$shop),\n"];

// ---------- オン・オフ ----------
$E['svc'][] = ["        if (\$data) \$shop->update(\$data);\n        return \$this->overview(\$shop->fresh());\n    }\n", <<<'N'
        if ($data) $shop->update($data);
        // [カスタムエイド 2026-11-06 GBP4] 予約リンク:オンにしたときだけ作る/オフにしたら消す
        $bookResult = null;
        if (array_key_exists('book_on', $in)) {
            $fresh = $shop->fresh();
            $want = $in['book_on'] ? 1 : 0;
            if ($want && !(int) ($fresh->gbp_book_on ?? 0)) $bookResult = $this->bookOn($fresh);
            elseif (!$want && ((int) ($fresh->gbp_book_on ?? 0) || (string) ($fresh->gbp_book_link ?? '') !== '')) $bookResult = $this->bookOff($fresh);
        }
        $ov = $this->overview($shop->fresh());
        if ($bookResult) $ov['book_result'] = $bookResult;
        return $ov;
    }

N];

// ---------- 連携解除のときにリンクを消す(許可を取り消す前に) ----------
$E['svc'][] = ["        try { \$this->api->revoke(\$shop->gbp_refresh_token ?: \$shop->gbp_access_token); } catch (\\Throwable \$e) {}\n",
               "        try { if ((int) (\$shop->gbp_book_on ?? 0) || (string) (\$shop->gbp_book_link ?? '') !== '') \$this->bookOff(\$shop); } catch (\\Throwable \$e) {} // [{$MARK}] 作った予約リンクを消す\n"
             . "        try { \$this->api->revoke(\$shop->gbp_refresh_token ?: \$shop->gbp_access_token); } catch (\\Throwable \$e) {}\n"];

// ---------- 部品 ----------
$E['svc'][] = ["    /** [カスタムエイド 2026-11-06 GBP3] 返信が却下された理由(Google の policyViolation)を日本語に */\n", <<<'N'
    /* ======================= [カスタムエイド 2026-11-06 GBP4] 予約リンク ======================= */

    /** Card-San の予約ページ */
    public static function bookUrl($shop)
    {
        return 'https://order.card-san.jp/reserve?id=' . (int) $shop->id;
    }

    /** accounts/{a}/locations/{l} → locations/{l} */
    public static function placeLocationId($shop)
    {
        $n = (string) $shop->gbp_location_name;
        $p = strrpos($n, 'locations/');
        return $p === false ? '' : substr($n, $p);
    }

    private static function saveBook($shop, array $v)
    {
        $shop->newQuery()->whereKey($shop->getKey())->update($v);
    }

    /** Google の答えを、お店に見せる文に */
    private static function bookErrText($r)
    {
        $m = (string) ($r['message'] ?? '') . ' ' . json_encode($r['data']['error'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (stripos($m, 'has not been used') !== false || stripos($m, 'is disabled') !== false || stripos($m, 'SERVICE_DISABLED') !== false || stripos($m, 'quota') !== false) {
            return 'Google 側の準備が整っていないため、予約リンクを付けられませんでした。Card-San のサポートにお問い合わせください。';
        }
        if (stripos($m, 'not supported') !== false || stripos($m, 'unsupported') !== false || stripos($m, 'HTTP 422') !== false || stripos($m, 'FAILED_PRECONDITION') !== false) {
            return 'このお店の Google の業種では、予約リンクを付けられません。';
        }
        return 'Google に予約リンクを付けられませんでした(' . mb_substr((string) ($r['message'] ?? ''), 0, 80) . ')';
    }

    /** オン:業種を確かめて、予約リンクを作り、優先にする(優先にするのはこの1回だけ) */
    public function bookOn($shop)
    {
        $loc = self::placeLocationId($shop);
        $tk = $this->api->getValidToken($shop);
        if ($loc === '' || !$tk['success']) {
            $msg = 'Google との連携を確かめられませんでした。もう一度連携してください。';
            self::saveBook($shop, ['gbp_book_on' => 0, 'gbp_book_msg' => $msg]);
            return ['ok' => 0, 'msg' => $msg];
        }
        $token = $tk['token'];
        // 1. このお店の業種で「予約(APPOINTMENT)」が付けられるか
        $t = $this->api->placeActionTypes($token, $loc);
        if ($t['success']) {
            $types = array_column((array) ($t['data']['placeActionTypeMetadata'] ?? []), 'placeActionType');
            if (!in_array('APPOINTMENT', $types, true)) {
                $msg = 'このお店の Google の業種では、予約リンクを付けられません。';
                self::saveBook($shop, ['gbp_book_on' => 0, 'gbp_book_msg' => $msg]);
                return ['ok' => 0, 'msg' => $msg];
            }
        } else {
            $msg = self::bookErrText($t);
            self::saveBook($shop, ['gbp_book_on' => 0, 'gbp_book_msg' => $msg]);
            return ['ok' => 0, 'msg' => $msg];
        }
        // 2. もう同じリンクがあれば、それを使って優先にする。なければ作る
        $url = self::bookUrl($shop);
        $name = '';
        $l = $this->api->listPlaceActionLinks($token, $loc);
        if ($l['success']) {
            foreach ((array) ($l['data']['placeActionLinks'] ?? []) as $x) {
                if (($x['placeActionType'] ?? '') === 'APPOINTMENT' && rtrim((string) ($x['uri'] ?? ''), '/') === $url) { $name = (string) ($x['name'] ?? ''); break; }
            }
        }
        if ($name !== '') {
            $p = $this->api->preferPlaceActionLink($token, $name);
            if (!$p['success']) Log::info('[gbp4] 優先にできませんでした shop ' . $shop->id . ' ' . ($p['message'] ?? ''));
        } else {
            $c = $this->api->createPlaceActionLink($token, $loc, ['uri' => $url, 'placeActionType' => 'APPOINTMENT', 'isPreferred' => true]);
            if (!$c['success']) {
                $msg = self::bookErrText($c);
                Log::info('[gbp4] 予約リンクを作れませんでした shop ' . $shop->id . ' ' . ($c['message'] ?? ''));
                self::saveBook($shop, ['gbp_book_on' => 0, 'gbp_book_msg' => $msg]);
                return ['ok' => 0, 'msg' => $msg];
            }
            $name = (string) ($c['data']['name'] ?? '');
        }
        $msg = 'Google に予約リンクを付けました。Google に出るまで時間がかかることがあります。';
        self::saveBook($shop, ['gbp_book_on' => 1, 'gbp_book_link' => $name, 'gbp_book_msg' => $msg]);
        return ['ok' => 1, 'msg' => $msg];
    }

    /** オフ:Card-San が作った予約リンクを消す */
    public function bookOff($shop)
    {
        $name = (string) ($shop->gbp_book_link ?? '');
        $ok = 1;
        if ($name !== '') {
            $tk = $this->api->getValidToken($shop);
            if ($tk['success']) {
                $d = $this->api->deletePlaceActionLink($tk['token'], $name);
                if (!$d['success'] && stripos((string) ($d['message'] ?? ''), 'not found') === false && stripos((string) ($d['message'] ?? ''), 'HTTP 404') === false) {
                    $ok = 0;
                    Log::info('[gbp4] 予約リンクを消せませんでした shop ' . $shop->id . ' ' . ($d['message'] ?? ''));
                }
            } else {
                $ok = 0;
            }
        }
        $msg = $ok ? 'Google の予約リンクを外しました。' : 'Google の予約リンクを外せませんでした。Google のプロフィールの編集からも外せます。';
        self::saveBook($shop, ['gbp_book_on' => 0, 'gbp_book_link' => '', 'gbp_book_msg' => $ok ? '' : $msg]);
        return ['ok' => $ok, 'msg' => $msg];
    }

    /** [カスタムエイド 2026-11-06 GBP3] 返信が却下された理由(Google の policyViolation)を日本語に */

N];

// ---------- コントローラー ----------
$E['ctl'][] = ["        \$data = (new GbpService())->updateFeatures(\$shop, \$request->only(['basic_on', 'review_on', 'ins_on', 'auto_sync', 'sync_fields', 'ins_auto']));\n",
               "        \$data = (new GbpService())->updateFeatures(\$shop, \$request->only(['basic_on', 'review_on', 'ins_on', 'auto_sync', 'sync_fields', 'ins_auto', 'book_on'])); // [{$MARK}] book_on(予約リンク)\n"];
$E['ctl'][] = ["        return responseToJson(200, __('message.edit_success'), \$data);\n    }\n\n    // POST /api/shop/google_business/sync_now\n",
               "        if (!empty(\$data['book_result'])) return responseToJson(200, \$data['book_result']['msg'], \$data); // [{$MARK}] 予約リンクの結果を知らせる\n"
             . "        return responseToJson(200, __('message.edit_success'), \$data);\n    }\n\n    // POST /api/shop/google_business/sync_now\n"];
$E['ctl'][] = ["        \$changed = (string) \$shop->gbp_location_name !== \$name;\n",
               "        \$changed = (string) \$shop->gbp_location_name !== \$name;\n"
             . "        if (\$changed && (string) (\$shop->gbp_book_link ?? '') !== '') { try { (new GbpService())->bookOff(\$shop); } catch (\\Throwable \$e) {} } // [{$MARK}] 前のお店の予約リンクを消す\n"];

// ---------- 当てる(メモリの上で) ----------
$out = $src;
foreach ($E as $k => $list) foreach ($list as $i => $p) {
    $n = substr_count($out[$k], $p[0]);
    if ($n !== 1) $ng($F[$k] . ' の直す場所 ' . ($i + 1) . ' が見つかりません(見つかった数 ' . $n . ')');
    $out[$k] = str_replace($p[0], $p[1], $out[$k]);
}
if (strpos($out['svc'], 'use Illuminate\Support\Facades\Log;') === false && strpos($out['svc'], "\\Log::") === false) {
    // Log を使えるようにする
    $out['svc'] = preg_replace('/^namespace App\\\\Services;\n/m', "namespace App\\Services;\n\nuse Illuminate\\Support\\Facades\\Log; // [{$MARK}]\n", $out['svc'], 1, $cnt);
    if (!$cnt) $ng('GbpService.php に Log を足せません');
}
$allOk = true;
foreach ($out as $k => $s) {
    $tmp = sys_get_temp_dir() . '/cs_gbpfix4_' . $k . '.php';
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
require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    if (!Illuminate\Support\Facades\Schema::hasColumn('web_shop', 'gbp_book_on')) {
        Illuminate\Support\Facades\DB::statement("ALTER TABLE web_shop ADD COLUMN gbp_book_on TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Googleに予約リンクを出す', ADD COLUMN gbp_book_link VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Card-Sanが作った予約リンク(Google)', ADD COLUMN gbp_book_msg VARCHAR(255) NOT NULL DEFAULT '' COMMENT '予約リンクの結果'");
        echo "表 web_shop に gbp_book_on・gbp_book_link・gbp_book_msg を足しました\n";
    }
} catch (\Throwable $e) {
    exit("表に欄を足せませんでした。ファイルは変えていません:" . $e->getMessage() . "\n");
}
foreach ($out as $k => $s) file_put_contents($base . $F[$k], $s);
foreach ($F as $f) {
    $o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c);
    if ($c !== 0) { foreach ($F as $g) copy($backup . str_replace('/', '__', $g), $base . $g); exit("文法エラーがあったので元に戻しました:{$f}\n"); }
}
@exec('/etc/init.d/php-fpm-73 reload 2>&1');
echo "完了しました。バックアップ: {$backup}\n";
echo "このあと Google Cloud で「My Business Place Actions API」が有効になっているか確かめてください。\n";
