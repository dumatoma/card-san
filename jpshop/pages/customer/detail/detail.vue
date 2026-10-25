<template>
    <view>
        <!-- 自定义顶部导航 -->
        <u-navbar title="メンバー情報" :placeholder="true" :safeAreaInsetTop="true" :border="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" mode="" @click="bac">
                </image>
            </view>
            <view class="u-nav-slot" slot="right">
                <text @click="toCsv" class="tc">編集</text>
            </view>
        </u-navbar>
        <!-- [カスタムエイド 2026-10-20] 見た目をこれまでの画面に合わせた(白い角丸のまとまり・項目名は灰色)。
             来店回数は「来店履歴」の行に。いちばん下の「画像」は、新しい機能「カルテ」に変わる予定と分かるようにした -->
        <view class="md">
            <view class="md-head">
                <u-avatar mode="aspectFill" size="84" :src="userInfo.avatar"></u-avatar>
                <view class="md-name">{{userInfo.name || '-'}}</view>
                <view class="md-kana" v-if="userInfo.pronunciation">{{userInfo.pronunciation}}</view>
            </view>

            <view class="md-card">
                <view class="md-link" hover-class="md-hover" @click="toRecord">
                    <text class="md-link-name">来店履歴</text>
                    <text class="md-link-val">{{count}}回</text>
                    <view class="md-chev"></view>
                </view>
                <view class="md-link" hover-class="md-hover" @click="toCard">
                    <text class="md-link-name">メンバーズカード</text>
                    <text class="md-link-val blue">{{userInfo.card_name || '-'}}</text>
                    <view class="md-chev"></view>
                </view>
            </view>

            <view class="md-card">
                <view class="md-item">
                    <view class="md-label">ID</view>
                    <view class="md-val"><text selectable="true">{{userInfo.member_id}}</text></view>
                </view>
                <view class="md-item">
                    <view class="md-label">顧客NO</view>
                    <view class="md-val" :class="{ empty: !userInfo.member_no }">{{userInfo.member_no || '未登録'}}</view>
                </view>
                <view class="md-item">
                    <view class="md-label">電話番号</view>
                    <view class="md-val blue" v-if="userInfo.phone" @click="makePhoneCall(userInfo.phone)">{{userInfo.phone}}</view>
                    <view class="md-val empty" v-else>ー</view>
                </view>
                <view class="md-item">
                    <view class="md-label">性別</view>
                    <view class="md-val" :class="{ empty: !userInfo.sex }">{{userInfo.sex || '未登録'}}</view>
                </view>
                <view class="md-item">
                    <view class="md-label">生年月日</view>
                    <view class="md-val" v-if="userInfo.birthday_year">{{userInfo.birthday_year}}年{{userInfo.birthday_month}}月{{userInfo.birthday_day}}日</view>
                    <view class="md-val empty" v-else>未登録</view>
                </view>
                <view class="md-item">
                    <view class="md-label">住所</view>
                    <view class="md-val" v-if="userInfo.zip_code || userInfo.province || userInfo.address1">
                        <view v-if="userInfo.zip_code">〒{{userInfo.zip_code}}</view>
                        <view>{{userInfo.province}}{{userInfo.address1}}</view>
                        <view v-if="userInfo.address2">{{userInfo.address2}}</view>
                    </view>
                    <view class="md-val empty" v-else>未登録</view>
                </view>
                <view class="md-item">
                    <view class="md-label">備考</view>
                    <view class="md-val" :class="{ empty: !userInfo.remark }" style="white-space: pre-wrap;">{{userInfo.remark || 'なし'}}</view>
                </view>
            </view>

            <!-- [カスタムエイド 2026-10-23] カルテ:カルテ(1ページ目の見本)と最新の写真4枚。押すとカルテの画面へ -->
            <view class="md-h3"><text>カルテ</text></view>
            <view class="md-card md-karte" v-if="kLock">
                <view class="mk-lock"><image class="k-lock" src="/static/karte/lock.svg"></image>カルテを見る権限がありません。{{'\n'}}オーナーに「カルテ」の権限を付けてもらってください。</view>
            </view>
            <view class="md-card md-karte" hover-class="md-hover" v-else-if="kLoaded" @click="toKarte">
                <block v-if="kFirst || kPhotos.length">
                    <view class="mk-row" v-if="kFirst">
                        <view class="mk-thumb">
                            <image v-if="kFirst.cover" :src="kFirst.cover.url" mode="aspectFill"></image>
                            <text class="mk-cnt">{{kFirst.pages}}</text>
                        </view>
                        <view class="mk-tt">
                            <view class="mk-name">{{kFirst.title}}</view>
                            <view class="mk-sub"><text class="mk-tag" :class="{ pic: kFirst.source != 1 }">{{kFirst.source == 1 ? 'PDF' : '写真'}}</text>{{kFirst.pages}}ページ<text v-if="kKarte.length > 1"> ・ ほか{{kKarte.length - 1}}件</text></view>
                            <view class="mk-sub2" v-if="kFirst.job && kFirst.job.status == 'converting'">取り込み中… {{kFirst.job.done}} / {{kFirst.job.total || '?'}}</view>
                            <view class="mk-sub2" v-else-if="kFirst.updated_by">最後に書いた人:{{kFirst.updated_by}}・{{kDate(kFirst.updated_at)}}</view>
                        </view>
                        <view class="md-chev"></view>
                    </view>
                    <view class="mk-ph" :class="{ line: kFirst }" v-if="kPhotos.length">
                        <view class="mk-ph-h"><text>写真</text><text class="mk-ph-n">{{kPhotoCount}}枚 ›</text></view>
                        <view class="mk-grid">
                            <view class="mk-pic" v-for="p in kPhotos" :key="p.id">
                                <image :src="p.page.url" mode="aspectFill"></image>
                                <text class="mk-pen" v-if="p.marked">✎</text>
                            </view>
                        </view>
                    </view>
                </block>
                <view class="mk-row" v-else>
                    <view class="mk-empty-i"><image class="k-ic" src="/static/karte/clipboard.svg"></image></view>
                    <view class="mk-tt">
                        <view class="mk-name blue">＋ カルテを読み込む</view>
                        <view class="mk-sub2">紙の撮影・写真・テンプレートから</view>
                    </view>
                    <view class="md-chev"></view>
                </view>
            </view>
            <!-- [カスタムエイド 2026-10-24] 以前の「画像」欄は不要になったので外した(カルテに一本化) -->
        </view>
        <view v-if="showPreview"
            style="z-index: 1002 !important;position: fixed;top: 0;left: 0;width: 100vw;height: 100vh;">
            <previewImage :images="userInfo.image" :currentPage="current" :userId="userInfo.id" @closeMask="closeM">
            </previewImage>
        </view>
        <permission :show="showpermission" @close='showpermission = false'></permission>
    </view>
