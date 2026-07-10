<template>
  <div>
    <div class="head u-flex u-row-between">
      <div class="title">営業カレンダー</div>
      <div class="tit_btn u-flex-center shou" @click="timeClick">
        定休日と営業時間の設定
      </div>
    </div>
    <div class="ttss">
        <div class="clandar">
          <clandar @onDayClick="selectDay" :restArr='rest' @monthChange="monthChange"></clandar>
        </div>
        <div style="width: 375px;">
           <div class="xiu-date">
             <div class="xiu-day-title"> {{month}}月{{date}}日  {{day}}曜日</div>
             <div class="yingye"  v-if="open == 1"  @click="showSetting">営業時間</div>
             <div class="yingye" style="color: rgb(217, 48, 37)" v-if="open == 0" @click="showSetting">
                 休業日
             </div>
              <div v-if="open == 1">
             <div class="xiu-time" v-for="(item,index) in openArr" v-text="`${item.start} ~ ${item.end}`"></div>
             </div> 
           </div> 
        </div>
    </div>
    
    
    <!-- 修改时间弹窗 -->
    <div class="fixed" v-show="timeShow">
      <div class="time no-size">
        <div class="fi_tit">
         {{month}}月{{date}}日  {{day}}曜日
          <img
            class="shou"
            src="@/static/tclose.png"
            @click="timeShow = false"
          />
        </div>
        <div class="u-flex">
          <div class="fi_center u-flex u-row-between">
            <div class="u-flex" v-for="(item, index) in timeList" :key="index">
              <div
                class="radius"
                :class="{ radiusCss: index == open }"
                @click="radiusClick(index)"
              >
                <div class="blue_dot" v-if="index == open"></div>
              </div>
              <div :style="`color: ${(open == 0 && index == open) ? '#D93025' : '#1d1d1f'}`">
                {{ item }}
              </div>
            </div>
          </div>
        </div>
        <div class="bot_text u-flex" v-if="open == 1">
          <div style="margin-right: 40px">{{open == 1 ? '営' : '休'}}業時間</div>
          <div style="margin-right: 70px">開始</div>
          <div>終了</div>
        </div>
        <div class="u-flex-col"  v-if="open == 1">
          <div
            class="u-flex shou day_time no-size"
            :class="indexT == 1 ? 'm-t-20' : ''"
            v-for="(itemT, indexT) in openArr"
            :key="indexT"
          >
            <el-time-select v-model="itemT.start" :picker-options="{start: '09:00',step: '00:30',end: '22:00'}" :clearable="false">
            </el-time-select>
            <el-time-select
              v-model="itemT.end"
              :clearable="false"
              :picker-options="{start: '09:00',step: '00:30',end: '22:00'}"
            ></el-time-select>
            <i
              class="el-icon-circle-plus-outline"
              @click="plus()"
              v-if="indexT == 0"
            ></i>
            <img src="@/static/tclose.png" @click="reduce()" v-else />
          </div>
        </div>
        <div class="bottom">
          <div class="btn shou u-flex-center" @click="keepClick">保　存</div>
        </div>
      </div>
    </div>
    <!-- 保存 -->
    <div class="fixed" v-show="keepShow">
      <div class="release u-flex-col u-col-center u-row-center">
        <img src="@/static/index/sign_blue.png" class="sign" />
        <div>保存しました</div>
      </div>
    </div>
  </div>
</template>

