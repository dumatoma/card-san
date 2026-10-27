<template>
    <view class="contain cs-chat">
        <scroll-view class="chatContent">
            <view class="msgItem" v-for="(item, index) in messageArr" :key="item.id">
                <view class="msgDate" v-if="item.Showdate">{{ item.Showdate }}　({{ item.week }})</view>
                <view class="userItem" :style="item.admin_id == myId ? 'flex-direction: row-reverse' : ''">
                    <view>
                        <view class="avatar">
                            <image :src="item.avatar || defaultImage" mode="aspectFill"></image>
                        </view>
                        <view class="chatName">{{ item.name }}</view>
                    </view>
                    <view class="detail" :style="item.admin_id == myId ? 'flex-direction: row-reverse' : ''">
                        <view v-if="item.type == 1" class="msgDetail" :class="{ 'cs-me': item.admin_id == myId }" :style="item.admin_id == myId ? 'background:#87FA7D' : ''">
                            <text style="white-space: pre-wrap;">{{ item.message }}</text>
                        </view>
                        <view v-if="item.type == 2" class="msgDetailImage">
                            <image @click="previewImage(item.message)" :src="item.message" mode="widthFix" @load="onImgLoad"></image>
                        </view>
                        <view style="display: flex;align-items: center;">
                            <view class="sendTime">{{ item.time }}</view>
                            <view class="delas" v-if="item.admin_id == myId && isNum(item.id)" @click.stop="delthis(item)">
                                <image src="../../../static/svg/delta.svg" mode=""></image>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <!-- [カスタムエイド 2026-10-25] キーボードが出ている間は、その高さの分だけ下に余白を足す -->
            <view class="blank" :style="'height:' + ((csBoxPad || blankBase) + kbH) + 'px'"></view>
        </scroll-view>
        <view class="cs-ime" style="display:none" :prop="csImeTick" :change:prop="csIme.flush"></view>
        <view class="sendBox" :style="{bottom: h > 0 ? h + 'px' : '0'}">
            <view class="boxLeft">
                <image src="../../../static/images/camera.png" mode="aspectFit" @click="chooseCamear" v-if="!fs"></image>
                <image src="../../../static/images/album.png" mode="aspectFit" @click="chooseImage" v-if="!fs"></image>
                <image src="../../../static/images/showmore.png" mode="aspectFit" v-if="fs" @touchend.prevent="fs = false"></image>
            </view>
            <view class="boxRight">
                <textarea placeholder="メッセージを入力"
                    :style="csTaStyle + 'height:' + ((inputText && taH) ? taH : taH1) + 'px;'"
                    v-model="inputText" :ignore-composition-event="false" @input="csOnInput" adjust-position="false" @linechange="onLine" @focus="onFocus" @blur="onBlur"></textarea>
            </view>
            <view class="boxRights" v-if="inputText != ''">
                <image src="../../../static/images/send.png" mode="" @touchend.prevent="sendMsg"></image>
            </view>
        </view>
        <!-- メンバー一覧ボタン -->
        <view class="memberBtn" @click="toMemberList">
            <text>メンバー</text>
        </view>
    </view>
</template>

