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
                    <div :class="current == index ? 'item move no-size' : 'item no-size'"
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
                                <div class="m"> </div>
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
                    <textarea v-model="messageTxt" placeholder="Enterで送信／Shift+Enterで改行"
                        @keydown.enter="handleKeyCode($event)" @focus="getf = true" @blur="getf = false"></textarea>
                </div>
                <div class="bot no-size">

                    <el-upload class="upload-demo" accept="image/*" action="https://api.card-san.jp/tool/upload/file"
                        :on-success="handleAvatarSuccess" :limit="1" :show-file-list="false" name="file">
                        <div class="hx">
                            <img src="../static/fasong.png" class="huixing" alt="" />
                        </div>

                    </el-upload>

                    <div>
                        <img src="../static/send.png" class="huixing"
                            :style="messageTxt == ''?'opacity:0.6 !important' : ''" alt=""
                            @click.stop="handleSendText" />
                    </div>

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
        width: 30px;
        height: 30px;
        border-radius: 50%;
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

    .content {
        margin-top: 18px;
        width: 872px;
        height: 608px;

        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
        display: flex;

        .left {
            width: 375px;
            border-right: 1px solid #d2d2d7;

            .lef-top {
                width: 100%;
                height: 100px;
                background: #f5f5f7;
                border-bottom: 1px solid #d2d2d7;

                .search {
                    width: 100%;
                    padding: 16px;
                    position: relative;

                    img {
                        position: absolute;
                        top: 26px;
                        left: 26px;
                        width: 16px;
                        height: 16px;
                    }

                    input {
                        padding-left: 30px;
                        width: 100%;
                        height: 35px;
                        border-radius: 10px;
                        background: rgb(233, 233, 234);
                        border: 0;
                        outline: none;
                    }
                }

                .total {
                    margin-left: 20px;
                    color: #1d1d1f;
                    font-size: 16px;
                }
            }

            .user-list {
                width: 100%;
                height: calc(608px - 100px);
                overflow-y: auto;
                overflow-x: hidden;

                .move {
                    // padding: 10px 0;
                    background: rgba(29, 29, 31, 0.05) !important;
                }

                // border-right: 1px solid #d2d2d7;
                .item {
                    // padding: 10px 0;
                    // margin-right: 16px;

                    background: #ffffff;

                    .box {
                        padding: 20px 0;
                        margin-left: 16px;
                        width: calc(100% - 16px);
                        // height: 76px;
                        display: flex;
                        border-bottom: 1px solid rgba(60, 60, 67, 0.36);
                    }

                    // align-items: center;
                    .ite-left {
                        // vertical-align: middle;
                        display: flex;
                        // align-items: center;
                        line-height: 76px;
                        margin-right: 10px;
                        height: 100%;

                        img {
                            width: 40px;
                            height: 40px;
                            border-radius: 50%;
                            object-fit: cover;
                        }
                    }

                    .ite-midd {
                        width: 251px;
                        margin-right: 16px;

                        .uname {
                            color: #1d1d1f;
                            font-size: 16px;
                            margin-bottom: 4px;
                        }

                        .info {
                            width: 100%;
                            color: #707070;
                            font-size: 14px;
                        }
                    }

                    .ite-rig {
                        .time {
                            color: #86868b;
                            font-size: 10px;
                        }

                        .huis {
                            margin-top: 5px;
                            position: relative;

                            img {
                                width: 26px;
                                height: 26px;
                            }

                            .del {
                                position: absolute;
                                right: 14px;
                                bottom: -28px;
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

                        .hui {
                            width: 26px;
                            height: 26px;
                            background: #d93025;
                            border-radius: 50%;
                            text-align: center;
                            line-height: 26px;
                            color: #ffffff;
                            margin-top: 5px;
                        }
                    }
                }
            }
        }

        .right {
            width: 498px;

            .top {
                display: flex;
                align-items: center;
                height: 100px;
                padding-left: 30px;
                background: #f5f5f7;
                border-bottom: 1px solid #d2d2d7;

                img {
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                }

                .uname {
                    margin-left: 20px;
                }
            }

            .info-box {
                width: 100%;
                height: 370px;
                overflow: scroll;
                overflow-x: hidden;
                // &::-webkit-scrollbar {
                //   width: 0px;
                //   height: 0px;
                //   background-color: rgba(240, 240, 240, 1);
                // }
            }

            .info-item {
                width: 100%;
                padding: 0 16px 0 14px;

                .rig-time {
                    margin-top: 30px;
                    color: #86868b;
                    font-size: 14px;
                    text-align: center;
                }

                .info-message {
                    display: flex;
                    flex-wrap: wrap;
                    margin-top: 16px;

                    .info-left {
                        width: 100%;
                        display: flex;

                        .le {
                            margin-right: 9px;

                            .left-img {
                                img {
                                    width: 36px;
                                    height: 36px;
                                    border-radius: 50%;
                                    object-fit: cover;
                                }
                            }

                            .left-name {
                                color: #86868b;
                                margin-top: 3px;
                                font-size: 8px;
                            }
                        }

                        .ri {
                            max-width: 246px;
                            display: flex;
                            align-items: center;
                            background: rgba(143, 168, 248, 0.2);
                            color: #1d1d1f;
                            font-size: 14px;
                            padding: 10px;
                            word-break: break-all;
                            word-wrap: break-word;
                            white-space: pre-wrap;
                            border-radius: 10px;
                        }

                        .m {
                            margin-left: 10px;
                            color: #86868b;
                            font-size: 10px;
                            align-self: flex-end;
                        }
                    }

                    .info-right {
                        width: 100%;
                        display: flex;
                        justify-content: flex-end;

                        .m {
                            margin-right: 10px;
                            color: #86868b;
                            font-size: 10px;
                            align-self: flex-end;

                            .di {
                                text-align: right;
                                color: #86868b;
                                margin-bottom: 4px;
                            }

                            .dian {
                                text-align: right;

                                img {
                                    width: 26px;
                                    height: 26px;
                                }
                            }

                            .del-box {
                                width: 56px;
                                height: 30px;
                                background: #ffffff;
                                opacity: 1;
                                border: 1px solid #aaaaaa;

                                div {
                                    text-align: center;
                                    margin: 4px 0;
                                    width: 56px;
                                    height: 22px;
                                    line-height: 22px;
                                    background: rgba(29, 29, 31, 0.1);
                                }
                            }
                        }

                        .ri {
                            max-width: 246px;
                            background: #87fa7d;
                            color: #1d1d1f;
                            font-size: 14px;
                            padding: 10px;
                            word-break: break-all;
                            word-wrap: break-word;
                            white-space: pre-wrap;
                            margin-right: 10px;
                            border-radius: 10px;
                            line-height: 32px;
                        }

                        .le {
                            .left-img {
                                img {
                                    width: 36px;
                                    height: 36px;
                                    border-radius: 50%;
                                    object-fit: cover;
                                }
                            }

                            .left-name {
                                color: #86868b;
                                margin-top: 3px;
                                font-size: 8px;
                            }
                        }
                    }
                }
            }
        }
    }

    .bot {
        width: 100%;
        height: 40px;
        background: #f5f5f7;
        border-top: 1px solid #d2d2d7;
        border-bottom: 1px solid #d2d2d7;
        display: flex;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 0 10px;
        align-items: center;

        img {
            width: 18px;
            height: 20px;
            // margin-left: 19px;
            cursor: pointer;
        }
    }

    .send-box {
        width: 100%;
        height: 98px;
        padding: 10px 13px;
        border-top: 1px solid #d2d2d7;

        textarea {
            border: 0;
            outline: none;
            width: 100%;
            height: 100%;
            resize: none;
            overflow: scroll;
            overflow-x: hidden;
            /*去掉滚动条*/
            // &::-webkit-scrollbar {
            //   width: 0px;

            //   height: 0px;

            //   background-color: rgba(240, 240, 240, 1);
            // }
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