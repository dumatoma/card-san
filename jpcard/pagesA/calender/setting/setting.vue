<template>
    <view>
        <!-- 自定义顶部导航 -->
        <u-navbar title="定休日と営業時間" :placeholder="true" :safeAreaInsetTop="true" :border="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" @click="goBack"
                    mode=""></image>
            </view>
        </u-navbar>
        <view class="content">
            <view class="tips">
                <view class="tipContent">
                    <text>祝日、不定期な休業日や営業時間は、営業日カレンダーの日付から直接設定してください。</text>
                </view>
            </view>
            <view class="timePicker">
                <view class="pickerTitle">
                    <view class="titleitem">
                        定休日
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
                    <view class="pickerItem" v-for="(item,index) in pickerArr">
                        <view class="ptItem"
                            style="display: flex;align-items: center;justify-content: center;width: 25%;">
                            <view class="selectContent" @click="chooseItem(index,item)">
                                <view class="unselect" v-if="!item.rest"></view>
                                <view class="selected" v-if="item.rest">
                                    <view class="fill"></view>
                                </view>
                            </view>
                            <text v-text="item.week"></text>
                        </view>
                        <view class="timeContent" v-if="!item.rest">
                            <view class="timItems" v-for="(ite,idx) in item.time">
                                <view class="pItem" v-text="ite.start" @click="showModule(index)"></view>
                                <view class="pItem" v-text="ite.end" @click="showModule(index)"></view>
                                <view class="pItem">
                                    <u-icon v-if="item.time.length == 1" @click="showModule(index)" name="plus-circle"
                                        color="#1A73E8" size="25"></u-icon>
                                    <u-icon v-if="idx == 1" @click="showModule(index)" name="close-circle-fill"
                                        size="25">
                                    </u-icon>
                                </view>
                            </view>
                        </view>
                        <view class="timeContent" v-if="item.rest">
                            <view class="timItems">
                                <view class="pItem" style="color: #D93025;">休業日</view>
                                <view class="pItem"></view>
                                <view class="pItem"></view>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <view class="warnning">
                <u-icon name="info-circle-fill" color="#D93025"></u-icon>
                <text>営業時間の設定が不足しています</text>
            </view>
            <u-button type="primary" text="設定を保存" class="btn" @click="save"></u-button>
            <view style="height: 100upx;"></view>
            <!-- 单出设置营业时间的弹窗 -->
            <u-popup :show="show" mode="bottom" @close="show = false" :safeAreaInsetTop="true" round="16">
                <view class="pContent">
                    <view class="title">
                        <view @click="show = false">
                            <image src="../../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" mode="">
                            </image>
                        </view>
                        <view class="titleName">
                            詳細
                        </view>
                        <view style="color: #1A73E8;" @click="saveSetting">
                            保存
                        </view>
                    </view>
                    <view class="dateInfo">
                        <view class="toweek">
                            {{current == 0? '日' : current == 1? '月' : current == 2? '火' : current == 3? '水' : current == 4? '木' : current == 5? '金' : '土'}}曜日
                        </view>
                    </view>
                    <view class="Cdate">
                        <view class="CdateItem" @click="changeStatus(false)">
                            <view class="selectContent">
                                <view class="unselect" v-if="takeRest"></view>
                                <view class="selected" v-if="!takeRest">
                                    <view class="fill"></view>
                                </view>
                            </view>
                            <view :style="takeRest? '' : 'color:#1D1D1F' ">
                                営業日
                            </view>
                        </view>
                        <view class="CdateItem" @click="changeStatus(true)">
                            <view class="selectContent">
                                <view class="unselect" v-if="!takeRest"></view>
                                <view class="selected" v-if="takeRest">
                                    <view class="fill"></view>
                                </view>
                            </view>
                            <view :style="takeRest? 'color:#1D1D1F' : '' ">
                                休業日
                            </view>
                        </view>
                    </view>

                    <view class="timePicker">
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
                            <view class="pickerItem" v-for="(item,index) in pickerArray">
                                <view class="pItem"></view>
                                <view class="pItem" v-text="item.start" @click="showPicker(index,'start')"></view>
                                <view class="pItem" v-text="item.end" @click="showPicker(index,'end')"></view>
                                <view class="pItem">
                                    <u-icon v-if="pickerArray.length == 1" @click="additem" name="plus-circle"
                                        color="#1A73E8" size="30"></u-icon>
                                    <u-icon v-if="index == 1" @click="delitem" name="close-circle-fill" size="30">
                                    </u-icon>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </u-popup>
            <u-picker :show="show1" @confirm="confirmPicker" @close="show1 = false" @cancel="show1 = false"
                :columns="timeArr"></u-picker>
        </view>
    </view>
</template>

