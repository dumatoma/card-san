<template>
    <view class="container">
        <!-- [カスタムエイド 2026-10-26] 店舗QR統一(版B):お店の QR・リンクから来た人には、そのお店のカードを見せて登録してもらう。
             それ以外の人には、上に「お店のQRコードをお持ちですか?」を出す
             [カスタムエイド 2026-10-26b] 版Bモック(画面4・画面3')どおりに作り直した -->
        <view class="cs-shop" v-if="csShop" :style="{ paddingTop: (csTop + 16) + 'px' }">
            <view class="cs-brand">
                <image class="cs-brand-icon" src="../../static/icons/cs_appicon.png"></image>
                <text class="cs-brand-t">Card-San App</text>
            </view>
            <view class="cs-card" :style="{ background: (csShop.card && csShop.card.background_color) || '#F2F2F4' }">
                <image v-if="csShop.card && csShop.card.image" class="cs-card-img" :src="csShop.card.image" mode="widthFix"></image>
                <view v-else class="cs-card-blank"></view>
            </view>
            <view class="cs-shop-name">{{ csShop.shop_name }}</view>
            <view class="cs-h1">
                <view>このカードを受け取るために、</view>
                <view>アカウントを作成してください</view>
            </view>
            <view class="cs-reasons">
                <view>・スマホを買いかえても、カード・スタンプ・ポイントを引き継げます</view>
                <view>・お店の予約・クーポン・メッセージが使えます</view>
                <view>・お名前や連絡先は、プライバシーポリシーにそって大切に扱います</view>
            </view>
            <view class="cs-after">登録後、そのままお店の会員情報の確認へ進みます</view>
        </view>
        <view v-else class="cs-welcome" :style="{ paddingTop: (csTop + 20) + 'px' }">
            <view class="cs-qr">
                <view class="cs-qr-t">お店のQRコードをお持ちですか？</view>
                <view class="cs-qr-btn" hover-class="cs-hover" @click="csScan">QRコードを読み取る</view>
                <view class="cs-qr-s">読み取ると、お店のカードを見ながら登録できます</view>
            </view>
            <view class="cs-title">ようこそ！</view>
            <view class="cs-register">安全かつ便利にお使い頂くためにアカウントの登録をお願いします。登録がない場合、機種変更などの際にデータの引き継ぎが出来なくなります。</view>
        </view>
        <!-- [カスタムエイド 2026-10-26d] 押したとき少し縮んで薄くなる(坂本さんの指示)
             [カスタムエイド 2026-10-26b] ボタンは角をいっぱいまで丸く・同じ書体と太さ・全部「〜でサインイン」。順番は Apple・Google・LINE・SMS(Android は Apple なし) -->
        <view class="cs-btns" :class="{ 'cs-btns-shop': csShop }">
            <view class="cs-btn-w" v-if="env == 'ios'" @click="iosLogin" hover-class="cs-btn-press" :hover-stay-time="120"><image class="cs-btn" src="../../static/icons/cs_btn_apple.png"></image></view>
            <view class="cs-btn-w" @click="getUrlGoogle" hover-class="cs-btn-press" :hover-stay-time="120"><image class="cs-btn" src="../../static/icons/cs_btn_google.png"></image></view>
            <view class="cs-btn-w" @click="getUrlLine" hover-class="cs-btn-press" :hover-stay-time="120"><image class="cs-btn" src="../../static/icons/cs_btn_line.png"></image></view>
            <view class="cs-btn-w" @click="toCode" hover-class="cs-btn-press" :hover-stay-time="120"><image class="cs-btn" src="../../static/icons/cs_btn_sms.png"></image></view>
        </view>
        <!-- [カスタムエイド 2026-10-26] すでにアカウントがある人はログインへ(目立つように)。[カスタムエイド 2026-10-26d]「(ログイン)」は1行に収めるため外した(坂本さんの指示) -->
        <view class="cs-login" hover-class="cs-hover" @click="toIndex">すでにアカウントをお持ちの方はこちら</view>
        <view class="userAgreement">
            サインイン、ログインすることで Card-San App の<text @click="toProvice" style="text-decoration: underline;color: cornflowerblue;">利用規約とプライバシーポリシー</text>に同意したものとみなされます。
        </view>
    </view>
</template>

<script>
    import {mapState,mapMutations,mapActions} from "vuex"
    import { pendingShopId, fetchShopCard, scanShopId, csLineLogin } from '@/utils/csShopLink.js' // [カスタムエイド 2026-10-26] 店舗QR統一
    import {getLineUrl,getGoogleUrl,setGoogleLogin, getFacebookUrl,setFacebookLogin,setAppleLogin,getLineInfo,setIosLogin} from "@/api/index.js"
    export default {
        data() {
            return {
                csShop: null, // [カスタムエイド 2026-10-26] お店の QR・リンクから来たときの、お店とカードの情報
                csTop: uni.getSystemInfoSync().statusBarHeight || 20, // [カスタムエイド 2026-10-26e] ステータスバーの高さ(px)。上の文字が時計に重ならないよう、その分下げる
                env: uni.getSystemInfoSync().platform // [カスタムエイド 2026-10-19] 保存データではなく端末から直接取る(ログアウト後に Apple ボタンが消えていた)
            }
        },
        computed:{
           
        },
        created() {
           
        },
        onShow() {
            this.csLoadShop() // [カスタムエイド 2026-10-26]
        },
        methods: {
            // [カスタムエイド 2026-10-26] ログイン前に受け取った店舗IDがあれば、そのお店とカードの情報を読む
            csLoadShop() {
                const id = pendingShopId()
                if (!id) { this.csShop = null; return }
                if (this.csShop && this.csShop.id == id) return
                fetchShopCard(id, this.$baseUrl).then((d) => { this.csShop = d })
            },
            // [カスタムエイド 2026-10-26] 「QRコードを読み取る」:お店の QR なら店舗IDを覚えて、カードの画像つきの登録画面にする
            csScan() {
                scanShopId().then((sid) => {
                    if (sid === -1) return
                    if (!sid) {
                        uni.showModal({ title: 'お店の QR コードではありません', content: 'お店に置いてある Card-San の QR コードを読み取ってください。', showCancel: false, confirmText: '閉じる' })
                        return
                    }
                    uni.setStorageSync('csPendingShop', { id: sid, at: Math.floor(Date.now() / 1000) })
                    this.csLoadShop()
                })
            },
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
                              setIosLogin(info.userInfo.openId || info.userInfo.user, info.userInfo.identityToken, /* [カスタムエイド 2026-10-27] 利用者の番号は openId(ログイン画面と同じ)。user は来ないことがあり「undefined」が送られて error になっていた */ (info.userInfo.fullName ? ((info.userInfo.fullName.familyName || '') + ' ' + (info.userInfo.fullName.givenName || '')).trim() : '')) /* [カスタムエイド 2026-10-26e] Apple のお名前も送る */.then((result) => {
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
                              }).catch(() => {})
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
            
            getUrlLine() {
                // [カスタムエイド 2026-10-26e] LINE のログイン画面を、アプリの中ではなく Safari で開く(LINE アプリにログインしている人は、入力なしでログインできる)。
                //   ログインが終わると cardsan://line-login でアプリに戻り、App.vue → csShopLink.js の handleLaunchLink で受け取る(どの画面からでも)
                csLineLogin()
            },
            
            faceLogin(){
                getFacebookUrl().then((res) => {
                    if(res.code == 200){
                        uni.navigateTo({
                            url:"../../pagesA/facelogin/facelogin?url="+res.data.url
                        })
                        uni.login({
                            provider: 'facebook',
                            success: function (loginRes) {
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
                                            }).catch(() => {})
                                        }
                                    }
                                })
                            },
                            fail: function (err) {
                            }
                        });
                    }
                }).catch(() => {})
            },
            
            getUrlGoogle(){
                getGoogleUrl().then((res) => {
                    if(res.code == 200){
                        uni.login({
                            provider: 'google',
                            success: function (loginRes) {
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
                                            }).catch(() => {})
                                        }
                                    }
                                })
                            },
                            fail: function (err) {
                            }
                        });
                    }
                }).catch(() => {})
            },
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-26] 店舗QR統一:カードの画像つきの登録画面・QRコードの入り口
       [カスタムエイド 2026-10-26b] 版Bモック(画面4・画面3')の寸法 */
    .cs-hover { opacity: 0.6; }
    .cs-shop { } /* [カスタムエイド 2026-10-26e] 上の余白はステータスバーの高さから決める(元 96upx 固定) */
    .cs-brand { display: flex; align-items: center; }
    .cs-brand-icon { width: 56upx; height: 56upx; border-radius: 14upx; border: 1px solid #E5E5EA; margin-right: 16upx; }
    .cs-brand-t { font-size: 28upx; font-weight: 700; color: #6E6E73; }
    .cs-card { width: 404upx; margin: 24upx auto 0; border-radius: 24upx; overflow: hidden; border: 1px solid #E5E5EA; }
    .cs-card-img { display: block; width: 100%; }
    .cs-card-blank { height: 252upx; }
    .cs-shop-name { text-align: center; font-size: 34upx; font-weight: 700; color: #1D1D1F; margin-top: 12upx; }
    .cs-h1 { text-align: center; font-size: 36upx; font-weight: 700; color: #1D1D1F; line-height: 1.5; margin-top: 30upx; }
    .cs-reasons { margin-top: 20upx; padding: 22upx 26upx; border-radius: 24upx; background: #F5F5F7; font-size: 23upx; line-height: 1.6; color: #3A3A3C; }
    .cs-reasons view + view { margin-top: 10upx; }
    .cs-after { text-align: center; font-size: 25upx; color: #6E6E73; line-height: 1.6; margin-top: 16upx; }
    .cs-welcome { } /* [カスタムエイド 2026-10-26e] 上の余白はステータスバーの高さから決める(元 104upx 固定) */
    .cs-qr { padding: 34upx; border-radius: 32upx; background: #F5F5F7; text-align: center; }
    .cs-qr-t { font-size: 29upx; font-weight: 700; color: #1D1D1F; }
    .cs-qr-btn { margin-top: 20upx; height: 92upx; border-radius: 46upx; background: #0A66D6; color: #FFFFFF; font-size: 29upx; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .cs-qr-s { font-size: 23upx; color: #6E6E73; margin-top: 18upx; }
    .cs-title { text-align: center; font-size: 50upx; font-weight: 700; color: #1D1D1F; margin-top: 50upx; }
    .cs-register { font-size: 25upx; line-height: 1.7; color: #3A3A3C; margin-top: 20upx; }
    .cs-btns { display: flex; flex-direction: column; align-items: center; margin-top: 34upx; }
    .cs-btns-shop { margin-top: 26upx; }
    .cs-btn-w { width: 540upx; height: 101upx; margin-bottom: 18upx; border-radius: 51upx; transition: transform 0.1s, opacity 0.1s; }
    .cs-btn-press { transform: scale(0.96); opacity: 0.7; } /* [カスタムエイド 2026-10-26d] 押したときの動き */
    .cs-btn { display: block; width: 540upx; height: 101upx; }
    .cs-login { text-align: center; font-size: 27upx; font-weight: 700; color: #1D1D1F; margin: 40upx 0 0; }

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
           margin: 16upx 0 60upx; /* [カスタムエイド 2026-10-26b] モックどおり「ログイン」のすぐ下に(元 100upx 0 92upx) */
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
