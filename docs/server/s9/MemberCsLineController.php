<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Libs\CsLine;
use App\Libs\CsReserveCh;
use Illuminate\Http\Request;

/**
 * [カスタムエイド 2026-11-04] 段階9:予約WEB の「LINE で受け取る」
 *  GET /api/member/cs-line/auth?sid=&back=   LINE ログインの住所(back は予約WEB の住所だけ。戻るときに ?cs_lt=合言葉 が付く)
 *  GET /api/member/cs-line/ticket?sid=&t=    つながったか・友だち追加したか(LINE の名前はお客様本人の画面にだけ出す)
 * [カスタムエイド 2026-11-04 v2] LIFF(LINE アプリの中で予約を確定する)
 *  POST /api/member/cs-line/draft   {sid, data}  予約WEB が入れた内容を預ける → {d, liff_url}(LIFF の住所を開くと LINE アプリが開く)
 *  GET  /api/member/cs-line/draft?d=             LIFF のページが預けた内容を読む(LIFF ID・お店の LINE の名前・友だち追加の住所も)
 *  POST /api/member/cs-line/liff    {d, id_token} LIFF の ID トークンを確かめる → 予約なら {ticket, friend}(予約は今までどおり POST /api/member/order に cs_ch=line・cs_lt=ticket)、試しなら {sent}
 */
class CsLineController extends Controller
{
    public function auth(Request $request)
    {
        $sid = (int) $request->sid;
        $back = (string) $request->back;
        if (!CsLine::backOk($back)) return responseToJson(400, '戻り先が正しくありません');
        if (!(int) (CsReserveCh::get($sid)['line'] ?? 0)) return responseToJson(400, 'このお店では、LINE での受け取りは選べません');
        $url = CsLine::authUrl($sid, $back, 'order');
        if ($url === '') return responseToJson(400, 'このお店では、LINE での受け取りは選べません');
        return responseToJson(200, '', ['url' => $url]);
    }

    public function ticket(Request $request)
    {
        $d = CsLine::ticket((string) $request->t, (int) $request->sid);
        if (!$d) return responseToJson(400, 'LINE とのつながりが切れました。もう一度「LINE で受け取る」を押してください');
        unset($d['uid']);
        return responseToJson(200, '', $d);
    }

    public function draft(Request $request)
    {
        $sid = (int) $request->sid;
        if (!(int) (CsReserveCh::get($sid)['line'] ?? 0)) return responseToJson(400, 'このお店では、LINE での受け取りは選べません');
        $data = $request->input('data');
        if (!is_array($data)) return responseToJson(400, '予約の内容がありません');
        if (strlen(json_encode($data)) > 20000) return responseToJson(400, '予約の内容が長すぎます');
        list($d, $url) = CsLine::draftPut($sid, 'order', $data);
        if ($url === '') return responseToJson(400, 'このお店では、LINE での受け取りは選べません');
        return responseToJson(200, '', ['d' => $d, 'liff_url' => $url]);
    }

    public function draftGet(Request $request)
    {
        $x = CsLine::draftGet((string) $request->d);
        if (!$x) return responseToJson(400, '時間が切れました。予約WEB から、もう一度やり直してください');
        return responseToJson(200, '', $x);
    }

    public function liff(Request $request)
    {
        $r = CsLine::liff((string) $request->d, (string) $request->id_token);
        if (!$r['ok']) return responseToJson(400, $r['err'] === 'expired' ? '時間が切れました。予約WEB から、もう一度やり直してください' : 'LINE で本人の確認ができませんでした。もう一度開き直してください');
        return responseToJson(200, '', $r);
    }

    // [カスタムエイド 2026-11-04 追記6] POST /api/member/cs-line/done {d, order}:LINE の中で予約が終わった / GET /api/member/cs-line/done?d=:予約WEB の元の画面が聞く
    public function done(Request $request)
    {
        $o = $request->input('order');
        if (!is_array($o) || !CsLine::draftDone((string) $request->d, $o)) return responseToJson(400, '');
        return responseToJson(200, '');
    }

    public function doneGet(Request $request)
    {
        $x = CsLine::draftStatus((string) $request->d);
        return responseToJson(200, '', $x ? ['done' => 1] + $x : ['done' => 0]);
    }
}
