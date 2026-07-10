<template>
  <div>
    <div class="wrap no-size">
      <div class="top no-size">
        <div class="member u-flex">
          <img v-if="info.avatar" :src="info.avatar" alt="" style="object-fit: cover;" />
          <img v-else src="@/static/icon3.svg" alt="" />
          メンバー情報
        </div>
        <div class="member_info">
          <div class="member_item m-b-20">
            <div class="grey_left">ID</div>
            <div class="bor" style="border: none !important;">
				<input style="border: none;outline: none;font-size: 16px;color: 1d1d1f;" disabled v-model="info.member_id" type="text">
			</div>
          </div>
          <div class="member_item m-b-20">
            <div class="grey_left">顧客NO</div>
            <div class="bor">
				<input style="border: none;outline: none;font-size: 16px;color: 1d1d1f;" v-model="info.member_no" type="text">
			</div>
          </div>
          <div class="member_item m-b-20">
            <div class="grey_left">カードの種類</div>
            <div style="font-weight: bold" v-text="info.card_name" class="zl" @click="toCard"></div>
          </div>
          <div class="member_item m-b-20" @click="toNext">
            <div class="grey_left">来店回数</div>
            <div class="blue" v-text="total"></div>
          </div>
          <div class="member_item">
            <div class="grey_left">氏名</div>
            <div v-text="info.name"></div>
          </div>
          <div class="member_item">
            <div class="grey_left">カナ</div>
            <div v-text="info.pronunciation"></div>
          </div>
          <div class="member_item">
            <div class="grey_left">電話番号</div>
            <div v-text="info.phone"></div>
          </div>
          <div class="member_item">
            <div class="grey_left">性別</div>
            <div v-text="info.sex"></div>
          </div>
          <div class="member_item">
            <div class="grey_left">生年月日</div>
            <div>{{info.birthday_year}}年　{{info.birthday_month}}月　{{info.birthday_day}}日</div>
          </div>

          <div class="member_item" style="align-items: flex-start">
            <div class="grey_left">住所</div>
            <div>
              {{info.zip_code}}<br />{{info.province}} <br />{{info.address1}}
            </div>
          </div>
          <div class="member_item" style="align-items: flex-start">
            <div class="grey_left">備考</div>
            <div class="remarks no-size">
			  <textarea class="insertRemark" name="" id="" cols="30" rows="10" placeholder="お客さまのデータには反映されません" v-model="info.remark"></textarea>
            </div>
          </div>
        </div>
        <div class="add-pictur no-size m-t-40">
          <div class="pic-tit">添付画像(最大12枚)</div>
          <div class="des">
            アップロードしたデータは、お客様には反映されません。
          </div>
          <div class="img-bos">
            <div class="img-boxs u-flex u-flex-wrap">
              <div
                class="img-item"
                v-for="(item, index) in info.image"
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
              :limit="12"
              :show-file-list="false"
              :file-list="fileList"
			  name="file"
            >
              <div class="tuo no-size">
                <img src="../static/edit-image.svg" alt="" />
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
                drag
                :limit="12"
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
      </div>
      <div class="bottom no-size u-flex u-col-top u-row-center">
        <div class="button shou" @click="back">戻　る</div>
        <div class="button btn_blue shou" @click="keepClick">保 存</div>
      </div>
    </div>
    <!-- 保存弹窗 -->
    <div class="fixed" v-show="keepShow">
      <div class="zhe"></div>
      <div class="sus-box">
        <img src="../static/success.png" alt="" />
        <div class="keep">保存しました</div>
      </div>
    </div>
    <!-- 预览图片 -->
    <el-dialog :visible.sync="dialogVisible" :append-to-body="true">
      <img :src="dialogImageUrl" style="width: 70%" />
    </el-dialog>
  </div>
</template>

