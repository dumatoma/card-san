<template>
    <!-- [カスタムエイド 2026-10-23] カルテ・写真を開いて書き足す(C1〜C6)。実機での確認を受けて作り直し:
         ・書き込みは iPhone のマークアップと同じく、右上の ✓ で保存/✕ でやめる。戻す・やり直す
         ・図形(自由線・直線・矢印・円・四角・チェック)。タップした所に置き、青い点のドラッグで大きさ・形を変える
         ・文字・図形・メモは、押さえてそのままドラッグで動かせる
         ・文字の大きさは数字(幅 1000 のときの px)
         紙の表示・拡大・書き込みの表示と指の動きは renderjs(画面の側)で動かす。位置はページに対する割合(0〜1)で保存 -->
    <view class="kv">
        <!-- 紙の表示(renderjs)。前に出たり消えたりする要素の後ろに置くと画面の作り直しで壊れるため、いちばん最初に置く -->
        <view id="kvStage" key="kvStage" class="kv-stage" :class="{ ed: edit && edit.tool != 'adjust', eda: edit && edit.tool == 'adjust' }" :style="{ top: (top + 52) + 'px' }" :prop="stage" :change:prop="kv.onProp"></view>

        <!-- 上のバー:書き込み中は ✕(やめる)・戻す・やり直す・✓(保存) -->
        <view class="kv-nav" :style="{ paddingTop: top + 'px' }">
            <!-- [カスタムエイド 2026-10-24] その場で画像編集:✕(やめる)・保存 -->
            <block v-if="edit">
                <view class="kv-btn l" hover-class="kv-hover" @click="cancelEdit">✕</view>
                <view class="kv-tt">画像編集<text class="kv-sub">{{ edit.tool == 'crop' ? '四隅・辺をドラッグして切り抜き' : edit.tool == 'adjust' ? 'スライダーで明るさを調整' : (name ? name + ' さん' : '') + (pages.length > 1 ? ' ・ ' + (idx + 1) + ' / ' + pages.length : '') }}</text></view>
                <view class="kv-sv2" :class="{ off: busy }" hover-class="kv-hover" @click="saveEdit">保存</view>
            </block>
            <block v-else-if="dirty">
                <view class="kv-btn l" hover-class="kv-hover" @click="discard">✕</view>
                <view class="kv-ur">
                    <view class="kv-urb" :class="{ off: hpos <= 0 }" @click="undo">↶</view>
                    <view class="kv-urb" :class="{ off: hpos >= hist.length - 1 }" @click="redo">↷</view>
                </view>
                <view class="kv-tt">書き込み中<text class="kv-sub">✓ で保存します</text></view>
                <view class="kv-ok" hover-class="kv-hover" @click="save">✓</view>
            </block>
            <block v-else>
                <view class="kv-btn l" hover-class="kv-hover" @click="close">✕</view>
                <view class="kv-tt">{{ title }}<text class="kv-sub">{{ name ? name + ' さん' : '' }}{{ pages.length > 1 ? ' ・ ' + (idx + 1) + ' / ' + pages.length : '' }}</text></view>
                <view class="kv-btn r" hover-class="kv-hover" @click="menuShow = true"><view class="kv-dot"></view><view class="kv-dot"></view><view class="kv-dot"></view></view>
            </block>
        </view>

        <view class="kv-chip l" :style="{ top: (top + 60) + 'px' }" v-if="pins.length && !edit" hover-class="kv-hover" @click="listShow = true">メモ {{ pins.length }}件 ・ 一覧 ›</view>
        <view class="kv-chip r" :style="{ top: (top + 60) + 'px' }" v-if="zoom > 1.02">{{ Math.round(zoom * 100) }}%</view>
        <view class="kv-hint" :style="{ top: (top + 96) + 'px' }" v-if="hint">{{ hint }}</view>
        <view class="kv-wait" v-if="page && page.status != 0">このページは取り込み中です</view>

        <!-- 下の2段目:選んでいるもの・道具の設定・ページの見本 -->
        <view class="kv-panel" v-if="!edit">
            <!-- 選んでいる文字 -->
            <view class="kv-row" v-if="selMark && selMark.type == 1">
                <view class="kv-step"><text class="kv-sb" @click="bumpSize(-1)">−</text><text class="kv-sv">{{ sizeOf(selMark.data.size) }}</text><text class="kv-sb" @click="bumpSize(1)">＋</text></view>
                <view v-for="c in colors" :key="c.key" class="kv-tl" :class="{ on: selMark.data.color == c.key }" :style="{ background: c.hex }" @click="setSel({ color: c.key })"></view>
                <text class="kv-act2" @click="editText">直す</text>
                <text class="kv-act2 red" @click="removeSel">消す</text>
            </view>
            <!-- 選んでいる図形(上の段で種類を変えられる。自由線は変えない) -->
            <block v-else-if="selMark && selMark.type == 2">
                <view class="kv-row">
                    <view v-for="s in shapes" :key="s.key" class="kv-shp" :class="{ on: (selMark.data.shape || 'free') == s.key, dim: (selMark.data.shape || 'free') == 'free' || s.key == 'free' }" @click="changeShape(s.key)"><text class="kv-shp-i">{{ s.icon }}</text><text class="kv-shp-n">{{ s.name }}</text></view>
                </view>
                <view class="kv-row sm">
                    <view v-for="c in colors" :key="c.key" class="kv-tl" :class="{ on: selMark.data.color == c.key }" :style="{ background: c.hex }" @click="setSel({ color: c.key })"></view>
                    <view class="kv-tl g" :class="{ on: selMark.data.width == 'thin' }" @click="setSel({ width: 'thin' })"><view class="kv-w1"></view></view>
                    <view class="kv-tl g" :class="{ on: selMark.data.width == 'thick' }" @click="setSel({ width: 'thick' })"><view class="kv-w2"></view></view>
                    <text class="kv-act2 red" @click="removeSel">消す</text>
                </view>
            </block>
            <!-- 選んでいるメモ -->
            <view class="kv-row" v-else-if="selMark && selMark.type == 3">
                <text class="kv-pl">メモ {{ pinNo(selMark) }}</text>
                <text class="kv-act2" @click="openThread(selMark)">開く</text>
                <text class="kv-act2 red" @click="removeSel">消す</text>
            </view>
            <!-- 図形の道具 -->
            <block v-else-if="tool == 'shape'">
                <view class="kv-row">
                    <view v-for="s in shapes" :key="s.key" class="kv-shp" :class="{ on: shape == s.key }" @click="pickShape(s.key)"><text class="kv-shp-i">{{ s.icon }}</text><text class="kv-shp-n">{{ s.name }}</text></view>
                </view>
                <view class="kv-row sm">
                    <text class="kv-pl">色</text>
                    <view v-for="c in colors" :key="c.key" class="kv-tl" :class="{ on: style.color == c.key }" :style="{ background: c.hex }" @click="style.color = c.key"></view>
                    <text class="kv-pl ml">太さ</text>
                    <view class="kv-tl g" :class="{ on: style.width == 'thin' }" @click="style.width = 'thin'"><view class="kv-w1"></view></view>
                    <view class="kv-tl g" :class="{ on: style.width == 'thick' }" @click="style.width = 'thick'"><view class="kv-w2"></view></view>
                </view>
            </block>
            <!-- 文字の道具 -->
            <view class="kv-row" v-else-if="tool == 'text'">
                <text class="kv-pl">大きさ</text>
                <view class="kv-step"><text class="kv-sb" @click="style.size = nextSize(style.size, -1)">−</text><text class="kv-sv">{{ style.size }}</text><text class="kv-sb" @click="style.size = nextSize(style.size, 1)">＋</text></view>
                <view v-for="c in colors" :key="c.key" class="kv-tl" :class="{ on: style.textColor == c.key }" :style="{ background: c.hex }" @click="style.textColor = c.key"></view>
            </view>
            <!-- ページの見本(カルテのとき) -->
            <scroll-view class="kv-thumbs" scroll-x v-else-if="kind == 1" :scroll-into-view="'th' + idx">
                <view class="kv-th-in">
                    <view v-for="(p, i) in pages" :key="p.id" :id="'th' + i" class="kv-th" :class="{ on: i == idx }" @click="go(i)">
                        <image :src="p.url" mode="aspectFill"></image>
                        <text class="kv-th-n">{{ i + 1 }}</text>
                    </view>
                    <view class="kv-th blank" hover-class="kv-hover" @click="openTpl">＋{{ '\n' }}テンプレート</view>
                </view>
            </scroll-view>
        </view>

        <view class="kv-bar" v-if="!edit">
            <view class="kv-bt" :class="{ on: tool == 'text' }" @click="setTool('text')"><view class="kv-bi">Aa</view>文字</view>
            <view class="kv-bt" :class="{ on: tool == 'shape' }" @click="setTool('shape')"><view class="kv-bi">{{ shapeIcon }}</view>図形</view>
            <!-- [カスタムエイド 2026-10-24] 下の画像だけを切り抜き・明るさ・回転で直す(書き込みは動かさない) -->
            <view class="kv-bt" @click="openImgEdit"><view class="kv-bi">⌗</view>画像編集</view>
            <view class="kv-bt" :class="{ on: tool == 'pin' }" @click="setTool('pin')"><view class="kv-bi">●</view>メモ</view>
            <view class="kv-bt" v-if="canDownload" @click="openDl"><view class="kv-bi">↓</view>ダウンロード</view>
        </view>

        <!-- [カスタムエイド 2026-10-24] その場で画像編集の道具(切り抜き・明るさ・回転・元に戻す)。直すのは下の画像だけ -->
        <view class="kv-epanel" :class="{ adj: edit.tool == 'adjust' }" v-if="edit">
            <view class="kv-eadj" v-if="edit.tool == 'adjust'">
                <view class="kv-esl"><text class="kv-esl-l">明るさ</text><slider class="kv-eslider" :min="-60" :max="60" :value="edit.bright" activeColor="#4C9BFF" backgroundColor="#48484A" block-size="22" @changing="edit.bright = $event.detail.value" @change="edit.bright = $event.detail.value"></slider><text class="kv-esl-v">{{ edit.bright > 0 ? '+' : '' }}{{ edit.bright }}</text></view>
                <view class="kv-esl"><text class="kv-esl-l">コントラスト</text><slider class="kv-eslider" :min="-60" :max="60" :value="edit.contrast" activeColor="#4C9BFF" backgroundColor="#48484A" block-size="22" @changing="edit.contrast = $event.detail.value" @change="edit.contrast = $event.detail.value"></slider><text class="kv-esl-v">{{ edit.contrast > 0 ? '+' : '' }}{{ edit.contrast }}</text></view>
            </view>
            <view class="kv-enote" v-else>{{ edit.tool == 'crop' ? '枠の外は切り取られます。書き込みは消えずに内側へ寄ります' : edit.rot ? '書き込みは画像についていきます(文字の向きは変わりません)' : '直すのは下の画像だけです。文字・図形・メモはそのまま残ります' }}</view>
            <view class="kv-etools">
                <view class="kv-et" :class="{ on: edit.tool == 'crop' }" @click="editTool('crop')"><view class="kv-ei">⌗</view>切り抜き</view>
                <view class="kv-et" :class="{ on: edit.tool == 'adjust' }" @click="editTool('adjust')"><view class="kv-ei">☀︎</view>明るさ</view>
                <view class="kv-et" @click="editTool('rotate')"><view class="kv-ei">↻</view>回転</view>
                <view class="kv-et" :class="{ dim: !editChanged && !(page && page.edited) }" @click="restoreEdit"><view class="kv-ei">↺</view>元に戻す</view>
            </view>
        </view>

        <!-- 文字を書く・直す -->
        <cs-sheet :show="textShow" :title="textEditId ? '文字を直す' : '文字を書き込む'" @close="textShow = false">
            <view class="kv-in">
                <textarea class="kv-ta" v-model="textVal" :style="{ color: colorHex(textColorNow) == '#FFFFFF' ? '#1D1D1F' : colorHex(textColorNow) }" :auto-height="true" :maxlength="500" placeholder="書き込む文字" :focus="textShow" :cursor-spacing="160"></textarea>
            </view>
            <view class="kv-btn1" :class="{ off: !textVal.trim() }" hover-class="kv-hover" @click="putText">{{ textEditId ? '直す' : '書き込む' }}</view>
        </cs-sheet>

        <!-- 見るだけのときに書き込みを押したとき(書いた人・日時) -->
        <cs-sheet :show="infoShow" :title="info ? (info.type == 2 ? shapeName(info.data.shape) : '書き込んだ文字') : ''" @close="infoShow = false">
            <view class="kv-in" v-if="info">
                <view class="kv-act-t" v-if="info.type == 1">{{ info.data.text }}</view>
                <view class="kv-who">{{ info.by_name || '—' }} ・ {{ time(info.at) }}{{ info.edited_at ? '(直した:' + time(info.edited_at) + ')' : '' }}</view>
                <view class="kv-tip">押さえたまま指を動かすと、移動できます。直す・消すは、選んだときに下に出るボタンから。</view>
            </view>
        </cs-sheet>

        <!-- メモを置く -->
        <cs-sheet :show="pinShow" :title="'メモ ' + nextPin + ' を置く'" @close="pinShow = false">
            <view class="kv-in">
                <textarea class="kv-ta dark" v-model="pinVal" :auto-height="true" :maxlength="1000" placeholder="メモ(スタッフどうしの連絡にも)" :focus="pinShow" :cursor-spacing="160"></textarea>
            </view>
            <view class="kv-btn1" :class="{ off: !pinVal.trim() }" hover-class="kv-hover" @click="putPin">メモを置く</view>
        </cs-sheet>

        <!-- メモのやりとり -->
        <cs-sheet :show="threadShow" :title="thread ? 'メモ ' + pinNo(thread) : 'メモ'" @close="threadShow = false">
            <view class="kv-in" v-if="thread">
                <view class="kv-cm">
                    <view class="kv-cm-who"><text class="kv-cm-n">{{ pinNo(thread) }}</text>{{ thread.by_name || '自分' }} ・ {{ thread.at ? time(thread.at) : 'まだ保存していません' }}</view>
                    <view class="kv-cm-t">{{ thread.data.text }}</view>
                </view>
                <view class="kv-cm reply" v-for="r in replies(thread.id)" :key="r.id">
                    <view class="kv-cm-who">{{ r.by_name || '—' }} ・ {{ time(r.at) }}<text class="kv-cm-del" @click="removeReply(r)">消す</text></view>
                    <view class="kv-cm-t">{{ r.data.text }}</view>
                </view>
                <view class="kv-reply" v-if="thread.id > 0">
                    <textarea class="kv-ta dark sm" v-model="replyVal" :auto-height="true" :maxlength="1000" placeholder="返信する" :cursor-spacing="120"></textarea>
                    <view class="kv-send" :class="{ off: busy || !replyVal.trim() }" @click="sendReply">送る</view>
                </view>
                <view class="kv-tip" v-else>✓ で保存すると、返信できるようになります。</view>
            </view>
        </cs-sheet>

        <!-- メモの一覧 -->
        <cs-sheet :show="listShow" title="メモの一覧" @close="listShow = false">
            <view class="kv-in">
                <view class="kv-cm" v-for="m in pins" :key="m.id" hover-class="kv-hover" @click="jumpPin(m)">
                    <view class="kv-cm-who"><text class="kv-cm-n">{{ pinNo(m) }}</text>{{ pageNo(m.page_id) }}ページ目 ・ {{ m.by_name || '自分' }} ・ {{ m.at ? time(m.at) : '保存前' }}<text class="kv-cm-r" v-if="replies(m.id).length">返信 {{ replies(m.id).length }}</text></view>
                    <view class="kv-cm-t">{{ m.data.text }}</view>
                </view>
            </view>
        </cs-sheet>

        <!-- テンプレートを足す -->
        <cs-sheet :show="tplShow" title="テンプレートを足す" @close="tplShow = false">
            <view class="kv-tnote">カルテの後ろ({{ pages.length + 1 }}ページ目)に足します。差し込みを置いたテンプレートは、お客様の情報が自動で入ります</view>
            <view class="kv-tpls">
                <view class="kv-tc" :class="{ on: tplSel == t.id }" v-for="t in tpls" :key="t.id" @click="tplSel = t.id">
                    <image class="kv-tc-img" :src="t.url" mode="aspectFit"></image>
                    <view class="kv-tc-n">{{ t.name }}</view>
                    <view class="kv-tc-s">{{ t.note }}</view>
                </view>
            </view>
            <view class="kv-btn1" :class="{ off: !tplSel || busy }" hover-class="kv-hover" @click="addTpl">{{ busy ? '足しています…' : 'このページを足す' }}</view>
        </cs-sheet>

        <!-- ダウンロード(オーナーだけ) -->
        <cs-sheet :show="dlShow" title="ダウンロード" @close="dlShow = false">
            <view class="kv-op" v-if="kind == 1" hover-class="kv-hover" @click="download('pdf')">
                <view class="kv-op-i"><image class="k-ic" src="/static/karte/pdf.svg"></image></view>
                <view class="kv-op-tt">カルテをまとめてPDFで<text class="kv-op-s">{{ pages.length }}ページ・書き込みも入ります</text></view>
            </view>
            <view class="kv-op" hover-class="kv-hover" @click="download('image')">
                <view class="kv-op-i"><image class="k-ic" src="/static/karte/image.svg"></image></view>
                <view class="kv-op-tt">{{ kind == 1 ? 'このページを画像で' : 'この写真を画像で' }}<text class="kv-op-s">{{ kind == 1 ? (idx + 1) + ' / ' + pages.length + ' ページ目・' : '' }}書き込みも入ります</text></view>
            </view>
            <view class="kv-lock"><image class="k-lock" src="/static/karte/lock.svg"></image>ダウンロードはオーナーだけができます。記録が残ります。</view>
        </cs-sheet>

        <!-- 「…」 -->
        <cs-sheet :show="menuShow" title="メニュー" @close="menuShow = false">
            <view class="kv-op" v-if="kind == 1" hover-class="kv-hover" @click="rename">
                <view class="kv-op-i"><image class="k-ic" src="/static/karte/edit.svg"></image></view>
                <view class="kv-op-tt">カルテの名前を変える<text class="kv-op-s">今:{{ title }}</text></view>
            </view>
            <view class="kv-op red" v-if="kind == 1 && pages.length > 1" hover-class="kv-hover" @click="removePage">
                <view class="kv-op-i"><image class="k-ic" src="/static/karte/trash.svg"></image></view>
                <view class="kv-op-tt">このページを消す<text class="kv-op-s">{{ idx + 1 }}ページ目。30日は「削除したもの」から戻せます</text></view>
            </view>
            <view class="kv-op red" hover-class="kv-hover" @click="removeKarte">
                <view class="kv-op-i"><image class="k-ic" src="/static/karte/trash.svg"></image></view>
                <view class="kv-op-tt">{{ kind == 1 ? 'カルテを消す' : 'この写真を消す' }}<text class="kv-op-s">30日は「削除したもの」から戻せます</text></view>
            </view>
        </cs-sheet>
    </view>
