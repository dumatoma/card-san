<template>
    <view>
        <u-navbar :title="titles" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56"
            :border="true" @leftClick="leftClick">
        </u-navbar>
        <view class="h-title">
            お客様情報の入力
        </view>
        <!-- [カスタムエイド 2026-10-26b] 版Bモック(画面5)の文言に(元「メンバーズカード作成に必要なお客様情報のご提供に、ご協力お願いします。」) -->
        <!-- [カスタムエイド 2026-10-27] 案B:もらえるカードとお店の名前を横に並べる(アプリを入れている人もカードを見てから登録できるように) -->
        <view class="cs-get" v-if="csCard">
            <view class="cs-get-card" :style="{ background: (csCard.card && csCard.card.background_color) || '#F2F2F4' }">
                <image v-if="csCard.card && csCard.card.image" class="cs-get-img" :src="csCard.card.image" mode="widthFix"></image>
                <view v-else class="cs-get-blank"></view>
            </view>
            <view class="cs-get-txt">
                <view class="cs-get-name">{{ csCard.shop_name || titles }}</view>
                <view class="cs-get-sub">このカードがもらえます</view>
            </view>
        </view>
        <!-- [カスタムエイド 2026-10-27] 坂本さんの指示の文言に(元「お店が必要としている項目だけ入力してください。」) -->
        <view class="fo-txt">
            お店の会員登録をします。<br/>必須項目のみの入力でOKです。
        </view>
        <view class="modify-user">
            <u--form labelPosition="top" :model="model1" ref="form1" :labelWidth="200">
                <view class="tt">氏名<text v-if="must1" style="color: red;">*(必須)</text></view>
                <u-form-item ref="item1">
                    <u--input v-model="model1.userInfo.name" @change="nameChange"></u--input>
                </u-form-item>
                <view class="tt">カナ<text v-if="must2" style="color: red;">*(必須)</text></view>
                <u-form-item ref="item1">
                    <u--input v-model="model1.userInfo.pronunciation" @change="pChange"></u--input>
                </u-form-item>
                <view class="tt">電話番号<text v-if="must3" style="color: red;">*(必須)</text></view>
                <u-form-item prop="userInfo.tel" ref="item1">
                    <u--input v-model="model1.userInfo.tel" type="number" maxlength="11"
                        @change="phoneChange"></u--input>
                </u-form-item>
                <view class="tt">性別<text v-if="must4" style="color: red;">*(必須)</text></view>
                <u-form-item prop="userInfo.sex" @click="showSex = true" ref="item1">
                    <u--input v-model="model1.userInfo.sexName" disabled disabledColor="#F6F5F8" placeholder="選択"
                        placeholderStyle=" color:#AAAAAA ;" suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB">
                    </u--input>
                    <view class="" style=" width: 40%;"></view>
                </u-form-item>
                <view class="tt">生年月日<text v-if="must5" style="color: red;">*(必須)</text></view>
                <u-form-item prop="userInfo.time" @click="show = true; hideKeyboard()" ref="item1">
                    <u--input v-model="model1.userInfo.year" disabled disabledColor="#F6F5F8" placeholder="年"
                        suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB"
                        placeholderStyle=" text-align: center ; color:#AAAAAA ;" inputAlign="center">
                    </u--input>
                    <view style="width: 5%;"></view>
                    <u--input v-model="model1.userInfo.month" disabled disabledColor="#F6F5F8" placeholder="月"
                        suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB"
                        placeholderStyle=" text-align: center;  color:#AAAAAA ;" inputAlign="center">
                    </u--input>
                    <view style="width: 5%;"></view>
                    <u--input v-model="model1.userInfo.day" disabled disabledColor="#F6F5F8" placeholder="日"
                        suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB"
                        placeholderStyle=" text-align: center;  color:#AAAAAA ;" inputAlign="center">
                    </u--input>
                </u-form-item>
                <view class="tt">郵便番号<text v-if="must6" style="color: red;">*(必須)</text></view>
                <u-form-item prop="userInfo.addSn" ref="item1">
                    <u--input v-model="model1.userInfo.zipCode" placeholder="ハイフンなし" @change="zipChange"></u--input>
                    <view style="width: 10%;"></view>
                    <view class="bns" @click="searchAddress">
                        住所検索
                    </view>
                </u-form-item>
                <u-form-item prop="userInfo.addSn" ref="item1" @click="showAdd = true">
                    <u--input v-model="model1.userInfo.addSn" disabled disabledColor="#EAEAEA " placeholder="都道府県"
                        suffixIcon="arrow-down" suffixIconStyle="color: #A9A8AB"
                        placeholderStyle=" color:#868688  ;"></u--input>
                    <view style="width: 40%;"></view>
                </u-form-item>
                <view class="tt">住所1<text v-if="must6" style="color: red;">*(必須)</text></view>
                <u-form-item prop="userInfo.address1" ref="item1">
                    <u--input v-model="model1.userInfo.address1" placeholder="市区町村、番地"
                        @change="addressChange"></u--input>
                </u-form-item>
                <view class="tt">住所2</view>
                <u-form-item prop="userInfo.address2" ref="item1">
                    <u--input v-model="model1.userInfo.address2" placeholder="建物名、部屋番号"></u--input>
                </u-form-item>
            </u--form>
            <u-action-sheet :show="showSex" :actions="actions" title="" @close="showSex = false" @select="sexSelect">
            </u-action-sheet>
            <!-- [カスタムエイド 2026-10-22] 新しいドラムロール(見出し・選んだ日付を大きく) -->
            <cs-date-picker :show="show" v-model="value1" :minDate="-631180800000"
                @confirm="timeConfirm($event)" @cancel="cancel()" @close="cancel()"></cs-date-picker>
            <u-modal :show="keepShow" :title="title" :content='content' confirmText="ok" @confirm="keepShow = false">
            </u-modal>
            <cs-picker title="都道府県を選ぶ" @close="showAdd = false"
                @confirm="confirm" @cancel="showAdd = false" :show="showAdd" :columns="city"></cs-picker>
        </view>
        <!-- [カスタムエイド 2026-10-26b] 版Bモック(画面5):ボタンを「お店のカードを入手」に(元「メンバー登録」)・角をいっぱいまで丸く・黄緑。下に説明を追加 -->
        <view class="logins" @click="sub" :style="insertAll == true?'' : 'opacity:0.6 !important'" hover-class="op">
            お店のカードを入手
        </view>
        <view class="cs-logins-note">入力内容は、アカウント情報の空欄にも保存され、次のお店で使えます</view>
        <view style="height: 20upx;"></view>
        <u-modal :show="showm" :title="titlem" :content='contentm' confirmText="OK" @confirm="confirmm"></u-modal>
    </view>
