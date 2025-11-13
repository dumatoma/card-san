<template>
    <view class="contain border_top">
        <u-navbar :placeholder='true' :title="titles" :autoBack="true" :border="true" :safeAreaInsetTop="true">
            <view class="u-nav-slot" slot="right">
                <image src="../../../static/svg/pre.svg" style="width: 60upx;height: 60upx;" @click="show3 = true" mode="">
                </image>
            </view>
        </u-navbar>
        <view class="topImage">
            <view class="bannerItem" v-for="(item,index) in banner">
                <image :src="item" mode="aspectFill"></image>
                <view class="close" @click="del(index)">
                    <image src="../../../static/icons/wclose.png" mode=""></image>
                </view>
            </view>
        </view>
        <view class="addImage" @click="chooseImage">
            <text>+</text>
            <view>画像を選択</view>
        </view>
        <view class="inp">
            <view class="inpTitle">
                <text style="color: #D93025;">※</text>タイトル
            </view>
            <u-input border="bottom" v-model="title" placeholder="タイトルを入力"></u-input>
        </view>
        <view class="inp">
            <view class="inpTitle">
                詳細
            </view>
            <u-textarea height="200" v-model="details"
                placeholder="詳細を入力">
            </u-textarea>
        </view>
        <view class="swit border_top border_bottom" style="margin-top: 100upx;">
            <view class="sName">
                固定メッセージ設定
            </view>
            <view>
                <u-switch v-model="value" activeColor="#34C759"></u-switch>
            </view>
        </view>
        <view class="tip">
            オンにすると、１番上にこのメッセージが固定表示されます。
        </view>
        <!-- <u-button type="primary" text="投稿" class="btn" @click="sub"></u-button> -->
        <view class="btn" @click="sub" v-text="btntext" :style="title == ''?'opacity:0.6' : ''" hover-class="hover"></view>
        <view style="height: 246upx;"></view>
        <u-popup :show="show3" @close="closePop" round="20">
            <view class="popContent">
                <view class="popTitle">
                    <image class="closee" src="../../../static/icons/tclose.png" mode="" @click="show3 = false"></image>
                    <view>プレビュー</view>
                </view>
                <u-swiper :list="banner" height="251" indicator :autoplay="false" indicatorMode="dot"
                    indicatorActiveColor='#000' indicatorInactiveColor="rgba(0,0,0,.3)"></u-swiper>
                <view class="popInfo">
                    <view class="infoTitle">
                        <view class="itLeft">
                            <image src="../../../static/images/avatar.png" mode=""></image>
                        </view>
                        <view class="itRight">
                            <view class="rightTop" v-text="name"></view>
                            <view class="rightBottom">
                                <text>今日</text> <text v-if="value" style="color: #1A73E8;">固定されたメッセージ</text>
                            </view>
                        </view>
                    </view>
                    <view class="titleName" v-text="title"></view>
                    <u-read-more closeText="続きを見る" showHeight="300">
                        <rich-text :nodes="details"></rich-text>
                    </u-read-more>
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import {
        getShopInfo,
        addNotice,
        editNotice
    } from '@/api/index.js'
    export default {
        data() {
            return {
                value: false,
                details: "",
                title: "",
                show: false,
                show1: false,
                show2: false,
                show3: false,
                dateType: 0,
                id: "",
                source: "",
                banner: [],
                name: "",
                titles:"",
                btntext:""
            }
        },
        onNavigationBarButtonTap() {
            this.show3 = true
        },
        created() {
            // uni.setNavigationBarTitle({
            //     title:"お知らせ作成"
            // })
        },
        onLoad(options) {
            let that = this
            let query = options
            that.source = query.source
            if (query.source == "edit") {
                let data = JSON.parse(query.params)
                that.banner = data.images
                that.title = data.title
                that.details = data.content
                that.value = data.is_top == 1 ? true : false
                that.id = data.id
                that.titles = "お知らせ編集"
                that.btntext = '保存'
            }else{
               that.titles = "お知らせ作成" 
               that.btntext = '投稿'
            }
            that.getShopInfo()
        },
        methods: {
            del(index) {
                let that = this
                that.banner.splice(index, 1)
            },

            getShopInfo() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        that.name = res.data.shop_info.shop_name
                    }
                })
            },

            sub() {
                let that = this
                if(that.title != ''){
                   let data = {}
                   data.images = that.banner
                   data.title = that.title
                   data.content = that.details
                   data.is_top = that.value == true ? 1 : 0
                   if (that.source == 'add') {
                       addNotice(data).then((res) => {
                           if (res.code == 200) {
                               uni.showToast({
                                   title: res.message
                               })
                               setTimeout(function() {
                                   uni.navigateBack()
                               }, 1500)
                           } else {
                               uni.showToast({
                                   title: res.message,
                                   icon: "none"
                               })
                           }
                       })
                   } else if (that.source == "edit") {
                       data._method = "put"
                       let id = that.id
                       editNotice(data, id).then((res) => {
                           if (res.code == 200) {
                               uni.showToast({
                                   title: res.message
                               })
                               setTimeout(function() {
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
                }
            },
            chooseImage() {
                let that = this
                uni.chooseImage({
                    count: 9,
                    success: (res) => {
                        const tempPaths = res.tempFilePaths
                        for (let i = 0; i < tempPaths.length; i++) {
                            uni.uploadFile({
                                url: that.$baseUrl + '/tool/upload/file',
                                filePath: res.tempFilePaths[i],
                                name: "file",
                                success: (result) => {
                                    if (result.errMsg == "uploadFile:ok") {
                                        let re = JSON.parse(result.data)
                                        if (re.code == 200) {
                                            that.banner.push(re.data.path)
                                        }
                                    }
                                }
                            })
                        }
                    }
                });
            },
            showPicker() {
                this.show1 = true
            },
            closeDate() {
                this.show = false
            },
            chooseDate(e) {
                this.dateType = e
                this.show = true
            },
            closePicker() {
                this.show1 = false
            },
            showSheet() {
                this.show2 = true
            },
            closeSheet() {
                this.show2 = false
            },
            closePop() {
                this.show3 = false
            },
            load() {
                this.$refs.uReadMore.init();
            }
        }
    }
</script>

<style lang="scss">
    .titleName {
        font-size: 32upx;
        font-family: Hiragino Sans-W6, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        margin: 28upx 0;
    }
    .hover{
        opacity: 0.6 !important;
    }
    .popInfo {
        box-sizing: border-box;
        padding: 56upx 32upx;
    }

    .infoTitle {
        display: flex;
        align-items: center;

        .itLeft {
            width: 62upx;
            height: 62upx;
            margin-right: 32upx;

            image {
                width: 100%;
                height: 100%;
            }
        }

        .itRight {
            .rightTop {
                font-size: 28upx;
                font-family: Hiragino Sans-W6, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }

            .rightBottom {
                text:first-child {
                    font-size: 24upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #6E6E73;
                    margin-right: 40upx;
                }

                text:last-child {
                    font-size: 24upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                }
            }
        }
    }

    .popTitle {
        text-align: center;
        position: relative;
        height: 112upx;
        background: #fff;
        border-radius: 20upx;
        display: flex;
        align-items: center;

        view {
            flex: 1;
            position: relative;
            left: -32upx;
        }

        .closee {
            width: 60upx;
            height: 60upx;
            margin-left: 32upx;
        }
    }

    .tip {
        box-sizing: border-box;
        padding: 10upx 40upx 32upx;
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #AAAAAA;
    }

    .btn {
        width: 646upx !important;
        height: 96upx;
        margin: 100upx auto 0;
        background: #1a73e8;
        color: #fff;
        text-align: center;
        line-height: 96upx;
        border-radius: 18upx;
        font-size: 32upx;
    }

    .select {
        box-sizing: border-box;
        padding: 0 40upx;

        .selectItem {
            width: 400upx;
            height: 80upx;
            background: rgba(249, 249, 249, 0.94);
            border-radius: 20upx;
            border: 2upx solid #AAAAAA;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
            padding: 0 20upx;
        }
    }



    .dateitem {
        box-sizing: border-box;
        padding: 0 40upx;
    }

    .can {
        width: 100%;
        height: 80upx;
        border-radius: 20upx;
        display: flex;
        align-items: center;
        // justify-content: space-between;
        border: 2upx solid #aaa;
        overflow: hidden;
        margin: 10upx 0 60upx;

        .canLeft {
            background: #EAEAEA;
            text-align: center;
            border-right: 2upx solid #aaa;
            height: 100%;
            line-height: 80upx;
            width: 80upx;
            box-sizing: border-box;
            padding-left: 8upx;
        }
    }


    .inp {
        box-sizing: border-box;
        padding: 0 54upx;
        margin-bottom: 80upx;

        .inpTitle {
            margin-bottom: 10upx;
        }
    }

    .swit {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 44upx 36upx;
    }

    .addImage {
        width: 480upx;
        height: 96upx;
        background: #F5F5F7;
        border: 2upx solid #707070;
        text-align: center;
        line-height: 96upx;
        margin: 80upx auto 98upx;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32upx;

        text {
            font-size: 60upx;
            margin-right: 26upx;
            position: relative;
            top: -5upx;
        }
    }

    .topImage {
        box-sizing: border-box;
        padding-top: 40upx;
        display: flex;
        overflow-x: scroll;
        padding-left: 118upx;

        .bannerItem {
            width: 514upx;
            height: 514upx;
            flex-shrink: 0;
            margin-right: 46upx;
            position: relative;

            .close {
                width: 52upx;
                height: 52upx;
                position: absolute;
                right: 14upx;
                top: 14upx;
                z-index: 999;

                image {
                    width: 100%;
                    height: 100%;
                }
            }

            image {
                width: 100%;
                height: 100%;
            }
        }
    }
</style>
