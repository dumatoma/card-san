<template>
    <view class="container">
        <view class="memberItem" v-for="item in members" :key="item.id" @click="onMemberTap(item)">
            <view class="left">
                <image :src="item.avatar || defaultImage" class="avatar" mode="aspectFill"></image>
                <view class="info">
                    <view class="name">{{ item.name }}</view>
                    <view class="tag" v-if="item.admin_type == 1">主管理者</view>
                </view>
            </view>
            <view class="right">
                <view class="dmBtn" :class="{ disabled: !item.staff_room_dm || !myDm }" v-if="item.id != myId">
                    <text>1対1</text>
                </view>
            </view>
        </view>

        <!-- 1対1確認ダイアログ -->
        <view class="dialogMask" v-if="showDialog" @click.self="showDialog = false">
            <view class="dialog">
                <view class="dialogTitle">{{ dialogTarget.name }} さんへ1対1チャット</view>
                <view class="dialogSub">このメンバーと1対1チャットを開始しますか？</view>
                <view class="dialogBtns">
                    <view class="dialogBtn cancel" @click="showDialog = false">キャンセル</view>
                    <view class="dialogBtn confirm" @click="goToDm">チャットする</view>
                </view>
            </view>
        </view>
    </view>
</template>

<script>
    import { getStaffRoom } from '@/api/index.js'
    export default {
        data() {
            return {
                members: [],
                myId: 0,
                myDm: 1,
                showDialog: false,
                dialogTarget: {},
                defaultImage: '../../../static/images/avatar.png',
            }
        },
        onLoad() {
            let admin = uni.getStorageSync('admin')
            this.myId = admin.id
            this.load()
        },
        methods: {
            load() {
                getStaffRoom().then(res => {
                    if (res.code == 200) {
                        this.members = res.data.members
                        this.myDm    = res.data.my_dm
                    }
                }).catch(() => {})
            },
            onMemberTap(item) {
                if (item.id == this.myId) return
                if (!item.staff_room_dm || !this.myDm) {
                    uni.showToast({ title: '1対1チャットが許可されていません', icon: 'none' })
                    return
                }
                this.dialogTarget = item
                this.showDialog   = true
            },
            goToDm() {
                this.showDialog = false
                uni.navigateTo({
                    url: './dm?to_id=' + this.dialogTarget.id + '&name=' + encodeURIComponent(this.dialogTarget.name)
                })
            }
        }
    }
</script>

<style lang="scss">
.container {
    background: #f9f9f9;
    min-height: 100vh;
    padding-top: 20rpx;
}
.memberItem {
    display: flex; align-items: center;
    justify-content: space-between;
    background: #fff;
    padding: 30rpx 40rpx;
    border-bottom: 2rpx solid #f0f0f0;
}
.left { display: flex; align-items: center; }
.avatar { width: 90rpx; height: 90rpx; border-radius: 50%; margin-right: 24rpx; }
.name { font-size: 30rpx; color: #1D1D1F; font-weight: bold; margin-bottom: 6rpx; }
.tag { font-size: 22rpx; color: #007AFF; }
.dmBtn {
    background: #007AFF; color: #fff;
    font-size: 24rpx; padding: 12rpx 28rpx;
    border-radius: 30rpx;
    &.disabled { background: #ccc; }
}
.dialogMask {
    position: fixed; top: 0; left: 0;
    width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.4);
    display: flex; align-items: center; justify-content: center;
    z-index: 999;
}
.dialog {
    background: #fff; border-radius: 20rpx;
    padding: 50rpx 40rpx; width: 600rpx;
}
.dialogTitle { font-size: 32rpx; font-weight: bold; color: #1D1D1F; margin-bottom: 16rpx; }
.dialogSub { font-size: 28rpx; color: #86868B; margin-bottom: 50rpx; }
.dialogBtns { display: flex; justify-content: space-between; }
.dialogBtn {
    flex: 1; text-align: center; padding: 24rpx 0;
    border-radius: 12rpx; font-size: 30rpx;
    &.cancel { background: #f0f0f0; color: #333; margin-right: 20rpx; }
    &.confirm { background: #007AFF; color: #fff; }
}
</style>
