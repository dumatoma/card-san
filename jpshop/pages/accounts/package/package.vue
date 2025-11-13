<template>
    <view>
        <u-navbar title=" ご利用プラン " :safeAreaInsetTop="true" :placeholder="true" :autoBack="true">
            <view class="u-nav-slot" slot="right">
                <view style="font-size: 32upx;padding-right: 32upx;color: rgb(26, 115, 232);font-weight: bolder;" @click="ton">
                    保存
                </view>
            </view>
        </u-navbar>
        <view class="content">
            <view class="sq1">
                <view class="nameItem">
                    <view class="names">
                        ご利用中のプラン:
                    </view>
                    <view class="name" style="font-weight: bolder !important;margin-left: 12upx;" v-if="info.type">
                        {{info.type==2?'スタンダード':info.type == 1? 'ライト' : info.type == 3? 'プレミアム' : 'プレミアム'}}
                    </view>
                    <view class="name" style="font-weight: bolder !important;margin-left: 12upx;" v-if="info1.type && !info.type">
                        {{info1.type==2?'スタンダード':info1.type == 1? 'ライト' : info1.type == 3? 'プレミアム' : 'プレミアム'}}
                    </view>
                </view>
                <!-- <view class="nameItem" v-if="info.is_try != 1 && endTime != '1970/01/01'">
                    <view class="name" style="color:#D93025">
                        契约终止日:
                    </view>
                    <view class="name" style="color:#D93025">
                        {{endTime}}
                    </view>
                </view> -->
                <view class="using" v-if="info.is_try == 1" style="border-radius: 36upx;margin-top: 20upx;">
                    無料お試し期間中
                </view>
                <view class="tips" v-if="info.is_try == 1">
                    アカウント作成から30日間は全てのプランが無料お試し期間の対象です。　顧客登録数が30人以下の場合は、無料期間終了後も、全てのプランが無料でご利用できます。
                </view>
               <!-- <view class="using" v-if="info.is_try != 1 && endTime != '1970/01/01'" @click="continuePlan"
                    style="border-radius: 36upx;margin-top: 20upx;">
                    契約を続ける
                </view> -->
            </view>

            <view class="review" @click="show = true">
                各プランの機能一覧はこちら
            </view>

            <view class="packageItem"
                :style="current == index?'box-shadow: 0 0 5upx 5upx rgb(26,115,232, 0.6);border:1px solid rgb(26,115,232, 0.6)': '' "
                v-for="(item,index) in list">
                <view class="topcolor" style="background-color: #E8E8E8;color: #fff;" v-if="index == 0"></view>
                <view class="topcolor" style="background-color: #71D3D1;color: #fff;" v-if="index == 1">おすすめ</view>
                <view class="topcolor" style="background-color: #E6BF18;color: #fff;" v-if="index == 2">予約機能付き</view>
                <view class="top1">
                    <view class="packageName">
                        <view class="selectContents" @click="choosePlan(index)">
                            <view class="unselect" v-if="current != index"></view>
                            <view class="selected" v-if="current == index">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="realName" v-text="item.name"></view>
                    </view>
                    <view class="packPrice">
                        ¥{{ formatPriceWithCommas(item.price[1] / 6) }}~/月
                    </view>
                    <view class="introduce" v-text="item.des"></view>
                </view>
                <view v-for="(ite,idx) in item.items">
                    <view class="gitem" v-if="item.functions.includes(ite.e_name)" @click="openItems(index,idx)">
                        <view class="gLeft" v-text="ite.name"></view>
                        <u-icon name="arrow-right" color="#707070"
                            :style="item.functions.includes(ite.e_name) && ite.checked?'transform:rotate(90deg)' : ''"></u-icon>
                    </view>
                    <view class="gitem citem" v-if="item.functions.includes(ite.e_name) && ite.checked">
                        <image :src="ite.icon" mode=""></image>
                        <view class="dess" v-text="ite.des"></view>
                    </view>
                </view>
            </view>
            <view class="tips">注1)クーポン、メッセージチャットの配信数は、3,000件／月を超過した場合は、3,000件毎に1,200円／月の追加料金が発生します。</view>
            <view class="tips">注2) 予約SMS送信数は、100件を超過した場合は、100件毎に1,200円／月の追加料金が発生します。</view>
            <!-- <u-button class="cb" type="default" :plain="true" text="解約手続き" @click="deletePlan"></u-button>s -->
        </view>
        <u-popup mode="center" :show="show" round="20">
            <view class="pContent">
                <view class="close">
                    <image @click="show = false" src="../../../static/icons/tclose.png"></image>
                </view>
                <view class="pDetail">
                    <view class="pItem">
                        <view class="p1 titleWord">
                            機能比較
                        </view>
                        <view class="p2 titleWord">
                            ライト
                        </view>
                        <view class="p3 titleWord">
                            スタンダード
                        </view>
                        <view class="p4 titleWord">
                            プレミアム
                        </view>
                    </view>

                    <view class="pItem">
                        <view class="p1 normalWord">
                            メンバーズ <br />カード
                        </view>
                        <view class="p2">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p3">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p4">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                    </view>

                    <view class="pItem">
                        <view class="p1 normalWord">
                            店舗情報
                        </view>
                        <view class="p2">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p3">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p4">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                    </view>

                    <view class="pItem">
                        <view class="p1 normalWord">
                            営業カレンダー
                        </view>
                        <view class="p2">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p3">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p4">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                    </view>

                    <view class="pItem">
                        <view class="p1 normalWord">
                            メッセージ<br />
                            チャット
                        </view>
                        <view class="p2">
                            <view class="pItemD">
                                <image src="../../../static/images/wrong.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p3">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p4">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                    </view>


                    <view class="pItem">
                        <view class="p1 normalWord">
                            お知らせ投稿
                        </view>
                        <view class="p2">
                            <view class="pItemD">
                                <image src="../../../static/images/wrong.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p3">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p4">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                    </view>


                    <view class="pItem">
                        <view class="p1 normalWord">
                            メニュー表示
                        </view>
                        <view class="p2">
                            <view class="pItemD">
                                <image src="../../../static/images/wrong.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p3">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p4">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                    </view>


                    <view class="pItem">
                        <view class="p1 normalWord">
                            クーポン機能
                        </view>
                        <view class="p2">
                            <view class="pItemD">
                                <image src="../../../static/images/wrong.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p3">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p4">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                    </view>


                    <view class="pItem">
                        <view class="p1 normalWord">
                            予約機能
                        </view>
                        <view class="p2">
                            <view class="pItemD">
                                <image src="../../../static/images/wrong.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p3">
                            <view class="pItemD">
                                <image src="../../../static/images/wrong.png" mode=""></image>
                            </view>
                        </view>
                        <view class="p4">
                            <view class="pItemD">
                                <image src="../../../static/images/circle.png" mode=""></image>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import {
        getShopInfo,
        getPackageList,
        delPlan
    } from "@/api/index.js"
    export default {
        data() {
            return {
                checked: true,
                show: false,
                info: {
                    is_try: -1
                },
                endTime: "",
                list: [],
                current: 0,
                info1:{}
            };
        },
        created() {
            let that = this
            that.getShopInfo()
            that.getpackage()
        },
        methods: {
            formatPriceWithCommas(price) {
              return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            },
            getShopInfo() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        that.info = res.data.shop_info.vip
                        that.info1 = res.data.shop_info.last_vip
                        that.current = res.data.shop_info.vip.type * 1 - 1
                        let date = new Date(res.data.shop_info.vip.end_time * 1000)
                        const Y = date.getFullYear()
                        const M = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                        const D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate()
                        that.endTime = `${Y}/${M}/${D}`
                    }
                })
            },
            deletePlan() {
                let that = this
                delPlan().then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                        setTimeout(() => {
                            uni.navigateBack()
                        }, 1000)

                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },
            ton(){
                let that = this
                let type = that.current * 1 + 1
                uni.navigateTo({
                    url: '../../payment/payment?type=' + type
                })
            },
            continuePlan() {
                let that = this
                let type = that.info.type
                uni.navigateTo({
                    url: '../../payment/payment?type=' + type
                })
            },
            openItems(index, idx) {
                let that = this
                that.list[index].items[idx].checked = !that.list[index].items[idx].checked
            },
            choosePlan(e) {
                let that = this
                that.current = e
            },
            getpackage() {
                let that = this
                getPackageList().then((res) => {
                    if (res.code == 200) {
                        let arr = Object.values(res.data.vip_function)
                        let keys = []
                        for (let i in res.data.vip_function) {
                            keys.push(i)
                        }
                        arr.forEach((val, index) => {
                            val.e_name = keys[index]
                            val.checked = false
                        })
                        res.data.vip_contract.forEach((val, index) => {
                            val.items = arr
                        })
                        that.keys = keys
                        that.list = res.data.vip_contract
                        that.arrs = res.data.vip_function
                        that.list.forEach((value, idx) => {
                            value.items.forEach((v, i) => {})
                        })
                    }
                })
            }

        }
    }
