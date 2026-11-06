<?php
// [カスタムエイド 2026-11-07] GBP 連携 その8(サーバー):お知らせを Google の「最新情報」に出す・投稿の状態(審査中など)
//   1. Googleビジネス連携の設定に2つ足す(web_shop)
//        gbp_notice_on … 「お知らせ → Google の最新情報」:オンなら、お知らせを作るときに「Google の最新情報にも出す」が最初から入る
//        gbp_coupon_on … 「クーポン → Google の特典」:オンなら、クーポン配信設定に「Google の特典にも出す」が出る(中身は gbp_fix9)
//   2. お知らせ(web_notice に gbp_post_name・gbp_post_err)
//        作る・直すとき gbp=1 なら Google の最新情報に出す(直したときは出し直す)。gbp=0 なら Google から消す
//        消すときは Google からも消す。一覧に gbp_state(processing=審査中 / live=掲載中 / rejected=掲載されませんでした / failed=出せなかった)
//        gbp を送らない古いアプリのときは、今までどおり(Google には何もしない)
//   3. Instagram×GBP投稿管理:転載済みの投稿の状態を Google から見て、gbp_state に processing(審査中)・rejected(掲載されませんでした)・live を出す(status は今までどおり)
//   Google の投稿の状態は、お店ごとに10分ためておく(Redis)
//
//   php -d disable_functions= /root/gbp_fix8.php          … 確かめるだけ(何も変えない)
//   php -d disable_functions= /root/gbp_fix8.php apply    … バックアップを取ってから変える(表に欄を足す)
//   php -d disable_functions= /root/gbp_fix8.php undo     … ファイルを元に戻す(足した欄は残る。使われないだけ)

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_gbpfix8_20261107/';
$mode = $argv[1] ?? 'check';
$MARK = 'カスタムエイド 2026-11-07 GBP8';
$F = [
    'svc'    => 'app/Services/GbpService.php',
    'ctl'    => 'app/Http/Controllers/Api/Shop/GoogleBusinessController.php',
    'notice' => 'app/Http/Controllers/Api/Shop/NoticeController.php',
];
if ($mode === 'undo') {
    $n = 0;
    foreach ($F as $f) { $b = $backup . str_replace('/', '__', $f); if (is_file($b)) { copy($b, $base . $f); $n++; } }
    if (!$n) exit("バックアップがありません。何もしていません。\n");
    @exec('/etc/init.d/php-fpm-73 reload 2>&1');
    exit("元に戻しました({$n} ファイル)。足した欄は残っています。\n");
}
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
$src = [];
foreach ($F as $k => $f) { $src[$k] = file_get_contents($base . $f); if (strpos($src[$k], $MARK) !== false) $ng("すでに直してあります({$f})"); }
if (strpos($src['svc'], 'カスタムエイド 2026-11-07 GBP7b') === false) $ng('先に gbp_fix7b.php を apply してください');

$E = [];
// ---------- 1. 設定 ----------
$E['svc'][] = ["            'book_url'       => self::bookUrl(\$shop),\n",
               "            'book_url'       => self::bookUrl(\$shop),\n"
             . "            'notice_on'      => (int) (\$shop->gbp_notice_on ?? 0), // [{$MARK}] お知らせ → Google の最新情報\n"
             . "            'coupon_on'      => (int) (\$shop->gbp_coupon_on ?? 0), // [{$MARK}] クーポン → Google の特典\n"];
$E['svc'][] = ["'auto_sync' => 'gbp_auto_sync', 'ins_auto' => 'gbp_ins_sync_on'];\n",
               "'auto_sync' => 'gbp_auto_sync', 'ins_auto' => 'gbp_ins_sync_on', 'notice_on' => 'gbp_notice_on', 'coupon_on' => 'gbp_coupon_on']; // [{$MARK}] notice_on・coupon_on\n"];
$E['ctl'][] = ["'ins_auto', 'book_on'])); // [カスタムエイド 2026-11-06 GBP4] book_on(予約リンク)\n",
               "'ins_auto', 'book_on', 'notice_on', 'coupon_on'])); // [カスタムエイド 2026-11-06 GBP4] book_on(予約リンク) / [{$MARK}] notice_on・coupon_on\n"];

