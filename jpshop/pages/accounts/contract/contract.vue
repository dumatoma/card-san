<template>
    <view>
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="ご契約内容" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-20] 見た目を管理WEB(スマホ・タブレット版)の「ご契約内容」に合わせた。処理(ボタンを押したときの動き)は今までと同じ -->
        <view class="ct">
            <!-- 契約期間終了(契約がない) -->
            <view class="ct-alert red" v-if="isexpire == false">
                <view class="ct-alert-title">ご契約期間終了のため、現在ご利用できません。</view>
                <view class="ct-alert-text">お申し込みを再開することで、すぐにご利用ができます。</view>
                <view class="ct-btn" hover-class="ct-hover" @click="tone">再開はこちら</view>
            </view>
            <!-- 解約手続き済み(終了日まで使える) -->
            <view class="ct-alert" v-if="isexpire == true && vips[0].cancel_time > 0">
                <view class="ct-alert-row">
                    <view class="ct-i">!</view>
                    <view class="ct-alert-text" v-if="vips[0].card_type == 1">ご契約は <text class="bd">{{vips[0].end_time}}</text> に終了します(解約手続き済み)。終了日までは、すべての機能をご利用いただけます。</view>
                    <view class="ct-alert-text" v-else>ご契約は <text class="bd">{{vips[0].end_time}}</text> に終了します(解約手続き済み)。ご利用を継続するには、各端末の「設定」から手続きをお願いします。</view>
                </view>
                <view class="ct-btn" hover-class="ct-hover" v-if="vips[0].card_type == 1" @click="toResume">継続する</view>
            </view>

            <!-- ご契約 -->
            <view class="ct-card">
                <view class="ct-sec">ご契約</view>
                <view class="ct-row">
                    <view class="ct-row-left">
                        <view class="ct-cap">ご利用中のプラン</view>
                        <view class="ct-val">{{planName(isexpire == true ? vips[0].type : (vip || {}).type)}}</view>
                    </view>
                    <view class="ct-pill" hover-class="ct-hover" @click="changeT">プランを変更</view>
                </view>
                <view class="ct-row" v-if="vips[0].is_try != 1">
                    <view class="ct-row-left">
                        <view class="ct-cap">支払い周期</view>
                        <view class="ct-val">{{(isexpire == true ? vips[0].month : (vip || {}).month) ? (isexpire == true ? vips[0].month : (vip || {}).month) + 'ヶ月' : '-'}}</view>
                    </view>
                    <view class="ct-pill" hover-class="ct-hover" @click="changeZ">周期を変更</view>
                </view>
                <view class="ct-row" v-if="isexpire == true && vips[0].is_try == 1">
                    <view class="ct-row-left">
                        <view class="ct-cap">無料お試し</view>
                        <view class="ct-val">残り{{vips[0].end_day}}日</view>
                    </view>
                </view>
                <view class="ct-row" v-if="vips[0].is_try != 1">
                    <view class="ct-row-left">
                        <view class="ct-cap">{{isexpire == true && vips[0].cancel_time > 0 ? '契約終了日' : '契約更新日'}}</view>
                        <view class="ct-val">{{isexpire == true && vips[0].end_time ? vips[0].end_time : '-'}}</view>
                    </view>
                </view>
                <view class="ct-box yellow" v-if="isexpire == true && vips.length == 2">
                    次回更新日({{formatTime(vips[1].start_time).replace(' ', '')}})から「{{planName(vips[1].type)}}({{vips[1].month}}ヶ月)」に変更予定です。
                </view>
                <view class="ct-box blue" v-if="isexpire == true && vips[0].is_try == 1">
                    <view class="bd">無料お試し期間中です</view>
                    <view>お申し込みは、下の「お申し込みはこちら」から行えます。</view>
                </view>
            </view>

            <!-- お支払い方法 -->
            <view class="ct-card">
                <view class="ct-sec">お支払い方法</view>
                <view class="ct-pay" v-if="isexpire == true && vips[0].is_try != 1">
                    <view class="ct-pay-name">{{vips[0].card_type == 1 ? 'クレジットカード決済' : vips[0].card_type == 2 ? 'App Store 課金' : 'Google Play 課金'}}</view>
                    <view class="ct-cardline" v-if="vips[0].card_type == 1 && card">
                        <text class="ct-brand">{{card.brand_name}}</text>
                        <text class="ct-last4">**** {{card.last4}}</text>
                        <text class="ct-exp">有効期限 {{('0' + card.exp_month).slice(-2)}}/{{String(card.exp_year).slice(-2)}}</text>
                    </view>
                    <view class="ct-small" v-if="vips[0].card_type == 1">カードの変更は、Card-San管理ウェブサイトから行えます。</view>
                </view>
                <view class="ct-pay ct-muted" v-else>{{isexpire == true && vips[0].is_try == 1 ? '無料お試し中のため、お支払い方法の登録はありません。' : '-'}}</view>
            </view>

            <view class="ct-btn ct-mt" hover-class="ct-hover" v-if="vips.length != 0 && vips[0].is_try == 1" @click="toc">お申し込みはこちら</view>
            <view class="ct-link-red" hover-class="ct-hover" v-if="isexpire == true && vips[0].cancel_time == 0 && vips[0].is_try == 0" @click="toff">解約手続き</view>
        </view>
        <u-popup :show="show" @close="show = false">
            <subscribe @btmnclick="changeS" :title="title" :content="content"></subscribe>
        </u-popup>
        <u-popup :show="show2" @close="show2 = false">
            <subscribe @btmnclick="changeSs" :title="title" :content="content"></subscribe>
        </u-popup>
        <u-popup :show="show3" @close="show3 = false">
           <sios @btmnclick="show3 = false"></sios>
        <!--  <view style="width: 100vw;height: 60vh;background: #d93025;">
              
          </view> -->
        </u-popup>
    </view>
