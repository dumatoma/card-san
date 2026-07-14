<template>
  <div @click="wrapClick" style="user-select: none;">
    <div class="not_tit u-flex u-row-between">
      クーポン作成
      <div
        v-if="coupon_set"
        class="btn_grey shou u-flex u-row-center"
        @click.stop="previewShow = true"
      >
        Preview
      </div>
    </div>
    <div class="not_cont" v-if="coupon_set">
      <div
        style="padding: 50px 40px 69px 40px; border-bottom: 1px solid #d2d2d7"
      >
        <div class="not_top u-flex">
          <div style="color: #d93025">※</div>
          タイトル
        </div>
        <input
          class="int"
          type="text"
          placeholder-style="color:#AAAAAA;"
          placeholder="タイトルを入力"
          v-model="couponInfo.title"
        />

        <div class="add-pictur m-t-50 no-size">
          <div class="pic-tit">添付画像(最大6枚)</div>

          <div class="img-bos">
            <div class="img-boxs u-flex u-flex-wrap">
              <div
                class="img-item"
                v-for="(item, index) in couponInfo.images"
                :key="index"
                :style="{ background: item ? '' : 'rgba(0,0,0,0.07)' }"
              >
                <img
                  src="@/static/gClose.png"
                  alt=""
                  class="colse shou"
                  @click.stop="delPicture(index)"
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
                <img src="../static/edit-image.png" alt="" style="object-fit: contain;"/>
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

        <div class="detailed">詳細</div>
        <textarea class="tarea no-size" v-model="couponInfo.des"></textarea>
        <div class="couponNo">クーポン番号(英数字、最大10桁)</div>
        <input class="couponInt no-size" v-model="couponInfo.number" maxlength="10" placeholder="任意のクーポン番号を入力"/>
      </div>
      <div class="bor_pat">
        <div class="u-flex">
          <div
            class="radius"
            :class="{ radiusCss: couponInfo.is_indate == 1}"
            @click.stop="showClick"
          >
            <div class="blue_dot" v-if="couponInfo.is_indate == 1"></div>
          </div>
          <div
            style="font-size: 16px; font-weight: bold"
            :style="couponInfo.is_indate == 1 ? 'color: #1d1d1f;' : 'color: #707070;'"
          >
            クーポン有効期限
          </div>
          <br>
          
        </div>
        <div style="color: #AAA;font-size: 14px;box-sizing: border-box;padding-left: 30px;">
            オフにすると有効期限は設定されません
        </div>
        <!-- 日历 -->
        <div class="rili">
          <!-- 开始时间 -->
          <div>
            <div class="u-flex-col">
              <div class="m-b-10 text_grey">開始日</div>
              <el-date-picker
                    v-model="couponInfo.indate_start_time"
                    type="date"
                    size="large"
                    format="yyyy/MM/dd"
                    value-format="yyyy/MM/dd"
                    :disabled = "couponInfo.is_indate == 0">
              </el-date-picker>
            </div>
          </div>
          <!-- 结束时间 -->
          <div style="margin-left: 36px">
            <div class="u-flex-col">
              <div class="m-b-10 text_grey">終了日</div>
              <el-date-picker
                    v-model="couponInfo.indate_end_time"
                    type="date"
                    size="large"
                    format="yyyy/MM/dd"
                    value-format="yyyy/MM/dd"
                    :disabled = "couponInfo.is_indate == 0">
              </el-date-picker>
            </div>

            <div class="warp" v-if="riliShowTwo">
              <div class="switch">
                <div class="back shou" @click.stop="backTwo"><</div>
                <div class="forward shou" @click.stop="forwardTwo">></div>
              </div>
              <div class="le shou" @click.stop="backYearTwo"><</div>
              <div class="ri shou" @click.stop="addYearTwo">></div>
              <div
                v-for="(items, index) in months2"
                :key="index"
                class="riliSelect"
              >
                <div v-if="index == monthTwoIndex1">
                  <month
                    :monthName="items"
                    :year="year1"
                    :monthIndex="index"
                    :key="index"
                    :day="day1"
                    @choose="chooseTwo"
                  ></month>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="bor_pat">
        <div class="u-flex">
          <div
            class="radius"
            :class="{ radiusCss: frequencyShow }"
            @click.stop="frequencyClick"
          >
            <div class="blue_dot" v-if="couponInfo.count != 0"></div>
          </div>
          <div
            style="font-size: 16px; font-weight: bold"
            :style="couponInfo.count != 0 ? 'color: #1d1d1f;' : 'color: #707070;'"
          >
            利用回数制限
          </div>
        </div>
        <div style="color: #AAA;font-size: 14px;box-sizing: border-box;padding-left: 30px;">
            オフにすると利用回数は無制限になります
        </div>
        <div
          class="fre_select u-flex no-size"
          style="position: relative;left: 30px;"
          @click.stop="numClick"
          :style="
            num == '利用回数を選択' ? 'color: #707070;' : 'color: #1d1d1f;'
          "
        >
          {{ num }}
          <img src="@/static/index/arrowD.png" alt="" />
          <div class="num_sel" v-if="limitShow">
            <div
              class="num_item u-flex"
              @click.stop="numberClick(index, item)"
              :class="[index == numIndex ? 'numCss' : '']"
              v-for="(item, index) in numList"
              :key="index"
            >
              {{ item }}回
            </div>
          </div>
        </div>
      </div>
      <div class="bor_pat">
        <div class="u-flex">
          <div
            class="radius"
            :class="{ radiusCss: couponInfo.is_bar_code == 1 }"
            @click.stop="displayClick"
          >
            <div class="blue_dot" v-if="couponInfo.is_bar_code == 1"></div>
          </div>
          <div
            style="font-size: 16px; font-weight: bold"
            :style="couponInfo.is_bar_code == 1 ? 'color: #1d1d1f;' : 'color: #707070;'"
          >
            バーコードを表示
          </div>
        </div>
        <!-- <div class="display">
          JANコード番号：<input type="text" :readonly="!displayShow" />
        </div> -->
      </div>
      <div class="bor_pat">
        備考欄
        <textarea class="tarea no-size" placeholder="" v-model="couponInfo.remark"></textarea>
      </div>
      <div style="padding: 30px 0 70px 0">
        <div class="btn red shou" @click="nextStep" style="margin: 0 auto;">次　へ</div>
      </div>
    </div>
    <!-- 优惠券分配设置  クーポン配信設定 -->
    <div class="not_cont" v-else>
      <div class="member u-flex u-row-between no-size">
        <div style="font-weight: bold">クーポン配信設定</div>
        <div class="member_right u-flex">
          配信数
          <div class="u-flex u-row-right num">
            <div class="blue">{{sceneNum}}</div>
            /{{totalNum}}
          </div>
        </div>
      </div>
      <div class="publish">
        <div class="u-flex">
          <div
            class="radius"
            :class="{ radiusCss: couponInfo.is_order == 0}"
            @click.stop="publishClick(0)"
          >
            <div class="blue_dot" v-if="couponInfo.is_order == 0"></div>
          </div>
          <div
            class="text_radius"
            :style="couponInfo.is_order == 0? 'color:#1D1D1F' : 'color:#707070'"
          >
            今すぐ配信
          </div>
        </div>
        <div class="u-flex m-t-40">
          <div
            class="radius"
            :class="{ radiusCss: couponInfo.is_order == 1 }"
            @click.stop="publishClick(1)"
          >
            <div class="blue_dot" v-if="couponInfo.is_order == 1"></div>
          </div>
          <div
            class="text_radius"
            :style="couponInfo.is_order == 1 ? 'color:#1D1D1F' : 'color:#707070'"
          >
            予約配信設定
          </div>
        </div>
        <div class="rili">
         <el-date-picker
               v-model="couponInfo.order_time"
               type="datetime"
               placeholder="選択してください"
               :disabled="couponInfo.is_order == 0"
               format="yyyy/MM/dd HH:mm"
               value-format="yyyy/MM/dd HH:mm"
               class="no-today-btn"
               popper-class="hide-time">
             </el-date-picker>
        </div>
      </div>
      <div class="bor_pat">
        <div style="font-weight: bold">配信ターゲットを選択</div>
        <div class="u-flex" style="margin-top: 32px">
          <div
            class="radius"
            :class="{ radiusCss: couponInfo.receiver == 1 }"
            @click.stop="radClick(1)"
          >
            <div class="blue_dot" v-if="couponInfo.receiver == 1"></div>
          </div>
          <div
            class="text_radius"
            :style="
              couponInfo.receiver == 1 ? 'color:#1D1D1F;font-weight:bold;' : 'color:#707070'
            "
          >
            全て
          </div>
        </div>
        <div class="u-flex" style="margin-top: 32px">
          <div
            class="radius"
            :class="{ radiusCss: couponInfo.receiver == 2 }"
            @click.stop="radClick(2)"
          >
            <div class="blue_dot" v-if="couponInfo.receiver == 2"></div>
          </div>
          <div
            class="text_radius"
            :style="
              couponInfo.receiver == 2 ? 'color:#1D1D1F;font-weight:bold;' : 'color:#707070'
            "
          >
            詳細条件で絞り込む
          </div>
        </div>
        <div class="u-flex-col set_info" v-if="couponInfo.receiver == 2">
          <div class="tit_info">誕生月</div>
          <div class="ip">
            <input
              class="int shou"
              type="text"
              placeholder="誕生月を選択"
              readonly
              :value="array.join()"
              @click.stop="shengChangeShow = true"
              style="margin-top: 0;"
            />
            <div class="data no-size" v-if="shengChangeShow">
              <div class="tit no-size">
                <div class="t-le">※複数選択可</div>
                <div class="t-ri shou" @click.stop="shengChangeShow = false">
                  <img src="../static/tclose.png" alt="" />
                </div>
              </div>
              <div
                class="it no-size"
                v-for="(item, index) in options"
                :key="index"
                :style="item.checked?'color:#1d1d1f' : ''"
              >
                <span
                  class="no-check shou"
                  v-if="!item.checked"
                  @click.stop="Check(index)"
                ></span>
                <span
                  class="check shou"
                  v-if="item.checked"
                  @click.stop="quChecked(index)"
                >
                  <span></span
                ></span>
                {{ item.label }}月
              </div>
            </div>
          </div>
          <div class="tit_info">性別</div>
          <div class="ip">
            <input
              class="int shou"
              type="text"
              placeholder="性別を選択"
              readonly
              :value="array1.join()"
              @click.stop="sexChangeShow = true"
              style="margin-top: 0;"
            />

            <div class="data no-size" v-if="sexChangeShow">
              <div class="tit no-size">
                <div class="t-le">※複数選択可</div>
                <div class="t-ri shou" @click.stop="sexChangeShow = false">
                  <img src="../static/tclose.png" alt="" />
                </div>
              </div>
              <div
                class="it no-size"
                v-for="(item, index) in sexOptions"
                :key="index"
                :style="item.checked?'color:#1d1d1f' : ''"
              >
                <span
                  class="no-check shou"
                  v-if="!item.checked"
                  @click.stop="sexCheck(index)"
                ></span>
                <span
                  class="check shou"
                  v-if="item.checked"
                  @click.stop="sexquChecked(index)"
                >
                  <span></span
                ></span>
                {{ item.label == 1? '男性' : item.label == 2? '女性' : 'Xジェンダー' }}
              </div>
            </div>
          </div>
          <div class="tit_info">前回来店日から</div>
          <div class="u-flex m-t-10">
            <div class="le">
              <el-select v-model="couponInfo.shop_time" placeholder="日数を選択">
                <el-option
                  v-for="item in timeOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value*86400"
                >
                </el-option>
              </el-select>
            </div>
            <div
              class="radius m-l-30"
              :class="{ radiusCss: couponInfo.shop_time_desc == 1 }"
              @click.stop="oneClick(1)"
            >
              <div class="blue_dot" v-if="couponInfo.shop_time_desc == 1"></div>
            </div>
            <div style="color: #707070; margin-right: 19px" :style="couponInfo.shop_time_desc == 1?'color:#1d1d1f' : ''">以上経過</div>
            <div
              class="radius"
              :class="{ radiusCss: couponInfo.shop_time_desc == 2 }"
              @click.stop="oneClick(2)"
            >
              <div class="blue_dot" v-if="couponInfo.shop_time_desc == 2"></div>
            </div>
            <div style="color: #707070" :style="couponInfo.shop_time_desc == 2?'color:#1d1d1f' : ''">以内</div>
          </div>

          <div class="tit_info">来店回数</div>
          <div class="u-flex m-t-10">
            <div class="le">
              <el-select v-model="couponInfo.shop_count" placeholder="回数を選択">
                <el-option
                  v-for="item in cishuOptions"
                  :key="item.value"
                  :label="item.label"
                  :value="item.value"
                >
                </el-option>
              </el-select>
            </div>
            <div
              class="radius m-l-30"
              :class="{ radiusCss: couponInfo.shop_count_desc == 2 }"
              @click.stop="withClick(2)"
            >
              <div class="blue_dot" v-if="couponInfo.shop_count_desc == 2"></div>
            </div>
            <div style="color: #707070; margin-right: 52px" :style="couponInfo.shop_count_desc == 2?'color:#1d1d1f' : ''">以上</div>
            <div
              class="radius"
              :class="{ radiusCss: couponInfo.shop_count_desc == 1 }"
              @click.stop="withClick(1)"
            >
              <div class="blue_dot" v-if="couponInfo.shop_count_desc == 1"></div>
            </div>
            <div style="color: #707070" :style="couponInfo.shop_count_desc == 1?'color:#1d1d1f' : ''">以内</div>
          </div>
        </div>
      </div>
      <div style="padding: 30px 0 70px 0;display:flex;align-items: center;justify-content: center;">
          <div class="btn shou ho" @click.stop="coupon_set = true">
            戻 る
          </div>
        <div class="btn shou ho red" @click.stop="showmo">
          クーポンを配信
        </div>
      </div>
    </div>

    <!-- 预览 -->
    <div class="fixed" v-show="previewShow">
      <div class="zhe"></div>
      <div class="per-box">
          <img
            class="shou clo"
            @click.stop="previewShow = false"
            src="../static/index/close.png"
            alt=""
          />
          <div style="width: 100%;height: 100%;overflow-y: auto;">
              <div class="tit">
               
                <div class="t">プレビュー</div>
              </div>
              <el-carousel height="200px" indicator-position="none" arrow="never">
                <el-carousel-item v-for="(item, index) in couponInfo.images" :key="index">
                  <img style="width: 100%; height: 100%;object-fit: cover;" :src="item" alt="" />
                </el-carousel-item>
              </el-carousel>
              <div class="phone">
                <div class="ph_one" v-if="couponInfo.is_bar_code == 1">クーポン番号</div>
                <div class="ph_two" v-text="couponInfo.number"></div>
                <img src="@/static/index/ITF.png" v-if="couponInfo.is_bar_code == 1" alt="" />
                <div class="ph_three" v-if="couponInfo.is_bar_code == 1">4582333189776</div>
                <div class="ph_content" v-text="couponInfo.title"></div>
                <div class="container" v-text="couponInfo.des" style="white-space: pre-wrap;"></div>
                
                <div class="container1" v-if="couponInfo.is_indate == 1">
                  <div class="ct1">有効期限</div>
                  <div class="ct2" style="text-align: left;">{{couponInfo.indate_start_time}} ~ {{couponInfo.indate_end_time}}</div>
                </div>
                
                <div class="container1" v-if="couponInfo.count != 0">
                  <div class="ct1">利用回数</div>
                  <div class="ct2" style="text-align: left;">残り{{num}}</div>
                </div>
                
               <div class="container1">
                  <div class="ct1">備考</div>
                  <div class="ct2" style="text-align: left;white-space: pre-wrap;font-size: 14px;" v-text="couponInfo.remark"></div>
                </div>
                <div class="ph_four"></div>
              </div>
          </div>
        
      </div>
    </div>
    <!-- 分发优惠券  クーポンを配信 -->
    <div class="fixed" v-show="distributeShow">
      <div class="distribute no-size">
        <div class="dis_one">クーポンを配信</div>
        <div class="dis_two">
          このクーポンは<br />
          {{selectTime}}に<br />
          {{sceneNum}}人に配信されます。<br />
          配信しますか？
        </div>
        <div class="dis_btn btn_red shou" @click.stop='submi'>配　信</div>
        <div class="dis_btn grey shou" @click.stop = 'distributeShow = false'>キャンセル</div>
      </div>
    </div>
    <!-- 预览图片 -->
    <el-dialog :visible.sync="dialogVisible" :append-to-body="true">
      <img :src="dialogImageUrl" style="width: 70%" />
    </el-dialog>
    <div class="mask" v-if="shows1 == true || shows2 == true">
        <div class="containp" v-if="shows1 == true">
            <div class="tipContent">
                <div class="tipTitle">
                    クーポンを配信
                </div>
                <div class="tipinfo">
                    このクーポンは 今すぐ{{sceneNum}}人に配信されます。
                </div>
                <div class="tipinfo">
                    配信しますか？
                </div>
                <div class="buttons">
                    <div class="btn1 ho" style="margin-top: 52px;" @click="sub" >配信する</div/>
                    <div class="btn2 ho" @click="shows1 = false">キャンセル</div>
                </div>
            </div>
        </div>
        <div class="containp" v-if="shows2 == true">
            <div class="tipContent">
                <div class="tipTitle">
                    クーポンを配信
                </div>
                <div class="tipinfo">
                    このクーポンは
                </div>
                <div class="tipinfo">
                    今すぐ{{sceneNum}}人に配信されます。
                </div>
                <div class="tipwarning" style="margin: 40px 0;">
                    配信上限数を超過しています。
                </div>
                <div class="tipinfo">
                    このクーポンを配信するには、
                </div>
                <div class="tipinfo">
                     追加料(¥1,200)のお支払いで、
                </div>
                <div class="tipinfo">
                    更に3,000通のクーポンを配信できます。
                </div>
                <div class="tipinfo">
                    配信しますか？
                </div>
                <div class="buttons">
                    <div class="btn1 ho" @click="pays">配信する</div>
                    <div class="btn2 ho" style="margin: 30px auto 0;" @click="shows2 = false">キャンセル</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mask" v-if="maskShow" @click="maskShow = false">
        <div class="maskcontent">
            クーポンの“利用回数制限”機能を使う場合は、必ず“バーコード”が必要になります。
        </div>
    </div>
    
  </div>
