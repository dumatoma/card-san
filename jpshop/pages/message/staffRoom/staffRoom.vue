<template>
    <view class="container">
        <view class="roomIcon" @click="uploadImage">
            <image :src="avatar || '/static/icons/staffroom_setting.svg'" mode="aspectFit"></image>
        </view>
        <view class="section roomNameSection">
            <view class="label">ルーム名</view>
            <u-input v-model="name" placeholder="スタッフルーム名を入力"></u-input>
        </view>
        <view class="switchRow">
            <view class="switchLabel">スタッフルーム</view>
            <u-switch v-model="isOn" activeColor="#33C75A"></u-switch>
        </view>
        <view class="desc">
            <view class="descTitle">スタッフルームについて</view>
            <view class="descItem">・スタッフのみが利用できる業務連絡用のグループです。</view>
            <view class="descItem">・プライベートな会話や業務外の話題は禁止しています。</view>
            <view class="descItem">・1対1の個人チャットにも対応（選択可）。</view>
        </view>
        <u-button class="btn" type="primary" @click="save">保存</u-button>
    </view>
</template>

<script>
    import { getStaffRoom, updateStaffRoomSettings } from '@/api/index.js'
    export default {
        data() {
            return {
                name: '',
                avatar: '',
                isOn: false,
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
                uni.chooseImage({
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
</style>
