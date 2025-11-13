<template>
    <view class="container">
<u-navbar :placeholder='true' :title="title" :border="true" :safeAreaInsetTop="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" mode="" @click="goback"></image>
            </view>
            <view class="u-nav-slot" slot="right">
            </view>
        </u-navbar>
        <view class="listContent">
            <u-swipe-action autoClose>
                <u-swipe-action-item v-for="(item,index) in array" :options="options1" :key="index"
                    @click="handleClick(item.id)">
                    <view class="swipe-action u-border-top u-border-bottom">
                        <view class="swipe-action__content">
                            <view class="item">
                                <view class="itemLeft">
                                    <image src="../../static/images/avatar.png" mode=""></image>
                                </view>
                                <view class="itemRight" @click="toDe(item.mid)" style="display: flex;align-items: center;flex-wrap: wrap;color:#1A73E8">
                                    <view class="il" v-if="item.member_name" v-text="item.member_name"></view>
                                    <!-- <view class="ir" v-text="it" v-if="item.member_name" style="margin-left: 10upx;"></view> -->
                                    <view class="ir" v-if="!item.member_name" style="margin-left: 10upx;">--</view>
                                </view>
                            </view>
                            
                            
                            <view class="item">
                                <view class="itemLeft">
                                    <image src="../../static/svg/correct.svg" mode=""></image>
                                </view>
                                <view class="itemRight" style="display: flex;align-items: center;flex-wrap: wrap;">
                                    <view class="il" v-text="item.date"></view>
                                    <view class="ir" v-for="it,idx in item.times" v-text="it"
                                        v-if="item.times.length > 0" style="margin-left: 10upx;"></view>
                                    <view class="ir" v-if="item.times.length == 0" style="margin-left: 10upx;">--</view>
                                </view>
                            </view>

                            <view class="item">
                                <view class="itemLeft">
                                    <image src="../../static/svg/menu.svg" mode=""></image>
                                </view>
                                <view class="itemRight">
                                    <view v-if="item.menu_names.length > 0" v-for="it,idx in item.menu_names"
                                        v-text="it"></view>
                                    <view v-if="item.menu_names.length == 0">--</view>
                                </view>
                            </view>

                            <view class="item">
                                <view class="itemLeft">
                                    <image src="../../static/svg/user.svg" mode=""></image>
                                </view>
                                <view class="itemContent">
                                    <view class="itemRight">
                                        <view v-for="it,idx in item.day_sadmin_names"
                                            v-if="item.day_sadmin_names.length > 0" v-text="it"></view>
                                        <view v-if="item.day_sadmin_names.length == 0">--</view>
                                    </view>
                                </view>

                            </view>
                            <view class="item">
                                <view class="coin" v-if="item.point.length > 0">
                                    <view class="coni" v-text="toUp(item.point[0].card)"></view>
                                    <view v-for="it,idx in item.point" style="display: flex;margin: 0 15upx;">
                                        <view class="count" style="color: #1d1d1f;" v-text="it.point"
                                            v-if="it.point > 0"></view>
                                        <view class="count" v-text="it.point" v-else></view>
                                    </view>
                                </view>
                            </view>
                            <view class="item">
                                <view class="itemLeft">
                                    <image src="../../static/svg/coupon.svg" mode=""></image>
                                </view>
                                <view class="itemRight">
                                    <view v-text="it" v-for="it,idx in item.coupon_names"
                                        v-if="item.coupon_names.length > 0"></view>
                                    <view v-if="item.coupon_names.length == 0">--</view>
                                </view>
                            </view>
                        </view>
                    </view>
                </u-swipe-action-item>
            </u-swipe-action>
        </view>
        <mod v-if="showModule" @getStatus="getStatus" :config="configs"></mod>
    </view>
</template>

<script>
    import mod from "@/components/mod.vue"
    import {
        getAppointmentList,
        getScan,
        deleteAppointOrders
    } from "@/api/index.js"

    export default {
        components: {
            mod
        },
        data() {
            return {
                options1: [{
                    text: '削除',
                    style: {
                        backgroundColor: '#D93025'
                    }
                }],
                showModule: false,
                configs: {},
                current: 0,
                array: [],
                aid: "",
                title:""
            }
        },
        onLoad(options) {
            let that = this
            that.date = options.date
            that.title = options.date
            that.getList(options.id)
        },
        methods: {
            toDe(e){
              uni.navigateTo({
                  url:"/pages/customer/detail/detail?id="+e
              })  
            },
            goback(){
              uni.navigateBack()  
            },
            getList(id) {
                let that = this
                let data = {}
                data.date = that.date
                data.type = "come"
                getScan(data).then((res) => {
                    if (res.code == 200) {
                        that.array = res.data.orders
                    }
                })
            },
            toUp(e) {
                return e.toUpperCase()
            },
            handleClick(e) {
                this.id = e
                let obj = {}
                obj.title = 'この情報を削除しますか？'
                obj.content = '削除すると、この情報は完全に削除されます。'
                obj.confirm = true
                obj.cancel = true
                obj.confirmText = "情報を削除"
                obj.cancelText = "キャンセル"
                this.configs = obj
                this.current = e
                this.showModule = true
            },
            getStatus(e) {
                if (e == "confirm") {
                    let id = this.id
                    deleteAppointOrders(id).then((res) => {
                        this.showModule = false
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            this.getList(this.aid)
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                } else {
                    this.showModule = false
                }
            }
        }
    }
</script>

<style lang="scss">
    .swipe-action__content {
        box-sizing: border-box;
        padding: 20upx 0;
    }

    .itemRight {
        font-family: Hiragino Sans-W3, Hiragino Sans;
    }

    .item {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        padding: 20upx 0;

        .itemContent {
            display: flex;
            align-items: center;
        }

        .coin {
            align-items: flex-start;
            // margin-left: 276upx;
            display: flex;
            align-items: center;

            .coni {
                width: 40upx;
                height: 40upx;
                background: #D93025;
                color: #fff;
                text-align: center;
                line-height: 40upx;
                font-size: 28upx;
                font-weight: bold;
                border-radius: 50%;
                margin-right: 20upx;
            }

            .count {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #D93025;
            }

        }

        .itemLeft {
            width: 40upx;
            height: 40upx;
            margin-right: 20upx;

            image {
                width: 40upx;
                height: 40upx;
            }
        }
    }
</style>