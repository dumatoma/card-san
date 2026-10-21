<template>
    <view class="page">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="Googleビジネス連携" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- 権限・プランで利用不可（403） -->
        <view class="denied" v-if="denied">{{ denied }}</view>

        <view v-else>
            <!-- ===== 連携アカウントセクション ===== -->
            <view class="accountSection">
                <view class="gbCard">
                    <image class="gbIcon" src="../../../static/svg/setting_g_mono.svg" mode="aspectFit"></image>
                    <view class="gbText">
                        <view class="gbEmail">{{ info.connected ? (info.email || info.location_title || 'Google Business Profile') : 'Google Business Profile' }}</view>
                        <view class="gbStatus" :class="info.connected ? '' : 'off'">{{ info.connected ? '[連携済み]' : '[未連携]' }}</view>
                        <view class="gbLoc" v-if="info.connected && info.location_title">店舗：{{ info.location_title }}</view>
                    </view>
                </view>
                <view class="gbDesc">Googleビジネスプロフィールと連携すると、店舗情報の同期やクチコミ管理が使えます。</view>
                <view class="notice" v-if="loaded && !info.configured && !info.connected">
                    Googleビジネス連携は現在準備中です。準備が整い次第ご利用いただけます。
                </view>
                <view class="btnDisconnect" v-if="info.connected" @click="openDisconnect">連携を解除</view>
                <view class="btnConnect" v-else :class="connecting ? 'dis' : ''" @click="connect">{{ connecting ? '処理中…' : '連携する' }}</view>
            </view>

            <view class="sectionLabel">この連携で使える機能</view>

            <!-- ===== 基本情報 ===== -->
            <view class="featureRow">
                <image class="featureIcon" :src="icons.info" mode="aspectFit"></image>
                <view class="featureText">
                    <view class="featureTitle">基本情報</view>
                    <view class="featureSub">営業時間・住所・業種・写真をGBPと同期</view>
                </view>
                <view v-if="info.connected" class="sw" :class="info.basic_on == 1 ? 'on' : ''" @click="toggle('basic_on')">
                    <view class="knob"></view>
                </view>
            </view>

            <!-- 自動同期設定パネル（基本情報ONの間のみ） -->
            <view class="autosyncPanel" v-if="info.connected && info.basic_on == 1">
                <view class="autosyncHeader">
                    <text class="autosyncTitle">自動同期設定</text>
                    <view class="sw" :class="info.auto_sync == 1 ? 'on' : ''" @click="toggle('auto_sync')">
                        <view class="knob"></view>
                    </view>
                </view>
                <view class="autosyncGrid">
                    <view class="autosyncItem" v-for="f in fieldList" :key="f.key"
                        :class="isFieldOn(f.key) ? 'selected' : ''" @click="toggleField(f.key)">
                        <view class="dot"><view class="dotInner"></view></view>
                        <text>{{ f.label }}</text>
                    </view>
                </view>
                <view class="autosyncFooter">
                    <text class="lastsync">最終同期:{{ fmtSync(info.last_sync_at) }}</text>
                    <view class="btnSync" :class="(info.auto_sync == 1 || syncing) ? 'isDisabled' : ''" @click="syncNow">
                        <image class="syncIcon" :src="(info.auto_sync == 1 || syncing) ? icons.syncOff : icons.sync" mode="aspectFit"></image>
                        <text>今すぐ同期</text>
                    </view>
                </view>
            </view>

            <!-- ===== クチコミ管理 ===== -->
            <view class="featureRow">
                <image class="featureIcon" :src="icons.review" mode="aspectFit"></image>
                <view class="featureText">
                    <view class="featureTitle">クチコミ管理</view>
                    <view class="featureSub">GBPのクチコミの反映・確認・返信</view>
                </view>
                <view v-if="info.connected" class="sw" :class="info.review_on == 1 ? 'on' : ''" @click="toggle('review_on')">
                    <view class="knob"></view>
                </view>
            </view>

            <!-- ===== Instagram×GBP投稿連動 ===== -->
            <view class="featureRow">
                <image class="featureIcon" :src="icons.ig" mode="aspectFit"></image>
                <view class="featureText">
                    <view class="featureTitle">Instagram×GBP投稿連動</view>
                    <view class="featureSub">Instagramの投稿をGBPに反映</view>
                </view>
                <view v-if="info.connected" class="sw" :class="info.ins_on == 1 ? 'on' : ''" @click="toggle('ins_on')">
                    <view class="knob"></view>
                </view>
            </view>

            <!-- 投稿管理への導線（Instagram×GBP投稿連動ONの間のみ） -->
            <view class="postmanageRow" v-if="info.connected && info.ins_on == 1">
                <text class="postmanageText">Google側への投稿を設定・管理</text>
                <view class="btnPostmanage" @click="toPostManage">投稿管理→</view>
            </view>
        </view>

        <!-- 同期完了トースト（画面中央） -->
        <view class="syncToast" :class="toastShow ? 'show' : ''">{{ toastText }}</view>

        <!-- 連携を解除 確認オーバーレイ（下からスライドイン） -->
        <view class="dcOverlay" :class="dcShow ? 'show' : ''" @touchmove.stop.prevent="noop" @click="closeDisconnect">
            <view class="dcCard" @click.stop="noop">
                <view class="dcTitle">{{ info.email || 'このアカウント' }}とのGBP連携を解除してよろしいですか？</view>
                <view class="dcSub">解除すると、このアカウントでのGBP連携機能は全て解除されます。</view>
                <view class="dcActions">
                    <view class="dcCancel" @click="closeDisconnect">キャンセル</view>
                    <view class="dcConfirm" :class="disconnecting ? 'dis' : ''" @click="doDisconnect">解除する</view>
                </view>
            </view>
        </view>
    </view>
