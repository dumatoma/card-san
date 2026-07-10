<template>
  <div>
    <div class="not_tit u-flex u-row-between">
      メンバーズカード一覧
      <div
        v-if="fillInShow"
        class="btn_grey shou u-flex u-row-center"
        @click.stop="breadShow = true"
      >
        Preview
      </div>
    </div>
    <div
      class="not_cont no-size"
      :style="fillInShow ? '' : 'padding: 30px 40px 50px 40px;'"
    >
      <div class="tit">
        {{ fillInShow ? "カードアプリのアイコン設定" : "カードの種類" }}
      </div>
      <div class="u-flex m-t-30" v-if="fillInShow">
        <div class="text_red">※</div>
        <div class="text_grey">店舗名(アプリ名)</div>
      </div>
      <input
       v-if="fillInShow"
        type="text"
        class="int"
        placeholder="店舗名をアプリ名にすることを推奨"
        v-model="shopName"
      />
      <!-- カードの種類  卡片类型 -->
      <div v-if="!fillInShow">
        <div class="u-flex m-t-30">
          <div class="text_red">※</div>
          <div class="text_black">カードの種類を選択してください <span style="color: #1a73e8;" @click="showCards(1)" class="shou">詳細>></span> </div>
        </div>
        <div style="margin-top: 9px">
          <el-select
            v-model="choice"
            @change="selectChanged"
            placeholder="選択する"
          >
            <el-option
              v-for="item in cates"
              :key="item"
              :label="item"
              :value="item"
            >
            </el-option>
          </el-select>
        </div>
        <div class="card u-flex u-col-bottom u-row-between">
          カード一覧
          <div class="new_card u-flex-center shou" @click="cardtype = 2,addnew()">
            <i
              class="el-icon-circle-plus"
              style="color: #1a73e8; margin-right: 6px"
            ></i
            >新規カード作成
          </div>
        </div>
        <!-- <el-checkbox-group v-model="checkList" @change="change"> -->
        <div
          class="card_item no-size"
          v-for="(item, index) in array"
          :key="index"
          :class="item.default == 1?'blueborder' : ''"
        >
          <div class="u-flex">
            <!-- <el-checkbox size="medium "></el-checkbox> -->
            <div
              class="box u-flex u-row-center"
              :class="{ boxCss: item.default == 1 }"
               @click.stop="cardClick(item.id,item.name)"
            >
              <i class="el-icon-check" style="color: #fff"></i>
            </div>
            <div class="img">
                <img :src="item.image" style="width: 100%;height: 100%;object-fit: cover;" alt="">
            </div>
            <div class="card_text u-flex-col">
              <div style="font-size: 16px; color: #1d1d1f; font-weight: bold" class="iti" @click.stop="toEdit(item)" v-text="item.name"></div>
              <div style="margin: 5px 0" v-if="item.type != 3">スタンプ数 : {{item.count}}個</div>
              <div style="margin: 5px 0" v-if="(item.type == 3) && (item.is_point == 1)">ポイント数 : {{item.amount}}円で{{item.point}}ポイント</div>
              <div style="margin: 5px 0" v-if="(item.type == 3) && (item.is_point == 0)">ポイント数 : - </div>
             <div v-if="item.valid_type == 2">有効期限：{{item.expire_time}}</div>
              <div v-if="item.valid_type == 0">有効期限：{{item.valid_text}}</div>
              <div v-if="item.valid_type == 1 && item.valid_day > 200">有効期限：発行から{{item.valid_day / 365}}年</div>
              <div v-if="item.valid_type == 1 && item.valid_day < 200">有効期限：発行から{{item.valid_day}}日</div>
              <div class="u-flex m-t-10">
                カード背景色
                <div v-if="item.type != 3" class="card_bg" :style="`background:${item.background_color}`"></div>
                <div v-if="item.type == 3">:-</div>
              </div>
            </div>
          </div>
          <div class="u-flex">
            <!-- <div class="utilize uGrey">編集</div> -->
            <div class="utilize uRed" @click.stop="delteThis(item)">削除</div>
          </div>
        </div>
        <!-- </el-checkbox-group> -->
      </div>
      <!-- カードアプリのアイコン設定   卡应用程序的图标设定 -->
      
    </div>
    <!-- 创建新卡弹窗 -->
    <div class="fixed" v-show="createCardShow">
      <div class="read_display">
        <div class="header no-size u-flex u-row-between">
          <div class="head_text">
            メンバーズカードの種類を1つ選択してください。
          </div>
          <div
            class="error shou u-flex u-row-center"
            @click.stop="createCardShow = false"
          >
            ×
          </div>
        </div>
        <div class="u-flex u-col-top">
          <div style="padding: 40px 29px 50px 50px; width: 260px">
            <div class="tit_right u-flex">
              <div
                class="radius"
                :class="{ radiusCss: radShow == 'one' }"
                @click.stop="radClick('one')"
              >
                <div class="blue_dot" v-if="radShow == 'one'"></div>
              </div>
              <div :class="{ textCss: radShow == 'one' }">スタンプカード A</div>
            </div>
            <img src="@/static/index/typeA.png" style="width: 250px" alt="" />
            <div class="tit_right u-flex m-t-40">
              <div
                class="radius"
                :class="{ radiusCss: radShow == 'two' }"
                @click.stop="radClick('two')"
              >
                <div class="blue_dot" v-if="radShow == 'two'"></div>
              </div>
              <div :class="{ textCss: radShow == 'two' }">スタンプカード B</div>
            </div>
            <img src="@/static/index/typeB.png" style="width: 250px" alt="" />

            <div class="tit_right u-flex m-t-40">
              <div
                class="radius"
                :class="{ radiusCss: radShow == 'three' }"
                @click.stop="radClick('three')"
              >
                <div class="blue_dot" v-if="radShow == 'three'"></div>
              </div>
              <div :class="{ textCss: radShow == 'three' }">ポイントカード</div>
            </div>
            <img src="@/static/index/tiaoPreview.png" style="width: 250px" />
          </div>
          <div style="padding-top: 40px; line-height: 22px; font-size: 12px">
            <div style="font-weight: bold; font-size: 14px">
              [スタンプカードの特徴]
            </div>
            <div>
                ・スタンプでのポイント付与カード。 <br />
                ・スタンプ満了数は3個〜30個まで設定できます。 <br />
                ・１度に１〜９個までのスタンプ付与が可能。 <br />
                ・カード枠の色は自由に設定できます。 <br />
                ・付与した日付、担当者(設定任意)が表示されます。 <br />
                ・スタンプデザインはテンプレートから選択、または <br />
                　PNGファイルアップロードでオリジナルデザイン設
            </div>
           
            <div style="font-weight: bold; margin-top: 50px; font-size: 14px;visibility: hidden;">
              [ポイントカードの特徴]
            </div>
            <div style="visibility: hidden;">
                ・バーコードによるカード情報の読み込み。 <br />
                ・利用金額に合わせたポイント付与が可能。 <br />
                ・ポイント利用による増減もアプリからできます。 <br />
                ・現在お使いの他社会員システムのバーコード情報の<br />&nbsp;&nbsp;&nbsp;&nbsp;入力で、デジタル会員カードの作成が可能。<br />
                ・バーコード情報(番号)は編集できます。 <br />
                ・ポイント付与無し、ポイント数の非表示設定ができます。
            </div>
            
            
            <div style="font-weight: bold; margin-top: 50px; font-size: 14px">
              [ポイントカードの特徴]
            </div>
            <div>
                ・バーコードによるカード情報の読み込み。 <br />
                ・利用金額に合わせたポイント付与が可能。 <br />
                ・ポイント利用による増減もアプリからできます。 <br />
                ・現在お使いの他社会員システムのバーコード情報の<br />&nbsp;&nbsp;&nbsp;&nbsp;入力で、デジタル会員カードの作成が可能。<br />
                ・バーコード情報(番号)は編集できます。 <br />
                ・ポイント付与無し、ポイント数の非表示設定ができます。
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- プレビュー -->
    <div class="fixed" v-if="breadShow">
      <div class="breadRoll">
        <div class="bread_head">
          <div class="bread_text">プレビュー</div>
          <div
            class="bread_error shou u-flex u-row-center"
            @click.stop="breadShow = false"
          >
            ×
          </div>
        </div>
        <div class="bread_tit u-flex u-row-between">
          <img src="@/static/index/list.png" alt="" />
          <div>CARD BOX</div>
          <img src="@/static/index/aixin.png" />
        </div>
        <div style="padding: 34px 33px 0 33px">
          <div class="image"></div>
          <div class="store_name">店铺名</div>
        </div>
      </div>
    </div>
    <!-- 预览图片 -->
    <el-dialog :visible.sync="dialogVisible" :append-to-body="true">
      <img :src="dialogImageUrl" style="width: 70%" />
    </el-dialog>
    
    <div class="mask" v-if="showmask == true">
        <div class="maskContent" v-if="type == 'default'">
            <div class="maskTitle">
                {{cardName}}
            </div>
            <div class="masksubtitle">
                に初回登録時のカードを変更しますか？
            </div>
            <div class="cfm" @click="changedefault">
                変更する
            </div>
            <div class="cle" @click="showmask = false">
                キャンセル
            </div>
        </div>
        
        <div class="maskContent" v-if="type == 'del'">
            <div class="maskTitle">
                このカードを削除しますか？
            </div>
            <div class="cle" style="border-bottom: 1px solid #86868B;border-top: 1px solid #86868B;" @click="delThis">
                削除する
            </div>
            <div class="cfm" style="color: #1d1d1f;border: none;" @click="showmask = false">
                キャンセル
            </div>
        </div>
    </div>
    
  </div>
