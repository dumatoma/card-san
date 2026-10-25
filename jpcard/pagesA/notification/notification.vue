<template>
    <view :lrDistance="5" leftMed="scrollL" rightMed="scrollR" @scrollL="scrollL" @scrollR="scrollR">
        <!-- [カスタムエイド 2026-10-20] 店名が長いときに右のアイコンにくっつかないよう、店名の幅を 340rpx に(入りきらない分は「…」) -->
        <u-navbar titleWidth="340rpx" :leftIconColor="color" :title="shopName" :titleStyle="{'color':color}" :bgColor="bgColor"
            :placeholder="true" :safeAreaInsetTop="true" :border="true" @rightClick="rightClick" @leftClick="leftClick">
            <view class="u-nav-right" style="display: flex;align-items: center;" slot="right">
                <!-- [カスタムエイド 2026-10-20] 予約履歴のアイコン(会員証のタブと同じ) -->
                <rh-icon :sid="sid" :color="color" :bgColor="bgColor"></rh-icon>
                <view :style="`backgroundImage: url(${svgData30})`" style="width: 50upx;height: 50upx;"
                    class="iconright" @click=""></view>
            </view>
        </u-navbar>
        <view class="news-box cs-nb" v-if="type == 1">
            <view class="news-items" v-for="(item,index) in list" :key="index">
                <!-- [カスタムエイド 2026-10-19] 点(●●●)をやめ、2枚以上のときだけ右下に「1 / 4」。押したら全画面・左右にスワイプ -->
                <view class="cs-swipe" v-if="item.images.length > 0">
                    <u-swiper :list="item.images" height="375" :autoplay="false" :indicator="false" :radius="0"
                        @change="setImgIndex(index, $event)" @click="openImages(item.images, $event)">
                    </u-swiper>
                    <view class="cs-top-count" v-if="item.images.length > 1">{{(imgIndex[index] || 0) + 1}} / {{item.images.length}}</view>
                </view>
                <view class="user-box">
                    <view class="user">
                        <view class="right">
                            <view class="store-name">
                                {{item.shop_name}}
                            </view>
                            <view class="time">
                                {{item.date}}
                                <!-- [カスタムエイド 2026-10-22] 編集したお知らせは、投稿した日のあとに編集した日を小さく -->
                                <text v-if="item.edit_date">({{item.edit_date}} 編集)</text>
                                <!-- [カスタムエイド 2026-10-25] 固定は小さな「固定」の印に -->
                                <text class="cs-pin" v-if="item.is_top == 1">固定</text>
                                <!-- <text>{{item.zhi}}</text> -->
                            </view>
                        </view>
                    </view>
                    <view class="title">
                        {{item.title}}
                    </view>
                    <!-- [カスタムエイド 2026-10-19] 空行を少しの間として表示(共通の行間 1.6) -->
                    <!-- [カスタムエイド 2026-10-25] 長い本文は「…」で切り、後ろに「続きを読む」(押すと全部) -->
                    <view class="des">
                        <view class="cs-para" v-for="(t, ti) in textParas(noticeText(item, index))" :key="ti">{{t}}</view>
                        <text class="cs-more" v-if="isLongNotice(item) && !openNotice[index]" @click.stop="$set(openNotice, index, true)">続きを読む</text>
                    </view>
                </view>
            </view>
        </view>
        <view style="height: 90vh;" @scrolltolower="getMore" class="news-box" scroll-y v-if="type == 2">
            <!-- [カスタムエイド 2026-10-24] 動画は押したらアプリの中で再生(以前は Instagram のページを開いて見られなかった) -->
            <view class="news-items1" v-for="(item,index) in insList" :key="item.id || index" @click="openIns(item)">
                <view class="insImage" v-if="item.media_type == 'IMAGE'">
                    <image :src="item.media_url" mode="aspectFill"></image>
                </view>
                <view class="insImage insVideoThumb" v-if="item.media_type == 'VIDEO'">
                    <image v-if="item.thumbnail_url" :src="item.thumbnail_url" mode="aspectFill" style="width:100%;height:100%;"></image>
                    <view v-else class="videoPlaceholder"></view>
                    <view class="playIcon">▶</view>
                </view>
                <view class="insImage" v-if="item.media_type == 'CAROUSEL_ALBUM'">
                    <u-swiper interval="6000" :indicator="true" indicatorActiveColor="#1A73E8" height="400"
                        :list="item.carousel"></u-swiper>
                </view>
                <view class="user-box1">
                    <view class="insinfo">
                        <view class="insavatar">
                            <image :src="item.avatar" mode="aspectFill"></image>
                        </view>
                        <view class="insname" v-text="item.username"></view>
                    </view>
                    <view class="timestamps" v-text="item.datetime"></view>
                    <view class="des">
                        <text>{{ (item.showAllCaption || !isLongCaption(item.caption)) ? item.caption : (item.caption.slice(0, 85) + '…') }}</text>
                        <text v-if="isLongCaption(item.caption) && !item.showAllCaption" class="readmore" @click.stop="expandCaption(index)">続きを読む</text>
                    </view>
                    <view class="tps">
                        Synced from Instagram
                    </view>
                </view>
            </view>
            <!-- [カスタムエイド 2026-10-24] 続きの読み込み中は、画面全体の「Loading...」ではなく一番下に小さく -->
            <view class="ins-more" v-if="insLoading">読み込み中…</view>
        </view>
        <!-- <web-view v-if="links != ''" :src="links"></web-view> -->
        <list :caiDanShow="caiDanShow" :mid="id" :sid="sid" :tabs="tabs" @seed="seed" @selected="chooseItem"></list>
    </view>
