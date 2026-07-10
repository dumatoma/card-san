<template>
  <div style="position: relative;height: 100%;">
    <div class="not_tit u-flex u-row-between">
      お知らせ一覧
      <div class="u-flex">
        <el-tooltip class="item" effect="dark" content="設定" placement="bottom">
             <img src="@/static/index/setting.svg" class="shou" @click="goSet" />
         </el-tooltip>
        <div class="btn_blue shou u-flex u-row-center" @click="goContribution('000')">
          <i class="el-icon-plus" style="margin-right: 10px"></i>
          お知らせ投稿
        </div>
      </div>
    </div>
    <div class="not_cont">
      <div class="not_item u-flex u-row-between" v-for="(item, index) in list" :key="index" @click="goContribution(item.id)">
        <div class="u-flex">
          <div class="img">
            <div
              class="image u-flex-col u-row-center u-col-center"
              style="z-index: 3"
            >
              <img style="width: 100%;height: 100%;object-fit: cover;" :src="item.images[0]" v-if="item.images.length > 0" alt="" />
              <img src="../static/notice.svg" class="noimage" v-else alt="">
            </div>
          </div>
          <div class="u-flex-col" style="margin-left: 10px">
            <div class="one" v-text="item.title"></div>
            <div class="two" style="margin: 8px 0" v-text="item.content"></div>
            <div class="u-flex two">
              <div class="blue_box u-flex u-row-center" style="margin-bottom: 5px;" v-if="item.is_top == 1">
                固定
              </div>
              {{item.update_time}}
            </div>
          </div>
        </div>
         
         <el-tooltip class="item" effect="dark" content="削除" placement="bottom">
              <img src="@/static/index/delete.png" @click.stop="deleteitem(item.id)" style="width:35px;cursor: pointer;"  alt="">
          </el-tooltip>
      </div>
    </div>
    
    <div class="nqmask" v-if="showg">
        <div class="nqmaskContent">
            <div class="nqmaskTitle">
                ご契約のプランでは <br/>この機能をご利用できません。<br/> プランを変更しますか？
            </div>
            <div class="nqmaskDetail">
               プランを変更するとすぐに、この機能をご利用できます。
            </div>
            <div class="nqbt" @click="toBuy">
                プランを変更
            </div>
            <div class="nqbt2" @click="goback">
               キャンセル
            </div>
        </div>
    </div>
    
  </div>
</template>

<script>
import {getNoticeList,deleteNoticeList,getStore} from "@/http/api.js"
export default {
  data() {
    return {
      notShow: false,
      list:[],
      showMask:false,
      showg:false
    };
  },
  created() {
    let that = this
    getStore().then((res) => {
        console.log("store",res)
        if(res.code == 200){
            if(res.data.shop_info.notice_type == 2){
                that.showMask = true
            }
        }
    })
       getNoticeList().then((res) =>{
           console.log(res)
           if(res.code == 200){
               that.list = res.data.notices
           }else if (res.code == 403){
               that.showg = true
           }
       })
  },
  methods: {
    goSet() {
      this.$router.push({
        path: "/noticeSet",
      });
    },
    toBuy() {
        this.$router.push({
            path: "/utilizePlan",
        })
    },
    goback(){
        this.showg = false
        // // this.$router.go(-1)
        // window.history.back();
        this.$router.replace("topIndex")
    },
    getList(){
        let that =  this
        getNoticeList().then((res) =>{
            console.log(res)
            if(res.code == 200){
                that.list = res.data.notices
            }
        })
    },
    deleteitem(id){
      let that = this
      deleteNoticeList(id).then((res) => {
          if(res.code == 200){
              that.$message({
              	message: res.message,
              	type: 'success',
                offset:400
              });
              that.getList()
          }
      })
      console.log(id)
    },
    goContribution(id) {
      this.$router.replace({
        path: "/contribution?id="+id,
      });
    },
  },
};
</script>

<style lang="scss" scoped>
    .naskContent{
        width: 423px;
        height: 190px;
        background: rgba(29, 29, 31, 0.8);
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .mask{
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        // background: pink;
    }
    .minner{
        color: #fff;
        font-size: 20px;
        text-align: center;
        line-height: 1.5;
    }
    .item:hover{
        opacity: 0.8 !important;
    }
.not_tit {
  width: 820px;
  padding: 20px 0 22px 0;
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
  img {
    width: 34px;
    height: 34px;
  }
  .btn_blue {
    width: 177px;
    height: 44px;
    font-size: 16px;
    color: #ffffff;
    margin-left: 29px;
    font-weight: lighter;
    background: #007aff;
    border-radius: 24px;
  }
}
.not_cont {
  width: 820px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .not_item {
    padding: 15px 22px 16px 10px;
    border-bottom: 1px solid #d2d2d7;
    &:last-child {
      border: none;
    }
    .img {
      width: 100px;
      height: 100px;
      background: rgba(29, 29, 31, 0.1);
      border-radius: 6px;
      font-size: 12px;
      color: #aaaaaa;
      position: relative;
      border: 1px solid #eaeaea;
      flex-shrink: 0;
      .image {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        border-radius: 6px;
        overflow: hidden;
        // z-index: 1;
      }
      img {
        width: 35px;
        height: 27px;
      }
    }
    .blue_box {
      width: 48px;
      height: 24px;
      border-radius: 4px;
      background: #ffffff;
      border: 1px solid #1a73e8;
      font-size: 12px;
      color: #1a73e8;
      margin-right: 10px;
    }
    .one {
      font-size: 16px;
      color: #1d1d1f;
      font-weight: bold;
      cursor: pointer;
      text-overflow: ellipsis;
      white-space: nowrap;
      overflow: hidden;
      max-width: 620px;
    }
    .one:hover{
        color: #1A73E8 !important;
        text-decoration: underline;
    }
    .two {
      font-size: 16px;
      color: #707070;
      max-width: 620px;
       display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        text-overflow: ellipsis;
    }
  }
}
</style>
