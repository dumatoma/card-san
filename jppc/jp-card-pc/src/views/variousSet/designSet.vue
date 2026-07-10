<template>
  <div>
    <div class="wrap u-flex u-col-top">
      <div class="half">
        <div class="title">デザイン設定</div>
        <div class="setColor">
          <div class="setTit no-size u-flex u-row-between">
            各パーツの色を設定してください。
            <div class="shou" style="color: #d93025;" @click="retry">元に戻す</div>
          </div>
          
          <div class="setItem shou no-size u-flex">
            <div class="setText">ヘッダー</div>
            <div class="yuan" :style="`background:${color1}`">
                <el-color-picker
                  v-model="color1"
                  show-alpha
                  :predefine="predefineColors"
                  style="position: absolute;left: 0;top:0;z-index: 9 !important;border: none !important;"
                  @active-change="colorChange"
                  >
                </el-color-picker>
            </div>
          </div>
          
          
          <div class="setItem shou no-size u-flex">
            <div class="setText">ヘッダーテキスト</div>
            <div class="yuan" :style="`background:${color2}`">
                <el-color-picker
                  v-model="color2"
                  show-alpha
                  :predefine="predefineColors"
                  style="position: absolute;left: 0;top:0;z-index: 9 !important;border: none !important;"
                  >
                </el-color-picker>
            </div>
          </div>
          
          
          <div class="setItem shou no-size u-flex">
            <div class="setText">フッター</div>
            <div class="yuan" :style="`background:${color3}`">
                <el-color-picker
                  v-model="color3"
                  show-alpha
                  :predefine="predefineColors"
                  style="position: absolute;left: 0;top:0;z-index: 9 !important;border: none !important;"
                  >
                </el-color-picker>
            </div>
          </div>
          
          
          <div class="setItem shou no-size u-flex">
            <div class="setText">フッターアイコン</div>
            <div class="yuan" :style="`background:${color4}`">
                <el-color-picker
                  v-model="color4"
                  show-alpha
                  :predefine="predefineColors"
                  style="position: absolute;left: 0;top:0;z-index: 9 !important;border: none !important;"
                  @change="handleChangeColor"
                  >
                </el-color-picker>
            </div>
          </div>
          
        </div>
        <div class="setIcon">
          <div class="iconTit u-flex no-size">
            フッターに表示するアイコンにチェックを入れて下さい。
            使わない機能のアイコンはチェックを外してください。
          </div>
          <div
            class="u-flex u-row-between u-flex-wrap"
            style="padding: 0px 31px 30px 30px"
          >
            <div
              class="iconWrap u-flex-col u-col-center"
              v-for="(item, index) in iconList"
              :key="index"
            >
              <div class="iconItem u-flex-col u-row-center u-col-center">
                <div :style="`backgroundImage: url(${svgData1})`" class="icon1" v-if="index == 0"></div>
                <div :style="`backgroundImage: url(${svgData2})`" class="icon1" v-if="index == 1"></div>
                <div :style="`backgroundImage: url(${svgData3})`" class="icon1" v-if="index == 2"></div>
                <div :style="`backgroundImage: url(${svgData4})`" class="icon1" v-if="index == 3"></div>
                <div :style="`backgroundImage: url(${svgData5})`" class="icon1" v-if="index == 4"></div>
                <div :style="`backgroundImage: url(${svgData6})`" class="icon1" v-if="index == 5"></div>
                <div class="icon_text">{{ item.title }}</div>
              </div>
              <div class="iconBottom" v-if="index == 0 || index == 1">必須</div>
              <div
                v-else
                class="radius shou"
                @click="iconClick(item, index)"
                style="margin-top: 16px"
                :class="item.show? 'radiusCss' : '' "
              >
                <div class="blue_dot" v-if="item.show"></div>
              </div>
            </div>
          </div>
        </div>
        <div class="btn u-flex-center shou" @click="submit">設定保存</div>
      </div>
      <div class="half">
        <div class="phone">
          <div class="small">
            <div class="top u-flex u-row-between u-col-bottom">
              9:41
              <div class="rectangle"></div>
              <div class="u-flex u-row-between" style="width:50px;padding-right:5px;">
                
              <img src="@/static/index/signal.png" alt="">
              <img src="@/static/index/WiFi.png" alt="">
              <img src="@/static/index/power.png" >
              </div>
            </div>
            <div class="phTit u-flex u-row-center" :style="`background:${color1}`">
              <i
                class="el-icon-arrow-left"
                style="
                  position: absolute;
                  top: 16px;
                  left: 15px;
                  font-weight: bold;
                "
                :style="`color:${color2}`"
              ></i
              > <span :style="`color:${color2}`">{{info.shop_name}}</span> 
            </div>
            <div
              class="bottom no-size u-flex"
              :style="`background:${color3}`"
            >
              <div
                class="bot_item"
                v-for="(item, index) in botList"
                :key="index"
              >
                <div :style="`backgroundImage: url(${svgData11})`" class="icon1" v-if="index == 0"></div>
                <div :style="`backgroundImage: url(${svgData12})`" class="icon1" v-if="index == 1"></div>
                <div :style="`backgroundImage: url(${svgData13})`" class="icon1" v-if="item.name == 'message'"></div>
                <div :style="`backgroundImage: url(${svgData14})`" class="icon1" v-if="item.name == 'menu'"></div>
                <div :style="`backgroundImage: url(${svgData15})`" class="icon1" v-if="item.name == 'notice'"></div>
                <div :style="`backgroundImage: url(${svgData16})`" class="icon1" v-if="item.name == 'coupon'"></div>
                <div class="bot_tetx" :style='`color:${color4}`'>{{item.title}}</div>
               <!-- <div class="bot_tetx" :style='`color:${color4}`' v-if="index == 1">店舗情報</div>
                <div class="bot_tetx" :style='`color:${color4}`' v-if="index == 2">メッセージ</div>
                <div class="bot_tetx" :style='`color:${color4}`' v-if="index == 3">メニュー</div>
                <div class="bot_tetx" :style='`color:${color4}`' v-if="index == 4">お知らせ</div>
                <div class="bot_tetx" :style='`color:${color4}`' v-if="index == 5">クーポン</div> -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- 保存弹窗 -->
    <div class="fixed" v-show="keepShow">
      <div class="zhe"></div>
      <div class="sus-box">
        <img src="@/static/success.png" alt="" />
        <div class="keep">保存しました</div>
      </div>
    </div>
  </div>
