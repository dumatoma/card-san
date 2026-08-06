<template>
    <view class="contain">
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
                        <view v-if="item.type == 1" class="msgDetail" :style="item.admin_id == myId ? 'background:#87FA7D' : ''">
                            <text style="white-space: pre-wrap;">{{ item.message }}</text>
                        </view>
                        <view v-if="item.type == 2" class="msgDetailImage">
                            <image @click="previewImage(item.message)" :src="item.message" mode="widthFix"></image>
                        </view>
                        <view class="sendTime">{{ item.time }}</view>
                    </view>
                </view>
            </view>
            <view class="blank"></view>
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
        <!-- メンバー一覧ボタン -->
        <view class="memberBtn" @click="toMemberList">
            <text>メンバー</text>
        </view>
    </view>
</template>

<script>
    import {
        getStaffRoomMessages,
        sendStaffRoomMessage,
        readStaffRoomMessages,
        getStaffRoom
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
            }
        },
        created() {
            uni.onKeyboardHeightChange(res => {
                this.fs = res.height > 0
            })
        },
        onLoad() {
            let admin = uni.getStorageSync('admin')
            this.myId = admin.id
            this.loadHistory()
            this.setRoomTitle()
        },
        onShow() {
            readStaffRoomMessages().catch(() => {})
        },
        onUnload() {
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
                if (e.detail && e.detail.lineCount <= 8) {
                    this.taH = e.detail.height
                }
            },
            listenSocket() {
                this._socketHandler = (result) => {
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
                this.inputText = ''
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
                uni.chooseImage({ count: 1, sourceType: ['album'], success(res) { that.uploadAndSend(res.tempFilePaths[0]) } })
            },
            chooseCamear() {
                let that = this
                uni.hideKeyboard()
                uni.chooseImage({ count: 1, sourceType: ['camera'], success(res) { that.uploadAndSend(res.tempFilePaths[0]) } })
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
                                sendStaffRoomMessage({ type: 2, message: re.data.path }).catch(() => {})
                            }
                        }
                    }
                })
            },
            previewImage(url) {
                uni.previewImage({ current: 0, urls: [url] })
            },
            onFocus() {
                setTimeout(() => { this.scrollBottom() }, 800)
            },
            onBlur() {
                let that = this
                uni.getSystemInfo({ success(res) { that.h = res.safeAreaInsets.bottom } })
            },
            scrollBottom() {
                this.$nextTick(() => {
                    setTimeout(() => {
                        uni.createSelectorQuery().select('.chatContent').boundingClientRect(res => {
                            if (res) uni.pageScrollTo({ scrollTop: res.height, duration: 10 })
                        }).exec()
                    }, 50)
                })
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
</style>
