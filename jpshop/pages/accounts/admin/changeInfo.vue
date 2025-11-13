<template>
    <view>
        <view class="basicInfo">
            <view class="infoItem">
                <view class="infoTitle">
                    <text style="color: #D93025;">※</text>氏名
                </view>
                <view class="infoDetail">
                    <u-input placeholder="" v-model="info.name"></u-input>
                </view>
            </view>

            <view class="infoItem">
                <view class="infoTitle">
                    法人名
                </view>
                <view class="infoDetail">
                    <u-input placeholder="会社名・法人名" v-model="info.frName"></u-input>
                </view>
            </view>

            <view class="infoItem">
                <view class="infoTitle">
                    <text style="color: #D93025;">※</text>店舗名
                </view>
                <view class="infoDetail">
                    <u-input placeholder="" v-model="info.shopName"></u-input>
                </view>
            </view>

            <view class="infoItem">
                <view class="infoTitle">
                    <text style="color: #D93025;">※</text>電話番号
                </view>
                <view class="infoDetail">
                    <u-input placeholder="ハイフンなし" v-model="info.phone"></u-input>
                </view>
            </view>
        </view>
        <view class="addressContent">
            <view class="addressItem">
                <view class="addressItemTitle">
                    郵便番号
                </view>
                <view class="addressItemContent">
                    <view class="addItemLeft">
                        <u-input placeholder="ハイフンなし" v-model="info.postalCode"></u-input>
                    </view>
                    <view class="addItemRight">
                        <u-button type="primary" text="住所検索" id="addBtn" @click="searchAddress"></u-button>
                    </view>
                </view>
            </view>
            <view class="tips">
                <view>個人:オーナーの住所または店舗所在地の住所をご記載ください。</view>
                <view>法人:法人登記住所または店舗所在地の住所をご記載ください。</view>
            </view>
            <view class="addressItem">
                <view class="addressItemContent">
                    <view class="addItemLeft">
                        <view class="pickers" @click="show = true">
                            <view class="pickerName" v-text="info.province || '都道府県' "></view>
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
                    <u-input v-model="info.address1" placeholder="市区町村・番地"></u-input>
                </view>
            </view>
            <view class="infoItem">
                <view class="infoTitle">
                    住所2
                </view>
                <view class="infoDetail">
                    <u-input v-model="info.address2" placeholder="建物名・部屋番号"></u-input>
                </view>
            </view>
        </view>
        <u-button text="登録する" class="bbtn" :class="info.name !='' && info.shopName != '' && info.phone != '' && info.address1 != ''? 'po' :'' " type="primary" @click="save"></u-button>
        <view class="blank"></view>
        <u-popup :show="showPopup" round="32">
            <view class="pTitle">
                <view class="closeImage">
                    <image src="../../../static/icons/tclose.png" @click="showPopup = false" mode=""></image>
                </view>
                <view class="pt">
                    プレビュー
                </view>
            </view>
            <view class="pContent">
                <preview :infos="info"></preview>
            </view>
        </u-popup>
        <u-picker :closeOnClickOverlay="true" @close="show = false" cancelText="キャンセル" confirmText="保存"
            @confirm="confirm" @cancel="show = false" :show="show" :columns="city"></u-picker>
    </view>
</template>

<script>
    import preview from "@/components/preview/preview.vue"
    import {
        getOwnerInformation,
        getAddress,
        setOwnerInformation
    } from '@/api/index.js'
    export default {
        components: {
            preview
        },
        data() {
            return {
                showPopup: false,
                info: {
                    name: "",
                    frName: "",
                    phone: "",
                    shopName: "",
                    postalCode: "",
                    address: "",
                    address1: "",
                    address2: "",
                    province: ""
                },
                city: [
                    ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都',
                        '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府',
                        '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県',
                        '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
                    ]
                ],
                show: false
            };
        },
        created() {
            let that = this
            that.getOwnerInformation()
        },
        methods: {
            getOwnerInformation() {
                let that = this
                getOwnerInformation().then((res) => {
                    if (res.code == 200) {
                        let info = uni.getStorageSync("admin")
                        that.info.name = res.data.owner.name
                        that.info.phone = res.data.owner.phone
                        that.info.frName = res.data.owner.legal_person
                        that.info.postalCode = res.data.owner.zip_code
                        that.info.address1 = res.data.owner.address1
                        that.info.address2 = res.data.owner.address2
                        that.info.shopName = res.data.owner.shop_name
                        that.info.province = res.data.owner.province
                    }
                })
            },
            searchAddress() {
                let that = this
                uni.showLoading({
                    title: "読み込み中"
                })
                getAddress(that.info.postalCode).then((res) => {
                    if (res.code == 200) {
                        that.info.province = res.data.address.address1
                        that.info.address1 = res.data.address.address2 + res.data.address.address3
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                    uni.hideLoading()
                })
            },
            confirm(e) {
                this.info.province = e.value[0]
                this.show = false
            },
            save() {
                let that = this
                let data = {}
                data.name = that.info.name
                data.legal_person = that.info.frName
                data.shop_name = that.info.shopName
                data.phone = that.info.phone
                data.zip_code = that.info.postalCode
                data.province = that.info.province
                data.address1 = that.info.address1
                data.address2 = that.info.address2
                
                if(that.info.shopName == '' ||  that.info.name == '' || that.info.phone == '' || that.info.address1 == ''){
                    return false
                }
                setOwnerInformation(data).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                        setTimeout(function() {
                            let pages = getCurrentPages();
                            let currPage = pages[pages.length - 1] //当前页面
                            let prePage = pages[pages.length - 2] //上一个页面
                            //调用上一页拉取数据的方法
                            if (prePage.route == "pages/accounts/admin/admin") {
                                prePage.$vm.getOwnerInformation()
                            }
                            uni.navigateBack({})
                        }, 1500)
                    }
                })
            }
        }
    }
</script>

<style lang="scss">
    .bbtn {
        width: 670upx !important;
        height: 100upx !important;
        background: rgb(26, 115, 232);
        border-radius: 20upx !important;
        opacity:0.6
    }
    
    .po{
         opacity:1 !important;
    }

    .tips {
        box-sizing: border-box;
        padding: 30upx;
        width: 100%;
        font-size: 20upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
        background: rgba(143, 168, 248, 0.2);
        border-radius: 20upx;
        margin-bottom: 60upx;
    }

    #addBtn {
        width: 200upx;
        height: 76upx;
        box-sizing: border-box;
        padding: 0 !important;
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
        padding: 60upx 46upx 0;
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

    .title {
        background: #f5f5f7;
        box-sizing: border-box;
        padding: 44upx 46upx;
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: rgba(29, 29, 31, 0.9);
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
