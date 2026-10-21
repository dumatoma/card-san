<template>
    <view>
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="レビューリンク" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#fbfbfd"></u-navbar>
        <!-- [カスタムエイド 2026-10-21] 見た目をこれまでの画面に合わせ、並び順を変えた。
             「Googleクチコミを管理」は設定ではなく別の機能なので、いちばん上の目立つ行に。
             その下が「クチコミ依頼メッセージの設定」:①レビュー用リンクURL(先に必要)→②ボタンのデザイン。決まっているボタンの画像は今までと同じ -->
        <view class="rv">
            <view class="rv-card rv-gbp" hover-class="rv-row-hover" @click="toGBPReviews">
                <image src="../../../static/svg/cs_gstar.svg" class="rv-g" mode="aspectFit"></image>
                <view class="rv-gbp-text">
                    <view class="rv-gbp-title">Googleクチコミを管理</view>
                    <view class="rv-sub">返信・統計の確認ができます</view>
                </view>
                <view class="rv-chev"></view>
            </view>

            <view class="rv-h3">クチコミ依頼メッセージの設定</view>
            <view class="rv-card rv-pad">
                <view class="rv-lab">① レビュー用リンクURL</view>
                <view class="rv-sub rv-mb">お店のGoogleビジネスプロフィールのレビュー用リンクURLを貼り付けてください</view>
                <textarea class="rv-ta" :class="{ on: fk }" v-model="des" placeholder="https://g.page/r/…/review" placeholder-class="rv-ph"
                    @focus="fk = true" @blur="fk = false"></textarea>
                <view class="rv-link" @click.stop="openURL">
                    <text>レビュー用リンクの作成方法</text>
                    <view class="rv-chev rv-chev-blue"></view>
                </view>
            </view>

            <view class="rv-card">
                <view class="rv-pad rv-pad-b0">
                    <view class="rv-lab">② ボタンのデザイン</view>
                    <view class="rv-sub">メッセージで送るボタンの見た目を選びます</view>
                </view>
                <view class="rv-opt" @click="current = 0">
                    <view class="rv-rad" :class="{ on: current == 0 }"><view class="rv-rad-in"></view></view>
                    <image class="rv-img" src="../../../static/images/review.svg" mode="aspectFit"></image>
                </view>
                <view class="rv-opt" @click="image ? (current = 1) : chooseImage()">
                    <view class="rv-rad" :class="{ on: current == 1 }"><view class="rv-rad-in"></view></view>
                    <image class="rv-img rv-own" v-if="image" :src="image" mode="aspectFit"></image>
                    <view class="rv-up" v-else hover-class="rv-row-hover">＋ 自分の画像を使う</view>
                </view>
                <view class="rv-change" v-if="image" @click.stop="chooseImage">画像を変える</view>
            </view>

            <view class="rv-save" hover-class="rv-save-hover" @click="saveData">保存する</view>
        </view>
    </view>
</template>

