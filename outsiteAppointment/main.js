import App from './App'

// #ifndef VUE3
import Vue from 'vue'
import uView from '@/uni_modules/uview-ui'
Vue.use(uView)
Vue.config.productionTip = false
// Vue.prototype.$baseUrl = "https://hyk.ljcyh.com"
Vue.prototype.$baseUrl = "https://api.card-san.jp"
Vue.prototype.parseFormatNum = function parseFormatNum(number,n){
                                if(n != 0 ){
                                    n = (n > 0 && n <= 20) ? n : 2; 
                               }
                                 number = parseFloat((number + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";  
                                 var sub_val = number.split(".")[0].split("").reverse(); 
                                 var sub_xs = number.split(".")[1];  
                                 var show_html = "";  
                                 for (let i = 0; i < sub_val.length; i++){  
                                     show_html += sub_val[i] + ((i + 1) % 3 == 0 && (i + 1) != sub_val.length ? "," : "");  
                                 }  
                                 if(n == 0 ){
                                     return show_html.split("").reverse().join("");  
                                 }else{
                                 return show_html.split("").reverse().join("") + "." + sub_xs;  
                                 } 
                             }
App.mpType = 'app'
const app = new Vue({
    ...App,
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