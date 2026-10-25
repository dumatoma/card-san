<template>
    <!-- [カスタムエイド 2026-10-23] 各種設定 › カルテ(D5)。いちばん上にテンプレート(3種類まで)、下に副管理者の権限・使っている容量 -->
    <view class="ks">
        <u-navbar title="カルテ" :placeholder="true" :safeAreaInsetTop="true" :border="true" @leftClick="back"></u-navbar>
        <view class="ks-body">
            <view class="ks-h">テンプレート<text class="ks-h-n">{{ list.length }} / 3</text></view>
            <view class="ks-cap">カルテのページが埋まったときに足す、お店の紙のカルテです。紙を撮影するか、写真から選んで登録できます。</view>
            <view class="ks-card">
                <!-- [カスタムエイド 2026-10-24] 押すと「テンプレートを編集」が直接開く(名前・メモ・消すは編集画面のテンプレート名から) -->
                <view class="ks-tp" v-for="t in list" :key="t.id" hover-class="ks-hover" @click="edit(t)">
                    <view class="ks-th">
                        <image v-if="t.url" :src="t.url" mode="aspectFit"></image>
                        <text v-else class="ks-th-w">取り込み中</text>
                    </view>
                    <view class="ks-tt">
                        <view class="ks-n">{{ t.name }}</view>
                        <view class="ks-s">{{ t.note ? t.note + ' ・ ' : '' }}{{ t.source == 1 ? 'PDF' : '写真' }}・1ページ{{ t.marks && t.marks.length ? ' ・ 差し込み ' + t.marks.length : '' }}</view>
                    </view>
                    <view class="ks-chev"></view>
                </view>
                <view class="ks-slot" hover-class="ks-hover" v-if="list.length < 3" @click="add">＋ テンプレートを登録(あと{{ 3 - list.length }}つ)</view>
            </view>

            <view class="ks-h">そのほか</view>
            <view class="ks-card">
                <view class="ks-lk" hover-class="ks-hover" v-if="owner" @click="toAdmins">
                    <text class="ks-lk-n">副管理者のカルテの権限</text>
                    <text class="ks-lk-v">副管理者の設定へ</text>
                    <view class="ks-chev"></view>
                </view>
                <view class="ks-lk">
                    <text class="ks-lk-n">使っている容量</text>
                    <text class="ks-lk-v">{{ usageText }}</text>
                </view>
            </view>
        </view>

        <!-- 登録・編集 -->
        <cs-sheet :show="formShow" :title="form.id ? 'テンプレートを編集' : 'テンプレートを登録'" @close="formShow = false">
            <view class="ks-in">
                <view class="ks-prev" v-if="form.path || form.url">
                    <image :src="form.path || form.url" mode="aspectFit"></image>
                </view>
                <view class="ks-tagbtn" hover-class="ks-hover" v-if="form.id && form.url" @click="toTags">
                    <image class="k-ic" src="/static/karte/tag.svg"></image>
                    <view class="ks-tagtt">テンプレートを編集<text class="ks-tags">{{ form.marks && form.marks.length ? form.marks.length + 'か所に置いています' : '名前・電話番号などを、テンプレートを足したときに自動で入れます' }}</text></view>
                    <view class="ks-chev"></view>
                </view>
                <view class="ks-lbl">名前<text class="ks-req">※</text></view>
                <input class="ks-ip" v-model="form.name" maxlength="20" placeholder="例:問診票" />
                <view class="ks-lbl">メモ</view>
                <input class="ks-ip" v-model="form.note" maxlength="20" placeholder="例:初回用・2回目以降" />
            </view>
            <view class="ks-btn" :class="{ off: busy || !form.name.trim() }" hover-class="ks-hover" @click="save">{{ busy ? '保存中…' : (form.id ? '保存する' : '登録する') }}</view>
            <view class="ks-del" v-if="form.id" hover-class="ks-hover" @click="remove">このテンプレートを消す</view>
        </cs-sheet>
    </view>
