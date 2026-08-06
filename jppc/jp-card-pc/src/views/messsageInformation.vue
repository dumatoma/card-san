<template>
  <div>
    <div class="tit">メッセージアカウント情報</div>
    <div class="avatar-box">
      <div class="top no-size">
        <img :src="admin.avatar" alt="" style="border-radius: 50%;object-fit: cover;" v-if="admin.avatar"/>
        <img src="../static/yulan-tou.png" alt="" style="border-radius: 50%;object-fit: cover;" v-else/>
        <!-- <div class="txt">[各種設定/店舗情報/店舗名]が反映される</div> -->
      </div>

      <div class="bottom no-size">
        <el-upload
          class="upload-demo"
          action="https://api.card-san.jp/tool/upload/file"
          :show-file-list="false"
          :on-success="handleAvatarSuccess"
        >
          <div class="add shou">
            <span>+</span>
            画像添付
          </div>
        </el-upload>

        <div class="zuozhe">
          <div class="name">担当者(任意)</div>
          <input
            class="no-size"
            type="text"
            placeholder="担当者を入力してください"
            v-model="admin.name"
          />
          <div class="staff-room-set no-size">
            <div class="srs-tit">スタッフチャットルーム設定</div>
            <label class="srs-row shou">
              <span class="srs-radio" :class="staffRoomJoin ? 'on' : ''"></span>
              <input type="checkbox" v-model="staffRoomJoin" style="display:none" />
              スタッフルームに参加する
            </label>
            <label class="srs-row shou">
              <span class="srs-radio" :class="staffRoomDm ? 'on' : ''"></span>
              <input type="checkbox" v-model="staffRoomDm" style="display:none" />
              1対1のトークを許可する
            </label>
          </div>
          <div class="keep shou" @click="keep">保　存</div>
        </div>
      </div>
    </div>
    <div class="fixed" v-show="keepShow">
      <div class="zhe"></div>
      <div class="sus-box">
        <img src="../static/success.png" alt="" />
        <div class="keep">保存しました</div>
      </div>
    </div>
  </div>
</template>

<script>
import {getStore,setMessageOn,getStaffRoom,updateStaffRoomMember} from "@/http/api.js"
export default {
  name: "",
  components: {},
  data() {
    return {
      keepShow: false,
      imgUrl: require("../static/yulan-tou.png"),
      name:"",
      admin:{},
      staffRoomJoin:false,
      staffRoomDm:false
    };
  },
  created() {
      this.getInfo()
      this.loadFlags()
  },
  methods: {
    getInfo(){
        let that = this
        let info = localStorage.getItem("admin")
        let infos = JSON.parse(info)
        that.admin = infos
    },
    loadFlags(){
        let that = this
        getStaffRoom().then((res) => {
            if(res && res.code == 200){
                that.staffRoomJoin = res.data.my_join == 1
                that.staffRoomDm = res.data.my_dm == 1
            }
        }).catch(() => {})
    },
    handleAvatarSuccess(res, file) {
        let that = this
        if(res.code == 200){
            that.admin.avatar = res.data.path
        }else{
            that.$message.error(res.message)
        }
      // this.imgUrl = URL.createObjectURL(file.raw);
    },
    keep(e) {
      let that = this 
      let data = {}
      data['name'] = that.admin.name
      data['avatar'] = that.admin.avatar
      data['type'] = "info"
      setMessageOn(data).then((res) => {
          if(res.code == 200){
              that.keepShow = true;
              let admin = that.admin
              localStorage.setItem("admin",JSON.stringify(admin))
              setTimeout(() => {
                that.keepShow = false;
              }, 1500);
          }
      })
      // スタッフチャットルーム設定（参加・1対1許可）を保存
      updateStaffRoomMember({
          staff_room_join: that.staffRoomJoin ? 1 : 0,
          staff_room_dm: that.staffRoomDm ? 1 : 0
      }).catch(() => {})
    },
  },
};
</script>

<style lang="scss" scoped>
.tit {
  color: #1d1d1f;
  font-size: 21px;
  font-weight: bold;
  margin-bottom: 29px;
}
.avatar-box {
  width: 410px;
  height: 523px;
  background: #ffffff;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .top {
    width: 100%;
    text-align: center;
    padding-top: 56px;
    padding-bottom: 40px;
    border-bottom: 1px solid #d2d2d7;
    img {
      width: 117px;
      height: 119px;
      margin-bottom: 30px;
    }
    .txt {
      font-size: 16px;
      color: #1d1d1f;
      font-weight: 600;
    }
  }
  .bottom {
    padding-left: 40px;
    .add {
      width: 108px;
      height: 34px;
      line-height: 34px;
      border-radius: 10px 10px 10px 10px;
      color: #2699fb;
      font-size: 14px;
      text-align: center;
      border: 1px solid #2699fb;
      margin-top: 10px;
      span {
        font-size: 18px;
        margin-right: 10px;
      }
    }
    .zuozhe {
      margin-top: 25px;
      color: #1d1d1f;
      font-size: 14px;
      .name {
        margin-bottom: 5px;
      }
      input {
        border: 0;
        outline: 0;
        width: 329px;
        height: 44px;
        background: #ffffff;
        border-radius: 10px 10px 10px 10px;
        // opacity: 1;
        padding: 0 6px;
        border: 1px solid #aaaaaa;
      }
      .keep {
        width: 120px;
        height: 40px;
        line-height: 40px;
        text-align: center;
        background: #1a73e8;
        border-radius: 26px 26px 26px 26px;
        color: #ffffff;
        font-size: 16px;
        margin: 40px auto;
      }
      .staff-room-set {
        width: 329px;
        margin-top: 25px;
        border-top: 1px solid #d2d2d7;
        padding-top: 18px;
        .srs-tit {
          font-size: 15px;
          font-weight: bold;
          color: #1d1d1f;
          margin-bottom: 14px;
        }
        .srs-row {
          display: flex;
          align-items: center;
          font-size: 14px;
          color: #1d1d1f;
          margin-bottom: 14px;
          .srs-radio {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2px solid #aaaaaa;
            margin-right: 10px;
            box-sizing: border-box;
            position: relative;
          }
          .srs-radio.on {
            border-color: #1a73e8;
          }
          .srs-radio.on::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #1a73e8;
          }
        }
      }
    }
  }
}
.add:hover{
    background: rgba(26,115,232,0.1) !important;
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
</style>
