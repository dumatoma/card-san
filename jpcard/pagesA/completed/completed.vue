<template>
  <view class="bbs">
    <u-navbar title="予約完了" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56" :border="true"
      leftIcon="" @leftClick="leftClick" :titleWidth="484">
    </u-navbar>
    <!-- [カスタムエイド 2026-10-22] 予約完了を見やすく(白いカード・大きめの文字)。「ありがとうございます」は予約内容確認のボタンと同じ濃い緑・太字 -->
    <view class="rc-page rd-page">
      <view class="rd-hero">
        <view class="rd-ok">✓</view>
        <view class="rd-name">{{query.name}}<text class="rd-san">さん</text></view>
        <view class="rd-thanks">ありがとうございます<br />ご予約が完了しました</view>
      </view>

      <view class="rc-sec">ご予約の内容</view>
      <view class="rc-card">
        <view class="rc-row">
          <view class="rc-lab">予約番号</view>
          <view class="rc-val">{{ query.order_no }}</view>
        </view>
        <view class="rc-row">
          <view class="rc-lab">来店日時</view>
          <view class="rc-val rc-strong">{{ query.start_time }}</view>
        </view>
        <view class="rc-row">
          <view class="rc-lab">メニュー</view>
          <view class="rc-val rc-menu">
            <view class="rc-menu-m">{{ query.menu_name }}<text class="rc-price" v-if="amountText">{{ amountText }}</text></view>
            <image class="rc-photo" v-if="photo" :src="photo" mode="aspectFill"></image>
          </view>
        </view>
        <view class="rc-row">
          <view class="rc-lab">担当者</view>
          <view class="rc-val">{{ query.appoint_name == '' || query.appoint_name == 'undefined' ? '指名なし' : query.appoint_name }}</view>
        </view>
        <view class="rc-row" v-if="query.appoint_amount && query.appoint_amount != 0">
          <view class="rc-lab">指名料</view>
          <view class="rc-val rc-red">¥{{ query.appoint_amount }}</view>
        </view>
        <view class="rc-row">
          <view class="rc-lab">ご要望</view>
          <view class="rc-val" :class="{ 'rc-empty': !query.remark }">{{ query.remark ? query.remark : 'なし' }}</view>
        </view>
      </view>

      <view class="rc-card rc-pad rd-msg">
        <image src="../../static/image/message.png" mode="aspectFit"></image>
        <view class="rd-msg-t">アプリのメッセージに予約内容を送信しましたのでご確認ください。</view><!-- [カスタムエイド 2026-10-22] どこに届いたか分かるよう「アプリの」を付けた -->
      </view>
      <view class="rd-top" hover-class="rc-btn-h" @click="back()">TOPに戻る</view>
    </view>
  </view>
</template>

