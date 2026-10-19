<template>
    <view class="contain">
        <!-- <u-navbar title="予約日時選択" :safeAreaInsetTop="false" :autoBack="true" bgColor="rgb(251,251,253)" :placeholder="false"> -->
            <!-- <view class="u-nav-slot" slot="left">
                <image src="../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" @click="toIndex"></image>
            </view> -->
        <!-- </u-navbar> -->
        <view class="top">
            <!-- [カスタムエイド 2026-10-19] 選んだメニューを小さくまとめる:左に写真(ある時だけ)、名前は省略せず全部、
                 価格と所要時間は1行、右に「変更」(メニューを選んだ画面に戻る) -->
            <view class="topContent">
                <image v-if="orderInfo.image && orderInfo.image.length" :src="orderInfo.image[0]" mode="aspectFill" class="to-img"></image>
                <view class="to-body">
                    <view class="to1" v-text="orderInfo.title"></view>
                    <view class="to-meta"><text class="to2" v-text="`${orderInfo.price}`"></text><text class="to3" v-text="`　・　所要時間 ${orderInfo.order_time}分`"></text></view>
                </view>
                <view class="to-change" @click="changeMenu">変更</view>
            </view>
        </view>
        <view class="tip">
            ご希望の来店日時を選択してください
        </view>
        <view class="content">
            <view class="askForName" v-if="info.is_appoint == 1">
                <view class="name_item" v-if="no_appoint == 1" :class="current == 0? 'active' : ''" @click="changeName(0,0,0)">指名なし</view>
                <view class="name_item" :class="current == index + 1? 'active' : ''"
                    @click="changeName(item.id,index+1,item.appoint_price)" v-for="(item,index) in staff" v-text="item.name"></view>
            </view>
            <!-- 员工信息1 -->
            <view class="staffItem" v-for="item,index in staff" v-if="staffId == item.id">
                <view class="staffImage">
                    <image :src="item.image[0]" mode="aspectFill"></image>
                </view>
                <view class="staffDetail">
                    <view class="staffName" v-text="item.name"></view>
                    <view class="staffFu">
                        <text v-if="item.post" v-text="item.post"></text>
                        <text v-if="item.is_appoint_price" :style="item.post? 'margin-left: 100upx;margin-right: 20upx;' : ''">指名料</text>
                        <text v-if="item.is_appoint_price" style="color: rgba(217, 48, 37, 0.60);margin-left: 15rpx;">￥</text>
                        <text v-if="item.is_appoint_price" style="color: rgba(217, 48, 37, 0.60);" v-text="formatNumberWithCommas(item.appoint_price)"></text>
                    </view>
                    <view class="staffIntro" v-text="item.self_introduction"></view>
                </view>
            </view>
            <!-- 日历组件 -->
            <!-- [カスタムエイド 2026-10-19] 予約WEBページと同じ:日にちと予約枠を一緒に指について横に動かして週を切り替える
                 (左の時間の列は固定)。青い丸は今日だけ。日曜・祝日は赤。見出しは表示中の週(◯月◯日〜◯日)。
                 担当者の選択がある店舗は、カレンダーを担当者の選択(高さ43px)のすぐ下に止める -->
            <u-sticky :key="'cal' + (info.is_appoint == 1 ? 1 : 0)" :offsetTop="info.is_appoint == 1 ? 43 : 0">
            <view class="calendar">
                <view class="cs-wk">
                    <view class="cs-wk-head">
                        <view class="cs-wk-title">{{ weekTitle }}</view>
                        <view class="cs-wk-arrows">
                            <view class="cs-wk-arrow" :class="{ dis: weekOffset <= 0 }" @click="goWeek(-1)">
                                <view class="cs-chev prev"></view>
                            </view>
                            <view class="cs-wk-arrow" @click="goWeek(1)">
                                <view class="cs-chev next"></view>
                            </view>
                        </view>
                    </view>
                    <view class="cs-wk-labels">
                        <view class="cs-wk-label" v-for="(w, i) in weekLabels" :key="i" :class="i == 0 ? 'sun' : i == 6 ? 'sat' : ''">{{ w }}</view>
                    </view>
                    <view class="cs-pager cs-day-pager" @touchstart="swStart" @touchmove="swMove" @touchend="swEnd" @touchcancel="swEnd">
                        <view class="cs-track" :style="trackStyle">
                            <view class="cs-pane" v-for="p in panes" :key="p.key">
                                <view class="cs-wk-days" v-if="!p.empty">
                                    <view class="cs-wk-day" v-for="d in p.days" :key="d.key" :class="d.cls">
                                        <text class="cs-wk-num">{{ d.day }}</text>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            </u-sticky>
            <!-- 周 -->
            <view v-if="status == 'week'" class="cs-wbody">
                <view class="cs-timecol" :style="`height:${80 * (times.length + 1)}upx`">
                    <view class="time">
                        <view class="timeItem" v-for="(item, index) in times" :key="index">
                            <view class="timeName" v-text="item.time"></view>
                            <view class="point"></view>
                        </view>
                    </view>
                </view>
                <view class="cs-pager cs-grid-pager" @touchstart="swStart" @touchmove="swMove" @touchend="swEnd" @touchcancel="swEnd">
                    <view class="cs-track" :style="trackStyle">
                        <view class="cs-pane" v-for="p in panes" :key="p.key">
                            <week v-if="!p.empty" :info="info" :times="times" :hideTime="true" @addNew="addNewAppointment" :weekData="p.data"></week>
                        </view>
                    </view>
                </view>
            </view>
            <view class="btn1" hover-class="btnhover" @click="showMenu = true">
                お問い合わせ
            </view>
            <view style="height: 1upx;"></view>
        </view>
        <!-- 弹出层 -->
        <u-popup :show="showMenu" @close="showMenu = false" bgColor="transparent">
            <view class="menuContent">
                <view class="menuConfirm">
                    <view class="menuItem" style="border-bottom: 1upx solid  rgba(17,17,17,0.5);" @click="sendPhone">
                        <image style="width: 56upx;height: 56upx;" src="../../static/svg/phoneSvg.svg" alt=""></image>
                        <text>電話で問い合わせ</text>
                    </view>
                    <view class="menuItem" @click="toChat">
                        <image style="width: 60upx;height: 51upx;" src="../../static/svg/messageSvg.svg" alt=""></image>
                        <text>チャットで問い合わせ</text>
                    </view>
                </view>
                
                <view class="menuItem br" @click="showMenu = false">
                    <text>キャンセル</text>
                </view>
            </view>
        </u-popup>
    </view> 
