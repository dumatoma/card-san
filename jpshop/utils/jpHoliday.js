// [カスタムエイド 2026-10-18] 日本の祝日(国民の祝日に関する法律に基づく。2020年以降のルール)
// 外部のサービスに頼らず計算で出す。振替休日・国民の休日にも対応
const cache = {}

function nthMonday(y, m, n) {
    // m は 1〜12。第n月曜日の日付
    const first = new Date(y, m - 1, 1).getDay()
    const firstMonday = 1 + ((8 - first) % 7)
    return firstMonday + (n - 1) * 7
}

function vernal(y) {
    return Math.floor(20.8431 + 0.242194 * (y - 1980) - Math.floor((y - 1980) / 4))
}

function autumnal(y) {
    return Math.floor(23.2488 + 0.242194 * (y - 1980) - Math.floor((y - 1980) / 4))
}

function key(y, m, d) {
    return y + '-' + m + '-' + d
}

export function holidaysOf(y) {
    if (cache[y]) return cache[y]
    const base = {}
    const add = (m, d, name) => { base[key(y, m, d)] = name }
    add(1, 1, '元日')
    add(1, nthMonday(y, 1, 2), '成人の日')
    add(2, 11, '建国記念の日')
    add(2, 23, '天皇誕生日')
    add(3, vernal(y), '春分の日')
    add(4, 29, '昭和の日')
    add(5, 3, '憲法記念日')
    add(5, 4, 'みどりの日')
    add(5, 5, 'こどもの日')
    add(7, nthMonday(y, 7, 3), '海の日')
    add(8, 11, '山の日')
    add(9, nthMonday(y, 9, 3), '敬老の日')
    add(9, autumnal(y), '秋分の日')
    add(10, nthMonday(y, 10, 2), 'スポーツの日')
    add(11, 3, '文化の日')
    add(11, 23, '勤労感謝の日')

    const all = Object.assign({}, base)
    const isH = (dt) => !!all[key(dt.getFullYear(), dt.getMonth() + 1, dt.getDate())]
    // 振替休日: 祝日が日曜日のとき、その後の最初の祝日でない日
    Object.keys(base).forEach((k) => {
        const p = k.split('-').map(Number)
        const dt = new Date(p[0], p[1] - 1, p[2])
        if (dt.getDay() === 0) {
            const n = new Date(dt)
            do { n.setDate(n.getDate() + 1) } while (isH(n))
            all[key(n.getFullYear(), n.getMonth() + 1, n.getDate())] = '振替休日'
        }
    })
    // 国民の休日: 前日と翌日が祝日で、その日が祝日でない日(日曜日を除く)
    for (let m = 1; m <= 12; m++) {
        const days = new Date(y, m, 0).getDate()
        for (let d = 2; d < days; d++) {
            const dt = new Date(y, m - 1, d)
            if (dt.getDay() === 0 || isH(dt)) continue
            const prev = new Date(y, m - 1, d - 1)
            const next = new Date(y, m - 1, d + 1)
            if (base[key(prev.getFullYear(), prev.getMonth() + 1, prev.getDate())] && base[key(next.getFullYear(), next.getMonth() + 1, next.getDate())]) {
                all[key(y, m, d)] = '国民の休日'
            }
        }
    }
    cache[y] = all
    return all
}

export function holidayName(date) {
    const y = date.getFullYear()
    return holidaysOf(y)[key(y, date.getMonth() + 1, date.getDate())] || ''
}
