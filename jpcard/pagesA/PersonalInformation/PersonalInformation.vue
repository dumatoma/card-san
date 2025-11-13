<template>
  <view>
    <u-navbar title="お客様情報" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56" :border="true"
      @leftClick="leftClick">
    </u-navbar>
    <view class="con">
      <view class="user-box">
        <image :src="avatar == ''? '../../static/image/avatar.png' : avatar" mode="aspectFill" @click="uploadImage"></image>
        <view class="name">
          NO:{{info.member_id}}
        </view>
      </view>
      <view class="edit-right" @click="shows = true">
        編集する
      </view>
      <view class="left-box">
        <view class="item" v-if="configs.includes('name')">
          <view class="name">
            氏名
          </view>
          <view class="i-txt" v-text="info.name"></view>
        </view>
        <view class="item" v-if="configs.includes('pronunciation')">
          <view class="name">
            カナ
          </view>
          <view class="i-txt" v-text="info.pronunciation"></view>
        </view>
        <view class="item" v-if="configs.includes('phone')">
          <view class="name">
            電話番号
          </view>
          <view class="i-txt" v-text="info.phone"></view>
        </view>
        <view class="item" v-if="configs.includes('sex')">
          <view class="name">
            性別
          </view>
          <view class="i-txt" v-text="info.sex == 1? '男性' : info.sex == 2? '女性' : 'Xジェンダー' "></view>
        </view>
        <view class="item" v-if="configs.includes('birthday')">
          <view class="name">
            生年月日
          </view>
          <view class="i-txt">
            {{info.birthday_year}}年　{{info.birthday_month}}月　{{info.birthday_day}}日
          </view>
        </view>
        <view class="item" v-if="configs.includes('address')">
          <view class="name">
            住所
          </view>
          <view class="i-txt">
            <view class="" v-text="info.zip_code"></view>
            <view class="top" v-text="info.province"></view>
            <view class="top" v-text="info.address1"></view>
            <view class="top" v-text="info.address2"></view>
          </view>
        </view>
      </view>
    </view>
    <u-popup :show="shows" :round="16" mode="bottom" @close="close">
        <view style="height: 90vh;">
            <view class="tit-box">
              <view class="title">
                <view class="left" @click="shows = false">
                  <image src="../../static/image/close.png" mode=""></image>
                </view>
                <view class="content">
                  お客様情報の編集
                </view>
                <view class="keep" @click="sub">
                  保存
                </view>
              </view>
            </view>
            <view class="con">
              <view class="modify-user">
                <u--form labelPosition="top" ref="form1" :labelWidth="200">
                  <u-form-item label="氏名" prop="userInfo.name" ref="item1" v-if="configs.includes('name')">
                    <u--input v-model="info1.name"></u--input>
                  </u-form-item>
                  <u-form-item label="カナ" prop="userInfo.name" ref="item1" v-if="configs.includes('pronunciation')">
                    <u--input v-model="info1.pronunciation"></u--input>
                  </u-form-item>
                  <u-form-item label="電話番号" prop="userInfo.tel" ref="item1" v-if="configs.includes('phone')">
                    <u--input v-model="info1.phone" type="number" maxlength="11"></u--input>
                  </u-form-item>
                  <u-form-item label="性別" prop="userInfo.sex" @click="showSex = true; hideKeyboard()" ref="item1" v-if="configs.includes('sex')">
                    <u--input v-model="info1.sex == 1? '男性' : info.sex == 2? '女性' : 'Xジェンダー' " disabled disabledColor="#F6F5F8" placeholder="選択"
                      placeholderStyle=" color:#AAAAAA ;" suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB">
            
                    </u--input>
                    <view class="" style=" width: 40%;"></view>
                  </u-form-item>
                  <u-form-item label="生年月日" prop="userInfo.time" @click="show = true; hideKeyboard()" ref="item1" v-if="configs.includes('birthday')">
                    <u--input v-model="info1.birthday_year" disabled disabledColor="#F6F5F8" placeholder="年"
                      suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB"
                      placeholderStyle=" text-align: center ; color:#AAAAAA ;" inputAlign="center">
                    </u--input>
                    <view style="width: 5%;"></view>
                    <u--input v-model="info1.birthday_month" disabled disabledColor="#F6F5F8" placeholder="月"
                      suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB"
                      placeholderStyle=" text-align: center;  color:#AAAAAA ;" inputAlign="center">
                    </u--input>
                    <view style="width: 5%;"></view>
                    <u--input v-model="info1.birthday_day" disabled disabledColor="#F6F5F8" placeholder="日"
                      suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB"
                      placeholderStyle=" text-align: center;  color:#AAAAAA ;" inputAlign="center">
                    </u--input>
                  </u-form-item>
                  <u-form-item label="郵便番号" prop="userInfo.addSn" ref="item1" v-if="configs.includes('address')">
                    <u--input v-model="info1.zip_code" placeholder="ハイフンなし"></u--input>
                    <view style="width: 10%;"></view>
                    <view class="bns" @click="searchAddress">
                      住所検索
                    </view>
                  </u-form-item>
                  <u-form-item prop="userInfo.addSn" ref="item1" v-if="configs.includes('address')">
                    <u--input v-model="info1.province" disabled disabledColor="#EAEAEA " placeholder="都道府県"
                      suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB" placeholderStyle=" color:#868688  ;">
                    </u--input>
                    <view style="width: 40%;"></view>
                  </u-form-item>
                  <u-form-item label="住所1" prop="userInfo.address1" ref="item1" v-if="configs.includes('address')">
                    <u--input v-model="info1.address1" placeholder="市区町村、番地"></u--input>
                  </u-form-item>
                  <u-form-item label="住所2" prop="userInfo.address2" ref="item1" v-if="configs.includes('address')">
                    <u--input v-model="info1.address2" placeholder="建物名、部屋番号"></u--input>
                  </u-form-item>
                  <view style="height: 50upx;"></view>
                </u--form>
                <u-action-sheet :show="showSex" :actions="actions" title="性別を選択してください" @close="showSex = false" @select="sexSelect">
                </u-action-sheet>
                <u-datetime-picker :show="show" :minDate="-30000000000" v-model="value1" mode="date" @confirm="timeConfirm($event)"
                  @cancel="cancel()">
                </u-datetime-picker>
                <u-modal :show="keepShow" :title="title" :content='content' confirmText="ok" @confirm="keepShow = false">
                </u-modal>
              </view>
            </view>
        </view>
    </u-popup>
    <u-picker :closeOnClickOverlay="true" @close="showAdd = false" cancelText="キャンセル" confirmText="保存"
        @confirm="confirm" @cancel="showAdd = false" :show="showAdd" :columns="city"></u-picker>
  </view>
