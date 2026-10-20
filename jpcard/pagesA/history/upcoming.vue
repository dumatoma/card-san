<template>
    <!-- [カスタムエイド 2026-10-20] 全店舗の予約中(My Card の右上から)。モック「予約履歴とお気に入り(Card-San App)」③ -->
    <view class="page">
        <u-navbar title="予約中" :border="true" :placeholder="true" :autoBack="true" :height="56"></u-navbar>
        <view class="list">
            <view class="empty" v-if="loaded && !orders.length">
                <view class="empty-txt">予約はありません</view>
            </view>
            <view class="card" v-for="o in orders" :key="o.id" @click="toShop(o)">
                <view class="shopline">
                    <image class="cardimg" v-if="o.card_image" :src="o.card_image" mode="aspectFill"></image>
                    <view class="cardimg" v-else></view>
                    <text class="shopname">{{o.shop_name}}</text>
                    <image class="arrow" src="../../static/image/arrow-right.png" mode=""></image>
                </view>
                <view class="when">{{o.date}}({{o.week}}) {{o.start}}<text class="when-end">〜{{o.end}}</text></view>
                <view class="menu">
                    <image class="ph" v-if="o.menu_image" :src="o.menu_image" mode="aspectFill" lazy-load></image>
                    <view class="menu-txt">
                        <view class="menu-name">{{o.menu_name || 'メニュー'}}</view>
                        <view class="staff">担当:{{o.staff || '指名なし'}}</view>
                    </view>
                </view>
            </view>
            <view class="note" v-if="loaded">終わった予約は、各お店の「来店履歴」で見られます</view>
        </view>
    </view>
</template>

<script>
    import {
        historyOrders
    } from "@/api/index.js"
    export default {
        data() {
            return {
                orders: [],
                loaded: false
            }
        },
        onShow() {
            historyOrders().then((res) => {
                if (res.code == 200) this.orders = res.data.orders || []
                this.loaded = true
            }).catch(() => {
                this.loaded = true
            })
        },
        methods: {
            toShop(o) {
                uni.navigateTo({ url: '../../pagesA/history/history?sid=' + o.sid })
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

    .shopline {
        display: flex;
        align-items: center;
        margin-bottom: 14rpx;

        .cardimg {
            width: 88rpx;
            height: 56rpx;
            border-radius: 10rpx;
            border: 1px solid #E5E5EA;
            background: #F5F5F7;
            flex-shrink: 0;
            margin-right: 16rpx;
        }

        .shopname {
            flex: 1;
            min-width: 0;
            font-size: 28rpx;
            font-weight: 500;
            color: #1D1D1F;
        }

        .arrow {
            width: 32rpx;
            height: 32rpx;
            opacity: 0.5;
        }
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

    .empty {
        padding: 120rpx 40rpx;
        text-align: center;

        .empty-txt {
            font-size: 30rpx;
            color: #6E6E73;
        }
    }

    .note {
        text-align: center;
        font-size: 24rpx;
        color: #86868B;
        padding: 8rpx 0 20rpx;
    }
</style>
