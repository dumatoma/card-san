<?php
// [カスタムエイド 2026-10-27] プッシュ通知に入れるアイコンのバッジの数。アプリを開いたときにアプリが出す数と同じ計算
//   Card-San App:お店からのメッセージの未読 + 見ていないクーポン(Member/MemberController の unread_num と同じ)
//   管理App:お客様のメッセージの未読 + 運営からのお知らせの未読 + スタッフルーム・スタッフ間チャットの未読 + 口コミの未読(管理App の index.vue と同じ)
namespace App\Libs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class CsBadge
{
    public static function count($type, $cid)
    {
        try {
            if ($type === 'user') return self::member($cid);
            if ($type === 'shop') return self::shop($cid);
        } catch (\Throwable $e) {}
        return null; // 計算できなければバッジは入れない(今までどおり)
    }
    public static function member($cid)
    {
        // 同じ iPhone で別のアカウントを使ったことがあると、同じ端末番号の人が何人もいる。今ログインしている人(Apple の番号を持つ人)を選ぶ
        $u = DB::table('web_user')->where('getui_clientid', $cid)->whereNotNull('cs_apns_token')->where('cs_apns_token', '<>', '')->orderBy('id', 'desc')->first(['id']);
        if (!$u) return null;
        $n = 0; // [カスタムエイド 2026-10-27] カードを持っているお店の未読だけ数える(カードを消したお店の未読が残って数えられていた)
        foreach (\App\Models\ShopMemberModel::has('card')->has('shop')->where('uid', $u->id)->get() as $m) {
            $csG = \App\Models\GroupModel::where('uid', $u->id)->where('sid', $m->sid)->first(); $n += (int) ($csG->unread['member'] ?? 0);
            $n += \App\Models\MemberCouponModel::where('sid', $m->sid)->where('mid', $m->id)->where('create_time', '>', $m->read_coupon_time)->count();
        }
        return $n;
    }
    // [カスタムエイド 2026-10-27] 予約通知:この人にとって未読か。お店全体の印が未読なら未読。担当者(指名・担当)なら、本人が開くまで未読
    public static function noticeUnreadFor($notice, $aid)
    {
        return !(int) $notice->is_read; // [カスタムエイド 2026-10-27 2回目] お店全体の印だけで決める(担当者だけ未読を残すのはやめた)
        if (!(int) $notice->is_read) return true;
        $o = $notice->order; if (!$o) return false;
        if (!in_array((int) $aid, array_filter([(int) $o->appoint_said, (int) $o->day_said]))) return false;
        return strpos((string) ($notice->staff_read ?? ''), ',' . (int) $aid . ',') === false;
    }
    public static function orderUnread($sid, $aid)
    {
        $n = 0;
        foreach (\App\Models\OrderNoticeModel::with('order')->has('order')->where('sid', $sid)->get() as $v) if (self::noticeUnreadFor($v, $aid)) $n++;
        return $n;
    }
    public static function shop($cid)
    {
        $a = \App\Models\ShopAdminModel::where('getui_clientid', $cid)->whereNotNull('cs_apns_token')->where('cs_apns_token', '<>', '')->orderBy('id', 'desc')->first(); // 上と同じ理由
        if (!$a) return null;
        $sid = (int) $a->sid; $shop = $a->shop;
        $n = (int) \App\Models\GroupModel::where('sid', $sid)->where('unread->admin->' . $a->id, '>', 0)->sum('unread->admin->' . $a->id);
        $n += self::orderUnread($sid, $a->id); // [カスタムエイド 2026-10-27] 予約通知の未読(運営からのお知らせは数に入れない。アプリのバッジと同じ)
        $ctl = new \App\Http\Controllers\Api\Shop\StaffRoomController();
        $rm = new \ReflectionMethod($ctl, 'groupTable'); $rm->setAccessible(true); $gt = $rm->invoke($ctl, $sid);
        if (Schema::hasTable($gt)) $n += DB::table($gt)->where('id', '>', (int) $a->staff_room_last_read)->where('admin_id', '!=', $a->id)->count();
        foreach (DB::select("SHOW TABLES LIKE 'web_staff_dm_{$sid}_%'") as $row) {
            $n += DB::table(array_values((array) $row)[0])->where('to_id', $a->id)->where('is_read', 0)->count();
        }
        $priv = is_array($a->privileges) ? $a->privileges : (json_decode((string) $a->privileges, true) ?: []);
        if ($shop && $shop->gbp_review_on && ($a->admin_type == 1 || in_array(15, $priv))) $n += DB::table('web_gbp_review')->where('sid', $shop->id)->where('is_read', 0)->count();
        return $n;
    }
}
