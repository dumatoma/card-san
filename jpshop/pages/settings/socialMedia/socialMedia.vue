<template>
    <!-- [カスタムエイド 2026-10-22] Instagram×GBP投稿管理（UI/UX仕様 App版 2026-08-25 準拠で作り直し） -->
    <view class="page">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="Instagram×GBP投稿管理" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- 投稿管理／連動履歴 -->
        <view class="screenTabs">
            <view class="screenTab" :class="screen == 'posts' ? 'act' : ''" @click="switchScreen('posts')">投稿管理</view>
            <view class="screenTab" :class="screen == 'history' ? 'act' : ''" @click="switchScreen('history')">連動履歴</view>
        </view>

        <!-- ===== 投稿管理 ===== -->
        <view v-show="screen == 'posts'">
            <!-- Instagramアカウント行 -->
            <view class="igRow">
                <image v-if="ins.avatar && !avaErr" :src="ins.avatar" class="igAvatar" mode="aspectFill" @error="avaErr = true"></image>
                <view v-else class="igAvatar blank"></view>
                <view class="igTxt">
                    <view class="igName">{{ ins.connected && ins.username ? ins.username : 'Instagramアカウント' }}</view>
                    <view class="igStatus" :class="igOk ? '' : 'off'">{{ !ins.connected ? '[未連携]' : igExpired ? '[要再連携]' : '[連携済み]' }}</view>
                </view>
                <view class="igLink" v-if="loaded && !igOk" @click="toNoticeSetting">お知らせ設定へ ›</view>
            </view>

            <!-- 自動同期設定 -->
            <view class="toggleRow" :class="canAuto ? '' : 'off'">
                <view class="toggleTxt">
                    <view class="toggleLabel">自動同期設定</view>
                    <view class="toggleSub">Instagram投稿時にGBP最新情報へ自動転載</view>
                </view>
                <u-switch v-model="autoOn" activeColor="#1A73E8" :disabled="!canAuto || savingAuto" @change="saveAuto"></u-switch>
            </view>

            <!-- 注記ボックス -->
            <view class="noteBox">動画・リール投稿は連動の対象外です。Google側の投稿機能が写真のみ対応のため、動画を含む投稿はGBPへ転載されません。</view>
            <view class="warnBox" v-if="loaded && !gbpConnected">Googleビジネスプロフィールと連携されていないため、転載できません。[各種設定]＞[Googleビジネス連携]から連携してください。</view>
            <view class="warnBox" v-if="igError">{{ igError }}</view>

            <!-- セグメントコントロール（ここだけ固定表示） -->
            <view class="segWrap" :style="{ top: navTop + 'px' }">
                <view class="seg">
                    <view class="segBtn" :class="seg == 'ig' ? 'act' : ''" @click="switchSeg('ig')">Instagram投稿</view>
                    <view class="segBtn" :class="seg == 'gbp' ? 'act' : ''" @click="switchSeg('gbp')">GBP投稿</view>
                </view>
            </view>

            <!-- Instagram 未連携 -->
            <view class="flatPanel" v-if="loaded && !ins.connected">
                <view>Instagramと連携すると投稿を選択できます</view>
                <view class="flatLink" @click="toNoticeSetting">お知らせ設定へ ›</view>
            </view>

            <template v-else-if="loaded">
                <!-- Instagram投稿 -->
                <view class="list" v-if="seg == 'ig'">
                    <view class="listMsg" v-if="loading && !igAll.length">読み込み中…</view>
                    <view class="listMsg" v-else-if="!igAll.length">{{ igError ? '投稿を取得できませんでした' : 'まだ投稿がありません' }}</view>
                    <view v-for="it in igShownList" :key="it.id" class="postRow"
                        :class="it.status == 'synced' ? 'done' : it.status == 'video' ? 'blocked' : ''" @click="openPreview(it)">
                        <view class="stIcon done" v-if="it.status == 'synced'"><u-icon name="checkmark" color="#fff" size="13" bold></u-icon></view>
                        <view class="stIcon blocked" v-else-if="it.status == 'video'"><u-icon name="close" color="#fff" size="11" bold></u-icon></view>
                        <view class="stIcon pending" v-else></view>
                        <view class="postThumb">
                            <image v-if="it.image" :src="it.image" mode="aspectFill"></image>
                            <view class="play" v-if="it.status == 'video'"><u-icon name="play-right-fill" color="#fff" size="16"></u-icon></view>
                        </view>
                        <view class="postMain">
                            <view class="postCap">{{ it.caption || '（本文なし）' }}</view>
                            <view class="postMeta">
                                <text class="postDate">{{ dotDate(it.date) }}</text>
                                <text class="pill done" v-if="it.status == 'synced'">転載済み</text>
                                <text class="pill blocked" v-else-if="it.status == 'video'">動画(転載不可)</text>
                                <text class="pill pending" v-else>未転載</text>
                            </view>
                        </view>
                    </view>
                </view>

                <!-- GBP投稿（Instagramから転載済みの投稿のみ） -->
                <view class="list" v-else>
                    <view class="listMsg" v-if="loading && !gbpAll.length">読み込み中…</view>
                    <view class="listMsg" v-else-if="!gbpAll.length">{{ igError ? '投稿を取得できませんでした' : 'GBPへ転載した投稿はまだありません' }}</view>
                    <view v-for="it in gbpShownList" :key="'g' + it.id" class="postRow done">
                        <view class="stIcon done"><u-icon name="checkmark" color="#fff" size="13" bold></u-icon></view>
                        <view class="postThumb"><image v-if="it.image" :src="it.image" mode="aspectFill"></image></view>
                        <view class="postMain">
                            <view class="postCap">{{ it.caption || '（本文なし）' }}</view>
                            <view class="postMeta">
                                <text class="postDate">{{ dotDate(it.date) }}</text>
                                <text class="pill done">転載済み</text>
                            </view>
                        </view>
                    </view>
                </view>

                <!-- もっと見る（表示中のタブのみ） -->
                <view class="loadMoreWrap" v-if="curAllCount > 0">
                    <view class="btnLoadMore" v-if="curHasMore" :class="loading ? 'dis' : ''" @click="loadMore">{{ loading ? '読み込み中…' : 'もっと見る' }}</view>
                    <view class="loadMoreCount">{{ curShown }} / {{ curTotal }} 件を表示中</view>
                </view>
            </template>
        </view>

        <!-- ===== 連動履歴 ===== -->
        <view v-show="screen == 'history'" class="historyWrap">
            <view class="listMsg" v-if="loaded && !ins.connected">Instagramと連携すると履歴が表示されます</view>
            <view class="listMsg" v-else-if="historyLoading && !historyList.length">読み込み中…</view>
            <view class="listMsg" v-else-if="loaded && !historyList.length">連動履歴はまだありません</view>
            <view class="historyItem" v-for="it in historyList" :key="'h' + it.id">
                <view class="hThumb"><image v-if="it.image" :src="it.image" mode="aspectFill"></image></view>
                <text class="hArrow">→</text>
                <view class="hThumb gbp" v-if="it.status == 'synced'">G</view>
                <view class="hThumb gbp ng" v-else>✕</view>
                <view class="hBody">
                    <view class="hTitle">{{ it.caption || '（本文なし）' }}</view>
                    <view class="hTime">
                        <text>{{ dotDate(it.date) }}</text>
                        <text class="pill done" v-if="it.status == 'synced'">転載成功</text>
                        <text class="pill blocked" v-else>対象外（動画）</text>
                    </view>
                </view>
            </view>
            <view class="captionNote" v-if="historyList.length">動画・リール投稿は転載対象外として履歴にも記録されます（自動連動の場合）。</view>
            <view style="height: 100rpx;"></view>
        </view>

        <!-- ===== GBPへの転載プレビュー（下からせり上がるモーダルシート） ===== -->
        <view class="sheetOverlay" v-if="sheetVisible" :class="sheetShow ? 'show' : ''" @click="closePreview" @touchmove.stop.prevent="noop">
            <view class="previewSheet" @click.stop="noop">
                <view class="sheetHeader">
                    <text class="sheetTitle">GBPへの転載プレビュー</text>
                    <view class="closeX" @click="closePreview"><u-icon name="close" color="#333" size="16"></u-icon></view>
                </view>
                <view class="previewBody" v-if="preview">
                    <view class="previewCard">
                        <view class="previewMedia">
                            <swiper class="previewSwiper" :current="pvIndex" @change="pvIndex = $event.detail.current" v-if="pvImages.length">
                                <swiper-item v-for="(src, i) in pvImages" :key="i">
                                    <image :src="src" mode="aspectFill" class="previewImg"></image>
                                </swiper-item>
                            </swiper>
                            <view class="progressTrack" v-if="pvImages.length > 1">
                                <view class="progressFill" :style="{ width: (100 / pvImages.length) + '%', left: (pvIndex * 100 / pvImages.length) + '%' }"></view>
                            </view>
                        </view>
                        <view class="previewText">
                            <view class="previewDate">{{ slashDate(preview.date) }}</view>
                            <view class="previewCapBox">
                                <view class="previewCap">{{ preview.caption || '（本文なし）' }}</view>
                                <view class="capMore" v-if="capLong">…<text class="capMoreTxt">続きを見る</text></view>
                            </view>
                        </view>
                    </view>
                    <view class="sheetBtns">
                        <view class="sBtn outline" @click="closePreview">キャンセル</view>
                        <view class="sBtn primary" :class="reposting ? 'dis' : ''" @click="repost">{{ reposting ? '転載中…' : 'GBPに転載する' }}</view>
                    </view>
                    <view class="previewNote">GBPの「最新情報」として投稿されます。実際の表示はGoogleマップ・検索結果側の仕様により多少異なる場合があります。</view>
                </view>
            </view>
        </view>

        <!-- トースト -->
        <view class="toast" :class="toastShow ? 'show' : ''">{{ toastMsg }}</view>
    </view>
