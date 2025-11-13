<template>
    <view class="contain" style="padding-top: 80upx;">
        <view class="shopInfo">
            <view class="info">
                <view class="shopName" v-text="shopInfo.shop_name"></view>
                <view class="shopId" v-if="admin.admin_type == 1" v-text="`ID:${admin.email}`"></view>
                <view class="shopId" v-if="admin.admin_type != 1" v-text="`ID:${admin.phone}`"></view>
                <view class="shopId">
                    ご利用プラン：{{shopInfo.vip.type==2?'スタンダード':shopInfo.vip.type == 1? 'ライト' : shopInfo.vip.type == 3? 'プレミアム' : ''}}
                </view>
            </view>
            <view class="logouts">
                <u-button class="logout" @click="logOut">
                    ログアウト
                </u-button>
            </view>
        </view>
        <view class="sales">
            <view class="saleItem" @click="toaaaa">
                <view class="saleTop">
                    今日
                </view>
                <view class="saleBot" v-text="day_order_count"></view>
            </view>
            <view class="saleItem" @click="tom">
                <view class="saleTop">
                    今月
                </view>
                <view class="saleBot" v-text="month_order_count"></view>
            </view>
            <view class="saleItem">
                <view class="saleTop">
                    メンバー数
                </view>
                <view class="saleBot m" v-text="member_count"></view>
            </view>
        </view>
        <view class="content">
            <view class="indexItem" hover-class="click-hover" @click="scanCode">
                <view class="itemTop">
                    <image src="../../static/index/QRRリーダー_icon.svg" mode="aspectFill"></image>
                </view>
                <view class="itemBot">
                    QRリーダー
                </view>
            </view>
            <view class="indexItem" hover-class="click-hover" @click="toApp">
                <view class="itemTop">
                    <image src="../../static/index/Download_icon.svg" mode="aspectFill"></image>
                </view>
                <view class="itemBot">
                    ダウンロード
                </view>
            </view>
            <view class="indexItem" hover-class="click-hover" @click="toCustomer">
                <view class="itemTop">
                    <image src="../../static/index/Member_icon.svg" mode="aspectFill"></image>
                </view>
                <view class="itemBot">
                    メンバー管理
                </view>
            </view>
            <view class="indexItem" hover-class="click-hover" @click="toMessage">
                <view class="itemMask" v-if="shopInfo.vip.type == 1" @click.stop="maskClick"></view>
                <view class="itemTop" style="width: 100upx;height: 86upx;">
                    <image src="../../static/index/Message_icon.svg" mode="widthFix"></image>
                </view>
                <view class="itemBot">
                    メッセージ
                </view>
                <view class="bdg" v-if="value * 1 > 0 && shopInfo.vip.type != 1" v-text="value"></view>
            </view>
            <view class="indexItem" hover-class="click-hover" @click="toNotice">
                <view class="itemMask" v-if="shopInfo.vip.type == 1" @click.stop="maskClick"></view>
                <view class="itemTop" style="height: 88upx;">
                    <image src="../../static/index/News_icon.svg" mode="aspectFill"></image>
                </view>
                <view class="itemBot">
                    お知らせ
                </view>
            </view>
            <view class="indexItem" hover-class="click-hover" @click="toMenu">
                <view class="itemMask" v-if="shopInfo.vip.type == 1" @click.stop="maskClick"></view>
                <view class="itemTop">
                    <image src="../../static/index/Menu_icon.svg" mode="aspectFill"></image>
                </view>
                <view class="itemBot">
                    メニュー
                </view>
            </view>
            <view class="indexItem" hover-class="click-hover" @click="toAppointmeng">
                <view class="itemMask" v-if="shopInfo.vip.type == 1 || shopInfo.vip.type == 2" @click.stop="maskClick">
                </view>
                <view class="itemTop">
                    <image src="../../static/index/Resarve_icon.svg" mode="aspectFill"></image>
                </view>
                <view class="itemBot">
                    予約
                </view>
                <view class="bdg g" v-text="value2"
                    v-if="value2 * 1 > 0 && shopInfo.vip.type != 1 && shopInfo.vip.type != 2"></view>
            </view>
            <view class="indexItem" hover-class="click-hover" @click="toCoupon">
                <view class="itemMask" v-if="shopInfo.vip.type == 1" @click.stop="maskClick"></view>
                <view class="itemTop">
                    <image src="../../static/index/Coupon_icon.svg" mode="aspectFill"></image>
                </view>
                <view class="itemBot">
                    クーポン
                </view>
            </view>
            <view class="indexItem" hover-class="click-hover" @click="toCalendar">
                <view class="itemTop">
                    <image src="../../static/index/calender_icon.svg" mode="aspectFill"></image>
                </view>
                <view class="itemBot">
                    カレンダー
                </view>
            </view>
           <!-- <view class="indexItem" hover-class="click-hover" @click="getChannels">
                <view class="itemTop">
                    <image src="../../static/index/calender_icon.svg" mode="aspectFill"></image>
                </view>
                <view class="itemBot">
                    支付
                </view>
            </view> -->
        </view>
        <view class="tips" v-if="shopInfo.vip.is_try != 0 && shopInfo.vips.length > 0">
            <view class="tips_left">
                無料お試し中：<text
                    style="color: #FC0303;font-size: 24rpx;">残り{{shopInfo.vips[0].end_day}}日</text>
            </view>
            <view class="tips_right">
                <u-button @click="toPay" :ripple='true' type="primary " shape="circle" class="apply_btn">
                    お申し込みはこちら</u-button>
            </view>
        </view>
        <view v-if="shopInfo.vips.length > 0">
            <view class="tips" v-if="shopInfo.vips[0].cancel_time > 0">
                <view class="tips_left">
                    解約終了日：<text style="color: #FC0303;">{{shopInfo.vips[0].end_time || 0}}</text>
                </view>
                <view class="tips_right">
                    <u-button @click="toPay" :ripple='true' type="primary " shape="circle" class="apply_btn"
                        text="継続はこちら"></u-button>
                </view>
            </view>
        </view>

        <u-popup :show="show" round="15">
            <view class="cons">
                <view class="contitle">
                    クーポンの利用確認
                </view>
                <u-swiper :list="banner" height="250" :indicator="true" indicatorMode="dot" :radius="0"
                    indicatorActiveColor="#000000" indicatorInactiveColor="rgba(0,0,0,.3)" keyName="img"></u-swiper>
                <view class="details">
                    <view class="de_1">
                        <view class="tt1">
                            クーポン番号
                        </view>
                        <view class="tt2" v-text="info.number"></view>
                    </view>
                    <view class="de_2">
                        <view class="tt1" v-if="info.title" v-text="info.title"></view>
                        <view class="tt1" v-else>タイトル</view>
                    </view>
                    <u-button class="btn1" text="クーポンを承認" @click="userCoupon"></u-button>
                    <u-button class="btn2" text="キャンセル" @click="show = false"></u-button>
                </view>
                <view style="height: 50upx;"></view>
            </view>
        </u-popup>

        <!-- 套餐弹窗 -->
        <u-popup :show="showPackage" mode="center" :placeholder="false" :safeAreaInsetBottom="false">
            <view class="packageContent" v-if="sf == true">
                <view class="alertTitle">
                    ご利用プランについて
                </view>
                <view class="packageDetail">
                    <view class="pd1">
                        現在のプラン：<text class="bolderWord">プレミアム</text>
                    </view>
                    <view class="pd2">
                        お試し期間中は、全ての機能が無料でご利用いただけますので、お店の運用に合った機能を選んでお試しください。
                    </view>
                    <u-button customStyle="{margin-top,48upx}" type="primary" text="OK" @click="closeshi"></u-button>
                </view>
            </view>
        </u-popup>
        <u-popup :show="showalert" mode="center" bgColor="transparent">
            <view class="alertContent">
                <image class="closeAlert" src="../../static/icons/wclose.png" @click.stop="closeAlert"></image>
                <view class="imageCon" @click="openUel">
                    <image :src="alertInfo.image" mode="aspectFill"></image>
                </view>
                <view class="alerttitle" @click="openUel" v-text="alertInfo.title"></view>
            </view>
        </u-popup>
        <u-popup :show="showd" mode="center" round="14" :placeholder="false" :safeAreaInsetBottom="false"
            overlayStyle="{background: 'rgba(3, 100, 219, 0.5)'}" zIndex="170006">
            <view class="dcontent" v-if="gtype == 1">
                <view class="dtitle">
                    無料トライアル期間終了のため、現在ご利用できません。
                </view>
                <view class="ddetail">
                    お申し込み手続きをすることで、すぐにご利用ができます。
                </view>
                <view class="dbtn" @click="toPay">
                    お申し込みはこちら
                </view>
            </view>
           <view class="dcontent" v-else>
                <view class="dtitle">
                    ご契約期間終了のため、現在ご利用できません。
                </view>
                <view class="ddetail">
                    お申し込みを再開することで、すぐにご利用ができます。
                </view>
                <view class="dbtn" @click="tobuyplan(2)">
                    再開はこちら
                </view>
            </view>
        </u-popup>

        <u-popup class="smsale" :show="sms" round="14" mode="center" :safeAreaInsetBottom = "false">
            <view class="smscontent">
                <view class="smstitle">
                    SMS通知の配信数超過のお知らせ
                </view>
                <view class="smsdetail">
                    SMS通知数が配信上限数を超過しました。<br />追加配信利用料のお支払いください。
                </view>
                <view class="smsdetail" style="margin-top: 34upx;">
                    利用料（￥1,200）で、
                </view>
                <view class="smsdetail">
                    更に100通のSMS通知を配信できます。
                </view>
                <view class="smsdetail">
                    お支払い完了後に、この表示は消去されます。
                </view>
                <view class="smsbtn" hover-class="smshover" @click="buysms">
                    お支払い
                </view>
            </view>
        </u-popup>
        <view class="mask" v-if="kjgu" @click="kjgu = false">
            <view class="co">
                <image src="../../static/images/redright.png" mode=""></image>
                <view>実行する権限がありません</view>
            </view>
        </view>
        <mod v-if="showModule" @getStatus="getStatus" :config="configs"></mod>
        <mod v-if="showModule1" @getStatus="getStatus1" :config="configs1"></mod>
        <permission :show="showpermission" @close='showpermission = false'></permission>
    </view>
