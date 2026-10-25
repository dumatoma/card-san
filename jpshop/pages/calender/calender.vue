<template>
    <view class="bc-page">
        <!-- [カスタムエイド 2026-10-22] 営業カレンダーを作り直し:
             カレンダーはクーポン・お知らせ設定と同じ形(月曜はじまり・休みは赤い「休」・この日だけ変えた日は青い点)、
             右上は歯車ではなく「設定」、下のカードは中央そろえで Google と同じ言葉(営業中/営業時間外/休業日)、
             「この日だけ変更する」から下の画面で その日の営業・休み・時間・中休みを変える -->
        <u-navbar title="営業カレンダー" :placeholder="true" :safeAreaInsetTop="true" :border="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="left">
                <image src="../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" mode="" @click="toIndex"></image>
            </view>
            <view class="u-nav-slot" slot="right">
                <view class="bc-set" hover-class="bc-hover" @click="toSetting">設定</view>
            </view>
        </u-navbar>

        <view class="bc-body">
            <view class="bc-card bc-cal">
                <cs-calendar :value="sel" :rest="restKeys" :dots="dotKeys" weekColor @change="pick" @month="onMonth"></cs-calendar>
                <view class="bc-legend">
                    <text><text class="bc-l-rest">休</text> 休業日</text>
                    <text><text class="bc-l-dot"></text>この日だけ営業時間を変更</text>
                </view>
            </view>

            <view class="bc-card bc-day">
                <view class="bc-date">{{ selText }}</view>
                <view class="bc-st" :class="'c-' + status.color">{{ status.text }}<text class="bc-sub" v-if="status.sub"> ・ {{ status.sub }}</text></view>
                <view v-if="selDay && selDay.business == 1">
                    <view class="bc-hm" v-for="(t, i) in selTimes" :key="i">{{ t.start }} 〜 {{ t.end }}</view>
                </view>
                <view class="bc-off" v-else>終日お休み</view>
                <view class="bc-btn2" hover-class="bc-hover" @click="openSheet">この日だけ変更する</view>
            </view>
        </view>

        <!-- この日だけ変更(下から出る画面) -->
        <u-popup :show="show" mode="bottom" round="16" @close="show = false" :safeAreaInsetBottom="false">
            <view class="bs">
                <view class="bs-h">{{ selText }}<view class="bs-x" @click="show = false">✕</view></view>
                <view class="bs-in">
                    <view class="bs-seg">
                        <view :class="{ on: edit.business == 1 }" @click="edit.business = 1">営業日</view>
                        <view :class="{ on: edit.business == 0 }" @click="edit.business = 0">休業日</view>
                    </view>
                    <block v-if="edit.business == 1">
                        <view class="bs-lbl">営業時間</view>
                        <view class="bs-rng">
                            <view class="bs-tm" @click="pickTime('open')">{{ edit.open }}</view><text>〜</text>
                            <view class="bs-tm" @click="pickTime('close')">{{ edit.close }}</view>
                        </view>
                        <view class="bs-lbl">中休み</view>
                        <view class="bs-rng" v-if="edit.brk">
                            <view class="bs-tm" @click="pickTime('bs')">{{ edit.brk.start }}</view><text>〜</text>
                            <view class="bs-tm" @click="pickTime('be')">{{ edit.brk.end }}</view>
                            <view class="bs-del" @click="edit.brk = null">✕</view>
                        </view>
                        <view class="bs-add" v-else @click="addBreak">＋ 中休みを入れる</view>
                    </block>
                    <view class="bs-err" v-if="editErr">{{ editErr }}</view>
                    <view class="bs-link" v-if="weekDay" @click="useWeek">{{ weekLinkText }}</view>
                    <view class="bs-btn" :class="{ off: !!editErr }" hover-class="bc-hover" @click="save">保存する</view>
                </view>
            </view>
        </u-popup>
        <cs-picker :show="show1" :title="pickTitle" :columns="[times]" :defaultIndex="[pickIndex]" @confirm="confirmPicker" @close="show1 = false" @cancel="show1 = false"></cs-picker>
        <permission :show="showpermission" @close="showpermission = false"></permission>
    </view>
