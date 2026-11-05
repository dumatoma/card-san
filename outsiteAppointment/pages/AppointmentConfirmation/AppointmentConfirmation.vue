<template>
    <view style="width: 100vw;overflow: hidden;">
       <!-- <u-navbar title="予約内容確認" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56"
            :border="true" @leftClick="leftClick" :titleWidth="484">
        </u-navbar> -->
        
        <u-navbar :placeholder="true" :safeAreaInsetTop="true" autoBack :height="60" bgColor="#ffffff" :border="true">
            <view class="u-nav-slot" slot="center">
                <view style="text-align: center;">予約内容確認</view>
                <view v-text="shopname" style="text-align: center;"></view>
            </view>
        </u-navbar>
        
        
        <!-- [カスタムエイド 2026-10-22] 予約前の最終確認の画面を見やすく(白いカード・大きめの文字)。ボタンの仕様(氏名・電話番号を入れて確認にチェックするまで薄い色)は今までどおり、色を濃く -->
        <view class="rc-page">
            <view class="rc-lead">予約内容をご確認ください<text class="rc-lead-s">{{ leadText }}</text></view>

            <view class="rc-sec">ご予約の内容</view>
            <view class="rc-card">
                <view class="rc-row">
                    <view class="rc-lab">来店日時</view>
                    <view class="rc-val rc-strong">{{ comeTime }}</view>
                </view>
                <view class="rc-row">
                    <view class="rc-lab">メニュー</view>
                    <view class="rc-val rc-menu">
                        <view class="rc-menu-m">{{ orderItem.title }}<text class="rc-price" v-if="orderItem.price">{{ orderItem.price }}</text></view>
                        <image class="rc-photo" v-if="firstImage(orderItem.image)" :src="firstImage(orderItem.image)" mode="aspectFill"></image>
                    </view>
                </view>
                <view class="rc-row">
                    <view class="rc-lab">担当者</view>
                    <view class="rc-val">{{ staff.name }}</view>
                </view>
                <view class="rc-row" v-if="staff.appoint_price && (staff.appoint_price * 1) > 0">
                    <view class="rc-lab">指名料</view>
                    <view class="rc-val rc-red">￥{{ staff.appoint_price }}</view>
                </view>
            </view>

            <view class="rc-sec">キャンセルについて</view>
            <view class="rc-card rc-pad">
                <view class="rc-note">予約確定後のキャンセルは、直接お店にご連絡ください。</view>
            </view>

            <!-- [カスタムエイド 2026-11-03] 段階8-1:受け取り方法(お店がオンにした方法だけ。2つ以上のときだけボタンを出す)と、その方法に必要な欄 -->
            <view class="rc-sec">お客様情報</view>
            <view class="rc-card rc-fcard">
                <block v-if="chCount > 1">
                    <view class="rc-flab rc-flab-ch">予約内容の受け取り方法</view>
                    <view class="rc-chs">
                        <view v-if="chs.mail" class="rc-chb m" :class="{ on: pick == 'mail' }" @click="pickCh('mail')">
                            <image class="rc-chi" src="../../static/image/ch_mail.svg" mode="aspectFit"></image>
                            <text class="rc-cht">メール</text>
                            <text class="rc-ck" v-if="pick == 'mail'">✓</text>
                        </view>
                        <view v-if="chs.sms" class="rc-chb s" :class="{ on: pick == 'sms' }" @click="pickCh('sms')">
                            <image class="rc-chi" :src="pick == 'sms' ? '../../static/image/ch_sms_w.svg' : '../../static/image/ch_sms.svg'" mode="aspectFit"></image>
                            <text class="rc-cht">SMS(電話番号に送信)</text>
                            <text class="rc-ck" v-if="pick == 'sms'">✓</text>
                        </view>
                        <!-- [カスタムエイド 2026-11-04] 段階9:LINE(スマホのときだけ。LINE アプリの中で予約を確定する) -->
                        <view v-if="chs.line" class="rc-chb l" :class="{ on: pick == 'line' }" @click="pickCh('line')">
                            <image class="rc-chi" src="../../static/image/ch_line.svg" mode="aspectFit"></image>
                            <text class="rc-cht">LINE</text>
                            <text class="rc-ck" v-if="pick == 'line'">✓</text>
                        </view>
                    </view>
                </block>
                <!-- [カスタムエイド 2026-11-04 追記9] 坂本さん:LINE だけのお店をパソコンで開いたときは、ブラウザで LINE にログインしてもらう -->
                <view v-if="linePc" class="rc-ch-note">パソコンでは、LINE へのログインが必要です(スマホの LINE で QR コードを読むか、メールアドレス・パスワードで)。</view>
                <view class="rc-fg">
                    <view class="rc-flab">お名前<text class="rc-b-req">必須</text></view>
                    <input class="rc-fin" v-model="name" placeholder="例:山田 花子" placeholder-style="color:#AEAEB2" />
                </view>
                <view class="rc-fg">
                    <view class="rc-flab">{{ pick == 'sms' ? '電話番号(携帯電話)' : '電話番号' }}<text class="rc-b-req">必須</text></view>
                    <input class="rc-fin" v-model="phone" type="tel" placeholder="ハイフンなし(携帯電話)" placeholder-style="color:#AEAEB2" />
                </view>
                <view class="rc-fg" v-if="pick == 'mail'">
                    <view class="rc-flab">メール<text class="rc-b-req">必須</text></view>
                    <input class="rc-fin" v-model="email" type="email" inputmode="email" autocomplete="email" autocapitalize="off" placeholder="例:hanako@example.com" placeholder-style="color:#AEAEB2" />
                </view>
                <view class="rc-fnote" v-if="pick == 'mail'">ご予約の内容は、このメールアドレスにお送りします。「@mail.card-san.jp」からのメールを受け取れるようにしてください。届かないときは、迷惑メールのフォルダもご確認ください。</view>
                <view class="rc-fnote" v-else-if="pick == 'sms'">この電話番号(携帯電話)あてに、SMS で予約内容をお送りします。メールアドレスは入れなくてかまいません。</view>
                <view class="rc-fnote" v-else>下の「LINE で受け取る」を押すと LINE アプリが開きます。LINE の画面で「予約を確定する」を押すと予約が完了し、お店の LINE から予約内容が届きます。</view>
            </view>

            <view class="rc-sec">ご要望(任意)</view>
            <view class="rc-card rc-pad">
                <view class="rc-hint">お店に事前にお伝えしたいご要望などあれば、ご記入ください。</view>
                <textarea class="rc-ta" v-model="text" placeholder="例:前髪は短めにしたい" placeholder-style="color:#AEAEB2"></textarea>
            </view>

            <view class="rc-card rc-check" @click="isCheck()">
                <view class="rc-box" :class="{ on: checked }"><text class="rc-tick" v-if="checked">✓</text></view>
                <view class="rc-check-t">予約内容を確認しました<text class="rc-check-s">{{ pick == 'mail' ? 'お名前・電話番号・メールアドレス' : 'お名前・電話番号' }}を入れてチェックすると「{{ pick == 'line' ? 'LINE で受け取る' : '予約を確定する' }}」が押せます</text></view>
            </view>
        </view>
        <view class="rc-bar">
            <view class="rc-btn" :class="{ on: checked && !isSubmitting, line: pick == 'line' }" hover-class="rc-btn-h" @click="yuYue()">{{ isSubmitting ? (pick == 'line' ? 'LINE を開いています…' : '予約しています…') : (pick == 'line' ? 'LINE で受け取る' : '予約を確定する') }}</view>
        </view>
        <!-- [カスタムエイド 2026-10-30] 段階6:予約する前に、メールアドレスを大きく見せて確かめてもらう(入力は1回だけ) -->
        <view class="rc-mask" v-if="showMailConfirm" @click.self="showMailConfirm = false">
            <view class="rc-dlg">
                <view class="rc-dlg-t">このメールアドレスに<br />予約の内容をお送りします</view>
                <view class="rc-dlg-mail">{{ emailClean }}</view>
                <view class="rc-dlg-s">間違いがないか、もう一度ご確認ください。<br />届かないときは、迷惑メールのフォルダもご確認ください。</view>
                <view class="rc-dlg-ok" hover-class="rc-btn-h" @click="confirmMail()">このアドレスで予約する</view>
                <view class="rc-dlg-ng" hover-class="rc-btn-h" @click="showMailConfirm = false">メールアドレスを直す</view>
            </view>
        </view>
    </view>
