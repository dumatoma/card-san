<template>
    <view>
        <view class="content">
            <view class="aler bkblue" v-if="isexpire == true && vips.length == 1 && vips[0].cancel_time > 0">
                <view class="ic">
                     <image src="../../../static/svg/gt.svg" mode=""></image>
                </view>
                <view class="alerContent">
                    現在のご契約は{{vips[0].end_time}}にキャンセルされます。
                </view>
            </view>
            <view class="aler" style="background-color: #d93025;" v-if="isexpire == false">
                <view class="ic">
                     <image src="../../../static/svg/gt.svg" mode=""></image>
                </view>
                <view class="alerContent">
                    現在ご契約はキャンセルされています。再開するには、ページ下部の“再開する”を選択してください。
                </view>
            </view>
            <view class="c1 mb40">
                <view class="citem bb">
                    <view class="cTop" v-if="isexpire == true">
                        ご利用中のプラン：<text class="bd"
                            style="font-size: 32upx;">{{vips[0].type == 1?"ライト" : vips[0].type == 2?"スタンダード" : vips[0].type == 3?"プレミアム":''}}</text>
                    </view>
                    <view class="cTop" v-if="isexpire == false">
                        ご利用中のプラン：<text class="bd"
                            style="font-size: 32upx;">{{vip.type == 1?"ライト" : vip.type == 2?"スタンダード" : vip.type == 3?"プレミアム":''}}</text>
                    </view>
                    <view class="cBottom bl" @click="changeT">
                        プランを変更
                    </view>
                </view>
                <view class="citem bb" v-if="isexpire == true && vips[0].is_try == 1">
                    <view class="cTop">
                        現在、無料お試し中です。
                    </view>
                    <view class="cBottom">
                        残り{{vips[0].end_day}}日
                    </view>
                </view>
                <view class="citem bb" v-if=" vips[0].is_try != 1">
                    <view class="cTop" v-if="isexpire == true">
                        お支払い周期：<text class="bd">{{vips[0].month}}ヶ月</text>
                    </view>
                    <view class="cTop" v-else>
                        お支払い周期：<text class="bd">{{vip.month}}ヶ月</text>
                    </view>
                    <view class="cBottom bl" @click="changeZ">
                        お支払い周期を変更
                    </view>
                </view>
                <view class="citem bb" v-if=" vips[0].is_try != 1">
                    <view class="cTop" v-if=" isexpire == true && vips[0].end_time">
                        契約更新日：{{vips[0].end_time}}
                    </view>
                    <view v-else>
                        契約更新日：-
                    </view>
                </view>
                <view class="citem bb" v-if="isexpire == true && vips[0].cancel_time > 0">
                    <view class="cTop colorRed">
                        現在、解約手続き中です。ご利用を継続するには各端末の「設定」から手続きをお願いします。
                    </view>
                </view>
                <view class="citem" v-if="isexpire == true && vips.length == 2">
                    <view class="cTop1 bd">
                        新しいサブスクリプションの内容
                    </view>
                    <view class="cTop1">
                        プラン：{{vips[1].type == 1?"ライト" : vips[1].type == 2?"スタンダード" : vips[1].type == 3?"プレミアム":''}}
                    </view>
                    <view class="cTop1">
                        お支払い周期：{{vips[1].month}}ヶ月
                    </view>
                    <view class="cTop1">
                        契約開始日：{{formatTime(vips[1].start_time)}}
                    </view>
                </view>
            </view>
            <view class="c1">
                <view class="citem" v-if="isexpire == true">
                    <view class="cTop">
                        お支払い方法
                    </view>
                    <view class="cBottom" style="text-align: left !important;"
                        v-text="vips[0].is_try == 1?'-':'App 課金'"></view>
                </view>
                <view class="citem" v-else>
                    <view class="cTop">
                        お支払い方法
                    </view>
                    <view class="cBottom" style="text-align: left !important;">-</view>
                </view>
            </view>
            <!-- <view class="tip">
                アプリ内課金以外のクレジットカード決済の選択、更にお得な1 年契約の設定は、Card-Sanのウェブサイトでのみ行うことができます。
            </view> -->
            <view class="btn" hover-class="cl" v-if="vips.length != 0 && vips[0].is_try == 1" @click="toc">
                お申し込みはこちら
            </view>
            <view class="btn" hover-class="cl" v-if="isexpire == false" @click="tone">
                再開する
            </view>
            <!-- <view class="btn" hover-class="cl" v-if="vips.length == 1 && vips[0].cancel_time > 0 && plt == 'ios'"
                @click="toc">
                継続する
            </view> -->
            <view class="jie" hover-class="cj" v-if="isexpire == true && vips[0].cancel_time == 0 && vips[0].is_try == 0" @click="toff">
                解約手続き
            </view>
        </view>
        <u-popup :show="show" @close="show = false">
            <subscribe @btmnclick="changeS" :title="title" :content="content"></subscribe>
        </u-popup>
        <u-popup :show="show2" @close="show2 = false">
            <subscribe @btmnclick="changeSs" :title="title" :content="content"></subscribe>
        </u-popup>
        <u-popup :show="show3" @close="show3 = false">
           <sios @btmnclick="show3 = false"></sios>
        <!--  <view style="width: 100vw;height: 60vh;background: #d93025;">
              
          </view> -->
        </u-popup>
    </view>
