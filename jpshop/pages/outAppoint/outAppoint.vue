<template>
    <view class="containers">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="予約ページのURL" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-22] 外部リンク(メニュー)と同じ見た目に:説明は青い帯、URLは枠の中、青い「URLをコピー」ボタン -->
        <view class="oa-body">
            <view class="oa-info">ホームページ・Instagram のプロフィール・Googleビジネスプロフィールなどのボタンに、このURLを貼り付けてください。お客さまはそこから予約ページを開いて予約できます(アプリと同じ予約表につながります)。</view>
            <view class="oa-card">
                <view class="oa-label">予約ページのURL</view>
                <view class="oa-url" @longpress="copy">{{ link }}</view>
                <view class="oa-cap">スマホ・タブレット・パソコンのどれでも開けます。</view>
            </view>
            <view class="oa-btn" hover-class="oa-hover" @click="copy">URLをコピー</view>
            <view class="oa-btn2" hover-class="oa-hover" @click="openPage">予約ページを開いて確かめる</view>
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                link: "",
                showA:false
            };
        },
        onShow() {
            let that = this
            let info = uni.getStorageSync("admin")
            that.link = "https://order.card-san.jp/reserve/?id=" + info.sid // [カスタムエイド 2026-10-13] 新しい予約URL(アプリの次回更新で反映)
        },
        methods: {
            // [カスタムエイド 2026-10-22] 予約ページをブラウザで開く
            openPage() {
                // #ifdef APP-PLUS
                plus.runtime.openURL(this.link)
                // #endif
                // #ifdef H5
                window.open(this.link)
                // #endif
            },
            copy() {
                let that = this
                uni.setClipboardData({
                    data: that.link,
                    success: () => {
                        uni.hideToast()
                        // [カスタムエイド 2026-10-22] アプリ共通の小さなお知らせ「✓ コピーしました」に
                        setTimeout(() => { uni.showToast({ title: "コピーしました" }) }, 50)
                    }
                })
            }
        }
    }
</script>

<style lang="less">
    /* [カスタムエイド 2026-10-22] 外部リンク(メニュー)と同じ決まり */
    .oa-body { padding: 48rpx 24rpx calc(80rpx + env(safe-area-inset-bottom)); }
    .oa-info { padding: 24rpx 28rpx; border-radius: 24rpx; background: #EEF4FF; font-size: 26rpx; line-height: 1.7; color: #1D1D1F; margin-bottom: 48rpx; } /* [カスタムエイド 2026-10-22] 1.5倍 */
    .oa-card { background: #FFFFFF; border-radius: 32rpx; padding: 40rpx 32rpx; margin-bottom: 60rpx; }
    .oa-label { font-size: 26rpx; font-weight: 500; line-height: 1.2; margin-bottom: 8rpx; color: #1D1D1F; }
    .oa-url { padding: 24rpx 28rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; background: #F5F5F7; font-size: 30rpx; line-height: 1.5; color: #1D1D1F; word-break: break-all; }
    .oa-cap { font-size: 24rpx; color: #6E6E73; margin-top: 24rpx; line-height: 1.5; }
    .oa-btn { height: 104rpx; line-height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; text-align: center; font-size: 32rpx; font-weight: 700; }
    .oa-btn2 { margin-top: 48rpx; height: 104rpx; line-height: 104rpx; border-radius: 28rpx; background: #FFFFFF; color: #1A73E8; text-align: center; font-size: 30rpx; }
    .oa-hover { opacity: 0.7; }

    .hit {
        background: rgba(29, 29, 31, 0.1) !important;
    }
    .mask{
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(29,29,31,0.2);
        z-index: 999;
        
        .maskcontent{
            width: 356upx;
            height: 292upx;
            background: #fff;
            border-radius: 20upx;
            text-align: center;
            box-sizing: border-box;
            padding: 60upx;
            font-size: 32upx;
            color: #707070;
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%,-50%);
            
            .mi{
                width: 100upx;
                height: 100upx;
                margin-bottom: 40upx;
            }
            
        }
    }
    .cop {
        width: 478rpx;
        // height: 96rpx;
        padding: 28upx 0;
        background: rgba(29, 29, 31, 0.2);
        border-radius: 20rpx 20rpx 20rpx 20rpx;
        opacity: 1;
        border: 2rpx solid #1D1D1F;
        text-align: center;
        // line-height: 96upx;
        font-size: 40rpx;
        font-family: Noto Sans Kannada-Regular, Noto Sans Kannada;
        color: #1D1D1F;
        margin: 100upx auto 0;
        display: flex;
        align-items: center;
        // padding: 0;
        justify-content: center;
    }

    .titel {
        font-size: 28rpx;
        font-family: Noto Sans Kannada-Regular, Noto Sans Kannada;
        color: #1D1D1F;
        // margin-bottom: 100upx;
    }

    .uirls {
        width: 100%;
        height: 400upx;
        border-radius: 20upx;
        border: 2rpx solid #AAAAAA;
        box-sizing: border-box;
        padding: 20upx;
        word-wrap: break-word;
        word-break: normal;
    }

    .containers {
        width: 100vw;
        min-height: 100vh;
        box-sizing: border-box;
        background: #F5F5F7;
    }

    .words {
        font-size: 28upx;
        font-family: Noto Sans Kannada-Regular, Noto Sans Kannada;
        font-weight: 400;
        color: #1D1D1F;
    }
</style>