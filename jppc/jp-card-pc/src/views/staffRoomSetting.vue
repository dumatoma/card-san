<template>
  <div>
    <div class="tit">スタッフルーム設定</div>
    <div class="card no-size">
      <div class="top no-size">
        <!-- 読み込みが終わるまでは空の円を表示(以前は読み込み中に初期アイコンが一瞬見えていた) -->
        <div v-if="!loaded" class="avt avt-loading"></div>
        <img :src="avatar" v-else-if="avatar" class="avt up-avatar" />
        <img src="../static/group170.svg" v-else class="avt" />
      </div>
      <div class="bottom no-size">
        <el-upload
          class="upload-demo"
          action="https://api.card-san.jp/tool/upload/file"
          :show-file-list="false"
          :on-success="handleAvatarSuccess"
          accept="image/*"
          name="file"
        >
          <div class="add shou"><span>+</span> 画像添付</div>
        </el-upload>

        <div class="field">
          <div class="name">ルーム名</div>
          <input class="no-size" type="text" placeholder="チャットルーム名を入力" v-model="name" />
        </div>

        <div class="room-radio">
          <label class="srs-row shou" :class="isOn ? '' : 'off'">
            <span class="srs-radio" :class="isOn ? 'on' : ''"></span>
            <input type="checkbox" v-model="isOn" style="display:none" />
            スタッフルーム
          </label>
          <div class="note">
            <div>・スタッフのみが利用できる業務連絡用のグループです。</div>
            <div>・プライベートな会話や業務外の話題は禁止しています。</div>
            <div>・1対1の個人チャットにも対応（選択可）。</div>
          </div>
        </div>

        <div class="keep shou" @click="save">保　存</div>
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
import { getStaffRoom, updateStaffRoomSettings } from "@/http/api.js";
export default {
  name: "staffRoomSetting",
  data() {
    return {
      name: "",
      avatar: "",
      isOn: false,
      keepShow: false,
      loaded: false, // ルーム情報の読み込みが終わったか
    };
  },
  created() {
    this.load();
  },
  methods: {
    load() {
      let that = this;
      getStaffRoom()
        .then((res) => {
          if (res && res.code == 200 && res.data.room) {
            that.name = res.data.room.name || "";
            that.avatar = res.data.room.avatar || "";
            that.isOn = res.data.room.is_on == 1;
          }
          that.loaded = true;
        })
        .catch(() => {
          that.loaded = true;
        });
    },
    handleAvatarSuccess(res) {
      if (res.code == 200) {
        this.avatar = res.data.path;
      } else {
        this.$message.error(res.message);
      }
    },
    save() {
      let that = this;
      updateStaffRoomSettings({
        name: that.name,
        avatar: that.avatar,
        is_on: that.isOn ? 1 : 0,
      }).then((res) => {
        if (res.code == 200) {
          that.keepShow = true;
          setTimeout(() => {
            that.keepShow = false;
          }, 1500);
        } else {
          that.$message.error(res.message);
        }
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.tit {
  color: #1d1d1f;
  font-size: 21px;
  font-weight: bold;
  margin-bottom: 10px; // タイトル下の余白(以前は29px)
}
.card {
  width: 410px;
  background: #ffffff;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  padding-bottom: 20px;
  .top {
    width: 100%;
    text-align: center;
    padding: 56px 0 40px;
    border-bottom: 1px solid #d2d2d7;
    .avt {
      width: 117px;
      height: 117px;
      border-radius: 50%;
      object-fit: cover;
      background: #ededed;
      vertical-align: top;
    }
    .avt-loading {
      display: inline-block; // 画像と同じ大きさの空の円(読み込み中の表示)
    }
    .up-avatar {
      border: 1px solid #aaaaaa; // アップした画像の周りに1pxの線(白い背景との境目を見せる)
      box-sizing: border-box;
    }
  }
  .bottom {
    padding: 0 40px; // 左右を同じ余白にして、保存ボタンがカードの中心に来るようにする(以前は左だけ40px)
    .add {
      width: 108px;
      height: 34px;
      line-height: 34px;
      border-radius: 10px;
      color: #2699fb;
      font-size: 14px;
      text-align: center;
      border: 1px solid #2699fb;
      margin-top: 16px;
      span {
        font-size: 18px;
        margin-right: 8px;
      }
    }
    .field {
      margin-top: 22px;
      color: #1d1d1f;
      font-size: 14px;
      .name {
        margin-bottom: 5px;
      }
      input {
        border: 1px solid #aaaaaa;
        outline: 0;
        width: 329px;
        height: 44px;
        border-radius: 10px;
        padding: 0 6px;
      }
    }
    .room-radio {
      margin-top: 24px;
      border-top: 1px solid #d2d2d7;
      padding-top: 18px;
      width: 329px;
      .srs-row {
        display: flex;
        align-items: center;
        font-size: 14px;
        color: #1d1d1f;
        &.off {
          color: #707070; // チェックを外しているときの文字色
        }
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
      .note {
        margin-top: 12px;
        font-size: 12px;
        color: #909399;
        line-height: 1.9;
      }
    }
    .keep {
      width: 120px;
      height: 40px;
      line-height: 40px;
      text-align: center;
      background: #1a73e8;
      border-radius: 26px;
      color: #ffffff;
      font-size: 16px;
      margin: 34px auto 10px;
    }
  }
}
.add:hover {
  background: rgba(26, 115, 232, 0.1) !important;
}
.fixed {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(29, 29, 31, 0.2);
  .zhe {
    position: absolute;
    width: 100vw;
    height: 100vh;
  }
}
.sus-box {
  width: 160px;
  height: 146px;
  background: #ffffff;
  border-radius: 10px;
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
