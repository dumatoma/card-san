<template>
    <!-- [カスタムエイド 2026-10-20] 新しい色えらびパネル(モック「管理App メンバーズカード作成 改善モック」の「新しい色えらび」)
         ・マイカラー:+ で今の色をお店に保存(次回も出る)。長押しで削除。保存先は今までと同じ(お店情報の color)
         ・お店の画像から:TOP画像1枚目から、よく使われている色を取り出す
         ・おすすめの色・色コード入力・3本のつまみ(色あい・あざやかさ・明るさ) -->
    <u-popup :show="show" round="20" :closeOnClickOverlay="true" @close="$emit('close')" @open="onOpen">
        <view class="cp">
            <view class="cp-grip"></view>
            <view class="cp-head">
                <view class="cp-cancel" @click="$emit('close')">キャンセル</view>
                <view class="cp-title">{{title}}</view>
                <view class="cp-ok" @click="ok">決定</view>
            </view>
            <!-- [カスタムエイド 2026-10-20] いまの色と「細かく調整」は、上に止めておく(下の色を選んでいる間も、調整している間も、いまの色がいつも見えるように) -->
            <view class="cp-top">
                <view class="cp-cur">
                    <!-- [カスタムエイド 2026-10-20] いまの色を押すと、細かく調整を出す/隠す -->
                    <view class="cp-cur-chip" :class="{ on: adjOpen }" :style="{ background: cur }" @click="toggleAdj"></view>
                    <view class="cp-cur-right">
                        <view class="cp-cur-label">
                            <text class="cp-small">色コード(#から始まる6けた)</text>
                        </view>
                        <view class="cp-hex-row">
                            <input class="cp-hex" :class="{ on: hexFocus }" v-model="hexText" maxlength="7"
                                @focus="hexFocus = true" @blur="hexBlur" @confirm="hexBlur" />
                        </view>
                    </view>
                </view>
                <!-- 細かく調整(ふだんは隠す。いまの色を押すと、そのすぐ下に出る) -->
                <view class="cp-adj" v-if="adjOpen">
                    <view class="cp-sl" v-for="s in sliders" :key="s.key">
                        <text class="cp-sl-label">{{s.label}}</text>
                        <view class="cp-track" :id="'cpTrack_' + s.key" :style="{ background: s.bg }"
                            @touchstart="slide(s.key, $event)" @touchmove.stop.prevent="slide(s.key, $event)">
                            <view class="cp-thumb" :style="{ left: s.pos + '%' }"></view>
                        </view>
                    </view>
                </view>
            </view>
            <scroll-view class="cp-body" scroll-y>
                <!-- マイカラー -->
                <view class="cp-sec">
                    <view class="cp-sec-head">
                        <text class="cp-h">マイカラー</text>
                        <text class="cp-small">お店に保存され、次回もここに出ます</text>
                    </view>
                    <view class="cp-grid6">
                        <view class="cp-dot" v-for="(c, i) in mine" :key="'m' + i" :class="{ sel: same(c, cur) }"
                            :style="{ background: c }" @click="pick(c)" @longpress="removeMine(i)"></view>
                        <view class="cp-dot cp-add" v-if="mine.length < MAX" @click="saveMine">
                            <text>+</text>
                        </view>
                    </view>
                    <view class="cp-small">+ で、いまの色を保存({{MAX}}色まで)。長押しで削除。</view>
                </view>

                <!-- お店の画像から -->
                <view class="cp-sec" v-if="imgColors.length">
                    <text class="cp-h">お店の画像から</text>
                    <view class="cp-row">
                        <image class="cp-src" :src="srcImage" mode="aspectFill"></image>
                        <view class="cp-dot cp-dot-sm" v-for="(c, i) in imgColors" :key="'i' + i"
                            :class="{ sel: same(c, cur) }" :style="{ background: c }" @click="pick(c)"></view>
                    </view>
                    <view class="cp-small">TOP画像1枚目から、よく使われている色を取り出しました</view>
                </view>

                <!-- おすすめ -->
                <view class="cp-sec">
                    <text class="cp-h">おすすめの色</text>
                    <view class="cp-grid8">
                        <view class="cp-dot" v-for="(c, i) in PRESET" :key="'p' + i" :class="{ sel: same(c, cur) }"
                            :style="{ background: c }" @click="pick(c)"></view>
                    </view>
                </view>

                <view style="height: 40rpx;"></view>
            </scroll-view>
        </view>
        <!-- 画像の色を取り出すための見えないキャンバス -->
        <canvas canvas-id="csCpCanvas" id="csCpCanvas" class="cp-canvas"></canvas>
    </u-popup>
</template>

<script>
    import {
        getShopInfo,
        settingDiyColor
    } from '@/api/index.js'

    function clamp(v, a, b) {
        return Math.min(b, Math.max(a, v))
    }

    // [カスタムエイド 2026-10-21] 最初のマイカラー(白・黒)と、以前の初期の色(これだけのままなら初期状態とみなす)
    const DEFAULT_MINE = ['#FFFFFF', '#000000']
    const LEGACY = ['#FEECD4', '#B1DD8B', '#EAF28F', '#FFC5AB', '#B51A00', '#74A7FF', '#BE38F3', '#00A1D8', '#0042A9', '#FFAC02']
    const LEGACY_MIN = 8
    export default {
        props: {
            show: { type: Boolean, default: false },
            title: { type: String, default: '色を選ぶ' },
            value: { type: String, default: '#D93025' }
        },
        data() {
            return {
                MAX: 12,
                PRESET: [
                    '#FFFFFF', '#F5F5F7', '#D2D2D7', '#AEAEB2', '#6E6E73', '#3A3A3C', '#1D1D1F', '#000000',
                    '#FDE2E4', '#FEECD4', '#FFF6CC', '#E2F5D8', '#D6F1EF', '#DCEBFB', '#E6E0F8', '#F6DDEB',
                    '#F28B82', '#FBBC77', '#FDD663', '#81C995', '#5BC8C0', '#78A9F0', '#A794E6', '#F08DB8',
                    '#D93025', '#E8710A', '#C9A100', '#188038', '#00897B', '#1A73E8', '#6A3DC4', '#B0306A'
                ],
                cur: '#D93025',
                hexText: '#D93025',
                hexFocus: false,
                h: 0,
                s: 0,
                l: 0,
                mineRaw: [], // お店に保存されている形のまま([{color, selected}])
                imgColors: [],
                srcImage: '',
                loaded: false,
                rects: {},
                adjOpen: false // [カスタムエイド 2026-10-20] 細かく調整を出しているか
            }
        },
        computed: {
            mine() {
                return this.mineRaw.map((v) => this.norm(v && v.color ? v.color : v)).filter((v) => v)
            },
            sliders() {
                return [{
                        key: 'h',
                        label: '色あい',
                        pos: this.h / 360 * 100,
                        bg: 'linear-gradient(90deg, #ff0000, #ffff00, #00ff00, #00ffff, #0000ff, #ff00ff, #ff0000)'
                    },
                    {
                        key: 's',
                        label: 'あざやかさ',
                        pos: this.s,
                        bg: 'linear-gradient(90deg, ' + this.hex(this.h, 0, this.l) + ', ' + this.hex(this.h, 100, this.l) + ')'
                    },
                    {
                        key: 'l',
                        label: '明るさ',
                        pos: this.l,
                        bg: 'linear-gradient(90deg, #000000, ' + this.hex(this.h, this.s, 50) + ', #ffffff)'
                    }
                ]
            }
        },
        methods: {
            onOpen() {
                this.setColor(this.value || '#D93025')
                this.rects = {}
                this.adjOpen = false // [カスタムエイド 2026-10-20] 開いたときは、細かく調整を隠しておく
                if (!this.loaded) this.load()
            },
            // [カスタムエイド 2026-10-20] 細かく調整を出す/隠す。出したら、つまみの位置を測る
            openAdj() {
                this.adjOpen = true
                this.$nextTick(() => setTimeout(() => this.measure(), 60))
            },
            toggleAdj() {
                if (this.adjOpen) this.adjOpen = false
                else this.openAdj()
            },
            load() {
                getShopInfo().then((res) => {
                    if (res.code != 200) return
                    this.loaded = true
                    const info = res.data.shop_info || {}
                    // [カスタムエイド 2026-10-21] 最初のマイカラーは白・黒の2色だけ。
                    // まだ何も保存していないお店と、以前の初期の色(LEGACY)のまま変えていないお店は、白・黒を出す(保存は + か長押しをしたとき)
                    const saved = Array.isArray(info.color) ? info.color : []
                    this.mineRaw = this.isLegacy(saved) ? DEFAULT_MINE.map((c) => ({ color: c, selected: false })) : saved
                    const img = Array.isArray(info.carousel) && info.carousel.length ? info.carousel[0] : ''
                    if (img) this.extract(img)
                }).catch(() => {})
            },
            // [カスタムエイド 2026-10-21] 保存されている色が、空か、以前の初期の色だけでできているか
            isLegacy(list) {
                if (!list.length) return true
                const cs = list.map((v) => this.norm(v && v.color ? v.color : v)).filter((v) => v)
                if (cs.length < LEGACY_MIN) return false
                return cs.every((c) => LEGACY.indexOf(c) > -1)
            },
            measure() {
                ['h', 's', 'l'].forEach((k) => {
                    uni.createSelectorQuery().in(this).select('#cpTrack_' + k).boundingClientRect((r) => {
                        if (r) this.$set(this.rects, k, r)
                    }).exec()
                })
            },
            norm(c) {
                if (!c) return ''
                c = String(c).trim()
                const m = /^#?([0-9a-fA-F]{6})$/.exec(c)
                if (m) return '#' + m[1].toUpperCase()
                const r = /rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/.exec(c)
                if (r) return this.rgbHex(+r[1], +r[2], +r[3])
                return ''
            },
            same(a, b) {
                return this.norm(a) == this.norm(b)
            },
            rgbHex(r, g, b) {
                const t = (x) => ('0' + clamp(Math.round(x), 0, 255).toString(16)).slice(-2)
                return ('#' + t(r) + t(g) + t(b)).toUpperCase()
            },
            hex(h, s, l) {
                s /= 100
                l /= 100
                const k = (n) => (n + h / 30) % 12
                const a = s * Math.min(l, 1 - l)
                const f = (n) => l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)))
                return this.rgbHex(f(0) * 255, f(8) * 255, f(4) * 255)
            },
            hsl(hex) {
                const n = parseInt(hex.slice(1), 16)
                const r = (n >> 16) / 255,
                    g = ((n >> 8) & 255) / 255,
                    b = (n & 255) / 255
                const mx = Math.max(r, g, b),
                    mn = Math.min(r, g, b)
                let h = 0,
                    s = 0
                const l = (mx + mn) / 2
                if (mx != mn) {
                    const d = mx - mn
                    s = l > 0.5 ? d / (2 - mx - mn) : d / (mx + mn)
                    h = mx == r ? (g - b) / d + (g < b ? 6 : 0) : mx == g ? (b - r) / d + 2 : (r - g) / d + 4
                    h *= 60
                }
                return { h: Math.round(h), s: Math.round(s * 100), l: Math.round(l * 100) }
            },
            setColor(c) {
                const v = this.norm(c)
                if (!v) return false
                const x = this.hsl(v)
                this.cur = v
                this.hexText = v
                this.h = x.h
                this.s = x.s
                this.l = x.l
                return true
            },
            pick(c) {
                this.setColor(c)
            },
            hexBlur() {
                this.hexFocus = false
                let v = String(this.hexText || '').trim()
                if (v && v[0] != '#') v = '#' + v
                if (!this.setColor(v)) {
                    this.hexText = this.cur
                    uni.showToast({ title: '#と6けたの英数字で入力してください', icon: 'none' })
                }
            },
            slide(key, e) {
                const t = (e.touches && e.touches[0]) || (e.changedTouches && e.changedTouches[0])
                const r = this.rects[key]
                if (!t || !r) {
                    if (!r) this.measure()
                    return
                }
                const p = clamp((t.clientX - r.left) / r.width, 0, 1)
                if (key == 'h') this.h = Math.round(p * 360)
                if (key == 's') this.s = Math.round(p * 100)
                if (key == 'l') this.l = Math.round(p * 100)
                this.cur = this.hex(this.h, this.s, this.l)
                this.hexText = this.cur
            },
            saveMine() {
                if (this.mine.indexOf(this.cur) > -1) {
                    uni.showToast({ title: 'この色はもう保存されています', icon: 'none' })
                    return
                }
                const list = this.mineRaw.concat([{ color: this.cur, selected: false }])
                this.storeMine(list, '保存しました')
            },
            removeMine(i) {
                uni.showModal({
                    title: 'マイカラーから削除しますか?',
                    confirmText: '削除',
                    cancelText: 'キャンセル',
                    success: (r) => {
                        if (!r.confirm) return
                        const list = this.mineRaw.slice()
                        list.splice(i, 1)
                        this.storeMine(list, '削除しました')
                    }
                })
            },
            storeMine(list, msg) {
                const old = this.mineRaw
                this.mineRaw = list
                settingDiyColor({ type: 'color', color: list }).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({ title: msg, icon: 'none' })
                    } else {
                        this.mineRaw = old
                        uni.showToast({ title: res.message || '保存できませんでした', icon: 'none' })
                    }
                }).catch(() => {
                    this.mineRaw = old
                })
            },
            // TOP画像1枚目を小さく描いて、よく使われている色を5つ取り出す
            extract(url) {
                this.srcImage = url
                uni.getImageInfo({
                    src: url,
                    success: (info) => {
                        const W = 40,
                            H = 40
                        const ctx = uni.createCanvasContext('csCpCanvas', this)
                        ctx.drawImage(info.path, 0, 0, W, H)
                        ctx.draw(false, () => {
                            setTimeout(() => {
                                uni.canvasGetImageData({
                                    canvasId: 'csCpCanvas',
                                    x: 0,
                                    y: 0,
                                    width: W,
                                    height: H,
                                    success: (r) => {
                                        this.imgColors = this.topColors(r.data)
                                    }
                                }, this)
                            }, 100)
                        })
                    }
                })
            },
            topColors(d) {
                const box = {}
                for (let i = 0; i < d.length; i += 4) {
                    if (d[i + 3] < 128) continue
                    const k = (d[i] >> 4) + ',' + (d[i + 1] >> 4) + ',' + (d[i + 2] >> 4)
                    const b = box[k] || (box[k] = { n: 0, r: 0, g: 0, b: 0 })
                    b.n++
                    b.r += d[i]
                    b.g += d[i + 1]
                    b.b += d[i + 2]
                }
                const list = Object.keys(box).map((k) => box[k]).sort((a, b) => b.n - a.n)
                const out = []
                for (const b of list) {
                    const c = [b.r / b.n, b.g / b.n, b.b / b.n]
                    if (out.some((o) => Math.abs(o[0] - c[0]) + Math.abs(o[1] - c[1]) + Math.abs(o[2] - c[2]) < 60)) continue
                    out.push(c)
                    if (out.length >= 5) break
                }
                return out.map((c) => this.rgbHex(c[0], c[1], c[2]))
            },
            ok() {
                if (this.hexFocus) this.hexBlur()
                this.$emit('confirm', this.cur)
            }
        }
    }
