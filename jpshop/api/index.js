import request from '../utils/request.js';

/**
 * 获取验证码登录接口
 */
export const Login = (data) => {
    return request({
        url: '/api/shop/login',
        method: 'post',
        data: data
    })
}


// 绑定ins
export const bindIns = (data) => {
    return request({
        url: '/api/shop/instagram/create_access_token',
        method: 'post',
        data: data
    })
}

// 获取会员列表接口
export const getMemberList = (data) => {
    return request({
        url: '/api/shop/member?page=' + data.page + "&page_size=" + data.page_size + "&keywords=" + data
            .keywords + "&sort=" + data.sort + "&desc=" + data.desc,
        method: 'get',
    })
}

// 筛选会员
export const sceneMember = (data) => {
    return request({
        url: '/api/shop/member?birthday_month=' + data.birthday_month + '&sex=' + data.sex + '&shop_time=' +
            data.shop_time + '&shop_time_desc=' + data.shop_time_desc + '&shop_count=' + data.shop_count +
            '&shop_count_desc=' + data.shop_count_desc,
        method: 'get',
    })
}

// 删除会员
export const delMember = (data) => {
    return request({
        url: '/api/shop/member/' + data.id,
        method: "post",
        data: {
            "_method": "delete"
        }
    })
}

// 获取会员信息详情
export const getMemberDetail = (data) => {
    return request({
        url: '/api/shop/member/' + data.id+"?show_message=0",
        method: "get",
    })
}

export const getMemberDetaill = (data) => {
    return request({
        url: '/api/shop/member/' + data.id+"?show_message=1",
        method: "get",
    })
}

// 会员管理的上传图片
export const uploadMemberImage = (data, id) => {
    return request({
        url: '/api/shop/member/upload/' + id,
        method: "post",
        data: {
            "image": data.img
        }
    })
}

// 删除会员预览图片
export const deleteMemberImage = (data, id) => {
    return request({
        url: '/api/shop/member/upload_delete/' + id,
        method: "post",
        data: {
            "k": data
        }
    })
}

// 编辑顾客信息
export const editMemberInformation = (data, id) => {
    return request({
        url: '/api/shop/member/' + id,
        method: "post",
        data: data
    })
}

// 获取店铺信息接口
export const getShopInfo = () => {
    return request({
        url: "/api/shop/shop",
        method: "get"
    })
}

// 优惠券列表
export const getCouponList = (data) => {
    return request({
        url: "/api/shop/coupon",
        method: "get",
    })
}

// 优惠券详情接口
export const getCouponDetail = (data) => {
    return request({
        url: "/api/shop/coupon/" + data,
        method: "get",
    })
}

// 删除优惠券
export const deleteCoupon = (data) => {
    return request({
        url: "/api/shop/coupon/" + data,
        method: "post",
        data: {
            "_method": "delete"
        }
    })
}

// 添加优惠券
export const addCoupon = (data) => {
    return request({
        url: "/api/shop/coupon",
        method: "post",
        data: data
    })
}

// 编辑优惠券
export const editCoupon = (data, id) => {
    return request({
        url: "/api/shop/coupon/" + id,
        method: "post",
        data: data
    })
}

// 获取通知列表
export const getNoticeList = (data) => {
    return request({
        url: "/api/shop/notice",
        method: "get",
    })
}

// 新增通知
export const addNotice = (data) => {
    return request({
        url: "/api/shop/notice",
        method: "post",
        data: data
    })
}

// 修改通知
export const editNotice = (data, id) => {
    return request({
        url: "/api/shop/notice/" + id,
        method: "post",
        data: data
    })
}

// 删除通知
export const deleteNotice = (id) => {
    return request({
        url: "/api/shop/notice/" + id,
        method: "post",
        data: {
            "_method": "delete"
        }
    })
}

// 获取菜单列表
export const menuList = () => {
    return request({
        url: "/api/shop/menu",
        method: "get",
    })
}

