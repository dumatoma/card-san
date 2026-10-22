<template>
    <!-- [カスタムエイド 2026-10-21] 配信設定を作り直し(見本2)。
         ・配信のタイミング「すぐに配信/日時を予約」、配信先「全員/絞り込む」を切り替えで
         ・絞り込みは同じ画面の中で:誕生月・性別(※複数選択可)/来店日(期間で選ぶ・しばらく来ていない)/これまでの来店回数
           来店日・来店回数は、実際の来店の記録(会員証の読み込み)で数える(サーバー 2026-10-21 CsMemberFilter)
         ・日時・期間はカレンダー(フリックで月を移動)と時刻を1つのシートで選ぶ
         ・「この条件で届く人」はサーバーの数え方で出す(配信のときと同じ条件)
         ・以前は、別の画面で選んだ絞り込みの条件が配信に送られていなかった(全員に届いていた)。条件を一緒に送るように直した -->
    <view class="ds">
        <u-navbar title="配信設定" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="ds-pg">
            <view class="ds-sum">
                <view class="ds-sum-b">
                    <view class="ds-sum-k">配信先</view>
                    <view class="ds-sum-v">{{ searchNumber }}<text class="ds-sum-t"> / {{ totalMember }}人</text></view>
                </view>
                <view class="ds-sum-b">
                    <view class="ds-sum-k">今月の配信数</view>
                    <view class="ds-sum-v" :class="{ over: messageUsedCount >= messageMaxCount }">{{ num(messageUsedCount) }}<text class="ds-sum-t"> / {{ num(messageMaxCount) }}通</text></view>
                </view>
            </view>

            <view class="ds-card">
                <view class="ds-h">配信のタイミング</view>
                <view class="ds-seg">
                    <view :class="{ on: !value }" @click="value = false">すぐに配信</view>
                    <view :class="{ on: value }" @click="value = true">日時を予約</view>
                </view>
                <view class="ds-note" v-if="!value">「配信する」を押すと、すぐに届きます。</view>
                <view class="ds-sel" v-else hover-class="ds-hover" @click="openDate">
                    <text>{{ dateLabel(todayDate) }} {{ nowTime }}</text><view class="ds-chev"></view>
                </view>
            </view>

            <view class="ds-card">
                <view class="ds-h">配信先</view>
                <view class="ds-seg">
                    <view :class="{ on: current == 0 }" @click="changeTab(0)">全員</view>
                    <view :class="{ on: current == 1 }" @click="changeTab(1)">絞り込む</view>
                </view>
                <view class="ds-note" v-if="current == 0">メンバー全員({{ totalMember }}人)に届きます。</view>

                <view class="ds-cond" v-else>
                    <view class="ds-fld">
                        <view class="ds-lbl">誕生月<text class="ds-lbl-s">※複数選択可</text></view>
                        <view class="ds-months">
                            <view v-for="mm in 12" :key="mm" class="ds-chip" :class="{ on: months.indexOf(mm) > -1 }" @click="toggle(months, mm)">{{ mm }}月</view>
                        </view>
                    </view>
                    <view class="ds-fld">
                        <view class="ds-lbl">性別<text class="ds-lbl-s">※複数選択可</text></view>
                        <view class="ds-chips">
                            <view v-for="x in sexList" :key="x.v" class="ds-chip" :class="{ on: sexes.indexOf(x.v) > -1 }" @click="toggle(sexes, x.v)">{{ x.t }}</view>
                        </view>
                    </view>
                    <view class="ds-fld">
                        <view class="ds-lbl">来店日</view>
                        <view class="ds-seg ds-seg3">
                            <view :class="{ on: comeMode == 0 }" @click="setCome(0)">指定なし</view>
                            <view :class="{ on: comeMode == 1 }" @click="setCome(1)">期間で選ぶ</view>
                            <view :class="{ on: comeMode == 2 }" @click="setCome(2)">しばらく<br />来ていない</view>
                        </view>
                        <block v-if="comeMode == 1">
                            <view class="ds-sel ds-sel-gap" hover-class="ds-hover" @click="openRange">
                                <text>{{ rangeLabel }}</text><view class="ds-chev"></view>
                            </view>
                            <view class="ds-note">この期間に1回でも来店したお客様に届きます(今日の来店も選べます)。</view>
                        </block>
                        <block v-if="comeMode == 2">
                            <view class="ds-text ds-sel-gap">最後の来店から</view>
                            <view class="ds-chips ds-chips-mid">
                                <view v-for="d in absentList" :key="d.v" class="ds-chip" :class="{ on: absentDays == d.v }" @click="absentDays = d.v; recount()">{{ d.t }}</view>
                            </view>
                            <view class="ds-text">以上たったお客様</view>
                            <view class="ds-note">例:{{ absentLabel }}を選ぶと、{{ absentCutoff }}より前が最後の来店のお客様に届きます。</view>
                        </block>
                    </view>
                    <view class="ds-fld">
                        <view class="ds-lbl">これまでの来店回数</view>
                        <view class="ds-chips">
                            <view v-for="(o, i) in visitList" :key="i" class="ds-chip" :class="{ on: visitOpt == i }" @click="visitOpt = i; recount()">{{ o.t }}</view>
                        </view>
                    </view>
                    <view class="ds-cnt"><text>この条件で届く人</text><text class="ds-cnt-n">{{ counting ? '…' : searchNumber + '人' }}</text></view>
                </view>
            </view>
        </view>

        <view class="ds-foot">
            <view class="ds-btn" :class="{ off: searchNumber == 0 }" hover-class="ds-hover" @click="toConfirm">{{ btnLabel }}</view>
        </view>

        <!-- 配信の日時(カレンダー+時刻) -->
        <u-popup :show="showDate" mode="bottom" round="24" @close="showDate = false">
            <view class="ds-sheet">
                <view class="ds-grip"></view>
                <view class="ds-shx" hover-class="ds-hover" @click="showDate = false">×</view><view class="ds-sh-t">配信の日時</view>
                <view class="ds-chips">
                    <view v-for="(q, i) in quickDays" :key="i" class="ds-chip" :class="{ on: dDate == q.d }" @click="dDate = q.d">{{ q.t }}</view>
                </view>
                <view class="ds-cal"><cs-calendar :key="'d' + calKey" mode="single" :value="dDate" :min="today" @change="dDate = $event"></cs-calendar></view>
                <view class="ds-time">
                    <view class="ds-time-h">時刻<text class="ds-time-s">上下にスライドして選びます(5分ごと)</text></view>
                    <picker-view class="ds-pv" :value="pvIndex" indicator-class="ds-pv-ind" @change="pvChange">
                        <picker-view-column>
                            <view class="ds-pv-i" v-for="h in 24" :key="h">{{ pad(h - 1) }}</view>
                        </picker-view-column>
                        <picker-view-column>
                            <view class="ds-pv-i" v-for="mi in 12" :key="mi">{{ pad((mi - 1) * 5) }}</view>
                        </picker-view-column>
                    </picker-view>
                </view>
                <view class="ds-res"><text class="ds-res-b">{{ dateLabel(dDate) }} {{ dTime }}</text> に配信します</view>
                <view class="ds-bbtn" hover-class="ds-hover" @click="confirmDate">この日時にする</view>
            </view>
        </u-popup>

        <!-- 来店日の期間 -->
        <u-popup :show="showRange" mode="bottom" round="24" @close="showRange = false">
            <view class="ds-sheet">
                <view class="ds-grip"></view>
                <view class="ds-shx" hover-class="ds-hover" @click="showRange = false">×</view><view class="ds-sh-t">来店日の期間</view>
                <view class="ds-chips">
                    <view v-for="(q, i) in quickRanges" :key="i" class="ds-chip" :class="{ on: rFrom == q.f && rTo == q.t2 }" @click="rFrom = q.f; rTo = q.t2">{{ q.t }}</view>
                </view>
                <view class="ds-cal"><cs-calendar :key="'r' + calKey" mode="range" :from="rFrom" :to="rTo" :max="today" @range="rFrom = $event.from; rTo = $event.to"></cs-calendar></view>
                <view class="ds-res">はじめの日 → おわりの日の順に押します。<view class="ds-res-b">{{ rFrom ? (mdLabel(rFrom) + ' 〜 ' + (rTo ? mdLabel(rTo) : 'おわりの日を押してください')) : 'はじめの日を押してください' }}</view></view>
                <view class="ds-bbtn" :class="{ off: !rFrom }" hover-class="ds-hover" @click="confirmRange">この期間にする</view>
            </view>
        </u-popup>

        <!-- [カスタムエイド 2026-10-21] 確認・お知らせを、ほかの画面と同じ見た目のダイアログに -->
        <cs-dialog :show="show6" :title="confirmTitle" :message="confirmMsg" :ok-text="confirmOk" :ok-color="query.source == 'edit' ? 'blue' : 'green'"
            @ok="saveData" @cancel="show6 = false"></cs-dialog>
        <cs-dialog :show="show7" icon="warn" title="今月の配信数を超えています"
            message="今月の無料配信数(3,000通/月)を超えています。追加料 ¥1,200 のお支払いで、さらに3,000通のメッセージを配信できます。"
            ok-text="お支払いへ進む" ok-color="blue" @ok="goPay" @cancel="show7 = false"></cs-dialog>
        <cs-dialog :show="showStripeInfo" icon="info" title="お支払いについて" ok-text="確認しました" cancel-text="" ok-color="blue"
            :message="'ウェブサイトでご契約し、クレジットカード決済をご利用の方は、このアプリからお支払いの手続きができません。\n\nCard-San管理ウェブサイトにログインし、［ご契約内容］の［配信数の追加購入］からお支払いをお願いします。お支払い後は、このアプリからそのまま配信できます。'"
            @ok="showStripeInfo = false"></cs-dialog>
        <cs-dialog :show="showPayFail" icon="warn" title="お支払いができませんでした" message="お支払いに失敗しました。もう一度お試しください。"
            ok-text="閉じる" cancel-text="" ok-color="blue" @ok="showPayFail = false"></cs-dialog>
        <!-- #ifdef H5 -->
        <cs-dialog :show="showAppInfo" icon="info" title="お支払いについて" ok-text="確認しました" cancel-text="" ok-color="blue"
            message="お支払いについては、お手数ではございますが、Card-San管理アプリからご契約時の決済方法でお支払いをお願いします。"
            @ok="showAppInfo = false"></cs-dialog>
        <!-- #endif -->
    </view>
