// [カスタムエイド 2026-10-22] 営業カレンダー・定休日と営業時間で使う共通の処理
//   ・営業時間の形 business_time [{start,end}, {start,end}] ⇔ 画面の「営業時間+中休み」
//   ・今日の状態(営業中/営業時間外/休業日)
//   ・「一部の週だけ休み」(曜日の設定 week_business[i].rest_weeks = [2, 4] など)
//     サーバーは曜日の設定を送った形のまま保存するので、決まりは rest_weeks として一緒に保存する。
//     予約の受付・お客さま向け表示・Google への送信は「この日だけ変更」(day_business)を見るので、
//     決まりに当たる日を 12か月先まで「この日だけ休み」として入れておく(管理App を開いたとき 1日1回、足りない分を足す)。
import { getCalendar, setBusinessHours } from '@/api/index.js'

export const WEEK = ['日', '月', '火', '水', '木', '金', '土']
// [カスタムエイド 2026-10-22] 予約・営業時間で選べる時間は 7:00〜24:00(サーバーも同じ範囲)
export const MIN_TIME = '07:00'
export const MAX_TIME = '24:00'
export const TIMES = (() => {
    const a = []
    for (let h = 7; h <= 24; h++) {
        a.push((h < 10 ? '0' : '') + h + ':00')
        if (h < 24) a.push((h < 10 ? '0' : '') + h + ':30')
    }
    return a
})()
export const FILL_MONTHS = 12

const pad = (n) => (n < 10 ? '0' : '') + n
export const keyOf = (dt) => dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate())
export const nthOf = (day) => Math.ceil(day / 7) // その月の第何週(何回目のその曜日か)

// business_time → { open, close, brk }(中休みは 1つ目の終わり〜2つ目の始まり)
export function toRange(bt) {
    const a = (bt || []).filter((t) => t && t.start && t.end)
    if (!a.length) return { open: '10:00', close: '19:00', brk: null }
    if (a.length == 1) return { open: a[0].start, close: a[0].end, brk: null }
    return { open: a[0].start, close: a[a.length - 1].end, brk: { start: a[0].end, end: a[1].start } }
}
export function fromRange(r) {
    if (r.brk) return [{ start: r.open, end: r.brk.start }, { start: r.brk.end, end: r.close }]
    return [{ start: r.open, end: r.close }]
}
// 入力のまちがい(なければ '')
export function rangeError(r) {
    if (!(r.open < r.close)) return '終了は開始より後にしてください'
    if (r.brk) {
        if (!(r.brk.start < r.brk.end)) return '中休みの終わりは始まりより後にしてください'
        if (!(r.open < r.brk.start && r.brk.end < r.close)) return '中休みは営業時間の中にしてください'
    }
    return ''
}
export function rangeText(bt) {
    return (bt || []).filter((t) => t && t.start && t.end).map((t) => t.start + '〜' + t.end).join('  ')
}

// 今日の状態 { text, color, sub }(color: green / red / plain)
export function dayStatus(day, isToday, now) {
    if (!day || day.business != 1) return { text: '休業日', color: 'red', sub: '' }
    if (!isToday) return { text: '営業時間', color: 'plain', sub: '' }
    const hm = pad(now.getHours()) + ':' + pad(now.getMinutes())
    const a = (day.business_time || []).filter((t) => t && t.start && t.end)
    if (a.some((t) => t.start <= hm && hm < t.end)) return { text: '営業中', color: 'green', sub: '' }
    const next = a.find((t) => t.start > hm)
    return { text: '営業時間外', color: 'red', sub: next ? '営業開始 ' + next.start : '' }
}

// week_business(オブジェクトでも配列でも)→ 7件の配列
export function weekList(wb) {
    const arr = []
    for (let i = 0; i < 7; i++) {
        const v = (wb && (wb[i] || wb[String(i)])) || null
        arr.push(v)
    }
    return arr
}

// 決まり(rest_weeks)に当たる日の一覧 [{ key, w }](from〜to、'YYYY-MM-DD')
export function ruleDates(wb, from, to) {
    const list = weekList(wb)
    const out = []
    const d = new Date(Number(from.slice(0, 4)), Number(from.slice(5, 7)) - 1, Number(from.slice(8, 10)))
    for (let n = 0; n < 400; n++) {
        const k = keyOf(d)
        if (k > to) break
        const v = list[d.getDay()]
        if (v && v.business == 1 && Array.isArray(v.rest_weeks) && v.rest_weeks.indexOf(nthOf(d.getDate())) > -1) {
            out.push({ key: k, w: d.getDay() })
        }
        d.setDate(d.getDate() + 1)
    }
    return out
}
export function fillEnd() {
    const d = new Date()
    d.setMonth(d.getMonth() + FILL_MONTHS)
    return keyOf(d)
}