</template>

<script>
    import csSheet from '@/components/csSheet/csSheet.vue'
    import {
        karteDetail, karteAddMark, karteUpdateMark, karteDeleteMark, karteDownload, karteTpl, karteFromTpl,
        karteRename, karteDelete, kartePageDelete, karteEditPage, COLORS, colorHex, PEN_WIDTH, shortTime
    } from '@/api/karte.js'

    const SIZES = [12, 16, 20, 24, 28, 32, 36, 40, 48, 56, 64, 72, 88, 104, 120]
    const LEGACY = { s: 25, m: 35, l: 50 }
    const clamp = (v) => Math.min(1, Math.max(0, v))
    const r5 = (v) => Math.round(v * 100000) / 100000

    export default {
        components: { csSheet },
        data() {
            return {
                top: 20,
                id: 0,
                name: '',
                title: '',
                kind: 1,
                memberId: 0,
                pages: [],
                saved: [], // サーバーにある書き込み
                work: [], // 今の書き込み(✓ で保存するまでの間の分を含む)
                hist: [],
                hpos: 0,
                tmpSeq: 0,
                idx: 0,
                canDownload: false,
                tool: '', // '' 見るだけ / text / shape / pin
                shape: 'rect',
                shapes: [
                    { key: 'free', name: '自由線', icon: '✎' },
                    { key: 'line', name: '直線', icon: '／' },
                    { key: 'arrow', name: '矢印', icon: '↗' },
                    { key: 'circle', name: '円', icon: '◯' },
                    { key: 'rect', name: '四角', icon: '▢' },
                    { key: 'check', name: 'チェック', icon: '✓' }
                ],
                style: { color: 'red', width: 'thin', size: 32, textColor: 'red' },
                colors: COLORS,
                zoom: 1,
                resetKey: 0,
                sel: 0,
                hint: '',
                busy: false,
                // 文字
                textShow: false, textEditId: 0, textVal: '', at: null,
                infoShow: false, info: null,
                // メモ
                pinShow: false, pinVal: '', threadShow: false, threadId: 0, replyVal: '', listShow: false,
                // そのほか
                tplShow: false, tpls: [], tplSel: 0, dlShow: false, menuShow: false,
                firstPageId: 0,
                edit: null // [カスタムエイド 2026-10-24] その場で画像編集中:{ rot, crop:[x,y,w,h], bright, contrast, tool }
            }
        },
        computed: {
            page() {
                return this.pages[this.idx] || null
            },
            top3() {
                return this.work.filter((m) => !m.parent_id)
            },
            pins() {
                return this.work.filter((m) => m.type == 3 && !m.parent_id).sort((a, b) => this.pinNo(a) - this.pinNo(b))
            },
            nextPin() {
                const maxSaved = this.saved.reduce((n, m) => (m.type == 3 && !m.parent_id ? Math.max(n, m.pin_no) : n), 0)
                return maxSaved + this.work.filter((m) => m.type == 3 && m.id < 0).length + 1
            },
            selMark() {
                return this.sel ? this.work.find((m) => m.id == this.sel) || null : null
            },
            thread() {
                return this.threadId ? this.work.find((m) => m.id == this.threadId) || null : null
            },
            dirty() {
                return this.snap(this.work) != this.snap(this.saved)
            },
            editChanged() {
                const e = this.edit
                if (!e) return false
                const c = e.crop
                return !!(e.rot || e.bright || e.contrast || c[0] > 0.002 || c[1] > 0.002 || c[2] < 0.996 || c[3] < 0.996)
            },
            shapeIcon() {
                return (this.shapes.find((s) => s.key == this.shape) || this.shapes[0]).icon
            },
            textColorNow() {
                const m = this.textEditId ? this.work.find((x) => x.id == this.textEditId) : null
                return m ? m.data.color : this.style.textColor
            },
            // renderjs に渡すもの
            stage() {
                const p = this.page
                if (!p) return { key: 0 }
                const replyCount = {}
                this.work.forEach((m) => { if (m.parent_id) replyCount[m.parent_id] = (replyCount[m.parent_id] || 0) + 1 })
                return {
                    key: p.id + ':' + p.url, url: p.url,
                    // 画像編集の中身は1つずつ読む(中の値が変わったときも renderjs に渡すため)
                    edit: this.edit ? { rot: this.edit.rot, crop: this.edit.crop.slice(), bright: this.edit.bright, contrast: this.edit.contrast, tool: this.edit.tool } : null, w: p.w, h: p.h, tool: this.tool, shape: this.shape, reset: this.resetKey, sel: this.sel,
                    pen: { hex: colorHex(this.style.color), width: PEN_WIDTH[this.style.width] },
                    marks: this.work.filter((m) => m.page_id == p.id && !m.parent_id).map((m) => ({
                        id: m.id, type: m.type, x: m.x, y: m.y, pin: this.pinNo(m), replies: replyCount[m.id] || 0,
                        text: m.type == 1 ? m.data.text : '', hex: colorHex(m.data.color), size: this.sizeOf(m.data.size) / 1000,
                        shape: m.type == 2 ? (m.data.shape || 'free') : '', points: m.type == 2 ? m.data.points : null, width: PEN_WIDTH[m.data.width] || PEN_WIDTH.thin
                    }))
                }
            }
        },
        onLoad(o) {
            try { this.top = uni.getSystemInfoSync().statusBarHeight || 20 } catch (e) {}
            this.id = Number(o.id) || 0
            this.name = o.name ? decodeURIComponent(o.name) : ''
            this.firstPageId = Number(o.page_id) || 0
            this.load()
        },
        // Android の戻るボタン:書き込み中なら確かめる
        onBackPress() {
            if (this.edit) { this.cancelEdit(); return true }
            if (this.dirty) {
                this.discard(true)
                return true
            }
            return false
        },
        methods: {
            colorHex,
            time(t) {
                return shortTime(t)
            },
            snap(list) {
                return JSON.stringify(list.filter((m) => !m.parent_id).map((m) => [m.id, m.page_id, m.type, m.x, m.y, m.data]))
            },
            clone(v) {
                return JSON.parse(JSON.stringify(v))
            },
            sizeOf(s) {
                return typeof s == 'number' || /^\d+$/.test(String(s)) ? Number(s) : LEGACY[s] || 35
            },
            nextSize(n, d) {
                n = Number(n) || 32
                if (d > 0) return SIZES.find((v) => v > n) || Math.min(300, n + 16)
                const s = SIZES.slice().reverse().find((v) => v < n)
                return s || Math.max(8, n - 4)
            },
            shapeName(k) {
                return (this.shapes.find((s) => s.key == (k || 'free')) || this.shapes[0]).name
            },
            pinNo(m) {
                if (!m) return 0
                if (m.id > 0) return m.pin_no
                const maxSaved = this.saved.reduce((n, x) => (x.type == 3 && !x.parent_id ? Math.max(n, x.pin_no) : n), 0)
                const tmp = this.work.filter((x) => x.type == 3 && x.id < 0).sort((a, b) => b.id - a.id)
                return maxSaved + tmp.findIndex((x) => x.id == m.id) + 1
            },
            pageNo(pid) {
                return this.pages.findIndex((p) => p.id == pid) + 1
            },
            replies(id) {
                return this.work.filter((m) => m.parent_id == id)
            },
            flash(t) {
                this.hint = t
                clearTimeout(this.hintT)
                this.hintT = setTimeout(() => (this.hint = ''), 2500)
            },
            load(goLast) {
                return karteDetail(this.id).then((res) => {
                    if (!res) return
                    if (res.code == 403) {
                        uni.showToast({ title: res.message, icon: 'none' })
                        setTimeout(() => uni.navigateBack(), 1200)
                        return
                    }
                    if (res.code != 200) return
                    const d = res.data
                    this.title = d.title
                    this.kind = d.kind
                    this.memberId = d.member_id
                    this.pages = d.pages || []
                    this.saved = d.marks || []
                    this.work = this.clone(this.saved)
                    this.hist = [this.snapWork()]
                    this.hpos = 0
                    this.sel = 0
                    this.canDownload = !!d.can_download
                    if (this.firstPageId) {
                        const i = this.pages.findIndex((p) => p.id == this.firstPageId)
                        if (i >= 0) this.idx = i
                        this.firstPageId = 0
                    } else if (goLast) {
                        this.idx = this.pages.length - 1
                    }
                    if (this.idx > this.pages.length - 1) this.idx = Math.max(0, this.pages.length - 1)
                })
            },
            // ---- 戻す・やり直す ----
            snapWork() {
                return JSON.stringify(this.work.filter((m) => !m.parent_id))
            },
            commit() {
                this.hist = this.hist.slice(0, this.hpos + 1)
                this.hist.push(this.snapWork())
                this.hpos = this.hist.length - 1
            },
            restore(i) {
                const top = JSON.parse(this.hist[i])
                this.work = top.concat(this.work.filter((m) => m.parent_id))
                this.hpos = i
                if (this.sel && !this.work.find((m) => m.id == this.sel)) this.sel = 0
            },
            undo() {
                if (this.hpos > 0) this.restore(this.hpos - 1)
            },
            redo() {
                if (this.hpos < this.hist.length - 1) this.restore(this.hpos + 1)
            },
            // ---- 道具 ----
            go(i) {
                if (i < 0 || i >= this.pages.length || i == this.idx) return
                this.idx = i
                this.sel = 0
                this.zoom = 1
            },
            setTool(t) {
                this.tool = this.tool == t ? '' : t
                this.sel = 0
                if (this.tool == 'text') this.flash('文字を書きたい所を押してください')
                else if (this.tool == 'pin') this.flash('メモを置きたい所を押してください')
                else if (this.tool == 'shape') this.flash(this.shape == 'free' ? '1本指で書く ・ 2本指で拡大・移動' : '置きたい所を押してください')
            },
            pickShape(k) {
                this.shape = k
                this.flash(k == 'free' ? '1本指で書く ・ 2本指で拡大・移動' : '置きたい所を押してください。青い点で大きさを変えられます')
            },
            addMark(m) {
                m.id = --this.tmpSeq
                m.page_id = this.page.id
                m.parent_id = 0
                m.pin_no = 0
                m.by_name = ''
                m.at = 0
                this.work.push(m)
                this.commit()
                return m
            },
            updateMark(id, fn) {
                const i = this.work.findIndex((m) => m.id == id)
                if (i < 0) return
                const m = this.clone(this.work[i])
                fn(m)
                this.$set(this.work, i, m)
                this.commit()
            },
            setSel(o) {
                if (!this.selMark) return
                this.updateMark(this.sel, (m) => { Object.assign(m.data, o) })
                // 次に書くものも同じ色・太さに
                if (o.color) { if (this.selMark.type == 1) this.style.textColor = o.color; else this.style.color = o.color }
                if (o.width) this.style.width = o.width
            },
            // 選んでいる図形の種類を変える(2点で決まる図形どうし)。自由線を選んだときは、選ぶのをやめて自由線で書けるように
            changeShape(k) {
                const m = this.selMark
                if (!m) return
                const cur = m.data.shape || 'free'
                if (k == 'free' || cur == 'free') {
                    this.sel = 0
                    this.pickShape(k)
                    return
                }
                this.shape = k
                this.updateMark(m.id, (x) => {
                    const p = x.data.points
                    // 直線・矢印 ⇔ 四角などで形が平らすぎるときは、少し高さを付ける
                    if ((k != 'line' && k != 'arrow') && Math.abs(p[1][1] - p[0][1]) < 0.01) {
                        const R = this.page.h / this.page.w, w = Math.abs(p[1][0] - p[0][0])
                        const cy = (p[0][1] + p[1][1]) / 2, hh = (k == 'circle' ? w : w * 0.6) / R / 2
                        x.data.points = [[p[0][0], r5(clamp(cy - hh))], [p[1][0], r5(clamp(cy + hh))]]
                    }
                    x.data.shape = k
                })
            },
            bumpSize(d) {
                const n = this.nextSize(this.sizeOf(this.selMark.data.size), d)
                this.setSel({ size: n })
                this.style.size = n
            },
            removeSel() {
                const m = this.selMark
                if (!m) return
                this.work = this.work.filter((x) => x.id != m.id && x.parent_id != m.id)
                this.sel = 0
                this.commit()
            },
            // 図形をタップした所に置く(大きさはページの幅の 2割くらい。あとで青い点で変える)
            placeShape(x, y) {
                const p = this.page
                const R = p.h / p.w
                const w = this.shape == 'check' ? 0.08 : 0.22
                const h = (this.shape == 'line' || this.shape == 'arrow') ? 0 : (this.shape == 'check' ? 0.06 : (this.shape == 'circle' ? w : 0.14)) / R
                let pts
                if (this.shape == 'line' || this.shape == 'arrow') pts = [[clamp(x - w / 2), clamp(y)], [clamp(x + w / 2), clamp(y)]]
                else pts = [[clamp(x - w / 2), clamp(y - h / 2)], [clamp(x + w / 2), clamp(y + h / 2)]]
                const m = this.addMark({ type: 2, x: pts[0][0], y: pts[0][1], data: { shape: this.shape, points: pts.map((q) => q.map(r5)), color: this.style.color, width: this.style.width } })
                this.sel = m.id
            },
            // ---- renderjs から ----
            onZoom(s) {
                this.zoom = s
            },
            // renderjs で描けなかったとき(確認用。原因が分かったら外す)
            onDebug(msg) {
                uni.showToast({ title: '表示エラー ' + String(msg).slice(0, 60), icon: 'none', duration: 4000 })
            },
            onSwipe(dir) {
                if (this.tool == '') this.go(this.idx + dir)
            },
            onTap(e) {
                const mark = e.mark ? this.work.find((m) => m.id == e.mark) : null
                if (mark && mark.type == 3 && (this.tool == '' || this.tool == 'pin')) return this.openThread(mark)
                // [カスタムエイド 2026-10-24] 道具を選んでいなくても、押すと選べる(青い点線)。選んでいるものをもう一度押すと、書いた人・日時
                if (this.tool == '') {
                    if (mark) { if (this.sel == mark.id) { this.info = mark; this.infoShow = true } else this.sel = mark.id } else this.sel = 0
                    return
                }
                if (mark) { this.sel = mark.id; return }
                if (this.sel) { this.sel = 0; return } // まず選んでいるものを外す
                if (this.tool == 'text') {
                    this.at = { x: e.x, y: e.y }
                    this.textEditId = 0
                    this.textVal = ''
                    this.textShow = true
                } else if (this.tool == 'pin') {
                    this.at = { x: e.x, y: e.y }
                    this.pinVal = ''
                    this.pinShow = true
                } else if (this.tool == 'shape' && this.shape != 'free') {
                    this.placeShape(e.x, e.y)
                }
            },
            onStroke(e) {
                if (!this.page || !e.points || e.points.length < 2) return
                this.addMark({ type: 2, x: e.points[0][0], y: e.points[0][1], data: { shape: 'free', points: e.points, color: this.style.color, width: this.style.width } })
            },
            // ドラッグで動かした・大きさを変えた
            onMarkChange(e) {
                this.updateMark(e.id, (m) => {
                    if (e.points) {
                        m.data.points = e.points.map((q) => [r5(clamp(q[0])), r5(clamp(q[1]))])
                        m.x = m.data.points[0][0]
                        m.y = m.data.points[0][1]
                    } else {
                        m.x = r5(clamp(e.x))
                        m.y = r5(clamp(e.y))
                    }
                })
                this.sel = e.id
            },
            // ---- 文字 ----
            putText() {
                const t = this.textVal.trim()
                if (!t) return
                if (this.textEditId) {
                    this.updateMark(this.textEditId, (m) => { m.data.text = t })
                } else {
                    const m = this.addMark({ type: 1, x: this.at.x, y: this.at.y, data: { text: t, color: this.style.textColor, size: this.style.size } })
                    this.sel = m.id
                }
                this.textShow = false
            },
            editText() {
                const m = this.selMark
                if (!m) return
                this.textEditId = m.id
                this.textVal = m.data.text
                this.textShow = true
            },
            // ---- メモ ----
            putPin() {
                const t = this.pinVal.trim()
                if (!t) return
                this.addMark({ type: 3, x: this.at.x, y: this.at.y, data: { text: t } })
                this.pinShow = false
                this.flash('✓ で保存すると、ほかのスタッフにも見えます')
            },
            openThread(m) {
                this.threadId = m.id
                this.sel = this.tool ? m.id : 0
                this.replyVal = ''
                this.threadShow = true
            },
            sendReply() {
                const t = this.replyVal.trim()
                if (!t || this.busy || !this.thread) return
                this.busy = true
                karteAddMark(this.thread.page_id, { type: 3, parent_id: this.thread.id, data: { text: t } }).then((res) => {
                    this.busy = false
                    if (res && res.code == 200) {
                        this.work.push(res.data.mark)
                        this.saved.push(this.clone(res.data.mark))
                        this.replyVal = ''
                    }
                })
            },
            removeReply(r) {
                uni.showModal({
                    title: '返信を消しますか?', confirmText: '消す', confirmColor: '#D93025', cancelText: 'キャンセル',
                    success: (x) => {
                        if (!x.confirm) return
                        karteDeleteMark(r.id).then((res) => {
                            if (res && res.code == 200) {
                                this.work = this.work.filter((m) => m.id != r.id)
                                this.saved = this.saved.filter((m) => m.id != r.id)
                            }
                        })
                    }
                })
            },
            jumpPin(m) {
                this.listShow = false
                const i = this.pages.findIndex((p) => p.id == m.page_id)
                if (i >= 0) this.go(i)
                setTimeout(() => this.openThread(m), 300)
            },
            // ---- 保存・やめる ----
            async save() {
                if (this.busy) return
                this.busy = true
                uni.showLoading({ title: '保存しています', mask: true })
                const savedMap = {}
                this.saved.filter((m) => !m.parent_id).forEach((m) => (savedMap[m.id] = m))
                const work = this.work.filter((m) => !m.parent_id)
                let ng = ''
                try {
                    // 消したもの
                    for (const id in savedMap) {
                        if (!work.find((m) => m.id == id)) {
                            const r = await karteDeleteMark(id)
                            if (!r || r.code != 200) ng = (r && r.message) || '保存できませんでした'
                        }
                    }
                    // 直したもの
                    for (const m of work) {
                        const o = savedMap[m.id]
                        if (m.id < 0 || !o) continue
                        if (JSON.stringify([m.x, m.y, m.data]) == JSON.stringify([o.x, o.y, o.data])) continue
                        const r = await karteUpdateMark(m.id, { x: m.x, y: m.y, data: m.data })
                        if (!r || r.code != 200) ng = (r && r.message) || '保存できませんでした'
                    }
                    // 足したもの(足した順に。メモの番号がその順になる)
                    for (const m of work.filter((x) => x.id < 0).sort((a, b) => b.id - a.id)) {
                        const r = await karteAddMark(m.page_id, { type: m.type, x: m.x, y: m.y, data: m.data })
                        if (!r || r.code != 200) ng = (r && r.message) || '保存できませんでした'
                    }
                } catch (e) {
                    ng = '通信できませんでした'
                }
                uni.hideLoading()
                this.busy = false
                const keep = this.idx
                await this.load()
                this.idx = Math.min(keep, this.pages.length - 1)
                this.tool = ''
                uni.showToast({ title: ng ? ng : '保存しました', icon: 'none' })
            },
            discard(leave) {
                uni.showModal({
                    title: '書き込みを保存しますか?',
                    content: '保存しないと、今の書き込みは消えます。',
                    confirmText: '保存する',
                    cancelText: '保存しない',
                    success: (r) => {
                        if (r.confirm) {
                            this.save().then(() => { if (leave === true) uni.navigateBack() })
                        } else if (r.cancel) {
                            this.work = this.clone(this.saved)
                            this.hist = [this.snapWork()]
                            this.hpos = 0
                            this.sel = 0
                            if (leave === true) uni.navigateBack()
                        }
                    }
                })
            },
            close() {
                if (this.dirty) return this.discard(true)
                uni.navigateBack()
            },
            // ---- テンプレート ----
            openTpl() {
                if (this.dirty) return uni.showToast({ title: '先に ✓ で書き込みを保存してください', icon: 'none' })
                karteTpl().then((res) => {
                    if (!res || res.code != 200) return
                    this.tpls = (res.data.list || []).filter((t) => t.status == 0 && t.url)
                    this.tplSel = this.tpls.length ? this.tpls[0].id : 0
                    if (!this.tpls.length) {
                        uni.showModal({
                            title: 'テンプレートがありません',
                            content: '各種設定の「カルテ」で、お店のテンプレート(3種類まで)を登録してください。',
                            confirmText: '登録する',
                            cancelText: 'あとで',
                            success: (r) => { if (r.confirm) uni.navigateTo({ url: '/pages/karte/setting' }) }
                        })
                        return
                    }
                    this.tplShow = true
                })
            },
            addTpl() {
                if (!this.tplSel || this.busy) return
                this.busy = true
                karteFromTpl(this.memberId, { tpl_id: this.tplSel, karte_id: this.id }).then((res) => {
                    this.busy = false
                    if (res && res.code == 200) {
                        this.tplShow = false
                        this.load(true)
                    }
                })
            },
            // ---- 画像編集(切り抜き・明るさ・回転)----
            // 直すのは下の画像だけ。文字・図形・メモは位置(ページに対する割合)をそのまま残すので、消えずに画像の中にとどまる
            // [カスタムエイド 2026-10-24] 別の画面に移らず、この画面のまま画像編集にする
            openImgEdit() {
                if (this.dirty) return uni.showToast({ title: '先に ✓ で書き込みを保存してください', icon: 'none' })
                const p = this.page
                if (!p) return
                if (p.status != 0) return uni.showToast({ title: 'このページは取り込み中です', icon: 'none' })
                this.tool = ''
                this.sel = 0
                this.edit = { rot: 0, crop: [0, 0, 1, 1], bright: 0, contrast: 0, tool: '' }
            },
            editTool(t) {
                const e = this.edit
                if (!e) return
                if (t == 'rotate') {
                    // 右へ90°。切り抜きの枠もいっしょに回す
                    const c = e.crop
                    e.crop = [1 - c[1] - c[3], c[0], c[3], c[2]]
                    e.rot = (e.rot + 90) % 360
                    return
                }
                e.tool = e.tool == t ? '' : t
            },
            // renderjs から:切り抜きの枠を動かした
            onCrop(c) {
                if (this.edit && c && c.length == 4) this.edit.crop = c
            },
            cancelEdit() {
                this.edit = null
            },
            // 元に戻す:この場で直したところがあれば戻す。なければ、最初に取り込んだ画像に戻す(サーバー)
            restoreEdit() {
                if (this.editChanged) { this.edit = { rot: 0, crop: [0, 0, 1, 1], bright: 0, contrast: 0, tool: '' }; return }
                const p = this.page
                if (!p || !p.edited) return uni.showToast({ title: '元の画像のままです', icon: 'none' })
                uni.showModal({
                    title: '最初の画像に戻しますか?', content: '切り抜き・明るさ・回転を取り消します。文字・図形・メモは消えません。',
                    confirmText: '戻す', cancelText: 'キャンセル',
                    success: (m) => { if (m.confirm) this.sendEdit({ restore: 1 }, '元の画像に戻しました') }
                })
            },
            saveEdit() {
                const e = this.edit
                if (!e || this.busy) return
                if (!this.editChanged) { this.edit = null; return }
                const data = { rotate: e.rot, bright: e.bright, contrast: e.contrast }
                const c = e.crop
                if (c[0] > 0.002 || c[1] > 0.002 || c[2] < 0.996 || c[3] < 0.996) data.crop = JSON.stringify(c.map((v) => Math.round(v * 10000) / 10000))
                this.sendEdit(data, '画像を直しました')
            },
            sendEdit(data, done) {
                const p = this.page
                if (!p || this.busy) return
                this.busy = true
                uni.showLoading({ title: '保存しています', mask: true })
                karteEditPage(p.id, data).then((r) => {
                    if (r && r.code == 200) {
                        // 新しい画像と、画像に合わせて動いた書き込みを読み直す(この画面のまま)
                        this.firstPageId = p.id
                        this.load().then(() => {
                            uni.hideLoading()
                            this.busy = false
                            this.edit = null
                            uni.showToast({ title: done, icon: 'none' })
                        })
                    } else {
                        uni.hideLoading()
                        this.busy = false
                        uni.showToast({ title: (r && r.message) || '保存できませんでした', icon: 'none' })
                    }
                })
            },
            // ---- ダウンロード ----
            openDl() {
                if (this.dirty) return uni.showToast({ title: '先に ✓ で書き込みを保存してください', icon: 'none' })
                this.dlShow = true
            },
            download(format) {
                this.dlShow = false
                uni.showLoading({ title: '作っています', mask: true })
                karteDownload(this.id, format, format == 'image' || this.kind != 1 ? this.page.id : 0).then((res) => {
                    if (!res || res.code != 200) { uni.hideLoading(); if (res && res.message) uni.showToast({ title: res.message, icon: 'none' }); return }
                    const url = res.data.url
                    // #ifdef H5
                    uni.hideLoading()
                    window.open(url)
                    // #endif
                    // #ifndef H5
                    uni.downloadFile({
                        url,
                        success: (r) => {
                            uni.hideLoading()
                            if (r.statusCode != 200) return uni.showToast({ title: 'ダウンロードできませんでした', icon: 'none' })
                            if (format == 'image') {
                                uni.saveImageToPhotosAlbum({
                                    filePath: r.tempFilePath,
                                    success: () => uni.showToast({ title: '写真に保存しました', icon: 'none' }),
                                    fail: () => uni.showToast({ title: '写真への保存が許可されていません(設定アプリで許可してください)', icon: 'none' })
                                })
                            } else {
                                uni.openDocument({ filePath: r.tempFilePath, fileType: 'pdf', showMenu: true })
                            }
                        },
                        fail: () => { uni.hideLoading(); uni.showToast({ title: 'ダウンロードできませんでした', icon: 'none' }) }
                    })
                    // #endif
                })
            },
            // ---- 「…」 ----
            rename() {
                this.menuShow = false
                uni.showModal({
                    title: 'カルテの名前', editable: true, content: this.title, confirmText: '保存', cancelText: 'キャンセル',
                    success: (m) => {
                        if (!m.confirm) return
                        karteRename(this.id, (m.content || '').trim()).then((res) => { if (res && res.code == 200) this.title = (m.content || '').trim() })
                    }
                })
            },
            removePage() {
                this.menuShow = false
                const p = this.page
                uni.showModal({
                    title: (this.idx + 1) + 'ページ目を消しますか?', content: '30日以内なら「削除したもの」から戻せます。',
                    confirmText: '消す', confirmColor: '#D93025', cancelText: 'キャンセル',
                    success: (m) => {
                        if (!m.confirm) return
                        kartePageDelete(p.id).then((res) => { if (res && res.code == 200) { uni.showToast({ title: '消しました', icon: 'none' }); this.load() } })
                    }
                })
            },
            removeKarte() {
                this.menuShow = false
                uni.showModal({
                    title: this.kind == 1 ? 'カルテを消しますか?' : 'この写真を消しますか?', content: '30日以内なら「削除したもの」から戻せます。',
                    confirmText: '消す', confirmColor: '#D93025', cancelText: 'キャンセル',
                    success: (m) => {
                        if (!m.confirm) return
                        karteDelete(this.id).then((res) => { if (res && res.code == 200) { uni.showToast({ title: '消しました', icon: 'none' }); setTimeout(() => uni.navigateBack(), 600) } })
                    }
                })
            }
        }
    }
