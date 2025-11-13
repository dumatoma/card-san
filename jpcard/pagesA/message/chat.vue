<template>
    <view class="contain">
        <scroll-view class="chatContent">
            <view class="msgItem" v-for="(item,index) in messageArr">
                <view class="msgDate" v-if="item.Showdate" v-text="`${item.Showdate}  (${item.week})`"></view>
                <view class="userItem" :style="item.sender != 1? 'flex-direction: row-reverse' : '' ">
                    <view>
                        <view class="avatar">
                            <image :src="item.avatar || defaultImage" mode="aspectFill"></image>
                        </view>
                        <view class="chatName" v-text="item.name"></view>
                    </view>
                    <view class="detail" :style="item.sender != 1? 'flex-direction: row-reverse' : '' ">
                        <view v-if="item.type == 1" class="msgDetail"
                            :style="item.sender != 1? 'background:#87FA7D' : '' ">
                            <text v-html="formattedText(item.message)" selectable="true"
                                style="white-space: pre-wrap;"></text>
                        </view>
                        <view v-if="item.type == 2" class="msgDetailImage">
                            <image @click="previewImage(item.message)" :src="item.message" mode="widthFix"></image>
                        </view>
                        <view v-if="item.type == 3" class="msgDetailImage" style="height: 120rpx;overflow: hidden">
                            <image @click="openURL(item.url)" :src="item.message" style="height: 120rpx;width: 100%;" mode="aspectFill"></image>
                        </view>                        
                        <view style="display: flex;align-items: center;">
                            <view class="sendTime" v-text="item.time"></view>
                            <view class="delas" v-if="item.sender != 1" @click.stop="delthis(item,index)">
                                <image src="../../static/svg/delta.svg" mode=""></image>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <view class="blank"></view>
        </scroll-view>
        <view class="sendBox" :style="{bottom: h > 0 ? h + 'px' : '0'}" style="position: fixed;">
            <view class="boxLeft">
                <image src="../../static/image/camera.png" mode="aspectFit" @click="chooseCamear" v-if="!fs"></image>
                <image src="../../static/image/album.png" mode="aspectFit" @click="chooseImage" v-if="!fs"></image>
                <image src="../../static/image/showmore.png" mode="aspectFit" v-if="fs" @touchend.prevent="fs = false">
                </image>
            </view>
            <view class="boxRight" style="flex: 1;">
                <input placeholder="メッセージを入力"
                    style="border: 2upx solid #999;height:65upx;border-radius: 30upx;box-sizing: border-box;padding: 0 15upx;"
                    v-model="sendMessage" adjust-position="false" @focus="emitFocus" @blur="blur"></input>
            </view>
            <view class="boxRights" v-if="sendMessage != ''">
                <image src="../../static/image/send.png" mode="" @touchend.prevent="send" v-if="msid == 0"></image>
                <image src="../../static/svg/nosend.svg" mode="" v-if="msid != 0"></image>
            </view>
        </view>
        <mod :config="config" @getStatus="confirm" v-if="delshow"></mod>
    </view>
</template>

