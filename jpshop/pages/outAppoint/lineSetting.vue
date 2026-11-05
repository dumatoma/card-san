<template>
    <!-- [カスタムエイド 2026-11-04 段階9] LINE予約の設定:つないでいる LINE 公式アカウント・鍵(シークレットははじめとおわりの3文字だけ)・Webhook URL・コールバック URL・設定を解除する -->
    <view class="ls">
        <u-navbar title="LINE予約の設定" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="ls-body">
            <view class="ls-loading" v-if="!loaded">読み込んでいます…</view>
            <view class="ls-card" v-else-if="d.set != 1">
                <view class="ls-note">LINE 予約の設定はありません。「予約Webサイト」の LINE のブロックから設定してください。</view>
            </view>
            <view class="ls-card" v-else>
                <view class="ls-ok">✓ LINE 公式アカウント「{{ d.bot_name }}」とつながっています</view>

                <view class="ls-grp">
                    <view class="ls-gh"><text class="ls-gt">Messaging API のチャネル</text><text class="ls-tag ma">Messaging API</text></view>
                    <view class="ls-lab">チャネル ID</view>
                    <view class="ls-val">{{ k.msg_id }}</view>
                    <view class="ls-lab">チャネルシークレット</view>
                    <view class="ls-val">{{ k.msg_secret }}</view>
                </view>
                <view class="ls-grp">
                    <view class="ls-gh"><text class="ls-gt">LINE ログインのチャネル</text><text class="ls-tag lg">LINEログイン</text></view>
                    <view class="ls-lab">チャネル ID</view>
                    <view class="ls-val">{{ k.login_id }}</view>
                    <view class="ls-lab">チャネルシークレット</view>
                    <view class="ls-val">{{ k.login_secret }}</view>
                    <view class="ls-lab">LIFF ID(「LIFF」タブ)</view>
                    <view class="ls-val">{{ k.liff_id }}</view>
                </view>

                <view class="ls-url">
                    <view class="ls-lab">Webhook URL(Messaging API のチャネルに入れる)</view>
                    <view class="ls-urlrow"><text class="ls-urltx">{{ d.webhook_url }}</text><text class="ls-copy" hover-class="ls-hover" @click="copy(d.webhook_url)">コピー</text></view>
                </view>
                <view class="ls-url">
                    <view class="ls-lab">コールバック URL(LINE ログインのチャネルに入れる)</view>
                    <view class="ls-urlrow"><text class="ls-urltx">{{ d.callback_url }}</text><text class="ls-copy" hover-class="ls-hover" @click="copy(d.callback_url)">コピー</text></view>
                </view>
                <view class="ls-note">安全のため、シークレットははじめとおわりの3文字だけ出しています。</view>

                <block v-if="canEdit">
                    <view class="ls-del" :class="{ off: busy }" hover-class="ls-hover" @click="remove">{{ busy ? '解除しています…' : '設定を解除する' }}</view>
                    <view class="ls-note">解除すると LINE でのお知らせを止め、予約WEB に LINE を出しません。LINE のアカウントを変えるときも、ここから解除して入れ直します。</view>
                </block>
                <view class="ls-warn" v-else>変えられるのは、管理者と「予約設定」の権限がある副管理者だけです。</view>
            </view>
        </view>
    </view>
</template>

