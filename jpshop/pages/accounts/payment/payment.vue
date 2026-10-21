<template>
    <view>
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="お支払い方法" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="content border_top">
            <view class="cardContent border_bottom ">
                <view class="cardItem" v-for="(item,index) in array">
                    <view class="cardTitle">
                        <view class="logo">
                            <image src="../../../static/icons/cardLogo.png" mode=""></image>
                        </view>
                        <view class="cardName" v-text="item.brand_name || 'クレジットカード'"></view>
                        <view class="cardEdit" @click.stop="toEdit(item)">
                            <image src="../../../static/icons/edit.png" mode=""></image>
                        </view>
                    </view>
                    <view class="cardsContent">
                        <!-- カード番号は保持しない（PCI DSS）。Stripe のカード概要（ブランド・下4桁・期限）のみ表示 -->
                        <view class="nameItem" v-text="`${item.brand_name || ''} **** ${item.last4 || ''}`"></view>
                        <view class="nameItem" v-if="item.exp_month && item.exp_year" v-text="`有効期限　${('0' + item.exp_month).slice(-2)}/${String(item.exp_year).slice(-2)}`"></view>
                    </view>
                </view>
            </view>
            <view class="addNewCard" @click="addNew" v-if="array.length == 0">
                ＋ お支払い方法を変更
            </view>
        </view>
        <mod :config="config" v-if="show == true" @getStatus="changeStatus"></mod>
    </view>
</template>

<script>
    import {getVipCard,deletePayMethod,setupVipCard} from '@/api/index.js'
    import mod from "@/components/mod.vue"
    export default {
        components:{mod},
        data() {
            return {
                array:[],
                current:0,
                config:{
                    title:"削除しますか",
                    content:"削除しますか？",
                    type:"buttoin",
                    confirm:true,
                    cancel:true,
                    confirmText:"確認",
                    cancelText:"キャンセル"
                },
                show:false
            };
        },
        created() {
          let that = this  
          that.getList()
        },
        onShow() {
          this.getList()
        },
        methods:{
            // カード番号は当社サーバーで扱わず、Stripe の安全な入力画面（ブラウザ）で登録・変更する（PCI DSS 対応）
            addNew(){
                this.openCardSetup()
            },
            getList(){
                // Stripe の登録カード概要（brand_name / last4 / exp_month / exp_year）
                getVipCard().then((res) => {
                    if(res.code == 200){
                        this.array = res.data.card ? [res.data.card] : []
                    }
                }).catch(() => {})
            },
            toEdit(e){
                this.openCardSetup()
            },
            openCardSetup(){
                let that = this
                uni.showLoading({ title: '読み込み中', mask: true })
                setupVipCard().then((res) => {
                    uni.hideLoading()
                    if(res.code == 200 && res.data.url){
                        uni.showModal({
                            title: 'カード情報の登録・変更',
                            content: 'ブラウザでStripeの安全な入力画面を開きます。入力完了後、アプリに戻って再表示してください。',
                            cancelText: 'キャンセル',
                            confirmText: '開く',
                            success(r){
                                if(!r.confirm) return
                                // #ifdef APP-PLUS
                                plus.runtime.openURL(res.data.url)
                                // #endif
                                // #ifndef APP-PLUS
                                window.location.href = res.data.url
                                // #endif
                            }
                        })
                    }else{
                        uni.showToast({ title: res.message || 'エラーが発生しました', icon: 'none' })
                    }
                }).catch(() => { uni.hideLoading() })
            },
            deleteItem(item,index){
                this.show = true
                this.current = index
            },
            changeStatus(e){
                let that = this
                if(e == "confirm"){
                    let index = that.current
                    deletePayMethod(that.array[index].id).then((res) => {
                        if(res.code == 200){
                            uni.showToast({
                                title:res.message
                            })
                            that.getList()
                            that.show = false
                        }else{
                            uni.showToast({
                                title:res.message,
                                icon:"none"
                            })
                        }
                    })
                }
                
                if(e == 'cancel'){
                    that.show = false
                }
            }
        }
    }
</script>

<style lang="scss">
    page{
        box-sizing: border-box;
        padding: 0 34upx;
    }
    
    .addNewCard{
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
        margin-top: 60upx;
    }
    
    .cardContent{
        box-sizing: border-box;
        padding: 100upx 0 0;
    }
    
    .cardItem{
        width: 566upx;
        height: 256upx;
        background: #F5F5F7;
        box-shadow: 0px 0px 10upx 2upx rgba(29,29,31,0.3);
        opacity: 1;
        border: 2upx solid #D2D2D7;
        border-radius: 10upx;
        margin: 0 auto 60upx;
        box-sizing: border-box;
        padding: 32upx 40upx;
        
        .nameItem{
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-top: 12upx;
        }
        
        .cardTitle{
            display: flex;
            align-items: center;
            justify-content: space-between;
            
            .cardName{
                flex: 1;
                box-sizing: border-box; 
                padding-left: 20upx;
                font-size: 28upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }
            
            .logo{
                width: 70upx;
                height: 48upx;
                
                image{
                    width: 100%;
                    height: 100%;
                }
            }
            
            .cardEdit{
                width:  32upx;
                height: 32upx;
                
                image{
                    width: 100%;
                    height: 100%;
                }
            }
        }
    }
</style>
