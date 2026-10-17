<template>
    <!-- [カスタムエイド 2026-10-17] スマホ・タブレット版 ご利用プラン(プラン・機能はPC版 utilizePlan.vue と同じくサーバーのデータを使う) -->
    <sp-shell mode="sub" title="ご利用プラン" back="/sp/contract" ref="shell">
        <div class="sp-body" style="padding-top: 16px; gap: 14px; padding-bottom: 24px">
            <section class="sp-card" style="padding: 14px 16px; display: flex; flex-direction: column; gap: 8px">
                <div style="display: flex; align-items: center; justify-content: space-between">
                    <span style="font-size: 13px; color: #6e6e73">ご利用中のプラン</span>
                    <span style="font-size: 15px; font-weight: 700">{{ currentName }}</span>
                </div>
                <div class="sp-blue-box" v-if="vip.is_try == 1"><b>無料お試し期間中</b><br />アカウント作成から30日間は全てのプランが無料お試し期間の対象です。顧客登録数が30人以下の場合は、最大30日無料期間が延長されます。</div>
            </section>

            <button type="button" class="sp-btn-line" style="border-radius: 10px; font-size: 14px" @click="tableOpen = true">各プランの機能一覧はこちら</button>

            <h2 style="margin: 4px 0 0; font-size: 15px; font-weight: 700">プランを選択</h2>

            <div class="sp-loading" v-if="!list.length">読み込み中…</div>

            <div v-for="(p, i) in list" :key="p.id" class="sp-plan" :class="{ on: picked == i }">
                <div class="sp-plan-strip" :style="{ background: strip[i] || '#D2D2D7' }"></div>
                <button type="button" class="sp-plan-head" :aria-pressed="picked == i ? 'true' : 'false'" @click="picked = i">
                    <span class="sp-radio" :class="{ on: picked == i }"><span></span></span>
                    <span style="flex: 1; display: flex; flex-direction: column; gap: 2px; min-width: 0">
                        <span style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap">
                            <span style="font-size: 17px; font-weight: 700; color: #1d1d1f">{{ p.name }}</span>
                            <span v-if="badge[i]" class="sp-badge" :style="{ background: badgeBg[i] }">{{ badge[i] }}</span>
                        </span>
                        <span style="font-size: 13px; color: #515154; line-height: 1.6">{{ p.des }}</span>
                    </span>
                    <span style="text-align: right; flex-shrink: 0">
                        <span style="display: block; font-size: 18px; font-weight: 700; color: #1d1d1f">{{ monthly(p) }}</span>
                        <span style="display: block; font-size: 11px; color: #6e6e73">〜/月</span>
                    </span>
                </button>

                <button type="button" class="sp-plan-detail" :aria-expanded="open[i] ? 'true' : 'false'" @click="toggleDetail(i)">
                    <span>機能詳細<span style="margin-left: 6px; font-size: 12px; color: #6e6e73">{{ feats(p).length }}機能</span></span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1a73e8" stroke-width="2.2" aria-hidden="true" :style="open[i] ? 'transform: rotate(180deg)' : ''"><path d="M6 9l6 6 6-6"></path></svg>
                </button>

                <div v-if="open[i]" style="border-top: 1px solid #f0f0f2">
                    <div v-for="f in feats(p)" :key="f.e_name" style="border-bottom: 1px solid #f0f0f2">
                        <button type="button" class="sp-feat" :aria-expanded="openFeat == p.id + ':' + f.e_name ? 'true' : 'false'" @click="toggleFeat(p.id + ':' + f.e_name)">
                            {{ f.name }}
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#707070" stroke-width="2.2" aria-hidden="true" :style="openFeat == p.id + ':' + f.e_name ? 'transform: rotate(90deg)' : ''"><path d="M9 5l7 7-7 7"></path></svg>
                        </button>
                        <div v-if="openFeat == p.id + ':' + f.e_name" style="padding: 2px 16px 14px; display: flex; gap: 12px; align-items: flex-start">
                            <span style="width: 40px; height: 40px; flex-shrink: 0; border-radius: 10px; background: #f5f5f7; display: flex; align-items: center; justify-content: center">
                                <img :src="iconOf(f)" alt="" style="width: 24px; height: 24px; display: block; object-fit: contain" />
                            </span>
                            <span style="font-size: 13px; line-height: 1.7; color: #515154">{{ f.des }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px; font-size: 11px; line-height: 1.7; color: #6e6e73">
                <p style="margin: 0">注1)クーポン、メッセージチャットの配信数は、3,000件/月を超過した場合は、3,000件毎に1,200円/月の追加料金が発生します。</p>
                <p style="margin: 0">注2)予約SMS送信数は、100件を超過した場合は、100件毎に1,200円/月の追加料金が発生します。</p>
            </div>
        </div>

        <div class="sp-plan-bar" v-if="list.length">
            <div class="sp-plan-bar-in">
                <div style="flex-shrink: 0">
                    <div style="font-size: 11px; color: #6e6e73">選択中</div>
                    <div style="font-size: 15px; font-weight: 700">{{ list[picked] ? list[picked].name : '' }}</div>
                </div>
                <button type="button" class="sp-btn" style="flex: 1; font-size: 15px" @click="next">このプランで次へ</button>
            </div>
        </div>

        <!-- 各プランの機能一覧(管理Appの「各プランの機能一覧はこちら」と同じ表) -->
        <div class="sp-mask" v-if="tableOpen" @click.self="tableOpen = false">
            <div class="sp-dialog" role="dialog" aria-labelledby="sp-tb-t" style="padding: 20px 16px 16px">
                <div class="sp-dialog-title" id="sp-tb-t" style="text-align: center">各プランの機能一覧</div>
                <table class="sp-table">
                    <thead>
                        <tr><th></th><th v-for="p in list" :key="p.id">{{ p.name }}</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="f in allFeats" :key="f.e_name">
                            <th>{{ f.name }}</th>
                            <td v-for="p in list" :key="p.id" :aria-label="has(p, f) ? 'あり' : 'なし'">{{ has(p, f) ? '○' : '×' }}</td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="sp-dbtn" @click="tableOpen = false">閉じる</button>
            </div>
        </div>
    </sp-shell>
