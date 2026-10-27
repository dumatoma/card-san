<template>
    <!-- [カスタムエイド 2026-10-26] 開くときは右から横にスライドして入る/「<」で戻るときは右へスライドして出る(ほかの画面の移り方と同じに。以前はぱっと出ていた) -->
    <view class="contains" :class="{ 'cs-leave': csLeaving }">
        <u-navbar title="新規予約" border @leftClick="handleBack" :safeAreaInsetTop="true" bgColor="rgb(251,251,253)" :placeholder="true">
        </u-navbar>
        <!-- [カスタムエイド 2026-10-22] お休み・休憩はお客さまがいないので、お客さまの欄を出さない -->
        <view class="ct" v-if="!wai">
           <!-- <u--input placeholder="" disabled="true" @click="searchMem" prefixIcon="search"
                prefixIconStyle="font-size: 22px;color: #909399">
            </u--input> -->
            <view class="searchp" @click="searchMem">
                <u-icon name="search" color="#d2d2d7" size="24"></u-icon>
                <view>名前、電話番号、IDで検索</view>
            </view>
            <view class="ctItem">
                <view class="itemName">
                    氏名
                </view>
                <view class="itemDetail">
                    <u--input v-model="info.name" @change="insert1" placeholder="名前" border="bottom" clearable>
                    </u--input>
                </view>
            </view>
            <view class="ctItem">
                <view class="itemName">
                    電話番号
                </view>
                <view class="itemDetail">
                    <u--input v-model="info.phone" @change="insert2" placeholder="入力してください" border="bottom" clearable>
                    </u--input>
                </view>
            </view>
            <view class="ctItem">
                <view class="itemName">
                    性别
                </view>
                <view class="itemDetail">
                    <view class="picker" @click="show = true">
                        <view v-text="sexname"></view>
                        <u-icon name="arrow-down"></u-icon>
                    </view>
                </view>
            </view>
            <view class="ctItem">
                <view class="itemName">
                    会員ID
                </view>
                <view class="itemDetail">
                    <u--input border="none" disabledColor="#fff" disabled v-model="ID" clearable>
                    </u--input>
                </view>
            </view>
        </view>
        <view class="ct">
            <view class="pickers" @click="timeShow = true">
                <view v-if="menu.id == -1">
                    メニューを選択
                </view>
                <view v-else v-text="menu.name" :class="{ 'cs-off': waiType == 'off', 'cs-brk': waiType == 'break' }"></view>
                <u-icon name="arrow-right"></u-icon>
            </view>
        </view>
        <view class="ct" style="padding: 20upx 32upx;">
            <view class="citem border_bottom">
                <view class="citemLeft">
                    日付
                </view>
                <view class="citemRight">
                    <view class="dateDetail" @click="calendarShow = true" v-text="date"></view>
                </view>
            </view>

            <!-- [カスタムエイド 2026-10-22] お休み:時間は営業時間から自動で決まる(変えられない) -->
            <view class="citem border_bottom" v-if="waiType == 'off'">
                <view class="citemLeft">
                    時間
                </view>
                <view class="citemRight">
                    <view class="cs-lock" v-text="offLabel"></view>
                </view>
            </view>
            <view class="citem border_bottom" v-if="waiType != 'off'">
                <view class="citemLeft">
                    開始
                </view>
                <view class="citemRight">
                    <view class="dateDetail" v-text="start" @click="menu.id != -1 ? tdOpen('start') : ''"></view>
                </view>
            </view>

            <view class="citem border_bottom" v-if="waiType != 'off'">
                <view class="citemLeft">
                    終了
                </view>
                <view class="citemRight">
                    <view class="dateDetail" v-text="end" @click="showEnd"></view>
                </view>
            </view>

            <view class="citem border_bottom" v-if="aviliable == true && !wai" @click="openList">
                <view class="citemLeft">
                    指名担当
                </view>
                <view class="citemRight">
                    <view class="cperson">
                        <view class="pcolor" :style="`background:${person.color}`"></view>
                        <view class="pname" v-text="person.name"></view>
                        <u-icon name="arrow-right"></u-icon>
                    </view>
                </view>
            </view>
            
            
            
            <view class="citem border_bottom" @click="ct">
                <view class="citemLeft" v-text="wai ? '担当者' : '当日担当'">
                </view>
                <view class="citemRight">
                    <view class="cperson">
                        <view class="pcolor" :style="`background:${todaystaff.color}`"></view>
                        <view class="pname" v-text="todaystaff.name"></view>
                        <u-icon name="arrow-right"></u-icon>
                    </view>
                </view>
            </view>
        </view>
        <view class="ct" style="padding-top: 20upx;padding-bottom: 20upx;">
            <view>メモ</view>
            <u-textarea placeholder="メモがあれば入力してください" v-model="remark" confirmType="done" border="none" :height="100"></u-textarea>
        </view>
        <view class="cs-note" v-if="waiType == 'off'">その日の営業時間をすべて止めます。昼休みなどで営業時間が2つに分かれている日は、両方とも止まります。お客さまの予約画面では、この担当者はこの日に選べなくなります。この日に予約が入っているときは、お休みにできません。</view>
        <!-- @click="saveData" -->
        <view class="cConfirm" hover-class="savehover" @click="askSave" v-text="waiType == 'off' ? 'お休みにする' : waiType == 'break' ? '休憩・営業時間外にする' : 'この内容で予約する'">
        </view>
        <!-- 选择性别弹出层 -->
        <u-popup :show="show" @close="show = false" mode="bottom" bgColor="#f9f9f9">
            <view class="sexItem" v-for="(item,index) in columns" hover-class="chan" v-text="item" @click="chooseSex(item,index)"></view>
        </u-popup>
        <!-- 选择时间弹出层 -->
        <u-popup :show="timeShow" @close="closeShow" mode="bottom" :safeAreaInsetTop='false' bgColor="f9f9f9"
            :round="16">
            <view style="height: 90vh;width: 100%;overflow-y: scroll;position: relative;">
                <view class="title">
                    <image src="../../static/icons/tclose.png" @click="closeShow" mode=""></image>
                    <view class="titleName">メニュー選択</view>
                </view>
                <view class="detail">
                    <!-- [カスタムエイド 2026-10-22] 「お休み・勤務時間外」を「お休み(1日)」と「休憩・勤務時間外(時間を決める)」に分けた -->
                    <view class="selectAll cs-wai" @click="changeWai('off')">
                        <view class="selectContent">
                            <view class="unselect" v-if="waiType != 'off'"></view>
                            <view class="selected" v-if="waiType == 'off'">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view>
                            <view class="allname">お休み</view>
                            <view class="cs-wai-d">担当者のその日を、営業時間の始めから終わりまで全部止めます</view>
                        </view>
                    </view>
                    <view class="selectAll cs-wai" @click="changeWai('break')">
                        <view class="selectContent">
                            <view class="unselect" v-if="waiType != 'break'"></view>
                            <view class="selected" v-if="waiType == 'break'">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view>
                            <view class="allname cs-brk">休憩・勤務時間外</view>
                            <view class="cs-wai-d">休憩・遅い出勤・早い退勤など、時間を決めて止めます</view>
                        </view>
                    </view>
                    <view class="settingD">
                        <view class="sDitems" v-for="(item,index) in array" v-if="item.items.length > 0">
                            <view class="sDtitle" v-text="item.name"></view>
                            <view class="sDitem" v-for="(ite,idx) in item.items">
                                <view class="selectContent" @click="chooseMenu(index,idx)" v-if="ite.is_order == 1">
                                    <view class="unselect" v-if="!ite.checked"></view>
                                    <view class="selected" v-if="ite.checked">
                                        <view class="fill"></view>
                                    </view>
                                </view>
                                <view class="sDitemRight">
                                    <view class="sDtop" v-text="ite.name"></view>
                                    <view class="sDbottom">
                                        <view class="sDimage" v-if="ite.image">
                                            <image :src="ite.image[0]" mode="aspectFill"></image>
                                        </view>
                                        <view class="sDinfo" v-if="ite.is_order == 1">
                                            <view class="sDprice" v-text="`${ite.price}`"></view>
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
        <!-- 开始时间弹出层 -->
        <!-- [カスタムエイド 2026-10-22] 新しいドラムロール(見出し・選んでいる時刻を大きく・2けた・その日の営業時間の中だけ) -->
        <cs-picker :show="startShow" ref="startPicker" title="開始時間" sep=":" :columns="tdCols" :defaultIndex="tdDefault" :note="tdNote" @close="startShow = false" @change="tdHourChange($event, 'startPicker')"
            @cancel='startShow = false' @confirm="changeHandler"></cs-picker>
        <!-- 结束时间弹出层 -->
        <cs-picker :show="endShow" ref="endPicker" title="終了時間" sep=":" :columns="tdCols" :defaultIndex="tdDefault" :note="tdNote" @close="endShow = false" @change="tdHourChange($event, 'endPicker')"
            @cancel='endShow = false' @confirm="changeHandler1"></cs-picker>
        <!-- 日历弹出层 -->
        <u-calendar :show="calendarShow" @confirm="dateConfirm" @close="calendarShow = false" monthNum="24" :defaultDate="dDate"></u-calendar>
        <!-- 选人弹出层(指名担当者) -->
        <u-popup :show="peoShow" round="16">
            <view style="height: 60vh;width: 100%;overflow-y: scroll;position: relative;">
                <view class="title">
                    <image src="../../static/icons/tclose.png" mode="aspectFill" @click="peoShow = false"></image>
                    <view class="titleName">担当者を選択</view>
                </view>
                <view class="peoContent">
                    <view class="peoItem" v-for="(item,index) in peolist">
                        <view class="selectContent" @click="chooseStaff(index,item)">
                            <view class="unselect" v-if="!item.checked"></view>
                            <view class="selected" v-if="item.checked">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="peoSqu" v-if="item.color != ''" :style="`background:${item.color}`"></view>
                        <view class="peoSqu" v-if="item.color == ''" style="background:#1A73E8"></view>
                        <view class="peoName" v-text="item.name"></view>
                    </view>
                </view>
            </view>
        </u-popup>
        <!-- 搜索弹窗 -->
        <u-popup :show="searchShow" round="16">
            <view scroll-y="true" style="width: 100%;position: relative;">
                <view class="title">
                    <image src="../../static/icons/tclose.png" mode="" @click="searchShow = false"></image>
                    <view class="titleName">顧客一覧</view>
                </view>
                <view class="searchContent">
                    <u--input v-model="keywords" @confirm="confirmSearch" placeholder="名前、電話番号、IDで検索"
                        prefixIcon="search" prefixIconStyle="font-size: 22px;color: #909399">
                    </u--input>
                </view>
                <scroll-view class="searchList" @scrolltolower="scrollToBottom" scroll-y="true">
                    <view class="searchItem" v-for="(item,index) in searchList">
                        <view class="sitemLeft">
                            <image :src="item.avatar" mode="aspectFill"></image>
                        </view>
                        <view class="sitemMiddle">
                            <view v-text="item.name"></view>
                            <view v-text="`${item.pronunciation}・${item.phone}`"></view>
                            <view>
                                顧客NO:{{item.member_no}}・ID:{{item.member_id}}
                            </view>
                        </view>
                        <view class="sitemRight" @click="selectItem(index)">
                            選択
                        </view>
                    </view>
                </scroll-view>
            </view>
        </u-popup>
        <!-- 选人弹出框(当日担当) -->
        <u-popup :show="peoShow2" round="16">
            <scroll-view scroll-y="true" style="height: 1000upx;width: 100%;position: relative;">
                <view class="title">
                    <image src="../../static/icons/tclose.png" mode="" @click="peoShow2 = false"></image>
                    <view class="titleName">担当者を選択</view>
                </view>
                <view class="peoContent">
                    <view class="peoItem" v-for="(item,index) in p2">
                        <view class="selectContent" @click="choosePeot(item,index)">
                            <view class="unselect" v-if="!item.checked"></view>
                            <view class="selected" v-if="item.checked">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="peoSqu" v-if="item.color != ''" :style="`background:${item.color}`"></view>
                        <view class="peoSqu" v-if="item.color == ''" style="background:#1A73E8"></view>
                        <view class="peoName" v-text="item.name"></view>
                    </view>
                </view>
            </scroll-view>
        </u-popup>
        <view class="mask" v-if="showmask == true">
            <view class="maskcontent">
                <image src="../../static/images/blueright.png" class="mi" mode=""></image>
                <view v-text="maskText">
                </view>
            </view>
        </view>
        <mod :config="config" v-if="showAle == true" @getStatus="changeStatus"></mod>
        <view class="msk" v-if="showm"></view>
    </view>
