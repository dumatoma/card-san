<template>
    <view class="">
        <u-navbar :title="info.shop_name" :leftIconColor="color" :titleStyle="{'color':color}" :bgColor="bgColor"
            :placeholder="true" :safeAreaInsetTop="true" :border="true" @rightClick="rightClick" @leftClick="leftClick">
            <view class="u-nav-right" slot="right">
                <view :style="`backgroundImage: url(${svgData30})`" style="width: 50upx;height: 50upx;"
                    class="iconright" @click=""></view>
            </view>
        </u-navbar>
        <view>
            <view class="top-box">
                <view class="con">
                    <view class="left">
                        NO:{{info.member_no || '-'}}
                    </view>
                    <view class="title" v-text="`${info.name} さん`"></view>
                    <view class="img" v-if="info.card_type != 3">
                        <image :src="info.qrcode" mode=""></image>
                    </view>
                    <view class="img" v-else>
                        <image class="tiaoxing" :src="info.qrcode" mode=""></image>
                    </view>
                    <view class="order-sn" v-text="info.member_id" v-if="info.is_point == 0"></view>
                </view>
            </view>
            <view class="cardInfo">
                <view class="cardTitle" v-text="`${info.card_name}`"></view>
                <view class="cardY" v-if="info.card_type == 1" :style="`background:${info.background_color}`">
                    <view class="cardInner">
                        <view class="seal" v-for="(item,index) in info.seals">
                            <text v-text="index + 1"></text>
                            <view class="sealed" v-if="item.sealed">
                                <view style="font-size: 24upx;height: 20upx;"></view>
                                <image :src="item.seal_icon" mode="" v-if="item.seal_icon.includes('https')"></image>
                                <view :style="`backgroundImage: url(${svgData1})`" class="icon" v-if="iconNum == 1">
                                </view>
                                <view :style="`backgroundImage: url(${svgData2})`" class="icon" v-if="iconNum == 2">
                                </view>
                                <view :style="`backgroundImage: url(${svgData3})`" class="icon" v-if="iconNum == 3">
                                </view>
                                <view :style="`backgroundImage: url(${svgData4})`" class="icon" v-if="iconNum == 4">
                                </view>
                                <view :style="`backgroundImage: url(${svgData5})`" class="icon" v-if="iconNum == 5">
                                </view>
                                <view :style="`backgroundImage: url(${svgData6})`" class="icon" v-if="iconNum == 6">
                                </view>
                                <view :style="`backgroundImage: url(${svgData7})`" class="icon" v-if="iconNum == 7">
                                </view>
                                <view :style="`backgroundImage: url(${svgData8})`" class="icon" v-if="iconNum == 8">
                                </view>
                                <view class="sealTime" v-text="formatTime(item.time)"></view>
                            </view>
                        </view>
                    </view>
                </view>
                <view class="app-border" v-if="info.card_type == 2" :style="`background:${info.background_color}`">
                    <view class="apps">
                        <view class="apps-top">
                            <view class="apps-top-text no" style="width: 10%;">
                                NO
                            </view>
                            <view class="apps-top-text day-pay" style="width: 30%;">
                                日付
                            </view>
                            <view class="apps-top-img img-width" style="width: 20%;">
                                <image src="../../static/image/record2.png" mode=""></image>
                            </view>
                            <view class="apps-top-img img2-width" style="width: 20%;">
                                <image src="../../static/image/record4.png" mode=""></image>
                            </view>
                            <view style="width: 30%;"></view>
                        </view>
                        <view class="apps-items" v-for="(item,index) in info.seals" :key="index">
                            <view class="apps-ind" style="width: 10%;">
                                {{(index + 1) + '.'}}
                            </view>
                            <view class="apps-time data" style="width: 30%;" v-text="formatTime(item.time)"></view>
                            <view class="apps-time time" style="width: 20%;" v-text="formatT(item.time)"></view>
                            <view class="apps-time uname" style="width: 30%;" v-text="item.admin_name"></view>
                            <view class="apps-img" v-if="item.sealed" style="width: 20%;text-align: center;">
                                <image :src="item.seal_icon" mode="" v-if="item.seal_icon.includes('https')"></image>
                                <view>
                                    <view :style="`backgroundImage: url(${svgData1})`" class="icon"
                                        style="width: 40upx;height: 40upx;" v-if="iconNum == 1"></view>
                                    <view :style="`backgroundImage: url(${svgData2})`" class="icon"
                                        style="width: 40upx;height: 40upx;" v-if="iconNum == 2"></view>
                                    <view :style="`backgroundImage: url(${svgData3})`" class="icon"
                                        style="width: 40upx;height: 40upx;" v-if="iconNum == 3"></view>
                                    <view :style="`backgroundImage: url(${svgData4})`" class="icon"
                                        style="width: 40upx;height: 40upx;" v-if="iconNum == 4"></view>
                                    <view :style="`backgroundImage: url(${svgData5})`" class="icon"
                                        style="width: 40upx;height: 40upx;" v-if="iconNum == 5"></view>
                                    <view :style="`backgroundImage: url(${svgData6})`" class="icon"
                                        style="width: 40upx;height: 40upx;" v-if="iconNum == 6"></view>
                                    <view :style="`backgroundImage: url(${svgData7})`" class="icon"
                                        style="width: 40upx;height: 40upx;" v-if="iconNum == 7"></view>
                                    <view :style="`backgroundImage: url(${svgData8})`" class="icon"
                                        style="width: 40upx;height: 40upx;" v-if="iconNum == 8"></view>
                                </view>

                            </view>
                        </view>
                    </view>
                </view>
                <view class="cardInfos">
                    <view class="pri-box" v-if="info.card_type == 3 && info.is_point == 1">
                        <view class="pri">
                            保有ポイント数
                        </view>
                        <view class="pri-sum" v-text="`${member.point}pt`"></view>
                    </view>
                    <view class="ciItem" v-if="info.expire_time != 'なし'">
                        <view class="ciLeft">
                            有効期限
                        </view>
                        <view class="ciRight" v-text="info.expire_time" :style="info.expire_time == '有効期限切れ'?'color:#d93025' : ''"></view>
                    </view>
                    <view class="ciItem" v-if="info.remark">
                        <view class="ciLeft">
                            備　考
                        </view>
                        <view class="ciRight" style="white-space: pre-wrap;" v-text="info.remark"></view>
                    </view>
                </view>
            </view>
            <view class="appoint" v-if="orders.length > 0">
                <view class="appointTitle">
                    予 約
                </view>
                <view class="appointItem" v-for="(item,index) in orders">
                    <view class="aItem" style="width: 100%;">
                        <view class="aItemImage" style="position: relative;top: 10upx;">
                            <image src="../../static/image/record1.png" mode=""></image>
                        </view>
                        <view class="aTime" style="flex: 1;">
                            <view class="" style="display: flex;align-items: center;justify-content: space-between;flex: 1;">
                                <view class="time">
                                    {{item.date}}({{item.week}})　{{item.start}}〜{{item.end}}
                                </view>
                                <view class="aItem" style="align-items: center;flex: 1;box-sizing: border-box;padding-left: 30upx;">
                                    <view class="aItemImage" style="width: 56upx;height: 56upx;">
                                        <image src="../../static/image/record3.png" mode=""></image>
                                    </view>
                                    <view class="aUser" style="flex-shrink: 0;" v-text="item.admin_name"></view>
                                </view>
                            </view>

                            <view class="copy" v-text="item.menu_name"></view>
                        </view>
                    </view>
                </view>
            </view>
            <view class="special" v-if="discount_images.length> 0">
                <view class="specialTitle">
                    特 典
                </view>
                <u-swiper :list="discount_images" height="250" :indicator="true" indicatorMode="dot" :radius="0"
                    indicatorActiveColor="#000000" :autoplay="false" indicatorInactiveColor="rgba(0,0,0,.3)"></u-swiper>
                <view class="remark" style="white-space: pre-wrap;" v-text="info.discount_content"></view>
            </view>
        </view>
        <view class="manshows">
            <u-popup :show="manShow" mode="center" @close="close" :safe-area-inset-bottom="false">
                <view class="pup-con">
                    <view class="pup-img">
                        <image src="../../static/image/paishou.png" mode=""></image>
                    </view>
                    <view class="pup-tit">
                        お手持ちのカードの
                    </view>
                    <view class="pup-tit">
                        スタンプが満了しました！
                    </view>
                    <view class="pup-des pup-top">
                        カードの更新や特典など
                    </view>
                    <view class="pup-des">
                        詳しくはスタッフにお尋ねください。
                    </view>
                </view>
                <view class="pup-close" @click="manShow = false">
                    閉じる
                </view>
            </u-popup>
        </view>
        <list :caiDanShow="caiDanShow" :mid="id" :sid="sid" :tabs="tabs" @seed="seed" @selected="chooseItem"></list>
        <view style="height: 100upx;"></view>
       <!-- <view class="mask">
            <view class="maskContent">
                <view class="man">
                    <image src="../../static/image/man.png" mode=""></image>
                </view>
                <view class="know">
                    閉じる
                </view>
            </view>
        </view> -->
    </view>
