<template>
    <view class="co">
        <u-navbar title="予約詳細" :safeAreaInsetTop="true" :border="true" :placeholder="true" autoBack>
            <view class="u-nav-slot" slot="left">
                <u-icon name="arrow-left" size="19"></u-icon>
            </view>
            <!-- <view class="u-nav-slot" slot="right">
                <text style="color: #D93025;" @click="toEdit">編集</text>
            </view> -->
        </u-navbar>
        <view class="content">
            <view class="coTime" v-text="`${info.create_time}`"></view>
            <view class="item">
                <view class="item_left">
                    氏名
                </view>
                <view class="item_right">
                    <text style="color:#1A73E8" v-text="`${info.name}`"></text>
                </view>
            </view>
            <view class="item">
                <view class="item_left">
                    電話番号
                </view>
                <view class="item_right">
                    <text v-text="`${info.phone}`"></text>
                </view>
            </view>
            <view class="item">
                <view class="item_left">
                    会員ID
                </view>
                <view class="item_right">
                    <text v-text="`${info.member_id}`"></text>
                </view>
            </view>
            <view class="item">
                <view class="item_left">
                    予約番号
                </view>
                <view class="item_right">
                    <text v-text="`${info.order_no}`"></text>
                </view>
            </view>
        </view>
        <view class="content" v-if="info.menu != null">
            <view class="detail" v-text="`${info.menu.title}`"></view>
            <view class="tiem">
                <view class="time" v-text="`所要時間：${info.menu.order_time}分`"></view>
                <view class="price" v-text="`￥${info.menu.price}`"></view>
            </view>
        </view>
        <view class="content">
            <view class="detail">
                {{info.day}}  {{info.week}}曜日
            </view>
            <view class="detail" style="margin-top: 10upx;">
                {{info.time}}
            </view>
        </view>
        <view class="content">
           <view class="item">
                <view class="item_left">
                    指名担当
                </view>
                <view class="item_right">
                    <view class="squ" :style="info.appoint_admin? 'background:'+info.appoint_admin.color : 'background: #d2d2d7;'">
                    </view>
                    <view class="pname">
                        {{info.appoint_admin? info.appoint_admin.name : '指名無し'}}
                    </view>
                </view>
            </view>
            <view class="item">
                <view class="item_left">
                    当日担当
                </view>
                <view class="item_right">
                    <view class="squ" :style="`background: ${day_admin.color == ''?'#d2d2d7':day_admin.color}`"></view>
                    <view class="pname" v-text="day_admin.name"></view>
                </view>
            </view>
        </view>
        <view class="content">
            <view>メモ</view>
            <view style="margin-top: 32upx;" v-text="`${info.remark}`"></view>
        </view>
       <!-- <view class="tips">
            <view>予約内容の変更をお客さんにお知らせする場合</view>
            <view class="connect" @click="show1 = true">こちら</view>
        </view> -->
        <view class="btnBlue" @click="toEdit">
            予約内容を編集
        </view>
        <!-- <view class="btnRed" @click="deleteOrder">
            この予約を削除
        </view> -->
        <view class="blank"></view>
        <u-popup :show="show"></u-popup>
        <u-popup :show="show1" @close="show1 = false" @cancel="show1 = false" >
            <view class="maskContent">
                <view class="mkitem" style="color: #1A73E8;" @click="sendMessage">
                   <text>この内容を送る</text> <image src="../../../../static/images/send.png" mode="aspectFit"></image>
                </view>
                <view class="mkitem" style="color: #D93025;" @click="show1 = false">
                   <text>キャンセル</text>
                </view>
            </view>
        </u-popup>
        <u-popup :show="mask" mode="center" @cancel="mask = false" @close="mask = false">
            <view class="ms">
                予約内容変更メッセージ”の設定がOFFになっています。 メッセージを送るには、「メッセージ設定」の「予約内容変更」をONに設定してください。
            </view>
        </u-popup>
    </view>
