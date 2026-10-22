<template>
    <!-- [カスタムエイド 2026-10-21] メッセージ作成を作り直し。テキストと写真を1通で送れるように(サーバーの content_type 3)。
         ・カード:タイトル/テキスト(テンプレート①②)/写真/口コミボタン
         ・写真は2つの形を切り替え:「写真を並べる」= 4枚まで・押すと拡大 /「リンク付きの写真」= 1枚・押すとリンクが開く(拡大しない)
         ・写真は選んだときに自動で小さくする(長い辺 1280px まで)。通信量と費用をおさえるため
         ・テキストだけのときは今までと同じ形(content_type 1)で送る -->
    <view class="ne">
        <u-navbar :title="ro ? '配信済みのメッセージ' : (query.source == 'edit' ? '予約メッセージの編集' : 'メッセージ作成')" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD">
            <view slot="right" class="ne-pv" v-if="!ro" @click="show = true">プレビュー</view>
        </u-navbar>
        <!-- [カスタムエイド 2026-10-21] 配信済みのメッセージは見るだけ(編集・次へは出さない)。テキストはワンタップでコピーできる -->
        <view class="ne-pg" v-if="ro">
            <view class="ne-card">
                <view class="ro-top">
                    <view class="ro-chip">配信済</view>
                    <view class="ro-when">{{ sentAt }}</view>
                </view>
                <view class="ro-row"><text class="ro-k">配信先</text><text class="ro-v">{{ target }}</text></view>
                <view class="ro-row" v-if="sentCount"><text class="ro-k">配信数</text><text class="ro-v">{{ sentCount }}</text></view>
                <view class="ro-row"><text class="ro-k">タイトル</text><text class="ro-v">{{ info.title }}</text></view>
            </view>

            <view class="ne-card" v-if="trimText">
                <view class="ro-head">
                    <view class="ne-h ro-h">テキスト</view>
                    <view class="ro-copy" hover-class="ne-hover" @click="copyText">{{ copied ? 'コピーしました' : 'テキストをコピー' }}</view>
                </view>
                <view class="ro-text">{{ info.content }}</view>
            </view>

            <view class="ne-card" v-if="info.images.length">
                <view class="ne-h">写真{{ info.url ? '(リンク付き)' : '' }}</view>
                <view class="ne-grid ro-grid" v-if="!info.url">
                    <view class="ne-im" v-for="(u, i) in info.images" :key="u + i" @click="preview(i)">
                        <image class="ne-im-img" :src="u" mode="aspectFill"></image>
                    </view>
                </view>
                <block v-else>
                    <view class="ne-one ro-grid"><image class="ne-one-img" :src="info.images[0]" mode="aspectFill"></image></view>
                    <view class="ro-row"><text class="ro-k">リンク</text><text class="ro-v ro-link">{{ info.url }}</text></view>
                </block>
            </view>

            <view class="ne-card">
                <view class="ro-row ro-row0"><text class="ro-k">口コミボタン</text><text class="ro-v">{{ selThis == 1 ? '有り' : '無し' }}</text></view>
            </view>

            <view class="ne-note ne-pad">配信済みのメッセージは編集できません。同じ内容を送りたいときは、テキストをコピーして「メッセージ作成」に貼り付けてください。</view>
        </view>

        <view class="ne-pg" v-else>
            <view class="ne-card">
                <view class="ne-h"><text class="ne-req">※</text>タイトル <text class="ne-sub">(お客様には表示されません)</text></view>
                <input class="ne-in" :class="{ on: fk == 't' }" v-model="info.title" placeholder="例:年末のご予約について" placeholder-class="ne-ph"
                    @focus="fk = 't'" @blur="fk = ''" />
            </view>

            <view class="ne-card">
                <view class="ne-h">テキスト</view>
                <textarea class="ne-ta" :class="{ on: fk == 'c' }" v-model="info.content" maxlength="-1" :show-confirm-bar="false"
                    :disable-default-padding="true" placeholder="お客様に送る文を入力してください" placeholder-class="ne-ph"
                    @focus="fk = 'c'" @blur="fk = ''"></textarea>
                <view class="ne-chips">
                    <view class="ne-chip" :class="{ on: tab == 0 }" hover-class="ne-hover" @click="changeReview(0)">テンプレート①</view>
                    <view class="ne-chip" :class="{ on: tab == 1 }" hover-class="ne-hover" @click="changeReview(1)">テンプレート②</view>
                </view>
            </view>

            <view class="ne-card">
                <view class="ne-h">写真</view>
                <view class="ne-seg">
                    <view :class="{ on: mode == 0 }" @click="setMode(0)">写真を並べる</view>
                    <view :class="{ on: mode == 1 }" @click="setMode(1)">リンク付きの写真</view>
                </view>
                <view class="ne-note ne-note-top">{{ mode == 0 ? '4枚まで。お客様は1つのかたまりで見られ、押すと拡大できます。' : '1枚だけ。お客様が押すと、下のリンクが開きます(拡大はしません)。' }}</view>

                <view class="ne-grid" v-if="mode == 0">
                    <view class="ne-im" v-for="(u, i) in info.images" :key="u + i">
                        <image class="ne-im-img" :src="u" mode="aspectFill"></image>
                        <view class="ne-x" @click.stop="delImage(i)">×</view>
                    </view>
                    <view class="ne-add" v-if="info.images.length < 4" hover-class="ne-hover" @click="uploadImage">
                        <text class="ne-add-p">＋</text><text class="ne-add-t">追加</text>
                    </view>
                </view>

                <block v-else>
                    <view class="ne-one" v-if="info.images.length">
                        <image class="ne-one-img" :src="info.images[0]" mode="aspectFill"></image>
                        <view class="ne-x" @click.stop="delImage(0)">×</view>
                    </view>
                    <view class="ne-one ne-one-add" v-else hover-class="ne-hover" @click="uploadImage">
                        <text class="ne-add-p">＋</text><text class="ne-add-t">写真を選ぶ</text>
                    </view>
                    <view class="ne-h ne-h2"><text>リンク</text><text class="ne-req">※</text></view>
                    <input class="ne-in" :class="{ on: fk == 'u' }" v-model="info.url" placeholder="https://" placeholder-class="ne-ph"
                        @focus="fk = 'u'" @blur="fk = ''" />
                </block>
                <view class="ne-note" v-if="uploading">写真を準備しています…</view>
            </view>

            <view class="ne-card ne-sw">
                <view class="ne-sw-t">Googleの口コミボタンを付ける</view>
                <u-switch :value="selThis == 1" activeColor="#34C759" @change="changeSel"></u-switch>
            </view>

            <view class="ne-note ne-pad">テキストか写真の、どちらか1つがあれば送れます。写真は送る前に自動で小さくします。</view>
            <view class="ne-btn" hover-class="ne-hover" @click="toNext">次へ(配信先・日時の設定)</view>
        </view>

        <cs-dialog :show="showMode" title="リンク付きの写真にしますか?" :message="'リンク付きの写真は1枚だけです。\n最初の1枚だけを残します。'"
            ok-text="切り替える" cancel-text="やめる" ok-color="blue" @ok="okMode" @cancel="showMode = false"></cs-dialog>

        <!-- プレビュー:お客様の Card-San App での見え方 -->
        <u-popup :show="show" mode="bottom" round="24" @close="close">
            <view class="ne-sheet">
                <view class="ne-grip"></view>
                <view class="ne-shx" hover-class="ne-hover" @click="show = false">×</view><view class="ne-sh-t">プレビュー</view>
                <view class="ne-sh-tip">端末やOSによって、実際の見え方と少し異なる場合があります。</view>
                <scroll-view scroll-y class="ne-chat">
                    <view class="ne-msg" v-if="trimText">
                        <u-avatar src="../../../../static/images/avatar.png" size="30"></u-avatar>
                        <view class="ne-bub">{{ info.content }}</view>
                    </view>
                    <view class="ne-msg" v-if="info.images.length">
                        <u-avatar src="../../../../static/images/avatar.png" size="30"></u-avatar>
                        <view v-if="mode == 1" class="ne-pv-one"><image :src="info.images[0]" mode="widthFix" class="ne-pv-one-img"></image></view>
                        <view v-else class="ne-pv-grid" :class="'n' + info.images.length">
                            <image v-for="(u, i) in info.images" :key="i" :src="u" mode="aspectFill" class="ne-pv-cell"></image>
                        </view>
                    </view>
                    <view class="ne-msg" v-if="selThis == 1 && previewImage">
                        <u-avatar src="../../../../static/images/avatar.png" size="30"></u-avatar>
                        <view class="ne-pv-rv"><image :src="previewImage" mode="aspectFill" class="ne-pv-rv-img"></image></view>
                    </view>
                    <view class="ne-empty" v-if="!trimText && !info.images.length">テキストか写真を入れると、ここに見え方が出ます。</view>
                </scroll-view>
                <view class="ne-close" hover-class="ne-hover" @click="close">閉じる</view>
            </view>
        </u-popup>
    </view>
