<template>
    <view>
        <!-- 自定义顶部导航 -->
        <u-navbar title="営業カレンダー" :placeholder="true" :safeAreaInsetTop="true" :border="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" mode="" @click="toIndex">
                </image>
            </view>
            <view class="u-nav-slot" slot="right">
                <image src="../../static/svg/gd2.svg" style="width: 60upx;height: 60upx;" @click="toSetting" mode="">
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
            <view class="opTitle" v-if="open == 1" @click="showSetting">
                営業時間
            </view>
            <view class="opTitle" style="color: rgb(217, 48, 37)" v-if="open == 0" @click="showSetting">
                休業日
            </view>
            <view v-if="open == 1">
                <view class="openTime" v-for="(item,index) in openArr" v-text="`${item.start} ~ ${item.end}`"></view>
            </view>
        </view>
        <!-- 单出设置营业时间的弹窗 -->
        <u-popup :show="show" mode="bottom" @close="show = false" :safeAreaInsetTop="false" round="16">
            <view class="pContent">
                <view class="title">
                    <view>
                        <image @click="show = false" src="../../static/icons/tclose.png"
                            style="width: 60upx;height: 60upx;" mode=""></image>
                    </view>
                    <view class="titleName">
                        詳細
                    </view>
                    <view style="color: #1A73E8;" @click="save">
                        保存
                    </view>
                </view>
                <view class="dateInfo">
                    <view class="todate">
                        {{month}}月{{date}}日
                    </view>
                    <view class="toweek">
                        {{day}}曜日
                    </view>
                </view>
                <view class="Cdate">
                    <view class="CdateItem" @click="changeStatus(1)">
                        <view class="selectContent">
                            <view class="unselect" v-if="open == 0"></view>
                            <view class="selected" v-if="open == 1">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view :style="open == 0? '' : 'color:#1D1D1F' ">
                            営業日
                        </view>
                    </view>
                    <view class="CdateItem" @click="changeStatus(0)">
                        <view class="selectContent">
                            <view class="unselect" v-if="open == 1"></view>
                            <view class="selected" v-if="open == 0">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view :style="open == 0? 'color:#1D1D1F' : '' ">
                            休業日
                        </view>
                    </view>
                </view>

                <view class="timePicker" v-if="open == 1">
                    <view class="pickerTitle">
                        <view class="titleitem">
                            営業時間
                        </view>
                        <view class="titleitem">
                            開始
                        </view>
                        <view class="titleitem">
                            終了
                        </view>
                        <view class="titleitem">

                        </view>
                    </view>
                    <view class="pickerContent">
                        <view class="pickerItem" v-for="(item,index) in openArr">
                            <view class="pItem"></view>
                            <view class="pItem" v-text="item.start" @click="showPicker(index,'start')"></view>
                            <view class="pItem" v-text="item.end" @click="showPicker(index,'end')"></view>
                            <view class="pItem">
                                <u-icon v-if="openArr.length == 1" @click="additem" name="plus-circle" color="#1A73E8"
                                    size="30"></u-icon>
                                <u-icon v-if="index == 1" @click="delitem" name="close-circle-fill" size="30"></u-icon>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
        </u-popup>
        <u-picker @confirm="confirmPicker" :show="show1" @close="show1 = false" @cancel="show1 = false"
            :columns="timeArr"></u-picker>
            <permission :show="showpermission" @close='showpermission = false'></permission>
    </view>
</template>
<script>
    import calendar from "../../components/calendar.vue"
    import {
        getCalendar,
        setBusinessHours,
        getAdminDetail
    } from "@/api/index.js"
    import permission from "@/components/permission/permission.vue"
    export default {
        components: {
            calendar,
            permission
        },
        data() {
            return {
                showpermission:false,
                rest: [],
                openArr: [],
                days: {},
                month: "",
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
                    ["09:00", "09:30", "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", "13:00", "13:30", "14:00",
                        "14:30",
                        "15:00", "15:30", "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", "19:00", "19:30",
                        "20:00", "20:30", "21:00", "21:30", "22:00"
                    ]
                ]
            };
        },

        created() {
            let that = this
            let date = that.getMonth()
            let today = that.getToday()
            let data = {}
            data.type = "day"
            data.date = date
            that.month = today.month
            that.date = today.date
            that.day = today.week
            that.year = today.year
            that.fulldate = today.year + '-' + today.month + '-' + today.date
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
                let that = this
                uni.switchTab({
                    url: "../index/index"
                })
            },
            save() {
                let that = this
                let obj = {}
                obj.type = 'day'
                obj.date = that.fulldate
                obj.business = that.open
                obj.business_time = that.openArr
                console.log("obj",obj)
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
                    }else{
                        uni.showToast({
                            title: res.message,
                            icon:'none'
                        })
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
                        console.log("calcn",res)
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
                            that.open = res.data.day_business[index].business
                        }
                    }
                })
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
                let that = this
                let admin = uni.getStorageSync("admin")
                if(admin.admin_type == 2){
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    getAdminDetail(admin.id).then((res) => {
                        uni.hideLoading()
                        if(res.data.admin.privileges.includes(3)){
                            uni.redirectTo({
                                url: "../calender/setting/setting"
                            })
                        }else{
                            that.showpermission = true
                        }
                    })
                }else{
                    uni.redirectTo({
                        url: "../calender/setting/setting"
                    })
                }    
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
            margin-bottom: 24upx;

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