</template>

<script>
import {addNewCoupon,getCouponDetail,editCoupon,getExtra,sceen,getConfig,topay} from "@/http/api.js"
export default {
  name: "",
  data() {
    return {
      array:[],
      array1:[],
      sceneNum:0,
      totalNum:0,
      maskShow:false,
      couponInfo:{
          title:'',
          images:[],
          des:"",
          number:"",
          is_indate:0,
          indate_start_time:"",
          indate_end_time:"",
          is_bar_code:0,
          count:0,
          remark:"",
          is_order:0,
          order_time:"",
          receiver:1,
          birthday_month:"",
          sex:"",
          shop_time:"",
          shop_time_desc:1,
          shop_count:"",
          shop_count_desc:2,
         
      },
      selectTime:"",
      shows1:false,
      shows2:false,
      show: false,
      releaseShow: false,
      previewShow: false,
      swiperList: [
        {
          img: require("../static/index/google.png"),
        },
        {
          img: require("../static/index/close.png"),
        },
        {
          img: require("../static/index/ITF.png"),
        },
      ],
      list1:
        "当店で取り扱いのワフックスorトリートメントのどもらかをプレゼントいたします。テキス",
      isfold: true,
      isflag: 1,
      riliShow: false,
      riliShowTwo: false,
      value1: "",
      value2: "",
      monthTwoIndex1: 0,
      frequencyShow: false,
      numList: [
        "1",
        "2",
        "3",
        "4",
        "5",
        "6",
        "7",
        "8",
        "9",
        "10",
      ],
      numIndex: 0,
      limitShow: false,
      num: "利用回数を選択",
      displayShow: false,
      coupon_set: true,
      publishShow: false,
      riliPublishShow: false,
      value3: "",
      valueTime: "",
      radShow: false,
      shengChangeShow: false,
      options: [
        {
          checked: false,
          label: "1",
        },
        {
          checked: false,
          label: "2",
        },
        {
          checked: false,
          label: "3",
        },
        {
          checked: false,
          label: "4",
        },
        {
          checked: false,
          label: "5",
        },
        {
          checked: false,
          label: "6",
        },
        {
          checked: false,
          label: "7",
        },
        {
          checked: false,
          label: "8",
        },
        {
          checked: false,
          label: "9",
        },
        {
          checked: false,
          label: "10",
        },
        {
          checked: false,
          label: "11",
        },
        {
          checked: false,
          label: "12",
        },
      ],
      sexChangeShow: false,
      sexOptions: [
        {
          checked: false,
          label: 1,
          // label: "男性",
        },
        {
          checked: false,
          label: 2,
          // label: "女性",
        },
        {
          checked: false,
          label: 3,
          // label: "Xジェンダー",
        },
      ],
      fre_num: "日数を選択",
      freShow: false,
      timeOptions: [
        {
          value: "7",
          label: "7日",
        },
        {
          value: "14",
          label: "14日",
        },
        {
          value: "30",
          label: "30日",
        },
        {
          value: "60",
          label: "60日",
        },
        {
          value: "90",
          label: "90日",
        },
        {
          value: "180",
          label: "180日",
        },
        {
          value: "365",
          label: "1年",
        },
        {
          value: "730",
          label: "2年",
        },
        {
          value: "1095",
          label: "3年",
        },
      ],
      oneShow: false,
      twoShow: false,
      stime: "",
      withShow: true,
      cishuOptions: [
        {
          value: "1",
          label: "1回",
        },
        {
          value: "2",
          label: "2回",
        },
        {
          value: "3",
          label: "3回",
        },
        {
          value: "4",
          label: "4回",
        },
        {
          value: "5",
          label: "5回",
        },
        {
          value: "6",
          label: "6回",
        },
        {
          value: "7",
          label: "7回",
        },
        {
          value: "8",
          label: "8回",
        },
        {
          value: "9",
          label: "9回",
        },
        {
          value: "10",
          label: "10回",
        },
      ],
      cishu: "",
      distributeShow: false,
      imgList: [],
      fileList: [],
      dialogImageUrl: "",
      dialogVisible: false,
      query:{},
      sceenData:{
          birthday_month:"",
          sex:"",
          shop_time:"",
          shop_time_desc:1,
          shop_count:"",
          shop_count_desc:2
      }
    };
  },
  created() {
    let that = this 
    that.scenemember("ta")
    let query = that.$route.query
    that.query = query
    if(query.id == '000'){
        var myDate = new Date();
        console.log("myDate",myDate.toLocaleDateString())
        this.couponInfo.indate_start_time = myDate.toLocaleDateString();
        this.couponInfo.indate_end_time = myDate.toLocaleDateString();
        this.time = myDate.getHours() + ":" + myDate.getMinutes() + ":" + myDate.getSeconds();
    }else{
        getCouponDetail(query.id).then((res) => {
            console.log(res)
            if(res.code == 200){
                that.couponInfo = res.data.coupon
                that.num = res.data.coupon.count + '回'
                that.couponInfo.indate_start_time = that.formatWeek(that.couponInfo.indate_start_time)
                that.couponInfo.indate_end_time = that.formatWeek(that.couponInfo.indate_end_time)
                console.log(that.couponInfo)
            }else{
                that.$message.error(res.message)
            }
        })
    }
    
  },
  methods: {
      showmo(){
        let that = this
        if(that.couponInfo.is_order == 0){
            that.shows1 =  true
        }else{
            that.distributeShow = true  
        }
        
        if(that.couponInfo.order_time){
            that.selectTime = that.formatDate(that.couponInfo.order_time,"time")
        }else{
            getConfig().then((res) => {
                console.log("confifg",res)
                if(res.code == 200){
                    that.selectTime = that.formatDate(res.data.config.timestamp*1000,"time")
                }
            })
            
        }
        
      },
      scenemember(e){
          let that = this
          let data = {}
          data.birthday_month = that.couponInfo.birthday_month
          data.sex = that.couponInfo.sex
          data.shop_time = that.couponInfo.shop_time
          data.shop_time_desc = that.couponInfo.shop_time_desc
           data.shop_count = that.couponInfo.shop_count
           data.shop_count_desc = that.couponInfo.shop_count_desc
          sceen(data).then((res) => {
              console.log("members",res)
              if(res.code == 200){
                  if(e == 'ta'){
                    that.totalNum = res.data.total
                  }
                  that.sceneNum = res.data.members.length
              }
          })
      },
      pays(){
        let that = this
        let data = {}
        data['type'] = 2
        data['card_type'] = 1
        getExtra(data).then((res) => {
            if(res.code == 200){
                let  temp = {}
                temp.order_no = res.data.order_no
                topay(temp).then((rest) => {
                    if(rest.code == 200){
                        window.open(rest.data.url)
                    }
                })
                that.shows2 = false
                that.shows1 = false
            }else{
                that.shows2 = false
                that.shows1 = false
                that.$message.error(res.message)
            }
        })
      },
      changeDate(e){
        console.log(this.value1)
      },
      formatWeek(dates){
         let temp = dates.split("(")
         return '20'+temp[0]
      },
      submi(){
          let that = this
          let data = that.couponInfo
          data.indate_start_time = that.formatDate(data.indate_start_time,"date")
          data.indate_end_time = that.formatDate(data.indate_end_time,"date")
          data.order_time = that.formatDate(data.order_time,"time")
          if(that.query.id == '000'){
              addNewCoupon(data).then((res) => {
                  console.log(res)
                  if(res.code == 200){
                      that.$message({
                          message: res.message,
                          type: 'success',
                          offset:400
                      })
                  }else if(res.code == 4003){
                        that.shows1 = false
                        that.shows2 = true
                  }else{
                      that.$message.error(res.message);
                  }
                  setTimeout(() => {
                      this.$router.push({
                        path: "/coupon",
                      });
                  },1500)
              }) 
          }else{
              data['_method'] = "put"
             editCoupon(data,that.query.id).then((res) => {
                 if(res.code == 200){
                     that.$message({
                         message: res.message,
                         type: 'success'
                     })
                 }else if(res.code == 4003){
                        that.shows1 = false
                        that.shows2 = true
                } else{
                     that.$message.error(res.message);
                 }
                 setTimeout(() => {
                     this.$router.push({
                       path: "/coupon",
                     });
                 },1500)
             })
          }
          
      },
      sub(){
         let that = this
         console.log(that.couponInfo.is_order)
         that.distributeShow = false
         if(that.couponInfo.is_order == 1){
            that.distributeShow = true 
            console.log(that.distributeShow)
         }else{
            let data = that.couponInfo
            data.indate_start_time = that.formatDate(data.indate_start_time,"date")
            data.indate_end_time = that.formatDate(data.indate_end_time,"date")
            if(that.query.id == '000'){
                console.log(data)
                addNewCoupon(data).then((res) => {
                    if(res.code == 200){
                        that.$message({
                            message: res.message,
                            type: 'success',
                            offset:400
                        })
                        that.shows1 = false
                        setTimeout(() => {
                            this.$router.push({
                              path: "/coupon",
                            });
                        },1500)
                    }else if(res.code == 4003){
                        that.shows1 = false
                        that.shows2 = true
                    }else{
                        that.$message.error(res.message);
                    }
                    // setTimeout(() => {
                    //     this.$router.push({
                    //       path: "/coupon",
                    //     });
                    // },1500)
                }) 
            }else{
                data['_method'] = "put"
                editCoupon(data,that.query.id).then((res) => {
                    if(res.code == 200){
                        that.$message({
                            message: res.message,
                            type: 'success',
                            offset:400
                        })
                        setTimeout(() => {
                            this.$router.push({
                              path: "/coupon",
                            });
                        },1500)
                    }else if(res.code == 4003){
                        that.shows1 = false
                        that.shows2 = true
                    }else{
                        that.$message.error(res.message);
                    }
                    setTimeout(() => {
                        this.$router.push({
                          path: "/coupon",
                        });
                    },1500)
                })
            }
            
         }  
      },
      nextStep(){
        if(this.couponInfo.title == ''){
            this.$message({
                message: 'タイトルを入力してください',
                type: 'error',
                offset: 400
            })
        }else{
           this.coupon_set = !this.coupon_set 
        }
        
      },
      
    goSet() {
      this.$router.push({
        path: "/noticeSet",
      });
    },
    showClick() {
        if(this.couponInfo.is_indate == 0){
            this.couponInfo.is_indate = 1
        }else{
            this.couponInfo.is_indate = 0
        }
        // this.scenemember()
    },
    frequencyClick() {
        if(this.couponInfo.count == 0){
            this.couponInfo.count = -1
            this.couponInfo.is_bar_code = 1
        }else{
            this.couponInfo.count = 0
            this.num = '利用回数を選択'
        }
      this.limitShow = false;
      this.scenemember()
    },
    displayClick() {
        if(this.couponInfo.count == 0){
            if(this.couponInfo.is_bar_code == 0){
                this.couponInfo.is_bar_code = 1
            }else{
                this.couponInfo.is_bar_code = 0
            }
        }else{
            this.maskShow = true
        }
    },
    handleAvatarSuccess(res, file) {
      this.couponInfo.images.push(res.data.path);
      this.fileList.push({ url: res.data.path });
      this.scenemember()
    },
    delPicture(i) {
        console.log("i="+i)
      this.couponInfo.images.splice(i, 1);
      this.fileList.splice(i, 1);
    },
    open() {
      this.isfold = !this.isfold;
      this.isfold === true ? (this.isflag = 1) : (this.isflag = 2);
    },
   
    numClick() {
      if (this.couponInfo.count != 0) {
        this.limitShow = true;
      }
    },
    numberClick(index, item) {
        console.log(item)
      this.num = item+"回";
      this.numIndex = index;
      this.limitShow = false;
      this.couponInfo.count = item
    },
    wrapClick() {
      this.releaseShow = false;
      this.limitShow = false;
      this.shengChangeShow = false;
      this.sexChangeShow = false;
      this.distributeShow = false;
    },
    formatDate(dates,type){
      let that = this
      console.log("dates",dates)
      let now = new Date(dates);
      let year = now.getFullYear(); //得到年份
      let month = now.getMonth(); //得到月份
      let date = now.getDate(); //得到日期
      let hours = now.getHours();                   // 获取当前小时数(0-23)
      let minutes = now.getMinutes();                 // 获取当前分钟数(0-59)
      let seconds = now.getSeconds();
      let time = ''
      month = month + 1;
      if (month < 10) month = "0" + month;
      if (date < 10) date = "0" + date;
      if (hours < 10) hours = "0" + hours
      if (minutes < 10) minutes = "0" + minutes
      if (seconds < 10) seconds = "0" + seconds
      if(type == 'date'){
          time = year + "-" + month + "-" + date;
      }
      if(type == 'time'){
          time = year + "-" + month + "-" + date + " " + hours + ":" + minutes;
      }
      
      return time
    },
    publishClick(e) {
      this.couponInfo.is_order = e
    },
    radClick(e) {
      this.couponInfo.receiver = e
    },
    // 生日
    Check(i) {
      let array = []
      this.options[i].checked = true;
      this.options.forEach((v,i) => {
          if(v.checked == true){
              array.push(v.label+"月")
          }
      })
      this.array = array
      this.change(this.options, 1);
      this.scenemember()
    },
    quChecked(i) {
         let array = []
      this.options[i].checked = false;
      this.options.forEach((v,i) => {
          if(v.checked == true){
              array.push(v.label+"月")
          }
      })
      this.array = array
      this.change(this.options, 1);
      this.scenemember()
    },
    change(it, dat) {
      let arr = [];
      it.forEach((item) => {
        if (item.checked) {
          arr.push(item.label);
        }
      });
      
      if (dat == 1) {
        this.couponInfo.birthday_month = arr.toString()
      } else {
        this.couponInfo.sex = arr.toString()
      }
    },
    // 性别
    sexCheck(i) {
      let array = []
      let temp = []
      console.log("i",i)
      this.sexOptions[i].checked = true;
      this.sexOptions.forEach((v,i) => {
          if(v.checked == true){
              if(v.label == 1){
                  array.push("男性")
                  temp.push(1)
              }else if(v.label == 2){
                  array.push("女性")
                  temp.push(2)
              }else{
                  array.push("Xジェンダー")
                  temp.push(3)
              }
          }
      })
      this.array1 = array
      this.change(this.sexOptions, 2);
      this.scenemember()
    },
    sexquChecked(i) {
      console.log("i2",i)
      let array = []
      this.sexOptions[i].checked = false;
      this.sexOptions.forEach((v,i) => {
          if(v.checked == true){
              if(v.label == 1){
                  array.push("男性")
              }else if(v.label == 2){
                  array.push("女性")
              }else{
                  array.push("Xジェンダー")
              }
          }
      })
      this.array1 = array
      this.change(this.sexOptions, 2);
      this.scenemember()
    },
    oneClick(e) {
      if(this.couponInfo.shop_time_desc == e){
          this.couponInfo.shop_time_desc = ""
      }else{
          this.couponInfo.shop_time_desc = e;
      }
      this.scenemember()
    },
    twoClick() {
      this.twoShow = !this.twoShow;
      this.scenemember()
    },
    withClick(e) {
        if(this.couponInfo.shop_count_desc == e){
            this.couponInfo.shop_count_desc = ""
        }else{
            this.couponInfo.shop_count_desc = e
        }
      
      this.scenemember()
    },
    //图片预览
    handlePictureCardPreview(item) {
      this.dialogImageUrl = item;
      this.dialogVisible = true;
    },
  },
};
</script>
<style>

  ::v-deep .hide-time .el-picker-panel__footer{
      display: none !important;
 }

