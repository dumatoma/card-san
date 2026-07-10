<template>
    <div class="wrap">
        <!-- 左上角图标 -->
        <div class="big_box">
            <div class="login">
                <img src="../../static/index/dlogo.svg" alt="" />
            </div>
            <div class="content no-size" :class="[
          state == 'one'
            ? 'contentOne'
            : state == 'two'
            ? 'contentTwo'
            : state == 'three'
            ? 'contentThree'
            : 'contentFour',
        ]">
                <div class="bf" v-if="state != 'four'">
                    <!-- 进度条 -->
                    <div class="one">
                        ステップ {{ state == "one" ? "1" : state == "two" ? "2" : "3" }}/3
                    </div>
                    <div class="u-flex two">
                        <div class="grey box_one" v-if="state == 'one'"></div>
                        <div class="grey box_two" v-if="state == 'two'"></div>
                        <div class="grey box_three" v-if="state == 'three'"></div>
                    </div>
                    <!-- 标题 -->
                    <div class="three">
                        {{
              state == "one"
                ? "無料のアカウント登録"
                : state == "two"
                ? "認証コードを入力"
                : "最後のステップ"
            }}
                    </div>
                </div>
                <!-- 第一步 -->
                <div class="int" v-if="state == 'one'">
                    <div class="four">ID(メールアドレス)</div>
                    <input class="five no-size" placeholder-style="color:#aaa;font-size:16px"
                        placeholder="メールアドレスを入力してください" type="text" v-model="ID" />
                    <div class="six no-size u-flex u-row-center u-col-top">
                        <img src="../../static/index/notifiBlue.png" alt="" />
                        <div class="text">
                            スマートフォン、タブレットで確認できるメールアドレス、もしくは勤務用のメールアドレスをご入力いただくと、よりスムーズに、無料トライアル、アプリとの連携やチームメンバーの追加が行えます。
                        </div>
                    </div>
                </div>
                <!-- 第二步 -->
                <div v-if="state == 'two'">
                    <div class="tit_two">
                        アカウント登録されているEメールに送信された<br />桁の認証コードを入力してください。
                    </div>
                    <div class="tit_two" style="margin-top: 10px">
                        {{ID}}
                    </div>
                    <div class="tit_two" style="margin-top: 10px; font-weight: bold">
                        Eメールで送信された認証コードは10分間有効です
                    </div>
                    <div class="u-flex u-row-between" style="margin: 0 auto; margin-top: 40px; width: 390px">
                        <yzm :value.sync="password" :width="48" :height="76" backgroundColor="#ffffff" :bold="true"
                            showVal :length="6" @changeCode="changes">
                        </yzm>
                    </div>
                </div>

                <!-- 第三步 -->
                <div class="int no-size" v-if="state == 'three'">
                    <div class="four">氏名</div>
                    <input class="five no-size" placeholder-style="color:#aaa;font-size:16px"
                        placeholder="あなたの氏名を入力してください" type="text" v-model="name" />
                    <div class="four" style="margin-top: 30px">店舗名</div>
                    <input class="five no-size" placeholder-style="color:#aaa;font-size:16px"
                        placeholder="あなたの店舗名を入力してください" type="text" v-model="store_name" />
                </div>

                <!-- 第四步  state == four -->
                <div class="bf" v-if="state == 'four'">
                    <div class="th_one">ご登録ありがとうございます！</div>
                    <div class="th_two">あなたのアカウントID</div>
                    <div class="th_three">{{ID}}</div>
                    <div class="th_four">
                        ご登録のEメールアドレスにアカウント情報のEメールを送信しましたのでご確認ください。　メールが届いていない場合は、迷惑メールフォルダを確認するか、ご登録のメールアドレスが間違っておりますので、アカウントの再登録をお願いします。
                    </div>
                </div>
                <!-- 第一步确认按钮 -->
                <div class="submit shou" v-if="state == 'one'" :class="[ID ? '' : 'submitCss']" @click="oneClick">
                    次へ
                </div>
                <!-- 第二步确认按钮 -->
                <div class="submit shou" v-if="state == 'two'" :class="password.length > 5 ? '' : 'submitCss'"
                    @click='state = "three"'>
                    最後のステップへ
                </div>
                <!-- 第三步确认按钮 -->
                <div class="submit shou" style="width: 100%" v-if="state == 'three'"
                    :class="[store_name && name ? '' : 'submitCss']" @click="toRegister">
                    無料アカウント登録
                </div>
                <!-- 第四步确认按钮 -->
                <div class="submit shou" style="width: 100%; margin-top: 76px" v-if="state == 'four'" @click="toLogin">
                    ログイン画面に進む
                </div>

                <div class="goback" v-if="state == 'two' || state == 'three'" @click="preStep">
                    < 戻る </div>


                        <!-- 第一步下方提示文字 -->
                        <div class="tip_one tip" v-if="state == 'one'">
                            「次へ」を選択すると、ご登録のメールアドレスに認証コードが送信されます。
                        </div>
                        <!-- 第二步下方提示文字 -->
                        <div class="tip_two tip" v-if="state == 'two'">
                            数分経ってもコードが届かない場合は、認証コードを再送してください。　それでも届かない場合は、迷惑メールフォルダをご確認、もしくはご登録のメールアドレスが間違っておりますので、メールアドレスの登録から行なってください。
                        </div>
                        <div class="tip_blue" style="cursor: pointer;" @click="oneClick" v-if="state == 'two'">認証コードを再送
                        </div>
                        <!-- 第三步下方提示文字 -->
                        <div class="tip" v-if="state == 'three'">
                            <div>[無料アカウントの登録]をクリックすることで、</div>
                            <div class="ly">
                                Card-Sanの <span class="ho" @click="show = true">利用規約とプライバシーポリシー</span> に同意したものとみなされます。
                            </div>
                        </div>
                </div>
            </div>

            <!-- 底部文字 -->
            <div class="bottom">
                Copyright (C) 2022 Customaid:LLC All Rights Reserved.
            </div>
            <div style="height: 20px;"></div>
            <!-- 发送验证码弹窗 -->
            <div class="fixed" v-show="codeShow">
                <div class="zhe"></div>
                <div class="sus-box">
                    <div class="sus_tit">認証コードを送信しました</div>
                    <div class="sus_two">
                        アカウント登録されているEメールに送信された6桁の認証コードを入力してください。
                    </div>
                    <div class="sus_three">({{ID}})</div>
                    <div class="sus_four sus_three">
                        Eメールで送信された認証コードは10分間有効です
                    </div>
                    <div class="keep u-flex-center shou" @click="codeShow = false">
                        閉じる
                    </div>
                </div>
            </div>

            <!-- 隐私政策和利用规约弹窗 -->
            <div class="mask" v-if="show">
                <div class="maskContent">
                    <div class="ruleTitle">
                        <div class="ruleName">
                            利用規約とプライバシーポリシー
                        </div>
                        <img class="ruleClose" @click="show = false" src="../../static/ruleclose.svg" alt="" />
                    </div>
                    <div class="ruleConten">
                        <div class="ruleItem">
                            <div class="ruleDetailTitle">
                                利用規約
                            </div>
                            <div class="ruleDetail" v-text="content2">

                            </div>
                        </div>
                        <div class="ruleItem" style="margin-top: 40px;">
                            <div class="ruleDetailTitle">
                                プライバシーポリシー
                            </div>
                            <div class="ruleDetail" v-text="content1">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>

