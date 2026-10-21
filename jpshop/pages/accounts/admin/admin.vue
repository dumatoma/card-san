<template>
    <view class="border_top">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="オーナー情報" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-20] 見た目をこれまでの画面(白い角丸のまとまり・項目名は灰色・値は黒)と管理WEBのオーナー情報に合わせた -->
        <view class="ow">
            <view class="ow-card">
                <view class="ow-id">
                    <view class="ow-id-left">
                        <view class="ow-cap">ID(登録メールアドレス)</view>
                        <view class="ow-mail">{{eMail}}</view>
                    </view>
                    <view class="ow-pill" hover-class="ow-hover" @click="changeMail">変更</view>
                </view>
                <view class="ow-row ow-row-top">
                    <view class="ow-cap">契約番号</view>
                    <view class="ow-num">{{qyNum || '-'}}</view>
                </view>
            </view>

            <view class="ow-card">
                <view class="ow-item">
                    <view class="ow-label">氏名</view>
                    <view class="ow-val">{{name || '-'}}</view>
                </view>
                <view class="ow-item">
                    <view class="ow-label">法人名</view>
                    <view class="ow-val" :class="{ empty: !frname }">{{frname || '未登録'}}</view>
                </view>
                <view class="ow-item">
                    <view class="ow-label">店舗名</view>
                    <view class="ow-val">{{shopName || '-'}}</view>
                </view>
                <view class="ow-item">
                    <view class="ow-label">電話番号</view>
                    <view class="ow-val">{{phone || '-'}}</view>
                </view>
                <view class="ow-item">
                    <view class="ow-label">郵便番号</view>
                    <view class="ow-val">{{zip || '-'}}</view>
                </view>
                <view class="ow-item">
                    <view class="ow-label">住所</view>
                    <view class="ow-val">
                        <view>{{province}}</view>
                        <view>{{address1}}</view>
                        <view v-if="address2">{{address2}}</view>
                    </view>
                </view>
            </view>

            <view class="ow-btn" hover-class="ow-btn-hover" @click="toEdit">編集する</view>
        </view>
    </view>
</template>

<script>
    import {
        getOwnerInformation,getShopInfo
    } from "@/api/index.js"
    export default {
        data() {
            return {
                eMail: "example@mail.jp",
                qyNum:"",
                name:"",
                frname:"",
                shopName:"",
                phone:"",
                zip:"",
                address1:"",
                address2:"",
                province:""
            };
        },
        created() {
            let htat = this
            htat.getOwnerInformation()
            getShopInfo().then((res) => {
                if(res.code == 200){
                   
                }
            })
        },
        methods: {
            toEdit() {
                uni.navigateTo({
                    url: "../../accounts/admin/changeInfo"
                })
            },
            changeMail() {
                let that = this
                uni.navigateTo({
                    url: "../../accounts/admin/changeMail?mail="+that.eMail
                })
            },
            getOwnerInformation() {
                let that = this
                getOwnerInformation().then((res) => {
                    if(res.code == 200){
                        let info = uni.getStorageSync("admin")
                        that.name = res.data.owner.name
                        that.phone = res.data.owner.phone
                        that.frname = res.data.owner.legal_person
                        that.zip = res.data.owner.zip_code
                        that.address1 = res.data.owner.address1
                        that.address2 = res.data.owner.address2
                        that.eMail = info.email
                        that.shopName = res.data.owner.shop_name
                        that.province = res.data.owner.province
                        that.qyNum = res.data.owner.number
                    }
                })
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-20] これまでの画面と同じ見た目 */
    .ow {
        min-height: 100vh;
        background: #F5F5F7;
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
        box-sizing: border-box;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .ow-hover {
        opacity: 0.6;
    }

    .ow-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 8rpx 32rpx;
        margin-bottom: 32rpx;
    }

    .ow-id {
        display: flex;
        align-items: center;
        padding: 24rpx 0;
    }

    .ow-id-left {
        flex: 1;
        min-width: 0;
        margin-right: 20rpx;
    }

    .ow-cap {
        font-size: 24rpx;
        color: #6E6E73;
        margin-bottom: 6rpx;
    }

    .ow-mail {
        font-size: 30rpx;
        font-weight: 500;
        word-break: break-all;
    }

    .ow-pill {
        flex-shrink: 0;
        height: 68rpx;
        line-height: 66rpx;
        padding: 0 28rpx;
        border: 1px solid #1A73E8;
        border-radius: 34rpx;
        color: #1A73E8;
        font-size: 26rpx;
        font-weight: 500;
        box-sizing: border-box;
    }

    .ow-row {
        padding: 24rpx 0;
    }

    .ow-row-top {
        border-top: 1px solid #E5E5EA;
    }

    .ow-num {
        font-size: 30rpx;
        font-weight: 500;
    }

    .ow-item {
        display: flex;
        align-items: flex-start;
        padding: 28rpx 0;
        border-bottom: 1px solid #E5E5EA;
    }

    .ow-item:last-child {
        border-bottom: 0;
    }

    .ow-label {
        width: 160rpx;
        flex-shrink: 0;
        font-size: 26rpx;
        line-height: 1.6;
        color: #6E6E73;
    }

    .ow-val {
        flex: 1;
        min-width: 0;
        font-size: 30rpx;
        line-height: 1.6;
        word-break: break-all;
    }

    .ow-val.empty {
        color: #AEAEB2;
    }

    .ow-btn {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
    }

    .ow-btn-hover {
        opacity: 0.8;
    }
</style>
