<template>
    <view>
        <!-- <view class="topblank"></view> -->
        <u-navbar :leftIconColor="color" :title="shopName" :titleStyle="{'color':color}" :bgColor="bgColor"
            :safeAreaInsetTop="true" :border="true" :placeholder="true" @rightClick="rightClick" @leftClick="leftClick">
            <view class="u-nav-right" slot="right">
                <view :style="`backgroundImage: url(${svgData30})`" style="width: 50upx;height: 50upx;"
                    class="iconright" @click=""></view>
            </view>
        </u-navbar>
        <!-- <view style="height: 142upx;"></view> -->
        <view class="cou-list-box" style="box-sizing:border-box;padding-top: 40upx;">
            <view class="cou-item" v-for="(item,index) in couponList" :key="index" @click.stop="toDetail(item.id)">
                <view class="">
                    <!-- [カスタムエイド 2026-10-19] 画像が2枚以上のときだけ右下に枚数(詳細で全部見られる) -->
                    <view class="cou-img" v-if="item.images.length > 0">
                        <image :src="item.images[0]" mode="aspectFill"></image>
                        <view class="cs-count" v-if="item.images.length > 1">
                            <image src="../../static/svg/multiPhoto.svg" class="cs-count-icon"></image>
                            <text class="cs-count-num">{{item.images.length}}</text>
                        </view>
                    </view>
                    <view class="id" style="font-weight: bold;">
                        NO ：<text v-if="item.number">{{item.number}}</text><text v-else>-</text>
                    </view>
                    <view class="cou-title">
                        {{item.title}}
                    </view>
                    <view class="cou-des">
                        {{item.des}}
                    </view>
                    <view class="cou-time" v-if="item.in_date">
                        {{item.in_date}}<text>まで有効</text>
                    </view>
                    <view class="cou-time" v-else>
                        無期限のクーポンです
                    </view>
                </view>
                <view class="cou-btn" hover-class="hover">
                    クーポン詳細
                </view>
            </view>
        </view>
        <view style="height: 100upx;"></view>
        <list :caiDanShow="caiDanShow" :mid="id" :sid="sid" :tabs="tabs" @seed="seed" @selected="chooseItem"></list>
    </view>
</template>

<script>
    import list from '../../components/list.vue'
    import sp from "@/components/swiper-direct-com.vue"
    import svg30 from '../../static/svgjs/sv_30.js'
    import {
        getShopCoupon
    } from "@/api/index.js"
    export default {
        components: {
            list,
            sp
        },
        props: ["color", "id", "sid", "bgColor", "shopName", "tabs"],
        data() {
            return {
                caiDanShow: false,
                page: 1,
                couponList: [],
                svgData30: '"' + svg30 + '"',
            };
        },
        created() {
            let that = this
            that.svgData30 = that.changeColor(that.svgData30, that.color);
            that.getCoupon()
        },
        methods: {
            rightClick() {
                this.caiDanShow = true
            },
            changeColor(url, color) {
                let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#",
                "%23")); //转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
                return res;
            },
            scrollR() {

                this.caiDanShow = false
            },
            scrollL() {

            },
            toDetail(id) {
                uni.navigateTo({
                    url: "../couponDetail/couponDetail?id=" + id
                })
            },
            getCoupon() {
                let that = this
                let data = {}
                data.id = that.sid
                data.page = that.page
                getShopCoupon(data).then((res) => {
                    if (res.code == 200) {
                        that.couponList = res.data.coupons
                    }
                })
            },
            leftClick() {
                uni.navigateBack()
            },
            seed(e) {
                this.caiDanShow = false
            },
            chooseItem(e) {
                this.caiDanShow = false
                this.$emit("changes", e)
            }
        }
    }
</script>

