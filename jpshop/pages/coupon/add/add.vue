<template>
    <view class="contain border_top">
        <u-navbar :placeholder='true' title="クーポン作成" :autoBack="true" :border="true" :safeAreaInsetTop="true">
            <view class="u-nav-slot" slot="right">
                <image src="../../../static/svg/pre.svg" style="width: 60upx;height: 60upx;" @click="show3 = true" mode="">
                </image>
            </view>
        </u-navbar>
        <view class="topImage">
            <view class="bannerItem" v-for="(item,index) in info.banner">
                <image :src="item" mode="aspectFit"></image>
                <view class="close" @click="del(index)">
                     <image src="../../../static/svg/CloseButton.svg" mode=""></image>
                </view>
            </view>
        </view>
        <view class="addImage" @click="uploadImage">
            <text>+</text>
            <view>画像を選択</view>
        </view>
        <view class="inp">
            <view class="inpTitle">
                <!-- 标题 -->
                <text style="color: #D93025;">※</text>タイトル
            </view>
            <u-input border="bottom" v-model="info.title" placeholder="クーポン名を入力"></u-input>
        </view>
        <view class="inp">
            <view class="inpTitle">
                詳細
            </view>
            <u-textarea height="200"
                placeholder="クーポンの詳細を入力"
                v-model="info.detail">
            </u-textarea>
        </view>
        <view class="inp">
            <view class="inpTitle">
                クーポン番号(英数字、最大10桁)
            </view>
            <u-input border="surround" v-model="info.number" placeholder="任意のクーポン番号を入力" maxlength="10"></u-input>
        </view>
        <view class="swit border_top border_bottom">
            <view class="sName">
                有効期限
            </view>
            <view>
                <u-switch v-model="info.value" activeColor="#34C759"></u-switch>
            </view>
        </view>
        <view class="tip" style="font-size: 24upx;color: #999;">
            オフにすると、有効期限は設定されません
        </view>
        <view class="dateitem" v-if="info.value">
            <view class="dateTitle">
                開始日
            </view>
            <view class="can" @click="chooseDate(1)">
                <view class="canLeft">
                    <image class="calogo" src="../../../static/svg/calogo.svg" mode="aspectFill"></image>
                </view>
                <view style="box-sizing: border-box;padding: 0 20upx;" v-text="info.startDate"></view>
            </view>
        </view>
        <view class="dateitem" v-if="info.value">
            <view class="dateTitle">
                終了日
            </view>
            <view class="can" @click="chooseDate(2)">
                <view class="canLeft">
                    <image class="calogo" src="../../../static/svg/calogo.svg" mode="aspectFill"></image>
                </view>
                <view style="box-sizing: border-box;padding: 0 20upx;" v-text="info.endDate"></view>
            </view>
        </view>
        <view class="swit border_top border_bottom">
            <view class="sName">
                利用回数制限
            </view>
            <view>
                <u-switch v-model="info.value2" activeColor="#34C759" @change="changel"></u-switch>
            </view>
        </view>
        <view class="tip" style="font-size: 24upx;color: #999;">
            オフにすると、利用回数は無制限になります
        </view>
        <view class="select" v-if="info.value2">
            <view class="selectItem" @click="showPicker">
                <text v-text="`${info.count}回`"></text>
                <u-icon name="arrow-down"></u-icon>
            </view>
        </view>
        <view style="height: 50upx;" v-if="info.value2"></view>
        <view class="swit border_top border_bottom">
            <view class="sName">
                バーコード
            </view>
            <view>
                <u-switch v-model="info.value3" activeColor="#34C759" @change="changeb"></u-switch>
            </view>
        </view>
        <view class="tip" style="font-size: 24upx;color: #999;">
            オフにすると、バーコードなしのクーポンになります
        </view>
         <!-- <view style="height: 50upx;" v-if="info.value3"></view> -->
        <view class="inp">
            <view class="inpTitle" v-if="info.value3 == true">
                JANコード：
            </view>
        </view>
        <view class="inp" style="margin-top: 50upx;">
            <view class="inpTitle">
                備考
            </view>
            <u-textarea height="200" v-model="info.remark" placeholder=""></u-textarea>
        </view>
        <view class="btn" @click="nextStep" :style="(info.title == '')?'background: rgba(217, 48, 37, 0.6);' :'' " hover-class="hover">次へ</view>
        <!-- :disabled=" " -->
        <view style="height: 246upx;"></view>
        <u-calendar confirmText="完了" :show="show" :closeOnClickOverlay='true' monthNum="36" :showTitle="false"
            @close="closeDate" @confirm="chooseData" color="#1A73E8"></u-calendar>
        <u-picker :show="show1" keyName="valu" :columns="columns" @confirm="selectCount" @cancel="closePicker" @close="closePicker" :closeOnClickOverlay="true">
        </u-picker>
        <!-- <u-action-sheet :actions="list" :show="show2" @close="closeSheet" closeOnClickAction safeAreaInsetBottom
            closeOnClickOverlay></u-action-sheet> -->
        <!-- 预览弹窗 -->
        <u-popup :show="show3" @close="closePop" round="20">
            <view class="popContent">
                <view class="popTitle">
                    <image class="closee" src="../../../static/icons/tclose.png" mode="" @click="show3 = false"></image>
                    <view>プレビュー</view>
                </view>
                <u-swiper :list="info.banner"height="251" indicator :autoplay="false" indicatorMode="dot"
                    indicatorActiveColor='#000' indicatorInactiveColor="rgba(0,0,0,.3)"></u-swiper>
                <view class="popInfo">
                    <view class="cId">
                        クーポン番号
                    </view>
                    <view class="cNum" v-text="info.number"></view>
                    <view class="cCode" v-if="info.value3">
                        <image src="../../../static/images/ITF.png" mode=""></image>
                    </view>
                    <view class="cNumber" v-if="info.value3">95268723536355</view>
                </view>
                <view style="width: 100%;height: 44upx;border-bottom: 2upx solid #d2d2d7;"></view>
                <view class="pContents">
                    <view class="pcTitle" v-text="info.title"></view>
                    <view class="pcContent" v-text="info.detail"></view>
                </view>
                <view class="pcItem" v-if="info.value">
                    <view class="pcTop ac">
                        有効期限
                    </view>
                    <view>
                        {{info.startDate}} ~ {{info.endDate}}
                    </view>
                </view>
                <view class="pcItem" v-if="info.value2">
                    <view class="pcTop">
                        利用回数
                    </view>
                    <view>
                        残り{{info.count}}回
                    </view>
                </view>
                <view class="pcItem">
                    <view class="pcTop">
                        　備考　
                    </view>
                    <view v-text="info.remark"></view>
                </view>
            </view>
        </u-popup>
        <view class="mask" v-if="alerts" @click="alerts = false">
            <view class="maskcontent">
                クーポン"利用回数制限"機能を使う場合は、必ず"バーコード"の設定が必要になります。
            </view>
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                show: false,
                show1: false,
                show2: false,
                show3: false,
                dateType: 0,
                alerts:false,
                info: {
                    banner: [],
                    title: "",
                    detail: "",
                    number: "",
                    startDate: "",
                    endDate: "",
                    count:"利用回数を選択",
                    hasCode:"",
                    code:'',
                    remark:"",
                    value: false,
                    value2: false,
                    value3: false,
                    sDate:"",
                    eDate:""
                },
                columns: [
                    [{
                        number: "1",
                        valu:"1回"
                    }, {
                        number: "2",
                        valu:"2回"
                    }, {
                        number: "3",
                        valu:"3回"
                    }, {
                        number: "4",
                        valu:"4回"
                    }, {
                        number: "5",
                        valu:"5回"
                    }, {
                        number: "6",
                        valu:"6回"
                    }, {
                        number: "7",
                        valu:"7回"
                    }, {
                        number: "8",
                        valu:"8回"
                    },{
                        number: "9",
                        valu:"9回"
                    },{
                        number: "10",
                        valu:"10回"
                    }
                    ]
                ],
               
                content: `当店で取り扱いのワックスorトリートメントのどちらかをプレゼントいたします。テキス当店で取り扱いのワックスorトリートメントのどちらかをプレゼントいたします。テキス当店で取り扱いのワックスorトリートメントのどちらかをプレゼントいたします。テキス当店で取り扱いのワックスorトリートメントのどちらかをプレゼントいたします。テキス当店で取り扱いのワックスorトリートメントのどちらかをプレゼントいたします。テキス`,
            }
        },
        onNavigationBarButtonTap() {
            this.show3 = true
        },
        created() {
            let that = this
            let date = new Date()
            let fullDate
            let year = date.getFullYear()
            let month = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
            let day = date.getDate() < 10 ? "0" + (date.getDate()) : date.getDate()
            let week = date.getDay() == 1 ? '月' : date.getDay() == 2 ? '火' : date.getDay() == 3 ? '水' : date.getDay() ==
                4 ? '木' : date.getDay() == 5 ? '金' : date.getDay() == 6 ? '土' : '日'
            fullDate = year + "-" + month + '-' + day + "(" + week + ")"
            that.info.sDate = year + "-" + month + '-' + day
            that.info.eDate = year + "-" + month + '-' + day
            that.info.startDate = fullDate
            that.info.endDate = fullDate
        },
        methods: {
            changel(e){
                let that = this
                if(that.info.value3 == false){
                    if(e == true){
                        that.info.value3 = true
                    }
                } 
                // if(that.info.value3 == true){
                //     if(e == false){
                //         that.info.value2 = true
                //     }
                // }
            },
            changeb(e){
                let that = this
                if(that.info.value2 == true){
                    if(e == false){
                        that.info.value3 = true
                        that.alerts = true
                    }
                }
            },
            // 选择图片并上传
            uploadImage() {
                let that = this
                uni.chooseImage({
                    count: 9,
                    success: (res) => {
                        const tempPaths = res.tempFilePaths
                        for (let i = 0; i < tempPaths.length; i++) {
                            uni.uploadFile({
                                url: that.$baseUrl+'/tool/upload/file',
                                filePath: res.tempFilePaths[i],
                                name:"file",
                                success: (result) => {
                                    if (result.errMsg == "uploadFile:ok") {
                                        let re = JSON.parse(result.data)
                                        if (re.code == 200) {
                                            that.info.banner.push(re.data.path)
                                        }
                                    }
                                }
                            })
                        }
                    }
                });
            },
            
            selectCount(e){
                let that = this
                that.info.count = e.value[0].number
                that.show1 = false
            },

            chooseData(e) {
                let that = this
                let date = new Date(e[0])
                let week = date.getDay()
                let wek
                if (week == 1) {
                    wek = "月"
                } else if (week == 2) {
                    wek = "火"
                } else if (week == 3) {
                    wek = "水"
                } else if (week == 4) {
                    wek = "木"
                } else if (week == 5) {
                    wek = "金"
                } else if (week == 6) {
                    wek = "土"
                } else if (week == 0) {
                    wek = "日"
                }

                if (that.dateType == 1) {
                    that.info.startDate = e[0] + "(" + wek + ")"
                    that.info.sDate = e[0]
                } else if (that.dateType == 2) {
                    that.info.endDate = e[0] + "(" + wek + ")"
                    that.info.eDate = e[0]
                }
                that.show = false
            },

            del(index) {
                let that = this
                that.info.banner.splice(index, 1)
            },
            showPicker() {
                let that = this
                if(that.info.value2 == true){
                   this.show1 = true 
                }else{
                    uni.showToast({
                        title:"この機能はオンになっていません",
                        icon:"none"
                    })
                }
                
            },
            closeDate() {
                this.show = false
            },
            chooseDate(e) {
                if (this.info.value == true) {
                    this.dateType = e
                    this.show = true
                } else {
                    uni.showToast({
                        title: "この機能はオンになっていません",
                        icon: "none"
                    })
                }
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
            },
            nextStep() {
                let that = this
                if(that.info.title != ''){
                    uni.setStorageSync("couponInfo",that.info)
                    uni.redirectTo({
                        url: "../../coupon/setting/setting?id=0&source=add"
                    })
                }
            }
        }
    }
