<template>
  <div>
    <div v-if="editShow == 1">
      <div class="tit">オーナー情報</div>
      <div class="user-box">
        <div class="box no-size">
          <div class="id">
            <div class="id-le"><span>ID:</span>{{email}}</div>
            <div class="id-ri shou" @click="edit()">
             <img src="../../static/changeid.svg" alt="" />
            </div>
          </div>
          <div class="fan-sn"><span>契約番号:</span>{{info.number}}</div>
          <div class="line"></div>
          <div class="form">
            <div class="its">
              <div class="name">
                <span class="fu">※</span>
                氏名
              </div>
              <div class="ipt">
                <input
                  type="text"
                  placeholder="オーナーの氏名を入力"
                  class="no-size"
                  v-model="name"
                />
              </div>
            </div>
            <div class="its">
              <div class="name">法人名</div>
              <div class="ipt">
                <input type="text" class="no-size" v-model="frname" />
              </div>
            </div>
            <div class="its">
              <div class="name">
                <span class="fu">※</span>
                店舗名
              </div>
              <div class="ipt">
                <input type="text" class="no-size" v-model="shopName" />
              </div>
            </div>
            <div class="its">
              <div class="name">
                <span class="fu">※</span>
                電話番号
              </div>
              <div class="ipt">
                <input type="number" class="no-size" placeholder="0312345678" v-model="phone" />
              </div>
            </div>
            <div class="its" style="margin-bottom: 20px">
              <div class="name">
                <span class="fu">※</span>
                郵便番号
              </div>
              <div class="ipt flex">
                <input type="number" class="no-size zhusuo" v-model="zip_code" />
                <div class="zhu shou" @click="searchAddress">住所検索</div>
              </div>
            </div>
            <div class="zhuyi">
              個人:オーナーの住所または店舗所在地の住所をご記載ください。<br /><br />
              法人:法人登記住所または店舗所在地の住所をご記載ください。
            </div>
            <div class="address">
              <el-select v-model="value" placeholder="都道府県">
                <el-option
                  v-for="item in city"
                  :key="item"
                  :label="item"
                  :value="item"
                >
                </el-option>
              </el-select>
            </div>
            <div class="its">
              <div class="name">
                <span class="fu">※</span>
                住所１
              </div>
              <div class="ipt">
                <input
                  type="text"
                  class="no-size"
                  placeholder="市区町村・番地"
                  style="width: 740px; height: 40px"
                  v-model="address1"
                />
              </div>
            </div>
            <div class="its">
              <div class="name">住所2</div>
              <div class="ipt">
                <input
                  type="text"
                  class="no-size"
                  placeholder="その他住所、建物名、号室など"
                  style="width: 740px; height: 40px"
                  v-model="address2"
                />
              </div>
            </div>
          </div>
        </div>
        <div class="keep-box">
          <div class="keep shou" @click="sends">保　存</div>
        </div>
      </div>
      <div class="fixed" v-show="keepShow">
        <div class="zhe"></div>
        <div class="sus-box">
          <img src="../../static/success.png" alt="" />
          <div class="keep">保存しました</div>
        </div>
      </div>
    </div>
    <div v-if="editShow == 2">
      <div class="edit-box" style="position: relative;">
        <div class="edits-b no-size">
          <img class="gclo" style="position: absolute;right: 15px;top: 15px;width: 24px;height: 24px;cursor: pointer;" src="../../static/gClose.png" alt="" @click="editShow = 1" />
          <div class="tits">ID(登録メールアドレス)の変更</div>
          <div class="now-id alid">
            <div class="na">現在のID(メールアドレス)</div>
            <div class="ip" v-text="email">
             
            </div>
          </div>
          <div class="zhuyis">
            変更後のメールアドレスをご入力の上、「メールアドレス(ID)の変更」をクリックしてください。
            <span>6桁の「メールアドレス変更認証コード」</span> を記載した
            Eメールをお送りします。　
          </div>
          <div class="now-id">
            <div class="na">新しいメールアドレス(ID)を入力</div>
            <div class="ip">
              <input
                type="text"
                v-model="newEmail"
                class="no-size newInpt"
                placeholder="メールアドレスを入力してください"
              />
            </div>
          </div>
        </div>
        <div class="bots">
          <!-- <div class="kep-btn shou" @click="editShow = 3"> -->
          <div class="kep-btn shou" @click="sendCode">
            メールアドレス(ID)の変更
          </div>
        </div>
      </div>
    </div>
    <div v-if="editShow == 3">
      <div class="content_login no-size">
        <div class="tit_one">認証コードを入力してください。</div>
        <div class="tit_two">
          変更した新しいメールアドレスに送信した6桁の<br />認証コードを入力してください。
        </div>
        <div class="tit_two" style="margin-top: 10px">({{newEmail}})</div>
        <div class="tit_two" style="margin-top: 10px; font-weight: bold">
          Eメールで送信された認証コードは10分間有効です
        </div>
        <div
          class="u-flex u-row-between"
          style="margin-top: 40px; margin-left: 42px"
        >
          <yzm
            :value.sync="password"
            :width="48"
            :height="76"
            backgroundColor="#ffffff"
            :bold="true"
            showVal
            :length="6"
            @changeCode="changes"
          >
          </yzm>
        </div>
        <div class="tit_three u-flex u-row-center shou" @click="sub">
          メールアドレス(ID)変更を認証
        </div>
        <div class="tit_four">
          数分経ってもコードが届かない場合は、認証コードを再送するか、
          入力したメールアドレスが間違っている可能性がございますので、再度ID(メールアドレス)の入力から行なってください。
        </div>
        <div class="title_five shou" @click="sendCode">認証コードを再送</div>
      </div>
    </div>
    <div class="fixed" v-if="tiShow">
      <div class="zhe"></div>
      <div class="bosx no-size">
        <div class="b-ts">メールアドレス(ID)を変更しました</div>
        <div class="you">djdujek@cardsan.com</div>
        <div class="des">
          今後、ログイン情報の通知などは、こちらの新しいメールアドレス(ID)をご利用ください。
        </div>
        <div class="o-btn shou" @click="tiShow = false">OK</div>
      </div>
    </div>
  </div>
