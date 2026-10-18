// [カスタムエイド 2026-10-18] 端末の種類(ios / android)を、使うたびにその場で判定する(管理App 修正一覧 No.10)
// 以前は起動時に保存した値(uni.getStorageSync("platform"))を使っていたが、ログイン切れ(401)やログアウトで
// uni.clearStorageSync() が保存データを全部消すと端末の種類も消え、iPhone が Android 扱いになっていた
// (App課金が Google Play で送られる・契約更新日が1970年になる など)
export function getPlatform() {
    try {
        const p = uni.getSystemInfoSync().platform
        if (p === 'ios' || p === 'android') return p
    } catch (e) {}
    // 判定できないとき(開発用のブラウザなど)は、保存されている値を使う
    return uni.getStorageSync('platform') || ''
}
