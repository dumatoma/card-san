<template>
    <!-- [カスタムエイド 2026-10-18] hideTime: 時間の列を日時選択の画面側に出す(週を横にスライドしても時間の列は動かさない) -->
    <view class="con" :class="{'cs-notime': hideTime}" :style="`height:${totalheight}upx`">
        <view class="time" v-if="!hideTime">
            <view class="timeItem" v-for="(item,index) in times">
                <view class="timeName" v-text="item.time"></view>
                <view class="point"></view>
            </view>
            <view class="timeItem">
                <view class="timeName">22:00</view>
                <view class="point"></view>
            </view>
        </view>
        <view class="manager">
            <view v-for="(item,index) in weel">
                <view class="mItem" v-if="item.business == 1">
                    <view v-if="d <= item.stamp" class="dateItem" v-for="(ite,idx) in item.times"
                        :style="ite.status == 'selected'? 'background:#fff !important' : '' ">
                        <!-- [カスタムエイド 2026-10-13] cs-slot: 押したときの見た目(App.vue) -->
                        <image class="cs-slot" src="../../static/icons/ring.svg" mode="" v-if="ite.status == 'selected'"
                            @click="addNew(ite)"></image>
                        <image style="width: 60upx;height: 60upx;" src="../../static/svg/noTime.svg" mode=""
                            v-if="ite.status == 'unselected'"></image>
                    </view>
                    <view v-if="d > item.stamp" class="dateItem" style="background: rgba(249, 249, 249, 0.94);"
                        v-for="(ite,idx) in item.times"></view>
                </view>
                <view class="mItem" v-if="item.business == 0" style="background: rgba(217, 48, 37, 0.15);position: relative;">
                    <view class="restItem" style="background: rgba(217, 48, 37, 0.15);" v-for="(ite,idx) in item.times"></view>
                    <view class="restName">
                        <view>休</view>
                         <view>業</view>
                         <view>日</view>
                    </view>
                </view>
            </view>
        </view>
    </view>
</template>

<script>
    import {
        getConfig
    } from "@/api/index.js"
    export default {
        name: "week",
        props: ["times", "weekData", "info", "hideTime"],
        data() {
            return {
                weel: [{}, {}, {}, {}, {}, {}, {}],
                current: 0,
                fdate: "",
                ftime: "",
                totalheight:2160,
                d: "",
            };
        },
        created() {
            this.getConfig()
        },
        onShow() {

        },
        watch: {
            times: function(newVal, oldVal) {
                this.times = newVal; //newVal即是chartData
            },
            // [カスタムエイド 2026-10-18] 週をスライドで切り替えるため、読み込み済みの週のデータを持って作られたときも表示する(immediate)
            weekData: {
                immediate: true,
                handler: function(newVal, oldVal) {
                let that = this
                if (!newVal || !newVal.length || !that.info || !that.info.week_business) return
                let array = JSON.parse(JSON.stringify(newVal))
                console.log("array",that.info)
                let tem = []
                if(that.info.week_business.length == 0){
                    that.info.week_business = that.wkbus
                }
                // [カスタムエイド 2026-10-24c] 以前は計算の前に 0.5秒、表示の前に 0.2秒 わざと待っていた(週をスライドしたとき表示が遅れる)。
                //   計算は一瞬で終わるので、待たずにすぐ出す。計算の中身は変えていない
                {
                    for (let i = 0; i < array.length; i++) {
                        let iosDate = array[i].date.replace(/-/g, "/")
                        let isd = new Date(iosDate)
                        array[i].stamp = isd.getTime()
                        let sb = []
                        let d1 = array[i].date.split('-')
                        let st = d1[0] + d1[1]
                        let sv = d1[2] * 1
                        let so = that.info.day_business[st]
                        // console.log("so",so)
                        if (so) {
                            if (so[sv]) {
                                if (so[sv].business == 1) {
                                    sb = so[sv].business_time
                                    console.log(sb, 11)
                                }
                            } else {
                                sb = that.info.week_business[i].business_time
                            }
                        } else {
                            console.log(i)
                            sb = that.info.week_business[i].business_time
                        }
                        array[i].sb = sb
                        // console.log("sb",array[i])
                        // [カスタムエイド 2026-10-24] 営業時間の枠(sb)が0個・1個・2個以上のどれでも判定できるように作り直した。
                        //   以前は「1個でなければ2個ある」前提で sb[0]・sb[1] を読んでいたため、営業カレンダーで休みにした日など
                        //   枠が0個の日があるとエラーで止まり、その週の予約枠が真っ白になっていた(隔週で休みがあると隔週で白くなる)
                        for (let k = 0; k < array[i].times.length; k++) {
                            const tm = array[i].times[k]
                            tm.date = array[i].date
                            let booked = false
                            for (let j = 0; j < array[i].orders.length; j++) {
                                const o = array[i].orders[j]
                                if (tm.time == o.start_time && that.nextTime(array[i].times, k) == that.endOfT(o.end_time)) { booked = true; break }
                            }
                            if (booked) { tm.status = "unselected"; continue }
                            tm.status = that.inBusiness(array[i].sb, tm.time) ? "selected" : "uselected"
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
                                if (aa2[0] * 1 <= aa1[0] * 1 && aa2[1] * 1 <= aa1[1] * 1 && aa2[2] *
                                    1 < aa1[2] * 1) {
                                    value.status = "uselected"
                                }
                            })
                        }
                    })
                    
                    that.weel = tem
                    that.totalheight = 80 * tem[0].times.length
                }
                
                }
            }
        },
        methods: {
            // [カスタムエイド 2026-10-24] 次の枠の時刻(最後の枠のあとは「30分後」)。予約の終わり「00:00」は 24:00 として比べる(アプリと同じ)
            addHalf(t) {
                const h = Number(t.split(':')[0]), m = Number(t.split(':')[1])
                const v = h * 60 + m + 30
                return (v / 60 < 10 ? '0' : '') + Math.floor(v / 60) + ':' + (v % 60 ? '30' : '00')
            },
            nextTime(times, k) {
                return times[k + 1] ? times[k + 1].time : this.addHalf(times[k].time)
            },
            endOfT(t) {
                return t == '00:00' ? '24:00' : t
            },
            // [カスタムエイド 2026-10-24] その時刻が営業時間の枠(いくつあっても、0個でも)の中か
            inBusiness(sb, t) {
                const list = Array.isArray(sb) ? sb : []
                return list.some((r) => r && r.start && r.end && this.formatTime(r.start) <= this.formatTime(t) && this.formatTime(t) < this.formatTime(r.end))
            },
            getCurrent() {
                let date = new Date()
                let stamp = date.getTime()
                this.current = stamp - 24 * 60 * 60 * 1000
            },
            addNew(e) {
                console.log("ite", e)
                let that = this
                that.$emit("addNew", e.date + ' ' + e.time)
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
                        // [カスタムエイド 2026-10-18] 確認用に日付(Date)をお知らせ表示していた行を削除
                    } else {

                    }
                })

            }
        }
    }
