<template>
    <view class="contain border_top">
        <u-navbar title="メニュー編集" :placeholder="true" :border="true" :autoBack="true" :safeAreaInsetTop="true">
            <view class="u-nav-slot" slot="right">
                <image src="../../../static/svg/pre.svg" style="width: 50upx;height: 50upx;" @click="showPop = true" mode=""></image>
            </view>
        </u-navbar>
        <view class="content">
            <view class="images">
                <view class="imageItem" v-for="(item,index) in array" :key="index">
                    <image :src="item" mode="aspectFill"></image>
                    <view class="close">
                        <image src="../../../static/icons/wclose.png" @click="delImage(index)"></image>
                    </view>
                </view>
            </view>
            <view class="tips">
                画像は4枚まで掲載可
            </view>
            <button type="default" class="addImg" @click="uploadImage">
                <text>+</text>
                <text>画像を選択</text>
            </button>
            <view class="insertContent">
                <view class="insertItem" style="margin-top: 0;">
                    <view class="insertTitle">
                        カテゴリー
                    </view>
                    <view class="insertPicker" @click="p1">
                        <view v-text="cateName"></view>
                        <u-icon name="arrow-down"></u-icon>
                    </view>
                </view>
                <view class="insertItem">
                    <view class="insertTitle">
                        <text style="color: #D93025;">※</text> メニュータイトル
                    </view>
                    <u-input placeholder="メニュー名を入力してください" v-model="info.title"></u-input>
                </view>
                <view class="insertItem">
                    <view class="insertTitle">
                        メニュー説明
                    </view>
                    <u-textarea placeholder="メニュー名を入力してください" maxlength="-1" v-model="info.des"></u-textarea>
                </view>
                <view class="insertItem">
                    <view class="insertTitle">
                        価格
                    </view>
                    <u-input placeholder="価格を入力してください" v-model="info.price"></u-input>
                </view>
                <view class="sTitle">
                    <view class="titleLeft" @click="changeAppoint">
                        <view class="select" :style="info.is_order == 0? 'border:2upx solid #d2d2d7' : ''">
                            <view class="fill" v-if="info.is_order == 1"></view>
                        </view>
                    </view>
                    <view class="titleRight">
                        予約メニューに設定
                    </view>
                </view>
                <view class="sTitle" style="margin-top: 44upx;" v-if="info.is_order == 1">
                    <view class="titleLeft">
                        <view class="select" style="visibility: hidden;">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view class="titleRight">
                        所要時間(目安)
                    </view>
                </view>
                <view class="sTitle" style="margin-top: 44upx;" v-if="info.is_order == 1">
                    <view class="titleLeft">
                        <view class="select" style="visibility: hidden;">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view class="titleRight" style="font-size: 24upx;color: #707070;">
                        <text style="color: #D93025;">※</text>施術にかかる時間を設定してください。
                    </view>
                </view>
                <view class="sTitle" style="margin-top: 20upx;"  v-if="info.is_order == 1">
                    <view class="titleLeft">
                        <view class="select" style="visibility: hidden;">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view class="titleRight">
                        <view class="iPicker" @click="p2">
                            <view v-text="`${info.order_time}分`"></view>
                            <u-icon name="arrow-down"></u-icon>
                        </view>
                    </view>
                </view>
            </view>
        </view>
        <u-button type="primary" style="width: 80%;margin-top: 200upx;background-color: #1A73E8;height: 96upx;border-radius: 20upx;" @click="save">保存</u-button>
        <view style="height: 110upx;"></view>
        <u-picker :show="show" :columns="columns" @confirm="show = false" @change="cateChange" keyName="name" cancelText=" " confirmText=" "
            :closeOnClickOverlay="true" @close="close1" @cancel="close1" title="カテゴリーを選択"></u-picker>
        <u-picker :show="show1" :columns="columns1" @change="timeChange" keyName="time" cancelText=" " confirmText=" "
            :closeOnClickOverlay="true" @close="close2" @confirm="show1 = false"  @cancel="close2" title="時間を選択"></u-picker>
        <u-popup :show="showPop" mode="bottom" :round="10" @close="closePop" @cancel="closePop">
            <view class="pTitle">
                <view class="pClose">
                    <image src="../../../static/svg/button.svg" style="width: 60upx;height: 60upx;" mode="" @click="closePop"></image>
                </view>
                <view class="tDetail">
                    メニュー
                </view>
            </view>
            <view class="pContent">
                <view class="pDetail">
                    <view class="dLeft">
                        <view class="dTop">
                            {{info.title || 'メニュータイトル・予約なしの場合'}}
                        </view>
                        <view class="dBottom">
                            <view class="dPrice">
                                ￥{{info.price || 0}}
                            </view>
                            <view class="dTime">
                                所要時間：{{info.order_time}}分
                            </view>
                        </view>
                    </view>
                    <view class="dRight">
                        <image class="dr" src="../../../static/icons/collection.png" mode=""></image>
                    </view>
                </view>
                <view class="dc">
                    <view class="dcLeft">
                        <view class="imageCotent">
                            <image class="album" src="../../../static/icons/album.png" mode=""></image>
                            <image style="width: 100%;height: 100%;" :src="array[0]" mode="aspectFill"></image>
                        </view>
                    </view>
                    <view class="dcRight" v-text="info.des"></view>
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import {
        menuCateList,addMenu,menuList,editMenu
    } from "@/api/index.js"
    export default {
        data() {
            return {
                show: false,
                show1: false,
                showPop: false,
                cateName:'',
                details:"",
                price:"",
                cid:"",
                time:"30",
                title:"",
                appoint:false,
                info:{},
                columns: [
                ],
                columns1: [
                    [{
                            time: "30分",
                            num:30
                        },
                        {
                            time: "60分",
                            num:60
                        },
                        {
                            time: "90分",
                            num:90
                        },
                        {
                            time: "120分",
                            num:120
                        },
                        {
                            time: "150分",
                            num:150
                        },
                        {
                            time: "180分",
                            num:180
                        },
                        {
                            time: "210分",
                            num:210
                        },
                        {
                            time: "240分",
                            num:240
                        }
                    ]
                ],
                array: [],
                id:0
            };
        },
        onNavigationBarButtonTap() {
            this.showPop = true
        },
        onLoad(options) {
            let that = this
            that.id = options.id
            menuCateList().then((res) => {
                if (res.code == 200) {
                     that.columns.push(res.data.cates)
                    menuList().then((result) => {
                        if(result.code == 200){
                            result.data.menus.forEach((v,i) => {
                                if(v.id == options.id){
                                    that.info = v
                                    that.array = v.image
                                    res.data.cates.forEach((value,index) => {
                                        if(v.cid == value.id){
                                            that.cateName = value.name
                                            that.cid = value.id
                                        }
                                    })
                                }   
                            })
                        }
                    })
                    
                }
            })  
        },
        methods: {
            changeAppoint(){
                if(  this.info.is_order == 1){
                      this.info.is_order =0
                }else{
                      this.info.is_order = 1
                }
            },
            timeChange(e){
               let that = this
               that.info.order_time = e.value[0].num
            },
            cateChange(e){
                let that = this
                that.info.cid = e.value[0].id
                that.cateName = e.value[0].name
            },
            delImage(index){
               let that = this
               that.array.splice(index, 1)
            },
            save(){
              let that = this
              let data = that.info
              data.image = that.array
              data.id = that.id
              data._method = "put"
              editMenu(data).then((res) => {
                  if(res.code == 200){
                      uni.showToast({
                          title:res.message,
                          duration:2000
                      })
                      setTimeout(function(){
                          let pages = getCurrentPages();
                          let currPage = pages[pages.length - 1] 
                          let prePage = pages[pages.length - 2] 
                          prePage.$vm.getCate()
                          uni.navigateBack({}) 
                      },2000)
                  }else{
                      uni.showToast({
                          title:res.message,
                          icon:"none"
                      })
                  }
              })
            },
            // 选择图片并上传
            uploadImage() {
                let that = this
                if(that.array.length < 4){
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
                }else{
                    uni.showToast({
                        title:"画像は最大で4枚まで",
                        icon:"none"
                    })
                }
                
            },
            p1() {
                this.show = true
            },
            p2() {
                this.show1 = true
            },
            close1() {
                this.show = false
            },
            close2() {
                this.show1 = false
            },
            closePop() {
                this.showPop = false
            }
        }
    }