// 获取菜单分类列表
export const menuCateList = () => {
    return request({
        url: "/api/shop/menu_cate",
        method: "get",
    })
}

// 添加菜单选项
export const addMenu = (data) => {
    return request({
        url: "/api/shop/menu",
        method: "post",
        data: data
    })
}

// 编辑菜单选项
export const editMenu = (data) => {
    return request({
        url: "/api/shop/menu/"+data.id,
        method: "post",
        data: data
    })
}

// 删除菜单选项
export const deleteMenu = (data) => {
    return request({
        url: "/api/shop/menu/"+data,
        method: "post",
        data: {"_method":"delete"}
    })
}

// 添加菜单分类
export const addMenuCates = (data) => {
    return request({
        url: "/api/shop/menu_cate",
        method: "post",
        data: data
    })
}

// 删除菜单分类
export const deleteMenuCates = (id) => {
    return request({
        url: "/api/shop/menu_cate/" + id,
        method: "post",
        data: {
            "_method": "delete"
        }
    })
}

// 编辑菜单分类
export const editMenuCates = (data, id) => {
    return request({
        url: "/api/shop/menu_cate/" + id,
        method: "post",
        data: data
    })
}

// 获取日历营业时间
export const getCalendar = (data) => {
    return request({
        url: "/api/shop/calendar?type=" + data.type + '&date=' + data.date,
        method: "get",
    })
}

// 设置营业时间
export const setBusinessHours = (data) => {
    return request({
        url: "/api/shop/calendar",
        method: "post",
        data: data
    })
}

// 获取副管理者列表
export const getAdminList = () => {
    return request({
        url: "/api/shop/admin?type=2",
        method: "get",
    })
}

// 添加员工
export const addAdmin = (data) => {
    return request({
        url: "/api/shop/admin",
        method: "post",
        data: data
    })
}

// 获取副管理者详情
export const getAdminDetail = (id) => {
    return request({
        url: "/api/shop/admin/" + id,
        method: "get",
    })
}

// 编辑员工信息
export const eidtAdmin = (data, id) => {
    return request({
        url: "/api/shop/admin/" + id,
        method: "post",
        data: data
    })
}

// 删除员工
export const deleteAdmin = (id) => {
    return request({
        url: "/api/shop/admin/" + id,
        method: "post",
        data: {
            "_method": "delete"
        }
    })
}

// 获取员工列表
export const getStaffList = (data) => {
    return request({
        url: "/api/shop/admin?smid=" + data.id + "&start_time=" + data.start_time +
            "&end_time=" + data.end_time,
        method: "get"
    })
}


// 获取住管理者的信息
export const getOwnerInformation = (data) => {
    return request({
        url: "/api/shop/admin/owner",
        method: "get"
    })
}

// 获取邮编地址
export const getAddress = (code) => {
    return request({
        url: "/tool/zipcode?zip_code="+code,
        method: "get"
    })
}

// 设置主管理者信息
export const setOwnerInformation = (data) => {
    return request({
        url: "/api/shop/admin/owner",
        method: "post",
        data:data
    })
}

// 获取预约订单列表
export const getAppointmentList = (data) => {
    return request({
        url: "/api/shop/order?date="+data.date+"&type="+data.type+"&said="+data.id,
        method: "get",
    })
}

// 获取固定日期预约列表
export const getAppointmentListDay = (data) => {
    return request({
        url: "/api/shop/order?date="+data.date+"&type=day",
        method: "get",
    })
}

// 获取来电履历
export const getCome = (data) => {
    return request({
        url: "/api/shop/order?type="+data.type+"&mid="+data.id,
        method: "get",
    })
}

// 获取来电履历
export const getScan = (data) => {
    return request({
        url: "/api/shop/order?type=come&date="+data.date,
        method: "get",
    })
}

// 设置店铺信息
export const setShopInfo = (data) => {
    return request({
        url: "/api/shop/shop/info",
        method: "post",
        data:data
    })
}

