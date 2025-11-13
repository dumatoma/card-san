<template>
    <view>
        <view class="content">
            <view class="ftitle">
                テンプレートの内容を入力してください。
            </view>
            
            <view class="mitem" style="margin-bottom: 26rpx;">
                <view class="title">
                    <text style="color: #D93025;">※</text><text>タイトル</text><text style="font-size: 24rpx;color: #707070;">(タイトルはメッセージに反映されません)</text>
                </view>
                <view class="inp">
                    <input type="text" v-model="val1" placeholder="タイトルを入力してください" />
                </view>
            </view>
            
            <view class="mitem">
                <view class="title">
                    <text style="color: #D93025;">※</text><text>テキスト</text>
                </view>
                <view class="txt">
                    <textarea placeholder="メッセージを入力してください。" maxlength="1600" v-model="val2"></textarea>
                </view>
            </view>
            
            <view class="buttons">
                <view class="btn can" @click="goback">
                    戻　る
                </view>
                <view class="btn con" @click="savedata">
                    保　存
                </view>
            </view>
        </view>
    </view>
</template>

<script>
    import {getShopInfo,changeChatInfo} from "@/api/index.js"
    export default {
        data() {
            return {
                val1:"",
                val2:"",
                type:""
            };
        },
        onLoad(options) {
            let that = this
             that.type = options.type
            uni.setNavigationBarColor({
                backgroundColor:"#FBFBFD"
            })
            if(options.type == 1){
                uni.setNavigationBarTitle({
                    title:"メッセージテンプレート①"
                })
            }else{
                uni.setNavigationBarTitle({
                    title:"メッセージテンプレート②"
                })
            }
            
            getShopInfo().then((res) => {
                console.log(res)
                if(options.type == 1){
                    if(res.data.shop_info.timing_message_tpl1.length > 0){
                        that.val1 = res.data.shop_info.timing_message_tpl1[0]
                        that.val2 = res.data.shop_info.timing_message_tpl1[1]
                    }
                }else{
                    if(res.data.shop_info.timing_message_tpl2.length > 0){
                        that.val1 = res.data.shop_info.timing_message_tpl2[0]
                        that.val2 = res.data.shop_info.timing_message_tpl2[1]
                    }
                }
            }) 
        },
        methods:{
            goback(){
                uni.navigateBack()
            },
            savedata(){
                let that = this 
                let data = {} 
                data.timing_message_tpl1 = []
                data.timing_message_tpl2 = []
                data.type = 'message'
                data.field = that.type == 1?'timing_message_tpl1' : 'timing_message_tpl2'
                data.timing_message_tpl1.push(that.val1)
                data.timing_message_tpl1.push(that.val2)
                data.timing_message_tpl2.push(that.val1)
                data.timing_message_tpl2.push(that.val2)
                changeChatInfo(data).then((res) => {
                    console.log(res)
                    if(res.code == 200){
                        uni.showToast({
                            title:res.message
                        })
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:'none'
                        })
                    }
                })
            }
        }
    }
</script>

<style lang="scss">
    .buttons{
        display: flex;
        align-items: center;
        margin-top: 80rpx;
        justify-content: center;
        .btn{
            width: 240rpx;
            height: 80rpx;
            background: #1A73E8;
            border-radius: 52rpx;
            font-size: 32rpx;
            line-height: 80rpx;
            text-align: center;
        }
        
        .can{
            background: rgba(29,29,31,0.2);
            border: 2rpx solid #707070;
            color: #707070;
            margin-right: 52rpx;
        }
        
        .con{
            color: #FFFFFF;
            background: #1A73E8;
        }
    }
    .content{
        border-top: 2rpx solid #d2d2d7;
        box-sizing: border-box;
        padding: 74rpx 52rpx;
        
        .ftitle{
            font-weight: 600;
            font-size: 32rpx;
            color: rgba(29,29,31,0.9);
            margin-bottom: 48rpx;
        }
        
        .mitem{
            .title{
                font-size: 28rpx;
                color: #1D1D1F;
            }
            
            .inp{
                width: 100%;
                height: 68rpx;
                border-radius: 12rpx;
                border: 2rpx solid #AAAAAA;
                margin-top: 6rpx;
                box-sizing: border-box;
                padding: 0 20rpx;
                
                input{
                    width: 100%;
                    height: 100%;
                    font-size: 28rpx;
                }
            }
            
            .txt{
                width: 100%;
                height: 646rpx;
                background: #FFFFFF;
                border-radius: 20rpx;
                border: 2rpx solid #AAAAAA;
                box-sizing: border-box;
                padding: 20rpx;
                
                textarea{
                    width: 100%;
                    height: 100%;
                    display: block;
                    font-size: 28rpx;
                }
            }
        }
        
        
    }
</style>