</template>

<script>
    import sp from "@/components/swiper-direct-com.vue"
    import list from '../../components/list.vue'
    import svg1 from '../../static/svgjs/sv_01.js'
    import svg2 from '../../static/svgjs/sv_02.js'
    import svg3 from '../../static/svgjs/sv_03.js'
    import svg4 from '../../static/svgjs/sv_04.js'
    import svg5 from '../../static/svgjs/sv_05.js'
    import svg6 from '../../static/svgjs/sv_06.js'
    import svg7 from '../../static/svgjs/sv_07.js'
    import svg8 from '../../static/svgjs/sv_08.js'
    import svg30 from '../../static/svgjs/sv_30.js'
    import {
        getCardInfo
    } from "@/api/index.js"
    // import tabbar from '../../components/tabbar.vue'
    export default {
        components: {
            list,
            sp
        },
        props: ["color", "id", "bgColor", "tabs", "sid", "sp","refresh"],
        data() {
            return {
                manShow: false,
                value: 0,
                iconNum: 0,
                card_no: "",
                caiDanShow: false,
                info: {},
                tableList: [],
                list: [],
                orders: [],
                svgData1: '"' + svg1 + '"',
                svgData2: '"' + svg2 + '"',
                svgData3: '"' + svg3 + '"',
                svgData4: '"' + svg4 + '"',
                svgData5: '"' + svg5 + '"',
                svgData6: '"' + svg6 + '"',
                svgData7: '"' + svg7 + '"',
                svgData8: '"' + svg8 + '"',
                svgData30: '"' + svg30 + '"',
                discount_images: [],
                member:{}
            };
        },
        watch: {
            color: function(newVal, oldVal) {
                let that = this
                this.svgData30 = that.changeColor(that.svgData30, that.color);
            },
            refresh:function(newVal, oldVal) {
                let that = this
                if(newVal == true){
                    getCardInfo(this.id).then((res) => {
                        if (res.code == 200) {
                            if(res.data.member.seals.length == res.data.member.card.count && res.data.member.card.type != 3){
                                that.manShow = true
                            }
                            let array = []
                            that.info = res.data.card
                            that.orders = res.data.orders.reverse()
                            that.card_no = res.data.member.card.card_no
                            that.discount_images = res.data.card.discount_images
                            that.member = res.data.member
                            res.data.member.seals.forEach((val, index) => {
                                val.sealed = true
                               let t1 = val.seal_icon
                               if (t1.includes('sv_01')) {
                                   that.iconNum = 1
                               } else if (t1.includes('sv_02')) {
                                   that.iconNum = 2
                               } else if (t1.includes('sv_03')) {
                                   that.iconNum = 3
                               } else if (t1.includes('sv_04')) {
                                   that.iconNum = 4
                               } else if (t1.includes('sv_05')) {
                                   that.iconNum = 5
                               } else if (t1.includes('sv_06')) {
                                   that.iconNum = 6
                               } else if (t1.includes('sv_07')) {
                                   that.iconNum = 7
                               } else if (t1.includes('sv_08')) {
                                   that.iconNum = 8
                               }
                               that.svgData1 = that.changeColor(that.svgData1, val.seal_color);
                               that.svgData2 = that.changeColor(that.svgData2, val.seal_color);
                               that.svgData3 = that.changeColor(that.svgData3, val.seal_color);
                               that.svgData4 = that.changeColor(that.svgData4, val.seal_color);
                               that.svgData5 = that.changeColor(that.svgData5, val.seal_color);
                               that.svgData6 = that.changeColor(that.svgData6, val.seal_color);
                               that.svgData7 = that.changeColor(that.svgData7, val.seal_color);
                               that.svgData8 = that.changeColor(that.svgData8, val.seal_color);
                                array.push(val)
                            })
                            for (let i = 0; i < res.data.card.count - res.data.card.seals.length; i++) {
                                let obj = {}
                                obj.time = ""
                                obj.admin_name = ""
                                obj.sealed = false
                                array.push(obj)
                            }
                            that.info.seals = array
                            if(that.color != '#000'){
                                that.svgData30 = that.changeColor(that.svgData30, that.color);
                            }
                        }
                    }).catch(() => {})
                }
            }
        },
        created() {
            let that = this

            getCardInfo(this.id).then((res) => {
                if (res.code == 200) {
                    if(res.data.member.seals.length == res.data.member.card.count && res.data.member.card.type != 3){
                        that.manShow = true
                    }
                    let array = []
                    that.info = res.data.card
                    that.member = res.data.member
                    that.orders = res.data.orders.reverse()
                    that.card_no = res.data.member.card.card_no
                    that.discount_images = res.data.card.discount_images
                    
                    res.data.card.seals.forEach((val, index) => {
                        val.sealed = true
                        val.sealed = true
                        let t1 = val.seal_icon
                        if (t1.includes('sv_01')) {
                            that.iconNum = 1
                        } else if (t1.includes('sv_02')) {
                            that.iconNum = 2
                        } else if (t1.includes('sv_03')) {
                            that.iconNum = 3
                        } else if (t1.includes('sv_04')) {
                            that.iconNum = 4
                        } else if (t1.includes('sv_05')) {
                            that.iconNum = 5
                        } else if (t1.includes('sv_06')) {
                            that.iconNum = 6
                        } else if (t1.includes('sv_07')) {
                            that.iconNum = 7
                        } else if (t1.includes('sv_08')) {
                            that.iconNum = 8
                        }
                        that.svgData1 = that.changeColor(that.svgData1, val.seal_color);
                        that.svgData2 = that.changeColor(that.svgData2, val.seal_color);
                        that.svgData3 = that.changeColor(that.svgData3, val.seal_color);
                        that.svgData4 = that.changeColor(that.svgData4, val.seal_color);
                        that.svgData5 = that.changeColor(that.svgData5, val.seal_color);
                        that.svgData6 = that.changeColor(that.svgData6, val.seal_color);
                        that.svgData7 = that.changeColor(that.svgData7, val.seal_color);
                        that.svgData8 = that.changeColor(that.svgData8, val.seal_color);
                         array.push(val)
                    })
                    for (let i = 0; i < res.data.card.count - res.data.card.seals.length; i++) {
                        let obj = {}
                        obj.time = ""
                        obj.admin_name = ""
                        obj.sealed = false
                        array.push(obj)
                    }
                    that.info.seals = array
                    if(that.color != '#000'){
                        that.svgData30 = that.changeColor(that.svgData30, that.color);
                    }
                }
            }).catch(() => {})
             // this.svgData30 = that.changeColor(that.svgData30, that.color);
        },
        // watch:{
        //     sp: {
        //         handler(newVal, oldVal) {
        //             console.log("newVal",newVal)
        //           this.caiDanShow = false
        //         },
        //         immediate: true,
        //         deep: true
        //       }
        // },
        methods: {
            scrollR() {

                this.caiDanShow = false
            },
            scrollL() {

            },
            close() {
                this.manShow = false
            },
            changeColor(url, color) {
                let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#",
                "%23")); //转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
                return res;
            },
            rightClick() {
                this.caiDanShow = true
            },
            leftClick() {
                uni.navigateBack()
            },
            seed(e) {
                this.caiDanShow = false
            },
            formatTime(time) {
                if (time) {
                    let date = new Date(time * 1000);
                    let Y = date.getFullYear() + '-';
                    let M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '-';
                    let D = date.getDate() + ' ';
                    return Y + M + D
                }

            },
            chooseItem(e) {
                this.caiDanShow = false
                this.$emit("changes", e)
            },

            formatT(e) {
                if (e) {
                    let date = new Date(e * 1000);
                    let h = date.getHours() + ':';
                    let m = date.getMinutes();
                    return h + m
                }

            }
        }
    }
