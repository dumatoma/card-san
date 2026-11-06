<?php
// [カスタムエイド 2026-11-07] GBP 連携 その9(サーバー):クーポンを Google の「特典」に出す・新規会員限定クーポン
//   1. web_shop_coupon に欄を足す:cs_target(0=今の会員 / 1=新規会員限定)・cs_gbp(Google の特典にも出す)・cs_gbp_post・cs_gbp_err
//   2. クーポン配信設定(target=new / gbp=1 を受け取る)
//        新規会員限定:有効期限(=配布の期間)が必要。今の会員には届けない。配信数は「1通」と数える。日時の予約はなし(すぐ)
//        Google の特典に出す:有効期限が必要。お店の「クーポン → Google の特典」がオンで、Google と連携しているときだけ出す
//   3. 配信のとき(CouponJob)に Google の特典(OFFER)を出す。「特典を利用」の行き先は https://card-san.jp/s/店舗ID?from=gbp
//   4. 新規会員限定:配布の期間中(有効期限の最後の日まで)に、このお店の会員に初めてなった人に届く(app/Libs/CsNewMemberCoupon.php を新しく作る)
//   5. クーポンを消したら Google の特典も消す。一覧に cs_target・cs_gbp・gbp_state(processing / live / rejected / failed)
//   6. 店舗ページ(/s/店舗ID):新規会員限定クーポンを配布中なら、クーポンの枠を出す。?from=gbp で開いたときは iPhone のボタンを「アプリ会員になる」に
//
//   php -d disable_functions= /root/gbp_fix9.php          … 確かめるだけ(何も変えない)
//   php -d disable_functions= /root/gbp_fix9.php apply    … バックアップを取ってから変える(表に欄を足す・ファイルを1つ作る)
//   php -d disable_functions= /root/gbp_fix9.php undo     … ファイルを元に戻す(足した欄は残る。使われないだけ)

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_gbpfix9_20261107/';
$mode = $argv[1] ?? 'check';
$MARK = 'カスタムエイド 2026-11-07 GBP9';
$F = [
    'svc'    => 'app/Services/GbpService.php',
    'val'    => 'app/Validators/ShopCouponValidator.php',
    'job'    => 'app/Jobs/CouponJob.php',
    'cpn'    => 'app/Http/Controllers/Api/Shop/CouponController.php',
    'member' => 'app/Http/Controllers/Api/Member/MemberController.php',
    'page'   => 'app/Http/Controllers/CsShopPageController.php',
];
$NEWF = 'app/Libs/CsNewMemberCoupon.php';
if ($mode === 'undo') {
    $n = 0;
    foreach ($F as $f) { $b = $backup . str_replace('/', '__', $f); if (is_file($b)) { copy($b, $base . $f); $n++; } }
    if (!$n) exit("バックアップがありません。何もしていません。\n");
    if (is_file($base . $NEWF)) rename($base . $NEWF, $backup . 'CsNewMemberCoupon.php.removed');
    @exec('/etc/init.d/php-fpm-73 reload 2>&1');
    exit("元に戻しました({$n} ファイル)。足した欄は残っています。\n");
}
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
$src = [];
foreach ($F as $k => $f) { $src[$k] = file_get_contents($base . $f); if (strpos($src[$k], $MARK) !== false) $ng("すでに直してあります({$f})"); }
if (strpos($src['svc'], 'カスタムエイド 2026-11-07 GBP8') === false) $ng('先に gbp_fix8.php を apply してください');
if (is_file($base . $NEWF)) $ng($NEWF . ' がもうあります');

