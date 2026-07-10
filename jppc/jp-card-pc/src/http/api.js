import request from "./request";

// 登录接口
export function Login (data) {
    return request({
        url: "/api/shop/login",
        method: "POST",
        data:data
    });
}

// 获取店铺信息
export function getStore () {
    return request({
        url: "/api/shop/shop",
        method: "GET"
    });
}

export const sceneMember = (data) => {
    return request({
        url: '/api/shop/member?birthday_month=' + data.birthday_month + '&sex=' + data.sex + '&shop_time=' +
            data.shop_time + '&shop_time_desc=' + data.shop_time_desc + '&shop_count=' + data.shop_count +
            '&shop_count_desc=' + data.shop_count_desc,
        method: 'get',
    })
}

// 获取运营者通知  /
export function getYYNotice () {
    return request({
        url: "/api/shop/admin_notice?type=list",
        method: "GET"
    });
}

// 获取商品信息
export function getGoodsList () {
    return request({
        url: "/api/shop/goods?page=1&page_size=500",
        method: "GET"
    });
}

// 获取商品详情
export function getGoodsDetail (id) {
    return request({
        url: "/api/shop/goods/"+id,
        method: "GET"
    });
}

// 获取会员列表
export function getMemberList (data) {
    return request({
        url: "/api/shop/member?page="+data.page+"&page_size="+data.page_size+"&keywords="+data.keywords+"&sort="+data.sort,
        method: "GET"
    });
}

// 筛选会员列表
export function sceen (data) {
    return request({
        url: "/api/shop/member?page=1&page_size=300&birthday_month="+data.birthday_month+"&sex="+data.sex+"&shop_time="+data.shop_time+"&shop_time_desc="+data.shop_time_desc+"&shop_count="+data.shop_count+"&shop_count_desc="+data.shop_count_desc,
        method: "GET"
    });
}

// 会员详情
export function getMemberDetail (data) {
    return request({
        url: "/api/shop/member/"+data,
        method: "GET"
    });
}

// 上传会员图片
export function uploadMemberImage (data) {
    return request({
        url: "/api/shop/member/upload/"+data.id,
        data:{image:data.image},
        method: "POST"
    });
}

// 删除会员图片
export function deleteMemberImage (data,index) {
    return request({
        url: "/api/shop/member/upload_delete/"+data,
        method: "POST",
        data:{k:index}
    });
}

// 编辑会员信息
export function editMemberInfo (data,id) {
    return request({
        url: "/api/shop/member/"+id,
        method: "POST",
        data:data
    });
}

// 获取通知列表
export function getNoticeList () {
    return request({
        url: "/api/shop/notice",
        method: "GET"
    });
}

// 新增同时消息 api/shop/notice
export function addNoticeList (data) {
    return request({
        url: "/api/shop/notice",
        method: "POST",
        data:data
    });
}

// 删除通知信息 
export function deleteNoticeList (data) {
    return request({
        url: "/api/shop/notice/"+data,
        method: "POST",
        data:{"_method":"delete"}
    });
}

// 编辑通知信息 
export function editNoticeList (data,id) {
    return request({
        url: "/api/shop/notice/"+id,
        method: "POST",
        data:data
    });
}

// 通知设定
export function noticeSetting (data) {
    return request({
        url: "/api/shop/shop/info",
        method: "POST",
        data:data
    });
}

// 获取优惠券列表
export function getCouponList () {
    return request({
        url: "/api/shop/coupon",
        method: "GET",
    });
}

// 新增优惠券
export function addNewCoupon (data) {
    return request({
        url: "/api/shop/coupon",
        method: "POST",
        data:data
    });
}

// 删除优惠券
export function deleteNewCoupon (data) {
    return request({
        url: "/api/shop/coupon/"+data,
        method: "POST",
        data:{"_method":"delete"}
    });
}

// 获取优惠券详情
export function getCouponDetail (data) {
    return request({
        url: "/api/shop/coupon/"+data,
        method: "GET"
    });
}

// 修改优惠券
export function editCoupon (data,id) {
    return request({
        url: "/api/shop/coupon/"+id,
        method: "POST",
        data:data
    });
}

