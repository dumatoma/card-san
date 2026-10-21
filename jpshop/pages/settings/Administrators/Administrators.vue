<template>
    <!-- [カスタムエイド 2026-10-20] 副管理者一覧を1人ずつのカードに(店舗情報などと同じ見た目)
         ・名前と電話番号(副管理者として登録した内容だけ。スタッフ登録の情報は使わない)。押すと編集画面
         ・削除はゴミ箱のボタン(横スワイプはやめた)。「副管理者を追加」の大きいボタン(右上の+も残す) -->
    <view class="ad">
        <u-navbar :border="true" :placeholder='true' :autoBack="true" title="副管理者一覧" :safeAreaInsetTop="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="right">
                <image src="../../../static/svg/addblue.svg" style="width: 60upx;height: 60upx;" @click="toAddnew"
                    mode=""></image>
            </view>
        </u-navbar>
        <view class="ad-body">
            <view class="ad-btn ad-add" hover-class="ad-btn-hover" @click="toAddnew"><text class="ad-plus">+</text>副管理者を追加</view>
            <text class="ad-sub ad-lead">副管理者は、オーナーが選んだ権限の範囲で管理アプリを使えます。押すと名前・電話番号・権限を変更できます。</text>
            <view class="ad-empty" v-if="loaded && !array.length">副管理者はまだ登録されていません</view>
            <view class="ad-item" v-for="(item,index) in array" :key="item.id">
                <view class="ad-item-main" hover-class="ad-hover" @click="toCheckDetail(item.id)">
                    <view class="ad-avatar"><text>{{(item.name || '?').slice(0, 1)}}</text></view>
                    <view class="ad-item-text">
                        <text class="ad-name">{{item.name}}</text>
                        <text class="ad-phone">電話番号 {{item.phone}}</text>
                    </view>
                    <u-icon name="arrow-right" size="16" color="#AEAEB2"></u-icon>
                </view>
                <view class="ad-del" hover-class="ad-hover" @click="deleteItem(index)">
                    <u-icon name="trash" size="18" color="#C5221F"></u-icon>
                </view>
            </view>
        </view>
        <mod :config="config" @getStatus="confirm" v-if="show"></mod>
    </view>
</template>

<script>
    import {
        getAdminList,
        deleteAdmin
    } from '@/api/index.js'
    import mod from '@/components/mod.vue'
    export default {
        components: {
            mod
        },
        data() {
            return {
                options1: [{
                    text: '削除',
                    style: {
                        backgroundColor: '#D93025'
                    }
                }],
                array: [],
                config:{},
                show: false,
                current: 0,
                name:"",
                loaded: false // [カスタムエイド 2026-10-20]
            };
        },
        created() {
            this.getAdminList()
        },
        methods: {
            toAddnew() {
                uni.navigateTo({
                    url: "addNew/addNew"
                })
            },
            toCheckDetail(id) {
                uni.navigateTo({
                    url: "addNew/jurisdiction?type=edit&id=" + id
                })
            },
            getAdminList() {
                let that = this
                getAdminList().then((res) => {
                    that.loaded = true
                    if (res.code == 200) {
                        that.array = res.data.admins
                    } else if (res.code == 403) {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                        setTimeout(() => {
                            uni.navigateBack()
                        }, 1500)
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },
            deleteItem(index) {
                let that = this
                that.current = index
                that.show = true
                that.config={
                    type: "button",
                    cancel: true,
                    default: true,
                    title: "この管理者を削除",
                    content: that.array[index].name+"を削除しますか？",
                    cancelText: "削除",
                    defaultText: "キャンセル"
                }
            },
            confirm(e) {
                let that = this
                if (e == "default") {
                    this.show = false
                } else {
                    let index = that.current * 1
                    let id = that.array[index].id
                    deleteAdmin(id).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            that.getAdminList()
                        } else if (res.code == 403) {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                            setTimeout(() => {
                                uni.navigateBack()
                            }, 1500)
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                }
                that.show = false
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
    .ad-add { display: flex; align-items: center; justify-content: center; margin-bottom: 20rpx; }
    .ad-plus { font-size: 44rpx; line-height: 1; margin-right: 12rpx; position: relative; top: -2rpx; }
    .ad-lead { padding: 0 8rpx; margin-bottom: 28rpx; }
    .ad-empty { padding: 80rpx 32rpx; text-align: center; font-size: 28rpx; color: #6E6E73; background: #FFFFFF; border-radius: 32rpx; }
    .ad-item { display: flex; align-items: center; background: #FFFFFF; border-radius: 32rpx; padding: 0 20rpx 0 28rpx; margin-bottom: 20rpx; box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05); }
    .ad-item-main { flex: 1; min-width: 0; display: flex; align-items: center; min-height: 140rpx; }
    .ad-avatar { width: 80rpx; height: 80rpx; border-radius: 50%; background: #E8F0FE; color: #1A73E8; font-size: 32rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; margin-right: 24rpx; flex-shrink: 0; }
    .ad-item-text { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .ad-name { font-size: 30rpx; font-weight: 700; margin-bottom: 6rpx; }
    .ad-phone { font-size: 26rpx; color: #515154; font-variant-numeric: tabular-nums; }
    .ad-del { width: 88rpx; height: 76rpx; margin-left: 16rpx; border: 1px solid #D2D2D7; border-radius: 20rpx; box-sizing: border-box; display: flex; align-items: center; justify-content: center; }
</style>
