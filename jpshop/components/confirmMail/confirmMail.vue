<template>
    <view class="co">
        <view class="con border_top">
            <view class="word1">
                アカウント登録されているEメールに送信した6桁の認証コードを入力してください。
            </view>
            <view class="word2" v-text="`(${email})`"></view>
            <view class="word3">
                Eメールで送信された認証コードは10分間有効です
            </view>
            <view class="word4">
                <u-code-input v-model="value" fontSize="24" mode="line" :focus="true"></u-code-input>
            </view>
            <view class="confirm">
                <u-button type="primary" :disabled="value.length != 6" shape="circle" :loading="logstatus" class="bt" @click="login">メールアドレス(ID)変更を認証</u-button>
            </view>
        </view>
    </view>
</template>

<script>
    import store from '@/store/index.js'
    import {changeMail} from "@/api/index.js"
    export default {
       props:{
          email: String
       },
        data() {
            return {
                value: "",
                logstatus:false
            };
        },
        created() {
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
                data.email = that.email
                data.request_type = 2
                data.code = that.value
                that.logstatus = true
               changeMail(data).then((res) => {
                   that.logstatus = true
                   if(res.code == 200){
                       let info = uni.getStorageSync("admin")
                       info.email = that.email
                       uni.setStorageSync("admin",info)
                       uni.showToast({
                           title:res.message,
                           duration:2000
                       })
                       that.$emit("success","123")
                   }else{
                       uni.showToast({
                           title:res.message,
                           duration:2000,
                           icon:"none"
                       })
                   }
               })

            }
        }
    }
</script>

<style lang="scss">
    .bt{
        width: 612upx;
        height: 100upx;
        // background: rgba(26,115,232,1);
        border-radius: 50upx;
        margin-top: 318upx;
    }
    .co{
        width: 100vw;
        height: 100vh;
        background-color: #fff;
    }
    
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
        width: 71vw;
    }
</style>
