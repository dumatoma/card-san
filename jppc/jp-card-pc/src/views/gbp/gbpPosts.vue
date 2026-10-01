<template>
  <div class="gbp no-size">
    <div class="tit">Instagram×GBP投稿管理</div>

    <div class="card ig">
      <div class="ig-acc">
        <img v-if="ins.avatar && !avaErr" :src="ins.avatar" class="ig-ava" alt="" @error="avaErr = true" />
        <div v-else class="ig-ava blank"></div>
        <div class="ig-txt">
          <div class="ig-name">{{ ins.connected ? (ins.username || 'Instagramアカウント') : 'Instagramアカウント' }}</div>
          <div :class="ins.connected && !igExpired ? 'st on' : 'st off'">{{ !ins.connected ? '[未連携]' : igExpired ? '[要再連携]' : '[連携済み]' }}</div>
        </div>
        <div class="ig-link shou" v-if="!ins.connected || igExpired" @click="$router.push('/noticeSet')">お知らせ設定画面へ→</div>
      </div>
      <div class="ig-auto" :class="canAuto ? '' : 'off'">
        <div>
          <div class="row-name small">自動同期設定</div>
          <div class="row-des">Instagram投稿時にGBP最新情報へ自動転載</div>
        </div>
        <el-switch v-model="insAuto" :active-value="1" :inactive-value="0" :disabled="!canAuto" @change="saveAuto"></el-switch>
      </div>
    </div>
    <div class="warn-box">動画・リール投稿は連動の対象外です。Google側の投稿機能が写真のみ対応のため、動画を含む投稿はGBPへ転載されません。</div>
    <div class="notice" v-if="igError">{{ igError }}</div>
    <div class="notice" v-if="!gbpConnected">Googleビジネスプロフィールと連携されていないため、転載できません。[各種設定]&gt;[Googleビジネス連携]から連携してください。</div>

    <div class="card list">
      <div class="tabs">
        <div :class="tab == 0 ? 'tab act' : 'tab shou'" @click="switchTab(0)">Instagram投稿</div>
        <div :class="tab == 1 ? 'tab act' : 'tab shou'" @click="switchTab(1)">GBP投稿</div>
      </div>

      <!-- Instagram投稿 -->
      <template v-if="tab == 0">
        <div class="empty" v-if="!ins.connected">Instagramと連携すると投稿が表示されます</div>
        <div class="empty" v-else-if="loading && !items.length"><i class="el-icon-loading"></i></div>
        <div class="empty" v-else-if="!items.length">{{ igError ? '投稿を取得できませんでした' : 'まだ投稿がありません' }}</div>
        <div v-for="it in items" :key="it.id" class="post" :class="it.status == 'synced' ? 'done' : ''">
          <div class="mark">
            <i class="el-icon-success ok" v-if="it.status == 'synced'"></i>
            <i class="el-icon-close ng" v-else-if="it.status == 'video'"></i>
            <span class="circle" v-else></span>
          </div>
          <div class="thumb">
            <img v-if="it.image" :src="it.image" alt="" />
            <i v-if="it.status == 'video'" class="el-icon-video-play play"></i>
          </div>
          <div class="p-txt">
            <div class="cap">{{ it.caption || '（本文なし）' }}</div>
            <div class="date">{{ it.date }}</div>
          </div>
          <div class="p-st synced" v-if="it.status == 'synced'">転載済み</div>
          <div class="p-st video" v-else-if="it.status == 'video'">転載不可(動画)</div>
          <div class="p-st pending shou" v-else @click="openPreview(it)">未転載</div>
        </div>
        <div class="more" v-if="ins.connected && items.length">
          <div class="more-btn shou" v-if="next" @click="load(true)">{{ loading ? '読み込み中…' : 'もっと見る' }}</div>
          <div class="count">{{ items.length }}/{{ Math.max(total, items.length) }}件を表示中</div>
        </div>
      </template>

      <!-- GBP投稿 -->
      <template v-else>
        <div class="empty" v-if="!gbpConnected">Googleビジネスプロフィールと連携すると投稿が表示されます</div>
        <div class="empty" v-else-if="gbpLoading && !gbpItems.length"><i class="el-icon-loading"></i></div>
        <div class="empty" v-else-if="gbpError">{{ gbpError }}</div>
        <div class="empty" v-else-if="!gbpItems.length">GBPの投稿はまだありません</div>
        <div v-for="(p, i) in gbpItems" :key="i" class="post">
          <div class="thumb"><img v-if="p.image" :src="p.image" alt="" /></div>
          <div class="p-txt">
            <div class="cap">{{ p.summary || '（本文なし）' }}</div>
            <div class="date">{{ p.date }}</div>
          </div>
          <a class="p-st pending" v-if="p.url" :href="p.url" target="_blank">Googleで見る</a>
        </div>
        <div class="more" v-if="gbpNext">
          <div class="more-btn shou" @click="loadGbp(true)">{{ gbpLoading ? '読み込み中…' : 'もっと見る' }}</div>
        </div>
      </template>
    </div>

    <!-- GBPへの転載プレビュー -->
    <div class="mask" v-if="preview">
      <div class="pv">
        <div class="pv-head">GBPへの転載プレビュー<i class="el-icon-close shou" @click="preview = null"></i></div>
        <div class="pv-card">
          <div class="pv-img"><img v-if="preview.image" :src="preview.image" alt="" /></div>
          <div class="pv-body">
            <div class="pv-date">{{ today }}</div>
            <div class="pv-cap" :class="fullCap ? 'full' : ''">{{ preview.caption }}</div>
            <span class="pv-more shou" v-if="!fullCap && preview.caption.length > 90" @click="fullCap = true">続きを見る</span>
          </div>
        </div>
        <div class="pv-btn shou" :class="reposting || !gbpConnected ? 'dis' : ''" @click="repost">{{ reposting ? '転載中…' : 'GBPに転載する' }}</div>
        <div class="pv-note">GBPの「最新情報」として投稿されます。実際の表示はGoogleマップ・検索結果側の仕様により多少異なる場合があります。</div>
      </div>
    </div>
  </div>
