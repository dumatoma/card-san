<template>
    <!-- [カスタムエイド 2026-10-22] クチコミ管理（アプリ版 UI/UX 2026-08-17 準拠で作り直し） -->
    <view class="page">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="Googleクチコミ管理" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- 権限なし（副管理者・プラン） -->
        <view class="denied" v-if="denied">{{ denied }}</view>

        <template v-else>
            <!-- ヘッダー（統計） -->
            <view class="stats">
                <view class="stat">
                    <view class="statNum">{{ stats.avg_rating == null || stats.avg_rating === '' ? '-' : Number(stats.avg_rating).toFixed(1) }}</view>
                    <view class="statLbl">平均評価</view>
                </view>
                <view class="stat mid" :class="filter == 'all' ? 'on' : ''" @click="setFilter('all')">
                    <view class="statNum">{{ stats.total || 0 }}</view>
                    <view class="statLbl">総件数</view>
                </view>
                <view class="stat" :class="filter == 'unreplied' ? 'on' : ''" @click="setFilter('unreplied')">
                    <view class="statNum red">{{ stats.unreplied || 0 }}</view>
                    <view class="statLbl">未返信</view>
                </view>
            </view>
            <view class="unreadRow">
                <view class="dot"></view>
                <text>未読 {{ stats.unread || 0 }}件</text>
                <text class="lastSync" v-if="lastSync">最終更新 {{ lastSync }}</text>
            </view>
            <view class="warn" v-if="warning">{{ warning }}</view>

            <!-- GBP 未連携 / クチコミ管理 OFF -->
            <view class="guide" v-if="loaded && (!connected || !reviewOn)">
                <view class="gTitle">{{ connected ? 'クチコミ管理をONにしましょう' : 'Googleビジネスプロフィールと連携しよう' }}</view>
                <view class="gSub" v-if="!connected">[各種設定]＞[Googleビジネス連携]から連携できます。連携すると、お客様からのクチコミの確認・返信がこの画面でできるようになります。</view>
                <view class="gSub" v-else>[各種設定]＞[Googleビジネス連携]の「クチコミ管理」をONにすると、お客様からのクチコミの確認・返信がこの画面でできるようになります。</view>
                <view class="gBtn" @click="toGBPSetting">Googleビジネス連携画面へ</view>
            </view>

            <!-- 0件 -->
            <view class="empty" v-else-if="loaded && !list.length">
                <template v-if="filter == 'unreplied'">未返信のクチコミはありません</template>
                <template v-else>まだクチコミがありません。<br />Googleでクチコミが投稿されると、ここに表示されます。</template>
            </view>

            <!-- 一覧 -->
            <view v-else-if="list.length">
                <view class="card" v-for="r in list" :key="r.id" @click="markRead(r)">
                    <view class="unreadDot" v-if="!r.is_read"></view>
                    <view class="head">
                        <image v-if="r.reviewer_photo && !avaErr[r.id]" :src="r.reviewer_photo" class="ava" mode="aspectFill" @error="onAvaErr(r.id)"></image>
                        <view v-else class="ava ini">{{ (r.reviewer_name || '?').substr(0, 1) }}</view>
                        <view class="who">
                            <view class="name">{{ r.reviewer_name || 'Googleユーザー' }}</view>
                            <view class="starRow">
                                <text v-for="n in 5" :key="n" class="star" :class="n <= r.star ? 'on' : ''">★</text>
                                <text class="date">{{ slashDate(r.date) }}</text>
                            </view>
                        </view>
                    </view>
                    <view class="cmt" v-if="r.comment">{{ r.comment }}</view>

                    <!-- クチコミ添付写真（reviewMediaItems） -->
                    <view class="photos" v-if="photoList(r).length">
                        <image v-for="(src, pi) in photoList(r)" :key="pi" :src="src" class="photo" mode="aspectFill" @click.stop="previewPhotos(r, pi)"></image>
                    </view>

                    <!-- ③ 返信入力中 -->
                    <view class="composer" v-if="editing == r.id" @click.stop="noop">
                        <textarea class="ta" v-model="draft" :maxlength="4096" placeholder="返信を入力..." :auto-height="true" :focus="focusTa" :show-confirm-bar="false"></textarea>
                        <view class="composerFoot">
                            <text class="len">{{ draft.length }} / 4096</text>
                            <view class="btns">
                                <view class="btn" @click.stop="cancelEdit">キャンセル</view>
                                <view class="btn send" :class="!draft.trim() || sending ? 'dis' : ''" @click.stop="send(r)">{{ sending ? '送信中...' : '送信' }}</view>
                            </view>
                        </view>
                    </view>
                    <!-- ④ 返信済み -->
                    <template v-else-if="r.reply">
                        <view class="reply">
                            <view class="replyHead">
                                <text class="replyLbl">オーナーからの返信</text>
                                <text class="replyDate">{{ slashDate(r.reply_date) }}</text>
                            </view>
                            <view class="replyTxt">{{ r.reply }}</view>
                        </view>
                        <view class="act"><view class="btn" @click.stop="edit(r)">編集</view></view>
                    </template>
                    <!-- ①② 未返信 -->
                    <view class="act" v-else>
                        <view class="btn reply" @click.stop="edit(r)"><u-icon name="arrow-left" size="12" color="#5F5E5A"></u-icon><text style="margin-left: 8rpx;">返信する</text></view>
                    </view>
                </view>

                <view class="more">
                    <view class="moreBtn" v-if="list.length < count" :class="loading ? 'dis' : ''" @click="load(true)">{{ loading ? '読み込み中…' : 'もっと見る' }}</view>
                    <view class="moreCount">{{ list.length }} / {{ count }} 件を表示中</view>
                </view>
            </view>

            <view class="empty" v-else-if="!loaded">読み込み中…</view>
        </template>
    </view>
