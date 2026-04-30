<template>
    <view>
        <u-navbar :border="true" :placeholder='true' title="副管理者の登録" :safeAreaInsetTop="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" @click="toAddnew" mode=""></image>
            </view>
        </u-navbar>
        <view class="cont">
            <view class="tips">
                副管理者の氏名、電話番号(SMS認証)を入力してください。
            </view>
            <view class="inpItem">
                <view class="inpName">
                    副管理者名
                </view>
                <u-input v-model="name"></u-input>
            </view>
            <view class="inpItem">
                <view class="inpName">
                    電話番号(SMS認証用)
                </view>
                <u-input v-model="phone" placeholder="副管理者の電話番号を登録"></u-input>
            </view>
            <u-button text="次へ" class="nextStep" @click="next" :class="name == ''?'' : phone == ''? '' : 'selected'"></u-button>
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                name:"",
                phone:""
            }
        },
        methods: {
            toAddnew(){
                uni.navigateBack()
            },
            next(){
                let that = this 
                if(that.name != '' && that.phone != ''){
                    uni.navigateTo({
                        url:"../addNew/jurisdiction?name="+that.name+"&phone="+that.phone+"&type=add"
                    })
                }
            
            }
        }
    }
</script>

<style lang="scss">
    .nextStep{
        width: 480upx !important;
        height: 96upx !important;
        background: rgba(26,115,232,0.6) !important;
        border-radius: 48upx !important;
        color: #fff !important;
        margin-top:  300upx !important;
    }
    
    .selected{
        background: rgb(26,115,232) !important;
    }
    
    .cont{
        box-sizing: border-box;
        padding: 80upx 74upx;
        
        .tips{
            font-size: 32upx;
            font-family: Hiragino Sans-W4, Hiragino Sans;
            font-weight: normal;
            color: rgba(29,29,31,0.9);
            margin-bottom: 80upx;
        }
        
        .inpItem{
            margin-bottom: 60upx;
            
            .inpName{
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;
                margin-bottom: 10upx;
            }
        }
    }
</style>
