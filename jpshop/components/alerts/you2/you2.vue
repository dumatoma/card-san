<template>
    <view class="containp">
        <view class="tipContent">
            <view class="tipTitle">
                クーポンを配信
            </view>
            <view class="tipinfo">
                このクーポンは
            </view>
            <view class="tipinfo" v-if="isToday">
                {{dates}}
            </view>
            <view class="tipinfo" v-if="isToday">
                {{number}}人に配信されます。
            </view>
            <view class="tipinfo" v-if="!isToday">
                今すぐ{{number}}人に配信されます。
            </view>
            <view class="tipwarning">
                配信上限数を超過しています。
            </view>
            <view class="tipinfo">
                このクーポンを配信するには、
            </view>
            <view class="tipinfo">
                 追加料(¥1,200)のお支払いで、
            </view>
            <view class="tipinfo">
                更に3,000通のクーポンを配信できます。
            </view>
            <view class="tipinfo">
                配信しますか？
            </view>
            <view class="buttons">
                <u-button type="error" text="配信する" class="btn1" @click="fasong('confirm')"></u-button>
                <u-button text="キャンセル" class="btn2" @click="fasong('cancel')"></u-button>
            </view>
        </view>
    </view>
</template>

<script>
    export default {
        props: {
            // 标记的日期
            number: {
                type: Number,
                default: () => {
                    return [];
                }
            },
            dates:{
                type: String,
                default: () => {
                    return "";
                }
            },
            isToday:{
                type: Boolean,
                default: () => {
                    return true;
                }
            }
        },
        data() {
            return {
                
            };
        },
        methods:{
            fasong(e){
                if(e == 'confirm'){
                    this.$emit("confirm",111)
                }else{
                    this.$emit("cancel",222)
                }
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
    
    .tipwarning{
        color: #d93025;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        text-align: center;
        margin: 40upx 0;
        font-size: 28upx;
    }
    
    .btn1{
        width: 480upx;
        height: 96upx;
        margin: 120upx auto 60upx;
    }
    
    .btn2{
        width: 480upx;
        height: 96upx;
        background: #EAEAEA;
        color: #707070;
    }
    
    .tipTitle{
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        text-align: center;
        margin-bottom: 60upx;
    }
    
    .tipinfo{
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        text-align: center;
    }
    
    .tipContent{
        width: 690upx;
        background: #fff;
        border-radius: 20upx;
        box-sizing: border-box;
        padding: 60upx 20upx;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
    }
</style>
