<template>
    <!-- [カスタムエイド 2026-11-06] Googleアクセス解析:閲覧数・電話・道順・検索キーワード(Business Profile Performance API) -->
    <view class="page">
        <u-navbar title="Googleアクセス解析" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="denied" v-if="denied">{{ denied }}</view>

        <view v-else>
            <view class="sec">Googleアクセス解析</view>
            <view class="seg">
                <view v-for="d in dayList" :key="d" :class="days == d ? 'on' : ''" @click="setDays(d)">{{ d }}日</view>
            </view>

            <view v-if="data">
                <view class="big">
                    <view class="l">閲覧数</view>
                    <text class="n">{{ fmt(data.views.total) }}</text>
                    <text v-if="pct(data.views) !== null" :class="pct(data.views) >= 0 ? 'up' : 'dn'">{{ pctText(data.views) }}</text>
                </view>
                <view class="sub">Google 検索から {{ fmt(data.views.search) }} ・ Google マップから {{ fmt(data.views.maps) }}</view>
                <image class="chart" :src="chartSrc" mode="widthFix"></image>
                <view class="axis"><text>{{ data.from }}</text><text>{{ data.until }}</text></view>

                <view class="grid">
                    <view v-for="it in counts" :key="it.key">
                        <view class="gn">{{ fmt(data[it.key].total) }}回</view>
                        <view class="gl">{{ it.label }}<text v-if="pct(data[it.key]) !== null" :class="pct(data[it.key]) >= 0 ? 'up' : 'dn'">{{ pctText(data[it.key]) }}</text></view>
                    </view>
                </view>
                <view class="period">{{ data.from }}〜{{ data.until }} の数字です。↑↓は、その前の{{ days }}日と比べた増え方です。</view>
            </view>
            <view class="empty" v-else-if="loading">読み込み中…</view>

            <view class="sec">検索キーワード</view>
            <view class="seg">
                <view :class="kw == 1 ? 'on' : ''" @click="setKw(1)">先月</view>
                <view :class="kw == 3 ? 'on' : ''" @click="setKw(3)">過去3ヶ月</view>
            </view>
            <view v-if="data">
                <view class="kwRange" v-if="data.kw_range">{{ data.kw_range }}</view>
                <view class="kw" v-for="(k, i) in data.keywords" :key="k.word">
                    <view class="kwWord"><text class="kwNo">{{ i + 1 }}</text>{{ k.word }}</view>
                    <text :class="k.low ? 'kwLow' : 'kwNum'">{{ k.low ? '少数' : fmt(k.value) + '人' }}</text>
                </view>
                <view class="empty" v-if="!data.keywords.length">{{ data.kw_msg || '検索キーワードはありません。' }}</view>
            </view>

            <view class="note">数字は Google から取り込んでいます。Google に数字が入るまで数日かかるため、数日前までの数字になります。「少数」は、人数が少ないため Google が正確な数を出していない言葉です。</view>
        </view>
    </view>
</template>

<script>
    import { gbpInsights } from '@/api/gbp.js'

    export default {
        data() {
            return {
                dayList: [7, 30, 90],
                days: 30,
                kw: 1,
                data: null,
                loading: false,
                denied: '',
                counts: [
                    { key: 'calls', label: '電話をかけた' },
                    { key: 'directions', label: '道順を調べた' },
                    { key: 'website', label: 'ホームページを見た' },
                ],
            }
        },
        computed: {
            // 折れ線グラフ(SVG を画像にする)
            chartSrc() {
                let s = (this.data && this.data.series) || []
                let W = 700, H = 200, P = 6
                let max = Math.max(1, ...s.map(x => Number(x.v) || 0))
                let pts = s.map((x, i) => {
                    let px = s.length > 1 ? P + (W - P * 2) * i / (s.length - 1) : W / 2
                    let py = H - P - (H - P * 2) * (Number(x.v) || 0) / max
                    return px.toFixed(1) + ',' + py.toFixed(1)
                }).join(' ')
                let svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + W + '" height="' + H + '" viewBox="0 0 ' + W + ' ' + H + '">' +
                    '<line x1="0" y1="' + (H - 1) + '" x2="' + W + '" y2="' + (H - 1) + '" stroke="#d2d2d7" stroke-width="2"/>' +
                    (pts ? '<polyline fill="none" stroke="#1a73e8" stroke-width="4" stroke-linejoin="round" stroke-linecap="round" points="' + pts + '"/>' : '') +
                    '</svg>'
                return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg)
            },
        },
        onLoad() {
            this.load()
        },
        methods: {
            fmt(n) {
                return String(Number(n) || 0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
            },
            // 前の期間と比べた増え方(前が 0 のときは出さない)
            pct(o) {
                let c = Number(o && o.total) || 0, p = Number(o && o.prev) || 0
                if (!p) return null
                return Math.round((c - p) / p * 100)
            },
            pctText(o) {
                let v = this.pct(o)
                return v >= 0 ? '↑' + v + '%' : '↓' + Math.abs(v) + '%'
            },
            setDays(d) {
                if (this.loading || this.days == d) return
                this.days = d
                this.load()
            },
            setKw(k) {
                if (this.loading || this.kw == k) return
                this.kw = k
                this.load()
            },
            load() {
                this.loading = true
                uni.showLoading({ title: '読み込み中…', mask: true })
                gbpInsights(this.days, this.kw).then(res => {
                    this.loading = false
                    uni.hideLoading()
                    if (res && res.code == 200) {
                        this.denied = ''
                        this.data = res.data
                    } else if (res && res.code == 403) {
                        this.denied = res.message || 'この機能を利用する権限がありません'
                    } else if (res) {
                        uni.showModal({ title: 'Googleアクセス解析', content: res.message || 'エラーが発生しました', showCancel: false, confirmText: '閉じる' })
                    }
                }).catch(err => {
                    this.loading = false
                    uni.hideLoading()
                    let r = err && err.response
                    if (r && r.status == 403) {
                        this.denied = (r.data && r.data.message) || 'この機能を利用する権限がありません'
                        return
                    }
                    uni.showModal({ title: 'Googleアクセス解析', content: (r && r.data && r.data.message) || '通信エラーが発生しました', showCancel: false, confirmText: '閉じる' })
                })
            },
        }
    }
