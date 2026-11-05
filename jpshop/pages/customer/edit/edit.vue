<template>
    <view>
        <u-navbar title="メンバー編集" :placeholder="true" :safeAreaInsetTop="true" :border="true">
            <view class="u-nav-slot" slot="left">
                <image @click="bac" src="../../../static/icons/tclose.png" style="width: 60upx;height: 60upx;" mode="">
                </image>
            </view>
        </u-navbar>
        <!-- [カスタムエイド 2026-10-20] 見た目をこれまでの画面に合わせた。ここで変えられるのは「顧客NO」「メンバーズカード」「備考」だけなので、
             それを上の1つのまとまりにし、変えられない項目は下に灰色の「お客様の登録情報」として表示。右上の「保存」は下の保存ボタンに -->
        <view class="me">
            <view class="me-h3">編集できる項目</view>
            <view class="me-card">
                <view class="me-field">
                    <view class="me-label">顧客NO</view>
                    <input class="me-in" :class="{ on: fk == 'no' }" v-model="no" placeholder="お店で使っている番号(なくてもよい)" placeholder-class="me-ph"
                        @focus="fk = 'no'" @blur="fk = ''" />
                </view>
                <view class="me-field">
                    <view class="me-label">メンバーズカード</view>
                    <view class="me-in me-select" hover-class="me-hover" @click="toChooseCard">
                        <text class="me-card-name">{{cards.card_name || '-'}}</text>
                        <text class="me-change">変更</text>
                        <view class="me-chev"></view>
                    </view>
                </view>
                <!-- [カスタムエイド 2026-11-03] 他社のバーコード番号(他社のバーコードのカードのときだけ)。手で入れる/カメラで読む。下の「保存する」で一緒に保存 -->
                <view class="me-field" v-if="extType > 0">
                    <view class="me-label">他社のバーコード番号</view>
                    <view class="me-ext-note">お客様のポイントカードの番号を入れてください。種類:{{extType == 1 ? 'JAN(13桁・8桁の数字)' : 'CODE128(英数字)'}}</view>
                    <input class="me-in" :class="{ on: fk == 'ext', ng: extCheck.message && !extCheck.ok }" v-model="extValue" :type="extType == 1 ? 'number' : 'text'"
                        :maxlength="extType == 1 ? 13 : 48" :placeholder="extType == 1 ? '例:4912345678904' : '例:AB123456'" placeholder-class="me-ph"
                        @focus="fk = 'ext'" @blur="fk = ''" />
                    <view class="me-ext-chk" :class="{ ok: extCheck.ok }" v-if="extCheck.message">{{ extCheck.ok ? '✓ ' : '' }}{{ extCheck.message }}</view>
                    <view class="me-ext-bc" v-if="extBars.length">
                        <view class="me-ext-bars">
                            <view v-for="(b, i) in extBars" :key="i" class="me-ext-b" :style="{ flex: b.w, background: b.b ? '#000' : 'transparent' }"></view>
                        </view>
                        <view class="me-ext-n">{{ extCheck.value }}</view>
                    </view>
                    <view class="me-ext-cam" hover-class="me-hover" @click="scanExt">カメラで読む</view>
                </view>
                <view class="me-field">
                    <view class="me-label">備考</view>
                    <textarea class="me-ta" :class="{ on: fk == 'remark' }" v-model="userInfo.remark" :maxlength="-1"
                        placeholder="お店だけのメモ(お客様のアプリには表示されません)" placeholder-class="me-ph"
                        @focus="fk = 'remark'" @blur="fk = ''"></textarea>
                </view>
            </view>

            <view class="me-h3">お客様の登録情報</view>
            <view class="me-card me-ro">
                <view class="me-item"><text class="me-k">ID</text><text class="me-v">{{id}}</text></view>
                <view class="me-item"><text class="me-k">氏名</text><text class="me-v">{{userInfo.name || '-'}}</text></view>
                <view class="me-item"><text class="me-k">カナ</text><text class="me-v">{{userInfo.pronunciation || '-'}}</text></view>
                <view class="me-item"><text class="me-k">電話番号</text><text class="me-v">{{userInfo.phone || 'ー'}}</text></view>
                <view class="me-item"><text class="me-k">性別</text><text class="me-v">{{userInfo.sex || '-'}}</text></view>
                <view class="me-item"><text class="me-k">生年月日</text>
                    <text class="me-v">{{userInfo.birthday_year ? userInfo.birthday_year + '年' + userInfo.birthday_month + '月' + userInfo.birthday_day + '日' : '-'}}</text></view>
                <view class="me-item"><text class="me-k">住所</text>
                    <view class="me-v">
                        <view v-if="userInfo.zip_code">〒{{userInfo.zip_code}}</view>
                        <view>{{(userInfo.province || '') + (userInfo.address1 || '') || '-'}}</view>
                        <view v-if="userInfo.address2">{{userInfo.address2}}</view>
                    </view>
                </view>
                <view class="me-ro-note">これらは、お客様がアプリで登録した内容です。お店からは変更できません。</view>
            </view>

            <view class="me-save" hover-class="me-save-hover" @click="save">保存する</view>
        </view>
    </view>
