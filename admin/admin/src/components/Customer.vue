<template>
  <div class="container">
    <div class="guide">
      顧客管理
    </div>
    <div class="cusContent">
      <!-- 导出按钮 -->
      <div class="export" @click="exportForm">
        Export CSV
      </div>
      <div class="searchContent">
        <div class="searchIcon">
          <img src="../assets/search.png" alt="" />
          <span>検索</span>
        </div>
        <div class="inp">
          <input v-model="keyword" type="text" placeholder="メールアドレス、電話番号、契約番号、店舗名、契約者、プラン">
        </div>
      </div>
      <div class="sceen">
        <div class="sceen_left">
          ステータス
        </div>
        <div class="sceen_right">
          <div id="pic">
            <el-select v-model="value" @change="dateChange">
              <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
              </el-option>
            </el-select>
          </div>
        </div>
      </div>

      <div class="dates">
        <div class="dates_left">
          <div class="dates1">
            対象期間
          </div>
          <div class="dates2">
            <el-date-picker v-model="value1" value-format="yyyy-MM-dd" type="date">
            </el-date-picker>
            <span>~</span>
            <el-date-picker v-model="value2" value-format="yyyy-MM-dd" type="date">
            </el-date-picker>
          </div>
          <div class="dates3" @click.stop="search">
            検索
          </div>
        </div>
        <div class="dates_right">
          検索結果: {{total}}件
        </div>
      </div>

      <div class="tableContent">
        <el-table ref="multipleTable" :data="tableData" tooltip-effect="dark" style="width: 100%"
          @selection-change="handleSelectionChange" height="320">
          <el-table-column type="selection" width="55">
          </el-table-column>
          <el-table-column prop="shop_name" label="名前" width="140">
          </el-table-column>
          <el-table-column prop="shop_admin_email" label="Email" width="220">
          </el-table-column>
          <el-table-column prop="phone" label="電話番号" width="120">
          </el-table-column>
          <el-table-column prop="create_time" label="登録日" width="120">
          </el-table-column>
          <el-table-column prop="vip_name" label="プラン" width="110">
          </el-table-column>
          <el-table-column label="契約周期" width="80">
            <template slot-scope="scope">
              <span v-text="`${scope.row.month}ヶ月`"></span>
            </template>
          </el-table-column>
          <el-table-column prop="vip_status_name" label="ステータス" width="110">
          </el-table-column>
          <el-table-column prop="" label=" " width="60">
            <template slot-scope="scope">
              <img class="more" v-if="selectArr.includes(scope.row.id)" @click.stop="toDel(scope.row)"
                src="../assets/del.svg" alt="" />
              <img class="more" v-else @click.stop="toDetail(scope.row)" src="../assets/more.png" alt="" />
            </template>
          </el-table-column>
        </el-table>
      </div>
    </div>
    <el-pagination :hide-on-single-page="true" @current-change="pageChange" :page-size="50" background
      layout="prev, pager, next" :total="total">
    </el-pagination>
    <div class="mask" v-if="show">
      <div class="maskContent" v-if="con == false">
          <div class="maskTitle">
            この顧客情報を削除しますか？
          </div>
          <div class="maskConfirm" @click="dels">
            削除する
          </div>
          <div class="maskCancel" @click="show = false">
            キャンセル
          </div>
      </div>
      <div class="comple" v-if="con == true">
        <img src="../assets/redright.png" alt="" />
        <div style="margin-top: 20px;">削除しました</div>
      </div>
    </div>
  </div>
