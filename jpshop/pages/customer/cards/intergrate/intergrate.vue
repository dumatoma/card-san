<template>
    <view>
        <!-- 自定义顶部导航 -->
        <u-navbar :title="info.name" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true">
            <view class="u-nav-slot" slot="right">
                <text @click="toCsv" class="tc">詳細</text>
            </view>
        </u-navbar>
        <view class="containe">
            <view class="userInfo">
                <view class="infoItem">
                    <view class="itemname">
                        会員ID
                    </view>
                    <view class="itemValue" v-text="info.member_id"></view>
                </view>
                <view class="infoItem">
                    <view class="itemname">
                        顧客NO
                    </view>
                    <view class="itemValue" v-text="info.member_no"></view>
                </view>
            </view>

            <view class="cardInfo">
                <view class="cardTitle">
                    {{info.shop_name}}({{info.card_name}})
                </view>
                <view class="ITF">
                    <view class="itf1">
                        <image :src="info.qrcode" mode=""></image>
                    </view>
                    <view class="itf2" v-text="info.member_id"></view>
                    <view class="itf3">
                        <view class="by">
                            保有ポイント数
                        </view>
                        <view class="inter" @click="showModal">
                            <text class="td" v-text="`${info.point} `"></text><text class="td">　pt</text>
                        </view>
                    </view>
                </view>
                <view class="cardInfos">
                    <view class="ciItem">
                        <view class="ciLeft">
                            有効期限
                        </view>
                        <view class="ciRight" v-text="card.expire_time"></view>
                    </view>
                    <view class="ciItem">
                        <view class="ciLeft">
                            備　考
                        </view>
                        <view class="ciRight" v-text="card.remark"></view>
                    </view>
                </view>
            </view>
            <view class="appoint" v-if="orders.length > 0">
                <view class="appointTitle">
                    予 約
                </view>
                <view class="appointItem" v-for="(item,index) in orders">
                    <view class="aItem">
                        <view class="aItemImage">
                            <image src="../../../../static/icons/record1.png" mode=""></image>
                        </view>
                        <view class="aTime">
                            <view class="time">
                                {{item.date}}({{item.week}})　{{item.start}}〜{{item.end}}
                            </view>
                            <view class="copy" v-text="item.menu_name"></view>
                        </view>
                    </view>
                    <view class="aItem">
                        <view class="aItemImage">
                            <image src="../../../../static/icons/record3.png" mode=""></image>
                        </view>
                        <view class="aUser" v-text="item.admin_name"></view>
                    </view>
                </view>
            </view>
            <view class="special" v-if="banner.length > 0">
                <view class="specialTitle">
                    特 典
                </view>
                <u-swiper :list="banner" height="250" :indicator="true" indicatorMode="dot"
                    indicatorActiveColor="#000000" indicatorInactiveColor="rgba(0,0,0,.3)"></u-swiper>
                <view class="remark">
                    当店で取り扱いのワックスorトリートメントのどちらかをプレゼントいたします。
                </view>
            </view>
        </view>
        <view class="popContent">
            <view class="popTitle"  v-if="info.is_point == 1">
                <text class="orange">{{card.amount}}円</text> で <text class="orange">{{card.point}}ポイント</text>を付与
            </view>
            <view class="choosecount"  v-if="info.is_point == 1">
                <view>
                    金額
                </view>
                <view class="inp">
                    <u-input border="none" v-model="points" inputAlign='center' type="number"></u-input>
                </view>
                <view>
                    円
                </view>
            </view>
            <u-button @click="addPoints" class="btn" text="決　定"></u-button>
            <view style="height: 80upx;"></view>
        </view>
        <view style="height: 400upx;"></view>
        <u-popup :show="show" round="32">
            <view class="useContent">
                <view class="useTitle">
                    <image src="../../../../static/icons/tclose.png" mode="" class="cloeseBtn" @click="show = false"></image>
                    <text style="margin: 0 auto;position: relative;left: -30upx;font-size: 40upx;font-weight: bold;">ポイントの使用</text>
                </view>
                <view class="useDetail">
                    <view class="use">
                        <view class="use1">
                            保有ポイント数 <text style="color: rgba(217,48,37,0.65);font-size: 48upx;margin: 0 20upx;font-weight: bold;" v-text="formatPrice(info.point)"></text><text style="color: rgba(217,48,37,0.65);font-size: 48upx;font-weight: bold;">pt</text>
                        </view>
                        <view class="use2">
                            今回使用するポイント数
                        </view>
                        <view class="use3">
                            <view class="ipt">
                                <input type="number" v-model="value1">
                            </view>
                            <view>
                                ポイント
                            </view>
                        </view>
                    </view>
                </view>
                <view class="bt" @click="usePoint">
                    ポイントを使う
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import {
        getMemberDetaill,
        addPoints
    } from "@/api/index.js"
    export default {
        data() {
            return {
                title: "name",
                number: 3,
                banner: [],
                show: false,
                info:{
                    point:0
                },
                card:{},
                orders:{},
                points:0,
                value1:""
            }
        },
        created() {
            
        },
        onLoad(options) {
            let that = this
            let query = options
            that.query = query
            that.getDetail(query.id)
        },
        methods: {
            usePoint(){
                let that = this
                let data = {}
                data.mid = that.info.id
                data.is_qrcode = that.query.source?1:0
                data.plus = that.value1 * -1
                addPoints(data).then((res) => {
                    if(res.code ==  200){
                        uni.showToast({
                            title:res.message
                        })
                       that.getDetail(data.mid)
                       that.value1 = 0
                       that.show = false
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                        
                    }
                })
            },
            showModal() {
                let that = this
                that.show = true
            },
            toCsv(){
              uni.redirectTo({
                  url:"../../detail/detail?id="+this.info.id
              })  
            },
            close() {
                this.show = false
            },

            getDetail(id) {
                let that = this
                let data = {}
                data.id = id
                getMemberDetaill(data).then((res) => {
                    if(res.code == 200){
                        that.info = res.data
                        that.card = res.data.card
                        that.orders = res.data.orders
                        that.banner = res.data.card.discount_images
                        // if(that.query.code == '111'){
                        //     that.show = true
                        // }
                    }else{
                        setTimeout(() => {
                            uni.navigateBack()
                        },1500)  
                    }
                })
            },
            formatPrice(e){
                return e.toLocaleString()
            },
            addPoints(){
                let that = this
                let data = {}
                data.mid = that.info.id
                data.is_qrcode = that.query.source == 1?1:0
                data.plus = that.points
                addPoints(data).then((res) => {
                    if(res.code ==  200){
                        uni.showToast({
                            title:res.message
                        })
                       that.getDetail(data.mid)
                       that.points = 0
                       that.show = false
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:'none'
                        })
                    }
                })
            }
        }
    }
