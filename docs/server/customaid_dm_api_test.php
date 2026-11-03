<?php
// [カスタムエイド 2026-10-29] DirectMail を SMTP(パスワード)ではなく API で送れるか試す。鍵は SMS と同じ Alibaba の鍵を使う(値は出さない)
//   予約409(店舗11)の「予約受付」のメールを1通送る。使い方: php -d disable_functions= customaid_dm_api_test.php 送り先
$b = '/www/wwwroot/api.card-san.jp/'; chdir($b);
$to = trim($argv[1] ?? ''); if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { echo "使い方:php -d disable_functions= customaid_dm_api_test.php 送り先\n"; exit(1); }
require $b . 'vendor/autoload.php'; $app = require $b . 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
new App\Libs\AlibabaCloudSms(); // SMS の部品と同じ鍵で、Alibaba につなぐ準備をする
$o = App\Models\OrderModel::find(409); if (!$o || (int) $o->sid !== 11) { echo "予約409(店舗11)が見つかりません\n"; exit(1); }
$p = App\Libs\CsSmsPage::parts('confirm', $o, $o->name); $code = App\Libs\CsSmsPage::make('confirm', $o, $o->name);
$url = $code ? App\Libs\CsSmsPage::base() . $code : '';
$html = view('cs_reserve_mail', ['p' => $p, 'url' => $url])->render(); $text = App\Libs\CsReserveMail::text($p, $url);
try {
    $r = AlibabaCloud\Client\AlibabaCloud::rpc()->product('Dm')->version('2015-11-23')->action('SingleSendMail')->method('POST')
        ->host('dm.ap-southeast-1.aliyuncs.com')->options(['query' => ['RegionId' => 'ap-southeast-1'], 'form_params' => ['AccountName' => 'no-reply@mail.card-san.jp', 'AddressType' => 1,
            'ReplyToAddress' => 'false', 'ToAddress' => $to, 'FromAlias' => $p['shop']['name'] ?: 'Card-San', 'Subject' => '【' . $p['shop']['name'] . '】ご予約を受け付けました',
            'HtmlBody' => $html, 'TextBody' => $text]])->request();
    $a = $r->toArray(); echo "OK 送りました(EnvId " . ($a['EnvId'] ?? '?') . ")\n";
} catch (\Throwable $e) { echo "NG " . substr($e->getMessage(), 0, 300) . "\n"; exit(1); }
