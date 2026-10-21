<template>
    <!-- [カスタムエイド 2026-10-20] カードを作る ②アイコン設定(作成と編集で同じ部品)。モック「管理App メンバーズカード作成 改善モック」
         ・店舗名(アプリ名)と、カードの画像1枚(横長 5:3 の枠で、実際の見え方で表示)
         ・×で削除・「画像を変える」で差し替え・押すと拡大。ファイル形式から PDF を外し HEIC(iPhoneの写真)を追加
         ・次へは店舗名と画像がそろうまで押せず、下に理由を出す -->
    <view class="ic">
        <u-navbar title="アイコン設定" :placeholder="true" :safeAreaInsetTop="true" :border="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="left">
                <view class="ic-nav-btn" @click="goBack">
                    <u-icon name="arrow-left" size="20" color="#1D1D1F"></u-icon>
                </view>
            </view>
            <view class="u-nav-slot" slot="right">
                <image src="../../static/icons/preview.svg" style="width: 50upx;height: 50upx;" @click="showPopup = true"
                    mode=""></image>
            </view>
        </u-navbar>

        <!-- 進み具合(作るときだけ) -->
        <view class="ic-steps" v-if="mode == 'add'">
            <view class="ic-step done"><view class="bar"></view><text>1 種類</text></view>
            <view class="ic-step now"><view class="bar"></view><text>2 アイコン</text></view>
            <view class="ic-step"><view class="bar"></view><text>3 デザイン</text></view>
        </view>

        <view class="ic-body">
            <view class="ic-h3">カードアプリのアイコン</view>
            <view class="ic-card">
                <view class="ic-field">
                    <view class="ic-label"><text class="ic-req">※</text>店舗名(アプリ名)</view>
                    <input class="ic-in" :class="{ on: fk }" v-model="name" maxlength="-1" :cursor-spacing="120"
                        placeholder-class="ic-ph" placeholder="例:CARD SALON" @focus="fk = true" @blur="fk = false" />
                    <text class="ic-sub">お客様の My Card に、この名前で表示されます</text>
                </view>

                <view class="ic-field">
                    <view class="ic-row">
                        <text class="ic-label ic-nomb"><text class="ic-req">※</text>カードの画像</text>
                        <text class="ic-sub">1枚</text>
                    </view>
                    <view class="ic-img" v-if="img" @click="preview">
                        <image :src="img" mode="aspectFill"></image>
                        <view class="ic-x-btn" @click.stop="img = ''"><view class="ic-x-in"><view class="ic-x"></view></view></view>
                    </view>
                    <view class="ic-img ic-add" v-else hover-class="ic-hover" @click="choose">
                        <view class="ic-add-in">
                            <text class="ic-plus">+</text>
                            <text>画像を選ぶ</text>
                            <text class="ic-add-sub">カメラで撮影/写真から選ぶ</text>
                        </view>
                    </view>
                    <view class="ic-outline" v-if="img" hover-class="ic-hover" @click="choose">画像を変える</view>
                    <view class="ic-notes">
                        <text class="ic-sub">推奨サイズ:横 1316 × 縦 792 px(横長 5:3)</text>
                        <text class="ic-sub">ファイル形式:JPG・JPEG・GIF・HEIC(iPhoneで撮った写真)</text>
                    </view>
                </view>
            </view>

            <view class="ic-next" :class="{ off: !canNext }" hover-class="ic-next-hover" @click="next">次へ</view>
            <text class="ic-sub ic-center" v-if="!canNext">店舗名とカードの画像を入れると押せます</text>
        </view>

        <!-- プレビュー(今までと同じ内容) -->
        <u-popup :show="showPopup" round="32" @close="showPopup = false">
            <view class="ic-pv">
                <view class="pTitle">
                    <view class="closeImage">
                        <image src="../../static/icons/tclose.png" @click="showPopup = false" mode=""></image>
                    </view>
                    <view class="pt">プレビュー</view>
                </view>
                <view class="preTitle">
                    <view class="iconLeft"><image src="../../static/icons/menu1.png" mode=""></image></view>
                    <view class="iconMiddle">CARD BOX</view>
                    <view class="iconRight"><image src="../../static/icons/heart.png" mode=""></image></view>
                </view>
                <view class="preContent">
                    <view class="preItem">
                        <view class="preImage"><image style="width: 100%;height: 100%;" :src="img" mode="aspectFill"></image></view>
                        <view class="preName" v-text="name"></view>
                    </view>
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import pickImage from '@/utils/pickImage.js'
    import { getShopInfo } from '@/api/index.js'
    export default {
        props: {
            mode: { type: String, default: 'add' },
            type: { type: [String, Number], default: 1 },
            cardId: { type: [String, Number], default: '' },
            shopName: { type: String, default: '' },
            image: { type: String, default: '' }
        },
        data() {
            return {
                name: this.shopName,
                img: this.image,
                fk: false,
                showPopup: false
            }
        },
        created() {
            // 作るときは、お店の名前を最初から入れておく
            if (this.mode == 'add' && !this.name) {
                getShopInfo().then((res) => {
                    if (res.code == 200 && !this.name) this.name = (res.data.shop_info || {}).shop_name || ''
                }).catch(() => {})
            }
        },
        computed: {
            canNext() {
                return !!(String(this.name || '').trim() && this.img)
            }
        },
        methods: {
            goBack() {
                uni.navigateBack()
            },
            preview() {
                uni.previewImage({ urls: [this.img] })
            },
            choose() {
                pickImage({
                    count: 1,
                    sizeType: ['compressed'], // iPhone の HEIC はここで JPG になって届く
                    success: (res) => {
                        uni.showLoading({ title: 'アップロード中', mask: true })
                        uni.uploadFile({
                            url: this.$baseUrl + '/tool/upload/file',
                            filePath: res.tempFilePaths[0],
                            name: 'file',
                            success: (result) => {
                                uni.hideLoading()
                                try {
                                    const re = JSON.parse(result.data)
                                    if (re.code == 200) {
                                        this.img = re.data.path
                                        return
                                    }
                                } catch (e) {}
                                uni.showToast({ title: 'アップロードできませんでした', icon: 'none' })
                            },
                            fail: () => {
                                uni.hideLoading()
                                uni.showToast({ title: 'アップロードできませんでした', icon: 'none' })
                            }
                        })
                    }
                })
            },
            next() {
                if (!this.canNext) {
                    uni.showToast({ title: '店舗名とカードの画像を入れてください', icon: 'none' })
                    return
                }
                const q = 'shop_name=' + encodeURIComponent(this.name) + '&image=' + encodeURIComponent(this.img) + '&type=' + this.type
                if (this.mode == 'edit') {
                    uni.navigateTo({ url: '/pages/settings/membershipCard/editSecondStep/editSecondStep?' + q + '&id=' + this.cardId })
                } else {
                    uni.navigateTo({ url: '/pages/settings/membershipCard/secondStep/secondStep?' + q })
                }
            }
        }
    }
