<template>
    <!-- [カスタムエイド 2026-11-04] 段階9:LINE アプリの中(LIFF)で開くページ。予約WEB で「LINE で受け取る」を押すと、ここが LINE の中で開く
         予約:預けた内容を見せる → 友だちか確かめる → 「予約を確定する」→ 予約完了(お店の LINE からお知らせが届く)
         試し(管理App の「試しに自分の LINE に送る」):LINE で本人を確かめて、テストのお知らせを送る -->
    <view class="lf-page">
        <view class="lf-head">{{ shopName || 'Card-San' }} 予約</view>

        <view v-if="state == 'loading'" class="lf-center">
            <view class="lf-spin"></view>
            <view class="lf-sub">読み込んでいます…</view>
        </view>

        <view v-else-if="state == 'error'" class="lf-card lf-c">
            <view class="lf-t">開けませんでした</view>
            <view class="lf-sub">{{ errMsg }}</view>
        </view>

        <view v-else-if="state == 'test'" class="lf-card lf-c">
            <view class="lf-ok" :class="{ ng: !testSent }">{{ testSent ? '✓' : '!' }}</view>
            <view class="lf-t">{{ testSent ? '送りました' : (friend ? '送れませんでした' : '友だち追加をしてください') }}</view>
            <view class="lf-sub">{{ testSent ? 'お店の LINE のトークに、テストのお知らせが届いたか確かめてください。' : (friend ? 'LINE の今月の上限に達しているか、LINE につながりませんでした。' : 'お店の LINE を友だち追加してから、もう一度お試しください。') }}</view>
            <view v-if="!friend && addUrl" class="lf-btn" @click="openAdd()">友だち追加する</view>
            <view v-if="!friend" class="lf-btn2" @click="check()">追加したら、もう一度確かめる</view>
        </view>

        <block v-else-if="state == 'friend'">
            <view class="lf-card lf-c">
                <view class="lf-av">LINE</view>
                <view class="lf-t">{{ botName }}</view>
                <view class="lf-sub">予約のお知らせは、お店の LINE から届きます。友だち追加をしてから、予約を確定してください。</view>
                <!-- [カスタムエイド 2026-11-04 追記9] パソコン(ブラウザで LINE にログイン)のとき -->
                <view v-if="browser" class="lf-sub">スマホの LINE で QR コードを読んで友だち追加してから、「追加したら、もう一度確かめる」を押してください。</view>
            </view>
            <view class="lf-btn" @click="openAdd()">友だち追加する</view>
            <view class="lf-btn2" @click="check()">追加したら、もう一度確かめる</view>
        </block>

        <block v-else-if="state == 'confirm' || state == 'sending'">
            <view class="lf-card">
                <view class="lf-t lf-tl">この内容で予約します</view>
                <view class="lf-row"><text class="lf-lab">日時</text><text class="lf-val lf-strong">{{ v.time }}</text></view>
                <view class="lf-row"><text class="lf-lab">メニュー</text><text class="lf-val">{{ v.menu }}<text v-if="v.price" class="lf-price">{{ v.price }}</text></text></view>
                <view class="lf-row"><text class="lf-lab">担当</text><text class="lf-val">{{ v.staff }}</text></view>
                <view class="lf-row" v-if="v.appoint_price && (v.appoint_price * 1) > 0"><text class="lf-lab">指名料</text><text class="lf-val">￥{{ v.appoint_price }}</text></view>
                <view class="lf-row"><text class="lf-lab">お名前</text><text class="lf-val">{{ v.name }}</text></view>
                <view class="lf-row"><text class="lf-lab">電話番号</text><text class="lf-val">{{ v.phone }}</text></view>
                <view class="lf-row" v-if="v.remark"><text class="lf-lab">ご要望</text><text class="lf-val">{{ v.remark }}</text></view>
            </view>
            <!-- [カスタムエイド 2026-11-04] 坂本さん:分かりやすい言い方に -->
            <view class="lf-okbox">✓ お店の LINE 公式アカウント「{{ botName }}」を友だち追加済みです。予約のお知らせは、「{{ botName }}」のトーク画面に届きます。</view>
            <view class="lf-btn" :class="{ off: state == 'sending' }" @click="submit()">{{ state == 'sending' ? '予約しています…' : '予約を確定する' }}</view>
            <view class="lf-note">予約確定後のキャンセルは、直接お店にご連絡ください。</view>
        </block>

        <view v-else-if="state == 'done'" class="lf-card lf-c">
            <view class="lf-ok">✓</view>
            <view class="lf-t">予約が完了しました</view>
            <view class="lf-sub">「{{ botName }}」のトーク画面に予約内容をお送りしました。<text v-if="orderNo">予約番号:{{ orderNo }}</text></view>
            <view v-if="!browser" class="lf-btn" @click="close()">閉じる</view>
        </view>
    </view>
</template>

