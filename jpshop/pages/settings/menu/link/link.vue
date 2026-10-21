<template>
    <!-- [カスタムエイド 2026-10-20] 外部リンクの見た目を店舗情報などと合わせた
         ・説明を青い帯に、URLの入力欄を共通の枠(#D2D2D7・角丸・入力中は青)に
         ・「外部リンク先を上記URLに設定する」をスイッチに(オンにした時だけ黒い文字)。保存ボタンも共通の形に -->
    <view class="lk">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="外部リンク" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="lk-body">
            <view class="lk-info">
                <image class="lk-info-icon" src="../../../../static/icons/menu_d.png" mode=""></image>
                <text>お客様アプリの「メニュー」アイコンを押したときに開く、リンク先のURLを入力してください。</text>
            </view>
            <view class="lk-card">
                <view class="lk-label">URL</view>
                <input class="lk-in" :class="{ on: fk }" v-model="urls" type="text" maxlength="-1" :cursor-spacing="120"
                    placeholder-class="lk-ph" placeholder="https://〜" @focus="fk = true" @blur="fk = false" />
                <view class="lk-sw-row">
                    <view class="lk-sw-text">
                        <text class="lk-sw-name" :class="{ on: checked == 1 }">外部リンク先を上記URLに設定する</text>
                    </view>
                    <u-switch :value="checked == 1" activeColor="#34C759" @change="changeCheck"></u-switch>
                </view>
            </view>
            <view class="lk-save" hover-class="lk-save-hover" @click="save">保存する</view>
        </view>
    </view>
</template>

<script>
    import {
        setShopInfo,
        getShopInfo
    } from "@/api/index.js"
    export default {
        data() {
            return {
                checked: 0,
                urls: "",
                fk: false // [カスタムエイド 2026-10-20]
            };
        },
        created() {
            this.getUrl()
        },
        methods: {
            getUrl() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        let url = res.data.shop_info.out_url.split("!@")
                        that.urls = url[0]
                        that.checked = url[1]
                    }
                })
            },

            save() {
                let that = this
                let data = {}
                data.type = 'out_url'
                data.out_url = that.urls + '!@' + that.checked
                setShopInfo(data).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                    } else {
                        uni.showToast({ title: res.message, icon: 'none' }) // [カスタムエイド 2026-10-20]
                    }
                })
            },

            changeCheck() {
                if (this.checked == 1) {
                    this.checked = 0
                } else {
                    this.checked = 1
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-20] 店舗情報などと同じ決まり */
    .lk {
        min-height: 100vh;
        background: #F5F5F7;
        font-family: 'Hiragino Sans', sans-serif;
        color: #1D1D1F;
    }

    .lk-body {
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
    }

    .lk-info {
        display: flex;
        align-items: flex-start;
        padding: 24rpx 28rpx;
        border-radius: 24rpx;
        background: #EEF4FF;
        font-size: 28rpx;
        line-height: 1.6;
        margin-bottom: 32rpx;

        text {
            flex: 1;
        }
    }

    .lk-info-icon {
        width: 44rpx;
        height: 44rpx;
        margin: 2rpx 16rpx 0 0;
        flex-shrink: 0;
    }

    .lk-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 32rpx 32rpx 8rpx;
        margin-bottom: 40rpx;
    }

    .lk-label {
        font-size: 26rpx;
        font-weight: 500;
        margin-bottom: 12rpx;
    }

    .lk-in {
        width: 100%;
        box-sizing: border-box;
        height: 88rpx;
        padding: 0 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        background: #FFFFFF;
        font-size: 30rpx;
        color: #1D1D1F;
        margin-bottom: 24rpx;
        transition: border-color .15s, box-shadow .15s;
    }

    .lk-in.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    .lk-ph {
        color: #8A8A8E;
    }

    .lk-sw-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 128rpx;
        border-top: 1px solid #E5E5EA;
    }

    .lk-sw-text {
        flex: 1;
        min-width: 0;
        margin-right: 20rpx;
    }

    .lk-sw-name {
        display: block;
        font-size: 30rpx;
        font-weight: 500;
        color: #8A8A8E;
        margin-bottom: 4rpx;
    }

    .lk-sw-name.on {
        color: #1D1D1F;
    }

    .lk-sub {
        display: block;
        font-size: 24rpx;
        line-height: 1.6;
        color: #6E6E73;
    }

    .lk-save {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
    }

    .lk-save-hover {
        opacity: 0.8;
    }
</style>