<script>
  export default {
    data() {
      return {
        query:{},
        photo: '' // [カスタムエイド 2026-10-22] メニューの1枚目の写真(予約内容確認から受け取る)
      };
    },
    created() {
     
    },
    onLoad(optiosn) {
        let that = this
        let query = optiosn
        that.query = query
        try { that.photo = uni.getStorageSync("lastOrderImage") || '' } catch (e) {}
    },
    computed: {
      // [カスタムエイド 2026-10-22] 金額はお店が書いたまま(数字だけのときは ¥ とカンマを付ける。以前は「5,000〜」も ¥5,000 になっていた)
      amountText() {
        let a = this.query.amount
        if (!a || a == 'undefined' || a == 'null' || a == '0') return ''
        try { a = decodeURIComponent(String(a)) } catch (e) { a = String(a) }
        // 数字(とカンマ)だけのときは ¥ とカンマを付ける。「円」「〜」などが付いているときは書いたまま
        if (/^[\d,]+$/.test(a)) {
          a = a.replace(/,/g, '')
          return '¥' + a.replace(/\B(?=(\d{3})+(?!\d))/g, ',')
        }
        return a
      }
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
    /* [カスタムエイド 2026-10-22] 予約内容確認(予約前の最終確認。見やすさを最優先:白いカード・大きめの文字・行の区切り)
       ボタン:必須がそろうまでは薄い色、そろったら濃い色(仕様は今までどおり)。色は以前(#53B1AF・薄い時は透明度0.6)より濃く */
    .rc-page { min-height: 100vh; background: #F5F5F7; padding-bottom: calc(260rpx + env(safe-area-inset-bottom)); }
    .rc-lead { padding: 48rpx 40rpx 24rpx; font-size: 34rpx; font-weight: 700; color: #1D1D1F; }
    .rc-lead-s { display: block; margin-top: 8rpx; font-size: 26rpx; font-weight: 400; color: #6E6E73; }
    .rc-sec { padding: 0 40rpx 16rpx; font-size: 26rpx; color: #6E6E73; }
    .rc-card { margin: 0 24rpx 48rpx; background: #FFFFFF; border-radius: 32rpx; padding: 0 32rpx; }
    .rc-pad { padding: 32rpx; }
    .rc-row { display: flex; align-items: flex-start; padding: 32rpx 0; border-bottom: 1px solid #E5E5EA; }
    .rc-row:last-child { border-bottom: 0; }
    .rc-lab { width: 160rpx; flex-shrink: 0; font-size: 28rpx; color: #6E6E73; line-height: 1.5; padding-top: 2rpx; }
    .rc-val { flex: 1; min-width: 0; font-size: 32rpx; color: #1D1D1F; line-height: 1.5; word-break: break-all; }
    .rc-strong { font-size: 36rpx; font-weight: 700; }
    .rc-price { display: block; margin-top: 8rpx; font-size: 30rpx; color: #D93025; font-weight: 600; }
    .rc-red { color: #D93025; font-weight: 600; }
    .rc-empty { color: #AEAEB2; }
    .rc-note { font-size: 30rpx; color: #1D1D1F; line-height: 1.7; }
    .rc-hint { font-size: 28rpx; color: #515154; line-height: 1.6; margin-bottom: 20rpx; }
    .rc-ta { width: 100%; height: 220rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 24rpx 28rpx; font-size: 30rpx; line-height: 1.6; box-sizing: border-box; background: #FFFFFF; display: block; }
    .rc-check { display: flex; align-items: center; padding: 36rpx 32rpx; }
    .rc-box { width: 48rpx; height: 48rpx; border-radius: 12rpx; border: 3rpx solid #8E8E93; box-sizing: border-box; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #FFFFFF; }
    .rc-box.on { background: #2B8A87; border-color: #2B8A87; }
    .rc-tick { color: #FFFFFF; font-size: 30rpx; font-weight: 700; line-height: 1; }
    .rc-check-t { margin-left: 24rpx; font-size: 32rpx; color: #1D1D1F; font-weight: 600; }
    .rc-check-s { display: block; margin-top: 4rpx; font-size: 24rpx; color: #6E6E73; font-weight: 400; }
    .rc-bar { position: fixed; left: 0; right: 0; bottom: 0; background: #FFFFFF; border-top: 1px solid #E5E5EA; padding: 24rpx 32rpx calc(32rpx + env(safe-area-inset-bottom)); z-index: 50; }
    .rc-btn { height: 108rpx; border-radius: 28rpx; background: #7FBDBA; color: #FFFFFF; font-size: 34rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; letter-spacing: 2rpx; }
    .rc-btn.on { background: #2B8A87; }
    .rc-btn-h { opacity: 0.85; }
    .rc-menu { display: flex; align-items: flex-start; }
    .rc-menu-m { flex: 1; min-width: 0; }
    .rc-photo { width: 128rpx; height: 128rpx; border-radius: 16rpx; margin-left: 20rpx; flex-shrink: 0; background: #F2F2F7; }
    .rc-req { color: #D93025; margin-right: 4rpx; }
    .rc-in { flex: 1; height: 88rpx; border: 1px solid #D2D2D7; border-radius: 20rpx; padding: 0 24rpx; font-size: 32rpx; box-sizing: border-box; background: #FFFFFF; }
    .rc-row-in { align-items: center; }

    /* [カスタムエイド 2026-10-22] 予約完了(見やすさを最優先)。「ありがとうございます」は予約内容確認のボタンと同じ濃い緑・太字 */
    .rd-hero { margin: 48rpx 24rpx 48rpx; background: #FFFFFF; border-radius: 32rpx; padding: 56rpx 32rpx 48rpx; text-align: center; }
    .rd-ok { width: 112rpx; height: 112rpx; border-radius: 50%; background: #2B8A87; color: #FFFFFF; font-size: 60rpx; font-weight: 700; line-height: 112rpx; margin: 0 auto 32rpx; }
    .rd-name { font-size: 44rpx; font-weight: 700; color: #1D1D1F; }
    .rd-san { font-size: 32rpx; font-weight: 400; margin-left: 12rpx; }
    .rd-thanks { margin-top: 24rpx; font-size: 36rpx; font-weight: 700; color: #2B8A87; line-height: 1.6; }
    .rd-msg { display: flex; align-items: center; }
    .rd-msg image { width: 64rpx; height: 54rpx; flex-shrink: 0; margin-right: 24rpx; }
    .rd-msg-t { font-size: 30rpx; color: #1D1D1F; line-height: 1.6; }
    .rd-page { padding-bottom: calc(80rpx + env(safe-area-inset-bottom)); }
    .rd-top { margin: 0 24rpx; height: 104rpx; border-radius: 28rpx; background: #FFFFFF; border: 1px solid #D2D2D7; color: #1D1D1F; font-size: 32rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; box-sizing: border-box; }

  ::v-deep .u-navbar__content__title {
    font-size: 40rpx;
    color: #1D1D1F;
  }

  .bbs {
    background-color: #F5F5F7;
    width: 100%;
    min-height: 100vh; /* [カスタムエイド 2026-10-22] 中身が長いときに切れないように(以前は height) */
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
      padding-bottom: 58rpx;
      border-bottom: 1px solid #D2D2D7;

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