</template>

<script>
    import csSheet from '@/components/csSheet/csSheet.vue'
    import pickImage from '@/utils/pickImage.js'
    import { karteTpl, karteTplStore, karteTplUpdate, karteTplDelete, karteUsage } from '@/api/karte.js'

    export default {
        components: { csSheet },
        data() {
            return {
                list: [],
                usage: 0,
                owner: true,
                formShow: false,
                form: { id: 0, name: '', note: '', path: '', url: '' },
                busy: false
            }
        },
        computed: {
            usageText() {
                const b = this.usage
                if (b >= 1073741824) return (b / 1073741824).toFixed(1) + 'GB'
                if (b >= 1048576) return Math.round(b / 1048576) + 'MB'
                return Math.max(0, Math.round(b / 1024)) + 'KB'
            }
        },
        onShow() {
            const a = uni.getStorageSync('admin') || {}
            this.owner = a.admin_type != 2
            this.load()
        },
        methods: {
            back() {
                uni.navigateBack()
            },
            load() {
                karteTpl().then((res) => {
                    if (res && res.code == 403) { uni.showToast({ title: res.message, icon: 'none' }); setTimeout(() => uni.navigateBack(), 1200); return }
                    if (res && res.code == 200) this.list = res.data.list || []
                })
                karteUsage().then((res) => { if (res && res.code == 200) this.usage = res.data.bytes || 0 })
            },
            add() {
                pickImage({
                    count: 1,
                    sizeType: ['original'],
                    success: (r) => {
                        this.form = { id: 0, name: '', note: '', path: r.tempFilePaths[0], url: '' }
                        this.formShow = true
                    }
                })
            },
            edit(t) {
                if (!t.url) return uni.showToast({ title: 'このテンプレートは取り込み中です', icon: 'none' })
                uni.setStorageSync('karteTplEdit', { id: t.id, name: t.name, note: t.note || '', url: t.url, w: t.w, h: t.h, marks: t.marks || [], edited: !!t.edited })
                uni.navigateTo({ url: '/pages/karte/tplEdit' })
            },
            save() {
                const name = this.form.name.trim()
                if (!name || this.busy) return
                this.busy = true
                const req = this.form.id
                    ? karteTplUpdate(this.form.id, { name, note: this.form.note.trim() })
                    : karteTplStore(this.form.path, { name, note: this.form.note.trim() })
                req.then((res) => {
                    this.busy = false
                    if (res && res.code == 200) {
                        uni.showToast({ title: res.message || '保存しました', icon: 'none' })
                        this.formShow = false
                        // 新しく登録したら、そのまま「テンプレートを編集」を開く(差し込みを置けるように)
                        const newId = !this.form.id && res.data ? res.data.id : 0
                        karteTpl().then((r2) => {
                            if (r2 && r2.code == 200) this.list = r2.data.list || []
                            const t = newId ? this.list.find((x) => x.id == newId) : null
                            if (t && t.url) setTimeout(() => this.edit(t), 500)
                        })
                    } else if (res && res.message) {
                        uni.showToast({ title: res.message, icon: 'none' })
                    }
                })
            },
            remove() {
                uni.showModal({
                    title: '「' + this.form.name + '」を消しますか?',
                    content: 'これまでに足したページは消えません。',
                    confirmText: '消す',
                    confirmColor: '#D93025',
                    cancelText: 'キャンセル',
                    success: (m) => {
                        if (!m.confirm) return
                        karteTplDelete(this.form.id).then((res) => {
                            if (res && res.code == 200) { this.formShow = false; this.load() }
                        })
                    }
                })
            },
            // [カスタムエイド 2026-10-23] 差し込みを置く画面へ
            toTags() {
                uni.setStorageSync('karteTplEdit', { id: this.form.id, name: this.form.name, note: this.form.note, url: this.form.url, w: this.form.w, h: this.form.h, marks: this.form.marks })
                this.formShow = false
                uni.navigateTo({ url: '/pages/karte/tplEdit' })
            },
            toAdmins() {
                uni.navigateTo({ url: '/pages/settings/Administrators/Administrators' })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .ks { min-height: 100vh; background: #F2F2F7; color: #1D1D1F; }
    .ks-hover { opacity: 0.6; }
    .ks-body { padding: 32rpx 24rpx calc(64rpx + env(safe-area-inset-bottom)); }
    .ks-h { display: flex; font-size: 30rpx; font-weight: 700; color: #3C3C43; margin: 8rpx 12rpx 12rpx; }
    .ks-h-n { margin-left: auto; font-weight: 400; color: #6E6E73; font-size: 24rpx; }
    .ks-cap { font-size: 24rpx; color: #6E6E73; line-height: 1.6; margin: 0 12rpx 20rpx; }
    .ks-card { background: #FFFFFF; border-radius: 32rpx; padding: 0 32rpx; margin-bottom: 48rpx; }
    .ks-tp { display: flex; align-items: center; padding: 28rpx 0; border-bottom: 1px solid #E5E5EA; }
    .ks-th { width: 96rpx; height: 132rpx; border-radius: 8rpx; background: #FBFAF6; border: 1px solid #E5E5EA; flex-shrink: 0; display: flex; align-items: center; justify-content: center; overflow: hidden;
        image { width: 100%; height: 100%; } }
    .ks-th-w { font-size: 18rpx; color: #8E8E93; }
    .ks-tt { flex: 1; min-width: 0; margin: 0 20rpx 0 24rpx; }
    .ks-n { font-size: 30rpx; }
    .ks-s { font-size: 22rpx; color: #6E6E73; margin-top: 4rpx; }
    .ks-ed { font-size: 26rpx; color: #1A73E8; }
    .ks-slot { margin: 28rpx 0; height: 120rpx; border: 3rpx dashed #8AB4F8; border-radius: 24rpx; background: #F5F9FF; color: #1A73E8; font-size: 28rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .ks-lk { display: flex; align-items: center; min-height: 104rpx; border-bottom: 1px solid #E5E5EA; }
    .ks-lk:last-child { border-bottom: 0; }
    .ks-lk-n { font-size: 28rpx; }
    .ks-lk-v { margin-left: auto; margin-right: 12rpx; font-size: 26rpx; color: #6E6E73; }
    .ks-chev { width: 14rpx; height: 14rpx; border-right: 3rpx solid #C7C7CC; border-bottom: 3rpx solid #C7C7CC; transform: rotate(-45deg); }
    .ks-in { padding: 24rpx 40rpx 0; }
    .ks-prev { height: 520rpx; background: #F2F2F7; border-radius: 20rpx; display: flex; align-items: center; justify-content: center; margin-bottom: 8rpx;
        image { width: 100%; height: 100%; } }
    .ks-lbl { font-size: 26rpx; font-weight: 700; margin: 32rpx 0 8rpx; line-height: 1.2; }
    .ks-req { color: #D93025; margin-left: 6rpx; }
    .ks-ip { height: 88rpx; border: 1px solid #D2D2D7; border-radius: 20rpx; padding: 0 24rpx; font-size: 28rpx; }
    .ks-btn { margin: 64rpx 40rpx 0; height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .ks-btn.off { opacity: 0.5; }
    .ks-del { margin: 32rpx 40rpx 0; text-align: center; color: #D93025; font-size: 28rpx; }
    .ks-tagbtn { display: flex; align-items: center; margin-top: 24rpx; padding: 20rpx 24rpx; border-radius: 20rpx; background: #F5F9FF; border: 1px solid #C6DAFC; }
    .ks-tagtt { flex: 1; margin-left: 20rpx; font-size: 28rpx; font-weight: 700; color: #1A73E8; display: flex; flex-direction: column; }
    .ks-tags { font-size: 22rpx; font-weight: 400; color: #6E6E73; margin-top: 4rpx; }
    .k-ic { width: 48rpx; height: 48rpx; }
</style>
