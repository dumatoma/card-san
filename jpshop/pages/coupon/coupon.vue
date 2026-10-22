<template>
    <!-- [カスタムエイド 2026-10-21] クーポン一覧を作り直し
         ・上に今月の配信数(使った量のバー)
         ・カードを押すと、クーポンの詳しい画面(予約中:編集/コピー/削除、配信済み:コピー/削除)
         ・左のチェックは複数選べる。選ぶと下のボタンが「選んだ○件を削除」に変わる
         ・下に固定の「＋ クーポン作成」(クーポンはメッセージと区別するため赤) -->
    <view class="cl">
        <u-navbar title="クーポン一覧" :placeholder="true" :border="true" :safeAreaInsetTop="true" bgColor="#FBFBFD">
            <view class="cl-x" slot="left" @click="toBack">×</view>
        </u-navbar>
        <view class="cl-pg">
            <view class="cl-sum">
                <view class="cl-sum-t">
                    <text class="cl-sum-k">今月の配信数</text>
                    <text><text class="cl-sum-n">{{ num(coupon_count) }}</text><text class="cl-sum-m"> / {{ num(sum_count) }}通</text></text>
                </view>
                <view class="cl-bar"><view class="cl-bar-i" :class="{ over: coupon_count >= sum_count }" :style="{ width: barW }"></view></view>
            </view>

            <view class="cl-lab" v-if="array.length">配信履歴</view>
            <view class="cl-row" v-for="item in array" :key="item.id">
                <view class="cl-ckw" @click="toggle(item.id)">
                    <view class="cl-ck" :class="{ on: picked.indexOf(item.id) > -1 }"><view class="cl-ck-m" v-if="picked.indexOf(item.id) > -1"></view></view>
                </view>
                <view class="cl-card" :class="{ pick: picked.indexOf(item.id) > -1 }" hover-class="cl-hover" @click="toDetail(item)">
                    <image v-if="item.images && item.images.length" class="cl-img" :src="item.images[0]" mode="aspectFill"></image>
                    <view class="cl-b">
                        <view class="cl-chips">
                            <text v-if="item.is_send == 0 && item.is_order == 1" class="cl-chip rv">予約中</text>
                            <text v-else class="cl-chip g">配信済</text>
                            <text v-if="item.is_send == 1 && item.indate == 1" class="cl-chip b">有効</text>
                            <text v-if="item.is_send == 1 && item.indate == 0" class="cl-chip x">期限切れ</text>
                        </view>
                        <view class="cl-t">{{ item.title }}</view>
                        <view class="cl-p">{{ period(item) }}</view>
                        <view class="cl-no" v-if="item.number">No. <text class="cl-no-v">{{ item.number }}</text></view>
                        <view class="cl-st">
                            <view class="cl-st-i"><text class="cl-st-k">配信数</text><text class="cl-st-v">{{ num(item.send_count) }}人</text></view>
                            <view class="cl-st-i"><text class="cl-st-k">利用回数</text><text class="cl-st-v">{{ num(item.use_count) }}回</text></view>
                            <view class="cl-st-i"><text class="cl-st-k">回数制限</text><text class="cl-st-v">{{ item.count > 0 ? '1人' + item.count + '回まで' : 'なし' }}</text></view>
                            <view class="cl-st-i"><text class="cl-st-k">バーコード</text><text class="cl-st-v">{{ item.is_bar_code == 1 ? 'あり' : 'なし' }}</text></view>
                        </view>
                    </view>
                </view>
            </view>
            <view class="cl-empty" v-if="loaded && !array.length">まだクーポンはありません</view>
            <view class="cl-hint" v-if="array.length">カードを押すと、コピー・削除ができます。左のチェックで、まとめて削除もできます。</view>
        </view>

        <view class="cl-foot">
            <view v-if="picked.length" class="cl-foot-2">
                <view class="cl-btn cl-btn-g" hover-class="cl-hover" @click="picked = []">選ぶのをやめる</view>
                <view class="cl-btn cl-btn-del" hover-class="cl-hover" @click="showDel = true">選んだ{{ picked.length }}件を削除</view>
            </view>
            <view v-else class="cl-btn" hover-class="cl-hover" @click="toEdit">＋ クーポン作成</view>
        </view>

        <cs-dialog :show="showDel" title="クーポンを削除" :message="'選んだ' + picked.length + '件のクーポンを削除しますか?\n削除すると元に戻せません。'"
            ok-text="削除する" ok-color="red" @ok="delPicked" @cancel="showDel = false"></cs-dialog>
        <permission :show="showpermission" @close="showpermission = false"></permission>
    </view>
