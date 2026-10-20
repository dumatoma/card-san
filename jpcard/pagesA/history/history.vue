<template>
    <!-- [カスタムエイド 2026-10-20] お店ごとの予約履歴(予約中/来店履歴)。モック「予約履歴とお気に入り(Card-San App)」② -->
    <view class="page">
        <u-navbar title="予約履歴" :border="true" :placeholder="true" :autoBack="true" :height="56"></u-navbar>
        <view class="tabs">
            <view class="tab" :class="{ on: tab == 'order' }" @click="tab = 'order'">予約中</view>
            <view class="tab" :class="{ on: tab == 'come' }" @click="tab = 'come'">来店履歴</view>
        </view>

        <!-- 予約中 -->
        <view class="list" v-if="tab == 'order'">
            <view class="empty" v-if="ordersLoaded && !orders.length">
                <view class="empty-txt">予約はありません</view>
                <view class="btn" v-if="fromShop" @click="toMenu">メニューを見る</view>
            </view>
            <view class="card" v-for="(o, i) in orders" :key="o.id">
                <view class="badge" v-if="i == 0">次回</view>
                <view class="when">{{o.date}}({{o.week}}) {{o.start}}<text class="when-end">〜{{o.end}}</text></view>
                <view class="menu">
                    <image class="ph" v-if="o.menu_image" :src="o.menu_image" mode="aspectFill" lazy-load></image>
                    <view class="menu-txt">
                        <view class="menu-name">{{o.menu_name || 'メニュー'}}</view>
                        <view class="staff">担当:{{o.staff || '指名なし'}}</view>
                    </view>
                </view>
            </view>
        </view>

        <!-- 来店履歴 -->
        <view class="list" v-if="tab == 'come'">
            <view class="empty" v-if="comesLoaded && !comes.length">
                <view class="empty-txt">来店履歴はありません</view>
            </view>
            <view class="card" v-for="c in comes" :key="c.id">
                <view class="when">{{c.date}}({{c.week}})</view>
                <view class="menu" v-for="(m, mi) in c.menus" :key="mi">
                    <image class="ph" v-if="m.image" :src="m.image" mode="aspectFill" lazy-load></image>
                    <view class="menu-name">{{m.name}}</view>
                </view>
                <view class="kv">
                    <text class="k">担当</text>
                    <text class="v">{{c.staff.length ? c.staff.join('・') : 'ー'}}</text>
                    <text class="k">クーポン</text>
                    <view class="v">
                        <text class="chip" v-for="(cp, ci) in c.coupons" :key="ci">{{cp}}</text>
                        <text class="none" v-if="!c.coupons.length">なし</text>
                    </view>
                </view>
                <view class="btn" v-if="firstOrderable(c)" @click="reorder(c)">同じメニューで予約する</view>
            </view>
            <view class="note" v-if="comes.length">365日以内の来店を表示しています</view>
        </view>
    </view>
</template>

<script>
    import {
        historyOrders,
        historyComes,
        menuList
    } from "@/api/index.js"
    export default {
        data() {
            return {
                sid: 0,
                fromShop: false,
                tab: 'order',
                orders: [],
                comes: [],
                ordersLoaded: false,
                comesLoaded: false
            }
        },
        onLoad(options) {
            this.sid = options.sid || 0
            this.fromShop = options.from == 'shop'
            if (options.tab == 'come') this.tab = 'come'
        },
        onShow() {
            this.load()
        },
        methods: {
            load() {
                historyOrders(this.sid).then((res) => {
                    if (res.code == 200) this.orders = res.data.orders || []
                    this.ordersLoaded = true
                }).catch(() => {
                    this.ordersLoaded = true
                })
                historyComes(this.sid).then((res) => {
                    if (res.code == 200) this.comes = res.data.comes || []
                    this.comesLoaded = true
                }).catch(() => {
                    this.comesLoaded = true
                })
            },
            firstOrderable(c) {
                return (c.menus || []).find((m) => m.can_order == 1 && m.id)
            },
            // 同じメニューで予約:メニュー画面を通らず、メニューを選んだ状態で予約日時の画面へ
            reorder(c) {
                let m = this.firstOrderable(c)
                if (!m) return
                uni.showLoading({ title: '読み込み中' })
                menuList({ page: 1, collect: '', sid: c.sid }).then((res) => {
                    uni.hideLoading()
                    let item = res.code == 200 ? (res.data.menus || []).find((v) => v.id == m.id) : null
                    if (!item || item.is_order != 1) {
                        uni.showToast({ title: 'このメニューは現在予約できません', icon: 'none' })
                        return
                    }
                    uni.setStorageSync('orderItem', item)
                    uni.navigateTo({ url: '../../pagesA/yuYue/yuYue?id=' + c.sid })
                }).catch(() => {
                    uni.hideLoading()
                })
            },
            // お店の画面に戻って、メニューのタブを開く
            toMenu() {
                uni.$emit('csShopTab', 'メニュー')
                uni.navigateBack()
            }
        }
    }