</script>

<style lang="scss">
    .dess{
        font-size: 16px !important;
    }
    .tips {
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        margin-top: 24upx;
    }

    .cb {
        margin-top: 160upx;
        color: #aaa;
        border: 2upx solid #aaa;
    }

    .pContent {
        // height: 80vh;
        background: #fff;
        width: 95vw;
        border-radius: 20upx;
        height: 90vh;
        overflow-y: scroll;

        .normalWord {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: rgba(29, 29, 31, 0.9);
        }

        .titleWord {
            font-size: 24upx;
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;

        }

        .pItem:nth-child(even) {
            background: rgba(143, 168, 248, 0.1);
        }

        .pItem {
            display: flex;
            align-items: center;

            .pItemD {

                text-align: center;

                image {
                    width: 35upx;
                    height: 35upx;
                    border-radius: 50%;
                }

                .explain {
                    font-size: 20upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                    text-align: center;
                }
            }

            .p1 {
                width: 31%;
                box-sizing: border-box;
                padding: 30upx 22upx;
            }

            .p2 {
                width: 23%;
                box-sizing: border-box;
                padding: 30upx 22upx;
            }

            .p3 {
                width: 23%;
                box-sizing: border-box;
                padding: 30upx 22upx;
            }

            .p4 {
                width: 23%;
                box-sizing: border-box;
                padding: 30upx 22upx;
            }

        }


        .close {
            width: 100%;
            display: flex;
            flex-direction: row-reverse;
            box-sizing: border-box;
            padding: 26upx;

            image {
                width: 60upx;
                height: 60upx;
            }
        }
    }

    .content {
        background: #F5F5F7;
        box-sizing: border-box;
        padding: 30upx 32upx;

        .innerName {
            flex: 1;
            text-align: left;
            margin-left: 20upx;
        }

        .packageItem {
            width: 100%;
            box-shadow: 0px 0px 20upx 2upx rgba(0, 0, 0, 0.16);
            background: #fff;
            margin-top: 60upx;
            box-sizing: border-box;

            .innerItem {
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-sizing: border-box;
                padding: 38upx 32upx;
                border-bottom: 2upx solid #d2d2d7;

                .innerLeft {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    flex: 1;
                    box-sizing: border-box;
                    padding-right: 66upx;
                }
            }

            .topcolor {
                width: 100%;
                height: 60upx;
                text-align: center;
                line-height: 60upx;
            }

            .top1 {
                box-sizing: border-box;
                padding: 40upx 80upx 100upx;
            }

            .packageName {
                display: flex;
                justify-content: center;
                align-items: center;

                .realName {
                    margin-left: 40upx;
                    font-size: 52upx;
                    font-family: Hiragino Sans-W6, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                }
            }

            .packPrice {
                font-size: 40upx;
                font-family: Hiragino Sans-W6, Hiragino Sans;
                font-weight: normal;
                color: #707070;
                text-align: center;
                margin-top: 40upx;
            }

            .introduce {
                font-size: 32upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;
                margin-top: 40upx;
            }

            .gitem {
                box-sizing: border-box;
                padding: 40upx 32upx;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-top: 2upx solid #d2d2d7;

                image {
                    width: 60upx;
                    height: 60upx;
                    flex-shrink: 0 !important;
                    margin-right: 10upx;
                }
            }

            .citem {
                align-items: flex-start;
                background: #f5f5f7 !important;
            }
        }

        .review {
            height: 104upx;
            background: #FFFFFF;
            opacity: 1;
            border: 2upx solid #1A73E8;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
            text-align: center;
            line-height: 104upx;
            border-radius: 20upx;
            margin-top: 60upx;
        }

        .sq1 {
            background: #FFFFFF;
            box-shadow: 0px 0px 20px 2px rgba(0, 0, 0, 0.16);
            box-sizing: border-box;
            padding: 60upx 32upx;

            .using {
                height: 72upx;
                background: #1A73E8;
                font-size: 32upx;
                font-family: Hiragino Sans-W6, Hiragino Sans;
                font-weight: normal;
                color: #FFFFFF;
                text-align: center;
                line-height: 72upx;
            }

            .tips {
                background: rgba(143, 168, 248, 0.1);
                border-radius: 8upx;
                box-sizing: border-box;
                padding: 32upx;
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1A73E8;
                margin-top: 40upx;
            }

            .nameItem:first-child {
                margin-top: 0 !important;
            }

            .nameItem {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-top: 40upx;

                .name {
                    flex: 1;
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                    text-align: left;
                }
            }
        }
    }
</style>