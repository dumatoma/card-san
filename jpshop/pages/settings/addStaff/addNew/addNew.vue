<template>
    <!-- [カスタムエイド 2026-10-20] スタッフ登録・編集の見た目を店舗情報などと合わせた
         ・写真は店舗情報と同じ(3列・押すと拡大・長押しで並び替え・×で削除・1枚目がTOP)。4枚までの制限をなくした
         ・入力欄を共通の枠に、間隔を詰めて白いまとまりに。「店舗情報ページに掲載する」をスイッチに。保存ボタンを共通の形に
         ・編集で開いたとき「店舗情報ページに掲載する」が正しく出ていなかった(書き間違い)のを直した -->
    <page-meta :page-style="dragIndex > -1 ? 'overflow: hidden;' : ''"></page-meta>
    <view class="mf">
        <u-navbar :placeholder='true' :title="query && query.type == 'edit' ? 'スタッフ編集' : 'スタッフ登録'" :border="true" :safeAreaInsetTop="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="left">
                <image src="../../../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" mode="" @click="goBack"></image>
            </view>
        </u-navbar>
        <view class="mf-body">
            <view class="mf-card">
                <view class="mf-img-head">
                    <view class="mf-img-title">
                        <text class="mf-h2">写真</text>
                        <text class="mf-count">{{array.length}}枚</text>
                    </view>
                    <view class="mf-add-btn" hover-class="mf-hover" @click="uploadImage">
                        <text class="mf-plus">+</text>追加
                    </view>
                </view>
                <text class="mf-sub mf-mb">1枚目が一覧とスタッフ紹介に出ます。長押しすると、つかんで順番を入れ替えられます。</text>
                <view class="mf-grid" id="mfGrid">
                    <view class="mf-tile" v-for="(item, index) in array" :key="item" :class="{ main: index == 0, drag: dragIndex == index }"
                        @click="previewImg(index)" @longpress="startDrag(index)" @touchmove="moveDrag" @touchend="endDrag" @touchcancel="endDrag">
                        <image class="mf-tile-img" :src="item" mode="aspectFill"></image>
                        <view class="mf-x-btn" @click.stop="del(index)"><view class="mf-x-in"><view class="mf-x"></view></view></view>
                        <text class="mf-no">{{index + 1}}</text>
                    </view>
                    <view class="mf-tile mf-add-tile" hover-class="mf-hover" @click="uploadImage">
                        <view class="mf-add-in"><text class="mf-plus big">+</text><text>追加</text></view>
                    </view>
                </view>
            </view>

            <view class="mf-h3">プロフィール</view>
            <view class="mf-card">
                <view class="mf-field">
                    <view class="mf-label"><text class="mf-req">※</text>氏名</view>
                    <input class="mf-in" :class="{ on: fk == 'name' }" v-model="name" maxlength="-1" :cursor-spacing="120"
                        placeholder-class="mf-ph" placeholder="例:鈴木 孝太郎" @focus="fk = 'name'" @blur="fk = ''" />
                </view>
                <view class="mf-field">
                    <view class="mf-label">役職・ポジション</view>
                    <input class="mf-in" :class="{ on: fk == 'post' }" v-model="position" maxlength="-1" :cursor-spacing="120"
                        placeholder-class="mf-ph" placeholder="例:スタイリスト(歴10年)" @focus="fk = 'post'" @blur="fk = ''" />
                </view>
                <view class="mf-field">
                    <view class="mf-label">コメント・アピール</view>
                    <textarea class="mf-ta" :class="{ on: fk == 'intro' }" v-model="intro" maxlength="-1" :show-confirm-bar="false"
                        :cursor-spacing="120" placeholder-class="mf-ph" placeholder="得意なことや、お客様へのひとことを入力してください"
                        @focus="fk = 'intro'" @blur="fk = ''"></textarea>
                </view>
            </view>

            <view class="mf-card mf-card-list">
                <view class="mf-sw-row">
                    <view class="mf-sw-text">
                        <text class="mf-sw-name" :class="{ dim: !checked }">店舗情報ページに掲載する</text>
                        <text class="mf-sub">オンにすると、お客様アプリの店舗情報のスタッフ紹介に出ます</text>
                    </view>
                    <u-switch :value="checked" activeColor="#34C759" @change="changeSelect"></u-switch>
                </view>
            </view>

            <view class="mf-save" :class="{ off: !name }" hover-class="mf-save-hover" @click="save">保存する</view>
        </view>
    </view>
</template>