</template>

<script>
    import { gbpReviewList, gbpReviewRead, gbpReviewReply } from '@/api/gbpContent.js'

    export default {
        data() {
            return {
                loaded: false,
                loading: false,
                denied: '',
                warning: '',
                connected: false,
                reviewOn: 0,
                filter: 'all',
                stats: { avg_rating: null, total: 0, unreplied: 0, unread: 0 },
                list: [],
                count: 0,
                avaErr: {},
                editing: '',
                draft: '',
                focusTa: false,
                sending: false,
                lastSync: '',
                reqSeq: 0,
            }
        },
        onLoad() {
            this.load(false)
        },
        onPullDownRefresh() {
            this.cancelEdit()
            this.load(false, true)
        },
        methods: {
            noop() {},
            onAvaErr(id) {
                this.$set(this.avaErr, id, 1)
            },
            setFilter(f) {
                if (this.filter == f) return
                this.filter = f
                this.cancelEdit()
                // 読み込み中でも新しいフィルタで取り直す（古い応答は reqSeq で破棄）
                this.load(false, true)
            },
            load(more, force) {
                if (this.loading && !force) return
                this.loading = true
                let seq = ++this.reqSeq
                gbpReviewList({ filter: this.filter, offset: more ? this.list.length : 0, limit: 10 }).then(res => {
                    // フィルタ切替・プル更新で後から投げた要求がある場合、古い応答は捨てる
                    if (seq != this.reqSeq) return
                    this.loading = false
                    this.loaded = true
                    uni.stopPullDownRefresh()
                    if (!res || res.code != 200) {
                        if (res && res.code == 403) this.denied = res.message || 'この機能を利用する権限がありません'
                        else if (!res || res.code != 400) uni.showToast({ title: (res && res.message) || '読み込みに失敗しました', icon: 'none' })
                        return
                    }
                    let d = res.data || {}
                    this.denied = ''
                    this.connected = !!d.connected
                    this.reviewOn = d.review_on
                    this.warning = d.warning || ''
                    if (d.stats) {
                        this.stats = d.stats
                        // 首页の未読バッジ・アプリ角標へ最新の未読数を反映
                        uni.$emit('gbpReviewUnread', d.stats.unread || 0)
                    }
                    this.count = d.count || 0
                    this.lastSync = d.last_sync_at ? String(d.last_sync_at).substr(0, 16).replace(/-/g, '/') : ''
                    let rows = d.list || []
                    this.list = more ? this.list.concat(rows) : rows
                }).catch(err => {
                    if (seq != this.reqSeq) return
                    this.loading = false
                    this.loaded = true
                    uni.stopPullDownRefresh()
                    // HTTP 403（権限・プラン）は request.js で reject される。未連携ガイドではなく権限メッセージを出す
                    let r = err && err.response
                    if (r && r.status == 403) this.denied = (r.data && r.data.message) || 'この機能を利用する権限がありません'
                })
            },
            // カード本体／「返信する」のタップで既読にする（表示しただけでは既読にしない）
            markRead(r) {
                if (r.is_read) return
                this.$set(r, 'is_read', 1)
                this.stats.unread = Math.max(0, (this.stats.unread || 0) - 1)
                uni.$emit('gbpReviewUnread', this.stats.unread)
                gbpReviewRead([r.id]).catch(() => {})
            },
            edit(r) {
                this.markRead(r)
                if (this.sending) return
                this.editing = r.id
                this.draft = r.reply || ''
                this.focusTa = false
                this.$nextTick(() => { this.focusTa = true })
            },
            cancelEdit() {
                if (this.sending) return
                this.editing = ''
                this.draft = ''
                this.focusTa = false
            },
            send(r) {
                let text = this.draft.trim()
                if (this.sending || !text) return
                this.sending = true
                let wasUnreplied = !r.reply
                gbpReviewReply(r.id, this.draft).then(res => {
                    this.sending = false
                    if (res && res.code == 200) {
                        let d = res.data || {}
                        this.$set(r, 'reply', d.reply || this.draft)
                        this.$set(r, 'reply_date', d.reply_date || this.today())
                        if (wasUnreplied) this.stats.unreplied = Math.max(0, (this.stats.unreplied || 0) - 1)
                        this.cancelEdit()
                        uni.showToast({ title: res.message || '返信しました', icon: 'none' })
                    } else if (!res || res.code != 400) {
                        // 入力内容は残したまま
                        uni.showToast({ title: (res && res.message) || '返信できませんでした', icon: 'none' })
                    }
                }).catch(() => {
                    this.sending = false
                    uni.showToast({ title: '返信できませんでした。通信状況をご確認ください', icon: 'none' })
                })
            },
            // photos: URL の配列（{url} 形式も可）
            photoList(r) {
                let p = r.photos || []
                if (!Array.isArray(p)) return []
                return p.map(i => (i && typeof i == 'object') ? (i.url || i.googleUrl || i.thumbnail_url || '') : i).filter(Boolean)
            },
            previewPhotos(r, idx) {
                let urls = this.photoList(r)
                uni.previewImage({ urls, current: urls[idx] })
            },
            toGBPSetting() {
                uni.navigateTo({ url: '/pages/settings/gbpSetting/gbpSetting' })
            },
            slashDate(s) {
                if (!s) return ''
                return String(s).substr(0, 10).replace(/[-.]/g, '/')
            },
            today() {
                let d = new Date()
                return d.getFullYear() + '/' + ('0' + (d.getMonth() + 1)).slice(-2) + '/' + ('0' + d.getDate()).slice(-2)
            },
        }
    }
