<template>
  <div>
    <div class="tit">ルームチャット</div>
    <div class="content no-size">
      <!-- 左：ルーム/相手一覧 -->
      <div class="left no-size">
        <div class="user-list no-size">
          <div
            v-for="(item, index) in threads"
            :key="index"
            :class="current == index ? 'item move no-size' : 'item no-size'"
            style="cursor: pointer"
            @click="openThread(index)"
          >
            <div class="box">
              <div class="ite-left">
                <img :src="item.avatar || defaultImg" style="object-fit: cover" alt="" />
              </div>
              <div class="ite-midd">
                <div class="uname">
                  {{ item.name }}<span v-if="item.kind == 'group'"> ({{ item.count }})</span>
                </div>
                <div class="info inlie2">{{ item.last_message }}</div>
              </div>
              <div class="ite-rig">
                <div class="time">{{ item.time }}</div>
                <div class="hui" v-if="item.unread > 0">{{ item.unread > 99 ? "99+" : item.unread }}</div>
              </div>
            </div>
          </div>
          <div v-if="threads.length == 0" class="empty">ルームチャットがありません</div>
        </div>
      </div>

      <!-- 右：チャット詳細 or メンバー一覧 -->
      <div class="right no-size">
        <template v-if="view == 'chat'">
          <div
            class="top no-size"
            :class="currentKind == 'group' ? 'shou clickable' : ''"
            @click="currentKind == 'group' ? openMembers() : null"
          >
            <img :src="currentThread.avatar || defaultImg" alt="" v-if="current > -1" />
            <div class="uname" v-if="current > -1">
              {{ currentThread.name }}<span v-if="currentKind == 'group'"> ({{ currentThread.count }})</span>
            </div>
          </div>
          <div class="info-box" ref="warp">
            <div class="info-item no-size" v-for="(m, i) in messages" :key="i">
              <div class="rig-time" v-if="m.Showdate">{{ m.Showdate }}</div>
              <div class="info-message">
                <!-- 相手 -->
                <div class="info-left" v-if="!isMine(m)">
                  <div class="le">
                    <div class="left-img"><img :src="rowAvatar(m)" style="object-fit: cover" alt="" /></div>
                    <div class="left-name">{{ rowName(m) }}</div>
                  </div>
                  <div class="ri no-size" v-if="m.type == 1">{{ m.message }}</div>
                  <div class="riImage no-size" v-if="m.type == 2"><img :src="m.message" alt="" /></div>
                  <div class="mtime">{{ m.time }}</div>
                </div>
                <!-- 自分 -->
                <div class="info-right" v-else>
                  <div class="mtime">{{ m.time }}</div>
                  <div class="ri no-size" v-if="m.type == 1">{{ m.message }}</div>
                  <div class="riImage no-size" v-if="m.type == 2"><img :src="m.message" alt="" /></div>
                </div>
              </div>
            </div>
            <div style="height: 5px"></div>
          </div>
          <div class="send-box no-size" v-if="current > -1">
            <textarea
              v-model="messageTxt"
              placeholder="Enterで送信／Shift+Enterで改行"
              @keydown.enter="handleKeyCode($event)"
            ></textarea>
          </div>
          <div class="bot no-size" v-if="current > -1">
            <el-upload
              class="upload-demo"
              accept="image/*"
              action="https://api.card-san.jp/tool/upload/file"
              :on-success="handleImageSuccess"
              :show-file-list="false"
              name="file"
            >
              <div class="hx"><img src="../static/fasong.png" class="huixing" alt="" /></div>
            </el-upload>
            <div>
              <img
                src="../static/send.png"
                class="huixing"
                :style="messageTxt == '' ? 'opacity:0.6 !important' : ''"
                alt=""
                @click.stop="handleSendText"
              />
            </div>
          </div>
        </template>

        <!-- メンバー一覧 -->
        <template v-else>
          <div class="member-head">
            <span class="back shou" @click="view = 'chat'">‹ 戻る</span>
            <span class="member-tit">メンバー一覧</span>
          </div>
          <div class="member-list">
            <div class="member-item" v-for="(mb, i) in members" :key="i">
              <div class="mb-left">
                <img :src="mb.avatar || defaultImg" alt="" />
                <div class="mb-info">
                  <div class="mb-name">{{ mb.name }}</div>
                  <div class="mb-role" v-if="mb.admin_type == 1">主管理者</div>
                </div>
              </div>
              <div
                v-if="mb.id != myId"
                class="talk-btn shou"
                :class="!mb.staff_room_dm || !myDm ? 'disabled' : ''"
                @click="onMemberTalk(mb)"
              >
                1対1トーク
              </div>
            </div>
            <div v-if="members.length == 0" class="empty">メンバーがいません</div>
          </div>
        </template>
      </div>
    </div>

    <!-- 1対1トーク確認ダイアログ -->
    <div class="fixed" v-show="showConfirm">
      <div class="zhe" @click="showConfirm = false"></div>
      <div class="dialog">
        <div class="d-title">{{ confirmTarget.name }}さんと1対1のチャットを開始しますか？</div>
        <div class="d-sub">業務連絡のみに使用してください。<br />不要な使用は禁止されています。</div>
        <div class="d-btns">
          <div class="d-cancel shou" @click="showConfirm = false">キャンセル</div>
          <div class="d-ok shou" @click="confirmTalk">チャットする</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {
  getStaffRoom,
  getStaffDmThreads,
  getStaffRoomMessages,
  sendStaffRoomMessage,
  readStaffRoomMessages,
  getStaffDmMessages,
  sendStaffDm,
  readStaffDm,
} from "@/http/api.js";

