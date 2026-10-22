<?php

namespace App\Libs;

use Illuminate\Support\Facades\Storage;

/**
 * [カスタムエイド 2026-10-22] お店のバーコード番号(レジ・POS で読み取る)のクーポン
 *  ・shop_coupon.barcode_type:0 = 自動で発行(今までどおり)/ 1 = JAN(13桁・8桁)/ 2 = CODE128
 *  ・お客様は Card-San App で「今すぐ使う」を押してから 10 分だけバーコードを見られる(押したときに「使った」と記録)
 */
class CsPosBarcode
{
    const SHOW_SECONDS = 600;

    // 入力された番号を確かめて、保存する形にそろえる。だめなときは理由の文を返す
    public static function normalize($type, $value)
    {
        $type = (int) $type;
        $v = trim((string) $value);
        if ($type == 1) {
            $v = preg_replace('/\s+/', '', $v);
            if (!preg_match('/^\d+$/', $v)) return 'JAN の番号は数字だけで入力してください';
            $n = strlen($v);
            if ($n == 12 || $n == 7) {
                $v .= self::checkDigit($v);
            } elseif ($n == 13 || $n == 8) {
                if ((int) substr($v, -1) !== self::checkDigit(substr($v, 0, -1))) return 'JAN の番号が正しくありません(最後の1桁が合いません)';
            } else {
                return 'JAN の番号は13桁か8桁で入力してください';
            }
            return ['type' => 1, 'value' => $v];
        }
        if ($type == 2) {
            if ($v === '' || strlen($v) > 48 || !preg_match('/^[\x20-\x7E]+$/', $v)) return 'CODE128 の番号は半角の英数字・記号48文字までで入力してください';
            return ['type' => 2, 'value' => $v];
        }
        return ['type' => 0, 'value' => ''];
    }

    // JAN の最後の1桁(右から 3・1・3・1… の重み)
    public static function checkDigit($digits)
    {
        $sum = 0;
        $len = strlen($digits);
        for ($i = 0; $i < $len; $i++) {
            $d = (int) $digits[$len - 1 - $i];
            $sum += ($i % 2 == 0) ? $d * 3 : $d;
        }
        return (10 - $sum % 10) % 10;
    }

    // お店のバーコードの画像(クーポンごとに1つ作って使い回す)
    public static function image($sc)
    {
        $bar = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $value = (string) $sc->barcode_value;
        if ((int) $sc->barcode_type == 1) {
            $t = strlen($value) == 8 ? $bar::TYPE_EAN_8 : $bar::TYPE_EAN_13;
        } else {
            $t = $bar::TYPE_CODE_128;
        }
        $file = 'pos_coupon/c' . $sc->id . '-' . substr(md5($sc->barcode_type . '|' . $value), 0, 12) . '.png';
        $path = '/storage/barcode/' . $file;
        if (!file_exists('.' . $path)) {
            Storage::put('barcode/' . $file, $bar->getBarcode($value, $t, 3, 120));
        }
        return requestUrl() . $path;
    }

    // Card-San App に返す、お店のバーコードの状態(使う前は番号も画像も返さない)
    public static function state($mc)
    {
        $sc = $mc->coupon;
        if (!$sc || (int) $sc->barcode_type === 0 || $sc->is_bar_code != 1 || (string) $sc->barcode_value === '') {
            return ['enabled' => false];
        }
        $now = time();
        $t = (int) $mc->self_use_time;
        $using = $t > 0 && $now < $t + self::SHOW_SECONDS;
        $inDate = $sc->is_indate == 0 || ($sc->indate_start_time <= $now && $sc->indate_end_time >= $now);
        $left = $mc->count == 0 ? -1 : max(0, $mc->count - $mc->use_count);
        $r = [
            'enabled' => true,
            'type' => (int) $sc->barcode_type == 1 ? 'JAN' : 'CODE128',
            'using' => $using,
            'now' => $now,
            'expire_at' => $using ? $t + self::SHOW_SECONDS : 0,
            'used_time' => $t ? date('Y-m-d H:i:s', $t) : '',
            'in_date' => $inDate,
            'left' => $left,
            'can_use' => !$using && $inDate && $left !== 0,
        ];
        if ($using) {
            $r['value'] = (string) $sc->barcode_value;
            $r['image'] = self::image($sc);
        }
        return $r;
    }
}
