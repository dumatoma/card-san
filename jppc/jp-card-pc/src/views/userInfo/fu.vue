<template>
  <div>
    <div>
      <div class="tit">副管理者情報</div>
      <div class="user-box">
        <div class="fuitem">
           副管理者名：{{name}}
        </div>
        <div class="fuitem">
           電話番号：{{phone}}
        </div>
        <div class="tips">
            名前、SMS認証用電話番号、付与された権限は、副管理者ご自身では変更できません。   変更する場合は主管理者が「各種設定」>「副管理者の設定」から編集する必要がございます。
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import yzm from "../../components/yzm.vue";

export default {
  name: "",
  components: { yzm },
  data() {
    return {
      tiShow: false,
      name:"",
      phone:""
    };
  },
  created() {
      let that = this
      let admin = localStorage.getItem("admin")
      let user = JSON.parse(admin)
      console.log(user)
      that.name = user.name
      that.phone = user.phone
  },
  methods: {
      
  },
};
</script>

<style lang="scss" scoped>
    ::v-deep .el-select .el-input .el-select__caret{
        color: #aaa !important;
    }
    .alid{
        width: 400px;
        background: rgba(29,29,31,0.1);
        text-align: center;
        border-radius: 10px;
        padding: 14px 0;
    }
    .tips{
        width: 464px;
        background: rgba(143,168,248,0.2);
        border-radius: 10px;
        box-sizing: border-box;
        padding: 16px;
        font-family: Hiragino Sans, Hiragino Sans;
        font-weight: normal;
        font-size: 16px;
        line-height: 24px;
        color: #1A73E8;
    }
.content_login {
  margin-top: 49px;
  margin-left: 149px;
  width: 640px;
  height: 587px;
  text-align: center;
  background: #fff;
  padding: 0 95px;
  box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);

  .tit_one {
    width: 100%;
    font-size: 30px;
    color: #1d1d1f;
    font-weight: 700;
    text-align: center;
    padding-top: 40px;
  }
  .tit_two {
    font-size: 14px;
    color: #1d1d1f;
    margin-top: 40px;
  }
  input::-webkit-outer-spin-button,
  input::-webkit-inner-spin-button {
    -webkit-appearance: none;
  }

  .tit_three {
    width: 335px;
    height: 56px;
    background: #1a73e8;
    border-radius: 10px;
    margin: 40px auto;
    font-size: 20px;
    color: #fff;
  }
  .tit_threeCss {
    background: rgba(83, 177, 175, 0.5);
  }
  .tit_four {
    font-size: 14px;
    color: #707070;
  }
  .title_five {
    font-size: 14px;
    color: #2699fb;
    margin-top: 30px;
  }
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none !important;
}
input[type="number"] {
  -moz-appearance: textfield;
}
.tit {
  font-size: 21px;
  font-weight: 700;
  color: #1d1d1f;
}
.fuitem{
    width: 165px;
    height: 16px;
    font-family: Hiragino Sans, Hiragino Sans;
    font-weight: normal;
    font-size: 16px;
    margin-bottom: 20px;
    width: 100%;
}
::v-deep .user-box {
  margin-top: 20px;
  width: 820px;
  background: #ffffff;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  box-sizing: border-box;
  padding: 30px 40px;
  min-height: 70vh;
  
  .box {
    width: 100%;
    padding-left: 40px;
    padding-top: 47px;
    padding-right: 42px;
    
    .fan-sn {
      font-size: 20px;
      color: #1d1d1f;
      span {
        color: #707070;
        margin-right: 10px;
      }
    }
    .line {
      margin-top: 40px;
      margin-bottom: 40px;
      width: 100%;
      height: 1px;
      background: #d2d2d7;
    }
    
  }
  
}

</style>
