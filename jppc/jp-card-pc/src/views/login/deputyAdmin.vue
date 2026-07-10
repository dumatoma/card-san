<template>
  <div class="wrap no-size">
    <!-- 上导航栏 -->
    <div class="mark-title no-size">
      <img src="../../static/tou-logo.svg" alt="" />
    </div>
    <div class="content_login no-size">
      <img class="title_img" src="../../static/index/dlogo.svg" alt="" />

      <div class="int u-flex-col u-col-top">
        <div class="four">電話番号(携帯電話)</div>
        <input
          class="five no-size"
          placeholder-style="color:#AAAAAA;font-size:16px"
          placeholder="副管理者として登録された番号を入力"
          type="text"
          v-model="phone"
        />
      </div>

      <!-- 确认按钮 -->
      <el-button class="submit shou" @click="sendCode" :loading="isloading" :style="phone.length> 0?'background:#06c755' : ''">{{logword}}</el-button>
      <div class="login_tip shou" @click="popShow = true">
        スマートフォン、タブレットでご利用の方は必ず
        こちから専用のアプリをダウンロードしてください
      </div>
    </div>
    <!-- 底部文字 -->
    <div class="footer u-flex u-row-center">
      Copyright (C) 2022 Customaid:LLC All Rights Reserved
    </div>
    <div class="popup" v-if="popShow">
    	<div class="white">
    		<div class="pop_title">
    			<div class="error shou u-flex u-row-center" @click="popShow = false">×</div>
    			<div class="pop_text">
    				スマートフォン、タブレットでご利用の方は、<br />
    				必ず以下のQRコードから専用の<br />
    				管理アプリをダウンロードしてください。
    			</div>
    			<div class="u-flex-col pop_img">
                    <img src="../../static/icon/cardsan_kanri_icon.svg" alt="">
    				CardSan管理
    			</div>
    			<div class="u-flex u-row-between u-col-bottom">
    				<div class="store u-flex-col u-col-center">
    					<img class="one_img no-size" src="https://cdn.me-qr.com/qr/125260401.png?v=1725897315" />
                        <div class="dobtns">
                            <img class="two_img" src="../../static/icon/Download_on_the_App_Store_Badge_JP_RGB_blk_100317.svg" />
                            <img class="two_img" src="../../static/icon/google-play-badge.svg" />
                        </div>
    					
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
  </div>
</template>

<script>
import{Login}from "@/http/api.js"
export default {
  data() {
    return {
      popShow: false,
      phone:"",
      logword:"SMSに認証コードを送る",
      isloading:false
    };
  },
  methods: {
      sendCode(){
         let that = this
         that.isloading = true
         that.logword = ''
         let data = {}
         data['login_type'] = 2
         data['phone'] = that.phone
         data['request_type'] = 1
         Login(data).then((res) => {
             console.log(res)
             if(res.code == 200){
                 // that.$message.success("code:"+res.message)
                 setTimeout(() => {
                     that.$router.push("/phone?email="+that.phone)
                 },1000)
             }else{
                 that.$message.error(res.message)
                 that.isloading = false
                 that.logword = 'SMSに認証コードを送る'
             }
         })
      }
  },
};
</script>
<style lang="scss" scoped>
* {
  padding: 0;
  margin: 0;
}

.wrap {
  width: 100vw;
  height: 100vh;
  box-sizing: border-box;
  background: #f9f9f9;

  .mark-title {
    width: 100%;
    background: #1d1d1f;
    height: 64px;
    padding: 9px 0 9px 20px;
    img {
      width: 160px;
      background: #1d1d1f;
    }
  }

  .content_login {
    width: 640px;
    height: 422px;
    position: absolute;
    text-align: center;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%); //移动的距离以自身的宽度为准
    background: #fff;
    padding: 0 134px;
    box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);
    .int {
      width: 329px;
      margin: 0 auto;
      .four {
        font-size: 16px;
        color: #0a4b6d;
        margin-top: 40px;
      }
      .five {
        width: 100%;
        height: 50px;
        outline: 0;
        border-radius: 10px;
        margin-top: 5px;
        padding-left: 10px;
        border: 1px solid #707070;
      }
    }
    .title_img {
      width: 200px;
      margin: 36px auto 0;
    }

    .submit {
      width: 329px;
        height: 50px;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      margin: 0 auto;
      margin-top: 30px;
      background: rgba(81, 216, 136, 1);
      border-radius: 25px;
    }
    .fu {
      color: #00a5a5;
      background: #fff;
      font-weight: bold;
      margin-top: 66px;
      border: 1px solid #53b1af;
    }

    .login_tip {
      font-size: 16px;
      color: #0a4b6d;
      margin-top: 50px;
      font-weight: bold;
    }
    
    .login_tip:hover{
        color: #1A73E8 !important;
        text-decoration: underline;
    }
  }
  .footer {
    width: 100%;
    height: 50px;
    font-size: 12px;
    color: #fff;
    position: absolute;
    bottom: 0px;
    background: #1d1d1f;
  }
  .dobtns img{
      margin: 0 10px;
  }

  .popup {
    top: 0;
    bottom: 0;
    position: absolute;
    z-index: 999;
    width: 100%;
    background: rgba(0, 0, 0, 0.16);
    .white {
      width: 893px;
      height: 627px;
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%); //移动的距离以自身的宽度为准
      background: #ffffff;
      box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);
      border-radius: 11px;

      .pop_title {
        padding: 40px 147px 30px 147px;
        position: relative;
        font-weight: bold;
        text-align: center;
        .error {
          width: 20px;
          height: 20px;
          font-size: 19px;
          color: rgba(29, 29, 31, 0.6);
          border-radius: 50%;
          background: rgba(29, 29, 31, 0.1);
          position: absolute;
          top: 20px;
          right: 20px;
        }
        .pop_text {
          font-size: 24px;
        }
        .pop_img {
          text-align: center;
          font-size: 16px;
          color: #1d1d1f;
          margin-top: 30px;
          background: transparent;
          
          img {
            width: 80px;
            margin: 0 auto;

          }
        }
        .center {
          width: 1px;
          height: 264px;
          background: #eaeaea;
        }
        .store {
          margin: 0 auto;
          .one_img {
            width: 224px;
            height: 224px;
            padding: 30px;
          }
          .two_img {
            width: 148px;
            height: 42px;
          }
        }
      }
    }
  }
}
</style>