</template>

<script>
    import {
        editMemberInformation,
        getMemberDetail
    } from '@/api/index.js'
    import { checkCode, barcodeBars } from '@/utils/csBarcode.js' // [カスタムエイド 2026-11-03] 他社のバーコード番号の確かめ・見本の線
    export default {
        data() {
            return {
                id: "",
                no: "",
                remark: "",
                userId: "",
                userInfo:{},
                cards:{},
                fk: '', // [カスタムエイド 2026-10-20] いま入力中の欄
                extType: 0, // [カスタムエイド 2026-11-03] 他社のバーコード 0:なし 1:JAN 2:CODE128(カードの設定)
                extValue: '', // [カスタムエイド 2026-11-03] 他社のバーコード番号
                extLoaded: false // [カスタムエイド 2026-11-03] 番号は最初に読んだときだけ入れる(カメラから戻ったときに、入れかけの番号を消さない)
            }
        },
        onLoad(options) {
            let that = this
            let query = options
            that.query = query
            
            that.id = query.id
            that.no = query.NO
            that.remark = query.remark
            that.userId = query.userId
        },
        onShow() {
            let that = this
            setTimeout(() => {
                that.getDetail(that.query.userId)
            },1000)
        },
        computed: {
            // [カスタムエイド 2026-11-03] 他社のバーコード番号の確かめ(クーポンと同じ決まり)
            extCheck() { return checkCode(this.extType, this.extValue) },
            extBars() { return this.extCheck.ok ? barcodeBars(this.extType, this.extCheck.value) : [] }
        },
        methods: {
            // [カスタムエイド 2026-11-03] お客様のプラスチックのカードをカメラで読む
            scanExt() {
                uni.scanCode({
                    onlyFromCamera: true,
                    scanType: ['barCode'],
                    success: (r) => {
                        const v = String(r.result || '').trim()
                        if (v) this.extValue = v
                    }
                })
            },
            bac() {
                uni.navigateBack()
            },
            
            getDetail(id){
                let that = this
                let data = {}
                data.id = id
                getMemberDetail(data).then((res) => {
                    if(res.code == 200){
                        that.userInfo = res.data
                        that.cards = res.data.card
                        that.extType = (res.data.cs_ext_bc_type || 0) * 1 // [カスタムエイド 2026-11-03]
                        if (!that.extLoaded) { that.extValue = res.data.cs_ext_bc_value || ''; that.extLoaded = true }
                    }
                })
            },

            toChooseCard(){
               let that = this
               uni.navigateTo({
                   url:"../cards/cards?userId="+that.query.userId+"&id="+that.userInfo.cid+"&name="+that.userInfo.name
               })
            },

            save() {
                let that = this
                let data = {}
                let obj = {}
                data._method = "put"
                data.member_no = that.no
                data.remark = that.userInfo.remark
                // [カスタムエイド 2026-11-03] 他社のバーコード番号(他社のカードのときだけ送る。空なら消す)
                if (that.extType > 0) {
                    if (that.extValue && !that.extCheck.ok) {
                        uni.showToast({ title: that.extCheck.message || '他社のバーコード番号を確かめてください', icon: 'none', duration: 2500 })
                        return
                    }
                    data.cs_ext_bc_value = that.extValue ? that.extCheck.value : ''
                }
                obj.id = that.userId
                editMemberInformation(data, that.userId).then((res) => {
                    if (res.code == 200) {
                        uni.showToast({
                            title: res.message,
                            duration: 2000
                        })
                        setTimeout(function() {
                            let pages = getCurrentPages();
                            let prePage = pages[pages.length - 2]
                            if (prePage.route == 'pages/customer/detail/detail') {
                                prePage.$vm.getDetail(obj)
                            }
                            uni.navigateBack()
                        }, 2100)
                    } else {
                        uni.showToast({ title: res.message || '保存できませんでした', icon: 'none' }) // [カスタムエイド 2026-10-20] 今までは何も出なかった
                    }
                })
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-20] これまでの画面と同じ見た目 */
    .me {
        min-height: 100vh;
        background: #F5F5F7;
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
        box-sizing: border-box;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .me-h3 {
        margin: 0 8rpx 16rpx;
        font-size: 30rpx;
        font-weight: 700;
        color: #515154;
    }

    .me-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 32rpx;
        margin-bottom: 40rpx;
    }

    /* [カスタムエイド 2026-10-21] 項目と項目のあいだを約6px広げる */
    .me-field {
        margin-bottom: 40rpx;
    }

    .me-field:last-child {
        margin-bottom: 0;
    }

    /* [カスタムエイド 2026-10-21] 見出しの文字と入力枠の見た目のすき間を約6pxに(行の高さを詰め、余白 4px) */
    .me-label {
        font-size: 26rpx;
        font-weight: 500;
        margin-bottom: 8rpx;
        line-height: 1.2;
    }

    .me-in {
        width: 100%;
        height: 88rpx;
        padding: 0 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        background: #FFFFFF;
        font-size: 30rpx;
        box-sizing: border-box;
    }

    .me-in.on,
    .me-ta.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    .me-ph {
        color: #8A8A8E;
        font-size: 28rpx;
    }

    .me-select {
        display: flex;
        align-items: center;
    }

    .me-card-name {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .me-change {
        margin: 0 12rpx 0 16rpx;
        font-size: 26rpx;
        color: #1A73E8;
    }

    /* 右向きの矢印 */
    .me-chev {
        width: 14rpx;
        height: 14rpx;
        border-right: 3rpx solid #1A73E8;
        border-bottom: 3rpx solid #1A73E8;
        transform: rotate(-45deg);
    }

    .me-hover {
        opacity: 0.6;
    }

    .me-ta {
        width: 100%;
        height: 280rpx;
        padding: 20rpx 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        font-size: 30rpx;
        line-height: 1.6;
        box-sizing: border-box;
    }

    .me-ro {
        padding: 8rpx 32rpx 28rpx;
    }

    .me-item {
        display: flex;
        align-items: flex-start;
        padding: 22rpx 0;
        border-bottom: 1px solid #E5E5EA;
    }

    .me-k {
        width: 160rpx;
        flex-shrink: 0;
        font-size: 26rpx;
        line-height: 1.6;
        color: #8A8A8E;
    }

    .me-v {
        flex: 1;
        min-width: 0;
        font-size: 28rpx;
        line-height: 1.6;
        color: #515154;
        word-break: break-all;
    }

    .me-ro-note {
        margin-top: 20rpx;
        font-size: 22rpx;
        line-height: 1.6;
        color: #8A8A8E;
    }

    .me-save {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
    }

    .me-save-hover {
        opacity: 0.8;
    }

    /* [カスタムエイド 2026-11-03] カードの影(2026-11-03):下へ1px・ぼかし4px・黒10%。グレーの背景と白いカードの境目を分かりやすく */
    .me-card {
        box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.10) !important;
    }
    /* [カスタムエイド 2026-11-03] 他社のバーコード番号 */
    .me-ext-note { font-size: 24rpx; color: #6E6E73; line-height: 1.6; margin-bottom: 12rpx; }
    .me-in.ng { border-color: #D93025; }
    .me-ext-chk { font-size: 24rpx; color: #D93025; margin-top: 10rpx; }
    .me-ext-chk.ok { color: #188038; }
    .me-ext-bc { border: 1px solid #E5E5EA; border-radius: 24rpx; padding: 24rpx 24rpx 16rpx; margin-top: 20rpx; text-align: center; background: #FFFFFF; }
    .me-ext-bars { display: flex; height: 120rpx; }
    .me-ext-b { height: 100%; }
    .me-ext-n { font-size: 26rpx; letter-spacing: 4rpx; color: #1D1D1F; margin-top: 8rpx; }
    .me-ext-cam { margin-top: 20rpx; height: 80rpx; border-radius: 24rpx; background: #F2F2F7; color: #1A73E8; font-size: 28rpx; font-weight: 600; display: flex; align-items: center; justify-content: center; }
</style>
