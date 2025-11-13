<template>
    <view class="contain">
        <u-navbar :placeholder="true" :safeAreaInsetTop="true" autoBack :height="60" :border="true" bgColor="rgb(251,251,253)">
            <view class="u-nav-slot" slot="center">
                <view style="text-align: center;">予約日時選択</view>
                <view v-text="info.shop_name" style="text-align: center;"></view>
            </view>
        </u-navbar>
        
        <view class="top">
            <view class="topContent">
                <view class="to1" v-text="orderInfo.title"></view>
                <view class="to2" v-text="`${orderInfo.price}`"></view>
                <view class="to3" v-text="`所要時間(目安)：${orderInfo.order_time}分`"></view>
            </view>
        </view>
        <view class="tip">
            ご希望の来店日時を選択してください
        </view>
        <view class="content">
            <view class="askForName" v-if="info.is_appoint == 1">
                <view class="name_item" :class="current == 0? 'active' : ''" @click="changeName(0,0,0)">指名なし</view>
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
            <u-sticky offsetTop="16">
            <view class="calendar">
                <lxCalendar @change="change"></lxCalendar>
            </view>
            </u-sticky>
            <!-- 周 -->
            <view v-if="status == 'week'">
                <week :info="info" :times="times" @addNew="addNewAppointment" :weekData="weekData"></week>
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
    import lxCalendar from "@/components/lx-calendar/lx-calendar.vue"
    import week from "@/components/week/week.vue"
    import {
        getAppointmentList,
        getStaffList,
        getShopInfo,
        menuList,
        menuCate
    } from "@/api/index.js"
    export default {
        components: {
            lxCalendar,
            week
        },
        data() {
            return {
                menuList:[],
                status: "week",
                showMenu:false,
                setting: false, //设置
                current: 0,
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
                query:{}
            }
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
            setTimeout(() => {
                that.getWeekList(that.formatDate(that.getWeekDate(that.getToday())))
                that.selectDate = that.formatDate(that.getWeekDate(that.getToday()))
            },1000)
           
        },
        onShow() {
            // let that = this
            // let query = that.$route.query
            // that.query = query
        },
        methods: {
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
                        // console.log(111111,res.data.shop.day_business["202303"])
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
                that.getWeekList(that.selectDate)
            },

            // 返回首页
            toIndex() {
                uni.navigateBack()
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


    .top {
        box-sizing: border-box;
        padding: 40upx 32upx;
        background: #F5F5F7;

        .topContent {
            background-color: #fff;
            border: 2upx solid #d2d2d7;
            border-radius: 20upx;
            box-sizing: border-box;
            box-shadow: 0px 0px 10upx 2upx rgba(0,0,0,0.16);
            padding: 40upx;

            .to1{
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }
            
            .to2{
                font-size: 28upx;
                font-family: Hiragino Sans-W6, Hiragino Sans;
                font-weight: normal;
                color: rgba(217,48,37,0.65);
                margin: 12upx 0;
            }
            
            .to3{
               font-size: 28upx;
               font-family: Hiragino Sans-W3, Hiragino Sans;
               font-weight: normal;
               color: #1D1D1F; 
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
</style>
