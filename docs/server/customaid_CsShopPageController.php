<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

/**
 * [カスタムエイド 2026-10-26] 店舗QR統一:お店に置く QR の行き先(https://card-san.jp/s/{店舗ID})と、
 *   ログインなしで見られるお店の情報(店名・ロゴ・カード)。
 *   ・show():店舗ページ(ブラウザで表示)。アプリが入っていない人だけが見る(入っている人は Universal Links でアプリが直接開く)
 *       iPhone:「App Store でカードを受け取る」を押した瞬間に店舗IDをコピーしてから App Store へ(自動では移動しない)
 *       Android:3秒後に Google Play へ(店舗IDを referrer で渡す)
 *       パソコン:両方のボタン
 *   ・info():アプリの登録画面にカード画像を出すための情報(JSON)。秘密の値・連絡先は返さない
 *   card-san.jp の /s/ は nginx で api.card-san.jp に回している(extension/card-san.jp/customaid_s.conf)
 */
class CsShopPageController extends Controller
{
    const APP_STORE = 'https://apps.apple.com/jp/app/id1663708425';
    const PLAY = 'https://play.google.com/store/apps/details?id=uni.UNI1ED8C1A';
    // [カスタムエイド 2026-10-26] 上に出すのはお店のロゴではなく Card-San アプリのアイコン(坂本さんの判断:このアプリを入れることを分かってもらう。お店はカードの画像で分かる)
    //   画像のファイルを別に置かなくて済むよう、180px のアイコンをページに埋め込む
    const APP_ICON = 'data:image/png;base64,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';

    // 店名・ロゴ・ふだん使うカード
    private function data($id)
    {
        $shop = DB::table('web_shop')->where('id', (int) $id)->first(['id', 'shop_name', 'avatar', 'carousel']);
        if (!$shop) return null;
        $card = DB::table('web_shop_card')->where('sid', $shop->id)->orderBy('default', 'desc')->orderBy('id')
            ->first(['id', 'type', 'name', 'shop_name', 'image', 'background_color', 'frame_image', 'frame_fit', 'count', 'seal_color', 'point']);
        return [
            'id' => (int) $shop->id,
            'shop_name' => (string) $shop->shop_name,
            'logo' => (string) ($shop->avatar ?: ''),
            'card' => $card ? [
                'id' => (int) $card->id,
                'type' => (int) $card->type,
                'name' => (string) $card->name,
                'image' => (string) ($card->image ?: ''),
                'background_color' => (string) ($card->background_color ?: ''),
                'frame_image' => (string) ($card->frame_image ?: ''),
                'frame_fit' => (string) ($card->frame_fit ?: ''),
                'count' => (int) $card->count,
                'seal_color' => (string) ($card->seal_color ?: ''),
                'point' => (int) $card->point,
            ] : null,
        ];
    }

    public function info($id)
    {
        $d = $this->data($id);
        if (!$d) return response()->json(['code' => 404, 'message' => 'お店が見つかりません', 'data' => null]);
        return response()->json(['code' => 200, 'message' => '', 'data' => $d]);
    }

    public function show($id)
    {
        $d = $this->data($id);
        $ua = (string) request()->header('User-Agent');
        $os = preg_match('/iPhone|iPad|iPod/i', $ua) ? 'ios' : (preg_match('/Android/i', $ua) ? 'android' : 'pc');
        $inApp = (bool) preg_match('/Line\/|Instagram|FBAN|FBAV|MicroMessenger/i', $ua);
        $html = $d ? $this->page($d, $os, $inApp) : $this->notFound();
        return response($html, $d ? 200 : 404)->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Cache-Control', 'no-store')->header('X-Robots-Tag', 'noindex');
    }

