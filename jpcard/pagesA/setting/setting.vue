<template>
  <view>
    <u-navbar title="通知設定" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56" :border="true"
      @leftClick="leftClick">
    </u-navbar>
    <view class="box">
      <view class="fle">
        <view class="title">
          通知を許可
        </view>
        <u-switch v-model="value" activeColor="#5ac725" @change="change"></u-switch>
      </view>
    </view>
  </view>
</template>

<script>
  import {editUser} from "@/api/index.js"
  export default {
    data() {
      return {
        value: '',
        query:{}
      };
    },
    created() {
      
    },
    onLoad(options) {
        let that = this
        that.query = options
        if(that.query.status == 1){
            that.value = true
        }else{
             that.value = false
        }
    },
    methods: {
      leftClick() {
        let pages = getCurrentPages();
        let currPage = pages[pages.length - 1] //当前页面
        let prePage = pages[pages.length - 2] //上一个页面
        //调用上一页拉取数据的方法
        if (prePage.route == "pagesA/accountInformation/accountInformation") {
            prePage.$vm.getInfo()
        }
        uni.navigateBack({})
      },
      change(e) {
        let data = {}
        data.type = "notice"
        data.notice = e == true? 1 : 0
        data._method = "put"
        let id = uni.getStorageSync("user").id
        editUser(data,id).then((res) => {
            if(res.code == 200){
                uni.showToast({
                    title:res.message
                })
            }else{
                uni.showToast({
                    title:res.message,
                    icon:"none"
                })
            }
        })
      },
    }
  }
</script>

<style lang="scss" scoped>
  ::v-deep .u-navbar__content__title {
    font-size: 40rpx;
    color: #1D1D1F;
  }

  .box {
    border-top: 1px solid #D0D0D0;
    border-bottom: 1px solid #D0D0D0;
  }

  .fle {
    width: 90%;
    margin: 0 auto;
    height: 112rpx;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #FFFFFF;

    .title {
      font-size: 32rpx;
      color: #1D1D1F;
    }
  }
</style>
