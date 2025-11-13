<template>
    <view>
        <u-popup :show="caiDanShow" mode="right" @close="close" @open="open">
            <view class="ss">
                <view class="item" @click="person()">
                    <view class="le">
                        <image src="../static/image/list-user.png" mode=""></image>
                    </view>
                    <view class="ri">
                        アカウント
                    </view>
                </view>
                <view class="item" @click="jump('My Card')">
                    <view class="le">
                        <image src="../static/image/list-card.png" mode="" class="img3"></image>
                    </view>
                    <view class="ri">
                        My Card
                    </view>
                </view>
                <view class="item" @click="jump('店舗情報')">
                    <view class="le">
                        <image src="../static/image/list-gps.png" mode="" class="img4"></image>
                    </view>
                    <view class="ri">
                        店舗情報
                    </view>
                </view>
                <view class="item" v-if="temp.includes('メッセージ')"  @click="jump('メッセージ')">
                    <view class="le">
                        <image src="../static/image/list-message.png" mode="" class="img2"></image>
                    </view>
                    <view class="ri">
                        メッセージ
                    </view>
                    <view class="ris bb" v-text="number" v-if="number > 0"></view>
                </view>
                <view class="item" v-if="temp.includes('クーポン')"  @click="jump('クーポン')">
                    <view class="le">
                        <image src="../static/image/list-copun.png" mode=""></image>
                    </view>
                    <view class="ri">
                        クーポン
                    </view>
                    <view class="ris bb" v-text="number1" v-if="number1 > 0"></view>
                </view>
                <view class="item" v-if="temp.includes('メニュー')"  @click="jump('メニュー')">
                    <view class="le">
                        <image src="../static/image/list-caidan.png" mode=""></image>
                    </view>
                    <view class="ri">
                        メニュー
                    </view>
                </view>
                <view class="item" v-if="temp.includes('お知らせ')"  @click="jump('お知らせ')">
                    <view class="le">
                        <image src="../static/image/list-bell.png" mode=""></image>
                    </view>
                    <view class="ri">
                        お知らせ
                    </view>
                     <view class="ris bb" v-text="number2" v-if="number2 > 0"></view>
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import {getUnreadNum,getMewCoupon,getNewNotice} from "@/api/index.js"
    export default {
        name: "list",
        props: {
            caiDanShow: Boolean,
            tabs: Array,
            mid: String,
            sid: String
        },
        data() {
            return {
                temp:[],
                number:0,
                number1:0,
                number2:0
            };
        },
        onShow() {
          
        },
        methods: {
            open() {
                let that = this
                that.show = true
                console.log("sid",this.sid)
                getUnreadNum(this.sid).then((res) => {
                    if(res.code == 200){
                        that.number = res.data.unread_num
                    }
                })
                getMewCoupon(this.sid).then((res) => {
                    if(res.code == 200){
                        that.number1 = res.data.count
                    }
                })
                
                getNewNotice(this.sid).then((res) => {
                    if(res.code == 200){
                        that.number2 = res.data.count
                    }
                })
                  
                that.tabs.forEach((val,index) => {
                    that.temp.push(val.name)
                })
            },
            person() {
                uni.navigateTo({
                    url: '../../pagesA/PersonalInformation/PersonalInformation?id='+this.mid
                })
            },
            
            close() {
                this.$emit("seed", false); //参数1：自定义事件；参数2：要传递的值
            },
            jump(e){
                console.log("sss",e)
                this.$emit("selected",e)
            }
        }
    }
</script>

<style lang="scss" scoped>
    ::v-deep .u-transition {
        width: 500rpx;
    }

    .ss {
        margin-top: 194rpx;
    }

    .item {
        display: flex;
        // justify-content: space-between;
        align-items: center;
        height: 112rpx;
        padding: 0 32rpx;
        border-bottom: 1px solid #D2D2D7;

        .le {
            width: 60rpx;

            image {
                width: 47rpx;
                height: 47rpx;
            }

            .img1 {
                width: 60rpx;
                height: 60rpx;
            }

            .img2 {
                width: 52rpx;
                height: 44rpx;
            }

            .img3 {
                width: 60rpx;
                height: 43rpx;
            }

            .img4 {
                width: 28rpx;
                height: 40rpx;
            }
        }

        .ri {
            width: 300rpx;
            font-size: 32rpx;
            color: #1D1D1F;
            margin-left: 28rpx;
        }

        .bb {
            width: 52rpx;
            height: 52rpx;
            line-height: 52rpx;
            text-align: center;
            border-radius: 50%;
            font-size: 32rpx;
            color: #fff;
            background: #D93025;
        }

        .ris {


            image {
                width: 52rpx;
                height: 52rpx;
            }
        }
    }
</style>
