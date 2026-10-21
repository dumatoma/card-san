<template>
    <view>
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="編集" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-20] 見た目をこれまでの画面(白い角丸のまとまり・入力欄の枠・保存ボタン)に合わせ、間延びをなくした -->
        <view class="ci">
            <view class="ci-card">
                <view class="ci-field">
                    <view class="ci-label"><text class="ci-req">※</text>氏名</view>
                    <input class="ci-in" :class="{ on: fk == 'name' }" v-model="info.name" placeholder="例:山田 花子" placeholder-class="ci-ph"
                        @focus="fk = 'name'" @blur="fk = ''" />
                </view>
                <view class="ci-field">
                    <view class="ci-label">法人名</view>
                    <input class="ci-in" :class="{ on: fk == 'fr' }" v-model="info.frName" placeholder="会社名・法人名" placeholder-class="ci-ph"
                        @focus="fk = 'fr'" @blur="fk = ''" />
                </view>
                <view class="ci-field">
                    <view class="ci-label"><text class="ci-req">※</text>店舗名</view>
                    <input class="ci-in" :class="{ on: fk == 'shop' }" v-model="info.shopName" placeholder="例:CARD SALON" placeholder-class="ci-ph"
                        @focus="fk = 'shop'" @blur="fk = ''" />
                </view>
                <view class="ci-field">
                    <view class="ci-label"><text class="ci-req">※</text>電話番号</view>
                    <input class="ci-in" :class="{ on: fk == 'tel' }" type="number" v-model="info.phone" placeholder="ハイフンなし" placeholder-class="ci-ph"
                        @focus="fk = 'tel'" @blur="fk = ''" />
                </view>
            </view>

            <view class="ci-h3">住所</view>
            <view class="ci-card">
                <view class="ci-field">
                    <view class="ci-label">郵便番号</view>
                    <view class="ci-zip">
                        <input class="ci-in ci-zip-in" :class="{ on: fk == 'zip' }" type="number" v-model="info.postalCode" placeholder="ハイフンなし" placeholder-class="ci-ph"
                            @focus="fk = 'zip'" @blur="fk = ''" />
                        <view class="ci-zip-btn" hover-class="ci-hover" @click="searchAddress">住所検索</view>
                    </view>
                </view>
                <view class="ci-gray">
                    <view>個人:オーナーの住所または店舗所在地の住所をご記載ください。</view>
                    <view>法人:法人登記住所または店舗所在地の住所をご記載ください。</view>
                </view>
                <view class="ci-field">
                    <view class="ci-label">都道府県</view>
                    <view class="ci-in ci-select" hover-class="ci-hover" @click="show = true">
                        <text :class="{ 'ci-ph': !info.province }">{{info.province || '選択してください'}}</text>
                        <view class="ci-caret"></view>
                    </view>
                </view>
                <view class="ci-field">
                    <view class="ci-label"><text class="ci-req">※</text>住所1</view>
                    <input class="ci-in" :class="{ on: fk == 'a1' }" v-model="info.address1" placeholder="市区町村・番地" placeholder-class="ci-ph"
                        @focus="fk = 'a1'" @blur="fk = ''" />
                </view>
                <view class="ci-field">
                    <view class="ci-label">住所2</view>
                    <input class="ci-in" :class="{ on: fk == 'a2' }" v-model="info.address2" placeholder="建物名・部屋番号" placeholder-class="ci-ph"
                        @focus="fk = 'a2'" @blur="fk = ''" />
                </view>
            </view>

            <view class="ci-save" :class="{ dis: !ready }" hover-class="ci-save-hover" @click="save">登録する</view>
        </view>
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
                show: false,
                fk: '' // [カスタムエイド 2026-10-20] いま入力中の欄(青い枠にする)
            };
        },
        created() {
            let that = this
            that.getOwnerInformation()
        },
        computed: {
            // [カスタムエイド 2026-10-20] 必須がそろうと保存ボタンを濃い青に
            ready() {
                const i = this.info
                return !!(i.name && i.shopName && i.phone && i.address1)
            }
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
                }).catch(() => {})
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
                }).catch(() => { uni.hideLoading() })
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
                    uni.showToast({ title: '※の項目を入力してください', icon: 'none' }) // [カスタムエイド 2026-10-20] 今までは何も出なかった
                    return false
                }
                setOwnerInformation(data).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                        setTimeout(function() {
                            let pages = getCurrentPages();
                            let currPage = pages[pages.length - 1]
                            let prePage = pages[pages.length - 2]
                            if (prePage.route == "pages/accounts/admin/admin") {
                                prePage.$vm.getOwnerInformation()
                            }
                            uni.navigateBack({})
                        }, 1500)
                    }
                }).catch(() => {})
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-20] これまでの画面と同じ見た目 */
    .ci {
        min-height: 100vh;
        background: #F5F5F7;
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
        box-sizing: border-box;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .ci-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 32rpx;
        margin-bottom: 40rpx;
    }

    .ci-h3 {
        margin: 0 8rpx 16rpx;
        font-size: 30rpx;
        font-weight: 700;
        color: #515154;
    }

    .ci-field {
        margin-bottom: 28rpx;
    }

    .ci-field:last-child {
        margin-bottom: 0;
    }

    .ci-label {
        font-size: 26rpx;
        font-weight: 500;
        margin-bottom: 12rpx;
    }

    .ci-req {
        color: #C5221F;
    }

    .ci-in {
        width: 100%;
        height: 88rpx;
        padding: 0 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        background: #FFFFFF;
        font-size: 30rpx;
        color: #1D1D1F;
        box-sizing: border-box;
        transition: border-color .15s, box-shadow .15s;
    }

    .ci-in.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    .ci-ph {
        color: #8A8A8E;
    }

    .ci-hover {
        opacity: 0.6;
    }

    .ci-zip {
        display: flex;
        align-items: center;
    }

    .ci-zip-in {
        flex: 1;
        min-width: 0;
    }

    .ci-zip-btn {
        flex-shrink: 0;
        height: 88rpx;
        line-height: 88rpx;
        margin-left: 16rpx;
        padding: 0 32rpx;
        border-radius: 24rpx;
        background: #E8F0FE;
        color: #1A73E8;
        font-size: 28rpx;
        font-weight: 500;
    }

    .ci-gray {
        padding: 20rpx 24rpx;
        border-radius: 20rpx;
        background: #F5F5F7;
        font-size: 24rpx;
        line-height: 1.7;
        color: #515154;
        margin-bottom: 28rpx;
    }

    .ci-select {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* 下向きの矢印 */
    .ci-caret {
        width: 16rpx;
        height: 16rpx;
        border-right: 3rpx solid #6E6E73;
        border-bottom: 3rpx solid #6E6E73;
        transform: translateY(-4rpx) rotate(45deg);
    }

    .ci-save {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
    }

    .ci-save.dis {
        background: #C6DAFC;
    }

    .ci-save-hover {
        opacity: 0.8;
    }

    .pTitle {
        position: relative;
        height: 112rpx;
        line-height: 112rpx;
        text-align: center;
        font-size: 34rpx;
        font-weight: 700;

        .closeImage {
            position: absolute;
            left: 32rpx;
            top: 26rpx;
            width: 60rpx;
            height: 60rpx;

            image {
                width: 60rpx;
                height: 60rpx;
            }
        }
    }
</style>
