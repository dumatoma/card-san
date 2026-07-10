<template>
  <div>
    <div class="header u-flex u-row-between">
      <div class="tit" v-if="query.type == 'add'">メニュー登録</div>
      <div class="tit" v-else>メニュー編集</div>
      <div
        class="btn_grey shou u-flex u-row-center"
        @click="previewShow = true"
      >
        Preview
      </div>
    </div>
    <div class="cont">
      <div style="padding: 40px; border-bottom: 1px solid #d2d2d7">
        <div>カテゴリー</div>

        <el-select
          v-model="industry"
          @change="selectChanged"
          placeholder="選択してください"
        >
          <el-option
            v-for="item in industryList"
            :key="item.id"
            :label="item.name"
            :value="item.id"
          >
          </el-option>
        </el-select>

        <div class="not_top u-flex m-t-40">
          <div style="color: #d93025">※</div>
          メニュータイトル
        </div>
        <input
          class="int"
          type="text"
          placeholder-style="color:#AAA;"
          placeholder="メニュー名を入力してください"
          v-model="MenuName"
        />
        <div class="add-pictur m-t-40 no-size">
          <div class="pic-tit">添付画像（最大6枚）</div>

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
              class="upload-demo"
              accept="image/*"
              action="https://api.card-san.jp/tool/upload/file"
              :on-success="handleAvatarSuccess"
              drag
              :limit="6"
              :show-file-list="false"
              :file-list="fileList"
            >
              <div class="tuo no-size">
                <img src="@/static/edit-image.png" alt="" />
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
              >
                <div class="zhui">
                  <span class="el-icon-plus" style="font-weight: 700"></span>
                  ファイル追加
                </div>
              </el-upload>
            </div> -->
          </div>
        </div>
      </div>
      <div
        style="padding: 50px 40px 40px 40px; border-bottom: 1px solid #d2d2d7"
      >
        <div class="detailed">メニュー説明</div>
        <textarea
          class="tarea no-size"
          placeholder="メニューの詳細を入力してください。"
          v-model="des"
        ></textarea>
        <div class="detailed m-t-40">価格(税込)</div>
        <input class="intTwo no-size" v-model="price" placeholder="価格を入力" />
      </div>
      <div style="padding: 40px; border-bottom: 1px solid #d2d2d7">
        <div class="u-flex">
          <div
            class="radius"
            @click.stop="showClick"
            :class="{ radiusCss: show }"
          >
            <div class="blue_dot" v-if="show"></div>
          </div>
          <div
            style="font-size: 16px"
            :style="show ? 'color:#1d1d1f;font-weight:bold;' : 'color:#70707'"
          >
            予約メニューに設定
          </div>
        </div>
        <div class="u-flex m-l-30 m-t-20" style="font-weight: bold">
          所要時間(目安)
          <div
            class="u-flex m-l-10"
            style="font-size: 12px; font-weight: lighter"
          >
            <div style="color: #d93025">※</div>
            <div>施術にかかる時間を設定してください。</div>
          </div>
        </div>
        <el-select
          v-model="min"
          @change="minChanged"
          placeholder="時間を選択"
          class="selectTwo"
        >
          <el-option
            v-for="item in minList"
            :key="item.name"
            :label="item.name"
            :value="item.value"
          >
          </el-option>
        </el-select>
      </div>
      <div style="padding: 30px 0 70px 0">
        <!-- <div class="btn shou info" v-if="info" @click="saveData">
          保　存
        </div> -->
        <div class="u-flex u-row-center">
          <div class="btn shou left" @click="goback">戻　る</div>
          <div class="btn shou right" @click="saveData">保 存</div>
        </div>
      </div>
    </div>
    <!-- 保存弹窗 -->
    <div class="fixed" v-show="keepShow">
      <div class="zhe"></div>
      <div class="sus-box">
        <img src="@/static/success.png" alt="" />
        <div class="keep">保存しました</div>
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
            src="@/static/index/close.png"
            alt=""
          />
          <div class="t">メニュー</div>
        </div>

        <div class="phone">
          <div class="u-flex u-row-between m-b-10">
            <div class="right">
              <div
                style="font-weight: bold; margin-bottom: 5px; font-size: 14px"
              >
                {{MenuName}}
              </div>
              <div class="u-flex">
                <div style="color: #e67b74; font-size: 14px">{{price}}</div>
                <div style="font-size: 14px; margin-left: 20px" v-if="show">
                  所要時間:{{min}}
                </div>
              </div>
            </div>
            <div class="img u-flex-center">
              <img src="@/static/index/collection.png" alt="" />
            </div>
          </div>
          <div class="ph_content u-flex u-col-top">
            <div class="ph_img">
              <img src="@/static/index/heji.png" class="pclose" alt="" />
              <img class="osi" :src="imgList[0]" alt="" />
            </div>
            <div class="ph_text" v-text="des" style="white-space: pre-wrap;"></div>
          </div>
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
import {getMenuList,getCates,addMenu,editMenu} from "@/http/api.js"
export default {
  data() {
    return {
      show: false,
      des:"",
      price:"",
      MenuName:"",
      industryList: [],
      minList: [
          {
              name:"30分",
              value:30
          },{
              name:"60分",
              value:60
              
          },{
              name:"90分",
              value:90
          },{
              name:"120分",
              value:120
          },{
              name:"150分",
              value:150
          },{
              name:"180分",
              value:180
          },{
              name:"210分",
              value:210
          },{
              name:"240分",
              value:240
          }
      ],
      industry: "",
      min: "",
      info: {}, //用于判断
      keepShow: false,
      previewShow: false,
      imgList: [],
      fileList: [],
      dialogImageUrl: "",
      dialogVisible: false,
      query:{}
    };
  },
  created() {
    let that = this
    let query = that.$route.query
    that.query = query
    that.getCates()
    if(query.type == "add"){
        
    }else{
        console.log(query)
        getMenuList().then((result) => {
            console.log(result)
            if(result.code == 200){
                result.data.menus.forEach((item,index) => {
                    if(item.id == query.id){
                        that.industry = item.cid
                        that.imgList = item.image
                        that.min = item.order_time
                        that.MenuName = item.title
                        that.des = item.des
                        that.show = item.is_order == 1? true :false
                        that.price = item.price
                    }
                })
            }
        })
    }
  },
  methods: {
      goback(){
        history.go(-1)
      },
      getCates(){
        let that = this
        getCates().then((res) => {
            if(res.code == 200){
                that.industryList = res.data.cates
            }
        })
      },
      saveData(){
         let that = this
         
         if(that.MenuName == ''){
             that.$message({
             	message: "タイトルを入力してください",
             	type: 'error',
                offset:400
             });
             return 
         }
         
         if(that.industry == ''){
             that.$message({
             	message: "カテゴリーを選択してください",
             	type: 'error',
                offset:400
             });
             return 
         }
         
         let data = {}
         data['cid'] = that.industry
         data['image'] = that.imgList
         data["title"] = that.MenuName
         data['des'] = that.des
         data['price'] = that.price
         data['is_order'] = that.show? 1 : 0
         data['order_time'] = that.min
         if(that.query.type == "add"){
           addMenu(data).then((res) => {
               if(res.code == 200){
                   that.keepShow = true
                   setTimeout(()=> {
                       this.$router.push({
                         path: "/menuList",
                       });
                   },1500)
               }else{
                   that.$message({
                   	message: res.message,
                   	type: 'error',
                      offset:400
                   });
               }
           })  
         }else{
             data['id'] = that.query.id
             data['_method'] = 'put'
             editMenu(data).then((res) => {
                 if(res.code == 200){
                     that.keepShow = true
                     setTimeout(()=> {
                         this.$router.push({
                           path: "/menuList",
                         });
                     },1500)
                 }else{
                     that.$message.error(res.message)
                 }
             })
         }
         
      },
    showClick() {
      this.show = !this.show;
    },
    selectChanged(e) {
      console.log(e)
      this.industry = e;
    },
    minChanged(e) {
      this.min = e;
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
  },
};
</script>


<style lang="scss">
:v-deep .el-input__suffix{
    height: 114% !important;
}
:v-deep .el-input__inner{
    color: #1d1d1f !important;
}
.el-dialog {
  box-shadow: 0 0 0 0;
  background: transparent;
}
.el-icon-close:before {
  content: "";
}
.el-dialog__body {
  display: flex;
  align-items: center;
  justify-content: center;
}
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
.header {
  width: 820px;
  font-size: 16px;
  color: #1d1d1f;
}
.tit {
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
}

.btn_grey {
  width: 124px;
  height: 30px;
  color: #707070;
  font-weight: lighter;
  background: rgba(29, 29, 31, 0.1);
  border-radius: 15px 15px 15px 15px;
  border: 1px solid #707070;
}

::v-deep .el-input__inner {
  width: 329px;
  padding-left: 10px;
  background: #f5f5f7;
  border-radius: 10px;
  margin-top: 5px;
  border: 1px solid #707070;
}
.selectTwo {
  ::v-deep .el-input__inner {
    width: 200px;
    height: 40px;
    margin-left: 30px;
    background: rgba(29, 29, 31, 0.1);
    border-radius: 10px 10px 10px 10px;
    border: 1px solid #aaa;
  }
}
.cont {
  width: 820px;
  background: #fff;
  margin-top: 24px;

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

  // .add {
  //   font-size: 16px;
  //   color: #707070;
  //   font-weight: bold;
  //   margin-top: 50px;
  // }
  // .drag {
  //   width: 740px;
  //   height: 100px;
  //   background: #f7faff;
  //   font-size: 16px;
  //   color: #2699fb;
  //   margin-top: 10px;
  //   border-radius: 10px;
  //   border: 1px solid #2699fb;
  //   img {
  //     width: 30px;
  //     height: 24px;
  //     margin-bottom: 10px;
  //   }
  // }
  // .plus {
  //   width: 136px;
  //   height: 40px;
  //   background: #ffffff;
  //   padding: 0 14px;
  //   font-size: 14px;
  //   color: #2699fb;
  //   border-radius: 10px;
  //   margin-top: 20px;
  //   border: 1px solid #2699fb;
  // }
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
          opacity: 0.6 !important;
        }
        .tu {
          width: 100%;
          height: 100%;
          object-fit: cover;
          border: 1px solid #aaaaaa;
          object-fit: cover;
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
    font-size: 16px;
    color: #86868b;
    font-weight: bold;
  }
  .tarea {
    width: 100%;
    height: 100px;
    outline: 0;
    margin-top: 5px;
    background: #ffffff;
    border-radius: 10px;
    padding: 14px;
    font-size: 16px;
    border: 1px solid #aaa;
  }
  textarea::-webkit-input-placeholder {
    /* WebKit browsers */
    color: #aaa;
    font-size: 16px;
  }
  textarea:-moz-placeholder {
    /* Mozilla Firefox 4 to 18 */
    color: #aaa;
    font-size: 16px;
  }
  textarea::-moz-placeholder {
    /* Mozilla Firefox 19+ */
    color: #aaa;
    font-size: 16px;
  }
  textarea::-ms-input-placeholder {
    /* Internet Explorer 10+ */
    color: #aaa;
    font-size: 16px;
  }
  .intTwo {
    width: 200px;
    height: 40px;
    background: #ffffff;
    border-radius: 10px;
    padding: 0 10px;
    outline: 0;
    border: 1px solid #aaa;
    margin-top: 5px;
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
  .zhe {
    position: absolute;
    width: 100vw;
    height: 100vh;
    background: rgba(29, 29, 31, 0.2);
  }
  .sus-box {
    width: 160px;
    height: 146px;
    background: #ffffff;
    border-radius: 10px 10px 10px 10px;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    img {
      margin-top: 30px;
      width: 50px;
      height: 50px;
    }
    .keep {
      margin-top: 20px;
      font-size: 16px;
      color: #2699fb;
    }
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
      background: #f5f5f7;
      border-bottom: 2px solid #e9e9eb;
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
      border-bottom: 1px solid #dfdfdf;
      .img {
        width: 30px;
        height: 30px;
        border-radius: 50%;
      }
      .ph_content {
        font-size: 12px;
        width: 100%;
        word-break: break-word;
        .ph_img {
          width: 100px;
          height: 100px;
          border-radius: 5px;
          background: #f4f6ff;
          position: relative;
          .osi{
              width: 100%;
              height: 100%;
              object-fit: cover;
          }
          .pclose {
            top: 0;
            left: 0;
            position: absolute;
            background: #a7a9ae;
            padding: 4px;
            width: 23px;
            border-radius: 5px;
          }
        }
        .ph_text {
          width: 155px;
          margin-left: 10px;
          color: #1D1D1F;
        }
      }
    }
  }
}
</style>
