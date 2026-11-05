<?php
// [カスタムエイド 2026-11-04] 段階9 追記1:「保存して確かめる」で、LINE ログインの欄に Messaging API のチャネルの ID・シークレットを入れたときも NG にする
//   app/Libs/CsLine.php の save() に確かめを1つ足すだけ
//   php -d disable_functions= /root/s9_line_fix1.php        … 確かめるだけ
//   php -d disable_functions= /root/s9_line_fix1.php apply  … バックアップを取ってから変更する
//   php -d disable_functions= /root/s9_line_fix1.php undo   … 元に戻す
const ADD = <<<'CSADD'
        // 4. [カスタムエイド 2026-11-04 追記] LINE ログインの欄に Messaging API のチャネルの ID・シークレットを入れていないか
        //    (3 の確かめでは、Messaging API のチャネルでも通ってしまうため。短い期間のトークンが取れるのは Messaging API のチャネルだけ ※確認が必要)
        if (!isset($err['login_secret']) && $k['login_secret'] === $k['msg_secret']) {
            $err['login_secret'] = 'Messaging API のチャネルシークレットと同じです。LINE ログインのチャネルのシークレットを入れてください';
        } elseif (!isset($err['login_secret'])) {
            list($c5, $st) = self::http('POST', 'https://api.line.me/v2/oauth/accessToken', [], null, ['grant_type' => 'client_credentials', 'client_id' => $k['login_id'], 'client_secret' => $k['login_secret']]);
            if ($c5 === 200 && !empty($st['access_token'])) {
                self::http('POST', 'https://api.line.me/v2/oauth/revoke', [], null, ['access_token' => $st['access_token']]);
                $err['login_id'] = 'これは Messaging API のチャネルの ID です。LINE ログインのチャネルの ID とシークレットを入れてください';
            }
        }
CSADD;
$base = '/www/wwwroot/api.card-san.jp/';
$f = 'app/Libs/CsLine.php';
$backup = '/www/backup_customaid_s9fix1_20261104/';
$bk = $backup . 'CsLine.php';
$mode = $argv[1] ?? 'check';
if ($mode === 'undo') { if (!is_file($bk)) exit("バックアップがありません。何もしていません。\n"); copy($bk, $base . $f); exit("元に戻しました: {$f}\n"); }
$s = file_get_contents($base . $f);
$ng = function ($m) { exit("NG {$m}\n止めました。何も変更していません。この結果を Claude に送ってください。\n"); };
if (strpos($s, 'v2/oauth/accessToken') !== false) $ng('すでに修正済みです');
$anchor = "        if (\$err) return ['ok' => false, 'msg' => 'つなげませんでした', 'errors' => \$err];\n\n        \$row = [";
if (substr_count($s, $anchor) !== 1) $ng('CsLine.php の足す場所が見つかりません');
$s2 = str_replace($anchor, ADD . $anchor, $s);
$tmp = sys_get_temp_dir() . '/cs_s9f1.php'; file_put_contents($tmp, $s2); $o = []; $c = 1; exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $o, $c); @unlink($tmp);
echo ($c === 0 ? 'OK' : 'NG') . " 直したあとの文法\n"; if ($c !== 0) $ng(implode(' ', $o));
if ($mode !== 'apply') exit("\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n");
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
copy($base . $f, $bk);
file_put_contents($base . $f, $s2);
$o = []; $c = 1; exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $o, $c);
if ($c !== 0) { copy($bk, $base . $f); exit("文法エラーがあったので元に戻しました。\n"); }
echo "完了しました。バックアップ: {$backup}\n";
