<?php
// [カスタムエイド 2026-10-24] カルテ:画像編集(切り抜き・明るさ・回転・元に戻す)と、テンプレートの画像編集
//   1. routes/api.php:受け口を2つ足す
//        POST /api/shop/karte/page/{id}/edit  … 保存済みのページ(カルテ・写真)の画像を直す。restore=1 で元の画像に戻す
//        POST /api/shop/karte/tpl/{id}/image  … テンプレートの画像を直す。restore=1 で元の画像に戻す
//   2. CsKarteController:上の2つの処理を足す。ページ・テンプレートの一覧に「直したことがあるか(edited)」を返す。
//      言葉を「白紙カルテ」→「テンプレート」に
//   直すのは画像だけ。書き込み(文字・図形・メモ)とテンプレートの差し込みは消さず、画像の中の同じ場所についていくように位置だけ直す。
//   元の画像と、今までの直し(回転・切り抜き・明るさ・コントラスト)は storage/app/karte/edit/ に覚えておき、
//   直すたびに元の画像から作り直す(何度直しても画質が落ちない)
//   使い方: php customaid_karte_imgedit_patch.php        … 確認だけ(何も変更しない)
//           php customaid_karte_imgedit_patch.php apply  … 変更する(バックアップを取ってから)
$base = '/www/wwwroot/api.card-san.jp/';
$backup = '/www/backup_customaid_karte_imgedit_20261024/';
$tag = '[カスタムエイド 2026-10-24] 画像編集';
$apply = (($argv[1] ?? '') === 'apply');
$changes = [];

// ---- 1. routes/api.php ----
$f = 'routes/api.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, 'CsKarteController@editPage') !== false) exit("すでに画像編集の受け口があります($f)。何も変更していません。\n");
$re = "/([ \\t]*)Route::post\\('\\/karte\\/page\\/\\{id\\}\\/delete', 'CsKarteController@destroyPage'\\);[^\\n]*\\n/u";
if (preg_match_all($re, $s) !== 1) exit("$f にページを消す受け口の行が1つだけありません。何も変更していません。\n");
$s = preg_replace_callback($re, function ($m) use ($tag) {
    return $m[0]
        . $m[1] . "Route::post('/karte/page/{id}/edit', 'CsKarteController@editPage'); // $tag\n"
        . $m[1] . "Route::post('/karte/tpl/{id}/image', 'CsKarteController@tplImage'); // $tag\n";
}, $s, 1);
$changes[$f] = $s;
echo "① $f:受け口を2つ足す(page/{id}/edit・tpl/{id}/image)\n";

// ---- 2. CsKarteController ----
$f = 'app/Http/Controllers/Api/Shop/CsKarteController.php';
$s = file_get_contents($base . $f);
if ($s === false) exit("読み込みに失敗しました: $f\n何も変更していません。\n");
if (strpos($s, 'function editPage') !== false) exit("すでに editPage があります($f)。何も変更していません。\n");

// 2-1 ページの返し方に edited を足す
$old = "'url' => \$p->file ? CsKarte::sign(\$p->file) : '', 'w' => (int) \$p->width, 'h' => (int) \$p->height,";
if (substr_count($s, $old) !== 1) exit("$f の pageOut の行が1つだけありません。何も変更していません。\n");
$s = str_replace($old, $old . "\n            'edited' => is_file(CsKarte::root('edit/p' . (int) \$p->id . '.json')), // $tag:直したことがあれば true(元に戻せる)", $s);

// 2-2 テンプレートの一覧に edited を足す
$old = "'marks' => json_decode((string) (\$t->marks ?? ''), true) ?: []];";
if (substr_count($s, $old) !== 1) exit("$f の tplIndex の marks の行が1つだけありません。何も変更していません。\n");
$s = str_replace($old, "'marks' => json_decode((string) (\$t->marks ?? ''), true) ?: [],\n                    'edited' => is_file(CsKarte::root('edit/t' . (int) \$t->id . '.json'))]; // $tag", $s);

// 2-3 言葉:「白紙カルテ」→「テンプレート」
$n = substr_count($s, '白紙カルテ');
$s = str_replace('白紙カルテ', 'テンプレート', $s);

