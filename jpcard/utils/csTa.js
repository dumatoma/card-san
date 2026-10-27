// [カスタムエイド 2026-10-27] チャットの入力欄の高さ(4画面で共通:Card-San App のメッセージ/管理App のメッセージ・スタッフルーム・スタッフ間チャット)
//   ・空のときは1行の高さ。改行するたびに1行ずつ伸び、6行まで伸びる(7行目からは伸びずに中でスクロール。LINE と同じ)
//   ・uni-app の入力欄は高さを決めないと 150px になるので、必ずこの高さを入れる(10/26 に空のとき高さを入れず、大きく崩れていた)
export const CS_TA_LINE = 22 // 1行の高さ(px)
export const CS_TA_MAX = 6 // 伸びる行数の上限
export const CS_TA_STYLE = 'border:1px solid #999;border-radius:15px;box-sizing:border-box;padding:6px 10px;width:100%;font-size:16px;line-height:' + CS_TA_LINE + 'px;overflow-y:auto;'
export function csTaH(lines) {
    const n = Math.max(1, Math.min(CS_TA_MAX, Number(lines) || 1))
    return n * CS_TA_LINE + 6 * 2 + 1 * 2 // 文字の行 + 上下の余白 + 上下の枠
}
