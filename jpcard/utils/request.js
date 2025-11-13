/****   request.js   ****/
// 导入axios
import axios from '../utils/axios/index.js'
// 使用element-ui Message做消息提醒
// import { Message} from 'element-ui';
//1. 创建新的axios实例，
const service = axios.create({
  // baseURL: 'https://hyk.ljcyh.com',//测试地址
  baseURL: 'https://api.card-san.jp',//测试地
  timeout: 5 * 1000
})
// 2.请求拦截器
service.interceptors.request.use(config => {
    config.data = JSON.stringify(config.data)
    config.headers = {
        'Content-Type':'application/json;charset=UTF-8'
    }
    let token = uni.getStorageSync("token")
    let info = uni.getStorageSync("user")
    let uuid = info.uuid
    if(token){
        config.headers = {
            'Content-Type':'application/json;charset=UTF-8',
            'token': token,
            "uuid": uuid
        }
    }
    return config
}, error => {
    console.log("errora",error)
    Promise.reject(error)
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
    
    // console.log('error>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>',response)
    if(response.status == 200) {
        let data = response.data;
        // console.log("data.code",data.data.code)
        if(data.code == 401) {
            // console.log('r',response)
            // let token = uni.getStorageSync("token")
            console.log("401data",data)
            Message.error(data.message)
			uni.showToast({
				title: "401"+data.message,
				duration:1500,
                icon:"none"
			})
			setTimeout(function() {
                // uni.removeStorageSync("user")
                // uni.removeStorageSync("token")
				uni.redirectTo({
				    url: "@/pages/login/login"
				})
                return false
			}, 1500);
           
            
        }else if(data.code == 2){
        }
        return data
    } else {
        console.log("error")
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
                error.message = '错误请求'
                break;
            case 401:
                error.message = '未授权，请重新登录'
                localStorage.removeItem('Authorization');
                localStorage.removeItem('homeowner');
                localStorage.removeItem('userInfo');
                break;
            case 403:
                error.message = '拒绝访问'
                break;
            case 404:
                error.message = '请求错误,未找到该资源'
                // window.location.href = "/NotFound"
                break;
            case 405:
                error.message = '请求方法未允许'
                break;
            case 408:
                error.message = '请求超时'
                break;
            case 500:
                error.message = '服务器端出错'
                break;
            case 501:
                error.message = '网络未实现'
                break;
            case 502:
                error.message = '网络错误'
                break;
            case 503:
                error.message = '服务不可用'
                break;
            case 504:
                error.message = '网络超时'
                break;
            case 505:
                error.message = 'http版本不支持该请求'
                break;
            default:
                error.message = `连接错误${error.response.status}`
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
    return Promise.resolve(error.response)
})
//4.导入文件
export default service
