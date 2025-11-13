<template>
    <view>
        <view class="mask" v-if="show" @click="closethis">
            <view class="co">
                <image src="../../static/images/redright.png" mode=""></image>
                <view>実行する権限がありません</view>
            </view>
        </view>
    </view>
</template>

<script>
    export default {
        name:"permission",
        props:{
           show: Boolean
        },
        data() {
            return {
                
            };
        },
        methods:{
            closethis(){
                this.$emit("close",1)
            }
        }
    }
</script>

<style lang="scss" scoped>
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
        z-index: 100007 !important;
    }
</style>