<template>
    <div class="con" :style="`height:${totalheight}px`">
        <div class="time">
            <div class="timeItem" v-for="(item,index) in times">
                <div class="timeName" v-text="item.time"></div>
                <div class="point"></div>
            </div>
            <div class="timeItem">
                <div class="timeName">22:00</div>
                <div class="point"></div>
            </div>
        </div>
        <div class="manager">
            <div v-for="(item,index) in weel">
                <div class="mItem" v-if="item.business == 1">
                    <div v-if="d <= item.stamp" class="dateItem" v-for="(ite,idx) in item.times"
                        :style="ite.status == 'selected'? 'background:#fff !important' : '' ">
                        <img src="~@/assets/ring.png" v-if="ite.status == 'selected'" @click="addNew(ite)" />
                        <img style="width: 30px;height: 30px;" src="~@/assets/noTime.svg" v-if="ite.status == 'unselected'" />
                    </div>
                    <div v-if="d > item.stamp" class="dateItem" style="background: rgba(249, 249, 249, 0.94);"
                        v-for="(ite,idx) in item.times"></div>
                </div>
                <div class="mItem" v-if="item.business == 0"
                    style="background: rgba(217, 48, 37, 0.15);position: relative;">
                    <div class="restItem" style="background: rgba(217, 48, 37, 0.15);" v-for="(ite,idx) in item.times">
                    </div>
                    <div class="restName">
                        <div>休</div>
                        <div>業</div>
                        <div>日</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="time">
            <div class="timeItem" v-for="(item,index) in times" style="flex-direction: row-reverse;border: none !important;">
                <div class="timeName" v-text="item.time"></div>
                <div class="point"></div>
            </div>
            <div class="timeItem" style="flex-direction: row-reverse;border: none !important;">
                <div class="timeName">22:00</div>
                <div class="point"></div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        getConfig
    } from "../../http/api.js"
    export default {
        name: "week",
        props: ["times", "weekData", "info"],
        data() {
            return {
                weel: [{}, {}, {}, {}, {}, {}, {}],
                current: 0,
                fdate: "",
                ftime: "",
                totalheight: 1080,
                d: "",
            };
        },
        created() {
            this.getConfig()
            console.log("in",this.info)
        },
        onShow() {

        },
        watch: {
            times: function(newVal, oldVal) {
                this.times = newVal; //newVal即是chartData
            },
            weekData: function(newVal, oldVal) {
                let that = this
                let options = {}
                 options.text = "読み込み中"
                that.$loading(options)
                let array = JSON.parse(JSON.stringify(newVal))
                console.log("arrat",array)
                let tem = []
                if (that.info.week_business.length == 0) {
                    that.info.week_business = that.wkbus
                }
                setTimeout(() => {
                    for (let i = 0; i < array.length; i++) {
                        let iosDate = array[i].date.replace(/-/g, "/")
                        let isd = new Date(iosDate)
                        array[i].stamp = isd.getTime()
                        let sb = []
                        let d1 = array[i].date.split('-')
                        let st = d1[0] + d1[1]
                        let sv = d1[2] * 1
                        let so = that.info.day_business[st]
                        console.log("so",so)
                        if (so) {
                            if (so[sv]) {
                                if (so[sv].business == 1) {
                                    sb = so[sv].business_time
                                    console.log(sb, 11)
                                }
                            } else {
                                console.log("equ",that.info.week_business[i])
                                sb = that.info.week_business[i].business_time
                            }
                        } else {
                            sb = that.info.week_business[i].business_time
                        }
                        array[i].sb = sb
                        if (array[i].orders.length > 0) {
                            for (let k = 0; k < array[i].times.length; k++) {
                                for (let j = 0; j < array[i].orders.length; j++) {
                                    if (array[i].times[k].time == array[i].orders[j].start_time && array[i]
                                        .times[k + 1].time == array[i].orders[j].end_time) {
                                        array[i].times[k].status = "unselected"
                                        array[i].times[k].date = array[i].date
                                        break;
                                    } else {
                                        if (array[i].sb.length == 1) {
                                            if (that.formatTime(array[i].sb[0].start) <= that.formatTime(
                                                    array[i].times[k]
                                                    .time) && that.formatTime(array[i].times[k].time) < that
                                                .formatTime(array[i]
                                                    .sb[
                                                        0].end)) {
                                                array[i].times[k].status = "selected"
                                            } else {
                                                array[i].times[k].status = "uselected"
                                            }
                                        } else {
                                            if ((that.formatTime(array[i].sb[0].start) <= that.formatTime(
                                                        array[i].times[k]
                                                        .time) && that
                                                    .formatTime(array[i].times[k].time) < that.formatTime(
                                                        array[i].sb[0].end)
                                                ) || (
                                                    that.formatTime(array[i].sb[1].start) <= that
                                                    .formatTime(array[i].times[k]
                                                        .time) && that.formatTime(array[i].times[k].time) <
                                                    that.formatTime(
                                                        array[i].sb[
                                                            1].end))) {
                                                array[i].times[k].status = "selected"
                                            } else {
                                                array[i].times[k].status = "uselected"
                                            }
                                        }
                                        array[i].times[k].date = array[i].date
                                    }

                                }

                            }

                        } else {
                            for (let k = 0; k < array[i].times.length; k++) {
                                // array[i].times[k].status = "unselected"
                                array[i].times[k].date = array[i].date
                                // console.log("sblength", array[i].sb.length)
                                if (array[i].sb.length == 1) {
                                    if (that.formatTime(array[i].sb[0].start) <= that.formatTime(array[i]
                                            .times[k]
                                            .time) && that.formatTime(array[i].times[k].time) < that
                                        .formatTime(array[i]
                                            .sb[
                                                0].end)) {
                                        array[i].times[k].status = "selected"
                                    } else {
                                        array[i].times[k].status = "uselected"
                                    }
                                } else {
                                    if ((that.formatTime(array[i].sb[0].start) <= that.formatTime(array[i]
                                                .times[k]
                                                .time) && that
                                            .formatTime(array[i].times[k].time) < that.formatTime(array[i]
                                                .sb[0].end)
                                        ) || (
                                            that.formatTime(array[i].sb[1].start) <= that.formatTime(array[
                                                    i].times[k]
                                                .time) && that.formatTime(array[i].times[k].time) < that
                                            .formatTime(
                                                array[i].sb[
                                                    1].end))) {
                                        array[i].times[k].status = "selected"
                                    } else {
                                        array[i].times[k].status = "uselected"
                                    }
                                }

                            }
                        }
                        tem.push(array[i])
                        // console.log("temp",array[i])
                    }

                    tem.forEach((v, i) => {
                        let that = this
                        if (v.date == that.fdate) {
                            v.times.forEach((value, index) => {
                                let t1 = value.time.split(':')
                                let t2 = ""
                                if (t1[1] == "00") {
                                    t2 = t1[0] * 1
                                } else {
                                    t2 = t1[0] * 1 + 0.5
                                }
                                if (t2 < that.ftime) {
                                    value.status = "uselected"
                                }
                            })
                        } else {
                            v.times.forEach((value, index) => {
                                let aa2 = value.date.split("-")
                                let aa1 = that.fdate.split("-")
                                if (aa2[0] * 1 <= aa1[0] * 1 && aa2[1] * 1 <= aa1[1] * 1 &&
                                    aa2[2] *
                                    1 < aa1[2] * 1) {
                                    value.status = "uselected"
                                }
                            })
                        }
                    })

                    setTimeout(function() {
                        that.weel = tem
                        that.totalheight = 40 * tem[0].times.length
                    }, 200)
                     that.$loading().close()
                }, 500)

            }
        },
        methods: {
            getCurrent() {
                let date = new Date()
                let stamp = date.getTime()
                this.current = stamp - 24 * 60 * 60 * 1000
            },
            addNew(e) {
                console.log("ite", e)
                let that = this
                that.$emit("addNew", e.time,e,"week")
            },
            // 通过日期获取时间戳
            getStamp(date) {
                let da = new Date(date)
                let stamp = da.getTime()
                return stamp
            },
            formatTime(e) {
                let time = e.split(':')
                let hour = time[0] * 1
                let minute = time[1] == "30" ? 0.5 : 0
                return hour + minute
            },
            getConfig() {
                let that = this
                getConfig().then((res) => {
                    if (res.code == 200) {
                        let array = res.data.config.time_date.split(" ")
                        let date = array[0]
                        let time = array[1]
                        let temp = date.split('/')
                        let temp1 = time.split(":")
                        let fdate = ""
                        let ftime = ''
                        for (let i = 0; i < temp.length; i++) {
                            if (i == temp.length - 1) {
                                fdate += temp[i]
                            } else {
                                fdate += temp[i] + '-'
                            }
                        }
                        if (temp1[1] * 1 > 30) {
                            ftime = temp1[0] * 1 + 1
                        } else {
                            ftime = temp1[0] * 1 + 0.5
                        }
                        that.fdate = fdate
                        that.ftime = ftime
                        let d = new Date(date)
                        that.d = d.getTime()

                        console.log("date", that.fdate)
                        // uni.showToast({
                        //     title: d,
                        //     icon: "none"
                        // })
                    } else {

                    }
                })

            }
        }
    }