</template>

<script>
    import week from "@/components/week/week.vue"
    import { holidayName } from "@/utils/jpHoliday.js" // [カスタムエイド 2026-10-19] 祝日を赤くする
    import {
        getAppointmentList,
        getStaffList,
        getShopInfo
    } from "@/api/index.js"
    export default {
        components: {
            week
        },
        data() {
            return {
                status: "week",
                showMenu:false,
                setting: false, //设置
                current: 0,
                staffId: 0,
                no_appoint: 0,
                showDetail: false, // 设置详情
                addNew: false, // 新増预约
                info: {},
                times: [
                    {
                        time: "09:00"
                    },
                    {
                        time: "09:30"
                    },
                    {
                        time: "10:00"
                    },
                    {
                        time: "10:30"
                    },
                    {
                        time: "11:00"
                    },
                    {
                        time: "11:30"
                    },
                    {
                        time: "12:00"
                    },
                    {
                        time: "12:30"
                    },
                    {
                        time: "13:00"
                    },
                    {
                        time: "13:30"
                    },
                    {
                        time: "14:00"
                    },
                    {
                        time: "14:30"
                    },
                    {
                        time: "15:00"
                    },
                    {
                        time: "15:30"
                    },
                    {
                        time: "16:00"
                    },
                    {
                        time: "16:30"
                    },
                    {
                        time: "17:00"
                    },
                    {
                        time: "17:30"
                    },
                    {
                        time: "18:00"
                    },
                    {
                        time: "18:30"
                    },
                    {
                        time: "19:00"
                    },
                    {
                        time: "19:30"
                    },
                    {
                        time: "20:00"
                    },
                    {
                        time: "20:30"
                    },
                    {
                        time: "21:00"
                    },
                    {
                        time: "21:30"
                    }
                ],
                array: [],
                manager: [],
                managers: [],
                weekData: [],
                // [カスタムエイド 2026-10-19] 週のスライド(予約WEBページと同じ)
                weekLabels: ['日', '月', '火', '水', '木', '金', '土'],
                weekOffset: 0,      // 今週から何週先か(0 = 今週。今週より前には戻れない)
                weekCache: {},      // 週の初日(YYYY-MM-DD)ごとの予約データ
                weekGen: 0,         // 担当者を切り替えたら増やす(古い読み込み結果を捨てる)
                weekBusy: false,
                swDx: 0,            // 指について動かしている量(px)
                swAnim: false,
                sw: { x: 0, y: 0, t: 0, axis: '' },
                staff: [],
                selectDate: "",
                currentDate: "",
                orderInfo: uni.getStorageSync("orderItem"),
                info:{},
                appointprice:0
            }
        },
        computed: {
            // [カスタムエイド 2026-10-19] 週のスライド
            baseSunday() {
                let d = new Date()
                d.setHours(0, 0, 0, 0)
                d.setDate(d.getDate() - d.getDay())
                return d
            },
            panes() {
                let list = []
                for (let i = -1; i <= 1; i++) {
                    let o = this.weekOffset + i
                    if (o < 0) {
                        list.push({ key: 'empty', empty: true })
                        continue
                    }
                    let start = this.weekStartOf(o)
                    let key = this.formatDate(start.getTime())
                    list.push({ key: key, empty: false, data: this.weekCache[key] || [], days: this.weekDays(start) })
                }
                return list
            },
            weekTitle() {
                let s = this.weekStartOf(this.weekOffset)
                let e = new Date(s)
                e.setDate(e.getDate() + 6)
                return s.getFullYear() + '年 ' + (s.getMonth() + 1) + '月' + s.getDate() + '日〜' + (e.getMonth() != s.getMonth() ? (e.getMonth() + 1) + '月' : '') + e.getDate() + '日'
            },
            trackStyle() {
                return {
                    transform: 'translate3d(calc(-33.3333% + ' + this.swDx + 'px),0,0)',
                    transition: this.swAnim ? 'transform .28s cubic-bezier(.2,.7,.2,1)' : 'none'
                }
            }
        },
        created() {
        },
        onLoad(options) {
            let that = this
            that.getInfo(options.id)
            let query = options
            that.query = query
            // [カスタムエイド 2026-10-19] 今週・来週の予約データは、店舗情報の読み込み後に読み込む(getInfo)
            that.selectDate = that.formatDate(that.getWeekDate(that.getToday()))
            that.getStaffData()
        },
        onPullDownRefresh() {
            let that = this
            // [カスタムエイド 2026-10-19] 読み込み済みの週を捨てて、店舗情報から読み込み直す
            that.weekGen++
            that.weekCache = {}
            that.getInfo(that.query.id)
            that.getStaffData()
            setTimeout(() => {
                uni.stopPullDownRefresh()
            },500)
        },
        onShow() {
            // let that = this
            // let query = that.$route.query
            // that.query = query
        },
        methods: {
            // [カスタムエイド 2026-10-19] 「変更」:メニューを選んだ画面に戻る
            changeMenu() {
                uni.navigateBack()
            },
            formatNumberWithCommas(number) {
                if (!number && number !== 0) return '';
                return String(parseInt(number)).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            },
            getInfo(e){
                getShopInfo(e).then((res) => {
                    if(res.code == 200){
                        this.info = res.data.shop
                        // console.log(111111,res.data.shop.day_business["202303"])
                        this.loadAroundWeeks(true) // [カスタムエイド 2026-10-19]
                    }
                }).catch(() => {})
            },
            toChat(){
                let that = this 
                that.showMenu = false
              uni.navigateTo({
                  url: "../message/chat?sid=" + that.query.id + "&name=" + that.info.shop_name
              })  
            },
            sendPhone(){
                let that = this
                that.showMenu = false
               uni.makePhoneCall({
                   phoneNumber:that.info.phone
               }) 
            },
            getStaffData() {
                let that = this
                let orderitem = uni.getStorageSync("orderItem")
                let data = {}
                data.id = orderitem.id
                data.sid = that.query.id
                getStaffList(data).then((res) => {
                    if (res.code == 200) {
                        that.no_appoint = res.data.no_appoint || 0
                        if(res.data.admin3s.length > 0){
                            let t = []
                            res.data.admin3s.forEach((v,i) => {
                                if(v.is_appoint == 1){
                                    t.push(v)
                                }
                            })

                            that.staff = t
                        }else{
                            uni.showToast({
                                title:"予約可能な時間帯がありません",
                                icon:'none',
                                duration:3000
                            })
                            setTimeout(() => {
                                uni.navigateBack()
                            },3000)
                        }

                    }
                }).catch(() => {})
            },
            addNewAppointment(e) {
                let that = this
                uni.setStorageSync("orderTime",e)
                uni.navigateTo({
                    url:"../AppointmentConfirmation/AppointmentConfirmation?staff="+that.staffId+"&sid="+that.query.id+"&mid="+that.query.mid+"&price="+that.appointprice
                })
            },
            // 获取本周日期
            getWeekDate(dms) {
                let that = this
                let today = new Date(dms);
                //构建当前日期,格式：2022-08-22 00:00:00
                let year = today.getFullYear(); //本年 
                let month = today.getMonth() + 1; //本月
                let day = today.getDate(); //本日
                let newDate = new Date(year + "/" + month + "/" + day + " 00:00:00"); //年月日拼接
                let nowTime = newDate.getTime(); //当前的时间戳
                let weekDay = newDate.getDay(); //当前星期 0.1.2.3.4.5.6 【0 = 周日】
                let oneDayTime = 24 * 60 * 60 * 1000; //一天的总ms
                let sundayTime = (7 - weekDay) * oneDayTime + nowTime; //本周日的时间戳
                let lastSundayTime = nowTime - (weekDay + 0) * oneDayTime; //上周日的时间戳
                return lastSundayTime
            },
            // 格式化日期
            formatDate(ms) {
                const date = new Date(ms)
                const Y = date.getFullYear()
                const M = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                const D = date.getDate()
                return `${Y}-${M}-${D}`
            },
            // 切换日期
            change(e) {
                let that = this
                let tempdate = e.fulldate
                let temparr = tempdate.split("-")
                let str = ''
                for(let i = 0; i < temparr.length;i++){
                    if(i == temparr.length -1){
                        str += temparr[i]
                    }else{
                        str += temparr[i] + "/"
                    }
                }
                if (that.status == "week") {
                    that.getWeekList(that.formatDate(that.getWeekDate(str + " 00:00:00")))
                    that.selectDate = that.formatDate(that.getWeekDate(str + " 00:00:00"))
                } else {
                    that.getList(str)
                }
            },
            toSet() {
                let that = this
                let array = that.manager
                that.managers = array
            },
            // 获取今日日期
            getToday() {
                let that = this
                let date = new Date()
                let year = date.getFullYear()
                let month = date.getMonth() + 1
                let day = date.getDate()
                month = (month > 9) ? month : ("0" + month);
                day = (day < 10) ? ("0" + day) : day;
                let today = year + "-" + month + "-" + day
                return today
            },
            // 颜色转换
            hexToRgba(hex) {
                if (!hex) hex = '#ededed';
                let rgb = 'rgb(' + parseInt('0x' + hex.slice(1, 3)) + ',' +
                    parseInt('0x' + hex.slice(3, 5)) + ',' +
                    parseInt('0x' + hex.slice(5, 7)) + ')'
                return rgb
            },
            getHour(s1, s2) {
                var reDate = /\d{4}-\d{1,2}-\d{1,2} /;
                s1 = new Date((reDate.test(s1) ? s1 : '2018-1-1 ' + s1).replace(/-/g, '/'));
                s2 = new Date((reDate.test(s2) ? s2 : '2018-1-1 ' + s2).replace(/-/g, '/'));
                var ms = s2.getTime() - s1.getTime();
                if (ms < 0) return 0;
                return Math.floor(ms / 1000 / 60 / 60); //小时
            },
            // 获取每周预约列表
            getWeekList(date) {
                let that = this
                let data = {}
                data.date = date
                data.sid = that.query.id
                data.id = that.staffId
                data.smid = that.orderInfo.id
                uni.showLoading({
                    title:"読み込み中",
                    mask:true
                })
                getAppointmentList(data).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        let array = res.data.orders
                        array.forEach((val, index) => {
                            val.times = that.times
                        })
                        that.weekData = array
                    }
                }).catch(() => { uni.hideLoading() })
            },
            // 切换员工
            changeName(e, index,price) {
                let that = this
                that.current = index
                that.staffId = e
                that.appointprice = price
                // [カスタムエイド 2026-10-19] 担当者を切り替えたら、表示中の週と前後の週を読み込み直す
                that.weekGen++
                that.weekCache = {}
                that.loadAroundWeeks(true)
            },

            // 返回首页
            toIndex() {
                uni.navigateBack()
            },
            // ===== [カスタムエイド 2026-10-19] 週のスライド(予約WEBページと同じ動き) =====
            weekStartOf(o) {
                let d = new Date(this.baseSunday)
                d.setDate(d.getDate() + o * 7)
                return d
            },
            weekDays(start) {
                let today = new Date()
                today.setHours(0, 0, 0, 0)
                let days = []
                for (let i = 0; i < 7; i++) {
                    let d = new Date(start)
                    d.setDate(d.getDate() + i)
                    let cls = []
                    if (d.getTime() == today.getTime()) cls.push('today')
                    if (i == 0 || holidayName(d)) cls.push('sun')
                    else if (i == 6) cls.push('sat')
                    days.push({ key: i, day: d.getDate(), cls: cls.join(' ') })
                }
                return days
            },
            // 1週分の予約データを読み込む(読み込み済みなら何もしない)
            loadWeek(o, showLoading) {
                let that = this
                if (o < 0) return
                let key = that.formatDate(that.weekStartOf(o).getTime())
                if (that.weekCache[key]) return
                that.$set(that.weekCache, key, [])
                let gen = that.weekGen
                if (showLoading) uni.showLoading({ title: "読み込み中", mask: true })
                getAppointmentList({ date: key, sid: that.query.id, id: that.staffId, smid: that.orderInfo.id }).then((res) => {
                    if (showLoading) uni.hideLoading()
                    if (gen != that.weekGen) return
                    if (res.code == 200) {
                        let array = res.data.orders
                        array.forEach((val) => { val.times = that.times })
                        that.$set(that.weekCache, key, array)
                    } else {
                        that.$delete(that.weekCache, key)
                    }
                }).catch(() => {
                    if (showLoading) uni.hideLoading()
                    if (gen == that.weekGen) that.$delete(that.weekCache, key)
                })
            },
            loadAroundWeeks(showLoading) {
                this.loadWeek(this.weekOffset, showLoading)
                this.loadWeek(this.weekOffset + 1, false)
                this.loadWeek(this.weekOffset - 1, false)
            },
            pagerWidth() {
                return uni.upx2px(560)
            },
            goWeek(dir) {
                let that = this
                if (that.weekBusy) return
                if (dir < 0 && that.weekOffset <= 0) {
                    that.swAnim = true
                    that.swDx = 0
                    return
                }
                that.weekBusy = true
                that.swAnim = true
                that.swDx = -dir * that.pagerWidth()
                setTimeout(() => {
                    that.weekOffset += dir
                    that.selectDate = that.formatDate(that.weekStartOf(that.weekOffset).getTime())
                    that.swAnim = false
                    that.swDx = 0
                    that.$nextTick(() => {
                        that.weekBusy = false
                        that.loadAroundWeeks(true)
                    })
                }, 290)
            },
            // 日にちの行・予約枠のどちらをなぞっても、両方を一緒に動かす
            // (アプリでは document が使えないので、指の動きはテンプレートの @touchstart などで受け取る。縦のスクロールは touch-action: pan-y で妨げない)
            swPoint(e) {
                let t = (e.touches && e.touches[0]) || (e.changedTouches && e.changedTouches[0]) || {}
                return { x: t.clientX != null ? t.clientX : t.pageX, y: t.clientY != null ? t.clientY : t.pageY }
            },
            swStart(e) {
                if (this.weekBusy) return
                let p = this.swPoint(e)
                this.sw = { x: p.x, y: p.y, t: Date.now(), axis: '' }
                this.swAnim = false
            },
            swMove(e) {
                if (this.weekBusy || !this.sw.t) return
                let p = this.swPoint(e)
                let mx = p.x - this.sw.x, my = p.y - this.sw.y
                if (!this.sw.axis) {
                    if (Math.abs(mx) < 6 && Math.abs(my) < 6) return
                    this.sw.axis = Math.abs(mx) > Math.abs(my) ? 'x' : 'y'
                }
                if (this.sw.axis != 'x') return
                // 今週より前には戻れないので、右へ引っぱったときは少しだけ動かす
                this.swDx = this.weekOffset <= 0 && mx > 0 ? mx / 3 : mx
            },
            swEnd() {
                if (this.weekBusy || this.sw.axis != 'x') {
                    this.sw = { x: 0, y: 0, t: 0, axis: '' }
                    return
                }
                let v = this.swDx
                let fast = Math.abs(v) > 30 && Date.now() - this.sw.t < 250
                let w = this.pagerWidth()
                this.sw = { x: 0, y: 0, t: 0, axis: '' }
                if (v < -w * 0.3 || (fast && v < 0)) this.goWeek(1)
                else if ((v > w * 0.3 || (fast && v > 0)) && this.weekOffset > 0) this.goWeek(-1)
                else {
                    this.swAnim = true
                    this.swDx = 0
                }
            },
        }
    }
