<template>
    <view class="content">
        <view class="logo">
            <image src="../../static/icon_rogo.svg" mode="heightFix"></image>
        </view>
        <view class="container">
            <view class="progressName">
                ステップ 1/3
            </view>
            <view class="progress">
                <view class="progressInner"></view>
            </view>
            <view class="title">
                無料のアカウント登録
            </view>
            <view class="ipName">
                ID(メールアドレス)
            </view>
            <view class="inp" :class="erro != 0?'errorInp' : ''">
                <u-input border="none" placeholder="メールアドレスを入力してください" v-model="value"></u-input>
            </view>
            <view class="errortip" v-if="erro == 400001">メールアドレスを正しく入力してください</view>
            <view class="errortip" v-if="erro == 400002">このメールアドレスは既に登録されています</view>
            <view class="tip">
                <view class="tipLeft">
                    <image src="../../static/attention_icon.svg" style="width: 28upx;height: 28upx;" mode=""></image>
                </view>
                <view class="tipRight">
                    スマートフォン、タブレットで確認できるメールアドレス、もしくは勤務用のメールアドレスをご入力いただくと、よりスムーズに、無料トライアル、アプリとの連携やチームメンバーの追加が行えます。
                </view>
            </view>
            <u-button :text="logword" @click="toNext" :loading="isloading" color="#0A4B6D" shape="circle" :disabled="value == ''? true : false "
                customStyle="height:100upx;font-family: Hiragino Sans-W3, Hiragino Sans;"></u-button>
            <view class="caution">
                「次へ」を選択すると、ご登録のメールアドレスに認証コードが送信されます。
            </view>
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                value: "",
                dis: true,
                erro:0,
                logword:"次へ",
                isloading:false
            }
        },
        onLoad() {

        },
        methods: {
            toNext() {
                let that = this
                that.isloading = true
                uni.request({
                    url: that.$baseUrl + "/api/shop/register",
                    method: "POST",
                    data: {
                        request_type: 1,
                        email: that.value
                    },
                    success(res) {
                        console.log(res)
                        if (res.data.code == 200) {
                            let code = res.data.data.code
                            uni.showToast({
                                title: res.data.message,
                                icon: "none",
                                duration: 2000
                            })
                            setTimeout(() => {
                                that.isloading = false
                                uni.navigateTo({
                                    url: "/pages/secondStep/secondStep?id=" + that.value+"&code="+res.data.data.code
                                })
                                 that.logword = "次へ"
                            }, 2000)
                           
                        }else{
                            that.erro = res.data.code 
                            that.logword = "次へ"
                            that.isloading = false
                        }
                    }
                })
            }
        }
    }
</script>

<style lang="scss">
    page{
        font-family: Hiragino Sans-W3, Hiragino Sans, Hiragino Sans-W5 !important;
    }
    .u-button__text{
        font-family: Hiragino Sans-W3, Hiragino Sans !important;
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
                height: 84upx;
            }
        }
        
        .errortip{
            color: #D93025;
            font-size: 24upx;
            margin-top: 14upx;
        }

        .container {
            width: 100%;
            background-color: #fff;
            box-shadow: 0px 40px 80px 2px rgba(0, 0, 0, 0.07);
            box-sizing: border-box;
            padding: 80upx;

            .caution {
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;
                margin-top: 40upx;
            }

            .tip {
                background: rgba(29, 29, 31, 0.1);
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
                margin-top: 10upx;
            }
            
            .errorInp{
                border: 2upx solid #D93025;
                box-shadow: 0 0 10upx 3upx #D93025;
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
                font-size: 40upx;
                font-family: Hiragino Sans-W6, Hiragino Sans;
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
