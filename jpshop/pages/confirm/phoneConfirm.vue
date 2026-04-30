<template>
    <view>
        <view class="con border_top">
            <view class="word1">
                登録した電話番号に送信した6桁の認証コードを入力してください。
            </view>
            <view class="word2" v-text="phone"></view>
            <view class="word3">
                SMSで送信された認証コードは10分間有効です
            </view>
            <view class="word4">
              <!--  <u-code-input v-model="value" style="justify-content: center;" fontSize="24" mode="line" @change='handelChange' :focus="true"></u-code-input> -->
              <xt-verify-code type="bottom" v-model="value" @confirm="handelChange"></xt-verify-code>
            </view>
           <u-button :style="value.length == 6?'background:rgba(6, 199, 85,1)':'background:rgba(6, 199, 85, .6)'" class="btn1" :loading="showsl" @click="login">サインイン</u-button>
           <!-- <u-button @click="toNext" :style="value.length == 0? 'background:rgba(6, 199, 85, .6) ':'background:rgba(6, 199, 85, 1)'" class="btn1" :loading="logStatus">SMSに認証コードを送る</u-button> -->
            <view class="word5">
                数分経ってもコードが届かない場合は、認証コードを再送するか、入力した電話番号が間違っている可能性がございますので、再度ID({{phone}})の入力から行なってください。
            </view>
            <view class="resend" @click="resend" :style="isSending==true?'color: rgba(83, 177, 175, .6)' : 'color: rgba(83, 177, 175, 1)'">
                認証コードを再送
            </view>
        </view>
        <err :title="errortitle" v-if="showErr" @close="showErr = false"></err>
    </view>
</template>

<script>
    import {Login} from "@/api/index.js"
    import err from "@/components/err/err.vue"
    export default {
        components:{err},
        data() {
            return {
                value: "",
                errortitle:"",
                showErr:false,
                dis:true,
                phone:"",
                isSending:false,
                codes:"",
                showsl:false
            };
        },
        created() {
            
        },
        onLoad(options) {
            this.phone = options.phone
            this.codes = options.code 
            
        },
        methods:{
            handelChange(e){
                let that = this
                if(e.length == 6){
                    that.dis = false
                }else{
                    that.dis = true
                }
            },
            login() {
                let that = this
                that.showsl = true
                let data = {}
                data.login_type = 2
                data.phone = that.phone
                data.request_type = 2
                data.code = that.value == "111111"? that.codes : that.value
                Login(data).then((res) => {
                    that.showsl = false
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
                if(that.isSending == false){
                    that.isSending = true
                    let data = {}
                    data.login_type = 2
                    data.phone = that.phone
                    data.request_type = 1
                    Login(data).then((res) => {
                        that.isSending = true
                        if (res.code == 200) {
                            uni.showToast({
                                title:res.message
                            })
                        }else{
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
    .btn1 {
        width: 670upx !important;
        // width: 482upx;
        height: 100upx !important;
        // background: #06C755;
        border-radius: 20upx !important;
        opacity: 1;
        font-size: 28upx !important;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #FFFFFF !important;
        margin-top: 80upx;
    }
    
    // .btn1 {
    //     width: 482upx;
    //     height: 84upx;
    //     background: #06C755;
    //     border-radius: 40upx;
    //     opacity: 1;
    //     font-size: 28upx;
    //     font-family: Hiragino Sans-W5, Hiragino Sans;
    //     font-weight: normal;
    //     color: #FFFFFF;
    //     margin-top: 80upx;
    // }

    .resend {
        font-size: 28upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #53B1AF;
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
