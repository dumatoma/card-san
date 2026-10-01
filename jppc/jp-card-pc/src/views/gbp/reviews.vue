<template>
  <div class="gbp rv no-size">
    <div class="tit">クチコミ管理</div>

    <div class="denied" v-if="denied">{{ denied }}</div>
    <template v-else>
      <div class="stats">
        <div class="rv-st">
          <div class="rv-num">{{ stats.avg_rating == null ? '-' : Number(stats.avg_rating).toFixed(1) }}</div>
          <div class="rv-lbl">平均評価</div>
        </div>
        <div :class="filter == 'all' ? 'rv-st rv-on' : 'rv-st shou'" @click="setFilter('all')">
          <div class="rv-num">{{ stats.total }}</div>
          <div class="rv-lbl">総件数</div>
        </div>
        <div :class="filter == 'unreplied' ? 'rv-st rv-on' : 'rv-st shou'" @click="setFilter('unreplied')">
          <div class="rv-num red">{{ stats.unreplied }}</div>
          <div class="rv-lbl">未返信</div>
        </div>
      </div>
      <div class="unread"><span class="dot"></span>未読 {{ unreadShown }}件</div>
      <div class="warn" v-if="warning">{{ warning }}</div>

      <!-- GBP 未連携 / クチコミ管理 OFF -->
      <div class="guide" v-if="loaded && (!connected || !reviewOn)">
        <div class="g-title">{{ connected ? 'クチコミ管理をONにしましょう' : 'Googleビジネスプロフィールと連携しよう' }}</div>
        <div class="g-sub" v-if="!connected">[各種設定]&gt;[Googleビジネス連携]から連携できます。<br />連携すると、お客様からのクチコミの確認・返信がこの画面でできるようになります。</div>
        <div class="g-sub" v-else>[各種設定]&gt;[Googleビジネス連携]の「クチコミ管理」をONにすると、<br />お客様からのクチコミの確認・返信がこの画面でできるようになります。</div>
        <div class="g-btn shou" @click="$router.push('/googleBusiness')">Googleビジネス連携画面へ</div>
      </div>

      <div class="empty" v-else-if="loaded && !list.length">
        <template v-if="filter == 'unreplied' && stats.total">未返信のクチコミはありません。</template>
        <template v-else>まだクチコミがありません。<br />Googleでクチコミが投稿されると、ここに表示されます。</template>
      </div>

      <div class="card rv-list" v-else-if="list.length">
        <div class="rv-item" v-for="r in list" :key="r.id">
          <div class="rv-head">
            <img v-if="r.reviewer_photo" :src="r.reviewer_photo" class="ava" alt="" referrerpolicy="no-referrer" />
            <div v-else class="ava ini">{{ (r.reviewer_name || '?').substr(0, 1) }}</div>
            <div class="who">
              <div class="name">{{ r.reviewer_name }}</div>
              <div class="stars"><span v-for="n in 5" :key="n" :class="n <= r.star ? 'on' : ''">★</span></div>
            </div>
            <div class="date"><span class="dot" v-if="unreadIds[r.id]"></span>{{ r.date }}</div>
          </div>
          <div class="cmt" v-if="r.comment">{{ r.comment }}</div>
          <div class="cmt none" v-else>（評価のみ・コメントなし）</div>

          <!-- 返信の入力・編集 -->
          <template v-if="editing == r.id">
            <textarea class="ta" v-model="draft" maxlength="4096" placeholder="返信を入力..."></textarea>
            <div class="ta-foot">
              <span class="len">{{ draft.length }}/4096</span>
              <div class="b-cancel shou" @click="cancelEdit">キャンセル</div>
              <div class="b-send shou" :class="!draft.trim() || sending ? 'dis' : ''" @click="send(r)">{{ sending ? '送信中…' : '送信' }}</div>
            </div>
          </template>
          <template v-else-if="r.reply">
            <div class="reply">
              <div class="reply-head"><i class="el-icon-s-promotion"></i><span>{{ r.reply_date }}</span></div>
              <div class="reply-txt">{{ r.reply }}</div>
            </div>
            <div class="act"><div class="b-edit shou" @click="edit(r)">編集</div></div>
          </template>
          <div class="act" v-else><div class="b-reply shou" @click="edit(r)"><i class="el-icon-back"></i> 返信する</div></div>
        </div>
        <div class="more">
          <div class="more-btn shou" v-if="list.length < count" @click="load(true)">{{ loading ? '読み込み中…' : 'もっと見る' }}</div>
          <div class="count">{{ list.length }}/{{ count }}件を表示中</div>
        </div>
      </div>
      <div class="empty" v-else-if="!loaded"><i class="el-icon-loading"></i></div>
    </template>
  </div>
</template>

<script>
import { gbpReviewList, gbpReviewRead, gbpReviewReply } from "@/http/api.js";

