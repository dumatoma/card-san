<template>
    <view class="contain">
        <view class="modual">
            <view class="contents">
                <view class="title" v-html="config.title"></view>
                <view class="detail" v-html="config.content"></view>
            </view>
            <view class="btns" v-if="config.type != 'button' && config.type != 'revers' ">
                <view hover-class="op" class="btn confirm" v-if="config.confirm" v-text="config.confirmText" @click="callBack('confirm')"></view>
                <view hover-class="op" class="btn cancel" v-if="config.cancel" style="border-bottom: none !important;" v-text="config.cancelText" @click="callBack('cancel')"></view>
                <view hover-class="op" class="btn default" v-if="config.default" v-text="config.defaultText" @click="callBack('default')"></view>
            </view>
            <view class="buttons" v-if="config.type == 'button' ">
                <u-button hover-class="op" class="but" v-if="config.confirm" type="success" v-text="config.confirmText" @click="callBack('confirm')"></u-button>
                <u-button class="but can" v-if="config.cancel" hover-class="err-hover"  v-text="config.cancelText" @click="callBack('cancel')"></u-button>
                <u-button hover-class="op" class="but bdefault" v-if="config.default" v-text="config.defaultText" @click="callBack('default')"></u-button>
            </view>
            <view class="btns" v-if="config.type == 'revers'">
                <view hover-class="op" class="btn cancel" v-if="config.cancel" style="border-bottom: none;" v-text="config.cancelText" @click="callBack('cancel')"></view>
                <view hover-class="op" class="btn confirm" v-if="config.confirm" v-text="config.confirmText" @click="callBack('confirm')"></view>
                <view hover-class="op" class="btn default" v-if="config.default" v-text="config.defaultText" @click="callBack('default')"></view>
            </view>
        </view>
    </view>
</template>
<script>
    export default {
        name:"mod",
        props:{
           config: Object
        },
        data() {
            return {
            };
        },
        created() {
        },
        methods:{
            callBack(e){
                this.$emit('getStatus',e)
            }
        }
    }
</script>

<style lang="scss" scoped>
    .op{
        opacity: 0.6;
    }
    .contain{
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(29,29,31,0.2);
        z-index: 999;
    }
    
    .modual{
        background: #f9f9f9;
        width: 600upx; 
        border-radius: 28upx;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
        text-align: center;
    }
    
    .contents{
        box-sizing: border-box;
        padding: 60upx 20upx;
    }
    
    .title{
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: rgba(29,29,31,0.9);
        font-weight: bold;;
    }
    
    .detail{
       font-size: 28upx;
       font-family: Hiragino Sans-W3, Hiragino Sans;
       font-weight: normal;
       color: #1D1D1F; 
       margin-top: 20upx;
    }
    
    .confirm{
        font-size: 40upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #007AFF;
        border-top: 1upx solid #868686;
    }
    
    .cancel{
       font-size: 40upx;
       font-family: Hiragino Sans-W3, Hiragino Sans;
       font-weight: normal;
       color: #D93025;
       border-top: 1upx solid #868686;
       border-bottom: 1upx solid #868686;
    }
    
    .bdefault{
       font-family: Hiragino Sans-W3, Hiragino Sans;
       font-weight: normal;
       color: #707070 !important;
       background: rgba(29,29,31,0.1);
    }
    
    .buttons{
       margin-top: 100upx; 
    }
    
    .btn{
        box-sizing: border-box;
        padding: 36upx 0;
        white-space: pre-wrap;
    }
    
    .but{
        width: 70%;
        margin-bottom: 40upx;
    }
    
    .can{
        background: #D93025 !important;
        color: #fff;
    }
</style>