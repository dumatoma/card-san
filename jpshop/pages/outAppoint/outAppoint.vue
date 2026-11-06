<template>
    <view class="containers">
        <!-- [カスタムエイド 2026-11-03] 「予約ページのURL」を「予約Webサイト」の画面に。ブロック:予約ページのURL/メール/SMS/LINE(SMS・LINE は準備中) -->
        <u-navbar title="予約Webサイト" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="oa-body">
            <!-- 予約ページのURL -->
            <view class="oa-card">
                <view class="ws-bt">予約ページのURL</view>
                <view class="oa-url" @longpress="copy">{{ link }}</view>
                <view class="ws-btn2">
                    <view class="ws-b" hover-class="oa-hover" @click="copy">コピー</view>
                    <view class="ws-b" hover-class="oa-hover" @click="openPage">開く</view>
                </view>
                <view class="oa-cap">ホームページ・Instagram のプロフィール・Googleビジネスプロフィールなどに貼ると、お客様がここから予約できます(アプリと同じ予約表につながります)。</view>
            </view>

            <view class="ws-sec">予約を受け付ける方法</view>

            <!-- メール -->
            <view class="oa-card">
                <view class="ws-bh">
                    <text class="ws-bt">メール</text>
                    <u-switch :value="ch.mail == 1" :disabled="!canEdit || (ch.opt_sms != 1 && ch.opt_line != 1)" activeColor="#34C759" @change="setCh('mail', $event)"></u-switch><!-- [カスタムエイド 2026-11-03] オプション契約中だけオフにできる -->
                </view>
                <view class="ws-note" v-if="ch.opt_sms == 1 || ch.opt_line == 1">お客様に、予約確認・リマインダー・変更・キャンセルをメールで送ります。オフにすると、予約WEB でメールを選べなくなります(すでに入っている予約は、予約したときの方法のまま送ります)。</view>
                <view class="ws-note" v-else>お客様に、予約確認・リマインダー・変更・キャンセルをメールで送ります。SMS・LINE のオプションがないときは、メールはオフにできません。</view>
                <view class="ws-hr"></view>

                <view class="ws-loading" v-if="!loaded">読み込んでいます…</view>
                <block v-else>
                    <!-- 入力(未登録のとき/アドレスを変えるとき) -->
                    <block v-if="mail.state == 'none' || editing">
                        <view class="ws-bh">
                            <text class="ws-bt2">予約受付用メールアドレス</text>
                            <text class="ws-chip red" v-if="mail.state == 'none'">未登録</text>
                        </view>
                        <view class="ws-note">お客様が予約メールに返信すると、このアドレスに届きます。登録すると確認のメールが届くので、メールの中のボタンを押してください(3日間有効)。</view>
                        <input class="ws-in" :class="{ on: fk }" v-model="input" type="text" maxlength="100" placeholder="例:reserve@salon.jp" placeholder-class="ws-ph"
                            :disabled="!canEdit" @focus="fk = true" @blur="fk = false" />
                        <view class="ws-pri" :class="{ off: busy || !canEdit }" hover-class="oa-hover" @click="save">{{ busy ? '送っています…' : '確認のメールを送る' }}</view>
                        <view class="ws-sub" hover-class="oa-hover" v-if="editing" @click="editing = false">やめる</view>
                        <view class="ws-note">迷惑メールに入らないように、@mail.card-san.jp からのメールを受け取れるようにしておいてください。</view>
                    </block>

                    <!-- 確認待ち -->
                    <block v-else-if="mail.state == 'wait'">
                        <view class="ws-bh">
                            <text class="ws-addr">{{ mail.email }}</text>
                            <text class="ws-chip warn">確認待ち</text>
                        </view>
                        <view class="ws-note">このアドレスに確認のメールを送りました。メールの中の「メールアドレスを確認する」を押すと、使えるようになります。<br />送った日時:{{ mail.sent }}(3日間有効)。見つからないときは迷惑メールのフォルダもご確認ください。</view>
                        <view class="ws-warn" v-if="mail.expired == 1">確認のリンクの期限(3日間)が過ぎました。送り直してください。</view>
                        <view class="ws-sub" :class="{ off: busy || !canEdit }" hover-class="oa-hover" @click="resend">確認のメールを送り直す</view>
                        <view class="ws-sub" :class="{ off: !canEdit }" hover-class="oa-hover" @click="startEdit">メールアドレスを変える</view>
                    </block>

                    <!-- 確認済み -->
                    <block v-else>
                        <view class="ws-bh">
                            <text class="ws-addr">{{ mail.email }}</text>
                            <text class="ws-chip ok">✓ 確認済み</text>
                        </view>
                        <view class="ws-note">お客様が予約メールに返信すると、このアドレスに届きます。</view>
                        <view class="ws-new" v-if="mail.new_email">
                            <view class="ws-new-t">新しいアドレスの確認待ち</view>
                            <view class="ws-new-a">{{ mail.new_email }}</view>
                            <view class="ws-note">送った日時:{{ mail.sent }}(3日間有効)。確認が済むまでは、今のアドレスを使います。</view>
                            <view class="ws-warn" v-if="mail.expired == 1">確認のリンクの期限(3日間)が過ぎました。送り直してください。</view>
                            <view class="ws-sub" :class="{ off: busy || !canEdit }" hover-class="oa-hover" @click="resend">確認のメールを送り直す</view>
                        </view>
                        <view class="ws-sub" :class="{ off: !canEdit }" hover-class="oa-hover" @click="startEdit">メールアドレスを変える</view>
                        <view class="ws-hr"></view>
                        <view class="ws-bh">
                            <text class="ws-bt2">予約メールの写しを受け取る</text>
                            <u-switch :value="mail.bcc_on == 1" :disabled="!canEdit" activeColor="#34C759" @change="setBcc"></u-switch>
                        </view>
                        <view class="ws-note">お客様に送った予約メールと同じもの(予約確認・リマインダー・変更・キャンセル)が、このアドレスにも届きます。</view>
                    </block>
                    <view class="ws-warn" v-if="!canEdit">変えられるのは、管理者と「予約設定」の権限がある副管理者だけです。</view>
                </block>
            </view>

            <!-- SMS・LINE(準備中) -->
            <!-- [カスタムエイド 2026-11-03] 段階8-1:SMS(電話番号に送信) -->
            <view class="oa-card" :class="{ 'ws-dim': ch.opt_sms != 1 }">
                <view class="ws-bh">
                    <text class="ws-bt">SMS<text class="ws-bt-sub">(電話番号に送信)</text></text>
                    <text class="ws-chip" :class="ch.opt_sms == 1 ? 'ok' : 'gray'">{{ ch.opt_sms == 1 ? 'ご契約中' : '未契約' }}</text>
                </view>
                <block v-if="ch.opt_sms == 1">
                    <view class="ws-bh">
                        <text class="ws-bt2">SMS で受け付ける</text>
                        <u-switch :value="ch.sms == 1" :disabled="!canEdit" activeColor="#34C759" @change="setCh('sms', $event)"></u-switch>
                    </view>
                    <view class="ws-note">お客様の携帯電話の番号あてに、予約確認・リマインダー・変更・キャンセルを SMS で送ります。</view>
                    <view class="ws-bh">
                        <text class="ws-note">今月の予約SMS{{ smsInfo.reset_day ? '(毎月' + smsInfo.reset_day + '日にリセット)' : '' }}</text>
                        <text class="ws-cnt">残り {{ smsInfo.left }} / {{ smsInfo.month }}通</text>
                    </view>
                    <view class="ws-bar"><view class="ws-bar-in" :style="{ width: smsPct + '%' }"></view></view>
                    <view class="ws-note" v-if="smsInfo.extra > 0">追加でご購入の分:{{ smsInfo.extra }}通</view>
                </block>
                <view class="ws-note" v-else>お客様の携帯電話の番号あてに SMS で送ります。有料オプションです(お申し込みは準備中)。</view>
            </view>
            <!-- [カスタムエイド 2026-11-04 段階9] LINE(お店の LINE 公式アカウントから送る)。①まだつないでいない ②つないだあと ③今月の上限 ⑩間違いがあったとき -->
            <view class="oa-card" :class="{ 'ws-dim': ch.opt_line != 1 }">
                <view class="ws-bh">
                    <text class="ws-bt">LINE</text>
                    <text class="ws-chip" :class="ch.opt_line == 1 ? 'ok' : 'gray'">{{ ch.opt_line == 1 ? 'ご契約中' : '未契約' }}</text>
                </view>
                <view class="ws-note" v-if="ch.opt_line != 1">お店の LINE 公式アカウントから、予約確認・リマインダー・変更・キャンセルを送ります。有料オプションです(お申し込みは準備中)。</view>
                <view class="ws-loading" v-else-if="!lineLoaded">読み込んでいます…</view>

                <!-- ②③ つないだあと -->
                <block v-else-if="line.set == 1">
                    <view class="ln-ok">✓ LINE 公式アカウント「{{ line.bot_name }}」とつながっています</view>
                    <view class="ln-over" v-if="lineOver">
                        <view class="ln-over-t">LINE の今月の上限に達しました</view>
                        <view>予約WEB で LINE を選べないようにしています。来月、上限が戻ると自動で選べるようになります。すでに LINE で受けた予約のお知らせも、送れない場合があります。</view>
                    </view>
                    <view class="ws-bh">
                        <text class="ws-bt2">LINE で受け付ける</text>
                        <u-switch :value="ch.line_on == 1" :disabled="!canEdit" activeColor="#34C759" @change="setCh('line', $event)"></u-switch>
                    </view>
                    <view class="ws-note">お客様が予約WEB で LINE を選ぶと、お店の LINE 公式アカウントから予約確認・リマインダー・変更・キャンセルが届きます。</view>
                    <view class="ws-bh">
                        <text class="ws-note">LINE 今月の残り(LINE のプランの通数)</text>
                        <text class="ws-cnt" :class="{ 'ln-red': lineOver }">{{ lineCnt }}</text>
                    </view>
                    <view class="ws-bar" v-if="lq && lq.limit > 0"><view class="ws-bar-in" :class="{ 'ln-barred': lineOver }" :style="{ width: linePct + '%' }"></view></view>
                    <view class="ws-note">お店が LINE でほかに送った分(一斉配信など)も入った数です。</view>
                    <view class="ws-hr"></view>
                    <view class="ws-bh" hover-class="oa-hover" @click="toLineSetting">
                        <text class="ws-bt2">LINE予約の設定</text>
                        <text class="ln-done">設定済み ›</text>
                    </view>
                    <view class="ws-sub ln-gap" :class="{ off: testBusy }" hover-class="oa-hover" @click="lineTest">{{ testBusy ? 'LINE を開いています…' : '試しに自分の LINE に送る' }}</view>
                    <view class="ws-note">お店の LINE の友だちになっている必要があります。</view>
                </block>

                <!-- ①⑩ まだつないでいない -->
                <block v-else>
                    <view class="ws-note">お店の LINE 公式アカウントから、予約確認・リマインダー・変更・キャンセルを送ります。<text class="ln-b">LINEビジネスアカウント</text>と、<text class="ln-b">LINE Developers</text> の設定が必要です。</view>
                    <view class="ln-guide" hover-class="oa-hover" @click="openLineGuide">LINE とつなぐ手順を見る ›</view><!-- [カスタムエイド 2026-11-05 段階9] サポートサイトの手順書 -->
                    <view class="ln-err" v-if="lineErr">
                        <view class="ln-err-t">{{ lineErr }}</view>
                        <view>赤い欄を、LINE Developers からコピーし直してください。まだ保存していません。</view>
                    </view>
                    <view class="ln-grp" v-for="g in lineGroups" :key="g.key">
                        <view class="ln-gh">
                            <text class="ln-gt">{{ g.title }}</text>
                            <text class="ln-tag" :class="g.key">{{ g.tag }}</text>
                        </view>
                        <view class="ln-f" v-for="f in g.fields" :key="f.k">
                            <view class="ln-lab">{{ f.label }}</view>
                            <input class="ws-in ln-in" :class="{ on: lk == f.k, ng: lineErrs[f.k] }" v-model="lin[f.k]" type="text" maxlength="100" placeholder="入力してください" placeholder-class="ws-ph"
                                :disabled="!canEdit" @focus="lk = f.k" @blur="lk = ''" @input="clearErr(f.k)" />
                            <view class="ln-fe" v-if="lineErrs[f.k]">{{ lineErrs[f.k] }}</view>
                        </view>
                    </view>
                    <view class="ws-pri ln-gap" :class="{ off: lineBusy || !canEdit }" hover-class="oa-hover" @click="lineSave">{{ lineBusy ? 'LINE に問い合わせています…' : '保存して確かめる' }}</view>
                </block>
                <view class="ws-warn" v-if="ch.opt_line == 1 && lineLoaded && !canEdit">変えられるのは、管理者と「予約設定」の権限がある副管理者だけです。</view>
            </view>
        </view>
    </view>
