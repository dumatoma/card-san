// [カスタムエイド 2026-10-26] 店舗QR統一:お店の QR・リンクの読み取りを1か所にまとめた共通の処理。
//   読める形:
//     ・新しい QR / リンク:https://card-san.jp/s/{店舗ID}(iPhone のカメラ → Universal Links でもここに来る)
//     ・アプリを開くリンク:card-sanapp://shop/{店舗ID}(店舗ページの「アプリをお持ちの方はこちら」。[カスタムエイド 2026-10-27] cardsan:// は管理App と重なるため card-sanapp:// に(ハイフンあり)。古い cardsan:// も読む)
//     ・今の QR:{"type":"shop","id":店舗ID}(type が shop 以外のものは使わない)
//     ・店舗ページでコピーした文字:cardsan-shop:{店舗ID}:{コピーした時刻}(24時間を過ぎたものは使わない)
//   以前は同じ処理が3か所(TOP・アカウント情報×2)にあり、今の形しか読めなかった(URL の QR を読むとエラーで止まる)
import { scanCode, getLineInfo } from '@/api/index.js'

const PENDING = 'csPendingShop' // ログイン前に受け取った店舗ID(ログインしたら開く)
const DAY = 24 * 60 * 60

// 文字から店舗IDを取り出す。読めなければ 0
export function parseShopId(text) {
    const s = String(text || '').trim()
    if (!s) return 0
    let m = s.match(/^https?:\/\/(?:www\.)?card-san\.jp\/s\/(\d+)/i)
    if (m) return Number(m[1])
    m = s.match(/^(?:card-?sanapp|cardsan):\/\/shop\/(\d+)/i)
    if (m) return Number(m[1])
    m = s.match(/^cardsan-shop:(\d+):(\d+)$/)
    if (m) {
        const at = Number(m[2])
        return at && Math.abs(Date.now() / 1000 - at) <= DAY ? Number(m[1]) : 0
    }
    if (s.charAt(0) == '{') {
        try {
            const j = JSON.parse(s)
            if (j && (!j.type || j.type == 'shop') && Number(j.id) > 0) return Number(j.id)
        } catch (e) {}
    }
    return 0
}

function loggedIn() {
    const u = uni.getStorageSync('user') || {}
    return !!(uni.getStorageSync('token') && u.uuid)
}

// お店を開く:まだ会員でなければ会員情報(BindCard)、会員ならそのお店のページ。ログイン前なら覚えておく
export function openShop(sid, opts) {
    sid = Number(sid) || 0
    if (!sid) return
    if (!loggedIn()) {
        uni.setStorageSync(PENDING, { id: sid, at: Math.floor(Date.now() / 1000) })
        // ログインしていなければ、カードの画像つきの登録画面(ようこそ!)へ。登録が終わって TOP に来たら、そのお店を開く
        if (!opts || opts.goRegister !== false) {
            const pages = getCurrentPages()
            const top = pages.length ? pages[pages.length - 1] : null
            if (top && top.route === 'pages/register/register' && top.$vm && top.$vm.csLoadShop) top.$vm.csLoadShop()
            else uni.reLaunch({ url: '/pages/register/register' })
        }
        return
    }
    uni.setStorageSync('csHadLogin', 1)
    uni.removeStorageSync(PENDING)
    uni.showLoading({ title: '読み込み中' })
    scanCode(sid).then((res) => {
        uni.hideLoading()
        if (!res || res.code != 200) {
            uni.showToast({ title: (res && res.message) || 'お店が見つかりませんでした', icon: 'none' })
            return
        }
        if (res.data.member == 0) {
            uni.navigateTo({ url: '/pagesA/BindCard/BindCard?id=' + res.data.cid + '&sid=' + sid + '&name=' + encodeURIComponent(res.data.shop_name || '') })
        } else {
            uni.navigateTo({ url: '/pagesA/shop/shop?id=' + sid + '&cid=' + res.data.mid })
        }
    }).catch(() => {
        uni.hideLoading()
        uni.showToast({ title: '通信がうまくいきませんでした', icon: 'none' })
    })
}

// アプリ内の QR リーダーで読む(TOP・アカウント情報から使う)
export function scanShopQr() {
    uni.scanCode({
        onlyFromCamera: true,
        success: (res) => {
            const sid = parseShopId(res && res.result)
            if (!sid) {
                uni.showModal({ title: 'お店の QR コードではありません', content: 'お店に置いてある Card-San の QR コードを読み取ってください。', showCancel: false, confirmText: '閉じる' })
                return
            }
            openShop(sid)
        }
    })
}

// リンク(Universal Links・cardsan://)でアプリが開かれたとき。App.vue から呼ぶ
export function handleLaunchLink(url) {
    if (/^(?:card-?sanapp|cardsan):\/\/line-login/i.test(String(url || ''))) { csFinishLineLogin(String(url)); return 0 } // [カスタムエイド 2026-10-26e] LINE ログインから戻ってきた
    const sid = parseShopId(url)
    if (sid) openShop(sid)
    return sid
}

