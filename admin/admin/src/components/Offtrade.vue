<template>
  <div class="container">
    <div class="guide">
      分析 > 契約・解約状況
    </div>
    <div class="pik">
      <div id="pic">
        <el-select v-model="value" @change="dateChange">
          <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
          </el-option>
        </el-select>
      </div>
    </div>
    <div class="contains">
      <div class="con_left">
        <div class="table">
          <div class="table_title">
            <div class="title_item"> 年月 </div>
            <div class="title_item"> トライアル/月 </div>
            <div class="title_item"> 成約数/月 </div>
            <div class="title_item"> 成約率/月 </div>
            <div class="title_item"> 解約数/月 </div>
            <div class="title_item"> 解約率/全 </div>
            <div class="title_item"> 契約数/全 </div>
          </div>
          <div class="table_content">
            <div class="table_row" v-for="item,index in list">
              <div class="table_item" v-text="item.date"></div>
              <div class="table_item" v-text="item.try_count"></div>
              <div class="table_item" v-text="item.vip_count"></div>
              <div class="table_item" v-text="item.vip_rate"></div>
              <div class="table_item" v-text="item.unvip_count"></div>
              <div class="table_item" v-text="item.unvip_rate"></div>
              <div class="table_item" v-text="item.all_vip_count"></div>
            </div>

          </div>
        </div>
      </div>
      <div class="con_right">
        <div class="industry">
          <div class="in_title"> 業種別 </div>
          <div class="incontent">
            <div class="ins_title">
              <div class="ins_title_item"> 業種 </div>
              <div class="ins_title_item"> トライアル </div>
              <div class="ins_title_item"> 契約数 </div>
              <div class="ins_title_item"> 解約数 </div>
            </div>
            <div class="ins_content">
              <div class="ins_row" v-for="item,index in cates">
                <div class="ins_item">
                  <div class="insname" :style="`background:${item.color}`" v-text="item.cate_name"></div>
                </div>
                <div class="ins_item" v-text="item.try_count"></div>
                <div class="ins_item" v-text="item.vip_count"></div>
                <div class="ins_item" v-text="item.unvip_count"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script>
  import {
    getOffSale,
    getIndexData
  } from "@/http/api.js"
  export default {
    name: 'Offtrade',
    data() {
      return {
        value: "",
        options: [],
        list: [],
        cates:[]
      }
    },
    beforeCreate() {
      let that = this
      getIndexData().then((res) => {
        if (res.code == 200) {
          let da = new Date(res.data.timestamp * 1000)
          let year = da.getFullYear()
          that.getData(year)
          that.value = year
          for (let i = -4; i < 1; i++) {
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
      dateChange(e) {
        console.log(e)
        this.getData(e)
      },
      getData(year) {
        let that = this
        getOffSale(year).then((res) => {
          if (res.code == 200) {
            console.log(res)
            that.list = res.data.data
            that.cates = res.data.shop_cate
          }
        })
      }
    }
  }
</script>

<style scoped>
  .industry {
    width: 450px;
    margin-top: 20px;
    margin-left: 30px;
  }

  .ins_title_item {
    flex: 1;
    text-align: center;
    color: #fff;
    padding: 16px 0;
  }

  .insname {
    display: inline-block;
    padding: 5px;
    border-radius: 5px;
    color: #fff;
  }

  .ins_item {
    flex: 1;
    text-align: center;
    font-size: 12px;
    padding: 12px 0;
    font-family: Hiragino Sans-W3, Hiragino Sans;
  }

  .ins_row:nth-child(odd) {
    background: #fff !important;
  }

  .ins_row {
    display: flex;
  }

  .ins_content {
    border: 1px solid #007aff;
  }

  .ins_title {
    display: flex;
    background: #007aff;
  }

  .in_title {
    margin-bottom: 13px;
    font-size: 19px;
    font-family: Hiragino Sans-W7, Hiragino Sans;
    font-weight: 700;
    color: #174a84;
  }

  .table_content {
    border: 1px solid #174a84;
    height: 550px;
    overflow-y: scroll;
  }


  .table_item {
    flex: 1;
    text-align: center;
    padding: 31px 0;
    font-size: 14px;
    font-family: Hiragino Sans-W3, Hiragino Sans;
    font-weight: 400;
    color: #1d1d1f;
  }

  .table_row {
    display: flex;
    background: #fff;
    border-bottom: 1px solid #174a84;
    box-sizing: border-box;
  }

  .title_item {
    flex: 1;
    flex-shrink: 0;
    font-size: 14px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 400;
    color: #174a84;
    text-align: center;
  }

  .contains {
    display: flex;
  }

  .table_title {
    display: flex;
    align-items: center;
    margin-bottom: 6px;
  }

  .table {
    width: 710px;
    margin-top: 40px;
  }

  .guide {
    width: 100%;
    font-size: 25px;
    font-family: Hiragino Sans-W7, Hiragino Sans;
    font-weight: bolder;
    color: #174a84;
  }

  .el-select {
    width: 240px !important;
    height: 48px !important;
    background: #f1f9ff !important;
    border: 1px solid #bce0fd;
    border-radius: 8px;
  }

  .pik {
    margin-top: 26px;
  }

  #pic .el-input {
    height: 100% !important;
    width: 100% !important;
    color: #007aff !important;
  }

  #pic .el-select .el-input__inner {
    height: 100% !important;
    width: 100% !important;
    color: #007aff !important;
    background: transparent !important;
    border: none;
    font-size: 16px !important;
  }
</style>
