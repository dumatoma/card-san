<template>
  <div>
    <div class="tit">メニュー 一覧</div>

    <div class="header u-flex u-row-between m-t-30">
      <div class="u-flex">
        表示カテゴリー
        <el-select
          v-model="industry"
          @change="selectChanged"
        >
          <el-option
            v-for="item in industryList"
            :key="item.id"
            :label="item.name"
            :value="item.id"
          >
          </el-option>
        </el-select>
      </div>
      <div class="append no-size u-flex-center" @click="goMenuRegister">
        <i
          class="el-icon-circle-plus"
          style="color: #1a73e8; margin-right: 5px"
        ></i>
        メニュー登録
      </div>
    </div>
    <div v-for="(item, index) in list" :key="index">
      <div class="cont_tit no-size u-flex" v-text="item.name"></div>
      <div class="cont no-size">
        <div class="cont_top u-flex u-row-between" v-for="ite,idx in item.menus">
          <div class="u-flex">
            <div class="img" v-if="ite.image.length > 0">
              <img
                    style="width: 100px;height: 100px;object-fit: cover;"
                    :src="ite.image[0]"></img>
            </div>
            <div class="cont_center u-flex-col u-row-around">
              <div class="tname" v-text="ite.title" @click="toEdit(ite)"></div>
              <div v-text="`${ite.price}`"></div>
              <div>所要時間:{{ite.order_time}}分</div>
            </div>
          </div>
       <!--   <el-tooltip class="item" effect="dark" content="削除" placement="bottom">
                <img style="cursor: pointer;" src="@/static/index/delete.png" class="delete" @click="deleteItem(ite.id)" />
           </el-tooltip> -->
           <div style="display: flex;align-items: center;">
               <img src="@/static/svg/upup.svg" style="margin-right: 20px;width: 26px;height: 26px;" class="shou"
                   @click.stop="sort(item,ite,idx)" v-if="idx != 0" />
                 <el-tooltip class="item" effect="dark" content="削除" placement="bottom">
                        <img style="cursor: pointer;" src="@/static/index/delete.png" class="delete" @click="deleteItem(ite.id)" />
                   </el-tooltip>
           </div>
         
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import {getMenuList,getCates,deleteMenu,getStore,menusort} from "@/http/api.js"
import { Loading } from 'element-ui';
export default {
  data() {
    return {
      edit: false,
      ed:0,
      temp:[
          {
              name:"すべて",
              id:0
          }
      ],
      industryList: [],
	  industry:"すべて",
      list:[],
      infactlist:[]
    };
  },
  created() {
    let that = this
    getStore().then((res) => {
        console.log("userinfo",res)
        let temp = localStorage.getItem("admin")
        let info = JSON.parse(temp)
        console.log(info)
        if((info.admin_type == 2 && res.data.shop_info.page_footer_icon.includes('menu')) ||  info.admin_type == 1){
            that.ed = 1
        }else{
            that.ed = 0
        }
        that.getLsit()
    })
    
  },
  methods: {
      sort(item,ite,index){
          let that = this 
          let options = {}
           options.text = "読み込み中"
          that.$loading(options)
          let ar = []
          item.menus.forEach((v, i) => {
              ar.push(v.id)
          })
          that.swapArrayElements(ar, index * 1 - 1, index)
          let data = {}
          data.ids = ar
          data.cid = ite.cid
          menusort(data).then((res) => {
              if (res.code == 200) {
                  getStore().then((res) => {
                      console.log("userinfo",res)
                      let temp = localStorage.getItem("admin")
                      let info = JSON.parse(temp)
                      console.log(info)
                      if((info.admin_type == 2 && res.data.shop_info.page_footer_icon.includes('menu')) ||  info.admin_type == 1){
                          that.ed = 1
                      }else{
                          that.ed = 0
                      }
                      that.getLsit()
                  })
              } else {
                  that.$message({
                      message: res.message,
                      type: 'error',
                      offset: 400
                  });
              }
          })
          console.log(item)
      },
      swapArrayElements(arr, indexA, indexB) {
          const temp = arr[indexA];
          arr[indexA] = arr[indexB];
          arr[indexB] = temp;
      },
      deleteItem(id){
          let that = this 
         deleteMenu(id).then((res) => {
             if(res.code == 200){
                 that.$message({
                     message:res.message,
                     type:"success",
                     offset:400
                 })
                 that.getLsit()
             }else{
                 that.$message({
                 	message: res.message,
                 	type: 'error',
                      offset: 400
                 });
             }
         }) 
      },
      getLsit(){
        let that = this
        getCates().then((res) => {
            if(res.code == 200){
             getMenuList().then((result) => {
                 console.log("result",result)
                 if(result.code == 200){
                    res.data.cates.forEach((item,index) => {
                        item.menus = []
                        result.data.menus.forEach((ite,idx) => {
                            if(ite.cid == item.id){
                                item.menus.push(ite)
                            }
                        })
                    }) 
                    that.list = res.data.cates
                    that.infactlist = res.data.cates
                    that.industryList = [...that.temp , ...that.list]
                    that.$loading().close()
                 }
             })   
            }
        })
      },
	  selectChanged(e){
          console.log(e)
         
          let that = this 
           console.log(that.infactlist)
          let temp = []
          if(e == 0){
              that.list = that.infactlist
          }else{
              that.infactlist.forEach((v,i) => {
                  if(v.id == e){
                      temp.push(v)
                  }
              })
              that.list = temp
          }
	  },
	  goMenuRegister(){
          if(this.ed == 1){
              this.$router.push({
                path: "/menuRegister?type=add",
              });
          }else{
              this.$message({
              	message: '実行する権限がありません',
              	type: 'error',
                   offset: 400
              });
          }
		
	  },
      toEdit(item){
          console.log(item)
          this.$router.push({
            path: "/menuRegister?type=edit&cid="+item.cid+"&id="+item.id,
          });
      }
  },
};
</script>

