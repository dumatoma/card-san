<template>
    <view class="calendar-wrapper">
        <view class="header" v-if="headerBar">
            <view>{{y+'年'+formatNum(m)+'月'}}</view>
            <view class="arrows">
                <view class="preWidth" @click="changeMonth('pre')">
                    <u-icon name="arrow-left" color="#1A73E8" size="22"></u-icon>
                </view>
                <view class="nextWidth" @click="changeMonth('next')">
                    <u-icon name="arrow-right" color="#1A73E8" size="22"></u-icon>
                </view>
            </view>
        </view>
        <view class="week">
            <view class="week-day" v-for="(item, index) in weekDay" :key="index" :style="item == '日'? 'color:rgba(217,48,37,0.65)' : item == '土'? 'color: rgba(26,115,232,0.6);' : '' " >{{ item }}</view>
        </view>
        <view :class="{ hide: !monthOpen }" class="content">
            <view :style="{ top: positionTop + 'rpx' }" class="days">
                <view class="item" v-for="(item, index) in dates" :key="index">
                    <view class="day" @click="selectOne(item, $event)" :class="{
                            choose: choose == `${item.year}-${item.month}-${item.date}`&&item.isCurM,
                            nolm: !item.isCurM,
                            today: isToday(item.year, item.month, item.date),
                            isWorkDay: isWorkDay(item.year, item.month, item.date)
                        }" :style="index % 7 == 0? 'color: rgba(217,48,37,0.65);' : index % 7 == 6? 'color: rgba(26,115,232,0.6);' : '' ">
                        {{ Number(item.date) }}
                    </view>
                    <view class="rest" v-show="restArr.indexOf(Number(item.date)) != '-1' && item.isCurM">休</view>
                    <view class="markDay" v-if="isMarkDay(item.year, item.month, item.date)&&item.isCurM"></view>
                </view>
            </view>
        </view>
    </view>
