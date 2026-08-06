<template>
  <div class="side">
    <div></div>
    <div></div>
    <!-- 预约 -->
    <div class="its" v-if="current1 == 4">
      <div
        :class="index == 1 ? 'item active no-size' : 'item  shou no-size'"
        @click="yueCuts(1)"
      >
        予約一覧<span class="hui1" v-if="yueNum > 0">{{
          yueNum > 99 ? "99+" : yueNum
        }}</span>
      </div>
      <div
        :class="index == 2 ? 'item active no-size' : 'item  shou no-size'"
        @click="yueCuts(2)"
      >
        予約基本設定
      </div>
      <div
        :class="index == 3 ? 'item active no-size' : 'item  shou no-size'"
        @click="yueCuts(3)"
      >
        予約機能外部LINK設定
      </div>
    </div>
    <!-- 信息 -->
    <div class="its" v-if="current1 == 3">
      <div
        :class="index == 1 ? 'item active no-size' : 'item  shou no-size'"
        @click="cuts(1)"
      >
        メッセージチャット<span class="hui" v-if="num > 0">{{
          num > 99 ? "99+" : num
        }}</span>
      </div>
      <div
        :class="index == 2 ? 'item active no-size' : 'item  shou no-size'"
        @click="cuts(2)"
      >
        一斉 or セグメント配信
      </div>
      <div
        :class="index == 3 ? 'item active no-size' : 'item  shou no-size'"
        @click="cuts(3)"
      >
        自動メッセージ設定
      </div>
      <div
        :class="index == 4 ? 'item active no-size' : 'item  shou no-size'"
        @click="cuts(4)"
      >
        アカウント情報
      </div>
      <div
        :class="index == 5 ? 'item active no-size' : 'item  shou no-size'"
        @click="cuts(5)"
      >
        レビューリンク設定
      </div>
      <div
        :class="index == 6 ? 'item active no-size' : 'item  shou no-size'"
        @click="cuts(6)"
      >
        メッセージテンプレート
      </div>
      <div
        :class="index == 7 ? 'item active no-size' : 'item  shou no-size'"
        @click="cuts(7)"
        v-if="type == 1"
      >
        スタッフルーム設定
      </div>
      <div
        :class="index == 8 ? 'item active no-size' : 'item  shou no-size'"
        @click="cuts(8)"
      >
        ルームチャット<span class="hui" v-if="roomNum > 0">{{
          roomNum > 99 ? "99+" : roomNum
        }}</span>
      </div>
    </div>
    <!-- TOP -->
    <div class="its" v-if="current1 == 0">
      <div
        class="u-flex"
        v-for="(item, index) in topList"
        :key="index"
        :class="
          index == topIndex ? 'item active no-size' : 'item  shou no-size'
        "
        @click="top(index)"
      >
        <img :src="index == topIndex ? item.imgCss : item.img" alt="" />
        {{ item.title }}
      </div>
    </div>
    <!-- 各種設定 -->
    <div class="its" v-if="current1 == 1">
      <div v-for="(item, index) in setList" :key="index" @click="set(index)">
        <div
          class="u-flex u-row-between"
          :class="
            index == setIndex
              ? index != 4
                ? 'item active no-size'
                : 'item shou no-size'
              : 'item  shou no-size'
          "
        >
          <div class="u-flex">
            <img
              :src="
                index == setIndex
                  ? index != 4
                    ? item.imgCss
                    : item.img
                  : item.img
              "
              v-if="index == 1"
              style="height: 17px"
            />
            <img
              v-else
              :src="
                index == setIndex
                  ? index != 4
                    ? item.imgCss
                    : item.img
                  : item.img
              "
            />
            {{ item.title }}
          </div>
          <i
            :class="
              index == setIndex && menuShow
                ? 'el-icon-arrow-down'
                : 'el-icon-arrow-right'
            "
            style="font-weight: bold; margin-right: 10px"
            v-if="index == 4"
          ></i>
        </div>
        <div
          class="no-size shou item menu"
          :class="index == 4 && n == menuIndex ? ' menuCss' : ''"
          v-for="(v, n) in menuList"
          :key="n"
          v-show="index == 4 && index == setIndex && menuShow"
          @click.stop="menuClick(n)"
        >
          {{ v }}
        </div>
      </div>
    </div>
    <!-- アカウント账户 -->
    <div class="its" v-if="current1 == 2">
        <div v-if="type == 1" :class="0 == accountIndex ? 'item active no-size' : 'item  shou no-size'" @click="accountClick(0)">
            オーナー情報
        </div>
        <div v-else :class="0 == accountIndex ? 'item active no-size' : 'item  shou no-size'" @click="accountClick(0)">
            副管理者情報
        </div>
      <div
        v-for="(item, index) in accountList"
        :key="index"
        :class="
          index == accountIndex ? 'item active no-size' : 'item  shou no-size'
        "
        @click="accountClick(index)"
        v-if="index != 0"
      >
        <div v-if="index != 5">{{ item }}</div>
        <div v-else>
          利用規約・<br />
          プライバシーポリシー
        </div>
      </div>
    </div>
    <div></div>
  </div>
