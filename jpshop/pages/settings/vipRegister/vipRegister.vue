<template>
    <view>
        <view class="notices">
            <view class="n1">
                カード会員登録の際に、お客さんに入力していただく情報です。
            </view>
            <view class="n2">
                必須項目のみチェックを入れてください。
            </view>
        </view>

        <view class="settingContent">
            <view class="settingItem">
                <view class="settingName" style="display: flex;align-items: center;">
                   <text style="color: red;font-size: 30rpx;">※</text> <text>氏名</text> 
                </view>
               <!-- <u-switch v-model="value" inactiveColor="#EAEAEA" :disabled="true" activeColor="#34C759" @change="handleChange('name')">
                </u-switch> -->
            </view>
            <view class="settingItem">
                <view class="settingName">
                    カナ
                </view>
                <u-switch v-model="value1" inactiveColor="#EAEAEA" activeColor="#34C759"
                    @change="handleChange('pronunciation')"></u-switch>
            </view>
            <view class="settingItem">
                <view class="settingName">
                    電話番号
                </view>
                <u-switch v-model="value2" inactiveColor="#EAEAEA" activeColor="#34C759"
                    @change="handleChange('phone')"></u-switch>
            </view>
            <view class="settingItem">
                <view class="settingName">
                    性別
                </view>
                <u-switch v-model="value3" inactiveColor="#EAEAEA" activeColor="#34C759" @change="handleChange('sex')">
                </u-switch>
            </view>
            <view class="settingItem">
                <view class="settingName">
                    生年月日
                </view>
                <u-switch v-model="value4" inactiveColor="#EAEAEA" activeColor="#34C759"
                    @change="handleChange('birthday')"></u-switch>
            </view>
            <view class="settingItem">
                <view class="settingName">
                    住所
                </view>
                <u-switch v-model="value5" inactiveColor="#EAEAEA" activeColor="#34C759"
                    @change="handleChange('address')"></u-switch>
            </view>
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

<style lang="scss">
    .settingItem {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 30upx 40upx;
        border-bottom: 2upx solid #d2d2d7;
        color: #AAA;
    }

    .notices {
        width: 100%;
        background: #F5F5F7;
        box-sizing: border-box;
        padding: 80upx 70upx;
        border-top: 2upx solid #d2d2d7;
        border-bottom: 2upx solid #d2d2d7;

        .n1 {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-bottom: 40upx;
        }

        .n2 {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
        }
    }
</style>