</template>

<script>
    import {
        gbpOverview, gbpAuthUrl, gbpConnect, gbpDisconnect, gbpFeatures, gbpSyncNow
    } from '@/api/gbp.js'

    // モックアップ（GBP連携_App版UI.html）のアイコン
    function svgUri(svg) {
        return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg)
    }
    const SYNC_SVG = (c) => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="16" viewBox="0 0 20 16" fill="none">' +
        '<path d="M2 8a8 6.3 0 0 1 13.5-4.2M2 8V2M2 8h6" stroke="' + c + '" stroke-width="1.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>' +
        '<path d="M18 8a8 6.3 0 0 1-13.5 4.2M18 8v6M18 8h-6" stroke="' + c + '" stroke-width="1.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>'
    const ICONS = {
        info: svgUri('<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none">' +
            '<circle cx="13" cy="13" r="10.5" stroke="#1d1d1f" stroke-width="1.6"/><circle cx="13" cy="8.6" r="1.15" fill="#1d1d1f"/>' +
            '<rect x="12" y="11.5" width="2" height="7.5" rx="1" fill="#1d1d1f"/></svg>'),
        review: svgUri('<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none">' +
            '<path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h13A2.5 2.5 0 0 1 22 6.5v9A2.5 2.5 0 0 1 19.5 18H10l-4.5 4v-4H6.5A2.5 2.5 0 0 1 4 15.5v-9Z" stroke="#1d1d1f" stroke-width="1.5" stroke-linejoin="round"/>' +
            '<circle cx="9.5" cy="11" r="1.1" fill="#1d1d1f"/><circle cx="13" cy="11" r="1.1" fill="#1d1d1f"/><circle cx="16.5" cy="11" r="1.1" fill="#1d1d1f"/></svg>'),
        ig: svgUri('<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 26 26" fill="none">' +
            '<rect x="3.5" y="3.5" width="19" height="19" rx="5.5" stroke="#1d1d1f" stroke-width="1.5"/>' +
            '<circle cx="13" cy="13" r="4.6" stroke="#1d1d1f" stroke-width="1.5"/><circle cx="18.3" cy="7.7" r="1.05" fill="#1d1d1f"/></svg>'),
        sync: svgUri(SYNC_SVG('#1d1d1f')),
        syncOff: svgUri(SYNC_SVG('#aaaaaa')),
    }

    function safeDecode(v) {
        if (!v) return ''
        try { return decodeURIComponent(v) } catch (e) { return v }
    }

    export default {
        data() {
            return {
                icons: ICONS,
                fieldList: [
                    { key: 'hours', label: '営業時間' },
                    { key: 'address', label: '住所' },
                    { key: 'category', label: '業種' },
                    { key: 'photos', label: '写真' },
                ],
                loaded: false,
                denied: '',
                info: { connected: false, configured: true, email: '', location_title: '', sync_fields: [], basic_on: 0, review_on: 0, ins_on: 0, auto_sync: 0, last_sync_at: '' },
                connecting: false,
                syncing: false,
                disconnecting: false,
                saving: false,
                dcShow: false,
                toastShow: false,
                toastText: '',
                toastTimer: null,
            }
        },
        onLoad(options) {
            // 外部ブラウザでのGoogle認証後、App.vue（cardsan://gbp?...）から渡される
            uni.$on('gbpOAuthCallback', this.onOAuthCallback)
            // 店舗選択画面（gbpSelectLocation）で連携が完了した
            uni.$on('gbpConnected', this.onConnected)
            if (options && (options.gbp_code || options.gbp_error)) {
                this.onOAuthCallback({ code: safeDecode(options.gbp_code), state: safeDecode(options.state), error: safeDecode(options.gbp_error) })
            } else {
                this.load()
            }
        },
        onUnload() {
            uni.$off('gbpOAuthCallback', this.onOAuthCallback)
            uni.$off('gbpConnected', this.onConnected)
            if (this.toastTimer) clearTimeout(this.toastTimer)
        },
        methods: {
            noop() {},
            // 仕様：「最終同期:2026/07/06 15:30」形式
            fmtSync(v) {
                if (!v) return '－'
                return String(v).replace(/-/g, '/').replace('T', ' ').substr(0, 16)
            },
            // 403（権限・プラン）などサーバーのメッセージを取り出す
            errMsg(res) {
                return (res && res.message) || 'エラーが発生しました'
            },
            showErr(res) {
                uni.showToast({ title: this.errMsg(res), icon: 'none', duration: 2500 })
            },
            // HTTP 403 で返ってきた場合（request.js は reject する）
            catchErr(err) {
                let r = err && err.response
                if (r && r.status == 403) {
                    this.denied = (r.data && r.data.message) || 'この機能を利用する権限がありません'
                    return
                }
                uni.showToast({ title: (r && r.data && r.data.message) || '通信エラーが発生しました', icon: 'none' })
            },
            applyData(d) {
                if (!d || typeof d !== 'object') return
                let info = Object.assign({}, this.info, d)
                if (!Array.isArray(info.sync_fields)) info.sync_fields = []
                ;['basic_on', 'review_on', 'ins_on', 'auto_sync'].forEach(k => { info[k] = Number(info[k]) || 0 })
                info.connected = !!(info.connected && info.connected != 0)
                this.info = info
            },
            load() {
                gbpOverview().then(res => {
                    this.loaded = true
                    if (res && res.code == 200) {
                        this.denied = ''
                        this.applyData(res.data)
                    } else if (res && res.code == 403) {
                        this.denied = this.errMsg(res)
                    }
                }).catch(err => {
                    this.loaded = true
                    this.catchErr(err)
                })
            },

            // ===== 連携（OAuth） =====
            connect() {
                if (this.connecting) return
                this.connecting = true
                gbpAuthUrl().then(res => {
                    this.connecting = false
                    if (res && res.code == 200 && res.data && res.data.url) {
                        // Google のポリシーにより埋め込みWebViewは不可。端末標準ブラウザで開き、cardsan://gbp で戻ってくる
                        // #ifdef APP-PLUS
                        plus.runtime.openURL(res.data.url)
                        // #endif
                        // #ifndef APP-PLUS
                        window.location.href = res.data.url
                        // #endif
                    } else if (res && res.code == 403) {
                        this.denied = this.errMsg(res)
                    } else if (res) {
                        this.showErr(res)
                    }
                }).catch(err => {
                    this.connecting = false
                    this.catchErr(err)
                })
            },
            onOAuthCallback(p) {
                p = p || {}
                if (p.error || !p.code) {
                    uni.showToast({
                        title: p.error == 'access_denied' ? 'Googleでのアクセスが許可されませんでした' : 'Googleの認証に失敗しました',
                        icon: 'none', duration: 2500
                    })
                    this.load()
                    return
                }
                if (this.connecting) return
                this.connecting = true
                uni.showLoading({ title: '連携中...', mask: true })
                gbpConnect({ code: p.code, state: p.state || '' }).then(res => {
                    this.connecting = false
                    uni.hideLoading()
                    if (res && res.code == 200) {
                        let d = res.data || {}
                        if (d.need_select == 1) {
                            // 連携する店舗の選択・確認画面へ（候補は画面間でストレージ受け渡し）
                            uni.setStorageSync('gbp_select', {
                                select_token: d.select_token || '',
                                candidates: d.candidates || [],
                                email: d.email || '',
                                message: d.message || res.message || '',
                            })
                            uni.navigateTo({ url: '/pages/settings/gbpSelectLocation/gbpSelectLocation' })
                            this.load()
                            return
                        }
                        uni.showToast({ title: 'Googleビジネスプロフィールと連携しました', icon: 'none', duration: 2500 })
                        this.applyData(d)
                        this.load()
                    } else if (res && res.code == 403) {
                        this.denied = this.errMsg(res)
                    } else {
                        if (res) this.showErr(res)
                        this.load()
                    }
                }).catch(err => {
                    this.connecting = false
                    uni.hideLoading()
                    this.catchErr(err)
                    this.load()
                })
            },
            onConnected(d) {
                if (d) this.applyData(d)
                this.load()
            },

            // ===== 機能トグル =====
            save(data, rollback) {
                this.saving = true
                return gbpFeatures(data).then(res => {
                    this.saving = false
                    if (res && res.code == 200) {
                        this.applyData(res.data)
                        return true
                    }
                    rollback && rollback()
                    if (res) this.showErr(res)
                    this.load()
                    return false
                }).catch(err => {
                    this.saving = false
                    rollback && rollback()
                    this.catchErr(err)
                    return false
                })
            },
            toggle(key) {
                if (this.saving) return
                let old = this.info[key]
                let val = old == 1 ? 0 : 1
                this.info[key] = val
                let data = {}
                data[key] = val
                this.save(data, () => { this.info[key] = old }).then(ok => {
                    if (ok && key == 'review_on' && val == 1) {
                        uni.showToast({ title: 'クチコミ管理をONにしました', icon: 'none' })
                    }
                })
            },
            isFieldOn(k) {
                return (this.info.sync_fields || []).indexOf(k) > -1
            },
            toggleField(k) {
                if (this.saving) return
                let old = (this.info.sync_fields || []).slice()
                let list = this.isFieldOn(k) ? old.filter(x => x != k) : old.concat([k])
                this.info.sync_fields = list
                this.save({ sync_fields: list }, () => { this.info.sync_fields = old })
            },

            // ===== 今すぐ同期 =====
            syncNow() {
                // 自動同期ONの間は手動同期不要のため押下不可
                if (this.info.auto_sync == 1 || this.syncing) return
                if (!this.info.sync_fields || !this.info.sync_fields.length) {
                    uni.showToast({ title: '同期する項目を選択してください', icon: 'none' })
                    return
                }
                this.syncing = true
                gbpSyncNow().then(res => {
                    this.syncing = false
                    if (res && res.code == 200) {
                        let d = (res.data && res.data.detail) || {}
                        let notes = Object.keys(d).filter(k => d[k] != 'ok' && !/枚追加$/.test(d[k])).map(k => d[k])
                        if (res.data && res.data.last_sync_at) this.info.last_sync_at = res.data.last_sync_at
                        if (notes.length) {
                            uni.showModal({ title: '同期しました', content: notes.join('\n'), showCancel: false })
                        } else {
                            this.showToast('同期しました')
                        }
                    } else if (res) {
                        this.showErr(res)
                    }
                }).catch(err => {
                    this.syncing = false
                    this.catchErr(err)
                })
            },
            showToast(text) {
                this.toastText = text
                this.toastShow = true
                if (this.toastTimer) clearTimeout(this.toastTimer)
                this.toastTimer = setTimeout(() => { this.toastShow = false }, 1800)
            },

            // ===== 連携解除 =====
            openDisconnect() {
                this.dcShow = true
            },
            closeDisconnect() {
                if (this.disconnecting) return
                this.dcShow = false
            },
            doDisconnect() {
                if (this.disconnecting) return
                this.disconnecting = true
                gbpDisconnect().then(res => {
                    this.disconnecting = false
                    if (res && res.code == 200) {
                        // API成功後に未連携表示へ切り替え
                        this.dcShow = false
                        this.info = Object.assign({}, this.info, {
                            connected: false, email: '', location_title: '', basic_on: 0, review_on: 0, ins_on: 0, auto_sync: 0
                        })
                        uni.showToast({ title: '連携を解除しました', icon: 'none' })
                        this.load()
                    } else if (res) {
                        this.showErr(res)
                    }
                }).catch(err => {
                    this.disconnecting = false
                    this.dcShow = false
                    this.catchErr(err)
                })
            },

            toPostManage() {
                uni.navigateTo({ url: '/pages/settings/socialMedia/socialMedia' })
            },
        }
    }
