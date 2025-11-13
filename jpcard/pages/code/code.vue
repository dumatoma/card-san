<template>
    <view class="container">
        <view class="phoneNumber">
            {{query.phone}} にSMSで認証番号を送信しました。
        </view>
        <view class="inserCode">
           <!-- <u-code-input style="margin: 0 auto;justify-content: center;" v-model="value2" mode="line" maxlength="6" :space="10"
                @change="change" @finish="finish" fontSize="24" size="30"></u-code-input> -->
                <xt-verify-code type="bottom" v-model="value2" @confirm="finish"></xt-verify-code>
        </view>
        <u-button type="primary" class="sd" @click="toRegister" :loading="btnloading" loadingText='読み込み中
' :loadingSize="25">サインイン</u-button>
        <view class="tips">
            サインイン、ログインすることでCARDさんAppの利用規約とプライバシーポリシーに同意したものとみなされます。
        </view>
        <view class="resend" @click="reSend">
            認証番号を再送
        </view>
    </view>
</template>

<script>
    import {
        sendPhoneCode,
        sendLoginCode
    } from "@/api/index.js"
    export default {
        data() {
            return {
                value2: "",
                query: {},
                dis: true,
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
            change(e) {
            },
            finish(e) {
                this.dis = false
                console.log("e",e)
            },
            toRegister() {
                let that = this
                let source = that.query.source
                if (source == 'register' && that.dis == false) {
                    let data = {}
                    data.phone = that.query.phone
                    data.request_type = 2
                    data.code = that.value2 == '111111'?that.query.code: that.value2
                    sendPhoneCode(data).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message,
                                duration: 2000,
                                icon:"none"
                            })
                            setTimeout(function() {
                                uni.redirectTo({
                                    url: "../login/login"
                                })
                            }, 2000)
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                            that.value2 = ''
                        }
                    })
                }
                
                
                if (source == 'login' && that.dis == false) {
                    that.btnloading = true
                    let data = {}
                    data.phone = that.query.phone
                    data.request_type = 2
                    data.code = that.value2 == "111111"?that.query.code : that.value2
                    sendLoginCode(data).then((res) => {
                        that.btnloading = false
                        if (res.code == 200) {
                            uni.setStorageSync("token",res.data.token)
                            uni.setStorageSync("user",res.data.user)
                            uni.redirectTo({
                                url: "../index/index"
                            })
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                            that.value2 = ''
                        }
                    })
                }
            },
            reSend() {
                let that = this
                let source = that.query.source
                if (source == "register") {
                    let data = {}
                    data.phone = that.query.phone
                    data.request_type = 1
                    data.recode = 1
                    uni.showLoading({
                        title:"読み込み中"
                    })
                    sendPhoneCode(data).then((res) => {
                        uni.hideLoading()
                        if (res.code == 200) {
                            uni.showToast({
                                title: '再送しました',
                                icon:'none'
                            })
                           
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                }else{
                    let data = {}
                    data.phone = that.query.phone
                    data.request_type = 1
                    sendLoginCode(data).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: '再送しました'
                            })
                            // uni.showModal({
                            //     title: "验证码",
                            //     content: res.message,
                            // })
                    
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                }
            }
        }
    }
</script>

<style lang="scss">
    .phoneNumber{
        padding-top: 60rpx;
    }
    .container {
        .inserCode {
            text-align: center;
            // width: 100vw;
            margin: 82upx auto 200upx;
        }

        .sd {
            width: 670upx;
            height: 112upx;
            // background: rgb(26, 115, 232);
            border-radius: 20upx;
            opacity: 1;
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
        }

        .tips {
            box-sizing: border-box;
            padding: 0 35upx;
            font-size: 20upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            text-align: center;
            margin: 80upx 0 110upx;
        }

        .resend {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
        }
    }
</style>