$E = [];
// ---------- Google の特典(GbpService) ----------
$E['svc'][] = ["    /* ======================= [カスタムエイド 2026-11-07 GBP7] メニュー → Google のサービス ======================= */\n", <<<'N'
    /* ======================= [カスタムエイド 2026-11-07 GBP9] クーポン → Google の特典 ======================= */

    private static function ymd($t)
    {
        $d = new \DateTime('@' . (int) $t); $d->setTimezone(new \DateTimeZone('Asia/Tokyo'));
        return ['year' => (int) $d->format('Y'), 'month' => (int) $d->format('n'), 'day' => (int) $d->format('j')];
    }

    /** クーポンを Google の特典に出す(出ていれば消して出し直す) */
    public function couponPost($couponId)
    {
        $c = DB::table('web_shop_coupon')->where('id', $couponId)->first();
        if (!$c) return ['ok' => 0, 'msg' => 'クーポンが見つかりません'];
        $shop = ShopModel::find($c->sid);
        $fail = function ($msg) use ($c) {
            DB::table('web_shop_coupon')->where('id', $c->id)->update(['cs_gbp_post' => '', 'cs_gbp_err' => mb_substr($msg, 0, 250)]);
            return ['ok' => 0, 'msg' => $msg];
        };
        if (!$shop || !self::connected($shop)) return $fail('Google と連携していないため、特典に出せませんでした');
        if (!(int) $c->is_indate || !$c->indate_end_time) return $fail('有効期限がないため、特典に出せませんでした');
        $tk = $this->api->getValidToken($shop);
        if (!$tk['success']) return $fail('Google との連携を確かめられませんでした');
        if ((string) $c->cs_gbp_post !== '') { try { $this->api->deletePost($tk['token'], $c->cs_gbp_post); } catch (\Throwable $e) {} }
        $title = trim((string) $c->title);
        $sum = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", (string) $c->des)));
        $body = [
            'topicType'    => 'OFFER',
            'languageCode' => 'ja',
            'summary'      => mb_substr($sum !== '' ? $sum : $title, 0, 1500),
            'event'        => ['title' => mb_substr($title, 0, 58), 'schedule' => ['startDate' => self::ymd($c->indate_start_time ?: time()), 'endDate' => self::ymd($c->indate_end_time)]],
            'offer'        => ['redeemOnlineUrl' => 'https://card-san.jp/s/' . (int) $c->sid . '?from=gbp'],
        ];
        if ((string) $c->number !== '') $body['offer']['couponCode'] = (string) $c->number;
        if (trim((string) $c->remark) !== '') $body['offer']['termsConditions'] = mb_substr(trim((string) $c->remark), 0, 500);
        foreach ((array) json_decode((string) $c->images, true) as $u) { if (is_string($u) && preg_match('#^https://#', $u)) { $body['media'] = [['mediaFormat' => 'PHOTO', 'sourceUrl' => $u]]; break; } }
        $p = $this->api->createPost($tk['token'], $shop->gbp_location_name, $body);
        $this->forgetPostStates($shop);
        if (!$p['success']) {
            $this->log("coupon post failed sid {$shop->id} coupon {$c->id}: " . $p['message']);
            return $fail('Google の特典に出せませんでした(' . mb_substr((string) $p['message'], 0, 60) . ')');
        }
        DB::table('web_shop_coupon')->where('id', $c->id)->update(['cs_gbp_post' => (string) ($p['data']['name'] ?? ''), 'cs_gbp_err' => '']);
        return ['ok' => 1, 'msg' => ''];
    }

    /** クーポンを Google の特典から消す */
    public function couponUnpost($couponId)
    {
        $c = DB::table('web_shop_coupon')->where('id', $couponId)->first();
        if (!$c || (string) $c->cs_gbp_post === '') return;
        $shop = ShopModel::find($c->sid);
        if ($shop && self::connected($shop)) {
            $tk = $this->api->getValidToken($shop);
            if ($tk['success']) { try { $this->api->deletePost($tk['token'], $c->cs_gbp_post); } catch (\Throwable $e) {} }
            $this->forgetPostStates($shop);
        }
        DB::table('web_shop_coupon')->where('id', $c->id)->update(['cs_gbp_post' => '']);
    }

    /* ======================= [カスタムエイド 2026-11-07 GBP7] メニュー → Google のサービス ======================= */

N];

