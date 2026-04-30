<template>
    <view>
        <!-- 自定义顶部导航 -->
        <u-navbar title="メンバー情報" :placeholder="true" :safeAreaInsetTop="true" :border="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" mode="" @click="bac">
                </image>
            </view>
            <view class="u-nav-slot" slot="right">
                <text @click="toCsv" class="tc">編集</text>
            </view>
        </u-navbar>
        <!-- 具体内容 -->
        <view class="container">
            <view class="userAvatar">
                <u-avatar mode="aspectFill" size="87" :src="userInfo.avatar"></u-avatar>
                <view @click="toRecord" class="count" v-text="count"></view>
            </view>
            <view class="userInfo">
                <view class="infoItem">
                    <view class="itemLeft">
                        ID
                    </view>
                    <view class="itemRight">
                        <text v-text="userInfo.member_id" selectable="true"></text>
                    </view>
                </view>
                <view class="infoItem">
                    <view class="itemLeft">
                        顧客NO
                    </view>
                    <view class="itemRight" v-text="userInfo.member_no"></view>
                </view>
                <view class="infoItem">
                    <view class="itemLeft">
                        <image src="../../../static/icons/cardImg.png" mode="widthFix"></image>
                    </view>
                    <view class="itemRight blue" @click="toCard"
                        v-text="`${userInfo.card_name}`"></view>
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
                    <view class="itemRight blue" @click="makePhoneCall(userInfo.phone)" v-text="userInfo.phone"></view>
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
                    <view class="itemRight" v-if="userInfo.birthday_year">
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
                    <view style="white-space: pre-wrap;" class="itemRight" v-text="userInfo.remark"></view>
                </view>
            </view>
        </view>
        <view class="blank"></view>
        <view class="userImages">
            <view class="infoItem">
                <view class="itemLeft">
                    画像
                </view>
                <view class="itemRight" style="color: #707070;">
                    アップロードした画像データは、お客様のデータには反映されません。
                </view>
            </view>
            <view class="imgs">
                <view class="imgItem" v-for="(item,index) in userInfo.image" @click="previewImg(index)">
                    <image :src="item" mode="aspectFit"></image>
                </view>
            </view>
            <button type="default" class="addImg" @click="uploadImage">
                <text>+</text>
                <text>画像アップロード</text>
            </button>
            <view class="blank" style="border: none;"></view>
        </view>
        <view v-if="showPreview"
            style="z-index: 1002 !important;position: fixed;top: 0;left: 0;width: 100vw;height: 100vh;">
            <previewImage :images="userInfo.image" :currentPage="current" :userId="userInfo.id" @closeMask="closeM">
            </previewImage>
        </view>
        <permission :show="showpermission" @close='showpermission = false'></permission>
    </view>
