<template>
  <div @click="releaseShow = false">
    <div class="not_tit u-flex u-row-between">
      <span v-if="query.type != 'three'">メンバーズカード > スタンプカード作成・編集</span> 
      <span v-else>メンバーズカード > ポイントカード作成・編集</span>
      <div
        class="btn_grey shou u-flex u-row-center"
        @click.stop="showpreview"
      >
        Preview
      </div>
    </div>
    <div class="not_cont no-size">
      <div v-if="query.type != 'three'">
        <div class="tit u-flex">デザイン・詳細設定</div>
        <div style="padding: 30px 0 0 39px">
          <div class="cardY" :style="`background:${form.color}`" v-if="query.type == 'one'">
            <div class="cardInner">
              <div
                class="seal u-flex u-row-center"
                v-for="(item, index) in choice"
                :key="index"
                style="position: relative;"
              >
                <div class="day">{{ index + 1 }}</div>
                <div class="sealItem">
                    <!-- <img :src="selectIcon" /> -->
                    <!-- <img :src="selectIcon" v-if="!temShow" :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${form.colorTwo});`" alt=""> -->
                    <div v-if="!temShow && temIndex == 0" :style="`backgroundImage: url(${svgData11})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 1" :style="`backgroundImage: url(${svgData12})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 2" :style="`backgroundImage: url(${svgData13})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 3" :style="`backgroundImage: url(${svgData14})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 4" :style="`backgroundImage: url(${svgData15})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 5" :style="`backgroundImage: url(${svgData16})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 6" :style="`backgroundImage: url(${svgData17})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 7" :style="`backgroundImage: url(${svgData18})`" class="icon1"></div>
                    <img :src="diyImage" v-if="temShow" style="object-fit: cover;width: 40px;height: 40px;">
                    <div class="selectName">YY/MM/DD</div>
                </div>  
              </div>
            </div>
          </div>
          
          <div class="cardY" :style="`background:${form.color}`" v-if="query.type == 'two'">
            <div class="cardInner" style="height: 164px;position: relative;overflow-y: auto;">
                <div class="sealTitle" style="border-bottom: 1px solid #d2d2d7;position: sticky;top: 0;z-index: 67;">
                    <div class="st1">NO</div>
                    <div class="st2">日付</div>
                    <div class="st3">
                        <img src="../../static/corent.png" alt="">
                    </div>
                    <div class="st4">
                        <img src="../../static/svg/user.svg" alt="">
                    </div>
                    <div class="st5"></div>
                </div>
              <div
                class="u-flex u-row-center seals"
                v-for="(item, index) in choice"
                :key="index"
                style="position: relative;"
              >
                <!-- <div class="day">{{ index + 1 }}</div>
                <div class="sealItem">
                    <img :src="selectIcon" /> 
                    <div class="selectName">22/20</div>
                </div>  -->
               <div class="sti1" v-text="index + 1"></div>
               <div class="sti2">YY/MM/DD</div>
               <div class="sti3">00:00</div>
               <div class="sti4">担当者</div>
               <div class="sti5">
                   <div v-if="!temShow && temIndex == 0" :style="`backgroundImage: url(${svgData11})`" class="icon3"></div>
                   <div v-if="!temShow && temIndex == 1" :style="`backgroundImage: url(${svgData12})`" class="icon3"></div>
                   <div v-if="!temShow && temIndex == 2" :style="`backgroundImage: url(${svgData13})`" class="icon3"></div>
                   <div v-if="!temShow && temIndex == 3" :style="`backgroundImage: url(${svgData14})`" class="icon3"></div>
                   <div v-if="!temShow && temIndex == 4" :style="`backgroundImage: url(${svgData15})`" class="icon3"></div>
                   <div v-if="!temShow && temIndex == 5" :style="`backgroundImage: url(${svgData16})`" class="icon3"></div>
                   <div v-if="!temShow && temIndex == 6" :style="`backgroundImage: url(${svgData17})`" class="icon3"></div>
                   <div v-if="!temShow && temIndex == 7" :style="`backgroundImage: url(${svgData18})`" class="icon3"></div>
                    <img :src="diyImage" v-if="temShow" style="object-fit: cover;width: 40px;height: 40px;" alt="" />
               </div>
              </div>
            </div>
          </div>
          <div class="u-flex m-t-40">
            <div class="text_red">※</div>
            <div class="text_grey">スタンプカード名</div>
          </div>
          <input
            type="text"
            class="int"
            placeholder="店舗名もしくはメンバーズカード名を入力"
            v-model="cardname"
          />
          <div class="u-flex m-t-40" v-if="query.type != 'three'">
            <div class="color-70">カードの背景色を選択</div>
            <div :style="`background:${form.color};width: 100px;height: 40px;border-radius: 6px;box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);position:relative;margin-left:15px`" >
                <el-color-picker
                  v-model="form.color"
                  show-alpha
                  :predefine="predefineColors"
                  style="position: absolute;left: 0;top:0;z-index: 9 !important;border: none !important;"
                  @change="colorChange1"
                  >
                </el-color-picker>
            </div>
            
           <!-- <colorPicker
              class="shou"
              v-model="form.color"
              @change="handleChangeColor"
              size="5"
            ></colorPicker> -->
          </div>
          <div class="seal u-flex m-t-30" v-if="query.type != 'three'">
            <div class="text_black color-70">スタンプ数</div>
            <el-select v-model="choice" placeholder="">
              <el-option
                v-for="item in 30"
                :key="item"
                :label="item"
                :value="item"
              >
              </el-option>
            </el-select>
            <div class="m-l-10 m-r-20">個</div>

            <div class="text_red font-12">※</div>
            <div class="text_grey font-12">
              ３〜３０の個数を設定してください。
            </div>
          </div>
        </div>
        <div class="tit u-flex m-t-60">スタンプデザイン</div>
        <div style="padding: 40px 0 0 45px" v-if="query.type != 'three'">
          <div class="u-flex-col u-col-center" style="width: 120px">
            <div class="select" style="display: flex;justify-content: center;align-items: center;">
                <!-- <img :src="selectIcon" alt="" :style="`transform: translate(-10000px);
        filter: drop-shadow(10000px 0 0 ${form.colorTwo});`" > -->
                <div v-if="temIndex == 0" :style="`backgroundImage: url(${svgData11})`" class="icon4" ></div>
                <div v-if="temIndex == 1" :style="`backgroundImage: url(${svgData12})`" class="icon4" ></div>
                <div v-if="temIndex == 2" :style="`backgroundImage: url(${svgData13})`" class="icon4" ></div>
                <div v-if="temIndex == 3" :style="`backgroundImage: url(${svgData14})`" class="icon4" ></div>
                <div v-if="temIndex == 4" :style="`backgroundImage: url(${svgData15})`" class="icon4" ></div>
                <div v-if="temIndex == 5" :style="`backgroundImage: url(${svgData16})`" class="icon4" ></div>
                <div v-if="temIndex == 6" :style="`backgroundImage: url(${svgData17})`" class="icon4" ></div>
                <div v-if="temIndex == 7" :style="`backgroundImage: url(${svgData18})`" class="icon4" ></div>
            </div>
            <div class="select_text">選択中のデザイン</div>
          </div>
          <div class="u-flex template m-t-40">
            <div class="tem_sign" v-if="temShow" @click.stop="temClick"></div>
            <img
              src="@/static/index/select.png"
              v-else
              @click.stop="temClick"
            />
            <div
              class="tem_text m-l-10"
              :class="[!temShow ? 'rad_black' : 'rad_grey']"
            >
              テンプレートから選択
            </div>
          </div>
          <div class="u-flex m-t-40">
              <!--  v-for="(item, index) in temList"
              :key="index"
               -->
              
            <div class="tem_item u-flex-col u-col-center m-r-20" @click.stop="radiuClick(0)">
              <div :style="`backgroundImage: url(${svgData11})`" class="icon2" ></div>
              <div
                class="radius shou"
                :class="{ radiusCss: 0 == temIndex }"
                style="margin-top: 16px"
              >
                <div class="blue_dot" v-if="0 == temIndex"></div>
              </div>
            </div>
            
            <div class="tem_item u-flex-col u-col-center m-r-20"  @click.stop="radiuClick(1)">
              <div :style="`backgroundImage: url(${svgData12})`" class="icon2" ></div>
              <div
                class="radius shou"
                :class="{ radiusCss: 1 == temIndex }"
                style="margin-top: 16px"
              >
                <div class="blue_dot" v-if="1 == temIndex"></div>
              </div>
            </div>
            
            <div class="tem_item u-flex-col u-col-center m-r-20"  @click.stop="radiuClick(2)">
              <div :style="`backgroundImage: url(${svgData13})`" class="icon2" ></div>
              <div
                class="radius shou"
                :class="{ radiusCss: 2 == temIndex }"
                style="margin-top: 16px"
              >
                <div class="blue_dot" v-if="2 == temIndex"></div>
              </div>
            </div>
            
            <div class="tem_item u-flex-col u-col-center m-r-20"  @click.stop="radiuClick(3)">
              <div :style="`backgroundImage: url(${svgData14})`" class="icon2" ></div>
              <div
                class="radius shou"
                :class="{ radiusCss: 3 == temIndex }"
                style="margin-top: 16px"
              >
                <div class="blue_dot" v-if="3 == temIndex"></div>
              </div>
            </div>
            
            <div class="tem_item u-flex-col u-col-center m-r-20" @click.stop="radiuClick(4)">
              <div :style="`backgroundImage: url(${svgData15})`" class="icon2" ></div>
              <div
                class="radius shou"
                :class="{ radiusCss: 4 == temIndex }"
                style="margin-top: 16px"
              >
                <div class="blue_dot" v-if="4 == temIndex"></div>
              </div>
            </div>
            
            <div class="tem_item u-flex-col u-col-center m-r-20" @click.stop="radiuClick(5)">
              <div :style="`backgroundImage: url(${svgData16})`" class="icon2" ></div>
              <div
                class="radius shou"
                :class="{ radiusCss: 5 == temIndex }"
                style="margin-top: 16px"
              >
                <div class="blue_dot" v-if="5 == temIndex"></div>
              </div>
            </div>
            
            <div class="tem_item u-flex-col u-col-center m-r-20" @click.stop="radiuClick(6)">
              <div :style="`backgroundImage: url(${svgData17})`" class="icon2" ></div>
              <div
                class="radius shou"
                :class="{ radiusCss: 6 == temIndex }"
                style="margin-top: 16px"
              >
                <div class="blue_dot" v-if="6 == temIndex"></div>
              </div>
            </div>
            
            <div class="tem_item u-flex-col u-col-center m-r-20"  @click.stop="radiuClick(7)">
              <div :style="`backgroundImage: url(${svgData18})`" class="icon2" ></div>
              <div
                class="radius shou"
                :class="{ radiusCss: 7 == temIndex }"
                style="margin-top: 16px"
              >
                <div class="blue_dot" v-if="7 == temIndex"></div>
              </div>
            </div>
          </div>
          <div class="u-flex m-t-40">
            <div class="color-70">スタンプカラーを選択</div>
            <div :style="`background:${form.colorTwo};width: 100px;height: 40px;border-radius: 6px;box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);position:relative;margin-left:15px`" >
                <el-color-picker
                  v-model="form.colorTwo"
                  show-alpha
                  :predefine="predefineColors"
                  style="position: absolute;left: 0;top:0;z-index: 9 !important;border: none !important;"
                  @change="colorChange"
                  >
                </el-color-picker>
            </div>
            
            <!-- <colorPicker
              class="shou"
              v-model="form.colorTwo"
              @change="handleChangeColor"
              size="5"
            ></colorPicker> -->
          </div>
          <div class="u-flex template m-t-40">
            <div class="tem_sign" v-if="!temShow" @click.stop="temClick"></div>
            <img
              src="@/static/index/select.png"
              v-else
              @click.stop="temClick"
            />
            <div
              class="tem_text m-l-10"
              :class="[temShow ? 'rad_black' : 'rad_grey']"
            >
              オリジナルデザインをアップロード
            </div>
          </div>
          <div class="u-flex m-t-10" style="position: relative;left: 30px;">
            <div class="text_red">※</div>
            <div class="text_grey">ファイル形式は.PNGのみ可</div>
          </div>
          <div class="u-flex" style="margin-top: 5px;position: relative;left: 30px;margin-bottom: 20px;">
            <div class="text_red">※</div>
            <div class="text_grey">スタンプカラーの変更はできません。</div>
          </div>
          <div class="select" style="position: relative;left: 30px;margin: 20px 0 10px;" v-if="temShow && diyImage">
                  <img :src="diyImage" alt="" style="object-fit: cover;width: 100%;height: 100%;" >
              </div>
          <div class="add" style="position: relative;left: 30px;">
            <el-upload
              class="upload-demo"
              accept="image/*"
              action="https://api.card-san.jp/tool/upload/file"
              :before-upload="overfk"
              :on-success="handleAvatarSuccess1"
              :limit="1"
              :show-file-list="false"
              :file-list="fileList"
            >
              <div class="zhui">
                <span class="el-icon-plus" style="font-weight: 700"></span>
                ファイル追加
              </div>
            </el-upload>
          </div>
          
        </div>
      </div>

      <div v-else>
        <div style="padding: 35px 0 0px 40px">
          <div> <span style="color: #D93025;">※</span> ポイントカード名</div>
          <input
            type="text"
            class="int"
            placeholder="店舗名もしくはメンバーズカード名を入力"
            v-model="cardname"
          />
        </div>
        <div class="tit u-flex m-t-60">ポイント付与設定</div>
        <div style="padding: 49px 0 30px 40px">
          <div class="u-flex">
            <div
              class="radius shou"
              style="margin-right: 16px"
              @click.stop="pointClick"
              :class="{ radiusCss: pointShow }"
            >
              <div class="blue_dot" v-if="pointShow"></div>
            </div>
            <div :class="[pointShow ? 'rad_black' : 'rad_grey']">設定する</div>
          </div>
          <div class="u-flex m-l-30 m-t-20">
            <input
              class="point_int"
              :readonly="!pointShow"
              type="number"
              style="box-sizing: border-box;padding-left: 10px;"
              v-model="money"
            />円利用ごとに
            <input
              class="point_int m-l-30"
              :readonly="!pointShow"
              type="number"
              style="box-sizing: border-box;padding-left: 10px;"
              v-model="point"
            />ポイントを付与
          </div>
          <div class="u-flex m-t-40">
            <div
              class="radius shou"
              style="margin-right: 16px"
              @click.stop="pointClick"
              :class="{ radiusCss: !pointShow }"
            >
              <div class="blue_dot" v-if="!pointShow"></div>
            </div>
            <div :class="[!pointShow ? 'rad_black' : 'rad_grey']">
              設定・表示しない
            </div>
          </div>
          <div class="point_bg no-size u-flex-center">
            他社のポイント管理システムをご利用の場合も、こちらを選択してください。
          </div>
        </div>
      </div>

      <div class="expiry">
        <div class="u-flex">
          <div
            class="radius shou"
            style="margin-right: 16px"
            @click.stop="expiryClick"
            :class="{ radiusCss: expiryShow }"
          >
            <div class="blue_dot" v-if="expiryShow"></div>
          </div>
          <div :class="[expiryShow ? 'rad_black' : 'rad_grey']">有効期限</div>
        </div>
        <div style="padding-left: 33px">
          <div class="exp_grey">オフにすると、有効期限は設定されません</div>
          <div class="u-flex m-t-30" v-if="expiryShow">
            <div
              class="radius shou"
              :class="{ radiusCss: !yearShow && expiryShow }"
              @click.stop="issueClick"
            >
              <div class="blue_dot" v-if="!yearShow"></div>
            </div>
            <div
              class="issue_date"
              :class="[!yearShow && expiryShow ? 'rad_black' : 'rad_grey']"
            >
              カード発行日から
            </div>
            <el-select
              v-model="choiceTwo"
              placeholder="日数を選択"
              :disabled="yearShow"
            >
              <el-option
                v-for="item in issue"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              >
              </el-option>
            </el-select>
          </div>
          <div class="u-flex m-t-30" v-if="expiryShow">
            <div
              class="radius shou"
              @click.stop="yearClick"
              :class="{ radiusCss: yearShow && expiryShow }"
            >
              <div class="blue_dot" v-if="yearShow"></div>
            </div>
            <div
              class="issue_date"
              :class="[yearShow && expiryShow ? 'rad_black' : 'rad_grey']"
            >
              年月日を指定する
            </div>
            <!-- 日历 -->
           <el-date-picker
                 v-model="value1s"
                 type="date"
                 size="big"
                 format="yyyy/MM/dd"
                 value-format="yyyy/MM/dd"
                 placeholder="日付の選択"
                 :disabled="!yearShow">
               </el-date-picker>
          </div>
        </div>
      </div>
      <div class="expiry">
        <div class="u-flex">
          <div
            class="radius shou"
            style="margin-right: 16px"
            @click.stop="remarksClick"
            :class="{ radiusCss: remarksShow }"
          >
            <div class="blue_dot" v-if="remarksShow"></div>
          </div>
          <div :class="[remarksShow ? 'rad_black' : 'rad_grey']">備考欄</div>
        </div>
        <div class="exp_grey" style="padding-left: 33px">
          オフにすると、備考欄は設定されません。
        </div>
        <textarea
          class="tarea no-size"
          placeholder="備考欄への追記事項をご記入ください。"
           v-if="remarksShow"
           v-model="remark"
        ></textarea>
      </div>
      <div class="expiry">
        <div class="u-flex">
          <div
            class="radius shou"
            style="margin-right: 16px"
            @click.stop="discountClick"
            :class="{ radiusCss: discountShow }"
          >
            <div class="blue_dot" v-if="discountShow"></div>
          </div>
          <div :class="[discountShow ? 'rad_black' : 'rad_grey']">特典</div>
        </div>
        <div class="exp_grey" style="padding-left: 33px">
          オフにすると、特典は設定されません。
        </div>

        <div class="add-pictur m-t-20 no-size" v-if="discountShow">
          <div class="pic-tit">特典画像(最大6枚)</div>

          <div class="img-bos">
            <div class="img-boxs u-flex u-flex-wrap">
              <div
                class="img-item"
                v-for="(item, index) in imgList"
                :key="index"
                :style="{ background: item ? '' : 'rgba(0,0,0,0.07)' }"
              >
                <img
                  src="@/static/gClose.png"
                  alt=""
                  class="colse shou"
                  @click="delPicture(index)"
                />
                <img :src="item" class="tu" v-if="item"   @click="handlePictureCardPreview(item)"/>
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
            >
              <div class="tuo no-size">
                <img src="@/static/edit-image.png" alt="" style="width: 24px;object-fit: contain;"/>
                <br />
                <span class="txt">クリックorファイルをドロップ</span>
              </div>
            </el-upload>
          </div>
        </div>
        <div class="portrait_tip"  v-if="discountShow">特典内容</div>
        <textarea
          class="tarea no-size"
           v-if="discountShow"
          placeholder="特典内容をご記入ください。"
          v-model="content"
        ></textarea>
      </div>
      <div class="foot">
          <div class="btn goba u-flex-center shou" @click="goback">戻　る</div>
          <div class="btn nxt u-flex-center shou" @click="saveCard">保　存</div>
      </div>
    </div>
    <!-- 保存 -->
    <div class="fixed" v-show="releaseShow">
      <div class="release u-flex-col u-col-center u-row-center">
        <img src="@/static/index/sign_blue.png" class="sign" />
        <div>保存しました</div>
      </div>
    </div>
    <!-- Top Hair Spa & Resort  -->
    <!-- 积分卡的预览 -->
    <div class="fixed" v-show="rectangleShow">
      <div class="zhe"></div>
      <div class="per-box" v-if="!squareShow">
        <div class="tit">
          <img
            class="shou"
            @click="rectangleShow = false"
            src="@/static/index/close.png"
            alt=""
          />
          <div class="t">プレビュー</div>
        </div>

        <div class="tit" style="font-size: 18px" v-text="query.name"></div>
        <div class="top-box">
          <div class="con">
            <div class="left">NO:一</div>
            <div class="title">山田太郎さん</div>
            <div class="img">
              <img style="width: 100%;object-fit: cover;height: 100%;" src="@/static/index/ITF.png" />
            </div>
            <div class="order-sn">1923055025809</div>
            <div class="dotNum u-flex-center m-t-30">
              保有ポイント数
              <div style="color: #e67972; font-size: 28px; margin-left: 20px">
                0 pt
              </div>
            </div>
          </div>
        </div>
        <div class="cardInfo">
          <div class="cardTitle">{{cardname}}</div>
          <div class="cardInfos">
           <div class="ciItem" v-if="expiryShow">
             <div class="ciLeft">有効期限</div>
             <div class="ciRight" v-if="yearShow">{{value1s}}</div>
             <div class="ciRight" v-else>カード発効日から{{choiceTwo > 200?(choiceTwo/365) +'年' : choiceTwo+'日' }}間</div>
           </div>
            <div class="ciItem" v-if="remarksShow">
              <div class="ciLeft">備　考</div>
              <div class="ciRight" v-text="remark"></div>
            </div>
          </div>
        </div>
        <div class="cardInfo">
          <div class="btn blue_back">予 約</div>
          <div
            class="u-flex u-col-top three_bottom"
            v-for="(item, index) in 2"
            :key="index"
            style="margin-top: 12px; font-size: 14px"
          >
            <div style="width: 40px; text-align: center">
              <i
                class="el-icon-circle-check"
                style="color: #aaaaaa; font-size: 16px"
              ></i>
            </div>
            <div class="u-flex-col" style="margin-right: 30px">
              <div>10/12(月)　09:30〜</div>
              <div style="margin-top: 5px">カット&カラー</div>
            </div>
            <div class="u-flex">
              <img
                src="@/static/index/record3.png"
                style="width: 22px; margin-right: 8px"
                alt=""
              />
              長谷川杏
            </div>
          </div>
        </div>
        <div class="cardInfo" v-if="discountShow">
          <div class="btn red_back">特典</div>
          <div class="red_text" v-text="content"></div>
        </div>
      </div>
      <div class="per-box" style="zoom: 0.9;" v-if="squareShow">
        <div class="tit">
          <img
            class="shou"
            @click="rectangleShow = false"
            src="@/static/index/close.png"
            alt=""
          />
          <div class="t">プレビュー</div>
        </div>

        <div class="tit" style="font-size: 18px" v-text="query.name"></div>
        <div class="top-box">
          <div class="con">
            <div class="left">NO:105</div>
            <div class="title">山田太郎さん</div>
            <div class="img">
              <img src="@/static/index/qrcode.png" />
            </div>
            <div class="order-sn">1923055025809</div>
          </div>
        </div>
        <div class="cardInfo">
          <div class="cardTitle" style="margin-bottom: 10px;">{{cardname}}</div>
          <div class="cardY" :style="`background:${form.color}`" v-if="query.type == 'one'">
              <div class="cardInner">
                <div
                  class="seal u-flex u-row-center"
                  v-for="(item, index) in choice"
                  :key="index"
                  style="position: relative;"
                >
                  <div class="day">{{ index + 1 }}</div>
                  <div class="sealItem">
                      <!-- <img :src="selectIcon" /> -->
                      <div v-if="!temShow && temIndex == 0" :style="`backgroundImage: url(${svgData11})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 1" :style="`backgroundImage: url(${svgData12})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 2" :style="`backgroundImage: url(${svgData13})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 3" :style="`backgroundImage: url(${svgData14})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 4" :style="`backgroundImage: url(${svgData15})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 5" :style="`backgroundImage: url(${svgData16})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 6" :style="`backgroundImage: url(${svgData17})`" class="icon1"></div>
                    <div v-if="!temShow && temIndex == 7" :style="`backgroundImage: url(${svgData18})`" class="icon1"></div>
                      <img :src="diyImage" style="object-fit: cover;" v-if="temShow">
                      <div class="selectName">YY/MM/DD</div>
                  </div>  
                </div>
              </div>
            </div>
            
            <div class="cardY" :style="`background:${form.color}`" v-if="query.type == 'two'">
              <div class="cardInner" style="height: 164px;overflow-y: auto;position: relative;">
                  <div class="sealTitle" style="position: sticky;top: 0;z-index: 67;">
                      <div class="st1">NO</div>
                      <div class="st2">日付</div>
                      <div class="st3">
                          <img src="../../static/corent.png" alt="">
                      </div>
                      <div class="st4">
                          <img src="../../static/userAdmin.png" alt="">
                      </div>
                      <div class="st5"></div>
                  </div>
                <div
                  class="u-flex u-row-center seals"
                  v-for="(item, index) in choice"
                  :key="index"
                  style="position: relative;"
                >
                  <!-- <div class="day">{{ index + 1 }}</div>
                  <div class="sealItem">
                      <img :src="selectIcon" /> 
                      <div class="selectName">22/20</div>
                  </div>  -->
                 <div class="sti1" v-text="index + 1"></div>
                 <div class="sti2">YY/MM/DD</div>
                 <div class="sti3">00:00</div>
                 <div class="sti4">担当者</div>
                 <div class="sti5">
                     <div v-if="!temShow && temIndex == 0" :style="`backgroundImage: url(${svgData11})`" class="icon3"></div>
                     <div v-if="!temShow && temIndex == 1" :style="`backgroundImage: url(${svgData12})`" class="icon3"></div>
                     <div v-if="!temShow && temIndex == 2" :style="`backgroundImage: url(${svgData13})`" class="icon3"></div>
                     <div v-if="!temShow && temIndex == 3" :style="`backgroundImage: url(${svgData14})`" class="icon3"></div>
                     <div v-if="!temShow && temIndex == 4" :style="`backgroundImage: url(${svgData15})`" class="icon3"></div>
                     <div v-if="!temShow && temIndex == 5" :style="`backgroundImage: url(${svgData16})`" class="icon3"></div>
                     <div v-if="!temShow && temIndex == 6" :style="`backgroundImage: url(${svgData17})`" class="icon3"></div>
                     <div v-if="!temShow && temIndex == 7" :style="`backgroundImage: url(${svgData18})`" class="icon3"></div>
                      <img :src="diyImage" v-if="temShow" style="object-fit: cover;" alt="" />
                 </div>
                </div>
              </div>
            </div>
          <div class="cardInfos">
            <div class="ciItem" v-if="expiryShow">
              <div class="ciLeft">有効期限</div>
              <div class="ciRight" v-if="yearShow">{{value1s}}</div>
              <div class="ciRight" v-else>カード発効日から{{choiceTwo > 200?(choiceTwo/365) +'年' : choiceTwo+'日' }}間</div>
            </div>
            <div class="ciItem" v-if="remarksShow">
              <div class="ciLeft">備　考</div>
              <div class="ciRight" v-text="remark"></div>
            </div>
            <div v-if="discountShow && imgList.length > 0" style="margin-bottom: 10px;">
                <div class="ciItem" v-if="discountShow">
                    <div class="ciLeft" style="background: rgb(213, 54, 32);color: #fff;">特 典</div>
                </div>
                <el-carousel :indicator-position="outside" height="180px">
                    <el-carousel-item v-for="item in imgList" :key="item">
                        <img :src="item" alt="" style="object-fit: cover;width: 100%;height: 100%;" />
                    </el-carousel-item>
                </el-carousel>
            </div>
            <div class="ciItem" v-if="discountShow">
                <div class="ciRight" v-text="content"></div>
            </div> 
          </div>
        </div>
      </div>
    </div>
         <!-- 预览图片 -->
    <el-dialog :visible.sync="dialogVisible" :append-to-body="true">
      <img :src="dialogImageUrl" style="width: 70%" />
    </el-dialog>
  </div>
</template>

<script>
import {addCard,getStore } from "@/http/api.js"
import svg1 from '../../static/svgData/svgjs/sv_01.js'
import svg2 from '../../static/svgData/svgjs/sv_02.js'
import svg3 from '../../static/svgData/svgjs/sv_03.js'
import svg4 from '../../static/svgData/svgjs/sv_04.js'
import svg5 from '../../static/svgData/svgjs/sv_05.js'
import svg6 from '../../static/svgData/svgjs/sv_06.js'
import svg7 from '../../static/svgData/svgjs/sv_07.js'
import svg8 from '../../static/svgData/svgjs/sv_08.js'
export default {
  name: "",
  data() {
    return {
      choice: 10,
      choiceTwo: "",
      predefineColors:[],
      cardname:'',
      money:"",
      point:"",
      value1s:'',
      temShow: false,
      selectIcon:require("../../static/index/sv_01.svg"),
      query:{},
      bgColor:"",
      dates:"",
      diyImage:'',
      temList: [
        require("../../static/index/sv_01.svg"),
        require("../../static/index/sv_02.svg"),
        require("../../static/index/sv_03.svg"),
        require("../../static/index/sv_04.svg"),
        require("../../static/index/sv_05.svg"),
        require("../../static/index/sv_06.svg"),
        require("../../static/index/sv_07.svg"),
        require("../../static/index/sv_08.svg"),
      ],
      temIndex: 0,
      expiryShow: false,
      issueShow: false,
      content:"",
      issue: [
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
      yearShow: false,
      riliPublishShow: false,
      remark:"",
      months: ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"],
      year: -1,
      day: -1,
      time: "",
      value2: "",
      monthIndex: 0,
      remarksShow: false,
      discountShow: false,
      releaseShow: false,
      rectangleShow: false,
      squareShow: false, //true二维码  false一维码
      detailShow: true,
      pointShow: false,
      imgList: [],
      fileList: [],
      form: {
        color: "#71d3d1", //默认选择颜色
        colorTwo: "#D93025",
      },
        dialogImageUrl: "",
      dialogVisible: false,
      svgData1:'"' + svg1 + '"',
      svgData2:'"' + svg2 + '"',
      svgData3:'"' + svg3 + '"',
      svgData4:'"' + svg4 + '"',
      svgData5:'"' + svg5 + '"',
      svgData6:'"' + svg6 + '"',
      svgData7:'"' + svg7 + '"',
      svgData8:'"' + svg8 + '"',
      
      svgData11:'"' + svg1 + '"',
      svgData12:'"' + svg2 + '"',
      svgData13:'"' + svg3 + '"',
      svgData14:'"' + svg4 + '"',
      svgData15:'"' + svg5 + '"',
      svgData16:'"' + svg6 + '"',
      svgData17:'"' + svg7 + '"',
      svgData18:'"' + svg8 + '"',
    };
  },
  created() {
      let that = this
    var myDate = new Date();
    this.value1 = myDate.toLocaleDateString();
    this.value2 = myDate.toLocaleDateString();
    this.time = myDate.getHours() + ":" + myDate.getMinutes() + ":" + myDate.getSeconds();
    this.query = this.$route.query
    console.log("query",this.query)
    getStore().then((res) => {
        console.log("res",res)
        if(res.code == 200){
            let colors = []
            res.data.shop_info.color.forEach((v,i) => {
                if(v.color.includes("#")){
                    colors.push(that.hexToRgb(v.color))
                }else{
                    colors.push(v.color)
                }  
            })
            that.predefineColors = colors
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
      showpreview(){
          let that = this
          that.rectangleShow = true
          if(that.query.type == 'three'){
              that.squareShow = false
          }else{
              that.squareShow = true
          }
      },
      changeColor(url,color){
       	let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#", "%23"));//转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
       	return res;
       },
       colorChange1(){
           
       },
    colorChange(e){
       let that = this
       this.svgData11 = this.changeColor(this.svgData1,e);
       this.svgData12 = this.changeColor(this.svgData2,e);
       this.svgData13 = this.changeColor(this.svgData3,e);
       this.svgData14 = this.changeColor(this.svgData4,e);
       this.svgData15 = this.changeColor(this.svgData5,e);
       this.svgData16 = this.changeColor(this.svgData6,e);
       this.svgData17 = this.changeColor(this.svgData7,e);
       this.svgData18 = this.changeColor(this.svgData8,e);
       console.log(e)
    },
    temClick() {
      this.temShow = !this.temShow;
      if(this.temShow == false){
          this.temIndex = 0
          this.selectIcon = this.temList[0]
      }
    },
    goback(){
      this.$router.back()
    },
    saveCard(){
       let that = this
       let data = {}
       data['type'] = that.query.type == 'one'? 1 : that.query.type == 'two'? 2 : 3
       data["shop_name"] = that.query.name
       data["image"] = that.query.image
       data["name"] = that.cardname
       data['background_color'] = that.form.color
       data['count'] = that.choice
       data['seal_icon'] = that.selectIcon
       data['seal_color'] = that.form.colorTwo
       data['is_point'] = that.pointShow == true? 1 : 0
       data['point'] = that.point
       data['amount'] = that.money
       data['valid_type'] = that.expiryShow == false? 0 : that.yearShow == false? 1 : 2
       data['valid_day'] = that.choiceTwo
       data['expire_time'] = that.value1s
       data['is_remark'] = that.remarksShow == true ? 1 : 0
       data['remark'] = that.remark
       data['is_discount'] = that.discountShow == true? 1 : 0
       data['discount_images'] = that.imgList
       data['discount_content'] = that.content
       addCard(data).then((res) => {
           if(res.code == 200){
               that.releaseShow = true
               setTimeout(() => {
                   that.releaseShow = false
                   this.$router.push({
                     path: "/member",
                   });
               },1500)
           }else{
               // that.$message.error(res.message)
               that.$message({
               	message: res.message,
               	type: 'error',
                    offset: 400
               });
           }
       })
    },
    radiuClick(index) {
      let that = this
      // that.temShow = false
      this.temIndex = index;
      that.selectIcon = that.temList[index]
    },
    expiryClick() {
      this.expiryShow = !this.expiryShow
    },
    pointClick() {
      this.pointShow = !this.pointShow;
    },

    issueClick() {
      if(this.expiryShow == true){
        this.yearShow = false  
      }
      
    },
    yearClick() {
        if(this.expiryShow == true){
          this.yearShow = true  
        }
    },
    // 日历
    chooseTwo(i) {
      this.value2 = i.time;
      this.riliPublishShow = false;
    },
    overfk(){
      this.diyImage = ''  
    },
    backTwo() {
      let myDate = new Date();

      if (
        this.monthIndex <= myDate.getMonth() &&
        this.year <= myDate.getFullYear()
      ) {
        return;
      }
      if (!this.monthIndex) {
        this.monthIndex = 11;
        this.year = this.year - 1;
      } else {
        this.monthIndex = this.monthIndex - 1;
      }
    },
    backYearTwo() {
      let myDate = new Date();

      if (this.year <= myDate.getFullYear()) {
        return;
      } else {
        this.year = this.year - 1;
      }
    },
    addYearTwo() {
      this.year = this.year + 1;
    },
    forwardTwo() {
      if (this.monthIndex == 11) {
        this.monthIndex = 0;
        this.year = this.year + 1;
      } else {
        this.monthIndex = this.monthIndex + 1;
      }
    },
    riliPublishTab() {
      if (this.yearShow) {
        this.riliPublishShow = !this.riliPublishShow;
      }
    },
    remarksClick() {
      this.remarksShow = !this.remarksShow;
    },
    discountClick() {
      this.discountShow = !this.discountShow;
    },
    handleAvatarSuccess(res, file) {
      this.imgList.push(res.data.path);
      this.fileList.push({ url: res.data.path });
    },
    handleAvatarSuccess1(res, file) {
      console.log("res",res)
      this.diyImage = res.data.path
       this.fileList = []
    },
    delPicture(i) {
      this.imgList.splice(i, 1);
      this.fileList.splice(i, 1);
    },
    handleChangeColor(val) {
      console.log("输出颜色111值", val); // 颜色 #6c8198
    },
      //图片预览
    handlePictureCardPreview(item) {
      this.dialogImageUrl = item;
      this.dialogVisible = true;
    },
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
</style>
<style lang="scss">
.sealItem{
    width: 100%;
    height: 100%;
    position: absolute;
    left: 0;
    top: 0;
    text-align: center;
    box-sizing: border-box;
    padding: 10px 0 0;
    
    .selectName{
        font-size: 10px;
        color: #999;
    }
}
.icon1{
    width: 40px;
    height: 40px;
    background-size: 100% 100%;
    display: inline-block
}
.icon2{
  width: 60px;
  height: 60px;
  background-size: 100% 100%;
  display: inline-block
}
.icon3{
    width: 20px;
    height: 20px;
    background-size: 100% 100%;
    display: inline-block
}
.icon4{
      width: 80px;
      height: 80px;
      background-size: 100% 100%;
      display: inline-block
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
  background: transparent !important;
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
    .goba{
        background: rgba(29,29,31,0.2) !important;
        color: #707070 !important;
    }
    .add {
      margin-bottom: 40px;
      width: 140px;
      // height: 53px;
      font-size: 14px;
      color: #2699fb;
      // background: #ffffff;
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
    width: 100px;
    height: 40px;
}
.not_tit {
  width: 820px;
  padding: 0 0 22px 0;
  font-size: 21px;
  color: #1d1d1f;
  font-weight: bold;
  .btn_grey {
    width: 124px;
    height: 30px;
    color: #707070;
    font-size: 16px;
    font-weight: lighter;
    background: rgba(29, 29, 31, 0.1);
    border-radius: 15px 15px 15px 15px;
    border: 1px solid #707070;
  }
}
.cardY {
  width: 357px;
  background: #71d3d1;
  box-shadow: 0px 0px 5px 1px rgba(0, 0, 0, 0.16);
  border-radius: 8px;
  box-sizing: border-box;
  padding: 18px;

    .sealTitle{
        display: flex;
        align-items: center;
        width: 100%;
        text-align: center;
        font-size: 12px;
        // justify-content: space-between;
        
        .st1{
            width: 10%;
        }
        
        .st2{
            width: 30%;
        }
        
        .st3{
            width: 20%;
            
            img{
                width: 20px;
                height: 20px;
                vertical-align: middle;
            }
        }
        .st4{
            width: 20%;
            
            img{
                width: 20px;
                height: 20px;
                vertical-align: middle;
            }
        }
        
        .st5{
            width: 20%;
        }
        
    }

  .cardInner {
    width: 100%;
    // height: 164px;
    overflow-y: auto;
    background-color: #fff;
    display: flex;
    flex-wrap: wrap;
    .day {
      font-size: 24px;
      color: #eaeaea;
    }
    .seals{
        height: 28px;
        border-bottom: 1px dotted #d2d2d7;
        display: flex;
        align-items: center;
        width: 100%;
        text-align: center;
        font-size: 12px;
        .sti1{
            width: 10%;
        }
        
        .sti2{
            width: 30%;
        }
        
        .sti3{
            width: 20%;
        }
        .sti4{
            width: 20%;
            
        }
        
        .sti5{
            width: 20%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            box-sizing: border-box;
            padding-right: 20px;
        }
        
        
    }
    .seal {
      width: 19.65%;
      height: 82px;
      border-right: 1px solid #eaeaea;
      border-bottom: 1px solid #eaeaea;
      position: relative;

      img {
        width: 40px;
        height: 40px;
      }
      .sealTime {
        font-size: 10px;
        color: #707070;
        margin-top: 3px;
        font-weight: lighter;
      }
    }
  }
}

.not_cont {
  width: 820px;
  box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
  .tit {
    font-size: 20px;
    color: rgba(29, 29, 31, 0.9);
    font-weight: bold;
    height: 48px;
    background: #f5f5f7;
    padding-left: 40px;
  }

  .text_red {
    color: #d93025;
  }
  .text_black {
    color: #1d1d1f;
    margin-right: 20px;
  }
  .seal {
    ::v-deep .el-input__inner {
      text-align: center;
      font-size: 20px;
      color: #1d1d1f;
    }
  }
  .text_grey {
    color: #707070;
  }
  .font-12 {
    font-size: 12px;
  }
  .color-70 {
    color: #707070;
  }
  .point_int {
    width: 100px;
    height: 44px;
    outline: 0;
    border-radius: 6px;
    margin-right: 10px;
    border: 1px solid #aaa;
  }
  .point_bg {
    width: 283px;
    height: 51px;
    font-size: 12px;
    color: #007aff;
    border-radius: 10px;
    margin: 13px 0 0 30px;
    padding: 10px;
    background: rgba(143, 168, 248, 0.2);
  }
  .int {
    width: 740px;
    height: 40px;
    outline: 0;
    font-size: 16px;
    background: #fff;
    padding-left: 12px;
    margin-top: 5px;
    border-radius: 10px;
    border: 1px solid #d2d2d7;
  }
  ::-webkit-input-placeholder {
    /*Webkit browsers*/
    color: #aaa;
    font-size: 16px;
  }
  :-moz-placeholder {
    /*Mozilla Firefox 4 to 8*/
    color: #aaa;
    font-size: 16px;
  }
  ::moz-placeholder {
    /*Mozilla Firefox 19+*/
    color: #aaa;
    font-size: 16px;
  }
  :-ms-input-placeholder {
    /*Internet Explorer 10+*/
    color: #aaa;
    font-size: 16px;
  }

  ::v-deep .m-colorPicker .colorBtn {
    width: 100px;
    height: 40px;
    margin-left: 17px;
    border-radius: 6px;
    box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
  }
  ::v-deep .m-colorPicker .box {
    left: 140px;
    margin-top:-40px;
  }
  .select {
    width: 100px;
    height: 100px;
    background: #fff;
    box-shadow: 0px 0px 5px 1px rgba(0, 0, 0, 0.16);
    border-radius: 6px;
    border: 1px solid #d2d2d7;
    text-align: center;
    line-height: 100px;
    img{
        vertical-align: middle;
    }
  }
  .select_text {
    font-size: 14px;
    color: #707070;
    margin-top: 5px;
  }
  .template {
    .tem_sign {
      width: 18px;
      height: 18px;
      border: 1px solid #707070;
      border-radius: 4px;
    }
    img {
      width: 20px;
      height: 20px;
    }
    .tem_text {
      font-size: 16px;
    }
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
  .rad_black {
    color: rgba(29, 29, 31, 0.9);
    font-weight: bold;
  }
  .rad_grey {
    color: #707070;
  }
  .expiry {
    padding: 40px;
    border-top: 1px solid #d2d2d7;
    .exp_grey {
      font-size: 12px;
      color: #aaa;
      margin-top: 12px;
    }
    .issue_date {
      margin: 0 20px 0 10px;
    }
    ::v-deep .el-input__inner {
      width: 200px;
      // padding-left: 10px;
      background: #f5f5f7;
      border-radius: 10px;
      border: 1px solid #707070;
    }
    .rili {
      display: flex;
      align-items: center;
      position: relative;

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
    }
    .tarea {
      width: 740px;
      height: 100px;
      outline: 0;
      font-size: 16px;
      margin-top: 5px;
      background: #fff;
      border-radius: 10px;
      padding: 14px;
      border: 1px solid #d2d2d7;
    }
    .pic-tit {
      font-size: 16px;
      color: #707070;
    }
    .img-bos {
      .img-boxs {
        margin-bottom: 5px;
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
            // margin-top: 10px;
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
      
    }

    .portrait_tip {
      font-size: 16px;
      color: #707070;
      margin: 46px 0 0 15px;
    }
  }
  .foot {
    margin-top: 50px;
    padding: 30px 0 70px 0;
    border-top: 1px solid #d2d2d7;
    display: flex;
    align-items: center;
    justify-content: center;
    .btn {
      width: 200px;
      height: 50px;
      font-size: 20px;
      color: #fff;
      margin: 0 30px;
      background: #1a73e8;
      border-radius: 10px;
    }
  }
}
.fixed {
  position: fixed;
  top: 0%;
  left: 0%;
  width: 100vw;
  height: 100vh;
  overflow-y: scroll !important;
  scrollbar-width: none;
  -ms-overflow-style: none;
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

  ::-webkit-scrollbar {
    display: none;
  }

  .per-box {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 375px;
    height: 80vh;
    overflow-y: auto;
    background: #fff;
    border-radius: 20px;
    .tit {
      position: relative;
      font-size: 20px;
      font-weight: 700;
      width: 100%;
      height: 50px;
      line-height: 50px;
      text-align: center;
      background-color: #fff;
      border-radius: 20px 20px 0 0;
      border-bottom: 2px solid #e7e7eb;

      img {
        position: absolute;
        left: 10px;
        top: 8px;
        width: 23px;
        height: 23px;
      }
    }
  }
  .top-box {
    border-bottom: 1px solid #c4c4c8;
    padding-bottom: 40px;

    .con {
      width: 90%;
      margin: 0 auto;

      .left {
        text-align: left;
        margin-top: 20px;
        color: #1d1d1f;
        font-size: 16px;
      }

      .title {
        color: #1d1d1f;
        margin-top: 5px;
        font-size: 24px;
        font-weight: 700;
        text-align: center;
      }

      .img {
        margin-top: 20px;
        text-align: center;

        img {
          width: 150px;
          height: 150px;
        }

        .tiaoxing {
          width: 230px;
          height: 51px;
        }
      }

      .order-sn {
        margin-top: 6px;
        text-align: center;
        font-size: 16px;
        color: #1d1d1f;
      }
      .dotNum {
        font-weight: bold;
      }
    }
  }
  .cardInfo {
    padding: 20px 9px 0;
    background: #fff;
    z-index: 99;
    border-bottom: 1px solid #c4c4c8;

    .cardTitle {
      font-size: 16px;
      font-weight: bold;
      color: #1d1d1f;
      text-align: center;
      width: 100%;
    }

    .cardInfos {
      margin-top: 20px;
      box-sizing: border-box;
      padding: 0 16px 20px;
      background: #fff;

      .ciItem {
        display: flex;
        align-items: center;
        margin-bottom: 10px;

        .ciLeft {
          width: 72px;
          height: 30px;
          background: #f5f5f7;
          font-size: 14px;
          border-radius: 5px;
          font-family: Hiragino Sans-W3, Hiragino Sans;
          font-weight: normal;
          color: #707070;
          text-align: center;
          line-height: 30px;
          margin-right: 14px;
        }

        .ciRight {
          font-size: 14px;
          font-family: Hiragino Sans-W3, Hiragino Sans;
          font-weight: normal;
          color: #1d1d1f;
          white-space: pre-wrap;
        }
      }
    }
    .three_bottom {
      margin: 12px 0 20px 0;
      font-size: 14px;
    }
  }
  .btn {
    width: 60px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 12px;
    color: #ffffff;
  }
  .blue_back {
    background: #1a73e8;
  }

  .red_back {
    background: #d93025;
  }
  .red_text {
    width: 334px;
    height: 38px;
    line-height: 20px;
    font-size: 14px;
    margin-top: 18px;
    color: #1d1d1f;
    padding: 0 0 20px 8px;
  }

  .blue_back {
    background: #1a73e8;
  }
}
::v-deep .el-input__inner {
  width: 100px;
  // padding-left: 10px;
  background: #f5f5f7;
  border-radius: 10px;
  border: 1px solid #707070;
}
</style>
