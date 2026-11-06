<?php
// [カスタムエイド 2026-11-06] GBP 連携 その5(サーバー):Google からのアクセス・検索キーワード(Business Profile Performance API)
//   GET /api/shop/google_business/insights?days=7|30|90&kw=1|3
//     ・Google からのアクセス:お店の情報が見られた回数(Google 検索・Google マップ)、電話をかけた・道順を調べた・ホームページを見た回数
//       選んだ日数の合計と、その前の同じ日数の合計(↑↓の比べ)、日ごとのグラフ
//     ・検索キーワード:先月 / 過去3ヶ月(Google は月ごとにしか出さない)
//   権限:Googleビジネス連携(16)/ プラン:スタンダード以上(gbp_review。クチコミ管理と同じ)
//   Google への問い合わせは、お店ごとに 6 時間ためておく(Redis)。表は増やさない
//
//   php -d disable_functions= /root/gbp_fix5.php            … 確かめるだけ(何も変えない)
//   php -d disable_functions= /root/gbp_fix5.php apply      … バックアップを取ってから変える
//   php -d disable_functions= /root/gbp_fix5.php undo       … 元に戻す
//   php -d disable_functions= /root/gbp_fix5.php test 店舗ID … (apply のあと)そのお店の数字を Google から取って表示する(見るだけ)

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_gbpfix5_20261106/';
$mode = $argv[1] ?? 'check';
$MARK = 'カスタムエイド 2026-11-06 GBP5';
$F = [
    'lib'   => 'app/Libs/GoogleBusiness.php',
    'svc'   => 'app/Services/GbpService.php',
    'ctl'   => 'app/Http/Controllers/Api/Shop/GoogleBusinessController.php',
    'route' => 'routes/api.php',
];
if ($mode === 'undo') {
    $n = 0;
    foreach ($F as $f) { $b = $backup . str_replace('/', '__', $f); if (is_file($b)) { copy($b, $base . $f); $n++; } }
    if (!$n) exit("バックアップがありません。何もしていません。\n");
    @exec('/etc/init.d/php-fpm-73 reload 2>&1');
    exit("元に戻しました({$n} ファイル)。\n");
}
if ($mode === 'test') {
    $sid = (int) ($argv[2] ?? 0);
    if (!$sid) exit("店舗ID を付けてください(例: php -d disable_functions= /root/gbp_fix5.php test 11)\n");
    require $base . 'vendor/autoload.php';
    $app = require $base . 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $shop = App\Models\ShopModel::find($sid);
    if (!$shop) exit("店舗ID {$sid} がありません。\n");
    foreach ([[30, 1], [90, 3]] as $p) {
        $r = (new App\Services\GbpService())->insights($shop, $p[0], $p[1], true);
        echo "==== {$p[0]}日 / キーワード " . ($p[1] == 1 ? '先月' : '過去3ヶ月') . "\n";
        if (!$r['ok']) { echo "取れませんでした: {$r['msg']}\n"; continue; }
        $d = $r['data'];
        echo "数字は {$d['from']}〜{$d['until']} の分\n";
        echo "見られた回数 {$d['views']['total']}(前の期間 {$d['views']['prev']})  検索 {$d['views']['search']} / マップ {$d['views']['maps']}\n";
        echo "電話をかけた {$d['calls']['total']}(前 {$d['calls']['prev']})  道順を調べた {$d['directions']['total']}(前 {$d['directions']['prev']})  ホームページを見た {$d['website']['total']}(前 {$d['website']['prev']})\n";
        echo "グラフの点の数 " . count($d['series']) . "\n";
        echo "検索キーワード({$d['kw_range']}): " . count($d['keywords']) . " 件 " . ($d['kw_msg'] ? "({$d['kw_msg']})" : '') . "\n";
        foreach (array_slice($d['keywords'], 0, 10) as $i => $k) echo '  ' . ($i + 1) . '. ' . $k['word'] . ' ' . ($k['low'] ? '少数' : $k['value'] . '人') . "\n";
    }
    exit("\n見るだけでした(何も変更していません)。\n");
}
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
$src = [];
foreach ($F as $k => $f) { $src[$k] = file_get_contents($base . $f); if (strpos($src[$k], $MARK) !== false) $ng("すでに直してあります({$f})"); }
if (strpos($src['svc'], 'カスタムエイド 2026-11-06 GBP4') === false) $ng('先に gbp_fix4.php を apply してください');

$E = [];

