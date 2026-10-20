<template>
    <view>
        <!-- [カスタムエイド 2026-10-20] 店名が長いときに右のアイコンにくっつかないよう、店名の幅を 340rpx に(入りきらない分は「…」) -->
        <u-navbar titleWidth="340rpx" :leftIconColor="color" :title="shopName" :titleStyle="{'color':color}" :bgColor="bgColor" :placeholder="true"
            :safeAreaInsetTop="true" :border="true" @rightClick="rightClick" @leftClick="leftClick">
            <view class="u-nav-right" style="display: flex;align-items: center;" slot="right">
               <!-- [カスタムエイド 2026-10-20] 予約履歴のアイコン(会員証のタブと同じ) -->
               <rh-icon :sid="sid" :color="color" :bgColor="bgColor"></rh-icon>
               <view :style="`backgroundImage: url(${svgData30})`" style="width: 50upx;height: 50upx;" class="iconright" @click=""></view>
            </view>
        </u-navbar>
        <u-sticky customNavHeight="60">
        	<view class="scroll">
        	    <view style="overflow-x: scroll;">
        	        <view class="flex" style="margin: 0;">
        	            <view class="item-lei" :class="current1 == item.id?'tab_item_active':'' "
        	                v-for="(item,index) in list" @click.stop="selectTab(item)" v-text="item.name"></view>
        	        </view>
        	    </view>
        	</view>
        </u-sticky>
        <view v-for="(item,index) in menuList" v-if="item.cid == current1 || current1 == -1">
            <view class="lei">
                <view class="lei-txt" v-text="item.cate"></view>
            </view>
            <view class="box-item">
                <view class="item" v-for="(ite,idx) in item.menus"
                    :style="ite.checked?'background: rgba(143,168,248,0.1) !important;' : ''">
                    <view class="itms">
                        <view :style="ite.is_order == 1? '' : 'visibility:hidden'"
                            :class="!ite.checked ? 'yuan' : 'yuan checked'"
                            style="box-sizing: border-box;padding: 10upx;" @click="checked(index,idx)" v-if="ite.is_order == 1">
                            <!-- <u-icon name="checkbox-mark" color="#fff" size="14"></u-icon> -->
                            <view class="inner" v-if="ite.checked"></view>
                        </view>
                        <!-- <view class="zhanwei" v-else>
            
                        </view> -->
                        <view class="midd">
                            <view class="tit">
                                {{ite.title}}
                            </view>
                            <!-- [カスタムエイド 2026-10-19] 価格と所要時間を1行に(価格は濃い文字の太字) -->
                            <view class="pri-box">
                                <text class="pri-left">{{ite.price}}</text>
                                <text class="pri-right" v-if="ite.is_order == 1">　・　所要時間 {{ite.order_time}}分</text>
                            </view>
                        </view>
                        <view class="right">
                            <image v-if="ite.collect == 0" src="../../static/image/love.png" mode=""
                                @click="toCollect(index,idx,ite.id)"></image>
                            <image v-if="ite.collect == 1" src="../../static/image/collection.png" mode=""
                                @click="toCollect(index,idx,ite.id)"></image>
                        </view>
                    </view>
                    <view class="btn-box" v-if="ite.image.length > 0 || ite.des">
                        <!-- [カスタムエイド 2026-10-19] 写真を押したらすぐ全画面で表示(スマホ標準の画面)。
                             2枚以上のときだけ、右下に枚数のラベルを出す(今までは1枚でも左上にマークが出ていた) -->
                        <view class="btn-left" v-if="ite.image.length > 0" @click="openPhotos(ite.image, 0)">
                            <image :src="ite.image[0]" mode="aspectFill" class="img"></image>
                            <view class="cs-count" v-if="ite.image.length > 1">
                                <image src="../../static/svg/multiPhoto.svg" class="cs-count-icon"></image>
                                <text class="cs-count-num">{{ite.image.length}}</text>
                            </view>
                        </view>
                        <view class="btn-right" >
                            {{ite.des}}
                        </view>
                    </view>
                </view>
            </view>
        </view>
        <view class="fix-btn">
            <view class="fix" :class="avi?'' : 'fixed'" @click="yuyue()">
                選択したメニューで予約
            </view>
        </view>
        <view style="height: 300upx;"></view>
        <list :caiDanShow="caiDanShow" :mid="id" :sid="sid" :tabs="tabs" @seed="seed" @selected="chooseItem"></list>
    </view>
</template>

