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
                <view class="fu">オフにすると、すぐにメッセージが配信されます</view>
                <view class="can" v-if="value">
                    <view class="canLeft">
                        <image src="../../../../static/image/rili.png" mode=""></image>
                    </view>
                    <view style="box-sizing: border-box;padding: 0 20upx;" @click="chooseDate">
                        {{todayDate}}({{toweek}}) <text style="margin-left: 20upx;"
                            @click.stop="chooseTime">{{nowTime}}</text>
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
                <view class="contentItem" v-for="(item,index) in tabs" @click="toSceen(index)">
                    <view class="tabLeft">
                        <view class="tabTop" v-text="item.name"></view>
                        <view class="tabBot" v-text="item.title"
                            :style="item.title== '誕生月を設定'?'' : item.title== '性別を設定'?'' : item.title== '来店期間を設定'? '' : item.title == '来店回数を設定'? '': 'color:#1d1d1f !important'">
                        </view>
                    </view>
                    <view class="tabRight">
                        <image src="../../../../static/icons/right.png" mode=""></image>
                    </view>
                </view>
            </view>
            <u-button type="success" color="#34C759" class="btn" @click="show6 = true">メッセージを配信</u-button>
            <view class="blank"></view>
        </view>
        <u-calendar confirmText="完了" :show="show" :closeOnClickOverlay='true' monthNum="36" :showTitle="false"
            @close="closeDate" @confirm="confirmDate"></u-calendar>
        <u-datetime-picker @confirm="timeconfirm" :show="show1" :closeOnClickOverlay="true" v-model="value1" mode="time"
            @close="closeTime">
        </u-datetime-picker>
        <mod v-if="showModule" @getStatus="getStatus" :config="configs"></mod>
        <confirms :dates="value == true?todayDate +' '+ nowTime: '今すぐ'" v-if="show6 == true" :number="searchNumber"
            @confirm="saveData" @cancel="show6 = false"></confirms>
        <pays :dates="value == true?todayDate +' '+ nowTime: '今すぐ'" v-if="show7 == true" :number="searchNumber"
            @confirm="goPay" @cancel="show7 = false"></pays>
    </view>
</template>

