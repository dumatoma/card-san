<template>
    <!-- [カスタムエイド 2026-10-22] 管理WEBのスマホ版「お申し込み」と同じ見た目にそろえた(白いカード・選んだ周期は薄い青・カードは黒いブランド表示)。申し込みの処理は変更なし -->
    <view class="pm-page">
        <u-navbar title="お申し込み" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="pm-body">
            <!-- お申し込みプラン -->
            <view class="pm-card pm-plan">
                <view>
                    <view class="pm-cap">お申し込みプラン</view>
                    <view class="pm-val">{{ planName }}</view>
                </view>
                <view class="pm-link" hover-class="pm-press" @click="checkMore">プランを変更</view>
            </view>

            <!-- お支払い周期(アプリ内課金は 6ヶ月・月額 のみ) -->
            <view class="pm-card">
                <view class="pm-h2 pm-h2-list">お支払い周期を選択</view>
                <view v-for="c in cycles" :key="c.m" class="pm-cycle" :class="{ on: current == c.m }" @click="chooseTime(c.m)">
                    <view class="pm-radio" :class="{ on: current == c.m }"><view class="pm-radio-in"></view></view>
                    <view class="pm-cycle-txt">
                        <view class="pm-cycle-label">{{ c.label }}</view>
                        <view class="pm-cycle-detail">{{ c.detail }}</view>
                    </view>
                    <view class="pm-save" v-if="c.save">{{ c.save }}</view>
                </view>
            </view>

            <!-- お支払い方法 -->
            <view class="pm-card pm-pad">
                <view class="pm-h2">お支払い方法</view>
                <!-- [カスタムエイド 2026-10-18] 「App 課金」の固定表示をやめ、契約・端末で出し分ける -->
                <view class="pm-cardline" v-if="onCard && vipCard">
                    <text class="pm-brand">{{ vipCard.brand_name || 'クレジットカード' }}</text>
                    <text class="pm-last4">**** {{ vipCard.last4 }}</text>
                    <text class="pm-exp" v-if="vipCard.exp_month">有効期限 {{ expText }}</text>
                </view>
                <view class="pm-cardline" v-else>
                    <text class="pm-last4">{{ payMethodLabel }}</text>
                </view>
                <view class="pm-note" v-if="onCard">ご契約中のクレジットカードで変更されます。カードの変更は Card-San管理ウェブサイトの「ご契約内容」から行えます。</view>
            </view>

            <!-- ご契約内容 -->
            <view class="pm-card pm-pad">
                <view class="pm-h3">ご契約内容</view>
                <view class="pm-line">
                    <text>{{ planName }}</text>
                    <text class="pm-bold">{{ totalText }}</text>
                </view>
                <view class="pm-line pm-blue">
                    <text>契約更新日</text>
                    <text class="pm-bold">{{ refreshTime }}</text>
                </view>
                <view class="pm-att" v-if="vips.length == 2">
                    <text class="pm-red">※</text>ご契約内容の変更は、上記の契約更新日から適用されます。
                </view>
                <view class="pm-att" v-if="vips.length == 1 && platform == 'ios' && type > vips[0].type">
                    <text class="pm-red">※</text>アップグレードは今すぐ開始されます。現在のサブスクリプションの残りの期間分の料金は返金されます。
                </view>
                <view class="pm-att" v-if="vips.length == 1 && platform == 'android' && type > vips[0].type">
                    <text class="pm-red">※</text>アップグレードは今すぐ開始されます。現在のサブスクリプションの残りの期間分の料金と新しいサブスクリプションの料金の差額分をお支払いください。
                </view>
            </view>

            <view class="pm-agree">お申し込みにより、<text class="pm-agree-link" @click="toxie">利用規約・プライバシーポリシー</text>に同意したものとみなします。</view>

            <view class="pm-btn" hover-class="pm-press" @click="toSuccess1">この内容でお申し込み</view>
            <view style="height: 120rpx;"></view>
        </view>
        <u-popup :show="show" @close="show = false">
            <subscribe @btmnclick="show = false" :title="title" :content="content"></subscribe>
        </u-popup>
    </view>
</template>