// ---------- 2・3. 投稿の部品 ----------
$E['svc'][] = ["    /* ======================= [カスタムエイド 2026-11-07 GBP7] メニュー → Google のサービス ======================= */\n", <<<'N'
    /* ======================= [カスタムエイド 2026-11-07 GBP8] お知らせ → Google の最新情報・投稿の状態 ======================= */

    /** Google の投稿の状態(投稿の名前 => PROCESSING / LIVE / REJECTED)。10分ためておく */
    public function postStates($shop, $fresh = false)
    {
        $key = 'gbp_pst_' . $shop->id . '_' . md5((string) $shop->gbp_location_name);
        if (!$fresh) { try { $c = Redis::get($key); if ($c) { $d = json_decode($c, true); if (is_array($d)) return $d; } } catch (\Throwable $e) {} }
        $map = [];
        $tk = $this->api->getValidToken($shop);
        if (!$tk['success']) return $map;
        $page = null;
        for ($i = 0; $i < 10; $i++) {
            $r = $this->api->getPosts($tk['token'], $shop->gbp_location_name, $page);
            if (!$r['success']) return $map;
            foreach ((array) ($r['data']['localPosts'] ?? []) as $p) if (!empty($p['name'])) $map[$p['name']] = (string) ($p['state'] ?? '');
            $page = $r['data']['nextPageToken'] ?? null;
            if (!$page) break;
        }
        try { Redis::setex($key, 600, json_encode($map)); } catch (\Throwable $e) {}
        return $map;
    }

    /** 投稿の名前 → processing(審査中) / live(掲載中) / rejected(掲載されませんでした) / removed(Google で消された) */
    public static function postStateCode(array $states, $name)
    {
        if ((string) $name === '') return '';
        if (!array_key_exists($name, $states)) return $states ? 'removed' : 'live';
        $s = strtoupper((string) $states[$name]);
        return $s === 'PROCESSING' ? 'processing' : ($s === 'REJECTED' ? 'rejected' : 'live');
    }

    private function forgetPostStates($shop)
    {
        try { Redis::del('gbp_pst_' . $shop->id . '_' . md5((string) $shop->gbp_location_name)); } catch (\Throwable $e) {}
    }

    /** お知らせの文(タイトル+本文。1500字まで) */
    public static function noticeSummary($title, $content)
    {
        $s = trim((string) $title);
        $c = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", (string) $content)));
        if ($c !== '') $s .= "\n\n" . $c;
        return mb_substr($s, 0, 1500);
    }

    /** お知らせを Google の最新情報に出す(出ていれば消して出し直す) */
    public function noticePost($shop, $noticeId)
    {
        $n = DB::table('web_notice')->where('id', $noticeId)->where('sid', $shop->id)->first();
        if (!$n) return ['ok' => 0, 'msg' => 'お知らせが見つかりません'];
        if (!self::connected($shop)) return ['ok' => 0, 'msg' => 'Google と連携していません'];
        $tk = $this->api->getValidToken($shop);
        if (!$tk['success']) {
            DB::table('web_notice')->where('id', $n->id)->update(['gbp_post_err' => 'Google との連携を確かめられませんでした']);
            return ['ok' => 0, 'msg' => 'Google との連携を確かめられませんでした'];
        }
        if ((string) $n->gbp_post_name !== '') { try { $this->api->deletePost($tk['token'], $n->gbp_post_name); } catch (\Throwable $e) {} }
        $body = ['topicType' => 'STANDARD', 'languageCode' => 'ja', 'summary' => self::noticeSummary($n->title, $n->content)];
        $imgs = json_decode((string) $n->images, true);
        foreach ((array) $imgs as $u) { if (is_string($u) && preg_match('#^https://#', $u)) { $body['media'] = [['mediaFormat' => 'PHOTO', 'sourceUrl' => $u]]; break; } }
        $p = $this->api->createPost($tk['token'], $shop->gbp_location_name, $body);
        $this->forgetPostStates($shop);
        if (!$p['success']) {
            $this->log("notice post failed sid {$shop->id} notice {$n->id}: " . $p['message']);
            $err = 'Google の最新情報に出せませんでした(' . mb_substr((string) $p['message'], 0, 60) . ')';
            DB::table('web_notice')->where('id', $n->id)->update(['gbp_post_name' => '', 'gbp_post_err' => mb_substr($err, 0, 250)]);
            return ['ok' => 0, 'msg' => $err];
        }
        DB::table('web_notice')->where('id', $n->id)->update(['gbp_post_name' => (string) ($p['data']['name'] ?? ''), 'gbp_post_err' => '']);
        return ['ok' => 1, 'msg' => ''];
    }

    /** お知らせを Google の最新情報から消す */
    public function noticeUnpost($shop, $noticeId)
    {
        $n = DB::table('web_notice')->where('id', $noticeId)->where('sid', $shop->id)->first();
        if (!$n) return;
        if ((string) $n->gbp_post_name !== '' && self::connected($shop)) {
            $tk = $this->api->getValidToken($shop);
            if ($tk['success']) { try { $this->api->deletePost($tk['token'], $n->gbp_post_name); } catch (\Throwable $e) {} }
            $this->forgetPostStates($shop);
        }
        DB::table('web_notice')->where('id', $n->id)->update(['gbp_post_name' => '', 'gbp_post_err' => '']);
    }

    /* ======================= [カスタムエイド 2026-11-07 GBP7] メニュー → Google のサービス ======================= */

N];