</template>

<script>
import { gbpOverview, gbpFeatures, gbpInsPosts, gbpInsRepost, gbpPosts } from "@/http/api.js";

export default {
  name: "gbpPosts",
  data() {
    return {
      ins: { connected: false, username: "", avatar: "" },
      gbpConnected: false,
      insOn: 0,
      insAuto: 0,
      tab: 0,
      items: [],
      next: "",
      total: 0,
      loading: false,
      igError: "",
      gbpItems: [],
      gbpNext: "",
      gbpLoading: false,
      gbpError: "",
      preview: null,
      avaErr: false,
      fullCap: false,
      reposting: false,
    };
  },
  computed: {
    igExpired() {
      return /再連携/.test(this.igError);
    },
    canAuto() {
      return this.ins.connected && !this.igError && this.gbpConnected && this.insOn == 1;
    },
    today() {
      let d = new Date();
      return d.getFullYear() + "/" + ("0" + (d.getMonth() + 1)).slice(-2) + "/" + ("0" + d.getDate()).slice(-2);
    },
  },
  created() {
    gbpOverview().then((res) => {
      if (res && res.code == 200) {
        this.ins = res.data.instagram;
        this.gbpConnected = res.data.connected;
        this.insOn = res.data.ins_on;
        this.insAuto = res.data.ins_auto;
        if (this.ins.connected) this.load(false);
      } else if (res) {
        this.$message({ message: res.message, type: "error", offset: 400 });
      }
    });
  },
  methods: {
    load(more) {
      if (this.loading) return;
      this.loading = true;
      gbpInsPosts(more ? this.next : "").then((res) => {
        this.loading = false;
        if (res && res.code == 200) {
          this.igError = "";
          this.items = more ? this.items.concat(res.data.items) : res.data.items;
          this.next = res.data.next;
          this.total = res.data.total;
        } else {
          // Instagram 側のトークン失効（パスワード変更・期限切れ等）
          this.igError = (res && res.message) || "Instagramの投稿を取得できませんでした。";
        }
      }).catch(() => { this.loading = false; });
    },
    loadGbp(more) {
      if (this.gbpLoading || !this.gbpConnected) return;
      this.gbpLoading = true;
      gbpPosts(more ? this.gbpNext : "").then((res) => {
        this.gbpLoading = false;
        if (res && res.code == 200) {
          let list = (res.data.localPosts || []).map((p) => ({
            summary: p.summary || (p.event && p.event.title) || "",
            image: p.media && p.media[0] ? p.media[0].googleUrl || p.media[0].sourceUrl : "",
            date: p.createTime ? p.createTime.substr(0, 10).replace(/-/g, ".") : "",
            url: p.searchUrl || "",
          }));
          this.gbpItems = more ? this.gbpItems.concat(list) : list;
          this.gbpNext = res.data.nextPageToken || "";
          this.gbpError = "";
        } else {
          this.gbpError = "GBPの投稿を取得できませんでした（" + ((res && res.message) || "") + "）";
        }
      }).catch(() => { this.gbpLoading = false; });
    },
    switchTab(t) {
      this.tab = t;
      if (t == 1 && !this.gbpItems.length) this.loadGbp(false);
    },
    saveAuto() {
      gbpFeatures({ ins_auto: this.insAuto }).then((res) => {
        if (!res || res.code != 200) {
          this.$message({ message: (res && res.message) || "保存できませんでした", type: "error", offset: 400 });
          this.insAuto = this.insAuto ? 0 : 1;
        }
      });
    },
    openPreview(it) {
      if (!this.gbpConnected) return this.$message({ message: "Googleビジネスプロフィールと連携されていません", type: "error", offset: 400 });
      this.fullCap = false;
      this.preview = it;
    },
    repost() {
      if (this.reposting || !this.preview) return;
      this.reposting = true;
      let it = this.preview;
      gbpInsRepost(it.id).then((res) => {
        this.reposting = false;
        if (res && res.code == 200) {
          it.status = "synced";
          this.preview = null;
          this.$message({ message: res.message, type: "success", offset: 400 });
        } else {
          this.$message({ message: (res && res.message) || "転載できませんでした", type: "error", offset: 400 });
        }
      }).catch(() => { this.reposting = false; });
    },
  },
};
</script>

