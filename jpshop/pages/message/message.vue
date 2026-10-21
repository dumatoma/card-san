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
        <!-- [カスタムエイド 2026-10-21] 見た目をこれまでの画面に合わせた(LINEと同じ1行ずつの一覧のまま、スタッフとお客様を白い角丸のまとまりに)。
             「メッセージ配信」のボタンを、メッセージ設定のいちばん下からこの画面のいちばん上に移した(行き先は同じ) -->
        <view class="ms">
            <view class="ms-send" hover-class="ms-hover" @click="toSend">＋メッセージ配信(一斉・セグメント)</view>

            <block v-if="(staffRoomOn && staffRoomJoin) || dmThreads.length">
                <view class="ms-h3">スタッフ</view>
                <view class="ms-card">
                    <view class="ms-row" v-if="staffRoomOn && staffRoomJoin" hover-class="ms-row-hover" @click="toGroupChat">
                        <image :src="staffRoomAvatar || '../../static/images/avatar.png'" class="ms-av" mode="aspectFill"></image>
                        <view class="ms-mid">
                            <view class="ms-name">{{ staffRoomName }}({{ staffRoomCount }})</view>
                            <view class="ms-last">{{ staffRoomLastMessage || 'スタッフルーム' }}</view>
                        </view>
                        <view class="ms-badge" v-if="groupUnread > 0">{{ groupUnread }}</view>
                    </view>
                    <view class="ms-row" v-for="thread in dmThreads" :key="thread.to_id" hover-class="ms-row-hover" @click="toDm(thread)">
                        <image :src="thread.avatar || '../../static/images/avatar.png'" class="ms-av" mode="aspectFill"></image>
                        <view class="ms-mid">
                            <view class="ms-name">{{ thread.name }}</view>
                            <view class="ms-last">{{ thread.last_message }}</view>
                        </view>
                        <view class="ms-badge" v-if="thread.unread > 0">{{ thread.unread }}</view>
                    </view>
                </view>
            </block>

            <view class="ms-h3">お客様</view>
            <view class="ms-search" :class="{ on: searchFocus }">
                <image class="ms-glass" src="../../static/svg/cs_search.svg" mode="aspectFit"></image>
                <input class="ms-search-in" v-model="keywords" placeholder="名前、電話番号、IDで検索" placeholder-class="ms-ph"
                    @focus="searchFocus = true" @blur="searchFocus = false" />
            </view>
            <view class="ms-count">メンバー <text class="ms-count-n">{{array.length}}</text> 人</view>
            <view class="ms-card" v-if="array.length">
                <view class="ms-row" v-for="(item, index) in array" :key="index" hover-class="ms-row-hover" @click="toDetail(item.uid, item.name)">
                    <image :src="item.avatar || '../../static/images/avatar.png'" class="ms-av" mode="aspectFill"></image>
                    <view class="ms-mid">
                        <view class="ms-top">
                            <text class="ms-name">{{item.name}}</text>
                            <text class="ms-time" v-if="item.timestamp != 0">{{showTimePipe(item.timestamp * 1000)}}</text>
                        </view>
                        <view class="ms-bottom">
                            <text class="ms-last" v-if="item.type == 1" v-text="lastText(item)"></text>
                            <text class="ms-last" v-if="item.type == 2">[画像]</text>
                            <view class="ms-badge" v-if="item.unread_num > 0">{{item.unread_num}}</view>
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
        getAdminDetail,
        getStaffRoom,
        getStaffRoomUnread,
        getDmThreads
    } from "@/api/index.js"
    import permission from "@/components/permission/permission.vue"
    export default {
        components: {
            permission
        },
        data() {
            return {
                showpermission:false,
                staffRoomOn: false,
                staffRoomJoin: false,
                staffRoomName: '',
                staffRoomAvatar: '',
                staffRoomCount: 0,
                staffRoomLastMessage: '',
                dmThreads: [],
                groupUnread: 0,
                options1: [{
                    text: '削除',
                    style: {
                        backgroundColor: '#D93025'
                    }
                }],
                keywords: "",
                searchFocus: false, // [カスタムエイド 2026-10-21]
                allMessages: []
            }
        },
        computed: {
            array() {
                if (!this.keywords.trim()) return this.allMessages
                const kw = this.keywords.trim().toLowerCase()
                return this.allMessages.filter(item => {
                    return (item.name || '').toLowerCase().includes(kw)
                })
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
            this.loadStaffRoom()
        },
        onPullDownRefresh() {
            uni.showLoading({
                title:"Loading...",
                mask:true
            })
            this.getList()
        },
        methods: {
            // [カスタムエイド 2026-10-21] メッセージ配信(一斉・セグメント)。メッセージ設定の「メッセージを配信」と同じ画面
            // [カスタムエイド 2026-10-21] 最後のメッセージ(運営からのお知らせは見出しだけ。今までと同じ)
            lastText(item) {
                const m = item.message || ''
                return m.indexOf('<p>') > -1 ? '【運営からのメッセージ】' : m
            },
            toSend() {
                uni.navigateTo({
                    url: "/pages/message/notice/notice"
                })
            },
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
                        }).catch(() => {})
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
                    }).catch(() => { uni.hideLoading() })
                }else{
                    uni.navigateTo({
                        url: "../message/setting/setting"
                    })
                }   
            },
            loadStaffRoom() {
                getStaffRoom().then(res => {
                    if (res.code == 200) {
                        this.staffRoomOn    = res.data.room.is_on == 1
                        this.staffRoomJoin  = res.data.my_join == 1
                        this.staffRoomName  = res.data.room.name
                        this.staffRoomAvatar = res.data.room.avatar
                        this.staffRoomCount = res.data.members ? res.data.members.length : 0
                        this.staffRoomLastMessage = res.data.room.last_message || ''
                    }
                }).catch(() => {})
                getStaffRoomUnread().then(res => {
                    if (res.code == 200) this.groupUnread = res.data.group_unread
                }).catch(() => {})
                getDmThreads().then(res => {
                    if (res.code == 200) this.dmThreads = res.data.threads || []
                }).catch(() => {})
            },
            toGroupChat() {
                uni.navigateTo({ url: '../message/staffRoom/groupChat' })
            },
            toDm(thread) {
                uni.navigateTo({ url: '../message/staffRoom/dm?to_id=' + thread.to_id + '&name=' + encodeURIComponent(thread.name) + '&avatar=' + encodeURIComponent(thread.avatar || '') })
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
                        that.allMessages = res.data.messages
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                }).catch(() => { uni.hideLoading(); uni.stopPullDownRefresh() })
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
    .staffRoomSection {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 28upx 32upx;
        background: #fff;
        border-bottom: 2upx solid #f0f0f0;
        .srLeft {
            display: flex;
            align-items: center;
        }
        .srAvatar {
            width: 90upx;
            height: 90upx;
            border-radius: 50%;
            margin-right: 24upx;
        }
        .srName {
            font-size: 32upx;
            font-weight: bold;
            color: #1D1D1F;
        }
        .srSub {
            font-size: 24upx;
            color: #86868B;
            margin-top: 6upx;
        }
        .srBadge {
            min-width: 44upx;
            height: 44upx;
            background: #D93025;
            border-radius: 22upx;
            flex-shrink: 0;
            box-sizing: border-box;
            text-align: center;
            line-height: 44upx;
            font-size: 24upx;
            color: #fff;
            padding: 0 10upx;
        }
    }

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

    /* [カスタムエイド 2026-10-21] これまでの画面と同じ見た目(LINEと同じ1行ずつの一覧) */
    .ms {
        min-height: 100vh;
        background: #F5F5F7;
        padding: 28rpx 24rpx calc(60rpx + env(safe-area-inset-bottom));
        box-sizing: border-box;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .ms-send {
        height: 92rpx;
        line-height: 92rpx;
        border-radius: 28rpx;
        background: #34C759;
        color: #FFFFFF;
        text-align: center;
        font-size: 30rpx;
        font-family: 'Hiragino Sans-W5', sans-serif;
        margin-bottom: 32rpx;
    }

    .ms-hover {
        opacity: 0.8;
    }

    .ms-h3 {
        margin: 0 8rpx 16rpx;
        font-size: 28rpx;
        font-family: 'Hiragino Sans-W5', sans-serif;
        color: #515154;
    }

    .ms-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        overflow: hidden;
        margin-bottom: 36rpx;
    }

    .ms-row {
        display: flex;
        align-items: center;
        padding: 24rpx 28rpx;
        position: relative;
    }

    .ms-row::after {
        content: '';
        position: absolute;
        left: 148rpx;
        right: 0;
        bottom: 0;
        height: 1px;
        background: #E5E5EA;
    }

    .ms-row:last-child::after {
        display: none;
    }

    .ms-row-hover {
        background: #F5F5F7;
    }

    .ms-av {
        width: 96rpx;
        height: 96rpx;
        flex-shrink: 0;
        border-radius: 50%;
        background: #E5E5EA;
        margin-right: 24rpx;
    }

    .ms-mid {
        flex: 1;
        min-width: 0;
    }

    .ms-top,
    .ms-bottom {
        display: flex;
        align-items: center;
    }

    .ms-name {
        flex: 1;
        min-width: 0;
        display: block;
        font-size: 31rpx;
        font-family: 'Hiragino Sans-W5', sans-serif;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ms-time {
        flex-shrink: 0;
        margin-left: 12rpx;
        font-size: 22rpx;
        color: #8A8A8E;
    }

    .ms-last {
        flex: 1;
        min-width: 0;
        display: block;
        margin-top: 4rpx;
        font-size: 26rpx;
        color: #6E6E73;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* 未読の数(今までと同じ赤) */
    .ms-badge {
        flex-shrink: 0;
        min-width: 40rpx;
        height: 40rpx;
        line-height: 40rpx;
        padding: 0 12rpx;
        margin-left: 16rpx;
        border-radius: 20rpx;
        background: #D93025;
        color: #FFFFFF;
        text-align: center;
        font-size: 22rpx;
        box-sizing: border-box;
    }

    .ms-search {
        display: flex;
        align-items: center;
        height: 84rpx;
        padding: 0 24rpx;
        border-radius: 24rpx;
        background: #FFFFFF;
        border: 1px solid #D2D2D7;
        box-sizing: border-box;
    }

    .ms-search.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    .ms-glass {
        width: 32rpx;
        height: 32rpx;
        flex-shrink: 0;
        margin-right: 16rpx;
    }

    .ms-search-in {
        flex: 1;
        height: 84rpx;
        font-size: 28rpx;
    }

    .ms-ph {
        color: #8A8A8E;
    }

    .ms-count {
        margin: 20rpx 8rpx 16rpx;
        font-size: 26rpx;
        color: #515154;
    }

    .ms-count-n {
        font-size: 30rpx;
        font-weight: 700;
        color: #1D1D1F;
    }
</style>