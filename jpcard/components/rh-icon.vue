<template>
    <!-- [カスタムエイド 2026-10-20] お店の各タブのヘッダーに置く予約履歴のアイコン(会員証のタブと同じ見た目)。
         このお店に予約中があればオレンジの点。押すと予約履歴を開く -->
    <view class="cs-rh" @tap.stop="toHistory">
        <view :style="`backgroundImage: url(${icon})`" class="cs-rh-icon"></view>
        <view class="cs-rh-dot" v-if="hasOrder" :style="`box-shadow: 0 0 0 4rpx ${bgColor || '#fff'}`"></view>
    </view>
</template>

<script>
    import rhShop from '../static/svgjs/reserveHistoryShop.js'
    import {
        historyOrders
    } from "@/api/index.js"
    export default {
        props: ["sid", "color", "bgColor"],
        data() {
            return {
                hasOrder: false
            }
        },
        computed: {
            // ヘッダーの文字色に合わせる
            icon() {
                let c = this.color || '#1d1d1f'
                if (!/^#[0-9a-fA-F]{6}$/.test(c)) c = '#1d1d1f'
                return ('"' + rhShop + '"').replace(/%23[a-zA-Z0-9]{6}/g, c.replace('#', '%23'))
            }
        },
        created() {
            this.check()
        },
        methods: {
            check() {
                if (!uni.getStorageSync('token') || !this.sid) return
                historyOrders(this.sid).then((res) => {
                    this.hasOrder = res.code == 200 && res.data.total > 0
                }).catch(() => {})
            },
            toHistory() {
                uni.navigateTo({
                    url: '/pagesA/history/history?sid=' + this.sid + '&from=shop'
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    /* 会員証のタブ(shopDetails)と同じ大きさ・間隔 */
    .cs-rh {
        position: relative;
        width: 50upx;
        height: 50upx;
        margin-right: 32rpx;
        padding: 8rpx;
        margin-top: -8rpx;
        margin-bottom: -8rpx;
        margin-left: -8rpx;
    }

    .cs-rh-icon {
        width: 50upx;
        height: 50upx;
        background-size: 100% 100%;
    }

    .cs-rh-dot {
        position: absolute;
        top: -4rpx;
        right: -6rpx;
        width: 30rpx;
        height: 30rpx;
        border-radius: 50%;
        background: #FF9500;
    }
</style>