<script>
    import {
        getCalendar,
        setBusinessHours
    } from '@/api/index.js'
    export default {
        data() {
            return {
                pickerArr: [],
                timeArr: [
                    ["00:00", "00:30", "01:00", "01:30", "02:00", "02:30", "03:00", "03:30", "04:00", "04:30",
                        "05:00", "05:30", "06:00", "06:30", "07:00", "07:30", "08:00", "08:30", "09:00", "09:30",
                        "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", "13:00", "13:30", "14:00", "14:30",
                        "15:00", "15:30", "16:00", "16:30", "17:00", "17:30", "18:00", "18:30", "19:00", "19:30",
                        "20:00", "20:30", "21:00", "21:30", "22:00", "22:30", "23:00", "23:30"
                    ]
                ],
                show: false,
                show1: false,
                takeRest: false,
                settingIndex: 0,
                settingStatus: "",
                current: 0,
                pickerArray: [{
                    start: "00:00",
                    end: "00:00"
                }],
            };
        },
        created() {
            this.getDetail()
        },
        methods: {
            getDetail() {
                let that = this
                getCalendar("week").then((res) => {
                    if (res.code == 200) {
                        let array = Object.keys(res.data.week_business).map((k) => res.data.week_business[
                            k]) //将对象转换成数组的方法
                        if (array.length < 7) {
                            that.pickerArr = [{
                                rest: false,
                                week: "日",
                                time: [{
                                    start: "00:00",
                                    end: "00:00"
                                }]
                            }, {
                                rest: false,
                                week: "月",
                                time: [{
                                    start: "00:00",
                                    end: "00:00"
                                }]
                            }, {
                                rest: false,
                                week: "火",
                                time: [{
                                    start: "00:00",
                                    end: "00:00"
                                }]
                            }, {
                                rest: false,
                                week: "水",
                                time: [{
                                    start: "00:00",
                                    end: "00:00"
                                }]
                            }, {
                                rest: false,
                                week: "木",
                                time: [{
                                    start: "00:00",
                                    end: "00:00"
                                }]
                            }, {
                                rest: false,
                                week: "金",
                                time: [{
                                    start: "00:00",
                                    end: "00:00"
                                }, ]
                            }, {
                                rest: false,
                                week: "土",
                                time: [{
                                    start: "00:00",
                                    end: "00:00"
                                }, ]
                            }]
                        } else {
                            let temp = []
                            array.forEach((value, index) => {
                                let obj = {}
                                obj.time = value.business_time
                                obj.rest = value.business == 0 ? true : false
                                obj.week = index == 0 ? '日' : index == 1 ? '月' : index == 2 ? '火' :
                                    index == 3 ? '水' : index == 4 ? '木' : index == 5 ? '金' : '土'
                                temp.push(obj)
                            })
                            that.pickerArr = temp
                        }
                    }
                })
            },

            save() {
                let that = this
                let obj = {}
                let temp = []
                let array = that.pickerArr
                array.forEach((value, index) => {
                    let object = {}
                    object.business = value.rest == true ? 0 : 1
                    object.business_time = value.time
                    object.key = index
                    temp.push(object)
                })
                let o = Object.fromEntries(temp.map(item => [item.key, item]))
                obj.week_business = o
                obj.type = "week"
                setBusinessHours(obj).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                        that.getDetail()
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },

            showModule(index) {
                let that = this
                let array = that.pickerArr
                that.show = true
                that.takeRest = array[index].rest
                that.current = index
                that.pickerArray = array[index].time
            },
            changeStatus(e) {
                let index = this.current
                this.takeRest = e
                this.pickerArr[index].rest = e
            },
            chooseItem(index, item) {
                let that = this
                that.pickerArr[index].rest = !that.pickerArr[index].rest
            },
            additem() {
                let that = this
                let obj = {}
                obj.start = "00:00"
                obj.end = "00:00"
                that.pickerArray.push(obj)
            },
            delitem() {
                let that = this
                that.pickerArray.splice(1, 1)
            },

            showPicker(index, status) {
                let that = this
                that.show1 = true
                that.settingIndex = index
                that.settingStatus = status
            },

            confirmPicker(e) {
                let that = this
                let index = that.settingIndex
                let status = that.settingStatus
                if (status == "start") {
                    that.pickerArray[index].start = e.value[0]
                } else {
                    that.pickerArray[index].end = e.value[0]
                }
                that.show1 = false
            },

            saveSetting() {
                let that = this
                let index = that.current
                that.pickerArr[index].rest = that.takeRest
                that.pickerArr[index].time = that.pickerArray
                that.show = false
            },
            goBack() {
                var pages = getCurrentPages();
                var currPage = pages[pages.length - 1]
                var prePage = pages[pages.length - 2]
                let data = {}
                data.type = "day"
                data.date = this.getMonth()
                prePage.$vm.getCalendar(data)
                uni.navigateBack({})
            },
            getMonth(){
              let that = this
              let date = new Date()
              let year = date.getFullYear()
              let month = (date.getMonth() + 1) < 10? '0'+ (date.getMonth() + 1) : date.getMonth() + 1
              return year + "-" + month
            },
        }
    }
</script>

<style lang="scss">
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

    .warnning {
        display: flex;
        align-items: center;
        margin: 42upx auto 158upx;
        text-align: center;
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #D93025;
        text-align: center;
        justify-content: center;
    }

    .btn {
        width: 480upx;
        height: 96upx;
        border-radius: 20upx;
    }

    .selectContent {
        margin-right: 20upx;
    }

    .tips {
        box-sizing: border-box;
        padding-top: 60upx;

        .tipContent {
            width: 656upx;
            height: 110upx;
            background: rgba(29, 29, 31, 0.1);
            border-radius: 20upx;
            opacity: 1;
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            box-sizing: border-box;
            padding: 20upx;
            margin: 0 auto 92upx;
        }
    }

    .timeContent {
        flex: 1;

        .timItems {
            display: flex;
            // margin-bottom: 40upx;
            align-items: center;
        }

        .timItems:nth-child(2) {
            margin-top: 40upx;
        }
    }

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
</style>
