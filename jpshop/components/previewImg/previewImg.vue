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
        <!-- [カスタムエイド 2026-10-23] 2本指で拡大・拡大したまま指で移動できるように(アプリ修正一覧 No.13)。
             等倍のときだけ左右のスワイプで次の画像へ。拡大中はスワイプを止める。画像を切り替えたら等倍に戻す。
             2回すばやく押すと 2倍 ⇔ 等倍 -->
        <view class="contain">
            <swiper class="pv-swiper" :style="{ height: areaH + 'px' }" :current="swiperIndex" :disable-touch="zoomed"
                :circular="false" @change="change">
                <swiper-item v-for="(item, i) in images" :key="i">
                    <movable-area class="pv-area" :scale-area="true">
                        <movable-view class="pv-view" direction="all" :scale="true" :scale-min="1" :scale-max="4"
                            :scale-value="scales[i] || 1" :out-of-bounds="false" :damping="40"
                            @scale="onScale($event, i)" @tap="onTap(i)">
                            <image class="pv-img" :src="item" mode="aspectFit"></image>
                        </movable-view>
                    </movable-area>
                </swiper-item>
            </swiper>
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
                name: "画像", // [カスタムエイド 2026-10-18] 「fileName」と出ていたので固定の見出しに
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
                imageArray: [],
                // [カスタムエイド 2026-10-23] 拡大
                swiperIndex: 0,
                areaH: 455,
                scales: [], // 画像ごとの拡大率(切り替えたら 1 に戻す)
                nowScale: 1,
                lastTap: 0
            }
        },
        computed: {
            zoomed() {
                return this.nowScale > 1.02
            }
        },
        created() {
            this.swiperIndex = Number(this.currentPage) || 0
            this.current = this.swiperIndex + 1
            this.scales = (this.images || []).map(() => 1)
            // 上のバー・下の「1/3」を除いた高さいっぱいに表示
            try {
                const sys = uni.getSystemInfoSync()
                const h = sys.windowHeight - (sys.statusBarHeight || 0) - 44 - uni.upx2px(140) - uni.upx2px(220)
                if (h > 200) this.areaH = h
            } catch (e) {}
        },
        methods: {
            change(e) {
                const i = e.detail ? e.detail.current : e.current
                this.current = i + 1
                this.swiperIndex = i
                this.resetZoom()
            },
            // 拡大率を等倍に戻す(movable-view は値が変わったときだけ反映されるので、少しずらしてから 1 に)
            resetZoom() {
                this.nowScale = 1
                this.scales = (this.images || []).map(() => 1.0001)
                this.$nextTick(() => {
                    this.scales = (this.images || []).map(() => 1)
                })
            },
            onScale(e, i) {
                if (i !== this.swiperIndex) return
                this.nowScale = (e.detail && e.detail.scale) || 1
            },
            // 2回すばやく押したら 2倍 ⇔ 等倍
            onTap(i) {
                const now = Date.now()
                if (now - this.lastTap < 300) {
                    const to = this.zoomed ? 1 : 2
                    this.$set(this.scales, i, to == 1 ? 1.0001 : 2)
                    this.$nextTick(() => {
                        this.$set(this.scales, i, to)
                        this.nowScale = to
                    })
                    this.lastTap = 0
                } else {
                    this.lastTap = now
                }
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
                if (e.name == "写真を削除") { // [カスタムエイド 2026-10-23] 「=」だったため、キャンセルを押しても削除の確認が出ていた
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
                            // [カスタムエイド 2026-10-23] 最後の1枚を消したら閉じる。後ろの画像を消したら番号を合わせる
                            if (!that.images.length) {
                                that.closeMask()
                                return
                            }
                            if (that.swiperIndex > that.images.length - 1) that.swiperIndex = that.images.length - 1
                            that.current = that.swiperIndex + 1
                            that.resetZoom()
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

    /* [カスタムエイド 2026-10-23] 拡大 */
    .pv-swiper { width: 100%; }
    .pv-area { width: 100%; height: 100%; overflow: hidden; }
    .pv-view { width: 100%; height: 100%; }
    .pv-img { width: 100%; height: 100%; }

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
