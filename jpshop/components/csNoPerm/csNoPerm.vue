<template>
    <!-- [カスタムエイド 2026-11-03] 副管理者が、お金・契約が変わる操作を押したときの「権限がありません」(アカウント画面の「実行する権限がありません」と同じ見た目) -->
    <view class="np-mask" v-if="show" @click="$emit('close')">
        <view class="np-co">
            <image src="/static/images/redright.png" mode=""></image>
            <view>実行する権限がありません</view>
        </view>
    </view>
</template>

<script>
    export default {
        name: 'csNoPerm',
        props: { show: { type: Boolean, default: false } }
    }
</script>

<style scoped>
    .np-mask { width: 100vw; height: 100vh; background: rgba(0, 0, 0, .6); position: fixed; top: 0; left: 0; z-index: 999; }
    .np-co { width: 272rpx; height: 268rpx; background: #FFFFFF; border-radius: 20rpx; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); text-align: center; color: #D93025; font-size: 32rpx; box-sizing: border-box; padding: 40upx; }
    .np-co image { width: 80upx; height: 80upx; }
</style>