</template>

<script>
    import { gbpOverview, gbpFeatures, gbpInsPosts, gbpInsRepost } from '@/api/gbpContent.js'

    const PAGE_SIZE = 10

    export default {
        data() {
            return {
                loaded: false,
                screen: 'posts',
                seg: 'ig',
                ins: { connected: false, username: '', avatar: '' },
                avaErr: false,
                gbpConnected: false,
                insOn: 0,
                autoOn: false,
                savingAuto: false,
                // Instagram 投稿（サーバーから取得済みのもの）
                igAll: [],
                next: '',
                total: 0,
                navTop: 0,
                // サーバーが返す転載済み総数（未対応のサーバーでは null → 読込済み件数で代用）
                syncedTotal: null,
                loading: false,
                igError: '',
                igShown: 0,
                gbpShown: 0,
                historyLoading: false,
                // 転載プレビュー
                preview: null,
                sheetVisible: false,
                sheetShow: false,
                pvIndex: 0,
                reposting: false,
                toastMsg: '',
                toastShow: false,
                toastTimer: null,
            }
        },
        computed: {
            igExpired() {
                return /再連携/.test(this.igError)
            },
            igOk() {
                return this.ins.connected && !this.igExpired
            },
            canAuto() {
                return this.ins.connected && !this.igError && this.gbpConnected && this.insOn == 1
            },
            gbpAll() {
                return this.igAll.filter(it => it.status == 'synced')
            },
            igShownList() {
                return this.igAll.slice(0, this.igShown)
            },
            gbpShownList() {
                return this.gbpAll.slice(0, this.gbpShown)
            },
            historyList() {
                return this.igAll.filter(it => it.status == 'synced' || it.status == 'video')
            },
            curAllCount() {
                return this.seg == 'ig' ? this.igAll.length : this.gbpAll.length
            },
            curShown() {
                return this.seg == 'ig' ? this.igShownList.length : this.gbpShownList.length
            },
            curTotal() {
                if (this.seg == 'ig') return Math.max(this.total || 0, this.igAll.length)
                return Math.max(this.syncedTotal || 0, this.gbpAll.length)
            },
            curHasMore() {
                if (this.seg == 'ig') return this.igShown < this.igAll.length || !!this.next
                return this.gbpShown < this.gbpAll.length || !!this.next
            },
            pvImages() {
                if (!this.preview) return []
                let list = Array.isArray(this.preview.images) && this.preview.images.length ? this.preview.images : (this.preview.image ? [this.preview.image] : [])
                return list.map(i => (i && typeof i == 'object') ? (i.url || i.media_url || '') : i).filter(Boolean)
            },
            capLong() {
                let c = (this.preview && this.preview.caption) || ''
                return c.length > 44 || c.split('\n').length > 2
            },
        },
        onLoad() {
            // 共通見出し(u-navbar・固定表示)の下で切替タブを止めるための高さ
            this.navTop = (uni.getSystemInfoSync().statusBarHeight || 0) + 44
            this.init()
        },
        onUnload() {
            if (this.toastTimer) clearTimeout(this.toastTimer)
        },
        methods: {
            noop() {},
            init() {
                gbpOverview().then(res => {
                    this.loaded = true
                    if (res && res.code == 200) {
                        let d = res.data || {}
                        this.ins = d.instagram || { connected: false, username: '', avatar: '' }
                        this.gbpConnected = !!d.connected
                        this.insOn = d.ins_on
                        this.autoOn = d.ins_auto == 1
                        if (this.ins.connected) this.fetchPage(false)
                    } else if (res && res.code != 400) {
                        uni.showToast({ title: res.message || '読み込みに失敗しました', icon: 'none' })
                    }
                }).catch(() => { this.loaded = true })
            },
            // Instagram 投稿を1ページ取得（サーバー側カーソル）
            fetchPage(more) {
                if (this.loading) return Promise.resolve(false)
                this.loading = true
                return gbpInsPosts(more ? this.next : '').then(res => {
                    this.loading = false
                    if (res && res.code == 200) {
                        let d = res.data || {}
                        this.igError = ''
                        let items = d.items || []
                        this.igAll = more ? this.igAll.concat(items) : items
                        this.next = d.next || ''
                        this.total = d.total || 0
                        this.syncedTotal = d.synced_total != null ? Number(d.synced_total) : null
                        if (!more) {
                            this.igShown = Math.min(PAGE_SIZE, this.igAll.length)
                            this.gbpShown = Math.min(PAGE_SIZE, this.gbpAll.length)
                        }
                        return true
                    }
                    // Instagram 側のトークン失効など
                    this.igError = (res && res.message) || 'Instagramの投稿を取得できませんでした。'
                    return false
                }).catch(() => { this.loading = false; return false })
            },
            // 表示中のタブに、未表示分から最大10件を追加
            async loadMore() {
                if (this.loading) return
                if (this.seg == 'ig') {
                    if (this.igShown + PAGE_SIZE > this.igAll.length && this.next) await this.fetchPage(true)
                    this.igShown = Math.min(this.igShown + PAGE_SIZE, this.igAll.length)
                } else {
                    // GBP投稿は転載済みのみ：足りなければ次のページを取りに行く（最大5ページ）
                    let n = 0
                    while (this.gbpShown + PAGE_SIZE > this.gbpAll.length && this.next && n < 5) {
                        let ok = await this.fetchPage(true)
                        if (!ok) break
                        n++
                    }
                    this.gbpShown = Math.min(this.gbpShown + PAGE_SIZE, this.gbpAll.length)
                }
            },
            // 一覧に無い分を全部取得（連動履歴・GBP投稿の件数用。最大20ページ）
            async fetchAll() {
                let n = 0
                while (this.next && n < 20) {
                    let ok = await this.fetchPage(true)
                    if (!ok) break
                    n++
                }
            },
            switchSeg(s) {
                if (this.seg == s) return
                this.seg = s
                uni.pageScrollTo({ scrollTop: 0, duration: 0 })
                if (s == 'gbp' && this.ins.connected && !this.loading) {
                    // GBP投稿の全件数を出すため残りも取得
                    this.fetchAll().then(() => {
                        if (!this.gbpShown) this.gbpShown = Math.min(PAGE_SIZE, this.gbpAll.length)
                    })
                }
            },
            switchScreen(s) {
                if (this.screen == s) return
                this.screen = s
                uni.pageScrollTo({ scrollTop: 0, duration: 0 })
                if (s == 'history' && this.ins.connected && this.next) {
                    this.historyLoading = true
                    this.fetchAll().then(() => { this.historyLoading = false })
                }
            },
            toNoticeSetting() {
                uni.navigateTo({ url: '/pages/notice/setting/setting' })
            },
            saveAuto(val) {
                if (this.savingAuto) return
                this.savingAuto = true
                gbpFeatures({ ins_auto: val ? 1 : 0 }).then(res => {
                    this.savingAuto = false
                    if (!res || res.code != 200) {
                        this.autoOn = !val
                        if (!res || res.code != 400) uni.showToast({ title: (res && res.message) || '保存できませんでした', icon: 'none' })
                    }
                }).catch(() => { this.savingAuto = false; this.autoOn = !val })
            },
            openPreview(it) {
                if (it.status == 'synced' || it.status == 'video') return
                if (!this.gbpConnected) {
                    uni.showToast({ title: 'Googleビジネスプロフィールと連携されていません', icon: 'none' })
                    return
                }
                this.preview = it
                this.pvIndex = 0
                this.sheetVisible = true
                setTimeout(() => { this.sheetShow = true }, 30)
            },
            closePreview() {
                if (this.reposting) return
                this.sheetShow = false
                setTimeout(() => {
                    this.sheetVisible = false
                    this.preview = null
                }, 320)
            },
            repost() {
                if (this.reposting || !this.preview) return
                this.reposting = true
                let it = this.preview
                gbpInsRepost(it.id).then(res => {
                    this.reposting = false
                    if (res && res.code == 200) {
                        // 成功レスポンスを受け取ってから「転載済み」に切り替える
                        this.$set(it, 'status', 'synced')
                        if (this.syncedTotal != null) this.syncedTotal++
                        this.closePreview()
                        this.showToast('転載しました')
                    } else if (!res || res.code != 400) {
                        uni.showToast({ title: (res && res.message) || '転載できませんでした', icon: 'none' })
                    }
                }).catch(() => {
                    this.reposting = false
                    uni.showToast({ title: '転載できませんでした', icon: 'none' })
                })
            },
            showToast(msg) {
                this.toastMsg = msg
                this.toastShow = true
                if (this.toastTimer) clearTimeout(this.toastTimer)
                this.toastTimer = setTimeout(() => { this.toastShow = false }, 1800)
            },
            // 2026.07.20 形式
            dotDate(s) {
                if (!s) return ''
                return String(s).substr(0, 10).replace(/[-\/]/g, '.')
            },
            // 2026/07/06 形式
            slashDate(s) {
                if (!s) return ''
                return String(s).substr(0, 10).replace(/[-.]/g, '/')
            },
        }
    }