<style lang="scss" scoped>
.tname:hover{
   color: #1a73e8 !important; 
   text-decoration: underline;
   cursor: pointer;
}
    
.tit {
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
}
.append:hover{
    background: rgba(26,115,232,0.1) !important;
    cursor: pointer;
}
.header {
  width: 820px;
  font-size: 16px;
  color: #1d1d1f;
  .append {
    width: 176px;
    height: 40px;
    background: #ffffff;
    font-size: 16px;
    color: #1a73e8;
    border-radius: 20px;
    border: 1px solid #1a73e8;
  }
}
::v-deep .el-input__inner {
  width: 329px;
  padding-left: 10px;
  background: #f5f5f7;
  border-radius: 10px;
  margin-left: 10px;
  border: 1px solid #707070;
}
.cont_tit {
  width: 820px;
  height: 40px;
  font-size: 16px;
  color: #1d1d1f;
  padding-left: 20px;
  margin-top: 30px;
  font-weight: bold;
  background: rgba(29, 29, 31, 0.1);
}
.cont {
  width: 820px;
  background: #ffffff;
  margin-top: 10px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .cont_top {
    padding: 10px 30px 10px 10px;
    border-bottom: 1px solid #d2d2d7;
    .img {
      width: 100px;
      height: 100px;
      background: rgba(29, 29, 31, 0.1);
      border-radius: 6px;
      position: relative;
      border: 1px solid #eaeaea;
      flex-shrink: 0;
      .tip {
        font-size: 10px;
        color: #aaa;
        width: 100%;
        height: 100%;
        img {
            width: 100%;
        }
      }
    }
  }

  .cont_center {
    height: 100px;
    padding-left: 20px;
    div {
      &:nth-child(1) {
        font-size: 16px;
        font-weight: bold;
        color: #1d1d1f;
      }
      &:nth-child(2) {
        font-size: 16px;
        color: rgba(217, 48, 37, 0.65);
      }
      &:nth-child(3) {
        font-size: 16px;
        color: #707070;
      }
    }
  }
  .delete {
    width: 36px;
  }
  .cont_bottom {
    font-size: 16px;
    font-weight: bold;
    color: #1d1d1f;
    padding: 25px 30px 25px 20px;
  }
}
</style>
