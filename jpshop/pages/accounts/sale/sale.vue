<template>
    <view>
        <view class="goodsList border_top">
            <view class="goodsitem border_bottom" v-for="(item,index) in list">
                <view class="itemLeft">
                    <image :src="item.carousel[0]" mode=""></image>
                </view>
                <view class="itemMiddle">
                    <view class="goodsName" v-text="item.goods_name"></view>
                    <view class="goodsPrice" v-text="`${item.price}円`"></view>
                </view>
                <view class="itemRight" @click="toDetail(item.id)">
                    詳 細
                </view>
            </view>
        </view>
    </view>
</template>

<script>
    import {
        getGoodsList
    } from "@/api/index.js"
    export default {
        data() {
            return {
                list: [],
                page: 1
            }
        },
        created() {
            this.getGoodsList()
        },
        methods: {
            getGoodsList() {
                let that = this
                let data = {}
                data.page = that.page
                data.page_size = 30
                getGoodsList(data).then((res) => {
                    if(res.code == 200){
                        that.list = res.data.goods
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
            },

            toDetail(e) {
                uni.navigateTo({
                    url: "../sale/detail?id="+e
                })
            }
        }
    }
</script>

<style lang="scss">
    .goodsList {
        .goodsitem {
            display: flex;
            box-sizing: border-box;
            padding: 40upx 32upx;

            .itemMiddle {
                flex: 1;
                
                .goodsName {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }

                .goodsPrice {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                    margin-top: 32upx;
                }
            }

            .itemLeft {
                width: 200upx;
                height: 200upx;
                flex-shrink: 0;
                margin-right: 20upx;
                border-radius: 12upx;
                overflow: hidden;

                image {
                    width: 100%;
                    height: 100%;
                }
            }

            .itemRight {
                width: 160upx;
                height: 80upx;
                background: #ED8E20;
                border-radius: 44upx;
                font-size: 28upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #FFFFFF;
                text-align: center;
                line-height: 80upx;
                margin-left: 20upx;
                position: relative;
                top: 56upx;
            }
        }
    }
</style>
