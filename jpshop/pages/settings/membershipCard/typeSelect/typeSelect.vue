<template>
    <!-- [カスタムエイド 2026-10-20] カードを作る ①カードの種類を選ぶ(新しい画面)。モック「管理App メンバーズカード作成 改善モック」
         今までは一覧の上の小さなパネルで選んでいたのを、全画面に。見本の絵は運営から受け取ったもの(static/images/cardtype_*.svg) -->
    <view class="ts">
        <u-navbar title="カードを作る" :placeholder="true" :safeAreaInsetTop="true" :border="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="left">
                <view class="ts-nav-btn" @click="goBack">
                    <image src="../../../../static/icons/tclose.png" style="width: 50upx;height: 50upx;" mode=""></image>
                </view>
            </view>
        </u-navbar>

        <view class="ts-steps">
            <view class="ts-step now"><view class="bar"></view><text>1 種類</text></view>
            <view class="ts-step"><view class="bar"></view><text>2 アイコン</text></view>
            <view class="ts-step"><view class="bar"></view><text>3 デザイン</text></view>
        </view>

        <view class="ts-body">
            <view class="ts-h2">どのカードを作りますか?</view>
            <view class="ts-opt" v-for="o in opts" :key="o.type" :class="{ on: pick == o.type }" @click="pick = o.type">
                <view class="ts-opt-top">
                    <image class="ts-img" :src="o.img" mode="widthFix"></image>
                    <view class="ts-text">
                        <text class="ts-name">{{o.name}}</text>
                        <text class="ts-lead">{{o.lead}}</text>
                    </view>
                    <view class="ts-check" :class="{ on: pick == o.type }">
                        <u-icon v-if="pick == o.type" name="checkmark" color="#FFFFFF" size="12" bold></u-icon>
                    </view>
                </view>
                <view class="ts-points" v-if="pick == o.type">
                    <view class="ts-point" v-for="(p, i) in o.points" :key="i">・{{p}}</view>
                </view>
            </view>
        </view>

        <view class="ts-foot">
            <view class="ts-next" hover-class="ts-next-hover" @click="next">次へ</view>
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                pick: 1,
                opts: [{
                        type: 1,
                        name: 'スタンプカードA',
                        img: '/static/images/cardtype_a.svg',
                        lead: 'マス目にスタンプが並ぶ、紙のスタンプカードに近い形',
                        points: ['スタンプは3〜30個で満了', '1度に1〜9個まで押せる', '押した日付と担当者がマスに出る', 'カードの枠は色か画像で自由に', 'スタンプはテンプレートかオリジナル画像(PNG)']
                    },
                    {
                        type: 2,
                        name: 'スタンプカードB',
                        img: '/static/images/cardtype_b.svg',
                        lead: '表の形。押した日付・時刻・担当者が一覧で見やすい',
                        points: ['スタンプは3〜30個で満了', '1度に1〜9個まで押せる', '日付・時刻・担当者が1行ずつ残る', 'カードの枠は色か画像で自由に', 'スタンプはテンプレートかオリジナル画像(PNG)']
                    },
                    {
                        type: 3,
                        name: 'ポイントカード',
                        img: '/static/images/cardtype_point.svg',
                        lead: 'バーコードで読み取り、利用金額に合わせてポイントを付ける',
                        points: ['利用金額に合わせてポイントを付与', 'ポイントの増減もアプリからできる', 'バーコードの番号は変えられない', 'ポイントを付けず、来店履歴だけを管理する会員カードにもできる']
                    }
                ]
            }
        },
        onLoad(options) {
            if (options.type) this.pick = options.type * 1
        },
        methods: {
            goBack() {
                uni.navigateBack()
            },
            next() {
                uni.navigateTo({
                    url: '/pages/settings/membershipCard/firstStep/firstStep?type=' + this.pick
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .ts {
        min-height: 100vh;
        background: #F5F5F7;
        font-family: 'Hiragino Sans', sans-serif;
        color: #1D1D1F;
    }

    .ts-nav-btn {
        width: 64rpx;
        height: 64rpx;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ts-steps {
        display: flex;
        background: #FFFFFF;
        padding: 24rpx 32rpx 28rpx;
        border-bottom: 1px solid #E5E5EA;
    }

    .ts-step {
        flex: 1;
        margin-right: 12rpx;
        font-size: 24rpx;
        color: #6E6E73;

        .bar {
            height: 8rpx;
            border-radius: 4rpx;
            background: #E5E5EA;
            margin-bottom: 12rpx;
        }
    }

    .ts-step:last-child {
        margin-right: 0;
    }

    .ts-step.now {
        color: #1A73E8;
        font-weight: 700;

        .bar {
            background: #1A73E8;
        }
    }

    .ts-body {
        padding: 40rpx 24rpx 240rpx;
    }

    .ts-h2 {
        margin: 0 8rpx 24rpx;
        font-size: 36rpx;
        font-weight: 700;
    }

    .ts-opt {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 28rpx;
        margin-bottom: 24rpx;
        box-shadow: 0 0 0 1px #E5E5EA;
    }

    .ts-opt.on {
        box-shadow: 0 0 0 4rpx #1A73E8;
    }

    .ts-opt-top {
        display: flex;
        align-items: center;
    }

    .ts-img {
        width: 264rpx;
        flex-shrink: 0;
        border-radius: 16rpx;
        margin-right: 24rpx;
    }

    .ts-text {
        flex: 1;
        min-width: 0;
    }

    .ts-name {
        display: block;
        font-size: 32rpx;
        font-weight: 700;
        margin-bottom: 8rpx;
    }

    .ts-lead {
        display: block;
        font-size: 26rpx;
        line-height: 1.5;
        color: #515154;
    }

    .ts-check {
        width: 48rpx;
        height: 48rpx;
        flex-shrink: 0;
        margin-left: 12rpx;
        border-radius: 50%;
        border: 3rpx solid #AEAEB2;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ts-check.on {
        border: 0;
        background: #1A73E8;
    }

    .ts-points {
        border-top: 1px solid #E5E5EA;
        margin-top: 24rpx;
        padding-top: 20rpx;
    }

    .ts-point {
        font-size: 26rpx;
        line-height: 1.7;
    }

    .ts-foot {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        padding: 24rpx 24rpx calc(32rpx + env(safe-area-inset-bottom));
        background: linear-gradient(rgba(245, 245, 247, 0), #F5F5F7 30%);
    }

    .ts-next {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
    }

    .ts-next-hover {
        opacity: 0.8;
    }
</style>
