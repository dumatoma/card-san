<template>
    <view class="container">
        <!-- 連携状態ヘッダー -->
        <view class="statusCard" :class="connected ? 'connected' : 'disconnected'">
            <view class="statusIcon">
                <image src="../../../static/svg/google_g.svg" mode="aspectFit" class="gIcon"></image>
            </view>
            <view class="statusInfo">
                <view class="statusTitle">Google Business Profile</view>
                <view class="statusBadge" v-if="connected">✓ 連携済み</view>
                <view class="statusBadge gray" v-else">未連携</view>
                <view class="statusDate" v-if="connected && status.connected_at">連携日時: {{ status.connected_at }}</view>
            </view>
        </view>

        <!-- 未連携 -->
        <view v-if="!connected">
            <view class="desc">
                Google Business Profileと連携すると、クチコミの確認・返信やGBP投稿がCard-Sanから行えます。
            </view>
            <u-button class="actionBtn" type="primary" @click="connectGBP" :loading="loading">
                Googleアカウントで連携する
            </u-button>
        </view>

        <!-- 連携済み -->
        <view v-else>
            <view class="section">
                <view class="sectionTitle">同期設定</view>
                <view class="switchItem">
                    <view>
                        <view class="switchLabel">Card-San → GBP 自動同期</view>
                        <view class="switchSub">店舗情報変更時にGBPへ自動反映</view>
                    </view>
                    <u-switch v-model="syncOn" activeColor="#1A73E8" @change="saveSettings"></u-switch>
                </view>
            </view>
            <view class="section">
                <view class="sectionTitle">クチコミ・投稿</view>
                <view class="menuItem" @click="toReviews">
                    <text>Googleクチコミを管理</text>
                    <text class="arrow">›</text>
                </view>
                <view class="menuItem" @click="toSocialMedia">
                    <text>GBP投稿を管理</text>
                    <text class="arrow">›</text>
                </view>
            </view>
            <view class="section">
                <u-button class="disconnectBtn" @click="confirmDisconnect">連携を解除する</u-button>
            </view>
        </view>

        <!-- WebView for OAuth -->
        <web-view v-if="showWebView" :src="authUrl" @message="onWebViewMessage"></web-view>
    </view>
</template>

<script>
    import {
        getGBPStatus, getGBPAuthUrl, bindGBP, disconnectGBP, updateGBPSettings
    } from '@/api/index.js'
    export default {
        data() {
            return {
                connected: false,
                status: {},
                syncOn: false,
                loading: false,
                showWebView: false,
                authUrl: '',
            }
        },
        onLoad(options) {
            // OAuth コールバックから code が渡ってきた場合
            if (options.code) {
                this.handleCode(options.code)
            } else {
                this.loadStatus()
            }
        },
        methods: {
            loadStatus() {
                getGBPStatus().then(res => {
                    if (res.code == 200) {
                        this.connected = res.data.connected
                        this.status    = res.data
                        this.syncOn    = res.data.sync_on == 1
                    }
                }).catch(() => {})
            },
            connectGBP() {
                this.loading = true
                getGBPAuthUrl().then(res => {
                    this.loading = false
                    if (res.code == 200) {
                        // アプリ内ブラウザでOAuthページを開く
                        plus.runtime.openURL(res.data.url)
                    }
                }).catch(() => { this.loading = false })
            },
            handleCode(code) {
                uni.showLoading({ title: '連携中...', mask: true })
                bindGBP({ code }).then(res => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        uni.showToast({ title: '連携しました', icon: 'success' })
                        this.loadStatus()
                    } else {
                        uni.showToast({ title: res.message, icon: 'none' })
                    }
                }).catch(() => { uni.hideLoading() })
            },
            saveSettings() {
                updateGBPSettings({ sync_on: this.syncOn ? 1 : 0 }).catch(() => {})
            },
            confirmDisconnect() {
                uni.showModal({
                    title: '連携を解除しますか？',
                    content: 'クチコミ管理・GBP投稿機能が使えなくなります。',
                    confirmText: '解除する',
                    confirmColor: '#D93025',
                    success: res => {
                        if (res.confirm) this.doDisconnect()
                    }
                })
            },
            doDisconnect() {
                disconnectGBP().then(res => {
                    if (res.code == 200) {
                        uni.showToast({ title: '連携を解除しました', icon: 'success' })
                        this.connected = false
                        this.status    = {}
                    }
                }).catch(() => {})
            },
            toReviews() {
                uni.navigateTo({ url: '/pages/message/gbpReviews/gbpReviews' })
            },
            toSocialMedia() {
                uni.navigateTo({ url: '/pages/settings/socialMedia/socialMedia' })
            },
        }
    }
</script>

<style lang="scss">
.container {
    padding: 40rpx;
    background: #f9f9f9;
    min-height: 100vh;
}
.statusCard {
    background: #fff; border-radius: 20rpx; padding: 40rpx;
    display: flex; align-items: center; margin-bottom: 40rpx;
    box-shadow: 0 2rpx 12rpx rgba(0,0,0,0.08);
    &.connected { border-left: 8rpx solid #1A73E8; }
    &.disconnected { border-left: 8rpx solid #ccc; }
}
.gIcon { width: 80rpx; height: 80rpx; margin-right: 30rpx; }
.statusTitle { font-size: 30rpx; font-weight: bold; color: #1D1D1F; margin-bottom: 10rpx; }
.statusBadge {
    display: inline-block; padding: 4rpx 16rpx; border-radius: 20rpx;
    font-size: 24rpx; background: #1A73E8; color: #fff; margin-bottom: 8rpx;
    &.gray { background: #ccc; color: #666; }
}
.statusDate { font-size: 22rpx; color: #86868B; }
.desc { font-size: 28rpx; color: #666; line-height: 1.8; margin-bottom: 60rpx; }
.actionBtn {
    width: 100%; height: 96upx; border-radius: 20upx;
    font-size: 32upx; background: #1A73E8; color: #fff;
}
.section {
    background: #fff; border-radius: 20rpx; padding: 0 30rpx;
    margin-bottom: 30rpx; box-shadow: 0 2rpx 8rpx rgba(0,0,0,0.05);
}
.sectionTitle {
    font-size: 26rpx; color: #86868B; padding: 24rpx 0 14rpx;
    border-bottom: 2rpx solid #f0f0f0;
}
.switchItem {
    display: flex; align-items: center; justify-content: space-between;
    padding: 30rpx 0; border-bottom: 2rpx solid #f0f0f0;
}
.switchLabel { font-size: 30rpx; color: #1D1D1F; margin-bottom: 6rpx; }
.switchSub { font-size: 24rpx; color: #86868B; }
.menuItem {
    display: flex; align-items: center; justify-content: space-between;
    padding: 34rpx 0; border-bottom: 2rpx solid #f0f0f0;
    font-size: 30rpx; color: #1D1D1F;
}
.arrow { color: #ccc; font-size: 36rpx; }
.disconnectBtn {
    width: 100%; height: 88upx; border-radius: 16upx; margin: 20rpx 0;
    background: #fff; color: #D93025; border: 2rpx solid #D93025; font-size: 30rpx;
}
</style>
