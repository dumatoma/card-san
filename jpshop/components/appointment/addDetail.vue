<template>
    <view class="contain">
        <u-navbar title="予約詳細設定" border @leftClick="handleBack" @rightClick="save" rightText="保存"
            :safeAreaInsetTop="true" bgColor="rgb(251,251,253)" :placeholder="true">
        </u-navbar>
        <!-- [カスタムエイド 2026-10-22] 予約詳細設定をほかの画面と同じ見た目に。担当者の名前を上に、切り替えは薄い青+青い線、メニューはメニュー一覧と同じカード、
             色は丸の見本から選ぶ。画面の下にも「保存する」ボタン(右上の保存も残す)。処理・送る値は変更なし -->
        <view class="ad-head">
            <view class="ad-dot" :style="{ background: currentColor }"></view>
            <view class="ad-name">{{ info.name }}</view>
        </view>
        <view class="ad-seg">
            <view class="ad-seg-i" :class="{ on: status == 0 }" @click="changeTab(0)">対応メニュー</view>
            <view class="ad-seg-i" :class="{ on: status == 1 }" @click="changeTab(1)">その他の設定</view>
        </view>

        <view class="ad-body" v-if="status == 0">
            <view class="ad-card">
                <view class="ad-row" @click="choosePerson">
                    <view class="selectContent">
                        <view class="unselect" v-if="!selectAll"></view>
                        <view class="selected" v-if="selectAll"><view class="fill"></view></view>
                    </view>
                    <view class="ad-row-t">すべてのメニューに対応</view>
                </view>
            </view>
            <view v-for="(item,index) in array" :key="index" v-if="item.items.length">
                <view class="ad-sec">{{ item.name }}<text class="ad-sec-n">{{ item.items.length }}件</text></view>
                <view class="ad-menu" v-for="(ite,idx) in item.items" :key="ite.id" :class="{ off: !ite.checked }" @click="chooseItem(index,idx)">
                    <view class="selectContent">
                        <view class="unselect" v-if="!ite.checked"></view>
                        <view class="selected" v-if="ite.checked"><view class="fill"></view></view>
                    </view>
                    <view class="ad-menu-m">
                        <view class="ad-menu-t">{{ ite.name }}</view>
                        <view class="ad-menu-s"><text class="ad-price" v-if="ite.price">{{ ite.price }}</text><text>所要時間 {{ ite.time }}分</text></view>
                    </view>
                    <image class="ad-photo" v-if="ite.image && ite.image.length" :src="ite.image[0]" mode="aspectFill"></image>
                </view>
            </view>
            <view class="ad-save" hover-class="ad-hover" @click="save">保存する</view>
        </view>

        <view class="ad-body" v-if="status == 1">
            <view class="ad-sec">対応するお客さま</view>
            <view class="ad-card">
                <view class="ad-row" @click="chooseSexAll">
                    <view class="selectContent">
                        <view class="unselect" v-if="!sexAll"></view>
                        <view class="selected" v-if="sexAll"><view class="fill"></view></view>
                    </view>
                    <view class="ad-row-t">すべてのジェンダーに対応</view>
                </view>
                <view class="ad-row ad-sub" v-for="(item,index) in sexItems" :key="index" @click="chooseSex(index)">
                    <view class="selectContent">
                        <view class="unselect" v-if="!item.checked"></view>
                        <view class="selected" v-if="item.checked"><view class="fill"></view></view>
                    </view>
                    <view class="ad-row-t">{{ item.name }}</view>
                </view>
            </view>

            <view class="ad-sec">指名料金</view>
            <view class="ad-card">
                <view class="ad-row" :class="{ 'no-line': priceSelect }" @click="choosePrice">
                    <view class="selectContent">
                        <view class="unselect" v-if="!priceSelect"></view>
                        <view class="selected" v-if="priceSelect"><view class="fill"></view></view>
                    </view>
                    <view class="ad-row-t">指名料金を設定する</view>
                </view>
                <view class="ad-price-in" v-if="priceSelect">
                    <input class="ad-in" type="number" v-model="price" placeholder="0" placeholder-class="ad-ph" @blur="normalizePrice" />
                    <text class="ad-yen">円</text>
                    <text class="ad-half">半角数字で入力</text>
                </view>
            </view>

            <view class="ad-sec">予約表の色</view>
            <view class="ad-card ad-pad">
                <view class="ad-colors">
                    <view class="ad-color" v-for="(item,index) in color" :key="index" :class="{ dis: !item.aviliable }" @click="chooseColor(index,item)">
                        <view class="ad-sw" :class="{ on: item.checked }" :style="{ background: item.color }">
                            <text class="ad-check" v-if="item.checked">✓</text>
                        </view>
                        <view class="ad-cname">{{ item.name }}</view>
                    </view>
                </view>
            </view>
            <view class="ad-save" hover-class="ad-hover" @click="save">保存する</view>
        </view>
    </view>
