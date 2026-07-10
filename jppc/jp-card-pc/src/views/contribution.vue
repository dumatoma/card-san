<template>
  <div @click="releaseShow = false">
    <div class="path" v-if="info">TOP > お知らせ > お知らせ編集</div>
    <div class="not_tit u-flex u-row-between">
      お知らせ編集
      <div
        class="btn_grey shou u-flex u-row-center"
        @click.stop="goContribution"
      >
        Preview
      </div>
    </div>
    <div class="not_cont">
      <div
        style="padding: 50px 40px 40px 40px; border-bottom: 1px solid #d2d2d7"
      >
        <div class="not_top u-flex">
          <div style="color: #d93025">※</div>
          タイトル
        </div>
        <input
          class="int"
          type="text"
          placeholder-style="color:#AAAAAA;"
          placeholder="タイトルを入力"
          v-model="title"
        />

        <div class="add-pictur m-t-50 no-size">
          <div class="pic-tit">添付画像(最大6枚)</div>

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
                <img :src="item" class="tu" v-if="item" style="object-fit: cover;" @click="handlePictureCardPreview(item)"/>
                <img v-else src="@/static/index/image.png" class="moren" />
              </div>
            </div>
            <el-upload
              class="upload-demo"
              accept="image/*"
              action="https://api.card-san.jp/tool/upload/file"
              :on-success="handleAvatarSuccess"
              drag
              :limit="6"
              :show-file-list="false"
              :file-list="fileList"
              name="file"
            >
              <div class="tuo no-size">
                <img src="../static/edit-image.png" alt="" style="object-fit: contain;" />
                <br />
                <span class="txt">クリックorファイルをドロップ</span>
              </div>
            </el-upload>
            <!-- <div class="add">
              <el-upload
                class="upload-demo"
                accept="image/*"
                action="https://hyk.ljcyh.com/tool/upload/file"
                :on-success="handleAvatarSuccess"
                :limit="6"
                :show-file-list="false"
                :file-list="fileList"
                name="file"
              >
                <div class="zhui">
                  <span class="el-icon-plus" style="font-weight: 700"></span>
                  ファイル追加
                </div>
              </el-upload>
            </div> -->
          </div>
        </div>

        <div class="detailed">詳細</div>
        <textarea class="tarea no-size" v-model="remark"></textarea>
        <div class="u-flex" style="margin-top: 42px">
          <div
            class="radius"
            :class="[show ? 'radiusCss' : '']"
            @click.stop="showClick"
          >
            <div class="blue_dot" v-if="show"></div>
          </div>
          <div :style="!show?'font-size: 16px; color: #707070' : 'color: #1d1d1f !important;font-weight:bold'  ">固定メッセージ設定</div>
        </div>
        <div class="tip">
          オンにすると、１番上にこのメッセージが固定表示されます。
        </div>
      </div>

      <div style="padding: 30px 0 70px 0">
        <div class="btn shou info" v-if="info" @click.stop="releaseClick()">
          保　存
        </div>
        <div class="u-flex u-row-center" v-else>
          <div class="btn shou left" @click.stop="goabck">キャンセル</div>
          <div class="btn shou right" @click.stop="releaseClick">
            投　稿
          </div>
        </div>
      </div>
    </div>
    <!-- 保存/投稿弹窗 -->
    <div class="fixed" v-show="releaseShow">
      <div class="release u-flex-col u-col-center u-row-center">
        <img src="@/static/index/sign_blue.png" v-if="info" class="sign" />
        <img src="@/static/index/sign.png" v-else class="sign" />
        <div>{{ info ? "保存しました" : "投稿しました" }}</div>
      </div>
    </div>
    <!-- 预览 -->
    <div class="fixed" v-show="previewShow">
      <div class="zhe"></div>
      <div class="per-box">
        <div class="tit">
          <img
            class="shou"
            @click="previewShow = false"
            src="../static/index/close.png"
            alt=""
          />
          <div class="t">プレビュー</div>
        </div>
        <el-carousel height="300px" indicator-position="outside" arrow="never">
          <el-carousel-item v-for="(item, index) in imgList" :key="index">
            <img style="width: 100%; height: 100%;object-fit: cover;" :src="item" alt="" />
          </el-carousel-item>
        </el-carousel>
        <div class="phone u-">
          <div class="u-flex m-b-10">
           <!-- <div class="img u-flex-center">
              <img src="@/static/index/buser.png" alt="" />
            </div> -->
            <div class="right">
              <div style="font-weight: bold; margin-bottom: 5px; font-size: 14px" v-text="name">
               <!-- TOP HAIR Spa&Resort(店铺名) -->
              </div>
              <div class="u-flex">
                <div style="color: #b6b6b8; font-size: 12px" v-text="fdate"></div>
                <div style="color: #70a7f0; font-size: 12px; margin-left: 20px" v-if="show">
                  固定されたメッセージ
                </div>
              </div>
              <div style="font-weight: bold; margin-bottom: 5px; font-size: 16px" v-text="title"></div>
            </div>
          </div>
          <!-- <div class="ph_content" v-text="title"></div> -->
          <div class="container">
            <div style="white-space: pre-wrap;">
              <span>{{remark}}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- 预览图片 -->
    <el-dialog :visible.sync="dialogVisible" :append-to-body="true"  >
      <img  :src="dialogImageUrl" style="width:70%;" />
    </el-dialog>
  </div>