<style lang="scss" scoped>
@import "./gbp.scss";
.ig-acc { display: flex; align-items: center; padding: 18px 26px; border-bottom: 1px solid #ededf0; }
.ig-ava { width: 46px; height: 46px; border-radius: 50%; object-fit: cover; margin-right: 12px; }
.ig-ava.blank { background: #d2d2d7; }
.ig-txt { flex: 1; }
.ig-name { font-size: 17px; color: #1d1d1f; }
.ig-link { color: #1a73e8; font-weight: bold; font-size: 15px; }
.ig-auto { display: flex; justify-content: space-between; align-items: center; padding: 16px 26px; }
.ig-auto.off { opacity: 0.45; }
.row-name.small { font-size: 16px; }
.warn-box { border: 1px solid #1d1d1f; border-radius: 8px; padding: 14px 18px; font-size: 13px; margin: 22px 0; background: #fff; }
.list { margin-top: 22px; overflow: hidden; }
.tabs { display: flex; border-bottom: 1px solid #e5e5ea; }
.tab { flex: 1; text-align: center; padding: 16px 0; font-size: 16px; color: #a1a1a6; font-weight: bold; }
.tab.act { background: #e8f0fe; color: #1d1d1f; }
.empty { text-align: center; color: #707070; padding: 40px 0; font-size: 14px; }
.post { display: flex; align-items: center; padding: 14px 22px; border-bottom: 1px solid #ededf0; }
.post.done { background: #eaf4ea; }
.mark { width: 36px; text-align: center; font-size: 22px; }
.mark .ok { color: #2e7d32; }
.mark .ng { color: #8e8e93; }
.circle { display: inline-block; width: 18px; height: 18px; border: 1.5px solid #8e8e93; border-radius: 50%; }
.thumb { width: 70px; height: 70px; background: #d2d2d7; border-radius: 4px; margin: 0 14px; position: relative; overflow: hidden; flex-shrink: 0; }
.thumb img { width: 100%; height: 100%; object-fit: cover; }
.play { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); font-size: 30px; color: #fff; }
.p-txt { flex: 1; min-width: 0; }
.cap { font-size: 14px; color: #1d1d1f; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.date { font-size: 12px; color: #86868b; margin-top: 6px; }
.p-st { width: 120px; text-align: center; border-radius: 4px; padding: 7px 0; font-size: 13px; margin-left: 16px; flex-shrink: 0; text-decoration: none; }
.p-st.synced { border: 1px solid #2e7d32; color: #2e7d32; background: #e3f1e3; }
.p-st.pending { border: 1px solid #1a73e8; color: #1a73e8; background: #e8f0fe; }
.p-st.video { border: 1px solid #8e8e93; color: #707070; }
.more { text-align: center; padding: 18px 0; }
.more-btn { display: inline-block; border: 1px solid #1d1d1f; border-radius: 16px; padding: 4px 16px; font-size: 13px; }
.count { font-size: 13px; color: #707070; margin-top: 10px; }
.pv { width: 420px; background: #f2f2f4; border-radius: 16px; padding: 18px 22px 26px; }
.pv-head { text-align: center; font-weight: bold; font-size: 16px; position: relative; margin-bottom: 14px; }
.pv-head i { position: absolute; right: 0; top: 0; font-size: 20px; color: #8e8e93; }
.pv-card { background: #fff; border-radius: 14px; overflow: hidden; border: 1px solid #e5e5ea; }
.pv-img { height: 300px; background: #d2d2d7; }
.pv-img img { width: 100%; height: 100%; object-fit: cover; }
.pv-body { padding: 12px 16px 16px; font-size: 14px; }
.pv-date { color: #707070; font-size: 12px; margin-bottom: 6px; }
.pv-cap { white-space: pre-wrap; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
.pv-cap.full { display: block; max-height: 220px; overflow-y: auto; }
.pv-more { color: #8e8e93; }
.pv-btn { margin-top: 18px; background: #3b73e0; color: #fff; text-align: center; border-radius: 24px; height: 46px; line-height: 46px; font-weight: bold; }
.pv-note { color: #707070; font-size: 13px; margin-top: 14px; line-height: 1.6; }
</style>
