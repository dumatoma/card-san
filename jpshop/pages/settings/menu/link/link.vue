<template>
    <view class="cont">
        <view class="tips">
            <view class="tipIcon">
                <image src="../../../../static/icons/menu_d.png" mode=""></image>
            </view>
            <view class="tipDetail">
                メニューアイコンをタップした際の、
                リンク先URLを入力して下さい。
            </view>
        </view>
        <view class="insertContent">
            <view class="insertName">
                URL
            </view>
            <u-input v-model="urls" placeholder="リンク先のURLを入力してください "></u-input>
        </view>

        <view class="selece">
            <view class="selectContents" @click="changeCheck">
                <view class="unselect" v-if="checked != 1"></view>
                <view class="selected" v-if="checked == 1">
                    <view class="fill"></view>
                </view>
            </view>
            <view class="seName" :style="checked?'color:#1d1d1d':''">
                外部リンク先を上記URLに設定する
            </view>
        </view>

        <u-button type="primary" text="保存" @click="save" class="butt"></u-button>
    </view>
</template>

<script>
    import {
        setShopInfo,
        getShopInfo
    } from "@/api/index.js"
    export default {
        data() {
            return {
                checked: 0,
                urls: ""
            };
        },
        created() {
            this.getUrl()
        },
        methods: {
            getUrl() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        let url = res.data.shop_info.out_url.split("!@")
                        that.urls = url[0]
                        that.checked = url[1]
                    }
                })
            },

            save() {
                let that = this
                let data = {}
                data.type = 'out_url'
                data.out_url = that.urls + '!@' + that.checked
                setShopInfo(data).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message
                        })
                    }
                })
            },

            changeCheck() {
                if (this.checked == 1) {
                    this.checked = 0
                } else {
                    this.checked = 1
                }
            }
        }
    }
</script>

<style lang="scss">
    .selece {
        display: flex;
        align-items: center;

        .seName {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #AAAAAA;
            margin-left: 20upx;
        }
    }

    .butt {
        width: 480upx;
        height: 96upx;
        background: #1A73E8;
        border-radius: 20upx;
        margin-top: 600upx;
    }

    .cont {
        width: 100vw;
        height: 100vh;
        box-sizing: border-box;
        padding: 46upx;
        border-top: 2upx solid #d2d2d7;

        .tips {
            display: flex;
            background: #F5F5F7;
            box-sizing: border-box;
            padding: 28upx;
            border-radius: 20upx;

            .tipIcon {
                width: 40upx;
                height: 40upx;
                margin-right: 14upx;
                flex-shrink: 0;

                image {
                    width: 100%;
                    height: 100%;
                }
            }
        }

        .insertContent {
            box-sizing: border-box;
            padding: 78upx 0 86upx;

            .insertName {
                font-size: 28upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #86868B;
                margin-bottom: 10upx;
            }
        }
    }
</style>