<script>
    import mod from "../../../../components/mod.vue"
    import confirms from "@/components/alerts/pe1/pe1.vue"
    import pays from "@/components/alerts/pe2/pe2.vue"
    var googlePay = uni.requireNativePlugin('sn-googlepay5');
    import {
        addNewTiming,
        editTiming,
        getExtra,
        payExtra,
        sceneMember,
        buyextras,
        buyextra,
        getConfig
    } from "@/api/index.js"
    export default {
        components: {
            mod,
            confirms,
            pays
        },
        data() {
            return {
                pid: "",
                current: 0,
                value: true,
                show1: false,
                show2: false,
                show6: false,
                shows: false,
                show7: false,
                totalMember: 0,
                searchNumber: 0,
                value1: "",
                show: false,
                showModule: false,
                todayDate: '',
                nowTime: "",
                toweek: "",
                num: 102,
                query: {},
                ids: ['premium_halfyear', 'premium_month', 'standard_halfyear', 'standard_month', 'light_halfyear',
                    'light_months', 'message'
                ],
                orid: "",
                configs: {
                    title: 'この情報を削除しますか？',
                    content: '削除すると、この情報は完全に削除されます。',
                    confirm: true,
                    cancel: true,
                    confirmText: "情報を削除",
                    cancelText: "キャンセル",
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
                is_comment_button:0
            }
        },
        created() {

        },
        onLoad(options) {
            let that = this
            that.getAllmember()
            let timing = uni.getStorageSync("timing")
            let query = options
            that.query = query
            if (query.source == "edit") {
                that.current = timing.receiver * 1 - 1
                let fullTime = timing.order_time.split(" ")
                that.todayDate = fullTime[0]
                that.nowTime = fullTime[1]
                that.getWeek(fullTime[0])
                that.value = timing.is_order == 1 ? true : false
            } else if (query.source == "add") {
                this.getToday()
            }
            uni.removeStorageSync("csvData")
            let data = {}
            data['birthday_month'] = ''
            data['sex'] = ''
            data['shop_time'] = ""
            data['shop_time_desc'] = 3
            data['shop_count'] = ""
            data['shop_count_desc'] = 3
            uni.setStorageSync("csvData", data)
            googlePay.init({}, (e) => {
                if (e.code == 0) {
                    // 初始化成功
                } else {
                    // 初始化失败
                }
            });
            googlePay.isFeatureSupported((e) => {
            });
        },
        onShow() {
            let that = this
            getConfig().then((res) => {
                if (res.code == 200) {
                    let pt = uni.getStorageSync("platform")
                    if (pt == 'ios') {
                        that.pid = res.data.config.vip_count.message.product_id[2]
                    } else {
                        that.pid = res.data.config.vip_count.message.product_id[3]
                    }

                }
            })
            that.sceenMember()
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
                    this.restoreComplateRequest();
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
                        
                    },
                    complete: () => {
                        this.loading = false;
                        this.restoreComplateRequest();
                    }
                })
            },
            // 获取已购的非消耗性商品和订阅商品
            restoreComplateRequest() {
                let that = this
                this.iap.restoreComplateRequest({
                    manualFinishTransaction: true
                }, results => {
                    // results 格式为数组存放恢复的IAP商品交易信息对象 IAPTransaction，通用需将返回的支付凭证传给后端进行二次认证  
                    for (let i = 0; i < results.length; i++) {
                        that.iap.finishTransaction(results[i], (success) => {
                        }, (fail) => {
                        });
                    }
                }, e => {
                    // 错误回调
                });
            },
            sceenMember() {
                let that = this
                let csvData = uni.getStorageSync("csvData")
                let data = {}
                if (csvData) {
                    data['birthday_month'] = csvData.birthday_month || ''
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
            goPay() {
                let that = this
                uni.showLoading({
                    title: "購入処理実施中..."
                })
                let plat = uni.getStorageSync("platform")
                if (plat != 'ios') {
                    var pId = that.pid;
                    let platform = plat == 'ios' ? 2 : 3
                    let orid = ''
                    let data = {}
                    data.type = 1
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
                                                            that.show7 = false
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
                    data.type = 1
                    data.card_type = 2
                    buyextras(data).then((res) => {
                        if (res.code == 200) {
                            that.getChannels()
                            that.orid = res.data.order_no
                        }
                    })
                }

            },
            toSceen(e) {
                if (e == 0) {
                    uni.navigateTo({
                        url: "birthday/birthday"
                    })
                } else if (e == 1) {
                    uni.navigateTo({
                        url: "sex/sex"
                    })
                } else if (e == 2) {
                    uni.navigateTo({
                        url: "time/time"
                    })
                } else {
                    uni.navigateTo({
                        url: "times/times"
                    })
                }
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
            chooseDate(e) {
                this.show = true
            },
            closeDate() {
                this.show = false
            },
            chooseTime() {
                this.show1 = true
            },
            closeTime() {
                this.show1 = false
            },
            getStatus(e) {},
            timeconfirm(e) {
                this.show1 = false
                this.nowTime = e.value
            },
            getToday() {
                let that = this
                let date = new Date()
                let year = date.getFullYear()
                let month = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                let day = date.getDate() < 10 ? '0' + date.getDate() : date.getDate()
                let week = date.getDay() == 0 ? '日' : date.getDay() == 1 ? '月' : date.getDay() == 2 ? "火" : date
                    .getDay() == 3 ? "水" : date.getDay() == 4 ? "木" : date.getDay() == 5 ? '金' : '土'
                let hour = date.getHours() < 10 ? "0" + date.getHours() : date.getHours()
                let minutes = date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()
                let final = year + "-" + month + "-" + day
                that.toweek = week
                that.todayDate = final
                that.nowTime = hour + ":" + minutes
            },
            getWeek(e) {
                let that = this
                let date = new Date(e)
                let week = date.getDay() == 0 ? '日' : date.getDay() == 1 ? '月' : date.getDay() == 2 ? "火" : date
                    .getDay() == 3 ? "水" : date.getDay() == 4 ? "木" : date.getDay() == 5 ? '金' : '土'
                that.toweek = week
                that.todayDate = e
            },
            confirmDate(e) {
                this.show = false
                this.getWeek(e[0])
            },
            saveData() {
                let that = this
                let data = uni.getStorageSync("timing")
                data.is_order = that.value == true ? 1 : 0
                data.order_time = that.todayDate + " " + that.nowTime + ":00"
                data.receiver = that.current + 1
                if (that.query.source == 'add') {
                    addNewTiming(data).then((res) => {
                        that.show6 = false
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            setTimeout(function() {
                                // let pages = getCurrentPages();
                                // let currPage = pages[pages.length - 1] //当前页面
                                // let prePage = pages[pages.length - 3] //上一个页面
                                // //调用上一页拉取数据的方法
                                // prePage.$vm.getList()
                                // uni.navigateBack({})
                                uni.navigateBack()
                            }, 1500)
                            uni.removeStorageSync("csvData")
                        } else if (res.code == 4003) {
                            that.show7 = true
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                            uni.removeStorageSync("csvData")
                        }
                    })
                } else if (that.query.source == 'edit') {
                    data._method = "put"
                    editTiming(data, that.query.id).then((res) => {
                        that.show6 = false
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            setTimeout(function() {
                                uni.navigateBack()
                            }, 1500)
                            uni.removeStorageSync("timing")
                        } else if (res.code == 4003) {
                            that.show7 = true
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                }
            }
        }
    }
</script>

<style lang="scss">
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
                width: 80upx;
                display: flex;
                align-items: center;
                justify-content: center;
                
                image{
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
            width: 478upx;
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