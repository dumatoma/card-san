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
        <div class="ig-link shou" @click="$router.push('/noticeSet')">お知らせ設定画面へ→</div>
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
        <div class="more" v-if="ins.connected && (next || Math.max(total, items.length) > 10)">
          <div class="more-btn shou" v-if="next" @click="load(true)">{{ loading ? '読み込み中…' : 'もっと見る' }}</div>
          <div class="count">{{ items.length }} / {{ Math.max(total, items.length) }} 件を表示中</div>
        </div>
      </template>

      <!-- GBP投稿：Instagram から GBP へ転載済みの投稿のみ（Insta投稿連動_Web版UIUX仕様 4.3） -->
      <template v-else>
        <div class="empty" v-if="!gbpConnected">Googleビジネスプロフィールと連携すると投稿が表示されます</div>
        <div class="empty" v-else-if="!ins.connected">Instagramと連携すると投稿が表示されます</div>
        <div class="empty" v-else-if="(loading || gbpFilling) && !syncedItems.length"><i class="el-icon-loading"></i></div>
        <div class="empty" v-else-if="!syncedItems.length">{{ igError ? '投稿を取得できませんでした' : 'GBPに転載した投稿はまだありません' }}</div>
        <div v-for="it in syncedShown" :key="'g' + it.id" class="post done">
          <div class="mark"><i class="el-icon-success ok"></i></div>
          <div class="thumb"><img v-if="it.image" :src="it.image" alt="" /></div>
          <div class="p-txt">
            <div class="cap">{{ it.caption || '（本文なし）' }}</div>
            <div class="date">{{ it.date }}</div>
          </div>
          <div class="p-st synced">転載済み</div>
        </div>
        <div class="more" v-if="gbpConnected && ins.connected && (syncedTotal > 10 || gbpHasMore)">
          <div class="more-btn shou" v-if="gbpHasMore" @click="moreGbp">{{ gbpFilling ? '読み込み中…' : 'もっと見る' }}</div>
          <div class="count">{{ syncedShown.length }} / {{ syncedTotal }} 件を表示中</div>
        </div>
      </template>
    </div>

    <!-- GBPへの転載プレビュー -->
    <div class="mask" v-if="preview">
      <div class="pv">
        <div class="pv-head">GBPへの転載プレビュー<i class="el-icon-close shou" @click="preview = null"></i></div>
        <div class="pv-main">
          <div class="pv-card">
            <div class="pv-img"><img v-if="preview.image" :src="preview.image" alt="" /></div>
            <div class="pv-body">
              <div class="pv-date">{{ today }}</div>
              <div class="pv-cap-wrap">
                <div class="pv-cap" ref="pvCap" :class="fullCap ? 'full' : ''">{{ preview.caption }}</div>
                <span class="pv-more shou" v-if="!fullCap && capOver" @click="fullCap = true">…<em>続きを見る</em></span>
              </div>
            </div>
          </div>
          <div class="pv-btn shou" :class="reposting || !gbpConnected ? 'dis' : ''" @click="repost">{{ reposting ? '転載中…' : 'GBPに転載する' }}</div>
          <div class="pv-note">GBPの「最新情報」として投稿されます。実際の表示はGoogleマップ・検索結果側の仕様により多少異なる場合があります。</div>
        </div>
      </div>
    </div>

    <transition name="c-toast"><div class="c-toast" v-if="toast">{{ toast }}</div></transition>
  </div>
</template>