</script>

<style lang="scss" scoped>
    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    .page {
        min-height: 100vh;
        background: #F5F5F7;
    }

    .tabs {
        display: flex;
        background: #FFFFFF;
        border-bottom: 1px solid #E5E5EA;

        .tab {
            flex: 1;
            text-align: center;
            padding: 22rpx 0;
            font-size: 30rpx;
            color: #6E6E73;
        }

        .tab.on {
            color: #1A73E8;
            font-weight: 700;
            box-shadow: inset 0 -6rpx 0 #1A73E8;
        }
    }

    .list {
        padding: 24rpx 24rpx calc(60rpx + env(safe-area-inset-bottom));
    }

    .card {
        background: #FFFFFF;
        border: 1px solid #E5E5EA;
        border-radius: 24rpx;
        padding: 24rpx;
        margin-bottom: 24rpx;
    }

    .badge {
        display: inline-block;
        font-size: 24rpx;
        color: #FFFFFF;
        background: #1A73E8;
        border-radius: 999rpx;
        padding: 2rpx 18rpx;
        margin-bottom: 12rpx;
    }

    .when {
        font-size: 32rpx;
        font-weight: 700;
        color: #1D1D1F;
        font-variant-numeric: tabular-nums;
        margin-bottom: 16rpx;
    }

    .when-end {
        font-size: 26rpx;
        font-weight: 400;
        color: #6E6E73;
        margin-left: 8rpx;
    }

    .menu {
        display: flex;
        align-items: center;
        margin-bottom: 16rpx;

        .ph {
            width: 112rpx;
            height: 112rpx;
            border-radius: 16rpx;
            flex-shrink: 0;
            margin-right: 20rpx;
            background: #E5E5EA;
        }

        .menu-txt {
            min-width: 0;
        }

        .menu-name {
            font-size: 30rpx;
            font-weight: 500;
            color: #1D1D1F;
            line-height: 1.4;
        }

        .staff {
            font-size: 26rpx;
            color: #6E6E73;
            margin-top: 4rpx;
        }
    }

    .kv {
        display: grid;
        grid-template-columns: 120rpx 1fr;
        row-gap: 8rpx;
        font-size: 26rpx;
        margin-bottom: 8rpx;

        .k {
            color: #6E6E73;
        }

        .v {
            color: #1D1D1F;
        }

        .chip {
            display: inline-block;
            font-size: 24rpx;
            background: #FFF1E6;
            color: #B35C1E;
            border-radius: 10rpx;
            padding: 2rpx 14rpx;
            margin-right: 8rpx;
        }

        .none {
            color: #6E6E73;
        }
    }

    .btn {
        margin-top: 16rpx;
        border: 1px solid #1A73E8;
        color: #1A73E8;
        background: #EEF4FF;
        border-radius: 20rpx;
        text-align: center;
        padding: 18rpx 0;
        font-size: 28rpx;
    }

    .empty {
        padding: 120rpx 40rpx;
        text-align: center;

        .empty-txt {
            font-size: 30rpx;
            color: #6E6E73;
            margin-bottom: 24rpx;
        }

        .btn {
            display: inline-block;
            padding: 18rpx 60rpx;
        }
    }

    .note {
        text-align: center;
        font-size: 24rpx;
        color: #86868B;
        padding: 8rpx 0 20rpx;
    }
</style>