</script>

<style lang="scss">
    .dc {
        display: flex;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 28upx 32upx;
        border-bottom: 2upx solid #d2d2d7;

        .dcRight {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-left: 20upx;
            flex: 1;
            text-align: left;
        }

        .imageCotent {
            width: 246upx;
            height: 246upx;
            position: relative;
            border-radius: 12upx;
            border: 2upx solid #EAEAEA;

            .album {
                width: 64upx;
                height: 64upx;
                border-radius: 12upx;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 99;
            }
        }

    }

    .pDetail {
        display: flex;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 44upx 30upx 0;

        .dRight {
            width: 60upx;
            height: 60upx;
            // background: #D2D2D7; 

            .dr {
                width: 100%;
                height: 100%;
            }
        }

        .dTop {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }
    }

    .dBottom {
        display: flex;
        align-items: center;
        margin-top: 28upx;

        .dPrice {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: rgba(217, 48, 37, 0.65);
            margin-right: 68upx;
        }

        .dTime {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }
    }

    .pTitle {
        height: 104upx;
        background: #F5F5F7;
        border-top-left-radius: 32upx;
        border-top-right-radius: 32upx;
        display: flex;
        align-items: center;
        box-sizing: border-box;
        padding: 0 32upx;

        .tDetail {
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .pClose {
            width: 60upx;
            height: 60upx;
            margin-right: 202upx;

            image {
                width: 100%;
                height: 100%;
            }
        }
    }

    .sTitle {
        display: flex;
        align-items: center;
        margin: 114upx 0 0;
    }

    .btn {
        width: 480upx;
        height: 96upx;
        background: #1A73E8;
        border-radius: 20upx;
        font-size: 32upx;
        font-family: Hiragino Sans-W6, Hiragino Sans;
        font-weight: normal;
        color: #FFFFFF;
        line-height: 96upx;
        text-align: center;
        margin-top: 182upx;
    }

    .titleLeft {
        margin-right: 20upx;
    }

    .iPicker {
        width: 400upx;
        height: 80upx;
        background: rgba(249, 249, 249, 0.94);
        border-radius: 8upx;
        opacity: 1;
        border: 2upx solid #D2D2D7;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 0 20upx;
        color: #86868B;
    }

    .select {
        width: 40upx;
        height: 40upx;
        background: #FFFFFF;
        opacity: 1;
        border: 2upx solid #1A73E8;
        border-radius: 50%;
        box-sizing: border-box;
        padding: 6upx;

        .fill {
            background: #1A73E8;
            border-radius: 50%;
            height: 100%;
            width: 100%;
        }
    }

    .insertContent {
        box-sizing: border-box;
        padding: 120upx 46upx 0;

        .insertItem {
            margin-top: 80upx;

            .insertTitle {
                margin-bottom: 10upx;
            }

            .insertPicker {
                height: 80upx;
                background: #F5F5F7;
                border-radius: 20upx;
                opacity: 1;
                border: 2upx solid #D2D2D7;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-sizing: border-box;
                padding: 0 20upx;
            }
        }
    }

    .addImg {
        width: 480upx;
        height: 96upx;
        background: #F5F5F7;
        border-radius: 20upx;
        border: 2upx solid #707070;
        display: flex;
        align-items: center;
        justify-content: center;

        text:last-child {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: rgba(29, 29, 31, 0.9);
        }

        text:first-child {
            font-size: 50upx;
            color: #1D1D1F;
            margin-right: 10upx;
        }
    }

    .content {
        .tips {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            margin-top: 50upx;
            margin-bottom: 26upx;
        }

        .images {
            display: flex;
            align-items: center;
            overflow-x: scroll;
            box-sizing: border-box;
            padding-left: 32upx;
            padding-top: 36upx;

            .imageItem {
                position: relative;
                width: 480upx;
                height: 480upx;
                border-radius: 8upx;
                flex-shrink: 0;
                margin-right: 20upx;

                .close {
                    position: absolute;
                    width: 52upx;
                    height: 52upx;
                    top: 14upx;
                    right: 14upx;
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
    }
</style>
