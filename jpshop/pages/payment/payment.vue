<template>
    <view class="container">
        <view class="item">
            <view class="item_title">
                プラン
            </view>
            <view class="item_detail">
                <view class="detail_item">
                    <view class="detail_item_left">
                        選択プラン：
                    </view>
                    <view class="detail_item_right">
                        {{type == 1? 'ライト' : type == 2? 'スタンダード' : 'プレミアム'}}
                    </view>
                </view>
                <view class="checkMore" @click="checkMore">
                    プランを変更
                </view>
            </view>
        </view>
        <view class="item">
            <view class="item_title">
                お支払い周期を選択してください
            </view>
            <view class="item_list">
                <view class="list_item" style="display: none;">
                    <view class="selectContent" @click="chooseTime(12)">
                        <view class="selectInner" v-if="current == 12"></view>
                    </view>
                    <view class="selectTime">
                        1年
                    </view>
                    <view class="selectPrice">
                        ¥{{ formatPriceWithCommas(price3 / 12)  }} × 12ヶ月
                    </view>
                    <view class="selectDisccount">
                        ¥{{ formatPriceWithCommas(price1 * 12 - price3)}} お得！
                    </view>
                </view>

                <view class="list_item">
                    <view class="selectContent" @click="chooseTime(6)">
                        <view class="selectInner" v-if="current == 6"></view>
                    </view>
                    <view class="selectTime">
                        6ヶ月
                    </view>
                    <view class="selectPrice">
                        ¥{{ formatPriceWithCommas(price2 / 6)}} × 6ヶ月
                    </view>
                    <view class="selectDisccount">
                        ¥{{ formatPriceWithCommas(price1 * 6 - price2) }} お得！
                    </view>
                </view>

                <view class="list_item">
                    <view class="selectContent" @click="chooseTime(1)">
                        <view class="selectInner" v-if="current == 1"></view>
                    </view>
                    <view class="selectTime">
                        月額
                    </view>
                    <view class="selectPrice">
                        ¥{{ formatPriceWithCommas(price1)}} × 1ヶ月
                    </view>
                    <view class="selectDisccount"></view>
                </view>
            </view>
        </view>
        <view class="item">
            <view class="item_title">
                お支払い方法
            </view>
            <view class="paymethod">
                <view class="payItem">
                    App 課金
                </view>
            </view>
        </view>
        <view class="item pd" style="box-shadow: 0rpx 0rpx 20rpx 2rpx rgba(0,0,0,0.2);">
            <view class="detaTitle">
                ご契約内容
            </view>
            <view class="deta">
                <view class="detaItem">
                    {{type == 1? 'ライト' : type == 2? 'スタンダード' : 'プレミアム'}}
                </view>
                <view class="detaItem" v-if="current == 1" style="font-weight: bold;">
                    ¥{{ formatPriceWithCommas(price1) }} (1ヶ月)
                </view>
                <view class="detaItem" v-if="current == 6" style="font-weight: bold;">
                    ¥{{ formatPriceWithCommas(price2) }} (6ヶ月)
                </view>
                <view class="detaItem" v-if="current == 12" style="font-weight: bold;">
                    ¥{{formatPriceWithCommas(price3)}} (12ヶ月)
                </view>
            </view>
           <view class="deta">
                <view class="detaItem coblue" style="font-size: 14px !important;">
                    契約更新日
                </view>
                <view class="detaItem coblue" style="font-weight: bold;font-size: 14px !important;" v-text="refreshTime"></view>
            </view>
            <view class="deta" v-if="vips.length == 2"  style="font-size: 14px !important;">
                <text style="color: #D93025;font-size: 14px !important;" >※</text>
                <view class="wor"  style="font-size: 14px !important;">
                    ご契約内容の変更は、上記の契約更新日から適用されます。
                </view>
            </view>
            <view class="deta" v-if="vips.length == 1 && platform == 'ios' && type > vips[0].type">
                <text style="color: #D93025;">※</text>
                <view class="wor">
                    アップグレードは今すぐ開始されます。現在のサブスクリプションの残りの期間分の料金は返金されます。
                </view>
            </view>

            <view class="deta" v-if="vips.length == 1 && platform == 'android' && type > vips[0].type">
                <text style="color: #D93025;">※</text>
                <view class="wor">
                    アップグレードは今すぐ開始されます。現在のサブスクリプションの残りの期間分の料金と新しいサブスクリプションの料金の差額分をお支払いください。
                </view>
            </view>
        </view>
        <view @click="toxie" class="xie" hover-class="xie1">
            利用規約・プライバシーポリシー
        </view>
        <u-button class="bt" type="primary" @click="toSuccess1" shape="circle"> この内容でお申し込み</u-button>
        <view style="height: 160upx;"></view>
        <u-popup :show="show" @close="show = false">
            <subscribe @btmnclick="changeS" :title="title" :content="content"></subscribe>
        </u-popup>
    </view>
