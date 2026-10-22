<template>
    <!-- [カスタムエイド 2026-10-21] クーポンの詳しい画面(見るだけ)
         ・配信済み:「このクーポンをコピー」「削除」
         ・予約中:「編集」「このクーポンをコピー」「削除」
         ・コピーすると、同じ内容が入ったクーポン作成の画面が開き、新しいクーポンとしてそのまま配信まで進める -->
    <view class="cd">
        <u-navbar title="クーポンの詳細" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="cd-pg" v-if="c.id">
            <view class="cd-card cd-card0" v-if="c.images && c.images.length">
                <swiper class="cd-sw" :indicator-dots="c.images.length > 1" indicator-active-color="#1D1D1F">
                    <swiper-item v-for="(u, i) in c.images" :key="i" @click="preview(i)">
                        <image class="cd-sw-img" :src="u" mode="aspectFill"></image>
                    </swiper-item>
                </swiper>
            </view>

            <view class="cd-card">
                <view class="cd-top">
                    <text class="cd-chip" :class="reserved ? 'rv' : 'g'">{{ reserved ? '予約中' : '配信済' }}</text>
                    <text class="cd-when">{{ whenText }}</text>
                </view>
                <view class="cd-t">{{ c.title }}</view>
                <view class="cd-row"><text class="cd-k">配信先</text><text class="cd-v">{{ target }}</text></view>
                <view class="cd-row" v-if="!reserved && li.send_count !== undefined"><text class="cd-k">配信数</text><text class="cd-v">{{ num(li.send_count) }}人</text></view>
                <view class="cd-row" v-if="!reserved && li.use_count !== undefined"><text class="cd-k">利用回数</text><text class="cd-v">{{ num(li.use_count) }}回</text></view>
            </view>

            <view class="cd-card">
                <view class="cd-row cd-row0"><text class="cd-k">有効期限</text><text class="cd-v">{{ period }}</text></view>
                <view class="cd-row"><text class="cd-k">クーポン番号</text><text class="cd-v">{{ c.number || 'なし' }}</text></view>
                <view class="cd-row"><text class="cd-k">回数制限</text><text class="cd-v">{{ c.count > 0 ? '1人' + c.count + '回まで' : 'なし' }}</text></view>
                <view class="cd-row"><text class="cd-k">バーコード</text><text class="cd-v">{{ c.is_bar_code != 1 ? 'なし' : (Number(c.barcode_type) > 0 ? ('お店の番号(' + (c.barcode_type == 2 ? 'CODE128' : 'JAN') + ') ' + (c.barcode_value || '')) : 'あり(自動で発行)') }}</text></view>
            </view>

            <view class="cd-card" v-if="c.des">
                <view class="cd-h">詳細</view>
                <view class="cd-text">{{ c.des }}</view>
            </view>
            <view class="cd-card" v-if="c.remark">
                <view class="cd-h">備考</view>
                <view class="cd-text">{{ c.remark }}</view>
            </view>
            <view class="cd-note">{{ reserved ? '予約中のクーポンは編集できます。' : '配信済みのクーポンは編集できません。' }}同じ内容をもう一度送りたいときは「このクーポンをコピー」を使ってください。</view>
        </view>

        <view class="cd-foot" v-if="c.id">
            <view class="cd-btn cd-btn-main" v-if="reserved" hover-class="cd-hover" @click="toEdit">編集する</view>
            <view class="cd-btn" :class="reserved ? 'cd-btn-sub' : 'cd-btn-main'" hover-class="cd-hover" @click="toCopy">このクーポンをコピー</view>
            <view class="cd-del" hover-class="cd-hover" @click="showDel = true">削除</view>
        </view>

        <cs-dialog :show="showDel" title="クーポンを削除" :message="'このクーポンを削除しますか?\n削除すると元に戻せません。'" ok-text="削除する" ok-color="red"
            @ok="doDel" @cancel="showDel = false"></cs-dialog>
        <permission :show="showpermission" @close="showpermission = false"></permission>
    </view>
</template>

