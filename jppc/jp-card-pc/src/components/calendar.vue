<template>
  <div>
    <!-- <u-popup :show="caiDanShow" @close="close" :round="20"> -->
    <div class="calendar-wrapper">
      <!-- <div class="head-title">
          <image src="../static/image/close.png" mode=""></image>
          <div class="tit">営業カレンダー</div>
        </div> -->
      <div class="header">
        <div class="header-b">
          <div>
            {{ y + "年" }} &nbsp;&nbsp;{{ formatNum(m) + "月" }}
          </div>
          <div class="header-a">
            <div class="preWidth" @click="changeMonth('pre')">
              <!-- <u-icon name="arrow-left" color="#1A73E8" size="21"></u-icon> -->
              <i class="el-icon-arrow-left" style="color: #1a73e8; size: 21px;font-weight:bold;"></i>
            </div>
            <div class="nextWidth" @click="changeMonth('next')">
              <!-- <u-icon name="arrow-right" color="#1A73E8" size="21"></u-icon> -->
              <i class="el-icon-arrow-right" style="color: #1a73e8; size: 21px;font-weight:bold;"></i>
            </div>
          </div>
        </div>
        <div class="week">
          <div class="week-day" v-for="(item, index) in weekDay" :key="index">
            {{ item }}
          </div>
        </div>
      </div>

      <div class="content">
        <div :style="{ top: positionTop + 'rpx' }" class="days">
          <div class="item" v-for="(item, index) in dates" :key="index">
            <div
              class="day"
              @click="selectOne(item, $event)"
              :class="{
                choose:choose == `${item.year}-${item.month}-${item.date}` && item.isCurM,
                nolm: !item.isCurM,
                today: isToday(item.year, item.month, item.date),
                isWorkDay: isWorkDay(item.year, item.month, item.date),
              }"
            >
              {{ Number(item.date) }}
              <!-- <div
                v-if="item.day == 1 || item.day == 2"
                class="xiu"
                style="color: red"
              >
                休
              </div> -->
              <div class="rest" v-show="restArr.indexOf(Number(item.date)) != '-1' && item.isCurM">休</div>
            </div>
            <div
              class="markDay"
              v-if="isMarkDay(item.year, item.month, item.date) && item.isCurM"
            ></div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- </u-popup> -->
  </div>
</template>