// 2-4 画像編集の処理を足す(テンプレートの一覧の前に)
$anchor = '    // ---- テンプレート(3種類まで)----';
if (substr_count($s, $anchor) !== 1) exit("$f にテンプレートの見出しの行が1つだけありません。何も変更していません。\n");
$code = <<<'PHPCODE'
    // ---- [カスタムエイド 2026-10-24] 画像編集(切り抜き・明るさ・回転・元に戻す)----
    //   直すのは画像だけ。書き込み(文字・図形・メモ)とテンプレートの差し込みは消さず、画像の中の同じ場所についていくように位置を直す
    //   (切り抜いて外に出たものは、画像の端の内側に寄せる)。文字の大きさ・向きは変えない
    //   元の画像と今までの直しを storage/app/karte/edit/{p|t}{ID}.json に覚え、直すたびに元の画像から作り直す
    private static function rotPt($p, $n) { for ($i = 0; $i < $n; $i++) $p = [1 - $p[1], $p[0]]; return $p; }       // 時計回りに 90°×n
    private static function rotPtBack($p, $n) { for ($i = 0; $i < $n; $i++) $p = [$p[1], 1 - $p[0]]; return $p; }   // 反時計回りに 90°×n
    private static function rotRect($c, $n) { for ($i = 0; $i < $n; $i++) $c = [1 - $c[1] - $c[3], $c[0], $c[3], $c[2]]; return $c; }
    private static function clamp01($v) { return round(min(1, max(0, (float) $v)), 5); }

    private function editState($kind, $id, $file, $w, $h)
    {
        $rel = 'edit/' . $kind . (int) $id . '.json';
        $s = is_file(CsKarte::root($rel)) ? json_decode((string) @file_get_contents(CsKarte::root($rel)), true) : null;
        if (!is_array($s) || empty($s['file']) || !is_file(CsKarte::root($s['file']))) {
            $s = ['file' => $file, 'w' => (int) $w, 'h' => (int) $h, 'rot' => 0, 'crop' => [0, 0, 1, 1], 'bright' => 0, 'contrast' => 0];
        }
        return [$rel, $s];
    }

    // 送られてきた直し。戻り値:[回転, 切り抜き[x,y,w,h], 明るさ, コントラスト] か エラーの文字
    private function editInput(Request $request)
    {
        $rot = (((int) $request->rotate % 360) + 360) % 360;
        if (!in_array($rot, [0, 90, 180, 270], true)) return '回転の角度が正しくありません';
        $c = $request->crop;
        if (is_string($c)) $c = json_decode($c, true);
        if (is_array($c) && count($c) == 4) {
            $c = array_map('floatval', array_values($c));
            $c[0] = min(0.95, max(0, $c[0])); $c[1] = min(0.95, max(0, $c[1]));
            $c[2] = min(1 - $c[0], max(0.05, $c[2])); $c[3] = min(1 - $c[1], max(0.05, $c[3]));
        } else {
            $c = [0, 0, 1, 1];
        }
        return [$rot, $c, max(-100, min(100, (int) $request->bright)), max(-100, min(100, (int) $request->contrast))];
    }

    // 今の画像の上の位置 → 直したあとの画像の上の位置
    private static function mapFwd($p, $in)
    {
        $q = self::rotPt([(float) $p[0], (float) $p[1]], intdiv($in[0], 90));
        $c = $in[1];
        return [self::clamp01(($q[0] - $c[0]) / $c[2]), self::clamp01(($q[1] - $c[1]) / $c[3])];
    }

    // 今の画像の上の位置 → 元の画像の上の位置(元に戻すとき)
    private static function mapBack($p, $s)
    {
        $C = $s['crop'];
        $q = [$C[0] + (float) $p[0] * $C[2], $C[1] + (float) $p[1] * $C[3]];
        $q = self::rotPtBack($q, intdiv((int) $s['rot'], 90));
        return [self::clamp01($q[0]), self::clamp01($q[1])];
    }

    // 直しを元の画像からかけ直して保存する。戻り値:[新しい状態, [rel, 幅, 高さ, バイト]] か エラーの文字
    private function editApply($s, $restore, $in)
    {
        if ($restore) {
            $n = ['file' => $s['file'], 'w' => (int) $s['w'], 'h' => (int) $s['h'], 'rot' => 0, 'crop' => [0, 0, 1, 1], 'bright' => 0, 'contrast' => 0];
            return [$n, [$s['file'], (int) $s['w'], (int) $s['h'], (int) @filesize(CsKarte::root($s['file']))]];
        }
        [$rot, $c, $b, $k] = $in;
        $C = self::rotRect($s['crop'], intdiv($rot, 90));
        $C = [$C[0] + $c[0] * $C[2], $C[1] + $c[1] * $C[3], $c[2] * $C[2], $c[3] * $C[3]];
        $n = ['file' => $s['file'], 'w' => (int) $s['w'], 'h' => (int) $s['h'], 'rot' => ((int) $s['rot'] + $rot) % 360,
            'crop' => array_map(function ($v) { return round($v, 6); }, $C),
            'bright' => max(-100, min(100, (int) $s['bright'] + $b)), 'contrast' => max(-100, min(100, (int) $s['contrast'] + $k))];
        @set_time_limit(120);
        $img = CsKarte::saveImage(CsKarte::root($s['file']), $this->sid(), ['rotate' => $n['rot'], 'crop' => $n['crop'], 'bright' => $n['bright'], 'contrast' => $n['contrast']]);
        if (is_string($img)) return $img;
        return [$n, $img];
    }

    // 状態を保存し、使わなくなった画像を消す(元の画像は消さない)
    private function editFinish($rel, $s, $n, $restore, $oldFile, $newFile)
    {
        if ($restore) {
            @unlink(CsKarte::root($rel));
        } else {
            if (!is_dir(CsKarte::root('edit'))) @mkdir(CsKarte::root('edit'), 0775, true);
            file_put_contents(CsKarte::root($rel), json_encode($n));
        }
        if ($oldFile && $oldFile !== $s['file'] && $oldFile !== $newFile && strpos($oldFile, '..') === false) @unlink(CsKarte::root($oldFile));
    }

    // 保存済みのページ(カルテ・写真)の画像を直す。restore=1 で元の画像に戻す
    public function editPage(Request $request, $id)
    {
        if ($r = $this->guard()) return $r;
        $p = DB::table('web_cs_karte_page')->where('id', (int) $id)->where('sid', $this->sid())->whereNull('deleted_at')->first();
        if (!$p || !$this->karte($p->karte_id)) return responseToJson(400, 'ページが見つかりません');
        if ((int) $p->status !== 0 || !$p->file || !is_file(CsKarte::root($p->file))) return responseToJson(400, 'このページは取り込み中です');
        $restore = (string) $request->restore === '1';
        $in = $restore ? null : $this->editInput($request);
        if (is_string($in)) return responseToJson(400, $in);
        [$rel, $s] = $this->editState('p', $p->id, $p->file, $p->width, $p->height);
        if ($restore && $p->file === $s['file']) return responseToJson(200, '元の画像のままです', ['page' => $this->pageOut($p)]);
        $res = $this->editApply($s, $restore, $in);
        if (is_string($res)) return responseToJson(400, $res);
        [$n, [$file, $w, $h, $bytes]] = $res;
        $map = function ($q) use ($restore, $in, $s) { return $restore ? self::mapBack($q, $s) : self::mapFwd($q, $in); };
        DB::transaction(function () use ($p, $map, $file, $w, $h, $bytes) {
            foreach (DB::table('web_cs_karte_mark')->where('page_id', $p->id)->whereNull('deleted_at')->where('parent_id', 0)->get() as $m) {
                $xy = $map([$m->x, $m->y]);
                $u = ['x' => $xy[0], 'y' => $xy[1]];
                if ((int) $m->type === 2) {
                    $d = json_decode((string) $m->data, true) ?: [];
                    if (!empty($d['points']) && is_array($d['points'])) {
                        $d['points'] = array_map($map, $d['points']);
                        $u['data'] = json_encode($d, JSON_UNESCAPED_UNICODE);
                        $u['x'] = $d['points'][0][0];
                        $u['y'] = $d['points'][0][1];
                    }
                }
                DB::table('web_cs_karte_mark')->where('id', $m->id)->update($u);
            }
            DB::table('web_cs_karte_page')->where('id', $p->id)->update(['file' => $file, 'width' => $w, 'height' => $h, 'bytes' => $bytes]);
        });
        $this->editFinish($rel, $s, $n, $restore, $p->file, $file);
        $this->touch($p->karte_id);
        $p2 = DB::table('web_cs_karte_page')->where('id', $p->id)->first();
        return responseToJson(200, $restore ? '元の画像に戻しました' : '画像を直しました', ['page' => $this->pageOut($p2)]);
    }

    // テンプレートの画像を直す(差し込みの位置もいっしょに直す)。restore=1 で元の画像に戻す
    public function tplImage(Request $request, $id)
    {
        if ($r = $this->guard()) return $r;
        $t = DB::table('web_cs_karte_tpl')->where('id', (int) $id)->where('sid', $this->sid())->whereNull('deleted_at')->first();
        if (!$t) return responseToJson(400, 'テンプレートが見つかりません');
        if ((int) $t->status !== 0 || !$t->file || !is_file(CsKarte::root($t->file))) return responseToJson(400, 'このテンプレートは取り込み中です');
        $restore = (string) $request->restore === '1';
        $in = $restore ? null : $this->editInput($request);
        if (is_string($in)) return responseToJson(400, $in);
        [$rel, $s] = $this->editState('t', $t->id, $t->file, $t->width, $t->height);
        if ($restore && $t->file === $s['file']) return responseToJson(200, '元の画像のままです');
        $res = $this->editApply($s, $restore, $in);
        if (is_string($res)) return responseToJson(400, $res);
        [$n, [$file, $w, $h, $bytes]] = $res;
        $tags = [];
        foreach ((json_decode((string) ($t->marks ?? ''), true) ?: []) as $g) {
            $xy = $restore ? self::mapBack([$g['x'] ?? 0, $g['y'] ?? 0], $s) : self::mapFwd([$g['x'] ?? 0, $g['y'] ?? 0], $in);
            $g['x'] = $xy[0];
            $g['y'] = $xy[1];
            $tags[] = $g;
        }
        DB::table('web_cs_karte_tpl')->where('id', $t->id)->update(['file' => $file, 'width' => $w, 'height' => $h, 'bytes' => $bytes, 'marks' => json_encode($tags, JSON_UNESCAPED_UNICODE)]);
        $this->editFinish($rel, $s, $n, $restore, $t->file, $file);
        return responseToJson(200, $restore ? '元の画像に戻しました' : '画像を直しました', [
            'url' => CsKarte::sign($file), 'w' => (int) $w, 'h' => (int) $h, 'marks' => $tags, 'edited' => !$restore,
        ]);
    }