</script>

<style lang="scss" scoped>
    .mask{
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, .4);
        position: fixed;
        top:0;
        left: 0;
        
        .maskContent{
            background: #fff;
            width: 710upx;
            box-sizing: border-box;
            padding: 20upx;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
            box-shadow: 0px 0px 12upx 2upx rgba(0,0,0,0.16);
            
            .man{
                width: 100%;
                height: 566upx;
                
                image{
                    width: 100%;
                    height: 100%
                }
            }
            
            .know{
                width: 170upx;
                height: 68upx;
                text-align: center;
                line-height: 68upx;
                background: rgba(217,48,37,0.65);
                font-size: 28upx;
                font-family: Hiragino Sans-W6, Hiragino Sans;
                font-weight: normal;
                color: #FFFFFF;
                border-radius: 15upx;
                margin: 60upx auto 40upx;
            }
        }
    }
    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    .icon {
        background-size: 100% 100%;
        display: inline-block;
        width: 80upx;
        height: 80upx;
        margin: 10upx 0;
    }

    .u-nav-right {
        image {
            width: 48rpx;
            height: 48rpx;
        }
    }

    .aTime {
        flex: 1 !important;
    }

    .top-box {
        border-bottom: 1px solid #C4C4C8;
        padding-bottom: 80rpx;

        .con {
            width: 90%;
            margin: 0 auto;

            .left {
                text-align: left;
                margin-top: 40rpx;
                color: #1D1D1F;
                font-size: 32rpx;
            }

            .title {
                margin-top: 10rpx;
                font-size: 48rpx;
                font-weight: 700;
                text-align: center;
            }

            .img {
                margin-top: 48rpx;
                text-align: center;

                image {
                    width: 300rpx;
                    height: 300rpx;
                }

                .tiaoxing {
                    width: 560rpx;
                    height: 152rpx;
                }
            }

            .order-sn {
                margin-top: 32rpx;
                text-align: center;
                font-size: 32rpx;
                color: #1D1D1F;
            }
        }
    }

    .cardInfo {
        box-sizing: border-box;
        padding: 40rpx 18rpx 0;
        box-shadow: 0px 0px 12rpx 2rpx rgba(0, 0, 0, 0.16);
        background: #fff;
        z-index: 99;

        .cardTitle {
            font-size: 32rpx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            width: 100%;
        }

        .cardInfos {
            margin-top: 40rpx;
            box-sizing: border-box;
            padding: 0 32rpx 40rpx;
            background: #fff;

            .ciItem {
                display: flex;
                align-items: center;
                margin-bottom: 20rpx;

                .ciLeft {
                    width: 144rpx;
                    height: 60rpx;
                    background: #F5F5F7;
                    font-size: 28rpx;
                    border-radius: 10rpx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                    text-align: center;
                    line-height: 60rpx;
                    margin-right: 28rpx;
                }

                .ciRight {
                    font-size: 28rpx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }
            }
        }
    }

    .appoint {
        box-sizing: border-box;
        padding: 32rpx 46rpx;
        box-shadow: 0px 0px 10rpx 2rpx rgba(0, 0, 0, 0.16);
        margin-top: 40rpx;
        margin-bottom: 40rpx;
        width: 100% !important;

        .appointTitle {
            width: 144rpx;
            height: 60rpx;
            background: #1A73E8;
            border-radius: 20rpx;
            font-size: 28rpx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            text-align: center;
            line-height: 60rpx;
        }

        .appointItem {
            display: flex;
            justify-content: space-between;
            margin-top: 32rpx;

            .aItem {
                display: flex;
                // align-items: center;
                .aItemImage {
                    width: 40rpx;
                    height: 40rpx;
                    margin-right: 20rpx;
                    flex-shrink: 0;

                    image {
                        width: 100%;
                        height: 100%;
                    }
                }
            }
        }
    }

    .special {
        background: #FFFFFF;
        box-shadow: 0px 0px 10rpx 2rpx rgba(29, 29, 31, 0.16);
        box-sizing: border-box;
        padding: 40rpx 0;

        .remark {
            font-size: 32rpx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            box-sizing: border-box;
            padding: 58rpx 40rpx 40rpx;
        }

        .specialTitle {
            width: 144rpx;
            height: 60rpx;
            background: #D93025;
            border-radius: 20rpx;
            font-size: 28rpx;
            font-family: Hiragino Sans-W4, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            line-height: 60rpx;
            text-align: center;
            position: relative;
            left: 46rpx;
            margin-bottom: 20rpx;
        }
    }

    .cardY {
        width: 100%;
        // height: 398rpx;
        background: #53B1AF;
        box-shadow: 0px 0px 10rpx 2rpx rgba(0, 0, 0, 0.16);
        border-radius: 12rpx;
        margin-top: 20rpx;
        box-sizing: border-box;
        padding: 36rpx;

        .cardInner {
            width: 100%;
            height: 100%;
            background-color: #fff;
            display: flex;
            flex-wrap: wrap;

            .seal {
                width: 20%;
                height: 50%;
                box-sizing: border-box;
                border-right: 2rpx solid #eaeaea;
                border-bottom: 2rpx solid #eaeaea;
                text-align: center;
                position: relative;

                .sealed {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    left: 0;
                    top: 0rpx;

                    image {
                        width: 80rpx;
                        height: 80rpx;
                    }

                    .sealTime {
                        font-size: 20rpx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #707070;
                        text-align: center;
                    }
                }

                text {
                    font-size: 48rpx;
                    font-family: Hiragino Sans-W6, Hiragino Sans;
                    font-weight: normal;
                    color: #EAEAEA;
                    line-height: 170rpx;
                }
            }
        }
    }

    .app-border {
        width: 100%;
        // height: 398rpx;
        background: #1A73E8;
        box-shadow: 0px 0px 10rpx 2rpx rgba(0, 0, 0, 0.16);
        border-radius: 12rpx;
        margin-top: 20rpx;
        box-sizing: border-box;
        padding: 36rpx;

        .apps {
            width: 100%;
            height: 100%;
            background-color: #fff;
            padding-bottom: 21rpx;

            .apps-top {
                display: flex;
                // justify-content: space-between;
                align-items: center;
                padding: 0 8rpx;
                padding-top: 29rpx;
                box-sizing: border-box;
                text-align: center;
                height: 63rpx;
                padding-bottom: 11rpx;
                border-bottom: 1px solid #D2D2D7;

                .no {
                    width: 42rpx;
                }

                .day-pay {
                    margin-left: 20rpx;
                    width: 179rpx;
                }

                .img-width {
                    width: 84rpx;
                    margin-left: 17rpx;
                }

                .img2-width {
                    margin-left: 37rpx;
                    width: 122rpx !important;
                }

                .apps-top-text {
                    font-size: 25rpx;
                    text-align: center;
                    color: #707070;

                }

                .apps-top-img {
                    width: 84rpx;

                    image {
                        width: 34rpx;
                        height: 34rpx;
                    }
                }
            }

            .apps-items {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0 8rpx;
                padding-top: 29rpx;
                box-sizing: border-box;
                height: 63rpx;
                padding-bottom: 11rpx;
                border-bottom: 1px dashed #D2D2D7;

                .apps-ind {
                    font-size: 25rpx;
                    color: #86868B;
                }

                .apps-time {
                    text-align: center;
                    font-size: 25rpx;
                    color: #1D1D1F;
                }

                .data {
                    width: 179rpx;
                }

                .time {
                    width: 84rpx;
                }

                .uname {
                    width: 122rpx;
                }

                .apps-img {
                    image {
                        width: 42rpx;
                        height: 42rpx;
                    }
                }
            }
        }
    }

    .u-page__item__slot-icon {
        width: 68rpx;
        height: 49rpx;
    }

    .u-page__item__slot-icon1 {
        width: 52rpx;
        height: 52rpx;
    }

    .u-page__item__slot-icon2 {
        width: 33rpx;
        height: 46rpx;
    }

    .u-page__item__slot-icon3 {
        width: 49rpx;
        height: 54rpx;
    }

    .u-page__item__slot-icon4 {
        width: 60rpx;
        height: 51rpx;
    }

    .pri-box {
        // width: 90%;
        margin: 0 auto;
        margin-top: 82rpx;
        margin-bottom: 44rpx;
        display: flex;
        align-items: center;

        .pri {
            font-size: 32rpx;
            color: #1D1D1F;
            margin-right: 40rpx;
        }

        .pri-sum {
            font-size: 48rpx;
            color: #D93025;
        }
    }

    .manshows {
        width: 100%;

        ::v-deep .u-popup__content {
            width: 710upx;

            .pup-con {
                width: 670upx;
                margin: 20rpx auto;
                border-radius: 40rpx;
                background-color: #E9EEFE;
                text-align: center;
                height: 566rpx;

                .pup-img {
                    margin-top: 40rpx;
                    margin-bottom: 40rpx;

                    image {
                        width: 167rpx;
                        height: 167rpx;
                    }
                }

                .pup-tit {
                    font-size: 48rpx;
                    color: #105B80;
                    font-weight: 700;
                }

                .pup-top {
                    margin-top: 60rpx;
                }

                .pup-des {
                    font-size: 32rpx;
                    color: #1D1D1F;
                }
            }

            .pup-close {
                width: 170rpx;
                height: 68rpx;
                line-height: 68rpx;
                background-color: #E67770;
                text-align: center;
                margin: 40rpx auto 60upx;
                font-size: 28rpx;
                color: #fff;
                border-radius: 20rpx;
            }
        }
    }
</style>