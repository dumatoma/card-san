<template>
    <view class="container">
        <!-- タブ -->
        <view class="tabs">
            <view class="tab" :class="activeTab == 0 ? 'active' : ''" @click="switchTab(0)">連携設定</view>
            <view class="tab" :class="activeTab == 1 ? 'active' : ''" @click="switchTab(1)">GBP投稿</view>
            <view class="tab" :class="activeTab == 2 ? 'active' : ''" @click="switchTab(2)">連動履歴</view>
        </view>

        <!-- 連携設定 -->
        <view v-if="activeTab == 0">
            <view class="section">
                <view class="sectionTitle">連携サービス</view>

                <view class="serviceRow" @click="toGBPSetting">
                    <view class="serviceLeft">
                        <image src="../../../static/svg/google_g.svg" class="serviceIcon" mode="aspectFit"></image>
                        <view>
                            <view class="serviceName">Google Business Profile</view>
                            <view class="serviceStatus" :class="gbpConnected ? 'connected' : 'unconnected'">
                                {{ gbpConnected ? '連携済み' : '未連携' }}
                            </view>
                        </view>
                    </view>
                    <text class="arrow">›</text>
                </view>
            </view>

            <view class="section" v-if="gbpConnected">
                <view class="sectionTitle">自動連動設定</view>
                <view class="switchRow">
                    <view>
                        <view class="switchLabel">Instagram → GBP 自動転載</view>
                        <view class="switchSub">Instagram投稿を自動的にGBPに転載します</view>
                    </view>
                    <u-switch v-model="insSyncOn" activeColor="#1A73E8" @change="saveSyncSettings"></u-switch>
                </view>
            </view>

            <view class="banner" v-if="!gbpConnected" @click="toGBPSetting">
                <text>Google Business Profileを連携して投稿・クチコミを管理する →</text>
            </view>
        </view>

        <!-- GBP投稿管理 -->
        <view v-if="activeTab == 1">
            <view v-if="!gbpConnected" class="emptyState">
                <view class="emptyText">GBP未連携です</view>
                <view class="linkBtn" @click="toGBPSetting">連携設定へ →</view>
            </view>
            <view v-else>
                <!-- 新規投稿ボタン -->
                <view class="newPostBtn" @click="showPostForm = true">
                    <u-icon name="plus-circle-fill" color="#fff" size="22"></u-icon>
                    <text style="margin-left: 12rpx;">新規投稿</text>
                </view>

                <!-- 投稿リスト -->
                <view v-if="posts.length == 0 && !postsLoading" class="emptyState">
                    <view class="emptyText">GBP投稿がまだありません</view>
                </view>
                <view class="postCard" v-for="(post, idx) in posts" :key="post.name">
                    <view class="postMeta">
                        <view class="postType">{{ postTypeLabel(post.topicType) }}</view>
                        <view class="postDate">{{ formatDate(post.createTime) }}</view>
                    </view>
                    <image v-if="post.media && post.media[0]" :src="post.media[0].googleUrl || post.media[0].sourceUrl" class="postImg" mode="aspectFill"></image>
                    <view class="postSummary">{{ post.summary }}</view>
                    <view class="postDel" @click="doDeletePost(post, idx)">削除</view>
                </view>

                <view class="loadMore" v-if="postsNextToken" @click="loadMorePosts">さらに読み込む</view>
            </view>
        </view>

        <!-- 連動履歴（Instagram投稿一覧） -->
        <view v-if="activeTab == 2">
            <view v-if="!gbpConnected" class="emptyState">
                <view class="emptyText">GBP未連携です</view>
                <view class="linkBtn" @click="toGBPSetting">連携設定へ →</view>
            </view>
            <view v-else>
                <view v-if="insPosts.length == 0 && !insLoading" class="emptyState">
                    <view class="emptyText">Instagram投稿がありません</view>
                </view>
                <view class="insCard" v-for="(item, idx) in insPosts" :key="item.id">
                    <image v-if="item.media_url" :src="item.media_url" class="insImg" mode="aspectFill"></image>
                    <view class="insInfo">
                        <view class="insCaption">{{ item.caption }}</view>
                        <view class="insDate">{{ formatDate(item.timestamp) }}</view>
                        <view class="gbpBtn" :class="item.synced ? 'synced' : ''" @click="syncToGBP(item, idx)">
                            {{ item.synced ? 'GBP転載済み' : 'GBPに転載' }}
                        </view>
                    </view>
                </view>
            </view>
        </view>

        <!-- 新規投稿フォーム -->
        <u-popup :show="showPostForm" mode="bottom" round="24" @close="showPostForm = false" :safeAreaInsetBottom="true">
            <view class="formSheet">
                <view class="formTitle">
                    <view class="formTitleText">GBP投稿</view>
                    <view class="formClose" @click="showPostForm = false">✕</view>
                </view>
                <view class="formItem">
                    <view class="formLabel">投稿種別</view>
                    <view class="typeSelect">
                        <view class="typeBtn" :class="newPost.post_type == 'STANDARD' ? 'sel' : ''" @click="newPost.post_type = 'STANDARD'">お知らせ</view>
                        <view class="typeBtn" :class="newPost.post_type == 'EVENT' ? 'sel' : ''" @click="newPost.post_type = 'EVENT'">イベント</view>
                        <view class="typeBtn" :class="newPost.post_type == 'OFFER' ? 'sel' : ''" @click="newPost.post_type = 'OFFER'">クーポン</view>
                    </view>
                </view>
                <view class="formItem" v-if="newPost.post_type == 'EVENT' || newPost.post_type == 'OFFER'">
                    <view class="formLabel">{{ newPost.post_type == 'OFFER' ? 'オファータイトル' : 'イベントタイトル' }}</view>
                    <u-input v-model="newPost.event_title" placeholder="タイトルを入力"></u-input>
                </view>
                <view class="formItem" v-if="newPost.post_type == 'EVENT' || newPost.post_type == 'OFFER'">
                    <view class="formLabel">開始日 〜 終了日</view>
                    <view class="dateRow">
                        <u-input v-model="newPost.start_date" placeholder="YYYY-MM-DD" style="flex:1"></u-input>
                        <text style="padding: 0 16rpx;line-height: 80rpx;">〜</text>
                        <u-input v-model="newPost.end_date" placeholder="YYYY-MM-DD" style="flex:1"></u-input>
                    </view>
                </view>
                <view class="formItem">
                    <view class="formLabel">本文</view>
                    <textarea v-model="newPost.summary" class="textarea" placeholder="投稿内容を入力（最大1500文字）" :maxlength="1500"></textarea>
                </view>
                <view class="formItem">
                    <view class="formLabel">画像（任意）</view>
                    <view class="imgPickRow">
                        <image v-if="newPost.media_url" :src="newPost.media_url" class="previewImg" mode="aspectFill"></image>
                        <view class="imgPickBtn" @click="chooseImage">
                            <u-icon name="plus" size="28" color="#1A73E8"></u-icon>
                            <text>画像を選択</text>
                        </view>
                    </view>
                </view>
                <u-button class="submitBtn" type="primary" :loading="submitting" @click="submitPost">投稿する</u-button>
                <view style="height: 40rpx;"></view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import {
        getGBPStatus, getIntegrationSettings, updateIntegrationSettings,
        getGBPPosts, createGBPPost, deleteGBPPost, syncInsToGBP
    } from '@/api/index.js'

    export default {
        data() {
            return {
                activeTab: 0,
                gbpConnected: false,
                insSyncOn: false,
                posts: [],
                postsNextToken: null,
                postsLoading: false,
                insPosts: [],
                insLoading: false,
                showPostForm: false,
                submitting: false,
                newPost: {
                    post_type: 'STANDARD',
                    summary: '',
                    media_url: '',
                    event_title: '',
                    start_date: '',
                    end_date: '',
                },
            }
        },
        onLoad() {
            this.loadStatus()
        },
        onPullDownRefresh() {
            this.refreshCurrent()
        },
        methods: {
            switchTab(idx) {
                this.activeTab = idx
                if (idx == 1 && this.posts.length == 0 && this.gbpConnected) this.loadPosts()
                if (idx == 2 && this.insPosts.length == 0 && this.gbpConnected) this.loadInsPosts()
            },
            loadStatus() {
                getGBPStatus().then(res => {
                    if (res.code == 200) {
                        this.gbpConnected = res.data.connected
                    }
                }).catch(() => {})
                getIntegrationSettings().then(res => {
                    if (res.code == 200) {
                        this.insSyncOn = res.data.ins_sync_on == 1
                    }
                }).catch(() => {})
            },
            saveSyncSettings() {
                updateIntegrationSettings({ ins_sync_on: this.insSyncOn ? 1 : 0 }).catch(() => {})
            },
            toGBPSetting() {
                uni.navigateTo({ url: '/pages/settings/gbpSetting/gbpSetting' })
            },
            loadPosts() {
                this.postsLoading = true
                uni.showLoading({ title: '読み込み中' })
                getGBPPosts({}).then(res => {
                    uni.hideLoading()
                    uni.stopPullDownRefresh()
                    this.postsLoading = false
                    if (res.code == 200) {
                        this.posts         = res.data.localPosts || []
                        this.postsNextToken = res.data.nextPageToken || null
                    }
                }).catch(() => { uni.hideLoading(); uni.stopPullDownRefresh(); this.postsLoading = false })
            },
            loadMorePosts() {
                getGBPPosts({ page_token: this.postsNextToken }).then(res => {
                    if (res.code == 200) {
                        this.posts          = [...this.posts, ...(res.data.localPosts || [])]
                        this.postsNextToken  = res.data.nextPageToken || null
                    }
                }).catch(() => {})
            },
            doDeletePost(post, idx) {
                uni.showModal({
                    title: 'この投稿を削除しますか？',
                    confirmText: '削除',
                    confirmColor: '#D93025',
                    success: r => {
                        if (!r.confirm) return
                        let id = post.name.split('/').pop()
                        deleteGBPPost(id).then(res => {
                            if (res.code == 200) {
                                this.posts.splice(idx, 1)
                                uni.showToast({ title: '削除しました', icon: 'success' })
                            }
                        }).catch(() => {})
                    }
                })
            },
            loadInsPosts() {
                // Instagram投稿はバックエンドのinstagram_postsテーブルから取得
                this.insLoading = true
                uni.request({
                    url: this.$baseUrl + '/api/shop/instagram/posts',
                    method: 'GET',
                    header: { Authorization: 'Bearer ' + uni.getStorageSync('token') },
                    success: res => {
                        this.insLoading = false
                        uni.stopPullDownRefresh()
                        if (res.data && res.data.code == 200) {
                            this.insPosts = (res.data.data || []).map(function(p) {
                                p.synced = false
                                return p
                            })
                        }
                    },
                    fail: () => { this.insLoading = false; uni.stopPullDownRefresh() }
                })
            },
            syncToGBP(item, idx) {
                if (item.synced) return
                uni.showModal({
                    title: 'GBPに転載しますか？',
                    content: 'この投稿をGoogle Business Profileに転載します。',
                    confirmText: '転載',
                    success: r => {
                        if (!r.confirm) return
                        syncInsToGBP({ media_url: item.media_url, caption: item.caption }).then(res => {
                            if (res.code == 200) {
                                this.insPosts[idx].synced = true
                                uni.showToast({ title: 'GBPに転載しました', icon: 'success' })
                            } else {
                                uni.showToast({ title: res.message, icon: 'none' })
                            }
                        }).catch(() => {})
                    }
                })
            },
            chooseImage() {
                let that = this
                uni.chooseImage({
                    count: 1,
                    success: res => {
                        uni.uploadFile({
                            url: that.$baseUrl + '/tool/upload/file',
                            filePath: res.tempFilePaths[0],
                            name: 'file',
                            header: { Authorization: 'Bearer ' + uni.getStorageSync('token') },
                            success: result => {
                                if (result.errMsg == 'uploadFile:ok') {
                                    let re = JSON.parse(result.data)
                                    if (re.code == 200) that.newPost.media_url = re.data.path
                                }
                            }
                        })
                    }
                })
            },
            submitPost() {
                if (!this.newPost.summary.trim()) {
                    uni.showToast({ title: '本文を入力してください', icon: 'none' })
                    return
                }
                this.submitting = true
                createGBPPost(this.newPost).then(res => {
                    this.submitting = false
                    if (res.code == 200) {
                        uni.showToast({ title: '投稿しました', icon: 'success' })
                        this.showPostForm = false
                        this.resetForm()
                        this.loadPosts()
                    } else {
                        uni.showToast({ title: res.message, icon: 'none' })
                    }
                }).catch(() => { this.submitting = false })
            },
            resetForm() {
                this.newPost = {
                    post_type: 'STANDARD',
                    summary: '',
                    media_url: '',
                    event_title: '',
                    start_date: '',
                    end_date: '',
                }
            },
            refreshCurrent() {
                if (this.activeTab == 1) this.loadPosts()
                else if (this.activeTab == 2) this.loadInsPosts()
                else { this.loadStatus(); uni.stopPullDownRefresh() }
            },
            postTypeLabel(type) {
                const m = { STANDARD: 'お知らせ', EVENT: 'イベント', OFFER: 'クーポン' }
                return m[type] || type
            },
            formatDate(dateStr) {
                if (!dateStr) return ''
                let d = new Date(dateStr)
                return d.getFullYear() + '/' + (d.getMonth() + 1) + '/' + d.getDate()
            },
        }
    }