</template>

<script>
  import {getUserInfo,getAddress,getMyCard,getUserInfos,editUserInfo,getShopInfo} from "@/api/index.js"
  export default {
    data() {
      return {
        keepShow: false,
        shows: false,
        avatar:"",
        showAdd:false,
        show: false,
        value1: Number(new Date()),
        showSex: false,
        title: 'アカウント情報の更新',
        content: 'アカウント情報を更新しました',
        configs:[],
        actions: [{
            name: '男',
            id:1
          },
          {
            name: '女',
            id:2
          },
          {
            name: 'Xジェンダー',
            id:3
          },
        ],
        query:{},
        info:{},
        info1:{},
        city: [
            ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都',
                '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府',
                '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県',
                '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
            ]
        ],
      };
    },
    created() {
      
    },
    onLoad(options) {
        let that = this
        that.query = options
        that.getInfo()
        // getUserInfos().then((res) => {
        //     that.avatar = res.data.member.avatar
        // })
    },
    methods: {
      leftClick() {
        uni.navigateBack()
      },
      uploadImage() {
          let that = this
          uni.chooseImage({
              count: 9,
              success: (res) => {
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
                                       data["id"] = that.query.id
                                       data["_method"] = "put"
                                       data["type"] = "avatar"
                                       data["avatar"] = re.data.path
                                      editUserInfo(data).then((res) => {
                                          if(res.code == 200){
                                              uni.showToast({
                                                  title:res.message
                                              })
                                              that.avatar = re.data.path
                                          }else{
                                              uni.showToast({
                                                  title:res.message,
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
      close() {
        this.shows = false
      },
      sexSelect(e) {
        this.info1.sex = e.id
      },
      sub(){
        let that = this
        let info = that.info1
        let data = {}
        data["id"] = that.query.id
        data["type"] = "info"
        data["_method"] = "put"
        data["name"] = info.name
        data["pronunciation"] = info.pronunciation
        data["phone"] = info.phone
        data["sex"] = info.sex
        data["birthday_year"] = info.birthday_year
        data["birthday_month"] = info.birthday_month
        data["birthday_day"] = info.birthday_day
        data["zip_code"] = info.zip_code
        data["province"] = info.province
        data["address1"] = info.address1
        data["address2"] = info.address2
        editUserInfo(data).then((res) => {
            if(res.code == 200){
                uni.showToast({
                    title:res.message,
                    duration:2000
                })
                that.shows = false
                that.getInfo()
            }else{
                uni.showToast({
                    title:res.message,
                    duration:2000,
                    icon:"none"
                })
            }
        })
      },
      searchAddress(){
        let that = this 
        uni.showLoading({
            title:"lodaing"
        })
        getAddress(that.info1.zip_code).then((res) => {
            uni.hideLoading()
            if(res.code == 200){
                that.info1.province = res.data.address.address1
                that.info1.address1 = res.data.address.address2+res.data.address.address3
            }
        })  
      },
      timeConfirm(e) {
        this.info1.birthday_year = uni.$u.timeFormat(e.value, 'yyyy')
        this.info1.birthday_month = uni.$u.timeFormat(e.value, 'mm')
        this.info1.birthday_day = uni.$u.timeFormat(e.value, 'dd')
        this.info1.birthday_time = uni.$u.timeFormat(e.value, 'yyyy-mm-dd')
        this.show = false
      },
      cancel() {
        this.show = false
      },
      confirm(e){
         this.info1.province = e.value[0]
         this.showAdd = false
      },
      getInfo(){
          let that = this
          getUserInfo(that.query.id).then((res) => {
              if(res.code == 200){
                  that.info = res.data.member
                  that.info1 = res.data.member
                  that.avatar = res.data.member.avatar
                  getShopInfo(res.data.member.sid).then((result) =>{
                      that.configs = result.data.shop.member_register_field
                  })
              }
          })
      }
    }
  }
</script>

<style lang="scss" scoped>
  ::v-deep .u-navbar__content__title {
    font-size: 40rpx;
    color: #1D1D1F;
  }



  .con {
    width: 90%;
    margin: 0 auto;

    .user-box {
      margin-top: 60rpx;
      margin-bottom: 104rpx;
      text-align: center;

      image {
        width: 175rpx;
        height: 175rpx;
        border-radius: 50%;
      }

      .name {
        margin-top: 62rpx;
        color: #1D1D1F;
        font-size: 32rpx;

      }
    }

    .edit-right {
      width: 100%;
      color: #1A73E8;
      font-size: 32rpx;
      text-align: right;
      margin-bottom: 54rpx;

      &:active {
        opacity: .7;
      }
    }

    .left-box {
      .item {
        display: flex;
        margin-bottom: 32rpx;

        .name {
          font-size: 32rpx;
          color: #86868B;
          width: 128rpx;
        }

        .i-txt {
          .top {
            margin-top: 10rpx;
          }

          margin-left: 70rpx;
          font-size: 32rpx;
          color: #1D1D1F;
        }
      }
    }
  }

  .tit-box {
    border-bottom: 1px solid #D2D2D7;
    height: 112rpx;
  }

  .title {
    width: 90%;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 100%;
    background-color: #FCFCFC;
    margin-bottom: 36rpx;


    .left {
      image {
        width: 48rpx;
        height: 48rpx;
      }
    }

    .content {
      font-size: 40rpx;
      font-weight: 700;
      color: #1D1D1F;
    }

    .keep {
      font-size: 32rpx;
      color: #1A73E8;
    }
  }

  ::v-deep .u-modal__title {
    font-size: 32rpx;
    color: #1D1D1F;
    font-weight: 700;
  }

  ::v-deep .u-modal {
    border-radius: 28rpx;
  }

  ::v-deep .u-line {
    border-color: #707070 !important;
  }

  ::v-deep .u-modal__button-group__wrapper__text {
    font-size: 32rpx;
    color: #1A73E8;
    font-weight: 700;
  }

  ::v-deep .u-modal__content__text {
    color: #1D1D1F;
    font-size: 28rpx;
    text-align: center;
  }

  ::v-deep .u-form-item__body__left__content__label {
    font-size: 32rpx;
    color: #707070;
    width: 100%;
  }

  ::v-deep .u-input {
    height: 88rpx;
    width: 100%;
    border-radius: 20rpx;
    padding: 0 20rpx;
    border: 2rpx solid #D2D2D7;
    box-sizing: border-box;
  }

  .modify-user {
    height: 90vh;
    overflow: scroll;
  }

  .bns {
    width: 200rpx;
    height: 88rpx;
    text-align: center;
    line-height: 88rpx;
    color: #FFFFFF;
    background: #1A73E8;
    border-radius: 20rpx;
  }
</style>
