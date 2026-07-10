<template>
  <div @click="wrapClick">
    <div class="u-flex u-row-between head">
      <div class="not_tit u-flex u-row-between">店舗情報</div>
      <div
        class="btn_grey shou u-flex u-row-center"
        @click.stop="previewShow = true"
      >
        Preview
      </div>
    </div>
    <div class="not_cont no-size">
      <div style="padding: 30px 40px 0 40px">
        <div class="add-pictur m-t-20 no-size">
          <div class="pic-tit">TOP画像(最大6枚)</div>

          <div class="img-bos">
            <div class="img-boxs u-flex u-flex-wrap">
              <div
                class="img-item"
                v-for="(item, index) in shopInfo.carousel"
                :key="index"
                :style="{ background: item ? '' : 'rgba(0,0,0,0.07)' }"
              >
                <img
                  src="@/static/gClose.png"
                  alt=""
                  class="colse shou"
                  @click="delPicture(index)"
                />
                <img
                  :src="item"
                  class="tu"
                  v-if="item"
                  @click="handlePictureCardPreview(item)"
                />
                <img v-else src="@/static/index/image.png" class="moren" />
              </div>
            </div>
            <el-upload
              class="upload-demo"
              accept="image/*"
              action="https://api.card-san.jp/tool/upload/file"
              :on-success="handleAvatarSuccess"
              drag
              :limit="6"
              :show-file-list="false"
              :file-list="fileList"
              name="file"
            >
              <div class="tuo no-size">
                <img src="@/static/edit-image.png" alt="" />
                <br />
                <span class="txt">クリックorファイルをドロップ</span>
              </div>
            </el-upload>
            <!-- <div class="add">
              <el-upload
                class="upload-demo"
                accept="image/*"
                action="https://hyk.ljcyh.com/tool/upload/file"
                :on-success="handleAvatarSuccess"
                drag
                :limit="6"
                :show-file-list="false"
                :file-list="fileList"
                name="file"
              >
                <div class="zhui">
                  <span class="el-icon-plus" style="font-weight: 700"></span>
                  ファイル追加
                </div>
              </el-upload>
            </div> -->
          </div>
        </div>
      </div>
      <!-- 基本情報 -->
      <div class="grey_back u-flex no-size">基本情報</div>
      
      <div class="basic">
          <div class="industry m-t-30">プロフィール</div>
          <textarea class="no-size" rows="8" style="width: 740px;resize: none;outline: none;margin-bottom: 30px;margin-top: 10px;box-sizing: border-box;padding: 5px;border: 1px solid #d2d2d7;border-radius: 10px;height: 100px;" type="text" placeholder="あなたのビジネスのプロフィールを入力してください。" v-model="shopInfo.des" />
        <div class="u-flex industry">
          <div style="color: #d93025">※</div>
          <div>業種(店舗情報には反映されません)</div>
        </div>
        <div class="m-t-10">
          <el-select
            v-model="shopInfo.shop_cate"
            @change="selectChanged"
            placeholder="業種を選択"
          >
            <el-option
              v-for="item in cates"
              :key="item"
              :label="item.value"
              :value="item.key"
            >
            </el-option>
          </el-select>
        </div>
        <div class="industry m-t-30">電話番号</div>
        <input class="int no-size" type="number" placeholder="0312345678" v-model="shopInfo.phone" />
        <div class="industry m-t-30">営業時間</div>
        <textarea
          class="area no-size"
          type="number"
          placeholder="平日9:00~20:00
土日祝 9:00~23:00"
          v-model="shopInfo.business_time"
        />
        <div class="industry m-t-30">定休日</div>
        <textarea
          class="area no-size"
          type="number"
          placeholder="毎週月曜日・第２、第４火曜日
