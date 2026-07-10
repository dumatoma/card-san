<template>
    <div class="wrap no-size">
        <!-- 上导航栏 -->
        <div class="mark-title no-size">
            <img src="../../static/tou-logo.svg" alt="" />
        </div>
        <div class="content_login no-size">
            <div class="tit_one">認証コードを入力してください。</div>
            <div class="tit_two">
                アカウント登録されているEメールに送信された<br />6桁の認証コードを入力してください。
            </div>
            <div class="tit_two" style="margin-top: 10px">
                {{ type == "user" ? query.email : "(08012****89)" }}
            </div>
            <div class="tit_two" style="margin-top: 10px; font-weight: bold">
                {{
          type == "user"
            ? "Eメールで送信された認証コードは10分間有効です"
            : "SMSで送信された認証コードは10分間有効です"
        }}
            </div>
            <div class="u-flex u-row-between" style="margin-top: 40px">
                <yzm @changeCode="changes" :password="password" :width="48" :height="76" backgroundColor="#ffffff" :bold="true" showVal
                    :length="6">
                </yzm>
            </div>
            <el-button class="submit u-flex u-row-center" :loading="isloading"
                :style="password.length == 6?'background:#1A73E8' : ''" @click="sub">
                {{logword}}
            </el-button>
            <div class="tit_four">
                数分経ってもコードが届かない場合は、認証コードを再送するか、
                入力したメールアドレスが間違っている可能性がございますので、再度ID(メールアドレス)の入力から行なってください。
            </div>
            <div class="title_five shou" :style="isResend?'opacity: 0.6;' : ''" @click="resend">
                認証コードを再送
            </div>
            <div style="height: 12px;"></div>
        </div>
        <div class="popup" v-if="popShow">
            <div class="white no-size">
                <div class="pop_text">認証コードを送信しました</div>
                <div class="pop_two">
                    アカウント登録されているEメールに送信された6桁の認証コードを入力してください。
                </div>
                <div class="pop_two" style="margin-top: 10px">
                    {{ type == "user" ? query.email : "(08012****89)" }}
                </div>
                <div class="pop_two" style="margin-top: 10px; font-weight: bold">
                    {{
            type == "user"
              ? "Eメールで送信された認証コードは10分間有効です"
              : "SMSで送信された認証コードは10分間有効です"
          }}
                </div>
                <div class="pop_sub u-flex u-row-center shou" @click="popShow = false">
                    閉じる
                </div>
            </div>
        </div>
        <!-- 底部文字 -->
        <div class="footer u-flex u-row-center">
            Copyright (C) 2022 Customaid:LLC All Rights Reserved
        </div>
    </div>
</template>

<script>
    import yzm from "../../components/yzm.vue";
    import {
        Login
    } from "../../http/api"
    export default {
        components: {
            yzm
        },
        // 976615
        data() {
            return {
                popShow: false,
                type: "user",
                code: "",
                password: "",
                query: {},
                logword: "サインイン",
                isloading: false,
                isResend: false
            };
        },
        created() {
            let query = this.$route.query
            this.query = query
        },
        methods: {
            changes(e) {
                console.log(e)
                this.password = e
            },
            resend() {
                let that = this

                if (that.isResend == false) {
                    that.isResend = true
                    let data = {}
                    data["login_type"] = that.type == 'user' ? 1 : 2
                    data["email"] = that.query.email
                    data['request_type'] = 1
                    Login(data).then((res) => {
                        console.log(res)
                        that.isResend = false
                        if (res.code == 200) {
                            that.popShow = true
                        } else {
                            that.$message({
                                message: '' + res.message,
                                type: 'error',
                                offset: 400
                            });
                        }
                    })
                }
            },
            sub() {
                let that = this
                if (that.password.length == 6) {
                    that.isloading = true
                    that.logword = ''
                    let data = {}
                    data["login_type"] = that.type == 'user' ? 1 : 2
                    data["email"] = that.query.email
                    data["phone"] = that.query.email
                    data["request_type"] = 2
                    data["code"] = that.password
                    Login(data).then((res) => {
                        console.log(res)
                        if (res.code == 200) {
                            that.$message({
                                message: '' + res.message,
                                type: 'success',
                                offset: 400,
                                center: true
                            });
                            let str = JSON.stringify(res.data.admin)
                            localStorage.setItem("admin", str)
                            localStorage.setItem("token", res.data.token)
                               location.href = "https://card-san.jp"
                                  // location.href = "http://localhost:8082"
                               localStorage.setItem("ale",1)
                            setTimeout(() => {
                                // location.href = "https://card-san.jp"
                               
                               // that.$router.push("/topIndex")
                            }, 1000)
                        } else {
                            that.isloading = false
                            that.logword = 'サインイン'
                            that.$message({
                                message: res.message,
                                type: 'error',
                                offset: 400,
                                center: true
                            });
                            that.password = '' 
                        }
                    })
                }

            }
        },
        // 302705
    };