<script>
    import { csLineDraftGet, csLineLiff, setOrder, csLineDone, csLineTicket } from "@/api/index.js"
    const SDK = 'https://static.line-scdn.net/liff/edge/2/sdk.js'
    export default {
        data() {
            return { state: 'loading', errMsg: '', d: '', mode: '', shopName: '', botName: '', addUrl: '', friend: 0, testSent: 0, ticket: '', order: {}, v: {}, orderNo: '', browser: false, sid: 0 }
        },
        onLoad() {
            this.start()
        },
        methods: {
            fail(msg) { this.state = 'error'; this.errMsg = msg },
            // ?d= か、LIFF が付ける liff.state(「?d=…」が入っている)から、下書きの番号を読む
            readD() {
                try {
                    const q = new URLSearchParams(window.location.search)
                    if (q.get('d')) return q.get('d')
                    const st = q.get('liff.state') || ''
                    const m = st.match(/[?&]d=([0-9a-f]{32})/)
                    return m ? m[1] : ''
                } catch (e) { return '' }
            },
            loadSdk() {
                return new Promise((ok, ng) => {
                    if (window.liff) return ok()
                    const s = document.createElement('script')
                    s.src = SDK; s.onload = () => ok(); s.onerror = () => ng()
                    document.head.appendChild(s)
                })
            },
            async start() {
                this.d = this.readD()
                if (!this.d) return this.fail('予約WEB から、もう一度「LINE で受け取る」を押してください。')
                let r
                try { r = await csLineDraftGet(this.d) } catch (e) { r = null }
                if (!r || r.code != 200) return this.fail((r && r.message) || '時間が切れました。予約WEB から、もう一度やり直してください。')
                const x = r.data
                this.mode = x.mode; this.botName = x.bot_name; this.addUrl = x.add_url
                this.order = (x.data && x.data.order) || {}
                this.v = (x.data && x.data.view) || {}
                this.shopName = this.v.shop || x.bot_name || ''
                this.sid = x.sid
                try { document.title = (this.shopName || 'Card-San') + '｜予約' } catch (e) {}
                // [カスタムエイド 2026-11-04 追記9] パソコン:ブラウザの LINE ログインから戻ってきた(?cs_lt= か ?cs_le=)。LIFF は使わない
                let q = null
                try { q = new URLSearchParams(window.location.search) } catch (e) {}
                if (q && (q.get('cs_lt') || q.get('cs_le'))) {
                    this.browser = true
                    if (!q.get('cs_lt')) return this.fail('LINE のログインができませんでした。予約WEB から、もう一度「LINE で受け取る」を押してください。')
                    this.ticket = q.get('cs_lt')
                    return this.check()
                }
                try {
                    await this.loadSdk()
                    await window.liff.init({ liffId: x.liff_id })
                } catch (e) { return this.fail('LINE の準備ができませんでした。LINE アプリで開き直してください。') }
                if (!window.liff.isLoggedIn()) { window.liff.login({ redirectUri: window.location.href }); return }
                this.check()
            },
            // LINE で本人を確かめる(ID トークン)。友だちかどうかも
            async check() {
                if (this.browser) return this.checkBrowser() // [カスタムエイド 2026-11-04 追記9]
                let r
                try { r = await csLineLiff({ d: this.d, id_token: window.liff.getIDToken() || '' }) } catch (e) { r = null }
                if (!r || r.code != 200) return this.fail((r && r.message) || 'LINE で本人の確認ができませんでした。もう一度開き直してください。')
                this.friend = r.data.friend * 1
                if (r.data.add_url) this.addUrl = r.data.add_url
                if (this.mode == 'test') { this.testSent = r.data.sent * 1; this.state = 'test'; return }
                this.ticket = r.data.ticket
                this.state = this.friend ? 'confirm' : 'friend'
            },
            // [カスタムエイド 2026-11-04 追記9] パソコン:ログインのときの合言葉で、友だちか確かめる
            async checkBrowser() {
                let r
                try { r = await csLineTicket(this.sid, this.ticket) } catch (e) { r = null }
                if (!r || r.code != 200) return this.fail('LINE のログインができませんでした。予約WEB から、もう一度「LINE で受け取る」を押してください。')
                this.friend = r.data.friend * 1
                if (r.data.add_url) this.addUrl = r.data.add_url
                if (r.data.bot_name) this.botName = r.data.bot_name
                this.state = this.friend ? 'confirm' : 'friend'
            },
            openAdd() {
                if (!this.addUrl) return
                if (this.browser) { try { window.open(this.addUrl, '_blank') } catch (e) { window.location.href = this.addUrl } return } // [カスタムエイド 2026-11-04 追記9] 新しいタブに QR コード
                try { window.liff.openWindow({ url: this.addUrl, external: false }) } catch (e) { window.location.href = this.addUrl }
            },
            async submit() {
                if (this.state == 'sending') return
                this.state = 'sending'
                let r
                try { r = await setOrder(Object.assign({}, this.order, { cs_ch: 'line', cs_lt: this.ticket })) } catch (e) { r = null }
                if (r && r.code == 200) {
                    this.orderNo = (r.data && r.data.order && r.data.order.order_no) || ''
                    this.state = 'done'
                    // [カスタムエイド 2026-11-04 追記6] 予約WEB の元の画面(Safari など)が、予約完了の画面に進めるように知らせる
                    try { await csLineDone({ d: this.d, order: (r.data && r.data.order) || {} }) } catch (e) {}
                    if (this.browser) this.toCompleted((r.data && r.data.order) || {}) // [カスタムエイド 2026-11-04 追記9] パソコン:そのまま予約完了の画面へ
                    return
                }
                this.state = 'confirm'
                uni.showToast({ title: (r && r.message) || '予約できませんでした。もう一度お試しください', icon: 'none', duration: 2500 })
            },
            // [カスタムエイド 2026-11-04 追記9] 予約WEB の予約完了の画面へ(確認画面の checkLine と同じ)
            toCompleted(o) {
                const g = (k) => (o[k] === undefined || o[k] === null) ? '' : o[k] // checkLine と同じ形で渡す
                try {
                    uni.removeStorageSync("orderTime")
                    uni.setStorageSync("lastOrderImage", this.v.image || '')
                    uni.removeStorageSync("orderItem")
                    uni.setStorageSync("lastOrderEmail", '')
                    uni.setStorageSync("lastOrderSms", '')
                    uni.setStorageSync("lastOrderLine", this.botName || 'お店')
                } catch (e) {}
                uni.redirectTo({
                    url: "/pages/completed/completed?name=" + g('name') + "&order_no=" + g('order_no') + "&start_time=" + g('start_time') + "&amount=" + g('amount') + "&remark=" + g('remark') + "&appoint_name=" + g('appoint_name') + "&menu_name=" + g('menu_name') + "&id=" + (o.sid || this.sid) + '&phone=' + g('phone') + "&appoint_amount=" + g('appoint_amount')
                })
            },
            close() {
                try { window.liff.closeWindow() } catch (e) {}
            }
        }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-11-04] 段階9:LINE の中の予約の画面。カードには影、ボタンの上はほかの間の2倍 */
    .lf-page { min-height: 100vh; background: #F5F5F7; padding: 0 24rpx calc(80rpx + env(safe-area-inset-bottom)); box-sizing: border-box; }
    .lf-head { padding: 36rpx 16rpx 24rpx; font-size: 30rpx; font-weight: 700; color: #1D1D1F; text-align: center; }
    .lf-card { background: #FFFFFF; border-radius: 32rpx; padding: 32rpx; box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.10); }
    .lf-c { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 56rpx 32rpx; }
    .lf-t { font-size: 34rpx; font-weight: 700; color: #1D1D1F; margin-top: 16rpx; }
    .lf-tl { margin: 0 0 8rpx; text-align: left; }
    .lf-sub { font-size: 26rpx; color: #6E6E73; line-height: 1.7; margin-top: 16rpx; }
    .lf-row { display: flex; padding: 24rpx 0; border-bottom: 1px solid #E5E5EA; }
    .lf-row:last-child { border-bottom: 0; }
    .lf-lab { width: 150rpx; flex-shrink: 0; font-size: 26rpx; color: #6E6E73; }
    .lf-val { flex: 1; min-width: 0; font-size: 30rpx; color: #1D1D1F; word-break: break-all; }
    .lf-strong { font-weight: 700; }
    .lf-price { display: block; margin-top: 6rpx; font-size: 26rpx; color: #D93025; }
    .lf-okbox { margin-top: 24rpx; background: #E6F4EA; color: #0D652D; border-radius: 20rpx; padding: 20rpx 24rpx; font-size: 26rpx; line-height: 1.6; font-weight: 600; }
    .lf-btn { margin-top: 48rpx; height: 104rpx; border-radius: 28rpx; background: #06C755; color: #FFFFFF; font-size: 32rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; width: 100%; }
    .lf-btn.off { opacity: 0.6; }
    .lf-btn2 { margin-top: 24rpx; height: 92rpx; border-radius: 28rpx; background: #FFFFFF; border: 1px solid #D2D2D7; color: #05A847; font-size: 28rpx; font-weight: 600; display: flex; align-items: center; justify-content: center; width: 100%; box-sizing: border-box; }
    .lf-note { margin-top: 24rpx; font-size: 24rpx; color: #6E6E73; text-align: center; }
    .lf-ok { width: 96rpx; height: 96rpx; border-radius: 50%; background: #06C755; color: #FFFFFF; font-size: 48rpx; display: flex; align-items: center; justify-content: center; }
    .lf-ok.ng { background: #B3261E; }
    .lf-av { width: 112rpx; height: 112rpx; border-radius: 50%; background: #06C755; color: #FFFFFF; font-size: 26rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .lf-center { display: flex; flex-direction: column; align-items: center; padding-top: 200rpx; }
    .lf-spin { width: 64rpx; height: 64rpx; border-radius: 50%; border: 6rpx solid #D2D2D7; border-top-color: #06C755; animation: lfspin 0.9s linear infinite; }
    @keyframes lfspin { to { transform: rotate(360deg); } }
</style>
