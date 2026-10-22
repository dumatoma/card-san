<template>
    <!-- [カスタムエイド 2026-10-21] 確認のお知らせ(メッセージ配信まわりで共通)。
         白いカード・見出し・本文・ボタン(縦に2つ)。ボタンの色は ok-color で(green/blue/red) -->
    <view class="cd" v-if="show" @touchmove.stop.prevent>
        <view class="cd-mask" @click="maskClose && $emit('cancel')"></view>
        <view class="cd-box">
            <view class="cd-icon" v-if="icon" :class="icon">{{ icon == 'warn' ? '!' : 'i' }}</view>
            <view class="cd-title" :class="{ warn: icon == 'warn' }">{{ title }}</view>
            <scroll-view scroll-y class="cd-body" v-if="message || $slots.default">
                <view class="cd-msg" :class="{ long: message.length > 60 }" v-if="message">{{ message }}</view>
                <slot></slot>
            </scroll-view>
            <view class="cd-btns">
                <view class="cd-ok" :class="okColor" hover-class="cd-hover" @click="$emit('ok')">{{ okText }}</view>
                <view class="cd-cancel" v-if="cancelText" hover-class="cd-hover" @click="$emit('cancel')">{{ cancelText }}</view>
            </view>
        </view>
    </view>
</template>

<script>
    export default {
        name: 'csDialog',
        props: {
            show: { type: Boolean, default: false },
            title: { type: String, default: '' },
            message: { type: String, default: '' },
            okText: { type: String, default: 'OK' },
            cancelText: { type: String, default: 'キャンセル' },
            okColor: { type: String, default: 'green' }, // green / blue / red
            icon: { type: String, default: '' }, // '' / warn / info
            maskClose: { type: Boolean, default: false }
        }
    }
</script>

<style lang="scss" scoped>
    .cd { position: fixed; left: 0; top: 0; right: 0; bottom: 0; z-index: 10000; display: flex; align-items: center; justify-content: center; }
    .cd-mask { position: absolute; left: 0; top: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.4); }
    .cd-box { position: relative; width: 600rpx; max-height: 80vh; background: #FFFFFF; border-radius: 40rpx; padding: 56rpx 48rpx 48rpx; box-sizing: border-box; display: flex; flex-direction: column; }
    .cd-icon { width: 80rpx; height: 80rpx; border-radius: 50%; margin: 0 auto 24rpx; text-align: center; line-height: 80rpx; font-size: 44rpx; font-family: 'Hiragino Sans-W5', sans-serif; }
    .cd-icon.warn { background: #FCE8E6; color: #D93025; }
    .cd-icon.info { background: #E8F0FE; color: #1A73E8; }
    .cd-title { text-align: center; font-size: 34rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.4; }
    .cd-title.warn { color: #D93025; }
    .cd-body { margin-top: 24rpx; max-height: 50vh; }
    .cd-msg { font-size: 28rpx; color: #515154; line-height: 1.7; text-align: center; white-space: pre-wrap; }
    .cd-msg.long { text-align: left; } /* 長い文は左寄せで読みやすく */
    .cd-btns { margin-top: 48rpx; }
    .cd-ok, .cd-cancel { height: 96rpx; border-radius: 24rpx; display: flex; align-items: center; justify-content: center; font-size: 30rpx; }
    .cd-ok { color: #FFFFFF; font-family: 'Hiragino Sans-W5', sans-serif; }
    .cd-ok.green { background: #34C759; }
    .cd-ok.blue { background: #1A73E8; }
    .cd-ok.red { background: #D93025; }
    .cd-cancel { margin-top: 24rpx; background: #F2F2F7; color: #1D1D1F; }
    .cd-hover { opacity: 0.7; }
</style>
