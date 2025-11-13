<template>
  <view class="bbs">
    <u-navbar title="予約完了" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56" :border="true"
      leftIcon="" @leftClick="leftClick" :titleWidth="484">
    </u-navbar>
    <view class="container">
      <view class="con">
        <view class="con-title">
          {{query.name}}　さん
        </view>
        <view class="con-green">
          ありがとうございます
          ご予約が完了しました
        </view>
      </view>
      <view class="con-i">
        <view class="con-item">
          <view class="con-left">
            予約番号
          </view>
          <view class="con-right" v-text="query.order_no"></view>
        </view>
        <view class="con-item">
          <view class="con-left">
            来店日時
          </view>
          <view class="con-right" v-text="query.start_time"></view>
        </view>
        <view class="con-item">
          <view class="con-left">
            メニュー
          </view>
          <view class="con-right">
            <view class="" v-text="query.menu_name"></view>
            <view class="red" v-if="query.amount" v-text="`¥${parseFormatNum(query.amount,0)}`"></view>
          </view>
        </view>
        <!--  -->
        <view class="con-item">
          <view class="con-left">
            担当者
          </view>
          <view class="con-right" v-text="query.appoint_name == ''?'指名なし':query.appoint_name"> </view>
        </view>
        <view class="con-item" v-if="query.appoint_amount != 0">
          <view class="con-left">
            指名料
          </view>
          <view class="con-right" v-text="`¥${query.appoint_amount}`" style="color: rgba(217, 48, 37, 0.65) !important;"> </view>
        </view>
        <view class="con-item">
          <view class="con-left">
            ご要望
          </view>
          <view class="con-right" v-text="query.remark == ''?'-':query.remark"></view>
        </view>
      </view>
      <view class="con-bot">
        <view class="img">
          <image src="../../static/image/message.png" mode=""></image>
        </view>
        <view class="con-text">
          メッセージに予約内容を送信しましたのでご確認ください。
        </view>
        <view class="con-btns" @click="back()">
          TOPに戻る
        </view>
      </view>
    </view>
  </view>
</template>

<script>
  export default {
    data() {
      return {
        query:{}
      };
    },
    created() {
     
    },
    onLoad(optiosn) {
        let that = this
        let query = optiosn
        that.query = query
    },
    methods: {
      leftClick() {
        uni.navigateBack()
      },
      back() {
        uni.reLaunch({
          url: '../../pages/index/index'
        })
      },
      formatDate(ms) {
          const date = new Date(ms)
          
      },
      getDate(e){
        let that = this
        let date = new Date(e*1000)
        let week = date.getDay() == 0 ? '日' : date.getDay() == 1 ? '月' : date.getDay() == 2 ? '水' : date.getDay() == 3 ? '火':date.getDay() == 4 ? '木':date.getDay() == 5 ? '金':'土'
       let Y = date.getFullYear()
       let M = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
       let D = date.getDate()
       return `${Y}年${M}月${D}日 `+" ("+week+")"
      },
    }
  }
</script>

<style lang="scss" scoped>
  ::v-deep .u-navbar__content__title {
    font-size: 40rpx;
    color: #1D1D1F;
  }

  .bbs {
    background-color: #F5F5F7;
    width: 100%;
    height: 100vh;
  }
  

  .container {
    width: 90%;
    margin: 32rpx auto;
    // height: 1272px;
    background: #FFFFFF;
    box-shadow: 0px 0px 12rpx 2rpx rgba(0, 0, 0, 0.16);
    border-radius: 20rpx;

    .con {
      padding-bottom: 80rpx;
      border-bottom: 1px solid #D2D2D7;
    }

    .con-title {
      padding-top: 80rpx;
      text-align: center;
      font-size: 48rpx;
      color: #1D1D1F;
      font-weight: 700;
      margin-bottom: 80rpx;
    }

    .con-green {
      width: 360rpx;
      margin: 0 auto;
      text-align: center;
      color: #53B1AF;
      font-size: 36rpx;
    }

    .con-i {
      margin-top: 40rpx;
      border-bottom: 1px solid #D2D2D7;
      box-sizing: border-box;
      padding: 0 30rpx;
      padding-bottom: 58rpx;
      .con-item {
        display: flex;
        margin-bottom: 20rpx;
        font-size: 28rpx;

        .con-left {
          width: 136rpx;
          flex-shrink: 0;
          color: #707070;
          margin-right: 10rpx;
        }

        .con-right {
          color: #1D1D1F;

          .red {
            margin-top: 10rpx;
            color: rgba(217, 48, 37, 0.65) !important;
          }
        }
      }
    }
  }

  .con-bot {
    margin-top: 72rpx;
    width: 100%;
    padding-bottom: 80rpx;

    .img {
      width: 100%;
      text-align: center;
      margin-bottom: 32rpx;

      image {
        width: 84rpx;
        height: 71rpx;
      }
    }

    .con-text {
      font-size: 32rpx;
      text-align: center;
      color: #1D1D1F;
      margin-bottom: 60rpx;
    }

    .con-btns {
      margin: 0 auto;
      width: 232rpx;
      height: 96rpx;
      border-radius: 20rpx;
      line-height: 96rpx;
      text-align: center;
      font-weight: 700;
      background: #D2D2D7;
      font-size: 32rpx;
      color: #1D1D1F;
    }
  }
</style>
