<template>
  <view>
    <u-navbar title="よくあるご質問" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56" :border="true"
      @leftClick="leftClick">
    </u-navbar>
    <u-collapse @change="change" @close="close" @open="open">
      <u-collapse-item title="CARD-SANとは？" name="Docs guide">
        <text class="u-collapse-content">会員カードアプリです。加盟店のスタンプカードや会員証、をこのアプリ一つで管理することができます。加盟店の情報や特典、連絡ツールとしてもご利用頂けます。また従来の紙やプラスティクのカードをアプリ化することで、無駄な資源利用を減らし、環境にも優しいサステナブルなツールとしてご利用頂ければ幸いです。</text>
      </u-collapse-item>
      <u-collapse-item title="店舗カードを取得するには？" name="Variety components">
        <text
          class="u-collapse-content">トップページからQRアクセス機能で、各店舗のアクセス用QRコードを読み取ってください。もしくは店舗名からも検索ができます。その後、対象店舗の情報が表示されますので、「カードを入手」を選択すれば、トップページに店舗カードのアイコンが表示されます。</text>
      </u-collapse-item>
      <u-collapse-item title="スタンプ、ポイントを貯める" name="Numerous tools">
        <text class="u-collapse-content">店舗ページの「MyCard」ページに表示されているQRコードもしくはバーコードをお店のスタッフに提示してコードを読み取ってもらってください。お店側からスタンプもしくはポイントの付与が行われます。付与されたスタンプやポイントは、MyCardのスタンプ数やポイント数に反映されます。</text>
      </u-collapse-item>
      <u-collapse-item title="ご登録情報を変更するには？" name="Numerous tools">
        <text class="u-collapse-content">トップページメニューの「アカウント情報」から、メールアドレス、パスワード、プロフィールの変更が可能です。</text>
      </u-collapse-item>
      <u-collapse-item title="プッシュ通知のオン/オフについて" name="Numerous tools">
        <text class="u-collapse-content">メニューの「通知設定」から設定できます。またはiPhoneの場合は設定＞通知から、Androidの場合は設定＞アプリと通知から設定してください。</text>
      </u-collapse-item>
      <u-collapse-item title="スマートフォンの機種変更、紛失や故障による買い替えの場合" name="Numerous tools">
        <text class="u-collapse-content">アカウントを作成していただければ、引継ぎ設定不要で新しい端末にて引き続きご利用いただけます。新たにアプリをダウンロードの上、アカウント作成時の任意の方法でログインしてください。ただしアカウントを作成していない場合は、保存されていたデータは復旧出来ませんので、何卒ご注意ください。</text>
      </u-collapse-item>
      
      <u-collapse-item title="ご利用料金について" name="Numerous tools">
        <text class="u-collapse-content">CARD-SANアプリのご利用料金は無料ですのでご安心ください。ただし通信費はお客様のご負担となりますので、予めご了承願います。※通信費はご契約の携帯電話会社、ご利用状況により内容が異なりますので、詳細については携帯電話会社へお問い合わせください。</text>
      </u-collapse-item>
      
      <u-collapse-item title="ネットワークへの接続について" name="Numerous tools">
        <text class="u-collapse-content">CARD-SANアプリは5G、4G、3GネットワークまたはWi-Fiを利用してインターネットに接続します。インターネットに接続ができない場合、ご利用いただけませんので予めご了承願います。</text>
      </u-collapse-item>
    </u-collapse>
  </view>
</template>

<script>
  export default {
    data() {
      return {

      };
    },
    methods: {
      leftClick() {
        uni.navigateBack()
      },
      open(e) {
      },
      close(e) {
      },
      change(e) {
      }
    }
  }
</script>

<style lang="scss" scoped>
  ::v-deep .u-navbar__content__title {
    font-size: 40rpx;
    color: #1D1D1F;
  }

  ::v-deep .u-cell__right-icon-wrap--down {
    transform: rotate(0deg);

  }

  ::v-deep .u-cell__right-icon-wrap--up {
    transform: rotate(90deg);

  }

  ::v-deep .u-cell__body {
    height: 108rpx;
  }

  ::v-deep .u-collapse-item__content__text {
    background-color: #F5F5F7;
    font-size: 28rpx;
    color: #1D1D1F;
  }

  ::v-deep .u-icon__icon--info {
    color: #707070;
    font-size: 56rpx !important;
  }

  ::v-deep .u-cell__title-text {
    font-size: 32rpx;
    color: #1D1D1F;
  }
</style>