</template>

<script>
    var googlePay = uni.requireNativePlugin('sn-googlepay5');
    import {
        getShopInfo,
        getPackageList,
        getCardInfo,
        buyPlan,
        buyPlans,
        getConfig
    } from "@/api/index.js"
    import subscribe from "@/components/su1b/su1b.vue"
    export default {
        components: {
            subscribe
        },
        data() {
            return {
                type: 0,
                show: false,
                price1: "",
                price2: "",
                price3: "",
                selectItem: {},
                current: 6,
                paymethod: 1,
                inp1: "",
                inp2: "",
                inp3: "",
                inp4: "",
                using: {},
                vips: [],
                platform: "",
                title: "",
                content: "",
                pid:"",
                iap:null,
                svid:"",
                ids: ['premium_halfyear', 'premium_month', 'standard_halfyear','standard_month','light_halfyear','light_months'],
                endDate:"",
                timestamp:"",
                refreshTime:"",
                paying: false
            }
        },
        onLoad(options) {
            let that = this
            that.type = options.type
            that.getPlanList(options.type)
            that.getCardInfo()
        },
        onShow() {
            let that = this
            that.platform = uni.getStorageSync("platform")
            if(that.platform != "ios"){
                googlePay.init({}, (e) => {
                    if (e.code == 0) {
                        // 初始化成功
                        console.log("初始化成功")
                    } else {
                        // 初始化失败
                    }
                });
                googlePay.isFeatureSupported((e) => {
                });
            }
            getConfig().then((res) => {
                if(res.code == 200){
                    that.timestamp = res.data.config.timestamp
                    that.getShopInfo()
                }
            })
        },
        methods: {
            formatPriceWithCommas(price) {
              return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            },
            // 获取支付通道
            getChannels() {
                // #ifdef APP-PLUS
                plus.payment.getChannels(res => {
                    let channel = res.find(i => i.id === 'appleiap')
                    this.iap = channel ? channel : null
                    this.requestOrder()
                }, function(e) {
                    plus.nativeUI.alert("Failed to obtain payment channel, please try again later.", function() {}, "warning");
                })
                // #endif
            },
            // 获取内购项目列表
            requestOrder() {
                uni.showLoading({
                    title: 'Detecting payment environment'
                })
                // #ifdef APP-PLUS
                this.iap.requestOrder(
                    this.ids,
                    res => {
                        uni.hideLoading()
                        this.product_list = res;
                        this.restoreComplateRequest()
                        setTimeout(() => {
                             this.pay();
                        },1000) 
                    },
                    (errormsg) => {
                        uni.hideLoading()
                        uni.showModal({
                            title: "エラー",
                            content: errormsg && errormsg.message ? errormsg.message : "決済環境の取得に失敗しました。再度お試しください。"
                        })
                    }
                )
                // #endif
            },
            // 调起支付
            pay() {
                let that = this
                if (that.paying) return
                that.paying = true
                uni.requestPayment({
                    provider: 'appleiap',
                    orderInfo: {
                        productid: that.pid
                    },
                    success: (e) => {
                        let da = {}
                        da.transaction_id = e.transactionIdentifier
                        da.pay_token = e.transactionReceipt
                        da.svid = that.svid
                        uni.showLoading({
                            title:"読み込み中"
                        })
                        buyPlans(da).then((re) => {
                            uni.hideLoading()
                            if (re.code == 200) {
                                uni.showToast({
                                    title: re.message,
                                    duration:2500
                                })
                                this.restoreComplateRequest();
                                setTimeout(() => {
                                    uni.reLaunch({
                                        url:"/pages/index/index"
                                    })
                                },2000)
                            } else {
                                uni.showToast({
                                    title: re.message,
                                    icon: "none"
                                })
                            }
                        }).catch((err) => {
                            uni.hideLoading()
                            uni.showToast({
                                title: "通信エラーが発生しました。サポートにお問い合わせください。",
                                icon: "none",
                                duration: 3000
                            })
                            console.log("buyPlans(Apple) error", err)
                        })
                    },
                    fail: (e) => {
                        uni.showModal({
                            content: "支払いに失敗しました。再度お試しください。",
                            showCancel: false
                        })
                        this.restoreComplateRequest();
                    },
                    complete: () => {
                        this.loading = false;
                        that.paying = false
                    }
                })
            },
            // // 获取已购的非消耗性商品和订阅商品
            // restoreComplateRequest() {
            //     this.iap.restoreComplateRequest({
            //         manualFinishTransaction: true
            //     }, results => {
            //         // results 格式为数组存放恢复的IAP商品交易信息对象 IAPTransaction，通用需将返回的支付凭证传给后端进行二次认证  
            //         console.log('restoreComplateRequest-results---', results)
            //         this.iap.finishTransaction(results[0], (success) => {
            //               // setTimeout(() => {
            //               //     uni.switchTab({
            //               //         url:"/pages/index/index"
            //               //     })
            //               // },2500)
            //             }, (fail) => {
            //                 uni.showToast({
            //                     title:"关闭订单失败",
            //                     duration:3000
            //                 })
            //               console.log('关闭订单失败',fail);
            //             });
            //     }, e => {
            //         // 错误回调
            //         console.log('restoreComplateRequest-results ---fail---', results)
            //     });
            // },
            
            
            restoreComplateRequest() {
                let that = this
                this.iap.restoreComplateRequest({
                    manualFinishTransaction: true
                }, results => {
                    for(let i = 0;i < results.length;i++){
                        that.iap.finishTransaction(results[i], (success) => {
                        }, (fail) => {
                            console.log("finishTransaction失敗", fail)
                        });
                    }
                }, e => {
                    console.log("restoreComplateRequest失敗", e)
                });
            },
            toxie(){
              uni.navigateTo({
                  url:"/pages/accounts/rules/rules"
              })  
            },
            changeS(e) {
                let that = this
                let platform = uni.getStorageSync("platform")
                let data = {}
                data['type'] = that.type
                data['month'] = that.current
                data['card_type'] = platform == 'ios' ? 2 : 3
                buyPlan(data).then((res) => {
                    if (res.code == 200) {
                        if(platform == 'ios'){
                            that.pid = res.data.product_id
                            that.svid = res.data.svid
                            that.getChannels()
                        }else{
                           var pId = res.data.parent_product_id;
                           let tep = []
                           tep.push(pId)
                           console.log('pid=',tep)
                           googlePay.querySku({
                                   subs: tep
                               },
                               (e) => {
                                   if (e.code == 0) {
                                       if (e.list.length > 0) {
                                           var pd = e.list[0];
                                           var offerToken = '';
                                           if (pd.subscriptionOfferDetails && pd.subscriptionOfferDetails.length > 0) {
                                               // Bug Fix: 不要な [1] 決め打ちを削除し、basePlanId で正確に照合
                                               pd.subscriptionOfferDetails.forEach((v) => {
                                                   if (v.basePlanId == res.data.product_id) {
                                                       offerToken = v.offerToken
                                                   }
                                               })
                                               // basePlanId が一致しない場合は先頭を使用
                                               if (!offerToken) {
                                                   offerToken = pd.subscriptionOfferDetails[0].offerToken
                                               }
                                           }

                                           googlePay.payAll({
                                                   productId: pId,
                                                   offerToken,
                                                   oldPurchaseToken: res.data.google_data.oldPurchaseToken,
                                                   replacementMode: res.data.google_data.replacementMode,
                                                   prorationMode: res.data.google_data.prorationMode,
                                                   externalTransactionId: res.data.google_data.externalTransactionId
                                               },
                                               (e) => {
                                                   if (e.msg == 'success') {
                                                       // Bug Fix: e.data 配列の存在チェック
                                                       if (!e.data || e.data.length === 0 || !e.data[0].original) {
                                                           uni.showToast({ title: "支払いデータの取得に失敗しました", icon: "none" })
                                                           return
                                                       }
                                                       let da = {}
                                                       da.svid = res.data.svid
                                                       da.pay_token = e.data[0].original.purchaseToken
                                                       da.transaction_id = e.data[0].original.orderId || ''
                                                       uni.showLoading({
                                                           title:"読み込み中"
                                                       })
                                                       buyPlans(da).then((re) => {
                                                           uni.hideLoading()
                                                           if (re.code == 200) {
                                                               uni.showToast({
                                                                   title: re.message,
                                                                   duration:2500
                                                               })
                                                               setTimeout(() => {
                                                                   uni.reLaunch({
                                                                       url:"/pages/index/index"
                                                                   })
                                                               },2000)
                                                           } else {
                                                               uni.showToast({
                                                                   title: re.message,
                                                                   icon: "none"
                                                               })
                                                           }
                                                       }).catch((err) => {
                                                           uni.hideLoading()
                                                           uni.showToast({
                                                               title: "通信エラーが発生しました。サポートにお問い合わせください。",
                                                               icon: "none",
                                                               duration: 3000
                                                           })
                                                           console.log("buyPlans(Google) error", err)
                                                       })
                                                   } else {
                                                       uni.showToast({
                                                           title: "支払いに失敗しました。再度お試しください。",
                                                           icon: "none",
                                                           duration: 3000
                                                       })
                                                       console.log("Google Pay failed", e)
                                                   }
                                               }
                                           );
                                       } else {
                                           uni.showToast({
                                               title: "商品情報が見つかりませんでした",
                                               icon: "none"
                                           })
                                       }
                                   } else {
                                       uni.hideLoading()
                                       console.log(e)
                                       uni.showToast({
                                           title: "商品情報の取得に失敗しました。再度お試しください。",
                                           icon:"none"
                                       })
                                   }
                               }
                           );
                        }
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },
            toSuccess1() {
                let that = this
                if(that.using.card_type == 1){
                    // Stripe ユーザーはモバイルからプラン変更不可、PCブラウザを案内
                    that.show = true
                    that.title = 'プラン・支払い周期の変更について'
                    that.content = 'お手数ではございますが、PCブラウザよりCard-Sanウェブサイトにログインしてプラン変更をお願いします。'
                    return
                }
                that.changeS("e")
            },
            changeMethods(e) {
                let that = this
                that.paymethod = e
            },
            toSuccess() {
                let that = this
                let platform = uni.getStorageSync("platform")
            },
            checkMore() {
                uni.navigateTo({
                    url: "/pages/accounts/package/package"
                })
            },
            getCardInfo() {
                let that = this
                getCardInfo().then((res) => {
                    if (res.code == 200) {
                        if (res.data.cards.length > 0) {
                            that.inp1 = res.data.cards[0].card_no
                            that.inp2 = res.data.cards[0].expire_date
                            that.inp3 = res.data.cards[0].cvv
                            that.inp4 = res.data.cards[0].name
                        }
                    }
                })
            },
            getPlanList(type) {
                let that = this
                getPackageList().then((res) => {
                    if (res.code == 200) {
                        that.selectItem = res.data.vip_contract[type - 1]
                        that.price1 = res.data.vip_contract[type - 1].price[0]
                        that.price2 = res.data.vip_contract[type - 1].price[1]
                        that.price3 = res.data.vip_contract[type - 1].price[2]
                    }
                })
            },
            chooseTime(e) {
                let that = this
                that.current = e
                if(that.vips.length > 0){
                    that.calculateTime(that.endDate * 1000)
                }else{
                    that.calculateTime(that.timestamp * 1000)
                }
            },
            
            calculateTime(newDate){
                let that = this 
                let dates = new Date(newDate)
                console.log('dates',dates)
                let result
                if(that.current == 12){
                   result = dates.setFullYear(dates.getFullYear() + 1);
                }else if(that.current == 6){
                    result = dates.setMonth(dates.getMonth() + 6);
                }else if(that.current == 1){
                    result = dates.setMonth(dates.getMonth() + 1);
                }
                that.refreshTime = that.getHalfYearLater(result)
                console.log("result",result)
            },
            
            getShopInfo() {
                let that = this
                getShopInfo().then((res) => {
                    that.using = res.data.shop_info.vip
                    if (res.data.shop_info.vips.length > 0) {
                        that.vips = res.data.shop_info.vips
                        that.endDate = res.data.shop_info.vips[0].end_timestamp
                        that.calculateTime(res.data.shop_info.vips[0].end_timestamp * 1000)
                    }else{
                        that.endDate = ""
                        console.log(333,that.timestamp*1000)
                        that.calculateTime(that.timestamp*1000)
                    }
                })
            },
            // 计算半年后的日期
            getHalfYearLater(timestamp) {
              const date = new Date(timestamp);
              const year = date.getFullYear();
              const month = date.getMonth() + 1;
              const day = date.getDate();
              const hours = date.getHours();
              const minutes = date.getMinutes();
              const seconds = date.getSeconds();
              const formattedDate = `${year}年${month}月${day}日`;
              console.log("formattedDate",formattedDate)
              return formattedDate;
            },
        }
    }
</script>

<style lang="scss">
    .xie{
        margin-top: 40upx;
        font-size: 28upx;
        color: #1A73E8;
    }
    
    .xie1{
        opacity: 0.6 !important;
    }
    
    .bt {
        background: #1A73E8;
        height: 112upx;
        margin-top: 96upx;
        line-height: 112upx;
        color: #fff;
    }

    .wor {
        font-size: 28rpx;
        color: #1D1D1F;
    }

    .coblue {
        color: #1A73E8 !important;
        font-size: 28upx !important;
        font-weight: normal !important;
    }

    .detaTitle {
        font-size: 28upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        padding-bottom: 20upx;
    }

    .tips {
        box-sizing: border-box;
        padding: 0 58upx;
        margin: 80upx auto;
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #0A4B6D;
    }

    .pd {
        box-sizing: border-box;
        padding: 48upx 48upx 0 48upx;
    }

    .deta {
        display: flex;
        justify-content: space-between;
        padding: 28upx 0;
        border-top: 2upx solid #D2D2D7;
       
        .deta_item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
            padding-left: 36upx;
            margin-top: 20upx;
        }

        .detaItem {
            font-family: Hiragino Sans-W3, Hiragino Sans;
            color: #1D1D1F;
             font-size: 18px !important;
            font-weight: bold;
        }
    }

    .applePay {
        width: 94upx;
        height: 60upx;
        margin-left: 36upx;
        margin-right: 20upx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .googlePay {
        width: 124upx;
        height: 60upx;
        margin-right: 20upx;
        margin-left: 36upx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .selectContent {
        width: 32upx;
        height: 32upx;
        box-sizing: border-box;
        padding: 6upx;
        border: 2upx solid #1A73E8;
        border-radius: 50%;
        margin-right: 10upx;

        .selectInner {
            width: 100%;
            height: 100%;
            background: #1A73E8;
            border-radius: 50%;
        }
    }

    .paymethod {
        .payItem {
            box-sizing: border-box;
            padding: 48upx;
            border-bottom: 2upx solid #D2D2D7;

            .cardsInfo {
                margin-top: 80upx;

                .info_item {
                    margin-top: 60upx;
                    flex: 1;

                    .info_name {
                        font-size: 24upx;
                        font-family: Hiragino Sans-W5, Hiragino Sans;
                        font-weight: normal;
                        color: #707070;
                    }
                }
            }

            .pTop {
                display: flex;
                align-items: center;

                .payName {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }
            }

            .cards {
                width: 413upx;
                height: 60upx;
                margin-top: 20upx;

                image {
                    width: 100%;
                    height: 100%;
                }
            }
        }
    }

    .container {
        background-color: #fff;
        box-sizing: border-box;
        padding: 0 32upx;
    }

    .checkMore {
        font-size: 28upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
        text-align: right;
    }

    .list_item {
        display: flex;
        box-sizing: border-box;
        padding: 40upx 32upx;
        border-bottom: 2upx solid #D2D2D7;
        align-items: center;
        font-size: 14px !important;
        .selectTime {
            width: 15%;
        }

        .selectPrice {
            width: 45%;
        }

        .selectDisccount {
            flex: 1;
            color: #D93025 !important;
        }
    }

    .item {
        box-shadow: 0px 0px 20upx 2upx rgba(0, 0, 0, 0.07);
        margin-top: 60upx;

        .item_title {
            box-sizing: border-box;
            padding: 24upx 46upx;
            background: rgba(26, 115, 232, 0.1);
            font-size: 28upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .item_detail {
            box-sizing: border-box;
            padding: 40upx;

            .detail_item {
                display: flex;
                align-items: center;
                margin-bottom: 40upx;

                .detail_item_left {
                    font-size: 28upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }

                .detail_item_right {
                    font-size: 28upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }

            }
        }
    }
</style>