<script>
    import list from '../../components/list.vue'
    import svg30 from '../../static/svgjs/sv_30.js'
    import rhIcon from '../../components/rh-icon.vue' // [カスタムエイド 2026-10-20]
    import {
        collectMenu,
        menuList,
        menuCate,
        getCardInfo
    } from "@/api/index.js"
    export default {
        props: ["color", "id", "sid", "bgColor", "shopName", "tabs"],
        components: {
            rhIcon,
            list,
        },
        data() {
            return {
                value: 4,
                caiDanShow: false,
                imgListShow: false,
                sum: 1,
                cid: 0,
                imgList: [],
                list: [],
                menuList: [],
                page: 1,
                current1: -1,
                mid: 0,
                avi: false,
                svgData30:'"' + svg30 + '"',
            };
        },
        created() {
            let that = this
            that.getList()
            that.getCardinf()
            that.svgData30 = that.changeColor(that.svgData30,that.color);  
        },
        methods: {
            changeColor(url,color){
             	let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#", "%23"));//转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
             	return res;
             },
            scrollR() {

                this.caiDanShow = false
            },
            scrollL() {

            },
            getCardinf() {
                getCardInfo(this.id).then((res) => {
                    if (res.code == 200) {
                        this.mid = res.data.member.id
                    }
                })
            },
            leftClick() {
                uni.navigateBack()
            },
            seed(e) {
                this.caiDanShow = false
            },
            selectTab(e) {

                let that = this
                if (that.current1 == e.id) {
                    that.current1 = -1
                } else {
                    that.current1 = e.id
                }
            },
            toCollect(index, idx, id) {
                let that = this
                collectMenu(id).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                        that.menuList[index].menus[idx].collect == 0 ? that.menuList[index].menus[idx].collect =
                            1 : that.menuList[index].menus[idx].collect = 0
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },
            getList() {
                let that = this
                let data = {}
                data.page = that.page
                data.collect = ""
                data.sid = that.sid
                data.cid = that.cid

                menuCate(that.sid).then((res) => {
                    if (res.code == 200) {
                        that.list = res.data.cates
                        if(res.data.cates.length == 0){
                            uni.showToast({
                                title:"予約可能なメニューがありません",
                                icon:'none',
                                duration:3000
                            })
                            return
                        }
                        menuList(data).then((result) => {
                            let a1 = []
                            res.data.cates.forEach((val, index) => {
                                let obj = {}
                                obj.cate = val.name
                                obj.cid = val.id
                                obj.menus = []
                                result.data.menus.forEach((value, idx) => {
                                    value.checked = false
                                    if (value.cid == val.id) {
                                        obj.menus.push(value)
                                    }
                                })
                                a1.push(obj)
                            })
                            that.menuList = a1
                            if(that.menuList.length == 0){
                                uni.showToast({
                                    title:"予約可能なメニューがありません",
                                    icon:'none',
                                    duration:3000
                                })
                            }
                        })
                    }
                })


            },
            rightClick() {
                this.caiDanShow = true
            },
            heJiShow(index, idx) {
                this.imgList = this.menuList[index].menus[idx].image
                this.imgListShow = true
                this.sum = 1
            },
            // [カスタムエイド 2026-10-19] メニューの写真を全画面で表示(拡大・左右スワイプで次の写真)
            openPhotos(urls, idx) {
                if (!urls || !urls.length) return
                uni.previewImage({ current: idx || 0, urls: urls, indicator: 'number', loop: urls.length > 1 })
            },
            openPreview(idx) {
                uni.previewImage({
                    current: idx,
                    urls: this.imgList
                })
            },
            change(e) {
                this.sum = e.detail.current + 1
            },
            checked(index, idx) {
                if (this.menuList[index].menus[idx].checked == true) {
                    this.menuList[index].menus[idx].checked = false
                    this.avi = false
                } else {
                    this.menuList.forEach((val, index) => {
                        val.menus.forEach((value, idx) => {
                            value.checked = false
                        })
                    })
                    this.menuList[index].menus[idx].checked = true
                    this.avi = true
                }


            },
            yuyue() {
                let that = this
                if (that.avi == true) {
                    let us = {}
                    this.menuList.forEach((val, index) => {
                        val.menus.forEach((value, idx) => {
                            if (value.checked == true) {
                                us = value
                            }
                        })
                    })
                    if (us.title) {
                        uni.setStorageSync("orderItem", us)
                    } else {
                        uni.showToast({
                            title: "メニューを選択してください",
                            icon: "none"
                        })
                        return false
                    }
                    uni.navigateTo({
                        url: '../../pagesA/yuYue/yuYue?id=' + that.sid + "&mid=" + that.mid
                    })
                }

            },
            imgClose() {
                this.imgListShow = false
            },
            chooseItem(e) {
                this.caiDanShow = false
                this.$emit("changes", e)
            },
        }
    }
</script>

