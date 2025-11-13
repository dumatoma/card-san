import request from '../utils/request.js';

// 发送注册手机验证码
export const sendPhoneCode = (data) => {
	return request({
		url: '/api/member/register',
		method: 'post',
		data: data
	})
}

//  发送登录手机验证码
export const sendLoginCode = (data) => {
	return request({
		url: '/api/member/login',
		method: 'post',
		data: data
	})
}

// 获取ins列表
export const getInsList = (data) => {
    return request({
        url: '/api/member/instagram/media?sid='+data.sid+"&after="+data.after,
        method: 'get'
    })
}
// 获取用户会员卡
export const getUserCards = (data) => {
	return request({
		url: '/api/member/member?page=' + data,
		method: 'get'
	})
}

// 获取所有未读数
export const getAllUnread = (data) => {
	return request({
		url: '/api/member/member?request_type=unread_num',
		method: 'get'
	})
}

// 获取所有未读数
export const getAllUnreadnum = (data) => {
	return request({
		url: '/api/member/member?request_type=unread_num',
		method: 'get'
	})
}

// 获取所有卡片 
export const getAllCards = (data) => {
	return request({
		url: '/api/member/card?keyword=' + data,
		method: 'get'
	})
}

// 获取附近卡片
// export const getNearCards = (data) => {
// 	return request({
// 		url: '/api/member/card?nearby=1',
// 		method: 'get'
// 	})
// }

// 获取附近卡片  card/nearby
export const getNearCards = (data) => {
	return request({
		url: '/api/member/card/nearby',
		method: 'get'
	})
}

// 查询地址
export const getAddress = (code) => {
	return request({
		url: "/tool/zipcode?zip_code=" + code,
		method: "get"
	})
}

// 获取卡片
export const getMyCard = (data) => {
	return request({
		url: '/api/member/member',
		method: 'post',
		data: data
	})
}

// 首页排序 
export const refreshSort = (data) => {
	return request({
		url: '/api/member/member/sort',
		method: 'post',
		data: data
	})
}

// 删除卡片
export const deleteCard = (id) => {
	return request({
		url: '/api/member/member/' + id,
		method: 'post',
		data: {
			"_method": "delete"
		}
	})
}

// 获取店铺信息
export const getShopInfo = (id) => {
	return request({
		url: '/api/member/shop/' + id,
		method: 'get'
	})
}

// 获取卡片详情 
export const getCardInfo = (id) => {
	return request({
		url: '/api/member/member/' + id,
		method: 'get'
	})
}


// 获取营业日历 
export const getCalendar = (data) => {
    return request({
        url: "/api/member/shop/calendar/"+data.id+"?type=" + data.type + '&date=' + data.date,
        method: "get",
    })
}

// 店铺通知
export const getShopNotice = (data) => {
    return request({
        url: "/api/member/notice?sid="+data,
        method: "get",
    })
}

// 获取店铺优惠券
export const getShopCoupon = (data) => {
    return request({
        url: "/api/member/coupon?sid="+data.id+"&page="+data.page+"&page_size=30",
        method: "get",
    })
}

// 获取运营通知
export const getYY = (data) => {
    return request({
        url: "/api/member/admin_notice?type=list",
        method: "get",
    })
}

export const getYYs = (data) => {
    return request({
        url: "/api/member/admin_notice",
        method: "get",
    })
}
  
// 获取用户信息
export const getUserInfo = (data) => {
    return request({
        url: "/api/member/member/"+data,
        method: "get",
    })
}

// 编辑个人信息
export const editUserInfo = (data) => {
    return request({
        url: "/api/member/member/"+data.id,
        method: "post",
        data:data
    })
}

// 编辑会员信息
export const editUser = (data,id) => {
    return request({
        url: "/api/member/user/"+id,
        method: "post",
        data:data
    })
}

// 获取优惠券详情  
export const getCouponDetail = (data) => {
    return request({
        url: "/api/member/coupon/"+data,
        method: "get"
    })
}

// 收藏菜单
export const collectMenu = (data) => {
    return request({
        url: "/api/member/menu/"+data,
        method: "post",
        data:{"type":"collect","_method":"put"}
    })
}

// 菜单列表
export const menuList = (data) => {
    return request({
        url: "/api/member/menu?page="+data.page+"&page_size=50&collect="+data.collect+"&sid="+data.sid+"&is_order=0",
        method: "get"
    })
}

// 收藏的菜单列表
export const collectList = (data) => {
    return request({
        url: "/api/member/menu?page="+data+"&page_size=50&collect=1",
        method: "get"
    })
}

// 菜单分类 
export const menuCate = (data) => {
    return request({
        url: "/api/member/menu/cate?sid="+data,
        method: "get"
    })
}

