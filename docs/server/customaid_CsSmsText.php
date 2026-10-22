<?php

namespace App\Libs;

/**
 * [カスタムエイド 2026-10-21] 予約の SMS(ウェブ予約などアプリを使っていないお客様向け)の文を、
 * 管理App・管理WEBの「自動メッセージ」で店舗が設定した文から作る。以前は SMS だけ固定の文だった。
 * 並び:見出し → お名前+敬称 → 本文(上) → 予約の内容 → 本文(下) → 店舗名・電話・住所
 * 店舗が設定した改行はそのまま使う。空の欄は出さない。まだ設定していない店舗は今までと同じ初期の文。
 */
class CsSmsText
{
    const FIELD = [
        'confirm' => 'order_confirm_message',
        'notice'  => 'order_notice_message',
        'cancel'  => 'order_cancel_message',
        'update'  => 'order_update_message',
        'thanks'  => 'thanks_message',
    ];

    const DEF = [
        'confirm' => ['【予約受付のお知らせ】', '様', "ご予約ありがとうございます。\n以下の通りご予約承りました。", 'キャンセルや来店時刻に遅れる場合は、必ずご連絡頂きますようお願いいたします。'],
        'notice'  => ['【予約確認のお知らせ】', '様', '明日ご来店日となりますので、お知らせいたします。', 'スタッフ一同、心よりご来店お待ちしております。'],
        'cancel'  => ['【予約キャンセルのお知らせ】', '様', '以下内容のご予約のキャンセルを受理いたしました。', '内容のご確認をお願いいたします。'],
        'update'  => ['【予約内容変更のお知らせ】', '様', 'ご予約の内容が変更になりましたのでお知らせします。', '変更内容のご確認お願いします。', 'キャンセルや来店時刻に遅れる場合は、直接店舗にご連絡頂きますようお願いいたします。'],
        'thanks'  => ['様', 'この度は当店をご利用頂き、誠にありがとうございました。', '今後も当店をよろしくお願い致します。'],
    ];

    // SMS 用(お名前は予約に入力された名前)
    public static function build($type, $order)
    {
        return self::text($type, $order, trim((string) ($order->name ?? '')), true);
    }

    // アプリ内メッセージ用(SMS と同じ文章を、アプリのメッセージの形 <p>・<br> にする。電話番号は押せるように)
    // 口コミボタンはアプリでは別のメッセージで送られるので、ここではリンクを付けない
    public static function html($type, $order, $name)
    {
        $text = self::text($type, $order, trim((string) $name), false);
        $phone = trim((string) ($order->shop->phone ?? ''));
        $out = '';
        foreach (explode("\n", $text) as $line) {
            if ($line === '') { $out .= '<br>'; continue; }
            $h = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
            if ($phone !== '' && $line === '電話：' . $phone) {
                $p = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
                $h = '電話：<a href="tel:' . $p . '">' . $p . '</a>';
            }
            $out .= '<p>' . $h . '</p>';
        }
        return $out;
    }

    private static function text($type, $order, $name, $sms)
    {
        $shop = $order->shop ?? null;
        $field = self::FIELD[$type];
        $saved = $shop ? $shop->$field : null;
        $use = is_array($saved) && count($saved) > 0;

        $m = function ($i) use ($use, $saved, $type) {
            if ($use) {
                $v = $saved[$i] ?? '';
                return is_string($v) ? trim(str_replace(["\r\n", "\r"], "\n", $v)) : '';
            }
            return self::DEF[$type][$i] ?? '';
        };

        $blocks = [];

        if ($type == 'thanks') {
            $blocks[] = trim($name . ' ' . $m(0));
            $blocks[] = $m(1);
            $blocks[] = $m(2);
            // 「Googleレビューリンクボタンの設置」がオンなら、SMS ではボタンの代わりにリンクを付ける
            $url = trim((string) ($shop->comment_button_url ?? ''));
            if ($sms && $use && (string) end($saved) === '1' && $url !== '') {
                $blocks[] = "Googleの口コミはこちら\n" . $url;
            }
        } else {
            $blocks[] = $m(0);
            $blocks[] = trim($name . ' ' . $m(1));
            $blocks[] = $m(2);
            if ($type == 'update') {
                $blocks[] = $m(3);
            }
            $detail = [
                '予約番号：' . $order->order_no,
                '日時：' . date('m月d日', $order->start_time) . '(' . webConfig()->type('week')[date('w', $order->start_time)] . ') ' . date('H:i', $order->start_time) . ' ~ ' . date('H:i', $order->end_time),
                'メニュー：' . ($order->menu->title ?? ''),
                '価格：' . $order->amount, // メニューの価格はお店が自由に書く欄(¥9,000〜・9,000円 など)なので、そのまま
                '担当者：' . ($order->appointAdmin->name ?? '指名なし'),
                ($order->appoint_amount > 0 ? ('指名料：￥' . number_format((float) $order->appoint_amount)) : ''),
            ];
            $blocks[] = implode("\n", array_filter($detail, function ($v) { return $v !== null && $v !== ''; }));
            $blocks[] = $m($type == 'update' ? 4 : 3);
        }

        $text = implode("\n\n", array_filter($blocks, function ($v) { return $v !== null && $v !== ''; }));

        $info = implode("\n", array_filter([
            (($shop->shop_name ?? '') !== '' ? '店舗名：' . $shop->shop_name : ''),
            (($shop->phone ?? '') !== '' ? '電話：' . $shop->phone : ''),
            (($shop->address1 ?? '') . ($shop->address2 ?? '')),
        ], function ($v) { return $v !== null && $v !== ''; }));

        return $info !== '' ? $text . "\n\n" . $info : $text;
    }
}
