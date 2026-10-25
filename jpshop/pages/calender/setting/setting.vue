<template>
    <view class="bh-page">
        <!-- [カスタムエイド 2026-10-22] 定休日と営業時間を作り直し:
             「いつもの営業時間」を1つ決めると全部の曜日に入り、曜日ごとに違うところだけ直す(例:金曜だけ19時まで)。
             曜日を押すと下から出る画面で、毎週営業/毎週休み/一部の週だけ休み(第1〜第5)・営業時間・中休みを決める。
             保存の形は今までどおり(曜日ごと 0〜6 の key 付き)。一部の週だけ休みは rest_weeks として一緒に保存し、
             当たる日を 12か月先まで「この日だけ休み」として入れる(予約の受付・Google にもそのまま反映される) -->
        <u-navbar title="定休日と営業時間" :placeholder="true" :safeAreaInsetTop="true" :border="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="left">
                <image src="../../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" @click="goBack" mode=""></image>
            </view>
        </u-navbar>

        <view class="bh-body" v-if="loaded">
            <view class="bh-sec">いつもの営業時間</view>
            <view class="bh-cap">ここを変えると「いつも通り」の曜日がすべて同じ時間になります。</view>
            <view class="bh-card">
                <view class="bh-row"><text>開始</text><view class="bh-tm" @click="pickTime('base', 'open')">{{ base.open }}</view></view>
                <view class="bh-row"><text>終了</text><view class="bh-tm" @click="pickTime('base', 'close')">{{ base.close }}</view></view>
                <view class="bh-row">
                    <text>中休み</text>
                    <u-switch :value="!!base.brk" @change="toggleBaseBreak" activeColor="#34C759"></u-switch>
                </view>
                <view class="bh-row" v-if="base.brk">
                    <text>中休みの時間</text>
                    <view class="bh-rng">
                        <view class="bh-tm" @click="pickTime('base', 'bs')">{{ base.brk.start }}</view><text>〜</text>
                        <view class="bh-tm" @click="pickTime('base', 'be')">{{ base.brk.end }}</view>
                    </view>
                </view>
            </view>
            <view class="bh-err" v-if="baseErr">{{ baseErr }}</view>

            <view class="bh-sec">曜日ごと</view>
            <view class="bh-cap">押すと、その曜日だけ調整できます。祝日や臨時のお休みは、営業カレンダーの日付から設定してください。</view>
            <view class="bh-card">
                <view class="bh-w" v-for="(r, i) in order" :key="i" hover-class="bh-hit" @click="openDay(r)">
                    <text class="bh-wd" :class="{ su: r == 0, sa: r == 6 }">{{ WEEK[r] }}</text>
                    <view class="bh-wt">
                        <text class="bh-rest" v-if="rows[r].mode == 'off'">定休日</text>
                        <block v-else>
                            <text>{{ timeText(rows[r]) }}</text>
                            <text class="bh-badge" :class="isBase(rows[r]) ? 'same' : 'adj'">{{ isBase(rows[r]) ? 'いつも通り' : '調整あり' }}</text>
                            <view class="bh-part" v-if="rows[r].mode == 'part'">{{ weeksText(rows[r].weeks) }} 休み</view>
                        </block>
                    </view>
                    <view class="bh-chev"></view>
                </view>
            </view>

            <view class="bh-btn" :class="{ off: !dirty || !!baseErr }" hover-class="bh-hover" @click="save">この設定で保存する</view>
        </view>

        <!-- 曜日の調整(下から出る画面) -->
        <u-popup :show="show" mode="bottom" round="16" @close="show = false" :safeAreaInsetBottom="false">
            <view class="bs" v-if="edit">
                <view class="bs-h">{{ WEEK[edit.w] }}曜日<view class="bs-x" @click="show = false">✕</view></view>
                <view class="bs-in">
                    <view class="bs-seg">
                        <view :class="{ on: edit.mode == 'on' }" @click="edit.mode = 'on'">毎週営業</view>
                        <view :class="{ on: edit.mode == 'off' }" @click="edit.mode = 'off'">毎週休み</view>
                        <view class="wide" :class="{ on: edit.mode == 'part' }" @click="setPart">一部の週だけ休み</view>
                    </view>
                    <block v-if="edit.mode == 'part'">
                        <view class="bs-lbl">休む週(いくつでも選べます)</view>
                        <view class="bs-wk">
                            <view v-for="n in [1, 2, 3, 4, 5]" :key="n" :class="{ on: edit.weeks.indexOf(n) > -1 }" @click="toggleWeek(n)">第{{ n }}</view>
                        </view>
                        <view class="bs-note" v-if="nextText">次の休み:{{ nextText }}</view>
                    </block>
                    <block v-if="edit.mode != 'off'">
                        <view class="bs-lbl">{{ edit.mode == 'part' ? '営業する週の営業時間' : '営業時間' }}</view>
                        <view class="bs-rng">
                            <view class="bs-tm" @click="pickTime('edit', 'open')">{{ edit.open }}</view><text>〜</text>
                            <view class="bs-tm" @click="pickTime('edit', 'close')">{{ edit.close }}</view>
                        </view>
                        <view class="bs-lbl">中休み</view>
                        <view class="bs-rng" v-if="edit.brk">
                            <view class="bs-tm" @click="pickTime('edit', 'bs')">{{ edit.brk.start }}</view><text>〜</text>
                            <view class="bs-tm" @click="pickTime('edit', 'be')">{{ edit.brk.end }}</view>
                            <view class="bs-del" @click="edit.brk = null">✕</view>
                        </view>
                        <view class="bs-add" v-else @click="edit.brk = { start: '13:00', end: '14:00' }">＋ 中休みを入れる</view>
                        <view class="bs-link" @click="useBase">いつもの営業時間({{ baseText }})に戻す</view>
                    </block>
                    <view class="bs-err" v-if="editErr">{{ editErr }}</view>
                    <view class="bs-btn" :class="{ off: !!editErr }" hover-class="bh-hover" @click="applyDay">保存する</view>
                </view>
            </view>
        </u-popup>
        <cs-picker :show="show1" :title="pickTitle" :columns="[times]" :defaultIndex="[pickIndex]" @confirm="confirmPicker" @close="show1 = false" @cancel="show1 = false"></cs-picker>
    </view>
