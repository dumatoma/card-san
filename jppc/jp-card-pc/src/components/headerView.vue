<template>
  <div class="headr">
    <div class="bottom">
      <div class="bgs" style="width: 95%;">
        <div class="left">
          <!-- <img src="../static/menu1.svg" alt="" /> -->
          <div
            class="item shou"
            v-for="(item, index) in list"
            :key="index"
            @click="cut(index)"
            :style="index == 3?'width:145px' : ''"
          >
            <div class="border" v-if="ind == index"></div>
            <div style="width: 20px;height: 20px;position: relative;top: -12px;">
                <div :style="`backgroundImage: url(${svgData11})`" class="icon1" v-if="index == 0"></div>
                <div :style="`backgroundImage: url(${svgData12})`" class="icon1" v-if="index == 1"></div>
                <div :style="`backgroundImage: url(${svgData13})`" class="icon1" v-if="index == 2"></div>
                <div :style="`backgroundImage: url(${svgData14})`" class="icon1" v-if="index == 3"></div>
                <div :style="`backgroundImage: url(${svgData15})`" class="icon1" v-if="index == 4"></div>
                <a href="https://cardsan.zendesk.com/" target="_blank"> <div :style="`backgroundImage: url(${svgData16})`" class="icon1" v-if="index == 5"></div></a>   
            </div>
            <div :class="ind == index ? 'top-active' : 'top'">
              {{ item.title }}
            </div>
            <div class="cut" v-if="item.current > 0 && index == 3">
              {{ item.current > 99 ? "99+" : item.current }}
            </div>
            <div class="cuts" v-if="item.current > 0 && index == 4">
              {{ item.current > 99 ? "99+" : item.current }}
            </div>
          </div>
        </div>
        <div class="right">
          <div class="ls" v-if="info.is_try == 1">無料お試し中</div>
          <div class="ls" style="color: #707070;" v-if="info.is_try != 1">ご契約プラン</div>
          <div class="ris" v-if="info.is_try == 1 && info.is_renew == 0">残り{{restday}}日</div>
          <div class="ris" v-if="info.is_try == 1 && info.is_renew == 1">残り{{restMember}}人</div>
          <div class="ris" style="color: #000;" v-if="info.is_try != 1" :style="info.type == 1?'background:rgb(219,219,221)' : info.type == 2?'background:rgb(114,212,211)' :'background:rgb(231,193,23)'">{{info.type==2?'スタンダード':info.type == 1? 'ライト' : 'プレミアム'}}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {getMessageList,sendMessage,readMessage,getStore,getAppointNoticeList} from "@/http/api.js"