// 获取会员卡列表  
export const getCardList = (type) => {
    return request({
        url: "/api/shop/card?page=1&page_size=100&type="+type,
        method: "get"
    })
}

// 设置默认会员卡
export const setDefaultCard = (id) => {
    return request({
        url: "/api/shop/card/"+id,
        method: "post",
        data:{"_method":"put","update_type":"default"}
    })
}
 
// 制作会员卡
export const addMembershipCard = (data) => {
    return request({
        url: "/api/shop/card",
        method: "post",
        data:data
    })
}

// 删除会员卡
export const deleteMembershipCard = (id) => {
    return request({
        url: "/api/shop/card/"+id,
        method: "post",
        data:{"_method":"delete"}
    })
}

// 获取支付方法
export const getPayList = () => {
    return request({
        url: "/api/shop/pay_card",
        method: "get"
    })
}

// 获取绑定的卡信息
export const getCardInfo = () => {
    return request({
        url: "/api/shop/pay_card?type=1",
        method: "get"
    })
}

// 添加支付方式
export const addPayMethod = (data) => {
    return request({
        url: "/api/shop/pay_card",
        method: "post",
        data:data
    })
}

// 获取支付详情
export const getPayDetail = (id) => {
    return request({
        url: "/api/shop/pay_card/"+id,
        method: "get"
    })
}

// 编辑支付方式
export const editPayMethod = (data,id) => {
    return request({
        url: "/api/shop/pay_card/"+id,
        method: "post",
        data:data
    })
}

// 删除支付方法
export const deletePayMethod = (data) => {
    return request({
        url: "/api/shop/pay_card/"+data,
        method: "post",
        data:{"_method":"delete"}
    })
}

// 获取商品列表
export const getGoodsList = (data) => {
    return request({
        url: "/api/shop/goods?page="+data.page+"&page_size="+data.page_size,
        method: "get"
    })
}

// 获取商品详情
export const getGoodsDetail = (data) => {
    return request({
        url: "/api/shop/goods/"+data,
        method: "get"
    })
}

// 获取地址列表
export const getAddressList = (data) => {
    return request({
        url: "/api/shop/shop_addr",
        method: "get"
    })
}

// 添加收货地址
export const addAddress = (data) => {
    return request({
        url: "/api/shop/shop_addr",
        method: "post",
        data:data
    })
}

// 删除收货地址
export const delAddress = (data) => {
    return request({
        url: "/api/shop/shop_addr/"+data,
        method: "post",
        data:{"_method":"delete"}
    })
}

// 商品下单
export const orderConfirm = (data) => {
    return request({
        url: "/api/shop/shop_order",
        method: "post",
        data:data
    })
}

// // 获取消息列表
export const getMessageList = (data) => {
    return request({
        url: "/api/shop/message?type="+data.type+"&uid="+data.uid+"&mid="+data.mid+"&timestamp="+data.timestamp,
        method: "get"
    })
}

// 设置发送消息的账户信息
export const changeChatInfo = (data) => {
    return request({
        url: "/api/shop/message/set",
        method: "post",
        data:data
    })
}

// 会员管理信息
export const memberSettings = (data) => {
    return request({
        url: "/api/shop/shop/info",
        method: "post",
        data:data
    })
}

// 设置员工预约信息
export const setStaffAppointInfo = (data) => {
    return request({
        url: "/api/shop/admin/admin3_set/"+data.id,
        method: "post",
        data:data
    })
}

// 设定是否开启指名
export const setPoint = (data) => {
    return request({
        url: "/api/shop/shop/info",
        method: "post",
        data:data
    })
}

// 添加新的预约
export const addNewAppoint = (data) => {
    return request({
        url: "/api/shop/order",
        method: "post",
        data:data
    })
}

// 预约通知列表
export const getAppointNoticeList = (page) => {
    return request({
        url: "/api/shop/order_notice",
        method: "get"
    })
}