</template>

<script>
import {addNoticeList,getNoticeList,editNoticeList,getStore,getConfig} from "@/http/api.js"
export default {
  data() {
    return {
      show: false,
      releaseShow: false,
      info: false, //false投稿  true保存
      previewShow: false,
      title:"",
      remark:"",
      list1:
        "当店で取り扱いのワフックスorトリートメントのどもらかをプレゼントいたします。テキス",
      isfold: true,
      isflag: 1,
      imgList: [],
      fileList: [],
      dialogImageUrl: "",
      dialogVisible: false,
      query:{},
      name:"",
      fdate:""
    };
  },
  created() {
    let that = this
    let query = that.$route.query
    that.query = query
    let id = query.id
    getConfig().then((res) => {
        console.log("config",res)
        if(res.code == 200){
            let date = new Date(res.data.config.timestamp * 1000); // 时间戳转换成Date对象
              let year = date.getFullYear(); // 获取年
              let month = date.getMonth() + 1; // 获取月（月份比实际小1，所以需要+1）
              let day = date.getDate(); // 获取日
              let hours = date.getHours(); // 获取小时
              let minutes = date.getMinutes(); // 获取分钟
              let seconds = date.getSeconds(); // 获取秒钟
             
              // 补零操作
              month = month < 10 ? '0' + month : month;
              day = day < 10 ? '0' + day : day;
              hours = hours < 10 ? '0' + hours : hours;
              minutes = minutes < 10 ? '0' + minutes : minutes;
              seconds = seconds < 10 ? '0' + seconds : seconds;
             
              that.fdate = `${year}/${month}/${day} ${hours}:${minutes}`; // 返回格式化时间字符串
        }
    })
    getStore().then((res) => {
        console.log("store",res)
        if(res.code == 200){
            that.name = res.data.shop_info.shop_name
        }
    })
    if(id == "000"){

    }else{
        that.info = true
        that.getDetail(id)
    }
  },
  methods: {
      goabck(){
         history.go(-1) 
      },
      getDetail(id){
          let that = this
          getNoticeList().then((res) => {
              console.log(res)
              if(res.code == 200){
                  res.data.notices.forEach((val,index) => {
                      console.log(val)
                      if(val.id == that.query.id){
                          that.title = val.title
                          that.remark = val.content
                          that.show = val.is_top == 1? true : false
                          that.imgList = val.images
                      }
                  })
              }
          })
      },
    goSet() {
      this.$router.push({
        path: "/noticeSet",
      });
    },
    goContribution() {
      this.previewShow = true;
    },
    showClick() {
      this.show = !this.show;
    },
    open() {
      this.isfold = !this.isfold;
      this.isfold === true ? (this.isflag = 1) : (this.isflag = 2);
    },
    handleAvatarSuccess(res, file) {
      this.imgList.push(res.data.path);
      this.fileList.push({ url: res.data.path });
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
    releaseClick(){
        let that = this
        let data = {}
        data["images"] = that.imgList
        data["title"] = that.title
        data["content"] = that.remark
        data["is_top"] = that.show == true ? 1 : 0
        if(that.query.id == "000"){
            addNoticeList(data).then((res) => {
                if(res.code == 200){
                    that.releaseShow = true
                    setTimeout(()=>{
                      that.$router.push({
                        path: "/notice",
                      });
                    },2000)
                }else{
                    that.$message({
                    	message: res.message,
                    	type: 'error',
                      offset:400
                    });
                }
            })
        }else{
            data["_method"] = "put"
            editNoticeList(data,that.query.id).then((res) => {
                if(res.code == 200){
                    that.releaseShow = true
                    setTimeout(()=>{
                      that.$router.push({
                        path: "/notice",
                      });
                    },2000)
                }else{
                    that.$message({
                    	message: res.message,
                    	type: 'error',
                      offset:400
                    });
                }
            })
        }
    }
  },
};
</script>


<style scoped>
::v-deeo .is-dragover{
    background: red !important;
}
    
::v-deep .el-dialog{
  box-shadow: 0 0 0 0;
  background: transparent;
}
::v-deep .el-icon-close:before{
  content: '';
}
::v-deep .el-dialog__body{
  display: flex;
  align-items: center;
 justify-content: center;
}
::v-deep .el-carousel__button {
  /* // 默认按钮的颜色大小等 */
  width: 7px;
  height: 7px;
  border: none;
  border-radius: 50%;
  background: #b5b5b5;
  opacity: 1;
}

::v-deep .is-active .el-carousel__button {
  /* // 选中图片的颜色 */
  background: #000;
  opacity: 1;
}
</style>
<style lang="scss">
.el-upload .el-upload-dragger {
  width: 740px;
  height: 100px;
  border: none;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.el-upload--picture-card {
  width: 240px;
  height: 160px;
  margin-bottom: 10px;
  background: rgba(0, 0, 0, 0.07);
  border: 1px solid #aaa;
  position: relative;
  border-radius: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  .error {
    width: 20px;
    height: 20px;
    font-size: 19px;
    color: #fff;
    border-radius: 50%;
    background: rgba(29, 29, 31, 0.5);
    position: absolute;
    top: 8px;
    right: 8px;
  }
}
</style>
<style lang="scss" scoped>
.not_tit {
  width: 820px;
  font-size: 16px;
  color: #1d1d1f;
  font-weight: bold;
  padding: 0px 0 22px 0;

  .btn_grey {
    width: 124px;
    height: 30px;
    color: #707070;
    font-weight: lighter;
    background: rgba(29, 29, 31, 0.1);
    border-radius: 15px 15px 15px 15px;
    border: 1px solid #707070;
  }
}
.not_cont {
  width: 816px;
  background: #fff;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .not_top {
    font-size: 16px;
    color: #757575;
    font-weight: bold;
  }
  .int {
    width: 100%;
    outline: 0;
    margin-top: 20px;
    border: none;
    padding-bottom: 5px;
    border-bottom: 1px solid #aaa;
  }

  .add-pictur {
    width: 100%;
    .pic-tit {
      font-size: 16px;
      color: #707070;
    }
    .des {
      font-size: 12px;
      color: #707070;
      margin-bottom: 10px;
    }
    .img-bos {
      .img-boxs {
        margin-bottom: 10px;
      }
      .img-item:nth-child(3n) {
        margin-right: 0 !important;
      }
      .img-item {
        margin-top: 10px;
        width: 240px;
        height: 240px;
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
          width: 100%;
          height: 100%;
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
        height: 100px;
        .el-upload {
          width: 100%;
          height: 100px;
          .el-upload-dragger {
            width: 100%;
            height: 100px;
            border: 0;
          }
          .tuo {
            height: 100px;
            width: 100%;
            background: #f7faff;
            border-radius: 10px 10px 10px 10px;
            opacity: 1;
            border: 1px solid #2699fb;
            img {
              margin-top: 25px;
              width: 30px;
              height: 24px;
            }
            .txt {
              margin-top: 10px;
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
        .zhui:hover{
           background: rgba(26,115,232,0.1);
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
  }

  .detailed {
    margin-top: 64px;
    font-size: 16px;
    color: #86868b;
    font-weight: bold;
  }
  .tarea {
    width: 100%;
    height: 200px;
    outline: 0;
    margin-top: 10px;
    background: #ffffff;
    border-radius: 10px;
    padding: 10px;
    border: 1px solid #aaa;
  }
  textarea::-webkit-input-placeholder {
    /* WebKit browsers */
    color: #aaa;
  }
  textarea:-moz-placeholder {
    /* Mozilla Firefox 4 to 18 */
    color: #aaa;
  }
  textarea::-moz-placeholder {
    /* Mozilla Firefox 19+ */
    color: #aaa;
  }
  textarea::-ms-input-placeholder {
    /* Internet Explorer 10+ */
    color: #aaa;
  }
  .radius {
    width: 16px;
    height: 16px;
    margin-right: 14px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #707070;

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
  .tip {
    font-size: 14px;
    color: #aaaaaa;
    margin: 12px 0 0 33px;
  }
  .btn {
    width: 200px;
    height: 50px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
  }
  .left {
    color: #707070;
    background: rgba(0, 0, 0, 0.07);
  }
  .right {
    color: #fff;
    margin-left: 50px;
    background: #2699fb;
  }
  .info {
    color: #fff;
    margin: 0 auto;
    background: #1a73e8;
  }
}
.fixed {
  position: fixed;
  top: 0%;
  left: 0%;
  width: 100vw;
  height: 100vh;
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
    color: #707070;
    transform: translate(-50%, -50%);
    box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
    .sign {
      width: 50px;
      height: 50px;
      margin-bottom: 20px;
    }
  }
  .zhe {
    position: absolute;
    width: 100vw;
    height: 100vh;
    background: rgba(29, 29, 31, 0.2);
  }
  .per-box {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 300px;
    height: 647px;
    background: #fff;
    border-radius: 20px;
    .tit {
      position: relative;
      font-size: 20px;
      font-weight: 700;
      width: 100%;
      height: 40px;
      line-height: 40px;
      text-align: center;
      background-color: #fff;
      border-radius: 20px 20px 0 0;

      img {
        position: absolute;
        left: -13px;
        top: -14px;
        width: 30px;
        height: 30px;
      }
    }
    .phone {
      padding: 10px 15px;
      // border:1px solid red;
      .img {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #eaeaea;
      }
      .ph_content {
        font-weight: bold;
        font-size: 14px;
        width: 100%;
        word-break: break-word;
      }
      .container {
        color: #a7a7aa;
        font-size: 12px;
        margin-top: 10px;
        width: 100%;
        word-break: break-word;
        border-bottom: 2px solid #f4f4f4cc;
        padding-bottom: 30px;
        .font {
          color: #70a7f0;
          cursor: pointer;
        }
      }
    }
    .right {
      // margin-left: 13px;
    }
  }
}
</style>
