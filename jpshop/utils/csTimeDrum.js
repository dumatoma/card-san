// [カスタムエイド 2026-10-22] 予約の開始・終了の時間えらび(ドラムロール)を、その日の営業時間の中だけにする共通の処理。
//   使う画面で tdDate()(その日 'YYYY-MM-DD')・tdValue('start'|'end')(今の時刻)を用意し、
//   <cs-picker :show="startShow" :columns="tdCols" :defaultIndex="tdDefault" ... @change="tdHourChange($event, 'startPicker')"> のように使う。
//   開くときは tdOpen('start') / tdOpen('end')。選んだ時刻は tdText(e)('07:30' の形)。
//   営業時間が分からないとき・休みの日は 7:00〜24:00。今の時刻が範囲の外なら、その時刻まで広げる
import { getCalendar } from '@/api/index.js'

const pad = (n) => ('0' + n).slice(-2)
const MIN = '07:00'
const MAX = '24:00'

export default {
    data() {
        return {
            tdRange: { s: MIN, e: MAX },
            tdCols: [[], ['00', '30']],
            tdDefault: [0, 0],
            tdMonth: {} // 'YYYY-MM' → その月の営業時間(1回だけ読む)
        }
    },
    computed: {
        tdNote() {
            const r = this.tdRange
            if (r.s == MIN && r.e == MAX) return ''
            return '営業時間 ' + r.s + '〜' + r.e + ' の中で選べます'
        }
    },
    methods: {
        tdMinutes(h) {
            const r = this.tdRange
            return ['00', '30'].filter((m) => {
                const t = h + ':' + m
                return t >= r.s && t <= r.e
            })
        },
        tdHours() {
            const r = this.tdRange
            const a = []
            for (let h = Number(r.s.slice(0, 2)); h <= Number(r.e.slice(0, 2)); h++) {
                if (this.tdMinutes(pad(h)).length) a.push(pad(h))
            }
            return a
        },
        // その日の営業時間(いちばん早い開始〜いちばん遅い終了)
        async tdLoadRange() {
            let s = '', e = ''
            try {
                const d = String(this.tdDate() || '')
                const ym = d.slice(0, 7)
                if (/^\d{4}-\d{2}$/.test(ym)) {
                    if (!this.tdMonth[ym]) {
                        const res = await getCalendar({ date: ym + '-01', type: 'day' })
                        if (res && res.code == 200) this.tdMonth[ym] = res.data
                    }
                    const cal = this.tdMonth[ym]
                    const day = cal && cal.day_business && cal.day_business[Number(d.slice(8, 10))]
                    if (day && day.business == 1) {
                        ;(day.business_time || []).forEach((t) => {
                            if (!t || !t.start || !t.end || t.start == t.end) return
                            if (!s || t.start < s) s = t.start
                            if (!e || t.end > e) e = t.end
                        })
                    }
                }
            } catch (err) {}
            if (!s || !e) { s = MIN; e = MAX }
            // 今入っている時刻は必ず選べるように
            ;['start', 'end'].forEach((k) => {
                let v = String(this.tdValue(k) || '')
                if (!/^\d{1,2}:\d{2}$/.test(v)) return
                v = pad(Number(v.split(':')[0])) + ':' + v.split(':')[1]
                if (v == '00:00' && k == 'end') v = '24:00'
                if (v < s) s = v
                if (v > e) e = v
            })
            if (s < MIN) s = MIN
            if (e > MAX) e = MAX
            s = s.slice(0, 3) + (s.slice(3, 5) >= '30' ? '30' : '00')
            this.tdRange = { s, e }
        },
        async tdOpen(which) {
            await this.tdLoadRange()
            const hours = this.tdHours()
            let v = String(this.tdValue(which) || '')
            let h = pad(Number(v.split(':')[0]) || 0), m = (v.split(':')[1] || '00').slice(0, 2)
            if (which == 'end' && h + ':' + m == '00:00') h = '24'
            let hi = hours.indexOf(h)
            if (hi < 0) hi = 0
            const mins = this.tdMinutes(hours[hi] || '07')
            let mi = mins.indexOf(m)
            if (mi < 0) mi = 0
            this.tdCols = [hours, mins]
            this.tdDefault = [hi, mi]
            if (which == 'start') this.startShow = true
            else this.endShow = true
        },
        // 「時」を回したら、その時に選べる「分」にする
        tdHourChange(e, ref) {
            if (e.columnIndex !== 0) return
            const p = this.$refs[ref]
            if (p && p.setColumnValues) p.setColumnValues(1, this.tdMinutes(e.value[0]))
        },
        tdText(e) {
            return pad(Number(e.value[0])) + ':' + (e.value[1] || '00')
        }
    }
}
