import Vue from 'vue'
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import App from './App.vue'
import router from './router'
import locale from 'element-ui/lib/locale/lang/ja'
import socketPublic from '@/store/index.js'
import store from "./store"
// import http from "http/request.js";
// Vue.prototype.$url="https://hyk.ljcyh.com"

// Vue.prototype.$baseUrl="https://hyk.ljcyh.com"
Vue.prototype.$socketPublic = socketPublic

Vue.prototype.$url="https://api.card-san.jp"

Vue.prototype.$baseUrl="https://api.card-san.jp"
Vue.prototype.$hosturl="https://card-san.jp"
import vcolorpicker from 'vcolorpicker'
Vue.use(vcolorpicker)


Vue.use(ElementUI,{locale});
Vue.config.productionTip = false
// Vue.prototype.$http = http;

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')
