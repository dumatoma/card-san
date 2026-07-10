<template>
  <div>
    <div class="not_tit u-flex u-row-between">配送先住所</div>
    <div class="not_cont no-size">
      <div class="tit">配送住所を入力してください</div>
      <div class="u-flex m-t-40">
        <div class="text_red">※</div>
        <div class="text_grey">郵便番号</div>
      </div>
      <div class="u-flex m-t-10">
        <input class="int no-size" v-model="form.zip_code" type="text" placeholder="ハイフンなし" />
        <div class="add_btn u-flex-center shou" @click="searchAddress">住所検索</div>
      </div>
      <div style="margin-top: 33px">
        <el-select
          v-model="form.province"
          @change="selectChanged"
          placeholder="都道府県"
        >
          <el-option v-for="item in city"  :key="item" :label="item" :value="item" >
          </el-option>
        </el-select>
      </div>
      <div class="u-flex m-t-30">
        <div class="text_red">※</div>
        <div class="text_grey">住所１</div>
      </div>
      <input
        class="int int_addOne no-size m-t-10"
        type="text"
        v-model="form.address1"
        placeholder="市区町村・番地"
      />
      <div class="text_grey m-t-30">住所2</div>
      <input
        class="int int_addOne no-size m-t-10"
        type="text"
        v-model="form.address2"
        placeholder="その他住所、建物名、号室など"
      />
      <div class="u-flex m-t-30">
        <div class="text_red">※</div>
        <div class="text_grey">電話番号</div>
      </div>
      <input
        class="int int_addTwo no-size m-t-10"
        type="text"
        v-model="form.phone"
        placeholder="ハイフンなし"
      />
      <div class="u-flex m-t-30">
        <div class="text_red">※</div>
        <div class="text_grey">宛名</div>
      </div>
      <input class="int int_addTwo no-size m-t-10"  v-model="form.name" type="text" />
      <div class="footer">
        <div class="btn u-flex-center shou" @click="saveAddress"  :class="[Object.keys(form).length == 6 ? 'btn_blue' : '']" >この住所を使用</div>
      </div>
    </div>
  </div>
</template>

<script>
import {addAddress,gatZipAddress} from "@/http/api.js"
export default {
  data() {
    return {
      form:{
          zip_code:"",
          province:"",
          address1:"",
          address2:"",
          phone:"",
          name:""
      },
      city: ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都',
              '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府',
              '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県',
              '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
          ],
    };
  },
  methods: {
    selectChanged(value){
      this.form.province = value
    },
    searchAddress(){
        let that = this
        gatZipAddress(that.form.zip_code).then((res) => {
            if(res.code == 200){
                console.log(res)
                that.form.province = res.data.address.address1
                that.form.address1 = res.data.address.address2 + res.data.address.address3
            }
        })
    },
    saveAddress(){
        let that = this
        addAddress(that.form).then((res) => {
            if(res.code == 200){
                that.$message.success(res.message)
                setTimeout(()=> {
                    history.go(-1)
                },1500)
            }else{
                that.$message.error(res.message)
            }
        })
    }
  },
};
</script>

<style lang="scss" scoped>
.not_tit {
  width: 820px;
  padding: 0 0 22px 0;
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
}
.not_cont {
  width: 820px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  padding: 30px 40px 0 40px;
  .tit {
    font-size: 16px;
    color: #707070;
    font-weight: bold;
    padding-bottom: 10px;
    border-bottom: 1px solid #d2d2d7;
  }
  .text_red {
    color: #d93025;
  }
  .text_grey {
    color: #86868b;
  }
  .int {
    width: 210px;
    height: 40px;
    outline: 0;
    background: #ffffff;
    border-radius: 10px;
    padding-left: 10px;
    border: 1px solid #aaaaaa;
  }
  .add_btn {
    width: 100px;
    height: 40px;
    color: #fff;
    font-size: 16px;
    margin-left: 20px;
    border-radius: 10px;
    background: #1a73e8;
  }

  .int_addOne {
    width: 740px;
  }
  .int_addTwo {
    width: 329px;
  }
  .footer {
    margin-top: 50px;
    padding: 30px 0 44px 0;
    border-top: 1px solid #d2d2d7;
    .btn {
      width: 335px;
      height: 56px;
      font-size: 16px;
      color: #fff;
      margin: 0 auto;
      background: rgba(26, 115, 232);
      border-radius: 10px;
    }
    .btn_blue {
    background: #1a73e8;
  }
  }
}
::v-deep .el-input__inner {
  width: 210px;
  padding-left: 10px;
  background: #f5f5f7;
  border-radius: 10px;
  border: 1px solid #707070;
}
::-webkit-input-placeholder {
  /* Chrome/Opera/Safari */
  color: rgba(29, 29, 31, 0.3);
}
::-moz-placeholder {
  /* Firefox 19+ */
  color: rgba(29, 29, 31, 0.3);
}
:-ms-input-placeholder {
  /* IE 10+ */
  color: rgba(29, 29, 31, 0.3);
}
:-moz-placeholder {
  /* Firefox 18- */
  color: rgba(29, 29, 31, 0.3);
}
</style>