<script>
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
    import { csMarkSent, csIsLeftover } from '@/utils/csIme.js' // [カスタムエイド 2026-10-26] 変換中のまま送信したとき
    import { csChatScroll } from '@/utils/csChatScroll.js' // [カスタムエイド 2026-10-25] 最新のメッセージを入力欄のすぐ上に(4画面で共通)
    import { csTaH, CS_TA_STYLE } from '@/utils/csTa.js' // [カスタムエイド 2026-10-27] 入力欄の高さ(1行〜6行)
    import { csViewStart, csViewStop } from '@/utils/csViewing.js' // [カスタムエイド 2026-10-27] 開いているチャットの相手からは通知を出さない
    import {
        getStaffRoomMessages,
        sendStaffRoomMessage,
        readStaffRoomMessages,
        getStaffRoom,
        deleteStaffRoomMessage
    } from '@/api/index.js'
    export default {
        data() {
            return {
                myId: 0,
                messageArr: [],
                history: [],
                currentMessages: [],
                inputText: '',
                defaultImage: '../../../static/images/avatar.png',
                fs: false,
                h: 0,
                taH: 0,
                taH1: csTaH(1), // [カスタムエイド 2026-10-27] 1行の高さ
                csTaStyle: CS_TA_STYLE,
                kbH: 0, // [カスタムエイド 2026-10-25] キーボードの高さ(px)
                csImeTick: 0, // [カスタムエイド 2026-10-26] 送信のたびに増やす(画面側の csIme.flush が動く)
                csBoxPad: 0, // [カスタムエイド 2026-10-25] 一番下の余白に使う入力欄の高さ(px)
                blankBase: uni.upx2px(140), // 下の余白(入力欄の分)
            }
        },
        created() {
            // [カスタムエイド 2026-10-25] お客様とのチャットと同じしくみに。キーボードが出たら、その高さの分だけ下に余白を足し、
            //   最新のメッセージが入力欄のすぐ上に来るようにスクロールする(キーボードに隠れない)
            this._kbCb = (res) => {
                this.fs = res.height > 0
                const kb = res.height > 0 ? res.height : 0
                if (this.kbH != kb) {
                    this.kbH = kb
                    this.scrollBottom()
                }
            }
            uni.onKeyboardHeightChange(this._kbCb)
            // [カスタムエイド 2026-10-25] リアルタイム接続がつながり直したら、その間に届いたメッセージを読み直す
            this._csReady = () => { this.loadHistory() }
            uni.$on('csSocketReady', this._csReady)
        },
        destroyed() {
            if (this._kbCb && uni.offKeyboardHeightChange) uni.offKeyboardHeightChange(this._kbCb) // [カスタムエイド 2026-10-25]
            if (this._csReady) uni.$off('csSocketReady', this._csReady)
        },
        onLoad() {
            let admin = uni.getStorageSync('admin')
            this.myId = admin.id
            this.loadHistory()
            this.setRoomTitle()
        },
        onShow() {
            readStaffRoomMessages().catch(() => {})
            csViewStart(this, 'staff_room') // [カスタムエイド 2026-10-27] スタッフルームを開いている
        },
        onHide() { csViewStop(this) }, // [カスタムエイド 2026-10-27]
        onUnload() {
            csViewStop(this) // [カスタムエイド 2026-10-27]
            readStaffRoomMessages().catch(() => {})
        },
        onPullDownRefresh() {
            this.loadMore()
        },
        mounted() {
            this.listenSocket()
        },
        beforeDestroy() {
            uni.$off('getPositonsOrder', this._socketHandler)
        },
        methods: {
            // [カスタムエイド 2026-10-26] 送信の直後に、変換が確定して入力欄に戻ってきた文字(送った文章の残り)は消す
            csOnInput(e) {
                const v = e && e.detail ? e.detail.value : ''
                // [カスタムエイド 2026-10-26] 残りの文字を消すとき、入力欄の高さも元に戻す(残りの文字で一度広がった高さのままになっていた)
                if (csIsLeftover(this, v)) {
                    this.$nextTick(() => { this.inputText = ''; this.taH = 0 })
                    setTimeout(() => { if (!this.inputText) this.taH = 0 }, 400)
                }
            },
            isNum(id) {
                return id != null && !isNaN(Number(id))
            },
            // 自分の送信メッセージを削除（相手の画面からも消える）。2026-10
            delthis(item) {
                uni.showModal({
                    title: '',
                    content: '選択したメッセージを削除しますか？',
                    confirmText: '削除',
                    confirmColor: '#D93025',
                    cancelText: 'キャンセル',
                    success: (r) => {
                        if (!r.confirm) return
                        deleteStaffRoomMessage(item.id).then(res => {
                            if (res.code == 200) {
                                this.removeMsg(item.id)
                            } else {
                                uni.showToast({ title: res.message || '削除できませんでした', icon: 'none' })
                            }
                        }).catch(() => {})
                    }
                })
            },
            removeMsg(id) {
                this.history = this.history.filter(m => m.id != id)
                this.currentMessages = this.currentMessages.filter(m => m.id != id)
                this.rebuildArr()
            },
            // 送信APIが返す正式なメッセージ（id・日時）で画面に追加
            pushSent(res, fallback) {
                let m = (res.data && res.data.message) ? Object.assign({}, fallback, res.data.message) : fallback
                if (res.data && res.data.id) m.id = res.data.id
                this.currentMessages.push(m)
                this.rebuildArr()
                this.scrollBottom()
            },
            // ヘッダーにルーム名と参加人数を表示（例: CARD SALONスタッフ(3)）
            setRoomTitle() {
                getStaffRoom().then(res => {
                    if (res.code == 200) {
                        let name = res.data.room.name || 'スタッフルーム'
                        let count = res.data.members ? res.data.members.length : 0
                        uni.setNavigationBarTitle({ title: name + '(' + count + ')' })
                    }
                }).catch(() => {})
            },
            // 入力欄の高さを8行まで追従させ、それ以上は固定して内部スクロールに任せる
            onLine(e) {
                // [カスタムエイド 2026-10-27] 空なら1行。改行するたびに伸ばし、6行まで(7行目からは中でスクロール)
                if (!this.inputText) { this.taH = 0; return }
                this.taH = csTaH(e && e.detail ? e.detail.lineCount : 1)
            },
            listenSocket() {
                this._socketHandler = (result) => {
                    if (result.type == 'staff_room_delete' && result.data) {
                        this.removeMsg(result.data.id)
                        return
                    }
                    if (result.type == 'staff_room_message') {
                        result.data.forEach(msg => {
                            this.currentMessages.push(msg)
                        })
                        this.rebuildArr()
                        this.scrollBottom()
                        readStaffRoomMessages().catch(() => {})
                    }
                }
                uni.$on('getPositonsOrder', this._socketHandler)
            },
            loadHistory() {
                uni.showLoading({ title: '読み込み中' })
                getStaffRoomMessages({}).then(res => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        this.history = res.data.messages
                        this.rebuildArr()
                        this.scrollBottom()
                    }
                }).catch(() => { uni.hideLoading() })
            },
            loadMore() {
                if (!this.history.length) { uni.stopPullDownRefresh(); return }
                getStaffRoomMessages({ before_id: this.history[0].id }).then(res => {
                    uni.stopPullDownRefresh()
                    if (res.code == 200 && res.data.messages.length) {
                        this.history = [...res.data.messages, ...this.history]
                        this.rebuildArr()
                    }
                }).catch(() => { uni.stopPullDownRefresh() })
            },
            rebuildArr() {
                // id重複を除去（楽観的追加とソケットエコーの二重表示を防ぐ）
                let merged = [...this.history, ...this.currentMessages]
                let seen = {}
                let arr = []
                for (let m of merged) {
                    if (m.id != null && seen[m.id]) continue
                    if (m.id != null) seen[m.id] = true
                    arr.push(m)
                }
                for (let i = 0; i < arr.length; i++) {
                    arr[i].Showdate = (i == 0 || arr[i].date != arr[i-1].date) ? arr[i].date : ''
                }
                this.messageArr = arr
            },
            sendMsg() {
                if (!this.inputText.trim()) return
                let text = this.inputText
                csMarkSent(this, text) // [カスタムエイド 2026-10-26]
                this.inputText = ''
                this.csImeTick++ // [カスタムエイド 2026-10-26] 画面の入力欄の変換を確定させて空にする
                this.taH = 0
                let admin = uni.getStorageSync('admin')
                let now = new Date()
                let pad = n => n < 10 ? '0' + n : n
                let date = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate())
                let time = pad(now.getHours()) + ':' + pad(now.getMinutes())
                let week = ['日', '月', '火', '水', '木', '金', '土'][now.getDay()]
                sendStaffRoomMessage({ type: 1, message: text }).then(res => {
                    if (res.code == 200) {
                        // 送信メッセージを即座に画面へ反映（ソケットのエコーを待たない）
                        this.currentMessages.push({
                            id: (res.data && res.data.id) ? res.data.id : ('local_' + now.getTime()),
                            admin_id: this.myId,
                            name: admin.message_name || admin.name,
                            avatar: admin.avatar,
                            type: 1,
                            message: text,
                            date: date,
                            time: time,
                            week: week
                        })
                        this.rebuildArr()
                        this.scrollBottom()
                    }
                }).catch(() => {})
            },
            chooseImage() {
                let that = this
                uni.hideKeyboard()
                pickImage({ count: 1, sourceType: ['album'], success(res) { that.uploadAndSend(res.tempFilePaths[0]) } })
            },
            chooseCamear() {
                let that = this
                uni.hideKeyboard()
                pickImage({ count: 1, sourceType: ['camera'], success(res) { that.uploadAndSend(res.tempFilePaths[0]) } })
            },
            uploadAndSend(filePath) {
                uni.uploadFile({
                    url: this.$baseUrl + '/tool/upload/file',
                    filePath,
                    name: 'file',
                    success: (result) => {
                        if (result.errMsg == 'uploadFile:ok') {
                            let re = JSON.parse(result.data)
                            if (re.code == 200) {
                                sendStaffRoomMessage({ type: 2, message: re.data.path }).then(r => {
                                    // 自分が送った画像も即時表示（旧：再読み込みまで表示されなかった）
                                    let admin = uni.getStorageSync('admin')
                                    if (r.code == 200) this.pushSent(r, { admin_id: this.myId, name: admin.message_name || admin.name, avatar: admin.avatar, type: 2, message: re.data.path })
                                }).catch(() => {})
                            }
                        }
                    }
                })
            },
            previewImage(url) {
                uni.previewImage({ current: 0, urls: [url] })
            },
            onFocus() {
                // [カスタムエイド 2026-10-25] スクロールはキーボードが出きったとき(created の _kbCb)に行う
            },
            onBlur() {
                let that = this
                uni.getSystemInfo({ success(res) { that.h = res.safeAreaInsets.bottom } })
            },
            // [カスタムエイド 2026-10-25] 一番下へ。以前はページの高さいっぱいまでスクロールしていたため、メッセージと入力欄が
            //   大きく離れたり、送信後に画面が真っ白に見えたりすることがあった(お客様とのチャットと同じ原因)。
            //   最新のメッセージの下が入力欄のすぐ上(12px)に来るように合わせる(お客様とのチャットと同じ処理)
            // [カスタムエイド 2026-10-25] 写真が読み込まれて高さが変わったとき、開いた直後・新しいメッセージの直後(3秒以内)なら一番下に合わせ直す
            onImgLoad() {
                if (this._autoUntil && Date.now() < this._autoUntil) this.scrollBottom()
            },
            scrollBottom() {
                this._autoUntil = this._autoUntil && Date.now() < this._autoUntil ? this._autoUntil : Date.now() + 3000
                csChatScroll(this) // [カスタムエイド 2026-10-25] 入力欄の位置を測らず、計算で合わせる(utils/csChatScroll.js)
            },
            toMemberList() {
                uni.navigateTo({ url: './memberList' })
            }
        }
    }
