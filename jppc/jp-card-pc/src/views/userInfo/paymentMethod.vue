<template>
  <div>
    <div v-if="selectShow == 1">
      <div class="tit">お支払い方法</div>

      <div class="pay-add no-size">
        <div class="bank-card-box" v-if="bankList.length > 0">
          <div class="bank-item" v-for="(item, index) in bankList" :key="index">
            <div class="bank-img">
              <img src="https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fwww.javy-lawyers.com%2FUploads%2Fimages%2Findustry%2F9.jpg&refer=http%3A%2F%2Fwww.javy-lawyers.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1671688835&t=8f0e8dd6f49006b3a0637ac22052b7b2" alt="" />
            </div>
            <div class="midd">
              <div class="title">{{ item.card_no }}</div>
              <div class="txt">{{ item.name }}</div>
              <div class="des">{{ item.cvv }}</div>
              <div class="time">有効期限{{ item.expire_date }}</div>
            </div>
            <div class="right">
              <div class="btns shou" @click="edit(item.id)">編集</div>
              <div class="btns red shou" @click="del(item.id)">削除</div>
            </div>
          </div>
        </div>
        <div class="btn shou" @click="selectShow = 2">
          ＋ お支払い方法を追加
        </div>
      </div>
    </div>
    <div v-if="selectShow == 2">
      <div class="tit">お支払い方法を選択してください</div>
      <div class="select-box">
        <div class="select-item no-size" @click="pays(0)">
          <div class="lef">クレジットカード</div>
          <i class="el-icon-arrow-right" color="#707070"></i>
        </div>
        <!-- <div class="select-item no-size">
          <div class="lef">
            <img src="../../static/apple-pay.png" alt="" class="apple" />
            Apple Pay
          </div>
          <i class="el-icon-arrow-right" color="#707070"></i>
        </div>
        <div class="select-item no-size">
          <div class="lef">
            <img src="../../static/googlePay.png" alt="" class="google" />
            Google Pay
          </div>
          <i class="el-icon-arrow-right" color="#707070"></i>
        </div> -->
      </div>
    </div>
    <div v-if="selectShow == 3">
      <div class="tit">クレジットカードを追加</div>
      <div class="add-box">
        <div class="add-tit">クレジットカード情報を入力して下さい</div>
        <div class="add-form no-size">
          <div class="add-item">
            <div class="add-name">カード番号</div>
            <div class="add-inp">
              <input
                type="number"
                placeholder="3456 7898 1987 0001"
                class="no-size"
                v-model="cardNum"
              />
            </div>
          </div>
          <div class="add-item">
            <div class="add-name">有効期限</div>
            <div class="add-inp">
              <input
                type="text"
                maxlength="5"
                placeholder="mm/yy"
                class="no-size date"
                v-model="yxq"
              />
              <!-- @input="dateInput" -->
            </div>
          </div>
          <div class="add-item">
            <div class="add-name">CVV</div>
            <div class="add-inp">
              <input
                type="text"
                placeholder="3桁or4桁の数字"
                class="no-size cvv"
                v-model="cvv"
              />
            </div>
          </div>
          <div class="add-item">
            <div class="add-name">カード名義人</div>
            <div class="add-inp">
              <input type="text" placeholder="ICHIROU SUZUKI" class="no-size" v-model="username" />
            </div>
          </div>
          <div class="add-btn shou" @click="add()">カードを追加</div>
        </div>
      </div>
    </div>
    <div v-if="selectShow == 4">
      <div class="tit">カード情報を編集</div>
      <div class="add-box">
        <div class="add-tit">クレジットカード情報を入力して下さい</div>
        <div class="add-form no-size">
          <div class="add-item">
            <div class="add-name">カード番号</div>
            <div class="add-inp">
              <input
                type="number"
                placeholder="3456 7898 1987 0001"
                class="no-size"
                v-model="cardNum"
              />
            </div>
          </div>
          <div class="add-item">
            <div class="add-name">有効期限</div>
            <div class="add-inp">
              <input
                type="text"
                maxlength="5"
                placeholder="mm/yy"
                class="no-size date"
                v-model="yxq"
              />
              <!-- @input="dateInput" -->
            </div>
          </div>
          <div class="add-item">
            <div class="add-name">CVV</div>
            <div class="add-inp">
              <input
                type="text"
                placeholder="3桁or4桁の数字"
                class="no-size cvv"
                v-model="cvv"
              />
            </div>
          </div>
          <div class="add-item">
            <div class="add-name">カード名義人</div>
            <div class="add-inp">
              <input type="text" placeholder="ICHIROU SUZUKI" class="no-size" v-model="username" />
            </div>
          </div>
          <div class="flex">
            <div class="edit-btn shou" @click="selectShow = 1">戻　る</div>
            <div class="edit-btn kep shou" @click="editmethod">保　存</div>
          </div>
        </div>
      </div>
    </div>
    <div class="fixed" v-show="addShow">
      <div class="zhe"></div>
      <div class="sus-box">
        <img src="../../static/success-hui.png" alt="" />
        <div class="keep">カードを追加しました</div>
      </div>
    </div>
    <div class="fixed" v-show="delShow">
      <div class="zhe"></div>
      <div class="sus-box" style="height: 146px">
        <img src="../../static/success-hui.png" alt="" />
        <div class="keep">削除しました</div>
      </div>
    </div>
  </div>
