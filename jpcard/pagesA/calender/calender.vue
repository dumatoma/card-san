<template>
    <view style="height: 100vh;overflow-y: scroll;">
        <!-- 自定义顶部导航 -->
        <u-navbar title="営業カレンダー" :placeholder="true" :safeAreaInsetTop="true" :border="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../static/svg/button.svg" style="width: 60upx;height: 60upx;" mode="" @click="toIndex">
                </image>
            </view>
        </u-navbar>
        <!-- 日历插件 -->
        <calendar @onDayClick="selectDay" :restArr='rest' @monthChange="monthChange"></calendar>
        <!-- 当日营业情况 -->
        <view class="today">
            <view class="todayInfo">
                <view>
                    {{month}}月{{date}}日
                </view>
                <view>
                    {{day}}曜日
                </view>
            </view>
            <view class="opTitle" v-if="open == 1 && isToday != 1">
                営業時間
            </view>
            <view class="opTitle" style="color: #707070;" v-if="open == 1 && isToday != 0 && ss == 0">
                営業時間外
            </view>
            <view class="opTitle" v-if="open == 1 && isToday != 0  && ss == 1">
                営業中
            </view>
            <view class="opTitle" style="color: rgb(217, 48, 37)" v-if="open == 0">
                休業日
            </view>
            <view v-if="open == 1">
                <view class="openTime" v-for="(item,index) in openArr" v-text="`${item.start} ~ ${item.end}`"></view>
            </view>
        </view>
        <view class="height:50upx">
            
        </view>
    </view>
</template>
<script>
    import calendar from "../../components/calendar.vue"
    import {
        getCalendar,
        setBusinessHours
    } from "@/api/index.js"
    export default {
        components: {
            calendar
        },
		// props:["sid"],
        data() {
            return {
                isToday:1,
                rest: [],
                today:{},
                openArr: [],
                days: {},
                month: "",
                ss:0,
                date: "",
                day: "",
                fulldate: "",
                year: "",
                settingIndex: 0,
                settingStatus: "",
                open: 1,
                show: false,
                show1: false,
                takeRest: false,
                pickerArr: [{
                    start: "00:00",
                    end: "00:00"
                }],
                timeArr: [
                    ["00:00", "00:30", "01:00", "01:30", "02:00", "02:30", "03:00", "03:30", "04:00", "04:30",
                        "05:00", "05:30", "06:00", "06:30", "07:00", "07:30", "08:00", "08:30", "09:00", "09:30",
                        "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", "13:00", "13:30", "14:00", "14:30",
                        "15:00", "15:30", "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", "19:00", "19:30",
                        "20:00", "20:30", "21:00", "21:30", "22:00", "22:30", "23:00", "23:30"
                    ]
                ]
            };
        },

        created() {
            
        },
        onLoad(options) {
            let that = this
            let date = that.getMonth()
            let today = that.getToday()
            that.today = today
            let data = {}
            data.type = "day"
            data.date = date
            that.month = today.month
            that.date = today.date
            that.day = today.week
            that.year = today.year
            that.fulldate = today.year + '-' + today.month + '-' + today.date
            data.id = options.sid
            that.getCalendar(data, "today", today.date * 1)
        },
        methods: {
            getMonth() {
                let that = this
                let date = new Date()
                let year = date.getFullYear()
                let month = (date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                return year + "-" + month
            },
            toIndex() {
                uni.navigateBack()
            },
            save() {
                let that = this
                let obj = {}
                obj.type = 'day'
                obj.date = that.fulldate
                obj.business = that.open
                obj.business_time = that.openArr
                setBusinessHours(obj).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                        let data = {}
                        data.type = "day"
                        data.date = that.year + '-' + that.month
                        that.getCalendar(data, "a", 1)
                        that.show = false
                    }
                })
            },
            confirmPicker(e) {
                let that = this
                let index = that.settingIndex
                let status = that.settingStatus
                if (status == "start") {
                    that.openArr[index].start = e.value[0]
                } else {
                    that.openArr[index].end = e.value[0]
                }
                that.show1 = false
            },
            showSetting() {
                let that = this
                that.show = true
            },
            getToday() {
                let date = new Date()
                let year = date.getFullYear()
                let month = (date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                let week = date.getDay()
                let sdate = date.getDate() < 10 ? '0' + date.getDate() : date.getDate()
                let wek
                wek = week == 0 ? '日' : week == 1 ? '月' : week == 2 ? '火' :
                    week == 3 ? '水' : week == 4 ? '木' : week == 5 ? '金' : '土'
                let obj = {}
                obj.week = wek
                obj.year = year
                obj.month = month
                obj.date = sdate
                return obj
            },
            getCalendar(data, status, index) {
                getCalendar(data).then((res) => {
                    let that = this
                    if (res.code == 200) {
                        let temp = []
                        that.days = res.data.day_business
                        res.data.rest_days.forEach((value, index) => {
                            temp.push(value * 1)
                        })
                        that.rest = temp
                        if (status == "today") {
                            if (res.data.day_business[index].business_time.length > 0) {
                                that.openArr = res.data.day_business[index].business_time
                                
                            } else {
                                that.openArr = that.pickerArr
                            }
                            if(that.openArr.length > 1){
                                if(that.checkAuditTime(that.openArr[0].start,that.openArr[0].end) || that.checkAuditTime(that.openArr[1].start,that.openArr[1].end)){
                                    that.ss = 1
                                }else{
                                    that.ss = 0
                                }
                            }else if(that.openArr.length = 1){
                                if(that.checkAuditTime(that.openArr[0].start,that.openArr[0].end)){
                                    that.ss = 1
                                }else{
                                    that.ss = 0
                                }
                            }
                            that.open = res.data.day_business[index].business
                        }
                    }
                })
            },
            checkAuditTime(beginTime, endTime) {
            		var nowDate = new Date();
            		var beginDate = new Date(nowDate);
            		var endDate = new Date(nowDate);
            		
            		var beginIndex = beginTime.lastIndexOf("\:");
            		var beginHour = beginTime.substring(0, beginIndex);
            		var beginMinue = beginTime.substring(beginIndex + 1, beginTime.length);
            		beginDate.setHours(beginHour, beginMinue, 0, 0);
            		
            		var endIndex = endTime.lastIndexOf("\:");
            		var endHour = endTime.substring(0, endIndex);
            		var endMinue = endTime.substring(endIndex + 1, endTime.length);
            		endDate.setHours(endHour, endMinue, 0, 0);
            		return nowDate.getTime() - beginDate.getTime() >= 0 && nowDate.getTime() <= endDate.getTime();
            },
            showPicker(index, status) {
                let that = this
                that.show1 = true
                that.settingIndex = index
                that.settingStatus = status
            },
            selectDay(e) {
                let that = this
                that.fulldate = e.date
                let indexArr = e.date.split("-")
                let index = indexArr[2] * 1
                let today = that.today
                if((today.year + '-' + today.month + "-"+today.date) == e.date){
                    that.isToday = 1
                }else{
                    that.isToday = 0
                }
                that.month = indexArr[1]
                that.date = indexArr[2]
                that.year = indexArr[0]
                that.day = e.week
                if (that.days[index].business_time.length > 0) {
                    that.openArr = that.days[index].business_time
                } else {
                    that.openArr = that.pickerArr
                }

                that.open = that.days[index].business
                if(that.openArr.length > 1){
                    if(that.checkAuditTime(that.openArr[0].start,that.openArr[0].end) || that.checkAuditTime(that.openArr[1].start,that.openArr[1].end)){
                        that.ss = 1
                    }else{
                        that.ss = 0
                    }
                }else if(that.openArr.length = 1){
                    if(that.checkAuditTime(that.openArr[0].start,that.openArr[0].end)){
                        that.ss = 1
                    }else{
                        that.ss = 0
                    }
                }
            },
            monthChange(e) {
                let that = this
                let date = e.year + '-' + e.month
                let data = {}
                data.type = "day"
                data.date = date
                that.rest = []
                that.getCalendar(data)
            },
            toSetting() {
                uni.navigateTo({
                    url: "../calender/setting/setting"
                })
            },
            changeStatus(e) {
                this.open = e
            },
            additem() {
                let that = this
                let obj = {}
                obj.start = "00:00"
                obj.end = "00:00"
                that.openArr.push(obj)
            },
            delitem() {
                let that = this
                that.openArr.splice(1, 1)
            }
        }
    }
