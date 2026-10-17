<template>
    <!-- [カスタムエイド 2026-10-17] スマホ・タブレット版 オーナー情報(処理はPC版 userInfo.vue と同じAPI) -->
    <sp-shell mode="main" tab="owner" :foot="true" ref="shell">
        <div class="sp-body" v-if="step == 1">
            <div class="sp-card sp-card-pad" style="display: flex; flex-direction: column; gap: 10px">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px">
                    <div style="min-width: 0">
                        <div class="sp-cap">ID(メールアドレス)</div>
                        <div style="font-size: 15px; font-weight: 500; word-break: break-all">{{ email }}</div>
                    </div>
                    <button type="button" class="sp-pill" style="min-height: 36px" @click="step = 2">変更</button>
                </div>
                <div style="height: 1px; background: #ececf0"></div>
                <div>
                    <div class="sp-cap">契約番号</div>
                    <div style="font-size: 15px; font-weight: 500">{{ number || "-" }}</div>
                </div>
            </div>

            <div class="sp-card" style="padding: 20px 16px; display: flex; flex-direction: column; gap: 18px">
                <label class="sp-field">
                    <span class="sp-label"><span class="sp-req">※</span>氏名</span>
                    <input class="sp-input" type="text" autocomplete="name" v-model="name" placeholder="オーナーの氏名を入力" />
                </label>
                <label class="sp-field">
                    <span class="sp-label">法人名</span>
                    <input class="sp-input" type="text" autocomplete="organization" v-model="frname" />
                </label>
                <label class="sp-field">
                    <span class="sp-label"><span class="sp-req">※</span>店舗名</span>
                    <input class="sp-input" type="text" v-model="shopName" />
                </label>
                <label class="sp-field">
                    <span class="sp-label"><span class="sp-req">※</span>電話番号</span>
                    <input class="sp-input" type="tel" inputmode="tel" autocomplete="tel" v-model="phone" placeholder="0312345678" />
                </label>
                <div class="sp-field">
                    <label class="sp-label" for="sp-zip"><span class="sp-req">※</span>郵便番号</label>
                    <div style="display: flex; gap: 8px">
                        <input id="sp-zip" class="sp-input" style="flex: 1; min-width: 0" type="text" inputmode="numeric" autocomplete="postal-code" v-model="zip_code" />
                        <button type="button" style="flex-shrink: 0; height: 48px; padding: 0 16px; border: 0; border-radius: 10px; background: #e8f0fe; color: #1a73e8; font-size: 14px; font-weight: 500" @click="searchAddress">住所検索</button>
                    </div>
                </div>
                <p class="sp-gray-box" style="margin: 0">個人:オーナーの住所または店舗所在地の住所をご記載ください。<br />法人:法人登記住所または店舗所在地の住所をご記載ください。</p>
                <label class="sp-field">
                    <span class="sp-label"><span class="sp-req">※</span>都道府県</span>
                    <select class="sp-input" v-model="province">
                        <option value="" disabled>都道府県</option>
                        <option v-for="c in city" :key="c" :value="c">{{ c }}</option>
                    </select>
                </label>
                <label class="sp-field">
                    <span class="sp-label"><span class="sp-req">※</span>住所1</span>
                    <input class="sp-input" type="text" v-model="address1" placeholder="市区町村・番地" />
                </label>
                <label class="sp-field">
                    <span class="sp-label">住所2</span>
                    <input class="sp-input" type="text" v-model="address2" placeholder="その他住所、建物名、号室など" />
                </label>
            </div>

            <button type="button" class="sp-btn" style="height: 52px; border-radius: 26px" :disabled="saving" @click="save">{{ saving ? "保存中…" : "保存" }}</button>
        </div>

        <!-- ID(メールアドレス)の変更 -->
        <div class="sp-body" v-if="step == 2">
            <h1 class="sp-h1" style="font-size: 20px">ID(登録メールアドレス)の変更</h1>
            <div class="sp-card sp-card-pad" style="display: flex; flex-direction: column; gap: 16px">
                <div>
                    <div class="sp-cap">現在のID(メールアドレス)</div>
                    <div style="font-size: 15px; font-weight: 500; word-break: break-all">{{ email }}</div>
                </div>
                <p class="sp-note">変更後のメールアドレスを入力して「メールアドレス(ID)の変更」を押してください。6桁の「メールアドレス変更認証コード」を記載したEメールをお送りします。</p>
                <label class="sp-field">
                    <span class="sp-label">新しいメールアドレス(ID)</span>
                    <input class="sp-input" type="email" inputmode="email" autocomplete="email" v-model.trim="newEmail" placeholder="メールアドレスを入力してください" />
                </label>
                <button type="button" class="sp-btn" :disabled="!newEmail || busy" @click="sendEmailCode">メールアドレス(ID)の変更</button>
            </div>
            <button type="button" class="sp-link" @click="step = 1">キャンセル</button>
        </div>

        <div class="sp-body" v-if="step == 3">
            <h1 class="sp-h1" style="font-size: 20px">認証コードを入力</h1>
            <p class="sp-note" style="font-size: 14px">新しいメールアドレスに送信した6桁の認証コードを入力してください。</p>
            <p style="margin: 0; font-size: 15px; font-weight: 500; word-break: break-all">{{ newEmail }}</p>
            <p style="margin: 0; font-size: 13px; font-weight: 700; color: #515154">認証コードは10分間有効です</p>
            <div class="sp-card" style="padding: 24px 16px; display: flex; flex-direction: column; gap: 20px">
                <div class="sp-code">
                    <div class="sp-code-box" v-for="i in 6" :key="i" :class="{ cur: i - 1 == Math.min(code.length, 5) }">{{ code.charAt(i - 1) }}</div>
                    <input class="sp-code-input" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" aria-label="認証コード(6桁)" :value="code" @input="onCode" />
                </div>
                <button type="button" class="sp-btn" :disabled="code.length != 6 || busy" @click="confirmEmail">メールアドレス(ID)の変更を認証</button>
            </div>
            <button type="button" class="sp-link" :disabled="busy" @click="sendEmailCode">認証コードを再送</button>
            <button type="button" class="sp-link" style="color: #515154" @click="step = 1">キャンセル</button>
        </div>
    </sp-shell>