</template>

<script>
    import { getPlatform } from '@/utils/platform.js' // [カスタムエイド 2026-10-18] 端末の種類はその場で判定(No.10)
    import subscribe from "@/components/subscribe/subscribe.vue"
    import subscribee from "@/components/su1b/su1b.vue"
    import sios from "@/components/sios.vue"
    import {
        getShopInfo,
        getPackageList,
        delPlan,
        getVipCard,
        resumePlan
    } from "@/api/index.js"
    export default {
        components: {
            subscribe,
            subscribee,
            sios
        },
        data() {
            return {
                show: false,
                shows: false,
                show2: false,
                show3: false,
                number: 100,
                date: "2022/07/06 15:08",
                info: {},
                endTime: "",
                startTime: "",
                title: "",
                vips: [{}],
                content: '',
                plt: "",
                isexpire: true,
                card: null,
                resuming: false
            };
        },
        onLoad() {
            let that = this
        },
        onShow() {
            uni.showLoading({
                title: "読み込み中",
                mask:true
            })
            this.getvip()
            this.plt = getPlatform()
            getVipCard().then((res) => {
                if (res.code == 200) this.card = res.data.card
            }).catch(() => {})
        },
        methods: {
            // [カスタムエイド 2026-10-20] プランの名前
            planName(t) {
                return t == 1 ? 'ライト' : t == 2 ? 'スタンダード' : t == 3 ? 'プレミアム' : '-'
            },
            toResume() {
                let that = this
                uni.showModal({
                    title: 'ご契約を継続しますか？',
                    content: '解約手続きを取り消し、現在のプランを継続します。新たな料金は発生しません。',
                    cancelText: 'キャンセル',
                    confirmText: '継続する',
                    success(r) {
                        if (!r.confirm || that.resuming) return
                        that.resuming = true
                        uni.showLoading({ title: '処理中', mask: true })
                        resumePlan(that.vips[0].id).then((res) => {
                            that.resuming = false
                            uni.hideLoading()
                            uni.showModal({
                                title: res.code == 200 ? 'お知らせ' : 'エラー',
                                content: res.message || (res.code == 200 ? '継続の手続きが完了しました' : '継続の手続きに失敗しました'),
                                showCancel: false,
                                confirmText: '閉じる'
                            })
                            that.getvip()
                        }).catch(() => {
                            that.resuming = false
                            uni.hideLoading()
                        })
                    }
                })
            },
            tone(){
                let platform = getPlatform()
                if(this.isexpire == true){
                    uni.navigateTo({
                        url: "/pages/payment/payment?type="+this.vips[0].type
                    })
                }else{
                    uni.navigateTo({
                        url: "/pages/payment/payment?type="+((this.vip||{}).type||1)
                    })
                }
            },
            changeS(e) {
                let that = this
                that.show = false
                if (e == 0) {
                    let id = that.vips[0].id
                    uni.navigateTo({
                        url: "../breakoff/breakoff?id=" + id
                    })
                    // delPlan(id).then((res) => {
                    //     if(res.code == 200){
                    //         uni.showToast({
                    //             title:res.message,
                    //             duration:2500
                    //         })
                    //         setTimeout(() => {
                    //             uni.switchTab({
                    //                 url:"/pages/index/index"
                    //             })
                    //         },2500)
                    //     }
                    // })
                } else {
                    uni.navigateTo({
                        url: "/pages/payment/payment"
                    })
                }
            },
            changeSs(e) {
                let that = this
                that.show = false
                if (e == 0) {
                    that.show2 = false
                } else {
                    uni.navigateTo({
                        url: "/pages/payment/payment"
                    })
                }
            },
            // [カスタムエイド 2026-10-18] 解約の案内は、端末ではなく「契約の支払い方法」で出し分ける(修正一覧 No.11)
            // 以前は iPhone なら支払い方法に関係なく Apple 用の案内が出ていた(カード決済の人にも)
            toff() {
                let platform = getPlatform()
                let ct = Number(this.info.card_type)
                if (ct == 1) {
                    // クレジットカード(Stripe)決済:ウェブサイトで解約
                    this.title = "解約手続きについて"
                    this.content = "解約手続きは、お手数ではございますが、Card-San管理ウェブサイトにログインしてください。" // [カスタムエイド 2026-10-18] Figma に合わせた
                    this.show2 = true
                } else if (ct == 2) {
                    // App Store 課金:iPhone の設定から解約(Apple 用の案内)
                    this.show3 = true
                } else if (ct == 3 && platform == 'ios') {
                    // Google Play 課金なのに iPhone で開いている:Android の Playストアで解約
                    this.title = "解約手続きについて"
                    this.content = "Google Play でご契約のサブスクリプションは、このiPhoneからは解約できません。ご契約時のAndroid端末の「Playストア」アプリ→[定期購入]→「CardSan管理」→[定期購入を解約]から解約手続きをお願いします。"
                    this.show2 = true
                } else if (platform != 'ios') {
                    // Google Play 課金(Android):今までどおりアプリ内の解約手続きへ
                    this.changeS(0)
                } else {
                    this.show3 = true
                }
            },
            toc() {
                if (this.info.card_type == 1) {
                    this.title = "ご利用のご継続について"
                    this.content = "ご利用のご継続手続きは、お手数ではございますが、Card-San管理ウェブサイトにログインしてください。" // [カスタムエイド 2026-10-18] 解約の案内と同じ言い方
                    this.show = true
                } else {
                    uni.navigateTo({
                        url: "/pages/accounts/package/package"
                    })
                }

            },
            changeT() {
                uni.navigateTo({
                    url: "/pages/accounts/package/package"
                })
            },
            changeZ() {
                uni.navigateTo({
                    url: "/pages/payment/payment?type=" + this.vips[0].type
                })
            },
            formatTime(e) {
                let that = this
                let d = new Date(e * 1000)
                var Y = d.getFullYear();
                var M =
                    (d.getMonth() + 1 < 10 ?
                        "0" + (d.getMonth() + 1) :
                        d.getMonth() + 1);
                var D = (d.getDate() < 10 ? "0" + d.getDate() : d.getDate()) + " ";
                return Y + '年' + M + "月" + D + "日";
            },
            close() {
                this.show = false
            },
            getvip() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        that.info = res.data.shop_info.vip
                        let date = new Date(res.data.shop_info.vip.end_time * 1000)
                        const Y = date.getFullYear()
                        const M = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                        that.endTime = `${Y}/${M}`
                        let date1 = new Date(res.data.shop_info.vip.start_time * 1000)
                        const Y1 = date1.getFullYear()
                        const M1 = date1.getMonth() + 1 < 10 ? '0' + (date1.getMonth() + 1) : date1.getMonth() +
                            1
                        that.startTime = `${Y1}/${M1}`

                        if (res.data.shop_info.vips.length > 0) {
                            that.vips = res.data.shop_info.vips
                            that.isexpire = true
                        } else {
                            that.isexpire = false
                        }
                        that.vip = res.data.shop_info.last_vip
                    }
                    uni.hideLoading()
                }).catch(() => { uni.hideLoading() })
            },
            tobuy() {
                uni.navigateTo({
                    url: "../package/package"
                })
            },
            tobuy1() {
                uni.navigateTo({
                    url: "../../payment/payment?type=" + this.info.type
                })
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-20] 管理WEB(スマホ・タブレット版)と同じ見た目 */
    .ct {
        min-height: 100vh;
        background: #F5F5F7;
        padding: 32rpx 32rpx calc(80rpx + env(safe-area-inset-bottom));
        box-sizing: border-box;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .bd {
        font-weight: 700;
    }

    .ct-hover {
        opacity: 0.6;
    }

    .ct-card {
        background: #FFFFFF;
        border-radius: 28rpx;
        overflow: hidden;
        margin-bottom: 32rpx;
    }

    .ct-sec {
        padding: 28rpx 32rpx 12rpx;
        font-size: 26rpx;
        font-weight: 500;
        color: #6E6E73;
    }

    .ct-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20rpx 32rpx;
        border-bottom: 1px solid #F0F0F2;
    }

    .ct-row:last-child {
        border-bottom: 0;
        padding-bottom: 32rpx;
    }

    .ct-row-left {
        flex: 1;
        min-width: 0;
        margin-right: 24rpx;
    }

    .ct-cap {
        font-size: 24rpx;
        color: #6E6E73;
        margin-bottom: 4rpx;
    }

    .ct-val {
        font-size: 34rpx;
        font-weight: 700;
    }

    .ct-pill {
        flex-shrink: 0;
        height: 80rpx;
        line-height: 78rpx;
        padding: 0 28rpx;
        border: 1px solid #1A73E8;
        border-radius: 40rpx;
        color: #1A73E8;
        font-size: 26rpx;
        font-weight: 500;
        box-sizing: border-box;
    }

    .ct-box {
        margin: 0 32rpx 32rpx;
        padding: 20rpx 24rpx;
        border-radius: 20rpx;
        font-size: 24rpx;
        line-height: 1.7;
    }

    .ct-box.yellow {
        background: #FFF8E1;
        color: #5F4B00;
    }

    .ct-box.blue {
        background: #E8F0FE;
        color: #174EA6;
    }

    .ct-pay {
        padding: 16rpx 32rpx 32rpx;
    }

    .ct-pay-name {
        font-size: 30rpx;
        font-weight: 500;
        margin-bottom: 20rpx;
    }

    .ct-muted {
        font-size: 28rpx;
        color: #515154;
    }

    .ct-cardline {
        display: flex;
        align-items: center;
        padding: 24rpx;
        border: 1px solid #E5E5EA;
        border-radius: 20rpx;
        margin-bottom: 16rpx;
    }

    .ct-brand {
        padding: 4rpx 16rpx;
        border-radius: 8rpx;
        background: #1D1D1F;
        color: #FFFFFF;
        font-size: 22rpx;
        font-weight: 700;
        margin-right: 20rpx;
    }

    .ct-last4 {
        font-size: 30rpx;
        letter-spacing: 0.04em;
    }

    .ct-exp {
        margin-left: auto;
        font-size: 24rpx;
        color: #6E6E73;
    }

    .ct-small {
        font-size: 22rpx;
        line-height: 1.7;
        color: #6E6E73;
    }

    .ct-alert {
        background: #FFF8E1;
        border: 1px solid #F0D77A;
        border-radius: 28rpx;
        padding: 28rpx 32rpx;
        margin-bottom: 32rpx;
    }

    .ct-alert.red {
        background: #FFFFFF;
        border-color: #F3C4C2;
        text-align: center;
    }

    .ct-alert-row {
        display: flex;
        align-items: flex-start;
        margin-bottom: 24rpx;
    }

    .ct-i {
        flex-shrink: 0;
        width: 36rpx;
        height: 36rpx;
        line-height: 32rpx;
        margin: 4rpx 20rpx 0 0;
        border: 2px solid #8A6D00;
        border-radius: 50%;
        box-sizing: border-box;
        text-align: center;
        font-size: 22rpx;
        font-weight: 700;
        color: #8A6D00;
    }

    .ct-alert-text {
        font-size: 28rpx;
        line-height: 1.7;
        color: #5F4B00;
    }

    .ct-alert.red .ct-alert-text {
        color: #1D1D1F;
        margin-bottom: 24rpx;
    }

    .ct-alert-title {
        font-size: 32rpx;
        font-weight: 700;
        line-height: 1.6;
        color: #C5221F;
        margin-bottom: 16rpx;
    }

    .ct-btn {
        height: 96rpx;
        line-height: 96rpx;
        border-radius: 48rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 30rpx;
        font-weight: 700;
    }

    .ct-mt {
        margin-top: 16rpx;
    }

    .ct-link-red {
        margin-top: 16rpx;
        height: 88rpx;
        line-height: 88rpx;
        text-align: center;
        font-size: 28rpx;
        color: #C5221F;
    }
</style>
