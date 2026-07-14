<template>
  <div class="container" @click="riliShow = false">
   <!-- <div class="tit-box" v-if="shows == 2">
      <div class="left">
        <div class="tit">配信履歴</div>
        <div class="total">メッセージ数/月 <span>{{counts.message_count}}/{{counts.message_sum_count}}</span></div>
      </div>
      <div class="right shou" @click="shows = 1">メッセージ作成</div>
    </div> -->
    <div class="tit-box" v-if="shows == 1">
      <div class="left">
        <div class="tit">メッセージ作成</div>
      </div>
      <div class="yulan shou" @click="previewShow = true">Preview</div>
    </div>
    <div class="content-box no-size" v-if="shows == 2">
      <div class="top no-size">
        <div class="name" style="width: 364px">内容</div>
        <div class="name" style="width: 80px; text-align: center">
          ターゲット
        </div>
        <div class="name" style="width: 100px; text-align: center">
          配信日時
        </div>
        <div class="name" style="width: 80px">ステータス</div>
      </div>
      <div class="content no-size" v-for="(item, index) in list" :key="index">
        <div class="left">
          <img :src="item.content_type == 2? item.content : ''" v-if="item.content_type == 2" alt="" style="object-fit: cover;" />
          <span @click="showDetail(item.id)" class="shou">
            <u>{{ item.title }}</u></span
          >
        </div>
        <div class="items" v-text="`${item.receiver == 1? '全員' : '絞り込み'}`"></div>
        <div class="itemes">{{ item.order_time }}</div>
        <div class="btn-box">
          <div class="btn" v-if="item.is_send == 0">予約中</div>
          <div class="btn1" v-if="item.is_send == 1">配信済</div>
        </div>
      </div>
    </div>
    <div class="con-box no-size" v-if="shows == 1">
      <div class="title">
        <span class="red">※</span> タイトル<span class="hui"
          >(タイトルはメッセージに反映されません）</span
        >
      </div>
      <div class="inp">
        <input
          type="text"
          class="no-size"
          placeholder="タイトルを入力してください"
          v-model="title"
        />
      </div>
      <div class="items">
        <div class="lin-box">
          <div
            :class="active ? 'active no-size' : 'item1 shou no-size'"
            @click="active = true,title = '', txt = '', es = 0"
          >
            <img v-if="active == false" src="../static/message.png" alt="" />
            <img v-if="active == true" src="../static/messageA.png" alt="" />
            テキスト
          </div>
          <div
            :class="active == false ? 'active1  no-size' : 'no-size shou item2'"
            @click="active = false,title = '', txt = '',es = 0"
          >
            <img v-if="active == true" src="../static/image.png" alt="" />
            <img v-if="active == false" src="../static/imageA.png" alt="" />
            写真・画像
          </div>
        </div>
        <div class="text-box no-size" v-if="active == true">
          <textarea
            class="no-size"
            v-model="txt"
            placeholder="テキストを入力"
          ></textarea>
        </div>
        <div class="text-box no-size" v-if="active == false">
          <div class="textare no-size" style="overflow: scroll;">
              <div class="imgcontent" v-if="imageUrl">
                  <img src="../static/gClose.png" class="imgclose shou" @click.stop="imageUrl = ''" alt="" />
                  <img :src="imageUrl" alt="" style="width: 175px;object-fit: cover;" />
              </div>
            <div v-else>
                <el-upload
                  class="el-image"
                  :limit="1"
                  :show-file-list="false"
                  action="https://api.card-san.jp/tool/upload/file"
                  :on-success="handleAvatarSuccess"
                  drag
                >
              <div class="tt" style="margin-top: 80px;color:#2699fb">
                クリックorファイルをドロップ
              </div>
             
            <!--    <div class="tt-bb no-size shou">+<span>クリックorファイルをドロップ</span></div> -->
              </el-upload>
            </div>
            
          </div>
        </div>
      </div>
      <div v-if="active == true">
          <div class="mbtns">
              <div class="mbtn" :style="es == 1?'opacity:0.6' : ''" @click="setModule(1)">テンプレート①</div>
              <div class="mbtn" :style="es == 2?'opacity:0.6' : ''" @click="setModule(2)">テンプレート②</div>
          </div>
          <div class="setPriview" :style="sel == 1?'border:1px solid #1d1d1f' : ''">
              <div class="sel" :style="sel == 1?'border:1px solid #2699fb' : ''" @click="sel == 1?sel = 0 : sel = 1">
                  <div class="inner" v-if="sel == 1"></div>
              </div>
              <div class="pword" :style="sel == 1?'color:#1d1d1f;font-weight:bold' : ''">
                  Googleレビューリンクボタンの設置
              </div>
          </div>
      </div>
      <div style="color: rgb(100, 100, 100);margin-top: 20px;margin-bottom: 8px;" v-if="active == false">
          URLリンク
      </div>
      <div v-if="active == false">
          <el-input placeholder="https:" v-model="weburl"></el-input>
      </div>
      <div class="btns shou" @click="nextStep">次　へ</div>
      <!-- shows = 3 -->
    </div>
    <div class="cos-box no-size" v-if="shows == 3">
      <div class="tit">メッセージ作成</div>
      <div class="cos-box">
        <div class="top no-size">
          <div class="left">メッセージ配信設定</div>
          <div class="right">
            配信数 <span class="blue">{{cMember}}</span> <span>/{{totalMember}}</span>
          </div>
        </div>
        <div class="pei-mes no-size">
          <el-radio-group v-model="radio">
            <div><el-radio size="medium" :label="0">今すぐ配信</el-radio></div>
            <div class="ra-top">
              <el-radio size="medium" :label="1">予約配信設定</el-radio>
            </div>
          </el-radio-group>
          <div class="rili" v-if="radio == 1">
            <div style="margin-right: 20px;">
             <el-date-picker
                v-model="value1"
                type="date"
                format="yyyy/MM/dd"
                value-format="yyyy/MM/dd"
                placeholder="日付を選択してください">
              </el-date-picker>
            </div>
           <el-time-picker
               v-model="value2"
               format="HH:mm"
               value-format="HH:mm"
               placeholder="時間を選択してください">
             </el-time-picker>
          </div>
        </div>
        <div class="pei-mes no-size">
          <el-radio-group v-model="radio1" @input="groupchange">
            <div><el-radio size="medium" :label="1">全て</el-radio></div>
            <div class="ra-top">
              <el-radio size="medium" :label="2">詳細条件で絞り込む</el-radio>
            </div>
          </el-radio-group>
          <div class="items-boxs" v-if="radio1 == 2">
            <div class="items-box">
              <div class="name">誕生月</div>
              <div class="ip">
                <input
                  class="wei-input no-size shou"
                  @click="shengChangeShow = true"
                  v-model="dataMonth"
                  placeholder="誕生月を選択"
                  readonly
                />

                <div class="data no-size" v-if="shengChangeShow">
                  <div class="tit no-size">
                    <div class="t-le">※複数選択可</div>
                    <div class="t-ri shou" @click="shengChangeShow = false">
                      <img src="../static/tclose.png" alt="" />
                    </div>
                  </div>
                  <div
                    class="it no-size"
                    v-for="(item, index) in options"
                    :key="index"
                  >
                    <span
                      class="no-check shou"
                      v-if="!item.checked"
                      @click="Check(index)"
                    ></span>
                    <span
                      class="check shou"
                      v-if="item.checked"
                      @click="quChecked(index)"
                    >
                      <span></span
                    ></span>
                    {{ item.label }}
                  </div>
                </div>
              </div>
            </div>
            <div class="items-box">
              <div class="name">性別</div>
              <div class="ip">
                <input
                  class="wei-input no-size shou"
                  @click="sexChangeShow = true"
                  v-model="sex"
                  placeholder="性別を選択"
                  readonly
                />
                <div class="data no-size" v-if="sexChangeShow">
                  <div class="tit no-size">
                    <div class="t-le">※複数選択可</div>
                    <div class="t-ri shou" @click="sexChangeShow = false">
                      <img src="../static/tclose.png" alt="" />
                    </div>
                  </div>
                  <div
                    class="it no-size"
                    v-for="(item, index) in sexOptions"
                    :key="index"
                  >
                    <span
                      class="no-check shou"
                      v-if="!item.checked"
                      @click="sexCheck(index)"
                    ></span>
                    <span
                      class="check shou"
                      v-if="item.checked"
                      @click="sexquChecked(index)"
                    >
                      <span></span
                    ></span>
                    {{ item.label }}
                  </div>
                </div>
              </div>
            </div>
            <div class="items-box">
              <div class="name">前回来店日から</div>
              <div class="ip le-f">
                <div class="le-left">
                  <el-select v-model="stime" placeholder="日数を選択">
                    <el-option
                      v-for="item in timeOptions"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    >
                    </el-option>
                  </el-select>
                </div>
                <div class="le-right">
                    <div
                      class="radius"
                      :class="{ radiusCss: storeTime == 1 }"
                      @click.stop="oneClick(1)"
                    >
                      <div class="blue_dot" v-if="storeTime == 1"></div>
                    </div>
                    <div style="color: #707070; margin-right: 19px" :style="storeTime == 1?'color:#1d1d1f' : ''">以上経過</div>
                    <div
                      class="radius"
                      :class="{ radiusCss: storeTime == 2 }"
                      @click.stop="oneClick(2)"
                    >
                      <div class="blue_dot" v-if="storeTime == 2"></div>
                    </div>
                    <div style="color: #707070" :style="storeTime == 2?'color:#1d1d1f' : ''">以内</div>
                 <!-- <el-radio-group v-model="storeTime" @input="timechange">
                    <el-radio size="medium" :label="2">以上経過</el-radio>

                    <el-radio size="medium" :label="1">以内に来店</el-radio> -->
                  </el-radio-group>
                </div>
              </div>
            </div>
            <div class="items-box">
              <div class="name">来店回数</div>
              <div class="ip le-f">
                <div class="le-left">
                  <el-select v-model="cishu" placeholder="回数を選択">
                    <el-option
                      v-for="item in cishuOptions"
                      :key="item.value"
                      :label="item.label"
                      :value="item.value"
                    >
                    </el-option>
                  </el-select>
                </div>
                <div class="le-right">
                    <div
                      class="radius"
                      :class="{ radiusCss: storeCi == 2 }"
                      @click.stop="twoClick(2)"
                    >
                      <div class="blue_dot" v-if="storeCi == 2"></div>
                    </div>
                    <div style="color: #707070; margin-right: 19px" :style="storeCi == 2?'color:#1d1d1f' : ''">以上</div>
                    <div
                      class="radius"
                      :class="{ radiusCss: storeCi == 1 }"
                      @click.stop="twoClick(1)"
                    >
                      <div class="blue_dot" v-if="storeCi == 1"></div>
                    </div>
                    <div style="color: #707070" :style="storeCi == 1?'color:#1d1d1f' : ''">以内</div>
                  <!-- <el-radio-group v-model="storeCi" @input="timeschange">
                    <el-radio size="medium" :label="2">以上</el-radio>

                    <el-radio size="medium" :label="1">以内</el-radio>
                  </el-radio-group> -->
                </div>
              </div>
            </div>
          </div>
        </div>
        <div style="display: flex;align-items: center;justify-content: center;">
            <div class="pei-btn1 shou" @click="goback">戻る</div>
            <div class="pei-btn shou" @click="keep()">メッセージを配信</div>
        </div>
      
      </div>
    </div>
    <div class="fixed" v-show="peiShow">
      <div class="zhe"></div>
      <div class="box">
        <div class="title">メッセージを配信</div>
        <div class="txt" v-if="radio == 1">
          <div>このメッセージは</div>
          <div>{{tempDate}} {{tempTime}}に</div>
          <div>{{cMember}}人に配信されます。</div>
          <div>配信しますか？</div>
        </div>
        <div class="txt" v-else>
            <div>
                このメッセージは今すぐ{{cMember}}人に配信されます。<br/> 配信しますか？
            </div>
        </div>
        
        <el-button type="success" class="btn11" @click="savesData">配信する</el-button>
        <el-button @click="peiShow = false" style="background: rgba(29,29,31,0.16);color: #1d1d1f;" class="btn11">キャンセル</el-button>
      </div>
    </div>
    <div class="fixed" v-show="editShow">
      <div class="zhe"></div>
      <div class="boxs">
        <img
          class="shou"
          @click="editShow = false"
          src="../static/wclose.png"
          alt=""
        />
        <div class="tit">メッセージ詳細</div>
        <div class="item-box">
          <div class="item no-size">
            <div class="left">メッセージ内容</div>
            <div class="right">{{detailInfo.content_type == 2? '画像・写真' : 'テキスト'}}</div>
          </div>
          <div class="item no-size">
            <div class="left">配信日時</div>
            <div class="right" v-text="detailInfo.order_time"></div>
          </div>
          <div class="item no-size">
            <div class="left">配信メッセージ数</div>
            <div class="right">300</div>
          </div>
          <div class="item no-size">
            <div class="left">配信ターゲット</div>
            <div class="right">誕生月:９月</div>
          </div>
          <div class="item no-size">
            <div class="left">編集日時</div>
            <div class="right" v-text="detailInfo.order_time"></div>
          </div>
        </div>
        <div class="btn-flex">
          <div class="fle-left shou" @click="deleteItem">削　除</div>
          <div class="fle-right shou" @click="editItem">
            編　集
          </div>
        </div>
      </div>
    </div>
    <div class="fixed" v-show="keepShow">
      <div class="zhe"></div>
      <div class="sus-box">
        <img src="../static/success-hui.png" alt="" />
        <div class="keep">配信しました</div>
      </div>
    </div>
    <div class="fixed" v-show="previewShow">
      <div class="zhe"></div>
      <div class="per-box">
        <div class="tit">
          <img
            class="shou"
            @click="previewShow = false"
            src="../static/wclose.png"
            alt=""
          />
          <div class="t">プレビュー</div>
        </div>
        <div class="top-zhu">
          端末やOSによっては、実際に表示される画面と異なる場合があります。
        </div>
        <div class="preview">
          <div class="lef">
            <img :src="avat" v-if="avat" alt="">
            <img src="../static/yulan-tou.png" v-else alt="" />
          </div>
          <div class="rig" v-if="!active">
            <img :src="imageUrl" alt="" style="width: 100%;" />
          </div>
          <div class="rig" v-if="active" v-html="txt"></div>
        </div>
        
        <div class="preview">
            <div class="lef" v-if="active && sel == 1">
              <img :src="avat" v-if="avat" alt="">
              <img src="../static/yulan-tou.png" v-else alt="" />
            </div>
          <div style="width: 246px;height: 50px;border-radius: 10px;overflow: hidden;" v-if="active && sel == 1">
            <img :src="pimage" alt="" style="object-fit:cover;width: 100%;height: 100%;"/>
          </div>
        </div>
        
      </div>
    </div>
    <div class="mask" v-if="shows2 == true">
        <div class="containp" v-if="shows2 == true">
            <div class="tipContent">
                <div class="tipTitle" style="font-weight: bold;">
                    メッセージ配信
                </div>
                <div class="tipinfo">
                    このメッセージは今すぐ300人に配信されます。<br/> 配信しますか？ 
                </div>
                <div class="tipwarning" style="margin: 20px 0;">
                    配信上限数を超過しています。
                </div>
                <div class="tipinfo">
                    このメッセージを配信するには、<br/> 利用料（￥1,200）で、<br/> 更に3,000通のメッセージを配信できます。<br/> 配信しますか？
                </div>
                <div class="buttons">
                    <el-button class="btn111" @click="pays">配信する</el-button>
                    <el-button type="info" class="btn222" @click="shows2 = false">キャンセル</el-button>
                </div>
            </div>
        </div>
    </div>
    <div class="mask" v-if="shows1 == true">
        <div class="containp" v-if="shows1 == true">
            <div class="tipContent">
                <div class="tipTitle" style="font-weight: bold;">
                    メッセージ配信
                </div>
                <!-- <div>{{tempDate}} {{tempTime}}に</div>
          <div>{{cMember}}人に配信されます。</div> -->
                <div class="tipinfo">
                    このメッセージは<br/>
                    {{tempDate}} {{tempTime}}に<br/>
                    {{cMember}}人に送信されます。配信しますか？
                </div>
                <div class="tipwarning" style="margin: 20px 0;">
                    配信上限数を超過しています。
                </div>
                <div class="tipinfo">
                    このメッセージを配信するには、<br/> 利用料（￥1,200）で、<br/> 更に3,000通のメッセージを配信できます。<br/> 配信しますか？
                </div>
                <div class="buttons">
                    <el-button class="btn111" @click="pays">配信する</el-button>
                    <el-button type="info" class="btn222" @click="shows1 = false">キャンセル</el-button>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import Month from "@/components/rili";
import {getTimingList,addTimingMessage,getTimingDetail,deleteTimingItem,editTimingMessage,getExtra,sceneMember,sceen,getStore,getConfig,topay} from "@/http/api.js"
export default {
  name: "",
  components: {
    Month,
  },
  data() {
    return {
      txt: "",
      sel:0,
      number:100,
      value2:"",
      riliShow: false,
      previewShow: false,
      imageUrl: "",
      shows1:false,
      shows2:false,
      sex: "",
      sexChangeShow: false,
      shengChangeShow: false,
      keepShow: false,
      cMember:0,
      es:0,
      pimage:"",
      sexOptions: [
        {
          checked: false,
          label: "男性",
          value: 1
        },
        {
          checked: false,
          label: "女性",
          value: 2
        },
        {
          checked: false,
          label: "Xジェンダー",
          value: 3
        },
      ],
      options: [
        {
          checked: false,
          label: "1月",
          value: 1
        },
        {
          checked: false,
          label: "2月",
          value: 2
        },
        {
          checked: false,
          label: "3月",
          value: 3
        },
        {
          checked: false,
          label: "4月",
          value: 4
        },
        {
          checked: false,
          label: "5月",
          value: 5
        },
        {
          checked: false,
          label: "6月",
          value: 6
        },
        {
          checked: false,
          label: "7月",
          value: 7
        },
        {
          checked: false,
          label: "8月",
          value: 8
        },
        {
          checked: false,
          label: "9月",
          value: 9
        },
        {
          checked: false,
          label: "10月",
          value: 10
        },
        {
          checked: false,
          label: "11月",
          value: 11
        },
        {
          checked: false,
          label: "12月",
          value: 12
        },
      ],
      cishuOptions: [
        {
          value: "1回",
          label: "1回",
          value: 1
        },
        {
          value: "2回",
          label: "2回",
          value: 2
        },
        {
          value: "3回",
          label: "3回",
          value: 3
        },
        {
          value: "4回",
          label: "4回",
          value: 4
        },
        {
          value: "5回",
          label: "5回",
          value: 5
        },
        {
          value: "6回",
          label: "6回",
          value: 6
        },
        {
          value: "7回",
          label: "7回",
          value: 7
        },
        {
          value: "8回",
          label: "8回",
          value: 8
        },
        {
          value: "9回",
          label: "9回",
          value: 9
        },
        {
          value: "10回",
          label: "10回",
          value: 10
        },
      ],
      timeOptions: [
        {
          value: "7日",
          label: "7日",
          value: 7
        },
        {
          value: "14日",
          label: "14日",
          value: 14
        },
        {
          value: "30日",
          label: "30日",
          value: 30
        },
        {
          value: "60日",
          label: "60日",
          value: 60
        },
        {
          value: "90日",
          label: "90日",
          value: 90
        },
        {
          value: "180日",
          label: "180日",
          value: 180
        },
        {
          value: "1年",
          label: "1年",
          value: 365
        },
        {
          value: "2年",
          label: "2年",
          value: 730
        },
        {
          value: "3年",
          label: "3年",
          value: 1095
        },
      ],
      stime: "",
      dataMonth: "",
      // 来店日期
      storeCi: 0,
      // 来店次数
      storeTime: 0,
      cishu: "",
      // 时间
      value: "",
      time: 0,
      // 日期
      ri: 0,
      // timeInfo: {},
      title:"",
      value1:"2022/05/08",
      radio: 0,
      radio1: 1,
      editShow: false,
      peiShow: false,
      active: true,
      // 1新增 2配送历史 3修改
      shows: 2,
      list: [],
      monthIndex: 0,
      months: ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"],
      tempDate:"",
      tempTime:"",
      saveData:{},
      detailInfo:{},
      totalMember:0,
      counts:{},
      weburl:"",
      avat:"",
      m1:[],
      m2:[]
    };
  },
  created() {
    let that = this 
    if(that.$route.query.new == 1){
        if(that.$route.query.id){
            that.id = that.$route.query.id
            that.showDetail(that.$route.query.id)
        }
        that.shows = 1
    }else if(that.$route.query.new == 3){
        that.shows = 3
    }
    that.getList()
    that.getAllmember()
    that.sceen()
    getStore().then((res) => {
        console.log("ff",res)
        if(res.code == 200){
            that.counts = res.data.message_count
            that.avat = res.data.shop_info.admin1_avatar
            that.m1 = res.data.shop_info.timing_message_tpl1
            that.m2 = res.data.shop_info.timing_message_tpl2
            that.pimage = res.data.shop_info.comment_button_style_image
            // that.sel = res.data.shop_info.comment_button_style
        }
    })
    getConfig().then((res) => {
        if(res.code == 200){
            let ar = res.data.config.time_date.split(' ')
            that.value1 = ar[0]
            let temp = ar[1]
            let temp1 = temp.split(":")
            that.value2 = temp1[0]+":"+temp1[1]
        }
    })
  },
  mounted() {
    let myDate = new Date();
    this.monthIndex = myDate.getMonth();
    this.year = myDate.getFullYear();
    this.day = myDate.getDate() - 1;
  },
  methods: {
      init(){
          this.title = ''
          this.txt = ""
      },
      setModule(e){
          let that = this 
          that.es = e
          if(e == 1){
              that.title = that.m1[0]
              that.txt = that.m1[1]
          }else{
              that.title = that.m2[0]
              that.txt = that.m2[1]
          }
      },
      goback(){
          this.shows = 1
      },
      oneClick(e){
         let that = this
         if(e == that.storeTime){
             that.storeTime = 0
         }else{
             that.storeTime = e
         }
         that.sceen()
      },
      twoClick(e){
          let that = this
          if(e == that.storeCi){
              that.storeCi = 0
          }else{
              that.storeCi = e
          }
          that.sceen()
      },
      timechange(e){
        this.storeTime = 0
        console.log("e1",e)  
        console.log(this.storeTime)
        if(this.storeTime == e){
            console.log(1231231)
            this.storeTime = 0
             console.log(this.storeTime)
        }else{
            this.storeTime = e
        }
      },
      timeschange(e){
        console.log("e2",e)
      },
      groupchange(e){
        let that = this 
         console.log(e)
        if(e == 1){
            that.radio1 = 1
            that.options.forEach((val,index) => {
                val.checked = false
            })
            
            that.sexOptions.forEach((val,index) => {
                val.checked = false
            })
            that.stime = 0
            // that.storeTime = 0
            that.cishu = 0
            that.storeC = 0
            that.dataMonth = ""
            that.sex = ''
            that.sceen()
        }
      },
      nextStep(){
        let that = this
        if((that.title != '' && that.txt != '')  || (that.title != '' && that.imageUrl != '')){
            that.shows = 3
        }
        
        if(that.title == ''){
            that.$message({
            	message: 'タイトルを入力してください',
            	type: 'error',
                 offset: 400
            });
            return false
        }
        
        if(that.txt == ''){
            that.$message({
            	message: 'テキストを入力してください',
            	type: 'error',
                 offset: 400
            });
        }
      },
      getAllmember(){
          let that =this
          let data = {}
          data['birthday_month'] = ''
          data['sex'] = ''
          data['shop_time'] = ''
          data['shop_time_desc'] = ''
          data['shop_count'] = ''
          data['shop_count_desc'] = ''
          sceneMember(data).then((res) => {
              if(res.code == 200){
                  console.log("all",res)
                  if(res.code == 200){
                      that.totalMember = res.data.total
                  }
              }
          })
      },
      // sceenMember(){
      //     let that = this
      //     let csvData = uni.getStorageSync("csvData")
      //     let data = {}
      //     data['birthday_month'] = saveData.birthday_month || ''
      //     data['sex'] = saveData.sex || ''
      //     data['shop_time'] = saveData.shop_time || ''
      //     data['shop_time_desc'] = saveData.shop_time_desc || ''
      //     data['shop_count'] = saveData.shop_count || ''
      //     data['shop_count_desc'] = saveData.shop_count_desc || ''
      //     uni.showLoading({
      //         title:"Loading"
      //     })
      //     sceneMember(data).then((res) => {
      //         uni.hideLoading()
      //         if(res.code == 200){
      //             console.log(res)
      //             that.searchNumber = res.data.total
      //         }
      //     })
      // },
      
      
      sceen(){
        let that = this 
        let month = []
        let sex = []
        that.options.forEach((val,index) => {
            if(val.checked == true){
                month.push(val.value)
            }
        })
        
        that.sexOptions.forEach((val,index) => {
            if(val.checked == true){
                sex.push(val.value)
            }
        })
        let data = {}
        data['birthday_month'] = month.toString()
        data['sex'] = sex.toString()
        data['shop_time'] = that.stime * 86400
        data['shop_time_desc'] = that.storeTime
        data['shop_count'] = that.cishu 
        data['shop_count_desc'] = that.storeCi
        that.showLoading()
        sceen(data).then((res) => {
            that.closeLoading()
            if(res.code == 200){
                that.cMember = res.data.members.length
            }else{
                that.$message({
                	message: res.message,
                	type: 'error',
                     offset: 400
                });
            }
            // that.formatCsv()
        })
      },
      showLoading(){
            let that = this 
            that.Loading = that.$loading({
      　　　　spinner:"",//自定义加载图标类名
      　　　　text:"読み込み中…",//显示在加载图标下方的加载文案
      　　　　background:"rgba(0, 0, 0, 0.6)",//遮罩背景色
      　　})   
        },
        closeLoading(){
          let that = this 
          that.Loading.close()
        },
      
      
      pays(){
        let that = this
        let data = {}
        data['type'] = 1
        data['card_type'] = 1
        getExtra(data).then((res) => {
            if(res.code == 200){
                let df = {}
                df.order_no = res.data.order_no
                topay(df).then((rest) => {
                    that.shows2 = false
                    that.shows1 = false
                    if(rest.code == 200){
                        window.open(rest.data.url)
                    }else{
                        that.$message({
                        	message: rest.message,
                        	type: 'error',
                             offset: 400
                        });
                    }
                })
               
            }else{
                that.$message({
                	message: res.message,
                	type: 'error',
                     offset: 400
                });
            }
        })
      },
      confirm1(){
         let that = this
         let data = {}
         data['type'] = 2
         getExtra(data).then((res) => {
             if(res.code == 200){
                 window.open(res.data.url)
                 that.shows2 = false
                 that.shows1 = false
             }else{
                 that.$message({
                 	message: res.message,
                 	type: 'error',
                      offset: 400
                 });
             }
         }) 
      },
    editItem(){
      let that = this
      console.log("444444888",that.detailInfo)
      that.shows = 1
      that.weburl = that.detailInfo.url
      that.radio = that.detailInfo.is_order
      that.radio1 = that.detailInfo.receiver
      that.title = that.detailInfo.title
      that.active = that.detailInfo.content_type == 1? true : false
      that.imageUrl = that.detailInfo.content
      that.txt = that.detailInfo.content
      that.storeTime = that.detailInfo.shop_time_desc
      that.storeCi = that.detailInfo.shop_count_desc
      that.stime = that.detailInfo.shop_time 
      that.cishu = that.detailInfo.shop_count 
      let temp1 = that.detailInfo.birthday_month.split(",")
      let temp2 = that.detailInfo.sex.split(",")
      that.options.forEach((value,index) => {
          temp1.forEach((val,idx) => {
              if(value.value == val){
                  value.checked = true
              }
          })
      })
    
      that.sexOptions.forEach((value,index) => {
          temp2.forEach((val,idx) => {
              if(value.value == val){
                  value.checked = true
              }
          })
      })
    
    },
    getList(){
      let that = this
      getTimingList().then((res) => {
          console.log(res)
          if(res.code == 200){
              that.list = res.data.messages
          }
      })
    },
    deleteItem(){
       let that = this
       deleteTimingItem(that.detailInfo.id).then((res) => {
           if(res.code == 200){
               that.$message({
               	message: res.message,
               	type: 'success',
                    offset: 400
               });
               that.editShow = false
               that.getList()
           }else{
               that.$message({
               	message: res.message,
               	type: 'error',
                    offset: 400
               });
           }
       })
    },
    showDetail(id){
      let that = this
      getTimingDetail(id).then((res) => {
          if(res.code == 200){
              that.detailInfo = res.data.timing_message
              that.id = id
              that.editItem()
          }
      })
    },
    handleAvatarSuccess(res, file) {
      this.imageUrl = res.data.path
      this.sceen()
    },
    sexCheck(i) {
      this.sexOptions[i].checked = true;
      this.change(this.sexOptions, 2);
      this.sceen()
    },
    sexquChecked(i) {
      this.sexOptions[i].checked = false;
      this.change(this.sexOptions, 2);
      this.sceen()
    },
    Check(i) {
      this.options[i].checked = true;
      this.change(this.options, 1);
      this.sceen()
    },
    quChecked(i) {
      this.options[i].checked = false;
      this.change(this.options, 1);
      this.sceen()
    },
    change(it, dat) {
      let arr = [];
      it.forEach((item) => {
        if (item.checked) {
          arr.push(item.label);
          console.log(arr);
        }
      });
      if (dat == 1) {
        this.dataMonth = arr + "";
      } else {
        this.sex = arr + "";
      }
      this.sceen()
    },
    formatDate(e){
        console.log("e",e)
      if(e){
         let year = e.getFullYear()
         let month = (e.getMonth() + 1) < 10? '0' + (e.getMonth() + 1):e.getMonth() + 1
         let day = e.getDate() < 10? '0' +  e.getDate() : e.getDate()
         return year + "-" + month + '-' + day 
      }else{
          return " "
      }
      
    },
    
    
    formatTime(e){
        if(e){
         let hour = e.getHours() < 10 ? '0' + e.getHours() : e.getHours()
         let minutes = e.getMinutes() < 10 ? '0' + e.getMinutes() : e.getMinutes()
         
         let seconds = e.getSeconds() < 10 ? '0' + e.getSeconds() : e.getSeconds()
         
         return hour + ":" + minutes + ":" + seconds   
        }else{
            return " "
        }
        
    },
    savesData(){
       let that = this
       that.saveData['is_comment_button'] = that.sel 
       if(that.detailInfo.title){
           that.saveData['_method'] = "put"
           editTimingMessage(that.saveData,that.detailInfo.id).then((res) => {
               if(res.code == 200){
                 that.keepShow = true;
                 setTimeout(() => {
                   that.keepShow = false;
                    that.$router.push("/delivery")
                 }, 1500);  
               }else if(res.code == 4003){
                   that.shows2 = true
                   that.peiShow = false
               }else{
                   that.$message({
                   	message: res.message,
                   	type: 'error',
                        offset: 400
                   });
               }
           })
       }else{
           addTimingMessage(that.saveData).then((res) => {
               if(res.code == 200){
                 that.keepShow = true;
                 setTimeout(() => {
                   that.keepShow = false;
                   that.$router.push("/delivery")
                 }, 1500);  
               }else if(res.code == 4003){
                   that.shows1 = true
                   that.peiShow = false
               }else{
                   that.$message({
                   	message: res.message,
                   	type: 'error',
                        offset: 400
                   });
               }
           })
       }
       
    },
    keep() {
      let that = this  
      let data = {}
      let temp1 = []
      let temp2 = []
      let tempDate = that.value1
      let tempTime = that.value2
      that.tempDate = tempDate
      that.tempTime = tempTime
      that.options.forEach((value,index) => {
          console.log(value)
          if(value.checked == true) temp1.push(value.value)
      })
      that.sexOptions.forEach((value,inex) => {
           if(value.checked == true) temp2.push(value.value)
      })
      data['title'] = that.title
      data['content_type'] = that.active == true?1 : 2
      data['content'] = that.active == true? that.txt : that.imageUrl
      data['is_order'] = that.radio
      data['order_time'] = tempDate + " " + tempTime
      data['receiver'] = that.radio1
      data['birthday_month'] = temp1.toString()
      data['sex'] = temp2.toString()
      data['shop_time_desc'] = that.storeTime
      data['shop_count_desc'] = that.storeCi
      data['shop_time'] = that.stime
      data["shop_count"] = that.cishu
      data['url'] = that.weburl
      console.log(data)
      that.peiShow = true
      that.saveData = data
    },
  },
};
</script>

<style lang="scss" scoped>
    .setPriview{
        display: flex;
        align-items: center;
        width: 323px;
        height: 44px;
        border: 1px solid #AAAAAA;
        box-sizing: border-box;
        padding-left: 14px;
        font-size: 16px;
        color: #AAAAAA;
        .sel{
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 1px solid #AAAAAA;
            box-sizing: border-box;
            padding: 3px;
            margin-right: 10px;
            cursor: pointer;
            .inner{
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background: #1a73e8;
            }
            
        }
    }
    .mbtns{
        display: flex;
        align-items: center;
        margin: 20px 0;
        .mbtn{
            width: 136px;
            height: 32px;
            background: rgb(232, 232, 232);
            text-align: center;
            line-height: 32px;
            font-size: 16px;
            color: rgba(29,29,31,0.9);
            border: 1px solid #1d1d1f;
            border-radius: 12px;
            margin-right: 20px;
            cursor: pointer;
        }
        
        .mbtn:hover{
            opacity: 0.6;
        }
    }
    :v-deep .el-upload-dragger{
        width: 100% !important;
        height: 100% !important;
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
.container {
  width: 820px;
}
.items-boxs .items-box .ip .wei-input{
    color: #1d1d1f !important;
}
.imgclose{
    position: absolute;
    display: block;
    width: 20px;
    height: 20px;
    top: 0;
    right: 0;
    z-index: 11;
}
.imgcontent{
    width: 175px;
    position: relative;
}
.mask{
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, .4);
    position: fixed;
    top: 0;
    left: 0;
     z-index: 999 !important;
}

.tipinfo{
        font-size: 14upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        text-align: center;
    }
    .btn111{
        background: #34C759;
        color:#fff
    }
.btn12{
        width: 240px;
        height: 48px;
        font-size: 12px;
        display: block;
        margin: 30px auto 0;
        background: #d2d2d2;
        // line-height: 48px !important;
    }
    .btn12:hover{
        opacity: 0.6 !important;
        
    }
    .btn111,.btn222{
        width: 240px;
        height: 48px;
        font-size: 12px;
        display: block;
        margin: 30px auto 0;
        // line-height: 48px !important;
    }
.tipwarning{
        color: #d93025;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        text-align: center;
        margin: 20upx 0;
        font-size: 14upx;
    }
    
    .btn11{
        width: 240px;
        height: 48px;
        font-size: 12px;
        display: block;
        margin: 30px auto 0;
        background: #34c759;
        // line-height: 48px !important;
    }
    
    .btn11:hover{
        opacity: 0.6 !important;
    }
    
    .btn21{
        width: 240px;
        height: 48px;
        font-size:12px;
        text-align: center;
        display: block;
        margin: 30px auto 0;
        line-height: 48px;
    }
    
    .tipTitle{
        font-size: 16px;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        text-align: center;
        margin-bottom: 30px;
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
    
.tit-box {
  display: flex;
  justify-content: space-between;
  .left {
    display: flex;
    align-items: center;
    .tit {
      color: #1d1d1f;
      font-size: 21px;
      font-weight: 700;
    }
    .total {
      margin-left: 20px;
      font-size: 16px;
      color: #707070;
      span {
        margin-left: 20px;
        font-size: 16px;
        color: #1d1d1f;
      }
    }
  }
  .right {
    width: 240px;
    height: 48px;
    background: #34c759;
    border-radius: 10px;
    text-align: center;
    line-height: 48px;
    color: #ffffff;
    font-size: 16px;
  }
  .yulan {
    width: 124px;
    height: 30px;
    line-height: 30px;
    text-align: center;
    background: rgba(29, 29, 31, 0.1);
    border-radius: 15px;
    color: #707070;
    font-size: 16px;
    border: 1px solid #707070;
  }
}
.content-box {
  width: 100%;
  margin-top: 14px;
  border-top: 1px solid #d2d2d7;
  border-bottom: 1px solid #d2d2d7;
  height: 532px;
  background: #ffffff;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  overflow-y: auto;
  .top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 48px;
    padding-left: 16px;
    padding-right: 20px;
    background: rgba(29, 29, 31, 0.05);
  }
  .content {
    padding-top: 16px;
    padding-bottom: 16px;
    padding-left: 16px;
    padding-right: 20px;
    border-bottom: 1px solid #d2d2d7;
    display: flex;
    justify-content: space-between;
    align-items: center;
    .left {
      width: 364px;
      display: flex;
      align-items: center;
      img {
        width: 118px;
        height: 88px;
        background: rgba(143, 168, 248, 0.2);
        border-radius: 6px;
        margin-right: 10px;
      }
      span {
        color: #707070;
        font-size: 16px;
      }
    }
    .items {
      width: 80px;
      color: #707070;
      font-size: 16px;
      text-align: center;
    }
    .itemes {
      text-align: center;
      width: 100px;
      color: #707070;
      font-size: 16px;
    }
    .btn-box {
      width: 80px;

      .btn {
        margin: 0 auto;
        width: 48px;
        height: 24px;
        line-height: 24px;
        text-align: center;
        border-radius: 6px;
        border: 1px solid #1a73e8;
        color: #1a73e8;
        font-size: 12px;
      }
      .btn1 {
        margin: 0 auto;
        width: 48px;
        height: 24px;
        line-height: 24px;
        text-align: center;
        border-radius: 6px;
        background: #ffffff;
        color: #aaaaaa;
        font-size: 12px;
        border: 1px solid #aaaaaa;
      }
    }
  }
}
.con-box {
  padding: 40px 42px 30px 38px;
  margin-top: 24px;
  width: 100%;
  // height: 556px;
  background: #ffffff;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .title {
    font-size: 16px;
    color: #1d1d1f;
    .red {
      color: #d93025;
    }
    .hui {
      color: #aaaaaa;
    }
  }
  .inp {
    margin-top: 10px;
    margin-bottom: 40px;
    width: 100%;
    input {
      width: 100%;
      border: 0;
      outline: none;
      height: 40px;
      background: #ffffff;
      border-radius: 6px;
      padding: 0 10px;
      border: 1px solid #aaaaaa;
    }
  }
  .items {
    width: 100%;
    height: 300px;
    border: 1px solid #aaaaaa;
    .lin-box {
      width: 100%;
      height: 60px;
      background: rgba(29, 29, 31, 0.05);
      display: flex;
      align-items: center;
      .item1 {
        width: 113px;
        height: 40px;
        display: flex;
        align-items: center;
        padding: 10px;
        margin-left: 15px;
        img {
          width: 21px;
          height: 20px;
          margin-right: 8px;
        }
      }
      .item2 {
        // width: 113px;
        height: 40px;
        margin-left: 10px;
        display: flex;
        padding: 10px;
        align-items: center;
        img {
          width: 22px;
          height: 17px;
          margin-right: 8px;
        }
      }
      .active {
        width: 113px;
        height: 40px;
        background: rgba(29, 29, 31, 0.1);
        display: flex;
        padding: 10px;
        align-items: center;
        margin-left: 15px;
        img {
          width: 21px;
          height: 20px;
          margin-right: 8px;
        }
      }
      .active1 {
        // width: 113px;
        height: 40px;
        background: rgba(29, 29, 31, 0.1);
        margin-left: 10px;
        padding: 10px;
        display: flex;
        align-items: center;
        img {
          width: 22px;
          height: 17px;
          margin-right: 8px;
        }
      }
    }
    .text-box {
      height: 240px;
      width: 100%;
      padding: 19px 25px 21px 15px;
      .textare {
        width: 100%;
        height: 200px;
        background: #ffffff;
        border-radius: 10px;
        opacity: 1;
        border: 1px solid #aaaaaa;
        padding: 14px;
        .tt {
          width: 100%;
          text-align: center;
          color: #707070;
          font-size: 14px;
        }
        .el-image {
          width: 100%;
          // margin-left: 270px;
          // height: 34px;
          display: block;
          margin: 0 auto;
        }
        .tt-bb:hover{
            background: rgba(26,115,232,0.1);
        }
        .tt-bb {
          width: 240px;
          height: 34px;
          background: #ffffff;
          border-radius: 10px 10px 10px 10px;
          opacity: 1;
          border: 1px solid #2699fb;
          font-size: 20px;
          color: #2699fb;
          text-align: center;

          margin-top: 11px;
          padding-top: 5px;
          font-size: 14px;
          // padding: 10px 14px;
          span {
            margin-left: 10px;
          }
        }
      }
      textarea {
        border: 0;
        outline: none;
        resize: none;
        width: 100%;
        height: 200px;
        background: #ffffff;
        border-radius: 10px;
        opacity: 1;
        border: 1px solid #aaaaaa;
        padding: 14px;
      }
    }
  }
  .btns {
    width: 200px;
    height: 50px;
    margin: 0 auto;
    background: rgba(52, 199, 89, 1);
    border-radius: 10px;
    text-align: center;
    line-height: 50px;
    color: #ffffff;
    font-size: 16px;
    margin-top: 30px;
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
  .box {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 345px;
    height: 348px;
    background: #ffffff;
    border-radius: 10px;
    .title {
      width: 285px;
      margin: 0 auto;
      padding-top: 30px;
      text-align: center;
      color: #1d1d1f;
      font-size: 16px;
    }
    .txt {
      margin-top: 30px;
      color: #707070;
      font-size: 14px;
      width: 100%;
      text-align: center;
    }
    .btn,
    .btn1 {
      width: 240px;
      height: 48px;
      margin: 0 auto;
      line-height: 48px;
      background: #34c759;
      border-radius: 10px;
      color: #ffffff;
      font-size: 16px;
      text-align: center;
    }
    .btn1 {
      color: #707070;
      background: rgba(29, 29, 31, 0.1);
    }
    .btn {
      margin-top: 29px;
      margin-bottom: 40px;
    }
  }
}
.boxs {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 532px;
  height: 464px;
  background: #ffffff;
  border-radius: 10px;
  img {
    position: absolute;
    top: 0;
    left: 0;
    width: 30px;
    height: 30px;
  }
  .tit {
    font-size: 16px;
    width: 100%;
    height: 48px;
    line-height: 48px;
    color: #1d1d1f;
    text-align: center;
    border-bottom: 1px solid #d2d2d7;
  }
  .item-box {
    margin-top: 39px;
    .item {
      height: 46px;
      display: flex;
      align-items: center;
      margin: 0 40px;
      border-bottom: 1px solid #d2d2d7;
      .left {
        width: 118px;
        text-align: center;
        color: #1d1d1f;
        font-size: 14px;
        margin-right: 30px;
      }
      .right {
        font-size: 14px;
        color: #707070;
      }
    }
  }
  .btn-flex {
    margin: 0 40px;
    margin-top: 52px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    .fle-left,
    .fle-right {
      width: 200px;
      height: 50px;
      line-height: 50px;
      color: #ffffff;
      font-size: 16px;
      text-align: center;
      background: #d93025;
      border-radius: 10px;
    }
    .fle-right {
      background: #1a73e8;
    }
  }
}
.le-right{
    display: flex;
    align-items: center;
}
::v-deep .cos-box {
  .tit {
    font-size: 21px;
    color: #1d1d1f;
    font-weight: 700;
    margin-bottom: 28px;
  }
  .cos-box {
    width: 820px;
    height: 1066px;
    background: #ffffff;
    box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
    .top {
      height: 102px;
      padding: 0 40px;
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid #d2d2d7;
      .left {
        color: #1d1d1f;
        font-size: 21px;
        font-weight: 700;
      }
      .right {
        color: #1d1d1f;
        font-size: 21px;
        span {
          font-weight: 700;
        }
        .blue {
          margin-left: 40px;
          color: #1a73e8;
        }
      }
    }
    .pei-mes {
      width: 100%;
      // height: 221px;
      padding: 42px 39px;
      border-bottom: 1px solid #d2d2d7;
      .ra-top {
        margin-top: 44px;
      }
      .rili {
        margin-top: 22px;
        margin-right: 20px;
        display: flex;
        align-items: center;
        position: relative;
        .warp {
          position: absolute;
          top: 0px;
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
          z-index: 1000;
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
          overflow: hidden;
          .img {
            border-radius: 10px 0px 0px 10px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f5f5f7;
            opacity: 1;
            border-right: 1px solid #aaaaaa;
            // text-align: center;
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
          // border: 1px solid rgb(228, 231, 237);
          cursor: not-allowed;
        }
        .img-border {
          // border-right: 1px solid rgb(228, 231, 237) !important;
        }
        .rig {
          margin-left: 20px;
          word-wrap: break-word;
          
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
    }
  }
}
::v-deep .el-radio__input.is-checked .el-radio__inner {
  position: relative;
  width: 16px;
  height: 16px;
  background: #ffffff;
  border-radius: 50%;
  border: 1px solid #1a73e8;
  &::after {
    content: "";
    position: absolute;
    top: 7px;
    left: 7px;
    width: 10px !important;
    height: 10px !important;
    background: #1a73e8;
    border-radius: 50%;
  }
}
::v-deep .el-radio__inner {
  width: 16px !important;
  height: 16px !important;
}
::v-deep .el-radio__input.is-checked + .el-radio__label {
  color: #1d1d1f;
  font-size: 16px;
  font-weight: 700;
}
::v-deep .el-radio__label {
  font-size: 16px;
  color: #707070;
}
::v-deep .items-boxs {
  .items-box {
    margin-top: 30px;
    .name {
      font-size: 16px;
      color: #1d1d1f;
    }
    .ip {
      margin-top: 10px;
      position: relative;
      .wei-input {
        width: 343px;

        height: 40px;
        line-height: 40px;
        padding: 0 16px;
        background: rgba(29, 29, 31, 0.05);
        opacity: 1;
        color: #aaaaaa;
        font-size: 16px;
        border-radius: 10px;
        border: 1px solid #aaaaaa;
      }
      .data {
        position: absolute;
        top: 40px;
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
      input {
        border: 0;
        outline: none;
        width: 343px;
        height: 40px;
      }
    }
  }
}
::v-deep .le-f {
  display: flex;
  align-items: center;
  height: 40px;
  width: 100%;
  .le-left {
    width: 200px !important;
    margin-right: 30px;
    .el-select {
      width: 200px !important;
    }
    .el-input__inner {
      width: 200px !important;
      height: 40px;
      background: #f5f5f7;
      opacity: 1;
      border-radius: 10px;
      border: 1px solid #aaaaaa !important;
    }
  }
}
.pei-btn {
  margin-left: 30px !important;
  margin-top: 30px;
  color: #ffffff;
  font-size: 16px;
  width: 200px;
  height: 50px;
  line-height: 50px;
  text-align: center;
  background: #34c759;
  border-radius: 10px 10px 10px 10px;
}
.pei-btn1 {
  margin-top: 30px;
  color: #ffffff;
  font-size: 16px;
  width: 200px;
  height: 50px;
  line-height: 50px;
  text-align: center;
  background: #d2d2d2;
  border-radius: 10px 10px 10px 10px;
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
    color: rgb(112, 112, 112);
  }
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
  overflow: hidden;
  .tit {
    position: relative;
    font-size: 20px;
    font-weight: 700;
    border-bottom: 1px solid #d2d2d7;
    width: 100%;
    height: 40px;
    line-height: 40px;
    text-align: center;
    background-color: rgb(244, 244, 246);
    img {
      position: absolute;
      left: 5px;
      top: 5px;
      width: 30px;
      height: 30px;
    }
  }
  .top-zhu {
    margin: 0 auto;
    margin-top: 20px;
    width: 90%;
    padding: 5px;
    border-radius: 10px;
    color: #fff;
    font-size: 10px;
    background-color: rgb(112, 112, 112);
  }
  .preview {
    margin: 0 auto;
    margin-top: 40px;
    width: 90%;
    display: flex;
    .lef {
      margin-right: 9px;
      img {
        width: 38px;
        height: 37px;
        border-radius: 50%;
        object-fit: cover;
      }
    }
    .rig {
      width: 246px;
      border-radius: 10px;
      color: #1d1d1f;
      padding: 10px;
      font-size: 14px;
      background: rgba(143, 168, 248, 0.2);
      white-space: pre-wrap;
      word-break: break-word;
    }
  }
}
</style>
