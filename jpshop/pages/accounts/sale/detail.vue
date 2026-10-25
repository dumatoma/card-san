<template>
    <view>
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="販促アイテム" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <u-swiper :list="goods.carousel" height="250" @change="change" indicatorActiveColor="#000"
            indicatorInactiveColor="rgba(0,0,0,.3)" indicatorMode="dot" keyName="image" indicator></u-swiper>
        <view class="goodsContent">
            <view class="goodsName" v-text="goods.goods_name"></view>
            <view class="goodsPrice" v-text="`${goods.price}円`"></view>
            <view class="goodsIntro">
                <u-textarea v-model="goods.des" disabled autoHeight border="none"></u-textarea>
            </view>
            <view class="goodsAmount" @click="show = true">
                数量:{{number}}
                <u-icon name="arrow-down"></u-icon>
            </view>
        </view>
        <u-button text="次へ" type="warning" :plain="true" class="btn" @click="toNext"></u-button>
        <view class="" style="height: 40upx;">
            
        </view>
        <cs-picker title="数量を選ぶ" @close="show = false" @cancel="show = false" @confirm="confirm" :show="show" :columns="columns">
        </cs-picker>
    </view>
</template>

<script>
    import csPicker from "@/components/csPicker/csPicker.vue" // [カスタムエイド 2026-10-22] 新しいドラムロール
    import {
        getGoodsDetail
    } from "@/api/index.js"
    export default {
        components: { csPicker },
        data() {
            return {
                goods: {},
                columns: [
                    [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                ],
                number: 1,
                show: false
            }
        },
        created() {
            
        },
        onLoad(options) {
            let that = this
            let query = options
            that.getDetail(query.id)
        },
        methods: {
            change(e) {},
            getDetail(id) {
                let that = this
                getGoodsDetail(id).then((res) => {
                    if (res.code == 200) {
                        that.goods = res.data.goods
                    }
                })
            },
            toNext() {
                uni.navigateTo({
                    url: "../sale/secondStep?id=" + this.goods.id + "&number=" + this.number
                })
            },
            confirm(e) {
                this.show = false
                this.number = e.value[0]
            }
        }
    }
</script>

<style lang="scss">
    .goodsContent {
        box-sizing: border-box;
        padding: 40upx;

        .goodsAmount {
            width: 216upx;
            height: 84upx;
            background: #F5F5F7;
            box-shadow: 0px 0px 10upx 2upx rgba(0, 0, 0, 0.16);
            border-radius: 20upx;
            opacity: 1;
            border: 2upx solid #707070;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 28upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            box-sizing: border-box;
            padding: 0 28upx;
            margin-top: 80upx;
        }

        .goodsName {
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .goodsPrice {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-top: 22upx;
        }

        .goodsIntro {
            margin-top: 46upx;

        }
    }

    .btn {
        width: 580upx !important;
        height: 100upx !important;
        opacity: 1 !important;
        border-radius: 50upx !important;
        color: #ED8E20 !important;
        margin-top: 120upx !important;
    }
</style>