</template>

<script>
    import { getCalendar, setBusinessHours } from '@/api/index.js'
    import csPicker from '@/components/csPicker/csPicker.vue'
    import { WEEK, TIMES, keyOf, toRange, fromRange, rangeError, ruleDates, fillEnd, weekList, syncRuleDays } from '@/utils/csBusiness.js'
    const same = (a, b) => a.open == b.open && a.close == b.close && JSON.stringify(a.brk || null) == JSON.stringify(b.brk || null)
    export default {
        components: { csPicker },
        data() {
            return {
                WEEK,
                times: TIMES,
                order: [1, 2, 3, 4, 5, 6, 0], // 月曜から
                loaded: false,
                base: { open: '10:00', close: '19:00', brk: null },
                rows: [], // 0(日)〜6(土):{ mode: on/off/part, weeks: [], open, close, brk }
                oldWb: null,
                dirty: false,
                show: false,
                show1: false,
                edit: null,
                pickWhere: '',
                pickKey: '',
                pickIndex: 0,
                saving: false
            }
        },
        computed: {
            pickTitle() {
                return { open: '開始', close: '終了', bs: '中休みの始まり', be: '中休みの終わり' }[this.pickKey] || '時間'
            },
            baseText() {
                const b = this.base
                return b.open + '〜' + b.close + (b.brk ? '・中休み ' + b.brk.start + '〜' + b.brk.end : '')
            },
            baseErr() {
                return rangeError(this.base)
            },
            editErr() {
                if (!this.edit) return ''
                if (this.edit.mode == 'part' && !this.edit.weeks.length) return '休む週を選んでください'
                if (this.edit.mode == 'off') return ''
                return rangeError(this.edit)
            },
            nextText() {
                if (!this.edit || !this.edit.weeks.length) return ''
                const wb = {}
                wb[this.edit.w] = { business: 1, rest_weeks: this.edit.weeks }
                const list = ruleDates(wb, keyOf(new Date()), fillEnd()).slice(0, 3)
                return list.map((x) => Number(x.key.slice(5, 7)) + '/' + Number(x.key.slice(8, 10)) + '(' + WEEK[x.w] + ')').join('・') + (list.length ? '…' : '')
            }
        },
        onLoad() {
            this.load()
        },
        methods: {
            load() {
                getCalendar({ type: 'week', date: '' }).then((res) => {
                    if (res.code != 200) {
                        uni.showToast({ title: res.message, icon: 'none' })
                        return
                    }
                    const wb = res.data.week_business
                    this.oldWb = JSON.parse(JSON.stringify(wb || {}))
                    const list = weekList(wb)
                    const rows = list.map((v) => {
                        const r = toRange(v && v.business_time)
                        const weeks = (v && Array.isArray(v.rest_weeks)) ? v.rest_weeks.map(Number) : []
                        const mode = !v ? 'on' : v.business != 1 ? 'off' : weeks.length ? 'part' : 'on'
                        return { mode, weeks, open: r.open, close: r.close, brk: r.brk }
                    })
                    // いつもの営業時間 = 営業する曜日でいちばん多い時間
                    const count = {}
                    rows.forEach((r) => {
                        if (r.mode == 'off') return
                        const k = JSON.stringify({ open: r.open, close: r.close, brk: r.brk })
                        count[k] = (count[k] || 0) + 1
                    })
                    const top = Object.keys(count).sort((a, b) => count[b] - count[a])[0]
                    if (top) this.base = JSON.parse(top)
                    this.rows = rows
                    this.loaded = true
                    this.dirty = false
                })
            },
            isBase(r) {
                return same(r, this.base)
            },
            timeText(r) {
                return r.open + '〜' + r.close + (r.brk ? '(中休み ' + r.brk.start + '〜' + r.brk.end + ')' : '')
            },
            weeksText(w) {
                return w.slice().sort().map((n) => '第' + n).join('・')
            },
            // いつもの営業時間を変えたら、いつも通りの曜日も同じに
            changeBase(fn) {
                const old = JSON.parse(JSON.stringify(this.base))
                fn(this.base)
                this.rows.forEach((r) => {
                    if (r.mode != 'off' && same(r, old)) {
                        r.open = this.base.open
                        r.close = this.base.close
                        r.brk = this.base.brk ? { start: this.base.brk.start, end: this.base.brk.end } : null
                    }
                })
                this.dirty = true
            },
            toggleBaseBreak(on) {
                this.changeBase((b) => { b.brk = on ? { start: '13:00', end: '14:00' } : null })
            },
            openDay(w) {
                const r = this.rows[w]
                this.edit = { w, mode: r.mode, weeks: r.weeks.slice(), open: r.open, close: r.close, brk: r.brk ? { start: r.brk.start, end: r.brk.end } : null }
                this.show = true
            },
            setPart() {
                this.edit.mode = 'part'
            },
            toggleWeek(n) {
                const i = this.edit.weeks.indexOf(n)
                if (i > -1) this.edit.weeks.splice(i, 1)
                else this.edit.weeks.push(n)
            },
            useBase() {
                this.edit.open = this.base.open
                this.edit.close = this.base.close
                this.edit.brk = this.base.brk ? { start: this.base.brk.start, end: this.base.brk.end } : null
            },
            pickTime(where, key) {
                this.pickWhere = where
                this.pickKey = key
                const o = where == 'base' ? this.base : this.edit
                const v = key == 'open' ? o.open : key == 'close' ? o.close : key == 'bs' ? o.brk.start : o.brk.end
                this.pickIndex = Math.max(0, this.times.indexOf(v))
                this.show1 = true
            },
            confirmPicker(e) {
                const v = e.value[0]
                const k = this.pickKey
                const set = (o) => {
                    if (k == 'open') o.open = v
                    else if (k == 'close') o.close = v
                    else if (k == 'bs') o.brk.start = v
                    else o.brk.end = v
                }
                if (this.pickWhere == 'base') this.changeBase(set)
                else set(this.edit)
                this.show1 = false
            },
            // 曜日の調整を決めて、そのまま保存する
            applyDay() {
                if (this.editErr) return
                const e = this.edit
                const r = this.rows[e.w]
                r.mode = e.mode
                r.weeks = e.mode == 'part' ? e.weeks.slice().sort() : []
                if (e.mode != 'off') {
                    r.open = e.open
                    r.close = e.close
                    r.brk = e.brk
                }
                this.show = false
                this.dirty = true
                this.save()
            },
            save() {
                if (!this.dirty || this.baseErr || this.saving) return
                const bad = this.rows.find((r) => r.mode != 'off' && rangeError(r))
                if (bad) {
                    uni.showToast({ title: WEEK[this.rows.indexOf(bad)] + '曜日の営業時間を確かめてください', icon: 'none' })
                    return
                }
                const wb = {}
                this.rows.forEach((r, i) => {
                    const v = { business: r.mode == 'off' ? 0 : 1, business_time: fromRange(r), key: i }
                    if (r.mode == 'part') v.rest_weeks = r.weeks
                    wb[i] = v
                })
                this.saving = true
                uni.showLoading({ title: '保存しています', mask: true })
                setBusinessHours({ type: 'week', week_business: wb }).then(async (res) => {
                    if (res.code != 200) {
                        this.saving = false
                        uni.hideLoading()
                        uni.showToast({ title: res.message, icon: 'none' })
                        return
                    }
                    // 一部の週だけ休み:当たる日を「この日だけ休み」に(外した週は営業に戻す)
                    let failed = false
                    try {
                        await syncRuleDays(wb, this.oldWb)
                    } catch (err) {
                        failed = true
                    }
                    this.saving = false
                    this.dirty = false
                    this.oldWb = JSON.parse(JSON.stringify(wb))
                    uni.hideLoading()
                    if (failed) uni.showToast({ title: '保存しました。休みの日の一部が入らなかったため、少し後に営業カレンダーを開き直してください', icon: 'none', duration: 3000 })
                    else uni.showToast({ title: '保存しました' })
                }).catch(() => {
                    this.saving = false
                    uni.hideLoading()
                })
            },
            goBack() {
                const pages = getCurrentPages()
                if (pages.length > 1) uni.navigateBack()
                else uni.redirectTo({ url: '../calender' })
            }
        }
    }
