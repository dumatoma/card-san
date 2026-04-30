/****   request.js   ****/
// 导入axios
import axios from '../utils/axios/index.js'
// 使用element-ui Message做消息提醒
// import { Message} from 'element-ui';
//1. 创建新的axios实例，
const service = axios.create({
  // 公共接口--这里注意后面会讲
  // baseURL: 'http://192.168.0.199',//测试地址
  // baseURL: 'https://hyk.ljcyh.com',//测试地址
  baseURL: 'https://api.card-san.jp',//测试地址
  
  
  
    // baseURL: 'https://che.lanyanceshi.com',//测试地址
  // 超时时间 单位是ms，这里设置了3s的超时时间
  timeout: 5 * 1000
})
// 2.请求拦截器
service.interceptors.request.use(config => {
    // 发请求前做的一些处理，数据转化，配置请求头，设置token,设置loading等，根据需求去添加
    config.data = JSON.stringify(config.data) // 数据转化, 也可以使用qs转换
    config.headers = {
        'Content-Type':'application/json;charset=UTF-8' // 配置请求头
    }
    // 注意使用 token 的时候需要引入 cookie 方法或者用本地 localStorage 等方法，推荐 js-cookie
    let token = uni.getStorageSync("token") // 这里取 token 之前，你肯定需要先拿到 token, 存一下
    let info = uni.getStorageSync("user") || {}
    let uuid = info.uuid || ''
    if(token){
        // config.params = {'token':token} // 如果要求携带在参数中
        // 如果要求携带在请求头中
        config.headers = {
            'Content-Type':'application/json;charset=UTF-8',
            // 'token': token,
            // "uuid": uuid
        }
    }
    return config
}, error => {
    console.log("errora",error)
    return Promise.reject(error)
})

axios.defaults.adapter = function(config) {
	return new Promise((resolve, reject) => {
		var settle = require('../utils/axios/lib/core/settle.js');
		var buildURL = require('../utils/axios/lib/helpers/buildURL.js');
		uni.request({
			method: config.method.toUpperCase(),
			url: config.baseURL + buildURL(config.url, config.params, config.paramsSerializer),
			header: config.headers,
			data: config.data,
			dataType: config.dataType,
			responseType: config.responseType,
			sslVerify: config.sslVerify,
			complete: function complete(response) {
				// console.log("执行完成：", response)
				response = {
					data: response.data,
					status: response.statusCode,
					errMsg: response.errMsg,
					header: response.header,
					config: config
				};
				settle(resolve, reject, response);
			}
		})
	})
}

// 3.响应拦截器
service.interceptors.response.use(response => {
    //接收到响应数据并成功后的一些共有的处理，关闭loading等
    console.log('r',response)
    // console.log('error>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',response)
    if(response.status == 200) {
        let data = response.data;
        // console.log("data.code",data.data.code)
        if(data.code == 401) {
            // Message.error(data.message)
			uni.showToast({
				title: data.message,
				duration:1500
			})
			setTimeout(function() {
				uni.redirectTo({
				    url: "../login/login"
				})
			}, 1500);
           
            return false
        }else if(data.code == 2){
        }
        return data
    } else {
        return false
    }
    return response
}, error => {
    console.log('e',error)
    /***** 接收到异常响应的处理开始 *****/
    if (error && error.response) {
        // 1.公共错误处理
        // 2.根据响应码具体处理

        switch (error.response.status) {
            case 400:
                error.message = 'リクエストエラー'
                break;
            case 401:
                error.message = '認証エラー、再度ログインしてください'
                uni.removeStorageSync('Authorization');
                uni.removeStorageSync('homeowner');
                uni.removeStorageSync('userInfo');
                break;
            case 403:
                error.message = 'アクセスが拒否されました'
                break;
            case 404:
                error.message = 'リソースが見つかりません'
                // window.location.href = "/NotFound"
                break;
            case 405:
                error.message = 'リクエストメソッドが許可されていません'
                break;
            case 408:
                error.message = 'リクエストがタイムアウトしました'
                break;
            case 500:
                error.message = 'サーバーエラーが発生しました'
                break;
            case 501:
                error.message = 'ネットワーク未実装'
                break;
            case 502:
                error.message = 'ネットワークエラー'
                break;
            case 503:
                error.message = 'サービスを利用できません'
                break;
            case 504:
                error.message = 'ネットワークタイムアウト'
                break;
            case 505:
                error.message = 'HTTPバージョンがサポートされていません'
                break;
            default:
                error.message = `接続エラー ${error.response.status}`
        }
    } else {
        // 超时处理
        if (JSON.stringify(error).includes('timeout')) {
            // Message.error('服务器响应超时，请刷新当前页')
        }
        // error.message = '连接服务器失败'
    }
    // Message.error(error.message)
    /***** 处理结束 *****/
    // 如果不需要错误处理，以上的处理过程都可省略
    return Promise.reject(error)
})
//4.导入文件
export default service
