<template>
    <view style="display: flex;flex-direction: column;height: 100vh;">
        <u-navbar title="メニュー" :placeholder="true" :border="true" :autoBack="false" @leftClick="goBack"
            :safeAreaInsetTop="true">
            <view class="u-nav-slot" slot="right">
                <image src="../../static/svg/addblue.svg" style="width: 60upx;height: 60upx;" @click="add" mode="">
                </image>
            </view>
        </u-navbar>
        <view class="tabs border_top">
            <view class="tab_item" :class="current == item.id?'tab_item_active':'' " @click="selectTab(item)" v-for="(item,index) in array" v-text="item.cate"></view>
        </view>
        <view class="content" style="flex: 1;overflow-y: scroll;">
            <view class="item" v-for="(item,index) in array" v-if="item.id == current || current == -1">
                <view class="itemTitle border_bottom" v-text="item.cate"></view>
                <view class="itemContent">
                    <view class="ite border_bottom" v-for="(ite,idx) in item.items">
                        <u-swipe-action-item :options="options1" @click="deleteThis(ite)">
                            <view class="swipe-action">
                                <view class="swipe-action__content">
                                    <view class="iteTitle" style="display: flex;justify-content: space-between;">
                                        <view v-text="ite.title" @click="toEdit(ite)" hover-class="click-hover" ></view>
                                        <image v-if="idx != 0" class="sor" src="../../static/svg/upup.svg" mode="" @click.stop="sort(item,ite,idx)"></image>
                                    </view>
                                    <view class="iteInfo" style="padding: 28rpx 0 20rpx;">
                                        <view class="price" v-text="`${ite.price}`"></view>
                                        <view class="time" v-if="ite.is_order == 1" v-text="`所要時間: ${ite.order_time}分`"></view>
                                    </view>
                                    <view class="iteContent">
                                        <view class="iteImage" v-if="ite.image.length > 0">
                                            <image :src="ite.image[0]" mode="aspectFill"></image>
                                        </view>
                                        <view class="iteInfo" style="white-space: pre-wrap;font-size: 24upx;color: #1d1d1f;" v-text="ite.des"></view>
                                    </view>
                                </view>
                            </view>
                        </u-swipe-action-item>
                    </view>
                </view>
            </view>
            </u-swipe-action>
        </view>
         <mod :config="config" @getStatus="confirm" v-if="show"></mod>
         <permission :show="showpermission" @close='showpermission = false'></permission>
    </view>
</template>