</template>

<script>
    import csPicker from "@/components/csPicker/csPicker.vue" // [カスタムエイド 2026-10-22] 新しいドラムロール
    import csDatePicker from "@/components/csPicker/csDatePicker.vue"
    import {
        getAddress,
        getMyCard,
        getUserInfos,
        getShopInfo
    } from "@/api/index.js"
    import { fetchShopCard } from '@/utils/csShopLink.js' // [カスタムエイド 2026-10-27] もらえるカードの画像(ログインなしのお店の情報)
    export default {
        components: { csPicker, csDatePicker },
        data() {
            return {
                csCard: null, // [カスタムエイド 2026-10-27] もらえるカード(店名・カードの画像)
                showm:false,
                titlem:"ありがとうございます！",
                contentm:"",
                must1: false,
                must2: false,
                must3: false,
                must4: false,
                must5: false,
                must6: false,
                show: false,
                insertAll: true, 
                noLogin: false,
                showAdd: false,
                keepShow: false,
                titles: uni.getStorageSync("user").name || '',
                value1: Number(new Date()),
                showSex: false,
                title: 'アカウント情報の更新',
                content: 'アカウント情報を更新しました',
                ass: [0,0,0,0,0,0],
                as: [],
                city: [
                    ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都',
                        '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府',
                        '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県',
                        '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
                    ]
                ],
                model1: {
                    userInfo: {
                        name: '',
                        pronunciation: '',
                        tel: '',
                        year: '',
                        month: '',
                        day: '',
                        time: '',
                        sex: 0, // [カスタムエイド 2026-10-19] 初期値を 2(女性)から 0(未選択)に
                        sexName: "",
                        addSn: '',
                        address1: '',
                        address2: '',
                        zipCode: ""
                    },
                },
                query: {},
                // [カスタムエイド 2026-10-19] 一番上に「------(未選択)」を追加。選ぶと 0(未回答)で保存。性別が必須の店舗では未選択のままでは登録できない
                actions: [{
                        name: '------(未選択)',
                        sex: 0
                    },
                    {
                        name: '男性',
                        sex: 1
                    },
                    {
                        name: '女性',
                        sex: 2
                    },
                    {
                        name: 'Xジェンダー',
                        sex: 3
                    },
                ],
                // rules: {
                //   'userInfo.name': {
                //     type: 'string',
                //     required: true,
                //     message: '请填写姓名',
                //     trigger: ['blur', 'change']
                //   },
                //   'userInfo.time': {
                //     type: 'string',
                //     required: true,
                //     message: '请选择时间',
                //     trigger: ['blur', 'change']
                //   },
                //   'userInfo.sex': {
                //     type: 'string',
                //     max: 1,
                //     required: true,
                //     message: '请选择男或女',
                //     trigger: ['blur']
                //   },
                //   'userInfo.tel': [{
                //       required: true,
                //       message: '请输入手机号',
                //       trigger: ['change', 'blur'],
                //     },
                //     {
                //       // 自定义验证函数，见上说明
                //       validator: (rule, value, callback) => {
                //         // 上面有说，返回true表示校验通过，返回false表示不通过
                //         // uni.$u.test.mobile()就是返回true或者false的
                //         return uni.$u.test.mobile(value);
                //       },
                //       message: '手机号码不正确',
                //       // 触发器可以同时用blur和change
                //       trigger: ['change', 'blur'],
                //     }
                //   ],
                // },
            };
        },
        created() {

        },
        onLoad(options) {
            let that = this
            uni.showLoading({
                title:'読み込み中',
                mask:true
            })
            that.query = options
            that.titles = decodeURIComponent(options.name) 
            that.getUserInfo()
            that.getShopInfo(options.sid)
            fetchShopCard(options.sid, that.$baseUrl).then((d) => { that.csCard = d }) // [カスタムエイド 2026-10-27]
        },
        methods: {
            checkMust() {
                let that = this
                const isInclude = (arr1, arr2) => arr2.every((val) => arr1.includes(val))
                if (isInclude(that.ass, that.as)) {
                    that.insertAll = true
                } else {
                    that.insertAll = false
                }
            },
            addressChange(e) {
                let that = this
                if(e != '' && that.model1.userInfo.zipCode != ''){
                    that.ass[5] = "address"
                }else{
                    that.ass[5] = "0"
                }
                that.checkMust()
            },
            phoneChange(e) {
                let that = this
                if (e == '') {
                    that.ass[2] = "0"
                } else {
                    that.ass[2] = 'phone'
                }
                that.checkMust()
            },
            pChange(e) {
                let that = this
                if (e == '') {
                    that.ass[1] = "0"
                } else {
                    that.ass[1] = 'pronunciation'
                }
                that.checkMust()
            },
            nameChange(e) {
                let that = this
                if (e == '') {
                    that.ass[0] = "0"
                } else {
                    that.ass[0] = 'name'
                }
                that.checkMust()
            },
            zipChange(e){
                let that = this
                if(e != '' && that.model1.userInfo.address1 != ''){
                    that.ass[5] = "address"
                }else{
                    that.ass[5] = "0"
                }
                that.checkMust()
            },
            leftClick() {
                uni.navigateBack()
            },
            getShopInfo(id) {
                let that = this
                getShopInfo(id).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        // [カスタムエイド 2026-10-19] 氏名はどの店舗でも必須(サーバーでも必須)。店舗の設定の一覧には氏名が入らないため、ここで必須に加える
                        that.as = (res.data.shop.member_register_field || []).slice()
                        if (!that.as.includes("name")) {
                            that.as.push("name")
                        }
                        that.must1 = true
                        if (res.data.shop.member_register_field.includes("pronunciation")) {
                            that.must2 = true
                        }
                        if (res.data.shop.member_register_field.includes("phone")) {
                            that.must3 = true
                        }
                        if (res.data.shop.member_register_field.includes("sex")) {
                            that.must4 = true
                        }
                        if (res.data.shop.member_register_field.includes("birthday")) {
                            that.must5 = true
                        }
                        if (res.data.shop.member_register_field.includes("address")) {
                            that.must6 = true
                        }
                        that.checkMust()
                    }
                }).catch(() => { uni.hideLoading() })
            },
            getUserInfo() {
                let that = this
                getUserInfos().then((res) => {
                    if (res.code == 200) {
                        that.model1.userInfo.name = res.data.user.name
                        that.model1.userInfo.pronunciation = res.data.user.pronunciation
                        that.model1.userInfo.tel = res.data.user.phone
                        that.model1.userInfo.year = res.data.user.birthday_year
                        that.model1.userInfo.month = res.data.user.birthday_month
                        that.model1.userInfo.day = res.data.user.birthday_day
                        that.model1.userInfo.sex = res.data.user.sex
                        that.model1.userInfo.zipCode = res.data.user.zip_code
                        that.model1.userInfo.address1 = res.data.user.address1
                        that.model1.userInfo.addSn = res.data.user.province
                        that.model1.userInfo.address2 = res.data.user.address2
                        that.model1.userInfo.sexName = res.data.user.sex == 1 ? '男性' : res.data.user.sex == 2 ?
                            '女性' : res.data.user.sex == 3 ? 'Xジェンダー' : ''


                        if (res.data.user.name) {
                            that.ass[0] = "name"
                        } else {
                            that.ass[0] = "0"
                        }

                        if (res.data.user.pronunciation) {
                            that.ass[1] = "pronunciation"
                        } else {
                            that.ass[1] = "0"
                        }

                        if (res.data.user.phone) {
                            that.ass[2] = "phone"
                        } else {
                            that.ass[2] = "0"
                        }

                        if (res.data.user.birthday_year) {
                            that.ass[4] = "birthday"
                        } else {
                            that.ass[4] = "0"
                        }

                        if (res.data.user.sex) {
                            that.ass[3] = "sex"
                        } else {
                            that.ass[3] = "0"
                        }

                        if (res.data.user.zip_code && res.data.user.province && res.data.user.address1) {
                            that.ass[5] = "address"
                        } else {
                            that.ass[5] = "0"
                        }

                        that.checkMust()
                    }
                }).catch(() => {})
            },
            sexSelect(e) {
                // [カスタムエイド 2026-10-19] 「------(未選択)」を選んだときは未入力として扱う(必須の店舗では登録ボタンが押せない)
                if (e.sex == 0) {
                    this.model1.userInfo.sexName = ""
                    this.model1.userInfo.sex = 0
                    this.ass[3] = "0"
                } else {
                    this.model1.userInfo.sexName = e.name
                    this.model1.userInfo.sex = e.sex
                    this.ass[3] = "sex"
                }
                this.checkMust()
            },
            searchAddress() {
                let that = this
                uni.showLoading({
                    title: "検索中"
                })
                getAddress(that.model1.userInfo.zipCode).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        that.model1.userInfo.addSn = res.data.address.address1
                        that.model1.userInfo.address1 = res.data.address.address2 + res.data.address.address3
                        that.addressChange(res.data.address.address1)
                        that.checkMust()
                    }
                }).catch(() => { uni.hideLoading() })
            },
            confirmm(){
                uni.redirectTo({
                    url: "../../pages/index/index"
                })
            },
            sub() {
                let that = this
             
                let info = that.model1.userInfo
                let data = {}
                data["cid"] = that.query.id
                data["name"] = info.name
                data["pronunciation"] = info.pronunciation
                data["phone"] = info.tel
                data["sex"] = info.sex
                data["birthday_year"] = info.year
                data["birthday_month"] = info.month
                data["birthday_day"] = info.day
                data["zip_code"] = info.zipCode
                data["province"] = info.addSn
                data["address1"] = info.address1
                data["address2"] = info.address2
                if (that.insertAll) {
                    uni.showLoading({
                        title:'読み込み中',
                        mask:true
                    })
                    getMyCard(data).then((res) => {
                        uni.hideLoading()
                        if (res.code == 200) {
                            // [カスタムエイド 2026-10-26b] 版Bモック(画面6):お知らせの窓は出さず、My Card に戻って「[店名] のカードを追加しました」を出す
                            uni.setStorageSync('csAddedCard', { name: that.titles, at: Date.now() })
                            uni.reLaunch({ url: "/pages/index/index" }) // [カスタムエイド 2026-10-26c] My Card が2枚重ならないよう、画面を積み直す
                            // uni.showModal({
                            //     title: "ありがとうございます！",
                            //     content: that.titles + "\nのカードを追加しました",
                            //     showCancel: false,
                            //     confirmText: "OK",
                            //     success(res) {
                            //         if (res.confirm) {
                            //             uni.redirectTo({
                            //                 url: "../../pages/index/index"
                            //             })
                            //         } else {

                            //         }
                            //     }
                            // })
                        } else {
                            uni.showToast({
                                title: res.message,
                                duration: 2000,
                                icon: "none"
                            })
                        }
                    }).catch(() => { uni.hideLoading() })
                }

            },
            confirm(e) {
                this.model1.userInfo.addSn = e.value[0]
                this.showAdd = false
            },
            timeConfirm(type, value) {
                this.model1.userInfo.year = uni.$u.timeFormat(type.value, 'yyyy')
                this.model1.userInfo.month = uni.$u.timeFormat(type.value, 'mm')
                this.model1.userInfo.day = uni.$u.timeFormat(type.value, 'dd')
                this.model1.userInfo.time = uni.$u.timeFormat(type.value, 'yyyy-mm-dd')
                this.show = false
                this.ass[4] = "birthday"
                this.checkMust()
            },
            cancel() {
                this.show = false
            }
        }
    }
