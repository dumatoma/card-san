<template>
    <view>
        <!-- 自定义顶部导航 -->
        <u-navbar title="店舗情報" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true">
            <view class="u-nav-slot" slot="right">
                <image src="../../../static/icons/preview.svg" style="width: 50upx;height: 50upx;"
                    @click="showPopup = true" mode="aspectFill"></image>
            </view>
        </u-navbar>
        <view class="contain border_top">
            <view class="topImage">
                <view class="bannerItem" v-for="(item,index) in carousel">
                    <image :src="item" mode="aspectFill"></image>
                    <view class="close" @click="del(index)">
                        <image src="../../../static/icons/wclose.png" mode=""></image>
                    </view>
                </view>
            </view>
            <view class="xz">
                TOP画像(6枚まで掲載可)
            </view>
            <view class="addImage" hover-class="btnhover" @click="uploadImage">
                <text>+</text>
                <view>画像アップロード</view>
            </view>
        </view>
        <view class="title">
            基本情報 
        </view>
        <view class="basicInfo border_bottom">
            <view class="infoItem">
                <view class="infoTitle">
                    プロフィール
                </view>
                <view class="infoDetail">
                    <u-textarea v-model="info.des"
                        placeholder="あなたのビジネスのプロフィールを入力してください。"></u-textarea>
                </view>
            </view>
            
            <view class="infoItem">
                <view class="infoTitle">
                    <text style="color: #d93025;">※</text> 業種(店舗情報には反映されません)
                </view>
                <view class="infoDetail">
                    <view class="pickers" @click="show = true">
                        <view class="pickerName" v-text="info.shop_cate_name || ' 業種を選択'" :style="info.shop_cate_name?'':'color:rgb(192,192,192)'"></view>
                        <u-icon name="arrow-down"></u-icon>
                    </view>
                </view>
            </view>

            <view class="infoItem">
                <view class="infoTitle">
                    電話番号
                </view>
                <view class="infoDetail">
                    <u-input placeholder="0312345678" v-model="info.phone"></u-input>
                </view>
            </view>

            <view class="infoItem">
                <view class="infoTitle">
                    営業時間
                </view>
                <view class="infoDetail">
                    <u-textarea placeholder="平日9:00~20:00
土日祝 9:00~23:00" v-model="info.business_time"></u-textarea>
                </view>
            </view>

            <view class="infoItem">
                <view class="infoTitle">
                    定休日
                </view>
                <view class="infoDetail">
                    <u-textarea placeholder="毎週月曜日・第２、第４火曜日
