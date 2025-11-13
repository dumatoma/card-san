<template>
    <view>
        <calendar @onDayClick="selectDay" :businessArr="days" :restArr='rest' @monthChange="monthChange"></calendar>
        <view class="item bt" style="margin-top: 106upx;">
            <view class="item_left">
                月来店数
            </view>
            <view class="item_right" style="color: rgba(29, 29, 31, 1);" v-text="`${month_qrcode_count}件`"></view>
        </view>
        <view class="item">
            <view class="item_left">
                予約件数({{total}}件)
            </view>
            <view class="item_right" @click="show = !show">
                <image src="../../static/icons/right.png" :style="show == true?'transform:rotate(90deg)' : ''" mode="scaleToFill" style="width: 20upx;height: 30upx;"></image>
            </view> 
        </view>
        <view v-if="show == true">
            <view class="item">
                <view class="item_left il">
                    <view class="squa" style='background:rgba(170, 170, 170, 1)'></view>
                    <view>指名無し</view>
                </view>
                <view class="item_right" v-text="`${no_appoint_count}件`"></view> 
            </view>
            <view class="item" v-for="item,index in shop_admin" :style="index % 2 == 0? 'background: #F5F5F7;' : ''">
                <view class="item_left il">
                    <view class="squa" v-if="!item.color" style='background:rgb(23, 84, 145)'></view>
                    <view class="squa" v-else :style="`background:${item.color}`"></view>
                    <view v-text="item.name"></view>
                </view>
                <view class="item_right" v-text="`${item.order_count}件`"></view> 
            </view>
            <view style="height: 80upx;"></view>
        </view>
    </view>
</template>
<script>
    import calendar from "../../components/calendate.vue"
    import {
        getCalendar,
        setBusinessHours
    } from "@/api/index.js"
    export default {
        components: {
            calendar
        },
        data() {
            return {
                rest: [],
                openArr: [],
                days: {},
                month: "",
                date: "",
                day: "",
                fulldate: "",
                year: "",
                takeRest: false,
                business:[],
                total:0,
                no_appoint_count:0,
                shop_admin:[],
                show:false,
                month_qrcode_count:0
            };
        },
        onShow() {
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
                        that.total = res.data.order_count
                        that.month_qrcode_count = res.data.month_qrcode_count
                        that.shop_admin = res.data.shop_admin
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
                uni.navigateTo({
                    url:"/pages/daily/daily?date="+indexArr[0]+"-"+indexArr[1]*1+"-"+indexArr[2]*1+'&time='+indexArr[0]+"年"+indexArr[1]*1+"月"+indexArr[2]*1+"日"
                })
            },
            monthChange(e) {
                let that = this
                let date = e.year + '-' + e.month
                let data = {}
                data.type = "day"
                data.date = date
                that.rest = []
                that.getCalendar(data)
            }
        }
    }
</script>
<style lang="scss" scoped>
    .squa{
        width: 32upx;
        height: 32upx;
        border-radius: 8upx;
        margin-right: 20upx;
    }
    .il{
        display: flex;
        align-items: center;
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
    .bt{
        border-bottom: 2upx solid rgba(60, 60, 67, 0.36);
    }
    .item{
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 28upx 32upx;
    }
    .page {
        // height: 80vh;
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
