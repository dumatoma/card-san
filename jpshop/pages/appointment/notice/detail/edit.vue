<template>
    <view class="contains">
        <view class="ct">
            <view class="ctItem" style="margin-top: 0 !important;">
                <view class="itemName">
                    氏名
                </view>
                <view class="itemDetail" v-if="info.mid" style="color: rgba(26, 115, 232, 1);" @click="toCustomer"
                    v-text="`${info.name}`"></view>
                <view class="itemDetail" v-else style="color: #999" @click="toCustomer" v-text="`${info.name}`"></view>
            </view>
            <view class="ctItem">
                <view class="itemName">
                    電話番号
                </view>
                <view class="itemDetail">
                    <u--input placeholder="入力してください" border="bottom" v-model="info.phone" clearable></u--input>
                </view>
            </view>
            <view class="ctItem">
                <view class="itemName">
                    会員ID
                </view>
                <view class="itemDetail">
                    <u--input border="none" disabledColor="#fff" disabled v-model="info.member_id" clearable>
                    </u--input>
                </view>
            </view>
            <view class="ctItem">
                <view class="itemName">
                    予約番号
                </view>
                <view class="itemDetail">
                    <u--input placeholder="入力してください" disabledColor="#fff" border="none" disabled v-model="info.order_no"
                        clearable>
                    </u--input>
                </view>
            </view>
        </view>
       <view class="ct" v-if="menu"  @click="timeShow = true" style="display: flex;align-items: center;justify-content: space-between;">
            <view class="pickers">
                <view class="">
                    <view v-text="menu.title"></view>
                    <view style="margin-top: 20upx;" v-if="wai == false">
                        所要時間：{{menu.order_time}}分 <text
                            style="color: rgba(217, 48, 37, 0.65);margin-left: 80upx;">￥{{menu.price}}</text>　　
                    </view>
                </view>
            </view>
            <view>
                <u-icon name="arrow-right"></u-icon>
            </view>
        </view>
        <view class="ct" v-else style="display: flex;align-items: center;justify-content: space-between;"  @click="timeShow = true">
             <view class="pickers">
                 <view class="">
                     <view>お休み・勤務時間外</view>
                 </view>
             </view>
             <view>
                   <u-icon name="arrow-right"></u-icon>
             </view>
         </view>
        <view class="ct" style="padding: 20upx 32upx;">
            <view class="citem border_bottom">
                <view class="citemLeft">
                    日付
                </view>
                <view class="citemRight">
                    <view class="dateDetail" @click="calendarShow = true" v-text="info.day"></view>
                </view>
            </view>
            <view class="citem border_bottom">
                <view class="citemLeft">
                    開始
                </view>
                <view class="citemRight">
                    <view class="dateDetail" v-text="info.start" @click="startShow = true"></view>
                </view>
            </view>
            <view class="citem border_bottom">
                <view class="citemLeft">
                    終了
                </view>
                <view class="citemRight">
                    <view class="dateDetail" v-text="info.end" @click="showEnd"></view>
                </view>
            </view>

            <view class="citem border_bottom" @click="getZhi">
                <view class="citemLeft">
                    指名担当
                </view>
                <view class="citemRight">
                    <view class="cperson">
                        <!-- <view class="pcolor"
                            :style="appoint_admin.color? 'background:'+appoint_admin.color : 'background: rgb(23,84,145);'">
                        </view> -->
                        <view class="pcolor" :style="`background:${appoint_admin.color == ''? 'rgb(23,84,145)' : appoint_admin.color}`"></view>
                        <view class="pname" v-text="appoint_admin.name"></view>
                        <u-icon name="arrow-right"></u-icon>
                    </view>
                </view>
            </view>

            <view class="citem border_bottom" @click="getDang">
                <view class="citemLeft">
                    当日担当
                </view>
                <view class="citemRight">
                    <view class="cperson">
                        <view class="pcolor" :style="`background:${day_admin.color == ''?'rgb(23,84,145)':day_admin.color}`"></view>
                        <view class="pname" v-text="day_admin.name"></view>
                        <u-icon name="arrow-right"></u-icon>
                    </view>
                </view>
            </view>
        </view>
        <view class="ct">
            <view>メモ</view>
            <u-textarea placeholder="テキスト" confirmType="done" border="none" :height="35" v-model="info.remark"></u-textarea>
        </view>
        <view class="tips">
            <view>予約内容の変更をお客さんにお知らせする場合</view>
            <view class="connect" @click="show1 = true">こちら</view>
        </view>
        <view class="cConfirm" style="padding-bottom: 0;" @click="save" hover-class="ho1">
            変更を保存
        </view>
        <!-- @click="deleteOrder" -->
        <view class="cConfirm" style="color: rgba(217, 48, 37, 1);" hover-class="ho1" @click="showAle = true">
            この予約を取り消す
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
            <view style="height: 90vh;width: 100%;overflow-y: scroll;position: relative;">
                <view class="title">
                    <image src="../../../../static/icons/tclose.png" mode="" @click="peoShow = false"></image>
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
            <view style="height: 90vh;width: 100%;overflow-y: scroll;position: relative;">
                <view class="title">
                    <image src="../../../../static/icons/tclose.png" mode="" @click="peoShow2 = false"></image>
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
            <view style="height: 90vh;width: 100%;overflow-y: scroll;position: relative;">
                <view class="title">
                    <image src="../../../../static/icons/tclose.png" @click="closeShow" mode=""></image>
                    <view class="titleName">メニュー選択</view>
                </view>
                <view class="detail">
                    <view class="selectAll">
                        <view class="selectContent" @click="changeWai">
                            <view class="unselect" v-if="!wai"></view>
                            <view class="selected" v-if="wai">
                                <view class="fill"></view>
                            </view>
                        </view>
                        <view class="allname">
                            お休み・勤務時間外
                        </view>
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
                                            <view class="sDprice" v-text="`￥${ite.price}`"></view>
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
                sending:false
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
                data.name = that.info.name
                data.phone = that.info.phone
                data.sex = that.info.sex
                data.member_id = that.info.member_id
                data.smid = that.menu.id
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
                })
            },
            getStaff(appoint, source) {
                uni.showLoading({
                    title:"読み込み中"
                })
                let that = this
                let data = {}
                let date = that.info.day1
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
                })
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
                menu["title"] = "お休み・勤務時間外"
                if (that.wai == true) {
                    menu["order_time"] = 0
                    that.menu = menu
                    that.totalTime = 0
                    // that.getEndTime()
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
                    that.totalTime = total
                    that.menu = array[i].items[ix]
                    that.menu["order_time"] = total
                    that.menu["title"] = that.menu["name"]
                }
                that.getEndTime()
            },
            
            getEndTime() {
                let that = this
                let time = that.info.day1 + " " + that.info.start
                let date = new Date(time)
                let min = date.getMinutes()
                date.setMinutes(min + that.totalTime * 1)
                let hour = date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours()
                let minute = date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes()
                that.info.end = hour + ":" + minute
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
                        that.mid = res.data.order.mid
                        that.totalTime = res.data.order.menu.order_time
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
            
            changeWai() {
                let that = this
                let array = that.array
            
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
                                            object.time = val.order_time
                                            object.checked = false
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
                if(that.menu.id == 0){
                    that.endShow = true
                }
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
                that.info.end = e.value[0] + ":" + e.value[1]
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