</script>

<style lang="scss" scoped>
    $text: #1d1d1f;
    $sub: #707070;
    $blue: #1a73e8;
    $border: #d2d2d7;
    $green: #008a32;
    $thumb: #d4d4de;
    $note: #103c6e;

    .page { background: #fff; min-height: 100vh; border-top: 2rpx solid $border; }

    .screenTabs { display: flex; border-bottom: 2rpx solid $border; }
    .screenTab {
        flex: 1; text-align: center; padding: 22rpx 0; font-size: 28rpx; color: #aaa;
        border-bottom: 4rpx solid transparent;
        &.act { color: $text; font-weight: 600; border-bottom-color: $blue; }
    }

    .igRow { display: flex; align-items: center; padding: 40rpx; border-bottom: 2rpx solid $border; }
    .igAvatar {
        width: 62rpx; height: 62rpx; border-radius: 50%; flex-shrink: 0; margin-right: 24rpx;
        &.blank { background: linear-gradient(135deg, #8aa88f, #5c7d8a); }
    }
    .igTxt { flex: 1; min-width: 0; }
    .igName { font-size: 28rpx; color: $text; }
    .igStatus { font-size: 28rpx; color: $blue; margin-top: 2rpx; &.off { color: $sub; } }
    .igLink { font-size: 26rpx; color: $blue; font-weight: 600; flex-shrink: 0; }

    .toggleRow {
        display: flex; align-items: center; justify-content: space-between;
        padding: 40rpx; border-bottom: 2rpx solid $border;
        &.off .toggleTxt { opacity: 0.45; }
    }
    .toggleTxt { flex: 1; margin-right: 20rpx; }
    .toggleLabel { font-size: 32rpx; color: $text; }
    .toggleSub { font-size: 24rpx; color: $sub; margin-top: 12rpx; }

    .noteBox {
        margin: 40rpx; padding: 24rpx 28rpx; background: rgba(26, 115, 232, 0.1);
        border: 2rpx solid $note; border-radius: 20rpx; font-size: 24rpx; color: $note; line-height: 1.7;
    }
    .warnBox {
        margin: -16rpx 40rpx 40rpx; padding: 20rpx 24rpx; background: #fff8e1; border-radius: 12rpx;
        font-size: 24rpx; color: #8a6d00; line-height: 1.6;
    }

    .segWrap {
        position: -webkit-sticky; position: sticky; top: var(--window-top); z-index: 5;
        background: #fff; padding-top: 12rpx;
    }
    .seg { display: flex; background: #e5e6e6; border-radius: 20rpx; padding: 6rpx; margin: 0 40rpx 32rpx; }
    .segBtn {
        flex: 1; text-align: center; padding: 18rpx 0; font-size: 30rpx; font-weight: 600; color: #aaa; border-radius: 20rpx;
        &.act { background: #fff; color: #000; }
    }

    .flatPanel {
        margin: 0 40rpx 40rpx; padding: 60rpx 30rpx; background: #f5f5f7; border-radius: 20rpx;
        text-align: center; font-size: 26rpx; color: $sub; line-height: 1.7;
    }
    .flatLink { color: $blue; font-weight: 600; margin-top: 16rpx; }

    .list { padding: 0 40rpx 8rpx; }
    .listMsg { text-align: center; font-size: 24rpx; color: #aaa; padding: 40rpx 0; }

    .postRow {
        display: flex; align-items: center; background: #fff; border: 2rpx solid $border; border-radius: 8rpx;
        padding: 20rpx; margin-bottom: 20rpx;
        &.done { background: rgba(30, 126, 66, 0.1); }
        &.blocked { opacity: 0.55; }
    }
    .stIcon {
        width: 44rpx; height: 44rpx; border-radius: 50%; flex-shrink: 0; margin-right: 20rpx; box-sizing: border-box;
        display: flex; align-items: center; justify-content: center;
        &.done { background: #1e7e42; }
        &.blocked { background: #aaaaaa; }
        &.pending { background: #fff; border: 3rpx solid #cfd2d8; }
    }
    .postThumb {
        width: 112rpx; height: 88rpx; border-radius: 8rpx; flex-shrink: 0; background: $thumb; margin-right: 20rpx;
        position: relative; overflow: hidden;
        image { width: 100%; height: 100%; }
        .play { position: absolute; left: 0; top: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.15); }
    }
    .postMain { flex: 1; min-width: 0; }
    .postCap {
        font-size: 24rpx; color: #000; line-height: 1.5; margin-bottom: 14rpx;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; word-break: break-all;
    }
    .postMeta { display: flex; align-items: center; justify-content: space-between; }
    .postDate { font-size: 24rpx; color: $sub; }
    .pill {
        display: inline-block; font-size: 24rpx; padding: 6rpx 16rpx; border-radius: 8rpx; white-space: nowrap;
        &.done { background: rgba(30, 126, 66, 0.2); color: $green; }
        &.blocked { background: rgba(170, 170, 170, 0.2); color: #aaa; }
        &.pending { background: rgba(26, 115, 232, 0.2); color: $blue; }
    }

    .loadMoreWrap { padding: 8rpx 40rpx 100rpx; text-align: center; }
    .btnLoadMore {
        padding: 22rpx 0; border-radius: 16rpx; border: 2rpx solid $border; background: #fff;
        color: $text; font-size: 28rpx; font-weight: 600;
        &.dis { opacity: 0.5; }
    }
    .loadMoreCount { font-size: 22rpx; color: $sub; margin-top: 16rpx; }

    .historyWrap { padding-top: 28rpx; }
    .historyItem {
        display: flex; align-items: center; background: #fff; border: 2rpx solid $border; border-radius: 8rpx;
        padding: 20rpx; margin: 0 40rpx 20rpx;
    }
    .hArrow { color: $sub; font-size: 32rpx; margin: 0 14rpx; }
    .hThumb {
        width: 88rpx; height: 88rpx; border-radius: 8rpx; background: $thumb; flex-shrink: 0; overflow: hidden;
        image { width: 100%; height: 100%; }
        &.gbp {
            background: #fff; border: 2rpx solid $border; box-sizing: border-box; margin-right: 20rpx;
            display: flex; align-items: center; justify-content: center; color: #4285F4; font-weight: 800; font-size: 28rpx;
        }
        &.gbp.ng { background: #f1f1f3; color: #aaa; }
    }
    .hBody { flex: 1; min-width: 0; }
    .hTitle { font-size: 24rpx; font-weight: 700; color: $text; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .hTime {
        font-size: 21rpx; color: $sub; margin-top: 8rpx; display: flex; align-items: center;
        .pill { margin-left: 16rpx; font-size: 22rpx; }
    }
    .captionNote { font-size: 22rpx; color: #8a8f98; text-align: center; padding: 10rpx 40rpx; line-height: 1.6; }

    /* 転載プレビュー */
    .sheetOverlay {
        position: fixed; left: 0; right: 0; top: 0; bottom: 0; z-index: 990;
        background: rgba(29, 29, 31, 0.2); opacity: 0; transition: opacity 0.3s ease;
        &.show { opacity: 1; }
        &.show .previewSheet { transform: translateY(0); }
    }
    .previewSheet {
        position: absolute; left: 0; right: 0; bottom: 0; top: 12.2%;
        background: #f5f5f7; border-radius: 40rpx 40rpx 0 0; overflow: hidden;
        display: flex; flex-direction: column;
        transform: translateY(100%); transition: transform 0.32s cubic-bezier(0.32, 0.72, 0, 1);
    }
    .sheetHeader {
        height: 114rpx; display: flex; align-items: center; justify-content: center; position: relative;
        background: #fff; border-bottom: 2rpx solid $border; flex-shrink: 0;
    }
    .sheetTitle { font-size: 32rpx; font-weight: 600; color: #000; }
    .closeX { position: absolute; right: 30rpx; top: 50%; transform: translateY(-50%); padding: 10rpx; }
    .previewBody { flex: 1; padding: 40rpx; padding-bottom: calc(40rpx + env(safe-area-inset-bottom)); }
    .previewCard { border-radius: 40rpx; overflow: hidden; border: 2rpx solid $border; background: #fff; }
    .previewMedia { position: relative; width: 100%; padding-top: 100%; background: $thumb; }
    .previewSwiper { position: absolute; left: 0; top: 0; width: 100%; height: 100%; }
    .previewImg { width: 100%; height: 100%; }
    .progressTrack {
        position: absolute; left: 50%; bottom: 32rpx; transform: translateX(-50%);
        width: 120rpx; height: 16rpx; border-radius: 20rpx; background: rgba(255, 255, 255, 0.5); overflow: hidden;
    }
    .progressFill { position: absolute; top: 0; height: 100%; background: $blue; border-radius: 20rpx; transition: left 0.2s; }
    .previewText { background: #fff; padding: 32rpx; }
    .previewDate { font-size: 24rpx; color: $sub; margin-bottom: 10rpx; }
    .previewCapBox { position: relative; }
    .previewCap {
        font-size: 28rpx; line-height: 1.6; color: $text; word-break: break-all;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .capMore {
        position: absolute; right: 0; bottom: 0; background: #fff; padding-left: 8rpx;
        font-size: 28rpx; line-height: 1.6; color: $text;
    }
    .capMoreTxt { color: $sub; }
    .sheetBtns { display: flex; margin-top: 52rpx; }
    .sBtn {
        flex: 1; text-align: center; padding: 26rpx 0; border-radius: 40rpx; font-size: 30rpx; font-weight: 600;
        &.outline { background: #fff; color: $text; border: 2rpx solid $border; margin-right: 20rpx; }
        &.primary { background: $blue; color: #fff; }
        &.dis { opacity: 0.6; }
    }
    .previewNote { font-size: 28rpx; color: $sub; line-height: 1.6; margin-top: 36rpx; }

    .toast {
        position: fixed; left: 50%; bottom: 180rpx; transform: translateX(-50%);
        background: #3c4a42; color: #fff; padding: 20rpx 40rpx; border-radius: 40rpx; font-size: 25rpx;
        opacity: 0; pointer-events: none; transition: all 0.25s; z-index: 999; white-space: nowrap;
        &.show { opacity: 1; transform: translateX(-50%) translateY(-12rpx); }
    }
</style>
