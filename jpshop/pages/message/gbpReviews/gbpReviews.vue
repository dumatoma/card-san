<template>
    <view class="container">
        <!-- 統計ヘッダー -->
        <view class="statsHeader">
            <view class="statItem">
                <view class="statNum">{{ stats.avg_rating || '-' }}</view>
                <view class="statLabel">平均評価</view>
                <view class="stars" v-if="stats.avg_rating">
                    <text v-for="i in 5" :key="i" :class="i <= Math.round(stats.avg_rating) ? 'starFilled' : 'starEmpty'">★</text>
                </view>
            </view>
            <view class="statDivider"></view>
            <view class="statItem">
                <view class="statNum">{{ stats.total || 0 }}</view>
                <view class="statLabel">総件数</view>
            </view>
            <view class="statDivider"></view>
            <view class="statItem">
                <view class="statNum unreplied">{{ stats.unreplied || 0 }}</view>
                <view class="statLabel">未返信</view>
            </view>
        </view>

        <!-- GBP未連携バナー -->
        <view class="banner" v-if="!gbpConnected" @click="toGBPSetting">
            <text>Googleビジネス連携を設定してクチコミを管理する →</text>
        </view>

        <!-- フィルタータブ -->
        <view class="tabs" v-if="gbpConnected">
            <view class="tab" :class="activeTab == 'all' ? 'active' : ''" @click="switchTab('all')">すべて</view>
            <view class="tab" :class="activeTab == 'unreplied' ? 'active' : ''" @click="switchTab('unreplied')">未返信</view>
            <view class="tab" :class="activeTab == 'replied' ? 'active' : ''" @click="switchTab('replied')">返信済み</view>
        </view>

        <!-- クチコミリスト -->
        <view v-if="gbpConnected">
            <view v-if="reviews.length == 0 && !loading" class="emptyState">
                クチコミがまだありません
            </view>
            <view class="reviewCard" v-for="(item, idx) in filteredReviews" :key="item.reviewId">
                <view class="reviewHeader">
                    <image :src="item.reviewer.profilePhotoUrl || defaultAvatar" class="reviewerAvatar" mode="aspectFill"></image>
                    <view class="reviewerInfo">
                        <view class="reviewerName">{{ item.reviewer.displayName }}</view>
                        <view class="reviewStars">
                            <text v-for="i in 5" :key="i" :class="i <= starToInt(item.starRating) ? 'starFilled' : 'starEmpty'">★</text>
                        </view>
                        <view class="reviewDate">{{ formatDate(item.createTime) }}</view>
                    </view>
                </view>
                <view class="reviewComment" v-if="item.comment">{{ item.comment }}</view>

                <!-- 返信済み -->
                <view class="replySection" v-if="item.reviewReply">
                    <view class="replyLabel">オーナーの返信</view>
                    <view class="replyText" v-if="editingIdx != idx">{{ item.reviewReply.comment }}</view>
                    <view v-else class="replyInput">
                        <textarea v-model="editText" class="textarea" :maxlength="4096" placeholder="返信を編集..."></textarea>
                        <view class="replyBtns">
                            <view class="replyBtn cancel" @click="editingIdx = -1">キャンセル</view>
                            <view class="replyBtn confirm" @click="submitReply(item, idx)">保存</view>
                        </view>
                    </view>
                    <view class="replyActions" v-if="editingIdx != idx">
                        <view class="replyActionBtn" @click="startEdit(item, idx)">編集</view>
                        <view class="replyActionBtn del" @click="deleteReply(item, idx)">削除</view>
                    </view>
                </view>

                <!-- 未返信 -->
                <view class="replySection" v-else>
                    <view v-if="replyingIdx == idx" class="replyInput">
                        <textarea v-model="replyText" class="textarea" :maxlength="4096" placeholder="返信を入力（最大4096文字）"></textarea>
                        <view class="replyBtns">
                            <view class="replyBtn cancel" @click="replyingIdx = -1">キャンセル</view>
                            <view class="replyBtn confirm" @click="submitReply(item, idx)">送信</view>
                        </view>
                    </view>
                    <view v-else class="replyBtn outline" @click="startReply(idx)">返信する</view>
                </view>
            </view>

            <!-- 次ページ -->
            <view class="loadMore" v-if="nextPageToken" @click="loadMore">さらに読み込む</view>
        </view>
    </view>
