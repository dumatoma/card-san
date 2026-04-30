<template>
    <view>
        <view class="mask" @click="closeerr">
            <view class="maskContent" v-text="title">
                
            </view>
        </view>
    </view>
</template>

<script>
    export default {
        props: {
            title: {
                type: String,
                default: () => {
                    return '';
                }
            },
        },
        name:"err",
        data() {
            return {
                
            };
        },
        methods:{
            closeerr(){
                this.$emit("close",123)
            }
        }
    }
</script>

<style lang="scss">
    .mask{
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(255, 255, 255, .5);
        // background: red;
        z-index: 10005 !important;
    }
    
    .maskContent{
        width: 402upx;
        background: rgba(217,48,37,0.85);
        border-radius: 20upx;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
        box-sizing: border-box;
        padding: 40upx 28upx;
        text-align: center;
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #FFFFFF;
    }
</style>