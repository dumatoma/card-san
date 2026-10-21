<template>
    <view class="border_top">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="運営からのお知らせ" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-20] 1つのお知らせを1枚の白い角丸のカードにまとめ、文字の間隔を整えた。未読のお知らせには「未読」の印 -->
        <view class="nt">
            <view class="nt-card" v-for="(item, index) in array" :key="'u' + index">
                <view class="nt-head">
                    <text class="nt-new">未読</text>
                    <text class="nt-date">{{item.create_time}}</text>
                </view>
                <view class="nt-title">{{item.title}}</view>
                <text class="nt-body" :user-select="true">{{item.content}}</text>
            </view>
            <view class="nt-ge" v-if="array.length > 0 && array1.length > 0">
                <view class="nt-ge-line"></view>
                <text class="nt-ge-text">ここまで未読</text>
                <view class="nt-ge-line"></view>
            </view>
            <view class="nt-card" v-for="(item, index) in array1" :key="'r' + index">
                <view class="nt-head">
                    <text class="nt-date">{{item.create_time}}</text>
                </view>
                <view class="nt-title">{{item.title}}</view>
                <text class="nt-body" :user-select="true">{{item.content}}</text>
            </view>
            <view class="nt-empty" v-if="loaded && array.length == 0 && array1.length == 0">運営からのお知らせはまだありません</view>
        </view>
    </view>
</template>

<script>
    import {
        getYY
    } from "@/api/index.js"
    export default {
        data() {
            return {
                array: [],
                array1: [],
                loaded: false // [カスタムエイド 2026-10-20] 読み込みが終わったか
            };
        },
        created() {
            let that = this
            getYY("list").then((res) => {
                let temp = []
                let temp1 = []
                that.loaded = true // [カスタムエイド 2026-10-20]
                if (res.code == 200) {
                    res.data.notices.forEach((val, index) => {
                        if (val.read == 0) {
                            temp.push(val)
                        } else {
                            temp1.push(val)
                        }
                    })
                    that.array = temp
                    that.array1 = temp1
                }
            })
        },
        methods: {
            format(val) {
                val.replace(/[\n\r]/g, '<br/>')
                return val
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-20] これまでの画面と同じ見た目 */
    .nt {
        min-height: 100vh;
        background: #F5F5F7;
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
        box-sizing: border-box;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .nt-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 32rpx;
        margin-bottom: 24rpx;
    }

    .nt-head {
        display: flex;
        align-items: center;
        margin-bottom: 12rpx;
    }

    .nt-new {
        padding: 2rpx 14rpx;
        margin-right: 16rpx;
        border-radius: 8rpx;
        background: #1A73E8;
        color: #FFFFFF;
        font-size: 20rpx;
        font-weight: 700;
    }

    .nt-date {
        font-size: 24rpx;
        color: #6E6E73;
    }

    .nt-title {
        font-size: 32rpx;
        font-weight: 700;
        line-height: 1.5;
        margin-bottom: 16rpx;
    }

    .nt-body {
        display: block;
        font-size: 28rpx;
        line-height: 1.8;
        color: #3A3A3C;
        white-space: pre-wrap;
        word-break: break-all;
    }

    .nt-ge {
        display: flex;
        align-items: center;
        margin: 16rpx 0 40rpx;
    }

    .nt-ge-line {
        flex: 1;
        height: 1px;
        background: #AECBFA;
    }

    .nt-ge-text {
        margin: 0 24rpx;
        font-size: 24rpx;
        color: #1A73E8;
    }

    .nt-empty {
        padding: 120rpx 0;
        text-align: center;
        font-size: 28rpx;
        color: #6E6E73;
    }
</style>
