<template>
    <view>
        <u-navbar :title="shopName":placeholder="true" :safeAreaInsetTop="true" :height="56" :border="true" :autoBack="false">
        </u-navbar>
        <view class="scroll">
            <scroll-view :scroll-x="true">
                <view class="flex">
                    <view class="item-lei" v-for="(item,index) in list" v-text="item.name"></view>
                </view>
            </scroll-view>
        </view>
        
        
        <view v-for="(item,index) in menuList">
            <view class="lei">
                <view class="lei-txt" v-text="item.cate"></view>
            </view>
            <view class="box-item">
                <view class="item" v-for="(ite,idx) in item.menus">
                    <view class="itms">
                        <view :class="!ite.checked ? 'yuan' : 'yuan checked'"  @click="checked(index,idx)">
                            <u-icon name="checkbox-mark" color="#fff" size="14" v-if="ite.checked"></u-icon>
                        </view>
                       <!-- <view class="zhanwei" v-else>
            
                        </view> -->
                        <view class="midd">
                            <view class="tit">
                                {{ite.title}}
                            </view>
                            <view class="pri-box">
                                <view class="pri-left">
                                    ￥{{ite.price}}
                                </view>
                                <view class="pri-right" v-if="ite.is_order == 1">
                                    所要時間：{{ite.order_time}}
                                </view>
                            </view>
                        </view>
                        <view class="right">
                            <image v-if="ite.collect == 0" src="../../static/image/love.png" mode="" @click="toCollect(index,idx,ite.id)"></image>
                            <image v-if="ite.collect == 1" src="../../static/image/collection.png" mode="" @click="toCollect(index,idx,ite.id)"></image>
                        </view>
                    </view>
                    <view class="btn-box">
                        <view class="btn-left" v-if="ite.image.length > 0">
                            <view class="tu-box">
                                <image src="../../static/image/heji.png" mode="" class="tuji" @click="heJiShow(index,idx)">
                                </image>
                            </view>
                            <image :src="ite.image[0]" mode="" class="img"></image>
                        </view>
                        <view class="btn-right" style="white-space: pre-wrap;">
                            {{ite.des}}
                        </view>
                    </view>
                </view>
            </view>
        </view>
        <view class="fix-btn">
            <view class="fix" @click="yuyue()">
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
                            <image :src="item" mode="" class="tu-img"></image>
                        </view>
                    </swiper-item>
                </swiper>
                <view class="shu-sum">
                    {{sum}} / {{imgList.length}}
                </view>
            </view>
        </u-popup>
        <list v-if="caiDanShow" :caiDanShow="caiDanShow" :mid="id" :sid="sid" :tabs="tabs" @seed="seed" @selected="chooseItem"></list>
    </view>
</template>

