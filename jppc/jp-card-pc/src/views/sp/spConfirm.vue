<template>
    <!-- [カスタムエイド 2026-10-17] スマホ・タブレット版 お申し込み(申し込みの処理はPC版 planconfirm.vue と同じ) -->
    <sp-shell mode="sub" title="お申し込み" back="/sp/plan" ref="shell">
        <div class="sp-body">
            <div class="sp-loading" v-if="!plan">読み込み中…</div>
            <template v-else>
                <section class="sp-card" style="padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; gap: 12px">
                    <div>
                        <div class="sp-cap">お申し込みプラン</div>
                        <div class="sp-val">{{ plan.name }}</div>
                    </div>
                    <button type="button" class="sp-link" style="width: auto; font-size: 13px; font-weight: 500" @click="$router.push('/sp/plan')">プランを変更</button>
                </section>

                <section class="sp-card" style="overflow: hidden">
                    <h2 style="margin: 0; padding: 14px 16px 8px; font-size: 15px; font-weight: 700">お支払い周期を選択</h2>
                    <button v-for="(c, i) in cycles" :key="i" type="button" class="sp-cycle" :class="{ on: cycle == i }" :aria-pressed="cycle == i ? 'true' : 'false'" @click="cycle = i">
                        <span class="sp-radio" :class="{ on: cycle == i }"><span></span></span>
                        <span style="flex: 1; display: flex; flex-direction: column; gap: 2px; text-align: left">
                            <span style="font-size: 15px; font-weight: 700; color: #1d1d1f">{{ c.label }}</span>
                            <span style="font-size: 13px; color: #515154">{{ c.detail }}</span>
                        </span>
                        <span v-if="c.save" style="flex-shrink: 0; font-size: 13px; font-weight: 700; color: #c5221f">{{ c.save }}</span>
                    </button>
                </section>

                <section class="sp-card" style="padding: 14px 16px 16px; display: flex; flex-direction: column; gap: 12px">
                    <h2 style="margin: 0; font-size: 15px; font-weight: 700">お支払い方法</h2>
                    <template v-if="activeStripe">
                        <div v-if="card" class="sp-cardline">
                            <span class="sp-brand">{{ card.brand_name }}</span>
                            <span style="font-size: 15px">**** {{ card.last4 }}</span>
                            <span style="margin-left: auto; font-size: 12px; color: #6e6e73">有効期限 {{ ('0' + card.exp_month).slice(-2) }}/{{ String(card.exp_year).slice(-2) }}</span>
                        </div>
                        <p class="sp-note">ご契約中のクレジットカードで変更されます。カードの変更は「ご契約内容」から行えます。</p>
                    </template>
                    <template v-else>
                        <label v-if="card" class="sp-cardline" :class="{ on: payCard == 'saved' }" style="cursor: pointer">
                            <input type="radio" value="saved" v-model="payCard" style="width: 20px; height: 20px; margin: 0; accent-color: #1a73e8" />
                            <span style="font-size: 14px">登録済みのカード</span>
                            <span class="sp-brand">{{ card.brand_name }}</span>
                            <span style="font-size: 14px">**** {{ card.last4 }}</span>
                        </label>
                        <label class="sp-cardline" :class="{ on: payCard == 'new' || !card }" style="cursor: pointer">
                            <input type="radio" value="new" v-model="payCard" style="width: 20px; height: 20px; margin: 0; accent-color: #1a73e8" />
                            <span style="font-size: 15px; font-weight: 500">{{ card ? '別のクレジットカードで支払う' : 'クレジットカード決済' }}</span>
                        </label>
                        <p class="sp-note" v-if="payCard == 'new' || !card">次の画面で、Stripe社の安全な入力画面に移動してカード情報を入力します。カード情報は当社では保存しません。</p>
                        <p class="sp-yellow-box" style="margin: 0" v-if="lastCardType == 2 || lastCardType == 3">前回は {{ lastCardType == 2 ? 'App Store' : 'Google Play' }} でのご契約でした。クレジットカードでお申し込みの場合、{{ lastCardType == 2 ? 'App Store' : 'Google Play' }} の定期購入が残っていないことをご確認ください。</p>
                    </template>
                </section>

                <section class="sp-card" style="padding: 14px 16px; display: flex; flex-direction: column; gap: 10px">
                    <div style="font-size: 13px; font-weight: 700">ご契約内容</div>
                    <div style="display: flex; justify-content: space-between; font-size: 14px"><span>{{ plan.name }}</span><span style="font-weight: 700">{{ cycles[cycle].total }}</span></div>
                    <div style="display: flex; justify-content: space-between; font-size: 14px; color: #1a73e8"><span>契約更新日</span><span style="font-weight: 700">{{ renewDate }}</span></div>
                </section>

                <p class="sp-note" style="text-align: center">お申し込みにより、<button type="button" class="sp-inline-link" @click="openTerms">利用規約・プライバシーポリシー</button>に同意したものとみなします。</p>

                <button type="button" class="sp-btn" style="height: 52px; border-radius: 26px" :disabled="submitting" @click="submit">{{ submitting ? '処理中…' : ctaLabel }}</button>
            </template>
        </div>

        <div class="sp-mask" v-if="doneMsg">
            <div class="sp-dialog center" role="alertdialog" aria-labelledby="sp-cf-t">
                <div class="sp-dialog-title" id="sp-cf-t">お知らせ</div>
                <div class="sp-dialog-text">{{ doneMsg }}</div>
                <button type="button" class="sp-dbtn" @click="$router.push('/sp/contract')">閉じる</button>
            </div>
        </div>

        <div class="sp-mask" v-if="termsOpen" @click.self="termsOpen = false">
            <div class="sp-dialog" role="dialog" aria-labelledby="sp-tm-t" style="padding: 20px 16px 16px">
                <div class="sp-dialog-title" id="sp-tm-t" style="text-align: center">利用規約・プライバシーポリシー</div>
                <div style="font-size: 13px; font-weight: 700">利用規約</div>
                <div class="sp-terms">{{ terms || '読み込み中…' }}</div>
                <div style="font-size: 13px; font-weight: 700">プライバシーポリシー</div>
                <div class="sp-terms">{{ policy || '読み込み中…' }}</div>
                <button type="button" class="sp-dbtn" @click="termsOpen = false">閉じる</button>
            </div>
        </div>
    </sp-shell>
