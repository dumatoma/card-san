<template>
    <view class="container">
        <view style="height: 80upx;"></view>
        <view class="top_title">
            <image src="../../static/icons/title.png" style="width: 300upx;" alt="" mode="widthFix"></image>
        </view>
        <view class="logo">
            <image src="../../static/icons/start.svg" alt=""></image>
        </view>
        <view class="title">
            ようこそ！
        </view>
        <view class="register">
            安全かつ便利にお使い頂くためにアカウントの登録 をお願いします。　登録がない場合、機種変更などの際にデータの引き継ぎが出来なくなります。
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
        </view>
        <view class="userAgreement">
            サインイン、ログインすることでCard-San Appの<br/> <text @click="toProvice" style="text-decoration: underline;color: cornflowerblue;">利用規約とプライバシーポリシー</text> に同意したものとみなされます。
        </view>
        <!-- <view class="skip" @click="toIndex">
            スキップ >>
        </view> -->
    </view>
</template>

<script>
    import {mapState,mapMutations,mapActions} from "vuex"
    import {getLineUrl,getGoogleUrl,setGoogleLogin, getFacebookUrl,setFacebookLogin,setAppleLogin,getLineInfo,setIosLogin} from "@/api/index.js"
    export default {
        data() {
            return {
                env:uni.getStorageSync("env")
            }
        },
        computed:{
           
        },
        created() {
           
        },
        methods: {
            // ...mapActions(["login"]),
            toCode(){
                uni.navigateTo({
                    url:"../sendCode/sendCode?source=register"
                })
            },
            toIndex(){
                uni.redirectTo({
                    url:"../login/login"
                })
            },
            toProvice(){
               uni.navigateTo({
                   url:"../../pagesA/privacyPolicy/privacyPolicy"
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
                              setIosLogin(info.userInfo.user,info.userInfo.identityToken).then((result) => {
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
            
            faceLogin(){
                getFacebookUrl().then((res) => {
                    // console.log("dag",res)
                    // uni.navigateTo({
                    //     url:"../../pagesA/facelogin/facelogin"
                    // })
                    if(res.code == 200){
                        uni.navigateTo({
                            url:"../../pagesA/facelogin/facelogin?url="+res.data.url
                        })
                        uni.login({
                            provider: 'facebook',
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
                                            setFacebookLogin(data).then((result) => {
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
           font-weight: bolder;
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
           margin: 100upx 0 92upx;
           box-sizing: border-box;
           padding: 0 20upx;
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
