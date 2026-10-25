<template>
    <!-- [カスタムエイド 2026-10-22] ドラムロール(今までの u-picker と同じ使い方で置きかえられる)。
         見た目:下から出る白い画面、上に見出しと右上の ×、選んでいる内容を大きく表示、ドラムロール、下に「決定」。
         使い方:columns(列ごとの配列)・keyName(項目が {..} のときの表示名)・defaultIndex・title・note(小さな説明)・sep(大きく出すときの区切り)
         イベント:confirm({ value, indexs })・change({ columnIndex, value, indexs })・cancel・close(u-picker と同じ)
         メソッド:setColumnValues(列, 値の配列)(u-picker と同じ) -->
    <u-popup :show="show" mode="bottom" round="16" :closeOnClickOverlay="closeOnClickOverlay" :safeAreaInsetBottom="false" @close="onClose">
        <view class="cp">
            <view class="cp-h">
                <text>{{ title || '選択してください' }}</text>
                <view class="cp-x" hover-class="cp-hover" @click="onCancel">✕</view>
            </view>
            <view class="cp-now">{{ preview }}</view>
            <picker-view class="cp-view" :value="idx" indicator-class="cp-ind" @change="onChange" :immediate-change="true">
                <picker-view-column v-for="(col, ci) in cols" :key="ci">
                    <view class="cp-item" v-for="(it, ii) in col" :key="ii">{{ labelOf(it) }}{{ units[ci] || '' }}</view>
                </picker-view-column>
            </picker-view>
            <view class="cp-note" v-if="note">{{ note }}</view>
            <view class="cp-btn" hover-class="cp-hover" @click="onConfirm">{{ confirmText && confirmText.trim() ? confirmText : '決定' }}</view>
        </view>
    </u-popup>
</template>

<script>
    export default {
        name: 'csPicker',
        props: {
            show: { type: Boolean, default: false },
            title: { type: String, default: '' },
            columns: { type: Array, default: () => [] },
            keyName: { type: String, default: 'label' },
            defaultIndex: { type: Array, default: () => [] },
            closeOnClickOverlay: { type: Boolean, default: true },
            confirmText: { type: String, default: '決定' },
            cancelText: { type: String, default: '' }, // u-picker と同じ書き方を受け付けるだけ(使わない)
            note: { type: String, default: '' },
            sep: { type: String, default: ' ' },
            units: { type: Array, default: () => [] } // 列ごとに後ろに付ける文字(例:['時', '分'])
        },
        data() {
            return { cols: [], idx: [] }
        },
        computed: {
            preview() {
                return this.cols.map((c, i) => this.labelOf(c[this.idx[i] || 0]) + (this.units[i] || '')).join(this.sep)
            }
        },
        watch: {
            columns: { immediate: true, handler(v) { this.cols = (v || []).map((c) => (c || []).slice()); this.fixIdx() } },
            defaultIndex: { immediate: true, handler(v) { this.idx = this.cols.map((c, i) => this.clamp((v || [])[i] || 0, c)) } },
            show(v) {
                // 開くたびに、渡された位置に合わせる
                if (v) this.idx = this.cols.map((c, i) => this.clamp((this.defaultIndex || [])[i] || 0, c))
            }
        },
        methods: {
            clamp(n, c) {
                n = Number(n) || 0
                return Math.max(0, Math.min(n, (c || []).length - 1))
            },
            fixIdx() {
                this.idx = this.cols.map((c, i) => this.clamp(this.idx[i] || 0, c))
            },
            labelOf(it) {
                if (it === undefined || it === null) return ''
                if (typeof it == 'object') return it[this.keyName] !== undefined ? it[this.keyName] : ''
                return it
            },
            values() {
                return this.cols.map((c, i) => c[this.idx[i] || 0])
            },
            onChange(e) {
                const v = e.detail.value.slice()
                let columnIndex = 0
                for (let i = 0; i < v.length; i++) {
                    if (v[i] !== this.idx[i]) { columnIndex = i; break }
                }
                this.idx = v.map((n, i) => this.clamp(n, this.cols[i]))
                this.$emit('change', { columnIndex, value: this.values(), indexs: this.idx.slice(), index: this.idx[columnIndex] })
            },
            setColumnValues(ci, values) {
                this.$set(this.cols, ci, (values || []).slice())
                this.fixIdx()
            },
            onConfirm() {
                this.$emit('confirm', { value: this.values(), indexs: this.idx.slice() })
            },
            onCancel() {
                this.$emit('cancel')
                this.$emit('close')
            },
            onClose() {
                this.$emit('close')
            }
        }
    }
</script>

<style lang="scss">
    .cp { background: #FFFFFF; border-radius: 32rpx 32rpx 0 0; padding-bottom: calc(48rpx + env(safe-area-inset-bottom)); }
    .cp-h { position: relative; height: 112rpx; line-height: 112rpx; text-align: center; font-size: 34rpx; font-weight: bold; color: #1D1D1F; border-bottom: 1px solid #E5E5EA; }
    .cp-x { position: absolute; right: 24rpx; top: 24rpx; width: 64rpx; height: 64rpx; line-height: 64rpx; border-radius: 50%; background: #E5E5EA; color: #6E6E73; font-size: 28rpx; font-weight: normal; }
    .cp-now { text-align: center; font-size: 60rpx; font-weight: bold; color: #1D1D1F; padding: 32rpx 40rpx 8rpx; line-height: 1.3; word-break: break-all; }
    .cp-view { height: 400rpx; margin: 0 40rpx; }
    .cp-ind { height: 88rpx; background: rgba(26, 115, 232, 0.06); }
    .cp-item { height: 88rpx; line-height: 88rpx; text-align: center; font-size: 36rpx; color: #1D1D1F; }
    .cp-note { text-align: center; font-size: 24rpx; color: #6E6E73; margin-top: 16rpx; }
    .cp-btn { margin: 32rpx 40rpx 0; height: 104rpx; line-height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; text-align: center; font-size: 32rpx; font-weight: bold; }
    .cp-hover { opacity: 0.6; }
</style>
