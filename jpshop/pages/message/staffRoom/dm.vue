<template>
    <view class="contain">
        <scroll-view class="chatContent">
            <view class="msgItem" v-for="(item, index) in messageArr" :key="item.id">
                <view class="msgDate" v-if="item.Showdate">{{ item.Showdate }}　({{ item.week }})</view>
                <view class="userItem" :style="item.from_id == myId ? 'flex-direction: row-reverse' : ''">
                    <view>
                        <view class="avatar">
                            <image :src="item.from_id == myId ? myAvatar : targetAvatar" mode="aspectFill"></image>
                        </view>
                        <view class="chatName">{{ item.from_id == myId ? myName : targetName }}</view>
                    </view>
                    <view class="detail" :style="item.from_id == myId ? 'flex-direction: row-reverse' : ''">
                        <view v-if="item.type == 1" class="msgDetail" :style="item.from_id == myId ? 'background:#87FA7D' : ''">
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
                <input placeholder="メッセージを入力"
                    style="border: 2upx solid #999;height:65upx;border-radius:30upx;box-sizing:border-box;padding: 0 15upx;"
                    v-model="inputText" adjust-position="false" @focus="onFocus" @blur="onBlur" />
            </view>
            <view class="boxRights" v-if="inputText != ''">
                <image src="../../../static/images/send.png" mode="" @touchend.prevent="sendMsg"></image>
            </view>
        </view>
    </view>
</template>

<script>
    import { getStaffDmMessages, sendStaffDm, readStaffDm } from '@/api/index.js'
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
            }
        },
        created() {
            uni.onKeyboardHeightChange(res => { this.fs = res.height > 0 })
        },
        onLoad(options) {
            let admin       = uni.getStorageSync('admin')
            this.myId      = admin.id
            this.myName    = admin.message_name || admin.name
            this.myAvatar  = admin.avatar
            this.toId      = parseInt(options.to_id)
            this.targetName = decodeURIComponent(options.name || '')
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
            listenSocket() {
                this._socketHandler = (result) => {
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
                        if (targetMsg) this.targetAvatar = targetMsg.avatar
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
                let arr = [...this.history, ...this.currentMessages]
                for (let i = 0; i < arr.length; i++) {
                    arr[i].Showdate = (i == 0 || arr[i].date != arr[i-1].date) ? arr[i].date : ''
                }
                this.messageArr = arr
            },
            sendMsg() {
                if (!this.inputText.trim()) return
                let text = this.inputText
                this.inputText = ''
                sendStaffDm({ to_id: this.toId, type: 1, message: text }).catch(() => {})
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
                                sendStaffDm({ to_id: this.toId, type: 2, message: re.data.path }).catch(() => {})
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
        width: 100vw; height: 122upx; background: #F9F9F9;
        box-shadow: 0px -1upx 0px 2upx rgba(0,0,0,0.3);
        display: flex; align-items: center;
        box-sizing: border-box; padding: 0 32upx 32upx;
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
</style>
