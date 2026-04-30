<template>
    <view>
        <u-navbar leftText="返回" :placeholder='true' title="クーポン一覧" :border="true" :safeAreaInsetTop="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" @click="toBack" mode=""></image>
            </view>
            <view class="u-nav-slot" slot="right">
                <image src="../../static/svg/addblue.svg" style="width: 60upx;height: 60upx;" @click="toEdit" mode="">
                </image>
            </view>
        </u-navbar>
        <view class="title">
            <view class="titleLeft">
                配信履歴
            </view>
            <view class="titleRight">
                <view>
                    配信数/月
                </view>
                <view>
                    {{coupon_count}}/{{sum_count}}
                </view>
            </view>
        </view>
        <view class="content">
            <view class="item" v-for="(item,index) in array">
                <view class="itemLeft" :class="current != index? 'border' : ''" @click="showModual(index,item)">
                    <image src="../../static/icons/select.png" v-if="current == index" mode=""></image>
                </view>
                <view class="itemRight">
                    <view class="iImage" v-if="item.images.length>0">
                        <u-swiper :list="item.images" height="394rpx" :autoplay="false" ></u-swiper>
                    </view>
                    <view class="iInfo">
                        <view class="info1">
                            <view class="i1Left">
                                <!-- <view v-text="`${item.use_count}/${item.count}`"></view> -->
                            </view>
                            <view class="i1Right">
                                <view class="op1" v-if="item.is_send == 0 && item.is_order == 1">予約中</view>
                                <view class="op2" v-else>配信済</view>
                                <view class="op3" v-if="item.is_send == 1 && item.indate == 1">有効</view>
                                <view class="op4" v-if="item.is_send == 1 && item.indate == 0">期限切</view>
                                
                            </view>
                        </view>
                        <view class="info2" v-text="item.title"></view>
                        <view class="info3">
                            NO: <text>{{item.number}}</text>
                        </view>
                        <view class="info4" v-if="item.is_indate == 0">無期限のクーポンです</view>
                        <view class="info4" v-if="item.is_indate == 1" v-text="`${item.indate_start_time} ~ ${item.indate_end_time}まで有効`"></view>
                        <view class="info5">
                            <view class="infoItem" v-text="`配信数:${item.send_count}`"></view>
                            <view class="infoItem">
                                バーコード:{{item.is_bar_code == 1? 'あり' : 'なし'}}
                            </view>
                        </view>
                        <view class="info5">
                            <view class="infoItem" v-text="`利用回数:${item.use_count}`"></view>
                            <view class="infoItem" v-text="`回数制限:${item.count}`"></view>
                        </view>
                    </view>
                </view>
            </view>
        </view>
        <u-action-sheet :round="20" :actions="list" :show="show" @select="choose" @close="close"></u-action-sheet>
        <permission :show="showpermission" @close='showpermission = false'></permission>
    </view>
</template>

<script>
    import {
        getCouponList,
        deleteCoupon,
        getAdminDetail
    } from '@/api/index.js'
    import permission from "@/components/permission/permission.vue"
    export default {
        components: {
            permission
        },
        data() {
            return {
                showpermission:false,
                current: -1,
                show: false,
                coupon_count:0,
                sum_count:0,
                list: [{
                        name: '編集する',
                        color: '#1D1D1F',
                        fontSize: '20'
                    },
                    {
                        name: 'このクーポンを削除',
                        color: '#D93025',
                        fontSize: '20'
                    },
                    {
                        name: 'キャンセル',
                        color: '#1A73E8',
                        fontSize: '20'
                    }
                ],
                array: []
            }
        },
        created() {
            
        },
        onShow() {
            this.getList()
        },
        methods: {
            getList() {
                let that = this
                getCouponList().then((res) => {
                    if (res.code == 200) {
                        that.array = res.data.coupons
                        that.coupon_count = res.data.coupon_count
                        that.sum_count = res.data.sum_count
                    }
                })
            },
            
            toBack(){
              uni.switchTab({
                  url:"../index/index"
              })  
            },

            close() {
                this.show = false
                this.current = -1
            },
            showModual(e,t) {
                let that = this
                if(t.is_send == 0 && t.is_order == 1){
                    that.list=[{
                        name: '編集する',
                        color: '#1D1D1F',
                        fontSize: '20'
                    },
                    {
                        name: 'このクーポンを削除',
                        color: '#D93025',
                        fontSize: '20'
                    },
                    {
                        name: 'キャンセル',
                        color: '#1A73E8',
                        fontSize: '20'
                    }
                ]
                }else{
                    that.list=[
                        {
                            name: 'このクーポンを削除',
                            color: '#D93025',
                            fontSize: '20'
                        },
                        {
                            name: 'キャンセル',
                            color: '#1A73E8',
                            fontSize: '20'
                        }
                    ]
                        
                }
                this.current = e
                this.show = true
            },
            choose(e) {
                let that = this
                let index = that.current
                if (e.name == "このクーポンを削除") {
                    deleteCoupon(that.array[index].id).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            that.getList()
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                } else if (e.name == '編集する') {
                    let index = that.current
                    if(that.array[index].is_send == 0){
                       uni.navigateTo({
                           url:"../coupon/edit/edit?id="+that.array[index].id
                       }) 
                    }
                }else{
                    that.current = -1
                }
            },
            toSetting() {
                uni.navigateTo({
                    url: "../notice/setting/setting"
                })
            },
            toEdit() {
                let that = this
                let admin = uni.getStorageSync("admin")
                if(admin.admin_type == 2){
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    getAdminDetail(admin.id).then((res) => {
                        uni.hideLoading()
                        if(res.data.admin.privileges.includes(7)){
                            uni.navigateTo({
                                url: "../coupon/add/add"
                            })
                        }else{
                            that.showpermission = true
                        }
                    })
                }else{
                    uni.navigateTo({
                        url: "../coupon/add/add"
                    })
                }   
            }
        }
    }