</script>

<style lang="scss">
    .ic {
        min-height: 100vh;
        background: #F5F5F7;
        font-family: 'Hiragino Sans', sans-serif;
        color: #1D1D1F;
    }

    .ic-nav-btn {
        width: 64rpx;
        height: 64rpx;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ic-steps {
        display: flex;
        background: #FFFFFF;
        padding: 24rpx 32rpx 28rpx;
        border-bottom: 1px solid #E5E5EA;
    }

    .ic-step {
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

    .ic-step:last-child {
        margin-right: 0;
    }

    .ic-step.done,
    .ic-step.now {
        color: #1A73E8;

        .bar {
            background: #1A73E8;
        }
    }

    .ic-step.now {
        font-weight: 700;
    }

    .ic-body {
        padding: 40rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
    }

    .ic-h3 {
        margin: 0 8rpx 16rpx;
        font-size: 30rpx;
        font-weight: 700;
        color: #515154;
    }

    .ic-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 32rpx;
        margin-bottom: 48rpx;
    }

    .ic-field {
        margin-bottom: 36rpx;
    }

    .ic-field:last-child {
        margin-bottom: 0;
    }

    .ic-label {
        display: block;
        font-size: 26rpx;
        font-weight: 500;
        margin-bottom: 12rpx;
    }

    .ic-nomb {
        margin-bottom: 0;
    }

    .ic-req {
        color: #C5221F;
    }

    .ic-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 16rpx;
    }

    .ic-in {
        width: 100%;
        box-sizing: border-box;
        height: 88rpx;
        padding: 0 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        background: #FFFFFF;
        font-size: 30rpx;
        margin-bottom: 8rpx;
        transition: border-color .15s, box-shadow .15s;
    }

    .ic-in.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    .ic-ph {
        color: #8A8A8E;
    }

    .ic-sub {
        display: block;
        font-size: 24rpx;
        line-height: 1.6;
        color: #6E6E73;
    }

    .ic-center {
        text-align: center;
    }

    .ic-img {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 60.18%;
        border-radius: 24rpx;
        overflow: hidden;
        background: #F5F5F7;
        box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.12);

        image {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
        }
    }

    .ic-add {
        box-shadow: none;
        border: 3rpx dashed #8AB0EE;
        background: #F7FAFF;
        box-sizing: border-box;
    }

    .ic-add-in {
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #1A73E8;
        font-size: 28rpx;
        font-weight: 500;
    }

    .ic-plus {
        font-size: 60rpx;
        line-height: 1;
        margin-bottom: 8rpx;
    }

    .ic-add-sub {
        margin-top: 8rpx;
        font-size: 24rpx;
        font-weight: 400;
        color: #515154;
    }

    .ic-hover {
        opacity: 0.6;
    }

    .ic-x-btn {
        position: absolute;
        top: 0;
        right: 0;
        width: 88rpx;
        height: 88rpx;
        display: flex;
        justify-content: flex-end;
        z-index: 2;
    }

    .ic-x-in {
        margin: 16rpx;
        width: 52rpx;
        height: 52rpx;
        border-radius: 26rpx;
        background: rgba(29, 29, 31, 0.72);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ic-x {
        position: relative;
        width: 22rpx;
        height: 22rpx;
    }

    .ic-x::before,
    .ic-x::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        width: 26rpx;
        height: 4rpx;
        margin: -2rpx 0 0 -13rpx;
        border-radius: 2rpx;
        background: #FFFFFF;
        transform: rotate(45deg);
    }

    .ic-x::after {
        transform: rotate(-45deg);
    }

    .ic-outline {
        height: 80rpx;
        line-height: 80rpx;
        margin-top: 20rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        text-align: center;
        color: #1A73E8;
        font-size: 28rpx;
        font-weight: 500;
    }

    .ic-notes {
        margin-top: 16rpx;
    }

    .ic-next {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
        margin-bottom: 16rpx;
    }

    .ic-next.off {
        background: #C6DAFC;
    }

    .ic-next-hover {
        opacity: 0.8;
    }

    /* プレビュー(今までと同じ見た目) */
    .ic-pv {
        .pTitle {
            position: relative;
            height: 111upx;
            background: #FBFBFD;
            border-radius: 32upx;

            .pt {
                width: 100%;
                text-align: center;
                font-size: 40upx;
                color: #1D1D1F;
                line-height: 111upx;
            }

            .closeImage {
                width: 60upx;
                height: 60upx;
                position: absolute;
                top: 26upx;
                left: 32upx;

                image {
                    width: 100%;
                    height: 100%;
                }
            }
        }

        .preTitle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2upx solid #d2d2d7;
            border-top: 2upx solid #d2d2d7;
            box-sizing: border-box;
            padding: 32upx;

            .iconLeft {
                width: 66upx;
                height: 48upx;
            }

            .iconRight {
                width: 66upx;
                height: 60upx;
            }

            image {
                width: 100%;
                height: 100%;
            }

            .iconMiddle {
                font-size: 48upx;
                color: rgba(29, 29, 31, 0.9);
            }
        }

        .preContent {
            box-sizing: border-box;
            padding: 80upx 66upx;
            height: 60vh;

            .preItem {
                width: 292upx;

                .preImage {
                    width: 280upx;
                    height: 170upx;
                    background: #FBFBFD;
                    box-shadow: 0px 0px 6upx 2upx rgba(0, 0, 0, 0.16);
                    overflow: hidden;
                }

                .preName {
                    margin-top: 10upx;
                    font-size: 24upx;
                    color: #1D1D1F;
                    text-align: center;
                }
            }
        }
    }
</style>
