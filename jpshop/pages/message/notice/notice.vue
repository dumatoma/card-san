<template>
    <!-- [カスタムエイド 2026-10-21] メッセージ一覧をほかの画面と同じ見た目に。
         白いカード・タイトル(太字)・本文の出だし・配信先と日時、右に状態(予約=青/配信済=グレー)。
         下の「メッセージ作成」ボタンは固定のまま、後ろは少し透けて一覧が見える -->
    <view class="nl">
        <u-navbar title="メッセージ一覧" :placeholder="true" :safeAreaInsetTop="true" :border="true" :autoBack="true" bgColor="#FBFBFD"></u-navbar>
        <view class="nl-pg">
            <view class="nl-sum">
                <view>
                    <view class="nl-sum-k">今月の配信数</view>
                    <view class="nl-bar"><view class="nl-bar-in" :style="{ width: (tanum ? Math.min(100, count / tanum * 100) : 0) + '%' }"></view></view>
                </view>
                <view class="nl-sum-v">{{ num(count) }}<text class="nl-sum-t"> / {{ num(tanum) }}通</text></view>
            </view>

            <view class="nl-sec">配信履歴</view>
            <view class="nl-list" v-if="array.length">
                <u-swipe-action autoClose>
                    <u-swipe-action-item v-for="(item, index) in array" :key="item.id" :options="options1" @click="delItem(item.id)">
                        <view class="nl-it" :class="{ first: index == 0 }" hover-class="nl-hover" @click="toDe(item.id)">
                            <image v-if="thumb(item)" class="nl-th" :src="thumb(item)" mode="aspectFill"></image>
                            <view v-else class="nl-th nl-th-t"><image class="nl-th-ic" src="../../../static/icons/messageA.png" mode="aspectFit"></image></view>
                            <view class="nl-bd">
                                <view class="nl-t">{{ item.title }}</view>
                                <view class="nl-p" v-if="lead(item)">{{ lead(item) }}</view>
                                <view class="nl-m">{{ item.receiver == 1 ? '全員' : '絞り込み' }} ・ {{ when(item.order_time) }}</view>
                            </view>
                            <view class="nl-st" :class="item.is_send == 1 ? 'done' : 'rsv'">{{ item.is_send == 1 ? '配信済' : '予約' }}</view>
                        </view>
                    </u-swipe-action-item>
                </u-swipe-action>
            </view>
            <view class="nl-empty" v-else>まだ配信したメッセージはありません</view>
            <view class="nl-hint" v-if="array.length">左にスライドすると削除できます</view>
        </view>
        <view class="nl-foot">
            <view class="nl-btn" hover-class="nl-hover" @click="toAddnew">＋ メッセージ作成</view>
        </view>
        <!-- [カスタムエイド 2026-10-21] 削除の確認を、ほかの画面と同じダイアログに -->
        <cs-dialog :show="show" title="メッセージを削除" :message="'このメッセージを削除しますか?\n削除すると元に戻せません。'" ok-text="削除する" ok-color="red"
            @ok="confirm('cancel')" @cancel="show = false"></cs-dialog>
    </view>
</template>