</template>

<script>
import {getStore,noticeSetting} from "@/http/api.js"
import svg1 from '../../static/svgData/svgjs/s1.js'
import svg2 from '../../static/svgData/svgjs/s2.js'
import svg3 from '../../static/svgData/svgjs/s3.js'
import svg4 from '../../static/svgData/svgjs/s4.js'
import svg5 from '../../static/svgData/svgjs/s5.js'
import svg6 from '../../static/svgData/svgjs/s6.js'
export default {
  data() {
    return {
        svgData1:'"' + svg1 + '"',
        svgData2:'"' + svg2 + '"',
        svgData3:'"' + svg3 + '"',
        svgData4:'"' + svg4 + '"',
        svgData5:'"' + svg5 + '"',
        svgData6:'"' + svg6 + '"',
        
        svgData11:'"' + svg1 + '"',
        svgData12:'"' + svg2 + '"',
        svgData13:'"' + svg3 + '"',
        svgData14:'"' + svg4 + '"',
        svgData15:'"' + svg5 + '"',
        svgData16:'"' + svg6 + '"',
        color1:"#ffffff",
        color2:"#1d1d1f",
        color3:"#f5f5f7",
        color4:"#707070",
      predefineColors: [],
      info:{},
      iconList: [
        {
          img: require("../../static/s1.svg"),
          title: "My Card",
          show: false,
          name:"my_card"
        },
        {
          img: require("../../static/s2.svg"),
          title: "店舗情報",
          show: false,
          name:"shop_info"
        },
        {
          img: require("../../static/menu4.svg"),
          title: "メッセージ",
          show: false,
          name:"message"
        },
        {
          img: require("../../static/icon7.svg"),
          title: "メニュー",
          show: false,
          name:"menu"
        },
        {
          img: require("../../static/s5.svg"),
          title: "お知らせ",
          show: false,
          name:"notice"
        },
        {
          img: require("../../static/s6.svg"),
          title: "クーポン",
          show: false,
          name:"coupon"
        },
      ],
      iconTwo: true,
      iconThree: true,
      iconFour: false,
      iconFive: false,
      subShow: false,
      botList: [
        {
          img: require("../../static/s1.svg"),
          title: "My Card",
          show: true,
          name:"my_card"
        },
        {
          img: require("../../static/s2.svg"),
          title: "店舗情報",
          show: true,
          name:"shop_info"
        }
      ],
      newList: [
        {
          img: require("../../static/index/message.png"),
          title: "メッセージ",
        },
        {
          img: require("../../static/index/menu.png"),
          title: "メニュー",
        },
      ],
      keepShow: false,
    };
  },
  created() {
    let that = this
    that.getInfo()
    console.log(that.hexToRgb("#FEECD4"))
  },
  watch:{
       setList: {
              handler(newVal, oldVal) {
                  console.log(newVal, oldVal)
              },
              deep: true,
        }
  },
  methods: {
      colorChange(e){
          console.log(e)
      },
      hexToRgb(hex) {
        // Remove "#" symbol from the beginning of the string
        hex = hex.replace("#", "");
        // Convert the hex value to an integer
        var intVal = parseInt(hex, 16);
        // Extract the red, green, and blue components from the integer value
        var red = (intVal >> 16) & 255;
        var green = (intVal >> 8) & 255;
        var blue = intVal & 255;
        return `rgba(${red},${green},${blue},1)`
      },
      changeColor(url,color){
       	let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#", "%23"));//转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
       	return res;
       },
      getInfo(){
        let that = this
        getStore().then((res) => {
            console.log("res",res)
            if(res.code == 200){
                let colors = []
                that.info = res.data.shop_info
                that.color1 = res.data.shop_info.page_header_color
                that.color2 = res.data.shop_info.page_header_font_color
                that.color3 = res.data.shop_info.page_footer_color
                that.color4 = res.data.shop_info.page_footer_icon_color
                res.data.shop_info.color.forEach((v,i) => {
                    if(v.color.includes("#")){
                        colors.push(that.hexToRgb(v.color))
                    }else{
                        colors.push(v.color)
                    }  
                })
                that.predefineColors = colors
                console.log(that.predefineColors)
                res.data.shop_info.page_footer_icon.forEach((item,index) => {
                    let temp = {}
                    if(item == 'notice'){
                        temp.img = require("../../static/s5.svg")
                        temp.title = 'お知らせ'
                        temp.name = item
                    }else if(item == 'message'){
                        temp.img = require("../../static/menu4.svg")
                        temp.title = 'メッセージ'
                        temp.name = item
                    }else if(item == 'coupon'){
                        temp.img = require("../../static/s6.svg")
                        temp.title = 'クーポン'
                        temp.name = item
                    }else{
                        temp.img = require("../../static/icon7.svg")
                        temp.title = 'メニュー'
                        temp.name = item
                    }
                    that.botList.push(temp)
                    this.svgData11 = this.changeColor(this.svgData1,res.data.shop_info.page_footer_icon_color);
                    this.svgData12 = this.changeColor(this.svgData2,res.data.shop_info.page_footer_icon_color);
                    this.svgData13 = this.changeColor(this.svgData3,res.data.shop_info.page_footer_icon_color);
                    this.svgData14 = this.changeColor(this.svgData4,res.data.shop_info.page_footer_icon_color);
                    this.svgData15 = this.changeColor(this.svgData5,res.data.shop_info.page_footer_icon_color);
                    this.svgData16 = this.changeColor(this.svgData6,res.data.shop_info.page_footer_icon_color);
                    that.iconList.forEach((ite,idx) => {
                        if(ite.name == item){
                            console.log(ite)
                            ite.show = true
                        }
                    }) 
                })
            }
        })
      },
    iconClick(ite, index) {
        let that = this
        if(ite.show == true){
            that.botList.forEach((item,index) => {
                if(item.name == ite.name){
                    that.botList.splice(index,1)
                }
            })
            that.iconList[index].show = false
        }else{
            that.iconList[index].show = true
            let data = {}
            data.name = ite.name
            data.title = ite.name == 'notice'? 'お知らせ':ite.name == 'message'? 'メッセージ':ite.name == 'coupon'?'クーポン':'メニュー'
            data.img = ite.name == 'notice'? require("../../static/s5.svg"):ite.name == 'message'? require("../../static/menu4.svg"):ite.name == 'coupon'?require("../../static/s6.svg"):require("../../static/icon7.svg")
            data.show = true
            that.botList.push(data)
        }
    },
    submit() {
        let that = this
        let data = {}
        let temp = []
        data['type'] = 'design'
        data['page_header_color'] = that.color1
        data['page_header_font_color'] = that.color2
        data['page_footer_color'] = that.color3
        data['page_footer_icon_color'] = that.color4
        
        that.iconList.forEach((item,index) => {
            if(item.show == true){
                temp.push(item.name)
            }
        })
        data['page_footer_icon'] = temp
        console.log(data)
        noticeSetting(data).then((res) => {
            if(res.code == 200){
                that.keepShow = true;
                setTimeout(() => {
                  that.keepShow = false;
                }, 1500);
            }else{
                that.$message.error(res.message)
            }
        })
      
    },
    retry(){
      let that = this
      that.color1 = "#ffffff"
      that.color2 = "#1D1D1F"
      that.color3 = "#1D1D1F"
      that.color4 = "#707070"
        this.svgData11 = this.changeColor(this.svgData1,"#707070");
        this.svgData12 = this.changeColor(this.svgData2,"#707070");
        this.svgData13 = this.changeColor(this.svgData3,"#707070");
        this.svgData14 = this.changeColor(this.svgData4,"#707070");
        this.svgData15 = this.changeColor(this.svgData5,"#707070");
        this.svgData16 = this.changeColor(this.svgData6,"#707070");
    },
    handleChangeColor(e) {
        console.log(e)
            this.svgData11 = this.changeColor(this.svgData1,e);
            this.svgData12 = this.changeColor(this.svgData2,e);
            this.svgData13 = this.changeColor(this.svgData3,e);
            this.svgData14 = this.changeColor(this.svgData4,e);
            this.svgData15 = this.changeColor(this.svgData5,e);
            this.svgData16 = this.changeColor(this.svgData6,e);
    },
  },
};
</script>

