<template>
    <view>
        <view class="content border_top">
            <view class="cardContent border_bottom ">
                <view class="cardItem" @longpress="deleteItem(item,index)" v-for="(item,index) in array">
                    <view class="cardTitle">
                        <view class="logo">
                            <image src="../../../static/icons/cardLogo.png" mode=""></image>
                        </view>
                        <view class="cardName" v-text="item.name"></view>
                        <view class="cardEdit" @click.stop="toEdit(item)">
                            <image src="../../../static/icons/edit.png" mode=""></image>
                        </view>
                    </view>
                    <view class="cardsContent">
                        <view class="nameItem" v-text="`下４桁 ${item.cvv}`"></view>
                        <view class="nameItem" v-text="`${item.card_no}`"></view>
                        <view class="nameItem" v-text="`有効期限　${item.expire_date}`"></view>
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
    import {getPayList,deletePayMethod} from '@/api/index.js'
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
        methods:{
            addNew(){
                uni.redirectTo({
                    url:'../payment/paylist'
                })
            },
            getList(){
                getPayList().then((res) => {
                    if(res.code == 200){
                        this.array = res.data.cards
                    }
                })
            },
            toEdit(e){
                uni.redirectTo({
                    url:"../payment/payDetail?id="+e.id+"&source=edit&type=1"
                })
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
