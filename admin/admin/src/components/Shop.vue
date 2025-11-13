<template>
  <div class="container">
    <div class="guide">
      お知らせ投稿 > エンドユーザーへのお知らせ
    </div>
    <div class="addNew pointer" @click="addGoods">
      <span style="font-size: 30px; position: relative; top: -2px; margin-right: 5px;">+</span>
      <span>アイテム追加</span>
    </div>
    <div class="goodsContent">
      <div class="item" v-for="item,index in list">
        <div class="item_left">
          <img :src="item.image" alt="">
        </div>
        <div class="item_middle">
          <div class="goodsname" v-text="item.goods_name" @click.stop="toDetail(item)"></div>
          <div class="goodsPrice" v-text="item.price"></div>
        </div>
        <div class="item_right"><img src="../assets/del.png" alt=""  @click.stop="delet(item)"></div>
      </div>
    </div>
    <el-pagination background layout="prev, pager, next" :total="list.length">
    </el-pagination>
  </div>

</template>

<script scoped>
  import {
    getGoodsList,
    deleteGoods
  } from "@/http/api.js"
  export default {
    name: 'CustomerDetail',
    data() {
      return {
        list: [],
        page: 1,
        showNew: false,
        showEdit: false,
        info: {
          id: "",
          title: "",
          content: ""
        },
        newInfo: {
          title: "",
          content: ""
        }
      }
    },
    beforeCreate() {},
    mounted() {},
    created() {
      let that = this
      let id = that.$route.query.id
      that.getNotice()
    },
    methods: {
      toDetail(e){
        console.log(e)
         this.$router.push("/Edit?id="+e.id)
      },
      addGoods() {
        let that = this
        that.$router.push("/NewGoods")
      },
      delet(e) {
        let that = this
        let data = {}
        data._method = "delete"
        data.id = e.id
        deleteGoods(data).then((res) => {
          if (res.code == 200) {
            that.$message({
              message: res.message,
              type: 'success'
            });
          }else{
            that.$message({
              message: res.message,
              type: 'error'
            });
          }
        })
      },
      getNotice(e) {
        let that = this
        let page = that.page
        getGoodsList(page).then((res) => {
          console.log("res", res)
          if (res.code == 200) {
            that.list = res.data.data
          }
        })
      },
    }
  }
</script>

<style scoped>
  .goodsContent {
    height: 600px;
    overflow-y: auto;
  }

  .item_left {
    width: 120px;
    height: 120px;
  }

  .item_right img {
    width: 100%;
    height: 100%;
  }

  .item_left img {
    width: 100%;
    height: 100%;
  }

  .item_right {
    width: 40px;
    height: 40px;
  }

  .goodsPrice {
    font-size: 24px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 400;
    color: rgba(217, 48, 37, .65);
  }

  .goodsname {
    margin-bottom: 24px;
    font-size: 24px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 400;
    color: #1d1d1f;
  }

  .goodsname:hover {
    color: #007aff !important;
    text-decoration: underline;
    cursor: pointer;
  }

  .item_middle {
    flex: 1;
    box-sizing: border-box;
    padding: 0 36px;
  }

  .item {
    box-sizing: border-box;
    padding: 12px;
    background: #fff;
    display: flex;
    align-items: center;
  }

  .btn {
    margin-top: 40px;
  }

  .ctitle {
    font-size: 18px;
    font-family: Hiragino Sans-W6, Hiragino Sans;
    font-weight: 400;
    color: #707070;
  }

  .itemName:hover {
    color: #007aff !important;
    text-decoration: underline;
  }

  .tableItem {
    display: flex;
    align-items: center;
    font-size: 16px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 400;
    color: #1d1d1f;
    box-sizing: border-box;
    padding: 17px;
    border-bottom: 1px solid #a5c9ff;
  }

  .tableContent {
    width: 996px;
    background: #fff;
    opacity: 1;
    height: 600px;
    overflow-y: scroll;
    margin-bottom: 12px;
  }

  .addNew {
    width: 212px;
    height: 58px;
    background: #007aff;
    border-radius: 29px 29px 29px 29px;
    opacity: 1;
    line-height: 58px;
    text-align: center;
    color: #fff;
    font-size: 19px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 400;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 24px 0;
  }

  .guide {
    width: 100%;
    font-size: 25px;
    font-family: Hiragino Sans-W7, Hiragino Sans;
    font-weight: bolder;
    color: #174a84;
  }
</style>