</template>
<script>
    import csCalendar from '@/components/csCalendar/csCalendar.vue'
    import csPicker from '@/components/csPicker/csPicker.vue'
    import { getCalendar, setBusinessHours, getAdminDetail } from '@/api/index.js'
    import permission from '@/components/permission/permission.vue'
    import { WEEK, TIMES, keyOf, toRange, fromRange, rangeError, rangeText, dayStatus, weekList } from '@/utils/csBusiness.js'
    export default {
        components: { csCalendar, permission, csPicker },
        data() {
            const today = keyOf(new Date())
            return {
                showpermission: false,
                today,
                sel: today,
                ym: today.slice(0, 7),
                days: {}, // 表示中の月:日にち → { business, business_time }
                overrides: {}, // 表示中の月の「この日だけ変更」
                rests: [],
                week: [], // 曜日の設定(7件)
                show: false,
                show1: false,
                edit: { business: 1, open: '10:00', close: '19:00', brk: null },
                pickKey: '',
                pickIndex: 0,
                times: TIMES,
                now: new Date(),
                saving: false
            }
        },
        computed: {
            pickTitle() {
                return { open: '開始', close: '終了', bs: '中休みの始まり', be: '中休みの終わり' }[this.pickKey] || '時間'
            },
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
            status() {
                return dayStatus(this.selDay, this.sel == this.today, this.now)
            },
            restKeys() {
                return this.rests.map((d) => this.ym + '-' + (d < 10 ? '0' : '') + d)
            },
            // この日だけ営業時間を変えた日(営業日で、曜日の設定と時間がちがう日)
            dotKeys() {
                const out = []
                Object.keys(this.overrides || {}).forEach((d) => {
                    const o = this.overrides[d]
                    if (!o || o.business != 1) return
                    const k = this.ym + '-' + (Number(d) < 10 ? '0' : '') + Number(d)
                    const p = k.split('-')
                    const w = this.week[new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2])).getDay()]
                    if (!w || w.business != 1 || rangeText(w.business_time) != rangeText(o.business_time)) out.push(k)
                })
                return out
            },
            weekDay() {
                const p = this.sel.split('-')
                return this.week[new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2])).getDay()] || null
            },
            weekLinkText() {
                const w = this.weekDay
                if (!w || w.business != 1) return 'いつもの設定(定休日)に戻す'
                return 'いつもの営業時間(' + rangeText(w.business_time).replace('  ', '・') + ')に戻す'
            },
            editErr() {
                if (this.edit.business != 1) return ''
                return rangeError(this.edit)
            }
        },
        onShow() {
            this.now = new Date()
            this.loadWeek()
            this.loadMonth(this.ym)
        },
        methods: {
            toIndex() {
                uni.switchTab({ url: '../index/index' })
            },
            loadWeek() {
                getCalendar({ type: 'week', date: '' }).then((res) => {
                    if (res.code == 200) this.week = weekList(res.data.week_business)
                })
            },
            loadMonth(ym) {
                getCalendar({ type: 'day', date: ym }).then((res) => {
                    if (res.code != 200 || ym != this.ym) return
                    this.days = res.data.day_business || {}
                    this.overrides = res.data.config_day_business || {}
                    this.rests = (res.data.rest_days || []).map((v) => v * 1)
                })
            },
            onMonth(e) {
                this.ym = e.y + '-' + (e.m < 10 ? '0' : '') + e.m
                this.days = {}
                this.overrides = {}
                this.rests = []
                // 今日の月なら今日、ほかの月は1日を選ぶ
                this.sel = this.today.slice(0, 7) == this.ym ? this.today : this.ym + '-01'
                this.loadMonth(this.ym)
            },
            pick(k) {
                this.sel = k
            },
            openSheet() {
                const d = this.selDay
                const r = toRange(d && d.business == 1 ? d.business_time : (this.weekDay && this.weekDay.business_time))
                this.edit = { business: d ? Number(d.business) : 1, open: r.open, close: r.close, brk: r.brk }
                this.show = true
            },
            useWeek() {
                const w = this.weekDay
                if (!w) return
                const r = toRange(w.business_time)
                this.edit = { business: Number(w.business), open: r.open, close: r.close, brk: r.brk }
            },
            addBreak() {
                this.edit.brk = { start: '13:00', end: '14:00' }
            },
            pickTime(k) {
                this.pickKey = k
                const v = k == 'open' ? this.edit.open : k == 'close' ? this.edit.close : k == 'bs' ? this.edit.brk.start : this.edit.brk.end
                this.pickIndex = Math.max(0, this.times.indexOf(v))
                this.show1 = true
            },
            confirmPicker(e) {
                const v = e.value[0]
                const k = this.pickKey
                if (k == 'open') this.edit.open = v
                else if (k == 'close') this.edit.close = v
                else if (k == 'bs') this.edit.brk.start = v
                else this.edit.brk.end = v
                this.show1 = false
            },
            save() {
                if (this.editErr || this.saving) return
                this.saving = true
                const obj = {
                    type: 'day',
                    date: this.sel,
                    business: this.edit.business,
                    business_time: this.edit.business == 1 ? fromRange(this.edit) : []
                }
                setBusinessHours(obj).then((res) => {
                    this.saving = false
                    if (res.code == 200) {
                        this.show = false
                        uni.showToast({ title: '保存しました' })
                        this.loadMonth(this.ym)
                    } else {
                        uni.showToast({ title: res.message, icon: 'none' })
                    }
                }).catch(() => { this.saving = false })
            },
            toSetting() {
                let that = this
                let admin = uni.getStorageSync('admin')
                if (admin.admin_type == 2) {
                    uni.showLoading({ title: '読み込み中', mask: true })
                    getAdminDetail(admin.id).then((res) => {
                        uni.hideLoading()
                        if (res.data.admin.privileges.includes(3)) {
                            uni.navigateTo({ url: '../calender/setting/setting' })
                        } else {
                            that.showpermission = true
                        }
                    }).catch(() => uni.hideLoading())
                } else {
                    uni.navigateTo({ url: '../calender/setting/setting' })
                }
            }
        }
    }
