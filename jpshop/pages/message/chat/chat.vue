<template>
    <view class="contain">
        <scroll-view class="chatContent">
            <view class="msgItem" v-for="(item,index) in messageArr">
                <view class="msgDate" v-if="item.Showdate" v-text="`${item.Showdate}  (${item.week})`"></view>
                <view class="userItem" :style="item.sender == 1? 'flex-direction: row-reverse' : '' ">
                    <view>
                        <view class="avatar">
                            <image :src="item.avatar || defaultImage" mode="aspectFill"></image>
                        </view>
                        <view class="chatName" v-text="item.name"></view>
                    </view>
                    <view class="detail" :style="item.sender == 1? 'flex-direction: row-reverse' : '' ">
                        <view v-if="item.type == 1" class="msgDetail"
                            :style="item.sender == 1? 'background:#87FA7D' : '' ">
                            <text v-html="formattedText(item.message)" selectable="true"
                                style="white-space: pre-wrap;"></text>
                        </view>
                        <view v-if="item.type == 2" class="msgDetailImage">
                            <image @click="previewImage(item.message)" :src="item.message" mode="widthFix"></image>
                        </view>
                        <view v-if="item.type == 3" class="msgDetailImage" style="height: 120rpx;overflow: hidden;">
                            <image @click="openURL(item.url)" :src="item.message" style="height: 120rpx;width: 100%;" mode="aspectFill"></image>
                        </view>
                        <view style="display: flex;align-items: center;">
                            <view class="sendTime" v-text="item.time"></view>
                            <view class="delas" v-if="item.said == myid" @click.stop="delthis(item,index)">
                                <image src="../../../static/svg/delta.svg" mode=""></image>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <view class="blank"></view>
        </scroll-view>
        <view class="sendBox" :style="{bottom: h > 0 ? h + 'px' : '0'}" style="position: fixed;">
            <view class="boxLeft">
                <image src="../../../static/images/camera.png" mode="aspectFit" @click="chooseCamear" v-if="!fs">
                </image>
                <image src="../../../static/images/album.png" mode="aspectFit" @click="chooseImage" v-if="!fs"></image>
                <image src="../../../static/images/showmore.png" mode="aspectFit" v-if="fs"
                    @touchend.prevent="fs = false"></image>
            </view>
            <view class="boxRight" style="flex: 1;">
                <input placeholder="メッセージを入力"
                    style="border: 2upx solid #999;height:65upx;border-radius: 30upx;box-sizing: border-box;padding: 0 15upx;"
                    v-model="sendMessage" adjust-position="false" @focus="emitFocus" @blur="blur"></input>
            </view>
            <view class="boxRights" v-if="sendMessage != ''">
                <image src="../../../static/images/send.png" mode="" @touchend.prevent="sendMessages" v-if="msid == 0">
                </image>
                <image src="../../../static/svg/nosend.svg" mode="" v-if="msid != 0"></image>
            </view>
        </view>
        <mod :config="config" @getStatus="confirm" v-if="delshow"></mod>
    </view>
</template>