</template>

<script>
import {getCardList,changeDefault,deleteCard} from "@/http/api.js"
export default {
  data() {
    return {
      checkList: ["选中且禁用", "复选框 A"],
      choice: "スタンプカード A",
      shopName:"",
      cardIndex: 0,
      createCardShow: false, //メンバーズカードの種類を1つ選択してください。弹窗
      radShow: "one",
      fillInShow: false,
      breadShow: false,
      imgList: [],
      array:[],
      dialogImageUrl: "",
      dialogVisible: false,
      cates:['スタンプカード A','スタンプカード B','ポイントカード'],
      cardtype:0,
      cardName:"メンバーズカード名",
      id:"",
      type:"",                       
      showmask:false
    };
  },
  created() {
    let that = this
    that.getList()
  },
  methods: {
      toEdit(e){
         console.log(e) 
         this.$router.push("/editFirst?id="+e.id)
      },
      showCards(e){
         let that = this
          that.cardtype = 1
          that.createCardShow = true
      },
    getList(){
        let that = this
        let type = that.choice == 'スタンプカード A'? 1 : that.choice == 'スタンプカード B'? 2 : 3
        getCardList(type).then((res) => {
            console.log(res)
            if(res.code == 200){
                that.array = res.data.cards
            }
        })
    },
    // 新增会员卡
    addnew(){
        let that = this
        that.$router.push("/firstStep?type="+that.radShow+"&source=new")
        // console.log(that.radShow)
    },
    delteThis(e){
        console.log("e="+e)
       let that = this  
       that.showmask = true
       that.type = 'del'
       that.id = e.id
    },
    
    
    delThis(){
       let that = this 
       let data = {}
       data['id'] = that.id
       deleteCard(data).then((res) => {
           that.showmask = false
           if(res.code == 200){
               that.$message({
               	message: res.message,
               	type: 'success',
                offset:400
               });
               that.getList()
           }else{
               that.$message({
               	message: res.message,
               	type: 'error',
                 offset:400
               });
           }
       })
    },
    toNext(){
       let that = this
       let type = that.radShow
       if(type == 'three'){
           this.$router.push({
             path: "/markPrinting?type="+type+"&name="+that.shopName+"&image="+that.imgList[0],
           });
       }else{
           this.$router.push({
             path: "/markPrinting?type="+type+"&name="+that.shopName+"&image="+that.imgList[0],
           });
       }
    },
    selectChanged(e){
        let that = this
        if(e == 'スタンプカード A'){
            that.radShow = 'one'
        }else if(e == 'スタンプカード B'){
            that.radShow = "two"
        }else{
            that.radShow = "three"
        }
        this.getList()
    },
    change(e) {
      console.log(e, "eeeeeeeeeee");
    },
    changedefault(){
      let that = this 
      let id = that.id
    changeDefault(id).then((res) => {
        that.showmask = false
        if(res.code == 200){
            that.$message({
                message: res.message,
                type: 'success',
                offset:400
            });
            that.getList()
        }else{
            that.$message({
                message: res.message,
                type: 'error',
                offset:400
            });
        }
    })
    },
    cardClick(id,name) {
      let that = this
      that.cardName = name
      that.id = id
      that.type = "default"
      that.showmask = true
      
    },
    radClick(e) {
      console.log(this.cardtype)
      this.radShow = e;
      if(e == "one"){
          this.choice = 'スタンプカード A'
      }else if(e == 'two'){
          this.choice = 'スタンプカード B'
      }else{
          this.choice = 'ポイントカード'
      }
      this.getList()   
      setTimeout(() => {
           this.createCardShow = false
      },500)
    },
    createCardClick() {
      this.$router.push({
        path: "/markPrinting",
      });
    },
    handleAvatarSuccess(res, file) {
      this.imgList.push(res.data.path);
    },
    delPicture(i) {
      this.imgList.splice(i, 1);
      this.fileList.splice(i, 1);
    },
    //图片预览
    handlePictureCardPreview(item) {
      this.dialogImageUrl = item;
      this.dialogVisible = true;
    },
  },
};
</script>

