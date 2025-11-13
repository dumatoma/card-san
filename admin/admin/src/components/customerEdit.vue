<template>
  <div class="container">
    <div class="guide">
      <span>顧客管理</span> &gt; <span>顧客情報</span> &gt; 顧客情報の編集
    </div>
    <div class="detailContent">
      <div class="itemd">
        <div class="item_left"> ID(メールアドレス) </div>
        <div class="item_right" v-text="shop.shop_admin_email"></div>
      </div>
      <div class="itemd">
        <div class="item_left"> 契約番号 </div>
        <div class="item_right" v-text="orderno"></div>
      </div>
      <div class="itemd">
        <div class="item_left"> 氏名 </div>
        <div class="item_right" v-text="info.value1">
          <!-- <input type="text" v-model=""> -->
        </div>
      </div>
      <div class="itemd">
        <div class="item_left"> 法人名 </div>
        <div class="item_right" v-text="info.value2">
          <!-- <input type="text" v-model=""> -->
        </div>
      </div>
      <div class="itemd">
        <div class="item_left"> 電話番号 </div>
        <div class="item_right" v-text="info.value3">
          <!-- <input type="text" v-model="" /> -->
        </div>
      </div>
      <div class="itemd">
        <div class="item_left"> 郵便番号 </div>
        <div class="item_right" v-text="info.value4">
          <!-- <input type="text" v-model="" /> -->
        </div>
      </div>
      <div class="itemd">
        <div class="item_left"> 住所 </div>
        <div class="item_right" v-text="info.value5">
          <!-- <input type="text" v-model=""  style="width: 400px;"/> -->
        </div>
      </div>
      <div class="itemd">
        <div class="item_left"> 店舗名 </div>
        <div class="item_right">
          <input type="text" v-model="info.value6" />
        </div>
      </div>
      <div class="itemd">
        <div class="item_left"> 店舗電話番号 </div>
        <div class="item_right" v-text="info.value7">
          <!-- <input type="text" v-model="info.value7" /> -->
        </div>
      </div>
      <div class="itemd">
        <div class="item_left"> 店舗郵便番号 </div>
        <div class="item_right">
          <input type="text" v-model="info.value8" />
        </div>
      </div>
      <div class="itemd">
        <div class="item_left"> 店舗住所 </div>
        <div class="item_right" v-text="info.value9">
          <!-- <input type="text" v-model="info." style="width: 400px;"/> -->
        </div>
      </div>
      <div class="itemd">
        <div class="item_left"> 登録日 </div>
        <div class="item_right" v-text="formateTime(shop.create_time)"></div>
      </div>
      <div class="itemd" v-if="vip">
        <div class="item_left"> 契約開始年月 </div>
        <div class="item_right" v-text="formateTime(vip.start_time)"></div>
      </div>
      <div class="itemd" v-if="vip">
        <div class="item_left"> 契約期間 </div>
        <div class="item_right" v-text="vip.month? vip.month + '月' : ''"></div>
      </div>
      <div class="itemd">
        <div class="item_left"> 業種 </div>
        <div class="item_right" v-text="shop.shop_cate_name"></div>
      </div>
      <div class="itemd" v-if="vip">
        <div class="item_left"> 契約プラン </div>
        <div class="item_right" v-text="vip.type == 1?'ライト' :  vip.type == 2?'スタンダード' : 'プレミアム'"></div>
      </div>
      <div class="itemd">
        <div class="item_left"> ステータス </div>
        <div class="item_right" v-text="isq"></div>
      </div>
      <div class="btns"><button type="button" @click="save" class="el-button el-button--primary"
          style="margin-left: 10px;"><span>保存</span></button>
      </div>
    </div>
  </div>

</template>

