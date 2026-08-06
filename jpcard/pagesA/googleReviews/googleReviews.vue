<template>
    <view class="page">
        <u-navbar :title="shopName || 'クチコミ'" :placeholder="true" :safeAreaInsetTop="true" :autoBack="true"
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
                <!-- Google でのクチコミの概要 -->
                <view class="summary">
                    <view class="sum-tit">
                        <text>Google でのクチコミの概要</text>
                        <u-icon name="info-circle" color="#909399" size="16"></u-icon>
                    </view>
                    <view class="sum-body">
                        <view class="sum-bars">
                            <view class="bar-row" v-for="star in [5,4,3,2,1]" :key="star">
                                <text class="bar-label">{{ star }}</text>
                                <view class="bar-track">
                                    <view class="bar-fill" :style="{ width: barWidth(star) }"></view>
                                </view>
                            </view>
                        </view>
                        <view class="sum-score">
                            <view class="score-num">{{ avgRating.toFixed(1) }}</view>
                            <view class="score-stars">
                                <text v-for="i in 5" :key="i"
                                    :class="i <= Math.round(avgRating) ? 'star-on' : 'star-off'">★</text>
                            </view>
                            <view class="score-total">({{ total }})</view>
                        </view>
                    </view>
                </view>

                <!-- クチコミ -->
                <view class="sec-tit">クチコミ</view>

                <!-- 絞り込み（星別） -->
                <scroll-view scroll-x class="filter-bar" :show-scrollbar="false">
                    <view class="chip" :class="filterStar === 0 ? 'chip-on' : ''" @click="filterStar = 0">すべて</view>
                    <view class="chip" v-for="star in [5,4,3,2,1]" :key="star" v-if="distribution[star] > 0"
                        :class="filterStar === star ? 'chip-on' : ''" @click="filterStar = star">
                        ★{{ star }} <text class="chip-num">{{ distribution[star] }}</text>
                    </view>
                </scroll-view>

                <!-- 並べ替え -->
                <view class="sort-wrap">
                    <view class="sort-label">並べ替え</view>
                    <view class="sort-bar">
                        <view class="sort-chip" v-for="s in sortOptions" :key="s.value"
                            :class="sortBy === s.value ? 'sort-on' : ''" @click="sortBy = s.value">
                            {{ s.label }}
                        </view>
                    </view>
                </view>

                <!-- クチコミリスト -->
                <view v-if="displayReviews.length === 0" class="empty">
                    <view class="empty-txt">該当するクチコミがありません</view>
                </view>
                <view v-else class="list">
                    <view class="review" v-for="(item, index) in displayReviews" :key="index">
                        <view class="rv-head">
                            <image v-if="item.reviewer_photo" class="rv-avatar" :src="item.reviewer_photo"
                                mode="aspectFill"></image>
                            <view v-else class="rv-avatar rv-avatar-txt"
                                :style="{ background: avatarColor(item.reviewer_name) }">
                                {{ initial(item.reviewer_name) }}
                            </view>
                            <view class="rv-head-r">
                                <view class="rv-name">{{ item.reviewer_name || 'ゲスト' }}</view>
                                <view class="rv-meta">
                                    <text class="rv-stars">
                                        <text v-for="i in 5" :key="i"
                                            :class="i <= item.rating ? 'star-on' : 'star-off'">★</text>
                                    </text>
                                    <text class="rv-date">{{ relativeTime(item.create_ts) }}</text>
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
                shopName: '',
                loading: true,
                connected: false,
                avgRating: 0,
                total: 0,
                distribution: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 },
                reviews: [],
                filterStar: 0,
                sortBy: 'default',
                sortOptions: [
                    { label: '関連度順', value: 'default' },
                    { label: '新規順', value: 'new' },
                    { label: '最高', value: 'high' },
                    { label: '最低', value: 'low' }
                ],
                avatarColors: ['#EA4335', '#4285F4', '#FBBC04', '#34A853', '#A142F4', '#00ACC1', '#FB8C00']
            }
        },
        computed: {
            displayReviews() {
                let list = this.reviews.slice()
                if (this.filterStar > 0) {
                    list = list.filter(r => r.rating === this.filterStar)
                }
                switch (this.sortBy) {
                    case 'new':
                        list.sort((a, b) => (b.create_ts || 0) - (a.create_ts || 0))
                        break
                    case 'high':
                        list.sort((a, b) => (b.rating - a.rating) || ((b.create_ts || 0) - (a.create_ts || 0)))
                        break
                    case 'low':
                        list.sort((a, b) => (a.rating - b.rating) || ((b.create_ts || 0) - (a.create_ts || 0)))
                        break
                    default:
                        // 関連度順：APIの返却順（Googleの既定順）をそのまま使用
                        break
                }
                return list
            }
        },
        onLoad(options) {
            this.sid = options.sid || ''
            this.shopName = options.name ? decodeURIComponent(options.name) : ''
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
            },
            initial(name) {
                if (!name) return 'G'
                return name.trim().charAt(0).toUpperCase()
            },
            avatarColor(name) {
                let key = (name || 'G').charCodeAt(0) || 0
                return this.avatarColors[key % this.avatarColors.length]
            },
            relativeTime(ts) {
                if (!ts) return ''
                let now = Math.floor(Date.now() / 1000)
                let diff = now - ts
                if (diff < 0) diff = 0
                let day = 86400
                if (diff < day) return '今日'
                if (diff < day * 7) return Math.floor(diff / day) + '日前'
                if (diff < day * 30) return Math.floor(diff / (day * 7)) + '週間前'
                if (diff < day * 365) return Math.floor(diff / (day * 30)) + 'か月前'
                return Math.floor(diff / (day * 365)) + '年前'
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
        padding: 120upx 0;
        text-align: center;
    }
    .empty-txt {
        font-size: 28upx;
        color: #909399;
    }

    .star-on { color: #FBBC04; }
    .star-off { color: #D2D2D7; }

    /* 概要 */
    .summary {
        background: #FFFFFF;
        padding: 32upx 40upx;
    }
    .sum-tit {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 30upx;
        font-weight: bold;
        color: #1D1D1F;
        margin-bottom: 24upx;
    }
    .sum-body {
        display: flex;
        align-items: center;
    }
    .sum-bars {
        flex: 1;
        padding-right: 32upx;
    }
    .bar-row {
        display: flex;
        align-items: center;
        margin: 5upx 0;
    }
    .bar-label {
        width: 24upx;
        font-size: 22upx;
        color: #707070;
    }
    .bar-track {
        flex: 1;
        height: 12upx;
        background: #EDEDED;
        border-radius: 6upx;
        margin-left: 12upx;
        overflow: hidden;
    }
    .bar-fill {
        height: 100%;
        background: #FBBC04;
        border-radius: 6upx;
    }
    .sum-score {
        width: 200upx;
        text-align: center;
    }
    .score-num {
        font-size: 76upx;
        font-weight: bold;
        color: #1D1D1F;
        line-height: 1;
    }
    .score-stars {
        font-size: 28upx;
        margin: 8upx 0 4upx;
    }
    .score-total {
        font-size: 24upx;
        color: #909399;
    }

    .sec-tit {
        font-size: 30upx;
        font-weight: bold;
        color: #1D1D1F;
        padding: 28upx 40upx 8upx;
    }

    /* 絞り込み */
    .filter-bar {
        white-space: nowrap;
        background: #F5F5F7;
        padding: 16upx 32upx;
    }
    .chip {
        display: inline-block;
        padding: 10upx 26upx;
        margin-right: 16upx;
        font-size: 26upx;
        color: #3C4043;
        background: #FFFFFF;
        border: 2upx solid #DADCE0;
        border-radius: 30upx;
    }
    .chip-num {
        color: #909399;
    }
    .chip-on {
        color: #FFFFFF;
        background: #1A73E8;
        border-color: #1A73E8;
    }
    .chip-on .chip-num {
        color: #FFFFFF;
    }

    /* 並べ替え */
    .sort-wrap {
        background: #F5F5F7;
        padding: 4upx 32upx 16upx;
    }
    .sort-label {
        font-size: 24upx;
        color: #707070;
        margin: 8upx 0;
    }
    .sort-bar {
        display: flex;
        flex-wrap: wrap;
    }
    .sort-chip {
        padding: 10upx 26upx;
        margin-right: 16upx;
        font-size: 26upx;
        color: #3C4043;
        background: #FFFFFF;
        border: 2upx solid #DADCE0;
        border-radius: 30upx;
    }
    .sort-on {
        color: #FFFFFF;
        background: #1A73E8;
        border-color: #1A73E8;
    }

    /* リスト */
    .list {
        padding: 0 24upx 24upx;
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
    .rv-avatar-txt {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #FFFFFF;
        font-size: 34upx;
        font-weight: bold;
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
