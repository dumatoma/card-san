<template>
    <view class="container rc-page">
        <!-- [カスタムエイド 2026-10-20] 見出しを共通の見出し(20px 太字)に。今までは iPhone の標準の見出し -->
        <u-navbar title="来店履歴" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <!-- [カスタムエイド 2026-10-20] 来店履歴を1件ずつ白い角丸のカードに(管理WEBの来店履歴と同じ並び)。
             右下の「編集」でメニュー・担当者を直せる(管理WEBと同じ動き・同じ言葉)。削除は横スライドをやめ「削除」の文字に -->
        <view class="rc">
            <view class="rc-card" v-for="(item, index) in array" :key="item.id"
                :class="{ editing: editingId == item.id, dim: editingId && editingId != item.id, saved: savedId == item.id }">
                <view class="rc-line">
                    <image class="rc-ic" src="../../../static/svg/correct.svg" mode="aspectFit"></image>
                    <view class="rc-text rc-date">
                        <text>{{item.date}}</text>
                        <text class="rc-time" v-for="(t, ti) in item.times" :key="ti">{{t}}</text>
                        <text class="rc-time" v-if="!item.times || item.times.length == 0">--</text>
                    </view>
                </view>

                <!-- メニュー -->
                <view class="rc-line" v-if="editingId != item.id">
                    <image class="rc-ic" src="../../../static/svg/menu.svg" mode="aspectFit"></image>
                    <view class="rc-text">
                        <view v-for="(m, mi) in item.menu_names" :key="mi">{{m}}</view>
                        <view v-if="!item.menu_names || item.menu_names.length == 0">--</view>
                    </view>
                </view>
                <view class="rc-line" v-else>
                    <image class="rc-ic" src="../../../static/svg/menu.svg" mode="aspectFit"></image>
                    <view class="rc-text">
                        <view class="rc-elabel">メニュー</view>
                        <view class="rc-chips">
                            <text class="rc-none" v-if="editMenus.length == 0">未設定</text>
                            <view class="rc-chip" :class="{ legacy: m.legacy }" v-for="(m, mi) in editMenus" :key="'m' + mi"
                                @click="m.legacy ? openMenuPicker(mi) : null">
                                <text>{{m.legacy ? m.title + '(現在は登録なし)' : m.title}}</text>
                                <view class="rc-chip-x" v-if="!m.legacy" @click.stop="removeEditMenu(mi)"><view class="rc-x"></view></view>
                            </view>
                            <view class="rc-chip-add" hover-class="rc-hover" @click="openMenuPicker(null)">メニューを選択</view>
                        </view>
                    </view>
                </view>

                <!-- 担当者 -->
                <view class="rc-line" v-if="editingId != item.id">
                    <image class="rc-ic" src="../../../static/svg/user.svg" mode="aspectFit"></image>
                    <view class="rc-text">
                        <view v-for="(n, ni) in item.day_sadmin_names" :key="ni">{{n}}</view>
                        <view v-if="!item.day_sadmin_names || item.day_sadmin_names.length == 0">--</view>
                    </view>
                </view>
                <view class="rc-line" v-else>
                    <image class="rc-ic" src="../../../static/svg/user.svg" mode="aspectFit"></image>
                    <view class="rc-text">
                        <view class="rc-elabel">担当者</view>
                        <picker v-for="(st, si) in editStaffs" :key="'s' + si" :range="staffRange(st)" range-key="label"
                            :value="staffIndex(st)" @change="pickStaff(si, st, $event)">
                            <view class="rc-select">
                                <text>{{staffLabel(st)}}</text>
                                <view class="rc-caret"></view>
                            </view>
                        </picker>
                    </view>
                </view>

                <!-- スタンプ・ポイント -->
                <view class="rc-line" v-if="item.point && item.point.length > 0">
                    <view class="rc-coin">{{toUp(item.point[0].card)}}</view>
                    <view class="rc-text rc-points">
                        <text class="rc-pt" :class="{ minus: it.point <= 0 }" v-for="(it, pi) in item.point" :key="pi">{{it.point}}</text>
                    </view>
                </view>

                <!-- クーポン -->
                <view class="rc-line">
                    <image class="rc-ic" src="../../../static/svg/coupon.svg" mode="aspectFit"></image>
                    <view class="rc-text">
                        <view v-for="(c, ci) in item.coupon_names" :key="ci">{{c}}</view>
                        <view v-if="!item.coupon_names || item.coupon_names.length == 0">--</view>
                    </view>
                </view>

                <view class="rc-act" v-if="editingId != item.id">
                    <text class="rc-edit" @click="startEditCome(item)">編集</text>
                    <text class="rc-del" @click="handleClick(item.id)">削除</text>
                </view>
                <view class="rc-btns" v-else>
                    <view class="rc-btn gray" hover-class="rc-hover" @click="cancelEditCome">キャンセル</view>
                    <view class="rc-btn" :class="{ dis: comeSaving }" hover-class="rc-hover" @click="saveEditCome(item)">保存</view>
                </view>
            </view>
            <view class="rc-empty" v-if="loaded && array.length == 0">来店履歴はまだありません</view>
        </view>

        <!-- メニューを選択(複数可) -->
        <u-popup :show="menuPickerShow" mode="bottom" round="32" @close="menuPickerShow = false">
            <view class="rc-pick">
                <view class="rc-pick-tit">メニューを選択</view>
                <view class="rc-pick-hint">複数選べます。外すときはチェックを外してください</view>
                <scroll-view scroll-y class="rc-pick-list">
                    <view class="rc-pick-opt" v-for="m in comeOptions.menus" :key="m.id" @click="toggleMenuCheck(m.id)">
                        <view class="rc-check" :class="{ on: menuPickerChecked.indexOf(m.id) > -1 }">
                            <view class="rc-tick" v-if="menuPickerChecked.indexOf(m.id) > -1"></view>
                        </view>
                        <text :class="{ on: menuPickerChecked.indexOf(m.id) > -1 }">{{m.title}}</text>
                    </view>
                    <view class="rc-pick-empty" v-if="comeOptions.menus.length == 0">登録されているメニューがありません</view>
                </scroll-view>
                <view class="rc-btns">
                    <view class="rc-btn gray" hover-class="rc-hover" @click="menuPickerShow = false">キャンセル</view>
                    <view class="rc-btn" hover-class="rc-hover" @click="applyMenuPicker">決定</view>
                </view>
            </view>
        </u-popup>
        <mod v-if="showModule" @getStatus="getStatus" :config="configs"></mod>
    </view>
