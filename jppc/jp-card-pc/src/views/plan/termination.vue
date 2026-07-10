<template>
  <div>
    <div class="title">解約手続き</div>
    <div class="cont no-size">
      <div class="cont_tit u-flex">
        本日解約手続きを行うと、
        <div style="font-weight: bold; color: #1d1d1f">2022/10/31</div>
        を持ちまして契約を終了いたします。
      </div>
      <div class="cont_tit bottom_tit u-flex">
        ご解約しても
        <div style="font-weight: bold; color: #1d1d1f">
          契約終了日までアカウントは有効
        </div>
        です。
      </div>
      <div class="cont_center">
        <div class="cen_one">残念ですが、解約されてしまうあなたへ。</div>
        <div class="cen_two">
          契約終了日の翌日には、今まで作成したデータ、コンテンツは全て失われてしまいます。　いかなる理由があってもデータの復旧は対応致し兼ねますので、ご了承ください。
        </div>
        <div class="u-flex-center cen_three">
          <div
            class="radius shou u-flex-center"
            :class="{ radiusCss: radShow }"
            @click="radClick"
          >
            <i class="el-icon-check"></i>
          </div>
          <div class="text" :class="{ textCss: radShow }">
            上記内容を確認した上で、解約を了承しました。
          </div>
        </div>
      </div>
      <div class="cont_bottom u-flex u-row-between">
        <div class="btn shou grey" @click="back">戻　る</div>
        <div class="btn shou red" @click="goTerminationFulfil">解約する</div>
      </div>
    </div>
  </div>
</template>

<script>
    import {cancelPlan} from "@/http/api.js"
export default {
  data() {
    return {
      radShow: false,
    };
  },
  methods: {
    radClick() {
      this.radShow = !this.radShow;
    },
    back() {
      this.$router.push({
        path: "/utilizePlan",
      });
    },
    goTerminationFulfil() {
        let that = this
        if(that.radShow == true){
           cancelPlan().then((res) => {
               if(res.code == 200){
                   that.$router.push({
                     path: "/terminationFulfil",
                   });
               }else{
                   that.$message.error(res.message)
               }
           }) 
        }
    },
  },
};
</script>

<style lang="scss" scoped>
.title {
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
}
.cont {
  width: 894px;
  margin-top: 28px;
  background: #fff;
  padding: 40px 40px 69px 40px;
  box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);
  .cont_tit {
    font-size: 16px;
    color: #707070;
    padding-right: 243px;
  }
  .bottom_tit {
    margin-top: 5px;
    padding-bottom: 20px;
    border-bottom: 1px solid #d2d2d7;
  }
  .cont_center {
    padding-top: 50px;
    text-align: center;
    .cen_one {
      font-size: 21px;
      color: #1d1d1f;
      font-weight: bold;
    }
    .cen_two {
      width: 648px;
      font-size: 16px;
      color: #d93025;
      margin: 0 auto;
      margin-top: 40px;
    }
    .cen_three {
      margin-top: 50px;
      border-bottom: 1px solid #d2d2d7;
      padding-bottom: 21px;
      .radius {
        width: 16px;
        height: 16px;
        border-radius: 2px;
        border: 1px solid #d2d2d7;
        margin-right: 10px;
        background: #fff;
        .el-icon-check {
          color: #fff;
          font-size: 10px;
        }
      }
      .radiusCss {
        background: #1a73e8;
        border: 1px solid #1a73e8;
      }
      .text {
        font-size: 14px;
        color: #707070;
      }
      .textCss {
        color: #1d1d1f;
      }
    }
  }
  .cont_bottom {
    width: 431px;
    margin: 0 auto;
    padding-top: 31px;
    .btn {
      width: 200px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      border-radius: 10px;
    }
    .grey {
      color: #707070;
      background: rgba(29, 29, 31, 0.05);
      border: 1px solid #707070;
    }
    .red {
      color: #fff;
      background: #d93025;
    }
  }
}
</style>
