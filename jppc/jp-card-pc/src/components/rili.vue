
<template>
  <div class="month">
    <div class="header">{{ year }}年{{ monthName }}月</div>
    <div class="week no-size">
      <div v-for="item in week" class="weekIndex" :key="item">
        {{ item }}
      </div>
    </div>
    <div class="day no-size">
      <div
        v-for="(item, index) in days"
        :key="index"
        class="dayIndex"
        :class="{ choose: chooseIndex == index }"
      >
        <div v-if="item != 0" @click.stop="choose(index)" class="shou">
          {{ item }}
        </div>

        <!-- <div v-else class="setGrey">
          {{ item[0] }}
        </div> -->
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Month",
  data() {
    return {
      week: ["土", "日", "月", "火", "水", "木", "金"],
      month: [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December",
      ],
      days: [],
      monthLastDay: {
        0: 31,
        1: 28,
        2: 31,
        3: 30,
        4: 31,
        5: 30,
        6: 31,
        7: 31,
        8: 30,
        9: 31,
        10: 30,
        11: 31,
      },
      chooseIndex: -1,
      leapyear: false,
      firstDayIndex: 0,
    };
  },
  // computed () {
  // 	chosen:{
  // 		return new Date().getMonth() == this.monthIndex
  // 	}
  // },
  methods: {
    choose(index) {
      this.chooseIndex = index;
      // console.log(index - 1);
      this.$emit("choose", {
        name: this.days[index] || this.days[index][0],
        index: index,
        time: this.year + "/" + this.monthName + "/" + this.days[index],
        days: this.days,
      });
    },
    judgeDays() {
      let lastMonthIndex = this.monthIndex - 1 <= 0 ? 11 : this.monthIndex - 1;
      let lastDay = this.getMonthLastDay(this.year, lastMonthIndex);
      this.generateDays(lastDay, this.firstDayIndex);
    },
    getMonthLastDay(year, month) {
        console.log("year",year)
      if (month != 1) {
        return this.monthLastDay[month];
      } else {
        if (this.leapyear) {
          return 29;
        } else {
          return 28;
        }
      }
    },
    generateDays(index, lastDayNum) {
      let k = 0;
      let temp = 1;
      //这个for循环是push上个月的剩余日期，
      for (let i = lastDayNum; i > 0; i--) {
        this.days.push("0");
        k++;
      }

      index = 1;
      for (let i = 0; i < 42 - lastDayNum; i++) {
        //闰年二月
        if (this.leapyear) {
          if (index <= 29) {
            this.days.push(index.toString());
          }
          //非闰年月份
        } else if (index <= this.monthLastDay[this.monthIndex]) {
          this.days.push(index.toString());
          //这个else是push下个月的日期
        } else {
          this.days.push("0");
          temp++;
        }
        index++;
        k++;
      }
    },
    isLeapYear() {
      return (
        (0 == this.year % 4 && this.year % 100 != 0) || this.year % 400 == 0
      );
    },
  },

  mounted() {
      console.log(this.year)
    this.firstDayIndex = new Date(
      this.year + "/" + (this.monthIndex + 1) + "/" + "01"
    ).getDay();
    this.judgeDays();
    this.leapyear = this.isLeapYear();
    if (new Date().getMonth() == this.monthIndex) {
      this.chooseIndex = this.day + this.firstDayIndex;
    }
  },
  watch:{
      'year': {
              handler(newVal, oldVal) {
              },
              deep: true,
              immediate: true
          }
  },
  props: ["monthName", "monthIndex", "year", "day"],
};
</script>

<style scoped lang="scss">
* {
  font-weight: 600;
  font-family: "Microsoft YaHei";
}
.month {
  margin-top: 100px;
  width: 343px;
  height: 316px;
  background: #ffffff;
  box-shadow: 0px 10px 60px 1px rgba(0, 0, 0, 0.1);
  border-radius: 13px 13px 13px 13px;
  .header {
    font-weight: 400;
    color: #000000;
    padding: 11px 30px;
    font-size: 16px;
  }
  .week {
    text-align: center;
    width: 100%;
    color: rgba(60, 60, 67, 0.3);
    padding-left: 23px;
    // padding-top: 12px;
    padding-right: 21px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    .weekIndex {
      // display: inline-block;
      // flex: 1;
      line-height: 40px;
      // height: 1.5rem;
      flex-shrink: 0;
      width: 40px;
      height: 40px;
      font-size: 13px;
    }
  }
  .day {
    text-align: center;
    // width: 15rem;
    padding-left: 23px;
    // padding-top: 12px;
    padding-right: 21px;
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    .dayIndex {
      // display: inline-block;
      // margin: 0.3rem;
      // line-height: 1.5rem;
      line-height: 40px;
      flex-shrink: 0;
      width: 40px;
      height: 40px;
      // width: 1.5rem;
      font-size: 0.8rem;
      .setGrey {
        color: grey;
      }
    }
    .choose {
      background: rgba(0, 122, 255, 0.12);
      border-radius: 50%;
      color: #007aff;
      // color: white;
    }
  }
}
</style>