</script>

<style lang="scss" scoped>
    $text: #1A1A18;
    $text2: #5F5E5A;
    $muted: #888780;
    $danger: #A32D2D;
    $accent: #185FA5;
    $fill: #378ADD;
    $bgAccent: #E6F1FB;
    $border: rgba(0, 0, 0, 0.10);
    $borderStrong: rgba(0, 0, 0, 0.18);

    .page { background: #fff; min-height: 100vh; }
    .denied { margin: 60rpx 40rpx; padding: 40rpx; background: #F5F5F7; border-radius: 16rpx; text-align: center; font-size: 26rpx; color: $text2; line-height: 1.7; }

    .stats { display: flex; padding: 32rpx 40rpx 16rpx; border-bottom: 1rpx solid $border; }
    .stat {
        flex: 1; text-align: center; padding: 8rpx 0; border-radius: 8rpx;
        &.mid { border-left: 1rpx solid $border; border-right: 1rpx solid $border; border-radius: 0; }
        &.on { background: $bgAccent; }
    }
    .statNum { font-size: 44rpx; font-weight: 500; color: $text; &.red { color: $danger; } }
    .statLbl { font-size: 22rpx; color: $muted; margin-top: 8rpx; }

    .unreadRow {
        display: flex; align-items: center; padding: 12rpx 40rpx 20rpx; border-bottom: 1rpx solid $border;
        font-size: 22rpx; color: $accent;
        .dot { width: 16rpx; height: 16rpx; border-radius: 50%; background: $fill; margin-right: 12rpx; }
        .lastSync { margin-left: auto; color: #8e8e93; }
    }
    .warn { margin: 20rpx 40rpx 0; padding: 16rpx 24rpx; background: #fff8e1; border-radius: 12rpx; font-size: 24rpx; color: #8a6d00; line-height: 1.6; }

    .guide { margin: 40rpx; background: #F5F5F7; border-radius: 20rpx; padding: 60rpx 40rpx; text-align: center; }
    .gTitle { font-size: 30rpx; font-weight: bold; color: $text; }
    .gSub { font-size: 24rpx; color: $text2; line-height: 1.7; margin-top: 20rpx; text-align: left; }
    .gBtn { display: inline-block; background: #1A73E8; color: #fff; border-radius: 12rpx; padding: 20rpx 60rpx; margin-top: 36rpx; font-size: 28rpx; }

    .empty { padding: 80rpx 40rpx; text-align: center; font-size: 26rpx; color: $muted; line-height: 1.8; }

    .card { padding: 32rpx 40rpx; border-bottom: 1rpx solid $border; position: relative; }
    .unreadDot { position: absolute; top: 28rpx; right: 40rpx; width: 18rpx; height: 18rpx; border-radius: 50%; background: $fill; }
    .head { display: flex; align-items: center; margin-bottom: 12rpx; padding-right: 40rpx; }
    .ava {
        width: 64rpx; height: 64rpx; border-radius: 50%; flex-shrink: 0; margin-right: 16rpx;
        &.ini { background: $bgAccent; color: $accent; font-size: 24rpx; display: flex; align-items: center; justify-content: center; }
    }
    .who { flex: 1; min-width: 0; }
    .name { font-size: 26rpx; font-weight: 500; color: $text; }
    .starRow { display: flex; align-items: center; }
    .star { color: #D2D2D7; font-size: 24rpx; &.on { color: #FFC800; } }
    .date { font-size: 22rpx; color: $muted; margin-left: 12rpx; }
    .cmt { font-size: 26rpx; color: $text; line-height: 1.7; margin-bottom: 20rpx; white-space: pre-wrap; word-break: break-all; }

    .photos { display: flex; flex-wrap: wrap; margin-bottom: 16rpx; }
    .photo { width: 140rpx; height: 140rpx; border-radius: 12rpx; margin: 0 12rpx 12rpx 0; background: #F0EFE9; }

    .act { display: flex; justify-content: flex-end; }
    .btn {
        display: flex; align-items: center; font-size: 22rpx; color: $text; padding: 10rpx 24rpx;
        border: 1rpx solid $borderStrong; border-radius: 16rpx;
        &.reply { font-size: 24rpx; padding: 10rpx 28rpx; }
        &.send { background: $fill; color: #fff; border-color: $fill; margin-left: 12rpx; }
        &.dis { opacity: 0.4; }
    }

    .composer { background: #F5F4F0; border-radius: 16rpx; padding: 16rpx; }
    .ta { width: 100%; min-height: 120rpx; font-size: 26rpx; background: transparent; line-height: 1.6; }
    .composerFoot { display: flex; align-items: center; justify-content: space-between; margin-top: 12rpx; }
    .len { font-size: 20rpx; color: $muted; }
    .btns { display: flex; }

    .reply { background: #F5F5F7; border-left: 6rpx solid #C7C7CC; border-radius: 0 16rpx 16rpx 0; padding: 20rpx 24rpx; margin-bottom: 16rpx; }
    .replyHead { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10rpx; }
    .replyLbl { font-size: 22rpx; color: $text2; font-weight: 500; }
    .replyDate { font-size: 22rpx; color: $muted; }
    .replyTxt { font-size: 24rpx; color: $text2; line-height: 1.7; white-space: pre-wrap; word-break: break-all; }

    .more { padding: 32rpx 40rpx 100rpx; text-align: center; }
    .moreBtn {
        padding: 22rpx 0; border: 1rpx solid #d2d2d7; border-radius: 16rpx; font-size: 28rpx; font-weight: 600; color: #1d1d1f;
        &.dis { opacity: 0.5; }
    }
    .moreCount { font-size: 22rpx; color: #707070; margin-top: 16rpx; }
</style>