</template>

<script>
    import {
        getCouponList,
        deleteCoupon,
        getAdminDetail
    } from '@/api/index.js'
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
        components: {
            permission,
            csDialog
        },
        data() {
            return {
                showpermission: false,
                showDel: false,
                loaded: false,
                coupon_count: 0,
                sum_count: 3000,
                picked: [],
                array: []
            }
        },
        computed: {
            barW() {
                const s = Number(this.sum_count) || 1
                return Math.min(100, Math.max(1, Number(this.coupon_count) / s * 100)) + '%'
            }
        },
        onShow() {
            this.getList()
        },
        methods: {
            num(n) {
                return (Number(n) || 0).toLocaleString()
            },
            md(s) {
                // 「2026-10-22」→「10/22(木)」
                if (!s) return ''
                const p = toDay(s).split('-')
                if (p.length < 3) return s
                const d = new Date(Number(p[0]), Number(p[1]) - 1, Number(p[2]))
                return Number(p[1]) + '/' + Number(p[2]) + '(' + '日月火水木金土'[d.getDay()] + ')'
            },
            period(item) {
                if (item.is_indate != 1) return '期限なし'
                return this.md(item.indate_start_time) + ' 〜 ' + this.md(item.indate_end_time) + 'まで有効'
            },
            getList() {
                getCouponList().then((res) => {
                    this.loaded = true
                    if (res.code == 200) {
                        this.array = res.data.coupons || []
                        this.coupon_count = res.data.coupon_count || 0
                        this.sum_count = res.data.sum_count || 3000
                        const ids = this.array.map((v) => v.id)
                        this.picked = this.picked.filter((id) => ids.indexOf(id) > -1)
                    }
                }).catch(() => { this.loaded = true })
            },
            toBack() {
                uni.switchTab({ url: "../index/index" })
            },
            toggle(id) {
                const i = this.picked.indexOf(id)
                if (i > -1) this.picked.splice(i, 1)
                else this.picked.push(id)
            },
            toDetail(item) {
                uni.setStorageSync('couponListItem', item) // 配信数・利用回数を詳しい画面でも出すため
                uni.navigateTo({ url: "detail/detail?id=" + item.id })
            },
            // 選んだクーポンを1件ずつ削除
            async delPicked() {
                this.showDel = false
                const ids = this.picked.slice()
                uni.showLoading({ title: '削除しています', mask: true })
                let ng = 0
                for (const id of ids) {
                    try {
                        const res = await deleteCoupon(id)
                        if (res.code != 200) ng++
                    } catch (e) { ng++ }
                }
                uni.hideLoading()
                this.picked = []
                uni.showToast({ title: ng ? (ng + '件は削除できませんでした') : '削除しました', icon: 'none' })
                this.getList()
            },
            toEdit() {
                const go = () => uni.navigateTo({ url: "../coupon/add/add?source=add" })
                const admin = uni.getStorageSync("admin")
                if (admin.admin_type == 2) {
                    uni.showLoading({ title: "読み込み中", mask: true })
                    getAdminDetail(admin.id).then((res) => {
                        uni.hideLoading()
                        if (res.data.admin.privileges.includes(7)) go()
                        else this.showpermission = true
                    }).catch(() => { uni.hideLoading() })
                } else {
                    go()
                }
            }
        }
    }
</script>

