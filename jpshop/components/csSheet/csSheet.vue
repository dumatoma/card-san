<template>
    <!-- [カスタムエイド 2026-10-23] 下から出る画面の共通の形(見出し・右上の ×・下の余白 48rpx)。中身は slot -->
    <u-popup :show="show" mode="bottom" round="16" :safeAreaInsetBottom="false" :closeOnClickOverlay="true" @close="$emit('close')">
        <view class="sh" :class="{ dark: dark }">
            <view class="sh-h">
                <text>{{ title }}</text>
                <view class="sh-x" hover-class="sh-hover" @click="$emit('close')">✕</view>
            </view>
            <view class="sh-body">
                <slot></slot>
            </view>
        </view>
    </u-popup>
</template>

<script>
    export default {
        name: 'csSheet',
        props: {
            show: { type: Boolean, default: false },
            title: { type: String, default: '' },
            dark: { type: Boolean, default: false }
        }
    }
</script>

<style lang="scss">
    .sh { background: #FFFFFF; border-radius: 32rpx 32rpx 0 0; padding-bottom: calc(48rpx + env(safe-area-inset-bottom)); color: #1D1D1F; }
    .sh-h { position: relative; height: 108rpx; line-height: 108rpx; text-align: center; font-size: 32rpx; font-weight: bold; border-bottom: 1px solid #E5E5EA; }
    .sh-x { position: absolute; right: 24rpx; top: 22rpx; width: 64rpx; height: 64rpx; line-height: 64rpx; border-radius: 50%; background: #E5E5EA; color: #6E6E73; font-size: 26rpx; font-weight: normal; text-align: center; }
    .sh-hover { opacity: 0.6; }
    .sh-body { max-height: 75vh; overflow-y: auto; }
</style>
