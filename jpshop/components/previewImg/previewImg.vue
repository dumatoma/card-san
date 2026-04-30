<template>
    <view class="sc">
        <!-- 自定义顶部导航 -->
        <u-navbar :title="name" :placeholder="true" :safeAreaInsetTop="true" bgColor="#1D1D1F"
            :titleStyle="{color:'#fff'}">
            <view class="u-nav-slot" slot="left">
                <image src="../../static/icons/wclose.png" style="width: 60upx;height: 60upx;" mode=""
                    @click="closeMask"></image>
            </view>
            <view class="u-nav-slot" slot="right">
                <image src="../../static/icons/delete.png" style="width: 60upx;height: 60upx;" @click="deleteImg"
                    mode=""></image>
            </view>
        </u-navbar>
        <view style="width: 100%;height: 140upx;"></view>
        <view class="contain">
            <u-swiper :autoplay="false" :list="images" @change="change" :current="currentPage" imgMode="aspectFit"
                height='455' bgColor="transparent"></u-swiper>
        </view>
        <view class="count">
            {{current}}/{{images.length}}
        </view>
        <u-action-sheet :actions="list" @select="handleSelect" @close="handleClose" :closeOnClickOverlay='true'
            :closeOnClickAction="true" :safeAreaInsetBottom='true' :show="show"></u-action-sheet>
        <mod v-if="showModule" @getStatus="getStatus" :config="configs"></mod>
    </view>
</template>

<script>
    import mod from '@/components/mod.vue'
    import {
        deleteMemberImage
    } from '@/api/index.js'
    export default {
        props: {
            images: {
                type: Array,
                default: () => {
                    return [];
                }
            },
            currentPage: {
                type: String,
                default: () => {
                    return 0;
                }
            },
            userId: {
                type: Number,
                default: () => {
                    return 0;
                }
            }
        },
        name: "previewImage",
        components: {
            mod
        },
        data() {
            return {
                name: "fileName",
                current: 1,
                show: false,
                configs: {},
                showModule: false,
                list: [{
                    name: "写真を削除",
                    color: '#D93025',
                }, {
                    name: 'キャンセル'
                }],
                imageArray: []
            }
        },
        created() {
            this.current = this.currentPage + 1
        },
        methods: {
            change(e) {
                this.current = e.current + 1
            },

            deleteImg() {
                let that = this
                that.show = true
            },
            handleClose() {
                this.show = false
            },
            handleSelect(e) {
                let that = this
                if (e.name = "写真を削除") {
                    that.configs = {
                        title: "",
                        content: "削除しますか？",
                        confirm: true,
                        cancel: true,
                        confirmText: "削除する",
                        cancelText: "キャンセル",
                        type: "normal"
                    }
                    that.showModule = true
                }

            },
            getStatus(status) {
                let that = this
                let index = that.current - 1
                if (status == 'confirm') {
                    deleteMemberImage(that.current - 1, that.userId).then((res) => {
                        if (res.code == 200) {
                            that.images.splice(index, 1)
                            that.showModule = false
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                } else if (status == "cancel") {
                    that.showModule = false
                }
            },

            closeMask() {
                let that = this
                this.$emit("closeMask", that.images)
            }
        }
    }
</script>

<style lang="scss" scoped>
    .sc {
        background: rgb(29, 29, 31);
        height: 100vh;
        z-index: 999 !important;
        width: 100vw;
    }

    .count {
        font-size: 36upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #FFFFFF;
        text-align: center;
        position: absolute;
        bottom: 146upx;
        width: 100%;
    }
</style>
