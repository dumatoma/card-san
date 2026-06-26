<template>
    <view class="container">
        <view class="avatarWrap" @click="uploadImage">
            <u-avatar :src="avatar" size="100" mode="aspectFill"></u-avatar>
            <view class="editBadge">編集</view>
        </view>
        <view class="section">
            <view class="label">ルーム名</view>
            <u-input v-model="name" placeholder="スタッフルーム名を入力"></u-input>
        </view>
        <view class="section switchRow">
            <view class="label">スタッフルームON/OFF</view>
            <u-switch v-model="isOn" activeColor="#33C75A"></u-switch>
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
.avatarWrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 60rpx;
    position: relative;
    width: 200rpx;
    margin: 0 auto 60rpx;
}
.editBadge {
    font-size: 22rpx;
    color: #007AFF;
    margin-top: 12rpx;
}
.section {
    margin-bottom: 50rpx;
}
.switchRow {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.label {
    font-size: 28rpx;
    color: #86868B;
    margin-bottom: 16rpx;
}
.btn {
    width: 100%;
    height: 96upx;
    background: #34C759;
    border-radius: 20upx;
    margin-top: 60rpx;
    font-size: 32upx;
    color: #fff;
}
</style>