</script>

<style lang="scss">
.container { background: #f9f9f9; min-height: 100vh; padding-bottom: 40rpx; }
.tabs {
    display: flex; background: #fff; border-bottom: 2rpx solid #f0f0f0; margin-bottom: 20rpx;
}
.tab {
    flex: 1; text-align: center; padding: 28rpx 0; font-size: 28rpx; color: #86868B;
    &.active { color: #1A73E8; border-bottom: 4rpx solid #1A73E8; font-weight: bold; }
}
.section {
    background: #fff; border-radius: 20rpx; margin: 0 24rpx 24rpx; padding: 0 30rpx;
    box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.05);
}
.sectionTitle {
    font-size: 26rpx; color: #86868B; padding: 24rpx 0 14rpx;
    border-bottom: 2rpx solid #f0f0f0;
}
.serviceRow {
    display: flex; align-items: center; justify-content: space-between;
    padding: 30rpx 0; border-bottom: 2rpx solid #f0f0f0;
}
.serviceLeft { display: flex; align-items: center; }
.serviceIcon { width: 60rpx; height: 60rpx; margin-right: 20rpx; }
.serviceName { font-size: 30rpx; color: #1D1D1F; margin-bottom: 6rpx; }
.serviceStatus { font-size: 24rpx; }
.serviceStatus.connected { color: #34C759; }
.serviceStatus.unconnected { color: #86868B; }
.arrow { color: #ccc; font-size: 36rpx; }
.switchRow {
    display: flex; align-items: center; justify-content: space-between;
    padding: 30rpx 0;
}
.switchLabel { font-size: 30rpx; color: #1D1D1F; margin-bottom: 6rpx; }
.switchSub { font-size: 24rpx; color: #86868B; }
.banner {
    background: #E8F0FE; padding: 28rpx 40rpx; margin: 0 24rpx 24rpx;
    border-radius: 16rpx; font-size: 28rpx; color: #1A73E8;
}
.newPostBtn {
    display: flex; align-items: center; justify-content: center;
    background: #1A73E8; color: #fff; font-size: 30rpx;
    padding: 28rpx; margin: 0 24rpx 20rpx; border-radius: 16rpx;
}
.postCard {
    background: #fff; border-radius: 16rpx; margin: 0 24rpx 20rpx; padding: 28rpx;
    box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.05);
}
.postMeta { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16rpx; }
.postType {
    font-size: 22rpx; color: #1A73E8; background: #E8F0FE;
    padding: 4rpx 16rpx; border-radius: 20rpx;
}
.postDate { font-size: 22rpx; color: #86868B; }
.postImg { width: 100%; height: 300rpx; border-radius: 12rpx; margin-bottom: 16rpx; }
.postSummary { font-size: 28rpx; color: #333; line-height: 1.6; margin-bottom: 16rpx; }
.postDel { font-size: 24rpx; color: #D93025; text-align: right; }
.loadMore {
    text-align: center; color: #1A73E8; font-size: 28rpx;
    padding: 40rpx; border-top: 2rpx solid #f0f0f0; background: #fff;
}
.insCard {
    display: flex; background: #fff; border-radius: 16rpx;
    margin: 0 24rpx 20rpx; padding: 24rpx;
    box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.05);
}
.insImg { width: 160rpx; height: 160rpx; border-radius: 12rpx; flex-shrink: 0; margin-right: 24rpx; }
.insInfo { flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
.insCaption { font-size: 26rpx; color: #333; line-height: 1.5; max-height: 120rpx; overflow: hidden; }
.insDate { font-size: 22rpx; color: #86868B; margin: 10rpx 0; }
.gbpBtn {
    font-size: 24rpx; color: #1A73E8; border: 2rpx solid #1A73E8;
    border-radius: 20rpx; padding: 8rpx 24rpx; display: inline-block;
    &.synced { color: #86868B; border-color: #ccc; }
}
.emptyState { text-align: center; color: #86868B; padding: 100rpx 0; }
.emptyText { font-size: 28rpx; margin-bottom: 30rpx; }
.linkBtn { font-size: 28rpx; color: #1A73E8; }
.formSheet { padding: 40rpx 40rpx 0; }
.formTitle {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 40rpx;
}
.formTitleText { font-size: 34rpx; font-weight: bold; color: #1D1D1F; }
.formClose { font-size: 36rpx; color: #86868B; padding: 10rpx; }
.formItem { margin-bottom: 30rpx; }
.formLabel { font-size: 26rpx; color: #86868B; margin-bottom: 16rpx; }
.typeSelect { display: flex; gap: 20rpx; }
.typeBtn {
    flex: 1; text-align: center; padding: 18rpx 0; border-radius: 20rpx;
    border: 2rpx solid #ddd; font-size: 28rpx; color: #666;
    &.sel { border-color: #1A73E8; color: #1A73E8; background: #E8F0FE; }
}
.dateRow { display: flex; align-items: center; }
.textarea {
    width: 100%; min-height: 180rpx; border: 2rpx solid #ddd;
    border-radius: 12rpx; padding: 16rpx; font-size: 28rpx; box-sizing: border-box;
}
.imgPickRow { display: flex; align-items: center; gap: 20rpx; }
.previewImg { width: 120rpx; height: 120rpx; border-radius: 12rpx; }
.imgPickBtn {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; width: 120rpx; height: 120rpx;
    border: 2rpx dashed #1A73E8; border-radius: 12rpx;
    font-size: 22rpx; color: #1A73E8; gap: 8rpx;
}
.submitBtn {
    width: 100%; height: 96rpx; border-radius: 16rpx;
    background: #1A73E8; color: #fff; font-size: 32rpx; margin-top: 20rpx;
}
</style>
