<template>
    <view>
        <view class="mask" v-if="showMask == true">
            <view class="maskContent">
                
                <view class="avatar">
                    <image :src="avatar" mode="aspectFill"></image>
                </view>
                <view class="userName" v-text="nickname">
                    
                </view>
                
                <view>
                   Instagramビジネスアカウントと連携中です。
                </view>
                <!-- <view>
                    通常のお知らせ機能を利用する
                </view>
                <view>
                     場合は、“お知らせ設定”の設定
                </view>
                <view>
                    を変更してください。
                </view> -->
            </view>
        </view>
        <u-navbar leftText="返回" :placeholder='true' title="お知らせ一覧" :border="true" :safeAreaInsetTop="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" mode="" @click="toIndex"></image>
            </view>
            <view class="u-nav-slot" slot="right">
                <image src="../../static/icons/settingnotice.svg" style="width: 60upx;height: 60upx;margin-right: 20upx;" @click="toSetting"></image>
                <image src="../../static/icons/addblue.svg" style="width: 60upx;height: 60upx;" @click="toEdit" mode=""></image>
            </view>
        </u-navbar>
        <view class="content">
            <view class="item" v-for="(item,index) in array">
                <view class="itemLeft" :class="current != index? 'border' : ''" @click="showModual(index)">
                    <image src="../../static/icons/select.png" v-if="current == index" mode=""></image>
                </view>
                <view class="itemRight">
                    <view class="iImage" v-if="item.images.length>0">
                        <u-swiper :list="item.images" height="580rpx" :autoplay="false" ></u-swiper>
                    </view>
                    <view class="iInfo">
                        <view class="info1">
                            <view class="i1Left">
                                <view v-text="item.update_time"></view>
                            </view>
                            <view class="i1Right" v-if="item.is_top">
                                <view style="position: relative;top: -2upx;">固定</view>
                            </view>
                        </view>
                        <view class="info2" v-text="item.title"></view>
                        <view class="info3" v-text="item.content"></view>
                    </view>
                </view>
            </view>
        </view>
        <u-action-sheet :round="20" :actions="list" :show="show" @select="choose" @close="close"></u-action-sheet>
        <permission :show="showpermission" @close='showpermission = false'></permission>
    </view>
</template>

<script>
    import {getNoticeList,deleteNotice,getShopInfo,getAdminDetail} from '@/api/index.js'
    import permission from "@/components/permission/permission.vue"
    export default {
        components: {
            permission
        },
        data() {
            return {
                showpermission:false,
                current: -1,
                show:false,
                list: [{
                        name: '編集する',
                        color: '#1D1D1F',
                        fontSize: '20'
                    },
                    {
                        name: 'この投稿を削除',
                        color: '#D93025',
                        fontSize: '20'
                    },
                    {
                        name: 'キャンセル',
                        color: '#1A73E8',
                        fontSize: '20'
                    }
                ],
                array: [ ],
                showMask:false,
                avatar:"",
                nickname:""
            }
        },
        created() {
           
        },
        onShow() {
            this.getList() 
            this.getInfo()
        },
        methods: {
            getInfo(){
              let that = this
                getShopInfo().then((res) => {
                    if(res.code == 200){
                        that.showMask = res.data.shop_info.notice_type == 2? true : false
                        that.avatar = res.data.shop_info.instagram_avatar
                        that.nickname = res.data.shop_info.instagram_username
                    }
                })
            },
            getList(){
              let that = this 
              getNoticeList().then((res) => {
                  if(res.code == 200){
                      that.array = res.data.notices
                  }
              })   
            },
            close(){
                this.show = false
                this.current = -1
            },
            showModual(e){
                this.current = e
                this.show = true
            },
            choose(e){
                let that = this
                let index = that.current
                if(e.name == '編集する'){
                     let params = JSON.stringify(that.array[index]) 
                     uni.navigateTo({
                         url:"edit/edit?source=edit&params="+params
                     })
                }else if(e.name == "この投稿を削除"){
                    deleteNotice(that.array[index].id).then((res) => {
                        if(res.code == 200){
                            uni.showToast({
                                title:res.message
                            })
                            that.getList()
                        }else{
                            uni.showToast({
                                title:res.message,
                                icon:"none"
                            })
                        }
                    })
                }else{
                    that.current = -1
                }
            },
            toSetting(){
                uni.navigateTo({
                    url:"../notice/setting/setting"
                })
            },
            toEdit(){
                let that = this
                let admin = uni.getStorageSync("admin")
                if(admin.admin_type == 2){
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    getAdminDetail(admin.id).then((res) => {
                        uni.hideLoading()
                        if(res.data.admin.privileges.includes(6)){
                            uni.navigateTo({
                                url:"../notice/edit/edit?source=add"
                            })
                        }else{
                            that.showpermission = true
                        }
                    })
                }else{
                    uni.navigateTo({
                        url:"../notice/edit/edit?source=add"
                    })
                }  
            },
            toIndex(){
                uni.switchTab({
                    url:"../index/index"
                })
            }
        }
    }