<script>
import { set } from 'vue';
import {getMemberDetail,uploadMemberImage,deleteMemberImage,editMemberInfo,getComeList} from "@/http/api.js"
export default {
  data() {
    return {
      keepShow: false,
      dialogImageUrl: "",
      dialogVisible: false,
      disabled: false,
      uploadNum: 6,
      imgList: ["", "", "", "", "", ""],
      fileList: [],
      dialogImageUrl: "",
      dialogVisible: false,
	  info:{},
      total:0
    };
  },
  created() {
  	let that = this
	let id = that.$route.query.id
	console.log("id",id)
	that.getDetail(id)
  },
  methods: {
      toNext(){
        let that = this
        that.$router.push({
            path:"/cardInfo?id="+that.$route.query.id+"&type=list"
        })
      },
    getList(id){
      let that = this
      let data = {}
      data['id'] = id
      data['date'] = "2022-12-19"
      getComeList(data).then((res) => {
          console.log("list",res)
          if(res.code == 200){
              that.total = res.data.total
          }
      })
    },
    back() {
      this.$router.push({
        path: "/manage",
      });
    },
    toCard(){
      this.$router.push({
          path:"/cardInfo?id="+this.$route.query.id+"&type=kkslkldjd"
      })  
    },
	getDetail(id){
		let that = this
		getMemberDetail(id).then((res) => {
			console.log(res)
			if(res.code == 200){
				that.info = res.data
                setTimeout(() => {
                    that.getList(id)
                },500)
                
			}
		})
	},
    //图片预览
    handlePictureCardPreview(item) {
      this.dialogImageUrl = item;
      this.dialogVisible = true;
    },
    handleAvatarSuccess(res, file) {
	  let that = this
      let data = {}
      data["id"]=that.info.id
      data["image"] = res.data.path
      uploadMemberImage(data).then((result) => {
          if(result.code == 200){
              that.$message({
              	message: result.message,
              	type: 'success',
                offset:400
              });
              this.info.image.push(res.data.path); 
          }else{
              that.$message({
              	message: result.message,
              	type: 'error',
                offset:400
              });
          }
      })
    },
    delPicture(i) {
      let that = this
      deleteMemberImage(that.info.id,i).then((res) => {
          if(res.code == 200){
              that.$message({
              	message: res.message,
              	type: 'success',
                offset:400
              });
              that.info.image.splice(i, 1);
          }else{
              that.$message({
              	message: res.message,
              	type: 'error',
                offset:400
              });
          }
      })
      
    },
   
    keepClick(){
        let that = this
        let id = that.info.id
        let data = {}
        data["_method"] = "put"
        data["member_id"] = that.info.member_id
        data["member_no"] = that.info.member_no
        data["remark"] = that.info.remark
        editMemberInfo(data,id).then((res) => {
            console.log(res)
            if(res.code == 200){
                this.keepShow = true
                setTimeout(()=>{
                  this.keepShow = false
                  history.go(-1)
                },1500)
            }else{
                that.$message({
                	message: res.message,
                	type: 'error',
                    offset:400
                });
            }
        })
      
    }
  },
};
</script>


<style scoped>
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
</style>
<style lang="scss">
.insertRemark{
	width: 100%;
	height: 100%;
	outline: none;
	border: none;
	resize: none;
}
.insertRemark::placeholder{
    color: #aaa !important;
}
.zl{
    color: #1a73e8;
    cursor: pointer;
}
.zl:hover{
    opacity: 0.6;
    text-decoration: underline;
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
  line-height: 160px;
  margin-bottom: 10px;
  background: rgba(0, 0, 0, 0.07);
  border: 1px solid #aaa;
  position: relative;
  border-radius: 0;
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
.wrap {
  width: 820px;
  background: #ffffff;
  box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);
  border: 1px solid #d2d2d7;
  .top {
    width: 100%;
    padding: 0 40px;
    .member {
      width: 100%;
      border-bottom: 1px solid#D2D2D7;
      font-size: 21px;
      color: rgba(29, 29, 31, 0.9);
      padding: 49px 0 20px 0;
      img {
        width: 50px;
        height: 50px;
        margin-right: 22px;
        border-radius: 50%;
        background: #eaeaea;
      }
    }
    .member_info {
      padding: 20px 40px;
      border-bottom: 1px solid#D2D2D7;

      .member_item {
        width: 660px;
        font-size: 16px;
        color: #1d1d1f;
        display: flex;
        align-items: center;
        padding: 12px 0;
        .grey_left {
          width: 140px;
          text-align: center;
          color: #707070;
          margin-right: 20px;
        }
        .bor {
          width: 250px;
          height: 20px;
          border-bottom: 1px solid #d2d2d7;
        }
        .blue {
          color: #1a73e8;
          font-weight: bold;
        }
        .blue:hover{
            opacity: 0.6;
            text-decoration: underline;
        }
        .remarks {
          width: 560px;
          height: 140px;
          background: #fff;
          border-radius: 10px;
          border: 1px solid #aaa;
          padding: 14px;
        }
      }
      .m-b-20 {
        margin-bottom: 20px;
      }
    }

    .add-pictur {
      width: 100%;
      .pic-tit {
        font-size: 16px;
        color: #707070;
        margin-bottom: 10px;
      }
      .des {
        font-size: 12px;
        color: #707070;
        margin-bottom: 10px;
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
          margin-right: 10px;
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
          height: 50px;
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
  }
  .bottom {
    width: 820px;
    height: 150px;
    font-size: 20px;
    margin-top: 51px;
    padding-top: 28px;
    border-top: 1px solid #d2d2d7;
    .button {
      width: 200px;
      height: 50px;
      color: #707070;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #ededed;
      border-radius: 10px;
    }
    .btn_blue {
      color: #fff;
      margin-left: 50px;
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
}
</style>