// ---------- 配信設定の受け取り(ShopCouponValidator) ----------
$E['val'][] = ["        \$data['receiver'] = \$request->receiver == 1 ? 1 : 2;\n",
               "        \$data['receiver'] = \$request->receiver == 1 ? 1 : 2;\n\n"
             . "        // [{$MARK}] 新規会員限定(target=new)・Google の特典にも出す(gbp=1)\n"
             . "        \$data['cs_target'] = \$request->target === 'new' ? 1 : 0;\n"
             . "        \$data['cs_gbp'] = \$request->gbp ? 1 : 0;\n"
             . "        if (\$data['cs_target'] && !\$data['is_indate']) return ['success' => false, 'message' => '新規会員限定のクーポンは、有効期限(配布の期間)を決めてください'];\n"
             . "        if (\$data['cs_gbp'] && !\$data['is_indate']) return ['success' => false, 'message' => 'Google の特典に出すクーポンは、有効期限を決めてください'];\n"
             . "        if (\$data['cs_target']) { \$data['receiver'] = 1; \$data['is_order'] = 0; \$data['order_time'] = time(); }\n"];
$E['val'][] = ["        \$data['order_send_count'] = \$model->count();\n",
               "        \$data['order_send_count'] = \$data['cs_target'] ? 1 : \$model->count(); // [{$MARK}] 新規会員限定は「1通」と数える\n"];

// ---------- 配信(CouponJob) ----------
$E['job'][] = ["        if(!\$coupon || \$coupon->is_send == 1 || (\$coupon->is_order == 1 && \$coupon->order_time > time())){\n            return false;\n        }\n",
               "        if(!\$coupon || \$coupon->is_send == 1 || (\$coupon->is_order == 1 && \$coupon->order_time > time())){\n            return false;\n        }\n\n"
             . "        // [{$MARK}] Google の特典にも出す(お店の「クーポン → Google の特典」がオンのとき)\n"
             . "        if ((int) (\$coupon->cs_gbp ?? 0) && (int) (\\Illuminate\\Support\\Facades\\DB::table('web_shop')->where('id', \$coupon->sid)->value('gbp_coupon_on') ?? 0)) {\n"
             . "            try { (new \\App\\Services\\GbpService())->couponPost(\$coupon->id); } catch (\\Throwable \$e) { \\Illuminate\\Support\\Facades\\Log::info('[gbp9] ' . \$e->getMessage()); }\n"
             . "        }\n"
             . "        // [{$MARK}] 新規会員限定:今の会員には届けない(新しく会員になった人に、会員登録のときに届く)\n"
             . "        if ((int) (\$coupon->cs_target ?? 0) === 1) {\n"
             . "            \$coupon->update(['is_send' => 1]);\n"
             . "            return true;\n"
             . "        }\n"];

// ---------- 一覧・詳しい画面・消す(CouponController) ----------
$E['cpn'][] = ["        \$data = [];\n        foreach(\$coupons as \$v){\n",
               "        \$data = [];\n"
             . "        // [{$MARK}] Google の特典に出したクーポンの状態\n"
             . "        \$cs_shop = auth('shop')->user()->shop; \$cs_pst = [];\n"
             . "        foreach (\$coupons as \$v) { if ((string) (\$v->cs_gbp_post ?? '') !== '' && \\App\\Services\\GbpService::connected(\$cs_shop)) { \$cs_pst = (new \\App\\Services\\GbpService())->postStates(\$cs_shop); break; } }\n"
             . "        foreach(\$coupons as \$v){\n"];
$E['cpn'][] = ["                'indate' => (\$v->is_indate == 1 && \$v->indate_start_time <= time() && \$v->indate_end_time > time()) || \$v->is_indate == 0 ? 1 : 0\n",
               "                'indate' => (\$v->is_indate == 1 && \$v->indate_start_time <= time() && \$v->indate_end_time > time()) || \$v->is_indate == 0 ? 1 : 0,\n"
             . "                'cs_target' => (int) (\$v->cs_target ?? 0), // [{$MARK}] 1=新規会員限定\n"
             . "                'cs_gbp' => (int) (\$v->cs_gbp ?? 0),\n"
             . "                'gbp_state' => (string) (\$v->cs_gbp_err ?? '') !== '' ? 'failed' : \\App\\Services\\GbpService::postStateCode(\$cs_pst, (string) (\$v->cs_gbp_post ?? '')),\n"];
$E['cpn'][] = ["            'visit_max' => (int) \$coupon->visit_max\n",
               "            'visit_max' => (int) \$coupon->visit_max,\n"
             . "            'cs_target' => (int) (\$coupon->cs_target ?? 0), // [{$MARK}]\n"
             . "            'cs_gbp' => (int) (\$coupon->cs_gbp ?? 0),\n"
             . "            'cs_gbp_err' => (string) (\$coupon->cs_gbp_err ?? '')\n"];
