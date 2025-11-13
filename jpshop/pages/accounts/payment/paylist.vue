<template>
    <view class="content border_top">
        <view class="payItem border_bottom" @click="toDetail(1)">
            <view class="payLeft">
                <view class="payName">
                    クレジットカード
                </view>
            </view>
            <view class="payRight">
                <u-icon name="arrow-right"></u-icon>
            </view>
        </view>

      <!--  <view class="payItem border_bottom" @click="toDetail(2)">
            <view class="payLeft">
                <view class="payLogo">
                    <image src="../../../static/icons/applePay.png" mode=""></image>
                </view>
                <view class="payName">
                    Apple Pay
                </view>
            </view>
            <view class="payRight">
                <u-icon name="arrow-right"></u-icon>
            </view>
        </view>

        <view class="payItem border_bottom" @click="toDetail(3)">
            <view class="payLeft">
                <view class="payLogo" style="width: 124upx;height: 60upx;">
                    <image src="../../../static/icons/googlePay.png" mode=""></image>
                </view>
                <view class="payName">
                    Google Pay
                </view>
            </view>
            <view class="payRight">
                <u-icon name="arrow-right"></u-icon>
            </view>
        </view> -->
    </view>
</template>

<script>
    export default {
        data() {
            return {

            };
        },
        methods: {
            toDetail(e) {
                uni.redirectTo({
                    url: "../payment/payDetail?type=" + e + "&source=add"
                })
            }
        }
    }
</script>

<style lang="scss">
    .content {
        .payItem {
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
            padding: 64upx 40upx;

            .payLeft {
                display: flex;
                align-items: center;


                .payLogo {
                    width: 94upx;
                    height: 60upx;
                    margin-right: 20upx;

                    image {
                        width: 100%;
                        height: 100%;
                    }
                }
            }
        }
    }
</style>
