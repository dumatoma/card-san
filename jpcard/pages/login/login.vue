<template>
    <view class="container">
        <view style="height: 80upx;"></view>
        <view class="top_title">
            <!-- <image src="../../static/icons/title.png" alt="" style="width: 332upx;height: 39upx;"></image>
            <image src="../../static/icons/card_2.png" style="width: 186upx;height: 36upx;" alt=""></image> -->
            <image src="../../static/icons/tttile.png" mode="widthFix" style="width: 60%;"></image>
        </view>
        <view class="logo">
            <image src="../../static/icons/start.svg" mode="widthFix" alt=""></image>
        </view>
        <view class="title">
            ログインしてください 
        </view>
        <view class="register">
            まだアカウントの登録 がお済みでない場合は、アカウント登録をお願いします。<span id="goRegister" @click="toRegister">登録はこちら>></span>
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
            <view class="btnItem" @click="iosLogin" v-if="env == 'ios'">
                <image src="../../static/icons/ios.png" mode="widthFix" alt=""></image>
            </view>
        </view>
        <view class="userAgreement">
            サインイン、ログインすることでCard-San Appの<br /> <text @click="toProvice"
                style="text-decoration: underline;color: cornflowerblue;">利用規約とプライバシーポリシー</text> に同意したものとみなされます。
        </view>
        <!-- <view class="skip" @click="toIndex">
            スキップ >>
        </view> -->
    </view>