</template>
<script>
    import csDialog from "@/components/csDialog/csDialog.vue"
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
    import {
        getTimingDetail,
        getShopInfo
    } from "@/api/index.js"

    const MAX_SIDE = 1280 // [カスタムエイド 2026-10-21] 写真の長い辺の上限(px)

    export default {
        components: { csDialog },
        data() {
            return {
                show: false,
                query: {},
                ro: false, // [カスタムエイド 2026-10-21] 配信済み = 見るだけ
                copied: false,
                showMode: false,
                detail: {},
                tab: -1,
                selThis: 0,
                mode: 0, // 0 = 写真を並べる(4枚まで)/1 = リンク付きの写真(1枚)
                fk: '',
                uploading: false,
                info: {
                    title: "",
                    content: "",
                    url: "",
                    images: []
                },
                module1: [],
                module2: [],
                previewImage: ""
            }
        },
        computed: {
            trimText() {
                return (this.info.content || '').trim()
            },
            sentAt() {
                const t = this.detail.order_time || this.detail.send_time || this.detail.updated_at || ''
                return t ? String(t).replace(/-/g, '/').slice(0, 16) + ' 配信' : ''
            },
            // [カスタムエイド 2026-10-21] 配信数:申し込んだときにサーバーが数えた人数(1人 = 1通。口コミボタンを付けても増えない)
            sentCount() {
                const n = Number(this.detail.order_send_count)
                return n ? n.toLocaleString() + '人' : ''
            },
            // 配信先のまとめ(全員/絞り込みの中身)
            target() {
                const d = this.detail
                if (d.receiver != 2) return '全員'
                const a = []
                const ms = String(d.birthday_month || '').split(',').filter((v) => v)
                if (ms.length) a.push('誕生月 ' + ms.map((v) => Number(v) + '月').join('・'))
                const sx = { 1: '男性', 2: '女性', 3: 'Xジェンダー' }
                const ss = String(d.sex || '').split(',').filter((v) => v)
                if (ss.length) a.push(ss.map((v) => sx[v] || '').filter((v) => v).join('・'))
                if (d.come_from) a.push('来店 ' + String(d.come_from).replace(/-/g, '/') + (d.come_to && d.come_to != d.come_from ? '〜' + String(d.come_to).replace(/-/g, '/') : ''))
                else if (Number(d.absent_days) > 0) a.push(d.absent_days + '日以上来店なし')
                const mn = Number(d.visit_min) || 0
                const mx = Number(d.visit_max) || 0
                if (mn == 1 && mx == 1) a.push('来店1回だけ')
                else if (mn > 0) a.push('来店' + mn + '回以上')
                return a.length ? '絞り込み(' + a.join(' / ') + ')' : '絞り込み'
            }
        },
        onLoad(options) {
            let that = this
            getShopInfo().then((res) => {
                if (res.code == 200) {
                    that.module1 = res.data.shop_info.timing_message_tpl1 || []
                    that.module2 = res.data.shop_info.timing_message_tpl2 || []
                    that.previewImage = res.data.shop_info.comment_button_style_image
                }
            }).catch(() => {})
            that.query = options
            if (options.source == "edit") {
                that.getDetail(options.id)
            }
        },
        methods: {
            changeSel() {
                this.selThis = this.selThis == 1 ? 0 : 1
            },
            changeReview(e) {
                const m = e == 0 ? this.module1 : this.module2
                if (!m || !m.length) {
                    uni.showToast({ title: 'テンプレートが登録されていません', icon: 'none' })
                    return
                }
                this.info.title = m[0] || this.info.title
                this.info.content = m[1] || ''
                this.tab = e
            },
            setMode(m) {
                if (this.mode == m) return
                // リンク付きの写真は1枚だけ。並べる→リンク付きにするときは、最初の1枚だけ残す
                if (m == 1 && this.info.images.length > 1) {
                    this.showMode = true // [カスタムエイド 2026-10-21] 確認はほかと同じダイアログで
                    return
                }
                this.mode = m
            },
            okMode() {
                this.info.images = this.info.images.slice(0, 1)
                this.mode = 1
                this.showMode = false
            },
            delImage(i) {
                this.info.images.splice(i, 1)
            },
            getDetail(id) {
                let that = this
                getTimingDetail(id).then((res) => {
                    if (res.code == 200) {
                        const d = res.data.timing_message
                        let images = []
                        let content = d.content || ''
                        if (d.content_type == 2) {
                            images = d.content ? [d.content] : []
                            content = ''
                        } else if (d.content_type == 3) {
                            images = Array.isArray(d.images) ? d.images : []
                        }
                        that.info = { title: d.title || '', content: content, url: d.url || '', images: images }
                        that.mode = d.url ? 1 : 0
                        that.selThis = d.is_comment_button == 1 ? 1 : 0
                        that.detail = d
                        that.ro = d.is_send == 1
                        if (!that.ro) uni.setStorageSync("timing", d)
                    } else {
                        uni.showToast({ title: res.message, icon: "none" })
                    }
                }).catch(() => {})
            },
            close() {
                this.show = false
            },
            // [カスタムエイド 2026-10-21] テキストをワンタップでコピー
            copyText() {
                uni.setClipboardData({
                    data: this.info.content || '',
                    showToast: false,
                    success: () => {
                        this.copied = true
                        uni.showToast({ title: 'コピーしました', icon: 'none' })
                        setTimeout(() => { this.copied = false }, 2000)
                    }
                })
            },
            preview(i) {
                uni.previewImage({ urls: this.info.images, current: i })
            },
            // 送る形にまとめる(テキストだけなら今までと同じ content_type 1。写真があれば content_type 3)
            buildData() {
                const imgs = this.info.images.slice(0, this.mode == 1 ? 1 : 4)
                const url = this.mode == 1 ? (this.info.url || '').trim() : ''
                const d = { title: (this.info.title || '').trim(), is_comment_button: this.selThis }
                if (!imgs.length) {
                    d.content_type = 1
                    d.content = this.info.content
                    d.url = ''
                    d.images = []
                } else {
                    d.content_type = 3
                    d.content = this.info.content || ''
                    d.images = imgs
                    d.url = url
                }
                return d
            },
            toNext() {
                if (this.uploading) {
                    uni.showToast({ title: '写真を準備しています。少しお待ちください', icon: 'none' })
                    return
                }
                if (!(this.info.title || '').trim()) {
                    uni.showToast({ title: 'タイトルを入力してください', icon: 'none' })
                    return
                }
                if (!this.trimText && !this.info.images.length) {
                    uni.showToast({ title: 'テキストか写真を入れてください', icon: 'none' })
                    return
                }
                if (this.mode == 1 && this.info.images.length) {
                    const u = (this.info.url || '').trim()
                    if (!/^https?:\/\/\S+$/.test(u)) {
                        uni.showToast({ title: 'リンクを「https://」から入力してください', icon: 'none' })
                        return
                    }
                }
                const d = this.buildData()
                if (this.query.source == "add") {
                    uni.setStorageSync("timing", d)
                    uni.redirectTo({ url: "../setting/setting?source=add" })
                } else if (this.query.source == "edit") {
                    // [カスタムエイド 2026-10-21] 以前は、ここで直した内容が次の画面に渡っていなかった(読み込んだときの内容のまま保存された)
                    const t = uni.getStorageSync("timing") || {}
                    uni.setStorageSync("timing", Object.assign({}, t, d))
                    uni.redirectTo({ url: "../setting/setting?source=edit&id=" + this.query.id })
                }
            },
            // 写真を選ぶ → 小さくする → アップロード(選んだ順に追加)
            uploadImage() {
                const left = this.mode == 1 ? 1 - this.info.images.length : 4 - this.info.images.length
                if (left <= 0) return
                pickImage({
                    count: left,
                    sizeType: ['compressed'],
                    success: async (res) => {
                        this.uploading = true
                        for (const p of res.tempFilePaths.slice(0, left)) {
                            try {
                                const small = await this.shrink(p)
                                const url = await this.upload(small)
                                if (url && this.info.images.length < (this.mode == 1 ? 1 : 4)) this.info.images.push(url)
                            } catch (e) {
                                uni.showToast({ title: '写真を送れませんでした', icon: 'none' })
                            }
                        }
                        this.uploading = false
                    }
                })
            },
            // 長い辺が 1280px を超える写真は縮め、画質 80 で保存し直す。小さくできないとき(ブラウザなど)はそのまま使う
            shrink(path) {
                return new Promise((resolve) => {
                    uni.getImageInfo({
                        src: path,
                        success: (info) => {
                            const opt = { src: path, quality: 80 }
                            if (Math.max(info.width, info.height) > MAX_SIDE) {
                                if (info.width >= info.height) opt.compressedWidth = MAX_SIDE
                                else opt.compressedHeight = MAX_SIDE
                            }
                            uni.compressImage(Object.assign(opt, {
                                success: (r) => resolve(r.tempFilePath || path),
                                fail: () => resolve(path)
                            }))
                        },
                        fail: () => resolve(path)
                    })
                })
            },
            upload(path) {
                return new Promise((resolve, reject) => {
                    uni.uploadFile({
                        url: this.$baseUrl + '/tool/upload/file',
                        filePath: path,
                        name: "file",
                        success: (result) => {
                            try {
                                const re = JSON.parse(result.data)
                                if (re.code == 200) resolve(re.data.path)
                                else reject(re)
                            } catch (e) {
                                reject(e)
                            }
                        },
                        fail: reject
                    })
                })
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-21] メッセージ作成(余白はほかの画面と同じ決まり) */
    page { background: #F2F2F7; }
    .ne-pv { font-size: 30rpx; color: #1A73E8; padding: 0 8rpx; }
    .ne-pg { padding: 40rpx 32rpx calc(96rpx + env(safe-area-inset-bottom)); }
    .ne-card { background: #FFFFFF; border-radius: 32rpx; padding: 40rpx 32rpx; margin-bottom: 32rpx; }
    /* [カスタムエイド 2026-10-21] 配信済み(見るだけ) */
    .ro-top { display: flex; align-items: center; margin-bottom: 32rpx; }
    .ro-chip { font-size: 24rpx; color: #6E6E73; background: #F2F2F7; border-radius: 999rpx; padding: 6rpx 20rpx; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ro-when { margin-left: 20rpx; font-size: 26rpx; color: #6E6E73; }
    .ro-row { display: flex; margin-top: 24rpx; font-size: 28rpx; line-height: 1.5; }
    .ro-row0 { margin-top: 0; }
    .ro-k { width: 200rpx; flex-shrink: 0; color: #6E6E73; }
    .ro-v { flex: 1; color: #1D1D1F; word-break: break-all; }
    .ro-link { color: #1A73E8; }
    .ro-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20rpx; }
    .ro-h { margin-bottom: 0; }
    .ro-copy { font-size: 26rpx; color: #1A73E8; background: rgba(26, 115, 232, 0.10); border: 1px solid #1A73E8; border-radius: 999rpx; padding: 10rpx 24rpx; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ro-text { font-size: 30rpx; color: #1D1D1F; line-height: 1.7; white-space: pre-wrap; word-break: break-all; background: #F7F7FA; border-radius: 20rpx; padding: 24rpx; }
    .ro-grid { margin-top: 20rpx; }
    .ne-h { font-size: 28rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.2; margin-bottom: 8rpx; }
    .ne-h2 { margin-top: 40rpx; }
    .ne-sub { font-size: 22rpx; color: #6E6E73; font-family: 'Hiragino Sans', sans-serif; }
    .ne-req { color: #D93025; }
    .ne-in { height: 96rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 0 28rpx; font-size: 30rpx; background: #FFFFFF; box-sizing: border-box; }
    .ne-ta { width: 100%; height: 320rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 24rpx 28rpx; font-size: 30rpx; line-height: 1.6; box-sizing: border-box; display: block; }
    .ne-in.on, .ne-ta.on { border-color: #1A73E8; box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.18); }
    .ne-ph { color: #AEAEB2; }
    .ne-chips { display: flex; margin-top: 24rpx; }
    .ne-chip { font-size: 26rpx; color: #1D1D1F; border: 1px solid #D2D2D7; border-radius: 999rpx; padding: 12rpx 28rpx; margin-right: 16rpx; background: #FFFFFF; }
    .ne-chip.on { border-color: #1A73E8; color: #1A73E8; background: #F5F9FF; }
    .ne-seg { display: flex; background: #F2F2F7; border-radius: 20rpx; padding: 6rpx; margin-top: 16rpx; }
    .ne-seg view { flex: 1; text-align: center; font-size: 26rpx; padding: 16rpx 0; border-radius: 16rpx; color: #6E6E73; border: 1px solid transparent; box-sizing: border-box; }
    /* 選んでいる方:薄い青の面+青い線+青い文字(全画面共通) */
    .ne-seg .on { background: rgba(26, 115, 232, 0.10); border-color: #1A73E8; color: #1A73E8; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ne-note { font-size: 24rpx; color: #8A8A8E; line-height: 1.6; margin-top: 16rpx; }
    .ne-note-top { margin: 16rpx 0 24rpx; }
    .ne-pad { margin: 0 8rpx; }
    .ne-grid { display: flex; flex-wrap: wrap; margin: 0 -8rpx; }
    .ne-im, .ne-add { width: calc(25% - 16rpx); margin: 0 8rpx; aspect-ratio: 1; height: 0; padding-bottom: calc(25% - 16rpx); position: relative; border-radius: 20rpx; overflow: hidden; box-sizing: border-box; }
    .ne-im-img { position: absolute; left: 0; top: 0; width: 100%; height: 100%; }
    .ne-add { border: 1.5px dashed #AECBFA; background: #F5F9FF; }
    .ne-add-p, .ne-add-t { position: absolute; left: 0; right: 0; text-align: center; color: #1A73E8; }
    .ne-add-p { top: 22%; font-size: 40rpx; }
    .ne-add-t { top: 62%; font-size: 20rpx; }
    .ne-x { position: absolute; right: 8rpx; top: 8rpx; width: 40rpx; height: 40rpx; border-radius: 50%; background: rgba(0, 0, 0, 0.55); color: #FFFFFF; font-size: 24rpx; line-height: 40rpx; text-align: center; }
    .ne-one { position: relative; width: 100%; height: 400rpx; border-radius: 24rpx; overflow: hidden; }
    .ne-one-img { width: 100%; height: 100%; }
    .ne-one-add { border: 1.5px dashed #AECBFA; background: #F5F9FF; box-sizing: border-box; }
    .ne-one-add .ne-add-p { top: 34%; }
    .ne-one-add .ne-add-t { top: 54%; font-size: 24rpx; }
    .ne-sw { display: flex; align-items: center; justify-content: space-between; padding: 32rpx; }
    .ne-sw-t { font-size: 30rpx; color: #1D1D1F; }
    .ne-btn { margin-top: 64rpx; height: 104rpx; border-radius: 28rpx; background: #34C759; color: #FFFFFF; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif; display: flex; align-items: center; justify-content: center; }
    .ne-hover { opacity: 0.7; }
    .ne-sheet { position: relative; padding: 24rpx 40rpx calc(48rpx + env(safe-area-inset-bottom)); }
    /* [カスタムエイド 2026-10-21] 閉じるボタン(右上の×) */
    .ne-shx { position: absolute; right: 24rpx; top: 20rpx; width: 64rpx; height: 64rpx; border-radius: 50%; background: #F2F2F7; color: #6E6E73; font-size: 36rpx; line-height: 64rpx; text-align: center; z-index: 2; }
    .ne-grip { width: 80rpx; height: 10rpx; border-radius: 6rpx; background: #D2D2D7; margin: 0 auto 36rpx; }
    .ne-sh-t { text-align: center; font-size: 34rpx; font-family: 'Hiragino Sans-W5', sans-serif; color: #1D1D1F; }
    .ne-sh-tip { text-align: center; font-size: 22rpx; color: #8A8A8E; margin: 12rpx 0 32rpx; }
    .ne-chat { max-height: 60vh; background: #E9EEF5; border-radius: 28rpx; padding: 28rpx 20rpx; box-sizing: border-box; }
    .ne-msg { display: flex; align-items: flex-start; margin-bottom: 20rpx; }
    .ne-bub { margin-left: 16rpx; background: #FFFFFF; border-radius: 28rpx; padding: 22rpx 26rpx; font-size: 28rpx; line-height: 1.6; color: #1D1D1F; max-width: 480rpx; white-space: pre-wrap; word-break: break-all; }
    .ne-pv-grid { margin-left: 16rpx; width: 440rpx; display: flex; flex-wrap: wrap; border-radius: 24rpx; overflow: hidden; }
    .ne-pv-cell { width: 220rpx; height: 220rpx; }
    .ne-pv-grid.n1 .ne-pv-cell { width: 440rpx; height: 440rpx; }
    .ne-pv-grid.n3 .ne-pv-cell:first-child { width: 440rpx; }
    .ne-pv-one { margin-left: 16rpx; width: 440rpx; border-radius: 24rpx; overflow: hidden; }
    .ne-pv-one-img { width: 440rpx; display: block; }
    .ne-pv-rv { margin-left: 16rpx; width: 480rpx; height: 120rpx; border-radius: 16rpx; overflow: hidden; }
    .ne-pv-rv-img { width: 100%; height: 100%; }
    .ne-empty { font-size: 26rpx; color: #8A8A8E; text-align: center; padding: 40rpx 0; }
    .ne-close { margin-top: 48rpx; height: 104rpx; border-radius: 28rpx; background: #F2F2F7; color: #1D1D1F; font-size: 32rpx; display: flex; align-items: center; justify-content: center; }
</style>
