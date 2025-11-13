<template>
    <view class="container">
        <view class="title" v-if="type == 1">
            予約確定時に送信されます
        </view>
        <view class="title" v-if="type == 2">
            予約日時の24時間前に送信されます
        </view>
        <view class="title" v-if="type == 4">
            予約来店日時の24時間後に送信されます
        </view>
        <view class="title" v-if="type == 3">
            予約削除の際のメッセージ
        </view>
        <view class="editContent" v-if="type != 4">
            <view class="editTitle">
                <u-input border="none" v-model="eTitle"></u-input>
            </view>
            <view class="name">
                <text class="blue">#name</text>
                <u-input border="none" v-model="name"></u-input>
            </view>
            <view class="edit1">
                <u--textarea border="none" autoHeight v-model="edit1"></u--textarea>
            </view>
           <!-- <view class="edit3" v-if="type == 5" style="margin-bottom: 50upx;">
                <u--textarea border="none" autoHeight v-model="edit2"></u--textarea>
            </view> -->
            <view class="info">
                <view class="edit2 blue">
                   予約番号: #reserve number
                </view>
                <view class="edit2 blue">
                    日時：00月00日(-) 00:00~
                </view>
                <view class="edit2 blue">
                    メニュー：#menu title
                </view>
                <view class="edit2 blue">
                    価格：¥price~
                </view>
                <view class="edit2 blue">
                    担当者：担当者氏名
                </view>
                <view class="edit2 blue">
                    指名料：price
                </view>
            </view>
            <view class="edit3" v-if="type == 5">
                 <u--textarea border="none" autoHeight v-model="edit4"></u--textarea>
            </view>
            <view class="edit3" v-if="type != 5">
                <u--textarea border="none" autoHeight v-model="edit2"></u--textarea>
            </view>
            <view style="margin-top: 60upx;" class="edit2 blue" v-if="type != 4">
                #店舗名
            </view>
            <view class="edit2 blue" v-if="type != 4">
                #電話番号
            </view>
            <view class="edit2 blue" v-if="type != 4">
                #住所
            </view>
        </view>
        <view class="editContent" v-else>
            <view class="name" style="margin-top: 0;">
                <text class="blue">#name</text>
                <u-input border="none" v-model="name"></u-input>
            </view>
            <view class="edit1">
                <u--textarea border="none" autoHeight v-model="edit1"></u--textarea>
            </view>
            <view class="edit3" v-if="type != 4">
                <u--textarea border="none" autoHeight v-model="edit2"></u--textarea>
            </view>
        </view>
        <view class="reviewContent" v-if="type == 4">
            <view class="checkBox" :style="current == 1? 'border:2rpx solid #1A73E8' : ''" @click.stop="changeSel">
                <view class="inner" v-if="current == 1"></view>
            </view>
            <view class="reviewDetail" :style="current == 1?'font-weight:bold;color:#1d1d1f' : ''">
                Googleレビューリンクボタンの設置
            </view>
        </view>
        <u-button class="btn" type="primary" :plain="true" @click="save">保存</u-button>
    </view>
