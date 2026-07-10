<template>
  <div>
    <div class="tit">ご利用明細</div>
    <div class="des">明細保有期間は12ヶ月間です</div>
    <div class="table">
      <div class="hander no-size">
        <div class="top" style="width: 194px">年月</div>
        <!-- <div class="top" style="width: 272px">プラン</div>
        <div class="top" style="width: 132px">OP</div> -->
        <div class="top" style="width: 222px">明細</div>
      </div>
      <div class="bodys no-size">
        <div
          :class="index % 2 ? 'item bg no-size' : 'item no-size'"
          v-for="(item, index) in list"
          :key="index"
        >
          <div class="con" style="width: 194px">{{ item.name}}</div>
         <!-- <div class="con" style="width: 272px">{{ item.name }}</div>
          <div class="con" style="width: 132px">
            {{ item.status == 1 ? "有" : "否" }}
          </div> -->
          <div
            class="con tiao shou"
            style="width: 222px"
            @click="url(item.download)"
          >
            {{ item.file}}.pdf
          </div>
        </div> 
      </div>
    </div>
    <div class="fixed" v-if="pdfShow">
      <div class="zhe"></div>
      <div class="pdf-box">
        <img src="../../static/tclose.png" alt="" @click="pdfShow = false" />
        <!-- //pdf展示 -->
        <div class="bos">
          <pdf
            v-for="i in numPages"
            :key="i"
            class="pdf"
            :page="i"
            :src="src"
          ></pdf>
          <!-- //页码切换 -->
          <div class="page-box">
            <!-- <el-button-group> 
              <el-button
                type="primary"
                icon="el-icon-arrow-left"
                size="mini"
                @click="prePage"
                >上一页</el-button
              >
              <el-button type="primary" size="mini" @click="nextPage"
                >下一页<i class="el-icon-arrow-right el-icon--right"></i
              ></el-button>
            </el-button-group> -->
            <!-- //页码展示 -->
            <!-- <div
              style="color: #409eff; display: flex; justify-content: flex-end"
            >
              {{ pageNum }} / {{ pageTotalNum }}
            </div> -->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import pdf from "vue-pdf";
import {getPdf} from "@/http/api.js"
export default {
  name: "",
  components: { pdf },
  data() {
    return {
      pdfShow: false,
      pdfForm: {
        url: "https://che.lanyanceshi.com/storage/pdf/2/2022-12.pdf",
      },
      currentPage: 0, // pdf文件页码
      pageCount: 0, // pdf文件总页数
      src: "", // pdf路径
      numPages: 0,
      list: [],
    };
  },
  created() {
      this.getlastYearMonth()
  },
  mounted() {
    // var url = ""; // 文件地址
    // console.log(13);
  },
  methods: {
      getlastYearMonth() {
          let that = this
          for (var i = 0; i < 12; i++) {
              var d = new Date();
              d.setDate(1);
              d.setMonth(d.getMonth() - i);
              var m = d.getMonth() + 1;
              m = m < 10 ? "0" + m : m; //在这里可以自定义输出的日期格式 
              let t = {}
              t['name'] = d.getFullYear() + "年" + m + "月"
              t['file'] = d.getFullYear() + "_" + m 
              t['download'] =  d.getFullYear() + "-" + m 
              that.list.push(t); //result.push(d.getFullYear() + "年" + m + '月'); 
          }
      },
    url(url) {
        let winOpen = window.open("", "_blank")
      getPdf(url).then((res) => {
          console.log(res)
          if(res.code == 200){
              // window.location.replace(res.data.pdf)
              winOpen.location = res.data.pdf
          }
      })
    },
    // 上一页
    prePage() {
      let page = this.pageNum;
      page = page > 1 ? page - 1 : this.pageTotalNum;
      this.pageNum = page;
    },

    // 下一页
    nextPage() {
      let page = this.pageNum;
      page = page < this.pageTotalNum ? page + 1 : 1;
      this.pageNum = page;
    },
  },
};
</script>

<style lang="scss" scoped>
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
  .pdf-box {
    width: 565px;
    height: 760px;
    position: fixed;
    top: 50%;
    left: 50%;
    background: #ffffff;
    transform: translate(-50%, -50%);
    img {
      width: 30px;
      height: 30px;
    }
    .bos {
      overflow: scroll;
      overflow-x: hidden;
      height: calc(716px - 30px);
      // &::-webkit-scrollbar {
      //   width: 6px; /*对垂直流动条有效*/
      //   height: 6px; /*对水平流动条有效*/
      // }
    }
  }
}

.tit {
  font-size: 21px;
  font-weight: 700;
  color: #1d1d1f;
  margin-bottom: 30px;
}
.des {
  color: #707070;
  font-size: 16px;
  margin-bottom: 18px;
}
.table {
  width: 410px;
  min-height: 573px;
  background: #ffffff;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .hander {
    background: #f5f5f7;
    padding-left: 10px;
    padding-right: 15px;
    height: 44px;
    display: flex;
    align-items: center;
    .top {
      color: #1d1d1f;
      font-size: 14px;
      text-align: center;
    }
  }
  .bodys {
    .item {
      padding-left: 10px;
      padding-right: 15px;
      text-align: center;
      height: 44px;
      display: flex;
      align-items: center;
      background: #ffffff;
      font-size: 14px;
    }
    .bg {
      background: #f5f5f7;
    }
    .tiao {
      color: #1a73e8;
    }
  }
}
</style>
