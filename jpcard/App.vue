<script>
    import { handleLaunchLink } from '@/utils/csShopLink.js' // [カスタムエイド 2026-10-26] 店舗QR統一:リンクから開かれたとき
    // import {getAllUnread,getAllUnreadnum} from "@/api/index.js"
    export default {
        onLaunch: function() {
            console.log('App Launch')
            // #ifdef APP-PLUS
            // [カスタムエイド 2026-10-27] リンク(card-sanapp://・Universal Links)でアプリが呼ばれた知らせ。アプリが開いたままのときもリンクを受け取る
            plus.globalEvent.addEventListener('newintent', () => { this.csOnNewIntent() }) // [カスタムエイド 2026-10-27 2回目] 同じリンクをもう一度押したときも開く
            // #endif
            setTimeout(() => {
                plus.push.getClientInfoAsync((info) => {
                    console.log("pushinfo", info)
                    let cid = info["clientid"];
                    console.log("cid", cid)
                    uni.setStorageSync("cid", cid)
                    // [カスタムエイド 2026-10-25] iPhone の通知用の番号(Apple に直接送るため)。前回と変わったら、すぐ1回サーバーに送る
                    try {
                        let ptoken = info["token"] || ""
                        let pold = uni.getStorageSync("push_token") || ""
                        uni.setStorageSync("push_token", ptoken)
                        if (ptoken && ptoken !== pold) this.connectPush(true)
                    } catch (e) {}
                });
            }, 500)
            
            // push 通知イベント処理
            // click: 通知バーをタップした時（バックグラウンド→フォアグラウンド遷移）
            // receive: アプリがフォアグラウンドで通知を受信した時
            // 通知タップ時：該当画面へ遷移
            // payload は JSON 文字列で届く（ローカル通知経由では二重に文字列化される）ためオブジェクトに戻す。2026-10
            // 文字列のまま扱うと前面受信時に生の文字列が表示され（文字化け）、タップ遷移もしなかった
            function parsePayload(message) {
                var p = message && message.payload
                for (var i = 0; i < 3 && typeof p === 'string'; i++) {
                    try { p = JSON.parse(p) } catch (e) { break }
                }
                return (p && typeof p === 'object') ? p : {}
            }
            function handlePushClick(message) {
                message = { payload: parsePayload(message) }
                if (!message.payload.type) return
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
            // フォアグラウンド受信時：システムは自動で通知バーに出さないため手動でローカル通知を作成し、バッジも更新
            var csLastPush = { k: '', t: 0 }
            function handlePushReceive(message) {
                var payload = parsePayload(message)
                // [カスタムエイド 2026-10-25] 個推と Apple の両方から同じ通知が届いたとき、2回出さない(15秒以内の同じ内容は1回だけ)
                var csKey = (payload.type || '') + ':' + (payload.id || '') + ':' + (payload.content || message.content || message.title || '')
                var csNow = Date.now()
                if (csKey === csLastPush.k && csNow - csLastPush.t < 15000) return
                csLastPush = { k: csKey, t: csNow }
                // [カスタムエイド 2026-10-27] アプリを開いているときに通知が2つ出ていた。
                //   原因:10/25 から、Apple の番号(push_token)がある iPhone には、サーバーが Apple に直接送っている。この通知は
                //   アプリを開いていても iPhone が表示する。そこへ下でアプリがもう1つ通知を作っていたので2つになった。
                //   直し:Apple の番号がある iPhone では、アプリでは通知を作らない(バッジだけ直す)。Android・番号のない iPhone は今までどおり作る
                var csIosDirect = false
                try { csIosDirect = plus.os.name == 'iOS' && !!uni.getStorageSync('push_token') } catch (e) {}
                if (!csIosDirect) try {
                    var title = payload.title || 'Card-San'
                    var content = payload.content || message.content || 'メッセージが届きました'
                    plus.push.createMessage(content, JSON.stringify(payload), {
                        title: title
                    })
                } catch (e) {}
                // payload に badge があればバッジを更新（サーバーが件数を付与した場合）
                try {
                    if (payload.badge != null) {
                        plus.runtime.setBadgeNumber(Number(payload.badge))
                    }
                } catch (e) {}
            }
            plus.push.addEventListener('click', handlePushClick)
            plus.push.addEventListener('receive', handlePushReceive)
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
            this.csSocketWake() // [カスタムエイド 2026-10-25] 切れていればすぐつなぎ直す
            this.csCheckLaunchLink() // [カスタムエイド 2026-10-26] リンク(card-san.jp/s/… ・cardsan://shop/…)から開かれたら、そのお店へ
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
            let _token = uni.getStorageSync("token")
            let _info = uni.getStorageSync("user") || {}
            let _uuid = _info.uuid || ''
            if (_token && _uuid) {
                uni.request({
                    url: this.$baseUrl + "/api/member/member?request_type=unread_num",
                    method: "get",
                    header: { 'token': _token, "uuid": _uuid },
                    success(res) {
                        if (res.data.code == 200) {
                            plus.runtime.setBadgeNumber(res.data.data.unread_num * 1)
                        }
                    }
                })
            }
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
            // [カスタムエイド 2026-10-26] 店舗QR統一:iPhone のカメラで QR を読んで「Card-San で開く」(Universal Links)や、店舗ページの
            //   「アプリをお持ちの方はこちら」(cardsan://)から開かれたとき、そのリンクは plus.runtime.arguments に入る。
            //   起動の直後は画面の準備ができていないので少し待ってから開く。同じリンクを2回開かないように消す
            // [カスタムエイド 2026-10-27] LINE ログインから card-sanapp:// で戻ったとき、ログインされずに登録画面に戻っていた。
            //   原因:iPhone では、アプリに戻った瞬間(onShow)には、まだ plus.runtime.arguments が新しいリンクになっていない(管理App の GBP 連携でも同じ対策をしている)。
            //   直し:そのとき見つからなければ 0.5秒後・1.5秒後にもう一度見る。リンクで呼ばれた知らせ(newintent)でも見る
            csCheckLaunchLink(isRetry) {
                // #ifdef APP-PLUS
                try {
                    let url = plus.runtime.arguments
                    if (typeof url === 'string') url = url.trim()
                    if (!url || typeof url !== 'string' || url === this._csLastLink) {
                        if (!isRetry) {
                            setTimeout(() => { this.csCheckLaunchLink(true) }, 500)
                            setTimeout(() => { this.csCheckLaunchLink(true) }, 1500)
                        }
                        return
                    }
                    this.csOpenLink(url)
                } catch (e) {}
                // #endif
            },
            // [カスタムエイド 2026-10-27 2回目] 同じお店のリンク(card-san.jp/s/11 など)をもう一度押しても開かなかった。
            //   原因:上の csCheckLaunchLink は、前と同じリンクを「古いリンクの読み直し」とみなして無視していた
            //   (plus.runtime.arguments は公式の説明では読むだけの値で、消しても消えないことがあるため。管理App の GBP 連携と同じ作り)。
            //   直し:リンクで呼ばれた知らせ(newintent)が来たときだけは、前と同じリンクでも「新しく押された」として開く。
            //   違うリンクのときは、今までどおり csCheckLaunchLink(すぐ・0.5秒後・1.5秒後)が開く。
            //   知らせから1.7秒たっても何も開いていなければ「前と同じリンクがもう一度押された」とみなして開く
            //   (先に開くと、新しいリンクに変わる前の古いリンクを開いてしまうことがあるため待つ)
            csOnNewIntent() {
                // #ifdef APP-PLUS
                const intentAt = Date.now()
                this.csCheckLaunchLink()
                setTimeout(() => {
                    try {
                        if ((this._csLastAt || 0) >= intentAt) return // この知らせのあと、もう開いた
                        let url = plus.runtime.arguments
                        if (typeof url === 'string') url = url.trim()
                        if (!url || typeof url !== 'string') return
                        this.csOpenLink(url)
                    } catch (e) {}
                }, 1700)
                // #endif
            },
            csOpenLink(url) {
                // #ifdef APP-PLUS
                this._csLastLink = url
                this._csLastAt = Date.now()
                try { plus.runtime.arguments = '' } catch (e) {}
                setTimeout(() => { handleLaunchLink(url) }, 800)
                // #endif
            },
            handleMessage(e) {
                console.log("收到了消息", e)
            },
            connectPush(once) {
                let that = this
                let token = uni.getStorageSync("token")
                let info = uni.getStorageSync("user") || {}
                let uuid = info.uuid || ''
                let client_id = uni.getStorageSync("cid")
                if (token && uuid && client_id) {
                    let d2 = {}
                    d2.clientid = client_id
                    // [カスタムエイド 2026-10-25] Apple に直接送るための番号と、iPhone か Android か
                    d2.push_token = uni.getStorageSync("push_token") || ""
                    d2.push_platform = uni.getSystemInfoSync().platform
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
                    if (once) return // 1回だけ送るとき(番号が変わったとき)は、ログイン待ちをしない
                    setTimeout(() => {
                        that.connectPush()
                    }, 1500)
                }
            },
            
            
            
            // ===== [カスタムエイド 2026-10-25] リアルタイム接続(WebSocket)の作り直し =====
            // 以前は、つながり直すたびに「切れたとき・エラーのとき・届いたとき」の見張りを毎回追加していたため、
            // 切れるたびに見張りが増え、1回切れると何本も同時につなぎ直す → サーバーに断られてまた切れる、をくり返していた。
            // そのあいだは「つながっていない」印が付いたままになり、チャットを開くと「サーバー接続エラー」で前の画面に戻されていた。
            // ・見張りは最初の1回だけ付ける ・つなぎ直しは同時に1本だけ、間隔を 1→2→4…最大30秒 に広げる
            // ・アプリに戻ってきたとき(onShow)、切れていればすぐつなぎ直す ・つながってお店/お客様と結び付いたら csSocketReady を知らせる
            connectSocket() {
                let that = this
                if (!that._csSockInit) {
                    that._csSockInit = true
                    uni.onSocketOpen(function() {
                        that._csOpen = true
                        that._csConnecting = false
                        that._csRetry = 0
                    })
                    uni.onSocketError(function() {
                        that.csSocketLost()
                    })
                    uni.onSocketClose(function() {
                        that.csSocketLost()
                    })
                    uni.onSocketMessage(function(res) {
                        let result = null
                        try { result = JSON.parse(res.data) } catch (e) { return }
                        if (!result) return
                        if (result.type == 'message' || result.type == 'delete_message') {
                            if (result.data) {
                                uni.$emit('getPositonsOrder', result);
                            }
                        }
                        if (result.type == 'connect' && result.data) {
                            uni.setStorageSync("client_id", result.data.client_id)
                            that.getConnect()
                        }
                    })
                }
                if (that._csOpen || that._csConnecting) return
                that._csConnecting = true
                uni.connectSocket({
                    url: 'wss://wss.card-san.jp/wss',
                    fail() {
                        that.csSocketLost()
                    }
                })
            },
            csSocketLost() {
                let that = this
                that._csOpen = false
                that._csConnecting = false
                uni.setStorageSync("socketStatus", "unconnect")
                if (that._csTimer) return
                let wait = Math.min(30000, 1000 * Math.pow(2, that._csRetry || 0))
                that._csRetry = (that._csRetry || 0) + 1
                that._csTimer = setTimeout(() => {
                    that._csTimer = null
                    that.connectSocket()
                }, wait)
            },
            // アプリに戻ってきたとき:切れていれば待たずにつなぎ直す
            csSocketWake() {
                if (this._csOpen || this._csConnecting) return
                if (this._csTimer) {
                    clearTimeout(this._csTimer)
                    this._csTimer = null
                }
                this._csRetry = 0
                this.connectSocket()
            },
            getConnect() {
                let that = this
                if (that._csBindTimer) {
                    clearTimeout(that._csBindTimer)
                    that._csBindTimer = null
                }
                let token = uni.getStorageSync("token")
                let info = uni.getStorageSync("user") || {}
                let uuid = info.uuid || ''
                let client_id = uni.getStorageSync("client_id")
                if (token && uuid && client_id) {
                    uni.request({
                        url: "https://api.card-san.jp/api/member/auth/bind_socket",
                        dataType: "json",
                        responseType: "json",
                        method: 'post',
                        data: { client_id: client_id },
                        header: {
                            'token': token,
                            "uuid": uuid
                        },
                        success(res) {
                            if (res.data && res.data.code == 200) {
                                uni.setStorageSync("socketStatus", "connected")
                                uni.$emit('csSocketReady') // 開いているチャットは、この間に届いたメッセージを読み直す
                            }
                            // 結び付けに失敗しても画面には出さない(以前は意味の分かりにくいメッセージが出ていた)。少し後にやり直す
                            else {
                                that._csBindTimer = setTimeout(() => { that.getConnect() }, 5000)
                            }
                        },
                        fail() {
                            that._csBindTimer = setTimeout(() => { that.getConnect() }, 5000)
                        }
                    })
                } else {
                    // ログインしていないときは、少し間をあけて確かめ直す(1本だけ)
                    that._csBindTimer = setTimeout(() => { that.getConnect() }, 3000)
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

    /* [カスタムエイド 2026-10-19] 日時選択の時間の枠(components/week/week.vue)を押したときの見た目(予約WEBページと同じ):枠全体を押せるようにし、押すと枠の色が変わる */
    .dateItem .cs-slot {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        border-radius: 4px;
        -webkit-tap-highlight-color: transparent;
        transition: transform .08s ease, background-color .12s ease;
    }
    .dateItem .cs-slot > div {
        background-size: auto 62% !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
    }
    .cs-slot:active {
        transform: scale(.92) !important;
        background-color: rgba(26, 115, 232, .22) !important;
    }


    /* [カスタムエイド 2026-10-19] アプリ共通の文章の表示(お店が入力した文章):行間 1.6 倍。
       空行は utils/textParas.js で段落に分け、段落の間を少しだけ空ける(まるまる1行は空けない) */
    .cs-para {
        white-space: pre-wrap;
        line-height: 1.6;
        word-break: break-word;
    }
    .cs-para + .cs-para {
        margin-top: 16rpx;
    }
</style>