</template>
<script>
    import { getPlatform } from '@/utils/platform.js' // [カスタムエイド 2026-10-18] 端末の種類はその場で判定(No.10)
    import csDialog from "@/components/csDialog/csDialog.vue" // [カスタムエイド 2026-10-21] 確認・お知らせ
    import csCalendar from "@/components/csCalendar/csCalendar.vue" // [カスタムエイド 2026-10-21]
    var googlePay = uni.requireNativePlugin('sn-googlepay5');
    import {
        addNewTiming,
        editTiming,
        getExtra,
        payExtra,
        sceneMember,
        buyextras,
        buyextra,
        getConfig,
        getShopInfo
    } from "@/api/index.js"

    const pad = (n) => (n < 10 ? '0' : '') + n
    const fmt = (dt) => dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate())
    const addDays = (s, n) => { const p = s.split('-'); const d = new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]) + n); return fmt(d) }
    const WEEK = ['日', '月', '火', '水', '木', '金', '土']

    export default {
        components: {
            csDialog,
            csCalendar
        },
        data() {
            return {
                pid: "",
                current: 0, // 0 = 全員 / 1 = 絞り込む
                value: false, // false = すぐに配信 / true = 日時を予約
                show6: false,
                show7: false,
                totalMember: 0,
                searchNumber: 0,
                todayDate: '',
                nowTime: "",
                messageUsedCount: 0,
                messageMaxCount: 3000,
                vipCardType: 0,
                showStripeInfo: false,
                showAppInfo: false,
                showPayFail: false,
                query: {},
                ids: ['premium_halfyear', 'premium_month', 'standard_halfyear', 'standard_month', 'light_halfyear',
                    'light_months', 'message'
                ],
                orid: "",
                // [カスタムエイド 2026-10-21] 絞り込み
                months: [],
                sexes: [],
                sexList: [{ v: 2, t: '女性' }, { v: 1, t: '男性' }, { v: 3, t: 'Xジェンダー' }],
                comeMode: 0, // 0 = 指定なし / 1 = 期間で選ぶ / 2 = しばらく来ていない
                comeFrom: '',
                comeTo: '',
                absentDays: 60,
                absentList: [{ v: 30, t: '30日' }, { v: 60, t: '60日' }, { v: 90, t: '90日' }, { v: 180, t: '180日' }, { v: 365, t: '1年' }],
                visitOpt: 0,
                visitList: [{ t: '指定なし' }, { t: '1回だけ(初めての方)', min: 1, max: 1 }, { t: '2回以上', min: 2 }, { t: '5回以上', min: 5 }, { t: '10回以上', min: 10 }],
                counting: false,
                countTimer: null,
                // 日時・期間のシート
                today: fmt(new Date()),
                showDate: false,
                dDate: '',
                dTime: '10:00',
                pvIndex: [10, 0],
                showRange: false,
                calKey: 0, // [カスタムエイド 2026-10-21] 開くたびに選んでいる月から見せる
                rFrom: '',
                rTo: ''
            }
        },
        computed: {
            quickDays() {
                return [{ t: '今日', d: this.today }, { t: '明日', d: addDays(this.today, 1) }, { t: 'あさって', d: addDays(this.today, 2) }]
            },
            quickRanges() {
                const t = this.today
                const p = t.split('-')
                const firstThis = p[0] + '-' + p[1] + '-01'
                const lastPrev = addDays(firstThis, -1)
                const firstPrev = lastPrev.slice(0, 8) + '01'
                return [
                    { t: '今日', f: t, t2: t },
                    { t: '昨日', f: addDays(t, -1), t2: addDays(t, -1) },
                    { t: 'この7日間', f: addDays(t, -6), t2: t },
                    { t: '今月', f: firstThis, t2: t },
                    { t: '先月', f: firstPrev, t2: lastPrev }
                ]
            },
            rangeLabel() {
                if (!this.comeFrom) return '期間を選んでください'
                return this.comeFrom == this.comeTo ? this.mdLabel(this.comeFrom) : (this.mdLabel(this.comeFrom) + ' 〜 ' + this.mdLabel(this.comeTo))
            },
            absentLabel() {
                const a = this.absentList.find((x) => x.v == this.absentDays)
                return a ? a.t : this.absentDays + '日'
            },
            absentCutoff() {
                return this.mdLabel(addDays(this.today, -this.absentDays))
            },
            btnLabel() {
                const n = '(' + this.searchNumber + '人)'
                // 予約の編集:保存する。すぐに配信に切り替えたときは、今すぐ配信
                if (this.query.source == 'edit') return this.value ? ('この内容で予約を保存する' + n) : ('今すぐ配信する' + n)
                return this.value ? (this.dateLabel(this.todayDate) + ' ' + this.nowTime + ' に予約する' + n) : ('配信する' + n)
            },
            // [カスタムエイド 2026-10-21] 確認のダイアログの文
            whenText() {
                return this.value ? (this.dateLabel(this.todayDate) + ' ' + this.nowTime + ' に') : '今すぐ'
            },
            confirmTitle() {
                if (this.query.source == 'edit') return this.value ? '予約の内容を保存' : 'メッセージを配信'
                return this.value ? '配信を予約' : 'メッセージを配信'
            },
            confirmMsg() {
                const who = this.searchNumber + '人へ届きます。'
                if (this.query.source == 'edit' && this.value) return this.whenText + '、' + who + '\nこの内容で予約を保存しますか?'
                return this.whenText + '、' + who + '\n' + (this.value ? 'この内容で予約しますか?' : '配信しますか?')
            },
            confirmOk() {
                if (this.query.source == 'edit') return this.value ? '保存する' : '配信する'
                return this.value ? '予約する' : '配信する'
            }
        },
        onLoad(options) {
            let that = this
            that.getAllmember()
            let timing = uni.getStorageSync("timing") || {}
            that.query = options
            if (options.source == "edit") {
                that.current = timing.receiver == 2 ? 1 : 0
                if (timing.order_time) {
                    let fullTime = timing.order_time.split(" ")
                    that.todayDate = fullTime[0]
                    that.nowTime = (fullTime[1] || '10:00').slice(0, 5)
                } else {
                    that.setDefaultTime()
                }
                that.value = timing.is_order == 1
                // [カスタムエイド 2026-10-21] 保存されている絞り込みを戻す
                that.months = String(timing.birthday_month || '').split(',').filter((v) => v).map(Number)
                that.sexes = String(timing.sex || '').split(',').filter((v) => v).map(Number)
                if (timing.come_from) {
                    that.comeMode = 1
                    that.comeFrom = timing.come_from
                    that.comeTo = timing.come_to || timing.come_from
                } else if (Number(timing.absent_days) > 0) {
                    that.comeMode = 2
                    that.absentDays = Number(timing.absent_days)
                }
                const vmin = Number(timing.visit_min) || 0
                const vmax = Number(timing.visit_max) || 0
                that.visitOpt = Math.max(0, that.visitList.findIndex((o, i) => i > 0 && (o.min || 0) == vmin && (o.max || 0) == vmax))
            } else {
                that.setDefaultTime()
            }
            googlePay.init({}, (e) => {});
            googlePay.isFeatureSupported((e) => {});
        },
        onShow() {
            let that = this
            getConfig().then((res) => {
                if (res.code == 200) {
                    let pt = getPlatform()
                    if (pt == 'ios') {
                        that.pid = res.data.config.vip_count.message.product_id[2]
                    } else {
                        that.pid = res.data.config.vip_count.message.product_id[3]
                    }
                }
            })
            getShopInfo().then((res) => {
                if (res.code == 200) {
                    that.messageUsedCount = res.data.message_count.message_count || 0
                    that.messageMaxCount = res.data.vip_message_config.count || 3000
                    that.vipCardType = (res.data.shop_info.vip && res.data.shop_info.vip.card_type) ? res.data.shop_info.vip.card_type : 0
                }
            })
            if (that.current == 1) that.recount()
        },
        methods: {
            pad: pad,
            num(v) {
                return String(Number(v) || 0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
            },
            dateLabel(s) {
                if (!s) return ''
                const p = s.split('-')
                const d = new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]))
                return Number(p[1]) + '月' + Number(p[2]) + '日(' + WEEK[d.getDay()] + ')'
            },
            mdLabel(s) {
                if (!s) return ''
                const p = s.split('-')
                return Number(p[1]) + '月' + Number(p[2]) + '日'
            },
            // 予約の初期値:次の 0 分か 30 分(5分きざみ)
            setDefaultTime() {
                const d = new Date(Date.now() + 60 * 60 * 1000)
                d.setMinutes(d.getMinutes() < 30 ? 30 : 60, 0, 0)
                this.todayDate = fmt(d)
                this.nowTime = pad(d.getHours()) + ':' + pad(d.getMinutes())
            },
            toggle(arr, v) {
                const i = arr.indexOf(v)
                if (i > -1) arr.splice(i, 1)
                else arr.push(v)
                arr.sort((a, b) => a - b)
                this.recount()
            },
            setCome(m) {
                this.comeMode = m
                if (m == 1 && !this.comeFrom) {
                    this.openRange()
                    return
                }
                this.recount()
            },
            // 送る条件(絞り込むときだけ)
            filters() {
                const f = { birthday_month: '', sex: '', shop_time: '', shop_time_desc: '', shop_count: '', shop_count_desc: '',
                    come_from: '', come_to: '', absent_days: 0, visit_min: 0, visit_max: 0 }
                if (this.current != 1) return f
                f.birthday_month = this.months.join(',')
                f.sex = this.sexes.join(',')
                if (this.comeMode == 1 && this.comeFrom) {
                    f.come_from = this.comeFrom
                    f.come_to = this.comeTo || this.comeFrom
                }
                if (this.comeMode == 2) f.absent_days = this.absentDays
                const o = this.visitList[this.visitOpt] || {}
                f.visit_min = o.min || 0
                f.visit_max = o.max || 0
                return f
            },
            // 「この条件で届く人」:サーバーで、配信のときと同じ条件で数える(保存はしない)
            recount() {
                if (this.current != 1) {
                    this.searchNumber = this.totalMember
                    return
                }
                clearTimeout(this.countTimer)
                this.counting = true
                this.countTimer = setTimeout(() => {
                    const t = uni.getStorageSync("timing") || {}
                    const data = Object.assign({}, t, this.filters(), {
                        receiver: 2, is_order: 0, is_comment_button: 0, request_type: 'count'
                    })
                    delete data.id
                    delete data._method
                    addNewTiming(data).then((res) => {
                        this.counting = false
                        if (res.code == 200) this.searchNumber = Number(res.data.count) || 0
                    }).catch(() => { this.counting = false })
                }, 350)
            },
            getAllmember() {
                let that = this
                sceneMember({ birthday_month: '', sex: '', shop_time: '', shop_time_desc: '', shop_count: '', shop_count_desc: '' }).then((res) => {
                    if (res.code == 200) {
                        that.totalMember = res.data.total
                        if (that.current == 0) that.searchNumber = res.data.total
                    }
                })
            },
            changeTab(e) {
                this.current = e
                this.recount()
            },
            openDate() {
                this.calKey++
                this.dDate = this.todayDate || this.today
                this.dTime = this.nowTime || '10:00'
                const p = this.dTime.split(':')
                this.pvIndex = [Number(p[0]) || 0, Math.round((Number(p[1]) || 0) / 5) % 12]
                this.showDate = true
            },
            pvChange(e) {
                const v = e.detail.value
                this.pvIndex = v
                this.dTime = pad(v[0]) + ':' + pad(v[1] * 5)
            },
            confirmDate() {
                const when = new Date(this.dDate.replace(/-/g, '/') + ' ' + this.dTime + ':00').getTime()
                if (when <= Date.now()) {
                    uni.showToast({ title: '今より後の日時を選んでください', icon: 'none' })
                    return
                }
                this.todayDate = this.dDate
                this.nowTime = this.dTime
                this.showDate = false
            },
            openRange() {
                this.calKey++
                this.rFrom = this.comeFrom
                this.rTo = this.comeTo
                this.showRange = true
            },
            confirmRange() {
                if (!this.rFrom) return
                this.comeFrom = this.rFrom
                this.comeTo = this.rTo || this.rFrom
                this.comeMode = 1
                this.showRange = false
                this.recount()
            },
            toConfirm() {
                if (this.searchNumber == 0) {
                    uni.showToast({ title: '届く人がいません。条件を見直してください', icon: 'none' })
                    return
                }
                if (this.current == 1 && this.comeMode == 1 && !this.comeFrom) {
                    uni.showToast({ title: '来店日の期間を選んでください', icon: 'none' })
                    return
                }
                if (this.value) {
                    const when = new Date(this.todayDate.replace(/-/g, '/') + ' ' + this.nowTime + ':00').getTime()
                    if (when <= Date.now()) {
                        uni.showToast({ title: '予約の日時が過ぎています。選び直してください', icon: 'none' })
                        return
                    }
                }
                this.show6 = true
            },
            // 追加購入の発注が拒否された場合（決済Attention：副管理者／Stripe契約者のアプリ内課金）
            extraFail(res) {
                uni.hideLoading()
                let at = res && res.data && res.data.attention
                if (at == 'web') {
                    this.showStripeInfo = true
                } else {
                    uni.showToast({ title: (res && res.message) || 'エラーが発生しました', icon: "none", duration: 2500 })
                }
            },
            // 获取支付通道
            getChannels() {
                // #ifdef APP-PLUS
                plus.payment.getChannels(res => {
                    let channel = res.find(i => i.id === 'appleiap')
                    this.iap = channel ? channel : null
                    this.restoreComplateRequest();
                    this.requestOrder()
                }, function(e) {
                    plus.nativeUI.alert("支払いチャネルの取得に失敗しました。しばらくしてからもう一度お試しください。", function() {}, "エラー");
                })
                // #endif
            },
            // 获取内购项目列表
            requestOrder() {
                uni.showLoading({
                    title: '購入処理実施中...'
                })
                // #ifdef APP-PLUS
                this.iap.requestOrder(
                    this.ids,
                    res => {
                        uni.hideLoading()
                        this.product_list = res;
                        this.pay();
                    },
                    (errormsg) => {
                        uni.hideLoading()
                        plus.nativeUI.alert("購入項目の取得に失敗しました。しばらくしてからもう一度お試しください。", function(e) {}, "エラー");

                    }
                )
                // #endif
            },
            // 调起支付
            pay() {
                let that = this
                uni.requestPayment({
                    provider: 'appleiap',
                    orderInfo: {
                        productid: that.pid
                    },
                    success: (e) => {
                        let da = {}
                        da.order_no = that.orid
                        da.pay_token = e.transactionReceipt
                        da.transaction_id = e.transactionIdentifier
                        buyextra(da).then((re) => {
                            uni.hideLoading()
                            if (re.code == 200) {
                                uni.showToast({
                                    title: re.message,
                                    duration: 2500,
                                    icon: "none"
                                })
                                that.sms = false
                            } else {
                                uni.showToast({
                                    title: re.message,
                                    icon: "none"
                                })
                            }
                        })
                    },
                    fail: (e) => {
                        this.showPayFail = true // [カスタムエイド 2026-10-21] ダイアログの見た目をそろえる
                    },
                    complete: () => {
                        this.loading = false;
                        this.restoreComplateRequest();
                    }
                })
            },
            // 获取已购的非消耗性商品和订阅商品
            restoreComplateRequest() {
                let that = this
                this.iap.restoreComplateRequest({
                    manualFinishTransaction: true
                }, results => {
                    // results 格式为数组存放恢复的IAP商品交易信息对象 IAPTransaction，通用需将返回的支付凭证传给后端进行二次认证  
                    for (let i = 0; i < results.length; i++) {
                        that.iap.finishTransaction(results[i], (success) => {
                        }, (fail) => {
                        });
                    }
                }, e => {
                    // 错误回调
                });
            },
            goPay() {
                let that = this
                that.show7 = false

                // 副管理者は支払い不可
                let admin = uni.getStorageSync("admin")
                if (admin.admin_type == 2) {
                    uni.showToast({ title: "実行する権限がありません", icon: "none" })
                    return
                }

                // #ifdef APP-PLUS
                // Stripe契約者はアプリ内課金不可
                if (that.vipCardType == 1) {
                    that.showStripeInfo = true
                    return
                }
                // Apple/Google IAP
                uni.showLoading({ title: "購入処理実施中..." })
                let plat = getPlatform()
                if (plat != 'ios') {
                    var pId = that.pid;
                    let orid = ''
                    let data = {}
                    data.type = 1
                    data.card_type = 3
                    buyextras(data).then((res) => {
                        if (res.code == 200) {
                            orid = res.data.order_no
                            googlePay.querySku({ inapp: [pId] }, (e) => {
                                if (e.code == 0) {
                                    googlePay.pay({ productId: pId }, (e) => {
                                        if (e.msg == 'success') {
                                            let da = {}
                                            da.order_no = orid
                                            da.pay_token = e.data[0].original.purchaseToken
                                            da.product_id = pId
                                            buyextra(da).then((re) => {
                                                uni.hideLoading()
                                                if (re.code == 200) {
                                                    uni.showToast({ title: re.message, duration: 2500, icon: "none" })
                                                } else {
                                                    uni.showToast({ title: re.message, icon: "none" })
                                                }
                                            })
                                        }
                                    })
                                } else {
                                    uni.showToast({ title: 'querySku fail,' + e })
                                }
                            })
                        } else {
                            that.extraFail(res)
                        }
                    })
                } else {
                    let data = {}
                    data.type = 1
                    data.card_type = 2
                    buyextras(data).then((res) => {
                        if (res.code == 200) {
                            that.getChannels()
                            that.orid = res.data.order_no
                        } else {
                            that.extraFail(res)
                        }
                    })
                }
                // #endif

                // #ifdef H5
                if (that.vipCardType == 1) {
                    // Stripe: 発注してStripe決済URLへリダイレクト
                    uni.showLoading({ title: "処理中..." })
                    let data = {}
                    data.type = 1
                    data.card_type = 1
                    buyextras(data).then((res) => {
                        if (res.code == 200) {
                            payExtra({ order_no: res.data.order_no }).then((payRes) => {
                                uni.hideLoading()
                                if (payRes.code == 200 && payRes.data.url) {
                                    window.location.href = payRes.data.url
                                } else {
                                    uni.showToast({ title: payRes.message || 'エラーが発生しました', icon: "none" })
                                }
                            })
                        } else {
                            that.extraFail(res)
                        }
                    })
                } else {
                    // アプリ決済契約者はアプリから支払い
                    that.showAppInfo = true
                }
                // #endif
            },
            saveData() {
                let that = this
                let data = Object.assign({}, uni.getStorageSync("timing") || {}, that.filters())
                data.is_order = that.value == true ? 1 : 0
                data.order_time = that.todayDate + " " + that.nowTime + ":00"
                data.receiver = that.current + 1
                delete data.request_type
                if (that.query.source == 'add') {
                    addNewTiming(data).then((res) => {
                        that.show6 = false
                        if (res.code == 200) {
                            uni.showToast({ title: res.message })
                            setTimeout(function() {
                                uni.navigateBack()
                            }, 1500)
                            uni.removeStorageSync("timing")
                        } else if (res.code == 4003) {
                            that.show7 = true
                        } else {
                            uni.showToast({ title: res.message, icon: "none" })
                        }
                    })
                } else if (that.query.source == 'edit') {
                    data._method = "put"
                    editTiming(data, that.query.id).then((res) => {
                        that.show6 = false
                        if (res.code == 200) {
                            uni.showToast({ title: res.message })
                            setTimeout(function() {
                                uni.navigateBack()
                            }, 1500)
                            uni.removeStorageSync("timing")
                        } else if (res.code == 4003) {
                            that.show7 = true
                        } else {
                            uni.showToast({ title: res.message, icon: "none" })
                        }
                    })
                }
            }
        }
    }