</template>

<script>
    import subscribe from "@/components/subscribe/subscribe.vue"
    import subscribee from "@/components/su1b/su1b.vue"
    import sios from "@/components/sios.vue"
    import {
        getShopInfo,
        getPackageList,
        delPlan
    } from "@/api/index.js"
    export default {
        components: {
            subscribe,
            subscribee,
            sios
        },
        data() {
            return {
                show: false,
                shows: false,
                show2: false,
                show3: false,
                number: 100,
                date: "2022/07/06 15:08",
                info: {},
                endTime: "",
                startTime: "",
                title: "",
                vips: [{}],
                content: '',
                plt: "",
                isexpire: true
            };
        },
        onLoad() {
            let that = this
        },
        onShow() {
            uni.showLoading({
                title: "読み込み中",
                mask:true
            })
            this.getvip()
            this.plt = uni.getStorageSync("platform")
        },
        methods: {
            tone(){
                let platform = uni.getStorageSync("platform")
                if(this.isexpire == true){
                    uni.navigateTo({
                        url: "/pages/payment/payment?type="+this.vips[0].type
                    })
                }else{
                    uni.navigateTo({
                        url: "/pages/payment/payment?type="+this.vip.type
                    })
                }
            },
            changeS(e) {
                let that = this
                that.show = false
                if (e == 0) {
                    let id = that.vips[0].id
                    uni.navigateTo({
                        url: "../breakoff/breakoff?id=" + id
                    })
                    // delPlan(id).then((res) => {
                    //     if(res.code == 200){
                    //         uni.showToast({
                    //             title:res.message,
                    //             duration:2500
                    //         })
                    //         setTimeout(() => {
                    //             uni.switchTab({
                    //                 url:"/pages/index/index"
                    //             })
                    //         },2500)
                    //     }
                    // })
                } else {
                    uni.navigateTo({
                        url: "/pages/payment/payment"
                    })
                }
            },
            changeSs(e) {
                let that = this
                that.show = false
                if (e == 0) {
                    that.show2 = false
                } else {
                    uni.navigateTo({
                        url: "/pages/payment/payment"
                    })
                }
            },
            toff() {
                let platform = uni.getStorageSync("platform")
                if(platform != 'ios'){
                    if (this.info.card_type == 1) {
                        this.title = "解約手続きについて"
                        this.content = "解約手続きは、お手数ではございますが、PCブラウザよりCard-Sanウェブサイトにログインしてください。"
                        this.show2 = true               
                    } else {
                        this.changeS(0)
                    }
                }else{
                    this.show3 = true
                }
            },
            toc() {
                if (this.info.card_type == 1) {
                    this.title = "ご利用のご継続について"
                    this.content = "ご利用のご継続手続きは、お手数ではございますが、PCブラウザよりCard-Sanウェブサイトにログインしてください。"
                    this.show = true
                } else {
                    uni.navigateTo({
                        url: "/pages/accounts/package/package"
                    })
                }

            },
            changeT() {
                uni.navigateTo({
                    url: "/pages/accounts/package/package"
                })
            },
            changeZ() {
                uni.navigateTo({
                    url: "/pages/payment/payment?type=" + this.vips[0].type
                })
            },
            formatTime(e) {
                let that = this
                let d = new Date(e * 1000)
                var Y = d.getFullYear();
                var M =
                    (d.getMonth() + 1 < 10 ?
                        "0" + (d.getMonth() + 1) :
                        d.getMonth() + 1);
                var D = (d.getDate() < 10 ? "0" + d.getDate() : d.getDate()) + " ";
                return Y + '年' + M + "月" + D + "日";
            },
            close() {
                this.show = false
            },
            getvip() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        that.info = res.data.shop_info.vip
                        let date = new Date(res.data.shop_info.vip.end_time * 1000)
                        const Y = date.getFullYear()
                        const M = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                        that.endTime = `${Y}/${M}`
                        let date1 = new Date(res.data.shop_info.vip.start_time * 1000)
                        const Y1 = date1.getFullYear()
                        const M1 = date1.getMonth() + 1 < 10 ? '0' + (date1.getMonth() + 1) : date1.getMonth() +
                            1
                        that.startTime = `${Y1}/${M1}`

                        if (res.data.shop_info.vips.length > 0) {
                            that.vips = res.data.shop_info.vips
                            that.isexpire = true
                        } else {
                            that.isexpire = false
                        }
                        that.vip = res.data.shop_info.last_vip
                    }
                    uni.hideLoading()
                })
            },
            tobuy() {
                uni.navigateTo({
                    url: "../package/package"
                })
            },
            tobuy1() {
                uni.navigateTo({
                    url: "../../payment/payment?type=" + this.info.type
                })
            }
        }
    }
