<template>
    <!-- [カスタムエイド 2026-10-17] スマホ・タブレット版 ご契約内容
         処理はPC版(content.vue / topIndex.vue / overagePay.js)と同じAPIを使う。
         ・解約予約中:上に帯と【継続する】(カード決済) / 【継続はこちら】(アプリ内課金)
         ・無料お試し終了/契約期間終了:ヘッダーの下を覆う注意表示
         ・配信数超過:SMSはログイン後に自動でお知らせ。メッセージ・クーポンは「配信数の追加購入」から支払う
         ・アプリ内課金の人:プラン・周期・お支払い方法・解約のボタンで Attention を表示 -->
    <sp-shell mode="main" tab="contract" :foot="true" ref="shell">
        <div class="sp-body" v-if="!loaded">
            <div class="sp-loading">読み込み中…</div>
        </div>
        <div class="sp-body" v-else>
            <!-- 解約予約中 -->
            <section v-if="cancelling" style="background: #fff8e1; border: 1px solid #f0d77a; border-radius: 14px; padding: 14px 16px; display: flex; flex-direction: column; gap: 12px">
                <div style="display: flex; gap: 10px; align-items: flex-start">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8a6d00" stroke-width="2" aria-hidden="true" style="flex-shrink: 0; margin-top: 2px"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v6M12 16.5v.5"></path></svg>
                    <div style="font-size: 14px; line-height: 1.7; color: #5f4b00">ご契約は <b>{{ endDate }}</b> に終了します(解約手続き済み)。<br />終了日までは、すべての機能をご利用いただけます。</div>
                </div>
                <button v-if="isCard" type="button" class="sp-btn" style="height: 46px; font-size: 15px" @click="modal = 'resume'">継続する</button>
                <button v-else type="button" class="sp-btn" style="height: 46px; font-size: 15px" @click="go('/sp/plan')">継続はこちら(WEB決済でお申し込み)</button>
            </section>

            <section class="sp-card" style="overflow: hidden">
                <h2 class="sp-sec-title">ご契約</h2>
                <div class="sp-row">
                    <div>
                        <div class="sp-cap">ご利用中のプラン</div>
                        <div class="sp-val">{{ planLabel }}</div>
                    </div>
                    <button type="button" class="sp-pill" @click="isStore ? (modal = 'plan') : go('/sp/plan')">プランを変更</button>
                </div>
                <div class="sp-row" v-if="!isTrial">
                    <div>
                        <div class="sp-cap">支払い周期</div>
                        <div class="sp-val">{{ vip.month ? vip.month + 'ヶ月' : '-' }}</div>
                    </div>
                    <button type="button" class="sp-pill" @click="isStore ? (modal = 'plan') : go({ path: '/sp/confirm', query: { index: vip.type - 1 } })">周期を変更</button>
                </div>
                <div class="sp-row" style="padding-bottom: 16px">
                    <div>
                        <div class="sp-cap">{{ isTrial ? '無料お試し期限' : cancelling ? '契約終了日' : '契約更新日' }}</div>
                        <div class="sp-val">{{ endDate || '-' }}</div>
                    </div>
                </div>
                <div class="sp-yellow-box" style="margin: 0 16px 16px" v-if="pending">次回更新日から「{{ planName(pending.type) }}({{ pending.month }}ヶ月)」に変更予定です。</div>
                <div class="sp-blue-box" style="margin: 0 16px 16px" v-if="isTrial"><b>無料お試し期間中</b><br />アカウント作成から30日間は全てのプランが無料お試し期間の対象です。顧客登録数が30人以下の場合は、最大30日無料期間が延長されます。</div>
            </section>

            <section class="sp-card" style="overflow: hidden">
                <h2 class="sp-sec-title">お支払い方法</h2>
                <div v-if="isCard" style="padding: 8px 16px 16px; display: flex; flex-direction: column; gap: 12px">
                    <div style="font-size: 15px; font-weight: 500">クレジットカード決済</div>
                    <div v-if="card" style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid #e5e5ea; border-radius: 10px">
                        <span style="padding: 2px 8px; border-radius: 4px; background: #1d1d1f; color: #fff; font-size: 11px; font-weight: 700">{{ card.brand_name }}</span>
                        <span style="font-size: 15px; letter-spacing: 0.04em">**** {{ card.last4 }}</span>
                        <span style="margin-left: auto; font-size: 12px; color: #6e6e73">有効期限 {{ ('0' + card.exp_month).slice(-2) }}/{{ String(card.exp_year).slice(-2) }}</span>
                    </div>
                    <button type="button" class="sp-btn-line" :disabled="busy" @click="changeCard">カードを変更</button>
                    <p class="sp-small">カード情報はStripe社の安全な画面で入力します。当社では保存しません。</p>
                </div>
                <div v-else-if="isStore" style="padding: 8px 16px 16px; display: flex; flex-direction: column; gap: 12px">
                    <div style="font-size: 15px; font-weight: 500">{{ vip.card_type == 2 ? 'App Store課金' : 'Google Play課金' }}</div>
                    <button type="button" class="sp-btn-line" @click="modal = 'pay'">お支払い方法の変更</button>
                </div>
                <div v-else style="padding: 8px 16px 16px; font-size: 14px; color: #515154">{{ isTrial ? '無料お試し中のため、お支払い方法の登録はありません。' : '-' }}</div>
            </section>

            <!-- 配信数の追加購入(カード決済の契約中のみ) -->
            <section class="sp-card" style="overflow: hidden" v-if="isCard && !expired">
                <h2 class="sp-sec-title">配信数の追加購入</h2>
                <p class="sp-note" style="padding: 0 16px 8px">管理アプリで「配信数超過のお知らせ」が出たときは、ここからお支払いできます。お支払い後、管理アプリからそのまま配信できます。</p>
                <div class="sp-row" v-for="o in overageList" :key="o.type">
                    <div style="min-width: 0">
                        <div style="font-size: 15px; font-weight: 700">{{ o.label }}</div>
                        <div class="sp-cap">{{ o.detail }}</div>
                    </div>
                    <button type="button" class="sp-pill" @click="openOverage(o.type)">購入する</button>
                </div>
            </section>

            <button v-if="showCancel && isCard" type="button" class="sp-link red" @click="toCancel">解約手続き</button>
            <button v-if="showCancel && isStore" type="button" class="sp-link red" @click="modal = 'cancel'">解約手続き</button>
        </div>

        <!-- 期間終了(ヘッダーの下を覆う。ログアウトは押せる) -->
        <div class="sp-mask under-header" v-if="loaded && expired">
            <div class="sp-dialog center" role="alertdialog" aria-labelledby="sp-exp-t" style="padding-top: 28px">
                <div class="sp-dialog-title" id="sp-exp-t" style="font-size: 17px; color: #c5221f">{{ trialEnded ? '無料トライアル期間終了のため、現在ご利用できません。' : 'ご契約期間終了のため、現在ご利用できません。' }}</div>
                <div style="font-size: 14px; line-height: 1.7">{{ trialEnded ? 'お申し込み手続きをすることで、すぐにご利用ができます。' : 'お申し込みを再開することで、すぐにご利用ができます。' }}</div>
                <button type="button" class="sp-btn" style="margin-top: 6px" @click="go('/sp/plan')">{{ trialEnded ? 'お申し込みはこちら' : '再開はこちら' }}</button>
            </div>
        </div>

        <!-- 配信数超過のお知らせ / 追加購入の確認 -->
        <div class="sp-mask" v-if="modal == 'overage'">
            <div class="sp-dialog" role="alertdialog" aria-labelledby="sp-ov-t">
                <div class="sp-dialog-title" id="sp-ov-t" style="color: #e02020; text-align: center">{{ ov.title }}</div>
                <div class="sp-dialog-text" style="color: #1d1d1f">{{ ov.body }}</div>
                <div style="text-align: center; font-size: 14px">利用料金:<b>{{ ov.price }}</b></div>
                <button type="button" class="sp-dbtn" :disabled="busy" @click="payOverage">{{ busy ? '処理中…' : 'お支払い' }}</button>
                <button type="button" class="sp-dbtn gray" @click="closeModal">キャンセル</button>
                <p class="sp-small">お支払いはStripe社の安全な画面で行います。</p>
            </div>
        </div>

        <!-- 継続の確認 -->
        <div class="sp-mask" v-if="modal == 'resume'">
            <div class="sp-dialog center" role="alertdialog" aria-labelledby="sp-rs-t">
                <div class="sp-dialog-title" id="sp-rs-t">ご契約を継続しますか？</div>
                <div class="sp-dialog-text">解約手続きを取り消し、現在のプランを継続します。
