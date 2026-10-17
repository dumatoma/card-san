// [カスタムエイド 2026-10-17] スマホ・タブレット版(/sp)の判定と共通の小さな関数
//  - スマホ・タブレット(iPhone / iPad / Android)で開いたときは、/sp の画面(オーナー情報・ご契約内容だけ)に切り替える
//  - 「PC版の画面を表示する」を押した人は、その端末ではPC版のまま(localStorage の sp_force = 'pc')

export function isSpDevice() {
    try {
        if (localStorage.getItem('sp_force') === 'pc') return false
    } catch (e) {}
    var ua = navigator.userAgent || ''
    if (/iPhone|iPod|iPad|Android|Mobile/i.test(ua)) return true
    // iPadOS 13以降の Safari はPCと同じ表示(Macintosh)を名乗るため、タッチ対応かで見分ける
    if (/Macintosh/.test(ua) && navigator.maxTouchPoints > 1) return true
    return false
}

export function setForcePc(on) {
    try {
        if (on) localStorage.setItem('sp_force', 'pc')
        else localStorage.removeItem('sp_force')
    } catch (e) {}
}

// PC版の画面名 → スマホ版の画面
var SP_MAP = {
    login: '/sp/login',
    phone: '/sp/login',
    code: '/sp/code',
    user: '/sp/owner',
    content: '/sp/contract',
    terminationFulfil: '/sp/contract',
    termination: '/sp/contract',
    utilizePlan: '/sp/plan',
    planConfirm: '/sp/confirm'
}
// スマホでもPC版の画面のまま開くもの(新規登録・副管理者ログイン)
var SP_PASS = ['register', 'deputyAdmin']

function hasToken() {
    try { return !!localStorage.getItem('token') } catch (e) { return false }
}

// router.beforeEach から呼ぶ。移動先を返す(移動しないときは null)
export function spRedirect(to, from) {
    var isSp = (to.path || '').indexOf('/sp/') === 0
    if (isSp) {
        // /sp の画面を直接開いたときは「PC版の画面を表示する」の設定を解除する
        if (!from || !from.name) setForcePc(false)
        var open = to.path === '/sp/login' || to.path === '/sp/code'
        if (!open && !hasToken()) return { path: '/sp/login' }
        return null
    }
    if (!isSpDevice()) return null
    if (SP_PASS.indexOf(to.name) >= 0) return null
    var target = SP_MAP[to.name]
    if (target) {
        if (!hasToken() && target !== '/sp/login' && target !== '/sp/code') return { path: '/sp/login' }
        return { path: target, query: to.query }
    }
    return { path: hasToken() ? '/sp/contract' : '/sp/login' }
}

export function isChromeless(route) {
    var name = route && route.name
    var path = (route && route.path) || ''
    if (['deputyAdmin', 'register', 'login', 'code', 'phone'].indexOf(name) >= 0) return true
    if (path.indexOf('/sp/') === 0) return true
    // 起動直後(移動前)に PC 版のヘッダーが一瞬表示されないようにする
    var hash = window.location.hash || ''
    if (isSpDevice() && !/^#\/(register|deputyAdmin)(\?|$)/.test(hash)) return true
    return false
}

export function planName(t) {
    t = t * 1
    return t == 1 ? 'ライト' : t == 2 ? 'スタンダード' : t == 3 ? 'プレミアム' : 'なし'
}

export function fmtDate(sec) {
    if (!sec) return ''
    var d = new Date(sec * 1000)
    return d.getFullYear() + '年' + (d.getMonth() + 1) + '月' + d.getDate() + '日'
}

export function yen(n) {
    n = Math.round(n * 1)
    return '¥' + String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

export function adminInfo() {
    try { return JSON.parse(localStorage.getItem('admin')) || {} } catch (e) { return {} }
}