</script>
<style lang="scss">
    .timePicker {
        .pickerTitle {
            display: flex;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-top: 96upx;
            border-bottom: 2upx solid #d2d2d7;
            padding-bottom: 12upx;

            .titleitem {
                flex: 1;
                text-align: center;
            }
        }

        .pickerContent {
            .pickerItem {
                display: flex;
                align-items: center;
                flex: 1;
                text-align: center;
                box-sizing: border-box;
                padding: 40upx 0;
                border-bottom: 2upx solid #d2d2d7;

                .pItem {
                    flex: 1;
                }

                .pItem:last-child {
                    // box-sizing: border-box;
                    // padding-left: 80upx;
                    position: relative;
                    left: 60upx;
                }
            }
        }
    }

    .pContent {
        width: 100vw;
        height: 90vh;
        background: #fff;
        overflow-y: scroll;
        border-top-left-radius: 32upx;
        border-top-right-radius: 32upx;

        .Cdate {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 10upx 50upx;

            .CdateItem:last-child {
                margin-left: 156upx;
            }

            .CdateItem {
                display: flex;
                align-items: center;
                font-size: 40upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #AAAAAA;

                .selectContent {
                    margin-right: 28upx;
                }
            }
        }

        .dateInfo {
            font-size: 48upx;
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            display: flex;
            box-sizing: border-box;
            padding: 92upx 40upx;

            .todate {
                margin-right: 60upx;
            }
        }

        .title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
            padding: 32upx 26upx;
            background: #FBFBFD;
            border-bottom: 2upx solid #d2d2d7;

            .titleName {
                font-size: 40upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }
        }
    }



    .today {
        width: 600upx;
        background: #F5F5F7;
        box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);
        border-radius: 20upx;
        margin: 80upx auto 0;
        box-sizing: border-box;
        padding: 24upx 0;
        text-align: center;

        .todayInfo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 40upx;

            view {
                margin: 0 20upx;
            }
        }

        .opTitle {
            font-size: 40upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #007AFF;
        }

        .openTime {
            font-size: 34upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-top: 20upx;
        }
    }

    .calendarChoose {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #F5F5F7;
        box-sizing: border-box;
        padding: 44upx 60upx;

        .chooseRight {
            display: flex;
            align-items: center;
        }
    }

    .page {
        height: 80vh;
    }

    .dateTitle {
        display: flex;
        box-sizing: border-box;
        padding: 20upx 40upx;
        background: #F5F5F7;
        border-bottom: 2upx solid #d2d2d7;

        .dateItem {
            flex: 1;
            text-align: center;
            font-size: 28upx;
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: normal;
            color: #86868B;
        }
    }
</style>
