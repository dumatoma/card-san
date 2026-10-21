<template>
    <!-- [カスタムエイド 2026-10-20] 店舗情報の画面を新しい見た目に(モック「管理App 店舗情報 改善モック」)
         ・TOP画像:3列の一覧。1枚目に「TOP」の印、長押しでつかんで並び替え、押すと拡大、×で削除、まとめて追加
         ・入力欄:角を丸く、入力中は枠を青く+うっすら光る、間隔を詰めて白いまとまりに分けた
         ・複数行の欄:続きがあるときは下をぼかして知らせる(Android は右にバーも出る)
         ・SNSのURL欄:ほかの欄と同じ枠・同じ線の色に -->
    <page-meta :page-style="dragIndex > -1 ? 'overflow: hidden;' : ''"></page-meta>
    <view class="si-page">
        <!-- 自定义顶部导航 -->
        <u-navbar title="店舗情報" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="right">
                <image src="../../../static/icons/preview.svg" style="width: 50upx;height: 50upx;"
                    @click="showPopup = true" mode="aspectFill"></image>
            </view>
        </u-navbar>

        <view class="si-body">
            <!-- TOP画像 -->
            <view class="si-card">
                <view class="si-img-head">
                    <view class="si-img-title">
                        <text class="si-h2">TOP画像</text>
                        <text class="si-count">{{images.length}}枚</text>
                    </view>
                    <view class="si-add-btn" hover-class="si-hover" @click="uploadImage">
                        <text class="si-plus">+</text>追加
                    </view>
                </view>
                <view class="si-note">1枚目がTOPに最初に出ます。画像を長押しすると、つかんで順番を入れ替えられます。複数枚をまとめて選べます。</view>

                <view class="si-grid" id="siGrid">
                    <view class="si-tile" v-for="(item, index) in shownImages" :key="item"
                        :class="{ main: index == 0, drag: dragIndex == index }" @click="previewImg(index)"
                        @longpress="startDrag(index)" @touchmove="moveDrag" @touchend="endDrag" @touchcancel="endDrag">
                        <image class="si-tile-img" :src="item" mode="aspectFill"></image>
                        <view class="si-del" @click.stop="del(index)">
                            <view class="si-del-in">
                                <view class="si-x"></view>
                            </view>
                        </view>
                        <text class="si-no">{{index + 1}}</text>
                        <text class="si-top" v-if="index == 0">TOP</text>
                    </view>
                    <view class="si-tile si-add-tile" hover-class="si-hover" @click="uploadImage">
                        <text class="si-plus big">+</text>
                        <text>追加</text>
                    </view>
                </view>
                <view class="si-more" v-if="images.length > LIMIT" hover-class="si-hover" @click="showAll = !showAll">
                    {{ showAll ? '閉じる' : 'すべて表示(全' + images.length + '枚)' }}
                </view>
                <view class="si-sub">推奨:横長(3:2)。アップロード時に自動で軽くします。</view>
            </view>

            <!-- 基本情報 -->
            <view class="si-h3">基本情報</view>
            <view class="si-card">
                <view class="si-field">
                    <view class="si-label">プロフィール</view>
                    <view class="si-ta-wrap">
                        <textarea class="si-ta" :class="{ on: fk == 'des' }" v-model="info.des" maxlength="-1"
                            :show-confirm-bar="false" :disable-default-padding="true" :cursor-spacing="120"
                            placeholder-class="si-ph" placeholder="あなたのビジネスのプロフィールを入力してください。"
                            @focus="fk = 'des'" @blur="fk = ''"></textarea>
                        <view class="si-fade" v-if="fk != 'des' && isLong(info.des)"></view>
                    </view>
                </view>

                <view class="si-field">
                    <view class="si-label"><text style="color: #d93025;">※</text> 業種(店舗情報には反映されません)</view>
                    <view class="si-in si-select" hover-class="si-hover" @click="show = true">
                        <text :class="{ 'si-ph': !info.shop_cate_name }">{{ info.shop_cate_name || '業種を選択' }}</text>
                        <u-icon name="arrow-down" color="#6E6E73" size="14"></u-icon>
                    </view>
                </view>

                <view class="si-field">
                    <view class="si-label">電話番号</view>
                    <input class="si-in" :class="{ on: fk == 'phone' }" v-model="info.phone" type="number" maxlength="-1"
                        :cursor-spacing="120" placeholder-class="si-ph" placeholder="0312345678"
                        @focus="fk = 'phone'" @blur="fk = ''" />
                </view>

                <view class="si-field">
                    <view class="si-label">営業時間</view>
                    <view class="si-ta-wrap">
                        <textarea class="si-ta" :class="{ on: fk == 'bt' }" v-model="info.business_time" maxlength="-1"
                            :show-confirm-bar="false" :disable-default-padding="true" :cursor-spacing="120"
                            placeholder-class="si-ph" :placeholder="'平日9:00~20:00\n土日祝 9:00~23:00'"
                            @focus="fk = 'bt'" @blur="fk = ''"></textarea>
                        <view class="si-fade" v-if="fk != 'bt' && isLong(info.business_time)"></view>
                    </view>
                </view>

                <view class="si-field">
                    <view class="si-label">定休日</view>
                    <view class="si-ta-wrap">
                        <textarea class="si-ta" :class="{ on: fk == 'rt' }" v-model="info.rest_time" maxlength="-1"
                            :show-confirm-bar="false" :disable-default-padding="true" :cursor-spacing="120"
                            placeholder-class="si-ph" :placeholder="'毎週月曜日・第２、第４火曜日\n夏季休暇、年末年始休暇など'"
                            @focus="fk = 'rt'" @blur="fk = ''"></textarea>
                        <view class="si-fade" v-if="fk != 'rt' && isLong(info.rest_time)"></view>
                    </view>
                </view>

                <view class="si-switch-row si-line-top">
                    <view>
                        <view class="si-switch-name">営業カレンダー</view>
                        <view class="si-switch-tip">OFFにすると営業カレンダーが表示されません</view>
                    </view>
                    <u-switch v-model="info.business_calendar" activeColor="#34C759"></u-switch>
                </view>
            </view>

            <!-- 住所・アクセス -->
            <view class="si-h3">住所・アクセス</view>
            <view class="si-card">
                <view class="si-field">
                    <view class="si-label">郵便番号</view>
                    <view class="si-row">
                        <input class="si-in si-flex" :class="{ on: fk == 'zip' }" v-model="info.zip_code" type="number"
                            maxlength="-1" :cursor-spacing="120" placeholder-class="si-ph" placeholder="1234567"
                            @focus="fk = 'zip'" @blur="fk = ''" />
                        <view class="si-btn" hover-class="si-btn-hover" @click="searchAddress">住所を検索</view>
                    </view>
                </view>

                <view class="si-field">
                    <view class="si-label">都道府県</view>
                    <view class="si-in si-select" hover-class="si-hover" @click="show1 = true">
                        <text :class="{ 'si-ph': !info.province }">{{ info.province || '都道府県を選択' }}</text>
                        <u-icon name="arrow-down" color="#6E6E73" size="14"></u-icon>
                    </view>
                </view>

                <view class="si-field">
                    <view class="si-label">住所1</view>
                    <input class="si-in" :class="{ on: fk == 'a1' }" v-model="info.address1" maxlength="-1"
                        :cursor-spacing="120" placeholder-class="si-ph" placeholder="市区町村・番地"
                        @focus="fk = 'a1'" @blur="fk = ''" />
                </view>

                <view class="si-field">
                    <view class="si-label">住所2</view>
                    <input class="si-in" :class="{ on: fk == 'a2' }" v-model="info.address2" maxlength="-1"
                        :cursor-spacing="120" placeholder-class="si-ph" placeholder="その他住所、建物名、号室など"
                        @focus="fk = 'a2'" @blur="fk = ''" />
                </view>

                <view class="si-field">
                    <view class="si-label">アクセス説明</view>
                    <view class="si-ta-wrap">
                        <textarea class="si-ta" :class="{ on: fk == 'te' }" v-model="info.traffic_explain" maxlength="-1"
                            :show-confirm-bar="false" :disable-default-padding="true" :cursor-spacing="120"
                            placeholder-class="si-ph" placeholder="お店へのアクセスを分かりやすく記入してください。駅から徒歩3分、コンビニのすぐ横など。"
                            @focus="fk = 'te'" @blur="fk = ''"></textarea>
                        <view class="si-fade" v-if="fk != 'te' && isLong(info.traffic_explain)"></view>
                    </view>
                </view>
            </view>

            <!-- 追加情報 -->
            <view class="si-h3">追加情報</view>
            <view class="si-card">
                <view class="si-field">
                    <view class="si-label">ホームページURL</view>
                    <input class="si-in" :class="{ on: fk == 'web' }" v-model="info.web_url" maxlength="-1"
                        :cursor-spacing="120" placeholder-class="si-ph" placeholder="https://~"
                        @focus="fk = 'web'" @blur="fk = ''" />
                </view>

                <view class="si-field">
                    <view class="si-label">利用可能決済</view>
                    <view class="si-ta-wrap">
                        <textarea class="si-ta" :class="{ on: fk == 'pay' }" v-model="info.pay_type" maxlength="-1"
                            :show-confirm-bar="false" :disable-default-padding="true" :cursor-spacing="120"
                            placeholder-class="si-ph" placeholder="現金、クレジットカード(VISA/Master/JCB/Amex/Diners)、 PayPayなど"
                            @focus="fk = 'pay'" @blur="fk = ''"></textarea>
                        <view class="si-fade" v-if="fk != 'pay' && isLong(info.pay_type)"></view>
                    </view>
                </view>

                <view class="si-field">
                    <view class="si-label">備考欄</view>
                    <view class="si-ta-wrap">
                        <textarea class="si-ta" :class="{ on: fk == 'rm' }" v-model="info.remark" maxlength="-1"
                            :show-confirm-bar="false" :disable-default-padding="true" :cursor-spacing="120"
                            placeholder-class="si-ph" :placeholder="'追記があればご記入ください。\n駐車場あり、保証制度付きなど。'"
                            @focus="fk = 'rm'" @blur="fk = ''"></textarea>
                        <view class="si-fade" v-if="fk != 'rm' && isLong(info.remark)"></view>
                    </view>
                </view>
            </view>

            <!-- その他設定 -->
            <view class="si-h3">その他設定</view>
            <view class="si-card si-card-list">
                <view class="si-switch-row si-line-bottom">
                    <view class="si-switch-name">スタッフ紹介</view>
                    <u-switch v-model="info.staff_des" activeColor="#34C759"></u-switch>
                </view>
                <view class="si-switch-row si-line-bottom">
                    <view class="si-switch-name">友達に共有(LINK)</view>
                    <u-switch v-model="info.share_line" activeColor="#34C759"></u-switch>
                </view>
                <view class="si-switch-row" :class="{ 'si-line-bottom': info.sns_url == true }">
                    <view class="si-switch-name">SNSリンク</view>
                    <u-switch v-model="info.sns_url" activeColor="#34C759"></u-switch>
                </view>

                <view class="si-sns" v-if="info.sns_url == true">
                    <view class="si-field" v-for="s in snsList" :key="s.key">
                        <view class="si-sns-head">
                            <view class="si-label">{{s.name}}</view>
                            <view class="si-check" @click="chooseThis(s.idx)">
                                <view class="si-box" :class="{ on: info[s.key] == 1 }">
                                    <u-icon v-if="info[s.key] == 1" name="checkmark" color="#FFFFFF" size="12" bold></u-icon>
                                </view>
                                <text>アプリに表示</text>
                            </view>
                        </view>
                        <input class="si-in" :class="{ on: fk == s.key }" v-model="info[s.key + '_url']" maxlength="-1"
                            :cursor-spacing="120" placeholder-class="si-ph" placeholder="アカウントURLを入力してください"
                            @focus="fk = s.key" @blur="fk = ''" />
                    </view>
                </view>
            </view>

            <view class="si-save" hover-class="si-btn-hover" @click="save">保存する</view>
        </view>

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
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
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
                // [カスタムエイド 2026-10-20] 新しい見た目で使うもの
                fk: '', // 入力中の欄
                LIMIT: 8, // TOP画像:最初に見せる枚数(それより多いと「すべて表示」)
                showAll: false,
                dragIndex: -1, // 長押しでつかんでいる画像
                gridRect: null,
                snsList: [
                    { key: 'sns_ins', name: 'Instagram', idx: 0 },
                    { key: 'sns_facebook', name: 'Facebook', idx: 2 },
                    { key: 'sns_youtobe', name: 'YouTube', idx: 3 }
                ],
                city: [
                    ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都',
                        '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府',
                        '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県',
                        '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
                    ]
                ],
            };
        },
        computed: {
            // [カスタムエイド 2026-10-20] TOP画像
            images() {
                return Array.isArray(this.info.carousel) ? this.info.carousel : []
            },
            shownImages() {
                return (this.showAll || this.dragIndex > -1) ? this.images : this.images.slice(0, this.LIMIT)
            }
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
                        // [カスタムエイド 2026-10-20] TOP画像がないお店でも、追加・並び替えができるように
                        if (!Array.isArray(res.data.shop_info.carousel)) res.data.shop_info.carousel = []
                        that.info = res.data.shop_info
                        that.carousel = res.data.shop_info.carousel
                    }
                })
            },
            // [カスタムエイド 2026-10-20] 複数行の欄に入りきらない文があるか(目安:1行およそ20文字・欄に見えるのは3行)
            isLong(v) {
                if (!v) return false
                let lines = 0
                String(v).split('\n').forEach((l) => {
                    lines += Math.max(1, Math.ceil(l.length / 20))
                })
                return lines > 3
            },
            // 选择图片并上传
            uploadImage() {
                let that = this
                // [カスタムエイド 2026-10-14] 6枚の上限をなくした。一度に選べるのは9枚まで(端末の選択画面の上限)
                if (!Array.isArray(that.info.carousel)) that.$set(that.info, 'carousel', [])
                // [カスタムエイド 2026-10-20] 自動で軽くした画像を使う。選んだ順番のまま追加し、追加中はお知らせを出す
                pickImage({
                    count: 9,
                    sizeType: ['compressed'],
                    success: (res) => {
                        const tempPaths = res.tempFilePaths || []
                        if (!tempPaths.length) return
                        const paths = new Array(tempPaths.length)
                        let left = tempPaths.length
                        let failed = 0
                        uni.showLoading({ title: 'アップロード中', mask: true })
                        const done = () => {
                            left--
                            if (left > 0) return
                            uni.hideLoading()
                            paths.forEach((p) => {
                                if (p) that.info.carousel.push(p)
                            })
                            if (failed) {
                                uni.showToast({ title: failed + '枚をアップロードできませんでした', icon: 'none' })
                            }
                        }
                        for (let i = 0; i < tempPaths.length; i++) {
                            uni.uploadFile({
                                url: that.$baseUrl+'/tool/upload/file',
                                filePath: tempPaths[i],
                                name: 'file',
                                success: (result) => {
                                    let ok = false
                                    if (result.errMsg == "uploadFile:ok") {
                                        try {
                                            let re = JSON.parse(result.data)
                                            if (re.code == 200) {
                                                paths[i] = re.data.path
                                                ok = true
                                            }
                                        } catch (e) {}
                                    }
                                    if (!ok) failed++
                                },
                                fail: () => {
                                    failed++
                                },
                                complete: done
                            })
                        }
                    }
                });
            },
            del(index){
              let that = this
              that.info.carousel.splice(index,1)
            },
            // [カスタムエイド 2026-10-20] 押すと大きく表示
            previewImg(index) {
                if (this.dragIndex > -1) return
                uni.previewImage({
                    urls: this.images,
                    current: index
                })
            },
            // [カスタムエイド 2026-10-20] 長押しでつかんで、指の位置に合わせて並び替える
            startDrag(index) {
                this.dragIndex = index
                this.gridRect = null
                uni.vibrateShort({})
                uni.createSelectorQuery().in(this).select('#siGrid').boundingClientRect((r) => {
                    this.gridRect = r
                }).exec()
            },
            moveDrag(e) {
                if (this.dragIndex < 0 || !this.gridRect) return
                const t = (e.touches && e.touches[0]) || (e.changedTouches && e.changedTouches[0])
                if (!t) return
                const r = this.gridRect
                const gap = uni.upx2px(16)
                const w = (r.width - gap * 2) / 3
                const h = w * 2 / 3
                const x = t.clientX - r.left
                const y = t.clientY - r.top
                let col = Math.floor(x / (w + gap))
                let row = Math.floor(y / (h + gap))
                col = Math.min(2, Math.max(0, col))
                row = Math.max(0, row)
                const n = this.info.carousel.length
                const to = Math.min(n - 1, row * 3 + col)
                const from = this.dragIndex
                if (to == from) return
                const item = this.info.carousel.splice(from, 1)[0]
                this.info.carousel.splice(to, 0, item)
                this.dragIndex = to
            },
            endDrag() {
                if (this.dragIndex < 0) return
                // 指を離した直後の「押す(拡大)」を出さない
                setTimeout(() => {
                    this.dragIndex = -1
                }, 300)
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
    /* [カスタムエイド 2026-10-20] 店舗情報の新しい見た目。ほかの画面に広げるときも同じ値を使う
       入力欄の枠 #D2D2D7 / 区切り線 #E5E5EA / 入力中 #1A73E8 / 角の丸み 24rpx
       間隔:項目名と欄 12rpx・欄と次の項目 28rpx・まとまりの間 40rpx */
    .infoShow{
        height: 90vh;
        width: 100%;
        overflow-y: scroll;
        border-radius: 32rpx 32rpx 0 0;
    }

    .si-page {
        min-height: 100vh;
        background: #F5F5F7;
        font-family: 'Hiragino Sans', sans-serif;
        color: #1D1D1F;
    }

    .si-body {
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
    }

    .si-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 32rpx;
        margin-bottom: 40rpx;
        display: flex;
        flex-direction: column;
    }

    .si-card-list {
        padding: 8rpx 32rpx;
    }

    .si-h3 {
        margin: 0 8rpx 16rpx;
        font-size: 30rpx;
        font-weight: 700;
        color: #515154;
    }

    .si-field {
        display: flex;
        flex-direction: column;
        margin-bottom: 28rpx;
    }

    .si-field:last-child {
        margin-bottom: 0;
    }

    .si-label {
        font-size: 26rpx;
        font-weight: 500;
        color: #1D1D1F;
        margin-bottom: 12rpx;
    }

    /* 入力欄(1行) */
    .si-in {
        width: 100%;
        box-sizing: border-box;
        height: 88rpx;
        line-height: normal;
        padding: 0 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        background: #FFFFFF;
        font-size: 30rpx;
        color: #1D1D1F;
        transition: border-color .15s, box-shadow .15s;
    }

    .si-in.on,
    .si-ta.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    .si-ph {
        color: #8A8A8E;
    }

    .si-select {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* 入力欄(複数行) */
    .si-ta-wrap {
        position: relative;
    }

    .si-ta {
        width: 100%;
        box-sizing: border-box;
        height: 192rpx;
        padding: 20rpx 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        background: #FFFFFF;
        font-size: 30rpx;
        line-height: 1.6;
        color: #1D1D1F;
        transition: border-color .15s, box-shadow .15s;
    }

    /* Android など、バーの見た目を変えられる端末では右にバーを出す */
    .si-ta textarea::-webkit-scrollbar,
    .si-ta::-webkit-scrollbar {
        display: block;
        width: 6px;
    }

    .si-ta textarea::-webkit-scrollbar-thumb,
    .si-ta::-webkit-scrollbar-thumb {
        background: #C7C7CC;
        border-radius: 3px;
    }

    /* 続きがあるときの、下のぼかし */
    .si-fade {
        position: absolute;
        left: 2px;
        right: 2px;
        bottom: 2px;
        height: 56rpx;
        border-radius: 0 0 22rpx 22rpx;
        background: linear-gradient(rgba(255, 255, 255, 0), #FFFFFF 85%);
        pointer-events: none;
    }

    .si-row {
        display: flex;
        align-items: center;
    }

    .si-flex {
        flex: 1;
        min-width: 0;
        width: auto;
    }

    .si-btn {
        flex-shrink: 0;
        height: 88rpx;
        line-height: 88rpx;
        margin-left: 16rpx;
        padding: 0 32rpx;
        border-radius: 24rpx;
        background: #1A73E8;
        color: #FFFFFF;
        font-size: 28rpx;
        font-weight: 500;
    }

    .si-btn-hover {
        opacity: 0.8;
    }

    .si-hover {
        opacity: 0.6;
    }

    /* スイッチの行 */
    .si-switch-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 104rpx;
    }

    .si-line-top {
        border-top: 1px solid #E5E5EA;
        margin-top: 8rpx;
        padding-top: 20rpx;
    }

    .si-line-bottom {
        border-bottom: 1px solid #E5E5EA;
    }

    .si-switch-name {
        font-size: 30rpx;
        color: #1D1D1F;
    }

    .si-switch-tip {
        font-size: 22rpx;
        color: #6E6E73;
        margin-top: 4rpx;
    }

    /* SNS */
    .si-sns {
        padding: 28rpx 0 32rpx;
    }

    .si-sns-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12rpx;

        .si-label {
            margin-bottom: 0;
        }
    }

    .si-check {
        display: flex;
        align-items: center;
        height: 64rpx;
        padding-left: 16rpx;
        font-size: 26rpx;
        color: #515154;
    }

    .si-box {
        width: 40rpx;
        height: 40rpx;
        box-sizing: border-box;
        border-radius: 12rpx;
        border: 3rpx solid #AEAEB2;
        background: #FFFFFF;
        margin-right: 12rpx;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .si-box.on {
        border: 0;
        background: #1A73E8;
    }

    /* TOP画像 */
    .si-img-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20rpx;
    }

    .si-img-title {
        display: flex;
        align-items: baseline;
    }

    .si-h2 {
        font-size: 34rpx;
        font-weight: 700;
        margin-right: 16rpx;
    }

    .si-count {
        font-size: 28rpx;
        color: #515154;
    }

    .si-add-btn {
        height: 72rpx;
        padding: 0 28rpx;
        border-radius: 36rpx;
        background: #1A73E8;
        color: #FFFFFF;
        font-size: 28rpx;
        font-weight: 500;
        display: flex;
        align-items: center;
    }

    .si-plus {
        font-size: 36rpx;
        line-height: 1;
        margin-right: 6rpx;
        position: relative;
        top: -2rpx;
    }

    .si-plus.big {
        font-size: 48rpx;
        margin-right: 0;
    }

    .si-note {
        font-size: 26rpx;
        line-height: 1.6;
        color: #515154;
        margin-bottom: 24rpx;
    }

    .si-grid {
        display: flex;
        flex-wrap: wrap;
    }

    .si-tile {
        position: relative;
        width: calc((100% - 32rpx) / 3);
        height: 0;
        padding-bottom: calc((100% - 32rpx) / 3 * 2 / 3);
        margin-right: 16rpx;
        margin-bottom: 16rpx;
        border-radius: 20rpx;
        overflow: hidden;
        background: #E5E5EA;
        transition: transform .12s;
    }

    .si-tile:nth-child(3n) {
        margin-right: 0;
    }

    .si-tile.main {
        box-shadow: 0 0 0 4rpx #1A73E8;
    }

    .si-tile.drag {
        transform: scale(1.06);
        opacity: 0.9;
        z-index: 5;
        box-shadow: 0 12rpx 32rpx rgba(0, 0, 0, 0.25);
    }

    .si-tile-img {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
    }

    .si-del {
        position: absolute;
        top: 0;
        right: 0;
        width: 80rpx;
        height: 80rpx;
        display: flex;
        justify-content: flex-end;
        z-index: 2;
    }

    .si-del-in {
        margin: 10rpx;
        width: 48rpx;
        height: 48rpx;
        border-radius: 24rpx;
        background: rgba(29, 29, 31, 0.72);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* × は線2本で描く(文字の部品を使わないので、どの端末でも同じに出る) */
    .si-x {
        position: relative;
        width: 22rpx;
        height: 22rpx;
    }

    .si-x::before,
    .si-x::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        width: 26rpx;
        height: 4rpx;
        margin: -2rpx 0 0 -13rpx;
        border-radius: 2rpx;
        background: #FFFFFF;
        transform: rotate(45deg);
    }

    .si-x::after {
        transform: rotate(-45deg);
    }

    .si-no {
        position: absolute;
        right: 10rpx;
        bottom: 10rpx;
        min-width: 40rpx;
        height: 36rpx;
        line-height: 36rpx;
        padding: 0 10rpx;
        box-sizing: border-box;
        border-radius: 18rpx;
        background: rgba(255, 255, 255, 0.92);
        color: #1D1D1F;
        font-size: 22rpx;
        font-weight: 700;
        text-align: center;
    }

    .si-top {
        position: absolute;
        left: 10rpx;
        bottom: 10rpx;
        height: 36rpx;
        line-height: 36rpx;
        padding: 0 14rpx;
        border-radius: 18rpx;
        background: #1A73E8;
        color: #FFFFFF;
        font-size: 22rpx;
        font-weight: 700;
    }

    .si-add-tile {
        background: #F7FAFF;
        border: 3rpx dashed #8AB0EE;
        box-sizing: border-box;
        color: #1A73E8;
        font-size: 24rpx;
        font-weight: 500;
    }

    .si-add-tile .si-plus.big {
        position: absolute;
        left: 0;
        right: 0;
        top: 22%;
        text-align: center;
    }

    .si-add-tile > text:last-child {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 18%;
        text-align: center;
    }

    .si-more {
        height: 80rpx;
        line-height: 80rpx;
        margin-top: 8rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        text-align: center;
        color: #1A73E8;
        font-size: 28rpx;
        font-weight: 500;
    }

    .si-sub {
        margin-top: 16rpx;
        font-size: 24rpx;
        line-height: 1.6;
        color: #6E6E73;
    }

    .si-save {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
    }
</style>
