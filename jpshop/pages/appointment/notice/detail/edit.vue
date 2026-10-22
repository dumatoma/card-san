<template>
    <view class="contains">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="予約内容" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-22] 予約内容の画面を、ほかの画面の作り直しと同じ見た目に(白いカード・行の区切り・下に青いボタン)。処理は変更なし -->
        <view class="ed-pg">
            <view class="ed-card">
                <view class="ed-row" v-if="!wai">
                    <view class="ed-lab">氏名</view>
                    <view class="ed-val" :class="{ 'ed-link': info.mid }" @click="info.mid && toCustomer()">{{ info.name || 'ー' }}</view>
                </view>
                <view class="ed-row" v-if="!wai">
                    <view class="ed-lab">電話番号</view>
                    <input class="ed-in" type="text" v-model="info.phone" placeholder="入力してください" placeholder-class="ed-ph" />
                </view>
                <view class="ed-row" v-if="!wai">
                    <view class="ed-lab">会員ID</view>
                    <view class="ed-val ed-gray">{{ info.member_id || 'ー' }}</view>
                </view>
                <view class="ed-row">
                    <view class="ed-lab">予約番号</view>
                    <view class="ed-val ed-gray">{{ info.order_no }}</view>
                </view>
            </view>

            <view class="ed-sec">メニュー</view>
            <view class="ed-card ed-menu" hover-class="ed-hover" @click="timeShow = true">
                <view class="ed-menu-l">
                    <view class="ed-menu-t" :class="{ 'ed-red': wai && waiName == 'お休み', 'ed-green': wai && waiName != 'お休み' }">{{ wai ? waiName : menu.title }}</view>
                    <view class="ed-menu-s" v-if="!wai">所要時間 {{ menu.order_time }}分<text class="ed-price" v-if="menu.price">{{ menu.price }}</text></view>
                </view>
                <u-icon name="arrow-right" color="#C7C7CC" size="16"></u-icon>
            </view>

            <view class="ed-sec">日時・担当</view>
            <view class="ed-card">
                <view class="ed-row">
                    <view class="ed-lab">日付</view>
                    <view class="ed-chip" hover-class="ed-hover" @click="calendarShow = true">{{ info.day }}</view>
                </view>
                <view class="ed-row">
                    <view class="ed-lab">開始</view>
                    <view class="ed-chip" hover-class="ed-hover" @click="startShow = true">{{ info.start }}</view>
                </view>
                <view class="ed-row">
                    <view class="ed-lab">終了</view>
                    <view class="ed-chip" hover-class="ed-hover" @click="showEnd">{{ info.end }}</view>
                </view>
                <view class="ed-row" hover-class="ed-hover" @click="getZhi">
                    <view class="ed-lab">指名担当</view>
                    <view class="ed-person">
                        <view class="ed-dot" :style="{ background: appoint_admin.color || 'rgb(23,84,145)' }"></view>
                        <text class="ed-pname">{{ appoint_admin.name }}</text>
                        <u-icon name="arrow-right" color="#C7C7CC" size="16"></u-icon>
                    </view>
                </view>
                <view class="ed-row" hover-class="ed-hover" @click="getDang">
                    <view class="ed-lab">当日担当</view>
                    <view class="ed-person">
                        <view class="ed-dot" :style="{ background: day_admin.color || 'rgb(23,84,145)' }"></view>
                        <text class="ed-pname">{{ day_admin.name }}</text>
                        <u-icon name="arrow-right" color="#C7C7CC" size="16"></u-icon>
                    </view>
                </view>
            </view>

            <view class="ed-sec">メモ</view>
            <view class="ed-card ed-pad">
                <textarea class="ed-ta" v-model="info.remark" placeholder="メモを入力" placeholder-class="ed-ph" maxlength="-1" />
            </view>

            <!-- お客さまがいる予約だけ(お休み・勤務時間外などは送る相手がいない) -->
            <view class="ed-card ed-menu" v-if="info.mid" hover-class="ed-hover" @click="show1 = true">
                <view class="ed-menu-l">
                    <view class="ed-menu-t">お客さまに変更を知らせる</view>
                    <view class="ed-menu-s">保存したあとに、予約内容の変更をメッセージで送ります</view>
                </view>
                <u-icon name="arrow-right" color="#C7C7CC" size="16"></u-icon>
            </view>

            <view class="ed-btn" hover-class="ed-hover" @click="save">変更を保存</view>
            <view class="ed-del" hover-class="ed-hover" @click="showAle = true">この予約を取り消す</view>
        </view>
        <!-- 开始时间弹出层 -->
        <u-picker :show="startShow" ref="uPicker" :columns="times" @cancel="startShow = false"
            :closeOnClickOverlay="true" @confirm="selectStart"></u-picker>
        <!-- 结束时间弹出层 -->
        <u-picker :show="endShow" ref="uPicker" :columns="times" @cancel="endShow = false" :closeOnClickOverlay="true"
            @confirm="selectEnd"></u-picker>
        <!-- 日历弹出层 -->
        <u-calendar :show="calendarShow" @confirm="confirmDate" :showTitle = "false" @close="calendarShow = false"></u-calendar>
        <!-- 选人弹出层(担当者) -->
        <u-popup :show="peoShow" round="16">
            <view style="height: 90vh;width: 100%;overflow-y: scroll;position: relative;box-sizing: border-box;padding-bottom: calc(48rpx + env(safe-area-inset-bottom));">
                <view class="title">
                    <view class="ed-x" @click="peoShow = false">×</view>
                    <view class="titleName">指名担当を選択</view>
                </view>
                <view class="peoContent">
                    <view class="peoItem" v-for="(item,index) in peolist">
                        <view class="selectContent" @click="chooseZ(item,index)">
                            <view class="unselect" v-if="!item.checked"></view>
                            <view class="selected" v-if="item.checked">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="peoSqu" :style="item.color == ''? 'background:#175491' : 'background:'+item.color">
                        </view>
                        <view class="peoName" v-text="item.name"></view>
                    </view>
                </view>
            </view>
        </u-popup>
        <!-- 选人弹出框 -->
        <u-popup :show="peoShow2" round="16">
            <view style="height: 90vh;width: 100%;overflow-y: scroll;position: relative;box-sizing: border-box;padding-bottom: calc(48rpx + env(safe-area-inset-bottom));">
                <view class="title">
                    <view class="ed-x" @click="peoShow2 = false">×</view>
                    <view class="titleName">当日担当を選択</view>
                </view>
                <view class="peoContent">
                    <view class="peoItem" v-for="(item,index) in tplist">
                        <view class="selectContent" @click="chooseD(item,index)">
                            <view class="unselect" v-if="!item.checked"></view>
                            <view class="selected" v-if="item.checked">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="peoSqu" :style="item.color == ''? 'background:#175491' : 'background:'+item.color">
                        </view>
                        <view class="peoName" v-text="item.name"></view>
                    </view>
                </view>
            </view>
        </u-popup>
        <u-popup :show="timeShow" @close="closeShow" mode="bottom" :safeAreaInsetTop='false' bgColor="f9f9f9"
            :round="16">
            <view style="height: 90vh;width: 100%;overflow-y: scroll;position: relative;box-sizing: border-box;padding-bottom: calc(48rpx + env(safe-area-inset-bottom));">
                <view class="title">
                    <view class="ed-x" @click="closeShow">×</view>
                    <view class="titleName">メニュー選択</view>
                </view>
                <view class="detail">
                    <!-- [カスタムエイド 2026-10-22] 「お休み」(赤)と「休憩・勤務時間外」(濃い緑)に分けた -->
                    <view class="selectAll" @click="changeWai('お休み')">
                        <view class="selectContent">
                            <view class="unselect" v-if="!(wai && waiName == 'お休み')"></view>
                            <view class="selected" v-if="wai && waiName == 'お休み'">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="allname">お休み</view>
                    </view>
                    <view class="selectAll" style="border-top: 2upx solid #d2d2d7;" @click="changeWai('休憩・勤務時間外')">
                        <view class="selectContent">
                            <view class="unselect" v-if="!(wai && waiName != 'お休み')"></view>
                            <view class="selected" v-if="wai && waiName != 'お休み'">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="allname ed-green">休憩・勤務時間外</view>
                    </view>
                    <view class="settingD">
                        <view class="sDitems" v-for="(item,index) in array" v-if="item.items.length > 0">
                            <view class="sDtitle" v-text="item.name"></view>
                            <view class="sDitem" v-for="(ite,idx) in item.items">
                                <view class="selectContent" @click="chooseMenu(index,idx)">
                                    <view class="unselect" v-if="!ite.checked"></view>
                                    <view class="selected" v-if="ite.checked">
                                        <view class="fill"></view>
                                    </view>
                                </view>
                                <view class="sDitemRight">
                                    <view class="sDtop" v-text="ite.name"></view>
                                    <view class="sDbottom">
                                        <view class="sDimage" v-if="ite.image">
                                            <image :src="ite.image[0]" mode=""></image>
                                        </view>
                                        <view class="sDinfo">
                                            <view class="sDprice" v-text="ite.price"></view>
                                            <view class="sDtime" v-text="`所要時間:${ite.time}分`"></view>
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
        </u-popup>
        <mod :config="config" v-if="showAle == true" @getStatus="changeStatus"></mod>
        <u-popup :show="show1" @close="show1 = false" @cancel="show1 = false" >
            <view class="maskContent">
                <view class="mkitem" :class="sending == true?'cccc' : ''" style="color: #1A73E8;" @click="sendMessage">
                   <text style="margin-right: 10upx;">この内容を送る</text> <image src="../../../../static/images/send.png" mode="aspectFit"></image>
                </view>
                <view class="mkitem" style="color: #D93025;" hover-class="vvvvv" @click="show1 = false">
                   <text>キャンセル</text>
                </view>
            </view>
        </u-popup>
        <view class="mask" v-if="showmask == true">
            <view class="maskcontent">
                <image src="../../../../static/svg/gray.svg" class="mi" mode=""></image>
                <view>
                     配信しました
                </view>
            </view>
        </view>
        <view class="msk" v-if="showm"></view>
    </view>