<script>
import { gbpOverview, gbpFeatures, gbpInsPosts, gbpInsRepost } from "@/http/api.js";

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
      gbpShown: 10,
      gbpFilling: false,
      syncedCount: null, // サーバーが転載済み総数(synced_total)を返す場合のみ使用
      preview: null,
      avaErr: false,
      fullCap: false,
      capOver: false,
      reposting: false,
      toast: "",
      toastTimer: null,
    };
  },
  computed: {
    igExpired() {
      return /再連携/.test(this.igError);
    },
    canAuto() {
      return this.ins.connected && !this.igError && this.gbpConnected && this.insOn == 1;
    },
    // GBP投稿タブ = Instagram 投稿のうち GBP へ転載済み(status=synced)のもの
    syncedItems() {
      return this.items.filter((it) => it.status == "synced");
    },
    syncedShown() {
      return this.syncedItems.slice(0, this.gbpShown);
    },
    syncedTotal() {
      // 総数はサーバー値(synced_total)があればそれ、なければ読み込み済みの転載済み件数
      if (this.syncedCount != null) return Math.max(this.syncedCount, this.syncedItems.length);
      return this.syncedItems.length;
    },
    gbpHasMore() {
      if (this.syncedShown.length < this.syncedItems.length) return true;
      if (this.syncedCount != null) return this.syncedShown.length < this.syncedCount && !!this.next;
      return !!this.next && this.syncedItems.length >= this.gbpShown;
    },
    today() {
      let d = new Date();
      return d.getFullYear() + "/" + ("0" + (d.getMonth() + 1)).slice(-2) + "/" + ("0" + d.getDate()).slice(-2);
    },
  },
  created() {
    gbpOverview().then((res) => {
      if (res && res.code == 200) {
        this.ins = res.data.instagram || { connected: false, username: "", avatar: "" };
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
      if (this.loading || this.gbpFilling) return;
      this.loading = true;
      gbpInsPosts(more ? this.next : "").then((res) => {
        this.loading = false;
        if (res && res.code == 200) {
          this.igError = "";
          let rows = Array.isArray(res.data.items) ? res.data.items : [];
          this.items = more ? this.items.concat(rows) : rows;
          this.next = res.data.next || "";
          this.total = res.data.total;
          if (res.data.synced_total != null) this.syncedCount = res.data.synced_total * 1;
          if (this.tab == 1) this.$nextTick(() => this.fillSynced());
        } else {
          // Instagram 側のトークン失効（パスワード変更・期限切れ等）
          this.igError = (res && res.message) || "Instagramの投稿を取得できませんでした。";
        }
      }).catch(() => { this.loading = false; });
    },
    // Instagram 投稿は「次ページ」方式なので、転載済みが表示件数に足りるまで次ページを読み込む
    fillSynced() {
      if (this.syncedItems.length >= this.gbpShown || !this.next || this.loading) {
        this.gbpFilling = false;
        return;
      }
      this.gbpFilling = true;
      let before = this.items.length;
      gbpInsPosts(this.next).then((res) => {
        if (res && res.code == 200) {
          this.items = this.items.concat(Array.isArray(res.data.items) ? res.data.items : []);
          this.next = res.data.next || "";
          this.total = res.data.total;
          if (res.data.synced_total != null) this.syncedCount = res.data.synced_total * 1;
        } else {
          this.next = "";
        }
        if (this.items.length == before) this.next = "";
        this.fillSynced();
      }).catch(() => { this.gbpFilling = false; });
    },
    moreGbp() {
      if (this.gbpFilling) return;
      this.gbpShown += 10;
      this.fillSynced();
    },
    switchTab(t) {
      this.tab = t;
      if (t == 1) {
        this.gbpShown = 10;
        if (this.ins.connected && this.gbpConnected && !this.loading && !this.gbpFilling) this.fillSynced();
      }
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
      this.capOver = false;
      this.preview = it;
      // 本文が2行を超える場合だけ「…続きを見る」を出す
      this.$nextTick(() => {
        let el = this.$refs.pvCap;
        if (el) this.capOver = el.scrollHeight > el.clientHeight + 1;
      });
    },
    repost() {
      if (this.reposting || !this.preview) return;
      this.reposting = true;
      let it = this.preview;
      gbpInsRepost(it.id).then((res) => {
        this.reposting = false;
        if (res && res.code == 200) {
          it.status = "synced";
          if (this.syncedCount != null) this.syncedCount++;
          this.preview = null;
          this.showToast("転載しました");
        } else {
          this.$message({ message: (res && res.message) || "転載できませんでした", type: "error", offset: 400 });
        }
      }).catch(() => { this.reposting = false; });
    },
    showToast(text) {
      this.toast = text;
      clearTimeout(this.toastTimer);
      this.toastTimer = setTimeout(() => { this.toast = ""; }, 1800);
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
.tabs { display: flex; border-bottom: 1px solid #d2d2d7; background: #f0f0f0; }
.tab { flex: 1; text-align: center; padding: 16px 0; font-size: 15px; color: #8a8a8e; font-weight: bold; background: #f0f0f0; }
.tab.act { background: #fff; color: #1d1d1f; }
.empty { text-align: center; color: #707070; padding: 40px 0; font-size: 14px; }
.post { display: flex; align-items: center; padding: 14px 22px; border-bottom: 1px solid #ededf0; }
.post.done { background: rgba(30, 126, 66, 0.06); }
.mark { width: 36px; text-align: center; font-size: 22px; }
.mark .ok { color: #1e7e42; }
.mark .ng { color: #8e8e93; }
.circle { display: inline-block; width: 18px; height: 18px; border: 1.5px solid #8e8e93; border-radius: 50%; }
.thumb { width: 70px; height: 70px; background: #d2d2d7; border-radius: 4px; margin: 0 14px; position: relative; overflow: hidden; flex-shrink: 0; }
.thumb img { width: 100%; height: 100%; object-fit: cover; }
.play { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); font-size: 30px; color: #fff; }
.p-txt { flex: 1; min-width: 0; }
.cap { font-size: 14px; color: #1d1d1f; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.date { font-size: 12px; color: #86868b; margin-top: 6px; }
.p-st { width: 120px; text-align: center; border-radius: 4px; padding: 7px 0; font-size: 13px; margin-left: 16px; flex-shrink: 0; text-decoration: none; }
.p-st.synced { color: #008a32; background: rgba(30, 126, 66, 0.15); font-weight: bold; }
.p-st.pending { border: 1px solid #1a73e8; color: #1a73e8; background: #e8f0fe; }
.p-st.video { border: 1px solid #8e8e93; color: #707070; }
.more { text-align: center; padding: 18px 0 22px; border-top: 1px solid #d2d2d7; margin-top: -1px; }
.more-btn { display: inline-block; border: 1px solid #1d1d1f; border-radius: 16px; padding: 4px 16px; font-size: 13px; }
.count { font-size: 13px; color: #707070; margin-top: 10px; }
.pv { width: 393px; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); max-height: calc(100vh - 40px); display: flex; flex-direction: column; }
.pv-head { text-align: center; font-weight: bold; font-size: 16px; position: relative; padding: 18px 0; border-bottom: 1px solid #d2d2d7; flex-shrink: 0; }
.pv-head i { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #333; }
.pv-main { padding: 20px; overflow-y: auto; }
.pv-card { background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid #d2d2d7; }
.pv-img { aspect-ratio: 1 / 1; width: 100%; background: #d4d4de; }
.pv-img img { width: 100%; height: 100%; object-fit: cover; }
.pv-body { padding: 12px 16px 16px; font-size: 14px; }
.pv-date { color: #707070; font-size: 12px; margin-bottom: 6px; }
.pv-cap-wrap { position: relative; }
.pv-cap { white-space: pre-wrap; word-break: break-all; line-height: 1.6; max-height: 3.2em; overflow: hidden; }
.pv-cap.full { max-height: none; }
.pv-more { position: absolute; right: 0; bottom: 0; line-height: 1.6; background: #fff; padding-left: 2px; color: #1d1d1f; }
.pv-more em { font-style: normal; color: #707070; }
.pv-btn { margin-top: 20px; background: #1a73e8; color: #fff; text-align: center; border-radius: 20px; height: 46px; line-height: 46px; font-weight: bold; font-size: 15px; }
.pv-note { color: #707070; font-size: 12.5px; margin-top: 16px; line-height: 1.6; }
</style>
