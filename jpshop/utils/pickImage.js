// [カスタムエイド 2026-10-19] 写真を選ぶ画面を開く共通の部品
// iPhone で、写真を選ぶ画面の上の「×」がまれに出ず、閉じられなくなることがあった。
// 「カメラで撮影/写真を選択」の選択肢やキーボードが閉じる動きと、写真の画面が開く動きが重なるのが原因とみて、
// 選択肢はアプリ側で出し、閉じきってから(少し待ってから)写真の画面を開く。
// 使い方は uni.chooseImage と同じ(count, sourceType, sizeType, success, fail, complete)。
import { checkPerm } from './csPerm.js' // [カスタムエイド 2026-10-23] 許可がないときは日本語の案内
const WAIT = 350 // 選択肢・キーボードが閉じきるまで待つ時間(ミリ秒)

function open(opts) {
    setTimeout(() => {
        const st = opts.sourceType || []
        const kind = st.length == 1 && st[0] == 'camera' ? 'camera' : st.length == 1 && st[0] == 'album' ? 'album' : ''
        if (!kind) return uni.chooseImage(opts)
        checkPerm(kind).then((ok) => {
            if (ok) uni.chooseImage(opts)
            else {
                const e = { errMsg: 'chooseImage:fail auth deny' }
                if (typeof opts.fail == 'function') opts.fail(e)
                if (typeof opts.complete == 'function') opts.complete(e)
            }
        })
    }, WAIT)
}

export default function pickImage(opts) {
    opts = opts || {}
    const st = opts.sourceType
    if (st && st.length == 1) {
        // カメラだけ・アルバムだけと決まっているとき(呼ぶ側で選択肢を出している・キーボードを閉じた直後など)
        open(opts)
        return
    }
    uni.showActionSheet({
        itemList: ['カメラで撮影', '写真を選択'],
        success: (r) => {
            open(Object.assign({}, opts, { sourceType: [r.tapIndex == 0 ? 'camera' : 'album'] }))
        },
        fail: () => {
            const e = { errMsg: 'chooseImage:fail cancel' }
            if (typeof opts.fail == 'function') opts.fail(e)
            if (typeof opts.complete == 'function') opts.complete(e)
        }
    })
}
