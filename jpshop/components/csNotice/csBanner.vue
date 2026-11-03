<template>
    <!-- [カスタムエイド 2026-11-03] A1 帯:31人目の登録から24時間。閉じられない。見本「Card-San お知らせ見本」A1 -->
    <view class="cs-bn" v-if="show">
        <view class="cs-bn-t">
            <view class="cs-bn-b">会員が30人を超えました</view>
            <view class="cs-bn-s">無料でお使いいただけるのは {{ endText }} までです</view>
        </view>
        <view class="cs-bn-btn" @click="$emit('apply')">お申し込み</view>
    </view>
</template>
<script>
    export default {
        props: { notice: { type: Object, default: null } },
        computed: {
            show() {
                const n = this.notice
                return !!(n && n.state === 'trial_ending' && n.ends_at * 1000 > Date.now())
            },
            endText() {
                const d = new Date((this.notice ? this.notice.ends_at : 0) * 1000)
                return (d.getMonth() + 1) + '月' + d.getDate() + '日 ' + d.getHours() + ':' + ('0' + d.getMinutes()).slice(-2)
            }
        }
    }
</script>
<style scoped>
    .cs-bn { display: flex; flex-direction: row; align-items: center; background: #FFF3E0; border-bottom: 4rpx solid #F29900; color: #5F3B00; padding: 16rpx 24rpx; box-sizing: border-box; width: 100%; }
    .cs-bn-t { flex: 1; min-width: 0; font-size: 23rpx; line-height: 1.5; margin-right: 20rpx; }
    .cs-bn-b { font-size: 25rpx; font-weight: bold; }
    .cs-bn-s { font-size: 23rpx; }
    .cs-bn-btn { background: #E8710A; color: #FFFFFF; border-radius: 16rpx; padding: 12rpx 20rpx; font-weight: bold; font-size: 24rpx; white-space: nowrap; }
</style>
