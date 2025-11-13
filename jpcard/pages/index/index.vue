<template>
    <view class="">
        <u-navbar title="My Card" :placeholder="true" :safeAreaInsetTop="true" :border="true" bgColor="#ffffff"
            :height="56">
            <view class="u-nav-slot" slot="left" @click="muLvList()">
                <image src="../../static/image/list.png" mode=""></image>
            </view>
            <view class="u-nav-right" slot="right" @click="collection()">
                <view :style="`backgroundImage: url(${favorite})`" class="icon" @touchstart="beginTouch1"
                    @touchend="endTouch1"></view>
            </view>
        </u-navbar>
        <view>
            <view class="search" style="position: fixed;top: 56;left: 0;z-index: 999 !important;">
                <view class="sear" @click="search()">
                <!--    <u-search placeholder="お店を探す" v-model="keyword" :disabled="true" bgColor=" background: #767680 " :showAction="false">
                    </u-search> -->
                    <u-icon name="search" size="18"></u-icon>
                    <view class="">お店を探す</view>
                </view>
                <view class="codees" @touchstart="beginTouch" @touchend="endTouch">
                    <view :style="`backgroundImage: url(${QR})`" @click="sao" class="icons"></view>
                </view>
            </view>
            <view style="height: 56px;"></view>
            <view class="swiper">
                <view class="sort1" id="sort1">
                    <view class="sort-item" v-for="(item,index) in cardList" :key="index"
                        @longpress="deleShow(index,item.id,item)" @click.stop="toDetail(item.sid,item.id)">
                        <!-- @touchend="ltouchend" @touchstart="ltouchstart" -->
                        <view class="unread" v-text="item.unread_num" v-if="item.unread_num > 0"></view>
                        <image :src="item.image" mode="aspectFill" class="big-img"></image>
                        <view class="title" v-if="!item.check">{{item.shop_name}}</view>
                        <view class="delet" v-if="item.check" @click.stop="show = true">
                            <view style="z-index: 999;">
                                カードを削除
                            </view>
                            <image src="../../static/image/dele.png" mode="" @click.stop="closeShow()" class="de-img">
                            </image>
                        </view>
                    </view>
                </view>
            </view>
            <u-popup :show="show" mode="center" @close="close" @open="open" :safeAreaInsetBottom="false" class="ttp">
                <view>
                    <view class="title">
                        “{{cardname}}”のカードを削除しますか？
                    </view>
                    <view class="des">
                        削除すると、このカードのデータは完全に削除されます。
                    </view>
                    <div class="btn red" @click="deleteItem">カードを削除</div>
                    <div class="btn blue" @click="show = false">キャンセル</div>
                </view>
            </u-popup>
            <u-popup :show="showAle" mode="bottom" :overlay="false">
                <view class="AllContent">
                    <view class="aleTitle">
                        <text>nearby</text>
                        <image @click="closeAle" class="closebtn" src="../../static/image/close.png"></image>
                    </view>
                    <view class="recommand">
                        <view class="aleItem" v-for='item,index in alrArray'
                            @click="toAdd(item.id,item.shop_name,item.sid,item.mid,item.has)">
                            <view class="cateName" v-if="item.shop_cate_name"
                                :style="item.shop_cate_name =='ヘアサロン'? 'background:#D873A2':item.shop_cate_name =='エステ'? 'background:#D873A2': item.shop_cate_name =='ネイル・マツエク'? 'background:#D873A2':item.shop_cate_name =='フィットネス'? 'background:#1A73E8' : item.shop_cate_name =='ヘルスケア'? 'background:#1A73E8':item.shop_cate_name =='グルメ'? 'background:#ED8E20':item.shop_cate_name =='クリニック'? 'background:#71D3D1':item.shop_cate_name =='ライフスタイル'? 'background:#7F00A5':item.shop_cate_name =='ヘアサロン'? 'background:#BFB801':'background:#AAAAAA'"
                                v-text="item.shop_cate_name"></view>
                            <view class="cateName" v-else style="visibility: hidden;">ヘアサロン</view>
                            <view class="aleImage">
                                <image :src="item.image" mode="aspectFill"></image>
                            </view>
                            <view class="shopNameA" v-text="item.shop_name"></view>
                        </view>
                    </view>
                </view>
            </u-popup>
            <u-popup :show="showInfo" round="16">
                <view class="infoShow">
                    <shopInfo :sid="sid" :name="name" @close="closeThis" @toCa="toCa" @toMap="toMap"
                        @makecall="makecall"></shopInfo>
                </view>
            </u-popup>
        </view>
    </view>
