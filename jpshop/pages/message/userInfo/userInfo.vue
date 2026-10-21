<template>
    <view class="container cs-pg">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="メッセージアカウント情報" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-21] 見た目をこれまでの画面に合わせた。丸い写真の右下のカメラを押すと写真を変えられる -->
        <view class="cs-av-wrap" @click="uploadImage">
            <image class="cs-av" :src="avatar || '../../../static/images/avatar.png'" mode="aspectFill"></image>
            <image class="cs-cam" src="../../../static/svg/cs_camera.svg" mode="aspectFit"></image>
        </view>
        <view class="cs-card">
            <view class="cs-fld">
                <view class="cs-lab">ユーザー名</view>
                <input class="cs-in" :class="{ on: fk }" v-model="name" placeholder="担当者を入力してください" placeholder-class="cs-ph"
                    @focus="fk = true" @blur="fk = false" />
                <view class="cs-note">お客様とのメッセージに表示される名前です</view>
            </view>
        </view>
        <view class="cs-h3">スタッフルーム</view>
        <view class="cs-card">
            <view class="cs-row">
                <text class="cs-row-text">参加する</text>
                <u-switch v-model="staffRoomJoin" activeColor="#33C75A" @change="saveMemberFlags"></u-switch>
            </view>
            <view class="cs-row">
                <text class="cs-row-text">1対1チャットを許可</text>
                <u-switch v-model="staffRoomDm" activeColor="#33C75A" @change="saveMemberFlags"></u-switch>
            </view>
        </view>
        <view class="cs-save" hover-class="cs-hover" @click="save">保存する</view>
    </view>
</template>
<script>
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
    import {
        getShopInfo,
        changeChatInfo,
        getStaffRoom,
        updateStaffRoomMember
    } from "@/api/index.js"
    export default {
        data() {
            return {
                name: "",
                show: false,
                info:{},
                avatar:"",
                staffRoomJoin: true,
                staffRoomDm: true,
                fk: false // [カスタムエイド 2026-10-21] 入力中か
            }
        },
        created() {
           let that = this
           let userInfo = uni.getStorageSync("admin")
           that.name = userInfo.message_name == ''?userInfo.name : userInfo.message_name
           that.avatar = userInfo.avatar
           that.getUserInfo()
           that.loadStaffRoomFlags()
        },
        onShow() {
            
        },
        methods: {
            uploadImage(source) {
                let that = this
                let type = []
                type.push(source)
                pickImage({
                    count: 1,
                    success: (res) => {
                        const tempPaths = res.tempFilePaths
                        for (let i = 0; i < tempPaths.length; i++) {
                            uni.uploadFile({
                                url: that.$baseUrl+'/tool/upload/file',
                                filePath: res.tempFilePaths[i],
                                name:"file",
                                success: (result) => {
                                    if (result.errMsg == "uploadFile:ok") {
                                        let re = JSON.parse(result.data)
                                        if (re.code == 200) {
                                            that.getValue(re.data.path) 
                                        }
                                    }
                                }
                            })
                        }
                    }
                });  
            },
            toPreview() {
                let that = this
                uni.navigateTo({
                    url: "preview/preview?url=" + that.info.avatar + "&name="+that.name
                })
            },
            getValue(e){
                let that = this
                if(e){
                    that.avatar = e
                }
            },
            getUserInfo() {
                let that = this
                getShopInfo().then((res) => {
                    if(res.code == 200){
                        that.info = res.data.shop_info
                    }
                }).catch(() => {})
            },
            loadStaffRoomFlags() {
                getStaffRoom().then(res => {
                    if (res.code == 200) {
                        this.staffRoomJoin = res.data.my_join == 1
                        this.staffRoomDm   = res.data.my_dm == 1
                    }
                }).catch(() => {})
            },
            saveMemberFlags() {
                updateStaffRoomMember({
                    staff_room_join: this.staffRoomJoin ? 1 : 0,
                    staff_room_dm:   this.staffRoomDm   ? 1 : 0,
                }).catch(() => {})
            },
            save(){
                let that = this
                let data = {}
                data.type = "info"
                data.name = that.name
                data.avatar = that.avatar
               changeChatInfo(data).then((res) => {
                   if(res.code == 200){
                       uni.showToast({
                           title:res.message,
                           duration:2000
                       })
                       let userInfo = uni.getStorageSync("admin")
                       userInfo.message_name = that.name
                       userInfo.avatar = that.avatar
                       uni.setStorageSync("admin",userInfo)
                       setTimeout(() => {
                           uni.navigateBack()
                       },2000)
                   }
               }).catch(() => {})
            }
        }
    }
</script>

<style lang="scss">
    .container {
        border-top: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding-top: 174upx;

        .avatar {
            margin: 0 auto;
            text-align: center;
            width: 240upx;
            height: 240upx;
        }

        .shopName {
            font-size: 32upx;
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            margin-top: 60upx;
        }

        .inpContent {
            margin-top: 100upx;

            .inpTitle {
                margin-bottom: 10upx;
            }
        }

        .btn {
            width: 480upx;
            height: 96upx;
            margin-top: 100upx;
            background: #1A73E8;
            color: #fff;
            border-radius: 20upx;
        }
    }
    /* スタッフルーム項目の上下余白を広げる（Figma指定） */
    .sectionTitle {
        font-size: 28rpx;
        color: #86868B;
        font-weight: bold;
        margin-top: 50rpx;
        padding: 40rpx 0 20rpx;
        border-bottom: 2rpx solid #d2d2d7;
    }
    .switchItem {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 44rpx 0;
        border-bottom: 2rpx solid #d2d2d7;
    }
    .switchLabel {
        font-size: 30rpx;
        color: #1D1D1F;
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
