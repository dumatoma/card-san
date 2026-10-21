<template>
  <div class="gbp no-size">
    <div class="tit">Googleビジネス連携</div>

    <div class="denied" v-if="denied">{{ denied }}</div>
    <template v-else>
      <!-- 連携状態 -->
      <div class="card conn">
        <span class="g-logo" v-html="gIcon"></span>
        <div class="conn-txt">
          <div class="conn-name">{{ info.connected ? (info.email || info.location_title || 'Google Business Profile') : 'Google Business Profile' }}</div>
          <div :class="info.connected ? 'st on' : 'st off'">{{ info.connected ? '[連携済み]' : '[未連携]' }}</div>
          <div class="loc" v-if="info.connected && info.location_title">店舗：{{ info.location_title }}</div>
        </div>
        <div class="conn-btn">
          <div class="btn-outline shou" v-if="info.connected" @click="showUnlink = true">連携を解除</div>
          <div class="btn-blue shou" v-else @click="connect" :class="connecting ? 'dis' : ''">{{ connecting ? '処理中…' : '連携する' }}</div>
        </div>
      </div>
      <div class="lead">Googleビジネスプロフィールと連携すると、店舗情報の同期やクチコミ管理が使えます。</div>
      <div class="notice" v-if="loaded && !info.configured && !info.connected">
        Googleビジネス連携は現在準備中です。準備が整い次第ご利用いただけます。
      </div>

      <div class="sub-tit">この連携で使える機能</div>
      <div class="card feat">
        <!-- 基本情報 -->
        <div class="row">
          <span class="ico" v-html="infoIcon"></span>
          <div class="row-txt">
            <div class="row-name">基本情報</div>
            <div class="row-des">営業時間・住所・業種・写真をGBPと同期</div>
          </div>
          <el-switch v-if="info.connected" v-model="info.basic_on" :active-value="1" :inactive-value="0" @change="save({ basic_on: info.basic_on })"></el-switch>
        </div>
        <div class="basic" v-if="info.connected && info.basic_on">
          <div class="b-head">
            <span>自動同期設定</span>
            <el-switch v-model="info.auto_sync" :active-value="1" :inactive-value="0" @change="save({ auto_sync: info.auto_sync })"></el-switch>
          </div>
          <div class="b-fields">
            <el-checkbox-group v-model="info.sync_fields" @change="save({ sync_fields: info.sync_fields })">
              <el-checkbox label="hours">営業時間</el-checkbox>
              <el-checkbox label="address">住所</el-checkbox>
              <el-checkbox label="category">業種</el-checkbox>
              <el-checkbox label="photos">写真</el-checkbox>
            </el-checkbox-group>
          </div>
          <div class="b-note">自動同期をONにすると、Card-Sanで店舗情報を変更したときにGoogle側にも自動で反映されます（1時間ごと）。</div>
          <div class="b-foot">
            <span class="last">最終同期:{{ info.last_sync_at || '－' }}</span>
            <div class="btn-sync shou" :class="syncing || info.auto_sync == 1 ? 'dis' : ''" @click="syncNow">
              <i :class="syncing ? 'el-icon-loading' : 'el-icon-refresh'"></i> 今すぐ同期
            </div>
          </div>
        </div>
        <!-- クチコミ管理 -->
        <div class="row">
          <span class="ico" v-html="reviewIcon"></span>
          <div class="row-txt">
            <div class="row-name">クチコミ管理</div>
            <div class="row-des">GBPのクチコミの反映・確認・返信</div>
          </div>
          <el-switch v-if="info.connected" v-model="info.review_on" :active-value="1" :inactive-value="0" @change="save({ review_on: info.review_on }, 'review_on')"></el-switch>
        </div>
        <!-- Instagram×GBP -->
        <div class="row">
          <span class="ico" v-html="igIcon"></span>
          <div class="row-txt">
            <div class="row-name">Instagram×GBP投稿連動</div>
            <div class="row-des">Instagramの投稿をGBPに反映</div>
          </div>
          <el-switch v-if="info.connected" v-model="info.ins_on" :active-value="1" :inactive-value="0" @change="save({ ins_on: info.ins_on }, 'ins_on')"></el-switch>
        </div>
        <div class="row post" v-if="info.connected && info.ins_on == 1">
          <div class="row-txt"><div class="row-name normal">Google側への投稿を設定・管理</div></div>
          <div class="btn-outline blue shou" @click="$router.push('/gbpPosts')">投稿管理→</div>
        </div>
      </div>
    </template>

    <!-- 連携解除の確認 -->
    <div class="mask" v-if="showUnlink">
      <div class="dialog">
        <div class="d-title">{{ info.email || 'このアカウント' }}との<br />GBP連携を解除してよろしいですか？</div>
        <div class="d-sub">解除すると、このアカウントでのGBP連携機能は全て解除されます。</div>
        <div class="d-btns">
          <div class="d-cancel shou" @click="showUnlink = false">キャンセル</div>
          <div class="d-ok shou" @click="unlink">解除する</div>
        </div>
      </div>
    </div>

    <!-- 連携する店舗の選択・確認（GBP連携_2026.10.11 §6 / Figma 1502-5223） -->
    <div class="mask" v-if="pick.show">
      <div class="dialog pick">
        <i class="el-icon-close pick-close shou" @click="closePick"></i>
        <div class="d-title">連携する店舗を選択してください</div>
        <div class="d-sub">このGoogleアカウントで管理している店舗の中から、Card-Sanと連携する店舗を1つ選んでください。</div>
        <div class="pick-list">
          <div v-for="c in pick.candidates" :key="c.name" class="pick-item shou" :class="pick.selected == c.name ? 'on' : ''" @click="pick.selected = c.name">
            <span class="radio"></span>
            <div class="pick-txt">
              <div class="pick-title">{{ c.title || c.name }}</div>
              <div class="pick-addr" v-if="c.address">{{ c.address }}</div>
            </div>
          </div>
        </div>
        <div class="pick-btn shou" :class="!pick.selected || pick.saving ? 'dis' : ''" @click="confirmPick">{{ pick.saving ? '処理中…' : '選択した店舗を連携する' }}</div>
      </div>
    </div>

    <transition name="c-toast"><div class="c-toast" v-if="toast">{{ toast }}</div></transition>
  </div>