<style lang="scss" scoped>
.maskTitle{
    width: 100%;
    text-align: center;
    font-family: Hiragino Sans, Hiragino Sans;
    font-size: 16px;
    color: #1D1D1F;
    box-sizing: border-box;
    padding: 20px 0;
    font-weight: bold;
}
.masksubtitle{
    font-family: Hiragino Sans, Hiragino Sans;
    font-size: 14px;
    color: #1D1D1F;
    text-align: center;
    width: 100%;
    padding-bottom: 20px;
    border-bottom: 1px solid #86868B;
}

.cfm{
    font-family: Hiragino Sans, Hiragino Sans;
    font-size: 16px;
    color: #007AFF;
    text-align: center;
    border-bottom: 1px solid #86868B;
    padding: 20px 0;
    cursor: pointer;
}
.cle{
  font-family: Hiragino Sans, Hiragino Sans;
  font-size: 16px;
  color: #D93025;
  text-align: center;  
   padding: 20px 0;
   cursor: pointer;
}
.mask{
    width: 100vw;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: rgba(29, 29, 31, .3);
    z-index: 199 !important;
    
    .maskContent{
        background: #fff;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
        width: 325px;
        border-radius: 10px;
    }
}
    
::v-deep .el-dialog {
  box-shadow: 0 0 0 0;
  background: transparent;
}
::v-deep .el-icon-close:before {
  content: "";
}
::v-deep .el-dialog__body {
  display: flex;
  align-items: center;
  justify-content: center;
}
::v-deep {
  .el-checkbox__inner::after {
    height: 8px;
    left: 6px;
    justify-content: space-between;
    align-items: center;
    top: 3px;
  }
  .el-checkbox__inner {
    width: 18px;
    height: 18px;
  }
}
::v-deep .el-upload .el-upload-dragger {
  width: 329px;
  height: 198px;
  border: none;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: transparent !important;
}
</style>