<script>
    import list from '../../components/list.vue'
    import {
        collectMenu,
        menuList,
        menuCate
    } from "@/api/index.js"
    export default {
        props: ["color", "id", "sid", "bgColor", "shopName","tabs"],
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
                menuList:[],
                page:1
            };
        },
        created() {
            let that = this
            that.getList()
        },
        methods: {
            seed(e) {
                this.caiDanShow = false
            },
            toCollect(index,idx,id){
                console.log(index)
                console.log(idx)
                console.log(id)
                let that = this
                collectMenu(id).then((res) => {
                    console.log(res)
                    if(res.code == 200){
                        uni.showToast({
                            title:res.message
                        })
                        that.menuList[index].menus[idx].collect == 0? that.menuList[index].menus[idx].collect = 1: that.menuList[index].menus[idx].collect = 0
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
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
                    console.log(res)
                    if (res.code == 200) {
                        that.list = res.data.cates
                        menuList(data).then((result) => {
                            let a1 = []
                            res.data.cates.forEach((val,index) => {
                                let obj = {}
                                obj.cate = val.name
                                obj.cid = val.id
                                obj.menus = []
                                result.data.menus.forEach((value,idx) => {
                                    value.checked = false
                                    if(value.cid == val.id){
                                        obj.menus.push(value)
                                    }
                                })
                                a1.push(obj)
                            })
                           that.menuList = a1
                        })
                    }
                })


            },
            rightClick() {
                this.caiDanShow = true
            },
            heJiShow(index,idx) {
                console.log(this.menuList[index].menus[idx].image)
                this.imgList = this.menuList[index].menus[idx].image
                this.imgListShow = true
            },
            change(e) {
                this.sum = e.detail.current + 1
            },
            checked(index,idx) {
                console.log(this.menuList[index].menus)
                console.log(idx)
                
                 this.menuList.forEach((val,index) => {
                     val.menus.forEach((value,idx) => {
                         value.checked = false
                     })
                 })
                 this.menuList[index].menus[idx].checked = true
            },
            yuyue() {
                let that = this
                let us = {}
                this.menuList.forEach((val,index) => {
                    val.menus.forEach((value,idx) => {
                        if(value.checked == true){
                            us = value
                        }
                    })
                })
                console.log("us",us)
                if(us.title){
                    uni.setStorageSync("orderItem",us)
                }else{
                  uni.showToast({
                      title: "メニューを選択してください",
                      icon: "none"
                  })
                   return false
                }
                uni.navigateTo({
                    url: '../../pagesA/yuYue/yuYue?id='+that.sid
                })
            },
            imgClose() {
                this.imgListShow = false
            },
            chooseItem(e){
              this.caiDanShow = false
              this.$emit("changes",e)
            },
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

    .scroll {
        width: 90%;
        margin-left: 5%;
        height: 142rpx;
        background: #FFFFFF;

        .flex {
            width: 100%;
            height: 100%;
            margin-top: 40rpx;
            display: flex;
        }

        .item-lei {
            flex-shrink: 0;
            margin-right: 40rpx;
            width: 152rpx;
            height: 64rpx;
            text-align: center;
            line-height: 64rpx;
            color: #86868B;
            border-radius: 10rpx;
            border: 2rpx solid #86868B;
        }
    }

    .lei {
        height: 100rpx;
        background: #F5F5F7;
        color: #707070;
        font-size: 32rpx;
        border-top: 1px solid #D2D2D7;
        border-bottom: 1px solid #D2D2D7;

        .lei-txt {
            width: 90%;
            margin: 0 auto;
            line-height: 100rpx;
        }
    }

    .box-item {
        .item {
            margin-top: 40rpx;
            padding-bottom: 34rpx;
            border-bottom: 1px solid #D2D2D7;

            .itms {
                display: flex;
                justify-content: space-between;
                width: 90%;
                margin: 0 auto;
                // align-items: center;
            }

            .yuan {
                width: 40rpx;
                height: 40rpx;
                text-align: center;
                border-radius: 50%;
                box-sizing: border-box;
                border: 2rpx solid #707070;
                // margin-right: 24rpx;
            }

            ::v-deep .u-icon__icon {
                padding-left: 7rpx;
                padding-top: 7rpx;
            }

            .zhanwei {
                width: 40rpx;
                height: 40rpx;
            }

            .checked {

                background-color: #2979ff;
                border: 0;
            }

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
                    justify-content: space-between;
                    align-items: center;
                    font-size: 32rpx;

                    .pri-left {
                        color: #E67770;
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
                width: 80%;
                margin: 28rpx 0;
                margin-left: 13%;
                margin-right: 5%;
                display: flex;
                justify-content: space-between;

                .btn-left {
                    position: relative;

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
                    margin-left: 20rpx;
                    color: #707070;
                    font-size: 24rpx;
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
        bottom: 105upx;
        left: 0;
        z-index: 2;
        width: 100%;
        height: 168rpx;
        box-shadow: 0px 0px 12rpx 2rpx rgba(0, 0, 0, 0.16);
        background-color: #fff;

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
    }
</style>
