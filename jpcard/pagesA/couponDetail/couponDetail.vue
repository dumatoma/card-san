<template>
    <view>
        <u-navbar title="クーポン詳細" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56"
            :border="true" @leftClick="leftClick">
        </u-navbar>
        <view class="c-img" v-if="images.length > 0">
            <u-swiper :list="images" :autoplay="false" indicatorActiveColor="#191919" indicatorInactiveColor="rgba(0,0,0,.3)" indicator indicatorMode="dot" height="250"></u-swiper>
        </view>
        <view class="c-tit">
            クーポン番号
            <view class="c-sn" v-text="info.number"></view>
        </view>
        <view @click="openmask" class="ma" v-if="info.bar_code">
            <image :src="info.bar_code" style="width: 700rpx;height: 160rpx;" mode=""></image>
            <view class="ma-sn" v-text="info.bar_code_number"></view>
        </view>
        <view class="cou-box">
            <view class="c-title" v-text="info.title"></view>
            <view class="c-des" v-text="info.des" style="white-space: pre-wrap;"></view>
        </view>
        <view class="c-itms">
            <view class="validity-btn">
                有効期限
            </view>
            <view class="validity" v-if="info.in_date" v-text="`${info.in_date}まで有効`"></view>
            <view class="validity" v-else>無期限のクーポンです</view>
        </view>
        <view class="beizhu" v-if="info.count">
            <view class="bei-btn">
                　利用回数
            </view>
            <view class="bei">残り{{info.count}}回</view>
        </view>
        <view class="beizhu" v-if="info.remark">
            <view class="bei-btn">
                　備考　
            </view>
            <view class="bei" v-text="info.remark" style="white-space: pre-wrap;"></view>
        </view>
        <view class="mask" @click="showT = false" v-if="showT == true">
            <image class="bimage" :src="info.bar_code" mode=""></image>
        </view>
    </view>
</template>

<script>
    import {
        getCouponDetail
    } from "@/api/index.js"
    export default {
        data() {
            return {
                info: {},
                showT: false,
                images: [],
                imnt:true
            };
        },
        created() {

        },
        onLoad(options) {
            let query = options
            let id = query.id
            this.getDetail(id)
        },
        methods: {
            leftClick() {
                uni.navigateBack()
            },
            openmask() {
                this.showT = true
            },
            getDetail(id) {
                let that = this
                getCouponDetail(id).then((res) => {
                    if (res.code == 200) {
                        that.info = res.data.coupon
                        that.images = res.data.coupon.images
                    }
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    .mask {
        width: 100vw;
        height: 100vh;
        background: #fff;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 999 !important;

        .bimage {
            display: block;
            width: 140vw;
            height: 20vh;
            transform-origin: center;
            transform: rotate(90deg);
            position: absolute;
            top: 550upx;
            left: -20vw;
        }
    }

    .c-img {
        image {
            width: 100vw;
            height: 500rpx;
        }

        margin-bottom: 20rpx;
    }

    .c-tit {
        text-align: center;
        font-size: 32rpx;
        color: #1D1D1F;
        margin: 40rpx 0;
        border-bottom: 2upx solid #D2D2D7;

        .c-sn {
            font-size: 68rpx;
            color: #1A73E8;
            font-weight: 700;
        }
    }

    .ma {

        width: 95%;
        margin: 0 auto;
        padding-bottom: 100rpx;
        border-bottom: 1px solid #D2D2D7;
        text-align: center;

        image {
            width: 100%;
            height: 200rpx;
        }

        .ma-sn {
            margin-top: 10rpx;
            font-size: 32rpx;
            color: #1D1D1F;
        }
    }

    .cou-box {
        width: 100%;
        box-sizing: border-box;
        padding: 0 32upx 60rpx;
        margin: 0 auto;
        margin-top: 40rpx;
        border-bottom: 1px solid #D2D2D7;

        .c-title {
            color: #1D1D1F;
            font-weight: 700;
            font-size: 32rpx;
            margin-bottom: 40rpx;
        }

        .c-des {
            font-size: 32rpx;
            color: #707070;
        }
    }

    .c-itms {
        width: 90%;
        margin: 0 auto;
        margin-top: 40rpx;
        padding-bottom: 60rpx;
        border-bottom: 1px solid #D2D2D7;

        .validity-btn {
            width: 152rpx;
            height: 52rpx;
            line-height: 52rpx;
            font-size: 28rpx;
            color: #1A73E8;
            text-align: center;
            border-radius: 20rpx;
            border: 2rpx solid #1A73E8;
            margin-bottom: 20rpx;
        }

        .validity {
            color: #1D1D1F;
            font-size: 32rpx;
            font-weight: 700;

            text {
                font-weight: 400;
            }
        }
    }

    .beizhu {
        width: 100%;
        margin: 40rpx auto;
        box-sizing: border-box;
        padding: 0 32upx 60rpx;
        border-bottom: 1px solid #D2D2D7;

        .bei-btn {
            width: 152rpx;
            height: 52rpx;
            line-height: 52rpx;
            text-align: center;
            background: #F5F5F7;
            border-radius: 20rpx;
            font-size: 28rpx;
            color: #707070;
            border: 2rpx solid #707070;
            margin-bottom: 20rpx;
        }

        .bei {
            font-size: 32rpx;
            color: #1D1D1F;
        }
    }
</style>