<template>
  <div @click="conserveShow = false">
    <div class="deputy_tit" v-if="query.type == 'add'">副管理者の登録</div>
    <div class="deputy_tit" v-else>副管理者の編集</div>
    <div class="deputy_cont">
      <div class="grey_bg">
        副管理者の氏名、電話番号(SMS認証用)を入力してください。
      </div>
      <div class="white_bg">
        <div>店舗名：{{shop_name}}</div>
        <div class="u-flex m-t-30">
          <div class="text_red">※</div>
          <div class="text_grey">氏名</div>
        </div>
        <input class="int no-size" type="text" v-model="uname" />
        <div class="u-flex m-t-30">
          <div class="text_red">※</div>
          <div class="text_grey">電話番号(SMS認証用)</div>
        </div>
        <input class="int no-size" type="text" v-model="tel" />
      </div>
      <div class="grey_bg">副管理者に付与する権限を選択して下さい。</div>
      <div class="u-flex title">
        通常利用には
        <div style="font-weight: bold">「運営」「各種設定」</div>
        の選択をおすすめします。
      </div>
      <div class="greyTwo_bg u-flex" :class="[operateShow ? 'black' : 'grey']">
        <div
          class="radius u-flex-center"
          @click.stop="operateClick"
          :class="[operateShow ? 'radiusCss' : '']"
        >
          <div class="blue_dot" v-if="operateShow"></div>
        </div>
        運営
      </div>
      <div class="whiteTwo_bg">
        <el-checkbox-group v-model="privileges" @change="operateChange">
          <div class="checkTwo">
            <el-checkbox
              class="checkItem"
              v-for="(item, index) in operateList"
              :label="item.value"
              :key="index"
              >{{ item.name }}</el-checkbox
            >
          </div>
        </el-checkbox-group>
      </div>
      <div class="greyTwo_bg u-flex" :class="[setShow ? 'black' : 'grey']">
        <div
          class="radius u-flex-center"
          @click.stop="setClick"
          :class="[setShow ? 'radiusCss' : '']"
        >
          <div class="blue_dot" v-if="setShow"></div>
        </div>
        各種設定
      </div>
      <div class="whiteTwo_bg">
        <el-checkbox-group v-model="privileges" @change="setChange">
          <div class="checkTwo">
            <el-checkbox
              class="checkItem"
              v-for="(item, index) in setList"
              :label="item.value"
              :key="index"
              >{{ item.name }}</el-checkbox
            >
          </div>
        </el-checkbox-group>
      </div>
      <!-- <div class="greyTwo_bg u-flex" :class="[accountShow ? 'black' : 'grey']">
        <div
          class="radius u-flex-center"
          @click.stop="accountClick"
          :class="[accountShow ? 'radiusCss' : '']"
        >
          <div class="blue_dot" v-if="accountShow"></div>
        </div>
        アカウント
      </div>
      <div class="whiteTwo_bg">
        <el-checkbox-group v-model="privileges" @change="accountChange">
          <div class="checkTwo">
            <el-checkbox
              class="checkItem"
              v-for="(item, index) in accountList"
              :label="item.value"
              :key="index"
              :disabled="!accountShow"
              >{{ item.name }}</el-checkbox
            >
          </div>
        </el-checkbox-group>
      </div> -->
      <div class="bottom">
        <div class="btn u-flex-center shou" @click.stop="conserveClick">
          保　存
        </div>
      </div>
    </div>

    <!-- 保存 -->
    <div class="fixed" v-show="conserveShow">
      <div class="release u-flex-col u-col-center u-row-center">
        <img src="@/static/index/sign_blue.png" class="sign" />
        <div>保存しました</div>
      </div>
    </div>
  </div>
</template>