<script>
    import { getPlatform } from '@/utils/platform.js' // [カスタムエイド 2026-10-18] 端末の種類はその場で判定(No.10)
    var googlePay = uni.requireNativePlugin('sn-googlepay5');
    import {
        getShopInfo,
        getPackageList,
        getVipCard,
        buyPlan,
        buyPlans,
        getConfig
    } from "@/api/index.js"
    import subscribe from "@/components/subscribe/subscribe.vue" // [カスタムエイド 2026-10-18] Figma の見本(黄色)の部品に切り替え
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
                refreshTime:"読み込み中…",
                datesLoaded: false, // [カスタムエイド 2026-10-18] 契約更新日の計算に必要な読み込みが終わったか(No.9)
                paying: false,
                vipCard: null // [カスタムエイド 2026-10-18] カード決済で契約中のカード(No.8)
            }
        },
        computed: {
            // [カスタムエイド 2026-10-22] 見た目をそろえるための表示用
            planName() {
                return this.type == 1 ? 'ライト' : this.type == 2 ? 'スタンダード' : 'プレミアム'
            },
            cycles() {
                const one = Number(this.price1) || 0, six = Number(this.price2) || 0
                const yen = (n) => '¥' + this.formatPriceWithCommas(Math.round(n))
                return [
                    { m: 6, label: '6ヶ月', detail: yen(six / 6) + ' × 6ヶ月', save: one * 6 - six > 0 ? yen(one * 6 - six) + ' お得!' : '' },
                    { m: 1, label: '月額', detail: yen(one) + ' × 1ヶ月', save: '' }
                ]
            },
            totalText() {
                const p = this.current == 1 ? this.price1 : this.current == 6 ? this.price2 : this.price3
                return '¥' + this.formatPriceWithCommas(Number(p) || 0) + '(' + this.current + 'ヶ月)'
            },
            onCard() {
                const v = this.vips.length > 0 ? this.vips[0] : null
                return !!(v && v.card_type == 1 && v.is_try != 1)
            },
            expText() {
                const c = this.vipCard || {}
                return ('0' + c.exp_month).slice(-2) + '/' + String(c.exp_year || '').slice(-2)
            },
            // [カスタムエイド 2026-10-18] お支払い方法の表示(No.8)
            payMethodLabel() {
                const v = this.vips.length > 0 ? this.vips[0] : null
                if (v && v.card_type == 1 && v.is_try != 1) {
                    const c = this.vipCard
                    return c ? ('クレジットカード ' + (c.brand_name || '') + ' **** ' + (c.last4 || '')) : 'クレジットカード'
                }
                return this.platform == 'ios' ? 'App Store 課金' : 'Google Play 課金'
            }
        },
        onLoad(options) {
            let that = this
            that.type = options.type
            that.getPlanList(options.type)
            getVipCard().then((res) => {
                if (res && res.code == 200) that.vipCard = res.data.card
            }).catch(() => {})
        },
        onShow() {
            let that = this
            that.platform = getPlatform()
            if(that.platform != "ios"){
                // [カスタムエイド 2026-10-18] Google課金の部品の準備で止まっても、下の読み込み(契約更新日)は必ず行う(No.9)
                try {
                    googlePay.init({}, (e) => {
                        if (e.code == 0) {
                            // 初始化成功
                        } else {
                            // 初始化失败
                        }
                    });
                    googlePay.isFeatureSupported((e) => {
                    });
                } catch (err) {
                    console.log('googlePay init error', err)
                }
            }
            that.loadDates(0)
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
                    plus.nativeUI.alert("お支払いの準備に失敗しました。しばらくしてから、もう一度お試しください。", function() {}, "エラー"); // [カスタムエイド 2026-10-18] 日本語に(No.12)
                })
                // #endif
            },
            // 获取内购项目列表
            requestOrder() {
                uni.showLoading({
                    title: 'お支払いの準備中…' // [カスタムエイド 2026-10-18] 日本語に(No.12)
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
                        });
                    }
                }, e => {
                });
            },
            toxie(){
              uni.navigateTo({
                  url:"/pages/accounts/rules/rules"
              })  
            },
            changeS(e) {
                let that = this
                that.show = false
                // Stripeユーザーの降级/同等プランはアプリ内課金不可（PCブラウザ誘導のみ）
                // 升级の場合はバックエンドがStripeキャンセル+返金するため許可
                const hasActiveStripe = that.using.card_type == 1
                    && that.vips.length > 0
                    && that.vips[0].cancel_time == 0
                const isUpgrade = Number(that.type) > Number(that.vips[0] ? that.vips[0].type : 0)
                if (hasActiveStripe && !isUpgrade) return
                let platform = getPlatform()
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
                                                       })
                                                   } else {
                                                       uni.showToast({
                                                           title: "支払いに失敗しました。再度お試しください。",
                                                           icon: "none",
                                                           duration: 3000
                                                       })
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
                }).catch(() => {})
            },
            toSuccess1() {
                let that = this
                // クレジットカード（WEB/Stripe）でご契約中の場合、アプリ内課金へ切り替えると二重課金になるため WEB で手続き
                const v = that.vips.length > 0 ? that.vips[0] : null
                const onStripe = v && v.card_type == 1 && v.is_try != 1
                if(onStripe){
                    that.show = true
                    // [カスタムエイド 2026-10-18] Figma の見本に合わせた。カード決済の人のプラン変更はウェブサイトで行うので、「上記内容を確認しました」は閉じるだけ
                    that.title = 'プラン変更について'
                    that.content = 'プラン・支払い周期の変更の手続きは、お手数ではございますが、Card-San管理ウェブサイトにログインしてください。'
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
                let platform = getPlatform()
            },
            checkMore() {
                uni.navigateTo({
                    url: "/pages/accounts/package/package"
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
                }).catch(() => {})
            },
            // [カスタムエイド 2026-10-18] 設定(今日の日付)と店舗情報(契約の終了日)を読み込む。失敗したら2回まで読み込み直す(No.9)
            loadDates(retry) {
                let that = this
                that.datesLoaded = false
                that.refreshTime = "読み込み中…"
                let fail = () => {
                    if (retry < 2) {
                        setTimeout(() => that.loadDates(retry + 1), 1500)
                    } else {
                        that.refreshTime = "読み込みに失敗しました。画面を開き直してください"
                    }
                }
                getConfig().then((res) => {
                    if(res.code == 200){
                        that.timestamp = res.data.config.timestamp
                        that.getShopInfo(fail)
                    } else {
                        fail()
                    }
                }).catch(fail)
            },
            chooseTime(e) {
                let that = this
                that.current = e
                // [カスタムエイド 2026-10-18] 読み込みが終わる前は計算しない(終了日0で計算して「1970年」になっていた。No.9)
                if (!that.datesLoaded) return
                that.updateRenewal()
            },
            // [カスタムエイド 2026-10-18] 契約更新日 =「次に請求される日」(修正一覧 No.12、資料「契約更新日の表示ずれ_決済別」)
            //  ・契約なし/無料お試し中:今日 + 期間(課金は申し込み日から始まる)
            //  ・アップグレード:App Store 課金・カード決済からの切り替えは 今日 + 期間(Apple は請求日が申し込み日に変わる。
            //    カード決済からアプリ内課金へのアップグレードは新しい契約が今日から始まる)
            //    Google Play 課金は請求日が変わらないため、今の契約の終了日
            //  ・ダウングレード・周期だけの変更・同じ内容:今の契約が終わる日に新しい内容で請求されるため、今の契約の終了日
            updateRenewal() {
                let that = this
                const v = that.vips.length > 0 ? that.vips[0] : null
                const today = that.timestamp * 1000
                if (!v || v.is_try == 1 || !that.endDate) {
                    that.calculateTime(today)
                    return
                }
                const upgrade = Number(that.type) > Number(v.type)
                if (upgrade && v.card_type != 3) {
                    that.calculateTime(today)
                    return
                }
                that.showDate(that.endDate * 1000)
            },
            // 日付をそのまま表示する(期間を足さない)
            showDate(ms) {
                if (!ms || Number(ms) <= 0 || isNaN(Number(ms))) {
                    this.refreshTime = "読み込み中…"
                    return
                }
                this.refreshTime = this.getHalfYearLater(Number(ms))
            },
            
            calculateTime(newDate){
                let that = this 
                // [カスタムエイド 2026-10-18] 日付が無い(0・空)ときは計算しない(No.9)
                if (!newDate || Number(newDate) <= 0 || isNaN(Number(newDate))) {
                    that.refreshTime = "読み込み中…"
                    return
                }
                let dates = new Date(newDate)
                let result
                if(that.current == 12){
                   result = dates.setFullYear(dates.getFullYear() + 1);
                }else if(that.current == 6){
                    result = dates.setMonth(dates.getMonth() + 6);
                }else if(that.current == 1){
                    result = dates.setMonth(dates.getMonth() + 1);
                }
                that.refreshTime = that.getHalfYearLater(result)
            },
            
            getShopInfo(fail) {
                let that = this
                getShopInfo().then((res) => {
                    if (!res || res.code != 200 || !res.data || !res.data.shop_info) {
                        if (fail) fail()
                        return
                    }
                    that.using = res.data.shop_info.vip
                    that.datesLoaded = true
                    if (res.data.shop_info.vips.length > 0) {
                        that.vips = res.data.shop_info.vips
                        that.endDate = res.data.shop_info.vips[0].end_timestamp
                    }else{
                        that.vips = []
                        that.endDate = ""
                    }
                    that.updateRenewal() // [カスタムエイド 2026-10-18] No.12
                }).catch(() => { if (fail) fail() })
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
              return formattedDate;
            },
        }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-22] 管理WEBのスマホ版(sp.css / spConfirm.vue)と同じ色・形。1px = 2rpx で写している */
    .pm-page { min-height: 100vh; background: #f5f5f7; color: #1d1d1f; }
    .pm-body { padding: 40rpx 32rpx 0; display: flex; flex-direction: column; gap: 32rpx; }
    .pm-card { background: #fff; border-radius: 28rpx; overflow: hidden; }
    .pm-pad { padding: 28rpx 32rpx 32rpx; display: flex; flex-direction: column; gap: 24rpx; }
    .pm-plan { padding: 28rpx 32rpx; display: flex; align-items: center; justify-content: space-between; }
    .pm-cap { font-size: 24rpx; color: #6e6e73; }
    .pm-val { margin-top: 4rpx; font-size: 34rpx; font-weight: 700; }
    .pm-link { padding: 16rpx 0 16rpx 24rpx; font-size: 26rpx; font-weight: 500; color: #1a73e8; }
    .pm-press { opacity: 0.6; }
    .pm-h2 { font-size: 30rpx; font-weight: 700; }
    .pm-h2-list { padding: 28rpx 32rpx 16rpx; }
    .pm-h3 { font-size: 26rpx; font-weight: 700; }
    /* 周期の行(選んでいる行は薄い青) */
    .pm-cycle { min-height: 128rpx; padding: 20rpx 32rpx; border-top: 2rpx solid #f0f0f2; display: flex; align-items: center; gap: 24rpx; background: #fff; box-sizing: border-box; }
    .pm-cycle.on { background: #eef4fe; }
    .pm-radio { width: 44rpx; height: 44rpx; flex-shrink: 0; border-radius: 50%; border: 4rpx solid #c7c7cc; display: flex; align-items: center; justify-content: center; box-sizing: border-box; }
    .pm-radio-in { width: 20rpx; height: 20rpx; border-radius: 50%; background: transparent; }
    .pm-radio.on { border-color: #1a73e8; }
    .pm-radio.on .pm-radio-in { background: #1a73e8; }
    .pm-cycle-txt { flex: 1; display: flex; flex-direction: column; gap: 4rpx; }
    .pm-cycle-label { font-size: 30rpx; font-weight: 700; }
    .pm-cycle-detail { font-size: 26rpx; color: #515154; }
    .pm-save { flex-shrink: 0; font-size: 26rpx; font-weight: 700; color: #c5221f; }
    /* お支払い方法 */
    .pm-cardline { display: flex; align-items: center; flex-wrap: wrap; gap: 20rpx; padding: 24rpx; border: 2rpx solid #e5e5ea; border-radius: 20rpx; }
    .pm-brand { padding: 4rpx 16rpx; border-radius: 8rpx; background: #1d1d1f; color: #fff; font-size: 22rpx; font-weight: 700; }
    .pm-last4 { font-size: 30rpx; }
    .pm-exp { margin-left: auto; font-size: 24rpx; color: #6e6e73; }
    .pm-note { font-size: 24rpx; line-height: 1.7; color: #515154; }
    /* ご契約内容 */
    .pm-line { display: flex; justify-content: space-between; font-size: 28rpx; }
    .pm-bold { font-weight: 700; }
    .pm-blue { color: #1a73e8; }
    .pm-att { font-size: 24rpx; line-height: 1.7; color: #515154; }
    .pm-red { color: #d93025; }
    /* 同意の文とボタン */
    .pm-agree { margin-top: 8rpx; text-align: center; font-size: 24rpx; line-height: 1.7; color: #515154; }
    .pm-agree-link { color: #1a73e8; text-decoration: underline; }
    .pm-btn { margin-top: 32rpx; height: 104rpx; border-radius: 52rpx; background: #1a73e8; color: #fff; font-size: 32rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; }
</style>