</template>

<script>
    import { csReserveMail, csReserveMailSave, csReserveMailResend, csReserveMailBcc, csReserveCh, csReserveChSave, csLine, csLineSave, csLineTestUrl } from '@/api/index.js' // [カスタムエイド 2026-11-03]([カスタムエイド 2026-11-04 段階9] LINE)
    export default {
        data() {
            return {
                link: "",
                showA:false,
                // [カスタムエイド 2026-11-03] 予約受付用メールアドレス
                loaded: false,
                mail: { state: 'none' },
                canEdit: true,
                editing: false,
                input: '',
                fk: false,
                busy: false,
                // [カスタムエイド 2026-11-03] 段階8-1:受け付ける方法と今月の予約SMS
                ch: { mail: 1, sms: 0, opt_sms: 0, line: 0, line_on: 0, opt_line: 0 },
                smsInfo: { month: 100, left: 0, extra: 0, reset_day: 0 },
                // [カスタムエイド 2026-11-04 段階9] LINE
                line: { set: 0 }, lineLoaded: false, lineBusy: false, testBusy: false, lk: '',
                lin: { msg_id: '', msg_secret: '', login_id: '', login_secret: '', liff_id: '' },
                lineErr: '', lineErrs: {},
                lineGroups: [
                    { key: 'ma', title: 'Messaging API のチャネル', tag: 'Messaging API', fields: [{ k: 'msg_id', label: 'チャネル ID' }, { k: 'msg_secret', label: 'チャネルシークレット' }] },
                    { key: 'lg', title: 'LINE ログインのチャネル', tag: 'LINEログイン', fields: [{ k: 'login_id', label: 'チャネル ID' }, { k: 'login_secret', label: 'チャネルシークレット' }, { k: 'liff_id', label: 'LIFF ID(「LIFF」タブ)' }] }
                ]
            };
        },
        onShow() {
            let that = this
            let info = uni.getStorageSync("admin")
            that.link = "https://order.card-san.jp/reserve/?id=" + info.sid // [カスタムエイド 2026-10-13] 新しい予約URL(アプリの次回更新で反映)
            that.load()
            that.loadCh() // [カスタムエイド 2026-11-03]
            that.loadLine() // [カスタムエイド 2026-11-04 段階9]
        },
        computed: {
            // [カスタムエイド 2026-11-04 段階9] LINE の今月の通数
            lq() { return this.line && this.line.quota ? this.line.quota : null },
            lineOver() { return !!(this.lq && this.lq.over == 1) },
            lineCnt() {
                const q = this.lq
                if (!q) return '取れませんでした'
                const f = (n) => String(Math.max(0, Number(n) || 0)).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
                return q.limit > 0 ? '残り ' + f(q.left) + ' / ' + f(q.limit) + '通' : '上限なし(送った数 ' + f(q.used) + '通)'
            },
            linePct() {
                const q = this.lq
                if (!q || !(q.limit > 0)) return 0
                return Math.max(0, Math.min(100, Math.round((q.left || 0) / q.limit * 100)))
            },
            smsPct() {
                const m = this.smsInfo.month || 100
                return Math.max(0, Math.min(100, Math.round((this.smsInfo.left || 0) / m * 100)))
            }
        },
        methods: {
            // [カスタムエイド 2026-11-03] 今の状態を読む
            load() {
                csReserveMail().then((res) => {
                    if (res.code == 200) this.apply(res.data)
                    this.loaded = true
                }).catch(() => { this.loaded = true })
            },
            apply(d) {
                this.mail = d || { state: 'none' }
                this.canEdit = !d || d.can_edit != 0
                if (this.mail.state == 'none' && !this.input) this.input = ''
            },
            toast(t) { uni.showToast({ title: t, icon: 'none', duration: 2500 }) },
            // [カスタムエイド 2026-11-03] 受け付ける方法を読む・変える
            loadCh() {
                csReserveCh().then((res) => {
                    if (res.code == 200 && res.data) {
                        this.ch = this.chOf(res.data)
                        this.smsInfo = res.data.sms_info || this.smsInfo
                        if (res.data.can_edit == 0) this.canEdit = false
                    }
                }).catch(() => {})
            },
            // [カスタムエイド 2026-11-04 段階9] 受け付ける方法(LINE も)
            chOf(d) {
                return { mail: d.mail, sms: d.sms, opt_sms: d.opt_sms, line: d.line || 0, line_on: d.line_on || 0, opt_line: d.opt_line || 0 }
            },
            setCh(key, v) {
                const next = { mail: this.ch.mail, sms: this.ch.sms, line: this.ch.line_on } // [カスタムエイド 2026-11-04 段階9] line を足した
                next[key] = v ? 1 : 0
                if (!next.mail && !next.sms && !next.line) {
                    this.toast('受け付ける方法を1つ以上オンにしてください')
                    this.ch = Object.assign({}, this.ch) // スイッチを元に戻す
                    return
                }
                csReserveChSave(next).then((res) => {
                    if (res.code == 200 && res.data) {
                        this.ch = this.chOf(res.data)
                        this.smsInfo = res.data.sms_info || this.smsInfo
                        this.toast(res.message || '変えました')
                    } else { this.toast(res.message || '変えられませんでした'); this.ch = Object.assign({}, this.ch); this.loadCh() }
                }).catch(() => { this.toast('通信できませんでした'); this.ch = Object.assign({}, this.ch); this.loadCh() })
            },
            // [カスタムエイド 2026-11-04 段階9] LINE の状態を読む
            loadLine() {
                csLine().then((res) => {
                    if (res.code == 200 && res.data) {
                        this.line = res.data
                        if (res.data.ch) this.ch = this.chOf(res.data.ch)
                        if (res.data.can_edit == 0) this.canEdit = false
                    }
                    this.lineLoaded = true
                }).catch(() => { this.lineLoaded = true })
            },
            clearErr(k) {
                if (this.lineErrs[k]) { const e = Object.assign({}, this.lineErrs); delete e[k]; this.lineErrs = e }
            },
            // 「保存して確かめる」:LINE に問い合わせて、5つとも正しければ保存(1つでも違えば保存しない)
            lineSave() {
                if (this.lineBusy || !this.canEdit) return
                const d = {}
                Object.keys(this.lin).forEach((k) => { d[k] = String(this.lin[k] || '').trim() })
                this.lineBusy = true
                this.lineErr = ''
                this.lineErrs = {}
                csLineSave(d).then((res) => {
                    this.lineBusy = false
                    if (res.code == 200 && res.data) {
                        this.line = res.data
                        if (res.data.ch) this.ch = this.chOf(res.data.ch)
                        this.lin = { msg_id: '', msg_secret: '', login_id: '', login_secret: '', liff_id: '' }
                        uni.showToast({ title: res.message || 'LINE とつながりました' })
                    } else {
                        this.lineErr = res.message || 'つなげませんでした'
                        this.lineErrs = (res.data && res.data.errors) || {}
                    }
                }).catch(() => { this.lineBusy = false; this.toast('通信できませんでした') })
            },
            // [カスタムエイド 2026-11-05 段階9] サポートサイト(Zendesk)の「LINE予約の設定」を、アプリの中で開く(ほかの画面と同じ helpview)
            openLineGuide() {
                uni.navigateTo({ url: '/pages/helpview/helpview?link=' + encodeURIComponent('https://cardsan.zendesk.com/hc/ja/articles/63992797407641') })
            },
            toLineSetting() {
                uni.navigateTo({ url: './lineSetting' })
            },
            // 「試しに自分の LINE に送る」:LIFF の住所を開く(LINE アプリが開いて、テストのお知らせが届く)
            lineTest() {
                if (this.testBusy) return
                this.testBusy = true
                csLineTestUrl().then((res) => {
                    setTimeout(() => { this.testBusy = false }, 2000)
                    if (res.code == 200 && res.data && res.data.url) {
                        // #ifdef APP-PLUS
                        plus.runtime.openURL(res.data.url)
                        // #endif
                        // #ifdef H5
                        window.open(res.data.url)
                        // #endif
                    } else this.toast(res.message || 'LINE を開けませんでした')
                }).catch(() => { this.testBusy = false; this.toast('通信できませんでした') })
            },
            startEdit() {
                if (!this.canEdit) return
                this.input = ''
                this.editing = true
            },
            save() {
                if (this.busy || !this.canEdit) return
                const v = String(this.input || '').trim()
                if (!v) { this.toast('メールアドレスを入れてください'); return }
                this.busy = true
                csReserveMailSave({ email: v }).then((res) => {
                    this.busy = false
                    if (res.code == 200) {
                        this.apply(res.data)
                        this.editing = false
                        this.input = ''
                        uni.showToast({ title: res.message || '確認のメールを送りました' })
                    } else this.toast(res.message || '送れませんでした')
                }).catch(() => { this.busy = false; this.toast('通信できませんでした') })
            },
            resend() {
                if (this.busy || !this.canEdit) return
                this.busy = true
                csReserveMailResend().then((res) => {
                    this.busy = false
                    if (res.code == 200) { this.apply(res.data); uni.showToast({ title: res.message || '確認のメールを送りました' }) }
                    else this.toast(res.message || '送れませんでした')
                }).catch(() => { this.busy = false; this.toast('通信できませんでした') })
            },
            setBcc(v) {
                if (!this.canEdit) return
                csReserveMailBcc({ on: v ? 1 : 0 }).then((res) => {
                    if (res.code == 200) { this.apply(res.data); this.toast(res.message) }
                    else { this.toast(res.message || '変えられませんでした'); this.load() }
                }).catch(() => { this.toast('通信できませんでした'); this.load() })
            },
            // [カスタムエイド 2026-10-22] 予約ページをブラウザで開く
            openPage() {
                // #ifdef APP-PLUS
                plus.runtime.openURL(this.link)
                // #endif
                // #ifdef H5
                window.open(this.link)
                // #endif
            },
            copy() {
                let that = this
                uni.setClipboardData({
                    data: that.link,
                    success: () => {
                        uni.hideToast()
                        // [カスタムエイド 2026-10-22] アプリ共通の小さなお知らせ「✓ コピーしました」に
                        setTimeout(() => { uni.showToast({ title: "コピーしました" }) }, 50)
                    }
                })
            }
        }
    }