export default {
  name: "roomChat",
  data() {
    return {
      defaultImg: require("../static/yulan-tou.png"),
      threads: [],
      members: [],
      myId: "",
      myName: "",
      myAvatar: "",
      myDm: 0,
      roomOn: false,
      roomJoin: false,
      current: -1,
      currentKind: "", // 'group' | 'dm'
      currentDmId: 0,
      messages: [],
      history: [],
      localMsgs: [],
      messageTxt: "",
      view: "chat",
      showConfirm: false,
      confirmTarget: {},
    };
  },
  computed: {
    currentThread() {
      return this.threads[this.current] || {};
    },
    socketMsgs() {
      return this.$store.state.msg;
    },
  },
  watch: {
    socketMsgs: {
      handler() {
        this.toDoSocket(this.socketMsgs && this.socketMsgs.data);
      },
    },
  },
  created() {
    let admin = JSON.parse(localStorage.getItem("admin") || "{}");
    this.myId = admin.id;
    this.myName = admin.message_name || admin.name;
    this.myAvatar = admin.avatar;
    this.loadList(true);
  },
  methods: {
    loadList(autoOpen) {
      let that = this;
      getStaffRoom()
        .then((res) => {
          if (res && res.code == 200) {
            that.members = res.data.members || [];
            that.myDm = res.data.my_dm == 1 ? 1 : 0;
            that.roomOn = res.data.room && res.data.room.is_on == 1;
            that.roomJoin = res.data.my_join == 1;
            that.buildThreads(res.data.room);
            if (autoOpen && that.current == -1 && that.threads.length > 0) {
              that.openThread(0);
            }
          }
        })
        .catch(() => {});
    },
    buildThreads(room) {
      let that = this;
      let list = [];
      if (that.roomOn && that.roomJoin && room) {
        list.push({
          kind: "group",
          name: room.name || "スタッフルーム",
          avatar: room.avatar,
          count: (that.members || []).length,
          last_message: room.last_message || "",
          unread: 0,
          time: "",
        });
      }
      getStaffDmThreads()
        .then((res) => {
          if (res && res.code == 200) {
            (res.data.threads || []).forEach((t) => {
              list.push({
                kind: "dm",
                to_id: t.to_id,
                name: t.name,
                avatar: t.avatar,
                last_message: t.last_message || "",
                unread: t.unread * 1 || 0,
                time: "",
              });
            });
          }
          that.threads = list;
        })
        .catch(() => {
          that.threads = list;
        });
    },
    openThread(index) {
      let that = this;
      let t = that.threads[index];
      if (!t) return;
      that.current = index;
      that.view = "chat";
      that.currentKind = t.kind;
      that.history = [];
      that.localMsgs = [];
      that.messages = [];
      if (t.kind == "group") {
        that.currentDmId = 0;
        that.loadGroup();
      } else {
        that.currentDmId = t.to_id;
        that.loadDm();
      }
      // 未読クリア
      that.$set(that.threads[index], "unread", 0);
    },
    loadGroup() {
      let that = this;
      getStaffRoomMessages()
        .then((res) => {
          if (res && res.code == 200) {
            that.history = res.data.messages || [];
            that.rebuild();
            that.scrollBottom();
            readStaffRoomMessages().catch(() => {});
          }
        })
        .catch(() => {});
    },
    loadDm() {
      let that = this;
      getStaffDmMessages(that.currentDmId)
        .then((res) => {
          if (res && res.code == 200) {
            that.history = res.data.messages || [];
            that.rebuild();
            that.scrollBottom();
            readStaffDm({ from_id: that.currentDmId }).catch(() => {});
          }
        })
        .catch(() => {});
    },
    rebuild() {
      let all = [...this.history, ...this.localMsgs];
      let seen = {};
      let out = [];
      all.forEach((m) => {
        let id = m.id != null ? m.id : m._lid;
        if (id != null && seen[id]) return;
        if (id != null) seen[id] = 1;
        out.push(m);
      });
      let lastDate = "";
      out.forEach((m) => {
        if (m.date && m.date != lastDate) {
          m.Showdate = m.week ? m.date + " (" + m.week + ")" : m.date;
          lastDate = m.date;
        } else {
          m.Showdate = "";
        }
      });
      this.messages = out;
    },
    isMine(m) {
      if (this.currentKind == "group") return m.admin_id == this.myId;
      return m.from_id == this.myId;
    },
    rowAvatar(m) {
      if (this.currentKind == "group") return m.avatar || this.defaultImg;
      return this.currentThread.avatar || this.defaultImg;
    },
    rowName(m) {
      if (this.currentKind == "group") return m.name;
      return this.currentThread.name;
    },
    handleKeyCode(e) {
      if (e.shiftKey) return; // Shift+Enter は改行
      e.preventDefault();
      this.handleSendText();
    },
    handleSendText() {
      let txt = (this.messageTxt || "").trim();
      if (!txt) return;
      this.doSend(1, txt);
      this.messageTxt = "";
    },
    handleImageSuccess(res) {
      if (res.code == 200) {
        this.doSend(2, res.data.path);
      } else {
        this.$message.error(res.message);
      }
    },
    doSend(type, message) {
      let that = this;
      if (that.current < 0) return;
      let optimistic = {
        _lid: "local_" + new Date().getTime(),
        type: type,
        message: message,
        time: "",
        date: "",
        week: "",
      };
      if (that.currentKind == "group") {
        optimistic.admin_id = that.myId;
        optimistic.name = that.myName;
        optimistic.avatar = that.myAvatar;
        sendStaffRoomMessage({ type: type, message: message })
          .then((res) => {
            if (res && res.code == 200 && res.data && res.data.id) optimistic.id = res.data.id;
          })
          .catch(() => {});
      } else {
        optimistic.from_id = that.myId;
        optimistic.to_id = that.currentDmId;
        sendStaffDm({ to_id: that.currentDmId, type: type, message: message })
          .then((res) => {
            if (res && res.code == 200 && res.data && res.data.id) optimistic.id = res.data.id;
          })
          .catch(() => {});
      }
      that.localMsgs.push(optimistic);
      that.rebuild();
      that.scrollBottom();
      // 一覧のプレビュー更新
      if (that.threads[that.current]) {
        that.$set(that.threads[that.current], "last_message", type == 2 ? "[画像]" : message);
      }
    },
    openMembers() {
      this.view = "members";
      this.loadList(false);
    },
    onMemberTalk(mb) {
      if (!mb.staff_room_dm || !this.myDm) return;
      this.confirmTarget = mb;
      this.showConfirm = true;
    },
    confirmTalk() {
      let that = this;
      that.showConfirm = false;
      let target = that.confirmTarget;
      // 既存スレッドがあれば開く、なければ追加して開く
      let idx = that.threads.findIndex((t) => t.kind == "dm" && t.to_id == target.id);
      if (idx == -1) {
        that.threads.push({
          kind: "dm",
          to_id: target.id,
          name: target.name,
          avatar: target.avatar,
          last_message: "",
          unread: 0,
          time: "",
        });
        idx = that.threads.length - 1;
      }
      that.openThread(idx);
    },
    scrollBottom() {
      this.$nextTick(() => {
        let el = this.$refs.warp;
        if (el) el.scrollTop = el.scrollHeight;
      });
    },
    toDoSocket(raw) {
      if (!raw) return;
      let datas;
      try {
        datas = typeof raw == "string" ? JSON.parse(raw) : raw;
      } catch (e) {
        return;
      }
      if (!datas || !datas.type) return;
      let that = this;
      if (datas.type == "staff_room_message") {
        let items = datas.data || [];
        if (that.currentKind == "group") {
          items.forEach((m) => that.history.push(m));
          that.rebuild();
          that.scrollBottom();
          readStaffRoomMessages().catch(() => {});
        } else {
          that.loadList(false);
        }
      } else if (datas.type == "staff_dm") {
        let items = datas.data || [];
        if (that.currentKind == "dm") {
          items.forEach((m) => {
            let inConv =
              (m.from_id == that.currentDmId && m.to_id == that.myId) ||
              (m.from_id == that.myId && m.to_id == that.currentDmId);
            if (inConv) that.history.push(m);
          });
          that.rebuild();
          that.scrollBottom();
          readStaffDm({ from_id: that.currentDmId }).catch(() => {});
        } else {
          that.loadList(false);
        }
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.tit {
  color: #1d1d1f;
  font-size: 21px;
  font-weight: bold;
  margin-bottom: 20px;
}
.content {
  display: flex;
  height: calc(100vh - 114px - 50px - 90px);
  background: #ffffff;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
}
.left {
  width: 340px;
  border-right: 1px solid #ededed;
  overflow-y: auto;
  .user-list {
    .item {
      padding: 14px 16px;
      border-bottom: 1px solid #f0f0f0;
    }
    .item.move {
      background: #f2f7ff;
    }
    .box {
      display: flex;
      align-items: center;
    }
    .ite-left img {
      width: 46px;
      height: 46px;
      border-radius: 50%;
      background: #ededed;
    }
    .ite-midd {
      flex: 1;
      margin-left: 12px;
      overflow: hidden;
    }
    .uname {
      font-size: 15px;
      font-weight: bold;
      color: #1d1d1f;
    }
    .info {
      font-size: 12px;
      color: #909399;
      margin-top: 4px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 200px;
    }
    .ite-rig {
      text-align: right;
      .time {
        font-size: 11px;
        color: #c0c4cc;
      }
      .hui {
        display: inline-block;
        min-width: 20px;
        height: 20px;
        line-height: 20px;
        padding: 0 5px;
        box-sizing: border-box;
        background: #d93025;
        color: #fff;
        font-size: 12px;
        text-align: center;
        border-radius: 10px;
        margin-top: 6px;
      }
    }
    .empty {
      text-align: center;
      color: #909399;
      font-size: 13px;
      padding-top: 60px;
    }
  }
}
.right {
  flex: 1;
  display: flex;
  flex-direction: column;
  .top {
    height: 62px;
    display: flex;
    align-items: center;
    padding: 0 20px;
    border-bottom: 1px solid #ededed;
    img {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 12px;
      background: #ededed;
    }
    .uname {
      font-size: 16px;
      font-weight: bold;
      color: #1d1d1f;
    }
  }
  .top.clickable:hover {
    background: #f6f9ff;
  }
  .info-box {
    flex: 1;
    overflow-y: auto;
    padding: 16px 20px;
    background: #fafafa;
  }
  .rig-time {
    text-align: center;
    font-size: 12px;
    color: #909399;
    margin: 14px 0;
  }
  .info-message {
    margin-bottom: 14px;
  }
  .info-left {
    display: flex;
    align-items: flex-end;
    .le {
      text-align: center;
      margin-right: 10px;
      .left-img img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        object-fit: cover;
        background: #ededed;
      }
      .left-name {
        font-size: 11px;
        color: #909399;
        max-width: 60px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }
    }
    .ri {
      max-width: 60%;
      background: #ffffff;
      border: 1px solid #ededed;
      border-radius: 10px;
      padding: 10px 14px;
      font-size: 14px;
      color: #1d1d1f;
      white-space: pre-wrap;
      word-break: break-all;
    }
    .riImage img {
      max-width: 200px;
      border-radius: 10px;
    }
    .mtime {
      font-size: 11px;
      color: #c0c4cc;
      margin-left: 8px;
    }
  }
  .info-right {
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
    .ri {
      max-width: 60%;
      background: #87fa7d;
      border-radius: 10px;
      padding: 10px 14px;
      font-size: 14px;
      color: #1d1d1f;
      white-space: pre-wrap;
      word-break: break-all;
    }
    .riImage img {
      max-width: 200px;
      border-radius: 10px;
    }
    .mtime {
      font-size: 11px;
      color: #c0c4cc;
      margin-right: 8px;
    }
  }
  .send-box {
    border-top: 1px solid #ededed;
    padding: 10px 20px 0;
    textarea {
      width: 100%;
      height: 56px;
      border: none;
      outline: none;
      resize: none;
      font-size: 14px;
    }
  }
  .bot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 20px 14px;
    .huixing {
      width: 26px;
      height: 26px;
      cursor: pointer;
    }
  }
}
.member-head {
  height: 52px;
  display: flex;
  align-items: center;
  border-bottom: 1px solid #ededed;
  padding: 0 16px;
  .back {
    color: #1a73e8;
    font-size: 14px;
    margin-right: 16px;
  }
  .member-tit {
    font-size: 16px;
    font-weight: bold;
    color: #1d1d1f;
  }
}
.member-list {
  padding: 16px 20px;
  overflow-y: auto;
  .member-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid #ededed;
    border-radius: 10px;
    padding: 12px 16px;
    margin-bottom: 12px;
    .mb-left {
      display: flex;
      align-items: center;
      img {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 12px;
        background: #ededed;
      }
      .mb-name {
        font-size: 15px;
        color: #1d1d1f;
        font-weight: bold;
      }
      .mb-role {
        font-size: 12px;
        color: #1a73e8;
        margin-top: 2px;
      }
    }
    .talk-btn {
      background: #1a73e8;
      color: #fff;
      font-size: 13px;
      padding: 8px 18px;
      border-radius: 20px;
    }
    .talk-btn.disabled {
      background: #a9c9f5;
      cursor: not-allowed;
    }
  }
  .empty {
    text-align: center;
    color: #909399;
    padding-top: 40px;
  }
}
.fixed {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 2000;
  .zhe {
    position: absolute;
    width: 100vw;
    height: 100vh;
    background: rgba(29, 29, 31, 0.4);
  }
}
.dialog {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 360px;
  background: #fff;
  border-radius: 12px;
  padding: 28px 24px 20px;
  text-align: center;
  .d-title {
    font-size: 16px;
    font-weight: bold;
    color: #1d1d1f;
    line-height: 1.6;
  }
  .d-sub {
    font-size: 13px;
    color: #909399;
    margin-top: 14px;
    line-height: 1.7;
  }
  .d-btns {
    display: flex;
    margin-top: 24px;
    .d-cancel,
    .d-ok {
      flex: 1;
      height: 42px;
      line-height: 42px;
      border-radius: 24px;
      font-size: 15px;
    }
    .d-cancel {
      background: #ededed;
      color: #606266;
      margin-right: 12px;
    }
    .d-ok {
      background: #1a73e8;
      color: #fff;
    }
  }
}
</style>
