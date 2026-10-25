<template>
    <view class="contain cs-chat">
        <scroll-view class="chatContent">
            <view class="msgItem" v-for="(item, index) in messageArr" :key="item.id">
                <view class="msgDate" v-if="item.Showdate">{{ item.Showdate }}　({{ item.week }})</view>
                <view class="userItem" :style="item.from_id == myId ? 'flex-direction: row-reverse' : ''">
                    <view>
                        <view class="avatar">
                            <image :src="(item.from_id == myId ? myAvatar : targetAvatar) || defaultImage" mode="aspectFill"></image>
                        </view>
                        <view class="chatName">{{ item.from_id == myId ? myName : targetName }}</view>
                    </view>
                    <view class="detail" :style="item.from_id == myId ? 'flex-direction: row-reverse' : ''">
                        <view v-if="item.type == 1" class="msgDetail" :class="{ 'cs-me': item.from_id == myId }" :style="item.from_id == myId ? 'background:#87FA7D' : ''">
                            <text style="white-space: pre-wrap;">{{ item.message }}</text>
                        </view>
                        <view v-if="item.type == 2" class="msgDetailImage">
                            <image @click="previewImage(item.message)" :src="item.message" mode="widthFix" @load="onImgLoad"></image>
                        </view>
                        <view style="display: flex;align-items: center;">
                            <view class="sendTime">{{ item.time }}</view>
                            <view class="delas" v-if="item.from_id == myId && isNum(item.id)" @click.stop="delthis(item)">
                                <image src="../../../static/svg/delta.svg" mode=""></image>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <!-- [カスタムエイド 2026-10-25] キーボードが出ている間は、その高さの分だけ下に余白を足す -->
            <view class="blank" :style="kbH ? 'height:' + (blankBase + kbH) + 'px' : ''"></view>
        </scroll-view>
        <view class="sendBox" :style="{bottom: h > 0 ? h + 'px' : '0'}">
            <view class="boxLeft">
                <image src="../../../static/images/camera.png" mode="aspectFit" @click="chooseCamear" v-if="!fs"></image>
                <image src="../../../static/images/album.png" mode="aspectFit" @click="chooseImage" v-if="!fs"></image>
                <image src="../../../static/images/showmore.png" mode="aspectFit" v-if="fs" @touchend.prevent="fs = false"></image>
            </view>
            <view class="boxRight">
                <textarea placeholder="メッセージを入力"
                    :style="'border: 2upx solid #999;min-height:65upx;max-height:400upx;overflow-y:auto;border-radius:30upx;box-sizing:border-box;padding: 10upx 15upx;width:100%;' + (taH ? 'height:' + taH + 'px;' : '')"
                    v-model="inputText" adjust-position="false" @linechange="onLine" @focus="onFocus" @blur="onBlur"></textarea>
            </view>
            <view class="boxRights" v-if="inputText != ''">
                <image src="../../../static/images/send.png" mode="" @touchend.prevent="sendMsg"></image>
            </view>
        </view>
    </view>
</template>