// 获取系统配置
export function getConfig () {
    return request({
        url: "/api/config",
        method: "GET"
    });
}

// 查询邮编地址
export function gatZipAddress (data) {
    return request({
        url: "/tool/zipcode?zip_code="+data,
        method: "GET"
    });
}

// 修改店铺信息 
export const editShop = (data) => {
    return request({
        url: "/api/shop/shop/info",
        method: "post",
        data:data
    })
}

// 获取会员卡列表
export const getCardList = (data) => {
    return request({
        url: "/api/shop/card?page=1&page_size=30&type="+data,
        method: "get"
    })
}

// 设置默认会员卡
export const changeDefault = (id) => {
    return request({
        url: "/api/shop/card/"+id,
        method: "post",
        data:{"_method":"put","update_type":"default"}
    })
}

// 新增会员卡  
export const addCard = (data) => {
    return request({
        url: "/api/shop/card",
        method: "post",
        data:data
    })
}

// 获取分类  
export const getCates = () => {
    return request({
        url: "/api/shop/menu_cate",
        method: "get"
    })
}

// 删除分类  
export const deleteCate = (data) => {
    return request({
        url: "/api/shop/menu_cate/"+data,
        method: "post",
        data:{"_method":"delete"}
    })
}

// 新增分类  
export const addCate = (data) => {
    return request({
        url: "/api/shop/menu_cate",
        method: "post",
        data:{"name":data}
    })
}

// 修改分类
export const editCate = (data,id) => {
    return request({
        url: "/api/shop/menu_cate/"+id,
        method: "post",
        data:data
    })
}

// 获取菜单  
export const getMenuList = () => {
    return request({
        url: "/api/shop/menu",
        method: "get"
    })
}

// 新增菜单
export const addMenu = (data) => {
    return request({
        url: "/api/shop/menu",
        method: "post",
        data:data
    })
}

// 删除菜单
export const deleteMenu = (data) => {
    return request({
        url: "/api/shop/menu/"+data,
        method: "post",
        data:{"_method":"delete"}
    })
}

// 修改菜单
export const editMenu = (data) => {
    return request({
        url: "/api/shop/menu/"+data.id,
        method: "post",
        data:data
    })
}

// 获取员工列表
export const getStaffList = (data) => {
    return request({
        url: "api/shop/admin?start_time="+data.startTime+"&end_time="+data.endTIme+"&smid="+data.id,
        method: "get"
    })
}

// 获取所有员工
export const getAllStaff = (data) => {
    return request({
        url: "api/shop/admin?start_time=&end_time=&smid=",
        method: "get"
    })
}

// 删除会员卡
export const deleteCard = (data) => {
    return request({
        url: "api/shop/card/"+data.id,
        method: "post",
        data:{"_method":"delete"}
    })
}

// 获取来店履历
export const getComeList = (data) => {
    return request({
        url: "api/shop/order?type=come&date="+data.date+"&mid="+data.id,
        method: "get"
    })
}

// 删除来店记录  
export const deleteAppointOrders = (id) => {
    return request({
        url: "/api/shop/order/del_come/"+id,
        method: "post",
    })
}

// 获取预约列表
export const getAppointmentList = (data) => {
    return request({
        url: "/api/shop/order?date="+data.date+"&type="+data.type+"&said="+data.id+"&day=14",
        method: "get",
    })
}

// 添加新员工  
export const addStaff = (data) => {
    return request({
        url: "/api/shop/admin",
        method: "post",
        data:data
    })
}

// 删除员工  
export const deleteStaff = (data) => {
    return request({
        url: "/api/shop/admin/"+data,
        method: "post",
        data:{"_method":"delete"}
    })
}

// 编辑员工信息
export const editStaff = (data) => {
    return request({
        url: "/api/shop/admin/"+data.id,
        method: "post",
        data:data
    })
}

// 获取副管理者列表
export const getFu = () => {
    return request({
        url: "/api/shop/admin?type=2",
        method: "get"
    })
}

// 删除副管理者
export const deleteFu = (data) => {
    return request({
        url: "/api/shop/admin/"+data.id,
        method: "post",
        data:data
    })
}

// 获取副管理信息
export const getFuDetail = (id) => {
    return request({
        url: "/api/shop/admin/"+id.id,
        method: "get"
    })
}

