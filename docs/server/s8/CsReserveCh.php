<?php

namespace App\Libs;

use Illuminate\Support\Facades\DB;

/**
 * [カスタムエイド 2026-11-03] 段階8-1:予約の受け取り方法(メール・SMS。LINE は段階9)
 *  web_shop の欄:cs_opt_sms(1 = SMS オプション契約中)/ reserve_ch_mail(1 = メールで受け付ける)/ reserve_ch_sms(1 = SMS で受け付ける)
 *  決まり(サーバーでも守る):
 *   ・SMS のオプションがないときは、メールは必ずオン・SMS は必ずオフ(プランを下げてオプションが外れたら、自然にメールだけに戻る)
 *   ・受け付ける方法を全部オフにはできない
 *  申し込み・お支払い(段階8-3)ができるまでは、cs_opt_sms はサーバーで手で付ける(試験用のお店だけ)
 */
class CsReserveCh
{
    const SMS_MONTH = 100; // 1か月の予約SMS の数(プランの注意書きと同じ)

    private static function row($sid)
    {
        try {
            return DB::table('web_shop')->where('id', (int) $sid)->first(['id', 'cs_opt_sms', 'reserve_ch_mail', 'reserve_ch_sms', 'vip_sms_month_count', 'vip_sms_count']);
        } catch (\Throwable $e) { return null; }
    }

    // 今、受け付けている方法(決まりを当てはめたあと)
    public static function get($sid)
    {
        $r = self::row($sid);
        $opt = $r && (int) $r->cs_opt_sms === 1;
        $mail = $opt ? ((int) $r->reserve_ch_mail === 1) : true;
        $sms = $opt && (int) $r->reserve_ch_sms === 1;
        if (!$mail && !$sms) $mail = true;
        return ['mail' => $mail ? 1 : 0, 'sms' => $sms ? 1 : 0, 'line' => 0, 'opt_sms' => $opt ? 1 : 0, 'opt_line' => 0];
    }

    // お店が変える。戻り値 ['ok' => bool, 'msg' => 文]
    public static function set($sid, $mail, $sms)
    {
        $r = self::row($sid);
        if (!$r) return ['ok' => false, 'msg' => '店舗が見つかりません'];
        $opt = (int) $r->cs_opt_sms === 1;
        if (!$opt && !$mail) return ['ok' => false, 'msg' => 'SMS・LINE のオプションがないときは、メールはオフにできません'];
        if (!$opt && $sms) return ['ok' => false, 'msg' => 'SMS のオプションをご契約ではありません'];
        if (!$mail && !$sms) return ['ok' => false, 'msg' => '受け付ける方法を1つ以上オンにしてください'];
        DB::table('web_shop')->where('id', (int) $sid)->update(['reserve_ch_mail' => $mail ? 1 : 0, 'reserve_ch_sms' => $sms ? 1 : 0]);
        return ['ok' => true, 'msg' => '受け付ける方法を変えました'];
    }

    // 今月の予約SMS の残り(数が戻る日 = ご契約の起算日)
    public static function smsInfo($sid)
    {
        $r = self::row($sid);
        $day = 0;
        try { $day = (int) \App\Models\ShopVipModel::where('sid', (int) $sid)->orderBy('id', 'desc')->value('settlement_day'); } catch (\Throwable $e) { $day = 0; }
        return [
            'month' => self::SMS_MONTH,
            'left' => $r ? max(0, (int) $r->vip_sms_month_count) : 0,
            'extra' => $r ? (int) $r->vip_sms_count : 0,
            'reset_day' => $day,
        ];
    }

    // 試験用:オプションの印を手で付ける・外す
    public static function setOpt($sid, $on)
    {
        return DB::table('web_shop')->where('id', (int) $sid)->update(['cs_opt_sms' => $on ? 1 : 0]);
    }
}