</script>

<style lang="scss">
    .bt{
        width: 488upx;
        height: 100upx;
        background: rgba(217,48,37,0.65);
        text-align: center;
        line-height: 100upx;
        border-radius: 20upx 20upx 20upx 20upx;
        margin: 0 auto;
        color: #fff;
    }
    .use3{
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 38upx;
        box-sizing: border-box;
        padding-left: 150upx;
    }
    .ipt{
        width: 362upx;
        height: 80upx;
        background: #FFFFFF;
        border-radius: 20upx;
        border: 2upx solid #AAAAAA;
        margin-right: 28upx;
        input{
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            padding: 0 20upx;
            text-align: center;
        }
    }
    .use2{
        text-align: center;
        margin-top: 62upx;
    }
    .use1{
        width: 100%;
        display: flex;
        justify-content: center;
        font-size: 32upx;
        font-family: Hiragino Sans, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        align-items: center;
    }
    .use{
        width: 100%;
        padding: 62upx 0;
        box-sizing: border-box;
       
    }
    .useDetail{
        box-sizing: border-box;
        padding: 0 32upx;
    }
    .useContent{
        width: 100vw;
        height: 60vh;
        background: #fff;
        border-top-left-radius: 32upx;
        border-top-right-radius: 32upx;
        overflow: hidden;
        background: #f5f5f7;
    }
    .useTitle{
        display: flex;
        align-items: center;
        text-align: center;
        background: #FBFBFD;
        border-bottom: 2upx solid #eaeaea;
        box-sizing: border-box;
        padding: 26upx 32upx;
    }
    .cloeseBtn{
        width: 60upx;
        height: 60upx;
    }
    .popContent{
        background: rgba(245, 245, 247, 0.95);
        box-shadow: 0px 0px 12px 2px rgba(0,0,0,0.16);
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100vw;
    }
    .tc {
        color: #1A73E8;
    }

    .orange {
        color: #D93025;
    }

    .inp {
        width: 362upx;
        height: 80upx;
        background: #FFFFFF;
        border-radius: 20upx;
        opacity: 1;
        border: 2upx solid #AAAAAA;
        display: flex;
        align-items: center;
        text-align: center;
        margin: 0 20upx;
        
        input{
            font-family: Hiragino Sans-W5 !important;
            color: #1d1d1f;
            font-size: 40upx;
        }
    }

    .itf3 {
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        .by {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-right: 66upx;
        }

        .inter {
            font-size: 48upx;
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: normal;
            color: rgba(217, 48, 37, 0.65);
            font-weight: bolder;
            
            .td {
                text-decoration: underline;
            }
        }
    }

    .btn {
        width: 328upx !important;
        height: 100upx !important;
        background: #1A73E8 !important;
        border-radius: 20upx !important;
        text-align: center !important;
        font-size: 40upx !important;
        color: #fff !important;
        margin-top: 60upx !important;
    }

    .coiunt {
        font-size: 80upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: rgba(29, 29, 31, 0.9);
        margin: 0 120upx;
    }

    .choosecount {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .reduce,
    .plus {
        width: 80upx;
        height: 80upx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .blank {
        width: 100%;
        height: 40upx;
        background: #FBFBFD;
        z-index: -1;
    }

    .popTitle {
        padding: 40upx 0;
        text-align: center;
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
    }

    .userInfo {
        border-top: 2upx solid #d2d2d7;
        border-bottom: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding: 32upx 40upx 12upx;
    }

    .special {
        background: #FFFFFF;
        box-shadow: 0px 0px 10upx 2upx rgba(29, 29, 31, 0.16);
        box-sizing: border-box;
        padding: 40upx 0;

        .remark {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            box-sizing: border-box;
            padding: 58upx 40upx 40upx;
        }

        .specialTitle {
            width: 144upx;
            height: 60upx;
            background: #D93025;
            border-radius: 20upx;
            font-size: 28upx;
            font-family: Hiragino Sans-W4, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            line-height: 60upx;
            text-align: center;
            position: relative;
            left: 46upx;
            margin-bottom: 20upx;
        }
    }

    .appoint {
        box-sizing: border-box;
        padding: 32upx 46upx;
        box-shadow: 0px 0px 10upx 2upx rgba(0, 0, 0, 0.16);
        margin-top: 40upx;
        margin-bottom: 40upx;

        .appointTitle {
            width: 144upx;
            height: 60upx;
            background: #1A73E8;
            border-radius: 20upx;
            font-size: 28upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            text-align: center;
            line-height: 60upx;
        }

        .appointItem {
            display: flex;
            justify-content: space-between;
            margin-top: 32upx;

            .aItem {
                display: flex;

                .aItemImage {
                    width: 40upx;
                    height: 40upx;
                    margin-right: 20upx;

                    image {
                        width: 100%;
                        height: 100%;
                    }
                }
            }
        }
    }

    .itf1 {
        width: 460upx;
        height: 102upx;
        margin: 40upx auto 20upx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .itf2 {
        text-align: center;
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        margin-bottom: 70upx;
    }

    .cardInfo {
        box-sizing: border-box;
        padding: 40upx 18upx 0;
        box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);
        background: #fff;
        z-index: 99;

        .cardTitle {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            width: 100%;
        }

        .cardInfos {
            margin-top: 40upx;
            box-sizing: border-box;
            padding: 0 32upx 40upx;
            background: #fff;

            .ciItem {
                display: flex;
                align-items: center;
                margin-bottom: 20upx;

                .ciLeft {
                    width: 144upx;
                    height: 60upx;
                    background: #F5F5F7;
                    font-size: 28upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                    text-align: center;
                    line-height: 60upx;
                    margin-right: 28upx;
                }

                .ciRight {
                    font-size: 28upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }
            }
        }
    }

    .infoItem {
        display: flex;
        align-items: center;
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        margin-bottom: 20upx;

        .itemname {
            color: #707070;
            margin-right: 70upx;
            width: 128upx;

        }

        .itemValue {
            color: #1D1D1F;
        }
    }
</style>
