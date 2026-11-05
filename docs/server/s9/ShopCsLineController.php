<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Libs\CsLine;
use Illuminate\Http\Request;

/**
 * [カスタムエイド 2026-11-04] 段階9:管理App「予約Webサイト」の LINE・「LINE予約の設定」
 *  GET  /api/shop/cs/line           今の状態(鍵ははじめとおわりの3文字だけ・今月の通数)。だれでも見られる
 *  POST /api/shop/cs/line           「保存して確かめる」(v2:msg_id・msg_secret・login_id・login_secret・liff_id)
 *  POST /api/shop/cs/line/remove    「設定を解除する」
 *  GET  /api/shop/cs/line/test-url  「試しに自分の LINE に送る」の住所(v2:LIFF。LINE アプリが開く)
 *  変えられるのは、管理者と「予約設定」(8)の権限がある副管理者だけ。
 *  鍵が記録(my_api のログ)に残らないよう、この入口は api.request を通さない(routes/api.php のいちばん下)
 */
class CsLineController extends Controller
{
    private function canEdit($u)
    {
        if ((int) ($u->admin_type ?? 1) !== 2) return true;
        $p = $u->privileges ?? [];
        if (!is_array($p)) $p = json_decode((string) $p, true) ?: [];
        return in_array(8, $p) || in_array('8', $p, true);
    }

    private function out($sid, $msg = '', $extra = [])
    {
        $d = CsLine::status($sid);
        $d['ch'] = \App\Libs\CsReserveCh::get($sid);
        $d['can_edit'] = $this->canEdit(auth('shop')->user()) ? 1 : 0;
        return responseToJson(200, $msg, array_merge($d, $extra));
    }

    public function show()
    {
        return $this->out(auth('shop')->user()->sid);
    }

    public function save(Request $request)
    {
        $u = auth('shop')->user();
        if (!$this->canEdit($u)) return responseToJson(403, '予約設定の権限がありません');
        if (!(int) (\App\Libs\CsReserveCh::get($u->sid)['opt_line'] ?? 0)) return responseToJson(400, 'LINE のオプションをご契約ではありません');
        $r = CsLine::save($u->sid, $request->only(['msg_id', 'msg_secret', 'login_id', 'login_secret', 'liff_id'])); // [カスタムエイド 2026-11-04 v2] 鍵は5つ
        if (!$r['ok']) return responseToJson(400, $r['msg'], ['errors' => $r['errors']]);
        return $this->out($u->sid, $r['msg']);
    }

    public function remove()
    {
        $u = auth('shop')->user();
        if (!$this->canEdit($u)) return responseToJson(403, '予約設定の権限がありません');
        CsLine::remove($u->sid);
        return $this->out($u->sid, 'LINE 予約の設定を解除しました');
    }

    public function testUrl()
    {
        $u = auth('shop')->user();
        list($d, $url) = CsLine::draftPut($u->sid, 'test', []); // [カスタムエイド 2026-11-04 v2] LIFF で試す
        if ($url === '') return responseToJson(400, '先に LINE 予約の設定をしてください');
        return responseToJson(200, '', ['url' => $url]);
    }
}
