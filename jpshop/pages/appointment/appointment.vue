<template>
    <view class="contain">
        <u-navbar title="予約" :safeAreaInsetTop="true" :border="true" bgColor="rgb(251,251,253)" :placeholder="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" @click="toIndex"></image>
            </view>
            <view class="u-nav-slot" slot="right">
                <image src="../../static/svg/gd2.svg" style="width: 60upx;height: 60upx;"
                    @click="showSetting"></image>
            </view>
        </u-navbar>
        <view class="top">
            <view class="topContent">
                <view class="content_item" @click="toNotice">
                    <view class="item_left">
                        予約通知
                    </view>
                    <view class="item_right bk" v-text="totalNum"></view>
                </view>
                <view class="content_item">
                    <view class="item_left">
                        今日の予約
                    </view>
                    <view class="item_right" v-text="todayNum"></view>
                </view>
            </view>
        </view>
        <view class="content">
            <view class="tabs">
                <view class="tab">
                    <view class="tab_item" @click="changeTab('day')" :class="status == 'day'? 'active' : '' ">
                        日
                    </view>
                    <view class="tab_item" @click="changeTab('week')" :class="status == 'week'? 'active' : '' ">
                        週
                    </view>
                </view>
            </view>
            <view class="askForName" v-if="status == 'week'">
                <view class="name_item" v-if="no_appoint == 1" :class="current == 0? 'active' : ''" @click="changeName(0,0)">指名なし</view>
                <view class="name_item" :class="current == index + 1? 'active' : ''"
                    @click="changeName(item.id,index+1)" v-for="(item,index) in staff" v-text="item.name"></view>
            </view>
            <!-- [カスタムエイド 2026-10-19] 日付の帯(予約WEBページと同じ作り。日・週どちらもこの帯を使う)
                 週:日にちと予約枠を一緒に指について横に動かす/青い丸は今日だけ/見出しは表示中の週
                 日:予約枠を左右にスワイプで次の日・前の日/青い丸は選んでいる日/担当者名の行を帯の下で止める
                 日曜・祝日は赤、土曜は青(utils/jpHoliday.js)。修正一覧 管理 No.21・22・5 -->
            <u-sticky :offset-top="totalDing" customNavHeight="44">
            <view class="calendar" :class="status == 'day' ? 'cs-cal-d' : ''" style="background: #ffffff;">
                <view class="cs-wk">
                    <view class="cs-wk-head">
                        <view class="cs-wk-title">{{ headTitle }}</view>
                        <view class="cs-wk-arrows">
                            <view class="cs-wk-arrow" @click="onArrow(-1)"><view class="cs-chev prev"></view></view>
                            <view class="cs-wk-arrow" @click="onArrow(1)"><view class="cs-chev next"></view></view>
                        </view>
                    </view>
                    <view class="cs-wk-labels" :class="status == 'day' ? 'cs-d' : ''">
                        <view class="cs-wk-label" v-for="(w, i) in weekLabels" :key="i" :class="i == 0 ? 'sun' : i == 6 ? 'sat' : ''">{{ w }}</view>
                    </view>
                    <view class="cs-pager cs-day-pager" :class="status == 'day' ? 'cs-d' : ''"
                        @touchstart="headStart" @touchmove="headMove" @touchend="headEnd" @touchcancel="headEnd">
                        <view class="cs-track" :style="trackStyle(headDx, headAnim)">
                            <view class="cs-pane" v-for="p in headPanes" :key="p.key">
                                <view class="cs-wk-days">
                                    <view class="cs-wk-day" v-for="d in p.days" :key="d.key" :class="d.cls" @click="pickDay(d.date)">
                                        <text class="cs-wk-num">{{ d.day }}</text>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <!-- 日:担当者名の行(日付の帯の3px下で止まる。予約枠と一緒に横に動く) -->
            <view class="cs-names" v-if="status == 'day' && centerManagers.length"
                @touchstart="dayStart" @touchmove="dayMove" @touchend="dayEnd" @touchcancel="dayEnd">
                <view class="cs-names-clip"><view class="cs-names-in" :style="{transform: 'translate3d(' + staffX + 'px,0,0)'}">
                    <view class="cs-name" v-for="(m, i) in centerManagers" :key="m.adid" :style="{width: colWidth + 'upx'}">
                        <view class="cs-name-t">{{ m.name }}</view>
                        <view class="cs-name-bar" :style="{background: m.color}"></view>
                    </view>
                </view></view>
            </view>
            </u-sticky>
            <!-- 日 -->
            <view v-if="status == 'day'" class="cs-dbody">
                <view class="cs-timecol cs-d">
                    <view class="time">
                        <view class="timeItem" v-for="(item, index) in times" :key="index">
                            <view class="timeName" v-text="item.time"></view>
                            <view class="point"></view>
                        </view>
                        <view class="timeItem">
                            <view class="timeName">22:00</view>
                            <view class="point"></view>
                        </view>
                    </view>
                </view>
                <view class="cs-pager cs-grid-pager" @touchstart="dayStart" @touchmove="dayMove" @touchend="dayEnd" @touchcancel="dayEnd">
                    <view class="cs-track" :style="trackStyle(gridDx, gridAnim)">
                        <view class="cs-pane" v-for="p in dayPanes" :key="p.key">
                            <progres v-if="p.data && p.cal && shopTime" :manager="p.data" :tdate="p.date" :times="times" :hideTime="true" :hideNames="true"
                                :nowTime="shopTime" :calendar="p.cal"
                                :offsetX="p.center ? staffX : 0" :quiet="!p.center" @addNew="addNewAppointment" @checkdetail="godetail"></progres>
                            <view v-else class="cs-loading">読み込み中…</view>
                        </view>
                    </view>
                </view>
            </view>
            <!-- 周 -->
            <view v-if="status == 'week'" class="cs-wbody">
                <view class="cs-timecol">
                    <view class="time">
                        <view class="timeItem" v-for="(item, index) in times" :key="index">
                            <view class="timeName" v-text="item.time"></view>
                            <view class="point"></view>
                        </view>
                        <view class="timeItem">
                            <view class="timeName">22:00</view>
                            <view class="point"></view>
                        </view>
                    </view>
                </view>
                <view class="cs-pager cs-grid-pager" @touchstart="weekStart" @touchmove="weekMove" @touchend="weekEnd" @touchcancel="weekEnd">
                    <view class="cs-track" :style="trackStyle(gridDx, gridAnim)">
                        <view class="cs-pane" v-for="p in weekPanes" :key="p.key">
                            <week v-if="p.data && p.info && shopTime" :times="times" :hideTime="true" @addNew="addNewAppointment" :info="p.info" :weekData="p.data" :nowTime="shopTime"></week>
                            <view v-else class="cs-loading">読み込み中…</view>
                        </view>
                    </view>
                </view>
            </view>
            <!-- 设置 -->
            <setting v-if="setting" @close="closeSetting" @goDetail="checkDetail" @toOut="toout"></setting>
            <!-- 设置详情 -->
            <settingDetail v-if="showDetail" :info="info" @closeDetail="closeDetail"></settingDetail>
            <!-- 新增预约 -->
            <newAppoint @cancel="changeAdd" v-if="addNew" :adid="adid" :aptype="aptype" :time="ctime" :ddate="dDate"></newAppoint>
            <view style="height: 160upx;">
                
            </view>
        </view>
        <permission :show="showpermission" @close='showpermission = false'></permission>
    </view>
