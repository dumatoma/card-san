<template>
    <view>
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar :title="navTitle" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-21] ほかの画面と同じ見た目に(白いカード・入力欄・保存ボタン)。テキストの枠を大きく、ボタンは上と少し離す。「戻る」は左上の < と同じなので外した -->
        <view class="mt">
            <view class="mt-when">チャットで、この文をすぐに呼び出して送れます。</view>
            <view class="mt-card">
                <view class="mt-h"><text class="mt-req">※</text>タイトル<text class="mt-sub">(お客様には表示されません)</text></view>
                <input class="mt-in" :class="{ on: fk == 1 }" type="text" v-model="val1" placeholder="例:Google口コミ依頼"
                    placeholder-class="mt-ph" @focus="fk = 1" @blur="fk = 0" />
            </view>
            <view class="mt-card">
                <view class="mt-h"><text class="mt-req">※</text>テキスト</view>
                <textarea class="mt-ta" :class="{ on: fk == 2 }" placeholder="メッセージを入力してください" placeholder-class="mt-ph"
                    maxlength="1600" :show-confirm-bar="false" v-model="val2" @focus="fk = 2" @blur="fk = 0"></textarea>
                <view class="mt-cnt">{{ (val2 || '').length }} / 1600</view>
            </view>
            <view class="mt-btn" hover-class="mt-hover" @click="savedata">保存する</view>
        </view>
    </view>
</template>

<script>
    import {getShopInfo,changeChatInfo} from "@/api/index.js"
    export default {
        data() {
            return {
                navTitle: '', // [カスタムエイド 2026-10-20] 見出しの文字

                val1:"",
                val2:"",
                type:"",
                fk: 0 // [カスタムエイド 2026-10-21] 入力中の欄(枠を青く)
            };
        },
        onLoad(options) {
            let that = this
             that.type = options.type
            uni.setNavigationBarColor({
                backgroundColor:"#FBFBFD"
            })
            if(options.type == 1){
                this.navTitle = "メッセージテンプレート①" // [カスタムエイド 2026-10-20]
            }else{
                this.navTitle = "メッセージテンプレート②" // [カスタムエイド 2026-10-20]
            }
            
            getShopInfo().then((res) => {
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
            }).catch(() => {})
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
                }).catch(() => {})
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-21] ほかの画面と同じ見た目 */
    page { background: #F2F2F7; }
    .mt { padding: 40rpx 32rpx calc(96rpx + env(safe-area-inset-bottom)); }
    .mt-when { font-size: 26rpx; color: #6E6E73; margin: 0 8rpx 24rpx; }
    .mt-card { background: #FFFFFF; border-radius: 32rpx; padding: 40rpx 32rpx; margin-bottom: 32rpx; }
    /* [カスタムエイド 2026-10-21] 見出しの文字と入力枠の見た目のすき間を約6pxに(行の高さを詰め、余白 4px) */
    .mt-h { font-size: 28rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; margin-bottom: 8rpx; line-height: 1.2; }
    .mt-req { color: #D93025; }
    .mt-sub { font-size: 22rpx; color: #6E6E73; font-family: 'Hiragino Sans', sans-serif; }
    .mt-in { height: 96rpx; border: 1px solid #D2D2D7; border-radius: 20rpx; padding: 0 24rpx; font-size: 30rpx; background: #FFFFFF; box-sizing: border-box; }
    .mt-ta { width: 100%; height: 760rpx; border: 1px solid #D2D2D7; border-radius: 20rpx; padding: 22rpx 24rpx; font-size: 30rpx; line-height: 1.6; box-sizing: border-box; display: block; }
    .mt-in.on, .mt-ta.on { border-color: #1A73E8; box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.18); }
    .mt-ph { color: #AEAEB2; }
    .mt-cnt { text-align: right; font-size: 22rpx; color: #8A8A8E; margin-top: 10rpx; }
    .mt-btn { margin-top: 64rpx; height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif;
        display: flex; align-items: center; justify-content: center; }
    .mt-hover { opacity: 0.7; }
</style>