</script>

<script module="kv" lang="renderjs">
    // [カスタムエイド 2026-10-24] 画面の側で動く部分(カルテ・写真の画面 view.vue と、テンプレートを編集 tplEdit.vue で同じものを使う。
    //   直すときは両方を同じ内容にする。違うのは STAGE_ID だけ)
    //   ・紙の表示・拡大(300%まで)/移動・書き込み(図形・文字・メモ)の表示・指で書く・ドラッグで動かす・青い点で大きさを変える
    //   ・その場で画像編集(切り抜き・明るさ・回転)。画像と書き込みは別の canvas に描くので、明るさを変えても書き込みの色は変わらない
    //   ・画面と同じ大きさの canvas に「今見えている部分だけ」を毎回描く(拡大してもメモリは同じ)
    //   ・iPhone で位置が NaN になった件:状態を Vue の data に入れず普通の値で持つ。枠の位置が取れないときは前の値を使う
    const STAGE_ID = 'kvStage'
    const MAXS = 3 // 拡大の上限 300%(2026-10-24 坂本さん確認:業務では300%で十分)
    const num = (v, d) => (typeof v === 'number' && isFinite(v) ? v : d)
    // 図形を線の集まりにする(サーバーの CsKarte::shapeLines と同じ考え方)。R = 高さ ÷ 幅
    function shapeLines(m, R) {
        const p = m.points || []
        if (!m.shape || m.shape == 'free' || p.length < 2) return [p]
        const [x1, y1] = p[0], [x2, y2] = p[1]
        if (m.shape == 'line') return [[[x1, y1], [x2, y2]]]
        if (m.shape == 'arrow') {
            const dx = x2 - x1, dy = (y2 - y1) * R, len = Math.hypot(dx, dy) || 1
            const hl = Math.min(len * 0.4, Math.max(0.03, m.width * 6))
            const ux = dx / len, uy = dy / len, a = 0.45
            const h1 = [x2 - hl * (ux * Math.cos(a) - uy * Math.sin(a)), y2 - hl * (uy * Math.cos(a) + ux * Math.sin(a)) / R]
            const h2 = [x2 - hl * (ux * Math.cos(a) + uy * Math.sin(a)), y2 - hl * (uy * Math.cos(a) - ux * Math.sin(a)) / R]
            return [[[x1, y1], [x2, y2]], [h1, [x2, y2], h2]]
        }
        const l = Math.min(x1, x2), r = Math.max(x1, x2), t = Math.min(y1, y2), b = Math.max(y1, y2)
        if (m.shape == 'rect') return [[[l, t], [r, t], [r, b], [l, b], [l, t]]]
        if (m.shape == 'check') return [[[l, t + (b - t) * 0.55], [l + (r - l) * 0.38, b], [r, t]]]
        if (m.shape == 'circle') {
            const cx = (l + r) / 2, cy = (t + b) / 2, rx = (r - l) / 2, ry = (b - t) / 2, o = []
            for (let i = 0; i <= 72; i++) { const th = i / 72 * 2 * Math.PI; o.push([cx + rx * Math.cos(th), cy + ry * Math.sin(th)]) }
            return [o]
        }
        return [p]
    }
    function bbox(pts) {
        let l = 1, t = 1, r = 0, b = 0
        pts.forEach((q) => { l = Math.min(l, q[0]); t = Math.min(t, q[1]); r = Math.max(r, q[0]); b = Math.max(b, q[1]) })
        return { l, t, r, b }
    }
    function segDist(px, py, a, b, R) {
        const ax = a[0], ay = a[1] * R, bx = b[0], by = b[1] * R, x = px, y = py * R
        const dx = bx - ax, dy = by - ay, L = dx * dx + dy * dy
        let u = L ? ((x - ax) * dx + (y - ay) * dy) / L : 0
        u = Math.max(0, Math.min(1, u))
        return Math.hypot(x - (ax + u * dx), y - (ay + u * dy))
    }
    // 角の丸い四角(左上・右上・右下・左下の丸み)
    function rrect(c, x, y, w, h, r) {
        c.beginPath()
        c.moveTo(x + r[0], y)
        c.lineTo(x + w - r[1], y); c.arcTo(x + w, y, x + w, y + r[1], r[1])
        c.lineTo(x + w, y + h - r[2]); c.arcTo(x + w, y + h, x + w - r[2], y + h, r[2])
        c.lineTo(x + r[3], y + h); c.arcTo(x, y + h, x, y + h - r[3], r[3])
        c.lineTo(x, y + r[0]); c.arcTo(x, y, x + r[0], y, r[0])
        c.closePath()
    }
    // 時計回りに 90°×n 回した位置(ページに対する割合)
    function rotPt(q, n) { let x = q[0], y = q[1]; for (let i = 0; i < n; i++) { const t = x; x = 1 - y; y = t } return [x, y] }
    const FONT = '"Hiragino Sans","Hiragino Kaku Gothic ProN","Noto Sans JP",sans-serif'
    export default {
        methods: {
            S() {
                // 状態は Vue の data に入れない(iPhone で値が消えて NaN になったため)
                if (!this.kvS) this.kvS = { key: -1, s: 1, tx: 0, ty: 0, reset: 0, lay: '', rcL: 0, rcT: 0 }
                return this.kvS
            },
            onProp(nv, ov, owner) {
                this.owner = owner
                const s = this.S()
                s.p = nv || {}
                if (!nv || !nv.key) { this.clear(); return }
                if (s.key !== nv.key || s.reset !== nv.reset || !s.cvI) {
                    s.key = nv.key
                    s.reset = nv.reset
                    this.build()
                    return
                }
                // 画像編集の回転で縦横が変わったら、並べ直す
                // 画像編集の回転・画像編集の始め/終わり(余白)・表示する場所の大きさが変わったら、並べ直す
                this.fitStage()
                if (this.layKey() !== s.lay) this.layout(true)
                setTimeout(() => { if (this.fitStage()) this.draw() }, 80)
                if (!s.g || s.g.kind != 'drag' || Date.now() - s.g.t0 > 8000) {
                    // ドラッグの途中は、書き込みを描き直さない(動かしているものが元に戻らないように)
                    s.marks = JSON.parse(JSON.stringify(nv.marks || []))
                }
                if (!s.g || s.g.kind != 'crop') s.crop = this.editCrop()
                this.filter()
                this.draw()
            },
            clear() {
                const el = this.el()
                if (el) el.innerHTML = ''
                const s = this.S()
                s.cvI = null; s.cvM = null; s.img = null
            },
            el() {
                return document.getElementById(STAGE_ID)
            },
            ed() {
                return this.S().p.edit || null
            },
            rotN() {
                const e = this.ed()
                return e ? (((num(e.rot, 0) / 90) % 4) + 4) % 4 : 0
            },
            editCrop() {
                const e = this.ed()
                const c = e && e.crop
                return c && c.length == 4 ? c.slice() : [0, 0, 1, 1]
            },
            // 表示する紙の縦横(画像編集で横に回したときは入れ替える)
            layKey() {
                const p = this.S().p
                return p.w + 'x' + p.h + '@' + (this.rotN() % 2) + (p.edit ? 'e' : '')
            },
            // 表示する場所の大きさが変わったとき(画像編集の始め/終わり)、canvas の大きさを合わせる。変わったら true
            fitStage() {
                const s = this.S(), el = this.el()
                if (!el || !s.cvI) return false
                const SW = el.clientWidth, SH = el.clientHeight
                if (!SW || !SH || (SW == s.SW && SH == s.SH)) return false
                ;[s.cvI, s.cvM].forEach((cv) => {
                    cv.width = Math.round(SW * s.dpr); cv.height = Math.round(SH * s.dpr)
                    cv.style.width = SW + 'px'; cv.style.height = SH + 'px'
                })
                s.SW = SW; s.SH = SH
                this.layout(true)
                return true
            },
            build() {
                const el = this.el()
                // 画面がまだできていないときは、できるまで待つ(テンプレートを開くと真っ暗になった件)
                if (!el) { setTimeout(() => { if (this.S().p.key) this.build() }, 60); return }
                const s = this.S()
                const p = s.p
                if (!this.bound) this.bind(el)
                const SW = el.clientWidth, SH = el.clientHeight
                if (!SW || !SH) { setTimeout(() => this.build(), 60); return }
                el.innerHTML = ''
                s.SW = SW; s.SH = SH
                // 画面と同じ大きさの canvas を2枚(下:画像/上:書き込み・切り抜きの枠)。画面の密度ぶん細かく(上限3)
                const dpr = Math.min(window.devicePixelRatio || 1, 3)
                const mk = () => {
                    const cv = document.createElement('canvas')
                    cv.width = Math.round(SW * dpr); cv.height = Math.round(SH * dpr)
                    cv.style.cssText = 'position:absolute;left:0;top:0;width:' + SW + 'px;height:' + SH + 'px;'
                    el.appendChild(cv)
                    return cv
                }
                s.cvI = mk(); s.cvM = mk()
                s.ctxI = s.cvI.getContext('2d'); s.ctxM = s.cvM.getContext('2d'); s.dpr = dpr
                s.img = null
                const img = new Image()
                const key = p.key
                img.onload = () => { const t = this.S(); if (t.key === key) { t.img = img; this.draw() } }
                img.src = p.url
                s.marks = JSON.parse(JSON.stringify(p.marks || []))
                s.crop = this.editCrop()
                this.rect()
                this.layout(true)
                this.filter()
                this.draw()
            },
            // 紙を枠の真ん中に、はみ出さない大きさで置く(等倍に戻す)
            layout(resetZoom) {
                const s = this.S(), p = s.p
                const el = this.el()
                const SW = (el && el.clientWidth) || s.SW, SH = (el && el.clientHeight) || s.SH
                const odd = this.rotN() % 2 == 1
                const pw = odd ? p.h : p.w, ph = odd ? p.w : p.h
                if (!SW || !SH || !pw || !ph) return
                // 画像編集中は、まわりに余白を取る(切り抜きの枠の四隅・辺が画面の端にかからず、つかみやすいように)
                const pad = p.edit ? 32 : 0
                const r = Math.min((SW - pad * 2) / pw, (SH - pad * 2) / ph)
                s.SW = SW; s.SH = SH
                s.W = pw * r; s.H = ph * r; s.R = ph / pw
                s.left = (SW - s.W) / 2; s.top = (SH - s.H) / 2
                s.lay = this.layKey()
                if (resetZoom) { s.s = 1; s.tx = 0; s.ty = 0; this.zoomOut() }
            },
            // 明るさ・コントラスト(画像の canvas だけにかける)
            filter() {
                const s = this.S(), e = this.ed()
                if (!s.cvI) return
                const f = e && (e.bright || e.contrast) ? 'brightness(' + (1 + num(e.bright, 0) / 100) + ') contrast(' + (1 + num(e.contrast, 0) / 100) + ')' : ''
                if (s.cvI.style.filter !== f) { s.cvI.style.filter = f; s.cvI.style.webkitFilter = f }
            },
            apply() { this.draw() },
            drawMarks() { this.draw() },
            zoomOut() {
                if (this.owner) this.owner.callMethod('onZoom', this.S().s)
            },
            // 描き直しの予約(1コマに1回だけ描く)
            draw() {
                if (this.raf) return
                const go = () => { this.raf = 0; this.paint() }
                this.raf = (window.requestAnimationFrame || ((f) => setTimeout(f, 16)))(go)
            },
            // うまく描けなかったときに、原因を画面に出す(確認用)
            report(where, err) {
                const msg = where + ': ' + (err && err.message ? err.message : String(err))
                if (this.lastErr == msg) return
                this.lastErr = msg
                if (this.owner) this.owner.callMethod('onDebug', msg)
            },
            // ページの割合 → 画面(この枠の中の px)
            sx(x) { const s = this.S(); return s.left + s.tx + x * s.W * s.s },
            sy(y) { const s = this.S(); return s.top + s.ty + y * s.H * s.s },
            // 書き込みの位置(元の画像の上の割合)→ 表示している紙の上の割合(画像編集で回しているとき)
            mp(q) { const n = this.rotN(); return n ? rotPt(q, n) : q },
            line(c, pts, hex, w, dash) {
                if (!pts || pts.length < 2) return
                c.beginPath()
                pts.forEach((q, i) => { const X = this.sx(q[0]), Y = this.sy(q[1]); i ? c.lineTo(X, Y) : c.moveTo(X, Y) })
                c.strokeStyle = hex; c.lineWidth = w; c.lineCap = 'round'; c.lineJoin = 'round'
                c.setLineDash(dash || [])
                c.stroke()
                c.setLineDash([])
            },
            // 選んでいる図形の青い点(ページの割合で返す)
            handles(m) {
                if (!m || m.type != 2 || !m.points) return []
                if (m.shape == 'line' || m.shape == 'arrow') return [{ k: 0, x: m.points[0][0], y: m.points[0][1] }, { k: 1, x: m.points[1][0], y: m.points[1][1] }]
                const b = bbox(m.points)
                return [{ k: 'tl', x: b.l, y: b.t }, { k: 'tr', x: b.r, y: b.t }, { k: 'bl', x: b.l, y: b.b }, { k: 'br', x: b.r, y: b.b }]
            },
            pinR() { return 0.018 * this.S().W * 1.4 * 1.5 }, // メモの丸の半径(拡大しても画面で同じ大きさ) [カスタムエイド 2026-10-25] 小さくて見にくかったので1.5倍(スマホの通知バッジくらい)
            paint() {
                try { this.paintImage(); this.paintMarks() } catch (err) { this.report('paint', err) }
            },
            fix() {
                const s = this.S()
                if (!isFinite(s.W) || !isFinite(s.left)) this.layout(false)
                if (!isFinite(s.s) || !isFinite(s.tx) || !isFinite(s.ty)) { s.s = 1; s.tx = 0; s.ty = 0 }
            },
            // 下の canvas:紙と画像(画像編集の回転もここで)
            paintImage() {
                const s = this.S()
                if (!s.cvI) return
                this.fix()
                const c = s.ctxI
                c.setTransform(s.dpr, 0, 0, s.dpr, 0, 0)
                c.clearRect(0, 0, s.SW, s.SH)
                const X0 = this.sx(0), Y0 = this.sy(0), PW = s.W * s.s, PH = s.H * s.s
                c.fillStyle = '#FBFAF6'
                c.fillRect(X0, Y0, PW, PH)
                if (!s.img) return
                const n = this.rotN()
                c.save()
                c.beginPath(); c.rect(0, 0, s.SW, s.SH); c.clip()
                c.imageSmoothingEnabled = true
                if (n) {
                    // 画像全体を置き、真ん中で回す(画面の外は canvas が切り捨てる)
                    c.translate(X0 + PW / 2, Y0 + PH / 2)
                    c.rotate(n * Math.PI / 2)
                    const dw = n % 2 ? PH : PW, dh = n % 2 ? PW : PH
                    c.drawImage(s.img, -dw / 2, -dh / 2, dw, dh)
                } else {
                    c.drawImage(s.img, X0, Y0, PW, PH)
                }
                c.restore()
            },
            // 上の canvas:書き込み・切り抜きの枠
            paintMarks() {
                const s = this.S()
                if (!s.cvM) return
                const c = s.ctxM, p = s.p, e = this.ed()
                c.setTransform(s.dpr, 0, 0, s.dpr, 0, 0)
                c.clearRect(0, 0, s.SW, s.SH)
                const PW = s.W * s.s
                s.boxes = {}
                const marks = s.marks || []
                const mp = (q) => this.mp(q)
                // 図形(下)
                marks.forEach((m) => {
                    if (m.type != 2 || !m.points) return
                    shapeLines({ shape: m.shape, width: m.width, points: m.points.map(mp) }, s.R).forEach((pts) => this.line(c, pts, m.hex, m.width * PW))
                })
                // 書いている途中の自由線
                if (s.drawing && s.drawing.length > 1) this.line(c, s.drawing, p.pen.hex, p.pen.width * PW)
                // 文字
                marks.forEach((m) => {
                    if (m.type != 1) return
                    const fs = m.size * PW
                    c.font = '700 ' + fs + 'px ' + FONT
                    c.textBaseline = 'top'
                    c.textAlign = 'left'
                    const lines = String(m.text || '').split('\n')
                    const q = mp([m.x, m.y])
                    const X = this.sx(q[0]), Y = this.sy(q[1]), lh = fs * 1.25
                    let w = 0
                    lines.forEach((t) => { w = Math.max(w, c.measureText(t).width) })
                    const box = { l: X, t: Y, r: X + w, b: Y + lh * lines.length }
                    s.boxes[m.id] = box
                    if (box.r < 0 || box.l > s.SW || box.b < 0 || box.t > s.SH) return
                    // 選んでいる文字は青い点線の枠。テンプレートの差し込み(tagBox)は、いつも枠を出す(選んでいるものは実線)
                    if (!e && (m.id == p.sel || p.tagBox)) {
                        const on = m.id == p.sel
                        c.fillStyle = on ? 'rgba(26,115,232,.18)' : 'rgba(26,115,232,.08)'
                        c.fillRect(box.l - 3, box.t - 3, box.r - box.l + 6, box.b - box.t + 6)
                        c.strokeStyle = '#1A73E8'; c.lineWidth = on && p.tagBox ? 2 : 1.5; c.setLineDash(on && p.tagBox ? [] : [4, 3])
                        c.strokeRect(box.l - 3, box.t - 3, box.r - box.l + 6, box.b - box.t + 6)
                        c.setLineDash([])
                    } else if (e && p.tagBox) {
                        c.strokeStyle = '#1A73E8'; c.lineWidth = 1.5; c.setLineDash([4, 3])
                        c.strokeRect(box.l - 3, box.t - 3, box.r - box.l + 6, box.b - box.t + 6)
                        c.setLineDash([])
                    }
                    if (m.hex == '#FFFFFF') { c.shadowColor = 'rgba(0,0,0,.8)'; c.shadowBlur = 3 }
                    c.fillStyle = m.hex
                    lines.forEach((t, i) => c.fillText(t, X, Y + i * lh + (lh - fs) / 2))
                    c.shadowBlur = 0; c.shadowColor = 'transparent'
                })
                // メモ(いちばん上)
                const r = this.pinR()
                marks.forEach((m) => {
                    if (m.type != 3) return
                    const q = mp([m.x, m.y])
                    const X = this.sx(q[0]), Y = this.sy(q[1])
                    s.boxes[m.id] = { cx: X, cy: Y, r }
                    if (X < -r * 3 || X > s.SW + r * 3 || Y < -r * 3 || Y > s.SH + r * 3) return
                    c.shadowColor = 'rgba(0,0,0,.45)'; c.shadowBlur = 4; c.shadowOffsetY = 1
                    rrect(c, X - r, Y - r, r * 2, r * 2, [r, r, r, 3])
                    c.fillStyle = m.id < 0 ? '#F7C04A' : '#F2A900'
                    c.fill()
                    c.shadowBlur = 0; c.shadowOffsetY = 0; c.shadowColor = 'transparent'
                    if (!e && m.id == p.sel) { c.strokeStyle = '#1A73E8'; c.lineWidth = 2; c.stroke() }
                    c.fillStyle = '#FFFFFF'
                    c.font = '700 ' + (r * 0.95) + 'px ' + FONT
                    c.textAlign = 'center'; c.textBaseline = 'middle'
                    c.fillText(String(m.pin), X, Y + 1)
                    if (m.replies) {
                        const t = String(m.replies)
                        c.font = '700 ' + (r * 0.6) + 'px ' + FONT
                        const bw = Math.max(r, c.measureText(t).width + 6)
                        const bx = X + r * 0.6, by = Y - r * 1.3
                        rrect(c, bx, by, bw, r, [r / 2, r / 2, r / 2, r / 2])
                        c.fillStyle = '#1A73E8'; c.fill()
                        c.fillStyle = '#FFFFFF'
                        c.fillText(t, bx + bw / 2, by + r / 2 + 1)
                    }
                    c.textAlign = 'left'; c.textBaseline = 'top'
                })
                // 選んでいる図形の枠と青い点(画面で同じ大きさ)。画像編集中は出さない
                const sel = !e && marks.find((m) => m.id == p.sel && m.type == 2 && m.points)
                if (sel) {
                    if (sel.shape != 'line' && sel.shape != 'arrow') {
                        const b = bbox(sel.points)
                        this.line(c, [[b.l, b.t], [b.r, b.t], [b.r, b.b], [b.l, b.b], [b.l, b.t]], '#1A73E8', 1.2, [5, 4])
                    }
                    this.handles(sel).forEach((h) => {
                        c.beginPath(); c.arc(this.sx(h.x), this.sy(h.y), 9, 0, Math.PI * 2)
                        c.fillStyle = '#1A73E8'; c.fill()
                        c.strokeStyle = '#FFFFFF'; c.lineWidth = 3; c.stroke()
                    })
                }
                // 画像編集:切り抜きの外を暗く、切り抜きのときは枠・線・角
                if (e) {
                    const k = s.crop || [0, 0, 1, 1]
                    const full = k[0] < 0.002 && k[1] < 0.002 && k[2] > 0.996 && k[3] > 0.996
                    const L = this.sx(k[0]), T = this.sy(k[1]), Rr = this.sx(k[0] + k[2]), B = this.sy(k[1] + k[3])
                    const X0 = this.sx(0), Y0 = this.sy(0), X1 = this.sx(1), Y1 = this.sy(1)
                    if (!full || e.tool == 'crop') {
                        c.fillStyle = 'rgba(0,0,0,.55)'
                        c.fillRect(X0, Y0, X1 - X0, T - Y0)
                        c.fillRect(X0, B, X1 - X0, Y1 - B)
                        c.fillRect(X0, T, L - X0, B - T)
                        c.fillRect(Rr, T, X1 - Rr, B - T)
                    }
                    if (e.tool == 'crop') {
                        c.strokeStyle = '#FFFFFF'; c.lineWidth = 2
                        c.strokeRect(L, T, Rr - L, B - T)
                        c.strokeStyle = 'rgba(255,255,255,.55)'; c.lineWidth = 1
                        for (let i = 1; i < 3; i++) {
                            const gx = L + (Rr - L) * i / 3, gy = T + (B - T) * i / 3
                            c.beginPath(); c.moveTo(gx, T); c.lineTo(gx, B); c.stroke()
                            c.beginPath(); c.moveTo(L, gy); c.lineTo(Rr, gy); c.stroke()
                        }
                        // 四隅は太い L 字、辺の真ん中は短い太線(写真アプリと同じ形)
                        c.strokeStyle = '#FFFFFF'; c.lineWidth = 5; c.lineCap = 'square'
                        const k2 = Math.min(26, (Rr - L) / 3, (B - T) / 3)
                        ;[[L, T, 1, 1], [Rr, T, -1, 1], [L, B, 1, -1], [Rr, B, -1, -1]].forEach((q) => {
                            c.beginPath(); c.moveTo(q[0] + q[2] * k2, q[1]); c.lineTo(q[0], q[1]); c.lineTo(q[0], q[1] + q[3] * k2); c.stroke()
                        })
                        const mh = Math.min(30, (Rr - L) / 4), mv = Math.min(30, (B - T) / 4)
                        c.beginPath(); c.moveTo((L + Rr) / 2 - mh / 2, T); c.lineTo((L + Rr) / 2 + mh / 2, T); c.stroke()
                        c.beginPath(); c.moveTo((L + Rr) / 2 - mh / 2, B); c.lineTo((L + Rr) / 2 + mh / 2, B); c.stroke()
                        c.beginPath(); c.moveTo(L, (T + B) / 2 - mv / 2); c.lineTo(L, (T + B) / 2 + mv / 2); c.stroke()
                        c.beginPath(); c.moveTo(Rr, (T + B) / 2 - mv / 2); c.lineTo(Rr, (T + B) / 2 + mv / 2); c.stroke()
                        c.lineCap = 'butt'
                    }
                }
            },
            // 枠の画面上の位置(取れないときは前の値)
            rect() {
                const s = this.S(), el = this.el()
                let L = NaN, T = NaN
                try { const r = el.getBoundingClientRect(); L = num(r.left, num(r.x, NaN)); T = num(r.top, num(r.y, NaN)) } catch (err) {}
                if (isFinite(L)) s.rcL = L
                if (isFinite(T)) s.rcT = T
                return { left: s.rcL, top: s.rcT }
            },
            // 画面の点 → ページに対する割合
            toPage(cx, cy) {
                const s = this.S()
                const rc = this.rect()
                const x = (num(cx, 0) - rc.left - s.left - s.tx) / (s.W * s.s)
                const y = (num(cy, 0) - rc.top - s.top - s.ty) / (s.H * s.s)
                return { x, y, inside: x >= 0 && x <= 1 && y >= 0 && y <= 1 }
            },
            // 押した所にある書き込み(上にあるものから)。青い点なら {h}
            hit(cx, cy) {
                const s = this.S()
                const q = this.toPage(cx, cy)
                const rc = this.rect()
                const px = cx - rc.left, py = cy - rc.top
                const tol = 16 / (s.W * s.s) // 画面で 16px
                const marks = s.marks || []
                const sel = marks.find((m) => m.id == s.p.sel)
                if (sel) {
                    const h = this.handles(sel).find((h) => Math.hypot(h.x - q.x, (h.y - q.y) * s.R) < tol * 1.4)
                    if (h) return { m: sel, h: h.k }
                }
                const boxes = s.boxes || {}
                // メモ → 文字(上に描いているものから)
                const top = marks.filter((m) => m.type == 3).reverse().concat(marks.filter((m) => m.type == 1).reverse())
                for (const m of top) {
                    const b = boxes[m.id]
                    if (!b) continue
                    if (m.type == 3 && Math.hypot(px - b.cx, py - b.cy) < b.r + 6) return { m }
                    if (m.type == 1 && px > b.l - 6 && px < b.r + 6 && py > b.t - 6 && py < b.b + 6) return { m }
                }
                const list = marks.filter((m) => m.type == 2 && m.points).reverse()
                for (const m of list) {
                    if (m.shape == 'free' || !m.shape) {
                        for (let i = 1; i < m.points.length; i++) if (segDist(q.x, q.y, m.points[i - 1], m.points[i], s.R) < tol) return { m }
                    } else if (m.shape == 'line' || m.shape == 'arrow') {
                        if (segDist(q.x, q.y, m.points[0], m.points[1], s.R) < tol) return { m }
                    } else {
                        const b = bbox(m.points)
                        if (q.x > b.l - tol && q.x < b.r + tol && q.y > b.t - tol / s.R && q.y < b.b + tol / s.R) return { m }
                    }
                }
                return null
            },
            clamp() {
                const s = this.S()
                if (!isFinite(s.s) || s.s <= 1.001) { s.s = 1; s.tx = 0; s.ty = 0; return }
                if (!isFinite(s.W) || !isFinite(s.left)) this.layout(false)
                if (!isFinite(s.tx) || !isFinite(s.ty)) {
                    this.report('pos', new Error('tx=' + s.tx + ' ty=' + s.ty))
                    s.tx = num(s.okTx, 0); s.ty = num(s.okTy, 0)
                }
                const SW = s.SW, SH = s.SH
                const w = s.W * s.s, h = s.H * s.s
                const minX = w > SW ? SW - w - s.left : (SW - w) / 2 - s.left, maxX = w > SW ? -s.left : minX
                const minY = h > SH ? SH - h - s.top : (SH - h) / 2 - s.top, maxY = h > SH ? -s.top : minY
                s.tx = Math.min(maxX, Math.max(minX, s.tx))
                s.ty = Math.min(maxY, Math.max(minY, s.ty))
                s.okTx = s.tx; s.okTy = s.ty
            },
            // 2回押しの拡大・等倍を 0.2秒で動かす
            animTo(ns, ntx, nty) {
                const s = this.S()
                const a = { s: s.s, tx: s.tx, ty: s.ty }, t0 = Date.now()
                const step = () => {
                    if (!s.cvI) return
                    const k = Math.min(1, (Date.now() - t0) / 200), e = 1 - Math.pow(1 - k, 3)
                    s.s = a.s + (ns - a.s) * e; s.tx = a.tx + (ntx - a.tx) * e; s.ty = a.ty + (nty - a.ty) * e
                    this.paint()
                    if (k < 1) (window.requestAnimationFrame || ((f) => setTimeout(f, 16)))(step)
                    else this.zoomOut()
                }
                step()
            },
            bind(el) {
                this.bound = true
                const opt = { passive: false }
                const T = (fn) => (e) => { this.lastTouch = Date.now(); fn(e) }
                el.addEventListener('touchstart', T((e) => this.ts(e)), opt)
                // 動かしている間・離したことは画面全体で受ける
                document.addEventListener('touchmove', T((e) => { if (this.kvS && this.kvS.g) this.tm(e) }), opt)
                document.addEventListener('touchend', T((e) => { if (this.kvS && this.kvS.g) this.te(e) }), opt)
                document.addEventListener('touchcancel', T((e) => { if (this.kvS && this.kvS.g) this.te(e) }), opt)
                // パソコンのブラウザで確かめるとき用(マウス)
                let down = false
                el.addEventListener('mousedown', (e) => { if (Date.now() - (this.lastTouch || 0) < 1000) return; down = true; this.ts({ touches: [e], preventDefault() {} }) })
                window.addEventListener('mousemove', (e) => { if (down) this.tm({ touches: [e], preventDefault() {} }) })
                window.addEventListener('mouseup', (e) => { if (down) { down = false; this.te({ touches: [], changedTouches: [e], preventDefault() {} }) } })
            },
            // 切り抜きの枠のどこを押したか(角・辺・中・外)
            cropHit(q) {
                const s = this.S(), k = s.crop || [0, 0, 1, 1]
                const tx = 32 / (s.W * s.s), ty = 32 / (s.H * s.s) // つかめる範囲:画面で 32px
                const L = k[0], T = k[1], R = k[0] + k[2], B = k[1] + k[3]
                const nl = Math.abs(q.x - L) < tx, nr = Math.abs(q.x - R) < tx, nt = Math.abs(q.y - T) < ty, nb = Math.abs(q.y - B) < ty
                const inX = q.x > L - tx && q.x < R + tx, inY = q.y > T - ty && q.y < B + ty
                let h = ''
                if (nt && inX) h += 't'
                if (nb && inX) h += 'b'
                if (nl && inY) h += 'l'
                if (nr && inY) h += 'r'
                if (h) return h
                if (q.x > L && q.x < R && q.y > T && q.y < B) return 'move'
                return ''
            },
            ts(e) {
                const s = this.S()
                if (!s.cvI) return
                e.preventDefault()
                const t = e.touches
                this.rect()
                if (t.length >= 2) {
                    // 2本指:拡大・移動(書いている途中・ドラッグの途中ならやめる)
                    if (s.drawing) s.drawing = null
                    if (s.g && s.g.kind == 'drag') s.marks = JSON.parse(JSON.stringify(s.p.marks || []))
                    if (s.g && s.g.kind == 'crop') s.crop = s.g.c0.slice()
                    const a = t[0], b = t[1]
                    const ax = num(a.clientX, 0), ay = num(a.clientY, 0), bx = num(b.clientX, 0), by = num(b.clientY, 0)
                    s.g = { kind: 'pinch', d: Math.hypot(ax - bx, ay - by) || 1, mx: (ax + bx) / 2, my: (ay + by) / 2, s0: num(s.s, 1), tx0: num(s.tx, 0), ty0: num(s.ty, 0) }
                    this.draw()
                    return
                }
                const a = t[0]
                const tool = s.p.tool
                const e2 = this.ed()
                s.g = { kind: 'one', x0: num(a.clientX, 0), y0: num(a.clientY, 0), tx0: num(s.tx, 0), ty0: num(s.ty, 0), t0: Date.now(), moved: false }
                // 画像編集:切り抜きのときは枠を動かす。ほかは紙を動かすだけ(書き込みには触らない)
                if (e2) {
                    if (e2.tool == 'crop') {
                        const q = this.toPage(a.clientX, a.clientY)
                        const h = this.cropHit(q)
                        if (h) { s.g.kind = 'crop'; s.g.h = h; s.g.q0 = q; s.g.c0 = (s.crop || [0, 0, 1, 1]).slice() }
                    }
                    return
                }
                // 書き込み(文字・図形・メモ)を押さえたら、道具に関係なくドラッグで動かす
                // (自由線で書いている最中は、上から書けるように、選んでいるものだけ動かす)
                const pen = tool == 'shape' && s.p.shape == 'free'
                const hit = this.hit(a.clientX, a.clientY)
                if (hit && (!pen || hit.m.id == s.p.sel)) {
                    s.g.kind = 'drag'
                    s.g.hit = hit
                    s.g.orig = JSON.parse(JSON.stringify(hit.m))
                    s.g.q0 = this.toPage(a.clientX, a.clientY)
                    return
                }
                if (pen) {
                    const q = this.toPage(a.clientX, a.clientY)
                    s.drawing = [[q.x, q.y]]
                }
            },
            tm(e) {
                const s = this.S()
                if (!s || !s.g) return
                e.preventDefault()
                const t = e.touches
                // 拡大の途中で指が1本になったら、残った指で紙を動かす形に切り替える(写真アプリと同じ)
                // [2026-10-24] これがないと、片方の指を先に離したあとの動きで位置が計算できなくなり、紙の左上に戻っていた
                if (s.g.kind == 'pinch' && t.length == 1) { this.toPan(t[0]); return }
                if (s.g.kind == 'pinch' && t.length >= 2) {
                    const a = t[0], b = t[1]
                    const ax = num(a.clientX, 0), ay = num(a.clientY, 0), bx = num(b.clientX, 0), by = num(b.clientY, 0)
                    const d = Math.hypot(ax - bx, ay - by)
                    const mx = (ax + bx) / 2, my = (ay + by) / 2
                    const ns = Math.min(MAXS, Math.max(1, s.g.s0 * d / s.g.d))
                    if (!isFinite(s.W) || !isFinite(s.left)) this.layout(false)
                    const rc = this.rect()
                    // 指の間の点が、拡大しても同じ場所にとどまるように
                    const ox = s.g.mx - rc.left - s.left, oy = s.g.my - rc.top - s.top
                    s.s = ns
                    s.tx = mx - rc.left - s.left - (ox - s.g.tx0) * (ns / s.g.s0)
                    s.ty = my - rc.top - s.top - (oy - s.g.ty0) * (ns / s.g.s0)
                    if (!isFinite(s.tx) || !isFinite(s.ty)) this.report('pinch', new Error('mx=' + mx + ' rcL=' + rc.left + ' L=' + s.left + ' tx0=' + s.g.tx0 + ' s0=' + s.g.s0))
                    this.clamp()
                    this.draw()
                    return
                }
                if (!t.length) return
                const a = t[0]
                const dxp = num(a.clientX, 0) - s.g.x0, dyp = num(a.clientY, 0) - s.g.y0
                if (Math.abs(dxp) > 6 || Math.abs(dyp) > 6) s.g.moved = true
                if (s.g.kind == 'crop') {
                    const q = this.toPage(a.clientX, a.clientY)
                    const dx = q.x - s.g.q0.x, dy = q.y - s.g.q0.y
                    const c = s.g.c0.slice(), h = s.g.h, MIN = 0.08
                    if (h == 'move') {
                        c[0] = Math.min(1 - c[2], Math.max(0, c[0] + dx))
                        c[1] = Math.min(1 - c[3], Math.max(0, c[1] + dy))
                    } else {
                        if (h.indexOf('l') != -1) { const nx = Math.min(c[0] + c[2] - MIN, Math.max(0, c[0] + dx)); c[2] += c[0] - nx; c[0] = nx }
                        if (h.indexOf('r') != -1) c[2] = Math.min(1 - c[0], Math.max(MIN, c[2] + dx))
                        if (h.indexOf('t') != -1) { const ny = Math.min(c[1] + c[3] - MIN, Math.max(0, c[1] + dy)); c[3] += c[1] - ny; c[1] = ny }
                        if (h.indexOf('b') != -1) c[3] = Math.min(1 - c[1], Math.max(MIN, c[3] + dy))
                    }
                    s.crop = c
                    this.draw()
                    return
                }
                if (s.g.kind == 'drag') {
                    if (!s.g.moved) return
                    const q = this.toPage(a.clientX, a.clientY)
                    const dx = q.x - s.g.q0.x, dy = q.y - s.g.q0.y
                    const o = s.g.orig, h = s.g.hit.h
                    const m = s.marks.find((x) => x.id == o.id)
                    if (!m) return
                    if (h === undefined) {
                        if (o.points) m.points = o.points.map((p) => [p[0] + dx, p[1] + dy])
                        else { m.x = o.x + dx; m.y = o.y + dy }
                    } else if (h === 0 || h === 1) {
                        m.points = o.points.map((p, i) => (i == h ? [q.x, q.y] : p))
                    } else {
                        // 四隅:枠の大きさを変える(自由線は中の点もいっしょに伸び縮み)
                        const b = bbox(o.points)
                        const nb = { l: b.l, t: b.t, r: b.r, b: b.b }
                        if (h.indexOf('l') != -1) nb.l = Math.min(q.x, b.r - 0.01)
                        if (h.indexOf('r') != -1) nb.r = Math.max(q.x, b.l + 0.01)
                        if (h.indexOf('t') != -1) nb.t = Math.min(q.y, b.b - 0.01)
                        if (h.indexOf('b') != -1) nb.b = Math.max(q.y, b.t + 0.01)
                        if (o.shape == 'check') {
                            // チェックは縦横比を変えず、大きさだけ変える
                            const ax = h.indexOf('l') != -1 ? b.r : b.l, ay = h.indexOf('t') != -1 ? b.b : b.t
                            const w0 = (b.r - b.l) || 1e-6, h0 = (b.b - b.t) || 1e-6
                            const k = Math.max(0.02 / Math.max(w0, h0), Math.abs(q.x - ax) / w0, Math.abs(q.y - ay) / h0)
                            const nw = w0 * k, nh = h0 * k
                            const l = h.indexOf('l') != -1 ? ax - nw : ax, tt = h.indexOf('t') != -1 ? ay - nh : ay
                            m.points = [[l, tt], [l + nw, tt + nh]]
                        } else if (o.shape == 'free' || !o.shape) {
                            const sx = (nb.r - nb.l) / ((b.r - b.l) || 1e-6), sy = (nb.b - nb.t) / ((b.b - b.t) || 1e-6)
                            m.points = o.points.map((p) => [nb.l + (p[0] - b.l) * sx, nb.t + (p[1] - b.t) * sy])
                        } else {
                            m.points = [[nb.l, nb.t], [nb.r, nb.b]]
                        }
                    }
                    this.draw()
                    return
                }
                if (s.drawing) {
                    const q = this.toPage(a.clientX, a.clientY)
                    const last = s.drawing[s.drawing.length - 1]
                    if (Math.hypot(q.x - last[0], q.y - last[1]) > 0.0015 / s.s) {
                        s.drawing.push([Math.min(1, Math.max(0, q.x)), Math.min(1, Math.max(0, q.y))])
                        this.draw()
                    }
                    return
                }
                if (s.s > 1.001) {
                    if (!isFinite(dxp) || !isFinite(dyp)) return
                    s.tx = s.g.tx0 + dxp
                    s.ty = s.g.ty0 + dyp
                    this.clamp()
                    this.draw()
                }
            },
            // 残った1本の指で、今の位置から紙を動かす(押しただけ・2回押しとは扱わない)
            toPan(a) {
                const s = this.S()
                s.g = { kind: 'one', x0: num(a.clientX, 0), y0: num(a.clientY, 0), tx0: num(s.tx, 0), ty0: num(s.ty, 0), t0: 0, moved: true, fromPinch: true }
            },
            te(e) {
                const s = this.S()
                if (!s || !s.g) return
                const g = s.g
                if (g.kind == 'pinch') {
                    if (e.touches && e.touches.length == 1) { this.toPan(e.touches[0]); this.zoomOut(); return }
                    if (!e.touches || e.touches.length == 0) { s.g = null; this.zoomOut() }
                    return
                }
                s.g = null
                const c = (e.changedTouches && e.changedTouches[0]) || {}
                if (g.kind == 'crop') {
                    const k = (s.crop || [0, 0, 1, 1]).map((v) => Math.round(v * 10000) / 10000)
                    if (this.owner) this.owner.callMethod('onCrop', k)
                    return
                }
                if (this.ed()) {
                    // 画像編集中は、書き込みを押しても何もしない
                    return
                }
                if (g.kind == 'drag') {
                    if (g.moved) {
                        const m = s.marks.find((x) => x.id == g.orig.id)
                        if (m) this.owner.callMethod('onMarkChange', m.points ? { id: m.id, points: m.points } : { id: m.id, x: m.x, y: m.y })
                    } else {
                        const q = this.toPage(g.x0, g.y0)
                        this.owner.callMethod('onTap', { x: q.x, y: q.y, mark: g.hit.m.id })
                    }
                    return
                }
                if (s.drawing) {
                    const pts = s.drawing
                    s.drawing = null
                    this.draw()
                    if (pts.length >= 2 && g.moved) {
                        this.owner.callMethod('onStroke', { points: pts.map((p) => [Math.round(p[0] * 100000) / 100000, Math.round(p[1] * 100000) / 100000]) })
                    } else {
                        const q = this.toPage(g.x0, g.y0)
                        const hit = this.hit(g.x0, g.y0)
                        if (hit) this.owner.callMethod('onTap', { x: q.x, y: q.y, mark: hit.m.id })
                    }
                    return
                }
                const dx = num(c.clientX, g.x0) - g.x0, dy = num(c.clientY, g.y0) - g.y0
                if (!g.moved && Date.now() - g.t0 < 400) {
                    const now = Date.now()
                    // 2回すばやく押したら 拡大 ⇔ 等倍(見るだけのとき)
                    if (!s.p.tool && this.lastTap && now - this.lastTap.t < 300 && Math.hypot(g.x0 - this.lastTap.x, g.y0 - this.lastTap.y) < 30) {
                        this.lastTap = null
                        if (s.s > 1.01) this.animTo(1, 0, 0)
                        else {
                            const rc = this.rect()
                            const ox = g.x0 - rc.left - s.left, oy = g.y0 - rc.top - s.top
                            const keep = { s: s.s, tx: s.tx, ty: s.ty }
                            s.s = 2.5; s.tx = ox - ox * 2.5; s.ty = oy - oy * 2.5
                            this.clamp()
                            const to = { s: s.s, tx: s.tx, ty: s.ty }
                            s.s = keep.s; s.tx = keep.tx; s.ty = keep.ty
                            this.animTo(to.s, to.tx, to.ty)
                        }
                        return
                    }
                    this.lastTap = { t: now, x: g.x0, y: g.y0 }
                    const hit = this.hit(g.x0, g.y0)
                    const q = this.toPage(g.x0, g.y0)
                    if (!q.inside && !hit) return
                    const fire = () => this.owner.callMethod('onTap', { x: Math.min(1, Math.max(0, q.x)), y: Math.min(1, Math.max(0, q.y)), mark: hit ? hit.m.id : 0 })
                    // 見るだけのときの1回押しは少し待つ(2回押しと区別するため)
                    if (!s.p.tool && !hit) setTimeout(() => { if (this.lastTap && this.lastTap.t == now) fire() }, 280)
                    else fire()
                    return
                }
                // 等倍のときの左右スワイプでページ送り(見るだけのとき)
                if (!s.p.tool && s.s <= 1.001 && Math.abs(dx) > 60 && Math.abs(dy) < 80) {
                    this.owner.callMethod('onSwipe', dx < 0 ? 1 : -1)
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    .kv { position: fixed; left: 0; top: 0; right: 0; bottom: 0; background: #1C1C1E; color: #FFFFFF; overflow: hidden; }
    .kv-hover { opacity: 0.6; }
    .kv-nav { position: absolute; left: 0; right: 0; top: 0; height: 52px; display: flex; align-items: center; justify-content: center; background: rgba(28, 28, 30, 0.94); z-index: 5; box-sizing: content-box; }
    .kv-btn { position: absolute; bottom: 9px; width: 34px; height: 34px; border-radius: 50%; background: #3A3A3C; display: flex; align-items: center; justify-content: center; font-size: 15px; }
    .kv-btn.l { left: 14px; }
    .kv-btn.r { right: 14px; }
    .kv-dot { width: 5px; height: 5px; border-radius: 50%; background: #FFFFFF; margin: 0 1.5px; }
    .kv-ur { position: absolute; left: 56px; bottom: 9px; height: 34px; border-radius: 17px; background: #3A3A3C; display: flex; align-items: center; padding: 0 4px; }
    .kv-urb { width: 34px; text-align: center; font-size: 18px; color: #FFFFFF; }
    .kv-urb.off { opacity: 0.3; }
    .kv-ok { position: absolute; right: 14px; bottom: 7px; width: 38px; height: 38px; border-radius: 50%; background: #1A73E8; color: #FFFFFF; font-size: 20px; font-weight: 700; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(26, 115, 232, 0.5); }
    .kv-tt { max-width: 50%; font-size: 16px; font-weight: 700; display: flex; flex-direction: column; align-items: center; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .kv-sub { font-size: 11px; font-weight: 400; color: #AEAEB2; }
    .kv-chip { position: absolute; z-index: 4; background: rgba(0, 0, 0, 0.6); color: #FFFFFF; font-size: 12px; border-radius: 12px; padding: 4px 10px; }
    .kv-chip.l { left: 12px; }
    .kv-chip.r { right: 12px; }
    .kv-hint { position: absolute; left: 50%; transform: translateX(-50%); z-index: 4; background: rgba(24, 128, 56, 0.95); color: #FFFFFF; font-size: 13px; border-radius: 16px; padding: 6px 14px; white-space: nowrap; }
    .kv-stage { position: absolute; left: 0; right: 0; bottom: calc(196px + env(safe-area-inset-bottom)); overflow: hidden; touch-action: none; }
    .kv-wait { position: absolute; left: 50%; top: 45%; transform: translateX(-50%); color: #AEAEB2; font-size: 14px; }
    .kv-panel { position: absolute; left: 0; right: 0; bottom: calc(84px + env(safe-area-inset-bottom)); height: 112px; background: rgba(28, 28, 30, 0.96); border-top: 1px solid #3A3A3C; z-index: 5; display: flex; flex-direction: column; justify-content: center; }
    .kv-row { display: flex; align-items: center; justify-content: center; padding: 0 10px; min-height: 52px; }
    .kv-row.sm { min-height: 44px; }
    .kv-pl { color: #AEAEB2; font-size: 12px; margin-right: 8px; white-space: nowrap; }
    .kv-pl.ml { margin-left: 8px; }
    .kv-tl { width: 28px; height: 28px; border-radius: 14px; border: 2px solid #48484A; margin-right: 8px; box-sizing: border-box; flex-shrink: 0; }
    .kv-tl.on { border-color: #4C9BFF; box-shadow: 0 0 0 2px #4C9BFF; }
    .kv-tl.g { background: #3A3A3C; display: flex; align-items: center; justify-content: center; }
    .kv-w1 { width: 12px; height: 2px; background: #FFFFFF; }
    .kv-w2 { width: 14px; height: 5px; border-radius: 2px; background: #FFFFFF; }
    .kv-step { display: flex; align-items: center; background: #3A3A3C; border-radius: 16px; height: 32px; margin-right: 10px; flex-shrink: 0; }
    .kv-sb { width: 34px; text-align: center; font-size: 20px; color: #FFFFFF; }
    .kv-sv { min-width: 34px; text-align: center; font-size: 15px; font-weight: 700; color: #FFFFFF; }
    .kv-act2 { margin-left: 6px; padding: 6px 12px; border-radius: 14px; background: #3A3A3C; color: #FFFFFF; font-size: 13px; white-space: nowrap; }
    .kv-act2.red { color: #FF6B5E; }
    .kv-shp { display: flex; flex-direction: column; align-items: center; width: 54px; padding: 4px 0; border-radius: 12px; color: #E5E5EA; }
    .kv-shp.on { background: rgba(26, 115, 232, 0.3); color: #4C9BFF; }
    .kv-shp.dim { opacity: 0.45; }
    .kv-shp-i { font-size: 20px; line-height: 26px; }
    .kv-shp-n { font-size: 10px; }
    .kv-thumbs { height: 100%; white-space: nowrap; }
    .kv-th-in { display: inline-flex; align-items: center; height: 100%; padding: 0 14px; }
    .kv-th { position: relative; width: 52px; height: 72px; margin-right: 8px; border-radius: 4px; overflow: hidden; background: #FBFAF6; opacity: 0.65; flex-shrink: 0;
        image { width: 100%; height: 100%; } }
    .kv-th.on { opacity: 1; outline: 2px solid #4C9BFF; }
    .kv-th-n { position: absolute; right: 2px; bottom: 2px; background: rgba(0, 0, 0, 0.55); color: #FFFFFF; font-size: 9px; border-radius: 6px; padding: 0 4px; }
    .kv-th.blank { width: 72px; opacity: 1; background: #3A3A3C; color: #E5E5EA; font-size: 11px; text-align: center; line-height: 1.3; display: flex; align-items: center; justify-content: center; white-space: pre-wrap; }
    .kv-bar { position: absolute; left: 0; right: 0; bottom: 0; height: 84px; padding-bottom: env(safe-area-inset-bottom); background: rgba(28, 28, 30, 0.96); border-top: 1px solid #3A3A3C; display: flex; justify-content: space-around; padding-top: 8px; box-sizing: content-box; z-index: 5; }
    .kv-bt { display: flex; flex-direction: column; align-items: center; color: #E5E5EA; font-size: 11px; width: 66px; white-space: nowrap; }
    .kv-bi { width: 32px; height: 32px; border-radius: 9px; background: #3A3A3C; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; margin-bottom: 3px; }
    .kv-bt.on { color: #4C9BFF; }
    .kv-bt.on .kv-bi { background: rgba(26, 115, 232, 0.3); color: #4C9BFF; }
    /* 下から出る画面の中身 */
    .kv-in { padding: 24rpx 40rpx 0; color: #1D1D1F; }
    .kv-ta { width: 100%; min-height: 120rpx; border: 1px solid #1A73E8; border-radius: 24rpx; padding: 20rpx 24rpx; font-size: 30rpx; font-weight: 700; box-sizing: border-box; }
    .kv-ta.dark { color: #1D1D1F; font-weight: 400; }
    .kv-ta.sm { min-height: 80rpx; flex: 1; }
    .kv-btn1 { margin: 48rpx 40rpx 0; height: 104rpx; border-radius: 28rpx; background: #1A73E8; color: #FFFFFF; font-size: 32rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    .kv-btn1.off { opacity: 0.5; }
    .kv-act-t { font-size: 32rpx; font-weight: 700; white-space: pre-wrap; }
    .kv-who { font-size: 24rpx; color: #6E6E73; margin-top: 12rpx; }
    .kv-tip { font-size: 22rpx; color: #8E8E93; margin-top: 20rpx; line-height: 1.6; }
    .kv-cm { background: #FFFFFF; border: 1px solid #E5E5EA; border-radius: 24rpx; padding: 20rpx 24rpx; margin-bottom: 20rpx; }
    .kv-cm.reply { margin-left: 40rpx; background: #F7F7F9; }
    .kv-cm-who { font-size: 22rpx; color: #6E6E73; display: flex; align-items: center; margin-bottom: 6rpx; }
    .kv-cm-n { width: 36rpx; height: 36rpx; border-radius: 18rpx 18rpx 18rpx 4rpx; background: #F2A900; color: #FFFFFF; font-size: 20rpx; font-weight: 700; display: flex; align-items: center; justify-content: center; margin-right: 10rpx; }
    .kv-cm-r { margin-left: auto; color: #1A73E8; }
    .kv-cm-del { margin-left: auto; color: #D93025; }
    .kv-cm-t { font-size: 28rpx; line-height: 1.6; white-space: pre-wrap; word-break: break-all; }
    .kv-reply { display: flex; align-items: flex-end; }
    .kv-send { margin-left: 16rpx; height: 80rpx; padding: 0 28rpx; border-radius: 20rpx; background: #1A73E8; color: #FFFFFF; font-size: 28rpx; display: flex; align-items: center; }
    .kv-send.off { opacity: 0.5; }
    .kv-tnote { font-size: 24rpx; color: #6E6E73; padding: 24rpx 40rpx 8rpx; line-height: 1.6; }
    .kv-tpls { display: flex; padding: 8rpx 40rpx 0; color: #1D1D1F; }
    .kv-tc { flex: 1; margin-right: 20rpx; border: 1px solid #D2D2D7; border-radius: 24rpx; padding: 20rpx 12rpx; text-align: center; }
    .kv-tc:last-child { margin-right: 0; }
    .kv-tc.on { border-color: #1A73E8; background: rgba(26, 115, 232, 0.10); }
    .kv-tc-img { width: 128rpx; height: 176rpx; background: #FBFAF6; }
    .kv-tc-n { font-size: 24rpx; margin-top: 12rpx; }
    .kv-tc.on .kv-tc-n { color: #1A73E8; font-weight: 700; }
    .kv-tc-s { font-size: 20rpx; color: #6E6E73; }
    .kv-op { display: flex; align-items: center; min-height: 128rpx; margin: 0 40rpx; border-bottom: 1px solid #E5E5EA; color: #1D1D1F; }
    .kv-op-i { width: 76rpx; height: 76rpx; border-radius: 20rpx; background: #EEF4FF; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .kv-op-tt { margin-left: 28rpx; font-size: 30rpx; display: flex; flex-direction: column; }
    .kv-op-s { font-size: 22rpx; color: #6E6E73; margin-top: 4rpx; }
    .kv-op.red { color: #D93025; }
    .kv-op.red .kv-op-i { background: #FCE8E6; }
    .kv-lock { font-size: 22rpx; color: #6E6E73; text-align: center; margin-top: 24rpx; }
    .k-ic { width: 48rpx; height: 48rpx; }
    .k-lock { width: 30rpx; height: 30rpx; vertical-align: -4rpx; margin-right: 8rpx; }
    /* [カスタムエイド 2026-10-24] その場で画像編集 */
    .kv-sv2 { position: absolute; right: 14px; bottom: 9px; height: 34px; padding: 0 16px; border-radius: 17px; background: #1A73E8; color: #FFFFFF; display: flex; align-items: center; font-size: 15px; font-weight: 700; }
    .kv-sv2.off { opacity: 0.5; }
    /* 画像編集中は下のパネルを低くして、紙を表示する場所を広げる(明るさのときだけスライダーのぶん高く) */
    .kv-stage.ed { bottom: calc(148px + env(safe-area-inset-bottom)); }
    .kv-stage.eda { bottom: calc(196px + env(safe-area-inset-bottom)); }
    .kv-epanel.adj { height: 184px; }
    .kv-epanel { position: absolute; left: 0; right: 0; bottom: 0; height: 136px; padding: 12px 16px env(safe-area-inset-bottom); box-sizing: content-box; background: #1C1C1E; border-top: 1px solid #3A3A3C; z-index: 5; display: flex; flex-direction: column; justify-content: space-between; }
    .kv-enote { color: #AEAEB2; font-size: 13px; text-align: center; line-height: 1.5; padding: 4px 8px 0; }
    .kv-eadj { display: flex; flex-direction: column; justify-content: center; }
    .kv-esl { display: flex; align-items: center; height: 44px; }
    .kv-esl-l { width: 84px; font-size: 13px; color: #E5E5EA; }
    .kv-eslider { flex: 1; margin: 0 8px; }
    .kv-esl-v { width: 34px; text-align: right; font-size: 13px; color: #AEAEB2; }
    .kv-etools { display: flex; justify-content: space-between; padding-bottom: 14px; }
    .kv-et { flex: 1; margin: 0 4px; height: 64px; border-radius: 12px; background: #3A3A3C; color: #E5E5EA; font-size: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; box-sizing: border-box; }
    .kv-et.on { background: rgba(76, 155, 255, 0.16); border: 1.5px solid #4C9BFF; color: #FFFFFF; font-weight: 700; }
    .kv-et.dim { opacity: 0.4; }
    .kv-ei { font-size: 20px; line-height: 1; margin-bottom: 6px; }
</style>