</template>
<script>
    var googlePay = uni.requireNativePlugin('sn-googlepay5');
    import {
        getShopInfo,
        getMessageList,
        getAppointNoticeList,
        getCouponINfo,
        useCoupon,
        getConfig,
        buyextras,
        buyextra,
        getAdminDetail
    } from "../../api/index.js";
    import mod from "@/components/mod.vue"
    import permission from "@/components/permission/permission.vue"
    export default {
        components: {
            mod,
            permission
        },
        data() {
            return {
                closeid:"",
                showpermission:false,
                orid:"",
                iapChannel: null,
                loading: false,
                kjgu: false,
                admin_type: 1,
                sms: false,
                smsprice: "",
                smscount: "",
                smsid: [],
                gtype: 1,
                showModule: false,
                showModule1: false,
                showd: false,
                value: 0,
                value2: 0,
                showPackage: false,
                sf: false,
                ss: false,
                showalert: false,
                type: "warning",
                admin: {},
                shopInfo: {
                    vip: {
                        type: 0
                    },
                    vips: [

                    ]
                },
                day_order_count: 0,
                member_count: 0,
                month_order_count: 0,
                show: false,
                cid: "",
                banner: [],
                info: {},
                alertInfo: {},
                currentStamp: 0,
                configs: {
                    title: "ログアウトしてよろしいですか？",
                    content: '',
                    confirm: true,
                    cancel: true,
                    confirmText: "キャンセル",
                    cancelText: "ログアウト",
                    type: "revers"
                },
                configs1: {
                    title: "ご契約のプランでは<br/>この機能をご利用できません。<br/>プランを変更しますか？",
                    content: '変更すると、すぐにこの機能を<br/>ご利用できます。',
                    confirm: true,
                    cancel: true,
                    confirmText: "プランを変更",
                    cancelText: "キャンセル",
                    type: ""
                },
                iap: null, // 支付通道
                ids: ['premium_halfyear', 'premium_month', 'standard_halfyear','standard_month','light_halfyear','light_months','sms'], // 应用内购项目产品 ID 数组
                product_list: [], // 应用内购项目列表
                mealId: 5,
            }
        },
        computed: {
            newMessage() {
                return this.$store.state.newMessage;
            },
        },
        watch: {
            newMessage: {
                handler(nval, oval) {
                    let that = this
                    if (nval) {
                        that.getUnread()
                    }
                },
                deep: true, // 深度监听
                immediate: true, //立即执行
            }
        },
        onLoad: function() {
            // 创建示例
            // this._iap = new Iap({
            //     products: ['premium_halfyear'] // 苹果开发者中心创建
            // })
            // console.log("iap", this._iap)
            // this.init();
        },
        onShow() {
            let that = this
            let admin = uni.getStorageSync("admin")
            that.admin_type = admin.admin_type
            uni.showLoading({
                title: "読み込み中",
                mask: true
            })
            // getShopInfo().then((res) => {
            //     if (res.code == 200) {
            //         that.shopInfo = res.data.shop_info
            //         that.day_order_count = res.data.day_order_count
            //         that.member_count = res.data.member_count
            //         that.month_order_count = res.data.month_order_count
            //     }
            // })
            that.admin = uni.getStorageSync("admin")
            that.getUnread()
            that.getLists()
            let cid = uni.getStorageSync("cid")
            getConfig().then((res) => {
                let data = {}
                if (res.code == 200) {
                    let status = uni.getStorageSync("ale")
                    data.title = res.data.config.shop_alert_title
                    data.web = res.data.config.shop_alert__url
                    data.status = res.data.config.shop_alert__status
                    data.image = res.data.config.shop_alert__image
                    that.alertInfo = data
                    that.currentStamp = res.data.config.timestamp
                    if (res.data.config.shop_alert__status == 1 && status == 'aviliable') {
                        that.showalert = true
                    }
                }
            })
            getShopInfo().then((res) => {
                let status = uni.getStorageSync("ale")
                uni.hideLoading()
                if (res.code == 200) {
                    console.log(123123123123123213213213213123123123213,res)
                    that.shopInfo = res.data.shop_info
                    that.day_order_count = res.data.day_order_count
                    that.member_count = res.data.member_count
                    that.month_order_count = res.data.month_order_count
                    if (res.data.shop_info.vip_sms_alert == 1) {
                        that.sms = true
                        that.smsprice = res.data.vip_sms_config.price
                        that.smscount = res.data.vip_sms_config.count
                        that.smsid = res.data.vip_sms_config.product_id
                        return;
                    }
                    if (res.data.shop_info.vip.is_try == 1 && status == 'aviliable') {
                        that.showPackage = true
                        that.sf = true
                    }

                    if (!res.data.shop_info.vip.type) {
                        if (res.data.shop_info.last_vip_is_try == 1) {
                            that.gtype = 1
                        } else {
                            that.gtype = 2
                        }
                        that.showd = true
                        // uni.hideTabBar()
                    } else {
                        that.showd = false
                    }
                } else {

                }
            })
            setTimeout(() => {
                let that = this
                plus.runtime.setBadgeNumber(that.value * 1 + that.value2 * 1);
            }, 220)
        },

        onHide() {
            setTimeout(() => {
                let that = this
                plus.runtime.setBadgeNumber(that.value * 1 + that.value2 * 1);
            }, 520)
        },
        methods: {
            // 获取支付通道
            getChannels() {
                // #ifdef APP-PLUS
                plus.payment.getChannels(res => {
                    let channel = res.find(i => i.id === 'appleiap')
                    this.iap = channel ? channel : null
                    this.requestOrder()
                    this.restoreComplateRequest()
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
                        productid: this.smsid[3]
                    },
                    success: (e) => {
                        that.closeid = e.transactionIdentifier
                        let da = {}
                        da.order_no = that.orid
                        da.pay_token = e.transactionReceipt
                        da.transaction_id = e.transactionIdentifier
                        console.log("da",da)
                        buyextra(da).then((re) => {
                            console.log("re",re)
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
                            content: "支払いに失敗しました。再度お試しください。",
                            showCancel: false
                        })
                    },
                    complete: () => {
                        this.loading = false;
                        this.restoreComplateRequest()
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
                    
                    for(let i = 0;i < results.length;i++){
                        that.iap.finishTransaction(results[i], (success) => {
                            }, (fail) => {
                            });
                    }
                    
                   
                }, e => {
                    // 错误回调
                });
            },
            handlePay() {
                this.iospay();
            },
            // iosPay() {
            //     let that = this
            //     uni.getProvider({
            //         service: 'payment',
            //         success: (res) => {
            //             console.log(res)
            //             res.providers.find((channel) => {
            //                 if (channel.id === 'appleiap') {
            //                     that.iapChannel = channel
            //                 }
            //             })
            //             // //成功之后会返回通道
            //             //     resolve(iapChannel)

            //             uni.requestPayment({
            //                 provider: 'appleiap',
            //                 orderInfo: {
            //                     manualFinishTransaction: false, //true为手动关闭订单，false为自动关闭订单
            //                     username: "00222", //透传参数
            //                     productid: 'premium_month', //档位id
            //                 },
            //                 success: (e) => {
            //                     console.log(123123131)
            //                 },
            //                 fail: (err) => {
            //                     console.log("err", err)
            //                 }
            //             })
            //         },
            //     })
            // },
            buysms() {
                let that = this
                let platform = uni.getStorageSync("platform")
                if (that.admin_type == 2) {
                    that.kjgu = true
                }
                
                if (that.admin_type == 1) {
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    if(platform != 'ios'){
                        var pId = that.smsid[3];
                        let orid = ''
                        let data = {}
                        data.type = 3
                        data.card_type = 3
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
                                                                that.sms = false
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
                    }else{
                        let data = {}
                        data.type = 3
                        data.card_type = 2
                        buyextras(data).then((res) => {
                            if (res.code == 200) {
                                 that.getChannels()
                                that.orid = res.data.order_no
                            }
                        })
                    }
                }
            },

            closeshi() {
                let that = this
                that.showPackage = false
                // that.showalert = true
            },
            tobuyplan(e) {
                if (this.admin_type == 1) {
                    uni.navigateTo({
                        url: "/pages/accounts/package/package"
                    })
                } else {
                    this.kjgu = true
                }
            },
            // topay() {


            // },


            // topay() {
            //     googleplay.init(function(res) {
            //         if (res.code == 0) {
            //             //成功
            //             googleplay.isFeatureSupported({
            //                 "type": 2
            //             }, function(result) {
            //                 console.log("result",result.code)
            //                 if (result.code == 0) {
            //                     //支持
            //                     googleplay.queryProduct({
            //                         "productList": ["lightplan"],
            //                         "productType": "subs"
            //                     }, function(e) {
            //                         if (e.code == 0) {
            //                             console.log("e",e)
            //                             //成功
            //                             // e.list;  商品列表 
            //                             googleplay.pay({
            //                                 productList: ['lightplan'],
            //                                 setIsOfferPersonalized: false
            //                             }, function(r) {
            //                                 console.log("r",r)
            //                                 if (r.code == 0 && !r.purchases) {
            //                                     //呼起谷歌支付成功 e.purchases 
            //                                     console.log("r1", r)
            //                                 } else if (r.code == 0 && r.purchases) {
            //                                     //支付成功,返回支付商品信息 e.purchases 是一个数组
            //                                     console.log("r2", r)
            //                                 } else {

            //                                     //失败
            //                                 }

            //                             });
            //                         } else {

            //                             //失败
            //                         }

            //                     });
            //                 } else {

            //                     //失败
            //                 }

            //             });
            //         } else {

            //             //失败
            //         }

            //     });
            // },
            getStatus1(e) {
                let that = this
                if (e == "confirm") {
                    uni.navigateTo({
                        url: "/pages/accounts/contract/contract"
                    })
                } else {
                    that.showModule1 = false
                }
            },
            maskClick() {
                let that = this
                that.showModule1 = true
            },
            getStatus(e) {
                if (e == 'confirm') {
                    this.showModule = false
                } else {
                    uni.clearStorageSync()
                    uni.clearStorage()
                    uni.closeSocket();
                    uni.redirectTo({
                        url: "../login/login"
                    })

                }
            },
            tom() {
                uni.navigateTo({
                    url: "../monthly/monthly"
                })
            },
            closeAlert() {
                this.showalert = false
                uni.setStorageSync("ale", "unaviliable")
            },
            toCustomer() {
                uni.navigateTo({
                    url: "../customer/customer"
                })
            },
            toPay() {
                uni.navigateTo({
                    url: "/pages/payment/payment?type=3"
                })
            },
            openUel() {
                let that = this
                let u = that.alertInfo.web
                // #ifdef MP-WEIXIN || H5
                url = '/pages/web-page/web-page?url=' + encodeURIComponent(u)
                // #endif

                // #ifdef APP-PLUS 
                if (plus.os.name == 'Android' || plus.os.name == 'iOS') {
                    plus.runtime.openURL(u);
                    return
                }
                // #endif
                uni.navigateTo({
                    url: url
                }) // uniapp跳转页面
            },
            formatDate(e) {
                let date = new Date(e * 1000)
                let year = date.getFullYear()
                let month = date.getMonth() + 1
                let das = date.getDate()
                return year + "年" + month + "月" + das + "日"
            },
            formatDate1(e) {
                let date = new Date(e * 1000)
                let year = date.getFullYear()
                let month = date.getMonth() + 1
                let das = date.getDate()
                return year + "-" + month + "-" + das
            },
            toaaaa() {
                let that = this
                uni.navigateTo({
                    url: "../daily/daily?time=" + this.formatDate(that.currentStamp) + "&date=" + this
                        .formatDate1(that.currentStamp)
                })
            },
            userCoupon() {
                let that = this
                let data = {}
                data.id = that.info.id
                data.is_qrcode = 1
                useCoupon(data).then((res) => {
                    that.show = false
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message,
                            duration: 2500
                        })
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none",
                            duration: 2500
                        })
                    }
                })
            },
            getLists() {
                let that = this
                getAppointNoticeList(1).then((res) => {
                    if (res.code == 200) {
                        that.value2 = res.data.unread_count
                        plus.runtime.setBadgeNumber(that.value * 1 + that.value2 * 1);
                    }
                })
            },
            getUnread() {
                let that = this
                let data = {}
                data["type"] = "list"
                data["uid"] = 0
                data["mid"] = 0
                data["timestamp"] = 0
                getMessageList(data).then((res) => {
                    let number = 0
                    if (res.code == 200) {
                        res.data.messages.forEach((val, index) => {
                            number += val.unread_num
                        })
                        that.value = number
                        plus.runtime.setBadgeNumber(that.value * 1 + that.value2 * 1);
                    }
                })
            },
            toCoupon() {
                uni.navigateTo({
                    url: "../coupon/coupon"
                })
            },
            scanCode() {
                let that = this
                let admin = uni.getStorageSync("admin")
                if(admin.admin_type == 2){
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    getAdminDetail(admin.id).then((res) => {
                        if(res.code == 200){
                            uni.hideLoading()
                            if(res.data.admin.privileges.includes(1)){
                                uni.scanCode({
                                    autoDecodeCharset: true,
                                    onlyFromCamera: true,
                                    success: function(res) {
                                        if (res.scanType == 'QR_CODE') {
                                            let resu = JSON.parse(res.result)
                                            uni.navigateTo({
                                                url: "../customer/cards/seal/seal?id=" + resu.id + "&source=1"
                                            })
                                        } else if (res.scanType == "CODE_128") {
                                            let temp = res.result.split("-")
                                            if (temp[0] == 'member_coupon') {
                                                getCouponINfo(temp[1]).then((res) => {
                                                    if (res.code == 200) {
                                                        that.show = true
                                                        that.banner = res.data.coupon.images
                                                        that.info = res.data.coupon
                                                    }
                                                })
                                            } else {
                                                uni.navigateTo({
                                                    url: "../customer/cards/intergrate/intergrate?id=" + temp[1] +
                                                        "&source=1&code=111"
                                                })
                                            }
                                        }
                                    }
                                })
                            }else{
                                that.showpermission = true
                            }
                        }
                    })
                }else{
                    uni.scanCode({
                        autoDecodeCharset: true,
                        onlyFromCamera: true,
                        success: function(res) {
                            console.log(res)
                            if (res.scanType == 'QR_CODE') {
                                let resu = JSON.parse(res.result)
                                uni.navigateTo({
                                    url: "../customer/cards/seal/seal?id=" + resu.id + "&source=1"
                                })
                            } else if (res.scanType == "CODE_128") {
                                let temp = res.result.split("-")
                                if (temp[0] == 'member_coupon') {
                                    getCouponINfo(temp[1]).then((res) => {
                                        console.log("res",res)
                                        if (res.code == 200) {
                                            that.show = true
                                            that.banner = res.data.coupon.images
                                            that.info = res.data.coupon
                                        }else{
                                            uni.showToast({
                                                title:res.message,
                                                icon:'none'
                                            })
                                        }
                                    })
                                } else {
                                    uni.navigateTo({
                                        url: "../customer/cards/intergrate/intergrate?id=" + temp[1] +
                                            "&source=1&code=111"
                                    })
                                }
                            }
                        }
                    })
                }    
            },
            toApp() {
                let that = this 
                let admin = uni.getStorageSync("admin")
                if(admin.admin_type == 2){
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    getAdminDetail(admin.id).then((res) => {
                        if(res.code == 200){
                            uni.hideLoading()
                            if(res.data.admin.privileges.includes(2)){
                                uni.navigateTo({
                                    url: "../appDownload/appDownload"
                                })
                            }else{
                                that.showpermission = true
                            }
                        }
                    })
                }else{
                    uni.navigateTo({
                        url: "../appDownload/appDownload"
                    })
                }    
            },
            toMessage() {
                uni.navigateTo({
                    url: "../message/message"
                })
            },
            toNotice() {
                uni.navigateTo({
                    url: "../notice/notice"
                })
            },
            toMenu() {
                uni.navigateTo({
                    url: "../menu/menu"
                })
            },
            toAppointmeng() {
                uni.navigateTo({
                    url: "../appointment/appointment"
                })
            },
            toCalendar() {
                uni.navigateTo({
                    url: "../calender/calender"
                })
            },
            logOut() {
                let that = this
                that.showModule = true

            }
        }
    }