<script>
import clandar from "../../components/calendar.vue";
import {getCalendar,setCalendar} from "@/http/api.js"
export default {
  components: {
    clandar,
  },
  data() {
    return {
      timeShow: false,
      timeList: ["休業日","営業日"],
      month:"",
      timeIndex: 0,
      time: [{ startTime: "9:00", endTime: "22:00" }],
      keepShow: false,
      rest:[],
      fullDate:"",
      fullWeek:"",
      fulldate:"",
      open:0,
      day:"",
      openArr:[],
      days:[],
      pickerArr:[],
      tshow:"",
      today:"",
      hour:'',
      minute:""
    };
  },
  created() {
    let that = this
    let date = that.getMonth()
    let today = that.getToday()
    let data = {}
    data.type = "day"
    data.date = date
    that.month = today.month
    that.date = today.date
    that.day = today.week
    that.year = today.year
    that.hour = today.hour
    that.minute = today.minute
    that.fullDate = today.year + "年" + today.date + "月"
    that.fulldate = today.year + '-' + today.month + '-' + today.date
    that.today = today.year + '-' + today.month + '-' + today.date
    console.log("tt",today)
    that.getCalendar(data, "today", today.date * 1)
  },
  methods: {
      monthChange(e) {
          console.log(e)
          let that = this
          let date = e.year + '-' + e.month
          let data = {}
          data.type = "day"
          data.date = date
          that.rest = []
          that.getCalendar(data)
      },
      changeMonth(type) {
          if (type == 'pre') {
              if (this.m + 1 == 2) {
                  this.m = 12;
                  this.y = this.y - 1;
              } else {
                  this.m = this.m - 1;
              }
          } else {
              if (this.m + 1 == 13) {
                  this.m = 1;
                  this.y = this.y + 1;
              } else {
                  this.m = this.m + 1;
              }
          }
          this.dates = this.monthDay(this.y, this.m);
          let obj = {}
          obj.year = this.y
          obj.month = this.m
          this.$emit("monthChange",obj)
      },
      showSetting(){
          let that = this
          that.timeShow = true
      },
      selectDay(e) {
          let that = this
          that.fulldate = e.date
          let indexArr = e.date.split("-")
          let index = indexArr[2] * 1
          console.log("ssss", e)
          that.month = indexArr[1]
          that.date = indexArr[2]
          that.year = indexArr[0]
          that.day = e.week 
          that.tshow = indexArr[1] + "月" + indexArr[2] + "日" + e.week 
          if (that.days[index].business_time.length > 0) {
              that.openArr = that.days[index].business_time
          } else {
              that.openArr = that.pickerArr
          }
        
          that.open = that.days[index].business
      },
      getCalendar(data, status, index) {
          getCalendar(data).then((res) => {
              let that = this
              console.log("rest",res)
              if (res.code == 200) {
                  let temp = []
                  that.days = res.data.day_business
                  res.data.rest_days.forEach((value, index) => {
                      temp.push(value * 1)
                  })
                  that.rest = temp
                  if (status == "today") {
                      if (res.data.day_business[index].business_time.length > 0) {
                          that.openArr = res.data.day_business[index].business_time
                      } else {
                          that.openArr = that.pickerArr
                      }
                      that.open = res.data.day_business[index].business
                  }
              }
          })
      },
      getMonth() {
          let that = this
          let date = new Date()
          let year = date.getFullYear()
          let month = (date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
          return year + "-" + month
      },
      getToday() {
          let date = new Date()
          let year = date.getFullYear()
          let month = (date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
          let week = date.getDay()
          let sdate = date.getDate() < 10 ? '0' + date.getDate() : date.getDate()
          let hour = date.getHours()
          let minute = date.getMinutes()
          let wek
          wek = week == 0 ? '日' : week == 1 ? '月' : week == 2 ? '火' :
              week == 3 ? '水' : week == 4 ? '木' : week == 5 ? '金' : '土'
          let obj = {}
          obj.week = wek
          obj.year = year
          obj.month = month
          obj.date = sdate
          obj.hour = hour
          obj.minute = minute
          return obj
      },
      getToMonth(){
        let that = this
        let date = new Date()
        let year = date.getFullYear()
        let month = date.getMonth() + 1 < 10? '0' +date.getMonth() + 1 : date.getMonth() + 1
        that.month = year + "-" + month
        that.fullDate = year + "-" + month
      },
      getList(){
        let that = this
        
      },
    timeClick() {
      this.$router.push({
        path: "/time",
      });
    },

    radiusClick(index) {
      this.open = index;
    },
    plus() {
      let time = {
        start: "00:00",
        end: "00:00",
      };
      if (this.openArr.length < 2) {
        this.openArr.push(time);
      }
    },
    reduce(index) {
      this.openArr.splice(1, 1);
    },
    keepClick() {
        console.log(this.openArr)
        console.log(this.time)
        let that = this
        let data = {}
        data['type'] = "day"
        data["date"] = that.year + "-" +that.month+"-"+that.date
        data['business'] = that.open
        data['business_time'] = that.openArr
        setCalendar(data).then((res) => {
            if(res.code == 200){
              that.keepShow = true;
              that.timeShow = false;
              setTimeout(() => {
                that.keepShow = false;
                history.go(0)
              }, 1000);  
            }else{
                that.$message.error(res.message)
            }
        })
    
    },
  },
};
</script>

<style lang="scss" scoped>
    ::v-deep .calendar-wrapper .content .days .item{
        height: 60px;
        line-height: 35px;
    }
    :v-deep .calendar-wrapper .content .days .item .day{
        line-height: 25px;
    }
    .yingye:hover{
        opacity: 0.6 !important;
        cursor: pointer;
    }
    .xiu-date {
      margin: 40px auto;
      width: 300px;
      // height: 288px;
      background: #f5f5f7;
      box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
      border-radius: 10px;
      text-align: center;
      padding: 10px 0;
    
      .xiu-day-title {
        font-size: 16px;
        color: #1d1d1f;
        margin-bottom: 10px;
      }
    
      .yingye {
        font-size: 16px;
        color: #007aff;
        margin-bottom: 10px;
      }
      
      
    
      .xiu-time {
        font-size: 16px;
        color: #1d1d1f;
        margin-bottom: 10px;
      }
    
      .xiu-pad {
        padding-bottom: 10px;
      }
    }
.head {
  width: 375px;
  .title {
    font-size: 21px;
    color: #1d1d1f;
    font-weight: bold;
  }
  .tit_btn {
    width: 180px;
    height: 40px;
    background: #53b1af;
    border-radius: 10px;
    font-size: 14px;
    color: #fff;
  }
}
.ttss{
    width: 375px;
    border: 1px solid transparent;
    box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
    margin-top: 20px;
}
.clandar {
  width: 375px;
  // margin-top: 10px;
  
}
.fixed {
  position: fixed;
  top: 0%;
  left: 0%;
  width: 100vw;
  height: 100vh;
  z-index: 1;
  background: rgba(29, 29, 31, 0.2);
  .time {
    width: 410px;
    // height: 400px;
    background: #fff;
    box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
    border-radius: 10px;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    .fi_tit {
      width: 92%;
      margin: 0 auto;
      font-size: 24px;
      color: #1d1d1f;
      font-weight: bold;
      text-align: center;
      position: relative;
      padding: 30px 0 20px 0;
      border-bottom: 1px solid #d2d2d7;
      img {
        top: 30px;
        right: 14px;
        position: absolute;
      }
    }
    .fi_center {
      width: 70%;
      font-size: 20px;
      padding: 30px 37px 44px 37px;
      .radius {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-right: 14px;
        background: #fff;
        border: 1px solid #707070;

        .blue_dot {
          width: 16px;
          height: 16px;
          border-radius: 50%;
          margin: 2px auto;
          background: #1a73e8;
        }
      }
    }
    .bot_text {
      width: 82%;
      margin: 0 auto;
      padding-left: 44px;
      font-size: 16px;
      color: #707070;
      padding-bottom: 8px;
      border-bottom: 1px solid #d2d2d7;
    }
    .day_time {
       width: 92%;
      margin: 0 auto;
      border-bottom: 1px solid #d2d2d7;
      padding: 19px 0 19px 143px;
     
      .el-icon-circle-plus-outline {
        color: #1a73e8;
        margin-left: -35px;
        font-size: 20px;
        z-index: 1;
      }
      img {
        width: 20px;
        height: 20px;
        margin-left: -35px;
        z-index: 1;
      }
      ::v-deep .el-date-editor.el-input,
      .el-date-editor.el-input__inner {
        width: 100px;
      }
      ::v-deep .el-input__inner {
        border: none;
        padding: 0;
        height: 20px;
        line-height: 0;
        font-size: 15px;
      }
      ::v-deep .el-icon-time:before {
        content: "";
      }
    }
    .bottom {
      padding: 53px 0 50px 0;
      .btn {
        width: 150px;
        height: 44px;
        font-size: 16px;
        color: #fff;
        margin: 0 auto;
        background: #1a73e8;
        border-radius: 26px;
      }
    }
  }
  .release {
    width: 160px;
    height: 146px;
    background: #fff;
    border-radius: 10px;
    position: fixed;
    top: 50%;
    left: 50%;
    font-size: 16px;
    color: #2699fb;
    transform: translate(-50%, -50%);
    box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
    .sign {
      width: 50px;
      height: 50px;
      margin-bottom: 20px;
    }
  }
}
</style>
