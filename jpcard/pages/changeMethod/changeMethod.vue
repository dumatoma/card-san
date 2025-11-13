<template>
    <view class="container">
        <view style="height: 40upx;"></view>
        <view class="title">
            ご希望のサインイン方法を選択してください。
        </view>
        <view class="register">
            ご登録の電話番号の変更は、「電話番号(SMS)でサインイン」を選択してください
        </view>
        <view class="logBtns">
            <view class="btnItem l" @click="toCode">
                電話番号(SMS)でサインイン
            </view>
            <view class="btnItem">
                <image @click="getUrlLine" src="../../static/icons/line.png" alt=""></image>
            </view>
            <view class="btnItem">
                <image @click="getUrlGoogle" src="../../static/icons/google.png" mode="widthFix" alt=""></image>
            </view>
            <view @click="iosLogin" v-if="env == 'ios'" class="btnItem">
                <image src="../../static/icons/ios.png" mode="widthFix" alt=""></image>
            </view>
            <!-- <view class="btnItem">
                <image src="../../static/icons/faceboo.png" mode="widthFix" alt=""></image>
            </view> --> 
           
        </view>
        <!-- <view class="userAgreement">
            サインイン、ログインすることでCARDさんAppの利用規約とプライバシーポリシーに同意したものとみなされます。
        </view> -->
        <!-- <view style="height: 10upx;"></view> -->
        <!-- <view class="skip" @click="toIndex">
            返回登录
        </view> -->
    </view>
</template>

<script>
    import {mapState,mapMutations,mapActions} from "vuex"
    import {getLineUrl,getGoogleUrl,setGoogleLogin,setAppleLogin,getLineInfo,setIosLogin} from "@/api/index.js"
    export default {
        data() {
            return {
                env:uni.getStorageSync("env")
            }
        },
        computed:{
           
        },
        created() {
           plus.oauth.getServices(servies => {
               const provide = servies.reduce((cur, next) => {
                   cur.push(next.id);
                   return cur
               }, [])
               const apple = servies[provide.indexOf("apple")]
               apple.logout(res => {
                   console.log("注销成功")
               }, err => {
                   console.log("注销失败", JSON.stringify(err))
               })
           })
        },
        methods: {
            // ...mapActions(["login"]),
           toCode() {
               uni.navigateTo({
                   url: "../sendCode/sendCode?source=login"
               })
           },
            toIndex(){
                uni.redirectTo({
                    url:"../login/login"
                })
            },
            
            iosLogin(){
               let that = this
               uni.login({
                   provider: 'apple',
                   success: function (loginRes) {
                       // 登录成功 res.data.token
                       uni.getUserInfo({
                           provider: 'apple',
                           success: function(info) {
                              setIosLogin(info.userInfo.openId,info.userInfo.identityToken).then((result) => {
                                  if(result.code == 200){
                                      uni.showToast({
                                          title: result.message,
                                          duration: 2000,
                                          icon: "none"
                                      })
                                      uni.setStorageSync("token",result.data.token)
                                      uni.setStorageSync("user",result.data.user)
                                      setTimeout(function() {
                                          uni.redirectTo({
                                              url: "../index/index"
                                          })
                                      }, 2000)
                                  }else{
                                      uni.showToast({
                                          title:result.message,
                                          icon:'none'
                                      })
                                  }
                              })
                               // 获取用户信息成功, info.authResult保存用户信息
                           }
                       })
                   },
                   fail: function (err) {
                       // 登录授权失败  
                       // err.code是错误码
                       uni.showToast({
                           title:"サインインできませんでした",
                           icon:"none"
                       })
                   }
               });
            },
            
            getUrlLine(){
                getLineUrl().then((res) => {
                    if(res.code == 200){
                        uni.navigateTo({
                            url:"../../pagesA/webLogin/webLogin?url="+res.data.url
                        })
                    }
                })
            },
            
            getUrlGoogle(){
                getGoogleUrl().then((res) => {
                    if(res.code == 200){
                        uni.login({
                            provider: 'google',
                            success: function (loginRes) {
                                // 登录成功 res.data.token
                                uni.getUserInfo({
                                    provider: 'google',
                                    success: function(info) {
                                        if(info.errMsg == 'getUserInfo:ok'){
                                            let data = {}
                                            data['token'] = res.data.token
                                            data['name'] = info.userInfo.nickname
                                            data['openid'] = info.userInfo.openid
                                            setGoogleLogin(data).then((result) => {
                                                if(result.code == 200){
                                                    uni.showToast({
                                                        title: result.message,
                                                        duration: 2000
                                                    })
                                                    uni.setStorageSync("token",result.data.token)
                                                    uni.setStorageSync("user",result.data.user)
                                                    setTimeout(function() {
                                                        uni.redirectTo({
                                                            url: "../index/index"
                                                        })
                                                    }, 2000)
                                                }else{
                                                   uni.showToast({
                                                       title:result.message,
                                                       icon:'none'
                                                   })
                                               }
                                            })
                                        }
                                        // 获取用户信息成功, info.authResult保存用户信息
                                    }
                                })
                            },
                            fail: function (err) {
                            }
                        });
                    }
                })  
            },
        }
    }
</script>

<style lang="scss">
   .container{
       .top_title{
           image{
               display: block;
               margin: 0 auto;
           }
           
           image:last-child{
               margin: 0 auto;
               margin-top: 22upx;
           }
       }
       
       .skip{
           font-size: 24upx;
           font-family: Hiragino Sans-W3, Hiragino Sans;
           font-weight: normal;
           color: #AAAAAA;
           text-align: center;
           padding-bottom: 130upx;
       }
       
       .logo{
           text-align: center;
           margin: 16upx auto 60upx;
           width: 160upx;
           height: 160upx;
           
           image{
               width: 100%;
               height: 100%;
           }
       }
       
       .title{
           font-size: 48upx;
           font-family: Hiragino Sans-W6, Hiragino Sans;
           font-weight: bold;
           color: #1D1D1F;
           text-align: center;
       }
       
       .register{
           font-size: 28upx;
           font-family: Hiragino Sans-W3, Hiragino Sans;
           font-weight: normal;
           color: #1D1D1F;
           margin-top: 80upx;
           
          #goRegister{
              color: #D93025;
          }
       }
       
       .userAgreement{
           text-align: center;
           font-size: 20upx;
           font-family: Hiragino Sans-W3, Hiragino Sans;
           font-weight: normal;
           color: #707070;
           margin: 100upx 0 208upx;
           box-sizing: border-box;
           padding: 0 44upx;
       }
       
       .skip{
           font-size: 24upx;
           font-family: Hiragino Sans-W3, Hiragino Sans;
           font-weight: normal;
           color: #AAAAAA;
           text-align: center;
           padding-bottom: 130upx;
       }
       
       .btnItem{
           width: 100%;
           border-radius: 28upx;
           margin-bottom: 40upx;
           height: 120upx;
           
           image{
               width: 100%;
               height: 100%;
           }
       }
       
       .logBtns{
           margin-top: 140upx;
       }
       
       .l{
           height: 120upx;
           background: #F5F5F7;
           box-shadow: 0upx 2upx 6upx 2upx rgba(0,0,0,0.3);
           border-radius: 20upx;
           text-align: center;
           line-height: 120upx;
           font-size: 36upx;
           font-family: Hiragino Sans-W5, Hiragino Sans;
           font-weight: normal;
           color: #1D1D1F;
       }
   }
</style>
