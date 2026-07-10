<template>
  <div class="home">
    <div class="title">
      <div class="left">ダッシュボード</div>
      <div class="right">販促アイテムはこちら</div>
    </div>
    <div class="flex-box">
      <div class="left">
        <div class="day">今日</div>
        <div class="sum">{{ todayNum }}</div>
      </div>
      <div class="midd">
        <div class="day">今月</div>
        <div class="sum">
          {{ monthNum }}
        </div>
      </div>
      <div class="right">
        <div class="day">メンバー数</div>
        <div class="sum total">
          {{ total }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// @ is an alias to /src

export default {
  name: "HomeView",
  components: {},
  data() {
    return {
      todayNum: 10,
      monthNum: 20,
      total: 300,
    };
  },
};
</script>
<style lang="scss">
.home {
  width: 672px;
  .title {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    .left {
      color: #1d1d1f;
      font-size: 21px;
      // height: 44px;
      font-weight: 700;
      display: table-cell;
      vertical-align: bottom;
    }
    .right {
      color: #ffffff;
      font-size: 16px;
      height: 44px;
      line-height: 44px;
      text-align: center;
      background: #53b1af;
      border-radius: 25px;
      padding: 0 19px;
    }
  }
  .flex-box {
    margin-top: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    .left,
    .midd,
    .right {
      width: 204px;
      height: 101px;
      background: #ffffff;
      box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
      text-align: center;
      .day {
        margin-top: 20px;
        color: #707070;
        font-size: 16px;
      }
      .sum {
        margin-top: 20px;
        color: #1a73e8;
        font-size: 25px;
      }
      .total {
        color: #1d1d1f;
      }
    }
  }
}
</style>
