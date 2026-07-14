<template>
  <view class="bgs">
    <u-navbar title="アカウント情報" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56" :border="true"
      @rightClick="rightClick" @leftClick="leftClick">
      <view class="u-nav-right" slot="right">
        保存
      </view>
    </u-navbar>
    <view class="user-box">
      <image :src="userinfo.avatar || mo" mode="" @click="editAvatar()" mode="aspectFill"></image>
      <view class="name">
        <text style="font-weight: 700;" v-text="userinfo.name"></text> さん
      </view>
    </view>
    <view class="des" v-if="userinfo.line_id || userinfo.apple_id || userinfo.google_id || (userinfo.phone && !userinfo.google_id && !userinfo.line_id && !userinfo.apple_id)">
      <view class="ds1">
        <text style="font-weight: 700;" v-if="userinfo.line_id">LINEでサインイン</text>
        <text style="font-weight: 700;" v-if="userinfo.apple_id">Apple IDでサインイン</text>
        <text style="font-weight: 700;" v-if="userinfo.google_id">Googleアカウントでサインイン</text>
        <text style="font-weight: 700;" v-if="userinfo.phone && !userinfo.google_id && !userinfo.line_id && !userinfo.apple_id">電話番号(SMS認証)でサインイン</text>
      </view>
      <view class="ds2" v-if="userinfo.google_id">
         <text v-if="userinfo.google_id">GOOGEL NAME:{{userinfo.google_name}}</text>
       </view>
     <view class="ds2">
        <text v-if="userinfo.apple_id">{{userinfo.apple_email}}</text>
        <text v-if="userinfo.line_id">LINE NAME:{{userinfo.line_name}}</text>
        <text v-if="userinfo.google_id && userinfo.google_email">{{userinfo.google_email}}</text>
      </view>
      <view class="ds2" v-text="userinfo.phone" v-if="userinfo.phone && !userinfo.google_id && !userinfo.line_id && !userinfo.apple_id"></view>
    </view>
    <view class="add des" @click="toChange" v-if="haslog == true">
      サインイン方法を変更
    </view>
    <!-- 退出登录 -->
    <view class="add des" style="color: #D93025;" @click="showAlert = true" v-if="haslog">
      ログアウト
    </view>
    <!-- 未登录去登录页面 -->
    <!-- <view class="add des" style="color: #D93025;" @click="toLogin" v-else>
      ログイン
    </view> -->
    <view class="des" v-if="haslog == false">
      <view class="text-box">
        安全かつ便利にお使い頂くためにアカウントの登録をしてください。　アカウント登録しない場合、機種変更などの際に、データの引き継ぎ・保守が出来なくなります。
      </view>
      <view class="text-login" @click="toLogin">
        アカウントを登録する
      </view>
    </view>
    <view class="line"></view>
    <view class="user-information">
      <view class="ttt des">
        プロフィール
      </view>
      <view class="user-des des">
        プロフィールを登録することで、カード登録ごとのお客様情報入力の手間を省くことが出来るので便利です。
      </view>
    </view>
    <view class="modify-user">
      <u--form labelPosition="top" ref="form1" :labelWidth="200">
        <u-form-item label="氏名" prop="userInfo.name" ref="item1">
          <u--input v-model="userinfo.name"></u--input>
        </u-form-item>
        <u-form-item label="カナ" prop="userInfo.name" ref="item1">
          <u--input v-model="userinfo.pronunciation"></u--input>
        </u-form-item>
        <u-form-item label="電話番号" prop="userInfo.tel" ref="item1">
          <u--input v-model="userinfo.phone" type="number" maxlength="11"></u--input>
        </u-form-item>
        <u-form-item label="性別" prop="userInfo.sex" @click="showSex = true;" ref="item1">
          <u--input v-model="userinfo.sex == 1? '男性' : userinfo.sex == 2? '女性' : userinfo.sex == 3?'Xジェンダー' : '女性' " disabled disabledColor="#F6F5F8" placeholder="選択"
            placeholderStyle=" color:#AAAAAA ;" suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB">

          </u--input>
          <view class="" style=" width: 40%;"></view>
        </u-form-item>
        <u-form-item label="生年月日" prop="userInfo.time" @click="show = true;" ref="item1">
          <u--input v-model="userinfo.birthday_year" disabled disabledColor="#F6F5F8" placeholder="年"
            suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB"
            placeholderStyle=" text-align: center ; color:#AAAAAA ;" inputAlign="center">
          </u--input>
          <view style="width: 5%;"></view>
          <u--input v-model="userinfo.birthday_month" disabled disabledColor="#F6F5F8" placeholder="月"
            suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB"
            placeholderStyle=" text-align: center;  color:#AAAAAA ;" inputAlign="center">
          </u--input>
          <view style="width: 5%;"></view>
          <u--input v-model="userinfo.birthday_day" disabled disabledColor="#F6F5F8" placeholder="日"
            suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB"
            placeholderStyle=" text-align: center;  color:#AAAAAA ;" inputAlign="center">
          </u--input>
        </u-form-item>
        <u-form-item label="郵便番号" prop="userInfo.addSn" ref="item1">
          <u--input v-model="userinfo.zip_code" placeholder="ハイフンなし"></u--input>
          <view style="width: 10%;"></view>
          <view class="bns" @click="getAddress">
            住所検索
          </view>
        </u-form-item>
        <u-form-item prop="userInfo.addSn" ref="item1" @click='showa = true'>
          <u--input v-model="userinfo.province" disabled disabledColor="#EAEAEA " placeholder="都道府県"
            suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB" placeholderStyle=" color:#868688  ;"></u--input>
          <view style="width: 40%;"></view>
        </u-form-item>
        <u-form-item label="住所1" prop="userInfo.address1" ref="item1">
          <u--input v-model="userinfo.address1" placeholder="市区町村、番地"></u--input>
        </u-form-item>
        <u-form-item label="住所2" prop="userInfo.address2" ref="item1">
          <u--input v-model="userinfo.address2" placeholder="建物名、部屋番号"></u--input>
        </u-form-item>
      </u--form>
      <u-action-sheet :show="showSex" :actions="actions" title="性別を選択してください" @close="showSex = false"
        @select="sexSelect">
      </u-action-sheet>
      <u-datetime-picker :show="show" v-model="value1" mode="date" :minDate="-631180800000" @confirm="timeConfirm($event)" @cancel="cancel()">
      </u-datetime-picker>
      <u-modal :show="keepShow" :title="title" :content='content' confirmText="OK" @confirm="confrims">
      </u-modal>
      <view class="btn" @click="showAlert1 = true" hover-class="hoverbtn">
          アカウントを削除
      </view>
    </view>
    <u-picker @confirm="chooseAddress" :show="showa" @cancel="showa = false" :columns="city"></u-picker>
    <mod v-if="showAlert" :config = 'configs' @getStatus='changeStatus'></mod>
    <mod v-if="showAlert1" :config = 'configg' @getStatus='changeStatus1'></mod>
  </view>
