import Vue from 'vue'
import VueRouter from 'vue-router'
import HomeView from '../views/HomeView.vue'
import register from '../views/login/register.vue'
import login from '../views/login/login.vue'
import code from '../views/login/codeLogin.vue'
import phone from '../views/login/phoneLogin.vue'
import deputyAdmin from '../views/login/deputyAdmin.vue'
import FirstStep from '../views/variousSet/firstStep.vue'

Vue.use(VueRouter)
const originalPush = VueRouter.prototype.push;
const originalReplace = VueRouter.prototype.replace;
//push
VueRouter.prototype.push = function push(location, onResolve, onReject) {
  if (onResolve || onReject)
    return originalPush.call(this, location, onResolve, onReject);
  return originalPush.call(this, location).catch(err => err);
};
//replace
VueRouter.prototype.replace = function push(location, onResolve, onReject) {
  if (onResolve || onReject)
    return originalReplace.call(this, location, onResolve, onReject);
  return originalReplace.call(this, location).catch(err => err);
};
const routes = [
  {
    path: '/register',
    name: 'register',
    component: register
  }, {
    path: '/code',
    name: 'code',
    component: code
  }, {
    path: '/deputyAdmin',
    name: 'deputyAdmin',
    component: deputyAdmin
  },{
    path: '/phone',
    name: 'phone',
    component: phone
  }, {
    path: '/login',
    name: 'login',
    component: login
  }, {
    path: '/loHomeViewgin',
    name: 'HomeView',
    component: HomeView
  }, {
    path: '/about',
    name: 'about',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import('../views/AboutView.vue')
  },
  {
    path: '/message',
    name: 'message',
    component: () => import('../views/message.vue')
  },
  {
    path: '/delivery',
    name: 'delivery',
    component: () => import('../views/deliveryHistory.vue')
  }, 
  {
    path: '/mass',
    name: 'mass',
    component: () => import('../views/mass.vue')
  },
  {
    path: '/',
    name: 'home',
    component: () => import('../views/topIndex.vue')
  }, {
    path: '/help',
    name: 'help',
    component: () => import('../views/help.vue')
  }, {
    path: '/topIndex',
    name: 'home',
    component: () => import('../views/topIndex.vue')
  }, {
    path: '/manage',
    name: 'manage',
    component: () => import('../views/manage.vue')
  }, {
    path: '/editinfo',
    name: 'editinfo',
    component: () => import('../views/editinfo.vue')
  }, {
    path: '/setting',
    name: 'setting',
    component: () => import('../views/automaticSetting.vue')
  }, {
    path: '/accountInfo',
    name: 'accountInfo',
    component: () => import('../views/messsageInformation.vue')
  }, {
    path: '/staffRoomSet',
    name: 'staffRoomSet',
    component: () => import('../views/staffRoomSetting.vue')
  }, {
    path: '/roomChat',
    name: 'roomChat',
    component: () => import('../views/roomChat.vue')
  },
  {
    path: '/cardInfo',
    name: 'cardInfo',
    component: () => import('../views/downloads.vue')
  },{
    path: '/download',
    name: 'download',
    component: () => import('../views/download.vue')
  }, {
    path: '/notice',
    name: 'notice',
    component: () => import('../views/notice.vue')
  }, {
    path: '/appointment',
    name: 'appointment',
    component: () => import('../views/appointment.vue')
  }, {
    path: '/appointmentSetting',
    name: 'appointmentSetting',
    component: () => import('../views/appointmentSetting.vue')
  }, {
    path: '/noticeSet',
    name: 'noticeSet',
    component: () => import('../views/noticeSet.vue')
  }, {
    path: '/contribution',
    name: 'contribution',
    component: () => import('../views/contribution.vue')
  }, {
    path: '/coupon',
    name: 'coupon',
    component: () => import('../views/coupon.vue')
  }, {
    path: '/foundCoupon',
    name: 'foundCoupon',
    component: () => import('../views/foundCoupon.vue')
  }, {
    path: '/projectList',
    name: 'projectList',
    component: () => import('../views/projectList.vue')
  }, {
    path: '/projectDetail',
    name: 'projectDetail',
    component: () => import('../views/projectDetail.vue')
  }, {
    path: '/order',
    name: 'order',
    component: () => import('../views/order.vue')
  }, {
    path: '/orderContent',
    name: 'orderContent',
    component: () => import('../views/orderContent.vue')
  }, {
    path: '/address',
    name: 'address',
    component: () => import('../views/address.vue')
  }, {
    path: '/storeInfo',
    name: 'storeInfo',
    component: () => import('../views/variousSet/storeInfo.vue')
  }, {
    path: '/member',
    name: 'member',
    component: () => import('../views/variousSet/member.vue')
  },{
    path: '/firstStep',
    name: 'firstStep',
    component:FirstStep,
  }, {
    path: '/markPrinting',
    name: 'markPrinting',
    component: () => import('../views/variousSet/markPrinting.vue')
  }, {
    path: '/designSet',
    name: 'designSet',
    component: () => import('../views/variousSet/designSet.vue')
  }, {
    path: '/typeRegister',
    name: 'typeRegister',
    component: () => import('../views/variousSet/typeRegister.vue')
  }, {
    path: '/menuList',
    name: 'menuList',
    component: () => import('../views/variousSet/menuList.vue')
  }, {
    path: '/menuRegister',
    name: 'menuRegister',
    component: () => import('../views/variousSet/menuRegister.vue')
  }, {
    path: '/utilizePlan',
    name: 'utilizePlan',
    component: () => import('../views/plan/utilizePlan.vue')
  },{
    path: '/planConfirm',
    name: 'planConfirm',
    component: () => import('../views/plan/planconfirm.vue')
  }, {
    path: '/changeContract',
    name: 'changeContract',
    component: () => import('../views/plan/changeContract.vue')
  }, {
    path: '/content',
    name: 'content',
    component: () => import('../views/plan/content.vue')
  }, {
    path: '/termination',
    name: 'termination',
    component: () => import('../views/plan/termination.vue')
  }, {
    path: '/terminationFulfil',
    name: 'terminationFulfil',
    component: () => import('../views/plan/terminationFulfil.vue')
  }, {
    path: '/clause',
    name: 'clause',
    component: () => import('../views/plan/clause.vue')
  },  {
    path: '/edition',
    name: 'edition',
    component: () => import('../views/plan/edition.vue')
  }, {
    path: '/deputyAdministrator',
    name: 'deputyAdministrator',
    component: () => import('../views/variousSet/deputyAdministrator.vue')
  }, {
    path: '/user',
    name: 'user',
    component: () => import('../views/userInfo/userInfo.vue')
  },{
    path: '/fu',
    name: 'fu',
    component: () => import('../views/userInfo/fu.vue')
  }, {
    path: '/payMethod',
    name: 'payMethod',
    component: () => import('../views/userInfo/paymentMethod.vue')
  }, {
    path: '/detail',
    name: 'detail',
    component: () => import('../views/userInfo/Details.vue')
  }, {
    path: '/deputyLook',
    name: 'deputyLook',
    component: () => import('../views/variousSet/deputyLook.vue')
  }, {
    path: '/registration',
    name: 'registration',
    component: () => import('../views/variousSet/registration.vue')
  }, {
    path: '/personnel',
    name: 'personnel',
    component: () => import('../views/variousSet/personnel.vue')
  }, {
    path: '/memberInfo',
    name: 'memberInfo',
    component: () => import('../views/variousSet/memberInfo.vue')
  }, {
    path: '/calendar',
    name: 'calendar',
    component: () => import('../views/variousSet/calendar.vue')
  }, {
    path: '/time',
    name: 'time',
    component: () => import('../views/variousSet/time.vue')
  },{
    path: '/editFirst',
    name: 'editFirst',
    component: () => import('../views/variousSet/editFirst.vue')
  },{
    path: '/editCard',
    name: 'editCard',
    component: () => import('../views/variousSet/editCard.vue')
  },{
    path: '/outappoint',
    name: 'outappoint',
    component: () => import('../views/outappoint.vue')
  },
  ,{
    path: '/reviewSetting',
    name: 'reviewSetting',
    component: () => import('../views/reviewSetting.vue')
  },
  ,{
    path: '/moduleSetting',
    name: 'moduleSetting',
    component: () => import('../views/moduleSetting.vue')
  },
]

const router = new VueRouter({
  mode: 'hash',
  base: process.env.BASE_URL,
  routes
})

export default router