// Instagram の一覧に状態を出す
$E['svc'][] = ["        \$synced = \$ids ? DB::table('web_gbp_ins_sync_log')->where('sid', \$shop->id)->whereIn('ins_media_id', \$ids)->pluck('ins_media_id')->all() : [];\n",
               "        \$syncedNames = \$ids ? DB::table('web_gbp_ins_sync_log')->where('sid', \$shop->id)->whereIn('ins_media_id', \$ids)->pluck('gbp_post_name', 'ins_media_id')->all() : []; // [{$MARK}]\n"
             . "        \$synced = array_keys(\$syncedNames);\n"
             . "        \$pst = (\$syncedNames && self::connected(\$shop)) ? \$this->postStates(\$shop) : []; // [{$MARK}] 転載した投稿の Google での状態\n"];
$E['svc'][] = ["                'status'    => in_array(\$m['id'], \$synced) ? 'synced' : (\$video ? 'video' : 'pending'),\n",
               "                'status'    => in_array(\$m['id'], \$synced) ? 'synced' : (\$video ? 'video' : 'pending'),\n"
             . "                'gbp_state' => isset(\$syncedNames[\$m['id']]) ? self::postStateCode(\$pst, (string) \$syncedNames[\$m['id']]) : '', // [{$MARK}] 転載済みの Google での状態:processing=審査中 / rejected=掲載されませんでした / live\n"];

// ---------- 2. お知らせの入口 ----------
$E['notice'][] = ["use App\\Validators\\NoticeValidator;\n",
                  "use App\\Validators\\NoticeValidator;\nuse App\\Services\\GbpService; // [{$MARK}]\n"];
$E['notice'][] = ["        \$data = [];\n        foreach(\$notices as \$v){\n",
                  "        \$data = [];\n"
                . "        // [{$MARK}] Google の最新情報に出したお知らせの状態\n"
                . "        \$cs_shop = auth('shop')->user()->shop; \$cs_pst = [];\n"
                . "        foreach (\$notices as \$v) { if ((string) (\$v->gbp_post_name ?? '') !== '' && GbpService::connected(\$cs_shop)) { \$cs_pst = (new GbpService())->postStates(\$cs_shop); break; } }\n"
                . "        foreach(\$notices as \$v){\n"];
$E['notice'][] = ["                'edit_time' => (\$v->create_time && \$v->update_time - \$v->create_time > 60) ? date('n/j', \$v->update_time) : ''\n",
                  "                'edit_time' => (\$v->create_time && \$v->update_time - \$v->create_time > 60) ? date('n/j', \$v->update_time) : '',\n"
                . "                'gbp_state' => (string) (\$v->gbp_post_err ?? '') !== '' ? 'failed' : GbpService::postStateCode(\$cs_pst, (string) (\$v->gbp_post_name ?? '')), // [{$MARK}]\n"];
