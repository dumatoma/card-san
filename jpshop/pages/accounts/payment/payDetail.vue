<template>
    <view>
        <view class="content border_top">
            <view class="tip">
                クレジットカード情報を編集して下さい
            </view>
            <view class="cardDetail">
                <view class="cardItem mgb_60">
                    <view class="cardInfo">
                        <view class="cardName">
                            カード番号
                        </view>
                        <u-input v-model="info.card_no" border="bottom" maxlength="16"></u-input>
                    </view>
                </view>

                <view class="cardItem mgb_60">
                    <view class="cardInfo">
                        <view class="cardName">
                            有効期限
                        </view>
                        <u-input v-model="info.expire_date" border="bottom" placeholder="MM/YY"></u-input>
                    </view>
                    <view class="cardInfo" style="margin-left: 52upx;">
                        <view class="cardName">
                            CVV
                        </view>
                        <u-input v-model="info.cvv" border="bottom"></u-input>
                    </view>
                </view>

                <view class="cardItem">
                    <view class="cardInfo">
                        <view class="cardName">
                            カード名義人
                        </view>
                        <u-input v-model="info.name" border="bottom"></u-input>
                    </view>
                </view>
            </view>
            <u-button type="primary" @click="save" text="保存" class="btn"></u-button>
            <view class="cancel" @click="goBack">キャンセル</view>
        </view>
    </view>
</template>

<script>
    import {
        addPayMethod,
        getPayDetail,
        editPayMethod
    } from "@/api/index.js"
    export default {
        data() {
            return {
                query: {},
                info: {
                    cvv: "",
                    name: "",
                    expire_date: "",
                    card_no: ""
                }
            };
        },
        created() {
           
        },
        onLoad(options) {
            let that = this
            that.query = options
            if (that.query.source == "edit") {
                that.getDetail(that.query.id)
            }
        },
        methods: {
            goBack() {
                uni.navigateBack()
            },
            getDetail(id) {
                let that = this
                getPayDetail(id).then((res) => {
                    if(res.code == 200){
                        that.info = res.data.card
                    }
                })
            },
            save() {
                let that = this
                let data = that.info
                if (that.query.source == "add") {
                    data.type = that.query.type
                    addPayMethod(data).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message,
                                duration: 2000
                            })
                            setTimeout(function() {
                                uni.redirectTo({
                                    url: "../payment/payment"
                                })
                            }, 2000)
                        } else {
                            uni.showToast({
                                title: res.message
                            })
                        }
                    })
                }
                
                
                if(that.query.source == "edit"){
                    data.type = that.query.type
                    data._method = "put"
                    editPayMethod(data,data.id).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message,
                                duration: 2000
                            })
                            setTimeout(function() {
                                uni.redirectTo({
                                    url: "../payment/payment"
                                })
                            }, 2000)
                        } else {
                            uni.showToast({
                                title: res.message
                            })
                        }
                    })
                }
                
            }
        }
    }
</script>

<style lang="scss">
    .btn {
        height: 112upx;
        background: #1A73E8;
        border-radius: 20upx;
        margin: 372upx 0 100upx;
    }

    .cancel {
        text-align: center;
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #707070;
    }

    .content {
        width: 100%;
        box-sizing: border-box;
        padding: 80upx 40upx;

        .tip {
            box-sizing: border-box;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-bottom: 40upx;
        }

        .cardDetail {
            width: 100%;
            box-sizing: border-box;
            padding: 40upx 38upx;
            background: #FFFFFF;
            box-shadow: 0px 0px 10upx 2upx rgba(0, 0, 0, 0.16);

            .cardItem {
                display: flex;

                .cardInfo {
                    flex: 1;

                    .cardName {
                        font-size: 24upx;
                        font-family: Hiragino Sans-W5, Hiragino Sans;
                        font-weight: normal;
                        color: #707070;
                    }
                }
            }
        }
    }
</style>
