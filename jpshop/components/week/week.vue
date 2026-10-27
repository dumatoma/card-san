<template>
    <!-- [カスタムエイド 2026-10-19] hideTime: 時間の列を予約画面の側に出す(週を横にスライドしても時間の列は動かさない。予約WEBページと同じ) -->
    <view class="con" :class="{'cs-notime': hideTime}" :style="`height:${totalheight}upx`">
        <view class="time" v-if="!hideTime">
            <view class="timeItem" v-for="(item,index) in times">
                <view class="timeName" v-text="item.time"></view>
                <view class="point"></view>
            </view>
            <view class="timeItem">
                <view class="timeName">{{ endLabel }}</view>
                <view class="point"></view>
            </view>
        </view>
        <view class="manager">
            <!-- [カスタムエイド 2026-10-24d] 予約できる枠の丸(ring)と予約済みの×を SVG に(拡大してもぼやけない)。丸の色は #7FBDBA(少し濃く) -->
            <view v-for="(item,index) in weel">
                <view class="mItem" v-if="item.business == 1">
                    <!-- [カスタムエイド 2026-10-25] 予約できる枠は、枠のどこを押しても選べる(以前は丸の上だけ)。押した枠はうっすらグレーに -->
                    <view v-if="d <= item.stamp" class="dateItem" v-for="(ite,idx) in item.times"
                        :style="ite.status == 'selected'? 'background:#fff !important' : '' "
                        :class="{ 'cs-tap': ite.status == 'selected' }"
                        @click="ite.status == 'selected' && addNew(ite)">
                        <image src="../../static/icons/ring.svg" mode="" v-if="ite.status == 'selected'"></image>
                        <image style="width: 60upx;height: 60upx;" src="../../static/svg/noTime.svg" mode=""
                            v-if="ite.status == 'unselected'"></image>
                    </view>
                    <view v-if="d > item.stamp" class="dateItem" style="background: rgba(249, 249, 249, 0.94);"
                        v-for="(ite,idx) in item.times"></view>
                </view>
                <view class="mItem" v-if="item.business == 0"
                    style="background: rgba(217, 48, 37, 0.15);position: relative;">
                    <view class="restItem" style="background: rgba(217, 48, 37, 0.15);" v-for="(ite,idx) in item.times">
                    </view>
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
        // [カスタムエイド 2026-10-19] nowTime = 店舗の今の日時(予約画面で1回だけ読み込んだもの)。渡されたときはサーバーに聞きに行かない
        props: ["times", "weekData", "info", "hideTime", "nowTime"],
        data() {
            return {
                weel: [{}, {}, {}, {}, {}, {}, {}],
                current: 0,
                fdate: "",
                ftime: "",
                totalheight: 2160,
                d: "",
            };
        },
        created() {
            if (!this.nowTime) this.getConfig()
        },
        onShow() {

        },
        // [カスタムエイド 2026-10-22] 表の最後の時刻(今までは 22:00 の決め打ち)。時間の枠はお店の営業時間から作るため
        computed: {
            endLabel() {
                const a = this.times || []
                if (!a.length) return ''
                return this.addHalf(a[a.length - 1].time)
            }
        },
        watch: {
            times: function(newVal, oldVal) {
                this.times = newVal; //newVal即是chartData
            },
            // [カスタムエイド 2026-10-19] 週をスライドで切り替えるため、読み込み済みの週のデータを持って作られたときも表示する(immediate)
            weekData: {
                immediate: true,
                handler: function(newVal, oldVal) {
                    this.buildWeek(newVal)
                }
            }
        },
        methods: {
            // [カスタムエイド 2026-10-22] 次の枠の時刻(最後の枠のあとは「30分後」。以前は最後の枠に予約があると止まっていた)
            addHalf(t) {
                const h = Number(t.split(':')[0]), m = Number(t.split(':')[1])
                const v = h * 60 + m + 30
                return (v / 60 < 10 ? '0' : '') + Math.floor(v / 60) + ':' + (v % 60 ? '30' : '00')
            },
            nextTime(times, k) {
                return times[k + 1] ? times[k + 1].time : this.addHalf(times[k].time)
            },
            // 予約の終わり「00:00」は次の日の 0:00 = 24:00
            endOfT(t) {
                return t == '00:00' ? '24:00' : t
            },
            buildWeek(newVal) {
                let that = this
                if (!newVal || !newVal.length || !that.info || !that.info.week_business) return
                if (that.nowTime && !that.d) that.applyTime(that.nowTime)
                let array = JSON.parse(JSON.stringify(newVal))
                let tem = []
                if (that.info.week_business.length == 0) {
                    that.info.week_business = that.wkbus
                }
                // [カスタムエイド 2026-10-19] データが届いたらすぐ表示する(今まで0.5秒+0.2秒わざと待っていた)
                {
                    for (let i = 0; i < array.length; i++) {
                        let iosDate = array[i].date.replace(/-/g, "/")
                        let isd = new Date(iosDate)
                        array[i].stamp = isd.getTime()
                        let sb = []
                        let d1 = array[i].date.split('-')
                        let st = d1[0] + d1[1]
                        let sv = d1[2] * 1
                        let so
                        if(that.info.day_business[sv]){
                            so = that.info.day_business[sv].business_time.length > 0 ? that.info
                                .day_business[sv] : that.info.week_business[i]
                            
                        }else{
                            so = that.info.week_business[i]
                        }
                       array[i].sb = so.business_time
                        if (array[i].orders.length > 0) {
                            for (let k = 0; k < array[i].times.length; k++) {
                                for (let j = 0; j < array[i].orders.length; j++) {
                                    if (array[i].times[k].time == array[i].orders[j].start_time && that.nextTime(array[i].times, k) == that.endOfT(array[i].orders[j].end_time)) {
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
                                array[i].times[k].date = array[i].date
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

                    that.weel = tem
                    that.totalheight = 80 * tem[0].times.length
                }

            },
            getCurrent() {
                let date = new Date()
                let stamp = date.getTime()
                this.current = stamp - 24 * 60 * 60 * 1000
            },
            addNew(e) {
                let that = this
                let s = {}
                s.time = e.time
                s.aptype = "week"
                s.dat = e.date
                console.log("sss", e)
                that.$emit("addNew", s)
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
                        that.applyTime(res.data.config.time_date)
                        // 先にデータが届いていたら、今の日時が分かったところで作り直す
                        if (that.weekData && that.weekData.length) that.buildWeek(that.weekData)
                    }
                })
            },
            applyTime(timeDate) {
                let that = this
                {
                    {
                        let array = timeDate.split(" ")
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
                        // [カスタムエイド 2026-10-19] 確認用に日付をお知らせ表示していた行を削除(スライド用に3週分作るため3回出ていた)
                    }
                }

            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-25] 予約表の枠を押したとき、押した枠がうっすらグレー(#F5F5F7 相当)になる。
       枠の背景は style で直接指定されている(!important)ため、上に薄い色を重ねて見せる */
    /* [カスタムエイド 2026-10-26] hover-class をやめ、CSS の :active に(押したあとの切り替えが遅くなっていたため) */
    .cs-tap:active::after {
        content: '';
        position: absolute;
        left: 0; top: 0; right: 0; bottom: 0;
        background: rgba(29, 29, 31, 0.05);
        pointer-events: none;
    }

    .restName {
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

            .restItem {
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
                position: relative;
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