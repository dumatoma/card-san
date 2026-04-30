<template>
    <view>
        <view class="scontent">
            <view class="title" v-text="title"></view>
            <scroll-view scroll-y="true" class="sdetail">
                <view class="firstPart" v-text="content"></view>
                <view class="secondPart">
                    <view class="w1">
                        ウェブサイトでご契約し、クレジットカード決済をご利用の方は、このアプリからの以下の手続きができません。
                        <br /><br /><br />
                        ご利用プランの変更、支払い周期の変更の手続きは、現在、このアプリから対応しておりません。<br />

                        上記の手続きを行うには、現在の契約が一旦終了した後に、再度、手続きを行うことで、プラン変更、支払い周期の変更が可能です。
                        <br />
                        今後、ご利用プラン、支払い周期の変更をスムーズに行うには、現在の契約が一旦終了した後に、PCブラウザよりCard-Sanのwebサイトにログインしてお手続きをしてください。［アカウント］→［ご契約］ページからお手続きが可能です。<br />
                        ご不明な点や詳細は、カスタマーサポートにお問い合わせください。<br />
                    </view>
                    <view class="w2" @click="tocus">
                        カスタマーサポートはこちら
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
        name: "subscribe",
        props: {
            title: {
                type: String,
                default: () => {
                    return '';
                }
            },
            content: {
                type: String,
                default: () => {
                    return '';
                }
            }
        },
        data() {
            return {

            };
        },
        methods: {
            tocus(){
                uni.switchTab({
                    url:"/pages/help/help"
                })
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
                box-shadow: 0rpx 0rpx 20rpx 2rpx rgba(0, 0, 0, 0.07);
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