</template>
<script>
    import {
        getShopInfo,
        changeChatInfo
    } from '@/api/index.js'
    export default {
        data() {
            return {
                eTitle: "【予約受付のお知らせ】",
                name: "様",
                edit1: "ご予約ありがとうございます。\n以下の通りご予約承りました。",
                edit2: "キャンセルや来店時刻に遅れる場合は、必ずご連絡頂きますようお願いいたします。",
                type: 1,
                edit4:"",
                current: 0
            }
        },
        created() {
            
        },
        onLoad(options) {
            let that = this
            let query = options
            that.type = query.type
            if (query.type == 1) {
                that.eTitle = '【予約受付のお知らせ】'
                that.edit1 = 'ご予約ありがとうございます。 以下の通りご予約承りました。'
                that.edit2 = 'キャンセルや来店時刻に遅れる場合は、必ずご連絡頂きますようお願いいたします。'
                uni.setNavigationBarTitle({
                    title:"予約確認"
                })
            }
            
            if (query.type == 2) {
                that.eTitle = '【予約確認のお知らせ】'
                that.edit1 = '明日ご来店日となりますので、お知らせいたします。'
                that.edit2 = 'スタッフ一同、心よりご来店お待ちしております。'
                uni.setNavigationBarTitle({
                    title:"予約リマインダー"
                })
            }
            
            if (query.type == 3) {
                that.eTitle = '【予約キャンセルのお知らせ】'
                that.edit1 = '以下内容のご予約のキャンセルを受理いたしました。'
                that.edit2 = '内容のご確認をお願いいたします。'
                uni.setNavigationBarTitle({
                    title:"予約キャンセル"
                })
            }
            
            if (query.type == 4) {
                that.edit1 = 'この度は当店をご利用頂き、誠にありがとうございました。\n\n 今後も当店をよろしくお願い致します。'
                 that.edit2 = ''
                uni.setNavigationBarTitle({
                    title:"サンキューメッセージ"
                })
               
            }
            
            if(query.type == 5){
                that.eTitle = '【予約内容変更のお知らせ】'
                that.edit1 = 'ご予約の内容が変更になりましたのでお知らせします。\n\n変更内容のご確認お願いします。'
                that.edit2 = ''
                that.edit4 = "キャンセルや来店時刻に遅れる場合 は、直接店舗にご連絡頂きますよう お願いいたします。"
                uni.setNavigationBarTitle({
                    title:" 予約内容変更"
                })
            }
            
            that.getInfo()
        },
        methods: {
            changeSel(){
                let that = this 
                if(that.current == 0){
                    that.current = 1
                }else{
                    that.current = 0
                }
            },
            getInfo() {
                let that = this
                getShopInfo().then((res) => {
                    if(res.code == 200){
                        if(that.type == 4){
                            if(res.data.shop_info.thanks_message.length > 0){
                                console.log(res.data.shop_info.thanks_message[3])
                                that.name = res.data.shop_info.thanks_message[0]
                                that.edit1 = res.data.shop_info.thanks_message[1]
                                that.edit2 = res.data.shop_info.thanks_message[2]
                                that.current = res.data.shop_info.thanks_message[3] || 0
                            }
                        }else if(that.type == 1){
                            if(res.data.shop_info.order_confirm_message.length > 0){
                                that.eTitle = res.data.shop_info.order_confirm_message[0]
                                that.name = res.data.shop_info.order_confirm_message[1]
                                that.edit1 = res.data.shop_info.order_confirm_message[2]
                                that.edit2 = res.data.shop_info.order_confirm_message[3]
                            }
                        }else if(that.type == 2){
                            if(res.data.shop_info.order_notice_message.length > 0){
                                that.eTitle = res.data.shop_info.order_notice_message[0]
                                that.name = res.data.shop_info.order_notice_message[1]
                                that.edit1 = res.data.shop_info.order_notice_message[2]
                                that.edit2 = res.data.shop_info.order_notice_message[3]
                            }
                        }else if(that.type == 3){
                            if(res.data.shop_info.order_cancel_message.length > 0){
                                that.eTitle = res.data.shop_info.order_cancel_message[0]
                                that.name = res.data.shop_info.order_cancel_message[1]
                                that.edit1 = res.data.shop_info.order_cancel_message[2]
                                that.edit2 = res.data.shop_info.order_cancel_message[3]
                            }
                        }else {
                            if(res.data.shop_info.order_update_message.length > 0){
                                that.eTitle = res.data.shop_info.order_update_message[0]
                                that.name = res.data.shop_info.order_update_message[1]
                                that.edit1 = res.data.shop_info.order_update_message[2]
                                that.edit2 = res.data.shop_info.order_update_message[3]
                                that.edit4 = res.data.shop_info.order_update_message[4]
                            }
                        }
                    }
                })
            },
            save(){
                let that = this
                let temp = []
                let data = {}
                if(that.type != 4){
                    temp.push(that.eTitle)
                }
                temp.push(that.name)
                temp.push(that.edit1)
                temp.push(that.edit2)
                temp.push(that.edit4)
                if(that.type == 4){
                    temp[3] = that.current 
                }
                
                data.type = 'message'
                data.field = that.type == 1? 'order_confirm_message' : that.type == 2? 'order_notice_message' : that.type == 3? 'order_cancel_message' : that.type == 4?'thanks_message' : 'order_update_message'
                data.order_confirm_message = temp
                data.order_notice_message = temp
                data.order_cancel_message = temp
                data.thanks_message = temp
                data.order_update_message = temp
                console.log(data)
                changeChatInfo(data).then((res) => {
                    if(res.code == 200){
                        uni.showToast({
                            title:res.message,
                            duration:2000
                        })
                        
                        setTimeout(() => {
                            uni.navigateBack()
                        },2000)
                    }else{
                       uni.showToast({
                           title:res.message,
                           duration:2000,
                           icon:"none"
                       }) 
                    }
                })
            }
        }
    }
</script>

<style lang="scss">
    .reviewContent{
        width: 100%;
        height: 88rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32rpx;
        color: #707070;
        background: #FFFFFF;
        border: 2rpx solid #AAAAAA;
        margin-top: 40rpx;
    }
    .checkBox{
        width: 32rpx;
        height: 32rpx;
        background: #FFFFFF;
        border: 2rpx solid #AAAAAA;
        box-sizing: border-box;
        padding: 6rpx;
        border-radius: 50%;
        margin-right: 20rpx;
        .inner{
            width: 100%;
            height: 100%;
            background: #1A73E8;
            border-radius: 50%;
        }
    }
    .blue {
        color: #007AFF;
    }
    ::v-deep .u-textarea{
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .container {
        background: #F5F5F7;
        border-top: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding: 96upx 52upx;
        min-height: calc(100vh - 44px);

        .title {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
        }

        .name {
            display: flex;
            align-items: center;
            margin: 60upx 0 20rpx;
            font-size: 28rpx;
            text {
                margin-right: 10upx;
            }
        }

        .editContent {
            width: 100%;
            height: 790upx;
            background: #FFFFFF;
            box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);
            border-radius: 20upx;
            opacity: 1;
            border: 2upx solid #D2D2D7;
            margin-top: 10upx;
            overflow-y: scroll;
            box-sizing: border-box;
            padding: 40upx 62upx;
            font-size: 28rpx !important;
        }

        .edit1 {
            margin-bottom: 60upx;
        }

        .edit3 {
            margin-top: 60upx;
        }
        .edit2 {
            margin-bottom: 20upx;
        }

        .btn {
            width: 480upx;
            height: 96upx;
            background: #FFFFFF;
            border-radius: 20upx;
            margin-top: 150upx;
        }
    }
</style>