.ct1{
    width: 76px;
    height: 26px;
    background: #F5F5F7;
    border-radius: 10px;
    border: 1px solid #707070;
    text-align: center;
    line-height: 26px;
    color: #707070;
    font-size: 14px;
    text-align: center;
}
.container1{
    border-bottom: 1px solid #D2D2D7;
    box-sizing: border-box;
    padding: 21px 0;
}
.clo{
    position: absolute;
    width: 30px;
    height: 30px;
    left: -10px;
    top: -10px;
    z-index: 99 !important;
}
 .ct2{
     font-size: 16px;
     color: #1D1D1F;
     text-align: left !important;
     margin-top: 10px;
 }   
.mask{
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, .4);
    position: fixed;
    top: 0;
    left: 0;
     z-index: 10999 !important;
}

.maskcontent{
    width: 240px;
    padding:20px;
    background: rgb(246,246,246);
    border-radius: 15px;
    position: absolute;
    left:50%;
    top:50%;
    transform: translate(-50%,-50%);
    color: rgb(221, 83, 74);
}
.ho:hover{
    opacity: 0.6 !important;
    cursor: pointer;
}
.taxe{
    width: 100%;
    background: transparent;
    display: block;
    resize: none;
    border: none !important;
    outline: none !important;
    height: 150px;
    /* height: auto; */
}
.btn1{
        width: 240px;
        height: 48px;
        background: #D93025;
        border-radius: 6px 6px 6px 6px;
        text-align: center;
        line-height: 48px;
        font-size: 16px;
        color: #FFFFFF;
        font-weight: bold;
    }
    
    .btn2{
        width: 240px;
        height: 48px;
        background: rgba(29,29,31,0.2);
        border-radius: 10px 10px 10px 10px;
        text-align: center;
        line-height: 48px;
        font-size: 16px;
        color: #707070;
        font-weight: bold;
        margin-top: 30px;
    }
    
