<template>
    <view :lrDistance="5" leftMed="scrollL" rightMed="scrollR" @scrollL="scrollL" @scrollR="scrollR">
        <u-navbar :leftIconColor="color" :title="shopName" :titleStyle="{'color':color}" :bgColor="bgColor"
            :placeholder="true" :safeAreaInsetTop="true" :border="true" @rightClick="rightClick" @leftClick="leftClick">
            <view class="u-nav-right" slot="right">
                <view :style="`backgroundImage: url(${svgData30})`" style="width: 50upx;height: 50upx;"
                    class="iconright" @click=""></view>
            </view>
        </u-navbar>
        <view class="news-box" v-if="type == 1">
            <view class="news-items" v-for="(item,index) in list" :key="index">
                <u-swiper :list="item.images" height="375" :autoplay="false" :indicator="true" indicatorMode="dot"
                    :radius="0" indicatorActiveColor="#000000" indicatorInactiveColor="rgba(0,0,0,.3)"
                    v-if="item.images.length > 0">
                </u-swiper>
                <view class="user-box">
                    <view class="user">
                        <view class="right">
                            <view class="store-name">
                                {{item.shop_name}}
                            </view>
                            <view class="time">
                                {{item.date}}
                                <text v-text="item.is_top == 1? '固定されたメッセージ' : '' "></text>
                                <!-- <text>{{item.zhi}}</text> -->
                            </view>
                        </view>
                    </view>
                    <view class="title">
                        {{item.title}}
                    </view>
                    <view class="des">
                        {{item.content}}
                    </view>
                </view>
            </view>
        </view>
        <view style="height: 90vh;" @scrolltolower="getMore" class="news-box" scroll-y v-if="type == 2">
            <view class="news-items1" v-for="(item,index) in insList" :key="index" @click="toInsDetai(item.permalink)">
                <view class="insImage" v-if="item.media_type == 'IMAGE'">
                    <image :src="item.media_url" mode="aspectFill"></image>
                </view>
                <view class="insImage" v-if="item.media_type == 'VIDEO'">
                    <video :src="item.media_url" mode="aspectFit"></video>
                </view>
                <view class="insImage" v-if="item.media_type == 'CAROUSEL_ALBUM'">
                    <u-swiper interval="6000" :indicator="true" indicatorActiveColor="#1A73E8" height="400"
                        :list="item.carousel"></u-swiper>
                </view>
                <view class="user-box1">
                    <view class="insinfo">
                        <view class="insavatar">
                            <image :src="item.avatar" mode="aspectFill"></image>
                        </view>
                        <view class="insname" v-text="item.username"></view>
                    </view>
                    <view class="timestamps" v-text="item.datetime"></view>
                    <view class="des">
                        {{item.caption}}
                    </view>
                    <view class="tps">
                        Synced from Instagram
                    </view>
                </view>
            </view>
        </view>
        <!-- <web-view v-if="links != ''" :src="links"></web-view> -->
        <list :caiDanShow="caiDanShow" :mid="id" :sid="sid" :tabs="tabs" @seed="seed" @selected="chooseItem"></list>
    </view>
</template>