</script>

<style lang="scss">
    /* モックアップ（393px幅）の px を rpx に換算（×約1.9） */
    $text: #1d1d1f;
    $sub: #707070;
    $blue: #1a73e8;
    $border: #d2d2d7;

    page {
        background: #fff;
    }

    .page {
        min-height: 100vh;
        background: #fff;
        color: $text;
        border-top: 1px solid $border;
        padding-bottom: 60rpx;
    }

    .denied {
        margin: 60rpx 38rpx;
        padding: 40rpx 30rpx;
        border: 1px solid $border;
        border-radius: 12rpx;
        font-size: 27rpx;
        line-height: 1.7;
        color: $text;
        text-align: center;
    }

    /* ===== 連携アカウントセクション ===== */
    .accountSection {
        padding: 38rpx;
        border-bottom: 1px solid $border;
    }

    .gbCard {
        display: flex;
        align-items: center;
        padding: 21rpx 38rpx;
        background: #fff;
        border: 1px solid $border;
        border-radius: 12rpx;
        box-shadow: 2rpx 4rpx 8rpx 2rpx rgba(0, 0, 0, .15);
        margin-bottom: 30rpx;
    }

    .gbIcon {
        width: 50rpx;
        height: 50rpx;
        flex-shrink: 0;
        border-radius: 12rpx;
        margin-right: 27rpx;
    }

    .gbText {
        flex: 1;
        min-width: 0;
    }

    .gbEmail {
        font-size: 27rpx;
        color: $text;
        line-height: 1.4;
        word-break: break-all;
    }

    .gbStatus {
        font-size: 27rpx;
        color: $blue;
        line-height: 1.4;

        &.off {
            color: $sub;
        }
    }

    .gbLoc {
        font-size: 23rpx;
        color: $sub;
        line-height: 1.4;
        margin-top: 4rpx;
    }

    .gbDesc {
        font-size: 27rpx;
        color: $text;
        line-height: 1.6;
        margin-bottom: 30rpx;
    }

    .notice {
        font-size: 24rpx;
        color: #b26a00;
        background: #fff7e6;
        border-radius: 10rpx;
        padding: 18rpx 24rpx;
        line-height: 1.6;
        margin-bottom: 30rpx;
    }

    .btnDisconnect {
        width: 100%;
        padding: 29rpx 0;
        text-align: center;
        background: #f5f5f7;
        border: 1px solid $text;
        border-radius: 19rpx;
        font-size: 27rpx;
        color: $text;
    }

    .btnConnect {
        width: 100%;
        padding: 29rpx 0;
        text-align: center;
        background: $blue;
        border: 1px solid $blue;
        border-radius: 19rpx;
        font-size: 27rpx;
        color: #fff;

        &.dis {
            opacity: .6;
        }
    }

    .sectionLabel {
        padding: 40rpx 38rpx;
        font-size: 27rpx;
        color: $sub;
        border-bottom: 1px solid $border;
    }

    /* ===== 機能行 ===== */
    .featureRow {
        display: flex;
        align-items: center;
        padding: 32rpx 38rpx 38rpx;
        border-bottom: 1px solid $border;
    }

    .featureIcon {
        width: 50rpx;
        height: 50rpx;
        flex-shrink: 0;
        margin-right: 23rpx;
    }

    .featureText {
        flex: 1;
        min-width: 0;
        margin-right: 23rpx;
    }

    .featureTitle {
        font-size: 31rpx;
        color: #000;
        margin-bottom: 10rpx;
    }

    .featureSub {
        font-size: 23rpx;
        color: $sub;
    }

    /* トグルスイッチ（60×30px） */
    .sw {
        position: relative;
        width: 114rpx;
        height: 57rpx;
        flex-shrink: 0;
        box-sizing: border-box;
        background: #fff;
        border: 1px solid #aaa;
        border-radius: 38rpx;
        box-shadow: 0 4rpx 4rpx rgba(0, 0, 0, .1);
        transition: all .2s;

        .knob {
            position: absolute;
            top: 4rpx;
            left: 4rpx;
            width: 46rpx;
            height: 46rpx;
            box-sizing: border-box;
            background: #fff;
            border: 1px solid #aaa;
            border-radius: 50%;
            box-shadow: 0 2rpx 4rpx rgba(0, 0, 0, .2);
            transition: all .2s;
        }

        &.on {
            background: $blue;
            border-color: $blue;

            .knob {
                left: 60rpx;
                border-color: $blue;
            }
        }
    }

    /* ===== 自動同期設定パネル ===== */
    .autosyncPanel {
        padding: 30rpx 38rpx 38rpx;
        border-bottom: 1px solid $border;
        background: #fff;
    }

    .autosyncHeader {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30rpx;
    }

    .autosyncTitle {
        font-size: 31rpx;
        color: #000;
    }

    .autosyncGrid {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 30rpx;
    }

    .autosyncItem {
        width: 50%;
        display: flex;
        align-items: center;
        font-size: 27rpx;
        color: $sub;
        padding: 11rpx 0;

        .dot {
            width: 27rpx;
            height: 27rpx;
            box-sizing: border-box;
            border: 1px solid $sub;
            border-radius: 50%;
            margin-right: 15rpx;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .dotInner {
            width: 13rpx;
            height: 13rpx;
            border-radius: 50%;
            background: transparent;
        }

        &.selected {
            color: $text;

            .dot {
                border-color: $blue;
                background: #fff;
            }

            .dotInner {
                background: $blue;
            }
        }
    }

    .autosyncFooter {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .lastsync {
        font-size: 23rpx;
        color: $sub;
    }

    .btnSync {
        display: flex;
        align-items: center;
        height: 72rpx;
        padding: 0 27rpx;
        box-sizing: border-box;
        background: rgba(29, 29, 31, .1);
        border: 1px solid $text;
        border-radius: 11rpx;
        font-size: 27rpx;
        color: $text;

        .syncIcon {
            width: 31rpx;
            height: 27rpx;
            margin-right: 11rpx;
        }

        &.isDisabled {
            background: #fff;
            border-color: #aaaaaa;
            color: #aaaaaa;
        }
    }

    /* ===== 投稿管理リンク行 ===== */
    .postmanageRow {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 30rpx 38rpx 114rpx;
        border-bottom: 1px solid $border;
    }

    .postmanageText {
        font-size: 27rpx;
        color: $text;
        margin-right: 23rpx;
    }

    .btnPostmanage {
        flex-shrink: 0;
        padding: 17rpx 30rpx;
        background: rgba(26, 115, 232, .1);
        border: 1px solid $blue;
        border-radius: 11rpx;
        font-size: 27rpx;
        color: $blue;
        white-space: nowrap;
    }

    /* ===== 同期完了トースト（画面中央） ===== */
    .syncToast {
        position: fixed;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%) scale(.96);
        background: rgba(40, 40, 40, .92);
        color: #fff;
        font-size: 27rpx;
        padding: 23rpx 46rpx;
        border-radius: 38rpx;
        white-space: nowrap;
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transition: opacity .25s ease, transform .25s ease;

        &.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
    }

    /* ===== 連携を解除 確認オーバーレイ ===== */
    .dcOverlay {
        position: fixed;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        z-index: 999;
        background: rgba(0, 0, 0, .45);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity .3s ease, visibility .3s ease;

        &.show {
            opacity: 1;
            visibility: visible;

            .dcCard {
                transform: translateY(0);
            }
        }
    }

    .dcCard {
        width: 597rpx;
        box-sizing: border-box;
        background: #fff;
        border-radius: 27rpx;
        padding: 46rpx 38rpx;
        text-align: center;
        box-shadow: 0 19rpx 76rpx rgba(0, 0, 0, .3);
        transform: translateY(60vh);
        transition: transform .32s cubic-bezier(.32, .72, 0, 1);
    }

    .dcTitle {
        font-size: 29rpx;
        color: $text;
        line-height: 1.6;
        margin-bottom: 23rpx;
        font-weight: 600;
        word-break: break-all;
    }

    .dcSub {
        font-size: 25rpx;
        color: $sub;
        line-height: 1.6;
        margin-bottom: 38rpx;
    }

    .dcActions {
        display: flex;
    }

    .dcCancel,
    .dcConfirm {
        flex: 1;
        padding: 23rpx 0;
        border-radius: 19rpx;
        font-size: 29rpx;
        text-align: center;
    }

    .dcCancel {
        background: #e5e5ea;
        color: #8a8a8e;
        margin-right: 19rpx;
    }

    .dcConfirm {
        background: #ff3b30;
        color: #fff;
        font-weight: 600;

        &.dis {
            opacity: .6;
        }
    }
</style>
