// [カスタムエイド 2026-10-19] チャットで写真を複数枚送る・まとめて表示するための共通の部品
// (Card-San App と CardSan管理 の両方で同じ内容のファイルを使う)
//
// 送り方:写真は1枚ずつ別のメッセージとして送る(サーバーの変更なし)。
// 見せ方:同じ人が続けて送った写真(同じ日・1分以内)を、LINE のように1つのかたまり(並べた表示)にする。

const MAX_SIDE = 1600 // 送る写真の長い辺の最大(px)。これより大きい写真だけ縮める

// 写真を送る前に縮める(長い辺 1600px・画質 80)。小さい写真・縮められない時はそのまま
export function shrinkImage(path) {
    return new Promise((resolve) => {
        // #ifdef APP-PLUS
        uni.getImageInfo({
            src: path,
            success: (info) => {
                const w = info.width || 0
                const h = info.height || 0
                if (!w || !h || Math.max(w, h) <= MAX_SIDE) {
                    resolve(path)
                    return
                }
                const opt = { src: path, quality: 80 }
                if (w >= h) {
                    opt.compressedWidth = MAX_SIDE
                } else {
                    opt.compressedHeight = MAX_SIDE
                }
                opt.success = (r) => resolve(r.tempFilePath || path)
                opt.fail = () => resolve(path)
                uni.compressImage(opt)
            },
            fail: () => resolve(path)
        })
        // #endif
        // #ifndef APP-PLUS
        resolve(path)
        // #endif
    })
}

// 写真を1枚アップロードして、サーバー上のパスを返す
export function uploadImage(baseUrl, path) {
    return new Promise((resolve, reject) => {
        uni.uploadFile({
            url: baseUrl + '/tool/upload/file',
            filePath: path,
            name: 'file',
            success: (result) => {
                try {
                    const re = JSON.parse(result.data)
                    if (re.code == 200 && re.data && re.data.path) {
                        resolve(re.data.path)
                        return
                    }
                } catch (e) {}
                reject(result)
            },
            fail: reject
        })
    })
}

function minutes(t) {
    const m = /(\d{1,2}):(\d{2})/.exec(String(t || ''))
    return m ? Number(m[1]) * 60 + Number(m[2]) : null
}

// 1つ前の写真と同じかたまりにするか
function sameGroup(a, b) {
    if (!a || !b) return false
    if (a.type != 2 || b.type != 2) return false
    // [カスタムエイド 2026-10-21] リンク付きの写真(一斉配信)は、押すとリンクが開く1枚の写真なので、かたまりにしない
    if (a.url || b.url) return false
    if (a.sender != b.sender) return false
    if (a.said !== undefined && b.said !== undefined && a.said != b.said) return false
    if (a.date != b.date) return false
    const ma = minutes(a.time)
    const mb = minutes(b.time)
    if (ma === null || mb === null) return a.time == b.time
    return Math.abs(mb - ma) <= 1
}

// メッセージの並びに「写真のかたまり」の印を付ける(並びが変わるたびに呼ぶ)
//   かたまりの先頭:item.grpUrls = [写真のURL...]、item.grpItems = [メッセージ...]
//   2枚目以降   :item.grpHide = true(表示しない。先頭にまとめて出る)
export function markImageGroups(arr) {
    if (!arr) return arr
    let i = 0
    while (i < arr.length) {
        const head = arr[i]
        head.grpHide = false
        head.grpUrls = null
        head.grpItems = null
        let j = i + 1
        while (j < arr.length && sameGroup(arr[j - 1], arr[j])) {
            arr[j].grpHide = true
            arr[j].grpUrls = null
            arr[j].grpItems = null
            j++
        }
        if (j - i > 1) {
            const items = arr.slice(i, j)
            head.grpItems = items
            head.grpUrls = items.map((v) => v.message)
        }
        i = j
    }
    // [カスタムエイド 2026-10-21] リンク付きの写真のすぐ後ろに届く「リンクの文字だけ」のメッセージ(古いアプリ向けにサーバーが一緒に送る)は出さない。
    // 写真を押せばリンクが開くので、二重に見えないようにする
    for (let k = 1; k < arr.length; k++) {
        const a = arr[k - 1]
        const b = arr[k]
        if (b.type == 1 && a.type == 2 && a.url && a.sender == b.sender && String(b.message || '').trim() == String(a.url).trim()) {
            b.grpHide = true
        }
    }
    return arr
}

// かたまりの写真を全画面で表示(押した写真から。左右にスワイプ、長押しで保存)
export function previewGroup(urls, index) {
    uni.previewImage({
        urls: urls,
        current: index || 0,
        indicator: 'number',
        loop: urls.length > 1,
        longPressActions: {
            itemList: ['画像を保存'],
            success: (data) => {
                saveImageUrl(urls[data.index])
            }
        }
    })
}

export function saveImageUrl(url) {
    uni.downloadFile({
        url: url,
        success: (res) => {
            if (res.statusCode === 200) {
                uni.saveImageToPhotosAlbum({
                    filePath: res.tempFilePath,
                    success: () => uni.showToast({ title: '保存しました', icon: 'success' }),
                    fail: () => uni.showToast({ title: '保存に失敗しました', icon: 'none' })
                })
            }
        }
    })
}