PHPCODE;
$s = str_replace($anchor, $code . $anchor, $s);
$changes[$f] = $s;
echo "② $f:editPage・tplImage を足す/一覧に edited を返す/「白紙カルテ」→「テンプレート」({$n}か所)\n";

if (!$apply) { echo "\n確認だけしました(何も変更していません)。問題なければ、最後に apply を付けて実行してください。\n"; exit; }
if (!is_dir($backup) && !mkdir($backup, 0700, true)) exit("バックアップ先を作れません。何も変更していません。\n");
foreach ($changes as $f => $src) if (!copy($base . $f, $backup . str_replace('/', '__', $f))) exit("バックアップに失敗しました: $f\n何も変更していません。\n");
foreach ($changes as $f => $src) file_put_contents($base . $f, $src);
$ok = true;
foreach (array_keys($changes) as $f) {
    $out = []; $code = 1;
    exec('php -l ' . escapeshellarg($base . $f) . ' 2>&1', $out, $code);
    echo implode("\n", $out), "\n";
    if ($code !== 0) $ok = false;
}
if (!$ok) { foreach ($changes as $f => $src) copy($backup . str_replace('/', '__', $f), $base . $f); exit("文法エラーがあったので、元に戻しました。\n"); }
if (glob($base . 'bootstrap/cache/routes*.php')) {
    $out = [];
    exec('cd ' . escapeshellarg($base) . ' && php artisan route:cache 2>&1', $out);
    echo "通信先の一覧を作り直しました: ", implode(' ', $out), "\n";
}
echo "完了しました。バックアップ: $backup\n";
