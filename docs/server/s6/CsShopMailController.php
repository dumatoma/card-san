<?php

namespace App\Http\Controllers;

use App\Libs\CsShopMail;

// [カスタムエイド 2026-10-30] 段階6:お店の予約受付用メールアドレスの確認のリンク(/cs-mail-ok/{token})
class CsShopMailController extends Controller
{
    public function verify($token)
    {
        $r = CsShopMail::verify($token);
        $e = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
        $body = $r['ok']
            ? '<div class="i" style="background:#2B8A87">✓</div><b>メールアドレスの確認が終わりました</b><p>' . $e($r['shop']) . '<br>' . $e($r['email']) . '</p><p class="s">お客様が予約のメールに返信すると、このアドレスに届きます。<br>この画面は閉じてかまいません。</p>'
            : '<div class="i" style="background:#8E8E93">–</div><b>このリンクは使えません</b><p class="s">期限(3日間)が過ぎたか、すでに確認が終わっています。<br>管理App の設定から、もう一度確認のメールを送ってください。</p>';
        $html = '<!doctype html><html lang="ja"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow"><title>Card-San</title>'
            . '<style>body{margin:0;background:#F5F5F7;color:#1D1D1F;font-family:-apple-system,BlinkMacSystemFont,"Hiragino Sans","Noto Sans JP",sans-serif;line-height:1.6}'
            . '.c{max-width:440px;margin:40px auto;background:#fff;border-radius:16px;padding:28px 20px;text-align:center}.i{width:44px;height:44px;border-radius:50%;color:#fff;font-size:22px;font-weight:700;display:grid;place-items:center;margin:0 auto 12px}'
            . 'p{word-break:break-all}.s{font-size:14px;color:#6E6E73}</style></head><body><div class="c">' . $body . '</div></body></html>';
        return response($html)->header('Content-Type', 'text/html; charset=utf-8');
    }
}
