<?php
namespace App\Libs;

use App\Models\ShopVipModel;
use App\Models\ShopMemberModel;

/**
 * [カスタムエイド 2026-11-03] 無料期間の終わり方と、契約が終わったあとの扱い(決定事項 2026-11-02)
 *  - 延長中に会員が31人を超えたら、31人目の登録時刻から24時間で終わる(登録から60日を超えない)
 *  - 一度31人を超えたら、会員を消して30人以下に戻しても無料には戻らない(web_shop.cs_trial_over_at)
 *  - お客様のアプリ:無料期間が終わってから3日間は使える。有料の契約は終わったら止まる
 *  - 管理App・管理WEB に渡す状態(cs_notice):帯・全面のお知らせ・データを消す日
 */
class CsTrial
{
    const GRACE = 3 * 86400;      // お客様のアプリの猶予(無料期間のあと)
    const KEEP = 30 * 86400;      // 契約が終わってからデータを残す日数
    const OVER_WAIT = 86400;      // 31人目から終わるまで

    public static function unlimited($sid)
    {
        return in_array((int)$sid, [1, 4, 7], true); // ShopController のテスト用店舗と同じ
    }

    /** 31人目の会員の登録時刻(31人以下なら 0) */
    public static function thirtyFirstAt($sid)
    {
        $t = ShopMemberModel::where('sid', $sid)->orderBy('create_time', 'asc')->orderBy('id', 'asc')->skip(30)->value('create_time');
        return (int)$t;
    }

    /** 31人を超えた印を付ける(まだなら)。付けた時刻を返す */
    public static function markOver($shop, $at = 0)
    {
        if ((int)$shop->cs_trial_over_at > 0) return (int)$shop->cs_trial_over_at;
        $at = $at ?: time();
        \App\Models\ShopModel::where('id', $shop->id)->where('cs_trial_over_at', 0)->update(['cs_trial_over_at' => $at]); // fillable に左右されないように
        $shop->cs_trial_over_at = $at;
        return $at;
    }

    /** 延長中の無料期間が終わる時刻(31人目から24時間・登録から60日のうち早いほう) */
    public static function extensionEnd($shop, $vip, $firstTrial)
    {
        $end = (int)$vip->end_time;
        $limit = (int)$firstTrial->start_time + 60 * 86400;
        $end = min($end, $limit);
        $over = (int)$shop->cs_trial_over_at;
        if (!$over && $shop->member()->count() > 30) {
            $t31 = self::thirtyFirstAt($shop->id) ?: time();
            $over = self::markOver($shop, max($t31, (int)$vip->start_time));
        }
        if ($over) $end = min($end, $over + self::OVER_WAIT);
        return $end;
    }

    /** 無料期間だけのお店(有料の契約なし)で会員が31人を超えていたら、印を付ける(会員を消しての延長を防ぐ) */
    public static function markOverIfTrialOnly($shop)
    {
        if (self::unlimited($shop->id) || (int)$shop->cs_trial_over_at > 0) return;
        if ($shop->member()->count() <= 30) return;
        $paid = ShopVipModel::where('sid', $shop->id)->where('is_try', 0)->where('status', 1)->exists();
        if (!$paid) self::markOver($shop, self::thirtyFirstAt($shop->id) ?: time());
    }

    /** お客様のアプリが見る契約:有効な契約。なければ、終わってから3日以内の無料期間 */
    public static function memberVip($shop)
    {
        if (!$shop) return null;
        $vip = ShopVipModel::where('sid', $shop->id)->where('start_time', '<=', time())->where('end_time', '>=', time())
            ->where('status', 1)->orderBy('create_time', 'desc')->first();
        if ($vip) return $vip;
        return ShopVipModel::where('sid', $shop->id)->where('is_try', 1)->where('status', 1)
            ->where('end_time', '>=', time() - self::GRACE)->where('end_time', '<', time())
            ->orderBy('end_time', 'desc')->first();
    }

    /** いちばん最近に終わった(または続いている)契約の記録 */
    public static function lastVip($sid)
    {
        return ShopVipModel::where('sid', $sid)->whereIn('status', [1, 2])->where('start_time', '<=', time())->where('end_time', '<=', time())
            ->where(function ($q) { $q->where('is_try', 1)->orWhere('confirm_time', '>', 0); })
            ->orderBy('end_time', 'desc')->first();
    }

    /**
     * 管理App・管理WEB に渡す状態
     *  state: '' (ふつう) / trial_ending (31人を超えて24時間の帯) / trial_ended (無料期間が終わった) / contract_ended (有料の契約が終わった)
     */
    public static function notice($shop, $shopVip)
    {
        $now = time();
        $n = ['state' => '', 'reason' => '', 'ends_at' => 0, 'over_at' => (int)$shop->cs_trial_over_at,
              'customer_app_until' => 0, 'delete_at' => 0, 'member_count' => $shop->member()->count()];
        if ($shopVip) {
            if ((int)$shopVip->is_try === 1 && (int)$shop->cs_trial_over_at > 0 && (int)$shopVip->end_time > $now) {
                $n['state'] = 'trial_ending';
                $n['reason'] = 'member';
                $n['ends_at'] = (int)$shopVip->end_time;
                $n['customer_app_until'] = (int)$shopVip->end_time + self::GRACE;
            }
            return $n;
        }
        $last = self::lastVip($shop->id);
        if (!$last) { $n['state'] = 'none'; return $n; }
        $end = min((int)$last->end_time, $now);
        $n['ends_at'] = $end;
        $n['delete_at'] = $end + self::KEEP;
        if ((int)$last->is_try === 1) {
            $n['state'] = 'trial_ended';
            $first = ShopVipModel::where('sid', $shop->id)->where('is_try', 1)->orderBy('id', 'asc')->first();
            $limit = $first ? (int)$first->start_time + 60 * 86400 : 0;
            $n['reason'] = (int)$shop->cs_trial_over_at > 0 ? 'member' : (($limit && $end >= $limit - 3600) ? '60days' : '30days');
            $n['customer_app_until'] = $end + self::GRACE;
        } else {
            $n['state'] = 'contract_ended';
            $n['reason'] = (int)$last->cancel_time > 0 ? 'cancel' : 'payment';
            $n['customer_app_until'] = $end;
        }
        return $n;
    }
}
