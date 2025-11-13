<template>
    <view>
        <u-navbar title="お気に入り" :border="true" :placeholder="true" :autoBack="true" :height="56">
        </u-navbar>
        <view v-for="(item,index) in list">
            <view class="box"> 
                <view class="title" style="display: flex;"> <text v-text="item.title" style=" flex: 1;"></text>
                    <image v-if="item.collect == 1" src="../../static/image/collection.png" @click="toCollect(item.id)" mode=""></image>
                </view>
                <view class="flex align-cen">
                    <view class="price" v-text="`￥${parseFormatNum(item.price,0)}`"></view>
                    
                    <view class="time" v-if="item.is_order == 1">所要時間：{{item.order_time}}分</view>
                </view>
                <view class="flex">
                    <view class="left">
                        <image :src="item.image[0]" mode="aspectFill" class="f-img"></image>
                        <view class="tuji" @click="showMo(index)">
                            <image src="../../static/image/heji.png" mode="" class="tu"></image>
                        </view>
                    </view>
                    <view class="right" style="white-space: pre-wrap;" v-text="item.des"></view>
                </view>
                <view class="yue" v-if="item.is_order == 1" @click="yuyue(item,item.sid)">
                    このメニューで予約する
                </view>
            </view>
            <view class="item1">
                <view class="flexs align-cen aps padding-bot w" @click="toshoP(item.mid,item.sid)">
                    <view class="tit" v-text="item.shop_name"></view>
                    <image src="../../static/image/arrow-right.png" mode=""></image>
                </view>
            </view>
        </view>
        <u-popup :show="shows" :round="10" mode="center" overlayOpacity="1">
            <view class="tu-list">
                <view class="icon" @click="close()">
                    <u-icon name="close" color="#FFFFFF" size="16"></u-icon>
                </view>
                <swiper :indicator-dots="false" :autoplay="false" :duration="1000" :circular="true" @change="change">
                    <swiper-item v-for="item in imgList" :key="item">
                        <view class="swiper-item">
                            <image :src="item" mode="" class="tu-img"></image>
                        </view>
                    </swiper-item>
                </swiper>
                <view class="shu-sum">
                    {{sum}} / {{imgList.length}}
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import {
        collectList,
        collectMenu
    } from "@/api/index.js"
    export default {
        data() {
            return {
                shows: false,
                sum: 1,
                page: 1,
                imgList: [],
                list: []
            };
        },
        created() {
            let that = this
            collectList(that.page).then((res) => {
                if (res.code == 200) {
                    that.list = res.data.menus
                }
            })
        },
        methods: {
            close() {
                this.shows = false
            },
            change(e) {
                this.sum = e.detail.current + 1
            },
            showMo(index){
                let that = this
                that.imgList = that.list[index].image
                that.shows = true
            },
            toCollect(id){
                let that = this
                collectMenu(id).then((res) => {
                    if(res.code == 200){
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                        collectList(that.page).then((res) => {
                            if (res.code == 200) {
                                that.list = res.data.menus
                            }
                        })
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
            },
            toshoP(mid,sid){
                uni.navigateTo({
                    url:"../../pagesA/shop/shop?id="+sid+"&cid="+mid
                })
            },
            yuyue(item,sid) {
                let that = this
                uni.setStorageSync("orderItem",item)
                uni.navigateTo({
                    url: '../../pagesA/yuYue/yuYue?id='+sid
                })
            },
        }
    }
</script>

<style lang="scss" scoped>
    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    // ::v-deep .u-icon__icon {
    //   color: #1D1D1F !important;
    // }

    .box,
    .item2 {
        width: 90%;
        margin: 0 auto;
        margin-top: 40rpx;

        .title {
            display: flex;
            font-size: 32rpx;
            color: #1D1D1F;
            margin-bottom: 28rpx;
            font-weight: 700;

            image {
                flex-shrink: 0;
                margin-left: 32rpx;
                width: 60rpx;
                height: 60rpx;
            }
        }



        .flex {
            display: flex;

            margin-top: 28rpx;
            font-size: 32rpx;

            .left {
                flex-shrink: 0;
                width: 246rpx;
                height: 246rpx;
                position: relative;
                border-radius: 12rpx;
                margin-right: 18rpx;

                .f-img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    border-radius: 12rpx;
                }

                .tuji {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 64rpx;
                    height: 64rpx;
                    background: rgba(29, 29, 31, 0.4);
                    border-radius: 12rpx;
                    text-align: center;

                    .tu {
                        margin-top: 6rpx;
                        width: 45rpx;
                        height: 51rpx;
                    }
                }
            }

            .right {
                font-size: 24rpx;
                color: #707070;
            }

            .price {
                color: #D93025;
            }

            .time {
                margin-left: 90rpx;
                color: #1D1D1F;
            }
        }

        .yue {
            width: 95%;
            margin: 0 auto;
            margin-top: 82rpx;
            margin-bottom: 60rpx;
            height: 88rpx;
            line-height: 88rpx;
            font-size: 32rpx;
            color: #1A73E8;
            border-radius: 26rpx;
            text-align: center;
            background-color: #F4F7FF;
            border: 2rpx solid #1A73E8;

            &:active {
                opacity: .7;
            }
        }

    }

    .padding-bot {
        padding-bottom: 54rpx;

    }

    .flexs {
        display: flex;
    }

    .align-cen {
        align-items: center;
    }

    .bot-des {
        margin-top: 28rpx;
        font-size: 24rpx;
        color: #707070;
    }

    .aps {
        justify-content: space-between;
    }

    .item1 {
        margin-top: 68rpx;
        border-bottom: 1px solid #D5D5D5;

        .w {
            width: 90%;
            margin: 0 auto;
        }

        .tit {
            font-size: 32rpx;
            color: #1D1D1F;
        }

        image {
            width: 38rpx;
            height: 60rpx;
        }
    }

    ::v-deep .u-popup__content {
        overflow: hidden;
    }

    .tu-list {
        width: 100vw;
        height: 100vh;
        position: relative;
        background-color: #000;

        .icon {
            position: absolute;
            top: 144rpx;
            left: 38rpx;
            z-index: 99;
        }

        .shu-sum {
            position: absolute;
            bottom: 122rpx;
            left: 50%;
            transform: translate(-50%, 0);
            color: #fff;
            font-size: 36rpx;
        }

        swiper {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translate(0, -50%);
            width: 100%;
            height: 560rpx;

            swiper-item {
                width: 100%;
                height: 100%;

                .swiper-item {
                    width: 100%;
                    height: 100%;

                    .tu-img {
                        width: 100%;
                        height: 100%;
                    }
                }
            }
        }
    }
</style>
