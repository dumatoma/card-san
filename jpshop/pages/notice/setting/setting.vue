<template>
    <!-- [カスタムエイド 2026-10-22] お知らせ設定を作り直し
         ・「お知らせを投稿する」「Instagram と連携する」を切り替え(選んでいる方は薄い青の面+青い線+青い文字)
         ・Instagram は、連携中のアカウント(写真・名前)を見せる。連携していなければ「Instagram と連携する」
         ・右上の「保存」をやめて、画面の下に「この設定で保存する」 -->
    <view class="ns">
        <u-navbar title="お知らせ設定" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="ns-pg">
            <view class="ns-card">
                <view class="ns-h">お客様の「お知らせ」に出すもの</view>
                <view class="ns-seg">
                    <view :class="{ on: current == 1 }" @click="current = 1">お知らせを投稿する</view>
                    <view :class="{ on: current == 2 }" @click="current = 2">Instagram と連携する</view>
                </view>
                <view class="ns-note" v-if="current == 1">管理Appで作ったお知らせが、お客様の Card-San App の「お知らせ」に出ます。</view>
                <view class="ns-note" v-else>連携した Instagram(ビジネスアカウント)の投稿が、お客様の Card-San App の「お知らせ」に出ます。投稿は Instagram で行います。</view>
            </view>

            <view class="ns-card" v-if="current == 2">
                <view class="ns-h">連携している Instagram</view>
                <view class="ns-acc" v-if="insName">
                    <image class="ns-av" :src="insAvatar || '../../../static/images/avatar.png'" mode="aspectFill"></image>
                    <view class="ns-acc-b">
                        <view class="ns-acc-n">@{{ insName }}</view>
                        <view class="ns-acc-s"><text class="ns-dot"></text>連携中</view>
                    </view>
                </view>
                <view class="ns-none" v-else>まだ連携していません</view>
                <view class="ns-link" hover-class="ns-hover" @click="toShou">{{ insName ? '別のアカウントで連携し直す' : 'Instagram と連携する' }}</view>
                <view class="ns-note">連携には Instagram の「ビジネスアカウント」が必要です。Instagram アプリを持っていないお客様が投稿を開いたときは、Instagram のダウンロード画面に移ります。</view>
            </view>

            <view class="ns-btn" hover-class="ns-hover" @click="save">この設定で保存する</view>
        </view>
    </view>
</template>
<script>
    import {
        setShopInfo,
        getShopInfo,
        getInsUrl,
        bindIns
    } from "@/api/index.js"
    export default {
        data() {
            return {
                current: 1,
                ins: "",
                twitter: "",
                insName: '',
                insAvatar: '',
                saving: false
            }
        },
        onShow() {
            let that = this
            // #ifdef APP-PLUS
            // Instagram の認証から戻ったとき(今までどおり)
            plus.globalEvent.addEventListener('plusMessage', function(msg) {
                if (msg.data && msg.data.args && msg.data.args.data && msg.data.args.data.arg != undefined) {
                    let urls = msg.data.args.data.arg.url
                    if (!urls) return
                    that.bindCode(urls.split("?code=")[1])
                }
            });
            // #endif
            that.getShopInfo()
        },
        onLoad(options) {
            // 連携が終わった画面から戻ってきたとき(?code=…)
            if (options && options.code) this.bindCode(decodeURIComponent(options.code))
        },
        methods: {
            // [カスタムエイド 2026-10-22] 認証のコードで連携を仕上げる(同じコードは1回だけ)
            bindCode(code) {
                if (!code) return
                const g = getApp().globalData || (getApp().globalData = {})
                if (g.csInsCode == code) return
                g.csInsCode = code
                bindIns({ code: code }).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({ title: 'Instagram と連携しました', duration: 2500 })
                        this.current = 2
                        this.getShopInfo()
                    }
                }).catch(() => {})
            },
            getShopInfo() {
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        const s = res.data.shop_info
                        if (s.notice_type == 1 || s.notice_type == 2) this.current = s.notice_type
                        this.ins = s.instagram
                        this.twitter = s.twitter
                        this.insName = s.instagram_username || ''
                        this.insAvatar = s.instagram_avatar || ''
                    }
                }).catch(() => {})
            },
            // Instagram の認証へ(今までどおり:先に「Instagram と連携する」に切り替えてから認証の画面を開く)
            toShou() {
                let that = this
                setShopInfo({ type: "notice", notice_type: 2, instagram: that.ins, twitter: that.twitter }).then((res) => {
                    if (res.code == 200) {
                        getInsUrl().then((rest) => {
                            if (rest.code == 200) {
                                uni.redirectTo({
                                    url: "../../webview/webview?link=" + encodeURIComponent(rest.data.url)
                                })
                            }
                        })
                    } else {
                        uni.showToast({ title: res.message, icon: "none" })
                    }
                })
            },
            save() {
                if (this.saving) return
                if (this.current == 2 && !this.insName) {
                    uni.showToast({ title: '先に「Instagram と連携する」で連携してください', icon: 'none', duration: 2500 })
                    return
                }
                this.saving = true
                setShopInfo({ type: "notice", notice_type: this.current, instagram: this.ins, twitter: this.twitter }).then((res) => {
                    this.saving = false
                    if (res.code == 200) {
                        uni.showToast({ title: '保存しました' })
                        setTimeout(() => uni.navigateBack(), 1200)
                    } else {
                        uni.showToast({ title: res.message, icon: "none" })
                    }
                }).catch(() => { this.saving = false })
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-22] お知らせ設定(余白はほかの画面と同じ決まり) */
    page { background: #F2F2F7; }
    .ns-pg { padding: 40rpx 32rpx calc(96rpx + env(safe-area-inset-bottom)); }
    .ns-card { background: #FFFFFF; border-radius: 32rpx; padding: 40rpx 32rpx; margin-bottom: 32rpx; }
    .ns-h { font-size: 30rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.2; margin-bottom: 24rpx; }
    .ns-seg { display: flex; background: #F2F2F7; border-radius: 20rpx; padding: 6rpx; }
    .ns-seg view { flex: 1; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 28rpx; min-height: 80rpx; border-radius: 16rpx; color: #6E6E73; border: 1px solid transparent; box-sizing: border-box; }
    .ns-seg .on { background: rgba(26, 115, 232, 0.10); border-color: #1A73E8; color: #1A73E8; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ns-note { font-size: 24rpx; color: #8A8A8E; line-height: 1.6; margin-top: 20rpx; }
    .ns-acc { display: flex; align-items: center; background: #F7F7FA; border-radius: 24rpx; padding: 24rpx; }
    .ns-av { width: 96rpx; height: 96rpx; border-radius: 50%; margin-right: 24rpx; background: #E5E5EA; }
    .ns-acc-n { font-size: 30rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ns-acc-s { font-size: 24rpx; color: #188038; margin-top: 6rpx; display: flex; align-items: center; }
    .ns-dot { width: 14rpx; height: 14rpx; border-radius: 50%; background: #34C759; margin-right: 10rpx; }
    .ns-none { font-size: 28rpx; color: #8A8A8E; background: #F7F7FA; border-radius: 24rpx; padding: 32rpx 24rpx; text-align: center; }
    .ns-link { margin-top: 24rpx; height: 88rpx; border-radius: 24rpx; border: 2rpx solid #1A73E8; color: #1A73E8; font-size: 28rpx; display: flex; align-items: center; justify-content: center; font-family: 'Hiragino Sans-W5', sans-serif; }
    .ns-btn { margin-top: 64rpx; height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif; display: flex; align-items: center; justify-content: center; }
    .ns-hover { opacity: 0.7; }
</style>
