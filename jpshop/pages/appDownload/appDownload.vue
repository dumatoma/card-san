<template>
    <!-- [カスタムエイド 2026-10-26] 店舗QR統一 段階6:「Card-San App(ダウンロード)」と「メンバーズカード」の2つのQRを、お店のQRコード1つにまとめた。
         ・ダウンロード用の QR は外部サービス(me-qr)の画像だったのをやめた
         ・お店の QR(card-san.jp/s/店舗ID)は、カメラで読むと:アプリがない人→ダウンロード→そのお店のカード/アプリがある人→そのお店が開く
         ・QR の画像はサーバーの店舗QR(shop_info.qrcode)。中身を card-san.jp/s/店舗ID に作り直すのはサーバー側の段階8
         ・控え:_backup/qr6_20261026/appDownload.vue -->
    <view class="page">
        <u-navbar title="お店のQRコード" :placeholder="true" :safeAreaInsetTop="true" :autoBack="true" :border="true"></u-navbar>
        <view class="card-box">
            <view class="membership" v-if="card">
                <image :src="card" mode="aspectFill"></image>
            </view>
            <view class="memName" v-text="name"></view>
            <view class="qrcode">
                <image v-if="qrcode" :src="qrcode" mode="aspectFit" @longpress="saveQr"></image>
            </view>
            <view class="lead">このQRコード1つで、お客様は<text class="em">アプリのダウンロード</text>と<text class="em">会員カードの受け取り</text>ができます。</view>
            <view class="sub">iPhone・Android のカメラで読み取れます。アプリをお持ちのお客様は、アプリの「QRコードを読み取る」でも読み取れます。</view>
        </view>
        <view class="link-box" v-if="shopUrl">
            <view class="link-label">お店のページのリンク(SNS・LINE に貼れます)</view>
            <view class="link-row">
                <view class="link-url" v-text="shopUrl"></view>
                <view class="link-btn cs-tap" @click="copyUrl">コピー</view>
            </view>
        </view>
        <view class="save cs-tap" v-if="qrcode" @click="saveQr">QRコードの画像を保存</view>
    </view>
</template>
<script>
    import { getShopInfo } from "@/api/index.js"
    export default {
        data() {
            return {
                qrcode: "",
                name: "",
                card: "",
                sid: 0
            }
        },
        computed: {
            shopUrl() {
                return this.sid ? 'https://card-san.jp/s/' + this.sid : ''
            }
        },
        onLoad() {
            const admin = uni.getStorageSync("admin") || {}
            this.sid = Number(admin.sid) || 0
            getShopInfo().then((res) => {
                if (res.code == 200) {
                    const s = res.data.shop_info || {}
                    this.qrcode = s.qrcode
                    this.name = s.shop_name
                    this.card = s.default_card && s.default_card.image
                    if (s.id) this.sid = Number(s.id)
                }
            }).catch(() => {})
        },
        methods: {
            copyUrl() {
                uni.setClipboardData({
                    data: this.shopUrl,
                    success: () => {
                        uni.showToast({ title: 'リンクをコピーしました', icon: 'none' })
                    }
                })
            },
            // QR の画像を写真に保存(印刷・SNS 用)
            saveQr() {
                if (!this.qrcode) return
                uni.showLoading({ title: '保存中', mask: true })
                uni.downloadFile({
                    url: this.qrcode,
                    success: (r) => {
                        uni.saveImageToPhotosAlbum({
                            filePath: r.tempFilePath,
                            success: () => { uni.hideLoading(); uni.showToast({ title: '写真に保存しました', icon: 'none' }) },
                            fail: () => { uni.hideLoading(); uni.showToast({ title: '保存できませんでした(写真へのアクセスを許可してください)', icon: 'none' }) }
                        })
                    },
                    fail: () => { uni.hideLoading(); uni.showToast({ title: '保存できませんでした', icon: 'none' }) }
                })
            }
        }
    }
</script>
<style lang="scss">
    .page { min-height: 100vh; background: #F5F5F7; padding-bottom: 60upx; }
    .card-box { margin: 32upx 30upx 0; background: #fff; border-radius: 24upx; padding: 48upx 40upx 40upx; display: flex; flex-direction: column; align-items: center; }
    .membership { width: 346upx; height: 210upx; margin-bottom: 18upx; border-radius: 16upx; overflow: hidden;
        image { width: 100%; height: 100%; }
    }
    .memName { font-size: 36upx; font-family: Hiragino Sans-W5, Hiragino Sans; color: #1D1D1F; text-align: center; }
    .qrcode { width: 448upx; height: 448upx; box-sizing: border-box; padding: 32upx; margin: 16upx auto 8upx;
        image { width: 100%; height: 100%; }
    }
    .lead { font-size: 28upx; color: #1D1D1F; line-height: 46upx; text-align: center; }
    .em { color: #1A73E8; font-weight: bold; }
    .sub { margin-top: 16upx; font-size: 24upx; color: #86868B; line-height: 38upx; text-align: center; }
    .link-box { margin: 24upx 30upx 0; background: #fff; border-radius: 24upx; padding: 28upx 32upx; }
    .link-label { font-size: 24upx; color: #86868B; margin-bottom: 14upx; }
    .link-row { display: flex; align-items: center; }
    .link-url { flex: 1; font-size: 28upx; color: #1D1D1F; word-break: break-all; }
    .link-btn { margin-left: 20upx; padding: 0 28upx; height: 60upx; line-height: 60upx; border-radius: 30upx; background: #1A73E8; color: #fff; font-size: 26upx; position: relative; overflow: hidden; }
    .save { margin: 32upx 30upx 0; height: 92upx; line-height: 92upx; text-align: center; border-radius: 46upx; border: 2upx solid #1A73E8; color: #1A73E8; font-size: 30upx; background: #fff; position: relative; overflow: hidden; }
    .cs-tap:active::after { content: ''; position: absolute; left: 0; top: 0; right: 0; bottom: 0; background: rgba(29, 29, 31, 0.05); }
</style>
