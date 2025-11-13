<template>
  <div class="container">
    <div class="guide">
      お知らせ投稿 > ご契約者様へのお知らせ
    </div>
    <div class="addNew pointer" @click="showNew=true">
      <span style="font-size: 30px; position: relative; top: -2px; margin-right: 5px;">+</span>
      <span>お知らせ投稿</span>
    </div>
    <div class="tableContent">
      <div class="tableItem" v-for="item,index in list">
        <div class="itemName" v-text="item.title" @click="showDetail(item)"></div>
        <div class="itemDate" v-text="item.create_time"></div>
        <div class="itemDelete" @click="del(item)"><img src="../assets/del.png" alt=""></div>
      </div>
    </div>
    <el-pagination background layout="prev, pager, next" :total="list.length">
    </el-pagination>
    <!-- 编辑遮罩 -->
    <div class="mask" v-if="showEdit == true">
      <div class="dialog">
        <div class="dititle">
          <span>編集</span>
          <span class="close" @click="showEdit = false">×</span>
        </div>
        <div class="dicontent">
          <div class="ctitle">
            <span style="color: rgb(217, 48, 37);">※</span>お知らせタイトル
          </div>
          <div class="inp"><input v-model="info.title" type="text"></div>
          <div class="ctitle" style="margin-top: 40px;"> お知らせ内容 </div>
          <div class="tx">
            <textarea cols="30" v-model="info.content" rows="30" placeholder="お知らせ内容を入力"></textarea>
          </div>
          <div class="btn">
            <button type="button" @click="editNotice" class="el-button el-button--primary is-round">
              <span>内容を変更</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- 新建遮罩 -->
    <div class="mask" v-if="showNew == true">
      <div class="dialog">
        <div class="dititle">
          <span>新しいお知らせ</span>
          <span class="close" @click="showNew = false">×</span>
        </div>
        <div class="dicontent">
          <div class="ctitle">
            <span style="color: rgb(217, 48, 37);">※</span>お知らせタイトル
          </div>
          <div class="inp"><input type="text" v-model="newInfo.title"></div>
          <div class="ctitle" style="margin-top: 40px;"> お知らせ内容 </div>
          <div class="tx">
            <textarea cols="30" rows="30" v-model="newInfo.content" placeholder="お知らせ内容を入力"></textarea>
          </div>
          <div class="btn">
            <button type="button" class="el-button el-button--primary is-round" @click="addNewNotice">
              <span>この内容で投稿</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script scoped>
  import {
    shopNotice,
    addShopNotice,
    delShopNotice,
    editShopNotice
  } from "@/http/api.js"
  export default {
    name: 'CustomerDetail',
    data() {
      return {
        list: [],
        page: 1,
        showNew:false,
        showEdit:false,
        info:{
          id:"",
          title:"",
          content:""
        },
        newInfo:{
          title:"",
          content:""
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
      editNotice(){
        let that = this
        let data = {}
        data.content = that.info.content
        data.id = that.info.id
        data.title = that.info.title
        data.type = 1
        data._method = "put"
        editShopNotice(data).then((res) => {
          if(res.code == 200){
            that.getNotice()
            that.$message({
              message: res.message,
              type: 'success'
            });
            that.showEdit = false
          }
        })
      },
      del(e){
        let that = this
        delShopNotice(e.id).then((res) => {
          if(res.code == 200){
            that.getNotice()
            that.$message({
              message: res.message,
              type: 'success'
            });
          }
        })
      },
      addNewNotice(){
        let that = this
        let data = {}
        data.content = that.newInfo.content
        data.title = that.newInfo.title
        data.type = 1
        addShopNotice(data).then((res) => {
          if(res.code == 200){
            that.getNotice()
            that.$message({
              message: res.message,
              type: 'success'
            });
            that.showNew = false
          }
        })
      },
      getNotice(e) {
        let that = this
        let page = that.page
        shopNotice(page).then((res) => {
          console.log("res", res)
          if (res.code == 200) {
            that.list = res.data.data
          }

        })
      },
      showDetail(e){
        let that = this
        that.info.title = e.title
        that.info.content = e.content
        that.info.id = e.id
        that.showEdit = true
      }
    }
  }
</script>

<style scoped>
  .dicontent {
    width: 100%;
    height: 100%;
    background: #fff;
    box-sizing: border-box;
    padding: 30px;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
  }

  .btn{
      margin-top: 40px;
  }

  .tx textarea {
    width: 100%;
    height: 100%;
    resize: none;
    border: none;
    outline: none;
    font-size: 16px;
  }

  .tx {
    width: 100%;
    height: 300px;
    border: 1px solid #aaa;
    border-radius: 10px;
    box-sizing: border-box;
    padding: 10px;
    margin-top: 12px;
  }

  .inp input {
    width: 100%;
    height: 25px;
    border: none;
    outline: none;
    font-size: 16px;
  }

  .inp {
    width: 100%;
    border-bottom: 1px solid #aaa;
    padding: 25px 0 10px 0;
  }

  .ctitle {
    font-size: 18px;
    font-family: Hiragino Sans-W6, Hiragino Sans;
    font-weight: 400;
    color: #707070;
  }

  .dialog {
    width: 686px;
    height: 620px;
    box-shadow: 0 0 25px 2px rgba(0, 0, 0, .2);
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }

  .close {
    color: #aaa;
    font-size: 40px;
    cursor: pointer;
  }

  .dititle {
    width: 100%;
    height: 60px;
    background: #1d1d1f;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    font-size: 20px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 400;
    color: #fff;
    display: flex;
    align-items: center;
    box-sizing: border-box;
    padding: 0 30px;
    justify-content: space-between;
  }

  .mask {
    width: 100vw;
    height: 100vh;
    z-index: 10005 !important;
    background: rgba(0, 0, 0, .4);
    position: fixed;
    top: 0;
    left: 0;
  }

  .itemDelete img {
    width: 100%;
    height: 100%;
    cursor: pointer;
  }

  .itemDelete {
    width: 40px;
    height: 40px;
  }

  .itemDate {
    margin-right: 209px;
  }

  .itemName {
    width: 426px;
    margin-right: 143px;
    cursor: pointer;
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
