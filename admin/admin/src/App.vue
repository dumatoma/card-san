<template>
  <div id="app">
    <el-container v-if="routerName != 'Login'">
      <el-aside width="270px;height:100vh;overflow-y:auto">
        <div class="top">
          <div class="top1">
            CARD-SAN
          </div>
          <div class="top2">
            Customaid:LLC
          </div>
        </div>

        <div class="items">
          <div class="item1" v-for="item,index in list" v-if="item.pid == 0 || pid == item.pid"
            :class="(item.id == current && item.show == false)?'active1':''" @click="changeRouter(item)">
            <div class="sel" v-if="item.id == current && item.show == false"></div>
            <div class="item_left">
              <div class="item_icon" v-if="item.id == current"
                :style="item.aicon?'visibility:visible' : 'visibility:hidden'">
                <img :src="item.aicon" alt="">
              </div>
              <div class="item_icon" v-else>
                <img :src="item.icon" :style="item.icon?'visibility:visible' : 'visibility:hidden'" alt="">
              </div>
              <div v-text="item.name"></div>
            </div>
            <div class="item_right" v-if="item.show" :style="(current == item.id || item.id == pid)?'transform:rotate(90deg)' : ''">
              <i class="el-icon-arrow-right"></i>
            </div>
          </div>
        </div>

      </el-aside>
      <el-container>
        <el-header height="77px">
          <div class="headers">
            <div class="logout" @click="logout">ログアウト</div>
            <div id="email">ID:admin</div>
            <img src="@/assets/user.png" class="userIcon" alt="">
          </div>
        </el-header>

        <el-main style="height: calc(100vh - 77px);overflow-y: auto;">
          <router-view />
        </el-main>

      </el-container>
    </el-container>
    <div v-if="routerName == 'Login'">
       <router-view />
    </div>
  </div>
</template>

<script>
  export default {
    name: 'App',
    data() {
      return {
        current: 1,
        pid: 0,
        hcurrent:1,
        routerName:"Login",
        list: [{
            name: "ダッシュボード",
            icon: require("@/assets/a1.png"),
            aicon: require("@/assets/a1a.png"),
            show: false,
            path: "/Home",
            id: 1,
            pid: 0
          },
          {
            name: "分析",
            icon: require("@/assets/a2.png"),
            aicon: require("@/assets/a2.png"),
            show: true,
            path: "/Sale",
            id: 3,
            pid: 0
          },
          {
            name: "売上",
            icon: "",
            aicon: "",
            show: false,
            path: "/Sale",
            id: 3,
            pid: 3
          },
          {
            name: "契約・解約状況",
            icon: "",
            aicon: "",
            show: false,
            path: "/Offtrade",
            id: 4,
            pid: 3
          },
          {
            name: "顧客管理",
            icon: require("@/assets/a4.png"),
            aicon: require("@/assets/a4a.png"),
            show: false,
            path: "/Customer",
            id: 6,
            pid: 0
          },
          {
            name: "お知らせ投稿",
            icon: require("@/assets/a5.png"),
            aicon: require("@/assets/a5.png"),
            show: true,
            path: "/Notice",
            id: 8,
            pid: 0
          },
          {
            name: "契約者",
            icon: "",
            aicon: "",
            show: false,
            path: "/Notice",
            id: 8,
            pid: 8
          },
          {
            name: "エンドユーザー",
            icon: "",
            aicon: "",
            show: false,
            path: "/NoticeCustomer",
            id: 9,
            pid: 8
          },
          {
            name: "販促アイテム",
            icon: require("@/assets/a6.png"),
            aicon: require("@/assets/a6a.png"),
            show: false,
            path: "/Shop",
            id: 10,
            pid: 0
          },
          {
            name: "ポップアップ",
            icon: require("@/assets/a7.png"),
            aicon: require("@/assets/a7a.png"),
            show: false,
            path: "/Alerts",
            id: 11,
            pid: 0
          },
          {
            name: "利用規約",
            icon: require("@/assets/a8.png"),
            aicon: require("@/assets/a8.png"),
            show: true,
            path: "/Rules?type=1",
            id: 13,
            pid: 0
          },
          {
            name: "契約者用",
            icon: "",
            aicon: "",
            show: false,
            path: "/Rules?type=1",
            id: 13,
            pid: 13
          },
          {
            name: "エンドユーザー用",
            icon: "",
            aicon: "",
            show: false,
            path: "/Privicy?type=2",
            id: 14,
            pid: 13
          },
          {
            name: "プラポリ",
            icon: require("@/assets/a9.png"),
            aicon: require("@/assets/a9.png"),
            show: true,
            path: "/Rule?type=3",
            id: 16,
            pid: 0
          },
          {
            name: "契約者用",
            icon: "",
            aicon: "",
            show: false,
            path: "/Privicy?type=3",
            id: 16,
            pid: 16
          },
          {
            name: "エンドユーザー用",
            icon: "",
            aicon: "",
            show: false,
            path: "/Rules?type=4",
            id: 17,
            pid: 16
          },
        ]
      };
    },
    watch: {
      // 如果路由有变化，会再次执行该方法
      $route: "fetchDate",
    },
    created(){
      console.log("refresh",this.$route.name)
      this.routerName = this.$route.name
    },
    methods: {
      fetchDate() {
        console.log("name-=", this.$route.name)
        this.routerName = this.$route.name
      },
      logout(){
          this.$router.push('/Login')
      },
      changeRouter(e) {
        let that = this
        console.log("e=",e)
        if (e.show == true) {
          // this.pid = e.id
          this.hcurrent = e.id
          if(that.current == e.id || that.pid == e.id){
            that.current = 9999
            that.pid = 9999
          }else{
            that.current = e.id
            that.pid = e.id
          }

        } else {
          this.current = e.id
          if (e.pid == this.pid) {
            this.pid = e.pid
          } else {
            this.pid = 0
          }
        }
        this.$router.push(e.path)
      }
    }
  }