</script>

<style lang="scss" scoped>
    .restName {
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        text-align: center;
        color: rgba(217, 48, 37, 0.65);
        box-sizing: border-box;
        padding-top: 180px;
    }

    .con {
        width: 750px;
        display: flex;
        padding-top: 8px;

        .time {
            box-sizing: border-box;
            padding: 0;
            position: relative;
            top: -20px;

            .timeItem {
                height: 40px;
                font-size: 12px;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                width: 87px;
                line-height: 40px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                text-align: center;

                .timeName {
                    flex: 1;
                    text-align: center;
                }

                .point {
                    width: 20px;
                    height: 2px;
                    background-color: #EAEAEA;
                }
            }
        }
    }
    .manager::-webkit-scrollbar{
        display: none !important;
    }
    .manager {
        width: 560px;
        overflow-x: scroll;
        display: flex;
        border-top: none;
        border-left: 2px solid #EAEAEA;

        // border-bottom: 2px solid #EAEAEA;
        .muItem {
            width: 40px;
            flex-shrink: 0;
            box-sizing: border-box;
            padding: 0;
            position: relative;
            text-align: center;
            background: rgba(249, 249, 249, 0.94);
            border-right: 1px solid #eaeaea;
            border-top: 1px solid #eaeaea;
            font-size: 16px;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: rgba(217, 48, 37, 0.65);
            padding-top: 121px;
        }

        .mItem {
            width: 40px;
            flex-shrink: 0;
            box-sizing: border-box;
            padding: 0;
            position: relative;

            .restItem {
                border: 1px solid transparent;
                height: 40px;
                background: rgba(217, 48, 37, 0.15) !important;
                text-align: center;
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            // border-bottom: 2px solid #eaeaea;
            .dateItem {
                border: 1px solid #eaeaea;
                height: 40px;
                background: rgba(249, 249, 249, 0.94) !important;
                text-align: center;
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: center;

                img {
                    width: 24px;
                    height: 24px;
                }
            }
        }

    }
</style>