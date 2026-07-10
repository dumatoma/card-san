<template>
    <div class="con">
        <div class="time">
            <div class="timeItem" v-for="(item,index) in times">
                <div class="timeName" v-text="item.time"></div>
                <div class="point"></div>
            </div>
            <div class="timeItem">
                <div class="timeName">22:00</div>
                <div class="point"></div>
            </div>
        </div>
        <div class="manager">
            <div class="mItem" v-for="(item,index) in managers">
                <div class="mTop">
                    <div class="pe" v-text="item.name"></div>
                    <div class="pecolor" :style="`background:${item.color}`"></div>
                </div>
                <div class="mContent">
                    <!-- <div class="meng" v-if="todayAviable == false"></div> -->
                    <!-- 未过期的预约 -->
                    <div class="dateItem" v-if="todayAviable == true" v-for="(ite,idx) in item.times"
                        :style="ite.checked?format(ite.color):ite.open?'background:#fff !important':'background: rgba(217,48,37,0.15);'"
                        @click="addThis(ite,1,item)">
                        <div :style="`height:${ite.duration * 80}px`" class="abc">
                            <div class="itemName" :style="`color:${ite.color}`">
                                <div class="bd" v-if="ite.start" :style="`background:${ite.color}`"></div>
                                {{ite.name}}
                                <!-- {{ite.duration}} -->
                            </div>
                            <div class="itemEvent" :style="`color:${ite.color}`">
                                {{ite.event}}
                            </div>
                        </div>
                    </div>
                    <!-- 已经过期的预约 -->
                    <div v-if="todayAviable == false" class="dateItem" v-for="(ite,idx) in item.times"
                        :style="ite.checked?format(ite.color):'background: #fff'" @click="addThis(ite,2)">
                        <div class="itemName" :style="`color:${ite.color}`">
                            <div class="bd" v-if="ite.start" :style="`background:${ite.color}`"></div>
                            {{ite.name}}
                        </div>
                        <div class="itemEvent" :style="`color:${ite.color}`">
                            {{ite.event}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="mask" v-if="mask">
            
        </div> -->
    </div>
</template>
<script>
    import {
        getConfig,
        getCalendar
    } from "../../http/api.js"
    import { Loading } from 'element-ui';
    export default {
        name: "progres",
        props: ['times', 'manager', "tdate"],
        data() {
            return {
                managers: [],
                currentTime: "",
                todayStamp: "",
                todayAviable: true,
                openTime: [],
                ttoday: "",
                istoday: false,
                mask:false,
                currentDate:""
            };
        },
        watch: {
            times: function(newVal, oldVal) {
                this.times = newVal; //newVal即是chartData
                this.mask = true
            },
            tdate: function(newVal, oldVal) {
                console.log("newtdate",newVal)
                let tiem = newVal
                let str = ''
                let arr = tiem.split("-")
                for (let i = 0; i < arr.length; i++) {
                    if (i == arr.length - 1) {
                        str += arr[i]
                    } else {
                        str += arr[i] + "/"
                    }
                }
                if (str == this.currentTime) {
                    this.istoday = true
                } else {
                    this.istoday = false
                }
                let today = Date.parse(new Date(this.currentTime))
                let ast =  Date.parse(new Date(str))
                if (today > ast) {
                    this.todayAviable = false
                } else {
                    this.todayAviable = true
                }
            },
            manager: function(newVal, oldVal) {
                let that = this
                let data = {}
                let idx
                let nowtime = 0
                data.type = "day"
                let options = {}
                options.text = "読み込み中"
               that.$loading(options)
                
                console.log("newVal",newVal)
                if(newVal.length == 0){
                   that.$loading().close()
                     return;
                }
               
                getConfig().then((res) => {
                    if (res.code == 200) {
                         let arr1 = newVal[0].date.split("-")
                        let str = ""
                        for (let i = 0; i < arr1.length; i++) {
                            if (i == arr1.length - 1) {
                                str += arr1[i]
                            } else {
                                str += arr1[i] + "/"
                            }
                        }
                        let to = Date.parse(new Date(str))
                        that.todayStamp = to
                        data.date = newVal[0].date
                        if(that.istoday == true){
                            idx = arr1[2] * 1
                            let arr = res.data.config.time_date.split(" ")
                            let alltime =arr[1]
                            let ta = alltime.split(":")
                            if (ta[1] * 1 > 30) {
                                nowtime = ta[0] * 1 + 1
                            } else {
                                nowtime = ta[0] * 1 + 0.5
                            }
                        }else{
                            data.date = newVal[0].date
                            nowtime = 0
                            let arr1 = newVal[0].date.split("-")
                            idx = arr1[2] * 1
                        
                        }
                        getCalendar(data).then((res) => {
                            if (res.code == 200) {
                                let temarr = []
                                that.$loading().close()
                                console.log("calander",res)
                                if (res.data.day_business[idx].business_time[1]) {
                                    let data = {}
                                    let data1 = {}
                                    let t1start = res.data.day_business[idx].business_time[0].start
                                        .split(":")
                                    let t1end = res.data.day_business[idx].business_time[0].end
                                        .split(":")
                                    let t2start = res.data.day_business[idx].business_time[1].start
                                        .split(":")
                                    let t2end = res.data.day_business[idx].business_time[1].end
                                        .split(":")
                                    if (t1start[1] == "00") {
                                        data.start = parseInt(t1start[0])
                                    } else {
                                        data.start = parseInt(t1start[0]) * 1 + 0.5
                                    }

                                    if (t1end[1] == "00") {
                                        data.end = parseInt(t1end[0])
                                    } else {
                                        data.end = parseInt(t1end[0]) * 1 + 0.5
                                    }

                                    if (t2start[1] == "00") {
                                        data1.start = parseInt(t2start[0])
                                    } else {
                                        data1.start = parseInt(t2start[0]) * 1 + 0.5
                                    }

                                    if (t2end[1] == "00") {
                                        data1.end = parseInt(t2end[0])
                                    } else {
                                        data1.end = parseInt(t2end[0]) * 1 + 0.5
                                    }
                                    data.business = res.data.day_business[idx].business
                                    temarr.push(data)
                                    temarr.push(data1)
                                    that.openTime = temarr
                                    console.log("temarr1",temarr)
                                } else {
                                    let data = {}
                                    let data1 = {}
                                    if(res.data.day_business[idx].business_time[0]){
                                        let t1start = res.data.day_business[idx].business_time[0].start
                                            .split(":")
                                        let t1end = res.data.day_business[idx].business_time[0].end
                                            .split(":")
                                        if (t1start[1] == "00") {
                                            data.start = t1start[0]
                                        } else {
                                            data.start = t1start[0] * 1 + 0.5
                                        }
                                        
                                        if (t1end[1] == "00") {
                                            data.end = t1end[0]
                                        } else {
                                            data.end = t1end[0] * 1 + 0.5
                                        }
                                    }else{
                                        data.start = "09"
                                        data.end = "22"
                                    }
                                    data.business = res.data.day_business[idx].business
                                    // temarr[0].business = res.data.day_business[idx].business
                                    data1.end = 0
                                    data1.start = 0
                                    temarr.push(data)
                                    temarr.push(data1)
                                    that.openTime = temarr
                                    console.log("temarr2",temarr)
                                }
                                let array = JSON.parse(JSON.stringify(newVal));
                                console.log('array',array)
                                for (let i = 0; i < array.length; i++) {
                                    for (let k = 0; k < array[i].times.length; k++) {
                                        if(this.istoday == true){
                                            if(that.openTime[0].business == 1){
                                                if(array[i].times[k].val >= nowtime){
                                                    if ((array[i].times[k].val >= that.openTime[0].start &&array[i].times[k].val < that.openTime[0].end) || (array[i].times[k].val >= that.openTime[1].start && array[i].times[k].val < that.openTime[1].end)) {
                                                        array[i].times[k].open = true
                                                    }
                                                }else{
                                                    array[i].times[k].open = false
                                                }
                                            }else{
                                                array[i].times[k].open = false
                                            }
                                        }else{
                                            if(that.openTime[0].business == 1){
                                                if(that.openTime[1]){
                                                    if ((array[i].times[k].val >= that.openTime[0].start &&array[i].times[k].val < that.openTime[0].end) || (array[i].times[k].val >= that.openTime[1].start && array[i].times[k].val < that.openTime[1].end)) {
                                                        array[i].times[k].open = true
                                                    }
                                                }else{
                                                    if (array[i].times[k].val >= that.openTime[0].start &&array[i].times[k].val < that.openTime[0].end) {
                                                        array[i].times[k].open = true
                                                    }
                                                }
                                            }else{
                                                array[i].times[k].open = false
                                            } 
                                        }
                                    }
                                    for (let j = 0; j < array[i].events.length; j++) {
                                        let start = 5000000
                                        for (let k = 0; k < array[i].times.length; k++) {
                                            if (array[i].times[k].time == array[i].events[j]
                                                .start) {
                                                array[i].times[k].start = true
                                                array[i].times[k].checked = true
                                                array[i].times[k].name = array[i].events[j].name
                                                array[i].times[k].event = array[i].events[j].event
                                                array[i].times[k].color =  array[i].events[j].appoint_said == 0? 'rgb(170,170,170)' : array[i].color  
                                                array[i].times[k].id = array[i].events[j].id
                                                start = k
                                                array[i].times[k].duration = array[i].events[j].duration
                                            } else {
                                                if (k > start && k < start + (array[i].events[j]
                                                        .duration) * 2) {
                                                    array[i].times[k].checked = true
                                                    array[i].times[k].color =  array[i].events[j].appoint_said == 0? 'rgb(170,170,170)' : array[i].color  
                                                    array[i].times[k].id = array[i].events[j].id
                                                    array[i].times[k].duration = array[i].events[j].duration
                                                } else {
                                                    // array[i].times[k].checked = false
                                                    // if ((array[i].times[k].val >= that.openTime[0]
                                                    //         .start && array[i].times[k].val <= that
                                                    //         .openTime[0].end) || (array[i].times[k]
                                                    //         .val >= that.openTime[1].start && array[
                                                    //             i].times[k].val <= that.openTime[1]
                                                    //         .end)) {
                                                    //     array[i].times[k].open = true
                                                    // }
                                                }
                                            }
                                        }
                                    }
                                }
                                that.managers = array
                                console.log("managers",that.managers)
                            } else {
                                // uni.showToast({
                                //     title: res.message
                                // })
                            }
                            
                        })
                    }
                })
            }
        },
        mounted() {
            let that = this 
            getConfig().then((res) => {
                if(res.code == 200){
                   let arr = res.data.config.time_date.split(" ")
                   let a1 = arr[0]
                   let a2 = a1.split('/')
                   let str = ''
                   for (let i = 0; i < a2.length; i++) {
                       if (i == a2.length - 1) {
                           str += a2[i]
                       } else {
                           str += a2[i] + "-"
                       }
                   }
                   if(this.tdate == str){
                       this.istoday = true
                   }else{
                       this.istoday = false
                   }
                   that.currentTime = a1
                   that.stamp = res.data.config.timestamp
                }
                
            })
            this.mask = true
        },
        created() {
        },
        methods: {
            format(e) {
                if (e) {
                    let color = e.replace(")", ",0.15)")
                    let final = color.replace("rgb", "rgba")
                    return "background:" + final + ";border-bottom:2px solid " + final
                }
            },
            addThis(e,status,item) {
                let that = this
                console.log("e",e)
                console.log("item",item)
                let t = e.time
                if(status == 1){
                    if (e.checked == true || e.start == true) {
                        console.log("e=",e)
                        this.$emit("checkdetail", e.id,e.id)
                        return;
                    }
                    if (!e.open) {
                        // uni.showToast({
                        //     title:"不可预定"
                        // })
                        return;
                    }
                    
                    this.$emit("addNew", t,item)
                }else{
                    this.$emit("checkorder", e.id)
                }
               
            }
        }
    }
</script>

<style lang="scss">
    .hasguo {
        background: #fff !important;
    }
    .abc{
        width: 100%;
        position: absolute;
        left: 0;
        top: 0;
        overflow:hidden;
        
    }
    .meng {
        position: absolute;
        z-index: 2 !important;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        background-color: rgba(0, 0, 0, .15);
    }

    .con {
        width: 100%;
        display: flex;

        .time {
            box-sizing: border-box;
            padding: 50px 0 0 15px;
            position: relative;
            top: -22px;

            .timeItem {
                height: 44px;
                font-size: 12px;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                width: 65px;
                line-height: 40px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                text-align: center;
                border-right: 1px double #d2d2d7;

                .timeName {
                    flex: 1;
                    text-align: center;
                }

                .point {
                    width: 10px;
                    height: 1px;
                    background-color: #EAEAEA;
                }
            }
        }
    }
    // .mask{
    //     width: 100vw;
    //     height: 100vh;
    //     position: fixed;
    //     top: 0;
    //     left: 0;
    //     background: red;
    //     z-index: 10006 !important;
    //     background: rgba(0, 0, 0, 0.1);
    // }
    .manager {
        width: 100%;
        overflow-x: scroll;
        display: flex;
        border-top: none;
        // border-left: 2px solid #EAEAEA;

        .mItem {
            width: 120px;
            flex-shrink: 0;
            box-sizing: border-box;
            padding: 0;
            position: relative;

            .mContent {
                width: 100%;
                border-top: 1px solid #eaeaee;
                position: relative;

                .dateItem {
                    width: 100%;
                    height: 44px;
                    box-sizing: border-box;
                    border: 1px solid #EAEAEA;
                    border-left: none;
                    border-top: none;
                    font-size: 12px;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    position: relative;
                    .itemName {
                        box-sizing: border-box;
                        position: relative;
                        padding: 8px 4px 0;

                        .bd {
                            width: 100%;
                            height: 4px;
                            border-radius: 4px;
                            position: absolute;
                            top: 0;
                            left: 0;
                        }
                    }

                    .itemEvent {
                        box-sizing: border-box;
                        padding: 0 4px;
                        overflow: hidden;
                        height: 100%;
                    }
                }
            }

            .mTop {
                height: 50px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                position: sticky;
                top: 0;
                left: 0;

                .pe {
                    width: 100%;
                    text-align: center;
                    font-size: 14px;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #86868B;
                    margin-bottom: 3px;

                }

                .pecolor {
                    width: 114px;
                    height: 4px;
                    border-radius: 4px;
                    margin: 0 auto;
                    position: absolute;
                    bottom: 8px;
                    left: 3px;
                }
            }
        }

    }
</style>