</script>

<style lang="scss">
    .avatar{
        width: 200rpx;
        height: 200rpx;
        border-radius: 0rpx 0rpx 0rpx 0rpx;
        border: 2rpx solid #707070;
        border-radius: 50%;
        overflow: hidden;
        margin: 0 auto;
        
        image{
            width: 100%;
            height: 100%;
        }
    }
    .userName{
        font-size: 40rpx;
        color: #1D1D1F;
        margin-top: 20rpx;
        margin-bottom: 32rpx;
    }
    .border {
        border-radius: 8upx;
        opacity: 1;
        border: 2upx solid #707070;
    }
    .mask{
        position: fixed;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(29, 29, 31, .2);
        z-index: 7 !important;
        
        .maskContent{
            width: 70vw;
            box-sizing: border-box;
            padding: 60upx 30upx;
            background: #fff;
            border-radius: 15upx;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
            text-align: center;
        }
    }
    .item {
        display: flex;
        margin-bottom: 40upx;

        .itemLeft {
            width: 40upx;
            height: 40upx;
            flex-shrink: 0;

            image {
                width: 100%;
                height: 100%;
            }
        }

        .itemRight {
            margin-left: 50upx;
            width: 600upx;
            box-shadow: 0px 0px 20upx 2upx rgba(0, 0, 0, 0.16);
            border-radius: 20upx;
            overflow: hidden;

            .iImage {
                width: 100%;
                height: 580upx;

                image {
                    width: 100%;
                    height: 100%;
                }
            }
        }
    }

    .content {
        box-sizing: border-box;
        padding: 40upx;
        .iInfo {
            box-sizing: border-box;
            padding: 20upx 20upx 40upx 20upx;
            .info2 {
                font-size: 28upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }

            .info3 {
               font-size: 28upx;
               font-family: Hiragino Sans-W3, Hiragino Sans;
               font-weight: normal;
               color: #707070;
               margin-top: 20upx;

                text {
                    color: #1A73E8;
                }
            }

            .info4 {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                margin-top: 20upx;
            }

            .info1 {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 20upx;

                .i1Left {
                   font-size: 28upx;
                   font-family: Hiragino Sans-W3, Hiragino Sans;
                   font-weight: normal;
                   color: #707070;
                }

                .i1Right {
                    font-size: 24upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1A73E8;
                    background: #FFFFFF;
                    border: 2upx solid #1A73E8;
                    padding: 8upx 12upx;
                    border-radius: 10upx;
                }
            }

            .info5 {
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                margin-top: 20upx;

                .infoItem {
                    flex: 1;
                }

            }
        }
    }

    .title {
        height: 126upx;
        background: #fff;
        box-shadow: 0px 0px 10upx 2upx rgba(0, 0, 0, 0.16);
        box-sizing: border-box;
        padding: 20upx 30upx;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;

        .titleRight {

            view:first-child {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;
            }

            view:last-child {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                text-align: right;
                margin-top: 10upx;
            }
        }

        .titleLeft {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: bold;
            color: #1D1D1F;
        }
    }
</style>
