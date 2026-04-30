<template>
    <view>
        <u-navbar leftText="" title="" :placeholder="true" :safeAreaInsetTop="true" bgColor="#F6F5F8" :height="56"
            :border="true" @rightClick="rightClick">
            <view class="u-nav-slot" slot="left">
                <u-search placeholder="お店を探す" v-model="keyword" bgColor=" background: #E5E5E9" @search="getList" :showAction="false">
                </u-search>
            </view>
            <view class="u-nav-right" slot="right">
                キャンセル
            </view>
        </u-navbar>
        <view class="box1">
            <view class="items" v-for="(item,index) in list" :key="index">
                <view class="title">
                    {{item.shop_name}}
                </view>
                <view class="flex">
                    <view class="des" v-text="item.des"></view>
                    <view class="btn bs" @click="toAdd(item.id,item.shop_name,item.sid)" v-if="item.has == 0">
                        カードを入手
                    </view>
                    <view class="btn" @click="toDetail(item.sid,item.mid)" v-if="item.has == 1">
                        入手済み
                    </view>
                </view>
                <view class="img">
                    <image :src="item.image" mode="aspectFill"></image>
                </view>
            </view>
        </view>
    </view>
</template>

<script>
    import {
        getAllCards
    } from "@/api/index.js"
    export default {
        data() {
            return {
                keyword: '',
                list: []
            };
        },
        created() {
          let that = this
          that.getList()
        },
        methods: {
            rightClick() {
                uni.navigateBack()
            },
            getList(){
                let that = this 
                getAllCards(that.keyword).then((res) => {
                    if(res.code == 200){
                        that.list = res.data.cards
                    }
                })
            },
            toDetail(sid,mid){
                let info = uni.getStorageSync("user")
                uni.navigateTo({
                    url:"../shop/shop?id="+sid+"&cid="+mid
                })
            },
            toAdd(id,name,sid){
                uni.navigateTo({
                    url:"../BindCard/BindCard?id="+id+"&name="+ encodeURIComponent(name) +"&sid="+sid
                })
               
            }
        }
    }
</script>

<style lang="scss">
    ::v-deep .u-search {
        width: 502rpx;
        height: 72rpx;
        border-radius: 20rpx;
        background: rgba(118, 118, 128, 0.12);
    }

    .u-nav-right {
        font-size: 32rpx;
        color: #1A73E8;
    }

    .box1 {
        width: 90%;
        margin: 0 auto;

        .items {
            margin-top: 58rpx;
            padding-bottom: 80rpx;
            border-bottom: 1px solid #E9E9E9;
        
            .title {
                font-size: 32rpx;
                font-weight: 700;
                color: #1D1D1F;
                margin-bottom: 24rpx;
            }

            .flex {
                display: flex;
                justify-content: space-between;
                align-items: center;

                .des {
                    font-size: 24rpx;
                    color: #707070;
                    overflow: hidden; //超出的文本隐藏
                    text-overflow: ellipsis; //溢出用省略号显示
                    display: -webkit-box;
                    -webkit-line-clamp: 2; // 超出多少行
                    -webkit-box-orient: vertical;
                    box-sizing: border-box;
                    padding-right: 30upx;
                }

                .btn {
                    flex-shrink: 0;
                    width: 200rpx;
                    height: 60rpx;
                    color: #007AFF;
                    font-size: 24rpx;
                    text-align: center;
                    line-height: 60rpx;
                    background: #EAEAEA;
                    border-radius: 48rpx;
                }
                
                .bs{
                   background: #007AFF !important; 
                   color: #EAEAEA !important;
                }
            }

            .img {
                margin-top: 24rpx;
                width: 100%;
                height: 396rpx;
                box-shadow: 0rpx 0rpx 12rpx 2rpx rgba(0, 0, 0, 0.16);
                border-radius: 22rpx;

                image {
                    width: 100%;
                    height: 100%;
                    border-radius: 22rpx;
                }
            }
        }
    }
</style>