<script>
    import {
        getTimingList,
        deleteTiming
    } from "@/api/index.js"
    import csDialog from "@/components/csDialog/csDialog.vue"
    export default {
        data() {
            return {
                array: [],
                count:0,
                tanum:0,
                options1: [{
                    text: '削除',
                    style: {
                        backgroundColor: '#D93025'
                    }
                }],
                show: false,
                id: "",
                config: {}, // [カスタムエイド 2026-10-21]
            }
        },
        components: {
            csDialog
        },
        created() {

        },
        onShow() {
            this.getList()
        },
        methods: {
            // [カスタムエイド 2026-10-21] 一覧の表示用
            num(v) {
                return String(Number(v) || 0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
            },
            thumb(item) {
                if (item.content_type == 2 && item.content) return item.content
                if (item.content_type == 3 && item.images && item.images.length) return item.images[0]
                return ''
            },
            lead(item) {
                if (item.content_type == 2) return '写真'
                if (item.content_type == 3) {
                    const t = (item.content || '').replace(/\s+/g, ' ').trim()
                    const n = (item.images || []).length
                    return t || (n ? '写真 ' + n + '枚' : '')
                }
                return (item.content || '').replace(/\s+/g, ' ').trim()
            },
            when(t) {
                // 「2026-09-30 21:24」→「2026/09/30 21:24」。今年なら年を省く
                if (!t) return ''
                const y = String(new Date().getFullYear())
                const s = String(t).replace(/-/g, '/')
                return s.indexOf(y + '/') == 0 ? s.slice(5) : s
            },
            getList() {
                let that = this
                getTimingList().then((res) => {
                    if (res.code == 200) {
                        that.array = res.data.messages
                        that.count = res.data.count
                        that.tanum = res.data.total_count
                    }
                })
            },
            toAddnew() {
                uni.navigateTo({
                    url: "edit/edit?source=add"
                })
            },
            toDe(e) {
                uni.navigateTo({
                    url: "edit/edit?source=edit&id=" + e
                })
            },

            confirm(e) {
                let that = this
                if (e == "cancel") {
                    deleteTiming(that.id).then((res) => {
                        if (res.code == 200) {
                            uni.showToast({
                                title: res.message
                            })
                            that.getList()
                            that.show = false
                        } else {
                            uni.showToast({
                                title: res.message,
                                icon: "none"
                            })
                        }
                    })
                } else {
                    that.show = false
                }
            },

            delItem(id) {
                let that = this
                that.show = true
                that.id = id
                that.config = {
                    type: "button",
                    cancel: true,
                    default: true,
                    title: "メッセージを削除",
                    content: "このメッセージを削除しますか？",
                    cancelText: "削除",
                    defaultText: "キャンセル"
                }

            },
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-21] メッセージ一覧(余白はほかの画面と同じ決まり) */
    page { background: #F2F2F7; }
    .nl-pg { padding: 40rpx 32rpx calc(220rpx + env(safe-area-inset-bottom)); }
    .nl-sum { display: flex; align-items: center; justify-content: space-between; background: #FFFFFF; border-radius: 32rpx; padding: 32rpx; margin-bottom: 48rpx; }
    .nl-sum-k { font-size: 26rpx; color: #6E6E73; line-height: 1.2; }
    .nl-bar { width: 300rpx; height: 12rpx; border-radius: 6rpx; background: #E5E5EA; margin-top: 16rpx; overflow: hidden; }
    .nl-bar-in { height: 100%; background: #34C759; border-radius: 6rpx; }
    .nl-sum-v { font-size: 40rpx; font-family: 'Hiragino Sans-W5', sans-serif; color: #1D1D1F; }
    .nl-sum-t { font-size: 26rpx; color: #6E6E73; font-family: 'Hiragino Sans', sans-serif; }
    .nl-sec { font-size: 26rpx; color: #6E6E73; font-family: 'Hiragino Sans-W5', sans-serif; margin: 0 8rpx 16rpx; line-height: 1.2; }
    .nl-list { background: #FFFFFF; border-radius: 32rpx; overflow: hidden; }
    .nl-it { display: flex; align-items: center; padding: 28rpx 32rpx; background: #FFFFFF; position: relative; }
    .nl-it:not(.first)::before { content: ''; position: absolute; left: 32rpx; right: 0; top: 0; height: 1px; background: #E5E5EA; }
    .nl-hover { opacity: 0.7; }
    .nl-th { width: 112rpx; height: 112rpx; border-radius: 20rpx; flex-shrink: 0; margin-right: 24rpx; background: #F2F2F7; }
    .nl-th-t { background: #E8F0FE; display: flex; align-items: center; justify-content: center; }
    .nl-th-ic { width: 52rpx; height: 52rpx; }
    .nl-bd { flex: 1; min-width: 0; }
    .nl-t { font-size: 30rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.35; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .nl-p { font-size: 26rpx; color: #515154; line-height: 1.4; margin-top: 6rpx; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .nl-m { font-size: 24rpx; color: #8A8A8E; line-height: 1.4; margin-top: 8rpx; }
    .nl-st { flex-shrink: 0; align-self: flex-start; margin-left: 16rpx; font-size: 24rpx; line-height: 1.2; padding: 8rpx 20rpx; border-radius: 999rpx; }
    .nl-st.done { background: #F2F2F7; color: #6E6E73; }
    .nl-st.rsv { background: #E8F0FE; color: #1A73E8; }
    .nl-empty { background: #FFFFFF; border-radius: 32rpx; padding: 64rpx 32rpx; text-align: center; font-size: 28rpx; color: #8A8A8E; }
    .nl-hint { font-size: 24rpx; color: #8A8A8E; margin: 20rpx 8rpx 0; }
    /* 下に固定のボタン。後ろは少し透けて一覧が見える */
    .nl-foot { position: fixed; left: 0; right: 0; bottom: 0; z-index: 99; padding: 32rpx 32rpx calc(32rpx + env(safe-area-inset-bottom));
        background: rgba(242, 242, 247, 0.78); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-top: 1px solid rgba(0, 0, 0, 0.06); }
    .nl-btn { height: 104rpx; border-radius: 28rpx; background: #34C759; color: #FFFFFF; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif;
        display: flex; align-items: center; justify-content: center; }
</style>