// ログインする前に受け取った店舗IDがあれば開く(TOP の画面に来たときに呼ぶ)。24時間を過ぎたものは使わない
export function consumePendingShop() {
    if (loggedIn()) uni.setStorageSync('csHadLogin', 1) // この端末で一度ログインした(ログアウトしたあとはログイン画面を出す)
    const p = uni.getStorageSync(PENDING)
    if (!p || !p.id) return false
    if (Math.floor(Date.now() / 1000) - (p.at || 0) > DAY) {
        uni.removeStorageSync(PENDING)
        return false
    }
    if (!loggedIn()) return false
    openShop(p.id)
    return true
}

export function pendingShopId() {
    const p = uni.getStorageSync(PENDING)
    return p && p.id && Math.floor(Date.now() / 1000) - (p.at || 0) <= DAY ? Number(p.id) : 0
}

// ログインなしで見られるお店の情報(店名・ロゴ・カード)。登録画面にカードの画像を出すため
export function fetchShopCard(sid, baseUrl) {
    return new Promise((resolve) => {
        uni.request({
            url: (baseUrl || 'https://api.card-san.jp') + '/api/cs/shop/' + Number(sid),
            method: 'GET',
            success: (r) => resolve(r && r.data && r.data.code == 200 ? r.data.data : null),
            fail: () => resolve(null)
        })
    })
}

// QR を読んで店舗IDを取り出すだけ(登録画面など、ログイン前に使う)
export function scanShopId() {
    return new Promise((resolve) => {
        uni.scanCode({
            onlyFromCamera: true,
            success: (res) => resolve(parseShopId(res && res.result)),
            fail: () => resolve(-1)
        })
    })
}

// [カスタムエイド 2026-10-26e] LINE でログイン:LINE のログイン画面を Safari で開く。
//   アプリの中のブラウザでは LINE の「自動ログイン」が働かず、メールアドレスとパスワードの画面になるため(LINE の決まり)。
//   ログインが終わると、サーバー(AuthController@apiLoginRedirect)が card-sanapp://line-login?code=… でアプリに戻す([カスタムエイド 2026-10-27] cardsan:// は管理App と重なるため変更)
const CS_LINE_CLIENT = '1657727494'
const CS_LINE_REDIRECT = 'https://api.card-san.jp/api/member/api_login_redirect/line'
export function csLineLogin() {
    const state = 'csapp' + Date.now().toString(36) + Math.random().toString(36).slice(2, 8)
    uni.setStorageSync('csLineState', state)
    const url = 'https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=' + CS_LINE_CLIENT
        + '&redirect_uri=' + encodeURIComponent(CS_LINE_REDIRECT) + '&state=' + state + '&scope=profile%20openid'
    // #ifdef APP-PLUS
    plus.runtime.openURL(url, () => { uni.showToast({ title: 'Safari を開けませんでした', icon: 'none' }) })
    // #endif
}

function csQuery(url) {
    const q = {}
    const i = url.indexOf('?')
    if (i < 0) return q
    url.slice(i + 1).split('&').forEach((kv) => {
        const p = kv.split('=')
        if (p[0]) q[decodeURIComponent(p[0])] = decodeURIComponent(p[1] || '')
    })
    return q
}

let csLineBusy = ''
function csFinishLineLogin(url) {
    const q = csQuery(url)
    const want = uni.getStorageSync('csLineState')
    if (!q.code) {
        uni.showToast({ title: 'LINE でのサインインをやめました', icon: 'none' })
        return
    }
    if (csLineBusy === q.code) return // 同じものの2回目は使わない
    if (!want || q.state !== want) { // このアプリで始めたログインでないものは使わない([カスタムエイド 2026-10-27] 黙って止めず、知らせる)
        uni.showToast({ title: 'もう一度「LINEでサインイン」を押してください', icon: 'none', duration: 2500 })
        return
    }
    csLineBusy = q.code
    uni.removeStorageSync('csLineState')
    uni.showLoading({ title: '読み込み中' })
    getLineInfo(q.code).then((result) => {
        uni.hideLoading()
        if (result && result.code == 200) {
            uni.setStorageSync('token', result.data.token)
            uni.setStorageSync('user', result.data.user)
            uni.setStorageSync('csHadLogin', 1)
            uni.showToast({ title: result.message || 'サインインしました', icon: 'none', duration: 1500 })
            setTimeout(() => { uni.reLaunch({ url: '/pages/index/index' }) }, 1500)
        } else {
            uni.showToast({ title: (result && result.message) || 'サインインできませんでした', icon: 'none' })
        }
    }).catch(() => {
        uni.hideLoading()
        uni.showToast({ title: '通信がうまくいきませんでした', icon: 'none' })
    })
}
