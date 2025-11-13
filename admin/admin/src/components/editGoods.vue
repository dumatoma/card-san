<template>
  <div class="container">
    <div class="guide">
      販促アイテム > アイテム追加
    </div>
    <div class="infoContent">
      <div class="title">
        <div class="ttitle"> アイテム名 </div>
        <div class="inp"><input type="text" v-model="goods_name"></div>
      </div>
      <div class="ttitle" style="margin-bottom: 52px;"> 添付画像(最大6枚) </div>
      <div class="imageList">
        <div class="imageitem" v-for="(item,index) in imageList">
          <img :src="item" style="object-fit: contain;width: 100%;height: 100%;" alt="" />
          <img src="../assets/blackClose.png" class="closeImage" alt="" @click="delImage(index)" />
        </div>
      </div>
      <div class="drg">
        <el-upload class="upload-demo" :on-success="uploadsuccess" :limit="6" drag
          action="https://api.card-san.jp/tool/upload/file" multiple>
          <img style="width: 30px;height: 24px;" src="../assets/upload.png" alt="" />
          <div class="el-upload__text" style="font-size: 16px;margin-top: 10px;color: #2699fb;">ここにファイルをドラッグ</em></div>
        </el-upload>
      </div>
      <div class="add">
        <el-upload class="upload-demo" :on-success="uploadsuccess" action="https://api.card-san.jp/tool/upload/file"
          multiple :limit="6">
          <div class="zhui">
            <span class="el-icon-plus" style="font-weight: 700;"></span> ファイル追加
          </div>
        </el-upload>
      </div>
      <div class="title">
        <div class="ttitle"> アイテム説明 </div>
        <div class="ttext"><textarea placeholder="アイテム説明を入力してください。" cols="30" rows="10" v-model="des"></textarea></div>
      </div>
      <div class="pitem">
        <div class="pLeft"> 販売価格 </div>
        <div class="pinp">
          <input type="text" v-model="price">
        </div>
        <span>円/</span>
        <div class="pinp"><input type="text" v-model="units"></div>
      </div>
      <div class="pitem">
        <div class="pLeft"> 送料 </div>
        <div class="pinp">
          <input type="text" v-model="freight">
        </div>
        <span>円</span>
      </div>
      <div class="pitem">
        <div class="pLeft"> 表示 </div>
        <div id="pic">
          <el-select v-model="value" @change="dateChange">
            <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value">
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="btn" @click="editGoods"> 保 存 </div>
    </div>
  </div>

</template>

