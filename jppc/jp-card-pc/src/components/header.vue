<template>
  <div class="header">
    <div class="top">
      <div class="bga">
        <div class="t-left">
          <img src="../static/tou-logo.svg" alt="" />
        </div>
        <div class="t-right">
          <img src="../static/user.svg" alt="" />
          ID:{{IDS}}
          <span class="shou" @click="toLogin">ログアウト</span>
        </div>
      </div>
    </div>
    <div class="mask" v-if="mask">
        <div class="maskContent">
            <div class="maskTitle">
                ログアウトしてよろしいですか？
            </div>
            <div class="blueWord" @click="toLogout">
                ログアウト
            </div>
            <div class="redWord" @click="mask = false">
                キャンセル
            </div>
        </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "Header",
  props: {
    msg: String,
  },
  data(){
      return {
          IDS:"",
          mask:false
      }
  },
  created(){
	  let admin = localStorage.getItem("admin")
	  let result = JSON.parse(admin)
      if(result.admin_type == 1){
          this.IDS = result.email
      }else{
          this.IDS = result.phone
      }
  },
  methods:{
	  toLogin(){
          this.mask = true
	  },
      toLogout(){
          location.href = this.$hosturl+"/#/login"
          // this.$router.replace({
          //     name:"login",
          //     params:{test:"test"}
          // })
      }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
    .maskTitle{
        width: 100%;
        text-align: center;
        font-size: 14px;
        color: #1d1d1f;
        font-weight: bold;
        box-sizing: border-box;
        height: 80px;
        border-bottom: 1px solid #868686;
        line-height: 80px;
    }
    .blueWord{
         text-align: center;
         height: 55px;
         line-height: 55px;
         font-size: 16px;
         color: #007AFF;
         border-bottom: 1px solid #868686;
         cursor: pointer;
    }
    .redWord{
         text-align: center;
         height: 55px;
         line-height: 55px;
         font-size: 16px;
         color: #D93025;
         cursor: pointer;
    }
    .maskContent{
        width: 270px;
        height: 192px;
        background: rgba(249,249,249,0.94);
        border-radius: 14px;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
    }
    .mask{
        width: 100vw;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background: rgba(29, 29, 31, .3);
        z-index: 999 !important;
    }
.header {
  width: 100%;
}
.top {
  background: #1d1d1f;
  height: 60px;
  .bga {
    margin: 0 32px 0 18px;
    height: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    .t-left {
      img {
        height: 40px;
        width: auto;
      }
    }
    .t-right {
      color: #ffffff;
      font-size: 14px;
      display: flex;
      align-items: center;
      img {
        width: 19px;
        height: 22px;
        margin-right: 8px;
      }
      span {
        margin-left: 19px;
        font-size: 16px;
        color: #ffffff;
      }
    }
  }
}
</style>
