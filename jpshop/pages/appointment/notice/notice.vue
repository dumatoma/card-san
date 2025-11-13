<template>
    <view style="position: relative;">
        <view class="ttitle">
            通知一覧 <text style="color: #1A73E8;" v-text="`(${array.length})`"></text>
        </view>
        <u-swipe-action>
            <u-swipe-action-item :options="options2" v-for="(item,index) in array" :name="index" @click="hasRead">
                <view class="swipe-action u-border-top u-border-bottom">
                    <view class="swipe-action__content" @click.stop="toDetail(item,index)">
                        <view class="noticeItem"
                            :style="item.is_read == 0?'background:rgba(143,168,248,0.1)' : '#fff' ">
                            <view class="itemLeft">
                                <view class="fill"
                                    :style="item.is_read == 0? 'background:#1A73E8':'background:transparent' "></view>
                            </view>
                            <view class="itemRight">
                                <view class="top1">
                                    <view class="name" style="margin-right: 15upx;" v-text="`${item.title}`"></view>
                                    <view class="cdate" v-text="item.create_time"></view>
                                </view>
                                <view class="top2 mgt_20">
                                    <!-- <u-icon name="checkmark-circle"></u-icon> -->
                                    <view v-text="item.day"></view>
                                    <view v-text="item.time"></view>
                                </view>
                                <view class="top3 mgt_20" v-text="item.menu_name"></view>
                                <view class="top4 mgt_20">
                                    <view>指名</view>
                                    <view class="topSqu" :style="`background:${item.appoint_admin.color}`"></view>
                                    <view v-text="item.appoint_admin.name"></view>
                                </view>
                                <view class="top4 mgt_20">
                                    <view>当日</view>
                                    <view class="topSqu" :style="`background:${item.day_admin.color}`"></view>
                                    <view v-text="item.day_admin.name"></view>
                                </view>
                                <view class="collect" @click.stop="toCollect(item.id,index)">
                                    <image v-if="item.is_collect == 0" src="../../../static/icons/uncollected.svg"
                                        mode=""></image>
                                    <image v-else src="../../../static/icons/collected.svg" mode=""></image>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </u-swipe-action-item>
        </u-swipe-action>
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
                array: []
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
                        console.log("notice",res.data.notices)
                    }
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
