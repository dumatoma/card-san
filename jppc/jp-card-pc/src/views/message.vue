<template>
    <div>
        <div class="tit">メッセージチャット</div>
        <div class="content no-size">
            <div class="left no-size">
                <div class="lef-top no-size">
                    <div class="search no-size">
                        <img src="../static/search.png" alt="" />
                        <input type="search" placeholder="名前、電話番号、IDで検索" v-model="searchContent" @change="search" />
                    </div>
                    <div class="total">メンバー数:{{showlist.length}}</div>
                </div>
                <div class="user-list no-size">
                    <div :class="['item', 'no-size', current == index ? 'move' : '', item.uid == currentUid ? 'sel' : '']"
                        v-for="(item, index) in showlist" :key="index" style="cursor: pointer;" @mousemove="move(index)"
                        @mouseleave="out(index)" @click="showDetail(item.uid,item.name,item.avatar,index)">
                        <div class="box">
                            <div class="ite-left">
                                <img :src="item.avatar" style="object-fit: cover" alt="" />
                            </div>
                            <div class="ite-midd">
                                <div class="uname">{{ item.name }}</div>
                                <div class="info inlie2" v-html="item.message"></div>
                            </div>
                            <div class="ite-rig">
                                <div class="time">{{ item.time }}</div>
                                <div class="hui" v-if="item.unread_num > 0">{{ item.unread_num }}</div>
                                <div class="huis shou" v-if="current == index">
                                    <img src="../static/shu-dian.png" alt=""
                                        @click="delIndex = delIndex == index ? '-1' : index" />
                                    <div class="del shou" v-if="delIndex == index" @click="del(index)">
                                        削除
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="right no-size">
                <div class="top no-size">
                    <img :src="currentAvatar" alt="" v-if="currentAvatar" />
                    <div class="uname" v-text="currentName"></div>
                </div>
                <div class="info-box" ref="warp">
                    <div class="more">{{ moreText }}</div>
                    <div class="info-item no-size" v-for="(item1, index1) in list" :key="index1"
                        @mousemove="messageMove(index1)" @mouseleave="messageOut(index1)">
                        <div class="rig-time">{{ item1.Showdate }}</div>
                        <div class="info-message">
                            <div class="info-left" v-if="item1.sender == 2">
                                <div class="le">
                                    <div class="left-img">
                                        <img :src="item1.avatar" style="object-fit: cover;" alt="" />
                                    </div>
                                    <div class="left-name">
                                        {{ item1.name }}
                                    </div>
                                </div>
                                <div class="ri no-size" v-if="item1.type == 1" v-html="item1.message"></div>
                                <div class="riImage no-size" style="background: transparent !important;"
                                    v-if="item1.type == 2">
                                    <img :src="item1.message" alt="">
                                </div>
                                
                                <div class="riImage no-size" style="background: transparent !important;"
                                    v-if="item1.type == 3">
                                    <img style="width: 100%;height: 60px;object-fit: cover;" :src="item1.message" @click="openurl(item1.url)" alt="">
                                </div>
                                
                                <div class="m">
                                    {{ item1.time }}
                                </div>
                            </div>
                            
                            
                            
                            <div class="info-right" v-if="item1.sender == 1" @mousemove="messageMoves(index1)"
                                @mouseleave="messageOuts(index1)">
                                <div style="display: flex;align-items: flex-end;">
                                    <div class="m">
                                        <div class="di" v-if="item1.read == 1">既読</div>
                                        <div class="time">
                                            {{ item1.time }}
                                        </div>
                                    </div>
                                    <div class="dels shou" @click="delt(item1,index1)" v-if="item1.said == myid">
                                        <img src="../static/svg/delta.svg" alt="" />
                                    </div>
                                </div>
                                <div class="ri" v-html="item1.message" v-if="item1.type == 1"></div>
                                <div class="riImage" v-if="item1.type == 2">
                                    <img :src="item1.message" alt="">
                                </div>
                                <div class="riImage no-size" style="background: transparent !important;"
                                    v-if="item1.type == 3">
                                    <img :src="item1.message" style="width: 100%;height: 60px;object-fit: cover;" @click="openurl(item1.url)" alt="">
                                </div>
                                <div class="le">
                                    <div class="left-img">
                                        <img :src="item1.avatar" alt="" />
                                    </div>
                                    <div class="left-name">
                                        {{ item1.name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="height: 5px;"></div>
                </div>

                <div class="send-box no-size">
                    <textarea ref="ta" v-model="messageTxt" placeholder="メッセージを入力（Enterで送信／Shift+Enterで改行）"
                        @keydown.enter="handleKeyCode($event)" @focus="getf = true" @blur="getf = false"></textarea>
                </div>
                <div class="bot no-size">

                    <el-upload class="upload-demo" accept="image/*" action="https://api.card-san.jp/tool/upload/file"
                        :on-success="handleAvatarSuccess" :limit="1" :show-file-list="false" name="file">
                        <div class="hx">
                            <img src="../static/fasong.png" class="huixing" alt="" />
                        </div>

                    </el-upload>

                    <button type="button" class="send-btn" :class="messageTxt == '' ? 'off' : ''" @click.stop="handleSendText">
                        送信
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 20.5l18-8.5L3 3.5v6.6l12.5 1.9L3 13.9z"></path></svg>
                    </button>

                </div>
            </div>
        </div>
        <div class="fixed" v-show="delListShow">
            <div class="zhe"></div>
            <div class="box">
                <div class="title">
                    このチャットルームのメッセージ(チャット履歴)が削除されます。削除しますか？
                </div>
                <div class="btn shou">削除</div>
                <div class="btn1 shou" @click="delListShow = false">キャンセル</div>
            </div>
        </div>
        <div class="fixed" v-show="delMessageShow">
            <div class="zhe"></div>
            <div class="box">
                <div class="title">このメッセージの送信を削除しますか？</div>
                <div class="btn shou">削除</div>
                <div class="btn1 shou" @click="delMessageShow = false">キャンセル</div>
            </div>
        </div>


        <div class="fixed" v-show="delMessageShow1">
            <div class="zhe"></div>
            <div class="box">
                <div class="title">選択したメッセージを削除しますか？</div>
                <div class="btn shou" @click="delsingle">削除</div>
                <div class="btn1 shou" @click="delMessageShow1 = false">キャンセル</div>
            </div>
        </div>
        
        
        <div class="nqmask" v-if="showg">
            <div class="nqmaskContent">
                <div class="nqmaskTitle">
                    ご契約のプランでは <br/>この機能をご利用できません。<br/> プランを変更しますか？
                </div>
                <div class="nqmaskDetail">
                   プランを変更するとすぐに、この機能をご利用できます。
                </div>
                <div class="nqbt" @click="toBuy">
                    プランを変更
                </div>
                <div class="nqbt2" @click="goback">
                   キャンセル
                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import {
        getMessageList,
        sendMessage,
        readMessage,
        delMsg
    } from "@/http/api.js"
    import {
        mapActions,
        mapGetters
    } from 'vuex';
    export default {
        name: "",
        components: {},
        data() {
            return {
                sroll: 0,
                delMessageShow: false,
                delListShow: false,
                delMessageShow1: false,
                messageTxt: "",
                current: "-1",
                // 列表删除
                delIndex: "-1",
                // 聊天一级
                messageCurrent: "-1",
                // 聊天二级
                messageIndex: "-1",
                // 聊天删除
                messageDelIndex: "-1",
                moreText: "",
                list: [],
                userList: [],
                showlist: [],
                currentUid: 0,
                currentName: "",
                currentAvatar: "",
                searchContent: "",
                getf: false,
                singleid: "",
                myid: "",
                singleidx: "",
                showg:false
            };
        },
        computed: {
            socketMsgs() {
                return this.$store.state.msg;
            },
        },
        watch: {
            'socketMsgs': {
                //处理接收到的消息
                handler: function() {
                    let that = this
                    that.toDoSocket(that.socketMsgs.data)
                }
            },
            // 入力欄の高さを文字量に合わせる [カスタムエイド]
            messageTxt() {
                this.autoGrow()
            }

        },
        created() {
            let that = this
            that.getList()
            let temp = localStorage.getItem("admin")
            let info = JSON.parse(temp)
            console.log("info", info)
            that.myid = info.id
        },
        mounted() {
            this.$nextTick(() => {
                this.$refs.warp.scrollTop = this.$refs.warp.scrollHeight;
                document
                    .querySelector(".info-box")
                    .addEventListener("scroll", this.handleScroll);
            });
        },
        methods: {
            toBuy() {
                this.$router.push({
                    path: "/utilizePlan",
                })
            },
            goback(){
                this.showg = false
                // this.$router.go(-1)
                window.history.back();
            },
            openurl(e){
                console.log(e)
                window.open(e)
            },
            delsingle() {
                let that = this
                let data = {}
                data.uid = that.currentUid
                data.message_id = that.singleid
                delMsg(data).then((res) => {
                    console.log(res)
                    if (res.code == 200) {
                        that.$message({
                            message: res.message,
                            type: 'success',
                            offset: 400
                        });
                        that.list.splice(that.singleidx * 1, 1)
                        that.delMessageShow1 = false
                         that.getList()
                    } else {
                        that.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });
                    }
                })
            },
            delt(e, idx) {
                console.log(e)
                let that = this
                that.delMessageShow1 = true
                that.singleid = e.id
                that.singleidx = idx
            },
            toDoSocket(data) {
                let that = this
                let datas = JSON.parse(data)
                console.log("message receive data", JSON.parse(data))
                if (datas.type == "message") {
                    if (datas.data[0].uid == that.currentUid) {
                        that.getList()
                        readMessage(that.currentUid).then((res) => {
                            console.log(res)
                            if (res.code == 200) {
                                console.log("res", res)
                                console.log("number", that.current)
                                that.list[that.current].unread_num = 0
                            }
                        })
                    } else {
                        that.getList()
                    }
                    
                    datas.data.forEach((v,i) => {
                        if(v.type == 3){
                            let t = JSON.parse(v.message)
                             v.message = t.comment_button_style_image
                             v.url = t.comment_button_url
                        }
                    })
                    
                    that.list = [...that.list, ...datas.data]
                    that.scollBottom()
                }

                if (datas.type == 'delete_message') {
                    console.log()
                    console.log(that.currentUid)
                    if (datas.data.uid == that.currentUid) {
                        that.showDetail(datas.data.uid, that.currentName, that.currentAvatar, 0)
                    }
                     that.getList()
                }
            },
            handleAvatarSuccess(e) {
                let that = this
                console.log(e)
                if (e.code == 200) {
                    let data = {}
                    data['uid'] = that.currentUid
                    data['type'] = 2
                    data['message'] = e.data.path
                    sendMessage(data).then((res) => {
                        if (res.code == 200) {
                            console.log('发送成功')
                            that.messageTxt = ''
                        }
                    })
                } else {
                    that.$message.error(e.message)
                }
            },
            search() {
                let that = this
                let content = this.searchContent
                if (content != '') {
                    let array = []
                    that.userList.forEach((v, i) => {
                        console.log(v)
                        if (v.member_id.includes(content) || v.name.includes(content) || v.phone.includes(
                                content)) {
                            array.push(v)
                        }
                    })
                    that.showlist = array
                } else {
                    that.showlist = that.userList
                }

            },
            getList() {
                let that = this
                let data = {}
                data['type'] = "list"
                data['uid'] = 0
                data['mid'] = 0
                data['timestamp'] = 0
                getMessageList(data).then((res) => {
                    console.log("userlist",res)
                    if (res.code == 200) {
                        that.userList = res.data.messages
                        that.showlist = res.data.messages
                    }else if(res.code == 403){
                        that.showg = true
                    }else{
                        that.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });
                    }
                })
            },

            showDetail(id, name, avatar, index) {
                let that = this
                let data = {}
                data['type'] = "detail"
                data['uid'] = id
                data['mid'] = 0
                data['timestamp'] = 0
                readMessage(id).then((res) => {
                    console.log(res)
                    if (res.code == 200) {
                        that.userList[index].unread_num = 0
                    }
                })
                getMessageList(data).then((res) => {
                    if (res.code == 200) {
                        res.data.messages.data.forEach((v,i) => {
                            if(v.type == 3){
                                let t = JSON.parse(v.message)
                                 v.message = t.comment_button_style_image
                                 v.url = t.comment_button_url
                            }
                            
                        })
                        console.log("detail", res)
                        that.list = []
                        that.currentName = name
                        that.currentUid = id
                        that.currentAvatar = avatar
                        that.list = res.data.messages.data.reverse()
                        
                        for (let i = 0; i < that.list.length; i++) {
                            if (i > 0) {
                                if (that.list[i].date == that.list[i - 1].date) {
                                    that.list[i].Showdate = ''
                                } else {
                                    that.list[i].Showdate = that.list[i].date
                                }
                            } else {
                                that.list[i].Showdate = that.list[i].date
                            }
                        }
                        that.scollBottom()
                    }
                })
            },
            handleScroll() {
                let scrollTop = document.querySelector(".info-box").scrollTop; //获取监听指定区域滚动位置的
                if (scrollTop == 0) {
                    console.log('到顶了')
                    this.getMoreMessage()
                }
            },
            scollBottom() {
                setTimeout(() => {
                    const dom = this.$refs.warp;
                    if (!dom) return;
                    dom.scrollTop = dom.scrollHeight;
                }, 20);
            },
            // 入力欄の高さを文字量に合わせて84px〜160pxで伸び縮みさせる [カスタムエイド]
            autoGrow() {
                this.$nextTick(() => {
                    const el = this.$refs.ta;
                    if (!el) return;
                    el.style.height = "84px";
                    el.style.height = Math.min(Math.max(el.scrollHeight + 2, 84), 160) + "px";
                });
            },
            messageMove(i) {
                this.messageCurrent = i;
            },
            messageOut(i) {
                this.messageCurrent = "-1";
            },
            messageMoves(i) {
                this.messageIndex = i;
            },
            messageOuts(i) {
                this.messageIndex = "-1";
                this.messageDelIndex = "-1";
            },
            move(index) {
                // console.log(index, 111);
                this.current = index;
            },
            out(i) {
                this.messageCurrent = "-1";
                this.delIndex = "-1";
            },
            // 键盘回车事件
            handleKeyCode(e) {
                console.log(e)
                if (!e.shiftKey && e.keyCode == 13) {
                    e.cancelBubble = true; //ie阻止冒泡行为
                    e.stopPropagation(); //Firefox阻止冒泡行为
                    e.preventDefault(); //取消事件的默认动作*换行
                    //以下处理发送消息代码
                    if (e.keyCode == 13) {
                        this.handleSendText();
                    }

                }
            },
            getMoreMessage() {
                let that = this
                let data = {}
                data['type'] = "detail"
                data['uid'] = that.currentUid
                data['mid'] = that.list[0].id
                data['timestamp'] = that.list[0].timestamp
                getMessageList(data).then((res) => {
                    console.log(res)
                    if (res.code == 200) {
                        let array = res.data.messages.data.reverse()
                        that.list = [...array, ...that.list]
                    }
                })
            },
            // 删除聊天信息
            delMessage() {
                this.delMessageShow = true;
            },
            // 删除列表
            del() {
                this.delIndex = "-1";
                this.delListShow = true;
            },
            handleSendText() {
                let that = this
                // if(that.getf == true){
                //     return;
                // }
                let data = {}
                data['uid'] = that.currentUid
                data['type'] = 1
                data['message'] = that.messageTxt
                if (that.messageTxt != '') {
                    that.messageTxt = ''
                    sendMessage(data).then((res) => {
                        if (res.code == 200) {
                            console.log('发送成功')
                        } else {
                            that.$message({
                                message: res.message,
                                type: 'error',
                                offset: 400
                            });
                        }
                    })
                } else {
                    // that.$message.error('请输入内容')
                    that.$message({
                        message: 'メッセージを入力してください',
                        type: 'error',
                        offset: 400
                    });
                }

            },
        },
    };
