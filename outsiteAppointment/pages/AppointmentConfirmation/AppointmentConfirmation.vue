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
        
        
        <!-- [カスタムエイド 2026-10-22] 予約前の最終確認の画面を見やすく(白いカード・大きめの文字)。ボタンの仕様(氏名・電話番号を入れて確認にチェックするまで薄い色)は今までどおり、色を濃く -->
        <view class="rc-page">
            <view class="rc-lead">予約内容をご確認ください<text class="rc-lead-s">お名前と電話番号を入力し、内容に間違いがなければ「予約を確定する」を押してください。</text></view>

            <view class="rc-sec">ご予約の内容</view>
            <view class="rc-card">
                <view class="rc-row">
                    <view class="rc-lab">来店日時</view>
                    <view class="rc-val rc-strong">{{ comeTime }}</view>
                </view>
                <view class="rc-row">
                    <view class="rc-lab">メニュー</view>
                    <view class="rc-val rc-menu">
                        <view class="rc-menu-m">{{ orderItem.title }}<text class="rc-price" v-if="orderItem.price">{{ orderItem.price }}</text></view>
                        <image class="rc-photo" v-if="firstImage(orderItem.image)" :src="firstImage(orderItem.image)" mode="aspectFill"></image>
                    </view>
                </view>
                <view class="rc-row">
                    <view class="rc-lab">担当者</view>
                    <view class="rc-val">{{ staff.name }}</view>
                </view>
                <view class="rc-row" v-if="staff.appoint_price && (staff.appoint_price * 1) > 0">
                    <view class="rc-lab">指名料</view>
                    <view class="rc-val rc-red">￥{{ staff.appoint_price }}</view>
                </view>
            </view>

            <view class="rc-sec">キャンセルについて</view>
            <view class="rc-card rc-pad">
                <view class="rc-note">予約確定後のキャンセルは、直接お店にご連絡ください。</view>
            </view>

            <view class="rc-sec">お客様情報(<text class="rc-req">※</text>は必須)</view>
            <view class="rc-card">
                <view class="rc-row rc-row-in">
                    <view class="rc-lab"><text class="rc-req">※</text>氏名</view>
                    <input class="rc-in" v-model="name" placeholder="例:山田 花子" placeholder-style="color:#AEAEB2" />
                </view>
                <view class="rc-row rc-row-in">
                    <view class="rc-lab"><text class="rc-req">※</text>電話番号</view>
                    <input class="rc-in" v-model="phone" type="tel" placeholder="ハイフンなし(携帯電話)" placeholder-style="color:#AEAEB2" />
                </view>
            </view>

            <view class="rc-sec">ご要望(任意)</view>
            <view class="rc-card rc-pad">
                <view class="rc-hint">お店に事前にお伝えしたいご要望などあれば、ご記入ください。</view>
                <textarea class="rc-ta" v-model="text" placeholder="例:前髪は短めにしたい" placeholder-style="color:#AEAEB2"></textarea>
            </view>

            <view class="rc-card rc-check" @click="isCheck()">
                <view class="rc-box" :class="{ on: checked }"><text class="rc-tick" v-if="checked">✓</text></view>
                <view class="rc-check-t">予約内容を確認しました<text class="rc-check-s">お名前・電話番号を入れてチェックすると「予約を確定する」が押せます</text></view>
            </view>
        </view>
        <view class="rc-bar">
            <view class="rc-btn" :class="{ on: checked && !isSubmitting }" hover-class="rc-btn-h" @click="yuYue()">{{ isSubmitting ? '予約しています…' : '予約を確定する' }}</view>
        </view>
    </view>
</template>

