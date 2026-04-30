<template>
    <view>
        <view class="contain">
            <view class="title">
                <view class="titleLeft">
                    配信履歴
                </view>
                <view class="titleRight">
                    <view>
                        メッセージ数/月
                    </view>
                    <view>
                        {{count}}/{{tanum}}
                    </view>
                </view>
            </view>
            <view class="content">
                <u-swipe-action>
                    <u-swipe-action autoClose>
                        <u-swipe-action-item v-for="(item,index) in array" :key="item.id" :options="options1"
                            @click="delItem(item.id)">
                            <view class="swipe-action u-border-top u-border-bottom">
                                <view class="swipe-action__content" style="padding: 0;" @click="toDe(item.id)">
                                    <view class="itemContent" v-if="item.status == 0">
                                        <view class="iLeft">
                                            <view class="icontent" v-text="item.content"></view>
                                            <view class="irange" v-text="`ターゲット：${item.receiver == 1? '全員' : '絞り込み'}`">
                                            </view>
                                            <view class="idate" v-text="`日時：${item.order_time}`"></view>
                                        </view>
                                        <view class="iRight">
                                            予約
                                        </view>
                                    </view>
                                    <view class="mContent" v-else>
                                        <view class="mImage" v-if="item.content.includes('http')">
                                            <image :src="item.content" mode="aspectFill"></image>
                                        </view>
                                        <view class="mInfo">
                                            <view class="mitem" v-text="`タイトル：${item.title}`"></view>
                                            <view class="mitem" v-text="`ターゲット：${item.receiver == 1? '全員' : '絞り込み'}`">
                                            </view>
                                            <view class="mitem" v-text="`日時：${item.order_time}`"></view>
                                        </view>
                                        <view class="mRight" v-if="item.is_send == 1">
                                            配信済
                                        </view>
                                        <view class="mRight" style="color: #1A73E8;border: 2upx solid #1A73E8;" v-else>
                                            予約
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </u-swipe-action-item>
                    </u-swipe-action>
                </u-swipe-action>
            </view>
            <view class="btns">
                <u-button class="btn" @click="toAddnew">メッセージ作成</u-button>
            </view>
            <view class="blank"></view>
        </view>
        <mod :config="config" @getStatus="confirm" v-if="show"></mod>
    </view>

</template>

<script>
    import {
        getTimingList,
        deleteTiming
    } from "@/api/index.js"
    import mod from "@/components/mod.vue"
    export default {
        data() {
            return {
                array: [],
                count:0,
                tanum:0,
                options1: [{
                    text: '削除',
                    style: {
                        backgroundColor: '#D93025'
                    }
                }],
                show: false,
                id: "",
            }
        },
        components: {
            mod
        },
        created() {

        },
        onShow() {
            this.getList()
        },
        methods: {
            getList() {
                let that = this
                getTimingList().then((res) => {
                    if (res.code == 200) {
                        that.array = res.data.messages
                        that.count = res.data.count
                        that.tanum = res.data.total_count
                    }
                })
            },
            toAddnew() {
                uni.navigateTo({
                    url: "edit/edit?source=add"
                })
            },
            toDe(e) {
                uni.navigateTo({
                    url: "edit/edit?source=edit&id=" + e
                })
            },

            confirm(e) {
                let that = this
                if (e == "cancel") {
                    deleteTiming(that.id).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            that.getList()
                            that.show = false
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                } else {
                    that.show = false
                }
            },

            delItem(id) {
                let that = this
                that.show = true
                that.id = id
                that.config = {
                    type: "button",
                    cancel: true,
                    default: true,
                    title: "メッセージを削除",
                    content: "このメッセージを削除しますか？",
                    cancelText: "削除",
                    defaultText: "キャンセル"
                }

            },
        }
    }
</script>

<style lang="scss" scoped>
    .u-page {
        padding: 0;
    }

    .u-demo-block__title {
        padding: 10px 0 2px 15px;
    }

    .swipe-action {
        &__content {
            padding: 25rpx 0;

            &__text {
                font-size: 15px;
                color: $u-main-color;
                padding-left: 30rpx;
            }
        }
    }

    .blank {
        height: 216upx;
        width: 100%;
    }

    .btns {
        height: 216upx;
        background: #F5F5F7;
        box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);
        box-sizing: border-box;
        padding: 40upx 0;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100vw;
        z-index: 999;

        .btn {
            width: 480upx;
            height: 96upx;
            background: #34C759;
            border-radius: 20upx;
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            line-height: 96upx;
            margin: 0 auto;
        }
    }

    .mContent {
        display: flex;
        box-sizing: border-box;
        padding: 20upx 32upx;
        align-items: center;
        justify-content: space-between;

        .mInfo {
            // margin-left: 20upx;
            margin-right: 12upx;
            flex: 1;
            margin-left: 20upx;

            .mitem {
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #AAAAAA;
                margin-bottom: 20upx;
            }
        }

        .mImage {
            width: 216upx;
            height: 160upx;

            image {
                width: 100%;
                height: 100%;
            }
        }

        .mRight {
            width: 96upx;
            height: 48upx;
            background: #FFFFFF;
            opacity: 1;
            border: 2upx solid #AAAAAA;
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #AAAAAA;
            text-align: center;
            line-height: 48upx;
            flex-shrink: 0;
            border-radius: 10upx;
            margin-left: 12upx;
        }
    }

    .itemContent {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        padding: 20upx 32upx;

        .irange,
        .idate {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #AAAAAA;
            margin-top: 20upx;
        }


        .icontent {
            display: -webkit-box;
            overflow: hidden;
            -webkit-box-orient: vertical;
            line-clamp: 2;
            -webkit-line-clamp: 2; //显示几行 
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            line-height: 42upx;
        }

        .iRight {
            width: 96upx;
            height: 48upx;
            background: #FFFFFF;
            opacity: 1;
            border: 2upx solid #1A73E8;
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
            text-align: center;
            line-height: 48upx;
            flex-shrink: 0;
            border-radius: 10upx;
            margin-left: 12upx;
        }
    }

    .title {
        height: 126upx;
        background: #F5F5F7;
        opacity: 1;
        border-top: 2upx solid #d2d2d7;
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