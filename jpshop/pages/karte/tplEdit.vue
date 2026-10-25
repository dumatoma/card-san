<template>
    <!-- [カスタムエイド 2026-10-24] テンプレートを編集(作り直し)。
         上の切り替えで「差し込み」と「画像編集」。差し込み(お名前・フリガナ・電話番号・性別・生年月日・住所)は、
         テンプレートを足したとき、置いた場所にそのお客様の会員登録の情報が文字で入る。
         紙の表示・拡大(300%まで)・差し込みの表示とドラッグは、カルテの画面と同じ renderjs(画面の側)で動かす。
         画像編集(切り抜き・明るさ・回転・元に戻す)も同じ画面のまま行い、差し込みは画像の中の同じ場所に残る -->
    <view class="te">
        <!-- 紙の表示(renderjs)。いちばん最初に置く -->
        <view id="teStage" key="teStage" class="te-stage" :class="{ ed: edit && edit.tool != 'adjust', eda: edit && edit.tool == 'adjust' }" :style="{ top: (top + 52) + 'px' }" :prop="stage" :change:prop="kv.onProp"></view>

        <view class="te-nav" :style="{ paddingTop: top + 'px' }">
            <view class="te-x" hover-class="te-hover" @click="back">✕</view>
            <view class="te-t" hover-class="te-hover" @click="openRename">テンプレートを編集<text class="te-s">{{ edit && edit.tool == 'crop' ? '四隅・辺をドラッグして切り抜き' : name + ' ✎' }}</text></view>
            <view class="te-ok" :class="{ off: busy }" hover-class="te-hover" @click="save(true)">保存</view>
        </view>
        <view class="te-chip" :style="{ top: (top + 60) + 'px' }" v-if="zoom > 1.02">{{ Math.round(zoom * 100) }}%</view>

        <view class="te-bot" :class="{ ed: edit && edit.tool != 'adjust', eda: edit && edit.tool == 'adjust' }">
            <view class="te-seg">
                <view class="te-sg" :class="{ on: !edit }" @click="setTab('tag')">差し込み</view>
                <view class="te-sg" :class="{ on: !!edit }" @click="setTab('image')">画像編集</view>
            </view>
            <!-- [カスタムエイド 2026-10-24] 画像編集(同じ画面のまま)。直すのは下の画像だけ -->
            <block v-if="edit">
                <view class="te-eadj" v-if="edit.tool == 'adjust'">
                    <view class="te-esl"><text class="te-esl-l">明るさ</text><slider class="te-eslider" :min="-60" :max="60" :value="edit.bright" activeColor="#4C9BFF" backgroundColor="#48484A" block-size="20" @changing="edit.bright = $event.detail.value" @change="edit.bright = $event.detail.value"></slider><text class="te-esl-v">{{ edit.bright > 0 ? '+' : '' }}{{ edit.bright }}</text></view>
                    <view class="te-esl"><text class="te-esl-l">コントラスト</text><slider class="te-eslider" :min="-60" :max="60" :value="edit.contrast" activeColor="#4C9BFF" backgroundColor="#48484A" block-size="20" @changing="edit.contrast = $event.detail.value" @change="edit.contrast = $event.detail.value"></slider><text class="te-esl-v">{{ edit.contrast > 0 ? '+' : '' }}{{ edit.contrast }}</text></view>
                </view>
                <view class="te-tip" v-else>{{ edit.tool == 'crop' ? '枠の外は切り取られます。差し込みは消えずに内側へ寄ります' : '直すのは下の画像だけです。差し込みは消えません' }}</view>
                <view class="te-etools">
                    <view class="te-et" :class="{ on: edit.tool == 'crop' }" @click="editTool('crop')"><view class="te-ei">⌗</view>切り抜き</view>
                    <view class="te-et" :class="{ on: edit.tool == 'adjust' }" @click="editTool('adjust')"><view class="te-ei">☀︎</view>明るさ</view>
                    <view class="te-et" @click="editTool('rotate')"><view class="te-ei">↻</view>回転</view>
                    <view class="te-et" :class="{ dim: !editChanged && !edited }" @click="restoreEdit"><view class="te-ei">↺</view>元に戻す</view>
                </view>
            </block>
            <!-- 選んでいる差し込み -->
            <view class="te-card" v-else-if="sel >= 0 && marks[sel]">
                <view class="te-row">
                    <text class="te-cn">{{ tags[marks[sel].key] || marks[sel].key }}</text>
                    <text class="te-del" hover-class="te-hover" @click="remove">消す</text>
                </view>
                <view class="te-row">
                    <text class="te-pl">大きさ</text>
                    <view class="te-step"><text class="te-sb" @click="bump(-1)">−</text><text class="te-sv">{{ marks[sel].size }}</text><text class="te-sb" @click="bump(1)">＋</text></view>
                </view>
                <view class="te-row">
                    <text class="te-pl">色</text>
                    <view v-for="c in colors" :key="c.key" class="te-cl" :class="{ on: marks[sel].color == c.key }" :style="{ background: c.hex }" @click="setColor(c.key)"></view>
                </view>
            </view>
            <!-- 差し込みを選ぶ -->
            <block v-else>
                <view class="te-tip">{{ pick ? 'テンプレートの入れたい所を押してください' : '入れたい項目を押してから、テンプレートの入れたい所を押します' }}</view>
                <view class="te-chips">
                    <view v-for="(n, k) in tags" :key="k" class="te-tg" :class="{ on: pick == k }" @click="pickTag(k)">{{ n }}<text class="te-ck" v-if="placed[k]">✓</text></view>
                </view>
            </block>
        </view>

        <!-- 名前・メモ・消す -->
        <cs-sheet :show="renameShow" title="テンプレートの名前" @close="renameShow = false">
            <view class="te-in">
                <view class="te-lbl">名前<text class="te-req">※</text></view>
                <input class="te-ip" v-model="form.name" maxlength="20" placeholder="例:問診票" />
                <view class="te-lbl">メモ</view>
                <input class="te-ip" v-model="form.note" maxlength="20" placeholder="例:初回用・2回目以降" />
            </view>
            <view class="te-btn1" :class="{ off: !form.name.trim() }" hover-class="te-hover" @click="applyRename">決定</view>
            <view class="te-del2" hover-class="te-hover" @click="removeTpl">このテンプレートを消す</view>
        </cs-sheet>
    </view>
</template>

