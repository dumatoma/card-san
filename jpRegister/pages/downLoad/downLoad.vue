<template>
    <view class="content">
        <view class="logo">
            <!-- <image src="../../static/logo.png" mode="heightFix"></image> -->
            <image src="../../static/icon_rogo.svg" mode="heightFix"></image>
        </view>
        <view class="container">
            <view class="title1 pd">
                ご登録ありがとうございます！
            </view>
            <view class="title2 pd">
                あなたのアカウントID
            </view>
            <view class="title3 pd" v-text="query.email"></view>
            <view class="caution pd">
                ご登録のEメールアドレスに「アカウント登録完了」のEメールを送信しましたのでご確認ください。　万が一、メールが届いていない場合は、迷惑メールフォルダをご確認お願いします。
            </view>
            <view class="pd">
                <view class="tip">
                    <view class="tipLeft">
                        <u-icon name="info-circle-fill" color="#D93025" size="14"></u-icon>
                    </view>
                    <view class="tipRight">
                        スマートフォン、タブレットで運用される場合は、専用の管理アプリ「CardSan管理」を、ご使用の端末に必ずダウンロードしてください。
                    </view>
                </view>
            </view>

            <view class="download">
                <view class="downloadLogo">
                    <!-- <image src="../../static/dlogo.png" mode=""></image> -->
                    <image src="../../static/cardsan_kanri_icon.svg" mode=""></image>
                </view>
                <view class="downname">
                    CardSan管理
                </view>
                <view class="des">
                    アプリ作成、運用、契約、その他全ての業務を、この管理用アプリで行うことができます。
                </view>
                <view class="d_item" @click="toOther(0)">
                    <view class="itemName">
                        【 iOS端末をお使いの方】
                    </view>
                    <view class="item_logo">
                        <!-- <image src="../../static/appStore.png" mode=""></image> -->
                        <image src="../../static/Download_on_the_App_Store_Badge_JP_RGB_blk_100317.svg" mode=""></image>
                    </view>
                </view>
                <view class="d_item" @click="toOther(1)">
                    <view class="itemName">
                        【 Androido端末をお使いの方】
                    </view>
                    <view class="item_logo">
                        <!-- <image src="../../static/google.png" mode=""></image> -->
                        <image src="../../static/google-play-badge.svg" mode=""></image>
                    </view>
                </view>
            </view>
            
            <view class="pd" style="margin-top: 80upx;">
                <view class="itemName">
                    【 PCでご利用される方】
                </view>
                <view class="caution" style="margin-top: 20upx;">
                    「アカウント登録完了」メールにログインページへのURLを記載しておりますので、お手数ですが、そちらからログインしてください。
                </view>
            </view>
        </view>
    </view>
</template>
<script>
    export default {
        data() {
            return {
                query: {}
            }
        },
        created() {
            this.query = this.$route.query
        },
        methods: {
            toOther(e){
                if(e == 0){
                    window.location.href = "https://apps.apple.com/jp/app/cardsan%E7%AE%A1%E7%90%86/id1663708240"
                }else{
                    window.location.href = "https://play.google.com/store/apps/details?id=uni.UNI86DBD5D"
                }
            }
        }
    }
</script>

<style lang="scss">
    .pd {
        box-sizing: border-box;
        padding-left: 52upx;
        padding-right: 52upx;
    }
    
    .itemName {
        font-size: 24upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        text-align: center;
    }

    .download {
        background: #FFFFFF;
        box-shadow: 0px 6upx 12upx 2upx rgba(0, 0, 0, 0.16);
        box-sizing: border-box;
        padding: 40upx 48upx;

        .d_item {
            margin-bottom: 62upx;

            .item_logo {
                width: 324upx;
                height: 96upx;
                text-align: center;
                margin: 26upx auto 0;

                image {
                    width: 100%;
                    height: 100%;
                }
            }
        }

        .des {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin: 40upx 0 80upx 0;
        }

        .downname {
            font-size: 28upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            margin-top: 10upx;
        }

        .downloadLogo {
            width: 120upx;
            height: 120upx;
            margin: 0 auto;

            image {
                width: 100%;
                height: 100%;
            }
        }
    }

    .content {
        background: #f9f9f9;
        box-sizing: border-box;
        padding: 40upx 32upx 0 32upx;

        .logo {
            box-sizing: border-box;
            text-align: center;
            padding-bottom: 32upx;

            image {
                height: 86upx;
            }
        }

        .container {
            width: 100%;
            background-color: #fff;
            box-shadow: 0px 40px 80px 2px rgba(0, 0, 0, 0.07);
            box-sizing: border-box;
            padding: 80upx 28upx;

            .title1 {
                text-align: center;
                font-size: 36upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }

            .title2 {
                font-size: 32upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                text-align: center;
                margin: 80upx 0 32upx;
            }

            .title3 {
                font-size: 32upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                text-align: center;
            }

            .caution {
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;
                margin-top: 80upx;
            }

            .tip {
                background: rgba(26, 115, 232, 0.1);
                box-sizing: border-box;
                padding: 40upx;
                margin-top: 40upx;
                display: flex;
                margin-bottom: 80upx;

                .tipRight {
                    font-size: 24upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #174A84;
                    line-height: 36upx;
                    margin-left: 20upx;
                }
            }

            .inp {
                border-radius: 20upx;
                border: 2upx solid #0A4B6D;
                height: 88upx;
                text-align: center;
                line-height: 88upx;
                display: flex;
                align-items: center;
                box-sizing: border-box;
                padding: 0 20upx;
                margin-top: 20upx;
            }

            .progressName {
                font-size: 28upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #AAAAAA;
                text-align: center;
            }

            .progress {
                width: 100%;
                height: 16upx;
                border-radius: 0px 0px 0px 0px;
                opacity: 1;
                border: 2upx solid #AAAAAA;
                margin-top: 28upx;

                .progressInner {
                    width: 33%;
                    height: 100%;
                    background: #AAAAAA;
                }
            }

            .title {
                margin: 60upx 0 80upx;
                text-align: center;
                font-size: 36upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #0A4B6D;
            }

            .ipName {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #0A4B6D;
            }
        }
    }
</style>