</script>

<style lang="scss" scoped>
    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }
    .tt{
        // margin-bottom: 10upx;
        margin-top: 60upx;
    }
    /* [カスタムエイド 2026-10-26b] 版Bモック(画面5):黄緑 #AAF409・黒い文字・角をいっぱいまで丸く(元 青 #1A73E8・白い文字・角 20rpx) */
    .logins {
        width: 90%;
        margin: 120rpx auto 0;

        height: 108rpx;
        line-height: 108rpx;
        background-color: #AAF409;
        color: #1D1D1F;
        font-weight: 700;
        text-align: center;
        font-size: 33rpx;
        border-radius: 54rpx;
    }
    .cs-logins-note { width: 90%; margin: 20rpx auto 60rpx; text-align: center; font-size: 23rpx; color: #6E6E73; line-height: 1.6; }

    .op{
        opacity: 0.6 !important;
    }
    /* [カスタムエイド 2026-10-27] 案B:もらえるカードとお店の名前 */
    .cs-get { width: 85%; margin: 40rpx auto 0; padding: 20rpx; box-sizing: border-box; background: #F6F6F8; border-radius: 24rpx; display: flex; align-items: center; }
    .cs-get-card { width: 240rpx; flex: none; border-radius: 16rpx; overflow: hidden; border: 1px solid #E5E5EA; }
    .cs-get-img { display: block; width: 100%; }
    .cs-get-blank { height: 150rpx; }
    .cs-get-txt { flex: 1; min-width: 0; margin-left: 24rpx; }
    .cs-get-name { font-size: 30rpx; font-weight: 700; color: #1D1D1F; word-break: break-all; }
    .cs-get-sub { font-size: 24rpx; color: #6E6E73; margin-top: 8rpx; }
    .fo-txt {
        width: 85%;
        margin: 0 auto;
        margin-top: 60rpx;
        color: #707070;
        font-size: 32rpx;
        text-align: center;
        margin-bottom: 80rpx;
    }

    .h-title {
        height: 210rpx;
        line-height: 210rpx;
        background: #F5F5F7;
        color: #1D1D1F;
        font-size: 48rpx;
        font-weight: 700;
        text-align: center;
        border-bottom: 1px solid #D2D2D7;
    }

    ::v-deep .u-modal__title {
        font-size: 32rpx;
        color: #1D1D1F;
        font-weight: 700;
    }

    ::v-deep .u-modal {
        border-radius: 28rpx;
    }

    ::v-deep .u-line {
        border-color: #707070 !important;
    }

    ::v-deep .u-modal__button-group__wrapper__text {
        font-size: 32rpx;
        color: #1A73E8;
        font-weight: 700;
    }

    ::v-deep .u-modal__content__text {
        color: #1D1D1F;
        font-size: 28rpx;
        text-align: center;
    }

    ::v-deep .u-form-item__body__left__content__label {
        font-size: 32rpx;
        color: #707070;
        width: 100%;
    }

    ::v-deep .u-input {
        height: 88rpx;
        width: 100%;
        border-radius: 20rpx;
        padding: 0 20rpx;
        border: 2rpx solid #D2D2D7;
        box-sizing: border-box;
    }

    .modify-user {
        width: 90%;
        margin: 0 auto;
    }

    .bns {
        width: 200rpx;
        height: 88rpx;
        text-align: center;
        line-height: 88rpx;
        color: #FFFFFF;
        background: #1A73E8;
        border-radius: 20rpx;
    }
</style>