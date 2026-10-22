<template>
    <view class="nc-page">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="予約通知" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-22] 予約通知をカード型に(未読は青い点と薄い青、左にスライドで既読・削除は今までどおり) -->
        <view class="nc-head">
            <view class="nc-ttl">通知一覧<text class="nc-cnt">{{ array.length }}件</text></view>
            <view class="nc-tip">左にスライドで「既読」「削除」</view>
        </view>
        <view class="nc-empty" v-if="loaded && array.length == 0">予約の通知はまだありません</view>
        <u-swipe-action>
            <view class="nc-wrap" v-for="(item,index) in array" :key="item.id">
            <u-swipe-action-item :options="options2" :name="index" @click="hasRead">
                <view class="nc-card" :class="{ unread: item.is_read == 0 }" @click.stop="toDetail(item,index)">
                    <view class="nc-top">
                        <view class="nc-dot" v-if="item.is_read == 0"></view>
                        <view class="nc-title">{{ item.title }}</view>
                        <view class="nc-time">{{ item.create_time }}</view>
                    </view>
                    <view class="nc-when">
                        <text>{{ item.day }}</text><text class="nc-hm">{{ item.time }}</text>
                    </view>
                    <view class="nc-menu" v-if="item.menu_name">{{ item.menu_name }}</view>
                    <view class="nc-bottom">
                        <view class="nc-staff">
                            <view class="nc-lab">指名</view>
                            <view class="nc-sq" :style="{ background: item.appoint_admin.color }"></view>
                            <view class="nc-sn">{{ item.appoint_admin.name }}</view>
                            <view class="nc-lab nc-lab2">当日</view>
                            <view class="nc-sq" :style="{ background: item.day_admin.color }"></view>
                            <view class="nc-sn">{{ item.day_admin.name }}</view>
                        </view>
                        <view class="nc-collect" @click.stop="toCollect(item.id,index)">
                            <image v-if="item.is_collect == 0" src="../../../static/icons/uncollected.svg" mode="aspectFit"></image>
                            <image v-else src="../../../static/icons/collected.svg" mode="aspectFit"></image>
                        </view>
                    </view>
                </view>
            </u-swipe-action-item>
            </view>
        </u-swipe-action>
        <view style="height: calc(48rpx + env(safe-area-inset-bottom));"></view>
    </view>
</template>