</script>

<style lang="scss" scoped>
    .hx {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;

        img {
            margin: 0 !important;
        }
    }

    .hx:hover {
        background: rgba(29, 29, 31, .1) !important;
    }

    .riImage {
        width: 266px;
        max-width: 60%;
        margin: 0 10px;
    }

    .huixing:hover {
        opacity: 0.6 !important;
    }

    .riImage img {
        width: 100%;
        object-fit: contain;
    }

    .tit {
        color: #1d1d1f;
        font-size: 21px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .dels {
        width: 20px;
        height: 20px;
        overflow: hidden;
    }

    .dels img {
        width: 100%;
        height: 100%;
    }

    // ===== [カスタムエイド 2026-10-12] チャット画面の統一デザイン =====
    // ルームチャット(roomChat.vue)と同じ寸法・同じ見た目にそろえている
    .content {
        width: 100%;
        max-width: 1120px;
        height: calc(100vh - 260px);
        min-height: 560px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        display: flex;

        // 左: メンバー一覧(幅320px固定)
        .left {
            width: 320px;
            flex-shrink: 0;
            border-right: 1px solid #ececf0;
            display: flex;
            flex-direction: column;

            .lef-top {
                flex-shrink: 0;
                padding: 14px 16px 10px;
                border-bottom: 1px solid #ececf0;

                .search {
                    position: relative;

                    img {
                        position: absolute;
                        top: 11px;
                        left: 12px;
                        width: 16px;
                        height: 16px;
                    }

                    input {
                        width: 100%;
                        height: 38px;
                        box-sizing: border-box;
                        padding: 0 12px 0 36px;
                        border: 0;
                        border-radius: 10px;
                        background: #f2f2f5;
                        outline: none;
                        font-size: 13px;
                    }
                }

                .total {
                    margin-top: 8px;
                    color: #6e6e73;
                    font-size: 12px;
                }
            }

            .user-list {
                flex: 1;
                min-height: 0;
                overflow-y: auto;
                overflow-x: hidden;

                .item {
                    background: #ffffff;
                    border-bottom: 1px solid #f2f2f5;
                }

                .move {
                    background: #f5f5f7;
                }

                // 表示中の相手
                .sel {
                    background: #eef4fe;
                }

                .box {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 10px 16px; // 上下20px → 10px
                }

                .ite-left {
                    flex-shrink: 0;
                    display: flex;

                    img {
                        width: 44px;
                        height: 44px;
                        border-radius: 50%;
                        border: 0.5px solid #aaaaaa;
                        box-sizing: border-box;
                        object-fit: cover;
                    }
                }

                .ite-midd {
                    flex: 1;
                    min-width: 0;

                    .uname {
                        color: #1d1d1f;
                        font-size: 15px;
                        font-weight: 500;
                        margin-bottom: 2px;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }

                    // 最新メッセージは1行で省略(…)
                    .info {
                        color: #6e6e73;
                        font-size: 13px;
                        -webkit-line-clamp: 1;
                        word-break: break-all;
                    }
                }

                // マウスを乗せたとき、削除メニュー(…)の分だけ文字を短くする
                .move .ite-midd {
                    padding-right: 26px;
                }

                .ite-rig {
                    flex-shrink: 0;
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    align-items: flex-end;
                    gap: 4px;

                    .time {
                        color: #8e8e93;
                        font-size: 11px;
                    }

                    .hui {
                        min-width: 20px;
                        height: 20px;
                        padding: 0 6px;
                        box-sizing: border-box;
                        border-radius: 10px;
                        background: #d93025;
                        color: #ffffff;
                        font-size: 11px;
                        line-height: 20px;
                        text-align: center;
                    }

                    .huis {
                        position: absolute;
                        right: calc(100% + 2px);
                        top: 50%;
                        transform: translateY(-50%);
                        z-index: 1;

                        img {
                            display: block;
                            width: 24px;
                            height: 24px;
                        }

                        .del {
                            position: absolute;
                            right: 0;
                            top: 28px;
                            width: 56px;
                            height: 30px;
                            background: #ffffff;
                            border: 1px solid #aaaaaa;
                            color: #707070;
                            font-size: 12px;
                            text-align: center;
                            line-height: 30px;
                        }
                    }
                }
            }
        }

        // 右: メッセージ欄
        .right {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;

            .top {
                height: 60px;
                flex-shrink: 0;
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 0 24px;
                background: #ffffff;
                border-bottom: 1px solid #ececf0;

                img {
                    width: 36px;
                    height: 36px;
                    border-radius: 50%;
                    border: 0.5px solid #aaaaaa;
                    box-sizing: border-box;
                    object-fit: cover;
                }

                .uname {
                    color: #1d1d1f;
                    font-size: 16px;
                    font-weight: bold;
                }
            }

            .info-box {
                flex: 1;
                min-height: 0;
                overflow-y: auto;
                overflow-x: hidden;
                box-sizing: border-box;
                padding: 6px 24px 14px;
                background: #f7f7f9;
            }

            .more {
                text-align: center;
                color: #8e8e93;
                font-size: 12px;
            }

            .info-item {
                width: 100%;

                // 日付
                .rig-time {
                    display: table;
                    margin: 14px auto 0;
                    padding: 3px 12px;
                    border-radius: 10px;
                    background: #ececf0;
                    color: #6e6e73;
                    font-size: 12px;

                    &:empty {
                        display: none;
                    }
                }

                .info-message {
                    display: flex;
                    flex-wrap: wrap;
                    margin-top: 14px;

                    // メッセージ横のアイコンと名前(相手側・自分側共通)
                    .le {
                        width: 44px;
                        flex-shrink: 0;
                        display: flex;
                        flex-direction: column;
                        align-items: center;

                        .left-img img {
                            display: block;
                            width: 32px;
                            height: 32px;
                            border-radius: 50%;
                            border: 0.5px solid #aaaaaa;
                            box-sizing: border-box;
                            object-fit: cover;
                        }

                        .left-name {
                            max-width: 44px;
                            margin-top: 2px;
                            color: #8e8e93;
                            font-size: 10px;
                            text-align: center;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }
                    }

                    // 吹き出し(相手側・自分側共通)
                    .ri {
                        max-width: #{"min(70%, 560px)"};
                        box-sizing: border-box;
                        padding: 10px 14px;
                        color: #1d1d1f;
                        font-size: 14px;
                        line-height: 1.7;
                        white-space: pre-wrap;
                        word-break: break-all;
                        word-wrap: break-word;
                    }

                    .info-left {
                        width: 100%;
                        display: flex;
                        align-items: flex-end;

                        .le {
                            margin-right: 10px;
                        }

                        .ri {
                            background: #ffffff;
                            border: 1px solid #e5e5ea;
                            border-radius: 16px 16px 16px 4px;
                        }

                        .m {
                            margin-left: 10px;
                            color: #8e8e93;
                            font-size: 11px;
                        }
                    }

                    .info-right {
                        width: 100%;
                        display: flex;
                        justify-content: flex-end;
                        align-items: flex-end;

                        .m {
                            margin-right: 10px;
                            color: #8e8e93;
                            font-size: 11px;
                            line-height: 1.4;
                            text-align: right;

                            .di {
                                text-align: right;
                                color: #8e8e93;
                            }
                        }

                        .dels {
                            margin-right: 8px;
                        }

                        .ri {
                            margin-right: 10px;
                            background: #87fa7d;
                            border-radius: 16px 16px 4px 16px;
                        }
                    }
                }
            }
        }
    }

    // 入力欄(3行分の高さ。入力に合わせて最大160pxまで伸びる)
    .send-box {
        width: 100%;
        flex-shrink: 0;
        box-sizing: border-box;
        padding: 12px 24px 0;
        background: #ffffff;
        border-top: 1px solid #ececf0;

        textarea {
            display: block;
            width: 100%;
            height: 84px;
            max-height: 160px;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #d2d2d7;
            border-radius: 10px;
            outline: none;
            resize: none;
            overflow-y: auto;
            color: #1d1d1f;
            line-height: 1.7;
            font-family: inherit;

            &:focus {
                border-color: #1a73e8;
            }
        }
    }

    .bot {
        width: 100%;
        flex-shrink: 0;
        box-sizing: border-box;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 24px 12px;
        background: #ffffff;

        img {
            width: 18px;
            height: 20px;
            cursor: pointer;
        }
    }

    // 送信ボタン
    .send-btn {
        height: 40px;
        padding: 0 20px;
        border: 0;
        border-radius: 20px;
        background: #1a73e8;
        color: #ffffff;
        font-size: 14px;
        font-weight: 500;
        font-family: inherit;
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;

        &:hover {
            background: #1557b0;
        }

        &.off {
            opacity: 0.5;
        }
    }

    .fixed {
        position: fixed;
        top: 0%;
        left: 0%;
        width: 100vw;
        height: 100vh;
        background: rgba(29, 29, 31, 0.2);

        .zhe {
            position: absolute;
            width: 100vw;
            height: 100vh;
            background: rgba(29, 29, 31, 0.2);
        }

        .box {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 345px;
            height: 281px;
            background: #ffffff;
            border-radius: 10px;

            .title {
                width: 285px;
                margin: 0 auto;
                padding-top: 30px;
                text-align: center;
                color: #707070;
                font-size: 14px;
            }

            .btn,
            .btn1 {
                width: 240px;
                height: 48px;
                margin: 0 auto;
                line-height: 48px;
                background: #d93025;
                border-radius: 10px;
                color: #ffffff;
                font-size: 16px;
                text-align: center;
            }

            .btn1 {
                color: #707070;
                background: rgba(29, 29, 31, 0.1);
            }

            .btn {
                margin-top: 60px;
                margin-bottom: 30px;
            }
        }
    }
</style>