<script>
import {getFuDetail,addFu,getStore,eidtAdmin} from '@/http/api.js' 
export default {
  data() {
    return {
      uname: "",
      tel: "", 
      operateShow: true,
      checkList: [],
      accountShow: false,
      setShow: true,
      checkedOperate: [],
      query:{},
      privileges:[1,2,3,4,5,6,7,8,9,10,11,12,13,14],
      operateList: [
          {
              name:"QRリーダー",
              value:1
          },
          {
              name:"ダウンロードQR表示",
              value:2
          },
          {
              name:"営業カレンダー",
              value:3
          },
          {
              name:"顧客情報の編集",
              value:4
          },
          {
              name:"メッセージ",
              value:5
          },
          {
              name:"お知らせ投稿",
              value:6
          },
          {
              name:"クーポン",
              value:7
          },
          {
              name:"予約管理",
              value:8
          },
      ],
      checkedAccount: [],
      // accountList: [
      //     {
      //         name:"お支払い方法",
      //         value:14
      //     },
      //     {
      //         name:"ご利用プラン",
      //         value:15
      //     },
      //     {
      //         name:"ご利用明細",
      //         value:16
      //     },
      //     {
      //         name:"販促アイテムの購入",
      //         value:17
      //     },
      //     {
      //         name:"ご契約内容",
      //         value:18
      //     }
      // ],
      checkedSet: [],
      setList: [
          {
              name:"店舗情報",
              value:9
          },
          {
              name:"メンバーズカード作成",
              value:10
          },
          {
              name:"デザイン設定",
              value:11
          },
          {
              name:"会員登録情報",
              value:12
          },
          {
              name:"メニュー登録",
              value:13
          },
          {
              name:"スタッフ登録",
              value:14
          },
      ],
      conserveShow: false,
      shop_name:""
    };
  },
  created() {
    let that = this  
    let query = that.$route.query
    let data = {}
    data.id = query.id
    that.query = query
    getStore().then((res) => {
        if(res.code == 200){
            that.shop_name = res.data.shop_info.shop_name
        }
    })
    if(query.type == 'add'){
        
    }else{
        getFuDetail(data).then((res) => {
            if(res.code == 200){
                console.log("qu",res)
                that.uname = res.data.admin.name
                that.tel = res.data.admin.phone
                
                res.data.admin.privileges.forEach((v,i) => {
                    v = v * 1 - 1
                })
                that.privileges = res.data.admin.privileges
                that.operateChange(that.privileges)
                that.setChange(that.privileges)
            }
        })
    }
  },
  methods: {
    operateClick() {
      let temp = [1,2,3,4,5,6,7,8]
      if(this.operateShow == false){
          let array = this.privileges
          let a1 = [...array,...temp]
          this.privileges = Array.from(new Set(a1))
          this.operateShow = true
      }else{
          this.privileges = this.privileges.filter(item => !temp.includes(item))
          this.operateShow = false
      }
      // this.operateShow = !this.operateShow;
    },
    setClick() {
      let temp = [9,11,10,12,13,14]
      if(this.setShow == false){
          let array = this.privileges
          let a1 = [...array,...temp]
          this.privileges = Array.from(new Set(a1))
          this.setShow = true
      }else{
          this.privileges = this.privileges.filter(item => !temp.includes(item))
          this.setShow = false
      }
    },
    operateChange(e) {
      if(e.includes(8) && e.includes(1) && e.includes(2) && e.includes(3) && e.includes(4) && e.includes(5) && e.includes(6) && e.includes(7)){
          this.operateShow = true
      }else{
           this.operateShow = false
      }
    },
    setChange(e) {
      if(e.includes(14) && e.includes(9) && e.includes(10) && e.includes(11) && e.includes(12) && e.includes(13)){
          this.setShow = true
      }else{
           this.setShow = false
      }
    },
    conserveClick(){
        let that = this
        let data = {}
        if(that.query.type == 'edit'){
            data._method = "put"
            data.name = that.uname 
            data.phone = that.tel
            data.privileges = that.privileges
            eidtAdmin(data,that.query.id).then((res) => {
                if(res.code == 200){
                    that.conserveShow = true
                    setTimeout(() => {
                        history.go(-1)
                    },1500)
                }else{
                    that.$message({
                    	message: res.message,
                    	type: 'error',
                         offset: 400
                    });
                }
            })
        }else{
            data['type'] = 2
            data['name'] = that.uname
            data['phone'] = that.tel
            data['privileges'] = that.privileges
            addFu(data).then((res) => {
                if(res.code == 200){
                   that.conserveShow = true
                   setTimeout(() => {
                       history.go(-1)
                   },1500)
                }else{
                    that.$message({
                    	message: res.message,
                    	type: 'error',
                         offset: 400
                    });
                }
            })
        }
    }
  },
};
</script>


