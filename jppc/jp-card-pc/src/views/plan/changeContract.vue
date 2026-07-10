<template>
  <div>
    <div class="title">ご契約プランの変更</div>
    <div class="cont no-size">
      <div class="cont_tit">以下の内容にプランを変更しました。</div>
      <div class="u-flex m-t-40">
        <div class="cont_left">ご利用プラン</div>
        <div class="cont_right">スタンダード</div>
      </div>
      <div class="u-flex cont_bottom">
        <div class="cont_left">オプション</div>
        <div class="cont_right">予約機能、クーポン発行</div>
      </div>
      <div class="btn shou u-flex-center">O K</div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {};
  },
  methods: {},
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
  height: 395px;
  margin-top: 28px;
  background: #fff;
  padding: 40px 40px 69px 40px;
  box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);
  .cont_tit {
    font-size: 21px;
    color: #1d1d1f;
    font-weight: bold;
    padding-bottom: 20px;
    border-bottom: 1px solid #d2d2d7;
  }
  .cont_bottom {
    margin-top: 23px;
    padding-bottom: 40px;
    border-bottom: 1px solid #d2d2d7;
  }
  .cont_left {
    width: 150px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    margin-right: 30px;
    color: #1d1d1f;
    background: rgba(29, 29, 31, 0.1);
  }
  .cont_right {
    font-size: 16px;
    color: #1d1d1f;
    font-weight: bold;
  }
  .btn {
    width: 200px;
    height: 50px;
    background: rgba(29, 29, 31, 0.05);
    border-radius: 10px;
    border: 1px solid #707070;
    font-size: 20px;
    color: #707070;
	margin: 0 auto;
	margin-top: 31px;
  }
}
</style>
