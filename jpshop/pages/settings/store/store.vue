<template>
    <view>
        <u-navbar :title="shopName" :border="true" :autoBack="true" :safeAreaInsetTop="true" :placeholder = "true" :bgColor="array[0].color" :leftIconColor="array[1].color" :titleStyle="{'color':array[1].color}">
        </u-navbar>
        <view class="tab">
            <view class="tabContent">
                <view class="tabItem" :class="current == 0? 'active' : '' " @click.stop="changeTab(0)">
                    色
                </view>
                <view class="tabItem" :class="current == 1? 'active' : '' " @click.stop="changeTab(1)">
                    アイコン
                </view>
            </view>
        </view>
        <view class="tabContents" v-if="current == 0">
            <view class="tabItems" v-for="(item,index) in array">
                <view class="tabLeft" v-text="item.name"></view>
                <view class="tabRight">
                    <view class="colorSqu" @click="chooseColor(index)" :style="`background:${item.color}`"></view>
                </view>
            </view>
        </view>
        <view class="tabContents gray border_top" v-if="current == 1">
            <view class="menuItem" v-for="(item,index) in menu">
                <view class="menuContent">
                    <view class="menuIcon">
                        <!-- <image :src="item.icon"
                            :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${iconColor});`"
                            mode=""></image> -->
                        <view :style="`backgroundImage: url(${svgData1})`" class="icon" v-if="item.name == 'My Card'">
                        </view>
                        <view :style="`backgroundImage: url(${svgData2})`" class="icon" v-if="item.name == '店舗情報'">
                        </view>
                        <view :style="`backgroundImage: url(${svgData3})`" class="icon" v-if="item.name == 'メッセージ'">
                        </view>
                        <view :style="`backgroundImage: url(${svgData4})`" class="icon" v-if="item.name == 'メニュー'">
                        </view>
                        <view :style="`backgroundImage: url(${svgData5})`" class="icon" v-if="item.name == 'お知らせ'">
                        </view>
                        <view :style="`backgroundImage: url(${svgData6})`" class="icon" v-if="item.name == 'クーポン'">
                        </view>
                    </view>
                    <view class="menuName" style="color:#707070" v-text="item.name"></view>
                </view>
                <view class="menuStatus">
                    <view class="selectContents" v-if="!item.must" @click="changeSelect(index)">
                        <view class="unselect" v-if="!item.checked"></view>
                        <view class="selected" v-if="item.checked">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view v-if="item.must">
                        必須
                    </view>
                </view>
            </view>
            <view class="tips">
                フッターに表示するアイコンにチェックを入れて下さい。 使わない機能のアイコンはチェックを外すことをおすすめします。
            </view>
        </view>

        <view class="btns">
            <view class="butn colorRed" hover-class="hover1" @click="reset">
                元に戻す
            </view>
            <view class="butn colorBlue" hover-class="hover2" @click="setShopInfo">
                設定保存
            </view>
        </view>
        <view style="height: 140upx;"></view>
        <view class="menus" :style="`background:${tabBack}`">
            <view class="menusItem" v-for="(item,index) in navArr">
                <view class="menuImage">
                    <!-- <image :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${iconColor});`"
                        :src="item.icon" mode=""></image> -->
                    <view :style="`backgroundImage: url(${svgData11})`" class="icon" v-if="item.name == 'My Card'">
                    </view>
                    <view :style="`backgroundImage: url(${svgData12})`" class="icon" v-if="item.name == '店舗情報'"></view>
                    <view :style="`backgroundImage: url(${svgData13})`" class="icon" v-if="item.name == 'メッセージ'"></view>
                    <view :style="`backgroundImage: url(${svgData14})`" class="icon" v-if="item.name == 'メニュー'"></view>
                    <view :style="`backgroundImage: url(${svgData15})`" class="icon" v-if="item.name == 'お知らせ'"></view>
                    <view :style="`backgroundImage: url(${svgData16})`" class="icon" v-if="item.name == 'クーポン'"></view>

                </view>
                <view class="menuName" :style="`color:${iconColor}`" v-text="item.name"></view>
            </view>
        </view>
        <!-- 选择颜色 -->
        <view v-if="show1 != 0" class="mask" @touchmove.stop.prevent="">
            <view class="maskContent">
                <view class="ptitle">
                    <view @click="show1 = 0">
                        <image src="../../../static/icons/tclose.png" style="width: 50upx;height: 50upx;" mode="">
                        </image>
                    </view>
                    <view class="pName">
                        カラー
                    </view>
                    <view class="saveColor" @click="confirmColor">
                        保存
                    </view>
                </view>
                <colorPicker :source='colorType' @selectColor="saveTemp"></colorPicker>
            </view>
        </view>
    </view>