<style scoped>
::v-deep .el-checkbox__input.is-checked .el-checkbox__inner,
.el-checkbox__input.is-indeterminate .el-checkbox__inner {
  border-color: #1a73e8 !important;
  background-color: #1a73e8 !important;
}
::v-deep .el-checkbox__input.is-focus .el-checkbox__inner {
  border-color: #1a73e8 !important;
}
::v-deep .el-checkbox__inner {
  width: 16px;
  height: 16px;
}
::v-deep .el-checkbox {
  color: #707070;
}
::v-deep .el-checkbox.is-bordered {
  border: 1px solid #d93025;
  border-radius: 0;
}
::v-deep .el-checkbox__inner::after {
  left: 5px;
  top: 1px;
  width: 4px;
  height: 9px;
}
/* //修改选中后文本的颜色 */
::v-deep .el-checkbox__input.is-checked + .el-checkbox__label {
  color: #1d1d1f !important;
  font-weight: bold;
}
</style>
<style lang="scss" scoped>
.deputy_tit {
  font-size: 21px;
  font-weight: bold;
  color: #1d1d1f;
}
.deputy_cont {
  width: 820px;
  margin-top: 31px;
  background: #fff;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .grey_bg {
    font-size: 16px;
    color: #1d1d1f;
    font-weight: bold;
    padding: 14px 20px;
    background: rgba(29, 29, 31, 0.1);
  }
  .white_bg {
    padding: 26px 40px 40px 40px;
    .text_red {
      font-size: 16px;
      color: #d93025;
    }
    .text_grey {
      font-size: 16px;
      color: #707070;
    }
    .int {
      width: 329px;
      height: 40px;
      outline: 0;
      margin-top: 5px;
      background: #ffffff;
      border-radius: 10px;
      padding-left: 14px;
      border: 1px solid #aaaaaa;
    }
  }
  .title {
    font-size: 14px;
    color: #1d1d1f;
    padding: 30px 0 42px 40px;
  }
  .greyTwo_bg {
    font-size: 16px;
    background: #f5f5f7;
    padding: 12px 0 12px 40px;
  }
  .radius {
    width: 20px;
    height: 20px;
    margin-right: 14px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #707070;

    .blue_dot {
      width: 16px;
      height: 16px;
      border-radius: 50%;
      background: #1a73e8;
    }
  }
  .radiusCss {
    border: 1px solid #1a73e8;
  }
  .black {
    font-weight: bold;
    color: #1d1d1f;
  }
  .grey {
    color: #707070;
  }
  .whiteTwo_bg {
    padding: 30px 70px;
    .checkTwo {
      width: 680px;
      .checkItem {
        width: 150px;
        margin-bottom: 22px;
        margin-right: 180px;
      }
      .marLeft {
        margin-left: -10px;
      }
    }
  }
  .bottom {
    padding: 30px 0 70px 0;
    border-top: 1px solid #d2d2d7;
    .btn {
      width: 200px;
      height: 50px;
      font-size: 20px;
      margin: 0 auto;
      color: #ffffff;
      background: #1a73e8;
      border-radius: 10px;
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
</style>
