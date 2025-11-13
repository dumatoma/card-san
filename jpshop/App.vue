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
            uni.getSystemInfo({
                success(res) {
                    if (res.osName == 'ios') {
                        plus.push.addEventListener('click', function(message) {
                            if (message.payload.type == 'message') {
                                uni.navigateTo({
                                    url: "/pages/message/message"
                                })
                            } else if (message.payload.type == 'order') {
                                uni.navigateTo({
                                    url: "/pages/appointment/appointment"
                                })
                            }
                        });
                    } else {
                        plus.push.addEventListener('receive', function(message) {
                            if (message.payload.type == 'message') {
                                uni.navigateTo({
                                    url: "/pages/message/chat/chat?uid=" + message.payload
                                        .id + "&name=" + message.payload.name
                                })
                            } else if (message.payload.type == 'order') {
                                uni.navigateTo({
                                    url: "/pages/appointment/appointment"
                                })
                            }
                        });
                    }
                }
            })
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
        },
        onShow: function() {
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

            connectPush() {
                // console.log("puishconnect")
                let that = this
                let token = uni.getStorageSync("token")
                let info = uni.getStorageSync("admin")
                let uuid = info.uuid
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

            connectSocket() {
                let that = this
                uni.connectSocket({
                    // url: 'ws://47.245.1.2:21000',
                    url: 'wss://wss.card-san.jp/wss',
                });
                uni.onSocketOpen(function(res) {});
                uni.onSocketError(function(res) {
                    uni.setStorageSync("socketStatus", "unconnect")
                    setTimeout(() => {
                        that.connectSocket()
                    }, 1000)
                });
                uni.onSocketMessage(function(res) {
                    let result = JSON.parse(res.data)
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
                let info = uni.getStorageSync("admin")
                let uuid = info.uuid
                let client_id = uni.getStorageSync("client_id")
                if (token && uuid && client_id) {
                    let d2 = {}
                    d2.client_id = client_id
                    uni.request({
                        url: "https://api.card-san.jp/api/shop/auth/bind_socket",
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