<template>
    <!-- [カスタムエイド 2026-10-23] 撮影したあと(B3)。画面いっぱいに表示して確かめ、
         「切り抜き」(四隅・辺をドラッグ)・「明るさ」(明るさ・コントラスト)・「回転」をしてから保存。
         切り抜き・明るさはサーバーで同じように仕上げる(画面の見た目は CSS で先に見せる)。歪み(台形)の直しは書類スキャンの部品で後から -->
    <view class="cp">
        <view class="cp-nav" :style="{ paddingTop: top + 'px' }">
            <view class="cp-x" hover-class="cp-hover" @click="back">✕</view>
            <view class="cp-t">{{ editId || tplId ? '画像編集' : '撮影した画像' }}<text class="cp-s">{{ tool == 'crop' ? '四隅・辺をドラッグして切り抜き' : tool == 'adjust' ? 'スライダーで明るさを調整' : '2本指で拡大して確かめられます' }}</text></view>
            <view class="cp-reset" v-if="changed && !editId && !tplId" hover-class="cp-hover" @click="reset">元に戻す</view>
            <!-- [カスタムエイド 2026-10-24] 画像編集のときは右上を「保存」に(テンプレートを編集・カルテと同じ位置) -->
            <view class="cp-ok" v-if="editId || tplId" :class="{ off: busy }" hover-class="cp-hover" @click="onSave">{{ tool ? '決定' : '保存' }}</view>
        </view>

        <view class="cp-stage" id="cpStage" :style="{ top: (top + 52) + 'px', bottom: bottomH }">
            <!-- 確かめるとき(拡大できる) -->
            <movable-area class="cp-area" :scale-area="true" v-if="tool == ''">
                <movable-view class="cp-mv" direction="all" :scale="true" :scale-min="1" :scale-max="4" :out-of-bounds="false">
                    <view class="cp-box cl" :style="boxStyle">
                        <view class="cp-clip" :style="clipStyle">
                            <image class="cp-img" :src="path" :style="imgStyle"></image>
                        </view>
                    </view>
                </movable-view>
            </movable-area>
            <!-- 明るさのとき(切り抜いた部分を見せる) -->
            <view class="cp-box cl" :style="boxStyle" v-else-if="tool == 'adjust'">
                <view class="cp-clip" :style="clipStyle">
                    <image class="cp-img" :src="path" :style="imgStyle"></image>
                </view>
            </view>
            <!-- 切り抜きのとき(全体を見せて枠を動かす) -->
            <view class="cp-box" :style="boxStyle" v-else>
                <image class="cp-img" :src="path" :style="imgStyle"></image>
                <block v-if="tool == 'crop'">
                    <view class="cp-dim" :style="{ left: 0, top: 0, right: 0, height: crop.y * 100 + '%' }"></view>
                    <view class="cp-dim" :style="{ left: 0, bottom: 0, right: 0, height: (1 - crop.y - crop.h) * 100 + '%' }"></view>
                    <view class="cp-dim" :style="{ left: 0, top: crop.y * 100 + '%', width: crop.x * 100 + '%', height: crop.h * 100 + '%' }"></view>
                    <view class="cp-dim" :style="{ right: 0, top: crop.y * 100 + '%', width: (1 - crop.x - crop.w) * 100 + '%', height: crop.h * 100 + '%' }"></view>
                    <view class="cp-rect" :style="{ left: crop.x * 100 + '%', top: crop.y * 100 + '%', width: crop.w * 100 + '%', height: crop.h * 100 + '%' }"
                        @touchstart.stop.prevent="cs($event, 'move')" @touchmove.stop.prevent="cm" @touchend="ce">
                        <view class="cp-grid v1"></view><view class="cp-grid v2"></view><view class="cp-grid h1"></view><view class="cp-grid h2"></view>
                        <view v-for="h in handles" :key="h" class="cp-h" :class="h" @touchstart.stop.prevent="cs($event, h)" @touchmove.stop.prevent="cm" @touchend="ce"></view>
                    </view>
                </block>
            </view>
        </view>

        <view class="cp-bot">
            <!-- 明るさ -->
            <view class="cp-adj" v-if="tool == 'adjust'">
                <view class="cp-sl"><text class="cp-sl-l">明るさ</text><slider class="cp-slider" :min="-60" :max="60" :value="bright" activeColor="#4C9BFF" backgroundColor="#48484A" block-size="22" @changing="bright = $event.detail.value" @change="bright = $event.detail.value"></slider><text class="cp-sl-v">{{ bright > 0 ? '+' : '' }}{{ bright }}</text></view>
                <view class="cp-sl"><text class="cp-sl-l">コントラスト</text><slider class="cp-slider" :min="-60" :max="60" :value="contrast" activeColor="#4C9BFF" backgroundColor="#48484A" block-size="22" @changing="contrast = $event.detail.value" @change="contrast = $event.detail.value"></slider><text class="cp-sl-v">{{ contrast > 0 ? '+' : '' }}{{ contrast }}</text></view>
                <view class="cp-preset">
                    <text class="cp-pr" @click="setAdj(0, 0)">元のまま</text>
                    <text class="cp-pr" @click="setAdj(15, 25)">くっきり(書類)</text>
                    <text class="cp-pr" @click="setAdj(25, 10)">明るく</text>
                </view>
            </view>
            <!-- [カスタムエイド 2026-10-24] テンプレートの画像編集:上の切り替えで「差し込み」に戻れる -->
            <view class="cp-tab" v-if="tplId && tool != 'adjust'">
                <view class="cp-tb" hover-class="cp-hover" @click="back">差し込み</view>
                <view class="cp-tb on">画像編集</view>
            </view>
            <view class="cp-note" v-if="(editId || tplId) && tool != 'adjust'">{{ tplId ? '直すのは下の画像だけです。差し込みは消えません' : '直すのは下の画像だけです。文字・図形・メモはそのまま残ります' }}</view>
            <view class="cp-seg" v-else-if="!editId && !tplId">
                <view class="cp-sg" :class="{ on: as == 'karte' }" @click="as = 'karte'">カルテにする<text class="cp-sg-s">{{ karteName ? '「' + karteName + '」の' + (pages + 1) + 'ページ目に' : '新しいカルテを作ります' }}</text></view>
                <view class="cp-sg" :class="{ on: as == 'photo' }" @click="as = 'photo'">写真として残す<text class="cp-sg-s">写真の欄に</text></view>
            </view>
            <view class="cp-tools">
                <view class="cp-tl" :class="{ on: tool == 'crop' }" @click="setTool('crop')"><view class="cp-ti">⌗</view>切り抜き</view>
                <view class="cp-tl" :class="{ on: tool == 'adjust' }" @click="setTool('adjust')"><view class="cp-ti">☀︎</view>明るさ</view>
                <view class="cp-tl" @click="turn"><view class="cp-ti">↻</view>回転</view>
                <view class="cp-tl" v-if="!editId && !tplId" @click="retake"><view class="cp-ti">◎</view>撮り直す</view>
                <!-- [カスタムエイド 2026-10-24] 元に戻す:この画面で直したところを戻す/直していなければ、最初に取り込んだ画像に戻す -->
                <view class="cp-tl" v-if="editId || tplId" :class="{ dim: !changed && !edited }" @click="restore"><view class="cp-ti">↺</view>元に戻す</view>
                <view class="cp-sv" v-if="!editId && !tplId" :class="{ off: busy }" hover-class="cp-hover" @click="onSave">{{ tool ? '決定' : (busy ? '保存中…' : '保存する') }}</view>
            </view>
        </view>
    </view>
