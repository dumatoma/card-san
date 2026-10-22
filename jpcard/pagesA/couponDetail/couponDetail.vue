<template>
    <view>
        <u-navbar title="クーポン詳細" :placeholder="true" :safeAreaInsetTop="true" bgColor="#ffffff" :height="56"
            :border="true" @leftClick="leftClick">
        </u-navbar>
        <view class="c-img" v-if="images.length > 0">
            <!-- [カスタムエイド 2026-10-19] 点(●●●)をやめ、2枚以上のときだけ右下に「1 / 3」。押したら全画面・左右にスワイプ -->
            <view class="cs-swipe">
                <u-swiper :list="images" :autoplay="false" :indicator="false" height="250" @change="imgChange" @click="openImages(images, $event)"></u-swiper>
                <view class="cs-top-count" v-if="images.length > 1">{{imgIndex + 1}} / {{images.length}}</view>
            </view>
        </view>
        <view class="c-tit">
            クーポン番号
            <view class="c-sn" v-text="info.number"></view>
        </view>
        <!-- [カスタムエイド 2026-10-22] お店のバーコード番号(レジで読み取る)のクーポン:使う前はバーコードを出さず、スライド → 確認 → 10分だけ表示 -->
        <view class="ps" v-if="isPos">
            <view class="ps-live" v-if="pos.using">
                <view class="ps-band">ご利用中 ・ スタッフに見せてください</view>
                <view class="ps-clock">{{ clock }}</view>
                <view class="ps-date">{{ today }}</view>
                <view class="ps-bc">
                    <image class="ps-bc-img" :src="pos.image" mode="aspectFit"></image>
                    <view class="ps-bc-n">{{ pos.value }}</view>
                </view>
            </view>
            <view class="ps-timer" v-if="pos.using">あと {{ leftText }} 表示します(そのあと「使用済み」になります)</view>
            <view class="ps-hint" v-if="pos.using">時刻が動いていて、帯の色が流れていれば本物の画面です。</view>

            <view class="ps-used" v-if="!pos.using && pos.used_time">
                <view class="ps-stamp">使用済み</view>
                <view class="ps-used-t">{{ usedLabel }} に使いました</view>
            </view>

            <block v-if="!pos.using && pos.can_use">
                <view class="ps-slide" id="psSlide" @touchstart="sStart" @touchmove.stop.prevent="sMove" @touchend="sEnd" @touchcancel="sEnd">
                    <view class="ps-slide-fill" :style="{ width: (knobX + knobW) + 'px' }"></view>
                    <text class="ps-slide-t">{{ pos.used_time ? 'もう一度使う(スライド)' : 'スライドして使う' }}</text>
                    <view class="ps-knob" :class="{ back: !dragging }" :style="{ transform: 'translateX(' + knobX + 'px)' }">›</view>
                </view>
                <view class="ps-hint">お店のスタッフの前で操作してください。<br />使うとバーコードが出ます(使う前は出ません)。</view>
            </block>
            <view class="ps-hint" v-if="!pos.using && !pos.can_use && !pos.used_time">{{ pos.in_date ? '利用回数の上限に達しています。' : '有効期限の外のため、使えません。' }}</view>
        </view>
        <view @click="openmask" class="ma" v-if="info.bar_code && !isPos">
            <image :src="info.bar_code" style="width: 700rpx;height: 160rpx;" mode=""></image>
            <view class="ma-sn" v-text="info.bar_code_number"></view>
        </view>
        <view class="cou-box">
            <view class="c-title" v-text="info.title"></view>
            <!-- [カスタムエイド 2026-10-19] 空行を少しの間として表示(共通の行間 1.6) -->
            <view class="c-des">
                <view class="cs-para" v-for="(t, i) in textParas(info.des)" :key="i">{{t}}</view>
            </view>
        </view>
        <view class="c-itms">
            <view class="validity-btn">
                有効期限
            </view>
            <view class="validity" v-if="info.in_date" v-text="`${info.in_date}まで有効`"></view>
            <view class="validity" v-else>無期限のクーポンです</view>
        </view>
        <view class="beizhu" v-if="info.count">
            <!-- [カスタムエイド 2026-10-19] 文字の前の全角スペースを消して、枠の中央にそろえる -->
            <view class="bei-btn">利用回数</view>
            <view class="bei">残り{{info.count}}回</view>
        </view>
        <view class="beizhu" v-if="info.remark">
            <view class="bei-btn">備考</view>
            <view class="bei">
                <view class="cs-para" v-for="(t, i) in textParas(info.remark)" :key="i">{{t}}</view>
            </view>
        </view>
        <!-- [カスタムエイド 2026-10-19] 一番下に余白(iPhone の下のバーの分も) -->
        <view class="cs-bottom-space"></view>
        <!-- [カスタムエイド 2026-10-22] 使う前の確認 -->
        <view class="ps-ov" v-if="showConfirm" @touchmove.stop.prevent>
            <view class="ps-dlg">
                <view class="ps-dlg-i">!</view>
                <view class="ps-dlg-t">このクーポンを使いますか?</view>
                <view class="ps-dlg-m">「今すぐ使う」を押すと、使ったことになります(元に戻せません)。{{ '\n' }}お店のスタッフの前で押してください。</view>
                <view class="ps-dlg-ok" :class="{ off: using }" hover-class="ps-hover" @click="useNow">{{ using ? '処理しています…' : '今すぐ使う' }}</view>
                <view class="ps-dlg-no" hover-class="ps-hover" @click="showConfirm = false">後で</view>
            </view>
        </view>
        <view class="mask" @click="showT = false" v-if="showT == true">
            <image class="bimage" :src="info.bar_code" mode=""></image>
        </view>
    </view>
