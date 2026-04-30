<template>
    <view>
        <view class="addContent">
            <view class="addButton" @click="addMenu">
                <u-icon name="plus-circle-fill" color="#fff" size="24"></u-icon>
                <view class="name">カテゴリー追加</view>
            </view>
        </view>
        <view>
            <u-swipe-action>
                <u-swipe-action-item :options="options1" v-for="(item,index) in array" @click="dels(item.id)">
                    <view class="swipe-action u-border-top u-border-bottom">
                        <view class="swipe-action__content">
                            <view class="arItem" @click.stop="edit(item.id,item.name)" >
                                <view v-text="item.name"></view>
                                <image v-if="index != 0" class="sor" src="../../../static/svg/upup.svg" mode="" @click.stop="sort(item,index)"></image>
                            </view>
                        </view>
                    </view>
                </u-swipe-action-item>
            </u-swipe-action>
        </view>
        <u-popup :show="show" round="24">
            <view class="pContent">
                <view class="pTitle">
                    <view class="closeImage">
                        <image src="../../../static/icons/tclose.png" mode="" @click="show = false"></image>
                    </view>
                    <view class="pt">
                        カテゴリー追加
                    </view>
                </view>

                <view class="insertContent">
                    <view class="insertName">
                        カテゴリー名
                    </view>
                    <u-input v-model="name" placeholder="メニューカテゴリーを入力してください"></u-input>
                </view>

                <u-button type="primary" text="保存" class="butt" @click="save"></u-button>
            </view>
        </u-popup>
        
       <u-popup :show="showdel" @close="showdel = false" mode="center" round="10">
               <view class="delContent">
                   <view class="delTitle">
                       このカテゴリーを削除しますか？
                   </view>
                   <view class="delDetail">
                       このカテゴリーを削除すると、カテゴリー内に登録されたメニューも全て削除されます。
                   </view>
                   <view class="delDetail">
                       残したいメニューがある場合は、別のカテゴリーに設定・登録してから削除してください。
                   </view>
                   <view class="cbtn" @click="del">
                       削除
                   </view> 
                   <view class="ccbtn" @click="showdel = false">
                       キャンセル
                   </view>
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
                id:""
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
                       
                    }
                })
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
                    if (res.code == 200) {
                        that.array = res.data.cates
                    }
                })
            },
            addMenu() {
                this.source = "add"
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
                })
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
                    })
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
                    })
                }
                
            }
        }
    }
</script>

<style lang="scss">
    .sor{
        width: 48rpx;
        height: 48rpx;
    }
    .delContent{
        width: 80vw;
        border-radius: 10rpx;
        box-sizing: border-box;
        padding: 30rpx 20upx;
        
        .delTitle{
            text-align: center;
            font-size: 32rpx;
            font-weight: bolder;
        }
        
        .delDetail{
            text-align: center;
            margin-top: 36upx;
            font-size: 24rpx;
        }
        .sor{
            width: 48rpx;
            height: 48rpx;
        }
        .cbtn{
            width: 480rpx;
            height: 96upx;
            background: #D93025;
            text-align: center;
            line-height: 48rpx;
            color: #fff;
            font-family: Noto Sans Kannada, Noto Sans Kannada;
            font-weight: bold;
            font-size: 32rpx;
            line-height: 96upx;
            border-radius: 12upx;
            margin: 52rpx auto 30rpx;
        }
        
        .ccbtn{
            width: 480rpx;
            height: 96upx;
            background: rgba(29,29,31,0.2);
            text-align: center;
            line-height: 48rpx;
            color: #707070;
            font-family: Noto Sans Kannada, Noto Sans Kannada;
            font-weight: bold;
            font-size: 32rpx;
            line-height: 96upx;
            border-radius: 12upx;
            margin: 52rpx auto 30rpx;
        }
    }
    
    .butt {
        width: 480upx !important;
        height: 96upx !important;
        background: #1A73E8 !important;
        border-radius: 20upx !important;
        position: absolute !important;
        left: 50% !important;
        bottom: 40upx !important;
        transform: translateX(-50%) !important;
    }

    .insertContent {
        box-sizing: border-box;
        padding: 160upx 46upx 0;
        height: 100%;
        position: relative;
        .insertName {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #86868B;
            margin-bottom: 10upx;
        }
    }

    .pTitle {
        position: relative;
        height: 111upx;
        background: #FBFBFD;
        border-top-left-radius: 32upx;
        border-top-right-radius: 32upx;
        border-bottom: 2upx solid #d2d2d7;

        .pt {
            width: 100%;
            text-align: center;
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            line-height: 111upx;
        }

        .closeImage {
            width: 60upx;
            height: 60upx;
            position: absolute;
            top: 26upx;
            left: 32upx;

            image {
                width: 100%;
                height: 100%;
            }
        }
    }

    .pContent {
        width: 100%;
        height: 80vh;
        position: relative;
        // background: red;
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

    .swipe-action__content {
        padding: 0 !important;
    }

    .arItem {
        font-size: 32upx;
        font-family: Hiragino Sans-W6, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        background: #F5F5F7;
        box-sizing: border-box;
        padding: 64upx 40upx;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .addContent {
        background: #fff;
        border-top: 2upx solid #d2d2d7;
        border-bottom: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding: 40upx;
    }

    .addButton {
        background: #1A73E8;
        display: flex;
        align-items: center;
        color: #fff;
        width: 324upx;
        height: 80upx;
        border-radius: 20upx;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 0 20upx;
        font-size: 32upx;
    }
</style>