</script>
<style lang="scss">
    /* [カスタムエイド 2026-10-21] 配信設定(余白はほかの画面と同じ決まり) */
    page { background: #F2F2F7; }
    .ds-pg { padding: 40rpx 32rpx calc(240rpx + env(safe-area-inset-bottom)); }
    .ds-sum { display: flex; margin-bottom: 48rpx; }
    .ds-sum-b { flex: 1; background: #FFFFFF; border-radius: 32rpx; padding: 28rpx 32rpx; }
    .ds-sum-b + .ds-sum-b { margin-left: 24rpx; }
    .ds-sum-k { font-size: 24rpx; color: #6E6E73; line-height: 1.2; }
    .ds-sum-v { font-size: 44rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; margin-top: 8rpx; }
    .ds-sum-v.over { color: #D93025; }
    .ds-sum-t { font-size: 24rpx; color: #6E6E73; font-family: 'Hiragino Sans', sans-serif; }
    .ds-card { background: #FFFFFF; border-radius: 32rpx; padding: 40rpx 32rpx; margin-bottom: 32rpx; }
    .ds-h { font-size: 30rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.2; margin-bottom: 24rpx; }
    /* 切り替え:選んでいる方は薄い青の面+青い線+青い文字 */
    .ds-seg { display: flex; background: #F2F2F7; border-radius: 20rpx; padding: 6rpx; }
    .ds-seg view { flex: 1; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 28rpx; line-height: 1.3; min-height: 76rpx; border-radius: 16rpx; color: #6E6E73; border: 1px solid transparent; box-sizing: border-box; }
    .ds-seg .on { background: rgba(26, 115, 232, 0.10); border-color: #1A73E8; color: #1A73E8; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ds-seg3 view { font-size: 26rpx; padding: 8rpx 4rpx; }
    .ds-note { font-size: 24rpx; color: #8A8A8E; line-height: 1.6; margin-top: 20rpx; }
    .ds-sel { display: flex; align-items: center; justify-content: space-between; height: 96rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 0 28rpx; font-size: 30rpx; color: #1D1D1F; margin-top: 28rpx; }
    .ds-sel-gap { margin-top: 56rpx; }
    .ds-chev { width: 14rpx; height: 14rpx; border-right: 3rpx solid #C7C7CC; border-bottom: 3rpx solid #C7C7CC; transform: rotate(-45deg); }
    .ds-hover { opacity: 0.7; }
    .ds-cond { border-top: 1px solid #E5E5EA; margin-top: 40rpx; padding-top: 40rpx; }
    .ds-fld + .ds-fld { margin-top: 68rpx; }
    .ds-lbl { font-size: 26rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.2; margin-bottom: 16rpx; }
    .ds-lbl-s { font-size: 22rpx; color: #8A8A8E; font-family: 'Hiragino Sans', sans-serif; margin-left: 12rpx; }
    .ds-months { display: flex; flex-wrap: wrap; margin: 0 -10rpx; }
    .ds-months .ds-chip { width: calc(25% - 20rpx); margin: 0 10rpx 20rpx; text-align: center; padding: 20rpx 0; box-sizing: border-box; }
    .ds-chips { display: flex; flex-wrap: wrap; }
    .ds-chips .ds-chip { margin: 0 16rpx 16rpx 0; }
    .ds-chips-mid { margin: 20rpx 0 4rpx; }
    .ds-chip { font-size: 26rpx; color: #1D1D1F; border: 1px solid #D2D2D7; border-radius: 999rpx; padding: 14rpx 26rpx; background: #FFFFFF; }
    .ds-chip.on { border-color: #1A73E8; background: rgba(26, 115, 232, 0.10); color: #1A73E8; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ds-text { font-size: 30rpx; color: #1D1D1F; }
    .ds-cnt { display: flex; align-items: center; justify-content: space-between; background: #E8F0FE; border-radius: 24rpx; padding: 24rpx 28rpx; margin-top: 56rpx; font-size: 28rpx; color: #1A73E8; }
    .ds-cnt-n { font-size: 36rpx; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ds-foot { position: fixed; left: 0; right: 0; bottom: 0; z-index: 99; padding: 32rpx 32rpx calc(32rpx + env(safe-area-inset-bottom));
        background: rgba(242, 242, 247, 0.85); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-top: 1px solid rgba(0, 0, 0, 0.06); }
    .ds-btn { height: 104rpx; border-radius: 28rpx; background: #34C759; color: #FFFFFF; font-size: 30rpx; font-family: 'Hiragino Sans-W5', sans-serif; display: flex; align-items: center; justify-content: center; padding: 0 24rpx; text-align: center; }
    .ds-btn.off { background: #AEAEB2; }
    /* シート(日時・期間) */
    .ds-sheet { position: relative; padding: 24rpx 40rpx calc(48rpx + env(safe-area-inset-bottom)); }
    /* [カスタムエイド 2026-10-21] 閉じるボタン(右上の×) */
    .ds-shx { position: absolute; right: 24rpx; top: 20rpx; width: 64rpx; height: 64rpx; border-radius: 50%; background: #F2F2F7; color: #6E6E73; font-size: 36rpx; line-height: 64rpx; text-align: center; z-index: 2; }
    .ds-grip { width: 80rpx; height: 10rpx; border-radius: 6rpx; background: #D2D2D7; margin: 0 auto 28rpx; }
    .ds-sh-t { text-align: center; font-size: 34rpx; font-family: 'Hiragino Sans-W5', sans-serif; color: #1D1D1F; margin-bottom: 28rpx; }
    .ds-cal { margin-top: 16rpx; }
    .ds-time { border-top: 1px solid #E5E5EA; margin-top: 32rpx; padding-top: 32rpx; }
    .ds-time-h { font-size: 28rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ds-time-s { font-size: 22rpx; color: #8A8A8E; font-family: 'Hiragino Sans', sans-serif; margin-left: 12rpx; }
    .ds-pv { width: 360rpx; height: 240rpx; margin: 12rpx auto 0; }
    .ds-pv-i { height: 80rpx; line-height: 80rpx; text-align: center; font-size: 40rpx; color: #1D1D1F; }
    .ds-pv-ind { height: 80rpx; background: rgba(26, 115, 232, 0.08); border-radius: 16rpx; }
    .ds-res { background: #F2F2F7; border-radius: 24rpx; padding: 24rpx; text-align: center; font-size: 28rpx; color: #1D1D1F; margin-top: 56rpx; line-height: 1.6; }
    .ds-res-b { color: #1A73E8; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ds-bbtn { height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif; display: flex; align-items: center; justify-content: center; margin-top: 32rpx; }
    .ds-bbtn.off { background: #AEAEB2; }
    .infoOverlay {
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.6);
        position: fixed;
        top: 0;
        left: 0;
        z-index: 999;
        display: flex;
        align-items: flex-end;
    }

    .infoPanel {
        width: 100vw;
        height: 90vh;
        background: #fff;

        .infoPanelTitle {
            width: 100%;
            height: 112upx;
            background: #eaeaea;
            text-align: center;
            line-height: 112upx;
            font-size: 32rpx;
            font-weight: bold;
            color: #1D1D1F;
        }

        .infoPanelScroll {
            height: calc(90vh - 112upx);
            box-sizing: border-box;
            padding: 40upx 34upx;
        }

        .infoPanelWarn {
            width: 100%;
            box-sizing: border-box;
            padding: 32upx;
            font-size: 32rpx;
            font-weight: bold;
            color: #1D1D1F;
            background: rgba(230, 191, 24, 0.5);
        }

        .infoPanelBtn {
            width: 100%;
            height: 112upx;
            line-height: 112upx;
            background: #06C755;
            border-radius: 56rpx;
            text-align: center;
            color: #fff;
            font-size: 40rpx;
            font-weight: bold;
            margin-top: 100upx;
        }
    }

    .infoPanelNote {
        border: 1px solid #d2d2d7;
        padding: 30upx;
        font-size: 26upx;
        line-height: 1.7;
        color: #1d1d1f;
        background: #fff;
        margin: 30upx 0 60upx;
    }
</style>