</script>
<style lang="scss">
    page { background: #F5F5F7; }
    .bc-page { min-height: 100vh; background: #F5F5F7; }
    .bc-set { height: 60rpx; line-height: 56rpx; padding: 0 28rpx; border: 1px solid #1A73E8; border-radius: 30rpx; color: #1A73E8; font-size: 26rpx; font-weight: bold; box-sizing: border-box; }
    .bc-hover { opacity: 0.6; }
    .bc-body { padding: 40rpx 24rpx calc(80rpx + env(safe-area-inset-bottom)); }
    .bc-card { background: #FFFFFF; border-radius: 32rpx; margin-bottom: 48rpx; }
    .bc-cal { padding: 24rpx 24rpx 8rpx; }
    .bc-legend { display: flex; gap: 32rpx; padding: 16rpx 8rpx 20rpx; font-size: 22rpx; color: #6E6E73; align-items: center; }
    .bc-legend > text { display: flex; align-items: center; }
    .bc-l-rest { color: #D93025; font-weight: bold; margin-right: 6rpx; }
    .bc-l-dot { display: inline-block; width: 12rpx; height: 12rpx; border-radius: 50%; background: #1A73E8; margin-right: 8rpx; }
    .bc-day { padding: 40rpx 32rpx; text-align: center; }
    .bc-date { font-size: 30rpx; color: #3C4043; font-weight: bold; }
    .bc-st { font-size: 34rpx; font-weight: bold; margin-top: 16rpx; }
    .bc-st.c-green { color: #188038; }
    .bc-st.c-red { color: #D93025; }
    .bc-st.c-plain { color: #3C4043; }
    .bc-sub { font-size: 28rpx; font-weight: normal; color: #3C4043; }
    .bc-hm { font-size: 48rpx; font-weight: bold; color: #1D1D1F; margin-top: 12rpx; }
    .bc-off { font-size: 30rpx; color: #6E6E73; margin-top: 16rpx; }
    .bc-btn2 { margin-top: 40rpx; height: 88rpx; line-height: 86rpx; border: 1px solid #1A73E8; border-radius: 24rpx; color: #1A73E8; font-size: 28rpx; box-sizing: border-box; }

    /* 下から出る画面 */
    .bs { background: #FFFFFF; border-radius: 32rpx 32rpx 0 0; }
    .bs-h { position: relative; height: 112rpx; line-height: 112rpx; text-align: center; font-size: 34rpx; font-weight: bold; color: #1D1D1F; border-bottom: 1px solid #E5E5EA; }
    .bs-x { position: absolute; right: 24rpx; top: 24rpx; width: 64rpx; height: 64rpx; line-height: 64rpx; border-radius: 50%; background: #E5E5EA; color: #6E6E73; font-size: 28rpx; font-weight: normal; }
    .bs-in { padding: 40rpx 40rpx calc(48rpx + env(safe-area-inset-bottom)); }
    .bs-seg { display: flex; background: #EEEEF0; border-radius: 20rpx; padding: 6rpx; }
    .bs-seg view { flex: 1; height: 76rpx; line-height: 74rpx; text-align: center; font-size: 28rpx; color: #515154; border-radius: 16rpx; border: 1px solid transparent; box-sizing: border-box; }
    .bs-seg view.on { background: rgba(26, 115, 232, 0.10); border-color: #1A73E8; color: #1A73E8; font-weight: bold; }
    .bs-lbl { font-size: 26rpx; font-weight: bold; color: #1D1D1F; margin: 40rpx 0 16rpx; }
    .bs-rng { display: flex; align-items: center; font-size: 30rpx; color: #1D1D1F; }
    .bs-rng > text { margin: 0 20rpx; }
    .bs-tm { background: #F2F2F7; border-radius: 16rpx; padding: 16rpx 28rpx; font-size: 30rpx; }
    .bs-del { margin-left: auto; color: #8E8E93; font-size: 30rpx; padding: 12rpx; }
    .bs-add { color: #1A73E8; font-size: 28rpx; padding: 8rpx 0; }
    .bs-err { color: #D93025; font-size: 24rpx; margin-top: 24rpx; }
    .bs-link { color: #1A73E8; font-size: 26rpx; text-align: center; margin-top: 40rpx; }
    .bs-btn { margin-top: 64rpx; height: 104rpx; line-height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; text-align: center; font-size: 32rpx; font-weight: bold; }
    .bs-btn.off { background: #A8C7FA; }
</style>