</template>
<script>
    import {
        getMemberDetail,
        uploadMemberImage,
        getCome,
        getAdminDetail
    } from "../../../api/index.js"
    import previewImage from '@/components/previewImg/previewImg.vue'
    import permission from "@/components/permission/permission.vue"
    export default {
        components: {
            previewImage,
            permission
        },
        data() {
            return {
                showpermission:false,
                count: 0,
                userInfo: {},
                userId: "",
                showPreview: false,
                current: 0,
                query:{}
            }
        },
        created() {
            
        },
        onLoad(options) {
            let that = this
            let query = options
            that.query = query
            if (query.id) {
                that.userId = query
                that.getDetail(query)
                that.getTimes(query.id)
            }
        },
        methods: {
            // 获取详情
            getDetail(e) {
                let that = this
                getMemberDetail(e).then((res) => {
                    if (res.code == 200) {
                        that.userInfo = res.data
                    }
                })
            },
            
            getTimes(id){
               let that = this
               let data = {}
               data.type = "come"
               data.id = id
                getCome(data).then((res) => {
                    if(res.code == 200){
                        that.count = res.data.total
                    }
                })
            },

            toCard() {
                let that = this
                let info = that.userInfo
                if (info.card.card_type == 3) {
                    uni.navigateTo({
                        url: "../cards/intergrate/intergrate?id=" + that.userInfo.id+"&source=0"
                    })
                } else {
                    uni.navigateTo({
                        url: "../cards/seal/seal?id=" + that.userInfo.id+"&source=0"
                    })
                }
            },

            // 关闭预览图片
            closeM(e) {
                this.showPreview = false
                this.userInfo.image = e
            },

            // 选择图片并上传
            uploadImage() {
                let that = this
                uni.chooseImage({
                    count: 9,
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
                                            let data = {}
                                            data.img = re.data.path
                                            uploadMemberImage(data, that.userInfo.id).then((
                                                rs) => {
                                                    if (i == res.tempFilePaths.length - 1 &&
                                                        rs.code == 200) {
                                                        uni.showToast({
                                                            title: rs.message,
                                                            icon:"none"
                                                        })
                                                        that.getDetail(that.userId)
                                                    }
                                                })
                                        }
                                    }
                                }
                            })
                        }
                    }
                });
            },
            bac() {
                uni.navigateBack()
            },

            makePhoneCall(e) {
                uni.makePhoneCall({
                    phoneNumber: e //仅为示例
                });
            },

            toRecord() {
                let that = this
                uni.navigateTo({
                    url: "../record/record?id="+that.query.id
                })
            },

            toCsv() {
                let that = this
                let admin = uni.getStorageSync("admin")
                if(admin.admin_type == 2){
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    getAdminDetail(admin.id).then((res) => {
                        uni.hideLoading()
                        if(res.data.admin.privileges.includes(4)){
                            uni.navigateTo({
                                url: "../edit/edit?id=" + that.userInfo.member_id + "&NO=" + that.userInfo.member_no +
                                    "&remark=" + that.userInfo.remark + "&userId=" + that.userInfo.id
                            })
                        }else{
                            that.showpermission = true
                        }
                    })
                }else{
                    uni.navigateTo({
                        url: "../edit/edit?id=" + that.userInfo.member_id + "&NO=" + that.userInfo.member_no +
                            "&remark=" + that.userInfo.remark + "&userId=" + that.userInfo.id
                    })
                }
               
            },

            previewImg(e) {
                let that = this
                that.showPreview = true
                that.current = e
            }
        }
    }
</script>

<style lang="scss" scoped>
    page {
        background: #fff;
        font-family: Hiragino Sans-W3, Hiragino Sans;
    }

    .tc {
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
    }

    .blank {
        width: 100%;
        height: 50upx;
        border-bottom: 2upx solid #D2D2D7;
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
        margin-top: 82upx;

        text:last-child {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: rgba(29, 29, 31, 0.9);
        }

        text:first-child {
            font-size: 50upx;
            color: #1D1D1F;
            margin-right: 20upx;
        }

    }

    .userImages {
        box-sizing: border-box;
        padding: 76upx 0 42upx;

        .imgs {
            display: flex;
            justify-content: flex-start;
            flex-wrap: wrap;

            .imgItem {
                width: 244upx;
                height: 180upx;
                flex-shrink: 0;
                // margin-bottom: 10upx;
                margin: 0 3upx 10upx;

                image {
                    width: 100%;
                    height: 100%;
                }
            }
        }

        .infoItem {
            box-sizing: border-box;
            padding: 0 46upx;

            .itemLeft {
                width: 130upx !important;
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

    }

    .userAvatar {
        height: 340upx;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;

        .count {
            width: 80upx;
            height: 80upx;
            background: #1A73E8;
            text-align: center;
            line-height: 80upx;
            font-size: 40upx;
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            border-radius: 50%;
            position: absolute;
            left: 421upx;
            top: 180upx;
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

    .blue {
        color: #1A73E8;
    }
</style>
