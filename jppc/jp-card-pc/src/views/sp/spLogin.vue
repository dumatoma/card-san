<template>
    <!-- [カスタムエイド 2026-10-17] スマホ・タブレット版 ログイン -->
    <sp-shell mode="auth" :foot="true" ref="shell">
        <div class="sp-body" style="padding-top: 40px; gap: 24px">
            <div style="display: flex; flex-direction: column; gap: 8px">
                <h1 class="sp-h1">ログイン</h1>
                <p class="sp-note" style="font-size: 14px">オーナー情報の確認・変更と、ご契約の手続きができます。</p>
            </div>

            <form class="sp-card" style="padding: 24px 20px; display: flex; flex-direction: column; gap: 20px" @submit.prevent="sendCode">
                <label class="sp-field">
                    <span class="sp-label" style="font-size: 14px">ID(メールアドレス)</span>
                    <input class="sp-input" type="email" inputmode="email" autocomplete="email" v-model.trim="email" placeholder="メールアドレスを入力してください" />
                </label>
                <button type="submit" class="sp-btn" :disabled="sending || !email">{{ sending ? "送信中…" : "認証コードを送信" }}</button>
                <p class="sp-small" style="font-size: 12px">登録されているメールアドレスに、6桁の認証コードをお送りします。</p>
            </form>

            <div class="sp-card" style="padding: 16px; display: flex; gap: 12px; align-items: flex-start">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1a73e8" stroke-width="1.8" aria-hidden="true" style="flex-shrink: 0; margin-top: 2px"><rect x="6" y="2.5" width="12" height="19" rx="2.5"></rect><path d="M10.5 18h3"></path></svg>
                <p class="sp-note">メッセージ配信やデザイン編集などの店舗運営は、専用の管理アプリ「CardSan管理」をご利用ください。</p>
            </div>
        </div>
    </sp-shell>
</template>

<script>
    import SpShell from "./SpShell.vue"
    import { Login } from "@/http/api.js"
    export default {
        components: { SpShell },
        data() {
            return { email: "", sending: false }
        },
        created() {
            if (this.$route.query.email) this.email = this.$route.query.email
        },
        methods: {
            sendCode() {
                if (this.sending || !this.email) return
                this.sending = true
                Login({ login_type: 1, email: this.email, request_type: 1 }).then((res) => {
                    this.sending = false
                    if (res && res.code == 200) {
                        this.$router.push({ path: "/sp/code", query: { email: this.email } })
                    } else {
                        this.$refs.shell.toast((res && res.message) || "送信できませんでした", true)
                    }
                }).catch(() => {
                    this.sending = false
                    this.$refs.shell.toast("通信エラーが発生しました。時間をおいてお試しください。", true)
                })
            }
        }
    }
</script>
