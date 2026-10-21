<template>
    <view class="contain">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="登録カード一覧" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-20] メンバーズカードの一覧(設定 > メンバーズカード)と同じ見た目に。ここでは「選ぶ」だけ(作成・編集・削除はしない) -->
        <view class="cs">
            <view class="cs-note">
                <text class="cs-bold">{{query.name}}</text> さんが使うカードを選んでください。選び直すと、今のカードのスタンプ数・ポイント数はリセットされます。
            </view>
            <view class="ml-empty" v-if="loaded && !array.length">登録されたカードはありません</view>
            <view class="ml-card" v-for="item in array" :key="item.id" :class="{ def: item.checked }" hover-class="ml-hover" @click="!item.checked && change(item)">
                <view class="ml-top">
                    <image class="ml-thumb" :src="item.image" mode="aspectFill"></image>
                    <view class="ml-text">
                        <view class="ml-tags">
                            <text class="ml-tag" :class="'t' + item.type">{{typeName(item.type)}}</text>
                            <text class="ml-tag ml-tag-def" v-if="item.checked">ご利用中</text>
                        </view>
                        <text class="ml-name">{{item.name}}</text>
                        <text class="ml-meta" :class="{ red: item.valid_text == '有効期限切れ' }">{{meta(item)}}</text>
                        <view class="ml-frame" v-if="item.type != 3 && (item.background_color || item.frame_image)">
                            <text>枠</text>
                            <view class="ml-frame-chip" :style="frameChip(item)"></view>
                        </view>
                    </view>
                    <view class="cs-radio" :class="{ on: item.checked }"><view class="cs-radio-in"></view></view>
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
                delInfo:{},
                loaded: false // [カスタムエイド 2026-10-20]
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
            // [カスタムエイド 2026-10-20] カードの種類の名前・説明・枠(メンバーズカードの一覧と同じ)
            typeName(t) {
                return t == 1 ? 'スタンプカードA' : t == 2 ? 'スタンプカードB' : 'ポイントカード'
            },
            meta(item) {
                const a = []
                if (item.type == 3) {
                    a.push(item.is_point == 1 ? item.amount + '円で' + item.point + 'ポイント' : 'ポイントなし')
                } else {
                    a.push('スタンプ' + item.count + '個')
                }
                if (item.valid_text == '有効期限切れ') {
                    a.push('有効期限切れ')
                } else if (item.valid_type == 1) {
                    a.push('有効期限 ' + (item.valid_day > 300 ? '発行から' + (item.valid_day / 365) + '年' : '発行から' + item.valid_day + '日'))
                } else {
                    a.push('有効期限 ' + (item.valid_text || 'なし'))
                }
                return a.join('・')
            },
            frameChip(item) {
                if (item.frame_image) {
                    return 'background-image: url(' + item.frame_image + '); background-size: cover; background-position: center;'
                }
                return 'background: ' + item.background_color + ';'
            },
            getList(){
                let that = this 
                getCardList(0).then((res) => {
                    that.loaded = true // [カスタムエイド 2026-10-20]
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
    /* [カスタムエイド 2026-10-20] メンバーズカードの一覧と同じ見た目 */
    .contain {
        min-height: 100vh;
        background: #F5F5F7;
    }

    .cs {
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .cs-note {
        padding: 20rpx 24rpx;
        margin-bottom: 24rpx;
        border-radius: 20rpx;
        background: #EAF2FE;
        font-size: 24rpx;
        line-height: 1.7;
        color: #3A3A3C;
    }

    .cs-bold {
        font-weight: 700;
    }

    .cs-radio {
        width: 44rpx;
        height: 44rpx;
        flex-shrink: 0;
        align-self: center;
        margin-left: 16rpx;
        border: 2px solid #C7C7CC;
        border-radius: 50%;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cs-radio-in {
        width: 20rpx;
        height: 20rpx;
        border-radius: 50%;
    }

    .cs-radio.on {
        border-color: #1A73E8;

        .cs-radio-in {
            background: #1A73E8;
        }
    }

    .ml-empty {
        padding: 80rpx 32rpx;
        text-align: center;
        font-size: 28rpx;
        color: #6E6E73;
        background: #FFFFFF;
        border-radius: 32rpx;
    }

    .ml-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 28rpx;
        margin-bottom: 24rpx;
    }

    .ml-card.def {
        box-shadow: 0 0 0 4rpx #1A73E8;
    }

    .ml-top {
        display: flex;
        align-items: flex-start;
    }

    .ml-thumb {
        width: 192rpx;
        height: 116rpx;
        flex-shrink: 0;
        border-radius: 16rpx;
        background: #E5E5EA;
        margin-right: 24rpx;
    }

    .ml-text {
        flex: 1;
        min-width: 0;
    }

    .ml-tags {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 8rpx;
    }

    .ml-tag {
        font-size: 22rpx;
        font-weight: 700;
        padding: 4rpx 16rpx;
        border-radius: 18rpx;
        margin: 0 12rpx 8rpx 0;
    }

    .ml-tag.t1 {
        background: #E6F4EA;
        color: #137333;
    }

    .ml-tag.t2 {
        background: #E8F0FE;
        color: #174EA6;
    }

    .ml-tag.t3 {
        background: #FEF3E0;
        color: #A34A00;
    }

    .ml-tag-def {
        background: #1A73E8;
        color: #FFFFFF;
    }

    .ml-name {
        display: block;
        font-size: 30rpx;
        font-weight: 700;
        line-height: 1.4;
        margin-bottom: 8rpx;
        word-break: break-all;
    }

    .ml-meta {
        display: block;
        font-size: 24rpx;
        color: #515154;
        line-height: 1.6;
    }

    .ml-meta.red {
        color: #D93025;
    }

    .ml-frame {
        display: flex;
        align-items: center;
        font-size: 24rpx;
        color: #515154;
        margin-top: 8rpx;
    }

    .ml-frame-chip {
        width: 80rpx;
        height: 28rpx;
        border-radius: 8rpx;
        margin-left: 12rpx;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.08);
    }

    .ml-hover {
        opacity: 0.6;
    }
</style>