新たな料金は発生しません。次回更新日以降も自動更新されます。</div>
                <div class="sp-grid2">
                    <button type="button" class="sp-dbtn gray" @click="closeModal">キャンセル</button>
                    <button type="button" class="sp-dbtn" :disabled="busy" @click="doResume">継続する</button>
                </div>
            </div>
        </div>

        <!-- お知らせ(完了など) -->
        <div class="sp-mask" v-if="modal == 'result'">
            <div class="sp-dialog center" role="alertdialog" aria-labelledby="sp-rt-t">
                <div class="sp-dialog-title" id="sp-rt-t">{{ resultTitle }}</div>
                <div class="sp-dialog-text">{{ resultMsg }}</div>
                <button type="button" class="sp-dbtn" @click="closeModal">閉じる</button>
            </div>
        </div>

        <!-- アプリ内課金の人向け Attention -->
        <div class="sp-mask" v-if="att">
            <div class="sp-att" role="alertdialog" aria-labelledby="sp-at-t">
                <div class="sp-att-title" id="sp-at-t">{{ att.title }}</div>
                <div class="sp-att-body">
                    <div class="sp-att-warn">{{ att.warn }}</div>
                    <div class="sp-att-note">{{ att.note }}</div>
                    <button type="button" class="sp-att-ok" @click="closeModal">上記内容を確認しました</button>
                </div>
            </div>
        </div>
    </sp-shell>