夏季休暇、年末年始休暇など" v-model="info.rest_time"></u-textarea>
                </view>
            </view>
        </view>
        <view class="calendarShows border_bottom border_top">
            <view>
                営業カレンダー
            </view>
            <u-switch v-model="info.business_calendar" activeColor="#34C759"></u-switch>
        </view>
        <view class="tip">
            ※OFFにすると営業カレンダーが表示されません
        </view>
        <view class="addressContent">
            <view class="addressItem">
                <view class="addressItemTitle">
                    郵便番号
                </view>
                <view class="addressItemContent">
                    <view class="addItemLeft">
                        <u-input placeholder="123456" v-model="info.zip_code"></u-input>
                    </view>
                    <view @click="searchAddress" id="addBtn">
                        住所検索
                    </view>
                   <!-- <u-button type="primary" text=""  @click="" style="height: 88upx !important;font-size: 32upx;line-height: 88upx;padding: 0 !important;"></u-button> -->
                </view>
            </view>
            <view class="addressItem">
                <view class="addressItemContent">
                    <view class="addItemLeft">
                        <view class="pickers" @click="show1 = true">
                            <view class="pickerName" v-text="info.province || '都道府県'"></view>
                            <u-icon name="arrow-down"></u-icon>
                        </view>
                    </view>
                </view>
            </view>
            <view class="infoItem">
                <view class="infoTitle">
                    住所1
                </view>
                <view class="infoDetail">
                    <u-input v-model="info.address1" placeholder="市区町村・番地"></u-input>
                </view>
            </view>
            <view class="infoItem">
                <view class="infoTitle">
                    住所2
                </view>
                <view class="infoDetail">
                    <u-input v-model="info.address2" placeholder="その他住所、建物名、号室など"></u-input>
                </view>
            </view>
            <view class="infoItem">
                <view class="infoTitle">
                    アクセス説明
                </view>
                <view class="infoDetail">
                    <u-textarea v-model="info.traffic_explain"
                        placeholder="お店へのアクセスを分かりやすく記入してください。駅から徒歩3分、コンビニのすぐ横など。"></u-textarea>
                </view>
            </view>
        </view>
        <view class="title">
            詳細情報
        </view>
        <view class="basicInfo border_bottom">
            <view class="infoItem">
                <view class="infoTitle">
                    ホームページURL
                </view>
                <view class="infoDetail">
                    <u-input v-model="info.web_url" placeholder="http://~"></u-input>
                </view>
            </view>
            <view class="infoItem">
                <view class="infoTitle">
                    利用可能決済
                </view>
                <view class="infoDetail">
                    <u-textarea v-model="info.pay_type"
                        placeholder="現金、クレジットカード(VISA/Master/JCB/Amex/Diners)、 PayPayなど"></u-textarea>
                </view>
            </view>
            <view class="infoItem">
                <view class="infoTitle">
                    備考欄
                </view>
                <view class="infoDetail">
                    <u-textarea placeholder="追記があればご記入ください。
駐車場あり、保証制度付きなど。" v-model="info.remark"></u-textarea>
                </view>
            </view>
        </view>
        <view class="title border_bottom">
            その他設定
        </view>
        <view class="switchContent">
            <view class="switchItem">
                <view class="switchLeft">
                    スタッフ紹介
                </view>
                <view class="switchRight">
                    <u-switch v-model="info.staff_des" activeColor="#34C759"></u-switch>
                </view>
            </view>
            <view class="switchItem">
                <view class="switchLeft">
                    友達に共有(LINK)
                </view>
                <view class="switchRight">
                    <u-switch v-model="info.share_line" activeColor="#34C759"></u-switch>
                </view>
            </view>
           <view class="switchItem">
                <view class="switchLeft">
                    SNSリンク
                </view>
                <view class="switchRight">
                    <u-switch v-model="info.sns_url" activeColor="#34C759"></u-switch>
                </view>
            </view>
            <view class="SNScontent" v-if="info.sns_url == true">
                <view class="SNSitem">
                    <view class="sitem_left" @click="chooseThis(0)"> 
                        <view class="selectContent">
                            <view class="unselect" v-if="info.sns_ins == 0"></view>
                            <view class="selected" v-if="info.sns_ins == 1">
                                <view class="fill"></view>
                            </view>
                        </view>
                    </view>
                    <view class="sitem_right">
                        <view class="right_top">
                            Instagram
                        </view>
                        <view class="right_bototm" v-if="info.sns_ins == 1">
                            <u-input v-model="info.sns_ins_url" border="bottom" placeholder="アカウントURLを入力してください。"></u-input>
                        </view>
                    </view>
                </view>
                
                <!-- <view class="SNSitem">
                    <view class="sitem_left" @click="chooseThis(1)">
                        <view class="selectContent">
                            <view class="unselect" v-if="info.sns_twitter == 0"></view>
                            <view class="selected" v-if="info.sns_twitter == 1">
                                <view class="fill"></view>
                            </view>
                        </view>
                    </view>
                    <view class="sitem_right">
                        <view class="right_top">
                            Twitter
                        </view>
                        <view class="right_bototm" v-if="info.sns_twitter == 1">
                            <u-input v-model="info.sns_twitter_url" border="bottom" placeholder="アカウントURLを入力してください。"></u-input>
                        </view>
                    </view>
                </view> -->
                
                <view class="SNSitem">
                    <view class="sitem_left" @click="chooseThis(2)">
                        <view class="selectContent">
                            <view class="unselect" v-if="info.sns_facebook == 0"></view>
                            <view class="selected" v-if="info.sns_facebook == 1">
                                <view class="fill"></view>
                            </view>
                        </view>
                    </view>
                    <view class="sitem_right">
                        <view class="right_top">
                            Facebook
                        </view>
                        <view class="right_bototm" v-if="info.sns_facebook == 1">
                            <u-input v-model="info.sns_facebook_url" border="bottom" placeholder="アカウントURLを入力してください。"></u-input>
                        </view>
                    </view>
                </view>
                
                <view class="SNSitem">
                    <view class="sitem_left" @click="chooseThis(3)">
                        <view class="selectContent">
                            <view class="unselect" v-if="info.sns_youtobe == 0"></view>
                            <view class="selected" v-if="info.sns_youtobe == 1">
                                <view class="fill"></view>
                            </view>
                        </view>
                    </view>
                    <view class="sitem_right">
                        <view class="right_top">
                            Youtube
                        </view>
                        <view class="right_bototm" v-if="info.sns_youtobe == 1">
                            <u-input v-model="info.sns_youtobe_url" border="bottom" placeholder="アカウントURLを入力してください。"></u-input>
                        </view>
                    </view>
                </view>
                
            </view>
        </view>
        <view class="save">
            <view id="save" @click="save" hover-class="hoc">
                保存する
            </view>
        </view>
        <view class="blank"></view>
        <u-popup :show="showPopup" round="32">
             <view class="infoShow">
            <preview :infos="info" @close="showPopup = false"></preview>
            </view>
        </u-popup>
        <u-picker :closeOnClickOverlay="true" @close="show = false" cancelText="キャンセル" confirmText="保存"
            @confirm="confirm" @cancel="show = false" :show="show" :columns="cates" keyName="value"></u-picker>
        <u-picker :closeOnClickOverlay="true" @close="show1 = false" cancelText="キャンセル" confirmText="保存"
            @confirm="confirm1" @cancel="show1 = false" :show="show1" :columns="city"></u-picker>
    </view>