</script>

<style lang="scss">
    page { background: #F5F5F7; }
    .bh-page { min-height: 100vh; background: #F5F5F7; }
    .bh-body { padding: 40rpx 24rpx calc(80rpx + env(safe-area-inset-bottom)); }
    .bh-sec { font-size: 28rpx; font-weight: bold; color: #1D1D1F; padding: 0 8rpx; }
    .bh-cap { font-size: 24rpx; color: #6E6E73; line-height: 1.6; padding: 8rpx 8rpx 20rpx; }
    .bh-card { background: #FFFFFF; border-radius: 32rpx; padding: 0 32rpx; margin-bottom: 48rpx; }
    .bh-row { display: flex; align-items: center; justify-content: space-between; min-height: 112rpx; border-bottom: 1px solid #E5E5EA; font-size: 30rpx; color: #1D1D1F; }
    .bh-row:last-child { border-bottom: 0; }
    .bh-rng { display: flex; align-items: center; }
    .bh-rng > text { margin: 0 16rpx; }
    .bh-tm { background: #F2F2F7; border-radius: 16rpx; padding: 14rpx 24rpx; font-size: 30rpx; color: #1D1D1F; }
    .bh-err { color: #D93025; font-size: 24rpx; margin: -32rpx 8rpx 40rpx; }
    .bh-w { display: flex; align-items: center; min-height: 116rpx; border-bottom: 1px solid #E5E5EA; }
    .bh-w:last-child { border-bottom: 0; }
    .bh-hit { background: #F5F5F7; }
    .bh-wd { width: 64rpx; font-size: 32rpx; font-weight: bold; color: #1D1D1F; flex-shrink: 0; }
    .bh-wd.su { color: #D93025; }
    .bh-wd.sa { color: #1A73E8; }
    .bh-wt { flex: 1; font-size: 28rpx; color: #1D1D1F; padding: 20rpx 0; line-height: 1.5; }
    .bh-rest { color: #D93025; font-weight: bold; font-size: 30rpx; }
    .bh-badge { display: inline-block; font-size: 20rpx; font-weight: bold; border-radius: 16rpx; padding: 2rpx 14rpx; margin-left: 12rpx; vertical-align: 2rpx; }
    .bh-badge.same { background: #F2F2F7; color: #6E6E73; }
    .bh-badge.adj { background: #E8F0FE; color: #1A73E8; }
    .bh-part { font-size: 22rpx; color: #D93025; font-weight: bold; }
    .bh-chev { width: 16rpx; height: 16rpx; border-top: 3rpx solid #C7C7CC; border-right: 3rpx solid #C7C7CC; transform: rotate(45deg); margin: 0 8rpx 0 16rpx; flex-shrink: 0; }
    .bh-btn { height: 104rpx; line-height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; text-align: center; font-size: 32rpx; font-weight: bold; }
    .bh-btn.off { background: #A8C7FA; }
    .bh-hover { opacity: 0.7; }

    /* 下から出る画面 */
    .bs { background: #FFFFFF; border-radius: 32rpx 32rpx 0 0; }
    .bs-h { position: relative; height: 112rpx; line-height: 112rpx; text-align: center; font-size: 34rpx; font-weight: bold; color: #1D1D1F; border-bottom: 1px solid #E5E5EA; }
    .bs-x { position: absolute; right: 24rpx; top: 24rpx; width: 64rpx; height: 64rpx; line-height: 64rpx; border-radius: 50%; background: #E5E5EA; color: #6E6E73; font-size: 28rpx; font-weight: normal; }
    .bs-in { padding: 40rpx 40rpx calc(48rpx + env(safe-area-inset-bottom)); }
    .bs-seg { display: flex; background: #EEEEF0; border-radius: 20rpx; padding: 6rpx; }
    .bs-seg view { flex: 1; height: 76rpx; line-height: 74rpx; text-align: center; font-size: 26rpx; color: #515154; border-radius: 16rpx; border: 1px solid transparent; box-sizing: border-box; }
    .bs-seg view.wide { flex: 1.45; }
    .bs-seg view.on { background: rgba(26, 115, 232, 0.10); border-color: #1A73E8; color: #1A73E8; font-weight: bold; }
    .bs-lbl { font-size: 26rpx; font-weight: bold; color: #1D1D1F; margin: 40rpx 0 16rpx; }
    .bs-wk { display: flex; gap: 16rpx; }
    .bs-wk view { flex: 1; height: 84rpx; line-height: 82rpx; text-align: center; border: 1px solid #D2D2D7; border-radius: 20rpx; font-size: 28rpx; color: #515154; box-sizing: border-box; }
    .bs-wk view.on { background: rgba(26, 115, 232, 0.10); border-color: #1A73E8; color: #1A73E8; font-weight: bold; }
    .bs-note { font-size: 24rpx; color: #6E6E73; margin-top: 20rpx; }
    .bs-rng { display: flex; align-items: center; font-size: 30rpx; color: #1D1D1F; }
    .bs-rng > text { margin: 0 20rpx; }
    .bs-tm { background: #F2F2F7; border-radius: 16rpx; padding: 16rpx 28rpx; font-size: 30rpx; }
    .bs-del { margin-left: auto; color: #8E8E93; font-size: 30rpx; padding: 12rpx; }
    .bs-add { color: #1A73E8; font-size: 28rpx; padding: 8rpx 0; }
    .bs-link { color: #1A73E8; font-size: 26rpx; text-align: center; margin-top: 40rpx; }
    .bs-err { color: #D93025; font-size: 24rpx; margin-top: 24rpx; }
    .bs-btn { margin-top: 56rpx; height: 104rpx; line-height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; text-align: center; font-size: 32rpx; font-weight: bold; }
    .bs-btn.off { background: #A8C7FA; }
</style>
