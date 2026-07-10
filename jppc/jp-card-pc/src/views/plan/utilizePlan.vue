<template>
  <div>
    <div class="tit">ご利用プラン</div>
    <div class="head no-size">
      <div class="u-flex">
        <div class="bg_grey">ご利用中プラン</div>
        <div class="text_right">{{info.type==2?'スタンダード':info.type == 1? 'ライト' : 'プレミアム'}}</div>
      </div>
      <!-- <div class="u-flex m-t-30">
        <div class="bg_grey">オプション</div>
        <div class="text_right">予約機能、クーポン発行</div>
      </div> -->
      <div class="u-flex u-col-top m-t-30" v-if="info.is_try == 1">
        <div class="bg_blue u-flex-center">無料お試し期間中</div>
        <div class="text_blue">
         アカウント作成から30日間は全てのプランが無料お試し期間の対象です。　顧客登録数が30人以下の場合は、無料期間終了後も、全てのプランが無料でご利用できます。
        </div>
      </div>
    </div>

    <div class="bottom no-size">
      <div class="u-flex u-row-between">
        <div class="month" v-for="(wItem, wIndex) in list" :key="wIndex" :style="wIndex == monIndex?'box-shadow: 0px 0px 10px 1px rgba(26,115,232,1);' : ''">
          <div
            class="mon_tit u-flex-center"
            :class="[
              wIndex == 0 ? 'one_bg' : wIndex == 1 ? 'two_bg' : 'three_bg',
            ]"
            style="color: #fff;"
          >
            {{ wIndex == 1 ? "おすすめ" : wIndex == 0? '': '予約機能付' }}
          </div>
          <div class="mon_bottom u-flex-col u-col-center">
            <div class="u-flex m-t-20">
              <div
                class="radius shou"
                :class="{ radiusCss: wIndex == monIndex }"
                @click="showClick(wIndex)"
              >
                <div class="blue_dot" v-if="wIndex == monIndex"></div>
              </div>
              <div
                class="rad_text"
                :style="wIndex == monIndex ? 'font-weight:bold;color:#1d1d1f' : ''"
              >
                {{ wItem.name }}
              </div>
            </div>
            <div class="money m-t-20">¥{{ addCommas(wItem.price[2] / 12)}} ~/月</div>
            <div class="mon_text m-t-20" style="padding-bottom: 10px;">
              {{ wItem.des }}
            </div>
            <div style="height: 528px;overflow-y: auto;width:100%">
                <div v-for="(item,index) in wItem.items" v-if="wItem.functions.indexOf(item.e_name) != -1" style="width: 100%;" @click.stop="tabDetail(wIndex,index)" :key="index">
                    <div class="mon_item" style="color: #1d1d1f !important;">
                      {{item.name}}<i class="el-icon-arrow-right" :style="item.checked?'transform:rotate(90deg)' : ''" ></i>
                    </div>
                    <div class="iteCon" v-if="item.checked">
                        <img :src="item.icon" class="itemIcon" alt="" />
                        <div class="itemDes" v-text="item.des"></div>
                    </div>
                </div>
            </div>
           
          </div>
        </div>
      </div>
      <div class="optional u-flex-center shou" @click="exhibitionShow = true">
        各プランの比較表
      </div>
      <div class="tip m-t-40">
        注1)クーポン、メッセージチャットの配信数は、3,000件／月を超過した場合は、3,000件毎に1,200円／月の追加料金が発生します。
      </div>
      <div class="tip m-t-10">
        注2) 予約SMS送信数は、100件を超過した場合は、100件毎に1,200円／月の追加料金が発生します。
      </div>
      <div class="button m-t-40">
        <div class="btn u-flex-center shou" @click="showSurface">保　存</div>
      </div>
      <!-- <div class="termination">解約手続き</div>
      <div class="tip m-t-10">
        解約のお手続きへ進みます。お手続きにはログインしている必要がございます。
      </div>
      <div class="termBtn shou u-flex-center" @click="termination">
        解約手続き
      </div> -->
    </div>

    <!-- 申请表弹窗 -->
    <div class="fixed" v-show="surfaceShow">
      <div class="wrap on-size">
        <div class="sur_tit">お申し込みフォーム</div>
        <div class="surface no-size">
          <div class="sur_box sur_one">
            <div class="sur_blue u-flex-center">ご利用プランの確認</div>
            <div class="sur_bg">
              <div class="u-flex">
                ご利用中のプラン：
                <div style="font-weight: bold" v-text="selectItem.name"></div>
              </div>
              <!-- <div class="u-flex m-t-20">
                オプション：
                <div style="font-weight: bold">クーポン機能</div>
              </div> -->
              <div class="text_blue" style="cursor: pointer;" @click="surfaceShow = false">プランを変更</div>
            </div>
          </div>
          <div class="sur_box sur_one m-t-30">
            <div class="sur_blue u-flex-center">お支払い周期を選択</div>
            <!-- 一年  -->
            <div class="sur_bg sur_bor u-flex">
              <div
                class="radius min shou"
                :class="{ radiusCss: cycleIndex == 0 }"
                @click="cycleClick(0)"
              >
                <div class="blue_dot bot_min" v-if="cycleIndex == 0"></div>
              </div>
              <div style="margin-left: -6px; width: 50px">1年間</div>
              <div style="width: 115px">¥{{twelvePrice / 12}} × 12ヶ月</div>
              <div style="color: #d93025">¥{{onePrice * 12 - twelvePrice}} お得！</div>
            </div>
            <!-- 半年 -->
            <div class="sur_bg sur_bor u-flex">
              <div
                class="radius min shou"
                :class="{ radiusCss: cycleIndex == 1 }"
                @click="cycleClick(1)"
              >
                <div class="blue_dot bot_min" v-if="cycleIndex == 1"></div>
              </div>
              <div style="margin-left: -6px; width: 50px">6ヶ月</div>
              <div style="width: 115px">¥{{sixPrice / 6}} × 6ヶ月</div>
              <div style="color: #d93025">¥{{onePrice * 6 - sixPrice}} お得！</div>
            </div>
            <!-- 一月 -->
           <div class="sur_bg sur_bor u-flex">
              <div
                class="radius min shou"
                :class="{ radiusCss: cycleIndex == 2 }"
                @click="cycleClick(2)"
              >
                <div class="blue_dot bot_min" v-if="cycleIndex == 2"></div>
              </div>
              <div style="margin-left: -6px; width: 50px">月額</div>
              <div style="width: 115px">{{onePrice}}</div>
            </div>
          </div>
          <div class="sur_box sur_one m-t-30">
            <div class="sur_blue u-flex-center">お支払い方法を選択</div>
            <div
              class="payment_bg"
              v-for="(item, index) in paymentList"
              :key="index"
            >
              <div class="u-flex pay_item">
                <div
                  class="radius min shou"
                  :class="{ radiusCss: payIndex == index }"
                  @click="payClick(index)"
                >
                  <div class="blue_dot bot_min" v-if="payIndex == index"></div>
                </div>
                <img :src="item.img" v-if="index != 0" />
                <div :style="index == payIndex ? 'font-weight:bold' : ''">
                  {{ item.title }}
                </div>
              </div>
              <div
                v-if="payIndex == 0 && index == 0"
                style="padding-left: 16px"
              >
                <img
                  src="@/static/index/utilize.png"
                  style="width: 210px; margin: 10px 0 40px 12px"
                />
                <div style="font-weight: bold; margin-bottom: 4px">
                  カード情報を入力して下さい
                </div>
                <div class="pay_top">カード番号</div>
                <input
                  class="pay_bottom pay_big"
                  type="text"
                  placeholder="3456 7898 1987 0001"
                  v-model="inp1"
                />
                <div class="pay_top">有効期限</div>
                <input
                  class="pay_bottom pay_big"
                  type="text"
                  placeholder="mm/yy"
                  v-model="inp2"
                />
                <div class="pay_top">CVV</div>
                <input
                  class="pay_bottom pay_big"
                  type="text"
                  placeholder="3桁or4桁の数字"
                  v-model="inp3"
                />
                <div class="pay_top">ICHIROU SUZUKI</div>
                <input
                  class="pay_bottom pay_big"
                  type="text"
                  placeholder="ICHIROU SUZUKI"
                  v-model="inp4"
                />
              </div>
            </div>
          </div>
          <div class="sur_box sur_one sur_card m-t-30 no-size">
            <div class="card_item" style="font-weight: bold">内容</div>
            <div class="card_item u-flex u-row-between">
              <div v-text="selectItem.name"></div>
              <div v-if="cycleIndex == 0">¥{{twelvePrice / 12}} × 12ヶ月</div>
              <div v-if="cycleIndex == 1">¥{{sixPrice / 6}} × 6ヶ月</div>
              <div v-if="cycleIndex == 2">¥{{onePrice}} × 1ヶ月</div>
            </div>
           <!-- <div class="card_item">
              オプション
              <div class="u-flex u-row-between" style="margin: 14px 0 0 18px">
                <div>クーポン機能</div>
                <div>¥1,000 × 6ヶ月</div>
              </div>
            </div> -->
            <div class="card_item u-flex u-row-between">
              <div>合計</div>
              <div v-if="cycleIndex == 2">¥{{onePrice}}</div>
              <div v-if="cycleIndex == 1">¥{{sixPrice}}</div>
              <div v-if="cycleIndex == 0">¥{{twelvePrice}}</div>
            </div>
          </div>
          <div class="u-flex u-flex-wrap u-row-center sur_tip">
            <div>ご契約月は利用料が</div>

            <div style="color: #d93025">無料</div>
            <div>です。</div>
            　 上記は次月からのご請求となります。
          </div>
          <div class="sur_btn u-flex-center shou" @click="sub">
            お申し込み手続き完了
          </div>
        </div>
      </div>
    </div>
    <!-- 表弹窗 -->
    <div class="fixed" v-show="exhibitionShow">
      <div class="release">
        <img
          src="@/static/tclose.png"
          class="cha shou"
          @click="exhibitionShow = false"
        />
        <div class="ex_tit no-size u-flex u-row-between">
          機能比較
          <div class="u-flex">
            <div
              class="tit_item u-flex-col u-col-center"
              v-for="(item, index) in bgList"
              :key="index"
            >
              {{ item.title }}
              <div class="bg" :style="{ background: item.bg }"></div>
            </div>
          </div>
        </div>
        <div
          class="info u-flex u-row-between"
          v-for="(item, index) in infoList"
          :key="index"
        >
          <div class="u-flex">
            {{ item.title }}
            <el-tooltip class="item" effect="dark" :content="item.des" placement="top-start">
                 <img class="tit_img" style="cursor:pointer" src="@/static/index/tan.png" />
            </el-tooltip>
          </div>
          <div class="u-flex-center">
            <div
              class="info_item u-flex-col u-row-center u-col-center"
              v-for="(i, n) in item.imgs"
              :key="n"
            >
              <img
                :src="i.img"
                class="san"
                v-if="i.tip == 'オプション'"
              />
              <img :src="i.img" class="cuo" v-if="i.tip == '非対応'" />
              <img :src="i.img" class="yuan" v-if="!i.tip" />
              <div v-if="i.tip">{{ i.tip }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- 保存 -->
    <div class="fixed" v-show="keepShow">
      <div class="keep u-flex-col u-col-center u-row-center">
        <img src="@/static/index/sign_blue.png" class="sign" />
        <div>保存しました</div>
      </div>
    </div>
  </div>
</template>

<script>
import {getPackageList,getBindCard,buyPlan,getStore} from "@/http/api.js" 
export default {
  data() {
    return {
      show: "one",
      inp1:"",
      inp2:"",
      inp3:"",
      inp4:"",
      info:{},
      monthList: [
        {
          title: "ライト",
          money: "970",
          cont: "まずはメンバーズカードをアプリ化したいという方におすすめ。",
        },
        {
          title: "スタンダード",
          money: "2,280",
          cont: "店舗運営に必要な機能を標準搭載したスタンダードプラン。",
        },
        {
          title: "プレミアム",
          money: "3,980",
          cont: "予約機能を含めたすべての機能を標準搭載したプラン。",
        },
      ],
      monIndex: 0,
      twoList: [
        "メッセージチャット",
        "お知らせ投稿",
        "営業カレンダー",
        "メニュー表示",
      ],
      threeList: [
        "メッセージチャット",
        "お知らせ投稿",
        "営業カレンダー",
        "メニュー表示",
        "クーポン発行",
        "予約機能",
      ],
      funList: [
        { title: "メッセージチャット", money: "1,000", show: false },
        { title: "お知らせ投稿", money: "500", show: false },
        { title: "営業カレンダー", money: "300", show: false },
        { title: "メニュー表示", money: "300", show: false },
        { title: "クーポン発行", money: "1.000", show: false },
      ],
      funTwoList: [
        { title: "クーポン発行", money: "1.000", show: false },
        { title: "予約機能", money: "1,900", show: false },
      ],
      botOneIndex: 0,
      botTwoIndex: 1,
      surfaceShow: false,
      cycleIndex: 0,
      cycleList: [
        { time: "1年間", money: "¥1,800 × 12ヶ月", tip: "¥4,800 お得！" },
        { time: "6ヶ月", money: "¥2,000 × 6ヶ月", tip: "¥1,200 お得！" },
        { time: "月額", money: "¥2,200/月" },
      ],
      paymentList: [
        {
          title: "クレジットカード",
        }
      ],
      payIndex: 0,
      exhibitionShow: false,
      bgList: [
        { title: "ライト", bg: "rgba(29,29,31,0.1)" },
        { title: "スタンダード", bg: "#53B1AF" },
        { title: "プレミアム", bg: "#E6BF18" },
      ],
      
      keepShow: false,
      keys:[],
      list:[],
      arrs:[],
      infoList: [
              {
                title: "メンバーズカード",
                imgs: [
                  { img: require("@/static/index/circle.png") },
                  { img: require("@/static/index/circle.png") },
                  { img: require("@/static/index/circle.png") },
                ],
                des:"スタンプカード、ポイントカードの機能が使えるメンバーズカードが作成できます。"
              },
              {
                title: "店舗情報",
                imgs: [
                  { img: require("@/static/index/circle.png") },
                  { img: require("@/static/index/circle.png") },
                  { img: require("@/static/index/circle.png") },
                ],
                des:"お店の基本情報を掲載。タップコール電話、MAPや地図アプリのナビ機能など。"
              },
              {
                title: "メッセージチャット",
                imgs: [
                 { img: require("@/static/index/circle.png") },
                  { img: require("@/static/index/circle.png") },
                  { img: require("@/static/index/circle.png") },
                ],
                des:"顧客とのメッセージチャット。テキスト、絵文字、画像、URLの送信、一斉配信やセグメント配信にも対応しています。"
              },
              {
                title: "お知らせ投稿",
                imgs: [
                  { img: require("@/static/wrong.png")},
                  { img: require("@/static/index/circle.png") },
                  { img: require("@/static/index/circle.png") },
                ],
                des:"お店の最新情報や近況情報がリアルタイムで投稿できます。　Instagram,Twitterとの連携も可能です。"
              },
              {
                title: "営業カレンダー",
                imgs: [
                  { img: require("@/static/wrong.png")},
                  { img: require("@/static/index/circle.png") },
                  { img: require("@/static/index/circle.png") },
                ],
                des:"リアルタイムの営業状況表示、営業時間、定休日、臨時休業や営業時間の変更にも対応できます。 "
              },
      
              {
                title: "メニュー表示",
                imgs: [
                  { img: require("@/static/wrong.png")},
                  { img: require("@/static/index/circle.png") },
                  { img: require("@/static/index/circle.png") },
                ],
                des:"お店のメニューを掲載できます。URLリンクにより外部ページ(メニューページ、予約ページなど)への連携もできます。"
              },
              {
                title: "クーポン発行",
                imgs: [
                  { img: require("@/static/wrong.png")},
                  { img: require("@/static/index/circle.png") },
                  { img: require("@/static/index/circle.png") },
                ],
                des:'期限、回数制限、バーコード付きクーポンの発行ができます。予約配信、一斉配信、セグメント配信にも対応。'
              },
              {
                title: "予約機能",
                imgs: [
                  { img: require("@/static/wrong.png")},
                  { img: require("@/static/wrong.png")},
                  { img: require("@/static/index/circle.png") },
                ],
                des:"メニュー毎の時間、担当者の設定、予約リマインダー通知ができます。　他社サービス(お店のホームページ等)に予約機能を表示、連携することが出来ます。"
              },
            ],
      p:999,
      c:999,
      selectItem :"",
      onePrice:"",
      sixPrice:"",
      twelvePrice:""
    };
  },
  created() {
    let that = this
    that.getList()
    that.getPlan()
  },
  
  methods: {
      addCommas(num) {
          return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      },
      getPlan(){
          console.log(1231231231)
        let that = this
        getStore().then((res) => {
            console.log("resr",res)
            if(res.code == 200){
                 console.log('1111',res.data.shop_info.vip.type * 1 - 1)
                that.info = res.data.shop_info.vip
                that.monIndex = res.data.shop_info.vip.type * 1 - 1
            }
            
        })
      },
      showSurface(){
         let that = this
         that.$router.push({
           path: "/planConfirm?index="+that.monIndex,
         });
         // console.log(that.list[that.monIndex])
         // that.selectItem = that.list[that.monIndex]
         // that.onePrice = that.selectItem.price[0]
         // that.sixPrice = that.selectItem.price[1]
         // that.twelvePrice = that.selectItem.price[2]
         // that.surfaceShow = true
         // that.getCardList()
         // console.log(that.selectItem)
         // that.$router.push({
         //   path: "/planConfirm?sel",
         // });
         // console.log(that.list)
      },
      sub(){
         let that = this 
         let data = {}
         data['type'] = that.monIndex * 1 + 1
         data['month'] = that.cycleIndex == 0? 12 : that.cycleIndex == 1? 6 : 1
         data['card_type'] = 1
         data['card_no'] = that.inp1
         data['expire_date'] = that.inp2
         data['cvv'] = that.inp3
         data['name'] = that.inp4
         buyPlan(data).then((res) => {
             console.log(res)
             if(res.code == 200){
                 if(res.data.amount > 0){
                     window.open(res.data.url)
                 }else{
                   that.keepShow = true
                   that.surfaceShow = false  
                 }
                 
             }else{
                 that.$message.error(res.message)
             }
         })
      },
      getCardList(){
          let that = this
          getBindCard().then((res) => {
              console.log(res)
              if(res.code == 200){
                  if(res.data.cards.length > 0){
                    that.inp1 = res.data.cards[0].card_no
                    that.inp2 = res.data.cards[0].expire_date
                    that.inp3 = res.data.cards[0].cvv
                    that.inp4 = res.data.cards[0].name
                  }
              }
          })
      },
      getList(){
          let that = this
        getPackageList().then((res) => {
            console.log(res)
            if(res.code == 200){
                let arr = Object.values(res.data.vip_function)
                let keys = []
                for(let i in res.data.vip_function){
                    keys.push(i)
                }
                arr.forEach((val,index) => {
                    val.e_name  = keys[index]
                    val.checked = false
                })
                res.data.vip_contract.forEach((val,index) => {
                    val.items = arr.map(v => ({...v}))
                })
                that.keys = keys
                that.list = res.data.vip_contract
                that.arrs = res.data.vip_function
                that.list.forEach((value,idx) => {
                    value.items.forEach((v,i) => {
                        // console.log(idx)
                        // console.log(i)
                        v.checked = false
                    })
                })
                console.log(that.list)
            }
        })  
      },
      tabDetail(idx,index){
        let that = this
        console.log(idx)
        console.log(index)
        console.log(that.list)
        // that.list[0].items[1].checked = true
        that.list[idx].items[index].checked = !that.list[idx].items[index].checked
        console.log(that.list[idx].items[index].checked )
        // console.log(that.list[idx].items[index].checked)
      },
    showClick(index) {
      this.monIndex = index;
    },
    monClick(index, wIndex) {
      if (wIndex == 0) {
        this.botOneIndex = index;
      } else {
        this.botTwoIndex = index;
      }
    },
    cycleClick(index) {
      this.cycleIndex = index;
    },
    payClick(index) {
      this.payIndex = index;
    },
    change() {
      this.$router.push({
        path: "/changeContract",
      });
    },
    termination() {
      this.$router.push({
        path: "/termination",
      });
    },
    keepClick() {
      this.keepShow = true;
      setTimeout(() => {
        this.keepShow = false;
      }, 500);
    },
  },
};
</script>

<style lang="scss" scoped>
.tit {
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
}
.iteCon{
    display: flex;
    font-size: 14px;
    justify-content: center;
    padding: 8px 0;
    color:#1d1d1f;
    background: #F5F5F7;
    
    .itemIcon{
        flex-shrink: 0 !important;
        width: 30px;
        height: 30px;
        margin-right: 8px;
    }
    .itemDes{
        width: 80%;
    }
}
.head {
  width: 894px;
  margin-top: 20px;
  padding: 30px;
  background: #ffffff;
  box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);
  .bg_grey {
    width: 150px;
    height: 30px;
    font-size: 16px;
    color: #1d1d1f;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(29, 29, 31, 0.1);
  }
  .text_right {
    font-weight: bold;
    margin-left: 30px;
  }
  .bg_blue {
    width: 150px;
    height: 30px;
    font-size: 16px;
    color: #ffffff;
    background: #1a73e8;
    border-radius: 10px;
  }
  .text_blue {
    width: 470px;
    font-size: 14px;
    color: #1a73e8;
    margin-left: 30px;
  }
}
.bottom {
  width: 894px;
  margin-top: 36px;
  background: #ffffff;
  padding: 42px 40px 60px 40px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .month {
    width: 258px;
    height: 728px;
    background: #ffffff;
    box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);

    .mon_bottom {
      height: 700px;
      overflow: auto;
    }

    .mon_tit {
      width: 258px;
      height: 30px;
      font-size: 14px;
    }
    .one_bg {
      background: rgba(29, 29, 31, 0.1);
    }
    .two_bg {
      color: #fff;
      background: #71d3d1;
    }
    .three_bg {
      background: #e6bf18;
    }
    .rad_text {
      font-size: 26px;
      color: #707070;
    }
    .money {
      font-size: 20px;
      color: #707070;
      font-weight:bold
    }
    .mon_text {
      width: 225px;
      font-size: 14px;
      color: #707070;
      box-sizing: border-box;
      padding-bottom: 20upx !important;
    }
    .blue_bg {
      border: none;
    }
    .mon_item {
      width: 100%;
      font-size: 14px;
      color: #1d1d1f;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 17px 18px 14px;
      border-bottom: 1px solid #d2d2d7;
      -moz-box-sizing: border-box;
      -webkit-box-sizing: border-box;
      -o-box-sizing: border-box;
      -ms-box-sizing: border-box;
      box-sizing: border-box;
      &:first-child {
        color: red;
        border-top: 1px solid #d2d2d7;
      }
      .el-icon-arrow-right {
        font-size: 16px;
        color: #707070;
        font-weight: bold;
      }
      .mon_black {
        font-size: 14px;
        color: #1d1d1f;
        font-weight: bold;
      }
      .mon_grey {
        font-size: 12px;
        color: #707070;
        margin-top: 5px;
      }
    }
    .function {
      font-size: 16px;
      font-weight: bold;
      color: #105b80;
      border: none;
      background: rgba(29, 29, 31, 0.05);
    }
  }
  .optional {
    width: 506px;
    height: 50px;
    font-size: 21px;
    color: #174a84;
    font-weight: bold;
    background: #ecf0f5;
    border-radius: 25px;
    margin: 0px auto;
    margin-top: 50px;
  }
  .tip {
    font-size: 12px;
    color: #707070;
  }
  .button {
    padding: 30px 0 0;
    border-top: 1px solid #d2d2d7;
    .btn {
      width: 200px;
      height: 50px;
      font-size: 20px;
      color: #fff;
      margin: 0 auto;
      background: #1a73e8;
      border-radius: 10px;
    }
  }
  .termination {
    font-size: 16px;
    color: #707070;
    font-weight: bold;
    padding-bottom: 12px;
    border-bottom: 1px solid #d2d2d7;
  }
  .termBtn {
    width: 335px;
    height: 56px;
    border-radius: 10px;
    font-size: 16px;
    margin: 0 auto;
    margin-top: 52px;
    color: #707070;
    background: rgba(29, 29, 31, 0.1);
    border: 1px solid #aaaaaa;
  }
}
.radius {
  width: 20px;
  height: 20px;
  margin-right: 14px;
  border-radius: 50%;
  background: #fff;
  border: 1px solid #707070;

  .blue_dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    margin: 2px auto;
    background: #1a73e8;
  }
}
.radiusCss {
  border: 1px solid #1a73e8;
}
.small {
  width: 16px;
  height: 16px;
  .bot_small {
    width: 12px;
    height: 12px;
  }
}
.min {
  width: 14px;
  height: 14px;
  .bot_min {
    width: 10px;
    height: 10px;
  }
}
// ::-webkit-scrollbar {
//   display: none;
// }
.fixed {
  position: fixed;
  top: 0%;
  left: 0%;
  width: 100vw;
  height: 100vh;
  background: rgba(29, 29, 31, 0.2);
  //   background: #F9F9F9;
  .wrap {
    width: 640px;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background:#fff;
    .sur_tit {
      font-size: 24px;
      color: #1d1d1f;
      font-weight: bold;
      margin-bottom: 23px;
    }
    .surface {
      width: 640px;
      background: #fff;
      padding: 48px 120px 21px;
      height: 60vh;
      overflow-y: scroll !important;
      scrollbar-width: none;
      -ms-overflow-style: none;
      box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);
      .sur_box {
        width: 400px;
        background: #ffffff;
        box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.2);
        .sur_blue {
          height: 40px;
          font-size: 16px;
          color: #1d1d1f;
          font-weight: bold;
          background: rgba(26, 115, 232, 0.1);
        }
        .sur_bg {
          padding: 20px;
          .text_blue {
            color: #1a73e8;
            text-align: right;
            margin-top: 14px;
          }
        }
        .sur_bor {
          border-bottom: 1px solid #d2d2d7;
          &:last-child {
            border: none;
          }
        }
        .payment_bg {
          padding: 24px;
          border-bottom: 1px solid #d2d2d7;
          &:last-child {
            border: none;
          }
          .pay_item {
            font-size: 16px;
            color: #1d1d1f;

            img {
              width: 30px;
              margin-right: 10px;
            }
          }
          .pay_top {
            font-size: 14px;
            color: #707070;
            margin-top: 20px;
          }
          .pay_bottom {
            width: 329px;
            height: 40px;
            background: #fff;
            border-radius: 10px;
            padding: 14px;
            margin-top: 10px;
            border: 1px solid #aaa;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            -o-box-sizing: border-box;
            -ms-box-sizing: border-box;
            box-sizing: border-box;
          }
          input::-webkit-input-placeholder {
            /* WebKit browsers */
            font-size: 16px;
            color: #d7d7d7;
          }
          input:-moz-placeholder {
            /* Mozilla Firefox 4 to 18 */
            font-size: 16px;
            color: #d7d7d7;
          }
          input::-moz-placeholder {
            /* Mozilla Firefox 19+ */
            font-size: 16px;
            color: #d7d7d7;
          }
          input::-ms-input-placeholder {
            /* Internet Explorer 10+ */
            font-size: 16px;
            color: #d7d7d7;
          }
        }
      }
      .sur_one {
        font-size: 14px;
        color: #1d1d1f;
      }
      .sur_card {
        padding: 10px 23px;
        .card_item {
          padding: 14px 0;
          border-bottom: 1px solid #d2d2d7;
          &:last-child {
            border: none;
          }
        }
      }
    }
    .sur_tip {
      font-size: 16px;
      color: #0a4b6d;
      text-align: center;
      margin-top: 50px;
    }
    .sur_btn {
      width: 295px;
      height: 48px;
      font-size: 16px;
      color: #fff;
      margin: 50px auto;
      background: rgba(26, 115, 232, 0.6);
      border-radius: 28px;
    }
  }
  .release {
    width: 612px;
    background: #ffffff;
    box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);
    position: fixed;
    top: 50%;
    left: 50%;
    font-size: 16px;
    color: #2699fb;
    transform: translate(-50%, -50%);
    .cha {
      position: absolute;
      top: 8px;
      left: 8px;
      width: 30px;
      height: 30px;
    }
    .ex_tit {
      width: 100%;
      font-size: 16px;
      color: #1d1d1f;
      font-weight: bold;
      margin-top: 60px;
      padding: 0 0 5px 20px;
      .tit_item {
        .bg {
          width: 140px;
          height: 14px;
          margin-top: 7px;
          background: rgba(29, 29, 31, 0.1);
        }
      }
    }
    .info {
      height: 56px;
      font-size: 14px;
      color: rgba(29, 29, 31, 0.9);
      padding-left: 20px;
      border-top: 1px solid #d2d2d7;

      &:nth-child(n) {
        background: rgba(143, 168, 248, 0.1);
      }
      &:nth-child(2n) {
        background: #ffffff;
      }
      .tit_img {
        width: 16px;
        height: 16px;
        margin-left: 14px;
      }
      .info_item {
        width: 140px;
        height: 56px;
        font-size: 5px;
        color: #707070;
        .yuan {
          width: 18px;
          height: 19px;
        }
        .san {
          width: 17px;
          height: 17px;
          margin-bottom: 3px;

        }
        .cuo {
          width: 12px;
          height: 12px;
          margin-top:6px;
          margin-bottom: 5px;

        }
      }
    }
  }
  .keep {
    width: 160px;
    height: 146px;
    background: #fff;
    border-radius: 10px;
    position: fixed;
    top: 50%;
    left: 50%;
    font-size: 16px;
    color: #2699fb;
    transform: translate(-50%, -50%);
    box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
    .sign {
      width: 50px;
      height: 50px;
      margin-bottom: 20px;
    }
  }
}
</style>
