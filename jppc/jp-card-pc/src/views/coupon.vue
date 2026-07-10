<template>
  <div>
    <div class="not_tit u-flex u-row-between">
      クーポン一覧
      <div class="btn_pink shou u-flex u-row-center" @click="goFoundCoupon(0,'000')">
        <i class="el-icon-plus" style="margin-right: 10px"></i>
        クーポン作成
      </div>
    </div>
    <div class="not_cont">
      <div class="not_item u-flex u-row-between" @click="goFoundCoupon(item.is_send,item.id)" v-for="(item, index) in list" :key="index">
        <div class="u-flex">
          <div class="img">
            <div
              class="image u-flex-col u-row-center u-col-center"
              style="z-index: 3"
            >
              <img v-if="item.images[0]" :src="item.images[0]" alt="" style="width: 100%;height: 100%;object-fit: cover;" />
              <img v-else src="../static/notice.svg" alt="" />
            </div>
            <!-- <img v-if="notShow" src="@/static/index/dlogo.png" class="image" /> -->
          </div>
          <div class="u-flex-col" style="margin-left: 14px">
            <div class="one" v-text="item.title"></div>
            <div class="two u-flex" style="margin: 8px 0">
              <div class="two_letter">NO.</div>
              <div class="two_num" v-text="item.number"></div>
              <div v-if="item.is_indate == 1">
                  {{item.indate_start_time}} ~ {{item.indate_end_time}}有効
              </div>
              <div v-else>
                  無期限のクーポンです
              </div>
            </div>
            <div class="u-flex two" style="font-size: 14px">
              <div class="m-r-20">配信数: {{item.send_count}}</div>
              <div class="m-r-20">バーコード:{{item.is_bar_code == 1? 'あり' : 'なし'}}</div>
              <div class="m-r-20">利用者数: {{item.use_count}}</div>
              <div class="m-r-20">回数制限: {{item.count}}</div>
            </div>
          </div>
        </div>
        <div class="u-flex">
          <div class="bor back_blue u-flex-center" v-if="item.is_send == 1 && item.indate == 1">有効</div>
          <div class="bor back_grey u-flex-center" v-if="item.is_send == 1 && item.indate == 0">期限切</div>
          <div class="bor grey u-flex-center" v-if="item.is_send == 1">配信済</div>
          <div class="bor blue u-flex-center" v-if="item.is_send == 0">予約中</div>
          <el-tooltip class="item" effect="dark" content="削除" placement="bottom">
               <img src="@/static/index/delete.png"  style="width:35px;"  class="shou" @click.stop="deleteCoupon(item.id)">
           </el-tooltip>
        </div>
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
import {getCouponList,deleteNewCoupon} from "@/http/api.js"
export default {
  data() {
    return {
      notShow: false,
      list:[],
      showg:false
    };
  },
  created() {
     let that = this
     that.getList()
  },
  methods: {
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
    goFoundCoupon(sta,id) {
        if(sta == 0){
            this.$router.push({
              path: "/foundCoupon?id="+id,
            });
        }
    },
    deleteCoupon(id){
      let that = this
      deleteNewCoupon(id).then((res) => {
          if(res.code == 200){
              that.getList()
             that.$message({
                 message: res.message,
                 type: 'success'
             })
          }else{
              that.$message.error(res.message);
          }
      })
      console.log(id)
    },
    getList(){
      let that = this
      getCouponList().then((res) => {
          console.log(res)
          if(res.code == 200){
              that.list = res.data.coupons
          }else if (res.code == 403){
              that.showg = true
          }
      })
    },
  },
};
</script>

<style lang="scss" scoped>
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
  .btn_pink {
    width: 177px;
    height: 44px;
    font-size: 16px;
    color: #ffffff;
    margin-left: 29px;
    font-weight: lighter;
    background: rgba(217, 48, 37, 1);
    border-radius: 24px;
  }
  
  .btn_pink:hover{
      background: rgba(217, 48, 37, 0.6);
  }
}
.not_cont {
  width: 820px;
  height: 55vh;
  overflow-y: auto;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .not_item {
    padding: 15px 22px 16px 10px;
    border-bottom: 1px solid #d2d2d7;
    &:last-child {
      border: none;
    }
    .img {
      width: 118px;
      height: 88px;
      background: rgba(29, 29, 31, 0.1);
      border-radius: 6px;
      font-size: 12px;
      color: #aaaaaa;
      position: relative;
      border: 1px solid #eaeaea;
      .image {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
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
    }
    .two {
      font-size: 16px;
      color: #707070;
      .two_num {
        padding-top: 3px;
        color: #1a73e8;
        margin-right: 20px;
      }
      .two_letter {
        color: #1d1d1f;
        font-weight: bold;
        padding-top: 3px;
      }
    }
  }
  .bor {
    width: 48px;
    height: 24px;
    border-radius: 6px;
    font-size: 12px;
    margin-right: 30px;
  }
  .back_blue{
      color:#fff;
      margin-right:10px;
      background: #1A73E8;
  }
  .blue {
    color: #1a73e8;
    border: 1px solid #1a73e8;
  }
  .grey {
    color: #aaa;
    border: 1px solid #aaaaaa;
  }
  .back_grey{
      color:#fff;
      margin-right:10px;

      background: #707070;
  }
  .greyTwo{
      color: #707070;
      
      border: 1px solid #707070;
  }
}
</style>
