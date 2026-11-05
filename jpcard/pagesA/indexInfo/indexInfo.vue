<template>
    <view class="cs-si">
        <view class="titleTop">
            <view v-text="name"></view>
            <image src="../../static/svg/button.svg" alt="" style="width: 48upx;height: 48upx;position: absolute;left: 32upx;top: 32upx;" @click="closeThis">
            <image/>
        </view>
        <view style="height: 112upx;"></view>
        <!-- [カスタムエイド 2026-11-03] Near By のプレビューを、店舗情報の画面(最新)と同じ作りに(storeInformation.vue から写した。地図のボタンは今までどおり toMap) -->
        <view class="swiper">
            <!-- [カスタムエイド 2026-10-19] 点(●●●)をやめ、2枚以上のときだけ右下に「3 / 12」と今の位置を表示。
                 画像を押したら全画面で表示し、左右にスワイプで次の画像(メニューの写真と同じ) -->
            <u-swiper :list="shopInfo.carousel" height="250" :indicator="false" :radius="0" :autoplay="false"
                @change="topChange" @click="topOpen"></u-swiper>
            <view class="cs-top-count" v-if="shopInfo.carousel && shopInfo.carousel.length > 1">{{topIndex + 1}} / {{shopInfo.carousel.length}}</view>
        </view>
        <view class="items-boxs">
            <view class="jj" v-if="shopInfo.des" v-text="shopInfo.des">
            </view>
            <!-- クチコミ（Googleビジネス連携店舗のみ表示） -->
            <view class="review-entry" v-if="reviewInfo.connected" @click="toReviews" hover-class="hover1">
                <view class="review-badge">★</view>
                <view class="review-score">{{ reviewInfo.avg_rating ? reviewInfo.avg_rating.toFixed(1) : '0.0' }}</view>
                <view class="review-stars">
                    <text v-for="i in 5" :key="i"
                        :class="i <= Math.round(reviewInfo.avg_rating) ? 'star-on' : 'star-off'">★</text>
                </view>
                <view class="review-count">・{{ reviewInfo.total }}クチコミ</view>
                <view class="review-arrow">›</view>
            </view>
            <view class="cs-group">
            <view class="items">
                <view class="items-fle">
                    <view class="left">
                        TEL　
                    </view>
                    <view class="midd w328" v-text="shopInfo.phone"></view>
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
                    <view class="midd" v-text="shopInfo.business_time">
                    </view>
                </view>
            </view>
            <view class="items">
                <view class="items-fle">
                    <view class="left">
                        定休日
                    </view>
                    <view class="midd" v-text="shopInfo.rest_time">
                    </view>
                </view>
            </view>
            <view class="items">
                <view class="items-fle" style="height: auto !important;padding: 26upx 0;">
                    <view class="left">
                        本日
                    </view>
                    <view class="midd w328">
                        <view class="">
                            {{today.month}}月{{today.date}}日({{today.week}})
                        </view>
                        <view :style="`color:${shopInfo.business_color}`">{{shopInfo.business_name}}</view>
                        <view class="" v-if="bsTime.length > 0 && shopInfo.business_name != '休業日'">
                            <view v-for="(item,index) in bsTime">
                                {{item.start}}~{{item.end}}
                            </view>
                        </view>
                    </view>
                    <view class="right ri-img" @click="toca"
                        style="border: 2upx solid #707070;display: flex;align-items: center;justify-content: center;"
                        hover-class="cs-map-hover">
                        <!-- [カスタムエイド 2026-10-25] アイコンもボタンの文字と同じ青に -->
                        <view class="cs-cal-ic"></view>
                        <text>カレンダー</text>
                    </view>
                </view>
            </view>
            </view>
        </view>
        <view class="cs-group">
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
            <!-- [カスタムエイド 2026-10-19] 地図ボタンの前の空白(39px)をなくし、ボタンは住所のすぐ下に並べる -->
            <!-- [カスタムエイド 2026-10-25] 薄い青・横いっぱい。頭に地図のアイコン(下のメニューの「店舗情報」と同じ形・赤) -->
            <view class="map-btn" @click="toMap" hover-class="cs-map-hover">
                <view class="cs-map-ic" :style="`backgroundImage: url(${svgMap})`"></view>
                <text>地図アプリで見る</text>
            </view>
        </view>
        <view class="zhu-item">
            <view class="zhu-box">
                <view class="zhu-left">
                    アクセス　　
                </view>
                <view class="midd ml" v-text="shopInfo.traffic_explain">
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
                <!-- [カスタムエイド 2026-10-19] コピーをリンクの右に並べて1行に(今までは別の行で、上下に余白があった) -->
                <view class="cop" hover-class="cs-btn-hover" @click="copy1(shopInfo.web_url)">
                    コピー
                </view>
            </view>
        </view>
        <view class="zhu-item">
            <view class="zhu-box">
                <view class="zhu-left">
                    利用可能決済
                </view>
                <view class="midd ml" v-text="shopInfo.pay_type">
                </view>
            </view>
        </view>
        <view class="zhu-item zhu-item-border">
            <view class="zhu-box">
                <view class="zhu-left">
                    備考
                </view>
                <view class="midd ml" v-text="shopInfo.remark">
                </view>
            </view>
        </view>
        </view>
        <view class="staff">
            STAFF
        </view>
        <view class="cs-group" v-if="shopInfo.staff_des == 1 && staff.length > 0">
        <view class="store-popel-box" v-for="(item,index) in staff" :key="index" v-if="shopInfo.staff_des == 1">
            <view class="store-box">
                <view class="store-left" v-if="staff.length > 0">
                    <!-- [カスタムエイド 2026-10-19] 写真を押したら全画面で表示。2枚以上のときだけ右下に枚数(メニュー一覧と同じ) -->
                    <image :src="item.image[0]" mode="aspectFill" class="store-img" @click="heJiShow(index)"></image>
                    <view class="cs-count" v-if="item.image && item.image.length > 1" @click="heJiShow(index)">
                        <image src="../../static/svg/multiPhoto.svg" class="cs-count-icon"></image>
                        <text class="cs-count-num">{{item.image.length}}</text>
                    </view>
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
        </view>
        <view class="shar" v-if="shopInfo.share_line == 1">
            <view class="shar-tit">
                友達にアプリを紹介
            </view>
            <view class="shar-btn" @click="copy">
                <image src="../../static/icons/infoa.png" mode=""></image>
                <view class="w">アプリダウンロードリンクをコピー</view>
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
        <!-- [カスタムエイド 2026-10-25] 一番下の余白(下のメニューに隠れないように) -->
        <view class="cs-si-bottom">
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
    import svgPin from '../../static/svgjs/s2.js' // [カスタムエイド 2026-11-03] 地図アプリで見るのアイコン
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
                reviewInfo: { connected: false, avg_rating: 0, total: 0 }, // [カスタムエイド 2026-11-03] クチコミ
                svgMap: '"' + svgPin + '"', // [カスタムエイド 2026-11-03]

                topIndex: 0, // [カスタムエイド 2026-10-19] TOP画像の今の位置
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
            that.svgData30 = that.changeColor(that.svgData30, that.color || '#000000');
            that.svgMap = that.changeColor(that.svgMap, '#EA4335'); // [カスタムエイド 2026-11-03]
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
            changeColor(url, color) { // [カスタムエイド 2026-11-03] 使っていたのに無くなっていた(コメントになっていた)ので戻した
                return url.replace(/%23[a-zA-Z0-9]{6}/g, (color || '#000000').replace("#", "%23"));
            },
            toReviews() { // [カスタムエイド 2026-11-03] クチコミ(店舗情報と同じ)
                uni.navigateTo({ url: '../googleReviews/googleReviews?sid=' + this.sid + '&name=' + encodeURIComponent(this.shopInfo.shop_name || '') })
            },
            // [カスタムエイド 2026-10-19] TOP画像:今の位置を覚える/押したら全画面で表示
            topChange(e) {
                this.topIndex = (e && e.current) || 0
            },
            topOpen(index) {
                let list = (this.shopInfo.carousel || []).map((v) => (typeof v == 'string' ? v : (v && (v.url || v.image)) || '')).filter((v) => v)
                if (!list.length) return
                uni.previewImage({ current: index || 0, urls: list, indicator: 'number', loop: list.length > 1 })
            },
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
                        that.bsTime = res.data.day_business[idx].business_time
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                }).catch(() => {})
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
                    if (res.code == 200) {
                        that.shopInfo = res.data.shop
                        that.staff = res.data.admin3
                        that.reviewInfo = res.data.review || { connected: false, avg_rating: 0, total: 0 } // [カスタムエイド 2026-11-03]
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
                }).catch(() => {})
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

    /* [カスタムエイド 2026-10-19] TOP画像の枚数表示(メニュー写真の枚数ラベルと同じ形) */
    .swiper {
        position: relative;
    }
    .cs-top-count {
        position: absolute;
        right: 24rpx;
        bottom: 24rpx;
        z-index: 2;
        padding: 6rpx 20rpx;
        border-radius: 24rpx;
        background: rgba(0, 0, 0, 0.6);
        color: #FFFFFF;
        font-size: 24rpx;
        font-weight: 500;
        letter-spacing: 1rpx;
        pointer-events: none;
    }

    /* ===== [カスタムエイド 2026-10-19] 店舗情報の間延びを直す(モック「Card-San App UI 改善案」) ===== */
    /* 説明文:太字のまま、余白と行間を詰める */
    .jj {
        padding: 28upx 40upx !important;
        line-height: 1.6;
    }
    /* TEL・営業時間などの行:高さの固定(75px)をやめ、上下の余白で決める */
    .items-boxs .items .items-fle {
        height: auto !important;
        min-height: 0 !important;
        padding: 26rpx 0 !important;
    }
    .items-boxs .items .items-fle .left,
    .zhu-item .zhu-box .zhu-left {
        width: 140rpx !important; // 「アクセス」が2行に割れないように
    }
    .items-boxs .items .items-fle .midd,
    .zhu-item .zhu-box .zhu-right,
    .ml {
        line-height: 1.6;
    }
    .zhu-item .zhu-box {
        margin: 26rpx auto !important;
        align-items: center;
    }
    .zhu-item .zhu-box .zhu-right {
        flex: 1;
        min-width: 0;
        word-break: break-all;
    }
    /* 地図アプリで見る:住所のすぐ下・右寄せ(線と重ならないように、浮かせずに並べる) */
    .map-btn {
        position: static !important;
        display: block;
        margin: -8rpx 5% 26rpx auto;
    }
    /* HP:リンクの右にコピー */
    .zhu-item .zhu-box .cop {
        flex-shrink: 0;
        margin-left: 20rpx;
    }
    /* カレンダー・地図アプリで見る・コピー:地の色 #F5F5F7、押すと少し濃く */
    .map-btn,
    .cop,
    .ri-img {
        background-color: #F5F5F7 !important;
    }
    .cs-btn-hover {
        background-color: #E5E5EA !important;
    }
    /* STAFF:写真を小さく(139×185px → 90×120px)、文字と余白も詰める */
    .staff {
        height: 80rpx !important;
        line-height: 80rpx !important;
        font-size: 32rpx !important;
        letter-spacing: 2rpx;
    }
    .store-popel-box {
        padding: 32rpx 0 !important;
    }
    .store-popel-box .store-box .store-left {
        width: 180rpx !important;
        height: 240rpx !important;
        margin-right: 28rpx !important;
        border-radius: 12rpx;
        overflow: hidden;
    }
    .store-popel-box .store-box .store-right {
        min-width: 0;
    }
    .store-popel-box .store-box .store-right .store-name {
        font-size: 30rpx;
        margin-bottom: 8rpx !important;
    }
    .store-popel-box .store-box .store-right .store-identity {
        font-size: 26rpx;
        color: #515154;
        margin-bottom: 12rpx !important;
    }
    .store-popel-box .store-box .store-right .store-des {
        font-size: 26rpx !important;
        line-height: 1.6;
    }