import { mapActions, mapGetters } from 'vuex';
import svg1 from '../static/svgData/svgjs/menu1.js'
import svg2 from '../static/svgData/svgjs/menu2.js'
import svg3 from '../static/svgData/svgjs/menu3.js'
import svg4 from '../static/svgData/svgjs/menu4.js'
import svg5 from '../static/svgData/svgjs/menu5.js'
import svg6 from '../static/svgData/svgjs/menu6.js'
export default {
  name: "",
  components: {},
  props: {
      ind: {
        type: Number,
        default: 0
      }
    },
  data() {
    return {
      i: 0,
      info:{},
      svgData1:'"' + svg1 + '"',
      svgData2:'"' + svg2 + '"',
      svgData3:'"' + svg3 + '"',
      svgData4:'"' + svg4 + '"',
      svgData5:'"' + svg5 + '"',
      svgData6:'"' + svg6 + '"',
      infos:{},
      svgData11:'"' + svg1 + '"',
      svgData12:'"' + svg2 + '"',
      svgData13:'"' + svg3 + '"',
      svgData14:'"' + svg4 + '"',
      svgData15:'"' + svg5 + '"',
      svgData16:'"' + svg6 + '"',
      restday:0,
      restMember:0,
      list: [
        { img: require("../static/menu1.svg"), title: "TOP" },
        { img: require("../static/menu2.svg"), title: "各種設定" },
        { img: require("../static/menu3.svg"), title: "アカウント" },
        {
          img: require("../static/menu4.svg"),
          title: "メッセージ",
          current: 0,
        },
        { img: require("../static/menu5.svg"), title: "予約", current: "0" },
        { img: require("../static/menu6.svg"), title: "ヘルプ" },
      ],
    };
  },
  computed: {
      socketMsgs() {
          return this.$store.state.msg;
      },
  },
  watch:{
      // 'socketMsgs': {
      //    //处理接收到的消息
      //    handler: function() {
      //    let that = this
      //    console.log("头部接收到消息")
      //    that.getUnreadnumber()
      //   }
      // },
      "ind":{
          handler: function(newval,oldval) {
          let that = this
          console.log("new",newval)
          console.log("old",oldval)
          // that.i = in
          }
      },
  },
  created() {
     // getAppointNoticeList().then((res) => {
     //     console.log('unread',res)
     //     this.list[4].current = res.data.unread_count * 1
     // })
     console.log("headShow",this.ind)
     this.getInfo()
     this.svgData11 = this.changeColor(this.svgData1,"#707070");
     this.svgData12 = this.changeColor(this.svgData2,"#707070");
     this.svgData13 = this.changeColor(this.svgData3,"#707070");
     this.svgData14 = this.changeColor(this.svgData4,"#707070");
     this.svgData15 = this.changeColor(this.svgData5,"#707070");
     this.svgData16 = this.changeColor(this.svgData6,"#707070");
     let user = localStorage.getItem("admin")
     let infos = JSON.parse(user)
     this.infos = infos
    setTimeout(() => {
      if (
        this.$route.name == "message" ||
        this.$route.name == "delivery" ||
        this.$route.name == "setting" ||
        this.$route.name == "accountInfo"
      ) {
        this.i = 3;
        this.svgData14 = this.changeColor(this.svgData4,"#1d1d1f");
      } else if (
        this.$route.name == "appointment" ||
        this.$route.name == "appointmentSetting"
      ) {
        this.i = 4;
        this.svgData15 = this.changeColor(this.svgData5,"#1d1d1f");
      } else if (
        this.$route.name == "topIndex" ||
        this.$route.name == "manage" ||
        this.$route.name == "notice"
      ) {
        this.i = 0;
      } else if (this.$route.name == "notice" || this.$route.name == "coupon") {
        this.i = 0;
        this.svgData11 = this.changeColor(this.svgData1,"#1d1d1f");
      } else if (
        this.$route.name == "storeInfo" ||
        this.$route.name == "member" ||
        this.$route.name == "designSet" ||
        this.$route.name == "typeRegister" ||
        this.$route.name == "menuList" ||
        this.$route.name == "menuRegister" ||
        this.$route.name == "deputyAdministrator"
      ) {
        this.i = 1;
        this.svgData12 = this.changeColor(this.svgData2,"#1d1d1f");
      } else if (
        this.$route.name == "user" ||
        this.$route.name == "utilizePlan" ||
        this.$route.name == "content" ||
        this.$route.name == "payMethod" ||
        this.$route.name == "detail"
      ) {
        this.i = 2;
         this.svgData13 = this.changeColor(this.svgData3,"#1d1d1f");
      }
    }, 200);
    setInterval(() => {
         this.getUnreadnumber() 
         // getAppointNoticeList().then((res) => {
         //     console.log('unread',res)
         //     this.list[4].current = res.data.unread_count * 1
         // })
    },3000)
   
  },
  mounted() {
       
  },
  methods: {
      getInfo(){
        let that = this
        getStore().then((res) => {
            console.log('head',res)
            if(res.code == 200){
                that.info = res.data.shop_info.vips[0]
                console.log("top",res.data.shop_info)
                that.restday = res.data.shop_info.vips[0].end_day
                that.restMember = 30 - res.data.member_count * 1
            }
        })
      },
      changeColor(url,color){
       	let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#", "%23"));//转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
       	return res;
       },
      getUnreadnumber(){
         let that = this
         setInterval(() => {
             let num = localStorage.getItem("unreadnum")
             that.list[3].current = num  
         },1000)
      },
    cut(index) {
      this.i = index;
      this.$emit("index", {
        index: index,
        hui: this.list[3].current,
        yueHui: this.list[4].current,
      });
      switch (index) {
        case 0:
          this.$router.push({
            path: "/topIndex",
          });
          this.svgData11 = this.changeColor(this.svgData1,"#1d1d1f");
          this.svgData12 = this.changeColor(this.svgData2,"#707070");
          this.svgData13 = this.changeColor(this.svgData3,"#707070");
          this.svgData14 = this.changeColor(this.svgData4,"#707070");
          this.svgData15 = this.changeColor(this.svgData5,"#707070");
          this.svgData16 = this.changeColor(this.svgData6,"#707070");
          break;

        case 1:
          this.$router.push({
            path: "/storeInfo",
          });
                   
          this.svgData11 = this.changeColor(this.svgData1,"#707070");
          this.svgData12 = this.changeColor(this.svgData2,"#1d1d1f");
          this.svgData13 = this.changeColor(this.svgData3,"#707070");
          this.svgData14 = this.changeColor(this.svgData4,"#707070");
          this.svgData15 = this.changeColor(this.svgData5,"#707070");
          this.svgData16 = this.changeColor(this.svgData6,"#707070");
          break;

        case 2:
        if(this.infos.admin_type == 1){
            this.$router.push({
              path: "/user",
            });
        }else{
            this.$router.push({
              path: "/fu",
            });
        }
          
          this.svgData11 = this.changeColor(this.svgData1,"#707070");
          this.svgData12 = this.changeColor(this.svgData2,"#707070");
          this.svgData13 = this.changeColor(this.svgData3,"#1d1d1f");
          this.svgData14 = this.changeColor(this.svgData4,"#707070");
          this.svgData15 = this.changeColor(this.svgData5,"#707070");
          this.svgData16 = this.changeColor(this.svgData6,"#707070");
          break;

        case 3:
          this.$router.push({
            path: "/message",
            // query: {
            //   id: this.id,
            // },
          });
          this.svgData11 = this.changeColor(this.svgData1,"#707070");
          this.svgData12 = this.changeColor(this.svgData2,"#707070");
          this.svgData13 = this.changeColor(this.svgData3,"#707070");
          this.svgData14 = this.changeColor(this.svgData4,"#1d1d1f");
          this.svgData15 = this.changeColor(this.svgData5,"#707070");
          this.svgData16 = this.changeColor(this.svgData6,"#707070");
          break;
        case 4:
          this.$router.push({
            path: "/appointment",
            // query: {
            //   id: this.id,
            // },
          });
          this.svgData11 = this.changeColor(this.svgData1,"#707070");
          this.svgData12 = this.changeColor(this.svgData2,"#707070");
          this.svgData13 = this.changeColor(this.svgData3,"#707070");
          this.svgData14 = this.changeColor(this.svgData4,"#707070");
          this.svgData15 = this.changeColor(this.svgData5,"#1d1d1f");
          this.svgData16 = this.changeColor(this.svgData6,"#707070");
          break;
        case 5:
          // this.svgData11 = this.changeColor(this.svgData1,"#707070");
          // this.svgData12 = this.changeColor(this.svgData2,"#707070");
          // this.svgData13 = this.changeColor(this.svgData3,"#707070");
          // this.svgData14 = this.changeColor(this.svgData4,"#707070");
          // this.svgData15 = this.changeColor(this.svgData5,"#707070");
          // this.svgData16 = this.changeColor(this.svgData6,"#1d1d1f");
          //    this.$router.push({
          //      path: "/help",
          //      // query: {
          //      //   id: this.id,
          //      // },
          //    });
          break;
        default:
          break;
      }
    },
  },
};
</script>