</script>

<style lang="scss" scoped>
    .cp {
        /* [カスタムエイド 2026-10-20] 少し高く(82vh → 84vh)。後ろの「色/アイコン」が半分かくれるくらいまで */
        height: 84vh;
        display: flex;
        flex-direction: column;
        background: #FFFFFF;
        border-radius: 40rpx 40rpx 0 0;
        font-family: 'Hiragino Sans', sans-serif;
        color: #1D1D1F;
    }

    .cp-grip {
        width: 72rpx;
        height: 10rpx;
        border-radius: 6rpx;
        background: #D2D2D7;
        margin: 16rpx auto 0;
    }

    .cp-head {
        height: 96rpx;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16rpx;
    }

    .cp-cancel,
    .cp-ok {
        height: 88rpx;
        line-height: 88rpx;
        padding: 0 20rpx;
        font-size: 32rpx;
    }

    .cp-cancel {
        color: #515154;
    }

    .cp-ok {
        color: #1A73E8;
        font-weight: 700;
    }

    .cp-title {
        font-size: 34rpx;
        font-weight: 700;
    }

    .cp-body {
        flex: 1;
        height: 0;
        padding: 32rpx 32rpx 0;
        box-sizing: border-box;
    }

    /* [カスタムエイド 2026-10-20] 上に止めておく所(いまの色・色コード・細かく調整) */
    .cp-top {
        flex-shrink: 0;
        padding: 8rpx 32rpx 24rpx;
        border-bottom: 1px solid #E5E5EA;
        background: #FFFFFF;
    }

    .cp-cur {
        display: flex;
        align-items: center;
    }

    .cp-cur-label {
        display: flex;
        align-items: center;
    }

    .cp-hex-row {
        display: flex;
        align-items: center;
        margin-top: 12rpx;
    }

    .cp-adj {
        margin-top: 24rpx;
    }

    /* [カスタムエイド 2026-10-20] 調整を出しているときは、いまの色に青い枠 */
    .cp-cur-chip.on {
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.08), 0 0 0 4rpx #FFFFFF, 0 0 0 8rpx #1A73E8;
    }

    .cp-cur-chip {
        width: 152rpx;
        height: 152rpx;
        flex-shrink: 0;
        border-radius: 32rpx;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.08);
        margin-right: 24rpx;
    }

    .cp-cur-right {
        flex: 1;
        min-width: 0;
    }

    .cp-hex {
        flex: 1;
        min-width: 0;
        height: 88rpx;
        padding: 0 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        font-size: 32rpx;
        font-weight: 500;
        letter-spacing: 0.04em;
        box-sizing: border-box;
    }

    .cp-hex.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    .cp-small {
        font-size: 24rpx;
        color: #6E6E73;
        line-height: 1.6;
    }

    .cp-sec {
        margin-bottom: 40rpx;
    }

    .cp-line {
        border-top: 1px solid #E5E5EA;
        padding-top: 32rpx;
    }

    .cp-sec-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
    }

    .cp-h {
        display: block;
        font-size: 30rpx;
        font-weight: 700;
        margin-bottom: 20rpx;
    }

    .cp-grid6,
    .cp-grid8 {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 12rpx;
    }

    .cp-dot {
        position: relative;
        border-radius: 50%;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.1);
    }

    .cp-grid6 .cp-dot {
        width: calc((100% - 100rpx) / 6);
        height: 0;
        padding-bottom: calc((100% - 100rpx) / 6);
        margin: 0 20rpx 20rpx 0;
    }

    .cp-grid6 .cp-dot:nth-child(6n) {
        margin-right: 0;
    }

    .cp-grid8 .cp-dot {
        width: calc((100% - 112rpx) / 8);
        height: 0;
        padding-bottom: calc((100% - 112rpx) / 8);
        margin: 0 16rpx 16rpx 0;
    }

    .cp-grid8 .cp-dot:nth-child(8n) {
        margin-right: 0;
    }

    .cp-dot.sel {
        box-shadow: 0 0 0 4rpx #FFFFFF, 0 0 0 8rpx #1A73E8;
    }

    .cp-add {
        box-shadow: none;
        border: 3rpx dashed #8AB0EE;
        background: #F7FAFF;
        box-sizing: border-box;

        text {
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            text-align: center;
            color: #1A73E8;
            font-size: 40rpx;
            line-height: 1;
        }
    }

    .cp-row {
        display: flex;
        align-items: center;
        margin-bottom: 12rpx;
    }

    .cp-src {
        width: 108rpx;
        height: 72rpx;
        border-radius: 12rpx;
        flex-shrink: 0;
        margin-right: 24rpx;
        background: #E5E5EA;
    }

    .cp-dot-sm {
        width: 68rpx;
        height: 68rpx;
        margin-right: 20rpx;
    }

    /* [カスタムエイド 2026-10-20] 1本ずつ横ならび(名前・つまみ)にして、高さをおさえる */
    .cp-sl {
        display: flex;
        align-items: center;
        height: 72rpx;
    }

    .cp-sl-label {
        width: 150rpx;
        flex-shrink: 0;
        font-size: 24rpx;
        color: #6E6E73;
    }

    .cp-track {
        position: relative;
        flex: 1;
        height: 44rpx;
        border-radius: 22rpx;
        margin: 0 24rpx;
    }

    .cp-thumb {
        position: absolute;
        top: -6rpx;
        width: 56rpx;
        height: 56rpx;
        margin-left: -28rpx;
        border-radius: 50%;
        background: #FFFFFF;
        box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.35);
        box-sizing: border-box;
        border: 6rpx solid #FFFFFF;
    }

    .cp-canvas {
        position: fixed;
        left: -9999px;
        top: 0;
        width: 40px;
        height: 40px;
    }
</style>
