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
// [カスタムエイド 2026-10-22] 完了のお知らせ(大きな ✓ の四角)を、小さな「✓ 〇〇しました」の表示に。アプリ全体の uni.showToast に効く
uni.addInterceptor('showToast', {
    invoke(args) {
        if (!args.icon || args.icon === 'success') {
            args.icon = 'none'
            args.title = '✓ ' + (args.title || '')
        }
    }
})
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