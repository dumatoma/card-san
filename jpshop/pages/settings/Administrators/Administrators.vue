<template>
    <view>
        <u-navbar :border="true" :placeholder='true' :autoBack="true" title="副管理者一覧" :safeAreaInsetTop="true">
            <view class="u-nav-slot" slot="right">
                <image src="../../../static/svg/addblue.svg" style="width: 60upx;height: 60upx;" @click="toAddnew"
                    mode=""></image>
            </view>
        </u-navbar>
        <view class="content">
            <u-swipe-action>
                <u-swipe-action-item :options="options1" v-for="(item,index) in array" @click="deleteItem(index)">
                    <view class="swipe-action u-border-top u-border-bottom">
                        <view class="swipe-action__content">
                            <view class="staffItem" @click="toCheckDetail(item.id)">
                                <view class="staffDetail">
                                    <view class="staffName" v-text="item.name"></view>
                                    <view class="staffFu" v-text="`電話番号：${item.phone}`"></view>
                                </view>
                            </view>
                        </view>
                    </view>
                </u-swipe-action-item>
            </u-swipe-action>
        </view>
        <mod :config="config" @getStatus="confirm" v-if="show"></mod>
    </view>
</template>

<script>
    import {
        getAdminList,
        deleteAdmin
    } from '@/api/index.js'
    import mod from '@/components/mod.vue'
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
                array: [],
                config:{},
                show: false,
                current: 0,
                name:""
            };
        },
        created() {
            this.getAdminList()
        },
        methods: {
            toAddnew() {
                uni.navigateTo({
                    url: "addNew/addNew"
                })
            },
            toCheckDetail(id) {
                uni.navigateTo({
                    url: "addNew/jurisdiction?type=edit&id=" + id
                })
            },
            getAdminList() {
                let that = this
                getAdminList().then((res) => {
                    if (res.code == 200) {
                        that.array = res.data.admins
                    } else if (res.code == 403) {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                        setTimeout(() => {
                            uni.navigateBack()
                        }, 1500)
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },
            deleteItem(index) {
                let that = this
                that.current = index
                that.show = true
                that.config={
                    type: "button",
                    cancel: true,
                    default: true,
                    title: "この管理者を削除",
                    content: that.array[index].name+"を削除しますか？",
                    cancelText: "削除",
                    defaultText: "キャンセル"
                }
            },
            confirm(e) {
                let that = this
                if (e == "default") {
                    this.show = false
                } else {
                    let index = that.current * 1
                    let id = that.array[index].id
                    deleteAdmin(id).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            that.getAdminList()
                        } else if (res.code == 403) {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                            setTimeout(() => {
                                uni.navigateBack()
                            }, 1500)
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                }
                that.show = false
            }
        }
    }
</script>

<style lang="scss">
    .staffItem {
        display: flex;
        box-sizing: border-box;
        padding: 60upx;

        .staffDetail {
            .staffName {
                font-size: 32upx;
                font-family: Hiragino Sans-W6, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }

            .staffFu {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: rgba(29, 29, 31, 0.9);
                margin: 20upx 0;
            }

            .staffIntro {
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #86868B;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
            }
        }



        .staffImage {
            width: 160upx;
            height: 214upx;
            flex-shrink: 0;
            margin-right: 20upx;

            image {
                width: 100%;
                height: 100%;
            }
        }
    }

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

    .swipe-action__content {
        padding: 0 !important;
    }
</style>
