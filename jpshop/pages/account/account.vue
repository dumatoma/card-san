<template>
    <view>
        <u-navbar title="アカウント" :placeholder="true" :safeAreaInsetTop="true" leftIconColor="transparent" :border="true">
        </u-navbar>
        <view class="settingItem" @click="toNext(0)" v-if="admin_type == 1">
            <view class="itemLeft">
                <view class="itemName">
                    オーナー情報
                </view>
            </view>
        </view>
        
        <view class="settingItem" @click="toNext(1)" v-if="admin_type == 2">
            <view class="itemLeft">
                <view class="itemName">
                    副管理者情報
                </view>
            </view>
        </view>
        
       <!--  -->
        
        <view class="settingItem" @click="toNext(3)">
            <view class="itemLeft">
                <view class="itemName">
                    ご利用プラン
                </view>
            </view>
            <view class="itemRight"></view>
        </view>
        
        <!-- <view class="settingItem" @click="toNext(4)">
            <view class="itemLeft">
                <view class="itemName">
                    ご利用明細
                </view>
            </view>
        </view> -->
        
        <view class="settingItem border_b" @click="toNext(8)">
            <view class="itemLeft">
                <view class="itemName">
                    ご契約内容
                </view>
            </view>
        </view>
        
        <view style="background: #F5F5F7;height: 60upx;">
            
        </view>
        <view class="settingItem" @click="toNext(5)">
            <view class="itemLeft">
                <view class="itemName">
                    販促アイテム
                </view>
            </view>
        </view>
        <view class="settingItem border_b" style="display: flex;justify-content: space-between;" @click="toNext(6)">
            <view class="itemLeft">
                <view class="itemName">
                    運営からのお知らせ
                </view>
            </view>
            <view class="num" v-if="number > 0">
                1
            </view>
        </view>
        <view style="background: #F5F5F7;height: 60upx;">
            
        </view>
        <view class="settingItem" @click="toTakeoff">
            <view class="itemLeft">
                <view class="itemName">
                    アカウント削除
                </view>
            </view>
        </view>
        <view class="settingItem" @click="toNext(7)">
            <view class="itemLeft">
                <view class="itemName">
                    利用規約・プライバシーポリシー
                </view>
            </view>
        </view>
        <view class="settingItem border_b">
            <view class="itemLeft">
                <view class="itemName">
                    バージョン情報
                </view>
            </view>
            <view class="itemRight">version:{{version}}</view>
        </view>
        <view class="mask" v-if="show" @click="show = false">
            <view class="co">
                <image src="../../static/images/redright.png" mode=""></image>
                <view>実行する権限がありません</view>
            </view>
        </view>
    </view>
</template>

<script>
    import {getYY} from "@/api/index.js"
    export default {
        data() {
            return {
                number:0,
                admin_type:"",
                show:false,
                version:""
            }
        },
       onShow() {
           let that = this
           let admin = uni.getStorageSync("admin")
           that.admin_type = admin.admin_type
           let info = uni.getSystemInfoSync()
           that.version = info.appVersion + ' ' +info.appVersionCode
           getYY(0).then((res) => {
               if(res.code == 200){
                   that.number = res.data.unread_count
               }
           })
       },
        onLoad() {
           let that = this
           let admin = uni.getStorageSync("admin")
           that.admin_type = admin.admin_type
        },
       
        methods: {
            toTakeoff(){
                 uni.navigateTo({
                     url:"../deleteAccount/deleteAccount"
                 })
            },
            toNext(e){
                if(e == 0){
                    uni.navigateTo({
                        url:"../accounts/admin/admin"
                    })
                }else if(e == 1){
                    uni.navigateTo({
                        url:"../accounts/mannager/mannager"
                    })
                }else if(e == 2){
                    uni.navigateTo({
                        url:"../accounts/payment/payment"
                    })
                }else if(e == 3){
                    uni.navigateTo({
                        url:"../accounts/package/package"
                    })
                }else if(e == 4){
                    uni.navigateTo({
                        url:"../accounts/detailed/detailed"
                    })
                }else if(e == 5){
                    uni.navigateTo({
                        url:"../accounts/sale/sale"
                    })
                }else if(e == 6){
                    uni.navigateTo({
                        url:"../accounts/notice/notice"
                    })
                }else if(e == 7){
                    uni.navigateTo({
                        url:"../accounts/rules/rules"
                    })
                }else if(e == 8){
                    if(this.admin_type == 2){
                        this.show = true
                    }
                    if(this.admin_type == 1){
                       uni.navigateTo({
                           url:"../accounts/contract/contract"
                       }) 
                    }
                }
            }
        },
        
    }
</script>

<style lang="scss">
    page{
        background: #f5f5f5;
    }
    
    .num{
        width: 52upx;
        height: 52upx;
        border-radius: 50%;
        background-color:rgba(26, 115, 232, 1);
        color: #fff;
        text-align: center;
        line-height: 52upx;
        font-size: 32upx;
    }
    .co{
        width: 272rpx;
        height: 268rpx;
        background: #FFFFFF;
        border-radius: 20rpx 20rpx 20rpx 20rpx;
        opacity: 1;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
        text-align: center;
        color: #D93025;
        font-size: 32rpx;
        box-sizing: border-box;
        padding: 40upx;
        
        image{
            width: 80upx;
            height: 80upx;
        }
    }
    .mask{
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, .6);
        position: fixed;
        top: 0;
        left: 0;
    }
    .border_b{
        border-bottom: 2upx solid #D2D2D7;
    }
    
    .settingItem{
        background: #fff;
        display: flex;
        height: 112upx;
        justify-content: space-between;
        box-sizing: border-box;
        align-items: center;
        padding: 0 32upx;
        border-top: 2upx solid #D2D2D7;
        
        .itemLeft{
            display: flex;
            align-items: center;
            
            .itemIcon{
                width: 60upx;
                margin-right: 20upx;
                
                image{
                    width: 100%;
                }
            }
            
            .itemName{
                font-family: 'Hiragino Sans';
                font-style: normal;
                font-weight: 400;
                font-size: 32upx;
                color: #1D1D1F;
            }
        }
        
        .settingItem:last-child{
            border-bottom: 2upx solid #D2D2D7;
        }
        
        .warning{
            width: 52upx;
            height: 52upx;
            
            image{
                width: 100%;
                height: 100%;
            }
        }
        
        .right{
            width: 22upx;
            height: 44upx;
            
            image{
                width: 100%;
                height: 100%;
            }
        }
        
    }
</style>
