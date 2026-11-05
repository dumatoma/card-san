<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Libs\CsShopMail;
use Illuminate\Http\Request;

/**
 * [カスタムエイド 2026-11-03] 段階7:管理App「予約Webサイト」の「メール」のブロック
 *  GET  /api/shop/cs/reserve-mail         今の状態(だれでも見られる。予約の画面の知らせにも使う)
 *  POST /api/shop/cs/reserve-mail         登録・変更して確認のメールを送る(email)
 *  POST /api/shop/cs/reserve-mail/resend  確認のメールを送り直す
 *  POST /api/shop/cs/reserve-mail/bcc     写しのオン・オフ(on = 1/0)
 *  変えられるのは、管理者と「予約設定」(8)の権限がある副管理者だけ
 */
class CsReserveMailController extends Controller
{
    private function canEdit($u)
    {
        if ((int) ($u->admin_type ?? 1) !== 2) return true;
        $p = $u->privileges ?? [];
        if (!is_array($p)) $p = json_decode((string) $p, true) ?: [];
        return in_array(8, $p) || in_array('8', $p, true);
    }

    private function out($sid, $msg = '')
    {
        $st = CsShopMail::status($sid);
        if (!$st) return responseToJson(400, '店舗が見つかりません');
        $st['can_edit'] = $this->canEdit(auth('shop')->user()) ? 1 : 0;
        return responseToJson(200, $msg, $st);
    }

    public function show()
    {
        return $this->out(auth('shop')->user()->sid);
    }

    public function save(Request $request)
    {
        $u = auth('shop')->user();
        if (!$this->canEdit($u)) return responseToJson(403, '予約設定の権限がありません');
        $r = CsShopMail::request($u->sid, (string) $request->input('email', ''));
        if (!$r['ok']) return responseToJson(400, $r['msg']);
        return $this->out($u->sid, $r['msg']);
    }

    public function resend()
    {
        $u = auth('shop')->user();
        if (!$this->canEdit($u)) return responseToJson(403, '予約設定の権限がありません');
        $r = CsShopMail::resend($u->sid);
        if (!$r['ok']) return responseToJson(400, $r['msg']);
        return $this->out($u->sid, $r['msg']);
    }

    public function bcc(Request $request)
    {
        $u = auth('shop')->user();
        if (!$this->canEdit($u)) return responseToJson(403, '予約設定の権限がありません');
        CsShopMail::setBcc($u->sid, (int) $request->input('on', 0) === 1);
        return $this->out($u->sid, (int) $request->input('on', 0) === 1 ? '写しを受け取るようにしました' : '写しを受け取らないようにしました');
    }
}