</template>
<script scoped>
  import {
    getIndexData,
    getCustomerList,
    editCustomer
  } from "@/http/api.js"
  import {
    Select
  } from 'element-ui';
  export default {
    name: 'Sale',
    data() {
      return {
        value: '',
        con:false,
        show:false,
        value1: "",
        value2: "",
        page: 1,
        keyword: "",
        selectArr: [],
        delData:{},
        options: [{
            label: "全て",
            value: 0
          },
          {
            label: "契約中",
            value: 1
          },
          {
            label: "トライアル中",
            value: 2
          },
          {
            label: "トライアル終了",
            value: 3
          },
          {
            label: "解約済み",
            value: 4
          }
        ],
        list: [],
        tableData: [],
        total: 0,
        exportData: []
      }
    },
    beforeCreate() {
      let that = this

    },
    mounted() {},
    created() {
      this.getList()
    },
    methods: {
      pageChange(e) {
        console.log(e)
        this.page = e
        this.getList()
      },
      dels(){
        let that = this
        editCustomer(this.delData).then((res) => {
          if(res.code == 200){
            that.con = true
            that.getList()
            setTimeout(() => {
              that.show = false
              that.con = false
            },2000)
          }
        })
      },
      toDel(e) {
        let that = this
        let data = {}
        data._method = 'delete'
        data.id = e.id
        that.delData = data
        that.show = true
      },
      handleSelectionChange(e) {
        let temp = []
        e.forEach((v, i) => {
          temp.push(v.id)
        })
        this.selectArr = temp
      },
      // 格式化导出的数据
      formatCsv() {
        let that = this
        let t = that.tableData
        let s = []
        t.forEach((val, index) => {
          console.log(val)
          let obj = {}
          obj['ID(メールアドレス)'] = val.shop.shop_admin_email || '-'
          obj['契約番号'] = val.vip.order_no || val.last_vip.order_no
          obj['氏名'] = val.shop_admin.name || '-'
          obj['法人名'] = val.shop.legal_person || '-'
          obj['店舗名'] = val.shop_name || '-'
          obj['店舗電話番号'] = val.phone || '-'
          obj['店舗郵便番号'] = val.shop.zip_code || '-'
          obj['店舗住所'] = val.shop.address1 + val.shop.address2 || '-'
          obj['登録日'] = val.create_time || '-'
          obj['契約開始年月'] = val.vip.start_date || val.last_vip.start_date
          obj['契約期間'] = val.month > 0?val.month + "ヶ月" : '-'
          obj['業種'] = val.shop.shop_cate_name || '-'
          obj['契約プラン'] = val.vip_name || '-'
          obj['ステータス'] = val.vip_status_name || '-'
          s.push(obj)
        })
        that.exportData = s
        console.log(that.exportData)
      },


      exportForm() {
        let that = this
        const _self = this
        let jsonData = {
          trade: {
            tHeader: ["ID(メールアドレス)", '契約番号', '氏名', '法人名', '店舗名', '店舗電話番号', '店舗郵便番号','店舗住所','登録日','契約開始年月','契約期間','業種','契約プラン','ステータス'], //导出对应的列头名称
            filterVal: ["ID(メールアドレス)", '契約番号', '氏名', '法人名', '店舗名', '店舗電話番号', '店舗郵便番号','店舗住所','登録日','契約開始年月','契約期間','業種','契約プラン','ステータス'],
            list: _self.exportData
          }
        }
        _self.exportPathMethod(jsonData)
      },
      exportPathMethod(data) {
        let mainLists = data.trade
        let _self = this
        let mainTitle = mainLists.tHeader;
        let mainTitleForKey = mainLists.filterVal;
        let mainList = mainLists.list;
        let mainStr = [];
        mainStr.push(mainTitle.join("\t,") + "\n");
        for (let i = 0; i < mainList.length; i++) {
          let temp = [];
          for (let j = 0; j < mainTitleForKey.length; j++) {
            temp.push(mainList[i][mainTitleForKey[j]]);
          }
          mainStr.push(temp.join("\t,") + "\n");
        }
        let merged = mainStr.join("")
        const uri = 'data:text/csv;charset=utf-8,\ufeff' + encodeURIComponent(merged)
        let datestr = 'customaidWEB管理_csv'
        let link = document.createElement('a')
        link.href = uri
        link.download = datestr + `.csv`
        document.body.appendChild(link)
        link.click()
      },
      dateChange(e) {
        console.log(e)
      },
      toDetail(e) {
        console.log(e)
        this.$router.push("CustomerDetail?id=" + e.id)
      },
      search() {
        let that = this
        that.getList()
      },
      getList() {
        let that = this
        let data = {}
        data.page = that.page
        data.keywords = that.keyword
        data.start_time = that.value1 || ''
        data.end_time = that.value2 || ''
        data.status = that.value || 0
        console.log(data)
        getCustomerList(data).then((res) => {
          console.log("res", res)
          if (res.code == 200) {
            that.tableData = res.data.data
            that.total = res.data.total
            that.formatCsv()
          }
        })
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
  .comple{
    width: 150px;
    height: 150px;
    background: #fff;
    border-radius: 10px;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%,-50%);
  }
  .maskContent{
    width: 345px;
    background: #fff;
    border-radius: 10px;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%,-50%);
  }
  .maskConfirm{
    text-align: center;
    color: rgb(247, 105, 100);
    border-bottom: 1px solid #eaeaea;
    cursor: pointer;
    padding: 20px 0;
  }
  .maskCancel{
    text-align: center;
    cursor: pointer;
    padding: 20px 0;
  }
  .maskTitle{
    box-sizing: border-box;
    padding: 20px;
    text-align: center;
    font-weight: bold;
  }

  .mask{
    width: 100vw;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 10009 !important;
  }

  .tableContent {
    width: 100%;
    margin-top: 62px;
  }

  .export {
    width: 112px;
    height: 40px;
    background: #1EE24C;
    border-radius: 10px;
    color: #fff;
    font-size: 14px;
    text-align: center;
    line-height: 40px;
    position: absolute;
    top: 53px;
    right: 23px;
    cursor: pointer;
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
    position: relative;
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
