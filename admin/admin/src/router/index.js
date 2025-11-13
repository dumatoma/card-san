import Vue from 'vue'
import Router from 'vue-router'
import Home from '@/components/Home'
import Login from '@/components/Login'
import Sale from '@/components/Sale'
import Offtrade from '@/components/Offtrade'
import Settlement from '@/components/Settlement'
import settlementDetail from '@/components/settlementDetail'
import Customer from '@/components/Customer'
import CustomerDetail from '@/components/CustomerDetail'
import CustomerEdit from '@/components/CustomerEdit'
import Notice from '@/components/Notice'
import NoticeCustomer from '@/components/NoticeCustomer'
import Shop from '@/components/Shop'
import NewGoods from "@/components/NewGoods"
import Rules from "@/components/Rule"
import Edit from "@/components/editGoods"
import Privicy from "@/components/privicy"
import Rule from "@/components/rules"
import Alerts from "@/components/Alerts"
Vue.use(Router)
const originalPush = Router.prototype.push
Router.prototype.push = function push(location) {
  return originalPush.call(this, location).catch(err => err)
}
export default new Router({
  routes: [{
      path: '/',
      name: 'Home',
      component: Home
    },
    {
      path: '/Privicy',
      name: 'Privicy',
      component: Privicy
    },
    {
      path: '/Rule',
      name: 'Rule',
      component: Rule
    },
    {
      path: '/Home',
      name: 'Home',
      component: Home
    },
    {
      path: '/Login',
      name: 'Login',
      component: Login
    },
    {
      path: '/Sale',
      name: 'Sale',
      component: Sale
    },
    {
      path: '/Offtrade',
      name: 'Offtrade',
      component: Offtrade
    },
    {
      path: '/Settlement',
      name: 'Settlement',
      component: Settlement
    },
    {
      path: '/settlementDetail',
      name: 'settlementDetail',
      component: settlementDetail
    },
    {
      path: '/Customer',
      name: 'Customer',
      component: Customer
    },
    {
      path: "/CustomerDetail",
      name: "CustomerDetail",
      component: CustomerDetail
    },
    {
      path: "/CustomerEdit",
      name: "CustomerEdit",
      component: CustomerEdit
    },
    {
      path: "/Notice",
      name: "Notice",
      component: Notice
    },
    {
      path: "/NoticeCustomer",
      name: "NoticeCustomer",
      component: NoticeCustomer
    },
    {
      path: "/Shop",
      name: "Shop",
      component: Shop
    },
    {
      path: "/NewGoods",
      name: "NewGoods",
      component: NewGoods
    },
    {
      path: "/Rules",
      name: "Rules",
      component: Rules
    },
    {
      path: "/Edit",
      name: "Edit",
      component: Edit
    },
    {
      path: "/Alerts",
      name: "Alerts",
      component: Alerts
    }
  ]
})
