<template>
    <view>
        <u-navbar :border="true" :placeholder='true' title="スタッフ" :autoBack="false" @leftClick = "goBack" :safeAreaInsetTop="true">
            <view class="u-nav-slot" slot="right">
                <image src="../../../static/icons/addB.png" style="width: 60upx;height: 60upx;" @click="toAddnew"
                    mode=""></image>
            </view>
        </u-navbar>
        <view class="content">
            <u-swipe-action>
                <u-swipe-action-item :options="options1" v-for="(item,index) in array" @click="delItem(index)">
                    <view class="swipe-action u-border-top u-border-bottom">
                        <view class="swipe-action__content">
                            <view class="staffItem" @click="editItem(index)">
                                <view class="staffImage">
                                    <image :src="item.image[0]" mode="aspectFill"></image>
                                </view>
                                <view class="staffDetail">
                                    <view class="staffName" style="display: flex;justify-content: space-between;">
                                        <view v-text="item.name"></view>
                                        <image v-if="index != 0" class="sor" src="../../../static/svg/upup.svg" mode="" @click.stop="sort(item,index)"></image>
                                    </view>
                                    <view class="staffFu" v-text="item.post"></view>
                                    <view class="staffIntro" v-text="item.self_introduction"></view>
                                </view>
                            </view>
                        </view>
                    </view>
                </u-swipe-action-item>
            </u-swipe-action>
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
                show:false
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
                console.log("ar",data)
                staffSort(data).then((res) => {
                    console.log("res",res)
                    if (res.code == 200) {
                         uni.hideLoading()
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
                })
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
                    })
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

<style lang="scss">
    .sor{
        width: 48rpx;
        height: 48rpx;
        flex-shrink: 0;
    }
    .staffItem {
        display: flex;
        box-sizing: border-box;
        padding: 24upx 24upx 40upx 16upx;

        .staffDetail {
            flex: 1;
            .staffName {
                font-size: 32upx;
                font-family: Hiragino Sans-W6, Hiragino Sans;
                font-weight: bold;
                color: #1D1D1F;
                width: 100%;
            }

            .staffFu {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: rgba(29, 29, 31, 0.9);
                margin: 20upx 0;
            }

            .staffIntro {
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #86868B;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
            }
        }



        .staffImage {
            width: 160upx;
            height: 214upx;
            flex-shrink: 0;
            margin-right: 20upx;

            image {
                width: 100%;
                height: 100%;
            }
        }
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
</style>