<script>
    import {
        getAppointNoticeList,
        changeAppointNotice,
        delAppointNotice
    } from "@/api/index.js"
    export default {
        data() {
            return {
                number: 0,
                page: 1,
                options2: [{
                    text: '既読',
                    style: {
                        backgroundColor: '#1A73E8'
                    }
                }, {
                    text: '削除',
                    style: {
                        backgroundColor: '#D93025 '
                    }
                }],
                array: [],
                loaded: false
            }
        },
        created() {
            
        },
        onShow() {
            this.getList()
        },
        methods: {
            toDetail(item,index) {
                let that = this
                 let id = that.array[index].id
                let data = {}
                data._method = "put"
                data.type = "read"
                                   
                changeAppointNotice(data, id).then((res) => {
                    if (res.code == 200) {
                       uni.navigateTo({
                           url: "../notice/detail/detail?id="+item.oid
                       })
                       that.array[index].is_read = 1
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
               
            },
            getList() {
                let that = this
                getAppointNoticeList(that.page).then((res) => {
                    let duibi = {}
                    if (res.code == 200) {
                        res.data.notices.forEach((val, index) => {
                            // console.log(val)
                            if(val.day_admin){  
                               if (val.day_admin.color == "") {
                                   val.day_admin.color = '#175491'
                               }
                            }
                           
                            if(val.appoint_admin){
                                if(val.appoint_admin.color == ""){
                                    val.appoint_admin.color = '#175491'
                                }
                            }
                            
                            if (val.appoint_admin.length == 0) {
                                val.appoint_admin = {}
                                val.appoint_admin.color = "#d2d2d7"
                                val.appoint_admin.name = "指名無し"
                            }
                            
                            if (val.day_admin.length == 0) {
                                val.day_admin = {}
                                val.day_admin.color = "#d2d2d7"
                                val.day_admin.name = "指名無し"
                            }
                        })
                        that.array = res.data.notices
                    }
                    that.loaded = true
                })
            },
            toCollect(id, index) {
                let that = this
                let data = {}
                data._method = "put"
                data.type = "collect"
                changeAppointNotice(data, id).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                        if (that.array[index].is_collect == 0) {
                            that.array[index].is_collect = 1
                        } else {
                            that.array[index].is_collect = 0
                        }
                    }
                })
            },
            hasRead(e){
                let that = this
                let index = e.name
                let type = e.index
                let id = that.array[index].id
                if(type == 0){
                    let data = {}
                    data._method = "put"
                    data.type = "read"
                    changeAppointNotice(data, id).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                           that.array[index].is_read = 1
                        }else{
                            uni.showToast({
                                title:res.message,
                                icon:"none"
                            })
                        }
                    })
                }else{
                    delAppointNotice(id).then((res) => {
                        if(res.code == 200){
                            uni.showToast({
                                title: res.message
                            })
                            // that.getList()
                            setTimeout(() => {
                                uni.redirectTo({
                                    url:"../notice/notice"
                                })
                            },2500)
                           
                        }else{
                            uni.showToast({
                                title:res.message,
                                icon:"none"
                            })
                        }
                    })
                }
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-22] 予約通知(カード型) */
    page { background: #F2F2F7; }
    .nc-page { min-height: 100vh; background: #F2F2F7; }
    .nc-head { display: flex; align-items: flex-end; justify-content: space-between; padding: 48rpx 40rpx 24rpx; }
    .nc-ttl { font-size: 32rpx; color: #1D1D1F; font-weight: bold; }
    .nc-cnt { font-size: 26rpx; color: #1A73E8; margin-left: 12rpx; font-weight: normal; }
    .nc-tip { font-size: 22rpx; color: #8A8A8E; }
    .nc-empty { text-align: center; color: #8A8A8E; font-size: 26rpx; padding: 120rpx 0; }
    .nc-wrap { margin: 0 32rpx 30rpx; /* [カスタムエイド 2026-10-22] カードのあいだを1.5倍に */ border-radius: 28rpx; overflow: hidden; }
    .nc-card { background: #FFFFFF; padding: 28rpx 32rpx; width: 100%; box-sizing: border-box; }
    .nc-card.unread { background: #F2F7FF; }
    .nc-top { display: flex; align-items: center; }
    .nc-dot { width: 16rpx; height: 16rpx; border-radius: 50%; background: #1A73E8; margin-right: 12rpx; flex-shrink: 0; }
    .nc-title { flex: 1; font-size: 28rpx; font-weight: bold; color: #1D1D1F; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .nc-time { font-size: 22rpx; color: #8A8A8E; margin-left: 16rpx; flex-shrink: 0; }
    .nc-when { margin-top: 16rpx; font-size: 30rpx; color: #1D1D1F; }
    .nc-hm { margin-left: 20rpx; font-weight: bold; }
    .nc-menu { margin-top: 8rpx; font-size: 26rpx; color: #515154; display: -webkit-box; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 1; }
    .nc-bottom { margin-top: 20rpx; padding-top: 20rpx; border-top: 1px solid #EEEEF0; display: flex; align-items: center; justify-content: space-between; }
    .nc-staff { display: flex; align-items: center; flex-wrap: wrap; font-size: 24rpx; color: #515154; flex: 1; }
    .nc-lab { color: #8A8A8E; }
    .nc-lab2 { margin-left: 28rpx; }
    .nc-sq { width: 24rpx; height: 24rpx; border-radius: 6rpx; margin: 0 8rpx 0 12rpx; flex-shrink: 0; }
    .nc-sn { color: #1D1D1F; }
    .nc-collect { width: 56rpx; height: 56rpx; flex-shrink: 0; margin-left: 16rpx; }
    .nc-collect image { width: 100%; height: 100%; }

    .collect {
        width: 60upx;
        height: 60upx;
        position: absolute;
        bottom: 40upx;
        right: 40upx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .ttitle {
        box-sizing: border-box;
        padding: 60upx 0 18upx 32upx;
        background: #fff;
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
    }

    .noticeItem {
        display: flex;
        box-sizing: border-box;
        padding: 40upx 80upx 40upx 40upx;
        width: 100%;
        position: relative;

        .itemLeft {
            margin-right: 38upx;

            .fill {
                width: 32upx;
                height: 32upx;
                border-radius: 50%;
            }
        }

        .itemRight {
            .top1 {
                display: flex;
                align-items: center;
                justify-content: space-between;

                .name {
                    font-size: 28upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }

                .cdate {
                    font-size: 24upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                }
            }

            .top2 {
                display: flex;
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;

                view:nth-child(2) {
                    margin: 0 20upx;
                }
            }

            .top3 {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                display: -webkit-box;
                overflow: hidden;
                -webkit-box-orient: vertical; //子元素应该被水平或垂直排列
                -webkit-line-clamp: 2; //3行后显示省略号
            }

            .top4 {
                display: flex;
                align-items: center;
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: rgba(29, 29, 31, 0.9);

                .topSqu {
                    width: 28upx;
                    height: 28upx;
                    border-radius: 8upx;
                    opacity: 1;
                    margin: 0 10upx 0 20upx;
                }
            }
        }

    }
</style>
