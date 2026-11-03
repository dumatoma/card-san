<?php
// [カスタムエイド 2026-10-29] 予約メールを DirectMail の API で送るように変える(SMTP はパスワードで断られたため。鍵は SMS と同じ Alibaba の鍵)
//   使い方: php -d disable_functions= customaid_mail5_api_patch.php → 確認だけ / ... apply → 控えを取ってから変える
$b = '/www/wwwroot/api.card-san.jp/'; $f = 'app/Libs/CsReserveMail.php'; $apply = ($argv[1] ?? '') === 'apply';
$old1 = " || !env('CS_RESERVE_MAIL_PASSWORD')) return false;";
$new1 = ") return false;";
$old2 = "            \$from = self::from(); \$dom = self::domain();
            self::mailer()->send([], [], function (\$m) use (\$to, \$subject, \$html, \$text, \$from, \$shop, \$dom) {
                \$m->getSwiftMessage()->setId(bin2hex(random_bytes(12)) . '@' . \$dom);
                \$m->from(\$from, \$shop !== '' ? \$shop : 'Card-San')->to(\$to)->subject(\$subject);
                \$m->setBody(\$html, 'text/html');
                \$m->addPart(\$text, 'text/plain');
            });
";
$new2 = "            // [カスタムエイド 2026-10-29] DirectMail の API で送る(鍵は SMS と同じ。送り手の名前は15文字まで)
            new AlibabaCloudSms(); // 鍵の準備(SMS の部品と同じ)
            \$alias = mb_strlen(\$shop) > 15 ? mb_substr(\$shop, 0, 15) : \$shop;
            \\AlibabaCloud\\Client\\AlibabaCloud::rpc()->product('Dm')->version('2015-11-23')->action('SingleSendMail')->method('POST')
                ->host(env('CS_RESERVE_MAIL_API_HOST', 'dm.ap-southeast-1.aliyuncs.com'))->options(['query' => ['RegionId' => 'ap-southeast-1'], 'form_params' => [
                    'AccountName' => self::from(), 'AddressType' => 1, 'ReplyToAddress' => 'false', 'ToAddress' => \$to, 'FromAlias' => \$alias !== '' ? \$alias : 'Card-San',
                    'Subject' => \$subject, 'HtmlBody' => \$html, 'TextBody' => \$text]])->request();
";
$s = file_get_contents($b . $f); $ng = 0;
foreach ([['パスワードの確かめ', $old1, $new1], ['送り方を API に', $old2, $new2]] as $e) { $n = substr_count($s, $e[1]); echo ($n === 1 ? 'OK ' : 'NG ') . "$e[0]:見つかった数 $n\n"; if ($n !== 1) $ng++; else $s = str_replace($e[1], $e[2], $s); }
if ($ng) { echo "\n止めました(何も変えていません)。この結果を Claude に送ってください。\n"; exit(1); }
if (!$apply) { echo "\n確認だけしました(何も変えていません)。問題なければ apply を付けて実行してください。\n"; exit(0); }
$bk = '/www/backup_customaid_mail5_api_' . date('Ymd_His') . '/'; mkdir($bk, 0700, true); copy($b . $f, $bk . basename($f));
file_put_contents($b . $f, $s);
exec('php -l ' . escapeshellarg($b . $f) . ' 2>&1', $o, $r);
if ($r !== 0) { copy($bk . basename($f), $b . $f); echo "文法NG のため元に戻しました。この結果を Claude に送ってください。\n"; exit(1); }
echo "文法OK $f\n\n変えました。控え: $bk\n";
