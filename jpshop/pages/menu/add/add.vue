<template>
    <!-- [カスタムエイド 2026-10-20] メニューの登録・編集の見た目を店舗情報などと合わせた
         ・写真:店舗情報のTOP画像と同じ(3列・押すと拡大・長押しで並び替え・×で削除・4枚まで・選んだ順に追加)
         ・入力欄を共通の枠(#D2D2D7・角丸・入力中は青)に、間隔を詰めて白いまとまりに。メニュー説明の欄を大きく
         ・「予約メニューに設定」をスイッチにし、オンにするとお客様アプリで予約できることを説明。保存ボタンも共通の形に -->
    <page-meta :page-style="dragIndex > -1 ? 'overflow: hidden;' : ''"></page-meta>
    <view class="mf">
        <u-navbar title="メニュー登録" :placeholder="true" :border="true" :autoBack="true" :safeAreaInsetTop="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="right">
                <image src="../../../static/svg/pre.svg" style="width: 50upx;height: 50upx;" @click="showPop = true" mode=""></image>
            </view>
        </u-navbar>
        <view class="mf-body">
            <!-- 写真 -->
            <view class="mf-card">
                <view class="mf-img-head">
                    <view class="mf-img-title">
                        <text class="mf-h2">メニューの写真</text>
                        <text class="mf-count">{{array.length}} / 4枚</text>
                    </view>
                    <view class="mf-add-btn" v-if="array.length < 4" hover-class="mf-hover" @click="uploadImage">
                        <text class="mf-plus">+</text>追加
                    </view>
                </view>
                <text class="mf-sub mf-mb">1枚目がメニュー一覧に出ます。長押しすると、つかんで順番を入れ替えられます。</text>
                <view class="mf-grid" id="mfGrid">
                    <view class="mf-tile" v-for="(item, index) in array" :key="item" :class="{ main: index == 0, drag: dragIndex == index }"
                        @click="previewImg(index)" @longpress="startDrag(index)" @touchmove="moveDrag" @touchend="endDrag" @touchcancel="endDrag">
                        <image class="mf-tile-img" :src="item" mode="aspectFill"></image>
                        <view class="mf-x-btn" @click.stop="delImage(index)"><view class="mf-x-in"><view class="mf-x"></view></view></view>
                        <text class="mf-no">{{index + 1}}</text>
                    </view>
                    <view class="mf-tile mf-add-tile" v-if="array.length < 4" hover-class="mf-hover" @click="uploadImage">
                        <view class="mf-add-in"><text class="mf-plus big">+</text><text>追加</text></view>
                    </view>
                </view>
            </view>

            <!-- メニューの内容 -->
            <view class="mf-h3">メニューの内容</view>
            <view class="mf-card">
                <view class="mf-field">
                    <view class="mf-label">カテゴリー</view>
                    <view class="mf-in mf-select" hover-class="mf-hover" @click="p1">
                        <text :class="{ 'mf-ph': !cateName }">{{cateName || 'カテゴリーを選ぶ'}}</text>
                        <u-icon name="arrow-down" color="#6E6E73" size="14"></u-icon>
                    </view>
                </view>
                <view class="mf-field">
                    <view class="mf-label"><text class="mf-req">※</text>メニュータイトル</view>
                    <input class="mf-in" :class="{ on: fk == 'title' }" v-model="title" maxlength="-1" :cursor-spacing="120"
                        placeholder-class="mf-ph" placeholder="例:カット+フルカラー" @focus="fk = 'title'" @blur="fk = ''" />
                </view>
                <view class="mf-field">
                    <view class="mf-label">メニュー説明</view>
                    <textarea class="mf-ta" :class="{ on: fk == 'des' }" v-model="details" maxlength="-1" :show-confirm-bar="false"
                        :cursor-spacing="120" placeholder-class="mf-ph" placeholder="メニューの内容や、おすすめの点を入力してください"
                        @focus="fk = 'des'" @blur="fk = ''"></textarea>
                </view>
                <view class="mf-field">
                    <view class="mf-label">価格</view>
                    <input class="mf-in" :class="{ on: fk == 'price' }" v-model="price" maxlength="-1" :cursor-spacing="120"
                        placeholder-class="mf-ph" placeholder="例:5,000円、5,000円〜" @focus="fk = 'price'" @blur="fk = ''; price = csPrice(price)" />
                </view>
            </view>

            <!-- 予約 -->
            <view class="mf-h3">予約</view>
            <view class="mf-card mf-card-list">
                <view class="mf-sw-row">
                    <view class="mf-sw-text">
                        <text class="mf-sw-name">予約メニューに設定</text>
                        <text class="mf-sub">オンにすると、お客様アプリでこのメニューを選んで予約できます</text>
                    </view>
                    <u-switch :value="appoint" activeColor="#34C759" @change="changeAppoint"></u-switch>
                </view>
                <view class="mf-sw-body" v-if="appoint">
                    <view class="mf-label">所要時間(目安)</view>
                    <view class="mf-in mf-select" hover-class="mf-hover" @click="p2">
                        <text>{{time}}分</text>
                        <u-icon name="arrow-down" color="#6E6E73" size="14"></u-icon>
                    </view>
                    <text class="mf-sub mf-mt">施術にかかる時間を設定してください。</text>
                </view>
            </view>

            <view class="mf-save" hover-class="mf-save-hover" @click="save">保存する</view>
        </view>
        <u-picker :show="show" :columns="columns" @confirm="show = false" @change="cateChange" keyName="name" cancelText=" " confirmText=" "
            :closeOnClickOverlay="true" @close="close1" @cancel="close1" title="カテゴリーを選択"></u-picker>
        <u-picker :show="show1" :columns="columns1" @change="timeChange" keyName="time" cancelText=" " confirmText=" "
            :closeOnClickOverlay="true" @close="close2" @confirm="show1 = false"  @cancel="close2" title="時間を選択"></u-picker>
        <u-popup :show="showPop" mode="bottom" :round="10" @close="closePop" @cancel="closePop">
            <view class="pTitle">
                <view class="pClose">
                    <image src="../../../static/icons/tclose.png" mode="" @click="closePop"></image>
                </view>
                <view class="tDetail">
                    メニュー
                </view>
            </view>
            <view class="pContent">
                <view class="pDetail">
                    <view class="dLeft">
                        <view class="dTop">
                            {{title || 'メニュータイトル・予約なしの場合'}}
                        </view>
                        <view class="dBottom">
                            <view class="dPrice">
                                {{price || '価格'}}<!-- [カスタムエイド 2026-10-21] 価格はお店が書いたまま(以前は先頭に ￥ を付けていたので「￥9,000円」になった) -->
                            </view>
                            <view class="dTime">
                                所要時間：{{time}}分
                            </view>
                        </view>
                    </view>
                    <view class="dRight">
                        <image class="dr" src="../../../static/icons/collection.png" mode=""></image>
                    </view>
                </view>
                <view class="dc">
                    <view class="dcLeft">
                        <view class="imageCotent">
                            <image class="album" src="../../../static/icons/album.png" mode=""></image>
                            <image style="width: 100%;height: 100%;" :src="array[0]" mode="aspectFill"></image>
                        </view>
                    </view>
                    <view class="dcRight" v-text="details"></view>
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
    import {
        menuCateList,addMenu
    } from "@/api/index.js"
    export default {
        data() {
            return {
                show: false,
                show1: false,
                showPop: false,
                cateName:'',
                details:"",
                price:"",
                cid:"",
                time:"30",
                title:"",
                appoint:false,
                columns: [
                ],
                columns1: [
                    [{
                            time: "30分",
                            num:30
                        },
                        {
                            time: "60分",
                            num:60
                        },
                        {
                            time: "90分",
                            num:90
                        },
                        {
                            time: "120分",
                            num:120
                        },
                        {
                            time: "150分",
                            num:150
                        },
                        {
                            time: "180分",
                            num:180
                        },
                        {
                            time: "210分",
                            num:210
                        },
                        {
                            time: "240分",
                            num:240
                        }
                    ]
                ],
                array: [],
                fk: '', // [カスタムエイド 2026-10-20] 入力中の欄
                dragIndex: -1, // 長押しでつかんでいる写真
                gridRect: null,
            };
        },
        onNavigationBarButtonTap() {
            this.showPop = true
        },
        created() {
            let that = this
            menuCateList().then((res) => {
                if (res.code == 200) {
                    that.columns.push(res.data.cates)
                    that.cateName = res.data.cates[0].name
                    that.cid = res.data.cates[0].id
                }
            })
        },
        methods: {
            // [カスタムエイド 2026-10-21] 価格:入力が終わったら、4けた以上の数字にカンマを付ける(9000円〜 → 9,000円〜)。¥・円・〜などはお店が書いたまま。全角の数字は半角に
            csPrice(v) {
                if (!v) return v
                const s = String(v).replace(/[０-９]/g, (c) => String.fromCharCode(c.charCodeAt(0) - 0xFEE0))
                return s.replace(/(^|[^\d.,])(\d{4,})(?![\d.])/g, (m, a, b) => a + b.replace(/\B(?=(\d{3})+(?!\d))/g, ','))
            },
            changeAppoint(){
              this.appoint = !this.appoint  
            },
            timeChange(e){
               let that = this
               that.time = e.value[0].num
            },
            cateChange(e){
                let that = this
                that.cid = e.value[0].id
                that.cateName = e.value[0].name
            },
            delImage(index){
               let that = this
               that.array.splice(index, 1)
            },
            save(){
              let that = this
              let data = {}
              data.cid = that.cid
              data.image = that.array
              data.title = that.title
              data.des = that.details
              data.price = that.csPrice(that.price) // [カスタムエイド 2026-10-21]
              data.is_order = that.appoint == true? 1 : 0
              data.order_time = that.time
              addMenu(data).then((res) => {
                  if(res.code == 200){
                      uni.showToast({
                          title:res.message,
                          duration:2000
                      })
                      setTimeout(function(){
                          let pages = getCurrentPages();
                          let currPage = pages[pages.length - 1] 
                          let prePage = pages[pages.length - 2] 
                          prePage.$vm.getCate()
                          uni.navigateBack({}) 
                      },2000)
                  }else{
                      uni.showToast({
                          title:res.message,
                          icon:"none"
                      })
                  }
              })
            },
            // 选择图片并上传
            // [カスタムエイド 2026-10-20] 軽くした画像を使い、選んだ順に追加。アップロード中はお知らせを出す
            uploadImage() {
                let that = this
                const left = 4 - that.array.length
                if (left <= 0) {
                    uni.showToast({ title: "画像は最大で4枚まで", icon: "none" })
                    return
                }
                pickImage({
                    count: left,
                    sizeType: ['compressed'],
                    success: (res) => {
                        const paths = (res.tempFilePaths || []).slice(0, left)
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
                            let failed = 0
                            list.forEach((p) => {
                                if (p && that.array.length < 4) that.array.push(p)
                                else if (!p) failed++
                            })
                            if (failed) uni.showToast({ title: failed + '枚をアップロードできませんでした', icon: 'none' })
                        })
                    }
                })
            },
            // [カスタムエイド 2026-10-20] 押すと拡大
            previewImg(index) {
                if (this.dragIndex > -1) return
                uni.previewImage({ urls: this.array, current: index })
            },
            // [カスタムエイド 2026-10-20] 長押しでつかんで並び替え(店舗情報のTOP画像と同じ)
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
            p1() {
                this.show = true
            },
            p2() {
                this.show1 = true
            },
            close1() {
                this.show = false
            },
            close2() {
                this.show1 = false
            },
            closePop() {
                this.showPop = false
            }
        }
    }
