<template>
  <div>
    <div class="tit u-flex u-row-between">
      <div>副管理者一覧</div>
      <div
        class="append no-size u-flex-center"
        @click="administratorClick"
      >
        <i
          class="el-icon-circle-plus"
          style="color: #1a73e8; margin-right: 5px"
        ></i>
        副管理者の登録
      </div>
    </div>
    <div class="cont no-size">
      <div
        class="cont_item no-size u-flex u-row-between"
        v-for="(item, index) in list"
        :key="index"
      >
        <div class="u-flex">
          <div class="cont_top">
              <span @click="toDetail(item.id)" v-text="item.name"></span>
          </div>
          <div class="cont_bottom">登録電話番号：{{item.phone}}</div>
        </div>
        <el-tooltip class="item" effect="dark" content="削除" placement="bottom">
             <img src="@/static/index/delete.png" alt="" @click.stop="deleteItem(item.id)" />
         </el-tooltip>
        
      </div>
    </div>
  </div>
</template>

<script>
import{getFu,deleteFu} from "@/http/api.js"
export default {
  data() {
    return {
      // appendShow: true,
      edit: false,
      list:[]
    };
  },
  created() {
    let that = this
    that.getList()
  },
  methods: {
    administratorClick() {
      this.$router.push({
        path: "/deputyAdministrator?type=add",
      });
    },
    deleteItem(id){
      let that = this 
       let data = {}
       data["id"] = id
       data['_method'] = "delete"
      deleteFu(data).then((res) => {
          console.log(res)
          if(res.code == 200){
              that.$message({
              	message: res.message,
              	type: 'success',
                  offset:400
              });
              that.getList()
          }else{
              that.$message({
              	message: res.message,
              	type: 'error',
                  offset:400
              });
          }
      })
    },
    toDetail(id){
      let that = this
      that.$router.push({
        path: "/deputyAdministrator?type=edit&id="+id,
      });
    },
    getList(){
      let that = this 
      getFu().then((res) => {
          console.log(res)
          if(res.code == 200){
              that.list = res.data.admins
          }else{
              that.$message.error(res.message)
          }
      })
    },
  },
};
</script>

<style lang="scss" scoped>
    .append:hover{
        background: rgba(26,115,232,0.1) !important;
        cursor: pointer;
    }
    .cont_top span:hover{
        cursor: pointer;
        color: #1a73e8 !important;
         text-decoration: underline;
    }
.tit {
  width: 820px;
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
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
.cont {
  width: 820px;
  background: #ffffff;
  padding: 40px;
  margin-top: 20px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .cont_item {
    width: 740px;
    height: 60px;
    font-size: 16px;
    color: #1d1d1f;
    font-weight: bold;
    background: #f5f5f7;
    border-radius: 10px;
    margin-bottom: 10px;
    padding: 20px;
    &:last-child {
      margin: 0;
    }
    .cont_top{
      width: 200px;
    }
    img {
      width: 38px;
    }
  }
}
</style>