</template>
<script>
    export default {
        name: 'ren-calendar',
        props: {
            // 星期几为第一天(0为星期日)
            weekstart: {
                type: Number,
                default: 0
            },
            // 标记的日期
            markDays: {
                type: Array,
                default: () => {
                    return [];
                }
            },
            // 是否展开
            open: {
                type: Boolean,
                default: true
            },
            //是否可收缩
            collapsible: {
                type: Boolean,
                default: true
            },
            //未来日期是否不可点击
            disabledAfter: {
                type: Boolean,
                default: false
            },
            restArr:{
                type: Array,
                default: () => {
                    return [];
                }
            }
        },
        data() {
            return {
                weektext: ['日', '月', '火', '水', '木', '金', '土'],
                y: new Date().getFullYear(), // 年
                m: new Date().getMonth() + 1, // 月
                dates: [], // 当前月的日期数据
                positionTop: 0,
                monthOpen: false,
                choose: '',
                headerBar: false // 月份切换按钮
            };
        },
        created() {
            this.dates = this.monthDay(this.y, this.m);
            // !this.open && this.toggle();
            this.toggle()
        },
        mounted() {
            this.choose = this.getToday().date;
        },
        computed: {
            // 顶部星期栏
            weekDay() {
                return this.weektext.slice(this.weekstart).concat(this.weektext.slice(0, this.weekstart));
            },
            height() {
                return (this.dates.length / 7) * 80 + 'rpx';
            }
        },
        methods: {
            formatNum(num) {
                let res = Number(num);
                return res < 10 ? '0' + res : res;
            },
            getToday() {
                let date = new Date();
                let y = date.getFullYear();
                let m = date.getMonth();
                let d = date.getDate();
                let week = new Date().getDay();
                let weekText = ['日', '一', '二', '三', '四', '五', '六'];
                let formatWeek = '星期' + weekText[week];
                let today = {
                    date: y + '-' + this.formatNum(m + 1) + '-' + this.formatNum(d),
                    week: formatWeek
                };
                return today;
            },
            // 获取当前月份数据
            monthDay(y, month) {
                let dates = [];
                let m = Number(month);
                let firstDayOfMonth = new Date(y, m - 1, 1).getDay(); // 当月第一天星期几
                let lastDateOfMonth = new Date(y, m, 0).getDate(); // 当月最后一天
                let lastDayOfLastMonth = new Date(y, m - 2, 0).getDate(); // 上一月的最后一天
                let weekstart = this.weekstart == 7 ? 0 : this.weekstart;
                let startDay = (() => {
                    // 周初有几天是上个月的
                    if (firstDayOfMonth == weekstart) {
                        return 0;
                    } else if (firstDayOfMonth > weekstart) {
                        return firstDayOfMonth - weekstart;
                    } else {
                        return 7 - weekstart + firstDayOfMonth;
                    }
                })();
                let endDay = 7 - ((startDay + lastDateOfMonth) % 7); // 结束还有几天是下个月的
                for (let i = 1; i <= startDay; i++) {
                    dates.push({
                        date: this.formatNum(lastDayOfLastMonth - startDay + i),
                        day: weekstart + i - 1 || 7,
                        month: m - 1 >= 0 ? this.formatNum(m - 1) : 12,
                        year: m - 1 >= 0 ? y : y - 1
                    });
                }
                for (let j = 1; j <= lastDateOfMonth; j++) {
                    dates.push({
                        date: this.formatNum(j),
                        day: (j % 7) + firstDayOfMonth - 1 || 7,
                        month: this.formatNum(m),
                        year: y,
                        isCurM: true //是否当前月份
                    });
                }
                for (let k = 1; k <= endDay; k++) {
                    dates.push({
                        date: this.formatNum(k),
                        day: (lastDateOfMonth + startDay + weekstart + k - 1) % 7 || 7,
                        month: m + 1 <= 11 ? this.formatNum(m + 1) : 0,
                        year: m + 1 <= 11 ? y : y + 1
                    });
                }
                return dates;
            },
            isWorkDay(y, m, d) {
                //是否工作日
                let ymd = `${y}/${m}/${d}`;
                let formatDY = new Date(ymd.replace(/-/g, '/'));
                let week = formatDY.getDay();
                if (week == 0 || week == 6) {
                    return false;
                } else {
                    return true;
                }
            },
            isFutureDay(y, m, d) {
                //是否未来日期
                let ymd = `${y}/${m}/${d}`;
                let formatDY = new Date(ymd.replace(/-/g, '/'));
                let showTime = formatDY.getTime();
                let curTime = new Date().getTime();
                if (showTime > curTime) {
                    return true;
                } else {
                    return false;
                }
            },
            // 标记日期
            isMarkDay(y, m, d) {
                let flag = false;
                for (let i = 0; i < this.markDays.length; i++) {
                    let dy = `${y}-${m}-${d}`;
                    if (this.markDays[i] == dy) {
                        flag = true;
                        break;
                    }
                }
                return flag;
            },
            isToday(y, m, d) {
                let checkD = y + '-' + m + '-' + d;
                let today = this.getToday().date;
                if (checkD == today) {
                    return false;
                } else {
                    return false;
                }
            },
            // 展开收起
            toggle() {
                this.monthOpen = !this.monthOpen;
                this.headerBar = !this.headerBar;
                if (this.monthOpen) {
                    this.positionTop = 0;
                } else {
                    let index = -1;
                    this.dates.forEach((i, x) => {
                        this.isToday(i.year, i.month, i.date) && (index = x);
                    });
                    this.positionTop = -((Math.ceil((index + 1) / 7) || 1) - 1) * 80;
                }
            },
            // 点击回调
            selectOne(i, event) {
                let date = `${i.year}-${i.month}-${i.date}`;
                let selectD = new Date(date).getTime();
                let curTime = new Date().getTime();
                let week = new Date(date).getDay();
                let weekText = ['日', '月', '火', '水', '木', '金', '土'];
                let formatWeek = weekText[week];
                let response = {
                    date: date,
                    week: formatWeek
                };
                if (!i.isCurM) {
                    return false;
                }
                if (selectD > curTime) {
                    if (this.disabledAfter) {
                        return false;
                    } else {
                        this.choose = date;
                        this.$emit('onDayClick', response);
                    }
                } else {
                    this.choose = date;
                    this.$emit('onDayClick', response);
                }
            },
            //改变年月
            changYearMonth(y, m) {
                this.dates = this.monthDay(y, m);
                this.y = y;
                this.m = m;
            },
            changeMonth(type) {
                if (type == 'pre') {
                    if (this.m + 1 == 2) {
                        this.m = 12;
                        this.y = this.y - 1;
                    } else {
                        this.m = this.m - 1;
                    }
                } else {
                    if (this.m + 1 == 13) {
                        this.m = 1;
                        this.y = this.y + 1;
                    } else {
                        this.m = this.m + 1;
                    }
                }
                this.dates = this.monthDay(this.y, this.m);
                let obj = {}
                obj.year = this.y
                obj.month = this.m
                this.$emit("monthChange",obj)
            }
        }
    };