</template>

<script>
    import { getGBPStatus, getGBPReviews, getGBPReviewStats, replyGBPReview, deleteGBPReply } from '@/api/index.js'
    export default {
        data() {
            return {
                gbpConnected: false,
                reviews: [],
                stats: {},
                activeTab: 'all',
                replyingIdx: -1,
                editingIdx: -1,
                replyText: '',
                editText: '',
                nextPageToken: null,
                loading: false,
                defaultAvatar: '../../../static/images/avatar.png',
            }
        },
        computed: {
            filteredReviews() {
                if (this.activeTab == 'unreplied') return this.reviews.filter(r => !r.reviewReply)
                if (this.activeTab == 'replied')   return this.reviews.filter(r =>  r.reviewReply)
                return this.reviews
            }
        },
        onLoad() {
            this.checkConnection()
        },
        onPullDownRefresh() {
            this.loadReviews()
            getGBPReviewStats().then(res => {
                if (res.code == 200) this.stats = res.data
            }).catch(() => {})
        },
        methods: {
            checkConnection() {
                getGBPStatus().then(res => {
                    if (res.code == 200 && res.data.connected) {
                        this.gbpConnected = true
                        this.loadReviews()
                        getGBPReviewStats().then(r => {
                            if (r.code == 200) this.stats = r.data
                        }).catch(() => {})
                    }
                }).catch(() => {})
            },
            loadReviews() {
                this.loading = true
                uni.showLoading({ title: '読み込み中' })
                getGBPReviews({}).then(res => {
                    uni.hideLoading()
                    uni.stopPullDownRefresh()
                    this.loading = false
                    if (res.code == 200) {
                        this.reviews       = res.data.reviews || []
                        this.nextPageToken = res.data.nextPageToken || null
                    }
                }).catch(() => { uni.hideLoading(); uni.stopPullDownRefresh(); this.loading = false })
            },
            loadMore() {
                getGBPReviews({ page_token: this.nextPageToken }).then(res => {
                    if (res.code == 200) {
                        this.reviews       = [...this.reviews, ...(res.data.reviews || [])]
                        this.nextPageToken = res.data.nextPageToken || null
                    }
                }).catch(() => {})
            },
            switchTab(tab) {
                this.activeTab = tab
                this.replyingIdx = -1
                this.editingIdx  = -1
            },
            startReply(idx) {
                this.replyText   = ''
                this.replyingIdx = idx
                this.editingIdx  = -1
            },
            startEdit(item, idx) {
                this.editText   = item.reviewReply.comment
                this.editingIdx = idx
                this.replyingIdx = -1
            },
            submitReply(item, idx) {
                let text = this.editingIdx == idx ? this.editText : this.replyText
                if (!text.trim()) {
                    uni.showToast({ title: '返信内容を入力してください', icon: 'none' })
                    return
                }
                let reviewId = item.name.split('/').pop()
                replyGBPReview(reviewId, { comment: text }).then(res => {
                    if (res.code == 200) {
                        item.reviewReply = { comment: text }
                        this.replyingIdx = -1
                        this.editingIdx  = -1
                        this.stats.unreplied = Math.max(0, (this.stats.unreplied || 1) - 1)
                        uni.showToast({ title: '返信しました', icon: 'success' })
                    } else {
                        uni.showToast({ title: res.message, icon: 'none' })
                    }
                }).catch(() => {})
            },
            deleteReply(item, idx) {
                uni.showModal({
                    title: '返信を削除しますか？',
                    confirmText: '削除',
                    confirmColor: '#D93025',
                    success: r => {
                        if (!r.confirm) return
                        let reviewId = item.name.split('/').pop()
                        deleteGBPReply(reviewId).then(res => {
                            if (res.code == 200) {
                                item.reviewReply = null
                                this.stats.unreplied = (this.stats.unreplied || 0) + 1
                                uni.showToast({ title: '削除しました', icon: 'success' })
                            }
                        }).catch(() => {})
                    }
                })
            },
            toGBPSetting() {
                uni.navigateTo({ url: '/pages/settings/gbpSetting/gbpSetting' })
            },
            starToInt(star) {
                const m = { ZERO: 0, ONE: 1, TWO: 2, THREE: 3, FOUR: 4, FIVE: 5 }
                return m[star] || 0
            },
            formatDate(dateStr) {
                if (!dateStr) return ''
                let d = new Date(dateStr)
                return d.getFullYear() + '/' + (d.getMonth()+1) + '/' + d.getDate()
            }
        }
    }