夏季休暇、年末年始休暇など"
          v-model="shopInfo.rest_time"
        />
      </div>
      <!-- 営業カレンダーを表示 -->
      <div class="sale u-flex u-col-top no-size">
        <div
          class="radius"
          :class="{ radiusCss: shopInfo.business_calendar == 1 }"
          @click.stop="shopInfo.business_calendar == 1?shopInfo.business_calendar = 0:shopInfo.business_calendar = 1"
        >
          <div class="blue_dot" v-if="shopInfo.business_calendar == 1"></div>
        </div>
        <div
          style="font-size: 16px; font-weight: bold"
          :style="shopInfo.business_calendar == 1 ? 'color: #1d1d1f;' : 'color: #707070;'"
        >
          営業カレンダーを表示
          <div class="sale_grey">
            ※チェックを外すと営業カレンダーは表示されません
          </div>
        </div>
      </div>
      <div class="four">
        <div class="industry m-t-30">郵便番号</div>
        <div class="u-flex m-t-10">
          <input class="int_two no-size" type="number" placeholder="郵便番号" v-model="shopInfo.zip_code" />
          <div class="add_btn u-flex-center shou" @click="searchAddress">住所検索</div>
        </div>
        <div class="m-t-30">
          <el-select
            style="width: 210px !important"
            placeholder="都道府県"
            v-model="shopInfo.province"
          >
            <el-option
              v-for="item in city"
              :key="item"
              :label="item"
              :value="item"
            >
            </el-option>
          </el-select>
        </div>
        <div class="industry m-t-30">住所１</div>
        <input
          class="int_three no-size"
          type="text"
          placeholder="市区町村・番地"
          v-model="shopInfo.address1"
        />
        <div class="industry m-t-30">住所2</div>
        <input
          class="int_three no-size"
          type="text"
          placeholder="その他住所、建物名、号室など"
          v-model="shopInfo.address2"
        />
        <div class="industry m-t-30">アクセス説明</div>
        <textarea
          class="area area_one no-size"
          type="number"
          placeholder="お店へのアクセスを分かりやすく記入してください。駅から徒歩3分、コンビニのすぐ横など。"
          v-model="shopInfo.traffic_explain"
        />
      </div>
      
      
      <!-- 詳細情報 -->
      <div class="grey_back u-flex no-size">詳細情報</div>
      <div class="detail">
        <div class="industry">ホームページURL</div>
        <input class="int_three no-size" type="text" placeholder="http://~" v-model="shopInfo.web_url" />
        <div class="industry m-t-30">利用可能決済</div>
        <textarea
          class="area area_one no-size"
          type="number"
          placeholder="現金、クレジットカード(VISA/Master/JCB/Amex/Diners)、 PayPayなど"
          v-model="shopInfo.pay_type"
        />
        <div class="industry m-t-30">備考欄</div>
        <textarea
          class="area area_one no-size"
          type="number"
          placeholder="追記があればご記入ください。
