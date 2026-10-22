<template>
    <!-- [カスタムエイド 2026-10-21] 月のカレンダー(1日を選ぶ/期間を選ぶ)。
         ・年月は大きく、‹ › は直径 40px の丸いボタン
         ・左右にフリックしても前の月・次の月に切り替わる(少しスライドして見せる)
         ・選べない日(min より前・max より後)は薄く表示して押せない
         値はすべて 'YYYY-MM-DD' の文字列 -->
    <view class="cc">
        <view class="cc-h">
            <view class="cc-arrow" :class="{ off: !canPrev }" hover-class="cc-hover" @click="move(-1)">‹</view>
            <view class="cc-ym">{{ y }}年{{ m }}月</view>
            <view class="cc-arrow" :class="{ off: !canNext }" hover-class="cc-hover" @click="move(1)">›</view>
        </view>
        <view class="cc-w">
            <text v-for="w in weeks" :key="w" :class="{ sat: w == '土', sun: w == '日' }">{{ w }}</text>
        </view>
        <view class="cc-body" :class="anim" @touchstart="ts" @touchend="te">
            <view class="cc-grid">
                <view v-for="(d, i) in cells" :key="i" class="cc-cell" :class="cellClass(d)" @click="pick(d)">
                    <view class="cc-d" v-if="d">{{ d.day }}</view>
                </view>
            </view>
        </view>
    </view>
</template>

<script>
    const pad = (n) => (n < 10 ? '0' : '') + n
    const fmt = (dt) => dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate())
    export default {
        name: 'csCalendar',
        props: {
            mode: { type: String, default: 'single' }, // single / range
            value: { type: String, default: '' }, // single のとき
            from: { type: String, default: '' }, // range のとき
            to: { type: String, default: '' },
            min: { type: String, default: '' },
            max: { type: String, default: '' }
        },
        data() {
            const base = this.value || this.from || this.min || fmt(new Date())
            const p = base.split('-')
            return {
                weeks: ['月', '火', '水', '木', '金', '土', '日'],
                y: Number(p[0]),
                m: Number(p[1]),
                anim: '',
                sx: 0,
                sy: 0,
                today: fmt(new Date())
            }
        },
        computed: {
            cells() {
                const first = new Date(this.y, this.m - 1, 1)
                const lead = (first.getDay() + 6) % 7 // 月曜はじまり
                const days = new Date(this.y, this.m, 0).getDate()
                const arr = []
                for (let i = 0; i < lead; i++) arr.push(null)
                for (let d = 1; d <= days; d++) arr.push({ day: d, key: this.y + '-' + pad(this.m) + '-' + pad(d) })
                return arr
            },
            canPrev() {
                if (!this.min) return true
                return this.y * 12 + this.m > Number(this.min.slice(0, 4)) * 12 + Number(this.min.slice(5, 7))
            },
            canNext() {
                if (!this.max) return true
                return this.y * 12 + this.m < Number(this.max.slice(0, 4)) * 12 + Number(this.max.slice(5, 7))
            }
        },
        watch: {
            // 外から日付が変わったら(「明日」「先月」などのボタン)、その月を見せる
            value(v) { this.show(v) },
            from(v) { this.show(v) }
        },
        methods: {
            show(v) {
                if (!v) return
                this.y = Number(v.slice(0, 4))
                this.m = Number(v.slice(5, 7))
            },
            disabled(k) {
                return (this.min && k < this.min) || (this.max && k > this.max)
            },
            cellClass(d) {
                if (!d) return 'empty'
                const k = d.key
                const c = []
                if (this.disabled(k)) c.push('off')
                if (k == this.today) c.push('today')
                if (this.mode == 'single') {
                    if (k == this.value) c.push('sel')
                } else {
                    const a = this.from
                    const b = this.to || this.from
                    if (a && k >= a && k <= b) {
                        c.push('in')
                        if (k == a) c.push('start')
                        if (k == b) c.push('end')
                    }
                }
                return c.join(' ')
            },
            pick(d) {
                if (!d || this.disabled(d.key)) return
                if (this.mode == 'single') {
                    this.$emit('change', d.key)
                    return
                }
                // 期間:1回目ではじめの日、2回目でおわりの日(はじめより前を押したら、はじめの日を選び直す)
                if (!this.from || (this.from && this.to) || d.key < this.from) {
                    this.$emit('range', { from: d.key, to: '' })
                } else {
                    this.$emit('range', { from: this.from, to: d.key })
                }
            },
            move(n) {
                if ((n < 0 && !this.canPrev) || (n > 0 && !this.canNext)) return
                let m = this.m + n
                let y = this.y
                if (m < 1) { m = 12; y-- }
                if (m > 12) { m = 1; y++ }
                this.anim = n > 0 ? 'from-r' : 'from-l'
                this.y = y
                this.m = m
                setTimeout(() => { this.anim = '' }, 260)
            },
            ts(e) {
                const t = e.touches && e.touches[0]
                if (!t) return
                this.sx = t.clientX
                this.sy = t.clientY
            },
            te(e) {
                const t = e.changedTouches && e.changedTouches[0]
                if (!t) return
                const dx = t.clientX - this.sx
                const dy = t.clientY - this.sy
                // 横に 50px 以上、縦より大きく動かしたらフリックとみなす
                if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy) * 1.5) this.move(dx < 0 ? 1 : -1)
            }
        }
    }
