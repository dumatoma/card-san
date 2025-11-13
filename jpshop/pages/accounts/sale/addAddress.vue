<template>
    <view>
        <view class="title border_bottom border_top">
            配送先住所を入力してください
        </view>
        <view class="addressContent">
            <view class="addressItem">
                <view class="addressItemTitle">
                    <text style="color: #D93025;">※</text>郵便番号
                </view>
                <view class="addressItemContent">
                    <view class="addItemLeft">
                        <u-input placeholder="ハイフンなし" v-model="info.postalCode"></u-input>
                    </view>
                    <view class="addItemRight">
                        <u-button type="primary" @click="checkAddress" text="住所検索" id="addBtn"></u-button>
                    </view>
                </view>
            </view>
            <view class="addressItem">
                <view class="addressItemContent" @click="co">
                    <view class="addItemLeft">
                        <view class="pickers">
                            <view class="pickerName" v-text="info.address"></view>
                            <u-icon name="arrow-down"></u-icon>
                        </view>
                    </view>
                </view>
            </view>
            <view class="infoItem">
                <view class="infoTitle">
                    <text style="color: #D93025;">※</text>住所1
                </view>
                <view class="infoDetail">
                    <u-input v-model="info.place1" placeholder="市区町村、番地"></u-input>
                </view>
            </view>
            <view class="infoItem">
                <view class="infoTitle">
                    住所2
                </view>
                <view class="infoDetail">
                    <u-input v-model="info.place2" placeholder="建物名、部屋番号"></u-input>
                </view>
            </view>
            <view class="infoItem">
                <view class="infoTitle">
                    <text style="color: #D93025;">※</text>電話番号
                </view>
                <view class="infoDetail">
                    <u-input v-model="info.phone" placeholder="ハイフンなし"></u-input>
                </view>
            </view>
            <view class="infoItem">
                <view class="infoTitle">
                    <text style="color: #D93025;">※</text>宛名
                </view>
                <view class="infoDetail">
                    <u-input v-model="info.name" placeholder=""></u-input>
                </view>
            </view>
        </view>
        <u-button text="この住所を使用" type="primary" class="btn" @click="save"></u-button>
        <view style="height: 200upx;"></view>
        <u-picker :show="show" @confirm="confirm" :columns="city"></u-picker>
    </view>
</template>

<script>
    import {
        addAddress,
        getAddress
    } from '@/api/index.js'
    export default {
        data() {
            return {
                show: false,
                info: {
                    phone: "",
                    name: "",
                    rest: "",
                    postalCode: "",
                    address: "都道府県",
                    place1: "",
                    place2: "",
                },
                city: [
                    ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都',
                        '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府',
                        '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県',
                        '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
                    ]
                ],
            };
        },
        created() {

        },
        methods: {
            checkAddress() {
                let that = this
                uni.showLoading({
                    title: "検索中"
                })
                getAddress(that.info.postalCode).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        that.info.address = res.data.address.address1
                        that.info.place1 = res.data.address.address1 + res.data.address.address2 + res.data
                            .address.address3
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },
            co() {
                this.show = true
            },
            confirm(e) {
                this.info.address = e.value[0]
                this.show = false
            },
            save() {
                let that = this
                let data = {}
                data.zip_code = that.info.postalCode
                data.province = that.info.address
                data.address1 = that.info.place1
                data.address2 = that.info.place2
                data.phone = that.info.phone
                data.name = that.info.name
                addAddress(data).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message,
                            duration:1500
                        })
                        setTimeout(function() {
                            let pages = getCurrentPages();
                            let currPage = pages[pages.length - 1] //当前页面
                            let prePage = pages[pages.length - 2] //上一个页面
                            //调用上一页拉取数据的方法
                            if (prePage.route == "pages/accounts/sale/secondStep") {
                                prePage.$vm.getAddressList()
                            }
                            uni.navigateBack({})
                        }, 1500)
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
    .btn {
        width: 670upx !important;
        height: 112upx !important;
        border-radius: 20upx !important;
        margin: 120upx auto 0 !important;
    }

    #addBtn {
        width: 200upx;
        height: 76upx;
        box-sizing: border-box;
        padding: 0 !important;
    }

    .title {
        font-size: 40upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        text-align: center;
        box-sizing: border-box;
        padding: 80upx 0 40upx;
        margin-bottom: 80upx;
    }

    .pTitle {
        position: relative;
        height: 111upx;
        background: #FBFBFD;
        border-radius: 32upx;

        .pt {
            width: 100%;
            text-align: center;
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            line-height: 111upx;
        }

        .closeImage {
            width: 60upx;
            height: 60upx;
            position: absolute;
            top: 26upx;
            left: 32upx;

            image {
                width: 100%;
                height: 100%;
            }
        }
    }

    .pContent {
        width: 100%;
        height: 80vh;
        overflow-y: scroll;
    }

    .blank {
        height: 120upx;
    }

    .switchItem {
        display: flex;
        box-sizing: border-box;
        padding: 44upx 36upx;
        justify-content: space-between;
        border-bottom: 2upx solid #d2d2d7;
    }

    #save {
        width: 480upx;
        margin-top: 120upx;
    }

    .basicInfo {
        box-sizing: border-box;
        padding: 60upx 46upx;
    }

    .addressContent {
        box-sizing: border-box;
        padding: 0 46upx;
    }

    .addItemLeft {
        width: 400upx;
        height: 88upx;
    }

    .addressItem {
        box-sizing: border-box;
        margin-bottom: 60upx;

        .addressItemTitle {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #86868B;
            margin-bottom: 10upx;
        }

        .addressItemContent {
            display: flex;
            justify-content: space-between;


            .addressItemContent {

                .addItemLeft {
                    width: 400upx;
                    height: 88upx;
                }

                .addItemRight {}
            }
        }
    }


    .tip {
        box-sizing: border-box;
        padding: 10upx 0 80upx 46upx;
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #AAAAAA;
    }

    .calendarShows {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 44upx 36upx;
    }

    .infoItem {
        margin-bottom: 60upx;
    }

    .infoTitle {
        margin-bottom: 10upx;
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
    }

    .pickers {
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
    }


    .xz {
        margin: 80upx 0 40upx;
        text-align: center;
        font-size: 22upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
    }

    .addImage {
        width: 480upx;
        height: 96upx;
        background: #F5F5F7;
        border: 2upx solid #707070;
        text-align: center;
        line-height: 96upx;
        margin: 0 auto 98upx;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32upx;

        text {
            font-size: 60upx;
            margin-right: 26upx;
            position: relative;
            top: -5upx;
        }
    }

    .topImage {
        box-sizing: border-box;
        padding-top: 40upx;
        display: flex;
        overflow-x: scroll;
        padding-left: 28upx;

        .bannerItem {
            width: 514upx;
            height: 344upx;
            flex-shrink: 0;
            margin-right: 46upx;
            position: relative;

            .close {
                width: 52upx;
                height: 52upx;
                position: absolute;
                right: 14upx;
                top: 14upx;
                z-index: 99;

                image {
                    width: 100%;
                    height: 100%;
                }
            }

            image {
                width: 100%;
                height: 100%;
            }
        }
    }
</style>