</template>

<script>
    import preview from "@/components/preview/preview.vue"
    import {
        getShopInfo,
        getAddress,
        setShopInfo,
        getConfig
    } from '@/api/index.js'
    export default {
        components: {
            preview
        },
        data() {
            return {
                showPopup: false,
                info: {},
                show: false,
                show1:false,
                carousel:[],
                cates: [],
                city: [
                    ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都',
                        '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府',
                        '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県',
                        '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
                    ]
                ],
            };
        },
        created() {
            let that = this
            that.getDetail()
            that.getConfig()
        },
        onLoad() {
            
        },
        methods: {
            back() {
                uni.navigateBack()
            },
            chooseThis(e){
              let that = this
              let t = 0
              if(e == 0){
                  that.info.sns_ins = that.info.sns_ins == 1?0:1
              }else if(e == 1){
                   that.info.sns_twitter=that.info.sns_twitter == 1? 0 : 1
              }else if(e == 2){
                   that.info.sns_facebook=that.info.sns_facebook == 1? 0 : 1
              }else if(e == 3){
                   that.info.sns_youtobe=that.info.sns_youtobe == 1? 0 : 1
              }
            },
            getConfig(){
              let that = this
              getConfig().then((res) => {
                  if(res.code == 200){
                      let array = []
                      Object.keys(res.data.config.shop_cate).map((k,item) =>{
                          let obj = {}
                          obj.key = k
                          obj.value = res.data.config.shop_cate[k]
                          array.push(obj)
                      }) 
                      that.cates.push(array)
                  }
              })
            },
            getDetail() {
                let that = this
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        if(res.data.shop_info.business_calendar == 1){
                            res.data.shop_info.business_calendar = true
                        }else{
                            res.data.shop_info.business_calendar = false
                        }
                        
                        if(res.data.shop_info.staff_des == 1){
                            res.data.shop_info.staff_des = true
                        }else{
                            res.data.shop_info.staff_des = false
                        }
                        
                        if(res.data.shop_info.share_line == 1){
                            res.data.shop_info.share_line = true
                        }else{
                            res.data.shop_info.share_line = false
                        }
                        
                        if(res.data.shop_info.sns_url == 1){
                            res.data.shop_info.sns_url = true
                        }else{
                            res.data.shop_info.sns_url = false
                        }
                        that.info = res.data.shop_info
                        that.carousel = res.data.shop_info.carousel
                    }
                })
            },
            // 选择图片并上传
            uploadImage() {
                let that = this
                if (that.info.carousel.length < 6) {
                    uni.chooseImage({
                        count: 6 - that.info.carousel.length,
                        success: (res) => {
                            const tempPaths = res.tempFilePaths
                            for (let i = 0; i < tempPaths.length; i++) {
                                uni.uploadFile({
                                    url: that.$baseUrl+'/tool/upload/file',
                                    filePath: res.tempFilePaths[i],
                                    name: 'file',
                                    success: (result) => {
                                        if (result.errMsg == "uploadFile:ok") {
                                            let re = JSON.parse(result.data)
                                            if (re.code == 200) {
                                                that.info.carousel.push(re.data.path)
                                            }
                                        }
                                    }
                                })
                            }
                        }
                    });
                } else {
                    uni.showToast({
                        title: "画像は6枚まで掲載可"
                    })
                }
            },
            del(index){
              let that = this  
              that.carousel.splice(index,1)
            },
            confirm(e) {
                this.info.shop_cate = e.value[0].key * 1
                this.show = false
                this.info.shop_cate_name = e.value[0].value
            },
            searchAddress() {
                let that = this
                uni.showLoading({
                    title: "検索中"
                })
                getAddress(that.info.zip_code).then((res) => {
                    if (res.code == 200) {
                        that.info.province = res.data.address.address1
                        that.info.address1 = res.data.address.address2 + res.data.address.address3
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: "none"
                        })
                    }
                    uni.hideLoading()
                })
            },
            confirm1(e){
                this.info.province = e.value[0]
                this.show1 = false
            },
            save(){
                let that = this
                let data1 = JSON.stringify(that.info) 
                let data = JSON.parse(data1)
                data.type = "info"
                
                if(data.business_calendar == true){
                    data.business_calendar = 1
                }else{
                    data.business_calendar = 0
                }
                
                if(data.staff_des == true){
                    data.staff_des = 1
                }else{
                    data.staff_des = 0
                }
                
                if(data.share_line == true){
                    data.share_line = 1
                }else{
                    data.share_line = 0
                }
                
                if(data.sns_url == true){
                    data.sns_url = 1
                }else{
                    data.sns_url = 0
                }
                
                uni.showLoading({
                    title:"保存中"
                })
                setShopInfo(data).then((res) => {
                    if(res.code == 200){
                        uni.showToast({
                            title:res.message,
                            duration:2500
                        })
                        uni.hideLoading()
                        setTimeout(()=> {
                            uni.navigateBack()
                        },2500)
                        
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
            }
        }
    }
</script>

<style lang="scss">
    ::v-deep .u-button__text{
        span{
           font-size: 16px !important; 
        }
        
    }
    .u-border{
        border: none !important;
        border: 2upx solid #d2d2d7 !important;
        // width: 100% !important;
    }
    .hoc{
        background-color: rgba(26,115,232,0.6) !important;
    }
    .SNScontent{
        box-sizing: border-box;
        padding: 44upx 60upx;
    }
    .right_bototm{
        width: 100%;
        
        input{
            border: none !important;
        }
    }
   .infoShow{
       height: 90vh;
       width: 100%;
       overflow-y: scroll;
       border-radius: 32rpx 32rpx 0 0;
   }
    .sitem_right{
        position: relative;
        top: -8upx;
        margin-left: 20upx;
        width: 100%;
    }
    .right_top{
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
    }
    .SNSitem{
        display: flex;
        align-items: flex-start;
        margin-bottom: 80upx;
    }
    #addBtn {
        width: 200upx;
        height: 76upx;
        box-sizing: border-box;
        padding: 0 !important;
        background: #1A73E8;
        line-height: 76upx;
        color: #fff;
        text-align: center;
        border-radius: 8upx;
    }

    .pTitle {
        position: relative;
        height: 111upx;
        background: #FBFBFD;
        border-radius: 32upx;

        .pt {
            width: 100%;
            text-align: center;
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            line-height: 111upx;
        }

        .closeImage {
            width: 60upx;
            height: 60upx;
            position: absolute;
            top: 26upx;
            left: 32upx;

            image {
                width: 100%;
                height: 100%;
            }
        }
    }
    
    .btnhover{
        background: rgba(29,29,31,.16) !important;
    }

    .pContent {
        width: 100%;
        height: 80vh;
        overflow-y: scroll;
    }

    .blank {
        height: 120upx;
    }

    .switchItem {
        display: flex;
        box-sizing: border-box;
        padding: 44upx 36upx;
        justify-content: space-between;
        border-bottom: 2upx solid #d2d2d7;
    }
    
    .u-button__text{
        font-size: 40upx !important;
    }
    
    #save {
        width: 658upx;
        height: 100upx;
        border-radius: 12upx;
        margin-top: 120upx;
        font-size: 40upx !important;
        background: #1A73E8;
        text-align: center;
        color: #fff;
        line-height: 100upx;
        margin: 120upx auto 0;
    }

    .basicInfo {
        box-sizing: border-box;
        padding: 60upx 46upx;
    }

    .addressContent {
        box-sizing: border-box;
        padding: 0 46upx;
    }

    .addItemLeft {
        width: 400upx;
        // height: 88upx;
    }

    .addressItem {
        box-sizing: border-box;
        margin-bottom: 60upx;

        .addressItemTitle {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #86868B;
            margin-bottom: 10upx;
        }

        .addressItemContent {
            display: flex;
            justify-content: space-between;


            .addressItemContent {

                .addItemLeft {
                    width: 400upx;
                    height: 88upx;
                }

                .addItemRight {
                     height: 88upx !important;
                }
            }
        }
    }


    .tip {
        box-sizing: border-box;
        padding: 10upx 0 80upx 46upx;
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #AAAAAA;
    }

    .calendarShows {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 44upx 36upx;
    }

    .infoItem {
        margin-bottom: 60upx;
    }

    .infoTitle {
        margin-bottom: 10upx;
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
    }

    .pickers {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        height: 80upx;
        background: rgba(249, 249, 249, 0.94);
        border-radius: 20upx;
        opacity: 1;
        border: 2upx solid #AAAAAA;
        box-sizing: border-box;
        padding: 0 20upx;
    }

    .title {
        background: #f5f5f7;
        box-sizing: border-box;
        padding: 44upx 46upx;
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: rgba(29, 29, 31, 0.9);
    }

    .xz {
        margin: 40upx 0 40upx;
        text-align: center;
        font-size: 22upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
    }

    .addImage {
        width: 480upx;
        height: 96upx;
        background: #F5F5F7;
        border: 2upx solid #707070;
        text-align: center;
        line-height: 96upx;
        margin: 0 auto 98upx;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32upx;
        border-radius: 12upx;

        text {
            font-size: 60upx;
            margin-right: 26upx;
            position: relative;
            top: -5upx;
        }
    }

    .topImage {
        box-sizing: border-box;
        padding-top: 40upx;
        display: flex;
        overflow-x: scroll;
        padding-left: 28upx;

        .bannerItem {
            width: 514upx;
            height: 344upx;
            flex-shrink: 0;
            margin-right: 46upx;
            position: relative;

            .close {
                width: 52upx;
                height: 52upx;
                position: absolute;
                right: 14upx;
                top: 14upx;
                z-index: 9;

                image {
                    width: 100%;
                    height: 100%;
                }
            }

            image {
                width: 100%;
                height: 100%;
            }
        }
    }
</style>
