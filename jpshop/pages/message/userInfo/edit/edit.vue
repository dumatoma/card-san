<template>
    <!-- [カスタムエイド 2026-10-21] 自動メッセージの編集画面を作り直し。
         白い枠=編集できる欄、薄い青の箱(青い文字)=予約ごとに自動で入る部分。枠と枠のあいだは、実際に送られる文の「1行あき」と同じ。
         上に説明、下に「送られる文章を確認」(アプリのメッセージにも SMS にも同じ文章が届く。2026-10-21 見本2で1つにまとめた)。保存の形(配列の並び)・API は今までと同じ -->
    <view class="am">
        <u-navbar :title="navTitle" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="am-pg">
            <view class="am-when">{{ whenText }}</view>
            <view class="am-note">
                <view class="am-ic">i</view>
                <view><text class="am-b">青い文字</text>は、予約ごとに自動で入る部分なので編集できません。<text class="am-b">白い枠の中</text>だけ編集できます。枠と枠のあいだは、1行あけて送られます。</view>
            </view>

            <view class="am-card">
                <block v-if="type != 4">
                    <view class="am-lbl">見出し</view>
                    <input class="am-in" :class="{ on: fk == 't' }" v-model="eTitle" placeholder="例:【予約受付のお知らせ】" placeholder-class="am-ph" @focus="fk = 't'" @blur="fk = ''" />
                    <view class="am-gap"></view>
                </block>

                <view class="am-lbl">お名前のあと</view>
                <view class="am-name">
                    <view class="am-chip">お客様の名前</view>
                    <input class="am-in am-flex" :class="{ on: fk == 'n' }" v-model="name" placeholder="例:様" placeholder-class="am-ph" @focus="fk = 'n'" @blur="fk = ''" />
                </view>
                <view class="am-gap"></view>

                <view class="am-lbl">本文(上)</view>
                <textarea class="am-ta" :class="{ on: fk == 'a' }" v-model="edit1" auto-height maxlength="-1" :show-confirm-bar="false" placeholder="空にすると、この部分は送られません" placeholder-class="am-ph" @focus="fk = 'a'" @blur="fk = ''"></textarea>
                <view class="am-gap"></view>

                <block v-if="type != 4">
                    <view class="am-fix"><text class="am-tag">自動で入ります</text>
                        <view>予約番号：予約ごとの番号</view>
                        <view>日時：予約の日時</view>
                        <view>メニュー：予約したメニュー</view>
                        <view>価格：メニューの価格</view>
                        <view>担当者：担当者の名前</view>
                        <view>指名料：指名料(あるときだけ)</view>
                    </view>
                    <view class="am-gap"></view>
                </block>

                <view class="am-lbl">本文(下)</view>
                <textarea v-if="type == 5" class="am-ta" :class="{ on: fk == 'b' }" v-model="edit4" auto-height maxlength="-1" :show-confirm-bar="false" placeholder="空にすると、この部分は送られません" placeholder-class="am-ph" @focus="fk = 'b'" @blur="fk = ''"></textarea>
                <textarea v-else class="am-ta" :class="{ on: fk == 'b' }" v-model="edit2" auto-height maxlength="-1" :show-confirm-bar="false" placeholder="空にすると、この部分は送られません" placeholder-class="am-ph" @focus="fk = 'b'" @blur="fk = ''"></textarea>
                <view class="am-gap"></view>

                <view class="am-fix"><text class="am-tag">自動で入ります</text>
                    <view>店舗名：{{ shop.shop_name || 'お店の名前' }}</view>
                    <view>電話：{{ shop.phone || 'お店の電話番号' }}</view>
                    <view>{{ shopAddr || 'お店の住所' }}</view>
                </view>
            </view>

            <view class="am-card am-rv" v-if="type == 4">
                <view class="am-rv-row">
                    <view class="am-rv-t">Googleの口コミボタンを付ける</view>
                    <u-switch :value="current == 1" activeColor="#34C759" @change="changeSel"></u-switch>
                </view>
                <view class="am-small">アプリのメッセージに、続けて口コミボタンが届きます。</view>
            </view>

            <view class="am-small am-pad">空にした枠は、送られる文から省かれます。</view>
            <view class="am-btn2" hover-class="am-hover" @click="showPrev = true">送られる文章を確認</view>
            <view class="am-btn" hover-class="am-hover" @click="save">保存する</view>
        </view>

        <u-popup :show="showPrev" mode="bottom" round="24" @close="showPrev = false">
            <view class="am-sheet">
                <view class="am-grip"></view>
                <view class="am-shx" hover-class="am-hover" @click="showPrev = false">×</view>
                <view class="am-sh-t">送られる文章(見本)</view>
                <scroll-view scroll-y class="am-bub-wrap">
                    <view class="am-bub">{{ previewText }}</view>
                    <!-- [カスタムエイド 2026-10-21] 口コミボタン(アプリのメッセージではボタン、SMS ではリンク) -->
                    <block v-if="type == 4 && current == 1">
                        <view class="am-rv-l">続けて口コミボタンが届きます</view>
                        <image v-if="shop.comment_button_style_image" class="am-rv" :src="shop.comment_button_style_image" mode="aspectFill"></image>
                        <view v-else class="am-rv am-rv-no">Googleの口コミを書く</view>
                    </block>
                </scroll-view>
                <view class="am-small">{{ type == 4 ? 'サンキューメッセージは、アプリのメッセージだけに届きます(SMS では送りません)。お名前は見本です。' : 'アプリのメッセージにも SMS にも、この文章が届きます。お名前・予約の内容は見本です。' }}</view>
                <view class="am-close" hover-class="am-hover" @click="showPrev = false">閉じる</view>
            </view>
        </u-popup>
    </view>
