<template>
    <view class="contain">
        <view class="setTitle">
            アカウント設定
        </view>
        <view class="settingContent">
            <view class="settingItem" @click="toUserInfo">
                <view class="icon">
                    <image src="../../../static/icons/buser.png" mode=""></image>
                </view>
                <view>
                    アカウント情報
                </view>
            </view>
            <view class="settingItem" @click="toSetReview">
                <view class="icon">
                    <image src="../../../static/icons/review.svg" mode=""></image>
                </view>
                <view>
                    レビューリンク
                </view>
            </view>
        </view>
        <view class="setTitle">
            メッセージテンプレート設定
        </view>
        <view class="settingContent">
            <view class="settingItem" @click="tomodule(1)">
                <view>
                    テンプレート①　＞
                </view>
            </view>
            <view class="settingItem" @click="tomodule(2)">
                <view>
                    テンプレート②　＞
                </view>
            </view>
        </view>
        <view class="setTitle">
            自動メッセージ設定
        </view>
        <view class="settingContent">
            <view class="item">
                <view class="itemLeft">
                    <view class="itemName" @click="toOne(1)">
                        予約確認　＞
                    </view>
                </view>
                <view class="itemRight">
                    <u-switch v-model="value1" activeColor="#33C75A" @change="changeValue1"></u-switch>
                </view>
            </view>
            <view class="item">
                <view class="itemLeft">
                    <view class="itemName" @click="toOne(2)">
                        予約リマインダー　＞
                    </view>
                </view>
                <view class="itemRight">
                    <u-switch v-model="value2" activeColor="#33C75A" @change="changeValue2"></u-switch>
                </view>
            </view>
            <view class="item">
                <view class="itemLeft">
                    <view class="itemName" @click="toOne(5)">
                        予約内容変更　＞
                    </view>
                </view>
                <view class="itemRight">
                    <u-switch v-model="value5" activeColor="#33C75A" @change="changeValue5"></u-switch>
                </view>
            </view>
            <view class="item">
                <view class="itemLeft">
                    <view class="itemName" @click="toOne(3)">
                        予約キャンセル　＞
                    </view>
                </view>
                <view class="itemRight">
                    <u-switch v-model="value3" activeColor="#33C75A" @change="changeValue3"></u-switch>
                </view>
            </view>
            <view class="item">
                <view class="itemLeft">
                    <view class="itemName" @click="toOne(4)">
                        サンキューメッセージ　＞
                    </view>
                </view>
                <view class="itemRight">
                    <u-switch v-model="value4" activeColor="#33C75A" @change="changeValue4"></u-switch>
                </view>
            </view>
        </view>
       <!-- <view class="setTitle">
            アカウント設定
        </view> -->
        <u-button @click="toDeset" class="btn">メッセージを配信</u-button>
        <view style="height: 116upx;"></view>
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
                value5: false
            }
        },
        created() {
            let that = this
            that.getInfo()
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
                })
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
                })
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
</style>