</template>

<script>
  // const JYGoogleSignin = uni.requireNativePlugin('JY-GoogleSignin');
  import {getUserInfos,getAddress,editUser,takeoff} from "@/api/index.js"
  import mod from '../../components/mod.vue'
  export default {
    components:{
        mod
    },
    data() {
      return {
        show: false,
        haslog:false,
        showAlert:false,
        showAlert1:false,
        showa:false,
        configg:{
            title: "このアカウントを削除してよろしいですか？",
            content: "削除すると、このアプリのデータは完全に消去されます。",
            confirm: true,
            cancel: true,
            confirmText: "アカウントを削除する",
            cancelText: "キャンセル",
            type: "normal"
        },
        configs:{
            title: "ログアウトしてよろしいですか？",
            content: "",
            confirm: true,
            cancel: true,
            confirmText: "ログアウト",
            cancelText: "キャンセル",
            type: "normal"
        },
        noLogin: uni.getStorageSync("token"),
        keepShow: false,
        value1: Number(new Date()),
        showSex: false,
        title: 'アカウント情報の更新',
        mo:"../../static/image/avatar.png",
        content: 'アカウント情報を更新しました',
        userinfo:{},
        city: [
            ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都',
                '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府',
                '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県',
                '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
            ]
        ],
        actions: [{
            name: '男性',
            id:1
          },
          {
            name: '女性',
            id:2
          },
          {
            name: 'Xジェンダー',
            id:3
          },
        ],
        radio: '',
        switchVal: false
      };
    },
    created() {
      let that = this
      // getUserInfos().then((res) => {
      //     if(res.code == 200){
      //         that.userinfo = res.data.user
      //     }
      // })
    },
    onShow() {
       let that = this
       // let user = uni.getStorageSync("user")
       getUserInfos().then((res) => {
           if(res.code == 200){
               that.userinfo = res.data.user
           }
       }).catch(() => {})
       let token = uni.getStorageSync("token")
       if(token){
           that.haslog = true
       }else{
           that.haslog = false
       }
    },
    methods: {
        toChange(){
          let that = this
          uni.navigateTo({
              url:"../../pages/changeMethod/changeMethod"
          })
        },
        changeStatus(e){
          let that = this
          if(e == 'confirm'){
              uni.clearStorageSync()
              uni.clearStorage()
              uni.removeStorageSync("token")
              uni.removeStorageSync("user")
              JYGoogleSignin.jy_logout(res=> {
              //  不会返回数据，调用就成功
              })
              // reLaunch: 全ページを破棄してログイン画面へ（tabBar/非tabBar両対応・キャッシュされた氏名も消える）
              uni.reLaunch({
                  url:"/pages/login/login"
              })
          }else{
              that.showAlert = false
          }
        },
        toLogin(){
            uni.removeStorageSync("token")
            uni.removeStorageSync("user")
            uni.clearStorageSync()
            uni.clearStorage()
            uni.removeStorageSync("token")
            uni.removeStorageSync("user")
            uni.clearStorageSync();
            // JYGoogleSignin.jy_logout(res=> {
            // //  不会返回数据，调用就成功
            // })
           uni.reLaunch({
               url:"/pages/login/login"
           })
        },
        changeStatus1(e){
            let that = this
            if(e == 'confirm'){
                takeoff().then((res) => {
                    if(res.code == 200){
                        that.showAlert1 = false
                        uni.showToast({
                            title:res.message,
                            icon:"none",
                            duration:2500
                        })
                        setTimeout(() => {
                            uni.redirectTo({
                                url:"../../pages/login/login"
                            })
                        },2500)
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                }).catch(() => {})
            }else{
              that.showAlert1 = false
            }
        },
        getInfo(){
            let that = this
            getUserInfos().then((res) => {
                if(res.code == 200){
                    that.userinfo = res.data.user
                }
            }).catch(() => {})
        },
        deleteAccount(){
          let that = this
          
        },
      sexSelect(e) {
        this.userinfo.sex = e.id
      },
      chooseAddress(e){
        this.userinfo.province = e.value[0]
        this.showa = false
      },
      editAvatar() {
          let that = this
          let image = ""
          if(that.userinfo.avatar == ''){
              image = that.mo
          }else{
              image = that.userinfo.avatar
          }
        uni.navigateTo({
          url: '../../pagesA/AvatarReplacement/AvatarReplacement?image='+image+"&id="+that.userinfo.id+"&name="+that.userinfo.name
        })
      },
      confrims(){
          let that = this
         that.keepShow = false 
         let info = uni.getStorageSync("user")
         let id = info.id
        getUserInfos().then((res) => {
            if(res.code == 200){
                that.userinfo = res.data.user
            }
        }).catch(() => {})
      },
      leftClick() {
        uni.navigateBack()
      },
      rightClick() {
        // this.keepShow = true
        let that = this 
        let data = that.userinfo
        data.type = "info"
        data._method = "put"
        let query= uni.getStorageSync("user")
        let id = query.id
        data['sex'] == 0?2:data['sex']
        editUser(data,id).then((res) => {
            if(res.code == 200){
                that.keepShow = true
                getUserInfos().then((res) => {
                    if(res.code == 200){
                        that.userinfo = res.data.user
                    }
                }).catch(() => {})
            }else{
                uni.showToast({
                    title:res.message,
                    icon:"none"
                })
            }
        }).catch(() => {})
      },
      timeConfirm(type, value) {

        this.userinfo.birthday_year = uni.$u.timeFormat(type.value, 'yyyy')
        this.userinfo.birthday_month = uni.$u.timeFormat(type.value, 'mm')
        this.userinfo.birthday_day = uni.$u.timeFormat(type.value, 'dd')
        // this.model1.userInfo.time = uni.$u.timeFormat(type.value, 'yyyy-mm-dd')
        this.show = false
      },
      cancel() {
        this.show = false
      },
      getAddress(){
          let that = this 
          uni.showLoading({
              title:"検索中"
          })
         getAddress(this.userinfo.zip_code).then((res) => {
             uni.hideLoading()
             if(res.code == 200){
                 that.userinfo.province = res.data.address.address1
                 that.userinfo.address1 = res.data.address.address2 + res.data.address.address3
             }
         }).catch(() => { uni.hideLoading() })
      }
    }
  }