</template>

<script>
    import { holidayName } from "@/utils/jpHoliday.js" // [カスタムエイド 2026-10-19] 日本の祝日(計算で出す)
    import progres from "@/components/progress/progress.vue"
    import week from "@/components/week/week.vue"
    import setting from "@/components/appointment/add.vue"
    import settingDetail from "@/components/appointment/addDetail.vue"
    import newAppoint from "@/components/appointment/new.vue"
    import permission from "@/components/permission/permission.vue"
    import {
        getAppointmentList,
        getCalendar,
        getStaffList,
        getAppointNoticeList,
        getConfig,
        getAdminDetail
    } from "@/api/index.js"
    export default {
        components: {
            progres,
            week,
            setting,
            settingDetail,
            newAppoint,
            permission
        },
        data() {
            return {
                showpermission:false,
                status: "day",
                setting: false, //设置
                current: 0,
                staffId: 0,
                showDetail: false, // 设置详情
                addNew: false, // 新增预约
                totalDing:0,
                info: {},
                cinfo:{},
                ctime:"",
                times: [
                    {
                        time: "09:00",
                        val:9
                    },
                    {
                        time: "09:30",
                        val:9.5
                    },
                    {
                        time: "10:00",
                        val:10
                    },
                    {
                        time: "10:30",
                        val:10.5
                    },
                    {
                        time: "11:00",
                        val:11
                    },
                    {
                        time: "11:30",
                        val:11.5
                    },
                    {
                        time: "12:00",
                        val:12
                    },
                    {
                        time: "12:30",
                        val:12.5
                    },
                    {
                        time: "13:00",
                        val:13
                    },
                    {
                        time: "13:30",
                        val:13.5
                    },
                    {
                        time: "14:00",
                        val:14
                    },
                    {
                        time: "14:30",
                        val:14.5
                    },
                    {
                        time: "15:00",
                        val:15
                    },
                    {
                        time: "15:30",
                        val:15.5
                    },
                    {
                        time: "16:00",
                        val:16
                    },
                    {
                        time: "16:30",
                        val:16.5
                    },
                    {
                        time: "17:00",
                        val:17
                    },
                    {
                        time: "17:30",
                        val:17.5
                    },
                    {
                        time: "18:00",
                        val:18
                    },
                    {
                        time: "18:30",
                        val:18.5
                    },
                    {
                        time: "19:00",
                        val:19
                    },
                    {
                        time: "19:30",
                        val:19.5
                    },
                    {
                        time: "20:00",
                        val:20
                    },
                    {
                        time: "20:30",
                        val:20.5
                    },
                    {
                        time: "21:00",
                        val:21
                    },
                    {
                        time: "21:30",
                        val:21.5
                    }
                ],
                array: [],
                manager: [],
                managers: [],
                weekData: [],
                staff: [],
                selectDate: "",
                currentDate: "",
                totalNum: 0,
                todayNum: 0,
                dDate:"",
                reloadtime:'',
                no_appoint:0,
                adid:"",
                aptype:"",
                adname:"",
                // [カスタムエイド 2026-10-19] 日付の帯・スライド
                weekLabels: ['日', '月', '火', '水', '木', '金', '土'],
                dayCache: {},   // 日付(YYYY-MM-DD)ごとの担当者と予約
                // [カスタムエイド 2026-10-19] 通信の回数を減らすため、店舗の今の日時と月ごとの営業時間は1回だけ読み込んで覚えておく
                shopTime: "",   // 店舗の今の日時(config.time_date)
                calCache: {},   // 月(YYYY-MM)ごとの営業時間(読み込み中は Promise)
                dayCal: {},     // 日付ごとの、その月の営業時間
                weekCache: {},  // 週の初日ごとの予約枠
                weekInfo: {},   // 週の初日ごとの営業時間
                weekGen: 0,     // 担当者を切り替えたら増やす(古い読み込み結果を捨てる)
                headDx: 0, headAnim: false,
                gridDx: 0, gridAnim: false,
                staffX: 0,      // 予約(日):担当者が多いときの横の位置(px)
                busy: false,
                tc: null        // 指の動きの記録
            }
        },
        computed: {
            // ===== [カスタムエイド 2026-10-19] 日付の帯・スライド =====
            headPanes() {
                let base = this.sundayOf(this.dDate)
                let list = []
                for (let i = -1; i <= 1; i++) {
                    let s = this.addDays(base, i * 7)
                    list.push({ key: this.ymd(s), days: this.weekDays(s) })
                }
                return list
            },
            headTitle() {
                let d = this.parseDay(this.dDate)
                if (this.status == 'week') {
                    let s = this.sundayOf(this.dDate)
                    let e = this.addDays(s, 6)
                    return s.getFullYear() + '年 ' + (s.getMonth() + 1) + '月' + s.getDate() + '日〜' + (e.getMonth() != s.getMonth() ? (e.getMonth() + 1) + '月' : '') + e.getDate() + '日'
                }
                return d.getFullYear() + '年 ' + (d.getMonth() + 1) + '月' + d.getDate() + '日'
            },
            dayPanes() {
                let d = this.parseDay(this.dDate)
                let list = []
                for (let i = -1; i <= 1; i++) {
                    let key = this.ymd(this.addDays(d, i))
                    list.push({ key: key, date: key, center: i == 0, data: this.dayCache[key], cal: this.dayCal[key] })
                }
                return list
            },
            weekPanes() {
                let base = this.sundayOf(this.dDate)
                let list = []
                for (let i = -1; i <= 1; i++) {
                    let key = this.weekKey(this.addDays(base, i * 7))
                    list.push({ key: key, data: this.weekCache[key], info: this.weekInfo[key] })
                }
                return list
            },
            centerManagers() {
                return this.dayCache[this.dDate] || []
            },
            // 担当者の列の幅(upx):4人以下は幅いっぱい、5人以上は140upx(progress.vue と同じ)
            colWidth() {
                let n = this.centerManagers.length || 1
                return n <= 4 ? 560 / n : 140
            },
            staffMax() {
                let n = this.centerManagers.length
                return n <= 4 ? 0 : uni.upx2px(n * 140 - 560)
            }
        },
        onPullDownRefresh() {
           uni.reLaunch({
               url:"../appointment/appointment"
           })
        },
        created() {
            let that = this
            
            // that.getWeekDate()
            that.dDate = that.getToday()
            that.loadShopTime() // [カスタムエイド 2026-10-19]
            that.getCalendar()
        },
        onShow() {
            let that = this
            // that.dDate = that.getToday()
            that.getLists()
           let ding = uni.getSystemInfoSync().statusBarHeight
           that.totalDing = ding
        },
        methods: {
            getCalendar(){
                let that = this
                let data = {}
                data.date = this.getToday()
                data.type = "day"
                getCalendar(data).then((res) => {
                    uni.hideLoading()
                    if(res.code == 200){
                        if(res.data.week_business.length == 0){
                            uni.showToast({
                                title:"営業時間を設定してください",
                                icon:"none",
                                duration:2500
                            })
                            setTimeout(() => {
                                uni.switchTab({
                                    url:"/pages/index/index"
                                })
                            },3000)
                        }else{
                          that.cinfo = res.data
                          if (!that.calCache[data.date.slice(0, 7)]) that.calCache[data.date.slice(0, 7)] = res.data // [カスタムエイド 2026-10-19] 同じ月を2回読まない
                          that.loadAroundDays() // [カスタムエイド 2026-10-19] 前後の日も読み込む
                          that.getStaffData()
                        }
                    }
                }).catch(() => { uni.hideLoading() })
            },
            toout(){
              uni.navigateTo({
                  url:"../outAppoint/outAppoint"
              })  
            },
            reload(){
                let that = this
                that.getCalendar()
                setTimeout(() => {
                    that.getStaffData()
                    that.getLists()
                    that.refreshAll() // [カスタムエイド 2026-10-19]
                },800)
                
                
            },
            // 切换日、周
            changeTab(e) {
                let that = this
                that.status = e
                // [カスタムエイド 2026-10-19] 表示中の日・週と前後を読み込む
                that.headDx = 0; that.gridDx = 0; that.staffX = 0
                if (e != "week") {
                    that.loadAroundDays()
                } else {
                    that.selectDate = that.weekKey(that.sundayOf(that.dDate))
                    that.loadAroundWeeks()
                }
            },
            godetail(e){ 
              uni.navigateTo({
                  url:"notice/detail/edit?id="+e
              })
            },
            // 获取顶部的数量
            getLists(){
                let that = this 
              getAppointNoticeList(1).then((res) => {
                  if(res.code == 200){
                      that.totalNum = res.data.unread_count
                      that.todayNum = res.data.today_count
                  }else{
                      uni.showToast({
                          title:res.message,
                          icon:"none"
                      })
                  }
              }).catch(() => {})
            },
            getStaffData() {
                let that = this
                let data = {}
                that.staff = []
                data.id = 0
                data.start_time = ""
                data.end_time = ""
                getStaffList(data).then((res) => {
                    if (res.code == 200) {
                        if(res.data.admins.length > 0){
                            let data = []
                            res.data.admins.forEach((v,i) => {
                                if(v.is_appoint == 1){
                                    data.push(v)
                                }
                            })
                            that.staff = data
                            that.no_appoint = res.data.no_appoint
                            if(res.data.no_appoint == 1){
                                that.staffId = 0
                                that.current = 0
                            }else{
                                that.staffId = data[0].id
                                that.current = 1
                            }
                        }else{
                           uni.showToast({
                               title:"スタッフを追加してください",
                               icon:"none"
                           })
                        }

                    }else{
                      uni.showToast({
                          title:res.message,
                          icon:"none"
                      })
                  }
                }).catch(() => {})
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
                that.reloadtime = e.fulldate
                if (that.status == "week") {
                    that.getWeekList(that.formatDate(that.getWeekDate(e.fulldate + " 00:00:00")))
                    that.selectDate = that.formatDate(that.getWeekDate(e.fulldate + " 00:00:00"))
                    let data = {}
                    data.date = e.fulldate
                    data.type = "day"
                    getCalendar(data).then((res) => {
                        if(res.code == 200){
                            that.cinfo = res.data
                        }
                    }).catch(() => {})
                } else {
                    that.getList(e.fulldate)
                }
                that.dDate = e.fulldate
            },
            toSet() {
                let that = this
                let array = that.manager
                that.managers = array
            },
            // 获取今日日期
            getToday() {
                let temdate = uni.getStorageSync("today")
                let date
                if (temdate) {
                    // iOS doesn't parse "YYYY-MM-DD HH:mm:ss" — extract date part and use / separator
                    let dateStr = String(temdate).split(' ')[0].replace(/-/g, '/')
                    date = new Date(dateStr)
                }
                if (!date || isNaN(date.getTime())) {
                    date = new Date()
                }
                let year = date.getFullYear()
                let month = date.getMonth() + 1
                let day = date.getDate()
                month = (month > 9) ? month : ("0" + month)
                day = (day < 10) ? ("0" + day) : day
                return year + "-" + month + "-" + day
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
            // 关闭新增预约
            changeAdd(e) {
                let that = this
                if (e == "close") {
                    that.addNew = false
                }

                if (e == "refresh") {
                    that.addNew = false
                    that.refreshAll() // [カスタムエイド 2026-10-19]
                }
            },
            // 获取每日预约列表
            getList(date) {
                let that = this
                let data = {}
                data.date = date
                data.id = 0
                data.type = "day"
                getAppointmentList(data).then((res) => {
                    if (res.code == 200) {
                        let tem = []
                        let timeArr = JSON.parse(JSON.stringify(that.times))
                        res.data.admins.forEach((val, index) => {
                            if(val.is_appoint == 1){
                                let obj = {}
                                obj.name = val.name
                                obj.adid = val.id
                                obj.times = that.times
                                obj.total = that.times.length
                                obj.date = date
                                if (val.color) {
                                    obj.color = that.hexToRgba(val.color)
                                } else {
                                    obj.color = "rgb(23, 84, 145)"
                                }
                                obj.events = []
                                val.orders.forEach((value, idx) => {
                                    let t = {}
                                    t.start = value.start_time
                                    t.end = value.end_time
                                    t.duration = value.m / 60
                                    t.name = value.name
                                    t.event = value.cate_name
                                    t.id = value.id
                                    t.appoint_said = value.appoint_said
                                    obj.events.push(t)
                                })
                                tem.push(obj)
                            }

                        })
                        that.manager = tem
                        that.toSet()
                    }else{
                      uni.showToast({
                          title:res.message,
                          icon:"none"
                      })
                  }
                }).catch(() => {})
            },
            // 获取每周预约列表
            getWeekList(date) {
                uni.showLoading({
                    title:"読み込み中",
                    mask:true
                })
                let that = this
                let data = {}
                data.date = date
                data.type = "order_week"
                data.id = that.staffId
                getAppointmentList(data).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        let array = res.data.data
                        array.forEach((val, index) => {
                            val.times = that.times
                        })
                        that.weekData = array
                    }else{
                      uni.showToast({
                          title:res.message,
                          icon:"none"
                      })
                  }
                }).catch(() => { uni.hideLoading() })
            },
            // 切换员工
            changeName(e, index) {
                let that = this
                that.current = index
                that.staffId = e
                // [カスタムエイド 2026-10-19] 担当者を切り替えたら、表示中の週と前後の週を読み込み直す
                that.weekGen++
                that.weekCache = {}
                that.loadAroundWeeks()
            },
            // ===== [カスタムエイド 2026-10-19] 日付の帯・スライド(修正一覧 管理 No.21・22・5) =====
            parseDay(s) {
                let a = String(s).split(' ')[0].split('-')
                return new Date(a[0] * 1, a[1] * 1 - 1, a[2] * 1)
            },
            ymd(d) {
                let m = d.getMonth() + 1, day = d.getDate()
                return d.getFullYear() + '-' + (m < 10 ? '0' + m : m) + '-' + (day < 10 ? '0' + day : day)
            },
            addDays(d, n) {
                let x = new Date(d.getFullYear(), d.getMonth(), d.getDate())
                x.setDate(x.getDate() + n)
                return x
            },
            sundayOf(s) {
                let d = this.parseDay(s)
                return this.addDays(d, -d.getDay())
            },
            // 週のデータの日付の書き方(今までの読み込みと同じ:月は2けた、日は1けたのまま)
            weekKey(d) {
                return this.formatDate(d.getTime())
            },
            weekDays(start) {
                let mark = this.status == 'week' ? this.getToday() : this.dDate // 週:今日だけ/日:選んでいる日
                let days = []
                for (let i = 0; i < 7; i++) {
                    let d = this.addDays(start, i)
                    let key = this.ymd(d)
                    let cls = []
                    if (key == mark) cls.push('today')
                    if (i == 0 || holidayName(d)) cls.push('sun')
                    else if (i == 6) cls.push('sat')
                    days.push({ key: i, day: d.getDate(), date: key, cls: cls.join(' ') })
                }
                return days
            },
            trackStyle(dx, anim) {
                return {
                    transform: 'translate3d(calc(-33.3333% + ' + dx + 'px),0,0)',
                    transition: anim ? 'transform .28s cubic-bezier(.2,.7,.2,1)' : 'none'
                }
            },
            pagerWidth() {
                return uni.upx2px(560)
            },
            // 1日分の担当者と予約を読み込む(読み込み済みなら何もしない)
            loadDay(date) {
                let that = this
                if (that.dayCache[date]) return
                that.$set(that.dayCache, date, null)
                // [カスタムエイド 2026-10-19] 予約の一覧と、その月の営業時間を同時に読み込む(1つずつ待たない)
                Promise.all([getAppointmentList({ date: date, id: 0, type: "day" }), that.loadMonth(date)]).then(([res, cal]) => {
                    if (res.code == 200 && cal) {
                        let tem = []
                        res.data.admins.forEach((val) => {
                            if (val.is_appoint == 1) {
                                let obj = {}
                                obj.name = val.name
                                obj.adid = val.id
                                obj.times = that.times
                                obj.total = that.times.length
                                obj.date = date
                                obj.color = val.color ? that.hexToRgba(val.color) : "rgb(23, 84, 145)"
                                obj.events = []
                                val.orders.forEach((value) => {
                                    obj.events.push({
                                        start: value.start_time,
                                        end: value.end_time,
                                        duration: value.m / 60,
                                        name: value.name,
                                        event: value.cate_name,
                                        id: value.id,
                                        appoint_said: value.appoint_said
                                    })
                                })
                                tem.push(obj)
                            }
                        })
                        that.$set(that.dayCal, date, cal)
                        that.$set(that.dayCache, date, tem)
                    } else {
                        that.$delete(that.dayCache, date)
                        uni.showToast({ title: res.message, icon: "none" })
                    }
                }).catch(() => { that.$delete(that.dayCache, date) })
            },
            // [カスタムエイド 2026-10-19] 月ごとの営業時間(1回だけ読み込む。同時に頼まれたら同じ読み込みを待つ)
            loadMonth(date) {
                let that = this
                let m = date.slice(0, 7)
                let c = that.calCache[m]
                if (c && typeof c.then != 'function') return Promise.resolve(c)
                if (c) return c
                let p = getCalendar({ date: m + '-01', type: "day" }).then((res) => {
                    if (res.code == 200) {
                        that.calCache[m] = res.data
                        return res.data
                    }
                    delete that.calCache[m]
                    return null
                }).catch(() => { delete that.calCache[m]; return null })
                that.calCache[m] = p
                return p
            },
            // [カスタムエイド 2026-10-19] 店舗の今の日時(1回だけ。予約の追加・変更のあとに読み直す)
            loadShopTime() {
                let that = this
                getConfig().then((res) => {
                    if (res.code == 200) that.shopTime = res.data.config.time_date
                }).catch(() => {})
            },
            loadAroundDays() {
                let d = this.parseDay(this.dDate)
                this.loadDay(this.dDate)
                this.loadDay(this.ymd(this.addDays(d, 1)))
                this.loadDay(this.ymd(this.addDays(d, -1)))
            },
            // 1週分の予約枠と営業時間を読み込む(両方そろってから表示する)
            loadWeek(sunday, showLoading) {
                let that = this
                let key = that.weekKey(sunday)
                if (that.weekCache[key] && that.weekInfo[key]) return
                if (that.weekCache[key] === null) return
                that.$set(that.weekCache, key, null)
                let gen = that.weekGen
                if (showLoading) uni.showLoading({ title: "読み込み中", mask: true })
                let p1 = getAppointmentList({ date: key, type: "order_week", id: that.staffId })
                let p2 = that.weekInfo[key] ? Promise.resolve({ code: 200, data: that.weekInfo[key] }) : that.loadMonth(that.ymd(sunday)).then((d) => d ? { code: 200, data: d } : { code: 500 })
                Promise.all([p1, p2]).then(([res, cal]) => {
                    if (showLoading) uni.hideLoading()
                    if (gen != that.weekGen) return
                    if (res.code == 200 && cal.code == 200) {
                        let array = res.data.data
                        array.forEach((val) => { val.times = that.times })
                        that.$set(that.weekInfo, key, cal.data)
                        that.$set(that.weekCache, key, array)
                    } else {
                        that.$delete(that.weekCache, key)
                        if (res.code != 200) uni.showToast({ title: res.message, icon: "none" })
                    }
                }).catch(() => {
                    if (showLoading) uni.hideLoading()
                    if (gen == that.weekGen) that.$delete(that.weekCache, key)
                })
            },
            loadAroundWeeks() {
                let s = this.sundayOf(this.dDate)
                this.loadWeek(s, !this.weekCache[this.weekKey(s)])
                this.loadWeek(this.addDays(s, 7), false)
                this.loadWeek(this.addDays(s, -7), false)
            },
            // 予約を追加・変更したあとなど:読み込み済みのデータを捨てて読み込み直す
            refreshAll() {
                this.calCache = {}
                this.dayCal = {}
                this.loadShopTime()
                this.dayCache = {}
                this.weekGen++
                this.weekCache = {}
                this.weekInfo = {}
                if (this.status == 'week') this.loadAroundWeeks()
                else this.loadAroundDays()
            },
            // 上の ‹ ›:週は週を切り替え(一緒に動く)、日は今までどおり1週間ずらす
            onArrow(dir) {
                if (this.status == 'week') this.goWeek(dir)
                else this.goHeadWeek(dir)
            },
            pickDay(date) {
                if (this.status != 'day' || this.busy || date == this.dDate) return
                this.dDate = date
                this.reloadtime = date
                this.staffX = 0
                this.loadAroundDays()
            },
            // 週:日にちの帯と予約枠を一緒に動かして、次の週・前の週へ
            goWeek(dir) {
                let that = this
                if (that.busy) return
                that.busy = true
                let w = that.pagerWidth()
                that.headAnim = true; that.gridAnim = true
                that.headDx = -dir * w; that.gridDx = -dir * w
                setTimeout(() => {
                    that.headAnim = false; that.gridAnim = false
                    that.dDate = that.ymd(that.addDays(that.parseDay(that.dDate), dir * 7))
                    that.selectDate = that.weekKey(that.sundayOf(that.dDate))
                    that.headDx = 0; that.gridDx = 0
                    that.$nextTick(() => {
                        that.busy = false
                        that.loadAroundWeeks()
                    })
                }, 290)
            },
            // 日:日付の帯だけを動かして1週間ずらす(選んでいる曜日はそのまま)
            goHeadWeek(dir) {
                let that = this
                if (that.busy) return
                that.busy = true
                that.headAnim = true
                that.headDx = -dir * that.pagerWidth()
                setTimeout(() => {
                    that.headAnim = false
                    that.dDate = that.ymd(that.addDays(that.parseDay(that.dDate), dir * 7))
                    that.reloadtime = that.dDate
                    that.headDx = 0
                    that.staffX = 0
                    that.$nextTick(() => {
                        that.busy = false
                        that.loadAroundDays()
                    })
                }, 290)
            },
            // 日:予約枠を動かして次の日・前の日へ
            goDay(dir) {
                let that = this
                if (that.busy) return
                that.busy = true
                that.gridAnim = true
                that.gridDx = -dir * that.pagerWidth()
                setTimeout(() => {
                    that.gridAnim = false
                    that.dDate = that.ymd(that.addDays(that.parseDay(that.dDate), dir))
                    that.reloadtime = that.dDate
                    that.gridDx = 0
                    that.staffX = dir > 0 ? 0 : -that.staffMaxFor(that.dDate) // 前の日は右端から(戻ってきた向きに合わせる)
                    that.$nextTick(() => {
                        that.busy = false
                        that.loadAroundDays()
                    })
                }, 290)
            },
            staffMaxFor(date) {
                let list = this.dayCache[date] || []
                return list.length <= 4 ? 0 : uni.upx2px(list.length * 140 - 560)
            },
            snapBack(which) {
                let that = this
                if (which == 'head') { that.headAnim = true; that.headDx = 0 }
                else { that.gridAnim = true; that.gridDx = 0; that.headAnim = true; that.headDx = 0 }
                setTimeout(() => { that.headAnim = false; that.gridAnim = false }, 290)
            },
            touchPoint(e) {
                let t = (e.touches && e.touches[0]) || (e.changedTouches && e.changedTouches[0]) || {}
                return { x: t.clientX != null ? t.clientX : t.pageX, y: t.clientY != null ? t.clientY : t.pageY }
            },
            // 指の向き(横か縦か)を決める。縦なら何もしない(ページの縦スクロールに任せる)
            touchAxis(e) {
                let tc = this.tc
                if (!tc) return ''
                let p = this.touchPoint(e)
                tc.mx = p.x - tc.sx
                let my = p.y - tc.sy
                if (!tc.axis) {
                    if (Math.abs(tc.mx) < 6 && Math.abs(my) < 6) return ''
                    tc.axis = Math.abs(tc.mx) > Math.abs(my) ? 'x' : 'y'
                }
                return tc.axis
            },
            touchBegin(e) {
                if (this.busy) { this.tc = null; return }
                let p = this.touchPoint(e)
                this.tc = { sx: p.x, sy: p.y, st: Date.now(), axis: '', mx: 0, staff: this.staffX }
            },
            isFlick() {
                let tc = this.tc
                return tc && Math.abs(tc.mx) > 30 && Date.now() - tc.st < 250
            },
            // 日付の帯をなぞったとき:週は予約枠も一緒に、日は帯だけ動く
            headStart(e) { this.touchBegin(e) },
            headMove(e) {
                if (this.touchAxis(e) != 'x') return
                this.headDx = this.tc.mx
                if (this.status == 'week') this.gridDx = this.tc.mx
            },
            headEnd() {
                let tc = this.tc
                this.tc = null
                if (!tc || tc.axis != 'x' || this.busy) return
                let w = this.pagerWidth(), v = this.headDx, fast = Math.abs(tc.mx) > 30 && Date.now() - tc.st < 250
                let dir = v < -w * 0.3 || (fast && v < 0) ? 1 : v > w * 0.3 || (fast && v > 0) ? -1 : 0
                if (!dir) return this.snapBack(this.status == 'week' ? 'grid' : 'head')
                if (this.status == 'week') this.goWeek(dir)
                else this.goHeadWeek(dir)
            },
            // 週の予約枠をなぞったとき:日付の帯と一緒に動く
            weekStart(e) { this.touchBegin(e) },
            weekMove(e) { this.headMove(e) },
            weekEnd() { this.headEnd() },
            // 日の予約枠・担当者名の行をなぞったとき:
            // 担当者が多いときは、まず担当者を横に動かす。端に着いた状態から「もう一度」スワイプすると次の日・前の日が見えてくる
            // (端に着くまでのスワイプでは日は変わらない。少しだけ引っぱれて、離すと戻る)
            dayStart(e) { this.touchBegin(e) },
            dayMove(e) {
                if (this.touchAxis(e) != 'x') return
                let tc = this.tc, max = this.staffMax
                let total = tc.staff + tc.mx
                let atLeft = tc.staff >= 0, atRight = tc.staff <= -max
                if (total > 0) { this.staffX = 0; this.gridDx = atLeft ? total : total / 4 }
                else if (total < -max) { this.staffX = -max; this.gridDx = atRight ? total + max : (total + max) / 4 }
                else { this.staffX = total; this.gridDx = 0 }
            },
            dayEnd() {
                let tc = this.tc
                this.tc = null
                if (!tc || tc.axis != 'x' || this.busy) return
                let w = this.pagerWidth(), v = this.gridDx, max = this.staffMax
                let fast = Math.abs(tc.mx) > 30 && Date.now() - tc.st < 250
                // 素早くはらって日を変えるのは、触り始めたときに担当者が端にいたときだけ(途中から勢いで日が変わらないように)
                let atLeft = tc.staff >= 0, atRight = tc.staff <= -max
                let dir = atRight && (v < -w * 0.3 || (fast && v < 0)) ? 1 : atLeft && (v > w * 0.3 || (fast && v > 0)) ? -1 : 0
                if (dir) this.goDay(dir)
                else if (v != 0) this.snapBack('grid')
            },
            // 显示设置组件
            showSetting() {
                let that = this
                let admin = uni.getStorageSync("admin")
                if(admin.admin_type == 2){
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    getAdminDetail(admin.id).then((res) => {
                        uni.hideLoading()
                        if(res.data.admin.privileges.includes(8)){
                            that.setting = true
                        }else{
                            that.showpermission = true
                        }
                    }).catch(() => { uni.hideLoading() })
                } else{
                    that.setting = true
                }    
            },
            // 关闭设置组件
            closeSetting(e) {
                this.setting = false
                this.reload()
            },
            // 显示设置详情
            checkDetail(e) {
                if (e) {
                    this.setting = false
                    this.showDetail = true
                }
                this.info = e
            },
            // 关闭设置详情
            closeDetail(e) {
                this.showDetail = false
                this.setting = true
            },
            // 跳转消息通知
            toNotice() {
                uni.navigateTo({
                    url: "../appointment/notice/notice"
                })
            },
            // 返回首页
            toIndex() {
                uni.switchTab({
                    url: "../index/index"
                })
            },
            // 新增预约
            addNewAppointment(e) {
                this.ctime = e.time
                this.aptype = e.aptype
                if(e.aptype == 'week'){
                     this.adid = this.staffId
                     this.dDate = e.dat
                }else{
                     this.adid = e.adid
                }
             
                setTimeout(() => {
                     this.addNew = true
                },500)
               
            }
        }
    }
