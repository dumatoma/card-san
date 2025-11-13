<template>
    <view>
        <view class="container">
            <view class="memCount">
                <view class="mem1">
                    メンバー数
                </view>
                <view class="mem2">
                    <text>{{searchNumber}}</text>/{{totalMember}}
                </view>
            </view>
            <view class="appointSet">
                <view>予約配信</view>
                <u-switch v-model="value" activeColor="#34C759"></u-switch>
            </view>
            <view class="date">
                <view class="fu">オフにすると、すぐにクーポンが配信されます</view>
                <view class="can" v-if="value == true">
                    <view class="canLeft">
                        <image class="calogo" src="../../../static/svg/calogo.svg" mode="aspectFill"></image>
                    </view>
                    <view style="box-sizing: border-box;padding: 0 20upx;" @click="chooseDate">
                        {{date}} <text style="margin-left: 40upx;" @click.stop="chooseTime">{{time}}</text>
                    </view>
                </view>
            </view>
            <view class="title">
                配信ターゲット
            </view>
            <view class="chooseItem">
                <view class="item">
                    <view class="itemLeft">
                        <view class="unselect" @click="changeTab(0)" v-if="current == 1"></view>
                        <view class="selected" v-if="current == 0">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view class="itemRight" :style="current == 0? 'color:#1D1D1F;' : 'color: #AAAAAA' ">
                        すべて
                    </view>
                </view>
                <view class="item">
                    <view class="itemLeft">
                        <view class="unselect" @click="changeTab(1)" v-if="current == 0"></view>
                        <view class="selected" v-if="current == 1">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view class="itemRight" :style="current == 1? 'color:#1D1D1F;' : 'color: #AAAAAA' ">
                        絞り込み
                    </view>
                </view>
            </view>
            <view class="content" v-if="current == 1">
                <view class="contentItem" v-for="(item,index) in tabs" @click="toScene(index)">
                    <view class="tabLeft">
                        <view class="tabTop" v-text="item.name"></view>
                        <view class="tabBot"
                            :style="item.title== '誕生月を設定'?'' : item.title== '性別を設定'?'' : item.title== '来店期間を設定'? '' : item.title == '来店回数を設定'? '': 'color:#1d1d1f !important'"
                            v-text="item.title"></view>
                    </view>
                    <view class="tabRight">
                        <image src="../../../static/icons/right.png" mode="aspectFit"></image>
                    </view>
                </view>
            </view>
            <u-button color="#D93025" @click="showMod" class="btn">クーポンを配信</u-button>
            <view class="blank"></view>
        </view>
        <u-calendar confirmText="完了" :show="showc" :closeOnClickOverlay='true' monthNum="36" :showTitle="false"
            @close="showc = false" @confirm="dateConfirm" color="#1A73E8"></u-calendar>
        <u-datetime-picker :show="showd" :closeOnClickOverlay="true" v-model="value1" @confirm="pickerConfirm"
            mode="time" @cancel="showd = false">
        </u-datetime-picker>
        <mod v-if="showModule" @getStatus="getStatus" :config="configs"></mod>
        <aleOne v-if="show1" :sendDate="date+time" :isToday="value" :number="searchNumber"></aleOne>
        <aleTwo v-if="show2" :dates="date+time" :isToday="value" @confirm="buythis" @cancel="canThis"
            :number="searchNumber"></aleTwo>
    </view>
</template>

