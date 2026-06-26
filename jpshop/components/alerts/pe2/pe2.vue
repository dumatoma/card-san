<template>
    <view class="containp">
        <view class="tipContent">
            <view class="tipRedTitle">
                メッセージ配信数超過のお知らせ
            </view>
            <view class="tipinfo" style="margin-top: 30upx;">
                今月の無料配信数（3,000通/月）を超過しています。
            </view>
            <view class="tipinfo">
                追加料¥1,200のお支払いで、更に3,000通の
            </view>
            <view class="tipinfo">
                メッセージを配信できます。
            </view>
            <view class="buttons">
                <view class="payBtn" @click="confirm1">お支払い</view>
                <view class="cancelBtn" @click="confirm2">キャンセル</view>
            </view>
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {};
        },
        methods:{
            confirm1(){
                this.$emit("confirm", 111)
            },
            confirm2(){
                this.$emit("cancel", 222)
            }
        }
    }
</script>

<style lang="scss">
    .containp{
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.6);
        position: fixed;
        top: 0;
        left: 0;
        z-index: 999 !important;
    }

    .tipRedTitle{
        font-size: 36upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: bold;
        color: #D93025;
        text-align: center;
        margin-bottom: 20upx;
        padding-bottom: 30upx;
        border-bottom: 2upx solid #EAEAEA;
    }

    .payBtn{
        width: 480upx;
        height: 96upx;
        line-height: 96upx;
        background: #1A73E8;
        border-radius: 48upx;
        text-align: center;
        color: #fff;
        font-size: 36upx;
        font-weight: bold;
        margin: 80upx auto 30upx;
    }

    .cancelBtn{
        width: 480upx;
        height: 96upx;
        line-height: 96upx;
        background: #EAEAEA;
        border-radius: 48upx;
        text-align: center;
        color: #707070;
        font-size: 36upx;
        margin: 0 auto;
    }

    .tipinfo{
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        text-align: center;
        line-height: 48upx;
    }

    .tipContent{
        width: 690upx;
        background: #fff;
        border-radius: 20upx;
        box-sizing: border-box;
        padding: 60upx 20upx 50upx;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
    }
</style>
