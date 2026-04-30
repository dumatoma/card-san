<template>
    <view>
        <u-navbar title="メンバー編集" :placeholder="true" :safeAreaInsetTop="true" :border="true">
            <view class="u-nav-slot" slot="left">
                <image @click="bac" src="../../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" mode="">
                </image>
            </view>
            <view class="u-nav-slot" slot="right">
                <text @click="save" class="tc">保存</text>
            </view>
        </u-navbar>
        <view class="container">
            <view class="userInfo">
                <view class="infoItem">
                    <view class="itemLeft">
                        ID
                    </view>
                    <view class="itemRight" v-text='id'></view>
                </view>
                <view class="infoItem">
                    <view class="itemLeft">
                        顧客NO
                    </view>
                    <view class="itemRight bd">
                        <u-input v-model="no" border="none" class="insert"></u-input>
                    </view>
                </view>
                <view class="infoItem">
                    <view class="itemLeft">
                        <image src="../../../static/icons/cardImg.png" mode="widthFix"></image>
                    </view>
                    <view class="itemRight blue" @click="toChooseCard">
                        <text>{{cards.card_name}}</text>
                        <image src="../../../static/icons/barr.png" mode=""></image>
                    </view>
                </view>
                <view class="infoItem">
                    <view class="itemLeft">
                        氏名
                    </view>
                    <view class="itemRight" v-text="userInfo.name"></view>
                </view>
                <view class="infoItem">
                    <view class="itemLeft">
                        カナ
                    </view>
                    <view class="itemRight" v-text="userInfo.pronunciation"></view>
                </view>
                <view class="infoItem">
                    <view class="itemLeft">
                        電話番号
                    </view>
                    <view class="itemRight blue" v-text="userInfo.phone"></view>
                </view>
                <view class="infoItem">
                    <view class="itemLeft">
                        性別
                    </view>
                    <view class="itemRight" v-text="userInfo.sex"></view>
                </view>
                <view class="infoItem">
                    <view class="itemLeft">
                        生年月日
                    </view>
                    <view class="itemRight">
                        {{userInfo.birthday_year}}年　{{userInfo.birthday_month}}月　{{userInfo.birthday_day}}日
                    </view>
                </view>
                <view class="infoItem">
                    <view class="itemLeft">
                        住所
                    </view>
                    <view class="itemRight">
                        <view v-text="userInfo.zip_code"></view>
                        <view v-text="userInfo.province"></view>
                        <view v-text="userInfo.address1"></view>
                        <view v-text="userInfo.address2"></view>

                    </view>
                </view>
                <view class="infoItem">
                    <view class="itemLeft">
                        備考
                    </view>
                </view>
                <scroll-view class="area" scroll-y>
                    <u-textarea autoHeight :maxlength="-1" border="none" v-model="userInfo.remark" placeholder="お客さまのデータには反映されません"></u-textarea>
                       
                </scroll-view>
            </view>
        </view>
        <view class="blank"></view>
    </view>
</template>

<script>
    import {
        editMemberInformation,
        getMemberDetail
    } from '@/api/index.js'
    export default {
        data() {
            return {
                id: "",
                no: "",
                remark: "",
                userId: "",
                userInfo:{},
                cards:{}
            }
        },
        onLoad(options) {
            let that = this
            let query = options
            that.query = query
            
            that.id = query.id
            that.no = query.NO
            that.remark = query.remark
            that.userId = query.userId
        },
        onShow() {
            let that = this
            setTimeout(() => {
                that.getDetail(that.query.userId)
            },1000)
        },
        methods: {
            bac() {
                uni.navigateBack()
            },
            
            getDetail(id){
                let that = this
                let data = {}
                data.id = id
                getMemberDetail(data).then((res) => {
                    if(res.code == 200){
                        that.userInfo = res.data
                        that.cards = res.data.card
                    }
                })
            },

            toChooseCard(){
               let that = this
               uni.navigateTo({
                   url:"../cards/cards?userId="+that.query.userId+"&id="+that.userInfo.cid+"&name="+that.userInfo.name
               })
            },

            save() {
                let that = this
                let data = {}
                let obj = {}
                data._method = "put"
                data.member_no = that.no
                data.remark = that.userInfo.remark
                obj.id = that.userId
                editMemberInformation(data, that.userId).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message,
                            duration: 2000
                        })
                        setTimeout(function() {
                            let pages = getCurrentPages();
                            let prePage = pages[pages.length - 2]
                            if (prePage.route == 'pages/customer/detail/detail') {
                                prePage.$vm.getDetail(obj)
                            }
                            uni.navigateBack()
                        }, 2100)
                    }
                })
            }
        }
    }
</script>

<style lang="scss">
    .area ::-webkit-scrollbar {
        display: block;
        width: 10rpx !important; 
         height: 30rpx !important;
        -webkit-appearance: normal !important;
         background: #f8f8f8 !important;
         scrollbar-color: dark !important;
    }
    .area ::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 20rpx !important;
    }
    .container {
        border-top: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding-top: 80upx;
    }

    .blank {
        height: 202upx;
    }

    .area {
        width: 658upx;
        height: 320upx;
        border-radius: 20upx;
        border: 2upx solid #707070;
        box-sizing: border-box;
        padding: 20upx;

        textarea {
            width: 100%;
        }
    }


    .bd {
        border-bottom: 2upx solid #707070;
    }

    .blue {
        display: flex;
        align-items: center;

        image {
            width: 24upx;
            height: 34upx;
            margin-left: 40upx;
        }
    }

    .infoItem {
        display: flex;
        margin-bottom: 50upx;

        .itemLeft {
            width: 130upx;
            text-align: center;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-right: 40upx;
            flex-shrink: 0;

            image {
                width: 60upx;
                height: 44upx;
            }
        }
    }
</style>
