<template>
    <!-- [カスタムエイド 2026-10-20] 副管理者の登録(1/2)の見た目を店舗情報などと合わせた
         ・入力欄を共通の枠に、白いまとまりに。上に進み具合(1 名前と電話番号 → 2 権限)
         ・「次へ」は名前と電話番号がそろうと青くなる(共通の形・位置) -->
    <view class="ad">
        <u-navbar :border="true" :placeholder='true' title="副管理者の登録" :safeAreaInsetTop="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="left">
                <image src="../../../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" @click="toAddnew" mode=""></image>
            </view>
        </u-navbar>
        <view class="ad-steps">
            <view class="ad-step now"><view class="bar"></view><text>1 名前と電話番号</text></view>
            <view class="ad-step"><view class="bar"></view><text>2 権限</text></view>
        </view>
        <view class="ad-body">
            <view class="ad-info">副管理者の氏名と、電話番号(ログインのときにSMSで確認コードが届きます)を入力してください。</view>
            <view class="ad-card">
                <view class="ad-field">
                    <view class="ad-label"><text class="ad-req">※</text>副管理者名</view>
                    <input class="ad-in" :class="{ on: fk == 'name' }" v-model="name" maxlength="-1" :cursor-spacing="120"
                        placeholder-class="ad-ph" placeholder="例:鈴木 花子" @focus="fk = 'name'" @blur="fk = ''" />
                </view>
                <view class="ad-field">
                    <view class="ad-label"><text class="ad-req">※</text>電話番号(SMS認証用)</view>
                    <input class="ad-in" :class="{ on: fk == 'phone' }" v-model="phone" type="number" maxlength="-1" :cursor-spacing="120"
                        placeholder-class="ad-ph" placeholder="例:09012345678" @focus="fk = 'phone'" @blur="fk = ''" />
                </view>
            </view>
            <view class="ad-btn" :class="{ off: !ready }" hover-class="ad-btn-hover" @click="next">次へ</view>
            <text class="ad-sub ad-center" v-if="!ready">名前と電話番号を入れると押せます</text>
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                name:"",
                phone:"",
                fk: '' // [カスタムエイド 2026-10-20]
            }
        },
        computed: {
            ready() {
                return !!(String(this.name).trim() && String(this.phone).trim())
            }
        },
        methods: {
            toAddnew(){
                uni.navigateBack()
            },
            next(){
                let that = this 
                if(that.name != '' && that.phone != ''){
                    uni.navigateTo({
                        url:"../addNew/jurisdiction?name="+encodeURIComponent(that.name)+"&phone="+encodeURIComponent(that.phone)+"&type=add"
                    })
                }
            
            }
        }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-20] 店舗情報などと同じ決まり */
    .ad { min-height: 100vh; background: #F5F5F7; font-family: 'Hiragino Sans', sans-serif; color: #1D1D1F; }
    .ad-body { padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom)); }
    .ad-btn { height: 104rpx; line-height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; text-align: center; font-size: 32rpx; font-weight: 700; }
    .ad-btn.off { background: #C6DAFC; }
    .ad-btn-hover { opacity: 0.8; }
    .ad-hover { opacity: 0.6; }
    .ad-sub { display: block; font-size: 24rpx; line-height: 1.6; color: #6E6E73; }
    .ad-info { padding: 24rpx 28rpx; border-radius: 24rpx; background: #EEF4FF; font-size: 28rpx; line-height: 1.6; margin-bottom: 32rpx; }
    .ad-card { background: #FFFFFF; border-radius: 32rpx; padding: 32rpx; margin-bottom: 32rpx; }
    .ad-h3 { margin: 0 8rpx 16rpx; font-size: 30rpx; font-weight: 700; color: #515154; }
    .ad-field { margin-bottom: 28rpx; }
    .ad-field:last-child { margin-bottom: 0; }
    .ad-label { font-size: 26rpx; font-weight: 500; color: #1D1D1F; margin-bottom: 12rpx; }
    .ad-req { color: #C5221F; }
    .ad-in { width: 100%; box-sizing: border-box; height: 88rpx; padding: 0 28rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; background: #FFFFFF; font-size: 30rpx; color: #1D1D1F; transition: border-color .15s, box-shadow .15s; }
    .ad-in.on { border-color: #1A73E8; box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16); }
    .ad-ph { color: #8A8A8E; }
    .ad-steps { display: flex; background: #FFFFFF; padding: 24rpx 32rpx 28rpx; border-bottom: 1px solid #E5E5EA; }
    .ad-step { flex: 1; margin-right: 12rpx; font-size: 24rpx; color: #6E6E73; }
    .ad-step:last-child { margin-right: 0; }
    .ad-step .bar { height: 8rpx; border-radius: 4rpx; background: #E5E5EA; margin-bottom: 12rpx; }
    .ad-step.done, .ad-step.now { color: #1A73E8; }
    .ad-step.done .bar, .ad-step.now .bar { background: #1A73E8; }
    .ad-step.now { font-weight: 700; }
    .ad-center { text-align: center; margin-top: 16rpx; }
</style>
