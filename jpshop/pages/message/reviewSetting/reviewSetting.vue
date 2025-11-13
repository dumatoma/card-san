<template>
    <view>
        <view class="content">
            <view class="firstPart">
                <view class="fptitle">
                    レビューリンクボタンのデザイン
                </view>
                <view class="reviewItem">
                    <view class="reviewLeft">
                        <view class="checkBox" :style="current == 0? 'border:2rpx solid #1A73E8' : ''" @click="current = 0">
                            <view class="inner" v-if="current == 0"></view>
                        </view>
                    </view>
                    <view class="reviewRight">
                        <image src="../../../static/images/review.svg" mode="aspectFill"></image>
                    </view>
                </view>
                <view class="reviewItem" style="margin: 40rpx 0;">
                    <view class="reviewLeft">
                        <view class="checkBox":style="current == 1? 'border:2rpx solid #1A73E8' : ''" @click="current = 1">
                            <view class="inner" v-if="current == 1"></view>
                        </view>
                    </view>
                    <view class="reviewRight" :style="image == '' ? 'border:2rpx solid #aaa' : ''">
                       <image v-if="image" :src="image" mode="aspectFill"></image>
                    </view>
                </view>
                <view class="reviewItem" style="margin: 40rpx 0;">
                    <view class="reviewLeft"> </view>
                    <view class="reviewRight">
                       <view class="btn" @click.stop="chooseImage">
                           <u-icon name="plus" color="#1D1D1F" :bold="true"></u-icon>
                           <text style="margin-left: 20rpx;font-size: 32rpx;color: rgba(29,29,31,0.9)">画像アップロード</text>
                       </view>
                    </view>
                </view>
            </view>
            
            <view class="secondPart">
                <view class="fptitle">
                    お店のGoogleビジネスプロフィールのレビュー用リンクURLを貼り付けてください
                </view>
                <view class="spContent">
                    <textarea v-model="des"></textarea>
                </view>
                <view class="rule" @click.stop="openURL">
                    レビュー用リンクの作成方法　＞
                </view>
                <view class="saveBtn" @click="saveData">
                    保存
                </view>
            </view>
            
        </view>
    </view>
</template>

<script>
    import {reviewSetting,getShopInfo} from "@/api/index.js"
    export default {
        data() {
            return {
                image:"",
                current:0,
                des:""
            };
        },
        onLoad() {
            let that = this 
            that.getShop()
        },
        methods:{
            getShop(){
                let that = this
                getShopInfo().then((res) => {
                    console.log(res)
                    if(res.code == 200){
                        that.current = res.data.shop_info.comment_button_style * 1 - 1
                        that.image = res.data.shop_info.comment_button_style2_image
                        that.des = res.data.shop_info.comment_button_url
                    }
                })
            },
            openURL() {
                // #ifdef APP-PLUS
                if (plus.os.name == 'Android' || plus.os.name == 'iOS') {
                    plus.runtime.openURL('https://cardsan.zendesk.com/hc/ja/articles/39169673962393-Google%E3%83%93%E3%82%B8%E3%83%8D%E3%82%B9%E3%83%97%E3%83%AD%E3%83%95%E3%82%A3%E3%83%BC%E3%83%AB%E3%81%AE%E3%83%AC%E3%83%93%E3%83%A5%E3%83%BC%E3%83%AA%E3%83%B3%E3%82%AF%E3%81%AE%E4%BD%9C%E6%88%90');
                    return
                }
                // #endif
            },
            saveData(){
                let that = this 
                let data = {}
                data.type = "comment_button"
                data.comment_button_style = that.current * 1 + 1
                data.comment_button_style2_image = that.image
                data.comment_button_url = that.des
                reviewSetting(data).then((res) => {
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
            chooseImage() {
                let that = this
                uni.hideKeyboard()
                uni.chooseImage({
                    count: 1,
                    success: (res) => {
                        const tempPaths = res.tempFilePaths
                        uni.uploadFile({
                            url: that.$baseUrl + '/tool/upload/file',
                            filePath: res.tempFilePaths[0],
                            name: "file",
                            success: (result) => {
                                if (result.errMsg == "uploadFile:ok") {
                                    let re = JSON.parse(result.data)
                                    if (re.code == 200) {
                                        that.image = re.data.path
                                    } 
                                }
                            }
                        })
                    }
                });
            },
        }
    }
</script>

<style lang="scss">
    .inner{
        width: 100%;
        height: 100%;
        background: #1A73E8;
        border-radius: 50%;
    }
    .saveBtn{
        width: 480rpx;
        height: 96rpx;
        background: #1A73E8;
        border-radius: 20rpx;
        text-align: center;
        line-height: 96rpx;
        font-weight: bold;
        font-size: 32rpx;
        color: #FFFFFF;
        margin: 0 auto;
        margin-top: 100rpx;
    }
    .content{
        font-family: Hiragino, Hiragino Sans !important;
        border-top: 2rpx solid #d2d2d7;
        
        .secondPart{
            box-sizing: border-box;
            padding: 80rpx 46rpx;
            
            .fptitle{
                font-size: 32rpx;
                color: rgba(29,29,31,0.9);
                margin-bottom: 58rpx;
            }
            
            .spContent{
                width: 100%;
                height: 160rpx;
                border-radius: 20rpx;
                border: 2rpx solid #D2D2D7;
                box-sizing: border-box;
                padding: 20rpx;
                
                textarea{
                    width: 100%;
                    height: 100%;
                    display: block;
                }
            }
            
            .rule{
                font-size: 28rpx;
                color: #1A73E8;
                margin-top: 20rpx;
            }
        }
        
        .firstPart{
            box-sizing: border-box;
            padding: 80rpx 46rpx;
            border-bottom: 2rpx solid #d2d2d7;
            
            .fptitle{
                font-size: 32rpx;
                color: rgba(29,29,31,0.9);
                margin-bottom: 58rpx;
            }
            
            .reviewItem{
                display: flex;
                align-items: center;
                
                .reviewLeft{
                    width: 64rpx;
                    display: flex;
                    align-items: center;
                    
                    .checkBox{
                        width: 32rpx;
                        height: 32rpx;
                        background: #FFFFFF;
                        border: 2rpx solid #AAAAAA;
                        box-sizing: border-box;
                        padding: 6rpx;
                        border-radius: 50%;
                    }
                }
                
                .reviewRight{
                    width: 492rpx;
                    height: 120rpx;
                    border-radius: 12rpx 12rpx 12rpx 12rpx;
                    .btn{
                        width: 100%;
                        height: 96rpx;
                        background: #F5F5F7;
                        border-radius: 12rpx 12rpx 12rpx 12rpx;
                        border: 2rpx solid #707070;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    
                    .imageContent{
                        width: 100%;
                        height: 100%;
                        border-radius: 20rpx;
                        border: 2rpx solid #AAAAAA;
                        overflow: hidden;
                    }
                    
                    image{
                        width: 492rpx;
                        height: 120rpx;
                        border-radius: 15rpx;
                    }
                }
                
            }
        }
    }
</style>