<style lang="scss" scoped>
    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    .yuan {
        width: 32rpx;
        height: 32rpx;
        text-align: center;
        border-radius: 50%;
        box-sizing: border-box;
        border: 2rpx solid #707070;
        padding: 5upx !important;
        position: relative;
        top: 10rpx;
        // margin-right: 24rpx;
    }


    .tab_item_active {
        background: rgba(29, 29, 31, 0.15) !important;
        color: rgba(29, 29, 31, 1) !important;
        border: 2upx solid rgba(29, 29, 31, 1) !important;
    }

    .inner {
        width: 100%;
        height: 100%;
        background: #2979ff;
        border-radius: 50%;
    }

    .u-nav-right {
        image {
            width: 48rpx;
            height: 48rpx;
        }
    }

    .scroll {
        width: 100%;
        box-sizing: border-box;
        padding: 20upx;
        background: #FFFFFF;
        // position: fixed;
        // left: 0;
        // top: 140upx;
        // z-index: 9;
        border: 2upx solid rgba(29, 29, 31, 0.15);

        .flex {
            width: 100%;
            height: 100%;
            margin: 0 auto;
            display: flex;
        }

        .item-lei {
            flex-shrink: 0;
            margin-right: 20rpx;
            padding: 0 20upx;
            height: 64rpx;
            text-align: center;
            line-height: 64rpx;
            color: #86868B;
            border-radius: 10rpx;
            border: 2rpx solid #86868B;
            font-size: 24upx;
        }
    }

    // [カスタムエイド 2026-10-19] カテゴリの帯を細く(52px→36px)。メニューは1つずつ白い角丸の枠に入れる
    .lei {
        height: 72rpx;
        background: #F5F5F7;
        color: #515154;
        font-size: 28rpx;
        font-weight: 600;
        border-top: 1px solid #E3E3E8;
        border-bottom: 1px solid #E3E3E8;
        margin-bottom: 24rpx;

        .lei-txt {
            padding-left: 32rpx;
            line-height: 72rpx;
        }
    }


    .box-item {
        padding: 0 24rpx;

        .item {
            background: #FFFFFF;
            border: 1px solid #E8E8ED;
            border-radius: 24rpx;
            box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.08);
            padding: 28rpx;
            margin-bottom: 24rpx;

            .itms {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                width: 100%;
                box-sizing: border-box;
            }

            ::v-deep .u-icon__icon {
                padding-left: 7rpx;
                padding-top: 7rpx;
            }

            .zhanwei {
                width: 40rpx;
                height: 40rpx;
            }

            .checked {}

            .yuan {
                flex-shrink: 0;
                margin-right: 24rpx;
                top: 6rpx;
            }

            .midd {
                flex: 1;
                min-width: 0;

                .tit {
                    font-size: 32rpx;
                    font-weight: 700;
                    line-height: 1.4;
                    color: #1D1D1F;
                    margin-bottom: 8rpx;
                }

                .pri-box {
                    font-size: 26rpx;
                    line-height: 1.4;
                    color: #6E6E73;

                    .pri-left {
                        font-size: 28rpx;
                        font-weight: 600;
                        color: #1D1D1F;
                    }

                    .pri-right {
                        color: #6E6E73;
                    }
                }
            }

            .right {
                flex-shrink: 0;
                margin-left: 16rpx;

                image {
                    width: 56rpx;
                    height: 56rpx;
                }
            }

            .btn-box {
                width: 100%;
                margin-top: 20rpx;
                display: flex;
                box-sizing: border-box;

                .btn-left {
                    position: relative;
                    flex-shrink: 0;
                    margin-right: 24rpx;
                    width: 208rpx;
                    height: 208rpx;

                    .img {
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

                .btn-right {
                    color: #3A3A3C !important;
                    font-size: 26rpx !important;
                    line-height: 1.6;
                    flex: 1;
                    min-width: 0;
                    white-space: pre-line;
                }
            }
        }
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

    .fix-btn {
        position: fixed;
        bottom: 140upx;
        left: 0;
        z-index: 2;
        width: 100%;
        height: 168rpx;
        box-shadow: 0px 0px 12rpx 2rpx rgba(0, 0, 0, 0.16);
        background-color: rgba(255, 255, 255, 0.8);

        .fix {
            width: 638rpx;
            height: 88rpx;
            line-height: 88rpx;
            margin: 0 56rpx;
            margin-top: 44rpx;
            text-align: center;
            background: rgb(26, 115, 232);
            border-radius: 20rpx;
            font-size: 32rpx;
            color: #FBFBFD;
        }

        .fixed {
            background: rgba(26, 115, 232, .6) !important;
        }
    }
</style>