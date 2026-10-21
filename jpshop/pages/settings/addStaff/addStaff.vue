<template>
    <!-- [カスタムエイド 2026-10-20] スタッフ一覧を1人ずつのカードに(店舗情報などと同じ見た目)
         ・写真は角を丸く。名前・役職・コメント、店舗情報ページに出すかどうかの印
         ・編集・↑(表示順:スタッフ紹介と予約の担当者の並び)・削除をボタンに(横スワイプはやめた)
         ・「スタッフ追加」の大きいボタン(右上の+も残す) -->
    <view class="sf">
        <u-navbar :border="true" :placeholder='true' title="スタッフ" :autoBack="false" @leftClick = "goBack" :safeAreaInsetTop="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="right">
                <image src="../../../static/icons/addB.png" style="width: 60upx;height: 60upx;" @click="toAddnew"
                    mode=""></image>
            </view>
        </u-navbar>
        <view class="sf-body">
            <view class="sf-add" hover-class="sf-btn-hover" @click="toAddnew">
                <text class="sf-plus">+</text>スタッフ追加
            </view>
            <text class="sf-sub">↑ で1つ上に移動します。この順番が、スタッフ紹介と予約の担当者の表示順になります。</text>
            <view class="sf-empty" v-if="loaded && !array.length">スタッフはまだ登録されていません</view>
            <view class="sf-card" v-for="(item,index) in array" :key="item.id">
                <view class="sf-top" @click="editItem(index)">
                    <image class="sf-photo" :src="(item.image && item.image[0]) || ''" mode="aspectFill"></image>
                    <view class="sf-text">
                        <text class="sf-name">{{item.name}}</text>
                        <text class="sf-post" v-if="item.post">{{item.post}}</text>
                        <text class="sf-intro" v-if="item.self_introduction">{{item.self_introduction}}</text>
                        <text class="sf-tag" :class="{ off: item.is_show_shop == 0 }" v-if="item.is_show_shop !== undefined">{{item.is_show_shop == 0 ? '店舗情報に出さない' : '店舗情報に掲載'}}</text>
                    </view>
                </view>
                <view class="sf-actions">
                    <view class="sf-act" hover-class="sf-hover" @click="editItem(index)">
                        <u-icon name="edit-pen" size="16" color="#1D1D1F"></u-icon><text>編集</text>
                    </view>
                    <view class="sf-icon-btn" :class="{ off: index == 0 }" hover-class="sf-hover" @click="index != 0 && sort(item, index)">
                        <u-icon name="arrow-upward" size="18" :color="index == 0 ? '#C7C7CC' : '#1D1D1F'"></u-icon>
                    </view>
                    <view class="sf-icon-btn" hover-class="sf-hover" @click="delItem(index)">
                        <u-icon name="trash" size="18" color="#C5221F"></u-icon>
                    </view>
                </view>
            </view>
            <mod :config="config" @getStatus="confirm" v-if="show"></mod>
        </view>
    </view>
</template>