</script>

<style>
  ::-webkit-scrollbar {
      width: 8px; /* 设置滚动条宽度 */
  }

  ::-webkit-scrollbar-track {
      background-color: #f5f5f5 !important; /* 设置背景色 */
  }

  ::-webkit-scrollbar-thumb {
      background-color: #888; /* 设置滑块颜色 */
      border-radius: 4px !important;
      height: 50px !important;
  }

  ::-webkit-scrollbar-thumb:hover {
      background-color: #555 !important; /* 当鼠标悬停在滑块上时的颜色 */
      cursor: pointer !important;
  }

  .item_right{
    transition: all linear 0.3s;
    margin-left: 28px;
  }
  .el-select {
    width: 240px !important;
    height: 48px !important;
    background: #f1f9ff !important;
    border: 1px solid #bce0fd;
    border-radius: 8px;
  }

  .pointer {
    cursor: pointer;
  }

  .table_content::-webkit-scrollbar {
    display: none;
  }

  .el-upload-dragger {
    width: 100% !important;
    background: #f7faff !important;
    border: 1px dashed #2699fb;
    width: 883px !important;
    height: 120px !important;
    box-sizing: border-box;
    padding-top: 35px !important;
  }

  .pik {
    margin-top: 26px;
  }

  #pic .el-input {
    height: 100% !important;
    width: 100% !important;
    color: #007aff !important;
  }

  #pic .el-select .el-input__inner {
    height: 100% !important;
    width: 100% !important;
    color: #007aff !important;
    background: transparent !important;
    border: none;
    font-size: 16px !important;
  }

  .w156 {
    width: 156px;
  }

  .w144 {
    width: 144px;
  }

  .w180 {
    width: 180px;
  }

  .w354 {
    width: 354px;
  }

  .w126 {
    width: 126px;
  }

  .w92 {
    width: 92px;
  }

  .w118 {
    width: 118px;
  }

  .container {
    background: #f0f0f0;
    height: 100%;
    box-sizing: border-box;
    padding: 52px 78px 100px;
    overflow-y: auto;
  }

  .headers {
    width: 100%;
    height: 77px;
    display: flex;
    align-items: center;
    background: #fff;
    flex-direction: row-reverse;
  }

  .logout {
    font-size: 20px;
    margin-left: 32px;
    cursor: pointer;
  }

  .userIcon {
    width: 32px;
    height: 32px;
    margin-right: 10px;
  }

  #email {
    font-family: Hiragino Sans-W3, Hiragino Sans;
    font-weight: 400;
    color: #0a4b6d;
  }

  .sel {
    position: absolute;
    left: 0;
    top: 0;
    width: 5px;
    height: 100%;
    background: #aaffbe;
  }

  .item_left {
    display: flex;
    align-items: center;
  }

  .item_icon {
    width: 29px;
    height: 29px;
    margin-right: 24px;
  }

  .item_icon img {
    width: 100%;
    height: 100%
  }

  .active1 {
    background: #103c6e !important;
    color: #aaffbe !important;
  }

  .item1 {
    display: flex !important;
    align-items: center;
    justify-content: space-between;
    box-sizing: border-box;
    padding: 15px 28px;
    font-size: 20px;
    font-family: Hiragino Sans-W6, Hiragino Sans;
    font-weight: 400;
    color: #fff;
    position: relative;
    cursor: pointer;
  }

  .items {
    margin-top: 39px;
  }

  .top1 {
    font-size: 29px;
    font-family: Lato-Black, Lato;
    font-weight: 900;
    color: #fbfbfd;
  }

  .top2 {
    font-size: 17px;
    font-family: Lato-Regular, Lato;
    font-weight: 400;
    color: #fbfbfd;
  }

  .top {
    box-sizing: border-box;
    padding: 20px 26px;
  }

  * {
    padding: 0;
    margin: 0;
    font-family: Arial, Helvetica, sans-serif !important;
  }

  .el-aside {
    background: #174a84;
    min-height: 100vh !important;
    padding-bottom: 100px;
  }

  .el-main {
    background-color: #f0f0f0;
    padding: 0 !important;
  }

  .el-header {
    background-color: #fff;
    text-align: center;
    height: 77px;
    line-height: 77px;
  }
</style>