</script>

<style lang="scss">
    .contain{
        width: 100vw;
        // overflow-x: hidden;
    }
    .calendar{
        border-bottom: 2upx solid #d2d2d7;
         border-top: 2upx solid #d2d2d7;
         margin-bottom: 12upx;
    }
    .askForName {
        display: flex;
        align-items: center;
        width: 100%;
        overflow-x: scroll;
        box-sizing: border-box;
        padding: 40upx 0 40upx 40upx;
        background: rgba(249, 249, 249, 0.94);

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


    .top {
        box-sizing: border-box;
        padding: 40upx 32upx;
        background: #F5F5F7;

        .topContent {
            background-color: #fff;
            border: 2upx solid #d2d2d7;
            border-radius: 20upx;

            .content_item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-sizing: border-box;
                padding: 40upx 34upx;

                .bk {
                    background-color: #1A73E8;
                    color: #fff !important;
                }

                .item_right {
                    width: 60upx;
                    height: 60upx;
                    border-radius: 50%;
                    font-size: 32upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    color: #1D1D1F;
                    text-align: center;
                    line-height: 60upx;
                }
            }

            .content_item:first-child {
                border-bottom: 2upx solid #d2d2d7;
            }
        }
    }

    /* ===== [カスタムエイド 2026-10-19] 日付の帯・スライド(予約WEBページと同じ見た目) ===== */
    .cs-wk { width: 100%; background: #fff; }
    .cs-wk-head { display: flex; align-items: center; justify-content: space-between; height: 100upx; box-sizing: border-box; padding: 0 30upx 0 60upx; border-bottom: 1px solid #f5f5f5; }
    .cs-wk-title { font-size: 34upx; color: #1D1D1F; font-family: Hiragino Sans-W6, Hiragino Sans; }
    .cs-wk-arrows { display: flex; align-items: center; }
    .cs-wk-arrow { width: 80upx; height: 80upx; display: flex; align-items: center; justify-content: center; }
    .cs-chev { width: 22upx; height: 22upx; border-top: 4upx solid #1A73E8; border-left: 4upx solid #1A73E8; }
    .cs-chev.prev { transform: rotate(-45deg); margin-left: 8upx; }
    .cs-chev.next { transform: rotate(135deg); margin-right: 8upx; }
    .cs-wk-labels { display: flex; margin-left: 158upx; width: 560upx; padding-top: 10upx; }
    .cs-wk-label { width: 80upx; text-align: center; font-size: 30upx; height: 60upx; line-height: 60upx; color: #1D1D1F; }
    .cs-wk-label.sun, .cs-wk-day.sun .cs-wk-num { color: #D93025; }
    .cs-wk-label.sat, .cs-wk-day.sat .cs-wk-num { color: #1A73E8; }
    /* touch-action: pan-y = 縦の指の動きはページのスクロール、横の動きは帯・予約枠のスライドに使う */
    .cs-pager { overflow: hidden; touch-action: pan-y; width: 560upx; }
    .cs-day-pager { margin-left: 158upx; padding-bottom: 12upx; }
    .cs-track { display: flex; width: 300%; transform: translate3d(-33.3333%, 0, 0); will-change: transform; }
    .cs-pane { width: 33.3333%; flex-shrink: 0; min-height: 1px; }
    .cs-wk-days { display: flex; }
    .cs-wk-day { width: 80upx; height: 80upx; display: flex; align-items: center; justify-content: center; }
    .cs-wk-num { width: 72upx; height: 72upx; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32upx; color: #1D1D1F; }
    .cs-wk-day.today .cs-wk-num { background: #1A73E8; color: #fff !important; }
    .cs-wbody, .cs-dbody { display: flex; width: 100%; }
    .cs-grid-pager { flex-shrink: 0; }
    /* 時間の列(予約枠の部品と同じ見た目)。週は week.vue、日は progress.vue と同じ */
    .cs-timecol { width: 158upx; flex-shrink: 0; padding-top: 8upx; box-sizing: border-box; }
    .cs-timecol .time { position: relative; top: -40upx; }
    .cs-timecol .timeItem { height: 80upx; line-height: 80upx; width: 158upx; font-size: 24upx; font-family: Hiragino Sans-W3, Hiragino Sans; color: #1D1D1F; display: flex; align-items: center; justify-content: space-between; text-align: center; }
    .cs-timecol .timeName { flex: 1; text-align: center; }
    .cs-timecol .point { width: 20upx; height: 2upx; background-color: #EAEAEA; }
    .cs-timecol.cs-d { width: 160upx; padding: 0 0 0 30upx; }
    .cs-timecol.cs-d .timeItem { width: 130upx; border-right: 2upx double #d2d2d7; }
    /* 日:予約表の上に9pxの余白(一番上の「09:00」が切れないように) */
    .cs-dbody { padding-top: 9px; }
    /* 日:担当者名の行。日付の帯の3px下(白い余白)で止まる。左は時間の列の幅だけあける */
    .cs-names { background: #fff; padding-top: 3px; padding-left: 160upx; width: 750upx; box-sizing: border-box; touch-action: pan-y; }
    .cs-names-in { display: flex; width: max-content; }
    .cs-names-clip { width: 560upx; overflow: hidden; }
    .cs-loading { padding-top: 160upx; text-align: center; font-size: 26upx; color: #86868B; }
    .calendar.cs-cal-d { margin-bottom: 0; }
    .cs-name { height: 100upx; flex-shrink: 0; position: relative; display: flex; flex-direction: column; justify-content: center; align-items: center; box-sizing: border-box; }
    .cs-name-t { width: 100%; text-align: center; font-size: 24upx; font-family: Hiragino Sans-W3, Hiragino Sans; color: #86868B; margin-bottom: 6upx; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cs-name-bar { position: absolute; left: 6upx; right: 6upx; bottom: 16upx; height: 8upx; border-radius: 8upx; }

    .content {
        background: #fff;

        .tabs {
            width: 100%;
            box-sizing: border-box;
            padding: 40upx 26upx;
            background:#fff;

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
</style>
