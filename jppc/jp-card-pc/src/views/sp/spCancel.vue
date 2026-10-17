<template>
    <!-- [カスタムエイド 2026-10-17] スマホ・タブレット版 解約手続き(処理はPC版 terminationFulfil.vue と同じ) -->
    <sp-shell mode="sub" :title="step == 1 ? '解約手続き' : '解約手続き完了'" back="/sp/contract" ref="shell">
        <div class="sp-body" v-if="step == 1">
            <section class="sp-card sp-card-pad" style="display: flex; flex-direction: column; gap: 10px; font-size: 14px; line-height: 1.8">
                <p style="margin: 0">本日解約手続きを行うと、<b>{{ date }}</b> をもちまして契約を終了いたします。</p>
                <p style="margin: 0">ご解約しても、<b>契約終了日までアカウントは有効</b>です。</p>
            </section>

            <section style="background: #fdecea; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 8px">
                <h2 style="margin: 0; font-size: 15px; font-weight: 700; color: #a50e0e">残念ですが、解約されてしまうあなたへ。</h2>
                <p style="margin: 0; font-size: 13px; line-height: 1.8; color: #5c1a14">契約終了後には、今まで作成したデータ、コンテンツは全て失われてしまう可能性がございます。いかなる理由があってもデータの復旧は対応いたしかねますので、ご了承ください。</p>
            </section>

            <label class="sp-card" style="padding: 14px 16px; display: flex; align-items: center; gap: 12px; min-height: 44px; cursor: pointer">
                <input type="checkbox" v-model="checked" style="width: 22px; height: 22px; flex-shrink: 0; accent-color: #1a73e8; margin: 0" />
                <span style="font-size: 14px; line-height: 1.6">上記内容を確認した上で、解約を了承しました。</span>
            </label>

            <div class="sp-grid2" style="gap: 12px; margin-top: 8px">
                <button type="button" class="sp-btn" style="height: 52px; border-radius: 26px; background: #fff; color: #1d1d1f; border: 1px solid #d2d2d7; font-weight: 500" @click="$router.push('/sp/contract')">戻る</button>
                <button type="button" class="sp-btn sp-red-btn" style="height: 52px; border-radius: 26px" :disabled="!checked || submitting" @click="doCancel">{{ submitting ? '処理中…' : '解約する' }}</button>
            </div>
        </div>

        <div class="sp-body" v-else>
            <section class="sp-card sp-card-pad" style="display: flex; flex-direction: column; gap: 12px; font-size: 14px; line-height: 1.8; text-align: center">
                <div style="font-size: 17px; font-weight: 700">解約手続きが完了しました。</div>
                <p style="margin: 0">本内容のメールを{{ email }}に送信しました。<br />契約終了日の{{ date }}まで引き続きご利用ができます。</p>
                <p style="margin: 0">ご契約の継続は、いつでも「ご契約内容」から手続きが可能です。</p>
            </section>
            <button type="button" class="sp-btn" @click="$router.push('/sp/contract')">完了</button>
        </div>
    </sp-shell>
</template>

<script>
    import SpShell from "./SpShell.vue"
    import { offtrad } from "@/http/api.js"
    import { adminInfo } from "@/utils/spDevice.js"
    export default {
        components: { SpShell },
        data() {
            return { step: 1, id: "", date: "", checked: false, submitting: false, email: "" }
        },
        created() {
            this.id = this.$route.query.id
            this.date = this.$route.query.refresh || ""
            this.email = adminInfo().email || ""
            if (!this.id) this.$router.replace("/sp/contract")
        },
        methods: {
            doCancel() {
                if (!this.checked || this.submitting) return
                this.submitting = true
                offtrad(this.id).then((res) => {
                    this.submitting = false
                    if (res && res.code == 200) {
                        this.step = 2
                        window.scrollTo(0, 0)
                    } else {
                        this.$refs.shell.toast((res && res.message) || "エラーが発生しました", true)
                    }
                }).catch(() => {
                    this.submitting = false
                    this.$refs.shell.toast("通信エラーが発生しました。ご契約内容で状況をご確認ください。", true)
                })
            }
        }
    }
</script>
