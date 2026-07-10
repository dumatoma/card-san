<template>
    <div>
        <div class="flex">
            <div class="tit">自動メッセージ設定</div>
            <div class="yulan shou" v-if="tab == 2" @click="previewShow = true">
                Preview
            </div>
        </div>

        <div class="table" v-if="tab == 1">
            <div class="tab-item no-size" style="
          background: rgba(29, 29, 31, 0.05);
          border-top: 1px solid #d2d2d7;
        ">
                <div class="top" style="width: 160px">内容</div>
                <div class="top" style="width: 32px; text-align: center">編集</div>
                <div class="top" style="width: 112px; text-align: center">
                    ステータス
                </div>
            </div>
            <div class="tab-item no-size">
                <div class="left">予約確認</div>
                <div class="midd shou" @click="(tab = 2),edit(1,'予約確認')">
                    <img src="../static/edit.png" alt="" />
                </div>
                <div class="right shou" @click="change1">
                    <div :class="oneShow == false ? 'lef lef-active ' : 'lef'">OFF</div>
                    <div :class="oneShow == true ? 'rig rig-active' : 'rig'">ON</div>
                </div>
            </div>


            <div class="tab-item no-size">
                <div class="left">予約リマインダー</div>
                <div class="midd shou" @click="(tab = 2),edit(2,'予約リマインダー')">
                    <img src="../static/edit.png" alt="" />
                </div>
                <div class="right shou" @click="change2">
                    <div :class="twoShow == false ? 'lef lef-active ' : 'lef'">OFF</div>
                    <div :class="twoShow == true ? 'rig rig-active' : 'rig'">ON</div>
                </div>
            </div>


            <div class="tab-item no-size">
                <div class="left">予約内容変更</div>
                <div class="midd shou" @click="(tab = 2),edit(5,'予約内容変更')">
                    <img src="../static/edit.png" alt="" />
                </div>
                <div class="right shou" @click="change5">
                    <div :class="fiveShow == false ? 'lef lef-active ' : 'lef'">OFF</div>
                    <div :class="fiveShow == true ? 'rig rig-active' : 'rig'">ON</div>
                </div>
            </div>


            <div class="tab-item no-size">
                <div class="left">予約キャンセル</div>
                <div class="midd shou" @click="(tab = 2),edit(3,'予約キャンセル')">
                    <img src="../static/edit.png" alt="" />
                </div>
                <div class="right shou" @click="change3">
                    <div :class="threeShow == false ? 'lef lef-active ' : 'lef'">OFF</div>
                    <div :class="threeShow == true ? 'rig rig-active' : 'rig'">ON</div>
                </div>
            </div>

            <div class="tab-item no-size">
                <div class="left">サンキューメッセージ</div>
                <div class="midd shou" @click="(tab = 2),edit(4,'サンキューメッセージ')">
                    <img src="../static/edit.png" alt="" />
                </div>
                <div class="right shou" @click="change4">
                    <div :class="fourShow == false ? 'lef lef-active ' : 'lef'">OFF</div>
                    <div :class="fourShow == true ? 'rig rig-active' : 'rig'">ON</div>
                </div>
            </div>
        </div>
        <div class="yue no-size" v-if="tab == 2">
            <div class="title no-size" v-text="titles">

            </div>
            <div class="yue-box no-size">
                <div class="send-txt" v-show="titles == '予約確認'">
                    予約確定時に送信されます
                </div>
                <div class="send-txt" v-show="titles == '予約リマインダー'">
                    予約日時の24時間前に送信されます
                </div>
                <div class="send-txt" v-show="titles == '予約キャンセル'">
                    予約削除の際のメッセージ
                </div>
                <div class="send-txt" v-show="titles == 'サンキューメッセージ'">
                    来店日時の24時間後に送信されます
                </div>
                <div class="send-txt" v-show="titles == '予約内容変更'">
                    予約内容変更時に送信されます
                </div>
                <div class="texta no-size">
                    <div class="tt" v-show="titles == '予約確認'">
                        <textarea placeholder="【予約受付のお知らせ】"
                            style="resize: none; border: none;outline: none;width: 100%;font-size: 14px !important;"
                            v-model="intitle"></textarea>
                    </div>
                    <div class="tt" v-show="titles == '予約リマインダー'">

                        <textarea placeholder="【予約確認のお知らせ】"
                            style="resize: none; border: none;outline: none;width: 100%;font-size: 14px !important;"
                            v-model="intitle"></textarea>
                    </div>
                    <div class="tt" v-show="titles == '予約キャンセル'">
                        <textarea placeholder="【予約キャンセルのお知らせ】"
                            style="resize: none; border: none;outline: none;width: 100%;font-size: 14px !important;"
                            v-model="intitle"></textarea>
                    </div>
                    <div class="tt" v-show="titles == '予約内容変更'">

                        <textarea placeholder="【予約内容変更】"
                            style="resize: none; border: none;outline: none;width: 100%;font-size: 14px !important;"
                            v-model="intitle"></textarea>
                    </div>
                    <div class="tt-boxs">
                        <div class="texts" :style="titles == 'サンキューメッセージ'?'margin-top:0':''" style="display: flex;">
                            <span>name</span> <input type="text" placeholder="様"
                                style="border: none;outline: none;flex: 1;margin-left: 10px;" v-model="inname"> </div>
                        <div class="txtare">
                            <el-input auto-complete='off' type='textarea' :placeholder="place1"
                                :autosize="{minRows:3,maxRows:100}" v-model="oneText" class="no-scroll">
                            </el-input>
                        </div>
                        <div v-show="titles != 'サンキューメッセージ'">
                            <div class="text">
                                <!-- <textarea name="" id="" cols="30" ></textarea> -->
                                <!-- <textarea v-model="txtt" rows="10" readonly></textarea> -->
                                <div v-text="txtt" class="nor"></div>
                            </div>
                        </div>

                        <div class="txtare" style="text-align: left;" v-show="titles != 'サンキューメッセージ'">
                            <!-- <textarea v-model="twoText" :placeholder="place2"></textarea> -->

                            <el-input auto-complete='off' type='textarea' :placeholder="place2"
                                :autosize="{minRows:3,maxRows:100}" v-model="twoText" class="no-scroll">
                            </el-input>
                        </div>

                        <div v-show="titles != 'サンキューメッセージ'">
                            <div class="text">
                                <div v-text="shopinfos" class="nor"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="setPriview" :style="sel == 1?'border:1px solid #1d1d1f' : ''" v-if="titles == 'サンキューメッセージ'">
                    <div class="sel" :style="sel == 1?'border:1px solid #2699fb' : ''"
                        @click="sel == 1?sel = 0 : sel = 1">
                        <div class="inner" v-if="sel == 1"></div>
                    </div>
                    <div class="pword" :style="sel == 1?'color:#1d1d1f;font-weight:bold' : ''">
                        Googleレビューリンクボタンの設置
                    </div>
                </div>


                <div class="fle-btn">
                    <div class="lef-btn shou" @click="tab = 1">戻　る</div>
                    <div class="rig-btn shou" @click="keep()">保　存</div>
                </div>
            </div>
        </div>
        <div class="fixed" v-show="previewShow">
            <div class="zhe"></div>
            <div class="per-box">
                <div class="tit">
                    <img class="shou" @click="previewShow = false" src="../static/wclose.png" alt="" />
                    <div class="t">プレビュー</div>
                </div>

                <div class="preview">
                    <div class="lef">
                        <img :src="avatar" style="border-radius: 50%;object-fit: cover;" alt="" />
                    </div>
                    <div class="rig">
                        <div class="tt" v-if="intitle">
                            {{intitle}}
                        </div>
                        <div style="margin: 24px 0">name {{inname}}</div>
                        <pre style="margin-bottom: 24px">{{ oneText }}</pre>
                        <pre v-show="titles != 'サンキューメッセージ'">{{ txtt }}</pre>
                        <pre style="margin: 24px 0">{{ twoText }}</pre>
                        <pre v-show="titles != 'サンキューメッセージ'">{{ shopinfos }}</pre>
                    </div>
                </div>

                <div class="preview" style="margin-top: 10px;" v-if="sel == 1 && titles == 'サンキューメッセージ'">
                    <div class="lef">
                        <img :src="avatar" style="border-radius: 50%;object-fit: cover;" alt="" />
                    </div>
                    <div class="rig" style="padding: 0;background: transparent;">
                        <div style="width: 215px;height: 60px;">
                            <img style="width: 215px;height: 60px;object-fit: cover;border-radius: 7px;"
                                :src="previewImage" alt="" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fixed" v-show="keepShow">
            <div class="zhe"></div>
            <div class="sus-box">
                <img src="../static/success.png" alt="" />
                <div class="keep">保存しました</div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        getStore,
        setMessageOn
    } from "@/http/api.js"
    export default {
        name: "",
        components: {},
        data() {
            return {
                sel: 0,
                keepShow: false,
                txtt: `予約番号：#reserve number  　
日時：00月00日(-)　00:00 ～  
メニュー：#menu title  
価格：¥price 
担当者：#担当者氏名
指名料：￥price
 `,
                shopinfos: `#店舗名
#電話番号
#住所`,
                // 予約確認
                oneShow: false,
                //   予約リマインダー
                twoShow: false,
                //   予約キャンセル
                threeShow: false,
                //   サンキューメッセージ
                fourShow: false,
                fiveShow: false,
                titles: "",
                tab: 1,
                previewShow: false,
                info: {},
                inname: "",
                intitle: "",
                oneText: "",
                twoText: "",
                place1: "",
                place2: "",
                avatar: "",
                previewImage: "",
                title: ""
            };
        },
        created() {
            this.getInfo()
            let admin = localStorage.getItem("admin")
            let a = JSON.parse(admin)
            this.avatar = a.avatar
        },
        methods: {
            getInfo() {
                let that = this
                getStore().then((res) => {
                    console.log(res)
                    if (res.code == 200) {
                        that.oneShow = res.data.shop_info.order_confirm_on == 1 ? true : false
                        that.twoShow = res.data.shop_info.order_notice_on == 1 ? true : false
                        that.threeShow = res.data.shop_info.order_cancel_on == 1 ? true : false
                        that.fourShow = res.data.shop_info.thanks_on == 1 ? true : false
                        that.fiveShow = res.data.shop_info.order_update_on == 1 ? true : false
                        that.info = res.data.shop_info
                        that.previewImage = res.data.shop_info.comment_button_style_image
                    }
                })
            },
            edit(e, name) {
                let that = this
                console.log(name)
                console.log("e=" + e)
                console.log(that.tab)

                that.titles = name
                if (e == 1) {
                    that.intitle = that.info.order_confirm_message[0] || '【予約受付のお知らせ】'
                    that.inname = that.info.order_confirm_message[1] || ''
                    that.oneText = that.info.order_confirm_message[2] || ''
                    that.twoText = that.info.order_confirm_message[3] || ''
                    that.place1 = `ご予約ありがとうございます。
以下の通りご予約承りました。`
                    that.place2 = `通知内容に心当たりのない場合や、キャンセル、来店時刻に遅れる場合は、直接店舗にご連絡頂きますようお願いいたします。`
                } else if (e == 2) {
                    that.intitle = that.info.order_notice_message[0] || '【予約確認のお知らせ】'
                    that.inname = that.info.order_notice_message[1] || ''
                    that.oneText = that.info.order_notice_message[2] || '明日ご来店日となりますので、お知らせいたします。'
                    that.twoText = that.info.order_notice_message[3] || 'スタッフ一同、心よりご来店お待ちしております。'
                    that.place1 = `明日ご来店日となりますので、お知らせいたします。`
                    that.place2 = `スタッフ一同、心よりご来店お待ちしております。`
                } else if (e == 3) {
                    that.intitle = that.info.order_cancel_message[0] || '【予約内容変更】'
                    that.inname = that.info.order_cancel_message[1] || ''
                    that.oneText = that.info.order_cancel_message[2] || '以下内容のご予約のキャンセルを受理いたしました。'
                    that.twoText = that.info.order_cancel_message[3] || '内容のご確認をお願いいたします。'
                    that.place1 = `以下内容のご予約のキャンセルを受理いたしました。`
                    that.place2 = `内容のご確認をお願いいたします。`
                } else if (e == 4) {
                    // that.intitle = that.info.thanks_message[0] || ''
                    that.inname = that.info.thanks_message[0] || ''
                    that.oneText = that.info.thanks_message[1] || 'この度は当店をご利用頂き、誠にありがとうございました。\n今後も当店をよろしくお願い致します。'
                    // that.twoText = that.info.thanks_message[3] || ''
                    that.place1 = `この度は当店をご利用頂き、誠にありがとうございました。\n今後も当店をよろしくお願い致します。`
                    console.log(that.info.thanks_message)
                    that.sel = that.info.thanks_message[3]
                    // that.place2 = `今後も当店をよろしくお願い致します。`
                } else if (e == 5) {
                    that.intitle = that.info.order_update_message[0] || ''
                    that.inname = that.info.order_update_message[1] || ''
                    that.oneText = that.info.order_update_message[2] || 'ご予約の内容が変更になりましたのでお知らせします。変更内容をご確認お願いします。'
                    that.twoText = that.info.order_update_message[3] || 'キャンセルや来店時刻に遅れる場合は、直接店舗にご連絡頂きますようお願いいたします。'
                    that.place1 = `ご予約の内容が変更になりましたのでお知らせします。
変更内容をご確認お願いします。`
                    that.place2 = `キャンセルや来店時刻に遅れる場合は、直接店舗にご連絡頂きますようお願いいたします。`
                }
            },
            change1() {
                let that = this
                that.oneShow = that.oneShow == false ? true : false
                that.changeStatus('order_confirm_on')
            },
            change2() {
                let that = this
                that.twoShow = that.twoShow == false ? true : false
                that.changeStatus('order_notice_on')
            },
            change3() {
                let that = this
                that.threeShow = that.threeShow == false ? true : false
                that.changeStatus('order_cancel_on')
            },
            change4() {
                let that = this
                that.fourShow = that.fourShow == false ? true : false
                that.changeStatus('thanks_on')
            },
            change5() {
                let that = this
                that.fiveShow = that.fiveShow == false ? true : false
                that.changeStatus('order_update_on')
            },
            changeStatus(e) {
                console.log(e)
                let that = this
                let data = {}
                data['type'] = "message_on"
                data['field'] = e
                data['order_confirm_on'] = that.oneShow == true ? 1 : 0
                data['order_notice_on'] = that.twoShow == true ? 1 : 0
                data['order_cancel_on'] = that.threeShow == true ? 1 : 0
                data['thanks_on'] = that.fourShow == true ? 1 : 0
                data['order_update_on'] = that.fiveShow == true ? 1 : 0
                setMessageOn(data).then((res) => {
                    if (res.code == 200) {
                        that.$message({
                            message: res.message,
                            type: 'success',
                            offset: 400
                        });
                        that.getInfo()
                    } else {
                        that.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });
                    }
                })
            },
            keep() {
                let that = this
                let data = {}
                console.log(that.titles)
                console.log(that.titles == 'サンキューメッセージ')
                data['type'] = 'message'
                if (that.titles == '予約確認') {
                    let temp = []
                    if (that.intitle == '') {
                        temp.push('【予約受付のお知らせ】')
                    } else {
                        temp.push(that.intitle)
                    }
                    temp.push(that.inname)
                    if (that.oneText == '') {
                        temp.push(that.place1)
                    } else {
                        temp.push(that.oneText)
                    }
                    if (that.twoText == '') {
                        temp.push(that.place2)
                    } else {
                        temp.push(that.twoText)
                    }
                    data['field'] = 'order_confirm_message'
                    data['order_confirm_message'] = temp
                    data['order_notice_message'] = that.info.order_notice_message
                    data['order_cancel_message'] = that.info.order_cancel_message
                    data['thanks_message'] = that.info.thanks_message
                    data['order_update_message'] = that.info.order_update_message
                } else if (that.titles == '予約リマインダー') {
                    let temp = []
                    if (that.intitle == '') {
                        temp.push('【予約確認のお知らせ】')
                    } else {
                        temp.push(that.intitle)
                    }
                    temp.push(that.inname)
                    if (that.oneText == '') {
                        temp.push(that.place1)
                    } else {
                        temp.push(that.oneText)
                    }
                    if (that.twoText == '') {
                        temp.push(that.place2)
                    } else {
                        temp.push(that.twoText)
                    }
                    data['field'] = 'order_notice_message'
                    data['order_confirm_message'] = that.info.order_confirm_message
                    data['order_notice_message'] = temp
                    data['order_cancel_message'] = that.info.order_cancel_message
                    data['thanks_message'] = that.info.thanks_message
                    data['order_update_message'] = that.info.order_update_message
                } else if (that.titles == '予約キャンセル') {
                    let temp = []
                    if (that.intitle == '') {
                        temp.push('【予約キャンセルのお知らせ】')
                    } else {
                        temp.push(that.intitle)
                    }
                    temp.push(that.inname)
                    if (that.oneText == '') {
                        temp.push(that.place1)
                    } else {
                        temp.push(that.oneText)
                    }
                    if (that.twoText == '') {
                        temp.push(that.place2)
                    } else {
                        temp.push(that.twoText)
                    }
                    data['field'] = 'order_cancel_message'
                    data['order_confirm_message'] = that.info.order_confirm_message
                    data['order_notice_message'] = that.info.order_notice_message
                    data['order_cancel_message'] = temp
                    data['thanks_message'] = that.info.thanks_message
                    data['order_update_message'] = that.info.order_update_message
                } else if (that.titles == 'サンキューメッセージ') {
                    let temp = []
                    temp.push(that.inname)
                    if (that.oneText == '') {
                        temp.push(that.place1)
                    } else {
                        temp.push(that.oneText)
                    }
                    if (that.twoText == '') {
                        temp.push(that.place2)
                    } else {
                        temp.push(that.twoText)
                    }
                    console.log("sel", that.sel)
                    temp.push(that.sel)
                    data['field'] = 'thanks_message'
                    data['order_confirm_message'] = that.info.order_confirm_message
                    data['order_notice_message'] = that.info.order_notice_message
                    data['order_cancel_message'] = that.info.order_cancel_message
                    data['thanks_message'] = temp
                    data['order_update_message'] = that.info.order_update_message
                } else {
                    let temp = []
                    if (that.intitle == '') {
                        temp.push('【予約内容変更】')
                    } else {
                        temp.push(that.intitle)
                    }
                    temp.push(that.inname)
                    if (that.oneText == '') {
                        temp.push(that.place1)
                    } else {
                        temp.push(that.oneText)
                    }
                    if (that.twoText == '') {
                        temp.push(that.place2)
                    } else {
                        temp.push(that.twoText)
                    }
                    data['field'] = 'order_update_message'
                    data['order_confirm_message'] = that.info.order_confirm_message
                    data['order_notice_message'] = that.info.order_notice_message
                    data['order_cancel_message'] = that.info.order_cancel_message
                    data['thanks_message'] = that.info.thanks_message
                    data['order_update_message'] = temp
                }
                setMessageOn(data).then((res) => {
                    if (res.code == 200) {
                        that.keepShow = true;
                        that.getInfo()
                        setTimeout(() => {
                            that.tab = 1
                            that.keepShow = false;
                        }, 1500);
                    }
                })
            },
        },
    };