</script>

<style lang="scss" scoped>
    page{
        font-family: Hiragino Sans-W5, Hiragino Sans;
    }
    .hoverbtn{
        background: rgba(217,48,37,0.6) !important;
    }
  .btn{
      width: 232upx;
      height: 64upx;
      background: rgba(217,48,37,1);
      border-radius: 10upx;
      font-size: 24upx;
      font-family: Hiragino Sans-W5, Hiragino Sans;
      font-weight: normal;
      color: #FFFFFF;
      line-height: 64upx;
      text-align: center;
      margin: 202upx auto 0;
  }
  ::v-deep .u-navbar__content__title {
    font-size: 40rpx;
    color: #1D1D1F;
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

  .u-nav-right {
    font-size: 32rpx;
    color: #1A73E8;

  }

  .text-box {
    margin-top: 40rpx;
    height: 200rpx;
    background: #FDEBEA;
    border-radius: 20rpx;
    font-size: 28rpx;
    color: #D93025;
    padding: 20rpx 24rpx;

  }

  .text-login {
    margin-top: 60rpx;
    height: 100rpx;
    background: #D93025;
    text-align: center;
    color: #FFFFFF;
    font-size: 32rpx;
    line-height: 100rpx;
    border-radius: 20rpx;
  }

  .user-box {
    margin-top: 60rpx;
    margin-bottom: 136rpx;
    text-align: center;

    image {
      width: 175rpx;
      height: 175rpx;
      border-radius: 50%;
    }

    .name {
      margin-top: 22rpx;
      color: #1D1D1F;
      font-size: 48rpx;

    }
  }

  .des {
    width: 90%;
    margin: 0 auto;

    .ds1 {
      font-size: 32rpx;
      color: #707070;
    }

    .ds2 {
      margin-top: 20rpx;
      font-size: 24rpx;
      color: #1D1D1F;
    }
  }

  .add {
    margin-top: 80rpx;
    color: #1A73E8;
    font-size: 24rpx;

    &:active {
      opacity: .7;
    }
  }

  .line {
    margin-top: 44rpx;
    width: 100vw;
    height: 2rpx;
    background-color: #D2D2D7;
  }

  .user-information {
    background-color: #F6F5F8;
    padding-top: 120rpx;
    color: #1D1D1F;
    padding-bottom: 40rpx;

    .ttt {
      font-size: 40rpx;
      font-weight: 700;
      margin-bottom: 20rpx;
    }

    .user-des {
      font-size: 28rpx;
    }
  }

  .modify-user {
    box-sizing: border-box;
    padding: 0 40upx;
    margin: 0 auto;
    padding-bottom: 200rpx;

    ::v-deep .u-input__content__field-wrapper__field {
      width: 100%;
      height: 88rpx;
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

    .bns {
      width: 200rpx;
      height: 88rpx;
      text-align: center;
      line-height: 88rpx;
      color: #FFFFFF;
      background: #1A73E8;
      border-radius: 20rpx;
    }

  }
</style>
