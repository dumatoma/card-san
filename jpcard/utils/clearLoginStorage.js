// [カスタムエイド 2026-10-19] ログアウト時の保存データの消去
// uni.clearStorageSync() で全部消すと、端末の種類(env)やプッシュ通知の端末ID(cid)まで消え、
// ログイン画面の Apple ボタンが出なくなる・再ログイン後にプッシュ通知の登録ができない、という不具合になっていた。
// 端末に紐づく値だけ残して、それ以外(ログイン情報など)を消す。
const KEEP_KEYS = ['env', 'cid', 'client_id']

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
    if (top && top.route === 'pages/login/login') return
    uni.reLaunch({ url: '/pages/login/login' })
}
