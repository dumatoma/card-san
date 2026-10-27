<template>
    <view class="container">
        <!-- [カスタムエイド 2026-10-26e] 上の文字が時計(ステータスバー)に重なって切れていたため、ステータスバーの高さぶん下げる(元 80upx 固定) -->
        <view :style="{ height: (csTop + 24) + 'px' }"></view>
        <view class="top_title">
            <!-- <image src="../../static/icons/title.png" alt="" style="width: 332upx;height: 39upx;"></image>
            <image src="../../static/icons/card_2.png" style="width: 186upx;height: 36upx;" alt=""></image> -->
            <image src="../../static/icons/tttile.png" mode="widthFix" style="width: 52%;"></image>
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
        <!-- [カスタムエイド 2026-10-26d] 押したとき少し縮んで薄くなる(坂本さんの指示)
             [カスタムエイド 2026-10-26b] 版Bモックと同じボタン(角をいっぱいまで丸く・同じ書体と太さ・全部「〜でサインイン」)。順番は Apple・Google・LINE・SMS(Android は Apple なし) -->
        <view class="cs-btns">
            <view class="cs-btn-w" v-if="env == 'ios'" @click="iosLogin" hover-class="cs-btn-press" :hover-stay-time="120"><image class="cs-btn" src="../../static/icons/cs_btn_apple.png"></image></view>
            <view class="cs-btn-w" @click="getUrlGoogle" hover-class="cs-btn-press" :hover-stay-time="120"><image class="cs-btn" src="../../static/icons/cs_btn_google.png"></image></view>
            <view class="cs-btn-w" @click="getUrlLine" hover-class="cs-btn-press" :hover-stay-time="120"><image class="cs-btn" src="../../static/icons/cs_btn_line.png"></image></view>
            <view class="cs-btn-w" @click="toCode" hover-class="cs-btn-press" :hover-stay-time="120"><image class="cs-btn" src="../../static/icons/cs_btn_sms.png"></image></view>
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
    // [カスタムエイド 2026-10-19] Google プラグイン(JY-GoogleSignin)はアプリに組み込まれていないため読み込みをやめた(Google ログインは uni.login)
    import { csLineLogin } from '@/utils/csShopLink.js' // [カスタムエイド 2026-10-26e]
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
    // [カスタムエイド 2026-10-19] LINE ログインの受け取りを1回だけ登録するための印と、最後に処理した code
    let customaidLineListenerAdded = false
    let lastCode = ''
    export default {
        data() {
            return {
                query: {},
                csTop: uni.getSystemInfoSync().statusBarHeight || 20, // [カスタムエイド 2026-10-26e] ステータスバーの高さ(px)
                env: uni.getSystemInfoSync().platform // [カスタムエイド 2026-10-19] 端末から直接取る
            }
        },
        computed: {

        },
        created() {
            // [カスタムエイド 2026-10-19] 組み込まれていないプラグインの jy_init でエラーになり、この下(LINE ログインの受け取りなど)が動いていなかったため削除
            // 端末の種類は保存データではなく端末から直接取る(ログアウト後に保存データが消えて Apple ボタンが出なくなっていた)
            this.env = uni.getSystemInfoSync().platform
            // [カスタムエイド 2026-10-19] ログイン画面を開くたびに受け取りが重ならないよう、アプリ全体で1回だけ登録する。
            // 同じ code は1回だけ処理する(ログアウト後に別の LINE アカウントでログインし直せるよう、code ごとに判定)
            if (customaidLineListenerAdded) {
                return
            }
            customaidLineListenerAdded = true
            plus.globalEvent.addEventListener('plusMessage', function(msg) {
                if (msg && msg.data && msg.data.args && msg.data.args.data && msg.data.args.data.arg && msg.data.args.data.arg.url) {
                    let temp = msg.data.args.data.arg.url.split('?')
                    let fin = (temp[1] || '').split("=")
                    let code = fin[1]
                    if (code && code !== lastCode) {
                        lastCode = code
                        uni.showLoading({
                            title: "読み込み中"
                        })
                        getLineInfo(code).then((result) => {
                            uni.hideLoading()
                            if (result.code == 200) {
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
                        }).catch(() => { uni.hideLoading() })
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
                                setIosLogin(info.userInfo.openId, info.userInfo.identityToken, (info.userInfo.fullName ? ((info.userInfo.fullName.familyName || '') + ' ' + (info.userInfo.fullName.givenName || '')).trim() : '')) /* [カスタムエイド 2026-10-26e] Apple のお名前も送る */
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
                                    }).catch(() => {})
                                // // 获取用户信息成功, info.authResult保存用户信息
                            }
                        })
                    },
                    fail: function(err) {
                        uni.showToast({
                            title: "サインインできませんでした",
                            icon: "none"
                        })
                    }
                });
            },

            getUrlLine() {
                // [カスタムエイド 2026-10-26e] LINE のログイン画面を、アプリの中ではなく Safari で開く(LINE アプリにログインしている人は、入力なしでログインできる)。
                //   ログインが終わると cardsan://line-login でアプリに戻り、App.vue → csShopLink.js の handleLaunchLink で受け取る(どの画面からでも)
                csLineLogin()
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
                                                if (result.code == 200) {
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
                                                } else {
                                                    uni.showToast({
                                                        title:"0"+result.message,
                                                        icon: 'none'
                                                    })
                                                }
                                            }).catch(() => {})
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
                }).catch(() => {})
            },
            copyTextToClipboard(text) {
                uni.setClipboardData({
                    data: text,
                    success: function() {
                        uni.showToast({
                            title: 'copy success',
                            icon: 'success',
                            duration: 2000
                        });
                    },
                    fail: function() {
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
    /* [カスタムエイド 2026-10-26b] サインインのボタン(版Bモックと同じ寸法) */
    .cs-btns { display: flex; flex-direction: column; align-items: center; margin-top: 60upx; } /* [カスタムエイド 2026-10-26e] 上との間 100→60upx(ボタンの大きさ・ボタン同士の間は変えない) */
    .cs-btn-w { width: 540upx; height: 101upx; margin-bottom: 18upx; border-radius: 51upx; transition: transform 0.1s, opacity 0.1s; }
    .cs-btn-press { transform: scale(0.96); opacity: 0.7; } /* [カスタムエイド 2026-10-26d] 押したときの動き */
    .cs-btn { display: block; width: 540upx; height: 101upx; }

    .container {
        .top_title {
            image {
                display: block;
                margin: 0 auto;
            }
        }

        .logo {
            text-align: center;
            margin: 28upx auto 40upx; /* [カスタムエイド 2026-10-26e] 見やすさの調整(元 16upx auto 60upx・160upx) */
            width: 132upx;
            height: 132upx;

            image {
                width: 100%;
                height: 100%;
            }
        }

        .title {
            font-size: 44upx; /* [カスタムエイド 2026-10-26e] 元 48upx・細字 */
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: 700;
            color: #1D1D1F;
            text-align: center;
        }

        .register {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-top: 36upx; /* [カスタムエイド 2026-10-26e] 元 80upx */
            line-height: 1.7;

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
            margin: 40upx 0 60upx; /* [カスタムエイド 2026-10-26e] 元 100upx 0 92upx */
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