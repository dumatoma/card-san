// import api from '@/common/js/config.js' // 接口Api，图片地址等等配置，可根据自身情况引入，也可以直接在下面url填入你的 webSocket连接地址
class socketIO {
	constructor(data, time, url) {
        this.socketTask = null
		this.is_open_socket = false //避免重复连接
		// this.url = "ws://47.245.1.2:21000"  //连接地址
        this.url = "wss://wss.card-san.jp/wss"  //连接地址
        // this.url = "ws://47.244.220.50:2346"  //连接地址  ws://47.244.220.50:2346
		this.data = data ? data : null
		this.connectNum = 1 // 重连次数
		this.traderDetailIndex = 100 // traderDetailIndex ==2 重连
		this.accountStateIndex = 100 // traderDetailIndex ==1 重连
		this.followFlake = false // traderDetailIndex == true 重连
		//心跳检测
		this.timeout = time ? time : 10000 //多少秒执行检测
		this.heartbeatInterval = null //检测服务器端是否还活着
		this.reconnectTimeOut = null //重连之后多久再次重连
	}
 
	// 进入这个页面的时候创建websocket连接【整个页面随时使用】
	connectSocketInit(data) {
		this.data = data
		this.socketTask = uni.connectSocket({
			url: this.url,
			success: () => {
				console.log("正准备建立websocket中...");
				// 返回实例
				return this.socketTask
			},
		});
		this.socketTask.onOpen((res) => {
			this.connectNum = 1
			console.log("WebSocket连接正常！");
            // uni.showToast({
            //     title:"Disconnected from chat server1111",
            //     icon:"none",
            //     mask:true,
            //     duration:5200
            // })
			this.send(data)
			clearInterval(this.reconnectTimeOut)
			clearInterval(this.heartbeatInterval)
			this.is_open_socket = true;
			this.start();
			// 注：只有连接正常打开中 ，才能正常收到消息
			this.socketTask.onMessage((e) => {
				// 字符串转json
				let res = JSON.parse(e.data);
				console.log("res---------->", res) // 这里 查看 推送过来的消息
				if (res.data) {
					uni.$emit('getPositonsOrder', res); 
				}
			});
		})
		// 监听连接失败，这里代码我注释掉的原因是因为如果服务器关闭后，和下面的onclose方法一起发起重连操作，这样会导致重复连接
		uni.onSocketError((res) => {
            uni.showToast({
                title:"Disconnected from chat server",
                icon:"none",
                mask:true,
                duration:5200
            })
			console.log('WebSocket连接打开失败，请检查！');
			this.socketTask = null
			this.is_open_socket = false;
			clearInterval(this.heartbeatInterval)
			clearInterval(this.reconnectTimeOut)
			uni.$off('getPositonsOrder')
			if (this.connectNum < 6) {
				// uni.showToast({
				// 	title: `WebSocket连接失败，正尝试第${this.connectNum}次连接`,
				// 	icon: "none"
				// })
				this.reconnect();
				this.connectNum += 1
			} else {
				uni.$emit('connectError');
				this.connectNum = 1
			}
 
		});
		// 这里仅是事件监听【如果socket关闭了会执行】
		this.socketTask.onClose(() => {
            uni.showToast({
                title:"Disconnected from chat server",
                icon:"none",
                mask:true,
                duration:5200
            })
			clearInterval(this.heartbeatInterval)
			clearInterval(this.reconnectTimeOut)
			this.is_open_socket = false;
			this.socketTask = null
			uni.$off('getPositonsOrder')
			if (this.connectNum < 6) {
				this.reconnect();
			} else {
				uni.$emit('connectError');
				this.connectNum = 1
			}

		})
	}
    // 主动关闭socket连接
	Close() {
		if (!this.is_open_socket) {
			return
		}
		this.socketTask.close({
			success() {
				// uni.showToast({
				// 	title: 'SocketTask 关闭成功',
				// 	icon: "none"
				// });
			}
		});
	}
	//发送消息
	send(data) {
		console.log("data---------->", data);
		// 注：只有连接正常打开中 ，才能正常成功发送消息
		if (this.socketTask) {
			this.socketTask.send({
				data: JSON.stringify(data),
				async success() {
					console.log("消息发送成功");
				},
			});
		}
	}
	//开启心跳检测
	start() {
		this.heartbeatInterval = setInterval(() => {
			this.send({
				"traderid": 10260,
				"type": "Ping"
			});
		}, this.timeout)
	}
	//重新连接
	reconnect() {
		//停止发送心跳
		clearInterval(this.heartbeatInterval)
		//如果不是人为关闭的话，进行重连
		if (!this.is_open_socket) {
			this.reconnectTimeOut = setInterval(() => {
				this.connectSocketInit(this.data);
			}, 5000)
		}
	}
	/**
	 * @description 将 scoket 数据进行过滤 
	 * @param {array} array
	 * @param {string} type 区分 弹窗 openposition 分为跟随和我的
	 */
	arrayFilter(array, type = 'normal', signalId = 0) {
		let arr1 = []
		let arr2 = []
		let obj = {
			arr1: [],
			arr2: []
		}
		arr1 = array.filter(v => v.flwsig == true)
		arr2 = array.filter(v => v.flwsig == false)
		if (type == 'normal') {
			if (signalId) {
				arr1 = array.filter(v => v.flwsig == true && v.sigtraderid == signalId)
				return arr1
			} else {
				return arr1.concat(arr2)
			}
		} else {
			if (signalId > 0) {
				arr1 = array.filter(v => v.flwsig == true && v.sigtraderid == signalId)
				obj.arr1 = arr1
			} else {
				obj.arr1 = arr1
			}
			obj.arr2 = arr2
			
			return obj
		}
	}
}
module.exports = socketIO