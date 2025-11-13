import request from "./request";

// 登录接口
export function Login (data) {
    return request({
        url: "/adminapi/login",
        method: "POST",
        data:data
    });
}

// 获取首页数据
export function getIndexData () {
    return request({
        url: "/adminapi",
        method: "GET"
    });
}

// 获取销售数据
export function getYearSale (data) {
    return request({
        url: "/adminapi/analyse/sale?year="+data,
        method: "GET"
    });
}

// 获取解约数据
export function getOffSale (data) {
    return request({
        url: "/adminapi/analyse/vip?year="+data,
        method: "GET"
    });
}

// 获取用户列表
export function getCustomerList (data) {
    return request({
        url: "adminapi/shop?page="+data.page+"&page_size=50&keywords="+data.keywords+"&start_time="+data.start_time+"&status="+data.status+"&end_time="+data.end_time,
        method: "GET"
    });
}

// 获取用户详情
export function getCustomerDetail (data) {
    return request({
        url: "adminapi/shop/"+data,
        method: "GET"
    });
}

// 店铺通知
export function shopNotice (data) {
    return request({
        url: "adminapi/notice/shop?page_size=50&page="+data,
        method: "GET"
    });
}

// 新增店铺通知
export function addShopNotice (data) {
    return request({
        url: "adminapi/notice",
        method: "POST",
        data:data
    });
}

// 编辑店铺通知
export function editShopNotice (data) {
    return request({
        url: "adminapi/notice/"+data.id,
        method: "POST",
        data:data
    });
}

// 删除店铺通知
export function delShopNotice (data) {
    return request({
        url: "adminapi/notice/"+data,
        method: "POST",
        data:{"_method":"delete"}
    });
}

// 顾客通知
export function customerNotice (data) {
    return request({
        url: "adminapi/notice/user?page_size=50&page="+data,
        method: "GET"
    });
}

//新增顾客通知
export function addCustomerNotice (data) {
    return request({
        url: "adminapi/notice",
        method: "POST",
        data:data
    });
}

// 商铺列表
export function getGoodsList (data) {
    return request({
        url: "adminapi/goods?page_size=50&page="+data,
        method: "GET"
    });
}



// 删除商品
export function deleteGoods (data) {
    return request({
        url: "adminapi/goods/"+data.id,
        method: "POST",
        data:data
    });
}

// 编辑用户信息
export function editCustomer (data) {
    return request({
        url: "adminapi/shop/"+data.id,
        method: "POST",
        data:data
    });
}
// 添加商品
export function addGoods (data) {
    return request({
        url: "adminapi/goods",
        method: "POST",
        data:data
    });
}

// 编辑商品
export function editGoods (data) {
    return request({
        url: "adminapi/goods/"+data.id,
        method: "POST",
        data:data
    });
}

// 获取系统配置
export function getConfig (data) {
    return request({
        url: "api/config",
        method: "GET"
    });
}

// 获取订单详情
export function getGoodsDetail (data) {
    return request({
        url: "adminapi/goods/"+data,
        method: "GET"
    });
}

// 修改使用须知
export function editPricy (data) {
    return request({
        url: "adminapi/config/store",
        method: "POST",
        data:data
    });
}

export function editConfig (data) {
    return request({
        url: "adminapi/config/store",
        method: "POST",
        data:data
    });
}
