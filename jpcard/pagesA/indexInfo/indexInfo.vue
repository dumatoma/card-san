<template>
    <view>
        <view class="titleTop">
            <view v-text="name"></view>
            <image src="../../static/svg/button.svg" alt="" style="width: 48upx;height: 48upx;position: absolute;left: 32upx;top: 32upx;" @click="closeThis">
            <image/>
        </view>
        <view style="height: 112upx;"></view>
        <view class="swiper">
            <u-swiper :list="shopInfo.carousel" height="250" :indicator="true" indicatorMode="dot" :radius="0"
                indicatorActiveColor="#000000" :autoplay="false" indicatorInactiveColor="rgba(0,0,0,.3)"></u-swiper>
        </view>
        <view class="items-boxs">
            <view class="jj" v-if="shopInfo.des" v-text="shopInfo.des">
            </view>
            <view class="items">
                <view class="items-fle">
                    <view class="left">
                        TEL　
                    </view>
                    <view class="midd" v-text="shopInfo.phone"></view>
                    <view class="right phone-btn" @click.stop="call" hover-class="click-hover">
                        電話する
                    </view>
                </view>
            </view>
            <view class="items">
                <view class="items-fle">
                    <view class="left">
                        営業時間
                    </view>
                    <view class="midd">
                        <view v-html="shopInfo.business_time"></view>
                    </view>
                </view>
            </view>
            <view class="items">
                <view class="items-fle">
                    <view class="left">
                        定休日
                    </view>
                    <view class="midd">
                        <view class="" v-html="shopInfo.rest_time"></view>
                    </view>
                </view>
            </view>
            <view class="items">
                <view class="items-fle" style="height: auto !important;padding: 40upx 0;">
                    <view class="left">
                        本日
                    </view>
                    <view class="midd">
                        <view class="">
                            {{today.month}}月{{today.date}}日({{today.week}})
                        </view>
                        <view :style="`color:${shopInfo.business_color}`">{{shopInfo.business_name}}</view>
                        <!-- <view>{{bsTime[0].start}}</view> -->
                        <view class="" v-if="bsTime.length > 0 && shopInfo.business_name != '休業日'">
                            <view v-for="(item,index) in bsTime">
                                {{item.start}}~{{item.end}}
                            </view>
                        </view>
                    </view>
                    <view class="right ri-img" @click="toca"
                        style="border: 2upx solid #707070;display: flex;align-items: center;justify-content: center;"
                        hover-class="hover1">
                        <image src="../../static/image/rili.png" mode="" style="vertical-align: middle;"></image>
                        <text>カレンダー</text>
                    </view>
                </view>
            </view>
        </view>
        <!-- <view class="maps">
            <image class="map" src="../../static/image/map.png" mode="aspectFill"></image>
           
        </view> -->
        <view class="zhu-item" style="border-top: none !important;">
            <view class="zhu-box">
                <view class="zhu-left">
                    住所　　
                </view>
                <view class="zhu-right">
                    <view class="">
                        〒{{shopInfo.zip_code}}
                    </view>
                    <view class="">
                        {{shopInfo.address1}}
                    </view>
                    <view class="" v-if="shopInfo.address2">
                        {{shopInfo.address2}}
                    </view>
                </view>
            </view>
            <view style="height: 78upx;"></view>
            <view class="map-btn" @click="toMap" hover-class="hover1">
                地図アプリで見る
            </view>
        </view>
        <view class="zhu-item">
            <view class="zhu-box">
                <view class="zhu-left">
                    アクセス　　
                </view>
                <view class="zhu-right">
                    <view class="" v-text="shopInfo.traffic_explain"></view>
                </view>
            </view>
        </view>
        <view class="zhu-item">
            <view class="zhu-box">
                <view class="zhu-left">
                    HP
                </view>
                <view class="zhu-right">
                    <view class="lian" @click="lian(shopInfo.web_url)" v-text="shopInfo.web_url"></view>
                </view>
            </view>
            <view style="display: flex;justify-content: flex-end;width: 90%;margin: 0 auto 40upx;">
                <view class="cop" hover-class="hover1" @click="copy1(shopInfo.web_url)">
                    コピー
                </view>
            </view>
        </view>
        <view class="zhu-item">
            <view class="zhu-box">
                <view class="zhu-left">
                    利用可能決済
                </view>
                <view class="zhu-right">
                    <view class="" v-text="shopInfo.pay_type"></view>
                </view>
            </view>
        </view>
        <view class="zhu-item zhu-item-border">
            <view class="zhu-box">
                <view class="zhu-left">
                    備考
                </view>
                <view class="zhu-right" v-text="shopInfo.remark">
                </view>
            </view>
        </view>
        <view class="staff">
            STAFF
        </view>
        <view class="store-popel-box" v-for="(item,index) in staff" :key="index" v-if="shopInfo.staff_des == 1">
            <view class="store-box">
                <view class="store-left" v-if="staff.length > 0">
                    <image :src="item.image[0]" mode="aspectFill" class="store-img"></image>
                    <image src="../../static/image/heji.png" mode="" class="store-heji" @click="heJiShow(index)">
                    </image>
                </view>
                <view class="store-right">
                    <view class="store-name">
                        {{item.name}}
                    </view>
                    <view class="store-identity">
                        {{item.post}}
                    </view>
                    <view class="store-des">
                        {{item.self_introduction}}
                    </view>
                </view>
            </view>
        </view>
        <view class="shar" v-if="shopInfo.share_line == 1">
            <view class="shar-tit">
                友達にアプリを紹介
            </view>
            <view class="shar-btn" @click="copy">
                <image src="../../static/image/shaere.png" mode=""></image>
                <text>アプリダウンロードリンクをコピー</text>
            </view>
        </view>
        <view class="shar-line"> </view>
        <view class="sns" v-if="shopInfo.sns_url == 1">
            <view class="sns-tit">
                SNS
            </view>
            <view class="img-sns">
                <image src="../../static/image/glyph.png" mode="" v-if="shopInfo.sns_ins == 1"
                    @click="shareClick(shopInfo.sns_ins_url)"></image>
                <image src="../../static/image/FaceBook.png" v-if="shopInfo.sns_facebook == 1"
                    @click="shareClick(shopInfo.sns_facebook_url)" mode=""></image>
                <image src="../../static/image/youtube.png" v-if="shopInfo.sns_youtobe == 1"
                    @click="shareClick(shopInfo.sns_youtobe_url)" mode=""></image>
                <image src="../../static/image/Twitter.png" v-if="shopInfo.sns_twitter == 1"
                    @click="shareClick(shopInfo.sns_twitter_url)" mode=""></image>
            </view>
        </view>
        <u-popup :show="imgListShow" :round="10" mode="center" overlayOpacity="1">
            <view class="tu-list">
                <view class="icon" @click="imgClose()">
                    <u-icon name="close" color="#FFFFFF" size="16"></u-icon>
                </view>
                <swiper :indicator-dots="false" :autoplay="false" :duration="1000" :circular="true" @change="change">
                    <swiper-item v-for="item in imgList" :key="item">
                        <view class="swiper-item">
                            <image :src="item" mode="aspectFill" class="tu-img"></image>
                        </view>
                    </swiper-item>
                </swiper>
                <view class="shu-sum">
                    {{sum}} / {{imgList.length}}
                </view>
            </view>
        </u-popup>
        <!-- <u-popup :show="show" height="90vh">
            <view style="height: 90vh;width: 100vw;">
                <clandar @close="closeCal" :sid="sid"></clandar>
            </view>
        </u-popup> -->
        <!-- <clandar :caiDanShow="clandarShows" @clandarClose="clandarClose"></clandar> -->
        <u-overlay :show="phoneShow">
            <view class="fix">
                <view class="fix-cen" @click="call()">
                    <image src="../../static/image/phoe.png" mode=""></image> <text>発信</text><text class="phone"
                        v-text="shopInfo.phone"></text>
                </view>
                <view class="fix-cen" @click="phoneShow = false">
                    <text class="xiao">キャンセル</text>
                </view>
            </view>
        </u-overlay>
        <view class="mask" v-if="showA == true">
            <view class="maskcontent">
                <image src="../../static/svg/gray.svg" class="mi" mode=""></image>
                <view>
                    コピーしました
                </view>
            </view>
        </view>
    </view>
