<template>
    <view class="content">
        <view class="logo">
            <!-- <image src="../../static/logo.png" mode="heightFix"></image> -->
            <image src="../../static/icon_rogo.svg" mode="heightFix"></image>
        </view>
        <view class="container">
            <view class="progressName">
                ステップ 3/3
            </view>
            <view class="progress">
                <view class="progressInner"></view>
            </view>
            <view class="title">
                最後のステップ
            </view>
            <view class="ipName">
                氏名
            </view>
            <view class="inp">
                <u-input border="none" placeholder="あなたの氏名を入力してください" v-model="value1"></u-input>
            </view>
            <view class="ipName" style="margin-top: 60upx;">
                店舗名
            </view>
            <view class="inp" style="margin-bottom: 80upx;">
                <u-input border="none" placeholder="あなたの店舗名を入力してください" v-model="value2"></u-input>
            </view>
            <u-button :text="loadingWord" @click="toNext" :loading="isLodaing" color="#0A4B6D" shape="circle" :disabled="value1 != '' && value2 != ''? false : true "
                customStyle="height:100upx;font-family: Hiragino Sans-W3, Hiragino Sans;"></u-button>
                <view class="goback" @click="goback">
                    < 戻る
                </view>
            <view class="caution">
                [無料アカウント登録]をクリックすることで、
                Card-Sanの <text style="text-decoration: underline;" @click="userInfos">利用規約と</text><text style="text-decoration: underline;"  @click="userInfos">プライバシーポリシー</text>に<br/>同意したものとみなされます。
            </view>
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                value1: "",
                value2: "",
                dis: true,
                loadingWord:"無料アカウント登録",
                isLodaing:false
            }
        },
        created() {
          let that = this
             that.query = that.$route.query
        },
        methods: {
            userInfos(){
              uni.navigateTo({
                  url:"/pages/rules/rules"
              })  
            },
            goback(){
                uni.navigateBack()
            },
            toNext() {
                let that = this
                that.loadingWord = ""
                that.isLodaing = true
                uni.request({
                    url: that.$baseUrl + "/api/shop/register",
                    method: "POST",
                    data: {
                        request_type: 2,
                        email: that.query.email,
                        code:that.query.code,
                        name:that.value1,
                        shop_name:that.value2
                    },
                    success(res) {
                        console.log(res)
                        that.loadingWord = "無料アカウント登録"
                        that.isLodaing = false
                        if(res.data.code == 200){
                            uni.showToast({
                                title:res.data.message,
                                duration:2500
                            })
                            setTimeout(() => {
                                uni.navigateTo({
                                    url:"../downLoad/downLoad?email="+that.query.email
                                })
                            },2500)
                        }else{
                            uni.showToast({
                                title:res.data.message,
                                icon:'none',
                                duration:2500
                            })
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
    .goback{
        text-align: center;
        margin-top: 30px;
        font-size: 16px;
        color: #1d1d1f;
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
                text-align: center;
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
                background: #AAAAAA;
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