</script>

<style lang="scss">
    .tip{
        font-size: 32upx;
        font-family: Noto Sans Kannada-Bold, Noto Sans Kannada;
        font-weight: bold;
        color: #1D1D1F;
        text-align: center;
        background-color: #f5f5f7;
    }
    .menuContent{
        background: transparent;
        box-sizing: border-box;
        padding: 0 30upx;
        
        .menuConfirm{
            border-radius: 20upx;
            overflow: hidden;
        }
        
        .menuItem{
            width: 100%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30upx 0;
            font-size: 40upx;
            font-family: Noto Sans Kannada-Regular, Noto Sans Kannada;
            font-weight: 400;
            color: #1A73E8;
            
            image{
                margin-right: 32upx;
            }
        }
        
        .br{
            color: #D93025;
            margin-top: 20upx;
            border-radius: 20upx;
        }
    }
    .btnhover{
        opacity:0.6 !important
    }
    .btn1{
        width: 638upx;
        // height: 96upx !important;
        background: #1A73E8;
        border-radius: 20upx;
        opacity: 1;
        margin: 148upx auto;
        font-size: 40upx;
        font-family: Noto Sans Kannada-Bold, Noto Sans Kannada;
        font-weight: bold;
        color: #FFFFFF;
        text-align: center;
        padding: 28upx 0;
        // line-height: 96upx !important;
    }
    .staffItem {
        display: flex;
        box-sizing: border-box;
        padding: 20upx 16upx;
        background: #fff;
        align-items: flex-start;
        .staffDetail {
            flex: 1;
            min-width: 0;
            .staffName {
                font-size: 32upx;
                font-family: Hiragino Sans-W6, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }
    
            .staffFu {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: rgba(29, 29, 31, 0.9);
                margin: 20upx 0;
                display: flex;
                flex-direction: row;
                align-items: center;
                flex-wrap: wrap;
            }
    
            .staffIntro {
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #86868B;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
            }
        }
    
    
    
        .staffImage {
            width: 150upx;
            height: 200upx;
            flex-shrink: 0;
            margin-right: 20upx;
            flex-shrink: 0;
            image {
                width: 100%;
                height: 100%;
            }
        }
    }
    .calendar{
        border-bottom: 2upx solid #D2D2D7;
        margin-bottom: 10upx;
        background: #fff;
    }
    .askForName {
        display: flex;
        align-items: center;
        width: 100%;
        overflow-x: scroll;
        box-sizing: border-box;
        padding: 40upx 0 40upx 40upx;
        background: #f5f5f7;

        .name_item {
            padding: 8upx 16upx;
            border: 2upx solid #AAAAAA;
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #AAAAAA;
            flex-shrink: 0;
            border-radius: 8upx;
            margin-right: 20upx;
        }

        .active {
            background: rgba(26, 115, 232, 0.1);
            color: #007AFF;
            border: 2upx solid #1A73E8;
        }
    }


    // [カスタムエイド 2026-10-19] 選んだメニューの枠を小さく(余白・行間を詰める)
    .top {
        box-sizing: border-box;
        padding: 24upx 32upx;
        background: #F5F5F7;

        .topContent {
            background-color: #fff;
            border: 2upx solid #E3E3E8;
            border-radius: 24upx;
            box-sizing: border-box;
            padding: 20upx 24upx;
            display: flex;
            align-items: flex-start;

            .to-img {
                width: 116upx;
                height: 116upx;
                border-radius: 16upx;
                flex-shrink: 0;
                margin-right: 24upx;
                background: #F5F5F7;
            }

            .to-body {
                flex: 1;
                min-width: 0;
            }

            .to1{
                font-size: 30upx;
                font-weight: 600;
                line-height: 1.4;
                color: #1D1D1F;
                margin-bottom: 6upx;
            }

            .to-meta {
                font-size: 26upx;
                line-height: 1.4;
                color: #6E6E73;
            }

            .to2{
                font-size: 28upx;
                font-weight: 600;
                color: #1D1D1F;
            }

            .to3{
                color: #6E6E73;
            }

            .to-change {
                flex-shrink: 0;
                margin-left: 16upx;
                padding: 8upx 4upx 20upx 16upx;
                font-size: 28upx;
                color: #0A66D8;
            }
        }
    }

    .content {
        background: #fff;

        .tabs {
            width: 100%;
            box-sizing: border-box;
            padding: 40upx 26upx;

            .tab {
                display: flex;
                align-items: center;
                background: #EAEAEA;
                border-radius: 16upx;

                .tab_item {
                    box-sizing: border-box;
                    padding: 20upx 0;
                    border-radius: 16upx;
                    flex: 1;
                    text-align: center;
                    font-size: 32upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                }

                .active {
                    background: #fff;
                    box-shadow: 0px 6upx 16upx 2upx rgba(0, 0, 0, 0.16);
                    color: #1A73E8 !important;
                }
            }
        }
    }
    /* [カスタムエイド 2026-10-19] 週のスライド(日にち・予約枠) */
    .cs-wk { width: 100%; background: #fff; }
    .cs-wk-head { display: flex; align-items: center; justify-content: space-between; height: 100upx; box-sizing: border-box; padding: 0 30upx 0 60upx; border-bottom: 1px solid #f5f5f5; }
    .cs-wk-title { font-size: 34upx; color: #1D1D1F; font-family: Hiragino Sans-W6, Hiragino Sans; }
    .cs-wk-arrows { display: flex; align-items: center; }
    .cs-wk-arrow { width: 80upx; height: 80upx; display: flex; align-items: center; justify-content: center; cursor: pointer; }
    .cs-wk-arrow.dis { opacity: .3; }
    .cs-wk-labels { display: flex; margin-left: 158upx; width: 560upx; padding-top: 10upx; }
    .cs-wk-label { width: 80upx; text-align: center; font-size: 30upx; height: 60upx; line-height: 60upx; color: #1D1D1F; }
    .cs-pager { overflow: hidden; touch-action: pan-y; width: 560upx; }
    .cs-day-pager { margin-left: 158upx; padding-bottom: 12upx; }
    .cs-track { display: flex; width: 300%; transform: translate3d(-33.3333%, 0, 0); will-change: transform; }
    .cs-pane { width: 33.3333%; flex-shrink: 0; min-height: 1px; }
    .cs-wk-days { display: flex; }
    .cs-wk-day { width: 80upx; height: 80upx; display: flex; align-items: center; justify-content: center; }
    .cs-wk-num { width: 72upx; height: 72upx; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32upx; color: #1D1D1F; }
    .cs-wk-label.sun, .cs-wk-day.sun .cs-wk-num { color: #D93025; }
    .cs-wk-label.sat, .cs-wk-day.sat .cs-wk-num { color: #1A73E8; }
    .cs-wk-day.today .cs-wk-num { background: #1A73E8; color: #fff !important; }
    .cs-wbody { display: flex; width: 100%; }
    .cs-chev { width: 22upx; height: 22upx; border-top: 4upx solid #1A73E8; border-left: 4upx solid #1A73E8; }
    .cs-chev.prev { transform: rotate(-45deg); margin-left: 8upx; }
    .cs-chev.next { transform: rotate(135deg); margin-right: 8upx; }
    /* 時間の列(予約枠の部品 week.vue と同じ見た目) */
    .cs-timecol { width: 158upx; flex-shrink: 0; padding-top: 8upx; box-sizing: border-box; }
    .cs-timecol .time { position: relative; top: -40upx; }
    .cs-timecol .timeItem { height: 80upx; line-height: 80upx; width: 158upx; font-size: 24upx; font-family: Hiragino Sans-W3, Hiragino Sans; color: #1D1D1F; display: flex; align-items: center; justify-content: space-between; text-align: center; }
    .cs-timecol .timeName { flex: 1; text-align: center; }
    .cs-timecol .point { width: 20upx; height: 2upx; background-color: #EAEAEA; }
    .cs-grid-pager { flex-shrink: 0; }

    /* [カスタムエイド 2026-10-19] 担当者の選択(指名なし…)をナビゲーションバーのすぐ下(4px下)に止め、そのすぐ下にカレンダーを止める
       (予約WEBページと同じ。行の高さは 上4px + ボタン34px + 下5px = 43px) */
    .askForName {
        position: sticky;
        top: 0;
        z-index: 971; // カレンダー(u-sticky 970)より上
        height: 43px;
        padding: 4px 0 5px 20px !important;
        align-items: center;
    }
    .askForName .name_item {
        height: 34px;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        flex-shrink: 0;
    }
</style>
