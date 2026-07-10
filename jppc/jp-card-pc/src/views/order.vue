<template>
  <div>
    <div class="not_tit u-flex u-row-between">ご注文手続き</div>
    <div class="not_cont no-size">
      <div class="tit_grey">ご注文内容</div>
      <div class="u-flex first m-t-20">
        <div class="img">
          <div
            class="image text u-flex-col u-row-center u-col-center"
            style="z-index: 3"
          >
            <img :src="goodsInfo.image" alt="" style="width: 100%;height: 100%;" />
            <!-- 100px/100px -->
          </div>
          <!-- <img v-if="notShow" src="@/static/index/dlogo.png" class="image" /> -->
        </div>
        <div class="u-flex-col" style="margin-left: 14px">
          <div class="one" v-text="goodsInfo.goods_name"></div>
          <div class="one two u-flex m-t-10">
            <div class="m-r-20">{{goodsInfo.price}}円/{{goodsInfo.units}}</div>
            x{{number}}
          </div>
          <div class="one u-flex m-t-10">
            <div class="two m-r-20">合計金額:</div>
            {{number * goodsInfo.price}}円
            <div class="three">(税込)</div>
          </div>
        </div>
      </div>
      <div class="tit_grey m-t-20">お支払い方法</div>
      <div class="m-t-20 method">
        <div class="u-flex">
          <img
            src="@/static/index/cardLogo.png"
            style="width: 40px; margin-right: 10px"
          />
          <div>American Express</div>
        </div>
        下４桁 1101<br />TARO YAMADA<br />有効期限　05/2025
      </div>
      <div class="tit_grey m-t-20">配送先住所</div>
      <div class="address u-col-top m-t-20">
          <div class="u-flex" v-for="item,index in list" style="align-items: flex-start;margin-bottom: 8px;">
              <div
                class="radius shou"
                :class="{ radiusCss: item.checked }"
                @click="showClick(index)"
              >
                <div class="blue_dot" v-if="item.checked"></div>
              </div>
              <div class="u-flex-col">
                <div style="font-size: 12px; line-height: 20px">
                  店舗の住所<br />〒{{item.zip_code}}<br />
                  {{item.province}}<br />
                  {{item.address1}}<br />{{item.phone}}<br />
                  <div style="font-weight: bold">{{item.name}}</div>
                </div>
                
              </div>
          </div>
        <div class="shou" @click="goAddress" style="font-size: 14px; color: #1a73e8; margin-top: 30px">
          別の住所に送る
        </div>
      </div>
      
      <div style="padding: 30px 0 50px 0">
        <div class="btn u-flex-center shou" @click="orderContent">
          ご注文確定
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {getAddressList,setOrder} from "@/http/api.js"
export default {
  data() {
    return {
      show: true,
      notShow:false,
      goodsInfo:{},
      number:0,
      list:[]
    };
  },
  created() {
    let that = this
    let query = that.$route.query
    let goodsInfo = JSON.parse(query.info)
    that.goodsInfo = goodsInfo
    that.number = query.number
    that.getAddressList()
    console.log(goodsInfo)
  },
  methods: {
      getAddressList(){
          let that = this
          getAddressList().then((res) => {
              console.log(res)
              if(res.code == 200){
                  res.data.addrs.forEach((value,index) => {
                     if(index == 0){
                         value.checked = true
                     }else{
                         value.checked = false
                     }
                  })
                  that.list = res.data.addrs
              }
          })
      },
    showClick(e) {
        let that = this
        that.list.forEach((value,index) => {
            if(e == index){
                value.checked = true
            }else{
                value.checked = false
            }
        })
      // this.show = !this.show;
    },
    orderContent() {
      let that = this
      console.log(that.goodsInfo.number)
      let data = {}
      data['gid'] = that.goodsInfo.id
      data['num'] = that.number
      that.list.forEach((value,index) => {
          if(value.checked == true){
              data['aid'] = value.id
          }
      })
      setOrder(data).then((res) => {
          console.log(res)
          if(res.code == 200){
              window.open(res.data.url)
          }else{
              that.$message.error(res.message)
          }
      })
      // this.$router.push({
      //   path: "/orderContent",
      // });
    },
    goAddress() {
      this.$router.push({
        path: "/address",
      });
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
  width: 820px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  padding: 30px 40px 0 40px;
  .tit_grey {
    font-size: 16px;
    color: #707070;
    font-weight: bold;
  }
  .first {
    padding-bottom: 10px;
    border-bottom: 1px solid #d2d2d7;
    .img {
      width: 100px;
      height: 100px;
      background: rgba(29, 29, 31, 0.1);
      border-radius: 6px;
      font-size: 12px;
      color: #7f00a5;
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
      }

      img {
        width: 35px;
        height: 27px;
      }
    }
    .one {
      font-size: 16px;
      color: #1d1d1f;
      font-weight: 600;
    }
    .two {
      font-weight: lighter;
    }
    .three {
      font-size: 12px;
      font-weight: lighter;
      margin-left: 10px;
    }
  }
  .method {
    font-size: 14px;
    color: #1d1d1f;
    line-height: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #d2d2d7;
  }
  .radius {
    width: 16px;
    height: 16px;
    margin-right: 14px;
    margin-left: 22px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #707070;

    .blue_dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin: 2px auto;
      background: #1a73e8;
    }
  }
  .radiusCss{
    border: 1px solid #1a73e8;
  }
  .address {
    padding-bottom: 20px;
    border-bottom: 1px solid #d2d2d7;
  }
  .btn {
    width: 290px;
    height: 50px;
    margin: 0 auto;
    color: #fff;
    background: #ed8e20;
    border-radius: 25px;
  }
}
</style>