<script>
    import { setShopTitle } from "@/utils/pageTitle.js"
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
                isSubmitting: false,
                staff:{},
                orderItem:{},
                comeTime:"",
                user:{},
                name:"",
                phone:"",
                shopname:''
            };
        },
        onShow() {
            setShopTitle()
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
            // [カスタムエイド 2026-10-22] メニューの1枚目の写真(写真が無ければ空)
            firstImage(img) {
                if (!img) return ''
                if (typeof img == 'string') {
                    try { let a = JSON.parse(img); if (Array.isArray(a)) return a[0] || '' } catch (e) {}
                    return img
                }
                return Array.isArray(img) ? (img[0] || '') : ''
            },

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
              // [カスタムエイド 2026-10-22] 火曜と水曜が逆になっていた
              let week = ['日', '月', '火', '水', '木', '金', '土'][date.getDay()] || ''
              console.log(week)
              that.comeTime = dates.item.date + "("+week+") "+dates.ite.time
              // [カスタムエイド 2026-10-22] ここで止まっていた(orderItem が無い)ため削除。orderItem は onLoad で読み込み済み
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
                // Loading中の連打による予約重複を防止（送信中はボタンを非活性）
                if (that.isSubmitting) return
                that.isSubmitting = true
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
                        uni.setStorageSync("lastOrderImage", that.firstImage(that.orderItem && that.orderItem.image)) // [カスタムエイド 2026-10-22] 予約完了の画面でメニューの写真を出すため
                        uni.removeStorageSync("orderItem")
                        uni.navigateTo({
                            url:"../completed/completed?name="+res.data.order.name+"&order_no="+res.data.order.order_no+"&start_time="+res.data.order.start_time+"&amount="+res.data.order.amount+"&remark="+res.data.order.remark+"&appoint_name="+res.data.order.appoint_name+"&menu_name="+res.data.order.menu_name+"&id="+res.data.order.sid+'&phone='+res.data.order.phone+"&appoint_amount="+res.data.order.appoint_amount
                        })
                    }else{
                        that.isSubmitting = false
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                }).catch(() => {
                    that.isSubmitting = false
                })
                
                // uni.navigateTo({
                //     url: '../../pagesA/completed/completed'
                // })
            },
         }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-22] 予約内容確認(予約前の最終確認。見やすさを最優先:白いカード・大きめの文字・行の区切り)
       ボタン:必須がそろうまでは薄い色、そろったら濃い色(仕様は今までどおり)。色は以前(#53B1AF・薄い時は透明度0.6)より濃く */
    .rc-page { min-height: 100vh; background: #F5F5F7; padding-bottom: calc(260rpx + env(safe-area-inset-bottom)); }
    .rc-lead { padding: 48rpx 40rpx 24rpx; font-size: 34rpx; font-weight: 700; color: #1D1D1F; }
    .rc-lead-s { display: block; margin-top: 8rpx; font-size: 26rpx; font-weight: 400; color: #6E6E73; }
    .rc-sec { padding: 0 40rpx 16rpx; font-size: 26rpx; color: #6E6E73; }
    .rc-card { margin: 0 24rpx 48rpx; background: #FFFFFF; border-radius: 32rpx; padding: 0 32rpx; }
    .rc-pad { padding: 32rpx; }
    .rc-row { display: flex; align-items: flex-start; padding: 32rpx 0; border-bottom: 1px solid #E5E5EA; }
    .rc-row:last-child { border-bottom: 0; }
    .rc-lab { width: 160rpx; flex-shrink: 0; font-size: 28rpx; color: #6E6E73; line-height: 1.5; padding-top: 2rpx; }
    .rc-val { flex: 1; min-width: 0; font-size: 32rpx; color: #1D1D1F; line-height: 1.5; word-break: break-all; }
    .rc-strong { font-size: 36rpx; font-weight: 700; }
    .rc-price { display: block; margin-top: 8rpx; font-size: 30rpx; color: #D93025; font-weight: 600; }
    .rc-red { color: #D93025; font-weight: 600; }
    .rc-empty { color: #AEAEB2; }
    .rc-note { font-size: 30rpx; color: #1D1D1F; line-height: 1.7; }
    .rc-hint { font-size: 28rpx; color: #515154; line-height: 1.6; margin-bottom: 20rpx; }
    .rc-ta { width: 100%; height: 220rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 24rpx 28rpx; font-size: 30rpx; line-height: 1.6; box-sizing: border-box; background: #FFFFFF; display: block; }
    .rc-check { display: flex; align-items: center; padding: 36rpx 32rpx; }
    .rc-box { width: 48rpx; height: 48rpx; border-radius: 12rpx; border: 3rpx solid #8E8E93; box-sizing: border-box; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #FFFFFF; }
    .rc-box.on { background: #2B8A87; border-color: #2B8A87; }
    .rc-tick { color: #FFFFFF; font-size: 30rpx; font-weight: 700; line-height: 1; }
    .rc-check-t { margin-left: 24rpx; font-size: 32rpx; color: #1D1D1F; font-weight: 600; }
    .rc-check-s { display: block; margin-top: 4rpx; font-size: 24rpx; color: #6E6E73; font-weight: 400; }
    .rc-bar { position: fixed; left: 0; right: 0; bottom: 0; background: #FFFFFF; border-top: 1px solid #E5E5EA; padding: 24rpx 32rpx calc(32rpx + env(safe-area-inset-bottom)); z-index: 50; }
    .rc-btn { height: 108rpx; border-radius: 28rpx; background: #7FBDBA; color: #FFFFFF; font-size: 34rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; letter-spacing: 2rpx; }
    .rc-btn.on { background: #2B8A87; }
    .rc-btn-h { opacity: 0.85; }
    .rc-menu { display: flex; align-items: flex-start; }
    .rc-menu-m { flex: 1; min-width: 0; }
    .rc-photo { width: 128rpx; height: 128rpx; border-radius: 16rpx; margin-left: 20rpx; flex-shrink: 0; background: #F2F2F7; }
    .rc-req { color: #D93025; margin-right: 4rpx; }
    .rc-in { flex: 1; height: 88rpx; border: 1px solid #D2D2D7; border-radius: 20rpx; padding: 0 24rpx; font-size: 32rpx; box-sizing: border-box; background: #FFFFFF; }
    .rc-row-in { align-items: center; }

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
