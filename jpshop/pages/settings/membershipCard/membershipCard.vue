<template>
    <!-- [カスタムエイド 2026-10-20] メンバーズカード一覧を作り直し(モック「管理App メンバーズカード作成 改善モック」の一覧)
         ・スタンプA・スタンプB・ポイントを1つの一覧に(種類ごとにメニューを切り替えなくてよい)。上のボタンで絞り込み
         ・「最初に渡すカード」(お客様が初めて登録したときに自動で持つカード。今までの丸いチェック)を青い枠と印で表示
         ・編集・削除はボタンで(横にスワイプする形はやめた)。「カードを作る」は ①種類を選ぶ画面へ -->
    <view class="ml">
        <u-navbar title="メンバーズカード" :border="true" @leftClick="goback" :placeholder="true" bgColor="#FBFBFD"></u-navbar>

        <view class="ml-body">
            <view class="ml-create" hover-class="ml-btn-hover" @click="addNew">
                <text class="ml-plus">+</text>カードを作る
            </view>

            <scroll-view class="ml-chips" scroll-x>
                <view class="ml-chip" v-for="c in chips" :key="c.key" :class="{ on: filter == c.key }" @click="filter = c.key">
                    {{c.label}}({{c.count}})
                </view>
            </scroll-view>

            <view class="ml-info">
                <u-icon name="info-circle" color="#1A73E8" size="16"></u-icon>
                <text>「最初に渡すカード」は、お客様がアプリに初めて登録したときに自動で持つカードです。1枚だけ選べます。</text>
            </view>

            <view class="ml-empty" v-if="loaded && !shown.length">
                {{filter == 'all' ? '登録されたカードはありません' : 'この種類のカードはまだありません'}}
            </view>

            <view class="ml-card" v-for="item in shown" :key="item.id" :class="{ def: item.default }">
                <view class="ml-top" @click="toEdit(item)">
                    <image class="ml-thumb" :src="item.image" mode="aspectFill"></image>
                    <view class="ml-text">
                        <view class="ml-tags">
                            <text class="ml-tag" :class="'t' + item.cardType">{{typeName(item.cardType)}}</text>
                            <text class="ml-tag ml-tag-def" v-if="item.default">最初に渡すカード</text>
                        </view>
                        <text class="ml-name">{{item.name}}</text>
                        <text class="ml-meta">{{meta(item)}}</text>
                        <view class="ml-frame" v-if="item.cardType != 3 && (item.background_color || item.frame_image)">
                            <text>枠</text>
                            <view class="ml-frame-chip" :style="frameChip(item)"></view>
                        </view>
                    </view>
                </view>
                <view class="ml-actions">
                    <view class="ml-act" hover-class="ml-hover" @click="toEdit(item)">
                        <u-icon name="edit-pen" size="16" color="#1D1D1F"></u-icon>
                        <text>編集</text>
                    </view>
                    <view class="ml-act ml-act-blue" v-if="!item.default" hover-class="ml-hover" @click="chooseItem(item)">
                        最初に渡すカードにする
                    </view>
                    <view class="ml-act ml-act-del" hover-class="ml-hover" @click="deleteItems(item)">
                        <u-icon name="trash" size="18" color="#C5221F"></u-icon>
                    </view>
                </view>
            </view>
        </view>

        <mod v-if="showModule" @getStatus="getStatus" :config="configs"></mod>
        <mod v-if="showModule1" @getStatus="getStatus1" :config="configs1"></mod>
    </view>
</template>

