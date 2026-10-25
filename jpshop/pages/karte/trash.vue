<template>
    <!-- [カスタムエイド 2026-10-23] 削除したもの(D2)。消したカルテ・ページ・写真が30日間残り、「戻す」で元の場所へ -->
    <view class="kt">
        <u-navbar :placeholder="true" :safeAreaInsetTop="true" :border="true" @leftClick="back">
            <view slot="center" class="kt-title">
                <text class="kt-t">削除したもの</text>
                <text class="kt-s" v-if="name">{{ name }} さん</text>
            </view>
        </u-navbar>
        <view class="kt-body">
            <view class="kt-note">30日たつと自動で完全に消えます。</view>
            <view class="kt-card" v-if="list.length">
                <view class="kt-row" v-for="o in list" :key="o.type + o.id">
                    <image class="kt-th" :src="o.thumb" mode="aspectFill"></image>
                    <view class="kt-tt">
                        <view class="kt-n">{{ o.name }}</view>
                        <view class="kt-d">あと{{ o.days_left }}日で消えます</view>
                    </view>
                    <view class="kt-rs" hover-class="kt-hover" @click="restore(o)">戻す</view>
                </view>
            </view>
            <view class="kt-empty" v-else-if="loaded">削除したものはありません</view>
        </view>
    </view>
</template>

<script>
    import { karteTrash, karteRestore } from '@/api/karte.js'

    export default {
        data() {
            return { mid: 0, name: '', list: [], loaded: false }
        },
        onLoad(o) {
            this.mid = Number(o.mid) || 0
            this.name = o.name ? decodeURIComponent(o.name) : ''
            this.load()
        },
        methods: {
            back() {
                uni.navigateBack()
            },
            load() {
                karteTrash(this.mid).then((res) => {
                    if (res && res.code == 200) this.list = res.data.list || []
                    else if (res && res.code == 403) uni.showToast({ title: res.message, icon: 'none' })
                    this.loaded = true
                })
            },
            restore(o) {
                karteRestore(o.type, o.id).then((res) => {
                    if (res && res.code == 200) {
                        uni.showToast({ title: '元に戻しました', icon: 'none' })
                        this.load()
                    }
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .kt { min-height: 100vh; background: #F2F2F7; color: #1D1D1F; }
    .kt-hover { opacity: 0.6; }
    .kt-title { display: flex; flex-direction: column; align-items: center; line-height: 1.2; }
    .kt-t { font-size: 32rpx; font-weight: 700; }
    .kt-s { font-size: 22rpx; color: #6E6E73; }
    .kt-body { padding: 32rpx 24rpx; }
    .kt-note { font-size: 24rpx; color: #6E6E73; margin: 0 12rpx 24rpx; }
    .kt-card { background: #FFFFFF; border-radius: 32rpx; padding: 0 32rpx; }
    .kt-row { display: flex; align-items: center; padding: 24rpx 0; border-bottom: 1px solid #E5E5EA; }
    .kt-row:last-child { border-bottom: 0; }
    .kt-th { width: 80rpx; height: 108rpx; border-radius: 8rpx; background: #FBFAF6; flex-shrink: 0; }
    .kt-tt { flex: 1; min-width: 0; margin: 0 20rpx 0 24rpx; }
    .kt-n { font-size: 28rpx; word-break: break-all; }
    .kt-d { font-size: 22rpx; color: #D93025; margin-top: 4rpx; }
    .kt-rs { font-size: 26rpx; color: #1A73E8; border: 1px solid #1A73E8; border-radius: 28rpx; padding: 6rpx 28rpx; }
    .kt-empty { text-align: center; color: #8E8E93; font-size: 26rpx; padding: 80rpx 0; }
</style>