// 添加副管理者
export const addFu = (data) => {
    return request({
        url: "/api/shop/admin/",
        method: "post",
        data:data
    })
}

// 修改副管理者信息
export const eidtAdmin = (data, id) => {
    return request({
        url: "/api/shop/admin/" + id,
        method: "post",
        data: data
    })
}



// 获取主管理者信息  
export const getUserINfo = () => {
    return request({
        url: "/api/shop/admin/owner",
        method: "get"
    })
}

// 修改主管理信息
export const editUser = (data) => {
    return request({
        url: "/api/shop/admin/owner",
        method: "post",
        data:data
    })
}

// 修改邮箱
export const changeEmail = (data) => {
    return request({
        url: "/api/shop/admin/update_email",
        method: "post",
        data:data
    })
}

// 获取支付方法列表
export const getPaymethod = () => {
    return request({
        url: "/api/shop/pay_card",
        method: "get"
    })
}


// 添加支付方式 
export const addPaymethod = (data) => {
    return request({
        url: "/api/shop/pay_card",
        method: "post",
        data:data
    })
}

// 删除支付方式
export const deletePaymethod = (data) => {
    return request({
        url: "/api/shop/pay_card/"+data,
        method: "post",
        data:{"_method":'delete'}
    })
}

// 获取支付方式详情  
export const getPayDetail = (data) => {
    return request({
        url: "/api/shop/pay_card/"+data,
        method: "get",
    })
}

// 编辑支付方式
export const editPaymethod = (data) => {
    return request({
        url: "/api/shop/pay_card/"+data.id,
        method: "post",
        data:data
    })
}

// 获取营业时间
export const getCalendar = (data) => {
    return request({
        url: "/api/shop/calendar?type=" + data.type + '&date=' + data.date,
        method: "get",
    })
}

// 设置营业时间
export const setCalendar = (data) => {
    return request({
        url: "/api/shop/calendar",
        method: "post",
        data:data
    })
}

// 设置消息开关
export const setMessageOn = (data) => {
    return request({
        url: "/api/shop/message/set",
        method: "post",
        data:data
    })
}

// 获取定时消息列表 
export const getTimingList = () => {
    return request({
        url: "/api/shop/timing_message",
        method: "get",
    })
}

// 生成定时消息
export const addTimingMessage = (data) => {
    return request({
        url: "/api/shop/timing_message",
        method: "post",
        data:data
    })
}

// 查询配信详情
export const getTimingDetail = (id) => {
    return request({
        url: "/api/shop/timing_message/"+id,
        method: "get",
    })
}

// 删除配信详情
export const deleteTimingItem = (id) => {
    return request({
        url: "/api/shop/timing_message/"+id,
        method: "post",
        data:{"_method":"delete"}
    })
}

// 修改配信详情  
export const editTimingMessage = (data,id) => {
    return request({
        url: "/api/shop/timing_message/"+id,
        method: "post",
        data:data
    })
}

// 设置店铺指名
export const setZhi = (data) => {
    return request({
        url: "/api/shop/shop/info",
        method: "post",
        data:{"type":"is_appoint","is_appoint":data}
    })
}

export const setZhi2 = (data) => {
    return request({
        url: "/api/shop/shop/info",
        method: "post",
        data:data
    })
}

// 设置员工
export const editStaffSkill = (data,id) => {
    return request({
        url: "/api/shop/admin/admin3_set/"+id,
        method: "post",
        data:data
    })
}

// 新增预约  
export const addAppointment = (data) => {
    return request({
        url: "/api/shop/order",
        method: "post",
        data:data
    })
}

// 预约通知列表
export const getAppointmentLists = (data) => {
    return request({
        url: "/api/shop/order_notice?page=1&page_size=500",
        method: "get"
    })
}

// 编辑预约通知 
export const editAppointNotice = (data) => {
    return request({
        url: "/api/shop/order_notice/"+data.id,
        method: "post",
        data:data
    })
}