// 编辑预约通知
export const changeAppointNotice = (data,id) => {
    return request({
        url: "/api/shop/order_notice/"+id,
        method: "post",
        data:data
    })
}

// 删除预约通知
export const delAppointNotice = (id) => {
    return request({
        url: "/api/shop/order_notice/"+id,
        method: "post",
        data:{"_method":"delete"}
    })
}

// 获取预约订单详情
export const getAppointOrderDetail = (id) => {
    return request({
        url: "/api/shop/order/"+id,
        method: "get"
    })
}

//  编辑预约订单
export const editAppointOrder = (data,id) => {
    return request({
        url: "/api/shop/order/"+id,
        method: "post",
        data:data
    })
}

// 删除预约订单
export const deleteAppointOrder = (id) => {
    return request({
        url: "/api/shop/order/"+id,
        method: "post",
        data:{"_method":"delete"}
    })
}

export const deleteAppointOrders = (id) => {
    return request({
        url: "/api/shop/order/del_come/"+id,
        method: "post",
    })
}

// 获取定时消息列表 
export const getTimingList = () => {
    return request({
        url: "/api/shop/timing_message",
        method: "get",
    })
}

// 新增定时消息
export const addNewTiming = (data) => {
    return request({
        url: "/api/shop/timing_message",
        method: "post",
        data:data
    })
}

// 删除定时消息
export const deleteTiming = (id) => {
    return request({
        url: "/api/shop/timing_message/"+id,
        method: "post",
        data:{"_method":"delete"}
    })
}

// 获取定时消息详情
export const getTimingDetail = (id) => {
    return request({
        url: "/api/shop/timing_message/"+id,
        method: "get",
    })
}

// 修改定时消息
export const editTiming = (data,id) => {
    return request({
        url: "/api/shop/timing_message/"+id,
        method: "post",
        data:data
    })
}

// 绑定socket (商铺端)
export const bindSocket = (id) => {
    return request({
        url: "/api/shop/auth/bind_socket",
        method: "post",
        data:{"client_id":id}
    })
}

// 绑定推送
export const bindPush = (id) => {
    return request({
        url: "/api/shop/auth/bind_clientid",
        method: "post",
        data:{"client_id":id}
    })
}



// 删除消息列表
// export const bindSocket = (id) => {
//     return request({
//         url: "/api/shop/auth/bind_socket",
//         method: "post",
//         data:{"client_id":id}
//     })
// }

// 支付接口  (商品)
export const payOrder = (data) => {
    return request({
        url: "/api/shop/shop_order/to_pay",
        method: "post",
        data:data
    })
}

// 支付接口 (额外次数)
export const payExtra = (data) => {
    return request({
        url: "/api/shop/vip_count/to_pay",
        method: "post",
        data:data
    })
}

// 发送消息
export const sendMessage = (data) => {
    return request({
        url: "/api/shop/message",
        method: "post",
        data:data
    })
}

// 消息已读
export const hasRead = (data) => {
    return request({
        url: "/api/shop/message/read",
        method: "post",
        data:data
    })
}

// 修改顾客默认会员卡  
export const changeDefaultCard = (data) => {
    return request({
        url: "/api/shop/member/change_card",
        method: "post",
        data:data
    })
}

// 增加印章、积分   
export const addPoints = (data) => {
    return request({
        url: "/api/shop/member/plus",
        method: "post",
        data:data
    })
}

// 获取ins授权url  
export const getInsUrl = (data) => {
    return request({
        url: "/api/shop/instagram/get_auth_url",
        method: "get",
    })
}

// 获取系统配置
export const getConfig = () => {
    return request({
        url: "/api/config",
        method: "get"
    })
}

// 运营者通知
export const getYY = (type) => {
    return request({
        url: "/api/shop/admin_notice?type="+type,
        method: "get"
    })
}

// 获取优惠券信息
export const getCouponINfo = (id) => {
    return request({
        url: "/api/shop/coupon/use?id="+id,
        method: "get"
    })
}

