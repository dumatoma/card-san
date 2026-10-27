// [カスタムエイド 2026-10-27] 今開いているチャットをサーバーに知らせる(その相手からの通知は届かなくなる。LINE と同じ)
//   画面を開いている間は30秒ごとに知らせる(サーバーは45秒で忘れる)。画面を閉じたり、アプリを裏に回したりしたら、すぐ取り消す
import { csViewing } from '@/api/index.js'
export function csViewStart(vm, view) {
    csViewStop(vm, true)
    if (!view) return
    const send = () => { try { csViewing(view).catch(() => {}) } catch (e) {} }
    send()
    vm._csViewTimer = setInterval(send, 30000)
}
export function csViewStop(vm, quiet) {
    if (!vm._csViewTimer) return
    clearInterval(vm._csViewTimer)
    vm._csViewTimer = null
    if (!quiet) { try { csViewing('').catch(() => {}) } catch (e) {} }
}
