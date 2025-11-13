<template>
    <view class="container">
        <view class="ctitle">
            解約手続きが完了しました。
        </view>
        <view class="ccontent">
            <view class="c1">
                本内容のメールをexample@mail.comに送信しました。契約終了日の{{vips[0].end_time}}まで引き続きご利用ができます。
            </view>
            <view class="c2">
                ご契約の継続は、いつでも「アカウント」の「ご契約内容」から手続きが可能です。
            </view>
        </view>
        <view class="ov" @click="toIndex">
            完了
        </view>
    </view>
</template>

<script>
    import {getShopInfo} from "@/api/index.js"
    export default {
        data() {
            return {
                vips:[]
            };
        },
        onLoad() {
           this.getvip()
        },
        methods:{
            getvip() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        that.info = res.data.shop_info.vip
                        let date = new Date(res.data.shop_info.vip.end_time * 1000)
                        const Y = date.getFullYear()
                        const M = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                        that.endTime = `${Y}/${M}`
                        let date1 = new Date(res.data.shop_info.vip.start_time * 1000)
                        const Y1 = date1.getFullYear()
                        const M1 = date1.getMonth() + 1 < 10 ? '0' + (date1.getMonth() + 1) : date1.getMonth() +
                            1
                        that.startTime = `${Y1}/${M1}`
                        
                        if (res.data.shop_info.vips.length > 0) {
                            that.vips = res.data.shop_info.vips
                        }
                    }
                })
            },
            toIndex(){
                uni.switchTab({
                    url:'/pages/index/index'
                })
            }
        }
    }
</script>

<style lang="scss">
    .container{
        box-sizing: border-box;
        padding: 0 40upx;
        border-top: 2upx solid #d2d2d7;
        .ov{
            font-size: 40rpx;
            font-weight: bold;
            color: #1D1D1F;
            line-height: 112rpx;
            width: 100%;
            height: 112upx;
            background: rgba(29,29,31,0.1);
            text-align: center;
            margin-top: 568upx;
            border-radius: 56upx;
        }
        .ctitle{
            font-size: 40rpx;
            font-weight: bold;
            color: #1D1D1F;
            width: 100%;
            text-align: center;
            box-sizing: border-box;
            padding: 80upx 0;
            border-bottom: 2upx solid #d2d2d7;
        }
        
        .ccontent{
            box-sizing: border-box;
            padding-top: 80upx;
            .c1{
                font-size: 32rpx;
                color: rgba(29,29,31,0.9);
                margin-bottom: 68upx;
            }
            .c2{
                
            }
        }
    }
</style>