<script scoped>
  import {
    getCustomerDetail,
    editCustomer
  } from "@/http/api.js"
  export default {
    name: 'CustomerEdit',
    data() {
      return {
        shop: {},
        admin: {},
        vip: {},
        orderno:"",
        info: {
          value1: "",
          value2: "",
          value3: "",
          value4: "",
          value5: "",
          value6: "",
          value7: "",
          value8: "",
          value9: "",
          id: "",
          isq:''
        }
      }
    },
    beforeCreate() {},
    mounted() {},
    created() {
      let that = this
      let id = that.$route.query.id
      that.id = id
      that.getCustomerDetail(id)
    },
    methods: {
      save() {
        let that = this
        let data = {}
        data._method = 'put'
        data.id = that.id
        data.shop_name = that.info.value6
        data.shop_admin_email = that.info.value8
        editCustomer(data).then((res) => {
          if(res.code == 200){
            that.$message({
              message: res.message,
              type: 'success'
            });
          }
        })
      },
      getCustomerDetail(e) {
        let that = this
        let data = e
        let info = {}
        getCustomerDetail(data).then((res) => {
          console.log("res", res)
          if (res.code == 200) {
            that.orderno = res.data.order_no
            that.isq = res.data.vip_status_name
            that.shop = res.data.shop
            that.admin = res.data.shop_admin
            that.vip = res.data.vip
            info.value1 = res.data.shop_admin.name
            info.value2 = res.data.shop.legal_person
            info.value3 = res.data.shop_admin.phone
            info.value4 = res.data.shop_admin.zip_code
            info.value5 = res.data.shop_admin.address1
            info.value6 = res.data.shop.shop_name
            info.value7 = res.data.shop.phone
            info.value8 = res.data.shop_admin.email
            info.value9 = res.data.shop.address1
            that.info = info
          }
        })
      },
      formateTime(e) {
        if (e) {
          // 时间戳
          let timestamp = e
          // 此处时间戳以毫秒为单位
          let date = new Date(parseInt(timestamp) * 1000);
          let Year = date.getFullYear();
          let Moth = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1);
          let Day = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate());
          let Hour = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours());
          let Minute = (date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes());
          let Sechond = (date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds());
          let GMT = Year + '-' + Moth + '-' + Day
          return GMT
        }

      }
    }
  }
</script>

<style scoped>
  input {
    width: 200px;
    border: none;
    outline: none;
    border-bottom: 1px solid #000;
    box-sizing: border-box;
    padding: 5px;
  }

  .tableContent {
    width: 100%;
    margin-top: 62px;
  }

  .btns {
    display: flex;
    flex-direction: row-reverse;
  }

  .itemd {
    display: flex;
    align-items: center;
    margin-bottom: 19px;
  }

  .item_right {
    font-size: 14px;
    font-family: Hiragino Sans-W3, Hiragino Sans;
    font-weight: 400;
  }

  .item_left {
    width: 150px;
    text-align: center;
    color: #707070;
    font-size: 14px;
  }

  .detailContent {
    width: 996px;
    height: 700px;
    background: #fff;
    box-shadow: 0 0 12px 1px rgba(0, 0, 0, .16);
    opacity: 1;
    margin-top: 40px;
    overflow-y: auto;
    box-sizing: border-box;
    padding: 46px;
  }

  .detailContent::-webkit-scrollbar {
    display: none !important;
  }

  .more {
    width: 20px;
    height: 32px;
    cursor: pointer;
  }

  .dates {
    display: flex;
    justify-content: space-between;
    margin-top: 37px;
  }

  .dates_right {
    font-size: 24px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 700;
    color: #1d1d1f;
  }

  .dates3 {
    width: 134px;
    height: 48px;
    background: #2699fb;
    border-radius: 24px;
    opacity: 1;
    color: #fff;
    text-align: center;
    line-height: 48px;
    margin-left: 30px;
    cursor: pointer;
  }

  .dates2 {
    display: flex;
    align-items: center;
  }

  .dates2 span {
    margin: 0 16px;
    font-size: 24px;
  }

  .dates1 {
    font-size: 19px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 700;
    color: #1d1d1f;
    margin-right: 42px;
  }

  .dates_left {
    display: flex;
    align-items: center;
  }

  .sceen {
    display: flex;
    align-items: center;
    margin-top: 36px;
  }

  .sceen_left {
    font-size: 19px;
    font-weight: 700;
    color: #1d1d1f;
    margin-right: 24px;
  }

  .searchIcon {
    display: flex;
    align-items: center;
    cursor: pointer;
  }

  .searchIcon img {
    width: 26px;
    height: 26px;
    margin-right: 13px;
  }

  .searchIcon span {
    font-size: 19px;
    font-family: Hiragino Sans-W3, Hiragino Sans;
    font-weight: 400;
    color: #1d1d1f;
    margin-right: 13px;
  }

  .inp {
    flex: 1;
  }

  .inp input {
    display: inline-block;
    width: 100%;
    height: 40px;
    background: transparent;
    border: none;
    outline: none;
    box-sizing: border-box;
    padding: 6px;
    font-size: 19px;
  }

  .cusContent {
    width: 1099px;
    height: 680px;
    background: #fff;
    box-shadow: 0 0 12px 1px rgba(0, 0, 0, .16);
    opacity: 1;
    border: 1px solid #a5c9ff;
    margin-top: 26px;
    box-sizing: border-box;
    padding: 48px 28px;
    margin-bottom: 12px;
  }

  .searchContent {
    width: 720px;
    height: 48px;
    background: #eaeaea;
    border-radius: 24px;
    opacity: 1;
    border: 1px solid #eaeaea;
    display: flex;
    align-items: center;
    box-sizing: border-box;
    padding: 0 19px;
  }

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