</template>

<script>
    import { textParas } from '@/utils/textParas.js' // [カスタムエイド 2026-10-19] お店が入れた空行を少しの間として表示
    import list from '../../components/list.vue'
    import sp from "@/components/swiper-direct-com.vue"
    import svg30 from '../../static/svgjs/sv_30.js'
    import rhIcon from '../../components/rh-icon.vue' // [カスタムエイド 2026-10-20]
    import {
        getShopNotice,
        getShopInfo,
        getInsList
    } from "@/api/index.js"
    export default {
        components: {
            rhIcon,
            list,
            sp
        },
        props: ["color", "id", "sid", "bgColor", "shopName", "tabs"],
        data() {
            return {
                imgIndex: {}, // [カスタムエイド 2026-10-19] お知らせごとの画像の今の位置
                openNotice: {}, // [カスタムエイド 2026-10-25] 「続きを読む」で開いたお知らせ
                value: 2,
                caiDanShow: false,
                type: "",
                list: [],
                insList: [],
                insLoading: false, // [カスタムエイド 2026-10-24] 続きを読み込み中か(同時に何回も読み込まない)
                insEnd: false, // 最後まで読み込んだか
                svgData30: '"' + svg30 + '"',
                afrt: "",
                insname: "ins昵称ins昵称ins昵称ins昵称ins昵称",
                insAvatar: "../../static/image/youtube.png"
            };
        },
        mounted() {
            //监听下拉加载更多
            uni.$on('onReachBottom', () => {
                this.getMore()
            })
        },
        beforeDestroy() {
            uni.$off('onReachBottom');
        },
        created() {
            let that = this
            that.svgData30 = that.changeColor(that.svgData30, that.color);
            that.getInfo()
        },
        methods: {
            setImgIndex(index, e) {
                this.$set(this.imgIndex, index, (e && e.current) || 0)
            },
            // [カスタムエイド 2026-10-19] 画像を全画面で表示(押した画像から。左右にスワイプで次の画像)
            openImages(list, index) {
                let urls = (list || []).map((v) => (typeof v == 'string' ? v : (v && (v.url || v.image)) || '')).filter((v) => v)
                if (!urls.length) return
                uni.previewImage({ current: index || 0, urls: urls, indicator: 'number', loop: urls.length > 1 })
            },
            // [カスタムエイド 2026-10-25] 本文が長いとき(100文字より長い)は、100文字で切って「…」を付ける
            isLongNotice(item) {
                return !!item.content && item.content.length > 100
            },
            noticeText(item, index) {
                if (!this.isLongNotice(item) || this.openNotice[index]) return item.content
                return item.content.slice(0, 100).replace(/\s+$/, '') + '…'
            },
            textParas(t) {
                return textParas(t)
            },
            // [カスタムエイド 2026-10-24] 以前は下までスクロールするたびに、読み込み中でも・最後まで読み込んだあとでも
            //   画面全体に「Loading...」を出して読み込み直していた(スクロール中に何度も出て止まる原因)。
            //   読み込み中・最後まで読んだあとは何もしない。表示は一番下に小さく「読み込み中…」だけ
            getMore() {
                if (this.type != 2 || this.insLoading || this.insEnd || !this.afrt) return
                this.getInsLists()
            },
            changeColor(url, color) {
                let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#",
                    "%23")); //转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
                return res;
            },
            scrollR() {
                this.caiDanShow = false
            },
            scrollL() {

            },
            seed(e) {
                this.caiDanShow = false
            },
            getInsLists() {
                let that = this
                if (that.type == 2) {
                    let data = {}
                    data.sid = that.sid
                    data.after = that.afrt
                    that.insLoading = true
                    getInsList(data).then((result) => {
                        that.insLoading = false
                        if (result.code == 200) {
                            that.afrt = result.data.after
                            if (!that.afrt) that.insEnd = true
                            that.insList = [...that.insList, ...(result.data.medias || [])]
                        }
                    }).catch(() => { that.insLoading = false })
                }

            },
            getInfo() {
                let that = this
                getShopInfo(that.sid).then((res) => {
                    if (res.code == 200) {
                        // notice_type が未設定(null/0)のショップはデフォルトでお知らせ表示
                        const noticeType = res.data.shop.notice_type || 1
                        that.type = noticeType
                        if (noticeType == 1) {
                            that.getList()
                        }
                        if (noticeType == 2) {
                            // [カスタムエイド 2026-10-24] 前回の1ページ目を端末に残しておき、先に出す(Instagram からの読み込みは時間がかかるため)。
                            //   最新のものが届いたら入れ替える
                            const ck = 'cs_ins_' + that.sid
                            try {
                                const c = uni.getStorageSync(ck)
                                if (c && Array.isArray(c.medias) && c.medias.length && !that.insList.length) that.insList = c.medias
                            } catch (e) {}
                            that.insLoading = true
                            getInsList({ sid: that.sid, after: '' }).then((result) => {
                                that.insLoading = false
                                if (result.code == 200) {
                                    that.afrt = result.data.after
                                    that.insEnd = !that.afrt
                                    that.insList = result.data.medias || []
                                    try { uni.setStorageSync(ck, { medias: that.insList }) } catch (e) {}
                                }
                            }).catch(() => { that.insLoading = false })
                        }
                    }
                }).catch((err) => {
                    // API失敗時はデフォルトでお知らせ表示を試みる
                    that.type = 1
                    that.getList()
                })
            },
            // [カスタムエイド 2026-10-24] 動画はアプリの中で再生
            openIns(item) {
                if (item.media_type == 'VIDEO' && item.media_url) {
                    uni.navigateTo({
                        url: '../insVideo/insVideo?src=' + encodeURIComponent(item.media_url) + '&poster=' + encodeURIComponent(item.thumbnail_url || '') + '&link=' + encodeURIComponent(item.permalink || '')
                    })
                    return
                }
                // [カスタムエイド 2026-10-25] 写真・複数枚も Instagram のページ(ログインを求められる)を開かず、アプリの中で全画面表示。
                //   複数枚は左右にスワイプ(動画が混ざっているときは、写真だけ)
                let urls = []
                if (item.media_type == 'CAROUSEL_ALBUM') {
                    urls = (item.carousel || []).map((v) => (typeof v == 'string' ? v : (v && (v.url || v.media_url || v.image)) || '')).filter((v) => v && !/\.mp4(\?|$)/i.test(v))
                } else if (item.media_url) {
                    urls = [item.media_url]
                }
                if (urls.length) {
                    uni.previewImage({ current: 0, urls: urls, indicator: 'number', loop: urls.length > 1 })
                    return
                }
                this.toInsDetai(item.permalink)
            },
            toInsDetai(e) {
                uni.navigateTo({
                    url: "../webins/webins?link=" + e
                })
            },
            isLongCaption(caption) {
                return !!caption && caption.length > 85
            },
            expandCaption(index) {
                this.$set(this.insList[index], 'showAllCaption', true)
            },
            leftClick() {
                let that = this
                uni.navigateBack()
            },
            rightClick() {
                this.caiDanShow = true
            },
            getList() {
                let that = this
                getShopNotice(that.sid).then((res) => {
                    if (res.code == 200) {
                        that.list = res.data.notices
                    }
                }).catch((err) => {
                })
            },
            chooseItem(e) {
                this.caiDanShow = false
                this.$emit("changes", e)
            },
        }
    }
