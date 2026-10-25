<script>
    export default {
        onLaunch: function() {
            setTimeout(() => {
                plus.push.getClientInfoAsync((info) => {
                    console.log("pushinfo", info)
                    let cid = info["clientid"];
                    console.log("cid", cid)
                    uni.setStorageSync("cid", cid)
                });
            }, 500)
            this.connectSocket()
            this.connectPush()
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
                        url: "/pages/message/chat/chat?uid=" + message.payload.id + "&name=" + message.payload.name
                    })
                } else if (message.payload.type == 'order') {
                    uni.navigateTo({
                        url: "/pages/appointment/appointment"
                    })
                } else if (message.payload.type == 'staff_room') {
                    uni.navigateTo({
                        url: "/pages/message/staffRoom/groupChat"
                    })
                } else if (message.payload.type == 'staff_dm') {
                    uni.navigateTo({
                        url: "/pages/message/staffRoom/memberList"
                    })
                }
            }
            // フォアグラウンド受信時：システムは自動で通知バーに出さないため手動でローカル通知を作成し、バッジも更新
            function handlePushReceive(message) {
                var payload = parsePayload(message)
                try {
                    var title = payload.title || 'Card-San'
                    var content = payload.content || message.content || '新しい通知があります'
                    plus.push.createMessage(content, JSON.stringify(payload), {
                        title: title
                    })
                } catch (e) {}
                try {
                    if (payload.badge != null) {
                        plus.runtime.setBadgeNumber(Number(payload.badge))
                    }
                } catch (e) {}
            }
            plus.push.addEventListener('click', handlePushClick)
            plus.push.addEventListener('receive', handlePushReceive)
            uni.setStorageSync("ale", "aviliable")
            uni.setStorageSync("socketStatus", "unconnect")
            uni.request({
                url: "https://api.card-san.jp/api/config",
                method: "get",
                success(res) {
                    if (res.data.code == 200) {
                        uni.setStorageSync('today', res.data.data.config.time_date)
                    }
                }
            })
            if (uni.getSystemInfoSync().platform == 'ios') {
                uni.setStorageSync("platform", "ios")
            }
            if (uni.getSystemInfoSync().platform == 'android') {
                uni.setStorageSync("platform", "android")
            }
            // GBP連携：外部ブラウザから cardsan://gbp?... で起動された場合（コールド起動）
            this.handleGbpScheme(true)
        },
        onShow: function() {
            this.csSocketWake() // [カスタムエイド 2026-10-25] 切れていればすぐつなぎ直す
            // GBP連携：外部ブラウザから cardsan://gbp?... で復帰した場合
            this.handleGbpScheme(false)
            // #ifdef APP-PLUS
            // if (plus.os.name == 'Android') {
            //     // 判断是Android
            //     let main = plus.android.runtimeMainActivity();
            //     const pkName = main.getPackageName();
            //     const uid = main.getApplicationInfo().plusGetAttribute('uid');
            //     let NotificationManagerCompat = plus.android.importClass(
            //         'android.support.v4.app.NotificationManagerCompat',
            //     );
            //     //android.support.v4升级为androidx
            //     if (NotificationManagerCompat == null) {
            //         NotificationManagerCompat = plus.android.importClass(
            //             'androidx.core.app.NotificationManagerCompat',
            //         );
            //     }
            //     const areNotificationsEnabled =
            //         NotificationManagerCompat.from(main).areNotificationsEnabled();
            //     // 未开通‘允许通知’权限，则弹窗提醒开通，并点击确认后，跳转到系统设置页面进行设置
            //     if (!areNotificationsEnabled) {
            //         uni.showModal({
            //             title: '通知权限开启提醒',
            //             content: '您还没有开启通知权限，无法接受到消息通知，请前往设置！',
            //             showCancel: true,
            //             confirmText: '去设置',
            //             cancelText: '稍后设置',
            //             success: function(res) {
            //                 if (res.confirm) {
            //                     const Intent = plus.android.importClass(
            //                         'android.content.Intent',
            //                     );
            //                     const Build = plus.android.importClass('android.os.Build');
            //                     //android 8.0引导
            //                     if (Build.VERSION.SDK_INT >= 26) {
            //                         const intent = new Intent(
            //                             'android.settings.APP_NOTIFICATION_SETTINGS',
            //                         );
            //                         intent.putExtra('android.provider.extra.APP_PACKAGE', pkName);
            //                         main.startActivity(intent); // 跳转到该应用的系统通知设置页
            //                     } else if (Build.VERSION.SDK_INT >= 21) {
            //                         //android 5.0-7.0
            //                         const intent = new Intent(
            //                             'android.settings.APP_NOTIFICATION_SETTINGS',
            //                         );
            //                         intent.putExtra('app_package', pkName);
            //                         intent.putExtra('app_uid', uid);
            //                         main.startActivity(intent); // 跳转到该应用的系统通知设置页
            //                     }
            //                 }
            //             },
            //         });
            //     }
            // } else if (plus.os.name == 'iOS') {
            //     // 判断是ISO
            //     let isOn = undefined;
            //     let types = 0;
            //     const app = plus.ios.invoke('UIApplication', 'sharedApplication');
            //     const settings = plus.ios.invoke(app, 'currentUserNotificationSettings');
            //     if (settings) {
            //         types = settings.plusGetAttribute('types');
            //         plus.ios.deleteObject(settings);
            //     } else {
            //         types = plus.ios.invoke(app, 'enabledRemoteNotificationTypes');
            //     }
            //     plus.ios.deleteObject(app);
            //     isOn = 0 != types;
            //     if (isOn == false) {
            //         uni.showModal({
            //             title: '通知权限开启提醒',
            //             content: '您还没有开启通知权限，无法接受到消息通知，请前往设置！',
            //             showCancel: true,
            //             confirmText: '去设置',
            //             cancelText: '稍后设置',
            //             success: function(res) {
            //                 if (res.confirm) {
            //                     const app = plus.ios.invoke('UIApplication', 'sharedApplication');
            //                     const setting = plus.ios.invoke(
            //                         'NSURL',
            //                         'URLWithString:',
            //                         'app-settings:',
            //                     );
            //                     plus.ios.invoke(app, 'openURL:', setting);
            //                     plus.ios.deleteObject(setting);
            //                     plus.ios.deleteObject(app);
            //                 }
            //             },
            //         });
            //     }
            // }
            // #endif
        },
        onHide: function() {
            uni.removeStorageSync("csvData")
        },
        methods: {
            // [GBP連携 2026-10] Google認証後、サーバーのコールバック画面が cardsan://gbp?code=...&state=...（エラー時 ?error=...）へ遷移する
            // plus.runtime.arguments からURLを読み取り、Googleビジネス連携画面へ code/state を渡す
            handleGbpScheme(isLaunch, isRetry) {
                // #ifdef APP-PLUS
                let args = ''
                try {
                    args = plus.runtime.arguments
                } catch (e) {}
                if (typeof args === 'string') args = args.trim()
                if (!args || typeof args !== 'string' || args.toLowerCase().indexOf('cardsan://gbp') !== 0) {
                    // iOS のホット起動では onShow 時点で arguments がまだ更新されていないことがあるため、一度だけ再確認する
                    if (!isLaunch && !isRetry && !this._gbpRetry) {
                        this._gbpRetry = setTimeout(() => {
                            this._gbpRetry = null
                            this.handleGbpScheme(false, true)
                        }, 500)
                    }
                    return
                }
                // 二重処理防止（onLaunch と onShow の両方で拾うケース、arguments のクリアが効かない端末で復帰のたびに拾うケース）
                // code は毎回異なるため、同じURLは二度と処理しない
                if (this._gbpLastUrl === args) return
                this._gbpLastUrl = args
                try {
                    plus.runtime.arguments = ''
                } catch (e) {}
                let params = {}
                let q = args.split('?')[1] || ''
                q = q.split('#')[0]
                q.split('&').forEach(kv => {
                    if (!kv) return
                    let i = kv.indexOf('=')
                    let k = i > -1 ? kv.slice(0, i) : kv
                    let v = i > -1 ? kv.slice(i + 1) : ''
                    try {
                        params[decodeURIComponent(k)] = decodeURIComponent(v.replace(/\+/g, ' '))
                    } catch (e) {
                        params[k] = v
                    }
                })
                let payload = { code: params.code || '', state: params.state || '', error: params.error || '' }
                let go = () => {
                    let pages = getCurrentPages()
                    let top = pages.length ? pages[pages.length - 1] : null
                    if (top && top.route == 'pages/settings/gbpSetting/gbpSetting') {
                        // 連携画面から外部ブラウザを開いて戻ってきた場合は、その画面で処理する
                        uni.$emit('gbpOAuthCallback', payload)
                        return
                    }
                    let url = '/pages/settings/gbpSetting/gbpSetting?'
                    if (payload.code) {
                        url += 'gbp_code=' + encodeURIComponent(payload.code) + '&state=' + encodeURIComponent(payload.state)
                    } else {
                        url += 'gbp_error=' + encodeURIComponent(payload.error || 'unknown')
                    }
                    uni.navigateTo({ url: url })
                }
                // コールド起動時は最初のページの表示を待つ
                setTimeout(go, isLaunch ? 1200 : 300)
                // #endif
            },

            connectPush() {
                let that = this
                let token = uni.getStorageSync("token")
                let info = uni.getStorageSync("admin") || {}
                let uuid = info.uuid || ''
                let client_id = uni.getStorageSync("cid")
                if (token && uuid && client_id) {
                    let d2 = {}
                    d2.clientid = client_id
                    uni.request({
                        url: "https://api.card-san.jp/api/shop/auth/bind_clientid",
                        dataType: "json",
                        responseType: "json",
                        method: 'post',
                        data: d2,
                        header: {
                            'token': token,
                            "uuid": uuid
                        },
                        success(res) {
                            console.log(res)
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
                        if (result.type == 'message' || result.type == 'delete_message' || result.type == 'staff_room_message' || result.type == 'staff_dm' || result.type == 'staff_room_delete' || result.type == 'staff_dm_delete') {
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
                let info = uni.getStorageSync("admin") || {}
                let uuid = info.uuid || ''
                let client_id = uni.getStorageSync("client_id")
                if (token && uuid && client_id) {
                    uni.request({
                        url: "https://api.card-san.jp/api/shop/auth/bind_socket",
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
        }
    }
</script>

<style lang="scss">
    @import "@/uni_modules/uview-ui/index.scss";

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
    .uni-page-head .uni-page-head__title {
        font-size: 40rpx !important;
        font-weight: normal;
    }

    page {
        font-family: Hiragino Sans-W3, Hiragino Sans, Hiragino Sans-W5;
    }

    body {
        font-family: Hiragino Sans-W3, Hiragino Sans, Hiragino Sans-W5;
    }

    .container {
        box-sizing: border-box;
        padding: 0 40upx;
    }

    // svg修改背景颜色
    .sv {
        transform: translate(-10000px);
        filter: drop-shadow(10000px 0 0 rgba(0, 0, 0, 1)); // 参数详解：阴影X偏移、阴影Y偏移、模糊值大小、色值
    }

    .border_top {
        border-top: 2upx solid #d2d2d7;
    }

    .border_bottom {
        border-bottom: 2upx solid #d2d2d7;
    }

    .mgb_20 {
        margin-bottom: 20upx;
    }

    .mgb_60 {
        margin-bottom: 60upx;
    }

    .mgt_20 {
        margin-top: 20upx;
    }

    .mgt_60 {
        margin-top: 60upx;
    }

    .pd20 {
        box-sizing: border-box;
        padding: 20upx;
    }

    .pd32 {
        box-sizing: border-box;
        padding: 32upx;
    }

    .pd40 {
        box-sizing: border-box;
        padding: 40upx;
    }

    .unselect {
        width: 40upx;
        height: 40upx;
        background: #FFFFFF;
        opacity: 1;
        box-sizing: border-box;
        border: 2upx solid #707070;
        border-radius: 50%;
    }

    .selected {
        width: 40upx;
        height: 40upx;
        background: #FFFFFF;
        border: 2upx solid #1A73E8;
        border-radius: 50%;
        box-sizing: border-box;
        padding: 6upx;

        .fill {
            width: 100%;
            height: 100%;
            background: #1A73E8;
            border-radius: 50%;
        }
    }
</style>