</template>

<script>
    import SpShell from "./SpShell.vue"
    import { getPackageList, getStore } from "@/http/api.js"
    import { planName, yen } from "@/utils/spDevice.js"
    // 機能アイコン(色 #1d1d1f・大きさをそろえたもの)。名前で当てはめ、当てはまらないときはサーバーのアイコンを使う
    var ICONS = [
        [/メンバーズ/, require("@/static/sp/icons/card.svg")],
        [/店舗情報/, require("@/static/sp/icons/shop.svg")],
        [/カレンダー/, require("@/static/sp/icons/cal.svg")],
        [/クチコミ|口コミ/, require("@/static/sp/icons/review.svg")],
        [/Google/, require("@/static/sp/icons/gbp.svg")],
        [/メッセージ|チャット/, require("@/static/sp/icons/msg.svg")],
        [/お知らせ/, require("@/static/sp/icons/notice.svg")],
        [/メニュー/, require("@/static/sp/icons/menu.svg")],
        [/クーポン/, require("@/static/sp/icons/coupon.svg")],
        [/予約/, require("@/static/sp/icons/reserve.svg")]
    ]
    export default {
        components: { SpShell },
        data() {
            return {
                list: [],
                allFeats: [],
                vip: {},
                picked: 1,
                open: {},
                openFeat: "",
                tableOpen: false,
                strip: ["#D2D2D7", "#71D3D1", "#E6BF18"],
                badge: ["", "おすすめ", "予約機能付"],
                badgeBg: ["", "#1a73e8", "#0b7a53"]
            }
        },
        computed: {
            currentName() { return this.vip && this.vip.type * 1 > 0 ? planName(this.vip.type) : 'なし' }
        },
        created() {
            getStore().then((res) => {
                if (res && res.code == 200) {
                    this.vip = res.data.shop_info.vip || {}
                    if (this.vip.type * 1 > 0) this.picked = this.vip.type * 1 - 1
                }
            }).catch(() => {})
            getPackageList().then((res) => {
                if (res && res.code == 200) {
                    var fn = res.data.vip_function || {}
                    var all = []
                    for (var k in fn) all.push(Object.assign({}, fn[k], { e_name: k }))
                    this.allFeats = all
                    this.list = res.data.vip_contract || []
                    // 機能詳細は最初はすべて閉じておく(運営の指示 2026-10-17)
                } else {
                    this.$refs.shell.toast((res && res.message) || "読み込みに失敗しました", true)
                }
            }).catch(() => {
                this.$refs.shell.toast("読み込みに失敗しました。再読み込みしてください。", true)
            })
        },
        methods: {
            monthly(p) { return p.price && p.price[2] ? yen(p.price[2] / 12) : '' },
            has(p, f) { return (p.functions || []).indexOf(f.e_name) != -1 },
            feats(p) { return this.allFeats.filter(f => this.has(p, f)) },
            iconOf(f) {
                for (var i = 0; i < ICONS.length; i++) if (ICONS[i][0].test(f.name || '')) return ICONS[i][1]
                return f.icon
            },
            toggleDetail(i) {
                this.$set(this.open, i, !this.open[i])
                this.openFeat = ""
            },
            toggleFeat(key) { this.openFeat = this.openFeat == key ? "" : key },
            next() { this.$router.push({ path: "/sp/confirm", query: { index: this.picked } }) }
        }
    }
</script>

<style>
    .sp-plan { width: 100%; border-radius: 14px; background: #fff; background-clip: padding-box; overflow: hidden; border: 2px solid transparent; }
    .sp-plan.on { border-color: #1a73e8; box-shadow: 0 2px 10px rgba(26,115,232,0.18); }
    .sp-plan-strip { height: 10px; }
    .sp-plan-head { width: 100%; padding: 16px; border: 0; background: transparent; display: flex; align-items: center; gap: 12px; text-align: left; }
    .sp-badge { padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 700; color: #fff; }
    .sp-plan-detail { width: 100%; min-height: 46px; padding: 0 16px; border: 0; border-top: 1px solid #f0f0f2; background: transparent; display: flex; align-items: center; justify-content: space-between; font-size: 14px; font-weight: 500; color: #1a73e8; }
    .sp-feat { width: 100%; min-height: 46px; padding: 0 16px; border: 0; background: #fff; display: flex; align-items: center; justify-content: space-between; font-size: 14px; color: #1d1d1f; text-align: left; }
    .sp-plan-bar { position: sticky; bottom: 0; background: #fff; border-top: 1px solid #e5e5ea; padding: 12px 16px calc(16px + env(safe-area-inset-bottom)); z-index: 10; }
    .sp-plan-bar-in { max-width: 568px; margin: 0 auto; display: flex; align-items: center; gap: 12px; }
    .sp-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .sp-table th, .sp-table td { border-bottom: 1px solid #ececf0; padding: 8px 4px; text-align: center; }
    .sp-table tbody th { text-align: left; font-weight: 500; }
    .sp-table thead th { font-weight: 700; }
    .sp-table td { color: #1a73e8; font-weight: 700; font-size: 15px; }
    .sp-table td[aria-label="なし"] { color: #c7c7cc; }
</style>
