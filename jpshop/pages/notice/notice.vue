<template>
    <!-- [カスタムエイド 2026-10-22] お知らせ一覧を作り直し
         ・お知らせを投稿するとき:カードを押すと編集、左のチェックは複数選べてまとめて削除。下に固定の「＋ お知らせ投稿」(右上の＋はやめた)
         ・Instagram と連携中:上に小さく連携中のアカウント、その下に Card-San App と同じ Instagram の投稿(見るだけ。編集・削除はできない)
           「＋ お知らせ投稿」は出さない -->
    <view class="nl">
        <u-navbar title="お知らせ一覧" :placeholder="true" :border="true" :safeAreaInsetTop="true" bgColor="#FBFBFD">
            <view class="nl-x" slot="left" @click="toIndex">×</view>
            <view slot="right" class="nl-set" hover-class="nl-hover" @click="toSetting">
                <image src="../../static/icons/settingnotice.svg" class="nl-set-ic" mode="aspectFit"></image>
            </view>
        </u-navbar>

        <!-- Instagram と連携中 -->
        <view class="nl-pg" v-if="isIns">
            <view class="nl-acc" hover-class="nl-hover" @click="toSetting">
                <image class="nl-acc-av" :src="avatar || '../../static/images/avatar.png'" mode="aspectFill"></image>
                <view class="nl-acc-b">
                    <view class="nl-acc-n">@{{ nickname }}</view>
                    <view class="nl-acc-s"><text class="nl-dot"></text>Instagram と連携中</view>
                </view>
                <view class="nl-acc-go">設定</view>
            </view>
            <view class="nl-note">お客様の「お知らせ」には、この Instagram の投稿が出ます(Card-San App と同じ内容)。投稿・編集・削除は Instagram で行います。</view>

            <view class="nl-ins" v-for="(item, index) in insList" :key="item.id || index" @click="openIns(item.permalink)">
                <image v-if="item.media_type == 'IMAGE'" class="nl-ins-img" :src="item.media_url" mode="aspectFill"></image>
                <view v-if="item.media_type == 'VIDEO'" class="nl-ins-img nl-ins-video">
                    <image v-if="item.thumbnail_url" class="nl-ins-img" :src="item.thumbnail_url" mode="aspectFill"></image>
                    <view class="nl-play">▶</view>
                </view>
                <swiper v-if="item.media_type == 'CAROUSEL_ALBUM'" class="nl-ins-img" :indicator-dots="true" indicator-active-color="#1A73E8">
                    <swiper-item v-for="(u, ui) in (item.carousel || [])" :key="ui"><image class="nl-ins-img" :src="u" mode="aspectFill"></image></swiper-item>
                </swiper>
                <view class="nl-ins-b">
                    <view class="nl-ins-who">
                        <image class="nl-ins-av" :src="item.avatar || avatar" mode="aspectFill"></image>
                        <text class="nl-ins-n">{{ item.username || nickname }}</text>
                    </view>
                    <view class="nl-ins-d">{{ item.datetime }}</view>
                    <view class="nl-ins-c" v-if="item.caption">
                        <text>{{ (item.open || item.caption.length <= 85) ? item.caption : (item.caption.slice(0, 85) + '…') }}</text>
                        <text v-if="item.caption.length > 85 && !item.open" class="nl-more" @click.stop="$set(item, 'open', true)">続きを読む</text>
                    </view>
                    <view class="nl-ins-f">Synced from Instagram</view>
                </view>
            </view>
            <view class="nl-empty" v-if="insLoaded && !insList.length">{{ insError || 'Instagram の投稿はまだありません' }}</view>
            <view class="nl-more-l" v-if="insLoading">読み込んでいます…</view>
        </view>

        <!-- お知らせを投稿する -->
        <view class="nl-pg" v-else>
            <view class="nl-row" v-for="item in array" :key="item.id">
                <view class="nl-ckw" @click="toggle(item.id)">
                    <view class="nl-ck" :class="{ on: picked.indexOf(item.id) > -1 }"><view class="nl-ck-m" v-if="picked.indexOf(item.id) > -1"></view></view>
                </view>
                <view class="nl-card" :class="{ pick: picked.indexOf(item.id) > -1 }" hover-class="nl-hover" @click="toEditItem(item)">
                    <image v-if="item.images && item.images.length" class="nl-img" :src="item.images[0]" mode="aspectFill"></image>
                    <view class="nl-b">
                        <view class="nl-top">
                            <text class="nl-date">{{ item.update_time }}<text v-if="item.edit_time" class="nl-edited">({{ item.edit_time }} 編集)</text></text>
                            <text class="nl-pin" v-if="item.is_top">固定</text>
                        </view>
                        <view class="nl-t">{{ item.title }}</view>
                        <view class="nl-c" v-if="item.content">{{ item.content }}</view>
                    </view>
                </view>
            </view>
            <view class="nl-empty" v-if="loaded && !array.length">まだお知らせはありません</view>
            <view class="nl-hint" v-if="array.length">カードを押すと編集できます。左のチェックで、まとめて削除もできます。</view>
        </view>

        <view class="nl-foot" v-if="!isIns && loaded">
            <view v-if="picked.length" class="nl-foot-2">
                <view class="nl-btn nl-btn-g" hover-class="nl-hover" @click="picked = []">選ぶのをやめる</view>
                <view class="nl-btn nl-btn-del" hover-class="nl-hover" @click="showDel = true">選んだ{{ picked.length }}件を削除</view>
            </view>
            <view v-else class="nl-btn" hover-class="nl-hover" @click="toEdit">＋ お知らせ投稿</view>
        </view>

        <cs-dialog :show="showDel" title="お知らせを削除" :message="'選んだ' + picked.length + '件のお知らせを削除しますか?\n削除すると元に戻せません。'"
            ok-text="削除する" ok-color="red" @ok="delPicked" @cancel="showDel = false"></cs-dialog>
        <permission :show="showpermission" @close="showpermission = false"></permission>
    </view>
