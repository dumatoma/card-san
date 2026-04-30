<template>
    <view class="container">
        <view class="avatar" @click="uploadImage">
            
            <u-avatar :src="avatar" size="120" mode="aspectFill"></u-avatar>
        </view>
        <!-- <view class="shopName">[各種設定/店舗情報/店舗名]が反映される</view> -->
        <view class="inpContent">
            <view class="inpTitle">
                ユーザー名
            </view>
            <view class="inp">
                <u-input v-model="name" placeholder="担当者を入力してください"></u-input>
            </view>
        </view>
        <u-button class="btn" type="primary" :plain="true" @click="save">保存</u-button>
    </view>
</template>
<script>
    import {
        getShopInfo,
        changeChatInfo
    } from "@/api/index.js"
    export default {
        data() {
            return {
                name: "",
                show: false,
                info:{},
                avatar:""
            }
        },
        created() {
           let that = this
           let userInfo = uni.getStorageSync("admin")
           that.name = userInfo.message_name == ''?userInfo.name : userInfo.message_name
           that.avatar = userInfo.avatar
           that.getUserInfo()
        },
        onShow() {
            
        },
        methods: {
            uploadImage(source) {
                let that = this
                let type = []
                type.push(source)
                uni.chooseImage({
                    count: 1,
                    success: (res) => {
                        const tempPaths = res.tempFilePaths
                        for (let i = 0; i < tempPaths.length; i++) {
                            uni.uploadFile({
                                url: that.$baseUrl+'/tool/upload/file',
                                filePath: res.tempFilePaths[i],
                                name:"file",
                                success: (result) => {
                                    if (result.errMsg == "uploadFile:ok") {
                                        let re = JSON.parse(result.data)
                                        if (re.code == 200) {
                                            that.getValue(re.data.path) 
                                        }
                                    }
                                }
                            })
                        }
                    }
                });  
            },
            toPreview() {
                let that = this
                uni.navigateTo({
                    url: "preview/preview?url=" + that.info.avatar + "&name="+that.name
                })
            },
            getValue(e){
                let that = this
                if(e){
                    that.avatar = e
                }
            },
            getUserInfo() {
                let that = this
                getShopInfo().then((res) => {
                    if(res.code == 200){
                        that.info = res.data.shop_info
                    }
                })
            },
            save(){
                let that = this
                let data = {}
                data.type = "info"
                data.name = that.name
                data.avatar = that.avatar
               changeChatInfo(data).then((res) => {
                   if(res.code == 200){
                       uni.showToast({
                           title:res.message,
                           duration:2000
                       })
                       let userInfo = uni.getStorageSync("admin")
                       userInfo.message_name = that.name
                       userInfo.avatar = that.avatar
                       uni.setStorageSync("admin",userInfo)
                       setTimeout(() => {
                           uni.navigateBack()
                       },2000)
                   }
               })
            }
        }
    }
</script>

<style lang="scss">
    .container {
        border-top: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding-top: 174upx;

        .avatar {
            margin: 0 auto;
            text-align: center;
            width: 240upx;
            height: 240upx;
        }

        .shopName {
            font-size: 32upx;
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            margin-top: 60upx;
        }

        .inpContent {
            margin-top: 100upx;

            .inpTitle {
                margin-bottom: 10upx;
            }
        }

        .btn {
            width: 480upx;
            height: 96upx;
            margin-top: 398upx;
        }
    }
</style>