// 获取订单列表 
export const getAppointmentList = (data) => {
    return request({
        url: "/api/member/order?date="+data.date+"&sid="+data.sid+"&said="+data.id+"&smid="+data.smid,
        method: "get",
    })
}

// 获取店铺员工
export const getStaffList = (data) => {
    return request({
        url: "/api/member/shop/admin3?smid=" + data.id + "&sid="+data.sid,
        method: "get",
    })
}

// 下单
export const setOrder = (data) => {
    return request({
        url: "/api/member/order",
        method: "post",
        data:data
    })
}

// 绑定socket (客户端)
export const bindSocket = (id) => {
    return request({
        url: "/api/member/auth/bind_socket",
        method: "post",
        data:{"client_id":id}
    })
}

// 获取聊天记录 
export const getMessageList = (data) => {
    return request({
        url: "/api/member/message?sid="+data+"&type=detail",
        method: "get",
    })
}

// 获取未读消息数量
export const getUnreadNum = (data) => {
    return request({
        url: "/api/member/message?sid="+data+"&type=count",
        method: "get",
    })
}

// 已读消息  api/member/message/read
export const hasRead = (sid) => {
    return request({
        url: "/api/member/message/read",
        method: "post",
        data:{"sid":sid}
    })
}

// 发送消息 
export const sendMessage = (data) => {
    return request({
        url: "/api/member/message",
        method: "post",
        data:data
    })
}

// 获取历史消息加载更多
export const loadMore = (data) => {
    return request({
        url: "/api/member/message?sid="+data.sid+"&mid="+data.mid+"&timestamp="+data.stamp+"&type=detail",
        method: "get"
    })
}

// 获取用户信息  
export const getUserInfos = () => {
    return request({
        url: "/api/member/user",
        method: "get"
    })
}

// 获取系统配置
export const getConfig = () => {
    return request({
        url: "/api/config",
        method: "get"
    })
}

// 获取新增优惠券数量
export const getMewCoupon = (id) => {
    return request({
        url: "/api/member/coupon?sid="+id+"&type=count",
        method: "get"
    })
}

// 获取新增通知
export const getNewNotice = (id) => {
    return request({
        url: "/api/member/notice?sid="+id+"&type=count",
        method: "get"
    })
}

//  扫码之后请求的接口
export const scanCode = (id) => {
    return request({
        url: "/api/member/shop/qrcode/"+id,
        method: "get"
    })
}

// 获取登录url(line)
export const getLineUrl = () => {
    return request({
        url: "/api/member/line/get_login_url",
        method: "get"
    })
}

// 获取登录url(google)
export const getGoogleUrl = () => {
    return request({
        url: "/api/member/google/get_login_url",
        method: "get"
    })
}

// 获取登录url(facebook)
export const getFacebookUrl = () => {
    return request({
        url: "/api/member/facebook/get_login_url",
        method: "get"
    })
}

// 获取ios登录url  
export const getIosUrl = () => {
    return request({
        url: "/api/member/facebook/get_login_url",
        method: "get"
    })
}

// 获取登录信息（line）
export const getLineInfo = (code) => {
    return request({
        url: "/api/member/line/get_user_info?code="+code,
        method: "get"
    })
}

// 获取登录信息（google）
export const getGoogleInfo = (code) => {
    return request({
        url: "/api/member/google/get_user_info?code="+code,
        method: "get"
    })
}

// 获取登录信息（facebook）
export const getFacebookInfo = (code) => {
    return request({
        url: "/api/member/facebook/get_user_info?code="+code,
        method: "get"
    })
}

// 获取登录信息（apple）
export const setIosLogin = (id,token) => {
    return request({
        url: "/api/member/apple/get_user_info?user_id="+id+"&id_token="+token,
        method: "get"
    })
}

// 传递谷歌登录信息 
export const setGoogleLogin = (data) => {
    return request({
        url: "/api/member/google/get_user_info?token="+data.token+"&name="+data.name+"&openid="+data.openid+"&google_email="+data.google_email,
        method: "get"
    })
}
// 传递脸书登录信息
export const setFacebookLogin = (data) => {
    return request({
        url: "/api/member/google/get_user_info?token="+data.token+"&name="+data.name+"&openid="+data.openid,
        method: "get"
    })
}

// 传递ios登录信息
export const setAppleLogin = (data) => {
    return request({
        url: "/api/member/google/get_user_info?token="+data.token+"&name="+data.name+"&openid="+data.openid,
        method: "get"
    })
}

// 注销账户
export const takeoff = (data) => {
    return request({
        url: "/api/member/write_off",
        method: "post",
    })
}

// 修改员工是否可指名  api/shop/shop/info
export const adminAppoint = (data) => {
    return request({
        url: "/api/shop/shop/info",
        method: "post",
    })
}

// 删除聊天记录
export const delMsg = (data) => {
    return request({
        url: "/api/member/message/del",
        method: "post",
        data:data
    })
}