</script>

<style lang="scss">
.container { background: #f9f9f9; min-height: 100vh; padding-bottom: 40rpx; }
.statsHeader {
    display: flex; background: #fff; padding: 40rpx;
    margin-bottom: 20rpx; justify-content: space-around; align-items: center;
}
.statItem { text-align: center; flex: 1; }
.statNum { font-size: 48rpx; font-weight: bold; color: #1D1D1F; }
.statNum.unreplied { color: #D93025; }
.statLabel { font-size: 24rpx; color: #86868B; margin-top: 8rpx; }
.stars { margin-top: 8rpx; }
.statDivider { width: 2rpx; height: 80rpx; background: #f0f0f0; }
.starFilled { color: #FBBC05; font-size: 28rpx; }
.starEmpty  { color: #ccc; font-size: 28rpx; }
.banner {
    background: #E8F0FE; padding: 28rpx 40rpx; margin-bottom: 20rpx;
    font-size: 28rpx; color: #1A73E8;
}
.tabs {
    display: flex; background: #fff; border-bottom: 2rpx solid #f0f0f0; margin-bottom: 20rpx;
}
.tab {
    flex: 1; text-align: center; padding: 28rpx 0; font-size: 28rpx; color: #86868B;
    &.active { color: #1A73E8; border-bottom: 4rpx solid #1A73E8; font-weight: bold; }
}
.reviewCard {
    background: #fff; border-radius: 16rpx; margin: 0 24rpx 20rpx; padding: 30rpx;
    box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.05);
}
.reviewHeader { display: flex; align-items: flex-start; margin-bottom: 20rpx; }
.reviewerAvatar { width: 80rpx; height: 80rpx; border-radius: 50%; margin-right: 20rpx; flex-shrink: 0; }
.reviewerName { font-size: 30rpx; font-weight: bold; color: #1D1D1F; }
.reviewDate { font-size: 22rpx; color: #86868B; margin-top: 6rpx; }
.reviewComment { font-size: 28rpx; color: #333; line-height: 1.7; margin-bottom: 20rpx; }
.replySection { border-top: 2rpx solid #f0f0f0; padding-top: 20rpx; }
.replyLabel { font-size: 24rpx; color: #86868B; margin-bottom: 10rpx; }
.replyText { font-size: 28rpx; color: #444; line-height: 1.6; }
.replyActions { display: flex; margin-top: 16rpx; }
.replyActionBtn {
    font-size: 24rpx; color: #1A73E8; margin-right: 30rpx;
    &.del { color: #D93025; }
}
.replyInput { margin-top: 16rpx; }
.textarea {
    width: 100%; min-height: 160rpx; border: 2rpx solid #ddd;
    border-radius: 12rpx; padding: 16rpx; font-size: 28rpx; box-sizing: border-box;
}
.replyBtns { display: flex; justify-content: flex-end; margin-top: 16rpx; }
.replyBtn {
    padding: 16rpx 40rpx; border-radius: 30rpx; font-size: 28rpx; margin-left: 20rpx;
    &.cancel  { background: #f0f0f0; color: #333; }
    &.confirm { background: #1A73E8; color: #fff; }
    &.outline { border: 2rpx solid #1A73E8; color: #1A73E8; display: inline-block; }
}
.emptyState { text-align: center; color: #86868B; padding: 100rpx 0; font-size: 28rpx; }
.loadMore {
    text-align: center; color: #1A73E8; font-size: 28rpx;
    padding: 40rpx; border-top: 2rpx solid #f0f0f0; background: #fff;
}
</style>