</script>

<style lang="scss">
    /* 下はプレビュー(右上のマーク)の見た目。今までのまま */
    .uni-page-head{
        z-index: 999 !important;
    }
    .dc {
        display: flex;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 28upx 32upx;
        border-bottom: 2upx solid #d2d2d7;

        .dcRight {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-left: 20upx;
            flex: 1;
            text-align: left;
        }

        .imageCotent {
            width: 246upx;
            height: 246upx;
            position: relative;
            border-radius: 12upx;
            border: 2upx solid #EAEAEA;

            .album {
                width: 64upx;
                height: 64upx;
                border-radius: 12upx;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 99;
            }
        }

    }

    .pDetail {
        display: flex;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 44upx 30upx 0;

        .dRight {
            width: 60upx;
            height: 60upx;
            // background: #D2D2D7; 

            .dr {
                width: 100%;
                height: 100%;
            }
        }

        .dTop {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }
    }

    .dBottom {
        display: flex;
        align-items: center;
        margin-top: 28upx;

        .dPrice {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: rgba(217, 48, 37, 0.65);
            margin-right: 68upx;
        }

        .dTime {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }
    }

    .pTitle {
        height: 104upx;
        background: #F5F5F7;
        border-radius: 32upx;
        display: flex;
        align-items: center;
        box-sizing: border-box;
        padding: 0 32upx;

        .tDetail {
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .pClose {
            width: 60upx;
            height: 60upx;
            margin-right: 202upx;

            image {
                width: 100%;
                height: 100%;
            }
        }
    }


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

    /* [カスタムエイド 2026-10-21] 項目と項目のあいだを約6px広げる */
    .mf-field {
        margin-bottom: 40rpx;
    }

    .mf-field:last-child {
        margin-bottom: 0;
    }

    /* [カスタムエイド 2026-10-21] 見出しの文字と入力枠の見た目のすき間を約6pxに(行の高さを詰め、余白 4px) */
    .mf-label {
        font-size: 26rpx;
        font-weight: 500;
        color: #1D1D1F;
        margin-bottom: 8rpx;
        line-height: 1.2;
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
</style>
