<template>
    <view class="page">
        <u-navbar title="クチコミ" :placeholder="true" :safeAreaInsetTop="true" :autoBack="true"
            :border="true" bgColor="#FFFFFF"></u-navbar>

        <view v-if="loading" class="loading">
            <u-loading-icon mode="circle"></u-loading-icon>
        </view>

        <block v-else>
            <!-- 未連携／クチコミなし -->
            <view v-if="!connected" class="empty">
                <view class="empty-txt">クチコミはまだありません</view>
            </view>

            <block v-else>
                <!-- 集計サマリー -->
                <view class="summary">
                    <view class="sum-left">
                        <view class="sum-score">{{ avgRating.toFixed(1) }}</view>
                        <view class="sum-stars">
                            <text v-for="i in 5" :key="i"
                                :class="i <= Math.round(avgRating) ? 'star-on' : 'star-off'">★</text>
                        </view>
                        <view class="sum-total">{{ total }}件のクチコミ</view>
                    </view>
                    <view class="sum-right">
                        <view class="bar-row" v-for="star in [5,4,3,2,1]" :key="star">
                            <text class="bar-label">{{ star }}</text>
                            <view class="bar-track">
                                <view class="bar-fill" :style="{ width: barWidth(star) }"></view>
                            </view>
                            <text class="bar-count">{{ distribution[star] || 0 }}</text>
                        </view>
                    </view>
                </view>

                <!-- 絞り込み -->
                <scroll-view scroll-x class="filter-bar" :show-scrollbar="false">
                    <view class="chip" :class="filterStar === 0 ? 'chip-on' : ''" @click="filterStar = 0">すべて</view>
                    <view class="chip" v-for="star in [5,4,3,2,1]" :key="star"
                        :class="filterStar === star ? 'chip-on' : ''" @click="filterStar = star">
                        ★{{ star }}
                    </view>
                </scroll-view>

                <!-- 並べ替え -->
                <view class="sort-bar">
                    <view class="sort-item" v-for="s in sortOptions" :key="s.value"
                        :class="sortBy === s.value ? 'sort-on' : ''" @click="sortBy = s.value">
                        {{ s.label }}
                    </view>
                </view>

                <!-- クチコミリスト -->
                <view v-if="displayReviews.length === 0" class="empty">
                    <view class="empty-txt">該当するクチコミがありません</view>
                </view>
                <view v-else class="list">
                    <view class="review" v-for="(item, index) in displayReviews" :key="index">
                        <view class="rv-head">
                            <image class="rv-avatar" :src="item.reviewer_photo || defaultAvatar" mode="aspectFill"></image>
                            <view class="rv-head-r">
                                <view class="rv-name">{{ item.reviewer_name || 'ゲスト' }}</view>
                                <view class="rv-meta">
                                    <text class="rv-stars">
                                        <text v-for="i in 5" :key="i"
                                            :class="i <= item.rating ? 'star-on' : 'star-off'">★</text>
                                    </text>
                                    <text class="rv-date">{{ item.create_time }}</text>
                                </view>
                            </view>
                        </view>
                        <view class="rv-comment" v-if="item.comment">{{ item.comment }}</view>
                        <view class="rv-reply" v-if="item.reply">
                            <view class="rv-reply-tit">店舗からの返信</view>
                            <view class="rv-reply-txt">{{ item.reply }}</view>
                        </view>
                    </view>
                </view>
            </block>
        </block>
    </view>
</template>

<script>
    import { getShopReviews } from '@/api/index.js'

    export default {
        data() {
            return {
                sid: '',
                loading: true,
                connected: false,
                avgRating: 0,
                total: 0,
                distribution: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 },
                reviews: [],
                filterStar: 0,
                sortBy: 'new',
                sortOptions: [
                    { label: '新しい順', value: 'new' },
                    { label: '古い順', value: 'old' },
                    { label: '評価が高い順', value: 'high' },
                    { label: '評価が低い順', value: 'low' }
                ],
                defaultAvatar: 'https://ssl.gstatic.com/images/branding/product/1x/avatar_circle_blue_512dp.png'
            }
        },
        computed: {
            displayReviews() {
                let list = this.reviews
                if (this.filterStar > 0) {
                    list = list.filter(r => r.rating === this.filterStar)
                }
                list = list.slice()
                switch (this.sortBy) {
                    case 'old':
                        list.sort((a, b) => (a.create_ts || 0) - (b.create_ts || 0))
                        break
                    case 'high':
                        list.sort((a, b) => (b.rating - a.rating) || ((b.create_ts || 0) - (a.create_ts || 0)))
                        break
                    case 'low':
                        list.sort((a, b) => (a.rating - b.rating) || ((b.create_ts || 0) - (a.create_ts || 0)))
                        break
                    default:
                        list.sort((a, b) => (b.create_ts || 0) - (a.create_ts || 0))
                }
                return list
            }
        },
        onLoad(options) {
            this.sid = options.sid || ''
            this.getData()
        },
        methods: {
            getData() {
                let that = this
                that.loading = true
                getShopReviews(that.sid).then((res) => {
                    if (res.code == 200 && res.data) {
                        that.connected = res.data.connected
                        that.avgRating = res.data.avg_rating || 0
                        that.total = res.data.total || 0
                        that.distribution = res.data.distribution || { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 }
                        that.reviews = res.data.reviews || []
                    }
                    that.loading = false
                }).catch(() => {
                    that.loading = false
                })
            },
            barWidth(star) {
                if (!this.total) return '0%'
                return Math.round(((this.distribution[star] || 0) / this.total) * 100) + '%'
            }
        }
    }