<style lang="scss">
    page { background: #F2F2F7; }
    .cl-x { width: 64rpx; height: 64rpx; border-radius: 50%; background: #E5E5EA; color: #6E6E73; font-size: 34rpx; display: flex; align-items: center; justify-content: center; }
    .cl-pg { padding: 40rpx 32rpx calc(220rpx + env(safe-area-inset-bottom)); }
    .cl-sum { background: #FFFFFF; border-radius: 32rpx; padding: 32rpx; margin-bottom: 40rpx; }
    .cl-sum-t { display: flex; justify-content: space-between; align-items: baseline; }
    .cl-sum-k { font-size: 26rpx; color: #6E6E73; }
    .cl-sum-n { font-size: 44rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; }
    .cl-sum-m { font-size: 24rpx; color: #6E6E73; }
    .cl-bar { height: 12rpx; border-radius: 6rpx; background: #E5E5EA; margin-top: 20rpx; overflow: hidden; }
    .cl-bar-i { height: 100%; background: #D93025; border-radius: 6rpx; }
    .cl-lab { font-size: 26rpx; color: #6E6E73; font-family: 'Hiragino Sans-W5', sans-serif; margin: 0 0 20rpx 8rpx; }
    .cl-row { display: flex; align-items: flex-start; margin-bottom: 32rpx; }
    .cl-ckw { padding: 24rpx 20rpx 24rpx 0; flex-shrink: 0; }
    /* 副管理者の権限の画面と同じチェック */
    .cl-ck { width: 44rpx; height: 44rpx; border-radius: 12rpx; border: 3rpx solid #AEAEB2; box-sizing: border-box; background: #FFFFFF; display: flex; align-items: center; justify-content: center; }
    .cl-ck.on { border: 0; background: #1A73E8; }
    .cl-ck-m { width: 12rpx; height: 22rpx; border-right: 5rpx solid #FFFFFF; border-bottom: 5rpx solid #FFFFFF; transform: rotate(45deg); margin-top: -6rpx; }
    .cl-card { flex: 1; min-width: 0; background: #FFFFFF; border-radius: 32rpx; overflow: hidden; box-shadow: 0 2rpx 6rpx rgba(0, 0, 0, 0.06); border: 4rpx solid transparent; }
    .cl-card.pick { border-color: #1A73E8; }
    .cl-img { width: 100%; height: 300rpx; display: block; }
    .cl-b { padding: 28rpx 28rpx 32rpx; }
    .cl-chips { display: flex; justify-content: flex-end; margin-bottom: 12rpx; }
    .cl-chip { font-size: 22rpx; border-radius: 999rpx; padding: 6rpx 20rpx; margin-left: 12rpx; font-family: 'Hiragino Sans-W5', sans-serif; }
    .cl-chip.g { background: #F2F2F7; color: #6E6E73; }
    .cl-chip.b { background: rgba(26, 115, 232, 0.10); color: #1A73E8; }
    .cl-chip.rv { background: #1A73E8; color: #FFFFFF; }
    .cl-chip.x { background: #FCE8E6; color: #D93025; }
    .cl-t { font-size: 32rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.4; overflow-wrap: anywhere; }
    .cl-p { font-size: 26rpx; color: #6E6E73; margin-top: 8rpx; }
    .cl-no { font-size: 26rpx; color: #6E6E73; margin-top: 4rpx; }
    .cl-no-v { color: #1A73E8; }
    .cl-st { display: flex; flex-wrap: wrap; margin-top: 24rpx; padding-top: 20rpx; border-top: 2rpx solid #F2F2F7; }
    .cl-st-i { width: 50%; font-size: 26rpx; line-height: 1.9; }
    .cl-st-k { color: #8A8A8E; margin-right: 12rpx; }
    .cl-st-v { color: #1D1D1F; }
    .cl-empty { text-align: center; color: #8A8A8E; font-size: 28rpx; padding: 120rpx 0; }
    .cl-hint { font-size: 24rpx; color: #8A8A8E; line-height: 1.6; margin: 8rpx 8rpx 0; }
    .cl-foot { position: fixed; left: 0; right: 0; bottom: 0; z-index: 10; padding: 24rpx 32rpx calc(24rpx + env(safe-area-inset-bottom)); background: rgba(242, 242, 247, 0.86); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-top: 1px solid rgba(0, 0, 0, 0.06); }
    .cl-foot-2 { display: flex; }
    .cl-btn { height: 104rpx; border-radius: 28rpx; background: #D93025; color: #FFFFFF; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif; display: flex; align-items: center; justify-content: center; flex: 1; }
    .cl-btn-g { background: #FFFFFF; color: #1D1D1F; border: 2rpx solid #D1D1D6; margin-right: 24rpx; flex: 0 0 280rpx; }
    .cl-hover { opacity: 0.7; }
</style>
