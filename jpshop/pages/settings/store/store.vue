<template>
    <!-- [カスタムエイド 2026-10-20] デザイン設定の見た目を店舗情報・メンバーズカードと合わせた
         ・色は新しい色えらびパネル(components/csColorPanel)に統一。各項目に、どこの色かの説明と色コードを表示
         ・アイコンは、押すと表示/非表示が切り替わる(選んでいるものは青い枠とチェック)。必須は印で表示
         ・「設定保存」を大きいボタンに、「元に戻す」はその下に
         ・一番上の見出しと一番下の帯は、お客様アプリでの見え方の見本(今までどおり) -->
    <view class="ds">
        <u-navbar :title="shopName" :border="true" :autoBack="true" :safeAreaInsetTop="true" :placeholder="true"
            :bgColor="array[0].color" :leftIconColor="array[1].color" :titleStyle="{'color':array[1].color}">
        </u-navbar>

        <view class="ds-body">
            <view class="ds-seg">
                <view class="ds-seg-item" :class="{ on: current == 0 }" @click.stop="changeTab(0)">色</view>
                <view class="ds-seg-item" :class="{ on: current == 1 }" @click.stop="changeTab(1)">アイコン</view>
            </view>
            <text class="ds-sub ds-lead">上の見出しと下の帯が、お客様アプリでの見え方の見本です。</text>

            <!-- 色 -->
            <view class="ds-card" v-if="current == 0">
                <view class="ds-row" v-for="(item, index) in array" :key="index" hover-class="ds-hover" @click="chooseColor(index)">
                    <view class="ds-row-text">
                        <text class="ds-row-name">{{item.name}}</text>
                        <text class="ds-sub">{{descs[index]}}</text>
                    </view>
                    <view class="ds-color">
                        <text class="ds-hex">{{hexOf(item.color)}}</text>
                        <view class="ds-chip" :style="{ background: item.color }"></view>
                    </view>
                </view>
            </view>

            <!-- アイコン -->
            <view class="ds-card" v-if="current == 1">
                <view class="ds-icons">
                    <view class="ds-icon" v-for="(item, index) in menu" :key="index"
                        :class="{ on: item.checked || item.must, must: item.must }" @click="changeSelect(index)">
                        <view class="ds-icon-img">
                            <view :style="`backgroundImage: url(${svgData1})`" class="icon" v-if="item.name == 'My Card'"></view>
                            <view :style="`backgroundImage: url(${svgData2})`" class="icon" v-if="item.name == '店舗情報'"></view>
                            <view :style="`backgroundImage: url(${svgData3})`" class="icon" v-if="item.name == 'メッセージ'"></view>
                            <view :style="`backgroundImage: url(${svgData4})`" class="icon" v-if="item.name == 'メニュー'"></view>
                            <view :style="`backgroundImage: url(${svgData5})`" class="icon" v-if="item.name == 'お知らせ'"></view>
                            <view :style="`backgroundImage: url(${svgData6})`" class="icon" v-if="item.name == 'クーポン'"></view>
                        </view>
                        <text class="ds-icon-name">{{item.name}}</text>
                        <text class="ds-must" v-if="item.must">必須</text>
                        <view class="ds-check" v-else :class="{ on: item.checked }">
                            <u-icon v-if="item.checked" name="checkmark" color="#FFFFFF" size="11" bold></u-icon>
                        </view>
                    </view>
                </view>
                <text class="ds-sub">押すと、下の帯に表示する/しないが切り替わります。使わない機能のアイコンは外すことをおすすめします。</text>
            </view>

            <view class="ds-save" hover-class="ds-save-hover" @click="setShopInfo">設定保存</view>
            <view class="ds-reset" hover-class="ds-hover" @click="reset">元に戻す(最初の色とアイコンに戻す)</view>
        </view>

        <view style="height: 160upx;"></view>
        <!-- 下の帯の見本 -->
        <view class="menus" :style="`background:${tabBack}`">
            <view class="menusItem" v-for="(item,index) in navArr" :key="index">
                <view class="menuImage">
                    <view :style="`backgroundImage: url(${svgData11})`" class="icon" v-if="item.name == 'My Card'"></view>
                    <view :style="`backgroundImage: url(${svgData12})`" class="icon" v-if="item.name == '店舗情報'"></view>
                    <view :style="`backgroundImage: url(${svgData13})`" class="icon" v-if="item.name == 'メッセージ'"></view>
                    <view :style="`backgroundImage: url(${svgData14})`" class="icon" v-if="item.name == 'メニュー'"></view>
                    <view :style="`backgroundImage: url(${svgData15})`" class="icon" v-if="item.name == 'お知らせ'"></view>
                    <view :style="`backgroundImage: url(${svgData16})`" class="icon" v-if="item.name == 'クーポン'"></view>
                </view>
                <view class="menuName" :style="`color:${iconColor}`" v-text="item.name"></view>
            </view>
        </view>

        <!-- 色えらび(新しいパネル) -->
        <cs-color-panel :show="show1 != 0" :title="show1 ? array[show1 - 1].name + 'の色' : '色を選ぶ'"
            :value="show1 ? hexOf(array[show1 - 1].color) : '#FFFFFF'" @close="show1 = 0" @confirm="confirmColor"></cs-color-panel>
    </view>