<script>
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
    import { getStaffDmMessages, sendStaffDm, readStaffDm, deleteStaffDm } from '@/api/index.js'
    export default {
        data() {
            return {
                toId: 0,
                myId: 0,
                myName: '',
                myAvatar: '',
                targetName: '',
                targetAvatar: '',
                messageArr: [],
                history: [],
                currentMessages: [],
                inputText: '',
                defaultImage: '../../../static/images/avatar.png',
                fs: false,
                h: 0,
                taH: 0,
                kbH: 0, // [カスタムエイド 2026-10-25] キーボードの高さ(px)
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
        onLoad(options) {
            let admin       = uni.getStorageSync('admin')
            this.myId      = admin.id
            this.myName    = admin.message_name || admin.name
            this.myAvatar  = admin.avatar
            this.toId      = parseInt(options.to_id)
            this.targetName = decodeURIComponent(options.name || '')
            this.targetAvatar = options.avatar ? decodeURIComponent(options.avatar) : ''
            uni.setNavigationBarTitle({ title: this.targetName })
            this.loadHistory()
            this.listenSocket()
        },
        onUnload() {
            uni.$off('getPositonsOrder', this._socketHandler)
            readStaffDm({ from_id: this.toId }).catch(() => {})
        },
        onPullDownRefresh() {
            this.loadMore()
        },
        methods: {
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
                        deleteStaffDm(this.toId, item.id).then(res => {
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
            // 入力欄の高さを8行まで追従させ、それ以上は固定して内部スクロールに任せる
            onLine(e) {
                if (e.detail && e.detail.lineCount <= 8) {
                    this.taH = e.detail.height
                }
            },
            listenSocket() {
                this._socketHandler = (result) => {
                    if (result.type == 'staff_dm_delete' && result.data) {
                        if (result.data.from_id == this.toId) this.removeMsg(result.data.id)
                        return
                    }
                    if (result.type == 'staff_dm') {
                        result.data.forEach(msg => {
                            if (
                                (msg.from_id == this.toId && msg.to_id == this.myId) ||
                                (msg.from_id == this.myId && msg.to_id == this.toId)
                            ) {
                                this.currentMessages.push(msg)
                                this.rebuildArr()
                                this.scrollBottom()
                                readStaffDm({ from_id: this.toId }).catch(() => {})
                            }
                        })
                    }
                }
                uni.$on('getPositonsOrder', this._socketHandler)
            },
            loadHistory() {
                uni.showLoading({ title: '読み込み中' })
                getStaffDmMessages(this.toId, {}).then(res => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        this.history = res.data.messages
                        // grab target avatar from first message
                        let targetMsg = this.history.find(m => m.from_id == this.toId)
                        // DMメッセージは avatar を持たないため、値がある時だけ上書き
                        // (一覧から渡された相手アイコンを undefined で潰さない) 2026.7.29
                        if (targetMsg && targetMsg.avatar) this.targetAvatar = targetMsg.avatar
                        this.rebuildArr()
                        this.scrollBottom()
                    }
                }).catch(() => { uni.hideLoading() })
            },
            loadMore() {
                if (!this.history.length) { uni.stopPullDownRefresh(); return }
                getStaffDmMessages(this.toId, { before_id: this.history[0].id }).then(res => {
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
                this.inputText = ''
                this.taH = 0
                let now = new Date()
                let pad = n => n < 10 ? '0' + n : n
                let date = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate())
                let time = pad(now.getHours()) + ':' + pad(now.getMinutes())
                let week = ['日', '月', '火', '水', '木', '金', '土'][now.getDay()]
                sendStaffDm({ to_id: this.toId, type: 1, message: text }).then(res => {
                    if (res.code == 200) {
                        // 送信メッセージを即座に画面へ反映（ソケットのエコーを待たない）
                        this.currentMessages.push({
                            id: (res.data && res.data.id) ? res.data.id : ('local_' + now.getTime()),
                            from_id: this.myId,
                            to_id: this.toId,
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
                                sendStaffDm({ to_id: this.toId, type: 2, message: re.data.path }).then(r => {
                                    // 自分が送った画像も即時表示（旧：再読み込みまで表示されなかった）
                                    if (r.code == 200) this.pushSent(r, { from_id: this.myId, to_id: this.toId, type: 2, message: re.data.path })
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
                this.$nextTick(() => {
                    setTimeout(() => {
                        const q = uni.createSelectorQuery()
                        q.selectAll('.msgItem').boundingClientRect()
                        q.select('.sendBox').boundingClientRect()
                        q.selectViewport().scrollOffset()
                        q.exec((r) => {
                            const items = (r && r[0]) || []
                            const box = r && r[1]
                            const vp = (r && r[2]) || {}
                            if (!items.length || !box) {
                                uni.pageScrollTo({ scrollTop: 0, duration: 0 })
                                return
                            }
                            const last = items[items.length - 1]
                            const top = (vp.scrollTop || 0) + last.bottom - (box.top - uni.upx2px(24))
                            uni.pageScrollTo({ scrollTop: Math.max(0, top), duration: 0 })
                        })
                    }, 50)
                })
            },
        }
    }
</script>

<style lang="scss">
.contain {
    display: flex; flex-direction: column; height: auto;
    .chatContent {
        flex: 1; background: #fff;
        box-sizing: border-box; padding: 0 32upx;
        .userItem {
            display: flex;
            .avatar {
                width: 60upx; height: 60upx; border-radius: 50%;
                flex-shrink: 0; overflow: hidden; border: 2rpx solid #aaa;
                image { width: 100%; height: 100%; }
            }
            .detail {
                display: flex; align-items: flex-end;
                .msgDetail {
                    max-width: 492upx; box-sizing: border-box; padding: 20upx;
                    font-size: 32upx; color: #1D1D1F;
                    background: rgba(143,168,248,0.2);
                    border-radius: 20upx; margin: 0 8upx; word-wrap: break-word;
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
        width: 100vw; min-height: 122upx; flex-shrink: 0; background: #F9F9F9;
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
