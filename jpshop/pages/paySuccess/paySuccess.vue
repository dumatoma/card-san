<template>
    <view class="content" style="position: relative;top: 50upx;">
        <view style="height: 80upx;"></view>
        <view class="logo">
            <image src="../../static/images/logo.png" mode="heightFix"></image>
        </view>
        <view class="container">
            <view class="title1">
                お手続きありがとうございますお申し込みが完了しました！
            </view>
            <view class="caution">
                ご登録のEメールアドレスに「お申し込み完了」のEメールを送信しましたのでご確認ください。　万が一、メールが届いていない場合は、迷惑メールフォルダをご確認お願いします。
            </view>
            <view class="details">
                <view class="detail_item">
                    <view class="item_left">
                        ID:
                    </view>
                    <view class="item_right">
                        {{email}}
                    </view>
                </view>
                <view class="detail_item">
                    <view class="item_left">
                        ご利用プラン:
                    </view>
                    <view class="item_right">
                        {{type==2?'スタンダード':type == 1? 'ライト' : 'プレミアム'}}
                    </view>
                </view>
               <!-- <view class="detail_item">
                    <view class="item_left">
                        オプション:
                    </view>
                    <view class="item_right">
                        クーポン機能
                    </view>
                </view> -->
                <view class="detail_item">
                    <view class="item_left">
                        支払い周期:
                    </view>
                    <view class="item_right">
                        {{month}}ヶ月
                    </view>
                </view>
                <view class="detail_item">
                    <view class="item_left">
                        支払い方法:
                    </view>
                    <view class="item_right">
                        クレジットカード
                    </view>
                </view>
            </view>
            <view class="details" style="border: none;margin-bottom: 172upx;">
                <view class="detail_item">
                    <view class="item_left">
                        ご利用プラン、オプション、支払い周期、支払い方法は後で変更できます。
                    </view>
                </view>
            </view>

            <u-button class="custom-style" @click="toIndex" :customStyle="customStyle" text="TOP画面に戻る" shape="circle" color="rgba(0,0,0,0.1)" iconColor="#707070">
            </u-button>
        </view>
    </view>
</template>
<script>
    import {getShopInfo,getPackageList,delPlan} from "@/api/index.js"
    export default {
        data() {
            return {
                customStyle: {
                    color: '#707070'
                },
                successInfo:{},
                email:"",
                type:"",
                month:""
            }
        },
        created() {
            let that = this
            getShopInfo().then((res) => {
                if(res.code == 200){
                    that.successInfo = res.data.shop_info.vip
                    that.email = res.data.shop_info.shop_admin_email
                }
            })
        },
        onLoad(options) {
            let that = this 
            that.type = options.type
            that.month = options.month
        },
        methods: {
            toIndex(){
                uni.switchTab({
                    url:"../index/index"
                })
            }
        }
    }
</script>

<style lang="scss">

    .details {
        border-top: 2upx solid #D2D2D7;
        border-bottom: 2upx solid #D2D2D7;
        margin-top: 78upx;

        .detail_item {
            padding: 20upx 0;
            display: flex;
            align-items: center;

            .item_left {
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;
            }

            .item_right {
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }
        }
    }

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
                height: 100upx;
            }
        }

        .container {
            width: 100%;
            background-color: #fff;
            box-shadow: 0px 40px 80px 2px rgba(0, 0, 0, 0.07);
            box-sizing: border-box;
            padding: 80upx;

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
                margin-top: 80upx;
                display: flex;
                margin-bottom: 120upx;

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