$E['cpn'][] = ["            \$coupon->delete();\n",
               "            try { (new \\App\\Services\\GbpService())->couponUnpost(\$coupon->id); } catch (\\Throwable \$e) {} // [{$MARK}] Google の特典からも消す\n"
             . "            \$coupon->delete();\n"];

// ---------- 会員登録(新規会員限定クーポンを届ける) ----------
$E['member'][] = ["        \$data['expire_time'] = [0, strtotime(date('Y-m-d') . ' +' . \$card->valid_day . ' days'), \$card->expire_time][\$card->valid_type];\n",
                  "        \$data['expire_time'] = [0, strtotime(date('Y-m-d') . ' +' . \$card->valid_day . ' days'), \$card->expire_time][\$card->valid_type];\n"
                . "        \$cs_first = !ShopMemberModel::where('uid', auth()->id())->where('sid', \$card->sid)->exists(); // [{$MARK}] このお店の会員に初めてなるか\n"];
$E['member'][] = ["        // [カスタムエイド 2026-10-19] アカウント情報の空欄だけを、カード作成時の入力内容で補完する\n",
                  "        // [{$MARK}] このお店の会員に初めてなった人に、配布中の新規会員限定クーポンを届ける(失敗してもカード作成は成功のまま)\n"
                . "        if (\$cs_first) { try { \\App\\Libs\\CsNewMemberCoupon::give(\$member); } catch (\\Throwable \$e) { \\Illuminate\\Support\\Facades\\Log::info('[gbp9] new member coupon: ' . \$e->getMessage()); } }\n\n"
                . "        // [カスタムエイド 2026-10-19] アカウント情報の空欄だけを、カード作成時の入力内容で補完する\n"];

// ---------- 店舗ページ ----------
$E['page'][] = ["        // [カスタムエイド 2026-10-26b] モックどおり「カードを受け取るまで」の手順の枠はなし\n",
                "        // [カスタムエイド 2026-10-26b] モックどおり「カードを受け取るまで」の手順の枠はなし\n"
              . "        // [{$MARK}] 新規会員限定クーポンを配布中なら、クーポンの枠を出す\n"
              . "        \$cp = \\App\\Libs\\CsNewMemberCoupon::current(\$id);\n"
              . "        if (\$cp) {\n"
              . "            \$h .= '<div style=\"background:#fff;border-radius:16px;padding:14px 16px;text-align:left;margin:0 0 16px;box-shadow:0 2px 8px rgba(0,0,0,.08)\">'\n"
              . "                . '<div style=\"font-size:12px;font-weight:700;color:#E5322D\">新規会員限定</div>'\n"
              . "                . '<div style=\"font-size:15px;font-weight:800;margin:4px 0 2px\">アプリで会員になると、このクーポンが届きます</div>'\n"
              . "                . '<div style=\"font-size:15px;margin-top:6px\">' . \$this->e(\$cp->title) . '</div>'\n"
              . "                . '<div style=\"font-size:12px;color:#86868B;margin-top:4px\">' . date('n/j', \$cp->indate_end_time) . 'まで ・ 来店時にご提示ください</div></div>';\n"
              . "        }\n"];
$E['page'][] = ["            \$h .= '<button class=\"btn blk\" id=\"ios\" type=\"button\"> App Store でカードを受け取る</button>'\n",
                "            \$h .= '<button class=\"btn blk\" id=\"ios\" type=\"button\">' . (request()->query('from') === 'gbp' ? 'アプリ会員になる' : ' App Store でカードを受け取る') . '</button>' // [{$MARK}] Google の特典から来たとき\n"];

$out = $src;
foreach ($E as $k => $list) foreach ($list as $i => $p) {
    $n = substr_count($out[$k], $p[0]);
    if ($n !== 1) $ng($F[$k] . ' の直す場所 ' . ($i + 1) . ' が見つかりません(見つかった数 ' . $n . ')');
    $out[$k] = str_replace($p[0], $p[1], $out[$k]);
}

// 新しいファイル:新規会員限定クーポン
$newSrc = <<<'N'
<?php