<script>
export default {
  name: "ren-calendar",
  props: {
    // 星期几为第一天(0为星期日)
    caiDanShow: {
      type: Boolean,
      default: false,
    },
    weekstart: {
      type: Number,
      default: 0,
    },
    // 标记的日期
    markDays: {
      type: Array,
      default: () => {
        return [];
      },
    },
    // 是否展开
    open: {
      type: Boolean,
      default: false,
    },
    //是否可收缩
    collapsible: {
      type: Boolean,
      default: false,
    },
    //未来日期是否不可点击
    disabledAfter: {
      type: Boolean,
      default: false,
    },
    restArr:{
        type: Array,
        default: () => {
            return [];
        }
    }
  },
  data() {
    return {
      weektext: ["日", "月", "火", "水", "木", "金", "土"],
      y: new Date().getFullYear(), // 年
      m: new Date().getMonth() + 1, // 月
      dates: [], // 当前月的日期数据
      positionTop: 0,
      monthOpen: false,
      choose: "",
      headerBar: false, // 月份切换按钮
    };
  },
  created() {
    this.dates = this.monthDay(this.y, this.m);
    !this.open && this.toggle();
    console.log("rets",this.restArr)
  },
  mounted() {
    this.choose = this.getToday().date;
  },
  computed: {
    // 顶部星期栏
    weekDay() {
      return this.weektext
        .slice(this.weekstart)
        .concat(this.weektext.slice(0, this.weekstart));
    },
    height() {
      return (this.dates.length / 7) * 80 + "rpx";
    },
  },
  methods: {
    close() {
      this.$emit("clandarClose", false); //参数1：自定义事件；参数2：要传递的值
    },
    formatNum(num) {
      let res = Number(num);
      return res < 10 ? "0" + res : res;
    },
    getToday() {
      let date = new Date();
      let y = date.getFullYear();
      let m = date.getMonth();
      let d = date.getDate();
      let week = new Date().getDay();
      let weekText = ["日", "一", "二", "三", "四", "五", "六"];
      let formatWeek = "星期" + weekText[week];
      let today = {
        date: y + "-" + this.formatNum(m + 1) + "-" + this.formatNum(d),
        week: formatWeek,
      };
      return today;
    },
    // 获取当前月份数据
    monthDay(y, month) {
      let dates = [];
      let m = Number(month);
      let firstDayOfMonth = new Date(y, m - 1, 1).getDay(); // 当月第一天星期几
      let lastDateOfMonth = new Date(y, m, 0).getDate(); // 当月最后一天
      let lastDayOfLastMonth = new Date(y, m - 2, 0).getDate(); // 上一月的最后一天
      let weekstart = this.weekstart == 7 ? 0 : this.weekstart;
      let startDay = (() => {
        // 周初有几天是上个月的
        if (firstDayOfMonth == weekstart) {
          return 0;
        } else if (firstDayOfMonth > weekstart) {
          return firstDayOfMonth - weekstart;
        } else {
          return 7 - weekstart + firstDayOfMonth;
        }
      })();
      let endDay = 7 - ((startDay + lastDateOfMonth) % 7); // 结束还有几天是下个月的
      for (let i = 1; i <= startDay; i++) {
        dates.push({
          date: this.formatNum(lastDayOfLastMonth - startDay + i),
          day: weekstart + i - 1 || 7,
          month: m - 1 >= 0 ? this.formatNum(m - 1) : 12,
          year: m - 1 >= 0 ? y : y - 1,
        });
      }
      for (let j = 1; j <= lastDateOfMonth; j++) {
        dates.push({
          date: this.formatNum(j),
          day: (j % 7) + firstDayOfMonth - 1 || 7,
          month: this.formatNum(m),
          year: y,
          isCurM: true, //是否当前月份
        });
      }
      for (let k = 1; k <= endDay; k++) {
        dates.push({
          date: this.formatNum(k),
          day: (lastDateOfMonth + startDay + weekstart + k - 1) % 7 || 7,
          month: m + 1 <= 11 ? this.formatNum(m + 1) : 0,
          year: m + 1 <= 11 ? y : y + 1,
        });
      }
      // console.log(dates);
      return dates;
    },
    isWorkDay(y, m, d) {
      //是否工作日
      let ymd = `${y}/${m}/${d}`;
      let formatDY = new Date(ymd.replace(/-/g, "/"));
      let week = formatDY.getDay();
      if (week == 0 || week == 6) {
        return false;
      } else {
        return true;
      }
    },
    isFutureDay(y, m, d) {
      //是否未来日期
      let ymd = `${y}/${m}/${d}`;
      let formatDY = new Date(ymd.replace(/-/g, "/"));
      let showTime = formatDY.getTime();
      let curTime = new Date().getTime();
      if (showTime > curTime) {
        return true;
      } else {
        return false;
      }
    },
    // 标记日期
    isMarkDay(y, m, d) {
      let flag = false;
      for (let i = 0; i < this.markDays.length; i++) {
        let dy = `${y}-${m}-${d}`;
        if (this.markDays[i] == dy) {
          flag = true;
          break;
        }
      }
      return flag;
    },
    isToday(y, m, d) {
      let checkD = y + "-" + m + "-" + d;
      let today = this.getToday().date;
      if (checkD == today) {
        return true;
      } else {
        return false;
      }
    },
    // 展开收起
    toggle() {
      this.monthOpen = !this.monthOpen;
      this.headerBar = !this.headerBar;
      if (this.monthOpen) {
        this.positionTop = 0;
      } else {
        let index = -1;
        this.dates.forEach((i, x) => {
          this.isToday(i.year, i.month, i.date) && (index = x);
        });
        this.positionTop = -((Math.ceil((index + 1) / 7) || 1) - 1) * 80;
      }
    },
    // 点击回调
    selectOne(i, event) {
      let date = `${i.year}-${i.month}-${i.date}`;
      let selectD = new Date(date).getTime();
      let curTime = new Date().getTime();
      let week = new Date(date).getDay();
      let weekText = ['日', '月', '火', '水', '木', '金', '土'];
      let formatWeek = weekText[week];
      let response = {
        date: date,
        week: formatWeek,
      };
      if (!i.isCurM) {
        // console.log('不在当前月范围内');
        return false;
      }
      if (selectD > curTime) {
        if (this.disabledAfter) {
          console.log("未来日期不可选");
          return false;
        } else {
          this.choose = date;
          this.$emit("onDayClick", response);
        }
      } else {
        this.choose = date;
        this.$emit("onDayClick", response);
      }
      console.log(response);
    },
    //改变年月
    changYearMonth(y, m) {
      this.dates = this.monthDay(y, m);
      this.y = y;
      this.m = m;
    },
    changeMonth(type) {
      if (type == "pre") {
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
  },
};
</script>

<style lang="scss" scoped>
.calendar-wrapper {
  color: #000;
  font-size: 14px;
  text-align: center;
  // background-color: #fe6766;
  padding-bottom: 5px;
  // box-shadow: 0 45px rgba(#cf3a18, .32);
  // border-top-right-radius: 32px;
  // border-top-left-radius: 32px;

  .head-title {
    display: flex;
    align-items: center;
    width: 90%;
    margin: 18px auto;
    background-color: #fff;

    image {
      flex-shrink: 0;
      width: 24px;
      height: 24px;
    }

    .tit {
      width: 100%;
      text-align: center;
      font-weight: 700;
      font-size: 20px;
      color: #1d1d1f;
    }
  }
  
  
  .rest{
      color: #D93025;
  }

  .header {
    height: 100px;
    color: #000;
    background-color: #f5f5f7;
    font-size: 18px;
    font-weight: bold;

    .header-b {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 90%;
      margin: 0 auto;
      padding-top: 22px;
      margin-bottom: 24px;

      .header-a {
        display: flex;

        .preWidth {
          margin-right: 35px;
        }
      }
    }

    .week {
      display: flex;
      align-items: center;
      font-size: 14px;
      color: #86868b;
      font-weight: 400;

      .week-day {
        flex: 1;
      }

      .week-day:first-child {
        color: #e3746d;
      }

      .week-day:last-child {
        color: #71a6ee;
      }
    }
  }

  .content {
    // position: relative;
    // overflow: hidden;
    padding-top: 10px;
    // height: 680px;
    // transition: height 0.4s ease;

    .days {
      // transition: top 0.3s;
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      // position: relative;

      .item {
        // position: relative;
        display: block;
        height: 80px;
        border-bottom: 1px solid #eaeaea;
        line-height: 80px;
        width: calc(100% / 7);

        .day {
         font-style: normal;
         display: inline-block;
         vertical-align: middle;
         width: 30px;
         height: 30px;
         line-height: 30px;
         // overflow: hidden;
         border-radius: 30px;

          &.choose {
            background-color: #1A73E8;
            color: #fff;
          }

          &.nolm {
            color: #000;
            opacity: 0.3;
          }
        }

        .isWorkDay {
          color: #000;
        }

        .notSigned {
          // font-style: normal;
          // width: 8px;
          // height: 8px;
          // background: #fa7268;
          // border-radius: 10px;
          // position: absolute;
          // left: 50%;
          // bottom: 0;
          // pointer-events: none;
        }

        .today {
          color: #000;
          // background-color: #a8c0ff;
        }

        .workDay {
          // font-style: normal;
          width: 4px;
          height: 4px;
          // background: #4d7df9;
          border-radius: 5px;
          // position: absolute;
          // left: 50%;
          // bottom: 0;
          // pointer-events: none;
        }

        .markDay {
          font-style: normal;
          width: 4px;
          height: 4px;
          // background: #fc7a64;
          border-radius: 5px;
          position: absolute;
          left: 50%;
          bottom: 0;
          pointer-events: none;
        }
      }
    }
  }

  .hide {
    height: 40px !important;
  }

  .dropDown {
    width: 25px;
    height: 25px;
    // background-color: #fc7a64;
  }

  .weektoggle {
    width: 50px;
    height: 15px;
    position: relative;
    bottom: -25px;
    left: 45%;
    // background-color: #fe6766;
    border-radius: 0 0 10px 10px;
    padding: 5px;

    image {
      width: 12px;
      height: 9px;
    }
  }
}

.xiu-date {
  margin: 40px auto;
  width: 300px;
  // height: 288px;
  background: #f9f9f9;
  box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
  border-radius: 10px;
  text-align: center;
  padding-top: 10px;

  .xiu-day-title {
    font-size: 20px;
    color: #1d1d1f;
    margin-bottom: 10px;
  }

  .yingye {
    font-size: 20px;
    color: #007aff;
    margin-bottom: 10px;
  }

  .xiu-time {
    font-size: 17px;
    color: #1d1d1f;
    margin-bottom: 10px;
  }

  .xiu-pad {
    padding-bottom: 10px;
  }
}
</style>