// その月の「この日だけ変更」(サーバーが返す、日にち → { business, business_time })
async function monthOverrides(ym) {
    const res = await getCalendar({ type: 'day', date: ym })
    if (!res || res.code != 200) throw new Error('calendar')
    return (res.data && res.data.config_day_business) || {}
}
function setDay(key, business, bt) {
    return setBusinessHours({ type: 'day', date: key, business: business, business_time: business ? bt : [] })
}

// 決まりに当たる日のうち、まだ何も入っていない日を「この日だけ休み」にする。
// 手で「この日だけ営業」にした日など、すでに入っている日はさわらない。
// reopen に古い決まりの日を渡すと、新しい決まりで外れた日のうち休みになっている日を、その曜日の営業時間に戻す。
export async function syncRuleDays(wb, reopenFrom) {
    const from = keyOf(new Date())
    const to = fillEnd()
    const want = ruleDates(wb, from, to)
    const wantKeys = want.map((x) => x.key)
    const back = reopenFrom ? ruleDates(reopenFrom, from, to).filter((x) => wantKeys.indexOf(x.key) < 0) : []
    const months = {}
    want.concat(back).forEach((x) => { months[x.key.slice(0, 7)] = true })
    const list = weekList(wb)
    let changed = 0
    for (const ym of Object.keys(months).sort()) {
        const ov = await monthOverrides(ym)
        for (const x of want) {
            if (x.key.slice(0, 7) != ym) continue
            if (ov[Number(x.key.slice(8, 10))]) continue
            const r = await setDay(x.key, 0, [])
            if (r && r.code == 200) changed++
        }
        for (const x of back) {
            if (x.key.slice(0, 7) != ym) continue
            const o = ov[Number(x.key.slice(8, 10))]
            if (!o || o.business != 0) continue
            const v = list[x.w]
            if (!v || v.business != 1) continue
            const r = await setDay(x.key, 1, v.business_time || [])
            if (r && r.code == 200) changed++
        }
    }
    return changed
}

// 管理App を開いたとき(1日1回):決まりの休みを 12か月先まで足す。失敗しても何も出さない
export async function fillRuleDaysDaily() {
    try {
        const admin = uni.getStorageSync('admin') || {}
        const mark = 'csRuleFill_' + (admin.sid || '') + '_' + keyOf(new Date())
        if (uni.getStorageSync('csRuleFillDone') == mark) return
        const res = await getCalendar({ type: 'week', date: '' })
        if (!res || res.code != 200) return
        const wb = res.data && res.data.week_business
        const has = weekList(wb).some((v) => v && v.business == 1 && Array.isArray(v.rest_weeks) && v.rest_weeks.length)
        if (has) await syncRuleDays(wb)
        uni.setStorageSync('csRuleFillDone', mark)
    } catch (e) {}
}

// ===== [カスタムエイド 2026-10-22] 予約表に出す時間の範囲 =====
// 予約の終わり「00:00」は次の日の 0:00 = 24:00
export const endOf = (t) => (t == '00:00' ? '24:00' : t)
const clampT = (t) => (t < MIN_TIME ? MIN_TIME : t > MAX_TIME ? MAX_TIME : t)
const floorHalf = (t) => t.slice(0, 3) + (t.slice(3, 5) >= '30' ? '30' : '00')
const ceilHalf = (t) => {
    const h = Number(t.slice(0, 2)), m = Number(t.slice(3, 5))
    if (m == 0 || m == 30) return t
    if (m < 30) return pad(h) + ':30'
    return pad(h + 1) + ':00'
}
// 営業カレンダーのデータ(week_business と、その月の「この日だけ変更」)から、いちばん早い開始〜いちばん遅い終了
export function rangeFromCal(cal) {
    let s = '', e = ''
    const take = (v) => {
        if (!v || v.business != 1) return
        ;(v.business_time || []).forEach((t) => {
            if (!t || !t.start || !t.end || t.start == t.end) return
            if (!s || t.start < s) s = t.start
            if (!e || t.end > e) e = t.end
        })
    }
    if (cal) {
        weekList(cal.week_business).forEach(take)
        const o = cal.config_day_business || {}
        Object.keys(o).forEach((k) => take(o[k]))
    }
    if (!s || !e) return null
    return { s: clampT(floorHalf(s)), e: clampT(ceilHalf(e)) }
}
// 範囲を広げる(予約がはみ出しているときなど)
export function widen(r, s, e) {
    const out = { s: r.s, e: r.e }
    if (s && clampT(floorHalf(s)) < out.s) out.s = clampT(floorHalf(s))
    if (e && clampT(ceilHalf(endOf(e))) > out.e) out.e = clampT(ceilHalf(endOf(e)))
    return out
}
// 予約表の枠 [{ time, val }](s から e の 30分前まで)
export function buildTimes(s, e) {
    const a = []
    let h = Number(s.slice(0, 2)), m = Number(s.slice(3, 5))
    for (let n = 0; n < 40; n++) {
        const t = pad(h) + ':' + pad(m)
        if (t >= e) break
        a.push({ time: t, val: h + (m ? 0.5 : 0) })
        m += 30
        if (m == 60) { m = 0; h++ }
    }
    return a
}
