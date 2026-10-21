<template>
    <view class="pk">
        <!-- [カスタムエイド 2026-10-20] 見た目を管理WEB(スマホ・タブレット版)の「ご利用プラン」に合わせた。
             見出しは共通の見出しに。右上の「保存」は、下の「このプランで次へ」に置きかえた(押したときの動きは同じ) -->
        <u-navbar title="ご利用プラン" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="pk-body">
            <view class="pk-card pk-cur">
                <view class="pk-cur-row">
                    <text class="pk-cur-cap">ご利用中のプラン</text>
                    <text class="pk-cur-name">{{currentName}}</text>
                </view>
                <view class="pk-blue" v-if="info.is_try == 1">
                    <view class="pk-bold">無料お試し期間中</view>
                    <!-- [カスタムエイド 2026-10-18] 無料お試し延長ロジックの変更に合わせた(管理WEB utilizePlan.vue と同じ文言) -->
                    <view>アカウント作成から30日間は全てのプランが無料お試し期間の対象です。顧客登録数が30人以下の場合は、最大30日無料期間が延長されます。</view>
                </view>
            </view>

            <view class="pk-line-btn" hover-class="pk-hover" @click="show = true">各プランの機能一覧はこちら</view>

            <view class="pk-h2">プランを選択</view>

            <view class="pk-plan" :class="{ on: current == index }" v-for="(item, index) in list" :key="index">
                <view class="pk-strip" :style="{ background: strip[index] || '#D2D2D7' }"></view>
                <view class="pk-head" @click="choosePlan(index)">
                    <view class="pk-radio" :class="{ on: current == index }"><view class="pk-radio-in"></view></view>
                    <view class="pk-head-mid">
                        <view class="pk-name-row">
                            <text class="pk-name">{{item.name}}</text>
                            <text class="pk-badge" v-if="badge[index]" :style="{ background: badgeBg[index] }">{{badge[index]}}</text>
                        </view>
                        <view class="pk-des">{{item.des}}</view>
                    </view>
                    <view class="pk-price">
                        <view class="pk-price-num">¥{{formatPriceWithCommas(item.price[1] / 6)}}</view>
                        <view class="pk-price-unit">〜/月</view>
                    </view>
                </view>
                <view class="pk-detail" @click="toggleDetail(index)">
                    <view>機能詳細<text class="pk-detail-n">{{feats(item).length}}機能</text></view>
                    <view class="pk-caret" :class="{ up: open[index] }"></view>
                </view>
                <view class="pk-feats" v-if="open[index]">
                    <view class="pk-feat-wrap" v-for="f in feats(item)" :key="f.e_name">
                        <view class="pk-feat" @click="toggleFeat(index + ':' + f.e_name)">
                            <text>{{f.name}}</text>
                            <view class="pk-chev" :class="{ down: openFeat == index + ':' + f.e_name }"></view>
                        </view>
                        <view class="pk-feat-des" v-if="openFeat == index + ':' + f.e_name">
                            <view class="pk-feat-icon"><image :src="iconOf(f)" mode="aspectFit"></image></view>
                            <text class="pk-feat-text">{{f.des}}</text>
                        </view>
                    </view>
                </view>
            </view>

            <view class="pk-note">注1)クーポン、メッセージチャットの配信数は、3,000件/月を超過した場合は、3,000件毎に1,200円/月の追加料金が発生します。</view>
            <view class="pk-note">注2)予約SMS送信数は、100件を超過した場合は、100件毎に1,200円/月の追加料金が発生します。</view>
        </view>

        <!-- 下に止めておく「このプランで次へ」 -->
        <view class="pk-bar" v-if="list.length">
            <view class="pk-bar-left">
                <view class="pk-bar-cap">選択中</view>
                <view class="pk-bar-name">{{list[current] ? list[current].name : '-'}}</view>
            </view>
            <view class="pk-bar-btn" :class="{ dis: !list[current] }" hover-class="pk-hover" @click="list[current] && ton()">このプランで次へ</view>
        </view>

        <!-- 各プランの機能一覧 -->
        <u-popup mode="center" :show="show" round="16" @close="show = false" :closeOnClickOverlay="true">
            <view class="pk-table-box">
                <view class="pk-table-title">各プランの機能一覧</view>
                <view class="pk-tr pk-th">
                    <view class="pk-td1"></view>
                    <view class="pk-td" v-for="(p, i) in list" :key="'h' + i">{{p.name}}</view>
                </view>
                <view class="pk-tr" v-for="f in allFeats" :key="f.e_name">
                    <view class="pk-td1">{{f.name}}</view>
                    <view class="pk-td" :class="{ no: !has(p, f) }" v-for="(p, i) in list" :key="f.e_name + i">{{has(p, f) ? '○' : '×'}}</view>
                </view>
                <view class="pk-close" hover-class="pk-hover" @click="show = false">閉じる</view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import {
        getShopInfo,
        getPackageList,
        delPlan
    } from "@/api/index.js"
    export default {
        data() {
            return {
                checked: true,
                show: false,
                info: {
                    is_try: -1
                },
                endTime: "",
                list: [],
                current: 0,
                info1:{},
                // [カスタムエイド 2026-10-20] 管理WEBと同じ色・印
                allFeats: [],
                open: {},
                openFeat: '',
                strip: ['#D2D2D7', '#71D3D1', '#E6BF18'],
                badge: ['', 'おすすめ', '予約機能付'],
                badgeBg: ['', '#1A73E8', '#0B7A53']
            };
        },
        created() {
            let that = this
            that.getShopInfo()
            that.getpackage()
        },
        onShow() {
            this.getShopInfo()
        },
        computed: {
            // [カスタムエイド 2026-10-20] ご利用中のプランの名前
            currentName() {
                const t = this.info.type || this.info1.type
                return t == 1 ? 'ライト' : t == 2 ? 'スタンダード' : t == 3 ? 'プレミアム' : 'なし'
            }
        },
        methods: {
            // [カスタムエイド 2026-10-20] 機能の一覧表・機能詳細(管理WEBと同じ)
            has(p, f) {
                return (p.functions || []).indexOf(f.e_name) != -1
            },
            feats(p) {
                return this.allFeats.filter((f) => this.has(p, f))
            },
            iconOf(f) {
                const n = f.name || ''
                const m = [[/メンバーズ/, 'card'], [/店舗情報/, 'shop'], [/カレンダー/, 'cal'], [/クチコミ|口コミ/, 'review'], [/Google/, 'gbp'],
                    [/メッセージ|チャット/, 'msg'], [/お知らせ/, 'notice'], [/メニュー/, 'menu'], [/クーポン/, 'coupon'], [/予約/, 'reserve']]
                for (let i = 0; i < m.length; i++) if (m[i][0].test(n)) return '/static/svg/plan/' + m[i][1] + '.svg'
                return f.icon
            },
            toggleDetail(i) {
                this.$set(this.open, i, !this.open[i])
                this.openFeat = ''
            },
            toggleFeat(k) {
                this.openFeat = this.openFeat == k ? '' : k
            },
            formatPriceWithCommas(price) {
              return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            },
            getShopInfo() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        const vip = res.data.shop_info.vip || {}
                        that.info = vip
                        that.info1 = res.data.shop_info.last_vip || {}
                        const vipType = vip.type * 1
                        that.current = vipType > 0 ? vipType - 1 : -1
                        let date = new Date((vip.end_time || 0) * 1000)
                        const Y = date.getFullYear()
                        const M = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                        const D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate()
                        that.endTime = `${Y}/${M}/${D}`
                    }
                })
            },
            deletePlan() {
                let that = this
                delPlan().then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                        setTimeout(() => {
                            uni.navigateBack()
                        }, 1000)

                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },
            ton(){
                let that = this
                let type = that.current * 1 + 1
                uni.navigateTo({
                    url: '../../payment/payment?type=' + type
                })
            },
            continuePlan() {
                let that = this
                let type = that.info.type
                uni.navigateTo({
                    url: '../../payment/payment?type=' + type
                })
            },
            openItems(index, idx) {
                let that = this
                that.list[index].items[idx].checked = !that.list[index].items[idx].checked
            },
            choosePlan(e) {
                let that = this
                that.current = e
            },
            getpackage() {
                let that = this
                getPackageList().then((res) => {
                    if (res.code == 200) {
                        let arr = Object.values(res.data.vip_function)
                        let keys = []
                        for (let i in res.data.vip_function) {
                            keys.push(i)
                        }
                        arr.forEach((val, index) => {
                            val.e_name = keys[index]
                            val.checked = false
                        })
                        res.data.vip_contract.forEach((val, index) => {
                            val.items = arr
                        })
                        that.allFeats = arr // [カスタムエイド 2026-10-20]
                        that.keys = keys
                        that.list = res.data.vip_contract
                        that.arrs = res.data.vip_function
                        that.list.forEach((value, idx) => {
                            value.items.forEach((v, i) => {})
                        })
                    }
                })
            }

        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-20] 管理WEB(スマホ・タブレット版)と同じ見た目 */
    .pk {
        min-height: 100vh;
        background: #F5F5F7;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .pk-body {
        padding: 32rpx 32rpx 48rpx;
    }

    .pk-hover {
        opacity: 0.6;
    }

    .pk-bold {
        font-weight: 700;
    }

    .pk-card {
        background: #FFFFFF;
        border-radius: 28rpx;
    }

    .pk-cur {
        padding: 28rpx 32rpx;
        margin-bottom: 28rpx;
    }

    .pk-cur-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pk-cur-cap {
        font-size: 26rpx;
        color: #6E6E73;
    }

    .pk-cur-name {
        font-size: 30rpx;
        font-weight: 700;
    }

    .pk-blue {
        margin-top: 16rpx;
        padding: 20rpx 24rpx;
        border-radius: 20rpx;
        background: #E8F0FE;
        color: #174EA6;
        font-size: 24rpx;
        line-height: 1.7;
    }

    .pk-line-btn {
        height: 92rpx;
        line-height: 90rpx;
        border: 1px solid #1A73E8;
        border-radius: 20rpx;
        background: #FFFFFF;
        color: #1A73E8;
        text-align: center;
        font-size: 28rpx;
        font-weight: 500;
        box-sizing: border-box;
        margin-bottom: 36rpx;
    }

    .pk-h2 {
        font-size: 30rpx;
        font-weight: 700;
        margin-bottom: 20rpx;
    }

    .pk-plan {
        background: #FFFFFF;
        border-radius: 28rpx;
        overflow: hidden;
        border: 2px solid transparent;
        margin-bottom: 28rpx;
    }

    .pk-plan.on {
        border-color: #1A73E8;
        box-shadow: 0 4rpx 20rpx rgba(26, 115, 232, 0.18);
    }

    .pk-strip {
        height: 20rpx;
    }

    .pk-head {
        display: flex;
        align-items: center;
        padding: 32rpx;
    }

    .pk-radio {
        width: 44rpx;
        height: 44rpx;
        flex-shrink: 0;
        border: 2px solid #C7C7CC;
        border-radius: 50%;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 24rpx;
    }

    .pk-radio-in {
        width: 20rpx;
        height: 20rpx;
        border-radius: 50%;
    }

    .pk-radio.on {
        border-color: #1A73E8;

        .pk-radio-in {
            background: #1A73E8;
        }
    }

    .pk-head-mid {
        flex: 1;
        min-width: 0;
    }

    .pk-name-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 4rpx;
    }

    .pk-name {
        font-size: 34rpx;
        font-weight: 700;
        margin-right: 16rpx;
    }

    .pk-badge {
        padding: 2rpx 16rpx;
        border-radius: 20rpx;
        font-size: 22rpx;
        font-weight: 700;
        color: #FFFFFF;
    }

    .pk-des {
        font-size: 26rpx;
        line-height: 1.6;
        color: #515154;
    }

    .pk-price {
        flex-shrink: 0;
        text-align: right;
        margin-left: 16rpx;
    }

    .pk-price-num {
        font-size: 36rpx;
        font-weight: 700;
    }

    .pk-price-unit {
        font-size: 22rpx;
        color: #6E6E73;
    }

    .pk-detail {
        min-height: 92rpx;
        padding: 0 32rpx;
        border-top: 1px solid #F0F0F2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 28rpx;
        font-weight: 500;
        color: #1A73E8;
    }

    .pk-detail-n {
        margin-left: 12rpx;
        font-size: 24rpx;
        font-weight: 400;
        color: #6E6E73;
    }

    /* 下向きの矢印(開くと上向き) */
    .pk-caret {
        width: 16rpx;
        height: 16rpx;
        border-right: 3rpx solid #1A73E8;
        border-bottom: 3rpx solid #1A73E8;
        transform: translateY(-4rpx) rotate(45deg);
    }

    .pk-caret.up {
        transform: translateY(4rpx) rotate(-135deg);
    }

    .pk-feats {
        border-top: 1px solid #F0F0F2;
    }

    .pk-feat-wrap {
        border-bottom: 1px solid #F0F0F2;
    }

    .pk-feat-wrap:last-child {
        border-bottom: 0;
    }

    .pk-feat {
        min-height: 92rpx;
        padding: 0 32rpx;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 28rpx;
    }

    /* 右向きの矢印(開くと下向き) */
    .pk-chev {
        width: 14rpx;
        height: 14rpx;
        border-right: 3rpx solid #707070;
        border-bottom: 3rpx solid #707070;
        transform: rotate(-45deg);
    }

    .pk-chev.down {
        transform: rotate(45deg);
    }

    .pk-feat-des {
        display: flex;
        align-items: flex-start;
        padding: 4rpx 32rpx 28rpx;
    }

    .pk-feat-icon {
        width: 80rpx;
        height: 80rpx;
        flex-shrink: 0;
        border-radius: 20rpx;
        background: #F5F5F7;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 24rpx;

        image {
            width: 48rpx;
            height: 48rpx;
        }
    }

    .pk-feat-text {
        font-size: 26rpx;
        line-height: 1.7;
        color: #515154;
    }

    .pk-note {
        font-size: 22rpx;
        line-height: 1.7;
        color: #6E6E73;
        margin-top: 12rpx;
    }

    .pk-bar {
        position: sticky;
        bottom: 0;
        z-index: 10;
        background: #FFFFFF;
        border-top: 1px solid #E5E5EA;
        padding: 24rpx 32rpx calc(24rpx + env(safe-area-inset-bottom));
        display: flex;
        align-items: center;
    }

    .pk-bar-left {
        flex-shrink: 0;
        margin-right: 24rpx;
    }

    .pk-bar-cap {
        font-size: 22rpx;
        color: #6E6E73;
    }

    .pk-bar-name {
        font-size: 30rpx;
        font-weight: 700;
    }

    .pk-bar-btn {
        flex: 1;
        height: 100rpx;
        line-height: 100rpx;
        border-radius: 50rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 30rpx;
        font-weight: 700;
    }

    .pk-bar-btn.dis {
        opacity: 0.45;
    }

    .pk-table-box {
        width: 90vw;
        max-height: 80vh;
        overflow-y: auto;
        padding: 40rpx 32rpx 32rpx;
        box-sizing: border-box;
        background: #FFFFFF;
        border-radius: 32rpx;
    }

    .pk-table-title {
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
        margin-bottom: 20rpx;
    }

    .pk-tr {
        display: flex;
        align-items: center;
        border-bottom: 1px solid #ECECF0;
        min-height: 68rpx;
    }

    .pk-td1 {
        width: 36%;
        font-size: 24rpx;
        font-weight: 500;
        padding: 12rpx 0;
    }

    .pk-td {
        flex: 1;
        text-align: center;
        font-size: 30rpx;
        font-weight: 700;
        color: #1A73E8;
    }

    .pk-td.no {
        color: #C7C7CC;
    }

    .pk-th .pk-td {
        font-size: 22rpx;
        color: #1D1D1F;
    }

    .pk-close {
        margin-top: 32rpx;
        height: 96rpx;
        line-height: 96rpx;
        border-radius: 20rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 30rpx;
        font-weight: 700;
    }
</style>