<style lang="scss" scoped>
    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    .u-nav-right {
        image {
            width: 48rpx;
            height: 48rpx;
        }
    }

    .cou-list-box {
        width: 90%;
        margin: 0 auto;
        padding-bottom: 40rpx;
        
        .cou-item {
            width: 100%;
            // margin-top: 40rpx;
            background: #FFFFFF;
            box-shadow: 0px 0px 20rpx 2rpx rgba(29, 29, 31, 0.16);
            border-radius: 20rpx;
            padding-bottom: 40rpx;
            margin-bottom: 40upx;
            .cou-img {
                width: 100%;

                image {
                    border-radius: 20rpx;
                    width: 100%;
                    height: 456rpx;
                }
            }

            .id {
                box-sizing: border-box;
                padding: 40rpx 20rpx 0;
                color: #1D1D1F;
                font-size: 32rpx;
                font-weight: 700;

                text {
                    font-weight: 400;
                    color: #1A73E8;
                }
            }

            .cou-title {
                box-sizing: border-box;
                padding: 0 20rpx;
                margin-top: 40rpx;
                font-size: 32rpx;
                color: #1D1D1F;
                margin-bottom: 28rpx;
                font-weight: 700;
            }

            .cou-des {
                box-sizing: border-box;
                padding: 0 20rpx;
                font-size: 28rpx;
                color: #707070;
                margin-bottom: 60rpx;
            }

            .cou-time {
                font-size: 28rpx;
                color: #1D1D1F;
                text-align: center;
                margin-bottom: 40rpx;
                font-weight: 700;

                text {
                    font-weight: 400;
                }
            }

            .cou-btn {
                width: 480rpx;
                height: 96rpx;
                line-height: 96rpx;
                text-align: center;
                margin: 0 auto;
                border-radius: 12rpx;
                font-size: 32rpx;
                color: #FFFFFF;
                background-color: rgba(217, 48, 37, .6);
            }

            .hover {
                background-color: rgba(217, 48, 37, 1) !important;
            }
        }
    }

    /* ===== [カスタムエイド 2026-10-19] クーポン一覧の間延びを直す ===== */
    .cou-list-box {
        padding-top: 28rpx !important;
    }
    .cou-list-box .cou-item {
        margin-bottom: 28rpx !important;
        padding-bottom: 28rpx !important;
        border-radius: 24rpx !important;
        overflow: hidden;
        box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.08) !important;
        border: 1px solid #E8E8ED;
    }
    .cou-list-box .cou-item .cou-img {
        position: relative;
    }
    .cou-list-box .cou-item .cou-img image {
        height: 380rpx !important;
        border-radius: 0 !important;
        display: block;
    }
    .cou-list-box .cou-item .id {
        padding: 24rpx 28rpx 0 !important;
        font-size: 26rpx !important;
    }
    .cou-list-box .cou-item .cou-title {
        padding: 0 28rpx !important;
        margin-top: 10rpx !important;
        margin-bottom: 8rpx !important;
        line-height: 1.4;
    }
    .cou-list-box .cou-item .cou-des {
        padding: 0 28rpx !important;
        margin-bottom: 16rpx !important;
        font-size: 26rpx !important;
        line-height: 1.6;
    }
    .cou-list-box .cou-item .cou-time {
        margin-bottom: 20rpx !important;
        font-size: 26rpx !important;
    }
    .cou-list-box .cou-item .cou-btn {
        height: 88rpx !important;
        line-height: 88rpx !important;
        border-radius: 20rpx !important;
    }
    .cou-list-box .cou-item .cou-img .cs-count {
        position: absolute;
        right: 20rpx;
        bottom: 20rpx;
        height: 44rpx;
        padding: 0 14rpx;
        border-radius: 22rpx;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
    }
    .cou-list-box .cou-item .cou-img .cs-count .cs-count-icon {
        width: 24rpx !important;
        height: 24rpx !important;
        margin-right: 8rpx;
    }
    .cou-list-box .cou-item .cou-img .cs-count .cs-count-num {
        color: #FFFFFF;
        font-size: 24rpx;
    }
</style>