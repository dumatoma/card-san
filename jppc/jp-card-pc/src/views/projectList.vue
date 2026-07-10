<template>
  <div>
    <div class="not_tit u-flex u-row-between">販促アイテム一覧</div>
    <div class="not_cont">
      <div class="not_item u-flex u-row-between" v-for="(item, index) in list" :key="index">
        <div class="u-flex">
          <div class="img">
            <div
              class="image text u-flex-col u-row-center u-col-center"
              style="z-index: 3"
            >
              <!-- <img src="@/static/index/image.png" alt="" /> -->
              <!-- 100px/100px -->
              <img :src="item.carousel[0]" style="width:100%;height:100%;">
            </div>
          </div>
          <div class="u-flex-col" style="margin-left: 14px">
            <div class="one" v-text="item.goods_name"></div>
            <div class="one m-t-20">{{item.price}}円</div>
          </div>
        </div>
        <div class="orange shou u-flex-center" @click="detail(item.id)">詳 細</div>
      </div>
    </div>
  </div>
</template>

<script>
import {getGoodsList} from "@/http/api.js"
export default {
  data() {
    return {
      notShow: false,
	  list:[]
    };
  },
  created() {
  	let that = this
	that.getList()
  },
  methods: {
    detail(e) {
      this.$router.push({
        path: "/projectDetail?id="+e,
      });
    },
	getList(){
		let that = this
		getGoodsList().then((res) => {
			console.log("res",res)
			if(res.code == 200){
				that.list = res.data.goods
			}
		})
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
  .not_item {
    padding: 15px 22px 16px 10px;
    border-bottom: 1px solid #d2d2d7;
    &:last-child {
      border: none;
    }
    .img {
      width: 100px;
      height: 100px;
      // background: rgba(29, 29, 31, 0.1);
      border-radius: 6px;
      font-size: 12px;
      color: #7f00a5;
      position: relative;
      overflow: hidden;
      .image {
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
      }

      img {
        width: 35px;
        height: 27px;
      }
    }
    .one {
      font-size: 20px;
      color: #1d1d1f;
      font-weight: 600;
    }
    .orange {
      width: 100px;
      height: 40px;
      color: #fff;
      font-size: 14px;
      background: #ed8e20;
      border-radius: 22px;
    }
  }
}
</style>
