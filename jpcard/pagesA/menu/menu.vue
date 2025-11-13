<template>
    <view>
        <u-navbar :leftIconColor="color" :title="shopName" :titleStyle="{'color':color}" :bgColor="bgColor" :placeholder="true"
            :safeAreaInsetTop="true" :border="true" @rightClick="rightClick" @leftClick="leftClick">
            <view class="u-nav-right" slot="right">
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
                            <view class="pri-box">
                                <view class="pri-left">
                                    {{ite.price}}
                                </view>
                                <view class="pri-right" v-if="ite.is_order == 1">
                                    所要時間：{{ite.order_time}}分
                                </view>
                            </view>
                        </view>
                        <view class="right">
                            <image v-if="ite.collect == 0" src="../../static/image/love.png" mode=""
                                @click="toCollect(index,idx,ite.id)"></image>
                            <image v-if="ite.collect == 1" src="../../static/image/collection.png" mode=""
                                @click="toCollect(index,idx,ite.id)"></image>
                        </view>
                    </view>
                    <view class="btn-box">
                        <view class="btn-left" v-if="ite.image.length > 0">
                            <view class="tu-box">
                                <image src="../../static/image/heji.png" mode="" class="tuji"
                                    @click="heJiShow(index,idx)">
                                </image>
                            </view>
                            <image :src="ite.image[0]" mode="aspectFill" class="img"></image>
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
        <u-popup :show="imgListShow" :round="10" mode="center" overlayOpacity="1">
            <view class="tu-list">
                <view class="icon" @click="imgClose()">
                    <u-icon name="close" color="#FFFFFF" size="16"></u-icon>
                </view>
                <swiper :indicator-dots="false" :autoplay="false" :duration="1000" :circular="true" @change="change">
                    <swiper-item v-for="item in imgList" :key="item">
                        <view class="swiper-item">
                            <image :src="item" mode="aspectFit" class="tu-img"></image>
                        </view>
                    </swiper-item>
                </swiper>
                <view class="shu-sum">
                    {{sum}} / {{imgList.length}}
                </view>
            </view>
        </u-popup>
        <list :caiDanShow="caiDanShow" :mid="id" :sid="sid" :tabs="tabs" @seed="seed" @selected="chooseItem"></list>
    </view>
</template>

<script>
    import list from '../../components/list.vue'
    import svg30 from '../../static/svgjs/sv_30.js'
    import {
        collectMenu,
        menuList,
        menuCate,
        getCardInfo
    } from "@/api/index.js"
    export default {
        props: ["color", "id", "sid", "bgColor", "shopName", "tabs"],
        components: {
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
                            console.log("result",result)
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

    .lei {
        height: 100rpx;
        background: #F5F5F7;
        color: #707070;
        font-size: 32rpx;
        border-bottom: 1px solid #D2D2D7;

        .lei-txt {
            width: 90%;
            margin: 0 auto;
            line-height: 100rpx;
        }
    }


    .box-item {
        .item {
            padding-top: 40rpx;
            padding-bottom: 34rpx;
            border-bottom: 1px solid #D2D2D7;

            .itms {
                display: flex;
                justify-content: space-between;
                width: 100%;
                box-sizing: border-box;
                padding: 0 32upx;
                margin: 0 auto;
                // align-items: center;
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

            .midd {
                width: 530rpx;

                .tit {
                    font-size: 32rpx;
                    font-weight: 700;
                    color: #1D1D1F;
                    margin-bottom: 28rpx;
                }

                .pri-box {
                    display: flex;
                    // justify-content: space-between;
                    align-items: center;
                    font-size: 32rpx;

                    .pri-left {
                        color: #E67770;
                        // margin-right: 90upx;
                        width: 38%;
                    }

                    .pri-right {
                        color: #1D1D1F;
                    }
                }
            }

            .right {
                image {
                    width: 60rpx;
                    height: 60rpx;
                }
            }

            .btn-box {
                width: 100%;
                margin: 28rpx 0;
                // margin-left: 13%;
                // margin-right: 5%;
                display: flex;
                justify-content: space-between;
                box-sizing: border-box;
                padding: 0 32upx;

                .btn-left {
                    position: relative;
                     margin-right: 20rpx;
                    .tu-box {
                        position: absolute;
                        top: 0;
                        left: 0;
                        z-index: 1;
                        width: 64rpx;
                        height: 64rpx;

                        text-align: center;
                        background: rgba(29, 29, 31, 0.4);
                        border-radius: 12rpx;

                        .tuji {
                            margin-top: 6rpx;
                            width: 45rpx;
                            height: 51rpx;
                        }
                    }

                    .img {
                        width: 246rpx;
                        height: 246rpx;
                        border-radius: 12rpx;
                        border: 2rpx solid #EAEAEA;
                    }
                }

                .btn-right {
                   
                    color: #1d1d1f !important;
                    font-size: 24rpx !important;
                    flex: 1;
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