    private function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }

    private function head($title)
    {
        return '<!doctype html><html lang="ja"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">'
            . '<meta name="robots" content="noindex"><meta name="apple-itunes-app" content="app-id=1663708425">'
            . '<title>' . $this->e($title) . '</title><style>'
            . '*{box-sizing:border-box;margin:0;padding:0}body{font-family:-apple-system,BlinkMacSystemFont,"Hiragino Sans","Noto Sans JP",sans-serif;background:#F5F5F7;color:#1D1D1F;-webkit-text-size-adjust:100%}'
            . '.w{max-width:440px;margin:0 auto;padding:28px 20px calc(32px + env(safe-area-inset-bottom));text-align:center}'
            . '.logo{width:72px;height:72px;border-radius:17px;margin:4px auto 6px;box-shadow:0 4px 12px rgba(0,0,0,.12);background:#1D1D1F;color:#fff;font-weight:800;font-size:22px;display:flex;align-items:center;justify-content:center;overflow:hidden}.logo img{width:100%;height:100%;object-fit:cover}'
            . '.an{font-size:13px;font-weight:700;color:#515154;margin-bottom:14px}.sn{font-size:21px;font-weight:800;line-height:1.35}.sd{font-size:14px;color:#515154;line-height:1.6;margin:6px 0 16px}'
            . '.card{border-radius:16px;overflow:hidden;margin:0 auto 18px;box-shadow:0 6px 20px rgba(0,0,0,.10);background:#fff}.card img{display:block;width:100%}'
            . '.card .cn{font-size:13px;font-weight:700;padding:10px 14px;text-align:left;background:#fff}'
            . '.steps{background:#fff;border-radius:16px;padding:14px 16px;text-align:left;margin-bottom:18px}.st{font-size:14px;font-weight:700;margin-bottom:6px}'
            . '.sp{display:flex;align-items:center;gap:10px;font-size:14px;line-height:1.5;margin-top:8px}.sp i{flex:0 0 22px;height:22px;border-radius:11px;background:#1D1D1F;color:#fff;font-style:normal;font-size:12px;display:flex;align-items:center;justify-content:center}'
            . '.btn{display:flex;align-items:center;justify-content:center;width:100%;height:54px;border-radius:27px;font-size:16px;font-weight:700;text-decoration:none;border:0;cursor:pointer;margin-top:10px;-webkit-tap-highlight-color:transparent}'
            . '.btn:active{opacity:.7}.blk{background:#1D1D1F;color:#fff}.grn{background:#A8F20A;color:#1D1D1F}'
            . '.tiny{font-size:12px;color:#86868B;line-height:1.6;margin-top:8px}.lnk{display:inline-block;font-size:14px;color:#1A73E8;margin-top:18px;text-decoration:none}'
            . '.note{background:#FFF8E1;color:#5F4B00;border-radius:12px;padding:10px 12px;font-size:13px;line-height:1.6;text-align:left;margin-bottom:14px}'
            . '.bar{height:4px;background:#E5E5EA;border-radius:2px;margin:10px 0 2px;overflow:hidden}.bar span{display:block;height:100%;width:0;background:#1D1D1F;transition:width 3s linear}'
            . '.ok{font-size:13px;color:#137333;margin-top:8px;min-height:1em}'
            . '</style></head><body><div class="w">';
    }

    private function page($d, $os, $inApp)
    {
        $id = $d['id'];
        $name = $d['shop_name'] ?: 'お店';
        $h = $this->head($name . '|会員カード');
        if ($inApp) {
            $h .= '<div class="note">LINE・Instagram などのアプリの中で開いています。うまく進めないときは、右上のメニューから「ブラウザで開く」を選んでください。</div>';
        }
        $h .= '<div class="logo app"><img src="' . self::APP_ICON . '" alt="Card-San"></div><div class="an">Card-San</div>';
        $h .= '<div class="sn">' . $this->e($name) . ' の会員カード</div>';
        $h .= '<div class="sd">Card-San アプリ(無料)で受け取れます</div>';
        if ($d['card']) {
            $c = $d['card'];
            $bg = $c['background_color'] ? ' style="background:' . $this->e($c['background_color']) . '"' : '';
            $h .= '<div class="card"' . $bg . '>' . ($c['image'] ? '<img src="' . $this->e($c['image']) . '" alt="">' : '<div style="height:120px"></div>')
                . '<div class="cn">' . $this->e($c['name'] ?: ($name . ' メンバーズカード')) . '</div></div>';
        }
        $step2 = $os == 'android' ? 'アプリを開いてアカウントを作る' : 'アプリを開いて「ペースト」を押す';
        $step3 = $os == 'android' ? 'お店のカードが自動で届きます' : 'アカウントを作るとカードが届きます';
        $h .= '<div class="steps"><div class="st">カードを受け取るまで</div>'
            . '<div class="sp"><i>1</i>下のボタンから Card-San を入れる(無料)</div>'
            . '<div class="sp"><i>2</i>' . $step2 . '</div><div class="sp"><i>3</i>' . $step3 . '</div></div>';

        $play = self::PLAY . '&referrer=' . rawurlencode('shop_id=' . $id);
        if ($os == 'ios' || $os == 'pc') {
            $h .= '<button class="btn blk" id="ios" type="button"> App Store でカードを受け取る</button>'
                . '<div class="tiny">押すと、お店の情報をコピーしてから App Store が開きます</div><div class="ok" id="ok"></div>';
        }
        if ($os == 'android') {
            $h .= '<div class="tiny"><b id="sec">3</b>秒後に Google Play へ移動します</div><div class="bar"><span id="bar"></span></div>'
                . '<a class="btn grn" href="' . $this->e($play) . '">今すぐ Google Play へ</a>';
        }
        if ($os == 'pc') {
            $h .= '<a class="btn grn" href="' . $this->e($play) . '">Google Play(Android)</a>';
        }
        $h .= '<a class="lnk" href="cardsan://shop/' . $id . '">アプリをお持ちの方はこちら</a>';
        $h .= '</div><script>(function(){'
            . 'var id=' . $id . ',as=' . json_encode(self::APP_STORE) . ',pl=' . json_encode($play) . ';'
            . 'var b=document.getElementById("ios");'
            . 'function go(){location.href=as}'
            // コピーする中身:アプリが見分けられる形(cardsan-shop:店舗ID:コピーした時刻)。24時間を過ぎたらアプリは使わない
            . 'function fallback(t){try{var a=document.createElement("textarea");a.value=t;a.setAttribute("readonly","");a.style.position="fixed";a.style.opacity="0";document.body.appendChild(a);a.select();a.setSelectionRange(0,t.length);document.execCommand("copy");document.body.removeChild(a)}catch(e){}}'
            . 'if(b)b.addEventListener("click",function(){var t="cardsan-shop:"+id+":"+Math.floor(Date.now()/1000);'
            . 'var ok=document.getElementById("ok");if(ok)ok.textContent="お店の情報をコピーしました。App Store を開きます…";'
            . 'try{if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(t).then(function(){setTimeout(go,300)},function(){fallback(t);setTimeout(go,300)});return}}catch(e){}'
            . 'fallback(t);setTimeout(go,300)});'
            . 'var s=document.getElementById("sec"),bar=document.getElementById("bar");'
            . 'if(s){var n=3;setTimeout(function(){if(bar)bar.style.width="100%"},50);var tm=setInterval(function(){n--;if(n<=0){clearInterval(tm);location.href=pl}else{s.textContent=n}},1000)}'
            . '})();</script></body></html>';
        return $h;
    }

    private function notFound()
    {
        return $this->head('お店が見つかりません')
            . '<div class="sn" style="margin-top:40px">お店が見つかりません</div>'
            . '<div class="sd">QRコードが古いか、お店の登録が終わっている可能性があります。お店の方にご確認ください。</div>'
            . '<a class="btn blk" href="' . self::APP_STORE . '">Card-San アプリ(App Store)</a>'
            . '<a class="btn grn" href="' . self::PLAY . '">Card-San アプリ(Google Play)</a>'
            . '</div></body></html>';
    }
}