</template>

<script>
import { getStaffRoomUnread, getStaffDmThreads } from "@/http/api.js";
export default {
  props: {
    current: {
      type: Number,
      default: 0,
    },
    yueNum: {
      type: Number,
      default: 0,
    },
  },
  name: "",
  components: {},
  data() {
    return {
      index: 1,
      indexes: 1,
      current1: 0,
      type:1,
      num:0,
      roomNum:0,
      topList: [
        {
          title: "ダッシュボード",
          img: require("../static/index/topOne.png"),
          imgCss: require("../static/index/topOneSelect.png"),
        },
        {
          title: "メンバー管理",
          img: require("../static/index/topTwo.png"),
          imgCss: require("../static/index/topTwoSelect.png"),
        },
        {
          title: "お知らせ",
          img: require("../static/index/topThree.png"),
          imgCss: require("../static/index/topThreeSelect.png"),
        },
        {
          title: "クーポン",
          img: require("../static/index/topFour.png"),
          imgCss: require("../static/index/topFourSelect.png"),
        },
      ],
      topIndex: 0,
      setList: [
        {
          title: "店舗情報",
          img: require("../static/index/s2.svg"),
          imgCss: require("../static/icon1.svg"),
        },
        {
          title: "メンバーズカード",
          img: require("../static/index/cardImg.png"),
          imgCss: require("../static/index/card1.png"),
        },
        {
          title: "デザイン設定",
          img: require("../static/index/start.png"),
          imgCss: require("../static/index/startSelect.png"),
        },
        {
          title: "会員登録情報",
          img: require("../static/index/user.png"),
          imgCss: require("../static/index/userSelect.png"),
        },
        {
          title: "メニュー",
          img: require("../static/index/menu.png"),
          imgCss: require("../static/index/cai1.png"),
        },
        {
          title: "カレンダー",
          img: require("../static/index/calenda.png"),
          imgCss: require("../static/index/calendaSelect.png"),
        },
        {
          title: "スタッフ登録",
          img: require("../static/icon2.svg"),
          imgCss: require("../static/index/threeSelect.png"),
        },
        {
          title: "副管理者の設定",
          img: require("../static/index/assistant.png"),
          imgCss: require("../static/index/assSelect.png"),
        },
      ],
      setIndex: 0,
      menuList: ["カテゴリー登録", "メニュー登録", "外部リンク"],
      menuIndex: 0,
      menuShow: false,
      accountList: [
        "オーナー情報",
        // "お支払い方法",
        "ご利用プラン",
        // "ご利用明細",
        "ご契約内容",
        "利用規約・プライバシーポリシー",
        "バージョン情報",
      ],
      accountIndex: 0,
    };
  },
  watch: {
    current() {
      this.current1 = this.current;
    },
    $route: {
      handler(val, oldVal) {
        if (val.path == "/message") {
          this.current1 = 3;
          this.index = 1;
        } else if (val.path == "/delivery") {
          this.current1 = 3;
          this.index = 2;
        } else if (val.path == "/setting") {
          this.current1 = 3;
          this.index = 3;
        } else if (val.path == "/accountInfo") {
          this.current1 = 3;
          this.index = 4;
        } else if (val.path == "/staffRoomSet") {
          this.current1 = 3;
          this.index = 7;
        } else if (val.path == "/roomChat") {
          this.current1 = 3;
          this.index = 8;
        } else if (val.path == "/appointment") {
          this.current1 = 4;
          this.index = 1;
        } else if (val.path == "/appointmentSetting") {
          this.current1 = 4;
          this.index = 2;
        } else if (val.path == "/topIndex") {
          this.current1 = 0;
          this.topIndex = 0;
        } else if (val.path == "/manage" || val.path == "/download") {
          this.current1 = 0;
          this.topIndex = 1;
        } else if (
          val.path == "/notice" ||
          val.path == "/noticeSet" ||
          val.path == "/contribution"
        ) {
          this.current1 = 0;
          this.topIndex = 2;
        } else if (val.path == "/coupon" || val.path == "/foundCoupon") {
          this.current1 = 0;
          this.topIndex = 3;
        } else if (val.path == "/storeInfo") {
          this.current1 = 1;
          this.setIndex = 0;
        } else if (val.path == "/memeber" || val.path == "/markPrinting") {
          this.current1 = 1;
          this.setIndex = 1;
        } else if (val.path == "/user") {
          this.current1 = 2;
          this.accountIndex = 0;
        } else if (
          val.path == "/utilizePlan" ||
          val.path == "/changeContract" ||
          val.path == "/termination" ||
          val.path == "/terminationFulfil"
        ) {
          this.current1 = 2;
          this.accountIndex = 1;
        } else if (val.path == "/detail") {
          this.current1 = 2;
          this.accountIndex = 3;
        } else if (val.path == "/content") {
          this.current1 = 2;
          this.accountIndex = 2;
        } else if (val.path == "/clause") {
          this.current1 = 2;
          this.accountIndex = 3;
        } else if (val.path == "/edition") {
          this.current1 = 2;
          this.accountIndex = 4;
        } else if (val.path == "/member") {
          this.current1 = 1;
          this.setIndex = 1;
        } else if (val.path == "/designSet") {
          this.current1 = 1;
          this.setIndex = 2;
        } else if (val.path == "/memberInfo") {
          this.current1 = 1;
          this.setIndex = 3;
        } else if (val.path == "/typeRegister" || val.path == "/menuList") {
          this.current1 = 1;
          this.setIndex = 4;
        } else if (val.path == "/calendar" || val.path == "/time") {
          this.current1 = 1;
          this.setIndex = 5;
        } else if (val.path == "/registration" || val.path == "/personnel") {
          this.current1 = 1;
          this.setIndex = 6;
        } else if (
          val.path == "/deputyAdministrator" ||
          val.path == "/deputyLook"
        ) {
          this.current1 = 1;
          this.setIndex = 7;
        }
      },
    },
  },
  created() {
      let admin = localStorage.getItem("admin")
      let result = JSON.parse(admin)
      this.type = result.admin_type
      
      setInterval(() => {
          let num = localStorage.getItem("unreadnum")
          this.num = num
      },1000)

      // ルームチャット未読（グループ＋1対1）バッジ
      this.getRoomNum()
      setInterval(() => { this.getRoomNum() }, 8000)


    setTimeout(() => {
      if (this.$route.name == "message") {
        this.current1 = 3;
        this.index = 1;
      } else if (this.$route.name == "delivery") {
        this.current1 = 3;
        this.index = 2;
      } else if (this.$route.name == "setting") {
        this.current1 = 3;
        this.index = 3;
      } else if (this.$route.name == "accountInfo") {
        this.current1 = 3;
        this.index = 4;
      } else if (this.$route.name == "staffRoomSet") {
        this.current1 = 3;
        this.index = 7;
      } else if (this.$route.name == "roomChat") {
        this.current1 = 3;
        this.index = 8;
      } else if (this.$route.name == "appointment") {
        this.current1 = 4;
        this.index = 1;
      } else if (this.$route.name == "appointmentSetting") {
        this.current1 = 4;
        this.index = 2;
      } else if (this.$route.name == "topIndex") {
        this.current1 = 0;
        this.topIndex = 0;
      } else if (
        this.$route.name == "manage" ||
        this.$route.name == "download"
      ) {
        this.current1 = 0;
        this.topIndex = 1;
      } else if (
        this.$route.name == "notice" ||
        this.$route.name == "noticeSet" ||
        this.$route.name == "contribution"
      ) {
        this.current1 = 0;
        this.topIndex = 2;
      } else if (
        this.$route.name == "coupon" ||
        this.$route.name == "foundCoupon"
      ) {
        this.current1 = 0;
        this.topIndex = 3;
      } else if (this.$route.name == "storeInfo") {
        this.current1 = 1;
        this.setIndex = 0;
      } else if (this.$route.name == "user") {
        this.current1 = 2;
        this.accountIndex = 0;
      } else if (this.$route.name == "payMethod") {
        this.accountIndex = 1;
        this.current1 = 2;
      } else if (this.$route.name == "detail") {
        this.accountIndex = 3;
        this.current1 = 2;
      } else if (this.$route.path == "memeber") {
        this.current1 = 1;
        this.setIndex = 1;
      } else if (this.$route.path == "designSet") {
        this.current1 = 1;
        this.setIndex = 2;
      } else if (this.$route.path == "deputyAdministrator") {
        this.current1 = 1;
        this.setIndex = 7;
      } else if (
        this.$route.path == "utilizePlan" ||
        this.$route.path == "changeContract" ||
        this.$route.path == "termination" ||
        this.$route.path == "terminationFulfil"
      ) {
        this.current1 = 2;
        this.accountIndex = 2;
      } else if (
        this.$route.name == "memeber" ||
        this.$route.name == "markPrinting"
      ) {
        this.current1 = 1;
        this.setIndex = 1;
      } else if (this.$route.name == "designSet") {
        this.current1 = 1;
        this.setIndex = 2;
      } else if (
        this.$route.name == "typeRegister" ||
        this.$route.name == "menuList"
      ) {
        this.current1 = 1;
        this.setIndex = 4;
      } else if (this.$route.name == "calendar" || this.$route.name == "time") {
        this.current1 = 1;
        this.setIndex = 5;
      } else if (this.$route.name == "memberInfo") {
        this.current1 = 1;
        this.setIndex = 3;
      } else if (
        this.$route.name == "registration" ||
        this.$route.name == "personnel"
      ) {
        this.current1 = 1;
        this.setIndex = 6;
      } else if (
        this.$route.name == "deputyAdministrator" ||
        this.$route.name == "deputyLook"
      ) {
        this.current1 = 1;
        this.setIndex = 7;
      } else if (this.$route.name == "content") {
        this.current1 = 2;
        this.accountIndex = 4;
      } else if (this.$route.name == "clause") {
        this.current1 = 2;
        this.accountIndex = 5;
      } else if (this.$route.name == "edition") {
        this.current1 = 2;
        this.accountIndex = 6;
      }
    }, 200);
  },
  methods: {
    getRoomNum() {
      let that = this;
      let apply = () => {
        that.roomNum = (that.roomGroup || 0) + (that.roomDm || 0);
        // 上部ナビ「メッセージ」バッジ合算用に共有
        localStorage.setItem("roomunread", that.roomNum);
      };
      getStaffRoomUnread().then((res) => {
        if (res && res.code == 200) that.roomGroup = res.data.group_unread * 1 || 0;
        apply();
      }).catch(() => {});
      getStaffDmThreads().then((res) => {
        if (res && res.code == 200)
          that.roomDm = (res.data.threads || []).reduce((s, t) => s + (t.unread * 1 || 0), 0);
        apply();
      }).catch(() => {});
    },
    yueCuts(i) {
      this.index = i;
      if (i == 1) {
        this.$router.push({
          path: "/appointment",
        });
        return;
      } else if (i == 2) {
        this.$router.push({
          path: "/appointmentSetting",
        });
      }else{
          this.$router.push({
            path: "/outappoint",
          });
      }
    },
    cuts(i) {
      this.index = i;
      if (i == 1) {
        this.$router.push({
          path: "/message",
        });
        
        return;
      } else if (i == 2) {
        this.$router.push({
          path: "/delivery",
        });
      } else if (i == 3) {
        this.$router.push({
          path: "/setting",
        });
      } else if (i == 4) {
        this.$router.push({
          path: "/accountInfo",
        });
      }else if (i == 5) {
        this.$router.push({
          path: "/reviewSetting",
        });
      }else if (i == 6) {
        this.$router.push({
          path: "/moduleSetting",
        });
      }else if (i == 7) {
        this.$router.push({
          path: "/staffRoomSet",
        });
      }else if (i == 8) {
        this.$router.push({
          path: "/roomChat",
        });
      }
    },
    top(e) {
      this.topIndex = e;
      if (e == 0) {
        this.$router.push({
          path: "/topIndex",
        });
        return;
      } else if (e == 1) {
        this.$router.push({
          path: "/manage",
        });
      } else if (e == 2) {
        this.$router.push({
          path: "/notice",
        });
      } else if (e == 3) {
        this.$router.push({
          path: "/coupon",
        });
      }
    },
    set(e) {
      this.setIndex = e;
      this.menuShow = !this.menuShow;
      console.log(this.menuShow, "==========");
      if (e == 0) {
        this.$router.push({
          path: "/storeInfo",
        });
        return;
      } else if (e == 1) {
        this.$router.push({
          path: "/member",
        });
      } else if (e == 2) {
        this.$router.push({
          path: "/designSet",
        });
      } else if (e == 3) {
        this.$router.push({
          path: "/memberInfo",
        });
      } else if (e == 4) {
        this.$router.push({
          path: "/typeRegister",
        });
      } else if (e == 5) {
        this.$router.push({
          path: "/calendar",
        });
      } else if (e == 6) {
        this.$router.push({
          path: "/registration",
        });
      } else if (e == 7) {
        this.$router.push({
          path: "/deputyLook",
        });
      }
    },
    menuClick(i) {
      this.menuIndex = i;
      if (i == 0) {
        this.$router.push({
          path: "/typeRegister",
        });
      } else if (i == 1) {
        this.$router.push({
          path: "/menuList",
        });
      }
    },
    accountClick(i) {
      this.accountIndex = i;
      if (i == 0) {
          if(this.type == 1){
              this.$router.push({
                path: "/user",
              });
          }else{
              this.$router.push({
                path: "/fu",
              });
          }
      } else if (i == 1) {
        this.$router.push({
          path: "/utilizePlan",
        });
      } else if (i == 2) {
        this.$router.push({
          path: "/content",
        });
      } else if (i == 3) {
        this.$router.push({
          path: "/clause",
        });
      } else if (i == 4) {
        this.$router.push({
          path: "/edition",
        });
      } 
    },
  },
};
</script>

