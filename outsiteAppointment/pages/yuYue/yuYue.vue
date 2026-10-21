<template>
    <view class="contain">
        <u-navbar :placeholder="true" :safeAreaInsetTop="true" autoBack :height="60" :border="true" bgColor="rgb(251,251,253)">
            <view class="u-nav-slot" slot="center">
                <view style="text-align: center;">予約日時選択</view>
                <view v-text="info.shop_name" style="text-align: center;"></view>
            </view>
        </u-navbar>
        
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
            <view class="staffItem" v-for="item,index in staff" v-if="staffId == item.id && item.is_appoint == 1">
                <view class="staffImage">
                    <image :src="item.image[0]" mode="aspectFill"></image>
                </view>
                <view class="staffDetail">
                    <view class="staffName" v-text="item.name"></view>
                    <view class="staffFu">
                        <text v-text="item.post"></text>
                        <text v-if="item.appoint_price > 0" style="margin-left: 100upx;margin-right: 20upx;">指名料</text>
                        <text v-if="item.appoint_price > 0" style="color: rgba(217, 48, 37, 0.60);" v-text="`￥${item.appoint_price}`"></text>
                    </view>
                    <view class="staffIntro" v-text="item.self_introduction"></view>
                </view>
            </view>
            <!-- 日历组件 -->
            <!-- [カスタムエイド 2026-10-18] 週の切り替え:日にちと予約枠を一緒に指について横に動かす(左の時間の列は固定)。
                 青い丸は今日だけ。日曜・祝日は赤。見出しは表示中の週(◯月◯日〜◯日) -->
            <u-sticky offsetTop="16">
            <view class="calendar">
                <view class="cs-wk">
                    <view class="cs-wk-head">
                        <view class="cs-wk-title">{{ weekTitle }}</view>
                        <view class="cs-wk-arrows">
                            <view class="cs-wk-arrow" :class="{ dis: weekOffset <= 0 }" @click="goWeek(-1)" aria-label="前の週">
                                <view class="cs-chev prev"></view>
                            </view>
                            <view class="cs-wk-arrow" @click="goWeek(1)" aria-label="次の週">
                                <view class="cs-chev next"></view>
                            </view>
                        </view>
                    </view>
                    <view class="cs-wk-labels">
                        <view class="cs-wk-label" v-for="(w, i) in weekLabels" :key="i" :class="i == 0 ? 'sun' : i == 6 ? 'sat' : ''">{{ w }}</view>
                    </view>
                    <view class="cs-pager cs-day-pager" id="csDayPager">
                        <view class="cs-track">
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
                <view class="cs-pager cs-grid-pager" id="csGridPager">
                    <view class="cs-track">
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
                </view>
                
                <view class="menuItem br" @click="showMenu = false">
                    <text>キャンセル</text>
                </view>
                <view style="height: 120upx;"></view>
            </view>
        </u-popup>
    </view> 
</template>

