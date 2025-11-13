<template>
    <view class="contain">
        <u-navbar title="新規予約" border @leftClick="handleBack" :safeAreaInsetTop="true" bgColor="rgb(251,251,253)"
            :placeholder="true">
        </u-navbar>
        <view class="ct">
            <u--input placeholder="名前、電話番号、IDで検索" @focus="searchMem" prefixIcon="search"
                prefixIconStyle="font-size: 22px;color: #909399">
            </u--input>
            <view class="ctItem">
                <view class="itemName">
                    氏名
                </view>
                <view class="itemDetail">
                    <u--input v-model="info.name" @change="insert1" placeholder="名前" border="bottom" clearable>
                    </u--input>
                </view>
            </view>
            <view class="ctItem">
                <view class="itemName">
                    電話番号
                </view>
                <view class="itemDetail">
                    <u--input v-model="info.phone" @change="insert2" placeholder="请输入内容" border="bottom" clearable>
                    </u--input>
                </view>
            </view>
            <view class="ctItem">
                <view class="itemName">
                    性别
                </view>
                <view class="itemDetail">
                    <view class="picker" @click="show = true">
                        <view v-text="sexname"></view>
                        <u-icon name="arrow-down"></u-icon>
                    </view>
                </view>
            </view>
            <view class="ctItem">
                <view class="itemName">
                    会員ID
                </view>
                <view class="itemDetail">
                    <u--input border="none" disabledColor="#fff" disabled v-model="ID" clearable>
                    </u--input>
                </view>
            </view>
            <!-- <view class="ctItem">
                <view class="itemName">
                    予約番号
                </view>
                <view class="itemDetail">
                    <u--input placeholder="请输入内容" disabledColor="#fff" border="none" disabled v-model="tel" clearable>
                    </u--input>
                </view>
            </view> -->
        </view>
        <view class="ct">
            <view class="pickers" @click="timeShow = true">
                <view v-if="menu.id == -1">
                    メニューを選択
                </view>
                <view v-else v-text="menu.name"></view>
                <u-icon name="arrow-right"></u-icon>
            </view>
        </view>
        <view class="ct" style="padding: 20upx 32upx;">
            <view class="citem border_bottom">
                <view class="citemLeft">
                    日付
                </view>
                <view class="citemRight">
                    <view class="dateDetail" @click="calendarShow = true" v-text="date"></view>
                </view>
            </view>

            <view class="citem border_bottom">
                <view class="citemLeft">
                    開始
                </view>
                <view class="citemRight">
                    <view class="dateDetail" v-text="start" @click="menu.id != -1? startShow = true : ''"></view>
                </view>
            </view>

            <view class="citem border_bottom">
                <view class="citemLeft">
                    終了
                </view>
                <view class="citemRight">
                    <view class="dateDetail" v-text="end" @click="showEnd"></view>
                </view>
            </view>

            <view class="citem border_bottom" v-if="aviliable == true" @click="openList">
                <view class="citemLeft">
                    指名担当
                </view>
                <view class="citemRight">
                    <view class="cperson">
                        <view class="pcolor" :style="`background:${person.color}`"></view>
                        <view class="pname" v-text="person.name"></view>
                        <u-icon name="arrow-right"></u-icon>
                    </view>
                </view>
            </view>

            <view class="citem border_bottom">
                <view class="citemLeft">
                    当日担当
                </view>
                <view class="citemRight">
                    <view class="cperson">
                        <view class="pcolor" :style="`background:${today.color}`"></view>
                        <view class="pname" v-text="today.name"></view>
                    </view>
                </view>
            </view>
        </view>
        <view class="ct">
            <view>メモ</view>
            <u-textarea placeholder="テキスト" v-model="remark" border="none" :height="100"></u-textarea>
        </view>
        <view class="cConfirm" @click="saveData">
            この内容で予約する
        </view>
        <!-- 选择性别弹出层 -->
        <u-popup :show="show" @close="show = false" mode="bottom" bgColor="#f9f9f9">
            <view class="sexItem" v-for="(item,index) in columns" v-text="item" @click="chooseSex(item,index)"></view>
        </u-popup>
        <!-- 选择时间弹出层 -->
        <u-popup :show="timeShow" @close="closeShow" mode="bottom" :safeAreaInsetTop='true' bgColor="f9f9f9"
            :round="16">
            <view style="height: 60vh;width: 100%;overflow-y: scroll;position: relative;">
                <view class="title">
                    <image src="../../static/icons/tclose.png" @click="closeShow" mode=""></image>
                    <view class="titleName">メニュー選択</view>
                </view>
                <view class="detail">
                    <view class="selectAll">
                        <view class="selectContent" @click="changeWai">
                            <view class="unselect" v-if="!wai"></view>
                            <view class="selected" v-if="wai">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="allname">
                            お休み・勤務時間外
                        </view>
                    </view>
                    <view class="settingD">
                        <view class="sDitems" v-for="(item,index) in array">
                            <view class="sDtitle" v-text="item.name"></view>
                            <view class="sDitem" v-for="(ite,idx) in item.items">
                                <view class="selectContent" @click="chooseMenu(index,idx)">
                                    <view class="unselect" v-if="!ite.checked"></view>
                                    <view class="selected" v-if="ite.checked">
                                        <view class="fill"></view>
                                    </view>
                                </view>
                                <view class="sDitemRight">
                                    <view class="sDtop" v-text="ite.name"></view>
                                    <view class="sDbottom">
                                        <view class="sDimage" v-if="ite.image">
                                            <image :src="ite.image[0]" mode=""></image>
                                        </view>
                                        <view class="sDinfo">
                                            <view class="sDprice" v-text="`￥${ite.price}`"></view>
                                            <view class="sDtime" v-text="`所要時間:${ite.time}分`"></view>
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
        </u-popup>
        <!-- 开始时间弹出层 -->
        <u-picker :show="startShow" ref="uPicker" :columns="times" @close="startShow = false"
            :closeOnClickOverlay="true" @cancel='startShow = false' @confirm="changeHandler"></u-picker>
        <!-- 结束时间弹出层 -->
        <u-picker :show="endShow" ref="uPicker" :columns="times" @close="endShow = false" :closeOnClickOverlay="true"
            @cancel='endShow = false' @confirm="changeHandler1"></u-picker>
        <!-- 日历弹出层 -->
        <u-calendar :show="calendarShow" @confirm="dateConfirm" @close="calendarShow = false"></u-calendar>
        <!-- 选人弹出层(担当者) -->
        <u-popup :show="peoShow" round="16">
            <view style="height: 60vh;width: 100%;overflow-y: scroll;position: relative;">
                <view class="title">
                    <image src="../../static/icons/tclose.png" mode="" @click="peoShow = false"></image>
                    <view class="titleName">担当者を選択</view>
                </view>
                <view class="peoContent">
                    <view class="peoItem" v-for="(item,index) in peolist">
                        <view class="selectContent" @click="chooseStaff(index,item)">
                            <view class="unselect" v-if="!item.checked"></view>
                            <view class="selected" v-if="item.checked">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="peoSqu" :style="`background:${item.color}`"></view>
                        <view class="peoName" v-text="item.name"></view>
                    </view>
                </view>
            </view>
        </u-popup>
        <!-- 搜索弹窗 -->
        <u-popup :show="searchShow" round="16">
            <view scroll-y="true" style="width: 100%;position: relative;">
                <view class="title">
                    <image src="../../static/icons/tclose.png" mode="" @click="searchShow = false"></image>
                    <view class="titleName">顧客一覧</view>
                </view>
                <view class="searchContent">
                    <u--input v-model="keywords" @confirm="confirmSearch" placeholder="名前、電話番号、IDで検索"
                        prefixIcon="search" prefixIconStyle="font-size: 22px;color: #909399">
                    </u--input>
                </view>
                <scroll-view class="searchList" @scrolltolower="scrollToBottom" scroll-y="true">
                    <view class="searchItem" v-for="(item,index) in searchList">
                        <view class="sitemLeft">
                            <image :src="item.avatar" mode="aspectFill"></image>
                        </view>
                        <view class="sitemMiddle">
                            <view v-text="item.name"></view>
                            <view v-text="`ヤマダタロウ・${item.phone}`"></view>
                            <view>
                                顧客NO:{{item.member_no}}・ID:{{item.member_id}}
                            </view>
                        </view>
                        <view class="sitemRight" @click="selectItem(index)">
                            選択
                        </view>
                    </view>
                </scroll-view>
            </view>
        </u-popup>
        <!-- 选人弹出框 -->
        <u-popup :show="peoShow2" round="16">
            <scroll-view scroll-y="true" style="height: 1000upx;width: 100%;position: relative;">
                <view class="title">
                    <image src="../../static/icons/tclose.png" mode="" @click="peoShow2 = false"></image>
                    <view class="titleName">担当者を選択</view>
                </view>
                <view class="peoContent">
                    <view class="peoItem" v-for="(item,index) in peolist">
                        <view class="selectContent" @click="choosePeo(item)">
                            <view class="unselect" v-if="!item.checked"></view>
                            <view class="selected" v-if="item.checked">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="peoSqu" :style="`background:${item.color}`"></view>
                        <view class="peoName" v-text="item.name"></view>
                    </view>
                </view>
            </scroll-view>
        </u-popup>
    </view>