</template>
<script>
    import {
        getAppointOrderDetail,
        getStaffList,
        editAppointOrder,
        deleteAppointOrder,
        menuCateList,
        menuList,
        cahngeInof,
        getTodayAdmin
    } from '@/api/index.js'
    import mod from "@/components/mod.vue"
    export default {
        components:{mod},
        data() {
            return {
                showm:false,
                show1:false,
                showAle:false,
                showmask:false,
                config:{
                    title:"この予約を取り消しますか？",
                    content:"取り消しすると、この予約情報は 完全に削除されます。",
                    type:"revers",
                    confirm:true,
                    cancel:true,
                    confirmText:"キャンセル",
                    cancelText:"予約を取り消す"
                },
                info: {},
                mid:"",
                wai:false,
                waiName: "休憩・勤務時間外", // [カスタムエイド 2026-10-22] お休み・休憩のどちらか(サーバーに名前として保存)
                // お休み・勤務時間外の長さ(分)。開始時刻・日付を変えても終了時刻はこの長さを保つ
                waiDur:0,
                menu:{},
                show: false,
                timeShow: false,
                startShow: false,
                endShow: false,
                calendarShow: false,
                peoShow: false,
                peoShow2: false,
                currentDate: "",
                current: "",
                day_admin:{},
                temp_day_admin:{},
                selectInfo: {
                    name: "メニュータイトル・メニュータイトル・メニュ・・・・",
                    time: 60,
                    price: 3500
                },
                query: {},
                appoint_admin:{},
                person: {
                    color: "#ED8E20",
                    name: "鈴木光"
                },
                peolist: [{
                    name: "指名なし",
                    color: "#d2d2d7",
                    checked: false,
                    id: 0
                }],
                templist: [{
                    name: "指名なし",
                    color: "#d2d2d7",
                    checked: false,
                    id: 0
                }],
                tplist: [],
                times: [
                    [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22],
                    ["00", "30"]
                ],
                selectAll: false,
                array: [],
                totalTime:0,
                sending:false,
                saveTimer: null
            };
        },
        created() {
            this.getCate()
        },
        onLoad(options) {
            let that = this
            let query = options
            that.query = query
            that.getDetail(query.id)
        },
        methods: {
            sendMessage(){
              let that = this
              that.showm = true
              that.sending = true
                 cahngeInof(that.info.id).then((res) => {
                     that.showm = false
                     if(res.code == 200){
                         that.show1 = false
                         that.showmask = true
                         setTimeout(() => {
                             that.showmask = false
                             that.sending = false
                         },2000)
                     }else if(res.code == 400){
                         uni.hideToast()
                          that.show1 = false
                         that.mask = true
                     }else{
                         uni.showToast({
                             title:res.message,
                             icon:"none"
                         })
                     }
                 })
            },
            changeStatus(e){
              this.showm = true
              if(e == "cancel"){
                    this.deleteOrder()
              }else{
                  this.showAle = false
                  this.showm = false
              }
            },
            // 保存编辑内容
            save() {
                let that = this
                that.showm = true
                // [カスタムエイド 2026-10-22] 返事が来ないときも、20秒で操作できる状態に戻す(透明のふたが残って「戻る」も押せなくなっていた)
                clearTimeout(that.saveTimer)
                that.saveTimer = setTimeout(() => { that.showm = false }, 20000)
                try {
                    that.doSave()
                } catch (e) {
                    // 想定外のエラーで画面全体がロックされないようにする
                    that.showm = false
                    uni.showToast({ title: "保存できませんでした。もう一度お試しください", icon: "none" })
                }
            },
            doSave() {
                let that = this
                let days 
                if(that.info.day.indexOf("年") == -1){
                    days = that.info.day
                }else{
                    let day1 = that.info.day.replace(/年/, "-")
                    let day2 = day1.replace(/月/, "-")
                    days = day2.replace(/日/, " ")
                }
                
                let data = {}
                data._method = "put"
                data.mid = that.info.mid
                data.name = that.wai ? that.waiName : that.info.name // [カスタムエイド 2026-10-22]
                data.phone = that.info.phone
                data.sex = that.info.sex
                data.member_id = that.info.member_id
                // お休み・勤務時間外は menu が null で返るため 0 とする（null参照で透明マスクが残り操作不能になっていた）
                data.smid = that.menu && that.menu.id ? that.menu.id : 0
                data.start_time = days + " " + that.info.start
                data.end_time = days + " " + that.info.end
                data.remark = that.info.remark
               
                if (that.appoint_admin == null || that.appoint_admin.id == 0) {
                    data.appoint_said = 0
                    data.day_said = that.day_admin.id
                }

                if (that.appoint_admin != null && that.appoint_admin.id != 0) {
                    data.appoint_said = that.appoint_admin.id
                    data.day_said = that.appoint_admin.id
                }
                editAppointOrder(data, that.query.id).then((res) => {
                    that.showm = false
                    clearTimeout(that.saveTimer)
                    if(res.code == 200){
                        uni.showToast({
                            title:res.message
                        })
                        setTimeout(function() {
                            let pages = getCurrentPages();
                            let currPage = pages[pages.length - 1] 
                            let prePage = pages[pages.length - 2] 
                            if (prePage.route == "pages/appointment/appointment") {
                                prePage.$vm.reload()
                            }
                            uni.navigateBack()
                           // uni.redirectTo({
                           //     url:"../../appointment"
                           // })
                        }, 1500)
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                }).catch(() => {
                    that.showm = false
                    // [カスタムエイド 2026-10-22] 通信に失敗したときも何も出なかったので、お知らせを出す
                    uni.showToast({ title: "通信に失敗しました。電波の良い所でもう一度お試しください", icon: "none" })
                })
            },
            getStaff(appoint, source) {
                uni.showLoading({
                    title:"読み込み中"
                })
                let that = this
                let data = {}
                // day1 は日付を選び直した時にしか設定されないため、未設定なら info.day（"YYYY年MM月DD日" / "YYYY-MM-DD"）から同じ "YYYY/MM/DD" 形式を作る
                // （未設定のまま "undefined HH:mm" を送っていた）
                let date = that.info.day1
                if (!date && that.info.day) {
                    date = String(that.info.day).replace(/年|月/g, "/").replace(/日/, "").replace(/-/g, "/").trim()
                }
                data.start_time = date + " " + that.info.start
                data.end_time = date + " " + that.info.end
                data.id = appoint
                data.oid = that.info.id
                getTodayAdmin(data).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        if (source == 1) {
                            that.peolist = [...that.templist,...res.data.admins]
                        } else {
                            that.tplist = [...that.templist,...res.data.admins]
                            // that.peoShow2 = true
                        }
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                }).catch(() => { uni.hideLoading() }) // [カスタムエイド 2026-10-22] 失敗しても「読み込み中」を残さない
            },
            
            chooseMenu(index, idx) {
                let that = this
                let array = that.array
                array.forEach((value, index) => {
                    value.items.forEach((val, idx) => {
                        val.checked = false
                    })
                })
                that.array = array
                that.array[index].items[idx].checked = true
                that.wai = false
            },
            
            closeShow() {
                let that = this
                that.timeShow = false
                let array = that.array
                let total = 0
                let i
                let ix
                let menu = {}
                menu["id"] = 0
                menu["title"] = that.waiName
                if (that.wai == true) {
                    menu["order_time"] = 0
                    that.menu = menu
                    that.totalTime = 0
                    // 終了時刻は書き換えない（現在の開始〜終了の長さを保持）
                    that.waiDur = Math.max(that.diffMin(that.info.start, that.info.end), 0)
                } else {
                    array.forEach((value, index) => {
                        value.items.forEach((val, idx) => {
                            if (val.checked == true) {
                                total = val.time * 1
                                i = index
                                ix = idx
                            }
                        })
                    })
                    // 何も選択されていない（メニュー未読込・削除済みメニュー）場合は現状維持で閉じる
                    if (i === undefined) return
                    that.totalTime = total
                    that.menu = array[i].items[ix]
                    that.menu["order_time"] = total
                    that.menu["title"] = that.menu["name"]
                }
                that.getEndTime()
            },
            
            // "HH:mm" 2つの差(分)
            diffMin(start, end) {
                if (!start || !end) return 0
                let a = start.split(":")
                let b = end.split(":")
                return (b[0] * 60 + b[1] * 1) - (a[0] * 60 + a[1] * 1)
            },
            getEndTime() {
                let that = this
                // info.day1 は日付を選び直すまで未設定のため new Date(day1 + start) だと Invalid Date →「NaN:NaN」になる。
                // 終了時刻は "HH:mm" なので開始時刻から分単位で計算する（日付をまたぐ場合は従来どおり 24h で折り返し）
                let p = String(that.info.start || "").split(":")
                let t = p[0] * 60 + p[1] * 1 + (that.wai ? that.waiDur * 1 : that.totalTime * 1)
                if (!isNaN(t)) {
                    t = ((t % 1440) + 1440) % 1440
                    let hour = Math.floor(t / 60)
                    let minute = t % 60
                    that.info.end = (hour < 10 ? '0' + hour : hour) + ":" + (minute < 10 ? '0' + minute : minute)
                }
                if(that.menu){
                    that.getStaff(that.menu.id?that.menu.id:0, 1)
                }else{
                    that.getStaff(0, 1)
                }
                
            },

            // 获取订单详情
            getDetail(id) {
                let that = this
                getAppointOrderDetail(id).then((res) => {
                    if (res.code == 200) {
                        let d = {}
                        d.name = "指名無し"
                        d.color = "#d2d2d2"
                        d.id = 0
                        let tem = res.data.order.time.split("-")
                        res.data.order.start = tem[0]
                        res.data.order.end = tem[1]
                        that.info = res.data.order
                        that.day_admin = res.data.order.day_admin
                        that.appoint_admin = res.data.order.appoint_admin  || d
                        that.menu = res.data.order.menu
                        // お休み・勤務時間外(menu.id が 0/なし)の編集時は「お休み・勤務時間外」を
                        // 選択状態(wai=true)にして、メニュー選択でチェックが入るようにする 2026.7.29
                        that.wai = !(that.menu && that.menu.id)
                        // [カスタムエイド 2026-10-22] 名前が「お休み」ならお休み、それ以外(今までの空の名前を含む)は休憩・勤務時間外
                        that.waiName = res.data.order.name == 'お休み' ? 'お休み' : '休憩・勤務時間外'
                        if (!that.menu) {
                            that.menu = { id: 0, title: that.waiName, order_time: 0 }
                        }
                        that.waiDur = Math.max(that.diffMin(that.info.start, that.info.end), 0)
                        that.mid = res.data.order.mid
                        that.totalTime = that.menu.order_time || 0
                    }
                })
            },
            
            choosePeo(e) {
                let data = {}
                data['name'] = e.name
                data['id'] = e.id
                data['color'] = e.color || "#D2D2D7"
                this.todaystaff = data
                this.person = e
                this.peoShow2 = false
            },
            
            changeWai(name) {
                let that = this
                let array = that.array
                that.waiName = name == 'お休み' ? 'お休み' : '休憩・勤務時間外'
                that.wai = true
                that.array.forEach((value, index) => {
                    value.items.forEach((val, idx) => {
                        val.checked = false
                    })
                })
            
            },
            
            getCate() {
                let that = this
                menuCateList().then((res) => {
                    if (res.code == 200) {
                        menuList().then((result) => {
                            if (result.code == 200) {
                                let temp = []
                                res.data.cates.forEach((value, index) => {
                                    let obj = {}
                                    obj.name = value.name
                                    obj.items = []
                                    result.data.menus.forEach((val, idx) => {
                                        if (val.cid == value.id) {
                                            let object = {}
                                            object.name = val.title
                                            object.image = val.image
                                            object.price = val.price
                                            object.time = val.order_time
                                            // 現在選択中のメニューを初期チェック状態にする（開いた時にチェックが外れる不具合の修正）
                                            object.checked = !!(that.menu && val.id == that.menu.id)
                                            object.id = val.id
                                            object.cid = val.cid
                                            obj.items.push(object)
                                        }
                                    })
                                    temp.push(obj)
                                })
                                that.array = temp
                            }
                        })
                    }
                })
            },
            
            // 删除订单
            deleteOrder(){
               let that = this
               that.showm = true
               deleteAppointOrder(that.query.id).then((res) => {
                    that.showm = false
                   that.showAle = false
                   if(res.code == 200){
                        uni.showToast({
                            title:res.message
                        })
                        setTimeout(function() {
                            let pages = getCurrentPages();
                            let currPage = pages[pages.length - 1] //当前页面
                            let prePage = pages[pages.length - 2] //上一个页面
                            //调用上一页拉取数据的方法
                            if (prePage.route == "pages/appointment/notice/detail/detail") {
                                prePage.$vm.getDetail(that.query.id)
                            }
                            if(prePage.route == "pages/appointment/appointment"){
                                prePage.$vm.reload()
                            }
                            uni.navigateBack({})
                        }, 1500)
                   }else{
                       uni.showToast({
                           title:res.message,
                           icon:"none"
                       })
                   }
               })
            },

            showEnd() {
                let that = this
                // 通常メニュー・お休み/勤務時間外のいずれでも終了時間を編集できるようにする
                // （開始編集時の所要時間による自動計算は維持したまま、終了の手動調整を可能にする）
                that.endShow = true
            },
            // 选择开始时间
            selectStart(e) {
                let that = this
                that.info.start = e.value[0] + ":" + e.value[1]
                that.startShow = false
                // if (that.info.menu.order_time) {
                //     that.calculateDuration()
                // }
                that.getEndTime()
            },
            // 选择结束时间
            selectEnd(e) {
                let that = this
                let end = e.value[0] + ":" + e.value[1]
                // 終了時間が開始時間より前（または同じ）にならないよう検証
                if (that.info.start && end <= that.info.start) {
                    uni.showToast({
                        title: '終了時間は開始時間より後に設定してください',
                        icon: 'none'
                    })
                    return
                }
                that.info.end = end
                if (that.wai) that.waiDur = that.diffMin(that.info.start, end)
                that.endShow = false
            },
            // 查看顾客详情
            toCustomer() {
                uni.navigateTo({
                    url: "../../../customer/detail/detail?id="+this.mid
                })
            },
            // 日历确认日期
            confirmDate(e) {
                let that = this
                let t = e[0]
                let t1 = t.split("-")
                let str = ""
                for(let i = 0; i < t1.length;i++){
                    if(i == t1.length -1){
                        str+=t1[i]
                    }else{
                        str+=t1[i]+"/"
                    }
                }
                that.info.day = e[0]
                that.info.day1 = str
                that.calendarShow = false
                that.getEndTime()
            },
            getDang(){
                let that = this
                that.getStaff(that.menu.id,2)
                if(that.appoint_admin){
                    if(that.appoint_admin.id){
                        // uni.showToast({
                        //     title:"无法选择当日担当",
                        //     icon:"none"
                        // })
                    }else{
                        that.peoShow2 = true
                    }
                }else{
                  that.peoShow2 = true
                }
              
                // if(that.appoint_admin.id){
                // }else{
                //     that.getStaff(that.menu.id,0)
                // }
            },
            
            // 获取员工列表
            getZhi() {
                let that = this
                that.peoShow = true
                that.getStaff(that.menu.id,1)
            },
            // 选择指名员工
            chooseZ(e, index) {
                let that = this
                if (e.color == '') {
                    e.color = "#175491"
                }
                that.peolist.forEach((val, idx) => {
                    if (index == idx) {
                        val.checked = true
                    } else {
                        val.checked = false
                    }
                })
                that.appoint_admin = e
                that.day_admin = e
            },
            // 选择当日担当
            chooseD(e, index) {
                let that = this
                if (e.color == '') {
                    e.color = "#175491"
                }
                that.tplist.forEach((val, idx) => {
                    if (index == idx) {
                        val.checked = true
                    } else {
                        val.checked = false
                    }
                })
                that.day_admin = e
            }
        }
    }