<script>
    import week from "@/components/week/week.vue"
    import { holidayName } from "@/utils/jpHoliday.js"
    import { setShopTitle } from "@/utils/pageTitle.js"
    import {
        getAppointmentList,
        getStaffList,
        getShopInfo,
        menuList,
        menuCate
    } from "@/api/index.js"
    export default {
        components: {
            week
        },
        data() {
            return {
                menuList:[],
                status: "week",
                showMenu:false,
                setting: false, //设置
                current: 0,
                no_appoint: 0,
                staffId: 0,
                showDetail: false, // 设置详情
                addNew: false, // 新增预约
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
                staff: [],
                selectDate: "",
                currentDate: "",
                orderInfo: uni.getStorageSync("orderItem"),
                info:{},
                appointprice:0,
                query:{},
                // [カスタムエイド 2026-10-18] 週のスライド
                weekLabels: ['日', '月', '火', '水', '木', '金', '土'],
                weekOffset: 0,      // 今週から何週先か(0 = 今週。今週より前には戻れない)
                weekCache: {},      // 週の初日(YYYY-MM-DD)ごとの予約データ
                weekGen: 0,         // 担当者を切り替えたら増やす(古い読み込み結果を捨てる)
                weekBusy: false
            }
        },
        computed: {
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
            }
        },
        mounted() {
            this.$nextTick(() => this.bindSwipe())
        },
        created() {
            
        },
        onLoad(options) {
            console.log(80*27)
            let that = this
            let query = options
            console.log(query)
            that.query = query
            that.getStaffData()
            that.getInfo(options.id)
            that.getList()
            // [カスタムエイド 2026-10-18] 今週・来週の予約データを読み込む(店舗情報の読み込み後)
            that.selectDate = that.formatDate(that.getWeekDate(that.getToday()))
           
        },
        onShow() {
            setShopTitle(this.info && this.info.shop_name ? this.info.shop_name : undefined)
            // let that = this
            // let query = that.$route.query
            // that.query = query
        },
        methods: {
            // [カスタムエイド 2026-10-19] 「変更」:メニューを選んだ画面に戻る(直接開いた時はメニュー一覧へ)
            changeMenu() {
                if (getCurrentPages().length > 1) {
                    uni.navigateBack()
                    return
                }
                uni.reLaunch({ url: '/pages/index/index' + (this.query && this.query.id ? '?id=' + this.query.id : '') })
            },
            getList() {
                let that = this
                console.log("id="+that.query.id)
                let data = {}
                data.page = 1
                data.collect = ""
                data.sid = that.query.id
                data.cid = 0
                menuCate(that.query.id).then((res) => {
                    console.log(1111111,res)
                    if (res.code == 200) {
                        // that.list = res.data.cates
                        menuList(data).then((result) => {
                            console.log(222,result)
                            let a1 = []
                            res.data.cates.forEach((val,index) => {
                                let obj = {}
                                obj.cate = val.name
                                obj.cid = val.id
                                obj.menus = []
                                result.data.menus.forEach((value,idx) => {
                                    value.checked = false
                                    if(value.cid == val.id){
                                        obj.menus.push(value)
                                    }
                                })
                                a1.push(obj)
                            })
                           that.menuList = a1
                          that.menuList.forEach((val,index) => {
                              val.menus.forEach((value,idx) => {
                                  if(value.id == that.query.mid){
                                      uni.setStorageSync("orderItem",value)
                                  }
                              })
                          })
                        })  
                    }
                })
            },
            getInfo(e){
                getShopInfo(e).then((res) => {
                    if(res.code == 200){
                        this.info = res.data.shop
                        if (res.data.shop.shop_name) {
                            uni.setStorageSync("shopName", res.data.shop.shop_name)
                            setShopTitle(res.data.shop.shop_name)
                        }
                        // console.log(111111,res.data.shop.day_business["202303"])
                        this.loadAroundWeeks(true)
                    }
                })
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
                            that.staff = res.data.admin3s
                        }else{
                            uni.showToast({
                                title:'予約可能な時間帯がありません',
                                icon:"none",
                                duration:3000
                            })
                        }
                        
                    }
                })
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
                data.smid = that.query.mid
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
                })
            },
            // 切换员工
            changeName(e, index,price) {
                let that = this
                that.current = index
                that.staffId = e
                that.appointprice = price
                // [カスタムエイド 2026-10-18] 担当者を切り替えたら、表示中の週と前後の週を読み込み直す
                that.weekGen++
                that.weekCache = {}
                that.loadAroundWeeks(true)
            },

            // 返回首页
            toIndex() {
                uni.navigateBack()
            },
            // ===== [カスタムエイド 2026-10-18] 週のスライド =====
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
                getAppointmentList({ date: key, sid: that.query.id, id: that.staffId, smid: that.query.mid }).then((res) => {
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
            tracks() {
                return document.querySelectorAll('.cs-track')
            },
            setTrackX(px, anim) {
                this.tracks().forEach((t) => {
                    t.style.transition = anim ? 'transform .28s cubic-bezier(.2,.7,.2,1)' : 'none'
                    t.style.transform = 'translate3d(calc(-33.3333% + ' + px + 'px),0,0)'
                })
            },
            goWeek(dir) {
                let that = this
                if (that.weekBusy) return
                if (dir < 0 && that.weekOffset <= 0) {
                    that.setTrackX(0, true)
                    return
                }
                that.weekBusy = true
                let el = document.getElementById('csGridPager') || document.getElementById('csDayPager')
                let w = el ? el.clientWidth : 300
                that.setTrackX(-dir * w, true)
                setTimeout(() => {
                    that.weekOffset += dir
                    that.selectDate = that.formatDate(that.weekStartOf(that.weekOffset).getTime())
                    that.$nextTick(() => {
                        that.setTrackX(0, false)
                        that.weekBusy = false
                        that.loadAroundWeeks(true)
                    })
                }, 290)
            },
            // 日にちの行・予約枠のどちらをなぞっても、両方を一緒に動かす(スマホは指、PCはマウスのドラッグとトラックパッド)
            bindSwipe() {
                let that = this
                let ids = ['csDayPager', 'csGridPager']
                ids.forEach((id) => {
                    let el = document.getElementById(id)
                    if (!el || el.__csBound) return
                    el.__csBound = true
                    let sx = 0, sy = 0, st = 0, axis = '', dx = 0
                    let limit = (v) => (that.weekOffset <= 0 && v > 0 ? v / 3 : v)
                    let finish = (v, fast) => {
                        let w = el.clientWidth
                        if (v < -w * 0.3 || (fast && v < 0)) that.goWeek(1)
                        else if (v > w * 0.3 || (fast && v > 0)) that.goWeek(-1)
                        else that.setTrackX(0, true)
                    }
                    el.addEventListener('touchstart', (e) => {
                        if (that.weekBusy) return
                        let p = e.touches[0]
                        sx = p.clientX; sy = p.clientY; st = Date.now(); axis = ''; dx = 0
                    }, { passive: true })
                    el.addEventListener('touchmove', (e) => {
                        if (that.weekBusy) return
                        let p = e.touches[0], mx = p.clientX - sx, my = p.clientY - sy
                        if (!axis) {
                            if (Math.abs(mx) < 6 && Math.abs(my) < 6) return
                            axis = Math.abs(mx) > Math.abs(my) ? 'x' : 'y'
                        }
                        if (axis != 'x') return
                        e.preventDefault()
                        dx = limit(mx)
                        that.setTrackX(dx, false)
                    }, { passive: false })
                    el.addEventListener('touchend', () => {
                        if (axis != 'x' || that.weekBusy) return
                        finish(dx, Math.abs(dx) > 30 && Date.now() - st < 250)
                    })
                    // マウスでつかんでドラッグ
                    let down = false, mdx = 0, msx = 0, moved = false
                    el.addEventListener('pointerdown', (e) => {
                        if (e.pointerType != 'mouse' || e.button != 0 || that.weekBusy) return
                        down = true; moved = false; msx = e.clientX; mdx = 0
                    })
                    window.addEventListener('pointermove', (e) => {
                        if (!down) return
                        mdx = e.clientX - msx
                        if (!moved && Math.abs(mdx) < 6) return
                        moved = true
                        that.setTrackX(limit(mdx), false)
                    })
                    window.addEventListener('pointerup', () => {
                        if (!down) return
                        down = false
                        if (moved) finish(limit(mdx), false)
                    })
                    // ドラッグしたあとのクリックで予約枠が押されないようにする
                    el.addEventListener('click', (e) => {
                        if (moved) { e.stopPropagation(); e.preventDefault(); moved = false }
                    }, true)
                    // トラックパッドの2本指スワイプ
                    let wx = 0, wt = null
                    el.addEventListener('wheel', (e) => {
                        if (Math.abs(e.deltaX) <= Math.abs(e.deltaY) || that.weekBusy) return
                        e.preventDefault()
                        wx -= e.deltaX
                        if (that.weekOffset <= 0 && wx > 0) wx = Math.min(wx, 60)
                        that.setTrackX(wx, false)
                        clearTimeout(wt)
                        wt = setTimeout(() => {
                            let v = wx
                            wx = 0
                            let w = el.clientWidth
                            if (v < -w * 0.25) that.goWeek(1)
                            else if (v > w * 0.25) that.goWeek(-1)
                            else that.setTrackX(0, true)
                        }, 140)
                    }, { passive: false })
                })
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
        .staffDetail {
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

    /* [カスタムエイド 2026-10-18] 週のスライド(日にち・予約枠) */
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
</style>
