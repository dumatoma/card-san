<template>
  <div>
    <div class="not_tit u-flex u-row-between">ご注文内容確認</div>
    <div class="not_cont no-size">
      <div class="tit_one">
        ありがとうございます<br />
        ご注文手続きが完了しました
      </div>
      <div class="tit_two">
        ご注文の販促品は7日営業日以内のお届けを予定しております。
      </div>
      <div class="u-flex u-col-top content">
        <div class="con_left m-r-20">注文内容</div>
        <div>
          <div class="u-flex">
            <div class="left">受注番号</div>
            <div class="right">2207051-店舗契約番号</div>
          </div>
          <div class="u-flex m-t-10">
            <div class="left">商品名</div>
            <div class="right">三角POPスタンド</div>
          </div>
          <div class="u-flex m-t-10">
            <div class="left">小計</div>
            <div class="right">440円(税込)</div>
          </div>
          <div class="u-flex m-t-10">
            <div class="left">数量</div>
            <div class="right">2</div>
          </div>
          <div class="u-flex m-t-10">
            <div class="left">合計</div>
            <div class="right">880円(税込)</div>
          </div>
        </div>
      </div>
      <div class="u-flex u-col-top content">
        <div class="con_left m-r-20">配送先</div>
        <div>
          <div class="right m-t-10">〒110-1987</div>
          <div class="right m-t-10">東京都墨田区両国100-999</div>
          <div class="right m-t-10">アクティブ墨田ビル西棟2階ー2012号</div>
          <div class="right m-t-10">03-1243-5678</div>
          <div class="right m-t-10" style="font-weight: bold">
            TOP HAIR SPA&RESORT
          </div>
        </div>
      </div>
      <div
        style="text-align: center;color:color: #1D1D1F;">
        <div class="fot_one">example@mail.jp(ID)</div>
        <div class="fot_two">
          宛にご注文内容を送信しました。<br />
          ご確認ください。
        </div>
        <div class="fot_three u-flex-center shou" @click="goIndex">TOPに戻る</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {};
  },
  methods: {
      goIndex(){
           this.$router.push({
        path: "/topIndex",
      });
      }
  },
};
</script>

<style lang="scss" scoped>
.not_tit {
  width: 820px;
  padding: 0 0 22px 0;
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
}
.not_cont {
  width: 820px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  padding: 30px 100px 50px 100px;
  .tit_one {
    font-size: 20px;
    color: #1d1d1f;
    font-weight: bold;
    text-align: center;
    line-height: 30px;
  }
  .tit_two {
    font-size: 16px;
    color: #1d1d1f;
    text-align: center;
    margin-top: 30px;
    padding-bottom: 38px;
    border-bottom: 1px solid #d2d2d7;
  }
  .content {
    font-size: 12px;
    padding: 20px 0;
    color: #707070;
    border-bottom: 1px solid #d2d2d7;
    .con_left {
      width: 68px;
    }
    .left {
      width: 68px;
    }
    .right {
      width: 202px;
      color: #1d1d1f;
    }
  }
  .fot_one {
    font-size: 20px;
    font-weight: bold;
    margin-top: 40px;
  }
  .fot_two {
    font-size: 16px;
    margin-top: 30px;
    line-height: 27px;
  }
  .fot_three {
    width: 116px;
    height: 48px;
    font-size: 16px;
    margin:0 auto;
    margin-top:50px;
    font-weight: bold;
    border-radius: 10px;
    background: #d2d2d7;
  }
}
</style>
