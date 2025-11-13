<template>
  <div class="container">
    <div class="guide">
      分析 > 売上（クレカ）
    </div>
    <div class="pik">
      <div id="pic">
        <el-select v-model="value" @change="dateChange">
          <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
          </el-option>
        </el-select>
      </div>
    </div>

    <div class="tableContent">
      <div class="table_title">
        <div class="title_item w156"> 年月 </div>
        <div class="title_item w144"> 契約件数 </div>
        <div class="title_item w180"> 売上金額（クレカ） </div>
        <div class="title_item w354"> プラン別件数/売上（クレカ） </div>
        <div class="title_item w126"> 前月比（クレカ） </div>
      </div>
      <div class="table">
        <div class="tableRow" v-for="item,index in list">
          <div class="titem w156" v-text="item.date"></div>
          <div class="titem w144" v-text="item.vip_count"></div>
          <div class="titem w180" v-text="item.vip_amount"></div>
          <div class="titem w354">
            <div style="display: flex;">
              <div class="titem_left w118">
                <div class="ttitem bd br" style="color: rgb(112, 112, 112);"> ライト </div>
                <div class="ttitem bd br" style="color: rgb(112, 112, 112);"> スタンダード </div>
                <div class="ttitem br" style="color: rgb(112, 112, 112);"> プレミアム </div>
              </div>
              <div class="titem_left w92">
                <div class="ttitem bd br" v-text="item.vip1_count"></div>
                <div class="ttitem bd br" v-text="item.vip2_count"></div>
                <div class="ttitem br" v-text="item.vip3_count"></div>
              </div>
              <div class="titem_left w144">
                <div class="ttitem bd" v-text="item.vip1_amount"></div>
                <div class="ttitem bd" v-text="item.vip2_amount"></div>
                <div class="ttitem" v-text="item.vip3_amount"></div>
              </div>
            </div>
          </div>
          <div class="titem w126" v-text="item.prev_month_rate"></div>
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
  import { Select } from 'element-ui';
  export default {
    name: 'Sale',
    data() {
      return {
        value: "",
        options: [],
        list:[]
      }
    },
    beforeCreate() {
      let that = this
       getIndexData().then((res) => {
         if(res.code == 200){
           let da = new Date(res.data.timestamp*1000)
           let year = da.getFullYear()
           that.getData(year)
           that.value = year
           for(let i = -4; i < 1; i ++){
             let obj = {}
             obj.label = (year+i)+"年度"
             obj.value = year+i
             that.options.push(obj)
           }
         }
       })
    },
    mounted() {
    },
    created() {
    },
    methods: {
      toLogin() {
        this.$router.push("Login")
      },
      dateChange(e){
        console.log(e)
        this.getData(e)
      },
      getData(year) {
        let that = this
        getYearSale(year).then((res) => {
          if(res.code == 200){
            that.list = res.data.data
          }
        })
      },
    }
  }
</script>

<style scoped>
  .br {
    border-right: 1px solid #eaeaea;
    box-sizing: border-box;
    font-size: 14px;
  }

  .bd {
    border-bottom: 1px solid #eaeaea;
    box-sizing: border-box;
  }

  .titem_left {
    height: 90px;
    font-size: 14px;
    box-sizing: border-box;
  }

  .ttitem {
    height: 30px;
    text-align: right;
    line-height: 30px;
    padding: 0 6px;
  }

  .tableRow {
    display: flex;
    align-items: center;
    height: 90px;
    line-height: 90px;
    text-align: center;
    border-bottom: 1px solid #eaeaea;
    box-sizing: border-box;
  }

  .titem {
    border-right: 1px solid #eaeaea;
    box-sizing: border-box;
  }

  .table {
    border: 1px solid #103c6e;
    display: inline-block;
    background-color: #fff !important;
    height: 600px;
    overflow-y: scroll;
  }

  .table_title {
    display: flex;
    align-items: center;
  }

  .title_item {
    text-align: center;
    font-size: 17px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 400;
    color: #174a84;
    padding: 12px 0;
    margin-top: 13px;
  }

  .guide {
    width: 100%;
    font-size: 25px;
    font-family: Hiragino Sans-W7, Hiragino Sans;
    font-weight: bolder;
    color: #174a84;
  }


</style>