</script>

<style lang="scss" scoped>
    .num {
        width: 52upx;
        height: 52upx;
        border-radius: 50%;
        background-color: rgba(26, 115, 232, 1);
        color: #fff;
        text-align: center;
        line-height: 52upx;
        font-size: 32upx;
    }

    .co {
        width: 272rpx;
        height: 268rpx;
        background: #FFFFFF;
        border-radius: 20rpx 20rpx 20rpx 20rpx;
        opacity: 1;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        color: #D93025;
        font-size: 32rpx;
        box-sizing: border-box;
        padding: 40upx;

        image {
            width: 80upx;
            height: 80upx;
        }
    }

    .mask {
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, .6);
        position: fixed;
        top: 0;
        left: 0;
        z-index: 199999 !important;
    }

    .smscontent {
        width: 690upx;
        height: 676upx;
        background: #fff;
        box-sizing: border-box;
        padding: 60upx 40upx;
        border-radius: 20upx;

        .smstitle {
            font-size: 32rpx;
            font-family: Noto Sans Kannada, Noto Sans Kannada;
            font-weight: bold;
            color: #1D1D1F;
            margin-bottom: 56upx;
            text-align: center;
        }

        .smsdetail {
            font-size: 28rpx;
            font-family: Noto Sans Kannada, Noto Sans Kannada;
            text-align: center;
            color: #1D1D1F;
            line-height: 1.5;
        }

        .smsbtn {
            width: 480rpx;
            height: 96rpx;
            background: #1A73E8;
            border-radius: 12upx;
            text-align: center;
            line-height: 96upx !important;
            font-size: 32rpx;
            font-family: Noto Sans Kannada, Noto Sans Kannada;
            font-weight: bold;
            color: #FFFFFF;
            margin: 124upx auto 0;
        }

    }

    .smshover {
        opacity: 0.6 !important;
    }

    .u-popup__content {
        background: transparent !important;
    }

    .dcontent {
        width: 540upx;
        background: rgba(249, 249, 249, 0.94);
        border-radius: 28upx;
        overflow: hidden;
    }

    .dtitle {
        box-sizing: border-box;
        padding: 46upx 34upx 28upx;
        font-size: 32rpx;
        font-family: Noto Sans Kannada, Noto Sans Kannada;
        font-weight: bold;
        color: #D93025;
        text-align: center;
    }

    .ddetail {
        font-size: 28rpx;
        font-weight: 400;
        color: #1D1D1F;
        box-sizing: border-box;
        padding: 0 34upx;
        text-align: center;
        height: 144upx;
        border-bottom: 2upx solid #86868b;
    }

    .dbtn {
        font-size: 40rpx;
        text-align: center;
        color: #1A73E8;
        padding: 30upx 0;
    }

    .closeAlert {
        width: 50upx;
        height: 50upx;
        position: absolute;
        top: 20upx;
        left: 20upx;
        z-index: 10005;
    }

    .imageCon {
        width: 100%;
        border-top-left-radius: 12upx;
        border-top-right-radius: 12upx;
        overflow: hidden;
        background: #fff;

        image {
            width: 100%;
        }
    }

    .click-hover {
        background-color: #eaeaea !important;
    }

    .alerttitle {
        text-align: center;
        box-sizing: border-box;
        padding: 20upx;
        background: #fff;
        border-bottom-left-radius: 12upx;
        border-bottom-right-radius: 12upx;
    }

    page {
        background-color: #F5F5F5;
    }

    .alertContent {
        width: 95vw;
        border-radius: 20upx;
    }

    .arrows {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 60upx;

        image {
            height: 60upx;
            width: 50upx;
            margin: 0 40upx;
        }
    }

    .custom-style {
        margin-top: 48upx;
        margin-bottom: 52upx;
        background: rgba(29, 29, 31, 0.1) !important;
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: rgba(29, 29, 31, 0.9);
    }

    .bolderWord {
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: bold;
    }

    .packageContent {
        width: 686upx;
        background: #fff;

        .warningTitle {
            width: 100%;
            padding: 40upx 0;
            background: rgba(217, 48, 37, 0.1);
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #D93025;
            text-align: center;
        }

        .alertTitle {
            width: 100%;
            height: 96upx;
            background: rgba(26, 115, 232, 0.1);
            text-align: center;
            line-height: 96upx;
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .packageDetail {
            box-sizing: border-box;
            padding: 52upx;

            .pd1 {
                font-size: 32upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                box-sizing: border-box;
                padding: 28upx 0;
                margin-bottom: 52upx;
            }

            .pd2 {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                margin-bottom: 52upx;
            }
        }
    }

    .apply_btn {
        width: 300upx !important;
        height: 60upx;
        background: #1A73E8;
        border-radius: 32upx;
        text-align: center;
        line-height: 60upx;
        color: #fff;
        font-size: 24upx !important;
    }

    .tips {
        width: 654upx;
        height: 100upx;
        background: #FFFFFF;
        box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);
        border-radius: 12upx;
        opacity: 1;
        margin: 0 auto 40upx;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 0 20upx;

        .tips_left {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1d1d1f;
        }
    }

    .contain {
        box-sizing: border-box;
    }

    .cons {
        width: 100vw;
        height: 90vh;
        border-top-left-radius: 15upx;
        border-top-right-radius: 15upx;
        background: #fff;
        overflow-y: scroll;
        position: relative;

        .btn1 {
            width: 480upx;
            height: 96upx;
            background: rgba(217, 48, 37, 0.6);
            border-radius: 20upx;
            opacity: 1;
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            margin-top: 120upx;
        }

        .btn2 {
            width: 480upx;
            height: 96upx;
            background: rgba(29, 29, 31, 0.1);
            border-radius: 20upx;
            opacity: 1;
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-top: 80upx;
        }

        .details {
            box-sizing: border-box;
            padding: 0 32upx;

            .de_1 {
                box-sizing: border-box;
                padding: 80upx 0 28upx;
                text-align: center;
                border-bottom: 2upx solid #D2D2D7;

                .tt1 {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }

                .tt2 {
                    font-size: 68upx;
                    font-family: Hiragino Sans-W6, Hiragino Sans;
                    font-weight: normal;
                    color: #1A73E8;
                    margin-top: 20upx;
                }
            }

            .de_2 {
                box-sizing: border-box;
                padding: 40upx 0 80upx;
                text-align: center;
                border-bottom: 2upx solid #D2D2D7;

                .tt1 {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }
            }
        }

        .contitle {
            text-align: center;
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            position: sticky;
            top: 0;
            left: 0;
            width: 100%;
            height: 100upx;
            line-height: 100upx;
            z-index: 10002 !important;
            background: #fff;
        }

        .tts {
            text-align: center;
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            height: 100upx;
        }
    }

    .shopInfo {
        display: flex;
        align-items: center;
        border-bottom: 2upx solid #D2D2D7;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 0 32upx;
        width: 100%;
        height: 180upx;
        background: #FBFBFD;
    }

    .logouts {
        width: 200upx;
    }

    .logout {
        height: 68upx;
        width: 180upx;
        border-radius: 10upx;
        background: #eaeaea;
        border: 2upx solid #707070;
        margin: 0 !important;
        font-size: 24upx;
    }

    .shopName {
        font-family: 'Hiragino Sans';
        font-style: normal;
        font-weight: 600;
        font-size: 28upx;
        color: #1D1D1F;
    }

    .shopId {
        font-family: 'Hiragino Sans';
        font-style: normal;
        font-weight: 300;
        font-size: 28upx;
        color: #1D1D1F;
    }

    .content {
        display: flex;
        flex-wrap: wrap;
        box-sizing: border-box;
        padding: 80upx 48upx 0;
        justify-content: space-between;

        .indexItem {
            width: 180upx;
            height: 180upx;
            background: #fff;
            box-shadow: 0px 0px 6upx rgba(0, 0, 0, 0.160784);
            border-radius: 40upx;
            box-sizing: border-box;
            padding: 26upx;
            margin-bottom: 80upx;
            position: relative;

            .itemMask {
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.4);
                z-index: 99 !important;
                position: absolute;
                left: 0;
                top: 0;
            }

            .itemTop {
                width: 80upx;
                height: 80upx;
                margin: 0 auto;

                image {
                    width: 100%;
                    height: 100%;
                }
            }

            .itemBot {
                font-family: 'Hiragino Sans';
                font-style: normal;
                font-weight: 500;
                font-size: 20upx;
                text-align: center;
                color: #707070;
                margin-top: 16upx;
            }

            .bdg {
                width: 60upx;
                height: 60upx;
                border-radius: 50%;
                background: #D93025;
                position: absolute;
                top: -30upx;
                right: -30upx;
                z-index: 99;
                text-align: center;
                line-height: 60upx;
                color: #fff;
            }

            .g {
                background: #1A73E8 !important;
            }
        }
    }

    .sales {
        display: flex;
        width: 100%;
        height: 200upx;
        align-items: center;
        box-shadow: 0px 0px 12upx rgba(0, 0, 0, 0.160784);
        background-color: #fff;

        .saleItem {
            flex: 1;
            text-align: center;

            .saleTop {
                font-family: 'Hiragino Sans';
                font-style: normal;
                font-weight: 300;
                font-size: 32upx;
                color: #86868B;
            }

            .saleBot {
                font-family: 'Hiragino Sans';
                font-style: normal;
                font-weight: 400;
                font-size: 48upx;
                color: #007AFF;
                margin-top: 20upx;
            }

            .m {
                color: #1D1D1F;
            }
        }
    }
</style>