<script>
    const admin = uni.getStorageSync("admin")
    import {
        getMessageList,
        sendMessage,
        hasRead,
        bindSocket,
        delMsg
    } from "@/api/index.js"
    import mod from "@/components/mod.vue"
    export default {
        components: {
            mod
        },
        data() {
            return {
                myid: "",
                fs: false,
                messageArr: [],
                query: {},
                currentMessages: [],
                fArray: [],
                defaultImage: "../../../static/images/avatar.png",
                sendMessage: "",
                history: [],
                reachBottom: true,
                shows: true,
                msid: 0,
                h: 0,
                delshow: false,
                config: {},
                didx: ""
            }
        },
        created() {
            uni.onKeyboardHeightChange(res => {
                if (res.height == 0) {
                    this.fs = false
                } else {
                    this.fs = true
                }
            })
        },
        onUnload() {
            let that = this
            let hasReadData = {}
            hasReadData.uid = that.query.uid
            hasRead(hasReadData).then((res) => {})
            this.scoketClose()
            this.socketIo.traderDetailIndex = 100
        },
        onHide() {
            // this.scoketClose()
        },
        onLoad(options) {
            let that = this
            uni.showLoading({
                title: "読み込み中"
            })
            let query = options
            
            console.log("adasdasdasdasdasdasd",options)
            
            that.query = query
            that.getHistory()
            uni.setNavigationBarTitle({
                title: options.name
            })
            setTimeout(() => {
                that.getWebsocketData()
            }, 1200)

        },
        onPullDownRefresh() {
            this.loadMore()
        },
        onReachBottom(e) {
            let that = this
            setTimeout(function() {
                that.reachBottom = true
            }, 300)

        },
        onShow() {
            let that = this
            let admin = uni.getStorageSync("admin")
            that.myid = admin.id
            let status = uni.getStorageSync("socketStatus")
            if (status == "unconnect") {
                uni.showModal({
                    title: "エラー",
                    content: "サーバー接続エラーが発生しました。後ほどもう一度実行してください。",
                    showCancel: false,
                    success: async (res) => {
                        if (res.confirm) {
                            uni.navigateBack()
                        }
                    }
                })
            }
        },
        onPageScroll(e) {
            this.reachBottom = false
            this.h = this.h - e.scrollTop
        },
        watch: {
            newMessage: {
                handler(nval, oval) {

                },
                deep: true, // 深度监听
                immediate: true, //立即执行
            }
        },
        methods: {
            openURL(e) {
                // #ifdef APP-PLUS
                if (plus.os.name == 'Android' || plus.os.name == 'iOS') {
                    plus.runtime.openURL(e);
                    return
                }
                // #endif
            },
            confirm(e) {
                let that = this
                if (e == "cancel") {
                    let data = {}
                    data.uid = that.query.uid
                    data.message_id = that.delid
                    delMsg(data).then((res) => {
                        if (res.code == 200) {
                            that.delshow = false
                        } else {
                            uni.showToast({
                                title: res.message
                            })
                        }
                    })
                } else {
                    this.delshow = false
                }
            },
            delthis(e, index) {
                let that = this
                that.delid = e.id
                that.didx = index
                that.config = {
                    type: "button",
                    cancel: true,
                    default: true,
                    title: "",
                    content: "選択したメッセージを削除しますか？",
                    cancelText: "削除",
                    defaultText: "キャンセル"
                }
                that.delshow = true
            },
            jumpLink(e) {},
            formattedText(e) {
                // 使用正则表达式找到所有的网址  
                const regex = /https?:\/\/[^\s]+/g;
                const urls = e.match(regex);

                if (urls) {
                    // 替换所有匹配的网址为带有蓝色样式的链接文本  
                    let linkText = '';
                    urls.forEach((match) => {
                        linkText += `<a href="${match}" class="blue-link">${match}</a> `;
                    });
                    // return e.replace(regex, '<a href="#" @click="toLink(123)" class="blue-link">$&</a>');
                    return linkText
                } else {
                    return e;
                }
            },
            blur() {
                let that = this
                uni.getSystemInfo({
                    success(res) {
                        that.h = res.safeAreaInsets.bottom + 'px'
                    }
                })
            },
            emitFocus(e) {
                let that = this
                setTimeout(() => {
                    // that.$nextTick(()=>{
                    //   uni.pageScrollTo({
                    //       scrollTop: 2000000,
                    //       duration: 0 
                    //   });
                    // }) 
                    that.scollBottom()
                }, 1000)
            },
            scoketClose() {
                this.socketIo.connectNum = 1
                const data = {}
                this.socketIo.send(data) // 这是给后端发送特定数据 关闭推送
                this.socketIo.Close() // 主动 关闭连接 ， 不会重连
            },

            getWebsocketData() {
                let that = this
                // // 打开连接
                // this.socketIo.connectSocketInit()
                // 接收数据
                uni.$on("getPositonsOrder", (result) => {
                    console.log("result", result)
                    that.connect = true
                    if (result.type == 'connect') {
                        bindSocket(result.data.client_id).then((res) => {
                            if (res.code != 200) {
                                uni.showToast({
                                    title: res.message,
                                    icon: "none"
                                })

                            }
                        })
                    }
                    // let that = this

                    if (result.type == "message" && result.data[0].uid == that.query.uid) {
                        if (result.data[0].sender == 1) {
                            if (result.data[0].id == that.msid) {
                                that.msid = 0
                            } else {
                                that.msid = result.data[0].id
                            }
                        } else {
                            that.msid = 0
                        }
                        result.data.forEach((v, i) => {
                            if (v.type == 3) {
                                let a = JSON.parse(v.message)
                                v.message = a.comment_button_style_image
                                v.url = a.comment_button_url
                            }
                        })
                        that.currentMessages = [...that.currentMessages, ...result.data]
                        let currentMessages = that.currentMessages
                        that.messageArr = [...that.history, ...currentMessages]
                        for (let i = 0; i < that.messageArr.length; i++) {
                            if (i > 0) {
                                if (that.messageArr[i].date == that.messageArr[i - 1].date) {
                                    that.messageArr[i].Showdate = ''
                                } else {
                                    that.messageArr[i].Showdate = that.messageArr[i].date
                                }
                            } else {
                                that.messageArr[i].Showdate = that.messageArr[i].date
                            }
                            if (that.messageArr[i].sender == 1) {
                                // that.messageArr[i].name = admin.name
                            }
                        }
                        that.scollBottom()
                    }

                    if (result.type == 'delete_message') {
                        that.messageArr.forEach((v, i) => {
                            if (v.id == result.data.message_id) {
                                that.messageArr.splice(i, 1)
                            }
                        })
                        
                        that.currentMessages.forEach((v, i) => {
                            if (v.id == result.data.message_id) {
                                that.currentMessages.splice(i, 1)
                            }
                        })
                        
                        
                        that.history.forEach((v, i) => {
                            if (v.id == result.data.message_id) {
                                that.history.splice(i, 1)
                            }
                        })
                    }
                })

                // 错误时做些什么
                uni.$on("connectError", () => {
                    this.connect = false
                    this.scoketError = true
                })
            },
            getHistory() {
                let that = this
                let data = {}
                data.type = "detail"
                data.timestamp = that.formatTime()
                data.uid = that.query.uid
                data.mid = 0
                getMessageList(data).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        res.data.messages.data.forEach((v, i) => {
                            if (v.type == 3) {
                                let a = JSON.parse(v.message)
                                v.message = a.comment_button_style_image
                                v.url = a.comment_button_url
                            }
                        })
                        that.history = res.data.messages.data.reverse()
                        let currentMessages = that.currentMessages
                        that.messageArr = [...that.history, ...currentMessages]
                        for (let i = 0; i < that.messageArr.length; i++) {
                            if (i > 0) {
                                if (that.messageArr[i].date == that.messageArr[i - 1].date) {
                                    that.messageArr[i].Showdate = ''
                                } else {
                                    that.messageArr[i].Showdate = that.messageArr[i].date
                                }
                            } else {
                                that.messageArr[i].Showdate = that.messageArr[i].date
                            }

                            if (that.messageArr[i].sender == 1) {
                                // that.messageArr[i].name = admin.name
                            }
                        }
                        that.scollBottom()
                        let hasReadData = {}
                        hasReadData.uid = that.query.uid
                        // hasRead(hasReadData).then((res) => {})
                    }
                })
            },
            jumpUrl(e) {
                if (e == "") {

                } else {
                    uni.navigateTo({
                        url: "../../webview/webview?link=" + e
                    })
                }

            },
            onBackPress() {

                let pages = getCurrentPages();
                let currPage = pages[pages.length - 1]
                let prePage = pages[pages.length - 2]
                if (prePage.route == "pages/message/message") {
                    prePage.$vm.getList()
                }
            },
            loadMore() {
                let that = this
                let data = {}
                data.type = "detail"
                data.timestamp = that.formatTime()
                data.uid = that.query.uid
                data.mid = that.history[0].id
                getMessageList(data).then((res) => {
                    if (res.code == 200) {
                        if (res.data.messages.data.length > 0) {
                            that.history = [...that.history, ...res.data.messages.data.reverse()]
                            that.messageArr = [...that.history, ...that.messageArr]
                            for (let i = 0; i < that.messageArr.length; i++) {
                                if (i > 0) {
                                    if (that.messageArr[i].date == that.messageArr[i - 1].date) {
                                        that.messageArr[i].Showdate = ''
                                    } else {
                                        that.messageArr[i].Showdate = that.messageArr[i].date
                                    }
                                } else {
                                    that.messageArr[i].Showdate = that.messageArr[i].date
                                }

                                if (that.messageArr[i].sender == 1) {
                                    that.messageArr[i].name = admin.name
                                }
                            }

                        }
                        uni.stopPullDownRefresh()
                    }
                })
            },
            previewImage(e) {
                let that = this
                let tem = []
                tem.push(e)
                uni.previewImage({
                    current: 0,
                    urls: tem
                })
            },
            formatTime() {
                var date = new Date()
                var timeStamp = Date.parse(date)
                return timeStamp
            },
            chooseImage() {
                let that = this
                uni.hideKeyboard()
                uni.chooseImage({
                    count: 1,
                    sourceType: ['album'],
                    success: (res) => {
                        const tempPaths = res.tempFilePaths
                        uni.uploadFile({
                            url: that.$baseUrl + '/tool/upload/file',
                            filePath: res.tempFilePaths[0],
                            name: "file",
                            success: (result) => {
                                if (result.errMsg == "uploadFile:ok") {
                                    let re = JSON.parse(result.data)
                                    if (re.code == 200) {
                                        let data = {}
                                        data.uid = that.query.uid
                                        data.type = 2
                                        data.message = re.data.path
                                        sendMessage(data).then((res) => {
                                            if (that.msid == res.data.message_id) {
                                                that.msid = 0
                                            } else {
                                                that.msid = res.data.message_id
                                            }
                                        })
                                    } else if (res.code == 4003) {

                                    }
                                }
                            }
                        })
                    }
                });
            },
            chooseCamear() {
                let that = this
                uni.hideKeyboard()
                uni.chooseImage({
                    count: 1,
                    sourceType: ['camera'],
                    success: (res) => {
                        const tempPaths = res.tempFilePaths
                        uni.uploadFile({
                            url: that.$baseUrl + '/tool/upload/file',
                            filePath: res.tempFilePaths[0],
                            name: "file",
                            success: (result) => {
                                if (result.errMsg == "uploadFile:ok") {
                                    let re = JSON.parse(result.data)
                                    if (re.code == 200) {
                                        let data = {}
                                        data.uid = that.query.uid
                                        data.type = 2
                                        data.message = re.data.path
                                        sendMessage(data).then((res) => {
                                            setTimeout(() => {
                                                if (that.msid == res.data
                                                    .message_id) {
                                                    that.msid = 0
                                                } else {
                                                    that.msid = res.data
                                                        .message_id
                                                }
                                            }, 1000)

                                        })
                                    }
                                }
                            }
                        })
                    }
                });
            },
            scollBottom() {
                this.$nextTick(function() {
                    setTimeout(() => {
                        uni.createSelectorQuery().select('.chatContent').boundingClientRect((res) => {
                            uni.pageScrollTo({
                                scrollTop: res.height,
                                duration: 10
                            })
                        }).exec()
                    }, 50)

                });
            },
            sendMessages() {
                let that = this
                let data = {}
                that.reachBottom = true
                data.uid = that.query.uid
                data.type = 1
                data.message = that.sendMessage
                if (that.sendMessage == '') {
                    uni.showToast({
                        title: "入力してください",
                        icon: "none"
                    })
                } else {
                    that.sendMessage = ""
                    sendMessage(data).then((res) => {
                        that.sendMessage = ""
                        // uni.hideKeyboard()
                        if (res.code == 200) {

                            if (that.msid == res.data.message_id) {
                                that.msid = 0
                            } else {
                                that.msid = res.data.message_id
                            }
                            let hasReads = {}
                            hasReads.uid = that.query.uid
                            hasRead(hasReads).then((res) => {})
                        }
                    })
                }
            }
        }
    }