<script>
    import yzm from "../../components/yzm.vue";
    import {
        registerAccount,
        getConfig
    } from "@/http/api.js"
    export default {
        components: {
            yzm
        },
        data() {
            return {
                state: "one", //state=one第一步，state=two第二步，state=three第三步
                ID: "",
                int_one: "",
                store_name: "",
                name: "",
                codeShow: false,
                type: "",
                code: "",
                password: "",
                show: false,
                content1: "",
                content2: ""
            };
        },
        created() {
            getConfig().then((res) => {
                if (res.code == 200) {
                    this.content1 = res.data.config.shop_privacy_policy
                    this.content2 = res.data.config.shop_service_regulations
                }
            })
        },
        methods: {
            oneClick() {
                let that = this
                let data = {}
                data['request_type'] = 1
                data['email'] = that.ID
                registerAccount(data).then((res) => {
                    if (res.code == 200) {
                        that.codeShow = true;
                        that.state = "two";
                    } else {
                        that.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });
                    }
                })
            },
            toLogin() {
                this.$router.replace({
                    name: "login",
                    params: {
                        test: "test"
                    }
                })
            },
            preStep() {
                let that = this
                if (that.state == "two") {
                    that.state = 'one'
                } else if (that.state == "three") {
                    that.state = 'two'
                }
            },
            changes(e) {
                console.log(e)
                this.password = e
            },
            toRegister() {
                let that = this
                let data = {}
                data['request_type'] = 2
                data['email'] = that.ID
                data['code'] = that.password
                data['name'] = that.name
                data['shop_name'] = that.store_name
                registerAccount(data).then((res) => {
                    if (res.code == 200) {
                        that.state = "four";
                    } else {
                        that.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });
                    }
                })
            }
        },
    };