<script>
    var googlePay = uni.requireNativePlugin('sn-googlepay5');
    import mod from "../../../components/mod.vue"
    import aleOne from "../../../components/alerts/you1/you1.vue"
    import aleTwo from "../../../components/alerts/you2/you2.vue"
    import {
        sceneMember,
        getMemberList,
        addCoupon,
        editCoupon,
        getExtra,
        payExtra,
        sceenMember,
        buyextras,
        buyextra,
        getConfig,
        getShopInfo
    } from '@/api/index.js'
    export default {
        components: {
            mod,
            aleOne,
            aleTwo
        },
        data() {
            return {
                current: 0,
                value: true,
                showd: false,
                show1: false,
                show2: false,
                showc: false,
                date: "",
                sdate: "",
                sDate: "",
                totalMember: 0,
                searchNumber: 0,
                time: "00:00",
                value1: "",
                pid: "",
                show: false,
                showModule: false,
                isToday: true,
                configs: {
                    title: 'クーポンを配信',
                    content: 'このクーポンは 今すぐ300人に送信されます。 配信しますか？',
                    cancel: true,
                    default: true,
                    cancelText: "配信",
                    defaultText: "キャンセル",
                    type: 'button'
                },
                tabs: [{
                        name: "誕生月",
                        title: "誕生月を設定"
                    },
                    {
                        name: "性別",
                        title: "性別を設定"
                    },
                    {
                        name: "来店期間",
                        title: "来店期間を設定"
                    },
                    {
                        name: "来店回数",
                        title: "来店回数を設定"
                    }
                ],
                ids: ['premium_halfyear', 'premium_month', 'standard_halfyear', 'standard_month', 'light_halfyear',
                    'light_months', 'coupon'
                ],
                orid: ""
            }
        },

        onLoad(options) {
            let that = this
            that.getAllmember()

            let info = uni.getStorageSync("couponInfo")
            let config = uni.getStorageSync("csvData")
            if (config) {

            } else {
                uni.removeStorageSync("csvData")
                let data = {}
                data['birthday_month'] = ''
                data['sex'] = ''
                data['shop_time'] = ""
                data['shop_time_desc'] = 3
                data['shop_count'] = ""
                data['shop_count_desc'] = 3
                uni.setStorageSync("csvData", data)
            }
            let param = options.source
            that.query = options
            if (param == 'edit') {
                let times = config.order_time.split(" ")
                that.current = config.receiver
                that.date = times[0]
                that.sdate = times[0]
                that.time = times[1]
                if (that.current == 1) {
                    that.checkMemberCount()
                } else {
                    this.member = this.allMember
                }
            } else {
                if (config.sendDate) {
                    that.date = config.sendDate
                } else {
                    let date = new Date()
                    let fullDate
                    let year = date.getFullYear()
                    let month = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                    let day = date.getDate() < 10 ? "0" + (date.getDate()) : date.getDate()
                    let week = date.getDay() == 1 ? '月' : date.getDay() == 2 ? '火' : date.getDay() == 3 ? '水' : date
                        .getDay() ==
                        4 ? '木' : date.getDay() == 5 ? '金' : date.getDay() == 6 ? '土' : '日'
                    fullDate = year + "-" + month + '-' + day + "(" + week + ")"
                    that.sDate = year + "-" + month + '-' + day
                    that.date = fullDate
                    that.sdate = year + "-" + month + '-' + day
                }

                if (config.sendTiem) {
                    that.time = config.sendTiem
                }
            }
            googlePay.init({}, (e) => {
                if (e.code == 0) {
                    // 初始化成功
                } else {
                    // 初始化失败
                }
            });
            googlePay.isFeatureSupported((e) => {});

        },
        onShow() {
            let that = this
            getShopInfo().then((res) => {
                let status = uni.getStorageSync("ale")
                uni.hideLoading()
                console.log(res)
                if (res.code == 200) {
                    that.smsid = res.data.vip_coupon_config.product_id
                } else {

                }
            })
            that.sceenMember()
            getConfig().then((res) => {
                if (res.code == 200) {
                    let pt = uni.getStorageSync("platform")
                    if (pt == 'ios') {
                        that.pid = res.data.config.vip_count.coupon.product_id[2]
                    } else {
                        that.pid = res.data.config.vip_count.coupon.product_id[3]
                    }
                }
            })
            let csvData = uni.getStorageSync("csvData")
            if (csvData.birthday_month) {
                that.tabs[0].title = csvData.birthday_month1
            } else {
                that.tabs[0].title = '誕生月を設定'
            }

            if (csvData.sex) {
                that.tabs[1].title = csvData.sex1
            } else {
                that.tabs[1].title = '性別を設定'
            }

            if (csvData.shop_time && csvData.shop_time_desc != 3) {
                that.tabs[2].title = '前回来店から' + csvData.shop_time / 86400 + '日' + (csvData.shop_time_desc == 2 ?
                    '以上経過' : '以内')
            } else {
                that.tabs[2].title = '来店期間を設定'
            }

            if (csvData.shop_count && csvData.shop_count_desc != 3) {
                that.tabs[3].title = csvData.shop_count + '回' + (csvData.shop_count_desc == 2 ? '以上' : '以下')
            } else {
                that.tabs[3].title = '来店回数を設定'
            }
        },
        methods: {
            // 获取支付通道
            getChannels() {
                // #ifdef APP-PLUS
                plus.payment.getChannels(res => {
                    let channel = res.find(i => i.id === 'appleiap')
                    this.iap = channel ? channel : null
                    this.restoreComplateRequest()
                    this.requestOrder()
                }, function(e) {
                    plus.nativeUI.alert("支払いチャネルの取得に失敗しました。しばらくしてからもう一度お試しください。", function() {}, "エラー");
                })
                // #endif
            },
            // 获取内购项目列表
            requestOrder() {
                uni.showLoading({
                    title: '購入処理実施中...'
                })
                // #ifdef APP-PLUS
                this.iap.requestOrder(
                    this.ids,
                    res => {
                        uni.hideLoading()
                        this.product_list = res;
                        this.pay();
                    },
                    (errormsg) => {
                        uni.hideLoading()
                        plus.nativeUI.alert("購入項目の取得に失敗しました。しばらくしてからもう一度お試しください。", function(e) {}, "エラー");
                    }
                )
                // #endif
            },
            // 调起支付
            pay() {
                let that = this
                uni.requestPayment({
                    provider: 'appleiap',
                    orderInfo: {
                        productid: that.pid
                    },
                    success: (e) => {
                        let da = {}
                        da.order_no = that.orid
                        da.pay_token = e.transactionReceipt
                        da.transaction_id = e.transactionIdentifier
                        buyextra(da).then((re) => {
                            uni.hideLoading()
                            if (re.code == 200) {
                                uni.showToast({
                                    title: re.message,
                                    duration: 2500,
                                    icon: "none"
                                })
                                that.sms = false
                            } else {
                                uni.showToast({
                                    title: re.message,
                                    icon: "none"
                                })
                            }
                        })
                    },
                    fail: (e) => {
                        uni.showModal({
                            content: '支払いに失敗しました。再度お試しください。',
                            showCancel: false
                        })
                        this.restoreComplateRequest();
                    },
                    complete: () => {
                        this.loading = false;
                    }
                })
            },
            // 获取已购的非消耗性商品和订阅商品
            restoreComplateRequest() {
                let that = this
                this.iap.restoreComplateRequest({
                    manualFinishTransaction: true
                }, results => {
                    for (let i = 0; i < results.length; i++) {
                        that.iap.finishTransaction(results[i], (success) => {}, (fail) => {});
                    }
                }, e => {
                    // 错误回调
                });
            },
            showMod() {
                let that = this
                if (that.value != true) {
                    this.configs.content = "<div>このクーポンは今すぐ" + this.searchNumber + "人に送信されます。</div><div> 配信しますか？</div>"
                    this.configs.cancelText = "配信"
                } else {
                    this.configs.content = "<div>このクーポンは</div><div> " + that.date + that.time + "</div><div>" + that
                        .searchNumber + "人に送信されます。 配信しますか？</div>"
                    this.configs.cancelText = "配信を予約"
                }
                this.showModule = true

            },
            changeTab(e) {
                let that = this
                this.current = e
                if (e == 0) {
                    that.searchNumber = that.totalMember
                } else {
                    that.sceenMember()
                }
            },
            sceenMember() {
                let that = this
                let csvData = uni.getStorageSync("csvData")
                let data = {}
                let months = csvData.birthday_month.split(",")
                for (let i = 0; i < months.length; i++) {
                    if (months[i] < 10) {
                        months[i] = "0" + months[i]
                    }
                }
                let smonths = months.join()
                console.log("smonths", smonths)
                if (csvData) {
                    data['birthday_month'] = smonths || ''
                    data['sex'] = csvData.sex || ''
                    data['shop_time'] = csvData.shop_time_desc == 3 ? '' : csvData.shop_time
                    data['shop_time_desc'] = csvData.shop_time_desc == 3 ? '' : csvData.shop_time_desc
                    data['shop_count'] = csvData.shop_count_desc == 3 ? '' : csvData.shop_count
                    data['shop_count_desc'] = csvData.shop_count_desc == 3 ? '' : csvData.shop_count_desc
                } else {
                    data['birthday_month'] = ""
                    data['sex'] = ""
                    data['shop_time'] = ""
                    data['shop_time_desc'] = ""
                    data['shop_count'] = ""
                    data['shop_count_desc'] = ""
                }
                uni.showLoading({
                    title: "読み込み中"
                })
                sceneMember(data).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        that.searchNumber = res.data.total
                    }
                })
            },
            getAllmember() {
                let that = this
                let data = {}
                data['birthday_month'] = ''
                data['sex'] = ''
                data['shop_time'] = ''
                data['shop_time_desc'] = ''
                data['shop_count'] = ''
                data['shop_count_desc'] = ''
                uni.showLoading({
                    title: "読み込み中"
                })
                sceneMember(data).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        if (res.code == 200) {
                            that.totalMember = res.data.total
                        }
                    }
                })
            },
            buythis(e) {
                let that = this
                uni.showLoading({
                    title: "読み込み中"
                })
                let plat = uni.getStorageSync("platform")
                if (plat != "ios") {
                    var pId = that.pid;
                    let platform = plat == 'ios' ? 2 : 3
                    let orid = ''
                    let data = {}
                    data.type = 2
                    data.card_type = platform
                    buyextras(data).then((res) => {
                        if (res.code == 200) {
                            orid = res.data.order_no
                            googlePay.querySku({
                                    inapp: [pId]
                                },
                                (e) => {
                                    if (e.code == 0) {
                                        googlePay.pay({
                                                productId: pId, // 产品id
                                            },
                                            (e) => {
                                                if (e.msg == 'success') {
                                                    let da = {}
                                                    da.order_no = orid
                                                    da.pay_token = e.data[0].original.purchaseToken
                                                    da.product_id = pId
                                                    buyextra(da).then((re) => {
                                                        uni.hideLoading()
                                                        if (re.code == 200) {
                                                            uni.showToast({
                                                                title: re.message,
                                                                duration: 2500,
                                                                icon: "none"
                                                            })
                                                            that.show2 = false
                                                        } else {
                                                            uni.showToast({
                                                                title: re.message,
                                                                icon: "none"
                                                            })
                                                        }
                                                    })
                                                }
                                            }
                                        );
                                    } else {
                                        //查询失败
                                        uni.showToast({
                                            title: 'querySku fail,' + e
                                        })
                                    }
                                }
                            );
                        }
                    })
                } else {
                    let data = {}
                    data.type = 2
                    data.card_type = 2
                    buyextras(data).then((res) => {
                        if (res.code == 200) {
                            that.getChannels()
                            that.orid = res.data.order_no
                        }
                    })
                }

            },
            canThis(e) {
                this.show2 = false
            },
            chooseDate(e) {
                this.showc = true
            },
            closeDate() {
                this.show = false
            },
            chooseTime() {
                this.showd = true
            },
            closeTime() {
                this.showd = false
            },
            getStatus(e) {
                let that = this
                if (e == "default") {
                    that.showModule = false
                }

                if (e == "cancel") {
                    let data = {}
                    // let query = that.query.source == "edit"?'edit':''

                    let config = uni.getStorageSync("csvData")
                    let info = uni.getStorageSync("couponInfo")
                    console.log("co", config)
                    let birthday = config.birthday_month
                    let sexStr = config.sex
                    //  if(config.birthday_month){
                    //      config.birthday_month.forEach((value,index) => {
                    //          if(index ==  config.birthday_month.length - 1){
                    //              birthday += value
                    //          }else{
                    //              birthday += value + ","
                    //          }
                    //      })
                    //  }else{
                    //      birthday = ""
                    //  }


                    //  if(config.sex.length > 0){
                    //      config.sex.forEach((value,index) => {
                    //          if(index ==  config.sex.length - 1){
                    //              sexStr += value
                    //          }else{
                    //              sexStr += value + ","
                    //          }
                    //      })
                    //  }else{
                    //      sexStr = ""
                    //  }

                    data.images = info.banner
                    data.title = info.title
                    data.number = info.number
                    data.is_indate = info.value == true ? 1 : 0
                    data.indate_start_time = info.sDate
                    data.indate_end_time = info.eDate
                    data.is_bar_code = info.value3 == true ? 1 : 0
                    data.count = info.value2 == true ? info.count : 0
                    data.des = info.detail
                    data.remark = info.remark
                    data.is_order = that.value == true ? 1 : 0
                    data.order_time = that.sdate + ' ' + that.time
                    data.receiver = that.current == 0 ? 1 : 2
                    data.birthday_month = birthday
                    data.sex = sexStr
                    data.shop_time = config.shop_time
                    data.shop_time_desc = config.shop_time_desc
                    data.shop_count = config.shop_count
                    data.shop_count_desc = config.shop_count_desc
                    if (that.query.source == "edit") {
                        let id = that.query.id
                        data._method = "put"
                        editCoupon(data, id).then((res) => {
                            if (res.code == 200) {
                                uni.showToast({
                                    title: res.message,
                                    duration: 2000
                                })
                                uni.removeStorageSync("csvData")
                                uni.removeStorageSync("couponInfo")
                                setTimeout(function() {
                                    // uni.redirectTo({
                                    //     url:"../../coupon/coupon"
                                    // })
                                    uni.navigateBack()
                                }, 2000)
                            } else if (res.code == '4003') {
                                that.show2 = true
                            } else {
                                uni.showToast({
                                    title: res.message,
                                    icon: "none"
                                })
                            }
                        })
                    } else {
                        addCoupon(data).then((res) => {
                            if (res.code == 200) {
                                uni.showToast({
                                    title: res.message,
                                    duration: 2000
                                })
                                uni.removeStorageSync("csvData")
                                uni.removeStorageSync("couponInfo")
                                setTimeout(function() {
                                    // uni.redirectTo({
                                    //     url:"../../coupon/coupon"
                                    // })
                                    uni.navigateBack()
                                }, 2000)
                            } else if (res.code == "4003") {
                                that.show2 = true
                            } else {
                                uni.showToast({
                                    title: res.message,
                                    icon: "none"
                                })
                            }
                        })
                    }
                }
            },
            dateConfirm(e) {
                let that = this
                let d = ""
                that.showc = false
                that.isToday = false
                let t = e[0].split('-')
                t.forEach((v, i) => {
                    if (i == 2) {
                        d += v
                    } else {
                        d += v + "/"
                    }
                })
                let date = new Date(d)
                let week = date.getDay()
                let wek
                if (week == 1) {
                    wek = "月"
                } else if (week == 2) {
                    wek = "火"
                } else if (week == 3) {
                    wek = "水"
                } else if (week == 4) {
                    wek = "木"
                } else if (week == 5) {
                    wek = "金"
                } else if (week == 6) {
                    wek = "土"
                } else if (week == 7) {
                    wek = "日"
                }
                that.date = e[0] + "(" + wek + ")"
                that.sdate = e[0]
            },

            pickerConfirm(e) {
                let that = this
                that.time = e.value
                that.showd = false
            },
            toScene(e) {
                let sceneCouponInfo = uni.getStorageSync("csvData")

                if (!sceneCouponInfo) {
                    uni.setStorageSync("csvData", {
                        "stype": 1
                    })
                } else {}


                if (e == 0) {
                    uni.navigateTo({
                        url: "../setting/birthday/birthday"
                    })
                } else if (e == 1) {
                    uni.navigateTo({
                        url: "../setting/sex/sex"
                    })
                } else if (e == 2) {
                    uni.navigateTo({
                        url: "../setting/time/time"
                    })
                } else if (e == 3) {
                    uni.navigateTo({
                        url: "../setting/times/times"
                    })
                }
            },

            checkMemberCount() {
                let that = this
                let config = uni.getStorageSync("csvData")
                let temp = [1, 2, 3]
                let aa = 1
                let data = {}
                data.birthday_month = config.birthday_month || []
                data.sex = config.sex || []
                data.shop_time = config.shop_time || ""
                data.shop_time_desc = config.shop_time_desc || 1
                data.shop_count = config.shop_count || ""
                data.shop_count_desc = config.shop_count_desc || 1
                sceneMember(data).then((res) => {
                    if (res.code == 200) {
                        that.member = res.data.total
                    }
                })

            }
        }
    }