</template>

<script>
    import {
        getAppointOrderDetail,
        deleteAppointOrder,
        cahngeInof
    } from '@/api/index.js'
    export default {
        data() {
            return {
                mask:false,
                show: false,
                info: {},
                id:"",
                day_admin:{},
                show1:false
            };
        },
        created() {
           
        },
        onLoad(options) {
            let that = this
            let query = options
            that.id = query.id
            that.getDetail(query.id)
            
        },
        onShow() {
            if(this.id){
                this.getDetail(this.id) 
            }
            
        },
        methods: {
            sendMessage(){
              let that = this
                 cahngeInof(that.info.id).then((res) => {
                     if(res.code == 200){
                         that.show1 = false
                         uni.showToast({
                             title:res.message
                         })
                     }else if(res.code == 400){
                         uni.hideToast()
                          that.show1 = false
                         that.mask = true
                     }else{
                         uni.showToast({
                             title:res.message,
                             icon:"none"
                         })
                     }
                 })
            },
            toEdit() {
                let that = this
                uni.navigateTo({
                    url: "../detail/edit?id="+that.id
                })
            },
            deleteOrder(){
              let that = this
              deleteAppointOrder(that.info.id).then((res) => {
                  if(res.code == 200){
                      uni.showToast({
                          title:res.message
                      })
                      setTimeout(() => {
                         uni.navigateBack() 
                      },1500)
                  }else{
                      uni.showToast({
                          title:res.message,
                          icon:"none"
                      })
                  }
              })
            },
            getDetail(id) {
                let that = this
                getAppointOrderDetail(id).then((res) => {
                    if (res.code == 200) {
                        that.info = res.data.order
                        that.day_admin = res.data.order.day_admin
                    } else {
                        uni.showToast({
                            title: res.message
                        })
                    }
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .blank{
        height: 100upx;
    }
    .ms{
        box-sizing: border-box;
        padding: 40upx;
        background: #fff;
        width: 70vw;
        border-radius: 28upx;
        text-align: center;
    }
    .mkitem{
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        width: 90%;
        margin: 20upx auto;
        border-radius: 28upx;
        box-sizing: border-box;
        padding: 36upx;
        font-size:40upx;
        image{
            width: 44upx;
            height: 44upx;
        }
    }
    // ::v-deep .u-popup__content{
    //     background: transparent !important;
    // }
    .maskContent{
        width: 100vw;
        background: transparent;
    }
    .btnBlue{
        font-size: 40upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
        text-align: center;
        margin-top: 96upx;
    }
    .btnRed{
        font-size: 40upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #D93025;
        text-align: center;
        margin-top: 72upx;
    }
    .co {
        box-sizing: border-box;
        padding: 0 40upx;

        .tips {
            box-sizing: border-box;
            padding: 28upx;
            background: #F5F5F7;
            color: #1D1D1F;
            border-radius: 20upx;
            font-size: 28upx;
            margin-top: 80upx;

            .connect {
                color: #1A73E8;
                margin-top: 20upx;
            }
        }

        .content {
            border-bottom: 2upx solid #d2d2d7;
            box-sizing: border-box;
            padding: 32upx 0 58upx 0;

            .detail {
                font-size: 32upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }

            .tiem {
                display: flex;
                align-items: center;
                font-size: 32upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                margin-top: 20upx;

                .price {
                    color: rgba(217, 48, 37, 0.65);
                }

                .time {
                    color: #1D1D1F;
                }

            }

            .coTime {
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #AAAAAA;
                text-align: right;
            }

            .item {
                display: flex;
                align-items: center;
                margin-top: 32upx;
                font-size: 32upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;

                .item_left {
                    width: 160upx;
                }

                .item_right {
                    display: flex;
                    align-items: center;

                    .squ {
                        width: 32upx;
                        height: 32upx;
                        border-radius: 8upx;
                        margin-right: 20upx;
                    }
                }
            }
        }
    }
</style>
