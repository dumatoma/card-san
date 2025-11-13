<template>
    <view>
        <cardInfo @changes="changeTas" v-if="current == 'My Card'" :refresh="refresh" :tabs="bottomTabs" :id="query.cid" :sid="query.id"
            :color="shopInfo.page_header_font_color || '#000'" :sp="slideData" :bgColor="shopInfo.page_header_color || '#fff'">
        </cardInfo>
        <shopInfo @changes="changeTas" v-if="current == '店舗情報'" :tabs="bottomTabs" :id="query.cid" :sid="query.id"
            :color="shopInfo.page_header_font_color || '#000'" :sp="slideData" :bgColor="shopInfo.page_header_color || '#fff'">
        </shopInfo>
        <notice @changes="changeTas" v-if="current == 'お知らせ'" :tabs="bottomTabs" :sp="slideData" :id="query.cid" :sid="query.id"
            :shopName="shopInfo.shop_name" :color="shopInfo.page_header_font_color || '#000'"
            :bgColor="shopInfo.page_header_color || '#fff'" @getInsNew="getInsNew"></notice>
        <couponList @changes="changeTas" v-if="current == 'クーポン'" :tabs="bottomTabs" :sp="slideData" :id="query.cid" :sid="query.id"
            :shopName="shopInfo.shop_name" :color="shopInfo.page_header_font_color || '#000'"
            :bgColor="shopInfo.page_header_color || '#fff'"></couponList>
        <menus @changes="changeTas" v-if="current == 'メニュー'" :tabs="bottomTabs" :sp="slideData" :id="query.cid" :sid="query.id"
            :shopName="shopInfo.shop_name" :color="shopInfo.page_header_font_color || '#000'"
            :bgColor="shopInfo.page_header_color || '#fff'"></menus>
        <view :style="`height:${bottom}px`"></view>
        <view class="tabbar"
            :style="`padding-bottom:${bottom}px;background:${shopInfo.page_footer_color == ''?'#f4f4f7':shopInfo.page_footer_color};padding-top:20upx`">
            <view class="tabItem" @click="changeTab(tabs[0].name)">
                <view class="tabIcon" v-if="current != tabs[0].name">
                    <view :style="`backgroundImage: url(${svgData1})`" class="icon"></view>
                </view>
                <view class="tabIcon" v-else>
                    <view :style="`backgroundImage: url(${svgData11})`" class="icon"></view>
                </view>
                <view class="tabName" v-text="tabs[0].name"
                    :style="`color:${current == tabs[0].name?'#1A73E8':shopInfo.page_footer_icon_color}`"></view>
            </view>
            <view class="tabItem" @click="changeTab(tabs[1].name)">
                <view class="tabIcon" v-if="current != tabs[1].name">
                    <view :style="`backgroundImage: url(${svgData2})`" class="icon"></view>
                </view>
                <view class="tabIcon" v-else>
                    <view :style="`backgroundImage: url(${svgData12})`" class="icon"></view>
                </view>
                <view class="tabName" v-text="tabs[1].name"
                    :style="`color:${current == tabs[1].name?'#1A73E8':shopInfo.page_footer_icon_color}`"></view>
            </view>
            <view class="tabItem" @click="changeTab(changable[0].name)" v-if="changable[0].name">
                <view class="tabIcon" v-if="current != changable[0].name">
                    <view :style="`backgroundImage: url(${svgData3})`" class="icon" v-if="changable[0].name == 'メッセージ'">
                        <view class="red" v-if="number > 0" v-text="number"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData4})`" class="icon" v-if="changable[0].name == 'メニュー'">
                    </view>
                    <view :style="`backgroundImage: url(${svgData5})`" class="icon" v-if="changable[0].name == 'お知らせ'">
                        <view class="bluee" v-if="number2 > 0"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData6})`" class="icon" v-if="changable[0].name == 'クーポン'">
                        <view class="blue" v-if="number1 > 0" v-text="number1"></view>
                    </view>
                </view>
                <view class="tabIcon" v-else>
                    <view :style="`backgroundImage: url(${svgData13})`" class="icon" v-if="changable[0].name == 'メッセージ'">
                        <view class="red" v-if="number > 0" v-text="number"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData14})`" class="icon" v-if="changable[0].name == 'メニュー'">
                    </view>
                    <view :style="`backgroundImage: url(${svgData15})`" class="icon" v-if="changable[0].name == 'お知らせ'">
                        <view class="bluee" v-if="number2 > 0" ></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData16})`" class="icon" v-if="changable[0].name == 'クーポン'">
                        <view class="blue" v-if="number1 > 0" v-text="number1"></view>
                    </view>
                </view>
                <view class="tabName" v-text="changable[0].name"
                    :style="`color:${current == changable[0].name?'#1A73E8' : shopInfo.page_footer_icon_color}`"></view>
            </view>
            <view class="tabItem" @click="changeTab(changable[1].name)" v-if="changable[1].name">
                <view class="tabIcon" v-if="current != changable[1].name">
                    <view :style="`backgroundImage: url(${svgData3})`" class="icon" v-if="changable[1].name == 'メッセージ'">
                        <view class="red" v-if="number > 0" v-text="number"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData4})`" class="icon" v-if="changable[1].name == 'メニュー'">
                    </view>
                    <view :style="`backgroundImage: url(${svgData5})`" class="icon" v-if="changable[1].name == 'お知らせ'">
                        <view class="bluee" v-if="number2 > 0"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData6})`" class="icon" v-if="changable[1].name == 'クーポン'">
                        <view class="blue" v-if="number1 > 0" v-text="number1"></view>
                    </view>
                </view>
               <view class="tabIcon" v-else>
                   <view :style="`backgroundImage: url(${svgData13})`" class="icon" v-if="changable[1].name == 'メッセージ'">
                       <view class="red" v-if="number > 0" v-text="number"></view>
                   </view>
                   <view :style="`backgroundImage: url(${svgData14})`" class="icon" v-if="changable[1].name == 'メニュー'">
                   </view>
                   <view :style="`backgroundImage: url(${svgData15})`" class="icon" v-if="changable[1].name == 'お知らせ'">
                       <view class="bluee" v-if="number2 > 0"></view>
                   </view>
                   <view :style="`backgroundImage: url(${svgData16})`" class="icon" v-if="changable[1].name == 'クーポン'">
                       <view class="blue" v-if="number1 > 0" v-text="number1"></view>
                   </view>
               </view>
                <view class="tabName" v-text="changable[1].name" :style="`color:${current == changable[1].name?'#1A73E8' : shopInfo.page_footer_icon_color}`">
                </view>
            </view>
            <view class="tabItem" @click="changeTab(changable[2].name)" v-if="changable[2].name">
                <view class="tabIcon" v-if="current != changable[2].name">
                    <view :style="`backgroundImage: url(${svgData3})`" class="icon" v-if="changable[2].name == 'メッセージ'">
                        <view class="red" v-if="number > 0" v-text="number"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData4})`" class="icon" v-if="changable[2].name == 'メニュー'">
                    </view>
                    <view :style="`backgroundImage: url(${svgData5})`" class="icon" v-if="changable[2].name == 'お知らせ'">
                         <view class="bluee" v-if="number2 > 0"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData6})`" class="icon" v-if="changable[2].name == 'クーポン'">
                        <view class="blue" v-if="number1 > 0" v-text="number1"></view>
                    </view>
                </view>
                <view class="tabIcon" v-else>
                    <view :style="`backgroundImage: url(${svgData13})`" class="icon" v-if="changable[2].name == 'メッセージ'">
                        <view class="red" v-if="number > 0" v-text="number"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData14})`" class="icon" v-if="changable[2].name == 'メニュー'">
                    </view>
                    <view :style="`backgroundImage: url(${svgData15})`" class="icon" v-if="changable[2].name == 'お知らせ'">
                         <view class="bluee" v-if="number2 > 0"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData16})`" class="icon" v-if="changable[2].name == 'クーポン'">
                        <view class="blue" v-if="number1 > 0" v-text="number1"></view>
                    </view>
                </view>
                <view class="tabName" v-text="changable[2].name" :style="`color:${current == changable[2].name?'#1A73E8' : shopInfo.page_footer_icon_color}`">
                </view>
            </view>
            <view class="tabItem" @click="changeTab(changable[3].name)" v-if="changable[3].name">
                <view class="tabIcon" v-if="current != changable[3].name">
                    <view :style="`backgroundImage: url(${svgData3})`" class="icon" v-if="changable[3].name == 'メッセージ'">
                        <view class="red" v-if="number > 0" v-text="number"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData4})`" class="icon" v-if="changable[3].name == 'メニュー'">
                    </view>
                    <view :style="`backgroundImage: url(${svgData5})`" class="icon" v-if="changable[3].name == 'お知らせ'">
                         <view class="bluee" v-if="number2 > 0"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData6})`" class="icon" v-if="changable[3].name == 'クーポン'">
                        <view class="blue" v-if="number1 > 0" v-text="number1"></view>
                    </view>
                </view>
                <view class="tabIcon" v-else>
                    <view :style="`backgroundImage: url(${svgData13})`" class="icon" v-if="changable[3].name == 'メッセージ'">
                        <view class="red" v-if="number > 0" v-text="number"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData14})`" class="icon" v-if="changable[3].name == 'メニュー'">
                    </view>
                    <view :style="`backgroundImage: url(${svgData15})`" class="icon" v-if="changable[3].name == 'お知らせ'">
                         <view class="bluee" v-if="number2 > 0"></view>
                    </view>
                    <view :style="`backgroundImage: url(${svgData16})`" class="icon" v-if="changable[3].name == 'クーポン'">
                        <view class="blue" v-if="number1 > 0" v-text="number1"></view>
                    </view>
                </view>
                <view class="tabName" v-text="changable[3].name" :style="`color:${current == changable[3].name?'#1A73E8' : shopInfo.page_footer_icon_color}`">
                </view>
            </view>
        </view>
    </view>
</template>
<script>
    import {
        getShopInfo,
        getUnreadNum,
        getMewCoupon,
        getNewNotice,
        getInsList
    } from "@/api/index.js"
    import sp from "@/components/swiper-direct-com.vue"
    import cardInfo from "../shopDetails/shopDetails.vue"
    import shopInfo from "../storeInformation/storeInformation.vue"
    import notice from "../notification/notification.vue"
    import couponList from "../couponList/couponList.vue"
    import menus from "../menu/menu.vue"
    import svg1 from '../../static/svgjs/s1.js'
    import svg2 from '../../static/svgjs/s2.js'
    import svg3 from '../../static/svgjs/s3.js'
    import svg4 from '../../static/svgjs/s4.js'
    import svg5 from '../../static/svgjs/s5.js'
    import svg6 from '../../static/svgjs/s6.js'
    export default {
        components: {
            cardInfo,
            shopInfo,
            notice,
            couponList,
            menus,
            sp
        },
        data() {
            return {
                refresh:false,
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
                titleName: "页面1",
                slideData:false,
                bgcolor: "#fff",
                color: "red",
                bottom: 50,
                current: "My Card",
                query: {},
                shopInfo: {},
                tabs: [{
                        icon: "../../static/svg/s1.svg",
                        name: "My Card"
                    },
                    {
                        icon: "../../static/svg/s2.svg",
                        name: "店舗情報"
                    }
                ],
                changable: [{
                    name: ""
                }, {
                    name: ""
                }, {
                    name: ""
                }, {
                    name: ""
                }],
                bottomTabs:[],
                number:0,
                number1:0,
                number2:0
            };
        },
        created() {
            let that = this
            
        },
        onShow() {
            let that = this 
            
        },
        onPullDownRefresh() {
            if(this.current == 'My Card'){
                this.refresh = true
            }
            
            setTimeout(() => {
                uni.stopPullDownRefresh()
                this.refresh = false
            },3000)
        },
        onLoad(options) {
            let that = this 
            if(options.source == 'push'){
                that.current = "クーポン"
            }else{
                that.current = 'My Card'
            }
            console.log("options",options)
            let query = options
            
            that.query = query
            setTimeout(() => {
                 that.getInfo()
            },500)
            getUnreadNum(query.id).then((res) => {
               
                if(res.code == 200){
                    if(res.data.unread_num < 100){
                        that.number = res.data.unread_num
                    }else{
                        that.number = "99+"
                    }
                    
                }
            })
            getMewCoupon(query.id).then((res) => {
                if(res.code == 200){
                    that.number1 = res.data.count
                }
            })  
        },
        onPageScroll(res) {
            // uni.$emit('onPageScroll', res.scrollTop);//传递参数
        },
        onReachBottom() {
            uni.$emit('onReachBottom');
        },
        methods: {
            changeColor(url, color) {
                let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#",
                    "%23")); //转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
                return res;
            },
            getInsNew(e){
                that.number2 = 0
            },
            getInfo() {
                let that = this
                this.bottom = 25
                // 获取店铺信息
                getShopInfo(that.query.id).then((res) => {
                    console.log("info",res)
                    if (res.code == 200) {
                        let temp = []
                        let notice = {
                            icon: "../../static/svg/s5.svg",
                            name: "お知らせ",
                        }
                        let menu = {
                            icon: "../../static/svg/s4.svg",
                            name: "メニュー"
                        }
                        let message = {
                            icon: "../../static/svg/s3.svg",
                            name: "メッセージ"
                        }
                        let coupon = {
                            icon: "../../static/svg/s6.svg",
                            name: "クーポン"
                        }
                        that.shopInfo = res.data.shop
                        // that.mid = res.data.
                        res.data.shop.page_footer_icon.forEach((val, index) => {
                            if (val == 'notice') {
                                temp.push(notice)
                            } else if (val == 'menu') {
                                temp.push(menu)
                            } else if (val == 'message') {
                                temp.push(message)
                            } else if (val == 'coupon') {
                                temp.push(coupon)
                            }
                        })
                        let ti = {}
                        ti['name'] = ''
                        that.changable = temp
                        let end = (4 - temp.length)
                        for (let i = 0; i < end; i++) {
                            that.changable.push(ti)
                        }
                        if(that.query.source == 'push'){
                            that.current = "クーポン"
                        }else{
                             that.current = 'My Card'
                        }
                       
                        let page_footer_icon_color = res.data.shop.page_footer_icon_color || "#000"
                        this.svgData1 = this.changeColor(this.svgData1, page_footer_icon_color);
                        this.svgData2 = this.changeColor(this.svgData2, page_footer_icon_color);
                        this.svgData3 = this.changeColor(this.svgData3, page_footer_icon_color);
                        this.svgData4 = this.changeColor(this.svgData4, page_footer_icon_color);
                        this.svgData5 = this.changeColor(this.svgData5, page_footer_icon_color);
                        this.svgData6 = this.changeColor(this.svgData6, page_footer_icon_color);
                        this.svgData11 = this.changeColor(this.svgData11, "#1A73E8");
                        this.svgData12 = this.changeColor(this.svgData12, "#1A73E8");
                        this.svgData13 = this.changeColor(this.svgData13, "#1A73E8");
                        this.svgData14 = this.changeColor(this.svgData14, "#1A73E8");
                        this.svgData15 = this.changeColor(this.svgData15, "#1A73E8");
                        this.svgData16 = this.changeColor(this.svgData16, "#1A73E8");
                        that.bottomTabs = [...that.tabs,...that.changable]
                        if(res.data.shop.notice_type == 2){
                            getInsList(that.query.id).then((result) => {
                                if(result.code == 200){
                                    let number = uni.getStorageSync("inslist")
                                    if(number && number < result.data.medias.length){
                                        that.number2 = result.data.medias.length - number * 1
                                    }else{
                                        uni.setStorageSync("inslist",result.data.medias.length)
                                    }
                                }
                            })
                        }else{
                            getNewNotice(that.query.id).then((res) => {
                                if(res.code == 200){
                                    that.number2 = res.data.count
                                }
                            })
                        }   
                    }
                })
            },
            changeTas(e) {
                let that = this
                let obj = {}
                obj.name = e
                that.changeTab(e)
                if(e == 'クーポン'){
                    that.number1 = 0
                }
                
                if(e == 'メッセージ'){
                    that.number = 0
                }
                
                if(e == 'お知らせ'){
                   that.number2 = 0 
                }
            },
            changeTab(item) {
                let that = this
                console.log("tab",item)
                if(item == 'メニュー'){
                    console.log(that.shopInfo)
                    let ar = that.shopInfo.out_url.split("!@")
                    if(ar[1] == 1){
                        uni.navigateTo({
                            url:"/pagesA/webins/webins?link="+ar[0]
                        })
                        return;
                    }
                    this.svgData1 = this.changeColor(this.svgData1, that.shopInfo.page_footer_icon_color);
                    this.svgData2 = this.changeColor(this.svgData2, that.shopInfo.page_footer_icon_color);
                }
                if(item == 'お知らせ'){
                    that.number2 = 0
                    this.svgData1 = this.changeColor(this.svgData1, that.shopInfo.page_footer_icon_color);
                    this.svgData2 = this.changeColor(this.svgData2, that.shopInfo.page_footer_icon_color);
                }
                if(item == 'クーポン'){
                    that.number1 = 0
                    this.svgData1 = this.changeColor(this.svgData1, that.shopInfo.page_footer_icon_color);
                    this.svgData2 = this.changeColor(this.svgData2, that.shopInfo.page_footer_icon_color);
                }
                if (item == 'メッセージ') {
                    this.svgData1 = this.changeColor(this.svgData1, that.shopInfo.page_footer_icon_color);
                    this.svgData2 = this.changeColor(this.svgData2, that.shopInfo.page_footer_icon_color);
                    that.number = 0
                    let name = encodeURIComponent(that.shopInfo.shop_name)
                    uni.navigateTo({
                        url: "../message/chat?sid=" + that.query.id + "&name=" + name
                    })
                } else {
                    that.current = item
                    if (item == that.tabs[0].name) {
                        this.svgData1 = this.changeColor(this.svgData1, "#1A73E8");
                        this.svgData2 = this.changeColor(this.svgData2, that.shopInfo.page_footer_icon_color);
                        this.svgData3 = this.changeColor(this.svgData3, that.shopInfo.page_footer_icon_color);
                        this.svgData4 = this.changeColor(this.svgData4, that.shopInfo.page_footer_icon_color);
                        this.svgData5 = this.changeColor(this.svgData5, that.shopInfo.page_footer_icon_color);
                        this.svgData6 = this.changeColor(this.svgData6, that.shopInfo.page_footer_icon_color);
                    }

                    if (item == that.tabs[1].name) {
                        this.svgData1 = this.changeColor(this.svgData1, that.shopInfo.page_footer_icon_color);
                        this.svgData2 = this.changeColor(this.svgData2, "#1A73E8");
                        this.svgData3 = this.changeColor(this.svgData3, that.shopInfo.page_footer_icon_color);
                        this.svgData4 = this.changeColor(this.svgData4, that.shopInfo.page_footer_icon_color);
                        this.svgData5 = this.changeColor(this.svgData5, that.shopInfo.page_footer_icon_color);
                        this.svgData6 = this.changeColor(this.svgData6, that.shopInfo.page_footer_icon_color);
                    }
                }
            }
        }
    }
</script>

<style lang="scss">
    page {
        background: #fff;
    }
    
    .blue{
        width: 40upx !important;
        height: 40upx !important;
        position: absolute !important;
        background: #d93025;
        border-radius: 50%;
        right: -12upx !important;
        top: -12upx !important;
        color: #fff;
        font-size: 20upx;
        text-align: center;
        line-height: 40upx;
    }
    
    .bluee{
        width: 30upx !important;
        height: 30upx !important;
        position: absolute !important;
        background: #1A73E8 !important;
        border-radius: 50%;
        right: 5upx !important;
        top: -12upx !important;
    }
    
    .red{
        width: 40upx !important;
        height: 40upx !important;
        position: absolute !important;
        background: #D93025;
        border-radius: 50%;
        right: -12upx !important;
        top: -12upx !important;
        text-align: center;
        line-height: 40upx;
        color: #fff;
        font-size: 20upx;
    }

    .icon {
        background-size: 100% 100%;
        display: inline-block;
        width: 80rpx;
        height: 60rpx;
        position: relative;
    }

    .tabbar {
        display: flex;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100vw;
        z-index: 999;
        justify-content: space-between;

        .tabItem {
            flex: 1;
            position: relative;
            .tabIcon {
                width: 100%;
                height: 50upx;
                margin: 0 auto;
                text-align: center;

                view {
                    width: 80upx;
                    height: 100%;
                    position: relative;
                }
            }

            .tabName {
                font-size: 22upx;
                text-align: center;
            }
        }
    }
</style>
