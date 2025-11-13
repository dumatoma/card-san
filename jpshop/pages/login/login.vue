<template>
    <view>
        <!-- 顶部黑色 -->
        <view class="topContent">
            <view class="logo">
                <image src="../../static/images/loginLogo.svg"></image>
            </view>
            <view class="dl">
                ログイン
            </view>
        </view>
        <view class="container">
            <view class="logTitle">
                アカウントID(メールアドレス)を入力してログインしてください。
            </view>
            <view class="phoneTitle">
                ID(メールアドレス)
            </view>
            <view class="insertPhone">
                <u--input placeholder="メールアドレスを入力してください" border="none" v-model="value" style="height: 100%;"></u--input>
            </view>
            <u-button class='login1' :style="value.length == 0? 'background: rgba(26, 115, 232, 0.6);' : ' background: rgba(26, 115, 232, 1);'" :loading="logStatus" @click="toConfirm">ログイン</u-button>
            <u-button :plain="true" class='login2' @click="toFu" >副管理者のログインはこちら</u-button>
            <view style="height: 200upx;"></view>
        </view>
        <err :title="errortitle" v-if="showErr" @close="showErr = false"></err>
    </view>
</template>

<script>
    import {
        Login
    } from "../../api/index.js";
    import err from "@/components/err/err.vue"
    import {
        mapState,
        mapGetters,
        mapActions,
        mapMutations
    } from 'vuex'
    import store from '@/store/index.js';

    export default {
        components:{err},
        data() {
            return {
                value: "",
                logStatus:false,
                errortitle:"",
                showErr:false,
            }
        },
        created() {
            let data = {}
        },
        methods: {
            toConfirm() {
                let that = this
                if (that.value == '') {
                    uni.showToast({
                        title: "アカウント名を入力してください",
                        icon: "none"
                    })
                    return;
                }
                let data = {}
                data.login_type = 1
                data.email = that.value
                data.request_type = 1
                that.logStatus = true
                Login(data).then((res) => {
                    that.logStatus = false
                    if (res.code == 200) {
                        uni.navigateTo({
                            url: "../confirm/mailConfirm?mail=" + that.value+"&code="+res.data.code
                        })
                    }else{
                        uni.hideToast()
                        that.showErr = true
                        that.errortitle = res.message
                    }
                })
            },

            toFu() {
                let that = this 
                // uni.showToast({
                //     title: '提交成功',
                //     duration: 2000
                // });
                uni.navigateTo({
                    url: "../confirm/confirm?"
                })
            }
        }
    }
</script>

<style lang="scss">
    .tips {
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        margin: 20upx 0 52upx;
    }

    .tip {
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
    }

    .topContent {
        height: 396upx;
        width: 100%;
        background: #1D1D1F;
        box-sizing: border-box;
        padding-top: 108upx;
    }

    .logo {
        width: 145upx;
        height: 150upx;
        margin: 0 auto;


        image {
            width: 100%;
            height: 100%;
        }
    }

    .dl {
        font-size: 48upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #FFFFFF;
        text-align: center;
        margin-top: 32upx;
    }

    .logTitle {
        font-size: 40upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: rgba(29, 29, 31, 0.9);
        box-sizing: border-box;
        padding: 0 17upx;
    }

    .container {
        box-sizing: border-box;
        padding-top: 140upx;

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

        .phoneTitle {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-top: 124upx;
        }

        .login1 {
            width: 100%;
            height: 100upx;
            background: rgba(26, 115, 232, 0.6);
            border-radius: 50upx;
            opacity: 1;
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            margin: 140upx 0 100upx;
        }

        .login2 {
            width: 100%;
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #00A5A5 !important;
            height: 100upx;
            background: rgba(83, 177, 175, 0);
            border-radius: 50upx;
            opacity: 1;
            border: 2upx solid #53B1AF;
        }
    }
</style>
