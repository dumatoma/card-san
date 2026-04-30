<template>
    <view class="contain">
        <view class="topUser">
            <text v-text="query.name"></text> のご利用中のカード
        </view>
        <view class="cards">
            <view class="cardItem" v-for="(item,index) in array" @click="change(item)"
                :style="item.checked? 'background:rgba(143,168,248,0.1)' : ''">
                <view class="itemLeft">
                    <view class="unselect" v-if="!item.checked"></view>
                    <view class="selected" v-if="item.checked">
                        <view class="fill"></view>
                    </view>
                </view>
                <view class="itemRight">
                    <view class="right1" v-text="item.name"></view>
                    <view class="right2" v-if="item.type != 3">
                        スタンプ数 : {{item.count}}個
                    </view>
                    <view class="right2" v-if="item.type == 3 && item.is_point == 1">
                        ポイント数 : {{item.amount}}円で{{item.point}}ポイント
                    </view>
                    <view class="right2" v-if="item.type == 3 && item.is_point == 0">
                        ポイント数 : -
                    </view>
                    <view class="right3" :style="item.valid_text == '有効期限切れ'? 'color:#D93025 !important' : '' " v-if="item.valid_type != 1">
                        有効期限：{{item.valid_text}}
                    </view>
                    <view class="right3" :style="item.valid_text == '有効期限切れ'? 'color:#D93025 !important' : '' " v-else>
                        有効期限：{{item.valid_day> 200? '発行から'+(item.valid_day / 365) + "年" : '発行から'+  item.valid_day  + "日"}}
                    </view>
                    <view class="right4">
                        <text>カード背景色</text>
                        <view class="sq" v-if="item.type != 3" :style="`background:${item.background_color}`"></view>
                        <view v-if="item.type == 3">:-</view>
                    </view>
                    <view class="right5">
                        <image :src="item.image" mode="aspectFill"></image>
                    </view>
                </view>
            </view>
        </view>
        <mod v-if="show" @getStatus="getsta" :config="config"></mod>
    </view>
</template>

<script>
    import {
        getCardList,
        changeDefaultCard
    } from '@/api/index.js'
    import mod from "@/components/mod.vue"
    export default {
        components:{mod},
        data() {
            return {
                array: [],
                query:{},
                show:false,
                config:{},
                delInfo:{}
            }
        },
        created() {},
        onLoad(options) {
            let that = this
            let query = options
            that.query = query
            that.getList()
        },
        methods: {
            getList(){
                let that = this 
                getCardList(0).then((res) => {
                    if(res.code == 200){
                        res.data.cards.forEach((val,index) => {
                            if(val.id == that.query.id){
                                val.checked = true
                            }else{
                                val.checked = false
                            }
                        })
                        that.array = res.data.cards
                    }
                })
            },
            getsta(e){
                let that = this 
              if(e == 'cancel'){
                  this.show = false
              }else{
                  
                  let data = {}
                  data.cid = that.delInfo.id
                  data.mid = that.query.userId
                  changeDefaultCard(data).then((res) => {
                      that.show = false
                      if(res.code == 200){
                          uni.showToast({
                              title:res.message,
                              icon:"none"
                          })
                         
                          for (let i = 0; i < that.array.length; i++) {
                              if (that.delInfo.id == that.array[i].id) {
                                   console.log(12312313)
                                  that.array[i].checked = true
                              } else {
                                  that.array[i].checked = false
                              }
                          }
                      }else{
                         uni.showToast({
                             title:res.message,
                             icon:"none"
                         }) 
                      }
                  })  
              }
            },
            change(e) {
                console.log(e)
                let that = this
                let config = {}
                config.title = e.name + '<br/>にカードを変更しますか？'
                config.content = '変更すると、現在ご利用中のカードのスタンプ数・ポイント数はリセットされます。'
                config.confirmText = '変更する'
                config.cancelText = 'キャンセル'
                config.confirm = true
                config.cancel = true
                that.config = config
                that.show = true
                that.delInfo = e
               
            }
        }
    }
</script>

<style lang="scss">
    .topUser {
        box-sizing: border-box;
        padding: 80upx 40upx 40upx;
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        box-shadow: 0px 0px 10upx 2upx rgba(0, 0, 0, 0.16);
        border-top: 2upx solid #d2d2d7;

        text {
            font-weight: bold;
        }
    }

    .right1 {
        font-size: 32upx;
        font-family: Hiragino Sans-W6, Hiragino Sans;
        color: #1D1D1F;
        font-weight: bold;
        margin-bottom: 20upx;
    }

    .right2 {
        font-size: 32upx;
        font-family: Hiragino Sans-W6, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        margin-bottom: 20upx;
    }

    .right3 {
        font-size: 32upx;
        font-family: Hiragino Sans-W6, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        margin-bottom: 20upx;
    }

    .right4 {
        display: flex;
        align-items: center;
        font-size: 32upx;
        font-family: Hiragino Sans-W6, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        margin-bottom: 40upx;

        .sq {
            width: 200upx;
            height: 40upx;
            background: #53B1AF;
            box-shadow: 0px 0px 6upx 2upx rgba(0, 0, 0, 0.16);
            border-radius: 8upx;
            margin-left: 20upx;
        }
    }

    .right5 {
        width: 550upx;
        height: 328upx;
        background: #FFFFFF;
        box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);

        image {
            width: 100%;
            height: 100%;
        }
    }

    .cardItem {
        display: flex;
        box-sizing: border-box;
        padding: 40upx 40upx 0;
        padding-right: 0;
    }

    .itemLeft {
        margin-right: 24upx;
        flex-shrink: 0;
    }

    .itemRight {
        flex: 1;
        border-bottom: 2upx solid #D2D2D7;
        padding-bottom: 80upx;
    }

    .unselect {
        width: 40upx;
        height: 40upx;
        background: #FFFFFF;
        opacity: 1;
        box-sizing: border-box;
        border: 2upx solid #707070;
        border-radius: 50%;
    }

    .selected {
        width: 40upx;
        height: 40upx;
        background: #FFFFFF;
        border: 2upx solid #1A73E8;
        border-radius: 50%;
        box-sizing: border-box;
        padding: 6upx;

        .fill {
            width: 100%;
            height: 100%;
            background: #1A73E8;
            border-radius: 50%;
        }
    }
</style>