</script>
<style lang="scss" scoped>
    * {
        padding: 0;
        margin: 0;
    }
    
    .ly:hover{
        
    }
    
    .ho{
        color: #1a73e8;
        cursor: pointer;
    }
    
    .ho:hover{
        text-decoration: underline;
    }
    
    
    .goback {
        width: 100%;
        text-align: center;
        margin-top: 26px;
        font-size: 16px;
        color: #1d1d1f;
        cursor: pointer;
    }

    .goback:hover {
        opacity: 0.5 !important;
    }

    page {
        body: #f9f9f9;
    }

    .ruleDetailTitle {
        font-weight: bold;
    }

    .ruleDetail {
        margin-top: 10px;
        width: 100%;
        height: 30vh;
        background: #FFFFFF;
        border-radius: 10px 10px 10px 10px;
        border: 1px solid #AAAAAA;
        overflow-y: scroll;
        box-sizing: border-box;
        padding: 16px;
        white-space: pre-wrap;
        font-size: 12px;
        color: #1D1D1F;
    }

    .ruleConten {
        width: 100%;
        height: 80vh;
        box-sizing: border-box;
        padding: 40px;
    }

    .ruleName {
        font-size: 21px;
        color: #1D1D1F;
        font-family: Hiragino Sans, Hiragino Sans;
        font-weight: bold;
    }

    .ruleTitle {
        height: 40px;
        background: #EAEAEA;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 0 15px 0 40px;
    }

    .ruleClose {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .maskContent {
        width: 820px;
        background: #fff;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0px 3px 10px 1px rgba(29, 29, 31, 0.5);
    }

    .mask {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
    }

    .wrap {
        width: 100vw;
        // height: 100vh;
        box-sizing: border-box;
        background: #f9f9f9;

        .contentOne {
            height: 623px;
        }

        .contentTwo {
            // height: 695px;
        }

        .contentThree {
            // height: 600px;
        }

        .contentfour {
            height: 535px;
            border: 1px solid red;
        }

        .big_box {
            width: 99vw;
            box-sizing: border-box;
            padding: 60px;

            .login {
                width: 197px;

                // position: absolute;
                // top: -100px;
                // left: 60px;
                img {
                    width: 200px;
                    background: #f9f9f9;
                }
            }

            .content {
                width: 640px;
                padding: 30px 108px;
                margin: 0 auto;
                background: #fff;
                position: relative;
                box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);
                top: -60px;

                .one {
                    font-size: 16px;
                    text-align: center;
                    color: #aaaaaa;
                }

                .two {
                    margin: 0 auto;
                    margin-top: 14px;
                    border: 1px solid #aaa;
                    width: 400px;

                    .box_one {
                        width: 33.33%;
                        height: 12px;
                    }

                    .box_two {
                        width: 66.66%;
                        height: 12px;
                    }

                    .box_three {
                        width: 100%;
                        height: 12px;
                    }

                    .grey {
                        background: #aaa;
                    }
                }

                .three {
                    font-size: 24px;
                    color: #0a4b6d;
                    margin-top: 40px;
                    font-weight: bold;
                    text-align: center;
                }

                .int {
                    .four {
                        width: 400px;
                        margin: 0 auto;
                        font-size: 16px;
                        color: #0a4b6d;
                        margin-top: 20px;
                    }

                    .five {
                        display: block;
                        width: 400px;
                        margin: 0 auto;
                        height: 50px;
                        border-radius: 10px;
                        margin-top: 5px;
                        padding-left: 10px;
                        border: 1px solid #0a4b6d;
                    }

                    .six {
                        width: 400px;
                        height: 127px;
                        margin: 0 auto;
                        margin-top: 40px;
                        padding: 25px;
                        background: rgba(29, 29, 31, 0.1);

                        img {
                            width: 14px;
                            height: 14px;
                            margin-right: 10px;
                        }

                        .text {
                            width: 326px;
                            font-size: 14px;
                            color: #174a84;
                        }
                    }
                }

                .tit_two {
                    font-size: 14px;
                    color: #1d1d1f;
                    text-align: center;
                    margin-top: 40px;
                }

                input::-webkit-outer-spin-button,
                input::-webkit-inner-spin-button {
                    -webkit-appearance: none;
                }

                .tit_int {
                    width: 48px;
                    height: 76px;
                    text-align: center;
                    font-weight: bold;
                    font-size: 30px;
                    margin-right: 15px;
                    border-radius: 10px;
                    border: 2px solid #aaa;

                    &:nth-child(2) {
                        border: 2px solid #1a73e8;
                    }

                    &:nth-child(3) {
                        border: 2px solid #d93025;
                    }

                    &:nth-child(6) {
                        margin-right: 0;
                    }
                }

                .submit {
                    width: 400px !important;
                    height: 50px;
                    color: #fff;
                    font-size: 16px;
                    margin: 0 auto;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-top: 40px;
                    background: #0a4b6d;
                    border-radius: 25px;
                }

                .submitCss {
                    background: #0a4b6d;
                    opacity: 0.6;
                }

                .tip_one {
                    width: 640px;
                    position: absolute;
                    bottom: 24px;
                    left: 0;
                }

                .tip_two {
                    width: 424px;
                    font-size: 14px !important;
                    margin-top: 40px;
                }

                .tip_blue {
                    font-size: 14px;
                    color: #1a73e8;
                    margin: 23px auto;
                    text-align: center;
                }

                .tip {
                    font-size: 12px;
                    color: #707070;
                    text-align: center;
                    margin-top: 20px;
                }

                .th_one {
                    width: 420px;
                    font-size: 30px;
                    color: #1d1d1f;
                    font-weight: bold;
                }

                .th_two {
                    color: #1d1d1f;
                    font-size: 20px;
                    margin-top: 40px;
                    text-align: center;
                }

                .th_three {
                    color: #1d1d1f;
                    font-size: 20px;
                    margin-top: 20px;
                    font-weight: bold;
                    text-align: center;
                }

                .th_four {
                    font-size: 14px;
                    color: #707070;
                    margin-top: 20px;
                }
            }
        }

        .bottom {
            font-size: 12px;
            color: #1d1d1f;
            // position: absolute;
            // bottom: 18px;
            // left: 50%;
            // transform: translate(-50%, -50%); //移动的距离以自身的宽度为准
            text-align: center;
            margin-top: 40px;
        }
    }

    .fixed {
        position: fixed;
        top: 0%;
        left: 0%;
        width: 100vw;
        height: 100vh;
        z-index: 1;
        background: rgba(29, 29, 31, 0.2);

        .zhe {
            position: absolute;
            width: 100vw;
            height: 100vh;
            background: rgba(29, 29, 31, 0.2);
        }

        .sus-box {
            width: 480px;
            height: 364px;
            background: #ffffff;
            box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);
            border-radius: 10px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;

            .sus_tit {
                margin-top: 60px;
                font-size: 30px;
                color: #1d1d1f;
            }

            .sus_two {
                width: 306px;
                font-size: 14px;
                margin: 0 auto;
                margin-top: 34px;
                color: #1d1d1f;
            }

            .sus_three {
                width: 306px;
                font-size: 14px;
                margin: 0 auto;
                margin-top: 10px;
                color: #1d1d1f;
            }

            .sus_four {
                width: 330px;
                font-weight: bold;
            }

            .keep {
                width: 112px;
                height: 40px;
                font-size: 14px;
                color: #1d1d1f;
                margin: 0 auto;
                margin-top: 60px;
                background: #eaeaea;
                border-radius: 20px 20px 20px 20px;
                border: 1px solid #707070;
            }
        }
    }
</style>