// ---------- Google の呼び出し(Business Profile Performance API) ----------
$E['lib'][] = ["    // 有効なアクセストークンを取得（期限切れなら自動リフレッシュ）\n", <<<'N'
    /* [カスタムエイド 2026-11-06 GBP5] Business Profile Performance API(Google からのアクセス・検索キーワード) */
    const PERF_API = 'https://businessprofileperformance.googleapis.com/v1';

    /** 日ごとの数字。$start・$end は 'Y-m-d'(両方ふくむ) */
    public function perfDaily($access_token, $location_id, array $metrics, $start, $end)
    {
        $q = [];
        foreach ($metrics as $m) $q[] = 'dailyMetrics=' . rawurlencode($m);
        foreach (['start_date' => $start, 'end_date' => $end] as $k => $v) {
            list($y, $mo, $d) = array_map('intval', explode('-', $v));
            $q[] = "dailyRange.{$k}.year={$y}";
            $q[] = "dailyRange.{$k}.month={$mo}";
            $q[] = "dailyRange.{$k}.day={$d}";
        }
        return $this->call('GET', self::PERF_API . '/' . $location_id . ':fetchMultiDailyMetricsTimeSeries?' . implode('&', $q), $access_token);
    }

    /** 検索キーワード(月ごと)。$from・$to は 'Y-m'(両方ふくむ。Google が合計して返す) */
    public function perfKeywords($access_token, $location_id, $from, $to, $pageToken = '')
    {
        list($fy, $fm) = array_map('intval', explode('-', $from));
        list($ty, $tm) = array_map('intval', explode('-', $to));
        $q = "monthlyRange.start_month.year={$fy}&monthlyRange.start_month.month={$fm}&monthlyRange.end_month.year={$ty}&monthlyRange.end_month.month={$tm}&pageSize=100";
        if ($pageToken !== '') $q .= '&pageToken=' . rawurlencode($pageToken);
        return $this->call('GET', self::PERF_API . '/' . $location_id . '/searchkeywords/impressions/monthly?' . $q, $access_token);
    }

    // 有効なアクセストークンを取得（期限切れなら自動リフレッシュ）

N];

