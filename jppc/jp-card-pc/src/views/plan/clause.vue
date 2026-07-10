<template>
  <div>
    <div class="title">利用規約とプライバシーポリシー</div>
    <div class="cont">
      <div class="cont_tit">利用規約</div>
      <textarea class="tarea no-size" disabled v-model="clause"></textarea>
	  <div class="cont_tit m-t-60">プライバシーポリシー</div>
	   <textarea class="tarea no-size" disabled v-model="policy"></textarea>
    </div>
  </div>
</template>

<script>
import {getConfig} from "@/http/api.js"
export default {
  data() {
    return {
		clause:'',
		policy:''
	
	};
  },
  created() {
    let that = this
    that.getConfig()
  },
  methods: {
      getConfig(){
          let that = this
          getConfig().then((res) => {
              console.log(res)
              if(res.code == 200){
                  that.clause = res.data.config.shop_service_regulations
                   that.policy = res.data.config.shop_privacy_policy
              }
          })
      }
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
  width: 820px;
  background: #fff;
  margin-top: 33px;
  padding: 27px 40px 40px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);

  .cont_tit {
    font-size: 16px;
    font-weight: bold;
    color: #1d1d1f;
  }
  .tarea {
    width: 740px;
    height: 306px;
    outline: 0;
	line-height: 20px;
    padding:10px 16px;
    font-size: 12px;
    color: #1d1d1f;
    background: #fff;
    border-radius: 10px;
    margin-top: 10px;
    border: 1px solid #aaa;
    resize: none;
  }
}
</style>
