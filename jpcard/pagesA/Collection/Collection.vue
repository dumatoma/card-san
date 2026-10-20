<template>
    <view>
        <u-navbar title="お気に入り" :border="true" :placeholder="true" :autoBack="true" :height="56">
        </u-navbar>
        <!-- [カスタムエイド 2026-10-19] お気に入りを1つずつ白い角丸の枠に入れる(メニュー一覧と同じ形)。
             価格は濃い文字の太字で所要時間と1行、写真は2枚以上のときだけ右下に枚数、押したら全画面 -->
        <view class="cs-list">
            <!-- [カスタムエイド 2026-10-20] お店が削除したメニューは灰色にし、「お取り扱いがありません」と「お気に入りから外す」を出す(一番下に並べる) -->
            <view class="box" :class="{ 'cs-off': item.deleted == 1 }" v-for="(item,index) in list" :key="item.id">
                <view class="title">
                    <text class="title-txt" v-text="item.title"></text>
                    <image v-if="item.collect == 1" src="../../static/image/collection.png" @click="toCollect(item.id)" mode=""></image>
                </view>
                <view class="meta">
                    <text class="price" v-text="`￥${parseFormatNum(item.price,0)}`"></text>
                    <text class="time" v-if="item.is_order == 1">　・　所要時間 {{item.order_time}}分</text>
                </view>
                <view class="cs-offmsg" v-if="item.deleted == 1">このメニューは現在お取り扱いがありません</view>
                <view class="body" v-if="(item.image && item.image.length) || item.des">
                    <view class="left" v-if="item.image && item.image.length" @click="openPhotos(item.image, 0)">
                        <image :src="item.image[0]" mode="aspectFill" class="f-img"></image>
                        <view class="cs-count" v-if="item.image.length > 1">
                            <image src="../../static/svg/multiPhoto.svg" class="cs-count-icon"></image>
                            <text class="cs-count-num">{{item.image.length}}</text>
                        </view>
                    </view>
                    <view class="right" v-text="item.des"></view>
                </view>
                <view class="yue" v-if="item.is_order == 1 && item.deleted != 1" @click="yuyue(item,item.sid)">
                    このメニューで予約する
                </view>
                <view class="yue cs-remove" v-if="item.deleted == 1" @click="toCollect(item.id)">
                    お気に入りから外す
                </view>
                <view class="shop" @click="toshoP(item.mid,item.sid)">
                    <text class="tit" v-text="item.shop_name"></text>
                    <image src="../../static/image/arrow-right.png" mode=""></image>
                </view>
            </view>
        </view>
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
                    that.list = that.sortList(res.data.menus)
                }
            })
        },
        methods: {
            // [カスタムエイド 2026-10-20] お店が削除したメニューは一番下に
            sortList(list) {
                list = list || []
                return list.filter((v) => v.deleted != 1).concat(list.filter((v) => v.deleted == 1))
            },
            close() {
                this.shows = false
            },
            change(e) {
                this.sum = e.detail.current + 1
            },
            // [カスタムエイド 2026-10-19] メニューの写真を全画面で表示(拡大・左右スワイプで次の写真)
            openPhotos(urls, idx) {
                if (!urls || !urls.length) return
                uni.previewImage({ current: idx || 0, urls: urls, indicator: 'number', loop: urls.length > 1 })
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
                                that.list = that.sortList(res.data.menus)
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

    // [カスタムエイド 2026-10-19] お気に入りを白い角丸の枠に(メニュー一覧と同じ形)
    .cs-list {
        padding: 24rpx 24rpx 40rpx;
    }

    .box {
        background: #FFFFFF;
        border: 1px solid #E8E8ED;
        border-radius: 24rpx;
        box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.08);
        padding: 28rpx 28rpx 0;
        margin-bottom: 24rpx;

        .title {
            display: flex;
            align-items: flex-start;
            font-size: 32rpx;
            line-height: 1.4;
            color: #1D1D1F;
            font-weight: 700;
            margin-bottom: 8rpx;

            .title-txt {
                flex: 1;
                min-width: 0;
            }

            image {
                flex-shrink: 0;
                margin-left: 16rpx;
                width: 56rpx;
                height: 56rpx;
            }
        }

        .meta {
            font-size: 26rpx;
            line-height: 1.4;
            color: #6E6E73;

            .price {
                font-size: 28rpx;
                font-weight: 600;
                color: #1D1D1F;
            }
        }

        .body {
            display: flex;
            margin-top: 20rpx;

            .left {
                flex-shrink: 0;
                width: 208rpx;
                height: 208rpx;
                position: relative;
                margin-right: 24rpx;

                .f-img {
                    width: 208rpx;
                    height: 208rpx;
                    border-radius: 16rpx;
                    background: #F5F5F7;
                }

                .cs-count {
                    position: absolute;
                    right: 12rpx;
                    bottom: 12rpx;
                    height: 44rpx;
                    padding: 0 14rpx;
                    border-radius: 22rpx;
                    background: rgba(0, 0, 0, 0.6);
                    display: flex;
                    align-items: center;

                    .cs-count-icon {
                        width: 24rpx;
                        height: 24rpx;
                        margin-right: 8rpx;
                    }

                    .cs-count-num {
                        color: #FFFFFF;
                        font-size: 24rpx;
                    }
                }
            }

            .right {
                flex: 1;
                min-width: 0;
                font-size: 26rpx;
                line-height: 1.6;
                color: #3A3A3C;
                white-space: pre-line;
            }
        }

        .yue {
            margin-top: 28rpx;
            height: 80rpx;
            line-height: 80rpx;
            font-size: 30rpx;
            color: #1A73E8;
            border-radius: 20rpx;
            text-align: center;
            background-color: #F4F7FF;
            border: 2rpx solid #1A73E8;

            &:active {
                opacity: .7;
            }
        }

        .shop {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 24rpx;
            padding: 22rpx 0;
            border-top: 1px solid #EFEFF2;

            .tit {
                font-size: 28rpx;
                color: #515154;
            }

            image {
                width: 24rpx;
                height: 38rpx;
            }
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

    /* [カスタムエイド 2026-10-20] お店が削除したメニュー */
    .box.cs-off {
        background: #F2F2F5;
        border-color: #E2E2E7;
        box-shadow: none;
    }
    .box.cs-off .title,
    .box.cs-off .meta,
    .box.cs-off .meta .price,
    .box.cs-off .body .right {
        color: #8E8E93 !important;
    }
    .box.cs-off .f-img {
        filter: grayscale(1);
        opacity: 0.55;
    }
    .box.cs-off .title image {
        opacity: 0.45;
    }
    .cs-offmsg {
        display: inline-block;
        font-size: 24rpx;
        color: #6E6E73;
        background: #E5E5EA;
        border-radius: 10rpx;
        padding: 4rpx 14rpx;
        margin: 8rpx 0 4rpx;
    }
    .box .yue.cs-remove {
        border-color: #C7C7CC !important;
        color: #3A3A3C !important;
        background: #F5F5F7 !important;
    }
</style>