</script>

<style lang="scss" scoped>
    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    .tps {
        text-align: right;
        width: 100%;
        font-size: 20rpx;
        color: #707070;
        margin-top: 20rpx;
    }

    .insinfo {
        display: flex;
        align-items: center;
        margin-bottom: 10rpx;

        .insavatar {
            width: 80rpx;
            height: 80rpx;
            border-radius: 50%;
            border: 2rpx solid #707070;
            overflow: hidden;
            margin-right: 20rpx;

            image {
                width: 100%;
                height: 100%;
            }
        }

        .insname {
            font-weight: bold;
            font-size: 28rpx;
            color: #1D1D1F;
        }
    }

    .timestamps {
        color: #707070;
        font-size: 24upx;
        margin-bottom: 10upx;
    }

    page {
        background: #fff !important;
    }

    .topblank {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 50upx;
        z-index: 150 !important;
        background: #fff;
    }

    .insImage {
        width: 100%;
        height: 750upx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .ins-more {
        text-align: center;
        font-size: 24rpx;
        color: #86868B;
        padding: 24rpx 0 40rpx;
    }

    .insVideoThumb {
        position: relative;
        background: #000;

        .videoPlaceholder {
            width: 100%;
            height: 100%;
            background: #1a1a1a;
        }

        .playIcon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 80rpx;
            color: rgba(255, 255, 255, 0.85);
        }
    }

    .u-nav-right {
        image {
            width: 48rpx;
            height: 48rpx;
        }
    }

    .user-box1 {
        width: 90%;
        margin: 0 auto;
        margin-top: 20rpx;
    }

    .news-items1 {
        padding-bottom: 60rpx;
        border-bottom: 1px solid #D2D2D7;
    }

    .news-box {
        margin-top: 12rpx;
        padding-bottom: 172rpx;

        .news-items {
            margin-top: 12rpx;
            padding-bottom: 60rpx;
            border-bottom: 1px solid #D2D2D7;

            .user-box:first-child {
                margin-top: 20upx;
            }

            .user-box {
                width: 100%;
                margin: 0 auto;
                margin-top: 40rpx;
                box-sizing: border-box;
                padding: 0 32upx;

                .user {
                    display: flex;
                    margin-bottom: 10rpx;

                    .left {
                        margin-right: 32rpx;

                        image {
                            width: 62rpx;
                            height: 62rpx;
                            border-radius: 50%;
                        }
                    }

                    .right {
                        .store-name {
                            color: #1D1D1F;
                            font-size: 28rpx;
                            font-weight: 700;
                        }

                        .time {
                            margin-top: 10rpx;
                            font-size: 24rpx;
                            color: #707070;

                            text {
                                margin-left: 40rpx;
                                color: #1A73E8;
                            }
                        }
                    }
                }

                .title {
                    font-size: 32rpx;
                    font-weight: 700;
                    color: #1D1D1F;
                    margin-bottom: 10rpx;
                }

                .des {
                    font-size: 28rpx;
                    color: #707070;
                    white-space: pre-wrap;

                    .readmore {
                        color: #1A73E8;
                        margin-left: 6rpx;
                    }
                }
            }
        }
    }

    /* [カスタムエイド 2026-10-19] 画像の枚数表示(メニュー写真・TOP画像と同じ形) */
    .cs-swipe {
        position: relative;
    }
    .cs-top-count {
        position: absolute;
        right: 24rpx;
        bottom: 24rpx;
        z-index: 2;
        padding: 6rpx 20rpx;
        border-radius: 24rpx;
        background: rgba(0, 0, 0, 0.6);
        color: #FFFFFF;
        font-size: 24rpx;
        font-weight: 500;
        letter-spacing: 1rpx;
        pointer-events: none;
    }

    /* ===== [カスタムエイド 2026-10-19] お知らせ一覧の間延びを直す ===== */
    .news-box {
        margin-top: 0 !important;
        background: #F5F5F7;
    }
    .news-box .news-items {
        margin-top: 0 !important;
        margin-bottom: 16rpx;
        padding-bottom: 28rpx !important;
        border-bottom: none !important;
        background: #FFFFFF;
    }
    .news-box .news-items .user-box,
    .news-box .news-items .user-box:first-child {
        margin-top: 24rpx !important;
    }
    .news-box .news-items .user-box .user {
        margin-bottom: 6rpx !important;
    }
    .news-box .news-items .user-box .user .right {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
    }
    .news-box .news-items .user-box .user .right .store-name {
        font-size: 24rpx !important;
        font-weight: 400 !important;
        color: #6E6E73 !important;
        margin-right: 16rpx;
    }
    .news-box .news-items .user-box .user .right .time {
        margin-top: 0 !important;
    }
    .news-box .news-items .user-box .title {
        line-height: 1.4;
        margin-bottom: 8rpx !important;
    }
    .news-box .news-items .user-box .des {
        white-space: normal !important;
        color: #515154 !important;
    }

    /* ===== [カスタムエイド 2026-10-25] お知らせ:1投稿ずつ角の丸い白いカードに(モック「Card-San App 画面改善モック」) ===== */
    .news-box.cs-nb {
        padding: 24rpx 24rpx 0;
        box-sizing: border-box;
    }
    .news-box .news-items {
        margin-bottom: 24rpx !important;
        border-radius: 28rpx;
        overflow: hidden;
        padding-bottom: 32rpx !important;
    }
    .news-box .news-items .user-box,
    .news-box .news-items .user-box:first-child {
        margin-top: 28rpx !important;
    }
    .news-box .news-items .user-box .title {
        font-size: 32rpx !important;
        margin-bottom: 10rpx !important;
    }
    .news-box .news-items .user-box .des {
        font-size: 28rpx !important;
        line-height: 1.7;
        color: #3A3A3C !important;
    }
    .news-box .news-items .user-box .user .right .time .cs-pin {
        margin-left: 16rpx !important;
        padding: 2rpx 12rpx;
        border-radius: 8rpx;
        background: #EEF4FF;
        color: #1A73E8 !important;
        font-size: 22rpx;
        font-weight: 700;
    }
    .cs-more {
        color: #1A73E8;
        font-weight: normal;
    }
</style>