</script>

<style lang="scss" scoped>
    .peoItem {
        background: #fff;
        border-bottom: 2upx solid #d2d2d7;
        display: flex;
        box-sizing: border-box;
        padding: 60upx 30upx;

        .peoSqu {
            width: 40upx;
            height: 40upx;
            border-radius: 8upx;
            margin-left: 36upx;
            margin-right: 20upx;
        }
    }
    .cccc{
        opacity: 0.6 !important;
        color: #1A73E8;
    }
    .vvvvv{
        opacity: 0.6;
        color: #D93025;
    }
    .msk{
        position: fixed;
        width: 100vw;
        height: 100vh;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        z-index: 10999 !important;
    }
    .cConfirm {
        font-size: 40upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
        text-align: center;
        box-sizing: border-box;
        padding: 72upx 0 186upx 0;
    }
    .ho1{
        opacity: 0.6 !important;
    }
    .selectContent {
        margin-right: 32upx;
    }
    
    .mask{
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(29,29,31,0.2);
        z-index: 999;
        
        .maskcontent{
            width: 320upx;
            height: 300upx;
            background: #fff;
            border-radius: 20upx;
            text-align: center;
            box-sizing: border-box;
            padding: 60upx;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            position: absolute;
            top: 40%;
            left: 50%;
            transform: translate(-50%,-50%);
            
            .mi{
                width: 100upx;
                height: 100upx;
                margin-bottom: 40upx;
            }
            
        }
    }
    

    .detail {
        box-sizing: border-box;

        .managerName {
            box-sizing: border-box;
            padding: 36upx 40upx 40upx;
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            box-shadow: 0px 4px 12upx 0upx rgba(0, 0, 0, 0.16);
            z-index: 99;
        }

        .selectAll {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 60upx 32upx;
        }

        .allname {
            color: #D93025 !important;
        }


        .sDitem {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 40upx 32upx;
            border-top: 2upx solid #d2d2d7;


            .sDitemRight {
                .sDtop {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                    margin-bottom: 32upx;
                }

                .sDbottom {
                    display: flex;

                    .sDimage {
                        width: 200upx;
                        height: 200upx;
                        margin-right: 32upx;
                        border-radius: 12upx;
                        overflow: hidden;

                        image {
                            width: 100%;
                            height: 100%;
                        }
                    }

                    .sDinfo {
                        display: flex;
                        font-size: 28upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #1D1D1F;

                        .sDprice {
                            color: #D93025;
                            margin-right: 70upx;
                        }
                    }

                }
            }
        }

        .sDtitle {
            width: 750upx;
            height: 104upx;
            background: #F5F5F7;
            opacity: 1;
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #86868B;
            line-height: 104upx;
            box-sizing: border-box;
            padding: 0 32upx;
            border-top: 2upx solid #d2d2d7;
        }
    }
    
    .tips {
        box-sizing: border-box;
        padding: 28upx;
        background: #fff;
        color: #1D1D1F;
        border-radius: 20upx;
        font-size: 28upx;
        margin-top: 80upx;
    
        .connect {
            color: #1A73E8;
            margin-top: 20upx;
        }
    }
    .mkitem{
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        width: 90%;
        margin: 20upx auto;
        border-radius: 28upx;
        box-sizing: border-box;
        padding: 36upx;
        font-size:40upx;
        image{
            width: 44upx;
            height: 44upx;
        }
    }
    // ::v-deep .u-popup__content{
    //     background: transparent !important;
    // }
    .maskContent{
        width: 100vw;
        background: transparent;
    }
    /* [カスタムエイド 2026-10-22] 予約内容(余白はほかの画面と同じ決まり) */
    .ed-pg { padding: 8rpx 0 calc(96rpx + env(safe-area-inset-bottom)); }
    .ed-card { background: #FFFFFF; border-radius: 32rpx; padding: 0 32rpx; margin-bottom: 56rpx; } /* [カスタムエイド 2026-10-22] カードのあいだを広めに */
    .ed-pad { padding: 32rpx; }
    .ed-sec { font-size: 26rpx; color: #6E6E73; padding: 0 8rpx; margin-bottom: 16rpx; line-height: 1.2; }
    .ed-row { display: flex; align-items: center; justify-content: space-between; min-height: 104rpx; border-bottom: 1px solid #E5E5EA; }
    .ed-row:last-child { border-bottom: 0; }
    .ed-lab { flex-shrink: 0; width: 180rpx; font-size: 30rpx; color: #1D1D1F; }
    .ed-val { flex: 1; text-align: right; font-size: 30rpx; color: #1D1D1F; word-break: break-all; }
    .ed-link { color: #1A73E8; }
    .ed-gray { color: #6E6E73; }
    .ed-in { flex: 1; height: 104rpx; text-align: right; font-size: 30rpx; color: #1D1D1F; }
    .ed-ph { color: #AEAEB2; }
    .ed-chip { padding: 14rpx 24rpx; border-radius: 16rpx; background: #F2F2F7; font-size: 30rpx; color: #1D1D1F; }
    .ed-person { display: flex; align-items: center; }
    .ed-dot { width: 28rpx; height: 28rpx; border-radius: 8rpx; flex-shrink: 0; }
    .ed-pname { font-size: 30rpx; color: #6E6E73; margin: 0 12rpx 0 16rpx; }
    .ed-menu { display: flex; align-items: center; justify-content: space-between; padding: 32rpx; }
    .ed-menu-l { flex: 1; margin-right: 16rpx; }
    .ed-menu-t { font-size: 30rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.4; }
    .ed-menu-s { font-size: 24rpx; color: #6E6E73; margin-top: 8rpx; line-height: 1.5; }
    .ed-price { color: #D93025; margin-left: 24rpx; }
    .ed-red { color: #D93025; }
    .ed-green, .detail .allname.ed-green { color: #137333 !important; }
    .ed-ta { width: 100%; height: 200rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 24rpx 28rpx; font-size: 30rpx; line-height: 1.6; box-sizing: border-box; display: block; }
    .ed-btn { margin-top: 64rpx; height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif; display: flex; align-items: center; justify-content: center; }
    .ed-del { margin-top: 48rpx; height: 104rpx; border-radius: 28rpx; background: #FFFFFF; color: #D93025; font-size: 32rpx; display: flex; align-items: center; justify-content: center; }
    .ed-hover { opacity: 0.6; }
    .ed-x { position: absolute; right: 24rpx; top: 20rpx; width: 64rpx; height: 64rpx; border-radius: 50%; background: #E5E5EA; color: #6E6E73; font-size: 36rpx; line-height: 64rpx; text-align: center; z-index: 2; }

    .title {
        width: 100%;
        height: 112upx;
        background: #F5F5F7;
        border-top-left-radius: 32upx;
        border-top-right-radius: 32upx;
        text-align: center;
        line-height: 112upx;
        display: flex;
        justify-content: center;
        position: relative;
        font-size: 40upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        position: sticky;
        top: 0;
        left: 0;
        z-index: 999;
        border-bottom: 2upx solid #d2d2d7;
        font-size: 34rpx;

        image {
            width: 60upx;
            height: 60upx;
            position: absolute;
            top: 26upx;
            left: 32upx;
        }
    }

    .titleName {
        font-size: 34rpx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
    }

    .citem {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 20upx 0;

        .citemLeft {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .citemRight {
            // flex: 1;

            .cperson {
                display: flex;
                align-items: center;

                .pcolor {
                    width: 32upx;
                    height: 32upx;
                    border-radius: 8upx;
                    flex-shrink: 0;
                }

                .pname {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #6E6E73;
                    margin-left: 20upx;
                    margin-right: 38upx;
                }
            }
        }

        .dateDetail {
            box-sizing: border-box;
            padding: 20upx;
            border-radius: 20upx;
            background: #F5F5F7;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }
    }

    .pickers {
        display: flex;
        align-items: center;
        box-sizing: border-box;
        justify-content: space-between;
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
    }

    .sexItem {
        text-align: center;
        font-size: 40upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        box-sizing: border-box;
        padding: 30upx 0;
    }

    .contains {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        background: #F2F2F7;
        z-index: 999;
        overflow-y: scroll;
        box-sizing: border-box;
        padding: 32upx;

        .ct {
            width: 100%;
            background: #fff;
            box-sizing: border-box;
            padding: 60upx 32upx;
            border-radius: 20upx;
            border: 2upx solid #D2D2D7;
            margin-bottom: 40upx;

            .ctItem {
                display: flex;
                margin-top: 56upx;
                align-items: center;

                .itemName {
                    width: 140upx;
                    flex-shrink: 0;
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                }

                .itemDetail {
                    flex: 1;

                    .picker {
                        width: 420upx;
                        height: 84upx;
                        background: #F5F5F7;
                        border-radius: 20upx;
                        opacity: 1;
                        border: 2upx solid #AAAAAA;
                        font-size: 28upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #AAAAAA;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        box-sizing: border-box;
                        padding: 0 20upx;
                    }
                }
            }
        }
    }
</style>
