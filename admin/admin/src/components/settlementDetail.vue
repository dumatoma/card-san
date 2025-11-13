<template>
  <div class="container">
    <div class="guide">
      <span @click="goback" style="cursor: pointer;">決済状況</span> > 決済詳細
    </div>
    <div class="contain">
      <div class="title"> 2023年1月 決済詳細 </div>
      <div class="dates"> 請求日:2023年1月10日 </div>
      <div class="sumb">
        <div class="subrow">
          <div class="sb_item"> 決済成立 </div>
          <div class="sb_item"> 2,000 </div>
          <div class="sb_item"> 4,000,000円 </div>
        </div>
        <div class="subrow">
          <div class="sb_item" style="cursor: pointer;"> 決済不成立 </div>
          <div class="sb_item" style="color: rgb(229, 0, 0);"> 2 </div>
          <div class="sb_item" style="color: rgb(229, 0, 0);"> 4,000円 </div>
        </div>
      </div>
      <div class="table">
        <div class="t_title">
          <div class="t_item"></div>
          <div class="t_item"> 決済金額 </div>
          <div class="t_item"> 件数 </div>
          <div class="t_item"> 料率 </div>
          <div class="t_item"> 手数料合計 </div>
          <div class="t_item"> 合計 </div>
        </div>
        <div class="table_content">
          <div class="table_row">
            <div class="row_item"> トータル </div>
            <div class="row_item"> 4,000,000円 </div>
            <div class="row_item"> 2,000 </div>
            <div class="row_item"> 4.0% </div>
            <div class="row_item"> 160,000円 </div>
            <div class="row_item"> 3,860,000円 </div>
          </div>
          <div class="table_row">
            <div class="row_item"> 内訳 </div>
            <div class="row_item"></div>
            <div class="row_item"></div>
            <div class="row_item"></div>
            <div class="row_item"></div>
            <div class="row_item"></div>
          </div>
          <div class="table_row">
            <div class="row_item"> クレジットカード </div>
            <div class="row_item"> 4,000,000円 </div>
            <div class="row_item"> 2,000 </div>
            <div class="row_item"> 4.0% </div>
            <div class="row_item"> 160,000円 </div>
            <div class="row_item"> 3,860,000円 </div>
          </div>
          <div class="table_row">
            <div class="row_item"> Apple Pay </div>
            <div class="row_item"> 4,000,000円 </div>
            <div class="row_item"> 2,000 </div>
            <div class="row_item"> 4.0% </div>
            <div class="row_item"> 160,000円 </div>
            <div class="row_item"> 3,860,000円 </div>
          </div>
          <div class="table_row">
            <div class="row_item"> Google Pay </div>
            <div class="row_item"> 4,000,000円 </div>
            <div class="row_item"> 2,000 </div>
            <div class="row_item"> 4.0% </div>
            <div class="row_item"> 160,000円 </div>
            <div class="row_item"> 3,860,000円 </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script>
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
        list: []
      }
    },
    beforeCreate() {
      let that = this
      console.log(that.$route.query.id)
      // getIndexData().then((res) => {
      //   if (res.code == 200) {
      //     let da = new Date(res.data.timestamp * 1000)
      //     let year = da.getFullYear()
      //     that.getData(year)
      //     that.value = year
      //     for (let i = -4; i < 5; i++) {
      //       let obj = {}
      //       obj.label = (year + i) + "年度"
      //       obj.value = year + i
      //       that.options.push(obj)
      //     }
      //   }
      // })
    },
    mounted() {},
    created() {},
    methods: {
      goback() {
        window.history.go(-1)
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
  .sumb {
    margin-top: 54px;
  }

  .subrow {
    display: flex;
    width: 450px;
    background: rgba(10, 75, 109, .08);
  }
  .table{
      margin-top: 48px;
      border: 1px solid #a5c9ff;
  }
  .table_row {
      display: flex;
  }
  .row_item{
      flex: 1;
      font-size: 14px;
      font-family: Hiragino Sans-W5,Hiragino Sans;
      font-weight: 400;
      color: #174a84;
      box-sizing: border-box;
      padding: 12px;
  }
  .t_item {
      flex: 1;
      box-sizing: border-box;
      padding: 0 12px;
  }
  .t_title {
      display: flex;
      align-items: center;
      box-sizing: border-box;
      padding: 34px 0 14px;
      background: rgba(0,122,255,.1);
  }
  .contain {
    width: 996px;
    box-sizing: border-box;
    padding: 60px 32px;
    box-shadow: 0 4px 7px 1px rgba(0, 0, 0, .16);
    background-color: #fff;
    border: 1px solid #a5c9ff;
    margin-top: 24px;
  }
  .sb_item {
      flex: 1;
      box-sizing: border-box;
      padding: 12px;
      font-size: 19px;
      font-family: Hiragino Sans-W3,Hiragino Sans;
      font-weight: 400;
      color: #174a84;
      text-align: center;
  }

  .title {
    font-size: 25px;
    font-family: Hiragino Sans-W7, Hiragino Sans;
    font-weight: 400;
    color: #174a84;
    font-weight: 700;
    text-align: center;
  }

  .guide {
    width: 100%;
    font-size: 25px;
    font-family: Hiragino Sans-W7, Hiragino Sans;
    font-weight: bolder;
    color: #174a84;
  }

  .dates {
    font-size: 19px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 400;
    color: #174a84;
    text-align: right;
    margin-top: 32px;
  }
</style>
