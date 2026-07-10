<template>
    <div class="comCointent">
        <i style="font-size: 20px;color: #707070;cursor: pointer;" class="el-icon-arrow-left" @click="prevWeek"></i>
        <div class="datesContainer">
            <div class="weekContainer">
                <div class="weekItem" v-for="item,index in weekList" v-text="item"
                    :style="index == 0? 'color:#D93025' : index == 7? 'color:#D93025' : index == 6? 'color: #1A73E8' : index == 13? 'color: #1A73E8' : '' ">
                </div>
            </div>
            <div class="dayContainer">
                <div class="dayItem"
                    :style="index == 0? 'color:#D93025' : index == 7? 'color:#D93025' : index == 6? 'color: #1A73E8' : index == 13? 'color: #1A73E8' : '' "
                    v-for="item,index in dateList">
                    <div class="dayWord" @click="selectItem(index)" :class="current == index? 'active' : '' "
                        v-text="formatTime(item.stamp,'date')"></div>
                </div>
            </div>
        </div>
        <i style="font-size: 20px;color: #707070;cursor: pointer;" class="el-icon-arrow-right" @click="nextWeek"></i>
    </div>
</template>

<script>
    import {getConfig} from "@/http/api.js"
    export default {
        name: 'calandars',
        data() {
            return {
                name: "日历测试页面",
                weekList: ["日", "月", "火", "水", "木", "金", "土", "日", "月", "火", "水", "木", "金", "土"],
                current: 0,
                startDate: "",
                dateList: [{
                        date: 1,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 2,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 3,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 4,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 5,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 6,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 7,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 8,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 9,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 10,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 11,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 12,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 13,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    },
                    {
                        date: 14,
                        fullDate: "",
                        checked: false,
                        stamp: ""
                    }
                ]
            };
        },
        created() {
            let that = this
            that.getToday()
            that.getDateData(that.getToday(), 'prev', "first")
            console.log("datetoday",this.getToday())
        },
        mounted() {},
        computed: {

        },
        methods: {
            getToday() {
                let that = this
                let stamp = localStorage.getItem("timestamp")
                console.log("stamp",stamp)
                let date = new Date(stamp*1000)
                let year = date.getFullYear()
                let month = date.getMonth() + 1
                let day = date.getDate()
                month = (month > 9) ? month : ("0" + month);
                day = (day < 10) ? ("0" + day) : day;
                let today = year + "-" + month + "-" + day
                return today
            },
            // 获取本周日期
            getWeekDate(dms, type) {
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
                if (type == 'prev') {
                    return lastSundayTime
                } else {
                    return sundayTime
                }

            },
            // 格式化时间
            formatTime(ms, type) {
                let date = new Date(ms)
                let Y = date.getFullYear()
                let M = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                let D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate()
                if (type == 'full') {
                    return `${Y}-${M}-${D}`
                } else {
                    return `${D}`
                }
            },
            getDateData(date, type, times) {
                let that = this
                
                that.dateList.forEach((value, index) => {
                    value.stamp = that.getWeekDate(date, type) + 24 * 60 * 60 * 1000 * index
                    value.fullDate = that.formatTime(value.stamp, 'full')
                    if (times == "first") {
                        if (value.fullDate == that.getToday()){
                            that.current = index
                            that.$emit("change", {"today":that.dateList[that.current].fullDate,"firstDay":that.dateList[0].fullDate})
                        }    
                    }
                })
            },
            // 点击下一周
            nextWeek() {
                let that = this
                that.getDateData(that.dateList[13].fullDate, 'next', "second")
                setTimeout(() => {
                    that.$emit("change", {"today":that.dateList[that.current].fullDate,"firstDay":that.dateList[0].fullDate})
                },500)
            },

            // 点击上一周
            prevWeek() {
                let that = this
                that.getDateData(that.formatTime(that.dateList[0].stamp - 24 * 60 * 60 * 10000, 'full'), 'prev',
                    "second")
                setTimeout(() => {
                    that.$emit("change", {"today":that.dateList[that.current].fullDate,"firstDay":that.dateList[0].fullDate})
                },500)
            },

            // 选择日期
            selectItem(index) {
                let that = this
                that.current = index
                that.$emit("change", {"today":that.dateList[index].fullDate,"firstDay":that.dateList[0].fullDate} )
            }
        }
    };
</script>

<style lang="scss" scoped>
    .comCointent {
        width: 100%;
        z-index: 999 !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        text-align: center;
    }

    .active {
        background: #1A73E8;
        color: #fff !important;
        border-radius: 50%;
    }

    .datesContainer {
        margin: 0 auto;
    }

    .weekContainer {
        display: flex;
        align-items: center;
        flex: 1;
        margin: 0 auto;

        .weekItem {
            width: 40px;
            font-size: 12px;
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: normal;
            color: #86868B;
        }
    }

    .dayContainer {
        display: flex;
        margin: 0 auto;

        .dayItem {
            width: 40px;
            font-size: 16px;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            height: 40px;
            cursor: pointer;

            .dayWord {
                width: 28px;
                height: 28px;
                line-height: 28px;
                margin: 0 auto;
                position: relative;
                top: 12px;
            }
        }
    }
</style>
