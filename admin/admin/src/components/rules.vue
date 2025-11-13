<template>
  <div class="container">
    <div class="guide" v-if="type == 1">
      利用規約 > 契約者用
    </div>
    <div class="guide" v-if="type == 2">
      利用規約 > エンドユーザー用
    </div>
    <div class="guide" v-if="type == 3">
      プライバシーポリシー > 契約者用
    </div>
    <div class="guide" v-if="type == 4">
      プライバシーポリシー > エンドユーザー用
    </div>
    <div class="rulesContent">
      <div class="title">利用規約</div>
      <div class="txtarea">
        <textarea name="" v-model="value" id="" cols="30" rows="10"></textarea>
      </div>
      <div class="btn" @click="save"> 保 存 </div>
    </div>
  </div>

</template>

<script scoped>
  import {
    getConfig,
    editPricy
  } from "@/http/api.js"
  export default {
    name: 'CustomerDetail',
    data() {
      return {
        type: 0,
        value:''
      }
    },
    created() {
      let that = this
      that.type = that.$route.query.type
      that.getConfig()
      console.log(that.type)
      // that.getNotice()
    },
    methods: {
      save(){
        let that = this
        let data = {}
        data.function = "index"
        if(that.type == 1){
          data.shop_service_regulations = that.value
        }else if(that.type == 2){
          data.service_regulations = that.value
        }else if(that.type == 3){
          data.shop_privacy_policy = that.value
        }else if(that.type == 4){
          data.privacy_policy = that.value
        }
        editPricy(data).then((res) => {
          console.log(res)
          if(res.code == 200){
            that.$message({
              message: res.message,
              type: 'success'
            });
          }
        })
      },
      getConfig(){
        let that = this
        getConfig().then((res) => {
          console.log(res)
          if(res.code == 200){
            if(that.type == 1){
              that.value = res.data.config.shop_service_regulations
            }else if(that.type == 2){
               that.value = res.data.config.service_regulations
            }else if(that.type == 3){
               that.value = res.data.config.shop_privacy_policy
            }else if(that.type == 4){
               that.value = res.data.config.privacy_policy
            }
          }
        })
      }
    }
  }
</script>

<style scoped>
  .btn {
    width: 240px;
    height: 60px;
    background: #007aff;
    border-radius: 29px;
    text-align: center;
    line-height: 60px;
    color: #fff;
    font-size: 24px;
    margin: 40px auto 0;
    cursor: pointer;
  }

  .txtarea textarea {
    width: 100%;
    height: 100%;
    border: none;
    resize: none;
    outline: none;
    font-size: 19px;
  }

  .txtarea {
    width: 100%;
    height: 528px;
    border-radius: 12px 12px 12px 12px;
    opacity: 1;
    border: 1px solid #aaa;
    box-sizing: border-box;
    padding: 17px;
  }

  .rulesContent {
    width: 996px;
    height: 720px;
    background: #fff;
    margin-top: 24px;
    box-sizing: border-box;
    padding: 34px;
  }

  .title {
    font-size: 19px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 400;
    color: #86868b;
    margin-bottom: 12px;
  }

  .dicontent {
    width: 100%;
    height: 100%;
    background: #fff;
    box-sizing: border-box;
    padding: 30px;
    border-bottom-left-radius: 10px;
    border-bottom-right-radius: 10px;
  }

  .btn {
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
