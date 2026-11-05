<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libs\CsLine;
use Illuminate\Http\Request;

/**
 * [カスタムエイド 2026-11-04] 段階9:LINE からの戻り先と合図の受け取り先(ログインなし)
 *  GET  /api/cs-line/callback         LINE ログインの戻り先(全部のお店で同じ住所。LINE ログインのチャネルの「コールバック URL」に入れる)
 *  POST /api/cs-line/webhook/{sid}    Messaging API の Webhook(お店ごと)。署名を確かめてから、友だち追加・ブロックを記録
 */
class CsLinePublicController extends Controller
{
    public function callback(Request $request)
    {
        $r = CsLine::callback((string) $request->code, (string) $request->state, (string) $request->error);
        if (($r['mode'] ?? '') === 'order' && CsLine::backOk($r['back'] ?? '')) {
            $q = $r['ok'] ? ['cs_lt' => $r['ticket']] : ['cs_le' => $r['err']];
            $sep = strpos($r['back'], '?') === false ? '?' : '&';
            return redirect()->away($r['back'] . $sep . http_build_query($q));
        }
        if (($r['mode'] ?? '') === 'test') {
            if (!$r['ok']) return $this->page('送れませんでした', $r['err'] === 'cancel' ? 'LINE のログインをやめました。' : 'もう一度、管理App の「試しに自分の LINE に送る」から押してください。');
            if (empty($r['friend'])) return $this->page('友だち追加をしてください', 'お店の LINE 公式アカウントを友だち追加してから、もう一度「試しに自分の LINE に送る」を押してください。');
            return $r['sent'] ? $this->page('送りました', 'LINE を開いて、テストのお知らせが届いたか確かめてください。この画面は閉じて大丈夫です。')
                : $this->page('送れませんでした', 'LINE の今月の上限に達しているか、LINE につながりませんでした。');
        }
        return $this->page('時間が切れました', 'もう一度、はじめからやり直してください。');
    }

    private function page($title, $text)
    {
        $t = htmlspecialchars($title, ENT_QUOTES);
        $x = htmlspecialchars($text, ENT_QUOTES);
        return response('<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Card-San</title>'
            . '<style>body{margin:0;font-family:-apple-system,"Hiragino Sans",sans-serif;background:#f5f5f7;color:#1d1d1f;text-align:center;padding:72px 24px}</style></head>'
            . '<body><div style="font-size:18px;font-weight:700">' . $t . '</div><div style="font-size:14px;color:#6e6e73;margin-top:12px;line-height:1.8">' . $x . '</div></body></html>')
            ->header('Content-Type', 'text/html; charset=utf-8')->header('Cache-Control', 'no-store');
    }

    public function webhook(Request $request, $sid)
    {
        $ok = CsLine::webhook((int) $sid, (string) $request->getContent(), (string) $request->header('X-Line-Signature', ''));
        return response('', $ok ? 200 : 401);
    }
}
