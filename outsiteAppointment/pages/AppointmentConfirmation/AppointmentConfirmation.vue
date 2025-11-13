<template>
    <view style="width: 100vw;overflow: hidden;">
       <!-- <u-navbar title="予約内容確認" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56"
            :border="true" @leftClick="leftClick" :titleWidth="484">
        </u-navbar> -->
        
        <u-navbar :placeholder="true" :safeAreaInsetTop="true" autoBack :height="60" bgColor="#ffffff" :border="true">
            <view class="u-nav-slot" slot="center">
                <view style="text-align: center;">予約内容確認</view>
                <view v-text="shopname" style="text-align: center;"></view>
            </view>
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
                        <view class="price" v-text="orderItem.price"></view>
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
            <view class="con-box" v-if="staff.appoint_price && (staff.appoint_price * 1) > 0">
                <view class="con">
                    <view class="con-left">
                        指名料
                    </view>
                    <view class="con-right" style="color: rgba(217, 48, 37, 0.65);" v-text="`￥${staff.appoint_price}`"></view>
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
            <view class="sss">氏名・電話番号(携帯電話)をご入力ください</view>
            <view class="user-info">
                <view class="user-item">
                    <view class="user-left">
                        <span style="color: red;">※</span> 氏名
                    </view>
                    <u-input placeholder="" v-model="name"></u-input>
                </view>
            </view>
            <view class="user-info" style="padding-bottom: 80upx;">
                <view class="user-item">
                    <view class="user-left">
                       <span style="color: red;">※</span>電話番号
                    </view>
                    <u-input placeholder="ハイフンなし" v-model="phone"></u-input>
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
                    <textarea v-model="text" placeholder="要望などあればご記入ください"> </textarea>
                </view>
            </view>
        </view>
        <view class="c-o-n">
            <view class="con-orng">
                <view class="co">
                    <view :class="!checked ? 'yuan' : 'yuan checked'" @click="isCheck()">
                        <!-- <u-icon name="checkbox-mark" color="#fff" size="14" v-if="checked"></u-icon> -->
                        <view class="inner" v-if="checked"></view>
                    </view>
                    <view class="co-text" :style="checked?'color:#1d1d1f' : ''">
                        予約内容を確認しました
                    </view>
                </view>
            </view>
        </view>
        <view class="fix-btn-ok">
            <view class="fix-b" hover-class="op" :style="checked?'' : 'opacity:0.6 !important'" @click="yuYue()">
                予約を確定する
            </view>
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
                staff:{},
                orderItem:{},
                comeTime:"",
                user:{},
                name:"",
                phone:"",
                shopname:''
            };
        },
        onLoad(options) {
           let that = this
            that.shopname = uni.getStorageSync("shopName")
           let query = options
           that.query = query
           that.orderItem = uni.getStorageSync("orderItem")
           that.getStaff(query.staff,query.sid)
           that.getDate() 
          
        },
        methods: {
            leftClick() {
                uni.navigateBack()
            },
            getStaff(id,sid){
                let that = this
                let data = {}
                data.sid = sid
                data.id = ""
                if(id == 0){
                    let obj = {}
                    obj.id = 0
                    obj.name = "指名なし"
                    that.staff = obj
                }else{
                   getStaffList(data).then((res) => {
                       console.log("staff",res)
                       if(res.code == 200){
                           res.data.admin3s.forEach((val,index) => {
                               if(val.id == id){
                                   that.staff = val
                               }
                           })
                       }
                   }) 
                } 
            },
            getDate(){
              let that = this
              let dates = uni.getStorageSync("orderTime")
              that.comeTime = dates
              let date = new Date(dates.item.date)
              let week = date.getDay() == 0 ? '日' : date.getDay() == 1 ? '月' : date.getDay() == 2 ? '水' : date.getDay() == 3 ? '火':date.getDay() == 4 ? '木':date.getDay() == 5 ? '金':'土'
              console.log(week)
              that.comeTime = dates.item.date + "("+week+") "+dates.ite.time
              that.orderItem = orderItem
              getUserInfo(uni.getStorageSync("user").id).then((res) => {
                  if(res.code == 200){
                      that.user = res.data.member
                  }
              })
            },
            isCheck() {
                let that = this
                if(!that.name){
                    uni.showToast({
                        title:"名前を入力してください",
                        icon:"none"
                    })
                    return false
                }
                
                if(!that.phone){
                    uni.showToast({
                        title:"電話番号を入力してください",
                        icon:"none"
                    })
                    return false
                }
                
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
                let data = {}
                let date = uni.getStorageSync("orderTime")
                data.start_time = that.comeTime
                data.smid = uni.getStorageSync("orderItem").id
                data.said = that.query.staff * 1
                data.remark = that.text
                data.phone = that.phone
                data.name = that.name
                console.log(data)
                setOrder(data).then((res) => {
                    console.log(res)
                    if(res.code == 200){
                        uni.removeStorageSync("orderTime")
                        uni.removeStorageSync("orderItem")
                        uni.navigateTo({
                            url:"../completed/completed?name="+res.data.order.name+"&order_no="+res.data.order.order_no+"&start_time="+res.data.order.start_time+"&amount="+res.data.order.amount+"&remark="+res.data.order.remark+"&appoint_name="+res.data.order.appoint_name+"&menu_name="+res.data.order.menu_name+"&id="+res.data.order.sid+'&phone='+res.data.order.phone+"&appoint_amount="+res.data.order.appoint_amount
                        })
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
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
    .op{
        opacity: 0.6 !important;
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
    .inner{
        width: 100%;
        height: 100%;
        background: #1A73E8;
        border-radius: 50%;
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
                        margin-top: 40rpx;
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
            padding: 22upx 0;
            // border-bottom: 1px solid #D2D2D7;

            .user-item {
                width: 90%;
                margin: 0 auto;
                // display: flex;
                font-size: 32rpx;
                // align-items: center;
                .user-left {
                    flex-shrink: 0;
                    width: 222rpx;
                    color: #707070;
                    margin-bottom: 10upx;
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

                    border: 2upx solid #2979ff !important;
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
            padding: 6upx !important;
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
            border-radius: 20rpx;
            font-size: 40rpx;
            color: #FFFFFF;
            margin: 60rpx auto;
            margin-bottom: 0;
        }
    }
    
    .sss{
        box-sizing: border-box;padding: 40upx 40upx 0;
    }
</style>