</script>

<style lang="scss">
    .calogo{
        width: 38rpx;
        height: 34rpx;
        margin: 0 auto;
    }
    .mask{
        width: 100vw;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, .4);
        z-index: 999 !important;
        
        .maskcontent{
            background-color: #fff;
            width: 540upx;
            height: 192upx;
            background: rgba(249,249,249,0.94);
            border-radius: 28upx;
            font-size: 28upx;
            font-family: Noto Sans Kannada-Regular, Noto Sans Kannada;
            font-weight: 400;
            color: #D93025;
            text-align: center;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
            box-sizing: border-box;
            padding: 40upx;
        }
    }
    .titleName {
        font-size: 32upx;
        font-family: Hiragino Sans-W6, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        margin: 28upx 0;
    }
    .hover{
        background: rgba(217, 48, 37, 0.6);
    }
    .ac {
        color: rgba(26, 115, 232, 1) !important;
        border: 2upx solid rgba(26, 115, 232, 1) !important;
        background: transparent !important;
    }

    .pcItem {
        box-sizing: border-box;
        padding: 40upx 32upx;
        border-bottom: 2upx solid #d2d2d7;

        view:first-child {
            width: 152upx;
            height: 52upx;
            background: #F5F5F7;
            opacity: 1;
            border: 2upx solid #707070;
            font-size: 28upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            border-radius: 20upx;
            text-align: center;
            line-height: 52upx;
        }

        view:last-child {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-top: 20upx;
        }
    }

    .popContent {
        width: 100%;
        height: 90vh;
        overflow-y: scroll;
        position: relative;
    }

    .pContents {
        box-sizing: border-box;
        padding: 40upx 32upx;
        border-bottom: 2upx solid #d2d2d7;

        .pcTitle {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .pcContent {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-top: 40upx;
        }
    }

    .popInfo {
        box-sizing: border-box;
        padding: 56upx 32upx;



        .cId {
            text-align: center;
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-bottom: 20upx;
        }

        .cNum {
            font-size: 68upx;
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
            text-align: center;
        }

        .cCode {
            text-align: center;
            margin-top: 40upx;

            image {
                width: 590upx;
                height: 200upx;
            }
        }

        .cNumber {
            text-align: center;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }
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
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        display: flex;
        align-items: center;
        position: sticky;
        top: 0;
        left: 0;
        z-index: 999 !important;

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
    }

    .btn {
        width: 646upx !important;
        height: 96upx;
        background: rgba(217, 48, 37, 1);
        color: #fff;
        font-size: 32upx;
        margin: 0 auto;
        border-radius: 20upx;
        text-align: center;
        line-height: 96upx;
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
            width: 80upx;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            padding-top: 23rpx;
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
            height: 344upx;
            flex-shrink: 0;
            margin-right: 46upx;
            position: relative;

            .close {
                width: 52upx;
                height: 52upx;
                position: absolute;
                right: 14upx;
                top: 14upx;
                z-index: 9;

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
