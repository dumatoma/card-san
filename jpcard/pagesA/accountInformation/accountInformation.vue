<template>
  <view class="bg">
    <u-navbar leftText="" title="" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56"
      :border="true" @rightClick="rightClick">
      <view class="u-nav-slot" slot="left">
        <text style="font-weight: 700;"> {{name}} </text> さん
      </view>
      <view class="u-nav-right" slot="right">
        <u-icon name="close" color="#1D1D1F" size="20" ></u-icon>
      </view>
    </u-navbar>
    <view class="big-box">
      <view class="box" @click="modify()" hover-class="bk">
        <view class="item">
          <div class="left">
            <image src="../../static/image/user.png" mode=""></image>
            <text>アカウント情報</text>
          </div>
          <div class="right" v-if="!name">
            <image src="../../static/image/error.png" mode=""></image>
          </div>
        </view>
      </view>
      <view class="box" @click="saoMa()" hover-class="bk">
        <view class="item">
          <div class="left">
            <image src="../../static/image/QR.png" @click="sao" mode=""></image>
            <text>QRコードリーダー</text>
          </div>
          <div class="right">
          </div>
        </view>
      </view>
      <view class="box" @click="addCard()" hover-class="bk">
        <view class="item">
          <div class="left">
            <image src="../../static/image/Symbol.png" mode=""></image>
            <text>登録カード一覧</text>
          </div>
          <div class="right">
          </div>
        </view>
      </view>
      <view class="box" @click="setting()" hover-class="bk">
        <view class="item">
          <div class="left">
            <image src="../../static/image/path.png" mode=""></image>
            <text>通知設定</text>
          </div>
          <div class="right">
            <text v-if="is_notice == 1">ON</text>
            <text v-if="is_notice == 0">OFF</text>
          </div>
        </view>
      </view>
      <view class="box" @click="wenQue()" hover-class="bk">
        <view class="item">
          <div class="left">
            <image src="../../static/image/wenhao.png" mode=""></image>
            <text>よくあるご質問</text>
          </div>
          <div class="right">
          </div>
        </view>
      </view>
      <view class="box" @click="notice()" hover-class="bk">
        <view class="item">
          <div class="left">
            <image src="../../static/image/Nerws.png" mode=""></image>
            <text>お知らせ</text>
          </div>
          <div class="right">
            <text class="bg" v-text="number" v-if="number > 0"></text>
          </div>
        </view>
      </view>
    </view>
    <view class="itms" @click="rules()">
      <view class="tm">
        利用規約・プライバシーポリシー
      </view>
    </view>
    <view class="itms">
      <view class="tm">
        <view class="le">
          バージョン情報
        </view>
        <view class="ri" v-text="version">
          
        </view>
      </view>
    </view>
  </view>
</template>

<script>
    import {getUserInfos,editUser,getYYs,scanCode} from "@/api/index.js"
  export default {
    data() {
      return {
        is_notice:1,
        number:0,
        showalert:false,
        name:"",
        version:""
      };
    },
    onShow() { 
      let that = this
      that.getInfo()
      let shou = uni.getSystemInfoSync()
      console.log("show1",shou)
      that.version = shou.appWgtVersion + ' '+ shou.appVersionCode
    },
    methods: {
        logout(){
           uni.removeStorageSync("token")
           uni.redirectTo({
               url:"/pages/login/login"
           })
        },
        
        getInfo(){
            let that = this
            getUserInfos().then((res) =>{
                that.name = res.data.user.name
            })
            getYYs().then((res) => {
                if(res.code == 200){
                    that.number = res.data.unread_count
                }
            })
        },
        sao() {
          let that = this
          uni.scanCode({
            success: function(res) {
                let result = JSON.parse(res.result)
                uni.showLoading({
                    title:"加载中"
                })
                if(result.id){
                    scanCode(result.id).then((res) => {
                        uni.hideLoading()
                        if(res.code==200){
                            if(res.data.member == 0){
                                uni.navigateTo({
                                    url:"../../pagesA/BindCard/BindCard?id="+res.data.cid
                                })
                            }else{
                                uni.navigateTo({
                                    url:"../../pagesA/shop/shop?id="+result.id+"&cid="+res.data.mid
                                })
                            }
                        }
                    })
                }
            }
          })
        },
      setting() {
        uni.navigateTo({
          url: '../../pagesA/setting/setting?status='+this.is_notice
        })
      },
      saoMa() {
        let that = this
        uni.scanCode({
            onlyFromCamera:true,
          success: function(res) {
              let result = JSON.parse(res.result)
              uni.showLoading({
                  title:"読み込み中"
              })
              if(result.id){
                  scanCode(result.id).then((res) => {
                      uni.hideLoading()
                      if(res.code==200){
                          if(res.data.member == 0){
                              uni.navigateTo({
                                  url:"../../pagesA/BindCard/BindCard?id="+res.data.cid
                              })
                          }else{
                              uni.navigateTo({
                                  url:"../../pagesA/shop/shop?id="+result.id+"&cid="+res.data.mid
                              })
                          }
                      }
                  })
              }
          }
        })
      },
      notice() {
        uni.navigateTo({
          url: '../../pagesA/notice/notice'
        })
      },
      rules() {
        uni.navigateTo({
          url: '../../pagesA/privacyPolicy/privacyPolicy'
        })
      },
      wenQue() {
        uni.navigateTo({
          url: '../../pagesA/commonProblem/commonProblem'
        })
      },
      rightClick() {
        uni.navigateBack()
      },
      addCard() {
        uni.navigateTo({
          url: '../../pagesA/addCard/addCard'
        })
      },
      modify() {
        uni.navigateTo({
          url: '../../pagesA/ModifyInformation/ModifyInformation'
        })
      },
    }
  }
</script>

<style lang="scss" scoped>
  .bg {
    height: 100vh;
    background-color: #F6F5F8;
  }
  
  .bk{
      background: rgba(29, 29, 31, 0.05) !important
  }

  .u-nav-slot {
    font-size: 40rpx;
    color: #1D1D1F;
  }

  .big-box {
    margin-top: 60rpx;
  }

  .itms {

    margin-top: 112rpx;
    background-color: #fff;
    border-bottom: 1px solid #D5D5D5;
    border-top: 1px solid #D5D5D5;

    .tm {
      width: 90%;
      margin: 0 auto;
      height: 112rpx;
      font-size: 32rpx;
      color: #1D1D1F;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  }

  .box {

    background-color: #fff;
    border-bottom: 1px solid #D5D5D5;

    .item {
      width: 90%;
      height: 112rpx;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;

      .left {
        image {
          width: 44rpx;
          height: 44rpx;
          vertical-align: middle;
        }

        text {
          margin-left: 26rpx;
          font-size: 32rpx;
          color: #1D1D1F;
          vertical-align: middle;
        }
      }

      .right {
        image {
          width: 52rpx;
          height: 52rpx;
        }

        text {
          font-size: 32rpx;
          color: #86868B;
        }

        .bg {
          display: inline-block;
          width: 52rpx;
          height: 52rpx;
          background: #1A73E8;
          font-size: 32rpx;
          text-align: center;
          line-height: 52rpx;
          border-radius: 50%;
          color: #FFFFFF;
        }
      }
    }
  }
</style>
