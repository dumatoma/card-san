// [カスタムエイド 2026-10-22] お店のバーコード番号(JAN 13桁・8桁/CODE128)の確かめと、見本のバーコードの線を作る
// サーバー(app/Libs/CsPosBarcode.php)と同じ決まりで確かめる

// JAN の最後の1桁(右から 3・1・3・1… の重み)
export function janCheck(digits) {
    let sum = 0
    const n = digits.length
    for (let i = 0; i < n; i++) {
        const d = Number(digits[n - 1 - i])
        sum += i % 2 == 0 ? d * 3 : d
    }
    return (10 - (sum % 10)) % 10
}

// type: 1 = JAN / 2 = CODE128。{ ok, value, message, fixed }(fixed:最後の1桁を自動で付けた)
export function checkCode(type, raw) {
    let v = String(raw || '').trim()
    if (type == 1) {
        v = v.replace(/\s+/g, '')
        if (!v) return { ok: false, value: v, message: '' }
        if (!/^\d+$/.test(v)) return { ok: false, value: v, message: 'JAN の番号は数字だけで入力してください' }
        const n = v.length
        if (n == 12 || n == 7) return { ok: true, value: v + janCheck(v), fixed: true, message: '最後の1桁(' + janCheck(v) + ')を自動で付けます' }
        if (n == 13 || n == 8) {
            if (Number(v.slice(-1)) !== janCheck(v.slice(0, -1))) return { ok: false, value: v, message: 'JAN の番号が正しくありません(最後の1桁が合いません)' }
            return { ok: true, value: v, message: '正しい JAN(' + n + '桁)の番号です' }
        }
        return { ok: false, value: v, message: 'JAN の番号は13桁か8桁で入力してください' }
    }
    if (type == 2) {
        if (!v) return { ok: false, value: v, message: '' }
        if (v.length > 48 || !/^[\x20-\x7E]+$/.test(v)) return { ok: false, value: v, message: '半角の英数字・記号48文字までで入力してください' }
        return { ok: true, value: v, message: '正しい CODE128 の番号です' }
    }
    return { ok: false, value: v, message: '' }
}

const L = ['0001101', '0011001', '0010011', '0111101', '0100011', '0110001', '0101111', '0111011', '0110111', '0001011']
const G = ['0100111', '0110011', '0011011', '0100001', '0011101', '0111001', '0000101', '0010001', '0001001', '0010111']
const R = ['1110010', '1100110', '1101100', '1000010', '1011100', '1001110', '1010000', '1000100', '1001000', '1110100']
const P = ['LLLLLL', 'LLGLGG', 'LLGGLG', 'LLGGGL', 'LGLLGG', 'LGGLLG', 'LGGGLL', 'LGLGLG', 'LGLGGL', 'LGGLGL']

function ean13(c) {
    const d = c.split('').map(Number)
    const p = P[d[0]]
    let s = '101'
    for (let i = 1; i < 7; i++) s += (p[i - 1] == 'L' ? L : G)[d[i]]
    s += '01010'
    for (let i = 7; i < 13; i++) s += R[d[i]]
    return s + '101'
}

function ean8(c) {
    const d = c.split('').map(Number)
    let s = '101'
    for (let i = 0; i < 4; i++) s += L[d[i]]
    s += '01010'
    for (let i = 4; i < 8; i++) s += R[d[i]]
    return s + '101'
}

// CODE128(B セット)
const C128 = ['212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213', '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132', '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211', '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313', '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331', '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111', '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214', '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111', '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141', '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141', '114131', '311141', '411131', '211412', '211214', '211232', '2331112']
function code128(v) {
    const codes = [104]
    for (let i = 0; i < v.length; i++) codes.push(v.charCodeAt(i) - 32)
    let sum = 104
    for (let i = 1; i < codes.length; i++) sum += codes[i] * i
    codes.push(sum % 103, 106)
    let s = ''
    codes.forEach((c) => {
        const w = C128[c]
        for (let i = 0; i < w.length; i++) s += (i % 2 == 0 ? '1' : '0').repeat(Number(w[i]))
    })
    return s
}

// 見本の線:[{ w: 太さ(何本分), b: 黒か }] にまとめる(余白 10 本分を左右に付ける)
export function barcodeBars(type, value) {
    const r = checkCode(type, value)
    if (!r.ok) return []
    let bits = type == 1 ? (r.value.length == 13 ? ean13(r.value) : ean8(r.value)) : code128(r.value)
    bits = '0'.repeat(10) + bits + '0'.repeat(10)
    const out = []
    for (let i = 0; i < bits.length; i++) {
        const b = bits[i] == '1'
        if (out.length && out[out.length - 1].b == b) out[out.length - 1].w++
        else out.push({ w: 1, b: b })
    }
    return out
}
