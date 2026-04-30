<template>
    <view>
        <!-- 自定义顶部导航 -->
        <u-navbar :title="name" :placeholder="true" :safeAreaInsetTop="true" bgColor="#1D1D1F" :border="true" :titleStyle="{color:'#fff'}">
            <view class="u-nav-slot" slot="left">
                <image src="../../../../static/icons/wclose.png" style="width: 60upx;height: 60upx;" mode=""
                    @click="goBack"></image>
            </view>
            <view class="u-nav-slot" slot="right">
                <image src="../../../../static/images/preview.png" style="width: 148upx;height: 68upx;"
                    @click="deleteImg" mode=""></image>
            </view>
        </u-navbar>
        <view style="width: 100%;height: 140upx;"></view>
        <view class="contain">
            <u-swiper :autoplay="false" :list="imageArray" @change="change" keyName="img" imgMode="widthFix"
                height='455' bgColor="transparent"></u-swiper>
        </view>
        <u-action-sheet :actions="list" @select="handleSelect" @close="handleClose" :closeOnClickOverlay='true'
            :closeOnClickAction="true" :safeAreaInsetBottom='true' :show="show"></u-action-sheet>
        <mod v-if="showModule" @getStatus="getStatus" :config="configs"></mod>
    </view>
</template>

<script>
    import mod from '@/components/mod.vue'
    import {changeChatInfo} from "@/api/index.js"
    export default {
        components: {
            mod
        },
        data() {
            return {
                name: "fileName",
                current: 1,
                show: false,
                sname:"",
                configs: {},
                showModule: false,
                list: [{
                        name: "カメラで撮影",
                        color: '#007AFF',
                        backgroundColor: "rgba(237,237,237,0.8)"
                    }, {
                        name: "写真を選択",
                        color: '#007AFF',
                        backgroundColor: "rgba(237,237,237,0.8)"
                    }, {
                        name: "プロフィール画像を削除",
                        color: '#D93025',
                        backgroundColor: "rgba(237,237,237,0.8)"
                    },
                    {
                        name: 'キャンセル',
                        color: "#1A73E8",
                        backgroundColor: "#fff",
                    }
                ],
                imageArray: []
            }
        },
        created() {
           
        },
        onLoad(options) {
            let query = options
            let data = {}
            data.img = query.url
            data.name = "file"
            this.imageArray.push(data)
            this.sname = options.name
        },
        methods: {
            change(e) {
                this.current = e.current + 1
                this.name = this.imageArray[e.current].name
            },
            
            // 上传图片
            uploadImage(source) {
                let that = this
                let type = []
                type.push(source)
                uni.chooseImage({
                    count: 1,
                    sourceType:type,
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
                                           let pages = getCurrentPages() 
                                           let prevPage = pages[pages.length -2] 
                                           prevPage.$vm.getValue(re.data.path) 
                                           uni.navigateBack({
                                               delta: 1 
                                           })
                                        }
                                    }
                                }
                            })
                        }
                    }
                });  
            },

            deleteImg() {
                let that = this
                that.show = true
            },
            handleClose() {
                this.show = false
            },
            handleSelect(e) {
                let that = this
                if (e.name == "プロフィール画像を削除") {
                    that.configs = {
                        title: "",
                        content: "削除しますか？",
                        confirm: true,
                        cancel: true,
                        confirmText: "削除する",
                        cancelText: "キャンセル",
                        type: "normal"
                    }
                    that.showModule = true
                }
                
                if(e.name == 'カメラで撮影'){
                    that.uploadImage('camera')
                }
                
                if(e.name == '写真を選択'){
                    that.uploadImage('album')
                }

            },
            getStatus(status) {
                let that = this
                if (status == 'confirm') {
                   let data = {}
                    data.type = "info"
                    data.name = that.sname
                    data.avatar = ''
                   changeChatInfo(data).then((res) => {
                       if(res.code == 200){
                           uni.showToast({
                               title:res.message,
                               duration:2000
                           })
                           let userInfo = uni.getStorageSync("admin")
                           userInfo.name = that.sname
                           uni.setStorageSync("admin",userInfo)
                           setTimeout(() => {
                               let pages = getCurrentPages()
                               let prevPage = pages[pages.length -2] 
                               prevPage.$vm.getUserInfo() 
                               uni.navigateBack({
                                   delta: 1 
                               })
                           },2000)
                       }
                   })
                } else if (status == "cancel") {
                    that.showModule = false
                } else {

                }
            },
            goBack() {
                uni.navigateBack()
            }
        }
    }
</script>

<style lang="scss">
    page {
        background: rgba(29, 29, 31, 0.9);
        position: relative;
        min-height: 100vh;
    }

    .count {
        font-size: 36upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #FFFFFF;
        text-align: center;
        position: absolute;
        bottom: 146upx;
        width: 100%;
    }
</style>
