<template>
    <view class="">
       <!-- <u-navbar title="電話番号入力" :placeholder="true" :safeAreaInsetTop="true" :autoBack="true" style="border-bottom: 2upx solid #FBFBFD;"></u-navbar> -->
        <view class="container">
            
            <view class="phoneTitle">
                電話番号(携帯電話)
            </view>
            <view class="insertPhone">
                <u--input placeholder="副管理者として登録された番号を入力" border="none" v-model="value" @change="change"
                    style="height: 100%;"></u--input>
            </view>
            <view class="tips">
                この電話番号にSMSで認証コードが送信されます
            </view>
            <u-button @click="toNext" :style="value.length == 0? 'background:rgba(6, 199, 85, .6) ':'background:rgba(6, 199, 85, 1)'" class="btn1" :loading="logStatus">SMSに認証コードを送る</u-button>
        </view>
        <err :title="errortitle" v-if="showErr" @close="showErr = false"></err>
    </view>
   
</template>

<script>
    import {Login} from '@/api/index.js'
    import err from "@/components/err/err.vue"
    export default {
        components:{
          err  
        },
        data() {
            return {
                value:"",
                logStatus:false,
                errortitle:"",
                showErr:false,
            }
        },
        methods: {
            change(e){
            },
            toNext(){
                let that = this
                if (that.value == '') {
                    return;
                }
                that.logStatus = true
                let data = {}
                data.login_type = 2
                data.phone = that.value
                data.request_type = 1
                Login(data).then((res) => {
                    that.logStatus = false
                    if (res.code == 200) {
                        uni.navigateTo({
                            url:"phoneConfirm?phone="+that.value+"&code="+res.data.code
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
</script>

<style lang="scss">
    .container {
        border-top: 2upx solid #D2D2D7;
        box-sizing: border-box;
        padding-top: 140upx;
        
        .insertPhone{
            height: 88upx;
            background: #FFFFFF;
            border-radius: 20upx;
            opacity: 1;
            border: 2upx solid #D2D2D7;
            filter: blur(undefinedpx);
            box-sizing: border-box;
            padding: 0 20upx;
            margin: 10upx 0 40upx;
        }
        
        .phoneTitle{
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }
        
        .btn1 {
            width: 482upx;
            height: 84upx;
            background: #06C755;
            border-radius: 40upx;
            opacity: 1;
            font-size: 28upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            margin-top: 80upx;
        }
        
        .tips{
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
        }
    }

    
</style>
