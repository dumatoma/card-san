<template>
    <view class="border_top">
        <view class="content">
            <view class="email" v-text="eMail"></view>
            <view class="changeEmail" @click="changeMail">ID(登録メールアドレス)の変更</view>
            <view class="info">
                <view class="infoItem">
                    <view class="infoLeft">
                        契約番号
                    </view>
                    <view class="infoRight" v-text="qyNum"></view>
                </view>

                <view class="infoItem">
                    <view class="infoLeft">
                        氏名
                    </view>
                    <view class="infoRight" v-text="name"></view>
                </view>

                <view class="infoItem">
                    <view class="infoLeft">
                        法人名
                    </view>
                    <view class="infoRight" v-text="frname"></view>
                </view>

                <view class="infoItem">
                    <view class="infoLeft">
                        店舗名
                    </view>
                    <view class="infoRight" v-text="shopName"></view>
                </view>

                <view class="infoItem">
                    <view class="infoLeft">
                        電話番号
                    </view>
                    <view class="infoRight" v-text="phone"></view>
                </view>

                <view class="infoItem">
                    <view class="infoLeft">
                        郵便番号
                    </view>
                    <view class="infoRight" v-text="zip"></view>
                </view>

                <view class="infoItem">
                    <view class="infoLeft">
                        住所
                    </view>
                    <view class="infoRight">
                        <view v-text="province"></view>
                        <view v-text="address1"></view>
                        <view v-text="address2"></view>
                    </view>
                </view>

                <view class="edit" @click="toEdit">
                    編集する
                </view>
            </view>
        </view>

    </view>
</template>

<script>
    import {
        getOwnerInformation,getShopInfo
    } from "@/api/index.js"
    export default {
        data() {
            return {
                eMail: "example@mail.jp",
                qyNum:"",
                name:"",
                frname:"",
                shopName:"",
                phone:"",
                zip:"",
                address1:"",
                address2:"",
                province:""
            };
        },
        created() {
            let htat = this
            htat.getOwnerInformation()
            getShopInfo().then((res) => {
                if(res.code == 200){
                   
                }
            })
        },
        methods: {
            toEdit() {
                uni.navigateTo({
                    url: "../../accounts/admin/changeInfo"
                })
            },
            changeMail() {
                let that = this
                uni.navigateTo({
                    url: "../../accounts/admin/changeMail?mail="+that.eMail
                })
            },
            getOwnerInformation() {
                let that = this
                getOwnerInformation().then((res) => {
                    if(res.code == 200){
                        let info = uni.getStorageSync("admin")
                        that.name = res.data.owner.name
                        that.phone = res.data.owner.phone
                        that.frname = res.data.owner.legal_person
                        that.zip = res.data.owner.zip_code
                        that.address1 = res.data.owner.address1
                        that.address2 = res.data.owner.address2
                        that.eMail = info.email
                        that.shopName = res.data.owner.shop_name
                        that.province = res.data.owner.province
                        that.qyNum = res.data.owner.number
                    }
                })
            }
        }
    }
</script>

<style lang="scss">
    .content {
        box-sizing: border-box;
        padding: 100upx 40upx;

        .edit {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
            text-align: right;
            margin-top: 100upx;
        }

        .email {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .changeEmail {
            font-size: 20upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
            margin-top: 40upx;
            box-sizing: border-box;
            padding-left: 40upx;
            margin-bottom: 100upx;
        }
    }

    .infoItem {
        display: flex;
        margin-bottom: 32upx;

        .infoLeft {
            width: 154upx;
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-right: 20upx;
            text-align: center;
        }

        .infoRight {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }
    }
</style>