</template>

<script>
    import list from '../../components/list.vue'
    import tabbar from '../../components/tabbar.vue'
    import sp from "@/components/swiper-direct-com.vue"
    import svg30 from '../../static/svgjs/sv_30.js'
    import {
        getCardInfo,
        getShopInfo,
        getCalendar
    } from "@/api/index.js"
    var wv
    export default {
        components: {
            list,
            tabbar,
            sp
        },
        props: ["sid","name"],
        data() {
            return {
                value: 1,
                showA: false,
                show: false,
                clandarShows: false,
                caiDanShow: false,
                phoneShow: false,
                imgListShow: false,
                sum: 1,
                imgList: [],
                shopInfo: {},
                staff: [],
                today: {},
                todayTime: {},
                bsTime: [],
                links: "",
                arr: [],
                svgData30: '"' + svg30 + '"',
                styles: {
                    height: "200upx"
                },
                // currentWebview:null,
                wv: null
            };
        },
        created() {
            let that = this
            that.getShopInfo()
            that.today = that.getToday()
            that.getDay()
            that.svgData30 = that.changeColor(that.svgData30, that.color);
            // // #ifdef APP-PLUS
            // var currentWebview = this.$parent.$scope.$getAppWebview()
            // that.currentWebview = currentWebview
            // const query = uni.createSelectorQuery().in(this);
            // 	let topHeight = 0;
            // 	query.select('.header-top-bg').boundingClientRect(data => {
            // 		topHeight = data.height; // 头部元素高度
            // 	}).exec();
            //     setTimeout(function() {
            // 		let wv = currentWebview.children()[0];
            //         that.wv = wv
            // 		wv.setStyle({top:670,height: 180})
            // 	}, 1000); //如果是页面初始化调用时，需要延时一下
            // // #endif
        },
        // mounted() {
        // 	var that = this;
        // 	uni.$on('onPageScroll', function(sc) {//接收参数
        //         // #ifdef APP-PLUS
        //         let wv = that.wv
        //         wv.setStyle({top:670-sc,height: 180})
        //         // #endif
        // 		// that.scrollTop = data;
        // 	});
        // },
        methods: {
            closeThis(){
                let that = this
                // console.log(123123123123123123123123)
                that.$emit("close",1)
            },
            // changeColor(url, color) {
            //     let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#",
            //     "%23")); //转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
            //     return res;
            // },
            copy() {
                let that = this
                uni.setClipboardData({
                    data: "http://www.google.com", //要被复制的内容
                    success: () => { //复制成功的回调函数
                    uni.hideToast()
                        that.showA = true
                        setTimeout(() => {
                            that.showA = false
                        },2000)
                    }
                })
            },
            copy1(e) {
                let that = this
                uni.setClipboardData({
                    data: e, //要被复制的内容
                    success: () => { //复制成功的回调函数
                        uni.showToast({ //提示
                            title: 'リンクをコピーしました',
                            icon: "none"
                        })
                    }
                })
            },
            showphones() {
                this.phoneShow = true
            },
            scrollR() {

                this.caiDanShow = false
            },
            scrollL() {

            },
            leftClick() {
                uni.redirectTo({
                    url: "../../pages/index/index"
                })
            },
            toca() {
                let that = this
                that.$emit("toCa",that.sid)
                
            },
            getDay() {
                let that = this
                let data = {}
                data.id = that.sid
                data.type = "day"
                data.date = that.today.year + "-" + that.today.month
                getCalendar(data).then((res) => {
                    if (res.code == 200) {
                        let dat = new Date()
                        let idx = dat.getDate()
                        that.todayTime = res.data.day_business[idx]
                        console.log("i", res.data.day_business[idx])
                        that.bsTime = res.data.day_business[idx].business_time
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },
            // 〒606-0816  京都府京都市左京区下鴨松ノ木町53 加茂みたらし茶屋
            toMap() {
                let that = this
                let data = {}
                data.zip_code = that.shopInfo.zip_code
                data.address = that.shopInfo.address1 + that.shopInfo.address2
                that.$emit("toMap",data)
                
            },
            getShopInfo() {
                let that = this
                getShopInfo(that.sid).then((res) => {
                    console.log(1113333,res)
                    if (res.code == 200) {
                        that.shopInfo = res.data.shop
                        that.staff = res.data.admin3
                        uni.request({
                            url: "https://maps.googleapis.com/maps/api/geocode/json?address=〒" + that
                                .shopInfo.zip_code + " " + that.shopInfo.address1 + that.shopInfo.address2 +
                                "&key=AIzaSyAOQupmIQwbcpUgbLGynK3IwWxNzFDcQRI",
                            method: "get",
                            success(res) {
                                if (res) {
                                    that.arr.push(res.data.results[0].geometry.location)
                                    that.links = '/hybrid/html/maps.html?arr=' + encodeURIComponent(JSON
                                        .stringify(that.arr));
                                }
                            }
                        })
                    }
                })
            },
            closeCal() {
                this.show = false
            },
            getToday() {
                let date = new Date()
                let year = date.getFullYear()
                let month = (date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                let week = date.getDay()
                let sdate = date.getDate() < 10 ? '0' + date.getDate() : date.getDate()
                let wek
                wek = week == 0 ? '日' : week == 1 ? '月' : week == 2 ? '火' :
                    week == 3 ? '水' : week == 4 ? '木' : week == 5 ? '金' : '土'
                let obj = {}
                obj.week = wek
                obj.year = year
                obj.month = month
                obj.date = sdate
                return obj
            },
            imgClose() {
                this.imgListShow = false
            },
            rightClick() {
                this.caiDanShow = true
            },
            seed(e) {
                this.caiDanShow = false
            },
            clandarClose() {
                this.clandarShows = false
            },
            call() {
                let that = this
                that.$emit("makecall",that.shopInfo.phone)
                
            },
            lian(e) {
                plus.runtime.openURL(e)
            },
            shareClick(e) {
                plus.runtime.openURL(e)
            },
            heJiShow(index) {
                this.imgList = this.staff[index].image
                uni.previewImage({
                    urls: this.imgList
                })
                // this.imgListShow = true
            },
            change(e) {
                this.sum = e.detail.current + 1
            },
            chooseItem(e) {
                this.caiDanShow = false
                this.$emit("changes", e)
            },
        }
    }
</script>

<style lang="scss" scoped>
    .jj{
        font-size: 32rpx;
        // font-family: Noto Sans Kannada, Noto Sans Kannada;
        color: #1D1D1F;
        font-weight: bold;
        border-bottom: 2rpx solid #d2d2d7;
        padding: 40upx;
        white-space: pre-wrap;
    }
    
    .jj textarea{
        display: block;
        margin: 0 auto;
        width: 100% !important;
    }
    .titleTop{
        height: 112rpx;
        background: #FFFFFF;
        border-radius: 32rpx 32rpx 0 0;
        text-align: center;
        position: absolute;
        left: 0;
        top: 0;
        width: 100vw;
        font-size: 40rpx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: bold;
        color: #1D1D1F;
        line-height: 112rpx;
        z-index: 1080 !important;
    }
    .mask {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(29, 29, 31, 0.2);
        z-index: 999;

        .maskcontent {
            width: 356upx;
            height: 292upx;
            background: #fff;
            border-radius: 20upx;
            text-align: center;
            box-sizing: border-box;
            padding: 60upx;
            font-size: 32upx;
            color: #707070;
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);

            .mi {
                width: 100upx;
                height: 100upx;
                margin-bottom: 40upx;
            }

        }
    }

    .click-hover {
        background: rgba(26, 115, 232, .6) !important;
    }

    .hover1 {
        background: rgba(29, 29, 31, .16) !important;
    }

    .hover2 {
        background: rgba(26, 115, 232, .6) !important;
    }

    page {
        font-family: Hiragino Sans-W3, Hiragino Sans, Hiragino Sans-W5 !important;
    }

    ::v-deep .u-tabbar__content {
        height: 164rpx;
    }

    ::v-deep .u-tabbar__content__item-wrapper {
        padding-top: 20rpx;
    }

    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    .cop {
        width: 120upx;
        height: 52upx;
        line-height: 52rpx;
        text-align: center;
        color: #86868B;
        font-weight: 700;
        font-size: 24rpx;
        border: 2rpx solid #86868B;
        border-radius: 10rpx;
        z-index: 9 !important;
        background-color: #F5F5F7;
    }

    .u-nav-right {
        image {
            width: 48rpx;
            height: 48rpx;
        }
    }

    .items-boxs {

        .items {
            border-bottom: 1px solid #D2D2D7;

            .items-fle {
                width: 90%;
                margin: 0 auto;
                height: 150rpx;
                display: flex;
                // justify-content: space-between;
                align-items: center;

                .left {
                    font-size: 28rpx;
                    width: 114rpx;
                    font-family: Hiragino Sans-W3;
                    color: #707070;
                    flex-shrink: 0;
                }

                .midd {
                    font-size: 28rpx;
                    color: #1D1D1F;
                    margin-left: 40rpx;
                    width: 328rpx;

                    .blue {
                        color: #1A73E8;
                    }
                }

                .ri-img {
                    width: 236rpx;
                    height: 80rpx;
                    line-height: 80rpx;
                    text-align: center;
                    font-size: 28rpx;
                    color: #707070;
                    background: #F5F5F7;
                    border-radius: 20rpx;

                    image {
                        width: 38rpx;
                        height: 32rpx;
                        vertical-align: middle;
                    }

                    text {
                        margin-left: 12rpx;
                        vertical-align: middle;
                    }
                }

                .phone-btn {
                    width: 200rpx;
                    height: 80rpx;
                    line-height: 80rpx;
                    text-align: center;
                    background: #1A73E8;
                    font-size: 28rpx;
                    border-radius: 20rpx;
                    color: #FFFFFF;
                    z-index: 9
                }
            }
        }
    }

    .fix {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;

        .fix-cen {
            width: 90%;
            margin: 0 auto;
            height: 112rpx;
            line-height: 112rpx;
            font-size: 40rpx;
            background: #FFFFFF;
            border-radius: 28rpx;
            box-sizing: border-box;
            padding: 0 40rpx;
            margin-bottom: 20rpx;
            display: flex;
            align-items: center;

            image {
                width: 48rpx;
                height: 48rpx;
                margin-right: 33rpx;

            }

            text {

                color: #1A73E8;

            }

            .xiao {
                display: inline-block;
                width: 100%;
                color: #D93025;
                text-align: center;
            }

            .phone {
                margin-left: 50rpx;
            }
        }
    }

    .maps {
        width: 100%;
        height: 416rpx;
        // background-color: #F5F5F7;
        // padding-bottom: 60rpx;
        position: relative;

        .map {
            width: 100%;
            height: 100%;
        }


    }



    .map-btn {
        width: 232rpx;
        height: 68rpx;
        line-height: 68rpx;
        text-align: center;
        color: #86868B;
        font-weight: 700;
        font-size: 24rpx;
        border: 2rpx solid #86868B;
        border-radius: 10rpx;
        z-index: 9 !important;
        background-color: #F5F5F7;
        position: absolute;
        right: 40upx;
        bottom: 20upx
    }

    .zhu-item {
        border-top: 1px solid #D2D2D7;
        background: #FFFFFF;
        // height: 192rpx;
        position: relative;

        .zhu-box {
            width: 90%;
            margin: 40rpx auto;
            display: flex;

            .zhu-left {
                flex-shrink: 0;
                width: 112rpx;
                font-size: 28rpx;
                color: #707070;
                margin-right: 40rpx;
            }

            .zhu-right {
                font-size: 28rpx;
                color: #1D1D1F;

                .lian {
                    color: #007AFF;
                }
            }
        }
    }

    .zhu-item-border {
        border-bottom: 1px solid #D2D2D7;
        margin-bottom: 120rpx;
    }

    .staff {
        height: 92rpx;
        line-height: 92rpx;
        background: #F5F5F7;
        font-size: 40rpx;
        text-align: center;
        font-weight: 700;
        color: #1D1D1F;
    }

    .store-popel-box {
        background-color: #fff;
        padding: 60rpx 0;
        border-bottom: 1px solid #D2D2D7;

        .store-box {
            width: 90%;
            margin: 0 auto;
            display: flex;

            .store-left {
                flex-shrink: 0;
                border-radius: 8rpx;
                width: 278rpx;
                height: 370rpx;
                margin-right: 32rpx;
                position: relative;

                .store-img {
                    width: 100%;
                    height: 100%;
                    border-radius: 8rpx;
                }

                .store-heji {
                    position: absolute;
                    top: 8rpx;
                    left: 8rpx;
                    width: 52rpx;
                    height: 52rpx;
                    border-radius: 12rpx;
                    background: rgba(29, 29, 31, 0.4);
                }
            }

            .store-right {
                color: #1D1D1F;
                font-size: 32rpx;

                .store-name {
                    font-weight: 700;
                    margin-bottom: 40rpx;


                }

                .store-identity {
                    margin-bottom: 36rpx;
                }

                .store-des {
                    font-size: 28rpx;
                    color: #707070;
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

    .shar {
        width: 90%;
        margin: 0 auto;
        margin-top: 44rpx;
        margin-bottom: 44rpx;

        .shar-tit {
            color: #1D1D1F;
            font-size: 32rpx;
            text-align: center;
            font-weight: 700;
        }

        .shar-btn {
            margin-top: 24rpx;
            width: 100%;
            height: 112rpx;
            line-height: 112rpx;
            text-align: center;
            background: #FFFFFF;
            box-shadow: 0px 0px 12rpx 2rpx rgba(29, 29, 31, 0.16);
            border-radius: 12rpx;
            border: 2rpx solid #EAEAEA;

            image {
                width: 72rpx;
                height: 72rpx;
                vertical-align: middle;
            }

            text {
                vertical-align: middle;
                margin-left: 38rpx;
                color: #1D1D1F;
                font-size: 32rpx;
            }
        }
    }

    .shar-line {
        width: 100%;
        height: 1px;
        background-color: #EAEAEA;
        margin-bottom: 80rpx;
    }

    .sns {
        width: 80%;
        margin: 0rpx auto;
        padding-bottom: 240rpx;

        .sns-tit {
            font-size: 32rpx;
            color: #1D1D1F;
            font-weight: 700;
            margin-bottom: 40rpx;
            text-align: center;
        }

        .img-sns {
            display: flex;
            justify-content: center;
            align-items: center;
            image {
                width: 72rpx;
                height: 72rpx;
                margin: 0 40rpx;
            }
        }
    }
</style>