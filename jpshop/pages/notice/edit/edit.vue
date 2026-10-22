<template>
    <!-- [カスタムエイド 2026-10-22] お知らせ作成・編集を作り直し(メッセージ作成・クーポン作成と同じカード型・余白の決まり)
         ・右上は「プレビュー」(目のアイコンをやめた)
         ・写真は9枚まで、選んだときに自動で小さくする(長い辺 1280px まで)
         ・ボタンはお知らせの色(青)。保存の形・API は今までと同じ -->
    <view class="nt">
        <u-navbar :title="titles" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD">
            <view slot="right" class="nt-pv" @click="show3 = true">プレビュー</view>
        </u-navbar>
        <view class="nt-pg">
            <view class="nt-card">
                <view class="nt-h">画像 <text class="nt-sub">(9枚まで・お客様は左右にめくって見られます)</text></view>
                <view class="nt-grid">
                    <view class="nt-im" v-for="(u, i) in banner" :key="u + i" @click="previewImg(i)">
                        <image class="nt-im-img" :src="u" mode="aspectFill"></image>
                        <view class="nt-x" @click.stop="del(i)">×</view>
                    </view>
                    <view class="nt-add" v-if="banner.length < 9" hover-class="nt-hover" @click="chooseImage">
                        <text class="nt-add-p">＋</text><text class="nt-add-t">追加</text>
                    </view>
                </view>
                <view class="nt-note" v-if="uploading">写真を準備しています…</view>
            </view>

            <view class="nt-card">
                <view class="nt-h"><text class="nt-req">※</text>タイトル</view>
                <input class="nt-in" :class="{ on: fk == 't' }" v-model="title" placeholder="例:年末年始の営業のお知らせ" placeholder-class="nt-ph"
                    @focus="fk = 't'" @blur="fk = ''" />
                <view class="nt-h nt-h2">詳細</view>
                <textarea class="nt-ta" :class="{ on: fk == 'd' }" v-model="details" maxlength="-1" :show-confirm-bar="false"
                    :disable-default-padding="true" placeholder="お知らせの内容を入力" placeholder-class="nt-ph"
                    @focus="fk = 'd'" @blur="fk = ''"></textarea>
            </view>

            <view class="nt-card">
                <view class="nt-sw">
                    <text class="nt-sw-t">いちばん上に固定する</text>
                    <u-switch :value="value" activeColor="#34C759" @change="value = !value"></u-switch>
                </view>
                <view class="nt-note">オンにすると、お客様の「お知らせ」のいちばん上に、このお知らせが固定して表示されます。</view>
            </view>

            <view class="nt-btn" :class="{ off: !(title || '').trim() }" hover-class="nt-hover" @click="sub">{{ btntext }}</view>
        </view>

        <!-- プレビュー:お客様の Card-San App での見え方 -->
        <u-popup :show="show3" mode="bottom" round="24" @close="show3 = false">
            <view class="nt-sheet">
                <view class="nt-grip"></view>
                <view class="nt-shx" hover-class="nt-hover" @click="show3 = false">×</view>
                <view class="nt-sh-t">プレビュー</view>
                <view class="nt-sh-tip">端末やOSによって、実際の見え方と少し異なる場合があります。</view>
                <scroll-view scroll-y class="nt-pvw">
                    <swiper v-if="banner.length" class="nt-pv-sw" :indicator-dots="banner.length > 1">
                        <swiper-item v-for="(u, i) in banner" :key="i"><image class="nt-pv-img" :src="u" mode="aspectFill"></image></swiper-item>
                    </swiper>
                    <view class="nt-pv-b">
                        <view class="nt-pv-who">
                            <image class="nt-pv-av" src="../../../static/images/avatar.png" mode="aspectFill"></image>
                            <view>
                                <view class="nt-pv-name">{{ name }}</view>
                                <view class="nt-pv-date">今日<text v-if="value" class="nt-pv-pin">固定されたお知らせ</text></view>
                            </view>
                        </view>
                        <view class="nt-pv-t">{{ title || 'タイトル' }}</view>
                        <view class="nt-pv-d" v-if="details">{{ details }}</view>
                    </view>
                </scroll-view>
                <view class="nt-close" hover-class="nt-hover" @click="show3 = false">閉じる</view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
    import {
        getShopInfo,
        addNotice,
        editNotice
    } from '@/api/index.js'

    const MAX_SIDE = 1280 // 写真の長い辺の上限(px)

    export default {
        data() {
            return {
                value: false,
                details: "",
                title: "",
                show3: false,
                id: "",
                source: "",
                banner: [],
                name: "",
                titles: "",
                btntext: "",
                fk: '',
                uploading: false,
                sending: false
            }
        },
        onLoad(options) {
            let that = this
            that.source = options.source
            if (options.source == "edit") {
                let data = {}
                try { data = JSON.parse(options.params) } catch (e) { try { data = JSON.parse(decodeURIComponent(options.params)) } catch (e2) {} }
                that.banner = Array.isArray(data.images) ? data.images : []
                that.title = data.title || ''
                that.details = data.content || ''
                that.value = data.is_top == 1
                that.id = data.id
                that.titles = "お知らせ編集"
                that.btntext = 'この内容で保存する'
            } else {
                that.titles = "お知らせ作成"
                that.btntext = '投稿する'
            }
            getShopInfo().then((res) => {
                if (res.code == 200) that.name = res.data.shop_info.shop_name
            }).catch(() => {})
        },
        methods: {
            del(i) {
                this.banner.splice(i, 1)
            },
            previewImg(i) {
                uni.previewImage({ urls: this.banner, current: i })
            },
            sub() {
                if (this.sending) return
                if (this.uploading) {
                    uni.showToast({ title: '写真を準備しています。少しお待ちください', icon: 'none' })
                    return
                }
                if (!(this.title || '').trim()) {
                    uni.showToast({ title: 'タイトルを入力してください', icon: 'none' })
                    return
                }
                const data = {
                    images: this.banner,
                    title: this.title.trim(),
                    content: this.details,
                    is_top: this.value ? 1 : 0
                }
                const done = (res) => {
                    this.sending = false
                    if (res.code == 200) {
                        uni.showToast({ title: res.message })
                        setTimeout(() => uni.navigateBack(), 1500)
                    } else {
                        uni.showToast({ title: res.message, icon: "none" })
                    }
                }
                this.sending = true
                if (this.source == 'edit') {
                    data._method = "put"
                    editNotice(data, this.id).then(done).catch(() => { this.sending = false })
                } else {
                    addNotice(data).then(done).catch(() => { this.sending = false })
                }
            },
            chooseImage() {
                const left = 9 - this.banner.length
                if (left <= 0) return
                pickImage({
                    count: left,
                    sizeType: ['compressed'],
                    success: async (res) => {
                        this.uploading = true
                        for (const p of res.tempFilePaths.slice(0, left)) {
                            try {
                                const small = await this.shrink(p)
                                const url = await this.upload(small)
                                if (url && this.banner.length < 9) this.banner.push(url)
                            } catch (e) {
                                uni.showToast({ title: '写真を送れませんでした', icon: 'none' })
                            }
                        }
                        this.uploading = false
                    }
                })
            },
            // 長い辺が 1280px を超える写真は縮め、画質 80 で保存し直す。小さくできないとき(ブラウザなど)はそのまま使う
            shrink(path) {
                return new Promise((resolve) => {
                    uni.getImageInfo({
                        src: path,
                        success: (info) => {
                            const opt = { src: path, quality: 80 }
                            if (Math.max(info.width, info.height) > MAX_SIDE) {
                                if (info.width >= info.height) opt.compressedWidth = MAX_SIDE
                                else opt.compressedHeight = MAX_SIDE
                            }
                            uni.compressImage(Object.assign(opt, {
                                success: (r) => resolve(r.tempFilePath || path),
                                fail: () => resolve(path)
                            }))
                        },
                        fail: () => resolve(path)
                    })
                })
            },
            upload(path) {
                return new Promise((resolve, reject) => {
                    uni.uploadFile({
                        url: this.$baseUrl + '/tool/upload/file',
                        filePath: path,
                        name: "file",
                        success: (result) => {
                            try {
                                const re = JSON.parse(result.data)
                                if (re.code == 200) resolve(re.data.path)
                                else reject(re)
                            } catch (e) {
                                reject(e)
                            }
                        },
                        fail: reject
                    })
                })
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-22] お知らせ作成(余白はほかの画面と同じ決まり) */
    page { background: #F2F2F7; }
    .nt-pv { font-size: 30rpx; color: #1A73E8; padding: 0 8rpx; }
    .nt-pg { padding: 40rpx 32rpx calc(96rpx + env(safe-area-inset-bottom)); }
    .nt-card { background: #FFFFFF; border-radius: 32rpx; padding: 40rpx 32rpx; margin-bottom: 32rpx; }
    .nt-h { font-size: 28rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.2; margin-bottom: 8rpx; }
    .nt-h2 { margin-top: 40rpx; }
    .nt-sub { font-size: 22rpx; color: #6E6E73; font-family: 'Hiragino Sans', sans-serif; }
    .nt-req { color: #D93025; }
    .nt-in { width: 100%; height: 96rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 0 28rpx; font-size: 30rpx; background: #FFFFFF; box-sizing: border-box; }
    .nt-ta { width: 100%; height: 360rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 24rpx 28rpx; font-size: 30rpx; line-height: 1.6; box-sizing: border-box; display: block; }
    .nt-in.on, .nt-ta.on { border-color: #1A73E8; box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.18); }
    .nt-ph { color: #AEAEB2; }
    .nt-note { font-size: 24rpx; color: #8A8A8E; line-height: 1.6; margin-top: 16rpx; }
    .nt-grid { display: flex; flex-wrap: wrap; margin: 16rpx -8rpx 0; }
    .nt-im, .nt-add { width: calc(25% - 16rpx); margin: 0 8rpx 16rpx; height: 0; padding-bottom: calc(25% - 16rpx); position: relative; border-radius: 20rpx; overflow: hidden; box-sizing: border-box; }
    .nt-im-img { position: absolute; left: 0; top: 0; width: 100%; height: 100%; }
    .nt-add { border: 1.5px dashed #AECBFA; background: #F5F9FF; }
    .nt-add-p, .nt-add-t { position: absolute; left: 0; right: 0; text-align: center; color: #1A73E8; }
    .nt-add-p { top: 22%; font-size: 40rpx; }
    .nt-add-t { top: 62%; font-size: 20rpx; }
    .nt-x { position: absolute; right: 8rpx; top: 8rpx; width: 40rpx; height: 40rpx; border-radius: 50%; background: rgba(0, 0, 0, 0.55); color: #FFFFFF; font-size: 24rpx; line-height: 40rpx; text-align: center; }
    .nt-sw { display: flex; align-items: center; justify-content: space-between; }
    .nt-sw-t { font-size: 30rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; }
    .nt-btn { margin-top: 64rpx; height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif; display: flex; align-items: center; justify-content: center; }
    .nt-btn.off { opacity: 0.45; }
    .nt-hover { opacity: 0.7; }
    .nt-sheet { position: relative; padding: 24rpx 40rpx calc(48rpx + env(safe-area-inset-bottom)); }
    .nt-grip { width: 80rpx; height: 10rpx; border-radius: 6rpx; background: #D2D2D7; margin: 0 auto 28rpx; }
    .nt-shx { position: absolute; right: 24rpx; top: 20rpx; width: 64rpx; height: 64rpx; border-radius: 50%; background: #F2F2F7; color: #6E6E73; font-size: 36rpx; line-height: 64rpx; text-align: center; z-index: 2; }
    .nt-sh-t { text-align: center; font-size: 34rpx; font-family: 'Hiragino Sans-W5', sans-serif; color: #1D1D1F; }
    .nt-sh-tip { text-align: center; font-size: 22rpx; color: #8A8A8E; margin: 12rpx 0 28rpx; }
    .nt-pvw { max-height: 60vh; background: #F2F2F7; border-radius: 28rpx; overflow: hidden; }
    .nt-pv-sw, .nt-pv-img { width: 100%; height: 400rpx; }
    .nt-pv-b { background: #FFFFFF; padding: 32rpx; }
    .nt-pv-who { display: flex; align-items: center; margin-bottom: 20rpx; }
    .nt-pv-av { width: 72rpx; height: 72rpx; border-radius: 50%; margin-right: 20rpx; }
    .nt-pv-name { font-size: 28rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; }
    .nt-pv-date { font-size: 22rpx; color: #8A8A8E; margin-top: 4rpx; }
    .nt-pv-pin { color: #1A73E8; margin-left: 12rpx; }
    .nt-pv-t { font-size: 32rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.4; }
    .nt-pv-d { font-size: 28rpx; color: #1D1D1F; line-height: 1.7; white-space: pre-wrap; margin-top: 16rpx; }
    .nt-close { margin-top: 32rpx; height: 104rpx; border-radius: 28rpx; background: #F2F2F7; color: #1D1D1F; font-size: 32rpx; display: flex; align-items: center; justify-content: center; }
</style>
