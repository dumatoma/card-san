<template>
    <view class="contain">
        <u-navbar title="メッセージ作成・編集" :placeholder="true" :autoBack="true">
            <view class="u-nav-slot" slot="right">
                <image @click="show = true" src="../../../../static/svg/preview.svg" mode=""
                    style="width: 40upx;height: 40upx;"></image>
            </view>
        </u-navbar>
        <view class="tabs">
            <view class="tab" :class="current == 1? 'active' : '' " @click="changeTab(1)">
                <view class="tabImage">
                    <image v-if="current == 1" src="../../../../static/icons/messageA.png" mode=""></image>
                    <image v-else src="../../../../static/icons/essage.png" mode=""></image>
                </view>
                <view class="tabText">
                    テキスト
                </view>
            </view>
            <view class="tab" :class="current == 2? 'active' : '' " @click="changeTab(2)">
                <view class="tabImage">
                    <image v-if="current == 2" src="../../../../static/icons/imageA.png" mode=""></image>
                    <image v-else src="../../../../static/icons/image.png" mode=""></image>
                </view>
                <view class="tabText">
                    写真・画像
                </view>
            </view>
        </view>
        <view class="content" v-if="current == 1">
            <view class="item">
                <view class="itemTitle">
                    <text class="icon">※</text>
                    <text>タイトル </text>
                    <text class="fu">(タイトルはメッセージに反映されません)</text>
                </view>
                <u-input v-model="info.title" placeholder="タイトルを入力してください"></u-input>
            </view>

            <view class="item">
                <view class="itemTitle">
                    <text class="icon">※</text>
                    <text>テキスト </text>
                </view>
                <u-textarea :placeholder="value2" v-model="info.content" height="323"></u-textarea>
            </view>
        </view>

        <view class="content" v-if="current == 2">
            <view class="item">
                <view class="itemTitle">
                    <text class="icon">※</text>
                    <text>タイトル </text>
                    <text class="fu">(タイトルはメッセージに反映されません)</text>
                </view>
                <u-input v-model="info.title" placeholder="タイトルを入力してください"></u-input>
            </view>

            <view class="item">
                <view class="itemTitle">
                    <text class="icon">※</text>
                    <text>画像 </text>
                </view>
                <view class="upload" @click="uploadImage">
                    <view style="color: #aaa;" v-if="info.image == '../../../../static/svg/gd3.svg'">
                        画像
                    </view>
                    <view v-if="info.image == '../../../../static/svg/gd3.svg'">
                        <image class="upimage" :src="info.image" mode=""></image>
                    </view>
                    <view class="uploadImage" v-if="info.image != '../../../../static/svg/gd3.svg'">
                        <image :src="info.image" mode="aspectFit"></image>
                    </view>
                </view>
            </view>
            <view class="item" style="margin-top: 28upx;margin-bottom: 0;">
                <view class="itemTitle">
                    <text>URLリンク</text>
                </view>
                <u-input border="bottom" v-model="info.url" placeholder="https:"></u-input>
            </view>
        </view>

        <view class="reviewContent" v-if="current == 1">
            <view class="modeuleBtns">
                <view class="mbtn" style="margin-right: 40rpx;" :style="tab == 0?'opacity:0.6 !important':''"
                    @click="changeReview(0)">
                    テンプレート①
                </view>
                <view class="mbtn" :style="tab == 1?'opacity:0.6 !important':''" @click="changeReview(1)">
                    テンプレート②
                </view>
            </view>
        </view>
        <view class="reviewContents" v-if="current == 1">
            <view class="checkBox" :style="selThis == 1? 'border:2rpx solid #1A73E8' : ''" @click.stop="changeSel">
                <view class="inner" v-if="selThis == 1"></view>
            </view>
            <view class="reviewDetail" :style="selThis == 1?'font-weight:bold;color:#1d1d1f' : ''">
                Googleレビューリンクボタンの設置
            </view>
        </view>

        <u-button type="success" @click="toNext" color="#34C759" class="btn">次へ</u-button>
        <view style="height: 40rpx;"></view>
        <u-popup :round="30" :show="show" @close="close" mode="bottom">
            <view class="modualContent">
                <view class="mTitle">
                    <image @click="close" class="mClose" src="../../../../static/icons/tclose.png" mode=""></image>
                    <view class="mWord">
                        プレビュー
                    </view>
                </view>
                <view class="mContent">
                    <view class="mTip">
                        端末やOSによっては、実際に表示される画面と異なる場合があります。
                    </view>
                    <view class="mDetail">
                        <u-avatar src="../../../../static/images/avatar.png" size="30"></u-avatar>
                        <view class="mChat" v-if="current == 1">
                            <view style="white-space: pre-wrap;word-break: break-all;box-sizing: border-box;width: 100%;padding: 20rpx;" v-text="info.content" v-if="current == 1"></view>
                        </view>
                        <view class="mChat" v-else>
                            <image :src="info.image" mode="aspectFill"
                                v-if="info.image != '../../../../static/icons/image.png'"
                                style="width:100% !important;"></image>
                        </view>
                    </view>

                    <view class="mDetail" style="margin-top: 20rpx;" v-if="current == 1 && selThis == 1">
                        <u-avatar src="../../../../static/images/avatar.png" size="30"></u-avatar>
                        <view class="mChat" style="width: 80vw;">
                            <view
                                style="height: 120rpx;width: 100%;overflow: hidden;border-radius: 10rpx;">
                                <image :src="previewImage" style="width: 100%;height: 100%;" mode="aspectFill"></image>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
        </u-popup>
    </view>
