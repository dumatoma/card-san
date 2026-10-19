<template>
    <view class="contain">
        <scroll-view class="chatContent">
            <!-- [カスタムエイド 2026-10-20] 上までスクロールして前のメッセージを読み込んでいる間 -->
            <view class="cs-older" v-if="loadingOlder">読み込み中…</view>
            <!-- [カスタムエイド 2026-10-19] :key を付けて、新しいメッセージのたびに全部を作り直さないように。続けて送った写真の2枚目以降は先頭にまとめて出すので隠す -->
            <view class="msgItem" v-for="(item,index) in messageArr" :key="item.id || ('i' + index)" v-if="!item.grpHide">
                <view class="msgDate" v-if="item.Showdate" v-text="`${item.Showdate}  (${item.week})`"></view>
                <view class="userItem" :style="item.sender != 1? 'flex-direction: row-reverse' : '' ">
                    <view>
                        <view class="avatar">
                            <image :src="item.avatar || defaultImage" mode="aspectFill"></image>
                        </view>
                        <view class="chatName" v-text="item.name"></view>
                    </view>
                    <view class="detail" :style="item.sender != 1? 'flex-direction: row-reverse' : '' ">
                        <view v-if="item.type == 1" class="msgDetail"
                            :style="item.sender != 1? 'background:#87FA7D' : '' ">
                            <text v-html="formattedText(item.message)" selectable="true"
                                style="white-space: pre-wrap;"></text>
                        </view>
                        <!-- [カスタムエイド 2026-10-19] 続けて送った写真は LINE のように並べて1つのかたまりで表示。押すと全画面(左右にスワイプ)、長押しで保存 -->
                        <view v-if="item.type == 2 && item.grpUrls" class="cs-imgGrid" :class="(item.grpUrls.length == 2 || item.grpUrls.length == 4) ? 'cs-col2' : 'cs-col3'">
                            <image v-for="(u, ui) in item.grpUrls" :key="ui" class="cs-imgCell" :src="u" mode="aspectFill" lazy-load
                                @click="openGroup(item.grpUrls, ui)" @longpress="saveImage(u)"></image>
                        </view>
                        <view v-if="item.type == 2 && !item.grpUrls" class="msgDetailImage">
                            <image @click="openBigImg(item.message)" @longpress="saveImage(item.message)" @load="onImgLoad" :src="item.message" mode="widthFix" lazy-load></image>
                        </view>
                        <view v-if="item.type == 3" class="msgDetailImage" style="height: 120rpx;overflow: hidden">
                            <image @click="openURL(item.url)" :src="item.message" style="height: 120rpx;width: 100%;" mode="aspectFill"></image>
                        </view>                        
                        <view style="display: flex;align-items: center;">
                            <view class="sendTime" v-text="item.time"></view>
                            <view class="delas" v-if="item.sender != 1" @click.stop="delthis(item,index)">
                                <image src="../../static/svg/delta.svg" mode=""></image>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <!-- [カスタムエイド 2026-10-20] キーボードが出ている間は、その高さの分だけ下に余白を足す(最新のメッセージを入力欄のすぐ上に出すため) -->
            <view class="blank" :style="kbH ? 'height:' + (blankBase + kbH) + 'px' : ''"></view>
        </scroll-view>
        <view class="sendBox" :style="{bottom: h > 0 ? h + 'px' : '0'}" style="position: fixed;">
            <view class="boxLeft">
                <image src="../../static/image/camera.png" mode="aspectFit" @click="chooseCamear" v-if="!fs"></image>
                <image src="../../static/image/album.png" mode="aspectFit" @click="chooseImage" v-if="!fs"></image>
                <image src="../../static/image/showmore.png" mode="aspectFit" v-if="fs" @touchend.prevent="fs = false">
                </image>
            </view>
            <view class="boxRight" style="flex: 1;">
                <textarea placeholder="メッセージを入力"
                    :style="'border: 2upx solid #999;min-height:65upx;max-height:400upx;overflow-y:auto;border-radius: 30upx;box-sizing: border-box;padding: 10upx 15upx;width:100%;' + (taH ? 'height:' + taH + 'px;' : '')"
                    v-model="sendMessage" adjust-position="false" @linechange="onLine" @focus="emitFocus" @blur="blur"></textarea>
            </view>
            <view class="boxRights" v-if="sendMessage != ''">
                <image src="../../static/image/send.png" mode="" @touchend.prevent="send" v-if="msid == 0"></image>
                <image src="../../static/svg/nosend.svg" mode="" v-if="msid != 0"></image>
            </view>
        </view>
        <mod :config="config" @getStatus="confirm" v-if="delshow"></mod>
        <!-- 受信画像のフルスクリーン表示（ピンチ拡大縮小＋ダウンロード） -->
        <img-viewer :show.sync="bigImgShow" :src="bigImgSrc" @close="bigImgShow = false"></img-viewer>
    </view>
