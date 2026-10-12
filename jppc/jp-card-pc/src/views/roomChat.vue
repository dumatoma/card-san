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
                <img :src="item.avatar || (item.kind == 'group' ? groupImg : defaultImg)" style="object-fit: cover" alt="" />
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
          <div class="top no-size">
            <div class="top-left">
              <img :src="currentThread.avatar || (currentKind == 'group' ? groupImg : defaultImg)" alt="" v-if="current > -1" />
              <div class="uname" v-if="current > -1">{{ currentThread.name }}</div>
            </div>
            <!-- メンバー一覧を開くボタン(グループのみ) [カスタムエイド] -->
            <button type="button" class="mem-btn" v-if="current > -1 && currentKind == 'group'" @click="openMembers()">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="3.5"></circle><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"></path><circle cx="17" cy="9" r="2.8"></circle><path d="M16 14.2c3 .2 5.5 2.3 5.5 5.8"></path></svg>
              メンバー {{ currentThread.count }}
            </button>
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
                  <div class="mdel shou" v-if="m.id" @click="askDelete(m)" title="削除"><img src="../static/svg/delta.svg" alt="" /></div>
                  <div class="ri no-size" v-if="m.type == 1">{{ m.message }}</div>
                  <div class="riImage no-size" v-if="m.type == 2"><img :src="m.message" alt="" /></div>
                  <div class="le">
                    <div class="left-img"><img :src="myAvatar || defaultImg" style="object-fit: cover" alt="" /></div>
                    <div class="left-name">{{ myName }}</div>
                  </div>
                </div>
              </div>
            </div>
            <div style="height: 5px"></div>
          </div>
          <div class="send-box no-size" v-if="current > -1">
            <textarea
              ref="ta"
              v-model="messageTxt"
              placeholder="メッセージを入力（Enterで送信／Shift+Enterで改行）"
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
            <button type="button" class="send-btn" :class="messageTxt == '' ? 'off' : ''" @click.stop="handleSendText">
              送信
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 20.5l18-8.5L3 3.5v6.6l12.5 1.9L3 13.9z"></path></svg>
            </button>
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

    <!-- 送信メッセージ削除の確認 -->
    <div class="fixed" v-show="delTarget">
      <div class="zhe" @click="delTarget = null"></div>
      <div class="dialog">
        <div class="d-title">選択したメッセージを削除しますか？</div>
        <div class="d-sub">削除したメッセージは相手の画面からも消えます。</div>
        <div class="d-btns">
          <div class="d-cancel shou" @click="delTarget = null">キャンセル</div>
          <div class="d-ok d-del shou" @click="doDelete">削除</div>
        </div>
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
  deleteStaffRoomMessage,
  deleteStaffDm,
} from "@/http/api.js";

