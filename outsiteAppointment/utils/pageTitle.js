// 予約WEB のページタイトル（ブラウザのタブ名）
// 初期: 「Card-San 予約」 / 店舗情報取得後: 「{店舗名}｜予約」
// uni-app(H5) はページ表示のたびに navigationBarTitleText で document.title を上書きするため、
// 各ページの onShow でも呼び直す。
export const DEFAULT_TITLE = 'Card-San 予約'

export function setShopTitle(shopName) {
    let name = shopName
    if (name === undefined) {
        try { name = uni.getStorageSync('shopName') } catch (e) { name = '' }
    }
    const title = name ? (name + '｜予約') : DEFAULT_TITLE
    const apply = () => {
        try { uni.setNavigationBarTitle({ title }) } catch (e) {}
        // #ifdef H5
        try { document.title = title } catch (e) {}
        // #endif
    }
    apply()
    // uni 側のタイトル更新より後に確実に反映させる
    setTimeout(apply, 0)
}
