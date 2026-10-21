<template>
    <!-- [カスタムエイド 2026-10-20] 副管理者の権限(登録2/2・編集)の見た目を店舗情報などと合わせた
         ・名前・電話番号の入力欄を共通の枠に、権限は「運営」「各種設定」ごとの白いまとまりに
         ・各権限は行のどこを押しても切り替わる。チェックした項目は黒い文字、外した項目は薄い文字
         ・まとまりの右上の「すべて選ぶ/すべて外す」で一度に切り替え。何個選んでいるかも表示。保存ボタンを共通の形に -->
    <view class="ad">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar :title="query.type == 'add' ? '副管理者の登録' : '副管理者の編集'" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="ad-steps" v-if="query.type == 'add'">
            <view class="ad-step done"><view class="bar"></view><text>1 名前と電話番号</text></view>
            <view class="ad-step now"><view class="bar"></view><text>2 権限</text></view>
        </view>
        <view class="ad-body">
            <view class="ad-card" v-if="query.type == 'add'">
                <view class="ad-kv"><text class="k">店舗名</text><text class="v">{{shopname}}</text></view>
                <view class="ad-kv"><text class="k">副管理者名</text><text class="v">{{query.name}}</text></view>
                <view class="ad-kv"><text class="k">電話番号</text><text class="v">{{query.phone}}</text></view>
            </view>
            <view class="ad-card" v-else>
                <view class="ad-kv ad-kv-top"><text class="k">店舗名</text><text class="v">{{shopname}}</text></view>
                <view class="ad-field">
                    <view class="ad-label"><text class="ad-req">※</text>副管理者名</view>
                    <input class="ad-in" :class="{ on: fk == 'name' }" v-model="name" maxlength="-1" :cursor-spacing="120"
                        placeholder-class="ad-ph" @focus="fk = 'name'" @blur="fk = ''" />
                </view>
                <view class="ad-field">
                    <view class="ad-label"><text class="ad-req">※</text>電話番号(SMS認証)</view>
                    <input class="ad-in" :class="{ on: fk == 'phone' }" v-model="phone" type="number" maxlength="-1" :cursor-spacing="120"
                        placeholder-class="ad-ph" @focus="fk = 'phone'" @blur="fk = ''" />
                </view>
            </view>

            <view class="ad-info">副管理者に使わせる機能を選んでください。ふだんの利用には「運営」「各種設定」をすべて選ぶのがおすすめです。</view>

            <view class="ad-card ad-perm" v-for="(item,index) in array" :key="index">
                <view class="ad-perm-head">
                    <view class="ad-perm-title">
                        <text class="ad-perm-name">{{item.name}}</text>
                        <text class="ad-perm-count">{{countOf(item)}} / {{item.jus.length}}</text>
                    </view>
                    <view class="ad-all" :class="{ on: item.checked }" hover-class="ad-hover" @click="selectAll(index)">
                        {{item.checked ? 'すべて外す' : 'すべて選ぶ'}}
                    </view>
                </view>
                <view class="ad-perm-row" v-for="(ite,idx) in item.jus" :key="idx" hover-class="ad-row-hover" @click="selectone(index,idx)">
                    <view class="ad-check" :class="{ on: ite.checked }"><view class="ad-check-mark" v-if="ite.checked"></view></view>
                    <text class="ad-perm-label" :class="{ on: ite.checked }">{{ite.name}}</text>
                </view>
            </view>

            <view class="ad-btn" hover-class="ad-btn-hover" @click="save">保存する</view>
        </view>
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
                fk: '', // [カスタムエイド 2026-10-20]
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
                            },
                            {
                                name: "クチコミ管理",
                                checked: false,
                                val:14
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
                            },
                            {
                                name: "Googleビジネス連携",
                                checked: false,
                                val:15
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
            // [カスタムエイド 2026-10-20] 登録の1/2から送る名前・電話番号を元の文字に戻す
            const dec = (v) => {
                try {
                    return decodeURIComponent(v || '')
                } catch (e) {
                    return v || ''
                }
            }
            if (options.name) options.name = dec(options.name)
            if (options.phone) options.phone = dec(options.phone)
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
            
            // [カスタムエイド 2026-10-20] 全部チェックされているかで「すべて」の状態を決める(今までは途中で止めるために、わざとエラーを出していた)
            checkSeleceAll(index,idx){
                let that = this
                that.array[index].checked = that.array[index].jus.every((v) => v.checked)
            },
            countOf(item) {
                return item.jus.filter((v) => v.checked).length
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
                        if(res.code != 200) uni.showToast({ title: res.message, icon: 'none' }) // [カスタムエイド 2026-10-20]
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
                    data.name = that.name
                    data.phone = that.phone
                    data.privileges = temp
                    eidtAdmin(data,that.query.id).then((res) => {
                        if(res.code != 200) uni.showToast({ title: res.message, icon: 'none' }) // [カスタムエイド 2026-10-20]
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

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-20] 店舗情報などと同じ決まり */
    .ad { min-height: 100vh; background: #F5F5F7; font-family: 'Hiragino Sans', sans-serif; color: #1D1D1F; }
    .ad-body { padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom)); }
    .ad-btn { height: 104rpx; line-height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; text-align: center; font-size: 32rpx; font-weight: 700; }
    .ad-btn.off { background: #C6DAFC; }
    .ad-btn-hover { opacity: 0.8; }
    .ad-hover { opacity: 0.6; }
    .ad-sub { display: block; font-size: 24rpx; line-height: 1.6; color: #6E6E73; }
    .ad-info { padding: 24rpx 28rpx; border-radius: 24rpx; background: #EEF4FF; font-size: 28rpx; line-height: 1.6; margin-bottom: 32rpx; }
    .ad-card { background: #FFFFFF; border-radius: 32rpx; padding: 32rpx; margin-bottom: 32rpx; }
    .ad-h3 { margin: 0 8rpx 16rpx; font-size: 30rpx; font-weight: 700; color: #515154; }
    .ad-field { margin-bottom: 28rpx; }
    .ad-field:last-child { margin-bottom: 0; }
    .ad-label { font-size: 26rpx; font-weight: 500; color: #1D1D1F; margin-bottom: 12rpx; }
    .ad-req { color: #C5221F; }
    .ad-in { width: 100%; box-sizing: border-box; height: 88rpx; padding: 0 28rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; background: #FFFFFF; font-size: 30rpx; color: #1D1D1F; transition: border-color .15s, box-shadow .15s; }
    .ad-in.on { border-color: #1A73E8; box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16); }
    .ad-ph { color: #8A8A8E; }
    .ad-steps { display: flex; background: #FFFFFF; padding: 24rpx 32rpx 28rpx; border-bottom: 1px solid #E5E5EA; }
    .ad-step { flex: 1; margin-right: 12rpx; font-size: 24rpx; color: #6E6E73; }
    .ad-step:last-child { margin-right: 0; }
    .ad-step .bar { height: 8rpx; border-radius: 4rpx; background: #E5E5EA; margin-bottom: 12rpx; }
    .ad-step.done, .ad-step.now { color: #1A73E8; }
    .ad-step.done .bar, .ad-step.now .bar { background: #1A73E8; }
    .ad-step.now { font-weight: 700; }
    .ad-kv { display: flex; align-items: baseline; padding: 8rpx 0; font-size: 28rpx; }
    .ad-kv .k { width: 180rpx; flex-shrink: 0; color: #6E6E73; font-size: 26rpx; }
    .ad-kv .v { flex: 1; min-width: 0; font-weight: 500; }
    .ad-kv-top { margin-bottom: 20rpx; }
    .ad-perm { padding: 8rpx 32rpx 16rpx; }
    .ad-perm-head { display: flex; align-items: center; justify-content: space-between; min-height: 104rpx; border-bottom: 1px solid #E5E5EA; }
    .ad-perm-title { display: flex; align-items: baseline; }
    .ad-perm-name { font-size: 32rpx; font-weight: 700; margin-right: 16rpx; }
    .ad-perm-count { font-size: 26rpx; color: #515154; }
    .ad-all { height: 60rpx; line-height: 58rpx; padding: 0 24rpx; border-radius: 30rpx; border: 1px solid #1A73E8; color: #1A73E8; font-size: 24rpx; font-weight: 500; box-sizing: border-box; }
    .ad-all.on { border-color: #D2D2D7; color: #515154; }
    .ad-perm-row { display: flex; align-items: center; min-height: 92rpx; }
    .ad-row-hover { background: #F5F5F7; }
    .ad-check { width: 44rpx; height: 44rpx; flex-shrink: 0; margin-right: 24rpx; border-radius: 12rpx; border: 3rpx solid #AEAEB2; box-sizing: border-box; background: #FFFFFF; display: flex; align-items: center; justify-content: center; }
    .ad-check.on { border: 0; background: #1A73E8; }
    .ad-check-mark { width: 12rpx; height: 22rpx; border-right: 5rpx solid #FFFFFF; border-bottom: 5rpx solid #FFFFFF; transform: rotate(45deg); margin-top: -6rpx; }
    .ad-perm-label { font-size: 30rpx; color: #8A8A8E; }
    .ad-perm-label.on { color: #1D1D1F; font-weight: 500; }
</style>