</template>
<script>
    import {
        getUserCards,
        refreshSort,
        deleteCard,
        scanCode,
        getNearCards,
        getAllUnread
    } from "@/api/index.js"
    import favorite from '../../static/svgjs/favorite'
    import shopInfo from "../../pagesA/indexInfo/indexInfo.vue"
    import QR from '../../static/svgjs/QR.js'
    export default {
        components: {
            shopInfo
        },
        data() {
            return {
                showInfo: false,
                show: false,
                showAle: false,
                keyword: '',
                cardList: [],
                oldIndex: null, // 初始位置
                newIndex: null, // 目标位置
                page: 1,
                deleteIndex: 0,
                flag: true,
                alrArray: [],
                cardname: "",
                favorite: '"' + favorite + '"',
                favorite1: '"' + favorite + '"',
                QR: '"' + QR + '"',
                QR1: '"' + QR + '"',
                isLongPress: false,
                dindex: 0,
                did: 0,
                tid: 0,
                ccid: 0,
                sid: "",
                name: "",
                longPressTimeout: null // 用于存储延时器的引用
            }
        },
        onLoad() {},
        onShow() {
            this.getList()
            this.getNearby()
            getAllUnread().then((res) => {
                console.log(res)
                if (res.code == 200) {
                    plus.runtime.setBadgeNumber(res.data.unread_num * 1);
                }
            })
        },
        onPullDownRefresh() {
           uni.showLoading({
               title:"loading...",
               mask:true
           }) 
            this.getList()
        },
        onReachBottom() {
            let that = this
        },
        methods: {
            ltouchstart() {
                this.longPressTimeout = setTimeout(() => {
                    this.isLongPress = true;
                    console.log(this.isLongPress)
                }, 200); // 设置500毫秒的长按触发阈值

            },
            ltouchend() {
                clearTimeout(this.longPressTimeout);
                this.longPressTimeout = null;
                setTimeout(() => {
                    this.isLongPress = false;
                }, 500)

            },
            makecall(e) {
                console.log(e)
                uni.makePhoneCall({
                    phoneNumber: e //仅为示例
                });
            },
            closeThis(e) {
                this.showInfo = false
            },
            toCa(e) {
                console.log(e)
                uni.navigateTo({
                    url: "../../pagesA/calender/calender?sid=" + e
                })
            },
            toMap(e) {
                console.log(e)
                uni.navigateTo({
                    url: "../../pagesA/maps/maps?address=〒" + e.zip_code + " " + e.address
                })
            },
            toDetail(id, cid) {
                let that = this
                console.log("按住", that.isLongPress)
                if (that.isLongPress == false) {
                    uni.navigateTo({
                        url: "../../pagesA/shop/shop?id=" + id + "&cid=" + cid
                    })
                } else if (that.islongPress == true) {

                }
            },
            touchend() {
                setTimeout(() => {
                    this.islongPress = false
                }, 200)
            },
            deleShow(i, id, e) {
                let that = this
                console.log(e)
                if (this.isLongPress == true) {
                    that.cardname = e.shop_name
                    let platform = uni.getSystemInfoSync().platform
                    // #ifdef APP-PLUS
                    if (platform == "ios") {
                        let UIImpactFeedbackGenerator = plus.ios.importClass('UIImpactFeedbackGenerator');
                        let impact = new UIImpactFeedbackGenerator();
                        impact.prepare();
                        impact.init(1);
                        impact.impactOccurred();
                    }
                    if (platform == "android") {
                        uni.vibrateShort();
                    }
                    // #endif
                    that.cardList.forEach((item, index) => {
                        console.log(index)
                        if (index == i) {
                            console.log(i)
                            that.$set(that.cardList[index], 'check', true)
                        } else {
                            that.$set(item, 'check', false)
                        }
                    })
                    that.deleteIndex = i
                }
            },
            beginTouch() {
                let that = this
                that.QR = that.changeColor(that.QR1, "#707070");
            },
            endTouch() {
                let that = this
                that.QR = that.changeColor(that.QR1, "#000000");
            },

            beginTouch1() {
                let that = this
                that.favorite = that.changeColor(that.favorite1, "#707070");
            },
            endTouch1() {
                let that = this
                that.favorite = that.changeColor(that.favorite1, "#000000");
            },

            totest() {
                uni.navigateTo({
                    url: "../../pagesA/testsvg/testsvg"
                })
            },
            changeColor(url, color) {
                let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#",
                    "%23")); //转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
                return res;
            },
            toins() {
                uni.navigateTo({
                    url: "../../pagesA/webins/webins"
                })
            },
            toAdd(id, name, sid, mid, has) {
                let that = this
                that.sid = sid
                that.showInfo = true
                that.name = name
                // if (has == 0) {
                //     uni.navigateTo({
                //         url: "../../pagesA/BindCard/BindCard?id=" + id
                //     })
                //     // uni.showModal({
                //     //     title:"ありがとうございます！",
                //     //     content:name+"\nのカードを追加しました",
                //     //     showCancel:false,
                //     //     confirmText:"OK",
                //     //     success(res){
                //     //         if(res.confirm){
                //     //             console.log(id)
                //     //             uni.navigateTo({
                //     //                 url:"../../pagesA/BindCard/BindCard?id="+id
                //     //             })
                //     //         }
                //     //     }
                //     // })
                // } else {
                //     let info = uni.getStorageSync("user")
                //     uni.navigateTo({
                //         url: "../../pagesA/shop/shop?id=" + sid + "&cid=" + mid
                //     })
                // }
                // that.closeAle()
            },
            getNearby() {
                let that = this
                getNearCards().then((res) => {
                    if (res.code == 200) {
                        let token = uni.getStorageSync("token")
                        if (res.data.cards.length > 0 && token) {
                            that.alrArray = res.data.cards
                            setTimeout(() => {
                                that.showAle = true
                            },1000) 
                        }
                    } else if (res.data.code == 401) {
                        uni.showToast({
                            title: res.data.message,
                            icon: "none",
                            duration: 2500
                        })
                        setTimeout(() => {
                            uni.navigateTo({
                                url: "/pages/login/login"
                            })
                        }, 2500)

                    }
                })
            },
            closeAle() {
                let that = this
                that.showAle = false
                uni.setStorageSync('ale', "unaviliable")
            },
            getList() {
                let that = this
                let page = that.page
                let token = uni.getStorageSync("token")
                let uu = uni.getStorageSync("user")
                let uuid = uu.uuid
                uni.request({
                    url: that.$baseUrl + "/api/member/member?page=" + page,
                    header: {
                        "token": token,
                        "uuid": uuid
                    },
                    method: "get",
                    success: function(res) {
                        uni.hideLoading()
                       
                        if (res.data.code == 200) {
                            that.cardList = []
                            setTimeout(() => {
                                 uni.stopPullDownRefresh()
                                that.cardList = res.data.data.members
                            },500)
                            
                        } else if (res.data.code == 401) {
                            uni.showToast({
                                title: res.data.message,
                                icon: "none",
                                duration: 2500
                            })
                            setTimeout(() => {
                                uni.navigateTo({
                                    url: "/pages/login/login"
                                })
                            }, 2500)

                        } else {
                            uni.showToast({
                                title: res.data.message,
                                icon: "none"
                            })
                        }
                    }
                })
            },

            // touchend(){
            //     setTimeout(() => {
            //         this.flag = true  
            //     },200)
            // },

            deleteItem() {
                let that = this
                that.flag = true
                deleteCard(that.cardList[that.deleteIndex * 1].id).then((res) => {
                    that.show = false
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                        that.getList()
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },

            search() {
                uni.navigateTo({
                    url: '../../pagesA/search/search'
                })
            },

            muLvList() {
                uni.navigateTo({
                    url: '../../pagesA/accountInformation/accountInformation'
                })
            },
            sao() {
                let that = this
                uni.scanCode({
                    onlyFromCamera: true,
                    success: function(res) {
                        let result = JSON.parse(res.result)
                        uni.showLoading({
                            title: "読み込み中"
                        })
                        console.log('result', result)
                        if (result.id) {
                            scanCode(result.id).then((res) => {
                                uni.hideLoading()
                                if (res.code == 200) {
                                    console.log("sacncode", res)
                                    if (res.data.member == 0) {
                                        uni.navigateTo({
                                            url: "../../pagesA/BindCard/BindCard?id=" + res
                                                .data.cid + '&sid=' + result.id + "&name=" +
                                                res.data.shop_name
                                        })
                                    } else {
                                        uni.navigateTo({
                                            url: "../../pagesA/shop/shop?id=" + result.id +
                                                "&cid=" + res.data.mid
                                        })
                                    }
                                }
                            })
                        }
                    }
                })
            },
            closeShow() {
                this.cardList.forEach((item, index) => {
                    this.$set(item, 'check', false)
                })
            },

            open() {},
            collection() {
                uni.navigateTo({
                    url: '../../pagesA/Collection/Collection'
                })
            },
            close() {
                this.show = false
            },
            // moveEnd(i) {
            //     this.$set(this.cardList[i], 'check', false)
            // },
            // changeSort(e) {
            //     uni.showLoading({
            //         title: "読み込み中"
            //     })
            //     let data = {}
            //     data["mids"] = e
            //     refreshSort(data).then((res) => {
            //         if (res.code == 200) {
            //             uni.hideLoading()
            //         } else {
            //             uni.showToast({
            //                 title: res.message
            //             })
            //         }
            //     })
            // }
        }
    }