</style>
<style lang="scss" scoped>
    /* [カスタムエイド 2026-11-03] 店舗情報(storeInformation.vue)の見た目をそのまま写した。Near By のプレビューを最新の作りにするため */
    .review-entry{
        display: flex;
        align-items: center;
        padding: 28upx 40upx;
        border-bottom: 2rpx solid #d2d2d7;
    }
    .review-entry .review-badge{
        width: 44upx;
        height: 44upx;
        border-radius: 10upx;
        background: #E8F0FE;
        color: #1A73E8;
        font-size: 26upx;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 18upx;
    }
    .review-entry .review-score{
        font-size: 32upx;
        font-weight: bold;
        color: #1D1D1F;
        margin-right: 12upx;
    }
    .review-entry .review-stars{
        font-size: 30upx;
    }
    .review-entry .review-stars .star-on{
        color: #FBBC04;
    }
    .review-entry .review-stars .star-off{
        color: #D2D2D7;
    }
    .review-entry .review-count{
        flex: 1;
        font-size: 26upx;
        color: #707070;
        margin-left: 8upx;
    }
    .review-entry .review-arrow{
        font-size: 44upx;
        color: #C7C7CC;
        margin-left: 12upx;
    }

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
    .w328{
        width: 328rpx !important;
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
    .ml{
        font-size: 28rpx;
        color: #1D1D1F;
        white-space: pre-wrap;
    }
    .items-boxs {
        // margin-top: 80rpx;

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
                    white-space: pre-wrap;
                    
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
            text-align: center;
            background: #FFFFFF;
            box-shadow: 0px 0px 12rpx 2rpx rgba(29, 29, 31, 0.16);
            border-radius: 12rpx;
            border: 2rpx solid #EAEAEA;
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 14upx 0;
            font-size: 28upx;
            justify-content: center;
            
            image {
                width: 60rpx;
                height: 60rpx;
            }

            .w {
                margin-left: 20rpx;
                color: #1D1D1F;
                font-size: 28rpx;
                text-align: left;
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

    /* [カスタムエイド 2026-10-19] TOP画像の枚数表示(メニュー写真の枚数ラベルと同じ形) */
    .swiper {
        position: relative;
    }
    .cs-top-count {
        position: absolute;
        right: 24rpx;
        bottom: 24rpx;
        z-index: 2;
        padding: 6rpx 20rpx;
        border-radius: 24rpx;
        background: rgba(0, 0, 0, 0.6);
        color: #FFFFFF;
        font-size: 24rpx;
        font-weight: 500;
        letter-spacing: 1rpx;
        pointer-events: none;
    }

    /* ===== [カスタムエイド 2026-10-19] 店舗情報の間延びを直す(モック「Card-San App UI 改善案」) ===== */
    /* 説明文:太字のまま、余白と行間を詰める */
    .jj {
        padding: 28upx 40upx !important;
        line-height: 1.6;
    }
    /* TEL・営業時間などの行:高さの固定(75px)をやめ、上下の余白で決める */
    .items-boxs .items .items-fle {
        height: auto !important;
        min-height: 0 !important;
        padding: 26rpx 0 !important;
    }
    .items-boxs .items .items-fle .left,
    .zhu-item .zhu-box .zhu-left {
        width: 140rpx !important; // 「アクセス」が2行に割れないように
    }
    .items-boxs .items .items-fle .midd,
    .zhu-item .zhu-box .zhu-right,
    .ml {
        line-height: 1.6;
    }
    .zhu-item .zhu-box {
        margin: 26rpx auto !important;
        align-items: center;
    }
    .zhu-item .zhu-box .zhu-right {
        flex: 1;
        min-width: 0;
        word-break: break-all;
    }
    /* 地図アプリで見る:住所のすぐ下・右寄せ(線と重ならないように、浮かせずに並べる) */
    .map-btn {
        position: static !important;
        display: block;
        margin: -8rpx 5% 26rpx auto;
    }
    /* HP:リンクの右にコピー */
    .zhu-item .zhu-box .cop {
        flex-shrink: 0;
        margin-left: 20rpx;
    }
    /* カレンダー・地図アプリで見る・コピー:地の色 #F5F5F7、押すと少し濃く */
    .map-btn,
    .cop,
    .ri-img {
        background-color: #F5F5F7 !important;
    }
    .cs-btn-hover {
        background-color: #E5E5EA !important;
    }
    /* STAFF:写真を小さく(139×185px → 90×120px)、文字と余白も詰める */
    .staff {
        height: 80rpx !important;
        line-height: 80rpx !important;
        font-size: 32rpx !important;
        letter-spacing: 2rpx;
    }
    .store-popel-box {
        padding: 32rpx 0 !important;
    }
    .store-popel-box .store-box .store-left {
        width: 180rpx !important;
        height: 240rpx !important;
        margin-right: 28rpx !important;
        border-radius: 12rpx;
        overflow: hidden;
    }
    .store-popel-box .store-box .store-right {
        min-width: 0;
    }
    .store-popel-box .store-box .store-right .store-name {
        font-size: 30rpx;
        margin-bottom: 8rpx !important;
    }
    .store-popel-box .store-box .store-right .store-identity {
        font-size: 26rpx;
        color: #515154;
        margin-bottom: 12rpx !important;
    }
    .store-popel-box .store-box .store-right .store-des {
        font-size: 26rpx !important;
        line-height: 1.6;
    }

    /* ===== [カスタムエイド 2026-10-25] 店舗情報:まとまりごとに白いカード(モック「Card-San App 画面改善モック」2回目) ===== */
    .cs-si {
        background: #F5F5F7;
        min-height: 100vh;
    }
    /* 画像の下のすき間を2倍に(28 → 56) */
    .jj {
        padding-top: 56upx !important;
        background: #FFFFFF;
        border-bottom: none !important;
    }
    .cs-group,
    .review-entry,
    .shar,
    .sns {
        background: #FFFFFF;
        border-radius: 28rpx;
        margin: 24rpx 24rpx 0 !important;
        overflow: hidden;
        width: auto !important;
    }
    .review-entry {
        border-bottom: none !important;
        padding: 28upx 32upx !important;
    }
    /* カードの中の行:左右の余白をそろえ、ボタンの右端をそろえる */
    .items-boxs .items .items-fle,
    .zhu-item .zhu-box {
        width: auto !important;
        margin-left: 32rpx !important;
        margin-right: 32rpx !important;
    }
    .items-boxs .items {
        border-bottom: 1px solid #E5E5EA !important;
    }
    .items-boxs .cs-group .items:last-child {
        border-bottom: none !important;
    }
    .zhu-item {
        border-top: 1px solid #E5E5EA !important;
        background: transparent !important;
    }
    .cs-group > .zhu-item:first-child {
        border-top: none !important;
    }
    .zhu-item-border {
        border-bottom: none !important;
        margin-bottom: 0 !important;
    }
    .items-boxs .items .items-fle .midd {
        flex: 1;
        min-width: 0;
    }
    .items-boxs .items .items-fle .right,
    .zhu-item .zhu-box .cop {
        margin-left: auto !important;
        flex-shrink: 0;
    }
    /* ボタン:色・大きさは今のまま。角を丸く、文字は太字にしない */
    .items-boxs .items .items-fle .phone-btn,
    .items-boxs .items .items-fle .ri-img,
    .cop {
        border-radius: 999rpx !important;
        font-weight: normal !important;
    }
    /* 地図アプリで見る:住所のすぐ下・横いっぱい・薄い青。線はボタンの下(次の行の上の線) */
    .map-btn {
        position: static !important;
        width: auto !important;
        height: 80rpx !important;
        line-height: normal !important;
        margin: 0 32rpx 32rpx !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        border: none !important;
        border-radius: 999rpx !important;
        background-color: #EEF4FF !important;
        color: #1A73E8 !important;
        font-size: 28rpx !important;
        font-weight: normal !important;
    }
    .cs-map-ic {
        width: 40rpx;
        height: 40rpx;
        margin-right: 10rpx;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
    }
    .cs-map-hover {
        background-color: #DCE8FD !important;
    }
    /* STAFF:見出しは背景の上に、写真と紹介は白いカードに */
    .staff {
        background: transparent !important;
        margin-top: 16rpx;
    }
    .store-popel-box {
        background: transparent !important;
        border-bottom: 1px solid #E5E5EA !important;
    }
    .cs-group .store-popel-box:last-child {
        border-bottom: none !important;
    }
    .store-popel-box .store-box {
        width: auto !important;
        margin: 0 32rpx !important;
    }
    /* 友達に紹介・SNS もカードに */
    .shar {
        padding: 32rpx;
    }
    .shar .shar-btn {
        box-shadow: none !important;
    }
    .shar-line {
        display: none;
    }
    .sns {
        padding: 32rpx 0 40rpx !important;
    }
    .cs-si-bottom {
        height: 240rpx;
    }

    /* [カスタムエイド 2026-10-25] 読み込めなかったとき */
    .cs-fail {
        background: #FFFFFF;
        border-radius: 28rpx;
        margin: 24rpx;
        padding: 40rpx 32rpx;
        text-align: center;
    }
    .cs-fail-t {
        font-size: 30rpx;
        font-weight: 700;
        color: #1D1D1F;
    }
    .cs-fail-m {
        margin-top: 12rpx;
        font-size: 26rpx;
        color: #6E6E73;
        line-height: 1.6;
    }
    .cs-fail-b {
        margin: 28rpx auto 0;
        width: 360rpx;
        height: 80rpx;
        line-height: 80rpx;
        border-radius: 999rpx;
        background: #EEF4FF;
        color: #1A73E8;
        font-size: 28rpx;
    }
    /* [カスタムエイド 2026-10-25] 項目名(アクセス・利用可能決済など)が2行に割れないように、幅を広げて1行に */
    .items-boxs .items .items-fle .left,
    .zhu-item .zhu-box .zhu-left {
        width: 176rpx !important;
        white-space: nowrap;
        margin-right: 24rpx !important;
    }
    .items-boxs .items .items-fle .midd {
        margin-left: 0 !important;
    }

    /* ===== [カスタムエイド 2026-10-25 c] 店舗情報:ボタンの色・STAFF の位置・カードの間 ===== */
    /* 電話する・カレンダー・地図アプリで見る:薄い青の地・青の文字・青の枠線(3つとも同じ配色) */
    .items-boxs .items .items-fle .phone-btn,
    .items-boxs .items .items-fle .ri-img,
    .map-btn {
        background-color: #EEF4FF !important;
        color: #1A73E8 !important;
        border: 2rpx solid #1A73E8 !important;
        box-sizing: border-box;
    }
    .items-boxs .items .items-fle .ri-img {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }
    .items-boxs .items .items-fle .ri-img text {
        color: #1A73E8 !important;
    }
    .cs-cal-ic {
        width: 36rpx;
        height: 36rpx;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%231A73E8' stroke-width='1.8' stroke-linecap='round'%3E%3Crect x='3' y='5' width='18' height='16' rx='2.5'/%3E%3Cpath d='M3 10h18M8 3v4M16 3v4'/%3E%3Ccircle cx='8' cy='14' r='.6' fill='%231A73E8'/%3E%3Ccircle cx='12' cy='14' r='.6' fill='%231A73E8'/%3E%3Ccircle cx='16' cy='14' r='.6' fill='%231A73E8'/%3E%3Ccircle cx='8' cy='17.5' r='.6' fill='%231A73E8'/%3E%3Ccircle cx='12' cy='17.5' r='.6' fill='%231A73E8'/%3E%3C/svg%3E") center / contain no-repeat;
        flex-shrink: 0;
    }
    .click-hover,
    .cs-map-hover {
        background-color: #DCE8FD !important;
    }
    /* カードとカードの間を 1.5倍に(24 → 36) */
    .cs-group,
    .review-entry,
    .shar,
    .sns {
        margin-top: 36rpx !important;
    }
    /* STAFF:スタッフのカードのすぐ上(約5px)に */
    .staff {
        height: auto !important;
        line-height: 1.4 !important;
        margin: 48rpx 0 0 !important;
        padding: 0 !important;
    }
    .staff + .cs-group {
        margin-top: 10rpx !important;
    }

    /* [カスタムエイド 2026-11-03] カードの影(2026-11-03):下へ1px・ぼかし4px・黒10%。グレーの背景と白いカードの境目を分かりやすく */
    .cs-group, .review-entry, .shar, .sns, .cs-fail {
        box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.10) !important;
    }
</style>