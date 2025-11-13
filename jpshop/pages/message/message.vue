<template>
    <view>
        <!-- 自定义顶部导航 -->
        <u-navbar title="メッセージ" :placeholder="true" :safeAreaInsetTop="true" :border="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" mode="" @click="goIndex">
                </image>
            </view>
            <view class="u-nav-slot" slot="right">
                <image src="../../static/svg/gd2.svg" style="width: 60upx;height: 60upx;" @click="toSetting" mode="">
                </image>
            </view>
        </u-navbar>
        <view class="con">
            <view class="search">
                <u--input placeholder="名前、電話番号、IDで検索" prefixIcon="search" prefixIconStyle="font-size: 22px;color: #aaa">
                </u--input>
            </view>
            <view class="total">
                メンバー数:{{array.length}}
            </view>
        </view>
        <view class="listContent">
            <view v-for="(item,index) in array" :options="options1">
                <view class="swipe-action u-border-top u-border-bottom">
                    <view class="swipe-action__content">
                        <view class="itemContent" @click="toDetail(item.uid,item.name)">
                            <view class="itemLeft">
                                <image :src="item.avatar" v-if="item.avatar != ''" mode="aspectFill" style="border-radius: 50%;">
                                </image>
                                <image v-else src="../../static/images/avatar.png" mode="aspectFill" style="border-radius: 50%;">
                                </image>
                            </view>
                            <view class="itemRight">
                                <view class="rightItem">
                                    <view class="userName" v-text="item.name"></view>
                                    <view class="mtime" v-if="item.timestamp != 0"
                                        v-text="showTimePipe(item.timestamp*1000)"></view>
                                </view>
                                <view class="rightItem">
                                    <view class="tel" v-if="item.type == 1"
                                        v-text="item.message.includes('<p>')? '【運営からのメッセージ】': item.message"></view>
                                    <view class="tel" v-if="item.type == 2">[画像]</view>
                                    <view v-if="item.unread_num > 0" class="userId" v-text="item.unread_num"></view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
        </view>
        <permission :show="showpermission" @close='showpermission = false'></permission>
    </view>
</template>

