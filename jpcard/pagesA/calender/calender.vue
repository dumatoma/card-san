<template>
    <view class="bc-page">
        <!-- [カスタムエイド 2026-10-22] お店の営業カレンダーを管理App と同じ形に(見るだけ):
             月曜はじまり・休みは赤い「休」、下のカードは中央そろえで Google と同じ言葉(営業中/営業時間外/休業日)。
             以前は月を変えるとお店の番号を送っておらず、ほかの月が表示できなかったのも直した -->
        <u-navbar title="営業カレンダー" :placeholder="true" :safeAreaInsetTop="true" :border="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="left">
                <image src="../../static/svg/button.svg" style="width: 60upx;height: 60upx;" mode="" @click="toIndex"></image>
            </view>
        </u-navbar>
        <view class="bc-body">
            <view class="bc-card bc-cal">
                <cs-calendar :value="sel" :rest="restKeys" weekColor @change="pick" @month="onMonth"></cs-calendar>
                <view class="bc-legend"><text><text class="bc-l-rest">休</text> 休業日</text></view>
            </view>
            <view class="bc-card bc-day">
                <view class="bc-date">{{ selText }}</view>
                <view class="bc-st" :class="'c-' + status.color">{{ status.text }}<text class="bc-sub" v-if="status.sub"> ・ {{ status.sub }}</text></view>
                <view v-if="selDay && selDay.business == 1">
                    <view class="bc-hm" v-for="(t, i) in selTimes" :key="i">{{ t.start }} 〜 {{ t.end }}</view>
                </view>
                <view class="bc-off" v-else-if="selDay">終日お休み</view>
            </view>
        </view>
    </view>
</template>
<script>
    import csCalendar from '@/components/csCalendar/csCalendar.vue'
    import { getCalendar } from '@/api/index.js'
    const WEEK = ['日', '月', '火', '水', '木', '金', '土']
    const pad = (n) => (n < 10 ? '0' : '') + n
    const keyOf = (dt) => dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate())
    export default {
        components: { csCalendar },
        data() {
            const today = keyOf(new Date())
            return { sid: '', today, sel: today, ym: today.slice(0, 7), days: {}, rests: [], now: new Date() }
        },
        computed: {
            selDay() {
                if (this.sel.slice(0, 7) != this.ym) return null
                return this.days[Number(this.sel.slice(8, 10))] || null
            },
            selTimes() {
                return ((this.selDay && this.selDay.business_time) || []).filter((t) => t && t.start && t.end)
            },
            selText() {
                const p = this.sel.split('-')
                const w = new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2])).getDay()
                return Number(p[1]) + '月' + Number(p[2]) + '日(' + WEEK[w] + ')'
            },
            restKeys() {
                return this.rests.map((d) => this.ym + '-' + pad(d))
            },
            status() {
                const day = this.selDay
                if (!day) return { text: '', color: 'plain', sub: '' }
                if (day.business != 1) return { text: '休業日', color: 'red', sub: '' }
                if (this.sel != this.today) return { text: '営業時間', color: 'plain', sub: '' }
                const hm = pad(this.now.getHours()) + ':' + pad(this.now.getMinutes())
                const a = this.selTimes
                if (a.some((t) => t.start <= hm && hm < t.end)) return { text: '営業中', color: 'green', sub: '' }
                const next = a.find((t) => t.start > hm)
                return { text: '営業時間外', color: 'red', sub: next ? '営業開始 ' + next.start : '' }
            }
        },
        onLoad(options) {
            this.sid = options.sid
            this.loadMonth(this.ym)
        },
        onShow() {
            this.now = new Date()
        },
        methods: {
            toIndex() {
                uni.navigateBack()
            },
            loadMonth(ym) {
                getCalendar({ id: this.sid, type: 'day', date: ym }).then((res) => {
                    if (res.code != 200 || ym != this.ym) return
                    this.days = res.data.day_business || {}
                    this.rests = (res.data.rest_days || []).map((v) => v * 1)
                })
            },
            onMonth(e) {
                this.ym = e.y + '-' + pad(e.m)
                this.days = {}
                this.rests = []
                this.sel = this.today.slice(0, 7) == this.ym ? this.today : this.ym + '-01'
                this.loadMonth(this.ym)
            },
            pick(k) {
                this.sel = k
            }
        }
    }
</script>
<style lang="scss">
    .bc-page { min-height: 100vh; background: #F5F5F7; }
    .bc-body { padding: 40rpx 24rpx calc(80rpx + env(safe-area-inset-bottom)); }
    .bc-card { background: #FFFFFF; border-radius: 32rpx; margin-bottom: 48rpx; }
    .bc-cal { padding: 24rpx 24rpx 8rpx; }
    .bc-legend { display: flex; padding: 16rpx 8rpx 20rpx; font-size: 22rpx; color: #6E6E73; }
    .bc-l-rest { color: #D93025; font-weight: bold; margin-right: 6rpx; }
    .bc-day { padding: 40rpx 32rpx; text-align: center; }
    .bc-date { font-size: 30rpx; color: #3C4043; font-weight: bold; }
    .bc-st { font-size: 34rpx; font-weight: bold; margin-top: 16rpx; }
    .bc-st.c-green { color: #188038; }
    .bc-st.c-red { color: #D93025; }
    .bc-st.c-plain { color: #3C4043; }
    .bc-sub { font-size: 28rpx; font-weight: normal; color: #3C4043; }
    .bc-hm { font-size: 48rpx; font-weight: bold; color: #1D1D1F; margin-top: 12rpx; }
    .bc-off { font-size: 30rpx; color: #6E6E73; margin-top: 16rpx; }
</style>
