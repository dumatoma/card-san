<template>
    <!-- [カスタムエイド 2026-10-24] Instagram の動画をアプリの中で再生する画面。
         以前(2026-06-30 の修正 #53)、お知らせの一覧に動画をそのまま置くと、動画がヘッダー・フッターの上に重なったため、
         一覧はサムネイル+▶にして、押すと Instagram のページ(ログインを求められ、アプリでは見られない)を開くようにしていた。
         一覧はサムネイルのまま、押したらこの画面で再生する(この画面には重なるものが無い) -->
    <view class="iv">
        <video class="iv-v" :src="src" :poster="poster" autoplay controls object-fit="contain" :show-fullscreen-btn="true"
            :enable-progress-gesture="true" @error="onErr" @waiting="loading = true" @play="onPlaying" @timeupdate="onPlaying"></video>
        <!-- [カスタムエイド 2026-10-25] 再生が始まるまで「読み込み中」を出す(止まって見えないように) -->
        <cover-view class="iv-load" v-if="loading && !err">
            <cover-view class="iv-load-t">動画を読み込んでいます…</cover-view>
        </cover-view>
        <view class="iv-err" v-if="err">
            <view>動画を再生できませんでした。</view>
            <view class="iv-link" v-if="link" @click="openIns">Instagram で見る</view>
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return { src: '', poster: '', link: '', err: false, loading: true }
        },
        onLoad(o) {
            this.src = decodeURIComponent(o.src || '')
            this.poster = decodeURIComponent(o.poster || '')
            this.link = decodeURIComponent(o.link || '')
        },
        methods: {
            onErr() {
                this.err = true
                this.loading = false
            },
            onPlaying() {
                if (this.loading) this.loading = false
            },
            openIns() {
                uni.redirectTo({ url: '../webins/webins?link=' + this.link })
            }
        }
    }
</script>

<style>
    page { background: #000; }
    .iv { position: fixed; left: 0; right: 0; top: 0; bottom: 0; background: #000; display: flex; align-items: center; justify-content: center; }
    .iv-v { width: 100%; height: 100%; }
    .iv-err { position: absolute; left: 0; right: 0; top: 40%; text-align: center; color: #fff; font-size: 28rpx; line-height: 2; }
    .iv-link { color: #4C9BFF; font-weight: bold; }
    .iv-load { position: absolute; left: 0; right: 0; top: 46%; display: flex; justify-content: center; }
    .iv-load-t { padding: 16rpx 32rpx; border-radius: 999rpx; background: rgba(0, 0, 0, 0.6); color: #FFFFFF; font-size: 26rpx; }
</style>