</template>

<script>
    import SpShell from "./SpShell.vue"
    import { getStore, getVipCard, setupVipCard, resumePlan } from "@/http/api.js"
    import { buyOverage } from "@/utils/overagePay.js"
    import { planName, fmtDate, yen } from "@/utils/spDevice.js"

    var NOTE_CHANGE = 'ご契約をWEB決済に変更することで、このウェブサイトから、全ての手続きが簡単になります。\n\n【支払い方法の変更方法】\niOSもしくはAndroidのデバイスから、ご契約中のサブスクリプションの解約、もしくは定期購入を解約をします。\n\n契約解除日に、契約期限切れのアテンションが表示されますので、このウェブサイトから支払い方法の変更が可能です。一旦契約を解除してもアカウント情報は引き継がれますので、運用には影響はございません。'
    var NOTE_CANCEL = '【App課金でご契約の場合】\n「設定」アプリの「アカウント設定」を選択します。下にスクロールして「サブスクリプション」から「CardSan管理」を選択します。「サブスクリプションをキャンセル」を選択します。\n\n【Google Play Store課金でご契約の場合】\n「Playストア」アプリを開き、画面左上の[メニュー](三本線のマーク)アイコンを選択。[定期購入]から「CardSan管理」を選択し、[定期購入を解約]を選択します。'
    var ATT = {
        plan: { title: 'プラン・支払い周期の変更について', warn: 'プラン・支払い周期の変更の手続きは、お手数ではございますが、サブスクリプション契約をしたデバイスからお手続きをお願いします。', note: NOTE_CHANGE },
        pay: { title: 'お支払い方法の変更について', warn: 'App Store課金、Google Play課金でご契約の方は、このウェブサイトからお支払い方法の変更ができません。', note: NOTE_CHANGE },
        cancel: { title: '解約手続きについて', warn: '解約手続きは、お手数ではございますが、サブスクリプション契約をしたデバイスからお手続きをお願いします。', note: NOTE_CANCEL },
        apppay: { title: 'お支払いについて', warn: 'お支払いについては、お手数ではございますが、Card-San管理アプリからご契約時の決済方法でお支払いをお願いします。', note: 'App Store、Google Play決済でご契約している場合は、Card-San管理アプリのトップ画面に表示されているアテンションから課金手続きをお願いします。そこからお支払い手続きをすることができます。' }
    }

    export default {
        components: { SpShell },
        data() {
            return {
                loaded: false,
                vip: {},
                vips: [],
                lastTry: 0,
                card: null,
                pending: null,
                smsAlert: false,
                smsConfig: null,
                modal: "",
                ovType: 0,
                resultTitle: "",
                resultMsg: "",
                busy: false
            }
        },
        computed: {
            expired() { return !(this.vip && this.vip.type * 1 > 0) },
            trialEnded() { return this.lastTry == 1 },
            isTrial() { return !this.expired && this.vip.is_try == 1 },
            isCard() { return !this.expired && !this.isTrial && this.vip.card_type == 1 },
            isStore() { return !this.expired && !this.isTrial && (this.vip.card_type == 2 || this.vip.card_type == 3) },
            cancelling() { return !this.expired && this.vips.length > 0 && this.vips[0].cancel_time != 0 },
            showCancel() { return !this.expired && this.vips.length > 0 && this.vips[0].cancel_time == 0 && !this.isTrial },
            planLabel() { return this.expired ? 'なし' : planName(this.vip.type) },
            endDate() { return this.expired ? '' : fmtDate(this.vip.end_time) },
            smsPrice() { return this.smsConfig && this.smsConfig.price ? yen(this.smsConfig.price) : '¥1,200' },
            smsCount() { return this.smsConfig && this.smsConfig.count ? this.smsConfig.count : 100 },
            overageList() {
                return [
                    { type: 1, label: 'メッセージ +3,000通', detail: '¥1,200(今月の配信数を3,000通追加)' },
                    { type: 2, label: 'クーポン +3,000通', detail: '¥1,200(今月の配信数を3,000通追加)' },
                    { type: 3, label: '予約SMS +' + this.smsCount + '通', detail: this.smsPrice + '(SMS通知を' + this.smsCount + '通追加)' }
                ]
            },
            ov() {
                if (this.ovType == 3) {
                    return {
                        title: this.smsAlert ? 'SMS通知の配信数超過のお知らせ' : '予約SMSの追加購入',
                        body: (this.smsAlert ? 'SMS通知数が配信上限数を超過しました。追加配信利用料のお支払いをお願いします。\n' : '') + '利用料(' + this.smsPrice + ')で、更に' + this.smsCount + '通のSMS通知を配信できます。お支払い完了後に、管理アプリのお知らせも消えます。',
                        price: this.smsPrice
                    }
                }
                var w = this.ovType == 1 ? 'メッセージ' : 'クーポン'
                return {
                    title: w + 'の追加購入',
                    body: w + '配信数が配信上限(3,000通/月)を超過した場合は、追加利用料のお支払いで、更に3,000通分の' + w + '配信が可能になります。',
                    price: '¥1,200'
                }
            },
            att() { return ATT[this.modal] || null }
        },
        created() {
            this.load(true)
            this.handleStripeReturn()
        },
        methods: {
            planName: planName,
            go(to) { this.$router.push(to) },
            load(first) {
                getStore().then((res) => {
                    if (res && res.code == 200) {
                        var s = res.data.shop_info || {}
                        this.vip = s.vip || {}
                        this.vips = s.vips || []
                        this.lastTry = s.last_vip_is_try
                        this.pending = this.vips.find(v => v.status == 0 && v.card_type == 1) || null
                        this.smsAlert = s.vip_sms_alert == 1
                        this.smsConfig = res.data.vip_sms_config || null
                        this.loaded = true
                        // SMS の配信数超過はログイン後に自動で表示(期間終了の注意が出ているときは出さない)
                        if (first && this.smsAlert && !this.expired && !this.modal) {
                            var seen = false
                            try { seen = sessionStorage.getItem("sp_sms_seen") == "1" } catch (e) {}
                            if (!seen) { this.ovType = 3; this.modal = "overage" }
                        }
                    } else {
                        this.loaded = true
                    }
                }).catch(() => {
                    this.loaded = true
                    this.$refs.shell && this.$refs.shell.toast("読み込みに失敗しました。再読み込みしてください。", true)
                })
                getVipCard().then((res) => {
                    if (res && res.code == 200) this.card = res.data.card
                }).catch(() => {})
            },
            // Stripe の決済画面/カード登録画面から戻ってきた場合(PC版 content.vue と同じ)
            handleStripeReturn() {
                var q = window.location.search || ""
                if (q.indexOf("stripe=") < 0) return
                var kind = (q.match(/stripe=(\w+)/) || [])[1]
                try { window.history.replaceState(null, "", window.location.pathname + window.location.hash) } catch (e) {}
                try { sessionStorage.setItem("sp_sms_seen", "1") } catch (e) {}
                if (kind == "success") {
                    this.showResult("お知らせ", "お支払いを受け付けました。反映まで数十秒かかる場合があります。")
                    var n = 0
                    var timer = setInterval(() => {
                        n++
                        this.load(false)
                        if (n >= 4) clearInterval(timer)
                    }, 4000)
                } else if (kind == "card") {
                    this.showResult("お知らせ", "お支払い方法(クレジットカード)を更新しました。")
                    setTimeout(() => this.load(false), 3000)
                }
            },
            showResult(title, msg) {
                this.resultTitle = title
                this.resultMsg = msg
                this.modal = "result"
            },
            closeModal() {
                if (this.modal == "overage" && this.ovType == 3) {
                    try { sessionStorage.setItem("sp_sms_seen", "1") } catch (e) {}
                }
                this.modal = ""
            },
            openOverage(type) {
                this.ovType = type
                this.modal = "overage"
            },
            payOverage() {
                if (!this.isCard) {
                    this.modal = "apppay"
                    return
                }
                if (this.busy) return
                this.busy = true
                try { sessionStorage.setItem("sp_sms_seen", "1") } catch (e) {}
                buyOverage(this.ovType).then((moved) => {
                    this.busy = false
                    if (!moved) this.modal = ""
                }).catch(() => { this.busy = false })
            },
            changeCard() {
                if (this.busy) return
                this.busy = true
                setupVipCard().then((res) => {
                    this.busy = false
                    if (res && res.code == 200 && res.data.url) window.location.href = res.data.url
                    else this.$refs.shell.toast((res && res.message) || "エラーが発生しました", true)
                }).catch(() => {
                    this.busy = false
                    this.$refs.shell.toast("通信エラーが発生しました。", true)
                })
            },
            doResume() {
                if (this.busy || !this.vips.length) return
                this.busy = true
                resumePlan(this.vips[0].id).then((res) => {
                    this.busy = false
                    if (res && res.code == 200) {
                        this.showResult("お知らせ", res.message || "継続の手続きが完了しました")
                        this.load(false)
                    } else {
                        this.modal = ""
                        this.$refs.shell.toast((res && res.message) || "エラーが発生しました", true)
                    }
                }).catch(() => {
                    this.busy = false
                    this.modal = ""
                    this.$refs.shell.toast("通信エラーが発生しました。ご契約内容を再読み込みしてご確認ください。", true)
                })
            },
            toCancel() {
                this.$router.push({ path: "/sp/cancel", query: { id: this.vips[0].id, refresh: this.endDate } })
            }
        }
    }
</script>