<script>
    let admin = uni.getStorageSync("user")
    import mod from "@/components/mod.vue"
    import {
        getMessageList,
        sendMessage,
        hasRead,
        loadMore,
        bindSocket,
        delMsg
    } from "@/api/index.js"
    export default {
        components: {
            mod
        },
        data() {
            return {
                delshow: false,
                messageArr: [],
                query: {},
                config: {},
                currentMessages: [],
                fArray: [],
                defaultImage: "../../static/image/avatar.png",
                sendMessage: "",
                history: [],
                reachBottom: true,
                sid: "",
                fs: false,
                connect: false,
                msid: 0,
                h: 0,
                myid: "",
                didx: "",
                insert:false
            }
        },
        computed: {
            newMessage() {
                return this.$store.state.newMessage;
            },
        },
        created() {
            console.log("created")
            this.getWebsocketData()
            this.myid = admin.id
            uni.onKeyboardHeightChange(res => {
                if (res.height == 0) {
                    this.fs = false
                } else {
                    this.fs = true
                }
            })
        },
        onLoad(options) {
            let that = this
            let query = options
            uni.showLoading({
                title: "読み込み中"
            })
            that.sid = query.sid
            that.getHistory()
            uni.setNavigationBarTitle({
                title: decodeURI(query.name)
            });
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

        onUnload() {
            let that = this
            hasRead(that.sid).then((res) => {})
        },
        onHide() {},
        onPageScroll(e) {
            if(this.insert == true){
                this.reachBottom = false
                this.h = this.h - e.scrollTop
            }
            
        },
        onShow() {
            let that = this
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
        methods: {
            blur() {
                this.insert = false
            },
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
                    data.sid = that.sid
                    data.message_id = that.delid
                    delMsg(data).then((res) => {
                        if (res.code == 200) {
                            that.messageArr.splice(that.didx * 1, 1)
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
            toLink(e) {
                console.log(e)
            },
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
            emitFocus(e) {
                console.log(e)
                let that = this
                that.insert = true
                setTimeout(() => {
                    // that.$nextTick(()=>{
                    //   uni.pageScrollTo({
                    //       scrollTop: 2000000,
                    //       duration: 0 
                    //   });
                    // }) 
                    that.scollBottom()
                }, 50)
            },
            getWebsocketData() {
                let that = this
                // 打开连接
                // 接收数据
                uni.$on("getPositonsOrder", (result) => {
                    // this.connect = true
                    // if (result.type == 'connect') {
                    //     bindSocket(result.data.client_id).then((res) => {
                    //         if (res.code != 200) {
                    //             uni.showToast({
                    //                 title: res.message,
                    //                 icon: "none"
                    //             })
                    //         }
                    //     })
                    // }
                    console.log("收到的消息result1", result)
                    if (result.type == "message" && result.data[0].sid == that.sid) {
                        console.log(123123123213213123213213213213213123213123123213123)
                        if (result.data[0].sender == 2) {
                            if (result.data[0].id == that.msid) {
                                console.log("m,", result.data[0].id)
                                that.msid = 0
                            } else {
                                console.log("m,1", result.data[0].id)
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
                        }
                        that.scollBottom()
                    }
                    if (result.type == 'delete_message') {
                        // that.messageArr.forEach((v, i) => {
                        //     if (v.id == result.data.message_id) {
                        //         console.log(true)
                        //         that.messageArr.splice(i, 1)
                        //     }
                        // })
                        // that.currentMessages.forEach((v, i) => {
                        //     if (v.id == result.data.message_id) {
                        //         that.currentMessages.splice(i, 1)
                        //     }
                        // })

                        // that.history.forEach((v, i) => {
                        //     if (v.id == result.data.message_id) {
                        //         that.history.splice(i, 1)
                        //     }
                        // })
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
                getMessageList(that.sid).then((res) => {
                    if (res.code == 200) {
                        that.$nextTick(() => {
                            uni.pageScrollTo({
                                scrollTop: 2000000, //滚动到页面的目标位置（单位px）
                                duration: 0 //滚动动画的时长，默认300ms，单位 ms
                            });
                        })
                        res.data.messages.data.forEach((v, i) => {
                            if (v.type == 3) {
                                let a = JSON.parse(v.message)
                                v.message = a.comment_button_style_image
                                v.url = a.comment_button_url
                            }
                        })
                        that.history = res.data.messages.data.reverse()
                        console.log('hjhs', that.history)
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

                            // if (that.messageArr[i].sender == 2) {
                            //     that.messageArr[i].name = admin.name
                            // }
                        }

                        setTimeout(() => {
                            that.scollBottom()
                            uni.hideLoading()
                        }, 50)
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
                let that = this
                let pages = getCurrentPages();
                // hasRead(that.sid).then((res) => {})
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
                data.stamp = that.formatTime()
                data.sid = that.sid
                if (that.history.length == 0) {
                    data.mid = 0
                } else {
                    data.mid = that.history[0].id
                }
                loadMore(data).then((res) => {
                    if (res.code == 200) {
                        if (res.data.messages.data.length > 0) {
                            that.history = res.data.messages.data.reverse()
                            that.messageArr = [...that.history, ...that.messageArr]
                            that.currentMessages = [...that.history, ...that.messageArr]
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
                                        data.sid = that.sid
                                        data.type = 2
                                        data.message = re.data.path
                                        sendMessage(data).then((res) => {
                                            if (that.msid == res.data
                                                .message_id) {
                                                that.msid = 0
                                            } else {
                                                that.msid = res.data.message_id
                                            }
                                        })
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
                                        data.sid = that.sid
                                        data.type = 2
                                        data.message = re.data.path
                                        sendMessage(data).then((res) => {
                                            if (that.msid == res.data
                                                .message_id) {
                                                that.msid = 0
                                            } else {
                                                that.msid = res.data.message_id
                                            }
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
                        uni.createSelectorQuery().select('.chatContent').boundingClientRect((
                            res) => {
                            uni.pageScrollTo({
                                scrollTop: res.height,
                                duration: 10
                            })
                        }).exec()
                    }, 50)

                });
            },
            send() {
                let that = this
                let data = {}
                data.sid = that.sid
                data.type = 1
                data.message = that.sendMessage
                if (that.sendMessage == '') {

                } else {
                    that.sendMessage = ""
                    // uni.hideKeyboard()
                    sendMessage(data).then((res) => {

                        if (res.code == 200) {
                            if (that.msid == res.data.message_id) {
                                that.msid = 0
                            } else {
                                that.msid = res.data.message_id
                            }

                        }
                    })
                }
            }
        }
    }
</script>

<style lang="scss">
    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    .delas {
        width: 40rpx;
        height: 40rpx;
        margin: 0 0 0 20rpx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .boxRights {
        margin-left: 20upx;

        image {
            width: 54upx;
            height: 54upx;
        }
    }

    .u-nav-right {
        image {
            width: 48rpx;
            height: 48rpx;
        }
    }

    .contain {
        display: flex;
        flex-direction: column;
        // height: calc(100vh - 44px);
        height: auto;

        .msgDetailImage {
            width: 492rpx;
            border-radius: 20upx;
            overflow: hidden;
            margin: 0 8rpx;
            image {
                width: 100%;
            }
        }

        .chatContent {
            flex: 1;
            background-color: #fff;
            box-sizing: border-box;
            padding: 0 32rpx;
            // background: red !important;
            .userItem {
                display: flex;

                .avatar {
                    width: 60upx;
                    height: 60upx;
                    border-radius: 50%;
                    flex-shrink: 0;
                    overflow: hidden;

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
            height: 140upx;
        }

        .chatName {
            font-size: 24upx;
            color: #aaa
        }

        .sendBox {
            width: 100vw;
            height: 120upx;
            background: #F9F9F9;
            box-shadow: 0px -1upx 0px 2upx rgba(0, 0, 0, 0.3);
            opacity: 0.93;
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 0 32upx 32upx;
            flex-shrink: 0;
            position: fixed;
            left: 0;
            bottom: 0;
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