import App from './App'

// #ifndef VUE3
import Vue from 'vue'
import uView from '@/uni_modules/uview-ui'
Vue.config.productionTip = false
App.mpType = 'app'
// Vue.prototype.$baseUrl = "https://hyk.ljcyh.com"
Vue.prototype.$baseUrl = "https://api.card-san.jp"

// baseURL: 'https://hyk.ljcyh.com',//测试地址
Vue.use(uView)
const app = new Vue({
    ...App
})
app.$mount()
// #endif

// #ifdef VUE3
import { createSSRApp } from 'vue'
export function createApp() {
  const app = createSSRApp(App)
  return {
    app
  }
}
// #endif