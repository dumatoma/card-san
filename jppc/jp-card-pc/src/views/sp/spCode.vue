<template>
    <!-- [カスタムエイド 2026-10-17] スマホ・タブレット版 認証コード入力 -->
    <sp-shell mode="auth" back="/sp/login" ref="shell">
        <div class="sp-body" style="padding-top: 32px; gap: 24px">
            <div style="display: flex; flex-direction: column; gap: 8px">
                <h1 class="sp-h1">認証コードを入力</h1>
                <p class="sp-note" style="font-size: 14px">次のメールアドレスに送信した、6桁の認証コードを入力してください。</p>
                <p style="margin: 0; font-size: 15px; font-weight: 500; word-break: break-all">{{ email }}</p>
                <p style="margin: 0; font-size: 13px; font-weight: 700; color: #515154">認証コードは10分間有効です</p>
            </div>

            <form class="sp-card" style="padding: 24px 16px; display: flex; flex-direction: column; gap: 20px" @submit.prevent="submit">
                <div class="sp-field" style="gap: 10px">
                    <span class="sp-label" style="font-size: 14px" id="sp-code-l">認証コード(6桁)</span>
                    <div class="sp-code">
                        <div class="sp-code-box" v-for="i in 6" :key="i" :class="{ cur: focused && i - 1 == Math.min(code.length, 5) }">{{ code.charAt(i - 1) }}</div>
                        <input class="sp-code-input" ref="inp" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" aria-labelledby="sp-code-l"
                            :value="code" @input="onInput" @focus="focused = true" @blur="focused = false" />
                    </div>
                </div>
                <button type="submit" class="sp-btn" :disabled="code.length != 6 || busy">{{ busy ? "確認中…" : "ログイン" }}</button>
            </form>

            <p class="sp-note">数分経ってもコードが届かない場合は、認証コードを再送するか、メールアドレスの入力からやり直してください。</p>
            <button type="button" class="sp-link" :disabled="resending" @click="resend">{{ resending ? "送信中…" : "認証コードを再送" }}</button>
        </div>
    </sp-shell>
</template>

<script>
    import SpShell from "./SpShell.vue"
    import { Login } from "@/http/api.js"
    export default {
        components: { SpShell },
        data() {
            return { email: "", code: "", focused: false, busy: false, resending: false }
        },
        created() {
            this.email = this.$route.query.email || ""
            if (!this.email) this.$router.replace("/sp/login")
        },
        mounted() {
            this.$nextTick(() => { this.$refs.inp && this.$refs.inp.focus() })
        },
        methods: {
            onInput(e) {
                var v = (e.target.value || "").replace(/\D/g, "").slice(0, 6)
                this.code = v
                e.target.value = v
                if (v.length == 6) this.submit()
            },
            submit() {
                if (this.code.length != 6 || this.busy) return
                this.busy = true
                Login({ login_type: 1, email: this.email, phone: this.email, request_type: 2, code: this.code }).then((res) => {
                    if (res && res.code == 200) {
                        localStorage.setItem("admin", JSON.stringify(res.data.admin))
                        localStorage.setItem("token", res.data.token)
                        localStorage.setItem("ale", 1)
                        // ログイン後は「ご契約内容」へ(期間終了・配信超過のお知らせもここで表示する)
                        window.location.replace(window.location.pathname + "#/sp/contract")
                        window.location.reload()
                    } else {
                        this.busy = false
                        this.code = ""
                        this.$refs.shell.toast((res && res.message) || "認証コードが正しくありません", true)
                    }
                }).catch(() => {
                    this.busy = false
                    this.$refs.shell.toast("通信エラーが発生しました。時間をおいてお試しください。", true)
                })
            },
            resend() {
                if (this.resending) return
                this.resending = true
                Login({ login_type: 1, email: this.email, request_type: 1 }).then((res) => {
                    this.resending = false
                    if (res && res.code == 200) this.$refs.shell.toast("認証コードを再送しました")
                    else this.$refs.shell.toast((res && res.message) || "送信できませんでした", true)
                }).catch(() => {
                    this.resending = false
                    this.$refs.shell.toast("通信エラーが発生しました。", true)
                })
            }
        }
    }
</script>
