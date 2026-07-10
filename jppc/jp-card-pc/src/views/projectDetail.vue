<template>
  <div>
    <div class="not_tit u-flex u-row-between">販促アイテム</div>
    <div class="not_cont no-size">
      <div class="photo">
        <div class="u-flex" style="padding: 30px 143px 30px 36px">
          <div>
            <div class="small_photo" v-for="(item, index) in goodsInfo.carousel" :key="index">
              <img
                :src="item"
                style="width: 100%; height: 100%"
              />
            </div>
          </div>
          <div class="big_small">
            <img
              :src="currentImage"
              style="width: 100%; height: 100%"
            />
          </div>
        </div>
      </div>
      <div class="detail">
        <div class="left">商品名</div>
        <div class="right" v-text="goodsInfo.goods_name"></div>
      </div>
      <div class="detail">
        <div class="left">商品説明</div>
        <div class="right grey" style="white-space: pre-wrap;" v-text="goodsInfo.des"></div>
      </div>
      <div class="detail">
        <div class="left">価格</div>
        <div class="right">{{goodsInfo.price}}円(税込)</div>
      </div>
      <div class="detail">
        <div class="left">送料</div>
        <div class="right">0円(税込)</div>
      </div>
      <div class="detail">
        <div class="left">数量</div>
        <div class="right">
          <el-select v-model="number" @change="selectChanged">
            <el-option
              v-for="item in 10"
              :key="item"
              :label="item"
              :value="item"
            >
            </el-option>
          </el-select>
        </div>
      </div>
      <div class="bottom">
        <div class="btn shou u-flex-center" @click="order">次　へ</div>
      </div>
    </div>
  </div>
</template>

<script>
import {getGoodsDetail} from "@/http/api.js"
export default {
  data() {
    return {
      number: "1",
	  currentImage:"",
	  goodsInfo:{},
	  query:{}
    };
  },
  created() {
  	let query = this.$route.query
	let id = query.id
	this.query = query
	this.getDetail(id)
  },
  methods: {
    selectChanged(value) {
      this.number = value;
    },
    order() {
      let that = this
      that.$router.push({
        path: "/order?number="+that.number+"&info="+encodeURIComponent(JSON.stringify(that.goodsInfo)),
      });
    },
	getDetail(id){
		let that =this
		getGoodsDetail(id).then((res) => {
			console.log("res",res)
			if(res.code == 200){
				that.goodsInfo = res.data.goods
				that.currentImage = res.data.goods.carousel[0]
			}
		})
	},
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
  width: 878px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .photo {
    width: 94%;
    margin: 0 auto;
    border-bottom: 1px solid #d2d2d7;
  }
  .small_photo {
    width: 85px;
    height: 56px;
    margin-bottom: 8px;
    // background: rgba(143, 168, 248, 0.1);
    &:last-child {
      margin-bottom: 0;
    }
  }
  .big_small {
    width: 570px;
    height: 380px;
    margin-left: 20px;
    // background: rgba(143, 168, 248, 0.1);
  }
  .detail {
    display: flex;
    margin-left: 149px;
    margin-top: 30px;
    &:last-child {
      padding-bottom: 30px;
    }
    .left {
      width: 120px;
      height: 40px;
      display: flex;
      align-items: center;
      font-size: 16px;
      color: #1d1d1f;
      font-weight: bold;
      margin-right: 30px;
      justify-content: center;
      background: #f5f5f7;
    }
    .right {
      display: flex;
      align-items: center;
      font-size: 20px;
      color: #1d1d1f;
      font-weight: bold;
    }
    .grey {
      font-size: 16px;
      font-weight: lighter;
      color:#1D1D1F;
    }
  }
  .bottom {
    width: 92%;
    border-top: 1px solid #d2d2d7;
    margin: 0 auto;
    margin-top: 40px;
    padding: 40px 0 50px 0;
    .btn {
      width: 290px;
      height: 50px;
      font-size: 20px;
      color: #ed8e20;
      margin: 0 auto;
      border-radius: 50px;
      background: #ed8e201a;
      border: 1px solid #ed8e20;
    }
  }
}
::v-deep .el-input__inner {
  width: 108px;
  color: #1d1d1f;
  font-size: 16px;
  padding-left: 10px;
  background: #f5f5f7;
  border-radius: 10px;
  border: 1px solid #707070;
  box-shadow: 0px 0px 5px 1px rgba(0, 0, 0, 0.1);
}
::v-deep .el-icon-arrow-up:before {
  color: #707070;
  font-weight: bold;
  font-size: 16px;
}
</style>
