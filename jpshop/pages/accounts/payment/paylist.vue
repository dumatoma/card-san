<template>
    <view class="content border_top">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="お支払い方法を選択" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="payItem border_bottom" @click="toDetail(1)">
            <view class="payLeft">
                <view class="payName">
                    クレジットカード
                </view>
            </view>
            <view class="payRight">
                <u-icon name="arrow-right"></u-icon>
            </view>
        </view>

      <!--  <view class="payItem border_bottom" @click="toDetail(2)">
            <view class="payLeft">
                <view class="payLogo">
                    <image src="../../../static/icons/applePay.png" mode=""></image>
                </view>
                <view class="payName">
                    Apple Pay
                </view>
            </view>
            <view class="payRight">
                <u-icon name="arrow-right"></u-icon>
            </view>
        </view>

        <view class="payItem border_bottom" @click="toDetail(3)">
            <view class="payLeft">
                <view class="payLogo" style="width: 124upx;height: 60upx;">
                    <image src="../../../static/icons/googlePay.png" mode=""></image>
                </view>
                <view class="payName">
                    Google Pay
                </view>
            </view>
            <view class="payRight">
                <u-icon name="arrow-right"></u-icon>
            </view>
        </view> -->
    </view>
</template>

<script>
    import { setupVipCard } from '@/api/index.js'
    // カード番号・CVV は当社サーバーで扱わない（PCI DSS）。旧 payDetail（生カード入力）は廃止し、
    // Stripe の安全な入力画面（Checkout setup）で登録・変更する（accounts/payment/payment.vue と同じ流れ）
    export default {
        data() {
            return {

            };
        },
        methods: {
            toDetail(e) {
                uni.showLoading({ title: '読み込み中', mask: true })
                setupVipCard().then((res) => {
                    uni.hideLoading()
                    if (res.code == 200 && res.data.url) {
                        uni.showModal({
                            title: 'カード情報の登録・変更',
                            content: 'ブラウザでStripeの安全な入力画面を開きます。入力完了後、アプリに戻って再表示してください。',
                            cancelText: 'キャンセル',
                            confirmText: '開く',
                            success(r) {
                                if (!r.confirm) return
                                // #ifdef APP-PLUS
                                plus.runtime.openURL(res.data.url)
                                // #endif
                                // #ifndef APP-PLUS
                                window.location.href = res.data.url
                                // #endif
                            }
                        })
                    } else {
                        uni.showToast({ title: res.message || 'エラーが発生しました', icon: 'none' })
                    }
                }).catch(() => { uni.hideLoading() })
            }
        }
    }
</script>

<style lang="scss">
    .content {
        .payItem {
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
            padding: 64upx 40upx;

            .payLeft {
                display: flex;
                align-items: center;


                .payLogo {
                    width: 94upx;
                    height: 60upx;
                    margin-right: 20upx;

                    image {
                        width: 100%;
                        height: 100%;
                    }
                }
            }
        }
    }
</style>
