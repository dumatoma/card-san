<template>
    <view class="contain">
        <u-navbar title="予約基本設定" border @leftClick="handleBack" :safeAreaInsetTop="true" bgColor="rgb(251,251,253)"
            :placeholder="true">
        </u-navbar>
        <!-- [カスタムエイド 2026-10-22] 予約基本設定をほかの設定画面と同じ見た目に(白いカード・行の区切り・小さな「詳細設定」ボタン)。処理は変更なし -->
        <view class="bs-body">
            <view class="bs-card">
                <view class="bs-row" hover-class="bs-hover" @click="toOut">
                    <view class="bs-row-t">予約ページのURL(外部リンク)</view>
                    <view class="bs-chev"></view>
                </view>
            </view>

            <view class="bs-card">
                <view class="bs-row bs-row-sw">
                    <view class="bs-row-t">指名制予約を利用する</view>
                    <u-switch v-model="value" @change="change" activeColor="#34C759"></u-switch>
                </view>
                <view class="bs-note">オフにすると、お客さまは担当者を指名できません。予約に対応するスタッフの人数分の枠で予約を受け付けます。</view>
            </view>

            <view class="bs-sec">予約に対応するスタッフ</view>
            <view class="bs-cap">チェックを入れたスタッフが予約を受け付けます。「詳細設定」で対応するメニュー・指名料金・予約表の色を決められます。</view>
            <view class="bs-card">
                <view class="bs-staff bs-left" v-if="value" @click="openZ">
                    <view class="selectContent">
                        <view class="unselect" v-if="no_appoint == 0"></view>
                        <view class="selected" v-if="no_appoint == 1"><view class="fill"></view></view>
                    </view>
                    <view class="bs-sq" style="background: #8E8E93;"></view>
                    <view class="bs-name" :class="{ on: no_appoint == 1 }">指名なし</view>
                </view>
                <view class="bs-staff" v-for="(item,index) in manager" :key="index">
                    <view class="bs-staff-l" @click="choosePerson(item.id,index)">
                        <view class="selectContent">
                            <view class="unselect" v-if="!item.is_appoint"></view>
                            <view class="selected" v-if="item.is_appoint"><view class="fill"></view></view>
                        </view>
                        <view class="bs-sq" :style="{ background: item.color }"></view>
                        <view class="bs-name" :class="{ on: item.is_appoint }">{{ item.name }}</view>
                    </view>
                    <view class="bs-detail" v-if="item.is_appoint" hover-class="bs-hover" @click="goDetail(item)">詳細設定</view>
                </view>
            </view>
        </view>
    </view>