<script>
    import {
        getStaffList,
        deleteAdmin,
        staffSort 
    } from '@/api/index.js'
    import mod from "@/components/mod.vue"
    export default {
        components:{
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
                current:0,
                config:{
                    type:"button",
                    cancel:true,
                    default:true,
                    title:"この管理者を削除",
                    content:"氏名氏名を削除しますか？",
                    cancelText:"削除",
                    defaultText:"キャンセル"
                },
                show:false,
                loaded: false // [カスタムエイド 2026-10-20]
            };
        },
        created() {
            this.getList()
        },
        methods: {
            sort(item, index) {
                let that = this
                uni.showLoading({
                    title:"読み込み中",
                    mask:true
                })
                let ar = []
                that.array.forEach((v, i) => {
                    ar.push(v.id)
                })
                that.swapArrayElements(ar, index * 1 - 1, index)
                let data = {}
                data.ids = ar
                staffSort(data).then((res) => {
                    if (res.code == 200) {
                        uni.hideLoading()
                        that.getList()
                    } else {
                        uni.hideLoading()
                    }
                }).catch(() => { uni.hideLoading() })
            },
            swapArrayElements(arr, indexA, indexB) {
                const temp = arr[indexA];
                arr[indexA] = arr[indexB];
                arr[indexB] = temp;
            },
            toAddnew() {
                uni.navigateTo({
                    url: "addNew/addNew"
                })
            },
            goBack(){ 
              uni.switchTab({
                  url:"../../setting/setting"
              })
            },
            getList() {
                let that = this
                let data = {}
                data.is_appoint = ""
                data.start_time = ""
                data.end_time = ""
                data.id = ""
                getStaffList(data).then((res) => {
                    if (res.code == 200) {
                        that.loaded = true
                        that.array = res.data.admins
                    }else if(res.code == 403){
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                        uni.hideLoading()
                        setTimeout(() => {
                            uni.navigateBack()
                        },1500)
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                }).catch(() => {})
            },
            delItem(index){
                this.current  = index
                this.show = true
                this.config.content = this.array[index].name + "を削除しますか？"
            },
            confirm(e){
                let that = this
                if(e == "default"){
                    this.show = false
                }else{
                    let index = that.current * 1
                    let id = that.array[index].id
                    deleteAdmin(id).then((res) => {
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
                    }).catch(() => {})
                }
                that.show = false
            },
            editItem(index){
                let that = this
                let id = that.array[index].id
                uni.navigateTo({
                    url:"addNew/addNew?id="+id+"&type=edit"
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-20] 店舗情報などと同じ決まり */
    .sf { min-height: 100vh; background: #F5F5F7; font-family: 'Hiragino Sans', sans-serif; color: #1D1D1F; }
    .sf-body { padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom)); }
    .sf-add { height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; margin-bottom: 20rpx; }
    .sf-plus { font-size: 44rpx; line-height: 1; margin-right: 12rpx; position: relative; top: -2rpx; }
    .sf-btn-hover { opacity: 0.8; }
    .sf-hover { opacity: 0.6; }
    .sf-sub { display: block; padding: 0 8rpx; font-size: 24rpx; line-height: 1.6; color: #6E6E73; margin-bottom: 28rpx; }
    .sf-empty { padding: 80rpx 32rpx; text-align: center; font-size: 28rpx; color: #6E6E73; background: #FFFFFF; border-radius: 32rpx; }
    .sf-card { background: #FFFFFF; border-radius: 32rpx; padding: 28rpx; margin-bottom: 24rpx; box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05); }
    .sf-top { display: flex; align-items: flex-start; }
    .sf-photo { width: 176rpx; height: 220rpx; flex-shrink: 0; border-radius: 20rpx; background: #E5E5EA; margin-right: 24rpx; }
    .sf-text { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .sf-name { font-size: 32rpx; font-weight: 700; line-height: 1.4; margin-bottom: 8rpx; }
    .sf-post { font-size: 26rpx; color: #1D1D1F; margin-bottom: 8rpx; }
    .sf-intro { font-size: 24rpx; line-height: 1.6; color: #6E6E73; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; overflow: hidden; margin-bottom: 8rpx; }
    .sf-tag { align-self: flex-start; font-size: 22rpx; font-weight: 700; color: #137333; background: #E6F4EA; border-radius: 18rpx; padding: 4rpx 14rpx; }
    .sf-tag.off { color: #515154; background: #F2F2F5; }
    .sf-actions { display: flex; border-top: 1px solid #E5E5EA; margin-top: 24rpx; padding-top: 20rpx; }
    .sf-act { flex: 1; height: 76rpx; border: 1px solid #D2D2D7; border-radius: 20rpx; display: flex; align-items: center; justify-content: center; font-size: 28rpx; font-weight: 500; box-sizing: border-box; text { margin-left: 8rpx; } }
    .sf-icon-btn { width: 88rpx; height: 76rpx; margin-left: 16rpx; border: 1px solid #D2D2D7; border-radius: 20rpx; box-sizing: border-box; display: flex; align-items: center; justify-content: center; }
    .sf-icon-btn.off { background: #F5F5F7; border-color: #E5E5EA; }
</style>
