<template>
    <view class="contain">
        <u-navbar title="予約基本設定" border @leftClick="handleBack" :safeAreaInsetTop="true" bgColor="rgb(251,251,253)"
            :placeholder="true">
        </u-navbar>
        <view class="top1" hover-class="hit" @click="toOut">
            <view class="topName">
                予約機能外部LINK設定
            </view>
            <view class="topTab">
               <u-icon name="arrow-right"></u-icon>
            </view>
        </view>
        <view class="top1">
            <view class="topName">
                指名制予約を利用する
            </view>
            <view class="topTab">
                 <u-switch v-model="value" @change="change" activeColor="#34C759"></u-switch>
            </view>
        </view>
        <view class="top2">
            オフにすると指名制の予約は利用できません。　予約に対応するスタッフの人数分の枠数で予約を受け付けます。
        </view>
        <view class="top3">
            予約に対応するスタッフを選択
        </view>
        <view class="top4">
            <view class="item" v-if="value">
                <view class="itemLeft">
                    <view class="selectContent" @click="openZ">
                        <view class="unselect" v-if="no_appoint == 0"></view>
                        <view class="selected" v-if="no_appoint == 1">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view class="itemName" :style="no_appoint == 1? 'color:#1D1D1F' : ''">指名なし</view>
                    <view class="colorSq" style="background:grey"></view>
                </view>
            </view>
            <view class="item" v-for="(item,index) in manager" :key="index">
                <view class="itemLeft">
                    <view class="selectContent" @click="choosePerson(item.id,index)">
                        <view class="unselect" v-if="!item.is_appoint"></view>
                        <view class="selected" v-if="item.is_appoint">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view class="itemName" v-text="item.name" :style="item.is_appoint? 'color:#1D1D1F' : ''"></view>
                    <view class="colorSq" :style="`background:${item.color}`"></view>
                </view>
                <view class="itemRight" v-if="item.is_appoint" @click="goDetail(item)">
                    詳細設定
                </view>
            </view>
        </view>
    </view>
</template>
<script>
    import {
        getShopInfo,
        getStaffList,
        setPoint,
        setStaffAppointInfo
    } from '@/api/index.js'
    export default {
        name: "add",
        props: {
            config: Object
        },
        data() {
            return {
                value: false,
                manager: [],
                no_appoint:0
            };
        },
        created() {
            this.getShopInfo()
            this.getStaffList()
        },
        methods: {
            openZ(){
              let that = this
              let data = {}
              data.type = "no_appoint"
              data.no_appoint = that.no_appoint == 0?1:0
              setPoint(data).then((res) => {
                  if(res.code == 200){
                      that.no_appoint = data.no_appoint
                  }
              })
            },
            handleBack() {
                this.$emit("close", true)
            },
            change(e) {
                let data = {}
                data.type = "is_appoint"
                data.is_appoint = e == true? 1 : 0
                setPoint(data).then((res) => {
                    if(res.code == 200){
                        uni.showToast({
                            title:res.message,
                        })
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
            },
            getStaffList() {
                let that = this
                let data = {
                    id: "",
                    start_time: "",
                    end_time: ""
                }
                getStaffList(data).then((res) => {
                    if (res.code == 200) {
                        res.data.admins.forEach((val, index) => {
                            val.checked = false
                            if (val.color == "") {
                                val.color = '#175491'
                            }
                        })
                        that.manager = res.data.admins
                        that.no_appoint = res.data.no_appoint
                    }
                })
            },
            getShopInfo() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        if (res.data.shop_info.is_appoint == 1) {
                            that.value = true
                        } else {
                            that.value = false
                        }
                    }
                })
            },
            choosePerson(e,idx) {
                let that = this
                let data = {}
                data.id = e
                data.is_appoint = !that.manager[idx].is_appoint
                setStaffAppointInfo(data).then((res) => {
                    if(res.code == 200){
                        that.manager[idx].is_appoint = !that.manager[idx].is_appoint
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
                // let arr = that.manager
                // arr.forEach((value, index) => {
                //     if (e == index) {
                //         value.checked = true
                //     } else {
                //         value.checked = false
                //     }
                // })
                // that.manager = arr
            },
            goDetail(e) {
                this.$emit("goDetail", e)
            },
            toOut(){
                this.$emit("toOut", 1)
            }
        }
    }
</script>

<style lang="scss">
    .hit{
        background: rgba(29, 29, 31, .1) !important;
    }
    .contain {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: #fff;
        z-index: 999;
        overflow-y: scroll;

        .top1 {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 64upx 40upx 44upx;
            justify-content: space-between;
            border-bottom: 2upx solid #d2d2d7;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: rgba(29, 29, 31, 0.9);
        }

        .top2 {
            box-sizing: border-box;
            padding: 10upx 40upx 60upx;
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            border-bottom: 2upx solid #d2d2d7;
        }

        .top3 {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            background: #F5F5F7;
            box-sizing: border-box;
            padding: 80upx 40upx 44upx;
            border-bottom: 2upx solid #d2d2d7;
        }

        .top4 {
            .item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-sizing: border-box;
                padding: 60upx 40upx;
                border-bottom: 2upx solid #d2d2d7;

                .itemLeft {
                    display: flex;
                    align-items: center;
                    font-size: 40upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;

                    .selectContent {
                        margin-right: 40upx;
                    }

                    .colorSq {
                        width: 40upx;
                        height: 40upx;
                        border-radius: 8upx;
                        opacity: 1;
                        margin-left: 32upx;
                    }
                }

                .itemRight {
                    width: 184upx;
                    height: 88upx;
                    background: #1A73E8;
                    opacity: 1;
                    font-size: 32upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #FFFFFF;
                    border-radius: 18upx;
                    text-align: center;
                    line-height: 88upx;
                }
            }

        }
    }
</style>
