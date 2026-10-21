<template>
    <!-- [カスタムエイド 2026-10-20] 会員登録情報の見た目を店舗情報などと合わせた
         ・氏名(いつも必須)は黒い文字に。オンにした項目は文字を黒く、オフの項目は今までの薄い色
         ・見出しは共通の見出し(20px 太字)に。説明と項目を白いまとまりに -->
    <view class="vr">
        <u-navbar title="会員登録情報" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="vr-body">
            <view class="vr-info">
                <text class="vr-info-1">カード会員登録のときに、お客様に入力していただく項目です。</text>
                <text class="vr-info-2">入力を必須にする項目だけ、オンにしてください。</text>
            </view>
            <view class="vr-card">
                <view class="vr-row">
                    <view class="vr-name on"><text class="vr-req">※</text>氏名</view>
                    <text class="vr-badge">いつも必須</text>
                </view>
                <view class="vr-row">
                    <view class="vr-name" :class="{ on: value1 }">カナ</view>
                    <u-switch v-model="value1" inactiveColor="#E5E5EA" activeColor="#34C759" @change="handleChange('pronunciation')"></u-switch>
                </view>
                <view class="vr-row">
                    <view class="vr-name" :class="{ on: value2 }">電話番号</view>
                    <u-switch v-model="value2" inactiveColor="#E5E5EA" activeColor="#34C759" @change="handleChange('phone')"></u-switch>
                </view>
                <view class="vr-row">
                    <view class="vr-name" :class="{ on: value3 }">性別</view>
                    <u-switch v-model="value3" inactiveColor="#E5E5EA" activeColor="#34C759" @change="handleChange('sex')"></u-switch>
                </view>
                <view class="vr-row">
                    <view class="vr-name" :class="{ on: value4 }">生年月日</view>
                    <u-switch v-model="value4" inactiveColor="#E5E5EA" activeColor="#34C759" @change="handleChange('birthday')"></u-switch>
                </view>
                <view class="vr-row">
                    <view class="vr-name" :class="{ on: value5 }">住所</view>
                    <u-switch v-model="value5" inactiveColor="#E5E5EA" activeColor="#34C759" @change="handleChange('address')"></u-switch>
                </view>
            </view>
            <text class="vr-sub">オンにした項目は、お客様が登録するときに入力が必要になります。変えるとすぐに保存されます。</text>
        </view>
    </view>
</template>

<script>
    import {
        memberSettings,
        getShopInfo
    } from "@/api/index.js"
    export default {
        data() {
            return {
                value: false,
                value1: false,
                value2: false,
                value3: false,
                value4: false,
                value5: false,
            };
        },
        created() {
           let that = this 
           that.getInfo()
        },
        methods: {
            getInfo(){
                let that = this 
                getShopInfo().then((res) => {
                    if(res.code == 200){
                        let arr = res.data.shop_info.member_register_field
                        if(arr.indexOf("name") == -1){
                            that.value = false
                        }else{
                            that.value = true
                        }
                        
                        if(arr.indexOf("pronunciation") == -1){
                            that.value1 = false
                        }else{
                            that.value1 = true
                        }
                        
                        
                        if(arr.indexOf("phone") == -1){
                            that.value2 = false
                        }else{
                            that.value2 = true
                        }
                        
                        if(arr.indexOf("sex") == -1){
                            that.value3 = false
                        }else{
                            that.value3 = true
                        }
                        
                        if(arr.indexOf("birthday") == -1){
                            that.value4 = false
                        }else{
                            that.value4 = true
                        }
                        
                        if(arr.indexOf("address") == -1){
                            that.value5 = false
                        }else{
                            that.value5 = true
                        }
                        
                    }else{
                        uni.showToast({
                            title:res.message
                        })
                    }
                })
            },
            
            handleChange(e) {
                let that = this
                let data = {}
                data.type = "member"
                data.member_register_field = []
                if (that.value == true) {
                    data.member_register_field.push('name')
                }

                if (that.value1 == true) {
                    data.member_register_field.push('pronunciation')
                }

                if (that.value2 == true) {
                    data.member_register_field.push('phone')
                }

                if (that.value3 == true) {
                    data.member_register_field.push('sex')
                }

                if (that.value4 == true) {
                    data.member_register_field.push('birthday')
                }

                if (that.value5 == true) {
                    data.member_register_field.push('address')
                }
                
                memberSettings(data).then((res) => {
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
            }
        }
    }
</script>

<style lang="scss" scoped>
    .vr {
        min-height: 100vh;
        background: #F5F5F7;
        font-family: 'Hiragino Sans', sans-serif;
        color: #1D1D1F;
    }

    .vr-body {
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
    }

    .vr-info {
        background: #EEF4FF;
        border-radius: 24rpx;
        padding: 28rpx 32rpx;
        margin-bottom: 32rpx;
    }

    .vr-info-1 {
        display: block;
        font-size: 28rpx;
        font-weight: 700;
        line-height: 1.6;
        margin-bottom: 8rpx;
    }

    .vr-info-2 {
        display: block;
        font-size: 28rpx;
        font-weight: 700;
        line-height: 1.6;
        color: #1A73E8;
    }

    .vr-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 0 32rpx;
        margin-bottom: 20rpx;
    }

    .vr-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 108rpx;
        border-bottom: 1px solid #E5E5EA;
    }

    .vr-row:last-child {
        border-bottom: 0;
    }

    .vr-name {
        font-size: 30rpx;
        color: #AAAAAA;
    }

    .vr-name.on {
        color: #1D1D1F;
        font-weight: 500;
    }

    .vr-req {
        color: #D93025;
        margin-right: 4rpx;
    }

    .vr-badge {
        font-size: 22rpx;
        font-weight: 700;
        color: #515154;
        background: #F2F2F5;
        border-radius: 18rpx;
        padding: 4rpx 16rpx;
    }

    .vr-sub {
        display: block;
        padding: 0 8rpx;
        font-size: 24rpx;
        line-height: 1.6;
        color: #6E6E73;
    }
</style>
