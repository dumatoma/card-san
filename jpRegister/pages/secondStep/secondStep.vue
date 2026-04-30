<template>
	<view class="content">
		<view class="logo">
            <!-- <image src="../../static/logo.png" mode="heightFix"></image> -->
            <image src="../../static/icon_rogo.svg" mode=""></image>
        </view>
        <view class="container">
            <view class="progressName">
                ステップ 2/3
            </view>
            <view class="progress">
                <view class="progressInner"></view>
            </view>
            <view class="title">
                認証コードを入力
            </view> 
            <view class="ipName">
                先ほど登録したメールアドレスに送信された6桁の認証コードを入力してください。
            </view>
            <view class="ipName" v-text="`(${email})`"></view>
            <view class="ipName t"> 
                Eメールで送信された認証コードは10分間有効です
            </view>
            <view class="inp" @click.stop="changeshow">
                 <u-code-input :disabledKeyboard="true" v-model="value2" mode="line" :maxlength="6"></u-code-input>
            </view>
            <u-button :text="logword" :loading="isloading" @click="toNext" color="#0A4B6D" shape="circle" :disabled="value2.length == 6? false : true " customStyle="height:100upx;font-family: Hiragino Sans-W3, Hiragino Sans;"></u-button>
            <view class="goback" @click="goback">
                < 戻る
            </view>
            <view class="caution">
                数分経ってもコードが届かない場合は、認証コードを再送してください。それでも届かない場合は、迷惑メールフォルダをご確認、もしくはご登録のメールアドレスが間違っておりますので、メールアドレスの登録から行なってください。
            </view>
            <view class="resend" :class="isSending?'active':''" @click="send">認証コードを再送</view>
        </view> 
        <u-keyboard @backspace="backspace" :overlay="true" @close="show = false"  mode="number" @change="numberChange" :tooltip="false" :safeAreaInsetBottom="true" :show="show"></u-keyboard>
	</view>
</template>

<script>
	export default {
		data() {       
			return {
                value:"",
                dis:true,
                email:"",
                value2:"",
                logword:"最後のステップへ",
                isloading:false,
                isSending:false,
                code:"",
                show:true
			}
		},
        created() {
            let that = this
            let query = that.$route.query
            that.email = query.id
            that.code = query.code
        },
		methods: {
            changeshow(){
               this.show = true
            },
            goback(){
                uni.navigateBack()
            },
            numberChange(e){
               let that = this
               if(that.value2.length < 6){
                   that.value2 += e
               }else{
                   that.show = false
               }
            },
            send(){
                let that = this
                if(that.isSending == false){
                    that.isSending = true
                    uni.request({
                        url: that.$baseUrl + "/api/shop/register",
                        method: "POST",
                        data: {
                            request_type: 1,
                            email: that.email
                        },
                        success(res) {
                            console.log(res)
                            that.isSending = false
                            if (res.data.code == 200) {
                                that.code = res.data.data.code
                                uni.showToast({
                                    title: that.code,
                                    icon: "none",
                                    duration: 2000
                                })
                            }else{
                                uni.showToast({
                                    title: res.data.message,
                                    icon: "none",
                                    duration: 2000
                                })
                            }
                        }
                    })
                }
               
            },
            backspace(){
                let that = this
                that.value2 = that.value2.slice(0,-1)
            },
            toNext(){
                let that = this
                console.log(that.value2)
                console.log(that.code)
                if(that.value2 == that.code){
                   uni.navigateTo({
                       url:"/pages/thirdStep/thirdStep?email="+this.email+"&code="+this.value2
                   }) 
                }else{
                    uni.showToast({
                        title:"認証コードが間違っています",
                        icon:"none"
                    })
                }
               
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
	.content{
        background: #f9f9f9;
        box-sizing: border-box;
        padding: 40upx 32upx 0 32upx;
        .t{
            box-sizing: border-box;
            padding: 0 62upx;
            font-size: 28upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }
        
        .logo{
            box-sizing: border-box;
            text-align: center;
            padding-bottom: 32upx;
            image{
                height: 84upx;
            }
        }
        
        .container{
            width: 100%;
            background-color: #fff;
            box-shadow: 0px 40px 80px 2px rgba(0,0,0,0.07);
            box-sizing: border-box;
            padding: 80upx 58upx;
            
            .caution{
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;
                margin-top: 40upx;
            }
            
            .tip{
                background: rgba(29,29,31,0.1);
                box-sizing: border-box;
                padding: 40upx;
                margin-top: 80upx;
                display: flex;
                margin-bottom: 120upx;
                
                .tipRight{
                    font-size: 24upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #174A84;
                    line-height: 36upx;
                    margin-left: 20upx;
                }
            }
            
            .inp{
                border-radius: 20upx;
                height: 88upx;
                margin-bottom: 170upx;
                text-align: center;
                line-height: 88upx;
                display: flex;
                align-items: center;
                box-sizing: border-box;
                padding: 0 20upx;
                margin-top: 40upx;
                position: relative;
                left: 55%;
                transform: translateX(-50%);
                z-index: 1999 !important;
            }
            
            .progressName{
                font-size: 28upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #AAAAAA;
                text-align: center; 
            }
            
            .progress{
                width: 100%;
                height: 16upx;
                border-radius: 0px 0px 0px 0px;
                opacity: 1;
                border: 2upx solid #AAAAAA;
                margin-top: 28upx;
                
                .progressInner{
                    width: 67%;
                    height: 100%;
                    background: #AAAAAA;
                }
            }
            
            .resend{
                font-size: 28upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1A73E8;
                margin-top: 70upx;
            }
            
            .active{
                opacity: 0.6 !important;
            }
            
            .title{
                margin: 60upx 0 40upx;
                text-align: center;
                font-size: 40upx;
                font-family: Hiragino Sans-W6, Hiragino Sans;
                font-weight: normal;
                color: #0A4B6D;
            }
            
            .ipName{
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                text-align: center;
            }
        }
    }
    
    
</style>
