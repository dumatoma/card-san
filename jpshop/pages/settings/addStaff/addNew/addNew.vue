<template>
    <view>
        <u-navbar :placeholder='true' title="スタッフ登録" :border="true" :safeAreaInsetTop="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" mode="" @click="goBack"></image>
            </view>
        </u-navbar>
        <view class="newContent">
            <view class="topImage">
                <view class="bannerItem" v-for="(item,index) in array">
                    <image :src="item" mode="aspectFill"></image>
                    <view class="close" @click="del(index)">
                        <image src="../../../../static/icons/wclose.png" mode=""></image>
                    </view>
                </view>
            </view>
            <view class="caution">
                画像は4枚まで掲載可
            </view>
            <view class="addImage" @click="uploadImage">
                <text>+</text>
                <view>画像を選択</view>
            </view>
            <view class="inp">
                <view class="inpTitle">
                    <text style="color: #D93025;">※</text>氏名
                </view>
                <u-input v-model="name" placeholder="鈴木　孝太郎"></u-input>
            </view>
            <view class="inp">
                <view class="inpTitle">
                    役職・ポジション
                </view>
                <u-input v-model="position"></u-input>
            </view>
            <view class="inp">
                <view class="inpTitle">
                    コメント・アピール
                </view>
                <u-textarea height="100" v-model="intro"> </u-textarea>
            </view>

            <view class="selece">
                <view class="selectContents" @click="changeSelect">
                    <view class="unselect" v-if="!checked"></view>
                    <view class="selected" v-if="checked">
                        <view class="fill"></view>
                    </view>
                </view>
                <view class="seName" :style="checked?'color:#1d1d1f' : ''">
                   店舗情報ページに掲載する
                </view>
            </view>
            <u-button type="primary" shape="circle" style="width: 80%;" text="保存" @click="save"></u-button>
            <view style="height: 100upx;"></view>
        </view>
    </view>
</template>

<script>
    import {
        addAdmin,
        getAdminDetail,
        eidtAdmin
    } from "@/api/index.js"
    export default {
        data() {
            return {
                array: [],
                checked: true,
                name: "",
                position: "",
                intro: ""
            };
        },
        created() {
            
        },
        onLoad(options) {
            let that = this
            that.query = options
            if (that.query.type == "edit") {
                getAdminDetail(that.query.id).then((res) => {
                    if (res.code == 200) {
                        that.array = res.data.admin.image
                        that.name = res.data.admin.name
                        that.position = res.data.admin.post
                        that.intro = res.data.admin.self_introduction
                        that.chedked = res.data.admin.is_show_shop == 1 ? true : false
                    }
                })
            }
        },
        methods: {
            uploadImage() {
                let that = this
                if (that.array.length < 4) {
                    uni.chooseImage({
                        count: 4 - that.array.length,
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
                                                that.array.push(re.data.path)
                                            }
                                        }
                                    }
                                })
                            }
                        }
                    });
                } else {
                    uni.showToast({
                        title: "画像は4枚まで掲載可",
                        icon: "none"
                    })
                }
            },

            del(index) {
                this.array.splice(index, 1)
            },

            changeSelect() {
                this.checked = !this.checked
            },
            goBack(){
              uni.navigateBack()  
            },
            save() {
                let that = this
                let data = {}
                data.name = that.name
                data.post = that.position
                data.self_introduction = that.intro
                data.is_show_shop = that.checked == 1 ? 1 : 0
                data.image = that.array
                if (that.query.type == "edit") {
                    data._method = "put"
                    eidtAdmin(data, that.query.id).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            setTimeout(function() {
                                uni.redirectTo({
                                    url: "../addStaff"
                                })
                            }, 2000)
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                } else {
                    addAdmin(data).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            setTimeout(function() {
                                uni.redirectTo({
                                    url: "../addStaff"
                                })
                            }, 2000)
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                }
            }
        }
    }
</script>

<style lang="scss">
    .swit {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 44upx 36upx;
    }

    .save {
        width: 480upx;
        height: 96upx;
        background: #1A73E8;
        border-radius: 48upx;
        color: #fff;
        margin-top: 160upx;
    }

    .selece {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        padding: 40upx 46upx;
        border-top: 2upx solid #d2d2d7;

        .seName {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #AAAAAA;
            margin-left: 20upx;
        }
    }

    .inp {
        box-sizing: border-box;
        padding: 0 54upx;
        margin-bottom: 80upx;

        .inpTitle {
            margin-bottom: 10upx;
            color: #86868B;
        }
    }

    .caution {
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        text-align: center;
        margin: 40upx 10upx 20upx;
    }

    .addImage {
        width: 480upx;
        height: 96upx;
        background: #F5F5F7;
        border: 2upx solid #707070;
        text-align: center;
        line-height: 96upx;
        margin: 0 auto 98upx;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32upx;

        text {
            font-size: 60upx;
            margin-right: 26upx;
            position: relative;
            top: -5upx;
        }
    }

    .topImage {
        box-sizing: border-box;
        padding-top: 40upx;
        display: flex;
        overflow-x: scroll;
        padding-left: 32upx;

        .bannerItem {
            width: 360upx;
            height: 480upx;
            flex-shrink: 0;
            margin-right: 32upx;
            position: relative;

            .close {
                width: 52upx;
                height: 52upx;
                position: absolute;
                right: 14upx;
                top: 14upx;
                z-index: 9;

                image {
                    width: 100%;
                    height: 100%;
                }
            }

            image {
                width: 100%;
                height: 100%;
            }
        }
    }
</style>
