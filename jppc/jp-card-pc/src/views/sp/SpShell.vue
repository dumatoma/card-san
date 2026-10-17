<template>
    <!-- [カスタムエイド 2026-10-17] スマホ・タブレット版の共通の枠(ヘッダー・タブ・お知らせ・フッター) -->
    <div class="sp-page">
        <div class="sp-hd-auth" v-if="mode == 'auth'">
            <button type="button" class="sp-hd-back" aria-label="戻る" v-if="back" @click="goBack">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 5l-7 7 7 7"></path></svg>
            </button>
            <img class="sp-logo" :src="logo" alt="Card-San" />
        </div>

        <template v-if="mode == 'main'">
            <div class="sp-hd-main">
                <div style="min-width: 0">
                    <img class="sp-logo" :src="logo" alt="Card-San" />
                    <div class="sp-shop">{{ shopName }}</div>
                </div>
                <button type="button" class="sp-logout" @click="logoutAsk = true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"></path><path d="M10 8l-4 4 4 4"></path><path d="M6 12h10"></path></svg>
                    ログアウト
                </button>
            </div>
            <div class="sp-tabs-wrap">
                <div class="sp-tabs" role="tablist">
                    <button type="button" role="tab" class="sp-tab" :class="{ on: tab == 'owner' }" :aria-selected="tab == 'owner'" @click="go('/sp/owner')">オーナー情報</button>
                    <button type="button" role="tab" class="sp-tab" :class="{ on: tab == 'contract' }" :aria-selected="tab == 'contract'" @click="go('/sp/contract')">ご契約内容</button>
                </div>
            </div>
        </template>

        <div class="sp-hd-sub" v-if="mode == 'sub'">
            <button type="button" class="sp-hd-back" aria-label="戻る" @click="goBack">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 5l-7 7 7 7"></path></svg>
            </button>
            <h1>{{ title }}</h1>
            <div style="width: 44px"></div>
        </div>

        <slot></slot>

        <div class="sp-foot" v-if="foot">
            <button type="button" @click="toPc">PC版の画面を表示する</button>
            <div>Copyright (C) 2022 Customaid:LLC All Rights Reserved</div>
        </div>

        <div class="sp-mask" v-if="logoutAsk" @click.self="logoutAsk = false">
            <div class="sp-dialog center" role="alertdialog" aria-labelledby="sp-lo-t">
                <div class="sp-dialog-title" id="sp-lo-t">ログアウトしてよろしいですか？</div>
                <div class="sp-grid2">
                    <button type="button" class="sp-dbtn gray" @click="logoutAsk = false">キャンセル</button>
                    <button type="button" class="sp-dbtn" @click="logout">ログアウト</button>
                </div>
            </div>
        </div>

        <div class="sp-toast" :class="{ err: toastErr }" v-if="toastMsg" role="status">{{ toastMsg }}</div>
    </div>
</template>

<script>
    import "./sp.css"
    import { setForcePc } from "@/utils/spDevice.js"
    import { getStore } from "@/http/api.js"
    export default {
        name: "SpShell",
        props: {
            mode: { type: String, default: "main" },
            tab: { type: String, default: "" },
            title: { type: String, default: "" },
            back: { type: [String, Boolean], default: false },
            foot: { type: Boolean, default: false }
        },
        data() {
            return {
                logo: require("@/static/sp/sp-logo.svg"),
                shopName: "",
                logoutAsk: false,
                toastMsg: "",
                toastErr: false,
                timer: null
            }
        },
        created() {
            if (this.mode == "main") {
                try { this.shopName = sessionStorage.getItem("sp_shop") || "" } catch (e) {}
                getStore().then((res) => {
                    if (res && res.code == 200 && res.data.shop_info) {
                        this.shopName = res.data.shop_info.shop_name || ""
                        try { sessionStorage.setItem("sp_shop", this.shopName) } catch (e) {}
                    }
                }).catch(() => {})
            }
        },
        beforeDestroy() {
            clearTimeout(this.timer)
        },
        methods: {
            toast(msg, err) {
                this.toastMsg = msg
                this.toastErr = !!err
                clearTimeout(this.timer)
                this.timer = setTimeout(() => { this.toastMsg = "" }, err ? 4000 : 2500)
            },
            go(path) {
                if (this.$route.path != path) this.$router.push(path)
            },
            goBack() {
                if (typeof this.back == "string") this.$router.push(this.back)
                else if (window.history.length > 1) this.$router.go(-1)
                else this.$router.push("/sp/contract")
            },
            logout() {
                try {
                    localStorage.removeItem("token")
                    localStorage.removeItem("admin")
                    sessionStorage.removeItem("sp_shop")
                } catch (e) {}
                window.location.replace(window.location.pathname + "#/sp/login")
                window.location.reload()
            },
            toPc() {
                setForcePc(true)
                window.location.replace(window.location.pathname + "#/")
                window.location.reload()
            }
        }
    }
</script>
