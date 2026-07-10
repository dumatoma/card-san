<template>
  <div>
    <div class="title">定休日と営業時間</div>
    <div class="tip no-size">
      不定期な休業日や営業時間は、営業日カレンダーの日付から直接設定してください。
    </div>
    <div class="cont">
      <div class="cont_top u-flex">
        <div style="margin-right: 60px">定休日</div>
        <div style="margin-right: 60px">開始</div>
        <div>終了</div>
      </div>
      <div class="cont_bottom">
        <div
          class="day_item u-flex u-col-top no-size"
          v-for="(item, index) in dayList"
          :key="index"
        >
            <el-checkbox :checked="!item.business" @change="checkClick(item, index)">
              <div
                class="text"
                :style="{
                  color:
                    index == 0
                      ? '#D93025'
                      : index == Number(dayList.length - 1)
                      ? '#1A73E8'
                      : '',
                }"
              >
                {{ weeks[index] }}
              </div>
            </el-checkbox>

          <div
            v-for="(itemT, indexT) in item.business_time"
            :key="indexT"
            v-if="item.business == 0"
          >
            <!-- <div >休業日</div> -->
          </div>

          <div class="u-flex-col" v-if="item.business == 1">
            <div
              class="u-flex shou day_time"
              :class="indexT == 1 ? 'm-t-20' : ''"
              v-for="(itemT, indexT) in item.business_time"
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
                @click="plus(index)"
                v-if="indexT == 0"
              ></i>
              <img src="@/static/tclose.png" @click="reduce(index)" v-else />
            </div>
          </div>
          <div class="rest" v-else>休業日</div>
        </div>

        <!-- <div class="u-flex-center red_tip">
          <img
            src="@/static/index/notification.png"
            alt=""
          />営業時間の設定が不足しています
        </div> -->
        <div class="bottom u-flex u-row-between">
          <div class="btn grey_btn shou" @click="back">戻　る</div>
          <div class="btn blue_btn shou" @click="keepClick">保　存</div>
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
import {getCalendar,setCalendar} from "@/http/api.js"
export default {
  data() {
    return {
      checkedCities: [],
      dayList: [],
      weeks:['日','月','火','水','木','金','土'],
      keepShow: false,
      checkList: [],
    };
  },
  created() {
    let that = this  
    let date = new Date()
    let year = date.getFullYear()
    let month = (date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
    let data = {}
    data["type"] = "week"
    data['date'] = year+"-"+month
    getCalendar(data).then((res) => {
        console.log('res',res)
        if(res.code == 200){
            if(res.data.week_business.length < 7){
                that.dayList = [{
                    business: true,
                    week: "日",
                    key:0,
                    business_time: [{
                        start: "09:00",
                        end: "22:00"
                    }]
                }, {
                    business: true,
                    week: "月",
                    key:1,
                    business_time: [{
                        start: "09:00",
                        end: "22:00"
                    }]
                }, {
                    business: true,
                    week: "火",
                    key:2,
                    business_time: [{
                        start: "09:00",
                        end: "22:00"
                    }]
                }, {
                    business: true,
                    week: "水",
                    key:3,
                    business_time: [{
                        start: "09:00",
                        end: "22:00"
                    }]
                }, {
                    business: true,
                    week: "木",
                    key:4,
                    business_time: [{
                        start: "09:00",
                        end: "22:00"
                    }]
                }, {
                    business: true,
                    week: "金",
                    key:5,
                    business_time: [{
                       start: "09:00",
                       end: "22:00"
                    }, ]
                }, {
                    business: true,
                    week: "土",
                    key:6,
                    business_time: [{
                        start: "09:00",
                        end: "22:00"
                    }, ]
                }]
            }else{
                that.dayList = res.data.week_business
            }
            
             
        }
    })
   
  },
  methods: {
    handleCheckedCitiesChange(value) {
      this.checkList = value;
    },
    checkClick(item, index) {
        if(this.dayList[index].business == 0) this.dayList[index].business = 1
        else this.dayList[index].business = 0
    },
    keepClick() {
        let that = this
        if(that.dayList.length == 0){
            that.$message({
            	message: "設定が不足しています",
            	type: 'error',
                offset:400
            });
            return
        }
        let FoundError = new Error("Found");
        that.dayList.forEach((v,i) => {
            if(v.business == 1){
                v.business_time.forEach((value,index) => {
                    if(value.end == value.start){
                        that.$message({
                        	message: "設定が不足しています",
                        	type: 'error',
                            offset:400
                        });
                        throw FoundError;
                    }
                })
            }
        })
        console.log(that.dayList)
        
        let temp = that.dayList
        let o = Object.fromEntries(temp.map(item => [item.key, item]))
        let data = {}
        data['type'] = "week"
        data['week_business'] = o
        console.log(data)
        setCalendar(data).then((res) => {
           if(res.code == 200){
              that.keepShow = true;
              setTimeout(() => {
                that.keepShow = false;
              }, 1500); 
           }else{
               that.$message.error(res.message)
           } 
        })
    },
    plus(index) {
      let time = {
        start: "00:00",
        end: "00:00",
      };
      if (this.dayList[index].business_time.length < 2) {
        this.dayList[index].business_time.push(time);
      }
    },
    reduce(index) {
      this.dayList[index].business_time.splice(1, 1);
    },
    back(){
      this.$router.push({
          path: "/calendar",
        });
    }
  },
};
</script>

<style lang="scss" scoped>
.title {
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
}
.tip {
  width: 410px;
  height: 59px;
  line-height: 18px;
  font-size: 12px;
  color: #707070;
  margin-top: 20px;
  border-radius: 10px;
  padding: 12px;
  background: rgba(29, 29, 31, 0.05);
}
.cont {
  width: 410px;
  background: #fff;
  margin-top: 20px;
  padding-bottom: 28px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .cont_top {
    height: 16px;
    font-size: 16px;
    color: #707070;
    padding: 15px 0 7px 62px;
    border-bottom: 1px solid #d2d2d7;
  }
  .cont_bottom {
    padding: 0 15px 0 20px;
    .day_item {
      padding: 14px 51px 18px 20px;
      border-bottom: 1px solid #d2d2d7;
      .text {
        margin-left: 13px;
        font-weight: bold;
      }
      .day_time {
        margin-left: 75px;
        .el-icon-circle-plus-outline {
          color: #1a73e8;
          margin-left: -15px;
          font-size: 20px;
          z-index: 1;
        }
        img {
          width: 20px;
          height: 20px;
          margin-left: -15px;
          z-index: 1;
        }
        ::v-deep .el-date-editor.el-input,
        .el-date-editor.el-input__inner {
          width: 90px;
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
      .rest {
        font-size: 16px;
        color: #d93025;
        margin-left: 115px;
      }
    }
  }
  .red_tip {
    font-size: 14px;
    color: #d93025;
    padding: 30px 0 40px 0;
    img {
      width: 14px;
      height: 14px;
      margin-right: 10px;
    }
  }
  .bottom {
    width: 90%;
    padding-left: 15px;
    margin-top: 20px;
    .btn {
      width: 150px;
      height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 26px;
    }
    .blue_btn {
      color: #fff;
      background: #1a73e8;
    }
    .grey_btn {
      background: rgba(29, 29, 31, 0.05);
      border: 1px solid #707070;
    }
  }
}
::v-deep .el-checkbox__input.is-checked .el-checkbox__inner,
.el-checkbox__input.is-indeterminate .el-checkbox__inner {
  background: #1a73e8;
  border: 1px solid #1a73e8;
}
::v-deep .el-checkbox__input.is-checked + .el-checkbox__label {
  color: #1d1d1f;
}
::v-deep .el-checkbox__inner {
  border: 1px solid #aaa;
}

.fixed {
  position: fixed;
  top: 0%;
  left: 0%;
  width: 100vw;
  height: 100vh;
  z-index: 1;
  background: rgba(29, 29, 31, 0.2);
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
