<template>
    <!-- [カスタムエイド 2026-10-31] 予約の「連絡のしかた」(アプリ・メール・LINE・SMS)。予約詳細・予約内容で同じ部品を使う。
         mode="icon" はマークだけ(予約通知の一覧)。mode="rows" は「連絡方法」の行と、メールの予約ならメールアドレスの行 -->
    <view v-if="info" class="cs-ct-wrap" :class="mode == 'icon' ? 'cs-ct-iconmode' : ''">
        <image v-if="mode == 'icon'" class="cs-ct-icon-s" :src="info.icon" mode="aspectFit"></image>
        <block v-else>
            <view class="cs-ct-row">
                <view class="cs-ct-lab">連絡方法</view>
                <view class="cs-ct-val">
                    <image class="cs-ct-icon" :src="info.icon" mode="aspectFit"></image>
                    <text>{{ info.name }}</text>
                </view>
            </view>
            <view class="cs-ct-row" v-if="email">
                <view class="cs-ct-lab">メール</view>
                <view class="cs-ct-val cs-ct-mail" hover-class="cs-ct-hover" @click="openMail">{{ email }}</view>
            </view>
        </block>
    </view>
</template>

<script>
    const LIST = {
        app: { name: 'アプリ', icon: '/static/icons/cs_ct_app.png' }, // [カスタムエイド 2026-11-02] Card-San のアイコンを丸く
        mail: { name: 'メール', icon: '/static/icons/cs_ct_mail.svg' },
        line: { name: 'LINE', icon: '/static/icons/cs_ct_line.svg' },
        sms: { name: 'SMS', icon: '/static/icons/cs_ct_sms.png' } // [カスタムエイド 2026-11-02] グレー・吹き出しに「SMS」
    }
    export default {
        name: 'csContact',
        props: {
            contact: { type: String, default: '' }, // app / mail / line / sms(サーバーの CsOrderContact と同じ)
            email: { type: String, default: '' },
            mode: { type: String, default: 'rows' } // rows / icon
        },
        computed: {
            info() { return LIST[this.contact] || null }
        },
        methods: {
            // メールアドレスを押すと、メールを書く画面を開く(開けないときはコピー)
            openMail() {
                const url = 'mailto:' + this.email
                // #ifdef APP-PLUS
                try { plus.runtime.openURL(url, () => { this.copyMail() }); return } catch (e) {}
                // #endif
                // #ifdef H5
                try { window.location.href = url; return } catch (e) {}
                // #endif
                this.copyMail()
            },
            copyMail() {
                uni.setClipboardData({ data: this.email, success: () => { uni.showToast({ title: 'メールアドレスをコピーしました', icon: 'none' }) } })
            }
        }
    }
</script>

<style scoped>
    .cs-ct-wrap { display: block; }
    .cs-ct-iconmode { display: inline-flex; align-items: center; flex-shrink: 0; }
    .cs-ct-icon-s { width: 40rpx; height: 40rpx; display: block; }
    .cs-ct-row { display: flex; align-items: center; justify-content: space-between; min-height: 100rpx; border-bottom: 1px solid #E5E5EA; } /* 予約詳細・予約内容の行と同じ大きさ */
    .cs-ct-lab { width: 180rpx; font-size: 30rpx; color: #1D1D1F; flex-shrink: 0; }
    .cs-ct-val { display: flex; align-items: center; justify-content: flex-end; font-size: 30rpx; color: #1D1D1F; flex: 1; min-width: 0; margin-left: 24rpx; text-align: right; word-break: break-all; }
    .cs-ct-icon { width: 40rpx; height: 40rpx; margin-right: 12rpx; flex-shrink: 0; }
    .cs-ct-mail { color: #1A73E8; }
    .cs-ct-hover { opacity: 0.6; }
</style>