</template>
<script>
    import {
        getMemberList,
        menuCateList,
        menuList,
        getStaffList,
        getShopInfo,
        addNewAppoint
    } from "@/api/index.js"
    export default {
        data() {
            return {
                phone: "",
                page: 1,
                show: false,
                timeShow: false,
                startShow: false,
                endShow: false,
                calendarShow: false,
                peoShow: false,
                peoShow2: false,
                keywords: "",
                searchShow: false,
                info: {},
				tempInfo:{},
                wai: false,
                menu: {
                    id: -1
                },
                aviliable: false,
                columns: ['男性', '女性', 'Xジェンダー'],
                sexname: "選択",
                ID: "",
                tel: "CB236587100912",
                date: "2022/08/10",
                start: "00:00",
                end: "00:00",
                remark: "",
                person: {
                    color: "#D2D2D7",
                    name: "指名なし",
                    id: 0
                },
                today: {

                },
                searchList: [],
                peolist: [{
                    name: "指名なし",
                    color: "#d2d2d7",
                    checked: true,
                    id: 0
                }, ],
                times: [
                    [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23],
                    ["00", "30"]
                ],
                selectAll: false,
                array: []
            };
        },
        created() {
            let that = this
            that.getCustomerList()
            that.getDate()
            that.getCate()
            that.getShopInfo()
        },
        methods: {
            getShopInfo() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        that.aviliable = res.data.shop_info.is_appoint == 1 ? true : false
                    }
                })
            },

            handleBack() {
                this.$emit("cancel", "close")
            },

            saveData() {
                let that = this
                let data = {}
				console.log(that.info)
                
                data.name = that.info.name
                data.phone = that.info.phone
                data.sex = that.info.sex
                data.member_id = that.ID || ''
                data.smid = that.menu.id
                data.start_time = that.date + " " + that.start
                data.end_time = that.date + " " + that.end
                data.remark = that.remark
                if (that.person.id) {
                    data.appoint_said = that.person.id
                    data.day_said = that.person.id
                } else {
                    data.appoint_said = ""
                    data.day_said = that.today.id
                }
				if(that.tempInfo.name == that.info.name && that.tempInfo.phone == that.info.phone &&that.tempInfo.sex == that.info.sex){
					data.mid = that.info.id
				}else{
					data.mid = ""
				}
				console.log(data)
                addNewAppoint(data).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                        setTimeout(function() {
                            that.$emit("cancel", "refresh")
                        }, 1500)
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },

            showEnd() {
                let that = this
                if (that.wai == true) {
                    that.endShow = true
                }
            },

            openList() {
                let that = this
                if (that.menu.id == -1) {
                    uni.showToast({
                        title: "请选择服务类目",
                        icon: "error"
                    })
                } else {
                    that.getStaff(that.menu.id, 2)
                }

            },

            changeWai() {
                let that = this
                let array = that.array

                that.wai = true
                that.array.forEach((value, index) => {
                    value.items.forEach((val, idx) => {
                        val.checked = false
                    })
                })

            },

            choosePeo(e) {
                this.person = e
                this.peoShow2 = false
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
                            }
                        })
                    }
                })
            },
            getStaff(appoint, source) {
                let that = this
                let data = {}
                let date = that.date
                data.start_time = date + " " + that.start
                data.end_time = date + " " + that.end
                data.id = appoint
                getStaffList(data).then((res) => {
                    if (res.code == 200) {
                        if (source == 1) {
                            let temp = {}
                            that.today.color = res.data.admins[0].color || "#175491"
                            that.today.id = res.data.admins[0].id
                            that.today.name = res.data.admins[0].name
                            console.log(that.today)
                        } else {
                            that.peolist = [...that.peolist, ...res.data.admins]
                            that.peoShow2 = true
                        }
                    }
                })
            },
            chooseStaff(index, item) {
                let that = this
                that.peolist.forEach((value, index) => {
                    value.checked = false
                })
                that.peolist[index].checked = true
            },
            getDate() {
                let that = this
                let date = new Date()
                let year = date.getFullYear()
                let month = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                let day = date.getDate() + 1 < 10 ? '0' + (date.getDate() + 1) : date.getDate() + 1
                that.date = year + '-' + month + '-' + day
            },
            chooseMenu(index, idx) {
                let that = this
                let array = that.array
                array.forEach((value, index) => {
                    value.items.forEach((val, idx) => {
                        val.checked = false
                    })
                })
                that.array = array
                that.array[index].items[idx].checked = true
                that.wai = false
            },
            dateConfirm(e) {
                this.date = e[0]
                this.calendarShow = false
                let person = {}
                person["color"] = "#D2D2D7"
                person["name"] = "指名なし"
                person["id"] = ""
                this.person = person
                this.getStaff(this.menu.id, 1)
            },
            insert1() {
            },
            insert2() {
            },
            chooseSex(e, index) {
                this.show = false
                this.sexname = e
                this.info.sex = index + 1
                this.info.id = ""
            },
            changeHandler(e) {
                let that = this
                that.start = e.value[0] + ":" + e.value[1]
                that.startShow = false
                that.getEndTime()
            },
            changeHandler1(e) {
                let that = this
                that.end = e.value[0] + ":" + e.value[1]
                that.endShow = false
            },
            searchMem() {
                this.searchShow = true
            },
            getCustomerList() {
                let that = this
                let data = {}
                data.keywords = that.keywords
                data.page = that.page
                data.page_size = 50
                getMemberList(data).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        that.searchList = res.data.members
                    }
                })
            },
            getMore() {
                let that = this
                let data = {}
                data.keywords = that.keywords
                data.page = that.page
                data.page_size = 50
                getMemberList(data).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        that.searchList = [...that.searchList, ...res.data.members]
                    }
                })
            },
            closeShow() {
                let that = this
                that.timeShow = false
                let array = that.array
                let total = 0
                let i
                let ix
                let menu = {}
                menu["id"] = 0
                menu["name"] = "お休み・勤務時間外"
                if (that.wai == true) {
                    that.menu = menu
                    that.totalTime = 0
                    that.getEndTime()
                } else {
                    array.forEach((value, index) => {
                        value.items.forEach((val, idx) => {
                            if (val.checked == true) {
                                total = val.time * 1
                                i = index
                                ix = idx
                            }
                        })
                    })
                    that.menu = array[i].items[ix]
                    that.totalTime = total
                    that.getEndTime()
                }
            },
            getEndTime() {
                let that = this
                let time = that.date + " " + that.start
                let date = new Date(time)
                let min = date.getMinutes()
                date.setMinutes(min + that.totalTime * 1)
                let hour = date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours()
                let minute = date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes()
                that.end = hour + ":" + minute
                that.getStaff(that.menu.id, 1)
            },
            scrollToBottom() {
                let that = this
                uni.showLoading({
                    title: "loading..."
                })
                this.page = this.page * 1 + 1
                that.getMore()
            },
            confirmSearch(e) {
                let that = this
                that.page = 1
                uni.showLoading({
                    title: "loading"
                })
                that.getCustomerList()
            },
            selectItem(e) {
                let that = this
                that.info.name = that.searchList[e].name
                that.info.phone = that.searchList[e].phone
                that.info.sex = that.searchList[e].sex
                that.info.id = that.searchList[e].id
                that.sexname = that.searchList[e].sex == 1 ? '男性' : that.searchList[e].sex == 2 ? '女性' : 'Xジェンダー'
                that.ID = that.searchList[e].member_id
				that.tempInfo.name = that.searchList[e].name
				that.tempInfo.phone = that.searchList[e].phone
				that.tempInfo.sex = that.searchList[e].sex
				that.tempInfo.id = that.searchList[e].id
				that.sexname = that.searchList[e].sex == 1 ? '男性' : that.searchList[e].sex == 2 ? '女性' : 'Xジェンダー'
				that.ID = that.searchList[e].member_id
                that.searchShow = false
				console.log(that.info)
            }
        }
    }