<script>
    import csSheet from '@/components/csSheet/csSheet.vue'
    import { karteTplUpdate, karteTplDelete, karteTplImage, COLORS, colorHex } from '@/api/karte.js'

    const SIZES = [12, 16, 20, 24, 28, 32, 36, 40, 48, 56, 64, 72]
    export default {
        components: { csSheet },
        data() {
            return {
                top: 20,
                id: 0,
                name: '',
                note: '',
                url: '',
                w: 1414,
                h: 2000,
                marks: [],
                edited: false,
                tags: { name: 'お名前', kana: 'フリガナ', phone: '電話番号', sex: '性別', birthday: '生年月日', address: '住所' },
                colors: COLORS,
                pick: '',
                sel: -1,
                busy: false,
                dirty: false,
                zoom: 1,
                resetKey: 0,
                renameShow: false,
                form: { name: '', note: '' },
                edit: null // 画像編集中:{ rot, crop:[x,y,w,h], bright, contrast, tool }
            }
        },
        computed: {
            editChanged() {
                const e = this.edit
                if (!e) return false
                const c = e.crop
                return !!(e.rot || e.bright || e.contrast || c[0] > 0.002 || c[1] > 0.002 || c[2] < 0.996 || c[3] < 0.996)
            },
            placed() {
                const o = {}
                this.marks.forEach((g) => { o[g.key] = true })
                return o
            },
            // renderjs に渡すもの(差し込みは文字の書き込みと同じ形にして、いつも枠を出す)
            stage() {
                if (!this.url) return { key: 0 }
                return {
                    key: this.id + ':' + this.url, url: this.url, w: this.w, h: this.h, tool: this.pick ? 'text' : '', shape: 'free',
                    edit: this.edit ? { rot: this.edit.rot, crop: this.edit.crop.slice(), bright: this.edit.bright, contrast: this.edit.contrast, tool: this.edit.tool } : null,
                    reset: this.resetKey, sel: this.sel >= 0 ? this.sel + 1 : 0, tagBox: true, pen: { hex: '#1D1D1F', width: 0.004 },
                    marks: this.marks.map((g, i) => ({ id: i + 1, type: 1, x: g.x, y: g.y, text: this.tags[g.key] || g.key, hex: colorHex(g.color), size: g.size / 1000 }))
                }
            }
        },
        onLoad() {
            try { this.top = uni.getSystemInfoSync().statusBarHeight || 20 } catch (e) {}
            this.read(true)
        },
        // 画像編集から戻ったら、新しい画像と差し込みの位置を読み直す
        onShow() {
            const t = uni.getStorageSync('karteTplEdit') || {}
            if (t.reload) this.read(false)
        },
        onBackPress() {
            if (this.dirty || this.editChanged) { this.back(); return true }
            return false
        },
        methods: {
            read(first) {
                const t = uni.getStorageSync('karteTplEdit') || {}
                this.id = t.id
                if (first) { this.name = t.name || ''; this.note = t.note || '' }
                this.url = t.url
                this.w = t.w || 1414
                this.h = t.h || 2000
                this.edited = !!t.edited
                this.marks = JSON.parse(JSON.stringify(t.marks || []))
                this.sel = -1
                this.resetKey++
                if (t.reload) { delete t.reload; uni.setStorageSync('karteTplEdit', t) }
            },
            clamp(v) {
                return Math.min(0.98, Math.max(0, v))
            },
            back() {
                if (!this.dirty && !this.editChanged) return uni.navigateBack()
                uni.showModal({
                    title: '保存しないで閉じますか?', content: '差し込み・画像の変更は消えます。', confirmText: '閉じる', cancelText: 'キャンセル',
                    success: (m) => { if (m.confirm) { this.dirty = false; this.edit = null; uni.navigateBack() } }
                })
            },
            pickTag(k) {
                this.pick = this.pick == k ? '' : k
                this.sel = -1
            },
            setColor(k) {
                this.marks[this.sel].color = k
                this.dirty = true
            },
            bump(dir) {
                const g = this.marks[this.sel]
                if (dir > 0) g.size = SIZES.find((v) => v > g.size) || Math.min(200, g.size + 16)
                else g.size = SIZES.slice().reverse().find((v) => v < g.size) || Math.max(8, g.size - 4)
                this.dirty = true
            },
            remove() {
                this.marks.splice(this.sel, 1)
                this.sel = -1
                this.dirty = true
            },
            // ---- renderjs から ----
            onTap(e) {
                if (e.mark) { this.sel = e.mark - 1; this.pick = ''; return }
                if (this.pick) {
                    this.marks.push({ key: this.pick, x: this.clamp(e.x), y: this.clamp(e.y), size: 32, color: 'black' })
                    this.sel = this.marks.length - 1
                    this.pick = ''
                    this.dirty = true
                    return
                }
                this.sel = -1
            },
            onMarkChange(e) {
                const g = this.marks[e.id - 1]
                if (!g) return
                g.x = this.clamp(e.x)
                g.y = this.clamp(e.y)
                this.sel = e.id - 1
                this.dirty = true
            },
            onZoom(s) {
                this.zoom = s
            },
            onSwipe() {},
            onStroke() {},
            onDebug(msg) {
                uni.showToast({ title: '表示エラー ' + String(msg).slice(0, 60), icon: 'none', duration: 4000 })
            },
            // ---- 名前・メモ・消す ----
            openRename() {
                this.form = { name: this.name, note: this.note }
                this.renameShow = true
            },
            applyRename() {
                if (!this.form.name.trim()) return
                this.name = this.form.name.trim()
                this.note = this.form.note.trim()
                this.renameShow = false
                this.dirty = true
            },
            removeTpl() {
                uni.showModal({
                    title: '「' + this.name + '」を消しますか?', content: 'これまでに足したページは消えません。',
                    confirmText: '消す', confirmColor: '#D93025', cancelText: 'キャンセル',
                    success: (m) => {
                        if (!m.confirm) return
                        karteTplDelete(this.id).then((res) => {
                            if (res && res.code == 200) { this.renameShow = false; this.dirty = false; uni.navigateBack() }
                        })
                    }
                })
            },
            // ---- [カスタムエイド 2026-10-24] 画像編集(同じ画面のまま)----
            // 差し込み → 画像編集:そのまま切り替える。画像編集 → 差し込み:直したところがあれば、先に画像を保存してから
            async setTab(t) {
                if (t == 'image') {
                    if (this.edit) return
                    this.pick = ''
                    this.sel = -1
                    this.edit = { rot: 0, crop: [0, 0, 1, 1], bright: 0, contrast: 0, tool: '' }
                    return
                }
                if (!this.edit) return
                if (this.editChanged && !(await this.applyImage())) return
                this.edit = null
            },
            editTool(t) {
                const e = this.edit
                if (!e) return
                if (t == 'rotate') {
                    const c = e.crop
                    e.crop = [1 - c[1] - c[3], c[0], c[3], c[2]]
                    e.rot = (e.rot + 90) % 360
                    return
                }
                e.tool = e.tool == t ? '' : t
            },
            onCrop(c) {
                if (this.edit && c && c.length == 4) this.edit.crop = c
            },
            restoreEdit() {
                if (this.editChanged) { this.edit = { rot: 0, crop: [0, 0, 1, 1], bright: 0, contrast: 0, tool: '' }; return }
                if (!this.edited) return uni.showToast({ title: '元の画像のままです', icon: 'none' })
                uni.showModal({
                    title: '最初の画像に戻しますか?', content: '切り抜き・明るさ・回転を取り消します。差し込みは消えません。',
                    confirmText: '戻す', cancelText: 'キャンセル',
                    success: (m) => { if (m.confirm) this.sendImage({ restore: 1 }, '元の画像に戻しました') }
                })
            },
            // 画像の直しをサーバーに送る(差し込みの変更があれば先に保存。差し込みの位置はサーバーが画像に合わせて動かす)
            async applyImage() {
                const e = this.edit
                if (!e || !this.editChanged) return true
                if (this.dirty && !(await this.save(false))) return false
                const data = { rotate: e.rot, bright: e.bright, contrast: e.contrast }
                const c = e.crop
                if (c[0] > 0.002 || c[1] > 0.002 || c[2] < 0.996 || c[3] < 0.996) data.crop = JSON.stringify(c.map((v) => Math.round(v * 10000) / 10000))
                return this.sendImage(data, '画像を直しました')
            },
            sendImage(data, done) {
                if (this.busy) return Promise.resolve(false)
                this.busy = true
                uni.showLoading({ title: '保存しています', mask: true })
                return karteTplImage(this.id, data).then((r) => {
                    uni.hideLoading()
                    this.busy = false
                    if (r && r.code == 200) {
                        if (r.data && r.data.url) {
                            this.url = r.data.url
                            this.w = r.data.w
                            this.h = r.data.h
                            this.marks = r.data.marks || []
                            this.edited = !!r.data.edited
                        }
                        this.sel = -1
                        if (this.edit) this.edit = { rot: 0, crop: [0, 0, 1, 1], bright: 0, contrast: 0, tool: '' }
                        uni.showToast({ title: done, icon: 'none' })
                        return true
                    }
                    uni.showToast({ title: (r && r.message) || '保存できませんでした', icon: 'none' })
                    return false
                })
            },
            async save(leave) {
                if (this.busy) return false
                // 右上の保存:画像の直しがあれば、それも保存する
                if (leave && this.editChanged) {
                    if (!(await this.applyImage())) return false
                    if (!this.dirty) { setTimeout(() => uni.navigateBack(), 600); return true }
                }
                this.busy = true
                return karteTplUpdate(this.id, { name: this.name, note: this.note, marks: this.marks }).then((res) => {
                    this.busy = false
                    if (res && res.code == 200) {
                        this.dirty = false
                        if (leave) {
                            uni.showToast({ title: '保存しました', icon: 'none' })
                            setTimeout(() => uni.navigateBack(), 600)
                        }
                        return true
                    }
                    uni.showToast({ title: (res && res.message) || '保存できませんでした', icon: 'none' })
                    return false
                })
            }
        }
    }
</script>