</script>

<style lang="scss" scoped>
    .cc-h { display: flex; align-items: center; justify-content: space-between; margin: 8rpx 0 24rpx; }
    .cc-ym { font-size: 36rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; }
    .cc-arrow { width: 80rpx; height: 80rpx; border-radius: 50%; background: #F2F2F7; color: #1A73E8; font-size: 48rpx; line-height: 76rpx; text-align: center; font-family: 'Hiragino Sans-W5', sans-serif; }
    .cc-arrow.off { color: #C7C7CC; }
    .cc-hover { opacity: 0.6; }
    .cc-w { display: flex; margin-bottom: 8rpx; }
    .cc-w text { flex: 1; text-align: center; font-size: 22rpx; color: #8A8A8E; }
    .cc-w .sat { color: #1A73E8; }
    .cc-w .sun { color: #D93025; }
    .cc-body { overflow: hidden; }
    .cc-body.from-r .cc-grid { animation: ccR 0.26s ease-out; }
    .cc-body.from-l .cc-grid { animation: ccL 0.26s ease-out; }
    @keyframes ccR { from { transform: translateX(40%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes ccL { from { transform: translateX(-40%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    .cc-grid { display: flex; flex-wrap: wrap; }
    .cc-cell { width: 14.2857%; height: 80rpx; display: flex; align-items: center; justify-content: center; margin: 4rpx 0; }
    .cc-d { width: 72rpx; height: 72rpx; line-height: 72rpx; text-align: center; border-radius: 50%; font-size: 30rpx; color: #1D1D1F; }
    .cc-cell.off .cc-d { color: #C7C7CC; }
    .cc-cell.today .cc-d { color: #1A73E8; font-family: 'Hiragino Sans-W5', sans-serif; }
    .cc-cell.sel .cc-d { background: #1A73E8; color: #FFFFFF; font-family: 'Hiragino Sans-W5', sans-serif; }
    .cc-cell.in { background: #E8F0FE; }
    .cc-cell.in .cc-d { color: #1A73E8; }
    .cc-cell.start { border-radius: 40rpx 0 0 40rpx; }
    .cc-cell.end { border-radius: 0 40rpx 40rpx 0; }
    .cc-cell.start.end { border-radius: 40rpx; }
    .cc-cell.start .cc-d, .cc-cell.end .cc-d { background: #1A73E8; color: #FFFFFF; font-family: 'Hiragino Sans-W5', sans-serif; }
</style>
