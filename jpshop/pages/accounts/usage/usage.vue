<template>
    <!-- [カスタムエイド 2026-11-03] 段階8-2:ご利用状況(今月の予約SMS・メッセージ・クーポンの残り、オプション)。副管理者も見られる -->
    <view class="us">
        <u-navbar title="ご利用状況" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="us-body">
            <view class="us-loading" v-if="!loaded">読み込んでいます…</view>
            <block v-else>
                <view class="us-sec">今月のご利用状況{{ d.reset_day ? '(毎月' + d.reset_day + '日にリセット)' : '' }}</view>
                <view class="us-card" v-for="it in items" :key="it.key">
                    <view class="us-h">
                        <text class="us-t">{{ it.name }}</text>
                        <text class="us-n" :class="{ red: it.v.left <= 0 }">残り <text class="us-big">{{ fmt(it.v.left) }}</text> / {{ fmt(it.v.month) }}通</text>
                    </view>
                    <view class="us-bar"><view class="us-bar-in" :class="barClass(it.v)" :style="{ width: pct(it.v) + '%' }"></view></view>
                    <view class="us-note">追加でご購入の分:{{ fmt(Math.max(it.v.extra, 0)) }}通</view>
                    <view class="us-warn" v-if="it.v.extra < 0">今月分を超えています(超過 {{ fmt(-it.v.extra) }}通)</view>
                </view>
                <!-- [カスタムエイド 2026-11-04 段階9] LINE(お店の LINE 公式アカウントの今月の通数。お店がほかに送った分も入る) -->
                <view class="us-card" v-if="d.opt_line == 1 && line && line.set == 1">
                    <view class="us-h">
                        <text class="us-t">LINE</text>
                        <text class="us-n" :class="{ red: lineOver }" v-if="lq && lq.limit > 0">残り <text class="us-big">{{ fmt(lq.left) }}</text> / {{ fmt(lq.limit) }}通</text>
                        <text class="us-n" v-else-if="lq">上限なし(送った数 {{ fmt(lq.used) }}通)</text>
                        <text class="us-n" v-else>取れませんでした</text>
                    </view>
                    <view class="us-bar" v-if="lq && lq.limit > 0"><view class="us-bar-in" :class="barClass({ month: lq.limit, left: lq.left })" :style="{ width: pct({ month: lq.limit, left: lq.left }) + '%' }"></view></view>
                    <view class="us-note">お店の LINE 公式アカウント「{{ line.bot_name }}」の今月の通数です。お店が LINE でほかに送った分(一斉配信など)も入った数です。</view>
                    <view class="us-warn" v-if="lineOver">LINE の今月の上限に達しました。予約WEB で LINE を選べないようにしています。</view>
                </view>
                <view class="us-sec">オプション</view>
                <view class="us-card us-opt">
                    <view class="us-row"><text>SMS(電話番号に送信)</text><text class="us-chip" :class="d.opt_sms == 1 ? 'ok' : 'gray'">{{ d.opt_sms == 1 ? 'ご契約中' : '未契約' }}</text></view>
                    <view class="us-row"><text>LINE</text><text class="us-chip" :class="d.opt_line == 1 ? 'ok' : 'gray'">{{ d.opt_line == 1 ? 'ご契約中' : '未契約' }}</text></view><!-- [カスタムエイド 2026-11-04 段階9] -->
                </view>
                <view class="us-foot">ご契約のプラン・お支払いは「ご契約内容」で見られます。</view>
            </block>
        </view>
    </view>
</template>

<script>
    import { csUsage, csLine } from '@/api/index.js' // [カスタムエイド 2026-11-04 段階9]
    export default {
        data() {
            return { loaded: false, d: {}, line: null } // [カスタムエイド 2026-11-04 段階9] line:LINE の状態
        },
        computed: {
            lq() { return this.line && this.line.quota ? this.line.quota : null }, // [カスタムエイド 2026-11-04 段階9]
            lineOver() { return !!(this.lq && this.lq.over == 1) },
            items() {
                const z = { month: 0, left: 0, extra: 0 }
                return [
                    { key: 'sms', name: '予約SMS', v: this.d.sms || z },
                    { key: 'message', name: 'メッセージの配信', v: this.d.message || z },
                    { key: 'coupon', name: 'クーポンの配信', v: this.d.coupon || z }
                ]
            }
        },
        onShow() {
            csUsage().then((res) => {
                if (res.code == 200 && res.data) {
                    this.d = res.data
                    if (res.data.opt_line == 1) csLine().then((r) => { if (r.code == 200 && r.data) this.line = r.data }).catch(() => {}) // [カスタムエイド 2026-11-04 段階9]
                }
                else uni.showToast({ title: res.message || '読み込めませんでした', icon: 'none' })
                this.loaded = true
            }).catch(() => { this.loaded = true; uni.showToast({ title: '通信できませんでした', icon: 'none' }) })
        },
        methods: {
            fmt(n) { return String(Math.max(0, Number(n) || 0)).replace(/\B(?=(\d{3})+(?!\d))/g, ',') },
            pct(v) { const m = v.month || 1; return Math.max(0, Math.min(100, Math.round((v.left || 0) / m * 100))) },
            // 残り1割を切ったら黄色、なくなったら赤
            barClass(v) { const m = v.month || 1; return (v.left || 0) <= 0 ? 'red' : (v.left / m) <= 0.1 ? 'yellow' : '' }
        }
    }
</script>

<style>
    page { background: #F5F5F7; }
    .us-body { padding: 24rpx 24rpx calc(80rpx + env(safe-area-inset-bottom)); }
    .us-loading { text-align: center; color: #8A8A8E; font-size: 26rpx; padding: 80rpx 0; }
    .us-sec { font-size: 26rpx; font-weight: 700; color: #1D1D1F; margin: 24rpx 8rpx 16rpx; }
    .us-card { background: #FFFFFF; border-radius: 24rpx; padding: 28rpx; margin-bottom: 24rpx; box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.10); }
    .us-h { display: flex; justify-content: space-between; align-items: baseline; }
    .us-t { font-size: 30rpx; font-weight: 700; color: #1D1D1F; }
    .us-n { font-size: 26rpx; font-weight: 700; color: #1D1D1F; }
    .us-n.red { color: #B3261E; }
    .us-big { font-size: 36rpx; }
    .us-bar { height: 16rpx; border-radius: 8rpx; background: #E5E5EA; overflow: hidden; margin-top: 16rpx; }
    .us-bar-in { height: 100%; border-radius: 8rpx; background: #1A73E8; }
    .us-bar-in.yellow { background: #F29900; }
    .us-bar-in.red { background: #D93025; }
    .us-note { font-size: 24rpx; color: #6E6E73; margin-top: 16rpx; }
    .us-warn { font-size: 24rpx; color: #B3261E; background: #FCE8E6; border-radius: 12rpx; padding: 12rpx 16rpx; margin-top: 12rpx; }
    .us-opt { padding: 8rpx 28rpx; }
    .us-row { display: flex; justify-content: space-between; align-items: center; min-height: 96rpx; font-size: 28rpx; color: #1D1D1F; }
    .us-row + .us-row { border-top: 1px solid #E5E5EA; }
    .us-chip { font-size: 22rpx; font-weight: 700; padding: 6rpx 16rpx; border-radius: 20rpx; }
    .us-chip.ok { background: #E6F4EA; color: #0D652D; }
    .us-chip.gray { background: #F5F5F7; color: #6E6E73; border: 1px solid #E5E5EA; }
    .us-foot { font-size: 24rpx; color: #6E6E73; margin: 8rpx 8rpx 0; }
</style>