</script>

<style lang="scss" scoped>
    .page {
        min-height: 100vh;
        background: #F5F5F7;
    }

    .loading {
        display: flex;
        justify-content: center;
        padding-top: 200upx;
    }

    .empty {
        padding: 160upx 0;
        text-align: center;
    }
    .empty-txt {
        font-size: 28upx;
        color: #909399;
    }

    .summary {
        display: flex;
        background: #FFFFFF;
        padding: 40upx;
    }
    .sum-left {
        width: 220upx;
        text-align: center;
        border-right: 2rpx solid #EDEDED;
    }
    .sum-score {
        font-size: 72upx;
        font-weight: bold;
        color: #1D1D1F;
        line-height: 1;
    }
    .sum-stars {
        font-size: 30upx;
        margin: 12upx 0 8upx;
    }
    .sum-total {
        font-size: 24upx;
        color: #707070;
    }
    .sum-right {
        flex: 1;
        padding-left: 32upx;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .bar-row {
        display: flex;
        align-items: center;
        margin: 4upx 0;
    }
    .bar-label {
        width: 28upx;
        font-size: 24upx;
        color: #707070;
        text-align: right;
    }
    .bar-track {
        flex: 1;
        height: 12upx;
        background: #EDEDED;
        border-radius: 6upx;
        margin: 0 16upx;
        overflow: hidden;
    }
    .bar-fill {
        height: 100%;
        background: #FBBC04;
        border-radius: 6upx;
    }
    .bar-count {
        width: 48upx;
        font-size: 22upx;
        color: #909399;
        text-align: right;
    }

    .star-on { color: #FBBC04; }
    .star-off { color: #D2D2D7; }

    .filter-bar {
        white-space: nowrap;
        background: #FFFFFF;
        padding: 20upx 24upx;
        margin-top: 16upx;
    }
    .chip {
        display: inline-block;
        padding: 10upx 28upx;
        margin-right: 16upx;
        font-size: 26upx;
        color: #707070;
        background: #F0F0F0;
        border-radius: 30upx;
    }
    .chip-on {
        color: #FFFFFF;
        background: #1A73E8;
    }

    .sort-bar {
        display: flex;
        background: #FFFFFF;
        padding: 0 24upx 20upx;
    }
    .sort-item {
        font-size: 24upx;
        color: #707070;
        margin-right: 32upx;
    }
    .sort-on {
        color: #1A73E8;
        font-weight: bold;
    }

    .list {
        padding: 0 24upx;
    }
    .review {
        background: #FFFFFF;
        border-radius: 16upx;
        padding: 32upx;
        margin-top: 20upx;
    }
    .rv-head {
        display: flex;
        align-items: center;
    }
    .rv-avatar {
        width: 72upx;
        height: 72upx;
        border-radius: 50%;
        background: #EDEDED;
        margin-right: 20upx;
    }
    .rv-name {
        font-size: 28upx;
        color: #1D1D1F;
        font-weight: bold;
    }
    .rv-meta {
        display: flex;
        align-items: center;
        margin-top: 6upx;
    }
    .rv-stars {
        font-size: 24upx;
        margin-right: 16upx;
    }
    .rv-date {
        font-size: 22upx;
        color: #909399;
    }
    .rv-comment {
        font-size: 28upx;
        color: #1D1D1F;
        line-height: 1.6;
        margin-top: 20upx;
        white-space: pre-wrap;
    }
    .rv-reply {
        background: #F5F5F7;
        border-radius: 12upx;
        padding: 20upx;
        margin-top: 20upx;
    }
    .rv-reply-tit {
        font-size: 24upx;
        color: #1A73E8;
        font-weight: bold;
        margin-bottom: 8upx;
    }
    .rv-reply-txt {
        font-size: 26upx;
        color: #707070;
        line-height: 1.6;
        white-space: pre-wrap;
    }
</style>
