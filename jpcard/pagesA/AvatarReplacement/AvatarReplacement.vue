<template>
  <view>
    <u-navbar :title="query.name" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="52" :border="true"
      @leftClick="leftClick">
    </u-navbar>
    <view class="boxs">
      <image :src="query.image" mode="aspectFill" @click="bigImg = true"></image>
      <view class="uname">
        {{query.name}}<text>さん</text>
      </view>
      <view class="id">
        NO:{{query.id}}
      </view>
    </view>
    <view class="fixed" v-if="bigImg">
      <view class="zhezhao"></view>
      <view class="fle">
        <u-icon name="close" color="#FFFFFF" size="16" @click="bigImg = false"></u-icon>
        <view class="creatm" @click="show = true">
          <image src="../../static/image/camera.png" mode=""></image> <text>編集</text>
        </view>
      </view>
      <view class="bo">
        <image :src="query.image"  mode="aspectFit"></image>
      </view>
    </view>
    <u-popup :show="show" @close="close">
      <view class="h">
        <view class="boxsf">
          <view class="itms" @click="camera()">
            カメラで撮影
          </view>
        </view>
        <view class="boxsf">
          <view class="itms" @click="chooseImage">
            写真を選択
          </view>
        </view>
        <view class="boxsf  border-bot">
          <view class="itms red" @click="deleteAvatar">
            プロフィール画像を削除
          </view>
        </view>
        <view class="choose-btn" @click="show =false">
          キャンセル
        </view>
      </view>
    </u-popup>
  </view>
</template>

<script>
  import {editUser} from "@/api/index.js"
  export default {
    data() {
      return {
        bigImg: false,
        show: false,
        query:{}
      };
    },
    created() {
      
    },
    onLoad(options) {
       let that = this
         let query = options
         that.query = query 
    },
    methods: {
      close() {
        this.show = false
      },
      deleteAvatar(){
         let that = this
         let data = {}
          data["_method"] = "put"
          data["type"] = "avatar"
          data["avatar"] = ""
         editUser(data,that.query.id).then((res) => {
             if(res.code == 200){
                 uni.showToast({
                     title:"削除しました",
                     duration:2000
                 })
                 setTimeout(() => {
                     uni.navigateBack()
                 },2000)
             }else{
                 uni.showToast({
                     title:res.message,
                     icon:"none"
                 })
             }
         })
      },
      camera() {
          let that = this
        uni.chooseImage({
          count: 1, //默认9
          sizeType: ['original', 'compressed'], //可以指定是原图还是压缩图，默认二者都有
          sourceType: ['camera'], //从相册选择
          success: function(res) {
            const tempPaths = res.tempFilePaths
            uni.uploadFile({
                url: that.$baseUrl+'/tool/upload/file',
                filePath: res.tempFilePaths[0],
                name:"file",
                success: (result) => {
                    if (result.errMsg == "uploadFile:ok") {
                        let re = JSON.parse(result.data)
                        if (re.code == 200) {
                            let data = {}
                             data["_method"] = "put"
                             data["type"] = "avatar"
                             data["avatar"] = re.data.path
                            editUser(data,that.query.id).then((resy) => {
                                if(resy.code == 200){
                                    uni.showToast({
                                        title:resy.message
                                    })
                                    that.query.image = re.data.path
                                    that.show = false
                                }else{
                                    uni.showToast({
                                        title:resy.message,
                                        icon:"none"
                                    })
                                }
                            })
                        }
                    }
                }
            })
          }
        });
      },
      chooseImage() {
          let that = this
        uni.chooseImage({
          count: 1, //默认9
          sizeType: ['original', 'compressed'], //可以指定是原图还是压缩图，默认二者都有
          sourceType: ['album'], //从相册选择
          success: function(res) {
            const tempPaths = res.tempFilePaths
            uni.uploadFile({
                url: that.$baseUrl+'/tool/upload/file',
                filePath: res.tempFilePaths[0],
                name:"file",
                success: (result) => {
                    if (result.errMsg == "uploadFile:ok") {
                        let re = JSON.parse(result.data)
                        if (re.code == 200) {
                            let data = {}
                             data["_method"] = "put"
                             data["type"] = "avatar"
                             data["avatar"] = re.data.path
                            editUser(data,that.query.id).then((resy) => {
                                if(resy.code == 200){
                                    uni.showToast({
                                        title:resy.message
                                    })
                                    that.query.image = re.data.path
                                    that.show = false
                                }else{
                                    uni.showToast({
                                        title:resy.message,
                                        icon:"none"
                                    })
                                }
                            })
                        }
                    }
                }
            })
          }
        });
      },
      leftClick() {
        let pages = getCurrentPages();
        let currPage = pages[pages.length - 1] //当前页面
        let prePage = pages[pages.length - 2] //上一个页面
        //调用上一页拉取数据的方法
        if (prePage.route == "pagesA/ModifyInformation/ModifyInformation") {
            prePage.$vm.getInfo()
        }
        uni.navigateBack({})
      }
    }
  }
</script>

<style lang="scss" scoped>
  .boxs {
    margin: 0 auto;
    margin-top: 182rpx;
    text-align: center;
    font-size: 48rpx;
    color: #1D1D1F;

    image {
      width: 233rpx;
      height: 237rpx;
      border-radius: 50%;
      margin-bottom: 60rpx;
    }

    .uname {

      font-weight: 700;


      text {
        margin-left: 28rpx;
      }
    }

    .id {
      margin-top: 32rpx;
    }
  }

  .fixed {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 99;
    width: 100%;
    height: 100vh;

    .zhezhao {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: #333333;
    }

    .fle {
      position: absolute;
      top: 106rpx;
      left: 38rpx;
      width: 90%;
      display: flex;
      justify-content: space-between;
      align-items: center;

      .creatm {
        width: 148rpx;
        height: 68rpx;
        display: flex;
        justify-content: space-around;
        align-items: center;
        border-radius: 8rpx;
        border: 2rpx solid #FFFFFF;
        text-align: center;

        text {
          font-size: 28rpx;
          color: #FFFFFF;

        }

        image {
          width: 33rpx;
          height: 28rpx;

        }
      }
    }

    .bo {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 100%;
      height: 560rpx;

      image {
        width: 100%;
        height: 100%;
      }
    }
  }

  .h {
    // height: 586rpx;
    background-color: #F0F0F0;

    .border-bot {
      border-bottom: 0 !important;
    }

    .boxsf {

      border-bottom: 1px solid #BDBDBD;

      ::v-deep .u-upload__wrap {
        justify-content: space-around;
      }

      .itms {
        width: 100%;
        height: 112rpx;
        line-height: 112rpx;
        text-align: center;
        font-size: 40rpx;
        color: #007AFF;
        width: 90%;
        margin: 0 auto;

      }

      .red {
        color: #D93025;
      }
    }

    .choose-btn {
      width: 100%;
      height: 112rpx;
      margin-top: 18rpx;
      line-height: 112rpx;
      background: #FFFFFF;
      border-radius: 28rpx;
      text-align: center;
      font-size: 40rpx;
      color: #1A73E8;
    }
  }
</style>
