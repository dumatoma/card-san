<template>
    <!-- [カスタムエイド 2026-10-19] ヘルプ記事をアプリの中で表示する画面(上の「<」で前の画面に戻れる)。
         今までは Safari が別アプリとして開き、アプリに戻りにくかった -->
    <view>
        <web-view v-if="link" :src="link"></web-view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                link: ""
            }
        },
        onLoad(options) {
            if (options && options.link) this.link = decodeURIComponent(options.link)
        }
    }
</script>

<style>
</style>
