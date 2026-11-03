// [カスタムエイド 2026-10-27c] アイコンのバッジの数を聞く通信の「順番」を守る。
//   数を聞く通信が2つ重なったとき、古い方の答えがあとから届くと、古い数でバッジを上書きしてしまう。最後に聞いた分の答えだけを使う
let csSeq = 0
export function csIconSeq() { return ++csSeq }
export function csIconLatest(q) { return q === csSeq }