</template>
<script>
    import {
        getTimingDetail,
        getShopInfo
    } from "@/api/index.js"
    export default {
        data() {
            return {
                current: 1,
                status: "create",
                show: false,
                value2: "メッセージを入力してください",
                query: {},
                tab: -1,
                selThis: 0,
                info: {
                    title: "",
                    content: "",
                    url: "",
                    image: "../../../../static/svg/gd3.svg"
                },
                module1: [],
                module2: [],
                previewImage: ""
            }
        },
        onNavigationBarButtonTap(e) {
            let that = this
            that.show = true
        },
        onLoad(options) {
            let that = this
            let query = options
            getShopInfo().then((res) => {
                console.log(res)
                if (res.code == 200) {
                    that.module1 = res.data.shop_info.timing_message_tpl1
                    that.module2 = res.data.shop_info.timing_message_tpl2
                    that.previewImage = res.data.shop_info.comment_button_style_image
                }
            })
            that.query = query
            if (query.source == "edit") {
                that.getDetail(query.id)
            }
        },
        methods: {
            changeSel() {
                if (this.selThis == 0) {
                    this.selThis = 1
                } else {
                    this.selThis = 0
                }
            },
            changeReview(e) {
                let that = this
                if (e == 0) {
                    that.info.title = that.module1[0]
                    that.info.content = that.module1[1]
                } else {
                    that.info.title = that.module2[0]
                    that.info.content = that.module2[1]
                }
                that.tab = e
            },
            changeTab(e) {
                if (this.status == 'create') {
                    this.current = e
                }
            },

            getDetail(id) {
                let that = this
                getTimingDetail(id).then((res) => {
                    if (res.code == 200) {
                        let data = {}
                        data = JSON.parse(JSON.stringify(res.data.timing_message))
                        if (res.data.timing_message.content_type == 2) {
                            data.image = res.data.timing_message.content
                            data.content = ""
                        }
                        that.info = data
                        that.current = data.content_type
                        that.selThis = data.is_comment_button
                        uni.setStorageSync("timing", res.data.timing_message)
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },

            close() {
                let that = this
                that.show = false
            },
            toNext() {
                let that = this
                if (that.query.source == "add") {
                    let data = {}
                    data.title = that.info.title
                    data.content_type = that.current
                    data.is_comment_button = that.selThis
                    if (that.current == 1) {
                        data.content = that.info.content
                        data.url = ""
                    } else {
                        data.content = that.info.image
                        data.url = that.info.url
                    }
                    uni.setStorageSync("timing", data)
                    uni.redirectTo({
                        url: "../setting/setting?source=add"
                    })
                } else if (that.query.source == "edit") {
                    uni.redirectTo({
                        url: "../setting/setting?source=edit&id=" + that.query.id
                    })
                }
            },
            uploadImage() {
                let that = this
                uni.chooseImage({
                    count: 1,
                    success: (res) => {
                        const tempPaths = res.tempFilePaths
                        uni.uploadFile({
                            url: that.$baseUrl + '/tool/upload/file',
                            filePath: res.tempFilePaths[0],
                            name: "file",
                            success: (result) => {
                                if (result.errMsg == "uploadFile:ok") {
                                    let re = JSON.parse(result.data)
                                    if (re.code == 200) {
                                        that.info.image = re.data.path
                                    }
                                }
                            }
                        })
                    }
                });
            },
        }
    }
</script>

<style lang="scss">
    .reviewContents {
        width: 646rpx;
        height: 88rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32rpx;
        color: #707070;
        background: #FFFFFF;
        border: 2rpx solid #AAAAAA;
        margin: 0 auto;
        margin-top: 40rpx;
    }

    .checkBox {
        width: 32rpx;
        height: 32rpx;
        background: #FFFFFF;
        border: 2rpx solid #AAAAAA;
        box-sizing: border-box;
        padding: 6rpx;
        border-radius: 50%;
        margin-right: 20rpx;

        .inner {
            width: 100%;
            height: 100%;
            background: #1A73E8;
            border-radius: 50%;
        }
    }

    .modeuleBtns {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;

        .mbtn {
            font-size: 32rpx;
            color: #1d1d1f;
            width: 272rpx;
            height: 64rpx;
            background: rgba(255, 255, 255, 0);
            text-align: center;
            border: 2rpx solid #1d1d1f;
            line-height: 64rpx;
            border-radius: 26rpx;
            background: #E8E8E8;
        }
    }

    .uploadImage {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 100%;
        transform: translate(-50%, -50%);

        image {
            height: 100%;
            width: 100%;
        }
    }

    .modualContent {
        height: 90vh !important;
    }

    .mTitle {
        display: flex;
        align-items: center;
        text-align: center;
        box-sizing: border-box;
        padding: 26upx 32upx;
        background: #FBFBFD;
        border-top-left-radius: 60upx;
        border-top-right-radius: 60upx;
        font-size: 40upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        border-bottom: 2upx solid #d2d2d7;

        .mClose {
            width: 60upx;
            height: 60upx;
            border-radius: 50%;
        }

        .mWord {
            flex: 1;
        }
    }

    .mContent {
        width: 100%;
        height: 1000upx;
        background: #fff;

        .mTip {
            width: 686upx;
            height: 52upx;
            background: #707070;
            border-radius: 16upx;
            font-size: 20upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            text-align: center;
            line-height: 52upx;
            margin: 52upx auto 60upx;
        }

        .mDetail {
            display: flex;
            align-items: flex-start;
            box-sizing: border-box;
            padding: 0 38upx;

            .mAvatar {
                width: 60upx;
                height: 60upx;
            }

            .mChat {
                margin-left: 6upx;
                background: rgba(143, 168, 248, 0.2);
                box-sizing: border-box;
                max-width: 80vw;
                font-size: 32upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                border-radius: 20upx;
            }
        }
    }

    .upload {
        width: 100%;
        height: 646upx;
        border: 2upx solid #aaa;
        border-radius: 12upx;
        text-align: center;
        box-sizing: border-box;
        padding-top: 200upx;
        position: relative;

        .upimage {
            width: 126upx;
            height: 120upx;
        }
    }

    .content {
        box-sizing: border-box;
        padding: 60upx 52upx 0;

        .item {
            margin-bottom: 32upx;
        }

        .itemTitle {
            font-size: 28upx;
            color: #707070;
            margin-bottom: 10upx;

            .icon {
                color: #D93025;
            }

            .fu {
                font-size: 24upx;
                color: #aaa;
            }
        }
    }

    .btn {
        width: 480upx !important;
        height: 96upx;
        background: #34C759;
        border-radius: 20upx;
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #FFFFFF;
        line-height: 96upx;
        margin: 60rpx auto 120upx;
    }

    .tabs {
        height: 120upx;
        background-color: #FFFFFF;
        display: flex;
        border-top: 2upx solid #D2D2D7;
        border-bottom: 2upx solid #D2D2D7;

        .active {
            background: rgba(29, 29, 31, 0.1);
            color: #1D1D1F !important;
        }

        .tab {
            display: flex;
            align-items: center;
            flex: 1;
            justify-content: center;
            color: rgba(29, 29, 31, 0.3);

            .tabImage {
                width: 42upx;
                height: 40upx;
                margin-right: 16upx;

                image {
                    width: 100%;
                    height: 100%;
                }
            }
        }
    }
</style>