<style lang="scss" scoped>
.side {
  height: calc(100vh - 114px - 50px);
  background: #ebeff4;
}
.its {
  padding-top: 28px;
}
.item:hover{
    background-color: rgba(219, 226, 235, 0.7) !important;
}
.item {
  // height: 48px;
  // line-height: 48px;
  padding: 16px 6px;
  color: #707070;
  font-size: 16px;
  font-weight: 700;
  padding-left: 30px;
  // display: flex;
  // align-items: center;

  img {
    width: 24px;
    height: 24px;
    margin-right: 20px;
  }
}
.menu {
  padding-left: 66px;
}
.menuCss {
  color: #1a73e8;
  padding-left: 60px;
  border-left: 6px solid #1a73e8;
  background-color: rgba(219, 226, 235, 0.7);
}
.active {
  color: #1a73e8;
  padding-left: 25px;
  border-left: 6px solid #1a73e8;
  background-color: rgba(219, 226, 235, 0.7);
}
.hui {
  display: inline-block;
  vertical-align: middle;
  color: #ffffff;
  font-size: 12px;
  width: 26px;
  text-align: center;
  height: 26px;
  line-height: 26px;
  background: #d93025;
  border-radius: 50%;
  margin-left: 10px;
}
.hui1 {
  display: inline-block;
  vertical-align: middle;
  color: #ffffff;
  font-size: 12px;
  width: 26px;
  text-align: center;
  height: 26px;
  line-height: 26px;
  background: #2699fb;
  border-radius: 50%;
  margin-left: 10px;
}
</style>