.tipinfo{
        font-size: 14upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        text-align: center;
    }

.tipwarning{
        color: #d93025;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        text-align: center;
        margin: 20upx 0;
        font-size: 14upx;
    }
    
    .btn1{
        width: 240px;
        height: 48px;
        font-size: 12px;
        display: block;
        margin: 30px auto 0;
        background: #D93025;
    }
    
    .btn2{
        width: 240px;
        height: 48px;
        font-size:12px;
        text-align: center;
        display: block;
        margin: 30px auto 0;
    }
    
    .tipTitle{
      font-size: 16px;
      color: #1D1D1F;
      font-weight: bold;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .tipinfo{
        font-size: 14px;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        text-align: center;
    }
    
    .tipContent{
        width: 345px;
        background: #fff;
        border-radius: 10px;
        box-sizing: border-box;
        padding: 30px 10px;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
    }
    
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
<style lang="scss" scoped>
::v-deep .el-input__inner{
    background-color: rgba(29, 29, 31, 0.05) !important;
}
 
::v-deep .el-select-dropdown__item{
 color: #AAAAAA !important;
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
.el-upload--picture-card {
  width: 240px;
  height: 160px;
  margin-bottom: 10px;
  background: rgba(0, 0, 0, 0.07);
  border: 1px solid #aaa;
  position: relative;
  border-radius: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  .error {
    width: 20px;
    height: 20px;
    font-size: 19px;
    color: #fff;
    border-radius: 50%;
    background: rgba(29, 29, 31, 0.5);
    position: absolute;
    top: 8px;
    right: 8px;
  }
}
</style>
<style lang="scss" scoped>
::v-deep .el-upload .el-upload-dragger {
  display: flex;
  justify-content: flex-start;
  align-items: flex-start;
  height: 102px !important;
  width: 740px !important;
}
.not_tit {
  width: 820px;
  font-size: 16px;
  color: #1d1d1f;
  font-weight: bold;
  padding: 0px 0 22px 0;

  .btn_grey {
    width: 124px;
    height: 30px;
    color: #707070;
    font-weight: lighter;
    background: rgba(29, 29, 31, 0.1);
    border-radius: 15px 15px 15px 15px;
    border: 1px solid #707070;
  }
}
.not_cont {
  width: 816px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  background: #fff !important;
  .not_top {
    font-size: 16px;
    color: #757575;
    font-weight: bold;
  }
  .int {
    width: 100%;
    outline: 0;
    margin-top: 20px;
    border: none;
    padding-bottom: 5px;
    border-bottom: 1px solid #aaa;
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
        
        .zhui:hover{
            background: rgba(26,115,232,.1);
        }
        .zhui {
          width: 140px;
          height: 40px;
          display: flex;
          align-items: center;
          justify-content: center;
          border-radius: 10px;
          margin-top: 20px;
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
  .detailed {
    margin-top: 56px;
    font-size: 16px;
    color: #86868b;
    font-weight: bold;
  }
  .tarea {
    width: 100%;
    height: 200px;
    outline: 0;
    margin-top: 10px;
    background: #ffffff;
    border-radius: 10px;
    padding: 10px;
    border: 1px solid #aaa;
  }
  textarea::-webkit-input-placeholder {
    /* WebKit browsers */
    color: #aaa;
  }
  textarea:-moz-placeholder {
    /* Mozilla Firefox 4 to 18 */
    color: #aaa;
  }
  textarea::-moz-placeholder {
    /* Mozilla Firefox 19+ */
    color: #aaa;
  }
  textarea::-ms-input-placeholder {
    /* Internet Explorer 10+ */
    color: #aaa;
  }
  .couponNo {
    font-size: 16px;
    color: #707070;
    margin-top: 50px;
  }
  .couponInt {
    width: 370px;
    height: 40px;
    outline: 0;
    margin-top: 10px;
    border-radius: 10px;
    padding-left:14px;
    border: 1px solid #aaaaaa;
  }
  .bor_pat {
    padding: 40px;
    border-bottom: 1px solid #d2d2d7;

    .display {
      color: #707070;
      font-size: 16px;
      padding: 26px 0 0 30px;
      input {
        outline: none;
        border: none;
        border-bottom: 1px solid #c6c6c6;
      }
    }
  }

  .radius {
    width: 16px;
    height: 16px;
    margin-right: 14px;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #707070;
    cursor: pointer;

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
  .btn {
    width: 200px;
    height: 50px;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #D2D2D7 ;
    color: #707070;
  }
  .left {
    color: #707070;
    background: rgba(0, 0, 0, 0.07);
  }
  .right {
    color: #fff;
    margin-left: 50px;
    background: #2699fb;
  }
  .info {
    color: #fff;
    margin: 0 auto;
    background: #34c759;
  }
  .red {
    color: #fff;
    margin-left: 30px;
    background: #d93025;
  }
  //   优惠券分配设置
  .member {
    width: 100%;
    font-size: 21px;
    padding: 40px;
    color: rgba(29, 29, 31, 0.9);
    border-bottom: 1px solid#D2D2D7;

    .right {
      color: #1d1d1f;
    }

    .num {
      width: 123px;
      margin-left: 10px;

      .blue {
        color: #1a73e8;
      }
    }
  }
  .publish {
    padding: 40px;
    border-bottom: 1px solid #e1e1e4;
    .text_radius {
      font-size: 16px;
      font-weight: bold;
    }
  }
  .fre_select {
    width: 200px;
    height: 40px;
    margin-top: 10px;
    background: rgba(249, 249, 249, 0.94);
    border-radius: 10px;
    position: relative;
    padding-left: 10px;
    font-size: 16px;
    cursor: pointer;
    border: 1px solid #aaa;
    img {
      top: 14px;
      right: 10px;
      position: absolute;
    }
    .num_sel {
      border: 1px solid pink;
      position: absolute;
      top: 40px;
      left: 0;
      width: 100px;
      // height: 370px;
      cursor: pointer;
      background: #fff;
      box-shadow: 0px 5px 10px 1px rgba(0, 0, 0, 0.25);
      border-radius: 4px;
      z-index: 9;
      border: 1px solid #eaeaea;
      .num_item {
        height: 40px;
        padding-left: 12px;
      }
      .numCss {
        color: #1a73e8;
      }
    }
  }
  .set_info {
    padding-left: 46px;

    .tit_info {
      font-size: 16px;
      color: #1d1d1f;
      margin-top: 30px;
    }
    .day {
      width: 200px;
      height: 40px;
      background: #f5f5f7;
      opacity: 1;
      border-radius: 10px;
      border: 1px solid #aaaaaa;
    }

    .ip {
      margin-top: 10px;
      position: relative;
    }

    .int {
      width: 327px;
      height: 40px;
      font-size: 16px;
      border-radius: 10px;
      padding-left: 16px;
      outline: 0;
      padding-bottom: 0;
      background: rgba(29, 29, 31, 0.05);
      border: 1px solid #aaa;

      input {
        border: none;
        background: #f5f5f7;
        outline: 0;
        font-size: 16px;
        width: 90%;
      }
    }

    .data {
      position: absolute;
      top: 50px;
      left: 0;
      width: 343px;
      // height: 540px;
      z-index: 9;
      background-color: #fff;
      box-shadow: 0px 5px 10px 1px rgba(0, 0, 0, 0.25);
      border-radius: 4px 4px 4px 4px;

      .tit {
        padding: 0 10px 0 21px;
        background: rgba(0, 0, 0, 0.07);
        display: flex;
        height: 40px;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;

        .t-le {
          font-size: 14px;
          color: #1d1d1f;
        }

        .t-ri {
          img {
            width: 24px;
            height: 24px;
          }
        }
      }

      .it {
        padding: 0 10px 0 21px;
        height: 40px;
        color: #707070;
        font-size: 14px;
        display: flex;
        align-items: center;

        .no-check {
          display: inline-block;
          width: 16px;
          height: 16px;
          background: #ffffff;
          opacity: 1;
          border-radius: 50%;
          border: 1px solid #707070;
          margin-right: 14px;
        }

        .check {
          display: inline-block;
          width: 16px;
          height: 16px;
          background: #ffffff;
          opacity: 1;
          border-radius: 50%;
          border: 1px solid #1a73e8;
          margin-right: 14px;

          span {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #1a73e8;
            margin: 3px;
          }
        }
      }
    }

    .small {
      width: 180px;
      padding: 0 10px;
    }

    .down {
      width: 12px;
      height: 12px;
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

  .release {
    width: 160px;
    height: 146px;
    background: #fff;
    border-radius: 10px;
    position: fixed;
    top: 50%;
    left: 50%;
    font-size: 16px;
    color: #707070;
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
    width: 300px;
    height: 647px;
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
        left: -13px;
        top: -14px;
        width: 30px;
        height: 30px;
      }
    }
    .phone {
      text-align: center;
      padding: 10px 15px;
      .ph_one {
        margin-top: 15px;
        font-weight: bold;
      }
      .ph_two {
        margin: 5px;
        font-size: 33px;
        color: #5094ed;
      }
      .ph_three {
        padding-bottom: 30px;
        border-bottom: 1px solid #dbdbe0;
      }

      .ph_content {
        font-weight: bold;
        font-size: 14px;
        width: 100%;
        margin-top: 10px;
        text-align: left;
        word-break: break-word;
      }
      .container {
        color: #a7a7aa;
        font-size: 12px;
        margin-top: 10px;
        text-align: left;
        padding-bottom: 20px;
        border-bottom: 1px solid #dbdbe0;
      }
    }
    .right {
      margin-left: 13px;
    }
  }
  // 分发优惠券
  .distribute {
    width: 345px;
    height: 348px;
    background: #ffffff;
    border-radius: 10px;
    position: fixed;
    top: 50%;
    left: 50%;
    font-size: 16px;
    color: #707070;
    text-align: center;
    transform: translate(-50%, -50%);
    .dis_one {
      font-size: 16px;
      color: #1d1d1f;
      margin: 30px 0;
      font-weight: bold;
    }
    .dis_two {
      font-size: 14px;
      color: #707070;
    }
    .dis_btn {
      width: 240px;
      height: 48px;
      color: #fff;
      display: flex;
      margin: 0 auto;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
    }
    .btn_red {
      margin-top: 39px;
      background: #d93025;
    }
    .grey {
      color: #707070;
      margin-top: 30px;
      background: rgba(29, 29, 31, 0.1);
    }
  }
}
// 日历样式
.rili {
  margin-top: 5px;
  display: flex;
  align-items: center;
  position: relative;
  padding-left: 32px;

  .text_grey {
    color: #707070;
  }
  .warp {
    position: absolute;
    top: 25px;
    height: 366px;
    width: 343px;
  }
  .le {
    position: absolute;
    color: #007aff;
    font-size: 24px;
    top: 60px;
    left: 10px;
    font-weight: 600;
    z-index: 999;
  }
  .ri {
    position: absolute;
    color: #007aff;
    font-size: 24px;
    top: 60px;
    left: 125px;
    font-weight: 600;
    z-index: 999;
  }
  .switch {
    top: 60px;
    right: 16px;
    // left: 11rem;
    font-weight: 600;
    position: absolute;
    display: flex;
    color: #007aff;
    font-size: 24px;
    z-index: 999;
    .forward {
      margin-left: 36px;
    }
  }
  .riliSelect {
    position: absolute;
    top: -50px;
    z-index: 989;
  }
  .lef {
    width: 335px;
    height: 40px;
    background: #ffffff;
    border-radius: 10px 10px 10px 10px;
    opacity: 1;
    border: 1px solid #aaaaaa;
    display: flex;
    align-items: center;
    color: #aaaaaa;
    font-size: 16px;
    .img {
      border-radius: 10px 0px 0px 10px;
      width: 40px;
      height: 40px;
      background: #f5f5f7;
      opacity: 1;
      border-right: 1px solid #aaa;
      margin-right: 12px;
      img {
        margin: 11px;
        width: 19px;
        height: 17px;
      }
    }
  }
  .le-bg {
    background-color: rgb(245, 247, 250);
    cursor: not-allowed;
  }
  .rig {
    margin-left: 20px;
    .el-input__prefix {
      display: none;
    }
    .el-input--prefix .el-input__inner {
      padding-left: 12px;
      border: 1px solid #aaaaaa;
      font-size: 16px;
    }
    .el-date-editor.el-input {
      width: 150px;
    }
    .el-input__inner {
      border-radius: 10px;
    }
  }
}

::v-deep .el-input__inner {
  // width: 150px !important;
  height: 40px;
  opacity: 1;
  border-radius: 10px;
  border: 1px solid #aaaaaa !important;
}
</style>