</script>
<script module='sortable' lang="renderjs">
    export default {
        mounted() {
            this.initSortable()
        },
        methods: {
            initSortable() {
                if (typeof window.Sortable === 'function') {
                    this.setSortable()
                } else {
                    const script = document.createElement('script')
                    script.src = 'https://cdn.bootcdn.net/ajax/libs/Sortable/1.15.0/Sortable.min.js'
                    script.onload = this.setSortable.bind(this)
                    document.head.appendChild(script)

                }
            },
            setSortable() {
                let that = this

                let option = {
                    animation: 150,
                    onEnd: (e) => {
                        // 拖拽完成后回调
                        this.$ownerInstance.callMethod('changeSort', sortable.toArray());
                        var itemEl = e.item
                    }
                }
                let sortable = Sortable.create(document.getElementById('sort'), option);
            },
        }
    }
</script>
<style lang="scss" scoped>
    .infoShow {
        height: 90vh;
        width: 100%;
        overflow-y: scroll;
        border-radius: 32rpx 32rpx 0 0;
    }

    .recommand {
        display: flex !important;
        align-items: center;
        width: 100vw;
        overflow-x: scroll;
    }

    .icon {
        width: 70upx;
        height: 70upx;
    }

    .icons {
        width: 70upx;
        height: 70upx;
    }

    .codees {
        background: #fff;
        padding: 6upx;
        border-radius: 10upx;
    }

    .aleItem {
        box-sizing: border-box;
        padding-top: 28upx;
        margin-left: 32upx;
        width: 243upx;
    }

    .cateName {
        padding: 8upx;
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #FFFFFF;
        margin-bottom: 16upx;
        display: inline-block;
        border-radius: 8upx;
    }

    .shopNameA {
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        margin-top: 16upx;
        text-align: center;
        width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .aleImage {
        width: 240upx;
        height: 160upx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .closebtn {
        width: 48upx;
        height: 48upx
    }

    .click-hover {
        background: #707070;
    }

    .aleTitle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 22upx 32upx;
        font-size: 32upx;
        font-family: Hiragino Sans-W6, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        border-bottom: 2upx solid #D2D2D7;
    }

    .AllContent {
        width: 100vw;
        height: 430upx;
        background: rgba(245, 245, 247, .9);
    }

    @keyframes fadenum {
        0% {
            opacity: 0;
        }
    }

    .u-nav-slot {
        image {
            width: 66rpx;
            height: 48rpx;
        }
    }

    .u-nav-right {
        image {
            width: 60rpx;
            height: 60rpx;
        }
    }

    .search {
        width: 100%;
        background: #F5F5F7;
        box-sizing: border-box;
        padding: 30rpx 32rpx;
        display: flex;
        justify-content: space-between;
        align-items: center;
        // position: fixed;
        // top: 170upx;
        // left: 0;
        // z-index: 888 !important;

        .sear {
            width: 564rpx;
            height: 72rpx;
            background: rgba(118, 118, 128, 0.12);
            border-radius: 20rpx;
            position: relative;
            z-index: 99 !important;
            display: flex;
            align-items: center;
            color: rgb(96, 98, 102);
            font-size: 26rpx;
            box-sizing: border-box;
            padding-left: 18rpx;
        }

        image {
            width: 92rpx;
            height: 92rpx;
        }
    }

    .swiper {
        // height: 12000rpx;
        box-sizing: border-box;
        padding-top: 30upx;

        swiper {
            // height: 100%;
        }
    }

    .sort1 {
        display: flex;
        flex-wrap: wrap;
        padding: 0 66rpx;
        margin-top: 28rpx;

        .sort-item:nth-child(2n) {
            margin-right: 0;
        }

        .sort-item {
            margin-right: 58rpx;
            margin-bottom: 54rpx;
            position: relative;

            .unread {
                width: 50upx;
                height: 50upx;
                border-radius: 50%;
                background-color: #D93025;
                text-align: center;
                line-height: 50upx;
                color: #fff;
                position: absolute;
                right: -20upx;
                top: -20upx;
                z-index: 9;
                font-size: 24upx;
            }

            .big-img {
                width: 280rpx;
                height: 170rpx;
                object-fit: cover;
                box-shadow: 0rpx 0rpx 6rpx 2rpx rgba(0, 0, 0, 0.16);
                border-radius: 10upx;
            }

            .title {
                font-weight: 600;
                color: #1D1D1F;
                font-size: 24rpx;
                text-align: center;
            }

            .delet {
                animation: fadenum 1s;
                font-weight: 600;
                margin-top: 16rpx;
                font-size: 24rpx;
                border-radius: 16rpx;
                text-align: center;
                // width: 100%;
                height: 82rpx;
                background: #EAEAEA;
                color: #D93025;
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0 30rpx;

                .de-img {
                    width: 34rpx;
                    height: 34rpx;
                    // background: #EAEAEA;
                }
            }
        }

    }

    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    ::v-deep .ttp .u-popup__content {
        width: 80%;
        border-radius: 28rpx;
        background-color: #fff;
        overflow: hidden;

        .title {
            padding: 40rpx 34rpx 0 34rpx;
            font-size: 32rpx;
            color: #1D1D1F;
            text-align: center;
        }

        .des {
            padding: 0rpx 34rpx 0 34rpx;
            margin-top: 40rpx;
            margin-bottom: 60rpx;
            font-size: 28rpx;
            color: #1D1D1F;
            text-align: center;
        }

        .btn {
            border-top: 1px solid #86858A;
            height: 88rpx;
            line-height: 88rpx;
            text-align: center;
            font-size: 32rpx;
            background-color: #fff;

            &:active {
                opacity: 0.7;
            }
        }

        .red {
            color: #D93025;
        }

        .blue {
            color: #007AFF;
        }
    }
</style>