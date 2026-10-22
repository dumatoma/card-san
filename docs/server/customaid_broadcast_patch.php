<?php
// [カスタムエイド 2026-10-21] 一斉配信(メッセージ配信)で、テキストと写真を1通で送れるようにする
//   1. 一斉配信の表(web_timing_message)に images(写真のURLの一覧・JSON)の欄を足す
//   2. 入力のチェック(TimingMessageValidator):content_type 3(テキスト+写真)を受け付ける
//        ・写真は4枚まで。テキストか写真のどちらか1つは必要
//        ・リンク(url)を付けるときは、写真は1枚だけ(リンク付きの写真)
//   3. 送るしくみ(TimingMessageJob):content_type 3 は「テキスト → 写真(1枚ずつ)」を続けて送る
//        ・Card-San App は続けて届いた写真を1つのかたまりで表示する(2026-10-19 に入れた仕組み)
//        ・リンク付きの写真は、写真にリンクを付けて送る。古いアプリのため、今までどおりリンクの文字も送る
//        ・配信数の数え方は今までどおり(写真が何枚でも1通)
//   4. 一覧・詳しく(TimingMessageController の index・show):images を返す
//   content_type 1(テキストだけ)・2(写真1枚)の動きは今までと同じ
//
// 使い方:
//   php -d disable_functions= /root/customaid_broadcast_patch.php        … 何を変えるかを表示するだけ(何も変更しない)
//   php -d disable_functions= /root/customaid_broadcast_patch.php apply  … 実際に変更する(先にバックアップを取る)
// 何か1つでも想定と違えば、何も変更せずに終了します。文法エラーがあれば自動で元に戻します。

$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_broadcast_20261021/';
$tag = '[カスタムエイド 2026-10-21]';
$apply = (($argv[1] ?? '') === 'apply');

// 空白・改行の違いに強い形で、元の文を探すための正規表現を作る
function looseRe($snippet)
{
    $parts = preg_split('/\s+/', trim($snippet));
    return '/' . implode('\s*', array_map(function ($p) { return preg_quote($p, '/'); }, $parts)) . '/u';
}

// --- 1. 欄 ---
require $base . 'vendor/autoload.php';
$app = require $base . 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$tm = new App\Models\TimingMessageModel();
$db = $tm->getConnection();
$table = $db->getTablePrefix() . $tm->getTable();
$cols = array_map(function ($c) { return $c->Field; }, $db->select("SHOW COLUMNS FROM `$table`"));
if (!in_array('content', $cols)) exit("$table に content の欄がありません。何も変更していません。\n");
$needCol = !in_array('images', $cols);
echo $needCol ? "① $table に images の欄を足します\n" : "① images の欄はもうあります(足しません)\n";

$changes = [];

// --- 2. 入力のチェック ---
$f = 'app/Validators/TimingMessageValidator.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, $tag) !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
$old = <<<'PHP'
        if(!in_array($request->content_type, [1, 2])){
            return ['success' => false, 'message' => __('message.timing_message_content_type_error')];
        }
        $data['content_type'] = $request->content_type;

        if(!$request->content){
            return ['success' => false, 'message' => __('message.timing_message_content_empty')];
        }
        $data['content'] = $request->content;