</template>
<script>
    import {
        getShopInfo,
        changeChatInfo
    } from '@/api/index.js'
    export default {
        data() {
            return {
                navTitle: '予約確認', // [カスタムエイド 2026-10-20] 見出しの文字

                eTitle: "【予約受付のお知らせ】",
                name: "様",
                edit1: "ご予約ありがとうございます。\n以下の通りご予約承りました。",
                edit2: "キャンセルや来店時刻に遅れる場合は、必ずご連絡頂きますようお願いいたします。",
                type: 1,
                edit4:"",
                current: 0,
                // [カスタムエイド 2026-10-21]
                fk: '',
                shop: {},
                showPrev: false
            }
        },
        computed: {
            // [カスタムエイド 2026-10-21] いつ送られるか
            whenText() {
                return ({ 1: '予約が確定したときに送られます', 2: '予約日時の24時間前に送られます', 3: '予約を取り消したときに送られます', 4: 'ご来店日時の24時間後に送られます', 5: '予約の内容を変更したときに送られます' })[this.type] || ''
            },
            shopAddr() {
                return (this.shop.address1 || '') + (this.shop.address2 || '')
            },
            // [カスタムエイド 2026-10-21] 送られる文の見本。サーバー(app/Libs/CsSmsText.php)と同じ並び:見出し → お名前+敬称 → 本文(上) → 予約の内容 → 本文(下) → 店舗情報
            previewText() {
                const t = (v) => (v == null ? '' : String(v)).replace(/\r\n?/g, '\n').trim()
                const nm = '山田 花子'
                const b = []
                if (this.type == 4) {
                    b.push((nm + ' ' + t(this.name)).trim(), t(this.edit1), t(this.edit2))
                } else {
                    b.push(t(this.eTitle), (nm + ' ' + t(this.name)).trim(), t(this.edit1))
                    if (this.type == 5) b.push(t(this.edit2))
                    b.push('予約番号：12345\n日時：10月22日(木) 10:00 ~ 11:00\nメニュー：カット\n価格：5,000円〜\n担当者：佐藤')
                    b.push(t(this.type == 5 ? this.edit4 : this.edit2))
                }
                let text = b.filter((v) => v !== '').join('\n\n')
                {
                    const info = [this.shop.shop_name ? '店舗名：' + this.shop.shop_name : '', this.shop.phone ? '電話：' + this.shop.phone : '', this.shopAddr].filter((v) => v).join('\n')
                    if (info) text += '\n\n' + info
                }
                return text
            }
        },
        onLoad(options) {
            let that = this
            let query = options
            that.type = query.type
            if (query.type == 1) {
                that.eTitle = '【予約受付のお知らせ】'
                that.edit1 = 'ご予約ありがとうございます。\n以下の通りご予約承りました。' // [カスタムエイド 2026-10-21] 空白ではなく改行(サーバーの初期の文と同じ)
                that.edit2 = 'キャンセルや来店時刻に遅れる場合は、必ずご連絡頂きますようお願いいたします。'
                this.navTitle = "予約確認" // [カスタムエイド 2026-10-20]
            }
            
            if (query.type == 2) {
                that.eTitle = '【予約確認のお知らせ】'
                that.edit1 = '明日ご来店日となりますので、お知らせいたします。'
                that.edit2 = 'スタッフ一同、心よりご来店お待ちしております。'
                this.navTitle = "予約リマインダー" // [カスタムエイド 2026-10-20]
            }
            
            if (query.type == 3) {
                that.eTitle = '【予約キャンセルのお知らせ】'
                that.edit1 = '以下内容のご予約のキャンセルを受理いたしました。'
                that.edit2 = '内容のご確認をお願いいたします。'
                this.navTitle = "予約キャンセル" // [カスタムエイド 2026-10-20]
            }
            
            if (query.type == 4) {
                // [カスタムエイド 2026-10-21] 初期の文をサーバーと同じ分け方に(本文(上)・本文(下))
                that.edit1 = 'この度は当店をご利用頂き、誠にありがとうございました。'
                that.edit2 = '今後も当店をよろしくお願い致します。'
                this.navTitle = "サンキューメッセージ" // [カスタムエイド 2026-10-20]
               
            }
            
            if(query.type == 5){
                that.eTitle = '【予約内容変更のお知らせ】'
                that.edit1 = 'ご予約の内容が変更になりましたのでお知らせします。\n\n変更内容のご確認お願いします。'
                that.edit2 = ''
                that.edit4 = "キャンセルや来店時刻に遅れる場合は、直接店舗にご連絡頂きますようお願いいたします。" // [カスタムエイド 2026-10-21] 途中の空白をなくした
                this.navTitle = "予約内容変更" // [カスタムエイド 2026-10-20]
            }
            
            that.getInfo()
        },
        methods: {
            changeSel(){
                let that = this 
                if(that.current == 0){
                    that.current = 1
                }else{
                    that.current = 0
                }
            },
            getInfo() {
                let that = this
                getShopInfo().then((res) => {
                    if(res.code == 200){
                        that.shop = res.data.shop_info || {} // [カスタムエイド 2026-10-21] 店舗名・電話・住所の表示用
                        if(that.type == 4){
                            if(res.data.shop_info.thanks_message.length > 0){
                                that.name = res.data.shop_info.thanks_message[0]
                                that.edit1 = res.data.shop_info.thanks_message[1]
                                that.edit2 = res.data.shop_info.thanks_message[2]
                                that.current = res.data.shop_info.thanks_message[3] || 0
                            }
                        }else if(that.type == 1){
                            if(res.data.shop_info.order_confirm_message.length > 0){
                                that.eTitle = res.data.shop_info.order_confirm_message[0]
                                that.name = res.data.shop_info.order_confirm_message[1]
                                that.edit1 = res.data.shop_info.order_confirm_message[2]
                                that.edit2 = res.data.shop_info.order_confirm_message[3]
                            }
                        }else if(that.type == 2){
                            if(res.data.shop_info.order_notice_message.length > 0){
                                that.eTitle = res.data.shop_info.order_notice_message[0]
                                that.name = res.data.shop_info.order_notice_message[1]
                                that.edit1 = res.data.shop_info.order_notice_message[2]
                                that.edit2 = res.data.shop_info.order_notice_message[3]
                            }
                        }else if(that.type == 3){
                            if(res.data.shop_info.order_cancel_message.length > 0){
                                that.eTitle = res.data.shop_info.order_cancel_message[0]
                                that.name = res.data.shop_info.order_cancel_message[1]
                                that.edit1 = res.data.shop_info.order_cancel_message[2]
                                that.edit2 = res.data.shop_info.order_cancel_message[3]
                            }
                        }else {
                            if(res.data.shop_info.order_update_message.length > 0){
                                that.eTitle = res.data.shop_info.order_update_message[0]
                                that.name = res.data.shop_info.order_update_message[1]
                                that.edit1 = res.data.shop_info.order_update_message[2]
                                that.edit2 = res.data.shop_info.order_update_message[3]
                                that.edit4 = res.data.shop_info.order_update_message[4]
                                // [カスタムエイド 2026-10-21] 以前の画面で隠れていた欄(本文(上)と予約の内容のあいだ)に文があれば、本文(上)にまとめる
                                if (that.edit2) { that.edit1 = [that.edit1, that.edit2].filter((v) => v).join('\n\n'); that.edit2 = '' }
                            }
                        }
                    }
                }).catch(() => {})
            },
            save(){
                let that = this
                let temp = []
                let data = {}
                if(that.type != 4){
                    temp.push(that.eTitle)
                }
                temp.push(that.name)
                temp.push(that.edit1)
                temp.push(that.edit2)
                temp.push(that.edit4)
                if(that.type == 4){
                    temp[3] = that.current 
                }
                
                data.type = 'message'
                data.field = that.type == 1? 'order_confirm_message' : that.type == 2? 'order_notice_message' : that.type == 3? 'order_cancel_message' : that.type == 4?'thanks_message' : 'order_update_message'
                data.order_confirm_message = temp
                data.order_notice_message = temp
                data.order_cancel_message = temp
                data.thanks_message = temp
                data.order_update_message = temp
                changeChatInfo(data).then((res) => {
                    if(res.code == 200){
                        uni.showToast({
                            title:res.message,
                            duration:2000
                        })

                        setTimeout(() => {
                            uni.navigateBack()
                        },2000)
                    }else{
                       uni.showToast({
                           title:res.message,
                           duration:2000,
                           icon:"none"
                       })
                    }
                }).catch(() => {})
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-21] 自動メッセージの編集画面(見本2:余白は Apple・Google の設定画面くらい) */
    page { background: #F2F2F7; }
    .am-pg { padding: 40rpx 32rpx calc(96rpx + env(safe-area-inset-bottom)); }
    .am-when { font-size: 26rpx; color: #6E6E73; margin: 0 8rpx 24rpx; line-height: 1.5; }
    .am-note { display: flex; background: #E8F0FE; border-radius: 28rpx; padding: 28rpx 32rpx; font-size: 26rpx; line-height: 1.7; color: #1D1D1F; margin-bottom: 48rpx; }
    .am-ic { flex: 0 0 36rpx; width: 36rpx; height: 36rpx; border-radius: 50%; background: #1A73E8; color: #FFFFFF; font-size: 24rpx; line-height: 36rpx; text-align: center; margin: 6rpx 20rpx 0 0; }
    .am-b { color: #1A73E8; font-family: 'Hiragino Sans-W5', sans-serif; }
    .am-card { background: #FFFFFF; border-radius: 32rpx; padding: 40rpx 32rpx; }
    /* [カスタムエイド 2026-10-21] 見出しの文字と入力枠の見た目のすき間を約6pxに(行の高さを詰め、余白 4px) */
    .am-lbl { font-size: 25rpx; color: #6E6E73; margin: 0 0 8rpx 4rpx; line-height: 1.2; }
    .am-in { height: 96rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 0 28rpx; font-size: 30rpx; background: #FFFFFF; box-sizing: border-box; }
    .am-ta { width: 100%; min-height: 140rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 24rpx 28rpx; font-size: 30rpx; line-height: 1.65; box-sizing: border-box; display: block; }
    .am-in.on, .am-ta.on { border-color: #1A73E8; box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.18); }
    .am-ph { color: #AEAEB2; }
    .am-name { display: flex; align-items: center; }
    .am-chip { background: #F2F6FD; color: #1A73E8; border-radius: 24rpx; height: 96rpx; line-height: 96rpx; padding: 0 28rpx; font-size: 30rpx; margin-right: 20rpx; white-space: nowrap; }
    .am-flex { flex: 1; }
    .am-gap { height: 48rpx; position: relative; }
    .am-gap::after { content: ''; position: absolute; left: 28rpx; right: 28rpx; top: 24rpx; border-top: 1px dashed #E5E5EA; }
    .am-fix { position: relative; background: #F2F6FD; border-radius: 24rpx; padding: 24rpx 28rpx; font-size: 28rpx; line-height: 1.8; color: #1A73E8; }
    .am-tag { position: absolute; right: 20rpx; top: 20rpx; font-size: 22rpx; color: #5F7FB0; background: #FFFFFF; border-radius: 999rpx; padding: 2rpx 16rpx; line-height: 1.6; }
    .am-rv { margin-top: 32rpx; padding: 32rpx; }
    .am-rv-row { display: flex; align-items: center; justify-content: space-between; }
    .am-rv-t { font-size: 30rpx; color: #1D1D1F; }
    .am-small { font-size: 24rpx; color: #8A8A8E; line-height: 1.6; margin-top: 12rpx; }
    .am-pad { margin: 20rpx 8rpx 0; }
    .am-btn2 { margin-top: 64rpx; height: 104rpx; border-radius: 28rpx; background: #FFFFFF; color: #1A73E8; font-size: 32rpx; display: flex; align-items: center; justify-content: center; box-sizing: border-box; }
    .am-btn { margin-top: 32rpx; height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif; display: flex; align-items: center; justify-content: center; }
    .am-hover { opacity: 0.7; }
    .am-sheet { position: relative; padding: 24rpx 40rpx calc(48rpx + env(safe-area-inset-bottom)); }
    .am-shx { position: absolute; right: 24rpx; top: 20rpx; width: 64rpx; height: 64rpx; border-radius: 50%; background: #F2F2F7; color: #6E6E73; font-size: 36rpx; line-height: 64rpx; text-align: center; z-index: 2; }
    .am-rv-l { font-size: 24rpx; color: #6E6E73; margin: 28rpx 0 12rpx; }
    .am-rv { display: block; width: 100%; height: 150rpx; border-radius: 20rpx; }
    .am-rv-no { background: #1A73E8; color: #FFFFFF; font-size: 30rpx; display: flex; align-items: center; justify-content: center; }
    .am-bub-s { font-size: 26rpx; }
    .am-grip { width: 80rpx; height: 10rpx; border-radius: 6rpx; background: #D2D2D7; margin: 0 auto 36rpx; }
    .am-sh-t { text-align: center; font-size: 34rpx; font-family: 'Hiragino Sans-W5', sans-serif; color: #1D1D1F; margin-bottom: 40rpx; }
    .am-bub-wrap { max-height: 56vh; }
    .am-bub { background: #F2F2F7; border-radius: 36rpx; padding: 32rpx 36rpx; font-size: 28rpx; line-height: 1.65; color: #1D1D1F; white-space: pre-wrap; }
    .am-close { margin-top: 48rpx; height: 104rpx; border-radius: 28rpx; background: #F2F2F7; color: #1D1D1F; font-size: 32rpx; display: flex; align-items: center; justify-content: center; }
</style>