</template>

<script>
import {getPaymethod,addPaymethod,deletePaymethod,getPayDetail,editPaymethod} from "@/http/api.js"
export default {
  name: "",
  components: {},
  data() {
    return {
      username:"",
      cvv:"",
      yxq:"",
      id:"",
      cardNum:"",
      addShow: false,
      delShow: false,
      date: "",
      method:"",
      selectShow: 1,
      type:"",
      bankList: [
        
      ],
    };
  },
  created() {
      this.getList()
      let id = this.$route.query.id
      if(id){
          this.edit(id)
      }
  },
  methods: {
    pays(e){
      let that = this
      that.selectShow = 3
      that.type = "add"
      that.method = e + 1
    },
    edit(id) {
      let that = this
      that.id = id
      getPayDetail(id).then((res) => {
          console.log(res)
          if(res.code == 200){
              that.cardNum = res.data.card.card_no
              that.cvv = res.data.card.cvv
              that.yxq = res.data.card.expire_date
              that.username = res.data.card.name
          }else{
              that.$message.error(res.message)
          }
          
      })
      this.selectShow = 4;
    },
    getList(){
      let that = this
      getPaymethod().then((res) => {
          console.log(res)
          if(res.code == 200){
              that.bankList = res.data.cards
          }
      })
    },
    editmethod(id){
       let that = this
        let data = {}
        data['_method'] = "put"
        data['card_no'] = that.cardNum
        data['expire_date'] = that.yxq
        data['cvv'] = that.cvv
        data['name'] = that.username
        data["id"] = that.id
        editPaymethod(data).then((res) => {
            if(res.code == 200){
                that.addShow = true;
                setTimeout(() => {
                  that.addShow = false;
                  that.selectShow = 1
                  that.getList()
                }, 1500);
            }else{
                that.$message.error(res.message)
            }
        })
    },
    del(id) {
        let that = this
        deletePaymethod(id).then((res) => {
            if(res.code == 200){
                that.delShow = true;
                setTimeout(() => {
                  that.delShow = false;
                  that.getList()
                }, 1000);
            }else that.$message.error(res.message)
        })
     
    },
    add() {
        let that = this
        let data = {}
        data['type'] = that.method
        data['card_no'] = that.cardNum
        data['expire_date'] = that.yxq
        data['cvv'] = that.cvv
        data['name'] = that.username
        addPaymethod(data).then((res) => {
            if(res.code == 200){
                that.addShow = true;
                setTimeout(() => {
                  that.addShow = false;
                  that.selectShow = 1
                  that.getList()
                }, 1500);
            }else{
                that.$message.error(res.message)
            }
        })
      
    },
    // dateInput() {
    //   if (this.date.length == 2) {
    //     this.date = this.date + "/";
    //   }
    //   console.log(this.date);
    // },
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
  .sus-box {
    width: 160px;
    height: 170px;
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
      width: 100px;
      margin: 0 auto;
      margin-top: 20px;
      font-size: 16px;
      color: #707070;
    }
  }
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none !important;
}
input[type="number"] {
  -moz-appearance: textfield;
}
.tit {
  font-size: 21px;
  font-weight: 700;
  color: #1d1d1f;
  margin-bottom: 24px;
}
.pay-add {
  width: 820px;
  min-height: 552px;
  background: #ffffff;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  padding-top: 30px;
  .btn {
    margin: 0 auto;

    width: 247px;
    height: 44px;
    line-height: 44px;
    background: #1a73e8;
    border-radius: 22px 22px 22px 22px;
    color: #ffffff;
    font-size: 20px;
    text-align: center;
  }
}
.bank-card-box {
  // display: flex;
  .bank-item {
    padding: 16px 20px 16px 10px;
    // width: 740px;
    margin-left: 40px;
    margin-right: 40px;
    background: #ffffff;
    box-shadow: 0px 0px 6px 1px rgba(29, 29, 31, 0.3);
    border-radius: 10px 10px 10px 10px;
    display: flex;
    align-items: center;
    margin-bottom: 30px;
    .bank-img {
      width: 100px;
      height: 100px;
      background: #ffffff;
      box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
      margin-right: 16px;
      img {
        width: 100%;
        height: 69px;
        border-radius: 3px;
        margin-top: 16px;
      }
    }
    .midd {
      flex: 1;
      .title {
        font-size: 16px;
        color: #1d1d1f;
        margin-bottom: 10px;
      }
      .txt {
        font-size: 14px;
        color: #1d1d1f;
        margin-bottom: 10px;
      }
      .des {
        font-size: 14px;
        color: #1d1d1f;
        margin-bottom: 10px;
      }
      .time {
        font-size: 14px;
        color: #1d1d1f;
        margin-bottom: 10px;
      }
    }
    .right {
      display: flex;
      justify-content: flex-end;
      flex: 1;
      .btns {
        width: 64px;
        height: 32px;
        line-height: 32px;
        text-align: center;
        font-size: 12px;
        color: #707070;
        background: #ffffff;
        opacity: 1;
        border: 1px solid #707070;
        border-radius: 10px;
      }
      .red {
        margin-left: 10px;
        color: #d93025;
        border: 1px solid #d93025;
      }
    }
  }
}
.select-box {
  background: #ffffff;
  box-shadow: 0px 0px 6px 1px rgba(29, 29, 31, 0.3);
  border-top: 1px solid #d2d2d7;
  border-bottom: 1px solid #d2d2d7;
  width: 422px;
  .select-item {
    height: 80px;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #d2d2d7;
    .lef {
      .apple {
        width: 47px;
        height: 30px;
        vertical-align: middle;
        margin-right: 24px;
      }
      .google {
        width: 62px;
        height: 30px;
        vertical-align: middle;
        margin-right: 28px;
      }
      color: #1d1d1f;
      font-size: 16px;
    }
  }
}
.add-box {
  width: 409px;
  height: 524px;
  background: #ffffff;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .add-tit {
    width: 100%;
    height: 56px;
    line-height: 56px;
    text-align: center;
    color: #1d1d1f;
    font-size: 16px;
    background: #f5f5f7;
    border-top-left-radius: 3px;
    border-top-right-radius: 3px;
    font-weight: 600;
  }
  .add-form {
    padding: 0 40px;
    .add-item {
      margin-top: 20px;
      .add-name {
        margin-bottom: 10px;
        font-size: 14px;
        color: #707070;
      }
      .add-inp {
        input {
          outline: none;
          border: 0;
          width: 100%;
          height: 40px;
          padding: 0 14px;
          background: #ffffff;
          border-radius: 10px 10px 10px 10px;
          border: 1px solid #aaaaaa;
        }
        .date {
          width: 210px;
        }
        .cvv {
          width: 210px;
        }
      }
    }
    .add-btn {
      margin-top: 40px;
      width: 100%;
      height: 44px;
      line-height: 44px;
      font-size: 16px;
      color: #ffffff;
      text-align: center;
      background: #1a73e8;
      border-radius: 26px 26px 26px 26px;
    }
    .flex {
      margin-top: 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      .edit-btn {
        width: 150px;
        height: 44px;
        line-height: 44px;
        border-radius: 26px 26px 26px 26px;
        background: rgba(29, 29, 31, 0.05);
        border: 1px solid #707070;
        color: #707070;
        font-size: 16px;
        text-align: center;
      }
      .kep {
        border: 0;
        background: #1a73e8;
        color: #ffffff;
      }
    }
  }
}
</style>
