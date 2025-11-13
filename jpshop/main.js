import App from './App'

// #ifndef VUE3
import Vue from 'vue'
import uView from '@/uni_modules/uview-ui'
import store from './store'
import socketIO from '@/common/socket.js'
Vue.use(uView)
Vue.prototype.$store = store
Vue.config.productionTip = false
Vue.prototype.$baseUrl = "https://api.card-san.jp"
Vue.prototype.socketIo = new socketIO()
App.mpType = 'app'
const app = new Vue({
    ...App,
    store
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