// ---------- 数字をまとめる ----------
$E['svc'][] = ["    /* ======================= [カスタムエイド 2026-11-06 GBP4] 予約リンク ======================= */\n", <<<'N'
    /* ======================= [カスタムエイド 2026-11-06 GBP5] Google からのアクセス・検索キーワード ======================= */

    const PERF_METRICS = [
        'BUSINESS_IMPRESSIONS_MOBILE_SEARCH' => 'search', 'BUSINESS_IMPRESSIONS_DESKTOP_SEARCH' => 'search',
        'BUSINESS_IMPRESSIONS_MOBILE_MAPS' => 'maps', 'BUSINESS_IMPRESSIONS_DESKTOP_MAPS' => 'maps',
        'CALL_CLICKS' => 'calls', 'BUSINESS_DIRECTION_REQUESTS' => 'directions', 'WEBSITE_CLICKS' => 'website',
    ];

    private static function perfErrText($r)
    {
        $m = (string) ($r['message'] ?? '') . ' ' . json_encode($r['data']['error'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (stripos($m, 'has not been used') !== false || stripos($m, 'is disabled') !== false || stripos($m, 'SERVICE_DISABLED') !== false) {
            return 'Google 側の準備が整っていないため、数字を取り込めませんでした。Card-San のサポートにお問い合わせください。';
        }
        return 'Google から数字を取り込めませんでした。しばらくしてからもう一度お試しください。';
    }

    /** 日ごとの数字(約200日分)。6時間ためておく */
    private function perfRows($shop, $token, $loc, $fresh)
    {
        $key = 'gbp_perf_' . $shop->id . '_' . md5($loc);
        if (!$fresh) { try { $c = Redis::get($key); if ($c) { $d = json_decode($c, true); if (is_array($d)) return ['ok' => 1, 'rows' => $d]; } } catch (\Throwable $e) {} }
        $tz = new \DateTimeZone('Asia/Tokyo');
        $end = (new \DateTime('yesterday', $tz))->format('Y-m-d');
        $start = (new \DateTime('-200 days', $tz))->format('Y-m-d');
        $r = $this->api->perfDaily($token, $loc, array_keys(self::PERF_METRICS), $start, $end);
        if (!$r['success']) {
            Log::info('[gbp5] アクセスを取れませんでした shop ' . $shop->id . ' ' . ($r['message'] ?? ''));
            return ['ok' => 0, 'msg' => self::perfErrText($r)];
        }
        $rows = []; // 'Y-m-d' => [search, maps, calls, directions, website]
        foreach ((array) ($r['data']['multiDailyMetricTimeSeries'] ?? []) as $grp) {
            foreach ((array) ($grp['dailyMetricTimeSeries'] ?? []) as $ts) {
                $col = self::PERF_METRICS[$ts['dailyMetric'] ?? ''] ?? null;
                if (!$col) continue;
                foreach ((array) ($ts['timeSeries']['datedValues'] ?? []) as $dv) {
                    $dt = $dv['date'] ?? [];
                    if (empty($dt['year'])) continue;
                    $day = sprintf('%04d-%02d-%02d', $dt['year'], $dt['month'], $dt['day']);
                    if (!isset($rows[$day])) $rows[$day] = ['search' => 0, 'maps' => 0, 'calls' => 0, 'directions' => 0, 'website' => 0, 'has' => 0];
                    if (isset($dv['value'])) { $rows[$day][$col] += (int) $dv['value']; $rows[$day]['has'] = 1; }
                }
            }
        }
        ksort($rows);
        try { Redis::setex($key, 21600, json_encode($rows)); } catch (\Throwable $e) {}
        return ['ok' => 1, 'rows' => $rows];
    }

    /** 検索キーワード。過ぎた月の分は 7 日、まだ届いていないときは 6 時間ためておく */
    private function perfKeywordList($shop, $token, $loc, $months, $fresh)
    {
        $tz = new \DateTimeZone('Asia/Tokyo');
        $to = (new \DateTime('first day of last month', $tz))->format('Y-m');
        $from = (new \DateTime('first day of -' . $months . ' month', $tz))->format('Y-m');
        $key = 'gbp_kw_' . $shop->id . '_' . md5($loc) . '_' . $from . '_' . $to;
        $label = $from === $to ? self::ymText($to) : self::ymText($from) . '〜' . self::ymText($to);
        if (!$fresh) { try { $c = Redis::get($key); if ($c) { $d = json_decode($c, true); if (is_array($d)) return ['ok' => 1, 'list' => $d, 'label' => $label]; } } catch (\Throwable $e) {} }
        $list = []; $page = '';
        for ($i = 0; $i < 5; $i++) {
            $r = $this->api->perfKeywords($token, $loc, $from, $to, $page);
            if (!$r['success']) {
                Log::info('[gbp5] キーワードを取れませんでした shop ' . $shop->id . ' ' . ($r['message'] ?? ''));
                return ['ok' => 0, 'msg' => self::perfErrText($r), 'label' => $label];
            }
            foreach ((array) ($r['data']['searchKeywordsCounts'] ?? []) as $k) {
                $w = (string) ($k['searchKeyword'] ?? '');
                if ($w === '') continue;
                $v = $k['insightsValue'] ?? [];
                $list[] = isset($v['value']) ? ['word' => $w, 'value' => (int) $v['value'], 'low' => 0] : ['word' => $w, 'value' => 0, 'low' => 1, 'under' => (int) ($v['threshold'] ?? 0)];
            }
            $page = (string) ($r['data']['nextPageToken'] ?? '');
            if ($page === '') break;
        }
        usort($list, function ($a, $b) { return $b['value'] <=> $a['value'] ?: $a['low'] <=> $b['low']; });
        $list = array_slice($list, 0, 50);
        try { Redis::setex($key, $list ? 604800 : 21600, json_encode($list, JSON_UNESCAPED_UNICODE)); } catch (\Throwable $e) {}
        return ['ok' => 1, 'list' => $list, 'label' => $label];
    }

    private static function ymText($ym)
    {
        list($y, $m) = explode('-', $ym);
        return (int) $y . '年' . (int) $m . '月';
    }

    /** 管理App・管理WEB の「Google からのアクセス」画面の数字 */
    public function insights($shop, $days, $kwMonths, $fresh = false)
    {
        $days = in_array((int) $days, [7, 30, 90], true) ? (int) $days : 30;
        $kwMonths = (int) $kwMonths === 3 ? 3 : 1;
        $loc = self::placeLocationId($shop);
        $tk = $this->api->getValidToken($shop);
        if ($loc === '' || !$tk['success']) return ['ok' => 0, 'msg' => 'Google との連携を確かめられませんでした。もう一度連携してください。'];
        $p = $this->perfRows($shop, $tk['token'], $loc, $fresh);
        if (!$p['ok']) return $p;
        $rows = $p['rows'];
        // Google の数字は数日おくれて入る。数字が入っている最後の日までを「最近」とする(10日より前にはさかのぼらない)
        $tz = new \DateTimeZone('Asia/Tokyo');
        $last = (new \DateTime('yesterday', $tz));
        for ($i = 0; $i < 10; $i++) {
            if (!empty($rows[$last->format('Y-m-d')]['has'])) break;
            $last->modify('-1 day');
        }
        if ($i >= 10) $last = new \DateTime('-3 days', $tz);
        $sum = function ($from, $to) use ($rows) {
            $s = ['search' => 0, 'maps' => 0, 'calls' => 0, 'directions' => 0, 'website' => 0];
            $d = clone $from;
            while ($d <= $to) { $r = $rows[$d->format('Y-m-d')] ?? null; if ($r) foreach ($s as $k => $v) $s[$k] += $r[$k]; $d->modify('+1 day'); }
            return $s;
        };
        $to = clone $last; $to->setTime(0, 0);
        $from = (clone $to)->modify('-' . ($days - 1) . ' days');
        $pTo = (clone $from)->modify('-1 day');
        $pFrom = (clone $pTo)->modify('-' . ($days - 1) . ' days');
        $cur = $sum($from, $to);
        $prev = $sum($pFrom, $pTo);
        $series = [];
        $d = clone $from;
        while ($d <= $to) { $r = $rows[$d->format('Y-m-d')] ?? null; $series[] = ['d' => $d->format('n/j'), 'v' => $r ? $r['search'] + $r['maps'] : 0]; $d->modify('+1 day'); }
        $kw = $this->perfKeywordList($shop, $tk['token'], $loc, $kwMonths, $fresh);
        return ['ok' => 1, 'data' => [
            'days'       => $days,
            'from'       => $from->format('n/j'),
            'until'      => $to->format('n/j'),
            'views'      => ['total' => $cur['search'] + $cur['maps'], 'prev' => $prev['search'] + $prev['maps'], 'search' => $cur['search'], 'maps' => $cur['maps']],
            'calls'      => ['total' => $cur['calls'], 'prev' => $prev['calls']],
            'directions' => ['total' => $cur['directions'], 'prev' => $prev['directions']],
            'website'    => ['total' => $cur['website'], 'prev' => $prev['website']],
            'series'     => $series,
            'kw_months'  => $kwMonths,
            'kw_range'   => $kw['label'] ?? '',
            'keywords'   => $kw['ok'] ? $kw['list'] : [],
            'kw_msg'     => $kw['ok'] ? ($kw['list'] ? '' : 'この期間の検索キーワードは、まだ Google から届いていません。') : $kw['msg'],
        ]];
    }

    /* ======================= [カスタムエイド 2026-11-06 GBP4] 予約リンク ======================= */

N];

// ---------- コントローラー ----------
$E['ctl'][] = ["    // GET /api/shop/google_business/overview\n", <<<'N'
    // [カスタムエイド 2026-11-06 GBP5] GET /api/shop/google_business/insights?days=7|30|90&kw=1|3 — Google からのアクセス・検索キーワード
    public function insights(Request $request)
    {
        if ($g = $this->guard(16, 'gbp_review')) return $g; // 権限:Googleビジネス連携 / プラン:スタンダード以上
        $shop = auth('shop')->user()->shop;
        if (!GbpService::connected($shop)) return responseToJson(400, 'Googleビジネスプロフィールと連携されていません');
        $r = (new GbpService())->insights($shop, (int) $request->input('days', 30), (int) $request->input('kw', 1));
        if (!$r['ok']) return responseToJson(400, $r['msg']);
        return responseToJson(200, '', $r['data']);
    }

    // GET /api/shop/google_business/overview

N];

// ---------- 道(ルート) ----------
$E['route'][] = ["                Route::put('/features',                    'GoogleBusinessController@features');\n",
                 "                Route::put('/features',                    'GoogleBusinessController@features');\n"
               . "                Route::get('/insights',                    'GoogleBusinessController@insights'); // [{$MARK}] Google からのアクセス・検索キーワード\n"];

// ---------- 当てる(メモリの上で) ----------
$out = $src;
foreach ($E as $k => $list) foreach ($list as $i => $p) {
    $n = substr_count($out[$k], $p[0]);
    if ($n !== 1) $ng($F[$k] . ' の直す場所 ' . ($i + 1) . ' が見つかりません(見つかった数 ' . $n . ')');
    $out[$k] = str_replace($p[0], $p[1], $out[$k]);
}
$allOk = true;
foreach ($out as $k => $s) {
    $tmp = sys_get_temp_dir() . '/cs_gbpfix5_' . $k . '.php';
    file_put_contents($tmp, $s);
    $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c); @unlink($tmp);
    echo ($c === 0 ? 'OK ' : 'NG ') . "直したあとの文法 {$F[$k]}\n";
    if ($c !== 0) { $allOk = false; echo '   ' . implode(' ', $o) . "\n"; }
}
if (!$allOk) $ng('文法エラー');
if (is_file($base . 'bootstrap/cache/routes-v7.php') || is_file($base . 'bootstrap/cache/routes.php')) echo "注意:ルートのキャッシュがあります(この結果を Claude に送ってください)\n";
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
$o = [];
exec('cd ' . escapeshellarg($base) . ' && php -d disable_functions= artisan route:list 2>&1 | grep -c google_business/insights', $o);
echo "insights の道:" . (trim(implode('', $o)) === '1' ? 'OK' : '確認が必要') . "\n";
echo "完了しました。バックアップ: {$backup}\n";
echo "続けて test を実行すると、Foot Comfort(店舗ID 11)の数字を見られます:\n php -d disable_functions= /root/gbp_fix5.php test 11\n";
