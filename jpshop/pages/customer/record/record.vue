<template>
    <view class="container">
        <view class="listContent">
            <u-swipe-action autoClose>
                <u-swipe-action-item v-for="(item,index) in array" :options="options1" :key="index"
                    @click="handleClick(item.id)">
                    <view class="swipe-action u-border-top u-border-bottom">
                        <view class="swipe-action__content">
                            <view class="item">
                                <view class="itemLeft">
                                    <image src="../../../static/svg/correct.svg" mode=""></image>
                                </view>
                                <view class="itemRight" style="display: flex;flex-wrap: wrap;">
                                    <view class="il" v-text="item.date"></view>
                                    <view style="flex: 1;display: flex;flex-wrap: wrap;align-items: flex-start;">
                                        <view class="ir" v-for="it,idx in item.times" v-text="it"
                                            v-if="item.times.length > 0"
                                            style="margin-left: 12upx;text-align: center;width: 80upx;"></view>
                                        <view class="ir" v-if="item.times.length == 0" style="margin-left: 10upx;">--
                                        </view>
                                    </view>

                                </view>
                            </view>

                            <view class="item">
                                <view class="itemLeft">
                                    <image src="../../../static/svg/menu.svg" mode=""></image>
                                </view>
                                <view class="itemRight">
                                    <view v-if="item.menu_names.length > 0" v-for="it,idx in item.menu_names"
                                        v-text="it"></view>
                                    <view v-if="item.menu_names.length == 0">--</view>
                                </view>
                            </view>

                            <view class="item">
                                <view class="itemLeft">
                                    <image src="../../../static/svg/user.svg" mode=""></image>
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
                                <view class="coin" style="width: 100%;" v-if="item.point.length > 0">
                                    <view class="coni" v-text="toUp(item.point[0].card)"></view>
                                    <view style="display:flex;flex:1;flex-wrap:wrap">
                                        <view v-for="it,idx in item.point">
                                            <view class="count" style="color: #1d1d1f;width: 60upx;text-align: center;"
                                                v-text="it.point" v-if="it.point > 0"></view>
                                            <view class="count" style="width: 60upx;text-align: center;" v-text="it.point"
                                                v-else></view>
                                        </view>
                                    </view>
                                </view>
                            </view>
                            <view class="item">
                                <view class="itemLeft">
                                    <image src="../../../static/svg/coupon.svg" mode=""></image>
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
        getCome,
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
                aid: ""
            }
        },
        onLoad(options) {
            let that = this
            that.aid = options.id
            that.getList(options.id)
        },
        methods: {
            getList(id) {
                let that = this
                let data = {}
                data.id = id
                data.type = "come"
                getCome(data).then((res) => {
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