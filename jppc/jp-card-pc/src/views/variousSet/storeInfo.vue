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
          <!-- [カスタムエイド 2026-10-14] TOP画像：枚数の上限(6枚)をなくし、小さいサムネイル・枠内スクロール・ドラッグ並び替え・まとめてドロップに変更 -->
          <div class="cs-top-head">
            <div>
              <div class="cs-top-title">TOP画像<span class="cs-top-count">{{ (shopInfo.carousel || []).length }}枚</span></div>
              <div class="cs-top-desc">1枚目がアプリのTOPに最初に表示されます。画像をドラッグして順番を入れ替えられます。</div>
            </div>
            <button type="button" class="cs-top-addbtn" @click="openTopUpload">＋ 画像を追加</button>
          </div>
          <div class="cs-top-box" :class="{ 'is-over': topDragOver }" @dragover.prevent="onTopDragOver" @dragleave.prevent="topDragOver = false" @drop.prevent="onTopDrop">
            <div class="cs-top-scroll">
              <div class="cs-top-grid" ref="topGrid">
                <div class="cs-top-tile" v-for="(item, index) in shopInfo.carousel" :key="index" :class="{ 'is-main': index == 0 }">
                  <img :src="topUrl(item)" class="cs-top-img" alt="" draggable="false" @click="previewTop(topUrl(item))" />
                  <span class="cs-top-drag" aria-hidden="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="9" cy="6" r="1.7"></circle><circle cx="15" cy="6" r="1.7"></circle><circle cx="9" cy="12" r="1.7"></circle><circle cx="15" cy="12" r="1.7"></circle><circle cx="9" cy="18" r="1.7"></circle><circle cx="15" cy="18" r="1.7"></circle></svg>
                  </span>
                  <!-- [カスタムエイド 2026-10-22] GBPから取得したロゴ・カバーは読み取り専用・削除不可（GBP関連の修正 2026.10.16） -->
                  <span class="cs-top-gbp" v-if="gbpLabel(item)">{{ gbpLabel(item) }}・Googleから取得</span>
                  <button type="button" class="cs-top-del" v-else :aria-label="(index + 1) + '枚目を削除'" @click="delPicture(index)">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"></path></svg>
                  </button>
                  <span class="cs-top-main" v-if="index == 0">メイン</span>
                  <span class="cs-top-no">{{ index + 1 }}</span>
                </div>
                <button type="button" class="cs-top-add" @click="openTopUpload">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"></path></svg>
                  <span>追加</span>
                </button>
              </div>
            </div>
            <div class="cs-top-hint" v-if="(shopInfo.carousel || []).length > 14">枠の中をスクロールすると、すべての画像を確認できます</div>
            <div class="cs-top-hint">この枠に画像をドラッグ&amp;ドロップしても追加できます(複数枚まとめて可)</div>
          </div>
          <el-upload
            ref="topUpload"
            class="cs-top-uploader"
            accept="image/*"
            action="https://api.card-san.jp/tool/upload/file"
            :on-success="handleAvatarSuccess"
            :show-file-list="false"
            multiple
            name="file"
          ><span></span></el-upload>
        </div>
      </div>
      <!-- 基本情報 -->
      <div class="grey_back u-flex no-size">基本情報</div>
      
      <div class="basic">
          <div class="industry">プロフィール</div> <!-- [カスタムエイド 2026-10-14] 上の余白 30px→0 -->
          <textarea class="no-size" rows="8" style="width: 740px;resize: none;outline: none;margin-bottom: 30px;margin-top: 5px;box-sizing: border-box;padding: 5px;border: 1px solid #d2d2d7;border-radius: 10px;height: 100px;" type="text" placeholder="あなたのビジネスのプロフィールを入力してください。" v-model="shopInfo.des" />
        <div class="u-flex industry">
          <div style="color: #d93025">※</div>
          <div>業種(店舗情報には反映されません)</div>
        </div>
        <div style="margin-top: 5px"><!-- [カスタムエイド 2026-10-14] 10px→5px -->
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
        <div class="u-flex" style="margin-top: 5px"><!-- [カスタムエイド 2026-10-14] 10px→5px -->
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
                <img style="width: 100%; height: 100%;object-fit: cover" :src="topUrl(item)" alt="" />
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
import Sortable from "sortablejs" // [カスタムエイド 2026-10-14] TOP画像の並び替え
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
      topDragOver: false, // [カスタムエイド 2026-10-14]
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
  // [カスタムエイド 2026-10-14] TOP画像のドラッグ並び替え
  mounted() {
    this.$nextTick(() => {
      if (!this.$refs.topGrid) return
      this._topSortable = Sortable.create(this.$refs.topGrid, {
        draggable: ".cs-top-tile",
        filter: ".cs-top-del", // 削除ボタンはドラッグの対象外
        preventOnFilter: false,
        forceFallback: true, // ブラウザ標準のドラッグを使わない(画像の標準ドラッグや外側のドロップ枠と干渉しないように)
        fallbackTolerance: 4, // 4px以上動かしたらドラッグ(それ未満はクリック=拡大表示)
        animation: 150,
        onStart: () => { this._topDragging = true },
        onMove: (e) => !(e.related && e.related.classList.contains("cs-top-add")),
        onEnd: (e) => {
          setTimeout(() => { this._topDragging = false }, 50)
          const from = e.oldDraggableIndex, to = e.newDraggableIndex
          if (from == null || to == null || from === to) return
          // Sortableが動かした要素を元の位置に戻し、並び替えはデータ側で行う(Vueの再描画に任せる)
          e.from.removeChild(e.item)
          e.from.insertBefore(e.item, e.from.children[e.oldIndex] || null)
          const list = this.shopInfo.carousel
          const moved = list.splice(from, 1)[0]
          list.splice(to, 0, moved)
        }
      })
    })
  },
  beforeDestroy() {
    if (this._topSortable) this._topSortable.destroy()
  },
  methods: {
      // [カスタムエイド 2026-10-14] TOP画像の追加(ボタン・ドラッグ&ドロップ)
      previewTop(item) {
        if (this._topDragging) return // ドラッグした直後のクリックでは拡大表示しない
        this.handlePictureCardPreview(item)
      },
      openTopUpload() {
        const inner = this.$refs.topUpload && this.$refs.topUpload.$refs["upload-inner"]
        if (inner) inner.handleClick()
      },
      onTopDragOver(e) {
        const types = (e.dataTransfer && e.dataTransfer.types) || []
        this.topDragOver = Array.prototype.indexOf.call(types, "Files") !== -1
      },
      onTopDrop(e) {
        this.topDragOver = false
        const files = Array.prototype.filter.call((e.dataTransfer && e.dataTransfer.files) || [], (f) => /^image\//.test(f.type))
        if (!files.length) return
        const inner = this.$refs.topUpload && this.$refs.topUpload.$refs["upload-inner"]
        if (inner) inner.uploadFiles(files)
      },
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
      if (!res || !res.data || !res.data.path) return // [カスタムエイド 2026-10-14]
      if (!Array.isArray(this.shopInfo.carousel)) this.$set(this.shopInfo, "carousel", [])
      this.shopInfo.carousel.push(res.data.path);
    },
    delPicture(i) {
      if (this.gbpLabel(this.shopInfo.carousel[i])) return
      this.shopInfo.carousel.splice(i, 1);
      // this.fileList.splice(i, 1);
    },
    //图片预览
    handlePictureCardPreview(item) {
      this.dialogImageUrl = item;
      this.dialogVisible = true;
    },
    // [カスタムエイド 2026-10-22] TOP画像は URL 文字列、または { url, gbp_category, readonly }（GBPのロゴ・カバー。App版 shopInfo と同じ形式）
    topUrl(item) {
      return item && typeof item == "object" ? (item.url || "") : item
    },
    gbpLabel(item) {
      if (!item || typeof item != "object") return ""
      if (item.gbp_category == "PROFILE") return "ロゴ"
      if (item.gbp_category == "COVER") return "カバー"
      return item.readonly == 1 ? "Google" : ""
    },
    conserveClick(){
        let that = this
        let data = Object.assign({}, that.shopInfo)
        data['type'] = "info"
        // GBPのロゴ・カバー（読み取り専用）はサーバーが表示時に付け足すものなので送り返さない。写真は従来どおり URL の配列で送る
        if (Array.isArray(data.carousel)) data.carousel = data.carousel.filter(i => !that.gbpLabel(i)).map(i => that.topUrl(i)).filter(Boolean)
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
  margin-top: 5px; /* [カスタムエイド 2026-10-14] 項目名の下の余白 10px→5px */
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
  margin-top: 5px; /* [カスタムエイド 2026-10-14] 項目名の下の余白 10px→5px */
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
  margin-top: 5px; /* [カスタムエイド 2026-10-14] 項目名の下の余白 10px→5px */
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
  margin-top: 5px; /* [カスタムエイド 2026-10-14] 項目名の下の余白 10px→5px */
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

<!-- [カスタムエイド 2026-10-14] TOP画像の一覧 -->
<style lang="scss" scoped>
.cs-top-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 12px; }
.cs-top-title { font-size: 16px; color: #707070; }
.cs-top-count { margin-left: 10px; font-size: 14px; color: #1d1d1f; }
.cs-top-desc { margin-top: 4px; font-size: 12px; line-height: 1.7; color: #707070; }
.cs-top-addbtn { flex-shrink: 0; height: 36px; padding: 0 16px; border: 0; border-radius: 18px; background: #1a73e8; color: #fff; font-size: 14px; cursor: pointer; }
.cs-top-addbtn:hover { background: #1765cc; }
.cs-top-box { border: 1px dashed #9fc3f7; border-radius: 10px; background: #f7faff; padding: 12px; }
.cs-top-box.is-over { border-color: #1a73e8; background: #e8f0fe; }
.cs-top-scroll { max-height: 360px; overflow-y: auto; padding: 2px; }
.cs-top-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; }
.cs-top-tile { position: relative; aspect-ratio: 3 / 2; border-radius: 8px; overflow: hidden; background: #e5e5ea; }
.cs-top-tile.is-main { box-shadow: 0 0 0 2px #1a73e8; }
.cs-top-img { width: 100%; height: 100%; object-fit: cover; display: block; cursor: grab; }
.cs-top-drag, .cs-top-del { position: absolute; top: 6px; width: 26px; height: 26px; padding: 0; border: 0; display: flex; align-items: center; justify-content: center; }
.cs-top-drag { left: 6px; border-radius: 6px; background: rgba(255, 255, 255, 0.92); color: #515154; pointer-events: none; }
.cs-top-tile { cursor: grab; }
.cs-top-img { -webkit-user-drag: none; user-select: none; }
.cs-top-del { right: 6px; border-radius: 13px; background: rgba(29, 29, 31, 0.72); color: #fff; cursor: pointer; }
.cs-top-gbp { position: absolute; right: 6px; top: 8px; padding: 1px 6px; border-radius: 4px; background: rgba(0, 0, 0, 0.55); color: #fff; font-size: 10px; font-weight: bold; white-space: nowrap; }
.cs-top-main { position: absolute; left: 6px; bottom: 6px; padding: 1px 8px; border-radius: 10px; background: #1a73e8; color: #fff; font-size: 11px; font-weight: bold; }
.cs-top-no { position: absolute; right: 6px; bottom: 6px; min-width: 20px; height: 18px; padding: 0 5px; box-sizing: border-box; border-radius: 9px; background: rgba(255, 255, 255, 0.92); color: #1d1d1f; font-size: 11px; line-height: 18px; text-align: center; font-weight: bold; }
.cs-top-add { aspect-ratio: 3 / 2; border: 1.5px dashed #8ab0ee; border-radius: 8px; background: #fff; color: #1a73e8; font-size: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; cursor: pointer; }
.cs-top-add:hover { background: #f0f6ff; }
.cs-top-hint { margin-top: 10px; text-align: center; font-size: 12px; color: #707070; }
.cs-top-uploader { display: none; }
.sortable-ghost { opacity: 0.4; }
</style>