</template>

<script>
    import { getNoticeList, deleteNotice, getShopInfo, getAdminDetail, getShopInsMedia } from '@/api/index.js'
    import permission from "@/components/permission/permission.vue"
    import csDialog from "@/components/csDialog/csDialog.vue"
    export default {
        components: { permission, csDialog },
        data() {
            return {
                showpermission: false,
                showDel: false,
                loaded: false,
                array: [],
                picked: [],
                isIns: false,
                avatar: "",
                nickname: "",
                insList: [],
                insAfter: '',
                insLoaded: false,
                insLoading: false,
                insEnd: false,
                insError: ''
            }
        },
        onShow() {
            this.getInfo()
        },
        onReachBottom() {
            if (this.isIns) this.getIns()
        },
        methods: {
            getInfo() {
                getShopInfo().then((res) => {
                    if (res.code == 200) {
                        const s = res.data.shop_info
                        this.isIns = s.notice_type == 2
                        this.avatar = s.instagram_avatar
                        this.nickname = s.instagram_username
                        if (this.isIns) {
                            this.insList = []
                            this.insAfter = ''
                            this.insEnd = false
                            this.getIns()
                        } else {
                            this.getList()
                        }
                    }
                }).catch(() => { this.getList() })
            },
            getList() {
                getNoticeList().then((res) => {
                    this.loaded = true
                    if (res.code == 200) {
                        this.array = res.data.notices || []
                        const ids = this.array.map((v) => v.id)
                        this.picked = this.picked.filter((id) => ids.indexOf(id) > -1)
                    }
                }).catch(() => { this.loaded = true })
            },
            // Instagram の投稿(下までスクロールすると続きを読む)
            getIns() {
                if (this.insLoading || this.insEnd) return
                this.insLoading = true
                getShopInsMedia(this.insAfter).then((res) => {
                    this.insLoading = false
                    this.insLoaded = true
                    if (res.code == 200) {
                        const m = res.data.medias || []
                        this.insList = this.insList.concat(m)
                        this.insAfter = res.data.after || ''
                        if (!this.insAfter || !m.length) this.insEnd = true
                    } else {
                        this.insEnd = true
                        this.insError = res.message || 'Instagram の投稿を読み込めませんでした'
                    }
                }).catch(() => {
                    this.insLoading = false
                    this.insLoaded = true
                    this.insEnd = true
                    this.insError = 'Instagram の投稿を読み込めませんでした'
                })
            },
            openIns(link) {
                if (!link) return
                uni.navigateTo({ url: "../webview/webview?link=" + encodeURIComponent(link) })
            },
            toggle(id) {
                const i = this.picked.indexOf(id)
                if (i > -1) this.picked.splice(i, 1)
                else this.picked.push(id)
            },
            // 投稿の権限(副管理者は権限 6 が必要)
            can(go) {
                const admin = uni.getStorageSync("admin")
                if (admin.admin_type != 2) return go()
                uni.showLoading({ title: "読み込み中", mask: true })
                getAdminDetail(admin.id).then((res) => {
                    uni.hideLoading()
                    if (res.data.admin.privileges.includes(6)) go()
                    else this.showpermission = true
                }).catch(() => { uni.hideLoading() })
            },
            toEdit() {
                this.can(() => uni.navigateTo({ url: "../notice/edit/edit?source=add" }))
            },
            toEditItem(item) {
                this.can(() => uni.navigateTo({ url: "edit/edit?source=edit&params=" + encodeURIComponent(JSON.stringify(item)) }))
            },
            async delPicked() {
                this.showDel = false
                const ids = this.picked.slice()
                uni.showLoading({ title: '削除しています', mask: true })
                let ng = 0
                for (const id of ids) {
                    try {
                        const res = await deleteNotice(id)
                        if (res.code != 200) ng++
                    } catch (e) { ng++ }
                }
                uni.hideLoading()
                this.picked = []
                uni.showToast({ title: ng ? (ng + '件は削除できませんでした') : '削除しました', icon: ng ? 'none' : 'success' })
                this.getList()
            },
            toSetting() {
                uni.navigateTo({ url: "../notice/setting/setting" })
            },
            toIndex() {
                uni.switchTab({ url: "../index/index" })
            }
        }
    }
