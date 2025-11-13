<template>
    <view class="contain border_top">
        <view class="title border_bottom">
            <view class="titleLeft">
                <view :class="current == 1?'selectA' : 'select'" @click="changeTab(1)">
                    <view class="fill" v-if="current == 1"></view>
                </view>
            </view>
            <view class="titleRight" :style="current == 1? '' : 'color:#d3d3d3 !important'">
                お知らせ投稿機能
            </view>
        </view>
        <view class="title">
            <view class="titleLeft">
                <view :class="current == 2?'selectA' : 'select' " @click="changeTab(2)">
                    <view class="fill" v-if="current == 2"></view>
                </view>
            </view>
            <view class="titleRight" :style="current == 2? '' : 'color:#d3d3d3 !important'">
                Instagram連携(ビジネスアカウント)
            </view>
        </view>
        <view class="insertContent border_bottom">
            <button @click="toShou">連携する</button>
        </view>
        <view class="tips">
            連携にはInstagram「ビジネスアカウント」が必要です。　連携したアカウントの投稿内容がユーザーアプリの「お知らせ」に表示されます。　Instagramを持っていないユーザーが「お知らせ」を閲覧した場合、Instagramアプリのダウンロード画面に推移します。　アプリダウンロード後は、Instagramの投稿内容がユーザーアプリの「お知らせ」に表示されます。
        </view>
    </view>
</template>
<script>
    import {
        setShopInfo,
        getShopInfo,
        getInsUrl,
        bindIns
    } from "@/api/index.js"
    export default {
        data() {
            return {
                current: 0,
                ins: "",
                twitter: ""
            }
        },
        onShow() {
            let that = this
            // that.getShopInfo()
            plus.globalEvent.addEventListener('plusMessage', function(msg) {
                if (msg.data.args.data.arg != undefined) {

                    let urls = msg.data.args.data.arg.url
                    let codeArr = urls.split("?code=")
                    let code = codeArr[1]
                    let requestData = {}
                    requestData['code'] = code
                    if (urls) {
                        bindIns(requestData).then((res) => {
                            if (res.code == 200) {
                                uni.showToast({
                                    title: res.message,
                                    duration: 3000
                                })
                                // setTimeout(() => {
                                //     that.current = 2
                                // },2000)
                        
                                if (that.current == 1) {
                                    that.current = 2
                                }
                                // plus.globalEvent.removeEventListener('plusMessage');
                            } else {
                                // uni.showToast({
                                //     title:res.message,
                                //     icon:"none",
                                //     duration:3000
                                // })
                            }
                        })
                    }
                } else {


                }
            });
        },
        onLoad() {
            let that = this
            setTimeout(() => {
                if(that.current != 2){
                   that.getShopInfo()
                }else{
                }
            },2000)
        },
        methods: {
            toShou() {
                let that = this
                let data = {}
                data['type'] = "notice"
                data['notice_type'] = 2
                data['instagram'] = that.ins
                data['twitter'] = that.twitter
                setShopInfo(data).then((res) => {
                    if (res.code == 200) {
                       getInsUrl().then((rest) => {
                           if (rest.code == 200) {
                               uni.redirectTo({
                                   url: "../../webview/webview?link=" + encodeURIComponent(rest.data.url)
                               })
                           }
                       })
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },
            getShopInfo() {
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        this.current = this.current == 2?2:res.data.shop_info.notice_type
                        this.ins = res.data.shop_info.instagram
                        this.twitter = res.data.shop_info.twitter
                    }
                    // plus.globalEvent.removeEventListener('plusMessage');
                })
            },
            changeTab(e) {
                this.current = e
            },
            onNavigationBarButtonTap() {
                let that = this
                let data = {}
                data['type'] = "notice"
                data['notice_type'] = that.current
                data['instagram'] = that.ins
                data['twitter'] = that.twitter
                setShopInfo(data).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
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
        }
    }
</script>

<style lang="scss">
    .titleLeft {
        margin-right: 20upx;
    }

    .insertContent {
        box-sizing: border-box;
        padding: 0 52upx 40upx;

        .ititle {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-bottom: 10upx;
        }
    }

    .tips {
        width: 646upx;
        background: #EAEAEA;
        border-radius: 20upx;
        opacity: 1;
        margin: 100upx auto 0;
        box-sizing: border-box;
        padding: 20upx;
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
    }

    .contain {
        box-sizing: border-box;
        padding-top: 60upx;
    }

    .select {
        width: 40upx;
        height: 40upx;
        background: #FFFFFF;
        border-radius: 50%;
        box-sizing: border-box;
        padding: 6upx;
        border: 2upx solid #d3d3d3 !important;

    }

    .selectA {
        width: 40upx;
        height: 40upx;
        background: #FFFFFF;
        border-radius: 50%;
        box-sizing: border-box;
        padding: 6upx;
        border: 2upx solid #1A73E8 !important
    }


    .fill {
        background: #1A73E8;
        border-radius: 50%;
        height: 100%;
        width: 100%;
    }

    .title {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        padding: 44upx 38upx;
    }
</style>