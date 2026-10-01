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
            <div class="btn-sync shou" :class="syncing ? 'dis' : ''" @click="syncNow">
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
            <div class="row-des">Instagramの投稿をGBP最新情報に反映</div>
          </div>
          <el-switch v-if="info.connected" v-model="info.ins_on" :active-value="1" :inactive-value="0" @change="save({ ins_on: info.ins_on }, 'ins_on')"></el-switch>
        </div>
        <div class="row post" v-if="info.connected">
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
  </div>
</template>

<script>
import { gbpOverview, gbpAuthUrl, gbpConnect, gbpDisconnect, gbpFeatures, gbpSyncNow } from "@/http/api.js";
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
          this.info = Object.assign({}, this.info, res.data);
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
        if (this.msg(res, "Googleビジネスプロフィールと連携しました")) this.info = Object.assign({}, this.info, res.data);
        this.load();
      }).catch(() => { this.connecting = false; this.load(); });
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
      if (this.syncing) return;
      if (!this.info.sync_fields.length) return this.$message({ message: "同期する項目を選択してください", type: "error", offset: 400 });
      this.syncing = true;
      gbpSyncNow().then((res) => {
        this.syncing = false;
        if (res && res.code == 200) {
          let d = (res.data && res.data.detail) || {};
          let notes = Object.keys(d).filter((k) => d[k] != "ok" && !/枚追加$/.test(d[k])).map((k) => d[k]);
          this.$message({ message: res.message + (notes.length ? "（" + notes.join("／") + "）" : ""), type: notes.length ? "warning" : "success", offset: 400, duration: notes.length ? 6000 : 3000 });
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
</style>
