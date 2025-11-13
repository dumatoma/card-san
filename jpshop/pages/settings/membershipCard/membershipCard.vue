<template>
    <view>
        <u-navbar title="メンバーズカード" :border="true" @leftClick="goback" :placeholder="true"></u-navbar>
        <view class="top1">
            <view class="topLeft">
                カードの種類
            </view>
            <view class="topRight">
                <view class="pickers" @click="showPop">
                    <view class="pickerName" v-text='type == 1? "スタンプカード A" :  type == 2? "スタンプカード B" :"ポイントカード" '></view>
                    <u-icon name="arrow-down"></u-icon>
                </view>
            </view>
        </view>
        <view class="addNew" @click="addNew">
            <u-icon name="plus-circle-fill" color="#1A73E8" size="22"></u-icon>
            <text>カードを作る</text>
        </view>
        <view class="noData" v-if="array.length == 0">
            登録されたカードはありません
        </view> 
        <view class="cards">
            <u-swipe-action>
                <u-swipe-action-item :options="options" v-for="(item,index) in array" @click="deleteItems(item)">
                    <view class="swipe-action u-border-top u-border-bottom">
                        <view class="swipe-action__content" style="padding: 0 !important;">
                            <view class="listContent">
                                <view class="listItem" :style="item.default?'background:rgba(26, 115, 232,.1)' : ''">
                                    <view class="itemLeft">
                                        <view class="selectContent" @click="chooseItem(item.id,item)">
                                            <view class="unselect" v-if="!item.default"></view>
                                            <view class="selected" v-if="item.default">
                                                <view class="fill"></view>
                                            </view>
                                        </view>
                                    </view>
                                    <view class="itemRight" @click="toEdit(item.id,item)">
                                        <view class="itemTitle" v-text="item.name"></view>
                                        <view class="items" v-if="type == 3 && item.is_point == 1" v-text="`ポイント数 : ${item.amount}円で${item.point}ポイント`"></view>
                                        <view class="items" v-if="type == 3 && item.is_point == 0" v-text="`ポイント数 : -`"></view>
                                        <view class="items" v-if="type != 3" v-text="`スタンプ数 : ${item.count}個`"></view>
                                        <view class="items" v-if="item.valid_type == 1 && item.valid_day < 300" v-text="`有効期限：発行から${item.valid_day}日`"></view>
                                        <view class="items" v-if="item.valid_type == 1 && item.valid_day > 300" v-text="`有効期限：発行から${item.valid_day / 365}年`"></view>
                                        <view class="items" v-if="item.valid_type != 1" v-text="`有効期限:${item.expire_time || 'なし'}`"></view>
                                        <view class="bk">
                                            <view>カード背景色</view>
                                            <view class="colorSqu" v-if="item.background_color" :style="`background:${item.background_color}`"></view>
                                            <view class="colorSqu" v-else>
                                                -
                                            </view>
                                        </view>
                                        <view class="itemImage">
                                            <image :src="item.image" mode="aspectFill"></image>
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </u-swipe-action-item>
            </u-swipe-action>
        </view>
        <u-popup :show="show" round="32">
            <view class="pTitle">
                <view class="pClose">
                    <image src="../../../static/icons/tclose.png" mode="" @click="show=false"></image>
                </view>
                <view class="pTitlename">
                    カードの種類
                </view>
                <view class="pSave" @click="getList">
                    保存
                </view>
            </view>
            <view class="pContent">
                <view id="title">
                    カードの種類を選んでください
                </view>
                <view class="pItem">
                    <view class="itemTitle">
                        <view class="selectContent"  @click="changeType(1)">
                            <view class="unselect" v-if="type != 1"></view>
                            <view class="selected" v-if="type == 1">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="typeName" :style="type == 1?'color:#1d1d1d' : ''">
                            スタンプカード A
                        </view>
                    </view>
                    <image class="pitemImage" src="../../../static/images/typeA.png" mode="widthFix"></image>
                </view>
                <view class="pItem">
                    <view class="itemTitle">
                        <view class="selectContent"  @click="changeType(2)">
                            <view class="unselect" v-if="type != 2"></view>
                            <view class="selected" v-if="type == 2">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="typeName" :style="type == 2?'color:#1d1d1d' : ''">
                            スタンプカードB
                        </view>
                    </view>
                    <image class="pitemImage" src="../../../static/images/typeB.png" mode="widthFix"></image>
                    <view class="introduce">
                        <view class="inTitle">
                            [スタンプカードの特徴]
                        </view>
                        <view class="introContent">
                            <view>
                                ・スタンプでのポイント付与カード。
                            </view>
                            <view>
                                ・スタンプ満了数は3個〜30個まで設定できます。
                            </view>
                            <view>
                                ・１度に１〜９個までのスタンプ付与が可能。
                            </view>
                            <view>
                                ・カード枠の色は自由に設定できます。
                            </view>
                            <view>
                                ・付与した日付、担当者(設定任意)が表示されます。
                            </view>
                            <view>
                                ・スタンプデザインはテンプレートから選択またはPNG ファイルアップロードでオリジナルデザイン設定が可能。
                            </view>
                        </view>
                    </view>
                </view>
                <view class="pItem border_top" style="margin-top: 100upx;padding-top: 38upx;">
                    <view class="itemTitle">
                        <view class="selectContent" @click="changeType(3)">
                            <view class="unselect" v-if="type != 3"></view>
                            <view class="selected" v-if="type == 3">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="typeName" :style="type == 3?'color:#1d1d1d' : ''">
                            ポイントカード
                        </view>
                    </view>
                    <image class="pitemImage" src="../../../static/images/typeC.png" mode="widthFix"></image>
                    <view class="introduce">
                        <view class="inTitle">
                            [ポイントカードの特徴]
                        </view>
                        <view class="introContent">
                            <view>
                                ・バーコードによるカード情報の読み込み。
                            </view>
                            <view>
                                ・利用金額に合わせたポイント付与が可能。
                            </view>
                            <view>
                                ・ポイント利用による増減もアプリからできます。
                            </view>
                            <view>
                                ・バーコード情報(番号)は編集はできません。
                            </view>
                            <view>
                                ・ポイント付与無し、保有ポイント数の非表示設定もできるので、来店履歴のみ管理する会員カードとして利用できます。
                            </view>
                        </view>
                    </view>
                </view>
            </view>
        </u-popup>
        <mod v-if="showModule" @getStatus="getStatus" :config="configs"></mod>
        <mod v-if="showModule1" @getStatus="getStatus1" :config="configs1"></mod>
        <view class="alertContent" v-if="k == true">
            <view class="alerts">
                <view class="t1">
                    クーポンを削除
                </view>
                <view class="t2">
                    このクーポンを削除しますか？
                </view>
                	<u-button text="削除" @click="deleteThis" style="background: #D93025;color: #fff;margin-top: 60px;margin-bottom: 30px;height: 48px;"></u-button>
                    <u-button text="キャンセル" @click="k=false" style="background: #EAEAEA;color: #707070;height: 48px;"></u-button>
            </view>
        </view>
        
    </view>