<style lang="scss" scoped>
.icon1{
    width: 30px;
    height: 30px;
    background-size: 100% 100%;
    display: inline-block
}
.yuan{
    width: 40px;
    height: 40px;
    border-radius: 50%;
    box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
    position: relative;
}
.menu3 {
  transform: translate(-10000px);
  filter: drop-shadow(10000px 0 0 #aaa);
}
.menu4 {
  transform: translate(-10000px);
  filter: drop-shadow(10000px 0 0 #1d1d1f);
}
.menu5 {
  transform: translate(-10000px);
  filter: drop-shadow(10000px 0 0 #d93025);
}
::v-deep .el-color-picker__icon{
    display: none !important;
}

::v-deep .el-color-picker__color{
    border: none !important;
    border-radius: 50%;
    visibility: hidden;
}
::v-deep .el-color-picker__trigger{
    // visibility: hidden;
    z-index: 99 !important;
    border: none !important;
}
.wrap {
  margin-top: -26px;
  .half {
    width: 500px;
    &:nth-child(1) {
      border-right: 1px solid rgba(0, 0, 0, 0.16);
      // box-shadow: 0px 3px 6px 1px rgba(0,0,0,0.16);
    }

    .title {
      font-size: 21px;
      color: #1d1d1f;
      font-weight: bold;
      margin-top: 30px;
    }
    .setColor {
      width: 438px;
      margin-top: 23px;
      box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
      .setTit {
        width: 100%;
        height: 42px;
        color: #707070;
        font-size: 14px;
        padding: 0 14px;
        background: rgba(29, 29, 31, 0.1);
      }
      .setItem {
        width: 100%;
        height: 54px;
        padding: 0 31px;
        .setText {
          width: 160px;
        }

        ::v-deep .m-colorPicker .colorBtn {
          width: 30px;
          height: 30px;
          border-radius: 50%;
          box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
        }
        ::v-deep .m-colorPicker .box {
          left: 50px;
          margin-top: -33px;
          z-index: 9;
        }
      }
    }
    .setIcon {
      width: 439px;
      margin-top: 41px;

      box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
      .iconTit {
        height: 63px;
        font-size: 14px;
        color: #707070;
        padding: 0 40px 0 20px;
        background: rgba(29, 29, 31, 0.1);
      }
      .iconWrap {
        margin-right: 40px;
        &:nth-child(3) {
          margin-right: 0px;
        }
        &:nth-child(6) {
          margin-right: 0px;
        }
        .iconItem {
          width: 70px;
          height: 70px;
          margin-top: 40px;
          background: #ffffff;
          box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);
          border-radius: 10px;
        }
        &:nth-child(1) {
          img {
            width: 40px;
          }
        }
        &:nth-child(2) {
          img {
            width: 20px;
          }
        }
        &:nth-child(3) {
          img {
            width: 35px;
          }
        }
        &:nth-child(4) {
          img {
            width: 36px;
          }
        }
        &:nth-child(5) {
          img {
            width: 32px;
          }
        }
        &:nth-child(6) {
          img {
            width: 35px;
          }
        }
        .icon_text {
          font-size: 11px;
          color: #1d1d1f;
          margin-top: 5px;
        }
        &:nth-child(6) {
          .icon_text {
            // color: #d93025;
          }
        }
        .iconBottom {
          font-size: 16px;
          color: #1d1d1f;
          margin-top: 16px;
        }
        .radius {
          width: 16px;
          height: 16px;
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
        .radiusCss {
          border: 1px solid #1a73e8;
        }
      }
    }
    .btn {
      width: 200px;
      height: 50px;
      color: #fff;
      font-size: 20px;
      margin: 30px 0px 0 120px;
      border-radius: 10px;
      background: #1a73e8;
    }
    .phone {
      width: 300px;
      height: 649px;
      margin: 22px auto;
      background: #000;
      box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
      border-radius: 35px;
      .small {
        width: 100%;
        height: 100%;
        border-radius: 30px;
        background: #fff;
        position: relative;
        .top {
            height: 30px;

          padding: 0 5px 0 20px;
          font-size: 15px;
          position: relative;
          .rectangle {
            width: 180px;
            height: 30px;
            background: #000;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            margin: 0 auto;
            border-radius: 0 0 50px 50px;
          }
          img{
            width:15px;
          }
        }
        .phTit {
          height: 45px;
          font-weight: bold;
          position: relative;
          margin-top: 10px;
          border-bottom: 1px solid #e4e4e7;
        }
        .bottom {
          width: 100%;
          bottom: 0;
          height: 76px;
          color: #707070;
          position: absolute;
          border-radius: 0 0 30px 30px;
          padding: 20px 15px 30px 15px;
          border-top: 1px solid rgba(0, 0, 0, 0.3);
          display:flex;
          align-items: center;
          
          .bot_item {
              flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            img {
              width: 27px;
            }
            .bot_tetx {
              font-size: 7px !important;
              margin-top: 5px;
            }
          }
          
          .bot_item:first-child{
             img {
               width: 30px !important;
             } 
          }
          
          .bot_item:nth-child(2){
            img {
              width: 16px !important;
            }  
          }
        }
      }
    }
  }
}
.fixed {
  position: fixed;
  top: 0%;
  left: 0%;
  width: 100vw;
  height: 100vh;
  background: rgba(29, 29, 31, 0.2);
  .zhe {
    position: absolute;
    width: 100vw;
    height: 100vh;
    background: rgba(29, 29, 31, 0.2);
  }
  .sus-box {
    width: 160px;
    height: 146px;
    background: #ffffff;
    border-radius: 10px 10px 10px 10px;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    img {
      margin-top: 30px;
      width: 50px;
      height: 50px;
    }
    .keep {
      margin-top: 20px;
      font-size: 16px;
      color: #2699fb;
    }
  }
}
</style>