</script>

<style lang="scss" scoped>
    .calendar-wrapper {
        color: #1D1D1F;
        font-size: 28rpx;
        text-align: center;
        background-color: transparent;
        padding-bottom: 10rpx;
        // box-shadow: 0 45rpx rgba(#cf3a18, .32);

        .rest{
            color: #D93025;
        }

        .week{
            background: #F5F5F7;
            box-sizing: border-box;
            padding: 0 64upx;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 88rpx;
            color: #1D1D1F;
            font-size: 32rpx;
            font-weight: bold;
            box-sizing: border-box;
            padding: 44upx 64upx 48upx;
            background: #F5F5F7;

            .arrows {
                display: flex;
                align-items: center;
            }

            .preWidth,
            .nextWidth {
                // background: rgba(red, 0.3);
                width: 40rpx;
                height: 40rpx;
                padding: 10rpx;
                margin: 0 20rpx;
            }

            .pre,
            .next {
                width: 0;
                height: 0;
                border-top: 20rpx solid transparent;
                border-bottom: 20rpx solid transparent;
            }

            .pre {
                margin-right: 30rpx;
                border-right: 20rpx solid #1D1D1F;
                margin-right: 10rpx;
            }

            .next {
                margin-left: 30rpx;
                border-left: 20rpx solid #1D1D1F;
                margin-left: 10rpx;
            }
        }

        .week {
            display: flex;
            align-items: center;
            height: 80rpx;
            line-height: 80rpx;

            view {
                flex: 1;
            }
        }

        .content {
            position: relative;
            overflow: hidden;
            transition: height 0.4s ease;

            .days {
                transition: top 0.3s;
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                position: relative;
                box-sizing: border-box;
                padding: 0 64upx;
                

                .item {
                    position: relative;
                    display: block;
                    height: 120rpx;
                    line-height: 60rpx;
                    width: calc(100% / 7);
                    

                    .day {
                        font-style: normal;
                        display: inline-block;
                        vertical-align: middle;
                        width: 60rpx;
                        height: 60rpx;
                        line-height: 60rpx;
                        overflow: hidden;
                        border-radius: 60rpx;

                        &.choose {
                            background-color: #1A73E8;
                            color: #fff !important;
                        }

                        &.nolm {
                            color: #1D1D1F;
                            opacity: 0.3;
                        }
                    }

                    .isWorkDay {
                        color: #1D1D1F;
                    }

                    .notSigned {
                        font-style: normal;
                        width: 8rpx;
                        height: 8rpx;
                        background: #fa7268;
                        border-radius: 10rpx;
                        position: absolute;
                        left: 50%;
                        bottom: 0;
                        pointer-events: none;
                    }

                    .today {
                        color: #fff;
                        background-color: #a8c0ff;
                    }

                    .workDay {
                        font-style: normal;
                        width: 8rpx;
                        height: 8rpx;
                        background: #4d7df9;
                        border-radius: 10rpx;
                        position: absolute;
                        left: 50%;
                        bottom: 0;
                        pointer-events: none;
                    }

                    .markDay {
                        font-style: normal;
                        width: 8rpx;
                        height: 8rpx;
                        background: #fc7a64;
                        border-radius: 10rpx;
                        position: absolute;
                        left: 50%;
                        bottom: 0;
                        pointer-events: none;
                    }
                }
            }
        }

        .hide {
            height: 80rpx !important;
        }

        .dropDown {
            width: 50rpx;
            height: 50rpx;
            background-color: #fc7a64;
        }

        .weektoggle {
            width: 100rpx;
            height: 30rpx;
            position: relative;
            bottom: -55rpx;
            left: 45%;
            background-color: #fe6766;
            border-radius: 0 0 20rpx 20rpx;
            padding: 10rpx;

            image {
                width: 25rpx;
                height: 18rpx;
            }
        }
    }
</style>