</template>
<script>
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
    import {
        getMemberDetail,
        uploadMemberImage,
        getCome,
        getAdminDetail
    } from "../../../api/index.js"
    import previewImage from '@/components/previewImg/previewImg.vue'
    import permission from "@/components/permission/permission.vue"
    import { karteList, shortDate } from '@/api/karte.js' // [カスタムエイド 2026-10-23] カルテ
    export default {
        components: {
            previewImage,
            permission
        },
        data() {
            return {
                showpermission:false,
                count: 0,
                userInfo: {},
                userId: "",
                showPreview: false,
                current: 0,
                query:{},
                // [カスタムエイド 2026-10-23] カルテ
                kLoaded: false,
                kLock: false,
                kKarte: [],
                kPhotos: [],
                kPhotoCount: 0
            }
        },
        created() {
            
        },
        onLoad(options) {
            let that = this
            let query = options
            that.query = query
            if (query.id) {
                that.userId = query
                that.getDetail(query)
                that.getTimes(query.id)
            }
        },
        onShow() {
            // カルテの画面から戻ったときも最新に
            if (this.userInfo && this.userInfo.id) this.loadKarte()
        },
        computed: {
            kFirst() {
                return this.kKarte[0] || null
            }
        },
        methods: {
            // [カスタムエイド 2026-10-23] カルテ(カルテと最新の写真4枚)
            loadKarte() {
                karteList(this.userInfo.id, 4).then((res) => {
                    if (!res) return
                    if (res.code == 403) {
                        this.kLock = true
                    } else if (res.code == 200) {
                        this.kLock = false
                        this.kKarte = res.data.karte || []
                        this.kPhotos = res.data.photos || []
                        this.kPhotoCount = res.data.photo_count || 0
                    }
                    this.kLoaded = true
                })
            },
            kDate(t) {
                return shortDate(t)
            },
            toKarte() {
                uni.navigateTo({
                    url: '/pages/karte/karte?mid=' + this.userInfo.id + '&name=' + encodeURIComponent(this.userInfo.name || '')
                })
            },
            // 获取详情
            getDetail(e) {
                let that = this
                getMemberDetail(e).then((res) => {
                    if (res.code == 200) {
                        that.userInfo = res.data
                        that.loadKarte() // [カスタムエイド 2026-10-23]
                    }
                })
            },
            
            getTimes(id){
               let that = this
               let data = {}
               data.type = "come"
               data.id = id
                getCome(data).then((res) => {
                    if(res.code == 200){
                        that.count = res.data.total
                    }
                })
            },

            toCard() {
                let that = this
                let info = that.userInfo
                if (info.card.card_type == 3) {
                    uni.navigateTo({
                        url: "../cards/intergrate/intergrate?id=" + that.userInfo.id+"&source=0"
                    })
                } else {
                    uni.navigateTo({
                        url: "../cards/seal/seal?id=" + that.userInfo.id+"&source=0"
                    })
                }
            },

            // 关闭预览图片
            closeM(e) {
                this.showPreview = false
                this.userInfo.image = e
            },

            // 选择图片并上传
            uploadImage() {
                let that = this
                pickImage({
                    count: 9,
                    success: (res) => {
                        const tempPaths = res.tempFilePaths
                        for (let i = 0; i < tempPaths.length; i++) {
                            uni.uploadFile({
                                url: that.$baseUrl+'/tool/upload/file',
                                filePath: res.tempFilePaths[i],
                                name:"file",
                                success: (result) => {
                                    if (result.errMsg == "uploadFile:ok") {
                                        let re = JSON.parse(result.data)
                                        if (re.code == 200) {
                                            let data = {}
                                            data.img = re.data.path
                                            uploadMemberImage(data, that.userInfo.id).then((
                                                rs) => {
                                                    if (i == res.tempFilePaths.length - 1 &&
                                                        rs.code == 200) {
                                                        uni.showToast({
                                                            title: rs.message,
                                                            icon:"none"
                                                        })
                                                        that.getDetail(that.userId)
                                                    }
                                                })
                                        }
                                    }
                                }
                            })
                        }
                    }
                });
            },
            bac() {
                uni.navigateBack()
            },

            makePhoneCall(e) {
                uni.makePhoneCall({
                    phoneNumber: e //仅为示例
                });
            },

            toRecord() {
                let that = this
                uni.navigateTo({
                    url: "../record/record?id="+that.query.id
                })
            },

            toCsv() {
                let that = this
                let admin = uni.getStorageSync("admin")
                if(admin.admin_type == 2){
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    getAdminDetail(admin.id).then((res) => {
                        uni.hideLoading()
                        if(res.data.admin.privileges.includes(4)){
                            uni.navigateTo({
                                url: "../edit/edit?id=" + that.userInfo.member_id + "&NO=" + that.userInfo.member_no +
                                    "&remark=" + that.userInfo.remark + "&userId=" + that.userInfo.id
                            })
                        }else{
                            that.showpermission = true
                        }
                    })
                }else{
                    uni.navigateTo({
                        url: "../edit/edit?id=" + that.userInfo.member_id + "&NO=" + that.userInfo.member_no +
                            "&remark=" + that.userInfo.remark + "&userId=" + that.userInfo.id
                    })
                }
               
            },

            previewImg(e) {
                let that = this
                that.showPreview = true
                that.current = e
            }
        }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-20] これまでの画面と同じ見た目 */
    .tc {
        font-size: 32rpx;
        font-weight: 700;
        color: #1A73E8;
    }

    .md {
        min-height: 100vh;
        background: #F5F5F7;
        padding: 0 24rpx calc(80rpx + env(safe-area-inset-bottom));
        box-sizing: border-box;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .md-head {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 40rpx 0 32rpx;
    }

    .md-name {
        margin-top: 20rpx;
        font-size: 36rpx;
        font-weight: 700;
    }

    .md-kana {
        margin-top: 4rpx;
        font-size: 24rpx;
        color: #8A8A8E;
    }

    .md-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 0 32rpx;
        margin-bottom: 32rpx;
    }

    .md-hover {
        opacity: 0.6;
    }

    .md-link {
        display: flex;
        align-items: center;
        min-height: 104rpx;
        border-bottom: 1px solid #E5E5EA;
    }

    .md-link:last-child {
        border-bottom: 0;
    }

    .md-link-name {
        flex-shrink: 0;
        font-size: 30rpx;
        font-weight: 500;
    }

    .md-link-val {
        flex: 1;
        min-width: 0;
        margin: 0 16rpx 0 24rpx;
        text-align: right;
        font-size: 28rpx;
        color: #515154;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .blue {
        color: #1A73E8;
    }

    /* 右向きの矢印 */
    .md-chev {
        width: 14rpx;
        height: 14rpx;
        flex-shrink: 0;
        border-right: 3rpx solid #C7C7CC;
        border-bottom: 3rpx solid #C7C7CC;
        transform: rotate(-45deg);
    }

    .md-item {
        display: flex;
        align-items: flex-start;
        padding: 26rpx 0;
        border-bottom: 1px solid #E5E5EA;
    }

    .md-item:last-child {
        border-bottom: 0;
    }

    .md-label {
        width: 160rpx;
        flex-shrink: 0;
        font-size: 26rpx;
        line-height: 1.6;
        color: #6E6E73;
    }

    .md-val {
        flex: 1;
        min-width: 0;
        font-size: 30rpx;
        line-height: 1.6;
        word-break: break-all;
    }

    .md-val.empty {
        color: #AEAEB2;
    }

    .md-h3 {
        display: flex;
        align-items: center;
        margin: 8rpx 8rpx 16rpx;
        font-size: 30rpx;
        font-weight: 700;
        color: #515154;
    }

    .md-soon {
        margin-left: 16rpx;
        padding: 2rpx 14rpx;
        border-radius: 8rpx;
        background: #FEF3E0;
        color: #B06000;
        font-size: 22rpx;
        font-weight: 700;
    }

    /* [カスタムエイド 2026-10-23] カルテの欄 */
    .md-karte {
        padding: 28rpx 32rpx;
    }

    .mk-lock {
        text-align: center;
        font-size: 26rpx;
        line-height: 1.7;
        color: #6E6E73;
        white-space: pre-wrap;
        padding: 12rpx 0;
    }

    .mk-row {
        display: flex;
        align-items: center;
    }

    .mk-thumb {
        position: relative;
        width: 104rpx;
        height: 140rpx;
        flex-shrink: 0;
        border-radius: 8rpx;
        overflow: hidden;
        background: #FBFAF6;
        border: 1px solid #E5E5EA;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .mk-cnt {
        position: absolute;
        right: 6rpx;
        bottom: 6rpx;
        padding: 0 10rpx;
        border-radius: 14rpx;
        background: rgba(0, 0, 0, 0.55);
        color: #FFFFFF;
        font-size: 20rpx;
        line-height: 32rpx;
    }

    .mk-tt {
        flex: 1;
        min-width: 0;
        margin: 0 16rpx 0 24rpx;
    }

    .mk-name {
        font-size: 30rpx;
        font-weight: 700;
    }

    .mk-sub {
        margin-top: 8rpx;
        font-size: 24rpx;
        color: #6E6E73;
    }

    .mk-tag {
        display: inline-block;
        margin-right: 10rpx;
        padding: 0 12rpx;
        border-radius: 8rpx;
        background: #FCE8E6;
        color: #C5221F;
        font-size: 20rpx;
        line-height: 32rpx;
    }

    .mk-tag.pic {
        background: #E8F0FE;
        color: #1A73E8;
    }

    .mk-sub2 {
        margin-top: 6rpx;
        font-size: 22rpx;
        color: #8E8E93;
    }

    .mk-ph.line {
        margin-top: 24rpx;
        padding-top: 24rpx;
        border-top: 1px solid #E5E5EA;
    }

    .mk-ph-h {
        display: flex;
        font-size: 26rpx;
        font-weight: 700;
        color: #3C3C43;
        margin-bottom: 12rpx;
    }

    .mk-ph-n {
        margin-left: auto;
        font-weight: 400;
        color: #6E6E73;
        font-size: 24rpx;
    }

    .mk-grid {
        display: flex;
    }

    .mk-pic {
        position: relative;
        width: calc((100% - 30rpx) / 4);
        height: 0;
        padding-bottom: calc((100% - 30rpx) / 4);
        margin-right: 10rpx;
        border-radius: 12rpx;
        overflow: hidden;
        background: #E5E5EA;

        image {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
        }
    }

    .mk-pic:last-child {
        margin-right: 0;
    }

    .mk-pen {
        position: absolute;
        right: 6rpx;
        bottom: 6rpx;
        padding: 0 8rpx;
        border-radius: 10rpx;
        background: rgba(0, 0, 0, 0.6);
        color: #FFFFFF;
        font-size: 18rpx;
    }

    .mk-empty-i {
        width: 96rpx;
        height: 96rpx;
        flex-shrink: 0;
        border-radius: 24rpx;
        background: #EEF4FF;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 44rpx;
    }

    /* [カスタムエイド 2026-10-23] 単色のアイコン */
    .k-ic { width: 48rpx; height: 48rpx; }
    .k-lock { width: 30rpx; height: 30rpx; vertical-align: -4rpx; margin-right: 8rpx; }
</style>