</script>
<style lang="scss" scoped>
    * {
        padding: 0;
        margin: 0;
    }

    .submit {
        width: 100%;
        height: 50px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        margin-top: 20px;
        margin-bottom: 20px;
        background: rgba(26, 115, 232, 0.6);
        border-radius: 25px;

    }

    .wrap {
        width: 100vw;
        height: 100vh;
        box-sizing: border-box;
        background: #f9f9f9;

        .mark-title {
            width: 100%;
            background: #1d1d1f;
            height: 64px;
            padding: 9px 0 9px 20px;

            img {
                width: 160px;
                background: #1d1d1f;
            }
        }

        .content_login {
            width: 640px;
            height: 520px;
            position: relative;
            text-align: center;
            left: 50%;
            top: 10%;
            transform: translateX(-50%); //移动的距离以自身的宽度为准
            background: #fff;
            padding: 1px 95px 0;
            box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);

            .tit_one {
                font-size: 30px;
                color: #1d1d1f;
                margin-top: 30px;
            }

            .tit_two {
                font-size: 14px;
                color: #1d1d1f;
                margin-top: 20px;
            }

            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
                -webkit-appearance: none;
            }

            .tit_int {
                width: 48px;
                height: 76px;
                outline: 0;
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

            .tit_three {
                width: 335px;
                height: 56px;
                background: rgba(26, 115, 232, 0.51);
                border-radius: 10px;
                margin: 40px auto;
                font-size: 20px;
                color: #fff;
            }

            .tit_threeCss {
                background: rgba(83, 177, 175, 0.5);
            }

            .tit_four {
                font-size: 14px;
                color: #707070;
            }

            .title_five {
                font-size: 14px;
                color: #2699fb;
                margin-top: 20px;
            }
        }

        .popup {
            top: 0;
            bottom: 0;
            position: absolute;
            z-index: 999;
            width: 100%;
            background: rgba(0, 0, 0, 0.16);

            .white {
                width: 480px;
                height: 376px;
                box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%); //移动的距离以自身的宽度为准
                background: #ffffff;
                border-radius: 11px;
                padding: 0 60px;
                color: #1d1d1f;

                .pop_text {
                    margin-top: 60px;
                    font-size: 30px;
                    font-weight: bold;
                }

                .pop_two {
                    font-size: 14px;
                    margin-top: 34px;
                    text-align: center;
                }

                .pop_sub {
                    width: 112px;
                    height: 40px;
                    background: #eaeaea;
                    border-radius: 20px;
                    border: 1px solid #707070;
                    margin: 60px auto;
                    font-size: 14px;
                    font-weight: bold;
                    color: #1d1d1f;
                }
            }
        }

        .footer {
            width: 100%;
            height: 50px;
            font-size: 12px;
            color: #fff;
            position: fixed;
            bottom: 0px;
            background: #1d1d1f;
        }
    }
</style>