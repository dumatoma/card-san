// [カスタムエイド 2026-10-19] お店が入力した文章を、段落に分けて表示するための共通の部品
// お店が入れた空行(何も書いていない行)を、まるまる1行の高さではなく「少しの間」(App.vue の .cs-para の余白)として出す。
// 1行だけの改行は、そのまま改行として残す。
export function textParas(text) {
    if (text === undefined || text === null) return []
    let t = String(text).replace(/\r\n?/g, '\n').trim()
    if (!t) return []
    return t.split(/\n[ \t　]*\n+/).map((p) => p.replace(/^\n+|\n+$/g, '')).filter((p) => p !== '')
}