<style lang="scss" scoped>
.not_tit {
  width: 820px;
  padding: 0 0 22px 0;
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
  .btn_grey {
    width: 124px;
    height: 30px;
    color: #707070;
    font-size: 16px;
    font-weight: lighter;
    background: rgba(29, 29, 31, 0.1);
    border-radius: 15px 15px 15px 15px;
    border: 1px solid #707070;
  }
}
.iti:hover{
    color: #1a73e8 !important;
    text-decoration: underline;
}
.not_cont {
  width: 820px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  padding: 30px 40px 0px 40px;
  background: #fff !important;
  .tit {
    font-size: 16px;
    color: #1d1d1f;
    font-weight: bold;
    padding-bottom: 10px;
    border-bottom: 1px solid #d2d2d7;
  }
  .text_red {
    color: #d93025;
  }
  .text_black {
    color: #1d1d1f;
  }
  .text_grey {
    color: #707070;
  }
  .int {
    width: 740px;
    height: 40px;
    outline: 0;
      font-size: 16px;
    background: #fff;
    padding-left: 12px;
    margin-top: 9px;
    border-radius: 10px;
    border: 1px solid #d2d2d7;
  }
  ::-webkit-input-placeholder {
    /*Webkit browsers*/
    color: #aaa;
    font-size: 16px;
  }
  :-moz-placeholder {
    /*Mozilla Firefox 4 to 8*/
    color: #aaa;
    font-size: 16px;
  }
  ::moz-placeholder {
    /*Mozilla Firefox 19+*/
    color: #aaa;
    font-size: 16px;
  }
  :-ms-input-placeholder {
    /*Internet Explorer 10+*/
    color: #aaa;
    font-size: 16px;
  }

  .card {
    margin-top: 37px;
    font-size: 16px;
    color: #1d1d1f;
    font-weight: bold;
    padding-bottom: 9px;
    border-bottom: 1px solid #d2d2d7;
    .new_card {
      width: 176px;
      height: 40px;
      background: #fff;
      border-radius: 20px;
      font-size: 16px;
      color: #1a73e8;
      font-weight: bold;
      border: 1px solid #1a73e8;
    }
    
    .new_card:hover{
        background: rgba(26,115,232,0.2) !important;
    }
  }
  
  .blueborder{
      box-shadow: 0px 0px 6px 1px rgba(26,115,232, 0.6) !important;
  }
  
  .card_item {
    width: 740px;
    height: 120px;
    background: #fff;
    margin-top: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0px 0px 6px 1px rgba(29, 29, 31, 0.3);
    border-radius: 10px;
    padding: 10px 20px 10px 15px;
    cursor: pointer;
    .box {
      width: 18px;
      height: 18px;
      border-radius: 5px;
      background: #ffffff;
      border: 1px solid #aaa;
    }
    .boxCss {
      background: #1a73e8;
      border: 1px solid #1a73e8;
    }
    .img {
      width: 168px;
      height: 100px;
      background: #fff;
      margin: 0 15px;
      box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
    }
    .card_text {
      font-size: 14px;
      color: #1d1d1f;
      .card_bg {
        width: 100px;
        height: 20px;
        margin-left: 22px;
        box-shadow: 0px 0px 3px 1px rgba(0, 0, 0, 0.09);
        border-radius: 4px 4px 4px 4px;
      }
      .green {
        background: #53b1af;
      }
      .yellow {
        background: #d9b625;
      }
      .grey {
        background: #f5f5f7;
      }
    }
    
    .uRed:hover{
        background: rgba(217,48,37, 0.2);
    }
    
    .utilize {
      width: 64px;
      height: 32px;
      font-size: 12px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .uRed {
      color: #d93025;
      margin-left: 10px;
      border: 1px solid #d93025;
    }
    .uGrey {
      color: #707070;
      border: 1px solid #707070;
    }
  }
  .img-bos {
    .img-boxs {
      margin-bottom: 20px;
    }
    .img-item:nth-child(3n) {
      margin-right: 0 !important;
    }
    .img-item {
      margin-top: 10px;
      width: 237px;
      height: 160px;
      position: relative;
      margin-right: 9px;
      border: 1px solid #aaa;

      .colse {
        position: absolute;
        right: 8px;
        top: 8px;
        width: 20px;
        height: 20px;
      }
      .tu {
        width: 237px;
        height: 160px;
        object-fit: cover;
        border: 1px solid #aaaaaa;
      }
      .moren {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        margin: auto;
        width: 30px;
        height: 24px;
      }
    }
    .upload-demo {
      .el-upload {
        width: 100%;
        .el-upload-dragger {
          width: 100%;
          border: 0;
        }
        .tuo {
          height: 198px;
          width: 100%;
          background: #f7faff;
          border-radius: 10px 10px 10px 10px;
          opacity: 1;
          display: flex;
          align-items: center;
          flex-direction: column;
          justify-content: center;
          border: 1px solid #2699fb;

          img {
            // margin-top: 25px;
            width: 30px;
            height: 24px;
          }
          .txt {
            margin-top: -10px;
            font-size: 16px;
            color: #2699fb;
          }
        }
      }
    }
    .add {
      margin-bottom: 20px;
      width: 140px;
      height: 53px;
      font-size: 14px;
      color: #2699fb;
      background: #ffffff;
      .upload-demo {
        width: 100%;
        height: 40px;

        .el-upload {
          width: 100%;
          height: 40px;
        }
      }
      ::v-deep .el-upload .el-upload-dragger {
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
      }

      .zhui {
        width: 140px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        margin-top: 20px;
        border: 1px solid #2699fb;

        .fu {
          font-size: 14px;
          font-weight: 700;
          color: #2699fb;
        }
      }
    }
  }
  .requirement {
    margin-top: 46px;
    font-size: 12px;
    color: #707070;
    line-height: 18px;
  }
  .footer {
    margin-top: 50px;
    padding: 30px 0 70px 0;
    border-top: 1px solid #d2d2d7;
    .btn {
      width: 200px;
      height: 50px;
      font-size: 20px;
      color: #fff;
      margin: 0 auto;
      background: #1a73e8;
      border-radius: 10px;
    }
  }
}
::v-deep .el-input__inner {
  width: 329px;
  padding-left: 10px;
  background: #f5f5f7;
  border-radius: 10px;
  border: 1px solid #707070;
}
.fixed {
  position: fixed;
  top: 0%;
  left: 0%;
  width: 100vw;
  height: 100vh;
  background: rgba(29, 29, 31, 0.2);
  z-index: 10007 !important;
  .read_display {
    width: 753px;
    height: 750px;
    position: fixed;
    top: 50%;
    left: 50%;
    border-radius: 5px;
    transform: translate(-50%, -50%);
    background: #fff;
    box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
  }

  .header {
    width: 100%;
    height: 61px;
    background: linear-gradient(180deg, #edeced 0%, #d2d1d2 100%);
    background: #f5f5f7;
    border-radius: 5px 4px 0px 0px;
    padding: 0 17px;
    text-align: right;
    .head_text {
      font-size: 21px;
      color: #1d1d1f;
      font-weight: bold;
    }

    .error {
      width: 30px;
      height: 30px;
      font-size: 30px;
      color: #1d1d1f;
      border-radius: 50%;
      background: rgba(29, 29, 31, 0.1);
    }
  }
  .radius {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    margin-right: 14px;
    background: #fff;
    border: 1px solid #747070;

    .blue_dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin: 2px auto;
      background: #1a73e8;
    }
  }
  .radiusCss {
    border: 1px solid #1a73e8;
  }

  .tit_right {
    width: 315px;
    color: #aaaaaa;
    font-size: 16px;
    padding: 0 0 8px 6px;
    .textCss {
      color: #1d1d1f;
      font-weight: bold;
    }
  }
  // プレビュー
  .breadRoll {
    width: 300px;
    height: 647px;
    background: #fff;
    border-radius: 20px;
    position: fixed;
    top: 50%;
    left: 50%;
    border-radius: 5px;
    transform: translate(-50%, -50%);
    .bread_head {
      text-align: center;
      padding: 13px 0;
      position: relative;
      border-bottom: 1px solid #dedee2;
      .bread_text {
        font-weight: bold;
        font-size: 18px;
      }
      .bread_error {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 30px;
        height: 30px;
        font-size: 30px;
        color: #757576;
        border-radius: 50%;
        background: #e4e4e6;
      }
    }

    .bread_tit {
      padding: 10px;
      border-bottom: 1px solid #dedee2;
      font-size: 20px;
      img {
        width: 30px;
      }
    }
    .image {
      width: 110px;
      height: 65px;
      object-fit: cover;
      box-shadow: 0px 0px 3px 1px rgba(0, 0, 0, 0.16);
    }
    .store_name {
      width: 110px;
      margin-top: 4px;
      font-weight: 600;
      color: #1d1d1f;
      font-size: 12px;
      text-align: center;
    }
  }
}
</style>