</template>

<script>
    import { textParas } from '@/utils/textParas.js' // [カスタムエイド 2026-10-19] お店が入れた空行を少しの間として表示
    import {
        getCouponDetail,
        selfUseCoupon
    } from "@/api/index.js"
    export default {
        data() {
            return {
                info: {},
                showT: false,
                images: [],
                imnt:true,
                imgIndex: 0, // [カスタムエイド 2026-10-19] 画像の今の位置
                // [カスタムエイド 2026-10-22] お店のバーコード番号
                id: '',
                offset: 0, // サーバーの時刻 - この端末の時刻(秒)
                nowSec: 0,
                timer: null,
                showConfirm: false,
                using: false,
                knobX: 0,
                knobW: 50,
                maxX: 0,
                startX: 0,
                dragging: false
            };
        },
        created() {

        },
        computed: {
            pos() {
                return (this.info && this.info.pos) || {}
            },
            isPos() {
                return !!this.pos.enabled
            },
            clock() {
                const d = new Date(this.nowSec * 1000)
                const p = (n) => ('0' + n).slice(-2)
                return p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds())
            },
            today() {
                const d = new Date(this.nowSec * 1000)
                return d.getFullYear() + '年' + (d.getMonth() + 1) + '月' + d.getDate() + '日(' + '日月火水木金土'[d.getDay()] + ')'
            },
            leftText() {
                const s = Math.max(0, (this.pos.expire_at || 0) - this.nowSec)
                return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2)
            },
            usedLabel() {
                const t = String(this.pos.used_time || '')
                const m = t.match(/\d{4}-(\d{2})-(\d{2}) (\d{2}:\d{2})/)
                if (!m) return t
                const d = new Date(t.replace(/-/g, '/'))
                return Number(m[1]) + '/' + Number(m[2]) + '(' + '日月火水木金土'[d.getDay()] + ') ' + m[3]
            }
        },
        onLoad(options) {
            let query = options
            let id = query.id
            this.id = id
            this.getDetail(id)
        },
        onUnload() {
            clearInterval(this.timer)
        },
        methods: {
            imgChange(e) {
                this.imgIndex = (e && e.current) || 0
            },
            // [カスタムエイド 2026-10-19] 画像を全画面で表示(押した画像から。左右にスワイプで次の画像)
            openImages(list, index) {
                let urls = (list || []).map((v) => (typeof v == 'string' ? v : (v && (v.url || v.image)) || '')).filter((v) => v)
                if (!urls.length) return
                uni.previewImage({ current: index || 0, urls: urls, indicator: 'number', loop: urls.length > 1 })
            },
            textParas(t) {
                return textParas(t)
            },
            leftClick() {
                uni.navigateBack()
            },
            openmask() {
                this.showT = true
            },
            getDetail(id) {
                let that = this
                getCouponDetail(id).then((res) => {
                    if (res.code == 200) {
                        that.info = res.data.coupon
                        that.images = res.data.coupon.images
                        that.startClock()
                    }
                })
            },
            // [カスタムエイド 2026-10-22] 時刻を1秒ごとに動かす。表示の時間が終わったら読み直して「使用済み」に
            startClock() {
                clearInterval(this.timer)
                if (!this.isPos) return
                if (this.pos.now) this.offset = this.pos.now - Math.floor(Date.now() / 1000)
                const tick = () => {
                    this.nowSec = Math.floor(Date.now() / 1000) + this.offset
                    if (this.pos.using && this.pos.expire_at && this.nowSec >= this.pos.expire_at) {
                        clearInterval(this.timer)
                        this.getDetail(this.id)
                    }
                }
                tick()
                this.timer = setInterval(tick, 1000)
                this.$nextTick(() => this.measure())
                // 使っている間は、バーコードが画面に入るところまで動かす
                if (this.pos.using) setTimeout(() => uni.pageScrollTo({ selector: '.ps', duration: 250 }), 300)
            },
            measure() {
                uni.createSelectorQuery().in(this).select('#psSlide').boundingClientRect((r) => {
                    if (r) this.maxX = Math.max(0, r.width - this.knobW - 10)
                }).exec()
            },
            sStart(e) {
                if (!this.maxX) this.measure()
                this.dragging = true
                this.startX = e.touches[0].clientX - this.knobX
            },
            sMove(e) {
                if (!this.dragging) return
                this.knobX = Math.min(this.maxX, Math.max(0, e.touches[0].clientX - this.startX))
            },
            sEnd() {
                if (!this.dragging) return
                this.dragging = false
                if (this.maxX && this.knobX >= this.maxX * 0.85) {
                    this.knobX = this.maxX
                    this.showConfirm = true
                }
                setTimeout(() => { this.knobX = 0 }, this.showConfirm ? 300 : 0)
            },
            useNow() {
                if (this.using) return
                this.using = true
                selfUseCoupon(this.id).then((res) => {
                    this.using = false
                    this.showConfirm = false
                    if (res.code == 200 && res.data && res.data.pos) {
                        this.$set(this.info, 'pos', res.data.pos)
                        this.startClock()
                    } else {
                        uni.showToast({ title: res.message || '使えませんでした', icon: 'none' })
                        this.getDetail(this.id)
                    }
                }).catch(() => {
                    this.using = false
                    uni.showToast({ title: '通信できませんでした。もう一度お試しください', icon: 'none' })
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    /* [カスタムエイド 2026-10-22] お店のバーコード番号 */
    .ps { padding: 8rpx 32rpx 40rpx; border-bottom: 1px solid #D2D2D7; }
    .ps-live { background: #FFFFFF; border-radius: 32rpx; overflow: hidden; box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.10); }
    .ps-band { height: 68rpx; display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-size: 26rpx; font-weight: 700; letter-spacing: 2rpx;
        background: linear-gradient(90deg, #D93025, #F29900, #D93025, #F29900); background-size: 300% 100%; animation: psFlow 3s linear infinite; }
    @keyframes psFlow { from { background-position: 0% 0; } to { background-position: 100% 0; } }
    .ps-clock { font-size: 64rpx; font-weight: 700; text-align: center; margin-top: 24rpx; color: #1D1D1F; font-variant-numeric: tabular-nums; }
    .ps-date { font-size: 26rpx; color: #6E6E73; text-align: center; }
    .ps-bc { padding: 20rpx 32rpx 32rpx; text-align: center; }
    .ps-bc-img { width: 100%; height: 200rpx; }
    .ps-bc-n { font-size: 30rpx; letter-spacing: 4rpx; color: #1D1D1F; margin-top: 8rpx; }
    .ps-timer { font-size: 26rpx; color: #D93025; text-align: center; margin-top: 20rpx; }
    .ps-hint { font-size: 24rpx; color: #8A8A8E; text-align: center; line-height: 1.6; margin-top: 16rpx; }
    .ps-used { background: #FFFFFF; border-radius: 32rpx; padding: 48rpx 32rpx; text-align: center; margin-bottom: 24rpx; }
    .ps-stamp { display: inline-block; border: 6rpx solid #8A8A8E; color: #8A8A8E; font-size: 44rpx; font-weight: 700; padding: 8rpx 36rpx; border-radius: 20rpx; transform: rotate(-6deg); }
    .ps-used-t { font-size: 28rpx; color: #6E6E73; margin-top: 28rpx; }
    .ps-slide { position: relative; height: 120rpx; border-radius: 60rpx; background: #FCE8E6; overflow: hidden; margin-top: 16rpx; }
    .ps-slide-fill { position: absolute; left: 0; top: 0; bottom: 0; background: rgba(217, 48, 37, 0.18); border-radius: 60rpx; }
    .ps-slide-t { position: absolute; left: 0; right: 0; top: 0; bottom: 0; display: flex; align-items: center; justify-content: center; color: #D93025; font-weight: 700; font-size: 30rpx; padding-left: 60rpx; }
    .ps-knob { position: absolute; left: 10rpx; top: 10rpx; width: 100rpx; height: 100rpx; border-radius: 50%; background: #D93025; color: #FFFFFF; font-size: 48rpx; display: flex; align-items: center; justify-content: center; box-shadow: 0 4rpx 10rpx rgba(217, 48, 37, 0.35); }
    .ps-knob.back { transition: transform 0.25s ease-out; }
    .ps-ov { position: fixed; left: 0; top: 0; right: 0; bottom: 0; z-index: 1000; background: rgba(0, 0, 0, 0.55); display: flex; align-items: center; justify-content: center; }
    .ps-dlg { width: 600rpx; background: #FFFFFF; border-radius: 40rpx; padding: 56rpx 48rpx 48rpx; text-align: center; box-sizing: border-box; }
    .ps-dlg-i { width: 80rpx; height: 80rpx; border-radius: 50%; background: #FCE8E6; color: #D93025; font-size: 44rpx; font-weight: 700; margin: 0 auto 24rpx; display: flex; align-items: center; justify-content: center; }
    .ps-dlg-t { font-size: 34rpx; font-weight: 700; color: #1D1D1F; }
    .ps-dlg-m { font-size: 28rpx; color: #515154; line-height: 1.7; margin-top: 20rpx; white-space: pre-wrap; }
    .ps-dlg-ok { margin-top: 48rpx; height: 96rpx; border-radius: 24rpx; background: #D93025; color: #FFFFFF; font-size: 30rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .ps-dlg-ok.off { opacity: 0.6; }
    .ps-dlg-no { margin-top: 24rpx; height: 96rpx; border-radius: 24rpx; background: #F2F2F7; color: #1D1D1F; font-size: 30rpx; display: flex; align-items: center; justify-content: center; }
    .ps-hover { opacity: 0.7; }

    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    .mask {
        width: 100vw;
        height: 100vh;
        background: #fff;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 999 !important;

        .bimage {
            display: block;
            width: 140vw;
            height: 20vh;
            transform-origin: center;
            transform: rotate(90deg);
            position: absolute;
            top: 550upx;
            left: -20vw;
        }
    }

    .c-img {
        image {
            width: 100vw;
            height: 500rpx;
        }

        margin-bottom: 20rpx;
    }

    .c-tit {
        text-align: center;
        font-size: 32rpx;
        color: #1D1D1F;
        margin: 40rpx 0;
        border-bottom: 2upx solid #D2D2D7;

        .c-sn {
            font-size: 68rpx;
            color: #1A73E8;
            font-weight: 700;
        }
    }

    .ma {

        width: 95%;
        margin: 0 auto;
        padding-bottom: 100rpx;
        border-bottom: 1px solid #D2D2D7;
        text-align: center;

        image {
            width: 100%;
            height: 200rpx;
        }

        .ma-sn {
            margin-top: 10rpx;
            font-size: 32rpx;
            color: #1D1D1F;
        }
    }

    .cou-box {
        width: 100%;
        box-sizing: border-box;
        padding: 0 32upx 60rpx;
        margin: 0 auto;
        margin-top: 40rpx;
        border-bottom: 1px solid #D2D2D7;

        .c-title {
            color: #1D1D1F;
            font-weight: 700;
            font-size: 32rpx;
            margin-bottom: 40rpx;
        }

        .c-des {
            font-size: 32rpx;
            color: #707070;
        }
    }

    .c-itms {
        width: 90%;
        margin: 0 auto;
        margin-top: 40rpx;
        padding-bottom: 60rpx;
        border-bottom: 1px solid #D2D2D7;

        .validity-btn {
            width: 152rpx;
            height: 52rpx;
            line-height: 52rpx;
            font-size: 28rpx;
            color: #1A73E8;
            text-align: center;
            border-radius: 20rpx;
            border: 2rpx solid #1A73E8;
            margin-bottom: 20rpx;
        }

        .validity {
            color: #1D1D1F;
            font-size: 32rpx;
            font-weight: 700;

            text {
                font-weight: 400;
            }
        }
    }

    .beizhu {
        width: 100%;
        margin: 40rpx auto;
        box-sizing: border-box;
        padding: 0 32upx 60rpx;
        border-bottom: 1px solid #D2D2D7;

        .bei-btn {
            width: 152rpx;
            height: 52rpx;
            line-height: 52rpx;
            text-align: center;
            background: #F5F5F7;
            border-radius: 20rpx;
            font-size: 28rpx;
            color: #707070;
            border: 2rpx solid #707070;
            margin-bottom: 20rpx;
        }

        .bei {
            font-size: 32rpx;
            color: #1D1D1F;
        }
    }

    /* [カスタムエイド 2026-10-19] 画像の枚数表示(メニュー写真・TOP画像と同じ形) */
    .cs-swipe {
        position: relative;
    }
    .cs-top-count {
        position: absolute;
        right: 24rpx;
        bottom: 24rpx;
        z-index: 2;
        padding: 6rpx 20rpx;
        border-radius: 24rpx;
        background: rgba(0, 0, 0, 0.6);
        color: #FFFFFF;
        font-size: 24rpx;
        font-weight: 500;
        letter-spacing: 1rpx;
        pointer-events: none;
    }

    /* ===== [カスタムエイド 2026-10-19] クーポン詳細の間延び・位置のずれを直す ===== */
    .c-tit {
        margin: 0 !important;
        padding: 24rpx 0 20rpx;
    }
    .c-tit .c-sn {
        font-size: 56rpx !important;
        margin-top: 4rpx;
    }
    .ma {
        padding: 24rpx 0 28rpx !important;
    }
    .ma .ma-sn {
        letter-spacing: 2rpx;
    }
    .cou-box {
        margin-top: 0 !important;
        padding: 28rpx 32rpx !important;
    }
    .cou-box .c-title {
        margin-bottom: 16rpx !important;
    }
    .cou-box .c-des {
        font-size: 30rpx !important;
    }
    /* 有効期限・利用回数・備考の左端をそろえる(今までは 有効期限=幅90%で中央、利用回数=左右16px でずれていた) */
    .c-itms,
    .beizhu {
        width: 100% !important;
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 28rpx 32rpx !important;
    }
    .c-itms .validity-btn,
    .beizhu .bei-btn {
        margin-bottom: 16rpx !important;
        box-sizing: border-box;
    }
    .beizhu .bei {
        font-size: 30rpx !important;
    }
    .cs-bottom-space {
        height: calc(80rpx + env(safe-area-inset-bottom));
    }
</style>