</template>
<script>
    import mod from "@/components/mod.vue"
    import csPicker from "@/components/csPicker/csPicker.vue" // [カスタムエイド 2026-10-22]
    import csTimeDrum from "@/utils/csTimeDrum.js"
    import {
        getMemberList,
        menuCateList,
        menuList,
        getStaffList,
        getShopInfo,
        addNewAppoint,
        getCalendar,
        getAppointmentList
    } from "@/api/index.js"
    // [カスタムエイド 2026-10-22] お休み・休憩の名前(サーバーはこの2つの名前だけ保存する)
    var OFF_NAME = "お休み"
    var BREAK_NAME = "休憩・勤務時間外"
    export default {
        props: {
            time: {
                type: String,
                default: "00:00"
            },
            ddate:{
                type: String,
                default: ""
            },
            adid:{
                type: Number,
                default: 0
            },
            aptype:{
                type: String,
                default: ""
            }
        },
        components:{mod, csPicker},
        mixins: [csTimeDrum],
        data() {
            return {
                csLeaving: false, // [カスタムエイド 2026-10-26] 右へスライドして出ている途中
                showm:false,
                p2:[],
                config:{
                    title:"予約を決定しますか？",
                    content:"決定すると、この予約情報はお客様に送信されます。",
                    type:"buttoin",
                    confirm:true,
                    cancel:true,
                    confirmText:"予約する",
                    cancelText:"キャンセル"
                },
                showmask:false,
                showAle:false,
                phone: "",
                dDate:"",
                page: 1,
                show: false,
                timeShow: false,
                startShow: false,
                endShow: false,
                calendarShow: false,
                peoShow: false,
                peoShow2: false,
                keywords: "",
                searchShow: false,
                info: {},
				tempInfo:{},
                wai: false,
                waiType: "", // [カスタムエイド 2026-10-22] 'off' = お休み(1日) / 'break' = 休憩・勤務時間外
                offRanges: [], // お休みで止める時間(営業時間)
                offLabel: "読み込み中…",
                maskText: "予約しました",
                menu: {
                    id: -1
                },
                aviliable: false,
                columns: ['男性', '女性', 'Xジェンダー'],
                sexname: "選択",
                ID: "",
                tel: "CB236587100912",
                date: "",
                start: "00:00",
                end: "00:00",
                remark: "",
                person: {
                    color: "#D2D2D7",
                    name: "指名なし",
                    id: 0
                },
                todaystaff: {},
                searchList: [],
                peolist: [{
                    name: "指名なし",
                    color: "#d2d2d7",
                    checked: true,
                    id: 0
                }, ],
                selectAll: false,
                array: []
            };
        },
        created() {
            let that = this
            that.start = that.time
            that.dDate = that.ddate
            that.date = that.ddate
            that.getCustomerList()
            console.log(that.adid)
            that.getCate()
            that.getShopInfo()
            let d = {}
            d.id = ""
            d.start_time = ""
            d.end_time = ""
            getStaffList(d).then((res) => {
                if(res.code == 200){
                    if(that.adid == 0){
                        let s = {}
                        s.name = "指名なし"
                        s.color = "#d2d2d7"
                        s.id = ""
                        that.todaystaff = s
                        that.person = s
                    }else{
                        if(that.aptype == 'week'){
                            res.data.admins.forEach((v,i) => {
                                if(v.id == that.adid){
                                    let temp = {}
                                    temp['color'] = v.color || 'rgb(26, 115, 232)'
                                    temp['id'] = v.id
                                    temp['name'] = v.name
                                    that.todaystaff = temp
                                    that.person = temp
                                }
                            })
                        }else{
                            res.data.admins.forEach((v,i) => {
                                if(v.id == that.adid){
                                    let temp = {}
                                    temp['color'] = v.color || "rgb(26, 115, 232)"
                                    temp['id'] = v.id
                                    temp['name'] = v.name
                                    that.todaystaff = temp
                                }
                            })
                        }
                    } 
                }
            })
        },
        onShow() {
           console.log("shwo") 
        },
        methods: {
            // getStaff(){
            //    let that = this 
            // },
            getShopInfo() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        that.aviliable = res.data.shop_info.is_appoint == 1 ? true : false
                    }
                })
            },
            changeStatus(e){ 
              if(e == "cancel"){
                  this.showAle = false
              }else{
                  this.saveData()
              }
            },
            handleBack() {
                // [カスタムエイド 2026-10-26] 右へスライドして出てから閉じる
                if (this.csLeaving) return
                this.csLeaving = true
                setTimeout(() => { this.$emit("cancel", "close") }, 260)
            },

            // [カスタムエイド 2026-10-22] 確認のダイアログ(お休み・休憩は文言を変える)
            askSave() {
                let that = this
                if (that.waiType == 'off') {
                    if (!that.todaystaff || !that.todaystaff.id) {
                        uni.showToast({ title: "担当者を選択してください", icon: "none" })
                        return
                    }
                    if (!that.offRanges.length) {
                        uni.showToast({ title: that.offLabel == "定休日" ? "この日は定休日です" : "止める時間がありません(営業時間が終わっています)", icon: "none" })
                        return
                    }
                    that.config = Object.assign({}, that.config, { title: "お休みにしますか？", content: that.date + " の " + that.todaystaff.name + " さんを、営業時間のあいだすべて止めます。", confirmText: "お休みにする" })
                } else if (that.waiType == 'break') {
                    that.config = Object.assign({}, that.config, { title: "休憩・営業時間外に設定しますか？", content: that.date + " " + that.start + "〜" + that.end + " を、休憩・勤務時間外にします。", confirmText: "設定する" }) // [カスタムエイド 2026-10-25] 文言を「休憩・営業時間外に設定」に
                } else {
                    that.config = Object.assign({}, that.config, { title: "予約を決定しますか？", content: "決定すると、この予約情報はお客様に送信されます。", confirmText: "予約する" })
                }
                that.showAle = true
            },
            // [カスタムエイド 2026-10-22] お休みで止める時間 = その日の営業時間(今日なら今より後だけ)
            loadOffRanges() {
                let that = this
                that.offRanges = []
                that.offLabel = "読み込み中…"
                let d = String(that.date || "")
                if (!d) return
                getCalendar({ date: d.slice(0, 7) + "-01", type: "day" }).then((res) => {
                    if (!res || res.code != 200) { that.offLabel = "読み込みに失敗しました"; return }
                    let day = (res.data.day_business || [])[d.slice(8, 10) * 1] || {}
                    if (day.business == 0) { that.offLabel = "定休日"; return }
                    let list = (day.business_time || []).filter((v) => v && v.start && v.end)
                    if (!list.length) list = [{ start: "09:00", end: "22:00" }]
                    let toMin = (t) => { let a = String(t).split(":"); return a[0] * 60 + a[1] * 1 }
                    let toStr = (m) => { let h = Math.floor(m / 60), n = m % 60; return (h < 10 ? "0" + h : h) + ":" + (n < 10 ? "0" + n : n) }
                    // 今日は、今より後(30分単位で切り上げ)だけ止める(サーバーは過去の時間を受け付けない)
                    let now = new Date()
                    let today = now.getFullYear() + "-" + ("0" + (now.getMonth() + 1)).slice(-2) + "-" + ("0" + now.getDate()).slice(-2)
                    let floor = d.slice(0, 10) == today ? Math.ceil((now.getHours() * 60 + now.getMinutes() + 1) / 30) * 30 : 0
                    let ranges = []
                    list.forEach((v) => {
                        let s = Math.max(toMin(v.start), floor), e = toMin(v.end)
                        if (e > s) ranges.push({ start: toStr(s), end: toStr(e) })
                    })
                    that.offRanges = ranges
                    if (!ranges.length) { that.offLabel = "営業時間が終わっています"; return }
                    that.offLabel = ranges.map((r) => r.start + "〜" + r.end).join("・") + "(終日)"
                    that.start = ranges[0].start
                    that.end = ranges[ranges.length - 1].end
                    that.getStaff(0, 1)
                }).catch(() => { that.offLabel = "読み込みに失敗しました" })
            },
            // [カスタムエイド 2026-10-22] お休みの登録:その日に予約があれば止めずに知らせる。なければ営業時間ごとに止める
            saveOff() {
                let that = this
                that.showm = true
                let staff = that.todaystaff
                let fail = (msg) => {
                    that.showm = false
                    that.showAle = false
                    uni.showModal({ title: "お休みにできません", content: msg, showCancel: false, confirmText: "閉じる" })
                }
                getAppointmentList({ date: that.date, id: 0, type: "day" }).then((res) => {
                    if (!res || res.code != 200) return fail((res && res.message) || "読み込みに失敗しました。もう一度お試しください")
                    let admin = (res.data.admins || []).find((a) => a.id == staff.id)
                    let orders = (admin && admin.orders) || []
                    if (orders.length) {
                        let lines = orders.map((o) => o.start_time + "〜" + o.end_time + " " + (o.name || (o.cate_name ? "" : BREAK_NAME) || "(名前なし)"))
                        return fail(that.date + " の " + staff.name + " さんには、次の予約が入っています。\n" + lines.join("\n") + "\n先に予約の担当者を変えるか、取り消してください。")
                    }
                    let i = 0
                    let next = () => {
                        if (i >= that.offRanges.length) {
                            that.showAle = false
                            that.showm = false
                            that.maskText = "お休みにしました"
                            that.showmask = true
                            setTimeout(() => { that.$emit("cancel", "refresh") }, 1500)
                            return
                        }
                        let r = that.offRanges[i++]
                        addNewAppoint({
                            name: OFF_NAME, phone: "", sex: 0, member_id: "", smid: 0, mid: "",
                            start_time: that.date + " " + r.start, end_time: that.date + " " + r.end,
                            remark: that.remark, appoint_said: staff.id, day_said: staff.id
                        }).then((re) => {
                            if (re && re.code == 200) return next()
                            fail((re && re.message) || "お休みにできませんでした")
                            if (i > 1) that.$emit("cancel", "refresh")
                        }).catch(() => fail("通信に失敗しました。電波の良い所でもう一度お試しください"))
                    }
                    next()
                }).catch(() => fail("通信に失敗しました。電波の良い所でもう一度お試しください"))
            },
            saveData() {
                let that = this
                if (that.waiType == 'off') return that.saveOff()
                that.showm = true
                let data = {}
                data.name = that.waiType == 'break' ? BREAK_NAME : that.info.name
                data.phone = that.info.phone
                data.sex = that.info.sex
                data.member_id = that.ID || ''
                data.smid = that.menu.id
                data.start_time = that.date + " " + that.start
                data.end_time = that.date + " " + that.end
                data.remark = that.remark
                if (that.person.id) {
                    data.appoint_said = that.person.id
                    data.day_said = that.person.id
                } else {
                    data.appoint_said = ""
                    data.day_said = that.todaystaff.id
                }
				if(that.tempInfo.name == that.info.name && that.tempInfo.phone == that.info.phone && that.tempInfo.sex == that.info.sex){
					data.mid = that.info.id
				}else{
					data.mid = ""
				}
                addNewAppoint(data).then((res) => {
                    that.showAle = false
                    that.showm = false
                    if (that.waiType == 'break') that.maskText = "休憩・営業時間外に設定しました" // [カスタムエイド 2026-10-25]
                    if (res.code == 200) {
                        that.showmask = true
                        setTimeout(function() {
                            that.$emit("cancel", "refresh")
                        }, 1500)
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                })
            },

            showEnd() {
                let that = this
                if (that.wai == true) {
                    that.tdOpen('end') // [カスタムエイド 2026-10-22]
                }
            },

            openList() {
                let that = this
                if (that.menu.id == -1) {
                    uni.showToast({
                        title: "メニューを選択してください",
                        icon: "none"
                    })
                } else {
                    that.getStaff(that.menu.id, 2)
                }

            },
            
            ct(){
              let that = this
              if(that.menu.id == -1){
                  uni.showToast({
                      title: "メニューを選択してください",
                      icon: "none"
                  })
              }else{
                  if(that.person.id){
                      
                  }else{
                      that.getStaff(that.menu.id, 1)
                      that.peoShow2 = true
                  }
              }
              
            },

            changeWai(type) {
                let that = this
                let array = that.array
                that.end = "00:00"
                that.wai = true
                that.waiType = type == 'off' ? 'off' : 'break' 
                that.array.forEach((value, index) => {
                    value.items.forEach((val, idx) => {
                        val.checked = false
                    })
                })
            },

            choosePeo(e) {
                console.log(e)
                let that = this
                let data = {}
                data['name'] = e.name
                data['id'] = e.id
                if(e.id == 0){
                    data['color'] = "#D2D2D7"
                }else{
                    data['color'] = e.color || "rgb(26, 115, 232)"
                }
                
                this.person = data
                // this.peoShow = false
                // this.peoShow2 = false
                if(e.id == 0){
                    this.todaystaff = that.p2[0]
                }else{
                    this.todaystaff = data
                }  
            },
            
            choosePeot(e,index){
                let data = {}
                data['name'] = e.name
                data['id'] = e.id
                data['color'] = e.color || "rgb(26, 115, 232)"
                this.todaystaff = data
                this.p2.forEach((v,i) => {
                    if(i == index){
                        v.checked = true
                    }else{
                        v.checked = false
                    }
                })
                // this.person = e
                // this.peoShow2 = false
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
                                            object.time = val.order_time
                                            object.checked = false
                                            object.id = val.id
                                            object.cid = val.cid
                                            object.is_order = val.is_order
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
            getStaff(appoint, source) {
                let that = this
                let data = {}
                let date = that.date
                let ar = []
                data.start_time = date + " " + that.start
                data.end_time = date + " " + that.end
                data.id = appoint
                getStaffList(data).then((res) => {
                    if (res.code == 200) {
                        if (source == 1) {
                            let matched = res.data.admins.find(v => v.id == that.todaystaff.id)
                            res.data.admins.forEach((v, i) => {
                                v.color = v.color || "rgb(26, 115, 232)"
                                v.checked = (v.id == that.todaystaff.id)
                                if (v.is_appoint == 1) {
                                    ar.push(v)
                                }
                            })
                            if (matched) {
                                that.todaystaff = {
                                    color: matched.color || "rgb(26, 115, 232)",
                                    id: matched.id,
                                    name: matched.name
                                }
                            } else if (res.data.admins.length > 0) {
                                let first = res.data.admins[0]
                                that.todaystaff = {
                                    color: first.color || "rgb(26, 115, 232)",
                                    id: first.id,
                                    name: first.name
                                }
                            }
                            that.p2 = ar
                        } else {
                            let t = {}
                            let obb = []
                            t.name = "指名なし"
                            t.color = "#d2d2d7"
                            if(that.person.id == 0){
                                 t.checked = true
                            }else{
                                t.checked = false
                            }
                            t.id = 0
                            obb.push(t)
                            let a = []
                            res.data.admins.forEach((v,i) => {
                                if(v.is_appoint == 1){
                                    a.push(v)
                                }
                                if(v.id == that.person.id){
                                    v.checked = true
                                }else{
                                    v.checked = false
                                }
                            })
                            
                            that.peolist = [...obb, ...a]
                            that.peoShow = true
                        }
                    }
                })
            },
            chooseStaff(index, item) {
                let that = this
                that.peolist.forEach((value, index) => {
                    value.checked = false
                })
                that.peolist[index].checked = true
                that.choosePeo(that.peolist[index])
            },
            // getDate() {
            //     let that = this
            //     let date = new Date()
            //     let year = date.getFullYear()
            //     let month = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
            //     let day = date.getDate() < 10 ? '0' + (date.getDate() + 1) : date.getDate() + 1
            //     that.date = year + '-' + month + '-' + day
            // },
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
                that.waiType = ""
            },
            dateConfirm(e) {
                this.date = e[0]
                this.calendarShow = false
                let person = {}
                person["color"] = "#D2D2D7"
                person["name"] = "指名なし"
                person["id"] = ""
                this.person = person
                if (this.waiType == 'off') return this.loadOffRanges() // [カスタムエイド 2026-10-22]
                this.getStaff(this.menu.id, 1)
            },
            insert1() {
            },
            insert2() {
            },
            chooseSex(e, index) {
                this.show = false
                this.sexname = e
                this.info.sex = index + 1
                this.info.id = ""
            },
            // [カスタムエイド 2026-10-22] 時間えらび(csTimeDrum)で使う:その日と今の時刻
            tdDate() { return this.date },
            tdValue(k) { return k == 'start' ? this.start : this.end },
            changeHandler(e) {
                let that = this
                that.start = that.tdText(e)
                that.startShow = false
                that.getEndTime()
            },
            changeHandler1(e) {
                let that = this
                that.end = that.tdText(e)
                that.endShow = false
            },
            searchMem() {
                uni.hideKeyboard()
                this.searchShow = true
            },
            getCustomerList() {
                let that = this
                let data = {}
                data.keywords = that.keywords
                data.page = that.page
                data.page_size = 50
                getMemberList(data).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        that.searchList = res.data.members
                    }
                })
            },
            getMore() {
                let that = this
                let data = {}
                data.keywords = that.keywords
                data.page = that.page
                data.page_size = 50
                getMemberList(data).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        that.searchList = [...that.searchList, ...res.data.members]
                    }
                })
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
                menu["name"] = that.waiType == 'off' ? OFF_NAME : BREAK_NAME
                if (that.wai == true) {
                    that.menu = menu
                    that.totalTime = 0
                    if (that.waiType == 'off') {
                        that.loadOffRanges() // [カスタムエイド 2026-10-22] お休みは営業時間をまとめて止める
                    } else {
                        that.getEndTime()
                    }
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
                    if (i === undefined) return // [カスタムエイド 2026-10-22] 何も選ばずに閉じたときは今のまま
                    that.menu = array[i].items[ix]
                    that.totalTime = total
                    that.getStaff(that.menu.id,1)
                    that.getEndTime()
                }
            },
            getEndTime() {
                let that = this
                let time = that.date + " " + that.start
                let array = time.split("-")
                let str = ""
                for(let i = 0; i < array.length; i++){
                    if(i == array.length - 1){
                        str += array[i]
                    }else{
                        str += array[i]+"/"
                    }
                }
                let date = new Date(str)
                let min = date.getMinutes()
                date.setMinutes(min + that.totalTime * 1)
                let hour = date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours()
                let minute = date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes()
                that.end = hour + ":" + minute
                if (that.end == "00:00") that.end = "24:00" // [カスタムエイド 2026-10-22] 夜0時ちょうどに終わるときは 24:00(次の日の 0:00)
                that.getStaff(that.menu.id, 1)
            },
            scrollToBottom() {
                let that = this
                uni.showLoading({
                    title: "検索中..."
                })
                this.page = this.page * 1 + 1
                that.getMore()
            },
            confirmSearch(e) {
                let that = this
                that.page = 1
                uni.showLoading({
                    title: "検索中"
                })
                that.getCustomerList()
            },
            selectItem(e) {
                let that = this
                that.info.name = that.searchList[e].name
                that.info.phone = that.searchList[e].phone
                that.info.sex = that.searchList[e].sex
                that.info.id = that.searchList[e].id
                that.sexname = that.searchList[e].sex == 1 ? '男性' : that.searchList[e].sex == 2 ? '女性' : 'Xジェンダー'
                that.ID = that.searchList[e].member_id
				that.tempInfo.name = that.searchList[e].name
				that.tempInfo.phone = that.searchList[e].phone
				that.tempInfo.sex = that.searchList[e].sex
				that.tempInfo.id = that.searchList[e].id
				that.sexname = that.searchList[e].sex == 1 ? '男性' : that.searchList[e].sex == 2 ? '女性' : 'Xジェンダー'
				that.ID = that.searchList[e].member_id
                that.searchShow = false
            }
        }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-22] お休み(赤)・休憩・勤務時間外(濃い緑) */
    .cs-off { color: #D93025; }
    .cs-brk, .detail .allname.cs-brk { color: #137333 !important; }
    .cs-wai { align-items: flex-start !important; border-bottom: 2upx solid #d2d2d7; }
    .cs-wai-d { font-size: 24rpx; color: #6E6E73; margin-top: 8rpx; line-height: 1.5; }
    .cs-lock { font-size: 30rpx; color: #6E6E73; }
    .cs-note { margin: 8rpx 0 0; padding: 20rpx 24rpx; border-radius: 20rpx; background: #E8F0FE; color: #174EA6; font-size: 24rpx; line-height: 1.7; }
    .searchList {
        box-sizing: border-box;
        padding: 0 32upx;
        height: 70vh;
    }
    .msk{
        position: fixed;
        width: 100vw;
        height: 100vh;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        z-index: 999 !important;
    }
    .chan{
        background-color: rgba(29, 29, 31, .1) !important;
    }
    .savehover{
        color: rgba(26, 115, 232, .6) !important;
    }
    .searchp{
        display: flex;
        align-items: center;
        border: 2upx solid #D2D2D7;
        color: #d2d2d7;
        font-size: 32upx;
        box-sizing: border-box;
        padding: 16upx;
        border-radius: 8upx;
    }
    .peoContent{
        height: 70vh;
    }
    .sDtime {
        flex-shrink: 0;
        flex:1
    }
    
    .mask{
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(29,29,31,0.2);
        z-index: 999;
        
        /* [カスタムエイド 2026-10-25] 完了のお知らせを、ほかの画面の作り直しと同じ見た目に(白いカード・角丸を大きく・文字は濃い色で太字・1行に収まる幅) */
        .maskcontent{
            width: 560upx;
            background: #fff;
            border-radius: 32upx;
            text-align: center;
            box-sizing: border-box;
            padding: 56upx 40upx 52upx;
            font-size: 30upx;
            font-weight: 700;
            line-height: 1.5;
            color: #1D1D1F;
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%,-50%);
            box-shadow: 0 16upx 48upx rgba(0, 0, 0, 0.12);
            display: flex;
            flex-direction: column;
            align-items: center;

            .mi{
                width: 96upx;
                height: 96upx;
                margin-bottom: 28upx;
            }
        }
    }

    .searchItem {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 20upx 0;
        border-bottom: 2upx solid #d2d2d7;

        .sitemLeft {
            image {
                width: 80upx;
                height: 80upx;
            }
        }

        .sitemRight {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
        }

        .sitemMiddle {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            flex: 1;
            box-sizing: border-box;
            padding-left: 20upx;

            view:first-child {
                font-size: 32upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }
        }
    }

    .searchContent {
        box-sizing: border-box;
        padding: 64upx 32upx;
        border-bottom: 2upx solid #d2d2d7;
        position: sticky;
        top: 112upx;
        left: 0;
        background: #fff;
        z-index: 10002;
    }

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

    .cConfirm {
        font-size: 40upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
        text-align: center;
        box-sizing: border-box;
        padding: 96upx 0 186upx 0;
    }

    .selectContent {
        margin-right: 32upx;
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
                flex: 1;
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
                        flex: 1;
                        .sDprice {
                            color: #D93025;
                            // margin-right: 70upx;
                            width: 46%;
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
        font-size: 40upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        position: sticky;
        top: 0;
        left: 0;
        z-index: 999;
        border-bottom: 2upx solid #d2d2d7;

        image {
            width: 60upx;
            height: 60upx;
            position: absolute;
            top: 26upx;
            left: 32upx;
        }
    }

    .titleName {
        font-size: 40upx;
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
        background: #f5f5f7;
        z-index: 999;
        overflow-y: scroll;
        box-sizing: border-box;
        padding: 32upx;
        animation: csSlideIn 0.3s cubic-bezier(0.2, 0.7, 0.3, 1); /* [カスタムエイド 2026-10-26] 右から横にスライドして入る(Card-San App・予約WEB と同じ 0.3秒) */
        box-shadow: -8rpx 0 24rpx rgba(0, 0, 0, 0.08);

        &.cs-leave {
            animation: csSlideOut 0.26s cubic-bezier(0.4, 0, 0.6, 1) forwards; /* [カスタムエイド 2026-10-26] 右へスライドして出る */
        }

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
    /* [カスタムエイド 2026-10-26] 新規予約の画面の移り方(iPhone の画面の移り方に近い速さ) */
    @keyframes csSlideIn {
        from { transform: translateX(100%); }
        to { transform: translateX(0); }
    }
    @keyframes csSlideOut {
        from { transform: translateX(0); }
        to { transform: translateX(100%); }
    }
</style>