</template>

<script>
    import pickImage from '@/utils/pickImage.js'
    import { karteUploadTo, karteEditPage, karteTplImage } from '@/api/karte.js'

    const FULL = { x: 0, y: 0, w: 1, h: 1 }
    export default {
        data() {
            return {
                top: 20,
                mid: 0,
                path: '',
                karteId: '',
                karteName: '',
                pages: 0,
                as: 'karte',
                rotate: 0,
                busy: false,
                tool: '', // '' / crop / adjust
                iw: 3, ih: 4, // 画像の縦横(向きを直したあと)
                sw: 375, sh: 500, // 表示する場所の大きさ
                crop: Object.assign({}, FULL),
                bright: 0,
                contrast: 0,
                handles: ['tl', 'tr', 'bl', 'br', 't', 'b', 'l', 'r'],
                drag: null,
                editId: 0, // [カスタムエイド 2026-10-24] 画像編集(保存済みのページを直す)のときのページID
                tplId: 0, // [カスタムエイド 2026-10-24] テンプレートの画像編集のときのテンプレートID
                edited: false // 前に直したことがある(元の画像に戻せる)
            }
        },
        computed: {
            bottomH() {
                if (this.tool == 'adjust') return 'calc(300px + env(safe-area-inset-bottom))'
                return this.tplId ? 'calc(236px + env(safe-area-inset-bottom))' : 'calc(176px + env(safe-area-inset-bottom))'
            },
            // 回転したあとの縦横
            rw() { return this.rotate % 180 ? this.ih : this.iw },
            rh() { return this.rotate % 180 ? this.iw : this.ih },
            // 表示する枠(回転・切り抜き前の全体)
            box() {
                const r = Math.min(this.sw / this.rw, this.sh / this.rh)
                return { w: this.rw * r, h: this.rh * r }
            },
            boxStyle() {
                // 確かめるときは、切り抜いた部分だけを大きく見せる
                if (this.tool != 'crop' && this.isCropped) {
                    const cw = this.rw * this.crop.w, ch = this.rh * this.crop.h
                    const r = Math.min(this.sw / cw, this.sh / ch)
                    return { width: cw * r + 'px', height: ch * r + 'px', left: (this.sw - cw * r) / 2 + 'px', top: (this.sh - ch * r) / 2 + 'px' }
                }
                return { width: this.box.w + 'px', height: this.box.h + 'px', left: (this.sw - this.box.w) / 2 + 'px', top: (this.sh - this.box.h) / 2 + 'px' }
            },
            clipStyle() {
                if (!this.isCropped) return { left: 0, top: 0, width: '100%', height: '100%' }
                return { left: -this.crop.x / this.crop.w * 100 + '%', top: -this.crop.y / this.crop.h * 100 + '%', width: 100 / this.crop.w + '%', height: 100 / this.crop.h + '%' }
            },
            imgStyle() {
                // 回転:画像の元の縦横の大きさで置いて、真ん中で回す
                const sideways = this.rotate % 180 != 0
                const f = 'brightness(' + (1 + this.bright / 100) + ') contrast(' + (1 + this.contrast / 100) + ')'
                if (!sideways) return { width: '100%', height: '100%', left: 0, top: 0, transform: 'rotate(' + this.rotate + 'deg)', filter: f }
                // 横向き:幅と高さを入れ替えて置く
                const wPct = (this.rh / this.rw) * 100, hPct = (this.rw / this.rh) * 100
                return { width: wPct + '%', height: hPct + '%', left: (100 - wPct) / 2 + '%', top: (100 - hPct) / 2 + '%', transform: 'rotate(' + this.rotate + 'deg)', filter: f }
            },
            isCropped() {
                const c = this.crop
                return c.x > 0.002 || c.y > 0.002 || c.w < 0.996 || c.h < 0.996
            },
            changed() {
                return this.isCropped || this.rotate || this.bright || this.contrast
            }
        },
        onLoad(o) {
            try { this.top = uni.getSystemInfoSync().statusBarHeight || 20 } catch (e) {}
            this.mid = Number(o.mid) || 0
            this.path = decodeURIComponent(o.path || '')
            this.karteId = o.karte_id || ''
            this.karteName = o.karte ? decodeURIComponent(o.karte) : ''
            this.pages = Number(o.pages) || 0
            if (o.as) this.as = o.as
            this.editId = Number(o.edit) || 0
            this.tplId = Number(o.tpl) || 0
            this.edited = o.edited == '1'
            this.info()
        },
        onReady() {
            this.measure()
        },
        watch: {
            tool() { this.$nextTick(() => this.measure()) }
        },
        methods: {
            back() {
                uni.navigateBack()
            },
            measure() {
                uni.createSelectorQuery().in(this).select('#cpStage').boundingClientRect((r) => {
                    if (r && r.width) { this.sw = r.width; this.sh = r.height }
                }).exec()
            },
            info() {
                uni.getImageInfo({
                    src: this.path,
                    success: (r) => {
                        const side = ['left', 'right', 'left-mirrored', 'right-mirrored'].indexOf(r.orientation) != -1
                        this.iw = side ? r.height : r.width
                        this.ih = side ? r.width : r.height
                    }
                })
            },
            setTool(t) {
                this.tool = this.tool == t ? '' : t
            },
            setAdj(b, c) {
                this.bright = b
                this.contrast = c
            },
            reset() {
                this.crop = Object.assign({}, FULL)
                this.rotate = 0
                this.bright = 0
                this.contrast = 0
            },
            turn() {
                // 切り抜きの枠も一緒に回す(時計回り 90°)
                const c = this.crop
                this.crop = { x: 1 - c.y - c.h, y: c.x, w: c.h, h: c.w }
                this.rotate = (this.rotate + 90) % 360
            },
            retake() {
                pickImage({
                    count: 1,
                    sourceType: ['camera'],
                    sizeType: ['original'],
                    success: (r) => {
                        this.path = r.tempFilePaths[0]
                        this.reset()
                        this.info()
                    }
                })
            },
            // ---- 切り抜きの枠のドラッグ ----
            cs(e, h) {
                const t = e.touches[0]
                this.drag = { h, x: t.clientX, y: t.clientY, c: Object.assign({}, this.crop) }
            },
            cm(e) {
                const d = this.drag
                if (!d) return
                const t = e.touches[0]
                const dx = (t.clientX - d.x) / this.box.w, dy = (t.clientY - d.y) / this.box.h
                const c = Object.assign({}, d.c)
                const MIN = 0.08
                if (d.h == 'move') {
                    c.x = Math.min(1 - c.w, Math.max(0, c.x + dx))
                    c.y = Math.min(1 - c.h, Math.max(0, c.y + dy))
                } else {
                    if (d.h.indexOf('l') != -1) { const nx = Math.min(c.x + c.w - MIN, Math.max(0, c.x + dx)); c.w += c.x - nx; c.x = nx }
                    if (d.h.indexOf('r') != -1) c.w = Math.min(1 - c.x, Math.max(MIN, c.w + dx))
                    if (d.h.indexOf('t') != -1) { const ny = Math.min(c.y + c.h - MIN, Math.max(0, c.y + dy)); c.h += c.y - ny; c.y = ny }
                    if (d.h.indexOf('b') != -1) c.h = Math.min(1 - c.y, Math.max(MIN, c.h + dy))
                }
                this.crop = c
            },
            ce() {
                this.drag = null
            },
            // [カスタムエイド 2026-10-24] 画像編集をサーバーに送る(ページ/テンプレート)
            sendEdit(data, done) {
                const req = this.tplId ? karteTplImage(this.tplId, data) : karteEditPage(this.editId, data)
                req.then((r) => {
                    uni.hideLoading()
                    this.busy = false
                    if (r && r.code == 200) {
                        // テンプレートは、編集画面が新しい画像と差し込みの位置を読み直せるように渡す
                        if (this.tplId && r.data && r.data.url) {
                            const t = uni.getStorageSync('karteTplEdit') || {}
                            uni.setStorageSync('karteTplEdit', Object.assign(t, { url: r.data.url, w: r.data.w, h: r.data.h, marks: r.data.marks || [], edited: !!r.data.edited, reload: 1 }))
                        }
                        uni.showToast({ title: done, icon: 'none' })
                        setTimeout(() => uni.navigateBack(), 600)
                    } else {
                        uni.showToast({ title: (r && r.message) || '保存できませんでした', icon: 'none' })
                    }
                })
            },
            // 元に戻す:この画面で直したところがあれば戻す。なければ、最初に取り込んだ画像に戻す(サーバー)
            restore() {
                if (this.changed) { this.reset(); this.tool = ''; return }
                if (!this.edited) return uni.showToast({ title: '元の画像のままです', icon: 'none' })
                uni.showModal({
                    title: '最初の画像に戻しますか?',
                    content: this.tplId ? '切り抜き・明るさ・回転を取り消します。差し込みは消えません。' : '切り抜き・明るさ・回転を取り消します。文字・図形・メモは消えません。',
                    confirmText: '戻す',
                    cancelText: 'キャンセル',
                    success: (m) => {
                        if (!m.confirm || this.busy) return
                        this.busy = true
                        uni.showLoading({ title: '戻しています', mask: true })
                        this.sendEdit({ restore: 1 }, '元の画像に戻しました')
                    }
                })
            },
            onSave() {
                if (this.tool) { this.tool = ''; return }
                this.save()
            },
            save() {
                if (this.busy) return
                this.busy = true
                uni.showLoading({ title: '保存しています', mask: true })
                const c = this.crop
                const data = { mode: this.as, karte_id: this.as == 'karte' ? this.karteId : '', rotate: this.rotate, bright: this.bright, contrast: this.contrast }
                if (this.isCropped) data.crop = JSON.stringify([c.x, c.y, c.w, c.h].map((v) => Math.round(v * 10000) / 10000))
                if (this.editId || this.tplId) {
                    // 画像編集:サーバーで元の画像から作り直す(書き込み・差し込みは消さず、位置だけ画像に合わせる)
                    if (!this.changed) { uni.hideLoading(); this.busy = false; return uni.navigateBack() }
                    delete data.mode
                    delete data.karte_id
                    this.sendEdit(data, '画像を直しました')
                    return
                }
                karteUploadTo(this.mid, this.path, data).then((r) => {
                    uni.hideLoading()
                    this.busy = false
                    if (r.code == 200) {
                        uni.showToast({ title: this.as == 'karte' ? 'カルテに足しました' : '写真を保存しました', icon: 'none' })
                        setTimeout(() => uni.navigateBack(), 600)
                    } else {
                        uni.showToast({ title: r.message || '保存できませんでした', icon: 'none' })
                    }
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .cp { position: fixed; left: 0; top: 0; right: 0; bottom: 0; background: #1C1C1E; color: #FFFFFF; }
    .cp-hover { opacity: 0.6; }
    .cp-nav { position: absolute; left: 0; right: 0; top: 0; height: 52px; display: flex; align-items: center; justify-content: center; z-index: 3; background: rgba(28, 28, 30, 0.92); box-sizing: content-box; }
    .cp-x { position: absolute; left: 24rpx; bottom: 12px; width: 60rpx; height: 60rpx; border-radius: 50%; background: #3A3A3C; display: flex; align-items: center; justify-content: center; font-size: 24rpx; }
    .cp-reset { position: absolute; right: 24rpx; bottom: 16px; font-size: 26rpx; color: #8AB4F8; }
    .cp-t { font-size: 30rpx; font-weight: 700; display: flex; flex-direction: column; align-items: center; }
    .cp-s { font-size: 20rpx; font-weight: 400; color: #AEAEB2; }
    .cp-stage { position: absolute; left: 24rpx; right: 24rpx; }
    .cp-area, .cp-mv { width: 100%; height: 100%; }
    .cp-box { position: absolute; }
    .cp-box.cl { overflow: hidden; }
    .cp-clip { position: absolute; }
    .cp-img { position: absolute; }
    .cp-dim { position: absolute; background: rgba(0, 0, 0, 0.55); }
    .cp-rect { position: absolute; border: 2px solid #FFFFFF; box-sizing: border-box; }
    .cp-grid { position: absolute; background: rgba(255, 255, 255, 0.35); }
    .cp-grid.v1 { left: 33.3%; top: 0; bottom: 0; width: 1px; }
    .cp-grid.v2 { left: 66.6%; top: 0; bottom: 0; width: 1px; }
    .cp-grid.h1 { top: 33.3%; left: 0; right: 0; height: 1px; }
    .cp-grid.h2 { top: 66.6%; left: 0; right: 0; height: 1px; }
    .cp-h { position: absolute; width: 44px; height: 44px; }
    .cp-h::after { content: ''; position: absolute; background: #FFFFFF; }
    .cp-h.tl { left: -22px; top: -22px; } .cp-h.tr { right: -22px; top: -22px; } .cp-h.bl { left: -22px; bottom: -22px; } .cp-h.br { right: -22px; bottom: -22px; }
    .cp-h.tl::after, .cp-h.tr::after, .cp-h.bl::after, .cp-h.br::after { width: 16px; height: 16px; border-radius: 8px; left: 14px; top: 14px; }
    .cp-h.t { left: 50%; margin-left: -22px; top: -22px; } .cp-h.b { left: 50%; margin-left: -22px; bottom: -22px; }
    .cp-h.l { top: 50%; margin-top: -22px; left: -22px; } .cp-h.r { top: 50%; margin-top: -22px; right: -22px; }
    .cp-h.t::after, .cp-h.b::after { width: 24px; height: 5px; border-radius: 3px; left: 10px; top: 19px; }
    .cp-h.l::after, .cp-h.r::after { width: 5px; height: 24px; border-radius: 3px; left: 19px; top: 10px; }
    .cp-bot { position: absolute; left: 0; right: 0; bottom: 0; background: rgba(28, 28, 30, 0.96); padding: 20rpx 32rpx calc(32rpx + env(safe-area-inset-bottom)); z-index: 3; }
    .cp-seg { display: flex; }
    .cp-sg { flex: 1; border: 1px solid #48484A; border-radius: 24rpx; padding: 16rpx 12rpx; font-size: 28rpx; font-weight: 700; text-align: center; color: #E5E5EA; display: flex; flex-direction: column; }
    .cp-sg:first-child { margin-right: 16rpx; }
    .cp-sg-s { font-size: 20rpx; font-weight: 400; color: #AEAEB2; margin-top: 4rpx; }
    .cp-sg.on { background: rgba(26, 115, 232, 0.25); border-color: #4C9BFF; color: #8AB4F8; }
    .cp-tools { display: flex; align-items: center; margin-top: 20rpx; }
    .cp-tl { width: 112rpx; display: flex; flex-direction: column; align-items: center; font-size: 20rpx; color: #E5E5EA; }
    .cp-ti { width: 64rpx; height: 64rpx; border-radius: 18rpx; background: #3A3A3C; display: flex; align-items: center; justify-content: center; font-size: 32rpx; margin-bottom: 6rpx; }
    .cp-tl.on { color: #4C9BFF; }
    .cp-tl.on .cp-ti { background: rgba(26, 115, 232, 0.3); color: #4C9BFF; }
    .cp-sv { margin-left: auto; background: #1A73E8; color: #FFFFFF; border-radius: 24rpx; height: 96rpx; padding: 0 40rpx; display: flex; align-items: center; font-size: 30rpx; font-weight: 700; }
    .cp-sv.off { opacity: 0.5; }
    .cp-note { padding: 14rpx 32rpx 6rpx; color: #AEAEB2; font-size: 24rpx; line-height: 1.5; text-align: center; }
    .cp-ok { position: absolute; right: 14px; bottom: 9px; height: 34px; padding: 0 16px; border-radius: 17px; background: #1A73E8; display: flex; align-items: center; font-size: 15px; font-weight: 700; }
    .cp-ok.off { opacity: 0.5; }
    .cp-tab { margin: 0 0 8rpx; padding: 6rpx; border-radius: 24rpx; background: #2C2C2E; display: flex; }
    .cp-tb { flex: 1; height: 76rpx; border-radius: 18rpx; display: flex; align-items: center; justify-content: center; font-size: 28rpx; color: #AEAEB2; box-sizing: border-box; }
    .cp-tb.on { background: rgba(76, 155, 255, 0.16); border: 1.5px solid #4C9BFF; color: #FFFFFF; font-weight: 700; }
    .cp-tl.dim { opacity: 0.4; }
    .cp-adj { padding: 0 4rpx; }
    .cp-sl { display: flex; align-items: center; height: 80rpx; }
    .cp-sl-l { width: 150rpx; font-size: 24rpx; color: #E5E5EA; }
    .cp-slider { flex: 1; margin: 0 16rpx; }
    .cp-sl-v { width: 64rpx; text-align: right; font-size: 24rpx; color: #AEAEB2; }
    .cp-preset { display: flex; margin-top: 8rpx; }
    .cp-pr { font-size: 22rpx; color: #8AB4F8; border: 1px solid #48484A; border-radius: 28rpx; padding: 8rpx 20rpx; margin-right: 16rpx; }
</style>