</template>

<script>
    import colorPicker from "../membershipCard/chooseColor/chooseColor.vue"
    import svg1 from '../../../static/svgjs/s1.js'
    import svg2 from '../../../static/svgjs/s2.js'
    import svg3 from '../../../static/svgjs/s3.js'
    import svg4 from '../../../static/svgjs/s4.js'
    import svg5 from '../../../static/svgjs/s5.js'
    import svg6 from '../../../static/svgjs/s6.js'
    import {
        setShopInfo,
        getShopInfo
    } from "@/api/index.js"
    export default {
        components: {
            colorPicker
        },
        data() {
            return {
                current: 0,
                show1: 0,
                tabBack: "",
                tempColor:"",
                navbak:"",
                colorType: "background",
                iconColor: "#707070",
                svgData1: '"' + svg1 + '"',
                svgData2: '"' + svg2 + '"',
                svgData3: '"' + svg3 + '"',
                svgData4: '"' + svg4 + '"',
                svgData5: '"' + svg5 + '"',
                svgData6: '"' + svg6 + '"',
                svgData11: '"' + svg1 + '"',
                svgData12: '"' + svg2 + '"',
                svgData13: '"' + svg3 + '"',
                svgData14: '"' + svg4 + '"',
                svgData15: '"' + svg5 + '"',
                svgData16: '"' + svg6 + '"',
                shopName:"",
                array1: [{
                        name: "ヘッダー",
                        color: "#FFFFFF",
                        order: 1
                    },
                    {
                        name: "ヘッダーテキスト",
                        color: "#1D1D1F",
                        order: 2
                    },
                    {
                        name: "フッター",
                        color: "#F5F5F7",
                        order: 1
                    },
                    {
                        name: "フッターアイコン",
                        color: "#707070",
                        order: 1
                    }
                ],
                array: [{
                        name: "ヘッダー",
                        color: "#FFFFFF",
                        order: 1
                    },
                    {
                        name: "ヘッダーテキスト",
                        color: "#1D1D1F",
                        order: 2
                    },
                    {
                        name: "フッター",
                        color: "#F5F5F7",
                        order: 1
                    },
                    {
                        name: "フッターアイコン",
                        color: "#707070",
                        order: 1
                    }
                ],
                menu: [{
                        icon: "../../../static/svg/s1.svg",
                        name: "My Card",
                        must: true,
                        checked: true,
                        color: "rgb(112, 112, 112)",
                        send: "my_card"
                    },
                    {
                        icon: "../../../static/svg/s2.svg",
                        name: "店舗情報",
                        must: true,
                        checked: true,
                        color: "rgb(112, 112, 112)",
                        send: "shop_info"
                    },
                    {
                        icon: "../../../static/svg/s3.svg",
                        name: "メッセージ",
                        must: false,
                        checked: false,
                        color: "rgb(112, 112, 112)",
                        send: "message"
                    },
                    {
                        icon: "../../../static/svg/s4.svg",
                        name: "メニュー",
                        must: false,
                        checked: false,
                        color: "rgb(112, 112, 112)",
                        send: "menu"
                    },
                    {
                        icon: "../../../static/svg/s5.svg",
                        name: "お知らせ",
                        must: false,
                        checked: false,
                        color: "rgb(112, 112, 112)",
                        send: "notice"
                    },
                    {
                        icon: "../../../static/svg/s6.svg",
                        name: "クーポン",
                        must: false,
                        checked: false,
                        color: "rgb(112, 112, 112)",
                        send: "coupon"
                    }
                ],
                menu1: [{
                        icon: "../../../static/svg/s1.svg",
                        name: "My Card",
                        must: true,
                        checked: true,
                        color: "rgb(112, 112, 112)",
                        send: "my_card"
                    },
                    {
                        icon: "../../../static/svg/s2.svg",
                        name: "店舗情報",
                        must: true,
                        checked: true,
                        color: "rgb(112, 112, 112)",
                        send: "shop_info"
                    },
                    {
                        icon: "../../../static/svg/s3.svg",
                        name: "メッセージ",
                        must: false,
                        checked: false,
                        color: "rgb(112, 112, 112)",
                        send: "message"
                    },
                    {
                        icon: "../../../static/svg/s4.svg",
                        name: "メニュー",
                        must: false,
                        checked: false,
                        color: "rgb(112, 112, 112)",
                        send: "menu"
                    },
                    {
                        icon: "../../../static/svg/s5.svg",
                        name: "お知らせ",
                        must: false,
                        checked: false,
                        color: "rgb(112, 112, 112)",
                        send: "notice"
                    },
                    {
                        icon: "../../../static/svg/s6.svg",
                        name: "クーポン",
                        must: false,
                        checked: false,
                        color: "rgb(112, 112, 112)",
                        send: "coupon"
                    }
                ],
                navArr: [{
                        icon: "../../../static/svg/s1.svg",
                        name: "My Card",
                        must: true,
                        checked: true,
                        color: "rgb(112, 112, 112)",
                        send: "my_card"
                    },
                    {
                        icon: "../../../static/svg/s2.svg",
                        name: "店舗情報",
                        must: true,
                        checked: true,
                        color: "rgb(112, 112, 112)",
                        send: "shop_info"
                    }
                ],
                navArr1: [{
                        icon: "../../../static/svg/s1.svg",
                        name: "My Card",
                        must: true,
                        checked: false,
                        color: "rgb(112, 112, 112)",
                        send: "my_card"
                    },
                    {
                        icon: "../../../static/svg/s2.svg",
                        name: "店舗情報",
                        must: true,
                        checked: false,
                        color: "rgb(112, 112, 112)",
                        send: "shop_info"
                    }
                ],
            };
        },
        created() {
            let that = this
            that.getDetail()


        },
        methods: {
            changeTab(e) {
                this.current = e
            },
            changeColor(url, color) {
                let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#",
                "%23")); //转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
                return res;
            },
            getDetail() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        that.shopName = res.data.shop_info.shop_name
                        that.array[0].color = res.data.shop_info.page_header_color
                        that.array[1].color = res.data.shop_info.page_header_font_color
                        that.array[2].color = res.data.shop_info.page_footer_color
                        that.array[3].color = res.data.shop_info.page_footer_icon_color
                        that.svgData11 = that.changeColor(that.svgData1, res.data.shop_info.page_footer_icon_color);
                        that.svgData12 = that.changeColor(that.svgData2, res.data.shop_info.page_footer_icon_color);
                        that.svgData13 = that.changeColor(that.svgData3, res.data.shop_info.page_footer_icon_color);
                        that.svgData14 = that.changeColor(that.svgData4, res.data.shop_info.page_footer_icon_color);
                        that.svgData15 = that.changeColor(that.svgData5, res.data.shop_info.page_footer_icon_color);
                        that.svgData16 = that.changeColor(that.svgData6, res.data.shop_info.page_footer_icon_color);
                        that.tabBack = res.data.shop_info.page_footer_color
                        that.iconColor = res.data.shop_info.page_footer_icon_color
                        let tem = []
                        res.data.shop_info.page_footer_icon.forEach((val, index) => {
                            that.menu.forEach((value, idx) => {
                                if (val == value.send) {
                                    value.checked = true
                                    tem.push(value)
                                }
                            })
                        })
                        that.navArr = [...that.navArr1, ...tem]
                    }
                })
            },
            changeSelect(e) {
                let that = this
                let array = that.menu
                if (array[e].checked == true) {
                    array[e].checked = false
                    that.navArr.forEach((val, index) => {
                        if (val.name == array[e].name) {
                            that.navArr.splice(index, 1)
                        }
                    })
                } else {
                    array[e].checked = true
                    that.navArr.push(array[e])
                }
            },
            reset() {
                let that = this
                that.menu = that.menu1
                that.array = that.array1
                that.navArr = that.navArr1
                that.iconColor = "#707070"
            },
            chooseColor(e) {
                let that = this
                that.show1 = e + 1
            },
            saveTemp(e) {
                this.tempColor = e
            },
            confirmColor(e){
                let that = this
                let index = that.show1 * 1 - 1
                that.array[index].color = that.tempColor
                if (index == 3) {
                    that.iconColor = that.tempColor
                    that.svgData11 = that.changeColor(that.svgData1, that.tempColor);
                    that.svgData12 = that.changeColor(that.svgData2, that.tempColor);
                    that.svgData13 = that.changeColor(that.svgData3, that.tempColor);
                    that.svgData14 = that.changeColor(that.svgData4, that.tempColor);
                    that.svgData15 = that.changeColor(that.svgData5, that.tempColor);
                    that.svgData16 = that.changeColor(that.svgData6, that.tempColor);
                }
                if (index == 2) {
                    that.tabBack = that.tempColor
                }
                that.show1 = 0
            },
            setShopInfo() {
                let that = this
                let temp = []
                that.navArr.forEach((val, index) => {
                    if (val.checked == true) {
                        temp.push(val.send)
                    }
                })
                let data = {}
                data.type = "design"
                data.page_header_color = that.array[0].color
                data.page_header_font_color = that.array[1].color
                data.page_footer_color = that.array[2].color
                data.page_footer_icon_color = that.array[3].color
                data.page_footer_icon = temp
                setShopInfo(data).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
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
    .menuContent {
        width: 140upx;
        height: 140upx;
        background: #FFFFFF;
        box-shadow: 0px 0px 20upx 2upx rgba(0, 0, 0, 0.16);
        border-radius: 20upx;
    }

    .icon {
        background-size: 100% 100%;
        display: inline-block;
        width: 80rpx;
        height: 60rpx;
    }

    .maskContent {
        background: #fff;
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100vw;
    }

    .ptitle {
        display: flex;
        padding: 20upx 30upx;
        position: relative;
        border-top-left-radius: 20upx;
        border-top-right-radius: 20upx;
        align-items: center;
        justify-content: space-between;
    }
    
    .saveColor{
        font-size: 30upx;
        color: #1A73E8;
    }

    .mask {
        position: fixed;
        top: 0;
        left: 0;
        background: rgba(0, 0, 0, .8);
        width: 100vw;
        height: 100vh;
        z-index: 10002 !important;
    }

    .menus {
        display: flex;
        height: 140upx;
        position: fixed;
        bottom: 0;
        width: 100vw;
        background: #fff;
        align-items: center;
        border-top: 2upx solid #D2D2D7;

        .menusItem {
            flex: 1;
            text-align: center;
        }
    }

    .menuImage {
        image {
            width: 120upx;
            height: 60upx;
        }
    }

    .menuName {
        font-size: 20upx;
        text-align: center;
    }

    .tab {
        width: 100%;
        height: 210upx;
        background: #fff;
        box-sizing: border-box;
        padding: 64upx 30upx 0;
        border-top: 2upx solid #D2D2D7;
    }

    .menuIcon {
        text-align: center;
        box-sizing: border-box;
        padding: 28upx 0 0 0;

        image {
            height: 62upx;
            width: 120upx;
        }
    }

    .tips {
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        line-height: 42upx;
    }

    .menuStatus {
        text-align: center;
        margin-top: 32upx;
        display: flex;
        justify-content: center;
    }

    .gray {
        background: #F5F5F7;
        box-sizing: border-box;
        padding: 60upx;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .menuItem {
        flex-shrink: 0;
        margin-bottom: 80upx;
    }

    .menuItem:nth-child(2) {
        margin: 0 100upx;
    }
    
    .hover1{
        color: rgba(217, 48, 37,0.6) !important;
    }
    
    .hover2{
         color: rgba(26, 115, 232,0.6) !important;
    }

    .btns {
        display: flex;
        align-items: center;
        width: 100%;
        height: 190upx;

        .butn {
            flex: 1;
            text-align: center;
        }

        .colorRed {
            font-size: 40upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: rgba(217, 48, 37,1);
        }

        .colorBlue {
            font-size: 40upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: rgba(26, 115, 232,1);
        }
    }

    .colorSqu {
        width: 96upx;
        height: 96upx;
        box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);
        border-radius: 50%;
    }

    .tabItem {
        text-align: center;
        line-height: 64upx;
        flex: 1
    }

    .tabItems {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 40upx;
        border-top: 2upx solid #d2d2d7;
    }

    .tabItems:last-child {
        border-bottom: 2upx solid #d2d2d7;
    }

    .tabContent {
        display: flex;
        align-items: center;
        background: #EAEAEA;
        width: 100%;
        border-radius: 16upx;
        height: 72upx;
    }

    .active {
        width: 344upx;
        height: 64upx;
        background: #FFFFFF;
        box-shadow: 0px 6upx 16upx 2upx rgba(0, 0, 0, 0.16);
        border-radius: 16upx;
        opacity: 1;
        border: 2upx solid rgba(255, 255, 255, 0.04);
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
    }
</style>
