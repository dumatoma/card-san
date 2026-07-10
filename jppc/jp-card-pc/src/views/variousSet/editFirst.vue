<template>
  <div>
    <div class="not_tit u-flex u-row-between">
      メンバーズカード
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
        v-model="info.shop_name"
      />
      <div class="m-t-50" v-if="fillInShow">
        <div class="tit u-flex">
          <div class="text_red">※</div>
          カードのアイコン画像をアップロード
        </div>

        <div class="img-bos">
          <div class="img-boxs u-flex u-flex-wrap">
            <div
              class="img-item"
              v-for="(item, index) in imgList"
              :key="index"
              :style="{ background: item ? '' : 'rgba(0,0,0,0.07)' }"
            >
              <img
                src="@/static/gClose.png"
                alt=""
                class="colse shou"
                @click="delPicture(index)"
              />
              <img
                :src="item"
                class="tu"
                v-if="item"
                @click="handlePictureCardPreview(item)"
              />
              <img v-else src="@/static/index/image.png" class="moren" />
            </div>
          </div>
          <el-upload
            v-if="imgList.length == 0"
            class="upload-demo"
            accept="image/*"
            action="https://api.card-san.jp/tool/upload/file"
            :on-success="handleAvatarSuccess"
            drag
            :limit="1"
            :show-file-list="false"
            :file-list="fileList"
          >
            <div class="tuo no-size">
              <img src="@/static/edit-image.png" style="width: 24px;" alt="" />
              <br />
              <span class="txt">クリックorファイルをドロップ</span>
            </div>
          </el-upload>
        </div>

        <div class="requirement">
          ・ファイル形式：JPG、JPEG、GIF、PDF<br />
          ・ファイルサイズ：1MB以下<br />
          ・推奨画像サイズ：1316px X 792px
        </div>
        <div class="footer">
            <div class="btn goba u-flex-center shou" @click="goback">戻　る</div>
          <div class="btn nxt u-flex-center shou" @click="toNext">次　へ</div>
        </div>
      </div>
    </div>

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
          <div style="font-weight: bold;">MY CARD</div>
          <img src="@/static/index/aixin.png" />
        </div>
        <div class="searchContent">   
            <div class="searchInner"> 
                <img src="../../static/index/search.png" alt="" />
                <div>お店を探す</div>
            </div>
            <div class="scancode">
                <img src="../../static/erwm.png" alt="" />
            </div>
        </div>
        <div style="padding: 34px 33px 0 33px">
          <div class="image">
              <img :src="imgList[0]" alt=""  style="width: 100%;height: 100%;object-fit: cover;border-radius: 6px;" />
          </div>
          <div class="store_name">{{info.shop_name}}</div>
        </div>
      </div>
    </div>
    <!-- 预览图片 -->
    <el-dialog :visible.sync="dialogVisible" :append-to-body="true">
      <img :src="dialogImageUrl" style="width: 70%" />
    </el-dialog>
  </div>
</template>

<script>
import {getCardInfo} from "@/http/api.js"
export default {
  data() {
    return {
     
      shopName:"",
      cardIndex: 0,
      createCardShow: false, //メンバーズカードの種類を1つ選択してください。弹窗
      radShow: "",
      fillInShow: true,
      breadShow: false,
      imgList: [],
      array:[],
      fileList:[],
      dialogImageUrl: "",
      dialogVisible: false,
      info:{},
      id:"",
      type:"",                       
      showmask:false,
    };
  },
  created() {
    let that = this
    let query = that.$route.query
    console.log("query",query)
    that.getDetail(query.id)
  },
  methods: {
    getDetail(e){
        let that = this 
        getCardInfo(e).then((res) => {
            console.log("detail;",res)
            if(res.code == 200){
                that.info = res.data.card
                that.imgList.push(res.data.card.image)
            }
        })
    },
    goback(){
      history.go(-1)  
    },
   
    toNext(){
       let that = this
       let type = that.info.type == 1?'one':that.info.type == 2?"two":"three"
       if(that.info.shop_name == ''){
           that.$message({
           	message: "アプリ名を入力してください",
           	type: 'error',
            offset: 400
           });
           return
       }
       if(that.imgList.length == 0){
           that.$message({
           	message: "アイコン画像をアップロードしてください",
           	type: 'error',
            offset: 400
           });
           return
       }
       that.$router.push({
         path: "/editCard?type="+type+"&name="+ encodeURIComponent(that.info.shop_name) +"&image="+that.imgList[0]+"&source=edit&id="+that.$route.query.id,
       });
    },
    selectChanged(e){
        console.log("e",e)
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
      
      if(this.cardtype == 2){
          this.radShow = e;
          setTimeout(() => {
              this.fillInShow = true
              // this.createCardShow = false
          },500)
      }else{
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
         
      }
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
.searchInner{
    flex: 1;
    background: #E5E5E9;
    display: flex;
    align-items: center;
    box-sizing: border-box;
    padding: 0 8px;
    border-radius: 6px;
    font-size: 12px;
    color: #848485;
    font-weight: bold;
    height: 30px;
    img{
        width: 16px;
        height: 16px;
        margin-right: 8px;
    }
}
.goba{
    background: rgba(29,29,31,0.2) !important;
    color: #707070 !important;
}
.nxt:hover{
    background: rgba(26, 115, 235, 0.6) !important;
}
.scancode{
    width: 40px;
    height: 40px;
    
    img{
        width: 100%;
        height: 100%;
    }
}
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
.searchContent{
    background: #f5f5f7;
    box-sizing: border-box;
    padding: 5px 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
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
    color: #1d1d1f;
    font-weight: bold;
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
      width: 329px;
      height: 198px;
      position: relative;
      margin-right: 9px;
      border: 1px solid #aaa;
      border-radius: 10px;
      .colse {
        position: absolute;
        right: 8px;
        top: 8px;
        width: 20px;
        height: 20px;
      }
      .tu {
        width: 329px;
        height: 198px;
        object-fit: cover;
        // border: 1px solid #aaaaaa;
        border-radius: 10px;
      }
      .moren {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        margin: auto;
        width: 24px !important;
        height: 24px;
        // object-fit: cover;
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
          width: 329px;
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
    margin-top: 20px;
    font-size: 12px;
    color: #707070;
    line-height: 18px;
  }
  .footer {
    margin-top: 50px;
    padding: 30px 0 70px 0;
    border-top: 1px solid #d2d2d7;
    display: flex;
    align-items: center;
    justify-content: center;
    .btn {
      width: 200px;
      height: 50px;
      font-size: 20px;
      color: #fff;
      margin: 0 30px;
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
      border-radius: 6px;
    }
    .store_name {
      width: 110px;
      margin-top: 4px;
      font-weight: 600;
      color: #1d1d1f;
      font-size: 10px;
      text-align: center;
    }
  }
}
</style>
