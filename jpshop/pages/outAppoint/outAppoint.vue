<template>
    <view class="containers">
        <view class="words">
            Webサイトのボタンやページに以下のLINK URLをコピーして貼り付けてください。　アプリと連携した予約ページが作成できます。
        </view>
        <view class="words" style="margin-top: 40upx;margin-bottom: 100upx;">
            <text style="color: #d93025;">※</text>ただしスマートフォン、タブレットサイトでの表示のみ対応可。PCサイトには対応していません。
        </view>
        <view class="titel">
            予約ページLINK URL
        </view>
        <view class="uirls" v-text="link"></view>
        <view class="cop" hover-class="hit" @click="copy">
            <text>URLコピー</text>
        </view>
        <view class="mask" v-if="showA == true">
            <view class="maskcontent">
                <image src="../../static/svg/gray.svg" class="mi" mode=""></image>
                <view>
                     コピーしました
                </view>
            </view>
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
            that.link = "https://order.card-san.jp/#/?id=" + info.sid
        },
        methods: {
            copy() {
                let that = this
                uni.setClipboardData({
                    data: that.link,
                    success: () => {
                        uni.hideToast()
                        that.showA = true
                        setTimeout(() => {
                            that.showA = false
                        },1500)
                    }
                })
            }
        }
    }
</script>

<style lang="less">
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
        border-top: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding: 100upx 52upx;
    }

    .words {
        font-size: 28upx;
        font-family: Noto Sans Kannada-Regular, Noto Sans Kannada;
        font-weight: 400;
        color: #1D1D1F;
    }
</style>