駐車場あり、保証制度付きなど。"
          v-model="shopInfo.remark"
        />
      </div>
      <!-- その他設定 -->
      <div class="grey_back u-flex no-size">その他設定</div>
      <div class="other_set u-flex u-col-top no-size">
        <div
          class="radius shou"
          :class="{ radiusCss: shopInfo.staff_des == 1 }"
          @click.stop="introduceClick"
        >
          <div class="blue_dot" v-if="shopInfo.staff_des == 1"></div>
        </div>
        <div
          style="font-size: 16px; font-weight: bold"
          :style="
            shopInfo.staff_des == 1
              ? 'color: #1d1d1f;'
              : 'color: #707070;font-weight: lighter;'
          "
        >
          スタッフ紹介
          <div class="sale_grey">
            ※チェックを外すとスタッフ紹介は表示されません
          </div>
        </div>
      </div>
      <div class="other_set u-flex u-col-top no-size">
        <div
          class="radius shou"
          :class="{ radiusCss: shopInfo.share_line == 1 }"
          @click.stop="friendClick"
        >
          <div class="blue_dot" v-if="shopInfo.share_line == 1"></div>
        </div>
        <div
          style="font-size: 16px; font-weight: bold"
          :style="
            shopInfo.share_line == 1
              ? 'color: #1d1d1f;'
              : 'color: #707070;font-weight: lighter;'
          "
        >
          友達にアプリを紹介
          <div class="sale_grey">
            ※チェックを外すとアプリ紹介LINKは表示されません
          </div>
        </div>
      </div>
      <div class="other_set other_bottom u-flex u-col-top no-size">
        <div
          class="radius shou"
          :class="{ radiusCss: shopInfo.sns_url == 1}"
          @click.stop="linkClick"
        >
          <div class="blue_dot" v-if="shopInfo.sns_url == 1"></div>
        </div>
        <div
          style="font-size: 16px; font-weight: bold"
          :style="
            shopInfo.sns_url == 1
              ? 'color: #1d1d1f;'
              : 'color: #707070;font-weight: lighter;'
          "
        >
          SNSリンク
          <div class="sale_grey">
            ※チェックを外すとSNSリンクは表示されません
          </div>
          <div v-if="shopInfo.sns_url == 1">
             <div
               class="u-flex m-t-30"
               :style="shopInfo.sns_ins == 1 ? 'font-weight: bold;' : 'font-weight: lighter;'"
             >
               <div
                 class="radius shou"
                 :class="{ radiusCss: shopInfo.sns_ins == 1 }"
                 @click.stop="shopInfo.sns_ins == 1?shopInfo.sns_ins = 0:shopInfo.sns_ins = 1"
               >
                 <div class="blue_dot" v-if="shopInfo.sns_ins == 1"></div>
               </div>
               Instagram
             </div> 
             <div v-if="shopInfo.sns_ins == 1">
                 <textarea
                           class="area area_one no-size"
                           type="text"
                           placeholder="アカウントURLを入力してください。"
                           v-model="shopInfo.sns_ins_url"
                           style="width: 655px;"
                         />
             </div>
             
             <div
               class="u-flex m-t-30"
               :style="shopInfo.sns_facebook == 1 ? 'font-weight: bold;' : 'font-weight: lighter;'"
             >
               <div
                 class="radius shou"
                 :class="{ radiusCss: shopInfo.sns_facebook == 1 }"
                 @click.stop="shopInfo.sns_facebook == 1?shopInfo.sns_facebook = 0:shopInfo.sns_facebook = 1"
               >
                 <div class="blue_dot" v-if="shopInfo.sns_facebook == 1"></div>
               </div>
               Facebook
             </div>
             <div v-if="shopInfo.sns_facebook == 1">
                 <textarea
                           class="area area_one no-size"
                           type="text"
                           placeholder="アカウントURLを入力してください。"
                           v-model="shopInfo.sns_facebook_url"
                           style="width: 655px;"
                         />
             </div>
             
             <div
               class="u-flex m-t-30"
               :style="shopInfo.sns_youtobe == 1 ? 'font-weight: bold;' : 'font-weight: lighter;'"
             >
               <div
                 class="radius shou"
                 :class="{ radiusCss: shopInfo.sns_youtobe == 1 }"
                 @click.stop="shopInfo.sns_youtobe == 1?shopInfo.sns_youtobe = 0:shopInfo.sns_youtobe = 1"
               >
                 <div class="blue_dot" v-if="shopInfo.sns_youtobe == 1"></div>
               </div>
               Youtube
             </div>
             <div v-if="shopInfo.sns_youtobe == 1" style="box-sizing: border-box;padding-right: 35px;">
                 <textarea
                           class="area area_one no-size"
                           type="text"
                           placeholder="アカウントURLを入力してください。"
                           v-model="shopInfo.sns_youtobe_url"
                           style="width: 655px;"
                         />
             </div>
              
          </div>
          
          
        </div>
      </div>

      <div class="footer">
        <div
          class="btn_blue shou u-flex-center"
          @click.stop="conserveClick"
        >
          保　存
        </div>
      </div>
    </div>
    <!-- 保存 -->
    <div class="fixed" v-show="conserveShow">
      <div class="release u-flex-col u-col-center u-row-center">
        <img src="@/static/index/sign_blue.png" class="sign" />
        <div>保存しました</div>
      </div>
    </div>
    <!-- 预览 -->
    <div class="fixed" v-show="previewShow">
      <div class="per-box">
        <div class="tit">
          <img
            class="shou"
            @click="previewShow = false"
            src="@/static/index/close.png"
            alt=""
          />
          <div class="t">プレビュー</div>
        </div>
        <div class="big_slide">
          <!-- <div class="slide"> -->
            <el-carousel
              height="250px"
              indicator-position="outside"
              arrow="never"
            >
              <el-carousel-item
                v-for="(item, index) in shopInfo.carousel"
                :key="index"
              >
                <img style="width: 100%; height: 100%;object-fit: cover" :src="item" alt="" />
              </el-carousel-item>
            </el-carousel>
            <div class="items-boxs">
                <div class="items u-flex u-row-between">
                  <div class="items-fle" style="display: block;">
                    <div class="left" style="width: 157px;">プロフィール　</div>
                    <div class="midd" style="padding: 0 !important;margin-left: 0;margin-top: 5px;" v-text="shopInfo.des"></div>
                  </div>
                </div>
              <div class="items u-flex u-row-between">
                <div class="items-fle">
                  <div class="left">TEL　</div>
                  <div class="midd" v-text="shopInfo.phone"></div>
                </div>
                <div
                  class="right shou phone-btn u-flex-center"
                  @click="phoneShow = true"
                >
                  電話する
                </div>
              </div>
              <div class="items" v-if="shopInfo.business_calendar == 1">
                <div class="items-fle">
                  <div class="left">営業時間</div>
                  <div class="midd wap" v-text="shopInfo.business_time">
                    <!-- <div class="">平日9:00~20:00</div>
                    <div class="">土日祝 9:00~23:00</div> -->
                  </div>
                </div>
              </div>
              <div class="items">
                <div class="items-fle">
                  <div class="left">定休日</div>
                  <div class="midd" style="width: 164px">
                    <div class="wap" v-text="shopInfo.rest_time"></div>
                  </div>
                </div>
              </div>
              <div class="items u-flex u-row-between">
                <div class="items-fle">
                  <div class="left">本日</div>
                  <div class="midd">
                    <div class="">6月25日(水)</div>
                    <div class="blue">営業中</div>
                    <div class="">9:00〜20:00</div>
                  </div>
                </div>
                <div
                  class="right shou ri-img u-flex u-row-center"
                  @click.stop="clandarShow = !clandarShow"
                >
                  <img src="../../static/rili.png" alt="" />
                  <div class="text">カレンダー</div>
                </div>
              </div>
             <!-- <clandar v-show="clandarShow"></clandar> -->
              <!-- <div class="map">
               
              </div> -->
              <div class="zhu-item" style="position: relative;">
                <div class="zhu-box" style="margin-bottom: 0;">
                  <div class="zhu-left">住所　　</div>
                  <div class="zhu-right">
                    <div class="">〒{{shopInfo.zip_code}}</div>
                     <div class="" v-text="shopInfo.province"></div>
                    <div class="" v-text="shopInfo.address1"></div>
                  </div>
                </div>
                <div class="mapbt">
                     <div class="map-btn shou">地図アプリで見る</div>
                </div>
                
              </div>
              <div class="zhu-item">
                <div class="zhu-box">
                  <div class="zhu-left">アクセス</div>
                  <div class="zhu-right">
                    <div class="wap">
                      {{shopInfo.traffic_explain}}
                    </div>
                  </div>
                </div>
              </div>
              <div class="zhu-item">
                <div class="zhu-box">
                  <div class="zhu-left">HP</div>
                  <div class="zhu-right">
                    <div class="lian" v-text="shopInfo.web_url"></div>
                  </div>
                </div>
              </div>
              <div class="zhu-item">
                <div class="zhu-box">
                  <div class="zhu-left">利用可能決済</div>
                  <div class="zhu-right">
                    <div class="wap">
                      {{shopInfo.pay_type}}
                    </div>
                  </div>
                </div>
              </div>
              <div class="zhu-item zhu-item-border">
                <div class="zhu-box">
                  <div class="zhu-left">備考</div>
                  <div class="zhu-right wap">
                    {{shopInfo.remark}}
                  </div>
                </div>
              </div>
              <div class="staff" v-if="shopInfo.staff_des == 1">STAFF</div>
              <div
                class="store-popel-box"
                v-for="(item, index) in staff"
                :key="index"
                v-if="shopInfo.staff_des == 1"
              >
                <div class="store-box">
                  <div class="store-left">
                    <img :src="item.image[0]" style="object-fit: cover;" class="store-img" />
                    <img
                      src="@/static/index/heji.png"
                      mode=""
                      class="store-heji"
                      @click.stop="heJiShow(index)"
                    />
                  </div>
                  <div class="store-right">
                    <div class="store-name">
                      {{ item.name }}
                    </div>
                    <div class="store-identity">
                      {{ item.post }}
                    </div>
                    <div class="store-des">
                      {{ item.self_introduction }}
                    </div>
                  </div>
                </div>
              </div>
              <div class="shar" v-if="shopInfo.share_line == 1">
                <div class="shar-tit">友達にアプリを紹介</div>
                <div class="shar-btn">
                  <img src="../../static/dlogo.png" />
                  <div>アプリダウンロードリンクをコピー</div>
                </div>
              </div>
              <div class="shar-line"></div>
              <div class="sns" v-if="shopInfo.sns_url == 1">
                <div class="sns-tit">SNS</div>
                <div class="img-sns">
                  <img src="../../static/index/glyph.png" v-if="shopInfo.sns_ins == 1"/>
                  <img src="../../static/index/FaceBook.png" v-if="shopInfo.sns_facebook == 1" />
                  <img src="../../static/index/youtube.png"  v-if="shopInfo.sns_youtobe == 1" />
                </div>
              </div>
            </div>
          <!-- </div> -->
        </div>

        <div class="bottom no-size u-flex u-row-between">
          <div class="bot_item" v-for="(item, index) in botList" :key="index">
            <div class="image u-flex-center">
              <img
                :src="item.img"
                :style="{
                  width: index == 0 ? '30px' : index == 1 ? '27px' : '25px',
                }"
              />
            </div>
            <div
              class="bot_text"
              :style="{ color: index == 1 ? '#1a73e8' : '' }"
            >
              {{ item.title }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- 预览图片 -->
    <el-dialog :visible.sync="dialogVisible" :append-to-body="true">
      <img :src="dialogImageUrl" style="width: 70%; height: auto;" />
    </el-dialog>
  </div>
</template>

<script>

import {getStore,getConfig,gatZipAddress,editShop,getAllStaff} from "@/http/api.js"
export default {
  data() {
    return {
      show: true,
      shopInfo:{},
      cates: [],
      industry: "",
      introduceShow: true,
      friendShow: true,
      linkShow: true,
      SNSList: ["Instagram", "Twitter", "Facebook", "Youtube"],
      SNSIndex: 0,
      conserveShow: false,
      previewShow: false,
      staff:[],
      swiperList: [
        {
          img: require("../../static/index/google.png"),
        },
        {
          img: require("../../static/index/close.png"),
        },
        {
          img: require("../../static/index/ITF.png"),
        },
      ],
      city: ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都',
              '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府',
              '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県',
              '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
          ],
      clandarShow: false,
      storePeopleList: [
        {
          img: "https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fpic1.win4000.com%2Fwallpaper%2F2018-03-24%2F5ab5ea38c5d77.jpg%3Fdown&refer=http%3A%2F%2Fpic1.win4000.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1665020966&t=872df6544f7c4e8920f69959d183bea1",
          uname: "鈴木　孝太郎",
          identity: "店長",
          des: "港区１１番にある美容院です。カットが安いです・・テキスト・テキスト・テキスト・テキスト・テキスト・テキス",
          imgList: [
            "https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fimg.jj20.com%2Fup%2Fallimg%2F4k%2Fs%2F01%2F210924164FC327-0-lp.jpg&refer=http%3A%2F%2Fimg.jj20.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1665020987&t=70e9689c35c34d556f1faac5c98f3f20",
            "https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fc-ssl.duitang.com%2Fuploads%2Fitem%2F201605%2F23%2F20160523002341_PJwcN.thumb.1000_0.jpeg&refer=http%3A%2F%2Fc-ssl.duitang.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1665021009&t=e7105187260c496012bc3ad78c846790",
            "https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fpic162.nipic.com%2Ffile%2F20180403%2F6083536_232802107031_2.jpg&refer=http%3A%2F%2Fpic162.nipic.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1665021029&t=8fd93dfc5716e0213b2214220a05199b",
          ],
        },
        {
          img: "https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fpic1.win4000.com%2Fwallpaper%2F2018-03-24%2F5ab5ea38c5d77.jpg%3Fdown&refer=http%3A%2F%2Fpic1.win4000.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1665020966&t=872df6544f7c4e8920f69959d183bea1",
          uname: "鈴木　孝太郎",
          identity: "店長",
          des: "港区１１番にある美容院です。カットが安いです・・テキスト・テキスト・テキスト・テキスト・テキスト・テキス",
          imgList: [
            "https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fimg.jj20.com%2Fup%2Fallimg%2F4k%2Fs%2F01%2F210924164FC327-0-lp.jpg&refer=http%3A%2F%2Fimg.jj20.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1665020987&t=70e9689c35c34d556f1faac5c98f3f20",
            "https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fc-ssl.duitang.com%2Fuploads%2Fitem%2F201605%2F23%2F20160523002341_PJwcN.thumb.1000_0.jpeg&refer=http%3A%2F%2Fc-ssl.duitang.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1665021009&t=e7105187260c496012bc3ad78c846790",
            "https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fpic162.nipic.com%2Ffile%2F20180403%2F6083536_232802107031_2.jpg&refer=http%3A%2F%2Fpic162.nipic.com&app=2002&size=f9999,10000&q=a80&n=0&g=0n&fmt=auto?sec=1665021029&t=8fd93dfc5716e0213b2214220a05199b",
          ],
        },
      ],
      imgList: [],
      fileList: [],
      dialogImageUrl: "",
      dialogVisible: false,
      botList: [
        {
          img: require("../../static/index/cardImg.png"),
          title: "My Card",
        },
        {
          img: require("../../static/icon1.svg"),
          title: "店舗情報",
        },
        {
          img: require("../../static/index/bell.png"),
          title: "お知らせ",
        },
        {
          img: require("../../static/index/message.png"),
          title: "メッセージ",
        },
        {
          img: require("../../static/index/menu.png"),
          title: "メニュー",
        },
      ],
    };
  },
  created() {
     let that = this
     that.getInfo()
     that.getConfig()
     console.log("storeInfo")
     let data = {}
     data.startTime = " "
     data.endTIme = " "
     data.id = ' '
     console.log(data)
     getAllStaff().then((res) => {
         console.log("staff",res)
         if(res.code == 200){
             that.staff = res.data.admins
         }
     })
  },
  methods: {
      getInfo(){
        let that = this
        getStore().then((res) => {
            if(res.code == 200){
                that.shopInfo = res.data.shop_info
                that.shopInfo.shop_cate = String(res.data.shop_info.shop_cate)
                that.getConfig()
            }
        })
      },
      searchAddress(){
         let that = this
         gatZipAddress(that.shopInfo.zip_code).then((res) => {
             console.log(res)
             if(res.code == 200){
                 that.shopInfo.province = res.data.address.address1
                 that.shopInfo.address1 = res.data.address.address1 + res.data.address.address2 + res.data.address.address3
             }
         })
      },
      getConfig(){
        let that = this
          getConfig().then((res) => {
              console.log(res)
              if(res.code == 200){
                  let array = []
                  Object.keys(res.data.config.shop_cate).map((k,item) =>{
                      let obj = {}
                      obj.key = k
                      obj.value = res.data.config.shop_cate[k]
                      array.push(obj)
                  }) 
                  console.log("array",array)
                  that.cates = array
              }
          })
      },
    wrapClick() {
      this.conserveShow = false;
    },
    selectChanged(e) {
        console.log(e)
      this.industry = e;
    },
    showClick() {
      this.show = !this.show;
    },
    introduceClick() {
        if(this.shopInfo.staff_des == 1){
            this.shopInfo.staff_des = 0
        }else{
            this.shopInfo.staff_des = 1
        }
    },
    friendClick() {
      if(this.shopInfo.share_line == 1){
          this.shopInfo.share_line = 0
      }else{
          this.shopInfo.share_line = 1
      }
    },
    linkClick() {
      if(this.shopInfo.sns_url == 1){
          this.shopInfo.sns_url = 0
      }else{
          this.shopInfo.sns_url = 1
      }
    },
    SNSClick(index) {
      this.SNSIndex = index;
    },
    handleAvatarSuccess(res, file) {
        console.log(res)
        console.log(file)
      // this.shopInfo.carousel(res.data.path);
      this.shopInfo.carousel.push(res.data.path);
    },
    delPicture(i) {
      this.shopInfo.carousel.splice(i, 1);
      // this.fileList.splice(i, 1);
    },
    //图片预览
    handlePictureCardPreview(item) {
      this.dialogImageUrl = item;
      this.dialogVisible = true;
    },
    conserveClick(){
        let that = this
        let data = that.shopInfo
        data['type'] = "info"
        data.shop_cate = that.shopInfo.shop_cate
         editShop(data).then((res) => {
             console.log(res)
             if(res.code == 200){
                 this.conserveShow = true
                 setTimeout(()=>{
                   this.conserveShow = false
                 },1500)
             }else{
                 that.$message.error(res.message)
             }
         })
    
    }
  },
};
</script>

