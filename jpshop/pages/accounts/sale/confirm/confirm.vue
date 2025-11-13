<template>
    <view>
        <view class="content">
            <view class="title">
                ありがとうございます<br />
                ご注文手続きが完了しました
            </view>
            <view class="fu">
                ご注文の販促品は7日営業日以内<br />のお届けを予定しております。
            </view>
            <view class="innerContent border_bottom">
                <view class="innerItem">
                    <view class="itemLeft">
                        受注番号
                    </view>
                    <view class="itemRight" v-text="query.orderId"></view>
                </view>
                <view class="innerItem">
                    <view class="itemLeft">
                        商品名
                    </view>
                    <view class="itemRight" v-text="goodsinfo.goods_name"></view>
                </view>
                <view class="innerItem">
                    <view class="itemLeft">
                        小計
                    </view>
                    <view class="itemRight">
                        {{goodsinfo.price}}円(税込)
                    </view>
                </view>
                <view class="innerItem">
                    <view class="itemLeft">
                        数量
                    </view>
                    <view class="itemRight" v-text="query.number"></view>
                </view>
                <view class="innerItem">
                    <view class="itemLeft">
                        合計
                    </view>
                    <view class="itemRight">
                        {{goodsinfo.price * query.number}} 円(税込)
                    </view>
                </view>
            </view>

            <view class="innerContent border_bottom">
                <view class="innerItem" style="align-items: flex-start;">
                    <view class="itemLeft">
                        配送先
                    </view>
                    <view class="itemRight">
                        <view class="mgb_20" v-text="`〒${addressinfo.zip_code}`"></view>
                        <view class="mgb_20" v-text="addressinfo.address1"></view>
                        <view class="mgb_20" v-if="addressinfo.address2 != ''" v-text="addressinfo.address2"></view>
                        <view class="mgb_20" v-text="addressinfo.phone"></view>
                        <view v-text="addressinfo.name"></view>
                    </view>
                </view>
            </view>

            <view class="email" v-text="email"></view>
            <view class="remark">
                宛にご注文内容を送信しました。<br />
                ご確認ください。
            </view>
            <view class="but" @click="toIndex">
                TOPに戻る
            </view>
        </view>
    </view>
</template>

<script>
    import {
        getGoodsDetail,
        getAddressList
    } from "@/api/index.js"
    export default {
        data() {
            return {
                goodsinfo:{},
                addressinfo:{},
                orderId:"",
                email:uni.getStorageSync("admin").email
            };
        },
        created() {
            
        },
        onLoad(options) {
        },
        methods:{
            getGoodsDetail(id){
                let that = this 
                getGoodsDetail(id).then((res) => {
                    if(res.code == 200){
                        that.goodsinfo = res.data.goods
                    }
                })
            },
            getAddressList(aid){
                let that = this
                getAddressList().then((res) => {
                    let list = res.data.addrs
                    list.forEach((value,index) => {
                        if(value.id == aid){
                            that.addressinfo = value
                        }
                    })
                })
            },
            toIndex(){
                uni.switchTab({
                    url:"../../../index/index"
                })
            }
        }
    }
</script>

<style lang="scss">
    page {
        background-color: #f5f5f7;
        box-sizing: border-box;
        padding: 72upx 40upx;
    }

    .content {
        width: 100%;
        background-color: #fff;
        box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);
        border-radius: 20upx;
        box-sizing: border-box;
        padding: 78upx 64upx;

        .but {
            width: 232upx;
            height: 96upx;
            background: #D2D2D7;
            text-align: center;
            line-height: 96upx;
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            border-radius: 20upx;
            margin: 0 auto;
        }

        .remark {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            margin-bottom: 100upx;
        }

        .email {
            margin: 100upx 0 58upx;
            text-align: center;
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .innerContent {
            box-sizing: border-box;
            padding: 40upx 0;

            .innerItem {
                display: flex;
                align-items: center;
                margin-bottom: 24upx;

                .itemLeft {
                    width: 140upx;
                    font-size: 24upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                }

                .itemRight {
                    flex: 1;
                    font-size: 24upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }
            }
        }

        .title {
            font-size: 36upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            margin-bottom: 58upx;
        }

        .fu {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            padding-bottom: 80upx;
            border-bottom: 2upx solid #d2d2d7;
        }
    }
</style>