</template>

<script>
    import { setShopTitle } from "@/utils/pageTitle.js"
    import {
        getStaffList,
        getUserInfo,
        setOrder,
        getShopInfo,
        csLineDraft,
        csLineDoneGet,
        csLineAuth
    } from "@/api/index.js"
    export default {
        data() {
            return {
                text: '',
                checked: false,
                isSubmitting: false,
                staff:{},
                orderItem:{},
                comeTime:"",
                user:{},
                name:"",
                phone:"",
                email:"", // [カスタムエイド 2026-10-30] 段階6
                showMailConfirm: false,
                chs: { mail: 1, sms: 0, line: 0 }, // [カスタムエイド 2026-11-03] お店が受け付ける方法([カスタムエイド 2026-11-04] line を足した)
                pick: 'mail', // [カスタムエイド 2026-11-03] お客様が選んだ方法
                lineD: '', lineTimer: null, lineUntil: 0, // [カスタムエイド 2026-11-04 追記6] LINE の中で予約が終わったかを聞くため
                linePc: false, // [カスタムエイド 2026-11-04 追記9] パソコンで LINE(ブラウザで LINE にログインする)
                shopname:''
            };
        },
        onShow() {
            setShopTitle()
        },
        onUnload() {
            this.stopLine() // [カスタムエイド 2026-11-04 追記6]
        },
        onLoad(options) {
           let that = this
            that.shopname = uni.getStorageSync("shopName")
           let query = options
           that.query = query
           that.orderItem = uni.getStorageSync("orderItem")
           that.getStaff(query.staff,query.sid)
           that.loadCh(query.sid) // [カスタムエイド 2026-11-03]
           that.getDate() 
          
        },
        computed: {
            chCount() { return (this.chs.mail ? 1 : 0) + (this.chs.sms ? 1 : 0) + (this.chs.line ? 1 : 0) },
            // [カスタムエイド 2026-11-04] 坂本さん:上の説明の文を、選んだ受け取り方法に合わせる
            leadText() {
                const head = this.chCount > 1 ? '受け取り方法を選び、' : ''
                const fields = this.pick == 'mail' ? 'お名前・電話番号・メールアドレス' : 'お名前・電話番号'
                const btn = this.pick == 'line' ? 'LINE で受け取る' : '予約を確定する'
                return head + fields + 'を入力し、内容に間違いがなければ「' + btn + '」を押してください。'
            }, // [カスタムエイド 2026-11-03] ([カスタムエイド 2026-11-04] line)
            // [カスタムエイド 2026-10-30] 段階6:全角の英数字・記号を半角に、空白を取る
            emailClean() {
                return String(this.email || '').replace(/[\uFF01-\uFF5E]/g, function (c) { return String.fromCharCode(c.charCodeAt(0) - 0xFEE0) }).replace(/[\s\u3000]/g, '')
            }
        },
        methods: {
            // [カスタムエイド 2026-10-30] 段階6:メールアドレスの形か(@ の前後があり、@ の後ろに . がある)
            isEmail(v) {
                return /^[^\s@]+@[^\s@]+\.[^\s@.]+$/.test(v) && v.length <= 100
            },
            // [カスタムエイド 2026-10-22] メニューの1枚目の写真(写真が無ければ空)
            firstImage(img) {
                if (!img) return ''
                if (typeof img == 'string') {
                    try { let a = JSON.parse(img); if (Array.isArray(a)) return a[0] || '' } catch (e) {}
                    return img
                }
                return Array.isArray(img) ? (img[0] || '') : ''
            },

            // [カスタムエイド 2026-11-03] お店が受け付ける方法を読む。前に選んだ方法(このブラウザ)があれば、それを選んだ状態に
            loadCh(sid) {
                getShopInfo(sid).then((res) => {
                    const c = res && res.code == 200 && res.data && (res.data.cs_ch || (res.data.shop && res.data.shop.cs_ch))
                    if (!c) return
                    // [カスタムエイド 2026-11-04] 段階9:LINE はスマホ(LINE アプリが開ける)のときだけ。パソコンなどでは出さない。LINE だけのお店でも、パソコンにはメールを出す
                    // [カスタムエイド 2026-11-04 追記9] 坂本さん:LINE だけのお店(メール・SMS なし)は、パソコンでも LINE を出し、ブラウザで LINE にログインしてもらう。メールも受け付けるお店は、パソコンではメールだけ
                    const lineOnly = c.mail != 1 && c.sms != 1
                    const lineOk = c.line == 1 && (this.isPhone() || lineOnly)
                    this.linePc = lineOk && !this.isPhone()
                    this.chs = { mail: c.mail == 1 ? 1 : 0, sms: c.sms == 1 ? 1 : 0, line: lineOk ? 1 : 0 }
                    if (!this.chs.mail && !this.chs.sms && !this.chs.line) this.chs.mail = 1
                    let last = ''
                    try { last = uni.getStorageSync('cs_ch_pick') || '' } catch (e) {}
                    if (last && this.chs[last]) this.pick = last
                    else this.pick = this.chs.mail ? 'mail' : (this.chs.sms ? 'sms' : 'line')
                }).catch(() => {})
            },
            // [カスタムエイド 2026-11-04] 段階9:スマホ(iPhone・Android)か
            isPhone() {
                try { return /iPhone|Android.+Mobile|Android/.test(navigator.userAgent || '') && !/iPad/.test(navigator.userAgent || '') } catch (e) { return false }
            },
            pickCh(v) {
                if (!this.chs[v] || this.pick == v) return
                this.pick = v
                this.checked = false
            },
            leftClick() {
                uni.navigateBack()
            },
            getStaff(id,sid){
                let that = this
                let data = {}
                data.sid = sid
                data.id = ""
                if(id == 0){
                    let obj = {}
                    obj.id = 0
                    obj.name = "指名なし"
                    that.staff = obj
                }else{
                   getStaffList(data).then((res) => {
                       console.log("staff",res)
                       if(res.code == 200){
                           res.data.admin3s.forEach((val,index) => {
                               if(val.id == id){
                                   that.staff = val
                               }
                           })
                       }
                   }) 
                } 
            },
            getDate(){
              let that = this
              let dates = uni.getStorageSync("orderTime")
              that.comeTime = dates
              let date = new Date(dates.item.date)
              // [カスタムエイド 2026-10-22] 火曜と水曜が逆になっていた
              let week = ['日', '月', '火', '水', '木', '金', '土'][date.getDay()] || ''
              console.log(week)
              that.comeTime = dates.item.date + "("+week+") "+dates.ite.time
              // [カスタムエイド 2026-10-22] ここで止まっていた(orderItem が無い)ため削除。orderItem は onLoad で読み込み済み
              getUserInfo(uni.getStorageSync("user").id).then((res) => {
                  if(res.code == 200){
                      that.user = res.data.member
                  }
              })
            },
            isCheck() {
                let that = this
                if(!that.name){
                    uni.showToast({
                        title:"名前を入力してください",
                        icon:"none"
                    })
                    return false
                }
                
                if(!that.phone){
                    uni.showToast({
                        title:"電話番号を入力してください",
                        icon:"none"
                    })
                    return false
                }
                
                // [カスタムエイド 2026-10-30] 段階6:メールアドレスも必須([カスタムエイド 2026-11-03] メールを選んだときだけ)
                if(that.pick == 'mail' && !that.emailClean){
                    uni.showToast({
                        title:"メールアドレスを入力してください",
                        icon:"none"
                    })
                    return false
                }
                if(that.pick == 'mail' && !that.isEmail(that.emailClean)){
                    uni.showToast({
                        title:"メールアドレスの形が正しくありません",
                        icon:"none"
                    })
                    return false
                }

                if (this.checked == true) {
                    this.checked = false
                } else {
                    this.checked = true
                }
            },
            yuYue() {
                let that = this
                if (!that.checked) {
                   uni.showToast({
                       title: '予約内容を確認してください',
                       icon: 'none',
                       duration: 1000
                   })
                    return
                }
                // Loading中の連打による予約重複を防止（送信中はボタンを非活性）
                if (that.isSubmitting) return
                // [カスタムエイド 2026-11-04] 段階9:LINE を選んだときは、入れた内容をサーバーに預けて LINE アプリを開く(LINE の中で予約を確定する)
                if (that.pick == 'line') {
                    if (!that.phone) { that.checked = false; uni.showToast({ title: "電話番号を入力してください", icon: "none" }); return }
                    that.openLine()
                    return
                }
                // [カスタムエイド 2026-11-03] SMS を選んだときは、メールアドレスの確認の表示を出さずに予約する
                if (that.pick == 'sms') {
                    if (!that.phone) { that.checked = false; uni.showToast({ title: "電話番号を入力してください", icon: "none" }); return }
                    that.confirmMail()
                    return
                }
                // [カスタムエイド 2026-10-30] 段階6:チェックのあとにメールアドレスを直した場合も、ここで確かめる
                if (!that.isEmail(that.emailClean)) {
                    that.checked = false
                    uni.showToast({ title: "メールアドレスの形が正しくありません", icon: "none" })
                    return
                }
                that.showMailConfirm = true
            },
            // [カスタムエイド 2026-11-04] 段階9:予約の内容(送るもの・LINE の画面に見せるもの)を預けて、LIFF の住所を開く
            openLine() {
                let that = this
                if (that.isSubmitting) return
                that.isSubmitting = true
                const order = {
                    start_time: that.comeTime,
                    smid: uni.getStorageSync("orderItem").id,
                    said: that.query.staff * 1,
                    remark: that.text,
                    phone: that.phone,
                    name: that.name,
                    email: '',
                    cs_ch: 'line'
                }
                const view = {
                    shop: that.shopname || '',
                    time: that.comeTime,
                    menu: (that.orderItem && that.orderItem.title) || '',
                    price: (that.orderItem && that.orderItem.price) || '',
                    staff: (that.staff && that.staff.name) || '',
                    appoint_price: (that.staff && that.staff.appoint_price) || '',
                    image: that.firstImage(that.orderItem && that.orderItem.image),
                    name: that.name,
                    phone: that.phone,
                    remark: that.text
                }
                csLineDraft({ sid: that.query.sid * 1, data: { order: order, view: view } }).then((res) => {
                    if (res && res.code == 200 && res.data && res.data.liff_url) {
                        try { uni.setStorageSync('cs_ch_pick', 'line') } catch (e) {}
                        // [カスタムエイド 2026-11-04 追記9] パソコン:同じ画面で LINE のログインに進む。戻り先は LINE の中と同じ画面(pages/liff/liff。?d= に cs_lt が付いて戻る)
                        if (that.linePc) { that.goLineLogin(res.data.d); return }
                        that.watchLine(res.data.d) // [カスタムエイド 2026-11-04 追記6]
                        // #ifdef H5
                        window.location.href = res.data.liff_url
                        // #endif
                        setTimeout(() => { that.isSubmitting = false }, 3000)
                    } else {
                        that.isSubmitting = false
                        uni.showToast({ title: (res && res.message) || 'LINE を開けませんでした', icon: 'none' })
                    }
                }).catch(() => {
                    that.isSubmitting = false
                    uni.showToast({ title: 'LINE を開けませんでした', icon: 'none' })
                })
            },
            // [カスタムエイド 2026-11-04 追記9] パソコン:LINE のログインの住所をもらって開く
            goLineLogin(d) {
                let that = this
                let back = ''
                // #ifdef H5
                back = window.location.origin + '/reserve/pages/liff/liff?d=' + d
                // #endif
                csLineAuth(that.query.sid * 1, back).then((r) => {
                    if (r && r.code == 200 && r.data && r.data.url) {
                        // #ifdef H5
                        window.location.href = r.data.url
                        // #endif
                        setTimeout(() => { that.isSubmitting = false }, 3000)
                    } else {
                        that.isSubmitting = false
                        uni.showToast({ title: (r && r.message) || 'LINE を開けませんでした', icon: 'none' })
                    }
                }).catch(() => {
                    that.isSubmitting = false
                    uni.showToast({ title: 'LINE を開けませんでした', icon: 'none' })
                })
            },
            // [カスタムエイド 2026-11-04 追記6] LINE の中で予約が終わったら(戻ってきたとき・3秒ごと・30分まで)、予約完了の画面に進む
            watchLine(d) {
                let that = this
                that.lineD = d
                that.lineUntil = Date.now() + 30 * 60 * 1000
                if (that.lineTimer) clearInterval(that.lineTimer)
                that.lineTimer = setInterval(() => { that.checkLine() }, 3000)
                // #ifdef H5
                if (!that._lineVis) { that._lineVis = () => { if (document.visibilityState == 'visible') that.checkLine() }; document.addEventListener('visibilitychange', that._lineVis) }
                // #endif
            },
            stopLine() {
                if (this.lineTimer) { clearInterval(this.lineTimer); this.lineTimer = null }
                // #ifdef H5
                if (this._lineVis) { document.removeEventListener('visibilitychange', this._lineVis); this._lineVis = null }
                // #endif
            },
            checkLine() {
                let that = this
                if (!that.lineD) return
                if (Date.now() > that.lineUntil) { that.stopLine(); return }
                csLineDoneGet(that.lineD).then((res) => {
                    if (!(res && res.code == 200 && res.data && res.data.done == 1)) return
                    const o = res.data.order || {}
                    that.stopLine()
                    that.lineD = ''
                    uni.removeStorageSync("orderTime")
                    uni.setStorageSync("lastOrderImage", that.firstImage(that.orderItem && that.orderItem.image))
                    uni.removeStorageSync("orderItem")
                    uni.setStorageSync("lastOrderEmail", '')
                    uni.setStorageSync("lastOrderSms", '')
                    uni.setStorageSync("lastOrderLine", res.data.bot_name || 'お店')
                    uni.redirectTo({
                        url:"../completed/completed?name="+o.name+"&order_no="+o.order_no+"&start_time="+o.start_time+"&amount="+o.amount+"&remark="+o.remark+"&appoint_name="+o.appoint_name+"&menu_name="+o.menu_name+"&id="+o.sid+'&phone='+o.phone+"&appoint_amount="+o.appoint_amount
                    })
                }).catch(() => {})
            },
            // [カスタムエイド 2026-10-30] 段階6:確認の表示で「このアドレスで予約する」を押したら予約する
            confirmMail() {
                let that = this
                that.showMailConfirm = false
                if (that.isSubmitting) return
                that.isSubmitting = true
                let data = {}
                let date = uni.getStorageSync("orderTime")
                data.start_time = that.comeTime
                data.smid = uni.getStorageSync("orderItem").id
                data.said = that.query.staff * 1
                data.remark = that.text
                data.phone = that.phone
                data.name = that.name
                data.email = that.pick == 'mail' ? that.emailClean : '' // [カスタムエイド 2026-10-30] 段階6
                data.cs_ch = that.pick // [カスタムエイド 2026-11-03] 受け取り方法(mail / sms)
                setOrder(data).then((res) => {
                    console.log(res)
                    if(res.code == 200){
                        uni.removeStorageSync("orderTime")
                        uni.setStorageSync("lastOrderImage", that.firstImage(that.orderItem && that.orderItem.image)) // [カスタムエイド 2026-10-22] 予約完了の画面でメニューの写真を出すため
                        uni.removeStorageSync("orderItem")
                        uni.setStorageSync("lastOrderEmail", that.pick == 'mail' ? that.emailClean : '') // [カスタムエイド 2026-10-30] 段階6:予約完了の画面で「◯◯にお送りしました」と出すため(アドレスを URL に入れない)
                        uni.setStorageSync("lastOrderSms", that.pick == 'sms' ? that.phone : '') // [カスタムエイド 2026-11-03] SMS のとき
                        uni.setStorageSync("lastOrderLine", '') // [カスタムエイド 2026-11-04 追記6]
                        try { uni.setStorageSync('cs_ch_pick', that.pick) } catch (e) {} // [カスタムエイド 2026-11-03] 次の予約で同じ方法を選んだ状態に
                        uni.navigateTo({
                            url:"../completed/completed?name="+res.data.order.name+"&order_no="+res.data.order.order_no+"&start_time="+res.data.order.start_time+"&amount="+res.data.order.amount+"&remark="+res.data.order.remark+"&appoint_name="+res.data.order.appoint_name+"&menu_name="+res.data.order.menu_name+"&id="+res.data.order.sid+'&phone='+res.data.order.phone+"&appoint_amount="+res.data.order.appoint_amount
                        })
                    }else{
                        that.isSubmitting = false
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                }).catch(() => {
                    that.isSubmitting = false
                })
                
                // uni.navigateTo({
                //     url: '../../pagesA/completed/completed'
                // })
            },
         }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-22] 予約内容確認(予約前の最終確認。見やすさを最優先:白いカード・大きめの文字・行の区切り)
       ボタン:必須がそろうまでは薄い色、そろったら濃い色(仕様は今までどおり)。色は以前(#53B1AF・薄い時は透明度0.6)より濃く */
    .rc-page { min-height: 100vh; background: #F5F5F7; padding-bottom: calc(260rpx + env(safe-area-inset-bottom)); }
    .rc-lead { padding: 48rpx 40rpx 24rpx; font-size: 34rpx; font-weight: 700; color: #1D1D1F; }
    .rc-lead-s { display: block; margin-top: 8rpx; font-size: 26rpx; font-weight: 400; color: #6E6E73; }
    .rc-sec { padding: 0 40rpx 16rpx; font-size: 26rpx; color: #6E6E73; }
    .rc-card { margin: 0 24rpx 48rpx; background: #FFFFFF; border-radius: 32rpx; padding: 0 32rpx; }
    .rc-pad { padding: 32rpx; }
    .rc-row { display: flex; align-items: flex-start; padding: 32rpx 0; border-bottom: 1px solid #E5E5EA; }
    .rc-row:last-child { border-bottom: 0; }
    .rc-lab { width: 160rpx; flex-shrink: 0; font-size: 28rpx; color: #6E6E73; line-height: 1.5; padding-top: 2rpx; }
    .rc-val { flex: 1; min-width: 0; font-size: 32rpx; color: #1D1D1F; line-height: 1.5; word-break: break-all; }
    .rc-strong { font-size: 36rpx; font-weight: 700; }
    .rc-price { display: block; margin-top: 8rpx; font-size: 30rpx; color: #D93025; font-weight: 600; }
    .rc-red { color: #D93025; font-weight: 600; }
    .rc-empty { color: #AEAEB2; }
    .rc-note { font-size: 30rpx; color: #1D1D1F; line-height: 1.7; }
    .rc-hint { font-size: 28rpx; color: #515154; line-height: 1.6; margin-bottom: 20rpx; }
    .rc-ta { width: 100%; height: 220rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 24rpx 28rpx; font-size: 30rpx; line-height: 1.6; box-sizing: border-box; background: #FFFFFF; display: block; }
    .rc-check { display: flex; align-items: center; padding: 36rpx 32rpx; }
    .rc-box { width: 48rpx; height: 48rpx; border-radius: 12rpx; border: 3rpx solid #8E8E93; box-sizing: border-box; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #FFFFFF; }
    .rc-box.on { background: #2B8A87; border-color: #2B8A87; }
    .rc-tick { color: #FFFFFF; font-size: 30rpx; font-weight: 700; line-height: 1; }
    .rc-check-t { margin-left: 24rpx; font-size: 32rpx; color: #1D1D1F; font-weight: 600; }
    .rc-check-s { display: block; margin-top: 4rpx; font-size: 24rpx; color: #6E6E73; font-weight: 400; }
    .rc-bar { position: fixed; left: 0; right: 0; bottom: 0; background: #FFFFFF; border-top: 1px solid #E5E5EA; padding: 24rpx 32rpx calc(32rpx + env(safe-area-inset-bottom)); z-index: 50; }
    .rc-btn { height: 108rpx; border-radius: 28rpx; background: #7FBDBA; color: #FFFFFF; font-size: 34rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; letter-spacing: 2rpx; }
    .rc-btn.on { background: #2B8A87; }
    .rc-btn-h { opacity: 0.85; }
    .rc-menu { display: flex; align-items: flex-start; }
    .rc-menu-m { flex: 1; min-width: 0; }
    .rc-photo { width: 128rpx; height: 128rpx; border-radius: 16rpx; margin-left: 20rpx; flex-shrink: 0; background: #F2F2F7; }
    .rc-req { color: #D93025; margin-right: 4rpx; }
    .rc-in { flex: 1; height: 88rpx; border: 1px solid #D2D2D7; border-radius: 20rpx; padding: 0 24rpx; font-size: 32rpx; box-sizing: border-box; background: #FFFFFF; }
    .rc-row-in { align-items: center; }
    /* [カスタムエイド 2026-10-30] 段階6:メールの注意書き・確認の表示 */
    .rc-mail-note { margin: -28rpx 40rpx 48rpx; font-size: 24rpx; color: #6E6E73; line-height: 1.7; }
    .rc-mask { position: fixed; left: 0; top: 0; right: 0; bottom: 0; background: rgba(0,0,0,.45); z-index: 100; display: flex; align-items: center; justify-content: center; padding: 0 40rpx; }
    .rc-dlg { width: 100%; max-width: 640rpx; background: #FFFFFF; border-radius: 32rpx; padding: 48rpx 40rpx 32rpx; box-sizing: border-box; text-align: center; }
    .rc-dlg-t { font-size: 32rpx; font-weight: 700; color: #1D1D1F; line-height: 1.5; }
    .rc-dlg-mail { margin: 32rpx 0 24rpx; padding: 28rpx 24rpx; background: #F5F5F7; border-radius: 20rpx; font-size: 36rpx; font-weight: 700; color: #1D1D1F; word-break: break-all; line-height: 1.4; }
    .rc-dlg-s { font-size: 24rpx; color: #6E6E73; line-height: 1.7; margin-bottom: 36rpx; }
    .rc-dlg-ok { height: 100rpx; border-radius: 28rpx; background: #2B8A87; color: #FFFFFF; font-size: 32rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .rc-dlg-ng { height: 88rpx; margin-top: 16rpx; border-radius: 28rpx; color: #2B8A87; font-size: 30rpx; font-weight: 600; display: flex; align-items: center; justify-content: center; }

    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }
    .op{
        opacity: 0.6 !important;
    }
    .top {
        width: 100%;
        padding: 50rpx 0;
        padding-left: 5%;
        font-weight: 700;
        background: #F5F5F7;
        font-size: 32rpx;
        color: #1A73E8;
        border-bottom: 1px solid #D2D2D7;
    }
    .inner{
        width: 100%;
        height: 100%;
        background: #1A73E8;
        border-radius: 50%;
    }
    .content-box {
        .con-box {

            border-bottom: 1px solid #D2D2D7;

            .con {
                width: 90%;
                margin: 0 auto;
                display: flex;
                font-size: 32rpx;
                padding: 40rpx 0;

                .con-left {
                    width: 128rpx;
                    flex-shrink: 0;
                    color: #707070;
        
      }

                .con-right {
                    color: #1D1D1F;
                    margin-left: 40rpx;

                    .price {
                        margin-top: 40rpx;
                        color: rgba(217, 48, 37, 0.65);
                    }
                }
            }
        }
    }

    .xq-box {
        width: 100%;
        padding: 36rpx 0;
        padding-left: 5%;
        background: #F5F5F7;
        font-size: 32rpx;
        font-weight: 700;
        color: #707070;
        border-bottom: 1px solid #D2D2D7;
    }

    .xq-des {
        font-size: 32rpx;
        color: #1D1D1F;
        padding: 42rpx 5% 80rpx 5%;
        border-bottom: 1px solid #D2D2D7;
    }

    .user-info-box {
        .user-info {
            width: 100%;
            padding: 22upx 0;
            // border-bottom: 1px solid #D2D2D7;

            .user-item {
                width: 90%;
                margin: 0 auto;
                // display: flex;
                font-size: 32rpx;
                // align-items: center;
                .user-left {
                    flex-shrink: 0;
                    width: 222rpx;
                    color: #707070;
                    margin-bottom: 10upx;
                }

                .user-right {
                    margin-left: 70rpx;
                    color: #1D1D1F;
                }
            }
        }
    }

    .forms {
        padding-bottom: 82rpx;
        border-bottom: 1px solid #D2D2D7;
    }

    .forms-item-box {
        padding-top: 40rpx;
        // border-bottom: 1px solid #D2D2D7;

        .fotms-item {
            width: 90%;
            margin: 0 auto;
            font-size: 32rpx;
            color: #1D1D1F;
            margin-bottom: 40rpx;
        }

        .fotms {
            width: 90%;
            margin: 0 auto;

            textarea {
                width: 100%;
                height: 200rpx;
                background: #FFFFFF;
                border-radius: 20rpx;
                box-sizing: border-box;
                padding: 20rpx;
                border: 2rpx solid #D2D2D7;
            }
        }
    }

    .c-o-n {
        padding-bottom: 300rpx;

        .con-orng {
            height: 128rpx;
            background: #FFFFFF;
            border-bottom: 2rpx solid #D2D2D7;

            .co {
                width: 90%;
                margin: 0 auto;
                height: 100%;
                display: flex;
                align-items: center;

                .checked {

                    border: 2upx solid #2979ff !important;
                    border: 0;
                }
            }

            .co-text {
                font-size: 36rpx;
                color: #86868B;
                margin-left: 20rpx;
            }
        }

        .yuan {
            width: 40rpx;
            height: 40rpx;
            text-align: center;
            border-radius: 50%;
            box-sizing: border-box;
            border: 2rpx solid #707070;
            padding: 6upx !important;
            // margin-right: 24rpx;
        }

        ::v-deep .u-icon__icon {
            padding-left: 7rpx;
            padding-top: 7rpx;
        }
    }

    .fix-btn-ok {
        position: fixed;
        left: 0;
        bottom: 0;
        width: 100%;
        height: 256rpx;
        background: #F5F5F7;
        box-shadow: 0px 0px 12rpx 2rpx rgba(0, 0, 0, 0.16);

        .fix-b {
            width: 480rpx;
            height: 96rpx;
            line-height: 96rpx;
            text-align: center;
            background: #53B1AF;
            border-radius: 20rpx;
            font-size: 40rpx;
            color: #FFFFFF;
            margin: 60rpx auto;
            margin-bottom: 0;
        }
    }
    
    .sss{
        box-sizing: border-box;padding: 40upx 40upx 0;
    }

    /* [カスタムエイド 2026-11-03] カードの影(2026-11-03):下へ1px・ぼかし4px・黒10%。グレーの背景と白いカードの境目を分かりやすく */
    .rc-card {
        box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.10) !important;
    }
    /* [カスタムエイド 2026-11-03] 受け取り方法のボタン・お客様情報(見出しと欄の間 5px・項目の間 18px・ボタンと「お名前」の間 30px) */
    .rc-fcard { padding: 36rpx 32rpx !important; display: flex; flex-direction: column; }
    .rc-fcard > view + view, .rc-fcard > view + .rc-fg, .rc-fcard .rc-fg + .rc-fg, .rc-fcard .rc-fg + .rc-fnote { margin-top: 36rpx; }
    .rc-flab { font-size: 26rpx; font-weight: 600; color: #1D1D1F; display: flex; align-items: center; }
    .rc-flab-ch { margin-bottom: 0; font-size: 30rpx; font-weight: 700; } /* [カスタムエイド 2026-11-04] 坂本さん:文字を約2px大きく・少し太く */
    .rc-b-req { font-size: 20rpx; font-weight: 700; color: #FFFFFF; background: #D93025; border-radius: 8rpx; padding: 2rpx 10rpx; margin-left: 12rpx; line-height: 1.6; }
    .rc-fg { display: flex; flex-direction: column; }
    .rc-fg .rc-fin { margin-top: 10rpx; }
    .rc-fin { height: 88rpx; padding: 0 24rpx; border: 1px solid #D2D2D7; border-radius: 20rpx; background: #FFFFFF; font-size: 30rpx; color: #1D1D1F; }
    .rc-fnote { font-size: 24rpx; color: #6E6E73; line-height: 1.6; margin-top: 24rpx !important; }
    /* [カスタムエイド 2026-11-04] 坂本さん:見出しとボタンの間を半分(52→26rpx)・ボタンの高さを約1.5倍(中の絵と文字も大きく)・ボタンとお名前の間を2倍(60→120rpx) */
    .rc-chs { display: flex; flex-direction: column; margin-bottom: 84rpx; }
    .rc-fcard > .rc-chs { margin-top: 26rpx; }
    .rc-ch-note { font-size: 28rpx; color: #515154; line-height: 1.6; background: #E6F4EA; border-radius: 20rpx; padding: 20rpx 24rpx; margin-bottom: 40rpx; } /* [カスタムエイド 2026-11-04 追記9] */
    .rc-chb { display: flex; align-items: center; border-radius: 28rpx; padding: 36rpx 28rpx; border: 3rpx solid; background: #FFFFFF; font-size: 36rpx; font-weight: 700; }
    .rc-chb + .rc-chb { margin-top: 20rpx; }
    .rc-chi { width: 72rpx; height: 72rpx; flex-shrink: 0; margin-right: 24rpx; }
    .rc-cht { flex: 1; min-width: 0; }
    .rc-ck { margin-left: 16rpx; font-size: 40rpx; }
    .rc-chb.m { border-color: #1A73E8; color: #1A73E8; }
    .rc-chb.m.on { background: #1A73E8; color: #FFFFFF; }
    .rc-chb.s { border-color: #48484A; color: #48484A; }
    .rc-chb.s.on { background: #48484A; color: #FFFFFF; }
    /* [カスタムエイド 2026-11-04] 段階9:LINE */
    .rc-chb.l { border-color: #06C755; color: #05A847; }
    .rc-chb.l.on { background: #06C755; color: #FFFFFF; }
    .rc-btn.line { background: #8BD9A9; }
    .rc-btn.line.on { background: #06C755; }
</style>