</script>

<style lang="scss" scoped>
    .container {
        border-top: 2upx solid #D2D2D7;
        box-sizing: border-box;
        padding-top: 24upx;

        .cons {
            text-align: center;
        }

        .can {
            width: 100%;
            height: 80upx;
            border-radius: 20upx;
            display: flex;
            align-items: center;
            // justify-content: space-between;
            border: 2upx solid #aaa;
            overflow: hidden;
            margin: 34upx 0 60upx;

            .canLeft {
                background: #EAEAEA;
                text-align: center;
                border-right: 2upx solid #aaa;
                height: 100%;
                line-height: 80upx;
                width: 80upx;
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: center;

                image {
                    width: 40rpx;
                    height: 40rpx;
                }
            }
        }

        .fu {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: rgba(29, 29, 31, 0.9);
        }

        .appointSet {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 43upx;
            border-bottom: 2upx solid #d2d2d7;
            padding-bottom: 30upx;
        }

        .blank {
            width: 100%;
            height: 50upx;
        }

        .btn {
            width: 646upx;
            height: 96upx;
            background: rgba(29, 29, 31, 0.1);
            border-radius: 20upx;
            opacity: 1;
            border: 2upx solid #707070;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-top: 144upx;
        }

        .content {
            border-left: 2upx solid #D2D2D7;
            border-right: 2upx solid #D2D2D7;
            border-top: 2upx solid #D2D2D7;
            margin: 32upx 0 0;
        }

        .contentItem {
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
            padding: 32upx;
            border-bottom: 2upx solid #D2D2D7;

            .tabLeft {
                .tabTop {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                    margin-bottom: 20upx;
                }

                .tabBot {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #AAAAAA;
                }
            }

            .tabRight {
                image {
                    height: 46upx;
                    width: 23upx;
                }
            }
        }


        .item {
            display: flex;
            align-items: center;
            margin-top: 60upx;

            .itemLeft {
                margin-right: 28upx;
            }

            .unselect {
                width: 40upx;
                height: 40upx;
                background: #FFFFFF;
                opacity: 1;
                box-sizing: border-box;
                border: 2upx solid #707070;
                border-radius: 50%;
            }

            .selected {
                width: 40upx;
                height: 40upx;
                background: #FFFFFF;
                border: 2upx solid #1A73E8;
                border-radius: 50%;
                box-sizing: border-box;
                padding: 6upx;

                .fill {
                    width: 100%;
                    height: 100%;
                    background: #1A73E8;
                    border-radius: 50%;
                }
            }

        }

        .mem1 {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            text-align: right;
            margin-bottom: 14upx;
        }

        .mem2 {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            text-align: right;

            text {
                color: #1D1D1F;
            }
        }

        .title {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-top: 40upx;
            border-bottom: 2upx solid #d2d2d7;
            padding-bottom: 22upx;
        }
    }
</style>