</template>

<script>
    import pickImage from '@/utils/pickImage.js' // [カスタムエイド 2026-10-19] 写真を選ぶ画面の「×」が出ない対策
    import { shrinkImage, uploadImage, markImageGroups, previewGroup } from '@/utils/chatImages.js' // [カスタムエイド 2026-10-19] 写真の複数枚送信・まとめて表示
    let admin = uni.getStorageSync("user")
    import mod from "@/components/mod.vue"
    import imgViewer from "@/components/img-viewer.vue"
    import {
        getMessageList,
        sendMessage,
        hasRead,
        loadMore,
        bindSocket,
        delMsg
    } from "@/api/index.js"
    export default {
        components: {
            mod,
            imgViewer
        },
        data() {
            return {
                bigImgShow: false,
                kbH: 0, // [カスタムエイド 2026-10-20] キーボードの高さ(px)
                blankBase: uni.upx2px(140), // 下の余白(入力欄の分)
                loadingOlder: false, // 前のメッセージを読み込み中
                bigImgSrc: "",
                taH: 0,
                delshow: false,
                messageArr: [],
                query: {},
                config: {},
                currentMessages: [],
                fArray: [],
                defaultImage: "../../static/image/avatar.png",
                sendMessage: "",
                history: [],
                reachBottom: true,
                sid: "",
                fs: false,
                connect: false,
                msid: 0,
                h: 0,
                myid: "",
                didx: "",
                insert:false
            }
        },
        computed: {
            newMessage() {
                return this.$store.state.newMessage;
            },
        },
        created() {
            this.getWebsocketData()
            this.myid = admin.id
            // [カスタムエイド 2026-10-19] キーボードが出きったら、一番新しいメッセージが見えるように下までスクロール(LINE と同じ)
            // 今までは入力欄を押した直後(キーボードが出る前)にスクロールしていたので、キーボードに隠れていた
            this._kbCb = (res) => {
                if (res.height == 0) {
                    this.fs = false
                    this.kbH = 0 // [カスタムエイド 2026-10-20]
                } else {
                    this.fs = true
                    // [カスタムエイド 2026-10-20] iPhone はキーボードが出てもページの高さが変わらず、最後の数件がキーボードの裏に残っていた。
                    // キーボードの高さの分だけ下に余白を足してから一番下へスクロールする
                    // [カスタムエイド 2026-10-20] 入力欄の表示が0.5秒ほど遅れる対策:スクロールは1回だけ、待たずにすぐ行う(2回目のスクロールで入力欄の描き直しが遅れていた)。
                    // キーボードの候補欄の出し入れなどで同じ高さの通知が続いたときはスクロールしない
                    if (this.kbH != res.height) {
                        this.kbH = res.height
                        this.$nextTick(() => {
                            uni.createSelectorQuery().select('.contain').boundingClientRect((r) => {
                                uni.pageScrollTo({ scrollTop: r && r.height ? r.height : 99999999, duration: 0 })
                            }).exec()
                        })
                    }
                }
            }
            uni.onKeyboardHeightChange(this._kbCb)
        },
        onLoad(options) {
            let that = this
            let query = options
            uni.showLoading({
                title: "読み込み中"
            })
            that.sid = query.sid
            that.getHistory()
            uni.setNavigationBarTitle({
                title: decodeURI(query.name)
            });
        },
        onPullDownRefresh() {
            this.loadOlder() // [カスタムエイド 2026-10-20]
        },
        onReachBottom(e) {
            let that = this
            setTimeout(function() {
                that.reachBottom = true
            }, 300)
        },

        onUnload() {
            // [カスタムエイド 2026-10-19] 画面を閉じたらキーボードの見張りをやめる
            if (this._kbCb && uni.offKeyboardHeightChange) uni.offKeyboardHeightChange(this._kbCb)
            let that = this
            hasRead(that.sid).then((res) => {}).catch(() => {})
        },
        onHide() {},
        // [カスタムエイド 2026-10-19] スクロールのたびに入力欄の位置(h)を書き換えて画面全体を描き直していたのをやめる(カクカクの原因)。
        // h は計算上いつも 0 以下で、入力欄の位置は今までと変わらない。
        // [カスタムエイド 2026-10-20] 一番上近くまでスクロールしたら、前の100件を自動で読み込む(画面は描き直さない軽い処理だけ)
        onPageScroll(e) {
            this._lastTop = e.scrollTop
            if (this._ready && e.scrollTop < 80) this.loadOlder() // 開いた直後(一番下へ移る前)は読まない
        },
        onShow() {
            let that = this
            let status = uni.getStorageSync("socketStatus")
            if (status == "unconnect") {
                uni.showModal({
                    title: "エラー",
                    content: "サーバー接続エラーが発生しました。後ほどもう一度実行してください。",
                    showCancel: false,
                    success: async (res) => {
                        if (res.confirm) {
                            uni.navigateBack()
                        }
                    }
                })
            }
        },
        methods: {
            blur() {
                this.insert = false
            },
            openURL(e) {
                // #ifdef APP-PLUS
                if (plus.os.name == 'Android' || plus.os.name == 'iOS') {
                    plus.runtime.openURL(e);
                    return
                }
                // #endif
            },
            confirm(e) {
                let that = this
                if (e == "cancel") {
                    // [カスタムエイド 2026-10-19] まとめて表示している写真は、かたまりの全部を削除
                    let ids = that.delids && that.delids.length ? that.delids : [that.delid]
                    let done = []
                    let step = (k) => {
                        if (k >= ids.length) {
                            let gone = (v) => done.indexOf(v.id) < 0
                            that.setList(that.messageArr.filter(gone)) // [カスタムエイド 2026-10-20]
                            that.delshow = false
                            return
                        }
                        delMsg({ sid: that.sid, message_id: ids[k] }).then((res) => {
                            if (res.code == 200) {
                                done.push(ids[k])
                                step(k + 1)
                            } else {
                                uni.showToast({
                                    title: res.message
                                })
                                step(ids.length)
                            }
                        }).catch(() => { step(ids.length) })
                    }
                    step(0)
                } else {
                    this.delshow = false
                }
            },
            delthis(e, index) {
                let that = this
                that.delid = e.id
                that.didx = index
                that.delids = e.grpItems ? e.grpItems.map((v) => v.id) : [e.id] // [カスタムエイド 2026-10-19] 写真のかたまりは全部
                that.config = {
                    type: "button",
                    cancel: true,
                    default: true,
                    title: "",
                    content: e.grpItems ? "この写真(" + e.grpItems.length + "枚)を削除しますか？" : "選択したメッセージを削除しますか？",
                    cancelText: "削除",
                    defaultText: "キャンセル"
                }
                that.delshow = true
            },
            toLink(e) {
            },
            formattedText(e) {
                // 使用正则表达式找到所有的网址  
                const regex = /https?:\/\/[^\s]+/g;
                const urls = e.match(regex);

                if (urls) {
                    // 替换所有匹配的网址为带有蓝色样式的链接文本  
                    let linkText = '';
                    urls.forEach((match) => {
                        linkText += `<a href="${match}" class="blue-link">${match}</a> `;
                    });
                    // return e.replace(regex, '<a href="#" @click="toLink(123)" class="blue-link">$&</a>');
                    return linkText
                } else {
                    return e;
                }
            },
            emitFocus(e) {
                let that = this
                that.insert = true
                // [カスタムエイド 2026-10-19] ここ(キーボードが出る前)ではなく、キーボードが出きった時にスクロールする(created の _kbCb)
                return
                setTimeout(() => {
                    // that.$nextTick(()=>{
                    //   uni.pageScrollTo({
                    //       scrollTop: 2000000,
                    //       duration: 0 
                    //   });
                    // }) 
                    that.scollBottom()
                }, 50)
            },
            getWebsocketData() {
                let that = this
                // 打开连接
                // 接收数据
                uni.$on("getPositonsOrder", (result) => {
                    // this.connect = true
                    // if (result.type == 'connect') {
                    //     bindSocket(result.data.client_id).then((res) => {
                    //         if (res.code != 200) {
                    //             uni.showToast({
                    //                 title: res.message,
                    //                 icon: "none"
                    //             })
                    //         }
                    //     })
                    // }
                    if (result.type == "message" && result.data[0].sid == that.sid) {
                        // [カスタムエイド 2026-10-19] 自分が送った写真では「送信中」の印を動かさない(写真は1枚ずつ続けて送るため)
                        if (!(result.data[0].type == 2 && result.data[0].sender == 2)) {
                            if (result.data[0].sender == 2) {
                                if (result.data[0].id == that.msid) {
                                    that.msid = 0
                                } else {
                                    that.msid = result.data[0].id
                                }
                            } else {
                                that.msid = 0
                            }
                        }
                        result.data.forEach((v, i) => {
                            if (v.type == 3) {
                                let a = JSON.parse(v.message)
                                v.message = a.comment_button_style_image
                                v.url = a.comment_button_url
                            }
                        })
                        // [カスタムエイド 2026-10-20] 届いたメッセージを今の一覧の後ろに足す(重複は除き、番号順に並べ直す)
                        that.setList([...that.messageArr, ...result.data])
                        that._autoUntil = Date.now() + 3000
                        that.scollBottom()
                    }
                    if (result.type == 'delete_message') {
                        // that.messageArr.forEach((v, i) => {
                        //     if (v.id == result.data.message_id) {
                        //         console.log(true)
                        //         that.messageArr.splice(i, 1)
                        //     }
                        // })
                        // that.currentMessages.forEach((v, i) => {
                        //     if (v.id == result.data.message_id) {
                        //         that.currentMessages.splice(i, 1)
                        //     }
                        // })

                        // that.history.forEach((v, i) => {
                        //     if (v.id == result.data.message_id) {
                        //         that.history.splice(i, 1)
                        //     }
                        // })
                    }
                })
                // 错误时做些什么
                uni.$on("connectError", () => {
                    this.connect = false
                    this.scoketError = true
                })
            },
            getHistory() {
                let that = this
                let data = {}
                data.type = "detail"
                data.timestamp = that.formatTime()
                data.uid = that.query.uid
                data.mid = 0
                getMessageList(that.sid).then((res) => {
                    if (res.code == 200) {
                        that.$nextTick(() => {
                            uni.pageScrollTo({
                                scrollTop: 2000000, //滚动到页面的目标位置（单位px）
                                duration: 0 //滚动动画的时长，默认300ms，单位 ms
                            });
                        })
                        res.data.messages.data.forEach((v, i) => {
                            if (v.type == 3) {
                                let a = JSON.parse(v.message)
                                v.message = a.comment_button_style_image
                                v.url = a.comment_button_url
                            }
                        })
                        // [カスタムエイド 2026-10-20] 開いたときの最新100件。読み込み中に届いたメッセージとも合わせる
                        that._noMore = res.data.messages.data.length < 100
                        that.setList([...res.data.messages.data.reverse(), ...that.messageArr])
                        that._autoUntil = Date.now() + 3000
                        setTimeout(() => { that._ready = true }, 800) // [カスタムエイド 2026-10-20] 一番下へ移ってから、上までスクロールで前の分を読めるようにする
                        setTimeout(() => {
                            that.scollBottom()
                            uni.hideLoading()
                        }, 50)
                    }
                }).catch(() => { uni.hideLoading() })
            },
            jumpUrl(e) {
                if (e == "") {

                } else {
                    uni.navigateTo({
                        url: "../../webview/webview?link=" + e
                    })
                }

            },
            onBackPress() {
                let that = this
                let pages = getCurrentPages();
                // hasRead(that.sid).then((res) => {})
                let currPage = pages[pages.length - 1]
                let prePage = pages[pages.length - 2]
                if (prePage.route == "pages/message/message") {
                    prePage.$vm.getList()
                }
            },
            // [カスタムエイド 2026-10-20] 一覧を作り直す共通の処理:重複を除き、番号順に並べ、日付の見出しと写真のかたまりを付ける
            setList(list) {
                let seen = {}
                let out = []
                ;(list || []).forEach((v) => {
                    if (!v) return
                    if (v.id !== undefined && v.id !== null) {
                        if (seen[v.id]) return
                        seen[v.id] = 1
                    }
                    out.push(v)
                })
                out.sort((x, y) => (Number(x.id) || 0) - (Number(y.id) || 0))
                for (let i = 0; i < out.length; i++) {
                    out[i].Showdate = i > 0 && out[i].date == out[i - 1].date ? '' : out[i].date
                }
                this.messageArr = markImageGroups(out)
            },
            // [カスタムエイド 2026-10-20] 前の100件を読み込んで上に足す。見ていた位置はずらさない
            loadOlder() {
                let that = this
                if (that._loadingOlder || that._noMore || !that.messageArr.length) {
                    uni.stopPullDownRefresh()
                    return
                }
                let first = that.messageArr[0].id
                if (!first) {
                    uni.stopPullDownRefresh()
                    return
                }
                that._loadingOlder = true
                that.loadingOlder = true
                that._autoUntil = 0
                let done = () => {
                    that._loadingOlder = false
                    that.loadingOlder = false
                    uni.stopPullDownRefresh()
                }
                loadMore({ type: 'detail', stamp: that.formatTime(), sid: that.sid, mid: first }).then((res) => {
                    if (res.code != 200) {
                        done()
                        return
                    }
                    let older = (res.data.messages.data || []).slice().reverse()
                    if (older.length < 100) that._noMore = true
                    if (!older.length) {
                        done()
                        return
                    }
                    uni.createSelectorQuery().select('.contain').boundingClientRect((r0) => {
                        let h0 = r0 ? r0.height : 0
                        that.setList([...older, ...that.messageArr])
                        that.$nextTick(() => {
                            setTimeout(() => {
                                uni.createSelectorQuery().select('.contain').boundingClientRect((r1) => {
                                    let h1 = r1 ? r1.height : h0
                                    uni.pageScrollTo({ scrollTop: Math.max(0, h1 - h0 + (that._lastTop || 0)), duration: 0 })
                                    done()
                                }).exec()
                            }, 50)
                        })
                    }).exec()
                }).catch(done)
            },
            previewImage(e) {
                let that = this
                let tem = []
                tem.push(e)
                uni.previewImage({
                    current: 0,
                    urls: tem
                })
            },
            openBigImg(url) {
                this.bigImgSrc = url
                this.bigImgShow = true
            },
            // 入力欄の高さを8行まで追従させ、それ以上は固定して内部スクロールに任せる
            onLine(e) {
                if (e.detail && e.detail.lineCount <= 8) {
                    this.taH = e.detail.height
                }
            },
            saveImage(url) {
                uni.showActionSheet({
                    itemList: ['画像を保存'],
                    success: (res) => {
                        if (res.tapIndex === 0) {
                            uni.downloadFile({
                                url: url,
                                success: (res) => {
                                    if (res.statusCode === 200) {
                                        uni.saveImageToPhotosAlbum({
                                            filePath: res.tempFilePath,
                                            success: () => uni.showToast({ title: '保存しました', icon: 'success' }),
                                            fail: () => uni.showToast({ title: '保存に失敗しました', icon: 'none' })
                                        })
                                    }
                                }
                            })
                        }
                    }
                })
            },
            formatTime() {
                var date = new Date()
                var timeStamp = Date.parse(date)
                return timeStamp
            },
            // [カスタムエイド 2026-10-19] 写真は最大9枚まで選べるように。小さくしてから1枚ずつ順番に送る
            chooseImage() {
                let that = this
                uni.hideKeyboard()
                pickImage({
                    count: 9,
                    sizeType: ['compressed'],
                    sourceType: ['album'],
                    success: (res) => {
                        that.sendImages(res.tempFilePaths || [])
                    }
                });
            },
            chooseCamear() {
                let that = this
                uni.hideKeyboard()
                pickImage({
                    count: 1,
                    sizeType: ['compressed'],
                    sourceType: ['camera'],
                    success: (res) => {
                        that.sendImages(res.tempFilePaths || [])
                    }
                });
            },
            async sendImages(paths) {
                let that = this
                if (!paths || !paths.length) return
                let ng = 0
                for (let i = 0; i < paths.length; i++) {
                    uni.showLoading({
                        title: paths.length > 1 ? '送信中 (' + (i + 1) + '/' + paths.length + ')' : '送信中',
                        mask: true
                    })
                    try {
                        let small = await shrinkImage(paths[i])
                        let path = await uploadImage(that.$baseUrl, small)
                        let res = await sendMessage({ sid: that.sid, type: 2, message: path })
                        if (!res || res.code != 200) ng++
                    } catch (e) {
                        ng++
                    }
                }
                uni.hideLoading()
                if (ng > 0) {
                    uni.showToast({
                        title: '送信できなかった写真があります(' + ng + '枚)',
                        icon: 'none'
                    })
                }
            },
            // まとめて表示している写真を全画面で
            openGroup(urls, index) {
                previewGroup(urls, index)
            },
            // 写真が読み込まれて高さが変わったとき、開いた直後・新しいメッセージの直後なら一番下に合わせ直す
            onImgLoad() {
                if (this._autoUntil && Date.now() < this._autoUntil) this.scollBottom()
            },
            // [カスタムエイド 2026-10-19] 一番下へ(アニメーションなしで一気に。カクつかないように)
            scollBottom() {
                this.$nextTick(() => {
                    setTimeout(() => {
                        // [カスタムエイド 2026-10-20] ページの実際の高さ(下の余白を含む)を測って、そこまでスクロール
                        uni.createSelectorQuery().select('.contain').boundingClientRect((res) => {
                            uni.pageScrollTo({
                                scrollTop: res && res.height ? res.height : 99999999,
                                duration: 0
                            })
                        }).exec()
                    }, 50)
                })
            },
            send() {
                let that = this
                let data = {}
                data.sid = that.sid
                data.type = 1
                data.message = that.sendMessage
                if (that.sendMessage == '') {

                } else {
                    that.sendMessage = ""
                    // uni.hideKeyboard()
                    sendMessage(data).then((res) => {

                        if (res.code == 200) {
                            if (that.msid == res.data.message_id) {
                                that.msid = 0
                            } else {
                                that.msid = res.data.message_id
                            }

                        }
                    }).catch(() => {})
                }
            }
        }
    }
