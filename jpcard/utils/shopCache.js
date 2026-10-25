// [カスタムエイド 2026-10-25] お店の情報(/api/member/shop/{id})を読み込む共通の処理
//   以前は、サーバーの返事が5秒を過ぎると何も言わずに諦めていたため、店名・写真・営業時間・下のメニューが空のままの画面になっていた
//   (2026-10-25 15:43 CARD SALON で発生)。
//   ・前回読み込めたお店の情報を端末に残し、開いた瞬間はそれを出す(最新が届いたら入れ替え)
//   ・読み込めなかったときは、少し待って2回までやり直す(待ち時間は1回20秒)
import { getShopInfo } from '@/api/index.js'

const KEY = 'cs_shop_'

export function shopCacheGet(sid) {
    try {
        const c = uni.getStorageSync(KEY + sid)
        return c && c.data && c.data.shop ? c : null
    } catch (e) {
        return null
    }
}

export function shopCacheSet(sid, res) {
    try {
        if (res && res.code == 200 && res.data && res.data.shop) uni.setStorageSync(KEY + sid, { code: 200, data: res.data, t: Date.now() })
    } catch (e) {}
}

// 2回までやり直す。成功したら端末にも残す
export function fetchShopInfo(sid, tries) {
    if (tries == null) tries = 2
    return getShopInfo(sid).then((res) => {
        if (res && res.code == 200) {
            shopCacheSet(sid, res)
            return res
        }
        if (tries > 0 && !(res && res.code == 401)) return wait(1500).then(() => fetchShopInfo(sid, tries - 1))
        return res
    }).catch((err) => {
        if (tries > 0) return wait(1500).then(() => fetchShopInfo(sid, tries - 1))
        throw err
    })
}

function wait(ms) {
    return new Promise((r) => setTimeout(r, ms))
}