</template>
<script>
    import {
        getShopInfo,
        getStaffList,
        setPoint,
        setStaffAppointInfo
    } from '@/api/index.js'
    export default {
        name: "add",
        props: {
            config: Object
        },
        data() {
            return {
                value: false,
                manager: [],
                no_appoint:0
            };
        },
        created() {
            this.getShopInfo()
            this.getStaffList()
        },
        methods: {
            openZ(){
              let that = this
              let data = {}
              data.type = "no_appoint"
              data.no_appoint = that.no_appoint == 0?1:0
              setPoint(data).then((res) => {
                  if(res.code == 200){
                      that.no_appoint = data.no_appoint
                  }
              })
            },
            handleBack() {
                this.$emit("close", true)
            },
            change(e) {
                let data = {}
                data.type = "is_appoint"
                data.is_appoint = e == true? 1 : 0
                setPoint(data).then((res) => {
                    if(res.code == 200){
                        uni.showToast({
                            title:res.message,
                        })
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
            },
            getStaffList() {
                let that = this
                let data = {
                    id: "",
                    start_time: "",
                    end_time: ""
                }
                getStaffList(data).then((res) => {
                    if (res.code == 200) {
                        res.data.admins.forEach((val, index) => {
                            val.checked = false
                            if (val.color == "") {
                                val.color = '#175491'
                            }
                        })
                        that.manager = res.data.admins
                        that.no_appoint = res.data.no_appoint
                    }
                })
            },
            getShopInfo() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        if (res.data.shop_info.is_appoint == 1) {
                            that.value = true
                        } else {
                            that.value = false
                        }
                    }
                })
            },
            choosePerson(e,idx) {
                let that = this
                let data = {}
                data.id = e
                data.is_appoint = !that.manager[idx].is_appoint
                setStaffAppointInfo(data).then((res) => {
                    if(res.code == 200){
                        that.manager[idx].is_appoint = !that.manager[idx].is_appoint
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
                // let arr = that.manager
                // arr.forEach((value, index) => {
                //     if (e == index) {
                //         value.checked = true
                //     } else {
                //         value.checked = false
                //     }
                // })
                // that.manager = arr
            },
            goDetail(e) {
                this.$emit("goDetail", e)
            },
            toOut(){
                this.$emit("toOut", 1)
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-22] 予約基本設定(ほかの設定画面と同じ決まり) */
    .bs-body { padding: 48rpx 24rpx calc(80rpx + env(safe-area-inset-bottom)); }
    .bs-card { background: #FFFFFF; border-radius: 32rpx; padding: 0 32rpx; margin-bottom: 48rpx; } /* [カスタムエイド 2026-10-22] カードのあいだを1.5倍に */
    .bs-row { display: flex; align-items: center; justify-content: space-between; min-height: 120rpx; }
    .bs-row-t { font-size: 30rpx; color: #1D1D1F; }
    .bs-chev { width: 16rpx; height: 16rpx; border-top: 3rpx solid #C7C7CC; border-right: 3rpx solid #C7C7CC; transform: rotate(45deg); margin-right: 8rpx; }
    .bs-note { font-size: 24rpx; color: #6E6E73; line-height: 1.6; padding: 0 0 32rpx; border-top: 1px solid #E5E5EA; padding-top: 24rpx; }
    .bs-sec { font-size: 28rpx; color: #1D1D1F; font-weight: bold; padding: 8rpx 8rpx 0; line-height: 1.2; }
    .bs-cap { font-size: 24rpx; color: #6E6E73; line-height: 1.6; padding: 12rpx 8rpx 24rpx; }
    .bs-staff { display: flex; align-items: center; justify-content: space-between; min-height: 128rpx; border-bottom: 1px solid #E5E5EA; }
    .bs-staff:last-child { border-bottom: 0; }
    .bs-staff.bs-left { justify-content: flex-start; }
    .bs-staff-l { flex: 1; display: flex; align-items: center; min-height: 128rpx; }
    .bs-staff .selectContent { margin-right: 24rpx; }
    .bs-sq { width: 28rpx; height: 28rpx; border-radius: 8rpx; margin-right: 16rpx; flex-shrink: 0; }
    .bs-name { font-size: 30rpx; color: #8A8A8E; }
    .bs-name.on { color: #1D1D1F; }
    .bs-detail { height: 64rpx; line-height: 62rpx; padding: 0 28rpx; border: 1px solid #1A73E8; border-radius: 32rpx; color: #1A73E8; font-size: 26rpx; box-sizing: border-box; flex-shrink: 0; }
    .bs-hover { opacity: 0.6; }

    .hit{
        background: rgba(29, 29, 31, .1) !important;
    }
    .contain {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: #F5F5F7;
        z-index: 999;
        overflow-y: scroll;

        .top1 {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 64upx 40upx 44upx;
            justify-content: space-between;
            border-bottom: 2upx solid #d2d2d7;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: rgba(29, 29, 31, 0.9);
        }

        .top2 {
            box-sizing: border-box;
            padding: 10upx 40upx 60upx;
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            border-bottom: 2upx solid #d2d2d7;
        }

        .top3 {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            background: #F5F5F7;
            box-sizing: border-box;
            padding: 80upx 40upx 44upx;
            border-bottom: 2upx solid #d2d2d7;
        }

        .top4 {
            .item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-sizing: border-box;
                padding: 60upx 40upx;
                border-bottom: 2upx solid #d2d2d7;

                .itemLeft {
                    display: flex;
                    align-items: center;
                    font-size: 40upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;

                    .selectContent {
                        margin-right: 40upx;
                    }

                    .colorSq {
                        width: 40upx;
                        height: 40upx;
                        border-radius: 8upx;
                        opacity: 1;
                        margin-left: 32upx;
                    }
                }

                .itemRight {
                    width: 184upx;
                    height: 88upx;
                    background: #1A73E8;
                    opacity: 1;
                    font-size: 32upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #FFFFFF;
                    border-radius: 18upx;
                    text-align: center;
                    line-height: 88upx;
                }
            }

        }
    }
</style>