<script>
    import {
        getCardList,
        setDefaultCard,
        deleteMembershipCard
    } from '@/api/index.js'
    import mod from "@/components/mod.vue"
    export default {
        components: {
            mod
        },
        data() {
            return {
                cards: [],
                loaded: false,
                filter: 'all',
                deleteItem: {},
                deleteitem: {},
                showModule: false,
                configs: {},
                configs1: {},
                showModule1: false
            }
        },
        computed: {
            chips() {
                const n = (t) => this.cards.filter((c) => c.cardType == t).length
                return [
                    { key: 'all', label: 'すべて', count: this.cards.length },
                    { key: 1, label: 'スタンプA', count: n(1) },
                    { key: 2, label: 'スタンプB', count: n(2) },
                    { key: 3, label: 'ポイント', count: n(3) }
                ]
            },
            shown() {
                return this.filter == 'all' ? this.cards : this.cards.filter((c) => c.cardType == this.filter)
            }
        },
        onShow() {
            this.getList()
        },
        methods: {
            goback() {
                uni.switchTab({
                    url: "../../setting/setting"
                })
            },
            typeName(t) {
                return t == 1 ? 'スタンプカードA' : t == 2 ? 'スタンプカードB' : 'ポイントカード'
            },
            meta(item) {
                const a = []
                if (item.cardType == 3) {
                    a.push(item.is_point == 1 ? item.amount + '円で' + item.point + 'ポイント' : 'ポイントなし')
                } else {
                    a.push('スタンプ' + item.count + '個')
                }
                if (item.valid_type == 1) {
                    a.push('有効期限 ' + (item.valid_day > 300 ? '発行から' + (item.valid_day / 365) + '年' : '発行から' + item.valid_day + '日'))
                } else if (item.valid_type == 2) {
                    a.push('有効期限 ' + (item.expire_time || 'なし'))
                } else {
                    a.push('有効期限なし')
                }
                return a.join('・')
            },
            frameChip(item) {
                if (item.frame_image) {
                    return 'background-image: url(' + item.frame_image + '); background-size: cover; background-position: center;'
                }
                return 'background: ' + item.background_color + ';'
            },
            // 3種類の一覧を同時に読み込んで、1つにまとめる(サーバーの変更なし)
            getList() {
                Promise.all([1, 2, 3].map((t) => getCardList(t).then((res) => {
                    if (res.code != 200) return []
                    return (res.data.cards || []).map((c) => Object.assign({}, c, { cardType: t }))
                }).catch(() => []))).then((lists) => {
                    const all = [].concat(lists[0], lists[1], lists[2])
                    // 最初に渡すカードを先頭に
                    all.sort((a, b) => (b.default ? 1 : 0) - (a.default ? 1 : 0))
                    this.cards = all
                    this.loaded = true
                })
            },
            toEdit(item) {
                const shop_name = encodeURIComponent(item.shop_name || '')
                uni.navigateTo({
                    url: "/pages/settings/membershipCard/editmembershipcard/editmembershipcard?id=" + item.id + "&name=" + shop_name +
                        "&image=" + encodeURIComponent(item.image || '') + "&type=" + item.cardType
                })
            },
            addNew() {
                uni.navigateTo({
                    url: "/pages/settings/membershipCard/typeSelect/typeSelect"
                })
            },
            chooseItem(item) {
                this.deleteItem = item
                this.configs = {
                    title: item.name,
                    content: 'を「最初に渡すカード」にしますか?',
                    confirm: true,
                    cancel: true,
                    confirmText: "変更する",
                    cancelText: "キャンセル"
                }
                this.showModule = true
            },
            getStatus(e) {
                this.showModule = false
                if (e != "confirm") return
                setDefaultCard(this.deleteItem.id).then((res) => {
                    uni.showToast({
                        title: res.message,
                        icon: res.code == 200 ? 'success' : 'none'
                    })
                    this.getList()
                })
            },
            deleteItems(e) {
                this.deleteitem = e
                this.configs1 = {
                    title: 'このカードを削除しますか?',
                    content: '削除すると、このカードの情報は全て削除されます。',
                    cancel: true,
                    confirm: true,
                    cancelText: "削除",
                    confirmText: "キャンセル",
                    type: "revers"
                }
                this.showModule1 = true
            },
            getStatus1(e) {
                this.showModule1 = false
                if (e != 'cancel') return
                deleteMembershipCard(this.deleteitem.id).then((res) => {
                    uni.showToast({
                        title: res.message,
                        icon: res.code == 200 ? 'success' : 'none'
                    })
                    if (res.code == 200) this.getList()
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .ml {
        min-height: 100vh;
        background: #F5F5F7;
        font-family: 'Hiragino Sans', sans-serif;
        color: #1D1D1F;
    }

    .ml-body {
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
    }

    .ml-create {
        height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        font-size: 32rpx;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 32rpx;
    }

    .ml-plus {
        font-size: 44rpx;
        line-height: 1;
        margin-right: 12rpx;
        position: relative;
        top: -2rpx;
    }

    .ml-btn-hover {
        opacity: 0.8;
    }

    .ml-chips {
        white-space: nowrap;
        margin-bottom: 24rpx;
    }

    .ml-chip {
        display: inline-block;
        height: 72rpx;
        line-height: 70rpx;
        padding: 0 28rpx;
        margin-right: 16rpx;
        border-radius: 36rpx;
        border: 1px solid #D2D2D7;
        background: #FFFFFF;
        font-size: 28rpx;
        font-weight: 500;
        box-sizing: border-box;
    }

    .ml-chip.on {
        background: #1D1D1F;
        border-color: #1D1D1F;
        color: #FFFFFF;
        font-weight: 700;
    }

    .ml-info {
        display: flex;
        align-items: flex-start;
        padding: 20rpx 24rpx;
        border-radius: 24rpx;
        background: #EEF4FF;
        font-size: 24rpx;
        line-height: 1.6;
        margin-bottom: 32rpx;

        text {
            flex: 1;
            margin-left: 12rpx;
        }
    }

    .ml-empty {
        padding: 80rpx 32rpx;
        text-align: center;
        font-size: 28rpx;
        color: #6E6E73;
        background: #FFFFFF;
        border-radius: 32rpx;
    }

    .ml-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 28rpx;
        margin-bottom: 24rpx;
    }

    .ml-card.def {
        box-shadow: 0 0 0 4rpx #1A73E8;
    }

    .ml-top {
        display: flex;
        align-items: flex-start;
    }

    .ml-thumb {
        width: 192rpx;
        height: 116rpx;
        flex-shrink: 0;
        border-radius: 16rpx;
        background: #E5E5EA;
        margin-right: 24rpx;
    }

    .ml-text {
        flex: 1;
        min-width: 0;
    }

    .ml-tags {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 8rpx;
    }

    .ml-tag {
        font-size: 22rpx;
        font-weight: 700;
        padding: 4rpx 16rpx;
        border-radius: 18rpx;
        margin: 0 12rpx 8rpx 0;
    }

    .ml-tag.t1 {
        background: #E6F4EA;
        color: #137333;
    }

    .ml-tag.t2 {
        background: #E8F0FE;
        color: #174EA6;
    }

    .ml-tag.t3 {
        background: #FEF3E0;
        color: #A34A00;
    }

    .ml-tag-def {
        background: #1A73E8;
        color: #FFFFFF;
    }

    .ml-name {
        display: block;
        font-size: 30rpx;
        font-weight: 700;
        line-height: 1.4;
        margin-bottom: 8rpx;
        word-break: break-all;
    }

    .ml-meta {
        display: block;
        font-size: 24rpx;
        color: #515154;
        line-height: 1.6;
    }

    .ml-frame {
        display: flex;
        align-items: center;
        font-size: 24rpx;
        color: #515154;
        margin-top: 8rpx;
    }

    .ml-frame-chip {
        width: 80rpx;
        height: 28rpx;
        border-radius: 8rpx;
        margin-left: 12rpx;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.08);
    }

    .ml-actions {
        display: flex;
        border-top: 1px solid #E5E5EA;
        margin-top: 24rpx;
        padding-top: 20rpx;
    }

    .ml-act {
        flex: 1;
        height: 76rpx;
        border: 1px solid #D2D2D7;
        border-radius: 20rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28rpx;
        font-weight: 500;
        margin-right: 16rpx;
        box-sizing: border-box;

        text {
            margin-left: 8rpx;
        }
    }

    .ml-act-blue {
        flex: 1.5;
        border-color: #1A73E8;
        color: #1A73E8;
        font-size: 26rpx;
    }

    .ml-act-del {
        flex: none;
        width: 88rpx;
        margin-right: 0;
    }

    .ml-hover {
        opacity: 0.6;
    }
</style>