</template>

<script>
import yzm from "../../components/yzm.vue";
import {getUserINfo,gatZipAddress,editUser,changeEmail,getStore} from "@/http/api.js"
export default {
  name: "",
  components: { yzm },
  data() {
    return {
      tiShow: false,
      name:"",
      phone:"",
      email:"",
      address1:"",
      address2:"",
      frname:"",
      shopName:"",
      zip_code:"",
      int_one: "",
      int_two: "",
      int_three: "",
      int_four: "",
      int_five: "",
      int_six: "",
      nowEmail: "example@mail.jp",
      newEmail: "",
      password: "",
      keepShow: false,
      // yzmShow: true,
      editShow: 1,
      city: ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都',
              '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府',
              '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県',
              '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
          ],
      value: "",
      info:{}
    };
  },
  created() {
      let that = this
      that.getDetail()
      getStore().then((res) => {
          if(res.code == 200){
              console.log("info",res)
              that.info = res.data.shop_info
          }
      })
  },
  methods: {
      changes(e){
        console.log(e)  
        this.password = e
      },
      sub(){
        let that = this
        let data = {}
        data['email'] = that.newEmail
        data['request_type'] = 2
        data["code"] = that.password
        changeEmail(data).then((res) => {
            if(res.code == 200){
               // that.$message.success(res.message) 
               that.$message({
               	message: res.message,
               	type: 'success',
                    offset: 400
               });
               setTimeout(() => {
                   that.editShow =1
               },1500)
            } 
            else{
                // that.$message.error(res.message)
                that.$message({
                	message: res.message,
                	type: 'error',
                     offset: 400
                });
            } 
        })
      },
      
    sendCode(){
       let that = this
       let data = {}
       data['email'] = that.newEmail
       data['request_type'] = 1
       changeEmail(data).then((res) => {
           if(res.code == 200){
               that.$message({
               	message: res.message,
               	type: 'success',
                    offset: 400
               });
               that.editShow = 3
           } else{
               that.$message({
               	message: res.message,
               	type: 'error',
                    offset: 400
               });
           }
       })
    },
      
    getDetail(){
      let that = this
      getUserINfo().then((res) => {
          console.log("res",res)
          if(res.code == 200){
              let info = localStorage.getItem("admin")
              let userinfo = JSON.parse(info)
              console.log(userinfo)
              that.name = res.data.owner.name
              that.address1 = res.data.owner.address1
              that.address2 = res.data.owner.address2
              that.frname = res.data.owner.legal_person
              that.phone = res.data.owner.phone
              that.zip_code = res.data.owner.zip_code
              that.shopName = res.data.owner.shop_name
              that.value = res.data.owner.province
              that.province = res.data.owner.province
              that.email = userinfo.email
          }
      })
    },
    searchAddress(){
       let that = this
       gatZipAddress(that.zip_code).then((res) => {
           console.log(res)
           if(res.code == 200){
               that.value = res.data.address.address1
               that.address1 = res.data.address.address2 + res.data.address.address3
           }
       })
    },
    yzmSong() {
      this.tiShow = true;
      this.editShow = 1;
      console.log(this.password);
    },

    sends() {
      let that = this
      let data = {}
      data['name'] = that.name
      data['legal_person'] = that.frname
      data['shop_name'] = that.shopName
      data['phone'] = that.phone
      data['zip_code'] = that.zip_code
      data['province'] = that.value
      data['address1'] = that.address1
      data["address2"] = that.address2
      editUser(data).then((res) => {
          if(res.code == 200){
              that.keepShow = true;
              setTimeout(() => {
                that.keepShow = false;
              }, 1000);
          }else{
              that.$message({
              	message: res.message,
              	type: 'error',
                   offset: 400
              });
          }
      })
     
    },
    edit() {
      this.editShow = 2;
    },
  },
};
</script>