</script>

<style lang="scss">
    page { background: #F2F2F7; }
    .nl-x { width: 64rpx; height: 64rpx; border-radius: 50%; background: #E5E5EA; color: #6E6E73; font-size: 34rpx; display: flex; align-items: center; justify-content: center; }
    .nl-set { width: 64rpx; height: 64rpx; border-radius: 50%; background: #E5E5EA; display: flex; align-items: center; justify-content: center; }
    .nl-set-ic { width: 40rpx; height: 40rpx; }
    .nl-pg { padding: 40rpx 32rpx calc(220rpx + env(safe-area-inset-bottom)); }
    .nl-hover { opacity: 0.7; }
    .nl-acc { display: flex; align-items: center; background: #FFFFFF; border-radius: 28rpx; padding: 20rpx 24rpx; }
    .nl-acc-av { width: 72rpx; height: 72rpx; border-radius: 50%; margin-right: 20rpx; background: #E5E5EA; }
    .nl-acc-b { flex: 1; min-width: 0; }
    .nl-acc-n { font-size: 28rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .nl-acc-s { font-size: 22rpx; color: #188038; margin-top: 4rpx; display: flex; align-items: center; }
    .nl-dot { width: 12rpx; height: 12rpx; border-radius: 50%; background: #34C759; margin-right: 8rpx; }
    .nl-acc-go { font-size: 24rpx; color: #1A73E8; padding: 8rpx 0 8rpx 16rpx; }
    .nl-note { font-size: 24rpx; color: #8A8A8E; line-height: 1.6; margin: 16rpx 8rpx 32rpx; }
    .nl-ins { background: #FFFFFF; border-radius: 32rpx; overflow: hidden; margin-bottom: 32rpx; box-shadow: 0 2rpx 6rpx rgba(0, 0, 0, 0.06); }
    .nl-ins-img { width: 100%; height: 640rpx; display: block; }
    .nl-ins-video { position: relative; background: #1D1D1F; }
    .nl-play { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 96rpx; height: 96rpx; border-radius: 50%; background: rgba(0, 0, 0, 0.5); color: #FFFFFF; font-size: 40rpx; display: flex; align-items: center; justify-content: center; }
    .nl-ins-b { padding: 28rpx 28rpx 24rpx; }
    .nl-ins-who { display: flex; align-items: center; }
    .nl-ins-av { width: 64rpx; height: 64rpx; border-radius: 50%; margin-right: 16rpx; background: #E5E5EA; }
    .nl-ins-n { font-size: 28rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; }
    .nl-ins-d { font-size: 22rpx; color: #8A8A8E; margin-top: 12rpx; }
    .nl-ins-c { font-size: 28rpx; color: #1D1D1F; line-height: 1.7; white-space: pre-wrap; margin-top: 12rpx; word-break: break-word; }
    .nl-more { color: #1A73E8; margin-left: 8rpx; }
    .nl-ins-f { font-size: 22rpx; color: #8A8A8E; text-align: right; margin-top: 16rpx; }
    .nl-more-l { text-align: center; color: #8A8A8E; font-size: 24rpx; padding: 24rpx 0; }
    .nl-row { display: flex; align-items: flex-start; margin-bottom: 32rpx; }
    .nl-ckw { padding: 24rpx 20rpx 24rpx 0; flex-shrink: 0; }
    /* 副管理者の権限の画面と同じチェック */
    .nl-ck { width: 44rpx; height: 44rpx; border-radius: 12rpx; border: 3rpx solid #AEAEB2; box-sizing: border-box; background: #FFFFFF; display: flex; align-items: center; justify-content: center; }
    .nl-ck.on { border: 0; background: #1A73E8; }
    .nl-ck-m { width: 12rpx; height: 22rpx; border-right: 5rpx solid #FFFFFF; border-bottom: 5rpx solid #FFFFFF; transform: rotate(45deg); margin-top: -6rpx; }
    .nl-card { flex: 1; min-width: 0; background: #FFFFFF; border-radius: 32rpx; overflow: hidden; box-shadow: 0 2rpx 6rpx rgba(0, 0, 0, 0.06); border: 4rpx solid transparent; }
    .nl-card.pick { border-color: #1A73E8; }
    .nl-img { width: 100%; height: 360rpx; display: block; }
    .nl-b { padding: 28rpx 28rpx 32rpx; }
    .nl-top { display: flex; align-items: center; justify-content: space-between; }
    .nl-date { font-size: 24rpx; color: #8A8A8E; }
    .nl-edited { margin-left: 10rpx; }
    .nl-pin { font-size: 22rpx; color: #FFFFFF; background: #1A73E8; border-radius: 999rpx; padding: 4rpx 18rpx; font-family: 'Hiragino Sans-W5', sans-serif; }
    .nl-t { font-size: 32rpx; color: #1D1D1F; font-family: 'Hiragino Sans-W5', sans-serif; line-height: 1.4; margin-top: 12rpx; overflow-wrap: anywhere; }
    .nl-c { font-size: 26rpx; color: #515154; line-height: 1.6; margin-top: 8rpx; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; white-space: pre-wrap; }
    .nl-empty { text-align: center; color: #8A8A8E; font-size: 28rpx; padding: 120rpx 0; }
    .nl-hint { font-size: 24rpx; color: #8A8A8E; line-height: 1.6; margin: 8rpx 8rpx 0; }
    .nl-foot { position: fixed; left: 0; right: 0; bottom: 0; z-index: 10; padding: 24rpx 32rpx calc(24rpx + env(safe-area-inset-bottom)); background: rgba(242, 242, 247, 0.86); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-top: 1px solid rgba(0, 0, 0, 0.06); }
    .nl-foot-2 { display: flex; }
    .nl-btn { height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-family: 'Hiragino Sans-W5', sans-serif; display: flex; align-items: center; justify-content: center; flex: 1; }
    .nl-btn-g { background: #FFFFFF; color: #1D1D1F; border: 2rpx solid #D1D1D6; margin-right: 24rpx; flex: 0 0 280rpx; }
    .nl-btn-del { background: #D93025; }
</style>
