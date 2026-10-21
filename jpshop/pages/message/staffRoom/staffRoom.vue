<template>
    <view class="container cs-pg">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="スタッフルーム設定" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-21] ルームの写真を丸型に(メッセージアカウント情報と同じ形)。右下のカメラを押すと写真を変えられる。ほかもこれまでの画面に合わせた -->
        <view class="cs-av-wrap" @click="uploadImage">
            <image class="cs-av" :src="avatar || '/static/icons/staffroom_setting.svg'" mode="aspectFill"></image>
            <image class="cs-cam" src="../../../static/svg/cs_camera.svg" mode="aspectFit"></image>
        </view>
        <view class="cs-card">
            <view class="cs-fld">
                <view class="cs-lab">ルーム名</view>
                <input class="cs-in" :class="{ on: fk }" v-model="name" placeholder="スタッフルーム名を入力" placeholder-class="cs-ph"
                    @focus="fk = true" @blur="fk = false" />
            </view>
        </view>
        <view class="cs-card">
            <view class="cs-row">
                <text class="cs-row-text">スタッフルームを使う</text>
                <u-switch v-model="isOn" activeColor="#33C75A"></u-switch>
            </view>
        </view>
        <view class="cs-gray">
            <view class="cs-gray-t">スタッフルームについて</view>
            <view>・スタッフのみが利用できる業務連絡用のグループです。</view>
            <view>・プライベートな会話や業務外の話題は禁止しています。</view>
            <view>・1対1の個人チャットにも対応(選択可)。</view>
        </view>
        <view class="cs-save" hover-class="cs-hover" @click="save">保存する</view>
    </view>
</template>

<script>
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
    import { getStaffRoom, updateStaffRoomSettings } from '@/api/index.js'
    export default {
        data() {
            return {
                name: '',
                avatar: '',
                isOn: false,
                fk: false // [カスタムエイド 2026-10-21] 入力中か
            }
        },
        onLoad() {
            this.load()
        },
        methods: {
            load() {
                getStaffRoom().then(res => {
                    if (res.code == 200) {
                        this.name   = res.data.room.name
                        this.avatar = res.data.room.avatar
                        this.isOn   = res.data.room.is_on == 1
                    }
                }).catch(() => {})
            },
            uploadImage() {
                let that = this
                pickImage({
                    count: 1,
                    success(res) {
                        uni.uploadFile({
                            url: that.$baseUrl + '/tool/upload/file',
                            filePath: res.tempFilePaths[0],
                            name: 'file',
                            success(result) {
                                if (result.errMsg == 'uploadFile:ok') {
                                    let re = JSON.parse(result.data)
                                    if (re.code == 200) that.avatar = re.data.path
                                }
                            }
                        })
                    }
                })
            },
            save() {
                updateStaffRoomSettings({
                    name:   this.name,
                    avatar: this.avatar,
                    is_on:  this.isOn ? 1 : 0,
                }).then(res => {
                    uni.showToast({ title: res.code == 200 ? '保存しました' : res.message, icon: res.code == 200 ? 'success' : 'none' })
                }).catch(() => {})
            }
        }
    }
</script>

<style lang="scss">
.container {
    padding: 60rpx 40rpx;
    border-top: 2upx solid #d2d2d7;
}
.roomIcon {
    width: 160rpx;
    height: 160rpx;
    margin: 0 auto 60rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    image {
        width: 160rpx;
        height: 160rpx;
    }
}
.section {
    margin-bottom: 40rpx;
}
/* ルーム名入力欄とスタッフルーム項目の間に約50pxの余白 */
.roomNameSection {
    margin-bottom: 100rpx;
}
.switchRow {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.switchLabel {
    font-size: 30rpx;
    color: #1d1d1f;
    font-weight: bold;
}
.label {
    font-size: 28rpx;
    color: #86868B;
    margin-bottom: 16rpx;
}
/* 説明文（上部に区切り線 D2D2D7） */
.desc {
    margin-top: 30rpx;
    padding-top: 30rpx;
    border-top: 2upx solid #D2D2D7;
    .descTitle {
        font-size: 28rpx;
        color: #1d1d1f;
        font-weight: bold;
        margin-bottom: 16rpx;
    }
    .descItem {
        font-size: 26rpx;
        color: #86868B;
        line-height: 40rpx;
    }
}
.btn {
    width: 100%;
    height: 96upx;
    background: #1A73E8;
    border-radius: 20upx;
    /* 説明文と保存ボタンの間に約50pxの余白 */
    margin-top: 100rpx;
    font-size: 32upx;
    color: #fff;
}

    /* [カスタムエイド 2026-10-21] これまでの画面と同じ見た目 */
    .container.cs-pg {
        min-height: 100vh;
        background: #F5F5F7;
        padding: 0 24rpx calc(60rpx + env(safe-area-inset-bottom));
        box-sizing: border-box;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .cs-av-wrap {
        position: relative;
        width: 208rpx;
        height: 208rpx;
        margin: 40rpx auto 40rpx;
    }

    .cs-av {
        width: 208rpx;
        height: 208rpx;
        border-radius: 50%;
        background: #E5E5EA;
    }

    /* 右下のカメラ(押すと写真を変える) */
    .cs-cam {
        position: absolute;
        right: -4rpx;
        bottom: -4rpx;
        width: 64rpx;
        height: 64rpx;
        border-radius: 50%;
        background: #FFFFFF;
        box-shadow: 0 0 0 6rpx #F5F5F7;
    }

    .cs-h3 {
        margin: 0 8rpx 16rpx;
        font-size: 28rpx;
        font-family: 'Hiragino Sans-W5', sans-serif;
        color: #515154;
    }

    .cs-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 0 32rpx;
        margin-bottom: 40rpx;
    }

    .cs-fld {
        padding: 32rpx 0;
    }

    .cs-lab {
        font-size: 26rpx;
        font-weight: 500;
        margin-bottom: 12rpx;
    }

    .cs-in {
        width: 100%;
        height: 88rpx;
        padding: 0 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        font-size: 30rpx;
        box-sizing: border-box;
    }

    .cs-in.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    .cs-ph {
        color: #8A8A8E;
    }

    .cs-note {
        margin-top: 12rpx;
        font-size: 22rpx;
        line-height: 1.6;
        color: #6E6E73;
    }

    .cs-row {
        display: flex;
        align-items: center;
        min-height: 104rpx;
        padding: 16rpx 0;
        border-bottom: 1px solid #E5E5EA;
        box-sizing: border-box;
    }

    .cs-row:last-child {
        border-bottom: 0;
    }

    .cs-row-text {
        flex: 1;
        min-width: 0;
        margin-right: 20rpx;
        font-size: 30rpx;
    }

    .cs-gray {
        padding: 24rpx 28rpx;
        margin-bottom: 40rpx;
        border-radius: 24rpx;
        background: #EBEBEF;
        font-size: 24rpx;
        line-height: 1.8;
        color: #515154;
    }

    .cs-gray-t {
        font-family: 'Hiragino Sans-W5', sans-serif;
        color: #1D1D1F;
        margin-bottom: 4rpx;
    }

    .cs-save {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-family: 'Hiragino Sans-W5', sans-serif;
    }

    .cs-hover {
        opacity: 0.8;
    }
</style>
