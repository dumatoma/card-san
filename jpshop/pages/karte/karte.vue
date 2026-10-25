<template>
    <!-- [カスタムエイド 2026-10-23] カルテの画面(A2・A3)。上にカルテ、下に写真。下の「＋ 追加」、右上「…」 -->
    <view class="kp">
        <u-navbar :placeholder="true" :safeAreaInsetTop="true" :border="true" @leftClick="back">
            <view slot="center" class="kp-title">
                <text class="kp-t">カルテ</text>
                <text class="kp-s" v-if="name">{{ name }} さん</text>
            </view>
            <view slot="right" class="kp-dots" hover-class="kp-hover" @click="menuShow = true" v-if="!lock"><view class="kp-dot"></view><view class="kp-dot"></view><view class="kp-dot"></view></view>
        </u-navbar>

        <view class="kp-lock" v-if="lock"><image class="k-lock" src="/static/karte/lock.svg"></image>カルテを見る権限がありません。{{ '\n' }}オーナーに「カルテ」の権限を付けてもらってください。</view>

        <view class="kp-body" v-else-if="loaded">
            <!-- PDF の取り込み中 -->
            <view class="kp-prog" v-for="k in converting" :key="'j' + k.id">
                <view class="kp-prog-t">PDF を取り込んでいます</view>
                <view class="kp-prog-s">{{ k.job.name }}</view>
                <view class="kp-bar"><view class="kp-bar-in" :style="{ width: progress(k.job) + '%' }"></view></view>
                <view class="kp-prog-s">ページを画像に変換中… {{ k.job.done }} / {{ k.job.total || '?' }}</view>
            </view>
            <view class="kp-fail" v-for="k in failed" :key="'f' + k.id">{{ k.job.name }}:{{ k.job.message }}</view>

            <!-- まだ何もないとき -->
            <block v-if="!karte.length && !photos.length && !converting.length">
                <view class="kp-card kp-empty">
                    <view class="kp-empty-i"><image class="k-ic" src="/static/karte/clipboard.svg"></image></view>
                    <view class="kp-empty-t">まだカルテがありません</view>
                    <view class="kp-empty-s">紙のカルテを撮影して読み込むと、{{ '\n' }}そこに文字・線・メモを書き足せます。</view>
                </view>
                <view class="kp-ob" hover-class="kp-hover" @click="pickPdf">
                    <view class="kp-ob-i"><image class="k-ic" src="/static/karte/pdf.svg"></image></view>
                    <view class="kp-ob-tt">PDFのカルテを読み込む<text class="kp-ob-s">スマホに保存してある PDF</text></view>
                </view>
                <view class="kp-ob" hover-class="kp-hover" @click="camera(true)">
                    <view class="kp-ob-i"><image class="k-ic" src="/static/karte/camera.svg"></image></view>
                    <view class="kp-ob-tt">紙のカルテを撮影する<text class="kp-ob-s">撮ったあとに大きく表示して確かめられます</text></view>
                </view>
                <view class="kp-ob" hover-class="kp-hover" @click="openTpl" v-if="tplCount">
                    <view class="kp-ob-i"><image class="k-ic" src="/static/karte/blank.svg"></image></view>
                    <view class="kp-ob-tt">テンプレートから始める<text class="kp-ob-s">登録したテンプレート({{ tplCount }}種類)から</text></view>
                </view>
                <view class="kp-ob" hover-class="kp-hover" @click="album">
                    <view class="kp-ob-i"><image class="k-ic" src="/static/karte/photo.svg"></image></view>
                    <view class="kp-ob-tt">写真を追加する<text class="kp-ob-s">施術写真など</text></view>
                </view>
            </block>

            <block v-else>
                <view class="kp-h" v-if="karte.length">カルテ</view>
                <view class="kp-card" v-for="k in karte" :key="k.id">
                    <view class="kp-k" hover-class="kp-hover" @click="open(k.id)">
                        <view class="kp-cover">
                            <image v-if="k.cover" :src="k.cover.url" mode="aspectFit"></image>
                        </view>
                        <view class="kp-kt">
                            <view class="kp-kname">{{ k.title }}</view>
                            <view class="kp-kmeta"><text class="kp-tag" :class="{ pic: k.source != 1 }">{{ k.source == 1 ? 'PDF' : '写真' }}</text>{{ k.pages }}ページ</view>
                            <view class="kp-kwho" v-if="k.updated_by">最後に書いた人{{ '\n' }}{{ k.updated_by }}・{{ time(k.updated_at) }}</view>
                            <view class="kp-open" v-if="k.pages">開いて書き足す ›</view>
                        </view>
                    </view>
                </view>

                <view class="kp-h" v-if="photos.length">写真<text class="kp-h-n">{{ photos.length }}枚</text></view>
                <view class="kp-card kp-grid-card" v-if="photos.length">
                    <view class="kp-grid">
                        <view class="kp-pic" v-for="p in photos" :key="p.id" hover-class="kp-hover" @click="open(p.id)">
                            <image :src="p.page.url" mode="aspectFill"></image>
                            <text class="kp-pic-d">{{ date(p.created_at) }}</text>
                            <text class="kp-pic-m" v-if="p.marked">✎</text>
                        </view>
                    </view>
                </view>
                <view class="kp-space"></view>
            </block>
        </view>

        <view class="kp-fab" hover-class="kp-hover" @click="addShow = true" v-if="loaded && !lock && (karte.length || photos.length || converting.length)">＋ 追加</view>

        <!-- 追加(A4) -->
        <cs-sheet :show="addShow" title="追加" @close="addShow = false">
            <view class="kp-op" hover-class="kp-hover" @click="pickPdf">
                <view class="kp-op-i"><image class="k-ic" src="/static/karte/pdf.svg"></image></view>
                <view class="kp-op-tt">PDFのカルテを読み込む<text class="kp-op-s">スマホに保存してある PDF(20ページ・20MBまで)</text></view>
            </view>
            <view class="kp-op" hover-class="kp-hover" @click="camera(false)">
                <view class="kp-op-i"><image class="k-ic" src="/static/karte/camera.svg"></image></view>
                <view class="kp-op-tt">カメラで撮影<text class="kp-op-s">撮ったあとに「カルテにする/写真として残す」</text></view>
            </view>
            <view class="kp-op" hover-class="kp-hover" @click="album">
                <view class="kp-op-i"><image class="k-ic" src="/static/karte/photo.svg"></image></view>
                <view class="kp-op-tt">写真から選ぶ<text class="kp-op-s">施術写真など(一度に9枚まで)</text></view>
            </view>
            <view class="kp-op" hover-class="kp-hover" @click="openTpl" v-if="tplCount">
                <view class="kp-op-i"><image class="k-ic" src="/static/karte/blank.svg"></image></view>
                <view class="kp-op-tt">テンプレートを足す<text class="kp-op-s">登録したテンプレートから、カルテの後ろに1ページ</text></view>
            </view>
        </cs-sheet>

        <!-- テンプレートを選ぶ -->
        <cs-sheet :show="tplShow" :title="karte.length ? 'テンプレートを足す' : 'テンプレートから始める'" @close="tplShow = false">
            <view class="kp-tpl-note">{{ karte.length ? '「' + karte[0].title + '」の後ろに足します' : '選んだテンプレートで、新しいカルテを作ります' }}</view>
            <view class="kp-tpls">
                <view class="kp-tc" :class="{ on: tplSel == t.id }" v-for="t in tpls" :key="t.id" @click="tplSel = t.id">
                    <image class="kp-tc-img" :src="t.url" mode="aspectFit"></image>
                    <view class="kp-tc-n">{{ t.name }}</view>
                    <view class="kp-tc-s">{{ t.note }}</view>
                </view>
            </view>
            <view class="kp-btn" :class="{ off: !tplSel || busy }" hover-class="kp-hover" @click="addTpl">{{ busy ? '足しています…' : 'このページを足す' }}</view>
        </cs-sheet>

        <!-- メニュー(D1) -->
        <cs-sheet :show="menuShow" title="メニュー" @close="menuShow = false">
            <view class="kp-op" hover-class="kp-hover" @click="go('/pages/karte/setting')">
                <view class="kp-op-i"><image class="k-ic" src="/static/karte/blank.svg"></image></view>
                <view class="kp-op-tt">テンプレートの登録・編集<text class="kp-op-s">お店のテンプレート(3種類まで)</text></view>
            </view>
            <view class="kp-op" hover-class="kp-hover" @click="go('/pages/karte/trash?mid=' + mid + '&name=' + encodeURIComponent(name))">
                <view class="kp-op-i"><image class="k-ic" src="/static/karte/restore.svg"></image></view>
                <view class="kp-op-tt">削除したもの<text class="kp-op-s">30日以内なら元に戻せます</text></view>
            </view>
            <view class="kp-op" hover-class="kp-hover" @click="rename" v-if="karte.length">
                <view class="kp-op-i"><image class="k-ic" src="/static/karte/edit.svg"></image></view>
                <view class="kp-op-tt">カルテの名前を変える<text class="kp-op-s">今:{{ karte[0].title }}</text></view>
            </view>
            <view class="kp-op red" hover-class="kp-hover" @click="removeKarte" v-if="karte.length">
                <view class="kp-op-i"><image class="k-ic" src="/static/karte/trash.svg"></image></view>
                <view class="kp-op-tt">カルテを消す<text class="kp-op-s">30日は「削除したもの」から戻せます</text></view>
            </view>
        </cs-sheet>
    </view>