</script>

<style lang="scss">
    .restName{
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        text-align: center;
         color: rgba(217, 48, 37, 0.65);
         box-sizing: border-box;
         padding-top: 180upx;
    }
    .con {
        width: 750upx;
        display: flex;
        padding-top: 8upx;

        .time {
            box-sizing: border-box;
            padding: 0;
            position: relative;
            top: -40upx;

            .timeItem {
                height: 80upx;
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                width: 158upx;
                line-height: 80upx;
                display: flex;
                align-items: center;
                justify-content: space-between;
                text-align: center;

                .timeName {
                    flex: 1;
                    text-align: center;
                }

                .point {
                    width: 20upx;
                    height: 2upx;
                    background-color: #EAEAEA;
                }
            }
        }
    }

    .con.cs-notime {
        width: 560upx;
    }
    .con.cs-notime .manager {
        overflow: hidden;
    }
    .manager {
        width: 560upx;
        overflow-x: scroll;
        display: flex;
        border-top: none;
        border-left: 2upx solid #EAEAEA;

        // border-bottom: 2upx solid #EAEAEA;
        .muItem {
            width: 80upx;
            flex-shrink: 0;
            box-sizing: border-box;
            padding: 0;
            position: relative;
            text-align: center;
            background: rgba(249, 249, 249, 0.94);
            border-right: 2upx solid #eaeaea;
            border-top: 2upx solid #eaeaea;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: rgba(217, 48, 37, 0.65);
            padding-top: 242upx;
        }

        .mItem {
            width: 80upx;
            flex-shrink: 0;
            box-sizing: border-box;
            padding: 0;
            position: relative;
            .restItem{
                border: 2upx solid transparent;
                height: 80upx;
                background: rgba(217, 48, 37, 0.15) !important;
                text-align: center;
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            // border-bottom: 2upx solid #eaeaea;
            .dateItem {
                border: 2upx solid #eaeaea;
                height: 80upx;
                background: rgba(249, 249, 249, 0.94) !important;
                text-align: center;
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: center;

                image {
                    width: 48upx;
                    height: 48upx;
                }
            }
        }

    }
</style>