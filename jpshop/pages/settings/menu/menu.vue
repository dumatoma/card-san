<template>
    <!-- [カスタムエイド 2026-10-20] メニューのカテゴリーの見た目を店舗情報などと合わせた
         ・カテゴリーは白いまとまりに。名前を押すと名前の変更、↑で1つ上へ(表示順)、ゴミ箱で削除(横スワイプはやめた)
         ・追加/変更のパネル:項目名を黒く、入力欄を共通の枠(#D2D2D7・角丸・入力中は青)に、保存ボタンも共通の形に -->
    <view class="mc">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="カテゴリー" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="mc-body">
            <view class="mc-add" hover-class="mc-btn-hover" @click="addMenu">
                <text class="mc-plus">+</text>カテゴリー追加
            </view>
            <text class="mc-sub">名前を押すと変更できます。↑ で1つ上に移動し、メニュー画面での表示順が変わります。</text>

            <view class="mc-empty" v-if="loaded && !array.length">カテゴリーはまだありません</view>
            <view class="mc-card" v-if="array.length">
                <view class="mc-row" v-for="(item, index) in array" :key="item.id">
                    <view class="mc-name" hover-class="mc-hover" @click="edit(item.id, item.name)">
                        <text class="mc-no">{{index + 1}}</text>
                        <text class="mc-text">{{item.name}}</text>
                        <u-icon name="edit-pen" size="16" color="#8A8A8E"></u-icon>
                    </view>
                    <view class="mc-btn" :class="{ off: index == 0 }" hover-class="mc-hover" @click="index != 0 && sort(item, index)">
                        <u-icon name="arrow-upward" size="18" :color="index == 0 ? '#C7C7CC' : '#1D1D1F'"></u-icon>
                    </view>
                    <view class="mc-btn" hover-class="mc-hover" @click="dels(item.id)">
                        <u-icon name="trash" size="18" color="#C5221F"></u-icon>
                    </view>
                </view>
            </view>
        </view>

        <!-- 追加・名前の変更 -->
        <u-popup :show="show" round="24" @close="show = false">
            <view class="mc-pop">
                <view class="mc-pop-head">
                    <view class="mc-pop-close" @click="show = false">
                        <image src="../../../static/icons/tclose.png" mode=""></image>
                    </view>
                    <text class="mc-pop-title">{{source == 'edit' ? 'カテゴリー名の変更' : 'カテゴリー追加'}}</text>
                    <view class="mc-pop-close"></view>
                </view>
                <view class="mc-pop-body">
                    <view class="mc-label"><text class="mc-req">※</text>カテゴリー名</view>
                    <input class="mc-in" :class="{ on: fk }" v-model="name" maxlength="-1" :cursor-spacing="120"
                        placeholder-class="mc-ph" placeholder="例:カット、カラー、パーマ" @focus="fk = true" @blur="fk = false" />
                    <view class="mc-save" :class="{ off: !name }" hover-class="mc-btn-hover" @click="save">保存する</view>
                </view>
            </view>
        </u-popup>

        <!-- 削除の確認 -->
        <u-popup :show="showdel" @close="showdel = false" mode="center" round="16">
            <view class="mc-del">
                <text class="mc-del-title">このカテゴリーを削除しますか?</text>
                <text class="mc-del-text">このカテゴリーを削除すると、カテゴリー内に登録されたメニューも全て削除されます。</text>
                <text class="mc-del-text">残したいメニューがある場合は、別のカテゴリーに設定・登録してから削除してください。</text>
                <view class="mc-del-btn" hover-class="mc-btn-hover" @click="del">削除</view>
                <view class="mc-cancel" hover-class="mc-hover" @click="showdel = false">キャンセル</view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import {
        addMenuCates,
        menuCateList,
        deleteMenuCates,
        editMenuCates,
         menucatesort
    } from '@/api/index.js'
    export default {
        data() {
            return {
                array: [],
                options1: [{
                    text: '削除',
                    style: {
                        backgroundColor: '#D93025'
                    }
                }],
                source: "add",
                show: false,
                name: "",
                cid:"",
                showdel:false,
                id:"",
                loaded: false, // [カスタムエイド 2026-10-20]
                fk: false
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
                menucatesort(data).then((res) => {
                    if (res.code == 200) {
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
            dels(id){
               this.showdel = true
               this.id = id 
            },
            getList() {
                menuCateList().then((res) => {
                    uni.hideLoading()
                    let that = this
                    that.loaded = true
                    if (res.code == 200) {
                        that.array = res.data.cates
                    }
                }).catch(() => { uni.hideLoading() })
            },
            addMenu() {
                this.source = "add"
                this.name = ""
                this.show = true
            },
            del() {
                let that = this
                let id = that.id
                deleteMenuCates(id).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                        that.getList()
                        that.showdel = false
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                }).catch(() => {})
            },
            edit(id,name){
              let that = this
              that.source = "edit"
              that.show = true
              that.name = name
              that.cid = id
            },
            save() {
                let that = this
                if (!String(that.name || '').trim()) {
                    uni.showToast({ title: 'カテゴリー名を入力してください', icon: 'none' })
                    return
                }
                let data = {}
                data.name = that.name
                if(that.source == "add"){
                    addMenuCates(data).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            that.show = false
                            that.name = ""
                            that.getList()
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    }).catch(() => {})
                }else if(that.source == "edit"){
                    let data = {}
                    data._method = "put"
                    data.name = that.name
                    editMenuCates(data,that.cid).then((res) => {
                        if(res.code == 200){
                            that.show = false
                            that.name = ""
                            that.cid = ""
                            uni.showToast({
                                title:res.message
                            })
                            that.getList()
                        }
                    }).catch(() => {})
                }
                
            }
        }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-20] 店舗情報などと同じ決まり */
    .mc {
        min-height: 100vh;
        background: #F5F5F7;
        font-family: 'Hiragino Sans', sans-serif;
        color: #1D1D1F;
    }

    .mc-body {
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
    }

    .mc-add {
        height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        font-size: 32rpx;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20rpx;
    }

    .mc-plus {
        font-size: 44rpx;
        line-height: 1;
        margin-right: 12rpx;
        position: relative;
        top: -2rpx;
    }

    .mc-btn-hover {
        opacity: 0.8;
    }

    .mc-hover {
        opacity: 0.6;
    }

    .mc-sub {
        display: block;
        padding: 0 8rpx;
        font-size: 24rpx;
        line-height: 1.6;
        color: #6E6E73;
        margin-bottom: 28rpx;
    }

    .mc-empty {
        padding: 80rpx 32rpx;
        text-align: center;
        font-size: 28rpx;
        color: #6E6E73;
        background: #FFFFFF;
        border-radius: 32rpx;
    }

    .mc-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 0 16rpx 0 32rpx;
    }

    .mc-row {
        display: flex;
        align-items: center;
        min-height: 112rpx;
        border-bottom: 1px solid #E5E5EA;
    }

    .mc-row:last-child {
        border-bottom: 0;
    }

    .mc-name {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        min-height: 112rpx;
    }

    .mc-no {
        width: 44rpx;
        font-size: 24rpx;
        color: #8A8A8E;
    }

    .mc-text {
        flex: 1;
        min-width: 0;
        font-size: 30rpx;
        font-weight: 500;
        margin-right: 12rpx;
        word-break: break-all;
    }

    .mc-btn {
        width: 76rpx;
        height: 76rpx;
        margin-left: 12rpx;
        border: 1px solid #D2D2D7;
        border-radius: 20rpx;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mc-btn.off {
        background: #F5F5F7;
        border-color: #E5E5EA;
    }

    .mc-pop {
        background: #FFFFFF;
        border-radius: 48rpx 48rpx 0 0;
        padding-bottom: calc(40rpx + env(safe-area-inset-bottom));
    }

    .mc-pop-head {
        height: 112rpx;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24rpx;
        border-bottom: 1px solid #E5E5EA;
    }

    .mc-pop-close {
        width: 64rpx;
        height: 64rpx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .mc-pop-title {
        font-size: 34rpx;
        font-weight: 700;
    }

    .mc-pop-body {
        padding: 40rpx 32rpx 0;
    }

    /* [カスタムエイド 2026-10-21] 見出しの文字と入力枠の見た目のすき間を約6pxに(行の高さを詰め、余白 4px) */
    .mc-label {
        font-size: 26rpx;
        font-weight: 500;
        color: #1D1D1F;
        margin-bottom: 8rpx;
        line-height: 1.2;
    }

    .mc-req {
        color: #C5221F;
    }

    .mc-in {
        width: 100%;
        box-sizing: border-box;
        height: 88rpx;
        padding: 0 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        background: #FFFFFF;
        font-size: 30rpx;
        color: #1D1D1F;
        margin-bottom: 48rpx;
        transition: border-color .15s, box-shadow .15s;
    }

    .mc-in.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    .mc-ph {
        color: #8A8A8E;
    }

    .mc-save {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
    }

    .mc-save.off {
        background: #C6DAFC;
    }

    .mc-del {
        width: 600rpx;
        padding: 48rpx 40rpx 24rpx;
        box-sizing: border-box;
    }

    .mc-del-title {
        display: block;
        font-size: 32rpx;
        font-weight: 700;
        text-align: center;
        margin-bottom: 24rpx;
    }

    .mc-del-text {
        display: block;
        font-size: 26rpx;
        line-height: 1.6;
        color: #515154;
        margin-bottom: 12rpx;
    }

    .mc-del-btn {
        height: 92rpx;
        line-height: 92rpx;
        margin-top: 28rpx;
        border-radius: 24rpx;
        background: #D93025;
        color: #FFFFFF;
        text-align: center;
        font-size: 30rpx;
        font-weight: 700;
    }

    .mc-cancel {
        height: 88rpx;
        line-height: 88rpx;
        text-align: center;
        font-size: 28rpx;
        color: #515154;
    }
</style>
