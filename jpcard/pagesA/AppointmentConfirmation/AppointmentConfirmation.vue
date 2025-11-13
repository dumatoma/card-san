<template>
    <view style="width: 100vw;overflow: hidden;">
        <u-navbar title="予約内容確認" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56"
            :border="true" @leftClick="leftClick" :titleWidth="484">
        </u-navbar>
        <view class="top">
            予約内容をご確認ください
        </view>
        <view class="content-box">
            <view class="con-box">
                <view class="con">
                    <view class="con-left">
                        来店日時
                    </view>
                    <view class="con-right" v-text="comeTime"></view>
                </view>
            </view>
            <view class="con-box">
                <view class="con">
                    <view class="con-left">
                        メニュー
                    </view>
                    <view class="con-right">
                        <view class="" v-text="orderItem.title"></view>
                        <view class="price" v-text="`${orderItem.price}`"></view>
                    </view>
                </view>
            </view>
            <view class="con-box">
                <view class="con">
                    <view class="con-left">
                        担当者
                    </view>
                    <view class="con-right" v-text="staff.name"></view>
                </view>
            </view>
            <view class="con-box" v-if="query.price*1 > 0">
                <view class="con">
                    <view class="con-left">
                        指名料
                    </view>
                    <view class="con-right" style="color: rgba(217, 48, 37, 0.65);" v-text="`￥${query.price}`"></view>
                </view>
            </view>
        </view>
        <view class="xq-box">
            キャンセルについて
        </view>
        <view class="xq-des">
            予約確定後のキャンセルは、直接お店にご連絡ください。
        </view>
        <view class="xq-box">
            お客様情報
        </view>
        <view class="user-info-box">
            <view class="user-info">
                <view class="user-item">
                    <view class="user-left">
                        氏名
                    </view>
                    <view class="user-right" v-text="user.name"></view>
                </view>
            </view>
            <view class="user-info">
                <view class="user-item">
                    <view class="user-left">
                        カナ
                    </view>
                    <view class="user-right" v-text="user.pronunciation"></view>
                </view>
            </view>
            <view class="user-info">
                <view class="user-item">
                    <view class="user-left">
                        電話番号
                    </view>
                    <view class="user-right" v-text="user.phone"></view>
                </view>
            </view>
            <view class="user-info">
                <view class="user-item">
                    <view class="user-left">
                        性別
                    </view>
                    <view class="user-right"
                        v-text="user.sex == 1? '男性' : user.sex == 2? '女性' : user.sex == 3?'Xジェンダー' : '女性' "></view>
                </view>
            </view>
            <view class="user-info">
                <view class="user-item">
                    <view class="user-left">
                        生年月日
                    </view>
                    <view class="user-right"
                        v-text="`${user.birthday_year || '-'}年${user.birthday_month || '-'}月${user.birthday_day || '-'}日`">
                    </view>
                </view>
            </view>
            <view class="user-info">
                <view class="user-item">
                    <view class="user-left">
                        住所
                    </view>
                    <view class="user-right">
                        <view class="" v-text="user.zip_code"></view>
                        <view class="" v-text="user.province"></view>
                        <view class="" v-text="user.address1"></view>
                        <view class="" v-text="user.address2"></view>
                    </view>
                </view>
            </view>
        </view>
        <view class="xq-box">
            ご要望
        </view>
        <view class="forms">
            <view class="forms-item-box">
                <view class="fotms-item">
                    お店に事前にお伝えしたいご要望などあれば、ご記入ください。
                </view>
                <view class="fotms">
                    <textarea @focus="getFocus,showBot = true" @blur="showBot = false" v-model="text" placeholder="要望などあればご記入ください"> </textarea>
                </view>
            </view>
        </view>
        <view class="c-o-n">
            <view class="con-orng">
                <view class="co">
                    <view :class="!checked ? 'yuan' : 'yuan checked'" @click="isCheck()">
                        <!-- <u-icon name="checkbox-mark" color="#fff" size="14" v-if="checked"></u-icon> -->
                        <image src="../../static/svg/sel.svg" v-if="checked" style="width: 40upx;height: 40upx;" alt="">
                    </view>
                    <view class="co-text" :style="checked?'color:#1d1d1f !important' : ''">
                        予約内容を確認しました
                    </view>
                </view>
            </view>
        </view>
        <view class="fix-btn-ok" v-if="showBot == false">
            <u-button class="fix-b" :loading="loads" :style="checked?'opacity:1 !important' : ''" @click="yuYue()">
                予約を確定する
            </u-button>
        </view>
    </view>
