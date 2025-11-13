<template>
  <view>
    <u-navbar title="登録カード一覧" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56" :border="true"
      @leftClick="leftClick">
    </u-navbar>
    <view class="boxs">
      <u-swipe-action>
        <u-swipe-action-item :options="options1" v-for="(item,index) in list" :key="index" @click="deleShow(index,item.id)">
          <view class="swipe-action u-border-bottom">
            <view class="swipe-action__content" @click="toDetail(item.sid,item.id)">
              <view class="left">
                <image :src="item.image" mode="aspectFill"></image>
              </view>
              <view class="right" style="flex: 1;">
                {{item.shop_name}}
              </view>
              <u-icon name="arrow-right" color="#707070" size="30"></u-icon>
            </view>
          </view>
        </u-swipe-action-item>
      </u-swipe-action>
    </view>
    <u-popup :show="show1" mode="center" :safeAreaInsetBottom="false">
      <view>
        <view class="title">
          "{{shopName}}"のカードを削除しますか？
        </view>
        <view class="des">
          削除すると、このカードのデータは完全に削除されます。
        </view>
        <div class="btn red" @click="deleteItem">カードを削除</div>
        <div class="btn blue" @click="show1 = false">キャンセル</div>
      </view>
    </u-popup>
  </view>
</template>
<script>
  import{getUserCards,deleteCard} from "@/api/index.js"
  export default {
    data() {
      return {
        options1: [{
          text: '削除',
          style: {
            width: '160rpx',
            backgroundColor: '#D93025'
          }
        }],
        list: [],
        page:1,
        deleteIndex:0,
        show1:false,
        shopName:""
      };
    },
    created(){
      let that = this
      that.getUserCards()
    },
    methods: {
      leftClick() {
        uni.navigateBack()
      },
      getUserCards(){
        let that = this
        let page = that.page
        getUserCards(page).then((res) => {
            if(res.code == 200){
                that.list = res.data.members
            }
        })
      },
     toDetail(id,cid){ 
       uni.navigateTo({
           url:"../../pagesA/shop/shop?id="+id+"&cid="+cid
       })
     },
     deleShow(i,id) {
        this.show1 = true
       // this.list.forEach((item, index) => {
       //   if (index == i) {
       //     this.$set(this.list[i], 'check', true)
       //   } else {
       //     this.$set(item, 'check', false)
       //   }
       // })
       this.deleteIndex = id
       this.shopName = this.list[i].shop_name
     },
     deleteItem(){
       let that = this  
       deleteCard(that.deleteIndex).then((res) => {
           that.show = false
           if(res.code == 200){
               uni.showToast({
                   title:res.message
               })
               that.getList()
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

  .swipe-action__content {
    width: 90%;
    margin: 0 auto;
    display: flex;
    align-items: center;
    height: 212rpx;

    .left {
      margin-right: 20rpx;

      image {
        width: 200rpx;
        height: 120rpx;
        border-radius: 10rpx;
        box-shadow: 0px 0px 10rpx 2rpx rgba(0, 0, 0, 0.2);
      }
    }

    .right {
      margin-right: 58rpx;
      color: #1D1D1F;
      font-size: 28rpx;
    }
  }

  ::v-deep .u-swipe-action-item__right__button__wrapper {
    width: 160rpx !important;

  }
  
  ::v-deep .u-popup__content {
    width: 80%;
    border-radius: 28rpx;
    background-color: #fff;
    overflow: hidden;
  
    .title {
      padding: 40rpx 34rpx 0 34rpx;
      font-size: 32rpx;
      color: #1D1D1F;
      text-align: center;
    }
  
    .des {
      padding: 0rpx 34rpx 0 34rpx;
      margin-top: 40rpx;
      margin-bottom: 60rpx;
      font-size: 28rpx;
      color: #1D1D1F;
      text-align: center;
    }
  
    .btn {
      border-top: 1px solid #86858A;
      height: 88rpx;
      line-height: 88rpx;
      text-align: center;
      font-size: 32rpx;
      background-color: #fff;
  
      &:active {
        opacity: 0.7;
      }
    }
  
    .red {
      color: #D93025;
    }
  
    .blue {
      color: #007AFF;
    }
  }
</style>
