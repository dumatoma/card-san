<template>
    <view>
        <!-- 自定义顶部导航 -->
        <u-navbar :title="info.name" :placeholder="true" :border="true" :safeAreaInsetTop="true" :autoBack="true">
            <view class="u-nav-slot" slot="right">
                <text @click="toCsv" class="tc">詳細</text>
            </view>
        </u-navbar>
        <view class="containe">
            <!-- [カスタムエイド 2026-10-20] 会員ID・顧客NOと「カルテ」を1つの白い角丸のまとまりに。カルテは準備中(押すとお知らせ) -->
            <view class="sl-card sl-top">
                <view class="sl-row">
                    <text class="sl-label">会員ID</text>
                    <text class="sl-val" selectable="true">{{info.member_id}}</text>
                </view>
                <view class="sl-row">
                    <text class="sl-label">顧客NO</text>
                    <text class="sl-val">{{info.member_no || '-'}}</text>
                </view>
                <view class="sl-karte" hover-class="sl-hover" @click="openKarte">
                    <view class="sl-karte-ic"><view class="sl-karte-line"></view><view class="sl-karte-line"></view><view class="sl-karte-line short"></view></view>
                    <text class="sl-karte-name">カルテ</text>
                    <text class="sl-soon">準備中</text>
                    <view class="sl-chev"></view>
                </view>
            </view>

            <view class="cardInfo">
                <!-- [カスタムエイド 2026-10-20] カードの上はスタンプカード名だけ(店舗名は出さない) -->
                <view class="sl-title">{{info.card_name}}</view>
                <!-- [カスタムエイド 2026-10-20] 枠の画像(お店が選んでいれば)。スタンプが3段以上は高さを2段半に止めて中をスクロールし、空いているマスの段が見える所まで自動で動かす(お客様アプリと同じ) -->
                <view class="cardY" :style="frameCss" v-if="card.card_type == 1">
                    <scroll-view class="cs-seal-scroll" scroll-y :style="Math.ceil(list.length / 5) > 2 ? 'height: ' + (SEAL_ROW * 2.5) + 'rpx;' : ''" :scroll-top="sealTop">
                    <view class="cardInner cs-inner">
                        <view class="seal" @click="showModal" v-for="(item,index) in list" :key="index">
                            <text v-text="index + 1"></text>
                            <view class="sealed" v-if="item.sealed" style="top: 0;">
                                 <view v-text="item.admin_name" style="font-size: 20upx;"></view>
                               <image :src="item.seal_icon" mode="" v-if="item.seal_icon.includes('https')"></image>
                               <view :style="`backgroundImage: url(${svgData1})`" class="icon" v-if="iconNum == 1 && item.seal_icon.includes('https') == false"></view>
                               <view :style="`backgroundImage: url(${svgData2})`" class="icon" v-if="iconNum == 2 && item.seal_icon.includes('https') == false"></view>
                               <view :style="`backgroundImage: url(${svgData3})`" class="icon" v-if="iconNum == 3 && item.seal_icon.includes('https') == false"></view>
                               <view :style="`backgroundImage: url(${svgData4})`" class="icon" v-if="iconNum == 4 && item.seal_icon.includes('https') == false"></view>
                               <view :style="`backgroundImage: url(${svgData5})`" class="icon" v-if="iconNum == 5 && item.seal_icon.includes('https') == false"></view>
                               <view :style="`backgroundImage: url(${svgData6})`" class="icon" v-if="iconNum == 6 && item.seal_icon.includes('https') == false"></view>
                               <view :style="`backgroundImage: url(${svgData7})`" class="icon" v-if="iconNum == 7 && item.seal_icon.includes('https') == false"></view>
                               <view :style="`backgroundImage: url(${svgData8})`" class="icon" v-if="iconNum == 8 && item.seal_icon.includes('https') == false"></view>
                               <view class="sealTime" v-text="item.date"></view>
                            </view>
                        </view>
                    </view>
                    </scroll-view>
                </view>
                <view class="cardY" :style="frameCss" v-if="card.card_type == 2">
                    <view class="cardTable">
                        <view class="cardTitle">
                            <view class="tableItem">
                                NO
                            </view>
                            <view class="tableItem" style="padding: 0 14upx;">
                                日付
                            </view>
                            <view class="tableItem">
                                <view style="margin: 0 auto;width: 48upx;">
                                    <u-icon name="checkmark-circle" size="20"></u-icon>
                                </view>
                            </view>
                            <view class="tableItem">
                                <view style="margin: 0 auto;width: 48upx;">
                                    <u-icon name="account-fill" size="20"></u-icon>
                                </view>
                            </view>
                            <view class="tableItem">
                                <image style="visibility: hidden;" class="set" src="../../../../static/icons/cright.png"
                                    mode=""></image>
                            </view>
                        </view>
                        <view class="tableContent">
                            <view class="contentItem" v-for="(item,index) in list"  @click="showModal">
                                <view class="tableItem" v-text="index+1"></view>
                                <view class="tableItem" v-text="item.date"></view>
                                <view class="tableItem" v-text="item.time"></view>
                                <view class="tableItem" v-text="item.admin_name"></view>
                                <view class="tableItem">
                                     <view class="sealed" v-if="item.sealed">
                                        <image :src="item.seal_icon" mode="" class="icon" v-if="item.seal_icon.includes('https')"></image>
                                        <view :style="`backgroundImage: url(${svgData1})`" class="icons" v-if="iconNum == 1 && item.seal_icon.includes('https') == false"></view>
                                        <view :style="`backgroundImage: url(${svgData2})`" class="icons" v-if="iconNum == 2 && item.seal_icon.includes('https') == false"></view>
                                        <view :style="`backgroundImage: url(${svgData3})`" class="icons" v-if="iconNum == 3 && item.seal_icon.includes('https') == false"></view>
                                        <view :style="`backgroundImage: url(${svgData4})`" class="icons" v-if="iconNum == 4 && item.seal_icon.includes('https') == false"></view>
                                        <view :style="`backgroundImage: url(${svgData5})`" class="icons" v-if="iconNum == 5 && item.seal_icon.includes('https') == false"></view>
                                        <view :style="`backgroundImage: url(${svgData6})`" class="icons" v-if="iconNum == 6 && item.seal_icon.includes('https') == false"></view>
                                        <view :style="`backgroundImage: url(${svgData7})`" class="icons" v-if="iconNum == 7 && item.seal_icon.includes('https') == false"></view>
                                        <view :style="`backgroundImage: url(${svgData8})`" class="icons" v-if="iconNum == 8 && item.seal_icon.includes('https') == false"></view>
                                     </view>
                                </view>
                            </view>
                        </view>
                    </view>
                </view>
                <view class="cardInfos">
                    <view class="ciItem">
                        <view class="ciLeft">
                            有効期限
                        </view>
                        <view class="ciRight" v-text="cardInfo.expire_time"></view>
                    </view>
                    <view class="ciItem" v-if="cardInfo.remark">
                        <view class="ciLeft">
                            備　考
                        </view>
                        <view class="ciRight" v-text="cardInfo.remark"></view>
                    </view>
                </view>
            </view>
            <view class="appoint" v-if="info.orders.length > 0">
                <view class="appointTitle">
                    予 約
                </view>
                <view class="appointItem" v-for="(item,index) in info.orders">
                    <view class="aItem">
                        <view class="aItemImage">
                            <image src="../../../../static/icons/record1.png" mode=""></image>
                        </view>
                        <view class="aTime">
                            <view class="time">
                                {{item.date}}({{item.week}})　{{item.start}}〜{{item.end}}
                            </view>
                            <view class="copy" v-text="item.menu_name"></view>
                        </view>
                    </view>
                    <view class="aItem" style="flex-shrink: 0;">
                        <view class="aItemImage">
                            <image src="../../../../static/icons/record3.png" mode=""></image>
                        </view>
                        <view class="aUser" v-text="item.admin_name"></view>
                    </view>
                </view>
               
            </view>
            <view class="special">
                <view class="specialTitle">
                    特 典
                </view>
                <u-swiper :list="card.discount_images" height="250" :indicator="true" indicatorMode="dot" indicatorActiveColor="#000000" indicatorInactiveColor="rgba(0,0,0,.3)"></u-swiper>
                <view class="remark">
                    <view v-text="card.discount_title" style="font-weight: bold;"></view>
                    <view v-text="card.discount_content"></view>
                </view>
            </view>
        </view>
        <!-- 添加印章 -->
        <u-popup :show="show" mode="bottom" @close="close">
            <view class="popContent">
                <view class="popTitle">
                    スタンプ付与数
                </view>
                <view class="choosecount">
                    <view class="reduce" hover-class="op" @click="reduce">
                      <image src="../../../../static/svg/reduce.svg" mode=""></image>  
                    </view>
                    <view class="coiunt" :style="number * 1 < 0? 'color:#d93025' : ''" v-text="number"></view>
                    <view class="plus" hover-class="op" @click="plus">
                        <image src="../../../../static/svg/plus.svg" mode=""></image>  
                    </view>
                </view>
                <u-button @click="save" class="btn" text="決　定"></u-button>
                <view style="height: 30upx;"></view>
            </view>
        </u-popup> 
        <!-- [カスタムエイド 2026-10-20] スタンプ満了のお知らせ(下から出る画面)。特典があれば写真と内容を出し、なければ「続けるには更新を」と案内。
             押した結果をボタンの下に書き、「あとで」も置く。押したときの動きは今までと同じ(更新=getStatus('confirm')、別のカード=getStatus('cancel')) -->
        <u-popup :show="showModule" mode="bottom" round="24" :closeOnClickOverlay="false" @close="showModule = false">
            <view class="fl">
                <view class="fl-grip"></view>
                <view class="fl-who">{{info.name}} さん</view>
                <view class="fl-h1">スタンプが満了しました</view>
                <view class="fl-gift" v-if="hasGift">
                    <image class="fl-gimg" v-if="card.discount_images && card.discount_images.length" :src="card.discount_images[0]" mode="aspectFill"
                        @click="previewGift"></image>
                    <view class="fl-gtext">
                        <view class="fl-glabel">特典をお渡しください</view>
                        <view class="fl-gtitle" v-if="card.discount_title">{{card.discount_title}}</view>
                        <view class="fl-gbody" v-if="card.discount_content">{{card.discount_content}}</view>
                    </view>
                </view>
                <view class="fl-info" v-else>このカードのスタンプはすべて使われました。続けてご利用いただくには、カードを更新するか、別のカードに変えてください。</view>
                <view class="fl-btn" hover-class="fl-hover" @click="getStatus('confirm')">
                    <text>このカードを更新する</text>
                    <text class="fl-small">同じカードで、スタンプを0からはじめます</text>
                </view>
                <view class="fl-btn2" hover-class="fl-hover" @click="getStatus('cancel')">
                    <text>別のカードに変える</text>
                    <text class="fl-small">会員ランクアップなど(カードを選ぶ画面へ)</text>
                </view>
                <view class="fl-later" hover-class="fl-hover" @click="showModule = false">あとで</view>
            </view>
        </u-popup>
        <mod v-if="showModule1" @getStatus="getStatus1" :config="configs1"></mod>
    </view>
