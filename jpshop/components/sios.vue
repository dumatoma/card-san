<template>
    <view>
        <view class="scontent">
            <view class="title">解約手続きについて</view>
            <scroll-view scroll-y="true" class="sdetail">
                <view class="secondPart">
                    <view class="w1">
                        <text style="font-weight: bold;">このアプリからはサブスクリプションの解約手続きができません。</text>
                        <br /><br />
                        このサブスクリプションを解約方法
                        <br /><br />
                        「設定 App」→「自分の名前」→「サブスクリプション」→「サブスクリプションをキャンセルする」から解約手続きをお願いします。
                        <br /><br />
                        詳細は、Appleサポートページをご確認ください。
                    </view>
                    <view class="w2" @click="tocus">
                        Appleサポートページはこちら
                    </view>
                    <!-- <view class="w1">
                        それ以外のお手続きは、Card-Sanのwebサイト【アカウント】からお手続きをお願いします。
                    </view> -->
                </view>
                <view class="bt" @click="getstatus">
                    上記内容を確認しました
                </view>
            </scroll-view>
        </view>
    </view>
</template>
<script>
    export default {
        name: "sios",
        data() {
            return {

            };
        },
        methods: {
            tocus(){
                let that = this
                let u = "https://support.apple.com/ja-jp/HT202039"
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
            getstatus() {
                if (this.title == '解約手続きについて') {
                    this.$emit("btmnclick", 0)
                } else {
                    this.$emit("btmnclick", 1)
                }

            }
        }
    }
</script>

<style lang="scss">
    page {}

    .ols {
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, .6);
        position: fixed;
        top: 0;
        left: 0;
    }

    .bt {
        width: 100%;
        height: 112upx;
        line-height: 112upx;
        background: #06C755;
        border-radius: 56rpx;
        text-align: center;
        color: #fff;
        font-size: 40rpx;
        font-weight: bold;
        margin-top: 100upx;
    }

    .scontent {
        width: 100vw;
        height: 90vh;
        background: #fff;

        .title {
            width: 100%;
            height: 112upx;
            background: #eaeaea;
            text-align: center;
            line-height: 112upx;
            font-size: 32rpx;
            font-weight: bold;
            color: #1D1D1F;
        }

        .sdetail {
            box-sizing: border-box;
            padding: 40upx 34upx;
            height: calc(90vh - 112upx);

            .firstPart {
                width: 100%;
                box-sizing: border-box;
                padding: 32upx;
                font-size: 32rpx;
                font-weight: bold;
                color: #1D1D1F;
                background: rgba(166, 170, 241, 1);
            }

            .secondPart {
                box-shadow: 0rpx 0rpx 20rpx 2rpx rgba(0, 0, 0, 0.1);
                background: #fff;
                box-sizing: border-box;
                padding: 32upx;
                margin-top: 42upx;
                border: 2rpx solid #D2D2D7;
                .w1 {
                    font-size: 32rpx;
                    font-family: Noto Sans Kannada, Noto Sans Kannada;
                    font-weight: 400;
                    color: #1D1D1F;
                }

                .w2 {
                    color: #1A73E8 !important;
                    margin: 40upx 0;
                }
            }

        }
    }
</style>