<script scoped>
  import {
    getGoodsDetail,
    editGoods
  } from "@/http/api.js"
  export default {
    name: 'CustomerDetail',
    data() {
      return {
        value: "",
        imageList: [],
        des: "",
        freight: "",
        goods_name: "",
        price: "",
        id:"",
        units: "",
        fits: ['fill', 'contain', 'cover', 'none', 'scale-down'],
        options: [{
            label: "表示中",
            value: 0
          },
          {
            label: "表示しない",
            value: 1
          }
        ],
      }
    },
    beforeCreate() {},
    mounted() {},
    created() {
      let that = this
      let id = that.$route.query.id
      console.log("id=" + id)
      that.getDetail(id)
      that.id = id
      // that.getNotice()
    },
    methods: {
      getDetail(id) {
        let that = this
        getGoodsDetail(id).then((res) => {
          console.log(res)
          if(res.code == 200){
            that.imageList = res.data.goods.carousel
            that.freight = res.data.goods.freight
            that.goods_name = res.data.goods.goods_name
            that.price = res.data.goods.price
            that.units = res.data.goods.units
            that.value = res.data.goods.status
            that.des = res.data.goods.des
          }
        })
      },
      editGoods(){
        let that = this
        let data = {}
        data.id = that.id
        data._method = "put"
        data.des = that.des
        data.freight = that.freight
        data.goods_name = that.goods_name
        data.price = that.price
        data.status = that.value
        data.units = that.units
        data.carousel = that.imageList
        editGoods(data).then((res) => {
          if(res.code == 200){
            that.$message({
              message: res.message,
              type: 'success'
            });
            setTimeout(() => {
              history.go(-1)
            },1500)
          }else{
            that.$message({
              message: res.message,
              type: 'error'
            });
          }
        })
      },
      dateChange(e) {
        console.log(e)
        // carousel: ["https://hyk.ljcyh.com/storage/uploads/20231110/e1f5004defd848d1f72fe50f2fe66ca6.png", …]
        // 0: "https://hyk.ljcyh.com/storage/uploads/20231110/e1f5004defd848d1f72fe50f2fe66ca6.png"
        // 1: "https://hyk.ljcyh.com/storage/uploads/20231110/e25266f6c13d3d4aacb50d75dc72afc5.png"
        // 2: "https://hyk.ljcyh.com/storage/uploads/20231110/a9b489deef80615bc27a5e4a92b930a5.png"
        // 3: "https://hyk.ljcyh.com/storage/uploads/20231110/7088d5df3eff289b85df06d8848b4f4f.png"
        // des: "tips"
        // freight: "12"
        // goods_name: "测2"
        // price: "60"
        // status: "1"
        // units: "个"
      },
      save() {
        let that = this
        let data = {}
        data.des = that.des
        data.freight = that.freight
        data.goods_name = that.goods_name
        data.price = that.price
        data.status = that.value
        data.units = that.units
        data.carousel = that.imageList
        addGoods(data).then((res) => {
          console.log(res)
          if (res.code == 200) {
            that.$message({
              message: res.message,
              type: 'success'
            });
            setTimeout(() => {
              history.go(-1)
            }, 1500)
          }
        })
      },
      delImage(index) {
        let that = this
        that.imageList.splice(index, 1)
      },
      uploadsuccess(e) {
        console.log(e)
        let that = this
        if (e.code == 200) {
          that.imageList.push(e.data.path)
        }
      },
      addGoods() {
        let that = this
        that.$router.push("/NewGoods")
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
  .closeImage {
    width: 36px !important;
    height: 36px !important;
    position: absolute;
    top: 7px;
    right: 7px;
    cursor: pointer;
  }

  input {
    outline: none !important;
    border: none !important;
    width: 100%;
    height: 100%;
  }

  .imageList {
    display: flex;
    flex-wrap: wrap;
  }

  .imageitem {
    width: 290px;
    height: 193px;
    margin-bottom: 5px;
    position: relative;
    overflow: hidden;
  }

  .tuo {
    height: 120px;
    width: 886px !important;
    background: #f7faff;
    border-radius: 10px 10px 10px 10px;
    opacity: 1;
    border: 1px solid #2699fb;
  }

  .zhui {
    width: 140px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    margin-top: 20px;
    border: 1px solid #2699fb;
  }

  .drg {
    width: 100%;
    height: 120px;
    overflow: hidden !important;
  }

  .el-upload-dragger {
    display: flex;
    justify-content: flex-start;
    align-items: flex-start;
    width: 100% !important;
    height: auto;
  }


  .upload-demo {
    width: 100%;
    height: 40px;
  }

  .ttitle {
    font-size: 19px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 700;
    color: #707070;
  }

  .pitem span {
    font-size: 19px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 700;
    color: #707070;
  }

  .btn {
    width: 240px;
    height: 60px;
    background: #007aff;
    border-radius: 29px;
    text-align: center;
    line-height: 60px;
    color: #fff;
    font-size: 24px;
    margin: 84px auto 0;
    cursor: pointer;
  }

  .pinp {
    width: 125px;
    height: 48px;
    background: #fff;
    border-radius: 5px;
    border: 1px solid #aaa;
    box-sizing: border-box;
    padding: 5px;
    margin: 0 12px;
  }

  .pLeft {
    font-size: 19px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 700;
    color: #707070;
    width: 80px;
    text-align: center;
  }

  .pitem {
    display: flex;
    align-items: center;
    margin-top: 48px;
  }

  .ttext {
    height: 240px;
    background: #fff;
    border-radius: 12px;
    opacity: 1;
    border: 1px solid #aaa;
    box-sizing: border-box;
    padding: 7px;
    margin-top: 12px;
  }

  .add {
    margin-bottom: 70px;
    width: 140px;
    height: 53px;
    font-size: 14px;
    color: #2699fb;
    background: #fff;
  }

  .inp {
    width: 100%;
    border-bottom: 1px solid #aaa;
    margin-bottom: 72px;
    padding: 8px 0;
    margin-top: 12px;
  }

  .guide {
    width: 100%;
    font-size: 25px;
    font-family: Hiragino Sans-W7, Hiragino Sans;
    font-weight: bolder;
    color: #174a84;
  }

  .pinp input {
    font-size: 19px;
    width: 100%;
    height: 100%;
    border: none;
    outline: none;
  }

  .ttext textarea {
    width: 100%;
    height: 100%;
    border: none;
    outline: none;
    resize: none;
    font-size: 19px;
    font-family: Hiragino Sans-W3, Hiragino Sans;
    font-weight: 400;
  }

  .inp input {
    display: inline-block;
    width: 100%;
    height: 100%;
    border: none !important;
  }

  .infoContent {
    width: 996px;
    background-color: #fff;
    box-sizing: border-box;
    padding: 48px;
    margin-top: 24px;
    height: 700px;
    overflow-y: auto;
  }
</style>
