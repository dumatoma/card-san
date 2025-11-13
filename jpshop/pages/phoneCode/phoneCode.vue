<template>
    <view class="container">
        <view class="title1">
            登録した電話番号に送信した6桁の認証コードを入力してください。
        </view>
        <view class="title2">
            (080******77)
        </view>
        <view class="title3">
            SMSで送信された認証コードは10分間有効です
        </view>
        <view class="inserCode">
          <!--  <u-code-input style="margin: 0 auto;" v-model="value2" mode="line" maxlength="6" :space="10"
                @change="change" @finish="finish" fontSize="24" size="30"></u-code-input> -->
                 <xt-verify-code type="bottom" v-model="value2" @confirm="finish"></xt-verify-code>
        </view>
        <u-button class="send">
            サインイン
        </u-button>
        <view class="tips">
            数分経ってもコードが届かない場合は、認証コードを再送するか、入力したメールアドレスが間違っている可能性がございますので、再度ID(メールアドレス)の入力から行なってください。
        </view>
        <view class="resend">
            認証コードを再送
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {

            }
        },
        methods: {

        }
    }
</script>

<style lang="scss">
    .container {
        border-top: 2upx solid #D2D2D7;
        box-sizing: border-box;
        padding-top: 100upx;

        .title1 {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            box-sizing: border-box;
            padding: 0 42upx;
            text-align: center;
        }

        .title2 {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            margin: 20upx 0;
        }

        .title3 {
            font-size: 28upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
        }

        .inserCode {
            text-align: center;
            width: 450upx;
            margin: 170upx auto;
        }

        .send {
            width: 100%;
            height: 112upx;
            background: #53B1AF;
            opacity: 1;
            border-radius: 20upx;
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            text-align: center;
        }

        .resend {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #53B1AF;
        }
        
        .tips{
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin: 160upx 0 110upx;
        }
    }
</style>