</template>

<script>
import { gbpOverview, gbpAuthUrl, gbpConnect, gbpSelectLocation, gbpDisconnect, gbpFeatures, gbpSyncNow } from "@/http/api.js";
import { G_ICON, INFO_ICON, REVIEW_ICON, IG_ICON } from "./icons.js";

export default {
  name: "googleBusiness",
  data() {
    return {
      gIcon: G_ICON,
      infoIcon: INFO_ICON,
      reviewIcon: REVIEW_ICON,
      igIcon: IG_ICON,
      loaded: false,
      denied: "",
      info: { connected: false, configured: true, sync_fields: [], basic_on: 0, review_on: 0, ins_on: 0, auto_sync: 0 },
      showUnlink: false,
      connecting: false,
      syncing: false,
      toast: "",
      toastTimer: null,
      pick: { show: false, candidates: [], selected: "", token: "", saving: false },
    };
  },
  created() {
    let q = this.$route.query;
    if (q.gbp_code) {
      this.finishConnect(q.gbp_code, q.state);
    } else {
      if (q.gbp_error) this.$message({ message: q.gbp_error == "access_denied" ? "Googleでのアクセスが許可されませんでした" : "Googleの認証に失敗しました", type: "error", offset: 400 });
      this.load();
    }
  },
  methods: {
    msg(res, okText) {
      if (res && res.code == 200) {
        if (okText) this.$message({ message: okText, type: "success", offset: 400 });
        return true;
      }
      this.$message({ message: (res && res.message) || "エラーが発生しました", type: "error", offset: 400 });
      return false;
    },
    load() {
      gbpOverview().then((res) => {
        this.loaded = true;
        if (res && res.code == 200) {
          let d = Object.assign({}, this.info, res.data);
          // App版と同じく数値化（"1"/"0" で返ってもスイッチ・判定が正しく動くように）
          ["basic_on", "review_on", "ins_on", "auto_sync"].forEach((k) => { d[k] = Number(d[k]) || 0; });
          if (!Array.isArray(d.sync_fields)) d.sync_fields = [];
          this.info = d;
        } else if (res && res.code == 403) {
          this.denied = res.message;
        }
      });
    },
    connect() {
      if (this.connecting) return;
      this.connecting = true;
      gbpAuthUrl().then((res) => {
        this.connecting = false;
        if (this.msg(res)) window.location.href = res.data.url;
      }).catch(() => { this.connecting = false; });
    },
    finishConnect(code, state) {
      this.connecting = true;
      gbpConnect({ code, state }).then((res) => {
        this.connecting = false;
        this.$router.replace({ path: "/googleBusiness" });
        if (res && res.code == 200 && res.data && res.data.need_select == 1) {
          // 店舗候補をユーザーに選ばせる（1件でも省略せず表示・選択済みにする）
          let list = res.data.candidates || [];
          if (!list.length) {
            this.$message({ message: "このGoogleアカウントにビジネス（店舗）が登録されていません。Googleビジネスプロフィールで店舗を登録してから連携してください。", type: "error", offset: 400, duration: 6000 });
          } else {
            this.pick = { show: true, candidates: list, selected: list.length == 1 ? list[0].name : "", token: res.data.select_token, saving: false };
          }
          this.load();
          return;
        }
        if (this.msg(res, "Googleビジネスプロフィールと連携しました")) this.info = Object.assign({}, this.info, res.data);
        this.load();
      }).catch(() => { this.connecting = false; this.$router.replace({ path: "/googleBusiness" }); this.load(); });
    },
    confirmPick() {
      if (!this.pick.selected || this.pick.saving) return;
      this.pick.saving = true;
      gbpSelectLocation({ select_token: this.pick.token, location_name: this.pick.selected }).then((res) => {
        this.pick.saving = false;
        if (this.msg(res, "Googleビジネスプロフィールと連携しました")) {
          this.pick.show = false;
          this.load();
        }
      }).catch(() => { this.pick.saving = false; });
    },
    closePick() {
      this.pick.show = false;
      this.load();
    },
    showToast(text) {
      this.toast = text;
      clearTimeout(this.toastTimer);
      this.toastTimer = setTimeout(() => { this.toast = ""; }, 2000);
    },
    unlink() {
      this.showUnlink = false;
      gbpDisconnect().then((res) => {
        if (this.msg(res, "連携を解除しました")) this.load();
      });
    },
    save(data, key) {
      gbpFeatures(data).then((res) => {
        if (res && res.code == 200) {
          this.info = Object.assign({}, this.info, res.data);
          if (key == "review_on" && data.review_on) this.$message({ message: "クチコミ管理をONにしました。上部の「クチコミ」から確認できます", type: "success", offset: 400 });
        } else {
          this.msg(res);
          this.load();
        }
      });
    },
    syncNow() {
      if (this.syncing || this.info.auto_sync == 1) return;
      if (!this.info.sync_fields.length) return this.$message({ message: "同期する項目を選択してください", type: "error", offset: 400 });
      this.syncing = true;
      gbpSyncNow().then((res) => {
        this.syncing = false;
        if (res && res.code == 200) {
          let d = (res.data && res.data.detail) || {};
          let notes = Object.keys(d).filter((k) => d[k] != "ok" && !/枚追加$/.test(d[k])).map((k) => d[k]);
          this.showToast("同期しました");
          // 一部項目が反映できなかった場合だけ内容を補足表示
          if (notes.length) this.$message({ message: notes.join("／"), type: "warning", offset: 400, duration: 6000 });
          this.info.last_sync_at = res.data.last_sync_at;
        } else {
          this.msg(res);
        }
      }).catch(() => { this.syncing = false; });
    },
  },
};
</script>

