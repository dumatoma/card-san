<template>
    <view>
        <view class="info">
            <view class="infoItem">
                <view class="infoLeft">
                    副管理者名
                </view>
                <view class="infoRight" v-text="admin.name"></view>
            </view>
            
            <view class="infoItem">
                <view class="infoLeft">
                    電話番号<br/> <text style="font-size: 20upx;">(SMS認証用)</text> 
                </view>
                <view class="infoRight" v-text="admin.phone"></view>
            </view>
            
            <view class="infoItem">
                <view class="infoLeft">
                    契約番号
                </view>
                <view class="infoRight" v-text="info.number"></view>
            </view>
            
            <view class="infoItem">
                <view class="infoLeft">
                    法人名
                </view>
                <view class="infoRight" v-text="info.legal_person"></view>
            </view>
            
            <view class="infoItem">
                <view class="infoLeft">
                    店舗名
                </view>
                <view class="infoRight" v-text="info.shop_name"></view>
            </view>
            
            <view class="tips">
                名前、電話番号、付与された権限は、副管理者は変更できません。変更する場合は主管理者が「各種設定」>「副管理者の設定」から編集する必要がございます。
            </view>
            
            <view class="cTitle">
                付与された権限
            </view>
        </view>
        <view class="content">
            <view class="mask"></view>
            <view class="juItem" v-for="(item,index) in array">
                <view class="title">
                    <view class="selectContents" style="visibility: hidden;">
                        <view class="unselect" v-if="!item.checked"></view>
                        <view class="selected" v-if="item.checked">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view v-text="item.name"></view>
                </view>
                <view class="inner">
                    <view class="inItem" v-for="(ite,idx) in item.jus">
                        <view class="select">
                            <image v-if="ite.checked" src="../../../static/icons/select.png" mode=""></image>
                        </view>
                        <view class="inName" v-text="ite.name"></view>
                    </view>
                </view>
            </view>

        </view>
    </view>
</template>

<script>
    import {getShopInfo,getAdminDetail} from "@/api/index.js"
    export default {
        data() {
            return {
                type:"check",
                name:"鈴木　孝太郎",
                phone:"08012345678",
                admin:{},
                info:{},
                array: [{
                        name: "運営",
                        checked: false,
                        jus: [{
                                name: "QRリーダー",
                                checked: false,
                                val:0
                            },
                            {
                                name: "ダウンロードQR表示",
                                checked: false,
                                val:1
                            },
                            {
                                name: "営業カレンダー",
                                checked: false,
                                val:2
                            },
                            {
                                name: "顧客情報の編集",
                                checked: false,
                                val:3
                            },
                            {
                                name: "メッセージ",
                                checked: false,
                                val:4
                            },
                            {
                                name: "お知らせ投稿",
                                checked: false,
                                val:5
                            },
                            {
                                name: "クーポン",
                                checked: false,
                                val:6
                            },
                            {
                                name: "予約管理",
                                checked: false,
                                val:7
                            }
                        ]
                    },
                    {
                        name: "各種設定",
                        checked: false,
                        jus: [{
                                name: "店舗情報",
                                checked: false,
                                val:8
                            },
                            {
                                name: "メンバーズカード作成",
                                checked: false,
                                val:9
                            },
                            {
                                name: "デザイン設定",
                                checked: false,
                                val:10
                            },
                            {
                                name: "会員登録情報",
                                checked: false,
                                val:11
                            },
                            {
                                name: "メニュー登録",
                                checked: false,
                                val:12
                            },
                            {
                                name: "スタッフ登録",
                                checked: false,
                                val:13
                            }
                        ]
                    },
                    // {
                    //     name: "アカウント",
                    //     checked: false,
                    //     jus: [
                    //         // {
                    //         //     name: "ご利用明細",
                    //         //     checked: false,
                    //         //     val:16
                    //         // },
                    //         {
                    //             name: "販促アイテムの購入",
                    //             checked: false,
                    //             val:17
                    //         }
                    //     ]
                    // }
                ]
            };
        },
        onShow() {
            let that = this
            let admin = uni.getStorageSync("admin")
            getAdminDetail(admin.id).then((res) => {
                if(res.code == 200){
                    that.admin = res.data.admin
                    let q = that.admin.privileges
                    that.array.forEach((v,i) => {
                        v.jus.forEach((value,index) => {
                            if(q.includes(value.val+1)){
                                value.checked = true
                            }
                        })
                    })
                }
            })
           
            getShopInfo().then((res) => {
                if(res.code == 200){
                    that.info = res.data.shop_info
                    
                }else{
                    uni.showToast({
                        title:res.message,
                        icon:"none"
                    })
                }
            })
            
        }
    }
</script>

<style lang="scss">
    .content{
        position: relative;
        
        .mask{
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, .5);
            // background-color: red;
        }
        
    }
    .title {
        display: flex;
        align-items: center;
        background: #F5F5F7;
        box-sizing: border-box;
        padding: 36upx 46upx;
        font-size: 40upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;

        .selectContents {
            margin-right: 20upx;
        }
    }
    
    .cTitle{
        font-size: 40upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        box-sizing: border-box;
        padding: 42upx 0;
        text-align: center;
    }
    
    .tips{
        width: 658upx;
        // height: 152upx;
        background: rgba(143,168,248,0.2);
        border-radius: 20upx;
        box-sizing: border-box;
        padding: 30upx;
        font-size: 20upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
        line-height: 36upx;
        margin-top: 40upx;
        margin-bottom: 58upx;
    }
    
    .infoItem{
        display: flex;
        margin-bottom: 32upx;
        
        .infoLeft{
            width: 154upx;
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-right: 20upx;
        }
        
        .infoRight{
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }
    }
    
    .inner{
        box-sizing: border-box;
        padding: 60upx 108upx;
    }
    
    .inItem{
        display: flex;
        align-items: center;
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        margin-bottom: 40upx;
    }

    .select {
        width: 40upx;
        height: 40upx;
        border: 2upx solid #d2d2d7;
        border-radius: 8upx;
        margin-right: 20upx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .info {
        box-sizing: border-box;
        padding: 80upx 52upx 0;
        border-top: 2upx solid #d2d2d7;

        .in1 {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: rgba(29, 29, 31, 0.9);
        }

        .in2 {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
            margin-top: 40upx;
            margin-bottom: 60upx;
        }

        .in3 {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-bottom: 20upx;
        }
    }
</style>