</script>

<style lang="scss">
    ::v-deep .u-navbar__content__title {
        font-size: 40rpx;
        color: #1D1D1F;
    }

    .delas {
        width: 40rpx;
        height: 40rpx;
        margin: 0 0 0 20rpx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .boxRights {
        margin-left: 20upx;

        image {
            width: 54upx;
            height: 54upx;
        }
    }

    .u-nav-right {
        image {
            width: 48rpx;
            height: 48rpx;
        }
    }

    .contain {
        display: flex;
        flex-direction: column;
        // height: calc(100vh - 44px);
        height: auto;

        .msgDetailImage {
            width: 492rpx;
            border-radius: 20upx;
            overflow: hidden;
            margin: 0 8rpx;
            image {
                width: 100%;
            }
        }

        .chatContent {
            flex: 1;
            background-color: #fff;
            box-sizing: border-box;
            padding: 0 32rpx;
            // background: red !important;
            .userItem {
                display: flex;

                .avatar {
                    width: 60upx;
                    height: 60upx;
                    border-radius: 50%;
                    flex-shrink: 0;
                    overflow: hidden;

                    image {
                        width: 100%;
                        height: 100%;
                    }
                }

                .detail {
                    display: flex;
                    align-items: flex-end;

                    .msgDetail {
                        max-width: 492upx;
                        box-sizing: border-box;
                        padding: 20upx;
                        font-size: 32upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #1D1D1F;
                        background: rgba(143, 168, 248, 0.2);
                        border-radius: 20upx;
                        margin: 0 8upx;
                        word-wrap: break-word;
                        word-break: normal;
                    }

                    .sendTime {
                        font-size: 20upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #86868B;
                    }
                }
            }
        }

        .msgItem {
            margin-bottom: 24upx;

            .msgDate {
                text-align: center;
                padding: 24upx 0;
            }
        }

        .blank {
            width: 100vw;
            height: 140upx;
        }

        .chatName {
            font-size: 24upx;
            color: #aaa
        }

        .sendBox {
            width: 100vw;
            min-height: 120upx;
            background: #F9F9F9;
            box-shadow: 0px -1upx 0px 2upx rgba(0, 0, 0, 0.3);
            opacity: 0.93;
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 20upx 32upx 32upx;
            flex-shrink: 0;
            position: fixed;
            left: 0;
            bottom: 0;
            justify-content: space-between;

            .boxRights {
                margin-left: 20upx;

                image {
                    width: 54upx;
                    height: 54upx;

                }
            }

            .boxLeft {
                display: flex;
                align-items: center;

                image {
                    width: 54upx;
                    height: 54upx;
                    margin-right: 28upx
                }

                image:first-child {
                    width: 54upx;
                    height: 54upx
                }
            }

            .boxRight {
                flex: 1
            }
        }
    }

    /* [カスタムエイド 2026-10-19] 続けて送った写真のかたまり(LINE のように並べる) */
    .cs-imgGrid {
        width: 492rpx;
        margin: 0 8rpx;
        display: flex;
        flex-wrap: wrap;
        border-radius: 20rpx;
        overflow: hidden;
    }
    .cs-imgGrid .cs-imgCell {
        display: block;
        background: #E8E8ED;
        margin-bottom: 4rpx;
    }
    .cs-imgGrid.cs-col2 .cs-imgCell {
        width: 244rpx;
        height: 244rpx;
        margin-right: 4rpx;
    }
    .cs-imgGrid.cs-col2 .cs-imgCell:nth-child(2n) {
        margin-right: 0;
    }
    .cs-imgGrid.cs-col3 .cs-imgCell {
        width: 161rpx;
        height: 161rpx;
        margin-right: 4rpx;
    }
    .cs-imgGrid.cs-col3 .cs-imgCell:nth-child(3n) {
        margin-right: 0;
    }

    /* [カスタムエイド 2026-10-20] 前のメッセージを読み込み中 */
    .cs-older {
        text-align: center;
        font-size: 24rpx;
        color: #86868B;
        padding: 16rpx 0;
    }

    /* [カスタムエイド 2026-10-20] 入力欄を別の層で描いて、キーボードが出たときにすぐ表示されるように */
    .contain .sendBox {
        -webkit-transform: translateZ(0);
        transform: translateZ(0);
        will-change: transform;
    }
</style>