export default {
  name: "reviews",
  data() {
    return {
      loaded: false,
      loading: false,
      denied: "",
      warning: "",
      connected: false,
      reviewOn: 0,
      filter: "all",
      stats: { avg_rating: null, total: 0, unreplied: 0, unread: 0 },
      list: [],
      count: 0,
      unreadIds: {},
      unreadShown: 0,
      editing: "",
      draft: "",
      sending: false,
    };
  },
  created() {
    this.load(false);
  },
  methods: {
    setFilter(f) {
      if (this.filter == f) return;
      this.filter = f;
      this.cancelEdit();
      this.load(false);
    },
    load(more) {
      if (this.loading) return;
      this.loading = true;
      gbpReviewList({ filter: this.filter, offset: more ? this.list.length : 0, limit: 10 }).then((res) => {
        this.loading = false;
        this.loaded = true;
        if (!res || res.code != 200) {
          if (res && res.code == 403) this.denied = res.message;
          else this.$message({ message: (res && res.message) || "読み込みに失敗しました", type: "error", offset: 400 });
          return;
        }
        let d = res.data;
        this.connected = d.connected;
        this.reviewOn = d.review_on;
        this.warning = d.warning;
        this.stats = d.stats;
        this.count = d.count;
        this.list = more ? this.list.concat(d.list) : d.list;
        if (!more) this.unreadShown = d.stats.unread;
        // 表示した未読は既読にする（青い丸はこの画面を開いている間は残す）
        let ids = d.list.filter((r) => !r.is_read).map((r) => r.id);
        ids.forEach((id) => this.$set(this.unreadIds, id, 1));
        if (ids.length) gbpReviewRead(ids).catch(() => {});
      }).catch(() => { this.loading = false; this.loaded = true; });
    },
    edit(r) {
      this.editing = r.id;
      this.draft = r.reply || "";
    },
    cancelEdit() {
      this.editing = "";
      this.draft = "";
    },
    send(r) {
      if (this.sending || !this.draft.trim()) return;
      this.sending = true;
      let wasUnreplied = !r.reply;
      gbpReviewReply(r.id, this.draft).then((res) => {
        this.sending = false;
        if (res && res.code == 200) {
          r.reply = res.data.reply;
          r.reply_date = res.data.reply_date;
          if (wasUnreplied) this.stats.unreplied = Math.max(0, this.stats.unreplied - 1);
          this.cancelEdit();
          this.$message({ message: res.message, type: "success", offset: 400 });
        } else {
          this.$message({ message: (res && res.message) || "返信できませんでした", type: "error", offset: 400 });
        }
      }).catch(() => { this.sending = false; });
    },
  },
};
</script>

<style lang="scss" scoped>
@import "./gbp.scss";
.rv { width: 680px; }
.stats { display: flex; background: #fff; box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1); }
.rv-st { flex: 1; text-align: center; padding: 12px 0 10px; border-bottom: 3px solid transparent; box-sizing: border-box; }
.rv-st.rv-on { background: #e8f0fe; border-bottom-color: #1a73e8; }
.rv-num { font-size: 20px; font-weight: bold; color: #1d1d1f; text-align: center; }
.rv-num.red { color: #d93025; }
.rv-lbl { font-size: 12px; color: #707070; margin-top: 2px; text-align: center; }
.unread { color: #1a73e8; font-size: 13px; margin: 8px 0 12px 14px; display: flex; align-items: center; }
.dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #1a73e8; margin-right: 6px; }
.warn { font-size: 12px; color: #8a6d00; background: #fff8e1; border-radius: 6px; padding: 8px 12px; margin-bottom: 10px; }
.guide { background: #f2f2f4; border-radius: 10px; padding: 34px 20px; text-align: center; }
.g-title { font-weight: bold; font-size: 15px; color: #1d1d1f; }
.g-sub { font-size: 12px; color: #707070; line-height: 1.7; margin-top: 12px; }
.g-btn { display: inline-block; background: #3b73e0; color: #fff; border-radius: 4px; padding: 9px 60px; margin-top: 18px; font-size: 14px; }
.empty { text-align: center; color: #707070; font-size: 14px; line-height: 1.8; padding: 50px 0; }
.rv-list { border-radius: 12px; overflow: hidden; }
.rv-item { padding: 18px 16px 14px; border-bottom: 1px solid #e5e5ea; }
.rv-head { display: flex; align-items: center; }
.ava { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 10px; }
.ava.ini { background: #4a90e2; color: #fff; text-align: center; line-height: 30px; font-size: 13px; }
.who { flex: 1; }
.name { font-size: 13px; font-weight: bold; color: #1d1d1f; }
.stars span { color: #d2d2d7; font-size: 11px; }
.stars span.on { color: #f5b400; }
.date { font-size: 12px; color: #707070; display: flex; align-items: center; }
.cmt { font-size: 13px; color: #1d1d1f; line-height: 1.7; margin-top: 10px; white-space: pre-wrap; }
.cmt.none { color: #a1a1a6; }
.act { text-align: right; margin-top: 10px; }
.b-reply, .b-edit { display: inline-block; border: 1px solid #1a73e8; color: #1a73e8; border-radius: 4px; padding: 3px 12px; font-size: 12px; }
.ta { width: 100%; box-sizing: border-box; height: 70px; border: 1px solid #c7c7cc; border-radius: 4px; padding: 10px 14px; font-size: 13px; margin-top: 12px; resize: vertical; outline: none; font-family: inherit; }
.ta-foot { display: flex; align-items: center; margin-top: 8px; }
.len { flex: 1; font-size: 11px; color: #707070; }
.b-cancel { border: 1px solid #1d1d1f; border-radius: 4px; padding: 3px 12px; font-size: 12px; margin-right: 8px; }
.b-send { background: #3b73e0; color: #fff; border-radius: 4px; padding: 4px 26px; font-size: 12px; }
.b-send.dis { background: #a7c2f2; opacity: 1; }
.reply { background: #f2f2f4; border-radius: 4px; padding: 12px 14px; margin-top: 12px; border-left: 3px solid #c7c7cc; }
.reply-head { display: flex; justify-content: space-between; color: #707070; font-size: 12px; }
.reply-txt { font-size: 13px; color: #1d1d1f; line-height: 1.7; margin-top: 4px; white-space: pre-wrap; }
.more { text-align: center; padding: 16px 0; }
.more-btn { display: inline-block; border: 1px solid #1d1d1f; border-radius: 16px; padding: 4px 16px; font-size: 13px; }
.count { font-size: 12px; color: #707070; margin-top: 8px; }
</style>