</script>

<style lang="scss">
    .border {
        border-radius: 8upx;
        opacity: 1;
        border: 2upx solid #707070;
    }

    .item {
        display: flex;
        margin-bottom: 40upx;

        .itemLeft {
            width: 40upx;
            height: 40upx;
            flex-shrink: 0;

            image {
                width: 100%;
                height: 100%;
            }
        }

        .itemRight {
            margin-left: 50upx;
            width: 588upx;
            box-shadow: 0px 0px 20upx 2upx rgba(0, 0, 0, 0.16);
            border-radius: 20upx;
            overflow: hidden;

            .iImage {
                width: 100%;
                height: 394upx;

                image {
                    width: 100%;
                    height: 100%;
                }
            }
        }
    }

    .content {
        box-sizing: border-box;
        padding: 40upx;

        .iInfo {
            box-sizing: border-box;
            padding: 20upx;

            .info2 {
                font-size: 28upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                margin-top: 20upx;
            }

            .info3 {
                font-size: 28upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                margin-top: 20upx;

                text {
                    color: #1A73E8;
                }
            }

            .info4 {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                margin-top: 20upx;
            }

            .info1 {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 26upx;
                box-sizing: border-box;
                padding-top: 15upx;

                .i1Left {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1A73E8;
                }

                .i1Right {

                    display: flex;


                    .op1 {
                        width: 96upx;
                        height: 48upx;
                        background: #FFFFFF;
                        border: 2upx solid #1A73E8;
                        font-size: 24upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #1A73E8;
                        text-align: center;
                        line-height: 48upx;
                        border-radius: 12upx;
                        margin-left: 20upx;
                    }


                    .op2 {
                        width: 96upx;
                        height: 48upx;
                        background: #FFFFFF;
                        border: 2upx solid #707070;
                        font-size: 24upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #707070;
                        text-align: center;
                        line-height: 48upx;
                        border-radius: 12upx;
                        margin-left: 20upx;
                    }

                    .op3 {
                        width: 96upx;
                        height: 48upx;
                        background: #1A73E8;
                        border: 2upx solid #1A73E8;
                        font-size: 24upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #ffffff;
                        text-align: center;
                        line-height: 48upx;
                        border-radius: 12upx;
                        margin-left: 20upx;
                    }

                    .op4 {
                        width: 96upx;
                        height: 48upx;
                        background: #707070;
                        border: 2upx solid #707070;
                        font-size: 24upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #ffffff;
                        text-align: center;
                        line-height: 48upx;
                        border-radius: 12upx;
                        margin-left: 20upx;
                    }
                }
            }

            .info5 {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                margin-top: 20upx;

                .infoItem {
                    flex: 1;
                }

            }
        }
    }

    .title {
        height: 126upx;
        background: #fff;
        box-shadow: 0px 0px 10upx 2upx rgba(0, 0, 0, 0.16);
        box-sizing: border-box;
        padding: 20upx 30upx;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;

        .titleRight {

            view:first-child {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;
            }

            view:last-child {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                text-align: right;
                margin-top: 10upx;
            }
        }

        .titleLeft {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: bold;
            color: #1D1D1F;
        }
    }
</style>
