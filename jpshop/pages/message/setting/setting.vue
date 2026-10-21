<template>
    <view class="contain">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="メッセージ設定" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-21] 見た目をこれまでの画面に合わせた(白い角丸のまとまり、アイコンは今までのもの、「>」は他の画面と同じ矢印)。
             「メッセージを配信」のボタンは、メッセージの一覧のいちばん上に移した -->
        <view class="st">
            <view class="st-h3">アカウント設定</view>
            <view class="st-card">
                <view class="st-row" hover-class="st-row-hover" @click="toUserInfo">
                    <image class="st-ic" src="../../../static/icons/buser.png" mode="aspectFit"></image>
                    <text class="st-name">アカウント情報</text>
                    <view class="st-chev st-right"></view>
                </view>
                <view class="st-row" hover-class="st-row-hover" @click="toSetReview">
                    <image class="st-ic" src="../../../static/icons/review.svg" mode="aspectFit"></image>
                    <text class="st-name">レビューリンク</text>
                    <view class="st-chev st-right"></view>
                </view>
                <view class="st-row" hover-class="st-row-hover" @click="toStaffRoom" v-if="isOwner">
                    <image class="st-ic" src="../../../static/icons/staffroom.svg" mode="aspectFit"></image>
                    <text class="st-name">スタッフルーム設定</text>
                    <view class="st-chev st-right"></view>
                </view>
            </view>

            <view class="st-h3">メッセージテンプレート設定</view>
            <view class="st-card">
                <view class="st-row" hover-class="st-row-hover" @click="tomodule(1)">
                    <text class="st-name">テンプレート①</text>
                    <view class="st-chev st-right"></view>
                </view>
                <view class="st-row" hover-class="st-row-hover" @click="tomodule(2)">
                    <text class="st-name">テンプレート②</text>
                    <view class="st-chev st-right"></view>
                </view>
            </view>

            <view class="st-h3">自動メッセージ設定</view>
            <view class="st-card">
                <view class="st-row">
                    <view class="st-name st-tap" hover-class="st-hover" @click="toOne(1)"><text>予約確認</text><view class="st-chev"></view></view>
                    <u-switch v-model="value1" activeColor="#33C75A" @change="changeValue1"></u-switch>
                </view>
                <view class="st-row">
                    <view class="st-name st-tap" hover-class="st-hover" @click="toOne(2)"><text>予約リマインダー</text><view class="st-chev"></view></view>
                    <u-switch v-model="value2" activeColor="#33C75A" @change="changeValue2"></u-switch>
                </view>
                <view class="st-row">
                    <view class="st-name st-tap" hover-class="st-hover" @click="toOne(5)"><text>予約内容変更</text><view class="st-chev"></view></view>
                    <u-switch v-model="value5" activeColor="#33C75A" @change="changeValue5"></u-switch>
                </view>
                <view class="st-row">
                    <view class="st-name st-tap" hover-class="st-hover" @click="toOne(3)"><text>予約キャンセル</text><view class="st-chev"></view></view>
                    <u-switch v-model="value3" activeColor="#33C75A" @change="changeValue3"></u-switch>
                </view>
                <view class="st-row">
                    <view class="st-name st-tap" hover-class="st-hover" @click="toOne(4)"><text>サンキューメッセージ</text><view class="st-chev"></view></view>
                    <u-switch v-model="value4" activeColor="#33C75A" @change="changeValue4"></u-switch>
                </view>
            </view>
        </view>
    </view>
</template>