</script>

<style lang="scss" scoped>
    textarea {
        font-size: 12px !important;
        resize: none !important;
        border: none !important;
        outline: none !important;
        height: auto !important;
    }

    .setPriview {
        display: flex;
        align-items: center;
        margin-top: 20px;
        width: 323px;
        height: 44px;
        border: 1px solid #AAAAAA;
        box-sizing: border-box;
        padding-left: 14px;
        font-size: 16px;
        color: #AAAAAA;

        .sel {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 1px solid #AAAAAA;
            box-sizing: border-box;
            padding: 3px;
            margin-right: 10px;
            cursor: pointer;

            .inner {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background: #1a73e8;
            }

        }
    }

    .nor {
        white-space: pre-wrap;
        color: #007aff;
        margin: 24px 0;
    }

    input {
        font-size: 14px !important;
    }

    ::v-deep .el-textarea__inner {
        line-height: 1.2 !important;
        resize: none !important;
        border: none !important;
        outline: none !important;
        padding: 0 !important;
        font-size: 12px;
        color: #000;
    }

    .midd:hover {
        background: rgba(29, 29, 31, 0.1);
    }

    .no-scroll textarea {
        overflow: hidden !important;
        /* 禁用滚动条 */
        resize: none !important;
        /* 禁止用户手动调整文本框的尺寸 */
        height: auto !important;
        /* 强制将高度设置为自适应 */
        max-height: none !important;
        /* 禁用最大高度限制 */
    }

    .flex {
        width: 420px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;

        .yulan {
            width: 124px;
            height: 30px;
            line-height: 30px;
            background: rgba(29, 29, 31, 0.1);
            border-radius: 15px 15px 15px 15px;
            opacity: 1;
            border: 1px solid #707070;
            color: #707070;
            font-size: 16px;
            text-align: center;
        }
    }

    .tit {
        color: #1d1d1f;
        font-size: 21px;
        font-weight: bold;
    }

    .table {
        width: 410px;
        //   height: 296px;
        background: #ffffff;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);

        .tab-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #707070;
            font-size: 16px;
            padding: 0 20px;
            width: 100%;
            height: 62px;
            border-bottom: 1px solid #d2d2d7;

            .left {
                width: 160px;
                color: rgba(29, 29, 31, 0.9);
            }

            .midd {
                width: 36px;
                padding: 3px 0;
                border-radius: 50%;
                text-align: center;

                img {
                    width: 30px;
                    height: 30px;
                    vertical-align: middle;
                }
            }

            .right {
                width: 112px;
                height: 36px;
                font-size: 16px;
                display: flex;
                align-items: center;
                text-align: center;
                border: 1px solid #aaaaaa;

                .lef {
                    width: 56px;
                    color: #aaaaaa;
                }

                .lef-active {
                    width: 56px;
                    height: 36px;
                    line-height: 36px;
                    color: #fff;
                    background: #aaaaaa;
                }

                .rig {
                    width: 56px;
                    color: #aaaaaa;
                }

                .rig-active {
                    width: 56px;
                    height: 36px;
                    line-height: 36px;
                    background: #34c759;
                    color: #fff;
                }
            }
        }
    }

    .yue {
        width: 420px;
        height: 662px;
        background: #ffffff;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
        overflow-y: auto;

        .title {
            width: 100%;
            padding-left: 20px;
            height: 48px;
            line-height: 48px;
            font-size: 16px;
            color: #1d1d1f;
            border-top: 1px solid #d2d2d7;
            font-weight: 600;
            border-bottom: 1px solid #d2d2d7;
            background: rgba(29, 29, 31, 0.05);
        }

        .yue-box {
            margin-top: 30px;
            padding-left: 49px;

            .send-txt {
                font-size: 14px;
                color: #707070;
                margin-bottom: 10px;
            }

            .texta {
                width: 323px;
                height: 440px;
                background: #ffffff;
                border-radius: 10px 10px 10px 10px;
                border: 1px solid #aaaaaa;
                overflow: scroll;
                overflow-x: hidden;
                padding-top: 14px;
                padding-left: 14px;
                font-size: 14px;

                .tt {
                    color: #1d1d1f;
                }

                .tt-boxs {
                    width: 100%;

                    .txtare {
                        width: 100%;

                        textarea {
                            border: 0;
                            outline: none;
                            resize: none;
                            width: 100%;
                            height: 100px;
                        }
                    }

                    .texts {
                        margin-top: 0;
                        margin-bottom: 20px;

                        span {
                            color: #007aff;
                        }
                    }

                    .text {
                        // margin-top: 12px;
                        width: 100%;

                        textarea {
                            font-size: 14px;
                            // height: auto;
                            width: 100%;
                            color: #007aff;
                            border: 0;
                            outline: none;
                            resize: none;
                        }
                    }
                }
            }

            .fle-btn {
                margin: 20px 77px;
                margin-left: 28px;
                display: flex;
                justify-content: space-between;
                align-items: center;

                .lef-btn,
                .rig-btn {
                    width: 120px;
                    height: 40px;
                    text-align: center;
                    line-height: 40px;
                    background: rgba(29, 29, 31, 0.05);
                    border-radius: 26px 26px 26px 26px;
                    border: 1px solid #707070;
                    color: #707070;
                    font-size: 16px;
                }

                .rig-btn {
                    background: #1a73e8;
                    border: 0;
                    color: #ffffff;
                }
            }
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
    }

    .sus-box {
        width: 160px;
        height: 146px;
        background: #ffffff;
        border-radius: 10px 10px 10px 10px;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;

        img {
            margin-top: 30px;
            width: 50px;
            height: 50px;
        }

        .keep {
            margin-top: 20px;
            font-size: 16px;
            color: #2699fb;
        }
    }

    .per-box {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 300px;
        height: 647px;
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        overflow-y: auto;

        .tit {
            position: relative;
            font-size: 20px;
            font-weight: 700;
            border-bottom: 1px solid #d2d2d7;
            width: 100%;
            height: 40px;
            line-height: 40px;
            text-align: center;
            background-color: rgb(244, 244, 246);

            img {
                position: absolute;
                left: 5px;
                top: 5px;
                width: 30px;
                height: 30px;
            }
        }

        .top-zhu {
            margin: 0 auto;
            margin-top: 20px;
            width: 90%;
            padding: 5px;
            border-radius: 10px;
            color: #fff;
            font-size: 10px;
            background-color: rgb(112, 112, 112);
        }

        .preview {
            margin: 0 auto;
            margin-top: 20px;
            width: 90%;
            display: flex;

            .lef {
                margin-right: 9px;

                img {
                    width: 38px;
                    height: 38px;
                    border-radius: 50%;
                    border: 1px solid #d2d2d7;
                }
            }

            .rig {
                width: 246px;
                border-radius: 10px;
                color: #1d1d1f;
                padding: 10px;
                font-size: 14px;
                background: rgba(143, 168, 248, 0.2);

                pre {
                    word-break: break-all;
                    word-wrap: break-word;
                    white-space: pre-wrap;
                    width: 100%;
                }
            }
        }
    }
</style>