<template>
    <view class="contain">
        <u-navbar title="予約詳細設定" border @leftClick="handleBack" @rightClick="save" rightText="保存"
            :safeAreaInsetTop="true" bgColor="rgb(251,251,253)" :placeholder="true">
        </u-navbar>
        <view class="decon">
            <view class="tabs">
                <view class="tab">
                    <view class="tab_item" @click="changeTab(0)" :class="status == 0? 'active' : '' ">
                        対応メニュー
                    </view>
                    <view class="tab_item" @click="changeTab(1)" :class="status == 1? 'active' : '' ">
                        その他設定
                    </view>
                </view>
            </view>
        </view>
        <view class="details">
            <view class="detail" v-if="status == 0">
                <view class="managerName" v-text="info.name"></view>
                <view class="selectAll">
                    <view class="selectContent" @click="choosePerson">
                        <view class="unselect" v-if="!selectAll"></view>
                        <view class="selected" v-if="selectAll">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view class="allname">
                        すべてのメニューに対応
                    </view>
                </view>
                <view class="settingD">
                    <view class="sDitems" v-for="(item,index) in array">
                        <view class="sDtitle" v-text="item.name"></view>
                        <view class="sDitem" v-for="(ite,idx) in item.items">
                            <view class="selectContent" @click="chooseItem(index,idx)">
                                <view class="unselect" v-if="!ite.checked"></view>
                                <view class="selected" v-if="ite.checked">
                                    <view class="fill"></view>
                                </view>
                            </view>
                            <view class="sDitemRight">
                                <view class="sDtop" v-text="ite.name"></view>
                                <view class="sDbottom">
                                    <view class="sDimage" v-if="ite.image">
                                        <image :src="ite.image[0]" mode="aspectFill"></image>
                                    </view>
                                    <view class="sDinfo">
                                        <view class="sDprice" v-text="`${ite.price}`"></view>
                                        <view class="sDtime" v-text="`所要時間:${ite.time}分`"></view>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <view class="detail" v-if="status == 1">
                <view class="managerName" v-text="info.name"></view>
                <view class="selectAll" style="padding-bottom: 23upx;">
                    <view class="selectContent" @click="chooseSexAll">
                        <view class="unselect" v-if="!sexAll"></view>
                        <view class="selected" v-if="sexAll">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view class="allname">
                        すべてのジェンダーに対応
                    </view>
                </view>
                <view class="selectItems">
                    <view class="sexItem" v-for="(item,index) in sexItems">
                        <view class="selectContent" @click="chooseSex(index)">
                            <view class="unselect" v-if="!item.checked"></view>
                            <view class="selected" v-if="item.checked">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="allname" v-text="item.name"></view>
                    </view>
                </view>

                <view class="pay">
                    <view class="selectAll" style="padding-bottom: 23upx;">
                        <view class="selectContent" @click="choosePrice">
                            <view class="unselect" v-if="!priceSelect"></view>
                            <view class="selected" v-if="priceSelect">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="allname">
                            指名料金を設定する
                            <br/>
                            <text style="color: red;font-size: 24rpx;">半角数字のみ</text>
                        </view>
                    </view>

                    <view class="insertContent">
                        <view style="width: 224upx;height: 56upx;margin-right: 20upx;">
                            <u-input type="digit" border="surround" v-model="price" :disabled="!priceSelect" shape="circle">
                            </u-input>
                        </view>
                        <view class="insertName">
                            円
                        </view>
                    </view>

                    <view class="colors">
                        <view class="currentColor" @click="show = !show">
                            <view style="display: flex;align-items: center;">
                                <view class="colorSqu" :style="`background:${currentColor}`"></view>
                                <view class="colorName" v-text="currentColorName"></view>
                            </view>
                            <u-icon name="arrow-down" :style="show?'transform:rotate(180deg)': ''"></u-icon>
                        </view>
                        <view class="colorsContent" v-if="show">
                            <view class="colorItem" v-for="(item,index) in color" @click="chooseColor(index,item)">
                                <view style="display: flex;align-items: center;">
                                    <view class="colorSqu" :style="`background:${item.color}`"></view>
                                    <view class="colorName" v-text="item.name"
                                        :style="item.aviliable? '' : 'color:#BCBCBC' "></view>
                                </view>
                                <u-icon name="checkmark" color="#1A73E8" size="20" v-if="item.checked"></u-icon>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
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
                let val = this.price;

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

            checkeSexAll() {
                let that = this
                that.sexItems.forEach((val, index) => {
                    if (val.checked == false) {
                        that.sexAll = false
                        throw new Error("stop", index)
                    } else {
                        that.sexAll = true
                    }
                })
            },

            checkSelectAll() {
                let that = this
                that.array.forEach((value, index) => {
                    value.items.forEach((val, idx) => {
                        if (val.checked == false) {
                            that.selectAll = false
                            that.checkeSexAll()
                            throw new Error("stop")
                        } else {
                            that.selectAll = true
                        }
                    })
                })
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
        background: #fff;
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