<style lang="scss" scoped>
    ::v-deep .el-select .el-input .el-select__caret{
        color: #aaa !important;
    }
    .alid{
        width: 400px;
        background: rgba(29,29,31,0.1);
        text-align: center;
        border-radius: 10px;
        padding: 14px 0;
    }
.content_login {
  margin-top: 49px;
  margin-left: 149px;
  width: 640px;
  height: 587px;
  text-align: center;
  background: #fff;
  padding: 0 95px;
  box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);

  .tit_one {
    width: 100%;
    font-size: 30px;
    color: #1d1d1f;
    font-weight: 700;
    text-align: center;
    padding-top: 40px;
  }
  .tit_two {
    font-size: 14px;
    color: #1d1d1f;
    margin-top: 40px;
  }
  input::-webkit-outer-spin-button,
  input::-webkit-inner-spin-button {
    -webkit-appearance: none;
  }

  .tit_three {
    width: 335px;
    height: 56px;
    background: #1a73e8;
    border-radius: 10px;
    margin: 40px auto;
    font-size: 20px;
    color: #fff;
  }
  .tit_threeCss {
    background: rgba(83, 177, 175, 0.5);
  }
  .tit_four {
    font-size: 14px;
    color: #707070;
  }
  .title_five {
    font-size: 14px;
    color: #2699fb;
    margin-top: 30px;
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
}
::v-deep .user-box {
  margin-top: 20px;
  width: 820px;
  background: #ffffff;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .box {
    width: 100%;
    padding-left: 40px;
    padding-top: 47px;
    padding-right: 42px;
    .id {
      // display: flex;
      align-items: center;
      margin-bottom: 30px;
      .id-le {
        margin-right: 40px;
        font-size: 20px;
        span {
          color: #707070;
          margin-right: 10px;
        }
        color: #1d1d1f;
      }
      .id-ri {
        width: 160px;
        height: 31px;
        border-radius: 20px 20px 20px 20px;
        padding: 0 12px;
        margin-top: 10px;
        
        img{
            width: 100%;
            height: 100%;
        }
      }
    }
    .fan-sn {
      font-size: 20px;
      color: #1d1d1f;
      span {
        color: #707070;
        margin-right: 10px;
      }
    }
    .line {
      margin-top: 40px;
      margin-bottom: 40px;
      width: 100%;
      height: 1px;
      background: #d2d2d7;
    }
    .its {
      margin-bottom: 30px;
      .name {
        font-size: 16px;
        margin-bottom: 5px;
        .fu {
          color: #d93025;
        }
        color: #707070;
      }
      .flex {
        display: flex;
        .zhusuo {
          width: 210px !important;
        }
        .zhu {
          width: 100px;
          height: 40px;
          text-align: center;
          line-height: 40px;
          background: #1a73e8;
          color: #ffffff;
          font-size: 16px;
          margin-left: 20px;
          border-radius: 10px 10px 10px 10px;
        }
      }
      .ipt {
        input {
          outline: none;
          padding: 0 14px;
          width: 329px;
          height: 40px;
          border: 1px solid #aaaaaa;
          border-radius: 10px 10px 10px 10px;
        }
      }
    }
    .zhuyi {
      padding: 12px;
      width: 360px;
      // height: 55px
      background: rgba(143, 168, 248, 0.2);
      border-radius: 10px ;
      color: #1a73e8;
      font-size: 12px;
      margin-bottom: 32px;
      line-height: 1 !important;
    }
    .address {
      margin-bottom: 32px;
      .el-select {
        .el-input__inner {
          width: 210px;
          height: 40px;
          background: rgba(29, 29, 31, 0.05);
          border-radius: 10px 10px 10px 10px;
          border: 1px solid #aaaaaa;
        }
      }
    }
  }
  .keep-box {
    margin-top: 103px;
    border-top: 1px solid #d2d2d7;
    padding-top: 29px;
    padding-bottom: 70px;
    .keep {
      width: 200px;
      height: 50px;
      margin: 0 auto;
      line-height: 50px;
      font-size: 20px;
      color: #ffffff;
      text-align: center;
      background: #1a73e8;
      border-radius: 10px 10px 10px 10px;
    }
  }
}
.bosx {
  width: 345px;
  height: 280px;
  background: #ffffff;
  border-radius: 10px 10px 10px 10px;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  padding: 0 25px;
  padding-top: 30px;
  text-align: center;
  .b-ts {
    color: #1d1d1f;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 30px;
  }
  .you {
    font-weight: 600;
    font-size: 14px;
    color: #1d1d1f;
    margin-bottom: 30px;
  }
  .des {
    font-size: 14px;
    color: #1d1d1f;
    margin-bottom: 49px;
  }
  .o-btn {
    margin: 0 auto;
    width: 240px;
    height: 48px;
    font-size: 16px;
    text-align: center;
    line-height: 48px;
    background: rgba(29, 29, 31, 0.1);
    border-radius: 10px 10px 10px 10px;
    color: #707070;
  }
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
.edit-box {
  width: 640px;
  height: 579px;
  background: #ffffff;
  box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);
  margin-left: 100px;
  margin-top: 4px;
  .edits-b {
    width: 100%;
    padding: 50px 120px;
    .tits {
      text-align: center;
      margin-bottom: 20px;
      font-size: 21px;
      font-weight: 700;
      color: #1d1d1f;
    }
    .now-id {
      .na {
        color: #1d1d1f;
        font-size: 16px;
        margin-bottom: 5px;
      }
      .ip {
        input {
          width: 400px;
          height: 42px;
          background: #f5f5f7;
          border-radius: 10px 10px 10px 10px;
          outline: none;
          padding: 0 10px;
        }
        .newInpt {
          background: #ffffff;
          border: 1px solid #aaaaaa;
        }
      }
    }
    .zhuyis {
      margin-top: 30px;
      margin-bottom: 50px;
      color: #1d1d1f;
      font-size: 14px;
      span {
        margin-left: 10px;
        font-weight: 700;
      }
    }
  }
  .bots {
    border-top: 1px solid #d2d2d7;
    padding-top: 30px;
    .kep-btn {
      width: 400px;
      height: 50px;
      line-height: 50px;
      color: #ffffff;
      font-size: 16px;
      text-align: center;
      background: #1a73e8;
      border-radius: 25px 25px 25px 25px;
      margin: 0 auto;
    }
  }
}
</style>