</template>

<script>
    import csSheet from '@/components/csSheet/csSheet.vue'
    import pickImage from '@/utils/pickImage.js'
    import { karteList, karteTpl, karteFromTpl, karteRename, karteDelete, karteUploadTo, shortTime, shortDate } from '@/api/karte.js'
    // #ifdef APP-PLUS
    // [カスタムエイド 2026-10-24] スマホに保存してある PDF を選ぶ部品(自社製 uni_modules/cardsan-file-picker)
    import { pickPdf as pickPdfFile } from '@/uni_modules/cardsan-file-picker'
    // #endif

    export default {
        components: { csSheet },
        data() {
            return {
                mid: 0,
                name: '',
                loaded: false,
                lock: false,
                karte: [],
                photos: [],
                tplCount: 0,
                tpls: [],
                tplSel: 0,
                addShow: false,
                tplShow: false,
                menuShow: false,
                busy: false,
                timer: null
            }
        },
        computed: {
            converting() {
                return this.karte.filter((k) => k.job && k.job.status == 'converting')
            },
            failed() {
                return this.karte.filter((k) => k.job && k.job.status == 'failed')
            }
        },
        onLoad(o) {
            this.mid = Number(o.mid) || 0
            this.name = o.name ? decodeURIComponent(o.name) : ''
        },
        onShow() {
            this.load()
        },
        onHide() {
            clearTimeout(this.timer)
        },
        onUnload() {
            clearTimeout(this.timer)
        },
        methods: {
            back() {
                uni.navigateBack()
            },
            time(t) {
                return shortTime(t)
            },
            date(t) {
                return shortDate(t)
            },
            progress(j) {
                return j.total ? Math.round((j.done / j.total) * 100) : 5
            },
            load() {
                clearTimeout(this.timer)
                karteList(this.mid).then((res) => {
                    if (!res) return
                    if (res.code == 403) {
                        this.lock = true
                    } else if (res.code == 200) {
                        const had = this.converting.length
                        this.karte = res.data.karte || []
                        this.photos = res.data.photos || []
                        this.tplCount = res.data.tpl_count || 0
                        if (had && !this.converting.length && !this.failed.length) uni.showToast({ title: 'PDF を取り込みました', icon: 'none' })
                        // 取り込み中は 3秒ごとに読み直す
                        if (this.converting.length) this.timer = setTimeout(() => this.load(), 3000)
                    }
                    this.loaded = true
                })
            },
            open(id) {
                uni.navigateTo({ url: '/pages/karte/view?id=' + id + '&name=' + encodeURIComponent(this.name) })
            },
            go(url) {
                this.menuShow = false
                uni.navigateTo({ url })
            },
            // PDF を選ぶ(アプリ:自社製の部品でファイルアプリ/ファイル選択画面を開く。ブラウザ版:ファイル選択)
            pickPdf() {
                this.addShow = false
                // #ifdef H5
                uni.chooseFile({
                    count: 1,
                    extension: ['.pdf'],
                    success: (r) => this.sendPdf(r.tempFilePaths[0])
                })
                // #endif
                // #ifdef APP-PLUS
                pickPdfFile({
                    success: (r) => {
                        // 上限 20MB(サーバーでも確かめる)
                        if (r.size > 20 * 1024 * 1024) return uni.showToast({ title: 'PDF が大きすぎます(20MB まで)', icon: 'none' })
                        this.sendPdf(r.path)
                    },
                    fail: (e) => {
                        if (e && e.cancel) return
                        uni.showToast({ title: 'PDF を読み込めませんでした' + (e && e.errMsg ? '(' + e.errMsg + ')' : ''), icon: 'none' })
                    }
                })
                // #endif
            },
            sendPdf(path) {
                uni.showLoading({ title: '送っています', mask: true })
                karteUploadTo(this.mid, path, { mode: 'pdf', karte_id: this.karte.length ? this.karte[0].id : '' }).then((r) => {
                    uni.hideLoading()
                    uni.showToast({ title: r.message || '', icon: 'none' })
                    this.load()
                })
            },
            // 撮影 → 大きく表示して「カルテにする/写真として残す」を選ぶ画面へ
            camera(asKarte) {
                this.addShow = false
                pickImage({
                    count: 1,
                    sourceType: ['camera'],
                    sizeType: ['original'],
                    success: (r) => {
                        uni.navigateTo({
                            url: '/pages/karte/capture?mid=' + this.mid + '&path=' + encodeURIComponent(r.tempFilePaths[0])
                                + '&karte=' + encodeURIComponent(this.karte.length ? this.karte[0].title : '') + '&pages=' + (this.karte.length ? this.karte[0].pages : 0)
                                + '&karte_id=' + (this.karte.length ? this.karte[0].id : '') + (asKarte ? '&as=karte' : '')
                        })
                    }
                })
            },
            // 写真から選ぶ → 写真の欄に
            album() {
                this.addShow = false
                pickImage({
                    count: 9,
                    sourceType: ['album'],
                    sizeType: ['original'],
                    success: async (r) => {
                        const list = r.tempFilePaths || []
                        let ok = 0
                        for (let i = 0; i < list.length; i++) {
                            uni.showLoading({ title: '保存しています ' + (i + 1) + ' / ' + list.length, mask: true })
                            const res = await karteUploadTo(this.mid, list[i], { mode: 'photo' })
                            if (res.code == 200) ok++
                            else uni.showToast({ title: res.message || '保存できませんでした', icon: 'none' })
                        }
                        uni.hideLoading()
                        if (ok) uni.showToast({ title: ok + '枚 保存しました', icon: 'none' })
                        this.load()
                    }
                })
            },
            openTpl() {
                this.addShow = false
                karteTpl().then((res) => {
                    if (res && res.code == 200) {
                        this.tpls = (res.data.list || []).filter((t) => t.status == 0 && t.url)
                        this.tplSel = this.tpls.length ? this.tpls[0].id : 0
                        if (!this.tpls.length) {
                            uni.showModal({
                                title: 'テンプレートがありません',
                                content: '各種設定の「カルテ」で、お店のテンプレートを登録してください。',
                                confirmText: '登録する',
                                success: (m) => { if (m.confirm) uni.navigateTo({ url: '/pages/karte/setting' }) }
                            })
                            return
                        }
                        this.tplShow = true
                    }
                })
            },
            addTpl() {
                if (!this.tplSel || this.busy) return
                this.busy = true
                karteFromTpl(this.mid, { tpl_id: this.tplSel, karte_id: this.karte.length ? this.karte[0].id : '' }).then((res) => {
                    this.busy = false
                    if (res && res.code == 200) {
                        this.tplShow = false
                        uni.navigateTo({ url: '/pages/karte/view?id=' + res.data.karte_id + '&page_id=' + res.data.page_id + '&name=' + encodeURIComponent(this.name) })
                    }
                })
            },
            rename() {
                this.menuShow = false
                const k = this.karte[0]
                uni.showModal({
                    title: 'カルテの名前',
                    editable: true,
                    content: k.title,
                    placeholderText: '例:問診・施術記録',
                    confirmText: '保存',
                    cancelText: 'キャンセル',
                    success: (m) => {
                        if (!m.confirm) return
                        karteRename(k.id, (m.content || '').trim()).then((res) => {
                            if (res && res.code == 200) { uni.showToast({ title: res.message, icon: 'none' }); this.load() }
                        })
                    }
                })
            },
            removeKarte() {
                this.menuShow = false
                const k = this.karte[0]
                uni.showModal({
                    title: '「' + k.title + '」を消しますか?',
                    content: '30日以内なら「削除したもの」から戻せます。',
                    confirmText: '消す',
                    confirmColor: '#D93025',
                    cancelText: 'キャンセル',
                    success: (m) => {
                        if (!m.confirm) return
                        karteDelete(k.id).then((res) => {
                            if (res && res.code == 200) { uni.showToast({ title: '消しました', icon: 'none' }); this.load() }
                        })
                    }
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .kp { min-height: 100vh; background: #F2F2F7; color: #1D1D1F; }
    .kp-hover { opacity: 0.6; }
    .kp-title { display: flex; flex-direction: column; align-items: center; line-height: 1.2; }
    .kp-t { font-size: 32rpx; font-weight: 700; }
    .kp-s { font-size: 22rpx; color: #6E6E73; }
    .kp-dots { width: 64rpx; height: 64rpx; border-radius: 50%; background: #E5E5EA; display: flex; align-items: center; justify-content: center; }
    .kp-dot { width: 9rpx; height: 9rpx; border-radius: 50%; background: #3C3C43; margin: 0 3rpx; } /* [カスタムエイド 2026-10-23] 点を大きく */
    .kp-lock { margin: 48rpx 32rpx; padding: 48rpx 24rpx; background: #FFFFFF; border-radius: 32rpx; text-align: center; font-size: 26rpx; line-height: 1.7; color: #6E6E73; white-space: pre-wrap; }
    .kp-body { padding: 32rpx 24rpx 0; }
    .kp-h { display: flex; font-size: 30rpx; font-weight: 700; color: #3C3C43; margin: 8rpx 12rpx 16rpx; }
    .kp-h-n { margin-left: auto; font-size: 24rpx; font-weight: 400; color: #6E6E73; }
    .kp-card { background: #FFFFFF; border-radius: 32rpx; padding: 0 32rpx; margin-bottom: 48rpx; }
    .kp-k { display: flex; padding: 32rpx 0; }
    .kp-cover { width: 240rpx; height: 332rpx; flex-shrink: 0; background: #FBFAF6; border: 1px solid #E5E5EA; border-radius: 8rpx; overflow: hidden;
        image { width: 100%; height: 100%; } }
    .kp-kt { flex: 1; min-width: 0; margin-left: 28rpx; }
    .kp-kname { font-size: 34rpx; font-weight: 700; word-break: break-all; }
    .kp-kmeta { margin-top: 12rpx; font-size: 24rpx; color: #6E6E73; }
    .kp-tag { display: inline-block; margin-right: 10rpx; padding: 0 12rpx; border-radius: 8rpx; background: #FCE8E6; color: #C5221F; font-size: 20rpx; line-height: 32rpx; }
    .kp-tag.pic { background: #E8F0FE; color: #1A73E8; }
    .kp-kwho { margin-top: 12rpx; font-size: 22rpx; color: #8E8E93; line-height: 1.5; white-space: pre-wrap; }
    .kp-open { display: inline-flex; align-items: center; margin-top: 24rpx; height: 72rpx; padding: 0 28rpx; border-radius: 36rpx; background: #1A73E8; color: #FFFFFF; font-size: 26rpx; font-weight: 700; }
    .kp-grid-card { padding: 24rpx; }
    .kp-grid { display: flex; flex-wrap: wrap; }
    .kp-pic { position: relative; width: calc((100% - 24rpx) / 3); height: 0; padding-bottom: calc((100% - 24rpx) / 3); margin: 0 12rpx 12rpx 0; border-radius: 16rpx; overflow: hidden; background: #E5E5EA;
        image { position: absolute; left: 0; top: 0; width: 100%; height: 100%; } }
    .kp-pic:nth-child(3n) { margin-right: 0; }
    .kp-pic-d { position: absolute; left: 8rpx; bottom: 6rpx; color: #FFFFFF; font-size: 18rpx; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.6); }
    .kp-pic-m { position: absolute; right: 8rpx; bottom: 8rpx; padding: 0 8rpx; border-radius: 10rpx; background: rgba(0, 0, 0, 0.6); color: #FFFFFF; font-size: 18rpx; }
    .kp-space { height: calc(200rpx + env(safe-area-inset-bottom)); }
    .kp-fab { position: fixed; left: 32rpx; right: 32rpx; bottom: calc(48rpx + env(safe-area-inset-bottom)); height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; box-shadow: 0 8rpx 24rpx rgba(26, 115, 232, 0.35); z-index: 10; }
    .kp-empty { padding: 48rpx 24rpx; text-align: center; }
    .kp-empty-i { width: 120rpx; height: 120rpx; border-radius: 32rpx; background: #EEF4FF; margin: 0 auto 24rpx; display: flex; align-items: center; justify-content: center; font-size: 56rpx; }
    .kp-empty-t { font-size: 32rpx; font-weight: 700; }
    .kp-empty-s { margin-top: 16rpx; font-size: 24rpx; color: #6E6E73; line-height: 1.7; white-space: pre-wrap; }
    .kp-ob { display: flex; align-items: center; background: #FFFFFF; border: 1px solid #D2D2D7; border-radius: 28rpx; padding: 24rpx 28rpx; margin-bottom: 20rpx; }
    .kp-ob-i { width: 76rpx; height: 76rpx; border-radius: 20rpx; background: #EEF4FF; display: flex; align-items: center; justify-content: center; font-size: 36rpx; flex-shrink: 0; }
    .kp-ob-tt { margin-left: 24rpx; font-size: 30rpx; font-weight: 700; display: flex; flex-direction: column; }
    .kp-ob-s { font-size: 22rpx; font-weight: 400; color: #6E6E73; margin-top: 4rpx; }
    .kp-prog { background: #FFFFFF; border-radius: 32rpx; padding: 32rpx; margin-bottom: 48rpx; }
    .kp-prog-t { font-size: 30rpx; font-weight: 700; }
    .kp-prog-s { font-size: 24rpx; color: #6E6E73; margin-top: 8rpx; }
    .kp-bar { height: 16rpx; border-radius: 8rpx; background: #E5E5EA; margin-top: 24rpx; overflow: hidden; }
    .kp-bar-in { height: 100%; background: #1A73E8; border-radius: 8rpx; transition: width 0.3s; }
    .kp-fail { background: #FCE8E6; color: #C5221F; border-radius: 24rpx; padding: 24rpx 28rpx; font-size: 26rpx; margin-bottom: 32rpx; line-height: 1.6; }
    .kp-op { display: flex; align-items: center; min-height: 128rpx; margin: 0 40rpx; border-bottom: 1px solid #E5E5EA; }
    .kp-op:last-child { border-bottom: 0; }
    .kp-op-i { width: 76rpx; height: 76rpx; border-radius: 20rpx; background: #EEF4FF; color: #1A73E8; display: flex; align-items: center; justify-content: center; font-size: 34rpx; flex-shrink: 0; }
    .kp-op-tt { margin-left: 28rpx; font-size: 30rpx; display: flex; flex-direction: column; }
    .kp-op-s { font-size: 22rpx; color: #6E6E73; margin-top: 4rpx; }
    .kp-op.red { color: #D93025; }
    .kp-op.red .kp-op-i { background: #FCE8E6; color: #D93025; }
    .kp-tpl-note { font-size: 24rpx; color: #6E6E73; padding: 24rpx 40rpx 8rpx; }
    .kp-tpls { display: flex; padding: 8rpx 40rpx 0; }
    .kp-tc { flex: 1; margin-right: 20rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 20rpx 12rpx; text-align: center; }
    .kp-tc:last-child { margin-right: 0; }
    .kp-tc.on { border-color: #1A73E8; background: rgba(26, 115, 232, 0.10); }
    .kp-tc-img { width: 128rpx; height: 176rpx; background: #FBFAF6; }
    .kp-tc-n { font-size: 24rpx; margin-top: 12rpx; }
    .kp-tc.on .kp-tc-n { color: #1A73E8; font-weight: 700; }
    .kp-tc-s { font-size: 20rpx; color: #6E6E73; }
    .kp-btn { margin: 40rpx 40rpx 0; height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .kp-btn.off { opacity: 0.5; }

    /* [カスタムエイド 2026-10-23] 単色のアイコン */
    .k-ic { width: 48rpx; height: 48rpx; }
    .kp-empty-i .k-ic { width: 64rpx; height: 64rpx; }
    .k-lock { width: 30rpx; height: 30rpx; vertical-align: -4rpx; margin-right: 8rpx; }
</style>
