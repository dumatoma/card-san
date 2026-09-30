<template>
    <div>
        <div class="cont_tit">お支払い方法</div>
        <div class="wrap">
            <div class="paytitle">クレジットカード</div>
            <div class="paycontent">
                <div v-if="card" class="card_line">
                    <span class="card_brand">{{card.brand_name}}</span>
                    <span>**** **** **** {{card.last4}}</span>
                    <span class="card_exp">有効期限 {{('0' + card.exp_month).slice(-2)}}/{{String(card.exp_year).slice(-2)}}</span>
                </div>
                <div v-else class="card_none">登録されているカードはありません。</div>
                <div class="note">
                    カード情報はStripe社の安全な決済画面で入力いただき、当社では保存しません。<br />
                    変更後のカードは次回以降のお支払い（プラン更新・差額など）に使用されます。
                </div>
                <div class="fe">
                    <div class="btn" @click="changeCard">{{ card ? 'カードを変更' : 'カードを登録' }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { getVipCard, setupVipCard } from "@/http/api.js"
    // 2026-10: カード番号・CVV を直接入力／保存する旧フォームを廃止（PCI DSS 対応）
    export default {
        data() {
            return { card: null, busy: false }
        },
        created() {
            getVipCard().then((res) => {
                if (res.code == 200) this.card = res.data.card
            }).catch(() => {})
        },
        methods: {
            changeCard() {
                if (this.busy) return
                this.busy = true
                setupVipCard().then((res) => {
                    this.busy = false
                    if (res.code == 200 && res.data.url) {
                        window.location.href = res.data.url
                    } else {
                        this.$message({ message: res.message, type: "error", offset: 400 })
                    }
                }).catch(() => {
                    this.busy = false
                    this.$message({ message: "通信エラーが発生しました。", type: "error", offset: 400 })
                })
            },
        },
    }
</script>

<style lang="scss" scoped>
    .cont_tit { font-size: 21px; color: #1d1d1f; font-weight: bold; margin-top: 20px; }
    .wrap { width: 450px; background: #fff; margin-top: 20px; box-shadow: 0px 3px 10px 1px rgba(0,0,0,0.16); }
    .paytitle { height: 42px; line-height: 42px; background: rgba(26,115,232,0.1); padding: 0 20px; font-size: 14px; color: #1d1d1f; }
    .paycontent { padding: 20px 25px; }
    .card_line { display: flex; align-items: center; gap: 12px; font-weight: bold; font-size: 15px; }
    .card_brand { color: #1a73e8; }
    .card_exp { font-weight: normal; color: #707070; font-size: 12px; }
    .card_none { color: #707070; font-size: 14px; }
    .note { margin: 16px 0; font-size: 12px; color: #707070; line-height: 1.7; }
    .fe { display: flex; flex-direction: row-reverse; }
    .btn { padding: 0 14px; height: 34px; line-height: 34px; background: rgba(29,29,31,0.1); border-radius: 10px; border: 1px solid #707070; font-size: 12px; color: #1d1d1f; cursor: pointer; }
    .btn:hover { opacity: 0.6; }
</style>
