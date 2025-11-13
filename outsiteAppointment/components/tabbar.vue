<template>
  <view>
    <u-tabbar :value="value" @change="change" :fixed="true" :placeholder="true" activeColor="#1A73E8"
      inactiveColor="#707070" :safeAreaInsetBottom="true">
      <u-tabbar-item text="My Card">
        <image class="u-page__item__slot-icon" slot="active-icon" src="../static/svg/s1.svg"></image>
        <image class="u-page__item__slot-icon" slot="inactive-icon" src="../static/svg/s1.svg">
        </image>
      </u-tabbar-item>
      <u-tabbar-item text="店舗情報">
        <image class="u-page__item__slot-icon2" slot="active-icon" src="../static/svg/s2.svg"></image>
        <image class="u-page__item__slot-icon2" slot="inactive-icon" src="../static/svg/s2.svg">
        </image>
      </u-tabbar-item>
      <u-tabbar-item text="お知らせ">
        <image class="u-page__item__slot-icon3" slot="active-icon" src="../static/svg/s3.svg"></image>
        <image class="u-page__item__slot-icon3" slot="inactive-icon" src="../static/svg/s3.svg">
        </image>
      </u-tabbar-item>
      <u-tabbar-item text="メッセージ">
        <image class="u-page__item__slot-icon4" slot="active-icon" src="../static/svg/s4.svg"></image>
        <image class="u-page__item__slot-icon4" slot="inactive-icon" src="../static/svg/s4.svg">
        </image>
      </u-tabbar-item>
      <u-tabbar-item text="メニュー">
        <image class="u-page__item__slot-icon1" slot="active-icon" src="../static/svg/s5.svg"></image>
        <image class="u-page__item__slot-icon1" slot="inactive-icon" src="../static/svg/s5.svg">
        </image>
      </u-tabbar-item>
      <u-tabbar-item text="123">
        <image class="u-page__item__slot-icon1" slot="active-icon" src="../static/svg/s6.svg"></image>
        <image class="u-page__item__slot-icon1" slot="inactive-icon" src="../static/svg/s6.svg">
        </image>
      </u-tabbar-item>
    </u-tabbar>
  </view>
</template>

<script>
  export default {
    name: "tabbar",
    props: {
      value: Number,
    },
    data() {
      return {

      };
    },
    methods: {
      change(name) {
        console.log(name)
        if (name === 0) {
          uni.navigateTo({
            url: '../../pagesA/shopDetails/shopDetails'
          })

        } else if (name === 1) {
          uni.navigateTo({
            url: '../../pagesA/storeInformation/storeInformation'
          })
        } else if (name === 2) {
          uni.navigateTo({
            url: '../../pagesA/notification/notification'
          })
        } else if (name === 3) {
          return
        } else if (name == 4) {
          uni.navigateTo({
            url: '../../pagesA/menu/menu'
          })
        }


      }
    }
  }
</script>

<style lang="scss" scoped>
  .u-page__item__slot-icon {
    width: 68rpx;
    height: 49rpx;
  }

  .u-page__item__slot-icon1 {
    width: 52rpx;
    height: 52rpx;
  }

  .u-page__item__slot-icon2 {
    width: 33rpx;
    height: 46rpx;
  }

  .u-page__item__slot-icon3 {
    width: 49rpx;
    height: 54rpx;
  }

  .u-page__item__slot-icon4 {
    width: 60rpx;
    height: 51rpx;
  }

  ::v-deep .u-tabbar__content {
    height: 164rpx;
  }

  ::v-deep .u-tabbar__content__item-wrapper {
    padding-top: 20rpx;
  }
</style>