// 删除预约通知
export const deleteAppointNotice = (data) => {
    return request({
        url: "/api/shop/order_notice/"+data,
        method: "post",
        data:{"_method":"delete"}
    })
}
// 已读预约通知
export const readAppointNotice = (data) => {
    return request({
        url: "/api/shop/order_notice/"+data,
        method: "post",
        data:{"_method":"put","type":"read"}
    })
}

// 获取订单详情
export const getAppointNoticeDetail = (data) => {
    return request({
        url: "/api/shop/order/"+data,
        method: "get",
    })
}

// 取消预约订单
export const deleteAppointment = (data) => {
    return request({
        url: "/api/shop/order/"+data,
        method: "post",
        data:{"_method":"delete"}
    })
}

// 编辑预约订单
export const editAppointment = (data) => {
    return request({
        url: "/api/shop/order/"+data.id,
        method: "post",
        data:data
    })
}

// 收货地址列表
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

// 下单
export const setOrder = (data) => {
    return request({
        url: "/api/shop/shop_order",
        method: "post",
        data:data
    })
}

// 设置会员默认会员卡
export const setdefaultCard = (data) => {
    return request({
        url: "/api/shop/member/change_card",
        method: "post",
        data:data
    })
}

// 增加印章卡，积分
export const addSeals = (data) => {
    return request({
        url: "/api/shop/member/plus",
        method: "post",
        data:data
    })
}

// 契约套餐列表  
export const getPackageList = (data) => {
    return request({
        url: "/api/shop/vip",
        method: "get"
    })
}

// 绑定socket
export const bindSocket = (id) => {
    return request({
        url: "/api/shop/auth/bind_socket",
        method: "post",
        data:{"client_id":id}
    })
}

// 获取消息列表
export const getMessageList = (data) => {
    return request({
        url: "/api/shop/message?type="+data.type+"&uid="+data.uid+"&mid="+data.mid+"&timestamp="+data.timestamp,
        method: "get"
    })
}

// 取消预约  
export const deleteOrder = (data) => {
    return request({
        url: "/api/shop/order/"+data,
        method: "post",
        data:{"_method":'delete'}
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

// 发送更改信息
export const cahngeInof = (data) => {
    return request({
        url: "/api/shop/order/send_update_message",
        method: "post",
        data:{"oid":data}
    })
}

// 已读消息
export const readMessage = (id) => {
    return request({
        url: "/api/shop/message/read",
        method: "post",
        data:{"uid":id}
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

// 获取绑定卡的方法
export const getBindCard = () => {
    return request({
        url: "/api/shop/pay_card?type=1",
        method: "get"
    })
}

// 解约接口
export const cancelPlan = () => {
    return request({
        url: "/api/shop/vip/0",
        method: "post",
        data:{"_method":"delete"}
    })
}

// 商铺端商户注册
export const registerAccount = (data) => {
    return request({
        url: "/api/shop/register",
        method: "post",
        data:data
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

// 获取pdf
export const getPdf = (data) => {
    return request({
        url: "/api/shop/pay_log/pdf?date="+data,
        method: "get"
    })
}

// 连接ins
export const getInsUrl = (data) => {
    return request({
        url: "/api/shop/instagram/get_auth_url",
        method: "get",
    })
}

// 获取会员卡详情
export const getCardInfo = (data) => {
    return request({
        url: "/api/shop/card/"+data,
        method: "get",
    })
}

// 编辑会员卡
export const editCardInfo = (data) => {
    return request({
        url: "/api/shop/card/"+data.id,
        method: "post",
        data:data
    })
}

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
        url: "api/shop/admin/set_sort",
        method: "post",
        data:data
    })
}


export const setpayOrder = (data) => {
    return request({
        url: "api/shop/vip/pay",
        method: "post",
        data:data
    })
}

export const offtrad = (data) => {
    return request({
        url: "api/shop/vip/"+data,
        method: "post",
        data:{'_method':'delete'}
    })
}

export const topay = (data) => {
    return request({
        url: "/api/shop/vip_count/to_pay",
        method: "post",
        data:data
    })
}
export const getAppointNoticeList = (page) => {
    return request({
        url: "/api/shop/order_notice",
        method: "get"
    })
}

// 设置评论 
export const reviewSetting = (data) => {
    return request({
        url: "/api/shop/message/set",
        method: "post",
        data:data
    })
}