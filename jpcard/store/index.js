import Vue from 'vue'
import Vuex from 'vuex'
import {bindSocket} from "@/api/index.js"
Vue.use(Vuex)

const store = new Vuex.Store({
    state: {
        socketTask: null,
        // 确保websocket是打开状态
        is_open_socket: false,
        newMessage:{}
    },
    getters: {

    },
    mutations: {
        // 进入这个页面的时候创建websocket连接【整个页面随时使用】(客户端)
        connectSocketInit(state, is_open_socket) {
            // 创建一个this.socketTask对象【发送、接收、关闭socket都由这个对象操作】
            this.state.socketTask = uni.connectSocket({
                // 【非常重要】必须确保你的服务器是成功的,如果是手机测试千万别使用ws://127.0.0.1:9099【特别容易犯的错误】
                // url:"wss://wss1.lanyanceshi.com/wss",  
                url:"ws://47.245.1.2:21000",
                success(data) {
                    console.log("websocket连接成功", data);
                },
            });

            // 消息的发送和接收必须在正常连接打开中,才能发送或接收【否则会失败】
            this.state.socketTask.onOpen((res) => {
                this.state.is_open_socket = is_open_socket;
                // 注：只有连接正常打开中 ，才能正常收到消息
                this.state.socketTask.onMessage((res) => {
                    let result
                    try {
                        result = JSON.parse(res.data)
                    } catch(e) {
                        console.log("WebSocket消息解析失败", e)
                        return
                    }
                    if(result.type == "connect" && result.data && result.data.client_id){
                        bindSocket(result.data.client_id).then((res) => {
                            console.log("bind",res)
                            if(res.code != 200){
                                uni.showToast({
                                    title:res.message,
                                    icon:"none"
                                })
                            }
                        })
                    }
                    this.state.newMessage = result
                    console.log("newMessage",this.state.newMessage)
                    uni.$emit("getPositonsOrder",result)
                });
            })
            // 这里仅是事件监听【如果socket关闭了会执行】
            this.state.socketTask.onClose(() => {
                console.log("已经被关闭了")
            })
        },
        // 关闭websocket【离开这个页面的时候执行关闭】
        closeSocket(state, is_open_socket) {
            console.log(this.state)
            const _this = this
            this.state.socketTask.close({
                success(res) {
                    console.log("关闭成功", res)
                    _this.state.is_open_socket = is_open_socket
                },
                fail(err) {
                    // console.log("关闭失败", err)
                }
            })
        }
    },
    actions: {}
})

export default store