<style lang="scss" scoped>
.item:hover{
    background: rgba(29,29,31, .1) !important;
    color: rgba(29,29,31, .6) !important;
}
.no {
  transform:translateX(-10000px);
  filter: drop-shadow(10000px 0 0 #707070);
}
.sv {
  transform: translate(-10000px);
  filter: drop-shadow(
    10000px 0 0 #1d1d1f
  ); // 参数详解：阴影X偏移、阴影Y偏移、模糊值大小、色值
}
.icon1{
    width: 25px;
    height: 25px;
    background-size: 100% 100%;
    display: inline-block;
    margin-right: 10px;
}
.headr {
  width: 100%;
  box-sizing: border-box;
  overflow: hidden;
  border-bottom: 1px solid #d2d2d7;
}
.bottom {
  height: 54px;
  background: #f5f5f7;
  .bgs {
    margin: 0 37px 0 58px;
    height: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #1d1d1f;
    .left {
      height: 100%;
      display: flex;
      align-items: center;
      width: 70%;
      
      
      .item {
        height: 100%;
        display: flex;
        align-items: center;
        line-height: 54px;
        margin-right: 40px;
        position: relative;
        width: 120px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        justify-content: center;
        flex-shrink: 0;
        
        .border {
          position: absolute;
          bottom: 0;
          left: -20px;
          width: calc(100% + 40px);
          height: 4px;
          background: #1a73e8;
          border-radius: 10px 10px 10px 10px;
        }
        .cut {
          // position: absolute;
          // top: 0;
          // right: 0;
          color: #ffffff;
          font-size: 12px;
          // padding: 0 5px;
          width: 26px;
          text-align: center;
          height: 26px;
          line-height: 26px;
          background: #d93025;
          border-radius: 50%;
          margin-left: 10px;
        }
        .cuts {
          color: #ffffff;
          font-size: 12px;
          // padding: 0 5px;
          width: 26px;
          text-align: center;
          height: 26px;
          line-height: 26px;
          background: #2699fb;
          border-radius: 50%;
          margin-left: 10px;
        }
        .top {
          margin-left: 10px;
          color: #707070;
          font-size: 16px;
        }
        .top-active {
          margin-left: 10px;
          color: #1d1d1f;
          font-weight: 700;
        }
      }
    }
    .right {
      display: flex;
      align-items: center;
      .ls {
        font-size: 16px;
        color: #2699fb;
        margin-right: 14px;
      }
      .ris {
        height: 30px;
        line-height: 30px;
        text-align: center;
        padding: 0 20px;
        background: #d93025;
        box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
        color: #ffffff;
        font-size: 16px;
        border-radius: 20px;
      }
    }
  }
}
</style>