</template>

<script>
    import SpShell from "./SpShell.vue"
    import { getUserINfo, gatZipAddress, editUser, changeEmail, getStore } from "@/http/api.js"
    import { adminInfo } from "@/utils/spDevice.js"
    export default {
        components: { SpShell },
        data() {
            return {
                step: 1,
                email: "", number: "", name: "", frname: "", shopName: "", phone: "", zip_code: "", province: "", address1: "", address2: "",
                newEmail: "", code: "", busy: false, saving: false,
                city: ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都',
                    '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府',
                    '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県',
                    '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県']
            }
        },
        created() {
            this.email = adminInfo().email || ""
            this.load()
            getStore().then((res) => {
                if (res && res.code == 200) this.number = res.data.shop_info.number || ""
            }).catch(() => {})
        },
        methods: {
            load() {
                getUserINfo().then((res) => {
                    if (res && res.code == 200) {
                        var o = res.data.owner || {}
                        this.name = o.name || ""
                        this.frname = o.legal_person || ""
                        this.shopName = o.shop_name || ""
                        this.phone = o.phone || ""
                        this.zip_code = o.zip_code || ""
                        this.province = o.province || ""
                        this.address1 = o.address1 || ""
                        this.address2 = o.address2 || ""
                    }
                }).catch(() => {})
            },
            searchAddress() {
                if (!this.zip_code) return
                gatZipAddress(this.zip_code).then((res) => {
                    if (res && res.code == 200) {
                        this.province = res.data.address.address1
                        this.address1 = res.data.address.address2 + res.data.address.address3
                    } else {
                        this.$refs.shell.toast((res && res.message) || "住所が見つかりませんでした", true)
                    }
                }).catch(() => {})
            },
            save() {
                if (this.saving) return
                this.saving = true
                editUser({
                    name: this.name, legal_person: this.frname, shop_name: this.shopName, phone: this.phone,
                    zip_code: this.zip_code, province: this.province, address1: this.address1, address2: this.address2
                }).then((res) => {
                    this.saving = false
                    if (res && res.code == 200) {
                        this.$refs.shell.toast("保存しました")
                        try { sessionStorage.setItem("sp_shop", this.shopName) } catch (e) {}
                    } else {
                        this.$refs.shell.toast((res && res.message) || "保存できませんでした", true)
                    }
                }).catch(() => {
                    this.saving = false
                    this.$refs.shell.toast("通信エラーが発生しました。", true)
                })
            },
            onCode(e) {
                var v = (e.target.value || "").replace(/\D/g, "").slice(0, 6)
                this.code = v
                e.target.value = v
            },
            sendEmailCode() {
                if (this.busy || !this.newEmail) return
                this.busy = true
                changeEmail({ email: this.newEmail, request_type: 1 }).then((res) => {
                    this.busy = false
                    if (res && res.code == 200) {
                        this.code = ""
                        this.step = 3
                        this.$refs.shell.toast("認証コードを送信しました")
                    } else {
                        this.$refs.shell.toast((res && res.message) || "送信できませんでした", true)
                    }
                }).catch(() => {
                    this.busy = false
                    this.$refs.shell.toast("通信エラーが発生しました。", true)
                })
            },
            confirmEmail() {
                if (this.busy || this.code.length != 6) return
                this.busy = true
                changeEmail({ email: this.newEmail, request_type: 2, code: this.code }).then((res) => {
                    this.busy = false
                    if (res && res.code == 200) {
                        var a = adminInfo()
                        a.email = this.newEmail
                        localStorage.setItem("admin", JSON.stringify(a))
                        this.email = this.newEmail
                        this.newEmail = ""
                        this.step = 1
                        this.$refs.shell.toast("メールアドレス(ID)を変更しました")
                    } else {
                        this.$refs.shell.toast((res && res.message) || "認証コードが正しくありません", true)
                    }
                }).catch(() => {
                    this.busy = false
                    this.$refs.shell.toast("通信エラーが発生しました。", true)
                })
            }
        }
    }
</script>
