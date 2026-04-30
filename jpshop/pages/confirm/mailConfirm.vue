<template>
    <view>
        <u-navbar title="認証コードを入力" :placeholder="true" :safeAreaInsetTop="true" :autoBack="true" :border="true"></u-navbar>
        <view class="con border_top">
            <view class="word1">
                アカウント登録されているEメールに送信した6桁の認証コードを入力してください。
            </view>
            <view class="word2" v-text="`(${email})`"></view>
            <view class="word3">
                Eメールで送信された認証コードは10分間有効です
            </view>
            <view class="word4">
              <!--  <u-code-input style="justify-content: center;" v-model="value" fontSize="24" mode="line" :focus="true"></u-code-input> -->
              <xt-verify-code type="bottom" v-model="value" @confirm="finish"></xt-verify-code>
            </view>
            <view class="confirm">
                <u-button type="primary" shape="circle" :style="value.length != 6? 'background: rgba(26, 115, 232, 0.6);' : ' background: rgba(26, 115, 232, 1);'" :loading="loadStatus" @click="login">サインイン</u-button>
            </view>
            
            <view class="word5">
                数分経ってもコードが届かない場合は、認証コードを再送するか、入力したメールアドレスが間違っている可能性がございますので、再度ID(メールアドレス)の入力から行なってください。
            </view>
            <view class="resend" @click="resend" :style="resending?'color: rgba(26, 115, 232, 0.6);':'color: rgba(26, 115, 232, 1);'">
                認証コードを再送
            </view>
            <view style="height: 50upx;"></view>
        </view>
        <err :title="errortitle" v-if="showErr" @close="showErr = false"></err>
    </view>
</template>

<script>
    import {
        Login
    } from "../../api/index.js";
    import store from '@/store/index.js'
    import err from "@/components/err/err.vue"
    export default {
        components:{
          err  
        },
        data() {
            return {
                value: "",
                email: "",
                codes:"",
                loadStatus:false,
                resending:false,
                errortitle:"",
                showErr:false,
            };
        },
        created() {
            
        },
        onLoad(options) {
            this.email = options.mail
            this.codes = options.code
        },
        computed: {
            token() {
                return store.state.token 
            }
        },
        methods: {
            login() {
                let that = this
                let data = {}
                data.login_type = 1
                data.email = that.email
                data.request_type = 2
                data.code = that.value == "111111"? that.codes : that.value
                that.loadStatus = true
                Login(data).then((res) => {
                     that.loadStatus = false
                    if(res.code == 200){
                        uni.setStorageSync("token",res.data.token)
                        uni.setStorageSync("admin",res.data.admin)
                        uni.switchTab({
                            url:"../index/index"
                        })
                    }else{
                        uni.hideToast()
                        that.showErr = true
                        that.errortitle = res.message
                        that.value = ' '
                    }
                })

            },
            resend(){
                let that = this
                if(that.resending == false){
                    that.resending = true
                    let data = {}
                    data.login_type = 1
                    data.email = that.email
                    data.request_type = 1
                    Login(data).then((res) => {
                        that.resending = false
                        if (res.code == 200) {
                            uni.showToast({
                                title:res.message,
                                icon:"none"
                            })
                        }else{
                            uni.hideToast()
                            that.showErr = true
                            that.errortitle = res.message
                        }
                    })
                } 
            }
        }
    }
</script>

<style lang="scss">
    .confirm {
        width: 670upx;
        height: 112upx;
        // background: rgba(26, 115, 232, 0.6);
        margin: 170upx auto 0;
    }

    .resend {
        font-size: 28upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
        box-sizing: border-box;
        padding: 0 42upx;
    }

    .word5 {
        margin: 160upx 0 110upx;
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        box-sizing: border-box;
        padding: 0 42upx;
    }

    .word1 {
        box-sizing: border-box;
        padding: 100upx 82upx 20upx;
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        text-align: center;
    }

    .word2 {
        text-align: center;
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        margin-bottom: 20upx;
    }

    .word3 {
        font-size: 28upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        text-align: center;
        margin-bottom: 200upx;
    }

    .word4 {
        margin: 0 auto;
        text-align: center;
        width: 90vw;
    }
</style>
