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
             <u-sticky :offset-top="totalDing" customNavHeight="44">
            <!-- 日历组件 -->
            <view class="calendar" style="background: #ffffff;">
                <lxCalendar @change="change"></lxCalendar>
            </view>
            </u-sticky>
            <!-- 日 -->
            <view v-if="status == 'day'">
                <progres :manager="managers" @addNew="addNewAppointment" :tdate="dDate" :times="times" @checkdetail="godetail"></progres>
            </view>
            <!-- 周 -->
            <view v-if="status == 'week'">
                <week :times="times" @addNew="addNewAppointment" :info="cinfo" :weekData="weekData"></week>
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
    import lxCalendar from "@/components/lx-calendar/lx-calendar.vue"
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
            lxCalendar,
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
                adname:""
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
                    console.log("ca",res)
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
                          that.getList(that.getToday())
                          that.getStaffData()
                        } 
                    }
                })
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
                    that.getList(that.reloadtime)
                },800)
                
                
            },
            // 切换日、周
            changeTab(e) {
                let that = this
                that.status = e
                if (e != "week") {
                    that.getList(that.dDate)
                } else {
                    that.selectDate = that.formatDate(that.getWeekDate(that.getToday()))
                    that.getWeekList(that.formatDate(that.getWeekDate(that.dDate)))
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
                  console.log("Res",res)
                  if(res.code == 200){
                      that.totalNum = res.data.unread_count
                      that.todayNum = res.data.today_count
                  }else{
                      uni.showToast({
                          title:res.message,
                          icon:"none"
                      })
                  }
              })  
            },
            getStaffData() {
                let that = this
                let data = {}
                that.staff = []
                data.id = 0
                data.start_time = ""
                data.end_time = ""
                getStaffList(data).then((res) => {
                    console.log("staff",res)
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
                    })
                } else {
                    that.getList(e.fulldate)
                }
                that.dDate = e.fulldate
            },
            toSet() {
                let that = this
                let array = that.manager
                console.log("ar",array)
                that.managers = array
            },
            // 获取今日日期
            getToday() {
                let that = this
                let temdate = uni.getStorageSync("today")
                let date = new Date(temdate)
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
            // 关闭新增预约
            changeAdd(e) {
                let that = this
                if (e == "close") {
                    that.addNew = false
                }

                if (e == "refresh") {
                    that.addNew = false
                    that.getList(that.dDate)
                    that.getWeekList(that.formatDate(that.getWeekDate(that.dDate)))
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
                    console.log("co",res)
                    if (res.code == 200) {
                        let tem = []
                        let timeArr = JSON.parse(JSON.stringify(that.times))
                        res.data.admins.forEach((val, index) => {
                            console.log("val",val)
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
                        console.log("manager",that.manager)
                        that.toSet()
                    }else{
                      uni.showToast({
                          title:res.message,
                          icon:"none"
                      })
                  }
                })
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
                        console.log("weekarray",array)
                    }else{
                      uni.showToast({
                          title:res.message,
                          icon:"none"
                      })
                  }
                })
            },
            // 切换员工
            changeName(e, index) {
                let that = this
                that.current = index
                that.staffId = e
                that.getWeekList(that.selectDate)
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
                    })
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
                console.log("e",e)
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
