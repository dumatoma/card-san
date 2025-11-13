<template>
  <div class="container">
    <div class="guide">
      決済状況
    </div>
    <div class="contain">
      <div class="pik">
        <div id="pic">
          <el-select v-model="value" @change="dateChange">
            <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="table">
        <div class="title">
          <div class="title_item"> 売上月 </div>
          <div class="title_item"> 請求書発行日 </div>
          <div class="title_item"> 件数 </div>
          <div class="title_item"> 成立 </div>
          <div class="title_item"> 未決済 </div>
          <div class="title_item"> 決済金額 </div>
          <div class="title_item" style="flex: 0.5 1 0%;"></div>
        </div>
        <div class="table_content">
          <div class="table_row" v-for="item,index in list">
            <div class="table_item" v-text="item.date"></div>
            <div class="table_item" v-text="item.applydate"></div>
            <div class="table_item" v-text="item.count"></div>
            <div class="table_item" style="color: rgb(0, 122, 255);" v-text="item.cl"></div>
            <div class="table_item" v-text="item.un"></div>
            <div class="table_item" v-text="item.money"></div>
            <div class="table_item" style="flex: 0.5 1 0%;"><img @click="toDetail(item.id)" src="../assets/more.png" alt="" class="more"></div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script scoped>
  import {
    getIndexData,
    getYearSale
  } from "@/http/api.js"
  export default {
    name: 'Settlement',
    data() {
      return {
        value: "",
        options: [],
        list: [
          {
            date:"2022年12月分",
            applydate:"2022年12月10日",
            count:"2000",
            cl:"1918",
            un:"2",
            money:"4,000,000円",
            id:1
          },
          {
            date:"2022年12月分",
            applydate:"2022年12月10日",
            count:"2000",
            cl:"1918",
            un:"2",
            money:"4,000,000円",
            id:2
          },
          {
            date:"2022年12月分",
            applydate:"2022年12月10日",
            count:"2000",
            cl:"1918",
            un:"2",
            money:"4,000,000円",
            id:3
          },
          {
            date:"2022年12月分",
            applydate:"2022年12月10日",
            count:"2000",
            cl:"1918",
            un:"2",
            money:"4,000,000円",
            id:4
          },
          {
            date:"2022年12月分",
            applydate:"2022年12月10日",
            count:"2000",
            cl:"1918",
            un:"2",
            money:"4,000,000円",
            id:5
          },
          {
            date:"2022年12月分",
            applydate:"2022年12月10日",
            count:"2000",
            cl:"1918",
            un:"2",
            money:"4,000,000円",
            id:6
          },
          {
            date:"2022年12月分",
            applydate:"2022年12月10日",
            count:"2000",
            cl:"1918",
            un:"2",
            money:"4,000,000円",
            id:7
          }
        ]
      }
    },
    beforeCreate() {
      let that = this
      getIndexData().then((res) => {
        if (res.code == 200) {
          let da = new Date(res.data.timestamp * 1000)
          let year = da.getFullYear()
          // that.getData(year)
          that.value = year
          for (let i = -4; i < 5; i++) {
            let obj = {}
            obj.label = (year + i) + "年度"
            obj.value = year + i
            that.options.push(obj)
          }
        }
      })
    },
    mounted() {},
    created() {},
    methods: {
      toDetail(e){
        console.log(e)
        this.$router.push("/settlementDetail?id="+e)
      },
      toLogin() {
        this.$router.push("Login")
      },
      dateChange(e) {
        console.log(e)
        this.getData(e)
      },
      getData(year) {
        let that = this
        getYearSale(year).then((res) => {
          if (res.code == 200) {
            that.list = res.data.data
          }
        })
      },
    }
  }
</script>

<style scoped>
  .pik{
    box-sizing: border-box;
    padding: 26px;
    margin-top: 0 !important;
  }
  .table_item:last-child {
      width: 10%!important;
  }
  .table_item {
    flex: 1;
    text-align: center;
    box-sizing: border-box;
    padding: 26px 0;
  }

  .more {
    width: 20px;
    height: 32px;
    cursor: pointer;
  }

  .table_row {
    display: flex;
    align-items: center;
    border-top: 1px solid #a5c9ff;
    font-size: 17px;
  }

  .table_content {
    height: 550px;
    overflow-y: scroll;
  }

  .title_item {
    flex: 1;
    text-align: center;
  }

  .title {
    display: flex;
    align-items: center;
    width: 100%;
    margin-bottom: 12px;
  }

  .contain {
    width: 996px;
    background: #fff;
    box-shadow: 0 4px 7px 1px rgba(0, 0, 0, .16);
    opacity: 1;
    border: 1px solid #a5c9ff;
    margin-top: 24px;
  }

  .guide {
    width: 100%;
    font-size: 25px;
    font-family: Hiragino Sans-W7, Hiragino Sans;
    font-weight: bolder;
    color: #174a84;
  }
</style>