</template>
<script>
    import {getCardList,setDefaultCard,deleteMembershipCard,getShopInfo} from '@/api/index.js'
    import mod from "@/components/mod.vue"
    export default {
        components:{
            mod
        },
        data() {
            return {
                select: "",
                show: false,
                k:false,
                deleteitem:{},
                type:1,
                options: [{
                    text: '削除',
                    style: {
                        backgroundColor: '#D93025'
                    }
                }],
                deleteItem:{},
                array: [],
                showModule:false,
                configs:{},
                configs1:{},
                showModule1:false
            }
        },
        created() {
            let that = this
           
            getShopInfo().then((res) => {
                if(res.code == 200){
                    that.type = res.data.shop_info.default_card.type
                     that.getList()
                }
            })
        },
        methods: {
            goback(){
              uni.switchTab({
                  url:"../../setting/setting"
              })  
            },
            toEdit(id,item){
                let shop_name = encodeURIComponent(item.shop_name)
                uni.navigateTo({
                    url:"../membershipCard/editmembershipcard/editmembershipcard?id="+id+"&name="+shop_name+"&image="+item.image+"&type="+this.type
                })
            },
            getStatus1(e){
                if(e == 'cancel'){
                    this.deleteThis()
                }else{
                   this.showModule1 = false
                }
            },
            deleteThis(){
              let that = this 
              let e = that.deleteitem
              deleteMembershipCard(e.id).then((res) => {
                  that.showModule1 = false
                  if(res.code == 200){
                      uni.showToast({
                          title:res.message
                      })
                      that.getList()
                  }else{
                      uni.showToast({
                          title:res.message,
                          icon:"none"
                      })
                  }
              })  
            },
            showPop() {
                let that = this
                that.show = true
            },
            addNew() {
                uni.navigateTo({
                    url: "firstStep/firstStep?type="+this.type
                })
            },
            changeType(e){
                let that = this
                that.type = e
            },
            
            chooseItem(id,item){
                let that = this 
                that.deleteItem = item
                let obj = {}
                obj.title = item.name
                obj.content = 'に初回登録時のカードに変更しますか？'
                obj.confirm = true
                obj.cancel = true
                obj.confirmText = "変更する"
                obj.cancelText = "キャンセル"
                this.configs = obj
                this.showModule = true
            },
            getStatus(e){
                if(e == "confirm"){
                    let id = this.id
                   setDefaultCard(this.deleteItem.id).then((res) =>{
                       if(res.code == 200){
                           uni.showToast({
                               title:res.message
                           })
                       }else{
                           uni.showToast({
                               title:res.message,
                               icon:"none"
                           })
                       }
                       this.getList()
                   })
                   this.showModule = false
                }else{
                    this.showModule = false
                }
            },
            getList(){
                let that = this
                getCardList(that.type).then((res) => {
                    if(res.code == 200){
                        that.array = res.data.cards
                        that.show = false
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
            },
            deleteItems(e){
                let that = this
                that.deleteitem = e
                let obj = {}
                obj.title = 'このカード削除しますか？'
                obj.content = '削除すると、このカードの情報は全て削除されます。'
                obj.cancel = true
                obj.confirm = true
                obj.cancelText = "削除"
                obj.confirmText = "キャンセル"
                obj.type = "revers"
                this.configs1 = obj
                that.showModule1 = true
                
            }
        }
    }
</script>

<style lang="scss" scoped>
    .alertContent{
        position: fixed;
        width: 100vw;
        height: 100vh;
        top: 0;
        left: 0;
        background: rgba(29,29,31,0.2);
        z-index: 999 !important;
        
        .alerts{
            width: 345px;
            height: 306px;
            background: #FFFFFF;
            border-radius: 10px 10px 10px 10px;
            opacity: 1;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
            text-align: center;
            box-sizing: border-box;
            padding: 30px 52px;
            
            .er{
                height: 96upx;
                width: 100%;
                background: #D93025;
                color: #fff;
            }
            
            .t1{
                font-size: 32upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }
            
            .t2{
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;
                margin-top: 30upx;
            }
        }
    }
    .introduce {
        box-sizing: border-box;
        padding: 0 38upx;
        margin-top: 80upx;

        .inTitle {}

        .introContent {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;

            view {
                padding: 10upx 0
            }

            ;
        }
    }

    .pContent {
        width: 100%;
        height: 80vh;
        background: #fff;
        overflow-y: scroll;

        .pItem {
            box-sizing: border-box;
            padding: 0 20upx;
            margin-bottom: 80upx;

            .pitemImage {
                width: 100%;
            }
        }

        .itemTitle {
            display: flex;
            // margin-bottom: 20upx;
            align-items: center;

            .typeName {
                font-size: 40upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #AAAAAA;
                margin-left: 20upx;
            }
        }

        #title {
            box-sizing: border-box;
            padding: 80upx 0 126upx;
            text-align: center;
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: rgba(29, 29, 31, 0.9);
        }
    }

    .pTitle {
        height: 112upx;
        background: #FBFBFD;
        border-top-left-radius: 32upx;
        border-top-right-radius: 32upx;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding: 0 32upx;

        .pTitlename {
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .pSave {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
        }

        .pClose {
            width: 60upx;
            height: 60upx;

            image {
                width: 100%;
                height: 100%;
            }
        }
    }

    .itemTitle {
        font-size: 32upx;
        font-family: Hiragino Sans-W6, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
    }

    .itemLeft {
        margin-right: 30upx;
    }

    .items,
    .bk {
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        margin-top: 20upx;
    }

    .itemImage {
        width: 550upx;
        height: 328upx;
        background: #FFFFFF;
        box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);
        margin-top: 40upx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .listItem {
        display: flex;
        box-sizing: border-box;
        padding: 40upx;
    }

    .bk {
        display: flex;
        align-items: center;
    }

    .colorSqu {
        width: 200upx;
        height: 40upx;
        border-radius: 8upx;
        margin-left: 20upx;
    }

    .u-page {
        padding: 0;
    }

    .u-demo-block__title {
        padding: 10px 0 2px 15px;
    }

    .swipe-action {
        &__content {
            padding: 25rpx 0;

            &__text {
                font-size: 15px;
                color: $u-main-color;
                padding-left: 30rpx;
            }
        }
    }

    .addNew {
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 40upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
        margin-top: 60upx;
        box-sizing: border-box;
        padding: 40upx 0;
        box-shadow: 0px 8upx 10upx 1upx rgba(0, 0, 0, 0.16);

        text {
            margin-left: 10upx;
        }
    }

    .top1 {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        padding: 60upx 40upx 0;


        .topLeft {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-right: 36upx;
        }
    }

    .topRight {
        flex: 1;
    }

    .noData {
        width: 100%;
        margin-top: 192upx;
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        text-align: center;
    }

    .pickers {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        height: 80upx;
        background: rgba(249, 249, 249, 0.94);
        border-radius: 20upx;
        opacity: 1;
        border: 2upx solid #AAAAAA;
        box-sizing: border-box;
        padding: 0 20upx;

        .pickerName {
            flex: 1;
            text-align: center;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
        }
    }
</style>
