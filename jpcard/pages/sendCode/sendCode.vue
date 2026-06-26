<template>
    <view class="">
        <u-navbar title="電話番号認証" :placeholder="true" :safeAreaInsetTop="true" :autoBack="true" style="border-bottom: 2upx solid #FBFBFD;"></u-navbar>
        <view class="container">
            <view class="phoneTitle">
                電話番号(携帯電話)
            </view>
            <view class="insertPhone">
                <u--input placeholder="電話番号を入力してください" border="none" v-model="value" style="height: 100%;"></u--input>
            </view>
            <view class="tips">
                この電話番号にSMSで認証番号が送信されます
            </view>
            <u-button class="submit" :loading="btnloading" loadingText="" :loadingSize="25" @click="toNext">SMSに認証コードを送る</u-button>
        </view>
    </view>
</template>

<script>
    import {
        sendPhoneCode,
        sendLoginCode
    } from '@/api/index.js'
    export default {
        data() {
            return {
                value: "",
                query: {},
                btnloading:false
            }
        },
        created() {
           
        },
        onLoad(options) {
            let that = this
            let query = options
            that.query = query
        },
        methods: {
            toNext() {
                let that = this
                let source = that.query.source
                that.btnloading = true
                if(source == "register"){
                    let data = {}
                    data.phone = that.value
                    data.request_type = 1
                    sendPhoneCode(data).then((res) => {
                        if (res.code == 200) {
                            that.btnloading = false
                            uni.navigateTo({
                                url: "../code/code?source=" + that.query.source +"&phone=" + that.value+"&code="+res.data.code
                            })
                        }else{
                            that.btnloading = false
                            uni.showToast({
                                title:res.message,
                                icon:"none"
                            })
                        }
                    }).catch(() => { that.btnloading = false })
                }else{
                    let data = {}
                    data.phone = that.value
                    data.request_type = 1
                    sendLoginCode(data).then((res) => {
                        if (res.code == 200) {
                            that.btnloading = false
                            uni.navigateTo({
                                url: "../code/code?source=" + that.query.source + "&phone=" + that.value+"&code="+res.data.code
                            })
                        }else{
                            that.btnloading = false
                            uni.showToast({
                                title:res.message,
                                icon:"none",
                                duration:2000
                            })
                            setTimeout(() => {
                                uni.navigateBack()
                            },1500)
                        }
                    }).catch(() => { that.btnloading = false })
                } 
            }
        },
       
    }
</script>

<style lang="scss">
    .container {
        border-top: 2upx solid #D2D2D7;
        box-sizing: border-box;
        padding-top: 140upx;

        .phoneTitle {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .insertPhone {
            height: 88upx;
            background: #FFFFFF;
            border-radius: 20upx;
            opacity: 1;
            border: 2upx solid #D2D2D7;
            filter: blur(undefinedpx);
            box-sizing: border-box;
            padding: 0 20upx;
            margin-top: 10upx;
        }

        .tips {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #AAAAAA;
            margin-top: 40upx;
            margin-bottom: 80upx;
        }

        .submit {
            width: 482upx;
            height: 84upx;
            background: #06C755;
            border-radius: 40upx;
            opacity: 1;
            font-size: 28upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
        }
    }
</style>
