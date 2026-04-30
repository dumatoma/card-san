<template>
    <view>
        <view class="info" v-if="query.type == 'add'">
            <view class="in1">
                副管理者に付与する権限を選択して下さい。
            </view>
            <view class="in2">
                通常利用には「運営」「各種設定」の選択をおすすめします。
            </view>
            <view class="in3">
                店舗名：{{shopname}}
            </view>
            <view class="in3" v-text="`副管理者名：${query.name}`">
            </view>
            <view class="in3" v-text="`電話番号：${query.phone}`"></view>
        </view>
        
        <view class="info" v-else>
            <view class="in1">
               店舗名：{{shopname}}
            </view>
            <view class="in4">
                <view class="iname">
                    副管理者名
                </view>
                <u-input v-model="name"></u-input>
            </view>
            <view class="in4">
                <view class="iname">
                    電話番号(SMS認証)
                </view>
                <u-input v-model="phone"></u-input>
            </view>
        </view>

        <view class="content">
            <view class="juItem" v-for="(item,index) in array">
                <view class="title">
                    <view class="selectContents" @click="selectAll(index)">
                        <view class="unselect" v-if="!item.checked"></view>
                        <view class="selected" v-if="item.checked">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view v-text="item.name"></view>
                </view>
                <view class="inner">
                    <view class="inItem" v-for="(ite,idx) in item.jus">
                        <view class="select" @click="selectone(index,idx)">
                            <image v-if="ite.checked" src="../../../../static/icons/select.png" mode=""></image>
                        </view>
                        <view class="inName" v-text="ite.name"></view>
                    </view>
                </view>
            </view>
        </view>
        <u-button text="保存" class="nextStep" @click="save"></u-button>
        <view style="height: 200upx;"></view>
    </view>
</template>

<script>
    import {addAdmin,getAdminDetail,eidtAdmin,getShopInfo} from "@/api/index.js"
    
    export default {
        data() {
            return {
                type:"",
                name:"",
                phone:"",
                query:{},
                shopname:"",
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
        created() {
            getShopInfo().then((res) => {
                if(res.code == 200){
                    this.shopname = res.data.shop_info.shop_name
                }
            })
        },
        onLoad(options) {
            let that = this
            that.query = options
            if(that.query.type == "edit"){
                getAdminDetail(that.query.id).then((res) => {
                    if(res.code == 200){
                        that.name = res.data.admin.name
                        that.phone = res.data.admin.phone
                        that.shopname = res.data.admin.shop.shop_name
                        that.array.forEach((value,index) => {
                            value.checked = true
                            value.jus.forEach((val,idx) => {
                                if(res.data.admin.privileges.indexOf(val.val+1) != -1){
                                    val.checked = true
                                }
                                
                                if(val.checked == false){
                                    value.checked = false
                                }
                            })
                        })
                    }
                })
            }else{
                
            }
        },
        methods:{
            selectAll(index){
                let that = this
                that.array[index].checked = !that.array[index].checked
                that.array[index].jus.forEach((value,idx) => {
                    value.checked = that.array[index].checked
                })
            },
            selectone(index,idx){
                let that = this
                that.array[index].jus[idx].checked = !that.array[index].jus[idx].checked
                that.checkSeleceAll(index,idx)
                
            },
            
            checkSeleceAll(index,idx){
                let that = this
                that.array[index].jus.forEach((val,ind) => {
                    if(val.checked == false){
                         that.array[index].checked = false
                         throw Error();
                    }else{
                        that.array[index].checked = true
                    }
                })
            },
            
            save(){
                let that = this
                let temp = []
                let array = that.array
                array.forEach((value,index) => {
                    value.jus.forEach((val,idx) => {
                        if(val.checked == true){
                            temp.push(val.val * 1 + 1)
                        }
                    })
                })
                if(that.query.type == "add"){
                    let data = {}
                    data.type = 2
                    data.name = that.query.name 
                    data.phone = that.query.phone
                    data.privileges = temp
                    addAdmin(data).then((res) =>{
                        if(res.code == 200){
                            uni.showToast({
                                title:res.message
                            })
                            setTimeout(function(){
                               uni.redirectTo({
                                   url:"../Administrators"
                               }) 
                            },2000)
                        }
                    })
                }else if(that.query.type == "edit"){
                    let that = this
                    let data = {}
                    data._method = "put"
                    data.name = that.name 
                    data.phone = that.phone
                    data.privileges = temp
                    eidtAdmin(data,that.query.id).then((res) => {
                        if(res.code == 200){
                            uni.showToast({
                                title:res.message
                            })
                            setTimeout(function(){
                               uni.redirectTo({
                                   url:"../Administrators"
                               }) 
                            },2000)
                        }
                    })
                }
            }
        }
    }
</script>

<style lang="scss">
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
    
    .nextStep{
        width: 480upx !important;
        height: 96upx !important;
        background: rgb(26,115,232) !important;
        border-radius: 48upx !important;
        color: #fff !important;
        margin-top:  0upx;
    }
    
    .in4{
        margin-top: 82upx;
        
        .iname{
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #86868B;
            margin-bottom: 10upx;
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
        padding: 80upx 52upx;
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