</script>

<style lang="scss">
    $text: #1d1d1f;
    $sub: #707070;
    $blue: #1a73e8;
    $border: #d2d2d7;

    page { background: #fff; }
    .page { min-height: 100vh; background: #fff; padding-bottom: 60rpx; }
    .denied { padding: 60rpx 38rpx; font-size: 27rpx; color: $sub; line-height: 1.7; }
    .sec { padding: 34rpx 38rpx 10rpx; font-size: 27rpx; color: $sub; }
    .seg {
        display: flex; margin: 10rpx 38rpx 26rpx; border: 1px solid $blue; border-radius: 15rpx; overflow: hidden;
        view { flex: 1; text-align: center; padding: 14rpx 0; font-size: 27rpx; color: $blue; border-left: 1px solid $blue; }
        view:first-child { border-left: 0; }
        .on { background: $blue; color: #fff; }
    }
    .big { padding: 0 38rpx 6rpx; }
    .l { font-size: 25rpx; color: $sub; }
    .n { font-size: 58rpx; font-weight: bold; color: $text; }
    .up { color: #188038; font-size: 25rpx; margin-left: 12rpx; }
    .dn { color: #D93025; font-size: 25rpx; margin-left: 12rpx; }
    .sub { font-size: 23rpx; color: $sub; padding: 0 38rpx 16rpx; }
    .chart { display: block; width: 674rpx; margin: 0 38rpx; }
    .axis { display: flex; justify-content: space-between; padding: 4rpx 38rpx 20rpx; font-size: 20rpx; color: $sub; }
    .grid {
        display: flex; border-top: 1px solid $border; border-bottom: 1px solid $border;
        > view { flex: 1; padding: 22rpx 0; text-align: center; border-left: 1px solid $border; }
        > view:first-child { border-left: 0; }
        .gn { font-size: 38rpx; font-weight: bold; color: $text; }
        .gl { font-size: 22rpx; color: $sub; margin-top: 4rpx; }
        .up, .dn { font-size: 22rpx; margin-left: 6rpx; }
    }
    .period { font-size: 22rpx; color: #6E6E73; line-height: 1.6; padding: 16rpx 38rpx 10rpx; }
    .kwRange { font-size: 23rpx; color: $sub; padding: 0 38rpx 8rpx; }
    .kw { display: flex; justify-content: space-between; align-items: center; padding: 22rpx 38rpx; border-bottom: 1px solid #eee; font-size: 27rpx; color: $text; }
    .kwWord { flex: 1; min-width: 0; margin-right: 20rpx; word-break: break-all; }
    .kwNo { color: $sub; margin-right: 20rpx; }
    .kwNum { color: $text; flex-shrink: 0; }
    .kwLow { color: $sub; font-size: 23rpx; flex-shrink: 0; }
    .empty { padding: 30rpx 38rpx; font-size: 25rpx; color: $sub; }
    .note { font-size: 22rpx; color: #6E6E73; line-height: 1.6; padding: 24rpx 38rpx 40rpx; }
</style>
