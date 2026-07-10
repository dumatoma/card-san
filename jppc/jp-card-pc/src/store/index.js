import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
 import {
        getConfig,
        getMessageList
    } from "@/http/api.js"
Vue.use(Vuex)


export default new Vuex.Store({
    state: {
        ws: null, //建立的连接
        lockReconnect: false, //是否真正建立连接
        timeout: 1500000, //15秒一次心跳
        timeoutObj: null, //心跳心跳倒计时
        serverTimeoutObj: null, //心跳倒计时
        timeoutnum: null, //断开 重连倒计时
        msg: null ,//接收到的信息
        unreadnum:0,
        planInfo:{}
    },
    getters: {
        // 获取接收的信息
        socketMsgs: state => {
            return state.msg
        }
    },
    mutations: {
        getStoreInfo(){
            let that = this
            axios({
                method: "post",
                url: 'http://api.card-san.jp/api/shop/auth/bind_socket',
                data: {
                    "client_id": data.data.client_id
                },
                headers: {
                    "token": token,
                    "uuid": uuid
                }
            }).then(function(res) {
                console.log(res)
            
            })
        },
        //初始化ws 用户登录后调用
        webSocketInit(state) {
            let that = this
            state.ws = new WebSocket('wss://wss.card-san.jp/wss');
            state.ws.onopen = function(res) {
                console.log("Connection success...");
                /**
                 * 启动心跳检测
                 */
                that.commit("start");
            }
            state.ws.onmessage = function(res) {
                console.log(JSON.parse(res.data))
                let data = JSON.parse(res.data)
                if (res.data === "heartCheck") {
                    // 收到服务器信息，心跳重置
                    that.commit("reset");
                    console.log("socket-heartCheck");
                } else if (data.type == 'connect') {
                    let token = localStorage.getItem("token")
                    let temp = localStorage.getItem("admin")
                    let info = JSON.parse(temp)
                    let uuid = info.uuid
                    console.log("sockettoken",token)
                    console.log("socketuuid",uuid)
                    axios({
                        method: "post",
                        url: 'https://api.card-san.jp/api/shop/auth/bind_socket',
                        data: {
                            "client_id": data.data.client_id
                        },
                        headers: {
                            "token": token,
                            "uuid": uuid,
                            "client":"pc",
                        }
                    }).then(function(res) {
                        console.log(res)

                    })
                } else {
                    state.msg = res;
                    data['type'] = "list"
                    data['uid'] = 0
                    data['mid'] = 0
                    data['timestamp'] = 0
                    let token = localStorage.getItem("token")
                    let temp = localStorage.getItem("admin")
                    let info = JSON.parse(temp)
                    let uuid = info.uuid
                    axios({
                        method: "get",
                        url: 'https://api.card-san.jp/api/shop/message?type=list&uid=0&mid=0&timestamp=0',
                        headers: {
                            "token": token,
                            "uuid": uuid,
                            "client":"pc",
                        }
                    }).then(function(res) {
                        console.log("message",res)
                        if(res.data.code == 200){
                            let num = 0
                            if(res.data.data.messages.length > 0){
                              res.data.data.messages.forEach((val,index) => {
                                  num += val.unread_num
                              })
                              localStorage.setItem("unreadnum", num)  
                                      console.log("un",num)
                              // that.list[3].current = num  
                            }
                        }
                    })
                }
            }
            state.ws.onclose = function(res) {
                console.log("Connection closed...");
                //重连
                that.commit('reconnect');
            }
            state.ws.onerror = function(res) {
                console.log("Connection error...");
                //重连
                that.commit('reconnect');
            }
        },
        reconnect(state) {
            //重新连接
            let that = this;
            if (state.lockReconnect) {
                return;
            }
            state.lockReconnect = true;
            //没连接上会一直重连,30秒重试请求重连，设置延迟避免请求过多
            state.timeoutnum &&
                clearTimeout(state.timeoutnum);
            state.timeoutnum = setTimeout(() => {
                //新连接
                that.commit('webSocketInit')
                state.lockReconnect = false;
            }, 5000);
        },
        reset(state) {
            //重置心跳
            let that = this;
            //清除时间
            clearTimeout(state.timeoutObj);
            clearTimeout(state.serverTimeoutObj);
            //重启心跳
            that.commit('start')
        },
        start(state) {
            //开启心跳
            var self = this;
            state.timeoutObj &&
                clearTimeout(state.timeoutObj);
            state.serverTimeoutObj &&
                clearTimeout(state.serverTimeoutObj);
            state.timeoutObj = setTimeout(() => {
                //这里发送一个心跳，后端收到后，返回一个心跳消息，
                if (state.ws.readyState === 1) {
                    //如果连接正常
                    state.ws.send("heartCheck");
                } else {
                    //否则重连
                    self.commit('reconnect');
                }
                state.serverTimeoutObj = setTimeout(function() {
                    //超时关闭
                    state.ws.close();
                }, state.timeout);
            }, state.timeout);
        },
    },
    actions: {
        webSocketInit({
            commit
        }, url) {
            commit('webSocketInit', url)
        },
        webSocketSend({
            commit
        }, p) {
            commit('webSocketSend', p)
        }
    }
})