</script>

<style lang="scss">
    .ic{
        width: 60upx;
        height: 60upx;
        flex-shrink: 0;
        image{
            width: 60upx;
            height: 60upx;
        }
    }
    
    .bkblue {
        background: rgba(26, 115, 232, 0.5) !important;
    }

    .colorRed {
        color: #D93025 !important;
        margin-bottom: 0 !important;
    }

    .cl {
        opacity: 0.6 !important;
    }

    .aler {
        box-sizing: border-box;
        padding: 40upx 32upx;
        display: flex;
        align-items: center;
        margin-bottom: 40upx;

        .alerContent {
            font-size: 32rpx;
            font-weight: bold;
            color: #1D1D1F;
            margin-left: 8upx;
        }
    }

    .cj {
        background: rgba(29, 29, 31, .1) !important;
    }

    .jie {
        height: 80rpx;
        background: #FFFFFF;
        border: 2rpx solid #aaa;
        text-align: center;
        line-height: 80upx;
        font-size: 32rpx;
        color: #aaa;
        margin-top: 80upx;
        border-radius: 20upx;
    }

    .btn {
        width: 100%;
        background: #105B80;
        border-radius: 56rpx;
        height: 112upx;
        text-align: center;
        line-height: 112upx;
        font-size: 40rpx;
        font-weight: bold;
        color: #FFFFFF;
        margin-top: 330upx;
        margin-bottom: 60upx;
    }

    .content {
        background: #f5f5f7;
        box-sizing: border-box;
        padding: 40upx 32upx;
        min-height: 100vh;
    }

    .tip {
        font-size: 28rpx;
        font-family: Noto Sans Kannada, Noto Sans Kannada;
        font-weight: 400;
        color: #1D1D1F;
        margin-top: 20upx;
    }

    .c1 {
        width: 100%;
        background: #FFFFFF;
        box-shadow: 0rpx 0rpx 10rpx 2rpx rgba(0, 0, 0, 0.16);
        border: 2rpx solid #D2D2D7;
        box-sizing: border-box;
        padding: 0 32upx;
    }

    .mb40 {
        margin-bottom: 40upx;
    }

    .citem {
        box-sizing: border-box;
        padding: 42upx 0;
        font-size: 28rpx;
    }

    .bl {
        color: #1A73E8;
    }

    .bb {
        border-bottom: 2upx solid #d2d2d7;
    }

    .cTop {
        color: #1d1d1f;
        margin-bottom: 42upx;
    }

    .cTop1 {
        color: #1d1d1f;
        margin-bottom: 20upx;
    }

    .cBottom {
        text-align: right;
    }

    .bd {
        font-weight: bold;
    }

    // .btips{
    //     background: rgba(26,115,232,0.1);
    //     border-radius: 20upx;
    //     opacity: 1;
    //     box-sizing: border-box;
    //     padding: 36upx;
    //     font-size: 24upx;
    //     font-family: Hiragino Sans-W3, Hiragino Sans;
    //     font-weight: normal;
    //     color: #1A73E8;
    //     margin-top: 60upx;
    //     margin-bottom: 120upx;
    // }

    // .list_item {
    //     display: flex;
    //     box-sizing: border-box;
    //     padding: 40upx 32upx;
    //     // border-bottom: 2upx solid #D2D2D7;
    //     align-items: center;
    //     font-size: 26upx;

    //     .item_list{
    //         border-bottom: 2upx solid #D2D2D7;
    //     }

    //     .selectTime {
    //         width: 15%;
    //     }

    //     .selectPrice {
    //         width: 45%;
    //     }

    //     .selectDisccount {
    //         flex: 1;
    //         color: #D93025 !important;
    //     }
    // }
    // .selectContent {
    //     width: 32upx;
    //     height: 32upx;
    //     box-sizing: border-box;
    //     padding: 6upx;
    //     border: 2upx solid #1A73E8;
    //     border-radius: 50%;
    //     margin-right: 10upx;

    //     .selectInner {
    //         width: 100%;
    //         height: 100%;
    //         background: #1A73E8;
    //         border-radius: 50%;
    //     }
    // }

    // .contain{
    //     background: #fff;
    //     box-sizing: border-box;
    //     padding: 100upx 66upx;

    //     .btitle{
    //         height: 80upx;
    //         background: rgba(26,115,232,0.1);
    //         text-align: center;
    //         line-height: 80upx;
    //         font-size: 32upx;
    //         font-family: Hiragino Sans-W5, Hiragino Sans;
    //         font-weight: normal;
    //         color: #1D1D1F;
    //     }
    // }

    // .detail {
    //     display: flex;
    //     align-items: center;
    //     font-size: 28upx;
    //     font-family: Hiragino Sans-W5, Hiragino Sans;
    //     font-weight: normal;
    //     color: #1D1D1F;
    //     line-height: 48upx;
    //     padding-bottom: 40upx;

    //     .detail_left {
    //         font-size: 28upx;
    //         font-family: Hiragino Sans-W3, Hiragino Sans;
    //         font-weight: normal;
    //         color: #1D1D1F;
    //     }
    // }

    // .title {
    //     height: 112upx;
    //     background: #F5F5F7;
    //     border-radius: 32upx;
    //     text-align: center;
    //     line-height: 112upx;
    //     font-size: 40upx;
    //     font-family: Hiragino Sans-W5, Hiragino Sans;
    //     font-weight: normal;
    //     color: #1D1D1F;
    //     position: sticky;
    //     top: 0;
    //     left: 0;
    //     border-bottom: #d2d2d7;

    //     .closeMask {
    //         width: 60upx; 
    //         height: 60upx;
    //         position: absolute;
    //         left: 32upx;
    //         top: 50%;
    //         transform: translateY(-50%);
    //     }
    // }

    // .container {
    //     border-top: 2upx solid #d2d2d7;
    //     box-sizing: border-box;
    //     padding: 0 32upx;

    //     .item {
    //         border-bottom: 2upx solid #d2d2d7;
    //         box-sizing: border-box;
    //         padding: 82upx 42upx;

    //         .changes {
    //             text-align: right;
    //             font-size: 28upx;
    //             font-family: Hiragino Sans-W5, Hiragino Sans;
    //             font-weight: normal;
    //             color: #1A73E8;
    //         }


    //     }

    // }
</style>