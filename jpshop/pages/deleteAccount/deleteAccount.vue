<template>
    <view class="contents da-page">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="アカウント削除" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-20] 両わきと枠の中の余白を広げ、角丸のカードに。チェックボックス・ボタンもこれまでの画面に合わせた -->
        <view class="da">
            <view class="da-title">アカウントは1度削除すると元に戻りません。</view>
            <view class="da-card">
                <view class="da-cap">アカウントを削除した場合:</view>
                <view class="da-li">
                    <view class="da-dot"></view>
                    <view class="da-li-text">今後、ご自分のアプリのすべてのデータ、コンテンツは削除されアクセス出来なくなります。</view>
                </view>
                <view class="da-li">
                    <view class="da-dot"></view>
                    <view class="da-li-text">1度削除されたアカウントはいかなる理由でも元に戻すことができません。</view>
                </view>
            </view>
            <view class="da-card da-check-row" @click="changCheck">
                <view class="da-check" :class="{ on: checked }"><view class="da-tick" v-if="checked"></view></view>
                <view class="da-check-text" :class="{ on: checked }">はい。上記事項を確認し、アカウントを完全に削除することに合意します。</view>
            </view>
            <view class="da-del" :class="{ dis: !checked }" hover-class="da-hover" @click="confirms">完全に削除する</view>
            <view class="da-cancel" hover-class="da-hover" @click="cancels">キャンセル</view>
        </view>
        <view class="mask" v-if="show == true">
            <view class="maskcontent">
                <image src="../../static/images/redright.png" class="mi" mode=""></image>
                <view>
                     アカウントを<br/>削除しました
                </view>
            </view>
        </view>
    </view>
</template>

<script>
    import {cancellation} from "@/api/index.js"
    export default {
        data() {
            return {
                checked:false,
                show:false
            };
        },
        methods:{
            changCheck(){
                this.checked = !this.checked
            },
            cancels(){
                uni.navigateBack()
            },
            confirms(){
                let that = this 
                if(that.checked == true){
                    cancellation().then((res) => {
                        if(res.code == 200){
                            that.show = true
                            setTimeout(() => {
                                uni.removeStorageSync("token")
                                uni.removeStorageSync("admin")
                                uni.redirectTo({
                                    url:"../login/login"
                                })
                            },2500)
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
    }
</script>

<style lang="scss" scoped>
    .mask{
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(29,29,31,0.2);
        z-index: 999;
        
        .maskcontent{
            width: 320upx;
            height: 300upx;
            background: #fff;
            border-radius: 20upx;
            text-align: center;
            box-sizing: border-box;
            padding: 60upx;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #D93025;
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%,-50%);
            
            .mi{
                width: 60upx;
                height: 60upx;
                margin-bottom: 40upx;
            }
            
        }
    }
    .customstyle{
        width: 100% !important;
        height: 88upx !important;
        color: #fff !important;
        background: #D93025;
        border-radius: 20upx;
        margin-top: 60upx;
        margin-bottom: 20upx;
    }
    .customs-style{
        width: 100% !important;
        height: 88upx !important;
        color: #1d1d1f !important;
        background: rgba(29,29,31,0.16);
        border-radius: 20upx;
    }
    .contents{
        background-color: #f5f5f7;
        box-sizing: border-box;
        padding: 0 20upx;
        border-top: 2upx solid #d2d2d7;
        height: 100vh;
    }
    
    .checkc{
        width: 32upx;
        height: 32upx;
        background: #FFFFFF;
        border-radius: 4upx;
        opacity: 1;
        border: 2upx solid #707070;
        margin-right: 10upx;
        position: relative;
        top: 10upx;
        
        image{
            width: 100%;
            height: 100%;
            display: inline;
        }
    }
    
    .title{
        font-size: 40upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        padding: 40upx 0 19upx 0;
        
    }
    
    .content{
        background: #fff;
        border: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding: 20upx;
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        
        .citem{
            margin-top: 16upx;
            display: flex;
            align-items: flex-start;
            
            .c_left{
                width: 20upx;
                height: 20upx;
                background: #707070;
                border-radius: 50%;
                flex-shrink: 0;
                position: relative;
                top: 10upx;
                margin-right: 10upx;
            }
        }
    }

    /* [カスタムエイド 2026-10-20] 新しい見た目 */
    .contents.da-page {
        min-height: 100vh;
        height: auto;
        padding: 0;
        border-top: 0;
        background: #F5F5F7;
    }

    .da {
        padding: 40rpx 32rpx calc(80rpx + env(safe-area-inset-bottom));
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .da-title {
        font-size: 36rpx;
        font-weight: 700;
        line-height: 1.6;
        margin: 0 8rpx 32rpx;
    }

    .da-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 36rpx 36rpx 16rpx;
        margin-bottom: 24rpx;
    }

    .da-cap {
        font-size: 28rpx;
        font-weight: 700;
        margin-bottom: 20rpx;
    }

    .da-li {
        display: flex;
        align-items: flex-start;
        margin-bottom: 20rpx;
    }

    .da-dot {
        width: 12rpx;
        height: 12rpx;
        flex-shrink: 0;
        margin: 18rpx 20rpx 0 4rpx;
        border-radius: 50%;
        background: #C5221F;
    }

    .da-li-text {
        font-size: 28rpx;
        line-height: 1.7;
        color: #3A3A3C;
    }

    .da-check-row {
        display: flex;
        align-items: flex-start;
        padding: 32rpx 36rpx;
        margin-bottom: 48rpx;
    }

    .da-check {
        width: 44rpx;
        height: 44rpx;
        flex-shrink: 0;
        margin: 2rpx 20rpx 0 0;
        border: 2px solid #C7C7CC;
        border-radius: 12rpx;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #FFFFFF;
    }

    .da-check.on {
        border-color: #1A73E8;
        background: #1A73E8;
    }

    /* 白いチェック */
    .da-tick {
        width: 10rpx;
        height: 20rpx;
        border-right: 4rpx solid #FFFFFF;
        border-bottom: 4rpx solid #FFFFFF;
        transform: translateY(-3rpx) rotate(45deg);
    }

    .da-check-text {
        font-size: 28rpx;
        line-height: 1.7;
        color: #6E6E73;
    }

    .da-check-text.on {
        color: #1D1D1F;
    }

    .da-del {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #C5221F;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
        margin-bottom: 24rpx;
    }

    .da-del.dis {
        background: #F2B8B5;
    }

    .da-cancel {
        height: 104rpx;
        line-height: 102rpx;
        border-radius: 28rpx;
        border: 1px solid #D2D2D7;
        background: #FFFFFF;
        color: #1D1D1F;
        text-align: center;
        font-size: 32rpx;
        font-weight: 500;
        box-sizing: border-box;
    }

    .da-hover {
        opacity: 0.7;
    }
</style>
