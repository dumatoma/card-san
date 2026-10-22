<template>
    <!-- [カスタムエイド 2026-10-21] クーポン作成を作り直し(メッセージ作成と同じカード型・余白の決まり)
         ・source=add 新しく作る / copy 前のクーポンをコピーして作る(新しいクーポンになる) / edit 予約中のクーポンを直す
         ・有効期限は1つの枠で期間を選ぶ(カレンダー)、利用回数は 1〜10回 のボタン
         ・利用回数を決めると、回数を数えるためバーコードも自動でオン(スイッチの下に説明)
         ・写真は選んだときに自動で小さくする(長い辺 1280px まで)
         ・ボタンはメッセージと区別するため赤 -->
    <view class="ca">
        <u-navbar :title="query.source == 'edit' ? '予約クーポンの編集' : 'クーポン作成'" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD">
            <view slot="right" class="ca-pv" @click="show3 = true">プレビュー</view>
        </u-navbar>
        <view class="ca-pg">
            <view class="ca-copy" v-if="query.source == 'copy'">コピーした内容が入っています。有効期限はオフになっているので、必要なら選び直してください。直したいところを変えて「次へ」に進むと、新しいクーポンとして配信されます。</view>

            <view class="ca-card">
                <view class="ca-h">画像 <text class="ca-sub">(9枚まで・お客様は左右にめくって見られます)</text></view>
                <view class="ca-grid">
                    <view class="ca-im" v-for="(u, i) in info.banner" :key="u + i" @click="previewImg(i)">
                        <image class="ca-im-img" :src="u" mode="aspectFill"></image>
                        <view class="ca-x" @click.stop="del(i)">×</view>
                    </view>
                    <view class="ca-add" v-if="info.banner.length < 9" hover-class="ca-hover" @click="uploadImage">
                        <text class="ca-add-p">＋</text><text class="ca-add-t">追加</text>
                    </view>
                </view>
                <view class="ca-note" v-if="uploading">写真を準備しています…</view>
            </view>

            <view class="ca-card">
                <view class="ca-h"><text class="ca-req">※</text>タイトル</view>
                <input class="ca-in" :class="{ on: fk == 't' }" v-model="info.title" placeholder="例:お誕生月 全メニュー10%OFF" placeholder-class="ca-ph"
                    @focus="fk = 't'" @blur="fk = ''" />
                <view class="ca-h ca-h2">詳細</view>
                <textarea class="ca-ta" :class="{ on: fk == 'd' }" v-model="info.detail" maxlength="-1" :show-confirm-bar="false"
                    :disable-default-padding="true" placeholder="クーポンの内容・使い方を入力" placeholder-class="ca-ph"
                    @focus="fk = 'd'" @blur="fk = ''"></textarea>
                <view class="ca-h ca-h2">クーポン番号 <text class="ca-sub">(英数字・10桁まで・なくても可)</text></view>
                <input class="ca-in" :class="{ on: fk == 'n' }" v-model="info.number" maxlength="10" placeholder="例:BD2026" placeholder-class="ca-ph"
                    @focus="fk = 'n'" @blur="fk = ''" />
            </view>

            <view class="ca-card">
                <view class="ca-sw">
                    <text class="ca-sw-t">有効期限</text>
                    <u-switch :value="info.value" activeColor="#34C759" @change="info.value = !info.value"></u-switch>
                </view>
                <view class="ca-sel" v-if="info.value" hover-class="ca-hover" @click="openRange">
                    <text>{{ dLabel(info.sDate) }} 〜 {{ dLabel(info.eDate) }}</text><view class="ca-chev"></view>
                </view>
                <view class="ca-note">{{ info.value ? 'この期間だけ使えます。' : 'オフにすると、期限なしのクーポンになります。' }}</view>
            </view>

            <view class="ca-card">
                <view class="ca-sw">
                    <text class="ca-sw-t">利用回数の制限</text>
                    <u-switch :value="info.value2" activeColor="#34C759" @change="changeCount"></u-switch>
                </view>
                <block v-if="info.value2">
                    <view class="ca-lbl">1人あたり</view>
                    <view class="ca-cnt">
                        <view v-for="n in 10" :key="n" class="ca-chip" :class="{ on: info.count == n }" @click="info.count = n">{{ n }}回</view>
                    </view>
                    <view class="ca-note">回数を数えるために、バーコードも自動でオンになります。</view>
                </block>
                <view class="ca-note" v-else>オフにすると、何回でも使えます。</view>
            </view>

            <view class="ca-card">
                <view class="ca-sw">
                    <text class="ca-sw-t">バーコード</text>
                    <u-switch :value="info.value3" activeColor="#34C759" @change="changeBar"></u-switch>
                </view>
                <view class="ca-note" v-if="!info.value3">オフにすると、バーコードなしのクーポンになります(画面を見せて使います)。</view>
                <!-- [カスタムエイド 2026-10-22] 自動で発行/お店の番号を使う(レジで読み取る) -->
                <block v-if="info.value3">
                    <view class="ca-seg">
                        <view :class="{ on: info.bcMode == 0 }" @click="info.bcMode = 0">自動で発行<text class="ca-seg-s">(アプリで読み取る)</text></view>
                        <view :class="{ on: info.bcMode == 1 }" @click="info.bcMode = 1">お店の番号を使う<text class="ca-seg-s">(レジで読み取る)</text></view>
                    </view>
                    <view class="ca-note" v-if="info.bcMode == 0">お店で、管理Appの読み取りで使います。バーコードはクーポンを配信すると、お客様ごとに自動で付きます。</view>
                    <block v-else>
                        <view class="ca-note">お店のレジ(POS)で使っているバーコードの番号を入れると、同じバーコードがお客様のクーポンに出ます。</view>
                        <view class="ca-lbl">バーコードの種類</view>
                        <view class="ca-seg ca-seg-s2">
                            <view :class="{ on: info.bcType == 1 }" @click="info.bcType = 1">JAN(13桁・8桁の数字)</view>
                            <view :class="{ on: info.bcType == 2 }" @click="info.bcType = 2">CODE128(英数字)</view>
                        </view>
                        <view class="ca-lbl">バーコードの番号</view>
                        <input class="ca-in" :class="{ on: fk == 'b', ng: bcCheck.message && !bcCheck.ok }" v-model="info.bcValue" :type="info.bcType == 1 ? 'number' : 'text'"
                            :maxlength="info.bcType == 1 ? 13 : 48" :placeholder="info.bcType == 1 ? '例:4912345678904' : '例:CS-2026-001'" placeholder-class="ca-ph"
                            @focus="fk = 'b'" @blur="fk = ''" />
                        <view class="ca-chk" :class="{ ok: bcCheck.ok }" v-if="bcCheck.message">{{ bcCheck.ok ? '✓ ' : '' }}{{ bcCheck.message }}</view>
                        <view class="ca-bc" v-if="bcBars.length">
                            <view class="ca-bc-l">お客様に出るバーコード(見本)</view>
                            <view class="ca-bc-bars">
                                <view v-for="(b, i) in bcBars" :key="i" class="ca-bc-b" :style="{ flex: b.w, background: b.b ? '#000' : 'transparent' }"></view>
                            </view>
                            <view class="ca-bc-n">{{ bcCheck.value }}</view>
                        </view>
                        <view class="ca-note">お客様は、お店で「今すぐ使う」を押してから、このバーコードを10分間見せます。押したときに「使った」と記録されるので、回数制限もそのまま使えます。</view>
                    </block>
                </block>
            </view>

            <view class="ca-card">
                <view class="ca-h">備考 <text class="ca-sub">(お客様にも表示されます)</text></view>
                <textarea class="ca-ta ca-ta-s" :class="{ on: fk == 'r' }" v-model="info.remark" maxlength="-1" :show-confirm-bar="false"
                    :disable-default-padding="true" placeholder="例:ほかのクーポンとの併用はできません" placeholder-class="ca-ph"
                    @focus="fk = 'r'" @blur="fk = ''"></textarea>
            </view>

            <view class="ca-btn" :class="{ off: !(info.title || '').trim() }" hover-class="ca-hover" @click="nextStep">次へ(配信先・日時の設定)</view>
        </view>

        <!-- 有効期限(期間) -->
        <u-popup :show="showRange" mode="bottom" round="24" @close="showRange = false">
            <view class="ca-sheet">
                <view class="ca-grip"></view>
                <view class="ca-shx" hover-class="ca-hover" @click="showRange = false">×</view><view class="ca-sh-t">有効期限</view>
                <view class="ca-chips">
                    <view v-for="(q, i) in quickRanges" :key="i" class="ca-chip ca-chip-s" :class="{ on: rFrom == q.f && rTo == q.t2 }" @click="rFrom = q.f; rTo = q.t2">{{ q.t }}</view>
                </view>
                <view class="ca-cal"><cs-calendar :key="'r' + calKey" mode="range" :from="rFrom" :to="rTo" :min="minDate" @range="rFrom = $event.from; rTo = $event.to"></cs-calendar></view>
                <view class="ca-res">はじめの日 → おわりの日の順に押します。<view class="ca-res-b">{{ rFrom ? (dLabel(rFrom) + ' 〜 ' + (rTo ? dLabel(rTo) : 'おわりの日を押してください')) : 'はじめの日を押してください' }}</view></view>
                <view class="ca-bbtn" :class="{ off: !rFrom }" hover-class="ca-hover" @click="confirmRange">この期間にする</view>
            </view>
        </u-popup>

        <!-- プレビュー:お客様の Card-San App での見え方 -->
        <u-popup :show="show3" mode="bottom" round="24" @close="show3 = false">
            <view class="ca-sheet">
                <view class="ca-grip"></view>
                <view class="ca-shx" hover-class="ca-hover" @click="show3 = false">×</view><view class="ca-sh-t">プレビュー</view>
                <view class="ca-sh-tip">端末やOSによって、実際の見え方と少し異なる場合があります。</view>
                <scroll-view scroll-y class="ca-pvw">
                    <swiper v-if="info.banner.length" class="ca-pv-sw" :indicator-dots="info.banner.length > 1">
                        <swiper-item v-for="(u, i) in info.banner" :key="i"><image class="ca-pv-img" :src="u" mode="aspectFill"></image></swiper-item>
                    </swiper>
                    <view class="ca-pv-b">
                        <view class="ca-pv-t">{{ info.title || 'タイトル' }}</view>
                        <view class="ca-pv-no" v-if="info.number">クーポン番号 {{ info.number }}</view>
                        <view class="ca-pv-bar" v-if="info.value3 && info.bcMode == 0">
                            <image class="ca-pv-bar-img" src="../../../static/images/ITF.png" mode="aspectFit"></image>
                            <view class="ca-pv-bar-n">(バーコードはお客様ごとに付きます)</view>
                        </view>
                        <view class="ca-pv-bar" v-if="info.value3 && info.bcMode == 1">
                            <view class="ca-pv-slide">スライドして使う</view>
                            <view class="ca-pv-bar-n">(お客様が「今すぐ使う」を押すと、お店のバーコードが10分間出ます)</view>
                        </view>
                        <view class="ca-pv-d" v-if="info.detail">{{ info.detail }}</view>
                        <view class="ca-pv-r"><text class="ca-pv-k">有効期限</text><text>{{ info.value ? dLabel(info.sDate) + ' 〜 ' + dLabel(info.eDate) : '期限なし' }}</text></view>
                        <view class="ca-pv-r" v-if="info.value2"><text class="ca-pv-k">利用回数</text><text>残り{{ info.count }}回</text></view>
                        <view class="ca-pv-r" v-if="info.remark"><text class="ca-pv-k">備考</text><text class="ca-pv-rm">{{ info.remark }}</text></view>
                    </view>
                </scroll-view>
                <view class="ca-close" hover-class="ca-hover" @click="show3 = false">閉じる</view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
    import csCalendar from "@/components/csCalendar/csCalendar.vue"
    import { checkCode, barcodeBars } from "@/utils/csBarcode.js" // [カスタムエイド 2026-10-22] お店のバーコード番号
    import { getCouponDetail } from "@/api/index.js"

    const MAX_SIDE = 1280 // 写真の長い辺の上限(px)
    const pad = (n) => (n < 10 ? '0' : '') + n
    const fmt = (dt) => dt.getFullYear() + '-' + pad(dt.getMonth() + 1) + '-' + pad(dt.getDate())
    const addDays = (s, n) => { const p = s.split('-'); return fmt(new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]) + n)) }
    const WEEK = ['日', '月', '火', '水', '木', '金', '土']
    // [カスタムエイド 2026-10-21] サーバーの日付は「2026-10-22」「2026/10/22」「2026.10.22」や秒の数字などいろいろなので、「YYYY-MM-DD」にそろえる
    const toDay = (v) => {
        if (v === null || v === undefined || v === '' || v === 0 || v === '0') return ''
        if (/^\d{9,}$/.test(String(v))) {
            const d = new Date(Number(v) * 1000)
            return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2)
        }
        const m = String(v).match(/(\d{4})\D(\d{1,2})\D(\d{1,2})/)
        return m ? (m[1] + '-' + ('0' + m[2]).slice(-2) + '-' + ('0' + m[3]).slice(-2)) : ''
    }

    export default {
        components: { csCalendar },
        data() {
            const t = fmt(new Date())
            return {
                query: {},
                today: t,
                fk: '',
                uploading: false,
                show3: false,
                showRange: false,
                calKey: 0, // [カスタムエイド 2026-10-21] 開くたびに選んでいる月から見せる
                rFrom: '',
                rTo: '',
                // 作成画面 → 配信設定へは今までと同じ形(couponInfo)で渡す
                info: {
                    banner: [],
                    title: "",
                    detail: "",
                    number: "",
                    remark: "",
                    value: false, // 有効期限
                    sDate: t,
                    eDate: addDays(t, 30),
                    value2: false, // 利用回数の制限
                    count: 1,
                    value3: false, // バーコード
                    bcMode: 0, // [カスタムエイド 2026-10-22] 0 = 自動で発行 / 1 = お店の番号を使う
                    bcType: 1, // 1 = JAN / 2 = CODE128
                    bcValue: ''
                }
            }
        },
        computed: {
            bcCheck() {
                return checkCode(this.info.bcType, this.info.bcValue)
            },
            bcBars() {
                return barcodeBars(this.info.bcType, this.info.bcValue)
            },
            minDate() {
                // 新しく作る・コピーは今日から。予約の編集は前の日付のままでも直せるように制限なし
                return this.query.source == 'edit' ? '' : this.today
            },
            quickRanges() {
                const t = this.today
                const p = t.split('-')
                const monthEnd = fmt(new Date(Number(p[0]), Number(p[1]), 0))
                return [
                    { t: '1週間', f: t, t2: addDays(t, 6) },
                    { t: '2週間', f: t, t2: addDays(t, 13) },
                    { t: '1か月', f: t, t2: addDays(t, 29) },
                    { t: '今月末まで', f: t, t2: monthEnd }
                ]
            }
        },
        onLoad(options) {
            this.query = options
            if (options.source == 'copy') {
                const c = uni.getStorageSync('couponCopy')
                if (c) this.fill(c, true)
            } else if (options.source == 'edit') {
                getCouponDetail(options.id).then((res) => {
                    if (res.code == 200) {
                        this.fill(res.data.coupon, false)
                        uni.setStorageSync('couponDetail', res.data.coupon) // 配信設定で日時・絞り込みを戻すため
                    } else {
                        uni.showToast({ title: res.message, icon: 'none' })
                    }
                }).catch(() => {})
            }
        },
        methods: {
            dLabel(s) {
                if (!s) return ''
                const p = String(s).slice(0, 10).split('-')
                const d = new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]))
                return Number(p[1]) + '月' + Number(p[2]) + '日(' + WEEK[d.getDay()] + ')'
            },
            // 前のクーポンの内容を入れる(コピー・編集)
            fill(c, isCopy) {
                const day = toDay
                this.info = {
                    banner: Array.isArray(c.images) ? c.images.slice(0, 9) : [],
                    title: c.title || '',
                    detail: c.des || '',
                    number: c.number || '',
                    remark: c.remark || '',
                    // コピーのときは有効期限をオフにする(期限は選び直してもらう)
                    value: isCopy ? false : c.is_indate == 1,
                    sDate: day(c.indate_start_time) || this.today,
                    eDate: day(c.indate_end_time) || addDays(this.today, 30),
                    value2: Number(c.count) > 0,
                    count: Number(c.count) > 0 ? Number(c.count) : 1,
                    value3: c.is_bar_code == 1,
                    bcMode: Number(c.barcode_type) > 0 ? 1 : 0,
                    bcType: Number(c.barcode_type) == 2 ? 2 : 1,
                    bcValue: c.barcode_value || ''
                }
                // コピーのときは、期限の候補を今日からの同じ日数にしておく(オンにしたとき用)
                if (isCopy && c.is_indate == 1 && this.info.sDate && this.info.eDate) {
                    const len = Math.max(0, Math.round((new Date(this.info.eDate) - new Date(this.info.sDate)) / 86400000))
                    this.info.sDate = this.today
                    this.info.eDate = addDays(this.today, len || 30)
                }
                if (!this.info.sDate) this.info.sDate = this.today
                if (!this.info.eDate || this.info.eDate < this.info.sDate) this.info.eDate = addDays(this.info.sDate, 30)
            },
            changeCount() {
                this.info.value2 = !this.info.value2
                if (this.info.value2) this.info.value3 = true // 回数を数えるにはバーコードが必要
            },
            changeBar() {
                if (this.info.value3 && this.info.value2) {
                    uni.showToast({ title: '利用回数の制限をオフにすると、バーコードもオフにできます', icon: 'none', duration: 2500 })
                    // スイッチの見た目を戻す
                    this.info.value3 = false
                    this.$nextTick(() => { this.info.value3 = true })
                    return
                }
                this.info.value3 = !this.info.value3
            },
            openRange() {
                this.calKey++
                this.rFrom = this.info.sDate
                this.rTo = this.info.eDate
                this.showRange = true
            },
            confirmRange() {
                if (!this.rFrom) return
                this.info.sDate = this.rFrom
                this.info.eDate = this.rTo || this.rFrom
                this.showRange = false
            },
            del(i) {
                this.info.banner.splice(i, 1)
            },
            previewImg(i) {
                uni.previewImage({ urls: this.info.banner, current: i })
            },
            uploadImage() {
                const left = 9 - this.info.banner.length
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
                                if (url && this.info.banner.length < 9) this.info.banner.push(url)
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
            },
            nextStep() {
                if (this.uploading) {
                    uni.showToast({ title: '写真を準備しています。少しお待ちください', icon: 'none' })
                    return
                }
                if (!(this.info.title || '').trim()) {
                    uni.showToast({ title: 'タイトルを入力してください', icon: 'none' })
                    return
                }
                if (this.info.number && !/^[0-9A-Za-z]{1,10}$/.test(this.info.number)) {
                    uni.showToast({ title: 'クーポン番号は英数字10桁までで入力してください', icon: 'none' })
                    return
                }
                if (this.info.value && this.query.source != 'edit' && this.info.eDate < this.today) {
                    uni.showToast({ title: '有効期限が過ぎています。選び直してください', icon: 'none' })
                    return
                }
                // お店のバーコード番号を確かめて、そろえた番号で送る(12桁・7桁なら最後の1桁を付ける)
                const pos = { barcode_type: 0, barcode_value: '' }
                if (this.info.value3 && this.info.bcMode == 1) {
                    if (!this.bcCheck.ok) {
                        uni.showToast({ title: this.bcCheck.message || 'バーコードの番号を入力してください', icon: 'none', duration: 2500 })
                        return
                    }
                    pos.barcode_type = this.info.bcType
                    pos.barcode_value = this.bcCheck.value
                }
                uni.setStorageSync("couponInfo", Object.assign({}, this.info, { title: this.info.title.trim() }, pos))
                if (this.query.source == 'edit') {
                    uni.redirectTo({ url: "../setting/setting?source=edit&id=" + this.query.id })
                } else {
                    uni.removeStorageSync("couponDetail")
                    uni.redirectTo({ url: "../setting/setting?source=add&id=0" })
                }
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-21] クーポン作成(余白はほかの画面と同じ決まり) */
    page { background: #F2F2F7; }
    .ca-pv { font-size: 30rpx; color: #1A73E8; padding: 0 8rpx; }
    .ca-pg { padding: 40rpx 32rpx calc(96rpx + env(safe-area-inset-bottom)); }
    .ca-copy { background: #FCE8E6; color: #A50E0E; font-size: 26rpx; line-height: 1.6; border-radius: 24rpx; padding: 24rpx 28rpx; margin-bottom: 32rpx; }
    .ca-card { background: #FFFFFF; border-radius: 32rpx; padding: 40rpx 32rpx; margin-bottom: 32rpx; }
    .ca-h { font-size: 28rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.2; margin-bottom: 8rpx; }
    .ca-h2 { margin-top: 40rpx; }
    .ca-sub { font-size: 22rpx; color: #6E6E73; font-family: 'Hiragino Sans', sans-serif; }
    .ca-req { color: #D93025; }
    .ca-in { width: 100%; height: 96rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 0 28rpx; font-size: 30rpx; background: #FFFFFF; box-sizing: border-box; }
    .ca-ta { width: 100%; height: 280rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 24rpx 28rpx; font-size: 30rpx; line-height: 1.6; box-sizing: border-box; display: block; }
    .ca-ta-s { height: 200rpx; }
    .ca-in.on, .ca-ta.on { border-color: #1A73E8; box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.18); }
    .ca-ph { color: #AEAEB2; }
    .ca-note { font-size: 24rpx; color: #8A8A8E; line-height: 1.6; margin-top: 16rpx; }
    .ca-grid { display: flex; flex-wrap: wrap; margin: 16rpx -8rpx 0; }
    .ca-im, .ca-add { width: calc(25% - 16rpx); margin: 0 8rpx 16rpx; height: 0; padding-bottom: calc(25% - 16rpx); position: relative; border-radius: 20rpx; overflow: hidden; box-sizing: border-box; }
    .ca-im-img { position: absolute; left: 0; top: 0; width: 100%; height: 100%; }
    .ca-add { border: 1.5px dashed #AEAEB2; background: #FAFAFC; }
    .ca-add-p, .ca-add-t { position: absolute; left: 0; right: 0; text-align: center; color: #6E6E73; }
    .ca-add-p { top: 22%; font-size: 40rpx; }
    .ca-add-t { top: 62%; font-size: 20rpx; }
    .ca-x { position: absolute; right: 8rpx; top: 8rpx; width: 40rpx; height: 40rpx; border-radius: 50%; background: rgba(0, 0, 0, 0.55); color: #FFFFFF; font-size: 24rpx; line-height: 40rpx; text-align: center; }
    .ca-sw { display: flex; align-items: center; justify-content: space-between; }
    .ca-sw-t { font-size: 30rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ca-sel { display: flex; align-items: center; justify-content: space-between; height: 96rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 0 28rpx; font-size: 30rpx; color: #1D1D1F; margin-top: 32rpx; }
    .ca-chev { width: 14rpx; height: 14rpx; border-right: 3rpx solid #C7C7CC; border-bottom: 3rpx solid #C7C7CC; transform: rotate(-45deg); }
    .ca-lbl { font-size: 26rpx; color: #1D1D1F; margin: 32rpx 0 16rpx; }
    .ca-cnt { display: flex; flex-wrap: wrap; margin: 0 -8rpx; }
    .ca-cnt .ca-chip { width: calc(20% - 16rpx); margin: 0 8rpx 16rpx; padding: 0; }
    .ca-chips { display: flex; flex-wrap: wrap; margin-bottom: 8rpx; }
    .ca-chip { height: 72rpx; display: flex; align-items: center; justify-content: center; border: 1px solid #D2D2D7; border-radius: 999rpx; font-size: 26rpx; color: #1D1D1F; background: #FFFFFF; box-sizing: border-box; padding: 0 28rpx; }
    .ca-chip-s { margin: 0 16rpx 16rpx 0; }
    /* 選んでいる方:薄い青の面+青い線+青い文字(全画面共通) */
    .ca-chip.on { background: rgba(26, 115, 232, 0.10); border-color: #1A73E8; color: #1A73E8; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ca-btn { margin-top: 64rpx; height: 104rpx; border-radius: 28rpx; background: #D93025; color: #FFFFFF; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif; display: flex; align-items: center; justify-content: center; }
    .ca-btn.off { opacity: 0.45; }
    .ca-hover { opacity: 0.7; }
    .ca-sheet { position: relative; padding: 24rpx 40rpx calc(48rpx + env(safe-area-inset-bottom)); }
    /* [カスタムエイド 2026-10-21] 閉じるボタン(右上の×) */
    .ca-shx { position: absolute; right: 24rpx; top: 20rpx; width: 64rpx; height: 64rpx; border-radius: 50%; background: #F2F2F7; color: #6E6E73; font-size: 36rpx; line-height: 64rpx; text-align: center; z-index: 2; }
    .ca-grip { width: 80rpx; height: 10rpx; border-radius: 6rpx; background: #D2D2D7; margin: 0 auto 36rpx; }
    .ca-sh-t { text-align: center; font-size: 34rpx; font-family: 'Hiragino Sans-W5', sans-serif; color: #1D1D1F; margin-bottom: 28rpx; }
    .ca-sh-tip { text-align: center; font-size: 22rpx; color: #8A8A8E; margin: -16rpx 0 32rpx; }
    .ca-cal { margin-top: 16rpx; }
    .ca-res { font-size: 24rpx; color: #6E6E73; text-align: center; margin-top: 24rpx; line-height: 1.6; }
    .ca-res-b { font-size: 30rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; margin-top: 4rpx; }
    .ca-bbtn { height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif; display: flex; align-items: center; justify-content: center; margin-top: 32rpx; }
    .ca-bbtn.off { opacity: 0.45; }
    .ca-pvw { max-height: 60vh; background: #F2F2F7; border-radius: 28rpx; overflow: hidden; }
    .ca-pv-sw, .ca-pv-img { width: 100%; height: 360rpx; }
    .ca-pv-b { background: #FFFFFF; padding: 32rpx; }
    .ca-pv-t { font-size: 34rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.4; }
    .ca-pv-no { font-size: 26rpx; color: #6E6E73; margin-top: 8rpx; }
    .ca-pv-bar { text-align: center; margin: 24rpx 0 8rpx; }
    .ca-pv-bar-img { width: 560rpx; height: 120rpx; }
    .ca-pv-bar-n { font-size: 22rpx; color: #8A8A8E; }
    /* [カスタムエイド 2026-10-22] お店のバーコード番号 */
    .ca-seg { display: flex; background: #F2F2F7; border-radius: 20rpx; padding: 6rpx; margin-top: 28rpx; }
    .ca-seg view { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; font-size: 26rpx; line-height: 1.35; min-height: 84rpx; padding: 8rpx 4rpx; border-radius: 16rpx; color: #6E6E73; border: 1px solid transparent; box-sizing: border-box; }
    .ca-seg .on { background: rgba(26, 115, 232, 0.10); border-color: #1A73E8; color: #1A73E8; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ca-seg-s { font-size: 22rpx; }
    .ca-seg-s2 { margin-top: 0; }
    .ca-seg-s2 view { min-height: 76rpx; }
    .ca-in.ng { border-color: #D93025; box-shadow: 0 0 0 6rpx rgba(217, 48, 37, 0.15); }
    .ca-chk { font-size: 24rpx; color: #D93025; margin-top: 10rpx; }
    .ca-chk.ok { color: #188038; }
    .ca-bc { border: 1px solid #E5E5EA; border-radius: 24rpx; padding: 24rpx 24rpx 16rpx; margin-top: 24rpx; text-align: center; }
    .ca-bc-l { font-size: 22rpx; color: #8A8A8E; margin-bottom: 16rpx; }
    .ca-bc-bars { display: flex; height: 140rpx; }
    .ca-bc-b { height: 100%; }
    .ca-bc-n { font-size: 26rpx; letter-spacing: 4rpx; color: #1D1D1F; margin-top: 8rpx; }
    .ca-pv-slide { height: 88rpx; border-radius: 44rpx; background: #FCE8E6; color: #D93025; font-size: 28rpx; display: flex; align-items: center; justify-content: center; margin: 0 40rpx 8rpx; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ca-pv-d { font-size: 28rpx; color: #1D1D1F; line-height: 1.7; white-space: pre-wrap; margin-top: 24rpx; }
    .ca-pv-r { display: flex; font-size: 26rpx; color: #1D1D1F; margin-top: 16rpx; }
    .ca-pv-k { width: 160rpx; flex-shrink: 0; color: #6E6E73; }
    .ca-pv-rm { flex: 1; white-space: pre-wrap; }
    .ca-close { margin-top: 32rpx; height: 104rpx; border-radius: 28rpx; background: #F2F2F7; color: #1D1D1F; font-size: 32rpx; display: flex; align-items: center; justify-content: center; }
</style>