<script>
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
    import {reviewSetting,getShopInfo} from "@/api/index.js"
    export default {
        data() {
            return {
                image:"",
                current:0,
                des:"",
                fk: false // [カスタムエイド 2026-10-21] 入力中か
            };
        },
        onLoad() {
            let that = this 
            that.getShop()
        },
        methods:{
            getShop(){
                let that = this
                getShopInfo().then((res) => {
                    if(res.code == 200){
                        that.current = res.data.shop_info.comment_button_style * 1 - 1
                        that.image = res.data.shop_info.comment_button_style2_image
                        that.des = res.data.shop_info.comment_button_url
                    }
                }).catch(() => {})
            },
            toGBPReviews() {
                uni.navigateTo({ url: '/pages/message/gbpReviews/gbpReviews' })
            },
            openURL() {
                // #ifdef APP-PLUS
                if (plus.os.name == 'Android' || plus.os.name == 'iOS') {
                    // [カスタムエイド 2026-10-18] 古い記事(39169673962393)はリンク切れ。管理WEB reviewSetting.vue と同じ新しい記事へ
                    // [カスタムエイド 2026-10-19] Safari に移らず、アプリの中の「ヘルプ」画面で開く(上の「<」で戻れる)
                    uni.navigateTo({ url: '/pages/helpview/helpview?link=' + encodeURIComponent('https://cardsan.zendesk.com/hc/ja/articles/57035978592409-%E3%83%AC%E3%83%93%E3%83%A5%E3%83%BC%E3%83%AA%E3%83%B3%E3%82%AF%E3%81%AE%E4%BD%9C%E6%88%90-Google%E3%83%93%E3%82%B8%E3%83%8D%E3%82%B9%E3%83%97%E3%83%AD%E3%83%95%E3%82%A3%E3%83%BC%E3%83%AB') });
                    return
                }
                // #endif
            },
            saveData(){
                let that = this 
                let data = {}
                data.type = "comment_button"
                data.comment_button_style = that.current * 1 + 1
                data.comment_button_style2_image = that.image
                data.comment_button_url = that.des
                reviewSetting(data).then((res) => {
                    if(res.code == 200){
                        uni.showToast({
                            title:res.message
                        })
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                }).catch(() => {})
            },
            chooseImage() {
                let that = this
                uni.hideKeyboard()
                pickImage({
                    count: 1,
                    success: (res) => {
                        const tempPaths = res.tempFilePaths
                        uni.uploadFile({
                            url: that.$baseUrl + '/tool/upload/file',
                            filePath: res.tempFilePaths[0],
                            name: "file",
                            success: (result) => {
                                if (result.errMsg == "uploadFile:ok") {
                                    let re = JSON.parse(result.data)
                                    if (re.code == 200) {
                                        that.image = re.data.path
                                        that.current = 1 // [カスタムエイド 2026-10-21] 画像を入れたら、その画像のボタンを選んだ状態に
                                    } 
                                }
                            }
                        })
                    }
                });
            },
        }
    }
</script>

<style lang="scss">
    .gbpSection {
        margin: 40rpx 46rpx 0;
        background: #fff;
        border-radius: 16rpx;
        border: 2rpx solid #E8F0FE;
        overflow: hidden;
    }
    .gbpRow {
        display: flex;
        align-items: center;
        padding: 30rpx;
    }
    .gbpIcon {
        width: 56rpx;
        height: 56rpx;
        margin-right: 20rpx;
        flex-shrink: 0;
    }
    .gbpText { flex: 1; }
    .gbpTitle { font-size: 30rpx; color: #1D1D1F; margin-bottom: 6rpx; }
    .gbpSub { font-size: 24rpx; color: #86868B; }
    .gbpArrow { font-size: 36rpx; color: #ccc; }

    .inner{
        width: 100%;
        height: 100%;
        background: #1A73E8;
        border-radius: 50%;
    }
    .saveBtn{
        width: 480rpx;
        height: 96rpx;
        background: #1A73E8;
        border-radius: 20rpx;
        text-align: center;
        line-height: 96rpx;
        font-weight: bold;
        font-size: 32rpx;
        color: #FFFFFF;
        margin: 0 auto;
        margin-top: 100rpx;
    }
    .content{
        font-family: Hiragino, Hiragino Sans !important;
        border-top: 2rpx solid #d2d2d7;
        
        .secondPart{
            box-sizing: border-box;
            padding: 80rpx 46rpx;
            
            .fptitle{
                font-size: 32rpx;
                color: rgba(29,29,31,0.9);
                margin-bottom: 58rpx;
            }
            
            .spContent{
                width: 100%;
                height: 160rpx;
                border-radius: 20rpx;
                border: 2rpx solid #D2D2D7;
                box-sizing: border-box;
                padding: 20rpx;
                
                textarea{
                    width: 100%;
                    height: 100%;
                    display: block;
                }
            }
            
            .rule{
                font-size: 28rpx;
                color: #1A73E8;
                margin-top: 20rpx;
            }
        }
        
        .firstPart{
            box-sizing: border-box;
            padding: 80rpx 46rpx;
            border-bottom: 2rpx solid #d2d2d7;
            
            .fptitle{
                font-size: 32rpx;
                color: rgba(29,29,31,0.9);
                margin-bottom: 58rpx;
            }
            
            .reviewItem{
                display: flex;
                align-items: center;
                
                .reviewLeft{
                    width: 64rpx;
                    display: flex;
                    align-items: center;
                    
                    .checkBox{
                        width: 32rpx;
                        height: 32rpx;
                        background: #FFFFFF;
                        border: 2rpx solid #AAAAAA;
                        box-sizing: border-box;
                        padding: 6rpx;
                        border-radius: 50%;
                    }
                }
                
                .reviewRight{
                    width: 492rpx;
                    height: 120rpx;
                    border-radius: 12rpx 12rpx 12rpx 12rpx;
                    .btn{
                        width: 100%;
                        height: 96rpx;
                        background: #F5F5F7;
                        border-radius: 12rpx 12rpx 12rpx 12rpx;
                        border: 2rpx solid #707070;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    
                    .imageContent{
                        width: 100%;
                        height: 100%;
                        border-radius: 20rpx;
                        border: 2rpx solid #AAAAAA;
                        overflow: hidden;
                    }
                    
                    image{
                        width: 492rpx;
                        height: 120rpx;
                        border-radius: 15rpx;
                    }
                }
                
            }
        }
    }

    /* [カスタムエイド 2026-10-21] これまでの画面と同じ見た目 */
    .rv {
        min-height: 100vh;
        background: #F5F5F7;
        padding: 32rpx 24rpx calc(60rpx + env(safe-area-inset-bottom));
        box-sizing: border-box;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .rv-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        overflow: hidden;
        margin-bottom: 32rpx;
    }

    .rv-pad {
        padding: 32rpx;
    }

    .rv-pad-b0 {
        padding-bottom: 8rpx;
    }

    .rv-gbp {
        display: flex;
        align-items: center;
        padding: 28rpx 32rpx;
        margin-bottom: 44rpx;
    }

    .rv-g {
        width: 64rpx;
        height: 64rpx;
        flex-shrink: 0;
        margin-right: 24rpx;
    }

    .rv-gbp-text {
        flex: 1;
        min-width: 0;
    }

    .rv-gbp-title {
        font-size: 30rpx;
        font-family: 'Hiragino Sans-W5', sans-serif;
    }

    .rv-sub {
        font-size: 24rpx;
        line-height: 1.6;
        color: #6E6E73;
    }

    .rv-mb {
        margin-bottom: 16rpx;
    }

    .rv-row-hover {
        opacity: 0.7;
    }

    /* 右向きの矢印 */
    .rv-chev {
        width: 14rpx;
        height: 14rpx;
        flex-shrink: 0;
        margin-left: 16rpx;
        border-right: 3rpx solid #C7C7CC;
        border-bottom: 3rpx solid #C7C7CC;
        transform: rotate(-45deg);
    }

    .rv-chev-blue {
        border-color: #1A73E8;
    }

    .rv-h3 {
        margin: 0 8rpx 16rpx;
        font-size: 28rpx;
        font-family: 'Hiragino Sans-W5', sans-serif;
        color: #515154;
    }

    .rv-lab {
        font-size: 28rpx;
        font-family: 'Hiragino Sans-W5', sans-serif;
        margin-bottom: 6rpx;
    }

    .rv-ta {
        width: 100%;
        height: 140rpx;
        padding: 20rpx 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        font-size: 28rpx;
        line-height: 1.5;
        box-sizing: border-box;
    }

    .rv-ta.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    .rv-ph {
        color: #8A8A8E;
    }

    .rv-link {
        display: flex;
        align-items: center;
        margin-top: 20rpx;
        font-size: 26rpx;
        color: #1A73E8;
    }

    .rv-opt {
        display: flex;
        align-items: center;
        padding: 24rpx 32rpx;
        border-top: 1px solid #E5E5EA;
    }

    .rv-rad {
        width: 44rpx;
        height: 44rpx;
        flex-shrink: 0;
        margin-right: 24rpx;
        border: 2px solid #C7C7CC;
        border-radius: 50%;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .rv-rad-in {
        width: 20rpx;
        height: 20rpx;
        border-radius: 50%;
    }

    .rv-rad.on {
        border-color: #1A73E8;

        .rv-rad-in {
            background: #1A73E8;
        }
    }

    /* 決まっているボタンの画像(今までと同じ大きさの割合) */
    .rv-img {
        flex: 1;
        height: 120rpx;
    }

    .rv-own {
        border-radius: 12rpx;
    }

    .rv-up {
        flex: 1;
        height: 120rpx;
        line-height: 116rpx;
        border: 2px dashed #8AB0EE;
        border-radius: 16rpx;
        background: #F7FAFF;
        color: #1A73E8;
        text-align: center;
        font-size: 26rpx;
        box-sizing: border-box;
    }

    .rv-change {
        padding: 0 32rpx 24rpx 100rpx;
        font-size: 24rpx;
        color: #1A73E8;
    }

    .rv-save {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-family: 'Hiragino Sans-W5', sans-serif;
    }

    .rv-save-hover {
        opacity: 0.8;
    }
</style>
