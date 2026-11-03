<template>
    <!-- [カスタムエイド 2026-11-03] A2 無料期間が終わりました/A3 ご契約が終了しました(全面のお知らせ)。見本「Card-San お知らせ見本」A2・A3 -->
    <view class="cs-ov" v-if="show" @touchmove.stop.prevent="noop">
        <view class="cs-lk">
            <view class="cs-ic" :class="{ end: isEnd }">{{ isEnd ? '×' : '!' }}</view>
            <view class="cs-t">{{ isEnd ? 'ご契約が終了しました' : '無料期間が終わりました' }}</view>
            <view class="cs-d">{{ isEnd ? 'お店のアプリは、お客様が使えない状態です。' : '続けてお使いいただくには、プランのお申し込みをお願いします。' }}</view>
            <view class="cs-warn" v-if="isEnd">
                <view><text>データの保管は </text><text class="b">30日間</text><text> です。</text></view>
                <view><text class="b">{{ md(notice.delete_at) }}</text><text> に、お店のアプリのデータはすべて削除されます。</text></view>
                <view>それまでに再開すれば、データはそのままです。</view>
            </view>
            <view class="cs-warn" v-else-if="untilFuture">
                <view><text>お客様のアプリは </text><text class="b">{{ md(notice.customer_app_until) }}</text><text> まで使えます。</text></view>
                <view>それまでにお申し込みください。</view>
            </view>
            <view class="cs-warn" v-else>
                <view>お客様のアプリは、現在使えない状態です。</view>
                <view>お申し込みいただくと、すぐに使えるようになります。</view>
            </view>
            <view class="cs-pay">
                <view class="cs-h" v-if="!isEnd">お申し込み方法</view>
                <view class="cs-btn" :class="ios ? 'ios' : 'gp'" @click="$emit('apply')">
                    <block v-if="isEnd">
                        <view>アプリを再開する</view>
                        <view class="cs-bs">プランを選んで {{ ios ? 'App Store' : 'Google Play' }} で申し込む</view>
                    </block>
                    <block v-else>
                        <view>プランを選んで</view>
                        <view>{{ ios ? 'App Store' : 'Google Play' }}で申し込む</view>
                    </block>
                </view>
                <view class="cs-cc">
                    <view class="cs-cc-t">
                        <image class="cs-cc-i" src="/static/svg/cs_card.svg" mode="aspectFit"></image>
                        <text>クレジットカード</text>
                    </view>
                    <view class="cs-cc-d">
                        <text>クレジットカードでのお申し込みは、CardSan管理Webサイト(</text><text class="g">card-san.jp</text><text>)から手続きしてください。</text>
                    </view>
                </view>
            </view>
            <view class="cs-links">
                <text class="cs-a" @click="$emit('contract')">契約内容を見る</text>
                <text class="cs-a" @click="help">お問い合わせ</text>
                <text class="cs-a" @click="$emit('logout')">ログアウト</text>
            </view>
        </view>
    </view>
</template>
<script>
    import { getPlatform } from '@/utils/platform.js'
    export default {
        props: { notice: { type: Object, default: null }, locked: { type: Boolean, default: false } },
        computed: {
            show() { return this.locked && !!this.notice && (this.notice.state === 'trial_ended' || this.notice.state === 'contract_ended') },
            isEnd() { return !!this.notice && this.notice.state === 'contract_ended' },
            ios() { return getPlatform() !== 'android' },
            untilFuture() { return !!this.notice && this.notice.customer_app_until * 1000 > Date.now() }
        },
        methods: {
            noop() {},
            md(t) { const d = new Date((t || 0) * 1000); return (d.getMonth() + 1) + '月' + d.getDate() + '日' },
            help() {
                const u = 'https://cardsan.zendesk.com/hc/ja'
                // #ifdef APP-PLUS
                plus.runtime.openURL(u)
                // #endif
            }
        }
    }
</script>
<style scoped>
    .cs-ov { position: fixed; left: 0; top: 0; right: 0; bottom: 0; z-index: 990; /* ログアウトの確認(mod 999)が上に出るように */ background: rgba(0, 0, 0, 0.55); display: flex; align-items: center; justify-content: center; padding: 28rpx 0; box-sizing: border-box; }
    .cs-lk { width: 86%; max-height: 100%; overflow-y: auto; border-radius: 36rpx; box-shadow: 0 20rpx 60rpx rgba(0, 0, 0, 0.35); box-sizing: border-box; background: #FFFFFF; display: flex; flex-direction: column; align-items: center; text-align: center; padding: 36rpx 28rpx 28rpx; }
    .cs-lk > view { margin-top: 18rpx; }
    .cs-lk > view:first-child { margin-top: 0; }
    .cs-lk > .cs-warn, .cs-lk > .cs-pay, .cs-lk > .cs-links { margin-top: 40rpx; } /* [カスタムエイド 2026-11-03] カード・ボタンの間は20px(坂本さん) */
    .cs-ic { width: 68rpx; height: 68rpx; border-radius: 34rpx; background: #E8710A; color: #FFFFFF; font-size: 36rpx; font-weight: bold; line-height: 68rpx; text-align: center; }
    .cs-ic.end { background: #5F6368; }
    .cs-t { font-size: 38rpx; font-weight: bold; color: #1D1D1F; }
    .cs-d { font-size: 26rpx; color: #515154; line-height: 1.6; }
    .cs-warn { background: #FCE8E6; color: #A50E0E; border-radius: 24rpx; padding: 20rpx 24rpx; font-size: 25rpx; line-height: 1.6; width: 100%; box-sizing: border-box; }
    .cs-warn .b { font-weight: bold; }
    .cs-pay { width: 100%; background: #F5F5F7; border-radius: 24rpx; padding: 24rpx; box-sizing: border-box; display: flex; flex-direction: column; }
    .cs-pay > view { margin-top: 40rpx; } /* カード・ボタンの間は20px */
    .cs-pay > view:first-child { margin-top: 0; }
    .cs-h { font-weight: bold; font-size: 26rpx; color: #1D1D1F; }
    .cs-btn { color: #FFFFFF; border-radius: 22rpx; padding: 22rpx; font-weight: bold; font-size: 28rpx; line-height: 1.5; }
    .cs-btn.ios { background: #000000; }
    .cs-btn.gp { background: #4285F4; }
    .cs-bs { margin-top: 4rpx; font-size: 23rpx; font-weight: 500; opacity: 0.9; }
    .cs-cc { background: #FFFFFF; border: 3rpx solid #2B8A87; border-radius: 22rpx; padding: 22rpx 24rpx; display: flex; flex-direction: column; text-align: left; }
    .cs-cc-t { display: flex; flex-direction: row; align-items: center; font-weight: bold; font-size: 28rpx; color: #1F6E6B; }
    .cs-cc-i { width: 44rpx; height: 32rpx; margin-right: 16rpx; }
    .cs-cc-d { margin-top: 10rpx; font-size: 25rpx; line-height: 1.6; color: #1D1D1F; }
    .cs-cc-d .g { color: #1F6E6B; font-weight: bold; }
    .cs-links { display: flex; flex-direction: row; flex-wrap: wrap; justify-content: center; }
    .cs-a { font-size: 24rpx; color: #1A73E8; margin: 6rpx 14rpx 0; }
</style>