<style scoped>
::v-deep .el-dialog {
  box-shadow: 0 0 0 0;
  background: transparent;
}
::v-deep .el-icon-close:before {
  content: "";
}
::v-deep .el-dialog__body {
  display: flex;
  align-items: center;
  justify-content: center;
}
::v-deep .el-carousel__button {
  /* // 默认按钮的颜色大小等 */
  width: 7px;
  height: 7px;
  border: none;
  border-radius: 50%;
  background: #b5b5b5;
  opacity: 1;
}

::v-deep .is-active .el-carousel__button {
  /* // 选中图片的颜色 */
  background: #000;
  opacity: 1;
}
</style>
<style lang="scss">
.wap{
    white-space: pre-wrap;
}
.mapbt{
    display: flex;
    justify-content: flex-end;
    box-sizing: border-box;
    padding: 10px 20px;
}
.el-upload .el-upload-dragger {
  width: 740px;
  height: 100px;
  border: none;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
</style>
<style lang="scss" scoped>
.head {
  width: 820px;
  padding-bottom: 20px;
  .not_tit {
    width: 820px;
    font-size: 21px;
    color: #1d1d1f;
    font-weight: bold;
  }
}

.btn_grey {
  width: 124px;
  height: 30px;
  color: #707070;
  font-weight: lighter;
  background: rgba(29, 29, 31, 0.1);
  border-radius: 15px 15px 15px 15px;
  border: 1px solid #707070;
}
.not_cont {
  width: 820px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  background: #fff !important;
}

.add-pictur {
  width: 100%;
  .pic-tit {
    font-size: 16px;
    color: #707070;
  }
  .des {
    font-size: 12px;
    color: #707070;
    margin-bottom: 10px;
  }
  .img-bos {
    .img-boxs {
      margin-bottom: 10px;
    }
    .img-item:nth-child(3n) {
      margin-right: 0 !important;
    }
    .img-item {
      margin-top: 10px;
      width: 237px;
      height: 160px;
      position: relative;
      margin-right: 9px;
      border: 1px solid #aaa;

      .colse {
        position: absolute;
        right: 8px;
        top: 8px;
        width: 20px;
        height: 20px;
      }
      .tu {
        width: 237px;
        height: 160px;
        object-fit: cover;
        border: 1px solid #aaaaaa;
      }
      .moren {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        margin: auto;
        width: 30px;
        height: 24px;
      }
    }
    .upload-demo {
      height: 100px;
      .el-upload {
        width: 100%;
        height: 100px;
        .el-upload-dragger {
          width: 100%;
          height: 100px;
          border: 0;
        }
        .tuo {
          height: 100px;
          width: 100%;
          background: #f7faff;
          border-radius: 10px 10px 10px 10px;
          opacity: 1;
          border: 1px solid #2699fb;
          img {
            margin-top: 25px;
            width: 30px;
            height: 24px;
          }
          .txt {
            margin-top: 10px;
            font-size: 16px;
            color: #2699fb;
          }
        }
      }
    }
    .add {
      margin-bottom: 20px;
      width: 140px;
      height: 53px;
      font-size: 14px;
      color: #2699fb;
      background: #ffffff;
      .upload-demo {
        width: 100%;
        height: 40px;

        .el-upload {
          width: 100%;
          height: 40px;
        }
      }
      ::v-deep .el-upload .el-upload-dragger {
        display: flex;
        margin-top: 20px;
        justify-content: flex-start;
        align-items: flex-start;
      }

      .zhui {
        width: 140px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: 1px solid #2699fb;

        .fu {
          font-size: 14px;
          font-weight: 700;
          color: #2699fb;
        }
      }
    }
  }
}
.grey_back {
  width: 820px;
  height: 48px;
  font-weight: bold;
  background: #f5f5f7;
  font-size: 20px;
  margin-top: 66px;
  padding-left: 40px;
  color: rgba(29, 29, 31, 0.9);
}
.basic {
  padding: 48px 0 41px 45px;
  ::v-deep .el-input__inner {
    width: 329px;
    padding-left: 10px;
    background: #f5f5f7;
    border-radius: 10px;
    border: 1px solid #707070;
  }
}
.industry {
  color: #707070;
  font-size: 16px;
}

.int {
  width: 329px;
  height: 40px;
  outline: 0;
  background: #fff;
  margin-top: 10px;
  border-radius: 10px;
  padding-left: 14px;
  font-size: 14px;
  border: 1px solid #d2d2d7;
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
}
.area {
  width: 329px;
  height: 100px;
  outline: 0;
  font-size: 18px;
  line-height: 22px;
  margin-top: 10px;
  border-radius: 10px;
  padding: 10px;
  border: 1px solid #d2d2d7;
  resize: none;
}
::-webkit-input-placeholder {
  /* Chrome/Opera/Safari */
  color: #aaa;
  font-size: 14px;
}
::-moz-placeholder {
  /* Firefox 19+ */
  color: #aaa;
  font-size: 14px;
}
:-ms-input-placeholder {
  /* IE 10+ */
  color: #aaa;
  font-size: 14px;
}
:-moz-placeholder {
  /* Firefox 18- */
  color: #aaa;
  font-size: 14px;
}
.sale {
  font-weight: bold;
  padding: 20px 0 20px 45px;
  border-top: 1px solid #d2d2d7;
  border-bottom: 1px solid #d2d2d7;
}
.radius {
  width: 16px;
  height: 16px;
  margin-right: 14px;
  border-radius: 50%;
  background: #fff;
  border: 1px solid #707070;
  position: relative;
  top: 2px;
  flex-shrink: 0;
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

.sale_grey {
  font-size: 12px;
  margin-top: 10px;
  color: #aaaaaa;
  font-weight: lighter;
}
.four {
  padding: 40px 35px 58px 45px;
  .add_btn {
    width: 100px;
    height: 40px;
    color: #fff;
    font-size: 16px;
    margin-left: 20px;
    border-radius: 10px;
    background: #1a73e8;
  }
  .int_two {
    width: 200px;
    height: 40px;
    font-size: 16px;
    outline: 0;
    background: #ffffff;
    border-radius: 10px;
    padding-left: 10px;
    border: 1px solid #d2d2d7;
  }
  ::v-deep .el-input__inner {
    width: 210px;
    padding-left: 10px;
    background: rgba(29, 29, 31, 0.05);
    border-radius: 10px;
    border: 1px solid #707070;
  }
}
.int_three {
  width: 740px;
  height: 40px;
  background: #fff;
  border-radius: 10px;
  padding-left: 14px;
  margin-top: 10px;
  border: 1px solid #d2d2d7;
}
.area_one {
  width: 740px;
  line-height: 22px;
}
.detail {
  padding: 40px 35px 60px 45px;
}
.other_set {
  padding: 20px 0 20px 45px;
  border-bottom: 1px solid #d2d2d7;
}
.other_bottom {
  padding: 20px 35px 60px 45px;
}
.footer {
  padding: 30px 0 70px 0;
  .btn_blue {
    width: 200px;
    height: 50px;
    color: #fff;
    margin: 0 auto;
    font-size: 20px;
    background: #1a73e8;
    border-radius: 10px;
  }
}
.fixed {
  position: fixed;
  top: 0%;
  left: 0%;
  width: 100vw;
  height: 100vh;
  background: rgba(29, 29, 31, 0.2);
  z-index: 10007 !important;
  .release {
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
  .zhe {
    position: absolute;
    width: 100vw;
    height: 100vh;
    background: rgba(29, 29, 31, 0.2);
  }
  .per-box {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 375px;
    height: 647px;
    // overflow: auto;
    background: #fff;
    border-radius: 20px;

    .tit {
      position: relative;
      font-size: 20px;
      font-weight: 700;
      width: 100%;
      height: 40px;
      line-height: 40px;
      text-align: center;
      background-color: #fff;
      border-radius: 20px 20px 0 0;

      img {
        position: absolute;
        left: 10px;
        top: 8px;
        width: 23px;
        height: 23px;
      }
    }

    // ::-webkit-scrollbar {
    //   display: none;
    // }
    .big_slide {
      overflow: auto;
      height: 565px;

        .items-boxs {
          .items {
            padding: 15px 20px;
            margin: 0 auto;
            border-bottom: 1px solid #d2d2d7;

            .items-fle {
              display: flex;
              align-items: flex-start;

              .left {
                font-size: 14px;
                width: 57px;
                color: #707070;
              }

              .midd {
                font-size: 14px;
                color: #1d1d1f;
                margin-left: 20px;

                .blue {
                  color: #1a73e8;
                }
              }
            }
            .ri-img {
              width: 118px;
              height: 40px;
              line-height: 40px;
              text-align: center;
              font-size: 14px;
              color: #707070;
              background: #f5f5f7;
              border-radius: 10px;
              border: 1px solid #707070;

              img {
                width: 19px;
                height: 16px;
              }

              .text {
                margin-left: 6px;
              }
            }
            .phone-btn {
              width: 100px;
              height: 34px;
              background: #1a73e8;
              font-size: 12px;
              border-radius: 8px;
              color: #ffffff;
            }
          }
        }
        .map-btn {
          width: 116px;
          height: 34px;
          line-height: 34px;
          text-align: center;
          color: #86868b;
          font-weight: 700;
          font-size: 12px;
          border: 1px solid #86868b;
          border-radius: 5px;
        }
        .map {
          width: 100%;
          height: 208px;
          background-color: #f5f5f7;
          margin-bottom: 30px;
          position: relative;

          
        }
        .zhu-item {
          border-top: 1px solid #d2d2d7;
          background: #ffffff;

          .zhu-box {
            width: 90%;
            margin: 20px auto;
            display: flex;

            .zhu-left {
              flex-shrink: 0;
              width: 56px;
              font-size: 14px;
              color: #707070;
              margin-right: 20px;
            }

            .zhu-right {
              font-size: 14px;
              color: #1d1d1f;

              .lian {
                color: #007aff;
              }
            }
          }
        }

        .zhu-item-border {
          border-bottom: 1px solid #d2d2d7;
          margin-bottom: 60px;
        }
        .staff {
          height: 46px;
          line-height: 46px;
          background: #f5f5f7;
          font-size: 20px;
          text-align: center;
          font-weight: 700;
          color: #1d1d1f;
        }

        .store-popel-box {
          background-color: #fff;
          padding: 30px 0;
          border-bottom: 1px solid #d2d2d7;

          .store-box {
            width: 90%;
            margin: 0 auto;
            display: flex;

            .store-left {
              flex-shrink: 0;
              border-radius: 4px;
              width: 139px;
              height: 185px;
              margin-right: 18px;
              position: relative;
              background: #bce0fd;

              .store-img {
                width: 100%;
                height: 100%;
                border-radius: 4px;
              }

              .store-heji {
                position: absolute;
                top: 4px;
                left: 4px;
                width: 26px;
                height: 26px;
                border-radius: 6px;
                background: rgba(29, 29, 31, 0.4);
              }
            }

            .store-right {
              color: #1d1d1f;
              font-size: 16px;

              .store-name {
                font-weight: 700;
                margin-bottom: 20px;
              }

              .store-identity {
                margin-bottom: 18px;
              }

              .store-des {
                font-size: 14px;
                color: #707070;
              }
            }
          }
        }
        .shar {
          width: 90%;
          margin: 0 auto;
          margin-top: 40px;
          margin-bottom: 22px;

          .shar-tit {
            color: #1d1d1f;
            font-size: 16px;
            text-align: center;
            font-weight: 700;
          }

          .shar-btn {
            margin-top: 12px;
            width: 100%;
            height: 56px;
            line-height: 56px;
            text-align: center;
            background: #ffffff;
            box-shadow: 0px 0px 6px 1px rgba(29, 29, 31, 0.16);
            border-radius: 6px;
            border: 1px solid #eaeaea;
            display: flex;
            align-items: center;
            justify-content: center;
            img {
              width: 36px;
              height: 36px;
              vertical-align: middle;
              margin-right: 5px;
            }

            text {
              vertical-align: middle;
              margin-left: 19px;
              color: #1d1d1f;
              font-size: 16px;
            }
          }
        }

        .shar-line {
          width: 100%;
          height: 1px;
          background-color: #eaeaea;
          margin-bottom: 40px;
        }

        .sns {
          width: 80%;
          margin: 0px auto;
          padding-bottom: 40px;

          .sns-tit {
            font-size: 16px;
            color: #1d1d1f;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
          }

          .img-sns {
            display: flex;
            justify-content: center;
            align-items: center;

            img {
              width: 36px;
              height: 36px;
              margin: 0 15px;
            }
          }
      }
    }
    .bottom {
      width: 100%;
      height: 82px;
      background: #f4f4f7;
      color: #707070;
      border-radius: 0 0 20px 20px;
      padding: 20px 15px 30px 15px;
      border-top: 1px solid rgba(0, 0, 0, 0.3);
      .bot_item {
        display: flex;
        flex-direction: column;
        align-items: center;
        .image {
          width: 30px;
          height: 30px;
        }

        .bot_text {
          font-size: 7px;
          margin-top: 5px;
        }
      }
    }
  }
}
</style>