<script>
    import {
        getMessageList,
        bindSocket,
        getAdminDetail
    } from "@/api/index.js"
    import permission from "@/components/permission/permission.vue"
    export default {
        components: {
            permission
        },
        data() {
            return {
                showpermission:false,
                options1: [{
                    text: '削除',
                    style: {
                        backgroundColor: '#D93025'
                    }
                }],
                array: []
            }
        },
        onUnload() {
            // this.scoketClose()
            this.socketIo.traderDetailIndex = 100 // 初始化 tabIndex 
        },
        onHide() {
            // this.scoketClose()
        },
        mounted() {},
        onLoad() {
            this.getWebsocketData()
        },
        onShow() {
            this.getList()
        },
        onPullDownRefresh() {
            uni.showLoading({
                title:"Loading...",
                mask:true
            })
            console.log("pull")
            this.getList()
        },
        methods: {
            scoketClose() {
                this.socketIo.connectNum = 1
                const data = {}
                this.socketIo.send(data) // 这是给后端发送特定数据 关闭推送
                this.socketIo.Close() // 主动 关闭连接 ， 不会重连
            },

            getWebsocketData() {
                let that = this
                // uni.showLoading({
                //     title:"読み込み中",
                //     mask:true
                // })
                // 打开连接
                // this.socketIo.connectSocketInit()
                // 接收数据
                uni.$on("getPositonsOrder", (result) => {
                    this.connect = true
                    if (result.type == 'connect') {
                        bindSocket(result.data.client_id).then((res) => {
                            if (res.code != 200) {
                                uni.hideLoading()
                                uni.showToast({
                                    title: res.message,
                                    icon: "none"
                                })
                            } else {
                                this.getList()
                            }
                        })
                    }
                    if (result.type == "message") {

                    }
                })
                // 错误时做些什么
                uni.$on("connectError", () => {
                    this.connect = false
                    this.scoketError = true
                })
            },
            toDetail(id, name) {
                // this.scoketClose()
                this.socketIo.traderDetailIndex = 100 // 初始化 tabIndex 
                uni.navigateTo({
                    url: "../message/chat/chat?uid=" + id + "&name=" + name
                })
            },
            toSetting() {
                let that = this
                let admin = uni.getStorageSync("admin")
                if(admin.admin_type == 2){
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    getAdminDetail(admin.id).then((res) => {
                        uni.hideLoading()
                        if(res.data.admin.privileges.includes(5)){
                            uni.navigateTo({
                                url: "../message/setting/setting"
                            })
                        }else{
                            that.showpermission = true
                        }
                    })
                }else{
                    uni.navigateTo({
                        url: "../message/setting/setting"
                    })
                }   
            },
            goIndex() {
                uni.switchTab({
                    url: "../index/index"
                })
            },
            getList() {
                let that = this
                let data = {}
                data["type"] = "list"
                data["uid"] = 0
                data["mid"] = 0
                data["timestamp"] = 0
                getMessageList(data).then((res) => {
                    uni.hideLoading()
                    uni.stopPullDownRefresh()
                    if (res.code == 200) {
                        that.array = res.data.messages
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },
            showTimePipe(unix_stamp) { // unix_stamp 精确到微秒
                var _today_obj = new Date(),
                    _today_date = {
                        y: _today_obj.getFullYear(),
                        m: (_today_obj.getMonth() + 1 < 10 ? '0' + (_today_obj.getMonth() - -1) : (_today_obj
                            .getMonth() - -1)),
                        d: (_today_obj.getDate() < 10 ? '0' + _today_obj.getDate() : _today_obj.getDate())
                    }

                var _today_stamp = Date.parse(_today_date.y + '/' + _today_date.m + '/' + _today_date.d + ' 00:00:00')

                var stamp = []
                stamp[0] = _today_stamp + 86400000
                stamp[1] = _today_stamp
                stamp[2] = _today_stamp - 86400000
                stamp[3] = _today_stamp - 172800000
                stamp[4] = _today_stamp - 518400000 // 7天
                stamp[5] = _today_stamp - 31536000000 // 365天
                var _compare_obj = new Date()
                _compare_obj.setTime(unix_stamp)
                var return_str
                if (unix_stamp >= stamp[1] && unix_stamp < stamp[0]) {
                    return_str = '今日' + _compare_obj.getHours() + ':' + (_compare_obj.getMinutes() < 10 ? '0' +
                        _compare_obj
                        .getMinutes() : _compare_obj.getMinutes())
                } else if (unix_stamp >= stamp[2] && unix_stamp < stamp[1]) {
                    var yesterdayText = '昨日'
                    return_str = yesterdayText + ' ' + _compare_obj.getHours() + ':' +
                        (_compare_obj.getMinutes() < 10 ? '0' + _compare_obj.getMinutes() : _compare_obj.getMinutes())
                } else if (unix_stamp >= stamp[3] && unix_stamp < stamp[2]) { // 7天内
                    var daynames = ['天', '一', '二', '三', '四', '五', '六']
                    var dathStr = '星期' + daynames[_compare_obj.getDay()]
                    var SundayText = '日'
                    var MondayText = '月'
                    var TuesdayText = '火'
                    var WednesdayText = '水'
                    var ThursdayText = '木'
                    var FridayText = '金'
                    var SaturdayText = '土'
                    var dathStr2
                    switch (dathStr) {
                        case '星期天':
                            dathStr2 = SundayText
                            break
                        case '星期一':
                            dathStr2 = MondayText
                            break
                        case '星期二':
                            dathStr2 = TuesdayText
                            break
                        case '星期三':
                            dathStr2 = WednesdayText
                            break
                        case '星期四':
                            dathStr2 = ThursdayText
                            break
                        case '星期五':
                            dathStr2 = FridayText
                            break
                        case '星期六':
                            dathStr2 = SaturdayText
                            break
                        default:
                            dathStr2 = dathStr
                            break
                    }
                    return_str = dathStr2 + ' ' + _compare_obj.getHours() + ':' +
                        (_compare_obj.getMinutes() < 10 ? '0' + _compare_obj.getMinutes() : _compare_obj.getMinutes())
                } else if (unix_stamp >= stamp[5] && unix_stamp < stamp[4]) { // 365天内
                    var monthText = '月'
                    var dayText = '日'
                    return_str = (_compare_obj.getMonth() - (-1)) + monthText + _compare_obj.getDate() + dayText + ' ' +
                        _compare_obj.getHours() + ':' + (_compare_obj.getMinutes() < 10 ? '0' + _compare_obj
                            .getMinutes() :
                            _compare_obj.getMinutes())

                } else {
                    var yearText = '年'
                    var monthText = '月'
                    var dayText = '日'
                    return_str = _compare_obj.getFullYear() + yearText + (_compare_obj.getMonth() - (-1)) +
                        monthText + _compare_obj.getDate() + dayText + ' ' + _compare_obj.getHours() + ':' +
                        (_compare_obj.getMinutes() < 10 ? '0' + _compare_obj.getMinutes() : _compare_obj.getMinutes())
                }
                return return_str
            }
        }
    }
</script>

<style lang="scss">
    .userName {
        font-size: 32upx;
        font-family: Hiragino Sans-W6, Hiragino Sans;
        font-weight: bold;
        color: #1D1D1F;
    }

    .userId {
        width: 60upx;
        height: 60upx;
        background: #D93025;
        text-align: center;
        line-height: 60upx;
        font-size: 32upx;
        font-family: Hiragino Sans-W6, Hiragino Sans;
        font-weight: normal;
        color: #FFFFFF;
        border-radius: 50%;
    }

    .itemRight {
        flex: 1;
    }

    .listContent {
        box-sizing: border-box;
        padding: 5upx 32upx;
    }

    .tel {
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        width: 502upx;
        -webkit-line-clamp: 2;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis   
    }

    .mtime {
        font-size: 20upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #86868B;
    }

    .con {
        background-color: #f9f9f9;
        box-sizing: border-box;
        padding: 32upx;
        box-shadow: 0px 1upx 0px 2upx rgba(0, 0, 0, 0.3);

        .search {
            background: rgba(0, 0, 0, .05);

        }

        .total {
            margin-top: 32upx;
        }
    }

    .itemContent {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        padding: 20upx 0;

        .itemLeft {
            width: 80upx;
            height: 80upx;
            margin-right: 20upx;
            flex-shrink: 0;

            image {
                width: 100%;
                height: 100%;
            }
        }

        .rightItem {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    }
</style>