<script>
    import { getCouponDetail, deleteCoupon, getAdminDetail } from '@/api/index.js'
    import permission from "@/components/permission/permission.vue"
    import csDialog from "@/components/csDialog/csDialog.vue"
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
        components: { permission, csDialog },
        data() {
            return {
                id: '',
                c: {},
                li: {},
                showDel: false,
                showpermission: false
            }
        },
        computed: {
            reserved() {
                return this.c.is_send == 0 && this.c.is_order == 1
            },
            whenText() {
                const t = this.c.order_time
                if (!t || t == '0') return ''
                return String(t).replace(/-/g, '/').slice(0, 16) + (this.reserved ? ' に配信予定' : ' 配信')
            },
            period() {
                if (this.c.is_indate != 1) return '期限なし'
                return this.md(this.c.indate_start_time) + ' 〜 ' + this.md(this.c.indate_end_time)
            },
            // 配信先のまとめ(全員/絞り込みの中身)
            target() {
                const d = this.c
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
        onLoad(o) {
            this.id = o.id
            const li = uni.getStorageSync('couponListItem')
            if (li && li.id == o.id) this.li = li
        },
        onShow() {
            this.getDetail()
        },
        methods: {
            num(n) {
                return (Number(n) || 0).toLocaleString()
            },
            md(s) {
                if (!s) return ''
                const p = toDay(s).split('-')
                if (p.length < 3) return s
                const d = new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]))
                return p[0] + '/' + Number(p[1]) + '/' + Number(p[2]) + '(' + '日月火水木金土'[d.getDay()] + ')'
            },
            getDetail() {
                getCouponDetail(this.id).then((res) => {
                    if (res.code == 200) {
                        const c = res.data.coupon
                        // 一覧の情報で、配信済みかどうかを補う(詳しい情報に無い場合のため)
                        if (c.is_send === undefined && this.li.id) c.is_send = this.li.is_send
                        if (c.is_order === undefined && this.li.id) c.is_order = this.li.is_order
                        c.id = c.id || this.id
                        this.c = c
                    } else {
                        uni.showToast({ title: res.message, icon: 'none' })
                    }
                }).catch(() => {})
            },
            preview(i) {
                uni.previewImage({ urls: this.c.images, current: i })
            },
            // クーポンを作る権限(副管理者は権限 7 が必要)
            can(go) {
                const admin = uni.getStorageSync("admin")
                if (admin.admin_type != 2) return go()
                uni.showLoading({ title: "読み込み中", mask: true })
                getAdminDetail(admin.id).then((res) => {
                    uni.hideLoading()
                    if (res.data.admin.privileges.includes(7)) go()
                    else this.showpermission = true
                }).catch(() => { uni.hideLoading() })
            },
            toEdit() {
                this.can(() => uni.navigateTo({ url: "../add/add?source=edit&id=" + this.id }))
            },
            toCopy() {
                this.can(() => {
                    uni.setStorageSync('couponCopy', this.c)
                    uni.navigateTo({ url: "../add/add?source=copy" })
                })
            },
            doDel() {
                this.showDel = false
                deleteCoupon(this.id).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({ title: '削除しました', icon: 'none' })
                        setTimeout(() => uni.navigateBack(), 800)
                    } else {
                        uni.showToast({ title: res.message, icon: 'none' })
                    }
                }).catch(() => {})
            }
        }
    }
</script>

<style lang="scss">
    page { background: #F2F2F7; }
    .cd-pg { padding: 40rpx 32rpx calc(380rpx + env(safe-area-inset-bottom)); }
    .cd-card { background: #FFFFFF; border-radius: 32rpx; padding: 40rpx 32rpx; margin-bottom: 32rpx; }
    .cd-card0 { padding: 0; overflow: hidden; }
    .cd-sw { width: 100%; height: 400rpx; }
    .cd-sw-img { width: 100%; height: 400rpx; }
    .cd-top { display: flex; align-items: center; margin-bottom: 24rpx; }
    .cd-chip { font-size: 24rpx; border-radius: 999rpx; padding: 6rpx 20rpx; font-family: 'Hiragino Sans-W5', sans-serif; }
    .cd-chip.g { background: #F2F2F7; color: #6E6E73; }
    .cd-chip.rv { background: #1A73E8; color: #FFFFFF; }
    .cd-when { margin-left: 20rpx; font-size: 26rpx; color: #6E6E73; }
    .cd-t { font-size: 36rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.4; overflow-wrap: anywhere; }
    .cd-row { display: flex; margin-top: 24rpx; font-size: 28rpx; line-height: 1.5; }
    .cd-row0 { margin-top: 0; }
    .cd-k { width: 200rpx; flex-shrink: 0; color: #6E6E73; }
    .cd-v { flex: 1; color: #1D1D1F; word-break: break-all; }
    .cd-h { font-size: 28rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.2; margin-bottom: 16rpx; }
    .cd-text { font-size: 30rpx; color: #1D1D1F; line-height: 1.7; white-space: pre-wrap; word-break: break-all; }
    .cd-note { font-size: 24rpx; color: #8A8A8E; line-height: 1.6; margin: 0 8rpx; }
    .cd-foot { position: fixed; left: 0; right: 0; bottom: 0; z-index: 10; padding: 24rpx 32rpx calc(16rpx + env(safe-area-inset-bottom)); background: rgba(242, 242, 247, 0.86); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-top: 1px solid rgba(0, 0, 0, 0.06); }
    .cd-btn { height: 104rpx; border-radius: 28rpx; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif; display: flex; align-items: center; justify-content: center; }
    .cd-btn + .cd-btn { margin-top: 24rpx; }
    .cd-btn-main { background: #D93025; color: #FFFFFF; }
    .cd-btn-sub { background: #FFFFFF; color: #D93025; border: 2rpx solid #D93025; }
    .cd-del { height: 88rpx; display: flex; align-items: center; justify-content: center; font-size: 30rpx; color: #D93025; margin-top: 8rpx; }
    .cd-hover { opacity: 0.7; }
</style>
