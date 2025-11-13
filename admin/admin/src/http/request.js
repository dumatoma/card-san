/****   request.js   ****/
// 导入axios
import axios from 'axios'
// 使用element-ui Message做消息提醒
// import { Message} from 'element-ui';
//1. 创建新的axios实例，
const service = axios.create({
  // 公共接口--这里注意后面会讲
    // baseURL: 'https://hyk.ljcyh.com',//测试地址''
    baseURL: 'https://api.card-san.jp',//测试地址''
  // 超时时间 单位是ms，这里设置了3s的超时时间
  timeout: 500000 * 1000
})
// 4003
// 2.请求拦截器
service.interceptors.request.use(config => {
  config.data = JSON.stringify(config.data)
  config.headers = {
    'Content-Type': 'application/json;charset=UTF-8',
    'client':'pc'
  }
  let token = localStorage.getItem("token")

  if(token){
      let aid = localStorage.getItem("aid")
      config.headers = {
          'Content-Type':'application/json;charset=UTF-8',
          'token': token,
          "aid": aid,
          "api": "1"
      }
  }
  return config
}, error => {
  Promise.reject(error)
})

// 3.响应拦截器
service.interceptors.response.use(response => {
  if (response.status == 200) {
    let data = response.data;
    if (data.code == 401) {
     // alert("未登录")
     window.location.href = "https://customaid.card-san.jp/#/Login"
      return false
    } else if (data.code == 2) {
    }
    return data
  } else {
    return false
  }
  return response
}, error => {
  console.log('e', error)
  /***** 接收到异常响应的处理开始 *****/
  if (error && error.response) {
    switch (error.response.status) {
      case 400:
        error.message = '错误请求'
        break;
      case 401:
        error.message = '未授权，请重新登录'
        localStorage.removeItem('admin');
        localStorage.removeItem('token');
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
