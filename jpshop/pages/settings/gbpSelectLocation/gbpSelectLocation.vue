<template>
    <view class="page">
        <u-navbar title="店舗を選択" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="head">
            <view class="title">連携する店舗を選択してください</view>
            <view class="lead" v-if="email">{{ email }} で管理している店舗です。Card-Sanと連携する店舗を1つ選択してください。</view>
            <view class="lead" v-else>Googleアカウントで管理している店舗です。Card-Sanと連携する店舗を1つ選択してください。</view>
        </view>

        <!-- 候補0件 -->
        <view class="empty" v-if="!candidates.length">
            {{ message || 'このGoogleアカウントにビジネス（店舗）が登録されていません。Googleビジネスプロフィールで店舗を登録してから連携してください。' }}
        </view>

        <!-- 店舗候補（単一選択） -->
        <view class="list" v-else>
            <view class="item" v-for="(c, i) in candidates" :key="c.name || i"
                :class="selected == c.name ? 'active' : ''" @click="selected = c.name">
                <view class="radio"><view class="radioInner"></view></view>
                <view class="itemText">
                    <view class="itemTitle">{{ c.title || '（店舗名なし）' }}</view>
                    <view class="itemAddr" v-if="c.address">{{ c.address }}</view>
                </view>
            </view>
        </view>

        <!-- 下部固定ボタン -->
        <view class="footer">
            <view class="btn" v-if="candidates.length" :class="(!selected || submitting) ? 'dis' : ''" @click="submit">
                {{ submitting ? '連携中…' : '選択した店舗を連携する' }}
            </view>
            <view class="btn outline" v-else @click="back">戻る</view>
        </view>
    </view>
</template>

<script>
    import { gbpSelectLocation } from '@/api/gbp.js'

    export default {
        data() {
            return {
                selectToken: '',
                candidates: [],
                email: '',
                message: '',
                selected: '',
                submitting: false,
            }
        },
        onLoad() {
            let d = uni.getStorageSync('gbp_select') || {}
            this.selectToken = d.select_token || ''
            this.candidates = Array.isArray(d.candidates) ? d.candidates : []
            this.email = d.email || ''
            this.message = d.message || ''
            // 候補が1件のみでも画面は省略せず、選択済みの状態で表示する
            if (this.candidates.length == 1) this.selected = this.candidates[0].name
        },
        onUnload() {
            uni.removeStorageSync('gbp_select')
        },
        methods: {
            submit() {
                if (!this.selected || this.submitting) return
                this.submitting = true
                gbpSelectLocation({ select_token: this.selectToken, location_name: this.selected }).then(res => {
                    if (res && res.code == 200) {
                        // 戻るまでの間に再送信されないよう submitting は解除しない（select_token は1回限り）
                        uni.$emit('gbpConnected', res.data || null)
                        uni.showToast({ title: 'Googleビジネスプロフィールと連携しました', icon: 'none', duration: 2500 })
                        setTimeout(() => { uni.navigateBack() }, 600)
                    } else {
                        this.submitting = false
                        if (res) uni.showToast({ title: res.message || 'エラーが発生しました', icon: 'none', duration: 2500 })
                    }
                }).catch(err => {
                    this.submitting = false
                    let r = err && err.response
                    uni.showToast({ title: (r && r.data && r.data.message) || '通信エラーが発生しました', icon: 'none', duration: 2500 })
                })
            },
            back() {
                uni.navigateBack()
            },
        }
    }
</script>

<style lang="scss">
    $text: #1d1d1f;
    $sub: #707070;
    $blue: #1a73e8;
    $border: #d2d2d7;

    page {
        background: #fff;
    }

    .page {
        min-height: 100vh;
        background: #fff;
        color: $text;
        border-top: 1px solid $border;
        box-sizing: border-box;
        padding-bottom: calc(180rpx + env(safe-area-inset-bottom));
    }

    .head {
        padding: 40rpx 38rpx 10rpx;
    }

    .title {
        font-size: 31rpx;
        color: #000;
        font-weight: 600;
        margin-bottom: 16rpx;
    }

    .lead {
        font-size: 25rpx;
        color: $sub;
        line-height: 1.6;
        word-break: break-all;
    }

    .empty {
        margin: 30rpx 38rpx;
        padding: 30rpx;
        border-radius: 12rpx;
        background: #fdecea;
        color: #d93025;
        font-size: 26rpx;
        line-height: 1.7;
    }

    .list {
        padding: 20rpx 38rpx;
    }

    .item {
        display: flex;
        align-items: center;
        padding: 30rpx;
        margin-bottom: 23rpx;
        background: #fff;
        border: 1px solid $border;
        border-radius: 12rpx;
        box-shadow: 2rpx 4rpx 8rpx 2rpx rgba(0, 0, 0, .08);

        &.active {
            border-color: $blue;
            background: rgba(26, 115, 232, .05);

            .radio {
                border-color: $blue;
            }

            .radioInner {
                background: $blue;
            }
        }
    }

    .radio {
        width: 36rpx;
        height: 36rpx;
        box-sizing: border-box;
        border: 1px solid $sub;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-right: 24rpx;
        background: #fff;
    }

    .radioInner {
        width: 18rpx;
        height: 18rpx;
        border-radius: 50%;
        background: transparent;
    }

    .itemText {
        flex: 1;
        min-width: 0;
    }

    .itemTitle {
        font-size: 29rpx;
        color: $text;
        line-height: 1.4;
        word-break: break-all;
    }

    .itemAddr {
        font-size: 24rpx;
        color: $sub;
        line-height: 1.5;
        margin-top: 8rpx;
        word-break: break-all;
    }

    .footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 10;
        background: #fff;
        border-top: 1px solid $border;
        padding: 24rpx 38rpx calc(24rpx + env(safe-area-inset-bottom));
    }

    .btn {
        width: 100%;
        padding: 29rpx 0;
        text-align: center;
        background: $blue;
        border: 1px solid $blue;
        border-radius: 19rpx;
        font-size: 29rpx;
        color: #fff;

        &.dis {
            opacity: .5;
        }

        &.outline {
            background: #f5f5f7;
            border-color: $text;
            color: $text;
        }
    }
</style>