<script>
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
    import {
        addAdmin,
        getAdminDetail,
        eidtAdmin
    } from "@/api/index.js"
    export default {
        data() {
            return {
                array: [],
                checked: true,
                name: "",
                position: "",
                intro: "",
                query: {},
                fk: '', // [カスタムエイド 2026-10-20]
                dragIndex: -1,
                gridRect: null
            };
        },
        created() {
            
        },
        onLoad(options) {
            let that = this
            that.query = options
            if (that.query.type == "edit") {
                getAdminDetail(that.query.id).then((res) => {
                    if (res.code == 200) {
                        that.array = res.data.admin.image
                        that.name = res.data.admin.name
                        that.position = res.data.admin.post
                        that.intro = res.data.admin.self_introduction
                        that.checked = res.data.admin.is_show_shop == 1 ? true : false // [カスタムエイド 2026-10-20] 書き間違い(chedked)を直した
                    }
                })
            }
        },
        methods: {
            // [カスタムエイド 2026-10-20] 枚数の制限をなくした(一度に選べるのは9枚まで)。軽くした画像を選んだ順に追加
            uploadImage() {
                let that = this
                pickImage({
                    count: 9,
                    sizeType: ['compressed'],
                    success: (res) => {
                        const paths = res.tempFilePaths || []
                        if (!paths.length) return
                        uni.showLoading({ title: 'アップロード中', mask: true })
                        Promise.all(paths.map((p) => new Promise((resolve) => {
                            uni.uploadFile({
                                url: that.$baseUrl + '/tool/upload/file',
                                filePath: p,
                                name: "file",
                                success: (result) => {
                                    try {
                                        const re = JSON.parse(result.data)
                                        resolve(re.code == 200 ? re.data.path : '')
                                    } catch (e) {
                                        resolve('')
                                    }
                                },
                                fail: () => resolve('')
                            })
                        }))).then((list) => {
                            uni.hideLoading()
                            if (!Array.isArray(that.array)) that.array = []
                            let failed = 0
                            list.forEach((p) => {
                                if (p) that.array.push(p)
                                else failed++
                            })
                            if (failed) uni.showToast({ title: failed + '枚をアップロードできませんでした', icon: 'none' })
                        })
                    }
                })
            },
            previewImg(index) {
                if (this.dragIndex > -1) return
                uni.previewImage({ urls: this.array, current: index })
            },
            startDrag(index) {
                this.dragIndex = index
                this.gridRect = null
                uni.vibrateShort({})
                uni.createSelectorQuery().in(this).select('#mfGrid').boundingClientRect((r) => {
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
                const col = Math.min(2, Math.max(0, Math.floor((t.clientX - r.left) / (w + gap))))
                const row = Math.max(0, Math.floor((t.clientY - r.top) / (h + gap)))
                const to = Math.min(this.array.length - 1, row * 3 + col)
                const from = this.dragIndex
                if (to == from) return
                const item = this.array.splice(from, 1)[0]
                this.array.splice(to, 0, item)
                this.dragIndex = to
            },
            endDrag() {
                if (this.dragIndex < 0) return
                setTimeout(() => {
                    this.dragIndex = -1
                }, 300)
            },
            del(index) {
                this.array.splice(index, 1)
            },

            changeSelect() {
                this.checked = !this.checked
            },
            goBack(){
              uni.navigateBack()  
            },
            save() {
                let that = this
                if (!String(that.name || '').trim()) {
                    uni.showToast({ title: '氏名を入力してください', icon: 'none' })
                    return
                }
                let data = {}
                data.name = that.name
                data.post = that.position
                data.self_introduction = that.intro
                data.is_show_shop = that.checked == 1 ? 1 : 0
                data.image = that.array
                if (that.query.type == "edit") {
                    data._method = "put"
                    eidtAdmin(data, that.query.id).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            setTimeout(function() {
                                uni.redirectTo({
                                    url: "../addStaff"
                                })
                            }, 2000)
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                } else {
                    addAdmin(data).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            setTimeout(function() {
                                uni.redirectTo({
                                    url: "../addStaff"
                                })
                            }, 2000)
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-20] メニュー登録・編集の新しい見た目(店舗情報などと同じ決まり) */
    .mf {
        min-height: 100vh;
        background: #F5F5F7;
        font-family: 'Hiragino Sans', sans-serif;
        color: #1D1D1F;
    }

    .mf-body {
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
    }

    .mf-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 32rpx;
        margin-bottom: 40rpx;
    }

    .mf-card-list {
        padding: 8rpx 32rpx;
    }

    .mf-h3 {
        margin: 0 8rpx 16rpx;
        font-size: 30rpx;
        font-weight: 700;
        color: #515154;
    }

    .mf-field {
        margin-bottom: 28rpx;
    }

    .mf-field:last-child {
        margin-bottom: 0;
    }

    .mf-label {
        font-size: 26rpx;
        font-weight: 500;
        color: #1D1D1F;
        margin-bottom: 12rpx;
    }

    .mf-req {
        color: #C5221F;
    }

    .mf-sub {
        display: block;
        font-size: 24rpx;
        line-height: 1.6;
        color: #6E6E73;
    }

    .mf-mb {
        margin-bottom: 24rpx;
    }

    .mf-mt {
        margin-top: 12rpx;
    }

    .mf-in {
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

    .mf-in.on,
    .mf-ta.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    .mf-ph {
        color: #8A8A8E;
    }

    .mf-select {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .mf-ta {
        width: 100%;
        box-sizing: border-box;
        height: 320rpx;
        padding: 20rpx 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        background: #FFFFFF;
        font-size: 30rpx;
        line-height: 1.6;
        color: #1D1D1F;
    }

    .mf-hover {
        opacity: 0.6;
    }

    .mf-img-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16rpx;
    }

    .mf-img-title {
        display: flex;
        align-items: baseline;
    }

    .mf-h2 {
        font-size: 32rpx;
        font-weight: 700;
        margin-right: 16rpx;
    }

    .mf-count {
        font-size: 26rpx;
        color: #515154;
    }

    .mf-add-btn {
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

    .mf-plus {
        font-size: 36rpx;
        line-height: 1;
        margin-right: 6rpx;
        position: relative;
        top: -2rpx;
    }

    .mf-plus.big {
        font-size: 48rpx;
        margin-right: 0;
        top: 0;
    }

    .mf-grid {
        display: flex;
        flex-wrap: wrap;
    }

    .mf-tile {
        position: relative;
        width: calc((100% - 32rpx) / 3);
        height: 0;
        padding-bottom: calc((100% - 32rpx) / 3 * 2 / 3);
        margin: 0 16rpx 16rpx 0;
        border-radius: 20rpx;
        overflow: hidden;
        background: #E5E5EA;
        transition: transform .12s;
    }

    .mf-tile:nth-child(3n) {
        margin-right: 0;
    }

    .mf-tile.main {
        box-shadow: 0 0 0 4rpx #1A73E8;
    }

    .mf-tile.drag {
        transform: scale(1.06);
        opacity: 0.9;
        z-index: 5;
        box-shadow: 0 12rpx 32rpx rgba(0, 0, 0, 0.25);
    }

    .mf-tile-img {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
    }

    .mf-add-tile {
        background: #F7FAFF;
        border: 3rpx dashed #8AB0EE;
        box-sizing: border-box;
        color: #1A73E8;
        font-size: 24rpx;
        font-weight: 500;
    }

    .mf-add-in {
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .mf-x-btn {
        position: absolute;
        top: 0;
        right: 0;
        width: 80rpx;
        height: 80rpx;
        display: flex;
        justify-content: flex-end;
        z-index: 2;
    }

    .mf-x-in {
        margin: 10rpx;
        width: 48rpx;
        height: 48rpx;
        border-radius: 24rpx;
        background: rgba(29, 29, 31, 0.72);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mf-x {
        position: relative;
        width: 22rpx;
        height: 22rpx;
    }

    .mf-x::before,
    .mf-x::after {
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

    .mf-x::after {
        transform: rotate(-45deg);
    }

    .mf-no {
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
        font-size: 22rpx;
        font-weight: 700;
        text-align: center;
    }

    .mf-sw-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 128rpx;
    }

    .mf-sw-text {
        flex: 1;
        min-width: 0;
        margin-right: 20rpx;
    }

    .mf-sw-name {
        display: block;
        font-size: 30rpx;
        font-weight: 700;
        margin-bottom: 4rpx;
    }

    .mf-sw-body {
        border-top: 1px solid #E5E5EA;
        padding: 28rpx 0 32rpx;
    }

    .mf-save {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
    }

    .mf-save-hover {
        opacity: 0.8;
    }

    .mf-sw-name.dim {
        color: #8A8A8E;
    }

    .mf-save.off {
        background: #C6DAFC;
    }

    .mf-ta {
        height: 260rpx;
    }
</style>
