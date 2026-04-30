<template>
    <view>
        <u-navbar title="ダウンロードQR" :placeholder="true" :safeAreaInsetTop="true" :autoBack="true" :border="true"></u-navbar>
        <view class="tab">
            <view class="tabContent">
                <view class="tabItem" :class="current == 0? 'active' : '' " @click.stop="changeTab(0)">
                    Card-San App
                </view>
                <view class="tabItem" :class="current == 1? 'active' : '' " @click.stop="changeTab(1)">
                    メンバーズカード
                </view>
            </view>
        </view>
        <view v-if="current == 0">
            <view class="logo">
                <image src="../../static/svg/shopinfo.svg" mode=""></image>
            </view>
            <view class="qrcode">
                <image :src='sc' mode=""></image>
            </view>
            <!-- <view class="btns">
                <view class="btn"  v-if="platform == 'ios'">
                    <image src="../../static/svg/Download_on_the_App_Store_Badge_JP_RGB_blk_100317.svg" mode=""></image>
                </view>
                <view class="btn" style="margin-left: 40upx;" v-if="platform == 'android'" >
                    <image src="../../static/svg/google-play-badge.svg" mode=""></image>
                </view>
            </view> -->
        </view>
        <view v-else>
            <view class="membership">
                <image :src="card" mode="aspectFill"></image>
            </view>
            <view class="memName" v-text="name"></view>
            <view class="qrcode">
                <image :src="qrcode" mode=""></image>
            </view>
        </view>
    </view>
</template>
<script>
    import {getShopInfo} from "@/api/index.js"
    export default {
        data() {
            return {
                current:0,
                qrcode:"",
                name:"",
                card:"",
                platform:'',
                sc:""
            }
        },
        onLoad() {
            let that = this 
            getShopInfo().then((res) => {
                if(res.code == 200){
                    that.qrcode = res.data.shop_info.qrcode
                    that.name = res.data.shop_info.shop_name
                    that.card = res.data.shop_info.default_card.image
                }
            })
            that.downImage()
        },
        onShow() {
            let that = this
            that.platform = uni.getStorageSync('platform')
        },
        methods: {
           changeTab(e){
               this.current = e
           },
           downImage(){
               let that = this 
               uni.downloadFile({
                   url:"https://cdn.me-qr.com/qr/125258049.png?v=1725896858",
                   success:res => {
                       that.sc = res.tempFilePath
                   }
               })
           }
        }
    }
</script>
<style lang="scss">
    .membership{
        width: 346upx;
        height: 210upx;
        margin: 60upx auto 18upx;
        
        image{
            width: 100%;
            height: 100%;
        } 
    }
    
    .memName{
        font-size: 40upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        text-align: center;
    }
    
    .tab{
        width: 100%;
        height: 210upx;
        background: #fff;
        box-sizing: border-box;
        padding: 64upx 30upx 0;
        box-shadow: 0px 0px 12upx 2upx rgba(0,0,0,0.16);
        border-top: 2upx solid #D2D2D7;
    }
    
    .logo{
        width: 180upx;
        height: 180upx;
        margin: 58upx auto 104upx;
        
        image{
            width: 100%;
            height: 100%;
        }
    }
    
    .qrcode{
        width: 448upx;
        height: 448upx;
        box-sizing: border-box;
        padding: 48upx;
        margin: 0 auto;
        image{
            width: 100%;
            height: 100%
        }
    }
    
    .tabItem{
        text-align: center;
        line-height: 64upx;
        flex: 1
    }
    
    .tabContent{
        display: flex;
        align-items: center;
        background: #EAEAEA;
        width: 100%;
        border-radius: 16upx;
        height: 72upx;
    }
    
    .active{
        width: 344upx;
        height: 64upx;
        background: #FFFFFF;
        box-shadow: 0px 6upx 16upx 2upx rgba(0,0,0,0.16);
        border-radius: 16upx;
        opacity: 1;
        border: 2upx solid rgba(255,255,255,0.04);
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
    }
    
    .btns{
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 50upx;
        
        .btn{
            width: 237upx;
            height: 68upx;
            
            image{
                width: 100%;
                height: 100%;
            }
            
        }
    }
</style>
