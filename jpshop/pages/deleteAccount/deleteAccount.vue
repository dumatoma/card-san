<template>
    <view class="contents">
        <view class="title">
            アカウントは１度削除すると元に戻りません。
        </view>
        <view class="content">
            <view class="ctitle">
                アカウントを削除した場合：
            </view>
            <view class="citem">
                <view class="c_left"></view>
                <view class="c_right">
                    今後、ご自分のアプリのすべてのデータ、コンテンツは削除されアクセス出来なくなります。
                </view>
            </view>
            <view class="citem">
                <view class="c_left"></view>
                <view class="c_right">
                    １度削除されたアカウントはいかなる理由でも元に戻すことができません。
                </view>
            </view>
            <view class="citem">
                <view class="c_leftCheck">
                    <view class="checkc" @click="changCheck" :style="checked == true? 'background:#1A73E8' : 'background:#fff'">
                        <image v-if="checked == true" src="../../static/icons/select.png" mode=""></image>
                    </view>
                </view>
                <view class="c_right">
                    はい。上記事項を確認し、アカウントを完全に削除することに合意します。
                </view>
            </view>
            <u-button class="customstyle" :disabled="checked == false" text="完全に削除する" @click="confirms"></u-button>
            <u-button class="customs-style" text="キャンセル" @click="cancels"></u-button>
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
</style>
