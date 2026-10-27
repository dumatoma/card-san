<template>
    <view class="co">
        <!-- [カスタムエイド 2026-10-25] 見出しを、予約通知・予約内容の画面と同じ共通の見出しに(戻る矢印は u-navbar の標準)。
             以前は左に独自の矢印を入れていて、表示が崩れ「undefined」と出て戻れないことがあった -->
        <u-navbar title="予約詳細" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-22] 予約詳細を、予約内容(編集)の画面と同じカード型に。お休み・休憩はお客さまの欄を出さない -->
        <!-- [カスタムエイド 2026-10-25] 読み込みが終わるまで中身を出さない(読み込み前に info.menu.title を読もうとして、画面の作りが失敗していた) -->
        <view class="dt-pg" v-if="info.id">
            <view class="dt-when">
                <view class="dt-day">{{ info.day }}({{ info.week }})</view>
                <view class="dt-hm">{{ info.time }}</view>
                <view class="dt-cre" v-if="info.create_time">受付 {{ info.create_time }}</view>
            </view>

            <view class="dt-card" v-if="!isOff">
                <view class="dt-row">
                    <view class="dt-lab">氏名</view>
                    <view class="dt-val dt-link">{{ info.name || 'ー' }}</view>
                </view>
                <view class="dt-row">
                    <view class="dt-lab">電話番号</view>
                    <view class="dt-val">{{ info.phone || 'ー' }}</view>
                </view>
                <view class="dt-row">
                    <view class="dt-lab">会員ID</view>
                    <view class="dt-val dt-gray">{{ info.member_id || 'ー' }}</view>
                </view>
                <view class="dt-row">
                    <view class="dt-lab">予約番号</view>
                    <view class="dt-val dt-gray">{{ info.order_no }}</view>
                </view>
            </view>

            <view class="dt-sec">メニュー</view>
            <view class="dt-card dt-pad">
                <view class="dt-mt" :class="{ 'dt-red': isOff && offName == 'お休み', 'dt-green': isOff && offName != 'お休み' }">{{ isOff ? offName : (info.menu ? info.menu.title : '') }}</view>
                <view class="dt-ms" v-if="!isOff && info.menu">所要時間 {{ info.menu.order_time }}分<text class="dt-price" v-if="info.menu.price">{{ info.menu.price }}</text></view>
            </view>

            <view class="dt-sec">担当</view>
            <view class="dt-card">
                <view class="dt-row">
                    <view class="dt-lab">指名担当</view>
                    <view class="dt-person">
                        <view class="dt-dot" :style="{ background: (info.appoint_admin && info.appoint_admin.color) || '#d2d2d7' }"></view>
                        <text>{{ info.appoint_admin ? info.appoint_admin.name : '指名無し' }}</text>
                    </view>
                </view>
                <view class="dt-row">
                    <view class="dt-lab">当日担当</view>
                    <view class="dt-person">
                        <view class="dt-dot" :style="{ background: day_admin.color || '#d2d2d7' }"></view>
                        <text>{{ day_admin.name }}</text>
                    </view>
                </view>
            </view>

            <view class="dt-sec">メモ</view>
            <view class="dt-card dt-pad">
                <view class="dt-memo" :class="{ 'dt-gray': !info.remark }">{{ info.remark || 'メモはありません' }}</view>
            </view>

            <view class="dt-card dt-pad" v-if="isOff">
                <view class="dt-row dt-row-last">
                    <view class="dt-lab">予約番号</view>
                    <view class="dt-val dt-gray">{{ info.order_no }}</view>
                </view>
            </view>

            <view class="dt-btn" hover-class="dt-hover" @click="toEdit">予約内容を編集</view>
        </view>
        <u-popup :show="show"></u-popup>
        <u-popup :show="show1" @close="show1 = false" @cancel="show1 = false" >
            <view class="maskContent">
                <view class="mkitem" style="color: #1A73E8;" @click="sendMessage">
                   <text>この内容を送る</text> <image src="../../../../static/images/send.png" mode="aspectFit"></image>
                </view>
                <view class="mkitem" style="color: #D93025;" @click="show1 = false">
                   <text>キャンセル</text>
                </view>
            </view>
        </u-popup>
        <u-popup :show="mask" mode="center" @cancel="mask = false" @close="mask = false">
            <view class="ms">
                予約内容変更メッセージ”の設定がOFFになっています。 メッセージを送るには、「メッセージ設定」の「予約内容変更」をONに設定してください。
            </view>
        </u-popup>
    </view>
</template>