<script>
    import {
        getShopInfo,
        changeChatInfo
    } from '@/api/index.js'
    export default {
        data() {
            return {
                value1: false,
                value2: false,
                value3: false,
                value4: false,
                value5: false,
                isOwner: false,
            }
        },
        created() {
            let admin = uni.getStorageSync('admin')
            this.isOwner = admin.admin_type == 1
            this.getInfo()
        },
        methods: {
            tomodule(e){
                let that = this 
                uni.navigateTo({
                    url:"../moduleSettin/moduleSettin?type="+e
                })
            },
            toSetReview(){
                uni.navigateTo({
                    url:"/pages/message/reviewSetting/reviewSetting"
                })
            },
            toUserInfo() {
                uni.navigateTo({
                    url: "../userInfo/userInfo"
                })
            },
            toStaffRoom() {
                uni.navigateTo({ url: "../staffRoom/staffRoom" })
            },
            toGBPSetting() {
                uni.navigateTo({ url: '/pages/settings/gbpSetting/gbpSetting' })
            },
            toSocialMedia() {
                uni.navigateTo({ url: '/pages/settings/socialMedia/socialMedia' })
            },
            
            toDeset(){
              uni.navigateTo({
                  url:"../notice/notice"
              })  
            },

            getInfo() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        that.value1 = res.data.shop_info.order_confirm_on == 1 ? true : false
                        that.value2 = res.data.shop_info.order_notice_on == 1 ? true : false
                        that.value3 = res.data.shop_info.order_cancel_on == 1 ? true : false
                        that.value4 = res.data.shop_info.thanks_on == 1 ? true : false
                        that.value5 = res.data.shop_info.order_update_on == 1 ? true : false
                    }
                }).catch(() => {})
            },

            changeValue1(e) {
                this.setSwitch('order_confirm_on')
            },

            changeValue2(e) {
                this.setSwitch('order_notice_on')
            },

            changeValue3(e) {
                this.setSwitch('order_cancel_on')
            },

            changeValue4(e) {
                this.setSwitch('thanks_on')
            },
            changeValue5(e) {
                this.setSwitch('order_update_on')
            },
            setSwitch(e) {
                let that = this
                let data = {}
                data.type = "message_on"
                data.field = e
                data.order_confirm_on = that.value1 == true? 1 : 0
                data.order_notice_on = that.value2 == true? 1 : 0
                data.order_cancel_on = that.value3 == true? 1 : 0
                data.thanks_on = that.value4 == true? 1 : 0
                data.order_update_on = that.value5 == true? 1 : 0
                changeChatInfo(data).then((res) => {
                    if(res.code == 200){
                        uni.showToast({
                            title:res.message
                        })
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                }).catch(() => {})
            },

            toOne(e) {
                uni.navigateTo({
                    url: "../userInfo/edit/edit?type="+e
                })
            }
        }
    }
</script>

<style lang="scss">
    .contain {
        border-top: 2upx solid #d2d2d7;

        .btn {
            width: 480upx;
            height: 96upx;
            background: #34C759;
            border-radius: 20upx;
            opacity: 1;
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            margin-top: 92upx;
        }
    }

    .setTitle {
        box-sizing: border-box;
        padding: 120upx 0 20upx 30upx;
        border-bottom: 2upx solid #d2d2d7;
        font-weight: bold;
        font-size: 32rpx;
        color: #1D1D1F;
    }

    .settingContent {
        .settingItem {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 30upx;
            border-bottom: 2upx solid #d2d2d7;
            font-size: 32rpx;
            color: #1D1D1F;
            .icon {
                width: 60upx;
                height: 60upx;
                margin-right: 20rpx;
                image {
                   width: 60upx;
                   height: 60upx;
                }
            }
        }

        .item {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 30upx;
            border-bottom: 2upx solid #d2d2d7;
            justify-content: space-between;
            font-size: 16px;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: rgba(29, 29, 31, 0.9);
        }
    }

    /* [カスタムエイド 2026-10-21] これまでの画面と同じ見た目 */
    .st {
        min-height: 100vh;
        background: #F5F5F7;
        padding: 32rpx 24rpx calc(60rpx + env(safe-area-inset-bottom));
        box-sizing: border-box;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .st-h3 {
        margin: 0 8rpx 16rpx;
        font-size: 28rpx;
        font-family: 'Hiragino Sans-W5', sans-serif;
        color: #515154;
    }

    .st-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        overflow: hidden;
        margin-bottom: 40rpx;
    }

    .st-row {
        display: flex;
        align-items: center;
        min-height: 104rpx;
        padding: 0 32rpx;
        border-bottom: 1px solid #E5E5EA;
    }

    .st-row:last-child {
        border-bottom: 0;
    }

    .st-row-hover {
        background: #F5F5F7;
    }

    .st-ic {
        width: 48rpx;
        height: 48rpx;
        flex-shrink: 0;
        margin-right: 24rpx;
    }

    .st-name {
        font-size: 30rpx;
    }

    .st-tap {
        flex: 1;
        display: flex;
        align-items: center;
        min-height: 104rpx;
    }

    .st-hover {
        opacity: 0.6;
    }

    /* 「>」(他の画面と同じ右向きの矢印) */
    .st-chev {
        width: 14rpx;
        height: 14rpx;
        flex-shrink: 0;
        margin-left: 16rpx;
        border-right: 3rpx solid #C7C7CC;
        border-bottom: 3rpx solid #C7C7CC;
        transform: rotate(-45deg);
    }

    .st-right {
        margin-left: auto;
    }
</style>