<script module="kv" lang="renderjs">
    // [カスタムエイド 2026-10-24] 画面の側で動く部分(カルテ・写真の画面 view.vue と、テンプレートを編集 tplEdit.vue で同じものを使う。
    //   直すときは両方を同じ内容にする。違うのは STAGE_ID だけ)
    //   ・紙の表示・拡大(300%まで)/移動・書き込み(図形・文字・メモ)の表示・指で書く・ドラッグで動かす・青い点で大きさを変える
    //   ・その場で画像編集(切り抜き・明るさ・回転)。画像と書き込みは別の canvas に描くので、明るさを変えても書き込みの色は変わらない
    //   ・画面と同じ大きさの canvas に「今見えている部分だけ」を毎回描く(拡大してもメモリは同じ)
    //   ・iPhone で位置が NaN になった件:状態を Vue の data に入れず普通の値で持つ。枠の位置が取れないときは前の値を使う
    const STAGE_ID = 'teStage'
    const MAXS = 3 // 拡大の上限 300%(2026-10-24 坂本さん確認:業務では300%で十分)
    const num = (v, d) => (typeof v === 'number' && isFinite(v) ? v : d)
    // 図形を線の集まりにする(サーバーの CsKarte::shapeLines と同じ考え方)。R = 高さ ÷ 幅
    function shapeLines(m, R) {
        const p = m.points || []
        if (!m.shape || m.shape == 'free' || p.length < 2) return [p]
        const [x1, y1] = p[0], [x2, y2] = p[1]
        if (m.shape == 'line') return [[[x1, y1], [x2, y2]]]
        if (m.shape == 'arrow') {
            const dx = x2 - x1, dy = (y2 - y1) * R, len = Math.hypot(dx, dy) || 1
            const hl = Math.min(len * 0.4, Math.max(0.03, m.width * 6))
            const ux = dx / len, uy = dy / len, a = 0.45
            const h1 = [x2 - hl * (ux * Math.cos(a) - uy * Math.sin(a)), y2 - hl * (uy * Math.cos(a) + ux * Math.sin(a)) / R]
            const h2 = [x2 - hl * (ux * Math.cos(a) + uy * Math.sin(a)), y2 - hl * (uy * Math.cos(a) - ux * Math.sin(a)) / R]
            return [[[x1, y1], [x2, y2]], [h1, [x2, y2], h2]]
        }
        const l = Math.min(x1, x2), r = Math.max(x1, x2), t = Math.min(y1, y2), b = Math.max(y1, y2)
        if (m.shape == 'rect') return [[[l, t], [r, t], [r, b], [l, b], [l, t]]]
        if (m.shape == 'check') return [[[l, t + (b - t) * 0.55], [l + (r - l) * 0.38, b], [r, t]]]
        if (m.shape == 'circle') {
            const cx = (l + r) / 2, cy = (t + b) / 2, rx = (r - l) / 2, ry = (b - t) / 2, o = []
            for (let i = 0; i <= 72; i++) { const th = i / 72 * 2 * Math.PI; o.push([cx + rx * Math.cos(th), cy + ry * Math.sin(th)]) }
            return [o]
        }
        return [p]
    }
    function bbox(pts) {
        let l = 1, t = 1, r = 0, b = 0
        pts.forEach((q) => { l = Math.min(l, q[0]); t = Math.min(t, q[1]); r = Math.max(r, q[0]); b = Math.max(b, q[1]) })
        return { l, t, r, b }
    }
    function segDist(px, py, a, b, R) {
        const ax = a[0], ay = a[1] * R, bx = b[0], by = b[1] * R, x = px, y = py * R
        const dx = bx - ax, dy = by - ay, L = dx * dx + dy * dy
        let u = L ? ((x - ax) * dx + (y - ay) * dy) / L : 0
        u = Math.max(0, Math.min(1, u))
        return Math.hypot(x - (ax + u * dx), y - (ay + u * dy))
    }
    // 角の丸い四角(左上・右上・右下・左下の丸み)
    function rrect(c, x, y, w, h, r) {
        c.beginPath()
        c.moveTo(x + r[0], y)
        c.lineTo(x + w - r[1], y); c.arcTo(x + w, y, x + w, y + r[1], r[1])
        c.lineTo(x + w, y + h - r[2]); c.arcTo(x + w, y + h, x + w - r[2], y + h, r[2])
        c.lineTo(x + r[3], y + h); c.arcTo(x, y + h, x, y + h - r[3], r[3])
        c.lineTo(x, y + r[0]); c.arcTo(x, y, x + r[0], y, r[0])
        c.closePath()
    }
    // 時計回りに 90°×n 回した位置(ページに対する割合)
    function rotPt(q, n) { let x = q[0], y = q[1]; for (let i = 0; i < n; i++) { const t = x; x = 1 - y; y = t } return [x, y] }
    const FONT = '"Hiragino Sans","Hiragino Kaku Gothic ProN","Noto Sans JP",sans-serif'
    export default {
        methods: {
            S() {
                // 状態は Vue の data に入れない(iPhone で値が消えて NaN になったため)
                if (!this.kvS) this.kvS = { key: -1, s: 1, tx: 0, ty: 0, reset: 0, lay: '', rcL: 0, rcT: 0 }
                return this.kvS
            },
            onProp(nv, ov, owner) {
                this.owner = owner
                const s = this.S()
                s.p = nv || {}
                if (!nv || !nv.key) { this.clear(); return }
                if (s.key !== nv.key || s.reset !== nv.reset || !s.cvI) {
                    s.key = nv.key
                    s.reset = nv.reset
                    this.build()
                    return
                }
                // 画像編集の回転で縦横が変わったら、並べ直す
                // 画像編集の回転・画像編集の始め/終わり(余白)・表示する場所の大きさが変わったら、並べ直す
                this.fitStage()
                if (this.layKey() !== s.lay) this.layout(true)
                setTimeout(() => { if (this.fitStage()) this.draw() }, 80)
                if (!s.g || s.g.kind != 'drag' || Date.now() - s.g.t0 > 8000) {
                    // ドラッグの途中は、書き込みを描き直さない(動かしているものが元に戻らないように)
                    s.marks = JSON.parse(JSON.stringify(nv.marks || []))
                }
                if (!s.g || s.g.kind != 'crop') s.crop = this.editCrop()
                this.filter()
                this.draw()
            },
            clear() {
                const el = this.el()
                if (el) el.innerHTML = ''
                const s = this.S()
                s.cvI = null; s.cvM = null; s.img = null
            },
            el() {
                return document.getElementById(STAGE_ID)
            },
            ed() {
                return this.S().p.edit || null
            },
            rotN() {
                const e = this.ed()
                return e ? (((num(e.rot, 0) / 90) % 4) + 4) % 4 : 0
            },
            editCrop() {
                const e = this.ed()
                const c = e && e.crop
                return c && c.length == 4 ? c.slice() : [0, 0, 1, 1]
            },
            // 表示する紙の縦横(画像編集で横に回したときは入れ替える)
            layKey() {
                const p = this.S().p
                return p.w + 'x' + p.h + '@' + (this.rotN() % 2) + (p.edit ? 'e' : '')
            },
            // 表示する場所の大きさが変わったとき(画像編集の始め/終わり)、canvas の大きさを合わせる。変わったら true
            fitStage() {
                const s = this.S(), el = this.el()
                if (!el || !s.cvI) return false
                const SW = el.clientWidth, SH = el.clientHeight
                if (!SW || !SH || (SW == s.SW && SH == s.SH)) return false
                ;[s.cvI, s.cvM].forEach((cv) => {
                    cv.width = Math.round(SW * s.dpr); cv.height = Math.round(SH * s.dpr)
                    cv.style.width = SW + 'px'; cv.style.height = SH + 'px'
                })
                s.SW = SW; s.SH = SH
                this.layout(true)
                return true
            },
            build() {
                const el = this.el()
                // 画面がまだできていないときは、できるまで待つ(テンプレートを開くと真っ暗になった件)
                if (!el) { setTimeout(() => { if (this.S().p.key) this.build() }, 60); return }
                const s = this.S()
                const p = s.p
                if (!this.bound) this.bind(el)
                const SW = el.clientWidth, SH = el.clientHeight
                if (!SW || !SH) { setTimeout(() => this.build(), 60); return }
                el.innerHTML = ''
                s.SW = SW; s.SH = SH
                // 画面と同じ大きさの canvas を2枚(下:画像/上:書き込み・切り抜きの枠)。画面の密度ぶん細かく(上限3)
                const dpr = Math.min(window.devicePixelRatio || 1, 3)
                const mk = () => {
                    const cv = document.createElement('canvas')
                    cv.width = Math.round(SW * dpr); cv.height = Math.round(SH * dpr)
                    cv.style.cssText = 'position:absolute;left:0;top:0;width:' + SW + 'px;height:' + SH + 'px;'
                    el.appendChild(cv)
                    return cv
                }
                s.cvI = mk(); s.cvM = mk()
                s.ctxI = s.cvI.getContext('2d'); s.ctxM = s.cvM.getContext('2d'); s.dpr = dpr
                s.img = null
                const img = new Image()
                const key = p.key
                img.onload = () => { const t = this.S(); if (t.key === key) { t.img = img; this.draw() } }
                img.src = p.url
                s.marks = JSON.parse(JSON.stringify(p.marks || []))
                s.crop = this.editCrop()
                this.rect()
                this.layout(true)
                this.filter()
                this.draw()
            },
            // 紙を枠の真ん中に、はみ出さない大きさで置く(等倍に戻す)
            layout(resetZoom) {
                const s = this.S(), p = s.p
                const el = this.el()
                const SW = (el && el.clientWidth) || s.SW, SH = (el && el.clientHeight) || s.SH
                const odd = this.rotN() % 2 == 1
                const pw = odd ? p.h : p.w, ph = odd ? p.w : p.h
                if (!SW || !SH || !pw || !ph) return
                // 画像編集中は、まわりに余白を取る(切り抜きの枠の四隅・辺が画面の端にかからず、つかみやすいように)
                const pad = p.edit ? 32 : 0
                const r = Math.min((SW - pad * 2) / pw, (SH - pad * 2) / ph)
                s.SW = SW; s.SH = SH
                s.W = pw * r; s.H = ph * r; s.R = ph / pw
                s.left = (SW - s.W) / 2; s.top = (SH - s.H) / 2
                s.lay = this.layKey()
                if (resetZoom) { s.s = 1; s.tx = 0; s.ty = 0; this.zoomOut() }
            },
            // 明るさ・コントラスト(画像の canvas だけにかける)
            filter() {
                const s = this.S(), e = this.ed()
                if (!s.cvI) return
                const f = e && (e.bright || e.contrast) ? 'brightness(' + (1 + num(e.bright, 0) / 100) + ') contrast(' + (1 + num(e.contrast, 0) / 100) + ')' : ''
                if (s.cvI.style.filter !== f) { s.cvI.style.filter = f; s.cvI.style.webkitFilter = f }
            },
            apply() { this.draw() },
            drawMarks() { this.draw() },
            zoomOut() {
                if (this.owner) this.owner.callMethod('onZoom', this.S().s)
            },
            // 描き直しの予約(1コマに1回だけ描く)
            draw() {
                if (this.raf) return
                const go = () => { this.raf = 0; this.paint() }
                this.raf = (window.requestAnimationFrame || ((f) => setTimeout(f, 16)))(go)
            },
            // うまく描けなかったときに、原因を画面に出す(確認用)
            report(where, err) {
                const msg = where + ': ' + (err && err.message ? err.message : String(err))
                if (this.lastErr == msg) return
                this.lastErr = msg
                if (this.owner) this.owner.callMethod('onDebug', msg)
            },
            // ページの割合 → 画面(この枠の中の px)
            sx(x) { const s = this.S(); return s.left + s.tx + x * s.W * s.s },
            sy(y) { const s = this.S(); return s.top + s.ty + y * s.H * s.s },
            // 書き込みの位置(元の画像の上の割合)→ 表示している紙の上の割合(画像編集で回しているとき)
            mp(q) { const n = this.rotN(); return n ? rotPt(q, n) : q },
            line(c, pts, hex, w, dash) {
                if (!pts || pts.length < 2) return
                c.beginPath()
                pts.forEach((q, i) => { const X = this.sx(q[0]), Y = this.sy(q[1]); i ? c.lineTo(X, Y) : c.moveTo(X, Y) })
                c.strokeStyle = hex; c.lineWidth = w; c.lineCap = 'round'; c.lineJoin = 'round'
                c.setLineDash(dash || [])
                c.stroke()
                c.setLineDash([])
            },
            // 選んでいる図形の青い点(ページの割合で返す)
            handles(m) {
                if (!m || m.type != 2 || !m.points) return []
                if (m.shape == 'line' || m.shape == 'arrow') return [{ k: 0, x: m.points[0][0], y: m.points[0][1] }, { k: 1, x: m.points[1][0], y: m.points[1][1] }]
                const b = bbox(m.points)
                return [{ k: 'tl', x: b.l, y: b.t }, { k: 'tr', x: b.r, y: b.t }, { k: 'bl', x: b.l, y: b.b }, { k: 'br', x: b.r, y: b.b }]
            },
            pinR() { return 0.018 * this.S().W * 1.4 * 1.5 }, // メモの丸の半径(拡大しても画面で同じ大きさ) [カスタムエイド 2026-10-25] 小さくて見にくかったので1.5倍(スマホの通知バッジくらい)
            paint() {
                try { this.paintImage(); this.paintMarks() } catch (err) { this.report('paint', err) }
            },
            fix() {
                const s = this.S()
                if (!isFinite(s.W) || !isFinite(s.left)) this.layout(false)
                if (!isFinite(s.s) || !isFinite(s.tx) || !isFinite(s.ty)) { s.s = 1; s.tx = 0; s.ty = 0 }
            },
            // 下の canvas:紙と画像(画像編集の回転もここで)
            paintImage() {
                const s = this.S()
                if (!s.cvI) return
                this.fix()
                const c = s.ctxI
                c.setTransform(s.dpr, 0, 0, s.dpr, 0, 0)
                c.clearRect(0, 0, s.SW, s.SH)
                const X0 = this.sx(0), Y0 = this.sy(0), PW = s.W * s.s, PH = s.H * s.s
                c.fillStyle = '#FBFAF6'
                c.fillRect(X0, Y0, PW, PH)
                if (!s.img) return
                const n = this.rotN()
                c.save()
                c.beginPath(); c.rect(0, 0, s.SW, s.SH); c.clip()
                c.imageSmoothingEnabled = true
                if (n) {
                    // 画像全体を置き、真ん中で回す(画面の外は canvas が切り捨てる)
                    c.translate(X0 + PW / 2, Y0 + PH / 2)
                    c.rotate(n * Math.PI / 2)
                    const dw = n % 2 ? PH : PW, dh = n % 2 ? PW : PH
                    c.drawImage(s.img, -dw / 2, -dh / 2, dw, dh)
                } else {
                    c.drawImage(s.img, X0, Y0, PW, PH)
                }
                c.restore()
            },
            // 上の canvas:書き込み・切り抜きの枠
            paintMarks() {
                const s = this.S()
                if (!s.cvM) return
                const c = s.ctxM, p = s.p, e = this.ed()
                c.setTransform(s.dpr, 0, 0, s.dpr, 0, 0)
                c.clearRect(0, 0, s.SW, s.SH)
                const PW = s.W * s.s
                s.boxes = {}
                const marks = s.marks || []
                const mp = (q) => this.mp(q)
                // 図形(下)
                marks.forEach((m) => {
                    if (m.type != 2 || !m.points) return
                    shapeLines({ shape: m.shape, width: m.width, points: m.points.map(mp) }, s.R).forEach((pts) => this.line(c, pts, m.hex, m.width * PW))
                })
                // 書いている途中の自由線
                if (s.drawing && s.drawing.length > 1) this.line(c, s.drawing, p.pen.hex, p.pen.width * PW)
                // 文字
                marks.forEach((m) => {
                    if (m.type != 1) return
                    const fs = m.size * PW
                    c.font = '700 ' + fs + 'px ' + FONT
                    c.textBaseline = 'top'
                    c.textAlign = 'left'
                    const lines = String(m.text || '').split('\n')
                    const q = mp([m.x, m.y])
                    const X = this.sx(q[0]), Y = this.sy(q[1]), lh = fs * 1.25
                    let w = 0
                    lines.forEach((t) => { w = Math.max(w, c.measureText(t).width) })
                    const box = { l: X, t: Y, r: X + w, b: Y + lh * lines.length }
                    s.boxes[m.id] = box
                    if (box.r < 0 || box.l > s.SW || box.b < 0 || box.t > s.SH) return
                    // 選んでいる文字は青い点線の枠。テンプレートの差し込み(tagBox)は、いつも枠を出す(選んでいるものは実線)
                    if (!e && (m.id == p.sel || p.tagBox)) {
                        const on = m.id == p.sel
                        c.fillStyle = on ? 'rgba(26,115,232,.18)' : 'rgba(26,115,232,.08)'
                        c.fillRect(box.l - 3, box.t - 3, box.r - box.l + 6, box.b - box.t + 6)
                        c.strokeStyle = '#1A73E8'; c.lineWidth = on && p.tagBox ? 2 : 1.5; c.setLineDash(on && p.tagBox ? [] : [4, 3])
                        c.strokeRect(box.l - 3, box.t - 3, box.r - box.l + 6, box.b - box.t + 6)
                        c.setLineDash([])
                    } else if (e && p.tagBox) {
                        c.strokeStyle = '#1A73E8'; c.lineWidth = 1.5; c.setLineDash([4, 3])
                        c.strokeRect(box.l - 3, box.t - 3, box.r - box.l + 6, box.b - box.t + 6)
                        c.setLineDash([])
                    }
                    if (m.hex == '#FFFFFF') { c.shadowColor = 'rgba(0,0,0,.8)'; c.shadowBlur = 3 }
                    c.fillStyle = m.hex
                    lines.forEach((t, i) => c.fillText(t, X, Y + i * lh + (lh - fs) / 2))
                    c.shadowBlur = 0; c.shadowColor = 'transparent'
                })
                // メモ(いちばん上)
                const r = this.pinR()
                marks.forEach((m) => {
                    if (m.type != 3) return
                    const q = mp([m.x, m.y])
                    const X = this.sx(q[0]), Y = this.sy(q[1])
                    s.boxes[m.id] = { cx: X, cy: Y, r }
                    if (X < -r * 3 || X > s.SW + r * 3 || Y < -r * 3 || Y > s.SH + r * 3) return
                    c.shadowColor = 'rgba(0,0,0,.45)'; c.shadowBlur = 4; c.shadowOffsetY = 1
                    rrect(c, X - r, Y - r, r * 2, r * 2, [r, r, r, 3])
                    c.fillStyle = m.id < 0 ? '#F7C04A' : '#F2A900'
                    c.fill()
                    c.shadowBlur = 0; c.shadowOffsetY = 0; c.shadowColor = 'transparent'
                    if (!e && m.id == p.sel) { c.strokeStyle = '#1A73E8'; c.lineWidth = 2; c.stroke() }
                    c.fillStyle = '#FFFFFF'
                    c.font = '700 ' + (r * 0.95) + 'px ' + FONT
                    c.textAlign = 'center'; c.textBaseline = 'middle'
                    c.fillText(String(m.pin), X, Y + 1)
                    if (m.replies) {
                        const t = String(m.replies)
                        c.font = '700 ' + (r * 0.6) + 'px ' + FONT
                        const bw = Math.max(r, c.measureText(t).width + 6)
                        const bx = X + r * 0.6, by = Y - r * 1.3
                        rrect(c, bx, by, bw, r, [r / 2, r / 2, r / 2, r / 2])
                        c.fillStyle = '#1A73E8'; c.fill()
                        c.fillStyle = '#FFFFFF'
                        c.fillText(t, bx + bw / 2, by + r / 2 + 1)
                    }
                    c.textAlign = 'left'; c.textBaseline = 'top'
                })
                // 選んでいる図形の枠と青い点(画面で同じ大きさ)。画像編集中は出さない
                const sel = !e && marks.find((m) => m.id == p.sel && m.type == 2 && m.points)
                if (sel) {
                    if (sel.shape != 'line' && sel.shape != 'arrow') {
                        const b = bbox(sel.points)
                        this.line(c, [[b.l, b.t], [b.r, b.t], [b.r, b.b], [b.l, b.b], [b.l, b.t]], '#1A73E8', 1.2, [5, 4])
                    }
                    this.handles(sel).forEach((h) => {
                        c.beginPath(); c.arc(this.sx(h.x), this.sy(h.y), 9, 0, Math.PI * 2)
                        c.fillStyle = '#1A73E8'; c.fill()
                        c.strokeStyle = '#FFFFFF'; c.lineWidth = 3; c.stroke()
                    })
                }
                // 画像編集:切り抜きの外を暗く、切り抜きのときは枠・線・角
                if (e) {
                    const k = s.crop || [0, 0, 1, 1]
                    const full = k[0] < 0.002 && k[1] < 0.002 && k[2] > 0.996 && k[3] > 0.996
                    const L = this.sx(k[0]), T = this.sy(k[1]), Rr = this.sx(k[0] + k[2]), B = this.sy(k[1] + k[3])
                    const X0 = this.sx(0), Y0 = this.sy(0), X1 = this.sx(1), Y1 = this.sy(1)
                    if (!full || e.tool == 'crop') {
                        c.fillStyle = 'rgba(0,0,0,.55)'
                        c.fillRect(X0, Y0, X1 - X0, T - Y0)
                        c.fillRect(X0, B, X1 - X0, Y1 - B)
                        c.fillRect(X0, T, L - X0, B - T)
                        c.fillRect(Rr, T, X1 - Rr, B - T)
                    }
                    if (e.tool == 'crop') {
                        c.strokeStyle = '#FFFFFF'; c.lineWidth = 2
                        c.strokeRect(L, T, Rr - L, B - T)
                        c.strokeStyle = 'rgba(255,255,255,.55)'; c.lineWidth = 1
                        for (let i = 1; i < 3; i++) {
                            const gx = L + (Rr - L) * i / 3, gy = T + (B - T) * i / 3
                            c.beginPath(); c.moveTo(gx, T); c.lineTo(gx, B); c.stroke()
                            c.beginPath(); c.moveTo(L, gy); c.lineTo(Rr, gy); c.stroke()
                        }
                        // 四隅は太い L 字、辺の真ん中は短い太線(写真アプリと同じ形)
                        c.strokeStyle = '#FFFFFF'; c.lineWidth = 5; c.lineCap = 'square'
                        const k2 = Math.min(26, (Rr - L) / 3, (B - T) / 3)
                        ;[[L, T, 1, 1], [Rr, T, -1, 1], [L, B, 1, -1], [Rr, B, -1, -1]].forEach((q) => {
                            c.beginPath(); c.moveTo(q[0] + q[2] * k2, q[1]); c.lineTo(q[0], q[1]); c.lineTo(q[0], q[1] + q[3] * k2); c.stroke()
                        })
                        const mh = Math.min(30, (Rr - L) / 4), mv = Math.min(30, (B - T) / 4)
                        c.beginPath(); c.moveTo((L + Rr) / 2 - mh / 2, T); c.lineTo((L + Rr) / 2 + mh / 2, T); c.stroke()
                        c.beginPath(); c.moveTo((L + Rr) / 2 - mh / 2, B); c.lineTo((L + Rr) / 2 + mh / 2, B); c.stroke()
                        c.beginPath(); c.moveTo(L, (T + B) / 2 - mv / 2); c.lineTo(L, (T + B) / 2 + mv / 2); c.stroke()
                        c.beginPath(); c.moveTo(Rr, (T + B) / 2 - mv / 2); c.lineTo(Rr, (T + B) / 2 + mv / 2); c.stroke()
                        c.lineCap = 'butt'
                    }
                }
            },
            // 枠の画面上の位置(取れないときは前の値)
            rect() {
                const s = this.S(), el = this.el()
                let L = NaN, T = NaN
                try { const r = el.getBoundingClientRect(); L = num(r.left, num(r.x, NaN)); T = num(r.top, num(r.y, NaN)) } catch (err) {}
                if (isFinite(L)) s.rcL = L
                if (isFinite(T)) s.rcT = T
                return { left: s.rcL, top: s.rcT }
            },
            // 画面の点 → ページに対する割合
            toPage(cx, cy) {
                const s = this.S()
                const rc = this.rect()
                const x = (num(cx, 0) - rc.left - s.left - s.tx) / (s.W * s.s)
                const y = (num(cy, 0) - rc.top - s.top - s.ty) / (s.H * s.s)
                return { x, y, inside: x >= 0 && x <= 1 && y >= 0 && y <= 1 }
            },
            // 押した所にある書き込み(上にあるものから)。青い点なら {h}
            hit(cx, cy) {
                const s = this.S()
                const q = this.toPage(cx, cy)
                const rc = this.rect()
                const px = cx - rc.left, py = cy - rc.top
                const tol = 16 / (s.W * s.s) // 画面で 16px
                const marks = s.marks || []
                const sel = marks.find((m) => m.id == s.p.sel)
                if (sel) {
                    const h = this.handles(sel).find((h) => Math.hypot(h.x - q.x, (h.y - q.y) * s.R) < tol * 1.4)
                    if (h) return { m: sel, h: h.k }
                }
                const boxes = s.boxes || {}
                // メモ → 文字(上に描いているものから)
                const top = marks.filter((m) => m.type == 3).reverse().concat(marks.filter((m) => m.type == 1).reverse())
                for (const m of top) {
                    const b = boxes[m.id]
                    if (!b) continue
                    if (m.type == 3 && Math.hypot(px - b.cx, py - b.cy) < b.r + 6) return { m }
                    if (m.type == 1 && px > b.l - 6 && px < b.r + 6 && py > b.t - 6 && py < b.b + 6) return { m }
                }
                const list = marks.filter((m) => m.type == 2 && m.points).reverse()
                for (const m of list) {
                    if (m.shape == 'free' || !m.shape) {
                        for (let i = 1; i < m.points.length; i++) if (segDist(q.x, q.y, m.points[i - 1], m.points[i], s.R) < tol) return { m }
                    } else if (m.shape == 'line' || m.shape == 'arrow') {
                        if (segDist(q.x, q.y, m.points[0], m.points[1], s.R) < tol) return { m }
                    } else {
                        const b = bbox(m.points)
                        if (q.x > b.l - tol && q.x < b.r + tol && q.y > b.t - tol / s.R && q.y < b.b + tol / s.R) return { m }
                    }
                }
                return null
            },
            clamp() {
                const s = this.S()
                if (!isFinite(s.s) || s.s <= 1.001) { s.s = 1; s.tx = 0; s.ty = 0; return }
                if (!isFinite(s.W) || !isFinite(s.left)) this.layout(false)
                if (!isFinite(s.tx) || !isFinite(s.ty)) {
                    this.report('pos', new Error('tx=' + s.tx + ' ty=' + s.ty))
                    s.tx = num(s.okTx, 0); s.ty = num(s.okTy, 0)
                }
                const SW = s.SW, SH = s.SH
                const w = s.W * s.s, h = s.H * s.s
                const minX = w > SW ? SW - w - s.left : (SW - w) / 2 - s.left, maxX = w > SW ? -s.left : minX
                const minY = h > SH ? SH - h - s.top : (SH - h) / 2 - s.top, maxY = h > SH ? -s.top : minY
                s.tx = Math.min(maxX, Math.max(minX, s.tx))
                s.ty = Math.min(maxY, Math.max(minY, s.ty))
                s.okTx = s.tx; s.okTy = s.ty
            },
            // 2回押しの拡大・等倍を 0.2秒で動かす
            animTo(ns, ntx, nty) {
                const s = this.S()
                const a = { s: s.s, tx: s.tx, ty: s.ty }, t0 = Date.now()
                const step = () => {
                    if (!s.cvI) return
                    const k = Math.min(1, (Date.now() - t0) / 200), e = 1 - Math.pow(1 - k, 3)
                    s.s = a.s + (ns - a.s) * e; s.tx = a.tx + (ntx - a.tx) * e; s.ty = a.ty + (nty - a.ty) * e
                    this.paint()
                    if (k < 1) (window.requestAnimationFrame || ((f) => setTimeout(f, 16)))(step)
                    else this.zoomOut()
                }
                step()
            },
            bind(el) {
                this.bound = true
                const opt = { passive: false }
                const T = (fn) => (e) => { this.lastTouch = Date.now(); fn(e) }
                el.addEventListener('touchstart', T((e) => this.ts(e)), opt)
                // 動かしている間・離したことは画面全体で受ける
                document.addEventListener('touchmove', T((e) => { if (this.kvS && this.kvS.g) this.tm(e) }), opt)
                document.addEventListener('touchend', T((e) => { if (this.kvS && this.kvS.g) this.te(e) }), opt)
                document.addEventListener('touchcancel', T((e) => { if (this.kvS && this.kvS.g) this.te(e) }), opt)
                // パソコンのブラウザで確かめるとき用(マウス)
                let down = false
                el.addEventListener('mousedown', (e) => { if (Date.now() - (this.lastTouch || 0) < 1000) return; down = true; this.ts({ touches: [e], preventDefault() {} }) })
                window.addEventListener('mousemove', (e) => { if (down) this.tm({ touches: [e], preventDefault() {} }) })
                window.addEventListener('mouseup', (e) => { if (down) { down = false; this.te({ touches: [], changedTouches: [e], preventDefault() {} }) } })
            },
            // 切り抜きの枠のどこを押したか(角・辺・中・外)
            cropHit(q) {
                const s = this.S(), k = s.crop || [0, 0, 1, 1]
                const tx = 32 / (s.W * s.s), ty = 32 / (s.H * s.s) // つかめる範囲:画面で 32px
                const L = k[0], T = k[1], R = k[0] + k[2], B = k[1] + k[3]
                const nl = Math.abs(q.x - L) < tx, nr = Math.abs(q.x - R) < tx, nt = Math.abs(q.y - T) < ty, nb = Math.abs(q.y - B) < ty
                const inX = q.x > L - tx && q.x < R + tx, inY = q.y > T - ty && q.y < B + ty
                let h = ''
                if (nt && inX) h += 't'
                if (nb && inX) h += 'b'
                if (nl && inY) h += 'l'
                if (nr && inY) h += 'r'
                if (h) return h
                if (q.x > L && q.x < R && q.y > T && q.y < B) return 'move'
                return ''
            },
            ts(e) {
                const s = this.S()
                if (!s.cvI) return
                e.preventDefault()
                const t = e.touches
                this.rect()
                if (t.length >= 2) {
                    // 2本指:拡大・移動(書いている途中・ドラッグの途中ならやめる)
                    if (s.drawing) s.drawing = null
                    if (s.g && s.g.kind == 'drag') s.marks = JSON.parse(JSON.stringify(s.p.marks || []))
                    if (s.g && s.g.kind == 'crop') s.crop = s.g.c0.slice()
                    const a = t[0], b = t[1]
                    const ax = num(a.clientX, 0), ay = num(a.clientY, 0), bx = num(b.clientX, 0), by = num(b.clientY, 0)
                    s.g = { kind: 'pinch', d: Math.hypot(ax - bx, ay - by) || 1, mx: (ax + bx) / 2, my: (ay + by) / 2, s0: num(s.s, 1), tx0: num(s.tx, 0), ty0: num(s.ty, 0) }
                    this.draw()
                    return
                }
                const a = t[0]
                const tool = s.p.tool
                const e2 = this.ed()
                s.g = { kind: 'one', x0: num(a.clientX, 0), y0: num(a.clientY, 0), tx0: num(s.tx, 0), ty0: num(s.ty, 0), t0: Date.now(), moved: false }
                // 画像編集:切り抜きのときは枠を動かす。ほかは紙を動かすだけ(書き込みには触らない)
                if (e2) {
                    if (e2.tool == 'crop') {
                        const q = this.toPage(a.clientX, a.clientY)
                        const h = this.cropHit(q)
                        if (h) { s.g.kind = 'crop'; s.g.h = h; s.g.q0 = q; s.g.c0 = (s.crop || [0, 0, 1, 1]).slice() }
                    }
                    return
                }
                // 書き込み(文字・図形・メモ)を押さえたら、道具に関係なくドラッグで動かす
                // (自由線で書いている最中は、上から書けるように、選んでいるものだけ動かす)
                const pen = tool == 'shape' && s.p.shape == 'free'
                const hit = this.hit(a.clientX, a.clientY)
                if (hit && (!pen || hit.m.id == s.p.sel)) {
                    s.g.kind = 'drag'
                    s.g.hit = hit
                    s.g.orig = JSON.parse(JSON.stringify(hit.m))
                    s.g.q0 = this.toPage(a.clientX, a.clientY)
                    return
                }
                if (pen) {
                    const q = this.toPage(a.clientX, a.clientY)
                    s.drawing = [[q.x, q.y]]
                }
            },
            tm(e) {
                const s = this.S()
                if (!s || !s.g) return
                e.preventDefault()
                const t = e.touches
                // 拡大の途中で指が1本になったら、残った指で紙を動かす形に切り替える(写真アプリと同じ)
                // [2026-10-24] これがないと、片方の指を先に離したあとの動きで位置が計算できなくなり、紙の左上に戻っていた
                if (s.g.kind == 'pinch' && t.length == 1) { this.toPan(t[0]); return }
                if (s.g.kind == 'pinch' && t.length >= 2) {
                    const a = t[0], b = t[1]
                    const ax = num(a.clientX, 0), ay = num(a.clientY, 0), bx = num(b.clientX, 0), by = num(b.clientY, 0)
                    const d = Math.hypot(ax - bx, ay - by)
                    const mx = (ax + bx) / 2, my = (ay + by) / 2
                    const ns = Math.min(MAXS, Math.max(1, s.g.s0 * d / s.g.d))
                    if (!isFinite(s.W) || !isFinite(s.left)) this.layout(false)
                    const rc = this.rect()
                    // 指の間の点が、拡大しても同じ場所にとどまるように
                    const ox = s.g.mx - rc.left - s.left, oy = s.g.my - rc.top - s.top
                    s.s = ns
                    s.tx = mx - rc.left - s.left - (ox - s.g.tx0) * (ns / s.g.s0)
                    s.ty = my - rc.top - s.top - (oy - s.g.ty0) * (ns / s.g.s0)
                    if (!isFinite(s.tx) || !isFinite(s.ty)) this.report('pinch', new Error('mx=' + mx + ' rcL=' + rc.left + ' L=' + s.left + ' tx0=' + s.g.tx0 + ' s0=' + s.g.s0))
                    this.clamp()
                    this.draw()
                    return
                }
                if (!t.length) return
                const a = t[0]
                const dxp = num(a.clientX, 0) - s.g.x0, dyp = num(a.clientY, 0) - s.g.y0
                if (Math.abs(dxp) > 6 || Math.abs(dyp) > 6) s.g.moved = true
                if (s.g.kind == 'crop') {
                    const q = this.toPage(a.clientX, a.clientY)
                    const dx = q.x - s.g.q0.x, dy = q.y - s.g.q0.y
                    const c = s.g.c0.slice(), h = s.g.h, MIN = 0.08
                    if (h == 'move') {
                        c[0] = Math.min(1 - c[2], Math.max(0, c[0] + dx))
                        c[1] = Math.min(1 - c[3], Math.max(0, c[1] + dy))
                    } else {
                        if (h.indexOf('l') != -1) { const nx = Math.min(c[0] + c[2] - MIN, Math.max(0, c[0] + dx)); c[2] += c[0] - nx; c[0] = nx }
                        if (h.indexOf('r') != -1) c[2] = Math.min(1 - c[0], Math.max(MIN, c[2] + dx))
                        if (h.indexOf('t') != -1) { const ny = Math.min(c[1] + c[3] - MIN, Math.max(0, c[1] + dy)); c[3] += c[1] - ny; c[1] = ny }
                        if (h.indexOf('b') != -1) c[3] = Math.min(1 - c[1], Math.max(MIN, c[3] + dy))
                    }
                    s.crop = c
                    this.draw()
                    return
                }
                if (s.g.kind == 'drag') {
                    if (!s.g.moved) return
                    const q = this.toPage(a.clientX, a.clientY)
                    const dx = q.x - s.g.q0.x, dy = q.y - s.g.q0.y
                    const o = s.g.orig, h = s.g.hit.h
                    const m = s.marks.find((x) => x.id == o.id)
                    if (!m) return
                    if (h === undefined) {
                        if (o.points) m.points = o.points.map((p) => [p[0] + dx, p[1] + dy])
                        else { m.x = o.x + dx; m.y = o.y + dy }
                    } else if (h === 0 || h === 1) {
                        m.points = o.points.map((p, i) => (i == h ? [q.x, q.y] : p))
                    } else {
                        // 四隅:枠の大きさを変える(自由線は中の点もいっしょに伸び縮み)
                        const b = bbox(o.points)
                        const nb = { l: b.l, t: b.t, r: b.r, b: b.b }
                        if (h.indexOf('l') != -1) nb.l = Math.min(q.x, b.r - 0.01)
                        if (h.indexOf('r') != -1) nb.r = Math.max(q.x, b.l + 0.01)
                        if (h.indexOf('t') != -1) nb.t = Math.min(q.y, b.b - 0.01)
                        if (h.indexOf('b') != -1) nb.b = Math.max(q.y, b.t + 0.01)
                        if (o.shape == 'check') {
                            // チェックは縦横比を変えず、大きさだけ変える
                            const ax = h.indexOf('l') != -1 ? b.r : b.l, ay = h.indexOf('t') != -1 ? b.b : b.t
                            const w0 = (b.r - b.l) || 1e-6, h0 = (b.b - b.t) || 1e-6
                            const k = Math.max(0.02 / Math.max(w0, h0), Math.abs(q.x - ax) / w0, Math.abs(q.y - ay) / h0)
                            const nw = w0 * k, nh = h0 * k
                            const l = h.indexOf('l') != -1 ? ax - nw : ax, tt = h.indexOf('t') != -1 ? ay - nh : ay
                            m.points = [[l, tt], [l + nw, tt + nh]]
                        } else if (o.shape == 'free' || !o.shape) {
                            const sx = (nb.r - nb.l) / ((b.r - b.l) || 1e-6), sy = (nb.b - nb.t) / ((b.b - b.t) || 1e-6)
                            m.points = o.points.map((p) => [nb.l + (p[0] - b.l) * sx, nb.t + (p[1] - b.t) * sy])
                        } else {
                            m.points = [[nb.l, nb.t], [nb.r, nb.b]]
                        }
                    }
                    this.draw()
                    return
                }
                if (s.drawing) {
                    const q = this.toPage(a.clientX, a.clientY)
                    const last = s.drawing[s.drawing.length - 1]
                    if (Math.hypot(q.x - last[0], q.y - last[1]) > 0.0015 / s.s) {
                        s.drawing.push([Math.min(1, Math.max(0, q.x)), Math.min(1, Math.max(0, q.y))])
                        this.draw()
                    }
                    return
                }
                if (s.s > 1.001) {
                    if (!isFinite(dxp) || !isFinite(dyp)) return
                    s.tx = s.g.tx0 + dxp
                    s.ty = s.g.ty0 + dyp
                    this.clamp()
                    this.draw()
                }
            },
            // 残った1本の指で、今の位置から紙を動かす(押しただけ・2回押しとは扱わない)
            toPan(a) {
                const s = this.S()
                s.g = { kind: 'one', x0: num(a.clientX, 0), y0: num(a.clientY, 0), tx0: num(s.tx, 0), ty0: num(s.ty, 0), t0: 0, moved: true, fromPinch: true }
            },
            te(e) {
                const s = this.S()
                if (!s || !s.g) return
                const g = s.g
                if (g.kind == 'pinch') {
                    if (e.touches && e.touches.length == 1) { this.toPan(e.touches[0]); this.zoomOut(); return }
                    if (!e.touches || e.touches.length == 0) { s.g = null; this.zoomOut() }
                    return
                }
                s.g = null
                const c = (e.changedTouches && e.changedTouches[0]) || {}
                if (g.kind == 'crop') {
                    const k = (s.crop || [0, 0, 1, 1]).map((v) => Math.round(v * 10000) / 10000)
                    if (this.owner) this.owner.callMethod('onCrop', k)
                    return
                }
                if (this.ed()) {
                    // 画像編集中は、書き込みを押しても何もしない
                    return
                }
                if (g.kind == 'drag') {
                    if (g.moved) {
                        const m = s.marks.find((x) => x.id == g.orig.id)
                        if (m) this.owner.callMethod('onMarkChange', m.points ? { id: m.id, points: m.points } : { id: m.id, x: m.x, y: m.y })
                    } else {
                        const q = this.toPage(g.x0, g.y0)
                        this.owner.callMethod('onTap', { x: q.x, y: q.y, mark: g.hit.m.id })
                    }
                    return
                }
                if (s.drawing) {
                    const pts = s.drawing
                    s.drawing = null
                    this.draw()
                    if (pts.length >= 2 && g.moved) {
                        this.owner.callMethod('onStroke', { points: pts.map((p) => [Math.round(p[0] * 100000) / 100000, Math.round(p[1] * 100000) / 100000]) })
                    } else {
                        const q = this.toPage(g.x0, g.y0)
                        const hit = this.hit(g.x0, g.y0)
                        if (hit) this.owner.callMethod('onTap', { x: q.x, y: q.y, mark: hit.m.id })
                    }
                    return
                }
                const dx = num(c.clientX, g.x0) - g.x0, dy = num(c.clientY, g.y0) - g.y0
                if (!g.moved && Date.now() - g.t0 < 400) {
                    const now = Date.now()
                    // 2回すばやく押したら 拡大 ⇔ 等倍(見るだけのとき)
                    if (!s.p.tool && this.lastTap && now - this.lastTap.t < 300 && Math.hypot(g.x0 - this.lastTap.x, g.y0 - this.lastTap.y) < 30) {
                        this.lastTap = null
                        if (s.s > 1.01) this.animTo(1, 0, 0)
                        else {
                            const rc = this.rect()
                            const ox = g.x0 - rc.left - s.left, oy = g.y0 - rc.top - s.top
                            const keep = { s: s.s, tx: s.tx, ty: s.ty }
                            s.s = 2.5; s.tx = ox - ox * 2.5; s.ty = oy - oy * 2.5
                            this.clamp()
                            const to = { s: s.s, tx: s.tx, ty: s.ty }
                            s.s = keep.s; s.tx = keep.tx; s.ty = keep.ty
                            this.animTo(to.s, to.tx, to.ty)
                        }
                        return
                    }
                    this.lastTap = { t: now, x: g.x0, y: g.y0 }
                    const hit = this.hit(g.x0, g.y0)
                    const q = this.toPage(g.x0, g.y0)
                    if (!q.inside && !hit) return
                    const fire = () => this.owner.callMethod('onTap', { x: Math.min(1, Math.max(0, q.x)), y: Math.min(1, Math.max(0, q.y)), mark: hit ? hit.m.id : 0 })
                    // 見るだけのときの1回押しは少し待つ(2回押しと区別するため)
                    if (!s.p.tool && !hit) setTimeout(() => { if (this.lastTap && this.lastTap.t == now) fire() }, 280)
                    else fire()
                    return
                }
                // 等倍のときの左右スワイプでページ送り(見るだけのとき)
                if (!s.p.tool && s.s <= 1.001 && Math.abs(dx) > 60 && Math.abs(dy) < 80) {
                    this.owner.callMethod('onSwipe', dx < 0 ? 1 : -1)
                }
            }
        }
    }