</template>

<script>
    import csColorPanel from "@/components/csColorPanel/csColorPanel.vue" // [カスタムエイド 2026-10-20] 新しい色えらび
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
            csColorPanel
        },
        data() {
            return {
                current: 0,
                descs: ['画面の一番上の帯の色', '一番上の帯の文字と矢印の色', '画面の一番下の帯の色', '一番下の帯のアイコンと文字の色'],
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
                if (array[e].must) return // 必須は外せない
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
            // [カスタムエイド 2026-10-20] 元に戻す:下の帯の見本(背景・アイコンの色)も最初の状態に戻るようにした(保存するまでは反映されない)
            reset() {
                let that = this
                that.menu = JSON.parse(JSON.stringify(that.menu1))
                that.array = JSON.parse(JSON.stringify(that.array1))
                that.navArr = JSON.parse(JSON.stringify(that.navArr1)) // 必須の2つ(保存するときは送らない)
                that.iconColor = that.array1[3].color
                that.tabBack = that.array1[2].color
                that.svgData11 = that.changeColor(that.svgData1, that.iconColor)
                that.svgData12 = that.changeColor(that.svgData2, that.iconColor)
                that.svgData13 = that.changeColor(that.svgData3, that.iconColor)
                that.svgData14 = that.changeColor(that.svgData4, that.iconColor)
                that.svgData15 = that.changeColor(that.svgData5, that.iconColor)
                that.svgData16 = that.changeColor(that.svgData6, that.iconColor)
                uni.showToast({ title: '「設定保存」で反映されます', icon: 'none' })
            },
            chooseColor(e) {
                let that = this
                that.show1 = e + 1
            },
            saveTemp(e) {
                this.tempColor = e
            },
            // [カスタムエイド 2026-10-20] 色コードを #RRGGBB で表示する(rgb() で保存されているお店もあるため)
            hexOf(c) {
                c = String(c || '').trim()
                const m = /^#?([0-9a-fA-F]{6})$/.exec(c)
                if (m) return '#' + m[1].toUpperCase()
                const r = /rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/.exec(c)
                if (r) return '#' + [r[1], r[2], r[3]].map((x) => ('0' + (+x).toString(16)).slice(-2)).join('').toUpperCase()
                return c
            },
            confirmColor(e){
                let that = this
                let index = that.show1 * 1 - 1
                that.tempColor = e // [カスタムエイド 2026-10-20] 新しいパネルは選んだ色をそのまま渡す
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
    /* [カスタムエイド 2026-10-20] 店舗情報・メンバーズカードと同じ決まり */
    .ds {
        min-height: 100vh;
        background: #F5F5F7;
        font-family: 'Hiragino Sans', sans-serif;
        color: #1D1D1F;
    }

    .ds-body {
        padding: 32rpx 24rpx 40rpx;
    }

    .ds-seg {
        display: flex;
        padding: 6rpx;
        background: #E9E9EE;
        border-radius: 24rpx;
        margin-bottom: 16rpx;
    }

    .ds-seg-item {
        flex: 1;
        height: 76rpx;
        line-height: 76rpx;
        text-align: center;
        border-radius: 18rpx;
        font-size: 30rpx;
        font-weight: 500;
        color: #515154;
    }

    .ds-seg-item.on {
        background: #FFFFFF;
        color: #1D1D1F;
        font-weight: 700;
        box-shadow: 0 2rpx 6rpx rgba(0, 0, 0, 0.12);
    }

    .ds-sub {
        display: block;
        font-size: 24rpx;
        line-height: 1.6;
        color: #6E6E73;
    }

    .ds-lead {
        padding: 0 8rpx;
        margin-bottom: 24rpx;
    }

    .ds-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 8rpx 32rpx;
        margin-bottom: 40rpx;
    }

    .ds-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 128rpx;
        border-bottom: 1px solid #E5E5EA;
    }

    .ds-row:last-child {
        border-bottom: 0;
    }

    .ds-row-text {
        flex: 1;
        min-width: 0;
    }

    .ds-row-name {
        display: block;
        font-size: 30rpx;
        font-weight: 500;
    }

    .ds-color {
        display: flex;
        align-items: center;
    }

    .ds-hex {
        font-size: 26rpx;
        color: #515154;
        margin-right: 16rpx;
    }

    .ds-chip {
        width: 72rpx;
        height: 72rpx;
        border-radius: 50%;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.12);
    }

    .ds-hover {
        opacity: 0.6;
    }

    .ds-icons {
        display: flex;
        flex-wrap: wrap;
        padding: 24rpx 0 8rpx;
    }

    .ds-icon {
        position: relative;
        width: calc((100% - 40rpx) / 3);
        margin: 0 20rpx 20rpx 0;
        padding: 24rpx 0 20rpx;
        box-sizing: border-box;
        border: 1px solid #E5E5EA;
        border-radius: 24rpx;
        display: flex;
        flex-direction: column;
        align-items: center;
        opacity: 0.55;
    }

    .ds-icon:nth-child(3n) {
        margin-right: 0;
    }

    .ds-icon.on {
        opacity: 1;
        border: 4rpx solid #1A73E8;
        background: #F3F7FF;
        padding: 22rpx 0 18rpx;
    }

    .ds-icon.must {
        border: 1px solid #D2D2D7;
        background: #FFFFFF;
        padding: 24rpx 0 20rpx;
    }

    .ds-icon-img {
        height: 64rpx;
        display: flex;
        align-items: center;
    }

    .ds-icon-name {
        font-size: 22rpx;
        color: #515154;
        margin-top: 8rpx;
    }

    .ds-must {
        position: absolute;
        top: 10rpx;
        right: 10rpx;
        font-size: 20rpx;
        font-weight: 700;
        color: #515154;
        background: #F2F2F5;
        border-radius: 14rpx;
        padding: 2rpx 10rpx;
    }

    .ds-check {
        position: absolute;
        top: 10rpx;
        right: 10rpx;
        width: 36rpx;
        height: 36rpx;
        border-radius: 50%;
        border: 3rpx solid #AEAEB2;
        box-sizing: border-box;
        background: #FFFFFF;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ds-check.on {
        border: 0;
        background: #1A73E8;
    }

    .ds-save {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
        margin-bottom: 8rpx;
    }

    .ds-save-hover {
        opacity: 0.8;
    }

    .ds-reset {
        height: 88rpx;
        line-height: 88rpx;
        text-align: center;
        font-size: 28rpx;
        font-weight: 500;
        color: #C5221F;
    }

    .icon {
        background-size: 100% 100%;
        display: inline-block;
        width: 80rpx;
        height: 60rpx;
    }

    /* 下の帯の見本(今までどおり) */
    .menus {
        display: flex;
        height: 140upx;
        position: fixed;
        bottom: 0;
        width: 100vw;
        background: #fff;
        align-items: center;
        border-top: 2upx solid #D2D2D7;
        padding-bottom: env(safe-area-inset-bottom);

        .menusItem {
            flex: 1;
            text-align: center;
        }
    }

    .menuName {
        font-size: 20upx;
        text-align: center;
    }
</style>
