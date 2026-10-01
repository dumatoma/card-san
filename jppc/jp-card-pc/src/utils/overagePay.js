// 配信数超過（メッセージ / クーポン / SMS）の追加購入と決済Attention（決済Attention.pdf）
//  - Stripe 契約          → Stripe 決済ページ（商品カタログの価格ID）へ移動
//  - App / Google 契約    → 「管理Appから課金」の Attention
//  - 副管理者             → 「実行する権限がありません」
import { MessageBox, Message } from 'element-ui'
import { getExtra, topay } from '@/http/api.js'

function ensureStyle() {
    if (document.getElementById('pay-attention-style')) return
    const st = document.createElement('style')
    st.id = 'pay-attention-style'
    st.textContent = `
.pay-attention { width: 520px; max-width: calc(100vw - 32px); padding-bottom: 24px; }
.pay-attention .el-message-box__header { background: #f5f5f7; padding: 14px 16px; }
.pay-attention .el-message-box__title { justify-content: center; font-size: 15px; }
.pay-attention .pa-warn { background: rgba(230, 191, 24, .45); padding: 14px 16px; font-weight: bold; font-size: 14px; line-height: 1.7; color: #1d1d1f; }
.pay-attention .pa-note { border: 1px solid #d2d2d7; padding: 14px 16px; margin-top: 16px; font-size: 13px; line-height: 1.8; color: #1d1d1f; }
.pay-attention .el-message-box__btns { text-align: center; }
.pay-attention .pay-attention-btn { background: #19c45a; border-color: #19c45a; border-radius: 20px; padding: 10px 48px; font-weight: bold; }
`
    document.head.appendChild(st)
}

export function showAppPayAttention() {
    ensureStyle()
    return MessageBox({
        title: 'お支払いについて',
        message: '<div class="pa-warn">お支払いについては、お手数ではございますが、Card-San管理アプリからご契約時の決済方法でお支払いをお願いします。</div>' +
            '<div class="pa-note">App Store、Google Play決済でご契約している場合は、Card-San管理アプリのトップ画面に表示されているアテンションから課金手続きをお願いします。そこからお支払い手続きをすることができます。</div>',
        dangerouslyUseHTMLString: true,
        confirmButtonText: '上記内容を確認しました',
        confirmButtonClass: 'pay-attention-btn',
        customClass: 'pay-attention',
        showClose: false,
    }).catch(() => {})
}

export function showNoPermission() {
    Message({ message: '実行する権限がありません', type: 'error', offset: 400 })
}

function fail(res) {
    const at = res && res.data && res.data.attention
    if (at === 'app') return showAppPayAttention()
    if (at === 'permission') return showNoPermission()
    Message({ message: (res && res.message) || 'エラーが発生しました', type: 'error', offset: 400 })
}

let busy = false

/**
 * @param {number} type 1=メッセージ 2=クーポン 3=SMS
 * @returns {Promise<boolean>} 決済ページへ移動したら true
 */
export function buyOverage(type) {
    let admin = {}
    try { admin = JSON.parse(localStorage.getItem('admin')) || {} } catch (e) {}
    if (admin.admin_type && admin.admin_type != 1) {
        showNoPermission()
        return Promise.resolve(false)
    }
    if (busy) return Promise.resolve(false)
    busy = true
    return getExtra({ type, card_type: 1 }).then((res) => {
        if (!res || res.code != 200) { fail(res); return false }
        return topay({ order_no: res.data.order_no }).then((rest) => {
            if (!rest || rest.code != 200 || !rest.data.url) { fail(rest); return false }
            // 同じタブで Stripe 決済ページへ（非同期後の window.open はブラウザにブロックされることがあるため）
            window.location.href = rest.data.url
            return true
        })
    }).catch(() => {
        Message({ message: '通信エラーが発生しました', type: 'error', offset: 400 })
        return false
    }).finally(() => { busy = false })
}