<script>
    import { csLine, csLineRemove } from '@/api/index.js'
    export default {
        data() {
            return { loaded: false, d: {}, busy: false, canEdit: true }
        },
        computed: {
            k() { return (this.d && this.d.keys) || {} }
        },
        onShow() {
            this.load()
        },
        methods: {
            load() {
                csLine().then((res) => {
                    if (res.code == 200 && res.data) { this.d = res.data; this.canEdit = res.data.can_edit != 0 }
                    else uni.showToast({ title: res.message || '読み込めませんでした', icon: 'none' })
                    this.loaded = true
                }).catch(() => { this.loaded = true; uni.showToast({ title: '通信できませんでした', icon: 'none' }) })
            },
            copy(t) {
                if (!t) return
                uni.setClipboardData({ data: t, success: () => { uni.hideToast(); setTimeout(() => { uni.showToast({ title: 'コピーしました' }) }, 50) } })
            },
            // 確認を出してから解除する(鍵を消し、LINE で送るのを止め、予約WEB に LINE を出さない)
            remove() {
                if (this.busy || !this.canEdit) return
                uni.showModal({
                    title: 'LINE予約の設定を解除しますか?',
                    content: 'LINE でのお知らせを止め、予約WEB に LINE を出さなくなります。すでに LINE で受けた予約にも、お知らせが届かなくなります。',
                    confirmText: '解除する',
                    confirmColor: '#D93025',
                    cancelText: 'やめる',
                    success: (r) => {
                        if (!r.confirm) return
                        this.busy = true
                        csLineRemove().then((res) => {
                            this.busy = false
                            if (res.code == 200) {
                                uni.showToast({ title: res.message || '解除しました', icon: 'none' })
                                setTimeout(() => { uni.navigateBack() }, 800)
                            } else uni.showToast({ title: res.message || '解除できませんでした', icon: 'none' })
                        }).catch(() => { this.busy = false; uni.showToast({ title: '通信できませんでした', icon: 'none' }) })
                    }
                })
            }
        }
    }
</script>

<style>
    page { background: #F5F5F7; }
    .ls-body { padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom)); }
    .ls-loading { text-align: center; color: #8A8A8E; font-size: 26rpx; padding: 80rpx 0; }
    .ls-card { background: #FFFFFF; border-radius: 32rpx; padding: 32rpx 28rpx; box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.10); }
    .ls-card > view + view { margin-top: 20rpx; }
    .ls-ok { font-size: 26rpx; font-weight: 700; color: #0D652D; background: #E6F4EA; border-radius: 16rpx; padding: 16rpx 20rpx; line-height: 1.6; }
    .ls-grp { border: 1px solid #E5E5EA; border-radius: 20rpx; padding: 24rpx; }
    .ls-gh { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4rpx; }
    .ls-gt { font-size: 26rpx; font-weight: 700; color: #1D1D1F; }
    .ls-tag { flex-shrink: 0; font-size: 20rpx; font-weight: 700; padding: 4rpx 12rpx; border-radius: 8rpx; }
    .ls-tag.ma { background: #E6F4EA; color: #06A64C; }
    .ls-tag.lg { background: #F5F5F7; color: #1D1D1F; border: 1px solid #D2D2D7; }
    .ls-lab { font-size: 24rpx; font-weight: 700; color: #1D1D1F; margin-top: 16rpx; margin-bottom: 8rpx; }
    .ls-val { min-height: 80rpx; line-height: 1.5; padding: 20rpx 24rpx; box-sizing: border-box; border: 1px solid #E5E5EA; border-radius: 20rpx; background: #F5F5F7; font-size: 28rpx; color: #1D1D1F; word-break: break-all; }
    .ls-url .ls-lab { margin-top: 0; }
    .ls-urlrow { display: flex; align-items: flex-start; }
    .ls-urltx { flex: 1; min-width: 0; font-size: 24rpx; color: #1D1D1F; word-break: break-all; line-height: 1.6; }
    .ls-copy { flex-shrink: 0; margin-left: 16rpx; font-size: 26rpx; font-weight: 700; color: #1A73E8; }
    .ls-note { font-size: 24rpx; color: #6E6E73; line-height: 1.6; }
    .ls-warn { font-size: 24rpx; color: #8A4B00; background: #FFF4E5; border-radius: 16rpx; padding: 16rpx 20rpx; line-height: 1.6; }
    /* ボタンの上の間はほかの2倍 */
    .ls-card > view.ls-del { margin-top: 40rpx; height: 96rpx; line-height: 96rpx; border-radius: 24rpx; border: 2rpx solid #D93025; color: #D93025; background: #FFFFFF; text-align: center; font-size: 30rpx; font-weight: 700; }
    .ls-del.off { opacity: 0.5; }
    .ls-hover { opacity: 0.7; }
</style>