namespace App\Libs;

use Illuminate\Support\Facades\DB;

/**
 * [カスタムエイド 2026-11-07 GBP9] 新規会員限定クーポン
 *   配布の期間 = クーポンの有効期限(始まりの日 〜 終わりの日の終わりまで)
 *   この期間に、このお店の会員に初めてなった人に届く(今の会員には届かない)
 */
class CsNewMemberCoupon
{
    /** いま配布中の新規会員限定クーポン(店舗ページに出す1件。終わりが近いもの) */
    public static function current($sid)
    {
        $now = time();
        return DB::table('web_shop_coupon')->where('sid', (int) $sid)->where('cs_target', 1)->where('is_indate', 1)
            ->where('indate_start_time', '<=', $now)->where('indate_end_time', '>', $now - 86400)
            ->orderBy('indate_end_time')->first(['id', 'title', 'indate_end_time']);
    }

    /** 会員になった人に、配布中の新規会員限定クーポンを届ける */
    public static function give($member)
    {
        $now = time();
        $list = DB::table('web_shop_coupon')->where('sid', $member->sid)->where('cs_target', 1)->where('is_indate', 1)
            ->where('indate_start_time', '<=', $now)->where('indate_end_time', '>', $now - 86400)->get(['id', 'count']);
        foreach ($list as $c) {
            if (DB::table('web_member_coupon')->where('mid', $member->id)->where('scid', $c->id)->exists()) continue;
            DB::table('web_member_coupon')->insert(['sid' => $member->sid, 'mid' => $member->id, 'scid' => $c->id, 'count' => (int) $c->count, 'create_time' => $now]);
            DB::table('web_shop_coupon')->where('id', $c->id)->increment('send_count');
        }
        return count($list);
    }
}
N;

$allOk = true;
foreach ($out + ['new' => $newSrc] as $k => $s) {
    $tmp = sys_get_temp_dir() . '/cs_gbpfix9_' . $k . '.php';
    file_put_contents($tmp, $s);
    $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c); @unlink($tmp);
    echo ($c === 0 ? 'OK ' : 'NG ') . "直したあとの文法 " . ($F[$k] ?? $NEWF) . "\n";
    if ($c !== 0) { $allOk = false; echo '   ' . implode(' ', $o) . "\n"; }
}
if (!$allOk) $ng('文法エラー');
echo "OK 直す場所はすべて見つかりました(" . array_sum(array_map('count', $E)) . " か所)・新しいファイル 1 つ\n";
if ($mode !== 'apply') exit("\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n");

if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($F as $f) copy($base . $f, $backup . str_replace('/', '__', $f));
require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    if (!Illuminate\Support\Facades\Schema::hasColumn('web_shop_coupon', 'cs_target')) {
        Illuminate\Support\Facades\DB::statement("ALTER TABLE web_shop_coupon ADD COLUMN cs_target TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0今の会員 1新規会員限定', ADD COLUMN cs_gbp TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Googleの特典にも出す', ADD COLUMN cs_gbp_post VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Googleの特典の投稿', ADD COLUMN cs_gbp_err VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Googleに出せなかった理由'");
        echo "表 web_shop_coupon に cs_target・cs_gbp・cs_gbp_post・cs_gbp_err を足しました\n";
    }
} catch (\Throwable $e) {
    exit("表に欄を足せませんでした。ファイルは変えていません:" . $e->getMessage() . "\n");
}
foreach ($out as $k => $s) file_put_contents($base . $F[$k], $s);
file_put_contents($base . $NEWF, $newSrc);
foreach (array_merge(array_values($F), [$NEWF]) as $f) {
    $o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c);
    if ($c !== 0) {
        foreach ($F as $g) copy($backup . str_replace('/', '__', $g), $base . $g);
        @unlink($base . $NEWF);
        exit("文法エラーがあったので元に戻しました:{$f}\n");
    }
}
@exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan queue:restart 2>&1');
@exec('/etc/init.d/php-fpm-73 reload 2>&1');
echo "完了しました。バックアップ: {$backup}\n";
echo "クーポンの配信(CouponJob)を新しくするため、配信の仕組みを再起動しました(queue:restart)。\n";
