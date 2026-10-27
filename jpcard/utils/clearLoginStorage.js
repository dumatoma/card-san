// [カスタムエイド 2026-10-19] ログアウト時の保存データの消去
// uni.clearStorageSync() で全部消すと、端末の種類(env)やプッシュ通知の端末ID(cid)まで消え、
// ログイン画面の Apple ボタンが出なくなる・再ログイン後にプッシュ通知の登録ができない、という不具合になっていた。
// 端末に紐づく値だけ残して、それ以外(ログイン情報など)を消す。
// [カスタムエイド 2026-10-26] 店舗QR統一:ログイン前に受け取ったお店(csPendingShop)・この端末で一度ログインしたことがあるか(csHadLogin)・iPhone の通知用の番号(push_token)も残す
// [カスタムエイド 2026-10-26] 段階5:「ペースト」で受け取る画面を一度出したか(csPasteShown)も残す
const KEEP_KEYS = ['env', 'cid', 'client_id', 'push_token', 'csPendingShop', 'csHadLogin', 'csPasteShown']

export function clearLoginStorage() {
    const keep = {}
    KEEP_KEYS.forEach((k) => {
        const v = uni.getStorageSync(k)
        if (v !== '' && v !== null && v !== undefined) keep[k] = v
    })
    uni.clearStorageSync()
    Object.keys(keep).forEach((k) => {
        uni.setStorageSync(k, keep[k])
    })
    if (!keep.env) {
        const p = uni.getSystemInfoSync().platform
        if (p === 'ios' || p === 'android') uni.setStorageSync('env', p)
    }
}

// [カスタムエイド 2026-10-19] ログイン画面を開く(すでにログイン画面なら何もしない)
// 複数の画面・通信から同時に呼ばれても、ログイン画面が重ならないようにする
export function goLogin() {
    const pages = getCurrentPages()
    const top = pages.length ? pages[pages.length - 1] : null
    if (top && (top.route === 'pages/login/login' || top.route === 'pages/register/register' || top.route === 'pages/csPaste/csPaste')) return
    // [カスタムエイド 2026-10-26] お店の QR・リンクから来た人と、この端末で一度もログインしたことがない人は「ようこそ!(新規登録)」へ。
    //   それ以外(ログアウトした人など)は今までどおりログイン画面へ
    const p = uni.getStorageSync('csPendingShop')
    if ((p && p.id) || !uni.getStorageSync('csHadLogin')) {
        // [カスタムエイド 2026-10-26] 段階5:iPhone(iOS 16 以上)で初めて開いた人には、先に「お店のカードを受け取りましょう(ペースト)」の画面を1回だけ出す
        // [カスタムエイド 2026-10-26b] モックどおり:コピーされた文字があるときだけ出す(ないときは「ようこそ!」へ。お店のページを見ずにアプリを入れた人に、意味のない画面を出さない)
        if (!(p && p.id) && csCanPaste() && !uni.getStorageSync('csPasteShown') && csClipHasText()) {
            uni.reLaunch({ url: '/pages/csPaste/csPaste' })
            return
        }
        uni.reLaunch({ url: '/pages/register/register' })
        return
    }
    uni.reLaunch({ url: '/pages/login/login' })
}

// [カスタムエイド 2026-10-26] 段階5:iPhone 標準の「ペースト」ボタンが使えるか(iPhone で iOS 16 以上)
function csCanPaste() {
    try {
        const s = uni.getSystemInfoSync()
        if (s.platform !== 'ios') return false
        return parseInt(String(s.osVersion || s.system || '').replace(/[^0-9.]/g, ''), 10) >= 16
    } catch (e) {
        return false
    }
}

// [カスタムエイド 2026-10-26b] コピーされた文字があるか(中身は読まない)。UIPasteboard.hasStrings は「ペーストを許可しますか?」の確認が出ない
//   確かめられないときは true(今までどおり画面を出す)
function csClipHasText() {
    try {
        const UIPasteboard = plus.ios.importClass('UIPasteboard')
        const pb = UIPasteboard.generalPasteboard()
        const has = plus.ios.invoke(pb, 'hasStrings')
        plus.ios.deleteObject(pb)
        // [カスタムエイド 2026-10-26c] はっきり「ない」と分かったときだけ false(読めない・形が違うときは画面を出す。いちばん大事な流れを止めないため)
        return !(has === false || has === 0 || has === '0')
    } catch (e) {
        return true
    }
}