<script>
    import {
        getAppointOrderDetail,
        deleteAppointOrder,
        cahngeInof
    } from '@/api/index.js'
    export default {
        data() {
            return {
                mask:false,
                show: false,
                info: {},
                id:"",
                day_admin:{},
                show1:false
            };
        },
        created() {
           
        },
        onLoad(options) {
            let that = this
            let query = options
            that.id = query.id
            that.getDetail(query.id)
            
        },
        onShow() {
            if(this.id){
                this.getDetail(this.id) 
            }
            
        },
        computed: {
            // [カスタムエイド 2026-10-22] メニューのない予約 = お休み・休憩・勤務時間外
            isOff() {
                return !!(this.info && this.info.id) && !this.info.menu
            },
            offName() {
                return this.info.name == 'お休み' ? 'お休み' : '休憩・勤務時間外'
            }
        },
        methods: {
            sendMessage(){
              let that = this
                 cahngeInof(that.info.id).then((res) => {
                     if(res.code == 200){
                         that.show1 = false
                         uni.showToast({
                             title:res.message
                         })
                     }else if(res.code == 400){
                         uni.hideToast()
                          that.show1 = false
                         that.mask = true
                     }else{
                         uni.showToast({
                             title:res.message,
                             icon:"none"
                         })
                     }
                 })
            },
            toEdit() {
                let that = this
                uni.navigateTo({
                    url: "../detail/edit?id="+that.id
                })
            },
            deleteOrder(){
              let that = this
              deleteAppointOrder(that.info.id).then((res) => {
                  if(res.code == 200){
                      uni.showToast({
                          title:res.message
                      })
                      setTimeout(() => {
                         uni.navigateBack() 
                      },1500)
                  }else{
                      uni.showToast({
                          title:res.message,
                          icon:"none"
                      })
                  }
              })
            },
            getDetail(id) {
                let that = this
                getAppointOrderDetail(id).then((res) => {
                    if (res.code == 200) {
                        that.info = res.data.order
                        that.day_admin = res.data.order.day_admin
                    } else {
                        uni.showToast({
                            title: res.message
                        })
                    }
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-22] 予約詳細(予約内容の画面と同じ見た目) */
    .dt-pg { padding: 48rpx 0 calc(96rpx + env(safe-area-inset-bottom)); }
    .dt-when { background: #FFFFFF; border-radius: 32rpx; padding: 32rpx; margin-bottom: 56rpx; } /* [カスタムエイド 2026-10-22] カードのあいだを広めに */
    .dt-day { font-size: 30rpx; color: #1D1D1F; }
    .dt-hm { font-size: 44rpx; font-weight: bold; color: #1D1D1F; margin-top: 8rpx; }
    .dt-cre { font-size: 22rpx; color: #8A8A8E; margin-top: 12rpx; }
    .dt-card { background: #FFFFFF; border-radius: 32rpx; padding: 0 32rpx; margin-bottom: 56rpx; }
    .dt-pad { padding: 28rpx 32rpx; }
    .dt-sec { font-size: 26rpx; color: #6E6E73; padding: 0 8rpx; margin-bottom: 16rpx; line-height: 1.2; }
    .dt-row { display: flex; align-items: center; justify-content: space-between; min-height: 100rpx; border-bottom: 1px solid #E5E5EA; }
    .dt-row:last-child { border-bottom: 0; }
    .dt-row-last { border-bottom: 0; min-height: 0; }
    .dt-lab { flex-shrink: 0; width: 180rpx; font-size: 30rpx; color: #1D1D1F; }
    .dt-val { flex: 1; text-align: right; font-size: 30rpx; color: #1D1D1F; word-break: break-all; }
    .dt-link { color: #1A73E8; }
    .dt-gray { color: #6E6E73; }
    .dt-person { display: flex; align-items: center; font-size: 30rpx; color: #1D1D1F; }
    .dt-dot { width: 28rpx; height: 28rpx; border-radius: 8rpx; margin-right: 16rpx; }
    .dt-mt { font-size: 30rpx; font-weight: bold; color: #1D1D1F; line-height: 1.4; }
    .dt-ms { font-size: 24rpx; color: #6E6E73; margin-top: 8rpx; }
    .dt-price { color: #D93025; margin-left: 24rpx; }
    .dt-red { color: #D93025; }
    .dt-green { color: #137333; }
    .dt-memo { font-size: 28rpx; line-height: 1.7; white-space: pre-wrap; color: #1D1D1F; }
    .dt-btn { margin-top: 64rpx; height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-weight: bold; display: flex; align-items: center; justify-content: center; }
    .dt-hover { opacity: 0.6; }

    .blank{
        height: 100upx;
    }
    .ms{
        box-sizing: border-box;
        padding: 40upx;
        background: #fff;
        width: 70vw;
        border-radius: 28upx;
        text-align: center;
    }
    .mkitem{
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        width: 90%;
        margin: 20upx auto;
        border-radius: 28upx;
        box-sizing: border-box;
        padding: 36upx;
        font-size:40upx;
        image{
            width: 44upx;
            height: 44upx;
        }
    }
    // ::v-deep .u-popup__content{
    //     background: transparent !important;
    // }
    .maskContent{
        width: 100vw;
        background: transparent;
    }
    .btnBlue{
        font-size: 40upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
        text-align: center;
        margin-top: 96upx;
    }
    .btnRed{
        font-size: 40upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #D93025;
        text-align: center;
        margin-top: 72upx;
    }
    .co {
        box-sizing: border-box;
        padding: 0 32rpx;
        min-height: 100vh;
        background: #F2F2F7;

        .tips {
            box-sizing: border-box;
            padding: 28upx;
            background: #F5F5F7;
            color: #1D1D1F;
            border-radius: 20upx;
            font-size: 28upx;
            margin-top: 80upx;

            .connect {
                color: #1A73E8;
                margin-top: 20upx;
            }
        }

        .content {
            border-bottom: 2upx solid #d2d2d7;
            box-sizing: border-box;
            padding: 32upx 0 58upx 0;

            .detail {
                font-size: 32upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }

            .tiem {
                display: flex;
                align-items: center;
                font-size: 32upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                margin-top: 20upx;

                .price {
                    color: rgba(217, 48, 37, 0.65);
                }

                .time {
                    color: #1D1D1F;
                }

            }

            .coTime {
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #AAAAAA;
                text-align: right;
            }

            .item {
                display: flex;
                align-items: center;
                margin-top: 32upx;
                font-size: 32upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;

                .item_left {
                    width: 160upx;
                }

                .item_right {
                    display: flex;
                    align-items: center;

                    .squ {
                        width: 32upx;
                        height: 32upx;
                        border-radius: 8upx;
                        margin-right: 20upx;
                    }
                }
            }
        }
    }
</style>