export default {
  name: "roomChat",
  data() {
    return {
      defaultImg: require("../static/yulan-tou.png"),
      groupImg: require("../static/group170.svg"),
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
      delTarget: null,
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
    // 入力欄の高さを文字量に合わせる [カスタムエイド]
    messageTxt() {
      this.autoGrow();
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
    // 入力欄の高さを文字量に合わせて84px〜160pxで伸び縮みさせる [カスタムエイド]
    autoGrow() {
      this.$nextTick(() => {
        const el = this.$refs.ta;
        if (!el) return;
        el.style.height = "84px";
        el.style.height = Math.min(Math.max(el.scrollHeight + 2, 84), 160) + "px";
      });
    },
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
      // 日本語入力(IME)の変換確定のEnterでは送信しない。
      // 変換中は keyCode=229 / isComposing=true になるため除外する。
      if (e.isComposing || e.keyCode === 229) return;
      if (e.shiftKey || e.keyCode !== 13) return; // Shift+Enterは改行 / Enter以外は無視
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
            if (res && res.code == 200 && res.data) that.applySent(optimistic, res.data);
          })
          .catch(() => {});
      } else {
        optimistic.from_id = that.myId;
        optimistic.to_id = that.currentDmId;
        sendStaffDm({ to_id: that.currentDmId, type: type, message: message })
          .then((res) => {
            if (res && res.code == 200 && res.data) that.applySent(optimistic, res.data);
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
    applySent(optimistic, data) {
      let m = data.message || {};
      this.$set(optimistic, "id", data.id || data.message_id);
      ["time", "date", "week", "create_time"].forEach((k) => {
        if (m[k] != null) this.$set(optimistic, k, m[k]);
      });
      this.rebuild();
    },
    askDelete(m) {
      if (!m.id) return;
      this.delTarget = m;
    },
    doDelete() {
      let that = this;
      let m = that.delTarget;
      that.delTarget = null;
      if (!m) return;
      let p = that.currentKind == "group" ? deleteStaffRoomMessage(m.id) : deleteStaffDm(that.currentDmId, m.id);
      p.then((res) => {
        if (res && res.code == 200) {
          that.removeMsg(m.id);
        } else {
          that.$message({ message: (res && res.message) || "削除できませんでした", type: "error", offset: 400 });
        }
      }).catch(() => {});
    },
    removeMsg(id) {
      this.history = this.history.filter((x) => x.id != id);
      this.localMsgs = this.localMsgs.filter((x) => x.id != id);
      this.rebuild();
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
      if (datas.type == "staff_room_delete") {
        if (that.currentKind == "group" && datas.data) that.removeMsg(datas.data.id);
        return;
      }
      if (datas.type == "staff_dm_delete") {
        if (that.currentKind == "dm" && datas.data && datas.data.from_id == that.currentDmId) that.removeMsg(datas.data.id);
        return;
      }
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
  margin-bottom: 10px;
}
// ===== [カスタムエイド 2026-10-12] チャット画面の統一デザイン =====
// メッセージチャット(message.vue)と同じ寸法・同じ見た目にそろえている
.content {
  display: flex;
  width: 100%;
  max-width: 1120px;
  height: calc(100vh - 260px);
  min-height: 560px;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}
// 左: ルーム/相手一覧(幅320px固定)
.left {
  width: 320px;
  flex-shrink: 0;
  border-right: 1px solid #ececf0;
  overflow-y: auto;
  .user-list {
    .item {
      padding: 10px 16px;
      border-bottom: 1px solid #f2f2f5;
    }
    .item.move {
      background: #eef4fe;
    }
    .box {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .ite-left {
      flex-shrink: 0;
      display: flex;
    }
    .ite-left img {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: #ededed;
      border: 0.5px solid #aaaaaa;
      box-sizing: border-box;
    }
    .ite-midd {
      flex: 1;
      min-width: 0;
    }
    .uname {
      font-size: 15px;
      font-weight: 500;
      color: #1d1d1f;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .info {
      font-size: 13px;
      color: #6e6e73;
      margin-top: 2px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      -webkit-line-clamp: 1;
    }
    .ite-rig {
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 4px;
      .time {
        font-size: 11px;
        color: #8e8e93;
      }
      .hui {
        min-width: 20px;
        height: 20px;
        line-height: 20px;
        padding: 0 6px;
        box-sizing: border-box;
        background: #d93025;
        color: #fff;
        font-size: 11px;
        text-align: center;
        border-radius: 10px;
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
// 右: メッセージ欄
.right {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  .top {
    height: 60px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 0 24px;
    border-bottom: 1px solid #ececf0;
    .top-left {
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
    }
    img {
      width: 36px;
      height: 36px;
      flex-shrink: 0;
      border-radius: 50%;
      object-fit: cover;
      background: #ededed;
      border: 0.5px solid #aaaaaa;
      box-sizing: border-box;
    }
    .uname {
      font-size: 16px;
      font-weight: bold;
      color: #1d1d1f;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
  }
  // メンバー一覧を開くボタン
  .mem-btn {
    flex-shrink: 0;
    height: 32px;
    padding: 0 12px;
    border: 1px solid #d2d2d7;
    border-radius: 16px;
    background: #ffffff;
    color: #1d1d1f;
    font-size: 13px;
    font-family: inherit;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    &:hover {
      background: #f5f5f7;
    }
  }
  .info-box {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 6px 24px 14px;
    background: #f7f7f9;
  }
  // 日付
  .rig-time {
    display: table;
    margin: 14px auto 0;
    padding: 3px 12px;
    border-radius: 10px;
    background: #ececf0;
    color: #6e6e73;
    font-size: 12px;
  }
  .info-message {
    margin-top: 14px;
    // メッセージ横のアイコンと名前。相手側・自分側の両方に効かせる
    // (以前は相手側だけに書かれていて、自分側のアイコンが元の画像サイズで表示されていた)
    .le {
      width: 44px;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      .left-img img {
        display: block;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        background: #ededed;
        border: 0.5px solid #aaaaaa; // アイコンの周りの枠線
        box-sizing: border-box;
      }
      .left-name {
        max-width: 44px;
        margin-top: 2px;
        font-size: 10px;
        color: #8e8e93;
        text-align: center;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }
    }
    // 吹き出し(相手側・自分側共通)
    .ri {
      max-width: #{"min(70%, 560px)"};
      box-sizing: border-box;
      padding: 10px 14px;
      font-size: 14px;
      line-height: 1.7;
      color: #1d1d1f;
      white-space: pre-wrap;
      word-break: break-all;
    }
    .riImage img {
      display: block;
      max-width: 200px;
      border-radius: 10px;
    }
    .mtime {
      font-size: 11px;
      color: #8e8e93;
    }
  }
  .info-left {
    display: flex;
    align-items: flex-end;
    .le {
      margin-right: 10px; // 相手側: アイコンの右に吹き出し
    }
    .ri {
      background: #ffffff;
      border: 1px solid #e5e5ea;
      border-radius: 16px 16px 16px 4px;
    }
    .mtime {
      margin-left: 10px;
    }
  }
  .info-right {
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
    .le {
      margin-left: 10px; // 自分側: 吹き出しの右にアイコン
    }
    .ri {
      background: #87fa7d;
      border-radius: 16px 16px 4px 16px;
    }
    .mtime {
      margin-right: 10px;
    }
    .mdel {
      width: 18px;
      height: 18px;
      margin-right: 8px;
      flex-shrink: 0;
      opacity: 0.7;
    }
    .mdel:hover {
      opacity: 1;
    }
    .mdel img {
      width: 100%;
      height: 100%;
    }
  }
  // 入力欄(3行分の高さ。入力に合わせて最大160pxまで伸びる)
  .send-box {
    flex-shrink: 0;
    border-top: 1px solid #ececf0;
    padding: 12px 24px 0;
    textarea {
      display: block;
      width: 100%;
      height: 84px;
      max-height: 160px;
      box-sizing: border-box;
      padding: 10px 12px;
      border: 1px solid #d2d2d7;
      border-radius: 10px;
      outline: none;
      resize: none;
      overflow-y: auto;
      font-size: 14px;
      line-height: 1.7;
      color: #1d1d1f;
      font-family: inherit;
      &:focus {
        border-color: #1a73e8;
      }
    }
  }
  .bot {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 24px 12px;
    .hx {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      &:hover {
        background: rgba(29, 29, 31, 0.1);
      }
      img {
        width: 18px;
        height: 20px;
      }
    }
  }
  // 送信ボタン
  .send-btn {
    height: 40px;
    padding: 0 20px;
    border: 0;
    border-radius: 20px;
    background: #1a73e8;
    color: #ffffff;
    font-size: 14px;
    font-weight: 500;
    font-family: inherit;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    &:hover {
      background: #1557b0;
    }
    &.off {
      opacity: 0.5;
    }
  }
}
.member-head {
  height: 60px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  border-bottom: 1px solid #ececf0;
  padding: 0 24px;
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
  flex: 1;
  min-height: 0;
  padding: 16px 24px;
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
        border: 0.5px solid #aaaaaa; // アイコンの周りの枠線
        box-sizing: border-box;
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
.d-ok.d-del {
  background: #d93025 !important;
  color: #fff;
}
</style>