$E['notice'][] = ["            \$cs_notice = NoticeModel::create(\$data);\n            \\DB::table(\$cs_notice->getTable())->where('id', \$cs_notice->id)->update(['create_time' => time()]); // [カスタムエイド 2026-10-22] お知らせの日付:投稿した日を記録する\n        } catch (\\Throwable \$th) {\n            return responseToJson(500);\n        }\n\n        return responseToJson(200, '投稿しました');\n",
                  "            \$cs_notice = NoticeModel::create(\$data);\n            \\DB::table(\$cs_notice->getTable())->where('id', \$cs_notice->id)->update(['create_time' => time()]); // [カスタムエイド 2026-10-22] お知らせの日付:投稿した日を記録する\n        } catch (\\Throwable \$th) {\n            return responseToJson(500);\n        }\n\n"
                . "        // [{$MARK}] Google の最新情報にも出す\n"
                . "        if (\$request->gbp && GbpService::connected(auth('shop')->user()->shop)) {\n"
                . "            \$g = (new GbpService())->noticePost(auth('shop')->user()->shop, \$cs_notice->id);\n"
                . "            if (!\$g['ok']) return responseToJson(200, '投稿しました。' . \$g['msg']);\n"
                . "            return responseToJson(200, '投稿しました。Google の最新情報には、Google で確認されてから表示されます。');\n"
                . "        }\n"
                . "        return responseToJson(200, '投稿しました');\n"];
$E['notice'][] = ["            'twitter' => \$notice->twitter\n        ]]);\n",
                  "            'twitter' => \$notice->twitter,\n"
                . "            'gbp' => ((string) (\$notice->gbp_post_name ?? '') !== '' || (string) (\$notice->gbp_post_err ?? '') !== '') ? 1 : 0, // [{$MARK}] Google の最新情報に出しているか\n"
                . "        ]]);\n"];
$E['notice'][] = ["            \$notice->update(\$data);\n        } catch (\\Throwable \$th) {\n            return responseToJson(500);\n        }\n\n        return responseToJson(200, __('message.edit_success'));\n",
                  "            \$notice->update(\$data);\n        } catch (\\Throwable \$th) {\n            return responseToJson(500);\n        }\n\n"
                . "        // [{$MARK}] gbp=1 なら Google に出し直す / gbp=0 なら Google から消す(gbp を送らない古いアプリは何もしない)\n"
                . "        if (\$request->has('gbp')) {\n"
                . "            \$cs_shop = auth('shop')->user()->shop;\n"
                . "            if (\$request->gbp && GbpService::connected(\$cs_shop)) {\n"
                . "                \$g = (new GbpService())->noticePost(\$cs_shop, \$notice->id);\n"
                . "                if (!\$g['ok']) return responseToJson(200, __('message.edit_success') . ' ' . \$g['msg']);\n"
                . "            } elseif (!\$request->gbp) {\n"
                . "                (new GbpService())->noticeUnpost(\$cs_shop, \$notice->id);\n"
                . "            }\n"
                . "        }\n"
                . "        return responseToJson(200, __('message.edit_success'));\n"];
$E['notice'][] = ["        try {\n            \$notice->delete();\n",
                  "        try { (new GbpService())->noticeUnpost(auth('shop')->user()->shop, \$notice->id); } catch (\\Throwable \$e) {} // [{$MARK}] Google の最新情報からも消す\n"
                . "        try {\n            \$notice->delete();\n"];

$out = $src;
foreach ($E as $k => $list) foreach ($list as $i => $p) {
    $n = substr_count($out[$k], $p[0]);
    if ($n !== 1) $ng($F[$k] . ' の直す場所 ' . ($i + 1) . ' が見つかりません(見つかった数 ' . $n . ')');
    $out[$k] = str_replace($p[0], $p[1], $out[$k]);
}
$allOk = true;
foreach ($out as $k => $s) {
    $tmp = sys_get_temp_dir() . '/cs_gbpfix8_' . $k . '.php';
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
    $S = Illuminate\Support\Facades\Schema::class; $D = Illuminate\Support\Facades\DB::class;
    if (!$S::hasColumn('web_shop', 'gbp_notice_on')) { $D::statement("ALTER TABLE web_shop ADD COLUMN gbp_notice_on TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'お知らせをGoogleの最新情報に(最初からチェック)', ADD COLUMN gbp_coupon_on TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'クーポンをGoogleの特典に出せる'"); echo "表 web_shop に gbp_notice_on・gbp_coupon_on を足しました\n"; }
    if (!$S::hasColumn('web_notice', 'gbp_post_name')) { $D::statement("ALTER TABLE web_notice ADD COLUMN gbp_post_name VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Googleの最新情報の投稿', ADD COLUMN gbp_post_err VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Googleに出せなかった理由'"); echo "表 web_notice に gbp_post_name・gbp_post_err を足しました\n"; }
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
