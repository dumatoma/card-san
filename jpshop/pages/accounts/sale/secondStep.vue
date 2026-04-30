<template>
    <view>
        <view class="content border_top">
            <view class="goodsInfo border_bottom">
                <view class="goodsImage">
                    <image :src="goods.carousel[0]" mode="aspectFill"></image>
                </view>
                <view class="goodsInfos">
                    <view class="goodsName" v-text="goods.goods_name"></view>
                    <view class="goodsFormat">
                        <view class="goodsPrice" v-text="`${goods.price}円`"></view>
                        <view class="goodsAmount" v-text="`×${number}${unit}`"></view>
                    </view>
                    <view class="goodsTotal">
                        合計金額: {{total}}円 <text>(税込)</text>
                    </view>
                </view>
            </view>

            <view class="payMethods border_bottom" v-if="cardInfo.length > 0" @click="choosepay">
                <view class="title">
                    お支払い方法
                </view>
                <view class="payName">
                    <image src="../../../static/icons/cardLogo.png" mode=""></image>
                    <text v-text="payinfo.name || ''"></text>
                </view>
                <view class="payItem">
                    下４桁 {{payinfo.cvv}}
                </view>
                <view class="payItem" v-text="payinfo.card_no"></view>
                <view class="payItem">
                    有効期限　{{payinfo.expire_date}}
                </view>
            </view>

            <view class="addressContent" v-if="addressList.length > 0">
                <view class="title">
                    配送先住所
                </view>
                <view class="addressItem border_bottom" v-for="(item,index) in addressList">
                    <view class="addressLeft" v-if="addressList.length > 0">
                        <view class="selectContent" @click="current = index">
                            <view class="unselect" v-if="current != index"></view>
                            <view class="selected" v-if="current == index">
                                <view class="fill"></view>
                            </view>
                        </view>
                    </view>
                    <view class="addressRight">
                        <view class="addName" v-if="addressList.length > 0" v-text="item.name"></view>
                        <view class="addItem" v-if="addressList.length > 0">
                            〒{{item.zip_code}}
                        </view>
                        <view class="addItem" v-if="addressList.length > 0" v-text="`${item.province}${item.address1}`">
                        </view>
                        <view class="addItem" v-if="addressList.length > 0" v-text="item.phone"></view>
                        <!-- <view class="shopName">
                            TOP HAIR SPA&RESORT
                        </view> -->
                        <view class="addNew" @click="addNew">
                            別の住所に送る
                        </view>
                        <view class="addNew" style="color: #D93025;" v-if="current == index && addressList.length > 0"
                            @click="deleItem(index)">
                            この住所を削除
                        </view>
                    </view>

                </view>
            </view>

            <view class="btn" @click="confirmOrder">
                ご注文確定
            </view>
            <view style="height: 200upx;">

            </view>
        </view>
    </view>
</template>