PHP;
$new = <<<PHP
        // $tag content_type 3 = テキスト+写真(4枚まで)。リンクを付けるときは写真1枚だけ
        if(!in_array(\$request->content_type, [1, 2, 3])){
            return ['success' => false, 'message' => __('message.timing_message_content_type_error')];
        }
        \$data['content_type'] = \$request->content_type;

        if(\$request->content_type == 3){
            \$imgs = \$request->images;
            if(is_string(\$imgs)){
                \$imgs = json_decode(\$imgs, true);
            }
            \$imgs = is_array(\$imgs) ? array_values(array_filter(\$imgs, function (\$v) { return is_string(\$v) && \$v !== ''; })) : [];
            if(count(\$imgs) > 4){
                return ['success' => false, 'message' => '写真は4枚までです'];
            }
            if(trim((string) \$request->content) === '' && count(\$imgs) == 0){
                return ['success' => false, 'message' => __('message.timing_message_content_empty')];
            }
            if((string) (\$request->url ?? '') !== '' && count(\$imgs) != 1){
                return ['success' => false, 'message' => 'リンク付きの写真は1枚だけです'];
            }
            \$data['content'] = (string) (\$request->content ?? '');
            \$data['images'] = json_encode(\$imgs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }else{
            if(!\$request->content){
                return ['success' => false, 'message' => __('message.timing_message_content_empty')];
            }
            \$data['content'] = \$request->content;
        }
PHP;
$re = looseRe($old);
if (preg_match_all($re, $s) !== 1) exit("$f の直す場所が見つかりません(または2つ以上)。何も変更していません。\n");
$changes[$f] = preg_replace_callback($re, function () use ($new) { return trim($new); }, $s, 1);
echo "② $f:content_type 3 を受け付ける\n";

// --- 3. 送るしくみ ---
$f = 'app/Jobs/TimingMessageJob.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, $tag) !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
$old = <<<'PHP'
            $group->update([
                'last_message_sender' => 2,
                'last_message_type' => $timing_message->content_type,
                'last_message' => $timing_message->content,
                'last_message_time' => time(),
                'unread' => $unread
            ]);

            $message_model->setTable('web_message_' . $shop_member->sid . '_' . $shop_member->uid);

            $message = $message_model->create([
                'sid' => $shop_member->sid,
                'uid' => $shop_member->uid,
                'sender' => 1,
                'type' => $timing_message->content_type,
                'message' => $timing_message->content,
                'create_time' => time(),
                'timing_message_id' => $timing_message->id,
                'url' => $timing_message->url,
                'read' => $read
            ]);

            $push_detail = $this->message_service->push($shop_member->sid, $shop_member->uid, $this->message_service->createMessage([$message]));

            $message->update(['push_detail' => $push_detail]);
PHP;
$new = <<<PHP
            // $tag 送るメッセージの一覧を作る。content_type 3 は「テキスト → 写真(1枚ずつ)」を続けて送る
            //   (Card-San App は続けて届いた写真を1つのかたまりで表示する)。1・2 は今までどおり1つ
            if(\$timing_message->content_type == 3){
                \$cs_imgs = json_decode(\$timing_message->images ?: '[]', true) ?: [];
                \$cs_parts = [];
                if(trim((string) \$timing_message->content) !== ''){
                    \$cs_parts[] = ['type' => 1, 'message' => \$timing_message->content, 'url' => ''];
                }
                foreach(\$cs_imgs as \$cs_u){
                    // リンク付きの写真(写真1枚+リンク)は、写真にリンクを付ける
                    \$cs_parts[] = ['type' => 2, 'message' => \$cs_u, 'url' => count(\$cs_imgs) == 1 ? (string) \$timing_message->url : ''];
                }
            }else{
                \$cs_parts = [['type' => \$timing_message->content_type, 'message' => \$timing_message->content, 'url' => \$timing_message->url]];
            }
            if(count(\$cs_parts) == 0){
                continue;
            }
            \$cs_last = \$cs_parts[0]; // 一覧に出す最後のメッセージは、テキストがあればテキスト

            \$group->update([
                'last_message_sender' => 2,
                'last_message_type' => \$cs_last['type'],
                'last_message' => \$cs_last['message'],
                'last_message_time' => time(),
                'unread' => \$unread
            ]);

            \$message_model->setTable('web_message_' . \$shop_member->sid . '_' . \$shop_member->uid);

            \$message = null;
            foreach(\$cs_parts as \$cs_part){
                \$cs_msg = \$message_model->create([
                    'sid' => \$shop_member->sid,
                    'uid' => \$shop_member->uid,
                    'sender' => 1,
                    'type' => \$cs_part['type'],
                    'message' => \$cs_part['message'],
                    'create_time' => time(),
                    'timing_message_id' => \$timing_message->id,
                    'url' => \$cs_part['url'],
                    'read' => \$read
                ]);

                \$push_detail = \$this->message_service->push(\$shop_member->sid, \$shop_member->uid, \$this->message_service->createMessage([\$cs_msg]));

                \$cs_msg->update(['push_detail' => \$push_detail]);

                // スマホへのお知らせ(プッシュ通知)は、最初のメッセージの内容で1回だけ
                if(!\$message){
                    \$message = \$cs_msg;
                }
            }
PHP;
$re = looseRe($old);
if (preg_match_all($re, $s) !== 1) exit("$f の直す場所が見つかりません(または2つ以上)。何も変更していません。\n");
$changes[$f] = preg_replace_callback($re, function () use ($new) { return trim($new); }, $s, 1);
echo "③ $f:テキスト+写真を続けて送る\n";

// --- 4. 一覧・詳しく ---
$f = 'app/Http/Controllers/Api/Shop/TimingMessageController.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, $tag) !== false) exit("すでに修正済みです($f)。何も変更していません。\n");
foreach ([
    ["'content' => \$v->content,", "'images' => json_decode(\$v->images ?: '[]', true) ?: [], // $tag 写真の一覧"],
    ["'content' => \$timing_message->content,", "'images' => json_decode(\$timing_message->images ?: '[]', true) ?: [], // $tag 写真の一覧"],
] as $pair) {
    $re = '/([ \t]*)' . preg_quote($pair[0], '/') . '\n/u';
    if (preg_match_all($re, $s) !== 1) exit("$f に「{$pair[0]}」が1つだけありません。何も変更していません。\n");
    $s = preg_replace_callback($re, function ($m) use ($pair) { return $m[0] . $m[1] . $pair[1] . "\n"; }, $s, 1);
}
$changes[$f] = $s;
echo "④ $f:一覧・詳しくで images を返す\n";

if (!$apply) {
    echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n";
    exit;
}

// --- 実行:バックアップ → 変更 → 文法チェック ---
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($changes as $f => $src) {
    if (!copy($base . $f, $backup . str_replace('/', '__', $f))) exit("バックアップに失敗しました: $f\n何も変更していません。\n");
}
if ($needCol) {
    $db->statement("ALTER TABLE `$table` ADD `images` TEXT NULL AFTER `content`");
    echo "欄を足しました: $table.images\n";
}
foreach ($changes as $f => $src) file_put_contents($base . $f, $src);

$ok = true;
foreach (array_keys($changes) as $f) {
    $out = []; $code = 1;
    exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $out, $code);
    echo implode("\n", $out), "\n";
    if ($code !== 0) $ok = false;
}
if (!$ok) {
    foreach ($changes as $f => $src) copy($backup . str_replace('/', '__', $f), $base . $f);
    exit("文法エラーがあったので、ファイルを元に戻しました(足した欄はそのままですが、使われません)。\n");
}
echo "完了しました。バックアップ: $backup\n";