// 使用会员卡
export const useCoupon = (data) => {
    return request({
        url: "/api/shop/coupon/use",
        method: "post",
        data:data
    })
}

// 获取会员卡详情
export const getCardDetail = (id) => {
    return request({
        url: "/api/shop/card/"+id,
        method: "get"
    })
}

// 编辑会员卡
export const editCard = (data) => {
    return request({
        url: "/api/shop/card/"+data.id,
        method: "post",
        data:data
    })
}

// 修改登录邮箱
export const changeMail = (data) => {
    return request({
        url: "/api/shop/admin/update_email",
        method: "post",
        data:data
    })
}

// 获取契约套餐 
export const getPackageList = (data) => {
    return request({
        url: "/api/shop/vip",
        method: "get"
    })
}

// 购买契约套餐
export const buyPlan = (data) => {
    return request({
        url: "/api/shop/vip",
        method: "post",
        data:data
    })
}

// 解约
export const delPlan = (id) => {
    return request({
        url: "/api/shop/vip/"+id,
        method: "post",
        data:{"_method":"delete"}
    })
}

// 利用明细
export const getPayDetails = (data) => {
    return request({
        url: "/api/shop/pay_log?date="+data,
        method: "get"
    })
}

// 购买额外使用次数
export const getExtra = (data) => {
    return request({
        url: "/api/shop/vip_count",
        method: "post",
        data:data
    })
}

// 获取pdf下载地址
export const getpdf = (data) => {
    return request({
        url: "/api/shop/pay_log/pdf?date="+data,
        method: "get"
    })
}

// 注销账户
export const cancellation = (data) => {
    return request({
        url: "/api/shop/write_off",
        method: "post",
    })
}

// 设置自定义颜色
export const settingDiyColor = (data) => {
    return request({
        url: "/api/shop/shop/info",
        method: "post",
        data:data
    })
}

// 更新过期的会员卡
export const refreshCard = (data) => {
    return request({
        url: "/api/shop/shop/info",
        method: "post",
        data:data
    })
}

// 发送更改信息
export const cahngeInof = (data) => {
    return request({
        url: "/api/shop/order/send_update_message",
        method: "post",
        data:{"oid":data}
    })
}


// 获取可用的当日担当  
export const getTodayAdmin = (data) => {
    return request({
        url: "/api/shop/admin?smid=" + data.id + "&start_time=" + data.start_time +
            "&end_time=" + data.end_time + "&oid="+data.oid,
        method: "get"
    })
}

// 查询导出数据
export const exportData = (data) => {
    return request({
        url: '/api/shop/member?birthday_month=' + data.birthday_month + '&sex=' + data.sex + '&shop_time=' +
            data.shop_time + '&shop_time_desc=' + data.shop_time_desc + '&shop_count=' + data.shop_count +
            '&shop_count_desc=' + data.shop_count_desc + '&export=1',
        method: 'get',
    })
}

// 契约支付
export const buyPlans = (data) => {
    return request({
        url: '/api/shop/vip/pay',
        method: 'post',
        data:data
    })
}

// 额外使用次数下单 (alias of getExtra)
export const buyextras = getExtra

// 额外使用次数支付 (alias of payExtra)
export const buyextra = payExtra

// 删除聊天记录
export const delMsg = (data) => {
    return request({
        url: "/api/shop/message/del",
        method: "post",
        data:data
    })
}

// 菜单排序
export const menucatesort = (data) => {
    return request({
        url: "/api/shop/menu_cate/set_sort",
        method: "post",
        data:data
    })
}

export const menusort = (data) => {
    return request({
        url: "/api/shop/menu/set_sort",
        method: "post",
        data:data
    })
}

export const staffSort = (data) => {
    return request({
        url: "/api/shop/admin/set_sort",
        method: "post",
        data:data
    })
}
// 评论按钮设置
export const reviewSetting = (data) => {
    return request({
        url: "/api/shop/message/set",
        method: "post",
        data:data
    })
}

