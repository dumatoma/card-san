<template>
  <div>
    <div class="title">会員登録情報</div>
    <div class="cont">
      <div style="padding: 40px 0 46px 40px">
        <div class="cont_tit">
          カード会員登録の際に、お客さんに入力していただく情報です。
        </div>
        <div class="blue_tit">必須項目のみチェックを入れてください。</div>
        <div style="display: flex; align-items: center;margin-top: 30px;">
            <span style="color: red;margin-right: 15px;">※</span> <span style="font-weight: bold;">氏名</span> 
        </div>
        <el-checkbox-group
          v-model="result"
          @change="handleCheckedCitiesChange"
        >
          <div class="choice u-flex-col u-row-between">
            <el-checkbox v-for="item,index in infoList" :label="item.name" :value="item.name" :key="index">
              <span :style="`${result.includes(item.name)?'font-weight:bold !important':''}`">{{item.title}}</span> 
            </el-checkbox>
          </div>
        </el-checkbox-group>
      </div>

      <div class="bottom">
        <div class="btn u-flex-center shou" @click="keepClick">保　存</div>
      </div>
    </div>
    <!-- 保存 -->
    <div class="fixed" v-show="keepShow">
      <div class="release u-flex-col u-col-center u-row-center">
        <img src="@/static/index/sign_blue.png" class="sign" />
        <div>保存しました</div>
      </div>
    </div>
  </div>
</template>


<script>
import {noticeSetting,getStore} from "@/http/api.js"
export default {
  data() {
    return {
      checkedCities: [],
      // infoList: ["氏名", "", "", "", "", ""],
      infoList:[
      {
         title:"カナ",
         name:"pronunciation"
      },
      {
         title:"電話番号",
         name:"phone"
      },
      {
         title:"性別",
         name:"sex"
      },
      {
         title:"生年月日",
         name:"birthday"
      },
      {
         title:"住所",
         name:"address"
      },
      
      ],
      keepShow: false,
      result:[]
    };
  },
  created() {
      let that = this
      getStore().then((res) => {
          console.log(res)
          if(res.code == 200){
              that.result = res.data.shop_info.member_register_field
              console.log(111,that.result)
          }
      })
  },
  methods: {
    handleCheckedCitiesChange(value) {
        this.result = value
      console.log(value, "chachahhchahac");
    },
    keepClick() {
        let that = this 
        let data = {}
        data.type = "member"
        data.member_register_field = that.result
        noticeSetting(data).then((res) => {
            if(res.code == 200){
                that.keepShow = true;
                setTimeout(() => {
                  that.keepShow = false;
                }, 1500);
            }else{
                that.$message.error(res.message)
            }
        })
    },
  },
};
</script>

<style lang="scss" scoped>
    ::v-deep .el-checkbox__input.is-checked + .el-checkbox__label{
        color: #1d1d1f !important;
    }
.title {
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
}
.cont {
  width: 820px;
  background: #fff;
  margin-top: 17px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .cont_tit {
    font-size: 20px;
    color: #1d1d1f;
    font-weight: bold;
  }
  .blue_tit {
    font-size: 16px;
    color: #1a73e8;
    font-weight: bold;
    margin-top: 16px;
  }
  .choice {
    height: 260px;
    margin-top: 30px;
    font-size: 16px;
    color: #707070;
  }
  .bottom {
    padding: 30px 0 40px 0;
    border-top: 1px solid #d2d2d7;
    .btn {
      width: 200px;
      height: 50px;
      font-size: 20px;
      color: #fff;
      border-radius: 10px;
      margin: 0 auto;
      background: #1a73e8;
    }
  }
}
.fixed {
  position: fixed;
  top: 0%;
  left: 0%;
  width: 100vw;
  height: 100vh;
  z-index: 1;
  background: rgba(29, 29, 31, 0.2);
  .release {
    width: 160px;
    height: 146px;
    background: #fff;
    border-radius: 10px;
    position: fixed;
    top: 50%;
    left: 50%;
    font-size: 16px;
    color: #2699fb;
    transform: translate(-50%, -50%);
    box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
    .sign {
      width: 50px;
      height: 50px;
      margin-bottom: 20px;
    }
  }
}
::v-deep .el-checkbox__inner {
  width: 20px;
  height: 20px;
  color: #707070;
  border-radius: 50%;
  border: 1px solid #707070;
}
::v-deep .el-checkbox__label {
  font-size: 16px;
}
::v-deep .el-checkbox__inner::after {
  width: 5px;
  height: 9px;
  top: 2px;
  left: 6px;
}
::v-deep .el-checkbox__input.is-checked .el-checkbox__inner,
.el-checkbox__input.is-indeterminate .el-checkbox__inner {
  background-color: #1a73e8;
  border-color: #1a73e8;
}
// ::v-deep .el-checkbox__input.is-checked + .el-checkbox__label {
//   color: #1a73e8;
// }
</style>
