<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Libs\CsReserveCh;
use Illuminate\Http\Request;

/**
 * [カスタムエイド 2026-11-03] 段階8-1:管理App「予約Webサイト」の受け付ける方法(メール・SMS)
 *  GET  /api/shop/cs/reserve-ch   今の状態と今月の予約SMS の残り(だれでも見られる)
 *  POST /api/shop/cs/reserve-ch   変える(mail = 1/0, sms = 1/0)。管理者と「予約設定」(8)の権限がある副管理者だけ
 */
class CsReserveChController extends Controller
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
        $ch = CsReserveCh::get($sid);
        $ch['sms_info'] = CsReserveCh::smsInfo($sid);
        $ch['can_edit'] = $this->canEdit(auth('shop')->user()) ? 1 : 0;
        return responseToJson(200, $msg, $ch);
    }

    public function show()
    {
        return $this->out(auth('shop')->user()->sid);
    }

    public function save(Request $request)
    {
        $u = auth('shop')->user();
        if (!$this->canEdit($u)) return responseToJson(403, '予約設定の権限がありません');
        $r = CsReserveCh::set($u->sid, (int) $request->input('mail', 1) === 1, (int) $request->input('sms', 0) === 1);
        if (!$r['ok']) return responseToJson(400, $r['msg']);
        return $this->out($u->sid, $r['msg']);
    }
}