</template>

<script>
    import {
        getMemberDetail,
        addPoints,
        getConfig,
        editMemberInformation
    } from "@/api/index.js"
    import mod from '@/components/mod.vue'
    import svg1 from '../../../../static/svgjs/sv_01.js'
    import svg2 from '../../../../static/svgjs/sv_02.js'
    import svg3 from '../../../../static/svgjs/sv_03.js'
    import svg4 from '../../../../static/svgjs/sv_04.js'
    import svg5 from '../../../../static/svgjs/sv_05.js'
    import svg6 from '../../../../static/svgjs/sv_06.js'
    import svg7 from '../../../../static/svgjs/sv_07.js'
    import svg8 from '../../../../static/svgjs/sv_08.js'
    export default {
        components: {
            mod
        },
        data() {
            return {
                SEAL_ROW: 172, // [カスタムエイド 2026-10-20] スタンプカードAの1段の高さ(rpx)
                sealTop: 0, // [カスタムエイド 2026-10-20] スタンプカードAの中のスクロール位置
                title: "name",
                number:1,
                banner: [],
                configs: {},
                iconNum:0,
                show:false,
                query:{},
                disImage:[],
                discount_images:[],
                info:{
                    orders:[],
                    
                },
                list: [],
                cardInfo:{},
                card:{},
                showModule:false,
                showModule1:false,
                svgData1:'"' + svg1 + '"',
                svgData2:'"' + svg2 + '"',
                svgData3:'"' + svg3 + '"',
                svgData4:'"' + svg4 + '"',
                svgData5:'"' + svg5 + '"',
                svgData6:'"' + svg6 + '"',
                svgData7:'"' + svg7 + '"',
                svgData8:'"' + svg8 + '"',
                background:""
            }
        },
        created() {
        },
        onLoad(options) {
          let that = this
          let query = options
          that.query = query
          that.getDetail(query.id)  
          let obj = {}
          obj.title = 'スタンプが満了しました'
          obj.content = 'カードを更新して下さい'
          obj.confirm = true
          obj.cancel = true
          obj.confirmText = "このカードを更新"
          obj.cancelText = "カードの種類を変更"
          this.configs = obj 
         
          
        },
        onShow() {
            let that = this
          if(that.query.id){
              that.getDetail(that.query.id)  
          }
        },
        computed: {
            // [カスタムエイド 2026-10-20] 特典があるか(特典の写真・題名・内容のどれかがある)
            hasGift() {
                const c = this.card || {}
                if (c.is_discount === 0 || c.is_discount === '0') return false
                return !!((c.discount_images && c.discount_images.length) || c.discount_title || c.discount_content)
            },
            // [カスタムエイド 2026-10-20] カードの枠:お店が画像を選んでいれば画像、なければ今までどおり色
            frameCss() {
                const c = this.card.background_color || ''
                const img = this.card.frame_image
                if (img) {
                    return 'background-color: ' + c + '; background-image: url(' + img + '); background-position: center; ' +
                        (this.card.frame_fit == 'tile' ? 'background-size: 160rpx 160rpx; background-repeat: repeat;' : 'background-size: cover; background-repeat: no-repeat;')
                }
                return 'background: ' + c + ';'
            }
        },
        methods: {
            // [カスタムエイド 2026-10-20] 特典の写真を大きく見る
            previewGift() {
                uni.previewImage({ urls: this.card.discount_images, current: 0 })
            },
            // [カスタムエイド 2026-10-20] カルテは準備中
            openKarte() {
                uni.showToast({ title: 'カルテ機能は準備中です', icon: 'none' })
            },
            // [カスタムエイド 2026-10-20] 空いているマスの1段上から見えるように、カードの中を自動で動かす
            scrollToEmpty() {
                const n = this.list.length
                let done = 0
                this.list.forEach((v) => { if (v.sealed) done++ })
                const first = done >= n ? n - 1 : done
                const a = uni.upx2px(Math.max(0, Math.floor(first / 5) - 1) * this.SEAL_ROW)
                this.sealTop = a + 0.5
                this.$nextTick(() => setTimeout(() => { this.sealTop = a }, 80))
            },
            getStatus1(e){
                let that = this 
                if(e == "confirm"){
                    that.showModule1 = false
                    let data = {}
                    data._method = "put"
                    data.update_type  = "expire_time"
                    let id = that.info.id
                    editMemberInformation(data,id).then((res) => {
                        if(res.code == 200){
                            uni.showToast({
                                title:res.message
                            })
                             that.getDetail(that.query.id)  
                        }else{
                            uni.showToast({
                                title:res.message,
                                icon:"none"
                            })
                        }
                    })
                   
                }else{
                    that.showModule1 = false
                    uni.navigateTo({
                        url:"../../cards/cards?userId="+that.query.id+"&id="+that.info.cid+"&name="+that.info.name
                    })
                }
            },
            formatTime(time){
            	if(time){
            		let date = new Date(time * 1000);
            		let Y = date.getFullYear() + '-';
            		let M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
            		let D = date.getDate() + ' ';
            		return Y+M+D
            	}
            	
            },
            getStatus(e){
              let that = this  
              if(e == "confirm"){
                  let data = {}
                  data.plus = 0
                  data.mid = that.info.id
                  addPoints(data).then((res) => {
                      if(res.code == 200){
                          uni.showToast({
                              title:res.message,
                              icon:"none"
                          })
                          that.getDetail(that.query.id)
                          that.showModule = false
                      }else if(res.code == 888){
                          that.close()
                          that.showModule = true
                      }else{
                          uni.showToast({
                              title:res.message,
                              icon:"none"
                          })
                          that.showModule = false
                      }
                  })
              }else{
                  that.showModule = false
                  uni.navigateTo({
                      url:"../../cards/cards?userId="+that.query.id+"&id="+that.info.cid+"&name="+that.info.name
                  })
              }
            },
            showModal(){
                let that = this
                that.show = true
            },
            toCsv(){
                uni.redirectTo({
                    url:"../../detail/detail?id="+this.info.id
                }) 
            },
            
            formatDate(ms) {
                const date = new Date(ms)
                const Y = date.getFullYear()
                const M = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                const D = date.getDate()
                return `${Y}-${M}-${D}`
            },
            
            close(){
                this.show = false
            },
            
            reduce(){
              this.number -= 1 
            },
            
            plus(){
               if(this.number < this.info.card.count){
                   this.number += 1
               }  
            },
            
            save(){
              let that = this
              let data = {}
              data.plus = that.number
              data.mid = that.info.id
              data.is_qrcode = that.query.source == 1?1:0
              if(that.number * 1 != 0){
                 addPoints(data).then((res) => {
                     that.show = false
                     if(res.code == 200){
                         uni.showToast({
                             title:res.message
                         })
                         that.getDetail(that.info.id)
                     }else if(res.code == 888){
                         that.close()
                         that.showModule = true
                     }else{
                         uni.showToast({
                             title:res.message,
                             icon:"none"
                         })
                     }
                 }) 
              } else{
                   that.close()
              } 
            },
            changeColor(url,color){
             	let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#", "%23"));//转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
             	return res;
             },
            getDetail(id){
                let that = this
                let data = {}
                data.id = id
                getMemberDetail(data).then((res) => {
                    if(res.code == 200){
                        that.info = res.data
                        that.cardInfo = res.data.card
                        let tem = []
                        let obj = {}
                        obj["sealed"] = false
                           obj["sealImage"] = ''
                           obj["date"] = ''
                           obj["time"] = ''
                           obj['admin_name'] = ''
                        // if(res.data.seals.length > 0){
                        //      obj["sealImage"] = res.data.seals[0].seal_icon
                        //      obj["date"] = res.data.seals[0].date
                        //      obj["time"] = res.data.seals[0].time
                        //      obj['admin_name'] = res.data.seals[0].admin_name
                        // }else{
                        //      obj["sealImage"] = ''
                        //      obj["date"] = ''
                        //      obj["time"] = ''
                        //      obj['admin_name'] = ''
                        // }
                        for(let i = 0; i < res.data.card.count - res.data.seals.length; i++){
                            tem.push(obj)
                        }
                        if(res.data.seals.length > 0){
                            that.svgData1 = that.changeColor(that.svgData1,that.info.seals[0].seal_color);
                            that.svgData2 = that.changeColor(that.svgData2,that.info.seals[0].seal_color);
                            that.svgData3 = that.changeColor(that.svgData3,that.info.seals[0].seal_color);
                            that.svgData4 = that.changeColor(that.svgData4,that.info.seals[0].seal_color);
                            that.svgData5 = that.changeColor(that.svgData5,that.info.seals[0].seal_color);
                            that.svgData6 = that.changeColor(that.svgData6,that.info.seals[0].seal_color);
                            that.svgData7 = that.changeColor(that.svgData7,that.info.seals[0].seal_color);
                            that.svgData8 = that.changeColor(that.svgData8,that.info.seals[0].seal_color);
                            that.info.seals.forEach((val,index) => {
                                val.sealed = true
                                if(val.seal_icon.includes("01")){
                                    that.iconNum = 1
                                }else if(val.seal_icon.includes("02")){
                                    that.iconNum = 2
                                }else if(val.seal_icon.includes("03")){
                                    that.iconNum = 3
                                }else if(val.seal_icon.includes("04")){
                                    that.iconNum = 4
                                }else if(val.seal_icon.includes("05")){
                                    that.iconNum = 5
                                }else if(val.seal_icon.includes("06")){
                                    that.iconNum = 6
                                }else if(val.seal_icon.includes("07")){
                                    that.iconNum = 7
                                }else if(val.seal_icon.includes("08")){
                                    that.iconNum = 8
                                }
                            })
                        }
                        that.list = [...that.info.seals , ...tem]
                        that.card = res.data.card
                        that.scrollToEmpty() // [カスタムエイド 2026-10-20]
                        that.disImage = res.data.card.discount_images
                        if(res.data.card.count == res.data.card.seals.length){
                           that.showModule = true
                        }
                        
                        if(res.data.is_valid == 0){
                            if(res.data.card.valid_type == 1){
                                let obj1 = {}
                                obj1.title = 'カード期限切れです'
                                obj1.content = 'カードを更新して下さい'
                                obj1.confirm = true
                                obj1.cancel = true
                                obj1.confirmText = "このカードを更新"
                                obj1.cancelText = "カードの種類を変更"
                                that.configs1 = obj1
                            }
                            
                            if(res.data.card.valid_type == 2){
                                let obj1 = {}
                                obj1.title = 'カード期限切れです'
                                obj1.content = 'カードを更新して下さい'
                                obj1.confirm = false
                                obj1.cancel = true
                                obj1.confirmText = "このカードを更新"
                                obj1.cancelText = "カードの種類を変更"
                                that.configs1 = obj1
                            }
                            
                            that.showModule1 = true
                        }    
                    }
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .tc {
        color: #1A73E8;
    }
    
    .icon {
        background-size: 100% 100%;
        display: inline-block;
        width: 80upx;
        height: 80upx;
        margin: 10upx 0;
    }
    
    .icons{
        background-size: 100% 100%;
        display: inline-block;
        width: 40upx;
        height: 40upx;
        margin: 10upx 0 0;
    }
    
    
    .tableContent {
        height: 360upx;
        overflow-y: scroll;
    
        .contentItem {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 20upx 0;
            border-bottom: 2upx dashed #d2d2d7;
    
            .tableItem {
                flex: 1;
                font-size: 25upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                text-align: center;
                flex-shrink: 0;
            }
    
            .set {
                width: 42upx;
                height: 42upx;
            }
        }
    }
    
    
    .btn{
       width: 328upx;
       height: 100upx;
       background: #1A73E8;
       border-radius: 20upx;
       text-align: center;
       font-size: 40upx;
       color: #fff;
       margin-top: 60upx;
    }
    
    .coiunt{
        font-size: 80upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: rgba(29,29,31,0.9);
        margin: 0 120upx;
    }
    
    .choosecount{
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .reduce,.plus{
        width: 80upx;
        height: 80upx;
        
        image{
            width: 80upx;
            height: 80upx;
        }
    }
    
    .op{
        opacity: 0.6 !important;
    }

    .blank {
        width: 100%;
        height: 40upx;
        background: #FBFBFD;
        z-index: -1;
    }
    
    .popTitle{
        padding: 40upx 0;
        text-align: center;
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
    }

    .userInfo {
        border-top: 2upx solid #d2d2d7;
        border-bottom: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding: 32upx 40upx 12upx;
    }

    .special {
        background: #FFFFFF;
        box-shadow: 0px 0px 10upx 2upx rgba(29, 29, 31, 0.16);
        box-sizing: border-box;
        padding: 40upx 0;

        .remark{
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            box-sizing: border-box;
            padding: 58upx 40upx 40upx;
        }

        .specialTitle {
            width: 144upx;
            height: 60upx;
            background: #D93025;
            border-radius: 20upx;
            font-size: 28upx;
            font-family: Hiragino Sans-W4, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            line-height: 60upx;
            text-align: center;
            position: relative;
            left: 46upx;
            margin-bottom: 20upx;
        }
    }

    .appoint {
        box-sizing: border-box;
        padding: 32upx 46upx;
        box-shadow: 0px 0px 10upx 2upx rgba(0, 0, 0, 0.16);
        margin-top: 40upx;
        margin-bottom: 40upx;

        .appointTitle {
            width: 144upx;
            height: 60upx;
            background: #1A73E8;
            border-radius: 20upx;
            font-size: 28upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            text-align: center;
            line-height: 60upx;
        }

        .appointItem {
            display: flex;
            justify-content: space-between;
            margin-top: 32upx;

            .aItem {
                display: flex;
               
                .aItemImage {
                    width: 40upx;
                    height: 40upx;
                    margin-right: 20upx;
                    flex-shrink: 0;
                    image {
                        width: 100%;
                        height: 100%;
                    }
                }
            }
        }
    }

    .cardInfo {
        box-sizing: border-box;
        padding: 40upx 18upx 0;
        box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);
        background: #fff;
        z-index: 99;

        .cardTitle {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            width: 100%;
        }

        .cardInfos {
            margin-top: 40upx;
            box-sizing: border-box;
            padding: 0 32upx 40upx;
            background: #fff;

            .ciItem {
                display: flex;
                align-items: center;
                margin-bottom: 20upx;

                .ciLeft {
                    width: 144upx;
                    height: 60upx;
                    background: #F5F5F7;
                    font-size: 28upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                    text-align: center;
                    line-height: 60upx;
                    margin-right: 28upx;
                }

                .ciRight {
                    font-size: 28upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }
            }
        }
        
        .cardTable {
            width: 100%;
            height: 100%;
            background: #fff;
            box-sizing: border-box;
            padding: 0 8upx;
            
            .cardTitle {
                display: flex;
                align-items: center;
                border-bottom: 2upx solid #d2d2d7;
                box-sizing: border-box;
                padding: 29upx 0 10upx;
            
                .tableItem {
                    flex: 1;
                    text-align: center;
                    font-size: 25upx;
                    font-family: Hiragino Sans-W5, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
            
                    .set {
                        width: 42upx;
                        height: 42upx;
                    }
                }
            }
        }
        
        .cardInner {
            width: 100%;
            height: 100%;
            background-color: #fff;
            display: flex;
            flex-wrap: wrap;
        
            .seal {
                width: 20%;
                height: 50%;
                box-sizing: border-box;
                border-right: 2upx solid #eaeaea;
                border-bottom: 2upx solid #eaeaea;
                text-align: center;
                position: relative;
        
                .sealed {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    left: 0;
                    top: 32upx;
        
                    image {
                        width: 80upx;
                        height: 80upx;
                    }
        
                    .sealTime {
                        font-size: 20upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #707070;
                        text-align: center;
                    }
                }
        
                text {
                    font-size: 48upx;
                    font-family: Hiragino Sans-W6, Hiragino Sans;
                    font-weight: normal;
                    color: #EAEAEA;
                    line-height: 170upx;
                }
            }
        }

        .cardY {
            width: 100%;
            // height: 398upx;
            background: #53B1AF;
            box-shadow: 0px 0px 10upx 2upx rgba(0, 0, 0, 0.16);
            border-radius: 12upx;
            margin-top: 20upx;
            box-sizing: border-box;
            padding: 36upx;

            .cardInner {
                width: 100%;
                height: 100%;
                background-color: #fff;
                display: flex;
                flex-wrap: wrap;
                // overflow-y: scroll;
                .seal {
                    width: 20%;
                    height: 50%;
                    box-sizing: border-box;
                    border-right: 2upx solid #eaeaea;
                    border-bottom: 2upx solid #eaeaea;
                    text-align: center;
                    position: relative;

                    .sealed {
                        position: absolute;
                        width: 100%;
                        height: 100%;
                        left: 0;
                        top: 32upx;

                        image {
                            width: 80upx;
                            height: 80upx;
                        }

                        .sealTime {
                            font-size: 20upx;
                            font-family: Hiragino Sans-W3, Hiragino Sans;
                            font-weight: normal;
                            color: #707070;
                            text-align: center;
                        }
                    }

                    text {
                        font-size: 48upx;
                        font-family: Hiragino Sans-W6, Hiragino Sans;
                        font-weight: normal;
                        color: #EAEAEA;
                        line-height: 170upx;
                    }
                }
            }
        }
    }
    
    

    .infoItem {
        display: flex;
        align-items: center;
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        margin-bottom: 20upx;

        .itemname {
            color: #707070;
            margin-right: 70upx;
            width: 128upx;

        }

        .itemValue {
            color: #1D1D1F;
        }
    }

    /* [カスタムエイド 2026-10-20] スタンプカードAの中のスクロール(高さは画面で決める)。マスの高さを決まった値に */
    .cs-seal-scroll {
        width: 100%;
        background-color: #fff;
    }

    .cardInner.cs-inner {
        height: auto !important;

        .seal {
            height: 172upx !important;
        }
    }

    /* [カスタムエイド 2026-10-20] 会員ID・顧客NO・カルテのまとまり、カード名、下のまとまり(これまでの画面と同じ見た目) */
    .sl-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 0 32rpx;
        margin: 0 24rpx 32rpx;
    }

    .sl-top {
        margin-top: 32rpx;
    }

    .sl-row {
        display: flex;
        align-items: center;
        min-height: 88rpx;
        border-bottom: 1px solid #E5E5EA;
    }

    .sl-label {
        width: 160rpx;
        flex-shrink: 0;
        font-size: 26rpx;
        color: #6E6E73;
    }

    .sl-val {
        flex: 1;
        font-size: 30rpx;
        color: #1D1D1F;
    }

    .sl-karte {
        display: flex;
        align-items: center;
        min-height: 104rpx;
    }

    .sl-hover {
        opacity: 0.6;
    }

    /* カルテの印(書類のかたち) */
    .sl-karte-ic {
        width: 40rpx;
        height: 48rpx;
        flex-shrink: 0;
        margin-right: 20rpx;
        padding: 10rpx 8rpx;
        border: 3rpx solid #1A73E8;
        border-radius: 8rpx;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .sl-karte-line {
        height: 3rpx;
        background: #1A73E8;
    }

    .sl-karte-line.short {
        width: 60%;
    }

    .sl-karte-name {
        font-size: 30rpx;
        font-weight: 700;
        color: #1A73E8;
    }

    .sl-soon {
        margin-left: 16rpx;
        padding: 2rpx 14rpx;
        border-radius: 8rpx;
        background: #FEF3E0;
        color: #B06000;
        font-size: 22rpx;
        font-weight: 700;
    }

    /* 右向きの矢印 */
    .sl-chev {
        margin-left: auto;
        width: 14rpx;
        height: 14rpx;
        border-right: 3rpx solid #C7C7CC;
        border-bottom: 3rpx solid #C7C7CC;
        transform: rotate(-45deg);
    }

    .sl-title {
        margin: 0 32rpx 4rpx;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
        color: #1D1D1F;
    }

    /* [カスタムエイド 2026-10-20] 下のまとまり(カード・有効期限と備考・予約・特典)を、これまでの画面と同じ白い角丸のまとまりに */
    .containe {
        min-height: 100vh;
        background: #F5F5F7;
        padding-bottom: calc(60rpx + env(safe-area-inset-bottom));
    }

    .cardInfo {
        margin: 0 24rpx 32rpx;
        padding: 32rpx 24rpx 8rpx;
        border-radius: 32rpx;
        box-shadow: none;
    }

    .cardInfo .cardInfos {
        margin-top: 24rpx;
        padding: 0 8rpx 16rpx;
    }

    .cardInfo .cardInfos .ciItem {
        align-items: flex-start;
        margin: 0;
        padding: 22rpx 0;
        border-top: 1px solid #E5E5EA;
    }

    .cardInfo .cardInfos .ciItem .ciLeft {
        width: 150rpx;
        height: auto;
        margin-right: 16rpx;
        background: none;
        text-align: left;
        line-height: 1.6;
        font-size: 26rpx;
        color: #6E6E73;
    }

    .cardInfo .cardInfos .ciItem .ciRight {
        flex: 1;
        font-size: 28rpx;
        line-height: 1.6;
        white-space: pre-wrap;
    }

    .appoint {
        margin: 0 24rpx 32rpx;
        padding: 32rpx;
        border-radius: 32rpx;
        background: #FFFFFF;
        box-shadow: none;
    }

    .appoint .appointTitle,
    .special .specialTitle {
        display: inline-block;
        width: auto;
        height: 48rpx;
        line-height: 48rpx;
        padding: 0 22rpx;
        border-radius: 12rpx;
        font-size: 24rpx;
        font-weight: 700;
    }

    .appoint .appointItem {
        margin-top: 24rpx;
        font-size: 28rpx;
    }

    .special {
        margin: 0 24rpx 32rpx;
        padding: 32rpx 0 8rpx;
        border-radius: 32rpx;
        overflow: hidden;
        box-shadow: none;
    }

    .special .specialTitle {
        left: 32rpx;
    }

    .special .remark {
        padding: 28rpx 32rpx 24rpx;
        font-size: 28rpx;
        line-height: 1.8;
    }

    /* [カスタムエイド 2026-10-20] スタンプ満了のお知らせ */
    .fl {
        padding: 20rpx 36rpx calc(40rpx + env(safe-area-inset-bottom));
        text-align: center;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .fl-grip {
        width: 72rpx;
        height: 10rpx;
        border-radius: 6rpx;
        background: #D2D2D7;
        margin: 0 auto 28rpx;
    }

    .fl-who {
        font-size: 26rpx;
        color: #6E6E73;
    }

    .fl-h1 {
        margin-top: 4rpx;
        font-size: 40rpx;
        font-weight: 800;
    }

    .fl-gift {
        display: flex;
        align-items: flex-start;
        margin-top: 28rpx;
        padding: 24rpx;
        border-radius: 28rpx;
        background: #FFF8E1;
        border: 1px solid #F0D77A;
        text-align: left;
    }

    .fl-gimg {
        width: 168rpx;
        height: 128rpx;
        flex-shrink: 0;
        margin-right: 24rpx;
        border-radius: 20rpx;
        background: #E5E5EA;
    }

    .fl-gtext {
        flex: 1;
        min-width: 0;
    }

    .fl-glabel {
        font-size: 22rpx;
        font-weight: 700;
        color: #8A6D00;
        margin-bottom: 4rpx;
    }

    .fl-gtitle {
        font-size: 28rpx;
        font-weight: 700;
        line-height: 1.5;
    }

    .fl-gbody {
        font-size: 26rpx;
        line-height: 1.6;
        color: #3A3A3C;
        white-space: pre-wrap;
    }

    .fl-info {
        margin-top: 28rpx;
        padding: 24rpx 28rpx;
        border-radius: 28rpx;
        background: #F5F5F7;
        font-size: 26rpx;
        line-height: 1.7;
        color: #3A3A3C;
        text-align: left;
    }

    .fl-btn,
    .fl-btn2 {
        height: 104rpx;
        border-radius: 28rpx;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 30rpx;
        font-weight: 700;
        box-sizing: border-box;
    }

    .fl-btn {
        margin-top: 32rpx;
        background: #1A73E8;
        color: #FFFFFF;
    }

    .fl-btn2 {
        margin-top: 20rpx;
        border: 1px solid #1A73E8;
        color: #1A73E8;
    }

    .fl-small {
        font-size: 22rpx;
        font-weight: 400;
        margin-top: 2rpx;
    }

    .fl-later {
        margin-top: 24rpx;
        padding: 12rpx 0;
        font-size: 28rpx;
        color: #6E6E73;
    }

    .fl-hover {
        opacity: 0.7;
    }
</style>
