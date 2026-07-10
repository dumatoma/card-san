<template>
  <div>
    <div class="title">
      {{ query.type == 'edit' ? "スタッフ情報編集" : "スタッフ登録" }}
    </div>
    <div class="cont">
      <div class="cont_one">
        <div class="one_tit">添付画像(最大4枚)</div>
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
        </div>
      </div>
      <div class="cont_one">
        <div class="u-flex text_black">
          <div class="text_red">※</div>
          氏名
        </div>
        <input type="text" class="int no-size" v-model="name" />
        <div class="u-flex text_black m-t-30">役職・ポジション</div>
        <input type="text" class="int no-size" v-model="position" />
        <div class="u-flex text_black m-t-30">コメント・アピール</div>
        <textarea type="text" class="tarea m-t-10 no-size" v-model="des" />
      </div>
      <div class="cont_one">
        <div class="u-flex no-size">
          <div class="radius" :class="{ radiusCss: show }" @click="showClick">
            <div class="blue_dot" v-if="show"></div>
          </div>
          <div
            style="font-size: 16px"
            :style="
              show ? 'color: #1d1d1f;font-weight: bold;' : 'color: #707070;'
            "
          >
            店舗情報ページに掲載する
          </div>
        </div>
        <div class="u-flex sale_grey">
          <div class="text_red">※</div>
          OFFにするとこのスタッフの情報は店舗情報ページに掲載されません
        </div>
      </div>
      <div
        class="bottom u-flex u-row-center"
        v-if="query.type == 'edit'"
      >
        <div class="btn grey u-flex-center" @click="goBack">戻　る</div>
        <div class="btn light_blue u-flex-center" @click="saveData">保　存</div>
      </div>
      <div class="bottom" v-else>
          <div class="btn grey u-flex-center" @click="goBack">戻　る</div>
        <div class="btn light_blue u-flex-center" @click="keepClick">保　存</div>
      </div>
    </div>
    <!-- 预览图片 -->
    <el-dialog :visible.sync="dialogVisible" :append-to-body="true">
      <img :src="dialogImageUrl" style="width: 70%" />
    </el-dialog>
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
import {addStaff,getStaffList,editStaff} from "@/http/api.js"
export default {
  data() {
    return {
      imgList: [],
      fileList: [],
      dialogImageUrl: "",
      dialogVisible: false,
      show: true,
      editShow: false,
	  keepShow:false,
      name:"",
      position:"",
      des:""
    };
  },
  created() {
    let that = this
    let query = that.$route.query
    that.query = query
    if(query.type == "add"){
        
    }else{
        that.getList()
    }
  },
  methods: {
      goBack(){
        history.go(-1)  
      },
      getList(){
        let that = this
        let data = {}
        data["startTime"] = ""
        data["endTime"] = ""
        data["id"] = ""
        getStaffList(data).then((res) => {
            console.log(res)
            if(res.code == 200){
                res.data.admins.forEach((item,index) => {
                    if(that.query.id == item.id){
                        console.log(item)
                        that.imgList = item.image
                        that.name = item.name
                        that.position = item.post
                        that.des = item.self_introduction
                        that.show = item.is_show_shop == 1? true : false
                    }
                })
            }
        })
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
    showClick() {
      this.show = !this.show;
    },
    saveData(){
      let that = this
      if(that.name == ''){
          that.$message({
              message:"氏名を入力してください",
              type:"error",
              offset:400
          })
          return
      }
      let data = {}
      data['name'] = that.name
      data["post"] = that.position
      data['self_introduction'] = that.des
      data['is_show_shop'] = that.show?1:0
      data['image'] = that.imgList
      data['_method'] = 'put'
      data["id"] = that.query.id
      editStaff(data).then((res) => {
          if(res.code == 200){
              that.keepShow = true
              setTimeout(()=>{
              	that.keepShow= false
                  this.$router.push({
                    path: "/registration",
                  });
              },1500)
          }else{
              that.$message.error(res.message)
          }
      })  
    },
	keepClick(){
        let that = this
        if(that.name == ''){
            that.$message({
                message:"氏名を入力してください",
                type:"error",
                offset:400
            })
            return
        }
        let data = {}
        data['name'] = that.name
        data["post"] = that.position
        data['self_introduction'] = that.des
        data['is_show_shop'] = that.show?1:0
        data['image'] = that.imgList
        addStaff(data).then((res) => {
            console.log(res)
            if(res.code == 200){
                that.keepShow = true
                setTimeout(()=>{
                	that.keepShow= false
                    this.$router.push({
                      path: "/registration",
                    });
                },1500)
            }else{
                that.$message.error(res.message)
            }
        })
		
	}
  },
};
</script>

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
.light_blue:hover{
    opacity: 0.6 !important;
    cursor: pointer;
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
</style>
<style lang="scss" scoped>
.title {
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
}
.grey:hover{
      color: #1D1D1F;
      background: rgba(29,29,31,0.1) !important;
      cursor: pointer;
    }
.cont {
  width: 820px;
  background: #fff;
  margin-top: 20px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .cont_one {
    padding: 40px;
    border-bottom: 1px solid #d2d2d7;
    .onr_tit {
      font-size: 16px;
      color: #707070;
    }
    .img-bos {
      .img-boxs {
        margin-bottom: 10px;
      }
      .img-item:nth-child(4n) {
        margin-right: 0 !important;
      }
      .img-item {
        margin-top: 10px;
        width: 177px;
        height: 237px;
        position: relative;
        margin-right: 7px;
        border: 1px solid #aaa;

        .colse {
          position: absolute;
          right: 8px;
          top: 8px;
          width: 20px;
          height: 20px;
        }
        .tu {
          width: 177px;
          height: 237px;
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
        margin-top: 20px;
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
          padding-top: 20px;
          height: 66px;
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
          border: 1px solid #2699fb;

          .fu {
            font-size: 14px;
            font-weight: 700;
            color: #2699fb;
          }
        }
      }
    }
    .text_black {
      color: #707070;
      font-size: 16px;
    }
    .text_red {
      color: #d93025;
    }
    .int {
      width: 329px;
      height: 40px;
      margin-top: 5px;
      outline: 0;
      padding: 14px;
      font-size: 16px;
      border-radius: 10px;
      background: #fff;
      border: 1px solid #aaa;
    }
    .tarea {
      width: 740px;
      height: 100px;
      outline: 0;
      padding: 14px;
      font-size: 16px;
      background: #fff;
      border-radius: 10px;
      border: 1px solid #aaa;
    }

    .radius {
      width: 18px;
      height: 18px;
      margin-right: 14px;
      border-radius: 50%;
      background: #fff;
      border: 1px solid #707070;

      .blue_dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        margin: 2px auto;
        background: #1a73e8;
      }
    }
    .radiusCss {
      border: 1px solid #1a73e8;
    }

    .sale_grey {
      font-size: 12px;
      margin-top: 5px;
      color: #707070;
      margin-left: 34px;
    }
  }
  .bottom {
    padding: 30px 0 70px 0;
    display: flex;
    justify-content: center;
    .btn {
      width: 200px;
      height: 50px;
      font-size: 20px;
      color: #fff;
      border-radius: 10px;
    }
    .blue {
      margin: 0 auto;
      background: #1a73e8;
    }
    .grey {
      color: #1D1D1F;
      background: rgba(29,29,31,0.2);
    }
    .light_blue {
     background: #1a73e8;
	 margin-left:41px;
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