<style lang="scss" scoped>
@import "./gbp.scss";
.pick { position: relative; display: flex; flex-direction: column; max-height: calc(100vh - 80px); }
.pick-close { position: absolute; right: 16px; top: 16px; font-size: 18px; color: #8e8e93; }
.pick-list { margin-top: 20px; overflow-y: auto; flex: 1; min-height: 0; text-align: left; }
.pick-item { display: flex; align-items: flex-start; border: 1px solid #d2d2d7; border-radius: 8px; padding: 14px 16px; margin-bottom: 10px; }
.pick-item:last-child { margin-bottom: 0; }
.pick-item.on { border-color: #1a73e8; background: #e8f0fe; }
.radio { width: 18px; height: 18px; border: 1.5px solid #8e8e93; border-radius: 50%; margin: 2px 12px 0 0; flex-shrink: 0; box-sizing: border-box; position: relative; }
.pick-item.on .radio { border-color: #1a73e8; }
.pick-item.on .radio::after { content: ""; position: absolute; left: 3px; top: 3px; width: 9px; height: 9px; border-radius: 50%; background: #1a73e8; }
.pick-txt { flex: 1; min-width: 0; }
.pick-title { font-size: 15px; color: #1d1d1f; font-weight: bold; word-break: break-all; }
.pick-addr { font-size: 13px; color: #707070; margin-top: 4px; line-height: 1.5; word-break: break-all; }
.pick-btn { margin-top: 20px; flex-shrink: 0; background: #1a73e8; color: #fff; height: 46px; line-height: 46px; border-radius: 6px; font-size: 16px; }
</style>