<script>
    import {
        getGoodsDetail,
        getPayList,
        getAddressList,
        delAddress,
        orderConfirm,
        payOrder
    } from "@/api/index.js"
    export default {
        data() {
            return {
                goods: {},
                checked: true,
                number: 0,
                total: 0,
                cardInfo: [],
                addressList: [],
                current: 0,
                unit: "",
                payinfo: {}
            };
        },
        created() {

        },
        onShow() {
            let that = this
            getPayList().then((res) => {
                if (res.code == 200) {
                    if (res.data.cards.length > 0) {
                        that.cardInfo = res.data.cards
                        that.payinfo = res.data.cards[0]
                    } else {
                        uni.showToast({
                            title: "no card",
                            icon: "none",
                            duration: 2500
                        })
                        setTimeout(() => {
                            uni.navigateTo({
                                url: "../payment/payment"
                            })
                        }, 2500)
                    }
                }
            })
        },
        onLoad(options) {
            let that = this
            let query = options
            that.number = query.number
            that.getDetail(query.id)
        },
        methods: {
            choosepay() {
                let that = this
                uni.navigateTo({
                    url: "../payment/payment"
                })
            },
            getDetail(id) {
                let that = this
                getGoodsDetail(id).then((res) => {
                    if (res.code == 200) {
                        that.goods = res.data.goods
                        that.total = res.data.goods.price * that.number
                        that.unit = res.data.goods.units
                    }
                })

                getPayList().then((res) => {
                    if (res.code == 200) {
                        if (res.data.cards.length > 0) {
                            that.cardInfo = res.data.cards
                            that.payinfo = res.data.cards[0]
                        } else {
                            uni.showToast({
                                title: "no card",
                                icon: "none",
                                duration: 2500
                            })
                            setTimeout(() => {
                                uni.navigateTo({
                                    url: "../payment/payment"
                                })
                            }, 2500)
                        }
                    }
                })

                getAddressList().then((res) => {
                    that.addressList = res.data.addrs
                })
            },
            getAddressList() {
                let that = this
                getAddressList().then((res) => {
                    that.addressList = res.data.addrs
                })
            },

            deleItem(index) {
                let that = this
                delAddress(that.addressList[index].id).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                        that.getAddressList()
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },

            addNew() {
                uni.navigateTo({
                    url: "addAddress"
                })
            },

            confirmOrder() {
                let that = this
                let gid = that.goods.id
                let number = that.number
                if (that.addressList.length == 0) {
                    uni.showToast({
                        title: "住所を入力してください",
                        icon: "none",
                        duration: 2000
                    })
                    setTimeout(() => {
                        uni.navigateTo({
                            url: "../sale/addAddress"
                        })
                    })
                    return
                }
                uni.showLoading({
                    title: "読み込み中",
                    mask: true
                })
                let aid = that.addressList[that.current].id
                let data = {}
                data.gid = gid
                data.num = number * 1
                data.aid = aid
                orderConfirm(data).then((res) => {
                    if (res.code == 200) {
                        let tem = {}
                        tem.order_no = res.data.order_no
                        payOrder(tem).then((result) => {
                            uni.hideLoading()
                            if (result.code == 200 && result.data.url != '') {
                                setTimeout(function() {
                                    uni.navigateTo({
                                        url: "../../pautextr/pautextr?url=" + result
                                            .data.url
                                    })
                                }, 500)
                            } else {
                                uni.showToast({
                                    title: result.message,
                                    icon: "none",
                                    duration: 1500
                                })
                                setTimeout(function() {
                                    uni.switchTab({
                                        url: "../../index/index"
                                    })
                                }, 1500)
                            }
                        })


                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })

            }
        }
    }
</script>

<style lang="scss">
    .goodsPrice {
        margin-right: 30upx;
    }

    .btn {
        width: 580upx;
        height: 100upx;
        background: #ED8E20;
        border-radius: 50upx;
        opacity: 1;
        text-align: center;
        line-height: 100upx;
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #FFFFFF;
        margin: 100upx auto 0;
    }

    .content {
        box-sizing: border-box;
        padding: 0 40upx;

        .addressContent {
            box-sizing: border-box;
            padding: 44upx 0;

            .addressItem {
                display: flex;
                padding-bottom: 44upx;
                margin-bottom: 62upx;

                .addressLeft {
                    margin-right: 20upx;
                }

                .addItem {
                    font-size: 24upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                    margin-bottom: 20upx;
                }

                .addNew {
                    margin-top: 60upx;
                    font-size: 28upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1A73E8;
                }

                .shopName {
                    font-size: 24upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }

                .addName {
                    margin-bottom: 28upx;
                }
            }
        }

        .title {
            box-sizing: border-box;
            padding: 16upx 0;
            margin-bottom: 28upx;
            font-size: 28upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #707070;
        }

        .payMethods {
            box-sizing: border-box;
            padding: 44upx 0;

            .payName {
                display: flex;
                align-items: center;

                image {
                    width: 87upx;
                    height: 60upx;
                    margin-right: 20upx;
                }
            }

            .payItem {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                margin: 16upx 0;
            }
        }

        .goodsInfo {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 40upx 0;

            .goodsInfos {

                .goodsName {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }

                .goodsFormat {
                    display: flex;
                    align-items: center;
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                    margin: 24upx 0;
                }

                .goodsTotal {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;

                    text {
                        font-size: 24upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #1D1D1F;
                    }
                }
            }

            .goodsImage {
                width: 200upx;
                height: 200upx;
                border-radius: 12upx;
                overflow: hidden;
                margin-right: 28upx;

                image {
                    width: 100%;
                    height: 100%;
                }
            }
        }
    }
</style>