<script>
    import list from '../../components/list.vue'
    import sp from "@/components/swiper-direct-com.vue"
    import svg30 from '../../static/svgjs/sv_30.js'
    import {
        getShopNotice,
        getShopInfo,
        getInsList
    } from "@/api/index.js"
    export default {
        components: {
            list,
            sp
        },
        props: ["color", "id", "sid", "bgColor", "shopName", "tabs"],
        data() {
            return {
                value: 2,
                caiDanShow: false,
                type: "",
                list: [],
                insList: [],
                svgData30: '"' + svg30 + '"',
                afrt: "",
                insname: "ins昵称ins昵称ins昵称ins昵称ins昵称",
                insAvatar: "../../static/image/youtube.png"
            };
        },
        mounted() {
            //监听下拉加载更多
            uni.$on('onReachBottom', () => {
                this.getMore()
            })
        },
        beforeDestroy() {
            uni.$off('onReachBottom');
        },
        created() {
            let that = this
            that.svgData30 = that.changeColor(that.svgData30, that.color);
            that.getInfo()
            console.log("noshow")
        },
        methods: {
            getMore() {
                uni.showLoading({
                    title: "Loading...",
                    mask: true
                })
                let that = this
                that.getInsLists()
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
            seed(e) {
                this.caiDanShow = false
            },
            getInsLists() {
                let that = this
                if (that.type == 2) {
                    let data = {}
                    data.sid = that.sid
                    data.after = that.afrt
                    getInsList(data).then((result) => {
                        uni.hideLoading()
                        if (result.code == 200) {
                            that.afrt = result.data.after
                            that.insList = [...that.insList, ...result.data.medias]
                        }
                    })
                }

            },
            getInfo() {
                let that = this
                getShopInfo(that.sid).then((res) => {
                    if (res.code == 200) {
                        // notice_type が未設定(null/0)のショップはデフォルトでお知らせ表示
                        const noticeType = res.data.shop.notice_type || 1
                        that.type = noticeType
                        if (noticeType == 1) {
                            that.getList()
                        }
                        if (noticeType == 2) {
                            let data = {}
                            data.sid = that.sid
                            data.after = that.afrt
                            getInsList(data).then((result) => {
                                if (result.code == 200) {
                                    that.afrt = result.data.after
                                    that.insList = [...that.insList, ...result.data.medias]
                                }
                            })
                        }
                    }
                }).catch((err) => {
                    console.log("getShopInfo error", err)
                    // API失敗時はデフォルトでお知らせ表示を試みる
                    that.type = 1
                    that.getList()
                })
            },
            toInsDetai(e) {
                uni.navigateTo({
                    url: "../webins/webins?link=" + e
                })
            },
            leftClick() {
                let that = this
                uni.navigateBack()
            },
            rightClick() {
                this.caiDanShow = true
            },
            getList() {
                let that = this
                getShopNotice(that.sid).then((res) => {
                    if (res.code == 200) {
                        that.list = res.data.notices
                    }
                }).catch((err) => {
                    console.log("getShopNotice error", err)
                })
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

    .tps {
        text-align: right;
        width: 100%;
        font-size: 20rpx;
        color: #707070;
        margin-top: 20rpx;
    }

    .insinfo {
        display: flex;
        align-items: center;
        margin-bottom: 10rpx;

        .insavatar {
            width: 80rpx;
            height: 80rpx;
            border-radius: 50%;
            border: 2rpx solid #707070;
            overflow: hidden;
            margin-right: 20rpx;

            image {
                width: 100%;
                height: 100%;
            }
        }

        .insname {
            font-weight: bold;
            font-size: 28rpx;
            color: #1D1D1F;
        }
    }

    .timestamps {
        color: #707070;
        font-size: 24upx;
        margin-bottom: 10upx;
    }

    page {
        background: #fff !important;
    }

    .topblank {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 50upx;
        z-index: 150 !important;
        background: #fff;
    }

    .insImage {
        width: 100%;
        height: 750upx;

        image {
            width: 100%;
            height: 100%;
        }

        video {
            width: 100%;
            height: 100%;
        }
    }

    .u-nav-right {
        image {
            width: 48rpx;
            height: 48rpx;
        }
    }

    .user-box1 {
        width: 90%;
        margin: 0 auto;
        margin-top: 20rpx;
    }

    .news-items1 {
        padding-bottom: 60rpx;
        border-bottom: 1px solid #D2D2D7;
    }

    .news-box {
        margin-top: 12rpx;
        padding-bottom: 172rpx;

        .news-items {
            margin-top: 12rpx;
            padding-bottom: 60rpx;
            border-bottom: 1px solid #D2D2D7;

            .user-box:first-child {
                margin-top: 20upx;
            }

            .user-box {
                width: 100%;
                margin: 0 auto;
                margin-top: 40rpx;
                box-sizing: border-box;
                padding: 0 32upx;

                .user {
                    display: flex;
                    margin-bottom: 10rpx;

                    .left {
                        margin-right: 32rpx;

                        image {
                            width: 62rpx;
                            height: 62rpx;
                            border-radius: 50%;
                        }
                    }

                    .right {
                        .store-name {
                            color: #1D1D1F;
                            font-size: 28rpx;
                            font-weight: 700;
                        }

                        .time {
                            margin-top: 10rpx;
                            font-size: 24rpx;
                            color: #707070;

                            text {
                                margin-left: 40rpx;
                                color: #1A73E8;
                            }
                        }
                    }
                }

                .title {
                    font-size: 32rpx;
                    font-weight: 700;
                    color: #1D1D1F;
                    margin-bottom: 10rpx;
                }

                .des {
                    font-size: 28rpx;
                    color: #707070;
                    white-space: pre-wrap;
                }
            }
        }
    }
</style>