<script>
    import {
        menuCateList,
        deleteMenu,
        menuList,
        getAdminDetail,
       menusort
    } from '@/api/index.js'
    import mod from "@/components/mod.vue"
    import permission from "@/components/permission/permission.vue"
    export default {
        components:{
            mod,
            permission
        },
        data() {
            return {
                current:-1,
                array: [],
                show:false,
                options1: [{
                    text: '削除',
                    style: {
                        backgroundColor: '#D93025'
                    }
                }],
                config:{},
                showpermission:false
            };
        },
        onShow() {
            let that = this
            that.getCate()
        },
        methods: {
            sort(item,ite,index){
                let that = this
                 console.log(item)
               let ar = []
               item.items.forEach((v, i) => {
                   ar.push(v.id)
               })
               that.swapArrayElements(ar, index * 1 - 1, index)
               let data = {}
               data.ids = ar
               data.cid = ite.cid
               menusort(data).then((res) => {
                   if(res.code == 200){
                       that.getCate()
                   }else{
                       uni.showToast({
                           title:res.message,
                           icon:"none"
                       })
                   }
               })
            },
            swapArrayElements(arr, indexA, indexB) {
                const temp = arr[indexA];
                arr[indexA] = arr[indexB];
                arr[indexB] = temp;
            },
            toEdit(ite) {
                let that = this
                let admin = uni.getStorageSync("admin")
                if(admin.admin_type == 2){
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    getAdminDetail(admin.id).then((res) => {
                        uni.hideLoading()
                        if(res.data.admin.privileges.includes(13)){
                            uni.navigateTo({
                                url: "/pages/menu/add/edit?id=" + ite.id
                            })
                        }else{
                            that.showpermission = true
                        }
                    })
                }else{
                   uni.navigateTo({
                       url: "/pages/menu/add/edit?id=" + ite.id
                   })
                }     
            },
            selectTab(e){
              let that = this
              if(that.current == e.id){
                  that.current = -1
              }else{
                  that.current = e.id
              }
            },
            confirm(e){  
              let that = this
              if(e == "cancel"){
                  deleteMenu(that.id).then((res) => {
                       that.show = false
                      if(res.code == 200){
                          uni.showToast({
                              title:res.message,
                              duration:2000
                          })
                          setTimeout(()=> {
                              that.getCate()
                          },2000)
                      }else{
                          uni.showToast({
                              title:res.message,
                              duration:2000,
                              icon:"none"
                          })
                      }
                  })
              }else{
                  that.show = false
              }
              
            },
            deleteThis(e){
                let that = this
                that.config = {
                   type:"button",
                   cancel:true,
                   default:true,
                   title:"このメニューを削除",
                   content:e.title+"を削除しますか?",
                   cancelText:"削除",
                   defaultText:"キャンセル" 
                }
                that.id = e.id
                that.show = true
               
            },
            add() {
                
                let that = this
                let admin = uni.getStorageSync("admin")
                if(admin.admin_type == 2){
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    getAdminDetail(admin.id).then((res) => {
                        uni.hideLoading()
                        if(res.data.admin.privileges.includes(13)){
                            uni.navigateTo({
                                url: "../menu/add/add"
                            })
                        }else{
                            that.showpermission = true
                        }
                    })
                }else{
                   uni.navigateTo({
                       url: "../menu/add/add"
                   })
                }
            },

            goBack() {
                uni.switchTab({
                    url: "../setting/setting"
                })
            },

            getCate() {
                let that = this
                uni.showLoading({
                    title:"読み込み中"
                })
                menuCateList().then((res) => {
                    if (res.code == 200) {
                        menuList().then((result) => {
                            uni.hideLoading()
                            if (res.code == 200) {
                                let array = []
                                for (let i = 0; i < res.data.cates.length; i++) {
                                    let obj = {}
                                    obj.id = res.data.cates[i].id
                                    obj.cate = res.data.cates[i].name
                                    let temp = []
                                    for (let j = 0; j < result.data.menus.length; j++) {
                                        if (result.data.menus[j].cid == res.data.cates[i].id) {
                                            temp.push(result.data.menus[j])
                                        }
                                    }
                                    obj.items = temp
                                    array.push(obj)
                                }
                                that.array = array
                            } else {
                                uni.showToast({
                                    title: res.message,
                                    icon: "none"
                                })
                            }
                        })
                    }else{
                        uni.hideLoading()
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .tab_item_active{
        background: rgba(29,29,31,0.15) !important;
        color: rgba(29,29,31,1) !important;
        border: 2upx solid rgba(29,29,31,1) !important;
    }
    .swipe-action__content{
        padding: 40rpx 0 20rpx !important;
    }
    .u-page {
        padding: 0;
    }

    .u-demo-block__title {
        padding: 10px 0 2px 15px;
    }

    .swipe-action {
        &__content {
            padding: 25rpx 0;

            &__text {
                font-size: 15px;
                color: $u-main-color;
                padding-left: 30rpx;
            }
        }
    }

    .click-hover {
        color: #1D1D1F !important;
        opacity: 0.5 !important;
    }
    .sor{
        width: 48rpx;
        height: 48rpx;
        flex-shrink: 0;
    }
    .itemTitle {
        box-sizing: border-box;
        padding: 36upx 32upx;
        background: #F5F5F7;
        border-bottom: 2upx solid #d2d2d7 !important; 
    }

    .iteInfo {
        display: flex;
        box-sizing: border-box;
        padding-right: 32upx;

        .price {
            color: #D93025;
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            margin-right: 60upx;
        }

        .time {
            color: #1D1D1F;
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
        }
    }


    .iteContent {
        display: flex;
        align-items: flex-start;

        .iteImage {
            width: 200upx;
            height: 200upx;
            margin-right: 32upx;
            flex-shrink: 0;

            image {
                width: 100%;
                height: 100%;
                border-radius: 12upx;
            }
        }
    }

    .iteTitle {
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        box-sizing: border-box;
        padding-right: 16px;
    }

    .ite {
        box-sizing: border-box;
        padding: 0  0 0 32upx;
        // border-bottom: none !important;
    }

    .tabs {
        display: flex;
        align-items: center;
        overflow-x: scroll;
        box-sizing: border-box;
        padding: 40upx 32upx;
        box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);
        background: #FFFFFF;
        border-bottom: 2upx solid #d2d2d7;
        .tab_item {
            padding:10upx 20upx;
            background: #FFFFFF;
            border-radius: 10upx;
            border: 2upx solid #86868B;
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #86868B;
            flex-shrink: 0;
            margin-right: 10px;
        }
    }
</style>