</template>
<script>
    const JYGoogleSignin = uni.requireNativePlugin('JY-GoogleSignin');
    import {
        getLineUrl,
        getGoogleUrl,
        setGoogleLogin,
        getFacebookUrl,
        setFacebookLogin,
        setAppleLogin,
        getLineInfo,
        setIosLogin
    } from "@/api/index.js"
    export default {
        data() {
            return {
                query: {},
                env: uni.getStorageSync("env")
            }
        },
        computed: {

        },
        created() {
            console.log(JYGoogleSignin)
            JYGoogleSignin.jy_init({
                //  安卓的client_id应该是谷歌开发者后台默认Web应用的；iOS的client_id应该是谷歌开发者后台iOS对应的  
                // client_id: "653081471652-e7f6bu17gcp0p0mku61qdg8am5fcriv1.apps.googleusercontent.com" //安卓的client_id
                client_id: "653081471652-tq9goiocjd1q4snjllaapu8uo90rtcau.apps.googleusercontent.com" //苹果的client_id
            }, res => {
                //    这里不会有返回数据
                console.log('初始化成功')
            })
            this.env = uni.getStorageSync("env")
            let flag = false
            plus.globalEvent.addEventListener('plusMessage', function(msg) {
                if (msg.data.args.data.arg.url) {
                    let temp = msg.data.args.data.arg.url.split('?')
                    let fin = temp[1].split("=")
                    let code = fin[1]
                    if (flag == false) {
                        flag = true
                        uni.showLoading({
                            title: "読み込み中"
                        })
                        getLineInfo(code).then((result) => {
                            uni.hideLoading()
                            if (result.code == 200) {
                                // flag = false
                                uni.showToast({
                                    title: result.message,
                                    duration: 2000,
                                    icon: "none"
                                })
                                uni.setStorageSync("token", result.data.token)
                                uni.setStorageSync("user", result.data.user)
                                setTimeout(() => {
                                    uni.redirectTo({
                                        url: "../index/index"
                                    })
                                }, 2000)

                            } else {
                                uni.showToast({
                                    title: result.message,
                                    icon: 'none'
                                })
                            }
                        })
                    }

                } else {
                    return false
                }
            });
        },
        mounted() {

        },
        onLoad(options) {
            let that = this
            let query = options
            that.query = query
        },
        methods: {
            iosLogin() {
                let that = this
                uni.login({
                    provider: 'apple',
                    success: function(loginRes) {
                        // console.log("loginRes",loginRes)
                        // 登录成功 res.data.token
                        uni.getUserInfo({
                            provider: 'apple',
                            success: function(info) {
                                // console.log("info",info)
                                setIosLogin(info.userInfo.openId, info.userInfo.identityToken)
                                    .then((result) => {
                                        if (result.code == 200) {
                                            uni.showToast({
                                                title: result.message,
                                                duration: 2000,
                                                icon: "none"
                                            })
                                            uni.setStorageSync("token", result.data.token)
                                            uni.setStorageSync("user", result.data.user)
                                            setTimeout(function() {
                                                uni.redirectTo({
                                                    url: "../index/index"
                                                })
                                            }, 2000)
                                        } else {
                                            uni.showToast({
                                                title: result.message,
                                                icon: 'none'
                                            })
                                        }
                                    })
                                // // 获取用户信息成功, info.authResult保存用户信息
                            }
                        })
                    },
                    fail: function(err) {
                        // 登录授权失败  
                        // err.code是错误码
                        console.log(err)
                        uni.showToast({
                            title: "サインインできませんでした",
                            icon: "none"
                        })
                    }
                });
            },

            getUrlLine() {
                getLineUrl().then((res) => {
                    if (res.code == 200) {
                        uni.navigateTo({
                            url: "../../pagesA/webLogin/webLogin?url=" + res.data.url
                        })
                    }
                })
            },

            // getUrlGoogle() {
            //     getGoogleUrl().then((res) => {
            //         if (res.code == 200) {
            //             JYGoogleSignin.jy_startLogin(results => {
            //                 //  这里会返回登录的结果，如果errorCode = 1，代表错误，可检查msg返回的数据判断；如果errorCode = 0，代表成功，也会在data里面返回登录数据
            //                 if (results.errorCode == 0) {
            //                     let data = {}
            //                     data['token'] = res.data.token
            //                     data['name'] = results.data.fullName
            //                     data['openid'] = results.data.userId
            //                     data['google_email'] = results.data.email
            //                     setGoogleLogin(data).then((result) => {
            //                         if (result.code == 200) {
            //                             uni.showToast({
            //                                 title: "5669"+result.message,
            //                                 duration: 2000,
            //                                 icon: "none"
            //                             })
            //                             uni.setStorageSync("token", result.data.token)
            //                             uni.setStorageSync("user", result.data.user)
            //                             setTimeout(function() {
            //                                 uni.redirectTo({
            //                                     url: "../index/index"
            //                                 })
            //                             }, 2000)
            //                         } else {
            //                             uni.showToast({
            //                                 title: "0" + result.message,
            //                                 icon: 'none'
            //                             })
            //                         }
            //                     })
            //                 } else {
            //                     uni.showToast({
            //                         icon: 'none',
            //                         title: "588"+JSON.stringify(results)
            //                     })
            //                 }

            //             })
            //         } else {
            //             uni.showToast({
            //                 title: "4" + res.message,
            //                 icon: 'none'
            //             })
            //         }
            //     })
            // },
            getUrlGoogle() {
                getGoogleUrl().then((res) => {
                    if (res.code == 200) {
                        uni.login({
                            provider: 'google',
                            success: function(loginRes) {
                                // 登录成功 res.data.token
                                console.log("step1",loginRes)
                                uni.getUserInfo({
                                    provider: 'google',
                                    success: function(info) {
                                        if (info.errMsg == 'getUserInfo:ok') {
                                            let data = {}
                                            data['token'] = res.data.token
                                            data['name'] = info.userInfo.nickname
                                            data['openid'] = info.userInfo.openid
                                            data['google_email'] = info.userInfo.email
                                            setGoogleLogin(data).then((result) => {
                                                console.log("step3",result)
                                                if (result.code == 200) {
                                                    uni.showToast({
                                                        title: result.message,
                                                        duration: 2000,
                                                        icon: "none"
                                                    })
                                                    uni.setStorageSync("token",result.data.token)
                                                    uni.setStorageSync("user",result.data.user)
                                                    let token = uni.getStorageSync("token")
                                                    let info = uni.getStorageSync("user")
                                                    let uuid = info.uuid
                                                    setTimeout(function() {
                                                        uni.redirectTo({
                                                            url: "../index/index"
                                                        })
                                                    }, 2000)
                                                } else {
                                                    uni.showToast({
                                                        title:"0"+result.message,
                                                        icon: 'none'
                                                    })
                                                }
                                            })
                                        }else{
                                            uni.showToast({
                                                title:"1"+info.errMsg,
                                                icon:"none"
                                            })
                                        }
                                    }
                                })
                            },
                            fail: function(err) {
                                let errword = JSON.stringify(err)
                                uni.showModal({
                                    title:"warning",
                                    content:err,
                                    success(res) {
                                        if(res.confirm){
                                            that.copyTextToClipboard(err)
                                        }
                                    }
                                })
                            }
                        });
                    }else{
                        uni.showToast({
                            title: "4"+res.message,
                            icon: 'none'
                        })
                    }
                })
            },
            copyTextToClipboard(text) {
                uni.setClipboardData({
                    data: text,
                    success: function() {
                        console.log('copy success');
                        // 可以添加用户友好的提示，例如使用uni.showToast提示复制成功
                        uni.showToast({
                            title: 'copy success',
                            icon: 'success',
                            duration: 2000
                        });
                    },
                    fail: function() {
                        console.log('copy failed');
                        // 可以添加提示复制失败的逻辑处理
                    }
                });
            },
            toCode() {
                uni.navigateTo({
                    url: "../sendCode/sendCode?source=login"
                })
            },
            toProvice() {
                uni.navigateTo({
                    url: "../../pagesA/privacyPolicy/privacyPolicy"
                })
            },
            toIndex() {
                uni.redirectTo({
                    url: "../index/index"
                })
            },
            toRegister() {
                uni.navigateTo({
                    url: "../register/register"
                })
            }
        }
    }
</script>

<style lang="scss">
    .container {
        .top_title {
            image {
                display: block;
                margin: 0 auto;
            }
        }

        .logo {
            text-align: center;
            margin: 16upx auto 60upx;
            width: 160upx;
            height: 160upx;

            image {
                width: 100%;
                height: 100%;
            }
        }

        .title {
            font-size: 48upx;
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
        }

        .register {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-top: 80upx;

            #goRegister {
                color: #1a73e8;
                font-weight: bold;
                font-size: 32rpx;
            }
        }

        .userAgreement {
            text-align: center;
            font-size: 20upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin: 100upx 0 92upx;
            box-sizing: border-box;
            padding: 0 20upx;
        }

        .skip {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #AAAAAA;
            text-align: center;
            padding-bottom: 130upx;
        }

        .btnItem {
            width: 100%;
            border-radius: 28upx;
            margin-bottom: 40upx;
            height: 120upx;

            image {
                width: 100%;
                height: 100%;
            }
        }

        .logBtns {
            margin-top: 140upx;
        }

        .l {
            height: 120upx;
            background: #F5F5F7;
            box-shadow: 0upx 2upx 6upx 2upx rgba(0, 0, 0, 0.3);
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