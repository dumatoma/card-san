// [カスタムエイド 2026-10-26] 変換中(下線が付いている文字)のまま送信ボタンを押したときの対策(チャット4画面で共通)。
//   以前は、変換が確定する前までの文字だけが送られ、変換中の文字を含む文章が入力欄に残っていた。
//   入力欄に :ignore-composition-event="false" を付けて変換中の文字も含めて受け取り(全部送る)、
//   送信の直後に、変換が確定して入力欄に戻ってきた文字(送った文章の一部)は消す(LINE と同じく入力欄は空に)。
export function csMarkSent(vm, text) {
    vm._csSent = { t: String(text || ''), at: Date.now() }
}
// 送信してすぐ(1.5秒以内)に入力欄に入ってきた文字が、送った文章の残り(同じ・最後の部分)なら true
export function csIsLeftover(vm, value) {
    const s = vm._csSent
    if (!s || Date.now() - s.at > 1500) return false
    const v = String(value || '')
    return !!v && s.t.slice(-v.length) === v
}
