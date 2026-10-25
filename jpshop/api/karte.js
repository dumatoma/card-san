// [カスタムエイド 2026-10-23] カルテ機能の API(サーバー:CsKarteController)
import request from '../utils/request.js'
import { getPlatform } from '../utils/platform.js'

const BASE = 'https://api.card-san.jp'

const get = (url) => request({ url, method: 'get' })
const post = (url, data) => request({ url, method: 'post', data: data || {} })

export const karteList = (mid, photos) => get('/api/shop/karte/member/' + mid + (photos ? '?photos=' + photos : ''))
export const karteDetail = (id) => get('/api/shop/karte/' + id)
export const karteStatus = (id) => get('/api/shop/karte/' + id + '/status')
export const karteFromTpl = (mid, data) => post('/api/shop/karte/member/' + mid + '/from_tpl', data)
export const karteRename = (id, title) => post('/api/shop/karte/' + id + '/rename', { title })
export const karteDelete = (id) => post('/api/shop/karte/' + id + '/delete')
export const kartePageDelete = (id) => post('/api/shop/karte/page/' + id + '/delete')
// [カスタムエイド 2026-10-24] 保存済みのページの画像を直す(切り抜き・明るさ・回転)。書き込みには触らない
export const karteEditPage = (id, data) => post('/api/shop/karte/page/' + id + '/edit', data)
// [カスタムエイド 2026-10-24] テンプレートの画像を直す(差し込みの位置もサーバーでいっしょに直す)。{ restore: 1 } で元の画像に戻す
export const karteTplImage = (id, data) => post('/api/shop/karte/tpl/' + id + '/image', data)
export const karteTrash = (mid) => get('/api/shop/karte/member/' + mid + '/trash')
export const karteRestore = (type, id) => post('/api/shop/karte/restore', { type, id })
export const karteAddMark = (pageId, data) => post('/api/shop/karte/page/' + pageId + '/mark', data)
export const karteUpdateMark = (id, data) => post('/api/shop/karte/mark/' + id + '/update', data)
export const karteDeleteMark = (id) => post('/api/shop/karte/mark/' + id + '/delete')
export const karteDownload = (id, format, page) => get('/api/shop/karte/' + id + '/download?format=' + format + (page ? '&page=' + page : ''))
export const karteTpl = () => get('/api/shop/karte/tpl')
export const karteTplUpdate = (id, data) => post('/api/shop/karte/tpl/' + id + '/update', data)
export const karteTplDelete = (id) => post('/api/shop/karte/tpl/' + id + '/delete')
export const karteUsage = () => get('/api/shop/karte/usage')

// 写真は送る前に長い辺 2000px まで小さくする(サーバーでも長い辺 2000px で保存するため。4800万画素の写真だとサーバーで処理しきれず、送れなかったため)
// [カスタムエイド 2026-10-24] 2400px・画質88 → 2000px・画質82(送る時間を短くする。見た目は変わらない)
function shrink(path) {
    return new Promise((resolve) => {
        // #ifdef APP-PLUS
        uni.getImageInfo({
            src: path,
            success: (info) => {
                const max = Math.max(info.width, info.height)
                if (!max || max <= 2000) return resolve(path)
                uni.compressImage({
                    src: path,
                    quality: 82,
                    compressedWidth: Math.round(info.width * 2000 / max),
                    success: (r) => resolve(r.tempFilePath || path),
                    fail: () => resolve(path)
                })
            },
            fail: () => resolve(path)
        })
        // #endif
        // #ifndef APP-PLUS
        resolve(path)
        // #endif
    })
}

// ファイルを送る(ログインの合言葉を付けて)。戻り値:サーバーの答え(code・message・data)
export async function karteUpload(url, filePath, formData) {
    if (!formData || formData.mode != 'pdf') filePath = await shrink(filePath)
    const token = uni.getStorageSync('token')
    const info = uni.getStorageSync('admin') || {}
    return new Promise((resolve) => {
        uni.uploadFile({
            url: BASE + url,
            filePath,
            name: 'file',
            formData: formData || {},
            header: { token, uuid: info.uuid || '', client: 'mobile', platform: getPlatform() == 'ios' ? 2 : 3 },
            success: (r) => {
                let d = null
                try { d = JSON.parse(r.data) } catch (e) {}
                if (!d) d = { code: r.statusCode == 413 ? 413 : 500, message: r.statusCode == 413 ? 'ファイルが大きすぎます' : '送れませんでした(' + r.statusCode + ')。もう一度お試しください' }
                resolve(d)
            },
            fail: () => resolve({ code: 0, message: '通信できませんでした。電波の良いところでもう一度お試しください' })
        })
    })
}

export const karteUploadTo = (mid, filePath, data) => karteUpload('/api/shop/karte/member/' + mid + '/upload', filePath, data)
export const karteTplStore = (filePath, data) => karteUpload('/api/shop/karte/tpl', filePath, data)

// 書き込みの色・大きさ(サーバーの CsKarte と同じ値。大きさ・太さはページの幅に対する割合)
export const COLORS = [
    { key: 'black', name: '黒', hex: '#1D1D1F' },
    { key: 'red', name: '赤', hex: '#D93025' },
    { key: 'blue', name: '青', hex: '#1A73E8' },
    { key: 'white', name: '白', hex: '#FFFFFF' }
]
export const colorHex = (k) => (COLORS.find((c) => c.key == k) || COLORS[0]).hex
export const TEXT_SIZE = { s: 0.025, m: 0.035, l: 0.05 }
export const PEN_WIDTH = { thin: 0.004, thick: 0.01 }

// 「10/16 15:20」の形
export function shortTime(sec) {
    if (!sec) return ''
    const d = new Date(sec * 1000)
    const p = (n) => ('0' + n).slice(-2)
    return (d.getMonth() + 1) + '/' + d.getDate() + ' ' + p(d.getHours()) + ':' + p(d.getMinutes())
}
export function shortDate(sec) {
    if (!sec) return ''
    const d = new Date(sec * 1000)
    return (d.getMonth() + 1) + '/' + d.getDate()
}

// 権限:オーナー、または副管理者で「カルテ」(20)がある人。サーバーでも確かめる
export function karteAllowed(privileges) {
    const a = uni.getStorageSync('admin') || {}
    if (a.admin_type != 2) return true
    return (privileges || []).map((v) => Number(v)).indexOf(20) != -1
}