</script>

<style lang="scss">
.contain {
    display: flex;
    flex-direction: column;
    height: auto;
    .chatContent {
        flex: 1;
        background: #fff;
        box-sizing: border-box;
        padding: 0 32upx;
        .userItem {
            display: flex;
            .avatar {
                width: 60upx; height: 60upx;
                border-radius: 50%; flex-shrink: 0; overflow: hidden;
                border: 2rpx solid #aaa;
                image { width: 100%; height: 100%; }
            }
            .detail {
                display: flex; align-items: flex-end;
                .msgDetail {
                    max-width: 492upx; box-sizing: border-box; padding: 20upx;
                    font-size: 32upx; color: #1D1D1F;
                    background: rgba(143,168,248,0.2);
                    border-radius: 20upx; margin: 0 8upx;
                    word-wrap: break-word;
                }
                .msgDetailImage {
                    width: 492rpx; border-radius: 20upx; margin: 0 10rpx;
                    image { width: 100%; border-radius: 20upx; }
                }
                .sendTime { font-size: 20upx; color: #86868B; }
            }
        }
    }
    .msgItem {
        margin-bottom: 24upx;
        .msgDate { text-align: center; padding: 24upx 0; font-size: 24upx; color: #aaa; }
    }
    .chatName { font-size: 24upx; color: #aaa; }
    .blank { width: 100vw; height: 122upx; }
    .sendBox {
        width: 100vw; min-height: 122upx; flex-shrink: 0;
        background: #F9F9F9;
        box-shadow: 0px -1upx 0px 2upx rgba(0,0,0,0.3);
        display: flex; align-items: center;
        box-sizing: border-box; padding: 20upx 32upx 32upx;
        position: fixed; bottom: 0; left: 0;
        justify-content: space-between;
        .boxLeft {
            display: flex; align-items: center;
            image { width: 54upx; height: 54upx; margin-right: 28upx; }
        }
        .boxRight { flex: 1; }
        .boxRights { margin-left: 20upx; image { width: 54upx; height: 54upx; } }
    }
    .memberBtn {
        position: fixed; top: 120upx; right: 0;
        background: #007AFF; color: #fff;
        font-size: 24upx; padding: 14upx 20upx;
        border-radius: 20upx 0 0 20upx;
        z-index: 100;
    }
}
    .delas {
        width: 36rpx;
        height: 36rpx;
        margin: 0 12rpx;
        flex-shrink: 0;
        image {
            width: 100%;
            height: 100%;
        }
    }

    /* ===== [カスタムエイド 2026-10-20] 吹き出しをやわらかく(管理ウェブサイトのチャットと同じ考え方)・行間を LINE に近く ===== */
    .contain.cs-chat {
        min-height: 100vh;
        background: #F7F7F9;
    }
    .contain.cs-chat .chatContent {
        background-color: transparent;
    }
    /* 相手:白い吹き出しに細い線。自分:緑(色は今までどおり)。しっぽ側の角だけ小さく */
    .contain.cs-chat .chatContent .userItem .detail .msgDetail {
        background: #FFFFFF;
        border: 1px solid #E5E5EA;
        border-radius: 32rpx 32rpx 32rpx 8rpx;
        padding: 18rpx 26rpx;
        line-height: 1.45;
        box-sizing: border-box;
    }
    .contain.cs-chat .chatContent .userItem .detail .msgDetail.cs-me {
        border-color: transparent;
        border-radius: 32rpx 32rpx 8rpx 32rpx;
    }
    .contain.cs-chat .msgDetailImage,
    .contain.cs-chat .chatContent .userItem .detail .msgDetailImage {
        border-radius: 24rpx;
        overflow: hidden;
    }
    /* 日付:小さな灰色の帯 */
    .contain.cs-chat .chatContent .msgItem .msgDate {
        display: table;
        margin: 24rpx auto 8rpx;
        padding: 6rpx 24rpx;
        border-radius: 20rpx;
        background: #ECECF0;
        color: #6E6E73;
        font-size: 24rpx;
        line-height: 1.4;
    }

    /* [カスタムエイド 2026-10-25 send-align-20261025] 送信ボタン(紙飛行機)と入力欄の横の中心をそろえる。
       画像・入力欄が文字と同じ並び方(下に文字のすき間ができる)になっていて、少し上下にずれていた */
    .sendBox .boxLeft,
    .sendBox .boxRights {
        display: flex;
        align-items: center;
        align-self: center;
    }
    .sendBox .boxLeft image,
    .sendBox .boxRights image {
        display: block;
    }
    .sendBox .boxRight {
        display: flex;
        align-items: center;
    }
    .sendBox .boxRight textarea {
        display: block;
    }
</style>

<!-- [カスタムエイド 2026-10-26] 変換中(下線)のまま送信したとき、画面の入力欄の変換を確定させてから空にする(画面側で直接動く処理)。
     変換が終わるまでは、プログラムから入力欄を空にしても画面に反映されない(Vue の決まり)ため -->
<script module="csIme" lang="renderjs">
    export default {
        methods: {
            flush(n) {
                if (!n) return
                try {
                    var ta = document.querySelector('.sendBox textarea')
                    if (!ta) return
                    var had = document.activeElement === ta
                    ta.blur()
                    ta.value = ''
                    if (had) ta.focus()
                } catch (e) {}
            }
        }
    }
</script>