</script>

<style lang="scss">
    .searchList {
        box-sizing: border-box;
        padding: 0 32upx;
        height: 800upx;
    }

    .sDtime {
        flex-shrink: 0;
    }

    .searchItem {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 20upx 0;
        border-bottom: 2upx solid #d2d2d7;

        .sitemLeft {
            image {
                width: 80upx;
                height: 80upx;
            }
        }

        .sitemRight {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
        }

        .sitemMiddle {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            flex: 1;
            box-sizing: border-box;
            padding-left: 20upx;

            view:first-child {
                font-size: 32upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }
        }
    }

    .searchContent {
        box-sizing: border-box;
        padding: 64upx 32upx;
        border-bottom: 2upx solid #d2d2d7;
        position: sticky;
        top: 112upx;
        left: 0;
        background: #fff;
        z-index: 10002;
    }

    .peoItem {
        background: #fff;
        border-bottom: 2upx solid #d2d2d7;
        display: flex;
        box-sizing: border-box;
        padding: 60upx 30upx;

        .peoSqu {
            width: 40upx;
            height: 40upx;
            border-radius: 8upx;
            margin-left: 36upx;
            margin-right: 20upx;
        }
    }

    .cConfirm {
        font-size: 40upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
        text-align: center;
        box-sizing: border-box;
        padding: 96upx 0 186upx 0;
    }

    .selectContent {
        margin-right: 32upx;
    }

    .detail {
        box-sizing: border-box;

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
            align-items: center;
            box-sizing: border-box;
            padding: 60upx 32upx;
        }

        .allname {
            color: #D93025 !important;
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

    .title {
        width: 100%;
        height: 112upx;
        background: #F5F5F7;
        border-top-left-radius: 32upx;
        border-top-right-radius: 32upx;
        text-align: center;
        line-height: 112upx;
        display: flex;
        justify-content: center;
        font-size: 40upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        position: sticky;
        top: 0;
        left: 0;
        z-index: 999;
        border-bottom: 2upx solid #d2d2d7;

        image {
            width: 60upx;
            height: 60upx;
            position: absolute;
            top: 26upx;
            left: 32upx;
        }
    }

    .titleName {
        font-size: 40upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
    }

    .citem {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 20upx 0;

        .citemLeft {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .citemRight {
            // flex: 1;

            .cperson {
                display: flex;
                align-items: center;

                .pcolor {
                    width: 32upx;
                    height: 32upx;
                    border-radius: 8upx;
                    flex-shrink: 0;
                }

                .pname {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #6E6E73;
                    margin-left: 20upx;
                    margin-right: 38upx;
                }
            }
        }

        .dateDetail {
            box-sizing: border-box;
            padding: 20upx;
            border-radius: 20upx;
            background: #F5F5F7;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }
    }

    .pickers {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        justify-content: space-between;
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
    }

    .sexItem {
        text-align: center;
        font-size: 40upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        box-sizing: border-box;
        padding: 30upx 0;
    }

    .contain {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: #f5f5f7;
        z-index: 999;
        overflow-y: scroll;
        box-sizing: border-box;
        padding: 32upx;

        .ct {
            width: 100%;
            background: #fff;
            box-sizing: border-box;
            padding: 60upx 32upx;
            border-radius: 20upx;
            border: 2upx solid #D2D2D7;
            margin-bottom: 40upx;

            .ctItem {
                display: flex;
                margin-top: 56upx;
                align-items: center;

                .itemName {
                    width: 140upx;
                    flex-shrink: 0;
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                }

                .itemDetail {
                    flex: 1;

                    .picker {
                        width: 420upx;
                        height: 84upx;
                        background: #F5F5F7;
                        border-radius: 20upx;
                        opacity: 1;
                        border: 2upx solid #AAAAAA;
                        font-size: 28upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #AAAAAA;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        box-sizing: border-box;
                        padding: 0 20upx;
                    }
                }
            }
        }
    }
</style>
