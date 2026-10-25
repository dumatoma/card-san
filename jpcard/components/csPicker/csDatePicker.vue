<template>
    <!-- [カスタムエイド 2026-10-22] 日付のドラムロール(今までの u-datetime-picker mode="date" と同じ使い方)。
         見た目は csPicker(見出し・右上の ×・選んでいる日付を大きく・下に「決定」)。
         v-model(タイムスタンプ ミリ秒)・minDate・maxDate・title。confirm({ value: タイムスタンプ, mode: 'date' })・cancel・close -->
    <cs-picker ref="p" :show="show" :title="title" :columns="cols" :defaultIndex="def" :units="['年', '月', '日']" sep=""
        @change="onChange" @confirm="onConfirm" @cancel="$emit('cancel')" @close="$emit('close')"></cs-picker>
</template>

<script>
    import csPicker from './csPicker.vue'
    export default {
        name: 'csDatePicker',
        components: { csPicker },
        props: {
            show: { type: Boolean, default: false },
            value: { type: [Number, String], default: () => Date.now() },
            minDate: { type: Number, default: () => new Date(1900, 0, 1).getTime() },
            maxDate: { type: Number, default: () => Date.now() },
            title: { type: String, default: '生年月日' }
        },
        data() {
            return { cols: [[], [], []], def: [0, 0, 0], y: 0, m: 1 }
        },
        watch: {
            show: { immediate: true, handler(v) { if (v) this.build() } }
        },
        methods: {
            days(y, m) {
                return new Date(y, m, 0).getDate()
            },
            list(a, b) {
                const r = []
                for (let i = a; i <= b; i++) r.push(String(i))
                return r
            },
            build() {
                const min = new Date(this.minDate), max = new Date(this.maxDate)
                let d = new Date(Number(this.value) || Date.now())
                // まだ選んでいない(今日のまま)ときは 1990年1月1日 から
                if (Date.now() - d.getTime() < 366 * 24 * 3600 * 1000) d = new Date(1990, 0, 1)
                if (d < min) d = min
                if (d > max) d = max
                const years = this.list(min.getFullYear(), max.getFullYear())
                this.y = d.getFullYear()
                this.m = d.getMonth() + 1
                this.cols = [years, this.list(1, 12), this.list(1, this.days(this.y, this.m))]
                this.def = [years.indexOf(String(this.y)), this.m - 1, d.getDate() - 1]
            },
            onChange(e) {
                if (e.columnIndex == 2) return
                const y = Number(e.value[0]), m = Number(e.value[1])
                if (this.days(y, m) != this.days(this.y, this.m)) this.$refs.p.setColumnValues(2, this.list(1, this.days(y, m)))
                this.y = y
                this.m = m
            },
            onConfirm(e) {
                const y = Number(e.value[0]), m = Number(e.value[1]), dd = Math.min(Number(e.value[2]), this.days(y, m))
                const t = new Date(y, m - 1, dd).getTime()
                this.$emit('input', t)
                this.$emit('confirm', { value: t, mode: 'date' })
            }
        }
    }
</script>
