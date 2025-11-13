<template>
  <div class="container">
    <div class="guide">
      Dashboard
    </div>
    <div class="dates" v-text="dates"></div>
    <div class="detailContent">
      <div class="detail_item">
        <div class="item_top">
          <div class="item_top_title"> 今日のトライアル </div>
          <div class="item_top_number" v-text="info.today_try_count"></div>
        </div>
        <div class="item_bottom">
          <div class="item_top_title"> 現在の契約者数 </div>
          <div class="item_top_number" v-text="info.vip_count"></div>
          <div class="itemContent">
            <div class="content_title"> プラン毎 </div>
            <div class="content_detail">
              <div class="detail_i">
                <div class="i_left"> ライト </div>
                <div class="i_right" v-text="info.vip1_count"></div>
              </div>
              <div class="detail_i">
                <div class="i_left"> スタンダード </div>
                <div class="i_right" v-text="info.vip2_count"></div>
              </div>
              <div class="detail_i">
                <div class="i_left"> プレミアム </div>
                <div class="i_right" v-text="info.vip3_count"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="detail_item">
        <div class="item_top">
          <div class="item_top_title"> 今月のトライアル </div>
          <div class="item_top_number" v-text="info.month_try_count"></div>
        </div>
        <div class="item_bottom">
          <div class="item_top_title"> 今月の解約数 </div>
          <div class="item_top_number" v-text="info.month_unvip_count"></div>
          <div class="itemContent">
            <div class="content_title"> 解約率 </div>
            <div class="content_detail">
              <div class="rate" v-text="`${formatNumber(info.month_unvip_rate)}%`"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="detail_item">
        <div class="item_top">
          <div class="item_top_title"> トライアル中 </div>
          <div class="item_top_number" v-text="info.try_count"></div>
        </div>
        <div class="item_bottom">
          <div class="item_top_title"> 今月の売上（クレカのみ） </div>
          <div class="item_top_number" v-text="info.month_sale"></div>
          <div class="itemContent">
            <div class="content_title"> 前月比（クレカのみ） </div>
            <div class="content_detail">
              <div class="rate" v-text="`${formatNumber(info.month_sale_rate)}%`"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script>
  import {
    getIndexData
  } from "@/http/api.js"
  export default {
    name: 'Home',
    data() {
      return {
        msg: 'This is Home Pages',
        dates: "",
        info: {}
      }
    },
    created() {
      this.getData()
    },
    methods: {
      toLogin() {
        this.$router.push("Login")
      },
      getData() {
        let that = this
        getIndexData().then((res) => {
          console.log(res)
          if (res.code == 200) {
            that.info = res.data
            that.getDates()
          }
        })
      },
      getDates() {
        let that = this
        let date = new Date(that.info.timestamp * 1000)
        console.log(date)
        let year = date.getFullYear()
        let month = date.getMonth() + 1
        let da = date.getDate()
        that.dates = year + "年" + month + "月" + da + "日"
      },
      formatNumber(e){
          let num = e * 1
          let result = num.toFixed(2)
          return result
      },
    }
  }
</script>

<style scoped>
  .detailContent {
    display: flex;
    align-items: center;
    margin-top: 24px;
  }

  .detail_item {
    margin-right: 48px;
  }

  .content_title {
    font-size: 17px;
    text-align: center;
    margin-bottom: 12px;
    font-weight: 400;
    color: #0a4b6d;
  }

  .i_left {
    font-weight: 400;
    color: #0a4b6d;
    font-size: 12px;
  }

  .i_right {
    font-weight: 400;
    color: #0a4b6d;
    font-size: 12px;
  }

  .detail_i {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 12px;
    padding: 12px 0;
    font-weight: 400;
    color: #0a4b6d;
  }

  .rate {
    font-size: 30px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 400;
    color: #0a4b6d;
    text-align: center;
    margin-top: 48px;
  }

  .item_bottom {
    width: 245px;
    background: #fff;
    box-shadow: 0 3px 8px 1px rgba(0, 0, 0, .16);
    box-sizing: border-box;
    height: 330px;
    margin-top: 36px;
    padding-top: 21px;
  }

  .itemContent {
    margin-top: 40px;
    box-sizing: border-box;
    padding: 0 16px;
  }

  .item_top_number {
    font-size: 30px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 400;
    color: #0a4b6d;
    text-align: center;
    margin-top: 36px;
  }

  .item_top_title {
    font-weight: 400;
    color: #0a4b6d;
    font-size: 17px;
    text-align: center;
  }

  .item_top {
    height: 144px;
    padding-top: 17px;
    width: 245px;
    background: #fff;
    box-shadow: 0 3px 8px 1px rgba(0, 0, 0, .16);
    box-sizing: border-box;
  }

  .dates {
    font-family: Hiragino Sans-W3, Hiragino Sans;
    font-weight: 400;
    color: #0a4b6d;
    font-size: 20px;
  }

  .guide {
    width: 100%;
    font-size: 25px;
    font-family: Hiragino Sans-W7, Hiragino Sans;
    font-weight: bolder;
    color: #174a84;
  }
</style>