</template>
<script>
    import {
        menuCateList,
        menuList,
        setStaffAppointInfo,
        getAdminDetail
    } from '@/api/index.js'
    export default {
        props: {
            info: {
                type: Object,
                default: {}
            },
        },
        data() {
            return {
                status: 0,
                selectAll: false,
                sexAll: false,
                priceSelect: false,
                currentColor: "#175491",
                currentColorName: "コバルト",
                show: false,
                price: 0,
                color: [{
                        color: "#175491",
                        name: "コバルト",
                        aviliable: true,
                        checked: true
                    },
                    {
                        color: "#5F28B7",
                        name: "パープル",
                        aviliable: true,
                        checked: false
                    },
                    {
                        color: "#00A5A5",
                        name: "セージ",
                        aviliable: true,
                        checked: false
                    },
                    {
                        color: "#C4A917",
                        name: "マスタード",
                        aviliable: true,
                        checked: false
                    },
                    {
                        color: "#007AFF",
                        name: "マリン",
                        aviliable: true,
                        checked: false
                    },
                    {
                        color: "#ED8E20",
                        name: "オレンジ",
                        aviliable: true,
                        checked: false
                    },
                    {
                        color: "#1D1D1F",
                        name: "ブラック",
                        aviliable: true,
                        checked: false
                    },
                    {
                        color: "#1F8037",
                        name: "グリーン",
                        aviliable: true,
                        checked: false
                    },
                    {
                        color: "#EA459E",
                        name: "ピンク",
                        aviliable: true,
                        checked: false
                    },
                    {
                        color: "#7B201B",
                        name: "ボルドー",
                        aviliable: true,
                        checked: false
                    }
                ],
                sexItems: [{
                        name: "男性",
                        checked: false
                    },
                    {
                        name: "女性",
                        checked: false
                    },
                    {
                        name: "Xジェンダー",
                        checked: false
                    }
                ],
                array: []
            };
        },
        created() {
            this.getCate()
            // this.priceSelect = this.info.is_appoint == 1 ? true : false
        },
        methods: {
            // 转半角输入的方法
            normalizePrice() {
                let val = String(this.price == null ? '' : this.price);

                // 全角数字 → 半角
                val = val.replace(/[０-９]/g, s => String.fromCharCode(s.charCodeAt(0) - 0xFEE0));

                // カンマや全角記号などを削除
                val = val.replace(/[,，]/g, '');

                // 数字以外を削除
                val = val.replace(/[^0-9]/g, '');

                this.price = val;
            },

            changeTab(e) {
                this.status = e
            },
            choosePerson() {
                let that = this
                that.selectAll = !that.selectAll
                that.array.forEach((val, index) => {
                    val.items.forEach((value, idx) => {
                        value.checked = that.selectAll
                    })
                })
            },
            handleBack() {
                this.$emit("closeDetail", true)
            },
            getStaffDetail() {
                let that = this
                getAdminDetail(that.info.id).then((res) => {
                    if (res.code == 200) {
                        that.price = res.data.admin.appoint_price
                        that.currentColor = res.data.admin.color
                        that.color.forEach((val, index) => {
                            if (val.color == res.data.admin.color) {
                                that.currentColorName = val.name
                                val.checked = true
                            } else {
                                val.checked = false
                            }
                        })
                        let sarray = res.data.admin.smids.split(",")
                        let sexArray = res.data.admin.sex.split(",")
                        if (sexArray.indexOf("1") != -1) {
                            that.sexItems[0].checked = true
                        }
                        if (sexArray.indexOf("2") != -1) {
                            that.sexItems[1].checked = true
                        }
                        if (sexArray.indexOf("3") != -1) {
                            that.sexItems[2].checked = true
                        }
                        that.array.forEach((value, index) => {
                            value.items.forEach((val, idx) => {
                                if (sarray.indexOf(val.id + "") != -1) {
                                    val.checked = true
                                } else {
                                    val.checked = false
                                }
                            })
                        })
                        that.priceSelect = res.data.admin.is_appoint_price == 1?true:false
                        that.checkSelectAll()
                        that.checkeSexAll()
                    }
                })
            },

            choosePrice() {
                this.priceSelect = !this.priceSelect
                this.price = 0
            },

            chooseItem(index, idx) {
                this.array[index].items[idx].checked = !this.array[index].items[idx].checked
                this.checkSelectAll()
            },

            chooseSexAll() {
                let that = this
                that.sexAll = !that.sexAll
                that.sexItems.forEach((val, index) => {
                    val.checked = that.sexAll
                })
            },

            // [カスタムエイド 2026-10-22] 以前は途中でエラーを投げて止めていた(画面の処理が途中で止まることがあった)ため、every で判定する
            checkeSexAll() {
                this.sexAll = this.sexItems.every((v) => v.checked)
            },

            checkSelectAll() {
                let all = []
                this.array.forEach((v) => { all = all.concat(v.items) })
                this.selectAll = all.length > 0 && all.every((v) => v.checked)
                this.checkeSexAll()
            },

            getCate() {
                let that = this
                menuCateList().then((res) => {
                    if (res.code == 200) {
                        menuList().then((result) => {
                            if (result.code == 200) {
                                let temp = []
                                res.data.cates.forEach((value, index) => {
                                    let obj = {}
                                    obj.name = value.name
                                    obj.items = []
                                    result.data.menus.forEach((val, idx) => {
                                        if (val.cid == value.id) {
                                            let object = {}
                                            object.name = val.title
                                            object.image = val.image
                                            object.price = val.price
                                            object.time = val.order_time
                                            object.time = val.order_time
                                            object.checked = false
                                            object.id = val.id
                                            object.cid = val.cid
                                            obj.items.push(object)
                                        }
                                    })
                                    temp.push(obj)
                                })
                                that.array = temp
                                that.getStaffDetail()
                                that.checkeSexAll()
                            }
                        })
                    }
                })
            },
            chooseSex(e) {
                this.sexItems[e].checked = !this.sexItems[e].checked
                this.checkeSexAll()
            },
            chooseColor(index, ite) {
                let that = this
                let array = that.color
                if (ite.aviliable == true) {
                    array.forEach((value, index) => {
                        value.checked = false
                    })
                    array[index].checked = true
                    that.currentColor = array[index].color
                    that.currentColorName = array[index].name
                }
            },

            save() {
                let that = this
                let ids = []
                let sex = []
                let idStr = ""
                let sexStr = ""
                let data = {}
                data.appoint_price = that.price
                data.color = that.currentColor
                that.array.forEach((val, index) => {
                    val.items.forEach((value, idx) => {
                        if (value.checked == true) {
                            ids.push(value.id)
                        }
                    })
                })
                that.sexItems.forEach((val, index) => {
                    if (val.checked == true) {
                        sex.push(index + 1)
                    }
                })

                for (let i = 0; i < sex.length; i++) {
                    if (i == sex.length - 1) {
                        sexStr += sex[i]
                    } else {
                        sexStr += sex[i] + ","
                    }
                }

                for (let i = 0; i < ids.length; i++) {
                    if (i == ids.length - 1) {
                        idStr += ids[i]
                    } else {
                        idStr += ids[i] + ","
                    }
                }
                data.smids = idStr
                data.sex = sexStr
                data.id = that.info.id
                data.is_appoint_price = that.priceSelect == true?1:0
                setStaffAppointInfo(data).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                        // that.$emit("closeDetail", "success")
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
    /* [カスタムエイド 2026-10-22] 予約詳細設定(ほかの設定画面と同じ決まり) */
    .ad-head { display: flex; align-items: center; padding: 48rpx 32rpx 12rpx; } /* [カスタムエイド 2026-10-22] 余白を1.5倍に */
    .ad-dot { width: 32rpx; height: 32rpx; border-radius: 10rpx; margin-right: 16rpx; flex-shrink: 0; }
    .ad-name { font-size: 36rpx; font-weight: bold; color: #1D1D1F; }
    .ad-seg { display: flex; margin: 36rpx 24rpx 0; padding: 6rpx; background: #E9E9EB; border-radius: 20rpx; }
    .ad-seg-i { flex: 1; height: 72rpx; line-height: 70rpx; text-align: center; border-radius: 16rpx; font-size: 28rpx; color: #515154; border: 1px solid transparent; box-sizing: border-box; }
    .ad-seg-i.on { background: rgba(26, 115, 232, 0.10); border-color: #1A73E8; color: #1A73E8; font-weight: bold; }
    .ad-body { padding: 48rpx 24rpx calc(80rpx + env(safe-area-inset-bottom)); }
    .ad-card { background: #FFFFFF; border-radius: 32rpx; padding: 0 32rpx; margin-bottom: 48rpx; }
    .ad-pad { padding: 32rpx 24rpx 16rpx; }
    .ad-row { display: flex; align-items: center; min-height: 120rpx; border-bottom: 1px solid #E5E5EA; }
    .ad-row.no-line { border-bottom: 0; } /* 指名料金:チェックと入力欄は1つのまとまりなので線を引かない */
    .ad-row:last-child { border-bottom: 0; }
    .ad-row .selectContent, .ad-menu .selectContent { margin-right: 24rpx; }
    .ad-row-t { font-size: 30rpx; color: #1D1D1F; }
    .ad-sub { padding-left: 56rpx; }
    .ad-sec { font-size: 26rpx; color: #6E6E73; padding: 8rpx 8rpx 16rpx; line-height: 1.2; }
    .ad-sec-n { margin-left: 12rpx; }
    .ad-menu { display: flex; align-items: center; background: #FFFFFF; border-radius: 24rpx; padding: 28rpx; margin-bottom: 24rpx; }
    .ad-menu.off .ad-menu-t { color: #8A8A8E; }
    .ad-menu-m { flex: 1; min-width: 0; }
    .ad-menu-t { font-size: 30rpx; font-weight: bold; color: #1D1D1F; line-height: 1.4; }
    .ad-menu-s { font-size: 24rpx; color: #6E6E73; margin-top: 6rpx; }
    .ad-price { color: #1D1D1F; font-weight: 600; margin-right: 20rpx; }
    .ad-photo { width: 112rpx; height: 112rpx; border-radius: 16rpx; margin-left: 20rpx; flex-shrink: 0; }
    .ad-price-in { display: flex; align-items: center; padding: 0 0 36rpx 56rpx; }
    .ad-in { width: 240rpx; height: 88rpx; padding: 0 24rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; font-size: 30rpx; box-sizing: border-box; text-align: right; }
    .ad-ph { color: #AEAEB2; }
    .ad-yen { font-size: 30rpx; margin-left: 12rpx; color: #1D1D1F; }
    .ad-half { font-size: 22rpx; color: #8A8A8E; margin-left: 20rpx; }
    .ad-colors { display: flex; flex-wrap: wrap; }
    .ad-color { width: 20%; display: flex; flex-direction: column; align-items: center; margin-bottom: 36rpx; }
    .ad-color.dis { opacity: 0.3; }
    .ad-sw { width: 72rpx; height: 72rpx; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-sizing: border-box; }
    .ad-sw.on { box-shadow: 0 0 0 4rpx #FFFFFF, 0 0 0 8rpx #1A73E8; }
    .ad-check { color: #FFFFFF; font-size: 32rpx; font-weight: bold; }
    .ad-cname { font-size: 20rpx; color: #515154; margin-top: 10rpx; }
    .ad-save { margin-top: 48rpx; height: 104rpx; line-height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; text-align: center; font-size: 32rpx; font-weight: 700; }
    .ad-hover { opacity: 0.7; }

    .sexItem {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        padding: 43upx 100upx;
    }

    .colorItem {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 24upx 100upx;
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
    }

    .colorSqu {
        margin-right: 20upx;
    }

    .currentColor {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 60upx 40upx;
    }

    .colorSqu {
        width: 40upx;
        height: 40upx;
        border-radius: 8upx;
    }

    .insertContent {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        padding-left: 100upx;
        border-bottom: 2upx solid #d2d2d7;
        padding-bottom: 60upx;
    }

    .selectItems {
        border-bottom: 2upx solid #d2d2d7;
    }

    .contain {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: #F5F5F7;
        z-index: 999;
        overflow-y: scroll;

        .selectContent {
            margin-right: 32upx;
        }

        .detail {
            box-sizing: border-box;
            padding-top: 60upx;

            .managerName {
                box-sizing: border-box;
                padding: 36upx 40upx 40upx;
                font-size: 40upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                box-shadow: 0px 4px 12upx 0upx rgba(0, 0, 0, 0.16);
                z-index: 99;
            }

            .selectAll {
                display: flex;
                align-items: flex-start;
                box-sizing: border-box;
                padding: 60upx 32upx;
            }

            .sDitem {
                display: flex;
                align-items: center;
                box-sizing: border-box;
                padding: 40upx 32upx;
                border-top: 2upx solid #d2d2d7;



                .sDitemRight {
                    .sDtop {
                        font-size: 32upx;
                        font-family: Hiragino Sans-W5, Hiragino Sans;
                        font-weight: normal;
                        color: #1D1D1F;
                        margin-bottom: 32upx;
                    }

                    .sDbottom {
                        display: flex;

                        .sDimage {
                            width: 200upx;
                            height: 200upx;
                            margin-right: 32upx;
                            border-radius: 12upx;
                            overflow: hidden;

                            image {
                                width: 100%;
                                height: 100%;
                            }
                        }

                        .sDinfo {
                            display: flex;
                            font-size: 28upx;
                            font-family: Hiragino Sans-W3, Hiragino Sans;
                            font-weight: normal;
                            color: #1D1D1F;

                            .sDprice {
                                color: #D93025;
                                margin-right: 70upx;
                            }
                        }

                    }
                }
            }

            .sDtitle {
                width: 750upx;
                height: 104upx;
                background: #F5F5F7;
                opacity: 1;
                font-size: 32upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #86868B;
                line-height: 104upx;
                box-sizing: border-box;
                padding: 0 32upx;
                border-top: 2upx solid #d2d2d7;
            }
        }

        .tabs {
            width: 100%;
            box-sizing: border-box;
            padding: 24upx 26upx;

            .tab {
                display: flex;
                align-items: center;
                background: #EAEAEA;
                border-radius: 16upx;

                .tab_item {
                    box-sizing: border-box;
                    padding: 16upx 0;
                    border-radius: 16upx;
                    flex: 1;
                    text-align: center;
                    font-size: 32upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                }

                .active {
                    background: #fff;
                    box-shadow: 0px 6upx 16upx 2upx rgba(0, 0, 0, 0.16);
                    color: #1A73E8 !important;
                }
            }
        }
    }
</style>