</script>
<style lang="scss" scoped>
    .te { position: fixed; left: 0; top: 0; right: 0; bottom: 0; background: #1C1C1E; color: #FFFFFF; }
    .te-hover { opacity: 0.6; }
    .te-nav { position: absolute; left: 0; right: 0; top: 0; height: 52px; display: flex; align-items: center; justify-content: center; background: rgba(28, 28, 30, 0.94); z-index: 3; box-sizing: content-box; }
    .te-x { position: absolute; left: 14px; bottom: 9px; width: 34px; height: 34px; border-radius: 50%; background: #3A3A3C; display: flex; align-items: center; justify-content: center; font-size: 15px; }
    .te-ok { position: absolute; right: 14px; bottom: 9px; height: 34px; padding: 0 16px; border-radius: 17px; background: #1A73E8; display: flex; align-items: center; font-size: 15px; font-weight: 700; }
    .te-ok.off { opacity: 0.5; }
    .te-t { font-size: 16px; font-weight: 700; display: flex; flex-direction: column; align-items: center; }
    .te-s { font-size: 11px; font-weight: 400; color: #AEAEB2; }
    .te-chip { position: absolute; right: 12px; z-index: 4; background: rgba(0, 0, 0, 0.6); color: #FFFFFF; font-size: 12px; border-radius: 12px; padding: 4px 10px; }
    /* 紙を表示する場所(下のパネルの高さぶん空ける) */
    .te-stage { position: absolute; left: 0; right: 0; bottom: calc(272px + env(safe-area-inset-bottom)); overflow: hidden; touch-action: none; }
    /* 画像編集中は下のパネルを低くして、紙を表示する場所を広げる */
    .te-stage.ed { bottom: calc(204px + env(safe-area-inset-bottom)); }
    .te-stage.eda { bottom: calc(244px + env(safe-area-inset-bottom)); }
    .te-bot.ed { height: calc(204px + env(safe-area-inset-bottom)); }
    .te-bot.eda { height: calc(244px + env(safe-area-inset-bottom)); }
    .te-bot { position: absolute; left: 0; right: 0; bottom: 0; height: calc(272px + env(safe-area-inset-bottom)); box-sizing: border-box; background: #1C1C1E; border-top: 1px solid #3A3A3C; padding: 32rpx 32rpx calc(40rpx + env(safe-area-inset-bottom)); z-index: 3; }
    /* 差し込み/画像編集の切り替え(選んでいる側は、薄い青+青い線) */
    .te-seg { display: flex; padding: 8rpx; border-radius: 24rpx; background: #2C2C2E; }
    .te-sg { flex: 1; height: 80rpx; border-radius: 18rpx; display: flex; align-items: center; justify-content: center; font-size: 28rpx; color: #AEAEB2; box-sizing: border-box; }
    .te-sg.on { background: rgba(76, 155, 255, 0.16); border: 1.5px solid #4C9BFF; color: #FFFFFF; font-weight: 700; }
    .te-tip { margin-top: 32rpx; font-size: 26rpx; color: #AEAEB2; text-align: center; }
    .te-chips { margin-top: 32rpx; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16rpx; }
    .te-tg { height: 88rpx; border-radius: 20rpx; background: #3A3A3C; display: flex; align-items: center; justify-content: center; font-size: 28rpx; color: #FFFFFF; box-sizing: border-box; }
    .te-tg.on { background: rgba(76, 155, 255, 0.16); border: 1.5px solid #4C9BFF; font-weight: 700; }
    .te-ck { margin-left: 8rpx; font-size: 22rpx; color: #4C9BFF; }
    .te-card { margin-top: 32rpx; padding: 24rpx 28rpx; border-radius: 28rpx; background: #2C2C2E; }
    .te-row { display: flex; align-items: center; min-height: 72rpx; }
    .te-row + .te-row { margin-top: 12rpx; }
    .te-cn { flex: 1; font-size: 30rpx; font-weight: 700; }
    .te-pl { width: 96rpx; color: #AEAEB2; font-size: 24rpx; }
    .te-step { display: flex; align-items: center; background: #3A3A3C; border-radius: 20rpx; height: 80rpx; }
    .te-sb { width: 88rpx; text-align: center; font-size: 36rpx; }
    .te-sv { min-width: 72rpx; text-align: center; font-size: 30rpx; font-weight: 700; }
    .te-cl { width: 64rpx; height: 64rpx; border-radius: 32rpx; border: 2px solid #48484A; margin-right: 24rpx; box-sizing: border-box; }
    .te-cl.on { border: 3px solid #4C9BFF; }
    .te-del { height: 72rpx; padding: 0 28rpx; border-radius: 36rpx; background: #3A3A3C; color: #FF6B60; font-size: 26rpx; display: flex; align-items: center; }
    /* 名前・メモのシート */
    .te-in { padding: 0 32rpx; color: #1D1D1F; }
    .te-lbl { margin-top: 40rpx; font-size: 26rpx; font-weight: 700; line-height: 1.2; margin-bottom: 8rpx; }
    .te-req { color: #D93025; margin-left: 4rpx; }
    .te-ip { height: 88rpx; border: 1px solid #D1D1D6; border-radius: 16rpx; padding: 0 24rpx; font-size: 30rpx; background: #FFFFFF; }
    .te-btn1 { margin: 64rpx 32rpx 0; height: 104rpx; border-radius: 24rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .te-btn1.off { opacity: 0.5; }
    .te-del2 { margin: 32rpx 32rpx 48rpx; height: 96rpx; display: flex; align-items: center; justify-content: center; color: #D93025; font-size: 28rpx; }
    /* [カスタムエイド 2026-10-24] 画像編集(同じ画面のまま) */
    .te-eadj { margin-top: 16rpx; }
    .te-esl { display: flex; align-items: center; height: 72rpx; }
    .te-esl-l { width: 160rpx; font-size: 24rpx; color: #E5E5EA; }
    .te-eslider { flex: 1; margin: 0 12rpx; }
    .te-esl-v { width: 64rpx; text-align: right; font-size: 24rpx; color: #AEAEB2; }
    .te-etools { margin-top: 24rpx; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16rpx; }
    .te-et { height: 112rpx; border-radius: 24rpx; background: #3A3A3C; color: #E5E5EA; font-size: 22rpx; display: flex; flex-direction: column; align-items: center; justify-content: center; box-sizing: border-box; }
    .te-et.on { background: rgba(76, 155, 255, 0.16); border: 1.5px solid #4C9BFF; color: #FFFFFF; font-weight: 700; }
    .te-et.dim { opacity: 0.4; }
    .te-ei { font-size: 36rpx; line-height: 1; margin-bottom: 10rpx; }
</style>
