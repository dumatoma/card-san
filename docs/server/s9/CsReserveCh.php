<?php

namespace App\Libs;

use Illuminate\Support\Facades\DB;

/**
 * [カスタムエイド 2026-11-03] 段階8-1:予約の受け取り方法(メール・SMS。LINE は段階9)
 * [カスタムエイド 2026-11-04] 段階9:LINE を足した。cs_opt_line(1 = LINE オプション契約中)/ reserve_ch_line(1 = LINE で受け付ける)
 *   LINE は、鍵がそろっていて今月の上限に達していないときだけ出す(CsLine::ready)。オプション(SMS か LINE)がないときは、メールは必ずオン
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
            return DB::table('web_shop')->where('id', (int) $sid)->first(['id', 'cs_opt_sms', 'cs_opt_line', 'reserve_ch_mail', 'reserve_ch_sms', 'reserve_ch_line', 'vip_sms_month_count', 'vip_sms_count']);
        } catch (\Throwable $e) { return null; }
    }

    // 今、受け付けている方法(決まりを当てはめたあと)。line_on = お店のスイッチ、line = 予約WEB に出すか
    public static function get($sid)
    {
        $r = self::row($sid);
        $optS = $r && (int) $r->cs_opt_sms === 1;
        $optL = $r && (int) $r->cs_opt_line === 1;
        $mail = ($optS || $optL) ? ((int) $r->reserve_ch_mail === 1) : true;
        $sms = $optS && (int) $r->reserve_ch_sms === 1;
        $lineOn = $optL && (int) $r->reserve_ch_line === 1;
        $line = $lineOn && CsLine::ready($sid);
        if (!$mail && !$sms && !$line) $mail = true;
        return ['mail' => $mail ? 1 : 0, 'sms' => $sms ? 1 : 0, 'line' => $line ? 1 : 0, 'line_on' => $lineOn ? 1 : 0, 'opt_sms' => $optS ? 1 : 0, 'opt_line' => $optL ? 1 : 0];
    }

    // お店が変える。$line が null なら今のまま。戻り値 ['ok' => bool, 'msg' => 文]
    public static function set($sid, $mail, $sms, $line = null)
    {
        $r = self::row($sid);
        if (!$r) return ['ok' => false, 'msg' => '店舗が見つかりません'];
        $optS = (int) $r->cs_opt_sms === 1;
        $optL = (int) $r->cs_opt_line === 1;
        if ($line === null) $line = $optL && (int) $r->reserve_ch_line === 1;
        if (!$optS && !$optL && !$mail) return ['ok' => false, 'msg' => 'SMS・LINE のオプションがないときは、メールはオフにできません'];
        if (!$optS && $sms) return ['ok' => false, 'msg' => 'SMS のオプションをご契約ではありません'];
        if (!$optL && $line) return ['ok' => false, 'msg' => 'LINE のオプションをご契約ではありません'];
        if ($line && !CsLine::configured($sid)) return ['ok' => false, 'msg' => '先に LINE 予約の設定(4つの鍵)をしてください'];
        if (!$mail && !$sms && !$line) return ['ok' => false, 'msg' => '受け付ける方法を1つ以上オンにしてください'];
        DB::table('web_shop')->where('id', (int) $sid)->update(['reserve_ch_mail' => $mail ? 1 : 0, 'reserve_ch_sms' => $sms ? 1 : 0, 'reserve_ch_line' => $line ? 1 : 0]);
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

    // [カスタムエイド 2026-11-04] 試験用:LINE オプションの印
    public static function setOptLine($sid, $on)
    {
        $u = ['cs_opt_line' => $on ? 1 : 0];
        if (!$on) $u['reserve_ch_line'] = 0;
        return DB::table('web_shop')->where('id', (int) $sid)->update($u);
    }
}
