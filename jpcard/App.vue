<script>
    // import {getAllUnread,getAllUnreadnum} from "@/api/index.js"
    export default {
        onLaunch: function() {
            console.log('App Launch')
            setTimeout(() => {
                plus.push.getClientInfoAsync((info) => {
                    console.log("pushinfo", info)
                    let cid = info["clientid"];
                    console.log("cid", cid)
                    uni.setStorageSync("cid", cid)
                });
            }, 500)
            
            // push 通知イベント処理
            // click: 通知バーをタップした時（バックグラウンド→フォアグラウンド遷移）
            // receive: アプリがフォアグラウンドで通知を受信した時
            function handlePushMessage(message) {
                if (!message.payload) return
                if (message.payload.type == 'message') {
                    uni.navigateTo({
                        url: "/pagesA/message/chat?sid=" + message.payload.id + "&name=" + message.payload.name
                    })
                } else if (message.payload.type == 'coupon') {
                    uni.navigateTo({
                        url: "/pagesA/shop/shop?source=push&id=" + message.payload.id + "&cid=" + message.payload.cid
                    })
                }
            }
            plus.push.addEventListener('click', handlePushMessage)
            plus.push.addEventListener('receive', handlePushMessage)
            this.connectSocket()
            this.connectPush()
            switch (uni.getSystemInfoSync().platform) {
                case 'android':
                    uni.setStorageSync("env", 'android')
                    break;
                case 'ios':
                    uni.setStorageSync("env", 'ios')
                    break;
                default:
                    console.log('运行在开发者工具上');
                    break;
            }
            uni.setStorageSync("ale", "aviliable")
            plus.oauth.getServices(servies => {
                const provide = servies.reduce((cur, next) => {
                    cur.push(next.id);
                    return cur
                }, [])
                const apple = servies[provide.indexOf("apple")]
                apple.logout(res => {
                    console.log("注销成功")
                }, err => {
                    console.log("注销失败", JSON.stringify(err))
                })
            })
        },
        onShow: function() {
            console.log('App Show')
            // this.getWebsocketData()
            switch (uni.getSystemInfoSync().platform) {
                case 'android':
                    uni.setStorageSync("env", 'android')
                    break;
                case 'ios':
                    uni.setStorageSync("env", 'ios')
                    break;
                default:
                    console.log('运行在开发者工具上');
                    break;
            }
            // getAllUnreadnum().then((res) => {
            //     console.log("获取未读消息数")
            //     console.log(res.data.unread_num * 1)
            //     if (res.code == 200) {
            //         plus.runtime.setBadgeNumber(res.data.unread_num * 1);
            //     }
            // })
            // this.$store.commit('connectSocketInit', true)
        },
        onHide: function() {
            let token = uni.getStorageSync("token")
            let info = uni.getStorageSync("user") || {}
            let uuid = info.uuid || ''
            console.log('App Hide')
            uni.request({
                url: this.$baseUrl + "/api/member/member?request_type=unread_num",
                method: "get",
                header: {
                    'token': token,
                    "uuid": uuid
                },
                success(res) {
                    console.log("hideres", res)
                    if (res.data.code == 200) {
                        plus.runtime.setBadgeNumber(res.data.data.unread_num * 1);
                    }
                }
            })
        },
        methods: {
            handleMessage(e) {
                console.log("收到了消息", e)
            },
            connectPush() {
                let that = this
                let token = uni.getStorageSync("token")
                let info = uni.getStorageSync("user") || {}
                let uuid = info.uuid || ''
                let client_id = uni.getStorageSync("cid")
                if (token && uuid && client_id) {
                    let d2 = {}
                    d2.clientid = client_id
                    uni.request({
                        url: "https://api.card-san.jp/api/member/auth/bind_clientid",
                        dataType: "json",
                        responseType: "json",
                        method: 'post',
                        data: d2,
                        header: {
                            'token': token,
                            "uuid": uuid
                        },
                        success(res) {
                            console.log("蚌精", res)
                            if (res.data.code == 200) {
                                // console.log('res',res)
                            } else {
                                uni.showToast({
                                    title: res.data.message,
                                    icon: "none"
                                })
                            }
                        }
                    })
                } else {
                    setTimeout(() => {
                        that.connectPush()
                    }, 1500)
                }
            },
            
            
            
            connectSocket() {
                let that = this
                uni.connectSocket({
                    // url: 'ws://47.245.1.2:21000',
                    url: 'wss://wss.card-san.jp/wss',
                });
                uni.onSocketOpen(function(res) {
                    console.log("socket链接正常",res)
                });
                uni.onSocketError(function(res) {
                    uni.setStorageSync("socketStatus", "unconnect")
                    setTimeout(() => {
                        that.connectSocket()
                    }, 1000)
                });
                uni.onSocketMessage(function(res) {
                    let result = JSON.parse(res.data)
                    console.log("messresult",result)
                    if (result.type == 'message' || result.type == 'delete_message') {
                        if (result.data) {
                            uni.$emit('getPositonsOrder', result);
                        }
                    }
            
                    if (result.type == 'connect') {
                        uni.setStorageSync("client_id", result.data.client_id)
                        setTimeout(() => {
                            that.getConnect()
                        }, 500)
                    }
            
                });
                uni.onSocketClose(function(res) {
                    that.connectSocket()
                });
            },
            getConnect() {
                let that = this
                let token = uni.getStorageSync("token")
                let info = uni.getStorageSync("user") || {}
                let uuid = info.uuid || ''
                let client_id = uni.getStorageSync("client_id")
                if (token && uuid && client_id) {
                    let d2 = {}
                    d2.client_id = client_id
                    uni.request({
                        url: "https://api.card-san.jp/api/member/auth/bind_socket",
                        dataType: "json",
                        responseType: "json",
                        method: 'post',
                        data: d2,
                        header: {
                            'token': token,
                            "uuid": uuid
                        },
                        success(res) {
                            if (res.data.code == 200) {
                                console.log("绑定成功了")
                                uni.setStorageSync("socketStatus", "connected")
                            } else {
                                uni.showToast({
                                    title: res.data.message,
                                    icon: "none"
                                })
                            }
                        }
                    })
                } else {
                    setTimeout(() => {
                        that.getConnect()
                    }, 1500)
                }
            },
            
            
            
            // scoketClose() {
            //     this.socketIo.connectNum = 1
            //     this.socketIo.send() // 这是给后端发送特定数据 关闭推送
            //     this.socketIo.Close() // 主动 关闭连接 ， 不会重连
            // },
            // getWebsocketData() {
            //     // 打开连接
            //     this.socketIo.connectSocketInit()
            //     // 接收数据
            //     uni.$on("getPositonsOrder", (res) => {
            //         if (res.type == 'connect') {
            //             uni.setStorageSync("client_id", res.data.client_id)
            //             this.getConnect()
            //         }
            //     })
            //     // 错误时做些什么
            //     uni.$on("connectError", () => {
            //         this.connect = false
            //         this.scoketError = true
            //     })
            // },
            // connectSocket() {
            //     let that = this
            //     console.log("connect")
            //     uni.connectSocket({
            //         // url: 'ws://47.245.1.2:21000'
            //         url: 'wss://wss.card-san.jp/wss'
            //     });
            //     uni.onSocketOpen(function(res) {
            //         console.log('WebSocket连接已打开！');
            //     });
            //     uni.onSocketError(function(res) {
            //         console.log('WebSocket连接打开失败，请检查');
            //         setTimeout(() => {
            //             that.connectSocket()
            //         }, 1000)
            //     });
            //     uni.onSocketMessage(function(res) {
            //         let result = JSON.parse(res.data)

            //         console.log('收到服务器内容234：' + result.type);
            //         if (result.type == 'message') {
            //             console.log("res---------->", result) // 这里 查看 推送过来的消息
            //             if (result.data) {
            //                 uni.$emit('getPositonsOrder', result);
            //             }
            //         }

            //         if (result.type == 'connect') {
            //             uni.setStorageSync("client_id", result.data.client_id)
            //             setTimeout(() => {
            //                 that.getConnect()
            //             }, 500)
            //         }

            //     });
            //     uni.onSocketClose(function(res) {
            //         console.log('WebSocket 已关闭！');
            //         that.connectSocket()
            //     });
            // },
            // getConnect() {
            //     let that = this

            //     let token = uni.getStorageSync("token")
            //     let info = uni.getStorageSync("user")
            //     let uuid = info.uuid
            //     let client_id = uni.getStorageSync("client_id")
            //     if (token && uuid && client_id) {
            //         let d2 = {}
            //         d2.client_id = client_id

            //         uni.request({
            //             url: "https://api.card-san.jp/api/member/auth/bind_socket",
            //             dataType: "json",
            //             responseType: "json",
            //             method: 'post',
            //             data: d2,
            //             header: {
            //                 'token': token,
            //                 "uuid": uuid
            //             },
            //             success(res) {
            //                 if (res.data.code == 200) {
            //                     console.log("绑定成功了")
            //                 } else {
            //                     uni.showToast({
            //                         title: res.data.message,
            //                         icon: "none"
            //                     })
            //                 }
            //             }
            //         })
            //     } else {
            //         setTimeout(() => {
            //             that.getConnect()
            //         }, 1500)
            //     }
            // },
        }
    }
</script>

<style lang="scss">
    @import "@/uni_modules/uview-ui/index.scss";

    /*每个页面公共css */
    /*每个页面公共css */
    @font-face {
        font-family: Hiragino Sans-W5;
        src: url('~@/static/fonts/NotoSansJP-Bold.otf');
    }

    @font-face {
        font-family: Hiragino Sans-W3;
        src: url('~@/static/fonts/NotoSansJP-Regular.otf');
    }

    @font-face {
        font-family: Hiragino Sans;
        src: url('~@/static/fonts/NotoSansJP-Regular.otf');
    }

    // * {
    //     font-family: Hiragino Sans-W3, Hiragino Sans;
    // }

    page {
        font-family: Hiragino Sans-W3, Hiragino Sans, Hiragino Sans-W5 !important;
    }

    body {
        font-family: Hiragino Sans-W3, Hiragino Sans, Hiragino Sans-W5 !important;
    }

    .container {
        box-sizing: border-box;
        padding: 0 55rpx;
        width: 100vw;
        overflow: hidden;
    }

    .topblank {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 50upx;
        z-index: 150 !important;
        background: #fff;
    }

    .iconright {
        background-size: 100% 100%;
        display: inline-block;
        position: relative;
        top: 6upx;
    }
</style>