</template>

<script>
    import mod from "@/components/mod.vue"
    import {
        getAppointmentList,
        getCome,
        deleteAppointOrders,
        getComeOptions, // [カスタムエイド 2026-10-20] 来店履歴の編集
        updateCome // [カスタムエイド 2026-10-20] 来店履歴の編集
    } from "@/api/index.js"

    export default {
        components: {
            mod
        },
        data() {
            return {
                options1: [{
                    text: '削除',
                    style: {
                        backgroundColor: '#D93025'
                    }
                }],
                showModule: false,
                configs: {},
                current: 0,
                array: [],
                aid: "",
                // [カスタムエイド 2026-10-20] 来店履歴の編集(管理WEBと同じ)
                loaded: false,
                editingId: null, // 編集中の来店履歴のid
                editMenus: [], // 編集中のメニュー [{id, title, legacy}]。legacy=今は登録のない名前
                editStaffs: [0], // 編集中の担当者(スタッフid、0=未設定、'keep:名前'=今は登録のない名前)
                comeOptions: { menus: [], admins: [] },
                comeOptionsLoaded: false,
                menuPickerShow: false,
                menuPickerChecked: [],
                menuReplaceIndex: null,
                comeSaving: false,
                savedId: null
            }
        },
        onLoad(options) {
            let that = this
            that.aid = options.id
            that.getList(options.id)
        },
        methods: {
            // [カスタムエイド 2026-10-20] 来店履歴の編集 ここから(管理WEB downloads.vue と同じ考え方)
            isKeepStaff(v) {
                return typeof v === 'string' && v.indexOf('keep:') === 0
            },
            staffRange(st) {
                const head = this.isKeepStaff(st) ? [{ v: st, label: st.slice(5) + '(現在は登録なし)' }] : [{ v: 0, label: '未設定(--)' }]
                return head.concat(this.comeOptions.admins.map((a) => ({ v: a.id, label: a.name })))
            },
            staffIndex(st) {
                const i = this.staffRange(st).findIndex((o) => o.v === st)
                return i < 0 ? 0 : i
            },
            staffLabel(st) {
                const o = this.staffRange(st)[this.staffIndex(st)]
                return o ? o.label : '未設定(--)'
            },
            pickStaff(si, st, e) {
                const o = this.staffRange(st)[e.detail.value]
                if (o) this.$set(this.editStaffs, si, o.v)
            },
            startEditCome(item) {
                const begin = () => {
                    this.editMenus = (item.menu_names || []).filter((n) => n !== '' && n !== null).map((n) => {
                        const m = this.comeOptions.menus.find((x) => x.title === n)
                        return m ? { id: m.id, title: m.title, legacy: false } : { id: null, title: n, legacy: true }
                    })
                    const staffs = (item.day_sadmin_names || []).filter((n) => n !== '' && n !== null).map((n) => {
                        const a = this.comeOptions.admins.find((x) => x.name === n)
                        return a ? a.id : 'keep:' + n
                    })
                    this.editStaffs = staffs.length > 0 ? staffs : [0]
                    this.editingId = item.id
                }
                if (this.comeOptionsLoaded) {
                    begin()
                    return
                }
                uni.showLoading({ title: '読み込み中', mask: true })
                getComeOptions().then((res) => {
                    uni.hideLoading()
                    if (res && res.code == 200) {
                        this.comeOptions = { menus: res.data.menus || [], admins: res.data.admins || [] }
                        this.comeOptionsLoaded = true
                        begin()
                    } else {
                        uni.showToast({ title: (res && res.message) || '読み込みに失敗しました', icon: 'none' })
                    }
                }).catch(() => {
                    uni.hideLoading()
                    uni.showToast({ title: '読み込みに失敗しました', icon: 'none' })
                })
            },
            cancelEditCome() {
                this.editingId = null
                this.menuPickerShow = false
            },
            removeEditMenu(mi) {
                this.editMenus.splice(mi, 1)
            },
            openMenuPicker(replaceIndex) {
                this.menuReplaceIndex = replaceIndex
                this.menuPickerChecked = this.editMenus.filter((m) => !m.legacy).map((m) => m.id)
                this.menuPickerShow = true
            },
            toggleMenuCheck(id) {
                const i = this.menuPickerChecked.indexOf(id)
                if (i > -1) this.menuPickerChecked.splice(i, 1)
                else this.menuPickerChecked.push(id)
            },
            applyMenuPicker() {
                const checked = this.menuPickerChecked
                const before = this.editMenus.filter((m) => !m.legacy).map((m) => m.id)
                let legacy = this.editMenus.filter((m) => m.legacy)
                const added = checked.filter((id) => before.indexOf(id) < 0)
                // 今は登録のない名前の札から開いて、新しいメニューを選んだら、その札と入れ替える
                if (this.menuReplaceIndex !== null && added.length > 0) {
                    const target = this.editMenus[this.menuReplaceIndex]
                    legacy = legacy.filter((m) => m !== target)
                }
                // 並び順:残っている今までのもの → 新しく選んだもの(選んだ順)
                const ids = before.filter((id) => checked.indexOf(id) >= 0).concat(added)
                this.editMenus = legacy.concat(ids.map((id) => {
                    const m = this.comeOptions.menus.find((x) => x.id === id)
                    return { id: id, title: m ? m.title : '', legacy: false }
                }))
                this.menuPickerShow = false
            },
            saveEditCome(item) {
                if (this.comeSaving) return
                const data = {
                    menu_ids: this.editMenus.filter((m) => !m.legacy).map((m) => m.id),
                    keep_menu_names: this.editMenus.filter((m) => m.legacy).map((m) => m.title),
                    admin_ids: this.editStaffs.filter((v) => typeof v === 'number' && v > 0),
                    keep_admin_names: this.editStaffs.filter((v) => this.isKeepStaff(v)).map((v) => v.slice(5))
                }
                this.comeSaving = true
                updateCome(item.id, data).then((res) => {
                    this.comeSaving = false
                    if (res && res.code == 200) {
                        item.menu_names = res.data.menu_names
                        item.day_sadmin_names = res.data.day_sadmin_names
                        this.editingId = null
                        this.savedId = item.id
                        setTimeout(() => {
                            if (this.savedId === item.id) this.savedId = null
                        }, 2000)
                        uni.showToast({ title: '保存しました', icon: 'none' })
                    } else {
                        uni.showToast({ title: (res && res.message) || '保存できませんでした', icon: 'none' })
                    }
                }).catch(() => {
                    this.comeSaving = false
                    uni.showToast({ title: '保存できませんでした', icon: 'none' })
                })
            },
            // 来店履歴の編集 ここまで
            getList(id) {
                let that = this
                let data = {}
                data.id = id
                data.type = "come"
                getCome(data).then((res) => {
                    that.loaded = true // [カスタムエイド 2026-10-20]
                    if (res.code == 200) {
                        that.array = res.data.orders
                    }
                })
            },
            toUp(e) {
                return e.toUpperCase()
            },
            handleClick(e) {
                this.id = e
                let obj = {}
                obj.title = 'この情報を削除しますか？'
                obj.content = '削除すると、この情報は完全に削除されます。'
                obj.confirm = true
                obj.cancel = true
                obj.confirmText = "情報を削除"
                obj.cancelText = "キャンセル"
                this.configs = obj
                this.current = e
                this.showModule = true
            },
            getStatus(e) {
                if (e == "confirm") {
                    let id = this.id
                    deleteAppointOrders(id).then((res) => {
                        this.showModule = false
                        this.editingId = null // [カスタムエイド 2026-10-20] 削除したら編集も終える
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            this.getList(this.aid)
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                } else {
                    this.showModule = false
                }
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-20] 来店履歴のカード(管理WEBと同じ並び・これまでの画面と同じ見た目) */
    .container.rc-page {
        padding: 0;
    }

    .rc {
        min-height: 100vh;
        background: #F5F5F7;
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
        box-sizing: border-box;
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .rc-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 28rpx 32rpx 20rpx;
        margin-bottom: 24rpx;
        border: 2px solid transparent;
        transition: background .6s;
    }

    .rc-card.editing {
        background: #EEF4FD;
        border-color: #1A73E8;
    }

    .rc-card.dim {
        opacity: 0.45;
        pointer-events: none;
    }

    .rc-card.saved {
        background: #EEF8F0;
    }

    .rc-line {
        display: flex;
        align-items: flex-start;
        padding: 10rpx 0;
    }

    .rc-ic {
        width: 40rpx;
        height: 40rpx;
        flex-shrink: 0;
        margin: 4rpx 24rpx 0 0;
    }

    .rc-coin {
        width: 40rpx;
        height: 40rpx;
        line-height: 40rpx;
        flex-shrink: 0;
        margin: 4rpx 24rpx 0 0;
        border-radius: 50%;
        background: #D93025;
        color: #FFFFFF;
        text-align: center;
        font-size: 24rpx;
        font-weight: 700;
    }

    .rc-text {
        flex: 1;
        min-width: 0;
        font-size: 30rpx;
        line-height: 1.6;
    }

    .rc-date {
        display: flex;
        flex-wrap: wrap;
        font-weight: 500;
    }

    .rc-time {
        margin-left: 20rpx;
    }

    .rc-points {
        display: flex;
        flex-wrap: wrap;
    }

    .rc-pt {
        min-width: 48rpx;
        margin-right: 16rpx;
    }

    .rc-pt.minus {
        color: #D93025;
    }

    .rc-act {
        display: flex;
        justify-content: flex-end;
        padding-top: 4rpx;
        font-size: 28rpx;
    }

    .rc-edit {
        color: #1A73E8;
        padding: 8rpx 0 8rpx 24rpx;
    }

    .rc-del {
        color: #D93025;
        padding: 8rpx 0 8rpx 40rpx;
    }

    .rc-elabel {
        font-size: 24rpx;
        font-weight: 700;
        color: #3A5A8C;
        margin-bottom: 8rpx;
    }

    .rc-chips {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
    }

    .rc-none {
        font-size: 26rpx;
        color: #8E8E93;
        margin: 0 16rpx 12rpx 0;
    }

    .rc-chip {
        display: flex;
        align-items: center;
        height: 60rpx;
        padding: 0 8rpx 0 24rpx;
        margin: 0 12rpx 12rpx 0;
        border-radius: 30rpx;
        background: #FFFFFF;
        border: 1px solid #9BB8E8;
        font-size: 26rpx;
        box-sizing: border-box;
    }

    .rc-chip.legacy {
        padding: 0 24rpx;
        background: #F2F2F7;
        border-color: #D1D1D6;
        color: #6E6E73;
    }

    .rc-chip-x {
        width: 44rpx;
        height: 44rpx;
        margin-left: 10rpx;
        border-radius: 22rpx;
        background: #E3E8F0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* × */
    .rc-x {
        position: relative;
        width: 20rpx;
        height: 20rpx;
    }

    .rc-x::before,
    .rc-x::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        width: 22rpx;
        height: 3rpx;
        margin: -1.5rpx 0 0 -11rpx;
        background: #3A3A3C;
        transform: rotate(45deg);
    }

    .rc-x::after {
        transform: rotate(-45deg);
    }

    .rc-chip-add {
        height: 60rpx;
        line-height: 56rpx;
        padding: 0 24rpx;
        margin: 0 12rpx 12rpx 0;
        border-radius: 30rpx;
        border: 2px dashed #1A73E8;
        color: #1A73E8;
        font-size: 26rpx;
        box-sizing: border-box;
    }

    .rc-select {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 80rpx;
        padding: 0 24rpx;
        margin-bottom: 12rpx;
        border: 1px solid #9BB8E8;
        border-radius: 20rpx;
        background: #FFFFFF;
        font-size: 28rpx;
        box-sizing: border-box;
    }

    /* 下向きの矢印 */
    .rc-caret {
        width: 14rpx;
        height: 14rpx;
        border-right: 3rpx solid #6E6E73;
        border-bottom: 3rpx solid #6E6E73;
        transform: translateY(-4rpx) rotate(45deg);
    }

    .rc-btns {
        display: flex;
        justify-content: flex-end;
        padding-top: 12rpx;
    }

    .rc-btn {
        min-width: 200rpx;
        height: 80rpx;
        line-height: 80rpx;
        margin-left: 20rpx;
        padding: 0 28rpx;
        border-radius: 20rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 28rpx;
        font-weight: 700;
        box-sizing: border-box;
    }

    .rc-btn.gray {
        background: #E5E5EA;
        color: #3A3A3C;
        font-weight: 500;
    }

    .rc-btn.dis {
        opacity: 0.5;
    }

    .rc-hover {
        opacity: 0.6;
    }

    .rc-empty {
        padding: 160rpx 0;
        text-align: center;
        font-size: 28rpx;
        color: #8A8A8E;
    }

    .rc-pick {
        padding: 36rpx 32rpx calc(32rpx + env(safe-area-inset-bottom));
    }

    .rc-pick-tit {
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
    }

    .rc-pick-hint {
        margin: 8rpx 0 20rpx;
        text-align: center;
        font-size: 24rpx;
        color: #6E6E73;
    }

    .rc-pick-list {
        max-height: 55vh;
    }

    .rc-pick-opt {
        display: flex;
        align-items: center;
        min-height: 92rpx;
        border-bottom: 1px solid #F0F0F2;
        font-size: 30rpx;
        color: #6E6E73;
    }

    .rc-pick-opt text.on {
        color: #1D1D1F;
    }

    .rc-check {
        width: 44rpx;
        height: 44rpx;
        flex-shrink: 0;
        margin-right: 24rpx;
        border: 2px solid #C7C7CC;
        border-radius: 12rpx;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .rc-check.on {
        border-color: #1A73E8;
        background: #1A73E8;
    }

    .rc-tick {
        width: 10rpx;
        height: 20rpx;
        border-right: 4rpx solid #FFFFFF;
        border-bottom: 4rpx solid #FFFFFF;
        transform: translateY(-3rpx) rotate(45deg);
    }

    .rc-pick-empty {
        padding: 60rpx 0;
        text-align: center;
        color: #8A8A8E;
        font-size: 28rpx;
    }
</style>