</template>

<script>
    import {
        getStaffList,
        getUserInfo,
        setOrder
    } from "@/api/index.js"
    export default {
        data() {
            return {
                text: '',
                checked: false,
                staff: {},
                orderItem: {},
                comeTime: "",
                user: {},
                loads: false,
                showBot:false
            };
        },
        created() {

        },
        onLoad(options) {
            let that = this
            let query = options
            that.query = query
            that.getStaff(query.staff, query.sid)
            that.getDate()
        },
        methods: {
            getFocus() {
                uni.pageScrollTo({
                    scrollTop: 99999,
                    duration: 300 // 动画时长，默认300ms
                });
            },
            leftClick() {
                uni.navigateBack()
            },
            // parseFormatNum(number,n){
            //     if(n != 0 ){
            //         n = (n > 0 && n <= 20) ? n : 2; 
            //    }
            //      number = parseFloat((number + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";  
            //      var sub_val = number.split(".")[0].split("").reverse(); 
            //      var sub_xs = number.split(".")[1];  
            //      var show_html = "";  
            //      for (let i = 0; i < sub_val.length; i++){  
            //          show_html += sub_val[i] + ((i + 1) % 3 == 0 && (i + 1) != sub_val.length ? "," : "");  
            //      }  
            //      if(n == 0 ){
            //          return show_html.split("").reverse().join("");  
            //      }else{
            //      return show_html.split("").reverse().join("") + "." + sub_xs;  
            //      } 
            //  },
            getStaff(id, sid) {
                let that = this
                let data = {}
                data.sid = sid
                data.id = ""
                if (id == 0) {
                    let obj = {}
                    obj.id = 0
                    obj.name = "指名なし"
                    that.staff = obj
                } else {
                    getStaffList(data).then((res) => {
                        if (res.code == 200) {
                            res.data.admin3s.forEach((val, index) => {
                                if (val.id == id) {
                                    that.staff = val
                                }
                            })
                        }
                    })
                }
            },
            getDate() {
                let that = this
                let dates = uni.getStorageSync("orderTime")
                let orderItem = uni.getStorageSync("orderItem")
                let date = new Date(dates)
                let week = date.getDay() == 0 ? '日' : date.getDay() == 1 ? '月' : date.getDay() == 2 ? '水' : date
                .getDay() == 3 ? '火' : date.getDay() == 4 ? '木' : date.getDay() == 5 ? '金' : '土'
                that.comeTime = dates + "(" + week + ")"
                that.orderItem = orderItem
                getUserInfo(that.query.mid).then((res) => {
                    if (res.code == 200) {
                        that.user = res.data.member
                    }
                })
            },
            isCheck() {
                if (this.checked == true) {
                    this.checked = false
                } else {
                    this.checked = true
                }
            },
            yuYue() {
                let that = this
                if (!that.checked) {
                    uni.showToast({
                        title: '予約内容を確認してください',
                        icon: 'none',
                        duration: 1000
                    })
                    return
                }
                that.loads = true
                let data = {}
                let date = uni.getStorageSync("orderTime")
                data.start_time = date
                data.smid = uni.getStorageSync("orderItem").id
                data.said = that.query.staff * 1
                data.remark = that.text
                setOrder(data).then((res) => {
                    console.log("resk", res)
                    if (res.code == 200) {

                        uni.removeStorageSync("orderTime")
                        uni.removeStorageSync("orderItem")
                        uni.navigateTo({
                            url: "../completed/completed?name=" + res.data.order.name + "&order_no=" +
                                res.data.order.order_no + "&start_time=" + res.data.order.start_time +
                                "&amount=" + res.data.order.amount + "&remark=" + res.data.order
                                .remark + "&appoint_name=" + res.data.order.appoint_name +
                                "&menu_name=" + res.data.order.menu_name + "&appoint_amount=" + res.data
                                .order.appoint_amount
                        })
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })

                // uni.navigateTo({
                //     url: '../../pagesA/completed/completed'
                // })
            },
        }
    }
</script>

<style lang="scss" scoped>
    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    .top {
        width: 100%;
        padding: 50rpx 0;
        padding-left: 5%;
        font-weight: 700;
        background: #F5F5F7;
        font-size: 32rpx;
        color: #1A73E8;
        border-bottom: 1px solid #D2D2D7;
      
    }

    .content-box {
        .con-box {

            border-bottom: 1px solid #D2D2D7;

            .con {
                width: 90%;
                margin: 0 auto;
                display: flex;
                font-size: 32rpx;
                padding: 40rpx 0;

                .con-left {
                    width: 128rpx;
                    flex-shrink: 0;
                    color: #707070;

                }

                .con-right {
                    color: #1D1D1F;
                    margin-left: 40rpx;

                    .price {
                        margin-top: 20rpx;
                        color: rgba(217, 48, 37, 0.65);
                    }
                }
            }
        }
    }

    .xq-box {
        width: 100%;
        padding: 36rpx 0;
        padding-left: 5%;
        background: #F5F5F7;
        font-size: 32rpx;
        font-weight: 700;
        color: #707070;
        border-bottom: 1px solid #D2D2D7;
    }

    .xq-des {
        font-size: 32rpx;
        color: #1D1D1F;
        padding: 42rpx 5% 80rpx 5%;
        border-bottom: 1px solid #D2D2D7;
    }

    .user-info-box {
        .user-info {
            width: 100%;
            padding: 40rpx 0 22rpx 0;
            border-bottom: 1px solid #D2D2D7;

            .user-item {
                width: 90%;
                margin: 0 auto;
                display: flex;
                font-size: 32rpx;

                .user-left {
                    flex-shrink: 0;
                    width: 128rpx;
                    color: #707070;
                }

                .user-right {
                    margin-left: 70rpx;
                    color: #1D1D1F;
                }
            }
        }
    }

    .forms {
        padding-bottom: 82rpx;
        border-bottom: 1px solid #D2D2D7;
    }

    .forms-item-box {
        padding-top: 40rpx;
        // border-bottom: 1px solid #D2D2D7;

        .fotms-item {
            width: 90%;
            margin: 0 auto;
            font-size: 32rpx;
            color: #1D1D1F;
            margin-bottom: 40rpx;
        }

        .fotms {
            width: 90%;
            margin: 0 auto;

            textarea {
                width: 100%;
                height: 200rpx;
                background: #FFFFFF;
                border-radius: 20rpx;
                box-sizing: border-box;
                padding: 20rpx;
                border: 2rpx solid #D2D2D7;
            }
        }
    }

    .c-o-n {
        padding-bottom: 300rpx;

        .con-orng {
            height: 128rpx;
            background: #FFFFFF;
            border-bottom: 2rpx solid #D2D2D7;

            .co {
                width: 90%;
                margin: 0 auto;
                height: 100%;
                display: flex;
                align-items: center;

                .checked {

                    // background-color: #2979ff;
                    border: 0;
                }
            }

            .co-text {
                font-size: 36rpx;
                color: #86868B;
                margin-left: 20rpx;
            }
        }

        .yuan {
            width: 40rpx;
            height: 40rpx;
            text-align: center;
            border-radius: 50%;
            box-sizing: border-box;
            border: 2rpx solid #707070;
            // margin-right: 24rpx;
        }

        ::v-deep .u-icon__icon {
            padding-left: 7rpx;
            padding-top: 7rpx;
        }
    }

    .fix-btn-ok {
        position: fixed;
        left: 0;
        bottom: 0;
        width: 100%;
        height: 256rpx;
        background: #F5F5F7;
        box-shadow: 0px 0px 12rpx 2rpx rgba(0, 0, 0, 0.16);

        .fix-b {
            width: 480rpx;
            height: 96rpx;
            line-height: 96rpx;
            text-align: center;
            background: #53B1AF;
            opacity: 0.6;
            border-radius: 20rpx;
            font-size: 40rpx;
            color: #FFFFFF;
            margin: 60rpx auto;
            margin-bottom: 0;
        }
    }
</style>