</template>

<script>
    import SpShell from "./SpShell.vue"
    import { getPackageList, buyPlan, getStore, setpayOrder, getConfig, getVipCard } from "@/http/api.js"
    import { yen } from "@/utils/spDevice.js"
    var MONTHS = [12, 6, 1]
    export default {
        components: { SpShell },
        data() {
            return {
                idx: 0, plan: null, cycle: 0, card: null, payCard: "new",
                vip: {}, vips: [], activeStripe: false, lastCardType: 0, nowSec: 0,
                submitting: false, doneMsg: "", termsOpen: false, terms: "", policy: ""
            }
        },
        computed: {
            cycles() {
                var p = (this.plan && this.plan.price) || [0, 0, 0]
                var one = p[0] * 1, six = p[1] * 1, year = p[2] * 1
                return [
                    { label: '1年', detail: yen(year / 12) + ' × 12ヶ月', save: one * 12 - year > 0 ? yen(one * 12 - year) + ' お得!' : '', total: yen(year) + '(12ヶ月)' },
                    { label: '6ヶ月', detail: yen(six / 6) + ' × 6ヶ月', save: one * 6 - six > 0 ? yen(one * 6 - six) + ' お得!' : '', total: yen(six) + '(6ヶ月)' },
                    { label: '月額', detail: yen(one) + ' × 1ヶ月', save: '', total: yen(one) + '(1ヶ月)' }
                ]
            },
            // 契約更新日 =「次に請求される日」(Stripe の動き。資料「契約更新日の表示ずれ_決済別」)
            //  ・契約なし/無料お試し中/アプリ内課金からの切り替え:今日 + 期間
            //  ・Stripe 契約中のアップグレードで周期も変わる:今日 + 期間
            //  ・Stripe 契約中のそれ以外(周期そのままのアップグレード・ダウングレード・周期だけの変更):今の契約の終了日
            renewDate() {
                var now = this.nowSec || Math.floor(Date.now() / 1000)
                var months = MONTHS[this.cycle]
                var cur = this.vip || {}
                var useEnd = this.activeStripe && cur.end_time
                if (useEnd) {
                    var newType = this.idx * 1 + 1
                    var upgrade = newType > cur.type * 1
                    var cycleChanged = months != cur.month * 1
                    if (upgrade && cycleChanged) useEnd = false
                }
                if (useEnd) return this.fmt(new Date(cur.end_time * 1000))
                var d = new Date(now * 1000)
                d.setMonth(d.getMonth() + months)
                return this.fmt(d)
            },
            ctaLabel() {
                if (!this.activeStripe && (this.payCard == 'new' || !this.card)) return 'カード情報の入力へ進む'
                return 'この内容でお申し込み'
            }
        },
        created() {
            var q = this.$route.query.index
            this.idx = q === undefined || q === '' || isNaN(q * 1) ? 1 : Math.max(0, Math.min(2, q * 1))
            getVipCard().then((res) => {
                if (res && res.code == 200) {
                    this.card = res.data.card
                    if (this.card) this.payCard = "saved"
                }
            }).catch(() => {})
            getStore().then((res) => {
                if (res && res.code == 200) {
                    var s = res.data.shop_info || {}
                    this.vip = s.vip || {}
                    this.vips = s.vips || []
                    this.activeStripe = this.vips.some(v => v.card_type == 1 && v.status == 1 && v.is_try != 1)
                    var lv = s.last_vip
                    this.lastCardType = lv && lv.is_try != 1 ? lv.card_type * 1 : 0
                    if (this.activeStripe && this.vip.month) this.cycle = Math.max(0, MONTHS.indexOf(this.vip.month * 1))
                }
            }).catch(() => {})
            getConfig().then((res) => {
                if (res && res.code == 200) {
                    this.nowSec = res.data.config.timestamp * 1
                    this.terms = res.data.config.shop_service_regulations || ""
                    this.policy = res.data.config.shop_privacy_policy || ""
                }
            }).catch(() => {})
            getPackageList().then((res) => {
                if (res && res.code == 200) {
                    var list = res.data.vip_contract || []
                    this.plan = list[this.idx] || list[0] || null
                } else {
                    this.$refs.shell.toast((res && res.message) || "読み込みに失敗しました", true)
                }
            }).catch(() => {
                this.$refs.shell.toast("読み込みに失敗しました。再読み込みしてください。", true)
            })
        },
        methods: {
            fmt(d) { return d.getFullYear() + '年' + (d.getMonth() + 1) + '月' + d.getDate() + '日' },
            openTerms() { this.termsOpen = true },
            submit() {
                // 二重送信防止。処理の流れはPC版 planconfirm.vue の sub() と同じ
                if (this.submitting) return
                this.submitting = true
                var fail = (msg) => {
                    this.submitting = false
                    this.$refs.shell.toast(msg || "エラーが発生しました", true)
                }
                buyPlan({ card_type: 1, type: this.idx * 1 + 1, month: MONTHS[this.cycle] }).then((res) => {
                    if (res && res.code == 201) {
                        this.submitting = false
                        this.doneMsg = res.message || "継続の手続きが完了しました"
                        return
                    }
                    if (!res || res.code != 200) return fail(res && res.message)
                    var payData = { svid: res.data.svid }
                    if (!this.activeStripe && (this.payCard == "new" || !this.card)) payData.new_card = 1
                    setpayOrder(payData).then((rest) => {
                        if (!rest || rest.code != 200) return fail(rest && rest.message)
                        var d = rest.data || {}
                        if (d.mode == "checkout" && d.url) {
                            window.location.href = d.url
                            return
                        }
                        this.submitting = false
                        this.doneMsg = d.message || rest.message || "お手続きが完了しました"
                    }).catch(() => fail("通信エラーが発生しました。二重申込みを防ぐため、「ご契約内容」で状況をご確認ください。"))
                }).catch(() => fail("通信エラーが発生しました。時間をおいて再度お試しください。"))
            }
        }
    }
</script>

<style>
    .sp-cycle { width: 100%; min-height: 64px; padding: 10px 16px; border: 0; border-top: 1px solid #f0f0f2; display: flex; align-items: center; gap: 12px; background: #fff; }
    .sp-cycle.on { background: #eef4fe; }
    .sp-cardline { display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid #e5e5ea; border-radius: 10px; flex-wrap: wrap; }
    .sp-cardline.on { border: 2px solid #1a73e8; padding: 11px; }
    .sp-brand { padding: 2px 8px; border-radius: 4px; background: #1d1d1f; color: #fff; font-size: 11px; font-weight: 700; }
    .sp-inline-link { border: 0; background: transparent; padding: 0; color: #1a73e8; font-size: 12px; text-decoration: underline; }
    .sp-terms { max-height: 28vh; overflow-y: auto; padding: 10px 12px; background: #f5f5f7; border-radius: 10px; font-size: 12px; line-height: 1.7; white-space: pre-wrap; color: #515154; }
</style>
