<template>
    <!-- [カスタムエイド 2026-10-20] メニュー一覧の見た目を、お客様アプリ(Card-San App)のメニュー画面に合わせた
         ・上にカテゴリーのボタン(押すとそのカテゴリーだけ)。カテゴリーごとの帯、メニューは1つずつ白い角丸の枠
         ・価格と所要時間を1行に、写真は押すと全画面、2枚以上は枚数を表示。予約できるメニューには「予約可」の印
         ・編集・↑(表示順)・削除をボタンに(横スワイプはやめた)。「メニュー追加」は上の大きいボタンと右上の+ -->
    <view class="mn">
        <u-navbar title="メニュー" :placeholder="true" :border="true" :autoBack="false" @leftClick="goBack"
            :safeAreaInsetTop="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="right">
                <image src="../../static/svg/addblue.svg" style="width: 60upx;height: 60upx;" @click="add" mode="">
                </image>
            </view>
        </u-navbar>
        <view class="mn-chips-wrap">
            <scroll-view class="mn-chips" scroll-x>
                <view class="mn-chip" :class="{ on: current == -1 }" @click="current = -1">すべて</view>
                <view class="mn-chip" :class="{ on: current == item.id }" @click="selectTab(item)" v-for="(item,index) in array" :key="item.id">{{item.cate}}</view>
            </scroll-view>
        </view>

        <view class="mn-body">
            <view class="mn-add" hover-class="mn-btn-hover" @click="add">
                <text class="mn-plus">+</text>メニュー追加
            </view>

            <view class="mn-empty" v-if="loaded && !array.length">カテゴリーとメニューはまだありません</view>

            <view v-for="(item,index) in array" :key="item.id" v-if="item.id == current || current == -1">
                <view class="mn-cate">{{item.cate}}<text class="mn-cate-count">{{item.items.length}}件</text></view>
                <view class="mn-none" v-if="!item.items.length">このカテゴリーのメニューはまだありません</view>
                <view class="mn-card" v-for="(ite,idx) in item.items" :key="ite.id">
                    <view class="mn-top" @click="toEdit(ite)">
                        <view class="mn-title-row">
                            <text class="mn-title">{{ite.title}}</text>
                            <text class="mn-badge" v-if="ite.is_order == 1">予約可</text>
                        </view>
                        <view class="mn-price">
                            <text class="mn-price-main">{{ite.price}}</text>
                            <text v-if="ite.is_order == 1">　・　所要時間 {{ite.order_time}}分</text>
                        </view>
                    </view>
                    <view class="mn-detail" v-if="(ite.image && ite.image.length) || ite.des">
                        <view class="mn-photo" v-if="ite.image && ite.image.length" @click="openPhotos(ite.image)">
                            <image :src="ite.image[0]" mode="aspectFill"></image>
                            <view class="mn-count" v-if="ite.image.length > 1">
                                <image src="../../static/svg/multiPhoto.svg" class="mn-count-icon"></image>
                                <text>{{ite.image.length}}</text>
                            </view>
                        </view>
                        <text class="mn-des" @click="toEdit(ite)">{{ite.des}}</text>
                    </view>
                    <view class="mn-actions">
                        <view class="mn-act" hover-class="mn-hover" @click="toEdit(ite)">
                            <u-icon name="edit-pen" size="16" color="#1D1D1F"></u-icon><text>編集</text>
                        </view>
                        <view class="mn-icon-btn" :class="{ off: idx == 0 }" hover-class="mn-hover" @click="idx != 0 && sort(item,ite,idx)">
                            <u-icon name="arrow-upward" size="18" :color="idx == 0 ? '#C7C7CC' : '#1D1D1F'"></u-icon>
                        </view>
                        <view class="mn-icon-btn" hover-class="mn-hover" @click="deleteThis(ite)">
                            <u-icon name="trash" size="18" color="#C5221F"></u-icon>
                        </view>
                    </view>
                </view>
            </view>
            <text class="mn-sub" v-if="array.length">↑ で、同じカテゴリーの中で1つ上に移動します。</text>
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
                loaded: false, // [カスタムエイド 2026-10-20]
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
            // [カスタムエイド 2026-10-20] 写真を押すと全画面で表示
            openPhotos(urls) {
                uni.previewImage({ urls: urls, current: 0 })
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
                                that.loaded = true
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
    /* [カスタムエイド 2026-10-20] お客様アプリのメニュー画面・店舗情報などと同じ決まり */
    .mn {
        min-height: 100vh;
        background: #F5F5F7;
        font-family: 'Hiragino Sans', sans-serif;
        color: #1D1D1F;
    }

    .mn-chips-wrap {
        background: #FFFFFF;
        border-bottom: 1px solid #E5E5EA;
        padding: 20rpx 0;
    }

    .mn-chips {
        white-space: nowrap;
        padding: 0 24rpx;
        box-sizing: border-box;
    }

    .mn-chip {
        display: inline-block;
        height: 64rpx;
        line-height: 62rpx;
        padding: 0 24rpx;
        margin-right: 16rpx;
        border-radius: 32rpx;
        border: 1px solid #D2D2D7;
        background: #FFFFFF;
        font-size: 26rpx;
        color: #515154;
        box-sizing: border-box;
    }

    .mn-chip.on {
        background: #1D1D1F;
        border-color: #1D1D1F;
        color: #FFFFFF;
        font-weight: 700;
    }

    .mn-body {
        padding: 28rpx 0 calc(80rpx + env(safe-area-inset-bottom));
    }

    .mn-add {
        height: 104rpx;
        margin: 0 24rpx 28rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        font-size: 32rpx;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mn-plus {
        font-size: 44rpx;
        line-height: 1;
        margin-right: 12rpx;
        position: relative;
        top: -2rpx;
    }

    .mn-btn-hover {
        opacity: 0.8;
    }

    .mn-hover {
        opacity: 0.6;
    }

    .mn-empty,
    .mn-none {
        margin: 0 24rpx 24rpx;
        padding: 48rpx 32rpx;
        text-align: center;
        font-size: 26rpx;
        color: #6E6E73;
        background: #FFFFFF;
        border-radius: 24rpx;
    }

    .mn-cate {
        height: 72rpx;
        line-height: 72rpx;
        padding: 0 32rpx;
        background: #ECECF0;
        color: #515154;
        font-size: 28rpx;
        font-weight: 600;
        border-top: 1px solid #E3E3E8;
        border-bottom: 1px solid #E3E3E8;
        margin-bottom: 24rpx;
    }

    .mn-cate-count {
        font-size: 24rpx;
        font-weight: 400;
        color: #8A8A8E;
        margin-left: 12rpx;
    }

    .mn-card {
        margin: 0 24rpx 24rpx;
        background: #FFFFFF;
        border: 1px solid #E8E8ED;
        border-radius: 24rpx;
        box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.06);
        padding: 28rpx;
    }

    .mn-title-row {
        display: flex;
        align-items: flex-start;
        margin-bottom: 8rpx;
    }

    .mn-title {
        flex: 1;
        min-width: 0;
        font-size: 32rpx;
        font-weight: 700;
        line-height: 1.4;
    }

    .mn-badge {
        flex-shrink: 0;
        margin-left: 12rpx;
        font-size: 22rpx;
        font-weight: 700;
        color: #137333;
        background: #E6F4EA;
        border-radius: 18rpx;
        padding: 4rpx 14rpx;
    }

    .mn-price {
        font-size: 26rpx;
        line-height: 1.4;
        color: #6E6E73;
    }

    .mn-price-main {
        font-size: 28rpx;
        font-weight: 600;
        color: #1D1D1F;
    }

    .mn-detail {
        display: flex;
        margin-top: 20rpx;
    }

    .mn-photo {
        position: relative;
        flex-shrink: 0;
        width: 208rpx;
        height: 208rpx;
        margin-right: 24rpx;

        image {
            width: 208rpx;
            height: 208rpx;
            border-radius: 16rpx;
            background: #F5F5F7;
        }
    }

    .mn-count {
        position: absolute;
        right: 12rpx;
        bottom: 12rpx;
        height: 44rpx;
        padding: 0 14rpx;
        border-radius: 22rpx;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        color: #FFFFFF;
        font-size: 24rpx;

        .mn-count-icon {
            width: 24rpx;
            height: 24rpx;
            margin-right: 8rpx;
            border-radius: 0;
            background: transparent;
        }
    }

    .mn-des {
        flex: 1;
        min-width: 0;
        font-size: 26rpx;
        line-height: 1.6;
        color: #3A3A3C;
        white-space: pre-line;
    }

    .mn-actions {
        display: flex;
        border-top: 1px solid #E5E5EA;
        margin-top: 24rpx;
        padding-top: 20rpx;
    }

    .mn-act {
        flex: 1;
        height: 76rpx;
        border: 1px solid #D2D2D7;
        border-radius: 20rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28rpx;
        font-weight: 500;
        box-sizing: border-box;

        text {
            margin-left: 8rpx;
        }
    }

    .mn-icon-btn {
        width: 88rpx;
        height: 76rpx;
        margin-left: 16rpx;
        border: 1px solid #D2D2D7;
        border-radius: 20rpx;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mn-icon-btn.off {
        background: #F5F5F7;
        border-color: #E5E5EA;
    }

    .mn-sub {
        display: block;
        padding: 0 32rpx;
        font-size: 24rpx;
        color: #6E6E73;
    }
</style>
