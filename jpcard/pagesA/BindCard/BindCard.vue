<template>
    <view>
        <u-navbar :title="titles" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56"
            :border="true" @leftClick="leftClick">
        </u-navbar>
        <view class="h-title">
            お客様情報の入力
        </view>
        <view class="fo-txt">
            メンバーズカード作成に必要なお客様情報のご提供に、ご協力お願いします。
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
            <u-datetime-picker :show="show" v-model="value1" mode="date" :minDate="-631180800000"
                @confirm="timeConfirm($event)" @cancel="cancel()">
            </u-datetime-picker>
            <u-modal :show="keepShow" :title="title" :content='content' confirmText="ok" @confirm="keepShow = false">
            </u-modal>
            <u-picker :closeOnClickOverlay="true" @close="showAdd = false" cancelText="キャンセル" confirmText="保存"
                @confirm="confirm" @cancel="showAdd = false" :show="showAdd" :columns="city"></u-picker>
        </view>
        <view class="logins" @click="sub" :style="insertAll == true?'' : 'opacity:0.6 !important'" hover-class="op">
            メンバー登録
        </view>
        <view style="height: 20upx;"></view>
        <u-modal :show="showm" :title="titlem" :content='contentm' confirmText="OK" @confirm="confirmm"></u-modal>
    </view>
</template>

<script>
    import {
        getAddress,
        getMyCard,
        getUserInfos,
        getShopInfo
    } from "@/api/index.js"
    export default {
        data() {
            return {
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
                ass: [0,0,0,0,0],
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
                        sex: 2,
                        sexName: "",
                        addSn: '',
                        address1: '',
                        address2: '',
                        zipCode: ""
                    },
                },
                query: {},
                actions: [{
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
            console.log(options)
            that.query = options
            that.titles = decodeURIComponent(options.name) 
            that.getUserInfo()
            that.getShopInfo(options.sid)
        },
        methods: {
            checkMust() {
                let that = this
                console.log("arr1",that.ass)
                console.log("arr2",that.as)
                const isInclude = (arr1, arr2) => arr2.every((val) => arr1.includes(val))
                if (isInclude(that.ass, that.as)) {
                    that.insertAll = true
                } else {
                    that.insertAll = false
                }
            },
            addressChange(e) {
                let that = this
                console.log("e="+e)
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
                console.log("id=",id)
                getShopInfo(id).then((res) => {
                    console.log('res', res)
                    uni.hideLoading()
                    if (res.code == 200) {
                        that.as = res.data.shop.member_register_field
                        console.log(that.as)
                        if (res.data.shop.member_register_field.includes("name")) {
                            that.must1 = true
                        }
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
                })
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

                        if (res.data.user.address) {
                            that.ass[5] = "address1"
                        } else {
                            that.ass[5] = "0"
                        }
                       
                        that.checkMust()
                    }
                })
            },
            sexSelect(e) {
                console.log("sexchange")
                this.model1.userInfo.sexName = e.name
                this.model1.userInfo.sex = e.sex
                this.ass[3] = "sex"
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
                })
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
                            that.showm = true
                            that.contentm = that.titles + "\nのカードを追加しました"
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
                    })
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
    .logins {
        width: 90%;
        margin: 120rpx auto;

        height: 112rpx;
        line-height: 112rpx;
        background-color: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        border-radius: 20rpx;
    }

    .op{
        opacity: 0.6 !important;
    }
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