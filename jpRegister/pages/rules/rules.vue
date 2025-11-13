<template>
    <view>
         <u-navbar title="利用規約・プライバシーポリシー" :autoBack="true" :placeholder="true" :safeAreaInsetTop="true">
        </u-navbar>
        <view class="item">
            <view class="title">
                利用規約
            </view>
            <scroll-view scroll-y class="contents">
                <u-textarea border="none" v-model="i2" disabled autoHeight style="background: transparent !important;"></u-textarea>
            </scroll-view>
        </view>
        <view class="item">
            <view class="title">
                プライバシーポリシー
            </view>
            <scroll-view scroll-y class="contents">
                <u-textarea border="none" v-model="i1" disabled autoHeight style="background: transparent !important;"></u-textarea>
            </scroll-view>
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                i1:"",
                i2:""
            };
        },
        created() {
            this.getConfig()
        },
        methods:{
            getConfig(){
                let that = this
                uni.request({
                    url:that.$baseUrl+"/api/config",
                    method:"get",
                    success(res) {
                        console.log(res)
                        if(res.data.code == 200){
                            that.i1 = res.data.data.config.shop_privacy_policy
                            that.i2 = res.data.data.config.shop_service_regulations
                        }
                    }
                })
            }
        }
    }
</script>

<style lang="scss">
    .item{
        // border-top: 2upx solid #D2D2D7;
        box-sizing: border-box;
        padding: 58upx 32upx 100upx;
        .title{
            font-size: 48upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: bold;
            color: #1D1D1F;
            text-align: center;
            margin-bottom: 40upx;
        }
        
        .contents{
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            height: 40vh;
            overflow-y: scroll;
            height: 790rpx;
            background: #FFFFFF;
            opacity: 1;
            border: 2rpx solid #AAAAAA;
            border-radius: 15upx;
        }
    }
    
    
</style>