</script>

<style lang="scss">
    .delas {
        width: 40rpx;
        height: 40rpx;
        margin: 0 0 0 20rpx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .blue-link {
        color: blue;
        text-decoration: underline;
    }

    .contain {
        display: flex;
        flex-direction: column;
        height: auto;

        .msgDetailImage {
            width: 492rpx;
            border-radius: 20upx;
            margin: 0 10rpx;
            image {
                width: 100%;
                border-radius: 20upx;
            }
        }

        .chatContent {
            flex: 1;
            background-color: #fff;
            box-sizing: border-box;
            padding: 0 32upx;

            .userItem {
                display: flex;

                .avatar {
                    width: 60upx;
                    height: 60upx;
                    border-radius: 50%;
                    flex-shrink: 0;
                    overflow: hidden;
                    border: 2rpx solid #aaa;

                    image {
                        width: 100%;
                        height: 100%;
                    }
                }

                .detail {
                    display: flex;
                    align-items: flex-end;

                    .msgDetail {
                        max-width: 492upx;
                        box-sizing: border-box;
                        padding: 20upx;
                        font-size: 32upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #1D1D1F;
                        background: rgba(143, 168, 248, 0.2);
                        border-radius: 20upx;
                        margin: 0 8upx;
                        word-wrap: break-word;
                        word-break: normal;
                    }

                    .sendTime {
                        font-size: 20upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #86868B;
                    }
                }
            }
        }

        .msgItem {
            margin-bottom: 24upx;

            .msgDate {
                text-align: center;
                padding: 24upx 0;
            }
        }

        .blank {
            width: 100vw;
            height: 122upx;
        }

        .chatName {
            font-size: 24upx;
            color: #aaa
        }

        .sendBox {
            width: 100vw;
            height: 122upx;
            background: #F9F9F9;
            box-shadow: 0px -1upx 0px 2upx rgba(0, 0, 0, 0.3);
            opacity: 0.93;
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 0 32upx 32upx;
            flex-shrink: 0;
            position: fixed;
            bottom: 0;
            left: 0;
            justify-content: space-between;

            .boxRights {
                margin-left: 20upx;

                image {
                    width: 54upx;
                    height: 54upx;

                }
            }

            .boxLeft {
                display: flex;
                align-items: center;

                image {
                    width: 54upx;
                    height: 54upx;
                    margin-right: 28upx
                }

                image:first-child {
                    width: 54upx;
                    height: 54upx
                }
            }

            .boxRight {
                flex: 1
            }
        }
    }
</style>