</script>

<style lang="less">
    /* [カスタムエイド 2026-10-22] 外部リンク(メニュー)と同じ決まり */
    .oa-body { padding: 48rpx 24rpx calc(80rpx + env(safe-area-inset-bottom)); }
    .oa-info { padding: 24rpx 28rpx; border-radius: 24rpx; background: #EEF4FF; font-size: 26rpx; line-height: 1.7; color: #1D1D1F; margin-bottom: 48rpx; } /* [カスタムエイド 2026-10-22] 1.5倍 */
    .oa-card { background: #FFFFFF; border-radius: 32rpx; padding: 40rpx 32rpx; margin-bottom: 60rpx; }
    .oa-label { font-size: 26rpx; font-weight: 500; line-height: 1.2; margin-bottom: 8rpx; color: #1D1D1F; }
    .oa-url { padding: 24rpx 28rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; background: #F5F5F7; font-size: 30rpx; line-height: 1.5; color: #1D1D1F; word-break: break-all; }
    .oa-cap { font-size: 24rpx; color: #6E6E73; margin-top: 24rpx; line-height: 1.5; }
    .oa-btn { height: 104rpx; line-height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; text-align: center; font-size: 32rpx; font-weight: 700; }
    .oa-btn2 { margin-top: 48rpx; height: 104rpx; line-height: 104rpx; border-radius: 28rpx; background: #FFFFFF; color: #1A73E8; text-align: center; font-size: 30rpx; }
    .oa-hover { opacity: 0.7; }

    .hit {
        background: rgba(29, 29, 31, 0.1) !important;
    }
    .mask{
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(29,29,31,0.2);
        z-index: 999;
        
        .maskcontent{
            width: 356upx;
            height: 292upx;
            background: #fff;
            border-radius: 20upx;
            text-align: center;
            box-sizing: border-box;
            padding: 60upx;
            font-size: 32upx;
            color: #707070;
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%,-50%);
            
            .mi{
                width: 100upx;
                height: 100upx;
                margin-bottom: 40upx;
            }
            
        }
    }
    .cop {
        width: 478rpx;
        // height: 96rpx;
        padding: 28upx 0;
        background: rgba(29, 29, 31, 0.2);
        border-radius: 20rpx 20rpx 20rpx 20rpx;
        opacity: 1;
        border: 2rpx solid #1D1D1F;
        text-align: center;
        // line-height: 96upx;
        font-size: 40rpx;
        font-family: Noto Sans Kannada-Regular, Noto Sans Kannada;
        color: #1D1D1F;
        margin: 100upx auto 0;
        display: flex;
        align-items: center;
        // padding: 0;
        justify-content: center;
    }

    .titel {
        font-size: 28rpx;
        font-family: Noto Sans Kannada-Regular, Noto Sans Kannada;
        color: #1D1D1F;
        // margin-bottom: 100upx;
    }

    .uirls {
        width: 100%;
        height: 400upx;
        border-radius: 20upx;
        border: 2rpx solid #AAAAAA;
        box-sizing: border-box;
        padding: 20upx;
        word-wrap: break-word;
        word-break: normal;
    }

    .containers {
        width: 100vw;
        min-height: 100vh;
        box-sizing: border-box;
        background: #F5F5F7;
    }

    .words {
        font-size: 28upx;
        font-family: Noto Sans Kannada-Regular, Noto Sans Kannada;
        font-weight: 400;
        color: #1D1D1F;
    }

    /* [カスタムエイド 2026-11-03] カードの影(2026-11-03):下へ1px・ぼかし4px・黒10%。グレーの背景と白いカードの境目を分かりやすく */
    .oa-card {
        box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.10) !important;
    }

    /* [カスタムエイド 2026-11-03] 予約Webサイト(ブロックの並び。カードとカードの間は 24rpx) */
    .oa-body .oa-card { margin-bottom: 24rpx; padding: 32rpx 28rpx; display: flex; flex-direction: column; }
    .oa-body .oa-card > view, .oa-body .oa-card > input { margin-top: 20rpx; }
    .oa-body .oa-card > view:first-child { margin-top: 0; }
    .ws-sec { font-size: 26rpx; font-weight: 700; color: #1D1D1F; margin: 24rpx 8rpx 16rpx; }
    .ws-bh { display: flex; align-items: center; justify-content: space-between; }
    .ws-bt { font-size: 30rpx; font-weight: 700; color: #1D1D1F; }
    .ws-bt2 { font-size: 28rpx; font-weight: 600; color: #1D1D1F; }
    .ws-addr { flex: 1; min-width: 0; font-size: 28rpx; font-weight: 700; color: #1D1D1F; word-break: break-all; margin-right: 16rpx; }
    .ws-note { font-size: 24rpx; color: #6E6E73; line-height: 1.6; }
    .ws-hr { height: 1px; background: #E5E5EA; }
    .ws-chip { flex-shrink: 0; font-size: 22rpx; font-weight: 700; padding: 6rpx 16rpx; border-radius: 20rpx; }
    .ws-chip.red { background: #FCE8E6; color: #B3261E; }
    .ws-chip.warn { background: #FFF4E5; color: #8A4B00; }
    .ws-chip.ok { background: #E6F4EA; color: #0D652D; }
    .ws-chip.gray { background: #F5F5F7; color: #6E6E73; border: 1px solid #E5E5EA; }
    .ws-in { height: 88rpx; padding: 0 24rpx; border: 1px solid #D2D2D7; border-radius: 20rpx; background: #FFFFFF; font-size: 30rpx; }
    .ws-in.on { border-color: #1A73E8; }
    .ws-ph { color: #AEAEB2; }
    .ws-pri { height: 96rpx; line-height: 96rpx; border-radius: 24rpx; background: #1A73E8; color: #FFFFFF; text-align: center; font-size: 30rpx; font-weight: 700; }
    .ws-sub { height: 88rpx; line-height: 88rpx; border-radius: 24rpx; background: #F5F5F7; border: 1px solid #E5E5EA; color: #1A73E8; text-align: center; font-size: 28rpx; font-weight: 600; }
    .ws-pri.off, .ws-sub.off { opacity: 0.5; }
    .ws-btn2 { display: flex; }
    .ws-b { flex: 1; height: 80rpx; line-height: 80rpx; border-radius: 20rpx; background: #F5F5F7; border: 1px solid #E5E5EA; color: #1A73E8; text-align: center; font-size: 28rpx; font-weight: 600; }
    .ws-b + .ws-b { margin-left: 16rpx; }
    .ws-warn { font-size: 24rpx; color: #8A4B00; background: #FFF4E5; border-radius: 16rpx; padding: 16rpx 20rpx; line-height: 1.6; }
    .ws-new { background: #F5F5F7; border-radius: 20rpx; padding: 20rpx; }
    .ws-new > view + view { margin-top: 12rpx; }
    .ws-new-t { font-size: 24rpx; font-weight: 700; color: #8A4B00; }
    .ws-new-a { font-size: 28rpx; font-weight: 700; color: #1D1D1F; word-break: break-all; }
    .ws-loading { font-size: 26rpx; color: #8A8A8E; text-align: center; padding: 20rpx 0; }
    .ws-dim { opacity: 0.55; }
    .oa-body { padding-top: 32rpx; }
    /* [カスタムエイド 2026-11-03] SMS のブロック */
    .ws-bt-sub { font-size: 24rpx; font-weight: 600; color: #6E6E73; }
    .ws-cnt { flex-shrink: 0; font-size: 26rpx; font-weight: 700; color: #1D1D1F; }
    .ws-bar { height: 14rpx; border-radius: 7rpx; background: #E5E5EA; overflow: hidden; }
    .ws-bar-in { height: 100%; background: #1A73E8; border-radius: 7rpx; }
    /* [カスタムエイド 2026-11-04 段階9] LINE のブロック。ボタンの上の間はほかの2倍(20rpx → 40rpx) */
    .oa-body .oa-card > view.ln-gap { margin-top: 40rpx; }
    .ln-b { font-weight: 700; color: #1D1D1F; }
    .ln-guide { font-size: 26rpx; font-weight: 600; color: #1A73E8; } /* [カスタムエイド 2026-11-05 段階9] */
    .ln-ok { font-size: 26rpx; font-weight: 700; color: #0D652D; background: #E6F4EA; border-radius: 16rpx; padding: 16rpx 20rpx; line-height: 1.6; }
    .ln-over { font-size: 24rpx; color: #B3261E; background: #FCE8E6; border-radius: 16rpx; padding: 16rpx 20rpx; line-height: 1.6; }
    .ln-over-t, .ln-err-t { font-size: 26rpx; font-weight: 700; margin-bottom: 4rpx; }
    .ln-err { font-size: 24rpx; color: #B3261E; background: #FCE8E6; border-radius: 16rpx; padding: 16rpx 20rpx; line-height: 1.6; }
    .ln-red { color: #B3261E; }
    .ln-barred { background: #D93025; }
    .ln-done { font-size: 28rpx; font-weight: 700; color: #1D1D1F; }
    .ln-grp { border: 1px solid #E5E5EA; border-radius: 20rpx; padding: 24rpx; }
    .ln-gh { display: flex; align-items: center; justify-content: space-between; }
    .ln-gt { font-size: 26rpx; font-weight: 700; color: #1D1D1F; }
    .ln-tag { flex-shrink: 0; font-size: 20rpx; font-weight: 700; padding: 4rpx 12rpx; border-radius: 8rpx; }
    .ln-tag.ma { background: #E6F4EA; color: #06A64C; }
    .ln-tag.lg { background: #F5F5F7; color: #1D1D1F; border: 1px solid #D2D2D7; }
    .ln-f { margin-top: 20rpx; }
    .ln-lab { font-size: 24rpx; font-weight: 700; color: #1D1D1F; margin-bottom: 8rpx; }
    .ln-in { display: block; width: 100%; box-sizing: border-box; }
    .ln-in.ng { border-color: #D93025; }
    .ln-fe { font-size: 22rpx; color: #B3261E; line-height: 1.5; margin-top: 8rpx; }
</style>